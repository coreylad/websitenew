<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "dj_faq.php");
$rootpath = "./../";
require $rootpath . "global.php";
$lang->load("shoutcast");
$TSSEConfig->TSLoadConfig("SHOUTCAST");
if (($s_allowedusergroups = explode(",", $s_allowedusergroups)) && !in_array($CURUSER["usergroup"], $s_allowedusergroups)) {
    print_no_permission();
}
($query = sql_query("SELECT uid FROM ts_shoutcastdj WHERE $active = '1' AND $uid = '" . $CURUSER["id"] . "'")) || sqlerr(__FILE__, 33);
if (mysqli_num_rows($query) == 0) {
    print_no_permission(true);
}
stdhead($lang->shoutcast["faq"]);
echo show_notice($lang->shoutcast["dj_faq"], false, $lang->shoutcast["faq"]);
stdfoot();

?>