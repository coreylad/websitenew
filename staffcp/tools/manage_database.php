<?php
declare(strict_types=1);

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_database.lang");
$Message = "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $table_names = isset($_POST["table_names"]) ? $_POST["table_names"] : "";
    if (is_array($table_names) && count($table_names)) {
        try {
            if (isset($_POST["backup"])) {
                if (function_263($table_names)) {
                    $Message = showAlertError($Language[18]);
                } else {
                    $Message = showAlertError($Language[17]);
                }
            } else {
                if (isset($_POST["repair"])) {
                    $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                    foreach ($table_names as $Table) {
                        $Table = preg_replace('/[^a-zA-Z0-9_]/', '', $Table);
                        $query = $GLOBALS["DatabaseConnect"]->query("REPAIR TABLE `" . $Table . "`");
                        $Res = $query->fetch_array();
                        $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Table, ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Res["Msg_text"], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                    }
                    $Message .= "\r\n\t\t\t</table>";
                } else {
                    if (isset($_POST["optimize"])) {
                        $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[15], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                        foreach ($table_names as $Table) {
                            $Table = preg_replace('/[^a-zA-Z0-9_]/', '', $Table);
                            $query = $GLOBALS["DatabaseConnect"]->query("OPTIMIZE TABLE `" . $Table . "`");
                            $Res = $query->fetch_array();
                            $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Table, ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Res["Msg_text"], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                        }
                        $Message .= "\r\n\t\t\t</table>";
                    } else {
                        if (isset($_POST["check"])) {
                            $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[16], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                            foreach ($table_names as $Table) {
                                $Table = preg_replace('/[^a-zA-Z0-9_]/', '', $Table);
                                $query = $GLOBALS["DatabaseConnect"]->query("CHECK TABLE `" . $Table . "`");
                                $Res = $query->fetch_array();
                                $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Table, ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Res["Msg_text"], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                            }
                            $Message .= "\r\n\t\t\t</table>";
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error managing database: " . $e->getMessage());
            $Message = showAlertError("An error occurred while performing the operation.");
        }
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked == false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"" . htmlspecialchars($_SERVER["SCRIPT_NAME"], ENT_QUOTES, 'UTF-8') . "?do=manage_database\" $method = \"post\" $name = \"manage_database\">\r\n<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . "\" />\r\n\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td $colspan = \"11\" class=\"tcat\" $align = \"center\">\r\n\t\t\t" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[3], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[4], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[5], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[7], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[9], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[10], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[11], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[12], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[13], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\">" . htmlspecialchars($Language[14], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('manage_database', this, 'group');\"></td>\r\n\t</tr>\r\n";

try {
    $DBTables = [];
    $query = $GLOBALS["DatabaseConnect"]->query("SHOW TABLES");
    while ($mysql = $query->fetch_row()) {
        $DBTables[] = $mysql[0];
    }
    
    $totalrows = $totaldsize = $totalisize = 0;
    foreach ($DBTables as $Table) {
        $stmt = $GLOBALS["DatabaseConnect"]->prepare("SHOW TABLE STATUS LIKE ?");
        $stmt->bind_param("s", $Table);
        $stmt->execute();
        $result = $stmt->get_result();
        $Res = $result->fetch_assoc();
        $stmt->close();
        
        echo "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars($Res["Name"], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars($Res["Engine"], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt1\">" . number_format($Res["Rows"]) . "</td>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars(formatBytes($Res["Data_length"]), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars(formatBytes($Res["Index_length"]), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt1\">" . (0 < $Res["Data_free"] ? "<font $color = \"red\"><b>" . htmlspecialchars(formatBytes($Res["Data_free"]), ENT_QUOTES, 'UTF-8') . "</b></font>" : htmlspecialchars(formatBytes($Res["Data_free"]), ENT_QUOTES, 'UTF-8')) . "</td>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars(formatTimestamp($Res["Create_time"]), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars(formatTimestamp($Res["Update_time"]), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars(formatTimestamp($Res["Check_time"]), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars($Res["Collation"], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t<td class=\"alt1\" $align = \"center\"><input $type = \"checkbox\" $name = \"table_names[]\" $value = \"" . htmlspecialchars($Res["Name"], ENT_QUOTES, 'UTF-8') . "\" $checkme = \"group\" /></td>\r\n\t</tr>\r\n\t";
        $totalrows += $Res["Rows"];
        $totaldsize += $Res["Data_length"];
        $totalisize += $Res["Index_length"];
    }
} catch (Exception $e) {
    error_log("Error fetching database tables: " . $e->getMessage());
}

echo "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"11\">\r\n\t\t\t<input $type = \"submit\" $name = \"backup\" $value = \"" . htmlspecialchars($Language[6], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"submit\" $name = \"repair\" $value = \"" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"submit\" $name = \"optimize\" $value = \"" . htmlspecialchars($Language[15], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"submit\" $name = \"check\" $value = \"" . htmlspecialchars($Language[16], ENT_QUOTES, 'UTF-8') . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
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
function logStaffAction($log): void
{
    try {
        $stmt = $GLOBALS["DatabaseConnect"]->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (?, ?, ?)");
        $uid = $_SESSION["ADMIN_ID"];
        $time = time();
        $stmt->bind_param("sis", $uid, $time, $log);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error logging staff action: " . $e->getMessage());
    }
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
function function_264()
{
    $hash = md5(uniqid(rand(), true));
    return $hash;
}
function function_263($tables)
{
    $dbHost = "../admin/backup/";
    if (is_dir($dbHost) && is_writable($dbHost)) {
        $file = $dbHost . "backup_" . function_264();
        if (function_exists("gzopen")) {
            $fp = gzopen($file . ".sql.gz", "w9");
        } else {
            $fp = fopen($file . ".sql", "w");
        }
        $time = date("dS F Y \\a\\t H:i", time());
        $header = "-- -------------------------------------\n-- TS SE Database Backup\n-- Generated: " . $time . "\n-- -------------------------------------\n\n";
        $tableRow = $header;
        
        try {
            foreach ($tables as $dbUser) {
                $dbPass = [];
                $dbName = function_265($dbUser);
                foreach ($dbName as $dbTable) {
                    $dbPass[] = $dbTable["Field"];
                }
                $dbQuery = implode(",", $dbPass);
                $dbResult = function_266($dbUser) . ";\n";
                $tableRow .= $dbResult;
                function_267($fp, $tableRow);
                
                $query = $GLOBALS["DatabaseConnect"]->query("SELECT * FROM " . $dbUser);
                while ($row = $query->fetch_array()) {
                    $dbError = "INSERT INTO " . $dbUser . " (" . $dbQuery . ") VALUES (";
                    $dbConnection = "";
                    foreach ($dbPass as $dbTable) {
                        if (!isset($row[$dbTable]) || trim($row[$dbTable]) == "") {
                            $dbError .= $dbConnection . "''";
                        } else {
                            $dbError .= $dbConnection . "'" . $GLOBALS["DatabaseConnect"]->real_escape_string($row[$dbTable]) . "'";
                        }
                        $dbConnection = ",";
                    }
                    $dbError .= ");\n";
                    $tableRow .= $dbError;
                    function_267($fp, $tableRow);
                }
            }
        } catch (Exception $e) {
            error_log("Error during backup: " . $e->getMessage());
            return false;
        }
        
        return true;
    } else {
        return false;
    }
}
function function_265($table)
{
    try {
        $dbBackup = [];
        $query = $GLOBALS["DatabaseConnect"]->query("SHOW FIELDS FROM " . $table);
        while ($dbTable = $query->fetch_array()) {
            $dbBackup[] = $dbTable;
        }
        return $dbBackup;
    } catch (Exception $e) {
        error_log("Error getting table fields: " . $e->getMessage());
        return [];
    }
}
function function_266($table)
{
    try {
        $query = $GLOBALS["DatabaseConnect"]->query("SHOW CREATE TABLE " . $table);
        $dbResult = $query->fetch_array();
        return $dbResult["Create Table"];
    } catch (Exception $e) {
        error_log("Error getting table create statement: " . $e->getMessage());
        return "";
    }
}
function function_267($fp, &$contents)
{
    if (function_exists("gzopen")) {
        gzwrite($fp, $contents);
    } else {
        fwrite($fp, $contents);
    }
    $contents = "";
}

?>