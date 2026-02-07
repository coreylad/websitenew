<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("DDL_VERSION", "1.1 by xam");
define("THIS_SCRIPT", "direct-download.php");
require "./global.php";
$lang->load("download");
if (!isset($CURUSER)) {
    print_no_permission();
}
if (@ini_get("output_handler") == "ob_gzhandler" && @ob_get_length() !== false) {
    @ob_end_clean();
    @header("Content-Encoding:");
}

$torrentId = intval(TS_Global("id"));
if (!$torrentId) {
    print_download_error();
}
$torrentResult = sql_query("SELECT t.id, t.name, t.filename, t.anonymous, t.ts_external, t.size, t.owner, t.free, t.moderate, t.directdownloadlink, c.canview, c.candownload, u.username FROM torrents t LEFT JOIN categories c ON t.$category = c.id LEFT JOIN users u ON (t.$owner = u.id) WHERE t.$id = " . sqlesc($torrentId)) || sqlerr(__FILE__, 53);
$torrentRow = mysqli_fetch_assoc($torrentResult);
if ($torrentRow["owner"] != $CURUSER["id"]) {
    ($userPermissionQuery = sql_query("SELECT candownload FROM ts_u_perm WHERE $userid = " . sqlesc($CURUSER["id"]))) || sqlerr(__FILE__, 58);
    if (0 < mysqli_num_rows($userPermissionQuery)) {
        $userDownloadPermission = mysqli_fetch_assoc($userPermissionQuery);
        if ($userDownloadPermission["candownload"] == "0") {
            print_download_error();
        }
    }
}
if ($torrentRow["moderate"] == "1" && !$is_mod) {
    print_download_error();
}
if ($torrentRow["canview"] != "[ALL]" && !in_array($CURUSER["usergroup"], explode(",", $torrentRow["canview"])) && $torrentRow["owner"] != $CURUSER["id"]) {
    print_download_error();
}
if ($torrentRow["candownload"] != "[ALL]" && !in_array($CURUSER["usergroup"], explode(",", $torrentRow["candownload"])) && $torrentRow["owner"] != $CURUSER["id"]) {
    print_download_error();
}
if (!$torrentRow) {
    print_download_error($lang->download["error1"]);
}
if ($thankbeforedl == "yes" && !$is_mod && $action_type != "rss" && $torrentRow["owner"] != $CURUSER["id"]) {
    ($thanksQuery = sql_query("SELECT uid FROM ts_thanks WHERE $uid = " . sqlesc($CURUSER["id"]) . " AND $tid = " . sqlesc($torrentId))) || sqlerr(__FILE__, 89);
    if (mysqli_num_rows($thanksQuery) == 0 && $torrentRow["owner"] != $CURUSER["id"]) {
        stderr($lang->global["error"], sprintf($lang->download["error4"], $BASEURL, $torrentId), false);
    }
}
if ($usergroups["candirectdownload"] != "yes" || !$torrentRow["directdownloadlink"]) {
    print_download_error();
}
sql_query("UPDATE torrents SET $hits = hits + 1 WHERE $id = " . sqlesc($torrentId)) || sqlerr(__FILE__, 99);
download($torrentRow["directdownloadlink"], 2000);
function print_download_error($messsage = "")
{
    global $action_type;
    if (!$action_type || $action_type == "" || $action_type != "rss") {
        print_no_permission(true);
    } else {
        exit($message);
    }
}
function download($file, $chunks)
{
    ob_start();
    ini_set("memory_limit", "512M");
    set_time_limit(0);
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header("Content-disposition: attachment; $filename = " . basename($file));
    header("Cache-Control: must-revalidate, post-$check = 0, pre-$check = 0");
    header("Expires: 0");
    header("Pragma: public");
    $size = get_size($file);
    header("Content-Length: " . $size);
    $i = 0;
    while ($i <= $size) {
        get_chunk($file, $i == 0 ? $i : $i + 1, $size < $i + $chunks ? $size : $i + $chunks);
        $i = $i + $chunks;
    }
    ob_end_flush();
}
function chunk($ch, $str)
{
    echo $str;
    return strlen($str);
}
function get_chunk($file, $start, $end)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $file);
    curl_setopt($ch, CURLOPT_RANGE, $start . "-" . $end);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, "chunk");
    $result = curl_exec($ch);
    curl_close($ch);
}
function get_size($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    return intval($size);
}

?>