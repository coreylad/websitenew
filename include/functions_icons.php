<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function get_user_icons($arr)
{
    global $rootpath;
    global $pic_base_url;
    global $lang;
    global $BASEURL;
    $donorpic = "star.gif";
    $leechwarnpic = "warned.gif";
    $warnedpic = "warned.gif";
    $disabledpic = "disabled.gif";
    $commentpos = "commentpos.gif";
    $sendpmpos = "sendpmpos.gif";
    $chatpost = "chatpost.gif";
    $downloadpos = "downloadpos.gif";
    $uploadpos = "uploadpos.gif";
    $style = "style=\"vertical-align: middle; margin-center: 4pt; white-space: nowrap;\" /";
    $pics = $arr["donor"] == "yes" ? "<img $src = \"" . $pic_base_url . $donorpic . "\" $alt = \"" . $lang->global["imgdonated"] . "\" $title = \"" . $lang->global["imgdonated"] . "\" $border = \"0\" " . $style . ">" : "";
    if ($arr["enabled"] == "yes") {
        $pics .= ($arr["leechwarn"] == "yes" ? "<img $src = \"" . $pic_base_url . $leechwarnpic . "\" $title = \"" . $lang->global["imgwarned"] . "\" $alt = \"" . $lang->global["imgwarned"] . "\" $border = \"0\" " . $style . ">" : "") . ($arr["warned"] == "yes" ? "<img $src = \"" . $pic_base_url . $warnedpic . "\" $alt = \"" . $lang->global["imgwarned"] . "\" $title = \"" . $lang->global["imgwarned"] . "\" $border = \"0\" " . $style . ">" : "");
        $pics .= $arr["cancomment"] == "0" ? "<img $src = \"" . $pic_base_url . $commentpos . "\" $title = \"" . $lang->global["imgcommentpos"] . "\" $alt = \"" . $lang->global["imgcommentpos"] . "\" $border = \"0\" " . $style . ">" : "";
        $pics .= isset($arr["canmessage"]) && $arr["canmessage"] == "0" ? "<img $src = \"" . $pic_base_url . $sendpmpos . "\" $title = \"" . $lang->global["imgsendpmpos"] . "\" $alt = \"" . $lang->global["imgsendpmpos"] . "\" $border = \"0\" " . $style . ">" : "";
        $pics .= $arr["canshout"] == "0" ? "<img $src = \"" . $pic_base_url . $chatpost . "\" $title = \"" . $lang->global["imgchatpost"] . "\" $alt = \"" . $lang->global["imgchatpost"] . "\" $border = \"0\" " . $style . ">" : "";
        $pics .= $arr["candownload"] == "0" ? "<img $src = \"" . $pic_base_url . $downloadpos . "\" $title = \"" . $lang->global["imgdownloadpos"] . "\" $alt = \"" . $lang->global["imgdownloadpos"] . "\" $border = \"0\" " . $style . ">" : "";
        $pics .= $arr["canupload"] == "0" ? "<img $src = \"" . $pic_base_url . $uploadpos . "\" $title = \"" . $lang->global["imguploadpos"] . "\" $alt = \"" . $lang->global["imguploadpos"] . "\" $border = \"0\" " . $style . ">" : "";
    } else {
        $pics .= "<img $src = \"" . $pic_base_url . $disabledpic . "\" $alt = \"" . $lang->global["disabled"] . "\"  $title = \"" . $lang->global["disabled"] . "\" $border = \"0\" " . $style . ">\n";
    }
    return $pics;
}

?>