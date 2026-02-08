<?php
if (!defined("IN_CRON")) {
    exit;
}
$TSSEConfig->TSLoadConfig(["MAIN", "HITRUN"]);
if ($xbt_active != "yes" && $Enabled == "yes" && (0 < $MinSeedTime || 0 < $MinRatio)) {
    $Queries = [];
    $Queries[] = "s.$warned = '0'";
    $Queries[] = "s.$finished = 'yes'";
    $Queries[] = "s.$seeder = 'no'";
    $Queries[] = "t.$banned = 'no'";
    $Queries[] = "u.$enabled = 'yes'";
    if (isset($CanWait) && 0 < intval($CanWait)) {
        $Queries[] = "UNIX_TIMESTAMP(s.last_action) < " . (TIMENOW - $CanWait * 60 * 60) . "";
    }
    if ($Categories) {
        $Queries[] = "t.category IN (" . $Categories . ")";
    }
    if ($HRSkipUsergroups) {
        $Queries[] = "u.usergroup NOT IN (" . $HRSkipUsergroups . ")";
    }
    if (0 < $MinFinishDate) {
        $Queries[] = "UNIX_TIMESTAMP(s.completedat) > " . $MinFinishDate;
    }
    if (0 < $MinSeedTime) {
        $Queries[] = "s.seedtime < " . $MinSeedTime * 60 * 60;
    }
    if (0 < $MinRatio) {
        $Queries[] = "s.uploaded / s.downloaded < " . $MinRatio;
    }
    $WarnUsers = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT s.id, s.torrentid, s.userid, s.seedtime, t.name, u.username FROM snatched s INNER JOIN torrents t ON (s.`torrentid` = t.id) INNER JOIN users u ON (s.`userid` = u.id) WHERE " . implode(" AND ", $Queries));
    $CQueryCount++;
    if (mysqli_num_rows($query)) {
        require_once INC_PATH . "/functions_pm.php";
        while ($HR = mysqli_fetch_assoc($query)) {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE snatched SET $warned = 1 WHERE `id` = " . sqlesc($HR["id"]));
            send_pm($HR["userid"], sprintf($lang->cronjobs["hr_warn_message"], $HR["username"], "[URL=" . $BASEURL . "/details.php?$id = " . $HR["torrentid"] . "]" . htmlspecialchars($HR["name"]) . "[/URL]", 0 < $HR["seedtime"] ? floor($HR["seedtime"] / 3600) : 0, $MinSeedTime, $MinRatio, "[URL=" . $BASEURL . "/download.php?$id = " . $HR["torrentid"] . "]" . htmlspecialchars($HR["name"]) . "[/URL]"), $lang->cronjobs["hr_warn_subject"]);
            $CQueryCount++;
            if (!in_array($HR["userid"], $WarnUsers)) {
                $WarnUsers[] = $HR["userid"];
            }
        }
        if (count($WarnUsers)) {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `timeswarned` = timeswarned + 1 WHERE id IN (" . implode(",", $WarnUsers) . ")");
            $CQueryCount++;
        }
    }
    unset($WarnUsers);
    unset($Queries);
}

?>