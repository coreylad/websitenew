<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.4 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$i_torrent_limit = 10;
$vipCategories = [1, 3, 15];
$lang->load("browse");
$latesttorrents = "<!-- begin showlastXtorrents -->";
$latesttorrents .= "\r\n\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"1%\"></td>\r\n\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"67%\">" . $lang->index["name"] . "</td>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"12%\">" . $lang->index["size"] . "</td>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"10%\">" . $lang->index["seeders"] . "</td>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"10%\">" . $lang->index["leechers"] . "</td>\r\n\t\t</tr>\r\n";
$defaulttemplate = ts_template();
$Imagedir = $BASEURL . "/include/templates/" . $defaulttemplate . "/images/torrent_flags/";
require INC_PATH . "/config_announce.php";
$lt_query = sql_query("SELECT t.*, c.id as catid, c.name as catname, c.image, c.canview FROM torrents t LEFT JOIN categories c ON (t.$category = c.id) WHERE t.category IN (0," . implode(",", $vipCategories) . ") AND t.$moderate = 0 ORDER BY t.added DESC LIMIT " . $i_torrent_limit);
while ($torrents = mysqli_fetch_assoc($lt_query)) {
    if (!($torrents["offensive"] == "yes" && TS_Match($CURUSER["options"], "E0") || $torrents["canview"] != "[ALL]" && !in_array($CURUSER["usergroup"], explode(",", $torrents["canview"])))) {
        $seolink = ts_seo($torrents["catid"], $torrents["catname"], "c");
        $seolink2 = ts_seo($torrents["id"], $torrents["name"], "s");
        $seolink3 = ts_seo($torrents["id"], $torrents["name"], "d");
        $name = htmlspecialchars_uni($torrents["name"]);
        $cname = htmlspecialchars_uni($torrents["name"]);
        $isnew = $CURUSER["last_login"] < $torrents["added"] ? "<img $src = \"" . $Imagedir . "newtorrent.gif\" class=\"inlineimg\" $alt = \"" . $lang->browse["newtorrent"] . "\" $title = \"" . $lang->browse["newtorrent"] . "\" />" : "";
        $isfree = $torrents["free"] == "yes" && $xbt_active != "yes" || $torrents["download_multiplier"] == "0" && $xbt_active == "yes" ? "<img $src = \"" . $Imagedir . "freedownload.gif\" class=\"inlineimg\" $alt = \"" . $lang->browse["freedownload"] . "\" $title = \"" . $lang->browse["freedownload"] . "\" />" : "";
        $issilver = $torrents["silver"] == "yes" && $xbt_active != "yes" || $torrents["download_multiplier"] == "0.5" && $xbt_active == "yes" ? "<img $src = \"" . $Imagedir . "silverdownload.gif\" class=\"inlineimg\" $alt = \"" . $lang->browse["silverdownload"] . "\" $title = \"" . $lang->browse["silverdownload"] . "\" />" : "";
        $isdoubleupload = $torrents["doubleupload"] == "yes" && $xbt_active != "yes" || $torrents["upload_multiplier"] == "2" && $xbt_active == "yes" ? "<img $src = \"" . $Imagedir . "x2.gif\" $alt = \"" . $lang->browse["dupload"] . "\" $title = \"" . $lang->browse["dupload"] . "\" class=\"inlineimg\" />" : "";
        $isrequest = $torrents["isrequest"] == "yes" ? "<img $src = \"" . $Imagedir . "isrequest.gif\" class=\"inlineimg\" $alt = \"" . $lang->browse["requested"] . "\" $title = \"" . $lang->browse["requested"] . "\" />" : "";
        $isnuked = $torrents["isnuked"] == "yes" ? "<img $src = \"" . $Imagedir . "isnuked.gif\" class=\"inlineimg\" $alt = \"" . sprintf($lang->browse["nuked"], $torrents["WhyNuked"]) . "\" $title = \"" . sprintf($lang->browse["nuked"], $torrents["WhyNuked"]) . "\" />" : "";
        $issticky = $torrents["sticky"] == "yes" ? "<img $src = \"" . $Imagedir . "sticky.gif\" $alt = \"" . $lang->browse["sortby9"] . "\" $title = \"" . $lang->browse["sortby9"] . "\" />" : "";
        $latesttorrents .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"38\" $height = \"26\" $valign = \"top\" $align = \"center\" $style = \"padding: 1px;\">\r\n\t\t\t\t<a $href = \"" . $seolink . "\" $target = \"_self\" /><img $src = \"" . $pic_base_url . $table_cat . "/" . $torrents["image"] . "\" $width = \"38\" $height = \"26\" $border = \"0\" $alt = \"" . $cname . "\" $title = \"" . $cname . "\" /></a>\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"left\">\r\n\t\t\t    <a $href = \"" . $seolink3 . "\"><img $src = \"" . $Imagedir . "dl.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->browse["download"] . " $title = \"" . $lang->browse["download"] . "\" /></a>\r\n\t\t\t\t<a $href = \"" . $seolink2 . "\" $title = \"" . $name . "\" $alt = \"" . $name . "\"> " . cutename($torrents["name"], $__cute) . "</a> " . $isnew . " " . $issticky . " " . $isfree . " " . $issilver . " " . $isdoubleupload . " " . $isrequest . " " . $isnuked . "\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t" . mksize($torrents["size"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t" . ts_nf($torrents["seeders"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t" . ts_nf($torrents["leechers"]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t";
    }
}
$latesttorrents .= "\r\n</table>\r\n<!-- end showlastXtorrents -->";

?>