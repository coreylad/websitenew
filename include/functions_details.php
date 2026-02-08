<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function getagent($httpagent = "", $peer_id = "")
{
    global $lang;
    return $httpagent ? $httpagent : ($peer_id ? $peer_id : $lang->global["unknown"]);
}
function dltable($name, $arr, $torrent, $id)
{
    global $CURUSER;
    global $pic_base_url;
    global $lang;
    global $usergroups;
    global $is_mod;
    global $BASEURL;
    $totalcount = $arr && is_array($arr) ? count($arr) : 0;
    $p = "<b>" . $totalcount . " " . $name . "</b>";
    if ($totalcount <= 0) {
        return $p;
    }
    $s = "\r\n\t<a $name = \"" . $id . "\" $id = \"" . $id . "\"></a>\r\n\t<table $width = \"100%\" $border = \"0\" $cellspacing = \"0\" $cellpadding = \"3\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"11\" class=\"thead\">" . $p . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\">" . $lang->details["userip"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["conn"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["up"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["urate"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["down"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["drate"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["ratio"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["done"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["since"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["idle"] . "</td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">" . $lang->details["client"] . "</td>\r\n\t\t\t</tr>";
    $now = TIMENOW;
    include_once INC_PATH . "/functions_ratio.php";
    foreach ($arr as $e) {
        if ((TS_Match($e["options"], "I3") || TS_Match($e["options"], "I4")) && !$is_mod && $CURUSER["id"] != $e["id"]) {
            $s .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\">" . $lang->global["anonymous"] . "</td>\r\n\t\t\t\t" . str_repeat("<td $align = \"center\">---</td>", 10) . "\r\n\t\t\t</tr>";
        } else {
            if (isset($num)) {
                $num++;
            } else {
                $num = 1;
            }
            $dnsstuff = "<br /><a $href = \"http://whois.domaintools.com/" . htmlspecialchars_uni($e["ip"]) . "\" $target = \"_blank\"><font class=\"small\"><b>" . htmlspecialchars_uni($e["ip"]) . "</b></font></a>:<u><font class=\"small\"><b>" . $e["port"] . "</b></font></u></td>\n";
            $pregreplace = "<br />***.***.***.***</td>\n";
            $highlight = $CURUSER["id"] == $e["id"] ? " class=highlight" : "";
            $s .= "<tr" . $highlight . ">\n";
            if (!empty($e["username"])) {
                if ($is_mod || $torrent["anonymous"] != "yes" || $e["id"] != $torrent["owner"]) {
                    $s .= "<td $style = \"white-space: nowrap; text-align: center;\"><a $href = \"" . ts_seo($e["userid"], $e["username"]) . "\"><b>" . get_user_color($e["username"], $e["namestyle"]) . "</b></a>" . ($e["donor"] == "yes" ? "<img $src = " . $pic_base_url . "star.gif $title = '" . $lang->global["imgdonated"] . "'>" : "") . ($e["enabled"] == "no" ? "<img $src = " . $pic_base_url . "disabled.gif $title = \"" . $lang->global["imgdisabled"] . "\" $style = 'margin-left: 2px'>" : ($e["warned"] == "yes" ? "<a $href = \"rules.php#warning\" class=\"altlink\"><img $src = \"" . $pic_base_url . "warned.gif\" $title = \"" . $lang->global["imgwarned"] . "\" $border = \"0\"></a>" : ""));
                    $s .= $is_mod ? $dnsstuff : $pregreplace;
                } else {
                    $s .= "<td>" . $lang->global["anonymous"] . "</a></td>\n";
                }
            } else {
                $s .= "<td>" . $lang->global["unknown"] . "</td>\n";
            }
            $secs = max(1, $now - $e["st"] - ($now - $e["la"]));
            $s .= "<td $align = \"center\">" . ($e["connectable"] == "yes" ? $lang->details["yes"] : $lang->details["no"]) . "</td>\n";
            $s .= "<td $align = \"right\">" . mksize($e["uploaded"]) . "</td>\n";
            $s .= "<td $align = \"right\"><span $style = \"white-space: nowrap;\">" . @mksize(($e["uploaded"] - $e["uploadoffset"]) / $secs) . "/s</span></td>\n";
            $s .= "<td $align = \"right\">" . mksize($e["downloaded"]) . "</td>\n";
            if ($e["seeder"] == "no") {
                $s .= "<td $align = \"right\"><span $style = \"white-space: nowrap;\">" . @mksize(($e["downloaded"] - $e["downloadoffset"]) / $secs) . "/s</span></td>\n";
            } else {
                $s .= "<td $align = \"right\"><span $style = \"white-space: nowrap;\">" . @mksize(($e["downloaded"] - $e["downloadoffset"]) / @max(1, $e["finishedat"] - $e["st"])) . "/s</span></td>\n";
            }
            if ($e["downloaded"]) {
                $ratio = @floor($e["uploaded"] / $e["downloaded"] * 1000) / 1000;
                $s .= "<td $align = \"right\"><font $color = " . get_ratio_color($ratio) . ">" . @number_format($ratio, 2) . "</font></td>\n";
            } else {
                if ($e["uploaded"]) {
                    $s .= "<td $align = \"right\">" . $lang->details["inf"] . "</td>\n";
                } else {
                    $s .= "<td $align = right>---</td>\n";
                }
            }
            $s .= "<td $align = \"right\">" . @sprintf("%.2f%%", 100 * (1 - $e["to_go"] / $torrent["size"])) . "</td>\n";
            $s .= "<td $align = \"right\">" . mkprettytime($now - $e["st"]) . "</td>\n";
            $s .= "<td $align = \"right\">" . mkprettytime($now - $e["la"]) . "</td>\n";
            $s .= "<td $align = \"left\">" . htmlspecialchars_uni(getagent($e["agent"], $e["peer_id"])) . "</td>\n";
            $s .= "</tr>\n";
        }
    }
    $s .= "</table>\n";
    return $s;
}
function leech_sort($a, $b)
{
    if (isset($_GET["usort"])) {
        return seed_sort($a, $b);
    }
    $x = $a["to_go"];
    $y = $b["to_go"];
    if ($x == $y) {
        return 0;
    }
    if ($x < $y) {
        return -1;
    }
    return 1;
}
function seed_sort($a, $b)
{
    $x = $a["uploaded"];
    $y = $b["uploaded"];
    if ($x == $y) {
        return 0;
    }
    if ($x < $y) {
        return 1;
    }
    return -1;
}

?>