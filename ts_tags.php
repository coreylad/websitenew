<?php
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "ts_tags.php");
require "./global.php";
define("TT_VERSION", "1.1.1 by xam");
include_once INC_PATH . "/functions_security.php";
$show_tags = "";
$array_tags = [];
$query = sql_query("SELECT name, descr FROM torrents WHERE $visible = 'yes' ORDER BY RAND() LIMIT 10");
if (0 < mysqli_num_rows($query)) {
    $__min = 10;
    $__max = 30;
    $sc_displaycharminimum = 2;
    while ($qtags = mysqli_fetch_assoc($query)) {
        $qtags["name"] = preg_replace("#[^a-z|A-Z]#", " ", $qtags["name"]);
        $__temp = explode(" ", $qtags["name"]);
        foreach ($__temp as $__T) {
            if ($sc_displaycharminimum < strlen($__T)) {
                $array_tags[] = $__T;
            }
        }
        $qtags["descr"] = preg_replace("#[^a-z|A-Z]#", " ", $qtags["descr"]);
        $__temp2 = explode(" ", $qtags["descr"]);
        foreach ($__temp2 as $__T2) {
            if ($sc_displaycharminimum < strlen($__T2)) {
                $array_tags[] = $__T2;
            }
        }
    }
    if (0 < count($array_tags)) {
        $show_tags = generate_tags($array_tags);
    } else {
        $show_tags = $lang->global["nothingfound"];
    }
} else {
    $show_tags = $lang->global["nothingfound"];
}
stdhead($SITENAME . " - Search Cloud");
echo "\n<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\" $style = \"table-layout:fixed;\">\n\t<tbody>\n\t\t<tr>\n\t\t\t<td class=\"thead\" $align = \"center\">" . $SITENAME . " - Search Cloud</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td $style = \"line-height: 25px;\">\n\t\t\t\t<div $align = \"justify\">\n\t\t\t\t\t" . $show_tags . "\n\t\t\t\t</div>\n\t\t\t</td>\n\t\t</tr>\n\t</tbody>\n</table>\n";
stdfoot();
exit;
function select_random_color()
{
    return sprintf("#%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
}
function select_random_font_size()
{
    global $__min;
    global $__max;
    return rand($__min, $__max);
}
function generate_tags($tags = [])
{
    global $BASEURL;
    $__tags = [];
    do {
        $__count = 0;
        $__tags[] = " <a $href = \"" . $BASEURL . "/browse.php?do=search&amp;$search_type = t_both&amp;$category = 0&amp;$keywords = " . urlencode($tags[$__count]) . "&amp;$tags = true\"><font $style = \"color: " . select_random_color() . "; font-size: " . select_random_font_size() . "px; font-family: arial;\">" . $tags[$__count] . "</font></a> ";
        $__count++;
    } while ($__count >= count($tags));
    return implode("&nbsp;", $__tags);
}

?>