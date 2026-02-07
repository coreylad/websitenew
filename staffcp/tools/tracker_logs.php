<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
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
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
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
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM sitelog" . $WhereQuery . " ORDER by added DESC " . $limit);
if (mysqli_num_rows($Query) == 0) {
    $Message = showAlertError($Language[1]);
} else {
    $PHPERRORS = [];
    $Count = 0;
    while ($Log = mysqli_fetch_assoc($Query)) {
        if (substr($Log["txt"], 0, 4) === "PHP ") {
            $PHPERRORS[] = $Log;
        } else {
            $class = $Count % 2 == 1 ? "alt2" : "alt1";
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\">" . formatTimestamp($Log["added"]) . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\">" . var_431($keyword, strip_tags($Log["txt"])) . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\"><input $type = \"checkbox\" $name = \"lid[]\" $value = \"" . $Log["id"] . "\" $checkme = \"group\" /></td>\r\n\t\t\t</td>\r\n\t\t\t";
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
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\r\n\t\tfunction ConfirmDelete()\r\n\t\t{\r\n\t\t\tif (confirm(\"" . trim($Language[15]) . "\"))\r\n\t\t\t{\r\n\t\t\t\tTSJump(\"index.php?do=tracker_logs&$act = delete_all\");\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=tracker_logs\" $method = \"post\" $name = \"search\">\r\n\t" . ($LoggedAdminDetails["cansettingspanel"] == "yes" ? showAlertMessage("<a $href = \"#\" $onclick = \"ConfirmDelete();\">" . $Language[14] . "</a>") : "") . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\"><b>" . $Language[10] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $align = \"center\"> " . $Language[11] . "<input $type = \"text\" class=\"bginput\" $style = \"width: 80%;\" $name = \"keyword\" $value = \"" . htmlspecialchars($keyword) . "\" /> <input $type = \"submit\" $value = \"" . $Language[12] . "\" /> <input $type = \"reset\" $value = \"" . $Language[13] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=tracker_logs\" $method = \"post\" $name = \"tracker_logs\">\r\n\t" . $FormField . "\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b>" . $Language[3] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('tracker_logs', this, 'group');\" /></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>\r\n\t" . $SHOWPHPERRORS . "\r\n\t" . $pagertop . "\r\n\t</form>\r\n\t";
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
        var_236("../index.php");
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
    $var_240 = ceil($numresults / $perpage);
    if ($var_240 == 0) {
        $var_240 = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($var_240 < $page) {
            $page = $var_240;
        }
    }
}
function calculatePagination($pagenumber, $perpage, $total)
{
    $var_241 = $perpage * ($pagenumber - 1);
    $var_89 = $var_241 + $perpage;
    if ($total < $var_89) {
        $var_89 = $total;
    }
    $var_241++;
    return ["first" => number_format($var_241), "last" => number_format($var_89)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $var_242 = @ceil($results / $perpage);
    } else {
        $var_242 = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $var_243 = ($pagenumber - 1) * $perpage;
    $var_244 = $pagenumber * $perpage;
    if ($results < $var_244) {
        $var_244 = $results;
        if ($results < $var_243) {
            $var_243 = $results - $perpage - 1;
        }
    }
    if ($var_243 < 0) {
        $var_243 = 0;
    }
    $var_245 = $var_246 = $var_247 = $var_248 = $var_249 = "";
    $var_250 = 0;
    if ($results <= $perpage) {
        $var_251["pagenav"] = false;
        return ["", "LIMIT " . $var_243 . ", " . $perpage];
    }
    $var_251["pagenav"] = true;
    $total = number_format($results);
    $var_251["last"] = false;
    $var_251["first"] = $var_251["last"];
    $var_251["next"] = $var_251["first"];
    $var_251["prev"] = $var_251["next"];
    if (1 < $pagenumber) {
        $var_252 = $pagenumber - 1;
        $var_253 = calculatePagination($var_252, $perpage, $results);
        $var_251["prev"] = true;
    }
    if ($pagenumber < $var_242) {
        $var_254 = $pagenumber + 1;
        $var_255 = calculatePagination($var_254, $perpage, $results);
        $var_251["next"] = true;
    }
    $var_256 = "3";
    if (!isset($var_257) || !is_array($var_257)) {
        $var_258 = "10 50 100 500 1000";
        $var_257[] = preg_split("#\\s+#s", $var_258, -1, PREG_SPLIT_NO_EMPTY);
        while ($var_250++ < $var_242) {
        }
        $var_259 = isset($var_252) && $var_252 != 1 ? "page=" . $var_252 : "";
        $var_245 = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $var_242 . "</li>\r\n\t\t\t\t\t\t" . ($var_251["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $var_260["first"] . " to " . $var_260["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($var_251["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $var_253["first"] . " to " . $var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $var_245 . "\r\n\t\t\t\t\t\t" . ($var_251["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_254 . "\" $title = \"Next Page - Show Results " . $var_255["first"] . " to " . $var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($var_251["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_242 . "\" $title = \"Last Page - Show Results " . $var_261["first"] . " to " . $var_261["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$var_245, "LIMIT " . $var_243 . ", " . $perpage];
    }
    if ($var_256 <= abs($var_250 - $pagenumber) && $var_256 != 0) {
        if ($var_250 == 1) {
            $var_260 = calculatePagination(1, $perpage, $results);
            $var_251["first"] = true;
        }
        if ($var_250 == $var_242) {
            $var_261 = calculatePagination($var_242, $perpage, $results);
            $var_251["last"] = true;
        }
        if (in_array(abs($var_250 - $pagenumber), $var_257) && $var_250 != 1 && $var_250 != $var_242) {
            $var_262 = calculatePagination($var_250, $perpage, $results);
            $var_263 = $var_250 - $pagenumber;
            if (0 < $var_263) {
                $var_263 = "+" . $var_263;
            }
            $var_245 .= "<li><a class=\"smalltext\" $href = \"" . $address . ($var_250 != 1 ? "page=" . $var_250 : "") . "\" $title = \"Show results " . $var_262["first"] . " to " . $var_262["last"] . " of " . $total . "\"><!--" . $var_263 . "-->" . $var_250 . "</a></li>";
        }
    } else {
        if ($var_250 == $pagenumber) {
            $var_264 = calculatePagination($var_250, $perpage, $results);
            $var_245 .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $var_264["first"] . " to " . $var_264["last"] . " of " . $total . "\">" . $var_250 . "</a></li>";
        } else {
            $var_262 = calculatePagination($var_250, $perpage, $results);
            $var_245 .= "<li><a $href = \"" . $address . ($var_250 != 1 ? "page=" . $var_250 : "") . "\" $title = \"Show results " . $var_262["first"] . " to " . $var_262["last"] . " of " . $total . "\">" . $var_250 . "</a></li>";
        }
    }
}
function formatTimestamp($timestamp = "")
{
    $var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($var_265, $timestamp);
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_152($search, $subject, $hlstart = "<b><font $color = '#f7071d'>", $hlend = "</font></b>")
{
    $var_432 = strlen($search);
    if ($var_432 == 0) {
        return $subject;
    }
    $var_433 = $subject;
    while ($var_433 = stristr($var_433, $search)) {
        $var_434 = substr($var_433, 0, $var_432);
        $var_433 = substr($var_433, $var_432);
        $subject = str_replace($var_434, $hlstart . $var_434 . $hlend, $subject);
    }
    return $subject;
}

?>