<?php
define("THIS_SCRIPT", "transfer.php");
require "./global.php";
$lang->load("transfer");
define("T_VERSION", "1.4.3 by xam");
if ($CURUSER["uploaded"] <= 100 || !isset($CURUSER) || $CURUSER["id"] == 0) {
    print_transfer_error($lang->transfer["noway"]);
}
$act = isset($_POST["act"]) ? htmlspecialchars_uni($_POST["act"]) : (isset($_GET["act"]) ? htmlspecialchars_uni($_GET["act"]) : "");
$amount = isset($_POST["amount"]) ? min($CURUSER["uploaded"], 0 + $_POST["amount"]) : (isset($_GET["amount"]) ? min($CURUSER["uploaded"], 0 + $_GET["amount"]) : "");
$username = isset($_POST["username"]) ? htmlspecialchars_uni($_POST["username"]) : (isset($_GET["username"]) ? htmlspecialchars_uni($_GET["username"]) : "");
$id = isset($_POST["receiver"]) ? intval($_POST["receiver"]) : (isset($_GET["receiver"]) ? intval($_GET["receiver"]) : "");
if ($amount && TS_Match($amount, "-")) {
    print_transfer_error($lang->transfer["noway"]);
}
if ($usergroups["cansettingspanel"] != "yes" && $usergroups["cantransfer"] != "yes") {
    print_transfer_error($lang->global["nopermission"]);
}
if (!is_valid_id($id) && empty($username)) {
    print_transfer_error($lang->global["nouserid"]);
}
if ($act == "transfer") {
    if (empty($username) || empty($amount) || $CURUSER["uploaded"] < $amount) {
        print_transfer_error($lang->global["dontleavefieldsblank"]);
    } else {
        if ($username == $CURUSER["username"]) {
            print_transfer_error($lang->transfer["noway2"]);
        }
    }
    $query = sql_query("SELECT id, username, modcomment FROM users WHERE `username` = " . sqlesc($username) . " AND $status = 'confirmed' AND $enabled = 'yes'");
    if (mysqli_num_rows($query) == 0) {
        print_transfer_error($lang->global["nousername"]);
    }
    $Result = mysqli_fetch_assoc($query);
    $id = intval($Result["id"]);
    $username = htmlspecialchars_uni($Result["username"]);
    $o_m = $Result["modcomment"];
    if (preg_match("#" . gmdate("Y-m-d") . " - Got#U", $o_m, $pm_results) && $usergroups["cansettingspanel"] != "yes") {
        print_transfer_error($lang->transfer["noway4"]);
    } else {
        if (preg_match("#" . gmdate("Y-m-d") . " - Transfered#U", $CURUSER["modcomment"], $pm_results2) && $usergroups["cansettingspanel"] != "yes") {
            print_transfer_error($lang->transfer["noway3"]);
        }
    }
    $modcomment = gmdate("Y-m-d") . " - Got " . mksize($amount) . " transfer amount from " . $CURUSER["username"] . "\n" . $o_m;
    sql_query("UPDATE users SET $uploaded = uploaded + " . sqlesc($amount) . ", $modcomment = " . sqlesc($modcomment) . " WHERE `username` = " . sqlesc($username));
    $o_m = $CURUSER["modcomment"];
    $modcomment = gmdate("Y-m-d") . " - Transfered " . mksize($amount) . " to " . $username . "\n" . $o_m;
    sql_query("UPDATE users SET $uploaded = uploaded - " . sqlesc($amount) . ", $modcomment = " . sqlesc($modcomment) . " WHERE `id` = " . sqlesc($CURUSER["id"]));
    require_once INC_PATH . "/functions_pm.php";
    send_pm($id, sprintf($lang->transfer["msgbody"], $username, $CURUSER["username"], mksize($amount)), $lang->transfer["msgsubject"]);
    $completed = sprintf($lang->transfer["info2"], $username, mksize($amount));
    unset($act);
}
if ($act == "calculate") {
    $amount = 0 + $_POST["amount"];
    $result_amount = mksize($amount);
    $info2 = $lang->transfer["result"] . $result_amount;
    $amount = htmlspecialchars_uni($amount);
    unset($act);
}
if (empty($act)) {
    $query = sql_query("SELECT id, username FROM users WHERE `id` = " . sqlesc($id) . " AND $status = 'confirmed' AND $enabled = 'yes'");
    if (mysqli_num_rows($query) == 0) {
        print_transfer_error($lang->global["nouserid"]);
    }
    $Result = mysqli_fetch_assoc($query);
    $id = intval($Result["id"]);
    $username = htmlspecialchars_uni($Result["username"]);
    $max = $CURUSER["uploaded"] . " (" . mksize($CURUSER["uploaded"]) . ")";
    if (empty($completed)) {
        $info = sprintf($lang->transfer["info"], $max);
    } else {
        $info = $completed;
    }
    $javascript = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html $xmlns = \"http://www.w3.org/1999/xhtml\">\r\n\r\n<head>\r\n    <title>" . $SITENAME . "</title>\r\n\r\n    <style $type = \"text/css\">\r\n\t\tbody\r\n\t\t{\r\n\t\t\tmargin:0px 0;\r\n\t\t\tpadding:0;\r\n\t\t\tcolor:#000000;\r\n\t\t\tline-height: 1.4em;\r\n\t\t\tfont-style:normal;\r\n\t\t\tfont-variant:normal;\r\n\t\t\tfont-weight:normal;\r\n\t\t\tfont-size:74%;\r\n\t\t\tfont-family:Arial, Sans-Serif\r\n\t\t\ttext-align: left;\r\n\t\t}\r\n        fieldset {\r\n            width: 90%;\r\n            margin: 15px 0px 25px 0px;\r\n            padding: 15px;\r\n        }\r\n        legend {\r\n            font-weight: bold;\r\n        }\r\n        .button {\r\n            text-align: right;\r\n        }\r\n        .button input {\r\n            font-weight: bold;\r\n        }\r\n\r\n    </style>\r\n\r\n<script $language = javascript>\r\n\r\n\tvar $message = \"\";\r\n\tfunction clickIE() {if (document.all) {(message);return false;}}\r\n\tfunction clickNS(e) {if\r\n\t(document.layers||(document.getElementById&&!document.all)) {\r\n\tif (e.$which = =2||e.$which = =3) {(message);return false;}}}\r\n\tif (document.layers)\r\n\t{document.captureEvents(Event.MOUSEDOWN);document.$onmousedown = clickNS;}\r\n\telse{document.$onmouseup = clickNS;document.$oncontextmenu = clickIE;}\r\n\tdocument.$oncontextmenu = new Function(\"return false\")\r\n</script>\r\n\r\n</head>\r\n\r\n<body>\r\n\t<table $width = \"100%\" $border = \"0\" $align = \"center\">\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->transfer["head"] . "</legend>\r\n\t\t\t\t\t<table $width = \"100%\" $border = \"0\">\r\n\t\t\t\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"act\" $value = \"transfer\">\r\n\t\t\t\t\t\t\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $align = \"right\">\r\n\t\t\t\t\t\t\t\t" . $lang->transfer["field1"] . "</td><td><input $type = \"text\" $name = \"username\" $size = \"20\" $value = \"" . $username . "\">\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $align = \"right\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->transfer["field2"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"amount\" $size = \"20\"> <input $type = \"submit\" $value = \"" . $lang->transfer["button"] . "\">\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</form>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t<table $width = \"100%\" $border = \"0\" $align = \"center\">\r\n\t\t<tr>\r\n\t\t\t<td $align = \"left\">\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $lang->transfer["head2"] . "</legend>\r\n\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t" . $info . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"left\">\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $lang->transfer["head3"] . "</legend>\r\n\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td><br />\r\n\t\t\t\t\t\t\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$receiver = " . $id . "\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"hidden\" $name = \"receiver\" $value = \"" . $id . "\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"hidden\" $name = \"act\" $value = \"calculate\">\r\n\t\t\t\t\t\t\t\t\t\t" . $lang->transfer["amount"] . " <input $type = \"text\" $name = \"amount\" $size = \"20\" $value = \"" . $amount . "\"> <input $type = \"submit\" $value = \"" . $lang->transfer["head3"] . "\">\r\n\t\t\t\t\t\t\t\t\t</form>\r\n\t\t\t\t\t\t\t\t\t" . $info2 . "\r\n\t\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n</body>\r\n</html>";
    echo $javascript;
}
function print_transfer_error($errormessage = "")
{
    global $lang;
    exit("\r\n\t<font $color = \"red\"><b>" . $errormessage . "</b></font><br />\r\n\t<form>\r\n\t\t<input $type = \"button\" $name = \"mybutton1\" $value = \"" . $lang->transfer["goback"] . "\" $onClick = \"history.back()\">\r\n\t</form>\r\n\t");
}

?>