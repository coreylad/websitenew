<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/unconnectable_peers.lang");
$Message = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
$Result = mysqli_fetch_assoc($query);
$ANNOUNCE = unserialize($Result["content"]);
if ($ANNOUNCE["xbt_active"] == "yes") {
    echo "\r\n\t" . showAlertError($Language[1]);
    $STOP = true;
}
if (!isset($STOP)) {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $userids = isset($_POST["userids"]) ? $_POST["userids"] : "";
        $ips = isset($_POST["ips"]) ? $_POST["ips"] : "";
        $ports = isset($_POST["ports"]) ? $_POST["ports"] : "";
        if ($userids[0] != "" && $ips[0] != "" && $ports[0] != "") {
            if (isset($_POST["check"])) {
                echo "\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                $DoneArray = [];
                for ($i = 0; $i <= count($ips); $i++) {
                    if (isset($ips[$i]) && $ips[$i] != "" && !in_array($ips[$i], $DoneArray) && isset($ports[$i]) && $ports[$i]) {
                        $DoneArray[] = $ips[$i];
                        $ip_address = long2ip(ip2long($ips[$i]));
                        $port = intval($ports[$i]);
                        echo "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t\t" . $Language[17] . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t\t" . htmlspecialchars($ip_address) . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t";
                        if (!($result = function_147($ip_address, $port))) {
                            $resultmsg = $Language[19];
                        } else {
                            $resultmsg = $Language[18];
                        }
                        echo "\r\n\t\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t\t" . $resultmsg . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t";
                    }
                }
                echo "\r\n\t\t\t\t</table>";
            } else {
                $DoneArray = [];
                foreach ($userids as $id) {
                    if (!in_array($id, $DoneArray)) {
                        $DoneArray[] = $id;
                        sendPrivateMessage($id, $Language[21], $Language[20], $_SESSION["ADMIN_ID"]);
                    }
                }
                $Message = showAlertError($Language[22]);
            }
        }
    }
    $results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM peers WHERE $connectable = 'no'"));
    list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=unconnectable_peers&amp;");
    $sql = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT p.torrent, p.userid, p.agent, p.started, p.last_action, p.ip, p.port, p.seeder, p.uploaded, p.downloaded, p.uploadoffset, p.downloadoffset, u.username, g.namestyle, t.name FROM peers p  INNER JOIN users u ON (p.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents t ON (p.$torrent = t.id) WHERE p.$connectable = 'no' AND u.$enabled = 'yes' AND g.$isbanned = 'no' ORDER BY u.username " . $limit);
    if (mysqli_num_rows($sql) == 0) {
        echo "\r\n\t\t\r\n\t\t" . showAlertError($Language[1]);
    } else {
        $Found = "";
        while ($User = mysqli_fetch_assoc($sql)) {
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $User["username"] . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($User["ip"]) . ":" . intval($User["port"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($User["agent"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"../details.php?$id = " . $User["torrent"] . "\" $alt = \"" . htmlspecialchars($User["name"]) . "\">" . htmlspecialchars(substr($User["name"], 0, 25)) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $User["seeder"] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatTimestamp($User["started"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatTimestamp($User["last_action"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($User["uploaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($User["downloaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($User["uploadoffset"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($User["downloadoffset"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"userids[]\" $value = \"" . intval($User["userid"]) . "\" $checkme = \"group\"  $checked = \"checked\" />\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"ips[]\" $value = \"" . htmlspecialchars($User["ip"]) . "\" $checkme = \"group\"  $checked = \"checked\" />\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"ports[]\" $value = \"" . intval($User["port"]) . "\" $checkme = \"group\"  $checked = \"checked\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t\t{\r\n\t\t\t\tvar $frm = document.forms[formname];\r\n\t\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<form $action = \"index.php?do=unconnectable_peers" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"unconnectable_peers\">\r\n\t\t\r\n\t\t" . $Message . "\r\n\t\t" . $pagertop . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"12\"><b>" . str_replace("{1}", number_format($results), $Language[3]) . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[11] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[10] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('unconnectable_peers', this, 'group');\"></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" $colspan = \"12\" $align = \"right\">\r\n\t\t\t\t\t<input $type = \"submit\" $name = \"message\" $value = \"" . $Language[15] . "\" /> <input $type = \"submit\" $name = \"check\" $value = \"" . $Language[16] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t" . $pagertop;
    }
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
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
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
    if (!isset($var_257) || !is_array($var_257)) {
        $var_258 = "10 50 100 500 1000";
        $var_257[] = preg_split("#\\s+#s", $var_258, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $var_259 = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $var_260["first"] . " to " . $var_260["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $var_253["first"] . " to " . $var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_254 . "\" $title = \"Next Page - Show Results " . $var_255["first"] . " to " . $var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $var_261["first"] . " to " . $var_261["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($var_256 <= abs($currentPage - $pagenumber) && $var_256 != 0) {
        if ($currentPage == 1) {
            $var_260 = calculatePagination(1, $perpage, $results);
            $paginationHtml["first"] = true;
        }
        if ($currentPage == $queryResult) {
            $var_261 = calculatePagination($queryResult, $perpage, $results);
            $paginationHtml["last"] = true;
        }
        if (in_array(abs($currentPage - $pagenumber), $var_257) && $currentPage != 1 && $currentPage != $queryResult) {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $pageOffsetDisplay = $currentPage - $pagenumber;
            if (0 < $pageOffsetDisplay) {
                $pageOffsetDisplay = "+" . $pageOffsetDisplay;
            }
            $paginationLinks .= "<li><a class=\"smalltext\" $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $var_264 = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $var_264["first"] . " to " . $var_264["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
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
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function function_147($ip = "", $port = "", $timeout = 5)
{
    return !($var_422 = @fsockopen($ip, $port, fsockError, fsockErrorStr, $timeout)) ? false : (@fclose($var_422) ? true : true);
}
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages\r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES\r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $receiver . "'");
    }
}

?>