<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_CRON")) {
    exit;
}
$torrents = [];
if ($xbt_active == "yes") {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `fid`, `left`, COUNT(*) AS c FROM xbt_files_users WHERE `active` = 1 GROUP BY `fid`, `left`");
    $CQueryCount++;
    while ($row = mysqli_fetch_assoc($query)) {
        $key = $row["left"] == "0" ? "seeders" : "leechers";
        $torrents[$row["fid"]][$key] = $row["c"];
    }
} else {
    $res = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
    $CQueryCount++;
    while ($row = mysqli_fetch_assoc($res)) {
        $key = $row["seeder"] == "yes" ? "seeders" : "leechers";
        $torrents[$row["torrent"]][$key] = $row["c"];
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, seeders, leechers FROM torrents WHERE ts_external = 'no'");
$CQueryCount++;
while ($torrent = mysqli_fetch_assoc($query)) {
    $Update = [];
    $work1 = isset($torrents[$torrent["id"]]["seeders"]) ? $torrents[$torrent["id"]]["seeders"] : 0;
    $work2 = isset($torrents[$torrent["id"]]["leechers"]) ? $torrents[$torrent["id"]]["leechers"] : 0;
    if ($work1 != $torrent["seeders"]) {
        $Update[] = "seeders = " . $work1;
    }
    if ($work2 != $torrent["leechers"]) {
        $Update[] = "leechers = " . $work2;
    }
    if (count($Update)) {
        $Update[] = "visible = 'yes'";
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET " . implode(",", $Update) . " WHERE id = " . sqlesc($torrent["id"]));
        $CQueryCount++;
    }
}
unset($torrents);
unset($key);
unset($Update);
unset($work1);
unset($work2);

?>