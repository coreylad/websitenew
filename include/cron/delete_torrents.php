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
$days = 30;
$deadtime = TIMENOW - $days * 24 * 60 * 60;
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, name FROM torrents WHERE mtime < '" . $deadtime . "' AND ts_external != 'yes'");
if (mysqli_num_rows($Query)) {
    while ($DT = mysqli_fetch_assoc($Query)) {
        if (DeepDeleteTorrent($DT["id"])) {
            SaveLog("The following torrent has been deleted automatically due inactivity: " . htmlspecialchars($DT["name"]));
            $CQueryCount = $CQueryCount + 11;
        }
    }
}
echo " ";
function DeepDeleteTorrent($id = 0)
{
    global $TSDatabase;
    $torrent_dir = "torrents";
    $id = intval($id);
    if (!$id) {
        return false;
    }
    $file = TSDIR . "/" . $torrent_dir . "/" . $id . ".torrent";
    if (@file_exists($file)) {
        @unlink($file);
    }
    $image_types = ["gif", "jpg", "png"];
    foreach ($image_types as $image) {
        if (@file_exists(TSDIR . "/" . $torrent_dir . "/images/" . $id . "." . $image)) {
            @unlink(TSDIR . "/" . $torrent_dir . "/images/" . $id . "." . $image);
        }
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t_link FROM torrents WHERE id=" . sqlesc($id));
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $t_link = $Result["t_link"];
        $regex = "#https://www.imdb.com/title/(.*)/#U";
        preg_match($regex, $t_link, $_id_);
        $_id_ = $_id_[1];
        $image_types = ["gif", "jpg", "png"];
        foreach ($image_types as $image) {
            if (@file_exists(TSDIR . "/" . $torrent_dir . "/images/" . $_id_ . "." . $image)) {
                @unlink(TSDIR . "/" . $torrent_dir . "/images/" . $_id_ . "." . $image);
            }
        }
        $image_types = ["gif", "jpg", "png"];
        for ($i = 0; $i <= 10; $i++) {
            foreach ($image_types as $image) {
                if (@file_exists(TSDIR . "/" . $torrent_dir . "/images/" . $_id_ . "_photo" . $i . "." . $image)) {
                    @unlink(TSDIR . "/" . $torrent_dir . "/images/" . $_id_ . "_photo" . $i . "." . $image);
                }
            }
        }
    }
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM peers WHERE torrent = " . @sqlesc($id));
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM xbt_files_users WHERE fid = " . @sqlesc($id));
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM comments WHERE torrent = " . @sqlesc($id));
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM bookmarks WHERE torrentid = " . @sqlesc($id));
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM snatched WHERE torrentid = " . @sqlesc($id));
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM torrents WHERE id=" . @sqlesc($id));
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_torrents_details WHERE tid=" . @sqlesc($id));
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_thanks WHERE tid=" . @sqlesc($id));
    @mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_nfo  WHERE id = " . @sqlesc($id));
    return true;
}

?>