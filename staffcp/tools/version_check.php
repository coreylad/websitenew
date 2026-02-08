<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
if (isset($_GET["checkVersion"])) {
    function_269();
    if (LATEST_VERSION < SHORT_SCRIPT_VERSION) {
        $STATUS = "FAKE";
        $IMAGE = "alert.png";
        $SUGGESTION = "Please visit <a $href = \"https://templateshares.net\">www.templateshares.net</a> for more information!";
    }
    if (SHORT_SCRIPT_VERSION < LATEST_VERSION) {
        $STATUS = "OLD";
        $IMAGE = "alert.png";
        $SUGGESTION = "Please visit <a $href = \"https://templateshares.net\">www.templateshares.net</a> for more information!";
    }
    if (SHORT_SCRIPT_VERSION == LATEST_VERSION) {
        $STATUS = "LATEST";
        $IMAGE = "accept.png";
        $SUGGESTION = "";
    }
    $Output = "\r\n\t<img $src = \"images/tree_ltr.gif\" $border = \"0\" $alt = \"\" $title = \"\" $style = \"vertical-align: middle;\" /> Your Version: <b>TS SE v." . SHORT_SCRIPT_VERSION . "</b><br />\r\n\t<img $src = \"images/tree_ltr.gif\" $border = \"0\" $alt = \"\" $title = \"\" $style = \"vertical-align: middle;\" /> Latest Version: <b>TS SE v." . LATEST_VERSION . "</b><br /><br />\r\n\t<img $src = \"images/" . $IMAGE . "\" $border = \"0\" $alt = \"\" $title = \"\" $style = \"vertical-align: middle;\" /> You are currently using the <b>" . $STATUS . "</b> of TS Special Edition. " . $SUGGESTION;
    exit($Output);
}
echo "<script $type = \"text/javascript\">\r\n\t\$(document).ready(function()\r\n\t{\r\n\t\tsetTimeout(function()\r\n\t\t{\r\n\t\t\t\$.get('index.php?do=version_check&$checkVersion = true', function(response)\r\n\t\t\t{\r\n\t\t\t\t\$('.alt1').html(response);\r\n\t\t\t});\r\n\t\t}, 1000);\r\n\t});\r\n</script>\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">Version Check</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<img $src = \"./images/fb_ajax-loader.gif\" $style = \"vertical-align: middle;\" $alt = \"\" $title = \"\" /> Checking...\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
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
// DEAD CODE: function_269() attempts to fetch version from templateshares.info which is likely defunct. Code still runs but remote endpoint unreachable.
function function_269()
{
    if (function_exists("curl_init")) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://www.templateshares.info/versioncheck/version.txt");
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "TSSE via cURL/PHP");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $actionParam = curl_exec($ch);
        define("LATEST_VERSION", $actionParam);
        curl_close($ch);
    } else {
        $actionParam = @file("http://www.templateshares.info/versioncheck/version.txt");
        define("LATEST_VERSION", $actionParam[0]);
    }
}

?>