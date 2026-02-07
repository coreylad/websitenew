<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "announcement.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
if (isset($_GET["aid"]) && is_valid_id($_GET["aid"])) {
    $aid = intval($_GET["aid"]);
    ($query = sql_query("SELECT a.*, u.id, u.username, g.namestyle, g.title as usergrouptitle FROM " . TSF_PREFIX . "announcement a LEFT JOIN users u ON (a.userid=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE a.announcementid = " . sqlesc($aid))) || sqlerr(__FILE__, 43);
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalidaid"]);
        exit;
    }
    $a = mysqli_fetch_assoc($query);
    $defaulttemplate = ts_template();
    echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\r\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\" />\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=";
    echo $charset;
    echo "\" />\r\n<link rel=\"stylesheet\" href=\"";
    echo $BASEURL;
    echo "/style.php?theme=";
    echo $defaulttemplate;
    echo "&style=style.css\" type=\"text/css\" media=\"screen\" />\r\n<title>";
    echo $SITENAME;
    echo "</title>\r\n<script type=\"text/javascript\">\r\n\tfunction to_old_win(url)\r\n\t{\r\n\t\tsetInterval(\"window.close()\",3000);\r\n\t\topener.location.href = url;\r\n\t}\r\n</script>\r\n</head>\r\n\r\n<body>\r\n";
    echo "\r\n<table border=\"0\" cellspacing=\"0\" cellpadding=\"4\" class=\"none\" style=\"clear: both;\" width=\"100%\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" colspan=\"2\">" . $lang->tsf_forums["atitle"] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" width=\"5%\" align=\"center\"><img src=\"" . $BASEURL . "/tsf_forums/images/announcement_new.gif\" border=\"0\" alt=\"" . $lang->tsf_forums["announcements"] . htmlspecialchars_uni($a["title"]) . "\" title=\"" . $lang->tsf_forums["announcements"] . htmlspecialchars_uni($a["title"]) . "\"></td>\r\n\t\t<td class=\"alt2\" colspan=\"6\">\r\n\t\t\t<div>\r\n\t\t\t\t<span class=\"smallfont\" style=\"float: right;\">" . $lang->tsf_forums["views"] . ": <strong>" . $a["views"] . "</strong> <img class=\"inlineimg\" src=\"" . $pic_base_url . "comments2.gif\" alt=\"\" border=\"0\"></span>\r\n\t\t\t\t<strong>" . $lang->tsf_forums["announcements"] . "</strong> " . htmlspecialchars_uni($a["title"]) . "\r\n\r\n\t\t\t</div>\r\n\t\t\t<div>\r\n\t\t\t\t<span style=\"float: right;\"><span class=\"smallfont\">" . my_datee($dateformat, $a["posted"]) . " " . my_datee($timeformat, $a["posted"]) . "</span></span>\r\n\t\t\t\t<span class=\"smallfont\"><a href=\"javascript:void(0);\" onClick=\"to_old_win('" . ts_seo($a["id"], $a["username"]) . "')\">" . get_user_color($a["username"], $a["namestyle"]) . "</a> (" . $a["usergrouptitle"] . ")</span>\r\n\t\t\t</div>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t<td class=\"alt1\" width=\"5%\" align=\"center\" valign=\"top\"><img src=\"" . $BASEURL . "/tsf_forums/images/announcement_old.gif\" border=\"0\" alt=\"" . $lang->tsf_forums["announcements"] . htmlspecialchars_uni($a["title"]) . "\" title=\"" . $lang->tsf_forums["announcements"] . htmlspecialchars_uni($a["title"]) . "\"></td>\r\n\t<td align=\"left\">" . $a["pagetext"] . "</td>\r\n\t</tr>\r\n</table>\r\n</body>\r\n</html>";
    sql_query("UPDATE " . TSF_PREFIX . "announcement SET views = views + 1 WHERE announcementid = " . sqlesc($aid)) or sql_query("UPDATE " . TSF_PREFIX . "announcement SET views = views + 1 WHERE announcementid = " . sqlesc($aid)) || sqlerr(__FILE__, 96);
} else {
    stderr($lang->global["error"], $lang->tsf_forums["invalidaid"]);
    exit;
}

?>