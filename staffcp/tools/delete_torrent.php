<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/delete_torrent.lang");
$Message = "";
$tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : (isset($_POST["tid"]) ? intval($_POST["tid"]) : "");
$reason = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $tid) {
    $reason = $_POST["reason"] ? trim($_POST["reason"]) : "";
    if ($reason) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, owner FROM torrents WHERE `id` = '" . $tid . "'");
        if (0 < mysqli_num_rows($query)) {
            $Torrent = mysqli_fetch_assoc($query);
            $SysMsg = str_replace(["{1}", "{2}"], [$Torrent["name"], $_SESSION["ADMIN_USERNAME"]], $Language[7]);
            function_151($tid);
            logStaffAction($SysMsg);
            sendPrivateMessage($Torrent["owner"], $SysMsg . "\r\n\t\t\t" . trim($Language[8]) . ": " . $reason, $Language[2]);
            $Message = showAlertError($SysMsg);
            $tid = "";
            $reason = "";
        } else {
            $Message = showAlertError($Language[6]);
        }
    } else {
        $Message = showAlertError($Language[9]);
    }
}
echo "\t\t\t\t\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=delete_torrent\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $align = \"right\">" . $Language[3] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"tid\" $value = \"" . intval($tid) . "\" $size = \"10\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $align = \"right\">" . $Language[8] . "</td>\r\n\t\t<td class=\"alt2\"><textarea $name = \"reason\" $rows = \"2\" $cols = \"66\">" . htmlspecialchars($reason) . "</textarea></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[4] . "\" /> <input $type = \"reset\" $value = \"" . $Language[5] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function function_151($id)
{
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $configRow = mysqli_fetch_assoc($configQuery);
    $configData = unserialize($configRow["content"]);
    $var_427 = "../" . $configData["torrent_dir"];
    $id = intval($id);
    if (!$id) {
        return NULL;
    }
    $file = $var_427 . "/" . $id . ".torrent";
    if (@file_exists($file)) {
        @unlink($file);
    }
    $var_428 = ["gif", "jpg", "png"];
    foreach ($var_428 as $var_361) {
        if (@file_exists($var_427 . "/images/" . $id . "." . $var_361)) {
            @unlink($var_427 . "/images/" . $id . "." . $var_361);
        }
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t_link FROM torrents WHERE `id` = " . $id);
    if (mysqli_num_rows($query)) {
        $var_20 = mysqli_fetch_assoc($query);
        $var_429 = $var_20["t_link"];
        $var_97 = "#https://www.imdb.com/title/(.*)/#U";
        preg_match($var_97, $var_429, $var_430);
        $var_430 = $var_430[1];
        foreach ($var_428 as $var_361) {
            if (@file_exists($var_427 . "/images/" . $var_430 . "." . $var_361)) {
                @unlink($var_427 . "/images/" . $var_430 . "." . $var_361);
            }
        }
        for ($i = 0; $i <= 10; $i++) {
            foreach ($var_428 as $var_361) {
                if (@file_exists($var_427 . "/images/" . $var_430 . "_photo" . $i . "." . $var_361)) {
                    @unlink($var_427 . "/images/" . $var_430 . "_photo" . $i . "." . $var_361);
                }
            }
        }
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM peers WHERE $torrent = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM xbt_files_users WHERE $fid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM comments WHERE $torrent = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM bookmarks WHERE `torrentid` = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM snatched WHERE `torrentid` = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM torrents WHERE `id` = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_torrents_details WHERE $tid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_thanks WHERE $tid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_nfo  WHERE `id` = " . $id);
}
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages \r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES \r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $receiver . "'");
    }
}

?>