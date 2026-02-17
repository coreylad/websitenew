<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('mass_invite');

// Initialize variables
$Message = '';
$amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 5;

// Process form submission
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // Validate form token
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[7] ?? 'Invalid form token');
    }
    elseif ($amount < 1 || $amount > 1000) {
        $Message = showAlertErrorModern($Language[8] ?? 'Amount must be between 1 and 1000');
    }
    else {
        try {
            $HashArray = [];
            
            for ($i = 1; $i <= $amount; $i++) {
                $hash = substr(md5(md5((string)rand())), 0, 32);
                $HashArray[] = '<li>' . escape_html($hash) . '</li>';
                
                // Insert invite with prepared statement
                $TSDatabase->query(
                    'INSERT INTO invites (inviter, invitee, hash, time_invited) VALUES (?, ?, ?, NOW())',
                    [$_SESSION['ADMIN_ID'], 'manual', $hash]
                );
            }
            
            $SysMsg = str_replace('{1}', (string)number_format($amount), $Language[6] ?? '{1} invites created');
            logStaffActionModern($SysMsg);
            
            $Message = showAlertSuccessModern($SysMsg . '<hr /><ol>' . implode(' ', $HashArray) . '</ol>');
            
            // Reset amount after success
            $amount = 5;
        } catch (Exception $e) {
            error_log('Mass invite error: ' . $e->getMessage());
            $Message = showAlertErrorModern($Language[9] ?? 'Failed to create invites');
        }
    }
}

// Output form
?>
<?php echo $Message; ?>
<form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=mass_invite'); ?>" method="post">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" colspan="2" align="center">
            <b><?php echo escape_html($Language[2] ?? 'Mass Invite Generator'); ?></b>
        </td>
    </tr>
    <tr>
        <td class="alt1" align="right"><?php echo escape_html($Language[5] ?? 'Number of Invites:'); ?></td>
        <td class="alt1">
            <input type="text" class="bginput" name="amount" 
                   value="<?php echo escape_attr((string)$amount); ?>" 
                   style="width: 50px;" />
        </td>
    </tr>
    <tr>
        <td align="right" class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" class="button" 
                   value="<?php echo escape_attr($Language[3] ?? 'Generate'); ?>" />
            <input type="reset" class="button" 
                   value="<?php echo escape_attr($Language[4] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
