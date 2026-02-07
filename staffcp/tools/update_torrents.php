<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

@set_time_limit(0);
var_235();
$Language = file("languages/" . getStaffLanguage() . "/update_torrents.lang");
$Message = "";
@ini_set("upload_max_filesize", 10485760);
@ini_set("memory_limit", "20000M");
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $perpage = intval($_POST["perpage"]);
    $wait = intval($_POST["wait"]);
} else {
    if (isset($_GET["page"])) {
        $perpage = intval($_GET["perpage"]);
        $wait = intval($_GET["wait"]);
    } else {
        echo "\r\n\t<form $method = \"post\" $action = \"index.php?do=update_torrents\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">" . $Language[8] . "</td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"perpage\" $value = \"20\" $size = \"10\" /> " . $Language[12] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . $Language[9] . "</td>\r\n\t\t\t<td class=\"alt2\"><input $type = \"text\" $name = \"wait\" $value = \"30\" $size = \"10\" /> " . $Language[13] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[10] . "\" /> <input $type = \"reset\" $value = \"" . $Language[11] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
    }
}
if (isset($perpage) && isset($wait)) {
    if ($perpage) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM torrents");
        $results = mysqli_num_rows($query);
        $totalpages = @ceil($results / $perpage);
        $pagenumber = isset($_GET["page"]) && 0 < $_GET["page"] ? intval($_GET["page"]) : 1;
        if ($totalpages == 0) {
            $totalpages = 1;
        }
        if ($pagenumber < 1) {
            $pagenumber = 1;
        } else {
            if ($totalpages < $pagenumber) {
                $pagenumber = $totalpages;
            }
        }
        $limitlower = ($pagenumber - 1) * $perpage;
        $limitupper = $pagenumber * $perpage;
        if ($results < $limitupper) {
            $limitupper = $results;
            if ($results < $limitlower) {
                $limitlower = $results - $perpage - 1;
            }
        }
        if ($limitlower < 0) {
            $limitlower = 0;
        }
        $nextpage = $pagenumber + 1;
    }
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'ANNOUNCE'");
    $Result = mysqli_fetch_assoc($Q);
    $ANNOUNCE = unserialize($Result["content"]);
    $torrents = [];
    if ($ANNOUNCE["xbt_active"] == "yes") {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid, `left`, COUNT(*) AS c FROM xbt_files_users WHERE $active = 1 GROUP BY fid, `left`");
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row["left"] == "0") {
                $key = "seeders";
            } else {
                $key = "leechers";
            }
            $torrents[$row["fid"]][$key] = $row["c"];
        }
    } else {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row["seeder"] == "yes") {
                $key = "seeders";
            } else {
                $key = "leechers";
            }
            $torrents[$row["torrent"]][$key] = $row["c"];
        }
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT torrentid, COUNT(*) as s FROM snatched WHERE $finished = 'yes' GROUP BY torrentid");
        while ($row = mysqli_fetch_assoc($query)) {
            $torrents[$row["torrentid"]]["times_completed"] = $row["s"];
        }
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT torrent, COUNT(*) AS c FROM comments GROUP BY torrent");
    while ($row = mysqli_fetch_assoc($query)) {
        $torrents[$row["torrent"]]["comments"] = $row["c"];
    }
    echo "\r\n\t<div $id = \"sending\" $name = \"sending\">\r\n\t\t\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\">" . $Language[2] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, id, ts_external FROM torrents ORDER BY added" . ($perpage ? " DESC LIMIT " . $limitlower . ", " . $limitupper : ""));
    if (mysqli_num_rows($Query)) {
        while ($Torrent = mysqli_fetch_assoc($Query)) {
            echo "\r\n\t\t\t\t\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t" . str_replace("{1}", htmlspecialchars($Torrent["name"]), $Language[3]) . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t";
            $UPDATE = mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $comments = '" . (isset($torrents[$Torrent["id"]]["comments"]) ? $torrents[$Torrent["id"]]["comments"] : 0) . "'" . ($Torrent["ts_external"] == "no" ? ", $seeders = '" . (isset($torrents[$Torrent["id"]]["seeders"]) ? $torrents[$Torrent["id"]]["seeders"] : 0) . "', $leechers = '" . (isset($torrents[$Torrent["id"]]["leechers"]) ? $torrents[$Torrent["id"]]["leechers"] : 0) . "'" . ($ANNOUNCE["xbt_active"] == "yes" ? "" : ", $times_completed = '" . (isset($torrents[$Torrent["id"]]["times_completed"]) ? $torrents[$Torrent["id"]]["times_completed"] : 0) . "'") . "" : "") . " WHERE $id = '" . $Torrent["id"] . "'");
            if ($UPDATE && mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                echo "<td><font $color = \"green\">" . $Language[6] . "</font> " . $Language[14] . " " . number_format(isset($torrents[$Torrent["id"]]["seeders"]) ? $torrents[$Torrent["id"]]["seeders"] : 0) . " / " . $Language[15] . " " . number_format(isset($torrents[$Torrent["id"]]["leechers"]) ? $torrents[$Torrent["id"]]["leechers"] : 0) . " / " . $Language[16] . " " . number_format(isset($torrents[$Torrent["id"]]["comments"]) ? $torrents[$Torrent["id"]]["comments"] : 0) . " " . ($ANNOUNCE["xbt_active"] == "yes" ? "" : "/ " . $Language[17] . " " . number_format(isset($torrents[$Torrent["id"]]["times_completed"]) ? $torrents[$Torrent["id"]]["times_completed"] : 0)) . "</td>";
            } else {
                echo "<td><font $color = \"green\">" . $Language[7] . "</font></td>";
            }
            echo "\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t";
        }
    }
    echo "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t" . (!$perpage || $totalpages < $nextpage ? "\r\n\t " . $Language[4] : "\r\n\t " . $Language[5] . " (" . $wait . ")\r\n\t<script $type = \"text/JavaScript\">\r\n\t\t<!--\r\n\t\t\tsetTimeout(\"location.$href = 'index.php?do=update_torrents&$page = " . $nextpage . "&$perpage = " . $perpage . "&$wait = " . $wait . "';\", " . $wait . "000);\r\n\t\t-->\r\n\t</script>\r\n\t") . "\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t</div>\r\n\t";
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

?>