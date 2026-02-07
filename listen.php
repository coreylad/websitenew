<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "listen.php");
require "./global.php";
define("L_VERSION", "0.5 by xam");
$Hash = TS_Global("hash");
if (empty($Hash)) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}
$Q = sql_query("SELECT answer FROM ts_captcha WHERE $hash = " . sqlesc($Hash));
if (mysqli_num_rows($Q)) {
    $Result = mysqli_fetch_assoc($Q);
    $Hash = $Result["answer"];
} else {
    $Hash = "";
}
if (empty($Hash) || !createWaveFile($Hash)) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}
$context["browser"] = ["is_opera" => strpos($_SERVER["HTTP_USER_AGENT"], "Opera") !== false, "is_opera6" => strpos($_SERVER["HTTP_USER_AGENT"], "Opera 6") !== false, "is_opera7" => strpos($_SERVER["HTTP_USER_AGENT"], "Opera 7") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "Opera/7") !== false, "is_opera8" => strpos($_SERVER["HTTP_USER_AGENT"], "Opera 8") !== false || strpos($_SERVER["HTTP_USER_AGENT"], "Opera/8") !== false, "is_ie4" => strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 4") !== false && strpos($_SERVER["HTTP_USER_AGENT"], "WebTV") === false, "is_safari" => strpos($_SERVER["HTTP_USER_AGENT"], "Safari") !== false, "is_mac_ie" => strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 5.") !== false && strpos($_SERVER["HTTP_USER_AGENT"], "Mac") !== false, "is_web_tv" => strpos($_SERVER["HTTP_USER_AGENT"], "WebTV") !== false, "is_konqueror" => strpos($_SERVER["HTTP_USER_AGENT"], "Konqueror") !== false, "is_firefox" => strpos($_SERVER["HTTP_USER_AGENT"], "Firefox") !== false, "is_firefox1" => strpos($_SERVER["HTTP_USER_AGENT"], "Firefox/1.") !== false, "is_firefox2" => strpos($_SERVER["HTTP_USER_AGENT"], "Firefox/2.") !== false];
$context["browser"]["is_gecko"] = strpos($_SERVER["HTTP_USER_AGENT"], "Gecko") !== false && !$context["browser"]["is_safari"] && !$context["browser"]["is_konqueror"];
$context["browser"]["is_ie7"] = strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 7") !== false && !$context["browser"]["is_opera"] && !$context["browser"]["is_gecko"] && !$context["browser"]["is_web_tv"];
$context["browser"]["is_ie6"] = strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6") !== false && !$context["browser"]["is_opera"] && !$context["browser"]["is_gecko"] && !$context["browser"]["is_web_tv"];
$context["browser"]["is_ie5.5"] = strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 5.5") !== false && !$context["browser"]["is_opera"] && !$context["browser"]["is_gecko"] && !$context["browser"]["is_web_tv"];
$context["browser"]["is_ie5"] = strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 5.0") !== false && !$context["browser"]["is_opera"] && !$context["browser"]["is_gecko"] && !$context["browser"]["is_web_tv"];
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html $xmlns = \"http://www.w3.org/1999/xhtml\">\r\n\t<head>\r\n\t\t<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = ";
echo $charset;
echo "\" />\r\n\t\t<title>Listen</title>\r\n\t</head>\r\n\t<body $style = \"margin: 1ex;\">\r\n\t\t<div class=\"popuptext\">";
if ($context["browser"]["is_ie"]) {
    echo "\r\n\t\t\t<object $classid = \"clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95\" $type = \"audio/x-wav\">\r\n\t\t\t\t<param $name = \"AutoStart\" $value = \"1\" />\r\n\t\t\t\t<param $name = \"FileName\" $value = \"";
    echo $context["verificiation_sound_href"];
    echo ";$format = .wav\" />\r\n\t\t\t</object>";
} else {
    echo "\r\n\t\t\t<object $type = \"audio/x-wav\" $data = \"";
    echo $context["verificiation_sound_href"];
    echo ";$format = .wav\">\r\n\t\t\t\t<a $href = \"";
    echo $context["verificiation_sound_href"];
    echo ";$format = .wav\">";
    echo $context["verificiation_sound_href"];
    echo ";$format = .wav</a>\r\n\t\t\t</object>";
}
echo "\r\n\t\t\t<br />\r\n\t\t\t<a $href = \"listen.php?$hash = " . $Hash . "\">Play Again</a><br />\r\n\t\t\t<a $href = \"javascript:self.close();\">Close</a><br />\r\n\t\t</div>\r\n\t</body>\r\n</html>";
function createWaveFile($word)
{
    global $rootpath;
    $sound_language = ".english";
    $word = strtolower($word);
    $sound_word = "";
    for ($i = 0; $i < strlen($word); $i++) {
        $sound_letter = implode("", file(INC_PATH . "/captcha_fonts/sound/" . $word[$i] . $sound_language . ".wav"));
        if (strpos($sound_letter, "data") === false) {
            return false;
        }
        $sound_word .= substr($sound_letter, strpos($sound_letter, "data") + 8) . str_repeat(chr(128), rand(700, 710) * 8);
    }
    $sound_header = [16, 0, 0, 0, 1, 0, 1, 0, 64, 31, 0, 0, 64, 31, 0, 0, 1, 0, 8, 0, 100, 97, 116, 97];
    $data_size = strlen($sound_word);
    $file_size = $data_size + 36;
    $i = 0;
    while ($i < $data_size) {
        $sound_word[$i] = chr(ord($sound_word[$i]) + rand(-1, 1));
        $i += rand(1, 10);
    }
    header("Content-type: audio/x-wav");
    header("Content-Length: " . $file_size);
    echo "RIFF";
    echo chr($file_size & 255);
    echo chr(($file_size & 65280) >> 8);
    echo chr(($file_size & 16711680) >> 16);
    echo chr(($file_size & 0) >> 24);
    echo "WAVEfmt ";
    foreach ($sound_header as $char) {
        echo chr($char);
    }
    echo chr($data_size & 255);
    echo chr(($data_size & 65280) >> 8);
    echo chr(($data_size & 16711680) >> 16);
    echo chr(($data_size & 0) >> 24);
    echo $sound_word;
    exit;
}

?>