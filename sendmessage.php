<?php
define("CSRF_PROTECTION", true);
define("SM_VERSION", "v2.1 by xam");
define("THIS_SCRIPT", "sendmessage.php");
require "./global.php";
$lang->load("messages");
$errors = [];
require_once INC_PATH . "/functions_message.php";
require_once INC_PATH . "/functions_pm.php";
$_GET["type"] = isset($_GET["type"]) ? $_GET["type"] : "";
$_GET["replyto"] = isset($_GET["replyto"]) ? $_GET["replyto"] : "";
if (!isset($CURUSER) || $usergroups["canpm"] != "yes" || $CURUSER["id"] == 0) {
    print_no_permission();
}
($query = sql_query("SELECT canmessage FROM ts_u_perm WHERE `userid` = " . sqlesc($CURUSER["id"]))) || sqlerr(__FILE__, 36);
if (0 < mysqli_num_rows($query)) {
    $messageperm = mysqli_fetch_assoc($query);
    if ($messageperm["canmessage"] == "0") {
        print_no_permission();
    }
}
$query = sql_query("SELECT added FROM messages WHERE $sender = " . sqlesc($CURUSER["id"]) . " ORDER by added DESC LIMIT 1");
if (0 < mysqli_num_rows($query)) {
    $Result = mysqli_fetch_assoc($query);
    $last_pm = $Result["added"];
    flood_check($lang->messages["floodcomment"], $last_pm);
}
$prvp = showPreview("message");
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $save = isset($_POST["save"]) && $_POST["save"] == "yes" ? "yes" : "no";
    $msg = isset($_POST["message"]) ? trim($_POST["message"]) : "";
    $subject = isset($_POST["subject"]) ? trim($_POST["subject"]) : "";
    $receiver = isset($_POST["receivername"]) ? htmlspecialchars_uni($_POST["receivername"]) : "";
    $origmsg = isset($_POST["origmsg"]) ? 0 + $_POST["origmsg"] : "";
    if (!empty($origmsg) && !is_valid_id($origmsg)) {
        $errors[] = $lang->messages["newtitle11"];
    }
    if (empty($msg) || empty($subject) || empty($receiver)) {
        $errors[] = $lang->global["dontleavefieldsblank"];
    }
    if (count($errors) == 0) {
        if (strpos($receiver, ";") === false) {
            if ($CURUSER["username"] == $receiver && !$is_mod) {
                $errors[] = $lang->messages["msgsenderror"];
            } else {
                $where = "WHERE u.$enabled = 'yes' AND u.$username = " . sqlesc($receiver);
                $res = sql_query("SELECT u.username,u.usergroup,u.options,u.email,u.id,u.notifs, UNIX_TIMESTAMP(u.last_access) as la, g.canstaffpanel, g.issupermod, g.cansettingspanel FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) " . $where);
                $receiver = mysqli_fetch_assoc($res);
                if (!$receiver || !$receiver["username"]) {
                    $errors[] = $lang->messages["nouser"];
                } else {
                    if (!$is_mod) {
                        $check_msg_perm = check_msg_perm($receiver);
                        if (!empty($check_msg_perm)) {
                            $errors[] = $check_msg_perm;
                        }
                    }
                    if (!pm_limit(false, true, $receiver["id"], $receiver["usergroup"]) && !is_mod($receiver) && !$is_mod) {
                        $errors[] = $lang->messages["error10"];
                    }
                    if (count($errors) == 0) {
                        send_pm($receiver["id"], $msg, $subject, $CURUSER["id"], $save);
                        $msgid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                        $date = get_date_time();
                        if (strpos($receiver["notifs"], "[pm]") !== false) {
                            $username = trim($CURUSER["username"]);
                            $msg_receiver = trim($receiver["username"]);
                            $body = sprintf($lang->messages["body"], $msg_receiver, $SITENAME, $username, $BASEURL);
                            @sent_mail($receiver["email"], @sprintf($lang->messages["msgsubject"], $SITENAME), $body, "sendmessage", false);
                        }
                    }
                }
            }
        } else {
            $receiver = preg_replace("#\\s+#", "", $receiver);
            $Array = explode(";", $receiver);
            if (5 < count($Array)) {
                $errors[] = sprintf($lang->messages["smmultiplerrror2"], count($Array));
            }
            if (in_array($CURUSER["username"], $Array) && !$is_mod) {
                $errors[] = $lang->messages["msgsenderror"];
            }
            if (count($errors) == 0) {
                $AUsername = "";
                $PostedUsers = [];
                foreach ($Array as $receiver) {
                    $AUsername = htmlspecialchars_uni($receiver);
                    if (!in_array($AUsername, $PostedUsers)) {
                        $where = "WHERE u.$enabled = 'yes' AND u.$username = " . sqlesc(trim($receiver));
                        $res = sql_query("SELECT u.username,u.usergroup,u.options,u.email,u.id,u.notifs, UNIX_TIMESTAMP(u.last_access) as la, g.canstaffpanel, g.issupermod, g.cansettingspanel FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) " . $where);
                        $receiver = mysqli_fetch_assoc($res);
                        if (!$receiver || !$receiver["username"]) {
                            $errors[] = sprintf($lang->messages["smmultiplerrror"], $AUsername);
                        }
                        if (!$is_mod) {
                            $check_msg_perm = check_msg_perm($receiver);
                            if (!empty($check_msg_perm)) {
                                $errors[] = $check_msg_perm;
                            }
                        }
                        if (!pm_limit(false, true, $receiver["id"], $receiver["usergroup"]) && !is_mod($receiver) && !$is_mod) {
                            $errors[] = $lang->messages["error10"];
                        }
                        if (count($errors) == 0) {
                            send_pm($receiver["id"], $msg, $subject, $CURUSER["id"], $save);
                            $msgid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                            $date = get_date_time();
                            if (strpos($receiver["notifs"], "[pm]") !== false) {
                                $username = trim($CURUSER["username"]);
                                $msg_receiver = trim($receiver["username"]);
                                $body = sprintf($lang->messages["body"], $msg_receiver, $SITENAME, $username, $BASEURL);
                                @sent_mail($receiver["email"], @sprintf($lang->messages["msgsubject"], $SITENAME), $body, "sendmessage", false);
                            }
                            $PostedUsers[] = $AUsername;
                        }
                    }
                }
            }
        }
    }
    if (!empty($origmsg) && count($errors) == 0 && isset($_POST["delete"]) && $_POST["delete"] == "yes") {
        $res = sql_query("SELECT receiver,saved FROM messages WHERE `id` = " . sqlesc($origmsg));
        if (0 < mysqli_num_rows($res)) {
            $arr = mysqli_fetch_assoc($res);
            if ($arr["receiver"] != $CURUSER["id"]) {
                redirect("messages.php");
                exit;
            }
            if ($arr["saved"] == "no") {
                sql_query("DELETE FROM messages WHERE `id` = " . sqlesc($origmsg));
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    unset($_POST["returnto"]);
                }
            } else {
                if ($arr["saved"] == "yes") {
                    sql_query("UPDATE messages SET $location = '0' WHERE `id` = " . sqlesc($origmsg));
                    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                        unset($_POST["returnto"]);
                    }
                }
            }
        }
    }
    if (count($errors) == 0) {
        define("FORCE_REDIRECT_MESSAGE", true);
        $returnto = !empty($_POST["returnto"]) ? fix_url($_POST["returnto"]) : "messages.php";
        $returnto = str_replace([$BASEURL, "//"], ["", "/"], $returnto);
        redirect($returnto, $lang->global["msgsend"]);
        exit;
    }
}
$compose = true;
if (!empty($_GET["receiver"]) && is_valid_id($_GET["receiver"])) {
    $compose = false;
    $receiver = 0 + $_GET["receiver"];
    if ($CURUSER["id"] == $receiver && !$is_mod) {
        $errors[] = $lang->messages["msgsenderror"];
    } else {
        $res = sql_query("SELECT u.options, u.id, u.usergroup, u.username, g.canstaffpanel, g.issupermod, g.cansettingspanel, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$enabled = 'yes' AND u.$id = " . sqlesc($receiver));
        $receiver = mysqli_fetch_assoc($res);
        if (!$receiver || !$receiver["username"]) {
            unset($receiver);
            $errors[] = $lang->messages["nouser"];
        } else {
            if (!$is_mod && $_GET["type"] != "forward") {
                $check_msg_perm = check_msg_perm($receiver);
                if (!empty($check_msg_perm)) {
                    $errors[] = $check_msg_perm;
                }
            }
            if (!pm_limit(false, true, $receiver["id"], $receiver["usergroup"]) && !is_mod($receiver) && !$is_mod) {
                $errors[] = $lang->messages["error10"];
            }
            if (!empty($_GET["replyto"]) && is_valid_id($_GET["replyto"]) && count($errors) == 0) {
                $replyto = 0 + $_GET["replyto"];
                $res = sql_query("SELECT receiver,sender,msg,subject FROM messages WHERE `id` = " . sqlesc($replyto));
                $msga = mysqli_fetch_assoc($res);
                if (!$msga || $msga["receiver"] != $CURUSER["id"] && $usergroups["cansettingspanel"] != "yes") {
                    $errors[] = $lang->messages["newtitle11"];
                } else {
                    $res = sql_query("SELECT username FROM users WHERE `id` = " . sqlesc($msga["sender"]));
                    $usra = mysqli_fetch_assoc($res);
                    if (!$usra || !$usra["username"]) {
                        $errors[] = $lang->messages["newtitle11"];
                    } else {
                        if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
                            $eol = "\r\n";
                        } else {
                            if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
                                $eol = "\r";
                            } else {
                                $eol = "\n";
                            }
                        }
                        $body = "[$quote = " . htmlspecialchars_uni($usra["username"]) . "]" . $msga["msg"] . "[/quote]" . $eol . $eol;
                        $subject = preg_replace("#(FW|RE):( *)#is", "", $msga["subject"]);
                        $subject = (isset($_GET["type"]) && $_GET["type"] == "forward" ? "FW: " : "Re: ") . $subject;
                    }
                }
            }
        }
    }
}
if ((!isset($_GET["receiver"]) || $_GET["receiver"] == "0") && isset($_GET["type"]) && $_GET["type"] == "forward" && !empty($_GET["replyto"]) && is_valid_id($_GET["replyto"])) {
    $replyto = 0 + $_GET["replyto"];
    $res = sql_query("SELECT receiver,msg,subject FROM messages WHERE `id` = " . sqlesc($replyto));
    $msga = mysqli_fetch_assoc($res);
    if (!$msga || $msga["receiver"] != $CURUSER["id"] && !$is_mod) {
        $errors[] = $lang->messages["newtitle11"];
    }
    if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
        $eol = "\r\n";
    } else {
        if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
            $eol = "\r";
        } else {
            $eol = "\n";
        }
    }
    $body = "[$quote = System]" . $msga["msg"] . "[/quote]" . $eol . $eol;
    $subject = preg_replace("#(FW|RE):( *)#is", "", $msga["subject"]);
    $subject = "FW: " . $subject;
}
stdhead($lang->messages["head"]);
show_sm_errors();
$returnto = isset($_GET["returnto"]) ? fix_url($_GET["returnto"]) : (isset($_POST["returnto"]) ? fix_url($_POST["returnto"]) : (isset($_SERVER["HTTP_REFERER"]) ? fix_url($_SERVER["HTTP_REFERER"]) : ""));
define("IN_EDITOR", true);
include_once INC_PATH . "/editor.php";
$str = "\n<form $method = \"post\" $name = \"compose\" $action = \"" . $_SERVER["SCRIPT_NAME"] . (!$compose ? "?$receiver = " . $receiver["id"] . "&$replyto = " . (isset($replyto) ? $replyto : "") . (isset($_GET["type"]) ? "&$type = " . htmlspecialchars_uni($_GET["type"]) : "") : "") . "\">\n<input $type = \"hidden\" $name = \"returnto\" $value = \"" . $returnto . "\" />\n<input $type = \"hidden\" $name = \"securitytoken\" $value = \"" . $CURUSER["securitytoken"] . "\" />\n" . (!$compose ? "\n<input $type = \"hidden\" $name = \"origmsg\" $value = \"" . (isset($replyto) ? (int) $replyto : "") . "\">" : "<input $type = \"hidden\" $name = \"compose\" $value = \"1\" />");
$postoptionstitle = [1 => $lang->global["buttonsave"], 2 => isset($replyto) ? $lang->global["buttondelete"] : ""];
$postoptions = [1 => "<input $type = \"checkbox\" $name = \"save\" $value = \"yes\" /> " . $lang->messages["savemsg"], 2 => isset($replyto) ? "<input $type = \"checkbox\" $name = \"delete\" $value = \"yes\" /> " . $lang->messages["delmsg"] : ""];
$extrasubject = [$lang->global["sendtousername"] => "<input $tabindex = \"2\" $name = \"receivername\" $size = \"52\" $type = \"text\" $value = \"" . (isset($_POST["receivername"]) ? htmlspecialchars_uni($_POST["receivername"]) : (isset($_GET["type"]) && $_GET["type"] == "forward" ? "" : (isset($receiver["username"]) ? $receiver["username"] : ""))) . "\" $rel = \"autoCompleteUsers\" /> " . $lang->messages["smmultiple"]];
if ($is_mod) {
    $StandartPMS = [$lang->messages["sm"] => "\n\t<select $name = \"standartpms\" $onchange = \"TS_Standart_Messages(this.value);\">\n\t\t<option $value = \"0\">" . $lang->messages["sm"] . "</option>\n\t\t<option $value = \"1\">" . $lang->messages["sm1a"] . "</option>\n\t\t<option $value = \"2\">" . $lang->messages["sm2a"] . "</option>\n\t\t<option $value = \"3\">" . $lang->messages["sm3a"] . "</option>\n\t\t<option $value = \"4\">" . $lang->messages["sm4a"] . "</option>\n\t\t<option $value = \"5\">" . $lang->messages["sm5a"] . "</option>\n\t\t<option $value = \"6\">" . $lang->messages["sm6a"] . "</option>\n\t\t<option $value = \"7\">" . $lang->messages["sm7a"] . "</option>\n\t\t<option $value = \"8\">" . $lang->messages["sm8a"] . "</option>\n\t\t<option $value = \"9\">" . $lang->messages["sm9a"] . "</option>\n\t\t<option $value = \"10\">" . $lang->messages["sm10a"] . "</option>\n\t</select>"];
    $extrasubject = $extrasubject + $StandartPMS;
}
if (!empty($prvp)) {
    $str .= $prvp;
}
$str .= insert_editor(true, isset($_POST["subject"]) ? $_POST["subject"] : (isset($subject) ? $subject : ""), isset($_POST["message"]) ? $_POST["message"] : (isset($body) ? $body : ""), $lang->messages["head"], $compose || isset($_GET["type"]) && $_GET["type"] == "forward" ? $lang->messages["title"] : $lang->messages["title2"] . " <a $href = \"" . ts_seo($receiver["id"], $receiver["username"]) . "\">" . get_user_color($receiver["username"], $receiver["namestyle"]) . "</a>", $postoptionstitle, $postoptions, true, $extrasubject, $lang->global["buttonsend"]);
$str .= "</form>";
echo $str . ($is_mod ? "\n<script $type = \"text/javascript\">\n\tfunction TS_Standart_Messages(mID)\n\t{\n\t\tif (mID && mID > 0)\n\t\t{\n\t\t\$tmessages = new Array();\n\t\t\tmessages[1] = \"" . $lang->messages["sm1b"] . "\";\n\t\t\tmessages[2] = \"" . $lang->messages["sm2b"] . "\";\n\t\t\tmessages[3] = \"" . $lang->messages["sm3b"] . "\";\n\t\t\tmessages[4] = \"" . $lang->messages["sm4b"] . "\";\n\t\t\tmessages[5] = \"" . $lang->messages["sm5b"] . "\";\n\t\t\tmessages[6] = \"" . $lang->messages["sm6b"] . "\";\n\t\t\tmessages[7] = \"" . $lang->messages["sm7b"] . "\";\n\t\t\tmessages[8] = \"" . $lang->messages["sm8b"] . "\";\n\t\t\tmessages[9] = \"" . $lang->messages["sm9b"] . "\";\n\t\t\tmessages[10] = \"" . $lang->messages["sm10b"] . "\";\n\t\t\tif (messages[mID])\n\t\t\t{\n\t\t\t\$ted = tinyMCE.activeEditor;\n\t\t\t\ted.focus();\n\t\t\t\ted.selection.setContent(ed.selection.getContent()+messages[mID]);\n\t\t\t}\n\t\t}\n\t}\n</script>" : "");
stdfoot();
function check_msg_perm($receiver = [])
{
    global $lang;
    global $CURUSER;
    if (TS_Match($receiver["options"], "A1")) {
        return $lang->messages["error9"];
    }
    if (TS_Match($receiver["options"], "K1")) {
        $res2 = sql_query("SELECT id FROM friends WHERE `status` = 'b' AND $userid = " . (int) $receiver["id"] . " AND $friendid = " . (int) $CURUSER["id"]);
        if (0 < mysqli_num_rows($res2)) {
            return $lang->messages["error5"];
        }
    } else {
        if (TS_Match($receiver["options"], "K2")) {
            $res2 = sql_query("SELECT id FROM friends WHERE `status` = 'c' AND $userid = " . (int) $receiver["id"] . " AND $friendid = " . (int) $CURUSER["id"]);
            if (mysqli_num_rows($res2) < 1) {
                return $lang->messages["error6"];
            }
        } else {
            return $lang->messages["error7"];
        }
    }
}
function show_sm_errors()
{
    global $errors;
    global $lang;
    if (0 < count($errors)) {
        $error = implode("<br />", $errors);
        echo "\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\n\t\t\t<tr>\n\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t" . $lang->global["error"] . "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td>\n\t\t\t\t\t<font $color = \"red\">\n\t\t\t\t\t\t<strong>\n\t\t\t\t\t\t\t" . $error . "\n\t\t\t\t\t\t</strong>\n\t\t\t\t\t</font>\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t</table>\n\t\t\t<br />\n\t\t";
    }
}

?>