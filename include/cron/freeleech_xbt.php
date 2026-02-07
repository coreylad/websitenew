<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_CRON")) {
    exit;
}
$VIPUSERGROUP = "Please enter your VIP usergroup(s) ID(s) here!";
if ($xbt_active == "yes") {
    if (strpos($VIPUSERGROUP, ",") === false) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE `users` SET $download_multiplier = 0 WHERE $usergroup = " . $VIPUSERGROUP);
    } else {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE `users` SET $download_multiplier = 0 WHERE usergroup IN (" . $VIPUSERGROUP . ")");
    }
    $CQueryCount++;
}

?>