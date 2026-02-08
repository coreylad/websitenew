<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function fetch_server_value($name)
{
    if (isset($_SERVER[$name]) && $_SERVER[$name]) {
        return $_SERVER[$name];
    }
    if (isset($_ENV[$name]) && $_ENV[$name]) {
        return $_ENV[$name];
    }
    return false;
}
function buildCurrentURL()
{
    $port = isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] ? intval($_SERVER["SERVER_PORT"]) : 0;
    $port = in_array($port, [80, 443]) ? "" : ":" . $port;
    $scheme = ":443" == $port || isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off" ? "https://" : "http://";
    $host = fetch_server_value("HTTP_HOST");
    $name = fetch_server_value("SERVER_NAME");
    $host = substr_count($name, ".") < substr_count($host, ".") ? $host : $name;
    if (!($scriptpath = fetch_server_value("PATH_INFO")) && !($scriptpath = fetch_server_value("REDIRECT_URL")) && !($scriptpath = fetch_server_value("URL")) && !($scriptpath = fetch_server_value("PHP_SELF"))) {
        $scriptpath = fetch_server_value("SCRIPT_NAME");
    }
    $url = $scheme . $host . "/" . str_replace("index.php", "", ltrim($scriptpath, "/\\"));
    return $url;
}
function stdhead($title = "", $msgalert = true, $includescripts = "")
{
    global $defaultlanguage;
    global $rootpath;
    global $CURUSER;
    global $site_online;
    global $SITENAME;
    global $SITEEMAIL;
    global $BASEURL;
    global $offlinemsg;
    global $disablerightclick;
    global $gzipcompress;
    global $delay;
    global $url;
    global $rootpath;
    global $pic_base_url;
    global $charset;
    global $metadesc;
    global $metakeywords;
    global $lang;
    global $usergroups;
    global $leechwarn_remove_ratio;
    global $cache;
    global $dateformat;
    global $timeformat;
    global $cachetime;
    global $checkconnectable;
    global $timezoneoffset;
    global $TSSEConfig;
    global $is_mod;
    global $staffcp_path;
    global $TSSECache;
    global $iv;
    global $reCAPTCHALanguage;
    if ($site_online != "yes" && isset($CURUSER) && 0 < $CURUSER["id"]) {
        if ($usergroups["canaccessoffline"] != "yes") {
            exit("\r\n\t\t\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r\n\t\t\t<html $xmlns = \"http://www.w3.org/1999/xhtml\">\r\n\t\t\t\t<head>\r\n\t\t\t\t\t<meta http-$equiv = \"content-type\" $content = \"text/html; $charset = " . $charset . "\" />\r\n\t\t\t\t\t<title>OFFLINE</title>\r\n\t\t\t\t\t<style $type = \"text/css\" $media = \"screen\">\r\n\t\t\t\t\t\tbody { margin:50px 0px; padding:0px; text-align:center; }\r\n\t\t\t\t\t</style>\r\n\t\t\t\t</head>\r\n\t\t\t\t<body>\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/misc/ts_message.php\" $border = \"0\" />\r\n\t\t\t\t</body>\r\n\t\t\t</html>");
        }
        $offlinemsg = true;
    }
    $lang->load("header");
    $ts_tzoffset = $CURUSER["tzoffset"] ? $CURUSER["tzoffset"] : $timezoneoffset;
    $title = ($title != "" ? htmlspecialchars_uni($title) : TS_MESSAGE) . " | " . $SITENAME;
    $defaulttemplate = ts_template();
    $inboxpic = isset($CURUSER) && 0 < $CURUSER["pmunread"] ? "<img $border = \"0\" $alt = \"" . sprintf($lang->header["newmessage"], ts_nf($CURUSER["pmunread"])) . "\" $title = \"" . sprintf($lang->header["newmessage"], ts_nf($CURUSER["pmunread"])) . "\" $src = \"" . $BASEURL . "/include/templates/" . $defaulttemplate . "/images/email_add.png\" />" : ($CURUSER ? "<img $border = \"0\" $alt = \"" . $lang->global["nonewmessage"] . "\" $title = \"" . $lang->global["nonewmessage"] . "\" $src = \"" . $BASEURL . "/include/templates/" . $defaulttemplate . "/images/email.png\" />" : "");
    if ($disablerightclick == "yes") {
        $includescripts .= "<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/disablerightclick.js?$v = " . O_SCRIPT_VERSION . "\"></script>";
    }
    if (isset($CURUSER) && 0 < $CURUSER["id"]) {
        $warn = "";
        $medaldon = "";
        include_once INC_PATH . "/functions_ratio.php";
        $ratio = get_user_ratio($CURUSER["uploaded"], $CURUSER["downloaded"], true);
        if ($CURUSER["donor"] == "yes") {
            $medaldon = "<img $src = \"" . $pic_base_url . "star.gif\" $alt = \"" . $lang->global["imgdonated"] . "\" $title = \"" . $lang->global["imgdonated"] . "\" />";
        }
        if ($CURUSER["warned"] == "yes") {
            $warn = "<img $src = \"" . $pic_base_url . "warned.gif\" $alt = \"" . $lang->global["imgwarned"] . "\" $title = \"" . $lang->global["imgwarned"] . "\" />";
        }
        if ($checkconnectable == "yes") {
            require INC_PATH . "/config_announce.php";
            if ($xbt_active != "yes") {
                $connectablequery = sql_query("SELECT id FROM peers WHERE $connectable = 'no' AND $userid = " . $CURUSER["id"]);
                $c_count = mysqli_num_rows($connectablequery);
                if (0 < $c_count) {
                    $connectablealert = sprintf($lang->global["connectablealert"], $c_count, $BASEURL . "/tsf_forums/", $BASEURL . "/faq.php");
                    $warnmessages[] = $connectablealert;
                }
            }
        }
    }
    if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
        global $iv;
        $lang->load("login");
        include_once INC_PATH . "/functions_security.php";
        include_once INC_PATH . "/unregistered.php";
        if (isset($UNREGISTERED)) {
            $includescripts .= $UNREGISTERED;
        }
    }
    if (substr(buildcurrenturl(), 0, 12) != substr($BASEURL, 0, 12) && !headers_sent()) {
        $websiteURL = parse_url($BASEURL);
        if (isset($websiteURL["path"]) && !empty($websiteURL["path"])) {
            $BASEURL = str_replace($websiteURL["path"], "", $BASEURL);
        }
        echo "\r\n\t\t<!DOCTYPE html>\r\n\t\t<html $id = \"TSUE\" $lang = \"en-us\">\r\n\t\t\t<head>\r\n\t\t\t\t<noscript><meta http-$equiv = refresh $content = \"6; URL=" . $BASEURL . $_SERVER["REQUEST_URI"] . "\" /></noscript>\r\n\t\t\t\t<title>Fatal Error</title>\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\tsetTimeout(function()\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\twindow.$location = \"" . $BASEURL . $_SERVER["REQUEST_URI"] . "\";\r\n\t\t\t\t\t}, 3000);\r\n\t\t\t\t</script>\r\n\t\t\t</head>\r\n\t\t\t<body>\r\n\t\t\t\t<div $style = \"border: 3px solid red; text-align: center; font-size: 18px; font-weight: bold; padding: 10px; width: 600px; margin: 100px auto; font-family: 'Trebuchet MS', Helvetica, Arial, sans-serif; line-height: 1.7;\">" . $lang->global["redirecttocorrecturl"] . "</div>\r\n\t\t\t</body>\r\n\t\t</html>";
        exit;
    }
    require INC_PATH . "/templates/" . $defaulttemplate . "/header.php";
}
function stdfoot()
{
    global $SITENAME;
    global $BASEURL;
    global $CURUSER;
    global $rootpath;
    global $lang;
    global $usergroups;
    global $is_mod;
    global $staffcp_path;
    $defaulttemplate = ts_template();
    $alertpm = !TS_Match($_SERVER["SCRIPT_NAME"], "message") && 0 < $CURUSER["pmunread"] && TS_Match($CURUSER["options"], "F1") && !TS_Match($CURUSER["options"], "A1") ? "\r\n\t<script $type = \"text/javascript\">\r\n\t\t//<![CDATA[\r\n\t\tvar $newpm = confirm(\"" . $lang->global["newmessagebox"] . "\")\r\n\t\tif (newpm)\r\n\t\t{\r\n\t\t\twindow.$location = \"" . $BASEURL . "/messages.php\";\r\n\t\t}\r\n\t\t//]]>\r\n\t</script>" : "";
    include INC_PATH . "/templates/" . $defaulttemplate . "/footer.php";
}
function get_user_avatar($AvatarUrl = "", $ForceShow = false, $Awidth = "", $Aheight = "", $style = "")
{
    global $CURUSER;
    if (!TS_Match($CURUSER["options"], "D1") && !$ForceShow) {
        return "";
    }
    if (empty($AvatarUrl)) {
        global $pic_base_url;
        $Avatar = $pic_base_url . "default_avatar.png";
    } else {
        $Avatar = fix_url($AvatarUrl);
    }
    global $f_resizeavatars;
    if ($f_resizeavatars == "yes" && !$Awidth && !$Aheight && !$style) {
        global $f_avatar_maxwidth;
        global $f_avatar_maxheight;
        $Avatar = "<img $src = \"" . $Avatar . "\" $border = \"0\" $alt = \"\" $title = \"\" $width = \"" . $f_avatar_maxwidth . "\" $height = \"" . $f_avatar_maxheight . "\"" . ($style ? " $style = \"" . $style . "\"" : "") . " />";
    } else {
        if ($Awidth && $Aheight && !$style) {
            $Avatar = "<img $src = \"" . $Avatar . "\" $border = \"0\" $alt = \"\" $title = \"\" $width = \"" . $Awidth . "\" $height = \"" . $Aheight . "\"" . ($style ? " $style = \"" . $style . "\"" : "") . " />";
        } else {
            $Avatar = "<img $src = \"" . $Avatar . "\" $border = \"0\" $alt = \"\" $title = \"\"" . ($style ? " $style = \"" . $style . "\"" : "") . " />";
        }
    }
    return $Avatar;
}
function redirect($url, $message = "", $title = "", $iSSeo = false)
{
    global $SITENAME;
    global $BASEURL;
    global $lang;
    global $charset;
    global $useredirectsystem;
    $message = empty($message) ? $lang->global["redirect"] : $message;
    $title = empty($title) ? $SITENAME : $title;
    $url = (!$iSSeo ? $BASEURL . (substr($url, 0, 1) == "/" ? "" : "/") : "") . $url;
    $url = str_replace("&amp;", "&", $url);
    if ($useredirectsystem == "no" && !headers_sent() && !defined("FORCE_REDIRECT_MESSAGE")) {
        header("Location: " . $url);
        exit;
    }
    exit("\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html $lang = \"en\">\r\n\t\t<head>\r\n\t\t\t<title>" . $title . "</title>\r\n\t\t\t<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = " . $charset . "\" />\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"2;URL=" . $url . "\" />\r\n\t\t\t<link $rel = \"stylesheet\" $type = \"text/css\" $href = \"" . $BASEURL . "/style.php?$theme = " . ts_template() . "&$style = style.css\" />\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<div $style = \"margin: auto auto; width: 50%; padding-top: 10px;\" $align = \"center\">\r\n\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" class=\"tborder\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\"><strong><a $href = \"" . $BASEURL . "\">" . $title . "</a></strong></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"trow1\" $align = \"center\"><p>" . $message . "</p></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"trow2\" $align = \"right\"><a $href = \"" . $url . "\" class=\"smalltext\">" . $lang->global["nowaitmessage"] . "</a></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</div>\r\n\t\t</body>\r\n\t</html>");
}
function stdmsg($heading = "", $text = "", $htmlstrip = true, $div = "error")
{
    if ($htmlstrip) {
        $heading = htmlspecialchars_uni($heading);
        $text = htmlspecialchars_uni($text);
    }
    echo show_notice($text, $div == "error" ? true : false, $heading);
}
function stderr($heading = "", $text = "", $htmlstrip = true)
{
    global $lang;
    stdhead($lang->global["error"]);
    stdmsg($heading, $text, $htmlstrip);
    stdfoot();
    exit;
}

?>