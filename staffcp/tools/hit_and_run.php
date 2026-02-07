<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/hit_and_run.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$torrentid = isset($_GET["torrentid"]) ? intval($_GET["torrentid"]) : (isset($_POST["torrentid"]) ? intval($_POST["torrentid"]) : 0);
$alreadywarnedarrays = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'HITRUN'");
$Result = mysqli_fetch_assoc($query);
$HITRUN = unserialize($Result["content"]);
$whereClauses = [];
$hiddenFields = "";
$link = "";
$Message = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
$Result = mysqli_fetch_assoc($query);
$ANNOUNCE = unserialize($Result["content"]);
if ($ANNOUNCE["xbt_active"] == "yes") {
    echo "\r\n\t\r\n\t" . showAlertError($Language[3]);
    exit;
}
if ($Act == "manage_users" && isset($_POST["user_torrent_ids"]) && $_POST["user_torrent_ids"][0] != "") {
    $user_torrent_ids = $_POST["user_torrent_ids"];
    $timenow = time();
    if (isset($_POST["warn"])) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
        $Result = mysqli_fetch_assoc($query);
        $MAIN = unserialize($Result["content"]);
        $msgsbj = $Language[11];
        foreach ($user_torrent_ids as $work) {
            $parts = explode("|", $work);
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_hit_and_run (userid, torrentid, added) VALUES ('" . $parts[0] . "', '" . $parts[1] . "', '" . $timenow . "')");
            $msgbody = trim(str_replace("{1}", "[URL]" . $MAIN["BASEURL"] . "/details.php?$id = " . $parts[1] . "[/URL]", $Language[12]));
            var_237($parts[0], $msgbody, $msgsbj);
            $Modcomment = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], $parts[1]], $Language[17]);
            $Modcomment = gmdate("Y-m-d") . " - " . trim($Modcomment) . "\n";
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `timeswarned` = timeswarned + 1, $modcomment = CONCAT(\"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Modcomment) . "\", modcomment) WHERE `id` = '" . $parts[0] . "'");
        }
        $SysMsg = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], implode(",", $user_torrent_ids)], $Language[13]);
        logStaffAction($SysMsg);
    } else {
        if (isset($_POST["ban"])) {
            $userQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid FROM usergroups WHERE `isbanned` = 'yes'");
            $Result = mysqli_fetch_assoc($userQuery);
            $usergroupid = $Result["gid"];
            foreach ($user_torrent_ids as $work) {
                $parts = explode("|", $work);
                $Modcomment = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], $parts[1]], $Language[15]);
                $Modcomment = gmdate("Y-m-d") . " - " . trim($Modcomment) . "\n";
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `enabled` = 'no', $usergroup = '" . $usergroupid . "', $notifs = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Modcomment) . "', $modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Modcomment) . "', modcomment) WHERE `id` = '" . $parts[0] . "'");
            }
            $SysMsg = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], implode(",", $user_torrent_ids)], $Language[14]);
            logStaffAction($SysMsg);
        }
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id,userid,torrentid,added FROM ts_hit_and_run");
if (0 < mysqli_num_rows($query)) {
    $DeleteIds = [];
    while ($alreadywarned = mysqli_fetch_assoc($query)) {
        if (time() - 604800 < $alreadywarned["added"]) {
            $alreadywarnedarrays[$alreadywarned["userid"]][$alreadywarned["torrentid"]] = $alreadywarned["added"];
        } else {
            $DeleteIds[] = $alreadywarned["id"];
        }
    }
    if (0 < count($DeleteIds)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_hit_and_run WHERE id IN (0," . implode(",", $DeleteIds) . ")");
    }
}
if ($torrentid) {
    $whereClauses[] = "s.$torrentid = " . $torrentid;
    $hiddenFields = "<input $type = \"hidden\" $name = \"torrentid\" $value = \"" . $torrentid . "\" />";
    $link = "torrentid=" . $torrentid . "&amp;";
}
if (isset($_GET["page"]) && 0 < intval($_GET["page"])) {
    $hiddenFields .= "<input $type = \"hidden\" $name = \"page\" $value = \"" . intval($_GET["page"]) . "\" />";
}
if (isset($_GET["show_by_userid"]) && ($userid = intval($_GET["show_by_userid"]))) {
    $whereClauses[] = "u.$id = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $userid) . "'";
    $link = $link . "show_by_userid=" . $userid . "&amp;";
}
$keywords = "";
$searchtype = "";
if ($Act == "do_search") {
    $keywords = isset($_GET["keywords"]) ? trim($_GET["keywords"]) : (isset($_POST["keywords"]) ? trim($_POST["keywords"]) : "");
    if ($keywords) {
        $searchtype = isset($_GET["searchtype"]) ? intval($_GET["searchtype"]) : (isset($_POST["searchtype"]) ? intval($_POST["searchtype"]) : "");
        switch ($searchtype) {
            case "1":
                $whereClauses[] = "u.$username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "'";
                break;
            case "2":
                $whereClauses[] = "u.$id = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "'";
                break;
            case "3":
                $whereClauses[] = "s.$torrentid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "'";
                break;
            default:
                $link = $link . "act=do_search&amp;$keywords = " . htmlspecialchars($keywords) . "&amp;$searchtype = " . htmlspecialchars($searchtype) . "&amp;";
        }
    }
}
$whereClauses[] = "s.$finished = 'yes'";
$whereClauses[] = "s.$seeder = 'no'";
$whereClauses[] = "t.$banned = 'no'";
$whereClauses[] = "u.$enabled = 'yes'";
if ($HITRUN["Categories"]) {
    $whereClauses[] = "t.category IN (" . $HITRUN["Categories"] . ")";
}
if ($HITRUN["HRSkipUsergroups"]) {
    $whereClauses[] = "u.usergroup NOT IN (" . $HITRUN["HRSkipUsergroups"] . ")";
}
if (0 < $HITRUN["MinFinishDate"]) {
    $whereClauses[] = "UNIX_TIMESTAMP(s.completedat) > " . $HITRUN["MinFinishDate"];
}
if (0 < $HITRUN["MinSeedTime"]) {
    $whereClauses[] = "s.seedtime < " . $HITRUN["MinSeedTime"] * 60 * 60;
}
if (0 < $HITRUN["MinRatio"]) {
    $whereClauses[] = "s.uploaded / s.downloaded < " . $HITRUN["MinRatio"];
}
$FinishedQuery = "SELECT s.torrentid, s.userid, s.seedtime, t.name, u.username FROM snatched s INNER JOIN torrents t ON (s.`torrentid` = t.id) INNER JOIN users u ON (s.`userid` = u.id) WHERE " . implode(" AND ", $whereClauses);
$query = mysqli_query($GLOBALS["DatabaseConnect"], $FinishedQuery);
$total_count = mysqli_num_rows($query);
list($pagertop, $limit) = buildPaginationLinks(25, $total_count, "index.php?do=hit_and_run&amp;" . $link);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT s.torrentid, s.seedtime, s.leechtime, s.userid, s.downloaded, s.uploaded, t.name, t.seeders, t.leechers, u.timeswarned, u.username, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle FROM snatched s INNER JOIN users u ON (s.`userid` = u.id) LEFT JOIN ts_u_perm p ON (u.`id` = p.userid) LEFT JOIN torrents t ON (s.`torrentid` = t.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE " . implode(" AND ", $whereClauses) . " ORDER by u.timeswarned DESC " . $limit);
if (mysqli_num_rows($query) == 0) {
    $Message = showAlertError($Language[3]);
} else {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'CLEANUP'");
    $Result = mysqli_fetch_assoc($query);
    $HRConfig = unserialize($Result["content"]);
    $criticallimit = $HRConfig["ban_user_limit"] - 1;
    $Found = "";
    while ($user = mysqli_fetch_assoc($query)) {
        $totalwarns = "";
        if (isset($alreadywarnedarrays[$user["userid"]][$user["torrentid"]])) {
            $disabled = " disabled";
        } else {
            $disabled = " $checkme = \"group\"";
        }
        if ($user["timeswarned"] == 0) {
            $totalwarns = "<font $color = \"green\"><b>";
        } else {
            if ($user["timeswarned"] == $criticallimit) {
                $totalwarns = "<font $color = \"red\"><b>";
            } else {
                if ($ban_user_limit <= $user["timeswarned"]) {
                    $totalwarns = "<font $color = \"darkred\"><b>";
                }
            }
        }
        $ratio = number_format($user["uploaded"] / $user["downloaded"], 2);
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><a $href = \"index.php?do=hit_and_run&$show_by_userid = " . $user["userid"] . "\">" . applyUsernameStyle($user["username"], $user["namestyle"]) . "</a></td>\r\n\t\t\t<td class=\"alt1\"><a $href = \"index.php?do=hit_and_run&$torrentid = " . $user["torrentid"] . "\">" . $user["name"] . "</a></td>\r\n\t\t\t<td class=\"alt1\">" . var_238($user["uploaded"]) . " (" . function_85($user["seedtime"]) . ")</td>\r\n\t\t\t<td class=\"alt1\">" . var_238($user["downloaded"]) . " (" . function_85($user["leechtime"]) . ")</td>\r\n\t\t\t<td class=\"alt1\"><font $color = \"red\">" . $ratio . "</font></td>\r\n\t\t\t<td class=\"alt1\">" . $totalwarns . $user["timeswarned"] . "</b></font></td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\"><input $type = \"checkbox\" $name = \"user_torrent_ids[]\" $value = \"" . $user["userid"] . "|" . $user["torrentid"] . "|" . $ratio . "\"" . $disabled . " /></td>\r\n\t\t</td>\r\n\t\t";
    }
    $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"7\"><input $type = \"submit\" $value = \"" . $Language[19] . "\" $name = \"warn\" /> <input $type = \"submit\" $value = \"" . $Language[20] . "\" $name = \"ban\" /></td>\r\n\t\t</tr>";
}
$SearchForm = "\r\n<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=hit_and_run&$act = do_search" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . ($torrentid ? "&$torrentid = " . $torrentid : "") . "\" $method = \"post\" $name = \"hit_and_run_search\">\r\n<input $type = \"hidden\" $name = \"act\" $value = \"do_search\" />\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\"><b>" . $Language[2] . "</b></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[21] . ": <input $type = \"text\" $name = \"keywords\" $value = \"" . htmlspecialchars($keywords) . "\" />\r\n\t\t\t<select $name = \"searchtype\">\r\n\t\t\t\t<option $value = \"3\"" . ($searchtype == 3 ? " $selected = \"selected\"" : "") . ">" . $Language[22] . "</option>\r\n\t\t\t\t<option $value = \"2\"" . ($searchtype == 2 ? " $selected = \"selected\"" : "") . ">" . $Language[23] . "</option>\r\n\t\t\t\t<option $value = \"1\"" . ($searchtype == 1 ? " $selected = \"selected\"" : "") . ">" . $Language[24] . "</option>\r\n\t\t\t</select>\r\n\t\t\t <input $type = \"submit\" $value = \"" . $Language[25] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
$InfoMessage = showAlertMessage($Language[10]);
if ($Message) {
    echo "\r\n\t" . $InfoMessage . "\r\n\t" . $Message . $SearchForm;
} else {
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . $InfoMessage . "\r\n\t" . $SearchForm . "\r\n\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=hit_and_run&$act = manage_users" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . ($torrentid ? "&$torrentid = " . $torrentid : "") . "\" $method = \"post\" $name = \"hit_and_run\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"manage_users\" />\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"7\"><b>" . $Language[2] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('hit_and_run', this, 'group');\" /></td>\r\n\t\t</tr>\r\n\t\t" . (isset($Found) ? $Found : "") . "\r\n\t</table>\r\n\t" . $pagertop . "\r\n\t</form>\r\n\t";
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
function function_85($sec, $padHours = false)
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
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages\r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES\r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $receiver . "'");
    }
}

?>