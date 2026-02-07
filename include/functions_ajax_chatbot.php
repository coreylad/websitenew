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
function TSAjaxShoutBOT($text)
{
    return sql_query("INSERT INTO ts_shoutbox (date, shout, notice) VALUES ('" . TIMENOW . "', " . sqlesc($text) . ", '1')");
}

?>