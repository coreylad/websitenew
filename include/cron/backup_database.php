<?php
if (!defined("IN_CRON")) {
    exit;
}
if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
    TS_BACKUP_DATABASE_WIN($CQueryCount);
} else {
    require_once INC_PATH . "/config_database.php";
    TS_BACKUP_DATABASE_NIX();
    $CQueryCount++;
}
function TS_BACKUP_DATABASE_NIX()
{
    $BackupDirectory = "admin/backup";
    $today = getdate();
    $day = $today["mday"];
    if ($day < 10) {
        $day = "0" . $day;
    }
    $month = $today["mon"];
    if ($month < 10) {
        $month = "0" . $month;
    }
    $year = $today["year"];
    $hour = $today["hours"];
    $min = $today["minutes"];
    $sec = "00";
    system(sprintf("mysqldump --opt -h %s -u %s -p%s %s | gzip > %s/%s/%s__%s-%s-%s-%s:%s.gz", MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, getenv("DOCUMENT_ROOT"), $BackupDirectory, MYSQL_DB, $year, $month, $day, $hour, $min));
}
function TS_BACKUP_DATABASE_WIN(&$CQueryCount)
{
    global $TSDatabase;
    $BackupDirectory = THIS_PATH . "/admin/backup";
    if (is_writable($BackupDirectory)) {
        $file = $BackupDirectory . "/backup_" . substr(md5(uniqid(rand(), true) . time()), 0, 10);
        if (function_exists("gzopen")) {
            $fp = gzopen($file . ".sql.gz", "w9");
        } else {
            $fp = fopen($file . ".sql", "w");
        }
        $time = date("dS F Y \\a\\t H:i", time());
        $header = "-- -------------------------------------\n-- TS SE Database Backup\n-- Generated: " . $time . "\n-- -------------------------------------\n\n";
        $contents = $header;
        $tables = [];
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW TABLES FROM `" . MYSQL_DB . "`");
        $CQueryCount++;
        while (list($table) = mysqli_fetch_array($query)) {
            $tables[] = $table;
        }
        foreach ($tables as $table) {
            $field_list = [];
            $fields_array = show_fields_from($table);
            $CQueryCount++;
            foreach ($fields_array as $field) {
                $field_list[] = $field["Field"];
            }
            $fields = implode(",", $field_list);
            $structure = show_create_table($table) . ";\n";
            $CQueryCount++;
            $contents .= $structure;
            clear_overflow($fp, $contents);
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . $table);
            $CQueryCount++;
            while ($row = mysqli_fetch_array($query)) {
                $insert = "INSERT INTO " . $table . " (" . $fields . ") VALUES (";
                $comma = "";
                foreach ($field_list as $field) {
                    if (!isset($row[$field]) || trim($row[$field]) == "") {
                        $insert .= $comma . "''";
                    } else {
                        $insert .= $comma . "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $row[$field]) . "'";
                    }
                    $comma = ",";
                }
                $insert .= ");\n";
                $contents .= $insert;
                clear_overflow($fp, $contents);
            }
        }
    }
}
function show_fields_from($table)
{
    global $TSDatabase;
    $field_info = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW FIELDS FROM " . $table);
    while ($field = mysqli_fetch_array($query)) {
        $field_info[] = $field;
    }
    return $field_info;
}
function show_create_table($table)
{
    global $TSDatabase;
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SHOW CREATE TABLE " . $table);
    $structure = mysqli_fetch_array($query);
    return $structure["Create Table"];
}
function clear_overflow($fp, &$contents)
{
    if (function_exists("gzopen")) {
        gzwrite($fp, $contents);
    } else {
        fwrite($fp, $contents);
    }
    $contents = "";
}

?>