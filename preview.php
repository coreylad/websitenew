<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("P_VERSION", "0.8 by xam");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("THIS_SCRIPT", "preview.php");
require "./global.php";
if (!isset($CURUSER)) {
    exit;
}
header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; $charset = " . $shoutboxcharset);
if (empty($_POST["msg"])) {
    exit("<error>" . $lang->global["dontleavefieldsblank"] . "</error>");
}
// Process and sanitize the message
$previewMessage = fixAjaxText($_POST["msg"]);
$previewMessage = strval($previewMessage);
if (strtolower($shoutboxcharset) != "utf-8") {
    if (function_exists("iconv")) {
        $previewMessage = iconv("UTF-8", $shoutboxcharset, $previewMessage);
    } else {
        if (function_exists("mb_convert_encoding")) {
            $previewMessage = mb_convert_encoding($previewMessage, $shoutboxcharset, "UTF-8");
        } else {
            if (strtolower($shoutboxcharset) == "iso-8859-1") {
                $previewMessage = utf8_decode($previewMessage);
            }
        }
    }
}
$lang->load("comment");
include_once INC_PATH . "/functions_ratio.php";

$userSignature = !empty($CURUSER["signature"]) ? "<br /><hr $size = \"1\" $width = \"50%\"  $align = \"left\" />" . format_comment($CURUSER["signature"], true, true, true, true, "signatures") : "";
$formattedComment = format_comment($previewMessage);
$userRatio = get_user_ratio($CURUSER["uploaded"], $CURUSER["downloaded"]);
$userStatsHtml = "<b>" . $lang->global["added"] . ":</b> " . my_datee($regdateformat, $CURUSER["added"]) . "<br /><b>" . $lang->global["uploaded"] . "</b> " . mksize($CURUSER["uploaded"]) . "<br /><b>" . $lang->global["downloaded"] . "</b> " . mksize($CURUSER["downloaded"]) . "<br /><b>" . $lang->global["ratio"] . "</b> " . strip_tags($userRatio);
$onMouseOverStats = "onmouseover=\"ddrivetip('" . $userStatsHtml . "', 200)\"; $onmouseout = \"hideddrivetip()\" ";
$userDisplayName = $CURUSER["username"] ? "<a " . $onMouseOverStats . "href=\"" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "\" $alt = \"" . $CURUSER["username"] . "\">" . get_user_color($CURUSER["username"], $usergroups["namestyle"]) . "</a> (" . ($CURUSER["title"] ? htmlspecialchars_uni($CURUSER["title"]) : get_user_color($usergroups["title"], $usergroups["namestyle"])) . ") " . ($CURUSER["donor"] == "yes" ? " <img $src = \"" . $pic_base_url . "star.gif\" $alt = \"" . $lang->global["imgdonated"] . "\" $title = \"" . $lang->global["imgdonated"] . "\" $border = \"0\" class=\"inlineimg\" />" : "") . ($CURUSER["warned"] == "yes" || $CURUSER["leechwarn"] == "yes" ? " <img $src = \"" . $pic_base_url . "warned.gif\" $alt = \"" . $lang->global["imgwarned"] . "\" $title = \"" . $lang->global["imgwarned"] . "\" $border = \"0\" class=\"inlineimg\" />" : "") : $lang->global["guest"];
$commentId = TIMENOW;
$commentPreviewTable = "<br />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"2\" class=\"subheader\">\r\n\t\t\t\t\t<div $style = \"float: right;\"></div>\r\n\t\t\t\t\t<div $style = \"float: left;\"><a $name = \"cid" . $commentId . "\" $id = \"cid" . $commentId . "\"></a><a $href = \"#cid" . $commentId . "\">#" . ($ts_perpage + 1) . "</a> by " . $userDisplayName . " " . my_datee($dateformat, TIMENOW) . " " . my_datee($timeformat, TIMENOW) . "</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\" $valign = \"top\" $height = \"1%\" $width = \"1%\">\r\n\t\t\t\t\t" . get_user_avatar($CURUSER["avatar"], false, 100, 100) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td $align = \"left\" $valign = \"top\">\r\n\t\t\t\t\t<div $id = \"post_message_" . $commentId . "\" $style = \"display: inline;\">" . $formattedComment . "</div>\r\n\t\t\t\t\t" . $userSignature . "\t\t\t\t\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t\r\n\t\t</tbody>\r\n\t</table>\r\n\t";
exit($commentPreviewTable);

?>