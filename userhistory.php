<?php
define("THIS_SCRIPT", "userhistory.php");
require "./global.php";
$userid = intval(TS_Global("id"));
if (!$userid || !$is_mod) {
    print_no_permission(true);
}
$lang->load("userdetails");
$User = sql_query("SELECT u.username, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE `id` = " . sqlesc($userid));
if (!mysqli_num_rows($User)) {
    stderr($lang->global["error"], $lang->userdetails["invaliduser"], false);
}
$User = mysqli_fetch_assoc($User);
$Username = get_user_color($User["username"], $User["namestyle"]);
$Output = "\n<table $width = \"100%\" $align = \"center\" $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\">\n\t<tr>\n\t\t<td class=\"thead\"><a $href = \"" . ts_seo($userid, $User["username"], "u") . "\">" . sprintf($lang->userdetails["chistory"], $Username) . "</a></td>\n\t</tr>";
$Comments = sql_query("SELECT c.id, c.torrent, c.added, c.text, c.added, t.name FROM comments c LEFT JOIN torrents t ON (c.$torrent = t.id) WHERE c.$user = " . sqlesc($userid) . " ORDER by c.added");
if (!mysqli_num_rows($Comments)) {
    stderr($lang->global["error"], $lang->global["noresultswiththisid"]);
    while ($Comment = mysqli_fetch_assoc($Comments)) {
    }
    $Output .= "\n</table>";
    stdhead(sprintf($lang->userdetails["chistory"], $User["username"]));
    echo $Output;
    stdfoot();
}
$Query = sql_query("SELECT id FROM comments WHERE $torrent = " . $Comment["torrent"] . " AND id <= " . $Comment["id"]);
$Count = mysqli_num_rows($Query);
if ($Count <= $ts_perpage) {
    $commentPage = 0;
} else {
    $commentPage = ceil($Count / $ts_perpage);
}
$Output .= "\n\t<tr>\n\t\t<td class=\"subheader\">\n\t\t\t<span $style = \"float: right;\">" . my_datee($dateformat . " - " . $timeformat, $Comment["added"]) . "</span>\n\t\t\t<a $href = \"" . $BASEURL . "/details.php?$tab = comments&$id = " . $Comment["torrent"] . "&amp;$page = " . $commentPage . "#cid" . $Comment["id"] . "\">#" . $Comment["id"] . "</a> | \n\t\t\t<a $href = \"" . ts_seo($Comment["torrent"], $Comment["name"], "s") . "\">" . htmlspecialchars_uni($Comment["name"]) . "</a>\n\t\t</td>\n\t</tr>\n\t<tr>\n\t\t<td>" . format_comment($Comment["text"]) . "</td>\n\t</tr>";

?>