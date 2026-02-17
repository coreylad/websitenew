<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

$Language = loadStaffLanguage('restore_database');
$backuppath = "./../admin/backup/";
$Message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["backupfiles"]) && 0 < count($_POST["backupfiles"])) {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        echo showAlertErrorModern($Language[21] ?? 'Invalid security token. Please try again.');
        exit;
    }
    ini_set("memory_limit", "256M");
    set_time_limit(0);
    if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
        $mysqldumppath = isset($_POST["mysqldumppath"]) ? trim($_POST["mysqldumppath"]) : "";
        $databasename = isset($_POST["databasename"]) ? trim($_POST["databasename"]) : MYSQL_DB;
        if ($mysqldumppath) {
            try {
                $TSDatabase->exec("CREATE DATABASE IF NOT EXISTS `" . $databasename . "`");
            } catch (PDOException $e) {
            }
            foreach ($_POST["backupfiles"] as $backupfile) {
                if (function_149($backupfile) === "sql") {
                    $DONEMYSQL = true;
                    system($mysqldumppath . "mysql --verbose --$user = " . MYSQL_USER . " --$password = " . MYSQL_PASS . " " . $databasename . " < " . realpath($backuppath) . "\\" . $backupfile, $retval);
                } else {
                    $Notice = showAlertMessage("\r\n\t\t\t\t\tCompressed file(s) does not supported on Windows Server. There are 2 ways to restore those file(s).<br><br>\r\n\t\t\t\t\t1) Use PHPMyAdmin to restore any compressed backup file..<br><br>\r\n\t\t\t\t\t2)<br>\r\n\t\t\t\t\t- Download the compressed backup file.<br>\r\n\t\t\t\t\t- Extract it on your computer (you can use Winrar, Winzip or 7zip).<br>\r\n\t\t\t\t\t- Upload the extracted backup file (filename.sql) into admin/backup folder on your server.<br>\r\n\t\t\t\t\t- Re-run this tool and select the extract file (filename.sql) to restore.\r\n\t\t\t\t\t");
                }
            }
        }
        $hiddenFields = "";
        foreach ($_POST["backupfiles"] as $backupfile) {
            $hiddenFields .= "<input $type = \"hidden\" $name = \"backupfiles[]\" $value = \"" . escape_attr($backupfile) . "\" />";
        }
        echo showAlertErrorModern("\r\n\t\t" . (isset($Notice) ? $Notice : "") . "\r\n\t\t" . (!isset($DONEMYSQL) ? "<form $action = \"" . escape_attr($_SERVER["SCRIPT_NAME"]) . "?do=restore_database\" $method = \"post\" $name = \"restore_database\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t" . $hiddenFields . "\r\n\t\t<table>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"2\" class=\"alt1\">\r\n\t\t\t\t\tWindows System detected! Please enter the path of <b>mysql (mysql.exe)</b> file.. If you have no idea about this, please use PHPMyAdmin to restore your database.\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">Path:</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"mysqldumppath\" $value = \"" . escape_attr($mysqldumppath) . "\" $size = \"70\" /> <small><b>Example</b>: D:\\\\wamp\\\\bin\\mysql\\\\mysql5.1.36\\\\bin\\\\</small>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">Database name:</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"databasename\" $value = \"" . escape_attr($databasename) . "\" $size = \"70\" /> <input $type = \"submit\" $value = \"" . escape_attr($Language[5]) . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</form>" : "DONE!") . "\r\n\t\t");
        exit;
    } else {
        $files = $_POST["backupfiles"];
        $databasename = isset($_POST["databasename"]) ? trim($_POST["databasename"]) : "";
        if ($databasename) {
            try {
                $TSDatabase->exec("CREATE DATABASE IF NOT EXISTS `" . $databasename . "`");
            } catch (PDOException $e) {
            }
            foreach ($files as $file) {
                if (is_file($backuppath . $file) && in_array(function_149($file), ["gz", "sql"])) {
                    if (function_149($file) === "gz") {
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
            $hiddenFields .= "<input $type = \"hidden\" $name = \"backupfiles[]\" $value = \"" . escape_attr($backupfile) . "\" />";
        }
        echo showAlertErrorModern("\r\n\t\t" . (isset($Notice) ? $Notice : "") . "\r\n\t\t" . (!isset($DONEMYSQL) ? "<form $action = \"" . escape_attr($_SERVER["SCRIPT_NAME"]) . "?do=restore_database\" $method = \"post\" $name = \"restore_database\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t" . $hiddenFields . "\r\n\t\t<table>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">Database name:</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"databasename\" $value = \"" . escape_attr($databasename) . "\" $size = \"70\" /> <input $type = \"submit\" $value = \"" . escape_attr($Language[5]) . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</form>" : "DONE!") . "\r\n\t\t");
        exit;
    }
} else {
    $BackupFiles = scandir($backuppath);
    $Totalfound = 0;
    if (count($BackupFiles)) {
        $Found = "";
        foreach ($BackupFiles as $File) {
            if ($File !== ".." && $File !== "." && $File !== ".htaccess" && $File !== "index.html") {
                $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($File) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html(formatBytes(filesize($backuppath . $File))) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html(formatTimestamp(filemtime($backuppath . $File))) . "</td>\r\n\t\t\t\t<td $align = \"center\" class=\"alt1\"><input $type = \"checkbox\" $name = \"backupfiles[]\" $value = \"" . escape_attr($File) . "\" $checkme = \"group\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t";
                $Totalfound++;
            }
        }
    }
    if (!$Totalfound) {
        $Found = "<tr><td $colspan = \"4\" class=\"alt1\">" . escape_html($Language[7]) . "</td></tr>";
    }
    echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked === false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"" . escape_attr($_SERVER["SCRIPT_NAME"]) . "?do=restore_database\" $method = \"post\" $name = \"restore_database\">\r\n" . getFormTokenField() . "\r\n\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\">" . escape_html($Language[2]) . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[3]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[8]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[4]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t<input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('restore_database', this, 'group');\">\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"4\">\r\n\t\t\t<input $type = \"submit\" $value = \"" . escape_attr($Language[5]) . "\" /> <input $type = \"reset\" $value = \"" . escape_attr($Language[6]) . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
}

function formatTimestamp($timestamp = ""): string
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
function function_149(string $file = ""): string
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function formatBytes(int $bytes = 0): string
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
    return number_format($bytes / (1024 * 1024 * 1024 * 1024), 2) . " TB";
}

?>