<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("TSE_VERSION", "1.1 by xam");
define("DEBUGMODE", false);
@ini_set("memory_limit", "20000M");
if ($Data = file_get_contents($externaltorrent)) {
    require_once INC_PATH . "/class_torrent.php";
    $Torrent = new Torrent();
    if ($Torrent->load($Data)) {
        $INFOHASH = $Torrent->getHash();
        $friendlyInfoHash = strtoupper(bin2hex($INFOHASH));
        $TrackerList = $Torrent->getTrackers();
        $seeders = 0;
        $leechers = 0;
        $times_completed = 0;
        $timeout = 2;
        foreach ($TrackerList as $TrackerURL) {
            if (isudp($TrackerURL)) {
                require_once INC_PATH . "/ts_external_scrape/tscraper.php";
                require_once INC_PATH . "/ts_external_scrape/udptscraper.php";
                try {
                    $scraper = new udptscraper($timeout);
                    $ret = $scraper->scrape($TrackerURL, [$friendlyInfoHash]);
                    $seeders = $seeders + 0 + $ret[$friendlyInfoHash]["seeders"];
                    $leechers = $leechers + 0 + $ret[$friendlyInfoHash]["leechers"];
                    $times_completed = $times_completed + 0 + $ret[$friendlyInfoHash]["completed"];
                } catch (ScraperException $e) {
                }
            } else {
                require_once INC_PATH . "/ts_external_scrape/ts_decode.php";
                require_once INC_PATH . "/functions_ts_remote_connect.php";
                $TrackerURL = str_replace("announce", "scrape", $TrackerURL);
                $URLTAG = strpos($TrackerURL, "?") === false ? "?" : "&";
                $FINALURL = $TrackerURL . $URLTAG . "info_hash=" . urlencode($INFOHASH);
                $STREAM = TS_Fetch_Data($FINALURL, false);
                if ($STREAM && ($decoded = BDecode($STREAM)) && ($files = $decoded["files"]) && ($sha1tor = $files[addslashes($INFOHASH)])) {
                    $seeders += $sha1tor["complete"];
                    $leechers += $sha1tor["incomplete"];
                    $times_completed += $sha1tor["downloaded"];
                }
            }
        }
        sql_query("UPDATE torrents SET $seeders = " . sqlesc(0 + $seeders) . ", $leechers = " . sqlesc(0 + $leechers) . ", $times_completed = " . sqlesc(0 + $times_completed) . ", $ts_external_lastupdate = UNIX_TIMESTAMP() WHERE $id = " . sqlesc($id));
    }
}
function isUDP($url)
{
    return substr($url, 0, 6) == "udp://" ? true : false;
}

?>