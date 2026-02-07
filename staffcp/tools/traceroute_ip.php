<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/traceroute_ip.lang");
$Message = "";
$ip = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $ip = preg_replace("/[^A-Za-z0-9.]/", "", trim($_POST["ip"]));
    if ($ip) {
        if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
            $windows = 1;
            $unix = 0;
        } else {
            $windows = 0;
            $unix = 1;
        }
        if ($unix) {
            system("traceroute " . $ip);
            system("killall -q traceroute");
            $output = ob_get_contents();
        } else {
            system("tracert " . $ip);
            $output = ob_get_contents();
        }
        @ob_end_clean();
        $Message = "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<pre>\r\n\t\t\t\t\t\t" . $output . "\r\n\t\t\t\t\t</pre>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>";
    } else {
        $Message = showAlertError($Language[5]);
    }
    exit($Message);
}
echo "<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=traceroute_ip\" $method = \"post\" $id = \"traceroute_ip\">\r\n";
echo $Message;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[3];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"ip\" $value = \"";
echo htmlspecialchars($ip);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[6];
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[7];
echo "\" $accesskey = \"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n<script $type = \"text/javascript\">\r\n\t\$(\"#traceroute_ip\").submit(function(e)\r\n\t{\r\n\t\te.preventDefault();\r\n\t\tvar \$form = \$(this), \$fields = \$form.serialize();\r\n\t\t\r\n\t\t\$.ajax\r\n\t\t({\r\n\t\t\turl: '";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=traceroute_ip',\r\n\t\t\ttype: 'POST',\r\n\t\t\tdata: \$fields,\r\n\t\t\tsuccess: function(response)\r\n\t\t\t{\r\n\t\t\t\t\$('<div>'+response+'</div>').insertAfter(\$form);\r\n\t\t\t}\r\n\t\t});\r\n\r\n\t\treturn false;\r\n\t});\r\n</script>";
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

?>