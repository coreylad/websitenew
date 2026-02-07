<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_tutorials.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("ts_tutorials");
define("TST_VERSION", "1.2 by xam");
// Action to perform (e.g., new, edit, delete)
$action = isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"]) ? $_POST["do"] : "");
// Output HTML string
$tutorialsOutput = "";
// Tutorials HTML content
$tutorialsHtml = "";
// Page title
$pageTitle = sprintf($lang->ts_tutorials["head"], $SITENAME);
// Error messages
$tutorialErrors = [];
// Views for editor
$editorViews = "";
// Preview HTML
$previewHtml = showPreview("message");
if ($action == "delete" && $is_mod && is_valid_id($_GET["tid"]) && ($tutorialId = intval($_GET["tid"]))) {
    ($tutorialQuery = sql_query("SELECT title, content FROM ts_tutorials WHERE $tid = '" . $tutorialId . "'")) || sqlerr(__FILE__, 39);
    if (mysqli_num_rows($tutorialQuery) == 0) {
        unset($action);
        $tutorialErrors[] = $lang->ts_tutorials["error2"];
    } else {
        sql_query("DELETE FROM ts_tutorials WHERE $tid = " . sqlesc($tutorialId)) || sqlerr(__FILE__, 47);
        sql_query("DELETE FROM ts_tutorials_comments WHERE $tid = " . sqlesc($tutorialId)) || sqlerr(__FILE__, 48);
        unset($action);
    }
}
if ($action == "new" && $is_mod) {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["submit"])) {
        if (!empty($_POST["subject"]) && !empty($_POST["message"])) {
            $editorTitle = trim($_POST["subject"]);
            $editorContent = trim($_POST["message"]);
            $editorViews = intval($_POST["views"]);
            sql_query("INSERT INTO ts_tutorials VALUES (NULL, '" . $CURUSER["id"] . "', '" . TIMENOW . "', " . sqlesc($editorTitle) . ", " . sqlesc($editorContent) . ", '" . $editorViews . "')") || sqlerr(__FILE__, 62);
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $tutorialEdited = true;
            }
        } else {
            $tutorialErrors[] = $lang->global["dontleavefieldsblank"];
        }
    }
    if (isset($tutorialEdited) && $tutorialEdited) {
        unset($action);
        $action = "show_tutorial";
        $_GET["tid"] = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
    } else {
        define("IN_EDITOR", true);
        include_once INC_PATH . "/editor.php";
        $postOptionsTitle = [1 => $lang->ts_tutorials["views"]];
        $postOptions = [1 => "<input $type = \"text\" $name = \"views\" $value = \"" . htmlspecialchars_uni($editorViews ? $editorViews : 0) . "\" $size = \"10\" />"];
        $tutorialsHtml .= "\r\n\t\t" . $previewHtml . "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=new\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"4\">\r\n\t\t\t\t\t" . insert_editor(true, isset($_POST["subject"]) ? $_POST["subject"] : "", isset($_POST["message"]) ? $_POST["message"] : "", $lang->ts_tutorials["new"], isset($editorTitle) ? $editorTitle : "", $postOptionsTitle, $postOptions, true, "", $lang->ts_tutorials["save"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</form>\r\n\t\t";
    }
}
if ($action == "edit" && $is_mod && is_valid_id($_GET["tid"]) && ($tutorialId = intval($_GET["tid"]))) {
    ($tutorialQuery = sql_query("SELECT title, content, views FROM ts_tutorials WHERE $tid = '" . $tutorialId . "'")) || sqlerr(__FILE__, 106);
    if (mysqli_num_rows($tutorialQuery) == 0) {
        unset($action);
        $tutorialErrors[] = $lang->ts_tutorials["error2"];
    } else {
        $tutorialData = mysqli_fetch_assoc($tutorialQuery);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["submit"])) {
            $tutorialEdited = false;
            if (!empty($_POST["subject"]) && !empty($_POST["message"])) {
                $editorTitle = trim($_POST["subject"]);
                $editorContent = trim($_POST["message"]);
                $editorViews = intval($_POST["views"]);
                sql_query("UPDATE ts_tutorials SET $title = " . sqlesc($editorTitle) . ", $content = " . sqlesc($editorContent) . ", $views = " . sqlesc($editorViews) . " WHERE $tid = '" . $tutorialId . "'") || sqlerr(__FILE__, 123);
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $tutorialEdited = true;
                }
            } else {
                $tutorialErrors[] = $lang->global["dontleavefieldsblank"];
            }
        }
        if (isset($tutorialEdited) && $tutorialEdited) {
            unset($action);
            $action = "show_tutorial";
            $_GET["tid"] = $tutorialId;
        } else {
            define("IN_EDITOR", true);
            include_once INC_PATH . "/editor.php";
            $postOptionsTitle = [1 => $lang->ts_tutorials["views"]];
            $postOptions = [1 => "<input $type = \"text\" $name = \"views\" $value = \"" . htmlspecialchars_uni($editorViews ? $editorViews : $tutorialData["views"]) . "\" $size = \"10\" />"];
            $tutorialsHtml .= "\r\n\t\t\t" . $previewHtml . "\r\n\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit&amp;$tid = " . $tutorialId . "\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"4\">\r\n\t\t\t\t\t\t" . insert_editor(true, isset($_POST["subject"]) ? $_POST["subject"] : $tutorialData["title"], isset($_POST["message"]) ? $_POST["message"] : $tutorialData["content"], $lang->ts_tutorials["edit"], $tutorialData["title"], $postOptionsTitle, $postOptions, true, "", $lang->ts_tutorials["save"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</form>\r\n\t\t\t";
        }
    }
}
if ($action == "delete_comment" && is_valid_id($commentId = intval($_GET["cid"])) && is_valid_id($tutorialId = intval($_GET["tid"])) && $is_mod) {
    sql_query("DELETE FROM ts_tutorials_comments WHERE $cid = " . sqlesc($commentId) . " AND $tid = " . sqlesc($tutorialId)) || sqlerr(__FILE__, 168);
    $action = "show_tutorial";
}
if ($action == "edit_comment" && is_valid_id($commentId = intval($_GET["cid"])) && is_valid_id($tutorialId = intval($_GET["tid"])) && $is_mod) {
    $previewHtml = "";
    $lang->load("ts_blog");
    ($commentQuery = sql_query("SELECT descr FROM ts_tutorials_comments  WHERE $cid = " . sqlesc($commentId) . " AND $tid = " . sqlesc($tutorialId))) || sqlerr(__FILE__, 176);
    if (0 < mysqli_num_rows($commentQuery)) {
        $commentData = mysqli_fetch_assoc($commentQuery);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["submit"])) {
            $commentDescription = trim($_POST["message"]);
            if (strlen($commentDescription) < 3) {
                stderr($lang->global["error"], $lang->ts_blog["editerror2"]);
            } else {
                sql_query("UPDATE ts_tutorials_comments SET `descr` = " . sqlesc($commentDescription) . " WHERE $cid = " . sqlesc($commentId) . " AND $tid = " . sqlesc($tutorialId)) || sqlerr(__FILE__, 190);
                header("Location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_tutorial&$tid = " . $tutorialId . "&$cid = " . $commentId . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "#show_comments" . $commentId);
                exit;
            }
        }
        $_TITLE = $lang->ts_blog["edit2"];
        stdhead($_TITLE);
        define("IN_EDITOR", true);
        include_once INC_PATH . "/editor.php";
        echo "\r\n\t\t" . $previewHtml . "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_comment&$tid = " . $tutorialId . "&$cid = " . $commentId . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"4\">\r\n\t\t\t\t\t" . insert_editor(false, "", isset($commentDescription) ? $commentDescription : (isset($_POST["message"]) ? $_POST["message"] : $commentData["descr"]), $lang->ts_blog["edit2"], "", "", "", true, "", $lang->ts_blog["save"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</form>\r\n\t\t";
        stdfoot();
        exit;
    }
}
if ($tutorialAction == "show_tutorial" && is_valid_id($_GET["tid"]) && ($tutorialId = intval($_GET["tid"]))) {
    ($tutorialQuery = sql_query("SELECT t.*, u.username, g.namestyle FROM ts_tutorials t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE t.$tid = '" . $tutorialId . "'")) || sqlerr(__FILE__, 218);
    if (mysqli_num_rows($tutorialQuery) == 0) {
        unset($tutorialAction);
        $errors[] = $lang->ts_tutorials["error2"];
    } else {
        sql_query("UPDATE ts_tutorials SET $views = views + 1 WHERE $tid = '" . $tutorialId . "'") || sqlerr(__FILE__, 226);
        $tutorialRow = mysqli_fetch_assoc($tutorialQuery);
        $DateContent = sprintf($lang->ts_tutorials["by"], "<b>" . my_datee($dateformat, $tutorialRow["date"]) . " " . my_datee($timeformat, $tutorialRow["date"]) . "</b>", "<a $href = \"" . ts_seo($tutorialRow["uid"], $tutorialRow["username"]) . "\">" . get_user_color($tutorialRow["username"], $tutorialRow["namestyle"]) . "</a>");
        $Tutorials .= "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"4\">\r\n\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $width = \"100%\" $align = \"left\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<span $style = \"float: right;\" class=\"smallfont\"><i>" . $DateContent . "</i></span>\r\n\t\t\t\t\t\t\t<h1>" . htmlspecialchars_uni($tutorialRow["title"]) . "</h1>\r\n\t\t\t\t\t\t\t<hr />\r\n\t\t\t\t\t\t\t" . format_comment($tutorialRow["content"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        $lang->load("ts_blog");
        ($Query = sql_query("SELECT * FROM ts_tutorials_comments WHERE $tid = " . sqlesc($Tid))) || sqlerr(__FILE__, 247);
        $count1 = mysqli_num_rows($Query);
        list($pagertop1, $pagerbottom1, $limit1) = pager($ts_perpage, $count1, $_SERVER["SCRIPT_NAME"] . "?do=show_tutorial&amp;$tid = " . $Tid . "&amp;");
        ($Query = sql_query("SELECT c.cid, c.uid, c.date, c.descr, u.username, u.avatar, g.namestyle FROM ts_tutorials_comments c LEFT JOIN users u ON (u.$id = c.uid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE c.$tid = " . sqlesc($Tid) . " ORDER BY date ASC " . $limit1)) || sqlerr(__FILE__, 251);
        $TutorialComments = "\r\n\t\t\t" . $pagertop1 . "\r\n\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"show_comments\" $name = \"show_comments\">\r\n\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">" . $lang->ts_blog["comments"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t";
        if (0 < mysqli_num_rows($Query)) {
            while ($Comments = mysqli_fetch_assoc($Query)) {
                $EditComment = "\r\n\t\t\t\t<span $style = \"float: right;\"><a $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 10&$reporting = " . $Comments["cid"] . "&$extra = " . $Tid . "&$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "', 'report', 500, 300); return false;\" $href = \"javascript:void(0);\"><u><i>" . $lang->ts_blog["report"] . "</i></u></a>";
                if ($is_mod || $Comments["uid"] === $CURUSER["id"]) {
                    $EditComment .= ($is_mod ? " | <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_comment&amp;$cid = " . $Comments["cid"] . "&amp;$tid = " . $Tid . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\"><u><i>" . $lang->ts_blog["edit2"] . "</i></u></a> |" : "") . ($is_mod ? " <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete_comment&amp;$cid = " . $Comments["cid"] . "&amp;$tid = " . $Tid . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\" $onclick = \"return AreYouSure('" . $lang->ts_blog["sure2"] . "');\"><u><i>" . $lang->ts_blog["delete2"] . "</i></u></a>" : "");
                }
                $EditComment .= "</span>";
                $Poster = "<a $href = \"" . ts_seo($Comments["uid"], $Comments["username"]) . "\">" . get_user_color($Comments["username"], $Comments["namestyle"]) . "</a>";
                $TutorialComments .= "\r\n\t\t\t\t\t\t<div $style = \"padding-top: 5px;\" $id = \"show_comments" . $Comments["cid"] . "\" $name = \"show_comments" . $Comments["cid"] . "\">\r\n\t\t\t\t\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td $colspan = \"2\" $align = \"left\" class=\"subheader\">\r\n\t\t\t\t\t\t\t\t\t\t" . $EditComment . "\r\n\t\t\t\t\t\t\t\t\t\t" . sprintf($lang->ts_blog["posted"], my_datee($dateformat, $Comments["date"]), my_datee($timeformat, $Comments["date"]), $Poster) . "\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td $align = \"left\" $width = \"1%\" $height = \"50\" $valign = \"top\">" . get_user_avatar($Comments["avatar"], false, 50, 50) . "</td>\r\n\t\t\t\t\t\t\t\t\t<td $valign = \"top\" $width = \"99%\">\r\n\t\t\t\t\t\t\t\t\t\t" . format_comment($Comments["descr"]) . "\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t";
            }
        } else {
            $TutorialComments .= $lang->ts_blog["nocomments"];
        }
        $TutorialComments .= "\r\n\t\t\t\t\t\t<div $id = \"PostedQuickTutorialComment\" $name = \"PostedQuickTutorialComment\" $style = \"padding-top: 5px;\">\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</div>\r\n\t\t\t" . $pagerbottom1;
        $PostComments = "";
        $dimagedir = $BASEURL . "/include/templates/" . $defaulttemplate . "/images/";
        if ($is_mod || $usergroups["cancomment"] != "no") {
            $lang->load("quick_editor");
            require INC_PATH . "/functions_quick_editor.php";
            require_once INC_PATH . "/class_tsquickbbcodeeditor.php";
            $QuickEditor = new TSQuickBBCodeEditor();
            $QuickEditor->ImagePath = $pic_base_url;
            $QuickEditor->SmiliePath = $pic_base_url . "smilies/";
            $QuickEditor->FormName = "quickreply";
            $QuickEditor->TextAreaName = "message";
            $PostComments = "\r\n\t\t\t" . $QuickEditor->GenerateJavascript() . "\r\n\t\t\t" . ($useajax == "yes" ? "\r\n\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_tm.js\"></script>" : "") . "\r\n\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=save_comment&$tid = " . $Tid . "\" $name = \"quickreply\" $id = \"quickreply\">\r\n\t\t\t<input $type = \"hidden\" $name = \"tid\" $value = \"" . $Tid . "\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"save_comment\" />\r\n\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"post_comment\" $name = \"post_comment\">\r\n\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">" . $lang->ts_blog["postc"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t" . $QuickEditor->GenerateBBCode() . "\r\n\t\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t\t<textarea $name = \"message\" $style = \"width:670px;height:85px;\" $id = \"message\"></textarea><br />\r\n\t\t\t\t\t\t\t\t\t<span $id = \"loading-layer\" $style = \"display:none;\"><img $src = \"" . $dimagedir . "loading.gif\" $border = \"0\" $alt = \"\" $title = \"\" class=\"inlineimg\" /></span>\r\n\t\t\t\t\t\t\t\t\t" . ($useajax == "yes" ? "\r\n\t\t\t\t\t\t\t\t\t<input $type = \"button\" class=\"button\" $value = \"" . $lang->ts_blog["postc"] . "\" $name = \"submitbm\" $id = \"submitbm\" $onclick = \"javascript:TSajaxquicktm('" . $Tid . "');\" />" : "<input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->ts_blog["postc"] . "\" class=\"button\" />") . "\r\n\t\t\t\t\t\t\t\t\t<input $type = \"reset\" $value = \"" . $lang->ts_blog["reset"] . "\" class=\"button\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</div>\r\n\t\t\t</form>\r\n\t\t\t";
        }
        $Tutorials .= "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction AreYouSure(Text)\r\n\t\t\t{\r\n\t\t\t\tif (confirm(Text))\r\n\t\t\t\t{\r\n\t\t\t\t\treturn true;\r\n\t\t\t\t}\r\n\t\t\t\telse\r\n\t\t\t\t{\r\n\t\t\t\t\treturn false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<tr><td $colspan = \"4\">" . $TutorialComments . $PostComments . "</td></tr>";
    }
}
if (!$action) {
    ($tutorialListQuery = sql_query("SELECT tid FROM ts_tutorials")) || sqlerr(__FILE__, 375);
    $tutorialCount = mysqli_num_rows($tutorialListQuery);
    $tutorialsPerPage = $CURUSER["torrentsperpage"] != 0 ? intval($CURUSER["torrentsperpage"]) : $ts_perpage;
    list($pagerTop, $pagerBottom, $tutorialsLimit) = pager($tutorialsPerPage, $tutorialCount, $_SERVER["SCRIPT_NAME"] . "?");
    $tutorialsHtml .= "\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $width = \"50%\">" . $lang->ts_tutorials["title"] . "</td>\r\n\t\t<td class=\"subheader\" $width = \"15%\">" . $lang->ts_tutorials["sender"] . "</td>\r\n\t\t<td class=\"subheader\" $width = \"15%\">" . $lang->ts_tutorials["date"] . "</td>\r\n\t\t<td class=\"subheader\" $width = \"10%\" $align = \"center\">" . $lang->ts_tutorials["views"] . "</td>\r\n\t</tr>\r\n\t";
    ($tutorialListQuery = sql_query("SELECT t.*, u.username, g.namestyle FROM ts_tutorials t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) ORDER BY t.date DESC " . $tutorialsLimit)) || sqlerr(__FILE__, 387);
    if (0 < mysqli_num_rows($tutorialListQuery)) {
        while ($tutorialData = mysqli_fetch_assoc($tutorialListQuery)) {
            $tutorialsHtml .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"50%\">" . ($is_mod ? "<span $style = \"float: right;\">[<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete&amp;$tid = " . $tutorialData["tid"] . "\" $alt = \"" . $lang->ts_tutorials["delete"] . "\" $title = \"" . $lang->ts_tutorials["delete"] . "\" $onclick = \"return confirm_delete();\">" . $lang->ts_tutorials["delete"] . "</a>] [<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit&amp;$tid = " . $tutorialData["tid"] . "\" $alt = \"" . $lang->ts_tutorials["edit"] . "\" $title = \"" . $lang->ts_tutorials["edit"] . "\">" . $lang->ts_tutorials["edit"] . "</a>]</span>" : "") . "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=show_tutorial&amp;$tid = " . $tutorialData["tid"] . "\">" . htmlspecialchars_uni($tutorialData["title"]) . "</a></td>\r\n\t\t\t\t<td $width = \"15%\"><a $href = \"" . ts_seo($tutorialData["uid"], $tutorialData["username"]) . "\">" . get_user_color($tutorialData["username"], $tutorialData["namestyle"]) . "</a></td>\r\n\t\t\t\t<td $width = \"15%\">" . my_datee($dateformat, $tutorialData["date"]) . " " . my_datee($timeformat, $tutorialData["date"]) . "</td>\r\n\t\t\t\t<td $width = \"10%\" $align = \"center\">" . ts_nf($tutorialData["views"]) . "</td>\r\n\t\t\t</tr>";
        }
    } else {
        $tutorialsHtml .= "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"4\">" . $lang->ts_tutorials["error"] . "</td>\r\n\t\t</tr>";
    }
}

$newTutorialButton = "<p $style = \"float: right;\"><input $type = \"button\" $value = \"" . $lang->ts_tutorials["new"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=new'); return false;\" /></p>";
$backButton = "<p $style = \"float: right;\"><input $type = \"button\" $value = \"" . $lang->ts_tutorials["back"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?'); return false;\" /></p>";
$tutorialsOutput .= ($is_mod ? "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction confirm_delete()\r\n\t\t{\r\n\t\t\tif (confirm(\"" . $lang->ts_tutorials["sure"] . "\"))\r\n\t\t\t{\r\n\t\t\t\treturn true;\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" : "") . "\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"4\">" . ts_collapse("title") . $pageTitle . "</td>\r\n\t\t</tr>\r\n\t\t" . ts_collapse("title", 2) . "\r\n\t\t" . $tutorialsHtml . "\r\n\t\t</tbody>\r\n\t</table>\r\n\t";
stdhead($pageTitle, true);
show_tutorial_errors();
echo (isset($pagerTop) ? $pagerTop : "") . (!$action && $is_mod ? $newTutorialButton : $backButton) . $tutorialsOutput . (isset($pagerBottom) ? $pagerBottom : "");
stdfoot();
function show_tutorial_errors()
{
    global $tutorialErrors;
    global $lang;
    if (0 < count($tutorialErrors)) {
        $error = implode("<br />", $tutorialErrors);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $error . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>