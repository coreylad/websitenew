<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
if (isset($_GET["getNews"])) {
    function_268();
    echo NEWS;
    exit;
}
echo "<script $type = \"text/javascript\">\r\n\t\$(document).ready(function()\r\n\t{\r\n\t\tsetTimeout(function()\r\n\t\t{\r\n\t\t\t\$.get('index.php?do=latest_news&$getNews = true', function(response)\r\n\t\t\t{\r\n\t\t\t\t\$('.alt1').html(response);\r\n\t\t\t});\r\n\t\t}, 1000);\r\n\t});\r\n</script>\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">Latest TS SE News</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t\t<img $src = \"./images/fb_ajax-loader.gif\" $style = \"vertical-align: middle;\" $alt = \"\" $title = \"\" /> Checking...\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
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
        var_236("../index.php");
    }
}
function redirectTo($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; $url = " . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
    }
    exit;
}
function function_268($url = "http://www.templateshares.info/tsnews/tsnews.txt")
{
    @ini_set("user_agent", "TS_SE via cURL/PHP");
    $data = false;
    $timeout = 10;
    if (function_exists("curl_init") && ($ch = curl_init())) {
        $var_581 = true;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
    }
    if (!isset($var_581) && !$data && ini_get("allow_url_fopen")) {
        $var_99 = ini_set("default_socket_timeout", $timeout);
        $var_568 = "";
        if ($handle = @fopen($url, "rb")) {
            ini_set("default_socket_timeout", $var_99);
            stream_set_timeout($handle, $timeout);
            stream_set_blocking($handle, 0);
            while (!feof($handle)) {
                $var_568 .= fread($handle, 8192);
            }
            fclose($handle);
            $data = $var_568;
            unset($var_568);
        }
    }
    if ($data) {
        define("NEWS", $data);
    } else {
        define("NEWS", "Connection Error!");
    }
}

?>