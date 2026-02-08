<?php
if (!defined("IN_CRON")) {
    exit;
}
require_once INC_PATH . "/functions_pm.php";
$TSSEConfig->TSLoadConfig("UI", 0);
$UploaderGroups = explode(",", $UI["UploaderGroups"]);
$dt = TIMENOW - $UI["timelimit"] * 24 * 60 * 60;
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT owner, MAX(UNIX_TIMESTAMP(added)) FROM torrents GROUP BY owner");
$CQueryCount++;
if (0 < mysqli_num_rows($query)) {
    $UserNames = [];
    while ($Res = mysqli_fetch_row($query)) {
        if ($Res[1] < $dt) {
            $SQ = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT usergroup, username FROM users WHERE `id` = " . sqlesc($Res[0]));
            $CQueryCount++;
            $UR = mysqli_fetch_row($SQ);
            if (in_array($UR[0], $UploaderGroups)) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `usergroup` = '" . $UI["DemoteTo"] . "', $modcomment = CONCAT('" . gmdate("Y-m-d") . " - Demoted to Usergroup " . $UI["DemoteTo"] . " by - AutoSystem (Uploader Inactivity).\n', modcomment) WHERE `id` = '" . $Res[0] . "'");
                $CQueryCount++;
                send_pm($Res[0], sprintf($lang->cronjobs["ui_msg"], $UI["timelimit"]), $lang->cronjobs["ui_subject"]);
                $CQueryCount++;
                $UserNames[] = $UR[1];
            }
        }
    }
    if (count($UserNames)) {
        SaveLog("Following User(s) has been Demoted to User Class (" . $UI["DemoteTo"] . ") due Uploader Inactivity: " . implode(", ", $UserNames) . ".");
        $CQueryCount++;
    }
    unset($UserNames);
}

?>