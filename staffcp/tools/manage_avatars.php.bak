<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_avatars.lang");
$Message = "";
$AvatarPath = "../include/avatars/";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'FORUMCP'");
$Result = mysqli_fetch_assoc($query);
$FORUMCP = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
$BASEURL = $MAIN["BASEURL"];
$AllowedFileTypes = ["gif", "jpg", "jpeg", "png", "bmp"];
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["avatars"]) && 0 < count($_POST["avatars"])) {
    $action_avatars = $_POST["avatars"];
    $action_type = $_POST["action_type"];
    if ($action_type == "delete") {
        foreach ($action_avatars as $delete_avatar) {
            $__exp = str_replace($AllowedFileTypes, "", $delete_avatar);
            $__exp = explode("_", $__exp);
            $__userid = intval($__exp[1]);
            if (unlink($AvatarPath . $delete_avatar)) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $avatar = '' WHERE `id` = " . $__userid);
            }
        }
    }
    if ($action_type == "add_text") {
        require "./class/class.upload.php";
        foreach ($action_avatars as $_avatar_) {
            $File_Ext = function_149($_avatar_);
            $__exp = str_replace($AllowedFileTypes, "", $_avatar_);
            $__exp = explode("_", $__exp);
            $__userid = intval($__exp[1]);
            $new_name = substr(md5(time()), 0, 15) . "_" . $__userid;
            $uploadHandler = new ImageUpload($AvatarPath . $_avatar_);
            if ($uploadHandler->uploaded) {
                $uploadHandler->image_text = $MAIN["SITENAME"];
                $uploadHandler->image_crop = "0 0 -16 0";
                $uploadHandler->image_background_color = "#000000";
                $uploadHandler->image_text_font = 2;
                $uploadHandler->image_text_position = "B";
                $uploadHandler->image_text_padding_y = 2;
                $uploadHandler->file_new_name_body = $new_name;
                $uploadHandler->process($AvatarPath);
                if ($uploadHandler->processed && unlink($AvatarPath . $_avatar_)) {
                    $Newavatar = $BASEURL . "/include/avatars/" . $new_name . "." . $File_Ext;
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $avatar = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Newavatar) . "' WHERE `id` = '" . $__userid . "'");
                }
            }
        }
    }
    if ($action_type == "resize_add_text") {
        require "./class/class.upload.php";
        foreach ($action_avatars as $_avatar_) {
            $File_Ext = function_149($_avatar_);
            $__exp = str_replace($AllowedFileTypes, "", $_avatar_);
            $__exp = explode("_", $__exp);
            $__userid = intval($__exp[1]);
            $new_name = substr(md5(time()), 0, 15) . "_" . $__userid;
            $uploadHandler = new ImageUpload($AvatarPath . $_avatar_);
            if ($uploadHandler->uploaded) {
                $uploadHandler->image_resize = true;
                $uploadHandler->image_y = $FORUMCP["f_avatar_maxheight"];
                $uploadHandler->image_x = $FORUMCP["f_avatar_maxwidth"];
                $uploadHandler->image_text = $MAIN["SITENAME"];
                $uploadHandler->image_crop = "0 0 -16 0";
                $uploadHandler->image_background_color = "#000000";
                $uploadHandler->image_text_font = 2;
                $uploadHandler->image_text_position = "B";
                $uploadHandler->image_text_padding_y = 2;
                $uploadHandler->file_new_name_body = $new_name;
                $uploadHandler->process($AvatarPath);
                if ($uploadHandler->processed && unlink($AvatarPath . $_avatar_)) {
                    $Newavatar = $BASEURL . "/include/avatars/" . $new_name . "." . $File_Ext;
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $avatar = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Newavatar) . "' WHERE `id` = '" . $__userid . "'");
                }
            }
        }
    }
    if ($action_type == "resize") {
        require "./class/class.upload.php";
        foreach ($action_avatars as $_avatar_) {
            $File_Ext = function_149($_avatar_);
            $__exp = str_replace($AllowedFileTypes, "", $_avatar_);
            $__exp = explode("_", $__exp);
            $__userid = intval($__exp[1]);
            $new_name = substr(md5(time()), 0, 15) . "_" . $__userid;
            $foo = new ImageUpload($AvatarPath . $_avatar_);
            if ($foo->uploaded) {
                $foo->image_resize = true;
                $foo->image_y = $FORUMCP["f_avatar_maxheight"];
                $foo->image_x = $FORUMCP["f_avatar_maxwidth"];
                $foo->file_new_name_body = $new_name;
                $foo->process($AvatarPath);
                if ($foo->processed && unlink($AvatarPath . $_avatar_)) {
                    $Newavatar = $BASEURL . "/include/avatars/" . $new_name . "." . $File_Ext;
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $avatar = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Newavatar) . "' WHERE `id` = '" . $__userid . "'");
                }
            }
        }
    }
}
$_avatars = [];
if ($handle = scandir($AvatarPath)) {
    foreach ($handle as $file) {
        if ($file != "." && $file != ".." && in_array(function_149($file), $AllowedFileTypes)) {
            $_avatars[] = trim($file);
        }
    }
}
$users = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.username, u.id, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.avatar REGEXP '^" . $BASEURL . "/include/avatars/.*\\.(gif|jpg|png|bmp|jpeg)'");
while ($user = mysqli_fetch_assoc($query)) {
    $users[$user["id"]] = "<a $href = \"" . $BASEURL . "/userdetails.php?$id = " . $user["id"] . "\">" . applyUsernameStyle($user["username"], $user["namestyle"]) . "</a>";
}
$str = "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">";
foreach ($_avatars as $avatar) {
    $_exp = str_replace($AllowedFileTypes, "", $avatar);
    $_exp = explode("_", $_exp);
    $_userid = intval($_exp[1]);
    $_sav = $AvatarPath . $avatar;
    $_ad = dbConnect($_sav);
    $str .= "\r\n\t<div class=\"thumb\">\r\n\t\t<ul class=\"userlist userlist_showavatars floatcontainer\" $id = \"buddylist\">\r\n\t\t\t<li $id = \"buddylist_" . $avatar . "\" class=\"alt1\">\r\n\t\t\t\t<div class=\"username\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $id = \"buddylist_usercheck_\"" . $avatar . "\" $name = \"avatars[]\" $value = \"" . $avatar . "\" />\r\n\t\t\t\t\t<label class=\"avatar_label\" for=\"buddylist_usercheck_\"" . $avatar . "\">\r\n\t\t\t\t\t\t<img $style = \"opacity: 1;\" $src = \"" . $_sav . "\" $id = \"buddylist_usercheck_\"" . $avatar . "\" $border = \"0\" $width = \"100\" $height = \"100\" $alt = \"\" $title = \"\" />\r\n\t\t\t\t\t</label>\r\n\t\t\t\t</div>\r\n\t\t\t\t" . $Language[3] . ": " . (dbQuery($_sav) ? "<font $color = \"green\"><b>" . $Language[12] . "</b></font>" : "<font $color = \"red\"><b>" . $Language[13] . "</b></font>") . "<br />\r\n\t\t\t\t" . $Language[4] . ": " . formatBytes(filesize($_sav)) . "<br />\r\n\t\t\t\t" . $Language[5] . ":  " . $_ad["width"] . "x" . $_ad["height"] . "<br />\r\n\t\t\t\t" . $Language[6] . ": " . $_ad["mime"] . "<br />\r\n\t\t\t\t" . $Language[7] . ": <small>" . (isset($users[$_userid]) ? $users[$_userid] : "N/A") . "</small><br />\r\n\t\t\t</li>\r\n\t\t</ul>\r\n\t</div>";
}
$str .= "\r\n\t</td>\r\n</tr>\r\n<tr>\r\n\t<td class=\"alt2\">\r\n\t\t" . $Language[8] . "\r\n\t\t<select $name = \"action_type\">\r\n\t\t\t<option $value = \"resize_add_text\">" . $Language[14] . "</option>\r\n\t\t\t<option $value = \"add_text\">" . $Language[15] . "</option>\r\n\t\t\t<option $value = \"resize\">" . $Language[9] . "</option>\r\n\t\t\t<option $value = \"delete\">" . $Language[10] . "</option>\r\n\t\t</select>\r\n\t\t<input $type = \"submit\" $value = \"" . $Language[11] . "\" class=button $style = \"vertical-align: middle;\" />\r\n\t</td>\r\n</tr>";
echo "\r\n<style $type = \"text/css\">\r\n\tdiv.thumb\r\n\t{\r\n\t\tfloat: left;\r\n\t\tmargin-bottom: 10px;\r\n\t\tmargin-right: 10px;\r\n\t\ttext-align: left;\r\n\t}\r\n\r\n\tdiv.thumb a img\r\n\t{\r\n\t\tborder: none;\r\n\t\tmargin: 0;\r\n\t}\r\n\tul.userlist { list-style:none; padding:0px; margin:0px; }\r\n\tul.userlist li { float:left; margin-right:6px; margin-bottom:6px; width:175px; }\r\n\tul.userlist hr { display:none; }\r\n\r\n\t/* avatars shown */\r\n\tul.userlist_showavatars li { position:relative; width:215px; height:190px; border:solid 1px silver; padding:4px; }\r\n\tul.userlist_showavatars div.username a { padding-left:6px; vertical-align:top; font-weight:bold; }\r\n\r\n\tul.userlist_showavatars div.username input { position:absolute; left:4px; top:4px; }\r\n\tul.userlist_showavatars div.friend label { position:absolute; right:4px; bottom:4px; }\r\n\r\n\t/* avatars hidden */\r\n\tul.userlist_hideavatars li { position:relative; width:215px; border:solid 1px silver; padding:4px; }\r\n\tul.userlist_hideavatars label.avatar_label img { display:none; }\r\n\tul.userlist_hideavatars div.username a { font-weight:bold; }\r\n\t/* Auto-clearing of floats */\r\n\t.floatcontainer:after, .block_row:after {\r\n\t\tcontent: \".\";\r\n\t\tdisplay: block;\r\n\t\theight: 0;\r\n\t\tclear: both;\r\n\t\tvisibility: hidden;\r\n\t}\r\n\t.floatcontainer, .block_row {\r\n\t\tdisplay: inline-block;\r\n\t}\r\n\t/* IE-Mac hide \\*/\r\n\t* html .floatcontainer, * html .block_row {\r\n\t\theight: 1%;\r\n\t}\r\n\t.floatcontainer, .block_row {\r\n\t\tdisplay: block;\r\n\t}\r\n\t/* End IE-Mac hide */\r\n\r\n</style>\r\n<script $type = \"text/javascript\">\r\n\tfunction active_this_image(ImageID)\r\n\t{\r\n\t\tvar $valuEtoChange = document.getElementById(ImageID).style.background;\r\n\t\tif (valuEtoChange.match(/white/) || !valuEtoChange)\r\n\t\t{\r\n\t\t\tdocument.getElementById(ImageID).style.$background = \"#DF7401\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tdocument.getElementById(ImageID).style.$background = \"white\";\r\n\t\t}\r\n\t}\r\n</script>\r\n<form $action = \"index.php?do=manage_avatars\" $method = \"post\" $name = \"manage_avatars\">\r\n\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t" . $str . "\r\n</table>\r\n</form>\r\n";
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
function formatTimestamp($timestamp = "")
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, $timestamp);
}
function formatBytes($bytes = 0)
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 0) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 0, 2) . " TB";
}
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function function_149($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function function_261($image)
{
    if (!($image = trim(file_get_contents($image)))) {
        return false;
    }
    if (preg_match("#(onblur|onchange|onclick|onfocus|onload|onmouseover|onmouseup|onmousedown|onselect|onsubmit|onunload|onkeypress|onkeydown|onkeyup|onresize|alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title)#is", $image)) {
        return false;
    }
    return true;
}
function function_262($image)
{
    if (!($image = getimagesize($image))) {
        return false;
    }
    return ["width" => $image[0], "height" => $image[1], "mime" => $image["mime"]];
}

?>