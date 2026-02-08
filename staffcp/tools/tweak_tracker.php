<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/tweak_tracker.lang");
$Message = "";
$Users = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE `enabled` = 'yes' AND $status = 'confirmed'");
while ($user = mysqli_fetch_assoc($query)) {
    $Users[] = $user["id"];
}
$Users = "0, " . implode(", ", $Users);
$Torrents = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM torrents");
while ($torrent = mysqli_fetch_assoc($query)) {
    $Torrents[] = $torrent["id"];
}
$Torrents = "0, " . implode(", ", $Torrents);
echo "\r\n<div $id = \"sending\" $name = \"sending\">\r\n\t\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\">" . $Language[4] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">";
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM addedrequests WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("addedrequests");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE addedrequests");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM bookmarks WHERE userid NOT IN (" . $Users . ") OR torrentid NOT IN (" . $Torrents . ")");
displayDatabaseUpdateMessage("bookmarks");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE bookmarks");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM cheat_attempts WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("cheat_attempts");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE cheat_attempts");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM comments WHERE user NOT IN (" . $Users . ") OR torrent NOT IN (" . $Torrents . ")");
displayDatabaseUpdateMessage("comments");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE comments");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM comments_votes WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("comments_votes");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE comments_votes");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM friends WHERE userid NOT IN (" . $Users . ") OR friendid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("friends");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE friends");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM invites WHERE inviter NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("invites");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE invites");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM iplog WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("iplog");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE iplog");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM messages WHERE receiver NOT IN (" . $Users . ") OR (sender NOT IN (" . $Users . ") AND sender != 0)");
displayDatabaseUpdateMessage("messages");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE messages");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM pmboxes WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("pmboxes");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE pmboxes");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM referrals WHERE uid NOT IN (" . $Users . ") OR referring NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("referrals");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE referrals");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM requests WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("requests");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE requests");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM snatched WHERE userid NOT IN (" . $Users . ") OR torrentid NOT IN (" . $Torrents . ")");
displayDatabaseUpdateMessage("snatched");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE snatched");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM staffmessages WHERE sender NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("staffmessages");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE staffmessages");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM tsf_pollvote WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("tsf_pollvote");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE tsf_pollvote");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM tsf_subscribe WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("tsf_subscribe");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE tsf_subscribe");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM tsf_thanks WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("tsf_thanks");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE tsf_thanks");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM tsf_threadrate WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("tsf_threadrate");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE tsf_threadrate");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM tsf_threadsread WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("tsf_threadsread");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE tsf_threadsread");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_albums WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_albums");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_albums");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_album_comments WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_album_comments");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_album_comments");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_album_images WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_album_images");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_album_images");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_application_requests WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_application_requests");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_application_requests");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_awards_users WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_awards_users");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_awards_users");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_blogs WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_blogs");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_blogs");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_blogs_comments WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_blogs_comments");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_blogs_comments");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_blogs_subscribe WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_blogs_subscribe");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_blogs_subscribe");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_hit_and_run WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_hit_and_run");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_blogs_subscribe");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_inactivity WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_inactivity");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_inactivity");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_lottery_tickets WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_lottery_tickets");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_lottery_tickets");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_nfo WHERE id NOT IN (" . $Torrents . ")");
displayDatabaseUpdateMessage("ts_nfo");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_nfo");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_profilevisitor WHERE userid NOT IN (" . $Users . ") OR visitorid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_profilevisitor");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_profilevisitor");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_ratings WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_ratings");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_ratings");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_reports WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_reports");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_reports");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_secret_questions WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_secret_questions");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_secret_questions");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_seedboxes WHERE sb_userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_seedboxes");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_seedboxes");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_shoutbox WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_shoutbox");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_shoutbox");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_shoutcastdj WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_shoutcastdj");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_shoutcastdj");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_social_group_members WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_social_group_members");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_social_group_members");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_social_group_messages WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_social_group_messages");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_social_group_messages");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_social_group_reports WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_social_group_reports");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_social_group_reports");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_social_groups_subscribe WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_social_groups_subscribe");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_social_groups_subscribe");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_social_groups WHERE owner NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_social_groups");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_social_groups");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_thanks WHERE uid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_thanks");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_thanks");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_torrents_details WHERE tid NOT IN (" . $Torrents . ")");
displayDatabaseUpdateMessage("ts_torrents_details");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_torrents_details");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_user_validation WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_user_validation");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_user_validation");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_u_perm WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_u_perm");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_u_perm");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_visitor_messages WHERE userid NOT IN (" . $Users . ") OR visitorid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_visitor_messages");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_visitor_messages");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_watch_list WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_watch_list");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_watch_list");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_games_champions WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_games_champions");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_games_champions");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_games_comments WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_games_comments");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_games_comments");
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_games_scores WHERE userid NOT IN (" . $Users . ")");
displayDatabaseUpdateMessage("ts_games_scores");
mysqli_query($GLOBALS["DatabaseConnect"], "OPTIMIZE TABLE ts_games_scores");
echo "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n</div>";
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function displayDatabaseUpdateMessage($Msg)
{
    global $Language;
    echo "\r\n\t<table>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t" . str_replace("{1}", $Msg, $Language[2]) . "\r\n\t\t\t</td>\r\n\t\t\t<td>\r\n\t\t\t\t" . str_replace("{1}", number_format(mysqli_affected_rows($GLOBALS["DatabaseConnect"])), $Language[3]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
}

?>