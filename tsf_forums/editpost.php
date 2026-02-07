<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "editpost.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$tid = intval(TS_Global("tid"));
$pid = intval(TS_Global("pid"));
$canpostattachments = false;
if (!is_valid_id($tid) || !is_valid_id($pid)) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
($query = sql_query("SELECT p.modnotice, p.pid, p.tid, p.subject as postsubject, p.uid as posterid, p.message,\r\n\t\t\tt.subject as threadsubject, t.closed, t.firstpost, t.sticky, f.type, f.name as currentforum, f.fid as currentforumid, f.moderate, ff.name as deepforum, ff.fid as deepforumid, ff.moderate as moderaterf\r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.tid=t.tid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE p.tid = " . sqlesc($tid) . " AND p.pid = " . sqlesc($pid) . " LIMIT 0, 1")) || sqlerr(__FILE__, 49);
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$thread = $orjthreadarray = mysqli_fetch_assoc($query);
$tid = 0 + $thread["tid"];
$pid = 0 + $thread["pid"];
$fid = 0 + $thread["currentforumid"];
$ftype = $thread["type"];
$firstpost = 0 + $thread["firstpost"];
$threadsubject = ts_remove_badwords($thread["threadsubject"]);
$postsubject = ts_remove_badwords($thread["postsubject"]);
$message = $thread["message"];
$attachment = $display_attachment = "";
$forummoderator = is_forum_mod($ftype == "s" ? $thread["deepforumid"] : $fid, $CURUSER["id"]);
if (($thread["moderate"] == 1 || $thread["moderaterf"] == 1) && ($forummoderator || $moderator)) {
    $thread["moderate"] = 0;
    $thread["moderaterf"] = 0;
}
$visible = $thread["moderate"] == 1 || $thread["moderaterf"] == 1 ? 0 : 1;
if ($permissions[$fid]["canpostattachments"] == "yes") {
    $canpostattachments = true;
}
if (!$moderator && !$forummoderator && ($permissions[$fid]["caneditposts"] != "yes" || $permissions[$fid]["canview"] != "yes" || $permissions[$fid]["canpostreplys"] != "yes")) {
    print_no_permission();
    exit;
}
if (!$moderator && !$forummoderator && $thread["closed"] == "yes") {
    stderr($lang->global["error"], $lang->tsf_forums["thread_closed"]);
    exit;
}
if (!$moderator && !$forummoderator && $thread["posterid"] != $CURUSER["id"]) {
    print_no_permission();
    exit;
}
$prvp = showPreview("message");
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $error = "";
    $subject = sqlesc($_POST["subject"]);
    $uid = sqlesc($CURUSER["id"]);
    $dateline = sqlesc(TIMENOW);
    $message = sqlesc($_POST["message"]);
    $closed = isset($_POST["closethread"]) && $_POST["closethread"] == "yes" && ($moderator || $forummoderator) ? "yes" : "no";
    $sticky = isset($_POST["stickthread"]) && $_POST["stickthread"] == "yes" && ($moderator || $forummoderator) ? 1 : 0;
    $modnotice = isset($_POST["modnotice"]) ? trim($_POST["modnotice"]) : "";
    $remove_modnotice = isset($_POST["remove_modnotice"]) ? $_POST["remove_modnotice"] : "";
    if ($moderator || $forummoderator) {
        $extraquery = "UPDATE " . TSF_PREFIX . "threads SET closed = " . sqlesc($closed) . ", sticky = " . sqlesc($sticky) . " WHERE tid = " . sqlesc($tid);
    }
    if (strlen($_POST["subject"]) < $f_minmsglength || strlen($_POST["message"]) < $f_minmsglength) {
        $error = $lang->tsf_forums["too_short"];
    }
    $query = sql_query("SELECT dateline FROM " . TSF_PREFIX . "posts WHERE uid = " . sqlesc($CURUSER["id"]) . " ORDER by dateline DESC LIMIT 1");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $last_post = $Result["dateline"];
        $floodcheck = flood_check($lang->tsf_forums["a_post"], $last_post, true);
        if ($floodcheck != "") {
            $error = $floodcheck;
        }
    }
    $eq0 = $eq2 = "";
    if (empty($error)) {
        if ($usergroups["cansettingspanel"] != "yes") {
            $eq0 = ", edituid = " . $uid . ", edittime = " . $dateline;
        }
        if ($moderator || $forummoderator) {
            if ($remove_modnotice == "yes") {
                $eq2 = ", modnotice = '', modnotice_info = ''";
            } else {
                if (!empty($modnotice) && $modnotice != $thread["modnotice"]) {
                    $modnotice_info = implode("~", [$CURUSER["username"], $CURUSER["id"], TIMENOW]);
                    $eq2 = ", modnotice = " . sqlesc($modnotice) . ", modnotice_info = " . sqlesc($modnotice_info);
                }
            }
        }
        @sql_query("UPDATE " . TSF_PREFIX . "posts SET visible = " . $visible . ", subject = " . $subject . ", message = " . $message . $eq0 . $eq2 . " WHERE tid = " . @sqlesc($tid) . " AND pid = " . @sqlesc($pid)) || sqlerr(__FILE__, 155);
        if ($pid == $firstpost) {
            @sql_query("UPDATE " . TSF_PREFIX . "threads SET subject = " . $subject . " WHERE tid = " . @sqlesc($tid));
        }
        @sql_query("UPDATE " . TSF_PREFIX . "forums SET lastpostsubject = " . $subject . " WHERE lastposttid = '" . $tid . "' AND fid = '" . $fid . "'");
        if (isset($extraquery) && ($moderator || $forummoderator)) {
            @sql_query($extraquery) || sqlerr(__FILE__, 164);
        }
        $lastpage = isset($_GET["page"]) && is_valid_id($_GET["page"]) ? "&amp;page=" . intval($_GET["page"]) : "";
        if ($canpostattachments && $pid && $tid && isset($_FILES)) {
            $error = [];
            for ($i = 0; $i < 3; $i++) {
                if (isset($_FILES["attachment"]["size"][$i]) && 0 < $_FILES["attachment"]["size"][$i]) {
                    if (!is_uploaded_file($_FILES["attachment"]["tmp_name"][$i]) || empty($_FILES["attachment"]["tmp_name"][$i])) {
                        $error[] = $lang->tsf_forums["a_error2"] . " (" . htmlspecialchars_uni($_FILES["attachment"]["name"][$i]) . ")";
                    } else {
                        $ext = get_extension($_FILES["attachment"]["name"][$i]);
                        $allowed_ext = explode(",", $f_allowed_types);
                        if (!in_array($ext, $allowed_ext, true)) {
                            $error[] = $lang->tsf_forums["a_error3"] . " (" . htmlspecialchars_uni($_FILES["attachment"]["name"][$i]) . ")";
                        } else {
                            if ($f_upload_maxsize * 1024 < $_FILES["attachment"]["size"][$i] && !$moderator) {
                                $error[] = sprintf($lang->tsf_forums["a_error4"], mksize($f_upload_maxsize * 1024)) . " (" . htmlspecialchars_uni($_FILES["attachment"]["name"][$i]) . ")";
                            } else {
                                if (file_exists($f_upload_path . $_FILES["attachment"]["name"][$i])) {
                                    $error[] = $lang->tsf_forums["a_error5"] . " (" . htmlspecialchars_uni($_FILES["attachment"]["name"][$i]) . ")";
                                } else {
                                    $filename = preg_replace(["#/\$#", "/\\s+/"], "_", $_FILES["attachment"]["name"][$i]);
                                    $moved = @move_uploaded_file($_FILES["attachment"]["tmp_name"][$i], $f_upload_path . $filename);
                                    if (!$moved) {
                                        $error[] = $lang->tsf_forums["a_error2"] . " (" . htmlspecialchars_uni($_FILES["attachment"]["name"][$i]) . ")";
                                    }
                                }
                            }
                        }
                    }
                    if (count($error) == 0) {
                        $a_name = sqlesc($filename);
                        $a_size = sqlesc(0 + $_FILES["attachment"]["size"][$i]);
                        sql_query("INSERT INTO " . TSF_PREFIX . "attachments (a_name,a_size,a_tid,a_pid, visible) VALUES (" . $a_name . "," . $a_size . "," . $tid . "," . $pid . "," . $visible . ")");
                    }
                }
            }
        }
        if ($thread["moderate"] == 0 && $thread["moderaterf"] == 0) {
            define("FORCE_REDIRECT_MESSAGE", true);
            redirect(tsf_seo_clean_text($postsubject, "t", $tid, "&pid=" . $pid . "&scrollto=pid" . $pid . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "")), $lang->tsf_forums["post_edited"] . (is_array($error) && 0 < count($error) ? @implode("<br />", $error) : ""), $lang->tsf_forums["edit_this_post"], true);
            exit;
        }
        stdhead(str_replace("&amp;", "&", $orjthreadarray["currentforum"]));
        add_breadcrumb($orjthreadarray["deepforum"], tsf_seo_clean_text($orjthreadarray["deepforum"], "f", $orjthreadarray["deepforumid"]));
        add_breadcrumb($orjthreadarray["currentforum"], tsf_seo_clean_text($orjthreadarray["currentforum"], "fd", $fid));
        add_breadcrumb(htmlspecialchars_uni($threadsubject), tsf_seo_clean_text($threadsubject, "t", $tid));
        add_breadcrumb(htmlspecialchars_uni($postsubject), tsf_seo_clean_text($postsubject, "t", $tid, "&pid=" . $pid . "&scrollto=pid" . $pid . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "")));
        add_breadcrumb($lang->tsf_forums["edit_this_post"]);
        build_breadcrumb();
        stdmsg($lang->global["sys_message"], $lang->tsf_forums["moderatemsg1"]);
        stdfoot();
        exit;
    }
}
if (isset($_GET["do"]) && $_GET["do"] == "removefile" && ($aid = intval($_GET["aid"])) && is_valid_id($aid)) {
    delete_attachments($pid, $tid, $aid);
}
$a_query = sql_query("SELECT * FROM " . TSF_PREFIX . "attachments WHERE a_pid = " . sqlesc($pid) . " AND a_tid = " . sqlesc($tid));
if (0 < mysqli_num_rows($a_query)) {
    while ($s_attachments = mysqli_fetch_assoc($a_query)) {
        $a_array[$s_attachments["a_pid"]][] = $s_attachments;
    }
}
add_breadcrumb($thread["deepforum"], tsf_seo_clean_text($thread["deepforum"], "f", $thread["deepforumid"]));
add_breadcrumb($thread["currentforum"], tsf_seo_clean_text($thread["currentforum"], "fd", $fid));
add_breadcrumb(htmlspecialchars_uni($threadsubject), tsf_seo_clean_text($threadsubject, "t", $tid));
add_breadcrumb(htmlspecialchars_uni($postsubject), tsf_seo_clean_text($postsubject, "t", $tid, "&pid=" . $pid . "&scrollto=pid" . $pid . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "")));
add_breadcrumb($lang->tsf_forums["edit_this_post"]);
stdhead(str_replace("&amp;", "&", $thread["currentforum"]));
if (isset($warningmessage)) {
    echo $warningmessage;
}
build_breadcrumb();
define("IN_EDITOR", true);
include_once INC_PATH . "/editor.php";
$str = "\r\n<form method=\"post\" name=\"editpost\" action=\"" . $_SERVER["SCRIPT_NAME"] . (isset($_GET["page"]) && is_valid_id($_GET["page"]) ? "?page=" . intval($_GET["page"]) : "") . "\" enctype=\"multipart/form-data\">\r\n<input type=\"hidden\" name=\"tid\" value=\"" . $tid . "\">\r\n<input type=\"hidden\" name=\"pid\" value=\"" . $pid . "\">";
if (!empty($prvp)) {
    $str .= $prvp;
}
if (isset($error)) {
    stdmsg($lang->global["error"], $error, false);
}
if ($moderator || $forummoderator) {
    $postoptionstitle = [1 => $lang->tsf_forums["mod_options"], 2 => $lang->tsf_forums["modnotice1"]];
    $postoptions = [1 => "\r\n\t\t\t\t<label><input class=\"checkbox\" name=\"closethread\" value=\"yes\" type=\"checkbox\"" . (isset($_POST["closethread"]) && $_POST["closethread"] == "yes" ? " checked=\"checked\"" : ($thread["closed"] == "yes" ? " checked=\"checked\"" : "")) . ">" . $lang->tsf_forums["mod_options_c"] . "</label><br />\r\n\t\t\t\t<label><input class=\"checkbox\" name=\"stickthread\" value=\"yes\" type=\"checkbox\"" . (isset($_POST["stickthread"]) && $_POST["stickthread"] == "yes" ? " checked=\"checked\"" : ($thread["sticky"] == "1" ? " checked=\"checked\"" : "")) . ">" . $lang->tsf_forums["mod_options_s"] . "</label>\r\n\t\t\t\t</span>", 2 => "<textarea name=\"modnotice\" id=\"modnotice\" style=\"width: 99%; height: 50px;\" tabindex=\"3\">" . htmlspecialchars_uni(isset($_POST["modnotice"]) ? $_POST["modnotice"] : $thread["modnotice"]) . "</textarea><br />\r\n\t\t\t\t<label><input style=\"vertical-align: middle;\" class=\"checkbox\" name=\"remove_modnotice\" value=\"yes\" tabindex=\"6\" type=\"checkbox\"" . (isset($_POST["remove_modnotice"]) && $_POST["remove_modnotice"] == "yes" ? " checked='checked'" : "") . "> " . $lang->tsf_forums["modnotice2"] . "</label>"];
}
if (isset($a_array[$pid])) {
    $display_attachment = "\r\n\t\t<!-- start: attachments -->\r\n\t\t<label><input name=\"attachment[]\" size=\"50\" type=\"file\"></label><br /><label><input name=\"attachment[]\" size=\"50\" type=\"file\"></label><br /><label><input name=\"attachment[]\" size=\"50\" type=\"file\"></label>\r\n\t\t<br />\r\n\t\t<fieldset>\r\n\t\t\t<legend><strong>" . $lang->tsf_forums["a_info"] . "</strong></legend>";
    require_once INC_PATH . "/functions_get_file_icon.php";
    foreach ($a_array[$thread["pid"]] as $_a_left => $showperpost) {
        $display_attachment .= get_file_icon($showperpost["a_name"]) . " <a href=\"" . $BASEURL . "/tsf_forums/attachment.php?aid=" . $showperpost["a_id"] . "&tid=" . $showperpost["a_tid"] . "&pid=" . $thread["pid"] . "\" target=\"_blank\">" . htmlspecialchars_uni($showperpost["a_name"]) . "</a> (<b>" . $lang->tsf_forums["a_size"] . "</b>" . mksize($showperpost["a_size"]) . " / <b>" . $lang->tsf_forums["a_count"] . "</b>" . ts_nf($showperpost["a_count"]) . ") [<a href=\"" . $_SERVER["SCRIPT_NAME"] . "?tid=" . $tid . "&pid=" . $pid . "&aid=" . $showperpost["a_id"] . "&do=removefile\">X</a>]<br />";
    }
    $display_attachment .= "\r\n\t\t</fieldset>\r\n\t\t<!-- end: attachments -->\r\n\t";
} else {
    $display_attachment = "\r\n\t<label><input name=\"attachment[]\" size=\"50\" type=\"file\"></label><br /><label><input name=\"attachment[]\" size=\"50\" type=\"file\"></label><br /><label><input name=\"attachment[]\" size=\"50\" type=\"file\"></label>";
}
if ($display_attachment != "") {
    if (isset($postoptionstitle) && isset($postoptions)) {
        array_push($postoptionstitle, $lang->tsf_forums["attachment"]);
        array_push($postoptions, "<label>" . $display_attachment . "</label>");
    } else {
        $postoptionstitle = [1 => $lang->tsf_forums["attachment"]];
        $postoptions = [1 => "<label>" . $display_attachment . "</label>"];
    }
}
$str .= insert_editor(true, isset($_POST["subject"]) ? $_POST["subject"] : $postsubject, isset($_POST["message"]) ? $_POST["message"] : (isset($message) ? $message : ""), $lang->tsf_forums["edit_this_post"], "", $postoptionstitle, $postoptions);
echo $str;
stdfoot();

?>