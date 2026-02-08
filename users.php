<?php
define("THIS_SCRIPT", "users.php");
require "./global.php";
define("U_VERSION", "2.4.7 by xam");
if ($usergroups["canmemberlist"] != "yes" || !isset($CURUSER)) {
    print_no_permission();
    exit;
}
$lang->load("users");
$errors = [];
$action = isset($_GET["action"]) ? htmlspecialchars_uni($_GET["action"]) : (isset($_POST["action"]) ? htmlspecialchars_uni($_POST["action"]) : "");
$orderby = "ORDER by u.username";
$array_where = [];
$array_links = [];
if ($action == "do_search") {
    function validusername($username)
    {
        if (!preg_match("|[^a-z\\|A-Z\\|0-9]|", $username)) {
            return true;
        }
        return false;
    }
    $byusername = isset($_POST["byusername"]) ? trim($_POST["byusername"]) : (isset($_GET["byusername"]) ? trim($_GET["byusername"]) : "");
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : (isset($_GET["username"]) ? trim($_GET["username"]) : "");
    $gender = isset($_POST["gender"]) ? trim($_POST["gender"]) : (isset($_GET["gender"]) ? trim($_GET["gender"]) : "");
    $country = isset($_POST["country"]) ? intval($_POST["country"]) : (isset($_GET["country"]) ? intval($_GET["country"]) : "");
    $usergroup = isset($_POST["usergroup"]) ? intval($_POST["usergroup"]) : (isset($_GET["usergroup"]) ? intval($_GET["usergroup"]) : "");
    if ($username != "" && validusername($username)) {
        $array_links[] = "username=" . htmlspecialchars_uni($username);
        switch ($byusername) {
            case "begins":
                $array_where[] = "u.username REGEXP(\"^" . $username . "\")";
                $array_links[] = "byusername=begins";
                break;
            case "contains":
                $array_where[] = "u.username LIKE(\"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "%\")";
                $array_links[] = "byusername=contains";
                break;
        }
    }
    if ($gender != "any" && ($gender == "male" || $gender == "female")) {
        $whatgender = $gender == "male" ? "L1" : "L2";
        $array_links[] = "gender=" . $gender;
        $array_where[] = "u.options REGEXP '" . $whatgender . "'";
    }
    if ($country != 0 && is_valid_id($country)) {
        $array_links[] = "country=" . $country;
        $array_where[] = "u.$country = '" . $country . "'";
    }
    if ($usergroup != 0 && is_valid_id($usergroup)) {
        $array_links[] = "usergroup=" . $usergroup;
        $array_where[] = "u.$usergroup = '" . $usergroup . "'";
        if (!$is_mod) {
            $array_where[] = "g.$canstaffpanel = 'no'";
            $array_where[] = "g.$cansettingspanel = 'no'";
            $array_where[] = "g.$issupermod = 'no'";
            $array_where[] = "g.$isvipgroup = 'no'";
        }
    }
}
if (0 < count($array_links)) {
    $implode_links = implode("&amp;", $array_links);
    $pagerlink = $_SERVER["SCRIPT_NAME"] . "?" . $implode_links . "&amp;$action = do_search&amp;";
} else {
    $pagerlink = $_SERVER["SCRIPT_NAME"] . "?";
}
if (0 < count($array_where)) {
    $where = "WHERE " . implode(" AND ", $array_where);
} else {
    $where = "";
}
$countries = "<select $name = \"country\"><option $value = \"any\">" . $lang->users["op5"] . "</option>";
$query = sql_query("SELECT id,name FROM countries ORDER by name") or ($query = sql_query("SELECT id,name FROM countries ORDER by name")) || sqlerr(__FILE__, 113);
while ($qcountry = mysqli_fetch_assoc($query)) {
    $countries .= "<option $value = \"" . $qcountry["id"] . "\"" . (isset($country) && $country == $qcountry["id"] ? " $selected = \"selected\"" : "") . ">" . $qcountry["name"] . "</option>";
}
$countries .= "</select\">";
$listusergroups = "<select $name = \"usergroup\"><option $value = \"any\">" . $lang->users["op5"] . "</option>";
$groupin = $is_mod ? "" : " WHERE $canstaffpanel = 'no' AND $cansettingspanel = 'no' AND $issupermod = 'no' AND $isvipgroup = 'no'";
$query = sql_query("SELECT gid, title FROM usergroups " . $groupin . "ORDER by title") or ($query = sql_query("SELECT gid, title FROM usergroups " . $groupin . "ORDER by title")) || sqlerr(__FILE__, 122);
while ($susergroup = mysqli_fetch_assoc($query)) {
    $listusergroups .= "<option $value = \"" . $susergroup["gid"] . "\"" . (isset($usergroup) && $usergroup == $susergroup["gid"] ? " $selected = \"selected\"" : "") . ">" . $susergroup["title"] . "</option>";
}
$listusergroups .= "</select\">";
stdhead($lang->users["title"]);
echo "\n<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\n<input $type = \"hidden\" $name = \"action\" $value = \"do_search\">\n<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" $align = \"center\" $width = \"100%\">\n\t<tr>\n\t\t<td class=\"colhead\" $colspan = \"4\">\n\t\t\t<a $name = \"searchuser\" $id = \"searchuser\"></a>" . ts_collapse("searchuser") . $lang->users["title3"] . "\n\t\t</td>\n\t</tr>\n\t" . ts_collapse("searchuser", 2) . "\n\t<tr>\n\t\t<td>\n\t\t\t<table $border = \"0\" $cellpadding = \"4\" $cellspacing = \"0\">\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\">" . $lang->users["op1"] . "</td>\n\t\t\t\t\t<td class=\"subheader\">" . $lang->users["op4"] . "</td>\n\t\t\t\t\t<td class=\"subheader\">" . $lang->users["op9"] . "</td>\n\t\t\t\t\t<td class=\"subheader\">" . $lang->users["op8"] . "</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td $width = \"60%\">\n\t\t\t\t\t\t<select $name = \"byusername\">\n\t\t\t\t\t\t\t<option $value = \"begins\"" . (isset($byusername) && $byusername == "begins" ? " $selected = \"selected\"" : "") . ">" . $lang->users["op2"] . "</option>\n\t\t\t\t\t\t\t<option $value = \"contains\"" . (isset($byusername) && $byusername == "contains" ? " $selected = \"selected\"" : "") . ">" . $lang->users["op3"] . "</option>\n\t\t\t\t\t\t</select>\n\t\t\t\t\t\t<input $type = \"text\" $name = \"username\" $value = \"" . (isset($username) ? htmlspecialchars_uni($username) : "") . "\" $size = \"30\" $rel = \"autoCompleteUsers\" />\n\t\t\t\t\t</td>\n\t\t\t\t\t<td $width = \"10%\">\n\t\t\t\t\t\t<select $name = \"gender\">\n\t\t\t\t\t\t\t<option $value = \"any\">" . $lang->users["op5"] . "</option>\n\t\t\t\t\t\t\t<option $value = \"male\"" . (isset($gender) && $gender == "male" ? " $selected = \"selected\"" : "") . ">" . $lang->users["op6"] . "</option>\n\t\t\t\t\t\t\t<option $value = \"female\"" . (isset($gender) && $gender == "female" ? " $selected = \"selected\"" : "") . ">" . $lang->users["op7"] . "</option>\n\t\t\t\t\t\t</select>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td $width = \"15%\">\n\t\t\t\t\t\t" . $countries . "\n\t\t\t\t\t</td>\n\t\t\t\t\t<td $width = \"15%\">\n\t\t\t\t\t\t" . $listusergroups . "\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td $colspan = \"4\" $align = \"center\">\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->global["buttonsearch"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->global["buttonreset"] . "\" />\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t</td>\n\t</tr>\n</table>\n</form>\n<br />\n";
($query = sql_query("SELECT u.id,g.gid FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) " . $where)) || sqlerr(__FILE__, 184);
$count = mysqli_num_rows($query);
list($pagertop, $pagerbottom, $limit) = pager(21, $count, $pagerlink);
($query = sql_query("SELECT u.id, u.username, u.options, u.avatar, u.last_access, u.last_login, u.added, u.added, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, c.name as countryname, c.flagpic as countryflag, g.namestyle, g.title as grouptitle FROM users u LEFT JOIN ts_u_perm p ON (u.`id` = p.userid) LEFT JOIN countries c ON (u.$country = c.id) LEFT JOIN usergroups g ON (g.$gid = u.usergroup) " . $where . " " . $orderby . " " . $limit)) || sqlerr(__FILE__, 188);
echo $pagertop;
echo "\n<table $width = \"100%\" $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\">\n\t<tr>\n\t\t<td class=\"colhead\" $colspan = \"4\">" . ts_collapse("userlist") . $lang->users["title"] . "</td>\n\t</tr>\n\t" . ts_collapse("userlist", 2);
if (0 < mysqli_num_rows($query)) {
    include_once INC_PATH . "/functions_icons.php";
    $userTimeoutDelta = TIMENOW - TS_TIMEOUT;
    $imagepath = $pic_base_url . "friends/";
    $lang->load("friends");
    $count = 0;
    echo "<tr>";
    for ($quickmenu = ""; $user = mysqli_fetch_assoc($query); $count++) {
        if ($count % 3 == 0) {
            echo "</tr><tr>";
        }
        if (TS_Match($user["options"], "L1")) {
            $UserGender = "<img $src = \"" . $imagepath . "Male.png\" $alt = \"" . $lang->global["male"] . "\" $title = \"" . $lang->global["male"] . "\" $border = \"0\" class=\"inlineimg\" />";
        } else {
            if (TS_Match($user["options"], "L2")) {
                $UserGender = "<img $src = \"" . $imagepath . "Female.png\" $alt = \"" . $lang->global["female"] . "\" $title = \"" . $lang->global["female"] . "\" $border = \"0\" class=\"inlineimg\" />";
            } else {
                $UserGender = "<img $src = \"" . $imagepath . "NA.png\" $alt = \"--\" $title = \"--\" $border = \"0\" class=\"inlineimg\" />";
            }
        }
        $xoffline = sprintf($lang->friends["xoffline"], $user["username"]);
        $xonline = sprintf($lang->friends["xonline"], $user["username"]);
        $xavatar = sprintf($lang->friends["xavatar"], $user["username"]);
        if (TS_Match($user["options"], "B1") && !$is_mod && $user["id"] != $CURUSER["id"]) {
            $user["last_access"] = $user["last_login"];
            $onoffpic = "<img $src = \"" . $imagepath . "offline.png\" $alt = \"" . $xoffline . "\" $title = \"" . $xoffline . "\" $border = \"0\" class=\"inlineimg\">";
        } else {
            if ($userTimeoutDelta < TS_MTStoUTS($user["last_access"]) || $user["id"] == $CURUSER["id"]) {
                $onoffpic = "<img $src = \"" . $imagepath . "online.png\" $alt = \"" . $xonline . "\" $title = \"" . $xonline . "\" $border = \"0\" class=\"inlineimg\">";
            } else {
                $onoffpic = "<img $src = \"" . $imagepath . "offline.png\" $alt = \"" . $xoffline . "\" $title = \"" . $xoffline . "\" $border = \"0\" class=\"inlineimg\">";
            }
        }
        echo "\n\t\t<td>\n\t\t\t<div>\n\t\t\t\t<div $style = \"float: right;\">\n\t\t\t\t\t<img $src = \"" . $pic_base_url . "flag/" . $user["countryflag"] . "\" $alt = \"" . $user["countryname"] . "\" $title = \"" . $user["countryname"] . "\" $height = \"20\" $width = \"32\" $border = \"0\"><br />\n\t\t\t\t\t<img $src = \"" . ($user["avatar"] ? fix_url($user["avatar"]) : $pic_base_url . "default_avatar.png") . "\" $alt = \"" . $xavatar . "\" $title = \"" . $xavatar . "\" $height = \"32\" $width = \"32\" $border = \"0\">\n\t\t\t\t</div>\n\t\t\t\t" . $UserGender . "\n\t\t\t\t<strong><a $href = \"javascript:void(0);\" $id = \"quickmenu" . $user["id"] . "\">" . get_user_color($user["username"], $user["namestyle"]) . "</a></strong> " . get_user_icons($user) . "\n\t\t\t\t<br />\n\t\t\t\t" . $onoffpic . "\n\t\t\t\t<strong>" . $lang->friends["act3"] . "</strong> " . my_datee($dateformat, $user["last_access"]) . " " . my_datee($timeformat, $user["last_access"]) . "\n\t\t\t\t<br />\n\t\t\t\t<a $href = \"" . $BASEURL . "/sendmessage.php?$receiver = " . $user["id"] . "\" $title = \"" . $lang->friends["act2"] . "\"><img $src = \"" . $imagepath . "pm.png\" $alt = \"\" $border = \"0\"></a> " . sprintf($lang->users["joined"], my_datee($regdateformat, $user["added"])) . "\n\t\t\t</div>\n\t\t</td>";
        $quickmenu .= "\n\t\t<div $id = \"quickmenu" . $user["id"] . "_menu\" class=\"menu_popup\" $style = \"display:none;\">\n\t\t\t<table $border = \"1\" $cellspacing = \"0\" $cellpadding = \"2\">\n\t\t\t\t<tr>\n\t\t\t\t\t<td $align = \"center\" class=\"thead\"><b>" . $lang->global["quickmenu"] . " " . $user["username"] . "</b></td>\n\t\t\t\t</tr>\n\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . ts_seo($user["id"], $user["username"]) . "\">" . $lang->global["qinfo1"] . "</a></td>\n\t\t\t\t</tr>\n\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/sendmessage.php?$receiver = " . $user["id"] . "\">" . sprintf($lang->global["qinfo2"], $user["username"]) . "</td>\n\t\t\t\t</tr>\n\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/tsf_search.php?$action = finduserposts&$id = " . $user["id"] . "\">" . sprintf($lang->global["qinfo3"], $user["username"]) . "</a></td>\n\t\t\t\t</tr>\n\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/tsf_search.php?$action = finduserthreads&$id = " . $user["id"] . "\">" . sprintf($lang->global["qinfo4"], $user["username"]) . "</a></td>\n\t\t\t\t</tr>\n\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/friends.php?$action = add_friend&$friendid = " . $user["id"] . "\">" . sprintf($lang->global["qinfo5"], $user["username"]) . "</td>\n\t\t\t\t</tr>\n\n\t\t\t\t" . ($is_mod ? "\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=edit_user&amp;$username = " . $user["username"] . "\">" . $lang->global["qinfo6"] . "</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=warn_user&amp;$username = " . $user["username"] . "\">" . $lang->global["qinfo7"] . "</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>" : "") . "\n\t\t\t</table>\n\t\t\t</div>\n\t\t\t<script $type = \"text/javascript\">\n\t\t\t\tmenu_register(\"quickmenu" . $user["id"] . "\");\n\t\t\t</script>";
    }
} else {
    echo "<tr><td $colspan = \"4\">" . $lang->users["nr"] . "</td>";
}
echo "\n</tr>\n</table>\n" . (isset($quickmenu) ? $quickmenu : "") . "\n<script $type = \"text/javascript\">\n\tmenu.activate(true);\n</script>\n" . $pagertop;
stdfoot();

?>