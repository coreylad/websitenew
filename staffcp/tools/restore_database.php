<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/restore_database.lang");
$backuppath = "./../admin/backup/";
$Message = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["backupfiles"]) && 0 < count($_POST["backupfiles"])) {
    ini_set("memory_limit", "256M");
    set_time_limit(0);
    if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
        $mysqldumppath = isset($_POST["mysqldumppath"]) ? trim($_POST["mysqldumppath"]) : "";
        $databasename = isset($_POST["databasename"]) ? trim($_POST["databasename"]) : MYSQL_DB;
        if ($mysqldumppath) {
            @mysqli_query($GLOBALS["DatabaseConnect"], "CREATE DATABASE IF NOT EXISTS `" . $databasename . "`");
            foreach ($_POST["backupfiles"] as $backupfile) {
                if (function_149($backupfile) == "sql") {
                    $DONEMYSQL = true;
                    system($mysqldumppath . "mysql --verbose --$user = " . MYSQL_USER . " --$password = " . MYSQL_PASS . " " . $databasename . " < " . realpath($backuppath) . "\\" . $backupfile, $retval);
                } else {
                    $Notice = showAlertMessage("\r\n\t\t\t\t\tCompressed file(s) does not supported on Windows Server. There are 2 ways to restore those file(s).<br><br>\r\n\t\t\t\t\t1) Use PHPMyAdmin to restore any compressed backup file..<br><br>\r\n\t\t\t\t\t2)<br>\r\n\t\t\t\t\t- Download the compressed backup file.<br>\r\n\t\t\t\t\t- Extract it on your computer (you can use Winrar, Winzip or 7zip).<br>\r\n\t\t\t\t\t- Upload the extracted backup file (filename.sql) into admin/backup folder on your server.<br>\r\n\t\t\t\t\t- Re-run this tool and select the extract file (filename.sql) to restore.\r\n\t\t\t\t\t");
                }
            }
        }
        $hiddenFields = "";
        foreach ($_POST["backupfiles"] as $backupfile) {
            $hiddenFields .= "<input $type = \"hidden\" $name = \"backupfiles[]\" $value = \"" . $backupfile . "\" />";
        }
        echo showAlertError("\r\n\t\t" . (isset($Notice) ? $Notice : "") . "\r\n\t\t" . (!isset($DONEMYSQL) ? "<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=restore_database\" $method = \"post\" $name = \"restore_database\">\r\n\t\t" . $hiddenFields . "\r\n\t\t<table>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"2\" class=\"alt1\">\r\n\t\t\t\t\tWindows System detected! Please enter the path of <b>mysql (mysql.exe)</b> file.. If you have no idea about this, please use PHPMyAdmin to restore your database.\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">Path:</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"mysqldumppath\" $value = \"" . $mysqldumppath . "\" $size = \"70\" /> <small><b>Example</b>: D:\\\\wamp\\\\bin\\mysql\\\\mysql5.1.36\\\\bin\\\\</small>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">Database name:</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"databasename\" $value = \"" . $databasename . "\" $size = \"70\" /> <input $type = \"submit\" $value = \"" . $Language[5] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</form>" : "DONE!") . "\r\n\t\t");
        exit;
    } else {
        $files = $_POST["backupfiles"];
        $databasename = isset($_POST["databasename"]) ? trim($_POST["databasename"]) : "";
        if ($databasename) {
            @mysqli_query($GLOBALS["DatabaseConnect"], "CREATE DATABASE IF NOT EXISTS `" . $databasename . "`");
            foreach ($files as $file) {
                if (is_file($backuppath . $file) && in_array(function_149($file), ["gz", "sql"])) {
                    if (function_149($file) == "gz") {
                        system("gunzip -c " . realpath($backuppath) . "/" . $file . " | mysql -h " . MYSQL_HOST . " -u " . MYSQL_USER . " -p" . MYSQL_PASS . " " . $databasename, $retval);
                        $DONEMYSQL = true;
                    } else {
                        system("mysql --verbose --$user = " . MYSQL_USER . " --$password = " . MYSQL_PASS . " " . $databasename . " < " . realpath($backuppath) . "/" . $file, $retval);
                        $DONEMYSQL = true;
                    }
                }
            }
        }
        $hiddenFields = "";
        foreach ($files as $backupfile) {
            $hiddenFields .= "<input $type = \"hidden\" $name = \"backupfiles[]\" $value = \"" . $backupfile . "\" />";
        }
        echo showAlertError("\r\n\t\t" . (isset($Notice) ? $Notice : "") . "\r\n\t\t" . (!isset($DONEMYSQL) ? "<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=restore_database\" $method = \"post\" $name = \"restore_database\">\r\n\t\t" . $hiddenFields . "\r\n\t\t<table>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">Database name:</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"databasename\" $value = \"" . $databasename . "\" $size = \"70\" /> <input $type = \"submit\" $value = \"" . $Language[5] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</form>" : "DONE!") . "\r\n\t\t");
        exit;
    }
} else {
    $BackupFiles = scandir($backuppath);
    $Totalfound = 0;
    if (count($BackupFiles)) {
        $Found = "";
        foreach ($BackupFiles as $File) {
            if ($File != ".." && $File != "." && $File != ".htaccess" && $File != "index.html") {
                $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $File . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . var_238(filesize($backuppath . $File)) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . formatTimestamp(filemtime($backuppath . $File)) . "</td>\r\n\t\t\t\t<td $align = \"center\" class=\"alt1\"><input $type = \"checkbox\" $name = \"backupfiles[]\" $value = \"" . $File . "\" $checkme = \"group\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t";
                $Totalfound++;
            }
        }
    }
    if (!$Totalfound) {
        $Found = "<tr><td $colspan = \"4\" class=\"alt1\">" . $Language[7] . "</td></tr>";
    }
    echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=restore_database\" $method = \"post\" $name = \"restore_database\">\r\n\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\">" . $Language[2] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t<td class=\"alt2\">\r\n\t\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[8] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[4] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t<input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('restore_database', this, 'group');\">\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"4\">\r\n\t\t\t<input $type = \"submit\" $value = \"" . $Language[5] . "\" /> <input $type = \"reset\" $value = \"" . $Language[6] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
}
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
        var_236("../index.php");
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function formatTimestamp($timestamp = "")
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
function function_149($file = "")
{
    return strtolower(substr(strrchr($file, "."), 1));
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
function function_319()
{
    $var_567 = "admin/backup";
    $var_622 = getdate();
    $var_623 = $var_622["mday"];
    if ($var_623 < 10) {
        $var_623 = "0" . $var_623;
    }
    $var_624 = $var_622["mon"];
    if ($var_624 < 10) {
        $var_624 = "0" . $var_624;
    }
    $var_625 = $var_622["year"];
    $var_626 = $var_622["hours"];
    $min = $var_622["minutes"];
    $var_627 = "00";
    system(sprintf("mysqldump --opt -h %s -u %s -p%s %s | gzip > %s/%s/%s__%s-%s-%s-%s:%s.gz", MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, getenv("DOCUMENT_ROOT"), $var_567, MYSQL_DB, $var_625, $var_624, $var_623, $var_626, $min));
}

?>