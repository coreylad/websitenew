<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_social_groups.php");
define("SG_VERSION", "1.0.3 by xam");
require "./global.php";
if (!SGPermission("canview")) {
    print_no_permission();
}
$errors = [];
$action = isset($_GET["do"]) ? trim($_GET["do"]) : (isset($_POST["do"]) ? trim($_POST["do"]) : "");
$groupId = isset($_GET["groupid"]) ? intval($_GET["groupid"]) : (isset($_POST["groupid"]) ? intval($_POST["groupid"]) : 0);
$lang->load("ts_social_groups");
$defaulttemplate = ts_template();
$dimagedir = $BASEURL . "/include/templates/" . $defaulttemplate . "/images/";
$pageHtml = "\r\n<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/prototype.js?V=" . O_SCRIPT_VERSION . "\"></script>";
if ($action == "masspm" && is_valid_id($groupId)) {
    ($groupQuery = sql_query("SELECT name, owner FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId))) || sqlerr(__FILE__, 37);
    if (0 < mysqli_num_rows($groupQuery)) {
        $groupResult = mysqli_fetch_assoc($groupQuery);
        $groupOwnerId = $groupResult["owner"];
        $groupName = $groupResult["name"];
        if ($groupOwnerId != $CURUSER["id"] && !$is_mod || !SGPermission("canmanagegroup")) {
            print_no_permission(true);
        } else {
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                $messageSubject = trim($_POST["subject"]);
                $messageBody = trim($_POST["message"]);
                if (strlen($messageSubject) < 2 || strlen($messageBody) < 10) {
                    $errors[] = $lang->ts_social_groups["tooshort"];
                } else {
                    ($membersQuery = sql_query("SELECT userid FROM ts_social_group_members WHERE userid != " . sqlesc($CURUSER["id"]) . " AND $groupid = " . sqlesc($groupId))) || sqlerr(__FILE__, 60);
                    if (mysqli_num_rows($membersQuery) < 1) {
                        $errors[] = $lang->ts_social_groups["nomembers"];
                    } else {
                        $messageCount = 0;
                        require_once INC_PATH . "/functions_pm.php";
                        $messageBody = $messageBody . sprintf($lang->ts_social_groups["addmsg"], htmlspecialchars_uni($groupName));
                        while ($groupMember = mysqli_fetch_row($membersQuery)) {
                            $messageCount++;
                            send_pm($groupMember[0], $messageBody, $messageSubject);
                        }
                        stdhead($lang->ts_social_groups["masspm"]);
                        add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
                        add_breadcrumb(htmlspecialchars_uni($groupName), $_SERVER["SCRIPT_NAME"] . "?do=showgroup&$groupid = " . $groupId);
                        add_breadcrumb($lang->ts_social_groups["masspm"]);
                        build_breadcrumb();
                        stdmsg($lang->global["msgsend"], sprintf($lang->ts_social_groups["masspmdone"], $messageCount, $groupId), false);
                        stdfoot();
                        exit;
                    }
                }
            }
            stdhead($lang->ts_social_groups["masspm"]);
            add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb(htmlspecialchars_uni($groupName), $_SERVER["SCRIPT_NAME"] . "?do=showgroup&$groupid = " . $groupId);
            add_breadcrumb($lang->ts_social_groups["masspm"]);
            build_breadcrumb();
            show_sg_errors();
            $pageHtml .= "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=masspm&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"masspm\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"groupid\" $value = \"" . intval($groupId) . "\" />\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ts_collapse("masspm") . "\r\n\t\t\t\t\t\t" . sprintf($lang->ts_social_groups["masspm2"], htmlspecialchars_uni($groupName)) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("masspm", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["masspm"] . "</legend>\r\n\t\t\t\t\t\t\t\t<div><b>" . $lang->ts_social_groups["subject"] . ":</b></div>\r\n\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"subject\" $size = \"50\" $value = \"" . (isset($messageSubject) ? htmlspecialchars_uni($messageSubject) : "") . "\" />\r\n\t\t\t\t\t\t\t\t<div $style = \"padding-top:5px;\"><b>" . $lang->ts_social_groups["message"] . ":</b></div>\r\n\t\t\t\t\t\t\t\t<textarea $name = \"message\" $rows = \"6\" $cols = \"85\">" . (isset($messageBody) ? htmlspecialchars_uni($messageBody) : "") . "</textarea><br />\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_social_groups["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_social_groups["reset"] . "\" />\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t";
            $pageHtml .= "\r\n\t\t\t</table>\r\n\t\t\t</form>";
            echo $pageHtml;
            stdfoot();
            exit;
        }
    } else {
        $errors[] = $lang->ts_social_groups["invalid"];
    }
}
if ($action == "manage" && is_valid_id($groupId)) {
    ($groupQuery = sql_query("SELECT name, owner FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId))) || sqlerr(__FILE__, 134);
    if (0 < mysqli_num_rows($groupQuery)) {
        $groupResult = mysqli_fetch_assoc($groupQuery);
        $groupOwnerId = $groupResult["owner"];
        $groupName = $groupResult["name"];
        if ($groupOwnerId != $CURUSER["id"] && !$is_mod || !SGPermission("canmanagegroup")) {
            print_no_permission(true);
        } else {
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                if (!empty($_POST["username"]) && !empty($_POST["reason"])) {
                    $targetUsername = trim($_POST["username"]);
                    $removalReason = trim($_POST["reason"]);
                    ($userQuery = sql_query("SELECT u.id, m.userid FROM users u LEFT JOIN ts_social_group_members m ON (u.$id = m.userid) WHERE m.$userid = u.id AND m.$groupid = " . sqlesc($groupId) . " AND m.$type = 'public' AND u.$username = " . sqlesc($targetUsername))) || sqlerr(__FILE__, 153);
                    if (0 < mysqli_num_rows($userQuery)) {
                        $userResult = mysqli_fetch_assoc($userQuery);
                        if ($userResult["id"] == $groupOwnerId) {
                            $errors[] = $lang->ts_social_groups["error14"];
                        } else {
                            ($deleteQuery = sql_query("DELETE FROM ts_social_group_members WHERE $groupid = " . sqlesc($groupId) . " AND $type = 'public' AND $userid = " . sqlesc($userResult["id"]) . ")) || sqlerr(__FILE__, 163);
                            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                sql_query("UPDATE ts_social_groups SET $members = IF(members > 0, members - 1, 0) WHERE $groupid = " . sqlesc($groupId));
                                require_once INC_PATH . "/functions_pm.php";
                                $subject = $lang->ts_social_groups["kicktitle"];
                                $message = sprintf($lang->ts_social_groups["kickmsg"], "[URL=" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "][b]" . $CURUSER["username"] . "[/b][/URL]", "[b]" . htmlspecialchars_uni($groupName) . "[/b]", $removalReason);
                                send_pm($userResult["id"], $message, $subject);
                                redirect("ts_social_groups.php?do=manage&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : ""));
                            } else {
                                $errors[] = $lang->ts_social_groups["error8"];
                            }
                        }
                    } else {
                        $errors[] = $lang->ts_social_groups["error8"];
                    }
                }
            } else {
                if (!empty($_GET["userid"]) && is_valid_id($_GET["userid"])) {
                    $targetUserId = intval($_GET["userid"]);
                    ($userQuery = sql_query("SELECT u.id, m.userid FROM users u LEFT JOIN ts_social_group_members m ON (u.$id = m.userid) WHERE m.$userid = u.id AND m.$groupid = " . sqlesc($groupId) . " AND m.$type = 'inviteonly' AND u.$id = " . sqlesc($targetUserId))) || sqlerr(__FILE__, 188);
                    if (0 < mysqli_num_rows($userQuery)) {
                        $userResult = mysqli_fetch_assoc($userQuery);
                        if ($userResult["id"] == $groupOwnerId) {
                            $errors[] = $lang->ts_social_groups["error14"];
                        } else {
                            ($deleteQuery = sql_query("DELETE FROM ts_social_group_members WHERE $groupid = " . sqlesc($groupId) . " AND $type = 'inviteonly' AND $userid = " . sqlesc($userResult["id"]) . ")) || sqlerr(__FILE__, 198);
                            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                require_once INC_PATH . "/functions_pm.php";
                                $subject = $lang->ts_social_groups["invitectitle"];
                                $msg = sprintf($lang->ts_social_groups["invitecmsg"], "[b]" . htmlspecialchars_uni($Name) . "[/b]", "[URL=" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "][b]" . $CURUSER["username"] . "[/b][/URL]");
                                send_pm($User["id"], $msg, $subject);
                                redirect("ts_social_groups.php?do=manage&amp;$groupid = " . intval($groupid) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : ""));
                            } else {
                                $errors[] = $lang->ts_social_groups["error8"];
                            }
                        }
                    } else {
                        $errors[] = $lang->ts_social_groups["error8"];
                    }
                }
            }
            $Query = sql_query("SELECT m.userid, m.type, u.username, u.avatar, g.namestyle FROM ts_social_group_members m LEFT JOIN users u ON (u.$id = m.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE m.$groupid = " . sqlesc($groupid) . " AND m.$type = 'public' ORDER by u.username ASC, m.joined DESC");
            $TotalMembers = mysqli_num_rows($Query);
            $ShowMembers = "";
            if (0 < $TotalMembers) {
                $ShowMembers = "\r\n\t\t\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t";
                for ($count = 0; $Members = mysqli_fetch_assoc($Query); $count++) {
                    if ($count % 15 == 0) {
                        $ShowMembers .= "</tr><tr>";
                    }
                    $ULink = "<a $href = \"" . ts_seo($Members["userid"], $Members["username"]) . "\">" . get_user_color($Members["username"], $Members["namestyle"]) . "</a>";
                    $UAvatar = get_user_avatar($Members["avatar"], true, "50", "50");
                    $ShowMembers .= "\r\n\t\t\t\t\t\t\t<td class=\"none\">" . $UAvatar . "<br />" . $ULink . "</td>\r\n\t\t\t\t\t";
                }
                $ShowMembers .= "\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>";
            }
            $str .= "\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ts_collapse("managemembers") . "\r\n\t\t\t\t\t\t" . $lang->ts_social_groups["title1"] . " (" . ts_nf($TotalMembers) . ")\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("managemembers", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t" . $ShowMembers . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t\t";
            $str .= "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage&amp;$groupid = " . intval($groupid) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"manage\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"groupid\" $value = \"" . intval($groupid) . "\" />\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ts_collapse("kickmember") . "\r\n\t\t\t\t\t\t" . $lang->ts_social_groups["kickm"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("kickmember", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["username"] . "</legend>\r\n\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"30\" $name = \"username\" $value = \"" . (isset($Username) ? htmlspecialchars_uni($Username) : "") . "\" />\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["kickreason"] . "</legend>\r\n\t\t\t\t\t\t\t\t<textarea $name = \"reason\" $rows = \"6\" $cols = \"85\">" . (isset($Reason) ? htmlspecialchars_uni($Reason) : "") . "</textarea><br />\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_social_groups["kickm"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_social_groups["reset"] . "\" />\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t\t";
            $Query = sql_query("SELECT m.userid, m.type, u.username, u.avatar, g.namestyle FROM ts_social_group_members m LEFT JOIN users u ON (u.$id = m.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE m.$groupid = " . sqlesc($groupid) . " AND m.$type = 'inviteonly' ORDER by u.username ASC, m.joined DESC");
            $TotalMembers = mysqli_num_rows($Query);
            $ShowMembers = "";
            if (0 < $TotalMembers) {
                $ShowMembers = "\r\n\t\t\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t";
                for ($count = 0; $Members = mysqli_fetch_assoc($Query); $count++) {
                    if ($count % 15 == 0) {
                        $ShowMembers .= "</tr><tr>";
                    }
                    $ULink = "<a $href = \"" . ts_seo($Members["userid"], $Members["username"]) . "\">" . get_user_color($Members["username"], $Members["namestyle"]) . "</a>";
                    $UAvatar = get_user_avatar($Members["avatar"], true, "50", "50");
                    $ShowMembers .= "\r\n\t\t\t\t\t\t\t<td class=\"none\">" . $UAvatar . "<br />" . $ULink . " [<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage&amp;$userid = " . $Members["userid"] . "&amp;$groupid = " . $groupid . "\" $alt = \"" . $lang->ts_social_groups["deleteinv"] . "\" $title = \"" . $lang->ts_social_groups["deleteinv"] . "\"><b>x</b></a>]</td>\r\n\t\t\t\t\t";
                }
                $ShowMembers .= "\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>";
            } else {
                $ShowMembers .= "<tr><td>" . $lang->ts_social_groups["nopending"] . "</td></tr>";
            }
            $str .= "\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ts_collapse("pending") . "\r\n\t\t\t\t\t\t" . $lang->ts_social_groups["pending"] . " (" . ts_nf($TotalMembers) . ")\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("pending", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t" . $ShowMembers . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t\t";
            stdhead($lang->ts_social_groups["managem"]);
            add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb(htmlspecialchars_uni($Name), $_SERVER["SCRIPT_NAME"] . "?do=showgroup&$groupid = " . $groupid);
            add_breadcrumb($lang->ts_social_groups["managem"]);
            build_breadcrumb();
            show_sg_errors();
            echo $str;
            stdfoot();
            exit;
        }
    } else {
        $errors[] = $lang->ts_social_groups["invalid"];
    }
}
if ($action == "delete_report" && is_valid_id($groupId) && isset($_GET["rid"]) && is_valid_id($_GET["rid"])) {
    $reportId = intval($_GET["rid"]);
    ($reportQuery = sql_query("SELECT r.rid, sg.owner FROM ts_social_group_reports r LEFT JOIN ts_social_groups sg ON (r.$groupid = sg.groupid) WHERE r.$rid = " . sqlesc($reportId) . " AND r.$groupid = " . sqlesc($groupId))) || sqlerr(__FILE__, 369);
    if (0 < mysqli_num_rows($reportQuery)) {
        $reportResult = mysqli_fetch_assoc($reportQuery);
        if (($reportResult["owner"] == $CURUSER["id"] || $is_mod) && SGPermission("canmanagegroup")) {
            sql_query("DELETE FROM ts_social_group_reports WHERE $rid = " . sqlesc($reportId) . " AND $groupid = " . sqlesc($groupId)) || sqlerr(__FILE__, 375);
            redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : ""));
            exit;
        }
    }
}
if ($action == "report_msg" && is_valid_id($groupId) && isset($_GET["mid"]) && is_valid_id($_GET["mid"])) {
    $messageId = intval($_GET["mid"]);
    ($messageQuery = sql_query("SELECT m.mid, sg.name FROM ts_social_group_messages m LEFT JOIN ts_social_groups sg ON (m.$groupid = sg.groupid) WHERE m.$mid = " . sqlesc($messageId) . " AND m.$groupid = " . sqlesc($groupId))) || sqlerr(__FILE__, 385);
    $messageResult = mysqli_fetch_assoc($messageQuery);
    $groupName = $messageResult["name"];
    if (0 < mysqli_num_rows($messageQuery)) {
        ($reportCheckQuery = sql_query("SELECT rid FROM ts_social_group_reports WHERE $mid = " . sqlesc($messageId) . " AND $groupid = " . sqlesc($groupId) . " AND $userid = " . sqlesc($CURUSER["id"]) . ")) || sqlerr(__FILE__, 392);
        if (0 < mysqli_num_rows($reportCheckQuery)) {
            stderr($lang->global["error"], $lang->ts_social_groups["error12"]);
        } else {
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                $reportReason = trim($_POST["reason"]);
                $reportDate = TIMENOW;
                $savePage = isset($_GET["page"]) ? intval($_GET["page"]) : 0;
                if (!$reportReason || strlen($reportReason) < 3) {
                    $errors[] = $lang->ts_social_groups["error13"];
                } else {
                    sql_query("INSERT INTO ts_social_group_reports (mid, groupid, userid, dateline, report, page) VALUES (" . sqlesc($messageId) . ", " . sqlesc($groupId) . ", " . sqlesc($CURUSER["id"]) . ", " . sqlesc($reportDate) . ", " . sqlesc($reportReason) . ", " . sqlesc($savePage) . ")") || sqlerr(__FILE__, 410);
                    redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "#message_" . $messageId);
                    exit;
                }
            }
            stdhead($lang->ts_social_groups["reportpost"]);
            add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb(htmlspecialchars_uni($groupName), $_SERVER["SCRIPT_NAME"] . "?do=showgroup&$groupid = " . $groupId);
            add_breadcrumb($lang->ts_social_groups["reportpost"]);
            build_breadcrumb();
            show_sg_errors();
            $pageHtml .= "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=report_msg&amp;$mid = " . $messageId . "&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"report_msg\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"groupid\" $value = \"" . intval($groupId) . "\" />\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ts_collapse("reportpost") . "\r\n\t\t\t\t\t\t" . $lang->ts_social_groups["reportpost"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("reportpost", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["reason"] . "</legend>\r\n\t\t\t\t\t\t\t\t<textarea $name = \"reason\" $rows = \"6\" $cols = \"85\">" . (isset($reportReason) ? htmlspecialchars_uni($reportReason) : "") . "</textarea><br />\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_social_groups["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_social_groups["reset"] . "\" />\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t";
            $pageHtml .= "\r\n\t\t\t</table>\r\n\t\t\t</form>";
            echo $pageHtml;
            stdfoot();
            exit;
        }
    } else {
        print_no_permission();
    }
}
if ($action == "edit_msg" && is_valid_id($groupId) && isset($_GET["mid"]) && is_valid_id($_GET["mid"])) {
    $messageId = intval($_GET["mid"]);
    ($messageQuery = sql_query("SELECT m.userid, m.message, sg.owner, sg.name FROM ts_social_group_messages m LEFT JOIN ts_social_groups sg ON (m.$groupid = sg.groupid) WHERE m.$mid = " . sqlesc($messageId) . " AND m.$groupid = " . sqlesc($groupId))) || sqlerr(__FILE__, 461);
    if (0 < mysqli_num_rows($messageQuery)) {
        $messageResult = mysqli_fetch_assoc($messageQuery);
        if ($messageResult["owner"] == $CURUSER["id"] && SGPermission("canmanagegroup") || $is_mod && SGPermission("canmanagegroup") || $messageResult["userid"] == $CURUSER["id"] && SGPermission("canmanagemsg")) {
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                $messageBody = trim($_POST["message"]);
                if (!$messageBody || strlen($messageBody) < 3) {
                    $errors[] = $lang->ts_social_groups["error1"];
                } else {
                    sql_query("UPDATE ts_social_group_messages SET $message = " . sqlesc($messageBody) . " WHERE $mid = " . sqlesc($messageId) . " AND $groupid = " . sqlesc($groupId)) || sqlerr(__FILE__, 476);
                    redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "#message_" . $messageId);
                    exit;
                }
            }
            stdhead($lang->ts_social_groups["editpost"]);
            add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb(htmlspecialchars_uni($messageResult["name"]), $_SERVER["SCRIPT_NAME"] . "?do=showgroup&$groupid = " . $groupId);
            add_breadcrumb($lang->ts_social_groups["editpost"]);
            build_breadcrumb();
            show_sg_errors();
            $pageHtml .= "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_msg&amp;$mid = " . $messageId . "&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"edit_msg\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"groupid\" $value = \"" . intval($groupId) . "\" />\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ts_collapse("editmsg") . "\r\n\t\t\t\t\t\t" . $lang->ts_social_groups["editpost"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("editmsg", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["message"] . "</legend>\r\n\t\t\t\t\t\t\t\t<textarea $name = \"message\" $rows = \"6\" $cols = \"85\">" . (isset($messageBody) ? htmlspecialchars_uni($messageBody) : htmlspecialchars_uni($messageResult["message"])) . "</textarea><br />\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_social_groups["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_social_groups["reset"] . "\" />\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t";
            $pageHtml .= "\r\n\t\t\t</table>\r\n\t\t\t</form>";
            echo $pageHtml;
            stdfoot();
            exit;
        }
        print_no_permission(true);
    } else {
        print_no_permission(true);
    }
}
if ($action == "delete_msg" && is_valid_id($groupId) && isset($_GET["mid"]) && is_valid_id($_GET["mid"])) {
    $messageId = intval($_GET["mid"]);
    ($messageQuery = sql_query("SELECT m.userid, sg.owner FROM ts_social_group_messages m LEFT JOIN ts_social_groups sg ON (m.$groupid = sg.groupid) WHERE m.$mid = " . sqlesc($messageId) . " AND m.$groupid = " . sqlesc($groupId))) || sqlerr(__FILE__, 531);
    if (0 < mysqli_num_rows($messageQuery)) {
        $messageResult = mysqli_fetch_assoc($messageQuery);
        if ($messageResult["owner"] == $CURUSER["id"] && SGPermission("canmanagegroup") || $is_mod && SGPermission("canmanagegroup") || $messageResult["userid"] == $CURUSER["id"] && SGPermission("canmanagemsg")) {
            sql_query("DELETE FROM ts_social_group_messages WHERE $mid = " . sqlesc($messageId) . " AND $groupid = " . sqlesc($groupId));
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $lastPostResults = ["userid" => "", "posted" => ""];
                $lastPostQuery = sql_query(" SELECT userid, posted FROM ts_social_group_messages WHERE $groupid = " . sqlesc($groupId) . " ORDER by posted DESC LIMIT 1");
                if (0 < mysqli_num_rows($lastPostQuery)) {
                    $lastPostResults = mysqli_fetch_assoc($lastPostQuery);
                }
                sql_query("UPDATE ts_social_groups SET $messages = IF(messages > 0, messages - 1, 0), $lastpostdate = '" . $lastPostResults["posted"] . "', $lastposter = '" . $lastPostResults["userid"] . "' WHERE $groupid = " . sqlesc($groupId));
            }
            redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : ""));
            exit;
        }
        print_no_permission(true);
    } else {
        print_no_permission(true);
    }
}
if ($action == "deny_invite" && is_valid_id($groupId)) {
    ($inviteQuery = sql_query("SELECT m.type, sg.name, sg.owner FROM ts_social_group_members m LEFT JOIN ts_social_groups sg ON (m.$groupid = sg.groupid) WHERE m.$userid = " . sqlesc($CURUSER["id"]) . " AND m.$groupid = " . sqlesc($groupId) . " AND m.$type = 'inviteonly'")) || sqlerr(__FILE__, 564);
    if (mysqli_num_rows($inviteQuery) == 0) {
        stderr($lang->global["error"], $lang->ts_social_groups["error4"]);
    } else {
        $inviteResult = mysqli_fetch_assoc($inviteQuery);
        if (!$inviteResult["name"] || $inviteResult["owner"] == $CURUSER["id"]) {
            stderr($lang->global["error"], $lang->ts_social_groups["invalid"]);
        } else {
            sql_query("DELETE FROM ts_social_group_members WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $groupid = " . sqlesc($groupId) . " AND $type = 'inviteonly'") || sqlerr(__FILE__, 578);
            redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId));
            exit;
        }
    }
}
if ($action == "accept_invite" && is_valid_id($groupId) && SGPermission("canjoin")) {
    ($inviteQuery = sql_query("SELECT m.type, sg.name, sg.owner FROM ts_social_group_members m LEFT JOIN ts_social_groups sg ON (m.$groupid = sg.groupid) WHERE m.$userid = " . sqlesc($CURUSER["id"]) . " AND m.$groupid = " . sqlesc($groupId) . " AND m.$type = 'inviteonly'")) || sqlerr(__FILE__, 587);
    if (mysqli_num_rows($inviteQuery) == 0) {
        stderr($lang->global["error"], $lang->ts_social_groups["error4"]);
    } else {
        $inviteResult = mysqli_fetch_assoc($inviteQuery);
        if (!$inviteResult["name"] || $inviteResult["owner"] == $CURUSER["id"]) {
            stderr($lang->global["error"], $lang->ts_social_groups["invalid"]);
        } else {
            sql_query("UPDATE ts_social_group_members SET $type = 'public' WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $groupid = " . sqlesc($groupId) . " AND $type = 'inviteonly'") || sqlerr(__FILE__, 601);
            sql_query("UPDATE ts_social_groups SET $members = members + 1 WHERE $groupid = " . sqlesc($groupId)) || sqlerr(__FILE__, 602);
            redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId));
            exit;
        }
    }
}
if ($action == "invite" && is_valid_id($groupId)) {
    $groupQuery = sql_query("SELECT name, owner FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId));
    if (0 < mysqli_num_rows($groupQuery)) {
        $groupResult = mysqli_fetch_assoc($groupQuery);
        $groupOwnerId = $groupResult["owner"];
        $groupName = $groupResult["name"];
        if ($groupOwnerId != $CURUSER["id"] && !$is_mod) {
            print_no_permission(true);
        } else {
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                $targetUsername = trim($_POST["username"]);
                $userQuery = sql_query("SELECT u.id, g.sgperms FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$username = " . sqlesc($targetUsername) . " AND u.$status = 'confirmed' AND u.$enabled = 'yes' AND u.usergroup != 9");
                if (mysqli_num_rows($userQuery) == 0) {
                    $errors[] = $lang->ts_social_groups["error8"];
                } else {
                    $userResult = mysqli_fetch_assoc($userQuery);
                    if ($groupOwnerId == $userResult["id"]) {
                        $errors[] = $lang->ts_social_groups["error10"];
                    } else {
                        if (!SGPermission("canjoin", $userResult["sgperms"])) {
                            $errors[] = $lang->ts_social_groups["error11"];
                        } else {
                            $membershipQuery = sql_query("SELECT userid FROM ts_social_group_members WHERE $userid = " . sqlesc($userResult["id"]) . " AND $groupid = " . sqlesc($groupId));
                            if (0 < mysqli_num_rows($membershipQuery)) {
                                $errors[] = $lang->ts_social_groups["error9"];
                            } else {
                                sql_query("REPLACE INTO ts_social_group_members VALUES (" . sqlesc($userResult["id"]) . ", " . sqlesc($groupId) . ", " . sqlesc(TIMENOW) . ", 'inviteonly')");
                                require_once INC_PATH . "/functions_pm.php";
                                $subject = $lang->ts_social_groups["invitetitle"];
                                $message = sprintf($lang->ts_social_groups["invitemsg"], "[b]" . htmlspecialchars_uni($groupName) . "[/b]", "[URL=" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "][b]" . $CURUSER["username"] . "[/b][/URL]", "[URL]" . $BASEURL . $_SERVER["SCRIPT_NAME"] . "?do=accept_invite&$groupid = " . intval($groupId) . "[/URL]", "[URL]" . $BASEURL . $_SERVER["SCRIPT_NAME"] . "?do=deny_invite&$groupid = " . intval($groupId) . "[/URL]");
                                send_pm($userResult["id"], $message, $subject);
                                redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : ""));
                                exit;
                            }
                        }
                    }
                }
            }
            stdhead($lang->ts_social_groups["invitemem"]);
            add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb(htmlspecialchars_uni($groupName), $_SERVER["SCRIPT_NAME"] . "?do=showgroup&$groupid = " . $groupId);
            add_breadcrumb($lang->ts_social_groups["invitemem"]);
            build_breadcrumb();
            show_sg_errors();
            $pageHtml .= "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=invite&amp;$groupid = " . intval($groupId) . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"invite\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"groupid\" $value = \"" . intval($groupId) . "\" />\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ts_collapse("invite") . "\r\n\t\t\t\t\t\t" . $lang->ts_social_groups["invitemem"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("invite", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["username"] . "</legend>\r\n\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"30\" $name = \"username\" $value = \"" . (isset($targetUsername) ? htmlspecialchars_uni($targetUsername) : "") . "\" />\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_social_groups["invite"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_social_groups["reset"] . "\" />\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t";
            $pageHtml .= "\r\n\t\t\t</table>\r\n\t\t\t</form>";
            echo $pageHtml;
            stdfoot();
            exit;
        }
    } else {
        $errors[] = $lang->ts_social_groups["invalid"];
    }
}
if ($action == "subscribe" && is_valid_id($groupId)) {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    $groupQuery = sql_query("SELECT owner FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId));
    if (0 < mysqli_num_rows($groupQuery)) {
        ($subscribeQuery = sql_query("SELECT uid FROM ts_social_groups_subscribe WHERE $groupid = " . sqlesc($groupId) . " AND $uid = " . sqlesc($CURUSER["id"])) || sqlerr(__FILE__, 715);
        if (0 < mysqli_num_rows($subscribeQuery)) {
            sql_query("DELETE FROM ts_social_groups_subscribe WHERE $groupid = " . sqlesc($groupId) . " AND $uid = " . sqlesc($CURUSER["id"])) or sql_query("DELETE FROM ts_social_groups_subscribe WHERE $groupid = " . sqlesc($groupId) . " AND $uid = " . sqlesc($CURUSER["id"])) || sqlerr(__FILE__, 718);
        } else {
            sql_query("INSERT INTO ts_social_groups_subscribe VALUES (NULL, " . sqlesc($groupId) . ", " . sqlesc($CURUSER["id"]) . ")") || sqlerr(__FILE__, 722);
        }
        $action = "showgroup";
    } else {
        $errors[] = $lang->ts_social_groups["invalid"];
    }
}
if ($action == "delete" && is_valid_id($groupId) && SGPermission("candelete")) {
    $groupQuery = sql_query("SELECT owner FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId));
    if (0 < mysqli_num_rows($groupQuery)) {
        $groupResult = mysqli_fetch_assoc($groupQuery);
        $groupOwnerId = $groupResult["owner"];
        if ($groupOwnerId != $CURUSER["id"] && !$is_mod) {
            print_no_permission(true);
        } else {
            sql_query("DELETE FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId));
            sql_query("DELETE FROM ts_social_group_members WHERE $groupid = " . sqlesc($groupId));
            sql_query("DELETE FROM ts_social_group_messages WHERE $groupid = " . sqlesc($groupId));
            sql_query("DELETE FROM ts_social_group_reports WHERE $groupid = " . sqlesc($groupId));
            sql_query("DELETE FROM ts_social_groups_subscribe WHERE $groupid = " . sqlesc($groupId));
        }
    } else {
        $errors[] = $lang->ts_social_groups["invalid"];
    }
}
if ($action == "leave" && is_valid_id($groupId)) {
    $groupQuery = sql_query("SELECT owner FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId));
    if (0 < mysqli_num_rows($groupQuery)) {
        $groupResult = mysqli_fetch_assoc($groupQuery);
        $groupOwnerId = $groupResult["owner"];
        if ($groupOwnerId == $CURUSER["id"]) {
            $errors[] = $lang->ts_social_groups["error5"];
        } else {
            sql_query("DELETE FROM ts_social_group_members WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $groupid = " . sqlesc($groupId));
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                sql_query("UPDATE ts_social_groups SET $members = IF(members > 0, members - 1, 0) WHERE $groupid = " . sqlesc($groupId));
            } else {
                $errors[] = $lang->ts_social_groups["error7"];
            }
        }
    } else {
        $errors[] = $lang->ts_social_groups["invalid"];
    }
}
if ($action == "join" && is_valid_id($groupId) && SGPermission("canjoin")) {
    $membershipQuery = sql_query("SELECT userid FROM ts_social_group_members WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $groupid = " . sqlesc($groupId));
    if (mysqli_num_rows($membershipQuery) == 0) {
        $groupTypeQuery = sql_query("SELECT type FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId));
        if (0 < mysqli_num_rows($groupTypeQuery)) {
            $groupTypeResult = mysqli_fetch_assoc($groupTypeQuery);
            $groupType = $groupTypeResult["type"];
            if ($groupType == "public") {
                sql_query("REPLACE INTO ts_social_group_members VALUES (" . sqlesc($CURUSER["id"]) . ", " . sqlesc($groupId) . ", " . sqlesc(TIMENOW) . ", 'public')");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    sql_query("UPDATE ts_social_groups SET $members = members + 1 WHERE $groupid = " . sqlesc($groupId)) or sql_query("UPDATE ts_social_groups SET $members = members + 1 WHERE $groupid = " . sqlesc($groupId)) || sqlerr(__FILE__, 803);
                }
            } else {
                $errors[] = $lang->ts_social_groups["error4"];
            }
        } else {
            $errors[] = $lang->ts_social_groups["invalid"];
        }
    } else {
        $errors[] = $lang->ts_social_groups["error6"];
    }
}
if ($action == "edit" && SGPermission("canedit") && is_valid_id($groupId)) {
    $groupQuery = sql_query("SELECT name, description, type, owner FROM ts_social_groups WHERE $groupid = " . sqlesc($groupId));
    if (0 < mysqli_num_rows($groupQuery)) {
        $editGroup = mysqli_fetch_assoc($groupQuery);
        if ($editGroup["owner"] != $CURUSER["id"] && !$is_mod) {
            print_no_permission(true);
        }
    } else {
        print_no_permission();
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $groupName = trim($_POST["name"]);
        $groupDescription = trim($_POST["description"]);
        $groupType = $_POST["type"] == "public" ? "public" : "inviteonly";
        if (strlen($groupName) < 3) {
            $errors[] = $lang->ts_social_groups["error2"];
        }
        if (strlen($groupDescription) < 10) {
            $errors[] = $lang->ts_social_groups["error3"];
        }
        if (count($errors) == 0 && ($groupName != $editGroup["name"] || $groupDescription != $editGroup["description"] || $groupType != $editGroup["type"])) {
            sql_query("UPDATE ts_social_groups SET $name = " . sqlesc($groupName) . ", $description = " . sqlesc($groupDescription) . ", $type = '" . $groupType . "' WHERE $groupid = " . sqlesc($groupId)) || sqlerr(__FILE__, 852);
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId));
                exit;
            }
            $errors[] = $lang->ts_social_groups["dberror"];
        } else {
            redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . intval($groupId));
            exit;
        }
    }
    stdhead($lang->ts_social_groups["edit"]);
    add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
    add_breadcrumb(htmlspecialchars_uni($editGroup["name"]), $_SERVER["SCRIPT_NAME"] . "?do=showgroup&$groupid = " . $groupId);
    add_breadcrumb($lang->ts_social_groups["edit"]);
    build_breadcrumb();
    show_sg_errors();
    $pageHtml .= "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit&amp;$groupid = " . intval($groupId) . "\">\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"edit\" />\r\n\t<input $type = \"hidden\" $name = \"groupid\" $value = \"" . intval($groupId) . "\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . ts_collapse("create") . "\r\n\t\t\t\t" . $lang->ts_social_groups["edit"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . ts_collapse("create", 2) . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["name"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" $size = \"100\" $name = \"name\" $value = \"" . (isset($groupName) ? htmlspecialchars_uni($groupName) : htmlspecialchars_uni($editGroup["name"])) . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["description"] . "</legend>\r\n\t\t\t\t\t\t<textarea $name = \"description\" $rows = \"6\" $cols = \"85\">" . (isset($groupDescription) ? htmlspecialchars_uni($groupDescription) : htmlspecialchars_uni($editGroup["description"])) . "</textarea>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["type"] . "</legend>\r\n\t\t\t\t\t\t<select $name = \"type\">\r\n\t\t\t\t\t\t\t<option $value = \"public\"" . (isset($groupType) && $groupType == "public" ? " $selected = \"selected\"" : ($editGroup["type"] == "public" ? " $selected = \"selected\"" : "")) . ">" . $lang->ts_social_groups["public"] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"inviteonly\"" . (isset($groupType) && $groupType == "inviteonly" ? " $selected = \"selected\"" : ($editGroup["type"] == "inviteonly" ? " $selected = \"selected\"" : "")) . ">" . $lang->ts_social_groups["inviteonly"] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_social_groups["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_social_groups["reset"] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t";
    $pageHtml .= "\r\n\t</table>\r\n\t</form>";
    echo $pageHtml;
    stdfoot();
    exit;
}
if ($action == "create" && SGPermission("cancreate") && SGPermission("canjoin")) {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $groupName = trim($_POST["name"]);
        $groupDescription = trim($_POST["description"]);
        $groupType = $_POST["type"] == "public" ? "public" : "inviteonly";
        if (strlen($groupName) < 3) {
            $errors[] = $lang->ts_social_groups["error2"];
        }
        if (strlen($groupDescription) < 10) {
            $errors[] = $lang->ts_social_groups["error3"];
        }
        if (count($errors) == 0) {
            $ownerId = 0 + $CURUSER["id"];
            $dateCreated = TIMENOW;
            sql_query("INSERT INTO ts_social_groups (name, description, owner, dateline, members, type) VALUES (" . sqlesc($groupName) . ", " . sqlesc($groupDescription) . ", '" . $ownerId . "', '" . $dateCreated . "', '1', '" . $groupType . "')") || sqlerr(__FILE__, 937);
            $newGroupId = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"]) && $newGroupId) {
                sql_query("INSERT INTO ts_social_group_members VALUES ('" . $ownerId . "', '" . $newGroupId . "', '" . $dateCreated . "', 'public')") || sqlerr(__FILE__, 941);
                redirect("ts_social_groups.php?do=showgroup&amp;$groupid = " . $newGroupId);
                exit;
            }
            $errors[] = $lang->ts_social_groups["dberror"];
        }
    }
    stdhead($lang->ts_social_groups["create"]);
    add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
    add_breadcrumb($lang->ts_social_groups["create"]);
    build_breadcrumb();
    show_sg_errors();
    $pageHtml .= "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=create">\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"create\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . ts_collapse("create") . "\r\n\t\t\t\t" . $lang->ts_social_groups["create"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . ts_collapse("create", 2) . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["name"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" $size = \"100\" $name = \"name\" $value = \"" . (isset($groupName) ? htmlspecialchars_uni($groupName) : "") . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["description"] . "</legend>\r\n\t\t\t\t\t\t<textarea $name = \"description\" $rows = \"6\" $cols = \"85\">" . (isset($groupDescription) ? htmlspecialchars_uni($groupDescription) : "") . "</textarea>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_social_groups["type"] . "</legend>\r\n\t\t\t\t\t\t<select $name = \"type\">\r\n\t\t\t\t\t\t\t<option $value = \"public\"" . (isset($groupType) && $groupType == "public" ? " $selected = \"selected\"" : "") . ">" . $lang->ts_social_groups["public"] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"inviteonly\"" . (isset($groupType) && $groupType == "inviteonly" ? " $selected = \"selected\"" : "") . ">" . $lang->ts_social_groups["inviteonly"] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_social_groups["create"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_social_groups["reset"] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t";
    $pageHtml .= "\r\n\t</table>\r\n\t</form>";
    echo $pageHtml;
    stdfoot();
    exit;
}
if ($action == "showgroup" && is_valid_id($groupId)) {
    ($groupQuery = sql_query("SELECT sg.name, sg.description, sg.owner, sg.type, u.username, g.namestyle, s.uid as issubs FROM ts_social_groups sg LEFT JOIN users u ON (u.$id = sg.owner) LEFT JOIN usergroups g ON (g.$gid = u.usergroup) LEFT JOIN ts_social_groups_subscribe s ON (s.$groupid = sg.groupid AND s.$uid = " . $CURUSER["id"] . ") WHERE sg.$groupid = " . sqlesc($groupId))) || sqlerr(__FILE__, 999);
    if (0 < mysqli_num_rows($groupQuery)) {
        $groupData = mysqli_fetch_assoc($groupQuery);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && SGPermission("canpost")) {
            $membershipQuery = sql_query("SELECT userid FROM ts_social_group_members WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $type = 'public'");
            if (mysqli_num_rows($membershipQuery) == 0) {
                $errors[] = $lang->ts_social_groups["error7"];
            } else {
                $messageBody = trim($_POST["message"]);
                if (!$messageBody || strlen($messageBody) < 3) {
                    $errors[] = $lang->ts_social_groups["error1"];
                } else {
                    $userId = intval($CURUSER["id"]);
                    $datePosted = TIMENOW;
                    sql_query("INSERT INTO ts_social_group_messages (groupid, userid, posted, message) VALUES (" . sqlesc($groupId) . ", " . sqlesc($CURUSER["id"]) . ", " . sqlesc($datePosted) . ", " . sqlesc($messageBody) . ")") || sqlerr(__FILE__, 1021);
                    $messageId = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                    if ($messageId) {
                        if ($groupData["owner"] != $CURUSER["id"]) {
                            require_once INC_PATH . "/functions_pm.php";
                            send_pm($groupData["owner"], sprintf($lang->ts_social_groups["s4"], $BASEURL . "/ts_social_groups.php?do=showgroup&$groupid = " . $groupId . "#message_" . $messageId), $lang->ts_social_groups["s5"]);
                        }
                        sql_query("UPDATE ts_social_groups SET $messages = messages + 1, $lastpostdate = '" . $datePosted . "', $lastposter = '" . $CURUSER["id"] . "' WHERE $groupid = " . sqlesc($groupId));
                        $subscriptionQuery = sql_query("SELECT uid FROM ts_social_groups_subscribe WHERE $groupid = " . sqlesc($groupId) . " AND uid != " . sqlesc($CURUSER["id"]));
                        if (0 < mysqli_num_rows($subscriptionQuery)) {
                            require_once INC_PATH . "/functions_pm.php";
                            while ($userResult = mysqli_fetch_assoc($subscriptionQuery)) {
                                send_pm($userResult["uid"], sprintf($lang->ts_social_groups["s6"], $BASEURL . "/ts_social_groups.php?do=showgroup&$groupid = " . $groupId . "#message_" . $messageId), $lang->ts_social_groups["s5"]);
                            }
                        }
                    }
                }
            }
        }
        $InviteButton = $SG["type"] == "inviteonly" && $SG["owner"] == $CURUSER["id"] || $SG["type"] == "inviteonly" && $is_mod ? "<input $type = \"button\" $value = \"" . $lang->ts_social_groups["invitemem"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=invite&amp;$groupid = " . $groupid . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "'); return false;\" />" : "";
        $ManageButton = $is_mod && SGPermission("canmanagegroup") || $SG["owner"] == $CURUSER["id"] && SGPermission("canmanagegroup") ? "<input $type = \"button\" $value = \"" . $lang->ts_social_groups["masspm"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=masspm&amp;$groupid = " . $groupid . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "'); return false;\" /> <input $type = \"button\" $value = \"" . $lang->ts_social_groups["managem"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=manage&amp;$groupid = " . $groupid . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "'); return false;\" />" : "";
        $str .= (!empty($ManageButton) || !empty($InviteButton) ? "<p $style = \"float: right;\">" . $ManageButton . " " . $InviteButton . "</p>" : "") . "\r\n\t\t<p $style = \"float: left;\">\r\n\t\t\t<input $type = \"button\" $value = \"" . ($SG["issubs"] == $CURUSER["id"] ? $lang->ts_social_groups["s2"] : $lang->ts_social_groups["s1"]) . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=subscribe&amp;$groupid = " . $groupid . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "'); return false;\" />\r\n\t\t</p>\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . ts_collapse("groupss") . "\r\n\t\t\t\t\t" . $lang->ts_social_groups["head2"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . ts_collapse("groupss", 2) . "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<span $style = \"float: right;\">" . sprintf($SG["type"] == "public" ? $lang->ts_social_groups["type1"] : $lang->ts_social_groups["type2"], "<a $href = \"" . ts_seo($SG["owner"], $SG["username"]) . "\">" . get_user_color($SG["username"], $SG["namestyle"]) . "</a>") . "</span>\r\n\t\t\t\t\t\t<h1>" . htmlspecialchars_uni($SG["name"]) . "</h1>\r\n\t\t\t\t\t\t" . htmlspecialchars_uni($SG["description"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t<br />\r\n\t\t";
        $Query = sql_query("SELECT m.userid, m.type, u.username, u.avatar, g.namestyle FROM ts_social_group_members m LEFT JOIN users u ON (u.$id = m.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE m.$groupid = " . sqlesc($groupid) . " ORDER by u.username ASC, m.joined DESC");
        $TotalMembers = mysqli_num_rows($Query);
        $ShowMembers = "";
        if (0 < $TotalMembers) {
            $ShowMembers = "\r\n\t\t\t<style $type = \"text/css\" $id = \"TSSE_Social_Groups_CSS\">\r\n\t\t\t\t#scg_member_list\r\n\t\t\t\t{\r\n\t\t\t\t\tmargin: 0px;\r\n\t\t\t\t\tpadding: 0px;\r\n\t\t\t\t\tlist-style-type: none;\r\n\t\t\t\t}\r\n\t\t\t\t#scg_member_list .group_members_small\r\n\t\t\t\t{\r\n\t\t\t\t\toverflow: hidden;\r\n\t\t\t\t\tfloat: left;\r\n\t\t\t\t\ttext-align: center;\r\n\t\t\t\t\tmargin: 1px;\r\n\t\t\t\t\theight: 100px;\r\n\t\t\t\t\twidth: 76px;\r\n\t\t\t\t}\r\n\t\t\t</style>\r\n\t\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t<ul $id = \"scg_member_list\">\r\n\t\t\t";
            $IsGroupMember = false;
            while ($Members = mysqli_fetch_assoc($Query)) {
                if ($Members["userid"] === $CURUSER["id"] && $Members["type"] == "public") {
                    $IsGroupMember = true;
                }
                if ($Members["type"] != "inviteonly") {
                    $Link = ts_seo($Members["userid"], $Members["username"]);
                    $ShowMembers .= "\r\n\t\t\t\t\t\t\t\t<li class=\"group_members_small\">\r\n\t\t\t\t\t\t\t\t\t<a $href = \"" . $Link . "\">" . get_user_avatar($Members["avatar"], true, "60", "60") . "</a>\r\n\t\t\t\t\t\t\t\t\t<div class=\"smallfont\" $title = \"" . $Members["username"] . "\">\r\n\t\t\t\t\t\t\t\t\t\t<a $href = \"" . $Link . "\">" . get_user_color($Members["username"], $Members["namestyle"]) . "</a>\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</li>";
                } else {
                    $TotalMembers--;
                }
            }
            $ShowMembers .= "\r\n\t\t\t\t\t\t\t</ul>\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>";
        }
        if ($SG["type"] == "inviteonly" && !$IsGroupMember && !$is_mod) {
            print_no_permission();
        }
        $str .= "\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . ts_collapse("memberss") . "\r\n\t\t\t\t\t" . $lang->ts_social_groups["title1"] . " (" . ts_nf($TotalMembers) . ")\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . ts_collapse("memberss", 2) . "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t" . $ShowMembers . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t<br />\r\n\t\t";
        $ShowReports = "";
        if ($SG["owner"] == $CURUSER["id"] && SGPermission("canmanagegroup") || SGPermission("canmanagegroup") && $is_mod) {
            $Query = sql_query("SELECT r.*, u.username, g.namestyle FROM ts_social_group_reports r LEFT JOIN users u ON (r.$userid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE r.$groupid = " . sqlesc($groupid));
            if (0 < mysqli_num_rows($Query)) {
                $ShowReports .= "\r\n\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\" $colspan = \"4\">\r\n\t\t\t\t\t\t\t" . ts_collapse("shwreports") . "\r\n\t\t\t\t\t\t\t" . $lang->ts_social_groups["shwreports"] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t" . ts_collapse("shwreports", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"subheader\" $width = \"15%\">" . $lang->ts_social_groups["reportby"] . "</td>\r\n\t\t\t\t\t\t<td class=\"subheader\" $width = \"15%\" $align = \"center\">" . $lang->ts_social_groups["created"] . "</td>\r\n\t\t\t\t\t\t<td class=\"subheader\" $width = \"45%\">" . $lang->ts_social_groups["reason"] . "</td>\r\n\t\t\t\t\t\t<td class=\"subheader\" $width = \"25%\" $align = \"center\">" . $lang->ts_social_groups["options"] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t";
                while ($Reports = mysqli_fetch_assoc($Query)) {
                    $RLink = "<a $href = \"" . ts_seo($Reports["userid"], $Reports["username"]) . "\">" . get_user_color($Reports["username"], $Reports["namestyle"]) . "</a>";
                    $RMsg = format_comment($Reports["report"]);
                    $RPosted = my_datee($dateformat, $Reports["dateline"]) . " " . my_datee($timeformat, $Reports["dateline"]);
                    $ShowReports .= "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $width = \"15%\">" . $RLink . "</td>\r\n\t\t\t\t\t\t<td $width = \"15%\" $align = \"center\">" . $RPosted . "</td>\r\n\t\t\t\t\t\t<td $width = \"45%\">" . $RMsg . "</td>\r\n\t\t\t\t\t\t<td $width = \"25%\" $align = \"center\"><input $type = \"button\" $value = \"" . $lang->ts_social_groups["showpost"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=showgroup&amp;$groupid = " . $groupid . "&amp;$page = " . $Reports["page"] . "#message_" . $Reports["mid"] . "'); return false;\" /> <input $type = \"button\" $value = \"" . $lang->ts_social_groups["delete"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=delete_report&amp;$rid = " . $Reports["rid"] . "&amp;$groupid = " . $groupid . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "'); return false;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t";
                }
                $ShowReports .= "\r\n\t\t\t\t\t</tbody>\r\n\t\t\t\t</table>\r\n\t\t\t\t<br />";
            }
        }
        if (0 < $CURUSER["postsperpage"] && is_valid_id($CURUSER["postsperpage"]) && $CURUSER["postsperpage"] <= 50) {
            $perpage = intval($CURUSER["postsperpage"]);
        } else {
            $perpage = $f_postsperpage;
        }
        $Query = sql_query("SELECT mid FROM ts_social_group_messages WHERE $groupid = " . sqlesc($groupid));
        $TotalMessages = mysqli_num_rows($Query);
        list($pagertop, $pagerbottom, $limit) = pager($perpage, $TotalMessages, $_SERVER["SCRIPT_NAME"] . "?do=showgroup&$groupid = " . $groupid . "&amp;");
        $SGMessageForm = "";
        if (SGPermission("canpost") && $IsGroupMember || $is_mod) {
            $lang->load("quick_editor");
            require INC_PATH . "/functions_quick_editor.php";
            require_once INC_PATH . "/class_tsquickbbcodeeditor.php";
            $QuickEditor = new TSQuickBBCodeEditor();
            $QuickEditor->ImagePath = $pic_base_url;
            $QuickEditor->SmiliePath = $pic_base_url . "smilies/";
            $QuickEditor->FormName = "quickreply";
            $QuickEditor->TextAreaName = "message";
            $SGMessageForm = "\r\n\t\t\t" . $QuickEditor->GenerateJavascript() . "\r\n\t\t\t" . ($useajax == "yes" ? "<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_sgm.js\"></script>" : "") . "\r\n\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=showgroup&amp;$groupid = " . $groupid . "\" $name = \"quickreply\" $id = \"quickreply\">\r\n\t\t\t<input $type = \"hidden\" $name = \"groupid\" $value = \"" . $groupid . "\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"showgroup\" />\r\n\t\t\t<br />\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ts_collapse("postmsg") . "\r\n\t\t\t\t\t\t" . $lang->ts_social_groups["postmsg"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("postmsg", 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t" . $QuickEditor->GenerateBBCode() . "\r\n\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t<textarea $name = \"message\" $style = \"width:670px;height:100px;\" $id = \"message\"></textarea><br />\r\n\t\t\t\t\t\t\t<span $id = \"loading-layer\" $style = \"display:none;\"><img $src = \"" . $dimagedir . "loading.gif\" $border = \"0\" $alt = \"\" $title = \"\" class=\"inlineimg\"></span>\r\n\t\t\t\t\t\t\t" . ($useajax == "yes" ? "<input $type = \"button\" class=\"button\" $value = \"" . $lang->ts_social_groups["postmsg"] . "\" $name = \"submitsgm\" $id = \"submitsgm\" $onclick = \"javascript:TSajaxquicksgm('" . $groupid . "');\" />" : "<input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->ts_social_groups["postmsg"] . "\" class=\"button\" />") . "\r\n\t\t\t\t\t\t\t<input $type = \"reset\" $value = \"" . $lang->ts_social_groups["reset"] . "\" class=button />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t\t</form>\r\n\t\t\t";
        }
        $str .= $ShowReports . $pagertop . "\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . ts_collapse("messagess") . "\r\n\t\t\t\t\t" . $lang->ts_social_groups["title2"] . " (" . ts_nf($TotalMessages) . ")\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . ts_collapse("messagess", 2) . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $id = \"PostedQuickMessage\" $name = \"PostedQuickMessage\" $style = \"display: none;\">\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t";
        $Query = sql_query("SELECT m.*, sg.owner, u.username, u.avatar, g.namestyle FROM ts_social_group_messages m LEFT JOIN ts_social_groups sg ON (m.$groupid = sg.groupid) LEFT JOIN users u ON (u.$id = m.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE m.$groupid = " . sqlesc($groupid) . " ORDER BY m.posted DESC " . $limit);
        if (0 < mysqli_num_rows($Query)) {
            while ($Msg = mysqli_fetch_assoc($Query)) {
                $ManageLinks = "[<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=report_msg&amp;$mid = " . $Msg["mid"] . "&amp;$groupid = " . $Msg["groupid"] . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">" . $lang->ts_social_groups["reportpost"] . "</a>] ";
                if ($Msg["owner"] == $CURUSER["id"] && SGPermission("canmanagegroup") || SGPermission("canmanagegroup") && $is_mod || $Msg["userid"] == $CURUSER["id"] && SGPermission("canmanagemsg")) {
                    $ManageLinks .= " [<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_msg&amp;$mid = " . $Msg["mid"] . "&amp;$groupid = " . $Msg["groupid"] . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">" . $lang->ts_social_groups["editpost"] . "</a>] [<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete_msg&amp;$mid = " . $Msg["mid"] . "&amp;$groupid = " . $Msg["groupid"] . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">" . $lang->ts_social_groups["delpost"] . "</a>]";
                }
                $ULink = "<a $href = \"" . ts_seo($Msg["userid"], $Msg["username"]) . "\">" . get_user_color($Msg["username"], $Msg["namestyle"]) . "</a>";
                $UAvatar = get_user_avatar($Msg["avatar"], true, "80", "80");
                $UMsg = format_comment($Msg["message"]);
                $Posted = my_datee($dateformat, $Msg["posted"]) . " " . my_datee($timeformat, $Msg["posted"]);
                $str .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $valign = \"top\">\r\n\t\t\t\t\t\t<table $width = \"100%\" $cellpadding = \"1\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<th $rowspan = \"2\" class=\"none\" $width = \"80\" $height = \"80\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t" . $UAvatar . "\r\n\t\t\t\t\t\t\t\t</th>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div class=\"subheader\"><span $style = \"float: right;\">" . $ManageLinks . "</span>" . sprintf($lang->ts_social_groups["by2"], $Posted, $ULink) . "</div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"message_" . $Msg["mid"] . "\" $name = \"message_" . $Msg["mid"] . "\">\r\n\t\t\t\t\t\t\t\t\t\t" . $UMsg . "\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
        } else {
            $str .= "<tr><td $id = \"NoMessageYet\" $name = \"NoMessageYet\">" . $lang->ts_social_groups["nomsg"] . "</td></tr>";
        }
        $str .= "\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t" . $pagerbottom . $SGMessageForm;
        stdhead($lang->ts_social_groups["head2"]);
        add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
        add_breadcrumb(htmlspecialchars_uni($SG["name"]));
        build_breadcrumb();
        show_sg_errors();
        echo $str;
        stdfoot();
        exit;
    }
    $errors[] = $lang->ts_social_groups["invalid"];
}
($memberQuery = sql_query("SELECT m.*, sg.name, sg.description, sg.owner FROM ts_social_group_members m LEFT JOIN ts_social_groups sg ON (m.$groupid = sg.groupid) WHERE m.$userid = '" . $CURUSER["id"] . "' AND m.$type = 'public' ORDER BY m.joined DESC, sg.name ASC")) || sqlerr(__FILE__, 1333);
if (0 < mysqli_num_rows($memberQuery)) {
    $pageHtml .= "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" class=\"thead\">\r\n\t\t\t\t" . ts_collapse("groupsM") . "\r\n\t\t\t\t" . $lang->ts_social_groups["in"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"75%\" $align = \"left\">\r\n\t\t\t\t" . $lang->ts_social_groups["name"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"subheader\" $width = \"25%\" $align = \"left\">\r\n\t\t\t\t" . $lang->ts_social_groups["joined"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . ts_collapse("groupsM", 2) . "\r\n\t";
    while ($memberRow = mysqli_fetch_assoc($memberQuery)) {
        $leaveButton = $memberRow["owner"] != $CURUSER["id"] && $memberRow["type"] == "public" ? "<span $style = \"float: right;\">[<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=leave&amp;$groupid = " . $memberRow["groupid"] . "\"><b>" . $lang->ts_social_groups["leave"] . "</b></a>]</span>" : "";
        $groupName = $leaveButton . "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=showgroup&amp;$groupid = " . $memberRow["groupid"] . "\"><strong>" . htmlspecialchars_uni($memberRow["name"]) . "</strong></a><br />" . htmlspecialchars_uni($memberRow["description"]);
        $joinedDate = my_datee($dateformat, $memberRow["joined"]) . " " . my_datee($timeformat, $memberRow["joined"]);
        $pageHtml .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"75%\" $align = \"left\">" . $groupName . "</td>\r\n\t\t\t<td $width = \"25%\" $align = \"left\">" . $joinedDate . "</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $pageHtml .= "\r\n\t\t</tbody>\r\n\t</table>";
}
$memberOfGroups = [];
$memberOfQuery = sql_query("SELECT groupid FROM ts_social_group_members WHERE $userid = " . sqlesc($CURUSER["id"]));
if (0 < mysqli_num_rows($memberOfQuery)) {
    while ($memberOfRow = mysqli_fetch_assoc($memberOfQuery)) {
        $memberOfGroups[$memberOfRow["groupid"]] = "1";
    }
}
$createGroupButton = SGPermission("cancreate") && SGPermission("canjoin") ? "<p $style = \"float: right;\"><input $type = \"button\" $value = \"" . $lang->ts_social_groups["create"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=create'); return false;\" /></p>" : "";
($allGroupsQuery = sql_query("SELECT sg.*, u.username, g.namestyle FROM ts_social_groups sg LEFT JOIN users u ON (sg.$lastposter = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) ORDER BY sg.name")) || sqlerr(__FILE__, 1382);
stdhead($lang->ts_social_groups["head"]);
add_breadcrumb($lang->ts_social_groups["head"], $_SERVER["SCRIPT_NAME"]);
build_breadcrumb();
show_sg_errors();
$pageHtml .= "\r\n<script $type = \"text/javascript\">\r\n\tfunction ConfirmDeletion(GroupID)\r\n\t{\r\n\t\tvar Delete = confirm(\"" . $lang->ts_social_groups["sure"] . "\");\r\n\t\tif (Delete)\r\n\t\t{\r\n\t\t\tjumpto(\"" . $_SERVER["SCRIPT_NAME"] . "?do=delete&$groupid = \"+GroupID);\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\treturn false;\r\n\t\t}\r\n\t}\r\n</script>\r\n" . $createGroupButton . "\r\n<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t<tr>\r\n\t\t<td $colspan = \"6\" class=\"thead\">\r\n\t\t\t" . ts_collapse("showgroupsss") . "\r\n\t\t\t" . $lang->ts_social_groups["groups"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $width = \"45%\" $align = \"left\">\r\n\t\t\t" . $lang->ts_social_groups["name"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $width = \"15%\" $align = \"left\">\r\n\t\t\t" . $lang->ts_social_groups["created"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $width = \"5%\" $align = \"center\">\r\n\t\t\t" . $lang->ts_social_groups["members"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $width = \"5%\" $align = \"center\">\r\n\t\t\t" . $lang->ts_social_groups["messages"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $width = \"15%\" $align = \"left\">\r\n\t\t\t" . $lang->ts_social_groups["lastpost"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $width = \"15%\" $align = \"center\">\r\n\t\t\t" . $lang->ts_social_groups["options"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . ts_collapse("showgroupsss", 2);
if (0 < mysqli_num_rows($allGroupsQuery)) {
    $images = ["public" => $pic_base_url . "public.gif", "inviteonly" => $pic_base_url . "private.gif"];
    while ($groupRow = mysqli_fetch_assoc($allGroupsQuery)) {
        $joinButton = $groupRow["type"] == "public" && SGPermission("canjoin") && $groupRow["owner"] != $CURUSER["id"] && !isset($memberOfGroups[$groupRow["groupid"]]) ? "<input $type = \"button\" $value = \"" . $lang->ts_social_groups["join"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=join&amp;$groupid = " . $groupRow["groupid"] . "'); return false;\" />" : ($groupRow["owner"] != $CURUSER["id"] && isset($memberOfGroups[$groupRow["groupid"]]) && $groupRow["type"] == "public" ? "<input $type = \"button\" $value = \"" . $lang->ts_social_groups["leave"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=leave&amp;$groupid = " . $groupRow["groupid"] . "'); return false;\" />" : "");
        $deleteButton = $is_mod || SGPermission("candelete") && $groupRow["owner"] == $CURUSER["id"] ? "<input $type = \"button\" $value = \"" . $lang->ts_social_groups["delete"] . "\" $onclick = \"ConfirmDeletion(" . $groupRow["groupid"] . ");\" />" : "";
        $editButton = $is_mod || SGPermission("canedit") && $groupRow["owner"] == $CURUSER["id"] ? "<input $type = \"button\" $value = \"" . $lang->ts_social_groups["edit"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=edit&amp;$groupid = " . $groupRow["groupid"] . "'); return false;\" />" : "";
        $groupName = "<span $style = \"float: right;\"><img $src = \"" . $images[$groupRow["type"]] . "\" $border = \"0\" $alt = \"" . $groupRow["type"] . "\" $title = \"" . $groupRow["type"] . "\" /></span> <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=showgroup&amp;$groupid = " . $groupRow["groupid"] . "\"><strong>" . htmlspecialchars_uni($groupRow["name"]) . "</strong></a><br />" . cutename($groupRow["description"], 100);
        $createdDate = my_datee($dateformat, $groupRow["dateline"]) . " " . my_datee($timeformat, $groupRow["dateline"]);
        $membersCount = ts_nf($groupRow["members"]);
        $messagesCount = ts_nf($groupRow["messages"]);
        if ($groupRow["lastpostdate"] != "0" && $groupRow["lastposter"] != "0") {
            $lastPost = "<div $style = \"text-align: left;\">" . my_datee($dateformat, $groupRow["lastpostdate"]) . " " . my_datee($timeformat, $groupRow["lastpostdate"]) . "</div><div $style = \"text-align: right;\">" . sprintf($lang->ts_social_groups["by"], "<a $href = \"" . ts_seo($groupRow["lastposter"], $groupRow["username"]) . "\">" . get_user_color($groupRow["username"], $groupRow["namestyle"]) . "</a>") . "</div>";
        } else {
            $lastPost = $lang->ts_social_groups["never"];
        }
        $pageHtml .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"45%\" $align = \"left\">" . $groupName . "</td>\r\n\t\t\t<td $width = \"15%\" $align = \"left\">" . $createdDate . "</td>\r\n\t\t\t<td $width = \"5%\" $align = \"center\">" . $membersCount . "</td>\r\n\t\t\t<td $width = \"5%\" $align = \"center\">" . $messagesCount . "</td>\r\n\t\t\t<td $width = \"15%\" $align = \"left\">" . $lastPost . "</td>\r\n\t\t\t<td $width = \"15%\" $align = \"center\">" . $joinButton . " " . $editButton . " " . $deleteButton . "</td>\r\n\t\t</tr>\r\n\t\t";
    }
} else {
    $pageHtml .= "\r\n\t<tr>\r\n\t\t<td $colspan = \"6\">\r\n\t\t\t" . $lang->ts_social_groups["nogroup"] . "\r\n\t\t</td>\r\n\t</tr>";
}
$pageHtml .= "</tbody></table>";
echo $pageHtml;
stdfoot();
function SGPermission($Option, $UG = "")
{
    global $usergroups;
    $Work = $usergroups;
    if ($UG) {
        $Work["sgperms"] = $UG;
    }
    $Options = ["canview" => "0", "cancreate" => "1", "canpost" => "2", "candelete" => "3", "canjoin" => "4", "canedit" => "5", "canmanagemsg" => "6", "canmanagegroup" => "7"];
    $What = isset($Options[$Option]) ? $Options[$Option] : 0;
    return $Work["sgperms"][$What] == "1" ? true : false;
}
function show_sg_errors()
{
    global $errors;
    global $lang;
    if (0 < count($errors)) {
        $error = implode("<br />", $errors);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $error . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>