<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.1.1 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
include_once INC_PATH . "/ts_cache.php";
define("CACHE_INCLUDED", true);
update_cache("indexstats");
include_once $cache . "/indexstats.php";
$stats = "\r\n<img src=\"" . $pic_base_url . "navigation/flag_blue.png\" alt=\"\" border=\"0\" class=\"inlineimg\" /> <b>" . $lang->index["members"] . ":</b> " . ts_nf($indexstats["registered"]) . "<br />\r\n<img src=\"" . $pic_base_url . "navigation/flag_green.png\" alt=\"\" border=\"0\" class=\"inlineimg\" /> <b>" . $lang->index["torrents"] . ":</b> " . ts_nf($indexstats["torrents"]) . "<br />\r\n<img src=\"" . $pic_base_url . "navigation/flag_orange.png\" alt=\"\" border=\"0\" class=\"inlineimg\" /> <b>" . $lang->index["seeders"] . ":</b> " . ts_nf($indexstats["seeders"]) . "<br />\r\n<img src=\"" . $pic_base_url . "navigation/flag_pink.png\" alt=\"\" border=\"0\" class=\"inlineimg\" /> <b>" . $lang->index["leechers"] . ":</b> " . ts_nf($indexstats["leechers"]) . "<br />\r\n<img src=\"" . $pic_base_url . "navigation/flag_purple.png\" alt=\"\" border=\"0\" class=\"inlineimg\" /> <b>" . $lang->index["peers"] . ":</b> " . ts_nf($indexstats["peers"]) . "<br />\r\n<img src=\"" . $pic_base_url . "navigation/flag_red.png\" alt=\"\" border=\"0\" class=\"inlineimg\" /> <b>" . $lang->index["threads"] . ":</b> " . ts_nf($indexstats["totalthreads"]) . "<br />\r\n<img src=\"" . $pic_base_url . "navigation/flag_yellow.png\" alt=\"\" border=\"0\" class=\"inlineimg\" /> <b>" . $lang->index["posts"] . ":</b> " . ts_nf($indexstats["totalposts"]) . "<br /><br />\r\n" . sprintf($lang->index["newestmember"], $indexstats["latestuser"]) . "\r\n";

?>