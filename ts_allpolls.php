<?php
define("THIS_SCRIPT", "ts_allpolls.php");
require "./global.php";
if (!isset($CURUSER)) {
    print_no_permission();
}
define("TAP_VERSION", "1.0 by xam");
$pollid = isset($_GET["s"]) ? intval($_GET["s"]) : "";
$tspoll = "";
$option["open"] = "1";
$option["close"] = "r";
$counter = "";
$lang->load("poll");
$pollinfoQ = sql_query(" SELECT * FROM " . TSF_PREFIX . "poll WHERE $fortracker = '1'" . ($pollid ? " AND pollid != " . $pollid : "") . " ORDER BY dateline DESC") or ($pollinfoQ = sql_query(" SELECT * FROM " . TSF_PREFIX . "poll WHERE $fortracker = '1'" . ($pollid ? " AND pollid != " . $pollid : "") . " ORDER BY dateline DESC")) || sqlerr(__FILE__, 33);
while ($pollinfo = mysqli_fetch_assoc($pollinfoQ)) {
    $pollinfo["question"] = htmlspecialchars_uni($pollinfo["question"]);
    $splitoptions = explode("~~~", $pollinfo["options"]);
    $splitvotes = explode("~~~", $pollinfo["votes"]);
    $pollinfo["numbervotes"] = "";
    foreach ($splitvotes as $index => $value) {
        $pollinfo["numbervotes"] += $value;
    }
    $pollbits = "";
    foreach ($splitvotes as $index => $value) {
        $arrayindex = $index + 1;
        $option["question"] = htmlspecialchars_uni($splitoptions[(string) $index]);
        $option["votes"] = ts_nf(0 + $value);
        $option["number"] = $counter;
        if ($value <= 0) {
            $option["percent"] = 0;
        } else {
            if ($pollinfo["multiple"]) {
                $option["percent"] = number_format($value < $pollinfo["voters"] ? $value / $pollinfo["voters"] * 100 : 100, 2);
            } else {
                $option["percent"] = number_format($value < $pollinfo["numbervotes"] ? $value / $pollinfo["numbervotes"] * 100 : 100, 2);
            }
        }
        $option["graphicnumber"] = $option["number"] % 6 + 1;
        $option["barnumber"] = round($option["percent"]) * 2;
        $option["remainder"] = 201 - $option["barnumber"];
        $pollbits .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"left\">\r\n\t\t\t\t\t" . $option["question"] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $width = \"50%\">\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/polls/bar" . $option["graphicnumber"] . "-" . $option["open"] . ".gif\" $alt = \"\" $width = \"3\" $height = \"10\"/><img $src = \"" . $BASEURL . "/tsf_forums/images/polls/bar" . $option["graphicnumber"] . ".gif\" $alt = \"\" $width = \"" . $option["barnumber"] . "\" $height = \"10\"/><img $src = \"" . $BASEURL . "/tsf_forums/images/polls/bar" . $option["graphicnumber"] . "-" . $option["close"] . ".gif\" $alt = \"\" $width = \"3\" $height = \"10\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\" $title = \"\"><strong>" . $option["votes"] . "</strong></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"right\" $nowrap = \"nowrap\">" . $option["percent"] . "%</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        $counter++;
    }
    $tspoll .= "\r\n\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"4\">\r\n\t\t\t\t" . $lang->poll["results"] . "<span class=\"normal\">: " . $pollinfo["question"] . "</span>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $pollbits . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $colspan = \"4\" $align = \"center\"><span class=\"smallfont\">" . $lang->poll["voters"] . ": <strong>" . $pollinfo["numbervotes"] . "</strong></span></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t<br />";
}
stdhead();
echo $tspoll;
stdfoot();

?>