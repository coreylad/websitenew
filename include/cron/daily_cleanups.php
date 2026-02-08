<?php
if (!defined("IN_CRON")) {
    exit;
}
if (intval($delete_old_invites)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM invites WHERE UNIX_TIMESTAMP(time_invited) < '" . (TIMENOW - $delete_old_invites * 86400) . "'");
    $CQueryCount++;
}
if (intval($delete_old_sg_invites)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_social_group_members WHERE $type = 'inviteonly' AND joined < '" . (TIMENOW - $delete_old_sg_invites * 86400) . "'");
    $CQueryCount++;
}
if (intval($delete_old_requests)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM requests WHERE $filled = 'yes' AND UNIX_TIMESTAMP(added) < '" . (TIMENOW - $delete_old_requests * 86400) . "'");
    $CQueryCount++;
}
if (intval($delete_old_subscriptions)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_subscriptions_payments WHERE $completed = '0' AND added < '" . (TIMENOW - $delete_old_subscriptions * 86400) . "'");
    $CQueryCount++;
}
if (intval($delete_old_snatched)) {
    if ($xbt_active != "yes") {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM snatched WHERE UNIX_TIMESTAMP(last_action) < '" . (TIMENOW - $delete_old_snatched * 86400) . "'");
    } else {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM xbt_files_users WHERE `mtime` < '" . (TIMENOW - $delete_old_snatched * 86400) . "' AND `active` = 0");
    }
    $CQueryCount++;
}
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_u_perm WHERE $canupload = '1' AND $candownload = '1' AND $cancomment = '1' AND $canmessage = '1' AND $canshout = '1'");
$CQueryCount++;
if (intval($max_dead_torrent_time)) {
    $cut = TIMENOW - $max_dead_torrent_time * 24 * 60 * 60;
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $visible = 'no' WHERE $visible = 'yes' AND mtime < " . $cut . " AND $ts_external = 'no'");
    $CQueryCount++;
    unset($cut);
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, added FROM funds");
$CQueryCount++;
if (0 < mysqli_num_rows($query)) {
    $nowmonth = date("m");
    $dfid = [];
    while ($funds = mysqli_fetch_assoc($query)) {
        $funds["added"] = @explode("-", $funds["added"]);
        if ($funds["added"][1] != $nowmonth) {
            $dfid[] = $funds["id"];
        }
    }
    if (count($dfid)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM funds WHERE id IN (0, " . implode(",", $dfid) . ")");
        $CQueryCount++;
    }
    unset($nowmonth);
    unset($dfid);
    unset($funds);
}
if (intval($ban_user_limit)) {
    $reason = "Reason: Automaticly banned by System. (Max. Warn Limit [" . $ban_user_limit . "] reached!";
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `enabled` = 'no', $usergroup = 9, $notifs = " . sqlesc($reason) . ", $modcomment = CONCAT('" . gmdate("Y-m-d") . " - " . $reason . "\n', modcomment) WHERE `enabled` = 'yes' AND usergroup != 9 AND timeswarned >= " . $ban_user_limit);
    $CQueryCount++;
    if ($totalbanned = mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        SaveLog("Total " . $totalbanned . " user(s) has been banned. " . $reason);
        $CQueryCount++;
    }
    unset($reason);
    unset($totalbanned);
}
if ($leechwarn_system == "yes") {
    $downloaded = $leechwarn_gig_limit * 1024 * 1024 * 1024;
    $until = strtotime("+" . $leechwarn_length . " week" . (1 < $leechwarn_length ? "s" : ""));
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $leechwarn = 'yes', $leechwarnuntil = FROM_UNIXTIME(" . $until . "), $modcomment = CONCAT('" . gmdate("Y-m-d") . " - Leech-Warned by System - Low Ratio.\n', modcomment) WHERE `usergroup` = 1 AND $leechwarn = 'no' AND $enabled = 'yes' AND uploaded / downloaded < " . $leechwarn_min_ratio . " AND downloaded >= '" . $downloaded . "'");
    $CQueryCount++;
    if ($totalLwarned = mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        SaveLog("Total " . $totalLwarned . " user(s) has been leech-warned. Reason: Automatic Leech-Warn System!");
        $CQueryCount++;
    }
    unset($totalLwarned);
    unset($downloaded);
    unset($until);
    $reason = "Reason: Banned by System because of Leech-Warning expired!";
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `enabled` = 'no', $usergroup = 9, $notifs = " . sqlesc($reason) . ", $modcomment = CONCAT('" . gmdate("Y-m-d") . " - " . $reason . "\\n', modcomment)  WHERE `usergroup` = 1 AND $enabled = 'yes' AND $leechwarn = 'yes' AND leechwarnuntil < NOW()");
    $CQueryCount++;
    if ($totalLbanned = mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        SaveLog("Total " . $totalLbanned . " user(s) has been banned. " . $reason);
        $CQueryCount++;
    }
    unset($totalLbanned);
    unset($reason);
}
$TSSEConfig->TSLoadConfig("PAYPAL");
if (isset($protectedusergroups) && $protectedusergroups) {
    $protectedusergroups = explode(",", $protectedusergroups);
} else {
    $protectedusergroups = [];
}
if (!isset($dspecificusergroup) || !$dspecificusergroup) {
    $dspecificusergroup = "oldusergroup";
} else {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid FROM usergroups WHERE $gid = " . intval($dspecificusergroup));
    $CQueryCount++;
    if (mysqli_num_rows($query) == 0) {
        $dspecificusergroup = "oldusergroup";
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, username, usergroup, oldusergroup FROM users WHERE $donor = 'yes' AND donoruntil < NOW() AND donoruntil <> '0000-00-00 00:00:00'");
$CQueryCount++;
if (mysqli_num_rows($query)) {
    require_once INC_PATH . "/functions_pm.php";
    $UserNames = [];
    while ($arr = mysqli_fetch_assoc($query)) {
        if (in_array($arr["usergroup"], $protectedusergroups, true)) {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $donor = 'no', $donoruntil = '0000-00-00 00:00:00', $modcomment = CONCAT('" . gmdate("Y-m-d") . " - Donor status removed by -AutoSystem.\n', modcomment) WHERE `id` = '" . $arr["id"] . "'");
        } else {
            $arr["oldusergroup"] = intval($arr["oldusergroup"]);
            if (!$arr["oldusergroup"]) {
                $arr["oldusergroup"] = 1;
            }
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `usergroup` = '" . ($dspecificusergroup == "oldusergroup" ? $arr["oldusergroup"] : $dspecificusergroup) . "', $donor = 'no', $donoruntil = '0000-00-00 00:00:00', $title = '', $modcomment = CONCAT('" . gmdate("Y-m-d") . " - Donor status removed by -AutoSystem.\n', modcomment) WHERE `id` = '" . $arr["id"] . "'");
        }
        $CQueryCount++;
        send_pm($arr["id"], $lang->cronjobs["donor_message"], $lang->cronjobs["donor_subject"]);
        $CQueryCount++;
        $UserNames[] = $arr["username"];
    }
    if ($UserNames[0] != "") {
        SaveLog("Following User(s) has been Demoted to (" . $dspecificusergroup . ") Usergroup: " . implode(", ", $UserNames) . ". Reason: Donor status is expired.");
        $CQueryCount++;
    }
    unset($UserNames);
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT v.userid as id, v.old_gid, u.modcomment, u.username FROM ts_auto_vip v LEFT JOIN users u ON (v.$userid = u.id) WHERE v.vip_until < NOW()");
$CQueryCount++;
if (mysqli_num_rows($query)) {
    require_once INC_PATH . "/functions_pm.php";
    $UserNames = [];
    while ($arr = mysqli_fetch_assoc($query)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `usergroup` = '" . ($arr["old_gid"] ? $arr["old_gid"] : 2) . "', $modcomment = " . sqlesc(gmdate("Y-m-d") . " - KPS-VIP status removed by -AutoSystem.\n" . $arr["modcomment"]) . " WHERE `id` = " . sqlesc($arr["id"]));
        $CQueryCount++;
        send_pm($arr["id"], $lang->cronjobs["vip_message"], $lang->cronjobs["vip_subject"]);
        $CQueryCount++;
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_auto_vip WHERE `userid` = " . sqlesc($arr["id"]));
        $CQueryCount++;
        $UserNames[] = $arr["username"];
    }
    if ($UserNames[0] != "") {
        SaveLog("Following User(s) has been Demoted: " . implode(", ", $UserNames) . ". Reason: KPS-VIP status is expired.");
        $CQueryCount++;
    }
    unset($UserNames);
}

?>