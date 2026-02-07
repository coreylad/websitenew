<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.1 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
if (!defined("SKIP_CACHE_MESSAGE")) {
    define("SKIP_CACHE_MESSAGE", true);
}
require_once INC_PATH . "/functions_cache2.php";
if (!($mostvisitedusers = cache_check2("mostvisitedusers"))) {
    $mostvisitedusers = "<!-- begin mostvisitedusers -->";
    ($query = sql_query("SELECT u.id, u.username, u.visitorcount, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE u.visitorcount > 0 ORDER BY u.visitorcount DESC LIMIT 5")) || sqlerr(__FILE__, 37);
    if (!mysqli_num_rows($query)) {
        $mostvisitedusers = "";
        return $mostvisitedusers;
    }
    while ($mv = mysqli_fetch_assoc($query)) {
        $mostvisitedusers .= "<a href=\"" . ts_seo($mv["id"], $mv["username"]) . "\">" . get_user_color($mv["username"], $mv["namestyle"]) . "</a> (" . ts_nf($mv["visitorcount"]) . ")<br />";
    }
    $mostvisitedusers .= "<!-- end mostvisitedusers -->";
    cache_save2("mostvisitedusers", $mostvisitedusers);
}

?>