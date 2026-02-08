<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/cheat_attempts.lang");
$Message = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
$Result = mysqli_fetch_assoc($query);
$ANNOUNCE = unserialize($Result["content"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["ids"]) && is_array($_POST["ids"]) && count($_POST["ids"])) {
    $Work = implode(",", $_POST["ids"]);
    if (isset($_POST["delete"])) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM cheat_attempts WHERE uid IN (0," . $Work . ")");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Message = str_replace(["{1}", "{2}"], [$Work, $_SESSION["ADMIN_USERNAME"]], $Language[16]);
            logStaffAction($Message);
            $Message = showAlertMessage($Message);
        }
    } else {
        if (isset($_POST["ban"])) {
            $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[18]);
            $modcomment = gmdate("Y-m-d") . " - " . trim($Message) . "\n";
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `enabled` = 'no', $modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment), $notifs = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Message) . "' WHERE id IN (0," . $Work . ")");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}"], [$Work, $_SESSION["ADMIN_USERNAME"]], $Language[14]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
            }
        } else {
            if (isset($_POST["warn"])) {
                $warneduntil = date("Y-m-d H:i:s", strtotime("+1 week"));
                $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[17]);
                $modcomment = gmdate("Y-m-d") . " - " . trim($Message) . "\n";
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $warned = 'yes', $timeswarned = timeswarned + 1, $warneduntil = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $warneduntil) . "', $modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment) WHERE id IN (0," . $Work . ")");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $Message2 = str_replace(["{1}", "{2}"], [$Work, $_SESSION["ADMIN_USERNAME"]], $Language[15]);
                    logStaffAction($Message2);
                    $Message2 = showAlertMessage($Message2);
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE id IN (0," . $Work . ")");
                    while ($User = mysqli_fetch_assoc($query)) {
                        sendPrivateMessage($User["id"], $Message, $Language[2]);
                    }
                    $Message = $Message2;
                }
            }
        }
    }
}
$Found = "";
if ($ANNOUNCE["xbt_active"] == "yes") {
    $queryxa = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT value FROM xbt_config WHERE $name = 'announce_interval'");
    $Result = mysqli_fetch_assoc($queryxa);
    $xbt_announce_interval = $Result["value"];
    $results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM xbt_files_users WHERE up_rate > " . $ANNOUNCE["max_rate"]));
    list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=cheat_attempts&amp;");
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.announced, t.mtime as added, t.up_rate as transfer_rate, t.uploaded as upthis, t.ipa as ip, u.username, g.namestyle, tr.id as torrentid, tr.name as torrentname FROM xbt_files_users t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents tr ON (t.$fid = tr.id) WHERE t.up_rate > " . $ANNOUNCE["max_rate"] . " ORDER by t.up_rate DESC " . $limit);
} else {
    $results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM cheat_attempts"));
    list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=cheat_attempts&amp;");
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.*,  u.username, g.namestyle, tr.name as torrentname FROM cheat_attempts t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents tr ON (t.$torrentid = tr.id) ORDER by t.added DESC " . $limit);
}
if ($results) {
    while ($R = mysqli_fetch_assoc($query)) {
        $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $R["username"] . "\">" . applyUsernameStyle($R["username"], $R["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($R["added"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"../details.php?$id = " . $R["torrentid"] . "\">" . $R["torrentname"] . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($ANNOUNCE["xbt_active"] == "yes" ? "----" : htmlspecialchars($R["agent"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatBytes($R["transfer_rate"]) . "/s\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatBytes($R["upthis"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($ANNOUNCE["xbt_active"] == "yes" ? formatSecondsToTime($R["announced"] * $xbt_announce_interval) : formatSecondsToTime($R["timediff"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($ANNOUNCE["xbt_active"] == "yes" ? long2ip($R["ip"]) : htmlspecialchars($R["ip"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"ids[]\" $value = \"" . $R["uid"] . "\" $checkme = \"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
} else {
    echo "\r\n\t\r\n\t" . showAlertError($Language[19]);
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"index.php?do=cheat_attempts" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"cheat_attempts\">\r\n" . $Message . "\r\n" . $pagertop . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"9\">" . $Language[2] . " " . ($ANNOUNCE["xbt_active"] == "yes" ? "Min. " . formatBytes($ANNOUNCE["max_rate"]) . "/s" : "") . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[4] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[5] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[7] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[8] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[9] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[10] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t<input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('cheat_attempts', this, 'group');\">\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $colspan = \"9\" $align = \"right\">\r\n\t\t\t" . ($ANNOUNCE["xbt_active"] == "yes" ? "" : "<input $type = \"submit\" $name = \"delete\" $value = \"" . $Language[13] . "\" /> ") . "<input $type = \"submit\" $name = \"warn\" $value = \"" . $Language[12] . "\" /> <input $type = \"submit\" $name = \"ban\" $value = \"" . $Language[11] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n" . $pagertop . "\r\n</form>";
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function validatePerPage($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $totalPages = ceil($numresults / $perpage);
    if ($totalPages == 0) {
        $totalPages = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($totalPages < $page) {
            $page = $totalPages;
        }
    }
}
function calculatePagination($pagenumber, $perpage, $total)
{
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    $paginationFirstItem++;
    return ["first" => number_format($paginationFirstItem), "last" => number_format($paginationLastItem)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $queryResult = @ceil($results / $perpage);
    } else {
        $queryResult = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $limitOffset = ($pagenumber - 1) * $perpage;
    $paginationOffset = $pagenumber * $perpage;
    if ($results < $paginationOffset) {
        $paginationOffset = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    $paginationLinks = $prevPage = $nextPage = $pageLinks = $paginationHtml = "";
    $currentPage = 0;
    if ($results <= $perpage) {
        $paginationHtml["pagenav"] = false;
        return ["", "LIMIT " . $limitOffset . ", " . $perpage];
    }
    $paginationHtml["pagenav"] = true;
    $total = number_format($results);
    $paginationHtml["last"] = false;
    $paginationHtml["first"] = $paginationHtml["last"];
    $paginationHtml["next"] = $paginationHtml["first"];
    $paginationHtml["prev"] = $paginationHtml["next"];
    if (1 < $pagenumber) {
        $previousPage = $pagenumber - 1;
        $previousPageInfo = calculatePagination($previousPage, $perpage, $results);
        $paginationHtml["prev"] = true;
    }
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPageNumber, $perpage, $results);
        $paginationHtml["next"] = true;
    }
    $pageRangeThreshold = "3";
    if (!isset($paginationSkipLinksArray) || !is_array($paginationSkipLinksArray)) {
        $paginationOptions = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $paginationOptions, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $previousPageQuery = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $previousPageQuery . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($pageRangeThreshold <= abs($currentPage - $pagenumber) && $pageRangeThreshold != 0) {
        if ($currentPage == 1) {
            $firstPageInfo = calculatePagination(1, $perpage, $results);
            $paginationHtml["first"] = true;
        }
        if ($currentPage == $queryResult) {
            $lastPageInfo = calculatePagination($queryResult, $perpage, $results);
            $paginationHtml["last"] = true;
        }
        if (in_array(abs($currentPage - $pagenumber), $paginationSkipLinksArray) && $currentPage != 1 && $currentPage != $queryResult) {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $pageOffsetDisplay = $currentPage - $pagenumber;
            if (0 < $pageOffsetDisplay) {
                $pageOffsetDisplay = "+" . $pageOffsetDisplay;
            }
            $paginationLinks .= "<li><a class=\"smalltext\" $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        }
    }
}
function formatTimestamp($timestamp = "")
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, $timestamp);
}
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages \r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES \r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $receiver . "'");
    }
}
function formatSecondsToTime($sec, $padHours = false)
{
    $startTime = "";
    $endTime = intval(intval($sec) / 3600);
    $startTime .= $padHours ? str_pad($endTime, 2, "0", STR_PAD_LEFT) . ":" : $endTime . ":";
    $timeRange = intval($sec / 60 % 60);
    $startTime .= str_pad($timeRange, 2, "0", STR_PAD_LEFT) . ":";
    $timePeriod = intval($sec % 60);
    $startTime .= str_pad($timePeriod, 2, "0", STR_PAD_LEFT);
    return $startTime;
}
function formatBytes($bytes = 0)
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 0) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 0, 2) . " TB";
}
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function function_89($stamp = "")
{
    $Language = file("languages/" . getStaffLanguage() . "/view_peers.lang");
    $dateFrom = 31536000;
    $dateTo = 2678400;
    $dateFormatted = 604800;
    $timeStamp = 86400;
    $duration = 3600;
    $durationFormatted = 60;
    $timeDiff = floor($stamp / $dateFrom);
    $stamp %= $dateFrom;
    $timeString = floor($stamp / $dateTo);
    $stamp %= $dateTo;
    $timeDisplay = floor($stamp / $dateFormatted);
    $stamp %= $dateFormatted;
    $periodDisplay = floor($stamp / $timeStamp);
    $stamp %= $timeStamp;
    $endTime = floor($stamp / $duration);
    $stamp %= $duration;
    $timeRange = floor($stamp / $durationFormatted);
    $stamp %= $durationFormatted;
    $timePeriod = $stamp;
    if ($timeDiff == 1) {
        $timePeriodDisplay["years"] = "<b>1</b> " . $Language[24];
    } else {
        if (1 < $timeDiff) {
            $timePeriodDisplay["years"] = "<b>" . $timeDiff . "</b> " . $Language[25];
        }
    }
    if ($timeString == 1) {
        $timePeriodDisplay["months"] = "<b>1</b> " . $Language[26];
    } else {
        if (1 < $timeString) {
            $timePeriodDisplay["months"] = "<b>" . $timeString . "</b> " . $Language[27];
        }
    }
    if ($timeDisplay == 1) {
        $timePeriodDisplay["weeks"] = "<b>1</b> " . $Language[28];
    } else {
        if (1 < $timeDisplay) {
            $timePeriodDisplay["weeks"] = "<b>" . $timeDisplay . "</b> " . $Language[29];
        }
    }
    if ($periodDisplay == 1) {
        $timePeriodDisplay["days"] = "<b>1</b> " . $Language[30];
    } else {
        if (1 < $periodDisplay) {
            $timePeriodDisplay["days"] = "<b>" . $periodDisplay . "</b> " . $Language[31];
        }
    }
    if ($endTime == 1) {
        $timePeriodDisplay["hours"] = "<b>1</b> " . $Language[32];
    } else {
        if (1 < $endTime) {
            $timePeriodDisplay["hours"] = "<b>" . $endTime . "</b> " . $Language[33];
        }
    }
    if ($timeRange == 1) {
        $timePeriodDisplay["minutes"] = "<b>1</b> " . $Language[34];
    } else {
        if (1 < $timeRange) {
            $timePeriodDisplay["minutes"] = "<b>" . $timeRange . "</b> " . $Language[35];
        }
    }
    if ($timePeriod == 1) {
        $timePeriodDisplay["seconds"] = "<b>1</b> " . $Language[36];
    } else {
        if (1 < $timePeriod) {
            $timePeriodDisplay["seconds"] = "<b>" . $timePeriod . "</b> " . $Language[37];
        }
    }
    if (isset($timePeriodDisplay) && is_array($timePeriodDisplay)) {
        $total = implode(", ", $timePeriodDisplay);
    } else {
        $total = "0 " . $Language[36];
    }
    return "<small\">" . $total . "</small>";
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>