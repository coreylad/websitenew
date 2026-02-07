<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function TS_fetch_start_end_total_array($pagenumber, $perpage, $total)
{
    $first = $perpage * ($pagenumber - 1);
    $last = $first + $perpage;
    if ($total < $last) {
        $last = $total;
    }
    $first++;
    return ["first" => ts_number_format($first), "last" => ts_number_format($last)];
}
function ts_number_format($number, $decimals = 0, $bytesize = false, $decimalsep = NULL, $thousandsep = NULL)
{
    $type = "";
    if (empty($number)) {
        return 0;
    }
    if (preg_match("#^(\\d+(?:\\.\\d+)?)(?>\\s*)([mkg])b?\$#i", trim($number), $matches)) {
        strtolower($matches[2]);
        switch (strtolower($matches[2])) {
            case "g":
                $number = $matches[1] * 1073741824;
                break;
            case "m":
                $number = $matches[1] * 1048576;
                break;
            case "k":
                $number = $matches[1] * 1024;
                break;
            default:
                $number = $matches[1] * 1;
        }
    }
    if ($bytesize) {
        if (1073741824 <= $number) {
            $number = $number / 1073741824;
            $decimals = 2;
            $type = " GB";
        } else {
            if (1048576 <= $number) {
                $number = $number / 1048576;
                $decimals = 2;
                $type = " MB";
            } else {
                if (1024 <= $number) {
                    $number = $number / 1024;
                    $decimals = 1;
                    $type = " KB";
                } else {
                    $decimals = 0;
                    $type = " B";
                }
            }
        }
    }
    if ($decimalsep === NULL) {
        $decimalsep = ".";
    }
    if ($thousandsep === NULL) {
        $thousandsep = ",";
    }
    return str_replace("_", "&nbsp;", number_format($number, $decimals, $decimalsep, $thousandsep)) . $type;
}
function TS_construct_page_nav($pagenumber, $perpage, $results, $address, $address2 = "", $anchor = "")
{
    global $show;
    $curpage = 0;
    $pagenav = "";
    $firstlink = "";
    $prevlink = "";
    $lastlink = "";
    $nextlink = "";
    if ($results <= $perpage) {
        $show["pagenav"] = false;
        return "";
    }
    $show["pagenav"] = true;
    $total = ts_number_format($results);
    $totalpages = ceil($results / $perpage);
    $show["prev"] = false;
    $show["next"] = false;
    $show["first"] = false;
    $show["last"] = false;
    if (1 < $pagenumber) {
        $prevpage = $pagenumber - 1;
        $prevnumbers = ts_fetch_start_end_total_array($prevpage, $perpage, $results);
        $show["prev"] = true;
    }
    if ($pagenumber < $totalpages) {
        $nextpage = $pagenumber + 1;
        $nextnumbers = ts_fetch_start_end_total_array($nextpage, $perpage, $results);
        $show["next"] = true;
    }
    $pagenavpages = "3";
    if (!is_array($pagenavsarr)) {
        $pagenavs = "10 50 100 500 1000";
        $pagenavsarr[] = preg_split("#\\s+#s", $pagenavs, -1, PREG_SPLIT_NO_EMPTY);
        while ($curpage++ < $totalpages) {
        }
        $pagenav = "\r\n\t<div class=\"pagenav\" $align = \"right\">\r\n\t\t<table class=\"tborder\" $cellpadding = \"1\" $cellspacing = \"0\" $border = \"0\" $width = \"100\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td $style = \"font-weight:normal\">Page " . $pagenumber . " of " . $totalpages . "</td>\r\n\t\t\t\t" . ($show["first"] ? "<td class=\"alt1\" $nowrap = \"nowrap\"><a class=\"smallfont\" $href = \"" . $address . $address2 . ($anchor ? "#" . $anchor : "") . "\" $title = \"First Page - Show results " . $firstnumbers["first"] . " to " . $firstnumbers["last"] . " of " . $total . "\"><strong>&laquo;</strong> First</a></td>" : "") . "\r\n\t\t\t\t" . ($show["prev"] ? "<td class=\"alt1\"><a class=\"smallfont\" $href = \"" . $address . $address2 . ($prevpage != 1 ? "&amp;$page = " . $prevpage : "") . ($anchor ? "#" . $anchor : "") . "\" $title = \"Prev Page - Show results " . $prevnumbers["first"] . " to " . $prevnumbers["last"] . " of " . $total . "\">&lt;</a></td>" : "") . "\r\n\t\t\t\t" . $pagenav . "\r\n\t\t\t\t" . ($show["next"] ? "<td class=\"alt1\"><a class=\"smallfont\" $href = \"" . $address . $address2 . "&amp;$page = " . $nextpage . ($anchor ? "#" . $anchor : "") . "\" $title = \"Next Page - Show results " . $nextnumbers["first"] . " to " . $nextnumbers["last"] . " of " . $total . "\">&gt;</a></td>" : "") . "\r\n\t\t\t\t" . ($show["last"] ? "<td class=\"alt1\" $nowrap = \"nowrap\"><a class=\"smallfont\" $href = \"" . $address . $address2 . "&amp;$page = " . $totalpages . ($anchor ? "#" . $anchor : "") . "\" $title = \"Last Page - Show results " . $lastnumbers["first"] . " to " . $lastnumbers["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></td>" : "") . "\r\n\t\t\t\t" . ($show["popups"] ? "<td class=\"vbmenu_control\" $title = \"" . $address . $address2 . "\"><a $name = \"PageNav\"></a></td>" : "") . "\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t</div>";
        return $pagenav;
    }
    if ($pagenavpages <= abs($curpage - $pagenumber) && $pagenavpages != 0) {
        if ($curpage == 1) {
            $firstnumbers = ts_fetch_start_end_total_array(1, $perpage, $results);
            $show["first"] = true;
        }
        if ($curpage == $totalpages) {
            $lastnumbers = ts_fetch_start_end_total_array($totalpages, $perpage, $results);
            $show["last"] = true;
        }
        if (in_array(abs($curpage - $pagenumber), $pagenavsarr) && $curpage != 1 && $curpage != $totalpages) {
            $pagenumbers = ts_fetch_start_end_total_array($curpage, $perpage, $results);
            $relpage = $curpage - $pagenumber;
            if (0 < $relpage) {
                $relpage = "+" . $relpage;
            }
            $pagenav .= "<td class=\"alt1\"><a class=\"smallfont\" $href = \"" . $address . $address2 . ($curpage != 1 ? "&amp;$page = " . $curpage : "") . ($anchor ? "#" . $anchor : "") . "\" $title = \"Show results " . $pagenumbers["first"] . " to " . $pagenumbers["last"] . " of " . $total . "\"><!--" . $relpage . "-->" . $curpage . "</a></td>";
        }
    } else {
        if ($curpage == $pagenumber) {
            $numbers = ts_fetch_start_end_total_array($curpage, $perpage, $results);
            $pagenav .= "<td class=\"alt2\"><span class=\"smallfont\" $title = \"Showing results " . $numbers["first"] . " to " . $numbers["last"] . " of " . $total . "\"><strong>" . $curpage . "</strong></span></td>";
        } else {
            $pagenumbers = ts_fetch_start_end_total_array($curpage, $perpage, $results);
            $pagenav .= "<td class=\"alt1\"><a class=\"smallfont\" $href = \"" . $address . $address2 . ($curpage != 1 ? "&amp;$page = " . $curpage : "") . ($anchor ? "#" . $anchor : "") . "\" $title = \"Show results " . $pagenumbers["first"] . " to " . $pagenumbers["last"] . " of " . $total . "\"><!--" . $relpage . "-->" . $curpage . "</a></td>";
        }
    }
}
function sanitize_maxposts($perpage = 0, $type = "post")
{
    global $CURUSER;
    global $ts_perpage;
    global $f_postsperpage;
    $usermaxposts = "5,10,20,30,40";
    $max = intval(max(explode(",", $usermaxposts)));
    $maxposts = $f_postsperpage;
    if ($type != "post") {
        $maxposts = $ts_perpage;
        $CURUSER["postsperpage"] = $CURUSER["torrentsperpage"];
    }
    if ($max && $CURUSER["postsperpage"]) {
        if (!$perpage) {
            return $CURUSER["postsperpage"] == 0 ? $maxposts : $CURUSER["postsperpage"];
        }
        if ($perpage == -1) {
            return $max;
        }
        return $max < $perpage ? $max : $perpage;
    }
    if (!empty($maxposts)) {
        return $maxposts;
    }
    return 10;
}

?>