<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('traceroute_ip');

$Message = '';
$ip = '';

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[8] ?? 'Invalid form token');
    } else {
        $ip = preg_replace('/[^A-Za-z0-9.]/', '', trim($_POST['ip'] ?? ''));
        
        if ($ip) {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            
            ob_start();
            if ($isWindows) {
                system('tracert ' . escapeshellarg($ip));
            } else {
                system('traceroute ' . escapeshellarg($ip));
                system('killall -q traceroute 2>/dev/null');
            }
            $output = ob_get_contents();
            ob_end_clean();
            
            $Message = '<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
                <tr>
                    <td class="tcat" align="center"><b>' . escape_html($Language[4] ?? 'Results') . '</b></td>
                </tr>
                <tr>
                    <td class="alt1">
                        <pre>' . escape_html($output) . '</pre>
                    </td>
                </tr>
            </table>';
        } else {
            $Message = showAlertErrorModern($Language[5] ?? 'Please enter a valid IP');
        }
    }
    
    exit($Message);
}

?>
<form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME']); ?>?do=traceroute_ip" method="post" id="traceroute_ip">
<?php echo getFormTokenField(); ?>
<?php echo $Message; ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center" colspan="2">
            <b><?php echo escape_html($Language[2] ?? 'Traceroute IP'); ?></b>
        </td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right"><?php echo escape_html($Language[3] ?? 'IP Address'); ?></td>
        <td class="alt1">
            <input type="text" class="bginput" name="ip" value="<?php echo escape_attr($ip); ?>" size="35" dir="ltr" tabindex="1" />
        </td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" class="button" tabindex="1" value="<?php echo escape_attr($Language[6] ?? 'Trace'); ?>" accesskey="s" />
            <input type="reset" class="button" tabindex="1" value="<?php echo escape_attr($Language[7] ?? 'Reset'); ?>" accesskey="r" />
        </td>
    </tr>
</table>
</form>
<script type="text/javascript">
    $("#traceroute_ip").submit(function(e) {
        e.preventDefault();
        var $form = $(this), $fields = $form.serialize();
        
        $.ajax({
            url: '<?php echo escape_js($_SERVER['SCRIPT_NAME']); ?>?do=traceroute_ip',
            type: 'POST',
            data: $fields,
            success: function(response) {
                $('<div>'+response+'</div>').insertAfter($form);
            }
        });
        
        return false;
    });
</script>
