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
if ($xbt_active == "yes" && 0 < $kpsseed && ($bonus == "enable" || $bonus == "disablesave")) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uid FROM xbt_files_users WHERE active = '1' AND `left` = '0'");
    $CQueryCount++;
    if (mysqli_num_rows($Query)) {
        $UsersEarnedPoints = [];
        while ($ActivePeers = mysqli_fetch_assoc($Query)) {
            $UsersEarnedPoints[] = 0 + $ActivePeers["uid"];
        }
        if (count($UsersEarnedPoints)) {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET seedbonus = seedbonus + " . $kpsseed . " WHERE id IN (" . implode(",", $UsersEarnedPoints) . ")");
            $CQueryCount++;
        }
    }
}
if ($xbt_active == "yes") {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM  `torrents` WHERE category =  '0'");
    $CQueryCount++;
}
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_captcha WHERE added < '" . (TIMENOW - 300) . "'");
$CQueryCount++;
if (intval($delete_old_login_attempts)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM loginattempts WHERE banned='no' AND UNIX_TIMESTAMP(added) < '" . (TIMENOW - $delete_old_login_attempts * 86400) . "'");
    $CQueryCount++;
}
if (intval($delete_old_unconfirmed_users)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM users WHERE status = 'pending' AND UNIX_TIMESTAMP(added) < '" . (TIMENOW - $delete_old_unconfirmed_users * 86400) . "'");
    $CQueryCount++;
}
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_sessions WHERE lastactivity < '" . (TIMENOW - 86400) . "'");
$CQueryCount++;
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "threadsread WHERE dateline < '" . (TIMENOW - 604800) . "'");
$CQueryCount++;
mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET warned = 'no', timeswarned = IF(timeswarned > 0, timeswarned - 1, 0), warneduntil = '0000-00-00 00:00:00', modcomment = CONCAT('" . gmdate("Y-m-d") . " - Warning removed by System.\n', modcomment) WHERE warned='yes' AND warneduntil < NOW() AND enabled='yes'");
$CQueryCount++;
mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET leechwarn = 'no', leechwarnuntil = '0000-00-00 00:00:00', modcomment = CONCAT('" . gmdate("Y-m-d") . " - Leech-Warning removed by System.\n', modcomment) WHERE leechwarn = 'yes' AND uploaded / downloaded >= '" . $leechwarn_remove_ratio . "' AND enabled='yes'");
$CQueryCount++;

?>