<?php

declare(strict_types=1);

require_once '../staffcp_modern.php';

// Load language file
$Language = loadStaffLanguage('tracker_logs');

// Check authentication and get staff details
try {
    $LoggedAdminDetails = checkStaffAuthenticationModern();
} catch (Exception $e) {
    redirectTo('../index.php');
    exit;
}

// Get additional staff permissions
global $TSDatabase;
$stmt = $TSDatabase->query(
    'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
     FROM users u 
     LEFT JOIN usergroups g ON u.usergroup = g.gid 
     WHERE u.id = ? 
     LIMIT 1',
    [(int)$_SESSION['ADMIN_ID']]
);
$LoggedAdminDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$LoggedAdminDetails) {
    redirectTo('../index.php');
    exit;
}

// Get input parameters with null coalescing and type casting
$Act = (string)($_POST['act'] ?? $_GET['act'] ?? '');
$keyword = (string)($_POST['keyword'] ?? $_GET['keyword'] ?? '');
$Page = (int)($_POST['page'] ?? $_GET['page'] ?? 0);

// Initialize variables
$Message = '';
$Found = '';
$pagertop = '';
$limit = '';
$WhereQuery = '';
$SHOWPHPERRORS = '';
$LinkQuery = '';
$FormField = '';

// Handle POST request for deleting logs (with CSRF protection)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $LoggedAdminDetails['cansettingspanel'] === 'yes') {
    // Validate CSRF token
    $token = (string)($_POST['form_token'] ?? '');
    if (!validateFormToken($token)) {
        $Message = showAlertErrorModern('Invalid security token. Please try again.');
    } elseif (isset($_POST['lid']) && is_array($_POST['lid']) && count($_POST['lid']) > 0) {
        // Sanitize log IDs
        $logIds = array_map('intval', $_POST['lid']);
        $logIds = array_filter($logIds, function($id) { return $id > 0; });
        
        if (count($logIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($logIds), '?'));
            $stmt = $TSDatabase->query(
                "DELETE FROM sitelog WHERE id IN ($placeholders)",
                $logIds
            );
            
            $affectedRows = $stmt->rowCount();
            if ($affectedRows > 0) {
                $idsString = implode(', ', $logIds);
                $SysMsg = str_replace(
                    ['{1}', '{2}'], 
                    [$idsString, $_SESSION['ADMIN_USERNAME']], 
                    $Language[4] ?? 'Log entries {1} deleted by {2}'
                );
                logStaffActionModern($SysMsg);
            }
        }
    }
}
// Build search query and form fields
if ($keyword !== '') {
    $WhereQuery = " WHERE txt LIKE ?";
    $LinkQuery = 'keyword=' . urlencode($keyword) . '&amp;';
    $FormField = '<input type="hidden" name="keyword" value="' . escape_attr($keyword) . '">';
}

if ($Page > 0) {
    $FormField .= '<input type="hidden" name="page" value="' . escape_attr((string)$Page) . '">';
}

// Handle delete all action (with CSRF protection)
if ($Act === 'delete_all' && $LoggedAdminDetails['cansettingspanel'] === 'yes') {
    $token = (string)($_GET['token'] ?? '');
    if (validateFormToken($token)) {
        $TSDatabase->query('TRUNCATE TABLE sitelog', []);
        logStaffActionModern('All tracker logs deleted by ' . $_SESSION['ADMIN_USERNAME']);
    }
}

// Count total results
$params = [];
if ($keyword !== '') {
    $params[] = '%' . $keyword . '%';
}

$countStmt = $TSDatabase->query(
    'SELECT COUNT(*) as total FROM sitelog' . $WhereQuery,
    $params
);
$countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
$results = (int)($countResult['total'] ?? 0);

// Build pagination
list($pagertop, $limit) = buildPaginationLinks(50, $results, $_SERVER['SCRIPT_NAME'] . '?do=tracker_logs&amp;' . $LinkQuery);

// Fetch logs with pagination
$stmt = $TSDatabase->query(
    'SELECT * FROM sitelog' . $WhereQuery . ' ORDER BY added DESC ' . $limit,
    $params
);
if ($stmt->rowCount() === 0) {
    $Message = showAlertErrorModern($Language[1] ?? 'No logs found.');
} else {
    $PHPERRORS = [];
    $Count = 0;
    
    while ($Log = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (substr($Log['txt'], 0, 4) === 'PHP ') {
            $PHPERRORS[] = $Log;
        } else {
            $class = $Count % 2 === 1 ? 'alt2' : 'alt1';
            $logText = strip_tags($Log['txt']);
            if ($keyword !== '') {
                $logText = highlightKeyword($keyword, $logText);
            }
            
            $Found .= "\n\t\t\t<tr>\n" .
                "\t\t\t\t<td class=\"" . escape_attr($class) . "\">" . escape_html(formatTimestamp((string)$Log['added'])) . "</td>\n" .
                "\t\t\t\t<td class=\"" . escape_attr($class) . "\">" . $logText . "</td>\n" .
                "\t\t\t\t<td class=\"" . escape_attr($class) . "\" align=\"center\">" .
                "<input type=\"checkbox\" name=\"lid[]\" value=\"" . escape_attr((string)$Log['id']) . "\" checkme=\"group\"></td>\n" .
                "\t\t\t</tr>\n";
            $Count++;
        }
    }
    
    // Display PHP errors separately if any
    if (count($PHPERRORS) > 0) {
        $SHOWPHPERRORS = "\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\n" .
            "\t\t\t<tr>\n\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"3\"><b>" . 
            escape_html($Language[16] ?? 'PHP Errors') . "</b></td>\n\t\t\t</tr>\n" .
            "\t\t\t<tr>\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[5] ?? 'Date') . "</b></td>\n" .
            "\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[6] ?? 'Log Entry') . "</b></td>\n" .
            "\t\t\t\t<td class=\"alt2\" align=\"center\">" .
            "<input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll('tracker_logs', this, 'group');\"></td>\n" .
            "\t\t\t</tr>";
            
        foreach ($PHPERRORS as $Log) {
            $SHOWPHPERRORS .= "\n\t\t\t<tr>\n" .
                "\t\t\t\t<td class=\"alt1\">" . escape_html(formatTimestamp((string)$Log['added'])) . "</td>\n" .
                "\t\t\t\t<td class=\"alt1\">" . escape_html(strip_tags($Log['txt'])) . "</td>\n" .
                "\t\t\t\t<td class=\"alt1\" align=\"center\">" .
                "<input type=\"checkbox\" name=\"lid[]\" value=\"" . escape_attr((string)$Log['id']) . "\" checkme=\"group\"></td>\n" .
                "\t\t\t</tr>\n";
        }
        
        $SHOWPHPERRORS .= "\n\t\t\t<tr>\n\t\t\t\t<td class=\"tcat2\" align=\"right\" colspan=\"3\">" .
            "<input type=\"submit\" value=\"" . escape_attr($Language[8] ?? 'Delete Selected') . "\"></td>\n" .
            "\t\t\t</tr>\n\t\t</table>";
    }
    
    // Add footer row with submit button
    $Found .= (empty($Found) ? "<tr><td colspan=\"3\" class=\"alt1\">" . escape_html($Language[1] ?? 'No logs found.') . "</td></tr>" : "") .
        "\n\t\t<tr>\n\t\t\t<td class=\"tcat2\" align=\"right\" colspan=\"3\">" .
        ($LoggedAdminDetails['cansettingspanel'] === 'yes' ? 
            "<input type=\"submit\" value=\"" . escape_attr($Language[8] ?? 'Delete Selected') . "\">" : "") .
        "</td>\n\t\t</tr>";
}
// Display output
if ($Message) {
    echo "\n\t" . $Message;
} else {
    $formToken = generateFormToken();
    $deleteAllLink = 'index.php?do=tracker_logs&act=delete_all&token=' . urlencode($formToken);
    
    echo "\n\t<script type=\"text/javascript\">\n" .
        "\t\tfunction select_deselectAll(formname, elm, group) {\n" .
        "\t\t\tvar frm = document.forms[formname];\n" .
        "\t\t\tfor (var i = 0; i < frm.length; i++) {\n" .
        "\t\t\t\tif (elm.attributes['checkall'] != null && elm.attributes['checkall'].value == group) {\n" .
        "\t\t\t\t\tif (frm.elements[i].attributes['checkme'] != null && frm.elements[i].attributes['checkme'].value == group) {\n" .
        "\t\t\t\t\t\tfrm.elements[i].checked = elm.checked;\n" .
        "\t\t\t\t\t}\n" .
        "\t\t\t\t} else if (frm.elements[i].attributes['checkme'] != null && frm.elements[i].attributes['checkme'].value == group) {\n" .
        "\t\t\t\t\tif (frm.elements[i].checked == false) {\n" .
        "\t\t\t\t\t\tfrm.elements[1].checked = false;\n" .
        "\t\t\t\t\t}\n" .
        "\t\t\t\t}\n" .
        "\t\t\t}\n" .
        "\t\t}\n\n" .
        "\t\tfunction ConfirmDelete() {\n" .
        "\t\t\tif (confirm(" . json_encode(trim($Language[15] ?? 'Are you sure you want to delete all logs?')) . ")) {\n" .
        "\t\t\t\tTSJump(" . json_encode($deleteAllLink) . ");\n" .
        "\t\t\t} else {\n" .
        "\t\t\t\treturn false;\n" .
        "\t\t\t}\n" .
        "\t\t}\n" .
        "\t</script>\n" .
        "\t<form action=\"" . escape_attr($_SERVER['SCRIPT_NAME'] ?? '') . "?do=tracker_logs\" method=\"post\" name=\"search\">\n\t";
    
    if ($LoggedAdminDetails['cansettingspanel'] === 'yes') {
        $deleteAllText = '<a href="#" onclick="ConfirmDelete();">' . escape_html($Language[14] ?? 'Delete All Logs') . '</a>';
        echo '<div class="alert alert-info">' . $deleteAllText . '</div>';
    }
    
    echo "\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\n" .
        "\t\t<tr>\n\t\t\t<td class=\"tcat\" align=\"center\"><b>" . escape_html($Language[10] ?? 'Search Logs') . "</b></td>\n" .
        "\t\t</tr>\n" .
        "\t\t<tr>\n\t\t\t<td class=\"alt1\" align=\"center\">" . escape_html($Language[11] ?? 'Keyword:') . 
        " <input type=\"text\" class=\"bginput\" style=\"width: 80%;\" name=\"keyword\" value=\"" . escape_attr($keyword) . "\"> " .
        "<input type=\"submit\" value=\"" . escape_attr($Language[12] ?? 'Search') . "\"> " .
        "<input type=\"reset\" value=\"" . escape_attr($Language[13] ?? 'Reset') . "\"></td>\n" .
        "\t\t</tr>\n" .
        "\t</table>\n" .
        "\t</form>\n" .
        "\t<form action=\"" . escape_attr($_SERVER['SCRIPT_NAME'] ?? '') . "?do=tracker_logs\" method=\"post\" name=\"tracker_logs\">\n\t" .
        getFormTokenField() . "\n\t" .
        $FormField . "\n\t" .
        $pagertop . "\n" .
        "\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\n" .
        "\t\t<tr>\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"3\"><b>" . escape_html($Language[3] ?? 'Tracker Logs') . "</b></td>\n" .
        "\t\t</tr>\n" .
        "\t\t<tr>\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[5] ?? 'Date') . "</b></td>\n" .
        "\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[6] ?? 'Log Entry') . "</b></td>\n" .
        "\t\t\t<td class=\"alt2\" align=\"center\">" .
        "<input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll('tracker_logs', this, 'group');\"></td>\n" .
        "\t\t</tr>\n\t\t" .
        $Found . "\n" .
        "\t</table>\n\t" .
        $SHOWPHPERRORS . "\n\t" .
        $pagertop . "\n" .
        "\t</form>\n\t";
}

/**
 * Highlight keyword in text
 * 
 * @param string $search Search term
 * @param string $subject Text to search in
 * @param string $hlstart Highlight start tag
 * @param string $hlend Highlight end tag
 * @return string Highlighted text
 */
function highlightKeyword(string $search, string $subject, string $hlstart = "<b><font color='#f7071d'>", string $hlend = "</font></b>"): string
{
    $searchLen = strlen($search);
    if ($searchLen === 0) {
        return $subject;
    }
    
    $tempSubject = $subject;
    while ($tempSubject = stristr($tempSubject, $search)) {
        $matchedText = substr($tempSubject, 0, $searchLen);
        $tempSubject = substr($tempSubject, $searchLen);
        $subject = str_replace($matchedText, $hlstart . $matchedText . $hlend, $subject);
    }
    
    return $subject;
}

/**
 * Format timestamp for display
 * 
 * @param string $timestamp Unix timestamp or date string
 * @return string Formatted date
 */
function formatTimestamp(string $timestamp = ''): string
{
    $dateFormatPattern = 'm-d-Y h:i A';
    
    if (empty($timestamp)) {
        $timestamp = (string)time();
    } elseif (strstr($timestamp, '-')) {
        $timestamp = (string)strtotime($timestamp);
    }
    
    return date($dateFormatPattern, (int)$timestamp);
}

/**
 * Validate items per page and current page
 * 
 * @param int $numresults Total number of results
 * @param int $page Current page (by reference)
 * @param int $perpage Items per page (by reference)
 * @param int $maxperpage Maximum items per page
 * @param int $defaultperpage Default items per page
 * @return void
 */
function validatePerPage(int $numresults, int &$page, int &$perpage, int $maxperpage = 20, int $defaultperpage = 20): void
{
    $perpage = (int)$perpage;
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } elseif ($maxperpage < $perpage) {
        $perpage = $maxperpage;
    }
    
    $totalPages = (int)ceil($numresults / $perpage);
    if ($totalPages === 0) {
        $totalPages = 1;
    }
    
    if ($page < 1) {
        $page = 1;
    } elseif ($totalPages < $page) {
        $page = $totalPages;
    }
}

/**
 * Calculate pagination item range
 * 
 * @param int $pagenumber Current page number
 * @param int $perpage Items per page
 * @param int $total Total items
 * @return array Array with 'first' and 'last' item numbers
 */
function calculatePagination(int $pagenumber, int $perpage, int $total): array
{
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    
    $paginationFirstItem++;
    
    return [
        'first' => number_format($paginationFirstItem),
        'last' => number_format($paginationLastItem)
    ];
}

/**
 * Build pagination links HTML
 * 
 * @param int $perpage Items per page
 * @param int $results Total results
 * @param string $address Base URL for links
 * @return array [pagination HTML, LIMIT clause]
 */
function buildPaginationLinks(int $perpage, int $results, string $address): array
{
    if ($results < $perpage) {
        return ['', ''];
    }
    
    $queryResult = $results ? (int)ceil($results / $perpage) : 0;
    $pagenumber = (int)($_GET['page'] ?? $_POST['page'] ?? 1);
    
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
    
    if ($results <= $perpage) {
        return ['', 'LIMIT ' . $limitOffset . ', ' . $perpage];
    }
    
    $total = number_format($results);
    $paginationHtml = [
        'pagenav' => true,
        'prev' => false,
        'next' => false,
        'first' => false,
        'last' => false
    ];
    
    // Previous page
    if ($pagenumber > 1) {
        $previousPage = $pagenumber - 1;
        $previousPageInfo = calculatePagination($previousPage, $perpage, $results);
        $paginationHtml['prev'] = true;
    }
    
    // Next page
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPageNumber, $perpage, $results);
        $paginationHtml['next'] = true;
    }
    
    $firstPageInfo = calculatePagination(1, $perpage, $results);
    $lastPageInfo = calculatePagination($queryResult, $perpage, $results);
    
    $previousPageQuery = isset($previousPage) && $previousPage !== 1 ? 'page=' . $previousPage : '';
    
    $paginationLinks = "\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTableNoBorder\">\n" .
        "\t\t<tr>\n\t\t\t<td style=\"padding: 0px 0px 1px 0px;\">\n" .
        "\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\n" .
        "\t\t\t\t\t<ul>\n" .
        "\t\t\t\t\t\t<li>" . escape_html($pagenumber . ' - ' . $queryResult) . "</li>\n" .
        ($paginationHtml['first'] ? "\t\t\t\t\t\t<li><a class=\"smalltext\" href=\"" . escape_attr($address) . "\" title=\"First Page - Show Results " . escape_attr($firstPageInfo['first']) . " to " . escape_attr($firstPageInfo['last']) . " of " . escape_attr($total) . "\">&laquo; First</a></li>\n" : '') .
        ($paginationHtml['prev'] ? "\t\t\t\t\t\t<li><a class=\"smalltext\" href=\"" . escape_attr($address . $previousPageQuery) . "\" title=\"Previous Page - Show Results " . escape_attr($previousPageInfo['first']) . " to " . escape_attr($previousPageInfo['last']) . " of " . escape_attr($total) . "\">&lt;</a></li>\n" : '') .
        ($paginationHtml['next'] ? "\t\t\t\t\t\t<li><a class=\"smalltext\" href=\"" . escape_attr($address . 'page=' . $nextPageNumber) . "\" title=\"Next Page - Show Results " . escape_attr($nextPageInfo['first']) . " to " . escape_attr($nextPageInfo['last']) . " of " . escape_attr($total) . "\">&gt;</a></li>\n" : '') .
        ($paginationHtml['last'] ? "\t\t\t\t\t\t<li><a class=\"smalltext\" href=\"" . escape_attr($address . 'page=' . $queryResult) . "\" title=\"Last Page - Show Results " . escape_attr($lastPageInfo['first']) . " to " . escape_attr($lastPageInfo['last']) . " of " . escape_attr($total) . "\">Last <strong>&raquo;</strong></a></li>\n" : '') .
        "\t\t\t\t\t</ul>\n" .
        "\t\t\t\t</div>\n" .
        "\t\t\t</td>\n" .
        "\t\t</tr>\n" .
        "\t</table>";
    
    return [$paginationLinks, 'LIMIT ' . $limitOffset . ', ' . $perpage];
}

?>