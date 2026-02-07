<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "dj.php");
$rootpath = "./../";
require $rootpath . "global.php";
$lang->load("shoutcast");
$TSSEConfig->TSLoadConfig("SHOUTCAST");
if (($s_allowedusergroups = explode(",", $s_allowedusergroups)) && !in_array($CURUSER["usergroup"], $s_allowedusergroups)) {
    print_no_permission();
}
if ($_GET["do"] == "manage") {
    ($djQuery = sql_query("SELECT activedays, activetime, genre FROM ts_shoutcastdj WHERE active = '1' AND uid = '" . $CURUSER["id"] . "'")) || sqlerr(__FILE__, 35);
    if (mysqli_num_rows($djQuery) == 0) {
        print_no_permission(true);
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $availableDays = ["1" => "Mon", "2" => "Tue", "3" => "Wed", "4" => "Thu", "5" => "Fri", "6" => "Sat", "7" => "Sun"];
        $activeDays = $_POST["activedays"];
        $activeTime = trim($_POST["activetime"]);
        $genre = trim($_POST["genre"]);
        if (is_array($activeDays) && count($activeDays) && 5 < strlen($activeTime) && 2 < strlen($genre)) {
            $selectedActiveDays = [];
            foreach ($activeDays as $activeDay) {
                if ($availableDays[$activeDay]) {
                    $selectedActiveDays[] = $availableDays[$activeDay];
                }
            }
            if (count($selectedActiveDays)) {
                $activeDays = implode(",", $selectedActiveDays);
                sql_query("UPDATE ts_shoutcastdj SET activedays = " . sqlesc($activeDays) . ", activetime = " . sqlesc($activeTime) . ", genre = " . sqlesc($genre) . " WHERE active = '1' AND uid = '" . $CURUSER["id"] . "'") || sqlerr(__FILE__, 60);
                redirect("shoutcast/index.php");
                exit;
            }
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        } else {
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        }
    }
    $djDataRow = mysqli_fetch_assoc($djQuery);
    stdhead($lang->shoutcast["bedj"]);
    $availableDaysList = explode(",", $lang->shoutcast["days"]);
    $daysCheckboxesHtml = "";
    for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
        $daysCheckboxesHtml .= "\r\n\t\t<input type=\"checkbox\" value=\"" . ($dayIndex + 1) . "\" name=\"activedays[]\"" . (in_array(substr($availableDaysList[$dayIndex], 0, 3), explode(",", $djDataRow["activedays"])) ? " checked=\"checked\"" : "") . " /> " . $availableDaysList[$dayIndex] . " ";
    }
    echo "\r\n\t<form method=\"POST\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?do=manage\">\r\n\t<table width=\"100%\" align=\"center\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">" . $lang->shoutcast["bedj"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td align=\"left\">\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . sprintf($lang->shoutcast["f1"], $SITENAME) . "</legend>\r\n\t\t\t\t\t" . $daysCheckboxesHtml . "\r\n\t\t\t\t\t<div style=\"padding-top:10px;\">\r\n\t\t\t\t\t\t<b>" . $lang->shoutcast["f2"] . "</b> <input type=\"text\" name=\"activetime\" value=\"" . htmlspecialchars_uni($djDataRow["activetime"]) . "\" /> <b>" . $lang->shoutcast["example"] . "</b>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $lang->shoutcast["f5"] . "</legend>\r\n\t\t\t\t\t\t<input type=\"text\" name=\"genre\" value=\"" . htmlspecialchars_uni($djDataRow["genre"]) . "\" size=\"50\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td align=\"center\" class=\"subheader\">\r\n\t\t\t\t<input type=\"submit\" value=\"" . $lang->shoutcast["f3"] . "\" /> <input type=\"reset\" value=\"" . $lang->shoutcast["f4"] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
    stdfoot();
    exit;
}
if ($_GET["do"] == "edit" && is_valid_id($_GET["id"]) && $is_mod) {
    $Updated = false;
    ($Query = sql_query("SELECT * FROM ts_shoutcastdj WHERE id = '" . (0 + $_GET["id"]) . "'")) || sqlerr(__FILE__, 120);
    if (0 < mysqli_num_rows($Query)) {
        $Updated = false;
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $availabledays = ["1" => "Mon", "2" => "Tue", "3" => "Wed", "4" => "Thu", "5" => "Fri", "6" => "Sat", "7" => "Sun"];
            $activedays = $_POST["activedays"];
            $activetime = trim($_POST["activetime"]);
            $genre = trim($_POST["genre"]);
            if (is_array($activedays) && count($activedays) && 5 < strlen($activetime) && 2 < strlen($genre)) {
                $selectedadays = [];
                foreach ($activedays as $ad) {
                    if ($availabledays[$ad]) {
                        $selectedadays[] = $availabledays[$ad];
                    }
                }
                if (count($selectedadays)) {
                    $activedays = implode(",", $selectedadays);
                    sql_query("UPDATE ts_shoutcastdj SET activedays = " . sqlesc($activedays) . ", activetime = " . sqlesc($activetime) . ", genre = " . sqlesc($genre) . " WHERE id = '" . (0 + $_GET["id"]) . "'") || sqlerr(__FILE__, 144);
                    $Updated = true;
                } else {
                    stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
                }
            } else {
                stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
            }
        }
        if (!$Updated) {
            stdhead($lang->shoutcast["bedj"]);
            $djEditRow = mysqli_fetch_assoc($Query);
            $availableDaysList = explode(",", $lang->shoutcast["days"]);
            $daysCheckboxesHtml = "";
            for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
                $daysCheckboxesHtml .= "\r\n\t\t\t\t<input type=\"checkbox\" value=\"" . ($dayIndex + 1) . "\" name=\"activedays[]\"" . (in_array(substr($availableDaysList[$dayIndex], 0, 3), explode(",", $djEditRow["activedays"])) ? " checked=\"checked\"" : "") . " /> " . $availableDaysList[$dayIndex] . " ";
            }
            echo "\r\n\t\t\t<form method=\"POST\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?do=edit&amp;id=" . $djEditRow["id"] . "\">\r\n\t\t\t<table width=\"100%\" align=\"center\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">" . $lang->shoutcast["bedj"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td align=\"left\">\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . sprintf($lang->shoutcast["f1"], $SITENAME) . "</legend>\r\n\t\t\t\t\t\t\t" . $daysCheckboxesHtml . "\r\n\t\t\t\t\t\t\t<div style=\"padding-top:10px;\">\r\n\t\t\t\t\t\t\t\t<b>" . $lang->shoutcast["f2"] . "</b> <input type=\"text\" name=\"activetime\" value=\"" . htmlspecialchars_uni($djEditRow["activetime"]) . "\" /> <b>" . $lang->shoutcast["example"] . "</b>\r\n\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . $lang->shoutcast["f5"] . "</legend>\r\n\t\t\t\t\t\t\t\t<input type=\"text\" name=\"genre\" value=\"" . htmlspecialchars_uni($djEditRow["genre"]) . "\" size=\"50\" />\r\n\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td align=\"center\" class=\"subheader\">\r\n\t\t\t\t\t\t<input type=\"submit\" value=\"" . $lang->shoutcast["f3"] . "\" /> <input type=\"reset\" value=\"" . $lang->shoutcast["f4"] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>\r\n\t\t\t";
            stdfoot();
            exit;
        }
    }
    $_GET["action"] = "list";
    $_GET["djId"] = 0 + $_GET["djId"];
}
if ($_GET["action"] == "approve" && is_valid_id($_GET["djId"]) && $is_mod) {
    sql_query("UPDATE ts_shoutcastdj SET active = '1' WHERE id = '" . (0 + $_GET["djId"]) . "'") || sqlerr(__FILE__, 210);
    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        ($djQuery = sql_query("SELECT uid FROM ts_shoutcastdj WHERE id = '" . (0 + $_GET["djId"]) . "'")) || sqlerr(__FILE__, 213);
        $djResult = mysqli_fetch_assoc($djQuery);
        require_once INC_PATH . "/functions_pm.php";
        send_pm($djResult["uid"], sprintf($lang->shoutcast["amsg"], "[URL]" . $BASEURL . "/shoutcast/dj_faq.php[/URL]"), $lang->shoutcast["subject"]);
    }
    $_GET["action"] = "list";
    $_GET["djId"] = 0 + $_GET["djId"];
}
if ($_GET["action"] == "deny" && is_valid_id($_GET["djId"]) && $is_mod) {
    sql_query("UPDATE ts_shoutcastdj SET active = '2' WHERE id = '" . (0 + $_GET["djId"]) . "'") || sqlerr(__FILE__, 225);
    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        ($djQuery = sql_query("SELECT uid FROM ts_shoutcastdj WHERE id = '" . (0 + $_GET["djId"]) . "'")) || sqlerr(__FILE__, 228);
        $djResult = mysqli_fetch_assoc($djQuery);
        require_once INC_PATH . "/functions_pm.php";
        send_pm($djResult["uid"], $lang->shoutcast["dmsg"], $lang->shoutcast["subject"]);
    }
    $_GET["action"] = "list";
    $_GET["djId"] = 0 + $_GET["djId"];
}
if ($_GET["action"] == "kick" && is_valid_id($_GET["djId"]) && $is_mod) {
    sql_query("UPDATE ts_shoutcastdj SET active = '3' WHERE id = '" . (0 + $_GET["djId"]) . "'") || sqlerr(__FILE__, 239);
    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        ($djQuery = sql_query("SELECT uid FROM ts_shoutcastdj WHERE id = '" . (0 + $_GET["djId"]) . "'")) || sqlerr(__FILE__, 242);
        $djResult = mysqli_fetch_assoc($djQuery);
        require_once INC_PATH . "/functions_pm.php";
        send_pm($djResult["uid"], $lang->shoutcast["kmsg"], sprintf($lang->shoutcast["subject2"], $SITENAME));
    }
    $_GET["action"] = "list";
    $_GET["djId"] = 0 + $_GET["djId"];
}
if ($_GET["action"] == "request") {
    ($djRequestQuery = sql_query("SELECT uid FROM ts_shoutcastdj WHERE uid = '" . $CURUSER["id"] . "'")) || sqlerr(__FILE__, 253);
    if (0 < mysqli_num_rows($djRequestQuery)) {
        print_no_permission();
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $availableDays = ["1" => "Mon", "2" => "Tue", "3" => "Wed", "4" => "Thu", "5" => "Fri", "6" => "Sat", "7" => "Sun"];
        $activeDays = $_POST["activedays"];
        $activeTime = trim($_POST["activetime"]);
        $genre = trim($_POST["genre"]);
        if (is_array($activeDays) && count($activeDays) && 5 < strlen($activeTime) && 2 < strlen($genre)) {
            $selectedDays = [];
            foreach ($activeDays as $activeDay) {
                if ($availableDays[$activeDay]) {
                    $selectedDays[] = $availableDays[$activeDay];
                }
            }
            if (count($selectedDays)) {
                $activeDays = implode(",", $selectedDays);
                sql_query("INSERT INTO ts_shoutcastdj VALUES (NULL, '" . $CURUSER["id"] . "', '0', " . sqlesc($activeDays) . ", " . sqlesc($activeTime) . ", " . sqlesc($genre) . ")") || sqlerr(__FILE__, 278);
                $djInsertId = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                ($staffQuery = sql_query("SELECT u.id, g.gid FROM users u LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE u.enabled = 'yes' AND (g.cansettingspanel = 'yes' OR g.canstaffpanel = 'yes' OR g.issupermod='yes')")) || sqlerr(__FILE__, 280);
                require_once INC_PATH . "/functions_pm.php";
                while ($staffInfo = mysqli_fetch_assoc($staffQuery)) {
                    send_pm($staffInfo["id"], sprintf($lang->shoutcast["msg"], $CURUSER["username"], "[URL]" . $BASEURL . "/shoutcast/dj.php?action=list&djId=" . $djInsertId . "[/URL]"), $lang->shoutcast["subject"]);
                }
                stdhead($lang->shoutcast["bedj"]);
                echo show_notice(sprintf($lang->shoutcast["thanks"], $SITENAME), false, $lang->shoutcast["bedj"]);
                stdfoot();
                exit;
            }
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        } else {
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        }
    }
    stdhead($lang->shoutcast["bedj"]);
    $availableDays = explode(",", $lang->shoutcast["days"]);
    $daysCheckboxes = "";
    for ($i = 0; $i < 7; $i++) {
        $daysCheckboxes .= "\r\n\t\t<input type=\"checkbox\" value=\"" . ($i + 1) . "\" name=\"activedays[]\" /> " . $availableDays[$i] . " ";
    }
    echo "\r\n\t<form method=\"POST\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?action=request\">\r\n\t<table width=\"100%\" align=\"center\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">" . $lang->shoutcast["bedj"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td align=\"left\">\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . sprintf($lang->shoutcast["f1"], $SITENAME) . "</legend>\r\n\t\t\t\t\t" . $daysCheckboxes . "\r\n\t\t\t\t\t<div style=\"padding-top:10px;\">\r\n\t\t\t\t\t\t<b>" . $lang->shoutcast["f2"] . "</b> <input type=\"text\" name=\"activetime\" value=\"00:00-00:00\" /> <b>" . $lang->shoutcast["example"] . "</b>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $lang->shoutcast["f5"] . "</legend>\r\n\t\t\t\t\t\t<input type=\"text\" name=\"genre\" value=\"\" size=\"50\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td align=\"center\" class=\"subheader\">\r\n\t\t\t\t<input type=\"submit\" value=\"" . $lang->shoutcast["f3"] . "\" /> <input type=\"reset\" value=\"" . $lang->shoutcast["f4"] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
    stdfoot();
    exit;
}
if ($_GET["action"] == "list") {
    ($djListQuery = sql_query("SELECT t.*, u.username, g.namestyle FROM ts_shoutcastdj t LEFT JOIN users u ON (t.uid=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) ORDER by t.active ASC")) || sqlerr(__FILE__, 345);
    if (mysqli_num_rows($djListQuery)) {
        $activeDjList = "\r\n\t\t<table width=\"100%\" align=\"center\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td colspan=\"5\" class=\"thead\">" . $lang->shoutcast["djlist"] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\">" . $lang->shoutcast["djname"] . "</td>\r\n\t\t\t\t<td class=\"subheader\">" . $lang->shoutcast["adays"] . "</td>\r\n\t\t\t\t<td class=\"subheader\">" . $lang->shoutcast["atime"] . "</td>\r\n\t\t\t\t<td class=\"subheader\">" . $lang->shoutcast["genre"] . "</td>\r\n\t\t\t\t<td class=\"subheader\">" . $lang->shoutcast["status"] . "</td>\r\n\t\t\t</tr>";
        while ($djList = mysqli_fetch_assoc($djListQuery)) {
            $activeDjList .= "\r\n\t\t\t<tr" . (isset($_GET["djId"]) && $_GET["djId"] == $djList["id"] ? " class=\"highlight\"" : "") . ">\r\n\t\t\t\t<td><a href=\"" . ts_seo($djList["uid"], $djList["username"]) . "\">" . get_user_color($djList["username"], $djList["namestyle"]) . "</a></td>\r\n\t\t\t\t<td>" . htmlspecialchars_uni($djList["activedays"]) . "</td>\r\n\t\t\t\t<td>" . htmlspecialchars_uni($djList["activetime"]) . "</td>\r\n\t\t\t\t<td>" . htmlspecialchars_uni($djList["genre"]) . "</td>\r\n\t\t\t\t<td>" . ($is_mod ? "<span style=\"float: right;\"><a href=\"" . $_SERVER["SCRIPT_NAME"] . "?action=approve&amp;djId=" . $djList["id"] . "\">[" . $lang->shoutcast["approve"] . "]</a> <a href=\"" . $_SERVER["SCRIPT_NAME"] . "?action=deny&amp;djId=" . $djList["id"] . "\">[" . $lang->shoutcast["deny"] . "]</a> <a href=\"" . $_SERVER["SCRIPT_NAME"] . "?action=kick&amp;djId=" . $djList["id"] . "\">[" . $lang->shoutcast["kick"] . "]</a> <a href=\"" . $_SERVER["SCRIPT_NAME"] . "?action=edit&amp;djId=" . $djList["id"] . "\">[" . $lang->shoutcast["edit"] . "]</a></span>" : "") . "<font color=\"" . ($djList["active"] == "0" ? "red\">" . $lang->shoutcast["pending"] : ($djList["active"] == "1" ? "green\">" . $lang->shoutcast["approved"] : ($djList["active"] == "2" ? "blue\">" . $lang->shoutcast["denied"] : "darkred\">" . $lang->shoutcast["kicked"]))) . "</font></td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        stderr($lang->global["error"], $lang->shoutcast["down2"]);
    }
    stdhead($lang->shoutcast["djlist"]);
    echo (isset($Updated) ? "<div class=\"success\">Changes has been saved.</div>" : "") . $activeDjList . "\r\n\t</table>";
    stdfoot();
    exit;
}

?>