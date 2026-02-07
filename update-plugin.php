<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "update-plugin.php");
require "./global.php";
define("TS_AJAX_VERSION", "1.0 by xam");
$plugin = isset($_POST["plugin"]) ? trim($_POST["plugin"]) : (isset($_GET["plugin"]) ? trim($_GET["plugin"]) : "");
if (!$plugin) {
    show_msg($lang->global["nopermission"] . " - A1");
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" && $plugin != "specialtorrents") {
    show_msg($lang->global["nopermission"] . " - A2");
}
$Row = sql_query("SELECT permission FROM ts_plugins WHERE $active = 1 AND $name = " . sqlesc($plugin));
if (!mysqli_num_rows($Row)) {
    show_msg($lang->global["nopermission"] . " - A3");
}
$Result = mysqli_fetch_assoc($Row);
$_curuser_usergroup = !isset($CURUSER["usergroup"]) || !isset($CURUSER) ? "[0]" : "[" . $CURUSER["usergroup"] . "]";
$show_content = false;
if ($Result["permission"] === "[guest]" && $_curuser_usergroup === "[0]") {
    $show_content = true;
} else {
    if ($Result["permission"] === "[all]" || strstr($Result["permission"], $_curuser_usergroup)) {
        $show_content = true;
    }
}
if (!$show_content) {
    show_msg($lang->global["nopermission"] . " - A4");
}
if (!file_exists(INC_PATH . "/plugins/" . $plugin . ".php")) {
    show_msg($lang->global["nopermission"] . " - A5");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    define("IN_PLUGIN_SYSTEM", true);
}
$lang->load("index");
if ($plugin == "specialtorrents") {
    $limit = 10;
    $Output = "";
    $action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : "");
    if ($action) {
        $Output = "\r\n\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\" $id = \"table-best-seeded\" $style = \"background: #fff; border: 0;\">\r\n\t\t\t<tr>\r\n\t\t\t\t<th $style = \"width: 35px;\"></th>\r\n\t\t\t\t<th $style = \"text-align: left;\">" . $lang->index["name"] . "</th>\r\n\t\t\t\t<th $style = \"width: 35px;text-align: center;\">" . $lang->index["seeders"] . "</th>\r\n\t\t\t\t<th $style = \"width: 35px;text-align: center;\">" . $lang->index["leechers"] . "</th>\r\n\t\t\t</tr>\r\n\t\t";
        switch ($action) {
            case "recent-torrents":
                $_Result = sql_query("SELECT t.*, c.id as catid, c.name as catname, c.image, c.canview FROM torrents t LEFT JOIN categories c ON (t.$category = c.id) WHERE t.$moderate = 0 ORDER BY t.added DESC LIMIT " . $limit);
                break;
            case "best-seeded":
                $_Result = sql_query("SELECT t.*, c.id as catid, c.name as catname, c.image, c.canview FROM torrents t LEFT JOIN categories c ON (t.$category = c.id) WHERE t.$moderate = 0 ORDER BY t.seeders DESC LIMIT " . $limit);
                break;
            case "best-rated":
                $_Result = sql_query("SELECT SUM(r.score) AS TotalScore, COUNT(r.userid) AS TotalScorers, t.*, c.id as catid, c.name as catname, c.image, c.canview FROM ts_ratings r LEFT JOIN torrents t ON (t.$id = CONVERT(SUBSTRING_INDEX(r.ratingid,'_',-1),UNSIGNED INTEGER)) LEFT JOIN categories c ON (t.$category = c.id) WHERE r.ratingid LIKE \"%torrent_%\" AND t.$moderate = 0 GROUP BY r.ratingid ORDER BY TotalScore DESC LIMIT " . $limit);
                break;
            case "most-active":
                $_Result = sql_query("SELECT t.*, t.seeders+t.leechers AS totalPeers, c.id as catid, c.name as catname, c.image, c.canview FROM torrents t LEFT JOIN categories c ON (t.$category = c.id) WHERE t.$moderate = 0 ORDER BY totalPeers DESC LIMIT " . $limit);
                break;
            case "most-downloaded":
                $_Result = sql_query("SELECT t.*, c.id as catid, c.name as catname, c.image, c.canview FROM torrents t LEFT JOIN categories c ON (t.$category = c.id) WHERE t.$moderate = 0 ORDER BY t.times_completed DESC LIMIT " . $limit);
                break;
            case "most-talked-about":
                $_Result = sql_query("SELECT t.*, c.id as catid, c.name as catname, c.image, c.canview FROM torrents t LEFT JOIN categories c ON (t.$category = c.id) WHERE t.$moderate = 0 ORDER BY t.comments DESC LIMIT " . $limit);
                break;
            default:
                if (!isset($_Result)) {
                    show_msg($lang->global["nothingfound"]);
                }
                if (!mysqli_num_rows($_Result)) {
                    show_msg($lang->global["nothingfound"]);
                }
                require_once INC_PATH . "/functions_get_torrent_flags.php";
                $i = 0;
                while ($Row = mysqli_fetch_assoc($_Result)) {
                    if (!($Row["offensive"] == "yes" && TS_Match($CURUSER["options"], "E0") || $Row["canview"] != "[ALL]" && !in_array($CURUSER["usergroup"], explode(",", $Row["canview"])))) {
                        if ($action == "best-rated") {
                            $Image = 0 < $Row["TotalScorers"] ? round($Row["TotalScore"] / $Row["TotalScorers"]) : 0;
                            $IMG = "<img $src = \"" . $pic_base_url . "imdb_rating/" . $Image . "-10.png\" $alt = \"\" $title = \"\" $border = \"0\" class=\"inlineimg\" />";
                        }
                        $trBG = "#fbfbfb";
                        if ($i % 2 == 0) {
                            $trBG = "#f6f6f5";
                            $i = 0;
                        }
                        $Output .= "\r\n\t\t\t<tr $style = \"background: " . $trBG . ";\">\r\n\t\t\t\t<td $style = \"border: 0; width: 35px;text-align: center;\">" . ($Row["t_image"] ? "<img $src = \"" . htmlspecialchars_uni($Row["t_image"]) . "\" $alt = \"\" $title = \"\" $style = \"width: 35px;\" />" : "") . "</td>\r\n\t\t\t\t<td $style = \"border: 0; text-align: left; vertical-align: middle; line-height:2;\">\r\n\t\t\t\t\t<span $style = \"float: right;\">" . GetTorrentTags($Row) . "</span>\r\n\t\t\t\t\t<a $href = \"" . ts_seo($Row["id"], $Row["name"], "s") . "\"><strong>" . htmlspecialchars_uni($Row["name"]) . "</strong></a>\r\n\t\t\t\t\t" . ($action == "best-rated" ? "<br />" . $IMG : "") . "\r\n\t\t\t\t\t" . ($action == "recent-torrents" ? "<br />" . $lang->index["uploaddat"] . " " . my_datee($dateformat . " " . $timeformat, $Row["added"]) : "") . "\r\n\t\t\t\t\t" . ($action == "best-seeded" ? "<br />" . ts_nf($Row["seeders"] + $Row["leechers"]) . " " . $lang->index["peers"] : "") . "\r\n\t\t\t\t\t" . ($action == "most-active" ? "<br />" . ts_nf($Row["seeders"] + $Row["leechers"]) . " " . $lang->index["peers"] : "") . "\r\n\t\t\t\t\t" . ($action == "most-downloaded" ? "<br />" . $lang->index["totaldown"] . ": " . ts_nf($Row["total_downloaded"]) : "") . "\r\n\t\t\t\t\t" . ($action == "most-talked-about" ? "<br />" . $lang->index["total_comments"] . ": " . ts_nf($Row["comments"]) : "") . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td $style = \"border: 0; width: 35px;text-align: center;\">" . ts_nf(0 + $Row["seeders"]) . "</td>\r\n\t\t\t\t<td $style = \"border: 0; width: 35px;text-align: center;\">" . ts_nf(0 + $Row["leechers"]) . "</td>\r\n\t\t\t</tr>";
                        $i++;
                    }
                }
                $Output .= "\r\n\t\t</table>";
        }
    }
    show_msg($Output);
}
require INC_PATH . "/plugins/" . $plugin . ".php";
show_msg(${$plugin});
function show_msg($message = "", $error = false)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; $charset = " . $shoutboxcharset);
    if ($error) {
        exit("<error>" . $message . "</error>");
    }
    exit($message);
}

?>