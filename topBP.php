<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "topBP.php");
require "./global.php";
if (!isset($CURUSER) || isset($CURUSER["id"]) && $CURUSER["id"] == 0) {
    print_no_permission();
}
$List = "";
$users = sql_query("SELECT u.id, u.username, u.seedbonus, g.namestyle FROM users u LEFT JOIN usergroups g on (u.$usergroup = g.gid) WHERE seedbonus > 0 ORDER BY seedbonus DESC LIMIT 200");
while ($u = mysqli_fetch_assoc($users)) {
    $List .= "<tr><td><a $href = \"" . ts_seo($u["id"], $u["username"], "u") . "\">" . get_user_color($u["username"], $u["namestyle"]) . "</a></td><td>" . $u["seedbonus"] . "</td></tr>";
}
stdhead("TOP BP");
echo "\r\n<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t<tbody>\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"2\">TOP BP</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\">Username</td><td class=\"subheader\">Points</td>\r\n\t\t</tr>\r\n\t\t" . $List . "\r\n\t</tbody>\r\n</table>\r\n";
stdfoot();

?>