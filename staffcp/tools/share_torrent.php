<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/share_torrent.lang");
$Message = "";
$Found = "";
$name = "";
$tracker = "http://";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $name = trim($_POST["name"]);
    $tracker = trim($_POST["tracker"]);
    if ($name && $tracker && $tracker != "http://") {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.id, t.name, t.added, t.size, t.leechers, t.seeders, u.username, g.namestyle, c.name as catname FROM torrents t LEFT JOIN users u ON (t.$owner = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) LEFT JOIN categories c ON (t.$category = c.id) WHERE MATCH (t.name) AGAINST ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "*' IN BOOLEAN MODE)");
        if (0 < mysqli_num_rows($query)) {
            $Found = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\t\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"8\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[16] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[15] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[5] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[9] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[10] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[11] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[13] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[14] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
            while ($Torrent = mysqli_fetch_assoc($query)) {
                $Found .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t\t<a $href = \"../download.php?$id = " . $Torrent["id"] . "&amp;$fromadminpanel = true\"><img $src = \"images/download.png\" $border = \"0\" $alt = \"" . trim($Language[17]) . "\" $title = \"" . trim($Language[17]) . "\" /></a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Torrent["name"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Torrent["catname"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . var_238($Torrent["size"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . formatTimestamp($Torrent["added"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $Torrent["username"] . "\">" . applyUsernameStyle($Torrent["username"], $Torrent["namestyle"]) . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . number_format($Torrent["seeders"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . number_format($Torrent["leechers"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
            $Found .= "\r\n\t\t\t</table>\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\t\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[6] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t<iframe $src = \"" . htmlspecialchars($tracker) . "\" $width = \"100%\" $height = \"300\"></iframe>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>";
        } else {
            $Message = showAlertError($Language[4]);
        }
    } else {
        $Message = showAlertError($Language[3]);
    }
}
$SelectBoxTorrents = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM torrents ORDER by added DESC, name ASC LIMIT 50");
if (0 < mysqli_num_rows($query)) {
    $SelectBoxTorrents = "\r\n\t<select $name = \"torrentname\" $onchange = \"TSUpdateFieldTorrent(this);\">\r\n\t<option $value = \"\"" . ($name == "" ? " $selected = \"selected\"" : "") . ">" . $Language[18] . "</option>";
    while ($Torrents = mysqli_fetch_assoc($query)) {
        $Torrents["name"] = htmlspecialchars($Torrents["name"]);
        $SelectBoxTorrents .= "\r\n\t\t<option $value = \"" . $Torrents["name"] . "\"" . ($name == $Torrents["name"] ? " $selected = \"selected\"" : "") . ">" . $Torrents["name"] . "</option>";
    }
    $SelectBoxTorrents .= "\r\n\t</select>";
}
$AutoSelectBox = "\r\n<select $name = \"remotetracker\" $onchange = \"TSUpdateField(this);\">\r\n\t<option $value = \"http://\"" . ($tracker == "http" ? " $selected = \"selected\"" : "") . ">" . $Language[18] . "</option>\r\n\t<option $value = \"http://www.mininova.org/upload\"" . ($tracker == "http://www.mininova.org/upload" ? " $selected = \"selected\"" : "") . ">MiniNova</option>\r\n\t<option $value = \"http://www.demonoid.com/torrent_upload.php5\"" . ($tracker == "http://www.demonoid.com/torrent_upload.php5" ? " $selected = \"selected\"" : "") . ">Demonoid</option>\r\n\t<option $value = \"http://www.thepiratebay.org/upload\"" . ($tracker == "http://www.thepiratebay.org/upload" ? " $selected = \"selected\"" : "") . ">ThePirateBay</option>\r\n\t<option $value = \"http://www.meganova.org/upload.html\"" . ($tracker == "http://www.meganova.org/upload.html" ? " $selected = \"selected\"" : "") . ">MegaNova</option>\r\n</select>\r\n";
echo "<script $type = \"text/javascript\">\r\n\tfunction TSUpdateField(Where)\r\n\t{\r\n\t\tTSGetID(\"tracker\").$value = Where.value;\r\n\t}\r\n\tfunction TSUpdateFieldTorrent(Where)\r\n\t{\r\n\t\tTSGetID(\"name\").$value = Where.value;\r\n\t}\r\n</script>\r\n<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=share_torrent\" $method = \"post\">\r\n";
echo $Message . $Found;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\t\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\"  $align = \"right\">";
echo $Language[5];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"name\" $id = \"name\" $value = \"";
echo htmlspecialchars($name);
echo "\" $size = \"45\" $dir = \"ltr\" $tabindex = \"1\" /> ";
echo $SelectBoxTorrents;
echo "</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[6];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $id = \"tracker\" $name = \"tracker\" $value = \"";
echo htmlspecialchars($tracker);
echo "\" $size = \"45\" $dir = \"ltr\" $tabindex = \"1\" /> ";
echo $AutoSelectBox;
echo "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[7];
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[8];
echo "\" $accesskey = \"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}

?>