<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();

$Language = loadStaffLanguage('mass_pm');
$Message = '';
$subject = '';
$msg = '';
$sender = '0';
$usergroups = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token');
    } else {
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $msg = isset($_POST['msg']) ? trim($_POST['msg']) : '';
        $sender = isset($_POST['sender']) ? (int)$_POST['sender'] : 0;
        $usergroups = isset($_POST['usergroups']) ? $_POST['usergroups'] : [];
        
        if ($subject && $msg && !empty($usergroups)) {
            try {
                $placeholders = implode(',', array_fill(0, count($usergroups), '?'));
                $stmt = $TSDatabase->query(
                    'SELECT id FROM users WHERE usergroup IN (' . $placeholders . ')',
                    $usergroups
                );
                
                $total = 0;
                if ($stmt) {
                    while ($User = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        try {
                            $TSDatabase->query(
                                'INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location) 
                                 VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)',
                                [$sender, $User['id'], $subject, $msg, 'yes', 'no', '1']
                            );
                            $TSDatabase->query(
                                'UPDATE users SET pmunread = pmunread + 1 WHERE id = ?',
                                [$User['id']]
                            );
                            $total++;
                        } catch (Exception $e) {
                            // Continue on error
                        }
                    }
                }
                
                if ($total > 0) {
                    $Message = showAlertSuccessModern(str_replace('{1}', number_format($total), $Language[12] ?? 'Sent to {1} users'));
                    logStaffActionModern(str_replace(
                        ['{1}', '{2}', '{3}'],
                        [$_SESSION['ADMIN_USERNAME'], implode(',', $usergroups), $subject],
                        $Language[13] ?? 'Mass PM sent by {1} to groups {2}: {3}'
                    ));
                }
            } catch (Exception $e) {
                $Message = showAlertErrorModern('Database error: ' . escape_html($e->getMessage()));
            }
        } else {
            $Message = showAlertErrorModern($Language[3] ?? 'All fields are required');
        }
    }
}

try {
    $stmt = $TSDatabase->query(
        'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
         FROM users u 
         LEFT JOIN usergroups g ON u.usergroup = g.gid 
         WHERE u.id = ? 
         LIMIT 1',
        [$_SESSION['ADMIN_ID']]
    );
    $LoggedAdminDetails = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
} catch (Exception $e) {
    $LoggedAdminDetails = null;
}

$showusergroups = '';
try {
    $stmt = $TSDatabase->query('SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER BY disporder ASC');
    if ($stmt) {
        while ($UG = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $showusergroups .= '<div style="margin-bottom: 3px;">
                <label><input type="checkbox" name="usergroups[]" value="' . (int)$UG['gid'] . '"' . 
                (in_array($UG['gid'], $usergroups, true) ? ' checked="checked"' : '') . 
                ' style="vertical-align: middle;" /> ' . 
                strip_tags(str_replace('{username}', escape_html($UG['title']), $UG['namestyle']), '<b><span><strong><em><i><u>') . 
                '</label>
            </div>';
        }
    }
} catch (Exception $e) {
    // Continue with empty list
}

function loadTinyMCEEditor(int $type, string $mode, string $elements): string {
    define('EDITOR_TYPE', $type);
    define('TINYMCE_MODE', $mode);
    define('TINYMCE_ELEMENTS', $elements);
    define('WORKPATH', './../scripts/');
    define('TINYMCE_EMOTIONS_URL', './../tinymce_emotions.php');
    
    global $TSDatabase;
    try {
        $stmt = $TSDatabase->query('SELECT content FROM ts_config WHERE configname = ?', ['MAIN']);
        if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $configData = @unserialize($row['content']);
            define('PIC_BASEURL', $configData['pic_base_url'] ?? '');
        }
    } catch (Exception $e) {
        define('PIC_BASEURL', '');
    }
    
    ob_start();
    if (file_exists('./../tinymce.php')) {
        include './../tinymce.php';
    }
    return ob_get_clean();
}

echo loadTinyMCEEditor(2, 'exact', 'textarea1') . '

' . $Message . '
<form method="post" action="index.php?do=mass_pm">
' . getFormTokenField() . '
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" colspan="2" align="center">' . ($Language[2] ?? 'Mass PM') . '</td>
    </tr>
    <tr>
        <td class="alt1" valign="top" style="width: 155px;">' . ($Language[6] ?? 'Sender') . '</td>
        <td class="alt1">
            <select name="sender">
                <option value="0"' . ($sender === '0' ? ' selected="selected"' : '') . '>' . ($Language[7] ?? 'System') . '</option>
                <option value="' . (int)$_SESSION['ADMIN_ID'] . '"' . ($sender === $_SESSION['ADMIN_ID'] ? ' selected="selected"' : '') . '>' . ($Language[8] ?? 'Me') . ' (' . escape_html($_SESSION['ADMIN_USERNAME']) . ')</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="alt1">' . ($Language[4] ?? 'Subject') . '</td>
        <td class="alt1"><input type="text" name="subject" value="' . escape_attr($subject) . '" style="width: 99%;" /></td>
    </tr>
    <tr>
        <td class="alt1" valign="top">' . ($Language[5] ?? 'Message') . '</td>
        <td class="alt1"><textarea name="msg" id="textarea1" style="width: 100%; height: 200px;">' . escape_html($msg) . '</textarea>
        <p><a href="javascript:toggleEditor(\'textarea1\');"><img src="images/tool_refresh.png" border="0" /></a></p></td>
    </tr>
    <tr>
        <td class="alt1" valign="top">' . ($Language[9] ?? 'User Groups') . '</td>
        <td class="alt1">' . $showusergroups . '</td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2"><input type="submit" value="' . ($Language[10] ?? 'Send') . '" /> <input type="reset" value="' . ($Language[11] ?? 'Reset') . '" /></td>
    </tr>
</table>
</form>';
