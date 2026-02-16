<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

check_staff_auth();

$lang = load_staff_language('subscription_manager');
$db = get_pdo();
$message = '';
$act = $_GET['act'] ?? $_POST['act'] ?? '';

$title = '';
$description = '';
$active = '1';
$disporder = '1';
$usergroup = '0';
$seedbonus = '';
$invites = '';
$uploaded = '';
$cost = '';
$currency = 'usd';
$length = '';
$lengthtype = '';

try {
    if ($act === 'delete' && isset($_GET['sid'])) {
        verify_csrf_token();
        $sid = intval($_GET['sid']);
        $stmt = $db->prepare("DELETE FROM ts_subscriptions WHERE sid = ?");
        $stmt->execute([$sid]);
        $act = '';
    }
    
    if ($act === 'edit' && isset($_GET['sid'])) {
        $sid = intval($_GET['sid']);
        $stmt = $db->prepare("SELECT * FROM ts_subscriptions WHERE sid = ?");
        $stmt->execute([$sid]);
        
        if ($stmt->rowCount() > 0) {
            $sub = $stmt->fetch(PDO::FETCH_ASSOC);
            $title = $sub['title'];
            $description = $sub['description'];
            $active = $sub['active'];
            $disporder = $sub['disporder'];
            $usergroup = $sub['usergroup'];
            $seedbonus = $sub['seedbonus'];
            $invites = $sub['invites'];
            $uploaded = $sub['uploaded'];
            $cost = $sub['cost'];
            $currency = $sub['currency'];
            $length = $sub['length'];
            $lengthtype = $sub['lengthtype'];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                verify_csrf_token();
                
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $active = intval($_POST['active'] ?? 0);
                $disporder = intval($_POST['disporder'] ?? 0);
                $usergroup = intval($_POST['usergroup'] ?? 0);
                $seedbonus = intval($_POST['seedbonus'] ?? 0);
                $invites = intval($_POST['invites'] ?? 0);
                $uploaded = intval($_POST['uploaded'] ?? 0);
                $cost = trim($_POST['cost'] ?? '');
                $currency = trim($_POST['currency'] ?? 'usd');
                $length = intval($_POST['length'] ?? 0);
                $lengthtype = trim($_POST['lengthtype'] ?? 'weeks');
                
                $requiredFields = ['title', 'cost', 'currency', 'length', 'lengthtype'];
                $allFilled = true;
                foreach ($requiredFields as $field) {
                    if (empty(${$field})) {
                        $allFilled = false;
                        break;
                    }
                }
                
                if (!$allFilled) {
                    $message = show_alert_error($lang[32]);
                } else {
                    $stmt = $db->prepare("UPDATE ts_subscriptions SET title = ?, description = ?, active = ?, disporder = ?, usergroup = ?, seedbonus = ?, invites = ?, uploaded = ?, cost = ?, currency = ?, length = ?, lengthtype = ? WHERE sid = ?");
                    $stmt->execute([$title, $description, $active, $disporder, $usergroup, $seedbonus, $invites, $uploaded, $cost, $currency, $length, $lengthtype, $sid]);
                    redirect_to('index.php?do=subscription_manager');
                    exit;
                }
            }
            
            $showUsergroups = '<select name="usergroup"><option value="0">' . escape_html($lang[25]) . '</option>';
            $stmt = $db->query("SELECT gid, title FROM usergroups WHERE isbanned = 'no' AND gid > 0 ORDER by disporder");
            while ($ug = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = $usergroup == $ug['gid'] ? ' selected="selected"' : '';
                $showUsergroups .= '<option value="' . escape_attr((string)$ug['gid']) . '"' . $selected . '>' . escape_html($ug['title']) . '</option>';
            }
            $showUsergroups .= '</select>';
            
            echo '
        <form action="index.php?do=subscription_manager&act=edit&sid=' . escape_attr((string)$sid) . '" method="post">
        ' . generate_csrf_token() . '
        ' . $message . '
        <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
            <tr>
                <td class="tcat" align="center" colspan="2">
                    ' . escape_html($lang[34]) . '
                </td>
            </tr>
            <tr>
                <td class="alt1" width="50%" valign="top">
                    <fieldset>
                        <legend>* ' . escape_html($lang[3]) . '</legend>
                        <input type="text" class="bginput" name="title" value="' . escape_attr($title) . '" size="60" dir="ltr" tabindex="1" />
                    </fieldset>
                    <fieldset>
                        <legend>' . escape_html($lang[13]) . '</legend>
                        <input type="text" class="bginput" name="description" value="' . escape_attr($description) . '" size="60" dir="ltr" tabindex="2" />
                    </fieldset>
                    <fieldset>
                        <legend>' . escape_html($lang[4]) . '</legend>
                        <input type="radio" name="active" value="1"' . ($active == '1' ? ' checked="checked"' : '') . ' /> ' . escape_html($lang[23]) . '
                        <input type="radio" name="active" value="0"' . ($active == '0' ? ' checked="checked"' : '') . ' /> ' . escape_html($lang[24]) . '
                    </fieldset>
                    <fieldset>
                        <legend>' . escape_html($lang[7]) . '</legend>
                        <input type="text" class="bginput" name="disporder" value="' . escape_attr((string)$disporder) . '" size="6" dir="ltr" tabindex="4" />
                    </fieldset>
                </td>
                <td class="alt1" width="50%" valign="top">
                    <fieldset>
                        <legend>' . escape_html($lang[14]) . '</legend>
                        ' . $showUsergroups . '
                    </fieldset>
                    <fieldset>
                        <legend>' . escape_html($lang[15]) . '</legend>
                        <input type="text" class="bginput" name="seedbonus" value="' . escape_attr($seedbonus) . '" size="10" dir="ltr" tabindex="5" />
                    </fieldset>
                    <fieldset>
                        <legend>' . escape_html($lang[16]) . '</legend>
                        <input type="text" class="bginput" name="invites" value="' . escape_attr($invites) . '" size="10" dir="ltr" tabindex="6" />
                    </fieldset>
                    <fieldset>
                        <legend>' . escape_html($lang[17]) . '</legend>
                        <input type="text" class="bginput" name="uploaded" value="' . escape_attr($uploaded) . '" size="10" dir="ltr" tabindex="7" /> ' . escape_html($lang[26]) . '
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td class="alt1" width="50%">
                    <fieldset>
                        <legend>* ' . escape_html($lang[27]) . '</legend>
                        <input type="text" class="bginput" name="cost" value="' . escape_attr($cost) . '" size="10" dir="ltr" tabindex="7" />
                        ' . build_currency_select($currency, $db) . '
                    </fieldset>
                </td>
                <td class="alt1" width="50%">
                    <fieldset>
                        <legend>* ' . escape_html($lang[18]) . '</legend>
                        <input type="text" class="bginput" name="length" value="' . escape_attr($length) . '" size="10" dir="ltr" tabindex="7" />
                        <select name="lengthtype">
                            <option value="days"' . ($lengthtype == 'days' ? ' selected="selected"' : '') . '>' . escape_html($lang[19]) . '</option>
                            <option value="weeks"' . ($lengthtype == 'weeks' ? ' selected="selected"' : '') . '>' . escape_html($lang[20]) . '</option>
                            <option value="months"' . ($lengthtype == 'months' ? ' selected="selected"' : '') . '>' . escape_html($lang[21]) . '</option>
                            <option value="years"' . ($lengthtype == 'years' ? ' selected="selected"' : '') . '>' . escape_html($lang[22]) . '</option>
                        </select>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td class="tcat2"></td>
                <td class="tcat2">
                    <input type="submit" value="' . escape_attr($lang[29]) . '" /> <input type="reset" value="' . escape_attr($lang[30]) . '" /> ' . escape_html($lang[31]) . '
                </td>
            </tr>
        </table>
        </form>
        ';
        } else {
            $act = '';
        }
    }
    
    if ($act === 'new') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf_token();
            
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $active = intval($_POST['active'] ?? 0);
            $disporder = intval($_POST['disporder'] ?? 0);
            $usergroup = intval($_POST['usergroup'] ?? 0);
            $seedbonus = intval($_POST['seedbonus'] ?? 0);
            $invites = intval($_POST['invites'] ?? 0);
            $uploaded = intval($_POST['uploaded'] ?? 0);
            $cost = trim($_POST['cost'] ?? '');
            $currency = trim($_POST['currency'] ?? 'usd');
            $length = intval($_POST['length'] ?? 0);
            $lengthtype = trim($_POST['lengthtype'] ?? 'weeks');
            
            $requiredFields = ['title', 'cost', 'currency', 'length', 'lengthtype'];
            $allFilled = true;
            foreach ($requiredFields as $field) {
                if (empty(${$field})) {
                    $allFilled = false;
                    break;
                }
            }
            
            if (!$allFilled) {
                $message = show_alert_error($lang[32]);
            } else {
                $stmt = $db->prepare("INSERT INTO ts_subscriptions (title, description, active, disporder, usergroup, seedbonus, invites, uploaded, cost, currency, length, lengthtype) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $active, $disporder, $usergroup, $seedbonus, $invites, $uploaded, $cost, $currency, $length, $lengthtype]);
                redirect_to('index.php?do=subscription_manager');
                exit;
            }
        }
        
        $showUsergroups = '<select name="usergroup"><option value="0">' . escape_html($lang[25]) . '</option>';
        $stmt = $db->query("SELECT gid, title FROM usergroups WHERE isbanned = 'no' AND gid > 0 ORDER by disporder");
        while ($ug = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $selected = $usergroup == $ug['gid'] ? ' selected="selected"' : '';
            $showUsergroups .= '<option value="' . escape_attr((string)$ug['gid']) . '"' . $selected . '>' . escape_html($ug['title']) . '</option>';
        }
        $showUsergroups .= '</select>';
        
        echo '
    <form action="index.php?do=subscription_manager&act=new" method="post">
    ' . generate_csrf_token() . '
    ' . $message . '
    <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
        <tr>
            <td class="tcat" align="center" colspan="2">
                ' . escape_html($lang[12]) . '
            </td>
        </tr>
        <tr>
            <td class="alt1" width="50%" valign="top">
                <fieldset>
                    <legend>* ' . escape_html($lang[3]) . '</legend>
                    <input type="text" class="bginput" name="title" value="' . escape_attr($title) . '" size="60" dir="ltr" tabindex="1" />
                </fieldset>
                <fieldset>
                    <legend>' . escape_html($lang[13]) . '</legend>
                    <input type="text" class="bginput" name="description" value="' . escape_attr($description) . '" size="60" dir="ltr" tabindex="2" />
                </fieldset>
                <fieldset>
                    <legend>' . escape_html($lang[4]) . '</legend>
                    <input type="radio" name="active" value="1"' . ($active == '1' ? ' checked="checked"' : '') . ' /> ' . escape_html($lang[23]) . '
                    <input type="radio" name="active" value="0"' . ($active == '0' ? ' checked="checked"' : '') . ' /> ' . escape_html($lang[24]) . '
                </fieldset>
                <fieldset>
                    <legend>' . escape_html($lang[7]) . '</legend>
                    <input type="text" class="bginput" name="disporder" value="' . escape_attr((string)$disporder) . '" size="6" dir="ltr" tabindex="4" />
                </fieldset>
            </td>
            <td class="alt1" width="50%" valign="top">
                <fieldset>
                    <legend>' . escape_html($lang[14]) . '</legend>
                    ' . $showUsergroups . '
                </fieldset>
                <fieldset>
                    <legend>' . escape_html($lang[15]) . '</legend>
                    <input type="text" class="bginput" name="seedbonus" value="' . escape_attr($seedbonus) . '" size="10" dir="ltr" tabindex="5" />
                </fieldset>
                <fieldset>
                    <legend>' . escape_html($lang[16]) . '</legend>
                    <input type="text" class="bginput" name="invites" value="' . escape_attr($invites) . '" size="10" dir="ltr" tabindex="6" />
                </fieldset>
                <fieldset>
                    <legend>' . escape_html($lang[17]) . '</legend>
                    <input type="text" class="bginput" name="uploaded" value="' . escape_attr($uploaded) . '" size="10" dir="ltr" tabindex="7" /> ' . escape_html($lang[26]) . '
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="alt1" width="50%">
                <fieldset>
                    <legend>* ' . escape_html($lang[27]) . '</legend>
                    <input type="text" class="bginput" name="cost" value="' . escape_attr($cost) . '" size="10" dir="ltr" tabindex="7" />
                    ' . build_currency_select($currency, $db) . '
                </fieldset>
            </td>
            <td class="alt1" width="50%">
                <fieldset>
                    <legend>* ' . escape_html($lang[18]) . '</legend>
                    <input type="text" class="bginput" name="length" value="' . escape_attr($length) . '" size="10" dir="ltr" tabindex="7" />
                    <select name="lengthtype">
                        <option value="days"' . ($lengthtype == 'days' ? ' selected="selected"' : '') . '>' . escape_html($lang[19]) . '</option>
                        <option value="weeks"' . ($lengthtype == 'weeks' ? ' selected="selected"' : '') . '>' . escape_html($lang[20]) . '</option>
                        <option value="months"' . ($lengthtype == 'months' ? ' selected="selected"' : '') . '>' . escape_html($lang[21]) . '</option>
                        <option value="years"' . ($lengthtype == 'years' ? ' selected="selected"' : '') . '>' . escape_html($lang[22]) . '</option>
                    </select>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="tcat2"></td>
            <td class="tcat2">
                <input type="submit" value="' . escape_attr($lang[29]) . '" /> <input type="reset" value="' . escape_attr($lang[30]) . '" /> ' . escape_html($lang[31]) . '
            </td>
        </tr>
    </table>
    </form>
    ';
    }
    
    if (empty($act)) {
        $list = '';
        $stmt = $db->query("SELECT * FROM ts_subscriptions ORDER BY disporder");
        if ($stmt->rowCount() > 0) {
            while ($sub = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lengthLabel = $sub['lengthtype'] == 'days' ? $lang[19] : ($sub['lengthtype'] == 'weeks' ? $lang[20] : ($sub['lengthtype'] == 'months' ? $lang[21] : $lang[22]));
                $list .= '
            <tr>
                <td class="alt1">
                    ' . escape_html($sub['title']) . '
                </td>
                <td class="alt1">
                    ' . ($sub['active'] == '1' ? escape_html($lang[23]) : escape_html($lang[24])) . '
                </td>
                <td class="alt1">
                    ' . escape_html($sub['cost']) . ' ' . escape_html($sub['currency']) . '
                </td>
                <td class="alt1">
                    ' . escape_html($sub['length']) . ' ' . escape_html($lengthLabel) . '
                </td>
                <td class="alt1">
                    ' . intval($sub['disporder']) . '
                </td>
                <td class="alt1">
                    <a href="index.php?do=subscription_manager&amp;act=edit&amp;sid=' . escape_attr((string)$sub['sid']) . '">' . escape_html(trim($lang[9])) . '</a> - <a href="index.php?do=subscription_manager&amp;act=delete&amp;sid=' . escape_attr((string)$sub['sid']) . '&amp;csrf_token=' . escape_attr(generate_csrf_token(false)) . '">' . escape_html(trim($lang[10])) . '</a>
                </td>
            </tr>
            ';
            }
        }
        
        echo '
    ' . show_alert_message('<span style="float: right;">' . escape_html(trim($lang[33])) . ' </span> <a href="index.php?do=subscription_manager&act=new">' . escape_html(trim($lang[12])) . '</a> ') . '
    <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
        <tr>
            <td class="tcat" colspan="6" align="center">
                ' . escape_html($lang[2]) . '
            </td>
        </tr>
        <tr>
            <td class="alt2">' . escape_html($lang[3]) . '</td>
            <td class="alt2">' . escape_html($lang[4]) . '</td>
            <td class="alt2">' . escape_html($lang[27]) . '</td>
            <td class="alt2">' . escape_html($lang[18]) . '</td>
            <td class="alt2">' . escape_html($lang[7]) . '</td>
            <td class="alt2">' . escape_html($lang[8]) . '</td>
        </tr>
        ' . $list . '
    </table>
    ';
    }
} catch (Exception $e) {
    error_log('Subscription Manager Error: ' . $e->getMessage());
    echo show_alert_error('An error occurred. Please try again.');
}

function build_currency_select(string $selected, PDO $db): string {
    $stmt = $db->query("SELECT currency FROM ts_subscriptions_api");
    $currencies = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $currencies[] = $row['currency'];
    }
    
    $html = '<select name="currency">';
    $usedCurrencies = [];
    foreach ($currencies as $currencyList) {
        $currencyArray = explode(',', $currencyList);
        foreach ($currencyArray as $currency) {
            $currency = trim($currency);
            if (!in_array($currency, $usedCurrencies) && $currency !== '') {
                $usedCurrencies[] = $currency;
                $selectedAttr = $selected == $currency ? ' selected="selected"' : '';
                $html .= '<option value="' . escape_attr($currency) . '"' . $selectedAttr . '>' . escape_html(strtoupper($currency)) . '</option>';
            }
        }
    }
    $html .= '</select>';
    return $html;
}
