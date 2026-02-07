<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function ts_external_share_ratio($shareratio)
{
    global $BASEURL;
    $imagepath = $BASEURL . "/include/ts_external_scrape/images/";
    if ($shareratio == 0 || 1 <= $shareratio && $shareratio < 10) {
        return "<img src=" . $imagepath . "0.png border=0 class=inlineimg>";
    }
    if ($shareratio == 100 || 100 < $shareratio) {
        return "<img src=" . $imagepath . "10.png border=0 class=inlineimg>";
    }
    if (10 <= $shareratio && $shareratio < 20) {
        return "<img src=" . $imagepath . "1.png border=0 class=inlineimg>";
    }
    if (20 <= $shareratio && $shareratio < 30) {
        return "<img src=" . $imagepath . "2.png border=0 class=inlineimg>";
    }
    if (30 <= $shareratio && $shareratio < 40) {
        return "<img src=" . $imagepath . "3.png border=0 class=inlineimg>";
    }
    if (40 <= $shareratio && $shareratio < 50) {
        return "<img src=" . $imagepath . "4.png border=0 class=inlineimg>";
    }
    if (50 <= $shareratio && $shareratio < 60) {
        return "<img src=" . $imagepath . "5.png border=0 class=inlineimg>";
    }
    if (60 <= $shareratio && $shareratio < 70) {
        return "<img src=" . $imagepath . "6.png border=0 class=inlineimg>";
    }
    if (70 <= $shareratio && $shareratio < 80) {
        return "<img src=" . $imagepath . "7.png border=0 class=inlineimg>";
    }
    if (80 <= $shareratio && $shareratio < 90) {
        return "<img src=" . $imagepath . "8.png border=0 class=inlineimg>";
    }
    if (90 <= $shareratio && $shareratio < 100) {
        return "<img src=" . $imagepath . "9.png border=0 class=inlineimg>";
    }
    return "<img src=" . $imagepath . "1.png border=0 class=inlineimg>";
}

?>