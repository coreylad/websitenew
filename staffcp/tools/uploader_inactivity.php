<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('uploader_inactivity');

$Message = '';

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[14] ?? 'Invalid form token');
    } else {
        $UploaderGroups = implode(',', $_POST['usergroups'] ?? []);
        $DemoteTo = (int)($_POST['DemoteTo'] ?? 0);
        $timelimit = (int)($_POST['timelimit'] ?? 0);
        
        $Configarray = serialize([
            'UploaderGroups' => $UploaderGroups,
            'DemoteTo' => $DemoteTo,
            'timelimit' => $timelimit
        ]);
        
        try {
            $TSDatabase->query(
                "REPLACE INTO ts_config VALUES (?, ?)",
                ['UI', $Configarray]
            );
            
            $SysMsg = str_replace(
                ['{1}'],
                [$_SESSION['ADMIN_USERNAME']],
                $Language[3] ?? 'Uploader inactivity settings updated by {1}'
            );
            
            logStaffActionModern($SysMsg);
            $Message = showAlertSuccessModern($SysMsg);
        } catch (Exception $e) {
            error_log('Update uploader inactivity error: ' . $e->getMessage());
            $Message = showAlertErrorModern('Failed to update settings');
        }
    }
}

$UI = ['UploaderGroups' => '', 'DemoteTo' => 0, 'timelimit' => 0];
try {
    $result = $TSDatabase->query("SELECT content FROM ts_config WHERE configname = ?", ['UI']);
    if ($result && $row = $result->fetch(PDO::FETCH_ASSOC)) {
        $UI = unserialize($row['content']);
    }
} catch (Exception $e) {
    error_log('Get UI config error: ' . $e->getMessage());
}

$UploaderGroups = $UI['UploaderGroups'] ? explode(',', $UI['UploaderGroups']) : [];

$sgids = '<fieldset><legend>' . escape_html($Language[6] ?? 'Uploader Groups') . '</legend>
    <table cellpadding="0" cellspacing="0" border="0" class="mainTableNoBorder">
        <tr>';

try {
    $result = $TSDatabase->query('SELECT gid, title, namestyle FROM usergroups');
    if ($result) {
        $scount = 1;
        while ($gid = $result->fetch(PDO::FETCH_ASSOC)) {
            if ($scount % 4 === 1 && $scount > 1) {
                $sgids .= '</tr><tr>';
            }
            
            $checked = in_array((string)$gid['gid'], $UploaderGroups, true) ? ' checked="checked"' : '';
            $groupName = str_replace('{username}', $gid['title'], $gid['namestyle']);
            
            $sgids .= '<td class="alt1">
                <input type="checkbox" name="usergroups[]" value="' . (int)$gid['gid'] . '"' . $checked . ' />
            </td>
            <td class="alt1">' . $groupName . '</td>';
            
            $scount++;
        }
    }
} catch (Exception $e) {
    error_log('Get usergroups error: ' . $e->getMessage());
}

$sgids .= '</tr></table></fieldset>';

$udgids = '<select name="DemoteTo">';
try {
    $result = $TSDatabase->query(
        'SELECT gid, title FROM usergroups WHERE cansettingspanel = ? AND canstaffpanel = ? AND issupermod = ?',
        ['no', 'no', 'no']
    );
    
    if ($result) {
        while ($udgid = $result->fetch(PDO::FETCH_ASSOC)) {
            $selected = ($UI['DemoteTo'] === $udgid['gid']) ? ' selected="selected"' : '';
            $udgids .= '<option value="' . (int)$udgid['gid'] . '"' . $selected . '>' . escape_html($udgid['title']) . '</option>';
        }
    }
} catch (Exception $e) {
    error_log('Get demote groups error: ' . $e->getMessage());
}
$udgids .= '</select>';

?>
<?php echo showAlertInfoModern('<a href="index.php?do=manage_cronjobs">' . escape_html($Language[13] ?? 'Manage Cronjobs') . '</a>'); ?>
<?php echo $Message; ?>
<form method="post" action="index.php?do=uploader_inactivity">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" colspan="3" align="center">
            <?php echo escape_html($Language[2] ?? 'Uploader Inactivity'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt2" colspan="2"><?php echo escape_html($Language[4] ?? 'Settings'); ?></td>
    </tr>
    <tr>
        <td class="alt1" width="50%" align="justify" valign="top">
            <?php echo escape_html($Language[5] ?? 'Select uploader groups'); ?>
        </td>
        <td class="alt1" width="50%" valign="top">
            <?php echo $sgids; ?>
        </td>
    </tr>
    <tr>
        <td class="alt2" colspan="2"><?php echo escape_html($Language[7] ?? 'Demotion Settings'); ?></td>
    </tr>
    <tr>
        <td class="alt1" width="50%" align="justify" valign="top">
            <?php echo escape_html($Language[8] ?? 'Demote to group'); ?>
        </td>
        <td class="alt1" width="50%" valign="top">
            <?php echo $udgids; ?>
        </td>
    </tr>
    <tr>
        <td class="alt2" colspan="2"><?php echo escape_html($Language[9] ?? 'Time Limit'); ?></td>
    </tr>
    <tr>
        <td class="alt1" width="50%" align="justify" valign="top">
            <?php echo escape_html($Language[10] ?? 'Inactive days before demotion'); ?>
        </td>
        <td class="alt1" width="50%" valign="top">
            <input type="text" name="timelimit" value="<?php echo (int)$UI['timelimit']; ?>" size="10" />
        </td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" value="<?php echo escape_attr($Language[11] ?? 'Update'); ?>" /> 
            <input type="reset" value="<?php echo escape_attr($Language[12] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
