<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "takeflush.php");
require "./global.php";
define("TF_VERSION", "0.3.3 by xam");
$id = isset($_GET["id"]) ? intval($_GET["id"]) : intval($CURUSER["id"]);
int_check($id);
$lang->load("takeflush");
$TSSEConfig->TSLoadConfig("ANNOUNCE");
if (($is_mod || $CURUSER["id"] == $id) && isset($CURUSER) && 0 < $CURUSER["id"]) {
    $deadtime = deadtime();
    if ($xbt_active == "yes") {
        sql_query("UPDATE xbt_files_users SET `active` = 0 WHERE `mtime` < " . $deadtime . " AND `active` = 1");
    } else {
        sql_query("DELETE FROM peers WHERE UNIX_TIMESTAMP(last_action) < " . $deadtime . " AND userid=" . sqlesc($id));
    }
    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        stderr($lang->takeflush["done"], $lang->takeflush["done2"]);
    } else {
        stderr($lang->global["error"], $lang->takeflush["noghost"]);
    }
} else {
    print_no_permission();
}
function deadtime()
{
    global $announce_interval;
    return TIMENOW - floor($announce_interval * 0);
}

?>