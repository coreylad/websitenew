<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();

$Language = loadStaffLanguage('manage_staff_team');
$Message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token');
    } else {
        $STAFFTEAM = isset($_POST['staffteam']) ? $_POST['staffteam'] : [];
        
        if (is_array($STAFFTEAM) && count($STAFFTEAM) > 0 && $STAFFTEAM[0] !== '') {
            $NewStaffArray = [];
            $ErrorArray = [];
            
            foreach ($STAFFTEAM as $StaffMember) {
                $StaffMember = trim($StaffMember);
                if ($StaffMember && !in_array($StaffMember, $NewStaffArray, true)) {
                    try {
                        $stmt = $TSDatabase->query(
                            'SELECT id FROM users WHERE username = ? AND enabled = ? AND status = ? LIMIT 1',
                            [$StaffMember, 'yes', 'confirmed']
                        );
                        
                        if ($stmt && $Result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $NewStaffArray[] = $StaffMember . ':' . $Result['id'];
                        } else {
                            $ErrorArray[] = $StaffMember;
                        }
                    } catch (Exception $e) {
                        $ErrorArray[] = $StaffMember;
                    }
                }
            }
            
            if (!empty($NewStaffArray)) {
                $NewStaffArrayStr = implode(',', $NewStaffArray);
                try {
                    $TSDatabase->query(
                        'REPLACE INTO ts_config (configname, content) VALUES (?, ?)',
                        ['STAFFTEAM', $NewStaffArrayStr]
                    );
                    logStaffActionModern(str_replace('{1}', $_SESSION['ADMIN_USERNAME'], $Language[11] ?? 'Staff team updated by {1}'));
                    $Message .= showAlertSuccessModern($Language[7] ?? 'Staff team updated successfully');
                } catch (Exception $e) {
                    $Message .= showAlertErrorModern('Database error: ' . escape_html($e->getMessage()));
                }
            }
            
            if (!empty($ErrorArray)) {
                $Message .= showAlertErrorModern(str_replace('{1}', escape_html(implode(', ', $ErrorArray)), $Language[6] ?? 'Invalid usernames: {1}'));
            }
        } else {
            $Message = showAlertErrorModern($Language[8] ?? 'Please enter at least one username');
        }
    }
}

try {
    $stmt = $TSDatabase->query('SELECT content FROM ts_config WHERE configname = ?', ['STAFFTEAM']);
    $Result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    $STAFFTEAM = $Result ? trim($Result['content']) : '';
} catch (Exception $e) {
    $STAFFTEAM = '';
}

$STAFFTEAM = explode(',', $STAFFTEAM);
$List = '';

foreach ($STAFFTEAM as $Member) {
    $NameIDArray = explode(':', $Member);
    $List .= ' <input type="text" size="20" name="staffteam[]" value="' . escape_attr($NameIDArray[0] ?? '') . '" /> ';
}

echo '<script type="text/javascript">
    function addnewfield() {
        var name = prompt("' . trim($Language[12] ?? 'Enter username') . '", "");
        if (name) {
            TSGetID("newfield").innerHTML = TSGetID("newfield").innerHTML + \' <input type="text" size="20" name="staffteam[]" value="\' + name + \'" />\';
            alert(\'' . trim($Language[13] ?? 'Field added') . '\');
        }
    }
</script>

' . $Message . '
<form method="post" action="index.php?do=manage_staff_team">
' . getFormTokenField() . '
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center">' . ($Language[2] ?? 'Manage Staff Team') . '</td>
    </tr>
    <tr>
        <td class="alt1">
            <img src="images/tool_new.png" border="0" style="vertical-align: middle;" onclick="javascript: addnewfield();" alt="' . ($Language[9] ?? 'Add') . '" title="' . ($Language[9] ?? 'Add') . '" />
            ' . $List . ' <span id="newfield"></span> <input type="submit" value="' . ($Language[5] ?? 'Save') . '" />
        </td>
    </tr>
</table>
</form>';
