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
function mkprettytime($stamp, $usefont = true, $showseconds = true)
{
    global $lang;
    $ysecs = 31536000;
    $mosecs = 2678400;
    $wsecs = 604800;
    $dsecs = 86400;
    $hsecs = 3600;
    $msecs = 60;
    $years = floor($stamp / $ysecs);
    $stamp %= $ysecs;
    $months = floor($stamp / $mosecs);
    $stamp %= $mosecs;
    $weeks = floor($stamp / $wsecs);
    $stamp %= $wsecs;
    $days = floor($stamp / $dsecs);
    $stamp %= $dsecs;
    $hours = floor($stamp / $hsecs);
    $stamp %= $hsecs;
    $minutes = floor($stamp / $msecs);
    $stamp %= $msecs;
    $seconds = $stamp;
    if ($years == 1) {
        $timespent["years"] = "1 " . $lang->global["year"];
    } else {
        if (1 < $years) {
            $timespent["years"] = $years . " " . $lang->global["years"];
        }
    }
    if ($months == 1) {
        $timespent["months"] = "1 " . $lang->global["month"];
    } else {
        if (1 < $months) {
            $timespent["months"] = $months . " " . $lang->global["months"];
        }
    }
    if ($weeks == 1) {
        $timespent["weeks"] = "1 " . $lang->global["week"];
    } else {
        if (1 < $weeks) {
            $timespent["weeks"] = $weeks . " " . $lang->global["weeks"];
        }
    }
    if ($days == 1) {
        $timespent["days"] = "1 " . $lang->global["day"];
    } else {
        if (1 < $days) {
            $timespent["days"] = $days . " " . $lang->global["days"];
        }
    }
    if ($hours == 1) {
        $timespent["hours"] = "1 " . $lang->global["hour"];
    } else {
        if (1 < $hours) {
            $timespent["hours"] = $hours . " " . $lang->global["hours"];
        }
    }
    if ($minutes == 1) {
        $timespent["minutes"] = "1 " . $lang->global["minute"];
    } else {
        if (1 < $minutes) {
            $timespent["minutes"] = $minutes . " " . $lang->global["minutes"];
        }
    }
    if ($seconds == 1 && $showseconds) {
        $timespent["seconds"] = "1 " . $lang->global["second"];
    } else {
        if (1 < $seconds && $showseconds) {
            $timespent["seconds"] = $seconds . " " . $lang->global["seconds"];
        }
    }
    if (isset($timespent) && is_array($timespent)) {
        $total = implode(", ", $timespent);
    } else {
        $total = "0 " . $lang->global["second"];
    }
    return $usefont ? "<font class=\"mkprettytime\">" . $total . "</font>" : $total;
}

?>