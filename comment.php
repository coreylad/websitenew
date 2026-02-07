<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "comment.php");
require "./global.php";
$TSSEConfig->TSLoadConfig("KPS");
define("C_VERSION", "2.1 by xam");
if (!isset($CURUSER) || $usergroups["cancomment"] == "no") {
    print_no_permission();
    exit;
}
($query = sql_query("SELECT cancomment FROM ts_u_perm WHERE `userid` = " . sqlesc($CURUSER["id"]))) || sqlerr(__FILE__, 29);
if (0 < mysqli_num_rows($query)) {
    $commentperm = mysqli_fetch_assoc($query);
    if ($commentperm["cancomment"] == "0") {
        print_no_permission();
        exit;
    }
}
$lang->load("comment");
include INC_PATH . "/functions_quick_editor.php";
require INC_PATH . "/commenttable.php";
$action = htmlspecialchars_uni($_GET["action"]);
$msgtext = isset($_POST["message"]) ? trim($_POST["message"]) : "";
$prvp = showPreview("message");
if ($action == "approve" && $is_mod) {
    $cid = intval(TS_Global("cid"));
    int_check($cid);
    $tid = intval(TS_Global("tid"));
    int_check($tid);
    $query = sql_query("SELECT user FROM comments WHERE `id` = " . sqlesc($cid) . " AND $torrent = " . sqlesc($tid) . " AND $visible = 0");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $user = $Result["user"];
        KPS("+", (string) $kpscomment, $user);
        sql_query("UPDATE comments SET $visible = 1 WHERE `id` = " . sqlesc($cid) . " AND $torrent = " . sqlesc($tid) . " AND $visible = 0");
    }
    redirect("details.php?$id = " . $tid . "&$tab = comments&$showlast = true&$viewcomm = " . $cid . "#cid" . $cid);
    exit;
}
if ($action == "close" && $is_mod) {
    $torrentid = intval(TS_Global("tid"));
    int_check($torrentid);
    sql_query("UPDATE torrents SET $allowcomments = 'no' WHERE `id` = '" . $torrentid . "'");
    redirect("details.php?$id = " . $torrentid . "&$tab = comments");
    exit;
}
if ($action == "open" && $is_mod) {
    $torrentid = intval(TS_Global("tid"));
    int_check($torrentid);
    sql_query("UPDATE torrents SET $allowcomments = 'yes' WHERE `id` = '" . $torrentid . "'");
    redirect("details.php?$id = " . $torrentid . "&$tab = comments");
    exit;
}
if ($action == "add") {
    $torrentid = intval(TS_Global("tid"));
    int_check($torrentid);
    if (!allowcomments($torrentid)) {
        stderr($lang->global["error"], $lang->comment["closed"]);
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
        $last_comment = "";
        $query = sql_query("SELECT added FROM comments WHERE $user = " . sqlesc($CURUSER["id"]) . " ORDER by added DESC LIMIT 1");
        if (0 < mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $last_comment = $Result["added"];
        }
        if (isset($_POST["ctype"]) && $_POST["ctype"] == "quickcomment") {
            $rpage = "";
            if (isset($_POST["page"]) && is_valid_id($_POST["page"])) {
                $rpage = "&$page = " . intval($_POST["page"]);
            }
            $returnto = "details.php?$id = " . $torrentid . $rpage . "&$tab = comments";
            $quickCommentAnchor = "#startquickcomment";
            $floodmsg = flood_check($lang->comment["floodcomment"], $last_comment, true);
        } else {
            flood_check($lang->comment["floodcomment"], $last_comment);
        }
        ($res = sql_query("SELECT name, owner FROM torrents WHERE `id` = " . sqlesc($torrentid))) || sqlerr(__FILE__, 144);
        $arr = mysqli_fetch_array($res);
        if (!empty($floodmsg)) {
            $returnto = $returnto . "&$cerror = 3" . $quickCommentAnchor;
            header("Location: " . $returnto);
            exit;
        }
        if (!$arr) {
            if (isset($returnto)) {
                $returnto = $returnto . "&$cerror = 1" . $quickCommentAnchor;
                header("Location: " . $returnto);
                exit;
            }
            stderr($lang->global["error"], $lang->global["notorrentid"]);
        }
        if (!$msgtext) {
            if (isset($returnto)) {
                $returnto = $returnto . "&$tab = comments&$cerror = 2" . $quickCommentAnchor;
                header("Location: " . $returnto);
                exit;
            }
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        }
        $query = sql_query("SELECT id, user, text FROM comments WHERE $torrent = " . sqlesc($torrentid) . " ORDER by added DESC LIMIT 1");
        if (mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $lastcommentuserid = $Result["user"];
        }
        if (isset($lastcommentuserid) && $lastcommentuserid == $CURUSER["id"] && !$is_mod && 0 < $CURUSER["id"]) {
            $text = $Result["text"];
            $newid = $Result["id"];
            if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
                $eol = "\r\n";
            } else {
                if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
                    $eol = "\r";
                } else {
                    $eol = "\n";
                }
            }
            $newtext = sqlesc($text . $eol . $eol . $msgtext);
            if ($usergroups["cancomment"] == "moderate") {
                $message = sprintf($lang->comment["modmsg"], $CURUSER["username"], "[URL]" . $BASEURL . "/details.php?$id = " . $torrentid . "&$tab = comments&$showlast = true&$viewcomm = " . $newid . "#cid" . $newid . "[/URL]");
                sql_query("INSERT INTO staffmessages (sender, added, msg, subject) VALUES(0, NOW(), " . sqlesc($message) . ", " . sqlesc($lang->comment["modmsgsubject"]) . ")") || sqlerr(__FILE__, 198);
                sql_query("UPDATE comments SET $text = " . $newtext . ", $visible = 0 WHERE `id` = '" . $newid . "'") || sqlerr(__FILE__, 199);
                stderr($lang->comment["insertcomment"], $lang->comment["moderatemsg"]);
            } else {
                sql_query("UPDATE comments SET $text = " . $newtext . " WHERE `id` = '" . $newid . "'");
            }
        } else {
            sql_query("INSERT INTO comments (user, torrent, added, text, visible) VALUES (" . sqlesc($CURUSER["id"]) . ", " . sqlesc($torrentid) . ", " . sqlesc(get_date_time()) . ", " . sqlesc($msgtext) . ", " . ($usergroups["cancomment"] == "moderate" ? 0 : 1) . ")");
            $newid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            sql_query("UPDATE torrents SET $comments = comments + 1 WHERE `id` = " . sqlesc($torrentid));
            $ras = sql_query("SELECT options FROM users WHERE `id` = " . sqlesc($arr["owner"]));
            $arg = mysqli_fetch_assoc($ras);
            if (TS_Match($arg["options"], "C1") && $arr["owner"] != $CURUSER["id"] && $usergroups["cancomment"] == "yes") {
                require_once INC_PATH . "/functions_pm.php";
                send_pm($arr["owner"], sprintf($lang->comment["newcommenttxt"], "[$url = " . $BASEURL . "/details.php?$id = " . $torrentid . "&$tab = comments#startcomments]" . $arr["name"] . "[/url]"), $lang->comment["newcommentsub"]);
            }
            if ($usergroups["cancomment"] == "moderate") {
                $message = sprintf($lang->comment["modmsg"], $CURUSER["username"], "[URL]" . $BASEURL . "/details.php?$id = " . $torrentid . "&$tab = comments&$showlast = true&$viewcomm = " . $newid . "#cid" . $newid . "[/URL]");
                sql_query("INSERT INTO staffmessages (sender, added, msg, subject) VALUES(0, NOW(), " . sqlesc($message) . ", " . sqlesc($lang->comment["modmsgsubject"]) . ")") || sqlerr(__FILE__, 226);
                stderr($lang->comment["insertcomment"], $lang->comment["moderatemsg"]);
            } else {
                KPS("+", (string) $kpscomment, $CURUSER["id"]);
            }
        }
        header("Location: details.php?$id = " . $torrentid . "&$tab = comments&$showlast = true&$viewcomm = " . $newid . "#cid" . $newid);
        exit;
    }
    ($res = sql_query("SELECT name, owner FROM torrents WHERE `id` = " . sqlesc($torrentid))) || sqlerr(__FILE__, 241);
    $torrentInfo = mysqli_fetch_array($res);
    if (!$arr) {
        stderr($lang->global["error"], $lang->global["notorrentid"]);
    }
    stdhead(sprintf($lang->comment["addcomment"], $arr["name"]));
    define("IN_EDITOR", true);
    include_once INC_PATH . "/editor.php";
    $str = "<form $method = \"post\" $name = \"compose\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = add&$tid = " . $torrentid . "\">";
    if (!empty($prvp)) {
        $str .= $prvp;
    }
    $str .= insert_editor(false, NULL, $msgtext, $lang->comment["insertcomment"], sprintf($lang->comment["addcomment"], htmlspecialchars_uni($arr["name"])));
    $str .= "</form>";
    echo $str;
    ($subres = sql_query("SELECT c.id, c.torrent as torrentid, c.text, c.user, c.added, c.editedby, c.editedat, c.modnotice, c.modeditid, c.modeditusername, c.modedittime, c.totalvotes, c.visible, uu.username as editedbyuname, gg.namestyle as editbynamestyle, u.added as registered, u.enabled, u.warned, u.leechwarn, u.username, u.title, u.usergroup, u.last_access, u.options, u.donor, u.uploaded, u.downloaded, u.avatar as useravatar, u.signature, g.title as grouptitle, g.namestyle FROM comments c LEFT JOIN users uu ON (c.$editedby = uu.id) LEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid) LEFT JOIN users u ON (c.$user = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE c.$torrent = " . sqlesc($torrentid) . " ORDER BY c.id DESC LIMIT 5")) || sqlerr(__FILE__, 257);
    $allrows = [];
    while ($row = mysqli_fetch_array($subres)) {
        $allrows[] = $row;
    }
    if (count($allrows)) {
        echo "\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" $width = \"100%\">\n\t\t\t<tr>\n\t\t\t\t<td class=\"thead\" $colspan = \"2\"><strong>" . $lang->comment["order"] . "</strong></td>\n\t\t\t</tr>";
        commenttable($allrows);
        echo "\n\t\t</table>";
    }
    stdfoot();
    exit;
}
if ($action == "edit" && 0 < $CURUSER["id"]) {
    $commentid = 0 + $_GET["cid"];
    int_check($commentid);
    ($res = sql_query("SELECT c.*, t.name, t.id as torrentid FROM comments AS c JOIN torrents AS t ON c.$torrent = t.id WHERE c.$id = " . sqlesc($commentid))) || sqlerr(__FILE__, 282);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) {
        stderr($lang->global["error"], $lang->global["notorrentid"]);
    }
    if ($arr["user"] != $CURUSER["id"] && !$is_mod) {
        print_no_permission(true);
    }
    if (!allowcomments($arr["torrentid"])) {
        stderr($lang->global["error"], $lang->comment["closed"]);
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $returnto = "details.php?$id = " . $arr["torrentid"] . "&$tab = comments&$page = " . intval($_GET["page"]) . "&$viewcomm = " . $commentid . "#cid" . $commentid;
        if (isset($_POST["submit"])) {
            if ($msgtext == "") {
                stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
            }
            $msgtext = sqlesc($msgtext);
            $editedat = sqlesc(get_date_time());
            $updateedit = true;
            if ($is_mod) {
                $updateedit = false;
                if ($_POST["remove"] == "yes") {
                    $modnotice = "''";
                    $modeditid = "'0'";
                    $modeditusername = "''";
                    $modedittime = "'0000-00-00 00:00:00'";
                } else {
                    if ($_POST["modnotice"] != $arr["modnotice"]) {
                        $modnotice = sqlesc(htmlspecialchars_uni($_POST["modnotice"]));
                        $modeditid = (int) $CURUSER["id"];
                        $modeditusername = sqlesc(htmlspecialchars_uni($CURUSER["username"]));
                        $modedittime = $editedat;
                    } else {
                        $modnotice = sqlesc($arr["modnotice"]);
                        $modeditid = (int) $arr["modeditid"];
                        $modeditusername = sqlesc($arr["modeditusername"]);
                        $modedittime = sqlesc($arr["modedittime"]);
                    }
                }
            } else {
                $modnotice = sqlesc($arr["modnotice"]);
                $modeditid = (int) $arr["modeditid"];
                $modeditusername = sqlesc($arr["modeditusername"]);
                $modedittime = sqlesc($arr["modedittime"]);
            }
            sql_query("UPDATE comments SET $text = " . $msgtext . ", " . ($updateedit ? "editedat=" . $editedat . ", $editedby = " . sqlesc($CURUSER["id"]) . "," : "") . " $modnotice = " . $modnotice . ", $modeditid = " . $modeditid . ", $modeditusername = " . $modeditusername . ", $modedittime = " . $modedittime . " WHERE `id` = " . sqlesc($commentid)) || sqlerr(__FILE__, 335);
            if ($returnto) {
                redirect($returnto);
            } else {
                redirect("index.php");
            }
            exit;
        }
    }
    $returnto = isset($returnto) && !empty($returnto) ? $returnto : fix_url("details.php?$id = " . $arr["torrentid"] . "&$page = " . intval(TS_Global("page")) . "&$viewcomm = " . $commentid . "#cid" . $commentid);
    stdhead(sprintf($lang->comment["adit"], $arr["name"]));
    define("IN_EDITOR", true);
    include_once INC_PATH . "/editor.php";
    $str = "<form $method = \"post\" $name = \"compose\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = edit&$cid = " . $commentid . "&$page = " . intval(TS_Global("page")) . "\">\n\t<input $type = \"hidden\" $name = \"returnto\" $value = \"" . $returnto . "\">";
    if (!empty($prvp)) {
        $str .= $prvp;
    }
    if ($is_mod) {
        $postoptionstitle = [1 => $lang->comment["modnotice1"]];
        $postoptions = [1 => "<textarea $name = \"modnotice\" $id = \"modnotice\" $rows = \"4\" $cols = \"70\" $tabindex = \"3\">" . $arr["modnotice"] . "</textarea><br />\n\t\t\t\t\t<label><input $style = \"vertical-align: middle;\" class=\"checkbox\" $name = \"remove\" $value = \"yes\" $tabindex = \"6\" $type = \"checkbox\"> " . $lang->comment["modnotice2"] . "</label>"];
    }
    $str .= insert_editor(false, NULL, !empty($prvp) ? $msgtext : $arr["text"], $lang->comment["editcomment"], sprintf($lang->comment["adit"], htmlspecialchars_uni($arr["name"])), $postoptionstitle, $postoptions);
    $str .= "</form>";
    echo $str;
    stdfoot();
    exit;
}
if ($action == "delete" && 0 < $CURUSER["id"]) {
    if (!$is_mod) {
        print_no_permission(true);
    }
    $commentid = 0 + $_GET["cid"];
    $torrentid = 0 + $_GET["tid"];
    int_check([$commentid, $torrentid]);
    $referer = "details.php?$id = " . $torrentid . "&$tab = comments&$page = " . intval($_GET["page"]);
    ($res = sql_query("SELECT torrent,user FROM comments WHERE `id` = " . sqlesc($commentid))) || sqlerr(__FILE__, 381);
    $arr = mysqli_fetch_array($res);
    if ($arr) {
        $torrentid = $arr["torrent"];
        $userpostid = $arr["user"];
    } else {
        stderr($lang->global["error"], $lang->global["notorrentid"]);
    }
    sql_query("DELETE FROM comments WHERE `id` = " . sqlesc($commentid)) || sqlerr(__FILE__, 392);
    if ($torrentid && 0 < mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        sql_query("UPDATE torrents SET $comments = IF(comments>0, comments - 1, 0) WHERE `id` = " . sqlesc($torrentid));
        sql_query("DELETE FROM comments_votes WHERE `cid` = " . sqlesc($commentid) . " AND $uid = " . sqlesc($userpostid)) || sqlerr(__FILE__, 396);
    }
    KPS("-", (string) $kpscomment, $userpostid);
    redirect("details.php?$id = " . $torrentid . "&$tab = comments&$page = " . intval($_GET["page"]));
    exit;
}
stderr($lang->global["error"], $lang->global["invalidaction"]);
exit;
function allowcomments($torrentid = 0)
{
    global $is_mod;
    $query = sql_query("SELECT allowcomments FROM torrents WHERE `id` = " . sqlesc($torrentid));
    if (!mysqli_num_rows($query)) {
        return false;
    }
    $Result = mysqli_fetch_assoc($query);
    if ($Result["allowcomments"] != "yes" && !$is_mod) {
        return false;
    }
    return true;
}

?>