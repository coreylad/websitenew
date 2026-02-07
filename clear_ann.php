<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "clear_ann.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("CA_VERSION", "1.0 by xam");
define("NO_LOGIN_REQUIRED", true);
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0 || $usergroups["isbanned"] == "yes" || $CURUSER["enabled"] != "yes") {
    exit;
}
$lang->load("clear_ann");
header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=" . $shoutboxcharset);
$res = sql_query("SELECT * FROM announcements ORDER by added DESC");
if (0 < mysqli_num_rows($res)) {
    while ($arr = mysqli_fetch_assoc($res)) {
        $minclassread = explode(",", $arr["minclassread"]);
        if (in_array($CURUSER["usergroup"], $minclassread)) {
            sql_query("UPDATE users SET announce_read = \"yes\" WHERE announce_read = \"no\" AND id = \"" . $CURUSER["id"] . "\"");
            showAnnouncement("<h1>" . str_replace("&amp;", "&", htmlspecialchars_uni($arr["subject"])) . " - " . htmlspecialchars_uni($arr["by"]) . " - " . my_datee($dateformat, $arr["added"]) . ", " . my_datee($timeformat, $arr["added"]) . "</h1>" . nl2br($arr["message"]));
            exit;
        }
    }
}
sql_query("UPDATE users SET announce_read = \"yes\" WHERE id = \"" . $CURUSER["id"] . "\"");
showAnnouncement($lang->clear_ann["noann"]);
function showAnnouncement($message)
{
    global $shoutboxcharset;
    echo "\r\n\t<html>\r\n\t\t<head>\r\n\t\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $shoutboxcharset . "\" />\r\n\t\t\t<style>\r\n\t\t\t\t*{padding: 0; margin: 0}\r\n\t\t\t\tbody{font:9pt verdana, geneva, lucida, \"lucida grande\", arial, helvetica, sans-serif; padding: 10px;}\r\n\t\t\t\th1{padding: 0; margin: 0; font-size: 14px; color: red; margin-bottom: 3px; padding-bottom: 3px; border-bottom: 1px solid #ccc;}\r\n\t\t\t\ta{color: #000;}\r\n\t\t\t\ta:hover{color: red;}\r\n\t\t\t\timg{border: 0; max-width: 99%;}\r\n\t\t\t</style>\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t" . $message . "\r\n\t\t</body>\r\n</html>";
}

?>