<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
define("FFL_VERSION", "2.0 by xam");
@ini_set("upload_max_filesize", 1000 < $max_torrent_size ? $max_torrent_size : 10485760);
@ini_set("memory_limit", "20000M");
$s = "\r\n<table width=\"100%\" align=\"center\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\">\r\n\t<tr>\r\n\t\t<td class=\"subheader\" width=\"5%\" align=\"center\"></td>\r\n\t\t<td class=\"subheader\" width=\"85%\">" . $lang->details["path"] . "</td>\r\n\t\t<td class=\"subheader\" width=\"10%\">" . $lang->details["size"] . "</td>\r\n\t</tr>";
if (100 < TOTAL_FILES) {
    $s .= "<tr><td colspan=\"3\">" . sprintf($lang->details["bigfile"], ts_nf(TOTAL_FILES)) . "</td></tr>";
} else {
    if (is_file(TSDIR . "/" . $torrent_dir . "/" . $id . ".torrent") && ($Data = file_get_contents(TSDIR . "/" . $torrent_dir . "/" . $id . ".torrent"))) {
        require_once INC_PATH . "/class_torrent.php";
        $Torrent = new Torrent();
        if ($Torrent->load($Data)) {
            require_once INC_PATH . "/functions_get_file_icon.php";
            $FileCount = 0;
            $TorrentFiles = $Torrent->getFiles();
            foreach ($TorrentFiles as $TorrentFile) {
                if (100 < $FileCount) {
                } else {
                    $FileCount++;
                    $s .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td align=\"center\">" . get_file_icon($TorrentFile->name, $BASEURL . "/tsf_forums/images/attach/") . "</td>\r\n\t\t\t\t\t<td>" . htmlspecialchars_uni($TorrentFile->name) . "</td>\r\n\t\t\t\t\t<td>" . mksize($TorrentFile->length) . "</td>\r\n\t\t\t\t</tr>";
                }
            }
        }
    }
}
$s .= "</table>";

?>