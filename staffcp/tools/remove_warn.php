<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('remove_warn');

// Initialize variables
$Message = '';
$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$reason = '';

// Process form submission
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // Validate form token
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[1] ?? 'Invalid form token');
    }
    else {
        $username = trim($_POST['username'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        
        if (empty($username) || empty($reason)) {
            $Message = showAlertErrorModern($Language[1] ?? 'All fields are required');
        }
        else {
            try {
                // Check if user exists
                $result = $TSDatabase->query(
                    'SELECT id FROM users WHERE username = ?',
                    [$username]
                );
                
                if (!$result || !($User = $result->fetch(PDO::FETCH_ASSOC))) {
                    $Message = showAlertErrorModern($Language[2] ?? 'User not found');
                }
                else {
                    // Remove warning
                    $SysMsg = str_replace(['{1}', '{2}'], [$username, $_SESSION['ADMIN_USERNAME']], 
                                        $Language[9] ?? 'Warning removed from {1} by {2}');
                    $modcomment = gmdate('Y-m-d') . ' - ' . trim($SysMsg) . ' Reason: ' . $reason . "\n";
                    
                    $TSDatabase->query(
                        'UPDATE users SET warned = ?, warneduntil = ?, modcomment = CONCAT(?, modcomment) WHERE username = ?',
                        ['no', '0000-00-00 00:00:00', $modcomment, $username]
                    );
                    
                    // Log action
                    logStaffActionModern($SysMsg);
                    
                    $Message = showAlertSuccessModern($Language[8] ?? 'Warning removed successfully');
                    
                    // Clear form
                    $username = '';
                    $reason = '';
                }
            } catch (Exception $e) {
                error_log('Remove warning error: ' . $e->getMessage());
                $Message = showAlertErrorModern($Language[10] ?? 'Failed to remove warning');
            }
        }
    }
}

// Output form
?>
<form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=remove_warn'); ?>" method="post" name="remove_warn">
<?php echo getFormTokenField(); ?>
<?php echo $Message; ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center" colspan="2">
            <b><?php echo escape_html($Language[6] ?? 'Remove Warning'); ?></b>
        </td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right">
            <?php echo escape_html($Language[4] ?? 'Username:'); ?>
        </td>
        <td class="alt1">
            <input type="text" class="bginput" name="username" 
                   value="<?php echo escape_attr($username); ?>" 
                   size="35" dir="ltr" tabindex="1" />
        </td>
    </tr>
    <tr valign="top">
        <td class="alt2" align="right">
            <?php echo escape_html($Language[5] ?? 'Reason:'); ?>
        </td>
        <td class="alt2">
            <input type="text" class="bginput" name="reason" 
                   value="<?php echo escape_attr($reason); ?>" 
                   size="35" dir="ltr" tabindex="1" />
        </td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" class="button" tabindex="1" 
                   value="<?php echo escape_attr($Language[6] ?? 'Remove Warning'); ?>" 
                   accesskey="s" />
            <input type="reset" class="button" tabindex="1" 
                   value="<?php echo escape_attr($Language[7] ?? 'Reset'); ?>" 
                   accesskey="r" />
        </td>
    </tr>
</table>
</form>
