<?php


define("THIS_SCRIPT", "ts_blog.php");
require "./global.php";
if (!isset($CURUSER)) {
    print_no_permission();
}
$lang->load("ts_blog");
define("TSB_VERSION", "1.2 by xam");
// Main action for blog operations
$blogAction = isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"]) ? $_POST["do"] : "");
$blogErrors = [];
$blogImagePath = $pic_base_url . "ts_blog/";
$defaultTemplate = ts_template();
$templateImageDir = $BASEURL . "/include/templates/" . $defaultTemplate . "/images/";
$previewHtml = showPreview("message");
if ($blogAction == "create_blog") {
    if (!$is_mod && !blogpermission("cancreate")) {
        print_no_permission();
        exit;
    }
    $totalBlogs = mysqli_num_rows(sql_query("SELECT uid FROM ts_blogs WHERE $uid = " . sqlesc($CURUSER["id"])));
    $allowedMaxBlogs = intval(substr($usergroups["blogperms"], -1, 1));
    if (0 < $allowedMaxBlogs && $allowedMaxBlogs <= $totalBlogs) {
        stderr($lang->global["error"], sprintf($lang->ts_blog["maxallowed"], $totalBlogs, $allowedMaxBlogs), false);
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["submit"])) {
        $userId = intval($CURUSER["id"]);
        $blogTitle = trim($_POST["subject"]);
        $blogDescription = trim($_POST["message"]);
        $blogDate = TIMENOW;
        if (strlen($blogTitle) < 2 || strlen($blogDescription) < 10) {
            $blogErrors[] = $lang->ts_blog["editerror"];
        } else {
            sql_query("INSERT INTO ts_blogs (`uid`, `title`, `desc`, `date`) VALUES ('" . $userId . "', " . sqlesc($blogTitle) . ", " . sqlesc($blogDescription) . ", '" . $blogDate . "')") || sqlerr(__FILE__, 172);
            $blogId = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            if ($blogId) {
                header("Location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&$bid = " . $blogId . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : ""));
                exit;
            }
        }
    }
    $_TITLE = $lang->ts_blog["createb"];
    stdhead($_TITLE);
    add_breadcrumb(sprintf($lang->ts_blog["listtitle"], $SITENAME), $_SERVER["SCRIPT_NAME"]);
    add_breadcrumb($_TITLE);
    build_breadcrumb();
    show_blog_errors();
    define("IN_EDITOR", true);
    include_once INC_PATH . "/editor.php";
    echo "\r\n\t" . $previewHtml . "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=create_blog\">\r\n\t\t" . insert_editor(true, isset($blogTitle) ? $blogTitle : "", isset($blogDescription) ? $blogDescription : (isset($_POST["message"]) ? $_POST["message"] : ""), $lang->ts_blog["createb"], "", "", "", true, "", $lang->ts_blog["save"]) . "\r\n\t</form>\r\n\t";
    stdfoot();
    exit;
}
if ($do == "subscribe" && is_valid_id($BID = intval($_GET["bid"]))) {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    ($Query = sql_query("SELECT uid FROM ts_blogs WHERE $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 206);
    if (0 < mysqli_num_rows($Query)) {
        ($Query = sql_query("SELECT uid FROM ts_blogs_subscribe WHERE $bid = " . sqlesc($BID) . " AND $uid = " . sqlesc($CURUSER["id"]))) || sqlerr(__FILE__, 209);
        if (0 < mysqli_num_rows($Query)) {
            sql_query("DELETE FROM ts_blogs_subscribe WHERE $bid = " . sqlesc($BID) . " AND $uid = " . sqlesc($CURUSER["id"])) or sql_query("DELETE FROM ts_blogs_subscribe WHERE $bid = " . sqlesc($BID) . " AND $uid = " . sqlesc($CURUSER["id"])) || sqlerr(__FILE__, 212);
        } else {
            sql_query("INSERT INTO ts_blogs_subscribe VALUES (NULL, " . sqlesc($BID) . ", " . sqlesc($CURUSER["id"]) . ")") || sqlerr(__FILE__, 216);
        }
        $do = "show_blog";
    } else {
        $blog_error[] = $lang->ts_blog["invalidbid"];
    }
}
if ($do == "delete_blog" && is_valid_id($BID = intval($_GET["bid"])) && ($is_mod || blogpermission("candeleteb"))) {
    ($Query = sql_query("SELECT uid FROM ts_blogs WHERE $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 228);
    if (0 < mysqli_num_rows($Query)) {
        $Result = mysqli_fetch_assoc($Query);
        $owner = $Result["uid"];
        if (!$is_mod && $owner != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        sql_query("DELETE FROM ts_blogs WHERE $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 241);
        sql_query("DELETE FROM ts_blogs_comments WHERE $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 242);
        sql_query("DELETE FROM ts_blogs_subscribe WHERE $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 243);
        header("Location: " . $_SERVER["SCRIPT_NAME"]);
        exit;
    }
    $blog_error[] = $lang->ts_blog["invalidbid"];
}
if ($do == "edit_comment" && is_valid_id($CID = intval($_GET["cid"])) && is_valid_id($BID = intval($_GET["bid"])) && ($is_mod || blogpermission("caneditc"))) {
    ($Query = sql_query("SELECT uid, descr FROM ts_blogs_comments  WHERE $cid = " . sqlesc($CID) . " AND $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 255);
    if (0 < mysqli_num_rows($Query)) {
        $Comment = mysqli_fetch_assoc($Query);
        if (!$is_mod && $Comment["uid"] != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        ($Query = sql_query("SELECT `title`  FROM ts_blogs WHERE $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 269);
        if (0 < mysqli_num_rows($Query)) {
            $Blog = mysqli_fetch_assoc($Query);
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["submit"])) {
                $descr = trim($_POST["message"]);
                if (strlen($descr) < 3) {
                    $blog_error[] = $lang->ts_blog["editerror2"];
                } else {
                    sql_query("UPDATE ts_blogs_comments SET `descr` = " . sqlesc($descr) . " WHERE $cid = " . sqlesc($CID) . " AND $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 289);
                    header("Location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&$bid = " . $BID . "&$cid = " . $CID . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "#show_comments" . $CID);
                    exit;
                }
            }
            $BlogTitle = htmlspecialchars_uni($Blog["title"]);
            $TITLE = sprintf($lang->ts_blog["showblog"], $BlogTitle);
            $_TITLE = $lang->ts_blog["edit2"];
            stdhead($_TITLE);
            add_breadcrumb(sprintf($lang->ts_blog["listtitle"], $SITENAME), $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb($TITLE, $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $BID);
            add_breadcrumb($_TITLE);
            build_breadcrumb();
            show_blog_errors();
            define("IN_EDITOR", true);
            include_once INC_PATH . "/editor.php";
            echo "\r\n\t\t" . $prvp . "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_comment&$bid = " . $BID . "&$cid = " . $CID . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\">\r\n\t\t\t" . insert_editor(false, "", isset($descr) ? $descr : (isset($_POST["message"]) ? $_POST["message"] : $Comment["descr"]), $lang->ts_blog["edit2"], "", "", "", true, "", $lang->ts_blog["save"]) . "\r\n\t\t</form>\r\n\t\t";
            stdfoot();
            exit;
        }
        print_no_permission();
        exit;
    }
    $blog_error[] = $lang->ts_blog["invalidcid"];
}
if ($do == "delete_comment" && is_valid_id($CID = intval($_GET["cid"])) && is_valid_id($BID = intval($_GET["bid"])) && ($is_mod || blogpermission("candeletec"))) {
    ($Query = sql_query("SELECT uid FROM ts_blogs_comments WHERE $cid = " . sqlesc($CID) . " AND $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 325);
    if (0 < mysqli_num_rows($Query)) {
        $Result = mysqli_fetch_assoc($Query);
        $owner = $Result["uid"];
        if (!$is_mod && $owner != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        sql_query("DELETE FROM ts_blogs_comments WHERE $cid = " . sqlesc($CID) . " AND $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 339);
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            ($query = sql_query("SELECT uid, date FROM ts_blogs_comments WHERE $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 342);
            if (0 < mysqli_num_rows($query)) {
                $LastPostData = mysqli_fetch_assoc($query);
                sql_query("UPDATE ts_blogs SET $comments = IF(comments > 1, comments - 1, 0), $lastposter = " . sqlesc($LastPostData["uid"]) . ", $lastpostdate = " . sqlesc($LastPostData["date"]) . " WHERE $bid = " . sqlesc($BID)) or sql_query("UPDATE ts_blogs SET $comments = IF(comments > 1, comments - 1, 0), $lastposter = " . sqlesc($LastPostData["uid"]) . ", $lastpostdate = " . sqlesc($LastPostData["date"]) . " WHERE $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 346);
            } else {
                sql_query("UPDATE ts_blogs SET $comments = IF(comments > 1, comments - 1, 0), $lastposter = 0, $lastpostdate = 0 WHERE $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 350);
            }
        }
        header("Location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&$bid = " . $BID . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "#show_comments");
        exit;
    }
    $blog_error[] = $lang->ts_blog["invalidcid"];
}
if ($do == "edit_entry" && is_valid_id($BID = intval($_GET["bid"])) && ($is_mod || blogpermission("caneditb"))) {
    ($Query = sql_query("SELECT `uid`, `title`, `desc` FROM ts_blogs WHERE $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 364);
    if (0 < mysqli_num_rows($Query)) {
        $Blog = mysqli_fetch_assoc($Query);
        if (!$is_mod && $Blog["uid"] != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["submit"])) {
            $title = trim($_POST["subject"]);
            $desc = trim($_POST["message"]);
            if (strlen($title) < 3 || strlen($desc) < 10) {
                $blog_error[] = $lang->ts_blog["editerror"];
            } else {
                sql_query("UPDATE ts_blogs SET `title` = " . sqlesc($title) . ", `desc` = " . sqlesc($desc) . ", `updatedate` = " . sqlesc(TIMENOW) . ", `updatedby` = " . sqlesc($CURUSER["id"]) . " WHERE $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 387);
                header("Location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&$bid = " . $BID . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : ""));
                exit;
            }
        }
        $BlogTitle = htmlspecialchars_uni($Blog["title"]);
        $TITLE = sprintf($lang->ts_blog["showblog"], $BlogTitle);
        $_TITLE = $lang->ts_blog["edit"] . ": " . htmlspecialchars_uni($Blog["title"]);
        stdhead($_TITLE);
        add_breadcrumb(sprintf($lang->ts_blog["listtitle"], $SITENAME), $_SERVER["SCRIPT_NAME"]);
        add_breadcrumb($TITLE, $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $BID);
        add_breadcrumb($_TITLE);
        build_breadcrumb();
        show_blog_errors();
        define("IN_EDITOR", true);
        include_once INC_PATH . "/editor.php";
        echo "\r\n\t\t" . $prvp . "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_entry&$bid = " . $BID . "\">\r\n\t\t\t" . insert_editor(true, isset($title) ? $title : $Blog["title"], isset($desc) ? $desc : (isset($_POST["message"]) ? $_POST["message"] : $Blog["desc"]), $lang->ts_blog["edit"], htmlspecialchars_uni($Blog["title"]), "", "", true, "", $lang->ts_blog["save"]) . "\r\n\t\t</form>\r\n\t\t";
        stdfoot();
        exit;
    }
    $blog_error[] = $lang->ts_blog["invalidbid"];
}
if ($do == "enable_disable_comments" && is_valid_id($BID = intval($_GET["bid"])) && ($is_mod || blogpermission("candisablec"))) {
    ($Query = sql_query("SELECT uid FROM ts_blogs WHERE $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 422);
    if (0 < mysqli_num_rows($Query)) {
        $Result = mysqli_fetch_assoc($Query);
        $owner = $Result["uid"];
        if (!$is_mod && $owner != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        sql_query("UPDATE ts_blogs SET $allowcomments = IF($allowcomments = 1, 0, 1) WHERE $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 435);
        header("Location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&$bid = " . $BID . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : ""));
        exit;
    }
    $blog_error[] = $lang->ts_blog["invalidbid"];
}
if ($do == "save_comment" && is_valid_id($BID = intval($_GET["bid"])) && ($is_mod || blogpermission("canpost"))) {
    $Query = sql_query("SELECT uid, allowcomments FROM ts_blogs WHERE $bid = " . sqlesc($BID));
    if (mysqli_num_rows($Query) == 0) {
        $blog_error[] = $lang->ts_blog["disabled"];
    } else {
        $Blog = mysqli_fetch_assoc($Query);
        if (!$is_mod && $Blog["allowcomments"] == 0) {
            $blog_error[] = $lang->ts_blog["disabled"];
        }
    }
    $message = fixAjaxText($_POST["message"]);
    if (strlen($message) < 2) {
        $blog_error[] = $lang->global["dontleavefieldsblank"];
    }
    if (count($blog_error) == 0) {
        $TIME = TIMENOW;
        sql_query("INSERT INTO ts_blogs_comments (bid, uid, date, descr) VALUES (" . sqlesc($BID) . ", " . sqlesc($CURUSER["id"]) . ", " . sqlesc($TIME) . ", " . sqlesc($message) . ")");
        $CID = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        if ($CID) {
            if ($Blog["uid"] != $CURUSER["id"]) {
                require_once INC_PATH . "/functions_pm.php";
                send_pm($Blog["uid"], sprintf($lang->ts_blog["s4"], $BASEURL . "/ts_blog.php?do=show_blog&$bid = " . $BID . "&$cid = " . $CID . "#show_comments" . $CID), $lang->ts_blog["s5"]);
            }
            send_pm($Blog["uid"], sprintf($lang->ts_blog["s4"], $BASEURL . "/ts_blog.php?do=show_blog&$bid = " . $BID . "&$cid = " . $CID . "#show_comments" . $CID), $lang->ts_blog["s5"]);
            $query = sql_query("SELECT uid FROM ts_blogs_subscribe WHERE $bid = " . sqlesc($BID) . " AND uid != " . sqlesc($CURUSER["id"]));
            if (0 < mysqli_num_rows($query)) {
                require_once INC_PATH . "/functions_pm.php";
                while ($User = mysqli_fetch_assoc($query)) {
                    send_pm($User["uid"], sprintf($lang->ts_blog["s6"], $BASEURL . "/ts_blog.php?do=show_blog&$bid = " . $BID . "&$cid = " . $CID . "#show_comments" . $CID), $lang->ts_blog["s5"]);
                }
            }
        }
        header("location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&$bid = " . $BID . "&$cid = " . $CID . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "#show_comments" . $CID);
        exit;
    }
    $do = "show_blog";
}
if ($do == "show_blog" && is_valid_id($BID = intval($_GET["bid"]))) {
    if (!$is_mod && !blogpermission("canview")) {
        print_no_permission();
        exit;
    }
    ($Query = sql_query("SELECT b.uid, b.title, b.desc, b.views, b.comments, b.date, b.allowcomments, b.updatedate, b.updatedby, u.username, u.added, u.last_access, u.last_login, u.options, u.avatar, g.namestyle, uu.username as updater, gg.namestyle as updaterns, s.uid as issubs FROM ts_blogs b LEFT JOIN users u ON (b.$uid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) LEFT JOIN users uu ON (b.$updatedby = uu.id) LEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid) LEFT JOIN ts_blogs_subscribe s ON (s.$bid = b.bid AND s.$uid = " . $CURUSER["id"] . ") WHERE b.$bid = " . sqlesc($BID))) || sqlerr(__FILE__, 508);
    if (0 < mysqli_num_rows($Query)) {
        $rating = "";
        if ($ratingsystem == "yes") {
            require INC_PATH . "/class_ts_rating.php";
            $TSRating = new TS_Rating("blog_" . $BID, $CURUSER["id"]);
            $SHowForm = $TSRating->ShowForm($lang->ts_blog["rateblog"]);
            $rating = "\r\n\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/prototype.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_rate.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"show_rating\" $name = \"show_rating\">\r\n\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t" . $lang->ts_blog["rating"] . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t<div $id = \"tsrating_results\" $name = \"tsrating_results\" $style = \"padding-bottom: 5px; padding-top: 10px;\">\r\n\t\t\t\t\t\t\t\t\t" . $TSRating->GetScore($lang->ts_blog["ratedetails"]) . "\r\n\t\t\t\t\t\t\t\t\t" . ($SHowForm ? $SHowForm : "<br /><i><b>" . $lang->ts_blog["alreadyvotes"] . "</b></i>") . "\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</tbody>\r\n\t\t\t\t</table>\r\n\t\t\t</div>\r\n\t\t\t";
        }
        $Blog = mysqli_fetch_assoc($Query);
        $Options = "\r\n\t\t<div $style = \"padding-bottom: 15px;\" $id = \"show_calendar\" $name = \"show_calendar\">\r\n\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">" . $lang->ts_blog["options"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=subscribe&amp;$bid = " . $BID . "\">" . ($Blog["issubs"] == $CURUSER["id"] ? $lang->ts_blog["s2"] : $lang->ts_blog["s1"]) . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</div>\r\n\t\t";
        $ShowCalendar = "\r\n\t\t<div $style = \"padding-bottom: 15px;\" $id = \"show_calendar\" $name = \"show_calendar\">\r\n\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">" . $lang->ts_blog["archive"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t" . show_calendar() . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</div>\r\n\t\t";
        $TotalBlogs = ts_nf(mysqli_num_rows(sql_query("SELECT uid FROM ts_blogs WHERE $uid = " . sqlesc($Blog["uid"]))));
        $Owner = "<a $href = \"" . ts_seo($Blog["uid"], $Blog["username"]) . "\">" . get_user_color($Blog["username"], $Blog["namestyle"]) . "</a>";
        $imagepath = $pic_base_url . "friends/";
        $xoffline = sprintf($lang->ts_blog["xoffline"], $Blog["username"]);
        $xonline = sprintf($lang->ts_blog["xonline"], $Blog["username"]);
        $dt = TIMENOW - TS_TIMEOUT;
        if (TS_Match($Blog["options"], "B1") && !$is_mod && $Blog["uid"] != $CURUSER["id"]) {
            $Blog["last_access"] = $Blog["last_login"];
            $onoffpic = "<img $src = \"" . $imagepath . "offline.png\" $alt = \"" . $xoffline . "\" $title = \"" . $xoffline . "\" $border = \"0\" class=\"inlineimg\" />";
        } else {
            if ($dt < TS_MTStoUTS($Blog["last_access"]) || $Blog["uid"] == $CURUSER["id"]) {
                $onoffpic = "<img $src = \"" . $imagepath . "online.png\" $alt = \"" . $xonline . "\" $title = \"" . $xonline . "\" $border = \"0\" class=\"inlineimg\" />";
            } else {
                $onoffpic = "<img $src = \"" . $imagepath . "offline.png\" $alt = \"" . $xoffline . "\" $title = \"" . $xoffline . "\" $border = \"0\" class=\"inlineimg\" />";
            }
        }
        $OwnerAvatar = get_user_avatar($Blog["avatar"], 50, 50);
        $OwnerRegDate = my_datee($regdateformat, $Blog["added"]);
        $LastSeen = my_datee($dateformat, $Blog["last_access"]) . " " . my_datee($timeformat, $Blog["last_access"]);
        $OwnerDetails = "\r\n\t\t<div $style = \"padding-bottom: 15px;\" $id = \"show_owner\" $name = \"show_owner\">\r\n\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\" $colspan = \"2\">" . $onoffpic . " " . $Owner . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $width = \"50\" $height = \"50\" $align = \"center\">\r\n\t\t\t\t\t\t" . $OwnerAvatar . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $width = \"%99\" $align = \"left\" $valign = \"top\">\r\n\t\t\t\t\t\t" . sprintf($lang->ts_blog["ownerdetails"], $OwnerRegDate, $LastSeen, $TotalBlogs) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</div>\r\n\t\t";
        $BlogTitle = htmlspecialchars_uni($Blog["title"]);
        $TITLE = sprintf($lang->ts_blog["showblog"], $BlogTitle);
        if ($Blog["uid"] != $CURUSER["id"]) {
            sql_query("UPDATE ts_blogs SET $views = views + 1 WHERE $bid = " . sqlesc($BID)) || sqlerr(__FILE__, 617);
        }
        $Updated = "";
        if (0 < $Blog["updatedate"] && 0 < $Blog["updatedby"]) {
            $Updated = "<br />" . sprintf($lang->ts_blog["updated"], my_datee($dateformat, $Blog["updatedate"]), my_datee($timeformat, $Blog["updatedate"]));
        }
        ($Query = sql_query("SELECT * FROM ts_blogs_comments WHERE $bid = " . sqlesc($BID))) || sqlerr(__FILE__, 626);
        $count = mysqli_num_rows($Query);
        list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $count, $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $BID . "&amp;");
        ($Query = sql_query("SELECT c.cid, c.uid, c.date, c.descr, u.username, u.avatar, g.namestyle FROM ts_blogs_comments c LEFT JOIN users u ON (u.$id = c.uid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE c.$bid = " . sqlesc($BID) . " ORDER BY date ASC " . $limit)) || sqlerr(__FILE__, 630);
        $BlogComments = "\r\n\t\t\t" . $pagertop . "\r\n\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"show_comments\" $name = \"show_comments\">\r\n\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">" . $lang->ts_blog["comments"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t";
        if (0 < mysqli_num_rows($Query)) {
            while ($Comments = mysqli_fetch_assoc($Query)) {
                $EditComment = "\r\n\t\t\t\t<span $style = \"float: right;\"><a $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 6&$reporting = " . $Comments["cid"] . "&$extra = " . $BID . "&$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "', 'report', 500, 300); return false;\" $href = \"javascript:void(0);\"><u><i>" . $lang->ts_blog["report"] . "</i></u></a>";
                if ($is_mod || $Comments["uid"] === $CURUSER["id"]) {
                    $EditComment .= (blogpermission("caneditc") || $is_mod ? " | <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_comment&amp;$cid = " . $Comments["cid"] . "&amp;$bid = " . $BID . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\"><u><i>" . $lang->ts_blog["edit2"] . "</i></u></a> |" : "") . (blogpermission("candeletec") || $is_mod ? " <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete_comment&amp;$cid = " . $Comments["cid"] . "&amp;$bid = " . $BID . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\" $onclick = \"return AreYouSure('" . $lang->ts_blog["sure2"] . "');\"><u><i>" . $lang->ts_blog["delete2"] . "</i></u></a>" : "");
                }
                $EditComment .= "</span>";
                $Poster = "<a $href = \"" . ts_seo($Comments["uid"], $Comments["username"]) . "\">" . get_user_color($Comments["username"], $Comments["namestyle"]) . "</a>";
                $BlogComments .= "\r\n\t\t\t\t\t\t<div $style = \"padding-top: 5px;\" $id = \"show_comments" . $Comments["cid"] . "\" $name = \"show_comments" . $Comments["cid"] . "\">\r\n\t\t\t\t\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td $colspan = \"2\" $align = \"left\" class=\"subheader\">\r\n\t\t\t\t\t\t\t\t\t\t" . $EditComment . "\r\n\t\t\t\t\t\t\t\t\t\t" . sprintf($lang->ts_blog["posted"], my_datee($dateformat, $Comments["date"]), my_datee($timeformat, $Comments["date"]), $Poster) . "\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td $align = \"left\" $width = \"1%\" $height = \"50\" $valign = \"top\">" . get_user_avatar($Comments["avatar"], false, 50, 50) . "</td>\r\n\t\t\t\t\t\t\t\t\t<td $valign = \"top\" $width = \"99%\">\r\n\t\t\t\t\t\t\t\t\t\t" . format_comment($Comments["descr"]) . "\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t";
            }
        } else {
            $BlogComments .= $lang->ts_blog["nocomments"];
        }
        $BlogComments .= "\r\n\t\t\t\t\t\t<div $id = \"PostedQuickBlogComment\" $name = \"PostedQuickBlogComment\" $style = \"padding-top: 5px;\">\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</div>\r\n\t\t\t" . $pagerbottom;
        $PostComments = "";
        if ($is_mod || $Blog["allowcomments"] == 1 && blogpermission("canpost")) {
            $lang->load("quick_editor");
            require INC_PATH . "/functions_quick_editor.php";
            require_once INC_PATH . "/class_tsquickbbcodeeditor.php";
            $QuickEditor = new TSQuickBBCodeEditor();
            $QuickEditor->ImagePath = $pic_base_url;
            $QuickEditor->SmiliePath = $pic_base_url . "smilies/";
            $QuickEditor->FormName = "quickreply";
            $QuickEditor->TextAreaName = "message";
            $PostComments = "\r\n\t\t\t" . $QuickEditor->GenerateJavascript() . "\r\n\t\t\t" . ($useajax == "yes" ? "\r\n\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_bm.js\"></script>" : "") . "\r\n\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=save_comment&$bid = " . $BID . "\" $name = \"quickreply\" $id = \"quickreply\">\r\n\t\t\t<input $type = \"hidden\" $name = \"bid\" $value = \"" . $BID . "\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"save_comment\" />\r\n\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"post_comment\" $name = \"post_comment\">\r\n\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">" . $lang->ts_blog["postc"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t" . $QuickEditor->GenerateBBCode() . "\r\n\t\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t\t<textarea $name = \"message\" $style = \"width:670px;height:85px;\" $id = \"message\"></textarea><br />\r\n\t\t\t\t\t\t\t\t\t<span $id = \"loading-layer\" $style = \"display:none;\"><img $src = \"" . $dimagedir . "loading.gif\" $border = \"0\" $alt = \"\" $title = \"\" class=\"inlineimg\" /></span>\r\n\t\t\t\t\t\t\t\t\t" . ($useajax == "yes" ? "\r\n\t\t\t\t\t\t\t\t\t<input $type = \"button\" class=\"button\" $value = \"" . $lang->ts_blog["postc"] . "\" $name = \"submitbm\" $id = \"submitbm\" $onclick = \"javascript:TSajaxquickbm('" . $BID . "');\" />" : "<input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->ts_blog["postc"] . "\" class=\"button\" />") . "\r\n\t\t\t\t\t\t\t\t\t<input $type = \"reset\" $value = \"" . $lang->ts_blog["reset"] . "\" class=\"button\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</div>\r\n\t\t\t</form>\r\n\t\t\t";
        }
        $RecentBlogs = "\r\n\t\t<div $style = \"padding-bottom: 15px;\" $id = \"recent_blogs\" $name = \"recent_blogs\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "\"><u>" . $lang->ts_blog["recentb"] . "</u></a>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>";
        ($Query = sql_query("SELECT bid, title FROM ts_blogs ORDER by date DESC LIMIT 5")) || sqlerr(__FILE__, 744);
        if (0 < mysqli_num_rows($Query)) {
            while ($RB = mysqli_fetch_row($Query)) {
                $RecentBlogs .= "\r\n\t\t\t\t<div $style = \"padding-top: 2px;\"><img $src = \"" . $blog_image_path . "recentblogs.jpg\" $border = \"0\" $alt = \"\" $title = \"\" /> <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $RB[0] . "\"><u>" . cutename($RB[1], 35) . "</u></a></div>\r\n\t\t\t\t";
            }
        } else {
            $RecentBlogs .= $lang->ts_blog["noblogs"];
        }
        $RecentBlogs .= "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t";
        stdhead($TITLE);
        add_breadcrumb(sprintf($lang->ts_blog["listtitle"], $SITENAME), $_SERVER["SCRIPT_NAME"]);
        add_breadcrumb($TITLE);
        build_breadcrumb();
        show_blog_errors();
        $BlogCommands = [];
        if ($is_mod || $Blog["uid"] === $CURUSER["id"]) {
            if ($is_mod || blogpermission("caneditb")) {
                $BlogCommands[] = "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_entry&amp;$bid = " . $BID . "\"><u><i>" . $lang->ts_blog["edit"] . "</i></u></a>";
            }
            if ($is_mod || blogpermission("candisablec")) {
                $BlogCommands[] = "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=enable_disable_comments&amp;$bid = " . $BID . "\"><u><i>" . ($Blog["allowcomments"] == 0 ? $lang->ts_blog["ecomments"] : $lang->ts_blog["dcomments"]) . "</i></u></a>";
            }
            if ($is_mod || blogpermission("candeleteb")) {
                $BlogCommands[] = "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete_blog&amp;$bid = " . $BID . "\" $onclick = \"return AreYouSure('" . $lang->ts_blog["sure"] . "');\"><u><i>" . $lang->ts_blog["delete"] . "</i></u></a>";
            }
        }
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction AreYouSure(Text)\r\n\t\t\t{\r\n\t\t\t\tif (confirm(Text))\r\n\t\t\t\t{\r\n\t\t\t\t\treturn true;\r\n\t\t\t\t}\r\n\t\t\t\telse\r\n\t\t\t\t{\r\n\t\t\t\t\treturn false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<table $align = \"center\" $cellpadding = \"0\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t<tbody>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td $valign = \"top\" class=\"none\">\r\n\t\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t\t\t\t\t\t<!-- AddThis Button BEGIN -->\r\n\t\t\t\t\t\t\t\t\t\t\t<script $type = \"text/javascript\">var $addthis_pub = \"templateshares\";</script>\r\n\t\t\t\t\t\t\t\t\t\t\t<a $href = \"http://www.addthis.com/bookmark.php?$v = 20\" $onmouseover = \"return addthis_open(this, '', '[URL]', '[TITLE]')\" $onmouseout = \"addthis_close()\" $onclick = \"return addthis_sendto()\"><img $src = \"http://s7.addthis.com/static/btn/lg-bookmark-en.gif\" $width = \"125\" $height = \"16\" $alt = \"Bookmark and Share\" $style = \"border:0\"/></a><script $type = \"text/javascript\" $src = \"http://s7.addthis.com/js/200/addthis_widget.js\"></script>\r\n\t\t\t\t\t\t\t\t\t\t\t<!-- AddThis Button END -->\r\n\t\t\t\t\t\t\t\t\t\t\t</span>\r\n\t\t\t\t\t\t\t\t\t\t\t" . $TITLE . "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t\t\t" . (count($BlogCommands) ? "<span $style = \"float: right;\">" . implode(" | ", $BlogCommands) . "</span>" : "") . "\r\n\t\t\t\t\t\t\t\t\t\t\t<h1 $id = \"blog_title\" class=\"none\" $style = \"font-size:14pt; font-weight:normal; margin:0px\">" . $BlogTitle . "</h1>\r\n\t\t\t\t\t\t\t\t\t\t\t<hr />\r\n\t\t\t\t\t\t\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t\t\t\t\t\t\t<script $src = \"http://digg.com/tools/diggthis.js\" $type = \"text/javascript\"></script>\r\n\t\t\t\t\t\t\t\t\t\t\t</span>\r\n\t\t\t\t\t\t\t\t\t\t\t" . format_comment($Blog["desc"]) . "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t" . $BlogComments . "\r\n\t\t\t\t\t\t" . $PostComments . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $style = \"padding-left: 15px\" $valign = \"top\" $width = \"210\" class=\"none\">\r\n\t\t\t\t\t\t" . $OwnerDetails . "\r\n\t\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t\t\t" . $lang->ts_blog["stats"] . "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t\t\t" . sprintf($lang->ts_blog["created"], my_datee($dateformat, $Blog["date"]), my_datee($timeformat, $Blog["date"])) . $Updated . "<br />\r\n\t\t\t\t\t\t\t\t\t\t\t<b>" . $lang->ts_blog["owner"] . ":</b> " . $Owner . "<br /><b>" . $lang->ts_blog["views"] . ":</b> " . ts_nf($Blog["views"]) . "<br /><b>" . $lang->ts_blog["comments"] . ":</b> " . ts_nf($Blog["comments"]) . "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t" . $Options . "\r\n\t\t\t\t\t\t" . $rating . "\r\n\t\t\t\t\t\t" . $RecentBlogs . "\r\n\t\t\t\t\t\t" . $ShowCalendar . "\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t";
        stdfoot();
        exit;
    }
    $blog_error[] = $lang->ts_blog["invalidbid"];
}
($Query = sql_query("SELECT bid, title FROM ts_blogs ORDER by date DESC LIMIT 5")) || sqlerr(__FILE__, 873);
if (0 < mysqli_num_rows($Query)) {
    $RecentBlogs = "";
    while ($RB = mysqli_fetch_row($Query)) {
        $RecentBlogs .= "\r\n\t\t<div $style = \"padding-top: 2px;\"><img $src = \"" . $blog_image_path . "recentblogs.jpg\" $border = \"0\" $alt = \"\" $title = \"\" /> <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $RB[0] . "\"><u>" . cutename($RB[1], 35) . "</u></a></div>\r\n\t\t";
    }
} else {
    $RecentBlogs = $lang->ts_blog["noblogs"];
}
($Query = sql_query("SELECT c.cid, c.bid, c.uid, c.date, b.title, u.username, g.namestyle FROM ts_blogs_comments c LEFT JOIN ts_blogs b ON (c.$bid = b.bid) LEFT JOIN users u ON (c.$uid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) ORDER BY date DESC LIMIT 5")) || sqlerr(__FILE__, 889);
if (0 < mysqli_num_rows($Query)) {
    $RecentComments = "";
    while ($RC = mysqli_fetch_assoc($Query)) {
        $RecentComments .= "\r\n\t\t<div $style = \"padding-top: 2px;\"><img $src = \"" . $blog_image_path . "comments.gif\" $border = \"0\" $alt = \"\" $title = \"\" /> <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $RC["bid"] . "#show_comments" . $RC["cid"] . "\"><u>" . cutename($RC["title"], 20) . "</u></a> " . sprintf($lang->ts_blog["wrecentc"], "<a $href = \"" . ts_seo($RC["uid"], $RC["username"]) . "\">" . get_user_color($RC["username"], $RC["namestyle"]) . "</a>") . "</div>\r\n\t\t";
    }
} else {
    $RecentComments = $lang->ts_blog["nocomments"];
}
$Esql = [];
$orderby = "b.date DESC, b.lastpostdate DESC";
if ($do == "search") {
    $month = $_GET["m"] < 1 || 12 < $_GET["m"] ? date("n", TIMENOW) : intval($_GET["m"]);
    $year = 2037 < $_GET["y"] || $_GET["y"] < 1970 ? date("Y", TIMENOW) : intval($_GET["y"]);
    if (($day = intval($_GET["d"])) && gmdate("t", gmmktime(12, 0, 0, $month, $day, $year)) < $day) {
        $day = 0;
    }
    $today = getdate(TIMENOW);
    if ($today["year"] < $year || $today["mon"] < $month && $year == $today["year"]) {
        print_no_permission();
    }
    if ($day) {
        $starttime = mktime(0, 0, 0, $month, $day, $year);
        $endtime = mktime(0, 0, 0, $month, $day + 1, $year);
    } else {
        $starttime = mktime(0, 0, 0, $month, 1, $year);
        $endtime = mktime(0, 0, 0, $month + 1, 1, $year);
    }
    $Esql[] = "b.date >= " . $starttime;
    $Esql[] = "b.date < " . $endtime;
}
if ($do == "view_own") {
    $Esql[] = "b.$uid = '" . $CURUSER["id"] . "'";
}
$keywords = "";
if ($do == "search_blog") {
    $keywords = isset($_GET["keywords"]) ? urldecode($_GET["keywords"]) : (isset($_POST["keywords"]) ? trim($_POST["keywords"]) : "");
    if ($keywords) {
        if (isset($_POST["titleonly"]) && $_POST["titleonly"] == "on") {
            $Esql[] = "(MATCH (b.title) AGAINST ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "*' IN BOOLEAN MODE))";
        } else {
            $Esql[] = "(MATCH (b.title, b.desc) AGAINST ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "*' IN BOOLEAN MODE))";
        }
    }
}
($Query = sql_query("SELECT b.bid FROM ts_blogs b" . (count($Esql) ? " WHERE " . implode(" AND ", $Esql) : "") . " ORDER BY b.date")) || sqlerr(__FILE__, 963);
$count = mysqli_num_rows($Query);
list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $count, $_SERVER["SCRIPT_NAME"] . "?" . ($keywords ? "do=search_blog&amp;$keywords = " . urlencode(htmlspecialchars_uni($keywords)) . "&amp;" : ""));
$BlogList = "";
($Query = sql_query("SELECT b.*, u.username as owner, u.avatar, g.namestyle as ownerns, uu.username as lastpostername, gg.namestyle as lastposterns FROM ts_blogs b LEFT JOIN users u ON (b.$uid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) LEFT JOIN users uu ON (b.$lastposter = uu.id) LEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid)" . (count($Esql) ? " WHERE " . implode(" AND ", $Esql) : "") . " ORDER BY " . $orderby . " " . $limit)) || sqlerr(__FILE__, 968);
if (0 < mysqli_num_rows($Query)) {
    $BlogList = "\r\n\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t";
    while ($BL = mysqli_fetch_assoc($Query)) {
        $BL["desc"] = QuickBBCode($BL["desc"]);
        $BlogList .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"100\" $height = \"100\">\r\n\t\t\t\t" . get_user_avatar($BL["avatar"], 100, 100) . "\r\n\t\t\t</td>\r\n\t\t\t<td $valign = \"top\">\r\n\t\t\t\t<div class=\"blog_comments\" $style = \"float: right;\">\r\n\t\t\t\t\t<span>\r\n\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $BL["bid"] . "#show_comments\" $alt = \"" . $lang->ts_blog["comments"] . "\" $title = \"" . $lang->ts_blog["comments"] . "\">" . $BL["comments"] . "</a>\r\n\t\t\t\t\t</span>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div $style = \"font-size:14pt; font-weight:normal; margin:0px\">\r\n\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $BL["bid"] . "\"><b><u>" . htmlspecialchars_uni($BL["title"]) . "</u></b></a>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div>\r\n\t\t\t\t\t<small>" . sprintf($lang->ts_blog["s8"], my_datee($dateformat, $BL["date"]), my_datee($timeformat, $BL["date"]), "<a $href = \"" . ts_seo($BL["uid"], $BL["owner"]) . "\">" . get_user_color($BL["owner"], $BL["ownerns"]) . "</a>") . "</small>\r\n\t\t\t\t\t<br /><hr />\r\n\t\t\t\t</div>\r\n\t\t\t\t<div>\r\n\t\t\t\t\t" . substr($BL["desc"], 0, 300) . "...\r\n\t\t\t\t</div>\r\n\t\t\t\t<div $align = \"right\">\r\n\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=show_blog&amp;$bid = " . $BL["bid"] . "\"><small><b><u>" . $lang->ts_blog["s7"] . "</u></b></small></a>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $BlogList .= "\r\n\t</table>\r\n\t";
} else {
    $BlogList = $lang->ts_blog["noblog"];
}
$TITLE = sprintf($lang->ts_blog["listtitle"], $SITENAME);
stdhead($TITLE);
add_breadcrumb($TITLE, $_SERVER["SCRIPT_NAME"]);
build_breadcrumb();
show_blog_errors();
echo "\r\n<table $align = \"center\" $cellpadding = \"0\" $cellspacing = \"0\" $width = \"100%\">\r\n\t<tbody>\r\n\t\t<tr $valign = \"top\">\r\n\t\t\t<td $valign = \"top\" class=\"none\">\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t" . $TITLE . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t" . $BlogList . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t\t" . $pagerbottom . "\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t\t<td $style = \"padding-left: 15px\" $valign = \"top\" $width = \"210\" class=\"none\">\r\n\t\t\t" . ($is_mod || blogpermission("cancreate") ? "\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->ts_blog["options"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=create_blog\"><u>" . $lang->ts_blog["createb"] . "</u></a>\r\n\t\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=view_own\"><u>" . $lang->ts_blog["viewb"] . "</u></a>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t\t" : "") . "\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->ts_blog["recentb"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t" . $RecentBlogs . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"search_blog\" />\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->ts_blog["s9"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t" . $lang->ts_blog["s10"] . "<br /><input $type = \"text\" $name = \"keywords\" $value = \"" . htmlspecialchars($keywords) . "\" /><br />\r\n\t\t\t\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"titleonly\"" . (isset($_POST["titleonly"]) && $_POST["titleonly"] == "on" ? " $checked = \"checked\"" : "") . " /> " . $lang->ts_blog["s11"] . "<br/>\r\n\t\t\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_blog["s12"] . "\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t\t</form>\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->ts_blog["recentc"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t" . $RecentComments . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</tbody>\r\n</table>\r\n";
stdfoot();
function BlogPermission($Option)
{
    global $usergroups;
    $Options = ["canview" => "0", "cancreate" => "1", "caneditb" => "2", "candeleteb" => "3", "canpost" => "4", "caneditc" => "5", "candeletec" => "6", "candisablec" => "7"];
    $What = isset($Options[$Option]) ? $Options[$Option] : 0;
    return $usergroups["blogperms"][$What] == "1" ? true : false;
}
function show_calendar()
{
    global $BASEURL;
    $_datearray = [];
    $__query = sql_query("SELECT date FROM ts_blogs GROUP BY date ORDER BY date ASC");
    if (0 < mysqli_num_rows($__query)) {
        while ($__R = mysqli_fetch_assoc($__query)) {
            $_datearray[] = "\"" . date("F-j-Y", $__R["date"]) . "\"";
        }
    }
    $calendar = "\r\n\t<script $type = \"text/javascript\">\r\n\t\t//<![CDATA[\r\n\t\tfunction in_array(needle, haystack, strict)\r\n\t\t{\r\n\t\t\tvar $found = false, key, $strict = !!strict;\r\n\t\t\tfor (key in haystack) {if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {$found = true;\tbreak;}}\r\n\t\t\treturn found;\r\n\t\t}\r\n\t\tfunction buildCal(m, y, cM, cH, cDW, cD, brdr)\r\n\t\t{\r\n\t\t\tvar $mn = ['January','February','March','April','May','June','July','August','September','October','November','December'];\r\n\t\t\tvar $mnumbers = new Array();\r\n\t\t\tmnumbers['January'] = 1;\r\n\t\t\tmnumbers['February'] = 2;\r\n\t\t\tmnumbers['March'] = 3;\r\n\t\t\tmnumbers['April'] = 4;\r\n\t\t\tmnumbers['May'] = 5;\r\n\t\t\tmnumbers['June'] = 6;\r\n\t\t\tmnumbers['July'] = 7;\r\n\t\t\tmnumbers['August'] = 8;\r\n\t\t\tmnumbers['September'] = 9;\r\n\t\t\tmnumbers['October'] = 10;\r\n\t\t\tmnumbers['November'] = 11;\r\n\t\t\tmnumbers['December'] = 12;\r\n\r\n\t\t\tvar $dim = [31,0,31,30,31,30,31,31,30,31,30,31];\r\n\t\t\tvar $oD = new Date(y, m-1, 1);\r\n\t\t\toD.$od = oD.getDay()+1;\r\n\t\t\tvar $todaydate = new Date()\r\n\t\t\tvar $scanfortoday = ($y = =todaydate.getFullYear() && $m = =todaydate.getMonth()+1)? todaydate.getDate() : 0\r\n\t\t\tdim[1]=(((oD.getFullYear()%100!=0)&&(oD.getFullYear()%4==0))||(oD.getFullYear()%400==0))?29:28;\r\n\t\t\tvar $t = '<div class=\"'+cM+'\"><table class=\"'+cM+'\" $cols = \"7\" $cellpadding = \"1\" $border = \"'+brdr+'\" $cellspacing = \"0\" $width = \"100%\"><tr $align = \"center\">';\r\n\t\t\tt+='<td $colspan = \"7\" $align = \"center\" class=\"'+cH+'\">'+mn[m-1]+' - '+y+'</td></tr><tr $align = \"center\">';\r\n\t\t\tfor($s = 0;s<7;s++)t+='<td class=\"'+cDW+'\">'+\"SMTWTFS\".substr(s,1)+'</td>';\r\n\t\t\tt+='</tr><tr $align = \"center\">';\r\n\t\t\tfor($i = 1;i<=42;i++)\r\n\t\t\t{\r\n\t\t\t\tvar $x = ((i-oD.od>=0)&&(i-oD.od<dim[m-1]))? i-oD.od+1 : '&nbsp;';\r\n\t\t\t\tif (in_array(mn[m-1]+\"-\"+x+\"-\"+y, [" . @implode(",", $_datearray) . "])) $x = '<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=search&$m = '+mnumbers[mn[m-1]]+'&$d = '+x+'&$y = '+y+'\">'+($x = =scanfortoday ? '<span $id = \"today\"><u><b><font $size = 2>'+x+'</font></u></b></span>' : '<u><b><font $size = 2>'+x+'</font></u></b>')+'</a>'\r\n\t\t\t\tif ($x = =scanfortoday)\r\n\t\t\t\$tx = '<span $id = \"today\">'+x+'</span></a>'\r\n\t\t\t\tt+='<td class=\"'+cD+'\">'+x+'</td>';\r\n\t\t\t\tif(((i)%7==0)&&(i<36))t+='</tr><tr $align = \"center\">';\r\n\t\t\t}\r\n\t\t\treturn t+='</tr></table></div>';\r\n\t\t}\r\n\t\t//]]>\r\n\t</script>\r\n\t<script $type = \"text/javascript\">\r\n\t\t//<![CDATA[\r\n\t\tvar $todaydate = new Date()\r\n\t\tvar $curmonth = todaydate.getMonth()+1\r\n\t\tvar $curyear = todaydate.getFullYear()\r\n\t\tdocument.write(buildCal(curmonth ,curyear, \"\", \"thead\", \"subheader\", \"\", 1));\r\n\t\t//]]>\r\n\t</script>";
    return $calendar;
}
function show_blog_errors()
{
    global $blog_error;
    global $lang;
    if (0 < count($blog_error)) {
        $errors = implode("<br />", $blog_error);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}
function QuickBBCode($entry)
{
    $entry = htmlspecialchars_uni($entry);
    $simple_search = ["/\\[b\\](.*?)\\[\\/b\\]/is", "/\\[i\\](.*?)\\[\\/i\\]/is", "/\\[h\\](.*?)\\[\\/h\\]/is", "/\\[u\\](.*?)\\[\\/u\\]/is", "/\\[$color = ([a-zA-Z]+)\\]((\\s|.)+?)\\[\\/color\\]/is", "/\\[$color = (#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\\]((\\s|.)+?)\\[\\/color\\]/is", "/\\[$url = ([^()<>\\s]+?)\\]((\\s|.)+?)\\[\\/url\\]/is", "/\\[url\\]([^()<>\\s]+?)\\[\\/url\\]/is", "/\\[$font = ([a-zA-Z ,]+)\\]((\\s|.)+?)\\[\\/font\\]/is", "/\\[pre\\](.*?)\\[\\/pre\\]/is", "/\\[nfo\\](.*?)\\[\\/nfo\\]/is", "#\\[$size = (xx-small|x-small|small|medium|large|x-large|xx-large)\\](.*?)\\[/size\\]#si", "#\\[$align = (left|center|right|justify)\\](.*?)\\[/align\\]#si"];
    $simple_replace = ["<b>\\1</b>", "<i>\\1</i>", "<h3>\\1</h3>", "<u>\\1</u>", "<font $color = \"\\1\">\\2</font>", "<font $color = \"\\1\">\\2</font>", "<a $href = \"\\1\" $target = \"_blank\">\\2</a>", "<a $href = \"\\1\" $target = \"_blank\">\\1</a>", "<font $face = \"\\1\">\\2</font>", "<pre>\\1</pre>", "<tt><span $style = \"white-space: nowrap;\"><font $face = \"MS Linedraw\" $size = \"2\" $style = \"font-size: 10pt; line-height: 10pt\">\\1</font></span></tt>", "<span $style = \"font-size: \$1;\">\$2</span>", "<p $style = \"text-align: \$1;\">\$2</p>"];
    return preg_replace($simple_search, $simple_replace, $entry);
}

?>