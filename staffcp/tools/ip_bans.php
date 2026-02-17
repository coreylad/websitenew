<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('ip_bans');

// Initialize variables
$Message = '';
$value = '';

// Process form submission
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // Validate form token
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[4] ?? 'Invalid form token');
    }
    else {
        $value = trim($_POST['value'] ?? '');
        
        try {
            // Update IP bans list
            $TSDatabase->query(
                'UPDATE ipbans SET value = ?, date = NOW(), modifier = ? WHERE id = 1',
                [$value, $_SESSION['ADMIN_ID']]
            );
            
            if ($TSDatabase->rowCount() > 0) {
                $SysMsg = str_replace('{1}', $_SESSION['ADMIN_USERNAME'], 
                                    $Language[5] ?? 'IP bans updated by {1}');
                logStaffActionModern($SysMsg);
                
                $Message = showAlertSuccessModern($Language[9] ?? 'IP bans updated successfully');
            }
        } catch (Exception $e) {
            error_log('IP bans error: ' . $e->getMessage());
            $Message = showAlertErrorModern($Language[10] ?? 'Failed to update IP bans');
        }
    }
}

// Get current IP bans
try {
    $result = $TSDatabase->query('SELECT value FROM ipbans WHERE id = 1');
    $IPBANS = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['value' => ''];
    $value = $IPBANS['value'] ?? '';
} catch (Exception $e) {
    error_log('Get IP bans error: ' . $e->getMessage());
    $value = '';
}

// Output form
?>
<?php echo $Message; ?>
<form method="post" action="index.php?do=ip_bans">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center">
            <?php echo escape_html($Language[2] ?? 'IP Bans'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt1">
            <textarea style="width: 99%; height: 400px;" name="value"><?php echo escape_html($value); ?></textarea>
            <small><?php echo escape_html($Language[8] ?? 'Enter IPs separated by spaces'); ?></small>
        </td>
    </tr>
    <tr>
        <td class="tcat2">
            <input type="submit" value="<?php echo escape_attr($Language[6] ?? 'Update'); ?>" />
            <input type="reset" value="<?php echo escape_attr($Language[7] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
