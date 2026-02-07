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
if ($xbt_active == "yes") {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE `torrents` SET $flags = 1 WHERE $name = \"\"");
    $CQueryCount++;
}

?>