<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function isCaptchaEnabled()
{
    global $iv;
    return $iv == "yes" || $iv == "reCAPTCHA";
}
function show_reCAPTCHA_code($submitbutton = false, $buttonname = "go", $extra = "")
{
    global $reCAPTCHAPublickey;
    global $lang;
    echo "\r\n\t<tr>\r\n\t\t<td class=\"rowhead\">\r\n\t\t\t" . $lang->global["secimage"] . "\r\n\t\t</td>\r\n\t\t<td>\r\n\t\t\t<div class=\"g-recaptcha\" data-$sitekey = \"" . $reCAPTCHAPublickey . "\" data-$theme = \"light\" data-$size = \"compact\"></div>\r\n\t\t</td>\r\n\t</tr>" . ($submitbutton ? "<tr><td class=\"rowhead\">" . $lang->global["seccode"] . "</td><td><input $type = \"submit\" $value = \"" . $buttonname . "\" class=\"button\" " . $extra . " /></td></tr>" : "");
}
function show_image_code($submitbutton = false, $buttonname = "go", $extra = "")
{
    global $iv;
    global $BASEURL;
    global $lang;
    global $pic_base_url;
    global $securehash;
    if ($iv == "reCAPTCHA") {
        show_recaptcha_code($submitbutton, $buttonname, $extra);
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
            echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\t//<![CDATA[\r\n\t\t\tfunction reload ()\r\n\t\t\t{\r\n\t\t\t\tTSGetID('regimage').$src = \"" . $BASEURL . "/ts_image.php?$type = new&\" + (new Date()).getTime();\r\n\t\t\t\tTSGetID('listen').style.$visibility = \"hidden\";\r\n\t\t\t\treturn;\r\n\t\t\t};\r\n\t\t\t//]]>\r\n\t\t</script>\r\n\t\t<tr>\r\n\t\t\t<td class=\"rowhead\">" . $lang->global["secimage"] . "</td>\r\n\t\t\t<td>\r\n\t\t\t\t<table>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $rowspan = \"2\" class=\"none\"><img $src = \"" . $BASEURL . "/ts_image.php?$hash = " . $Hash . "\" $id = \"regimage\" $border = \"0\" $alt = \"\" /></td>\r\n\t\t\t\t\t\t<td class=\"none\"><img $src = \"" . $pic_base_url . "listen.gif\" $border = \"0\" $style = \"cursor:pointer\" $onclick = \"return TSOpenPopup('" . $BASEURL . "/listen.php?$hash = " . $Hash . "', 'listen', 400, 120);\" $alt = \"" . $lang->global["seclisten"] . "\" $title = \"" . $lang->global["seclisten"] . "\" $id = \"listen\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"none\"><img $src = \"" . $pic_base_url . "reload.gif\" $border = \"0\" $style = \"cursor: pointer;\" $onclick = \"javascript:reload();\" $alt = \"" . $lang->global["secimagehint"] . "\" $title = \"" . $lang->global["secimagehint"] . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr><td class=\"rowhead\">" . $lang->global["seccode"] . "</td>\r\n\t\t<td><input $type = \"text\" $size = \"26\" $name = \"imagestring\" class=\"inputPassword\" $value = \"\" />\r\n\t\t" . ($submitbutton ? "<input $type = \"submit\" $value = \"" . $buttonname . "\" class=\"button\" " . $extra . "/>" : "") . "\r\n\t\t</td></tr>";
        }
    }
}
function show_reCAPTCHA_code_editor()
{
    global $reCAPTCHAPublickey;
    global $lang;
    return [$lang->global["secimage"], "<div class=\"g-recaptcha\" data-$theme = \"light\" data-$size = \"compact\" data-$sitekey = \"" . $reCAPTCHAPublickey . "\"></div>"];
}
function show_image_code_editor()
{
    global $iv;
    global $BASEURL;
    global $lang;
    global $pic_base_url;
    global $securehash;
    if ($iv == "reCAPTCHA") {
        return show_recaptcha_code_editor();
    }
    if ($iv == "yes") {
        if (!defined("IMAGE_CODE_GENERATED")) {
            $Code = mksecret(5, false);
            $Hash = md5($securehash . $Code . USERIPADDRESS);
            sql_query("INSERT INTO ts_captcha VALUES ('" . $Hash . "', '" . $Code . "', " . TIMENOW . ")");
        } else {
            $IMAGE_CODE_GENERATED = explode("~", IMAGE_CODE_GENERATED);
            list($Code, $Hash) = $IMAGE_CODE_GENERATED;
        }
        return [$lang->global["secimage"], "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\t//<![CDATA[\r\n\t\t\tfunction reload ()\r\n\t\t\t{\r\n\t\t\t\tTSGetID('regimage').$src = \"" . $BASEURL . "/ts_image.php?$type = new&\" + (new Date()).getTime();\r\n\t\t\t\tTSGetID('listen').style.$visibility = \"hidden\";\r\n\t\t\t\treturn;\r\n\t\t\t};\r\n\t\t\t//]]>\r\n\t\t</script>\r\n\t\t<table>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $rowspan = \"2\" class=\"none\"><img $src = \"" . $BASEURL . "/ts_image.php?$hash = " . $Hash . "\" $id = \"regimage\" $border = \"0\" $alt = \"\" /></td>\r\n\t\t\t\t<td class=\"none\"><img $src = \"" . $pic_base_url . "listen.gif\" $border = \"0\" $style = \"cursor:pointer\" $onclick = \"return TSOpenPopup('" . $BASEURL . "/listen.php?$hash = " . $Hash . "', 'listen', 400, 120);\" $alt = \"" . $lang->global["seclisten"] . "\" $title = \"" . $lang->global["seclisten"] . "\" $id = \"listen\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\"><img $src = \"" . $pic_base_url . "reload.gif\" $border = \"0\" $style = \"cursor: pointer;\" $onclick = \"javascript:reload();\" $alt = \"" . $lang->global["secimagehint"] . "\" $title = \"" . $lang->global["secimagehint"] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>", $lang->global["seccode"], "<input $type = \"text\" $size = \"26\" $name = \"imagestring\" class=\"inputPassword\" $value = \"\" />"];
    }
}
function check_code($imagestring, $where = "signup.php", $maxattemptlog = true, $extra = "", $returnback = false)
{
    global $BASEURL;
    global $iv;
    global $reCAPTCHAPrivatekey;
    global $securehash;
    $__is_valided = false;
    if ($iv == "reCAPTCHA") {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = ["secret" => $reCAPTCHAPrivatekey, "response" => $_POST["g-recaptcha-response"], "remoteip" => USERIPADDRESS];
        $options = ["http" => ["method" => "POST", "content" => http_build_query($data)]];
        $context = stream_context_create($options);
        $verify = file_get_contents($url, false, $context);
        $captcha_success = json_decode($verify);
        $__is_valided = !$captcha_success->success ? false : true;
    } else {
        $Code = $imagestring;
        $Hash = md5($securehash . $Code . USERIPADDRESS);
        $Q = sql_query("SELECT answer FROM ts_captcha WHERE $hash = " . sqlesc($Hash));
        if (mysqli_num_rows($Q)) {
            sql_query("DELETE FROM ts_captcha WHERE $hash = " . sqlesc($Hash));
            $__is_valided = true;
        }
    }
    if ($__is_valided) {
        if ($returnback) {
            return $returnback;
        }
    } else {
        if ($returnback) {
            if ($maxattemptlog) {
                failedlogins("silent");
            }
            return false;
        }
        if ($where == "login.php" && $maxattemptlog) {
            failedlogins("silent");
            Header("Location: " . $BASEURL . "/login.php?$error = 2" . $extra);
            exit;
        }
        if ($where == "recover.php" && $maxattemptlog) {
            failedlogins("silent");
            Header("Location: " . $BASEURL . "/recover.php?$error = 2");
            exit;
        }
        if ($where == "recoverhint.php" && $maxattemptlog) {
            failedlogins("silent");
            Header("Location: " . $BASEURL . "/recoverhint.php?$error = 2");
            exit;
        }
        if (strstr($where, "signup.php")) {
            $ayrac = strstr($where, "?") ? "&" : "?";
            Header("Location: " . $BASEURL . "/" . $where . $ayrac . "error=2");
            exit;
        }
        if ($maxattemptlog) {
            failedlogins("silent");
        }
        $where = $BASEURL . (substr($where, 0, 1) == "/" ? "" : "/") . $where;
        Header("Location: " . $where);
        exit;
    }
}
function remaining($type = "login")
{
    global $maxloginattempts;
    $Query = sql_query("SELECT attempts FROM loginattempts WHERE $ip = " . sqlesc(USERIPADDRESS) . " LIMIT 1");
    $Result = mysqli_fetch_assoc($Query);
    $total = 0 < mysqli_num_rows($Query) ? intval($Result["attempts"]) : 0;
    $left = $maxloginattempts - $total;
    return $left <= 2 ? "<font $color = \"#f90510\">[" . $left . "]</font>" : "<font $color = \"#037621\">[" . $left . "]</font>";
}
function failedloginscheck($type = "Login")
{
    global $maxloginattempts;
    global $BASEURL;
    global $lang;
    $Query = sql_query("SELECT attempts FROM loginattempts WHERE $ip = " . sqlesc(USERIPADDRESS) . " LIMIT 1");
    $Result = mysqli_fetch_assoc($Query);
    $total = 0 < mysqli_num_rows($Query) ? intval($Result["attempts"]) : 0;
    if ($maxloginattempts <= $total) {
        sql_query("UPDATE loginattempts SET $banned = 'yes' WHERE $ip = " . sqlesc(USERIPADDRESS));
        stderr(sprintf($lang->global["xlocked"], $type), sprintf($lang->global["xlocked2"], "<a $href = \"" . $BASEURL . "/unbaniprequest.php\">", "<a $href = \"" . $BASEURL . "/contactus.php\">"), false);
    }
}
function failedlogins($type = "login", $recover = false, $head = true, $msg = false, $uid = 0)
{
    global $BASEURL;
    global $lang;
    global $username;
    global $password;
    global $md5pw;
    global $iphost;
    $added = sqlesc(get_date_time());
    $a = @mysqli_fetch_row(@sql_query("SELECT COUNT(*) FROM loginattempts WHERE $ip = " . @sqlesc(USERIPADDRESS) . " LIMIT 1"));
    if ($a[0] == 0) {
        sql_query("INSERT INTO loginattempts (ip, added, attempts) VALUES (" . sqlesc(USERIPADDRESS) . ", " . $added . ", 1)");
    } else {
        sql_query("UPDATE loginattempts SET $attempts = attempts + 1 WHERE $ip = " . sqlesc(USERIPADDRESS));
    }
    if ($recover) {
        sql_query("UPDATE loginattempts SET $type = 'recover' WHERE $ip = " . sqlesc(USERIPADDRESS));
    }
    if ($msg && $uid) {
        require_once INC_PATH . "/functions_pm.php";
        send_pm($uid, sprintf($lang->global["accountwarn"], $username, $password, $md5pw, USERIPADDRESS, $iphost), $lang->global["warning"]);
    }
    if ($type == "silent" || $type == "login") {
        return NULL;
    }
    stderr($lang->global["error"], $type, false, $head);
}

?>