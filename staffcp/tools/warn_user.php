<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();

$Language = loadStaffLanguage('warn_user');
$Message = '';
$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$reason = '';
$warneduntil = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token');
    } else {
        $username = trim($_POST['username']);
        $reason = trim($_POST['reason']);
        $warneduntil = trim($_POST['warneduntil']);
        
        if ($username && $reason && $warneduntil) {
            try {
                $stmt = $TSDatabase->query(
                    'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
                     FROM users u 
                     LEFT JOIN usergroups g ON u.usergroup = g.gid 
                     WHERE u.username = ? 
                     LIMIT 1',
                    [$username]
                );
                
                if (!$stmt || !($User = $stmt->fetch(PDO::FETCH_ASSOC))) {
                    $Message = showAlertErrorModern($Language[2] ?? 'User not found');
                } elseif (strtotime($warneduntil) < time()) {
                    $Message = showAlertErrorModern($Language[14] ?? 'Warning date must be in the future');
                } else {
                    $SysMsg = str_replace(['{1}', '{2}'], [$username, $_SESSION['ADMIN_USERNAME']], $Language[10] ?? 'User {1} warned by {2}');
                    $modcomment = gmdate('Y-m-d') . ' - ' . trim($SysMsg) . ' Reason: ' . $reason . "\n";
                    
                    $TSDatabase->query(
                        'UPDATE users SET warned = ?, timeswarned = timeswarned + 1, warneduntil = ?, 
                         modcomment = CONCAT(?, modcomment) 
                         WHERE username = ?',
                        ['yes', $warneduntil, $modcomment, $username]
                    );
                    
                    logStaffActionModern($SysMsg);
                    
                    try {
                        $pmMsg = str_replace(
                            ['{1}', '{2}'],
                            [$_SESSION['ADMIN_USERNAME'], $reason],
                            $Language[11] ?? 'You have been warned by {1}. Reason: {2}'
                        );
                        $TSDatabase->query(
                            'INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location) 
                             VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)',
                            [0, $User['id'], $Language[7] ?? 'User Warning', $pmMsg, 'yes', 'no', '1']
                        );
                        $TSDatabase->query('UPDATE users SET pmunread = pmunread + 1 WHERE id = ?', [$User['id']]);
                    } catch (Exception $e) {
                        // PM failed but warning was applied
                    }
                    
                    $Message = showAlertSuccessModern($Language[3] ?? 'User warned successfully');
                }
            } catch (Exception $e) {
                $Message = showAlertErrorModern('Database error: ' . escape_html($e->getMessage()));
            }
        } else {
            $Message = showAlertErrorModern($Language[1] ?? 'All fields are required');
        }
    }
}

echo '<script type="text/javascript">
    $(function() {
        $(\'#warneduntil\').datepicker({dateFormat: "yy-mm-dd", changeMonth: true, changeYear: true, closeText: "X", showButtonPanel: true});
    });
</script>

<form action="' . escape_attr($_SERVER['SCRIPT_NAME']) . '?do=warn_user" method="post" name="warn_user">
' . getFormTokenField() . '
' . $Message . '
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center" colspan="2"><b>' . ($Language[7] ?? 'Warn User') . '</b></td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right">' . ($Language[4] ?? 'Username') . '</td>
        <td class="alt1"><input type="text" class="bginput" name="username" value="' . escape_attr($username) . '" size="35" dir="ltr" tabindex="1" /></td>
    </tr>
    <tr valign="top">
        <td class="alt2" align="right">' . ($Language[5] ?? 'Warned Until') . '</td>
        <td class="alt2"><input type="text" name="warneduntil" id="warneduntil" value="' . escape_attr($warneduntil) . '" size="10" dir="ltr" tabindex="1" /></td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right">' . ($Language[6] ?? 'Reason') . '</td>
        <td class="alt1"><input type="text" class="bginput" name="reason" value="' . escape_attr($reason) . '" size="35" dir="ltr" tabindex="1" /></td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" class="button" tabindex="1" value="' . ($Language[7] ?? 'Submit') . '" accesskey="s" />
            <input type="reset" class="button" tabindex="1" value="' . ($Language[8] ?? 'Reset') . '" accesskey="r" />
        </td>
    </tr>
</table>
</form>';
