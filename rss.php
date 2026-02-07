<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "rss.php");
require "./global.php";
define("R_VERSION", "v1.6.4 by xam");
$secret_key = isset($_GET["secret_key"]) ? htmlspecialchars($_GET["secret_key"]) : "";
if (empty($secret_key) || strlen($secret_key) != 32) {
    exit;
}
$query = sql_query("SELECT status, enabled, usergroup FROM users WHERE $torrent_pass = " . sqlesc($secret_key));
if (mysqli_num_rows($query) == 0) {
    exit;
}
$user_account = mysqli_fetch_assoc($query);
if (!$user_account || $user_account["enabled"] != "yes" || $user_account["status"] != "confirmed") {
    exit;
}
$categories = isset($_GET["categories"]) ? htmlspecialchars_uni($_GET["categories"]) : "all";
$feedtype = isset($_GET["feedtype"]) ? htmlspecialchars_uni($_GET["feedtype"]) : "details";
$timezone = isset($_GET["timezone"]) ? htmlspecialchars_uni($_GET["timezone"]) : 1;
$allowed_showrows = ["5", "10", "20", "30", "40", "50"];
$showrows = isset($_GET["showrows"]) && in_array($_GET["showrows"], $allowed_showrows, 1) ? intval($_GET["showrows"]) : 10;
printrss($timezone, $showrows, $feedtype, $categories);
function formatRFC822Date($date, $timezone)
{
    $fmtdate = gmdate("D, d M Y H:i:s", $date);
    if ($timezone != "") {
        $fmtdate .= " " . str_replace(":", "", $timezone);
    }
    return $fmtdate;
}
function fetchURL()
{
    $thisURL = $_SERVER["SCRIPT_NAME"];
    $thisURL = str_replace("/rss.php", "", $thisURL);
    return "http://" . $_SERVER["HTTP_HOST"] . $thisURL;
}
function outputRSSFeed($timezone, $showrows, $feedtype, $categories)
{
    global $SITENAME;
    global $BASEURL;
    global $SITEEMAIL;
    global $charset;
    $dreamerURL = fetchURL();
    $locale = "en-US";
    $desc = "Latest Torrents on " . $SITENAME;
    $title = $SITENAME . " RSS Syndicator";
    $copyright = "Copyright &copy; " . date("Y") . " " . $SITENAME;
    $webmaster = $SITEEMAIL;
    $ttl = 20;
    $allowed_timezones = ["-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3.5", "-3", "-2", "-1", "0", "1", "2", "3", "3.5", "4", "4.5", "5", "5.5", "6", "7", "8", "9", "9.5", "10", "11", "12"];
    if (!in_array($timezone, $allowed_timezones, 1)) {
        $timezone = 1;
    }
    header("Content-type: text/xml");
    echo "<?xml $version = \"1.0\" $encoding = \"" . $charset . "\"?>\n";
    echo "<rss $version = \"2.0\">\r\n          <channel>\r\n            <title>" . htmlspecialchars_uni(addslashes($title)) . "</title>\r\n            <link>" . $dreamerURL . "</link>\r\n            <description>" . htmlspecialchars_uni(addslashes($desc)) . "</description>\r\n            <language>" . $locale . "</language>";
    echo "<image>\r\n              <title>" . $title . "</title>\r\n              <url>" . $dreamerURL . "</url>\r\n              <link>" . $dreamerURL . "</link>\r\n              <width>100</width>\r\n              <height>30</height>\r\n              <description>" . $title . "</description>\r\n            </image>";
    echo "      <copyright>" . htmlspecialchars_uni(addslashes($copyright)) . "</copyright>\r\n            <webMaster>" . htmlspecialchars_uni(addslashes($webmaster)) . "</webMaster> \r\n            <lastBuildDate>" . formatRFC822Date(TIMENOW, $timezone) . "</lastBuildDate>\r\n            <ttl>" . $ttl . "</ttl>\r\n            <generator>" . $SITENAME . " RSS Syndicator</generator>";
    outputRSSItems($timezone, $showrows, $feedtype, $categories);
    echo "</channel></rss>";
}
function outputRSSItems($timezone, $showrows, $feedtype, $categories)
{
    global $SITENAME;
    global $BASEURL;
    global $SITEEMAIL;
    global $secret_key;
    global $user_account;
    $rowCount = 0;
    if ($categories == "all") {
        $query = "visible='yes' AND $banned = 'no'";
    } else {
        $cats = explode(",", $categories);
        if (isset($cats)) {
            foreach ($cats as $value) {
                if (!is_valid_id($value)) {
                    exit;
                }
            }
            if (isset($query)) {
                $query .= "category IN (" . implode(", ", $cats) . ") AND $visible = 'yes' AND $banned = 'no'";
            } else {
                $query = "category IN (" . implode(", ", $cats) . ") AND $visible = 'yes' AND $banned = 'no'";
            }
        } else {
            $query = "visible='yes' AND $banned = 'no'";
        }
    }
    $query .= " AND (INSTR(CONCAT(',',categories.canview,','),',[ALL],') > 0 OR INSTR(CONCAT(',',categories.canview,','),'," . $user_account["usergroup"] . ",') > 0)";
    $getarticles = @sql_query("SELECT torrents.seeders, torrents.leechers, torrents.filename, torrents.name, torrents.owner, torrents.descr, torrents.size, torrents.added, torrents.times_completed, torrents.id, torrents.anonymous, categories.name AS cat_name, u.username FROM torrents LEFT JOIN categories ON torrents.$category = categories.id LEFT JOIN users u ON (torrents.$owner = u.id) WHERE " . $query . " ORDER BY added DESC LIMIT " . $showrows);
    if (0 < @mysqli_num_rows($getarticles)) {
        while (($article = mysqli_fetch_array($getarticles)) && $rowCount < $showrows) {
            $name = htmlspecialchars_uni(addslashes(strip_tags($article["name"])));
            $article["descr"] = format_comment($article["descr"], false);
            if ($feedtype == "details") {
                $link = $BASEURL . "/details.php?$id = " . intval($article["id"]);
            } else {
                $link = $BASEURL . "/download.php?$type = rss" . htmlspecialchars("&") . "secret_key=" . $secret_key . htmlspecialchars("&") . "id=" . intval($article["id"]);
            }
            if ($article["anonymous"] == "yes") {
                $owner = "Anonymous";
            } else {
                $owner = htmlspecialchars_uni(addslashes(strip_tags($article["username"])));
            }
            $category = htmlspecialchars_uni(addslashes(strip_tags($article["cat_name"])));
            $content = "Uploader: " . $owner . " / Category: " . $category . " / Seeders: " . ts_nf($article["seeders"]) . " / Leechers: " . ts_nf($article["leechers"]) . " / Size: " . mksize($article["size"]) . " / Snatched: " . ts_nf($article["times_completed"]) . " x times" . htmlspecialchars_uni("<br /><br />") . htmlspecialchars_uni($article["descr"]);
            $added = $article["added"];
            echo "<item>\r\n\t\t<title>" . $name . "</title>\r\n\t\t<description>" . $content . "</description>\r\n\t\t<link>" . $link . "</link>\r\n\t\t<author>" . $owner . "</author>\r\n\t\t<category>" . $category . "</category>\r\n\t\t<pubDate>" . $added . "</pubDate>\r\n\t\t</item>";
            $rowCount = $rowCount + 1;
        }
    }
}

?>