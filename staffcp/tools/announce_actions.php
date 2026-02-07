<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/announce_actions.lang");
$Message = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["ids"]) && is_array($_POST["ids"]) && count($_POST["ids"])) {
    $Work = implode(",", $_POST["ids"]);
    if (isset($_POST["delete"])) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM announce_actions WHERE userid IN (0," . $Work . ")");
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
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM announce_actions"));
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=announce_actions&amp;");
$Found = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.*,  u.username, g.namestyle, tr.name as torrentname FROM announce_actions t LEFT JOIN users u ON (t.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents tr ON (t.$torrentid = tr.id) ORDER by t.actiontime DESC " . $limit);
if ($results) {
    while ($R = mysqli_fetch_assoc($query)) {
        $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $R["username"] . "\">" . applyUsernameStyle($R["username"], $R["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($R["actiontime"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"../details.php?$id = " . $R["torrentid"] . "\">" . $R["torrentname"] . "</a>\r\n\t\t\t</td>\t\t\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($R["passkey"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($R["ip"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($R["actionmessage"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"ids[]\" $value = \"" . $R["userid"] . "\" $checkme = \"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
} else {
    echo "\r\n\t\r\n\t" . showAlertError($Language[19]);
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"index.php?do=announce_actions" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"announce_actions\">\r\n" . $Message . "\r\n" . $pagertop . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"7\">" . $Language[2] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[4] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[5] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[10] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[9] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t<input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('announce_actions', this, 'group');\">\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $colspan = \"7\" $align = \"right\">\r\n\t\t\t<input $type = \"submit\" $name = \"delete\" $value = \"" . $Language[13] . "\" /> <input $type = \"submit\" $name = \"warn\" $value = \"" . $Language[12] . "\" /> <input $type = \"submit\" $name = \"ban\" $value = \"" . $Language[11] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n" . $pagertop . "\r\n</form>";
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
    $startOffset = $perpage * ($pagenumber - 1);
    $endOffset = $startOffset + $perpage;
    if ($total < $endOffset) {
        $endOffset = $total;
    }
    $startOffset++;
    return ["first" => number_format($startOffset), "last" => number_format($endOffset)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $totalPages = @ceil($results / $perpage);
    } else {
        $totalPages = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $startOffset = ($pagenumber - 1) * $perpage;
    $endOffset = $pagenumber * $perpage;
    if ($results < $endOffset) {
        $endOffset = $results;
        if ($results < $startOffset) {
            $startOffset = $results - $perpage - 1;
        }
    }
    if ($startOffset < 0) {
        $startOffset = 0;
    }
    $paginationHTML = $unused1 = $unused2 = $unused3 = $unused4 = "";
    $pageCounter = 0;
    if ($results <= $perpage) {
        $pageNav["pagenav"] = false;
        return ["", "LIMIT " . $startOffset . ", " . $perpage];
    }
    $pageNav["pagenav"] = true;
    $total = number_format($results);
    $pageNav["last"] = false;
    $pageNav["first"] = $pageNav["last"];
    $pageNav["next"] = $pageNav["first"];
    $pageNav["prev"] = $pageNav["next"];
    if (1 < $pagenumber) {
        $prevPage = $pagenumber - 1;
        $prevPageInfo = calculatePagination($prevPage, $perpage, $results);
        $pageNav["prev"] = true;
    }
    if ($pagenumber < $totalPages) {
        $nextPage = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPage, $perpage, $results);
        $pageNav["next"] = true;
    }
    $pageWindow = "3";
    if (!isset($jumpPages) || !is_array($jumpPages)) {
        $jumpPagesStr = "10 50 100 500 1000";
        $jumpPages[] = preg_split("#\\s+#s", $jumpPagesStr, -1, PREG_SPLIT_NO_EMPTY);
        while ($pageCounter++ < $totalPages) {
        }
        $prevPageQuery = isset($prevPage) && $prevPage != 1 ? "page=" . $prevPage : "";
        $paginationHTML = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $totalPages . "</li>\r\n\t\t\t\t\t\t" . ($pageNav["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($pageNav["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $prevPageQuery . "\" $title = \"Previous Page - Show Results " . $prevPageInfo["first"] . " to " . $prevPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationHTML . "\r\n\t\t\t\t\t\t" . ($pageNav["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPage . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($pageNav["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $totalPages . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationHTML, "LIMIT " . $startOffset . ", " . $perpage];
    }
    if ($pageWindow <= abs($pageCounter - $pagenumber) && $pageWindow != 0) {
        if ($pageCounter == 1) {
            $firstPageInfo = calculatePagination(1, $perpage, $results);
            $pageNav["first"] = true;
        }
        if ($pageCounter == $totalPages) {
            $lastPageInfo = calculatePagination($totalPages, $perpage, $results);
            $pageNav["last"] = true;
        }
        if (in_array(abs($pageCounter - $pagenumber), $jumpPages) && $pageCounter != 1 && $pageCounter != $totalPages) {
            $currentPageInfo = calculatePagination($pageCounter, $perpage, $results);
            $pageOffset = $pageCounter - $pagenumber;
            if (0 < $pageOffset) {
                $pageOffset = "+" . $pageOffset;
            }
            $paginationHTML .= "<li><a class=\"smalltext\" $href = \"" . $address . ($pageCounter != 1 ? "page=" . $pageCounter : "") . "\" $title = \"Show results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\"><!--" . $pageOffset . "-->" . $pageCounter . "</a></li>";
        }
    } else {
        if ($pageCounter == $pagenumber) {
            $activePageInfo = calculatePagination($pageCounter, $perpage, $results);
            $paginationHTML .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $activePageInfo["first"] . " to " . $activePageInfo["last"] . " of " . $total . "\">" . $pageCounter . "</a></li>";
        } else {
            $currentPageInfo = calculatePagination($pageCounter, $perpage, $results);
            $paginationHTML .= "<li><a $href = \"" . $address . ($pageCounter != 1 ? "page=" . $pageCounter : "") . "\" $title = \"Show results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\">" . $pageCounter . "</a></li>";
        }
    }
}
function formatTimestamp($timestamp = "")
{
    $dateFormat = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormat, $timestamp);
}
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages \r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES \r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $receiver . "'");
    }
}
function function_85($sec, $padHours = false)
{
    $formattedTime = "";
    $hours = intval(intval($sec) / 3600);
    $formattedTime .= $padHours ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":";
    $minutes = intval($sec / 60 % 60);
    $formattedTime .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";
    $seconds = intval($sec % 60);
    $formattedTime .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
    return $formattedTime;
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>