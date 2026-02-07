<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.4 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
    $lang->load("login");
    require_once INC_PATH . "/functions_security.php";
    $loginbox = "\r\n\t<form method=\"post\" action=\"takelogin.php\">\r\n\t" . $lang->login["username"] . "<br /><input type=\"text\" size=\"20\" name=\"username\" class=\"inputUsernameLoginbox\" value=\"\" /><br />\r\n\t" . $lang->login["password"] . "<br /><input type=\"password\" size=\"20\" name=\"password\" class=\"inputPasswordLoginbox\" value=\"\" /><br />";
    if ($iv == "reCAPTCHA") {
        list($secimage, $rHTML) = show_reCAPTCHA_code_editor();
        $loginbox .= "\r\n\t\t" . $secimage . "<br />" . $rHTML;
    } else {
        if ($iv == "yes") {
            if (!defined("IMAGE_CODE_GENERATED")) {
                $Code = mksecret(5, false);
                $Hash = md5($securehash . $Code . USERIPADDRESS);
                sql_query("INSERT INTO ts_captcha VALUES ('" . $Hash . "', '" . $Code . "', " . TIMENOW . ")");
            } else {
                $IMAGE_CODE_GENERATED = explode("~", IMAGE_CODE_GENERATED);
                list($Code, $Hash) = $IMAGE_CODE_GENERATED;
            }
            $loginbox .= "\r\n\t\t<script type=\"text/javascript\" src=\"" . $BASEURL . "/scripts/reload_image.js\"></script>\r\n\t\t" . $lang->global["secimage"] . "<br />\r\n\t\t<table>\r\n\t\t\t<tr>\r\n\t\t\t\t<td rowspan=\"2\" class=\"none\"><img src=\"" . $BASEURL . "/ts_image.php?hash=" . $Hash . "&amp;width=132&amp;height=50\" id=\"regimage\" border=\"0\" alt=\"\" /></td>\r\n\t\t\t\t<td class=\"none\"><img src=\"" . $pic_base_url . "reload.gif\" border=\"0\" style=\"cursor:pointer\" onclick=\"javascript:reload()\" alt=\"" . $lang->global["secimagehint"] . "\" title=\"" . $lang->global["secimagehint"] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\"><img src=\"" . $pic_base_url . "listen.gif\" border=\"0\" style=\"cursor:pointer\" onclick=\"return TSOpenPopup('" . $BASEURL . "/listen.php?hash=" . $Hash . "', 'listen', 400, 120);\" alt=\"" . $lang->global["seclisten"] . "\" title=\"" . $lang->global["seclisten"] . "\" id=\"listen\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t" . $lang->global["seccode"] . "<br />\r\n\t\t<input type=\"text\" size=\"20\" name=\"imagestring\" class=\"inputPasswordLoginbox\" value=\"\" />";
        }
    }
    $loginbox .= "\r\n\t<input type=\"checkbox\" class=\"none\" name=\"logout\" style=\"vertical-align: middle;\" value=\"yes\" />" . $lang->login["logout15"] . " <br />\r\n\t<input type=\"submit\" value=\"" . $lang->login["login"] . "\" /> <input type=\"reset\" value=\"" . $lang->login["reset"] . "\" />\r\n\t</form>\r\n\t";
} else {
    include_once INC_PATH . "/functions_ratio.php";
    $ratio = get_user_ratio($CURUSER["uploaded"], $CURUSER["downloaded"], true);
    if ($CURUSER["donor"] == "yes") {
        $medaldon = "<img src=\"" . $pic_base_url . "star.gif\" alt=\"" . $lang->global["imgdonated"] . "\" title=\"" . $lang->global["imgdonated"] . "\" class=\"inlineimg\" />";
    }
    if ($CURUSER["warned"] == "yes" || $CURUSER["leechwarn"] == "yes") {
        $warn = "<img src=\"" . $pic_base_url . "warned.gif\" alt=\"" . $lang->global["imgwarned"] . "\" title=\"" . $lang->global["imgwarned"] . "\" class=\"inlineimg\" />";
    }
    $loginbox = "\r\n\t\t<a href=\"" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "\">" . get_user_color($CURUSER["username"], $usergroups["namestyle"]) . "</a> (" . htmlspecialchars_uni($CURUSER["ip"]) . ") " . (isset($medaldon) ? $medaldon : "") . " " . (isset($warn) ? $warn : "") . "<br /><br />\r\n\t\t<span>\r\n\t\t\t" . $lang->global["ratio"] . " " . $ratio . "<br />" . $lang->global["bonus"] . " <a href=\"" . $BASEURL . "/mybonus.php\">" . number_format($CURUSER["seedbonus"], 2) . "</a><br />" . (intval($usergroups["slotlimit"]) ? sprintf($lang->global["slots"], $usergroups["slotlimit"]) . "<br />" : "") . $lang->global["uploaded"] . " <font color=\"green\">" . mksize($CURUSER["uploaded"]) . "</font><br />" . $lang->global["downloaded"] . " <font color=\"red\">" . mksize($CURUSER["downloaded"]) . "</font>\r\n\t\t</span>\r\n\t\t<br /><br />\r\n\r\n\t\t" . get_user_avatar($CURUSER["avatar"]) . "<br /><br />\r\n\t\t<a href=\"" . $BASEURL . "/usercp.php\">" . $lang->global["usercp"] . "</a> | <a href=\"" . $BASEURL . "/logout.php?logouthash=" . md5(USERIPADDRESS) . "\" onclick=\"return log_out();\">" . $lang->index["logout"] . "</a><br /><br />\r\n\r\n\t\t<div class=\"pluginFooterSmall\">" . sprintf($lang->index["llogin"], my_datee($dateformat, $CURUSER["last_login"]) . " " . my_datee($timeformat, $CURUSER["last_login"])) . "</div>\r\n\t";
}

?>