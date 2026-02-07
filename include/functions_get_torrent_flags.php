<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function GetTorrentTags($t, $canUpdateExternal = true)
{
    global $lang;
    global $BASEURL;
    global $is_mod;
    global $TWebSeeders;
    global $CURUSER;
    global $xbt_active;
    global $isfree;
    global $issilver;
    global $isdoubleupload;
    global $defaulttemplate;
    $ShowImage = TIMENOW - $t["ts_external_lastupdate"] < 3600 ? !$is_mod ? false : true : true;
    $Imagedir = $BASEURL . "/include/templates/" . $defaulttemplate . "/images/torrent_flags/";
    $I = [];
    if (!isset($lang->browse["dupload"])) {
        $lang->load("browse");
    }
    if (TS_MTStoUTS($CURUSER["last_login"]) < TS_MTStoUTS($t["added"])) {
        $I[] = "<img src=\"" . $Imagedir . "newtorrent.gif\" border=\"0\" alt=\"" . $lang->browse["newtorrent"] . "\" title=\"" . $lang->browse["newtorrent"] . "\" class=\"inlineimg\" />";
    }
    if ($t["ts_external"] != "yes" && ($xbt_active != "yes" && ($t["free"] == "yes" || $isfree) || $xbt_active == "yes" && $t["download_multiplier"] == "0")) {
        $I[] = "<img src=\"" . $Imagedir . "freedownload.gif\" border=\"0\" alt=\"" . $lang->browse["freedownload"] . "\" title=\"" . $lang->browse["freedownload"] . "\" class=\"inlineimg\" />";
    }
    if ($t["ts_external"] != "yes" && ($xbt_active != "yes" && ($t["silver"] == "yes" || $issilver) || $xbt_active == "yes" && $t["download_multiplier"] == "0.5")) {
        $I[] = "<img src=\"" . $Imagedir . "silverdownload.gif\" border=\"0\" alt=\"" . $lang->browse["silverdownload"] . "\" title=\"" . $lang->browse["silverdownload"] . "\" class=\"inlineimg\" />";
    }
    if ($t["ts_external"] != "yes" && ($xbt_active != "yes" && ($t["doubleupload"] == "yes" || $isdoubleupload) || $xbt_active == "yes" && $t["upload_multiplier"] == "2")) {
        $I[] = "<img src=\"" . $Imagedir . "x2.gif\" border=\"0\" alt=\"" . $lang->browse["dupload"] . "\" title=\"" . $lang->browse["dupload"] . "\" class=\"inlineimg\" />";
    }
    if ($t["isnuked"] == "yes") {
        $I[] = "<img src=\"" . $Imagedir . "isnuked.gif\" border=\"0\" alt=\"" . htmlspecialchars_uni($t["WhyNuked"]) . "\" title=\"" . htmlspecialchars_uni($t["WhyNuked"]) . "\" class=\"inlineimg\" />";
    }
    if ($t["isrequest"] == "yes") {
        $I[] = "<img src=\"" . $Imagedir . "isrequest.gif\" border=\"0\" alt=\"" . $lang->browse["requested"] . "\" title=\"" . $lang->browse["requested"] . "\" class=\"inlineimg\" />";
    }
    if ($t["ts_external"] == "yes" && $ShowImage === true) {
        $I[] = "<span id=\"isexternal_" . $t["id"] . "\">" . ($canUpdateExternal ? "<a href=\"javascript:void(0)\" onclick=\"UpdateExternalTorrent('./include/ts_external_scrape/ts_update.php', 'id=" . $t["id"] . "&ajax_update=true', " . $t["id"] . ")\"><img src=\"" . $Imagedir . "external.gif\" border=\"0\" alt=\"" . $lang->browse["update"] . "\" title=\"" . $lang->browse["update"] . "\" class=\"inlineimg\" /></a>" : "<img src=\"" . $Imagedir . "external.gif\" border=\"0\" alt=\"" . $lang->browse["sortby15"] . "\" title=\"" . $lang->browse["sortby15"] . "\" class=\"inlineimg\" />") . "</span>";
    }
    if (isset($TWebSeeders[$t["id"]])) {
        $I[] = "<img src=\"" . $Imagedir . "webseeder.png\" alt=\"" . sprintf($lang->browse["webseeder"], ts_nf($TWebSeeders[$t["id"]])) . "\" title=\"" . sprintf($lang->browse["webseeder"], ts_nf($TWebSeeders[$t["id"]])) . "\" class=\"inlineimg\" />";
    }
    return count($I) ? implode(" ", $I) : "";
}

?>