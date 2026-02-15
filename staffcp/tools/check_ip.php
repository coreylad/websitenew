<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('check_ip');

// Initialize variables
$Message = '';
$ip = '';
$Result = '';

// Process form submission
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // Validate form token
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[4] ?? 'Invalid form token');
    }
    else {
        // Sanitize IP input
        $ip = preg_replace('/[^A-Za-z0-9.]/', '', trim($_POST['ip'] ?? ''));
        
        if (empty($ip)) {
            $Message = showAlertErrorModern($Language[4] ?? 'IP address is required');
        }
        else {
            try {
                // Check if IP is banned
                $result = $TSDatabase->query('SELECT value FROM ipbans WHERE id = 1');
                
                if ($result) {
                    $row = $result->fetch(PDO::FETCH_ASSOC);
                    $iplist = explode(' ', $row['value'] ?? '');
                    
                    if (in_array($ip, $iplist, true)) {
                        $Result = '<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
                            <tr>
                                <td class="tcat" align="center"><b>' . escape_html($Language[3] ?? 'Result') . '</b></td>
                            </tr>
                            <tr>
                                <td class="alt1">' . escape_html($Language[7] ?? 'IP is banned') . '</td>
                            </tr>
                        </table>';
                    } else {
                        $Result = '<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
                            <tr>
                                <td class="tcat" align="center"><b>' . escape_html($Language[3] ?? 'Result') . '</b></td>
                            </tr>
                            <tr>
                                <td class="alt1">' . escape_html($Language[8] ?? 'IP is not banned') . '</td>
                            </tr>
                        </table>';
                    }
                }
            } catch (Exception $e) {
                error_log('Check IP error: ' . $e->getMessage());
                $Message = showAlertErrorModern($Language[9] ?? 'Database error occurred');
            }
        }
    }
}

// Output result if available
echo $Result;

// Output form
?>
<form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=check_ip'); ?>" method="post">
<?php echo getFormTokenField(); ?>
<?php echo $Message; ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center" colspan="2">
            <b><?php echo escape_html($Language[5] ?? 'Check IP'); ?></b>
        </td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right">
            <?php echo escape_html($Language[2] ?? 'IP Address:'); ?>
        </td>
        <td class="alt1">
            <input type="text" class="bginput" name="ip" 
                   value="<?php echo escape_attr($ip); ?>" 
                   size="35" dir="ltr" tabindex="1" />
        </td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" class="button" tabindex="1" 
                   value="<?php echo escape_attr($Language[5] ?? 'Check IP'); ?>" 
                   accesskey="s" />
            <input type="reset" class="button" tabindex="1" 
                   value="<?php echo escape_attr($Language[6] ?? 'Reset'); ?>" 
                   accesskey="r" />
        </td>
    </tr>
</table>
</form>
