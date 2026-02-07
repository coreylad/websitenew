<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "delete.php");
require "./global.php";
if (isset($CURUSER) && $CURUSER["id"] == 0) {
    print_no_permission();
}
define("D_VERSION", "0.9 by xam");
$TSSEConfig->TSLoadConfig("KPS");
$lang->load("delete");
$id = intval(TS_Global("id"));
int_check($id);
$res = sql_query("SELECT name,owner,moderate FROM torrents WHERE `id` = " . sqlesc($id));
$row = mysqli_fetch_assoc($res);
if (!$row) {
    stderr($lang->global["error"], $lang->global["notorrentid"]);
}
if ($is_mod || isset($CURUSER) && 0 < $CURUSER["id"] && $usergroups["candeletetorrent"] == "yes" && $CURUSER["id"] == $row["owner"]) {
    $rt = intval(TS_Global("reasontype"));
    if (!is_int($rt) || $rt < 1 || 5 < $rt) {
        stderr($lang->global["error"], sprintf($lang->delete["invalidreason"], $rt));
    }
    $r = TS_Global("r");
    $reason = TS_Global("reason");
    if ($rt == 1) {
        $reasonstr = $lang->delete["reasonstr1"];
    } else {
        if ($rt == 2) {
            $reasonstr = $lang->delete["reasonstr2"] . ($reason[0] ? ": " . trim($reason[0]) : "!");
        } else {
            if ($rt == 3) {
                $reasonstr = $lang->delete["reasonstr3"] . ($reason[1] ? ": " . trim($reason[1]) : "!");
            } else {
                if ($rt == 4) {
                    if (!$reason[2]) {
                        stderr($lang->global["error"], $lang->delete["violaterule"]);
                    }
                    $reasonstr = sprintf($lang->delete["reasonstr4"], $SITENAME) . trim($reason[2]);
                } else {
                    if (!$reason[3]) {
                        stderr($lang->global["error"], $lang->delete["enterreason"]);
                    }
                    $reasonstr = trim($reason[3]);
                }
            }
        }
    }
    require_once INC_PATH . "/functions_deletetorrent.php";
    deletetorrent($id, true);
    if ((TS_Match($CURUSER["options"], "I3") || TS_Match($CURUSER["options"], "I4")) && $is_mod) {
        write_log(sprintf($lang->delete["logmsg1"], $id, $row["name"], htmlspecialchars($reasonstr)));
    } else {
        write_log(sprintf($lang->delete["logmsg2"], $id, $row["name"], $CURUSER["username"], htmlspecialchars($reasonstr)));
    }
    if ($row["owner"] != $CURUSER["id"]) {
        require_once INC_PATH . "/functions_pm.php";
        send_pm($row["owner"], sprintf($lang->delete["logmsg2"], $id, $row["name"], $CURUSER["username"], htmlspecialchars($reasonstr)), $lang->delete["deleted"]);
    }
    if ($row["moderate"] == "0") {
        KPS("-", $kpsupload, $row["owner"]);
    }
    if (file_exists(TSDIR . "/" . $cache . "/latesttorrents.html")) {
        @unlink(TSDIR . "/" . $cache . "/latesttorrents.html");
    }
    redirect("browse.php", $lang->delete["deleted"]);
    exit;
}
print_no_permission(true);

?>