
<?php

define("THIS_SCRIPT", "checkuser.php");
require "./global.php";
define("CU_VERSION", "0.7 by xam");
$lang->load("checkuser");

$userId = 0 + $_GET["id"];
int_check($userId);
$userQueryResult = @sql_query("SELECT u.*, c.flagpic FROM users u LEFT JOIN countries c ON (u.$country = c.id) WHERE u.$status = 'pending' AND u.$id = " . @sqlesc($userId)) || sqlerr(__FILE__, 26);
$userData = mysqli_fetch_array($userQueryResult) || stderr($lang->global["error"], $lang->global["nouserid"]);
if (!$is_mod && $userData["invited_by"] != $CURUSER["id"]) {
    print_no_permission();
}
if ($userData["added"] == "0000-00-00 00:00:00") {
    $joinDate = $lang->checkuser["na"];
} else {
    require_once INC_PATH . "/functions_mkprettytime.php";
    $joinDate = my_datee($regdateformat, $userData["added"]) . " (" . mkprettytime(TIMENOW - TS_MTStoUTS($userData["added"])) . ")";
}
$countryHtml = "";
if ($userData["country"]) {
    $countryHtml = "<td class=embedded><img $src = " . $pic_base_url . "flag/" . $userData["flagpic"] . " $alt = \"" . $userData["name"] . "\" $title = \"" . $userData["name"] . "\" $style = 'margin-left: 8pt'></td>";
}
stdhead(sprintf($lang->checkuser["details"], $userData["username"]));
$isEnabled = $userData["enabled"] == "yes";
echo "<p><table class=main $border = 0 $cellspacing = 0 $cellpadding = 0><tr><td class=embedded><h1 $style = 'margin:0px'>" . sprintf($lang->checkuser["details"], $userData["username"]) . "</h1></td>" . $countryHtml . "</tr></table></p><br />\n";
if (!$isEnabled) {
    echo $lang->global["accountdisabled"];
}
echo "<table $width = 100% $border = 1 $cellspacing = 0 $cellpadding = 5>\r\n<tr><td class=rowhead $width = 10%>";
echo $lang->checkuser["joindate"];
echo "</td><td $align = left $width = 90%>";
echo $joinDate;
echo "</td></tr>\r\n<tr><td class=rowhead $width = 10%>";
echo $lang->checkuser["email"];
echo "</td><td $align = left $width = 90%><a $href = mailto:";
echo $userData["email"];
echo ">";
echo $userData["email"];
echo "</a></td></tr>\r\n";
if ($is_mod && $userData["ip"] != "") {
    echo "<tr><td class=rowhead $width = 1%>" . $lang->checkuser["ip"] . "</td><td $align = left $width = 99%>" . $userData["ip"] . "</td></tr>";
}
echo "<tr><td class=rowhead $width = 1%>" . $lang->checkuser["status"] . "</td><td $align = left $width = 99%>" . ($userData["status"] == "pending" ? "<font $color = #ca0226>" . $lang->checkuser["pending"] . "</font>" : "<font $color = #1f7309>" . $lang->checkuser["confirmed"] . "</font>") . "</td></tr>";
echo "</table>";
stdfoot();

?>