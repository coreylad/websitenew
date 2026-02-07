<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_221();
var_222();
function function_58()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        exit;
    }
}
function function_59()
{
    var_223 = "./../include/config_database.php";
    if (!var_224(var_223)) {
        function_60("Database connection failed!");
    }
}
function function_61($PathToConfig = "")
{
    if (is_file($PathToConfig)) {
        require $PathToConfig;
        $GLOBALS["DatabaseConnect"] = @mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
        if ($GLOBALS["DatabaseConnect"]) {
            function_62();
            return true;
        }
    }
    return false;
}
function function_62()
{
    if (MYSQL_CHARSET != "") {
        if (function_exists("mysqli_set_charset")) {
            mysqli_set_charset($GLOBALS["DatabaseConnect"], MYSQL_CHARSET);
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "SET NAMES " . MYSQL_CHARSET);
        }
    }
}
function function_60($message = "")
{
    echo "\r\n\t<html>\r\n\t\t<head>\r\n\t\t\t<title>TS SE: Critical Error!</title>\r\n\t\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\r\n\t\t\t<style>\r\n\t\t\t\t*{padding: 0; margin: 0}\r\n\t\t\t\t.alert\r\n\t\t\t\t{\r\n\t\t\t\t\tfont-weight: bold;\r\n\t\t\t\t\tcolor: #fff;\r\n\t\t\t\t\tfont: 14px verdana, geneva, lucida, \"lucida grande\", arial, helvetica, sans-serif;\r\n\t\t\t\t\tbackground: #ffacac;\r\n\t\t\t\t\ttext-align: center;\t\t\t\t\t\r\n\t\t\t\t\tborder-bottom: 4px solid #000;\r\n\t\t\t\t\tpadding: 20px;\r\n\t\t\t\t}\r\n\t\t\t</style>\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<div class=\"alert\">\r\n\t\t\t\t" . $message . "\r\n\t\t\t</div>\r\n\t\t</body>\r\n\t</html>\r\n\t";
    exit;
}

?>