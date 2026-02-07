<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "redirector.php");
define("R_VERSION", "1.0 by xam");
if (!function_exists("isSecure")) {
    function isSecure()
    {
        return !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" || $_SERVER["SERVER_PORT"] == 443;
    }
}
if (!function_exists("fix_url")) {
    function fix_url($url)
    {
        $url = htmlspecialchars($url);
        return str_replace(["&amp;", " "], ["&", "&nbsp;"], $url);
    }
}
$mainURL = isset($_GET["url"]) ? fix_url($_GET["url"]) : "";
if (substr($mainURL, 0, 7) != "http://" && substr($mainURL, 0, 8) != "https://" || filter_var($mainURL, FILTER_VALIDATE_URL) === false) {
    exit("Invalid URL!");
}
if (preg_match("@youtube|facebook|google|templateshares@i", $mainURL) || isSecure() && substr($mainURL, 0, 8) != "https://") {
    header("Location: " . $mainURL);
    exit;
}
echo "<!DOCTYPE html>\n\t<html>\n\t<frameset $border = \"1\" $framespacing = \"0\" $rows = \"*,20\" $frameborder = \"0\">\n\t\t<frame $name = \"content\" $marginwidth = \"0\" $marginheight = \"0\" $bottomnargin = \"0\" $src = \"";
echo $mainURL;
echo "\" />\n\t\t<frame $name = \"footer\" $marginwidth = \"0\" $marginheight = \"0\" $src = \"redirector_footer.php?$url = ";
echo $mainURL;
echo "\" $scrolling = \"no\" />\n\t</frameset>\n\t\n\t<noframes><body>Your browser doesn't support frames: ";
echo $mainURL;
echo "</body></noframes>\n</html>";

?>