<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();

$Language = loadStaffLanguage('bonus_gift');
$Message = '';
$amount = '0';
$usergroups = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token');
    } else {
        $amount = (int)($_POST['amount'] ?? 0);
        $usergroups = isset($_POST['usergroups']) ? $_POST['usergroups'] : [];
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        
        if ($amount && ((!empty($usergroups) && is_array($usergroups)) || $username)) {
            $SysMsg = '';
            
            if ($username) {
                $usernames = strpos($username, ',') !== false ? array_map('trim', explode(',', $username)) : [$username];
                
                foreach ($usernames as $user) {
                    try {
                        $stmt = $TSDatabase->query('SELECT id FROM users WHERE username = ? LIMIT 1', [$user]);
                        
                        if ($stmt && $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $newModComment = date('Y-m-d') . ' - ' . str_replace(
                                ['{1}', '{2}', '{3}'],
                                [$user, $amount, $_SESSION['ADMIN_USERNAME']],
                                $Language[13] ?? 'Bonus gift of {2} to {1} by {3}'
                            ) . "\n";
                            
                            $TSDatabase->query(
                                'UPDATE users SET seedbonus = seedbonus + ?, 
                                 modcomment = IF(ISNULL(modcomment), ?, CONCAT(?, modcomment)) 
                                 WHERE username = ?',
                                [$amount, $newModComment, $newModComment, $user]
                            );
                        }
                    } catch (Exception $e) {
                        // Continue on error
                    }
                }
                
                $SysMsg = str_replace(
                    ['{1}', '{2}', '{3}'],
                    [$username, $amount, $_SESSION['ADMIN_USERNAME']],
                    $Language[13] ?? 'Bonus gift of {2} to {1} by {3}'
                );
            } else {
                try {
                    $placeholders = implode(',', array_fill(0, count($usergroups), '?'));
                    $SysMsg = str_replace(
                        ['{1}', '{2}', '{3}'],
                        [implode(',', $usergroups), $amount, $_SESSION['ADMIN_USERNAME']],
                        $Language[3] ?? 'Bonus gift of {2} to groups {1} by {3}'
                    );
                    
                    $newModComment = date('Y-m-d') . ' - ' . $SysMsg . "\n";
                    
                    $query = 'UPDATE users SET seedbonus = seedbonus + ?, 
                              modcomment = IF(ISNULL(modcomment), ?, CONCAT(?, modcomment)) 
                              WHERE usergroup IN (' . $placeholders . ')';
                    
                    $params = array_merge([$amount, $newModComment, $newModComment], $usergroups);
                    $TSDatabase->query($query, $params);
                } catch (Exception $e) {
                    $Message = showAlertErrorModern('Database error: ' . escape_html($e->getMessage()));
                }
            }
            
            if (!$Message && $SysMsg) {
                logStaffActionModern($SysMsg);
                $Message = showAlertSuccessModern($SysMsg);
            }
        } else {
            $Message = showAlertErrorModern($Language[10] ?? 'Please enter amount and select users or groups');
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

$count = 0;
$showusergroups = '<table><tr>';

try {
    $stmt = $TSDatabase->query('SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER BY disporder ASC');
    if ($stmt) {
        while ($UG = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($LoggedAdminDetails && (
                ($UG['cansettingspanel'] === 'yes' && $LoggedAdminDetails['cansettingspanel'] !== 'yes') ||
                ($UG['canstaffpanel'] === 'yes' && $LoggedAdminDetails['canstaffpanel'] !== 'yes') ||
                ($UG['issupermod'] === 'yes' && $LoggedAdminDetails['issupermod'] !== 'yes')
            )) {
                continue;
            }
            
            if ($count && $count % 8 === 0) {
                $showusergroups .= '</tr><tr>';
            }
            
            $showusergroups .= '<td><input type="checkbox" name="usergroups[]" value="' . (int)$UG['gid'] . '"' .
                (is_array($usergroups) && in_array($UG['gid'], $usergroups, true) ? ' checked="checked"' : '') .
                ' /></td><td>' . str_replace('{username}', escape_html($UG['title']), $UG['namestyle']) . '</td>';
            $count++;
        }
    }
} catch (Exception $e) {
    // Continue with empty list
}

$showusergroups .= '</tr></table>';

echo '
' . $Message . '
<form method="post" action="index.php?do=bonus_gift">
' . getFormTokenField() . '
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" colspan="2" align="center">' . ($Language[2] ?? 'Bonus Gift') . '</td>
    </tr>
    <tr>
        <td class="alt1" align="right">' . ($Language[4] ?? 'Amount') . '</td>
        <td class="alt1"><input type="text" name="amount" value="' . escape_attr($amount) . '" size="10" /></td>
    </tr>
    <tr>
        <td class="alt2" align="right">' . ($Language[11] ?? 'Username(s)') . '</td>
        <td class="alt2"><input type="text" name="username" value="' . escape_attr($username) . '" size="45" /> <small>' . ($Language[14] ?? 'Comma separated') . '</small></td>
    </tr>
    <tr>
        <td class="alt1" valign="top" align="right">' . ($Language[6] ?? 'User Groups') . '</td>
        <td class="alt1">' . $showusergroups . '</td>
    </tr>
    <tr>
        <td class="tcat2" align="right"></td>
        <td class="tcat2"><input type="submit" value="' . ($Language[7] ?? 'Submit') . '" /> <input type="reset" value="' . ($Language[8] ?? 'Reset') . '" /></td>
    </tr>
</table>
</form>';
