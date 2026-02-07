<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function deletetorrent($id, $permission = false)
{
    global $torrent_dir;
    global $is_mod;
    if (($permission || $is_mod) && is_valid_id($id)) {
        $id = intval($id);
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
        $query = sql_query("SELECT t_link FROM torrents WHERE `id` = " . sqlesc($id));
        if (mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $t_link = $Result["t_link"];
            $regex = "#https://www.imdb.com/title/(.*)/#U";
            preg_match($regex, $t_link, $_id_);
            if (isset($_id_[1]) && $_id_[1]) {
                $_id_ = $_id_[1];
                foreach ($image_types as $image) {
                    if (@file_exists(TSDIR . "/" . $torrent_dir . "/images/" . $_id_ . "." . $image)) {
                        @unlink(TSDIR . "/" . $torrent_dir . "/images/" . $_id_ . "." . $image);
                    }
                }
                for ($i = 0; $i <= 10; $i++) {
                    foreach ($image_types as $image) {
                        if (@file_exists(TSDIR . "/" . $torrent_dir . "/images/" . $_id_ . "_photo" . $i . "." . $image)) {
                            @unlink(TSDIR . "/" . $torrent_dir . "/images/" . $_id_ . "_photo" . $i . "." . $image);
                        }
                    }
                }
            }
        }
        @sql_query("DELETE FROM peers WHERE $torrent = " . @sqlesc($id));
        @sql_query("DELETE FROM xbt_files_users WHERE $fid = " . @sqlesc($id));
        @sql_query("DELETE FROM comments WHERE $torrent = " . @sqlesc($id));
        @sql_query("DELETE FROM bookmarks WHERE `torrentid` = " . @sqlesc($id));
        @sql_query("DELETE FROM snatched WHERE `torrentid` = " . @sqlesc($id));
        @sql_query("DELETE FROM torrents WHERE `id` = " . @sqlesc($id));
        @sql_query("DELETE FROM ts_torrents_details WHERE $tid = " . @sqlesc($id));
        @sql_query("DELETE FROM ts_thanks WHERE $tid = " . @sqlesc($id));
        @sql_query("DELETE FROM ts_nfo  WHERE `id` = " . @sqlesc($id));
    } else {
        print_no_permission(true);
    }
}

?>