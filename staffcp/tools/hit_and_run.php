<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();
$Language = file("languages/" . getStaffLanguage() . "/hit_and_run.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$torrentid = isset($_GET["torrentid"]) ? intval($_GET["torrentid"]) : (isset($_POST["torrentid"]) ? intval($_POST["torrentid"]) : 0);
$alreadywarnedarrays = [];
$stmt = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = ?", ["HITRUN"]);
$Result = $stmt->fetch(PDO::FETCH_ASSOC);
$HITRUN = unserialize($Result["content"]);
$whereClauses = [];
$hiddenFields = "";
$link = "";
$Message = "";
$stmt = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = ?", ["ANNOUNCE"]);
$Result = $stmt->fetch(PDO::FETCH_ASSOC);
$ANNOUNCE = unserialize($Result["content"]);
if ($ANNOUNCE["xbt_active"] == "yes") {
    echo "\r\n\t\r\n\t" . showAlertErrorModern($Language[3]);
    exit;
}
if ($Act == "manage_users" && isset($_POST["user_torrent_ids"]) && $_POST["user_torrent_ids"][0] != "") {
    validateFormToken();
    $user_torrent_ids = $_POST["user_torrent_ids"];
    $timenow = time();
    if (isset($_POST["warn"])) {
        $stmt = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = ?", ["MAIN"]);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        $MAIN = unserialize($Result["content"]);
        $msgsbj = $Language[11];
        foreach ($user_torrent_ids as $work) {
            $parts = explode("|", $work);
            $TSDatabase->query("INSERT INTO ts_hit_and_run (userid, torrentid, added) VALUES (?, ?, ?)", [$parts[0], $parts[1], $timenow]);
            $msgbody = trim(str_replace("{1}", "[URL]" . $MAIN["BASEURL"] . "/details.php?$id = " . $parts[1] . "[/URL]", $Language[12]));
            sendPrivateMessage($parts[0], $msgbody, $msgsbj);
            $Modcomment = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], $parts[1]], $Language[17]);
            $Modcomment = gmdate("Y-m-d") . " - " . trim($Modcomment) . "\n";
            $TSDatabase->query("UPDATE users SET `timeswarned` = timeswarned + 1, modcomment = CONCAT(?, modcomment) WHERE `id` = ?", [$Modcomment, $parts[0]]);
        }
        $SysMsg = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], implode(",", $user_torrent_ids)], $Language[13]);
        logStaffActionModern($SysMsg);
    } else {
        if (isset($_POST["ban"])) {
            $stmt = $TSDatabase->query("SELECT gid FROM usergroups WHERE `isbanned` = 'yes'");
            $Result = $stmt->fetch(PDO::FETCH_ASSOC);
            $usergroupid = $Result["gid"];
            foreach ($user_torrent_ids as $work) {
                $parts = explode("|", $work);
                $Modcomment = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], $parts[1]], $Language[15]);
                $Modcomment = gmdate("Y-m-d") . " - " . trim($Modcomment) . "\n";
                $TSDatabase->query("UPDATE users SET `enabled` = 'no', usergroup = ?, notifs = ?, modcomment = CONCAT(?, modcomment) WHERE `id` = ?", [$usergroupid, $Modcomment, $Modcomment, $parts[0]]);
            }
            $SysMsg = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], implode(",", $user_torrent_ids)], $Language[14]);
            logStaffActionModern($SysMsg);
        }
    }
}
$stmt = $TSDatabase->query("SELECT id,userid,torrentid,added FROM ts_hit_and_run");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($results) > 0) {
    $DeleteIds = [];
    foreach ($results as $alreadywarned) {
        if (time() - 604800 < $alreadywarned["added"]) {
            $alreadywarnedarrays[$alreadywarned["userid"]][$alreadywarned["torrentid"]] = $alreadywarned["added"];
        } else {
            $DeleteIds[] = $alreadywarned["id"];
        }
    }
    if (count($DeleteIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($DeleteIds) + 1, '?'));
        $params = array_merge([0], $DeleteIds);
        $TSDatabase->query("DELETE FROM ts_hit_and_run WHERE id IN ($placeholders)", $params);
    }
}
if ($torrentid) {
    $whereClauses[] = "s.torrentid = " . $torrentid;
    $hiddenFields = "<input type=\"hidden\" name=\"torrentid\" value=\"" . escape_attr((string)$torrentid) . "\" />";
    $link = "torrentid=" . $torrentid . "&amp;";
}
if (isset($_GET["page"]) && 0 < intval($_GET["page"])) {
    $hiddenFields .= "<input type=\"hidden\" name=\"page\" value=\"" . escape_attr((string)intval($_GET["page"])) . "\" />";
}
if (isset($_GET["show_by_userid"]) && ($userid = intval($_GET["show_by_userid"]))) {
    $whereClauses[] = "u.id = " . $userid;
    $link = $link . "show_by_userid=" . $userid . "&amp;";
}
$keywords = "";
$searchtype = "";
if ($Act == "do_search") {
    $keywords = isset($_GET["keywords"]) ? trim($_GET["keywords"]) : (isset($_POST["keywords"]) ? trim($_POST["keywords"]) : "");
    if ($keywords) {
        $searchtype = isset($_GET["searchtype"]) ? intval($_GET["searchtype"]) : (isset($_POST["searchtype"]) ? intval($_POST["searchtype"]) : 0);
        switch ($searchtype) {
            case 1:
                $whereClauses[] = "u.username = '" . $keywords . "'";
                break;
            case 2:
                $whereClauses[] = "u.id = '" . $keywords . "'";
                break;
            case 3:
                $whereClauses[] = "s.torrentid = '" . $keywords . "'";
                break;
            default:
                $link = $link . "act=do_search&amp;keywords=" . escape_attr($keywords) . "&amp;searchtype=" . escape_attr((string)$searchtype) . "&amp;";
        }
    }
}
$whereClauses[] = "s.finished = 'yes'";
$whereClauses[] = "s.seeder = 'no'";
$whereClauses[] = "t.banned = 'no'";
$whereClauses[] = "u.enabled = 'yes'";
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
$stmt = $TSDatabase->query($FinishedQuery);
$total_count = count($stmt->fetchAll(PDO::FETCH_ASSOC));
list($pagertop, $limit) = buildPaginationLinks(25, $total_count, "index.php?do=hit_and_run&amp;" . $link);
$stmt = $TSDatabase->query("SELECT s.torrentid, s.seedtime, s.leechtime, s.userid, s.downloaded, s.uploaded, t.name, t.seeders, t.leechers, u.timeswarned, u.username, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle FROM snatched s INNER JOIN users u ON (s.`userid` = u.id) LEFT JOIN ts_u_perm p ON (u.`id` = p.userid) LEFT JOIN torrents t ON (s.`torrentid` = t.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE " . implode(" AND ", $whereClauses) . " ORDER by u.timeswarned DESC " . $limit);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($results) == 0) {
    $Message = showAlertErrorModern($Language[3]);
} else {
    $stmt = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = ?", ["CLEANUP"]);
    $Result = $stmt->fetch(PDO::FETCH_ASSOC);
    $HRConfig = unserialize($Result["content"]);
    $criticallimit = $HRConfig["ban_user_limit"] - 1;
    $Found = "";
    foreach ($results as $user) {
        $totalwarns = "";
        if (isset($alreadywarnedarrays[$user["userid"]][$user["torrentid"]])) {
            $disabled = " disabled";
        } else {
            $disabled = " checkme=\"group\"";
        }
        if ($user["timeswarned"] == 0) {
            $totalwarns = "<font color=\"green\"><b>";
        } else {
            if ($user["timeswarned"] == $criticallimit) {
                $totalwarns = "<font color=\"red\"><b>";
            } else {
                if (isset($ban_user_limit) && $ban_user_limit <= $user["timeswarned"]) {
                    $totalwarns = "<font color=\"darkred\"><b>";
                }
            }
        }
        $ratio = number_format($user["uploaded"] / $user["downloaded"], 2);
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><a href=\"index.php?do=hit_and_run&show_by_userid=" . escape_attr((string)$user["userid"]) . "\">" . escape_html(applyUsernameStyle($user["username"], $user["namestyle"])) . "</a></td>\r\n\t\t\t<td class=\"alt1\"><a href=\"index.php?do=hit_and_run&torrentid=" . escape_attr((string)$user["torrentid"]) . "\">" . escape_html($user["name"]) . "</a></td>\r\n\t\t\t<td class=\"alt1\">" . escape_html(formatBytes($user["uploaded"])) . " (" . escape_html(formatSecondsToTime($user["seedtime"])) . ")</td>\r\n\t\t\t<td class=\"alt1\">" . escape_html(formatBytes($user["downloaded"])) . " (" . escape_html(formatSecondsToTime($user["leechtime"])) . ")</td>\r\n\t\t\t<td class=\"alt1\"><font color=\"red\">" . escape_html($ratio) . "</font></td>\r\n\t\t\t<td class=\"alt1\">" . $totalwarns . escape_html((string)$user["timeswarned"]) . "</b></font></td>\r\n\t\t\t<td class=\"alt1\" align=\"center\"><input type=\"checkbox\" name=\"user_torrent_ids[]\" value=\"" . escape_attr($user["userid"] . "|" . $user["torrentid"] . "|" . $ratio) . "\"" . $disabled . " /></td>\r\n\t\t</td>\r\n\t\t";
    }
    $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" align=\"right\" colspan=\"7\"><input type=\"submit\" value=\"" . escape_attr($Language[19]) . "\" name=\"warn\" /> <input type=\"submit\" value=\"" . escape_attr($Language[20]) . "\" name=\"ban\" /></td>\r\n\t\t</tr>";
}
$SearchForm = "\r\n<form action=\"" . escape_attr($_SERVER["SCRIPT_NAME"]) . "?do=hit_and_run&act=do_search" . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "") . ($torrentid ? "&torrentid=" . $torrentid : "") . "\" method=\"post\" name=\"hit_and_run_search\">\r\n<input type=\"hidden\" name=\"act\" value=\"do_search\" />\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\"><b>" . escape_html($Language[2]) . "</b></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[21]) . ": <input type=\"text\" name=\"keywords\" value=\"" . escape_attr($keywords) . "\" />\r\n\t\t\t<select name=\"searchtype\">\r\n\t\t\t\t<option value=\"3\"" . ($searchtype == 3 ? " selected=\"selected\"" : "") . ">" . escape_html($Language[22]) . "</option>\r\n\t\t\t\t<option value=\"2\"" . ($searchtype == 2 ? " selected=\"selected\"" : "") . ">" . escape_html($Language[23]) . "</option>\r\n\t\t\t\t<option value=\"1\"" . ($searchtype == 1 ? " selected=\"selected\"" : "") . ">" . escape_html($Language[24]) . "</option>\r\n\t\t\t</select>\r\n\t\t\t <input type=\"submit\" value=\"" . escape_attr($Language[25]) . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
$InfoMessage = showAlertMessage($Language[10]);
if ($Message) {
    echo "\r\n\t" . $InfoMessage . "\r\n\t" . $Message . $SearchForm;
} else {
    echo "\r\n\t<script type=\"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar frm = document.forms[formname];\r\n\t\t\tfor(var i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . $InfoMessage . "\r\n\t" . $SearchForm . "\r\n\r\n\t<form action=\"" . escape_attr($_SERVER["SCRIPT_NAME"]) . "?do=hit_and_run&act=manage_users" . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "") . ($torrentid ? "&torrentid=" . $torrentid : "") . "\" method=\"post\" name=\"hit_and_run\">\r\n\t<input type=\"hidden\" name=\"act\" value=\"manage_users\" />\r\n\t" . generateFormToken() . "\r\n\t" . $pagertop . "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"7\"><b>" . escape_html($Language[2]) . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[4]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[5]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[6]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[7]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[8]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[9]) . "</b></td>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll ('hit_and_run', this, 'group');\" /></td>\r\n\t\t</tr>\r\n\t\t" . (isset($Found) ? $Found : "") . "\r\n\t</table>\r\n\t" . $pagertop . "\r\n\t</form>\r\n\t";
}
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}

function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href = \"" . escape_attr($url) . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . escape_attr($url) . "\" />\r\n\t\t</noscript>";
    }
    exit;
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
        $paginationLinks = "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td style=\"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . escape_html((string)$pagenumber) . " - " . escape_html((string)$queryResult) . "</li>\r\n\t\t\t\t\t\t" . (isset($paginationHtml["first"]) && $paginationHtml["first"] && isset($firstPageInfo) ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address) . "\" title=\"First Page - Show Results " . escape_attr($firstPageInfo["first"]) . " to " . escape_attr($firstPageInfo["last"]) . " of " . escape_attr($total) . "\">&laquo; First</a></li>" : "") . (isset($paginationHtml["prev"]) && $paginationHtml["prev"] && isset($previousPageInfo) ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . $previousPageQuery) . "\" title=\"Previous Page - Show Results " . escape_attr($previousPageInfo["first"]) . " to " . escape_attr($previousPageInfo["last"]) . " of " . escape_attr($total) . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . (isset($paginationHtml["next"]) && $paginationHtml["next"] && isset($nextPageInfo) && isset($nextPageNumber) ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . "page=" . $nextPageNumber) . "\" title=\"Next Page - Show Results " . escape_attr($nextPageInfo["first"]) . " to " . escape_attr($nextPageInfo["last"]) . " of " . escape_attr($total) . "\">&gt;</a></li>" : "") . (isset($paginationHtml["last"]) && $paginationHtml["last"] && isset($lastPageInfo) ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . "page=" . $queryResult) . "\" title=\"Last Page - Show Results " . escape_attr($lastPageInfo["first"]) . " to " . escape_attr($lastPageInfo["last"]) . " of " . escape_attr($total) . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
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
            $paginationLinks .= "<li><a class=\"smalltext\" href=\"" . escape_attr($address . ($currentPage != 1 ? "page=" . $currentPage : "")) . "\" title=\"Show results " . escape_attr($pageRangeInfo["first"]) . " to " . escape_attr($pageRangeInfo["last"]) . " of " . escape_attr($total) . "\"><!--" . escape_html((string)$pageOffsetDisplay) . "-->" . escape_html((string)$currentPage) . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a name=\"current\" class=\"current\" title=\"Showing results " . escape_attr($currentPageInfo["first"]) . " to " . escape_attr($currentPageInfo["last"]) . " of " . escape_attr($total) . "\">" . escape_html((string)$currentPage) . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a href=\"" . escape_attr($address . ($currentPage != 1 ? "page=" . $currentPage : "")) . "\" title=\"Show results " . escape_attr($pageRangeInfo["first"]) . " to " . escape_attr($pageRangeInfo["last"]) . " of " . escape_attr($total) . "\">" . escape_html((string)$currentPage) . "</a></li>";
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
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    global $TSDatabase;
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        $TSDatabase->query("INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)", [$sender, $receiver, $subject, $msg, $unread, $saved, $location]);
        $TSDatabase->query("UPDATE users SET pmunread = pmunread + 1 WHERE `id` = ?", [$receiver]);
    }
}

?>