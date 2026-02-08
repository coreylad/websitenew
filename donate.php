<?php
define("CSRF_PROTECTION", true);
define("D_VERSION", "2.3.1 by xam");
define("DISABLE_ADS", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "donate.php");
require "./global.php";
$lang->load("donate");
$Act = isset($_GET["act"]) ? $_GET["act"] : (isset($_POST["act"]) ? $_POST["act"] : "");
$TSSEConfig->TSLoadConfig("PAYPAL");
if ($Act == "thanks") {
    stdhead($lang->donate["title"] . " - " . $lang->donate["thanks"]);
    echo "\r\n\t<table $width = \"100%\" $align = \"center\" $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . $lang->donate["thanks2"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t" . $lang->donate["thanks"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t";
    stdfoot();
    exit;
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $Act == "order") {
    $Sid = isset($_GET["sid"]) ? intval($_GET["sid"]) : (isset($_POST["sid"]) ? intval($_POST["sid"]) : "");
    if ($Sid) {
        $Query = sql_query("SELECT * FROM ts_subscriptions WHERE $sid = \"" . $Sid . "\" AND $active = 1");
        if (mysqli_num_rows($Query)) {
            $Sub = mysqli_fetch_assoc($Query);
            $Query = sql_query("SELECT * FROM ts_subscriptions_api WHERE $active = 1");
            if (mysqli_num_rows($Query)) {
                $Item_number = substr(md5((isset($CURUSER["id"]) && 0 < $CURUSER["id"] ? $CURUSER["id"] : time()) . $Sub["title"]), 0, 30);
                sql_query("DELETE FROM ts_subscriptions_payments WHERE `uid` = " . sqlesc(isset($CURUSER["id"]) ? $CURUSER["id"] : 0) . " AND $item = '" . $Item_number . "' AND $completed = 0");
                sql_query("INSERT INTO ts_subscriptions_payments (sid, uid, item, added) VALUES ('" . $Sid . "', '" . (isset($CURUSER["id"]) ? $CURUSER["id"] : 0) . "', '" . $Item_number . "', '" . TIMENOW . "')") || sqlerr(__FILE__, 170);
                $ShowAPI = "";
                while ($API = mysqli_fetch_assoc($Query)) {
                    $ShowAPI .= "\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . sprintf($lang->donate["order"], $API["title"]) . "</legend>\r\n\t\t\t\t\t\t" . sprintf($lang->donate["order2"], $API["title"]) . "\r\n\t\t\t\t\t\t" . generateapiform($API, $Sub, $Item_number) . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t";
                }
                stdhead($lang->donate["title"] . " - " . sprintf($lang->donate["order4"], $Sub["title"]));
                echo "\r\n\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\">" . ts_collapse("title") . sprintf($lang->donate["order4"], $Sub["title"]) . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t" . ts_collapse("title", 2) . "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"subheader\">\r\n\t\t\t\t\t\t\t\t" . $lang->donate["order3"] . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t" . $lang->donate["slength"] . " <b>" . $Sub["length"] . " " . ($Sub["lengthtype"] == "days" ? $lang->donate["days"] : ($Sub["lengthtype"] == "weeks" ? $lang->donate["weeks"] : ($Sub["lengthtype"] == "months" ? $lang->donate["months"] : $lang->donate["years"]))) . "</b> - " . $lang->donate["cost"] . " <b>" . $Sub["cost"] . " " . strtoupper($Sub["currency"]) . "</b>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t" . $ShowAPI . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</tbody>\r\n\t\t\t\t</table>";
                stdfoot();
                exit;
            }
            stderr($lang->global["error"], $lang->donate["error2"]);
        }
    }
}
$CachedUsergroups = [];
$Query = sql_query("SELECT gid, title, namestyle FROM usergroups");
while ($UG = mysqli_fetch_assoc($Query)) {
    if ($UG["gid"]) {
        $CachedUsergroups[$UG["gid"]] = get_user_color($UG["title"], $UG["namestyle"]);
    } else {
        $CachedUsergroups[$UG["gid"]] = "N/A";
    }
}
$Query = sql_query("SELECT * FROM ts_subscriptions WHERE $active = 1 ORDER BY disporder");
if (mysqli_num_rows($Query) == 0) {
    $AvailableOptions = "<tr><td>" . sprintf($lang->donate["error"], $SITENAME) . "</td></tr>";
} else {
    $Count = 0;
    for ($AvailableOptions = "<tr>"; $Sub = mysqli_fetch_assoc($Query); $Count++) {
        if ($Count % 3 == 0) {
            $AvailableOptions .= "\r\n\t\t\t</tr>\r\n\t\t\t<tr>";
        }
        $AvailableOptions .= "\r\n\t\t\t\t<td class=\"none\" $valign = \"top\">\r\n\t\t\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = order\">\r\n\t\t\t\t\t<input $type = \"hidden\" $name = \"sid\" $value = \"" . $Sub["sid"] . "\" />\r\n\t\t\t\t\t<input $type = \"hidden\" $name = \"securitytoken\" $value = \"" . (isset($CURUSER["securitytoken"]) ? $CURUSER["securitytoken"] : "") . "\" />\r\n\t\t\t\t\t<table $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\" $width = \"290\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"subheader\">\r\n\t\t\t\t\t\t\t\t" . $Sub["title"] . "<br />\r\n\t\t\t\t\t\t\t\t<small>" . $Sub["description"] . "</small>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $height = \"100\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t<div $align = \"justify\" $style = \"padding-top: 4\tpx;\">\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $pic_base_url . "donate/clock_go.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->donate["slength"] . "\" $title = \"" . $lang->donate["slength"] . "\" /> " . $lang->donate["slength"] . "<b>" . $Sub["length"] . " " . ($Sub["lengthtype"] == "days" ? $lang->donate["days"] : ($Sub["lengthtype"] == "weeks" ? $lang->donate["weeks"] : ($Sub["lengthtype"] == "months" ? $lang->donate["months"] : $lang->donate["years"]))) . "</b><br />\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $pic_base_url . "donate/group_go.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->donate["usergroup"] . "\" $title = \"" . $lang->donate["usergroup"] . "\" /> " . $lang->donate["usergroup"] . " <b>" . (isset($CachedUsergroups[$Sub["usergroup"]]) ? $CachedUsergroups[$Sub["usergroup"]] : "--") . "</b><br />\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $pic_base_url . "donate/wand.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->donate["bonus"] . "\" $title = \"" . $lang->donate["bonus"] . "\" /> " . $lang->donate["bonus"] . " <b>" . ts_nf($Sub["seedbonus"]) . "</b><br />\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $pic_base_url . "donate/wand.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->donate["invites"] . "\" $title = \"" . $lang->donate["invites"] . "\" /> " . $lang->donate["invites"] . " <b>" . ts_nf($Sub["invites"]) . "</b><br />\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $pic_base_url . "donate/wand.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->donate["upload"] . "\" $title = \"" . $lang->donate["upload"] . "\" /> " . $lang->donate["upload"] . " <b>" . mksize($Sub["uploaded"] * 1024 * 1024 * 1024) . "</b>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $valign = \"bottom\">\r\n\t\t\t\t\t\t\t\t<div $style = \"float: right;\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->donate["purchase"] . "\" class=\"button\" />\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<div $style = \"float: left;\">\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $pic_base_url . "donate/money.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->donate["cost"] . "\" $title = \"" . $lang->donate["cost"] . "\" /> <b>" . $lang->donate["cost"] . " " . $Sub["cost"] . " " . strtoupper($Sub["currency"]) . "</b>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t\t</form>\r\n\t\t\t\t</td>\r\n\t\t";
    }
    $AvailableOptions .= "\r\n\t\t</tr>";
}
$AdminNotice = "";
if (!empty($wire_form) && 0 < strlen(strip_tags($wire_form))) {
    $AdminNotice = "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">" . ts_collapse("AdminNotice") . $lang->donate["adminnote"] . "</td>\r\n\t\t</tr>\r\n\t\t\t" . ts_collapse("AdminNotice", 2) . "\r\n\t\t<tr>\r\n\t\t\t<td $valign = \"top\">\r\n\t\t\t\t" . $wire_form . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t<br />\r\n";
}
stdhead($lang->donate["title"]);
echo $AdminNotice . "\r\n<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t<tr>\r\n\t\t<td class=\"thead\">" . ts_collapse("title") . $lang->donate["title2"] . "</td>\r\n\t</tr>\r\n\t" . ts_collapse("title", 2) . "\r\n\t\t<tr>\r\n\t\t\t<td $valign = \"top\">\r\n\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t" . $AvailableOptions . "\r\n\t\t\t\t</table>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</tbody>\r\n</table>";
stdfoot();
function generateSignature($params, $secret)
{
    ksort($params);
    $baseString = "";
    foreach ($params as $key => $value) {
        $baseString .= $key . "=" . $value;
    }
    $baseString .= $secret;
    return md5($baseString);
}
function GenerateAPIForm($API, $Sub, $Item_number)
{
    global $lang;
    global $BASEURL;
    global $CURUSER;
    global $paypal_demo_mode;
    global $SITENAME;
    $FormHTML = "";
    strtolower($API["method"]);
    switch (strtolower($API["method"])) {
        case "paymentwall":
            $params = ["key" => $API["secretkey"], "widget" => $API["widget"], "sign_version" => 2, "email" => $CURUSER["email"], "uid" => $CURUSER["id"], "success_url" => $BASEURL . "/donate.php?$act = thanks", "pingback_url" => $BASEURL . "/payment_gateway.php?$method = paymentwall", "amount" => $Sub["cost"], "currencyCode" => strtoupper($Sub["currency"]), "ag_name" => $Sub["title"], "ag_external_id" => $Item_number, "ag_type" => "fixed"];
            $params["sign"] = generatesignature($params, $API["secretkey2"]);
            $url = "https://wallapi.com/api/subscription";
            $par = http_build_query($params);
            $FormHTML = "\r\n\t\t\t<form $method = \"POST\" $action = \"" . $url . "?" . $par . "\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . sprintf($lang->donate["order"], $API["title"]) . "\" class=\"submit\" />\r\n\t\t\t</form>";
            break;
        case "paypal":
            $FormHTML = "\r\n\t\t\t<form $action = \"https://www." . ($paypal_demo_mode == "yes" ? "sandbox." : "") . "paypal.com/cgi-bin/webscr\" $method = \"post\" $name = \"paypal\">\r\n\t\t\t\t<input $type = \"hidden\" $name = \"cmd\" $value = \"_xclick\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"business\" $value = \"" . $API["email"] . "\" />\r\n\t\t\t\t\r\n\t\t\t\t<input $type = \"hidden\" $name = \"item_name\" $value = \"" . $Sub["title"] . " " . $lang->donate["sub"] . "\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"item_number\" $value = \"" . $Item_number . "\" />\r\n\t\t\t\t\r\n\t\t\t\t<input $type = \"hidden\" $name = \"currency_code\" $value = \"" . strtoupper($Sub["currency"]) . "\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"amount\" $value = \"" . $Sub["cost"] . "\" />\r\n\t\t\t\t\r\n\t\t\t\t<input $type = \"hidden\" $name = \"no_shipping\" $value = \"1\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"shipping\" $value = \"0.00\" />\r\n\t\t\t\t\r\n\t\t\t\t<input $type = \"hidden\" $name = \"return\" $value = \"" . $BASEURL . "/donate.php?$act = thanks\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"cancel_return\" $value = \"" . $BASEURL . "/donate.php\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"notify_url\" $value = \"" . $BASEURL . "/payment_gateway.php?$method = paypal\" />\t\t\t\t\r\n\t\t\t\t<input $type = \"hidden\" $name = \"rm\" $value = \"2\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"cbt\" $value = \"" . sprintf($lang->donate["return_to_x"], $SITENAME) . "\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"custom\" $value = \"" . $CURUSER["username"] . "\" />\r\n\t\t\t\t\r\n\t\t\t\t<input $type = \"hidden\" $name = \"no_note\" $value = \"1\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"tax\" $value = \"0.00\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"quantity\" $value = \"1\" />\r\n\r\n\t\t\t\t<input $type = \"submit\" class=\"button\" $style = \"font-weight:normal\" $value = \"" . sprintf($lang->donate["order"], $API["title"]) . "\" />\r\n\t\t\t</form>\r\n\t\t\t";
            break;
        case "daopay":
            $FormHTML = "\r\n\t\t\t<form $action = \"https://daopay.com/payment/?\" $method = \"get\">\r\n\t\t\t\t<input $type = \"hidden\" $name = \"appcode\" $value = \"" . $API["secretkey"] . "\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"prodcode\" $value = \"" . $Sub["title"] . "\" />\r\n\t\t\t\t<input $type = \"submit\" class=\"button\" $style = \"font-weight:normal\" $value = \"" . sprintf($lang->donate["order"], $API["title"]) . "\" />\r\n\t\t\t</form>\r\n\t\t\t";
            break;
        default:
            return $FormHTML;
    }
}

?>