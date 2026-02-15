<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_database.lang");
$Message = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $table_names = isset($_POST["table_names"]) ? $_POST["table_names"] : "";
    if (is_array($table_names) && count($table_names)) {
        if (isset($_POST["backup"])) {
            if (function_263($table_names)) {
                $Message = showAlertError($Language[18]);
            } else {
                $Message = showAlertError($Language[17]);
            }
        } else {
            if (isset($_POST["repair"])) {
                $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[8] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                foreach ($table_names as $Table) {
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "REPAIR TABLE `" . $Table . "`");
                    $Res = mysqli_fetch_array($query);
                    $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Table . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Res["Msg_text"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                }
                $Message .= "\r\n\t\t\t</table>";
            } else {
                if (isset($_POST["optimize"])) {
                    $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[15] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                    foreach ($table_names as $Table) {
                        $query = mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE `" . $Table . "`");
                        $Res = mysqli_fetch_array($query);
                        $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Table . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Res["Msg_text"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                    }
                    $Message .= "\r\n\t\t\t</table>";
                } else {
                    if (isset($_POST["check"])) {
                        $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[16] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                        foreach ($table_names as $Table) {
                            $query = mysqli_query($GLOBALS["DatabaseConnect"], "CHECK TABLE `" . $Table . "`");
                            $Res = mysqli_fetch_array($query);
                            $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Table . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Res["Msg_text"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                        }
                        $Message .= "\r\n\t\t\t</table>";
                    }
                }
            }
        }
    }
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked == false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_database\" $method = \"post\" $name = \"manage_database\">\r\n\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td $colspan = \"11\" class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">" . $Language[3] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[7] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[9] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[10] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[11] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[12] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[13] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[14] . "</td>\r\n\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('manage_database', this, 'group');\"></td>\r\n\t</tr>\r\n";
$DBTables = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW TABLES");
while ($mysql = mysqli_fetch_row($query)) {
    $DBTables[] = $mysql[0];
}
$totalrows = $totaldsize = $totalisize = 0;
foreach ($DBTables as $Table) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW TABLE STATUS LIKE '" . $Table . "'");
    $Res = mysqli_fetch_assoc($query);
    echo "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Res["Name"] . "</td>\r\n\t\t<td class=\"alt1\">" . $Res["Engine"] . "</td>\r\n\t\t<td class=\"alt1\">" . number_format($Res["Rows"]) . "</td>\r\n\t\t<td class=\"alt1\">" . formatBytes($Res["Data_length"]) . "</td>\r\n\t\t<td class=\"alt1\">" . formatBytes($Res["Index_length"]) . "</td>\r\n\t\t<td class=\"alt1\">" . (0 < $Res["Data_free"] ? "<font $color = \"red\"><b>" . formatBytes($Res["Data_free"]) . "</b></font>" : formatBytes($Res["Data_free"])) . "</td>\r\n\t\t<td class=\"alt1\">" . formatTimestamp($Res["Create_time"]) . "</td>\r\n\t\t<td class=\"alt1\">" . formatTimestamp($Res["Update_time"]) . "</td>\r\n\t\t<td class=\"alt1\">" . formatTimestamp($Res["Check_time"]) . "</td>\r\n\t\t<td class=\"alt1\">" . $Res["Collation"] . "</td>\r\n\t\t<td class=\"alt1\" $align = \"center\"><input $type = \"checkbox\" $name = \"table_names[]\" $value = \"" . $Res["Name"] . "\" $checkme = \"group\" /></td>\r\n\t</tr>\r\n\t";
    $totalrows += $Res["Rows"];
    $totaldsize += $Res["Data_length"];
    $totalisize += $Res["Index_length"];
}
echo "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"11\">\r\n\t\t\t<input $type = \"submit\" $name = \"backup\" $value = \"" . $Language[6] . "\" /> <input $type = \"submit\" $name = \"repair\" $value = \"" . $Language[8] . "\" /> <input $type = \"submit\" $name = \"optimize\" $value = \"" . $Language[15] . "\" /> <input $type = \"submit\" $name = \"check\" $value = \"" . $Language[16] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
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
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
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
        foreach ($tables as $dbUser) {
            $dbPass = [];
            $dbName = dbBackup($dbUser);
            foreach ($dbName as $dbTable) {
                $dbPass[] = $dbTable["Field"];
            }
            $dbQuery = implode(",", $dbPass);
            $dbResult = dbRestore($dbUser) . ";\n";
            $tableRow .= $dbResult;
            dbOptimize($fp, $tableRow);
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . $dbUser);
            while ($row = mysqli_fetch_array($query)) {
                $dbError = "INSERT INTO " . $dbUser . " (" . $dbQuery . ") VALUES (";
                $dbConnection = "";
                foreach ($dbPass as $dbTable) {
                    if (!isset($row[$dbTable]) || trim($row[$dbTable]) == "") {
                        $dbError .= $dbConnection . "''";
                    } else {
                        $dbError .= $dbConnection . "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $row[$dbTable]) . "'";
                    }
                    $dbConnection = ",";
                }
                $dbError .= ");\n";
                $tableRow .= $dbError;
                dbOptimize($fp, $tableRow);
            }
        }
        return true;
    } else {
        return false;
    }
}
function function_265($table)
{
    $dbBackup = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW FIELDS FROM " . $table);
    while ($dbTable = mysqli_fetch_array($query)) {
        $dbBackup[] = $dbTable;
    }
    return $dbBackup;
}
function function_266($table)
{
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW CREATE TABLE " . $table);
    $dbResult = mysqli_fetch_array($query);
    return $dbResult["Create Table"];
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