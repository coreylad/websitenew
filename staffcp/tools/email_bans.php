<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('email_bans');

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
            // Update banned emails
            $TSDatabase->query(
                'UPDATE bannedemails SET value = ? WHERE id = 1',
                [$value]
            );
            
            $SysMsg = str_replace('{1}', $_SESSION['ADMIN_USERNAME'], $Language[5] ?? 'Banned emails updated by {1}');
            logStaffActionModern($SysMsg);
            $Message = showAlertSuccessModern($SysMsg);
        } catch (Exception $e) {
            error_log('Email bans error: ' . $e->getMessage());
            $Message = showAlertErrorModern($Language[9] ?? 'Failed to update banned emails');
        }
    }
}

// Get current banned emails
try {
    $result = $TSDatabase->query('SELECT value FROM bannedemails WHERE id = 1');
    $BANNEDEMAILS = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['value' => ''];
    $value = $BANNEDEMAILS['value'] ?? '';
} catch (Exception $e) {
    error_log('Email bans fetch error: ' . $e->getMessage());
    $value = '';
}

// Output form
?>
<?php echo $Message; ?>
<form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=email_bans'); ?>" method="post">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center">
            <b><?php echo escape_html($Language[2] ?? 'Banned Email Addresses'); ?></b>
        </td>
    </tr>
    <tr>
        <td class="alt1">
            <textarea style="width: 99%; height: 100px;" name="value"><?php echo escape_html($value); ?></textarea>
            <div><?php echo escape_html($Language[8] ?? 'Enter banned email patterns (one per line)'); ?></div>
        </td>
    </tr>
    <tr>
        <td class="tcat2">
            <input type="submit" class="button" 
                   value="<?php echo escape_attr($Language[6] ?? 'Submit'); ?>" />
            <input type="reset" class="button" 
                   value="<?php echo escape_attr($Language[7] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
