<?php
declare(strict_types=1);

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/tweak_tracker.lang");
$Message = "";

try {
    $db = $GLOBALS["DatabaseConnect"];
    $Users = [];
    
    $stmt = $db->prepare("SELECT id FROM users WHERE enabled = ? AND status = ?");
    $enabled = 'yes';
    $confirmed = 'confirmed';
    $stmt->bind_param('ss', $enabled, $confirmed);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($user = $result->fetch_assoc()) {
        $Users[] = (int)$user["id"];
    }
    $stmt->close();
    
    $Users = "0, " . implode(", ", $Users);
    
    $Torrents = [];
    $stmt = $db->prepare("SELECT id FROM torrents");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($torrent = $result->fetch_assoc()) {
        $Torrents[] = (int)$torrent["id"];
    }
    $stmt->close();
    
    $Torrents = "0, " . implode(", ", $Torrents);
    
    echo "\r\n<div id=\"sending\" name=\"sending\">\r\n\t\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\">" . htmlspecialchars($Language[4], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">";
    
    $db->query("DELETE FROM addedrequests WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("addedrequests");
    $db->query("OPTIMIZE TABLE addedrequests");
    
    $db->query("DELETE FROM bookmarks WHERE userid NOT IN (" . $Users . ") OR torrentid NOT IN (" . $Torrents . ")");
    displayDatabaseUpdateMessage("bookmarks");
    $db->query("OPTIMIZE TABLE bookmarks");
    
    $db->query("DELETE FROM cheat_attempts WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("cheat_attempts");
    $db->query("OPTIMIZE TABLE cheat_attempts");
    
    $db->query("DELETE FROM comments WHERE user NOT IN (" . $Users . ") OR torrent NOT IN (" . $Torrents . ")");
    displayDatabaseUpdateMessage("comments");
    $db->query("OPTIMIZE TABLE comments");
    
    $db->query("DELETE FROM comments_votes WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("comments_votes");
    $db->query("OPTIMIZE TABLE comments_votes");
    
    $db->query("DELETE FROM friends WHERE userid NOT IN (" . $Users . ") OR friendid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("friends");
    $db->query("OPTIMIZE TABLE friends");
    
    $db->query("DELETE FROM invites WHERE inviter NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("invites");
    $db->query("OPTIMIZE TABLE invites");
    
    $db->query("DELETE FROM iplog WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("iplog");
    $db->query("OPTIMIZE TABLE iplog");
    
    $db->query("DELETE FROM messages WHERE receiver NOT IN (" . $Users . ") OR (sender NOT IN (" . $Users . ") AND sender != 0)");
    displayDatabaseUpdateMessage("messages");
    $db->query("OPTIMIZE TABLE messages");
    
    $db->query("DELETE FROM pmboxes WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("pmboxes");
    $db->query("OPTIMIZE TABLE pmboxes");
    
    $db->query("DELETE FROM referrals WHERE uid NOT IN (" . $Users . ") OR referring NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("referrals");
    $db->query("OPTIMIZE TABLE referrals");
    
    $db->query("DELETE FROM requests WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("requests");
    $db->query("OPTIMIZE TABLE requests");
    
    $db->query("DELETE FROM snatched WHERE userid NOT IN (" . $Users . ") OR torrentid NOT IN (" . $Torrents . ")");
    displayDatabaseUpdateMessage("snatched");
    $db->query("OPTIMIZE TABLE snatched");
    
    $db->query("DELETE FROM staffmessages WHERE sender NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("staffmessages");
    $db->query("OPTIMIZE TABLE staffmessages");
    
    $db->query("DELETE FROM tsf_pollvote WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("tsf_pollvote");
    $db->query("OPTIMIZE TABLE tsf_pollvote");
    
    $db->query("DELETE FROM tsf_subscribe WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("tsf_subscribe");
    $db->query("OPTIMIZE TABLE tsf_subscribe");
    
    $db->query("DELETE FROM tsf_thanks WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("tsf_thanks");
    $db->query("OPTIMIZE TABLE tsf_thanks");
    
    $db->query("DELETE FROM tsf_threadrate WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("tsf_threadrate");
    $db->query("OPTIMIZE TABLE tsf_threadrate");
    
    $db->query("DELETE FROM tsf_threadsread WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("tsf_threadsread");
    $db->query("OPTIMIZE TABLE tsf_threadsread");
    
    $db->query("DELETE FROM ts_albums WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_albums");
    $db->query("OPTIMIZE TABLE ts_albums");
    
    $db->query("DELETE FROM ts_album_comments WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_album_comments");
    $db->query("OPTIMIZE TABLE ts_album_comments");
    
    $db->query("DELETE FROM ts_album_images WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_album_images");
    $db->query("OPTIMIZE TABLE ts_album_images");
    
    $db->query("DELETE FROM ts_application_requests WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_application_requests");
    $db->query("OPTIMIZE TABLE ts_application_requests");
    
    $db->query("DELETE FROM ts_awards_users WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_awards_users");
    $db->query("OPTIMIZE TABLE ts_awards_users");
    
    $db->query("DELETE FROM ts_blogs WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_blogs");
    $db->query("OPTIMIZE TABLE ts_blogs");
    
    $db->query("DELETE FROM ts_blogs_comments WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_blogs_comments");
    $db->query("OPTIMIZE TABLE ts_blogs_comments");
    
    $db->query("DELETE FROM ts_blogs_subscribe WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_blogs_subscribe");
    $db->query("OPTIMIZE TABLE ts_blogs_subscribe");
    
    $db->query("DELETE FROM ts_hit_and_run WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_hit_and_run");
    $db->query("OPTIMIZE TABLE ts_hit_and_run");
    
    $db->query("DELETE FROM ts_inactivity WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_inactivity");
    $db->query("OPTIMIZE TABLE ts_inactivity");
    
    $db->query("DELETE FROM ts_lottery_tickets WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_lottery_tickets");
    $db->query("OPTIMIZE TABLE ts_lottery_tickets");
    
    $db->query("DELETE FROM ts_nfo WHERE id NOT IN (" . $Torrents . ")");
    displayDatabaseUpdateMessage("ts_nfo");
    $db->query("OPTIMIZE TABLE ts_nfo");
    
    $db->query("DELETE FROM ts_profilevisitor WHERE userid NOT IN (" . $Users . ") OR visitorid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_profilevisitor");
    $db->query("OPTIMIZE TABLE ts_profilevisitor");
    
    $db->query("DELETE FROM ts_ratings WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_ratings");
    $db->query("OPTIMIZE TABLE ts_ratings");
    
    $db->query("DELETE FROM ts_reports WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_reports");
    $db->query("OPTIMIZE TABLE ts_reports");
    
    $db->query("DELETE FROM ts_secret_questions WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_secret_questions");
    $db->query("OPTIMIZE TABLE ts_secret_questions");
    
    $db->query("DELETE FROM ts_seedboxes WHERE sb_userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_seedboxes");
    $db->query("OPTIMIZE TABLE ts_seedboxes");
    
    $db->query("DELETE FROM ts_shoutbox WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_shoutbox");
    $db->query("OPTIMIZE TABLE ts_shoutbox");
    
    $db->query("DELETE FROM ts_shoutcastdj WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_shoutcastdj");
    $db->query("OPTIMIZE TABLE ts_shoutcastdj");
    
    $db->query("DELETE FROM ts_social_group_members WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_social_group_members");
    $db->query("OPTIMIZE TABLE ts_social_group_members");
    
    $db->query("DELETE FROM ts_social_group_messages WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_social_group_messages");
    $db->query("OPTIMIZE TABLE ts_social_group_messages");
    
    $db->query("DELETE FROM ts_social_group_reports WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_social_group_reports");
    $db->query("OPTIMIZE TABLE ts_social_group_reports");
    
    $db->query("DELETE FROM ts_social_groups_subscribe WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_social_groups_subscribe");
    $db->query("OPTIMIZE TABLE ts_social_groups_subscribe");
    
    $db->query("DELETE FROM ts_social_groups WHERE owner NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_social_groups");
    $db->query("OPTIMIZE TABLE ts_social_groups");
    
    $db->query("DELETE FROM ts_thanks WHERE uid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_thanks");
    $db->query("OPTIMIZE TABLE ts_thanks");
    
    $db->query("DELETE FROM ts_torrents_details WHERE tid NOT IN (" . $Torrents . ")");
    displayDatabaseUpdateMessage("ts_torrents_details");
    $db->query("OPTIMIZE TABLE ts_torrents_details");
    
    $db->query("DELETE FROM ts_user_validation WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_user_validation");
    $db->query("OPTIMIZE TABLE ts_user_validation");
    
    $db->query("DELETE FROM ts_u_perm WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_u_perm");
    $db->query("OPTIMIZE TABLE ts_u_perm");
    
    $db->query("DELETE FROM ts_visitor_messages WHERE userid NOT IN (" . $Users . ") OR visitorid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_visitor_messages");
    $db->query("OPTIMIZE TABLE ts_visitor_messages");
    
    $db->query("DELETE FROM ts_watch_list WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_watch_list");
    $db->query("OPTIMIZE TABLE ts_watch_list");
    
    $db->query("DELETE FROM ts_games_champions WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_games_champions");
    $db->query("OPTIMIZE TABLE ts_games_champions");
    
    $db->query("DELETE FROM ts_games_comments WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_games_comments");
    $db->query("OPTIMIZE TABLE ts_games_comments");
    
    $db->query("DELETE FROM ts_games_scores WHERE userid NOT IN (" . $Users . ")");
    displayDatabaseUpdateMessage("ts_games_scores");
    $db->query("OPTIMIZE TABLE ts_games_scores");
    
    echo "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n</div>";
    
} catch (Exception $e) {
    error_log("Tweak tracker error: " . $e->getMessage());
    echo showAlertError("An error occurred during database cleanup");
}

function getStaffLanguage(): string
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}

function checkStaffAuthentication(): void
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}

function redirectTo(string $url): void
{
    $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . htmlspecialchars($Error, ENT_QUOTES, 'UTF-8') . "</div></div>";
}

function logStaffAction(string $log): void
{
    try {
        $db = $GLOBALS["DatabaseConnect"];
        $stmt = $db->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (?, ?, ?)");
        $adminId = (int)($_SESSION["ADMIN_ID"] ?? 0);
        $timestamp = time();
        $stmt->bind_param('iis', $adminId, $timestamp, $log);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Log staff action error: " . $e->getMessage());
    }
}

function displayDatabaseUpdateMessage(string $Msg): void
{
    global $Language;
    $affected = $GLOBALS["DatabaseConnect"]->affected_rows;
    echo "\r\n\t<table>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t" . str_replace("{1}", htmlspecialchars($Msg, ENT_QUOTES, 'UTF-8'), $Language[2]) . "\r\n\t\t\t</td>\r\n\t\t\t<td>\r\n\t\t\t\t" . str_replace("{1}", htmlspecialchars(number_format($affected), ENT_QUOTES, 'UTF-8'), $Language[3]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
}

?>
