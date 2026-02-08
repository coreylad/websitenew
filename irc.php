<?php
define("THIS_SCRIPT", "irc.php");
require "./global.php";
if ($usergroups["canshout"] != "yes") {
    print_no_permission();
    exit;
}
$lang->load("irc");
$TITLE = sprintf($lang->irc["head"], $SITENAME);
$TSSEConfig->TSLoadConfig("PJIRC");
$nick = $CURUSER["username"] ? $CURUSER["username"] : "TSGuest_" . TIMENOW;
stdhead($TITLE);
echo show_notice($lang->irc["info"]);
echo "<script $type = \"text/javascript\">\r\n\tfunction openchat(oForm)\r\n\t{\r\n\t\tif (oForm.nick.value.$length = = 0)\r\n\t\t{\r\n\t\t\talert (\"";
echo $lang->irc["error"];
echo "\");\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\$ts = oForm.style.value + '?$nick = ' + oForm.nick.value;\r\n\t\t\tTSOpenPopup(s,'chatroom');\r\n\t\t}\r\n\t\treturn false;\r\n\t}\r\n</script>\r\n<form $method = \"post\" $onsubmit = \"return openchat(this)\" $action = \"\">\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"center\" class=\"thead\">";
echo $TITLE;
echo "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\">";
echo $lang->irc["server"];
echo "</td>\r\n\t\t\t<td><input $id = \"hostfield\" $type = \"text\" $name = \"host\" $size = \"32\" $value = \"";
echo $pjirchost;
echo "\" $disabled = \"disabled\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\">";
echo $lang->irc["channel"];
echo "</td>\r\n\t\t\t<td><input $id = \"channelfield\" $type = \"text\" $name = \"channel\" $size = \"32\" $value = \"";
echo $pjircchannel;
echo "\" $disabled = \"disabled\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\">";
echo $lang->irc["nickname"];
echo "</td>\r\n\t\t\t<td><input $id = \"nickfield\" $type = \"text\" $name = \"nick\" $size = \"32\" $value = \"";
echo $nick;
echo "\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\">";
echo $lang->irc["style"];
echo "</td>\r\n\t\t\t<td>\r\n\t\t\t\t<select $id = \"applettype\" $size = \"1\" $name = \"style\">\t\t\t\t\t\t\t\r\n\t\t\t\t\t<option $value = \"pjirc/start_chat.php\">Heavy</option>\r\n\t\t\t\t</select>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"center\" class=\"subheader\">\r\n\t\t\t\t<input $id = \"submit\" $type = \"submit\" $value = \"";
echo $lang->irc["start"];
echo "\" $name = \"B1\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n</form>\r\n";
stdfoot();

?>