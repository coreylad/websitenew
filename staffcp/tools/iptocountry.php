<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();

$Language = loadStaffLanguage('iptocountry');
$Message = '';
$ip = isset($_GET['ip']) ? trim($_GET['ip']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token');
    } else {
        $ip = preg_replace('/[^A-Za-z0-9.]/', '', trim($_POST['ip']));
        
        if ($ip) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'content' => 'ip_address=' . urlencode($ip),
                        'timeout' => 10
                    ]
                ]);
                
                $postResult = @file_get_contents('http://ip-to-country.webhosting.info/node/view/36', false, $context);
                
                if ($postResult) {
                    $regex = '#<b>' . preg_quote($ip, '#') . '</b>(.*?).<br><br><img src=(.*)>#U';
                    preg_match_all($regex, $postResult, $result, PREG_SET_ORDER);
                    
                    if (isset($result[0][1])) {
                        $Res = str_replace(['{1}', '{2}'], [escape_html($ip), $result[0][1]], $Language[7] ?? 'IP {1} is from: {2}');
                        echo '<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
                            <tr>
                                <td class="tcat" align="center"><b>' . ($Language[3] ?? 'Result') . '</b></td>
                            </tr>
                            <tr>
                                <td class="alt1">' . $Res . '</td>
                            </tr>
                        </table>';
                    } else {
                        $Message = showAlertErrorModern($Language[9] ?? 'Could not determine country');
                    }
                } else {
                    $Message = showAlertErrorModern($Language[9] ?? 'Could not connect to lookup service');
                }
            } catch (Exception $e) {
                $Message = showAlertErrorModern('Error: ' . escape_html($e->getMessage()));
            }
        } else {
            $Message = showAlertErrorModern($Language[4] ?? 'Please enter an IP address');
        }
    }
}

echo '<form action="' . escape_attr($_SERVER['SCRIPT_NAME']) . '?do=iptocountry" name="iptocountryform" method="post" onsubmit="document.iptocountryform.submit.disabled=true">
' . getFormTokenField() . '
' . $Message . '
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center" colspan="2"><b>' . ($Language[8] ?? 'IP to Country') . '</b></td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right">' . ($Language[2] ?? 'IP Address') . '</td>
        <td class="alt1"><input type="text" class="bginput" name="ip" value="' . escape_attr($ip) . '" size="35" dir="ltr" tabindex="1" /></td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" name="submit" class="button" tabindex="1" value="' . ($Language[5] ?? 'Submit') . '" accesskey="s" />
            <input type="reset" class="button" tabindex="1" value="' . ($Language[6] ?? 'Reset') . '" accesskey="r" />
        </td>
    </tr>
</table>
</form>';
