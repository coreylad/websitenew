<?php
define("THIS_SCRIPT", "staff.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
define("SP_VERSION", "v.1.2 by xam");
$lang->load("staff");
$lang->load("comment");
stdhead($lang->staff["staff"]);
$userTimeoutDelta = TIMENOW - TS_TIMEOUT;
$staffGroups = [];
$staffGroupsQuery = sql_query("SELECT gid FROM usergroups WHERE $showstaffteam = 'yes'" . ($is_mod ? " OR $showstaffteam = 'staff'" : ""));
while ($staffGroup = mysqli_fetch_array($staffGroupsQuery)) {
    $staffGroups[$staffGroup["gid"]] = $staffGroup;
}
$staffGroupsIn = implode(",", array_keys($staffGroups));
$staffUsersQuery = sql_query("SELECT u.id,u.username,u.usergroup,u.last_access,u.options,u.country,c.name,c.flagpic,g.namestyle FROM users u LEFT JOIN countries c ON (u.$country = c.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.usergroup IN (" . $staffGroupsIn . ") ORDER BY u.username");
while ($staffUser = mysqli_fetch_array($staffUsersQuery)) {
    $lastAccess = $staffUser["last_access"];
    $userId = $staffUser["id"];
    if (TS_Match($staffUser["options"], "B1") && !$is_mod && $userId != $CURUSER["id"]) {
        $onoffpic = "<img $src = \"" . $pic_base_url . "input_error.gif\" $alt = \"" . $lang->comment["offline"] . "\" $title = \"" . $lang->comment["offline"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->comment["offline"];
    } else {
        if ($userTimeoutDelta < TS_MTStoUTS($lastAccess) || $userId == $CURUSER["id"]) {
            $onoffpic = "<img $src = \"" . $pic_base_url . "input_true.gif\" $alt = \"" . $lang->comment["online"] . "\" $title = \"" . $lang->comment["online"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->comment["online"];
        } else {
            $onoffpic = "<img $src = \"" . $pic_base_url . "input_error.gif\" $alt = \"" . $lang->comment["offline"] . "\" $title = \"" . $lang->comment["offline"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->comment["offline"];
        }
    }
    $staffTable[$staffUser["usergroup"]] = (isset($staffTable[$staffUser["usergroup"]]) ? $staffTable[$staffUser["usergroup"]] : "") . "\n\t\t<tr>\n\t\t<td $width = \"70%\" $align = \"left\"><a class=\"altlink\" $href = \"userdetails.php?$id = " . $staffUser["id"] . "\">" . get_user_color($staffUser["username"], $staffUser["namestyle"]) . "</a></td>\n\t\t<td $width = \"10%\" $align = \"center\">" . (!empty($staffUser["flagpic"]) ? "<img $src = \"" . $pic_base_url . "flag/" . $staffUser["flagpic"] . "\" $border = \"0\" $alt = \"" . $staffUser["name"] . "\" $title = \"" . $staffUser["name"] . "\">" : "<img $src = \"" . $pic_base_url . "flag/jollyroger.gif\" $border = \"0\">") . "</td>\n\t\t<td $width = \"10%\" $align = \"center\"><input $type = \"button\" $value = \"" . $lang->comment["pm"] . "\" $onclick = \"jumpto('" . $BASEURL . "/sendmessage.php?$receiver = " . $staffUser["id"] . "');\" /></td>\n\t\t<td $width = \"10%\" $align = \"center\">" . $onoffpic . "</td>\n\t\t</tr>";
}
$query = sql_query("SELECT gid,title,namestyle,disporder FROM usergroups WHERE $showstaffteam = 'yes'" . ($is_mod ? " OR $showstaffteam = 'staff'" : "") . " ORDER by disporder");
while ($group = mysqli_fetch_array($query)) {
    if (isset($staff_table[$group["gid"]])) {
        echo "\n\t\t<table class=\"none\" $border = \"0\" $cellpadding = \"4\" $cellspacing = \"1\" $width = \"100%\">\n\t\t\t<tbody>\n\t\t\t<tr>\n\t\t\t\t<td class=\"thead\" $colspan = \"4\">" . ts_collapse($group["gid"]) . "<strong>" . $group["title"] . "</strong></td>\n\t\t\t</tr>\n\t\t\t" . ts_collapse($group["gid"], 2) . "\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\" $align = \"left\"><strong>" . $lang->staff["username"] . "</strong></td>\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><strong>" . $lang->staff["country"] . "</strong></td>\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><strong>" . $lang->staff["contact"] . "</strong></td>\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><strong>" . $lang->staff["onoff"] . "</strong></td>\n\t\t\t</tr>\n\t\t\t\t" . $staff_table[$group["gid"]] . "\n\t\t\t</tbody>\n\t\t</table>\n\t\t<br>";
    }
}
$firstline = "";
$query = sql_query("SELECT s.supportlang,s.supportfor,u.id,u.username,u.usergroup,u.last_access,u.options,u.country,c.name,c.flagpic,g.namestyle FROM ts_support s LEFT JOIN users u ON (s.`userid` = u.id) LEFT JOIN countries c ON (u.$country = c.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) ORDER BY u.username");
while ($arr = mysqli_fetch_array($query)) {
    $last_access = $arr["last_access"];
    $userid = $arr["id"];
    if (TS_Match($arr["options"], "B1") && !$is_mod && $userid != $CURUSER["id"]) {
        $onoffpic = "<img $src = \"" . $pic_base_url . "input_error.gif\" $alt = \"" . $lang->comment["offline"] . "\" $title = \"" . $lang->comment["offline"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->comment["offline"];
    } else {
        if ($dt < TS_MTStoUTS($last_access) || $userid == $CURUSER["id"]) {
            $onoffpic = "<img $src = \"" . $pic_base_url . "input_true.gif\" $alt = \"" . $lang->comment["online"] . "\" $title = \"" . $lang->comment["online"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->comment["online"];
        } else {
            $onoffpic = "<img $src = \"" . $pic_base_url . "input_error.gif\" $alt = \"" . $lang->comment["offline"] . "\" $title = \"" . $lang->comment["offline"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->comment["offline"];
        }
    }
    $firstline .= "\n\t<tr>\n\t\t<td $width = \"30%\" $align = \"left\"><a class=\"altlink\" $href = \"userdetails.php?$id = " . $arr["id"] . "\">" . get_user_color($arr["username"], $arr["namestyle"]) . "</a></td>\n\t\t<td $width = \"30%\" $align = \"left\">" . $arr["supportfor"] . "</td>\n\t\t<td $width = \"10%\" $align = \"left\">" . $arr["supportlang"] . "</td>\n\t\t<td $width = \"10%\" $align = \"center\">" . (!empty($arr["flagpic"]) ? "<img $src = \"" . $pic_base_url . "flag/" . $arr["flagpic"] . "\" $border = \"0\" $alt = \"" . $arr["name"] . "\" $title = \"" . $arr["name"] . "\">" : "<img $src = \"" . $pic_base_url . "flag/jollyroger.gif\" $border = \"0\">") . "</td>\n\t\t<td $width = \"10%\" $align = \"center\"><input $type = \"button\" $value = \"" . $lang->comment["pm"] . "\" $onclick = \"jumpto('" . $BASEURL . "/sendmessage.php?$receiver = " . $arr["id"] . "');\" /></td>\n\t\t<td $width = \"10%\" $align = \"center\">" . $onoffpic . "</td>\n\t</tr>";
}
if (!empty($firstline)) {
    echo "\n\t<table class=\"none\" $border = \"0\" $cellpadding = \"4\" $cellspacing = \"1\" $width = \"100%\">\n\t\t<tbody>\n\t\t\t<tr>\n\t\t\t\t<td class=\"thead\" $colspan = \"6\">" . ts_collapse("firstline1") . "<strong>" . $lang->staff["firstline"] . "</strong></td>\n\t\t\t</tr>\n\t\t\t\t" . ts_collapse("firstline1", 2) . "\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\" $align = \"left\"><strong>" . $lang->staff["username"] . "</strong></td>\n\t\t\t\t<td class=\"subheader\" $align = \"left\"><strong>" . $lang->staff["supportfor"] . "</strong></td>\n\t\t\t\t<td class=\"subheader\" $align = \"left\"><strong>" . $lang->staff["language"] . "</strong></td>\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><strong>" . $lang->staff["country"] . "</strong></td>\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><strong>" . $lang->staff["contact"] . "</strong></td>\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><strong>" . $lang->staff["onoff"] . "</strong></td>\n\t\t\t</tr>\n\t\t\t\t" . $firstline . "\n\t\t\t<tr>\n\t\t\t\t<td $align = \"left\" $colspan = \"6\">" . $lang->staff["info"] . "<br>" . $lang->staff["firstlineinfo"] . "</td>\n\t\t\t</tr>\n\t\t</tbody>\n\t</table>\n\t<br>";
}
stdfoot();

?>