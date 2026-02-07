<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "newthread.php");
require "./global.php";
$fid = intval(TS_Global("fid"));
$polloptions = isset($_POST["polloptions"]) ? intval($_POST["polloptions"]) : 4;
$createpoll = isset($_POST["createpoll"]) && $_POST["createpoll"] == "yes" ? "yes" : "no";
$canpostattachments = false;
if (is_valid_id($fid)) {
    if (!isset($permissions[$fid]["canview"]) || $permissions[$fid]["canview"] != "yes" || !isset($permissions[$fid]["canpostthreads"]) || $permissions[$fid]["canpostthreads"] != "yes") {
        print_no_permission();
        exit;
    }
    ($query = @sql_query("SELECT f.name, f.pid, f.type, f.moderate, ff.name as realforum, ff.fid as realforumid, ff.moderate as moderaterf FROM " . TSF_PREFIX . "forums f LEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid) WHERE f.$fid = " . @sqlesc($fid))) || sqlerr(__FILE__, 42);
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
        exit;
    }
    $Result = mysqli_fetch_assoc($query);
    $realforum = $Result["realforum"];
    $realforumid = $Result["realforumid"];
    $forumname = $Result["name"];
    $parent = $Result["pid"];
    $type = $Result["type"];
    $moderateForum = $Result["moderate"];
    $moderateForumRF = $Result["moderaterf"];
    $forummoderator = is_forum_mod($type == "s" ? $realforumid : $fid, $CURUSER["id"]);
    if (($moderateForum == 1 || $moderateForumRF == 1) && ($forummoderator || $moderator)) {
        $moderateForum = 0;
        $moderateForumRF = 0;
    }
    $visible = $moderateForum == 1 || $moderateForumRF == 1 ? 0 : 1;
    if ($permissions[$fid]["canpostattachments"] == "yes") {
        $canpostattachments = true;
    }
    if ($permissions[$fid]["canview"] != "yes" || $permissions[$fid]["canpostthreads"] != "yes") {
        print_no_permission();
        exit;
    }
    if ($type == "c") {
        stderr($lang->global["error"], $lang->tsf_forums["cant_post"]);
        exit;
    }
    $useparent = false;
    if ($type == "s") {
        $useparent = true;
    }
    add_breadcrumb($realforum, tsf_seo_clean_text($realforum, "f", $realforumid));
    add_breadcrumb($forumname, tsf_seo_clean_text($forumname, "fd", $fid));
    add_breadcrumb($lang->tsf_forums["new_thread"]);
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
        if (strlen($subject) < $f_minmsglength || strlen($message) < $f_minmsglength) {
            $error = $lang->tsf_forums["too_short"];
        }
        $orjSubject = $subject;
        $subject = sqlesc($subject);
        $message = sqlesc($message);
        ($query = sql_query("SELECT dateline FROM " . TSF_PREFIX . "posts WHERE $uid = " . sqlesc($CURUSER["id"]) . " ORDER by dateline DESC LIMIT 1")) || sqlerr(__FILE__, 124);
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
            $iconid = isset($_POST["iconid"]) ? intval($_POST["iconid"]) : 0;
            if (is_valid_id($iconid)) {
                $iq1 = "iconid,";
                $iq2 = $iconid . ",";
            }
            @sql_query("INSERT INTO " . TSF_PREFIX . "posts (" . $iq1 . "fid,subject,uid,username,dateline,message,ipaddress,visible) VALUES (" . $iq2 . $fid . ", " . $subject . ", " . $uid . ", " . $username . ", " . $dateline . ", " . $message . ", " . $ipaddress . "," . $visible . ")") || sqlerr(__FILE__, 145);
            $pid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            @sql_query("INSERT INTO " . TSF_PREFIX . "threads (" . $iq1 . "fid,subject,uid,username,dateline,firstpost,lastpost,lastposter,lastposteruid,closed,sticky,visible) VALUES (" . $iq2 . $fid . "," . $subject . "," . $uid . "," . $username . "," . $dateline . "," . $pid . "," . $dateline . "," . $username . "," . $uid . "," . @sqlesc($closed) . "," . $sticky . "," . $visible . ")") || sqlerr(__FILE__, 148);
            $tid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            if ($subscribe) {
                sql_query("INSERT INTO " . TSF_PREFIX . "subscribe (tid,userid) VALUES (" . sqlesc($tid) . "," . $uid . ")") || sqlerr(__FILE__, 153);
            }
            @sql_query("UPDATE " . TSF_PREFIX . "posts SET $tid = " . $tid . " WHERE $pid = '" . $pid . "'") || sqlerr(__FILE__, 156);
            @sql_query("UPDATE " . TSF_PREFIX . "forums SET $threads = threads + 1, $posts = posts + 1, $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = '" . $fid . "'") || sqlerr(__FILE__, 158);
            if ($useparent) {
                @sql_query("UPDATE " . TSF_PREFIX . "forums SET $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = '" . $realforumid . "'") || sqlerr(__FILE__, 162);
            }
            @sql_query("UPDATE users SET $totalposts = totalposts + 1 WHERE $id = " . $uid) || sqlerr(__FILE__, 165);
            $TSSEConfig->TSLoadConfig("KPS");
            KPS("+", $kpscomment, $uid);
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
                            sql_query("INSERT INTO " . TSF_PREFIX . "attachments (a_name,a_size,a_tid,a_pid,visible) VALUES (" . $a_name . "," . $a_size . "," . $tid . "," . $pid . "," . $visible . ")") || sqlerr(__FILE__, 215);
                        }
                    }
                }
            }
            $TSSEConfig->TSLoadConfig("SHOUTBOX");
            if ($tsshoutbot == "yes" && TS_Match($tsshoutboxoptions, "newthread")) {
                require INC_PATH . "/functions_ajax_chatbot.php";
                $shoutbOT = sprintf($lang->tsf_forums["x_posted_thread"], "[URL=" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "]" . get_user_color($CURUSER["username"], $usergroups["namestyle"]) . "[/URL]", "[URL=" . tsf_seo_clean_text($orjSubject, "t", $tid) . "]" . cutename($orjSubject, 50, false) . "[/URL]");
                TSAjaxShoutBOT($shoutbOT);
            }
            if ($createpoll == "yes" && $usergroups["cancreatepoll"] == "yes") {
                define("FORCE_REDIRECT_MESSAGE", true);
                redirect("tsf_forums/poll.php?do=new&amp;$tid = " . $tid . "&amp;$polloptions = " . $polloptions, $lang->tsf_forums["poll10"] . "<br />" . (is_array($error) && 0 < count($error) ? @implode("<br />", $error) : ""));
                exit;
            }
            if ($moderateForum == 0 && $moderateForumRF == 0) {
                define("FORCE_REDIRECT_MESSAGE", true);
                redirect(tsf_seo_clean_text($subject, "t", $tid), $lang->tsf_forums["thread_created"] . "<br />" . (is_array($error) && 0 < count($error) ? @implode("<br />", $error) : ""), sprintf($lang->tsf_forums["new_thread_in"], str_replace("&amp;", "&", $forumname)), true);
                exit;
            }
            $new_thread_in = sprintf($lang->tsf_forums["new_thread_in"], str_replace("&amp;", "&", $forumname));
            stdhead($new_thread_in);
            build_breadcrumb();
            stdmsg($lang->global["sys_message"], $lang->tsf_forums["moderatemsg1"]);
            stdfoot();
            exit;
        }
    }
    $new_thread_in = sprintf($lang->tsf_forums["new_thread_in"], str_replace("&amp;", "&", $forumname));
    stdhead($new_thread_in);
    if (isset($warningmessage)) {
        echo $warningmessage;
    }
    build_breadcrumb();
    $prvp = showPreview("message");
    define("IN_EDITOR", true);
    include_once INC_PATH . "/editor.php";
    $str = "\r\n<form $method = \"post\" $name = \"newthread\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\" $enctype = \"multipart/form-data\">\r\n<input $type = \"hidden\" $name = \"fid\" $value = \"" . $fid . "\">";
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
            array_push($postoptions, "<label><input class=\"checkbox\" $name = \"closethread\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["closethread"]) && $_POST["closethread"] == "yes" ? " $checked = \"checked\"" : "") . ">" . $lang->tsf_forums["mod_options_c"] . "</label><br /><label><input class=\"checkbox\" $name = \"stickthread\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["stickthread"]) && $_POST["stickthread"] == "yes" ? " $checked = \"checked\"" : "") . ">" . $lang->tsf_forums["mod_options_s"] . "</label></span>");
        } else {
            $postoptionstitle = [1 => $lang->tsf_forums["mod_options"]];
            $postoptions = [1 => "\r\n\t\t\t\t\t<label><input class=\"checkbox\" $name = \"closethread\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["closethread"]) && $_POST["closethread"] == "yes" ? " $checked = \"checked\"" : "") . ">" . $lang->tsf_forums["mod_options_c"] . "</label><br />\r\n\t\t\t\t\t<label><input class=\"checkbox\" $name = \"stickthread\" $value = \"yes\" $type = \"checkbox\"" . (isset($_POST["stickthread"]) && $_POST["stickthread"] == "yes" ? " $checked = \"checked\"" : "") . ">" . $lang->tsf_forums["mod_options_s"] . "</label></span>"];
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
    if ($usergroups["cancreatepoll"] == "yes") {
        if (isset($postoptionstitle) && isset($postoptions)) {
            array_push($postoptionstitle, $lang->tsf_forums["poll1"] . ":");
            array_push($postoptions, "<label><input class=\"checkbox\" $name = \"createpoll\" $value = \"yes\" $type = \"checkbox\"" . ($createpoll == "yes" ? " $checked = \"checked\"" : "") . "> " . $lang->tsf_forums["poll2"] . "</label><br />" . $lang->tsf_forums["poll3"] . " <label><input $size = \"2\" $name = \"polloptions\" $value = \"" . $polloptions . "\" $type = \"text\"></label>");
        } else {
            $postoptionstitle = [1 => $lang->tsf_forums["poll1"] . ":"];
            $postoptions = [1 => "<label><input class=\"checkbox\" $name = \"createpoll\" $value = \"yes\" $type = \"checkbox\"" . ($createpoll == "yes" ? " $checked = \"checked\"" : "") . "> " . $lang->tsf_forums["poll2"] . "</label><br />" . $lang->tsf_forums["poll3"] . " <label><input $size = \"2\" $name = \"polloptions\" $value = \"" . $polloptions . "\" $type = \"text\"></label>"];
        }
    }
    $str .= insert_editor(true, isset($_POST["subject"]) ? $_POST["subject"] : "", isset($_POST["message"]) ? $_POST["message"] : "", $lang->tsf_forums["new_thread_head"], $new_thread_in, $postoptionstitle, $postoptions);
    echo $str;
    stdfoot();
} else {
    stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
    exit;
}

?>