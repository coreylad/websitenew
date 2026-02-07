<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("CSRF_PROTECTION", true);
define("UF_VERSION", "v.2.0 by xam");
define("THIS_SCRIPT", "uploaderform.php");
require "./global.php";
if (!isset($CURUSER)) {
    print_no_permission();
}
$lang->load("uploaderform");
$errors = [];
if ($usergroups["canupload"] == "yes") {
    stderr($lang->global["error"], $lang->uploaderform["error1"]);
}
if ($ul_form_enabled != "yes") {
    stderr($lang->global["error"], $lang->uploaderform["error4"]);
}
if (0 < intval($ul_form_min_upload) && $CURUSER["uploaded"] < $ul_form_min_upload * 1024 * 1024 * 1024) {
    stderr($lang->global["error"], sprintf($lang->uploaderform["error2"], mksize($ul_form_min_upload * 1024 * 1024 * 1024)));
}
if (0 < intval($ul_form_min_ratio)) {
    $UserRatio = 0 < $CURUSER["downloaded"] ? $CURUSER["uploaded"] / $CURUSER["downloaded"] : 0;
    if ($UserRatio < $ul_form_min_ratio) {
        stderr($lang->global["error"], sprintf($lang->uploaderform["error3"], $ul_form_min_ratio));
    }
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $content = isset($_POST["content"]) ? trim($_POST["content"]) : "";
    $why = isset($_POST["why"]) ? trim($_POST["why"]) : "";
    $uploadspeed = isset($_POST["uploadspeed"]) ? trim($_POST["uploadspeed"]) : "";
    $understand1 = isset($_POST["understand1"]) && $_POST["understand1"] == "yes" ? "yes" : "no";
    $understand2 = isset($_POST["understand2"]) && $_POST["understand2"] == "yes" ? "yes" : "no";
    if (!$content || !$why || !$uploadspeed) {
        $errors[] = $lang->uploaderform["error6"];
    }
    if ($understand1 != "yes" || $understand2 != "yes") {
        $errors[] = sprintf($lang->uploaderform["error5"], $SITENAME);
    }
    if (count($errors) == 0) {
        $msg = sprintf($lang->uploaderform["msgcontent"], "[URL=" . $BASEURL . "/userdetails.php?$id = " . $CURUSER["id"] . "]" . $CURUSER["username"] . "[/URL]", htmlspecialchars($content), htmlspecialchars($why), htmlspecialchars($uploadspeed));
        sql_query("INSERT INTO staffmessages (sender, added, msg, subject) VALUES ('" . $CURUSER["id"] . "', NOW(), " . sqlesc($msg) . ", " . sqlesc($lang->uploaderform["msgtitle"]) . ")");
        stderr($lang->uploaderform["done"], $lang->uploaderform["thanks"]);
        exit;
    }
}
require_once "./" . $cache . "/uploadspeed.php";
$UploadSpeed = "\r\n<select $name = \"uploadspeed\">";
foreach ($_uploadspeed as $uploadSpeedId => $uploadSpeedContent) {
    $UploadSpeed .= "\r\n\t<option $value = \"" . $uploadSpeedContent["name"] . "\">" . $uploadSpeedContent["name"] . "</option>";
}
$UploadSpeed .= "\r\n</select>";
stdhead($lang->uploaderform["title"]);
show_uploaderform_errors();
echo "\r\n<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n<input $type = \"hidden\" $name = \"securitytoken\" $value = \"" . $CURUSER["securitytoken"] . "\" />\r\n<table $width = \"100%\" $align = \"center\" $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\">\r\n\t<tr>\r\n\t\t<td class=\"thead\">\r\n\t\t\t" . $lang->uploaderform["title"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->uploaderform["option1"] . "</legend>\r\n\t\t\t\t<textarea $name = \"content\" $style = \"width: 95%; height: 35px;\"></textarea>\r\n\t\t\t</fieldset>\r\n\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->uploaderform["option2"] . "</legend>\r\n\t\t\t\t<textarea $name = \"why\" $style = \"width: 95%; height: 35px;\"></textarea>\r\n\t\t\t</fieldset>\r\n\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->uploaderform["option3"] . "</legend>\r\n\t\t\t\t" . $UploadSpeed . " <i>" . $lang->uploaderform["option3b"] . "</i>\r\n\t\t\t</fieldset>\r\n\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->uploaderform["option4"] . "</legend>\r\n\t\t\t\t<input $type = \"radio\" $name = \"understand1\" $value = \"no\" $checked = \"checked\" /> " . $lang->global["redno"] . "\r\n\t\t\t\t<input $type = \"radio\" $name = \"understand1\" $value = \"yes\" /> " . $lang->global["greenyes"] . "\r\n\t\t\t</fieldset>\r\n\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->uploaderform["option5"] . "</legend>\r\n\t\t\t\t<input $type = \"radio\" $name = \"understand2\" $value = \"no\" $checked = \"checked\" /> " . $lang->global["redno"] . "\r\n\t\t\t\t<input $type = \"radio\" $name = \"understand2\" $value = \"yes\" /> " . $lang->global["greenyes"] . "\r\n\t\t\t</fieldset>\r\n\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->uploaderform["option6"] . "</legend>\r\n\t\t\t\t<b><i>" . $lang->uploaderform["rules"] . "</i></b>\r\n\t\t\t</fieldset>\r\n\t\t</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t<input $type = \"submit\" $value = \"" . $lang->uploaderform["send"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->uploaderform["reset"] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
stdfoot();
function show_uploaderform_errors()
{
    global $errors;
    global $lang;
    if (0 < count($errors)) {
        $error = implode("<br />", $errors);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $error . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>