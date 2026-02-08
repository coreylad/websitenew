<?php
define("THIS_SCRIPT", "newreply.php");
require "./global.php";
$tid = intval(TS_Global("tid"));
$pid = intval(TS_Global("pid"));
$canpostattachments = false;
ini_set("memory_limit", "250M");
if (!is_valid_id($tid) || !empty($pid) && !is_valid_id($pid)) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
($query = sql_query("SELECT\r\n\t\t\tt.subject as threadsubject, t.closed, t.sticky, f.type, f.name as currentforum, f.fid as currentforumid, f.moderate, ff.name as deepforum, ff.fid as deepforumid, ff.moderate as moderaterf\r\n\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = " . sqlesc($tid) . " LIMIT 0, 1")) || sqlerr(__FILE__, 46);
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$thread = $orjthreadarray = mysqli_fetch_assoc($query);
$forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
if (($thread["moderate"] == 1 || $thread["moderaterf"] == 1) && ($forummoderator || $moderator)) {
    $thread["moderate"] = 0;
    $thread["moderaterf"] = 0;
}
$visible = $thread["moderate"] == 1 || $thread["moderaterf"] == 1 ? 0 : 1;
if (!isset($permissions[$thread["currentforumid"]]["canview"]) || $permissions[$thread["currentforumid"]]["canview"] != "yes" || !isset($permissions[$thread["currentforumid"]]["canpostreplys"]) || $permissions[$thread["currentforumid"]]["canpostreplys"] != "yes") {
    print_no_permission();
    exit;
}
if ($thread["closed"] == "yes" && !$moderator && !$forummoderator) {
    stderr($lang->global["error"], $lang->tsf_forums["thread_closed"]);
    exit;
}
$useparent = false;
if ($thread["type"] == "s") {
    $useparent = true;
}
if (isset($permissions[$thread["currentforumid"]]["canpostattachments"]) && $permissions[$thread["currentforumid"]]["canpostattachments"] == "yes") {
    $canpostattachments = true;
}
if (!empty($pid)) {
    ($query = sql_query("SELECT p.message, p.tid, p.subject, u.username FROM " . TSF_PREFIX . "posts p LEFT JOIN users u ON (p.$uid = u.id) WHERE p.$pid = " . sqlesc($pid))) || sqlerr(__FILE__, 90);
    $Result = mysqli_fetch_assoc($query);
    $p_tid = $Result["tid"];
    if ($p_tid != $tid) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_post"]);
        exit;
    }
    $subject = $Result["subject"];
    $message = $Result["message"];
    if (!$forummoderator && !$moderator && preg_match("/\\[hide\\](.*?)\\[\\/hide\\]/is", $message)) {
        while (preg_match("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", $message)) {
            $message = preg_replace("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", "", $message);
        }
    }
    $username = $Result["username"];
    $subject = preg_replace("#RE:\\s?#i", "", $subject);
    $subject = $lang->tsf_forums["re"] . $subject;
    $threadsubject = ts_remove_badwords($subject);
    $message = "[$quote = " . $username . "]" . $message . "[/quote]";
    $replyto = $pid;
} else {
    $subject = $lang->tsf_forums["re"] . $thread["threadsubject"];
    $threadsubject = ts_remove_badwords($subject);
}
if (!isset($replyto)) {
    $replyto = 0;
}
$fid = 0 + $thread["currentforumid"];
$prvp = showPreview("message");
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $error = "";
    $subject = isset($_POST["subject"]) ? $_POST["subject"] : "";
    $uid = sqlesc($CURUSER["id"]);
    $username = sqlesc($CURUSER["username"]);
    $dateline = sqlesc(TIMENOW);
    $message = isset($_POST["message"]) ? $_POST["message"] : "";
    $ipaddress = sqlesc($CURUSER["ip"]);
    $closed = isset($_POST["closethread"]) && $_POST["closethread"] == "yes" && ($moderator || $forummoderator) ? "yes" : "no";
    $sticky = isset($_POST["stickthread"]) && $_POST["stickthread"] == "yes" && ($moderator || $forummoderator) ? 1 : 0;
    $subscribe = isset($_POST["subscribe"]) && $_POST["subscribe"] == "yes" ? 1 : 0;
    if ($subscribe) {
        ($query = sql_query("SELECT userid FROM " . TSF_PREFIX . "subscribe WHERE $tid = " . sqlesc($tid) . " AND $userid = " . $uid)) || sqlerr(__FILE__, 144);
        if (mysqli_num_rows($query) == 0) {
            sql_query("INSERT INTO " . TSF_PREFIX . "subscribe (tid,userid) VALUES (" . sqlesc($tid) . "," . $uid . ")") || sqlerr(__FILE__, 147);
        }
    }
    $extraquery = "";
    if ($moderator || $forummoderator) {
        $extraquery = ", $closed = " . sqlesc($closed) . ", $sticky = " . sqlesc($sticky);
    }
    if (strlen($subject) < $f_minmsglength || strlen($message) < $f_minmsglength) {
        $error = $lang->tsf_forums["too_short"];
    }
    $subject = sqlesc($subject);
    $message = sqlesc($message);
    ($query = sql_query("SELECT dateline FROM " . TSF_PREFIX . "posts WHERE `uid` = " . sqlesc($CURUSER["id"]) . " ORDER by dateline DESC LIMIT 1")) || sqlerr(__FILE__, 165);
    if (0 < mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $last_post = $Result["dateline"];
        $floodcheck = flood_check($lang->tsf_forums["a_post"], $last_post, true);
        if ($floodcheck != "") {
            $error = $floodcheck;
        }
    }
    if (empty($error)) {
        $iq1 = $iq2 = "";
        $iconid = isset($_POST["iconid"]) ? intval($_POST["iconid"]) : "";
        if (is_valid_id($iconid)) {
            $iq1 = "iconid,";
            $iq2 = $iconid . ",";
        }
        sql_query("INSERT INTO " . TSF_PREFIX . "posts (" . $iq1 . "tid,replyto,fid,subject,uid,username,dateline,message,ipaddress,visible) VALUES (" . $iq2 . $tid . "," . $replyto . "," . $fid . ", " . $subject . ", " . $uid . ", " . $username . ", " . $dateline . ", " . $message . ", " . $ipaddress . "," . $visible . ")") || sqlerr(__FILE__, 186);
        $pid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        sql_query("UPDATE " . TSF_PREFIX . "threads SET $replies = replies + 1, $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . $extraquery . " WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 189);
        sql_query("UPDATE " . TSF_PREFIX . "forums SET $posts = posts + 1, $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = '" . $fid . "'") || sqlerr(__FILE__, 191);
        if ($useparent) {
            sql_query("UPDATE " . TSF_PREFIX . "forums SET $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = '" . $thread["deepforumid"] . "'") || sqlerr(__FILE__, 195);
        }
        $TSSEConfig->TSLoadConfig("KPS");
        KPS("+", $kpscomment, $uid);
        send_sub_mails();
        sql_query("UPDATE users SET $totalposts = totalposts + 1 WHERE `id` = " . $uid) || sqlerr(__FILE__, 203);
        if ($canpostattachments && $pid && $tid) {
            $error = [];
            for ($i = 0; $i < 3; $i++) {
                if (0 < $_FILES["attachment"]["size"][$i]) {
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
                                    $_FILES["attachment"]["name"][$i] = str_replace("." . $ext, "", $_FILES["attachment"]["name"][$i]);
                                    $find = ["/[^a-zA-Z0-9\\s]/", "/\\s+/"];
                                    $replace = ["_", "_"];
                                    $filename = strtolower(preg_replace($find, $replace, $_FILES["attachment"]["name"][$i])) . "." . $ext;
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
                        sql_query("INSERT INTO " . TSF_PREFIX . "attachments (a_name,a_size,a_tid,a_pid,visible) VALUES (" . $a_name . "," . $a_size . "," . $tid . "," . $pid . "," . $visible . ")") || sqlerr(__FILE__, 250);
                    }
                }
            }
        }
        if ($thread["moderate"] == 0 && $thread["moderaterf"] == 0) {
            $TSSEConfig->TSLoadConfig("SHOUTBOX");
            if ($tsshoutbot == "yes" && TS_Match($tsshoutboxoptions, "newpost")) {
                require INC_PATH . "/functions_ajax_chatbot.php";
                $shoutbOT = sprintf($lang->tsf_forums["x_replied_thread"], "[URL=" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "]" . get_user_color($CURUSER["username"], $usergroups["namestyle"]) . "[/URL]", "[URL=" . tsf_seo_clean_text($threadsubject, "t", $tid) . "]" . cutename($threadsubject, 50, false) . "[/URL]");
                TSAjaxShoutBOT($shoutbOT);
            }
            define("FORCE_REDIRECT_MESSAGE", true);
            $lastpage = get_last_post($tid);
            redirect(tsf_seo_clean_text($threadsubject, "t", $tid, "&$page = " . $lastpage . "&$pid = " . $pid . "&$scrollto = pid" . $pid . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "")), $lang->tsf_forums["post_done"] . "<br />" . (is_array($error) && 0 < count($error) ? @implode("<br />", $error) : ""), $lang->tsf_forums["new_reply"], true);
            exit;
        }
        stdhead(str_replace("&amp;", "&", $orjthreadarray["currentforum"]));
        add_breadcrumb($orjthreadarray["deepforum"], tsf_seo_clean_text($orjthreadarray["deepforum"], "f", $orjthreadarray["deepforumid"]));
        add_breadcrumb($orjthreadarray["currentforum"], tsf_seo_clean_text($orjthreadarray["currentforum"], "fd", $fid));
        add_breadcrumb(htmlspecialchars_uni($threadsubject), tsf_seo_clean_text($threadsubject, "t", $tid));
        add_breadcrumb($lang->tsf_forums["new_reply"]);
        build_breadcrumb();
        stdmsg($lang->global["sys_message"], $lang->tsf_forums["moderatemsg1"]);
        stdfoot();
        exit;
    }
}
add_breadcrumb($thread["deepforum"], tsf_seo_clean_text($thread["deepforum"], "f", $thread["deepforumid"]));
add_breadcrumb($thread["currentforum"], tsf_seo_clean_text($thread["currentforum"], "fd", $fid));
add_breadcrumb(htmlspecialchars_uni($threadsubject), tsf_seo_clean_text($threadsubject, "t", $tid));
add_breadcrumb($lang->tsf_forums["new_reply"]);
stdhead(str_replace("&amp;", "&", $thread["currentforum"]));
if (isset($warningmessage)) {
    echo $warningmessage;
}
build_breadcrumb();
define("IN_EDITOR", true);
include_once INC_PATH . "/editor.php";
$str = "\r\n<form $method = \"post\" $name = \"newreply\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\" $enctype = \"multipart/form-data\">\r\n<input $type = \"hidden\" $name = \"tid\" $value = \"" . $tid . "\">\r\n<input $type = \"hidden\" $name = \"replyto\" $value = \"" . $replyto . "\">";
if (!empty($prvp)) {
    $str .= $prvp;
}
if (isset($error)) {
    stdmsg($lang->global["error"], $error, false);
}
if ($array_icon_list = show_icon_list()) {
    $postoptionstitle = [1 => $lang->tsf_forums["picons1"]];
    $postoptions = [1 => $array_icon_list];
}
if ($moderator || $forummoderator) {
    if (isset($postoptionstitle) && isset($postoptions)) {
        array_push($postoptionstitle, $lang->tsf_forums["mod_options"]);
        array_push($postoptions, "<label><input class=\"checkbox\" $name = \"closethread\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["closethread"]) && $_POST["closethread"] == "yes" ? " $checked = \"checked\"" : ($thread["closed"] == "yes" ? " $checked = \"checked\"" : "")) . ">" . $lang->tsf_forums["mod_options_c"] . "</label><br />\r\n\t\t\t\t<label><input class=\"checkbox\" $name = \"stickthread\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["stickthread"]) && $_POST["stickthread"] == "yes" ? " $checked = \"checked\"" : ($thread["sticky"] == "1" ? " $checked = \"checked\"" : "")) . ">" . $lang->tsf_forums["mod_options_s"] . "</label></span>");
    } else {
        $postoptionstitle = [1 => $lang->tsf_forums["mod_options"]];
        $postoptions = [1 => "<label><input class=\"checkbox\" $name = \"closethread\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["closethread"]) && $_POST["closethread"] == "yes" ? " $checked = \"checked\"" : ($thread["closed"] == "yes" ? " $checked = \"checked\"" : "")) . ">" . $lang->tsf_forums["mod_options_c"] . "</label><br />\r\n\t\t\t\t<label><input class=\"checkbox\" $name = \"stickthread\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["stickthread"]) && $_POST["stickthread"] == "yes" ? " $checked = \"checked\"" : ($thread["sticky"] == "1" ? " $checked = \"checked\"" : "")) . ">" . $lang->tsf_forums["mod_options_s"] . "</label></span>"];
    }
}
if ($canpostattachments) {
    if (isset($postoptionstitle) && isset($postoptions)) {
        array_push($postoptionstitle, $lang->tsf_forums["attachment"]);
        array_push($postoptions, "<label><input $name = \"attachment[]\" $size = \"50\" $type = \"file\"></label><br /><label><input $name = \"attachment[]\" $size = \"50\" $type = \"file\"></label><br /><label><input $name = \"attachment[]\" $size = \"50\" $type = \"file\"></label>");
        array_push($postoptionstitle, "<b>" . $lang->tsf_forums["subs"] . ":</b>");
        array_push($postoptions, "<label><input class=\"checkbox\" $name = \"subscribe\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["subscribe"]) && $_POST["subscribe"] == "yes" ? " $checked = \"checked\"" : "") . "></label> " . $lang->tsf_forums["isubs"]);
    } else {
        $postoptionstitle = [1 => $lang->tsf_forums["attachment"], 2 => "<b>" . $lang->tsf_forums["subs"] . ":</b>"];
        $postoptions = [1 => "<label><input $name = \"attachment[]\" $size = \"50\" $type = \"file\"></label><br /><label><input $name = \"attachment[]\" $size = \"50\" $type = \"file\"></label><br /><label><input $name = \"attachment[]\" $size = \"50\" $type = \"file\"></label>", 2 => "<label><input class=\"checkbox\" $name = \"subscribe\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["subscribe"]) && $_POST["subscribe"] == "yes" ? " $checked = \"checked\"" : "") . "></label> " . $lang->tsf_forums["isubs"]];
    }
} else {
    if (isset($postoptionstitle) && isset($postoptions)) {
        array_push($postoptionstitle, $lang->tsf_forums["subs"] . ":");
        array_push($postoptions, "<label><input class=\"checkbox\" $name = \"subscribe\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["subscribe"]) && $_POST["subscribe"] == "yes" ? " $checked = \"checked\"" : "") . "></label> " . $lang->tsf_forums["isubs"]);
    } else {
        $postoptionstitle = [1 => $lang->tsf_forums["subs"] . ":"];
        $postoptions = [1 => "<label><input class=\"checkbox\" $name = \"subscribe\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["subscribe"]) && $_POST["subscribe"] == "yes" ? " $checked = \"checked\"" : "") . "></label> " . $lang->tsf_forums["isubs"]];
    }
}
$str .= insert_editor(true, isset($_POST["subject"]) ? $_POST["subject"] : $threadsubject, isset($_POST["message"]) ? $_POST["message"] : (isset($message) ? $message : ""), $lang->tsf_forums["new_reply_head"], $lang->tsf_forums["new_reply_head2"] . htmlspecialchars_uni($threadsubject), $postoptionstitle, $postoptions);
echo $str;
($query = sql_query("\r\n\t\t\tSELECT p.*, u.username\r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN users u ON (p.$uid = u.id)\r\n\t\t\tWHERE p.$tid = '" . $tid . "' AND p.$visible = '1'\r\n\t\t\tORDER BY p.dateline DESC LIMIT 0, 5\r\n\t\t")) || sqlerr(__FILE__, 391);
if (mysqli_num_rows($query)) {
    echo "\r\n\t<br />\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" class=\"tborder\">\r\n\t<tr>\r\n\t<td class=\"thead\" $align = \"center\"><strong>" . $lang->tsf_forums["thread_review"] . "</strong></td>\r\n\t</tr>";
    while ($post = mysqli_fetch_assoc($query)) {
        $reviewpostdate = my_datee($dateformat, $post["dateline"]) . " " . my_datee($timeformat, $post["dateline"]);
        $reviewmessage = format_comment($post["message"]);
        echo "\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\">\r\n\t\t\t\t<span class=\"smalltext\"><strong>" . $lang->tsf_forums["posted_by"] . " " . $post["username"] . " - " . $reviewpostdate . "</strong></span>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"trow1\">\r\n\t\t\t\t" . $reviewmessage . "\r\n\t\t\t</td>\r\n\t\t</tr>";
    }
    echo "</table>";
}
stdfoot();

?>