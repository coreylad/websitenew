<?php
var_216();
if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {
    function_51($_REQUEST);
    function_51($_GET);
    function_51($_POST);
    function_51($_COOKIE);
    if (is_array($_FILES)) {
        foreach ($_FILES as $key => $val) {
            $_FILES[(string) $key]["tmp_name"] = str_replace("\\", "\\\\", $val["tmp_name"]);
        }
        function_51($_FILES);
    }
}
if (function_exists("get_magic_quotes_runtime") && get_magic_quotes_runtime()) {
    @set_magic_quotes_runtime(false);
    @ini_set("magic_quotes_sybase", 0);
}
function function_52()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_217("../index.php");
    }
}
function function_53($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_51(&$value, $depth = 0)
{
    if (is_array($value)) {
        foreach ($value as $key => $val) {
            if (is_string($val)) {
                $value[(string) $key] = stripslashes($val);
            } else {
                if (is_array($val) && $depth < 10) {
                    function_51($value[(string) $key], $depth + 1);
                }
            }
        }
    }
}

?>