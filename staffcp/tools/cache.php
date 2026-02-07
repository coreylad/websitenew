<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/cache.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if (isset($_GET["cache"])) {
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
    $Result = mysqli_fetch_assoc($Q);
    $MAIN = unserialize($Result["content"]);
    $cache_arrays = ["categories", "ipban", "plugin", "usergroup", "indexstats", "smilies"];
    echo function_322("Categories");
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE $type = 'c' ORDER by name, id");
    while ($_c = mysqli_fetch_assoc($query)) {
        $_ccache[] = $_c;
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE $type = 's' ORDER by name, id");
    while ($_c = mysqli_fetch_assoc($query)) {
        $_ccache2[] = $_c;
    }
    $content = var_export($_ccache, true);
    $content2 = var_export($_ccache2, true);
    $_filename = "../" . $MAIN["cache"] . "/categories.php";
    $_cachecontents = "<?php\n/** TS Generated Cache#7 - Do Not Alter\n * Cache Name: Categories\n * Generated: " . gmdate("r") . "\n*/\n\n";
    $_cachecontents .= "\$_categoriesC = " . $content . ";\n\n";
    $_cachecontents .= "\$_categoriesS = " . $content2 . ";\n?>";
    if (file_put_contents($_filename, $_cachecontents)) {
        echo function_323();
    } else {
        echo var_633();
    }
    echo function_322("Smilies");
    $SimilieArray = "\$smilies = array (";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT stext, spath FROM ts_smilies ORDER BY sorder, stitle");
    while ($Sml = mysqli_fetch_assoc($query)) {
        $SimilieArray2[] = "'" . $Sml["stext"] . "' => '" . $Sml["spath"] . "'";
    }
    $SimilieArray = $SimilieArray . implode(", ", $SimilieArray2) . ");";
    $_filename = "../" . $MAIN["cache"] . "/smilies.php";
    $_cachecontents = "<?php\n/** TS Generated Cache#14 - Do Not Alter\n * Cache Name: Smilies\n * Generated: " . gmdate("r") . "\n*/\n";
    $_cachecontents .= $SimilieArray . "\n?>";
    if (file_put_contents($_filename, $_cachecontents)) {
        echo function_323();
    } else {
        echo var_633();
    }
    echo function_322("Index Stats");
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'ANNOUNCE'");
    $Result = mysqli_fetch_assoc($Q);
    $ANNOUNCE = unserialize($Result["content"]);
    $torrents = function_324("id", "torrents");
    if ($ANNOUNCE["xbt_active"] == "yes") {
        $seeders = function_324("1", "xbt_files_users", "`left` = 0 AND $active = 1");
        $leechers = function_324("1", "xbt_files_users", "`left` > 0 AND $active = 1");
    } else {
        $seeders = function_324("id", "peers", "seeder='yes'");
        $leechers = function_324("id", "peers", "seeder='no'");
    }
    if (isset($MAIN["includeexpeers"]) && $MAIN["includeexpeers"] == "yes") {
        $ts_e_query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(leechers) as leechers, SUM(seeders) as seeders FROM torrents WHERE $ts_external = 'yes'");
        $ts_e_query_r = mysqli_fetch_row($ts_e_query);
        $leechers += $ts_e_query_r[0];
        $seeders += $ts_e_query_r[1];
    }
    $peers = $seeders + $leechers;
    $ratio = $leechers == 0 ? 0 : round($seeders / $leechers * 100);
    $result = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(downloaded) AS totaldl, SUM(uploaded) AS totalul, COUNT(id) AS totaluser FROM users");
    $row = mysqli_fetch_assoc($result);
    $totaldownloaded = $row["totaldl"];
    $totaluploaded = $row["totalul"];
    $registered = $row["totaluser"];
    $latestuser = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id,username FROM users WHERE $status = 'confirmed' ORDER BY id DESC LIMIT 1"));
    $latestuser = "<a $href = \"" . $MAIN["BASEURL"] . "/userdetails.php?$id = " . $latestuser["id"] . "\">" . $latestuser["username"] . "</a>";
    $getfstats = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(posts) AS totalposts, SUM(threads) AS totalthreads FROM tsf_forums");
    $fstats = mysqli_fetch_assoc($getfstats);
    $totalposts = $fstats["totalposts"];
    $totalthreads = $fstats["totalthreads"];
    $contents = ["torrents" => $torrents, "seeders" => $seeders, "leechers" => $leechers, "peers" => (string) $peers, "totaldownloaded" => var_238($totaldownloaded), "totaluploaded" => var_238($totaluploaded), "registered" => $registered, "latestuser" => $latestuser, "totalposts" => $totalposts, "totalthreads" => $totalthreads];
    $_filename = "../" . $MAIN["cache"] . "/indexstats.php";
    $_cachecontents = "<?php\n/** TS Generated Cache#1 - Do Not Alter\n * Cache Name: Index Stats\n * Generated: " . gmdate("r") . "\n*/\n\n";
    $_cachecontents .= "\$indexstats = " . @var_export($contents, true) . ";\n?>";
    if (file_put_contents($_filename, $_cachecontents)) {
        echo function_323();
    } else {
        echo var_633();
    }
    $Message = str_replace(["{1}", "{2}"], ["Categories, Smilies, Index Stats ", $_SESSION["ADMIN_USERNAME"]], $Language[3]);
    logStaffAction($Message);
    echo showAlertError($Message);
    exit;
}
echo "<script $type = \"text/javascript\">\r\n\t\$(document).ready(function()\r\n\t{\r\n\t\tsetTimeout(function()\r\n\t\t{\r\n\t\t\t\$.get('index.php?do=cache&$cache = true', function(response)\r\n\t\t\t{\r\n\t\t\t\t\$('.alt1').html(response);\r\n\t\t\t});\r\n\t\t}, 1000);\r\n\t});\r\n</script>\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">";
echo $Language[2];
echo "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<img $src = \"./images/fb_ajax-loader.gif\" $style = \"vertical-align: middle;\" $alt = \"\" $title = \"\" /> Updating...\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
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
function redirectTo($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; $url = " . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
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
function function_324($C, $T, $E = "")
{
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT COUNT(" . $C . ") FROM " . $T . ($E ? " WHERE " . $E : ""));
    $var_634 = mysqli_fetch_row($Q);
    return $var_634[0];
}
function function_149($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function function_322($cachename)
{
    global $Language;
    return "<br />&nbsp;&nbsp;&nbsp;" . $Language[8] . " (" . $cachename . ") ";
}
function function_323()
{
    return "&nbsp;&nbsp;&nbsp;<img $src = \"images/accept.png\" $border = \"0\" $alt = \"\" $title = \"\" $style = \"vertical-align: middle;\" /><br /><br />";
}
function function_325()
{
    return "&nbsp;&nbsp;&nbsp;<img $src = \"images/alert.png\" $border = \"0\" $alt = \"\" $title = \"\" $style = \"vertical-align: middle;\" /><br /><br />";
}

?>