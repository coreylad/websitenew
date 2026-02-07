<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("THIS_SCRIPT", "fb-shoutbox-members-in-chat.php");
require "./global.php";
define("FB_MEMBERS_IN_CHAT", "1.0 by xam");
$TSSEConfig->TSLoadConfig("SHOUTBOX");
require_once INC_PATH . "/functions_icons.php";
$_dt = TIMENOW - $GLOBALS["S_REFRESHTIME"] * 2;
$_wgo_query = sql_query("SELECT distinct s.userid as id, u.username, u.options, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle FROM ts_sessions s LEFT JOIN users u ON (s.`userid` = u.id) LEFT JOIN ts_u_perm p ON (u.`id` = p.userid) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE s.lastactivity > '" . $_dt . "' ORDER by u.last_access DESC");
$Output = [];
while ($User = mysqli_fetch_assoc($_wgo_query)) {
    if ($User["id"]) {
        if (!(TS_Match($User["options"], "B1") && $User["id"] != $CURUSER["id"]) || $is_mod) {
            $Output[] = "<span><a $href = \"" . ts_seo($User["id"], $User["username"]) . "\">" . get_user_color($User["username"], $User["namestyle"]) . "</a>" . (TS_Match($User["options"], "B1") ? "+" : "") . get_user_icons($User) . "</span>";
        }
    }
}
echo implode(", ", $Output);

?>