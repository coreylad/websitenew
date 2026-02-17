<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();
$Language = loadStaffLanguage('view_peers');
$Message = "";
$ShowType = isset($_GET["type"]) && in_array($_GET["type"], ["s", "l"]) ? $_GET["type"] : "";

$result = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
$row = $result->fetch(PDO::FETCH_ASSOC);
$ANNOUNCE = unserialize($row["content"]);

if ($ANNOUNCE["xbt_active"] == "yes") {
    $countResult = $TSDatabase->query("SELECT COUNT(*) as count FROM xbt_files_users WHERE active = 1");
    $results = $countResult->fetch(PDO::FETCH_ASSOC)['count'];
} else {
    $countResult = $TSDatabase->query("SELECT COUNT(*) as count FROM peers");
    $results = $countResult->fetch(PDO::FETCH_ASSOC)['count'];
}
list($pagertop, $limit) = buildPaginationLinks(30, $results, $_SERVER["SCRIPT_NAME"] . "?do=view_peers&amp;" . ($ShowType ? "type=" . $ShowType . "&amp;" : ""));
$Found = "";
if ($ANNOUNCE["xbt_active"] == "yes") {
    $result = $TSDatabase->query("SELECT value FROM xbt_config WHERE name = 'announce_interval'");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $xbt_announce_interval = $row["value"];
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
    $query = $TSDatabase->query("SELECT p.uploaded, p.downloaded, p.fid, p.uid, p.active, p.announced, p.completed, p.`left`, p.mtime, p.down_rate, p.up_rate, u.id, u.enabled, u.username, u.options, u.warned, u.donor, g.namestyle, tr.name as torrentname, tr.size FROM xbt_files_users p LEFT JOIN users u ON (p.uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents tr ON (p.fid = tr.id) WHERE p.active = 1 ORDER by " . $ORDERBY . " " . $limit);
    if ($results) {
        while ($R = $query->fetch(PDO::FETCH_ASSOC)) {
            $Left = 0 < $R["left"] ? round($R["size"] / $R["left"] * 100) . "%" : "";
            $seedtime = formatSecondsToTime($R["announced"] * $xbt_announce_interval);
            $ratio = 0 < $R["uploaded"] && 0 < $R["downloaded"] ? number_format($R["uploaded"] / $R["downloaded"], 2) : "0.0";
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . escape_attr($R["username"]) . "\">" . applyUsernameStyle(escape_html($R["username"]), escape_html($R["namestyle"])) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a href=\"../details.php?id=" . (int)$R["fid"] . "\" alt=\"" . escape_attr($R["torrentname"]) . "\" title=\"" . escape_attr($R["torrentname"]) . "\">" . escape_html(substr($R["torrentname"], 0, 20)) . "...</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["uploaded"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["up_rate"])) . "/s\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["downloaded"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["down_rate"])) . "/s\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($ratio) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<font color=\"" . (0 < $R["left"] ? "red\">" . escape_html($Language[19]) : "green\">" . escape_html($Language[18])) . "</font>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatTimestamp($R["mtime"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(number_format($R["announced"])) . " x " . escape_html($Language[38]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $seedtime . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        echo "\r\n\t\t" . showAlertErrorModern($Language[17]);
    }
    echo "\r\n\t" . showAlertInfoModern("<a href=\"index.php?do=view_peers&amp;type=l\">Leechers</a> | <a href=\"index.php?do=view_peers&amp;type=s\">Seeders</a>") . "\r\n\t" . $pagertop . "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"11\">" . escape_html($Language[2]) . " (" . escape_html(number_format($results)) . ")</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[3]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[4]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[6]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[39]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[7]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[40]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[21]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[10]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[12]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[20]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[23]) . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>\r\n\t" . $pagertop;
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
    $query = $TSDatabase->query("SELECT t.*,  u.username, g.namestyle, tr.name as torrentname FROM peers t LEFT JOIN users u ON (t.userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents tr ON (t.torrent = tr.id) ORDER by " . $ORDERBY . " " . $limit);
    if ($results) {
        while ($R = $query->fetch(PDO::FETCH_ASSOC)) {
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . escape_attr($R["username"]) . "\">" . applyUsernameStyle(escape_html($R["username"]), escape_html($R["namestyle"])) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a href=\"../details.php?id=" . (int)$R["torrent"] . "\" alt=\"" . escape_attr($R["torrentname"]) . "\" title=\"" . escape_attr($R["torrentname"]) . "\">" . escape_html(substr($R["torrentname"], 0, 25)) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($R["ip"]) . ":" . (int)$R["port"] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["uploaded"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["downloaded"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(substr($R["peer_id"], 0, 8)) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($R["connectable"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($R["seeder"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatTimestamp($R["started"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatTimestamp($R["last_action"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatTimestamp($R["prev_action"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["uploadoffset"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["downloadoffset"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($R["to_go"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        echo "\r\n\t\t" . showAlertErrorModern($Language[17]);
    }
    echo "\r\n\t" . showAlertInfoModern("<a href=\"index.php?do=view_peers&amp;type=l\">Leechers</a> | <a href=\"index.php?do=view_peers&amp;type=s\">Seeders</a>") . "\r\n\t" . $pagertop . "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"14\">" . escape_html($Language[2]) . " (" . escape_html(number_format($results)) . ")</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[3]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[4]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[5]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[6]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[7]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[8]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[9]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[10]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[11]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[12]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[13]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[14]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[15]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[16]) . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>\r\n\t" . $pagertop;
}

function formatSecondsToTime($sec, $padHours = false)
{
    global $Language;
    $dateFrom = 31536000;
    $dateTo = 2678400;
    $dateFormatted = 604800;
    $timeStamp = 86400;
    $duration = 3600;
    $durationFormatted = 60;
    $timeDiff = floor($sec / $dateFrom);
    $sec %= $dateFrom;
    $timeString = floor($sec / $dateTo);
    $sec %= $dateTo;
    $timeDisplay = floor($sec / $dateFormatted);
    $sec %= $dateFormatted;
    $periodDisplay = floor($sec / $timeStamp);
    $sec %= $timeStamp;
    $endTime = floor($sec / $duration);
    $sec %= $duration;
    $timeRange = floor($sec / $durationFormatted);
    $sec %= $durationFormatted;
    $timePeriod = $sec;
    $timePeriodDisplay = [];
    if ($timeDiff == 1) {
        $timePeriodDisplay["years"] = "<b>1</b> " . escape_html($Language[24]);
    } else {
        if (1 < $timeDiff) {
            $timePeriodDisplay["years"] = "<b>" . $timeDiff . "</b> " . escape_html($Language[25]);
        }
    }
    if ($timeString == 1) {
        $timePeriodDisplay["months"] = "<b>1</b> " . escape_html($Language[26]);
    } else {
        if (1 < $timeString) {
            $timePeriodDisplay["months"] = "<b>" . $timeString . "</b> " . escape_html($Language[27]);
        }
    }
    if ($timeDisplay == 1) {
        $timePeriodDisplay["weeks"] = "<b>1</b> " . escape_html($Language[28]);
    } else {
        if (1 < $timeDisplay) {
            $timePeriodDisplay["weeks"] = "<b>" . $timeDisplay . "</b> " . escape_html($Language[29]);
        }
    }
    if ($periodDisplay == 1) {
        $timePeriodDisplay["days"] = "<b>1</b> " . escape_html($Language[30]);
    } else {
        if (1 < $periodDisplay) {
            $timePeriodDisplay["days"] = "<b>" . $periodDisplay . "</b> " . escape_html($Language[31]);
        }
    }
    if ($endTime == 1) {
        $timePeriodDisplay["hours"] = "<b>1</b> " . escape_html($Language[32]);
    } else {
        if (1 < $endTime) {
            $timePeriodDisplay["hours"] = "<b>" . $endTime . "</b> " . escape_html($Language[33]);
        }
    }
    if ($timeRange == 1) {
        $timePeriodDisplay["minutes"] = "<b>1</b> " . escape_html($Language[34]);
    } else {
        if (1 < $timeRange) {
            $timePeriodDisplay["minutes"] = "<b>" . $timeRange . "</b> " . escape_html($Language[35]);
        }
    }
    if ($timePeriod == 1) {
        $timePeriodDisplay["seconds"] = "<b>1</b> " . escape_html($Language[36]);
    } else {
        if (1 < $timePeriod) {
            $timePeriodDisplay["seconds"] = "<b>" . $timePeriod . "</b> " . escape_html($Language[37]);
        }
    }
    if (isset($timePeriodDisplay) && is_array($timePeriodDisplay) && count($timePeriodDisplay) > 0) {
        $total = implode(", ", $timePeriodDisplay);
    } else {
        $total = "0 " . escape_html($Language[36]);
    }
    return "<small>" . $total . "</small>";
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
        $paginationLinks = "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td style=\"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address) . "\" title=\"First Page - Show Results " . escape_attr($firstPageInfo["first"]) . " to " . escape_attr($firstPageInfo["last"]) . " of " . escape_attr($total) . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . $previousPageQuery) . "\" title=\"Previous Page - Show Results " . escape_attr($previousPageInfo["first"]) . " to " . escape_attr($previousPageInfo["last"]) . " of " . escape_attr($total) . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address) . "page=" . $nextPageNumber . "\" title=\"Next Page - Show Results " . escape_attr($nextPageInfo["first"]) . " to " . escape_attr($nextPageInfo["last"]) . " of " . escape_attr($total) . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address) . "page=" . $queryResult . "\" title=\"Last Page - Show Results " . escape_attr($lastPageInfo["first"]) . " to " . escape_attr($lastPageInfo["last"]) . " of " . escape_attr($total) . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
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
            $paginationLinks .= "<li><a class=\"smalltext\" href=\"" . escape_attr($address) . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" title=\"Show results " . escape_attr($pageRangeInfo["first"]) . " to " . escape_attr($pageRangeInfo["last"]) . " of " . escape_attr($total) . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a name=\"current\" class=\"current\" title=\"Showing results " . escape_attr($currentPageInfo["first"]) . " to " . escape_attr($currentPageInfo["last"]) . " of " . escape_attr($total) . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a href=\"" . escape_attr($address) . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" title=\"Show results " . escape_attr($pageRangeInfo["first"]) . " to " . escape_attr($pageRangeInfo["last"]) . " of " . escape_attr($total) . "\">" . $currentPage . "</a></li>";
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

?>