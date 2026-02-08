<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function XBT_Peers_Table($Rows = [], $Name = "", $IsLeecher = true)
{
    global $dateformat;
    global $timeformat;
    global $lang;
    global $is_mod;
    global $CURUSER;
    global $torrent;
    global $xbt_announce_interval;
    $Count = ts_nf(count($Rows));
    $List = "";
    foreach ($Rows as $Row) {
        if ((TS_Match($Row["options"], "I3") || TS_Match($Row["options"], "I4")) && !$is_mod && $CURUSER["id"] != $Row["id"]) {
            $Username = "<i>" . $lang->global["anonymous"] . "</i>";
        } else {
            $Username = "<a $href = \"" . ts_seo($Row["uid"], $Row["username"]) . "\">" . get_user_color($Row["username"], $Row["namestyle"]) . "</a> " . get_user_icons($Row);
        }
        $ratio = 0 < $Row["uploaded"] && 0 < $Row["downloaded"] ? @number_format($Row["uploaded"] / $Row["downloaded"], 2) : "0.0";
        $ratio = "<font $color = \"" . get_ratio_color($ratio) . "\">" . $ratio . "</font>";
        $Left = "";
        if ($IsLeecher) {
            $Left = @number_format($Row["left"] / $torrent["size"] * 100, 1);
            $Left = (100 < $Left ? 100 : $Left) . "%";
        }
        $seedtime = mkprettytime($Row["announced"] * $xbt_announce_interval);
        $List .= "\r\n\t\t<tr>\r\n\t\t\t<td>" . $Username . ($is_mod && $Row["ipa"] && $Row["port"] ? "<br /><small><i>" . long2ip($Row["ipa"]) . ":" . intval($Row["port"]) . "</i></small>" : "") . "</td>\r\n\t\t\t<td>" . mksize($Row["uploaded"]) . "</td>\r\n\t\t\t<td>" . mksize($Row["up_rate"]) . "/s</td>\r\n\t\t\t<td>" . mksize($Row["downloaded"]) . "</td>\r\n\t\t\t<td>" . mksize($Row["down_rate"]) . "/s</td>\r\n\t\t\t<td $align = \"center\">" . $ratio . "</td>\r\n\t\t\t<td $align = \"center\">" . ($IsLeecher ? mksize($Row["left"]) . " (" . $Left . ")" : ($Row["completed"] == 0 ? $lang->global["redno"] : $lang->global["greenyes"])) . "</td>\r\n\t\t\t<td>" . ts_nf($Row["announced"]) . " x " . $lang->global["times"] . "</td>\r\n\t\t\t<td>" . my_datee($dateformat, $Row["mtime"]) . " " . my_datee($timeformat, $Row["mtime"]) . "</td>\r\n\t\t\t<td>" . $seedtime . "</td>\r\n\t\t</tr>";
    }
    $List = "\r\n\t<table $width = \"100%\" $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"10\" class=\"thead\">" . $Count . " " . $Name . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"13%\">" . $lang->details["username"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"10%\">" . $lang->details["uploaded"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"10%\">" . $lang->details["speed"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"7%\">" . $lang->details["downloaded"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"7%\">" . $lang->details["speed"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"5%\" $align = \"center\">" . $lang->global["ratio"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"8%\" $align = \"center\">" . ($IsLeecher ? $lang->details["left"] : $lang->details["completed"]) . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"10%\">" . $lang->details["announced"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"10%\">" . $lang->details["lastactivity"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"20%\">" . $lang->details[$IsLeecher ? "leechtime" : "seedtime"] . "</td>\r\n\t\t</tr>\r\n\t\t" . $List . "\r\n\t</table>\r\n\t<br />";
    return $List;
}

?>