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
$TorrentGBLimitPerTorrent = 10;
$TorrentGBLimitPerTorrent = $TorrentGBLimitPerTorrent * 1073741824;
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM torrents WHERE size > " . $TorrentGBLimitPerTorrent);
$CQueryCount++;
if (mysqli_num_rows($Query)) {
    $Torrents = [];
    while ($_torrent = mysqli_fetch_assoc($Query)) {
        $Torrents[] = $_torrent["id"];
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET free = 'yes' WHERE id IN (0," . implode(",", $Torrents) . ")");
    $CQueryCount++;
}

?>