<?php

declare(strict_types=1);

require_once '../staffcp_modern.php';

// Check staff authentication
$staffUser = checkStaffAuthenticationModern();

// Load language file
$Language = loadStaffLanguage('unconnectable_peers');
$Message = "";

// Get ANNOUNCE configuration
$stmt = $TSDatabase->query(
    "SELECT `content` FROM `ts_config` WHERE `configname` = ?",
    ['ANNOUNCE']
);
$Result = $stmt->fetch(PDO::FETCH_ASSOC);
$ANNOUNCE = unserialize($Result["content"]);

if ($ANNOUNCE["xbt_active"] === "yes") {
    echo "\r\n\t" . showAlertErrorModern($Language[1]);
    $STOP = true;
}

if (!isset($STOP)) {
    if (strtoupper($_SERVER["REQUEST_METHOD"] ?? '') === "POST") {
        // CSRF protection
        $formToken = $_POST['form_token'] ?? '';
        if (!validateFormToken($formToken)) {
            echo showAlertErrorModern("Invalid form token. Please try again.");
            exit;
        }
        
        // Get POST data with null coalescing and type casting
        $userids = $_POST["userids"] ?? [];
        $ips = $_POST["ips"] ?? [];
        $ports = $_POST["ports"] ?? [];
        
        if (!empty($userids[0]) && !empty($ips[0]) && !empty($ports[0])) {
            if (isset($_POST["check"])) {
                echo "\r\n\t\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"3\"><b>" . escape_html($Language[16]) . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                $DoneArray = [];
                for ($i = 0; $i <= count($ips); $i++) {
                    if (isset($ips[$i]) && $ips[$i] !== "" && !in_array($ips[$i], $DoneArray) && isset($ports[$i]) && $ports[$i]) {
                        $DoneArray[] = $ips[$i];
                        $ip_address = long2ip(ip2long($ips[$i]));
                        $port = (int)$ports[$i];
                        echo "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t\t" . escape_html($Language[17]) . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t\t" . escape_html($ip_address) . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t";
                        if (!checkPeerConnectivity($ip_address, $port)) {
                            $resultmsg = $Language[19];
                        } else {
                            $resultmsg = $Language[18];
                        }
                        echo "\r\n\t\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t\t" . escape_html($resultmsg) . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t";
                    }
                }
                echo "\r\n\t\t\t\t</table>";
            } else {
                $DoneArray = [];
                foreach ($userids as $id) {
                    $userId = (int)$id;
                    if (!in_array($userId, $DoneArray)) {
                        $DoneArray[] = $userId;
                        sendPrivateMessageModern($userId, $Language[21], $Language[20], (int)$_SESSION["ADMIN_ID"]);
                    }
                }
                $Message = showAlertErrorModern($Language[22]);
            }
        }
    }
    
    // Get total count of unconnectable peers
    $countStmt = $TSDatabase->query("SELECT COUNT(*) as cnt FROM peers WHERE connectable = 'no'");
    $results = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=unconnectable_peers&amp;");
    
    // Get unconnectable peers
    $sql = $TSDatabase->query(
        "SELECT p.torrent, p.userid, p.agent, p.started, p.last_action, p.ip, p.port, p.seeder, 
                p.uploaded, p.downloaded, p.uploadoffset, p.downloadoffset, u.username, g.namestyle, t.name 
         FROM peers p  
         INNER JOIN users u ON (p.userid = u.id) 
         LEFT JOIN usergroups g ON (u.usergroup = g.gid) 
         LEFT JOIN torrents t ON (p.torrent = t.id) 
         WHERE p.connectable = 'no' AND u.enabled = 'yes' AND g.isbanned = 'no' 
         ORDER BY u.username " . $limit
    );
    
    if ($sql->rowCount() === 0) {
        echo "\r\n\t\t\r\n\t\t" . showAlertErrorModern($Language[1]);
    } else {
        $Found = "";
        while ($User = $sql->fetch(PDO::FETCH_ASSOC)) {
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . escape_attr($User["username"]) . "\">" . applyUsernameStyleModern($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($User["ip"]) . ":" . (int)$User["port"] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($User["agent"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a href=\"../details.php?id=" . (int)$User["torrent"] . "\" alt=\"" . escape_attr($User["name"]) . "\">" . escape_html(substr($User["name"], 0, 25)) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($User["seeder"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatTimestamp($User["started"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatTimestamp($User["last_action"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes((int)$User["uploaded"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes((int)$User["downloaded"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes((int)$User["uploadoffset"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes((int)$User["downloadoffset"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t\t<input type=\"checkbox\" name=\"userids[]\" value=\"" . (int)$User["userid"] . "\" checkme=\"group\" checked=\"checked\" />\r\n\t\t\t\t\t<input type=\"checkbox\" name=\"ips[]\" value=\"" . escape_attr($User["ip"]) . "\" checkme=\"group\" checked=\"checked\" />\r\n\t\t\t\t\t<input type=\"checkbox\" name=\"ports[]\" value=\"" . (int)$User["port"] . "\" checkme=\"group\" checked=\"checked\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t\t{\r\n\t\t\t\tvar frm = document.forms[formname];\r\n\t\t\t\tfor(var i = 0;i<frm.length;i++)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\tfrm.elements[i].checked = elm.checked;\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<form action=\"index.php?do=unconnectable_peers" . (isset($_GET["page"]) ? "&page=" . (int)$_GET["page"] : "") . "\" method=\"post\" name=\"unconnectable_peers\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t" . $Message . "\r\n\t\t" . $pagertop . "\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"12\"><b>" . escape_html(str_replace("{1}", number_format($results), $Language[3])) . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[4]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[5]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[11]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[10]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[6]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[7]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[12]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[8]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[9]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[13]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[14]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" align=\"center\"><input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll ('unconnectable_peers', this, 'group');\"></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" colspan=\"12\" align=\"right\">\r\n\t\t\t\t\t<input type=\"submit\" name=\"message\" value=\"" . escape_attr($Language[15]) . "\" /> <input type=\"submit\" name=\"check\" value=\"" . escape_attr($Language[16]) . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t" . $pagertop;
    }
}

/**
 * Format bytes with proper units
 * 
 * @param int $bytes Number of bytes
 * @return string Formatted string
 */
function formatBytes(int $bytes = 0): string
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 1099511627776) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 1099511627776, 2) . " TB";
}

/**
 * Validate per page settings for pagination
 * 
 * @param int $numresults Total results
 * @param int $page Page number
 * @param int $perpage Items per page
 * @param int $maxperpage Maximum per page
 * @param int $defaultperpage Default per page
 * @return void
 */
function validatePerPage(int $numresults, int &$page, int &$perpage, int $maxperpage = 20, int $defaultperpage = 20): void
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $totalPages = (int)ceil($numresults / $perpage);
    if ($totalPages === 0) {
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

/**
 * Calculate pagination range
 * 
 * @param int $pagenumber Page number
 * @param int $perpage Items per page
 * @param int $total Total items
 * @return array First and last item numbers
 */
function calculatePagination(int $pagenumber, int $perpage, int $total): array
{
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    $paginationFirstItem++;
    return ["first" => number_format($paginationFirstItem), "last" => number_format($paginationLastItem)];
}

/**
 * Build pagination links
 * 
 * @param int $perpage Items per page
 * @param int $results Total results
 * @param string $address Base URL
 * @return array Pagination HTML and LIMIT clause
 */
function buildPaginationLinks(int $perpage, int $results, string $address): array
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $queryResult = @ceil($results / $perpage);
    } else {
        $queryResult = 0;
    }
    $pagenumber = (int)($_GET["page"] ?? $_POST["page"] ?? 1);
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
        if (in_array(abs($currentPage - $pagenumber), $paginationSkipLinksArray) && $currentPage !== 1 && $currentPage !== $queryResult) {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $pageOffsetDisplay = $currentPage - $pagenumber;
            if (0 < $pageOffsetDisplay) {
                $pageOffsetDisplay = "+" . $pageOffsetDisplay;
            }
            $paginationLinks .= "<li><a class=\"smalltext\" href=\"" . escape_attr($address . ($currentPage !== 1 ? "page=" . $currentPage : "")) . "\" title=\"Show results " . escape_attr($pageRangeInfo["first"]) . " to " . escape_attr($pageRangeInfo["last"]) . " of " . escape_attr($total) . "\"><!--" . escape_html($pageOffsetDisplay) . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage === $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a name=\"current\" class=\"current\" title=\"Showing results " . escape_attr($currentPageInfo["first"]) . " to " . escape_attr($currentPageInfo["last"]) . " of " . escape_attr($total) . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a href=\"" . escape_attr($address . ($currentPage !== 1 ? "page=" . $currentPage : "")) . "\" title=\"Show results " . escape_attr($pageRangeInfo["first"]) . " to " . escape_attr($pageRangeInfo["last"]) . " of " . escape_attr($total) . "\">" . $currentPage . "</a></li>";
        }
    }
}

/**
 * Format timestamp to readable date
 * 
 * @param string|int $timestamp Unix timestamp or date string
 * @return string Formatted date
 */
function formatTimestamp(string|int $timestamp = ""): string
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (is_string($timestamp) && strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, (int)$timestamp);
}

/**
 * Apply username styling with proper escaping
 * 
 * @param string $username Username
 * @param string $namestyle Style template
 * @return string Styled username HTML
 */
function applyUsernameStyleModern(string $username, string $namestyle): string
{
    return str_replace("{username}", escape_html($username), $namestyle);
}

/**
 * Check if peer is connectable
 * 
 * @param string $ip IP address
 * @param int $port Port number
 * @param int $timeout Timeout in seconds
 * @return bool True if connectable
 */
function checkPeerConnectivity(string $ip, int $port, int $timeout = 5): bool
{
    $peerData = @fsockopen($ip, $port, $fsockError, $fsockErrorStr, $timeout);
    if (!$peerData) {
        return false;
    }
    @fclose($peerData);
    return true;
}

/**
 * Send private message using PDO
 * 
 * @param int $receiver Receiver user ID
 * @param string $msg Message content
 * @param string $subject Message subject
 * @param int $sender Sender user ID
 * @param string $saved Saved flag
 * @param string $location Location flag
 * @param string $unread Unread flag
 * @return bool Success status
 */
function sendPrivateMessageModern(int $receiver, string $msg, string $subject, int $sender, string $saved = "no", string $location = "1", string $unread = "yes"): bool
{
    global $TSDatabase;
    
    if ($sender === 0 || $receiver === 0 || empty($msg)) {
        return false;
    }
    
    try {
        $TSDatabase->query(
            "INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location) 
             VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)",
            [$sender, $receiver, $subject, $msg, $unread, $saved, $location]
        );
        
        $TSDatabase->query(
            "UPDATE users SET pmunread = pmunread + 1 WHERE id = ?",
            [$receiver]
        );
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to send private message: " . $e->getMessage());
        return false;
    }
}

?>