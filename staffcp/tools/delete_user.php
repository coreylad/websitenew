<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('delete_user');

// Initialize variables
$Message = '';
$username = isset($_GET['username']) ? trim($_GET['username']) : '';

// Process form submission
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $username = trim($_POST['username'] ?? '');
    
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[7] ?? 'Invalid form token');
    }
    elseif (empty($username)) {
        $Message = showAlertErrorModern($Language[1] ?? 'Username is required');
    }
    else {
        try {
            $result = $TSDatabase->query(
                'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
                 FROM users u LEFT JOIN usergroups g ON u.usergroup = g.gid 
                 WHERE u.username = ? LIMIT 1',
                [$username]
            );
            
            if (!$result || !($User = $result->fetch(PDO::FETCH_ASSOC))) {
                $Message = showAlertErrorModern($Language[2] ?? 'User not found');
            }
            else {
                $adminResult = $TSDatabase->query(
                    'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
                     FROM users u LEFT JOIN usergroups g ON u.usergroup = g.gid 
                     WHERE u.id = ? LIMIT 1',
                    [$_SESSION['ADMIN_ID']]
                );
                
                $LoggedAdminDetails = $adminResult ? $adminResult->fetch(PDO::FETCH_ASSOC) : [];
                
                $canDelete = true;
                if (($User['cansettingspanel'] ?? 'no') === 'yes' && 
                    ($LoggedAdminDetails['cansettingspanel'] ?? 'no') !== 'yes') {
                    $canDelete = false;
                }
                if (($User['canstaffpanel'] ?? 'no') === 'yes' && 
                    ($LoggedAdminDetails['canstaffpanel'] ?? 'no') !== 'yes') {
                    $canDelete = false;
                }
                if (($User['issupermod'] ?? 'no') === 'yes' && 
                    ($LoggedAdminDetails['issupermod'] ?? 'no') !== 'yes') {
                    $canDelete = false;
                }
                
                if (!$canDelete) {
                    $Message = showAlertErrorModern($Language[10] ?? 'No permission');
                }
                else {
                    $TSDatabase->beginTransaction();
                    
                    try {
                        $TSDatabase->query('DELETE FROM users WHERE username = ?', [$username]);
                        
                        if ($TSDatabase->rowCount() > 0) {
                            $userId = $User['id'];
                            
                            $TSDatabase->query('DELETE FROM ts_support WHERE userid = ?', [$userId]);
                            $TSDatabase->query('DELETE FROM ts_u_perm WHERE userid = ?', [$userId]);
                            $TSDatabase->query('DELETE FROM bookmarks WHERE userid = ?', [$userId]);
                            $TSDatabase->query('DELETE FROM comments WHERE user = ?', [$userId]);
                            
                            $TSDatabase->commit();
                            
                            $SysMsg = str_replace(['{1}', '{2}'], [$username, $_SESSION['ADMIN_USERNAME']], $Language[8] ?? 'User deleted');
                            logStaffActionModern($SysMsg);
                            
                            $Message = showAlertSuccessModern($Language[3] ?? 'User deleted successfully');
                            $username = '';
                        }
                        else {
                            $TSDatabase->rollback();
                            $Message = showAlertErrorModern($Language[9] ?? 'Failed to delete user');
                        }
                    }
                    catch (Exception $e) {
                        $TSDatabase->rollback();
                        error_log('Delete user error: ' . $e->getMessage());
                        $Message = showAlertErrorModern($Language[9] ?? 'Failed to delete user');
                    }
                }
            }
        }
        catch (Exception $e) {
            error_log('Delete user query error: ' . $e->getMessage());
            $Message = showAlertErrorModern($Language[9] ?? 'Database error occurred');
        }
    }
}
?>
<form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=delete_user'); ?>" method="post">
<?php echo getFormTokenField(); ?>
<?php echo $Message; ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center" colspan="2">
            <b><?php echo escape_html($Language[5] ?? 'Delete User'); ?></b>
        </td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right"><?php echo escape_html($Language[4] ?? 'Username:'); ?></td>
        <td class="alt1">
            <input type="text" class="bginput" name="username" value="<?php echo escape_attr($username); ?>" size="35" dir="ltr" tabindex="1" />
        </td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" class="button" tabindex="1" value="<?php echo escape_attr($Language[5] ?? 'Delete'); ?>" accesskey="s" />
            <input type="reset" class="button" tabindex="1" value="<?php echo escape_attr($Language[6] ?? 'Reset'); ?>" accesskey="r" />
        </td>
    </tr>
</table>
</form>
