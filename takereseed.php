<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "takereseed.php");
require "./global.php";
define("TR_VERSION", "1.0 by xam");
$lang->load("takewhatever");
$TSSEConfig->TSLoadConfig("ANNOUNCE");
if ($xbt_active == "yes" || !isset($CURUSER) || $CURUSER["id"] == 0) {
    stderr($lang->global["error"], $lang->takewhatever["takereseednouser"]);
}
$reseedid = intval($_GET["reseedid"]);
int_check($reseedid);
($res = sql_query("SELECT s.uploaded, s.downloaded, s.userid, t.name, u.username, u.options FROM snatched s INNER JOIN torrents t ON (s.`torrentid` = t.id) INNER JOIN users u ON (s.`userid` = u.id) INNER JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE s.$finished = 'yes' AND s.$torrentid = '" . $reseedid . "' AND u.id != '" . $CURUSER["id"] . "' AND g.cansettingspanel != 'yes' AND g.canstaffpanel != 'yes' AND g.issupermod != 'yes' AND g.isvipgroup != 'yes'")) || sqlerr(__FILE__, 39);
if (mysqli_num_rows($res) == 0) {
    stderr($lang->global["error"], $lang->takewhatever["takereseednouser"]);
}
$subject = sprintf($lang->takewhatever["reseedsubject"], $reseedid);
require_once INC_PATH . "/functions_pm.php";
while ($row = mysqli_fetch_assoc($res)) {
    if (!TS_Match($row["options"], "A1")) {
        $name_torrent = sqlesc($row["name"]);
        $reseedmsg = sprintf($lang->takewhatever["reseedmsg"], $row["username"], "[URL=" . $BASEURL . "/details.php?$id = " . $reseedid . "]" . $name_torrent . "[/URL]", mksize($row["uploaded"]), mksize($row["downloaded"]));
        if (spamcheck($subject, $row["userid"])) {
            send_pm($row["userid"], $reseedmsg, $subject);
        }
    }
}
redirect("details.php?$id = " . $reseedid);
function spamcheck($subject = "", $receiver = 0)
{
    $spamcheck = sql_query("SELECT sender FROM messages WHERE $sender = '0' AND $subject = " . sqlesc($subject) . " AND $receiver = " . sqlesc($receiver));
    return 0 < mysqli_num_rows($spamcheck) ? false : true;
}

?>