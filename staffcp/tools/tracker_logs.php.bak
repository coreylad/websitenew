<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/tracker_logs.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$Found = "";
$pagertop = "";
$limit = "";
$keyword = isset($_POST["keyword"]) ? trim($_POST["keyword"]) : (isset($_GET["keyword"]) ? trim(urldecode($_GET["keyword"])) : "");
$Page = isset($_POST["page"]) ? trim($_POST["page"]) : (isset($_GET["page"]) ? intval($_GET["page"]) : "");
$WhereQuery = "";
$SHOWPHPERRORS = "";
$LinkQuery = "";
$FormField = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
$LoggedAdminDetails = mysqli_fetch_assoc($query);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $LoggedAdminDetails["cansettingspanel"] == "yes" && isset($_POST["lid"]) && count($_POST["lid"])) {
    $Work = implode(", ", $_POST["lid"]);
    if ($Work) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM sitelog WHERE id IN (0, " . $Work . ")");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $SysMsg = str_replace(["{1}", "{2}"], [$Work, $_SESSION["ADMIN_USERNAME"]], $Language[4]);
            logStaffAction($SysMsg);
        }
    }
}
if ($keyword) {
    $WhereQuery = " WHERE `txt` LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keyword) . "%'";
    $LinkQuery = "keyword=" . urlencode(htmlspecialchars($keyword)) . "&amp;";
    $FormField = "<input $type = \"hidden\" $name = \"keyword\" $value = \"" . htmlspecialchars($keyword) . "\" />";
}
if ($Page) {
    $FormField .= "<input $type = \"hidden\" $name = \"page\" $value = \"" . $Page . "\" />";
}
if ($Act == "delete_all" && $LoggedAdminDetails["cansettingspanel"] == "yes") {
    mysqli_query($GLOBALS["DatabaseConnect"], "TRUNCATE TABLE sitelog");
}
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM sitelog" . $WhereQuery));
list($pagertop, $limit) = buildPaginationLinks(50, $results, $_SERVER["SCRIPT_NAME"] . "?do=tracker_logs&amp;" . $LinkQuery);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM sitelog" . $WhereQuery . " ORDER by added DESC " . $limit);
if (mysqli_num_rows($query) == 0) {
    $Message = showAlertError($Language[1]);
} else {
    $PHPERRORS = [];
    $Count = 0;
    while ($Log = mysqli_fetch_assoc($query)) {
        if (substr($Log["txt"], 0, 4) === "PHP ") {
            $PHPERRORS[] = $Log;
        } else {
            $class = $Count % 2 == 1 ? "alt2" : "alt1";
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\">" . formatTimestamp($Log["added"]) . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\">" . validateEmail($keyword, strip_tags($Log["txt"])) . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\"><input $type = \"checkbox\" $name = \"lid[]\" $value = \"" . $Log["id"] . "\" $checkme = \"group\" /></td>\r\n\t\t\t</td>\r\n\t\t\t";
            $Count++;
        }
    }
    if (count($PHPERRORS)) {
        $SHOWPHPERRORS = "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('tracker_logs', this, 'group');\" /></td>\r\n\t\t\t</tr>";
        foreach ($PHPERRORS as $Log) {
            $SHOWPHPERRORS .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . formatTimestamp($Log["added"]) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . strip_tags($Log["txt"]) . "</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\"><input $type = \"checkbox\" $name = \"lid[]\" $value = \"" . $Log["id"] . "\" $checkme = \"group\" /></td>\r\n\t\t\t</td>\r\n\t\t\t";
        }
        $SHOWPHPERRORS .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"3\"><input $type = \"submit\" $value = \"" . $Language[8] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>";
    }
    $Found .= (empty($Found) ? "<tr><td $colspan = \"3\" class=\"alt1\">" . $Language[1] . "</td></tr>" : "") . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"3\">" . ($LoggedAdminDetails["cansettingspanel"] == "yes" ? "<input $type = \"submit\" $value = \"" . $Language[8] . "\" />" : "") . "</td>\r\n\t\t</tr>";
}
if ($Message) {
    echo "\r\n\t\r\n\t" . $Message;
} else {
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked == false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\r\n\t\tfunction ConfirmDelete()\r\n\t\t{\r\n\t\t\tif (confirm(\"" . trim($Language[15]) . "\"))\r\n\t\t\t{\r\n\t\t\t\tTSJump(\"index.php?do=tracker_logs&$act = delete_all\");\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=tracker_logs\" $method = \"post\" $name = \"search\">\r\n\t" . ($LoggedAdminDetails["cansettingspanel"] == "yes" ? showAlertMessage("<a $href = \"#\" $onclick = \"ConfirmDelete();\">" . $Language[14] . "</a>") : "") . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\"><b>" . $Language[10] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $align = \"center\"> " . $Language[11] . "<input $type = \"text\" class=\"bginput\" $style = \"width: 80%;\" $name = \"keyword\" $value = \"" . htmlspecialchars($keyword) . "\" /> <input $type = \"submit\" $value = \"" . $Language[12] . "\" /> <input $type = \"reset\" $value = \"" . $Language[13] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=tracker_logs\" $method = \"post\" $name = \"tracker_logs\">\r\n\t" . $FormField . "\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b>" . $Language[3] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('tracker_logs', this, 'group');\" /></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>\r\n\t" . $SHOWPHPERRORS . "\r\n\t" . $pagertop . "\r\n\t</form>\r\n\t";
}
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
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $previousPageQuery . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_152($search, $subject, $hlstart = "<b><font color='#f7071d'>", $hlend = "</font></b>")
{
    $logType = strlen($search);
    if ($logType == 0) {
        return $subject;
    }
    $logMessage = $subject;
    while ($logMessage = stristr($logMessage, $search)) {
        $logData = substr($logMessage, 0, $logType);
        $logMessage = substr($logMessage, $logType);
        $subject = str_replace($logData, $hlstart . $logData . $hlend, $subject);
    }
    return $subject;
}

?>