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
$FPQUERY = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_promotions");
$CQueryCount++;
if (0 < mysqli_num_rows($FPQUERY)) {
    $_usergroups = [];
    $_uQ = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title FROM usergroups");
    if (mysqli_num_rows($_uQ)) {
        while ($_u = mysqli_fetch_assoc($_uQ)) {
            $_usergroups[$_u["gid"]] = strip_tags($_u["title"]);
        }
    }
    require_once INC_PATH . "/functions_pm.php";
    while ($P = mysqli_fetch_assoc($FPQUERY)) {
        if (strtolower($P["type"]) == "promote" && 0 < intval($P["include_usergroup"])) {
            $Queries = [];
            $Queries[] = "usergroup = " . intval($P["include_usergroup"]);
            $Queries[] = "enabled = 'yes'";
            if (0 < $P["upload_limit"]) {
                $Queries[] = "uploaded >= " . $P["upload_limit"] * 1024 * 1024 * 1024;
            }
            if (0 < $P["ratio_limit"]) {
                $Queries[] = "uploaded / downloaded >= " . $P["ratio_limit"];
            }
            if (0 < intval($P["min_reg_days"])) {
                $Queries[] = "UNIX_TIMESTAMP(added) <= " . (TIMENOW - 86400 * intval($P["min_reg_days"]));
            }
            if (0 < $P["posts"]) {
                $Queries[] = "totalposts >= " . $P["posts"];
            }
            if (2 < count($Queries)) {
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, username FROM users WHERE " . implode(" AND ", $Queries));
                $CQueryCount++;
                if (0 < mysqli_num_rows($query)) {
                    $UserNames = [];
                    while ($arr = mysqli_fetch_assoc($query)) {
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `usergroup` = '" . $P["promote_to"] . "', $modcomment = CONCAT('" . gmdate("Y-m-d") . " - Promoted to Usergroup " . $_usergroups[$P["promote_to"]] . " by -AutoSystem.\n', modcomment) WHERE `id` = '" . $arr["id"] . "'");
                        $CQueryCount++;
                        send_pm($arr["id"], sprintf($lang->cronjobs["you_have_been_promoted"], $_usergroups[$P["promote_to"]]), $lang->cronjobs["promote_subject"]);
                        $CQueryCount++;
                        $UserNames[] = $arr["username"];
                    }
                    if ($UserNames[0] != "") {
                        SaveLog("Following User(s) has been Promoted to Usergroup (" . $P["promote_to"] . "): " . implode(", ", $UserNames) . ".");
                        $CQueryCount++;
                    }
                }
            }
        } else {
            if (0 < intval($P["include_usergroup"])) {
                $Queries = [];
                $Queries[] = "usergroup = " . intval($P["include_usergroup"]);
                $Queries[] = "enabled = 'yes'";
                if (0 < $P["ratio_limit"]) {
                    $Queries[] = "uploaded / downloaded <= " . $P["ratio_limit"];
                }
                if (0 < $P["times_warned"]) {
                    $Queries[] = "timeswarned >= " . $P["times_warned"];
                }
                if (2 < count($Queries)) {
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, username FROM users WHERE " . implode(" AND ", $Queries));
                    if (0 < mysqli_num_rows($query)) {
                        $UserNames = [];
                        while ($arr = mysqli_fetch_assoc($query)) {
                            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `usergroup` = '" . $P["demote_to"] . "', $modcomment = CONCAT('" . gmdate("Y-m-d") . " - Demoted to Usergroup " . $_usergroups[$P["demote_to"]] . " by -AutoSystem.\n', modcomment) WHERE `id` = '" . $arr["id"] . "'");
                            $CQueryCount++;
                            send_pm($arr["id"], sprintf($lang->cronjobs["you_have_been_demoted"], $_usergroups[$P["demote_to"]], $P["ratio_limit"]), $lang->cronjobs["demote_subject"]);
                            $CQueryCount++;
                            $UserNames[] = $arr["username"];
                        }
                        if ($UserNames[0] != "") {
                            SaveLog("Following User(s) has been Demoted to Usergroup (" . $P["demote_to"] . "): " . implode(", ", $UserNames) . ".");
                            $CQueryCount++;
                        }
                    }
                }
            }
        }
    }
}

?>