<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.2 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$MaxRequests = 5;
if (!defined("SKIP_CACHE_MESSAGE")) {
    define("SKIP_CACHE_MESSAGE", true);
}
require_once INC_PATH . "/functions_cache2.php";
if (!($mostrequestedtorrents = cache_check2("mostrequestedtorrents"))) {
    $mostrequestedtorrents = "<!-- begin mostrequestedtorrents -->";
    ($query = sql_query("SELECT r.id, r.userid, r.request, r.hits, u.username, g.namestyle FROM requests r LEFT JOIN users u ON (u.id=r.userid) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE r.filled='no' ORDER BY r.hits DESC LIMIT " . $MaxRequests)) || sqlerr(__FILE__, 40);
    $mostrequestedtorrents .= "\r\n\t<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" align=\"left\" width=\"80%\">Request</td>\r\n\t\t\t<td class=\"subheader\" align=\"center\" width=\"10%\">Username</td>\r\n\t\t\t<td class=\"subheader\" align=\"center\" width=\"10%\">Votes</td>\r\n\t\t</tr>";
    while ($mr = mysqli_fetch_assoc($query)) {
        $mostrequestedtorrents .= "\r\n\t\t<tr>\r\n\t\t\t<td align=\"left\" width=\"80%\"><a href=\"" . $BASEURL . "/viewrequests.php?do=view_request&rid=" . $mr["id"] . "\">" . htmlspecialchars_uni($mr["request"]) . "</a></td>\r\n\t\t\t<td align=\"center\" width=\"10%\"><a href=\"" . ts_seo($mr["userid"], $mr["username"]) . "\">" . get_user_color($mr["username"], $mr["namestyle"]) . "</a></td>\r\n\t\t\t<td align=\"center\" width=\"10%\">" . ts_nf($mr["hits"]) . "</td>\r\n\t\t</tr>";
    }
    $mostrequestedtorrents .= "\r\n\t</table>\r\n\t<!-- end mostrequestedtorrents -->";
    cache_save2("mostrequestedtorrents", $mostrequestedtorrents);
}

?>