<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "messages.php");
require "./global.php";
$fromAjax = strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["fma"]) ? true : false;
if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
    if ($fromAjax) {
        print_no_permission(false, false, "", false);
    } else {
        print_no_permission();
    }
    exit;
}
$lang->load("messages");
define("MSG_VERSION", "2.7.6 by xam");
$do = isset($_POST["do"]) ? htmlspecialchars($_POST["do"]) : (isset($_GET["do"]) ? htmlspecialchars($_GET["do"]) : "");
$mailboxes = ["INBOX" => 1, "SENDBOX" => -1, "PMDELETED" => 0];
$userid = isset($_GET["userid"]) && is_valid_id($_GET["userid"]) && $usergroups["cansettingspanel"] == "yes" ? intval($_GET["userid"]) : intval($CURUSER["id"]);
$_errors = [];
$moderator = $is_mod;
$maxboxs = !$moderator ? 3 : 6;
if (isset($_GET["mailbox"])) {
    $mailbox = intval($_GET["mailbox"]);
} else {
    $mailbox = $mailboxes["INBOX"];
}
$mailbox = $mailbox == 0 ? $mailboxes["INBOX"] : $mailbox;
require_once INC_PATH . "/functions_message.php";
$folderid = 0;
switch ($mailbox) {
    case "1":
        $foldername = $lang->messages["inbox"];
        $folderid = $mailbox;
        break;
    case "-1":
        $foldername = $lang->messages["sendbox"];
        break;
    default:
        $query = sql_query("SELECT name, boxnumber FROM pmboxes WHERE $boxnumber = '" . $mailbox . "' AND $userid = '" . $userid . "' LIMIT 1");
        if (0 < mysqli_num_rows($query)) {
            $pmboxes = mysqli_fetch_assoc($query);
            $foldername = $pmboxes["name"];
            $foldername = empty($foldername) ? $lang->messages["Unknown"] : htmlspecialchars_uni($foldername);
            $boxnumber = intval($pmboxes["boxnumber"]);
            $folderid = $boxnumber == 0 ? $mailboxes["INBOX"] : $boxnumber;
        } else {
            $foldername = $lang->messages["inbox"];
            $folderid = $mailboxes["INBOX"];
        }
        if (!empty($do) && $do != "showpm" && $do != "editfolders" && $do != "emptybox" && $do != "download" && $do != "search") {
            $pmids = isset($_POST["pmid"]) ? $_POST["pmid"] : "";
            if (!is_array($pmids) || count($pmids) < 1) {
                $do = "";
                $_errors[] = $lang->messages["newtitle9"];
            } else {
                foreach ($pmids as $checkid) {
                    if (!is_valid_id($checkid)) {
                        $do = "";
                    }
                }
            }
        }
        if ($do == "editfolders") {
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
                if (isset($_POST["customfolders"]) && is_array($_POST["customfolders"]) && 0 < count($_POST["customfolders"])) {
                    foreach ($_POST["customfolders"] as $Uboxnumber => $Uname) {
                        if (empty($Uname)) {
                            $deletepmids = [];
                            $deletepmbx = sql_query("DELETE FROM pmboxes WHERE $boxnumber = " . sqlesc($Uboxnumber) . " AND $userid = " . $userid);
                            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                $query = sql_query("SELECT id FROM messages WHERE $location = " . sqlesc($Uboxnumber));
                                while ($Dpmids = mysqli_fetch_assoc($query)) {
                                    $deletepmids[] = $Dpmids["id"];
                                }
                                if (0 < count($deletepmids)) {
                                    delete_pms($deletepmids);
                                }
                            }
                        } else {
                            if (is_valid_id($Uboxnumber)) {
                                sql_query("UPDATE pmboxes SET $name = " . sqlesc(trim($Uname)) . " WHERE $boxnumber = " . sqlesc($Uboxnumber) . " AND $userid = '" . $userid . "'");
                            }
                        }
                    }
                }
                for ($i = 0; $i < $maxboxs && $i < 3; $i++) {
                    if (!empty($_POST["newfolder" . $i]) && 2 < strlen($_POST["newfolder" . $i])) {
                        $newfolders[] = trim($_POST["newfolder" . $i]);
                    }
                }
                if (isset($newfolders) && ($newfolderscount = count($newfolders)) && 0 < $newfolderscount) {
                    $query = sql_query("SELECT boxnumber FROM pmboxes WHERE $userid = '" . $userid . "'");
                    $usedboxes = mysqli_num_rows($query);
                    if ($maxboxs < $usedboxes || $maxboxs < $usedboxes + $newfolderscount) {
                        $_errors[] = sprintf($lang->messages["newtitle32"], $maxboxs, $usedboxes);
                    } else {
                        $query = sql_query("SELECT MAX(boxnumber) as lastboxnumber FROM pmboxes WHERE $userid = '" . $userid . "'");
                        $Result = mysqli_fetch_assoc($query);
                        $lastboxnumber = $Result["lastboxnumber"];
                        if (!$lastboxnumber || $lastboxnumber < 2 || $lastboxnumber == 0 || !is_valid_id($lastboxnumber)) {
                            $lastboxnumber = 1;
                        }
                        for ($i = 0; $i < $newfolderscount; $i++) {
                            $lastboxnumber++;
                            sql_query("INSERT INTO pmboxes (userid,boxnumber,name) VALUES (" . $userid . "," . $lastboxnumber . "," . sqlesc(trim($newfolders[$i])) . ")");
                        }
                    }
                }
            }
            $standartfolders = "\r\n\t<FIELDSET>\r\n\t\t<legend>" . $lang->messages["newtitle21"] . "</legend>\r\n\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailboxes["INBOX"] . "\">" . $lang->messages["inbox"] . "</a><br />\r\n\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailboxes["SENDBOX"] . "\">" . $lang->messages["sendbox"] . "</a>\r\n\t</FIELDSET>\r\n\t";
            $customfolderscount = 0;
            $customfolders = "";
            $query = sql_query("SELECT id,name,boxnumber FROM pmboxes WHERE $userid = '" . $userid . "' ORDER by boxnumber LIMIT 0, " . $maxboxs);
            if (0 < mysqli_num_rows($query)) {
                $customfolders = "\r\n\t\t<FIELDSET>\r\n\t\t\t\t<legend>" . $lang->messages["newtitle22"] . "</legend>";
                while ($cf = mysqli_fetch_assoc($query)) {
                    $customfolderscount++;
                    $customfolders .= "\r\n\t\t\t\t<p>" . $lang->messages["newtitle24"] . "<br /><input $type = \"text\" $size = \"30\" $name = \"customfolders[" . $cf["boxnumber"] . "]\" $value = \"" . htmlspecialchars_uni($cf["name"]) . "\" $maxlength = \"14\">";
                }
                $customfolders .= "\r\n\t\t<br />\r\n\t\t" . $lang->messages["newtitle25"] . "\r\n\t\t</FIELDSET>";
            }
            $shownewfolders = "";
            if ($customfolderscount < $maxboxs) {
                $shownewfolders = "\r\n\t\t<FIELDSET>\r\n\t\t\t<legend>" . $lang->messages["newtitle23"] . "</legend>";
                for ($i = 0; $i < $maxboxs && $i < 3; $i++) {
                    $shownewfolders .= "<p>" . $lang->messages["newtitle24"] . "<br /><input $type = \"text\" $size = \"30\" $name = \"newfolder" . $i . "\" $value = \"\" $maxlength = \"14\"></p>";
                }
                $shownewfolders .= "\r\n\t\t\t" . $lang->messages["newtitle25"] . "\r\n\t\t</FIELDSET>\r\n\t\t";
            }
            stdhead($lang->messages["newtitle19"]);
            show_message_errors_();
            echo "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailbox . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\" $name = \"messageform\">\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"editfolders\">\r\n\t<input $type = \"hidden\" $name = \"update\" $value = \"yes\">\r\n\r\n\t<table $align = \"center\" $border = \"0\" $cellpadding = \"6\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $align = \"left\">" . $lang->messages["newtitle19"] . "\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>" . $standartfolders . " " . $customfolders . " " . $shownewfolders . "</td>\r\n\t\t</tr>\r\n\t\t<tr><td $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->messages["newtitle20"] . "\"> <input $type = \"button\" $value = \"" . $lang->messages["newtitle17"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailbox . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "')\"></td></tr>\r\n\t</table>\r\n\t</form>\r\n\t";
            stdfoot();
            exit;
        } else {
            if ($do == "move") {
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["destination"])) {
                    $newfolder = intval($_POST["destination"]);
                    $query = sql_query("SELECT name FROM pmboxes WHERE $userid = '" . $userid . "' AND $boxnumber = '" . $newfolder . "'");
                    if ((mysqli_num_rows($query) == 0 || empty($newfolder)) && $newfolder != $mailboxes["INBOX"]) {
                        $_errors[] = sprintf($lang->messages["newtitle14"], $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;do=editfolders&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval($_GET["page"]));
                    } else {
                        sql_query("UPDATE messages SET $location = " . $newfolder . " WHERE id IN (0," . implode(",", $pmids) . ") AND $receiver = '" . $userid . "'");
                        $mailbox = $newfolder;
                    }
                } else {
                    $query = sql_query("SELECT boxnumber, name FROM pmboxes WHERE $userid = '" . $userid . "' ORDER by boxnumber LIMIT 0, " . $maxboxs);
                    if (mysqli_num_rows($query) == 0) {
                        $_errors[] = sprintf($lang->messages["newtitle14"], $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;do=editfolders&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval($_GET["page"]));
                    } else {
                        $showbox = "<select $name = \"destination\">";
                        if ($mailbox == $mailboxes["INBOX"]) {
                            while ($userboxes = mysqli_fetch_assoc($query)) {
                                $showbox .= "<option $value = \"" . intval($userboxes["boxnumber"]) . "\">" . htmlspecialchars_uni($userboxes["name"]) . "</option>";
                            }
                        } else {
                            $showbox .= "<option $value = \"" . intval($mailboxes["INBOX"]) . "\">" . $lang->messages["inbox"] . "</option>";
                            while ($userboxes = mysqli_fetch_assoc($query)) {
                                if ($userboxes["boxnumber"] != $mailbox) {
                                    $showbox .= "<option $value = \"" . intval($userboxes["boxnumber"]) . "\">" . htmlspecialchars_uni($userboxes["name"]) . "</option>";
                                }
                            }
                        }
                        $showbox .= "</select>";
                        $hiddenvalues = "";
                        foreach ($pmids as $pmid) {
                            $hiddenvalues .= "<input $type = \"hidden\" $name = \"pmid[]\" $value = \"" . intval($pmid) . "\">";
                        }
                        stdhead($lang->messages["newtitle18"]);
                        echo "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval($_GET["page"]) . "\" $name = \"messageform\">\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"move\">\r\n\t\t\t" . $hiddenvalues . "\r\n\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"6\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\" $align = \"left\">" . $lang->messages["newtitle18"] . "\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>" . $lang->messages["newtitle15"] . "<br />" . $lang->messages["newtitle10"] . "<br />" . $showbox . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr><td $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->messages["newtitle16"] . "\"> <input $type = \"button\" $value = \"" . $lang->messages["newtitle17"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval($_GET["page"]) . "')\"></td></tr>\r\n\t\t\t\t</table>\r\n\t\t\t\t</form>\r\n\t\t\t";
                        stdfoot();
                        exit;
                    }
                }
            }
            if ($do == "delete") {
                $query = sql_query("SELECT unread FROM messages WHERE id IN (0," . implode(",", $pmids) . ") AND $unread = 'yes'");
                if (mysqli_num_rows($query) == 0) {
                    delete_pms($pmids);
                } else {
                    $_errors[] = $lang->messages["newtitle30"];
                }
            }
            if ($do == "markasread") {
                sql_query("UPDATE messages SET $unread = 'no' WHERE $receiver = '" . $userid . "' AND id IN (0," . implode(",", $pmids) . ")");
            }
            if ($do == "markasunread") {
                sql_query("UPDATE messages SET $unread = 'yes' WHERE $receiver = '" . $userid . "' AND id IN (0," . implode(",", $pmids) . ")");
            }
            if ($do == "emptybox" && isset($_GET["sure"]) && $_GET["sure"] == "yes") {
                if ($mailbox == 1) {
                    sql_query("DELETE FROM messages WHERE $receiver = '" . $userid . "' AND $location = '" . $mailbox . "' AND $unread = 'no'") or sql_query("DELETE FROM messages WHERE $receiver = '" . $userid . "' AND $location = '" . $mailbox . "' AND $unread = 'no'") || sqlerr(__FILE__, 332);
                } else {
                    if ($mailbox == -1) {
                        sql_query("DELETE FROM messages WHERE $sender = '" . $userid . "' AND $saved = 'yes' AND $unread = 'no'") or sql_query("DELETE FROM messages WHERE $sender = '" . $userid . "' AND $saved = 'yes' AND $unread = 'no'") || sqlerr(__FILE__, 336);
                    } else {
                        if (is_valid_id($mailbox)) {
                            sql_query("DELETE FROM messages WHERE $receiver = '" . $userid . "' AND $location = '" . $mailbox . "' AND $unread = 'no'") || sqlerr(__FILE__, 340);
                        }
                    }
                }
            }
            if ($do == "download") {
                function TS_Strip_Tags($Text)
                {
                    $Text = format_comment($Text);
                    $Text = strip_tags($Text);
                    $Text = str_replace(["&amp;", "<br />", "&lt;", "&gt;"], ["&", "\\n", "<", ">"], $Text);
                    return $Text;
                }
                if ($mailbox == 1) {
                    $query = sql_query("SELECT m.subject, m.msg, m.added, u.username as ruser, uu.username as suser FROM messages m LEFT JOIN users u ON (m.$receiver = u.id) LEFT JOIN users uu ON (m.$sender = uu.id) WHERE m.$receiver = '" . $userid . "' AND m.$location = '" . $mailbox . "' AND m.$unread = 'no' ORDER BY m.added DESC") or ($query = sql_query("SELECT m.subject, m.msg, m.added, u.username as ruser, uu.username as suser FROM messages m LEFT JOIN users u ON (m.$receiver = u.id) LEFT JOIN users uu ON (m.$sender = uu.id) WHERE m.$receiver = '" . $userid . "' AND m.$location = '" . $mailbox . "' AND m.$unread = 'no' ORDER BY m.added DESC")) || sqlerr(__FILE__, 356);
                } else {
                    if ($mailbox == -1) {
                        $query = sql_query("SELECT m.subject, m.msg, m.added, u.username as ruser, uu.username as suser FROM messages m LEFT JOIN users u ON (m.$receiver = u.id) LEFT JOIN users uu ON (m.$sender = uu.id) WHERE m.$sender = '" . $userid . "' AND m.$saved = 'yes' AND m.$unread = 'no' ORDER BY m.added DESC") or ($query = sql_query("SELECT m.subject, m.msg, m.added, u.username as ruser, uu.username as suser FROM messages m LEFT JOIN users u ON (m.$receiver = u.id) LEFT JOIN users uu ON (m.$sender = uu.id) WHERE m.$sender = '" . $userid . "' AND m.$saved = 'yes' AND m.$unread = 'no' ORDER BY m.added DESC")) || sqlerr(__FILE__, 360);
                    } else {
                        if (is_valid_id($mailbox)) {
                            ($query = sql_query("SELECT m.subject, m.msg, m.added, u.username as ruser, uu.username as suser FROM messages m LEFT JOIN users u ON (m.$receiver = u.id) LEFT JOIN users uu ON (m.$sender = uu.id) WHERE m.$receiver = '" . $userid . "' AND m.$location = '" . $mailbox . "' AND m.$unread = 'no' ORDER BY m.added DESC")) || sqlerr(__FILE__, 364);
                        }
                    }
                }
                $Outbox = [];
                $Outbox[] = "\r\n" . $SITENAME . " - " . $BASEURL . "\r\n" . $lang->messages["dumptext"] . ": " . $CURUSER["username"] . "; " . my_datee($dateformat, TIMENOW, "", false) . " - " . my_datee($timeformat, TIMENOW, "", false) . " -->\r\n\r\n################################################################################\r\n" . $lang->messages["newtitle24"] . " " . $foldername . "\r\n################################################################################\r\n";
                while ($Msg = mysqli_fetch_assoc($query)) {
                    $Outbox[] = "\r\n" . $lang->messages["from"] . ": " . ($Msg["suser"] ? $Msg["suser"] : "System") . "\r\n" . $lang->messages["to"] . ": " . $Msg["ruser"] . "\r\n" . $lang->messages["date"] . ": " . my_datee($dateformat, $Msg["added"], "", false) . " - " . my_datee($timeformat, $Msg["added"], "", false) . "\r\n" . $lang->messages["subject"] . ": " . htmlspecialchars($Msg["subject"]) . "\r\n--------------------------------------------------------------------------------\r\n" . TS_Strip_Tags($Msg["msg"]);
                }
                $filename = "privatemessages-" . $CURUSER["username"] . "-" . date("d-m-Y") . ".txt";
                $contents = implode("\r\$n = ===============================================================================\r\n", $Outbox);
                require_once INC_PATH . "/functions_browser.php";
                if (is_browser("ie")) {
                    header("Pragma: public");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-$check = 0, pre-$check = 0");
                    header("Content-Disposition: attachment; $filename = " . basename($filename) . ";");
                    header("Content-Transfer-Encoding: binary");
                } else {
                    header("Expires: Tue, 1 Jan 1980 00:00:00 GMT");
                    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                    header("Cache-Control: no-store, no-cache, must-revalidate");
                    header("Cache-Control: post-$check = 0, pre-$check = 0", false);
                    header("Pragma: no-cache");
                    header("X-Powered-By: " . VERSION . " (c) " . date("Y") . " " . $SITENAME . "");
                    header("Accept-Ranges: bytes");
                    header("Connection: close");
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Type: text/plain");
                    header("Content-Disposition: attachment; $filename = " . basename($filename) . ";");
                }
                ob_implicit_flush(true);
                echo $contents;
                exit;
            }
            if ($do == "showpm") {
                $quickmenu = "";
                include_once INC_PATH . "/functions_icons.php";
                $pmid = intval($_GET["pmid"]);
                if (empty($pmid)) {
                    $_errors[] = $lang->messages["newtitle11"];
                }
                $res = sql_query("SELECT * FROM messages WHERE $id = " . sqlesc($pmid) . " AND ($receiver = " . sqlesc($userid) . " OR ($sender = " . sqlesc($userid) . " AND $saved = 'yes')) LIMIT 1");
                if (mysqli_num_rows($res) == 0) {
                    $_errors[] = $lang->messages["newtitle11"];
                }
                if (count($_errors) == 0) {
                    $message = mysqli_fetch_assoc($res);
                    $subject = htmlspecialchars_uni($message["subject"]);
                    $text = format_comment($message["msg"]);
                    $reply = "";
                    if ($message["sender"] == $CURUSER["id"]) {
                        ($res2 = sql_query("SELECT u.*, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle, g.title FROM users u LEFT JOIN ts_u_perm p ON (u.$id = p.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = " . sqlesc($message["receiver"]))) || sqlerr(__FILE__, 437);
                        $sender = $arraysender = mysqli_fetch_assoc($res2);
                        $username = $sender["username"];
                        $joindate = $sender["added"];
                        $lastseen = $sender["last_access"];
                        if (TS_Match($sender["options"], "B1") && !$moderator) {
                            $lastseen = $sender["last_login"];
                        }
                        $susergroup = $sender["usergroup"];
                        $stitle = $sender["title"];
                        $sender = "<a $href = \"javascript:void(0);\" $id = \"quickmenu" . $pmid . "\">" . get_user_color($username, $sender["namestyle"]) . " " . get_user_icons($sender) . " </a>";
                        $sender2 = $message["receiver"];
                    } else {
                        if ($message["sender"] == 0) {
                            $sender = $lang->messages["system"];
                        } else {
                            ($res2 = sql_query("SELECT u.*, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle, g.title, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN ts_u_perm p ON (u.$id = p.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = " . sqlesc($message["sender"]))) || sqlerr(__FILE__, 459);
                            $sender = $arraysender = mysqli_fetch_assoc($res2);
                            $username = $sender["username"];
                            $joindate = $sender["added"];
                            $lastseen = $sender["last_access"];
                            if (TS_Match($sender["options"], "B1") && !$moderator) {
                                $lastseen = $sender["last_login"];
                            }
                            $susergroup = $sender["usergroup"];
                            $stitle = $sender["title"];
                            $sender = "<a $href = \"javascript:void(0);\" $id = \"quickmenu" . $pmid . "\">" . get_user_color($username, $sender["namestyle"]) . " " . get_user_icons($sender) . " </a>";
                            $sender2 = $message["sender"];
                            $replylink = "\r\n\t\t\t\t<input $type = \"button\" $value = \"" . $lang->messages["reply"] . "\" $onclick = \"jumpto('" . $BASEURL . "/sendmessage.php?$receiver = " . $message["sender"] . "&amp;$replyto = " . $pmid . "')\">";
                        }
                    }
                    require INC_PATH . "/function_user_rank.php";
                    $image = $message["sender"] != 0 ? user_rank($arraysender) : "";
                    $forwardlink = "\r\n\t\t\t\t<input $type = \"button\" $value = \"" . $lang->messages["forwardpm"] . "\" $onclick = \"jumpto('" . $BASEURL . "/sendmessage.php?$receiver = " . $message["sender"] . "&amp;$replyto = " . $pmid . "&amp;$type = forward')\">";
                    $deletelink = "\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->messages["newtitle4"] . "\" $onclick = \"return confirm_delete()\">\r\n\t\t\t\t";
                    $returnlink = "\r\n\t\t\t\t<input $type = \"button\" $value = \"" . $lang->messages["newtitle31"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval($_GET["page"]) . "')\">\r\n\t\t\t\t";
                    if ($message["sender"] != 0) {
                        $quickmenu .= "\r\n\t\t\t<div $id = \"quickmenu" . $pmid . "_menu\" class=\"menu_popup\" $style = \"display:none;\">\r\n\t\t\t\t<table $border = \"1\" $cellspacing = \"0\" $cellpadding = \"2\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $align = \"center\" class=\"thead\"><b>" . $lang->global["quickmenu"] . " " . (isset($username) ? $username : "") . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . ts_seo(isset($sender2) ? $sender2 : "", $username) . "\">" . $lang->global["qinfo1"] . "</a></td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/sendmessage.php?$receiver = " . (isset($sender2) ? $sender2 : "") . "\">" . sprintf($lang->global["qinfo2"], $username) . "</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/friends.php?$action = add_friend&$friendid = " . (isset($sender2) ? $sender2 : "") . "\">" . sprintf($lang->global["qinfo5"], $username) . "</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . ($moderator ? "<tr><td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=edit_user&amp;$username = " . $username . "\">" . $lang->global["qinfo6"] . "</a></td></tr><tr><td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=warn_user&amp;$username = " . $username . "\">" . $lang->global["qinfo7"] . "</td></tr>" : "") . "\r\n\t\t\t\t</table>\r\n\t\t\t\t</div>";
                    }
                    if ($mailbox != $mailboxes["SENDBOX"]) {
                        sql_query("UPDATE messages SET $unread = 'no' WHERE $id = " . sqlesc($pmid)) || sqlerr(__FILE__, 516);
                        if ($message["unread"] == "yes") {
                            sql_query("UPDATE users SET $pmunread = IF(pmunread > 0, pmunread - 1, 0) WHERE $id = '" . $userid . "'") || sqlerr(__FILE__, 519);
                        }
                    }
                    $verified = "";
                    if (isset($arraysender) && is_mod($arraysender) || !isset($arraysender) || $arraysender["id"] == "0") {
                        $verified = show_notice("\r\n\t\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\" $style = \"border-bottom-width:0px\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $width = \"30\" class=\"none\"><img $src = \"" . $pic_base_url . "verified.gif\" $width = \"27\" $height = \"32\" $alt = \"Verified!\" /></td>\r\n\t\t\t\t\t<td class=\"none\"><strong><font $size = \"2\" $color = \"#339900\">" . $lang->messages["verified1"] . "</font></strong><br /><font $size = \"1\">" . sprintf($lang->messages["verified2"], $SITENAME) . "</font></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>");
                    }
                    stdhead(strip_tags(sprintf($lang->messages["newtitle12"], $subject)));
                    echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\tfunction confirm_delete()\r\n\t\t{\r\n\t\t\tvar $deletepm = confirm(\"" . $lang->messages["newtitle13"] . "\")\r\n\t\t\tif (deletepm)\r\n\t\t\t{\r\n\t\t\t\treturn true;\r\n\t\t\t}\r\n\t\t\treturn false;\r\n\t\t}\r\n\t\t</script>\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval($_GET["page"]) . "\" $name = \"messageform\">\r\n\t\t<input $type = \"hidden\" $name = \"do\" $value = \"delete\">\r\n\t\t<input $type = \"hidden\" $name = \"pmid[]\" $value = \"" . $pmid . "\">\r\n\t\t" . $verified . "\r\n\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"6\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\" $align = \"left\">" . sprintf($lang->messages["newtitle12"], $subject) . "\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"left\">" . my_datee($dateformat, $message["added"]) . " " . my_datee($timeformat, $message["added"]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"left\">\r\n\t\t\t\t\t<div>" . ($message["sender"] != 0 ? "\r\n\t\t\t\t\t<span $style = \"float: right;\" class=\"smallfont\">" . sprintf($lang->messages["qinfo8"], my_datee($dateformat, $joindate)) . "</span>" : "") . "\r\n\t\t\t\t\t" . $sender . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div class=\"smalltext\">" . ($message["sender"] != 0 ? "\r\n\t\t\t\t\t<span $style = \"float: right;\" class=\"smallfont\">" . sprintf($lang->messages["qinfo9"], my_datee($dateformat, $lastseen), my_datee($timeformat, $lastseen)) . "</span>" : "") . "\r\n\t\t\t\t\t" . $image . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t" . ($message["sender"] != 0 ? "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\tmenu_register(\"quickmenu" . $pmid . "\");\r\n\t\t\t\t</script>" : "") . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"left\">\r\n\t\t\t\t<strong>" . $subject . "</strong>\r\n\t\t\t\t<hr $size = \"1\">\r\n\t\t\t\t" . $text . "\r\n\t\t\t\t<br />\r\n\t\t\t\t<div>\r\n\t\t\t\t\t<span $style = \"float: right;\">" . (isset($returnlink) ? $returnlink : "") . " " . (isset($deletelink) ? $deletelink : "") . " " . (isset($forwardlink) ? $forwardlink : "") . " " . (isset($replylink) ? $replylink : "") . "</span>\r\n\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t" . $quickmenu . ($message["sender"] != 0 ? "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tmenu.activate(true);\r\n\t\t</script>" : "");
                    stdfoot();
                    exit;
                }
            }
            $WhereQuery = "";
            $Link = "";
            if ($do == "search") {
                $keywords = isset($_POST["keywords"]) ? trim($_POST["keywords"]) : (isset($_GET["keywords"]) ? trim($_GET["keywords"]) : "");
                $searchtype = isset($_POST["searchtype"]) ? trim($_POST["searchtype"]) : (isset($_GET["searchtype"]) ? trim($_GET["searchtype"]) : "");
                $from = isset($_POST["from"]) ? trim($_POST["from"]) : (isset($_GET["from"]) ? trim($_GET["from"]) : "");
                $to = isset($_POST["to"]) ? trim($_POST["to"]) : (isset($_GET["to"]) ? trim($_GET["to"]) : "");
                if ($keywords) {
                    $WhereQuery .= " AND (MATCH (m." . ($searchtype == "msg" ? "msg" : "subject") . ") AGAINST ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "*' IN BOOLEAN MODE))";
                    $Link .= "searchtype=" . ($searchtype == "msg" ? "msg" : "subject") . "amp;$keywords = " . htmlspecialchars_uni($keywords) . "&amp;";
                }
                if ($from && $to && strtotime($from) && strtotime($to)) {
                    $WhereQuery .= " AND (UNIX_TIMESTAMP(m.added) > " . strtotime($from) . " AND UNIX_TIMESTAMP(m.added) < " . strtotime($to) . ")";
                    $Link .= "from=" . htmlspecialchars_uni($from) . "&amp;$to = " . htmlspecialchars_uni($to) . "&amp;";
                }
                if ($Link) {
                    $Link .= "do=search&amp;";
                }
            }
            $eq = $mailbox != $mailboxes["SENDBOX"] ? ["m.sender", "m.receiver", ""] : ["m.receiver ", "m.sender", " AND m.$saved = 'yes'"];
            $count = mysqli_num_rows(sql_query("SELECT m.* FROM messages m WHERE " . $eq[1] . "=" . $userid . " " . ($folderid ? "AND m.$location = " . $folderid : "") . $eq[2] . $WhereQuery));
            list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $count, $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailbox . "&amp;" . $Link);
            include_once INC_PATH . "/functions_icons.php";
            if (!$fromAjax) {
                stdhead(strip_tags(sprintf($lang->messages["newtitle1"], $foldername)), true, "<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/prototype.js?$v = " . O_SCRIPT_VERSION . "\"></script>");
                echo pm_limit();
                show_message_errors_();
                $DisplayOptions = "\r\n\t<script $type = \"text/javascript\">\r\n\t\tjQuery(function()\r\n\t\t{\r\n\t\t\tjQuery(\"#from,#to\").datepicker({dateFormat: \"yy-mm-dd\", changeMonth: true, changeYear: true, closeText: \"X\", showButtonPanel: true});\r\n\t\t});\r\n\t</script>\r\n\t<br />\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=search&amp;$userid = " . $userid . "&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0) . "\" $name = \"displayoptions\">\r\n\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . $lang->messages["doptions"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t" . $lang->messages["field1"] . "\r\n\t\t\t\t<input $type = \"text\" $name = \"keywords\" $value = \"" . (isset($keywords) ? htmlspecialchars_uni($keywords) : "") . "\" />\r\n\t\t\t\t" . $lang->messages["field5"] . "\r\n\t\t\t\t<select $name = \"searchtype\">\r\n\t\t\t\t\t<option $value = \"subject\"" . (isset($searchtype) && $searchtype == "subject" ? " $selected = \"selected\"" : "") . ">" . $lang->messages["field3"] . "</option>\r\n\t\t\t\t\t<option $value = \"msg\"" . (isset($searchtype) && $searchtype == "msg" ? " $selected = \"selected\"" : "") . ">" . $lang->messages["field4"] . "</option>\r\n\t\t\t\t</select>\r\n\t\t\t\t " . $lang->messages["from"] . " <input $type = \"text\" $name = \"from\" $id = \"from\" $value = \"" . (isset($from) ? htmlspecialchars_uni($from) : "") . "\" />\r\n\t\t\t\t " . $lang->messages["to"] . " <input $type = \"text\" $name = \"to\" $id = \"to\" $value = \"" . (isset($to) ? htmlspecialchars_uni($to) : "") . "\" />\r\n\t\t\t\t <input $type = \"submit\" $value = \"" . $lang->global["buttonsearch"] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
                echo $pagertop . "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0) . "\" $name = \"messageform\">\r\n\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"left\" class=\"thead\">" . ts_collapse("messages") . "\r\n\t\t\t" . sprintf($lang->messages["newtitle1"], "<a $href = \"javascript:void(0);\" $id = \"quickmenu1\" />" . $foldername . "</a>") . "</td>\r\n\t\t\t<td class=\"thead\" $align = \"center\" $width = \"1%\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('messageform', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t\t" . ts_collapse("messages", 2) . "\r\n\t";
            }
            $str = "";
            ($query = sql_query("SELECT m.*, u.username, u.id as senderid, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle FROM messages m LEFT JOIN users u ON (u.$id = " . $eq[0] . ") LEFT JOIN ts_u_perm p ON (u.$id = p.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE " . $eq[1] . "=" . $userid . " " . ($folderid ? "AND m.$location = " . $folderid : "") . $eq[2] . $WhereQuery . " ORDER by m.added DESC " . $limit)) || sqlerr(__FILE__, 682);
            if (0 < mysqli_num_rows($query)) {
                if ($mailbox != $mailboxes["SENDBOX"]) {
                    ($QueryF = sql_query("SELECT m.id FROM messages m WHERE m.$unread = 'yes' AND " . $eq[1] . "=" . $userid . " " . ($folderid ? "AND m.$location = " . $folderid : "") . (string) $eq[2])) || sqlerr(__FILE__, 687);
                    sql_query("UPDATE users SET $pmunread = '" . mysqli_num_rows($QueryF) . "' WHERE $id = '" . $userid . "'") || sqlerr(__FILE__, 688);
                }
                $defaulttheme = ts_template();
                while ($message = mysqli_fetch_assoc($query)) {
                    $sender = $message["sender"] == 0 ? "<b>System<b>" : "<a $href = \"" . ts_seo($message["senderid"], $message["username"]) . "\">" . get_user_color($message["username"], $message["namestyle"]) . "</a> " . get_user_icons($message);
                    $msgimg = $message["unread"] == "yes" ? "unreadpm" : "readpm";
                    $imagetitle = $message["unread"] == "yes" && $mailbox != $mailboxes["SENDBOX"] ? "" : ($message["unread"] == "yes" && $mailbox == $mailboxes["SENDBOX"] ? $lang->messages["newtitle29"] : "");
                    $msgtxtpreview = htmlspecialchars(mysqli_real_escape_string($GLOBALS["DatabaseConnect"], substr($message["msg"], 0, 100))) . " ...";
                    $message["subject"] = $message["unread"] == "yes" ? "<strong>" . htmlspecialchars_uni($message["subject"]) . "</strong>" : htmlspecialchars_uni($message["subject"]);
                    $str .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"1%\"><img $src = \"" . $BASEURL . "/include/templates/" . $defaulttheme . "/images/" . $msgimg . ".gif\" $border = \"0\" $alt = \"" . $imagetitle . "\" $title = \"" . $imagetitle . "\" /></td>\r\n\t\t\t<td>\r\n\t\t\t\t<div>\r\n\t\t\t\t\t<span $style = \"float: right;\" class=\"smallfont\">" . my_datee($dateformat, $message["added"]) . "</span>\r\n\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;do=showpm&amp;$pmid = " . $message["id"] . "&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0) . "&amp;$mailbox = " . $mailbox . "\"" . ($fromAjax ? "" : " $onmouseover = \"ddrivetip('" . $msgtxtpreview . "', 600)\"; $onmouseout = \"hideddrivetip()\"") . ">" . $message["subject"] . "</a>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div class=\"smalltext\">\r\n\t\t\t\t\t<span $style = \"float: right;\" class=\"time\">" . my_datee($timeformat, $message["added"]) . "</span>\r\n\t\t\t\t\t" . $sender . "\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t\t" . ($fromAjax ? "" : "\r\n\t\t\t<td $width = \"1%\" $style = \"padding: 0px;\" $align = \"center\">\r\n\t\t\t\t<input $name = \"pmid[]\" $value = \"" . $message["id"] . "\" $type = \"checkbox\" $checkme = \"group\">\r\n\t\t\t</td>") . "\r\n\t\t</tr>";
                }
            } else {
                if ($fromAjax) {
                    echo "<div class=\"error\">" . sprintf($lang->messages["newtitle8"], $foldername, 0) . "</div>";
                    exit;
                }
                $str .= "<tr><td $colspan = \"3\" $align = \"left\">" . sprintf($lang->messages["newtitle8"], $foldername, 0) . "</td></tr>";
            }
            if ($fromAjax) {
                echo "<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">";
                echo $str;
                echo "</table>";
                exit;
            }
            $str .= "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction confirm_emptyfolder()\r\n\t\t\t{\r\n\t\t\t\tvar $emptyfolder = confirm(\"" . $lang->messages["newtitle34"] . "\");\r\n\t\t\t\tif (emptyfolder)\r\n\t\t\t\t{\r\n\t\t\t\t\tjumpto('" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&do=emptybox&$mailbox = " . $mailbox . "&$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0) . "&$sure = yes');\r\n\t\t\t\t}\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"3\" class=\"subheader\">\r\n\t\t\t<div>\r\n\t\t\t<span $style = \"float:right;\">\r\n\t\t\t\t" . $lang->messages["newtitle2"] . " " . action_box() . "\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->messages["newtitle7"] . "\"></span>\r\n\t\t\t\t<input $type = \"button\" $value = \"" . $lang->messages["newtitle28"] . "\" $onclick = \"jumpto('" . $BASEURL . "/sendmessage.php?$action = compose')\">\r\n\t\t\t\t<input $type = \"button\" $value = \"" . $lang->messages["newtitle27"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;do=editfolders&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0) . "')\">\r\n\t\t\t\t<input $type = \"button\" $value = \"" . $lang->messages["newtitle33"] . "\" $onclick = \"confirm_emptyfolder()\"> <input $type = \"button\" $value = \"" . $lang->messages["download"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;do=download&amp;$mailbox = " . $mailbox . "&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0) . "')\">\r\n\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t<script $type = \"text/javascript\">\r\n\t\tmenu_register(\"quickmenu1\");\r\n\t</script>\r\n\t<div $id = \"quickmenu1_menu\" class=\"menu_popup\" $style = \"display:none;\">\r\n\t\t<table $border = \"1\" $cellspacing = \"0\" $cellpadding = \"2\">\r\n\t\t<tr><td class=\"thead\">" . $lang->messages["newtitle10"] . "</td></tr>\r\n\t\t" . get_pmboxes() . "\r\n\t\t</table>\r\n\t</div>\r\n\t<script $type = \"text/javascript\">\r\n\t\tmenu.activate(true);\r\n\t</script>";
            echo $str;
            echo $pagerbottom;
            echo $DisplayOptions;
            stdfoot();
        }
}

?>