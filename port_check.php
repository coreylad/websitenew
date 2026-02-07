<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "port_check.php");
require "./global.php";
$logphperrors = "none";
$GLOBALS["logphperrors"] = "none";
if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("port_check");
define("CC_VERSION", "0.2 by xam");
$message = false;
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $ipAddress = @long2ip(@ip2long(USERIPADDRESS));
    $portNumber = @intval($_POST["port"]);
    if ($ipAddress && $portNumber) {
        if (!($connectionResult = @checkPortConnection($ipAddress, $portNumber))) {
            $message = sprintf($lang->port_check["bad"], $portNumber);
        } else {
            $message = sprintf($lang->port_check["good"], $portNumber);
        }
    }
}
stdhead($lang->port_check["head"]);
$str = "\n<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\n\t<tr>\n\t\t<td class=\"thead\" $colspan = \"2\" $align = \"center\">" . $lang->port_check["head"] . "</td>\n\t</tr>\n";
if (!$message) {
    $str .= "\n\t\t<script $type = \"text/javascript\">\n\t\t\tfunction show_message()\n\t\t\t{\n\t\t\t\tdocument.getElementById('message1').style.$display = 'none';\n\t\t\t\tdocument.getElementById('message2').style.$display = 'block';\n\t\t\t}\n\t\t</script>\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\n\t\t<tr>\n\t\t\t<td $align = \"right\" $width = \"20%\">" . $lang->port_check["field1"] . "</td>\n\t\t\t<td $align = \"left\" $width = \"80%\">\n\t\t\t\t<div $id = \"message1\" $style = \"display: block;\">\n\t\t\t\t<input $type = \"text\" $name = \"port\" $size = \"5\" $value = \"" . (isset($portNumber) ? $portNumber : "") . "\"> <input $type = \"submit\" $value = \"" . $lang->port_check["field2"] . "\" $onclick = \"javascript:show_message()\">\n\t\t\t\t" . $lang->port_check["title"] . "</div>\n\t\t\t\t<div $id = \"message2\" $style = \"display: none;\"><img $src = \"" . $BASEURL . "/tsf_forums/images/spinner.gif\" class=\"inlineimg\"> " . $lang->port_check["checking"] . "</div>\n\t\t\t</td>\n\t\t</tr>\n\t\t</form>\t\t\n\t";
} else {
    $str .= "\n\t\t<tr>\n\t\t<td $colspan = \"2\" $align = \"left\">" . $message . "</td>\n\t</tr>\n\t";
}
$str .= "\n</table>\n";
echo $str;
stdfoot();
function checkPortConnection($ipAddress = "", $portNumber = "", $connectionTimeout = 5)
{
    return !($socketResource = @fsockopen($ipAddress, $portNumber, $errorNumber, $errorString, $connectionTimeout)) ? false : (@fclose($socketResource) ? true : true);
}

?>