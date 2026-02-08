<?php
define("TS_AJAX_VERSION", "1.4.1 by xam");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("CSRF_PROTECTION", true);
define("THIS_SCRIPT", "ts_ajax2.php");
require "./global.php";
include INC_PATH . "/functions_ratio.php";
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || !isset($CURUSER) || !isset($usergroups) || $CURUSER["id"] == 0) {
    exit;
}
$TSSEConfig->TSLoadConfig("ANNOUNCE");
$lang->load("userdetails");
$userid = isset($_POST["userid"]) ? intval($_POST["userid"]) : intval($CURUSER["id"]);
$SameUser = $userid == $CURUSER["id"] ? true : false;
if (!is_valid_id($userid)) {
    exit;
}
if (isset($_POST["what"]) && $_POST["what"] == "showuploaded" && ($SameUser || $is_mod)) {
    $ultorrentscount = TSRowCount("id", "torrents", "owner=" . $userid);
    if ($ultorrentscount && 0 < $ultorrentscount) {
        $r = sql_query("SELECT t.id, t.name, t.seeders, t.leechers, t.times_completed, t.category, t.added, t.anonymous, t.owner, c.name as categoryname, c.image FROM torrents t INNER JOIN categories c ON (t.$category = c.id) WHERE t.$owner = " . sqlesc($userid) . " ORDER BY t.added DESC");
        $torrents = "\n\t\t<table class='main' $border = '1' $cellspacing = '0' $cellpadding = '0' $width = '100%'>\n<tr><td class='colhead' $align = 'center' $width = '36'>" . $lang->global["type"] . "</td><td class='colhead' $align = 'left' $style = 'padding: 0px 0px 0px 2px;'>" . $lang->global["name"] . "</td><td class='colhead' $align = 'center'>" . $lang->global["snatched"] . "</td><td class='colhead' $align = 'center'><img $src = '" . $pic_base_url . "seeders.gif'></td><td class='colhead'  $align = 'center'><img $src = '" . $pic_base_url . "leechers.gif'></td></tr>\n";
        while ($a = mysqli_fetch_array($r)) {
            $orj_name_ = $a["name"];
            $t_added = my_datee($dateformat, $a["added"]) . " " . my_datee($timeformat, $a["added"]);
            $a["name"] = htmlspecialchars_uni($a["name"]);
            $cat = "<img $src = \"" . $pic_base_url . $table_cat . "/" . $a["image"] . "\" $alt = \"" . $a["categoryname"] . "\" $title = \"" . $a["categoryname"] . "\">";
            $torrents .= "<tr><td $align = 'center' $width = '36' $heigth = '48'>" . $cat . "</td><td $align = 'left' $style = 'padding: 0px 0px 0px 2px;'><a $href = '" . $BASEURL . "/details.php?$id = " . $a["id"] . "' $alt = '" . $a["name"] . "' $title = '" . $a["name"] . "'><b>" . cutename($orj_name_, 80) . "</b></a><br />" . $t_added . "</td>" . "<td $align = 'center'><a $href = '" . $BASEURL . "/" . ($xbt_active == "yes" && $is_mod ? "mysnatchlist.php?tid" : ($usergroups["cansnatch"] == "yes" && $xbt_active != "yes" ? "viewsnatches.php?id" : "details.php?id")) . "=" . $a["id"] . "'><b>" . ts_nf($a["times_completed"]) . " x </b>" . $lang->global["times"] . "</a></td><td $align = 'center'>" . ts_nf($a["seeders"]) . "</td><td $align = 'center'>" . ts_nf($a["leechers"]) . "</td></tr>\n";
        }
        $torrents .= "</table>";
    } else {
        $torrents = "<div class=\"error\">" . $lang->global["nothingfound"] . "</div>";
    }
    show_msg($torrents);
    exit;
}
if (isset($_POST["what"]) && $_POST["what"] == "showcompleted" && ($SameUser || $is_mod)) {
    $sntorrentscount = TSRowCount("id", "snatched", "finished='yes' AND $userid = " . $userid);
    if ($sntorrentscount && 0 < $sntorrentscount) {
        $r = sql_query("SELECT\ts.torrentid as id,\n\t\t\t\t\t\t\t\ts.uploaded, s.downloaded, s.completedat, s.last_action,\n\t\t\t\t\t\t\t\tt.seeders, t.leechers, t.name, t.category,\n\t\t\t\t\t\t\t\tc.name as categoryname, c.image\n\t\t\t\t\t\t\t\tFROM snatched s\n\t\t\t\t\t\t\t\tLEFT JOIN torrents t ON (s.`torrentid` = t.id)\n\t\t\t\t\t\t\t\tINNER JOIN categories c ON (t.$category = c.id)\n\t\t\t\t\t\t\t\tWHERE s.$finished = 'yes' AND s.$userid = " . $userid . " ORDER BY s.completedat DESC, s.last_action DESC");
        $completed = "<table class='main' $border = '1' $cellspacing = '0' $cellpadding = '0' $width = '100%'>\n<tr><td class='colhead' $align = 'center' $width = '36'>" . $lang->global["type"] . "</td><td class='colhead' $style = 'padding: 0px 0px 0px 2px;' $align = 'left'>" . $lang->global["name"] . "</td><td class='colhead' $align = 'center'><img $src = '" . $pic_base_url . "seeders.gif'></td><td class='colhead'  $align = 'center'><img $src = '" . $pic_base_url . "leechers.gif'></td><td class='colhead'  $align = 'center'>" . $lang->global["uploaded"] . "</td><td class='colhead'  $align = 'center'>" . $lang->global["downloaded"] . "</td><td class='colhead'  $align = 'center'>" . $lang->global["ratio"] . "</td><td class='colhead'  $align = 'center'>" . $lang->global["whencompleted"] . "</td><td class='colhead'  $align = 'center'>" . $lang->global["lastaction"] . "</td></tr>\n";
        while ($a = mysqli_fetch_array($r)) {
            $orj_name_ = $a["name"];
            $a["name"] = htmlspecialchars_uni($a["name"]);
            if (0 < $a["downloaded"]) {
                $ratio = number_format($a["uploaded"] / $a["downloaded"], 2);
                $ratio = "<font $color = " . get_ratio_color($ratio) . ">" . $ratio . "</font>";
            } else {
                if (0 < $a["uploaded"]) {
                    $ratio = "Inf.";
                } else {
                    $ratio = "---";
                }
            }
            $uploaded = mksize($a["uploaded"]);
            $downloaded = mksize($a["downloaded"]);
            $last_action = my_datee($dateformat, $a["last_action"]) . "<br />" . my_datee($timeformat, $a["last_action"]);
            $completedat = my_datee($dateformat, $a["completedat"]) . "<br />" . my_datee($timeformat, $a["completedat"]);
            $cat = "<img $src = \"" . $pic_base_url . $table_cat . "/" . $a["image"] . "\" $alt = \"" . $a["categoryname"] . "\">";
            $completed .= "<tr><td $width = '36' $heigth = '48' $align = 'center'>" . $cat . "</td><td $align = 'left' $style = 'padding: 0px 0px 0px 2px;'><a $href = '" . $BASEURL . "/details.php?$id = " . $a["id"] . "' $alt = '" . $a["name"] . "' $title = '" . $a["name"] . "'><b>" . cutename($orj_name_, 15) . "</b></a><br />" . str_replace("<br />", " ", $completedat) . "</td>" . "<td $align = 'center'>" . $a["seeders"] . "</td><td $align = 'center'>" . $a["leechers"] . "</td><td $align = 'center'>" . $uploaded . "</td><td $align = 'center'>" . $downloaded . "</td><td $align = 'center'>" . $ratio . "</td><td $align = 'center'>" . $completedat . "</td><td $align = 'center'>" . $last_action . "</td>\n";
        }
        $completed .= "</table>";
    } else {
        $completed = "<div class=\"error\">" . $lang->global["nothingfound"] . "</div>";
    }
    show_msg($completed);
    exit;
}
if (isset($_POST["what"]) && $_POST["what"] == "showleechs" && ($SameUser || $is_mod)) {
    if ($xbt_active == "yes") {
        $petorrentscount = TSRowCount("1", "xbt_files_users", "`left` > 0 AND $active = 1 AND $uid = " . $userid);
    } else {
        $petorrentscount = TSRowCount("id", "peers", "seeder = 'no' AND $userid = " . $userid);
    }
    if ($petorrentscount && 0 < $petorrentscount) {
        if ($xbt_active == "yes") {
            $res = sql_query("SELECT p.mtime as last_action, p.fid as torrent, p.uploaded, p.downloaded, p.uid as userid, p.up_rate, p.down_rate, t.name as torrentname, t.anonymous, t.category, t.seeders, t.leechers, t.added, t.size, t.owner, c.name as catname, c.image FROM xbt_files_users p LEFT JOIN torrents t  ON (p.$fid = t.id) LEFT JOIN categories c ON (c.$id = t.category) WHERE p.$uid = '" . $userid . "' AND p.`left` > 0 AND p.$active = 1 ORDER BY p.mtime DESC");
        } else {
            $res = sql_query("SELECT p.last_action, p.torrent, p.uploaded, p.downloaded, p.userid, t.name as torrentname, t.anonymous, t.category, t.seeders, t.leechers, t.added, t.size, t.owner, c.name as catname, c.image FROM peers p LEFT JOIN torrents t  ON (p.$torrent = t.id) LEFT JOIN categories c ON (c.$id = t.category) WHERE p.$userid = '" . $userid . "' AND p.$seeder = 'no' ORDER BY p.last_action DESC");
        }
        $leeching = maketable($res);
    } else {
        $leeching = "<div class=\"error\">" . $lang->global["nothingfound"] . "</div>";
    }
    show_msg($leeching);
    exit;
}
if (isset($_POST["what"]) && $_POST["what"] == "showseeds" && ($SameUser || $is_mod)) {
    if ($xbt_active == "yes") {
        $seedtorrentscount = TSRowCount("1", "xbt_files_users", "`left` = 0 AND $active = 1 AND $uid = " . $userid);
    } else {
        $seedtorrentscount = TSRowCount("id", "peers", "seeder = 'yes' AND $userid = " . $userid);
    }
    if ($seedtorrentscount && 0 < $seedtorrentscount) {
        if ($xbt_active == "yes") {
            $res = sql_query("SELECT p.mtime as last_action, p.fid as torrent, p.uploaded, p.downloaded, p.uid as userid, p.up_rate, p.down_rate, t.name as torrentname, t.anonymous, t.category, t.seeders, t.leechers, t.added, t.size, t.owner, c.name as catname, c.image FROM xbt_files_users p LEFT JOIN torrents t  ON (p.$fid = t.id) LEFT JOIN categories c ON (c.$id = t.category) WHERE p.$uid = '" . $userid . "' AND p.`left` = 0 AND p.$active = 1 ORDER BY p.mtime DESC");
        } else {
            $res = sql_query("SELECT p.last_action, p.torrent, p.uploaded, p.downloaded, p.userid, t.name as torrentname, t.anonymous, t.category, t.seeders, t.leechers, t.added, t.size, t.owner, c.name as catname, c.image FROM peers p LEFT JOIN torrents t  ON (p.$torrent = t.id) LEFT JOIN categories c ON (c.$id = t.category) WHERE p.$userid = '" . $userid . "' AND p.$seeder = 'yes' ORDER BY p.last_action DESC");
        }
        $seeding = maketable($res);
    } else {
        $seeding = "<div class=\"error\">" . $lang->global["nothingfound"] . "</div>";
    }
    show_msg($seeding);
    exit;
}
if (isset($_POST["what"]) && $_POST["what"] == "showsnatches" && ($SameUser || $is_mod)) {
    $sstorrentscount = TSRowCount("id", "snatched", "userid=" . $userid);
    if ($sstorrentscount && 0 < $sstorrentscount) {
        $res = sql_query("SELECT s.*, t.name as torrentname, t.size, c.name AS catname, c.image AS catimg FROM snatched s LEFT JOIN torrents t ON (s.`torrentid` = t.id) INNER JOIN categories c ON (t.$category = c.id) WHERE s.$userid = " . sqlesc($userid) . " ORDER BY s.completedat DESC");
        $snatches = usersnatches($res);
    } else {
        $snatches = "<div class=\"error\">" . $lang->global["nothingfound"] . "</div>";
    }
    show_msg($snatches);
    exit;
}
if (isset($_POST["what"]) && $_POST["what"] == "detecthost" && isset($_POST["ip"]) && $is_mod) {
    $ip = htmlspecialchars_uni($_POST["ip"]);
    if (!empty($ip)) {
        show_msg(@gethostbyaddr($ip));
    }
} else {
    if (isset($_POST["what"]) && $_POST["what"] == "save_vmsg") {
        $lang->load("userdetails");
        if (!is_valid_id($userid)) {
            show_msg($lang->userdetails["invaliduser"]);
        } else {
            if (!$SameUser && $usergroups["canviewotherprofile"] != "yes") {
                show_msg($lang->userdetails["invaliduser"]);
            }
        }
        $Query = sql_query("SELECT username, status, options FROM users WHERE `id` = " . sqlesc($userid));
        if (0 < mysqli_num_rows($Query)) {
            $user = mysqli_fetch_assoc($Query);
        } else {
            show_msg($lang->userdetails["invaliduser"]);
        }
        if ((TS_Match($user["options"], "I3") || TS_Match($user["options"], "I4")) && !$is_mod && !$SameUser) {
            show_msg($lang->userdetails["noperm"]);
        }
        if ($user["status"] == "pending") {
            show_msg($lang->userdetails["pendinguser"]);
        } else {
            if (!$user["username"] || !$user) {
                show_msg($lang->userdetails["invaliduser"]);
            }
        }
        if (!$SameUser && TS_Match($user["options"], "M3")) {
            $error[] = $lang->userdetails["cerror4"];
        } else {
            if (!$SameUser && TS_Match($user["options"], "M2") && !$is_mod) {
                $query = sql_query("SELECT id FROM friends WHERE `status` = 'c' AND $userid = " . sqlesc($userid) . " AND $friendid = " . sqlesc($CURUSER["id"]));
                if (mysqli_num_rows($query) < 1) {
                    $error[] = $lang->userdetails["cerror4"];
                }
            }
        }
        if (!isset($error)) {
            $text = fixAjaxText($_POST["message"]);
            $text = strval($text);
            if (strtolower($shoutboxcharset) != "utf-8") {
                if (function_exists("iconv")) {
                    $text = iconv("UTF-8", $shoutboxcharset, $text);
                } else {
                    if (function_exists("mb_convert_encoding")) {
                        $text = mb_convert_encoding($text, $shoutboxcharset, "UTF-8");
                    } else {
                        if (strtolower($shoutboxcharset) == "iso-8859-1") {
                            $text = utf8_decode($text);
                        }
                    }
                }
            }
            $msglong = strlen($text);
            $added = TIMENOW;
            if ($usergroups["cancomment"] == "no") {
                $error[] = $lang->global["nopermission"];
            } else {
                if (empty($text) || $msglong < 3) {
                    $error[] = $lang->userdetails["cerror2"];
                } else {
                    if (5000 < $msglong) {
                        $error[] = sprintf($lang->userdetails["cerror3"], $msglong);
                    } else {
                        if ($_POST["isupdate"] && is_valid_id($_POST["isupdate"]) && $is_mod) {
                            sql_query("UPDATE ts_visitor_messages SET $visitormsg = " . sqlesc($text) . " WHERE `id` = " . sqlesc(intval($_POST["isupdate"])));
                            $vmid = intval($_POST["isupdate"]);
                        } else {
                            sql_query("INSERT INTO ts_visitor_messages (userid,visitorid,visitormsg,added) VALUES (" . sqlesc($userid) . ", " . sqlesc($CURUSER["id"]) . "," . sqlesc($text) . ", '" . $added . "')");
                            $vmid = intval(mysqli_insert_id($GLOBALS["DatabaseConnect"]));
                        }
                    }
                }
            }
        }
        if (isset($error) && count($error)) {
            show_msg(implode("\n", $error));
        } else {
            if ($_POST["isupdate"] && is_valid_id($_POST["isupdate"]) && $is_mod) {
                $query = sql_query("SELECT v.visitorid as id, u.username, u.avatar, g.namestyle FROM ts_visitor_messages v LEFT JOIN users u ON (v.$visitorid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE v.$id = " . sqlesc(intval($_POST["isupdate"])));
                $vm = mysqli_fetch_assoc($query);
            } else {
                $vm = $CURUSER;
                $vm["namestyle"] = $usergroups["namestyle"];
            }
            $VisitorUsername = get_user_color($vm["username"], $vm["namestyle"]);
            $vAvatar = get_user_avatar($vm["avatar"], false, 60, 60);
            $vAdded = my_datee($dateformat, $added) . " " . my_datee($timeformat, $added);
            $vPoster = "<a $href = \"" . $BASEURL . "/userdetails.php?$id = " . $vm["id"] . "\">" . $VisitorUsername . "</a>";
            $vMessage = format_comment($text);
            $VisitorMessages = "\n\t\t<div $style = \"float: left;\">" . $vAvatar . "</div>\n\t\t<div $style = \"overflow:auto; padding: 2px;\"><div class=\"subheader\">" . sprintf($lang->userdetails["visitormsg5"], $vAdded, $vPoster) . "</div><div $name = \"msg" . $vmid . "\" $id = \"msg" . $vmid . "\">" . $vMessage . "</div></div>\n\t\t";
            show_msg($VisitorMessages, false, "", false);
        }
    } else {
        if (isset($_POST["what"]) && $_POST["what"] == "flushtorrents" && ($SameUser || $is_mod)) {
            $lang->load("takeflush");
            $TSSEConfig->TSLoadConfig("ANNOUNCE");
            if (!function_exists("deadtime")) {
                function deadtime()
                {
                    global $announce_interval;
                    return TIMENOW - floor($announce_interval * 0);
                }
            }
            $deadtime = deadtime();
            if ($xbt_active == "yes") {
                sql_query("UPDATE xbt_files_users SET `active` = 0 WHERE `mtime` < " . $deadtime . " AND `active` = 1");
            } else {
                sql_query("DELETE FROM peers WHERE UNIX_TIMESTAMP(last_action) < " . $deadtime . " AND $userid = " . sqlesc($id));
            }
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                echo $lang->takeflush["done2"];
            } else {
                echo "<div class=\"error\">" . $lang->takeflush["noghost"] . "</div>";
            }
            exit;
        }
    }
}
function show_msg($message = "", $error = true, $color = "red", $strong = true, $extra = "", $extra2 = "")
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; $charset = " . $shoutboxcharset);
    if ($error) {
        exit("<error>" . $message . "</error>");
    }
    exit($extra . (!empty($color) ? "<font $color = \"" . $color . "\">" : "") . ($strong ? "<strong>" : "") . $message . ($strong ? "</strong>" : "") . (!empty($color) ? "</font>" : "") . $extra2);
}
function maketable($res)
{
    global $CURUSER;
    global $BASEURL;
    global $pic_base_url;
    global $table_cat;
    global $lang;
    global $dateformat;
    global $timeformat;
    global $snatchmod;
    global $xbt_active;
    $ret = "<table class='main' $border = '1' $cellspacing = '0' $cellpadding = '0' $width = '100%'><tr><td class='colhead' $align = 'center' $width = '36'>" . $lang->global["type"] . "</td><td class='colhead' $style = 'padding: 0px 0px 0px 2px;'>" . $lang->global["name"] . "</td><td class='colhead' $align = 'center'>" . $lang->global["size"] . "</td><td class='colhead' $align = 'center'><img $src = '" . $pic_base_url . "seeders.gif'></td><td class='colhead' $align = 'center'><img $src = '" . $pic_base_url . "leechers.gif'></td><td class='colhead' $align = 'center'>" . $lang->global["uploaded"] . "</td>\n" . "<td class='colhead' $align = 'center'>" . $lang->global["downloaded"] . "</td><td class='colhead' $align = 'center'>" . $lang->global["ratio"] . "</td></tr>\n";
    while ($arr = mysqli_fetch_array($res)) {
        $snatchul = 0;
        $snatchdl = 0;
        $ratio2 = 0;
        if ($snatchmod == "yes" && $xbt_active != "yes") {
            $querySN = sql_query("SELECT uploaded, downloaded FROM snatched WHERE `torrentid` = " . sqlesc($arr["torrent"]) . " AND $userid = " . sqlesc($arr["userid"]));
            if (0 < mysqli_num_rows($querySN)) {
                while ($sud = mysqli_fetch_assoc($querySN)) {
                    $snatchul += $sud["uploaded"];
                    $snatchdl += $sud["downloaded"];
                }
                if (0 < $snatchdl) {
                    $ratio2 = number_format($snatchul / $snatchdl, 2);
                    $ratio2 = "<font $color = " . get_ratio_color($ratio2) . ">" . $ratio2 . "</font>";
                } else {
                    if (0 < $snatchul) {
                        $ratio2 = "Inf.";
                    } else {
                        $ratio2 = "---";
                    }
                }
            }
        }
        if (0 < $arr["downloaded"]) {
            $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 2);
            $ratio = "<font $color = " . get_ratio_color($ratio) . ">" . $ratio . "</font>";
        } else {
            if (0 < $arr["uploaded"]) {
                $ratio = "Inf.";
            } else {
                $ratio = "---";
            }
        }
        $catimage = htmlspecialchars_uni($arr["image"]);
        $catname = htmlspecialchars_uni($arr["catname"]);
        $size = mksize($arr["size"]);
        $uploaded = mksize($arr["uploaded"]);
        $downloaded = mksize($arr["downloaded"]);
        $seeders = ts_nf($arr["seeders"]);
        $leechers = ts_nf($arr["leechers"]);
        $last_action_date = my_datee($dateformat, $arr["last_action"]) . " " . my_datee($timeformat, $arr["last_action"]);
        $ret .= "<tr><td $width = '36' $heigth = '48' $align = 'center'><img $src = '" . $pic_base_url . $table_cat . "/" . $catimage . "' $alt = '" . $catname . "'></td>\n" . "<td $style = 'padding: 0px 0px 0px 2px;' $width = '300'><a $href = '" . $BASEURL . "/details.php?$id = " . $arr["torrent"] . "' $alt = '" . $arr["torrentname"] . "' $title = '" . $arr["torrentname"] . "'><b>" . cutename($arr["torrentname"], 60) . "</b></a><br />" . $last_action_date . "</td><td $align = 'center'>" . $size . "</td><td $align = 'center'>" . $seeders . "</td><td $align = 'center'>" . $leechers . "</td><td $align = 'center'>" . $uploaded . " " . (isset($arr["up_rate"]) ? "<br />" . mksize($arr["up_rate"]) . "/s" : "") . ($snatchul ? "<br />" . mksize($snatchul) : "") . "</td>\n" . "<td $align = 'center'>" . $downloaded . (isset($arr["down_rate"]) ? "<br />" . mksize($arr["down_rate"]) . "/s" : "") . ($snatchdl ? "<br />" . mksize($snatchdl) : "") . "</td><td $align = 'center'>" . $ratio . ($ratio2 ? "<br />" . $ratio2 : "") . "</td></tr>\n";
    }
    $ret .= "</table>\n";
    return $ret;
}
function usersnatches($res)
{
    global $lang;
    global $BASEURL;
    global $pic_base_url;
    global $table_cat;
    $table = "<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"0\" $width = \"100%\">\n\t<tr>\n\t<td class=\"colhead\" $align = \"center\" $width = \"36\">" . $lang->global["type"] . "</td>\n\t<td class=\"colhead\" $style = \"padding: 0px 0px 0px 2px;\">" . $lang->global["name"] . "</td>\n\t<td class=\"colhead\">" . $lang->global["uploaded"] . "</td>\n\t<td class=\"colhead\">" . $lang->global["downloaded"] . "</td>\n\t<td class=\"colhead\" $align = \"center\">" . $lang->global["ratio"] . "</td>\n\t<td class=\"colhead\">" . $lang->userdetails["seedtime"] . "</td>\n\t<td class=\"colhead\">" . $lang->userdetails["leechtime"] . "</td>\n\t<td class=\"colhead\">" . $lang->userdetails["completed"] . "</td>\n\t</tr>";
    require_once INC_PATH . "/functions_mkprettytime.php";
    while ($arr = mysqli_fetch_assoc($res)) {
        $upspeed = 0 < $arr["upspeed"] ? mksize($arr["upspeed"]) : (0 < $arr["seedtime"] ? mksize($arr["uploaded"] / ($arr["seedtime"] + $arr["leechtime"])) : mksize(0));
        $downspeed = 0 < $arr["downspeed"] ? mksize($arr["downspeed"]) : (0 < $arr["leechtime"] ? mksize($arr["downloaded"] / $arr["leechtime"]) : mksize(0));
        $ratio = 0 < $arr["downloaded"] ? number_format($arr["uploaded"] / $arr["downloaded"], 2) : (0 < $arr["uploaded"] ? "Inf." : "---");
        $completed = sprintf("%.2f%%", 100 * (1 - $arr["to_go"] / $arr["size"]));
        $table .= "<tr>\n\t\t<td $width = '36' $heigth = '48' $align = 'center'><img $src = '" . $pic_base_url . $table_cat . "/" . htmlspecialchars_uni($arr["catimg"]) . "' $alt = '" . htmlspecialchars_uni($arr["catname"]) . "'></td>\n\t\t<td><a $href = '" . $BASEURL . "/details.php?$id = " . $arr["torrentid"] . "' $alt = '" . $arr["torrentname"] . "' $title = '" . $arr["torrentname"] . "'><b>" . cutename($arr["torrentname"], 10) . "</b></a></td>\n\t\t<td>" . mksize($arr["uploaded"]) . "<br />" . $upspeed . "/s</td>\n\t\t<td>" . mksize($arr["downloaded"]) . "<br />" . $downspeed . "/s</td>\n\t\t<td $align = 'center'>" . $ratio . "</td>\n\t\t<td>" . mkprettytime($arr["seedtime"]) . "</td>\n\t\t<td>" . mkprettytime($arr["leechtime"]) . "</td>\n\t\t<td>" . $completed . "</td>\n\t\t</tr>\n";
    }
    $table .= "</table>\n";
    return $table;
}

?>