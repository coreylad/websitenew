<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_donation_status.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("NO_LOGIN_REQUIRED", true);
$rootpath = "./../../";
require $rootpath . "global.php";
define("TDS_VERSION", "0.5 by xam");
if ((!isset($CURUSER) || preg_match("#templateshares#isU", $BASEURL)) && $CURUSER["username"] != "xam") {
    print_no_permission();
}
$lang->load("donate");
$TSSEConfig->TSLoadConfig("PAYPAL");
if ($TSSECache->Cache["total_funds"]["lastupdate"] < TIMENOW - 3600) {
    $Query = sql_query("SELECT SUM(cash) AS total_funds FROM funds WHERE cash > 0");
    if (mysqli_num_rows($Query)) {
        $Result = mysqli_fetch_assoc($Query);
        $total_funds = $Result["total_funds"];
        $Progress_so_far = $total_funds / $tn * 100;
        $Progress_so_far = 100 <= $Progress_so_far ? 100 : number_format($Progress_so_far, 1);
    } else {
        $Progress_so_far = "0.0";
    }
    $TSSECache->UpdateCache("total_funds", $Progress_so_far);
}
$Progress_so_far = $TSSECache->Cache["total_funds"]["content"];
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html $xmlns = \"http://www.w3.org/1999/xhtml\" xml:$lang = \"en\" $lang = \"en\">\r\n\t<head>\r\n\t<title>";
echo $SITENAME;
echo "</title>\r\n\t<meta http-$equiv = \"content-type\" $content = \"text/html; $charset = ";
echo $charset;
echo "\" />\r\n\t<script $type = \"text/javascript\" $src = \"";
echo $BASEURL;
echo "/scripts/prototype.js\"></script>\r\n\t<script $type = \"text/javascript\" $src = \"";
echo $BASEURL;
echo "/scripts/pbar/js/jsProgressBarHandler.js\"></script>\r\n\t<style $type = \"text/css\">\r\n\ta:link { text-decoration : none; color : #3366cc; border: 0px;}\r\n\ta:active { text-decoration : underline; color : #3366cc; border: 0px;}\r\n\ta:visited { text-decoration : none; color : #3366cc; border: 0px;}\r\n\ta:hover { text-decoration : underline; color : #ff5a00; border: 0px;}\r\n\timg { padding: 0px; margin: 0px; border: none;}\r\n\tbody\r\n\t{\r\n\t\tmargin : 0 auto;\r\n\t\twidth:100%;\r\n\t\tfont-family: 'Verdana';\r\n\t\tcolor: #40454b;\r\n\t\tfont-size: 12px;\r\n\t}\r\n\t.text\r\n\t{\r\n\t\tcolor:#006600;\r\n\t\tfont-weight:bold;\r\n\t}\r\n\t</style>\r\n\t<script $type = \"text/javascript\">\r\n\t<!--\r\n\tvar $message = \"";
echo $lang->global["notavailable"];
echo "\";\r\n\tfunction clickIE4(){\r\n\tif (event.$button = =2){\r\n\talert(message);\r\n\treturn false;\r\n\t}\r\n\t}\r\n\tfunction clickNS4(e){\r\n\tif (document.layers||document.getElementById&&!document.all){\r\n\tif (e.$which = =2||e.$which = =3){\r\n\talert(message);\r\n\treturn false;\r\n\t}\r\n\t}\r\n\t}\r\n\tif (document.layers){\r\n\tdocument.captureEvents(Event.MOUSEDOWN);\r\n\tdocument.$onmousedown = clickNS4;\r\n\t}\r\n\telse if (document.all&&!document.getElementById){\r\n\tdocument.$onmousedown = clickIE4;\r\n\t}\r\n\tdocument.$oncontextmenu = new Function(\"alert(message);return false\")\r\n\t// -->\r\n\t</script>\r\n\t</head>\r\n\r\n\t<body>\r\n\t\t<table $border = \"0\" $cellpadding = \"3\" $align = \"center\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t<p $style = \"width:540px; background: #ffff99; text-align: left; color: #000; border: 1px solid #ff9900; padding: 5px; font-size: 12px; font-weight: bold;\">";
echo sprintf($lang->donate["systemmessage"], $SITENAME);
echo "</p>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<span class=\"text\">";
echo $lang->donate["received"];
echo ":</span>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t <span class=\"progressBar\" $id = \"element1\"> ";
echo $Progress_so_far;
echo "%</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<span class=\"text\">";
echo $lang->donate["targetamount"];
echo ":\r\n\t\t\t\t</td>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t";
echo number_format($tn, 2);
echo "</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<span class=\"text\">";
echo $lang->donate["stilltogo"];
echo ":\r\n\t\t\t\t</td>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t";
echo number_format(round($Progress_so_far < 100 ? 100 - $Progress_so_far : 0), 1);
echo "%</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t<p $style = \"width:540px; background: #ffff99; text-align: center; color: #000; border: 1px solid #ff9900; padding: 5px; font-size: 12px; font-weight: bold;\">";
echo sprintf($lang->donate["clicktodonate"], $BASEURL);
echo "</p>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t</body>\r\n</html>";

?>