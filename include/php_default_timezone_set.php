<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (ini_get("date.timezone") == "" && function_exists("date_default_timezone_set")) {
    @date_default_timezone_set(@date_default_timezone_get());
}

?>