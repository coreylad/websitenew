<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "confirm.php");
require "./global.php";
include_once INC_PATH . "/functions_login.php";
cur_user_check();
define("C_VERSION", "1.2 by xam");
$lang->load("confirm");
$act = TS_Global("act");
if ($act == "manual") {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $_GET["id"] = $_POST["id"];
        $_GET["secret"] = $_POST["secret"];
    } else {
        $form = "\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = manual\">\n\t\t<input $type = \"hidden\" $name = \"act\" $value = \"manual\" />\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\n\t\t\t<tr>\n\t\t\t\t<td $colspan = \"2\" class=\"thead\">" . $lang->confirm["manual1"] . "</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td $colspan = \"2\" class=\"subheader\">" . $lang->confirm["manual4"] . "</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td $align = \"right\">" . $lang->confirm["manual2"] . "</td>\n\t\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"id\" $value = \"\" $size = \"32\" /></td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td $align = \"right\">" . $lang->confirm["manual3"] . "</td>\n\t\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"secret\" $value = \"\" $size = \"32\" /></td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t<td $colspan = \"2\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->confirm["manual5"] . "\" /></td>\n\t\t\t</tr>\n\t\t</table>\n\t\t</form>\n\t\t";
        stdhead($lang->confirm["manual1"]);
        echo $form;
        stdfoot();
        exit;
    }
}
$id = intval(TS_Global("id"));
$md5 = TS_Global("secret");
if (!is_valid_id($id)) {
    stderr($lang->global["error"], $lang->confirm["error1"]);
}
if (strlen($md5) != 32) {
    $md5 = preg_replace("#\\s+#", "", $md5);
    $md5 = urldecode($md5);
    if (strlen($md5) != 32) {
        stderr($lang->global["error"], $lang->confirm["error1"]);
    }
}
$res = sql_query("SELECT u.passhash, u.status, u.country, u.username, e.editsecret FROM users u INNER JOIN ts_user_validation e ON (u.`id` = e.userid) WHERE u.$enabled = 'yes' AND u.$id = " . sqlesc($id));
if (!mysqli_num_rows($res)) {
    stderr($lang->global["error"], $lang->global["dberror"]);
} else {
    $row = mysqli_fetch_assoc($res);
}
if ($row["status"] != "pending") {
    redirect("ok.php?$type = confirmed");
    exit;
}
if ($md5 != md5($row["editsecret"])) {
    stderr($lang->global["error"], $lang->confirm["error2"]);
}
sql_query("UPDATE users SET `status` = 'confirmed' WHERE `id` = " . sqlesc($id) . " AND $status = 'pending' AND $enabled = 'yes'");
if (!mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
    stderr($lang->global["error"], $lang->confirm["error3"]);
}
sql_query("DELETE FROM ts_user_validation WHERE `userid` = " . sqlesc($id));
$TSSEConfig->TSLoadConfig("SHOUTBOX");
if ($tsshoutbot == "yes" && TS_Match($tsshoutboxoptions, "newuser")) {
    if ($row["country"]) {
        $query = sql_query("SELECT name FROM countries WHERE `id` = " . sqlesc($row["country"]));
        if (mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $countryname = $Result["name"];
        }
    }
    if (!isset($countryname)) {
        $countryname = "--";
    }
    $username = $row["username"];
    $shoutbOT = sprintf($lang->confirm["shoutbOT"], $id, $username, $countryname, $BASEURL);
    require INC_PATH . "/functions_ajax_chatbot.php";
    TSAjaxShoutBOT($shoutbOT);
}
logincookie($id, $row["passhash"]);
redirect("ok.php?$type = confirm");

?>