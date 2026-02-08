<?php
if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.1 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
if (!defined("SKIP_CACHE_MESSAGE")) {
    define("SKIP_CACHE_MESSAGE", true);
}
require_once INC_PATH . "/functions_cache2.php";
if (!($mostsnatchedtorrents = cache_check2("mostsnatchedtorrents"))) {
    $mostsnatchedtorrents = "<!-- begin mostsnatchedtorrents -->";
    ($query = sql_query("SELECT id, name, times_completed FROM torrents WHERE $ts_external = 'no' ORDER BY times_completed DESC LIMIT 5")) || sqlerr(__FILE__, 37);
    if (!mysqli_num_rows($query)) {
        $mostsnatchedtorrents = "";
        return $mostsnatchedtorrents;
    }
    while ($ms = mysqli_fetch_assoc($query)) {
        $seolink = ts_seo($ms["id"], $ms["name"], "s");
        $fullname = htmlspecialchars_uni($ms["name"]);
        $mostsnatchedtorrents .= "<a $href = \"" . $seolink . "\" $title = \"" . $fullname . "\">" . cutename($ms["name"], 20) . "</a> (" . ts_nf($ms["times_completed"]) . ")<br />";
    }
    $mostsnatchedtorrents .= "<!-- end mostsnatchedtorrents -->";
    cache_save2("mostsnatchedtorrents", $mostsnatchedtorrents);
}

?>