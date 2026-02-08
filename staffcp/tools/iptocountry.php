<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/iptocountry.lang");
$Message = "";
$ip = isset($_GET["ip"]) ? trim($_GET["ip"]) : "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $ip = preg_replace("/[^A-Za-z0-9.]/", "", trim($_POST["ip"]));
    if ($ip) {
        $postResult = function_103("http://ip-to-country.webhosting.info/node/view/36", "ip_address=" . $ip);
        if ($postResult) {
            $regex = "#<b>" . $ip . "</b>(.*).<br><br><img $src = (.*)>#U";
            preg_match_all($regex, $postResult, $result, PREG_SET_ORDER);
            if (isset($result[0][1])) {
                $Res = str_replace(["{1}", "{2}"], [htmlspecialchars($ip), $result[0][1]], $Language[7]);
                echo "\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" $align = \"center\"><b>" . $Language[3] . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $Res . "</td>\r\n\t\t\t\t\t</tr>";
            } else {
                $Message = showAlertError($Language[9]);
            }
        } else {
            $Message = showAlertError($Language[9]);
        }
    } else {
        $Message = showAlertError($Language[4]);
    }
}
echo "<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=iptocountry\" $name = \"iptocountryform\" $method = \"post\" $onsubmit = \"document.iptocountryform.submit.$disabled = true\">\r\n";
echo $Message;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[8];
echo "</b></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[2];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"ip\" $value = \"";
echo htmlspecialchars($ip);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input $type = \"submit\" $name = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[5];
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[6];
echo "\" $accesskey = \"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function function_103($url, $data, $optional_headers = NULL)
{
    $ipAddress = ["http" => ["method" => "POST", "content" => $data]];
    if ($optional_headers !== NULL) {
        $ipAddress["http"]["header"] = $optional_headers;
    }
    $ipLong = stream_context_create($ipAddress);
    $fp = @fopen($url, "rb", false, $ipLong);
    if (!$fp) {
        return "";
    }
    $ipCountry = @stream_get_contents($fp);
    if ($ipCountry === false) {
        return "";
    }
    return $ipCountry;
}

?>