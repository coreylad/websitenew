<?php
define("TSC_VERSION", "1.2 by xam");
if (!isset($GLOBALS["DatabaseConnect"])) {
    require INC_PATH . "/class_ts_database.php";
    $TSDatabase = new TSDatabase();
    $TSDatabase->connect();
}
if (!function_exists("ts_seo")) {
    require INC_PATH . "/functions_tsseo.php";
}
if (!function_exists("sql_query")) {
    function sql_query($Q)
    {
        global $TSDatabase;
        return mysqli_query($GLOBALS["DatabaseConnect"], $Q);
    }
}
function update_cache($name = "indexstats", $ForceUpdate = false)
{
    global $cache;
    global $cachetime;
    global $cachesystem;
    global $BASEURL;
    global $includeexpeers;
    $filename = TSDIR . "/" . $cache . "/" . $name . ".php";
    if (file_exists($filename)) {
        clearstatcache();
        $Update = filemtime($filename) + $cachetime * 60 < TIMENOW ? true : false;
    } else {
        $Update = false;
        $ForceUpdate = false;
    }
    if ($Update || $ForceUpdate) {
        if ($name == "indexstats") {
            require TSDIR . "/include/config_announce.php";
            $torrents = TSRowCount("id", "torrents");
            if ($xbt_active == "yes") {
                $seeders = TSRowCount("1", "xbt_files_users", "`left` = 0 AND $active = 1");
                $leechers = TSRowCount("1", "xbt_files_users", "`left` > 0 AND $active = 1");
            } else {
                $seeders = TSRowCount("id", "peers", "seeder='yes'");
                $leechers = TSRowCount("id", "peers", "seeder='no'");
            }
            if ($includeexpeers == "yes") {
                $ts_e_query = sql_query("SELECT SUM(leechers) as leechers, SUM(seeders) as seeders FROM torrents WHERE $ts_external = 'yes'");
                $ts_e_query_r = mysqli_fetch_row($ts_e_query);
                $leechers += $ts_e_query_r[0];
                $seeders += $ts_e_query_r[1];
            }
            $peers = $seeders + $leechers;
            $ratio = $leechers == 0 ? 0 : round($seeders / $leechers * 100);
            $result = sql_query("SELECT SUM(downloaded) AS totaldl, SUM(uploaded) AS totalul, COUNT(id) AS totaluser FROM users");
            $row = mysqli_fetch_assoc($result);
            $totaldownloaded = $row["totaldl"];
            $totaluploaded = $row["totalul"];
            $registered = $row["totaluser"];
            $latestuser = mysqli_fetch_assoc(sql_query("SELECT id,username FROM users WHERE `status` = 'confirmed' ORDER BY id DESC LIMIT 0,1"));
            $latestuser = "<a $href = \"" . ts_seo($latestuser["id"], $latestuser["username"]) . "\">" . $latestuser["username"] . "</a>";
            $getfstats = sql_query("SELECT SUM(posts) AS totalposts, SUM(threads) AS totalthreads FROM " . TSF_PREFIX . "forums");
            $fstats = mysqli_fetch_assoc($getfstats);
            $totalposts = $fstats["totalposts"];
            $totalthreads = $fstats["totalthreads"];
            $contents = ["torrents" => $torrents, "seeders" => (string) $seeders, "leechers" => (string) $leechers, "peers" => (string) $peers, "totaldownloaded" => mksize($totaldownloaded), "totaluploaded" => mksize($totaluploaded), "registered" => $registered, "latestuser" => $latestuser, "totalposts" => $totalposts, "totalthreads" => $totalthreads];
        }
        $cachecontents = "<?php\n/** TS Generated Cache#1 - Do Not Alter\n * Cache Name: " . $name . "\n * Generated: " . gmdate("r") . "\n*/\n\n";
        $cachecontents .= "\$" . $name . " = " . var_export($contents, true) . ";\n?>";
        file_put_contents($filename, $cachecontents);
    }
}

?>