<?php

declare(strict_types=1);

require_once '../staffcp_modern.php';

checkStaffAuthentication();
$Language = loadStaffLanguage("donor_list");
$Message = "";
$ids = [];
$amount = "0";
$type = "0";

if (strtoupper($_SERVER["REQUEST_METHOD"] ?? "") === "POST") {
    // CSRF protection
    $formToken = $_POST["form_token"] ?? "";
    if (!validateFormToken($formToken)) {
        $Message = showAlertErrorModern("Invalid form token. Please try again.");
    } else {
        $userids = $_POST["ids"] ?? [];
        $amount = (int)($_POST["amount"] ?? 0);
        $type = $_POST["type"] ?? "";
        
        if (is_array($userids) && count($userids) > 0 && $amount > 0 && $type !== "" && $type !== "0") {
            global $TSDatabase;
            
            // Sanitize and validate user IDs
            $userids = array_map('intval', $userids);
            $userids = array_filter($userids, fn($id) => $id > 0);
            
            if (count($userids) > 0) {
                $SysMsg = str_replace(["{1}", "{2}", "{3}"], [(string)$amount, $type, $_SESSION["ADMIN_USERNAME"] ?? "Unknown"], $Language[22] ?? "");
                $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
                
                $affectedRows = 0;
                
                try {
                    $placeholders = implode(',', array_fill(0, count($userids), '?'));
                    
                    if ($type === "donoruntil") {
                        $donorlengthadd = $amount * 7;
                        $stmt = $TSDatabase->prepare(
                            "UPDATE users SET modcomment = CONCAT(?, modcomment), 
                            donoruntil = IF(donoruntil = '0000-00-00 00:00:00', 
                                ADDDATE(NOW(), INTERVAL ? DAY), 
                                ADDDATE(donoruntil, INTERVAL ? DAY)) 
                            WHERE id IN (" . $placeholders . ")"
                        );
                        $params = array_merge([$modcomment, $donorlengthadd, $donorlengthadd], $userids);
                        $stmt->execute($params);
                        $affectedRows = $stmt->rowCount();
                    } elseif ($type === "seedbonus") {
                        $stmt = $TSDatabase->prepare(
                            "UPDATE users SET modcomment = CONCAT(?, modcomment), 
                            seedbonus = seedbonus + ? 
                            WHERE id IN (" . $placeholders . ")"
                        );
                        $params = array_merge([$modcomment, $amount], $userids);
                        $stmt->execute($params);
                        $affectedRows = $stmt->rowCount();
                    } elseif ($type === "invites") {
                        $stmt = $TSDatabase->prepare(
                            "UPDATE users SET modcomment = CONCAT(?, modcomment), 
                            invites = invites + ? 
                            WHERE id IN (" . $placeholders . ")"
                        );
                        $params = array_merge([$modcomment, $amount], $userids);
                        $stmt->execute($params);
                        $affectedRows = $stmt->rowCount();
                    }
                    
                    if ($affectedRows > 0) {
                        $Message = str_replace(["{1}", "{2}", "{3}"], [(string)$amount, $type, $_SESSION["ADMIN_USERNAME"] ?? "Unknown"], $Language[21] ?? "");
                        logStaffActionModern($Message);
                        $Message = showAlertMessage($Message);
                    }
                } catch (Exception $e) {
                    $Message = showAlertErrorModern("Database error: " . $e->getMessage());
                }
            }
        } else {
            $Message = showAlertError($Language[20] ?? "Invalid input");
        }
    }
}
$orderby1 = "ASC";
$type = "donoruntil";
$orderbyGet = $_GET["orderby"] ?? "";
$typeGet = $_GET["type"] ?? "";

if ($orderbyGet !== "") {
    $orderby1 = $orderbyGet === "ASC" ? "ASC" : "DESC";
    $type = $typeGet === "donoruntil" ? "donoruntil" : "donated";
}

// Validate and whitelist ORDER BY clause to prevent SQL injection
$allowedTypes = ['donoruntil', 'donated'];
$allowedOrders = ['ASC', 'DESC'];

if (!in_array($type, $allowedTypes)) {
    $type = 'donoruntil';
}
if (!in_array($orderby1, $allowedOrders)) {
    $orderby1 = 'ASC';
}

$orderby = $type . " " . $orderby1;

global $TSDatabase;

try {
    // Get total count of donors
    $countStmt = $TSDatabase->prepare("SELECT COUNT(*) as total FROM users WHERE donor = 'yes' AND enabled = 'yes'");
    $countStmt->execute();
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $results = (int)($countResult['total'] ?? 0);
    
    list($pagertop, $limit) = buildPaginationLinks(25, $results, escape_attr($_SERVER["SCRIPT_NAME"] ?? "") . "?do=donor_list&amp;orderby=" . escape_attr($orderby1) . "&amp;type=" . escape_attr($type) . "&amp;");
    
    // Parse LIMIT clause to get offset and count
    $limitParts = explode(',', str_replace('LIMIT ', '', $limit));
    $limitOffset = isset($limitParts[0]) ? (int)trim($limitParts[0]) : 0;
    $limitCount = isset($limitParts[1]) ? (int)trim($limitParts[1]) : 25;
    
    // Get donors with pagination - using validated/whitelisted ORDER BY
    $sql = $TSDatabase->prepare("
        SELECT u.id, u.username, u.last_access, u.email, u.ip, u.uploaded, u.downloaded, 
               u.invites, u.seedbonus, u.donoruntil, u.donated, u.total_donated, 
               g.title, g.namestyle 
        FROM users u
        LEFT JOIN usergroups g ON (u.usergroup = g.gid) 
        WHERE u.donor = 'yes' AND u.enabled = 'yes' 
        ORDER BY " . $orderby . " 
        LIMIT ? OFFSET ?
    ");
    $sql->execute([$limitCount, $limitOffset]);
    
    if ($sql->rowCount() === 0) {
        echo "\r\n\t\r\n\t" . showAlertError($Language[1] ?? "No donors found");
    } else {
        $Found = "";
        while ($User = $sql->fetch(PDO::FETCH_ASSOC)) {
            $username = $User["username"] ?? "";
            $usernameAttr = escape_attr($username);
            $title = escape_html($User["title"] ?? "");
            $email = escape_html($User["email"] ?? "");
            $userId = (int)($User["id"] ?? 0);
            $invites = (int)($User["invites"] ?? 0);
            $seedbonus = escape_html($User["seedbonus"] ?? "0");
            $donated = escape_html($User["donated"] ?? "0");
            $totalDonated = escape_html($User["total_donated"] ?? "0");
            
            // Apply username style - username will be escaped inside applyUsernameStyle
            $styledUsername = applyUsernameStyle(escape_html($username), escape_html($User["namestyle"] ?? "{username}"));
            
            $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . $usernameAttr . "\">" . $styledUsername . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $title . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $email . "\r\n\t\t\t</td>\t\t\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html(formatTimestamp($User["last_access"] ?? "")) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html(formatBytes((int)($User["uploaded"] ?? 0))) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html(formatBytes((int)($User["downloaded"] ?? 0))) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . number_format($invites) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $seedbonus . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html(formatTimestamp($User["donoruntil"] ?? "")) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $donated . "/" . $totalDonated . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t<input type=\"checkbox\" name=\"ids[]\" value=\"" . $userId . "\" checkme=\"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
            }
        
        $currentPage = (int)($_GET["page"] ?? 1);
        $pageQuery = $currentPage > 1 ? "&page=" . $currentPage : "";
        $amountAttr = escape_attr((string)$amount);
        $resultsFormatted = number_format($results);
        
        echo "\r\n\t<script type=\"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar frm = document.forms[formname];\r\n\t\t\tfor(var i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form action=\"" . escape_attr($_SERVER["SCRIPT_NAME"] ?? "") . "?do=donor_list" . escape_attr($pageQuery) . "\" method=\"post\" name=\"donor_list\">\r\n\t" . getFormTokenField() . "\r\n\t" . $Message . "\r\n\t" . $pagertop . "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"11\"><b>" . str_replace("{1}", $resultsFormatted, $Language[3] ?? "") . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[4] ?? "") . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[5] ?? "") . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[6] ?? "") . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[7] ?? "") . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[8] ?? "") . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[9] ?? "") . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[10] ?? "") . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[11] ?? "") . "</b></td>\r\n\t\t\t<td class=\"alt2\"><a href=\"index.php?do=donor_list&amp;orderby=" . escape_attr($orderby1 === "ASC" ? "DESC" : "ASC") . "&amp;type=donoruntil\"><b>" . escape_html($Language[12] ?? "") . "</b></a></td>\r\n\t\t\t<td class=\"alt2\"><a href=\"index.php?do=donor_list&amp;orderby=" . escape_attr($orderby1 === "ASC" ? "DESC" : "ASC") . "&amp;type=donated\"><b>" . escape_html($Language[13] ?? "") . "</b></a></td>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll ('donor_list', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" align=\"right\" colspan=\"11\">\r\n\t\t\t\t" . escape_html($Language[14] ?? "") . " <input type=\"text\" name=\"amount\" size=\"10\" value=\"" . $amountAttr . "\" /> \r\n\t\t\t\t<select name=\"type\">\r\n\t\t\t\t\t<option value=\"0\"" . ($type === "0" ? " selected=\"selected\"" : "") . ">" . escape_html($Language[15] ?? "") . "</option>\r\n\t\t\t\t\t<option value=\"donoruntil\"" . ($type === "donoruntil" ? " selected=\"selected\"" : "") . ">" . escape_html($Language[16] ?? "") . "</option>\r\n\t\t\t\t\t<option value=\"seedbonus\"" . ($type === "seedbonus" ? " selected=\"selected\"" : "") . ">" . escape_html($Language[17] ?? "") . "</option>\r\n\t\t\t\t\t<option value=\"invites\"" . ($type === "invites" ? " selected=\"selected\"" : "") . ">" . escape_html($Language[18] ?? "") . "</option>\r\n\t\t\t\t</select> \r\n\t\t\t\t<input type=\"submit\" value=\"" . escape_attr($Language[19] ?? "") . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t" . $pagertop;
    }
} catch (Exception $e) {
    echo "\r\n\t\r\n\t" . showAlertErrorModern("Database error: " . $e->getMessage());
}

function getStaffLanguage(): string
{
    return getStaffLanguageModern();
}

function checkStaffAuthentication(): void
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}

function logStaffAction(string $log): bool
{
    return logStaffActionModern($log);
}

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

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . escape_html($Error) . "</div></div>";
}

function validatePerPage(int $numresults, int &$page, int &$perpage, int $maxperpage = 20, int $defaultperpage = 20): void
{
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $totalPages = (int)ceil($numresults / $perpage);
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

function buildPaginationLinks(int $perpage, int $results, string $address): array
{
    if ($results < $perpage) {
        return ["", ""];
    }
    
    $queryResult = $results ? (int)ceil($results / $perpage) : 0;
    
    $pagenumber = (int)($_GET["page"] ?? $_POST["page"] ?? 1);
    validatePerPage($results, $pagenumber, $perpage, 200, $perpage);
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
    $paginationLinks = "";
    $currentPage = 0;
    if ($results <= $perpage) {
        return ["", "LIMIT " . $limitOffset . ", " . $perpage];
    }
    
    $total = number_format($results);
    $paginationHtml = [
        "pagenav" => true,
        "last" => false,
        "first" => false,
        "next" => false,
        "prev" => false
    ];
    
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
    
    $pageRangeThreshold = 3;
    $paginationOptions = "10 50 100 500 1000";
    $paginationSkipLinksArray = preg_split("#\\s+#s", $paginationOptions, -1, PREG_SPLIT_NO_EMPTY);
    
    while ($currentPage++ < $queryResult) {
    }
    
    $previousPageQuery = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
    $paginationLinks = "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td style=\"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address) . "\" title=\"First Page - Show Results " . ($firstPageInfo["first"] ?? "1") . " to " . ($firstPageInfo["last"] ?? "1") . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . $previousPageQuery) . "\" title=\"Previous Page - Show Results " . ($previousPageInfo["first"] ?? "1") . " to " . ($previousPageInfo["last"] ?? "1") . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . "page=" . ($nextPageNumber ?? 1)) . "\" title=\"Next Page - Show Results " . ($nextPageInfo["first"] ?? "1") . " to " . ($nextPageInfo["last"] ?? "1") . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" href=\"" . escape_attr($address . "page=" . $queryResult) . "\" title=\"Last Page - Show Results " . ($lastPageInfo["first"] ?? "1") . " to " . ($lastPageInfo["last"] ?? "1") . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
    return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
}

function formatTimestamp(string $timestamp = ""): string
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestampInt = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestampInt = (int)strtotime($timestamp);
        } else {
            $timestampInt = (int)$timestamp;
        }
    }
    return date($dateFormatPattern, $timestampInt);
}

function applyUsernameStyle(string $username, string $namestyle): string
{
    return str_replace("{username}", $username, $namestyle);
}

function showAlertMessage(string $message = ""): string
{
    return "<div class=\"alert\"><div>" . escape_html($message) . "</div></div>";
}

?>