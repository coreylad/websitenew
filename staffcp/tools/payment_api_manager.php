<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();

$Language = loadStaffLanguage('payment_api_manager');
$Message = '';
$Act = isset($_GET['act']) ? trim($_GET['act']) : (isset($_POST['act']) ? trim($_POST['act']) : '');

if ($Act === 'edit' && isset($_GET['aid']) && ($Aid = (int)$_GET['aid'])) {
    $stmt = $TSDatabase->query('SELECT * FROM ts_subscriptions_api WHERE aid = ?', [$Aid]);
    
    if ($stmt && $API = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $active = $API['active'];
        $email = $API['email'];
        $secretkey = $API['secretkey'];
        $title = escape_html($API['title']);
        $currency = escape_html($API['currency']);
        $widget = $API['widget'];
        $secretkey2 = $API['secretkey2'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validateFormToken($_POST['form_token'] ?? '')) {
                $Message = showAlertErrorModern($Language[17] ?? 'Invalid form token');
            } else {
                $active = isset($_POST['active']) ? (int)$_POST['active'] : 0;
                $email = isset($_POST['email']) && !empty($_POST['email']) ? trim($_POST['email']) : $email;
                $secretkey = isset($_POST['secretkey']) && !empty($_POST['secretkey']) ? trim($_POST['secretkey']) : $secretkey;
                $currency = isset($_POST['currency']) && !empty($_POST['currency']) ? trim($_POST['currency']) : $currency;
                $widget = isset($_POST['widget']) && !empty($_POST['widget']) ? trim($_POST['widget']) : $widget;
                $secretkey2 = isset($_POST['secretkey2']) && !empty($_POST['secretkey2']) ? trim($_POST['secretkey2']) : $secretkey2;
                
                if ((!$email || !$currency) && $API['method'] !== 'paymentwall') {
                    $Message = showAlertErrorModern($Language[17] ?? 'Required fields missing');
                }
                
                if (empty($Message)) {
                    try {
                        $TSDatabase->query(
                            'UPDATE ts_subscriptions_api SET active = ?, email = ?, secretkey = ?, currency = ?, widget = ?, secretkey2 = ? WHERE aid = ?',
                            [$active, $email, $secretkey, $currency, $widget, $secretkey2, $Aid]
                        );
                        redirectTo('index.php?do=payment_api_manager');
                    } catch (Exception $e) {
                        $Message = showAlertErrorModern('Database error: ' . escape_html($e->getMessage()));
                    }
                }
            }
        }
        
        echo '<form action="index.php?do=payment_api_manager&amp;act=edit&amp;aid=' . $Aid . '" method="post">
        ' . getFormTokenField() . '
        ' . $Message . '
        <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
            <tr>
                <td class="tcat" align="center" colspan="2">' . ($Language[14] ?? 'Edit Payment API') . '</td>
            </tr>
            <tr>
                <td class="alt1" width="50%" valign="top">
                    <fieldset>
                        <legend>' . ($Language[3] ?? 'Title') . '</legend>
                        ' . $title . '
                    </fieldset>
                    <fieldset>
                        <legend>' . ($Language[5] ?? 'Status') . '</legend>
                        <input type="radio" name="active" value="1"' . ($active === '1' ? ' checked="checked"' : '') . ' /> ' . ($Language[7] ?? 'Active') . '
                        <input type="radio" name="active" value="0"' . ($active === '0' ? ' checked="checked"' : '') . ' /> ' . ($Language[8] ?? 'Inactive') . '
                    </fieldset>
                    <fieldset>
                        <legend><font color="red">*</font> ' . ($Language[4] ?? 'Currency') . '</legend>
                        <input type="text" class="bginput" name="currency" value="' . escape_attr($currency) . '" size="60" dir="ltr" tabindex="7" /><br /><font color="red">*</font><small>' . ($Language[18] ?? 'Required') . '</small>
                    </fieldset>
                </td>
                <td class="alt1" width="50%" valign="top">';
        
        if ($API['method'] === 'paymentwall') {
            echo '<fieldset>
                        <legend>Application Key</legend>
                        <input type="text" class="bginput" name="secretkey" value="' . escape_attr($secretkey) . '" size="60" dir="ltr" tabindex="7" />
                    </fieldset>
                    <fieldset>
                        <legend>Secret Key</legend>
                        <input type="text" class="bginput" name="secretkey2" value="' . escape_attr($secretkey2) . '" size="60" dir="ltr" tabindex="7" />
                    </fieldset>
                    <fieldset>
                        <legend>Widget</legend>
                        <input type="text" class="bginput" name="widget" value="' . escape_attr($widget) . '" size="60" dir="ltr" tabindex="7" />
                    </fieldset>';
        } else {
            echo '<fieldset>
                        <legend>' . str_replace('{1}', $title, trim($Language[10] ?? 'Email')) . '</legend>
                        <input type="text" class="bginput" name="email" value="' . escape_attr($email) . '" size="60" dir="ltr" tabindex="7" />
                        <div><small>' . str_replace('{1}', $title, trim($Language[11] ?? 'Email help')) . '</small></div>
                    </fieldset>
                    <fieldset>
                        <legend>' . str_replace('{1}', $title, trim($Language[12] ?? 'Secret Key')) . '</legend>
                        <input type="text" class="bginput" name="secretkey" value="' . escape_attr($secretkey) . '" size="60" dir="ltr" tabindex="7" />
                        <div><small>' . str_replace('{1}', $title, trim($Language[13] ?? 'Secret key help')) . '</small></div>
                    </fieldset>';
        }
        
        echo '</td>
            </tr>
            <tr>
                <td class="tcat2"></td>
                <td class="tcat2">
                    <input type="submit" value="' . ($Language[15] ?? 'Submit') . '" /> <input type="reset" value="' . ($Language[16] ?? 'Reset') . '" />
                </td>
            </tr>
        </table>
        </form>';
    } else {
        $Act = '';
    }
}

if (empty($Act)) {
    $List = '';
    $stmt = $TSDatabase->query('SELECT * FROM ts_subscriptions_api');
    
    if ($stmt) {
        while ($API = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $List .= '<tr>
                <td class="alt1">' . escape_html($API['title']) . '</td>
                <td class="alt1">' . escape_html($API['currency']) . '</td>
                <td class="alt1">' . ($API['active'] === '1' ? ($Language[7] ?? 'Active') : ($Language[8] ?? 'Inactive')) . '</td>
                <td class="alt1">
                    <a href="index.php?do=payment_api_manager&amp;act=edit&amp;aid=' . (int)$API['aid'] . '">' . trim($Language[9] ?? 'Edit') . '</a>
                </td>
            </tr>';
        }
    }
    
    echo '<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
        <tr>
            <td class="tcat" colspan="6" align="center">' . ($Language[2] ?? 'Payment API Manager') . '</td>
        </tr>
        <tr>
            <td class="alt2">' . ($Language[3] ?? 'Title') . '</td>
            <td class="alt2">' . ($Language[4] ?? 'Currency') . '</td>
            <td class="alt2">' . ($Language[5] ?? 'Status') . '</td>
            <td class="alt2">' . ($Language[6] ?? 'Options') . '</td>
        </tr>
        ' . $List . '
    </table>';
}
