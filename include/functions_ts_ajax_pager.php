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
function TSAjaxPager($perpage, $results, $tid, $jsFunction = "TSAjaxPager")
{
    global $lang;
    global $BASEURL;
    if ($results < $perpage) {
        return ["", "", ""];
    }
    if ($results) {
        $totalpages = @ceil($results / $perpage);
    } else {
        $totalpages = 0;
    }
    if (isset($_GET["showlast"]) && $_GET["showlast"] == "true") {
        $pagenumber = $totalpages;
    } else {
        $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    }
    sanitize_pageresults($results, $pagenumber, $perpage, 200);
    $limitlower = ($pagenumber - 1) * $perpage;
    $limitupper = $pagenumber * $perpage;
    if ($results < $limitupper) {
        $limitupper = $results;
        if ($results < $limitlower) {
            $limitlower = $results - $perpage - 1;
        }
    }
    if ($limitlower < 0) {
        $limitlower = 0;
    }
    $pagenav = $firstlink = $prevlink = $lastlink = $nextlink = "";
    $curpage = 0;
    if ($results <= $perpage) {
        $show["pagenav"] = false;
        return ["", "", "LIMIT " . $limitlower . ", " . $perpage];
    }
    $show["pagenav"] = true;
    $total = ts_nf($results);
    $show["last"] = false;
    $show["first"] = $show["last"];
    $show["next"] = $show["first"];
    $show["prev"] = $show["next"];
    if (1 < $pagenumber) {
        $prevpage = $pagenumber - 1;
        $prevnumbers = fetch_start_end_total_array($prevpage, $perpage, $results);
        $show["prev"] = true;
    }
    if ($pagenumber < $totalpages) {
        $nextpage = $pagenumber + 1;
        $nextnumbers = fetch_start_end_total_array($nextpage, $perpage, $results);
        $show["next"] = true;
    }
    $pagenavpages = "3";
    if (!isset($pagenavsarr) || !is_array($pagenavsarr)) {
        $pagenavs = "10 50 100 500 1000";
        $pagenavsarr[] = preg_split("#\\s+#s", $pagenavs, -1, PREG_SPLIT_NO_EMPTY);
        while ($curpage++ < $totalpages) {
        }
        $prp = isset($prevpage) && $prevpage != 1 ? "page=" . $prevpage : "";
        $pagenav = "\t\r\n\t<table $width = \"100%\" $border = \"0\" class=\"none\" $style = \"clear: both;\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td class=\"none\" $width = \"100%\" $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $totalpages . "</li>\r\n\t\t\t\t\t\t" . ($show["first"] ? "<li><a class=\"smalltext\" $onclick = \"" . $jsFunction . "(" . $tid . ", 1); return false;\" $href = \"#show_allcomments\" $title = \"" . $lang->global["first_page"] . " - " . sprintf($lang->global["show_results"], $firstnumbers["first"], $firstnumbers["last"], $total) . "\">&laquo; " . $lang->global["first"] . "</a></li>" : "") . ($show["prev"] ? "<li><a class=\"smalltext\" $onclick = \"" . $jsFunction . "(" . $tid . ", " . $prevpage . "); return false;\" $href = \"#show_allcomments\" $title = \"" . $lang->global["prev_page"] . " - " . sprintf($lang->global["show_results"], $prevnumbers["first"], $prevnumbers["last"], $total) . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $pagenav . "\r\n\t\t\t\t\t\t" . ($show["next"] ? "<li><a class=\"smalltext\" $onclick = \"" . $jsFunction . "(" . $tid . ", " . $nextpage . "); return false;\" $href = \"#show_allcomments\" $title = \"" . $lang->global["next_page"] . " - " . sprintf($lang->global["show_results"], $nextnumbers["first"], $nextnumbers["last"], $total) . "\">&gt;</a></li>" : "") . ($show["last"] ? "<li><a class=\"smalltext\" $onclick = \"" . $jsFunction . "(" . $tid . ", " . $totalpages . "); return false;\" $href = \"#show_allcomments\" $title = \"" . $lang->global["last_page"] . " - " . sprintf($lang->global["show_results"], $lastnumbers["first"], $lastnumbers["last"], $total) . "\">" . $lang->global["last"] . " <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
        $pagenav2 = str_replace(["quicknavpage", "Page_Number"], ["quicknavpage2", "Page_Number2"], $pagenav);
        return [$pagenav, $pagenav2, "LIMIT " . $limitlower . ", " . $perpage];
    }
    if ($pagenavpages <= abs($curpage - $pagenumber) && $pagenavpages != 0) {
        if ($curpage == 1) {
            $firstnumbers = fetch_start_end_total_array(1, $perpage, $results);
            $show["first"] = true;
        }
        if ($curpage == $totalpages) {
            $lastnumbers = fetch_start_end_total_array($totalpages, $perpage, $results);
            $show["last"] = true;
        }
        if (in_array(abs($curpage - $pagenumber), $pagenavsarr) && $curpage != 1 && $curpage != $totalpages) {
            $pagenumbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $relpage = $curpage - $pagenumber;
            if (0 < $relpage) {
                $relpage = "+" . $relpage;
            }
            $pagenav .= "<li><a class=\"smalltext\" $onclick = \"" . $jsFunction . "(" . $tid . ", " . ($curpage != 1 ? $curpage : 1) . "); return false;\" $href = \"#show_allcomments\" $title = \"" . sprintf($lang->global["show_results"], $pagenumbers["first"], $pagenumbers["last"], $total) . "\"><!--" . $relpage . "-->" . $curpage . "</a></li>";
        }
    } else {
        if ($curpage == $pagenumber) {
            $numbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $pagenav .= "<li><a $name = \"current\" class=\"current\" $id = \"page_" . $curpage . "\" $title = \"" . sprintf($lang->global["showing_results"], $numbers["first"], $numbers["last"], $total) . "\">" . $curpage . "</a></li>";
        } else {
            $pagenumbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $pagenav .= "<li><a $id = \"page_" . $curpage . "\" $onclick = \"" . $jsFunction . "(" . $tid . ", " . ($curpage != 1 ? $curpage : 1) . "); return false;\" $href = \"#show_allcomments\" $title = \"" . sprintf($lang->global["show_results"], $pagenumbers["first"], $pagenumbers["last"], $total) . "\">" . $curpage . "</a></li>";
        }
    }
}

?>