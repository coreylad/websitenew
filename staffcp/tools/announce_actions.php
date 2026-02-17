<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthenticationModern();

// Load language file
$Language = loadStaffLanguage('announce_actions');
$Message = "";

// Handle POST actions with CSRF protection
if (strtoupper($_SERVER["REQUEST_METHOD"] ?? '') === "POST") {
    // Validate CSRF token
    $formToken = $_POST['form_token'] ?? null;
    if (!validateFormToken($formToken)) {
        $Message = showAlertErrorModern("Invalid security token. Please try again.");
    } else {
        // Get and validate IDs
        $ids = $_POST["ids"] ?? [];
        if (is_array($ids) && count($ids) > 0) {
            // Sanitize and validate all IDs
            $validIds = array_filter(array_map('intval', $ids), function($id) {
                return $id > 0;
            });
            
            if (count($validIds) > 0) {
                $placeholders = implode(',', array_fill(0, count($validIds), '?'));
                
                if (isset($_POST["delete"])) {
                    // Delete announce actions
                    try {
                        $stmt = $TSDatabase->prepare("DELETE FROM announce_actions WHERE userid IN ({$placeholders})");
                        $stmt->execute($validIds);
                        $affected = $stmt->rowCount();
                        
                        if ($affected > 0) {
                            $idsStr = implode(',', $validIds);
                            $logMsg = str_replace(["{1}", "{2}"], [$idsStr, $_SESSION["ADMIN_USERNAME"] ?? 'Unknown'], $Language[16] ?? 'Deleted announce actions for users: {1} by {2}');
                            logStaffActionModern($logMsg);
                            $Message = showAlertSuccessModern($logMsg);
                        }
                    } catch (Exception $e) {
                        $Message = showAlertErrorModern("Database error: " . escape_html($e->getMessage()));
                    }
                } else if (isset($_POST["ban"])) {
                    // Ban users
                    try {
                        $banMsg = str_replace("{1}", $_SESSION["ADMIN_USERNAME"] ?? 'Staff', $Language[18] ?? 'Banned by {1}');
                        $modcomment = gmdate("Y-m-d") . " - " . trim($banMsg) . "\n";
                        
                        $stmt = $TSDatabase->prepare("UPDATE users SET enabled = 'no', modcomment = CONCAT(?, modcomment), notifs = ? WHERE id IN ({$placeholders})");
                        $params = array_merge([$modcomment, $banMsg], $validIds);
                        $stmt->execute($params);
                        $affected = $stmt->rowCount();
                        
                        if ($affected > 0) {
                            $idsStr = implode(',', $validIds);
                            $logMsg = str_replace(["{1}", "{2}"], [$idsStr, $_SESSION["ADMIN_USERNAME"] ?? 'Unknown'], $Language[14] ?? 'Banned users: {1} by {2}');
                            logStaffActionModern($logMsg);
                            $Message = showAlertSuccessModern($logMsg);
                        }
                    } catch (Exception $e) {
                        $Message = showAlertErrorModern("Database error: " . escape_html($e->getMessage()));
                    }
                } else if (isset($_POST["warn"])) {
                    // Warn users
                    try {
                        $warneduntil = date("Y-m-d H:i:s", strtotime("+1 week"));
                        $warnMsg = str_replace("{1}", $_SESSION["ADMIN_USERNAME"] ?? 'Staff', $Language[17] ?? 'Warned by {1}');
                        $modcomment = gmdate("Y-m-d") . " - " . trim($warnMsg) . "\n";
                        
                        $stmt = $TSDatabase->prepare("UPDATE users SET warned = 'yes', timeswarned = timeswarned + 1, warneduntil = ?, modcomment = CONCAT(?, modcomment) WHERE id IN ({$placeholders})");
                        $params = array_merge([$warneduntil, $modcomment], $validIds);
                        $stmt->execute($params);
                        $affected = $stmt->rowCount();
                        
                        if ($affected > 0) {
                            $idsStr = implode(',', $validIds);
                            $logMsg = str_replace(["{1}", "{2}"], [$idsStr, $_SESSION["ADMIN_USERNAME"] ?? 'Unknown'], $Language[15] ?? 'Warned users: {1} by {2}');
                            logStaffActionModern($logMsg);
                            $Message = showAlertSuccessModern($logMsg);
                            
                            // Send PMs to warned users
                            $stmt = $TSDatabase->prepare("SELECT id FROM users WHERE id IN ({$placeholders})");
                            $stmt->execute($validIds);
                            while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                sendPrivateMessage((int)$user["id"], $warnMsg, $Language[2] ?? 'Warning');
                            }
                        }
                    } catch (Exception $e) {
                        $Message = showAlertErrorModern("Database error: " . escape_html($e->getMessage()));
                    }
                }
            }
        }
    }
}
// Get total count for pagination
try {
    $countStmt = $TSDatabase->prepare("SELECT COUNT(*) as total FROM announce_actions");
    $countStmt->execute();
    $results = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
    $results = 0;
}

$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=announce_actions&amp;");

$Found = "";
if ($results > 0) {
    try {
        $stmt = $TSDatabase->prepare("
            SELECT t.*, u.username, g.namestyle, tr.name as torrentname 
            FROM announce_actions t 
            LEFT JOIN users u ON (t.userid = u.id) 
            LEFT JOIN usergroups g ON (u.usergroup = g.gid) 
            LEFT JOIN torrents tr ON (t.torrentid = tr.id) 
            ORDER BY t.actiontime DESC 
            {$limit}
        ");
        $stmt->execute();
        
        while ($R = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $username = escape_html($R["username"] ?? 'Unknown');
            $usernameStyled = applyUsernameStyle($username, $R["namestyle"] ?? '{username}');
            $torrentname = escape_html($R["torrentname"] ?? 'Unknown');
            $passkey = escape_html($R["passkey"] ?? '');
            $ip = escape_html($R["ip"] ?? '');
            $actionmessage = escape_html($R["actionmessage"] ?? '');
            $userid = (int)($R["userid"] ?? 0);
            $torrentid = (int)($R["torrentid"] ?? 0);
            $actiontime = formatTimestamp($R["actiontime"] ?? '');
            
            $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . escape_attr($username) . "\">" . $usernameStyled . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $actiontime . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a href=\"../details.php?id=" . $torrentid . "\">" . $torrentname . "</a>\r\n\t\t\t</td>\t\t\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $passkey . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $ip . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $actionmessage . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t<input type=\"checkbox\" name=\"ids[]\" value=\"" . $userid . "\" checkme=\"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        }
    } catch (Exception $e) {
        $Found = "";
    }
}

if (empty($Found)) {
    echo "\r\n\t\r\n\t" . showAlertErrorModern($Language[19] ?? 'No announce actions found');
}
$pageParam = isset($_GET["page"]) ? "&page=" . (int)$_GET["page"] : "";
$formToken = getFormTokenField();

echo "\r\n<script type=\"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar frm = document.forms[formname];\r\n\t\tfor(var i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form action=\"index.php?do=announce_actions" . escape_attr($pageParam) . "\" method=\"post\" name=\"announce_actions\">\r\n" . $formToken . "\r\n" . $Message . "\r\n" . $pagertop . "\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"7\">" . escape_html($Language[2] ?? 'Announce Actions') . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[3] ?? 'Username') . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[4] ?? 'Time') . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[5] ?? 'Torrent') . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[6] ?? 'Passkey') . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[10] ?? 'IP') . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[9] ?? 'Message') . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t<input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll ('announce_actions', this, 'group');\">\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" colspan=\"7\" align=\"right\">\r\n\t\t\t<input type=\"submit\" name=\"delete\" value=\"" . escape_attr($Language[13] ?? 'Delete') . "\" /> <input type=\"submit\" name=\"warn\" value=\"" . escape_attr($Language[12] ?? 'Warn') . "\" /> <input type=\"submit\" name=\"ban\" value=\"" . escape_attr($Language[11] ?? 'Ban') . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n" . $pagertop . "\r\n</form>";

// Keep helper functions for backward compatibility
function getStaffLanguage(): string
{
    return getStaffLanguageModern();
}
function checkStaffAuthentication(): void
{
    checkStaffAuthenticationModern();
}

function redirectTo(string $url): void
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href = \"" . escape_js($url) . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . escape_attr($url) . "\" />\r\n\t\t</noscript>";
    }
    exit;
}

function showAlertError(string $Error): string
{
    return showAlertErrorModern($Error);
}

function logStaffAction(string $log): void
{
    logStaffActionModern($log);
}

function validatePerPage(int $numresults, int &$page, int &$perpage, int $maxperpage = 20, int $defaultperpage = 20): void
{
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else if ($maxperpage < $perpage) {
        $perpage = $maxperpage;
    }
    
    $totalPages = (int)ceil($numresults / $perpage);
    if ($totalPages == 0) {
        $totalPages = 1;
    }
    
    if ($page < 1) {
        $page = 1;
    } else if ($totalPages < $page) {
        $page = $totalPages;
    }
}
function calculatePagination(int $pagenumber, int $perpage, int $total): array
{
    $startOffset = $perpage * ($pagenumber - 1);
    $endOffset = $startOffset + $perpage;
    if ($total < $endOffset) {
        $endOffset = $total;
    }
    $startOffset++;
    return ["first" => number_format($startOffset), "last" => number_format($endOffset)];
}

function buildPaginationLinks(int $perpage, int $results, string $address): array
{
    if ($results < $perpage) {
        return ["", ""];
    }
    
    $totalPages = $results ? (int)ceil($results / $perpage) : 0;
    $pagenumber = (int)($_GET["page"] ?? $_POST["page"] ?? 1);
    
    validatePerPage($results, $pagenumber, $perpage, 200);
    
    $startOffset = ($pagenumber - 1) * $perpage;
    $endOffset = $pagenumber * $perpage;
    
    if ($results < $endOffset) {
        $endOffset = $results;
        if ($results < $startOffset) {
            $startOffset = $results - $perpage - 1;
        }
    }
    
    if ($startOffset < 0) {
        $startOffset = 0;
    }
    
    $paginationHTML = "";
    $pageCounter = 0;
    
    if ($results <= $perpage) {
        return ["", "LIMIT " . $startOffset . ", " . $perpage];
    }
    
    $pageNav = [
        "pagenav" => true,
        "first" => false,
        "prev" => false,
        "next" => false,
        "last" => false
    ];
    
    $total = number_format($results);
    
    if (1 < $pagenumber) {
        $prevPage = $pagenumber - 1;
        $prevPageInfo = calculatePagination($prevPage, $perpage, $results);
        $pageNav["prev"] = true;
    }
    
    if ($pagenumber < $totalPages) {
        $nextPage = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPage, $perpage, $results);
        $pageNav["next"] = true;
    }
    
    $pageWindow = 3;
    $jumpPagesStr = "10 50 100 500 1000";
    $jumpPages = preg_split("#\\s+#s", $jumpPagesStr, -1, PREG_SPLIT_NO_EMPTY);
    
    while ($pageCounter++ < $totalPages) {
        if ($pageWindow <= abs($pageCounter - $pagenumber) && $pageWindow != 0) {
            if ($pageCounter == 1) {
                $firstPageInfo = calculatePagination(1, $perpage, $results);
                $pageNav["first"] = true;
            }
            if ($pageCounter == $totalPages) {
                $lastPageInfo = calculatePagination($totalPages, $perpage, $results);
                $pageNav["last"] = true;
            }
            if (in_array(abs($pageCounter - $pagenumber), $jumpPages) && $pageCounter != 1 && $pageCounter != $totalPages) {
                $currentPageInfo = calculatePagination($pageCounter, $perpage, $results);
                $pageOffset = $pageCounter - $pagenumber;
                if (0 < $pageOffset) {
                    $pageOffset = "+" . $pageOffset;
                }
                $paginationHTML .= "<li><a class=\"smalltext\" href=\"" . escape_attr($address . ($pageCounter != 1 ? "page=" . $pageCounter : "")) . "\" title=\"" . escape_attr("Show results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total) . "\"><!--" . $pageOffset . "-->" . $pageCounter . "</a></li>";
            }
        } else {
            if ($pageCounter == $pagenumber) {
                $activePageInfo = calculatePagination($pageCounter, $perpage, $results);
                $paginationHTML .= "<li><a name=\"current\" class=\"current\" title=\"" . escape_attr("Showing results " . $activePageInfo["first"] . " to " . $activePageInfo["last"] . " of " . $total) . "\">" . $pageCounter . "</a></li>";
            } else {
                $currentPageInfo = calculatePagination($pageCounter, $perpage, $results);
                $paginationHTML .= "<li><a href=\"" . escape_attr($address . ($pageCounter != 1 ? "page=" . $pageCounter : "")) . "\" title=\"" . escape_attr("Show results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total) . "\">" . $pageCounter . "</a></li>";
            }
        }
    }
    
    $prevPageQuery = isset($prevPage) && $prevPage != 1 ? "page=" . $prevPage : "";
    $firstPageLink = $pageNav["first"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address) . "\" title=\"" . escape_attr("First Page - Show Results " . ($firstPageInfo["first"] ?? '') . " to " . ($firstPageInfo["last"] ?? '') . " of " . $total) . "\">&laquo; First</a></li>" : "";
    $prevPageLink = $pageNav["prev"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . $prevPageQuery) . "\" title=\"" . escape_attr("Previous Page - Show Results " . ($prevPageInfo["first"] ?? '') . " to " . ($prevPageInfo["last"] ?? '') . " of " . $total) . "\">&lt;</a></li>" : "";
    $nextPageLink = $pageNav["next"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . "page=" . ($nextPage ?? '')) . "\" title=\"" . escape_attr("Next Page - Show Results " . ($nextPageInfo["first"] ?? '') . " to " . ($nextPageInfo["last"] ?? '') . " of " . $total) . "\">&gt;</a></li>" : "";
    $lastPageLink = $pageNav["last"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . "page=" . $totalPages) . "\" title=\"" . escape_attr("Last Page - Show Results " . ($lastPageInfo["first"] ?? '') . " to " . ($lastPageInfo["last"] ?? '') . " of " . $total) . "\">Last <strong>&raquo;</strong></a></li>" : "";
    
    $paginationHTML = "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td style=\"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $totalPages . "</li>\r\n\t\t\t\t\t\t" . $firstPageLink . $prevPageLink . "\r\n\t\t\t\t\t\t" . $paginationHTML . "\r\n\t\t\t\t\t\t" . $nextPageLink . $lastPageLink . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
    
    return [$paginationHTML, "LIMIT " . $startOffset . ", " . $perpage];
}
function formatTimestamp(string $timestamp = ""): string
{
    $dateFormat = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else if (strstr($timestamp, "-")) {
        $timestamp = strtotime($timestamp);
    }
    return date($dateFormat, (int)$timestamp);
}

function sendPrivateMessage(int $receiver = 0, string $msg = "", string $subject = "", int $sender = 0, string $saved = "no", string $location = "1", string $unread = "yes"): void
{
    global $TSDatabase;
    
    if ($sender != 0 || !$receiver || empty($msg)) {
        return;
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
    } catch (Exception $e) {
        // Silently fail
    }
}

function formatSecondsToTime(int $sec, bool $padHours = false): string
{
    $formattedTime = "";
    $hours = (int)($sec / 3600);
    $formattedTime .= $padHours ? str_pad((string)$hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":";
    $minutes = (int)($sec / 60 % 60);
    $formattedTime .= str_pad((string)$minutes, 2, "0", STR_PAD_LEFT) . ":";
    $seconds = (int)($sec % 60);
    $formattedTime .= str_pad((string)$seconds, 2, "0", STR_PAD_LEFT);
    return $formattedTime;
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

function applyUsernameStyle(string $username, string $namestyle): string
{
    return str_replace("{username}", $username, $namestyle);
}

function showAlertMessage(string $message = ""): string
{
    return showAlertSuccessModern($message);
}

?>