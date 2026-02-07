<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/view_peers.lang");
$Message = "";
$ShowType = isset($_GET["type"]) && in_array($_GET["type"], ["s", "l"]) ? $_GET["type"] : "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
$Result = mysqli_fetch_assoc($query);
$ANNOUNCE = unserialize($Result["content"]);
if ($ANNOUNCE["xbt_active"] == "yes") {
    $results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM xbt_files_users WHERE $active = 1"));
} else {
    $results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM peers"));
}
list($pagertop, $limit) = buildPaginationLinks(30, $results, $_SERVER["SCRIPT_NAME"] . "?do=view_peers&amp;" . ($ShowType ? "type=" . $ShowType . "&amp;" : ""));
$Found = "";
if ($ANNOUNCE["xbt_active"] == "yes") {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT value FROM xbt_config WHERE $name = 'announce_interval'");
    $Result = mysqli_fetch_assoc($query);
    $xbt_announce_interval = $Result["value"];
    $ORDERBY = "p.uploaded DESC, p.downloaded DESC, p.announced DESC";
    if ($ShowType) {
        switch ($ShowType) {
            case "s":
                $ORDERBY = "p.left ASC";
                break;
            case "l":
                $ORDERBY = "p.left DESC";
                break;
        }
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT p.uploaded, p.downloaded, p.fid, p.uid, p.active, p.announced, p.completed, p.`left`, p.mtime, p.down_rate, p.up_rate, u.id, u.enabled, u.username, u.options, u.warned, u.donor, g.namestyle, tr.name as torrentname, tr.size FROM xbt_files_users p LEFT JOIN users u ON (p.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents tr ON (p.$fid = tr.id) WHERE p.$active = 1 ORDER by " . $ORDERBY . " " . $limit);
    if ($results) {
        while ($R = mysqli_fetch_assoc($query)) {
            $Left = 0 < $R["left"] ? round($R["size"] / $R["left"] * 100) . "%" : "";
            $seedtime = formatSecondsToTime($R["announced"] * $xbt_announce_interval);
            $ratio = 0 < $R["uploaded"] && 0 < $R["downloaded"] ? number_format($R["uploaded"] / $R["downloaded"], 2) : "0.0";
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $R["username"] . "\">" . applyUsernameStyle($R["username"], $R["namestyle"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"../details.php?$id = " . $R["fid"] . "\" $alt = \"" . htmlspecialchars($R["torrentname"]) . "\" $title = \"" . htmlspecialchars($R["torrentname"]) . "\">" . substr(htmlspecialchars($R["torrentname"]), 0, 20) . "...</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["uploaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["up_rate"]) . "/s\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["downloaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["down_rate"]) . "/s\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $ratio . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<font $color = \"" . (0 < $R["left"] ? "red\">" . $Language[19] : "green\">" . $Language[18]) . "</font>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatTimestamp($R["mtime"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . number_format($R["announced"]) . " x " . $Language[38] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $seedtime . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        echo "\r\n\t\t" . showAlertError($Language[17]);
    }
    echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=view_peers&amp;$type = l\">Leechers</a> | <a $href = \"index.php?do=view_peers&amp;$type = s\">Seeders</a>") . "\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"11\">" . $Language[2] . " (" . number_format($results) . ")</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[3] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[4] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[6] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[39] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[7] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[40] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[21] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[10] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[12] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[20] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[23] . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>\r\n\t" . $pagertop;
} else {
    $ORDERBY = "t.last_action DESC";
    if ($ShowType) {
        switch ($ShowType) {
            case "s":
                $ORDERBY = "t.seeder DESC";
                break;
            case "l":
                $ORDERBY = "t.seeder ASC";
                break;
        }
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.*,  u.username, g.namestyle, tr.name as torrentname FROM peers t LEFT JOIN users u ON (t.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents tr ON (t.$torrent = tr.id) ORDER by " . $ORDERBY . " " . $limit);
    if ($results) {
        while ($R = mysqli_fetch_assoc($query)) {
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $R["username"] . "\">" . applyUsernameStyle($R["username"], $R["namestyle"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"../details.php?$id = " . $R["torrent"] . "\" $alt = \"" . htmlspecialchars($R["torrentname"]) . "\" $title = \"" . htmlspecialchars($R["torrentname"]) . "\">" . substr(htmlspecialchars($R["torrentname"]), 0, 25) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($R["ip"]) . ":" . intval($R["port"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["uploaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["downloaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . substr(htmlspecialchars($R["peer_id"]), 0, 8) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $R["connectable"] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $R["seeder"] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatTimestamp($R["started"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatTimestamp($R["last_action"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatTimestamp($R["prev_action"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["uploadoffset"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["downloadoffset"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($R["to_go"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        echo "\r\n\t\t" . showAlertError($Language[17]);
    }
    echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=view_peers&amp;$type = l\">Leechers</a> | <a $href = \"index.php?do=view_peers&amp;$type = s\">Seeders</a>") . "\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"14\">" . $Language[2] . " (" . number_format($results) . ")</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[3] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[4] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[5] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[6] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[7] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[8] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[9] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[10] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[11] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[12] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[13] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[14] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[15] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[16] . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>\r\n\t" . $pagertop;
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
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
    global $Language;
    $var_270 = 31536000;
    $var_271 = 2678400;
    $var_272 = 604800;
    $var_273 = 86400;
    $var_274 = 3600;
    $var_275 = 60;
    $var_276 = floor($stamp / $var_270);
    $stamp %= $var_270;
    $var_277 = floor($stamp / $var_271);
    $stamp %= $var_271;
    $var_278 = floor($stamp / $var_272);
    $stamp %= $var_272;
    $var_279 = floor($stamp / $var_273);
    $stamp %= $var_273;
    $var_267 = floor($stamp / $var_274);
    $stamp %= $var_274;
    $var_268 = floor($stamp / $var_275);
    $stamp %= $var_275;
    $var_269 = $stamp;
    if ($var_276 == 1) {
        $var_280["years"] = "<b>1</b> " . $Language[24];
    } else {
        if (1 < $var_276) {
            $var_280["years"] = "<b>" . $var_276 . "</b> " . $Language[25];
        }
    }
    if ($var_277 == 1) {
        $var_280["months"] = "<b>1</b> " . $Language[26];
    } else {
        if (1 < $var_277) {
            $var_280["months"] = "<b>" . $var_277 . "</b> " . $Language[27];
        }
    }
    if ($var_278 == 1) {
        $var_280["weeks"] = "<b>1</b> " . $Language[28];
    } else {
        if (1 < $var_278) {
            $var_280["weeks"] = "<b>" . $var_278 . "</b> " . $Language[29];
        }
    }
    if ($var_279 == 1) {
        $var_280["days"] = "<b>1</b> " . $Language[30];
    } else {
        if (1 < $var_279) {
            $var_280["days"] = "<b>" . $var_279 . "</b> " . $Language[31];
        }
    }
    if ($var_267 == 1) {
        $var_280["hours"] = "<b>1</b> " . $Language[32];
    } else {
        if (1 < $var_267) {
            $var_280["hours"] = "<b>" . $var_267 . "</b> " . $Language[33];
        }
    }
    if ($var_268 == 1) {
        $var_280["minutes"] = "<b>1</b> " . $Language[34];
    } else {
        if (1 < $var_268) {
            $var_280["minutes"] = "<b>" . $var_268 . "</b> " . $Language[35];
        }
    }
    if ($var_269 == 1) {
        $var_280["seconds"] = "<b>1</b> " . $Language[36];
    } else {
        if (1 < $var_269) {
            $var_280["seconds"] = "<b>" . $var_269 . "</b> " . $Language[37];
        }
    }
    if (isset($var_280) && is_array($var_280)) {
        $total = implode(", ", $var_280);
    } else {
        $total = "0 " . $Language[36];
    }
    return "<small\">" . $total . "</small>";
}

?>