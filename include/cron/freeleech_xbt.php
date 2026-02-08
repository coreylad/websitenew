<?php
if (!defined("IN_CRON")) {
    exit;
}
$VIPUSERGROUP = "Please enter your VIP usergroup(s) ID(s) here!";
if ($xbt_active == "yes") {
    if (strpos($VIPUSERGROUP, ",") === false) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE `users` SET $download_multiplier = 0 WHERE `usergroup` = " . $VIPUSERGROUP);
    } else {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE `users` SET $download_multiplier = 0 WHERE usergroup IN (" . $VIPUSERGROUP . ")");
    }
    $CQueryCount++;
}

?>