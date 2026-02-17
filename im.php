<?php
define("IM_VERSION", "0.5 by xam");
define("THIS_SCRIPT", "im.php");
require "./global.php";
$lang->load("im");
// Use descriptive variable names for clarity and PSR-12 compliance
$contactType = isset($_GET["type"]) ? trim($_GET["type"]) : "";
$contactUserId = isset($_GET["userid"]) ? intval($_GET["userid"]) : "";
if (!$contactUserId || !$contactType || !in_array($contactType, ["icq", "aim", "msn", "skype", "yahoo"]) || $CURUSER["id"] == 0) {
    error_($lang->global["nopermission"]);
}
$userQuery = sql_query("SELECT id, username, email, contact, options, g.namestyle FROM users  LEFT JOIN usergroups g ON (users.$usergroup = g.gid) WHERE `id` = " . sqlesc($contactUserId));
if (mysqli_num_rows($userQuery) == 0) {
    error_($lang->global["nopermission"]);
}
$userData = mysqli_fetch_assoc($userQuery);
if ($CURUSER["id"] != $contactUserId && $usergroups["canviewotherprofile"] != "yes") {
    error_($lang->global["nopermission"]);
}
if ((TS_Match($userData["options"], "I3") || TS_Match($userData["options"], "I4")) && $CURUSER["id"] != $contactUserId && !$is_mod) {
    perror_($lang->global["nopermission"]);
}
$userData["username"] = "<a $href = \"" . ts_seo($userData["id"], $userData["username"]) . "\" $target = \"_blank\">" . get_user_color($userData["username"], $userData["namestyle"]) . "</a>";
$contactArrayRaw = @explode("|", $userData["contact"]);
$contactArray = [];
foreach ($contactArrayRaw as $contactIndex => $contactValue) {
    $contactArray[$contactIndex] = htmlspecialchars_uni($contactValue);
}
$contactHtmlContent = "";
switch ($contactType) {
    case "icq":
        exit;
        break;
    case "aim":
        if (!$contactArray[1]) {
            error_($lang->global["nopermission"]);
        }
        $contactHtmlContent = "\r\n\t\t<div $style = \"padding-bottom: 5px; padding-top: 5px; padding-left: 5px;\"><img $src = \"" . $pic_base_url . "contact/aim.gif\" $border = \"0\" class=\"inlineimg\" /> " . sprintf($lang->im["aim1"], $userData["username"], $contactArray[1]) . "</div>\r\n\t\t<div $style = \"padding-bottom: 5px; padding-left: 5px;\"><a $href = \"aim:addbuddy?$screenname = " . $contactArray[1] . "\"><u>" . sprintf($lang->im["add"], $contactArray[1]) . "</u></a></div>\r\n\t\t<div $style = \"padding-bottom: 5px; padding-left: 5px;\"><a $href = \"aim:goim?$screenname = " . $contactArray[1] . "&amp;$message = " . $lang->im["default_aim_msg"] . "\"><u>" . sprintf($lang->im["msg"], $contactArray[1]) . "</u></a></div>\r\n\t\t<div $style = \"padding-top: 5px; padding-bottom: 5px; padding-left: 5px;\">" . $lang->im["aim2"] . "</div>\r\n\t\t";
        break;
    case "msn":
        if (!$contactArray[3]) {
            error_($lang->global["nopermission"]);
        }
        require INC_PATH . "/functions_browser.php";
        $contactHtmlContent = "\r\n\t\t\t<object $classid = \"clsid:B69003B3-C55E-4B48-836C-BC5946FC3B28\" $codetype = \"application/x-oleobject\" $id = \"MsgrObj\" $width = \"0\" $height = \"0\"></object>\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t<!--\r\n\t\t\t\tvar $tslangs = {\r\n\t\t\t\t\t\"you_must_be_logged_into_msn_before_doing_this\" : \"" . $lang->im["msn3"] . "\",\r\n\t\t\t\t\t\"msn_functions_only_work_in_ie\" : \"" . $lang->im["msn4"] . "\"\r\n\t\t\t\t};\r\n\t\t\t\tvar $is_ie = " . (is_browser("ie") ? "1" : "0") . ";\r\n\r\n\t\t\t\t function SendMSNMessage(name)\r\n\t\t\t\t{\r\n\t\t\t\t\tif (!is_ie)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\talert(tslangs['msn_functions_only_work_in_ie']);\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttry\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\tMsgrObj.InstantMessage(name);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t\tcatch(e)\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\talert(tslangs['msn_functions_only_work_in_ie']);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t}\r\n\r\n\t\t\t\t\treturn false;\r\n\t\t\t\t}\r\n\t\t\t\tfunction AddMSNContact(name)\r\n\t\t\t\t{\r\n\t\t\t\t\tif (!is_ie)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\talert(tslangs['msn_functions_only_work_in_ie']);\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttry\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\tMsgrObj.AddContact(0, name);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t\tcatch(e)\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\talert(tslangs['msn_functions_only_work_in_ie']);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t}\r\n\r\n\t\t\t\t\treturn false;\r\n\t\t\t\t}\r\n\r\n\t\t\t\tfunction handleError(msg, url, h)\r\n\t\t\t\t{\r\n\t\t\t\t\tif (msg.indexOf('8100031e') != '-1')\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\talert(tslangs[\"you_must_be_logged_into_msn_before_doing_this\"]);\r\n\t\t\t\t\t\treturn true;\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\treturn false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\r\n\t\t\t\twindow.$onerror = handleError;\r\n\t\t\t\t-->\r\n\t\t\t</script>\r\n\r\n\t\t\t<table $cellpadding = \"5\" $cellspacing = \"1\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\">\r\n\t\t\t\t<div class=\"panel\">\r\n\t\t\t\t\t<div $align = \"left\">\r\n\t\t\t\t\t<div $style = \"padding-bottom: 5px;\">\r\n\t\t\t\t\t\t<img $src = \"" . $pic_base_url . "contact/msn.gif\" $border = \"0\" class=\"inlineimg\" />\r\n\t\t\t\t\t\t" . sprintf($lang->im["msn1"], $userData["username"], $contactArray[3]) . "\r\n\t\t\t\t\t</div>\r\n\r\n\t\t\t\t\t<div $style = \"padding-bottom: 5px;\"><a $href = \"javascript:void(0);\" $onclick = \"AddMSNContact('" . $contactArray[3] . "'); return false;\"><u>" . sprintf($lang->im["add"], $contactArray[3]) . "</u></a></div>\r\n\r\n\t\t\t\t\t<div $style = \"padding-bottom: 5px;\"><a $href = \"javascript:void(0);\" $onclick = \"SendMSNMessage('" . $contactArray[3] . "'); return false;\"><u>" . sprintf($lang->im["msg"], $contactArray[3]) . "</u></a></div>\r\n\r\n\t\t\t\t\t<div $style = \"padding-bottom: 5px;\">\r\n\t\t\t\t\t\t" . $lang->im["msn2"] . "\r\n\t\t\t\t\t</div>\r\n\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t";
        break;
    case "skype":
        if (!$contactArray[2]) {
            error_($lang->global["nopermission"]);
        }
        $contactHtmlContent = "\r\n\t\t<script $type = \"text/javascript\" $src = \"https://download.skype.com/share/skypebuttons/js/skypeCheck.js\"></script>\r\n\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t<div class=\"panel\">\r\n\t\t\t\t<div $align = \"left\">\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"5\" $border = \"0\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"4\" class=\"none\" $style = \"padding:0px\" $align = \"center\"><br /><img $src = \"" . $pic_base_url . "contact/skype.gif\" class=\"inlineimg\" $alt = \"Skype&trade;\" /> " . sprintf($lang->im["skype1"], $userData["username"], $contactArray[2]) . "<br /><br /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"4\"  $align = \"center\">\r\n\t\t\t\t\t\t<a $href = \"https://www.skype.com\" $target = \"_blank\"><img $src = \"https://mystatus.skype.com/" . $contactArray[2] . ".png\" $alt = \"\" $border = \"0\" /></a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"4\" class=\"none\">\r\n\t\t\t\t\t<br />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"bottom\">\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?userinfo\" $onclick = \"return skypeCheck();\"><img $src = \"" . $pic_base_url . "contact/skype_info.gif\" $alt = \"" . $lang->im["skype2"] . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?userinfo\" $onclick = \"return skypeCheck();\">" . $lang->im["skype2"] . "</a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?add\" $onclick = \"return skypeCheck();\"><img $src = \"" . $pic_base_url . "contact/skype_addcontact.gif\" $alt = \"" . $lang->im["skype3"] . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?add\" $onclick = \"return skypeCheck();\">" . $lang->im["skype3"] . "</a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"bottom\">\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?call\" $onclick = \"return skypeCheck();\"><img $src = \"" . $pic_base_url . "contact/skype_callstart.gif\" $alt = \"" . $lang->im["skype4"] . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?call\" $onclick = \"return skypeCheck();\">" . $lang->im["skype4"] . "</a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?chat\" $onclick = \"return skypeCheck();\"><img $src = \"" . $pic_base_url . "contact/skype_message.gif\" $alt = \"" . $lang->im["skype5"] . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?chat\" $onclick = \"return skypeCheck();\">" . $lang->im["skype5"] . "</a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"bottom\">\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?voicemail\" $onclick = \"return skypeCheck();\"><img $src = \"" . $pic_base_url . "contact/skype_voicemail.gif\" $alt = \"" . $lang->im["skype6"] . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?voicemail\" $onclick = \"return skypeCheck();\">" . $lang->im["skype6"] . "</a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?sendfile\" $onclick = \"return skypeCheck();\"><img $src = \"" . $pic_base_url . "contact/skype_fileupload.gif\" $alt = \"" . $lang->im["skype7"] . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t\t<td class=\"none\"><a $href = \"skype:" . $contactArray[2] . "?sendfile\" $onclick = \"return skypeCheck();\">" . $lang->im["skype7"] . "</a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"4\" class=\"none\" $align = \"center\"><br /><b>" . $lang->im["skype8"] . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\r\n\t\t\t\t</div>\r\n\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t";
        break;
    case "yahoo":
        if (!$contactArray[4]) {
            handleError($lang->global["nopermission"]);
        }
        $contactHtmlContent = "\r\n\t\t\t<table $cellpadding = \"5\" $cellspacing = \"1\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\">\r\n\t\t\t\t<div class=\"panel\">\r\n\t\t\t\t\t<div $align = \"left\">\r\n\t\t\t\t\t<div $style = \"padding-bottom: 5px;\">\r\n\t\t\t\t\t\t<span $style = \"float:right\"><img $src = \"https://opi.yahoo.com/online?$u = " . $contactArray[4] . "&amp;$m = g&amp;$t = 2\" $alt = \"\" $border = \"0\" /></span>\r\n\t\t\t\t\t\t<img $src = \"" . $pic_base_url . "contact/yahoo.gif\" $alt = \"\" $border = \"0\" />\r\n\t\t\t\t\t\t" . sprintf($lang->im["yahoo2"], $userData["username"], $contactArray[4]) . "\r\n\t\t\t\t\t</div>\r\n\r\n\t\t\t\t\t<div $style = \"padding-bottom: 5px;\"><a $href = \"https://profiles.yahoo.com/" . $contactArray[4] . "\" $target = \"_blank\" $onclick = \"window.close(); return true;\"><u>" . sprintf($lang->im["yahoo1"], $contactArray[4]) . "</u></a></div>\r\n\r\n\t\t\t\t\t<div $style = \"padding-bottom: 5px;\"><a $href = \"https://edit.yahoo.com/config/send_webmesg?.$target = " . $contactArray[4] . "&amp;.$src = pg\" $target = \"_blank\" $onclick = \"window.close(); return true;\"><u>" . sprintf($lang->im["msg"], $contactArray[4]) . "</u></a></div>\r\n\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t";
        break;
    default:
        $defaulttemplate = ts_template();
        echo "\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html xmlns=\"https://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\r\n\t<head>\r\n\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $charset . "\" />\r\n\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $BASEURL . "/style.php?theme=" . $defaulttemplate . "&style=style.css\" />\r\n\t\t<title>" . strtoupper($contactType) . "</title>\r\n\t</head>\r\n\t<body>\r\n\t\t<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t<div style=\"float: right; padding-right: 5px;\">\r\n\t\t\t\t\t\t<a href=\"javascript:void(0);\" onclick=\"self.close()\"><img src=\"" . $pic_base_url . "close.gif\" border=\"0\" class=\"inlineimg\" /></a>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div style=\"padding-left: 5px;\">\r\n\t\t\t\t\t\t" . strtoupper($contactType) . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t" . $contactHtmlContent . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t</body>\r\n</html>\r\n";
}
function handleError($msg)
{
    global $lang;
    exit("\r\n\t<font $face = \"verdana\" $size = \"2\" $color = \"darkred\"><b>\r\n\t\t" . $msg . "\r\n\t</font>\r\n\t");
}

?>