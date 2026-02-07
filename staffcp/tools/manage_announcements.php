<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : "");
$Language = file("languages/" . getStaffLanguage() . "/manage_announcements.lang");
$Message = "";
$subject = "";
$message = "";
$minclassread = ["1"];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $subject = trim($_POST["subject"]);
    $message = trim($_POST["message"]);
    $minclassread = isset($_POST["usergroups"]) ? $_POST["usergroups"] : "";
    if ($subject && $message && is_array($minclassread) && count($minclassread)) {
        $Work = implode(",", $minclassread);
        if ($Act == "new") {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO announcements (subject, message, `by`, added, minclassread) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $message) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $_SESSION["ADMIN_USERNAME"]) . "', NOW(), '" . $Work . "')");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}"], [$subject, $_SESSION["ADMIN_USERNAME"]], $Language[10]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $announce_read = 'no' WHERE usergroup IN (0, " . $Work . ")");
                $Act = "";
            }
        } else {
            if ($Act == "edit" && $id) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE announcements SET $subject = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', $message = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $message) . "', $minclassread = '" . $Work . "' WHERE `id` = '" . $id . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $Message = str_replace(["{1}", "{2}"], [$subject, $_SESSION["ADMIN_USERNAME"]], $Language[12]);
                    logStaffAction($Message);
                    $Message = showAlertMessage($Message);
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $announce_read = 'no' WHERE usergroup IN (0, " . $Work . ")");
                }
                $Act = "";
            }
        }
    } else {
        $Message = showAlertError($Language[3]);
    }
}
if ($Act == "delete" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT subject FROM announcements WHERE `id` = '" . $id . "'");
    if (0 < mysqli_num_rows($query)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM announcements WHERE `id` = '" . $id . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Result = mysqli_fetch_assoc($query);
            $subject = $Result["subject"];
            $Message = str_replace(["{1}", "{2}"], [$subject, $_SESSION["ADMIN_USERNAME"]], $Language[11]);
            logStaffAction($Message);
            $Message = showAlertMessage($Message);
        }
    }
}
if ($Act == "new") {
    echo loadTinyMCEEditor() . "\r\n\t<form $action = \"index.php?do=manage_announcements&$act = new\" $method = \"post\">\t\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . " - " . $Language[6] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"subject\" $value = \"" . htmlspecialchars($subject) . "\" $style = \"width: 99%;\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t\t<td class=\"alt1\"><textarea $name = \"message\" $id = \"message\" $style = \"width: 100%; height: 100px;\" $dir = \"ltr\" $tabindex = \"1\">" . htmlspecialchars($message) . "</textarea>\r\n\t\t\t<p><a $href = \"javascript:toggleEditor('message');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[9] . "</td>\r\n\t\t\t<td class=\"alt1\">" . function_148($minclassread) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[14] . "\" /> <input $type = \"reset\" $value = \"" . $Language[15] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
}
if ($Act == "edit" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT subject, message, minclassread FROM announcements WHERE `id` = '" . $id . "'");
    if (0 < mysqli_num_rows($query)) {
        $Ann = mysqli_fetch_assoc($query);
        echo loadTinyMCEEditor() . "\r\n\t\t<form $action = \"index.php?do=manage_announcements&$act = edit&$id = " . $id . "\" $method = \"post\">\r\n\t\t\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[4] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"subject\" $value = \"" . htmlspecialchars($Ann["subject"]) . "\" $style = \"width: 99%;\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea $name = \"message\" $id = \"f_offlinemsg\" $style = \"width: 100%; height: 100px;\" $dir = \"ltr\" $tabindex = \"1\">" . htmlspecialchars($Ann["message"]) . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('f_offlinemsg');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[9] . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . function_148($Ann["minclassread"]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[14] . "\" /> <input $type = \"reset\" $value = \"" . $Language[15] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM announcements"));
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=manage_announcements&amp;");
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT a.*, u.username, g.namestyle FROM announcements a LEFT JOIN users u ON (a.$by = u.username) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) ORDER by a.added DESC " . $limit);
if (0 < mysqli_num_rows($query)) {
    echo showAlertMessage("<a $href = \"index.php?do=manage_announcements&amp;$act = new\">" . $Language[6] . "</a>") . "\r\n\t" . $Message . "\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"6\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . " (" . $results . ")\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . $Language[7] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[8] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[16] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[17] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[9] . "</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\">" . $Language[18] . "</td>\r\n\t\t</tr>\r\n\t\t";
    while ($Ann = mysqli_fetch_assoc($query)) {
        echo "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Ann["subject"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . strip_tags(substr($Ann["message"], 0, 150)) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $Ann["username"] . "\">" . applyUsernameStyle($Ann["username"], $Ann["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($Ann["added"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Ann["minclassread"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_announcements&amp;$act = edit&amp;$id = " . $Ann["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[4] . "\" $title = \"" . $Language[4] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_announcements&amp;$act = delete&amp;$id = " . $Ann["id"] . "\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[5] . "\" $title = \"" . $Language[5] . "\" $border = \"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    echo "\r\n\t</table>\r\n\t" . $pagertop;
} else {
    echo showAlertError(str_replace("{1}", "index.php?do=manage_announcements&amp;$act = new", $Language[13]));
}
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $var_81 = ob_get_contents();
    ob_end_clean();
    return $var_81;
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_148($selected)
{
    if (!is_array($selected) && preg_match("@,@Uis", $selected)) {
        $selected = explode(",", $selected);
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
    $var_318 = mysqli_fetch_assoc($query);
    $count = 0;
    $var_423 = "\r\n\t<table>\r\n\t\t<tr>\t";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups WHERE `isbanned` = 'no' ORDER by disporder ASC");
    while ($var_424 = mysqli_fetch_assoc($query)) {
        if (!($var_424["cansettingspanel"] == "yes" && $var_318["cansettingspanel"] != "yes" || $var_424["canstaffpanel"] == "yes" && $var_318["canstaffpanel"] != "yes" || $var_424["issupermod"] == "yes" && $var_318["issupermod"] != "yes")) {
            if ($count && $count % 8 == 0) {
                $var_423 .= "</tr><tr>";
            }
            $var_423 .= "<td><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $var_424["gid"] . "\"" . (is_array($selected) && count($selected) && in_array($var_424["gid"], $selected) ? " $checked = \"checked\"" : "") . " /></td><td>" . str_replace("{username}", $var_424["title"], $var_424["namestyle"]) . "</td>";
            $count++;
        }
    }
    $var_423 .= "</tr></table>";
    return $var_423;
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
        $var_253 = calculatePagination($previousPage, $perpage, $results);
        $paginationHtml["prev"] = true;
    }
    if ($pagenumber < $queryResult) {
        $var_254 = $pagenumber + 1;
        $var_255 = calculatePagination($var_254, $perpage, $results);
        $paginationHtml["next"] = true;
    }
    $var_256 = "3";
    if (!isset($paginationSkipLinksArray) || !is_array($paginationSkipLinksArray)) {
        $var_258 = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $var_258, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $var_259 = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $var_253["first"] . " to " . $var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_254 . "\" $title = \"Next Page - Show Results " . $var_255["first"] . " to " . $var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($var_256 <= abs($currentPage - $pagenumber) && $var_256 != 0) {
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