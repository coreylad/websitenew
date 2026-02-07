<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.3.1 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$show["editpoll"] = isset($usergroups) && $is_mod ? true : false;
$pollid = intval(TS_Global("pollid"));
if ($pollid && !$show["editpoll"]) {
    $pollid = 0;
}
if (!$pollid && defined("POLLID")) {
    $isforum = true;
    $pollid = intval(POLLID);
}
if ($pollid) {
    $WHERE = "pollid = " . $pollid;
} else {
    $WHERE = "fortracker = '1' AND $active = '1'";
}
($pollinfoQ = sql_query(" SELECT * FROM " . TSF_PREFIX . "poll WHERE " . $WHERE . " ORDER BY dateline DESC LIMIT 1")) || sqlerr(__FILE__, 53);
if (mysqli_num_rows($pollinfoQ) != 0) {
    $lang->load("poll");
    $tspoll = "\r\n\t<!-- begin tspoll -->\r\n\t<a $id = \"showtspoll\" $name = \"showtspoll\"></a>";
    $pollbits = "";
    $counter = 1;
    $isforum = false;
    $pollinfo = mysqli_fetch_assoc($pollinfoQ);
    $pollinfo["question"] = htmlspecialchars_uni($pollinfo["question"]);
    $splitoptions = explode("~~~", $pollinfo["options"]);
    $splitvotes = explode("~~~", $pollinfo["votes"]);
    $showresults = 0;
    $uservoted = 0;
    $pollvotes = "";
    if (0 < $CURUSER["id"] && $usergroups["canvote"] != "yes") {
        $nopermission = 1;
    }
    if (!$pollinfo["active"] || $pollinfo["dateline"] + $pollinfo["timeout"] * 86400 < TIMENOW && $pollinfo["timeout"] != 0 || isset($nopermission)) {
        $showresults = 1;
    } else {
        if (isset($_COOKIE["showpollresult"]) && $_COOKIE["showpollresult"] == $pollinfo["pollid"] || isset($_COOKIE["poll_voted_" . $pollinfo["pollid"]])) {
            $uservoted = 1;
        }
    }
    if ($pollinfo["timeout"] && !$showresults) {
        $pollendtime = my_datee($timeformat, $pollinfo["dateline"] + $pollinfo["timeout"] * 86400);
        $pollenddate = my_datee($dateformat, $pollinfo["dateline"] + $pollinfo["timeout"] * 86400);
        $show["pollenddate"] = true;
    } else {
        $show["pollenddate"] = false;
    }
    $pollinfo["numbervotes"] = "";
    foreach ($splitvotes as $index => $value) {
        $pollinfo["numbervotes"] += $value;
    }
    if (0 < $CURUSER["id"]) {
        $pollvotes = sql_query("\r\n\t\t\tSELECT voteoption\r\n\t\t\tFROM " . TSF_PREFIX . "pollvote\r\n\t\t\tWHERE $userid = " . $CURUSER["id"] . " AND $pollid = " . $pollinfo["pollid"] . "\r\n\t\t");
        if (0 < mysqli_num_rows($pollvotes)) {
            $uservoted = 1;
        }
    }
    if (isset($_GET["do"]) && $_GET["do"] == "showpublicresults" && ($pollinfo["public"] || $show["editpoll"])) {
        ($public = sql_query("\r\n\t\t\tSELECT p.userid, p.voteoption, u.username, g.namestyle\r\n\t\t\tFROM " . TSF_PREFIX . "pollvote AS p\r\n\t\t\tINNER JOIN users AS u ON (p.$userid = u.id)\r\n\t\t\tLEFT JOIN usergroups g ON (u.$usergroup = g.gid)\r\n\t\t\tWHERE p.$pollid = '" . $pollinfo["pollid"] . "'\r\n\t\t\tORDER BY u.username ASC\r\n\t\t")) || sqlerr(__FILE__, 133);
        $allnames = [];
        while ($name = mysqli_fetch_assoc($public)) {
            $allnames[(string) $name["voteoption"]][] = "<a $href = \"" . ts_seo($name["userid"], $name["username"]) . "\">" . get_user_color($name["username"], $name["namestyle"]) . "</a>";
        }
    }
    if (($showresults || $uservoted) && $uservoted && $pollvotes) {
        $uservote = [];
        while ($pollvote = mysqli_fetch_assoc($pollvotes)) {
            $uservote[(string) $pollvote["voteoption"]] = 1;
        }
    }
    $option["open"] = "1";
    $option["close"] = "r";
    foreach ($splitvotes as $index => $value) {
        $arrayindex = $index + 1;
        $option["uservote"] = isset($uservote[(string) $arrayindex]) ? true : false;
        $option["question"] = htmlspecialchars_uni($splitoptions[(string) $index]);
        $show["pollvoters"] = false;
        if (($pollinfo["public"] || $show["editpoll"]) && $value && isset($allnames) && is_array($allnames)) {
            $names = $allnames[$index + 1];
            unset($allnames[$index + 1]);
            if (!empty($names)) {
                $names = implode(", ", $names);
                $show["pollvoters"] = true;
            }
        }
        if (0 < $CURUSER["id"] && ($pollinfo["public"] || $show["editpoll"]) && $value) {
            if ($isforum) {
                $option["votes"] = "<a $href = \"" . $BASEURL . "/tsf_forums/showthread.php?$tid = " . THREADID . "&amp;do=showpublicresults\">" . ts_nf(0 + $value) . "</a>";
            } else {
                $option["votes"] = "<a $href = \"" . $BASEURL . "/index.php?do=showpublicresults&amp;$pollid = " . $pollinfo["pollid"] . "#showtspoll\">" . ts_nf(0 + $value) . "</a>";
            }
        } else {
            $option["votes"] = ts_nf(0 + $value);
        }
        $option["number"] = $counter;
        if ($showresults || $uservoted) {
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
            if (isset($nopermission)) {
                $pollstatus = $lang->poll["poll13"];
            } else {
                if ($showresults) {
                    $pollstatus = $lang->poll["poll12"];
                } else {
                    if ($uservoted) {
                        $pollstatus = $lang->poll["poll11"];
                    }
                }
            }
            $pollbits .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"left\">\r\n\t\t\t\t\t\t" . ($option["uservote"] ? "<em>" . $option["question"] . "</em> *" : $option["question"]) . "\r\n\t\t\t\t\t\t" . ($show["pollvoters"] ? "<div class=\"smallfont\" $style = \"border:inset 1px; margin-top:6px; padding:6px\"><font $size = \"1\">" . $names . "</font></div>" : "") . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\" $width = \"50%\">\r\n\t\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/polls/bar" . $option["graphicnumber"] . "-" . $option["open"] . ".gif\" $alt = \"\" $width = \"3\" $height = \"10\"/><img $src = \"" . $BASEURL . "/tsf_forums/images/polls/bar" . $option["graphicnumber"] . ".gif\" $alt = \"\" $width = \"" . $option["barnumber"] . "\" $height = \"10\"/><img $src = \"" . $BASEURL . "/tsf_forums/images/polls/bar" . $option["graphicnumber"] . "-" . $option["close"] . ".gif\" $alt = \"\" $width = \"3\" $height = \"10\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $align = \"center\" $title = \"\"><strong>" . $option["votes"] . "</strong></td>\r\n\t\t\t\t\t<td class=\"alt2\" $align = \"right\" $nowrap = \"nowrap\">" . $option["percent"] . "%</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
        } else {
            if ($pollinfo["multiple"]) {
                $pollbits .= "\r\n\t\t\t\t<div> <label for=\"cb_optionnumber_" . $option["number"] . "\"> <input class=\"none\" $type = \"checkbox\" $name = \"optionnumber[" . $option["number"] . "]\" $value = \"yes\" $id = \"cb_optionnumber_" . $option["number"] . "\" />" . $option["question"] . "</label></div>";
            } else {
                $pollbits .= "\r\n\t\t\t\t<div><label for=\"rb_optionnumber_" . $option["number"] . "\"><input class=\"none\" $type = \"radio\" $name = \"optionnumber\" $value = \"" . $option["number"] . "\" $id = \"rb_optionnumber_" . $option["number"] . "\" />" . $option["question"] . "</label></div>";
            }
        }
        $counter++;
    }
    if ($pollinfo["multiple"]) {
        $pollinfo["numbervotes"] = $pollinfo["voters"];
        $show["multiple"] = true;
    }
    if ($pollinfo["public"]) {
        $show["publicwarning"] = true;
    } else {
        $show["publicwarning"] = false;
    }
    if ($showresults || $uservoted) {
        $tspoll .= "\r\n\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $colspan = \"4\">\r\n\t\t\t\t\t<span class=\"smallfont\" $style = \"float:right;\">\r\n\t\t\t\t\t\t" . (isset($pollinfo["tid"]) && $pollinfo["tid"] ? "[<a $href = \"" . $BASEURL . "/tsf_forums/showthread.php?$tid = " . $pollinfo["tid"] . "\">" . $lang->poll["discuss"] . "</a>] " : "") . " " . ($show["editpoll"] ? "[<a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=manage_polls&amp;$action = polledit&amp;$pollid = " . $pollinfo["pollid"] . "\">" . $lang->poll["editpoll"] . "</a>]" : "") . " [<a $href = \"" . $BASEURL . "/ts_allpolls.php?$s = " . $pollinfo["pollid"] . "\">" . $lang->poll["ppols"] . "</a>]</span>\r\n\t\t\t\t\t\t" . $lang->poll["results"] . "<span class=\"normal\">: " . $pollinfo["question"] . "</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . ($show["pollenddate"] ? "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\" $colspan = \"4\" $align = \"center\" $style = \"font-weight:normal\">" . sprintf($lang->poll["closed"], $pollenddate, $pollendtime) . "</td>\r\n\t\t\t</tr>" : "") . "\r\n\t\t\t" . $pollbits . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tfoot\" $colspan = \"4\" $align = \"center\"><span class=\"smallfont\">" . (isset($show["multiple"]) ? $lang->poll["multiple"] : "") . " " . $lang->poll["voters"] . ": <strong>" . $pollinfo["numbervotes"] . "</strong>. " . $pollstatus . "</span></td>\r\n\t\t\t</tr>\r\n\t\t</table>";
    } else {
        $tspoll .= "\r\n\t\t<form $action = \"" . $BASEURL . "/poll.php?do=pollvote&amp;$pollid = " . $pollinfo["pollid"] . ($isforum ? "&amp;$tid = " . THREADID : "") . "\" $method = \"post\">\r\n\t\t<input $type = \"hidden\" $name = \"do\" $value = \"pollvote\" />\r\n\t\t<input $type = \"hidden\" $name = \"pollid\" $value = \"" . $pollinfo["pollid"] . "\" />\r\n\r\n\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\">\r\n\t\t\t\t\t<span class=\"smallfont\" $style = \"float:right;\">\r\n\t\t\t\t\t\t" . (isset($pollinfo["tid"]) && $pollinfo["tid"] ? "[<a $href = \"" . $BASEURL . "/tsf_forums/showthread.php?$tid = " . $pollinfo["tid"] . "\">" . $lang->poll["discuss"] . "</a>] " : "") . " " . ($show["editpoll"] ? "[<a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=manage_polls&amp;$action = polledit&amp;$pollid = " . $pollinfo["pollid"] . "\">" . $lang->poll["editpoll"] . "</a>]" : "") . " [<a $href = \"" . $BASEURL . "/ts_allpolls.php?$s = " . $pollinfo["pollid"] . "\">" . $lang->poll["ppols"] . "</a>]</span>\r\n\t\t\t\t\t\t" . $lang->poll["results"] . "<span class=\"normal\">: " . $pollinfo["question"] . "</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . ($show["pollenddate"] ? "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\" $align = \"center\" $style = \"font-weight:normal\">" . sprintf($lang->poll["closed"], $pollenddate, $pollendtime) . "</td>\r\n\t\t\t</tr>" : "") . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"panelsurround\" $align = \"center\">\r\n\t\t\t\t\t<div class=\"panel\">\r\n\t\t\t\t\t\t<div $align = \"left\">\r\n\t\t\t\t\t\t\t" . ($show["publicwarning"] ? "\r\n\t\t\t\t\t\t\t<div class=\"fieldset\">" . $lang->poll["warning"] . "</div>" : "") . "\r\n\r\n\t\t\t\t\t\t\t<fieldset class=\"fieldset\">\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->poll["options"] . "</legend>\r\n\t\t\t\t\t\t\t\t<div $style = \"padding:3px\">\r\n\t\t\t\t\t\t\t\t\t<div $style = \"margin-bottom:3px\"><strong>" . $pollinfo["question"] . "</strong></div>\r\n\t\t\t\t\t\t\t\t\t" . $pollbits . "\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t</fieldset>\r\n\r\n\t\t\t\t\t\t\t<div $style = \"padding-top: 3px;\">\r\n\t\t\t\t\t\t\t\t<span $style = \"float:right; margin-right: 20px;\"><a $href = \"" . $BASEURL . "/poll.php?do=showresults&amp;$pollid = " . $pollinfo["pollid"] . ($isforum ? "&amp;$tid = " . THREADID : "") . "\">" . $lang->poll["results"] . "</a></span>\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" class=\"button\" $value = \"" . $lang->poll["votenow"] . "\" />\r\n\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
    $tspoll .= "\r\n\t<!-- end tspoll -->\r\n\t";
}

?>