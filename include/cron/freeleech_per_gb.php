<?php
if (!defined("IN_CRON")) {
    exit;
}
$TorrentGBLimitPerTorrent = 10;
$TorrentGBLimitPerTorrent = $TorrentGBLimitPerTorrent * 1073741824;
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM torrents WHERE size > " . $TorrentGBLimitPerTorrent);
$CQueryCount++;
if (mysqli_num_rows($query)) {
    $Torrents = [];
    while ($_torrent = mysqli_fetch_assoc($query)) {
        $Torrents[] = $_torrent["id"];
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $free = 'yes' WHERE id IN (0," . implode(",", $Torrents) . ")");
    $CQueryCount++;
}

?>