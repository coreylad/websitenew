<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_ST_VERSION")) {
    define("TS_ST_VERSION", "1.0 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$specialtorrents = "\r\n<!-- begin specialtorrents -->\r\n<div id=\"specialtorrentstabs\">\r\n\t<ul>\r\n\t\t<li><a href=\"" . $BASEURL . "/update-plugin.php?plugin=specialtorrents&action=recent-torrents\" >" . $lang->index["recent_torrents"] . "</a></li>\r\n\t\t<li><a href=\"" . $BASEURL . "/update-plugin.php?plugin=specialtorrents&action=best-seeded\">" . $lang->index["best_seeded"] . "</a></li>\r\n\t\t<li><a href=\"" . $BASEURL . "/update-plugin.php?plugin=specialtorrents&action=best-rated\">" . $lang->index["best_rated"] . "</a></li>\r\n\t\t<li><a href=\"" . $BASEURL . "/update-plugin.php?plugin=specialtorrents&action=most-active\">" . $lang->index["most_active"] . "</a></li>\r\n\t\t<li><a href=\"" . $BASEURL . "/update-plugin.php?plugin=specialtorrents&action=most-downloaded\">" . $lang->index["most_downloaded"] . "</a></li>\r\n\t\t<li><a href=\"" . $BASEURL . "/update-plugin.php?plugin=specialtorrents&action=most-talked-about\">" . $lang->index["most_talked_about"] . "</a></li>\r\n\t</ul>\r\n</div>\r\n\r\n<script type=\"text/javascript\">\r\n\tjQuery(window).load(function()\r\n\t{\t\t\r\n\t\tjQuery(\"#specialtorrentstabs\").tabs\r\n\t\t({\r\n\t\t\tbeforeLoad: function( event, ui )\r\n\t\t\t{\r\n\t\t\t\tui.panel.html('<img src=\"" . $BASEURL . "/include/templates/" . $defaulttemplate . "/images/ajax_loading.gif\" alt=\"\" title=\"\" class=\"middle\" /> " . $lang->global["pleasewait"] . "...');\r\n\r\n\t\t\t\tui.jqXHR.error(function()\r\n\t\t\t\t{\r\n\t\t\t\t\t ui.panel.html(\"Something went wrong... Try agian later.\" );\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t});\r\n\t});\r\n</script>\r\n\r\n<!-- end specialtorrents -->";

?>