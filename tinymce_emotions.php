<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "tinymce_emotions.php");
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(32759);
require "./include/php_default_timezone_set.php";
fast_db_connect();
$configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$configResult = mysqli_fetch_assoc($configQuery);
$MAIN = unserialize($configResult["content"]);
$cache = $MAIN["cache"];
$pic_base_url = $MAIN["pic_base_url"];
unset($MAIN);
$example = "<a $href = \"javascript:EmotionsDialog.insert('{emotion}','{name}');\"><img $src = \"{emotion}\" $border = \"0\" $alt = \"{name}\" $title = \"{name}\" /></a>";
$start = "\r\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html $xmlns = \"http://www.w3.org/1999/xhtml\">\r\n\t<head>\r\n\t\t<title>{#emotions_dlg.title}</title>\r\n\t\t<script $type = \"text/javascript\" $src = \"./scripts/tinymce/tiny_mce_popup.js\"></script>\r\n\t\t<script $type = \"text/javascript\" $src = \"./scripts/tinymce/plugins/emotions/js/emotions.js\"></script>\r\n\t</head>\r\n\t<body $style = \"display: none\">\r\n\t\t<div $align = \"center\">\r\n\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\">\r\n\t\t\t\t<tr>";
$end = "\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</div>\r\n\t</body>\r\n</html>";
require $cache . "/smilies.php";
$count = 0;
$output = "";
foreach ($smilies as $code => $url) {
    if (in_array(get_extension($url), ["gif", "png", "jpg"])) {
        if ($count % 8 == 0) {
            $output .= "\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>";
        }
        $name = str_replace([".gif", ".png", ".jpg"], "", $url);
        $url = $pic_base_url . "smilies/" . $url;
        $output .= "\r\n\t\t\t\t\t\t<td $valign = \"top\" $align = \"center\">" . str_replace(["{emotion}", "{name}"], [$url, $code], $example) . "</td>";
        $count++;
    }
}
exit($start . $output . $end);
function fast_db_connect()
{
    require "./include/config_database.php";
    $GLOBALS["DatabaseConnect"] = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if (!$GLOBALS["DatabaseConnect"]) {
        Stop("Database Connection Error");
    }
}
function get_extension($file = "")
{
    return strtolower(substr(strrchr($file, "."), 1));
}

?>