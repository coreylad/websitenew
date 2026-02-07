<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/manage_database.lang");
$Message = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $table_names = isset($_POST["table_names"]) ? $_POST["table_names"] : "";
    if (is_array($table_names) && count($table_names)) {
        if (isset($_POST["backup"])) {
            if (function_263($table_names)) {
                $Message = function_76($Language[18]);
            } else {
                $Message = function_76($Language[17]);
            }
        } else {
            if (isset($_POST["repair"])) {
                $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[8] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                foreach ($table_names as $Table) {
                    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "REPAIR TABLE `" . $Table . "`");
                    $Res = mysqli_fetch_array($Query);
                    $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Table . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Res["Msg_text"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                }
                $Message .= "\r\n\t\t\t</table>";
            } else {
                if (isset($_POST["optimize"])) {
                    $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[15] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                    foreach ($table_names as $Table) {
                        $Query = mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE `" . $Table . "`");
                        $Res = mysqli_fetch_array($Query);
                        $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Table . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Res["Msg_text"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                    }
                    $Message .= "\r\n\t\t\t</table>";
                } else {
                    if (isset($_POST["check"])) {
                        $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\" class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[16] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
                        foreach ($table_names as $Table) {
                            $Query = mysqli_query($GLOBALS["DatabaseConnect"], "CHECK TABLE `" . $Table . "`");
                            $Res = mysqli_fetch_array($Query);
                            $Message .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Table . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Res["Msg_text"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                        }
                        $Message .= "\r\n\t\t\t</table>";
                    }
                }
            }
        }
    }
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_database\" $method = \"post\" $name = \"manage_database\">\r\n\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td $colspan = \"11\" class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">" . $Language[3] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[7] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[9] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[10] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[11] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[12] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[13] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[14] . "</td>\r\n\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('manage_database', this, 'group');\"></td>\r\n\t</tr>\r\n";
$DBTables = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW TABLES");
while ($mysql = mysqli_fetch_row($query)) {
    $DBTables[] = $mysql[0];
}
$totalrows = $totaldsize = $totalisize = 0;
foreach ($DBTables as $Table) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW TABLE STATUS LIKE '" . $Table . "'");
    $Res = mysqli_fetch_assoc($Query);
    echo "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Res["Name"] . "</td>\r\n\t\t<td class=\"alt1\">" . $Res["Engine"] . "</td>\r\n\t\t<td class=\"alt1\">" . number_format($Res["Rows"]) . "</td>\r\n\t\t<td class=\"alt1\">" . var_238($Res["Data_length"]) . "</td>\r\n\t\t<td class=\"alt1\">" . var_238($Res["Index_length"]) . "</td>\r\n\t\t<td class=\"alt1\">" . (0 < $Res["Data_free"] ? "<font $color = \"red\"><b>" . var_238($Res["Data_free"]) . "</b></font>" : var_238($Res["Data_free"])) . "</td>\r\n\t\t<td class=\"alt1\">" . function_84($Res["Create_time"]) . "</td>\r\n\t\t<td class=\"alt1\">" . function_84($Res["Update_time"]) . "</td>\r\n\t\t<td class=\"alt1\">" . function_84($Res["Check_time"]) . "</td>\r\n\t\t<td class=\"alt1\">" . $Res["Collation"] . "</td>\r\n\t\t<td class=\"alt1\" $align = \"center\"><input $type = \"checkbox\" $name = \"table_names[]\" $value = \"" . $Res["Name"] . "\" $checkme = \"group\" /></td>\r\n\t</tr>\r\n\t";
    $totalrows += $Res["Rows"];
    $totaldsize += $Res["Data_length"];
    $totalisize += $Res["Index_length"];
}
echo "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"11\">\r\n\t\t\t<input $type = \"submit\" $name = \"backup\" $value = \"" . $Language[6] . "\" /> <input $type = \"submit\" $name = \"repair\" $value = \"" . $Language[8] . "\" /> <input $type = \"submit\" $name = \"optimize\" $value = \"" . $Language[15] . "\" /> <input $type = \"submit\" $name = \"check\" $value = \"" . $Language[16] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
function function_75()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function function_77()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_236("../index.php");
    }
}
function function_78($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_88($bytes = 0)
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
function function_84($timestamp = "")
{
    $var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($var_265, $timestamp);
}
function function_264()
{
    $hash = md5(uniqid(rand(), true));
    return $hash;
}
function function_263($tables)
{
    $var_567 = "../admin/backup/";
    if (is_dir($var_567) && is_writable($var_567)) {
        $file = $var_567 . "backup_" . function_264();
        if (function_exists("gzopen")) {
            $fp = gzopen($file . ".sql.gz", "w9");
        } else {
            $fp = fopen($file . ".sql", "w");
        }
        $time = date("dS F Y \\a\\t H:i", time());
        $header = "-- -------------------------------------\n-- TS SE Database Backup\n-- Generated: " . $time . "\n-- -------------------------------------\n\n";
        $var_568 = $header;
        foreach ($tables as $var_569) {
            $var_570 = [];
            $var_571 = var_572($var_569);
            foreach ($var_571 as $var_573) {
                $var_570[] = $var_573["Field"];
            }
            $var_574 = implode(",", $var_570);
            $var_575 = var_576($var_569) . ";\n";
            $var_568 .= $var_575;
            var_577($fp, $var_568);
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . $var_569);
            while ($row = mysqli_fetch_array($query)) {
                $var_578 = "INSERT INTO " . $var_569 . " (" . $var_574 . ") VALUES (";
                $var_579 = "";
                foreach ($var_570 as $var_573) {
                    if (!isset($row[$var_573]) || trim($row[$var_573]) == "") {
                        $var_578 .= $var_579 . "''";
                    } else {
                        $var_578 .= $var_579 . "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $row[$var_573]) . "'";
                    }
                    $var_579 = ",";
                }
                $var_578 .= ");\n";
                $var_568 .= $var_578;
                var_577($fp, $var_568);
            }
        }
        return true;
    } else {
        return false;
    }
}
function function_265($table)
{
    $var_580 = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW FIELDS FROM " . $table);
    while ($var_573 = mysqli_fetch_array($query)) {
        $var_580[] = $var_573;
    }
    return $var_580;
}
function function_266($table)
{
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW CREATE TABLE " . $table);
    $var_575 = mysqli_fetch_array($query);
    return $var_575["Create Table"];
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