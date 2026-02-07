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
        $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $R["username"] . "\">" . applyUsernameStyle($R["username"], $R["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($R["added"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"../details.php?$id = " . $R["torrentid"] . "\">" . $R["torrentname"] . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($ANNOUNCE["xbt_active"] == "yes" ? "----" : htmlspecialchars($R["agent"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . var_238($R["transfer_rate"]) . "/s\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . var_238($R["upthis"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($ANNOUNCE["xbt_active"] == "yes" ? var_239($R["announced"] * $xbt_announce_interval) : formatSecondsToTime($R["timediff"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($ANNOUNCE["xbt_active"] == "yes" ? long2ip($R["ip"]) : htmlspecialchars($R["ip"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"ids[]\" $value = \"" . $R["uid"] . "\" $checkme = \"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
} else {
    echo "\r\n\t\r\n\t" . showAlertError($Language[19]);
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"index.php?do=cheat_attempts" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"cheat_attempts\">\r\n" . $Message . "\r\n" . $pagertop . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"9\">" . $Language[2] . " " . ($ANNOUNCE["xbt_active"] == "yes" ? "Min. " . var_238($ANNOUNCE["max_rate"]) . "/s" : "") . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[4] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[5] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[7] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[8] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[9] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[10] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t<input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('cheat_attempts', this, 'group');\">\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $colspan = \"9\" $align = \"right\">\r\n\t\t\t" . ($ANNOUNCE["xbt_active"] == "yes" ? "" : "<input $type = \"submit\" $name = \"delete\" $value = \"" . $Language[13] . "\" /> ") . "<input $type = \"submit\" $name = \"warn\" $value = \"" . $Language[12] . "\" /> <input $type = \"submit\" $name = \"ban\" $value = \"" . $Language[11] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n" . $pagertop . "\r\n</form>";
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
        $var_245 = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $var_242 . "</li>\r\n\t\t\t\t\t\t" . ($var_251["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $var_260["first"] . " to " . $var_260["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($var_251["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $var_253["first"] . " to " . $var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $var_245 . "\r\n\t\t\t\t\t\t" . ($var_251["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_254 . "\" $title = \"Next Page - Show Results " . $var_255["first"] . " to " . $var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($var_251["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_242 . "\" $title = \"Last Page - Show Results " . $var_261["first"] . " to " . $var_261["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
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
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages \r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES \r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $receiver . "'");
    }
}
function formatSecondsToTime($sec, $padHours = false)
{
    $var_266 = "";
    $var_267 = intval(intval($sec) / 3600);
    $var_266 .= $padHours ? str_pad($var_267, 2, "0", STR_PAD_LEFT) . ":" : $var_267 . ":";
    $var_268 = intval($sec / 60 % 60);
    $var_266 .= str_pad($var_268, 2, "0", STR_PAD_LEFT) . ":";
    $var_269 = intval($sec % 60);
    $var_266 .= str_pad($var_269, 2, "0", STR_PAD_LEFT);
    return $var_266;
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>