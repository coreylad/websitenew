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
function get_user_ratio($uploaded, $downloaded, $white = false)
{
    if (0 < $downloaded) {
        $ratio = $uploaded / $downloaded;
        $ratio = number_format($ratio, 2);
    } else {
        if (0 < $uploaded) {
            $ratio = "Inf.";
        } else {
            $ratio = "--";
        }
    }
    return $ratio;
}
function get_ratio_color($ratio)
{
    return "";
}

?>