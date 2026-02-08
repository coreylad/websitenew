<?php
define("SL_VERSION", "0.4 by xam");
// Refactored for PSR-12 compliance and descriptive naming
$language = fixUrl($_GET["language"]);
setcookie("ts_language", $language, time() + 31536000, "/");
if (isset($_GET["redirect"]) && ($redirect = $_GET["redirect"]) && $redirect == "yes") {
    $to = !empty($_SERVER["HTTP_REFERER"]) ? fixUrl($_SERVER["HTTP_REFERER"]) : "index.php";
    header("Location: " . $to);
    exit;
}
echo "\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html $xmlns = \"http://www.w3.org/1999/xhtml\">\r\n\t\t<head>\r\n\t\t\t<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = utf-8\" />\r\n\t\t\t<title>Update Language</title>\r\n\t\t\t<style $type = \"text/css\">\r\n\t\t\t\t<!--\r\n\t\t\t\t.style1\r\n\t\t\t\t{\r\n\t\t\t\t\tcolor: #FF0000;\t\t\t\t\t\r\n\t\t\t\t\tfont-weight: bold;\r\n\t\t\t\t\tfont-size: 10px;\r\n\t\t\t\t}\r\n\t\t\t\tbody,td,th\r\n\t\t\t\t{\r\n\t\t\t\t\tfont-family: Verdana, Arial, Helvetica, sans-serif;\r\n\t\t\t\t}\r\n\t\t\t\t-->\r\n\t\t\t</style>\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetInterval(\"window.close()\",3000);\r\n\t\t\t\topener.location.reload();\r\n\t\t\t</script>\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<span class=\"style1\">Language setting has been updated...</span>\r\n\t\t</body>\r\n\t</html>";
exit;
function fixUrl($url)
{
    $url = htmlspecialchars($url);
    $find = ["&amp;", " ", "  "];
    $replace = ["&", "&nbsp;", "&nbsp;&nbsp;"];
    return str_replace($find, $replace, $url);
}

?>