<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "topten.php");
require "./global.php";
define("T_VERSION", "v.1.3.1 by xam");
if ($usergroups["cantopten"] != "yes") {
    print_no_permission();
    exit;
}
if (isset($_GET["type"]) && $_GET["type"] == 5) {
    redirect("tsf_forums/top_stats.php?from=topten");
    exit;
}
include_once INC_PATH . "/functions_cache.php";
include_once INC_PATH . "/functions_ratio.php";
$TSSEConfig->TSLoadConfig("ANNOUNCE");
$lang->load("topten");
$notin = "8,7,6,5,9,10,11";
stdhead($lang->topten["head"]);
echo "<table class=\"main\" width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"embedded\">\n";
$type = isset($_GET["type"]) ? intval($_GET["type"]) : 1;
if (!in_array($type, [1, 2, 3, 4])) {
    $type = 1;
}
$limit = isset($_GET["lim"]) ? 0 + $_GET["lim"] : false;
$subtype = isset($_GET["subtype"]) ? $_GET["subtype"] : false;
echo "<p align=\"center\">" . ($type == 1 && !$limit ? "<b>" . $lang->topten["users"] . "</b>" : "<a href=\"topten.php?type=1\">" . $lang->topten["users"] . "</a>") . " | " . ($type == 2 && !$limit ? "<b>" . $lang->topten["torrents"] . "</b>" : "<a href=\"topten.php?type=2\">" . $lang->topten["torrents"] . "</a>") . " | " . ($type == 3 && !$limit ? "<b>" . $lang->topten["countries"] . "</b>" : "<a href=\"topten.php?type=3\">" . $lang->topten["countries"] . "</a>") . " | " . ($type == 4 && !$limit ? "<b>" . $lang->topten["peers"] . "</b>" : "<a href=\"topten.php?type=4\">" . $lang->topten["peers"] . "</a>") . " | " . ($type == 5 && !$limit ? "<b>" . $lang->topten["forums"] . "</b>" : "<a href=\"topten.php?type=5\">" . $lang->topten["forums"] . "</a>") . " </p>\n";
$pu = $is_mod ? true : false;
if (!$pu) {
    $limit = 10;
}
if ($type == 1) {
    if (!$limit || 250 < $limit) {
        $limit = 10;
    }
    $cachefile = "topten-type-" . $type . "-limit-" . $limit . "-poweruser-" . $pu . "-subtype-" . $subtype;
    cache_check($cachefile);
    $mainquery = "SELECT u.id as userid, u.username, u.usergroup, u.options, u.added, u.uploaded, u.downloaded, u.uploaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(u.added)) AS upspeed, u.downloaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(u.added)) AS downspeed, g.namestyle, g.canstaffpanel, g.issupermod, g.cansettingspanel FROM users u LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE u.enabled = 'yes' AND u.usergroup NOT IN (" . $notin . ")";
    if ($limit == 10 || $subtype == "ul") {
        $order = "uploaded DESC";
        ($r = sql_query($mainquery . " ORDER BY " . $order . " " . " LIMIT " . $limit)) || sqlerr(__FILE__, 233);
        usertable($r, sprintf($lang->topten["type1_title1"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&lim=100&subtype=ul>" . $lang->topten["top100"] . "</a>] - [<a href=topten.php?type=1&lim=250&subtype=ul>" . $lang->topten["top250"] . "</a>]</font>" : ""));
    }
    if ($limit == 10 || $subtype == "dl") {
        $order = "downloaded DESC";
        ($r = sql_query($mainquery . " ORDER BY " . $order . " " . " LIMIT " . $limit)) || sqlerr(__FILE__, 240);
        usertable($r, sprintf($lang->topten["type1_title2"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&lim=100&subtype=dl>" . $lang->topten["top100"] . "</a>] - [<a href=topten.php?type=1&lim=250&subtype=dl>" . $lang->topten["top250"] . "</a>]</font>" : ""));
    }
    if ($limit == 10 || $subtype == "uls") {
        $order = "upspeed DESC";
        ($r = sql_query($mainquery . " ORDER BY " . $order . " " . " LIMIT " . $limit)) || sqlerr(__FILE__, 247);
        usertable($r, sprintf($lang->topten["type1_title3"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&lim=100&subtype=uls>" . $lang->topten["top100"] . "</a>] - [<a href=topten.php?type=1&lim=250&subtype=uls>" . $lang->topten["top250"] . "</a>]</font>" : ""));
    }
    if ($limit == 10 || $subtype == "dls") {
        $order = "downspeed DESC";
        ($r = sql_query($mainquery . " ORDER BY " . $order . " " . " LIMIT " . $limit)) || sqlerr(__FILE__, 254);
        usertable($r, sprintf($lang->topten["type1_title4"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&lim=100&subtype=dls>" . $lang->topten["top100"] . "</a>] - [<a href=topten.php?type=1&lim=250&subtype=dls>" . $lang->topten["top250"] . "</a>]</font>" : ""));
    }
    if ($limit == 10 || $subtype == "bsh") {
        $order = "uploaded / downloaded DESC";
        $extrawhere = " AND downloaded > 1073741824";
        ($r = sql_query($mainquery . $extrawhere . " ORDER BY " . $order . " " . " LIMIT " . $limit)) || sqlerr(__FILE__, 262);
        usertable($r, sprintf($lang->topten["type1_title5"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&lim=100&subtype=bsh>" . $lang->topten["top100"] . "</a>] - [<a href=topten.php?type=1&lim=250&subtype=bsh>" . $lang->topten["top250"] . "</a>]</font>" : ""));
    }
    if ($limit == 10 || $subtype == "wsh") {
        $order = "uploaded / downloaded ASC, downloaded DESC";
        $extrawhere = " AND downloaded > 1073741824";
        ($r = sql_query($mainquery . $extrawhere . " ORDER BY " . $order . " " . " LIMIT " . $limit)) || sqlerr(__FILE__, 270);
        usertable($r, sprintf($lang->topten["type1_title6"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&lim=100&subtype=wsh>" . $lang->topten["top100"] . "</a>] - [<a href=topten.php?type=1&lim=250&subtype=wsh>" . $lang->topten["top250"] . "</a>]</font>" : ""));
    }
    cache_save($cachefile);
} else {
    if ($type == 2) {
        if (!$limit || 50 < $limit) {
            $limit = 10;
        }
        $cachefile = "topten-type-" . $type . "-limit-" . $limit . "-poweruser-" . $pu . "-subtype-" . $subtype;
        cache_check($cachefile);
        if ($limit == 10 || $subtype == "act") {
            if ($xbt_active == "yes") {
                $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE p.`left` > 0 GROUP BY t.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT " . $limit) or ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE p.`left` > 0 GROUP BY t.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT " . $limit)) || sqlerr(__FILE__, 287);
            } else {
                ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT " . $limit)) || sqlerr(__FILE__, 289);
            }
            _torrenttable($r, sprintf($lang->topten["type2_title1"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&lim=25&subtype=act>" . $lang->topten["top25"] . "</a>] - [<a href=topten.php?type=2&lim=50&subtype=act>" . $lang->topten["top50"] . "</a>]</font>" : ""));
        }
        if ($limit == 10 || $subtype == "sna") {
            if ($xbt_active == "yes") {
                $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE t.times_completed > 0 GROUP BY t.id ORDER BY times_completed DESC, added ASC LIMIT " . $limit) or ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE t.times_completed > 0 GROUP BY t.id ORDER BY times_completed DESC, added ASC LIMIT " . $limit)) || sqlerr(__FILE__, 296);
            } else {
                ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE t.times_completed > 0 GROUP BY t.id ORDER BY times_completed DESC, added ASC LIMIT " . $limit)) || sqlerr(__FILE__, 298);
            }
            _torrenttable($r, sprintf($lang->topten["type2_title2"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&lim=25&subtype=sna>" . $lang->topten["top25"] . "</a>] - [<a href=topten.php?type=2&lim=50&subtype=sna>" . $lang->topten["top50"] . "</a>]</font>" : ""));
        }
        if ($limit == 10 || $subtype == "mdt") {
            if ($xbt_active == "yes") {
                $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE times_completed > 0 GROUP BY t.id ORDER BY data DESC, added ASC LIMIT " . $limit) or ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE times_completed > 0 GROUP BY t.id ORDER BY data DESC, added ASC LIMIT " . $limit)) || sqlerr(__FILE__, 305);
            } else {
                ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE times_completed > 0 GROUP BY t.id ORDER BY data DESC, added ASC LIMIT " . $limit)) || sqlerr(__FILE__, 307);
            }
            _torrenttable($r, sprintf($lang->topten["type2_title3"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&lim=25&subtype=mdt>" . $lang->topten["top25"] . "</a>] - [<a href=topten.php?type=2&lim=50&subtype=mdt>" . $lang->topten["top50"] . "</a>]</font>" : ""));
        }
        if ($limit == 10 || $subtype == "bse") {
            if ($xbt_active == "yes") {
                $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE seeders >= 5 GROUP BY t.id ORDER BY seeders DESC, seeders+leechers DESC, added ASC LIMIT " . $limit) or ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE seeders >= 5 GROUP BY t.id ORDER BY seeders DESC, seeders+leechers DESC, added ASC LIMIT " . $limit)) || sqlerr(__FILE__, 314);
            } else {
                ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE seeders >= 5 GROUP BY t.id ORDER BY seeders DESC, seeders+leechers DESC, added ASC LIMIT " . $limit)) || sqlerr(__FILE__, 316);
            }
            _torrenttable($r, sprintf($lang->topten["type2_title4"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&lim=25&subtype=bse>" . $lang->topten["top25"] . "</a>] - [<a href=topten.php?type=2&lim=50&subtype=bse>" . $lang->topten["top50"] . "</a>]</font>" : ""));
        }
        if ($limit == 10 || $subtype == "wse") {
            if ($xbt_active == "yes") {
                $r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE p.`left` > 0 AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY seeders / leechers ASC, leechers DESC LIMIT " . $limit) or ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN xbt_files_users AS p ON t.id = p.fid WHERE p.`left` > 0 AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY seeders / leechers ASC, leechers DESC LIMIT " . $limit)) || sqlerr(__FILE__, 323);
            } else {
                ($r = sql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY seeders / leechers ASC, leechers DESC LIMIT " . $limit)) || sqlerr(__FILE__, 325);
            }
            _torrenttable($r, sprintf($lang->topten["type2_title5"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&lim=25&subtype=wse>" . $lang->topten["top25"] . "</a>] - [<a href=topten.php?type=2&lim=50&subtype=wse>" . $lang->topten["top50"] . "</a>]</font>" : ""));
        }
        cache_save($cachefile);
    } else {
        if ($type == 3) {
            if (!$limit || 25 < $limit) {
                $limit = 10;
            }
            $cachefile = "topten-type-" . $type . "-limit-" . $limit . "-poweruser-" . $pu . "-subtype-" . $subtype;
            cache_check($cachefile);
            if ($limit == 10 || $subtype == "us") {
                ($r = sql_query("SELECT name, flagpic, COUNT(users.country) as num FROM countries LEFT JOIN users ON users.country = countries.id GROUP BY name ORDER BY num DESC LIMIT " . $limit)) || sqlerr(__FILE__, 341);
                countriestable($r, sprintf($lang->topten["type3_title1"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&lim=25&subtype=us>" . $lang->topten["top25"] . "</a>]</font>" : ""), "Users");
            }
            if ($limit == 10 || $subtype == "ul") {
                ($r = sql_query("SELECT c.name, c.flagpic, sum(u.uploaded) AS ul FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name ORDER BY ul DESC LIMIT " . $limit)) || sqlerr(__FILE__, 347);
                countriestable($r, sprintf($lang->topten["type3_title2"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&lim=25&subtype=ul>" . $lang->topten["top25"] . "</a>]</font>" : ""), "Uploaded");
            }
            if ($limit == 10 || $subtype == "avg") {
                ($r = sql_query("SELECT c.name, c.flagpic, sum(u.uploaded)/count(u.id) AS ul_avg FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name HAVING sum(u.uploaded) > 1099511627776 AND count(u.id) >= 100 ORDER BY ul_avg DESC LIMIT " . $limit)) || sqlerr(__FILE__, 353);
                countriestable($r, sprintf($lang->topten["type3_title3"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&lim=25&subtype=avg>" . $lang->topten["top25"] . "</a>]</font>" : ""), "Average");
            }
            if ($limit == 10 || $subtype == "r") {
                ($r = sql_query("SELECT c.name, c.flagpic, sum(u.uploaded)/sum(u.downloaded) AS r FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name HAVING sum(u.uploaded) > 1099511627776 AND sum(u.downloaded) > 1099511627776 AND count(u.id) >= 100 ORDER BY r DESC LIMIT " . $limit)) || sqlerr(__FILE__, 359);
                countriestable($r, sprintf($lang->topten["type3_title4"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&lim=25&subtype=r>" . $lang->topten["top25"] . "</a>]</font>" : ""), "Ratio");
            }
            cache_save($cachefile);
        } else {
            if ($type == 4) {
                if (!$limit || 250 < $limit) {
                    $limit = 10;
                }
                $cachefile = "topten-type-" . $type . "-limit-" . $limit . "-poweruser-" . $pu . "-subtype-" . $subtype;
                if ($xbt_active == "yes") {
                    stdmsg($lang->global["error"], $lang->global["notavailable"]);
                    echo "</td></tr></table>\n";
                    stdfoot();
                    exit;
                }
                cache_check($cachefile);
                if ($limit == 10 || $subtype == "ul") {
                    ($r = sql_query("SELECT users.id AS userid, usergroup, options, username, IF(peers.uploaded >= peers.uploadoffset, (peers.uploaded - peers.uploadoffset), peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, IF(seeder = 'yes', (peers.downloaded - peers.downloadoffset)  / (finishedat - UNIX_TIMESTAMP(started)),(peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started))) AS downrate, g.namestyle, g.canstaffpanel, g.issupermod, g.cansettingspanel FROM peers LEFT JOIN users ON peers.userid = users.id LEFT JOIN usergroups g ON (users.usergroup=g.gid) WHERE usergroup NOT IN (" . $notin . ") ORDER BY uprate DESC LIMIT " . $limit)) || sqlerr(__FILE__, 384);
                    peerstable($r, sprintf($lang->topten["type4_title1"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&lim=100&subtype=ul>" . $lang->topten["top100"] . "</a>] - [<a href=topten.php?type=4&lim=250&subtype=ul>" . $lang->topten["top25"] . "0</a>]</font>" : ""));
                }
                if ($limit == 10 || $subtype == "dl") {
                    ($r = sql_query("SELECT users.id AS userid, usergroup, options, peers.id AS peerid, username, peers.uploaded, peers.downloaded, IF(peers.uploaded >= peers.uploadoffset, (peers.uploaded - peers.uploadoffset), peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, IF(seeder = 'yes',(peers.downloaded - peers.downloadoffset)  / (finishedat - UNIX_TIMESTAMP(started)),(peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started))) AS downrate, g.namestyle FROM peers LEFT JOIN users ON peers.userid = users.id LEFT JOIN usergroups g ON (users.usergroup=g.gid) ORDER BY downrate DESC LIMIT " . $limit)) || sqlerr(__FILE__, 390);
                    peerstable($r, sprintf($lang->topten["type4_title2"], $limit) . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&lim=100&subtype=dl>" . $lang->topten["top100"] . "</a>] - [<a href=topten.php?type=4&lim=250&subtype=dl>" . $lang->topten["top25"] . "0</a>]</font>" : ""));
                }
                cache_save($cachefile);
            }
        }
    }
}
echo "</td></tr></table>\n";
stdfoot();
function getUserTable($res, $frame_caption)
{
    global $CURUSER;
    global $lang;
    global $regdateformat;
    global $pic_base_url;
    global $BASEURL;
    echo "<h2>" . $frame_caption . "</h2>\n<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td align=\"center\">\n";
    echo "<table class=\"main\" width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n";
    echo "<tr>\r\n\t<td class=\"colhead\" align=\"center\" width=\"5%\">" . $lang->topten["rank"] . "</td>\r\n\t<td class=\"colhead\" align=\"left\" width=\"15%\">" . $lang->topten["user"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"15%\">" . $lang->topten["uploaded"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"15%\">" . $lang->topten["ulspeed"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"15%\">" . $lang->topten["downloaded"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"15%\">" . $lang->topten["dlspeed"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"10%\">" . $lang->topten["ratio"] . "</td>\r\n\t<td class=\"colhead\" align=\"center\" width=\"10%\">" . $lang->topten["joined"] . "</td>\r\n\t</tr>";
    $num = 0;
    while ($a = mysqli_fetch_array($res)) {
        $num++;
        if ($a["downloaded"]) {
            $ratio = $a["uploaded"] / $a["downloaded"];
            $color = get_ratio_color($ratio);
            $ratio = number_format($ratio, 2);
            if ($color) {
                $ratio = "<font color=\"" . $color . "\">" . $ratio . "</font>";
            }
        } else {
            $ratio = "Inf.";
        }
        if ($a["added"] == "0000-00-00 00:00:00") {
            $joindate = $lang->users["na"];
        } else {
            $joindate_date = my_datee($regdateformat, $a["added"]);
        }
        $report = "<span style=\"float: right;\"><a onclick=\"TSOpenPopup('" . $BASEURL . "/report.php?type=1&reporting=" . $a["userid"] . "', 'report', 500, 300); return false;\" href=\"javascript:void(0);\"><img src=\"" . $pic_base_url . "report2.gif\" border=\"0\" alt=\"" . $lang->topten["reportuser"] . "\" title=\"" . $lang->topten["reportuser"] . "\" /></a></span>";
        echo "<tr><td align=\"center\">" . $num . "</td><td align=\"left\">" . $report . "<a href=\"" . ts_seo($a["userid"], $a["username"]) . "\"><b>" . get_user_color($a["username"], $a["namestyle"]) . "</b>" . "</td><td align=\"right\">" . mksize($a["uploaded"]) . "</td><td align=\"right\">" . mksize($a["upspeed"]) . "/s" . "</td><td align=\"right\">" . mksize($a["downloaded"]) . "</td><td align=\"right\">" . mksize($a["downspeed"]) . "/s" . "</td><td align=\"right\">" . $ratio . "</td><td align=\"center\">" . $joindate_date . "</td></tr>";
    }
    echo "</td></tr></table>\n</td></tr></table>\n";
}
function getTorrentTable($res, $frame_caption)
{
    global $lang;
    global $BASEURL;
    global $pic_base_url;
    echo "<h2>" . $frame_caption . "</h2>\n<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td align=\"center\">\n";
    echo "<table class=\"main\" width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n";
    echo "<td class=\"colhead\" align=\"center\" width=\"5%\">" . $lang->topten["rank"] . "</td>\r\n\t<td class=\"colhead\" align=\"left\" width=\"35%\">" . $lang->topten["name"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"10%\">" . $lang->topten["snatched"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"10%\">" . $lang->topten["data"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"10%\">" . $lang->topten["seeders"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"10%\">" . $lang->topten["leechers"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"10%\">" . $lang->topten["total"] . "</td>\r\n\t<td class=\"colhead\" align=\"right\" width=\"10%\">" . $lang->topten["ratio"] . "</td>\r\n\t</tr>";
    $num = 0;
    while ($a = mysqli_fetch_array($res)) {
        $num++;
        if ($a["leechers"]) {
            $r = $a["seeders"] / $a["leechers"];
            $ratio = "<font color=\"" . get_ratio_color($r) . "\">" . number_format($r, 2) . "</font>";
        } else {
            $ratio = "Inf.";
        }
        $report = "<span style=\"float: right;\"><a onclick=\"TSOpenPopup('" . $BASEURL . "/report.php?type=2&reporting=" . $a["id"] . "', 'report', 500, 300); return false;\" href=\"javascript:void(0);\"><img src=\"" . $pic_base_url . "report2.gif\" border=\"0\" alt=\"" . $lang->topten["reporttorrent"] . "\" title=\"" . $lang->topten["reporttorrent"] . "\" /></a></span>";
        echo "<tr><td align=\"center\">" . $num . "</td><td align=\"left\">" . $report . "<a href=\"" . ts_seo($a["id"], $a["name"], "s") . "\"><b>" . cutename($a["name"], 55) . "</b></a></td><td align=\"right\">" . number_format($a["times_completed"]) . "</td><td align=\"right\">" . mksize($a["data"]) . "</td><td align=\"right\">" . number_format($a["seeders"]) . "</td><td align=\"right\">" . number_format($a["leechers"]) . "</td><td align=\"right\">" . ($a["leechers"] + $a["seeders"]) . "</td><td align=\"right\">" . $ratio . "</td>\n";
    }
    echo "</td></tr></table>\n</td></tr></table>\n";
}
function getCountriesTable($res, $frame_caption, $what)
{
    global $CURUSER;
    global $pic_base_url;
    global $lang;
    echo "<h2>" . $frame_caption . "</h2>\n<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td align=\"center\">\n";
    echo "<table class=\"main\" width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n";
    echo "<tr>\r\n\t<td class=\"colhead\" align=\"center\" width=\"10%\">" . $lang->topten["rank"] . "</td>\r\n\t<td class=\"colhead\" align=\"left\" width=\"70%\">" . $lang->topten["country"] . "</td>\r\n\t<td class=\"colhead\" align=\"center\" width=\"20%\">" . $what . "</td>\r\n\t</tr>";
    $num = 0;
    while ($a = mysqli_fetch_array($res)) {
        $num++;
        if ($what == "Users") {
            $value = number_format($a["num"]);
        } else {
            if ($what == "Uploaded") {
                $value = mksize($a["ul"]);
            } else {
                if ($what == "Average") {
                    $value = mksize($a["ul_avg"]);
                } else {
                    if ($what == "Ratio") {
                        $value = number_format($a["r"], 2);
                    }
                }
            }
        }
        echo "<tr><td align=\"center\">" . $num . "</td><td align=\"left\"><img style=\"vertical-align: middle;\" src=\"" . $pic_base_url . "flag/" . $a["flagpic"] . "\"> <b>" . $a["name"] . "</b></td><td align=\"center\">" . $value . "</td></tr>\n";
    }
    echo "</td></tr></table>\n</td></tr></table>\n";
}
function getPeersTable($res, $frame_caption)
{
    global $lang;
    global $BASEURL;
    global $pic_base_url;
    echo "<h2>" . $frame_caption . "</h2>\n<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\"><tr><td align=\"center\">\n";
    echo "<table class=\"main\" width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n";
    echo "<tr>\r\n\t<td class=\"colhead\" align=\"center\" width=\"10%\">" . $lang->topten["rank"] . "</td>\r\n\t<td class=\"colhead\" align=\"center\" width=\"20%\">" . $lang->topten["user"] . "</td>\r\n\t<td class=\"colhead\" align=\"center\" width=\"15%\">" . $lang->topten["ulspeed"] . "</td>\r\n\t<td class=\"colhead\" align=\"center\" width=\"15%\">" . $lang->topten["dlspeed"] . "</td>\r\n\t</tr>";
    for ($n = 1; $arr = mysqli_fetch_array($res); $n++) {
        $report = "<span style=\"float: right;\"><a onclick=\"TSOpenPopup('" . $BASEURL . "/report.php?type=1&reporting=" . $arr["userid"] . "', 'report', 500, 300); return false;\" href=\"javascript:void(0);\"><img src=\"" . $pic_base_url . "report2.gif\" border=\"0\" alt=\"" . $lang->topten["reportuser"] . "\" title=\"" . $lang->topten["reportuser"] . "\" /></a></span>";
        echo "<tr><td align=\"center\">" . $n . "</td><td>" . $report . "<a href=\"" . ts_seo($arr["userid"], $arr["username"]) . "\"><b>" . get_user_color($arr["username"], $arr["namestyle"]) . "</b></td><td>" . mksize($arr["uprate"]) . "/s</td><td>" . mksize($arr["downrate"]) . "/s</td></tr>\n";
    }
    echo "</td></tr></table>\n</td></tr></table>\n";
}

?>