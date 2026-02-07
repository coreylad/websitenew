<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.3 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$news = "";
if (!defined("SKIP_CACHE_MESSAGE")) {
    define("SKIP_CACHE_MESSAGE", true);
}
require_once INC_PATH . "/functions_cache2.php";
if (!($newscached = cache_check2("news"))) {
    if (1 < MAX_NEWS) {
        $news .= "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction collapseNews(nID)\r\n\t\t\t{\r\n\t\t\t\tjQuery(document).ready(function()\r\n\t\t\t\t{\r\n\t\t\t\t\tjQuery(\"#\"+nID).toggle(\"slow\");\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t";
    }
    $news_query = sql_query("SELECT id, added, body, title FROM news ORDER BY added DESC LIMIT 0," . MAX_NEWS);
    if (0 < mysqli_num_rows($news_query)) {
        for ($news_count = 0; $news_results = mysqli_fetch_assoc($news_query); $news_count++) {
            if (0 < $news_count) {
                $news .= "<br /><br />";
            }
            $news .= "\r\n\t\t\t<div class=\"subheader\" $style = \"padding: 5px;\">\r\n\t\t\t\t<strong>" . (0 < $news_count ? "<a $href = \"javascript: collapseNews('news" . $news_results["id"] . "');\">" . $news_results["title"] : $news_results["title"]) . " - " . my_datee($dateformat, $news_results["added"]) . " " . my_datee($timeformat, $news_results["added"]) . (0 < $news_count ? "</a>" : "") . "</strong>\r\n\t\t\t</div>\r\n\t\t\t<div $id = \"news" . $news_results["id"] . "\" $style = \"display: " . (0 < $news_count ? "none" : "inline") . ";\">\r\n\t\t\t\t" . nl2br($news_results["body"]) . "\r\n\t\t\t</div>";
        }
    }
    cache_save2("news", $news);
} else {
    $news .= $is_mod ? "<span $style = \"float: right;\">[<a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=manage_news\">" . $lang->index["newspage"] . "</a>]</span><br /><hr />" : "<hr />";
    $news .= $newscached;
}

?>