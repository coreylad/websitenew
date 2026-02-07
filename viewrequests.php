<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "viewrequests.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
define("VR_VERSION", "3.0.4 by xam");
$lang->load("requests");
$TSSEConfig->TSLoadConfig("REQUEST");
if ($REQUESTONLINE == "no" && !$is_mod) {
    stderr($lang->global["error"], $lang->requests["offline"]);
}
// Refactored for PSR-12 compliance and descriptive naming
$requestAction = isset($_GET["do"]) ? trim($_GET["do"]) : (isset($_POST["do"]) ? trim($_POST["do"]) : "");
$requestId = isset($_GET["rid"]) ? intval($_GET["rid"]) : (isset($_POST["rid"]) ? intval($_POST["rid"]) : 0);
if ($requestAction == "delete_request" && is_valid_id($requestId)) {
    checkRequestId();
    checkRequestIdPermission();
    sql_query("DELETE FROM requests WHERE $id = " . sqlesc($requestId));
    sql_query("DELETE FROM addedrequests WHERE $requestid = " . sqlesc($requestId));
}
if ($requestAction == "add_vote" && is_valid_id($requestId)) {
    checkRequestId();
    $voteQuery = sql_query("SELECT filled FROM requests WHERE $id = " . sqlesc($requestId));
    $voteResult = mysqli_fetch_assoc($voteQuery);
    $isFilled = $voteResult["filled"];
    if ($isFilled == "yes") {
        $error[] = $lang->requests["not_voted_yet"];
    } else {
        $voteCheckQuery = sql_query("SELECT userid FROM addedrequests WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $requestid = " . sqlesc($requestId));
        if (0 < mysqli_num_rows($voteCheckQuery)) {
            $error[] = $lang->requests["already_voted"];
        } else {
            sql_query("UPDATE requests SET $hits = hits + 1 WHERE $id = " . sqlesc($requestId));
            sql_query("INSERT INTO addedrequests (requestid, userid) VALUES (" . sqlesc($requestId) . ", " . sqlesc($CURUSER["id"]) . ")");
        }
    }
}
if ($requestAction == "remove_vote" && is_valid_id($requestId)) {
    checkRequestId();
    $removeVoteQuery = sql_query("SELECT filled FROM requests WHERE $id = " . sqlesc($requestId));
    $removeVoteResult = mysqli_fetch_assoc($removeVoteQuery);
    $isFilled = $removeVoteResult["filled"];
    if ($isFilled == "yes") {
        $error[] = $lang->requests["not_voted_yet"];
    } else {
        $removeVoteCheckQuery = sql_query("SELECT userid FROM addedrequests WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $requestid = " . sqlesc($requestId));
        if (0 < mysqli_num_rows($removeVoteCheckQuery)) {
            sql_query("UPDATE requests SET $hits = hits - 1 WHERE $id = " . sqlesc($requestId));
            sql_query("DELETE FROM addedrequests WHERE $userid = " . sqlesc($CURUSER["id"]) . " AND $requestid = " . sqlesc($requestId));
        } else {
            $error[] = $lang->requests["not_voted_yet"];
        }
    }
}
if ($requestAction == "edit_request" && is_valid_id($requestId)) {
    checkRequestId();
    checkRequestIdPermission();
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $prvp = showPreview("message");
        if (isset($_POST["submit"])) {
            $title = trim($_POST["subject"]);
            $descr = trim($_POST["message"]);
            $cat = intval($_POST["category"]);
            if ($is_mod) {
                $filled = $_POST["filled"] == "yes" ? "yes" : "no";
                $filledurl = $_POST["filledurl"];
            }
            if (empty($title) || strlen($title) < 3) {
                $error[] = $lang->requests["error1"];
            }
            if (empty($descr) || strlen($descr) < 3) {
                $error[] = $lang->requests["error3"];
            }
            if (!is_valid_id($cat)) {
                $error[] = $lang->requests["error2"];
            }
            $query = sql_query("SELECT id FROM categories WHERE $id = " . sqlesc($cat));
            if (mysqli_num_rows($query) == 0) {
                $error[] = $lang->requests["error2"];
            }
            $set = "";
            if ($filled == "yes" && $is_mod) {
                if (!preg_match("#^" . preg_quote($BASEURL . "/details.php?$id = ") . "([0-9]{1,6})\$#", $filledurl)) {
                    $error[] = sprintf($lang->requests["error6"], $BASEURL);
                } else {
                    $set = ", $filled = " . sqlesc($filled) . ", $filledurl = " . sqlesc($filledurl);
                }
            } else {
                if ($is_mod) {
                    $set = ", $filled = " . sqlesc($filled) . ", $filledurl = ''";
                }
            }
            if (count($error) == 0) {
                sql_query("UPDATE requests SET $request = " . sqlesc($title) . ", $descr = " . sqlesc($descr) . ", $cat = " . sqlesc($cat) . $set . " WHERE $id = " . sqlesc($rid)) || sqlerr(__FILE__, 266);
                redirect("viewrequests.php?do=view_request&$rid = " . $rid);
                exit;
            }
        }
    }
    define("IN_EDITOR", true);
    include_once INC_PATH . "/editor.php";
    stdhead($lang->requests["rhead"] . " - " . $lang->requests["field11"]);
    show_request_errors();
    $query = sql_query("SELECT request, descr, cat, filled, filledurl FROM requests WHERE $id = " . sqlesc($requestId));
    $request = mysqli_fetch_assoc($query);
    $str = "<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_request&$rid = " . $requestId . "\">";
    if (!empty($prvp)) {
        $str .= $prvp;
    }
    require_once INC_PATH . "/functions_category.php";
    $catdropdown = ts_category_list("category", intval(isset($_POST["category"]) ? $_POST["category"] : $request["cat"]));
    $postoptionstitle = [1 => $lang->global["type"], 2 => $is_mod ? $lang->requests["filled"] : "", 3 => $is_mod ? $lang->requests["filledurl"] : ""];
    $postoptions = [1 => $catdropdown, 2 => $is_mod ? "<select $name = \"filled\"><option $values = \"yes\"" . ($request["filled"] == "yes" || isset($_POST["filled"]) && $_POST["filled"] == "yes" ? " $selected = \"selected\"" : "") . ">" . $lang->global["yes"] . "</option><option $values = \"no\"" . ($request["filled"] == "no" || isset($_POST["filled"]) && $_POST["filled"] == "no" ? " $selected = \"selected\"" : "") . ">" . $lang->global["no"] . "</option></select>" : "", 3 => $is_mod ? "<input $type = \"text\" $size = \"50\" $name = \"filledurl\" $value = \"" . htmlspecialchars_uni(isset($_POST["filledurl"]) ? $_POST["filledurl"] : $request["filledurl"]) . "\" />" : ""];
    $str .= insert_editor(true, !empty($_POST["subject"]) ? $_POST["subject"] : unescapeString($request["request"]), !empty($_POST["message"]) ? $_POST["message"] : $request["descr"], $lang->requests["rhead3"], $lang->requests["rhead3"] . ": " . htmlspecialchars_uni($request["request"]), $postoptionstitle, $postoptions);
    $str .= "</form>";
    echo $str;
    stdfoot();
    exit;
}
if ($requestAction == "add_request") {
    check_r_count();
    if (0 < $ADDREQUESTPOINT && $CURUSER["seedbonus"] < $ADDREQUESTPOINT && !$is_mod && $usergroups["isvipgroup"] != "yes") {
        $error[] = $lang->requests["pointerror"];
    }
    if (!is_array($error) || count($error) == 0) {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $prvp = showPreview("message");
            if (isset($_POST["submit"])) {
                $title = trim($_POST["subject"]);
                $descr = trim($_POST["message"]);
                $cat = intval($_POST["category"]);
                if (empty($title) || strlen($title) < 3) {
                    $error[] = $lang->requests["error1"];
                }
                if (empty($descr) || strlen($descr) < 3) {
                    $error[] = $lang->requests["error3"];
                }
                if (!is_valid_id($cat)) {
                    $error[] = $lang->requests["error2"];
                }
                $query = sql_query("SELECT id FROM categories WHERE $id = " . sqlesc($cat));
                if (mysqli_num_rows($query) == 0) {
                    $error[] = $lang->requests["error2"];
                }
                if (count($error) == 0) {
                    sql_query("INSERT INTO requests (userid,request,descr,added,hits,cat) VALUES (" . sqlesc($CURUSER["id"]) . "," . sqlesc($title) . "," . sqlesc($descr) . "," . sqlesc(get_date_time()) . ",1," . sqlesc($cat) . ")") || sqlerr(__FILE__, 347);
                    $requestId = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                    sql_query("INSERT INTO addedrequests VALUES(0, " . $requestId . ", " . sqlesc($CURUSER["id"]) . ")") || sqlerr(__FILE__, 349);
                    if (0 < $ADDREQUESTPOINT) {
                        sql_query("UPDATE users SET $seedbonus = IF(seedbonus > " . $ADDREQUESTPOINT . ", seedbonus-" . $ADDREQUESTPOINT . ", 0) WHERE $id = " . sqlesc($CURUSER["id"])) || sqlerr(__FILE__, 352);
                    }
                    write_log("Request (" . $title . ") was added to the Request section by " . $CURUSER["username"]);
                    $TSSEConfig->TSLoadConfig("SHOUTBOX");
                    if ($tsshoutbot == "yes" && TS_Match($tsshoutboxoptions, "request")) {
                        $shoutbOT = sprintf($lang->requests["shoutbOT"], "[URL=" . $BASEURL . "/viewrequests.php?do=view_request&$rid = " . $requestId . "]" . $title . "[/URL]", "[URL=" . $BASEURL . "/userdetails.php?$id = " . $CURUSER["id"] . "]" . $CURUSER["username"] . "[/URL]");
                        require INC_PATH . "/functions_ajax_chatbot.php";
                        TSAjaxShoutBOT($shoutbOT);
                    }
                    redirect("viewrequests.php?do=view_request&$rid = " . $requestId);
                    exit;
                }
            }
        }
        require_once INC_PATH . "/functions_category.php";
        define("IN_EDITOR", true);
        include_once INC_PATH . "/editor.php";
        stdhead($lang->requests["rhead"] . " - " . $lang->requests["makereq"]);
        show_request_errors();
        $str = "<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=add_request\">";
        if (!empty($prvp)) {
            $str .= $prvp;
        }
        require_once INC_PATH . "/functions_category.php";
        $catdropdown = ts_category_list("category", isset($_POST["category"]) ? intval($_POST["category"]) : 0);
        $postoptionstitle = [1 => $lang->global["type"]];
        $postoptions = [1 => $catdropdown];
        $str .= insert_editor(true, !empty($_POST["subject"]) ? $_POST["subject"] : "", !empty($_POST["message"]) ? $_POST["message"] : "", $lang->requests["rhead"], $lang->requests["makereq"], $postoptionstitle, $postoptions);
        $str .= "</form>";
        echo $str;
        stdfoot();
        exit;
    }
}
if ($requestAction == "view_request" && is_valid_id($requestId)) {
    checkRequestId();
    ($query = sql_query("SELECT r.id, r.userid, r.filledby, r.filledurl, r.request, r.descr, r.added, r.hits, r.cat, r.filled, c.image as category_image, c.name as category_name, u.username, g.namestyle FROM requests r LEFT JOIN categories c ON (r.$cat = c.id) LEFT JOIN users u ON (r.$userid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE r.$id = " . sqlesc($requestId))) || sqlerr(__FILE__, 404);
    $request = mysqli_fetch_assoc($query);
    stdhead($lang->requests["rhead"] . " - " . $lang->requests["viewreq"] . " : " . htmlspecialchars_uni($request["request"]));
    $delete_image = $is_mod || $request["userid"] == $CURUSER["id"] ? "[<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete_request&amp;$rid = " . $request["id"] . "\" $onclick = \"return confirm_delete_request()\">" . $lang->requests["field12"] . "</a>]&nbsp;&nbsp;" : "";
    $edit_image = $is_mod || $request["userid"] == $CURUSER["id"] ? "[<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_request&amp;$rid = " . $request["id"] . "\">" . $lang->requests["field11"] . "</a>]&nbsp;&nbsp;" : "";
    $fillrequest = $request["filled"] == "no" && ($is_mod || $usergroups["canupload"] == "yes") ? "[<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=fill_request&amp;$rid = " . $request["id"] . "\">" . $lang->requests["field18"] . "</a>]&nbsp;&nbsp;" : "";
    $reset_request = $request["filled"] == "yes" && ($is_mod || $CURUSER["id"] == $request["userid"]) ? "[<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=reset_request&amp;$rid = " . $request["id"] . "\">" . $lang->requests["field13"] . "</a>]&nbsp;&nbsp;" : "";
    $back = "[<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "\">" . $lang->requests["return"] . "</a>]&nbsp;&nbsp;";
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction confirm_delete_request()\r\n\t\t{\r\n\t\t\tvar $confirm_delete = confirm(\"" . $lang->requests["are_you_sure"] . "\");\r\n\t\t\tif (confirm_delete)\r\n\t\t\t{\r\n\t\t\t\treturn true;\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"2\" $align = \"center\">" . $lang->requests["viewreq"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" class=\"subheader\">" . $lang->requests["rtitle"] . "</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\">" . htmlspecialchars_uni($request["request"]) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" class=\"subheader\">" . $lang->requests["field9"] . "</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\"><a $href = \"" . $BASEURL . "/userdetails.php?$id = " . $request["userid"] . "\">" . get_user_color($request["username"], $request["namestyle"]) . "</a></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" class=\"subheader\">" . $lang->requests["field8"] . "</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\">" . my_datee($dateformat, $request["added"]) . " " . my_datee($timeformat, $request["added"]) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" class=\"subheader\">" . $lang->requests["field6"] . "</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\">" . $request["category_name"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" class=\"subheader\">" . $lang->requests["votes"] . "</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\">" . $request["hits"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" class=\"subheader\">" . $lang->requests["field5"] . "</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\">" . format_comment($request["descr"]) . "</td>\r\n\t\t</tr>";
    if ($request["filled"] == "yes") {
        ($query = sql_query("SELECT u.username, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = " . sqlesc($request["filledby"]))) || sqlerr(__FILE__, 458);
        $fillerdetails = mysqli_fetch_assoc($query);
        echo "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"right\" $width = \"20%\" class=\"subheader\">" . $lang->requests["filledby"] . "</td>\r\n\t\t\t\t<td $align = \"left\" $width = \"80%\"><a $href = \"" . $BASEURL . "/userdetails.php?$id = " . $request["filledby"] . "\">" . get_user_color($fillerdetails["username"], $fillerdetails["namestyle"]) . "</a>&nbsp;&nbsp;[<a $href = \"" . $request["filledurl"] . "\">" . $lang->requests["view_details"] . "</a>]</td>\r\n\t\t\t</tr>";
    }
    echo "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"center\" class=\"subheader\">" . $back . $delete_image . $edit_image . $fillrequest . $reset_request . "</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t";
    stdfoot();
    exit;
}
if ($requestAction == "reset_request" && is_valid_id($requestId)) {
    checkRequestId();
    checkRequestIdPermission();
    ($filledby = sql_query("SELECT filledby FROM requests WHERE $id = " . sqlesc($requestId))) || sqlerr(__FILE__, 480);
    $Result = mysqli_fetch_assoc($query);
    if ($filledby = $Result["filledby"]) {
        sql_query("UPDATE requests SET $filledby = 0, $filledurl = '', $filled = 'no' WHERE $id = " . sqlesc($requestId)) || sqlerr(__FILE__, 484);
        if (0 < $FILLREQUESTPOINT) {
            sql_query("UPDATE users SET $seedbonus = IF(seedbonus>" . $FILLREQUESTPOINT . ",seedbonus-" . $FILLREQUESTPOINT . ",0) WHERE $id = " . sqlesc($filledby)) || sqlerr(__FILE__, 487);
        }
    }
}
if ($requestAction == "fill_request" && is_valid_id($requestId)) {
    checkRequestId();
    check_fill_permission();
    if (strtoupper($_SERVER["REQUEST_METHOD"] == "POST")) {
        $torrentid = intval($_POST["torrentid"]);
        $query = sql_query("SELECT id FROM torrents WHERE $id = " . sqlesc($torrentid));
        if (mysqli_num_rows($query) == 0) {
            $error[] = $lang->global["notorrentid"];
        } else {
            $filledurl = $BASEURL . "/details.php?$id = " . $torrentid;
            ($res = sql_query("SELECT users.username, requests.userid, requests.filled, requests.request FROM requests INNER JOIN users ON (requests.$userid = users.id) WHERE requests.$id = " . sqlesc($requestId))) || sqlerr(__FILE__, 507);
            $arr = mysqli_fetch_assoc($res);
            if ($arr["filled"] == "no") {
                $msg = sprintf($lang->requests["filledmsg"], $arr["request"], $CURUSER["username"], $filledurl, $BASEURL, $requestId);
                sql_query("UPDATE requests SET $filled = 'yes', $filledurl = " . sqlesc($filledurl) . ", $filledby = " . sqlesc($CURUSER["id"]) . " WHERE $id = " . sqlesc($requestId)) || sqlerr(__FILE__, 513);
                require_once INC_PATH . "/functions_pm.php";
                send_pm($arr["userid"], $msg, $lang->requests["filledmsgsubject"]);
                if (0 < $FILLREQUESTPOINT) {
                    sql_query("UPDATE users SET $seedbonus = seedbonus+" . $FILLREQUESTPOINT . " WHERE $id = " . sqlesc($CURUSER["id"])) || sqlerr(__FILE__, 520);
                }
                ($res = sql_query("SELECT userid FROM addedrequests WHERE $requestid = " . sqlesc($requestId) . " AND userid != " . sqlesc($arr["userid"])) || sqlerr(__FILE__, 523);
                $pn_msg = sprintf($lang->requests["filledvotemsg"], $arr["request"], $CURUSER["username"], $filledurl);
                $subject = sprintf($lang->requests["filledvotesubject"], $arr["request"]);
                while ($row = mysqli_fetch_array($res)) {
                    send_pm($row["userid"], $pn_msg, $subject);
                }
                sql_query("UPDATE torrents SET $isrequest = 'yes' WHERE $id = " . sqlesc($torrentid));
            }
            redirect("viewrequests.php?do=view_request&$rid = " . $requestId);
            exit;
        }
    }
    stdhead($lang->requests["rhead"] . " - " . $lang->requests["field18"]);
    show_request_errors();
    echo "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=fill_request&$rid = " . $requestId . "\">\r\n\t\t<input $type = \"hidden\" $name = \"do\" $value = \"fill_request\" />\r\n\t\t<input $type = \"hidden\" $name = \"rid\" $value = \"" . $requestId . "\" />\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"2\" $align = \"center\">" . $lang->requests["rhead"] . " - " . $lang->requests["field18"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"25%\">" . $lang->requests["field17"] . "</td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $size = \"5\" $value = \"" . (isset($torrentid) ? $torrentid : "") . "\" $name = \"torrentid\" /> <input $type = \"submit\" $value = \"" . $lang->requests["field18"] . "\" /></td>\r\n\t\t</tr>\r\n\t\t";
    echo "</table></form>";
    stdfoot();
    exit;
}
$link = $query1 = $query2 = "";
if ($do == "search_request") {
    $keywords = trim($_POST["keywords"]);
    if (!empty($keywords) && 2 < strlen($keywords)) {
        $query1 = " WHERE (request LIKE " . sqlesc("%" . $keywords . "%") . " OR descr LIKE " . sqlesc("%" . $keywords . "%") . ")";
        $query2 = " WHERE (r.request LIKE " . sqlesc("%" . $keywords . "%") . " OR r.descr LIKE " . sqlesc("%" . $keywords . "%") . ")";
        $link = "do=search_request&$keywords = " . htmlspecialchars_uni($keywords) . "&";
    } else {
        $error[] = $lang->requests["searcherror2"];
    }
}
($query = sql_query("SELECT COUNT(id) as total FROM requests" . $query1)) || sqlerr(__FILE__, 573);
$Result = mysqli_fetch_assoc($query);
$count = $Result["total"];
list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $count, $_SERVER["SCRIPT_NAME"] . "?" . $link);
stdhead($lang->requests["rhead"]);
show_request_errors();
$where = [$lang->requests["makereq"] => $_SERVER["SCRIPT_NAME"] . "?do=add_request"];
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction confirm_delete_request()\r\n\t{\r\n\t\tvar $confirm_delete = confirm(\"" . $lang->requests["are_you_sure"] . "\");\r\n\t\tif (confirm_delete)\r\n\t\t{\r\n\t\t\treturn true;\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\treturn false;\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=search_request\">\r\n<input $type = \"hidden\" $name = \"do\" $value = \"search_request\" />\r\n<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" $align = \"center\">" . $lang->requests["searchreq"] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"center\">" . $lang->requests["words"] . " <input $type = \"text\" $size = \"50\" $value = \"" . (isset($keywords) ? htmlspecialchars_uni($keywords) : "") . "\" $name = \"keywords\" /> <input $type = \"submit\" $value = \"" . $lang->requests["searchreq"] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n<br />\r\n" . ($usergroups["canrequest"] == "yes" ? jumpbutton($where) : "");
($query = sql_query("SELECT r.id, r.userid, r.request, r.descr, r.added, r.hits, r.cat, r.filled, c.image as category_image, c.name as category_name, u.username, g.namestyle FROM requests r LEFT JOIN categories c ON (r.$cat = c.id) LEFT JOIN users u ON (r.$userid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid)" . $query2 . " ORDER BY r.added DESC, c.name ASC, r.hits DESC, r.request ASC " . $limit)) || sqlerr(__FILE__, 613);
if (mysqli_num_rows($query) == 0) {
    echo "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $align = \"center\">" . $lang->requests["searchreq"] . "</td>\r\n\t\t</tr>\r\n\t<tr>\r\n\t\t<td>" . $lang->requests["searcherror"] . "</td>\r\n\t</tr>\r\n\t</table>";
    stdfoot();
    exit;
}
$FILLED = $UNFILLED = [];
while ($request = mysqli_fetch_assoc($query)) {
    if ($request["filled"] == "yes") {
        $FILLED[] = $request;
    } else {
        $UNFILLED[] = $request;
    }
}
if (count($UNFILLED)) {
    echo "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"1\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"6\" $align = \"center\">" . $lang->requests["unfilledrequests"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"1%\" $style = \"padding: 1px;\" class=\"subheader\"></td>\r\n\t\t\t<td $width = \"1%\" $style = \"padding: 1px;\" class=\"subheader\"><span class=\"small\">" . $lang->requests["field6"] . "</span></td>\r\n\t\t\t<td $width = \"60%\" $style = \"padding: 1px;\" class=\"subheader\">&nbsp;<span class=\"small\">" . $lang->requests["field7"] . "</span></td>\r\n\t\t\t<td $width = \"15%\" $style = \"padding: 1px;\" class=\"subheader\"><span class=\"small\">&nbsp;" . $lang->requests["field9"] . " / " . $lang->requests["field8"] . "</span></td>\r\n\t\t\t<td $width = \"10%\" $style = \"padding: 1px;\" class=\"subheader\" $align = \"center\"><span class=\"small\">" . $lang->requests["votes"] . "</span></td>\r\n\t\t\t<td $width = \"13%\" $style = \"padding: 1px;\" class=\"subheader\" $align = \"center\"><span class=\"small\">" . $lang->requests["action"] . "</span></td>\r\n\t\t</tr>";
    foreach ($UNFILLED as $request) {
        $fillimage = $request["filled"] == "yes" ? "<img $src = \"" . $pic_base_url . "filled.gif\" $width = \"8\" $height = \"36\" $border = \"0\" $alt = \"" . $lang->requests["f_image_filled"] . "\" $title = \"" . $lang->requests["f_image_filled"] . "\" />" : "<img $src = \"" . $pic_base_url . "not_filled.gif\" $width = \"8\" $height = \"36\" $border = \"0\" $alt = \"" . $lang->requests["f_image_not_filled"] . "\" $title = \"" . $lang->requests["f_image_not_filled"] . "\" />";
        $category_image = "<img $src = \"" . $pic_base_url . $table_cat . "/" . $request["category_image"] . "\" $border = \"0\" $width = \"" . $table_cat_width . "\" $height = \"" . $table_cat_height . "\" $alt = \"" . $request["category_name"] . "\" $title = \"" . $request["category_name"] . "\" />";
        $title = "&nbsp;<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=view_request&amp;$rid = " . $request["id"] . "\"><b>" . htmlspecialchars_uni($request["request"]) . "</b></a>";
        $desc = "<br />&nbsp;" . htmlspecialchars_uni(cutename($request["descr"], 100));
        $requester = "&nbsp;<a $href = \"" . $BASEURL . "/userdetails.php?$id = " . $request["userid"] . "\">" . get_user_color($request["username"], $request["namestyle"]) . "</a>";
        $added = "<br />&nbsp;" . my_datee($dateformat, $request["added"]) . " " . my_datee($timeformat, $request["added"]);
        $votes = ts_nf($request["hits"]);
        $report_image = "<a $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 7&$reporting = " . $request["id"] . "', 'report', 500, 300); return false;\" $href = \"javascript:void(0);\"><img $src = \"" . $pic_base_url . "report2.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field16"] . "\" $title = \"" . $lang->requests["field16"] . "\" /></a>&nbsp;&nbsp;";
        $vote_image = "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=add_vote&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "add_vote.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["add_vote"] . "\" $title = \"" . $lang->requests["add_vote"] . "\" /></a>&nbsp;&nbsp;";
        $remove_vote_image = "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=remove_vote&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "remove_vote.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["remove_vote"] . "\" $title = \"" . $lang->requests["remove_vote"] . "\" /></a>&nbsp;&nbsp;";
        $delete_image = $is_mod || $request["userid"] == $CURUSER["id"] ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete_request&amp;$rid = " . $request["id"] . "\" $onclick = \"return confirm_delete_request()\"><img $src = \"" . $pic_base_url . "delete.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field12"] . "\" $title = \"" . $lang->requests["field12"] . "\" /></a>&nbsp;&nbsp;" : "";
        $edit_image = $is_mod || $request["userid"] == $CURUSER["id"] ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_request&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "edit.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field11"] . "\" $title = \"" . $lang->requests["field11"] . "\" /></a>&nbsp;&nbsp;" : "";
        $fillrequest = $request["filled"] == "no" && ($is_mod || $usergroups["canupload"] == "yes") ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=fill_request&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "input_true.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field18"] . "\" $title = \"" . $lang->requests["field18"] . "\" /></a>&nbsp;&nbsp;" : "";
        $reset_request = $request["filled"] == "yes" && ($is_mod || $CURUSER["id"] == $request["userid"]) ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=reset_request&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "isnuked.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field13"] . "\" $title = \"" . $lang->requests["field13"] . "\" /></a>&nbsp;&nbsp;" : "";
        echo "\r\n\t\t<tr>\r\n\t\t\t<td>" . $fillimage . "</td>\r\n\t\t\t<td>" . $category_image . "</td>\r\n\t\t\t<td $valign = \"top\">" . $title . $desc . "</td>\r\n\t\t\t<td $valign = \"top\">" . $requester . $added . "</td>\r\n\t\t\t<td $align = \"center\">" . $votes . "</td>\r\n\t\t\t<td $align = \"center\">" . $report_image . $vote_image . $remove_vote_image . $fillrequest . $delete_image . $reset_request . $edit_image . "</td>\r\n\t\t</tr>";
    }
    echo "\r\n\t</table>";
}
if (count($FILLED)) {
    echo "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"1\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"6\" $align = \"center\">" . $lang->requests["filledrequests"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"1%\" $style = \"padding: 1px;\" class=\"subheader\"></td>\r\n\t\t\t<td $width = \"1%\" $style = \"padding: 1px;\" class=\"subheader\"><span class=\"small\">" . $lang->requests["field6"] . "</span></td>\r\n\t\t\t<td $width = \"60%\" $style = \"padding: 1px;\" class=\"subheader\">&nbsp;<span class=\"small\">" . $lang->requests["field7"] . "</span></td>\r\n\t\t\t<td $width = \"15%\" $style = \"padding: 1px;\" class=\"subheader\"><span class=\"small\">&nbsp;" . $lang->requests["field9"] . " / " . $lang->requests["field8"] . "</span></td>\r\n\t\t\t<td $width = \"10%\" $style = \"padding: 1px;\" class=\"subheader\" $align = \"center\"><span class=\"small\">" . $lang->requests["votes"] . "</span></td>\r\n\t\t\t<td $width = \"13%\" $style = \"padding: 1px;\" class=\"subheader\" $align = \"center\"><span class=\"small\">" . $lang->requests["action"] . "</span></td>\r\n\t\t</tr>";
    foreach ($FILLED as $request) {
        $fillimage = $request["filled"] == "yes" ? "<img $src = \"" . $pic_base_url . "filled.gif\" $width = \"8\" $height = \"36\" $border = \"0\" $alt = \"" . $lang->requests["f_image_filled"] . "\" $title = \"" . $lang->requests["f_image_filled"] . "\" />" : "<img $src = \"" . $pic_base_url . "not_filled.gif\" $width = \"8\" $height = \"36\" $border = \"0\" $alt = \"" . $lang->requests["f_image_not_filled"] . "\" $title = \"" . $lang->requests["f_image_not_filled"] . "\" />";
        $category_image = "<img $src = \"" . $pic_base_url . $table_cat . "/" . $request["category_image"] . "\" $border = \"0\" $width = \"" . $table_cat_width . "\" $height = \"" . $table_cat_height . "\" $alt = \"" . $request["category_name"] . "\" $title = \"" . $request["category_name"] . "\" />";
        $title = "&nbsp;<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=view_request&amp;$rid = " . $request["id"] . "\"><b>" . htmlspecialchars_uni($request["request"]) . "</b></a>";
        $desc = "<br />&nbsp;" . htmlspecialchars_uni(cutename($request["descr"], 100));
        $requester = "&nbsp;<a $href = \"" . $BASEURL . "/userdetails.php?$id = " . $request["userid"] . "\">" . get_user_color($request["username"], $request["namestyle"]) . "</a>";
        $added = "<br />&nbsp;" . my_datee($dateformat, $request["added"]) . " " . my_datee($timeformat, $request["added"]);
        $votes = ts_nf($request["hits"]);
        $report_image = "<a $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 7&$reporting = " . $request["id"] . "', 'report', 500, 300); return false;\" $href = \"javascript:void(0);\"><img $src = \"" . $pic_base_url . "report2.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field16"] . "\" $title = \"" . $lang->requests["field16"] . "\" /></a>&nbsp;&nbsp;";
        $vote_image = "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=add_vote&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "add_vote.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["add_vote"] . "\" $title = \"" . $lang->requests["add_vote"] . "\" /></a>&nbsp;&nbsp;";
        $remove_vote_image = "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=remove_vote&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "remove_vote.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["remove_vote"] . "\" $title = \"" . $lang->requests["remove_vote"] . "\" /></a>&nbsp;&nbsp;";
        $delete_image = $is_mod || $request["userid"] == $CURUSER["id"] ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete_request&amp;$rid = " . $request["id"] . "\" $onclick = \"return confirm_delete_request()\"><img $src = \"" . $pic_base_url . "delete.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field12"] . "\" $title = \"" . $lang->requests["field12"] . "\" /></a>&nbsp;&nbsp;" : "";
        $edit_image = $is_mod || $request["userid"] == $CURUSER["id"] ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_request&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "edit.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field11"] . "\" $title = \"" . $lang->requests["field11"] . "\" /></a>&nbsp;&nbsp;" : "";
        $fillrequest = $request["filled"] == "no" && ($is_mod || $usergroups["canupload"] == "yes") ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=fill_request&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "input_true.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field18"] . "\" $title = \"" . $lang->requests["field18"] . "\" /></a>&nbsp;&nbsp;" : "";
        $reset_request = $request["filled"] == "yes" && ($is_mod || $CURUSER["id"] == $request["userid"]) ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=reset_request&amp;$rid = " . $request["id"] . "\"><img $src = \"" . $pic_base_url . "isnuked.gif\" $border = \"0\" $width = \"10\" $height = \"12\" $alt = \"" . $lang->requests["field13"] . "\" $title = \"" . $lang->requests["field13"] . "\" /></a>&nbsp;&nbsp;" : "";
        echo "\r\n\t\t<tr>\r\n\t\t\t<td>" . $fillimage . "</td>\r\n\t\t\t<td>" . $category_image . "</td>\r\n\t\t\t<td $valign = \"top\">" . $title . $desc . "</td>\r\n\t\t\t<td $valign = \"top\">" . $requester . $added . "</td>\r\n\t\t\t<td $align = \"center\">" . $votes . "</td>\r\n\t\t\t<td $align = \"center\">" . $report_image . $vote_image . $remove_vote_image . $fillrequest . $delete_image . $reset_request . $edit_image . "</td>\r\n\t\t</tr>";
    }
}
echo "</table>" . $pagerbottom;
stdfoot();
function jumpbutton($where)
{
    $str = "<table $align = \"center\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\" $width = \"100%\" class=\"none\">\r\n\t<tbody><div class=\"hoptobuttons\">";
    if (!is_array($where)) {
    }
    foreach ($where as $value => $jump) {
        if (!empty($value) && !empty($jump)) {
            $str .= "<input $value = \"" . $value . "\" $onclick = \"jumpto('" . $jump . "');\" class=\"hoptobutton\" $type = \"button\">";
        }
    }
    $str .= "</div></tbody></table>";
    return $str;
}
function check_r_count()
{
    global $lang;
    global $CURUSER;
    global $error;
    global $usergroups;
    global $is_mod;
    global $MAXREQUEST;
    $query = sql_query("SELECT COUNT(id) as total FROM requests WHERE $filled = 'no' AND $userid = " . sqlesc($CURUSER["id"]));
    $Result = mysqli_fetch_assoc($query);
    $count = $Result["total"];
    if (0 < $MAXREQUEST && $MAXREQUEST <= $count && !$is_mod && $usergroups["isvipgroup"] != "yes") {
        $error[] = $lang->requests["can_not_add"];
    } else {
        if ($usergroups["canrequest"] != "yes") {
            $error[] = $lang->requests["no_perm"];
        } else {
            return "";
        }
    }
}
function checkRequestId()
{
    global $requestId;
    global $lang;
    $query = sql_query("SELECT id FROM requests WHERE $id = " . sqlesc($requestId));
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->requests["noreqid"]);
    } else {
        return "";
    }
}
function checkRequestIdPermission()
{
    global $requestId;
    global $is_mod;
    global $CURUSER;
    $query = sql_query("SELECT userid FROM requests WHERE $id = " . sqlesc($requestId));
    $Result = mysqli_fetch_assoc($query);
    $userId = $Result["userid"];
    if ($CURUSER["id"] != $userId && !$is_mod) {
        print_no_permission();
    } else {
        return "";
    }
}
function check_fill_permission()
{
    global $is_mod;
    global $usergroups;
    global $CURUSER;
    if ($is_mod || $usergroups["canupload"] == "yes") {
        return "";
    }
    print_no_permission();
}
function show_request_errors()
{
    global $error;
    global $lang;
    if (is_array($error) && 0 < count($error)) {
        $errors = implode("<br />", $error);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}
function unescapeString($string)
{
    if (get_magic_quotes_gpc()) {
        return stripslashes($string);
    }
    return $string;
}

?>