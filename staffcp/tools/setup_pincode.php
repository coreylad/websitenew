<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('setup_pincode');

$Message = '';

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[13] ?? 'Invalid form token');
    } else {
        $current_pincode = trim($_POST['current_pincode'] ?? '');
        $new_pincode1 = trim($_POST['new_pincode1'] ?? '');
        $new_pincode2 = trim($_POST['new_pincode2'] ?? '');
        
        if ($current_pincode && $new_pincode1 && $new_pincode2) {
            if ($new_pincode1 === $new_pincode2) {
                try {
                    $result = $TSDatabase->query('SELECT pincode, sechash FROM pincode WHERE area = ?', [2]);
                    
                    if ($result && $Pincode = $result->fetch(PDO::FETCH_ASSOC)) {
                        $expectedHash = md5(md5($Pincode['sechash']) . md5($current_pincode));
                        
                        if ($Pincode['pincode'] === $expectedHash) {
                            $TSDatabase->query('DELETE FROM pincode WHERE area = ?', [2]);
                            
                            $configResult = $TSDatabase->query(
                                "SELECT content FROM ts_config WHERE configname = ?",
                                ['MAIN']
                            );
                            
                            $MAIN = ['SITENAME' => 'Default'];
                            if ($configResult && $configRow = $configResult->fetch(PDO::FETCH_ASSOC)) {
                                $MAIN = unserialize($configRow['content']);
                            }
                            
                            $sechash = md5($MAIN['SITENAME']);
                            $pincode = md5(md5($sechash) . md5($new_pincode1));
                            
                            $TSDatabase->query(
                                'INSERT INTO pincode (pincode, sechash, area) VALUES (?, ?, ?)',
                                [$pincode, $sechash, 2]
                            );
                            
                            if ($TSDatabase->rowCount() > 0) {
                                $SysMsg = str_replace(
                                    '{1}',
                                    $_SESSION['ADMIN_USERNAME'],
                                    $Language[12] ?? 'PIN code updated by {1}'
                                );
                                logStaffActionModern($SysMsg);
                                $Message = showAlertSuccessModern($Language[10] ?? 'PIN code updated successfully');
                            } else {
                                $Message = showAlertErrorModern($Language[11] ?? 'Failed to update PIN code');
                            }
                        } else {
                            $Message = showAlertErrorModern($Language[8] ?? 'Current PIN code is incorrect');
                        }
                    } else {
                        $Message = showAlertErrorModern($Language[11] ?? 'PIN code not found');
                    }
                } catch (Exception $e) {
                    error_log('Setup pincode error: ' . $e->getMessage());
                    $Message = showAlertErrorModern($Language[11] ?? 'Failed to update PIN code');
                }
            } else {
                $Message = showAlertErrorModern($Language[9] ?? 'New PIN codes do not match');
            }
        } else {
            $Message = showAlertErrorModern($Language[3] ?? 'Please fill all fields');
        }
    }
}

?>
<?php echo $Message; ?>
<form method="post" action="index.php?do=setup_pincode">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" colspan="2" align="center">
            <?php echo escape_html($Language[2] ?? 'Setup PIN Code'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[4] ?? 'Current PIN Code'); ?></td>
        <td class="alt1"><input type="password" name="current_pincode" value="" size="35" autocomplete="off" /></td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[5] ?? 'New PIN Code'); ?></td>
        <td class="alt1"><input type="password" name="new_pincode1" value="" size="35" autocomplete="off" /></td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[6] ?? 'Confirm New PIN Code'); ?></td>
        <td class="alt1"><input type="password" name="new_pincode2" value="" size="35" autocomplete="off" /></td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" value="<?php echo escape_attr($Language[7] ?? 'Update'); ?>" />
        </td>
    </tr>
</table>
</form>
