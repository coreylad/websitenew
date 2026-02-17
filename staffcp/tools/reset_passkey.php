<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();

$Language = loadStaffLanguage('reset_passkey');
$Message = '';
$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$reason = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token');
    } else {
        $username = trim($_POST['username']);
        $reason = trim($_POST['reason']);
        
        if ($username && $reason) {
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
                } else {
                    $stmt2 = $TSDatabase->query(
                        'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
                         FROM users u 
                         LEFT JOIN usergroups g ON u.usergroup = g.gid 
                         WHERE u.id = ? 
                         LIMIT 1',
                        [$_SESSION['ADMIN_ID']]
                    );
                    $LoggedAdminDetails = $stmt2 ? $stmt2->fetch(PDO::FETCH_ASSOC) : null;
                    
                    if (($User['cansettingspanel'] === 'yes' && $LoggedAdminDetails['cansettingspanel'] !== 'yes') ||
                        ($User['canstaffpanel'] === 'yes' && $LoggedAdminDetails['canstaffpanel'] !== 'yes') ||
                        ($User['issupermod'] === 'yes' && $LoggedAdminDetails['issupermod'] !== 'yes')) {
                        $Message = showAlertErrorModern($Language[12] ?? 'Insufficient permissions');
                    } else {
                        $SysMsg = str_replace(['{1}', '{2}'], [$username, $_SESSION['ADMIN_USERNAME']], $Language[9] ?? 'Passkey reset for {1} by {2}');
                        $modcomment = gmdate('Y-m-d') . ' - ' . trim($SysMsg) . "\n";
                        
                        $TSDatabase->query(
                            'UPDATE users SET torrent_pass = ?, modcomment = CONCAT(?, modcomment) WHERE username = ?',
                            ['', $modcomment, $username]
                        );
                        
                        logStaffActionModern($SysMsg);
                        
                        try {
                            $pmMsg = str_replace(['{1}', '{2}'], [$_SESSION['ADMIN_USERNAME'], $reason], $Language[10] ?? 'Your passkey was reset by {1}. Reason: {2}');
                            $TSDatabase->query(
                                'INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location) 
                                 VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)',
                                [0, $User['id'], $Language[6] ?? 'Passkey Reset', $pmMsg, 'yes', 'no', '1']
                            );
                            $TSDatabase->query('UPDATE users SET pmunread = pmunread + 1 WHERE id = ?', [$User['id']]);
                        } catch (Exception $e) {
                            // PM failed but passkey was reset
                        }
                        
                        $Message = showAlertSuccessModern($Language[3] ?? 'Passkey reset successfully');
                    }
                }
            } catch (Exception $e) {
                $Message = showAlertErrorModern('Database error: ' . escape_html($e->getMessage()));
            }
        } else {
            $Message = showAlertErrorModern($Language[1] ?? 'All fields are required');
        }
    }
}

echo '<form action="' . escape_attr($_SERVER['SCRIPT_NAME']) . '?do=reset_passkey" method="post" name="reset_passkey">
' . getFormTokenField() . '
' . $Message . '
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center" colspan="2"><b>' . ($Language[6] ?? 'Reset Passkey') . '</b></td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right">' . ($Language[4] ?? 'Username') . '</td>
        <td class="alt1"><input type="text" class="bginput" name="username" value="' . escape_attr($username) . '" size="35" dir="ltr" tabindex="1" /></td>
    </tr>
    <tr valign="top">
        <td class="alt2" align="right">' . ($Language[5] ?? 'Reason') . '</td>
        <td class="alt2"><input type="text" class="bginput" name="reason" value="' . escape_attr($reason) . '" size="35" dir="ltr" tabindex="1" /></td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" class="button" tabindex="1" value="' . ($Language[6] ?? 'Submit') . '" accesskey="s" />
            <input type="reset" class="button" tabindex="1" value="' . ($Language[7] ?? 'Reset') . '" accesskey="r" />
        </td>
    </tr>
</table>
</form>';
