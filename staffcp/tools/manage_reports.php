<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_reports.lang");
$Message = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["reports"]) && is_array($_POST["reports"]) && count($_POST["reports"])) {
    $Work = implode(",", $_POST["reports"]);
    if (isset($_POST["delete"])) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_reports WHERE rid IN (0," . $Work . ")");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Message = str_replace(["{1}", "{2}"], [$Work, $_SESSION["ADMIN_USERNAME"]], $Language[3]);
            logStaffAction($Message);
            $Message = showAlertMessage($Message);
        }
    } else {
        if (isset($_POST["confirm"])) {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_reports SET $confirmed = '1', $confirmedby = '" . $_SESSION["ADMIN_ID"] . "' WHERE rid IN (0," . $Work . ")");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}"], [$Work, $_SESSION["ADMIN_USERNAME"]], $Language[4]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
            }
        } else {
            if (isset($_POST["deconfirm"])) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_reports SET $confirmed = '0', $confirmedby = '0' WHERE rid IN (0," . $Work . ")");
            }
        }
    }
}
$ReportTypes = [1 => $Language[13], 2 => $Language[14], 3 => $Language[15], 4 => $Language[16], 5 => $Language[17], 6 => $Language[18], 7 => $Language[19], 8 => $Language[20], 9 => $Language[26], 10 => $Language[27]];
$ReportColors = ["1" => "00FF00", "2" => "0033FF", "3" => "666600", "4" => "CC0000", "5" => "FFCC00", "6" => "FF00FF", "7" => "9900FF", "8" => "6633CC", "9" => "FFCC01", "10" => "FFCC01"];
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_reports"));
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=manage_reports&amp;");
$Found = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.*,  u.username, uu.username as cuser, g.namestyle, gg.namestyle as cuserns FROM ts_reports t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN users uu ON (t.$confirmedby = uu.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid) ORDER by t.confirmed ASC, t.date DESC " . $limit);
if ($results) {
    while ($R = mysqli_fetch_assoc($query)) {
        $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<font $color = \"#" . $ReportColors[$R["type"]] . "\"><b>" . $ReportTypes[$R["type"]] . "</b></font>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($R["date"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $R["username"] . "\" $target = \"_blank\">" . applyUsernameStyle($R["username"], $R["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"../" . $R["url"] . "\" $target = \"_blank\">" . $Language[21] . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($R["confirmed"] == "1" ? str_replace("{1}", "<a $href = \"index.php?do=edit_user&amp;$username = " . $R["cuser"] . "\" $target = \"_blank\">" . applyUsernameStyle($R["cuser"], $R["cuserns"]) . "</a>", $Language[23]) : $Language[22]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($R["reason"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"reports[]\" $value = \"" . $R["rid"] . "\" $checkme = \"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
} else {
    echo "\r\n\t" . showAlertError($Language[25]);
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"index.php?do=manage_reports" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"manage_reports\">\r\n" . $Message . "\r\n" . $pagertop . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"7\">" . $Language[2] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[7] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[8] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[9] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[24] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[10] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t<input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('manage_reports', this, 'group');\">\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $colspan = \"7\" $align = \"right\">\r\n\t\t\t<input $type = \"submit\" $name = \"delete\" $value = \"" . $Language[11] . "\" /> <input $type = \"submit\" $name = \"confirm\" $value = \"" . $Language[12] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n" . $pagertop . "\r\n</form>";
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
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
    $var_244 = $pagenumber * $perpage;
    if ($results < $var_244) {
        $var_244 = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    $paginationLinks = $var_246 = $var_247 = $var_248 = $var_249 = "";
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
        $var_258 = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $var_258, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $var_259 = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
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
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}

?>