<?php
declare(strict_types=1);

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/warned_users.lang");
$Message = "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST" && isset($_POST["ids"]) && is_array($_POST["ids"]) && !empty($_POST["ids"])) {
    if (!isset($_POST["csrf_token"]) || !hash_equals($_SESSION["csrf_token"] ?? "", $_POST["csrf_token"])) {
        die("CSRF token validation failed");
    }
    
    try {
        $db = $GLOBALS["DatabaseConnect"];
        $ids = array_map('intval', $_POST["ids"]);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $SysMsg = str_replace("{1}", htmlspecialchars($_SESSION["ADMIN_USERNAME"] ?? '', ENT_QUOTES, 'UTF-8'), $Language[16]);
        $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
        
        $stmt = $db->prepare("UPDATE users SET warned = ?, warneduntil = ?, leechwarn = ?, leechwarnuntil = ?, modcomment = CONCAT(?, modcomment) WHERE id IN ($placeholders)");
        $params = array_merge(['no', '0000-00-00 00:00:00', 'no', '0000-00-00 00:00:00', $modcomment], $ids);
        $stmt->bind_param(str_repeat('s', 5) . str_repeat('i', count($ids)), ...$params);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            foreach ($ids as $id) {
                sendPrivateMessage($id, $SysMsg, $Language[15]);
            }
            $SysMsg = str_replace(["{1}", "{2}"], [htmlspecialchars($_SESSION["ADMIN_USERNAME"] ?? '', ENT_QUOTES, 'UTF-8'), implode(",", $ids)], $Language[17]);
            logStaffAction($SysMsg);
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Warned users error: " . $e->getMessage());
        $Message = showAlertError("An error occurred while processing your request");
    }
}
try {
    $db = $GLOBALS["DatabaseConnect"];
    $countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE (u.warned = ? OR u.leechwarn = ?) AND u.enabled = ? AND g.isbanned = ?");
    $yes = 'yes';
    $no = 'no';
    $countStmt->bind_param('ssss', $yes, $yes, $yes, $no);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $results = $countResult->fetch_assoc()['cnt'];
    $countStmt->close();
    
    list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=warned_users&amp;");
    
    preg_match('/LIMIT\s+(\d+),\s*(\d+)/', $limit, $matches);
    $offset = (int)($matches[1] ?? 0);
    $perPage = (int)($matches[2] ?? 25);
    
    $stmt = $db->prepare("SELECT u.id, u.username, u.last_access, u.email, u.ip, u.uploaded, u.downloaded, u.invites, u.seedbonus, u.warned, u.warneduntil, u.leechwarn, u.leechwarnuntil, g.title, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.enabled = ? AND (u.warned = ? OR u.leechwarn = ?) AND g.isbanned = ? ORDER BY u.username LIMIT ?, ?");
    $stmt->bind_param('ssssii', $yes, $yes, $yes, $no, $offset, $perPage);
    $stmt->execute();
    $sql = $stmt->get_result();
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo showAlertError("Database error occurred");
    exit;
}
if ($sql->num_rows == 0) {
    echo "\r\n\t\r\n\t" . showAlertError($Language[1]);
} else {
    $Found = "";
    while ($User = $sql->fetch_assoc()) {
        $username = htmlspecialchars($User["username"], ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($User["title"], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($User["email"], ENT_QUOTES, 'UTF-8');
        $ip = htmlspecialchars($User["ip"], ENT_QUOTES, 'UTF-8');
        $userId = (int)$User["id"];
        $seedbonus = htmlspecialchars($User["seedbonus"], ENT_QUOTES, 'UTF-8');
        
        $warnUntil = $User["warneduntil"] !== "0000-00-00 00:00:00" ? 
            "blue\">" . htmlspecialchars(formatTimestamp($User["warneduntil"]), ENT_QUOTES, 'UTF-8') : 
            ($User["leechwarnuntil"] !== "0000-00-00 00:00:00" ? 
                "red\">" . htmlspecialchars(formatTimestamp($User["leechwarnuntil"]), ENT_QUOTES, 'UTF-8') : 
                "black\">----");
        
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . urlencode($User["username"]) . "\">" . applyUsernameStyle($username, $User["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $title . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $email . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $ip . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<font color=\"" . $warnUntil . "</font>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars(formatTimestamp($User["last_access"]), ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars(formatBytes($User["uploaded"]), ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars(formatBytes($User["downloaded"]), ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars(number_format((int)$User["invites"]), ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $seedbonus . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input type=\"checkbox\" name=\"ids[]\" value=\"" . $userId . "\" checkme=\"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $stmt->close();
    
    if (!isset($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    $csrfToken = htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, 'UTF-8');
    
    echo "\r\n\t<script type=\"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar frm = document.forms[formname];\r\n\t\t\tfor(var i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form action=\"" . htmlspecialchars($_SERVER["SCRIPT_NAME"], ENT_QUOTES, 'UTF-8') . "?do=warned_users" . (isset($_GET["page"]) ? "&page=" . (int)$_GET["page"] : "") . "\" method=\"post\" name=\"warned_users\">\r\n\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . $csrfToken . "\" />\r\n\t\r\n\t" . $pagertop . "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"11\"><b>" . str_replace("{1}", htmlspecialchars(number_format($results), ENT_QUOTES, 'UTF-8'), $Language[3]) . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[4], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[5], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[12], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[13], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[14], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[6], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[7], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[9], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[10], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td class=\"alt2\"><input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll ('warned_users', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t\t<tr>\r\n\t\t\t<td colspan=\"11\" align=\"right\" class=\"tcat2\">\r\n\t\t\t\t<input type=\"submit\" value=\"" . htmlspecialchars($Language[15], ENT_QUOTES, 'UTF-8') . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t" . $pagertop . "\r\n\t</form>";
}
function getStaffLanguage(): string
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}

function checkStaffAuthentication(): void
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}

function redirectTo(string $url): void
{
    $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}

function formatBytes(float $bytes = 0): string
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 1073741824000) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 1099511627776, 2) . " TB";
}
function logStaffAction(string $log): void
{
    try {
        $db = $GLOBALS["DatabaseConnect"];
        $stmt = $db->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (?, ?, ?)");
        $adminId = (int)($_SESSION["ADMIN_ID"] ?? 0);
        $timestamp = time();
        $stmt->bind_param('iis', $adminId, $timestamp, $log);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Log staff action error: " . $e->getMessage());
    }
}
function sendPrivateMessage(int $receiver = 0, string $msg = "", string $subject = "", int $sender = 0, string $saved = "no", string $location = "1", string $unread = "yes"): void
{
    if ($receiver > 0 && !empty($msg)) {
        try {
            $db = $GLOBALS["DatabaseConnect"];
            $stmt = $db->prepare("INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)");
            $stmt->bind_param('iissss s', $sender, $receiver, $subject, $msg, $unread, $saved, $location);
            $stmt->execute();
            $stmt->close();
            
            $stmt = $db->prepare("UPDATE users SET pmunread = pmunread + 1 WHERE id = ?");
            $stmt->bind_param('i', $receiver);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Send PM error: " . $e->getMessage());
        }
    }
}
function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . htmlspecialchars($Error, ENT_QUOTES, 'UTF-8') . "</div></div>";
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
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $previousPageQuery . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
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
function formatTimestamp(string $timestamp = ""): string
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, (int)$timestamp);
}

function applyUsernameStyle(string $username, string $namestyle): string
{
    return str_replace("{username}", $username, $namestyle);
}

?>