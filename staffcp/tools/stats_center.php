<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('stats_center');

// Initialize variables
$Message = '';
$stats_type = '';
$show_type = '';
$date_from = '';
$date_to = '';
$GeneratedChartImage = '';

// Process form submission
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // Get and sanitize input with null coalescing and type casting
    $stats_type = (string)($_POST['stats_type'] ?? '');
    $show_type = (string)($_POST['show_type'] ?? '');
    $date_from = (string)($_POST['date_from'] ?? '');
    $date_to = (string)($_POST['date_to'] ?? '');
    
    // Validate form token for CSRF protection
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[30] ?? 'Invalid form token. Please try again.');
    }
    elseif ($stats_type && $show_type && $date_from && $date_to) {
        // Validate and convert timestamps
        $timestamp_from = strtotime($date_from);
        $timestamp_to = strtotime($date_to);
        
        if ($timestamp_from === false || $timestamp_to === false) {
            $Message = showAlertErrorModern($Language[4] ?? 'Invalid date format');
        } else {
            global $TSDatabase;
            
            try {
                switch ($stats_type) {
                    case '5':
                        $stmt = $TSDatabase->query(
                            'SELECT added FROM users WHERE UNIX_TIMESTAMP(added) >= ? AND UNIX_TIMESTAMP(added) <= ? ORDER BY added ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'added')
                                : generateMonthlyChart($stmt, 'added');
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '6':
                        $stmt = $TSDatabase->query(
                            'SELECT dateline FROM tsf_posts WHERE dateline >= ? AND dateline <= ? ORDER BY dateline ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'dateline', false)
                                : generateMonthlyChart($stmt, 'dateline', false);
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '7':
                        $stmt = $TSDatabase->query(
                            'SELECT added FROM comments WHERE UNIX_TIMESTAMP(added) >= ? AND UNIX_TIMESTAMP(added) <= ? ORDER BY added ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'added')
                                : generateMonthlyChart($stmt, 'added');
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '8':
                        $stmt = $TSDatabase->query(
                            'SELECT added FROM messages WHERE UNIX_TIMESTAMP(added) >= ? AND UNIX_TIMESTAMP(added) <= ? ORDER BY added ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'added')
                                : generateMonthlyChart($stmt, 'added');
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '9':
                        $stmt = $TSDatabase->query(
                            'SELECT completedat FROM snatched WHERE UNIX_TIMESTAMP(completedat) >= ? AND UNIX_TIMESTAMP(completedat) <= ? AND finished = ? AND completedat != ? ORDER BY completedat ASC',
                            [$timestamp_from, $timestamp_to, 'yes', '0000-00-00 00:00:00']
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'completedat')
                                : generateMonthlyChart($stmt, 'completedat');
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '10':
                        $stmt = $TSDatabase->query(
                            'SELECT added FROM torrents WHERE UNIX_TIMESTAMP(added) >= ? AND UNIX_TIMESTAMP(added) <= ? ORDER BY added ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'added')
                                : generateMonthlyChart($stmt, 'added');
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '11':
                        $stmt = $TSDatabase->query(
                            'SELECT added FROM requests WHERE UNIX_TIMESTAMP(added) >= ? AND UNIX_TIMESTAMP(added) <= ? ORDER BY added ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'added')
                                : generateMonthlyChart($stmt, 'added');
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '12':
                        $stmt = $TSDatabase->query(
                            'SELECT dateline FROM tsf_poll WHERE dateline >= ? AND dateline <= ? ORDER BY dateline ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'dateline', false)
                                : generateMonthlyChart($stmt, 'dateline', false);
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '13':
                        $stmt = $TSDatabase->query(
                            'SELECT dateline FROM tsf_threads WHERE dateline >= ? AND dateline <= ? ORDER BY dateline ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'dateline', false)
                                : generateMonthlyChart($stmt, 'dateline', false);
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                        
                    case '14':
                        $stmt = $TSDatabase->query(
                            'SELECT added FROM news WHERE UNIX_TIMESTAMP(added) >= ? AND UNIX_TIMESTAMP(added) <= ? ORDER BY added ASC',
                            [$timestamp_from, $timestamp_to]
                        );
                        if ($stmt && $stmt->rowCount() > 0) {
                            $GeneratedChartImage = ($show_type === '22') 
                                ? generateYearlyChart($stmt, 'added')
                                : generateMonthlyChart($stmt, 'added');
                        } else {
                            $Message = showAlertErrorModern($Language[3] ?? 'No data found for selected period');
                        }
                        break;
                }
            } catch (PDOException $e) {
                $Message = showAlertErrorModern($Language[31] ?? 'Database error occurred');
                error_log('Stats center error: ' . $e->getMessage());
            }
        }
    } else {
        $Message = showAlertErrorModern($Language[4] ?? 'Please fill all required fields');
    }
}

// Build stats type dropdown
$StatsTypes = "\n<select name=\"stats_type\" style=\"width: 150px;\">";
for ($i = 5; $i <= 14; $i++) {
    $selected = ($stats_type === (string)$i) ? ' selected="selected"' : '';
    $StatsTypes .= "\n\t<option value=\"" . escape_attr((string)$i) . "\"" . $selected . ">" 
                   . escape_html($Language[$i] ?? "Option $i") . "</option>";
}
$StatsTypes .= "\n</select>";

// Build show by dropdown
$ShowBy = "\n<select name=\"show_type\" style=\"width: 150px;\">";
for ($i = 22; $i <= 23; $i++) {
    $selected = ($show_type === (string)$i) ? ' selected="selected"' : '';
    $ShowBy .= "\n\t<option value=\"" . escape_attr((string)$i) . "\"" . $selected . ">" 
               . escape_html($Language[$i] ?? "Option $i") . "</option>";
}
$ShowBy .= "\n</select>";

// Get main config from database using PDO
$MAIN = [];
try {
    global $TSDatabase;
    $stmt = $TSDatabase->query('SELECT content FROM ts_config WHERE configname = ?', ['MAIN']);
    if ($stmt) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['content'])) {
            $MAIN = unserialize($result['content']);
        }
    }
} catch (Exception $e) {
    error_log('Failed to load MAIN config: ' . $e->getMessage());
}

// Output HTML
echo "\n<script type=\"text/javascript\">\n\t$(function()\n\t{\n\t\t$(\"#date_from,#date_to\").datepicker({dateFormat: \"dd-mm-yy\", changeMonth: true, changeYear: true, closeText: \"X\", showButtonPanel: true});\n\t});\n</script>\n<script type=\"text/javascript\">\n\tfunction PleaseWait()\n\t{\n\t\tTSGetID(\"pleasewait\").disabled = \"disabled\";\n\t\tTSGetID(\"pleasewait\").value = \"" . escape_js($Language[29] ?? 'Please wait...') . "\";\n\t}\n</script>\n\n" . $Message . "\n";

if ($GeneratedChartImage) {
    echo "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\n\t<tr>\n\t\t<td class=\"tcat\" align=\"center\">\n\t\t\t" 
         . escape_html($Language[26] ?? 'Generated Chart') 
         . "\n\t\t</td>\n\t</tr>\n\t<tr>\n\t\t<td class=\"alt1\" align=\"center\">\n\t\t\t" 
         . $GeneratedChartImage 
         . "\n\t\t</td>\n\t</tr>\n</table>\n<br />\n";
}

echo "\n<form method=\"post\" action=\"index.php?do=stats_center\" name=\"stats_center\" onsubmit=\"PleaseWait();\">\n"
     . getFormTokenField() 
     . "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\n\t<tr>\n\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\n\t\t\t" 
     . escape_html($Language[2] ?? 'Statistics Center') 
     . "\n\t\t</td>\n\t</tr>\n\t<tr valign=\"top\">\n\t\t<td class=\"alt1\">" 
     . escape_html($Language[17] ?? 'Stats Type') 
     . "</td>\n\t\t<td class=\"alt1\">\n\t\t\t" 
     . $StatsTypes 
     . "\n\t\t</td>\n\t</tr>\n\t<tr valign=\"top\">\n\t\t<td class=\"alt1\">" 
     . escape_html($Language[21] ?? 'Show By') 
     . "</td>\n\t\t<td class=\"alt1\">\n\t\t\t" 
     . $ShowBy 
     . "\n\t\t</td>\n\t</tr>\n\t<tr valign=\"top\">\n\t\t<td class=\"alt1\">" 
     . escape_html($Language[15] ?? 'Date From') 
     . "</td>\n\t\t<td class=\"alt1\">\n\t\t\t<input type=\"text\" class=\"bginput\" name=\"date_from\" id=\"date_from\" value=\"" 
     . escape_attr($date_from) 
     . "\" size=\"20\" dir=\"ltr\" tabindex=\"1\" />\n\t\t</td>\n\t</tr>\n\t<tr valign=\"top\">\n\t\t<td class=\"alt1\">" 
     . escape_html($Language[16] ?? 'Date To') 
     . "</td>\n\t\t<td class=\"alt1\">\n\t\t\t<input type=\"text\" class=\"bginput\" name=\"date_to\" id=\"date_to\" value=\"" 
     . escape_attr($date_to) 
     . "\" size=\"20\" dir=\"ltr\" tabindex=\"1\" />\n\t\t</td>\n\t</tr>\n\t<tr>\n\t\t<td class=\"tcat2\"></td>\n\t\t<td class=\"tcat2\">\n\t\t\t<input type=\"submit\" class=\"button\" id=\"pleasewait\" tabindex=\"1\" value=\"" 
     . escape_attr($Language[26] ?? 'Generate') 
     . "\" accesskey=\"s\" />\n\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"" 
     . escape_attr($Language[27] ?? 'Reset') 
     . "\" accesskey=\"r\" />\n\t\t</td>\n\t</tr>\n</table>\n</form>";

/**
 * Generate yearly statistics chart
 * 
 * @param PDOStatement $stmt Query result statement
 * @param string $field Field name to extract
 * @param bool $strtotime Whether to use strtotime on field value
 * @return string Chart HTML
 */
function generateYearlyChart(PDOStatement $stmt, string $field, bool $strtotime = true): string
{
    $statValue = [];
    
    while ($currentRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($currentRow[$field])) {
            continue;
        }
        
        $timestamp = $strtotime ? strtotime($currentRow[$field]) : (int)$currentRow[$field];
        if ($timestamp === false || $timestamp === 0) {
            continue;
        }
        
        $year = date('Y', $timestamp);
        $statValue[$year] = ($statValue[$year] ?? 0) + 1;
    }
    
    if (empty($statValue)) {
        return '';
    }
    
    $statFormatted = 'http://chart.apis.google.com/chart?cht=p3&chd=t:{1}&chs=600x300&chl={2}';
    $statsArray = [];
    $chartData = [];
    
    foreach ($statValue as $year => $count) {
        $statsArray[] = $count;
        $chartData[] = escape_html($year . ' (' . number_format($count) . ')');
    }
    
    $visualData = number_format(array_sum($statsArray));
    $chartUrl = str_replace(
        ['{1}', '{2}'], 
        [implode(',', $statsArray), implode('|', $chartData)], 
        $statFormatted
    );
    
    return '<img src="' . escape_attr($chartUrl) . '" border="0" alt="' . escape_attr($visualData) . '" title="' . escape_attr($visualData) . '" />';
}

/**
 * Generate monthly statistics chart grouped by year
 * 
 * @param PDOStatement $stmt Query result statement
 * @param string $field Field name to extract
 * @param bool $strtotime Whether to use strtotime on field value
 * @return string Chart HTML
 */
function generateMonthlyChart(PDOStatement $stmt, string $field, bool $strtotime = true): string
{
    global $Language;
    
    $statValue = [];
    
    while ($currentRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($currentRow[$field])) {
            continue;
        }
        
        $timestamp = $strtotime ? strtotime($currentRow[$field]) : (int)$currentRow[$field];
        if ($timestamp === false || $timestamp === 0) {
            continue;
        }
        
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        
        if (!isset($statValue[$year])) {
            $statValue[$year] = [];
        }
        
        $statValue[$year][$month] = ($statValue[$year][$month] ?? 0) + 1;
    }
    
    if (empty($statValue)) {
        return '';
    }
    
    $statFormatted = 'http://chart.apis.google.com/chart?cht=p3&chd=t:{1}&chs=800x375&chl={2}';
    $chartLabels = explode(',', $Language[28] ?? 'Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec');
    $statName = '';
    
    ksort($statValue);
    
    foreach ($statValue as $year => $months) {
        $statsArray = [];
        $chartData = [];
        
        ksort($months);
        
        foreach ($months as $month => $count) {
            $statsArray[] = $count;
            $monthIndex = (int)$month - 1;
            $monthName = $chartLabels[$monthIndex] ?? $month;
            $chartData[] = escape_html($monthName . ' ' . $year . ' (' . number_format($count) . ')');
        }
        
        $visualData = number_format(array_sum($statsArray));
        $chartUrl = str_replace(
            ['{1}', '{2}'], 
            [implode(',', $statsArray), implode('|', $chartData)], 
            $statFormatted
        );
        
        $statName .= '<img src="' . escape_attr($chartUrl) . '" border="0" alt="' . escape_attr($visualData) . '" title="' . escape_attr($visualData) . '" />';
    }
    
    return $statName;
}

// Legacy compatibility functions - retained for backward compatibility
function getStaffLanguage(): string
{
    return getStaffLanguageModern();
}

function checkStaffAuthentication(): void
{
    if (!defined('IN-TSSE-STAFF-PANEL')) {
        redirectTo('../index.php');
    }
}

function showAlertError(string $error): string
{
    return '<div class="alert"><div>' . escape_html($error) . '</div></div>';
}

?>