<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_1();
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["_do"]) && $_POST["_do"] == "login") {
    var_2();
    exit;
}
if (!function_1()) {
    function_2();
    exit;
}
function function_3()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_3("../index.php");
    }
}
function function_4($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_5()
{
    $_SESSION["form_hash"] = md5(time() . var_4() . rand());
}
function function_6($dir)
{
    if (!is_dir($dir)) {
        return "";
    }
    $dl = [];
    if ($directoryEntries = scandir($dir)) {
        foreach ($directoryEntries as $directoryName) {
            if ($directoryName != "." && $directoryName != ".." && is_dir($dir . $directoryName)) {
                $dl[] = $directoryName;
            }
        }
        return $dl;
    } else {
        return "";
    }
}
function function_7()
{
    $availableLanguages = function_6("./languages/");
    if (is_array($availableLanguages)) {
        $languageSelectHtml = "\r\n\t\t<select $name = \"staffcplanguage\" $id = \"staffcplanguage\">";
        foreach ($availableLanguages as $dir) {
            $languageSelectHtml .= "\r\n\t\t\t<option $value = \"" . $dir . "\"" . (isset($_COOKIE["staffcplanguage"]) && $_COOKIE["staffcplanguage"] == $dir ? " $selected = \"selected\"" : "") . ">" . ucfirst($dir) . "</option>";
        }
        $languageSelectHtml .= "\r\n\t\t</select>";
    }
    return $languageSelectHtml;
}
function function_8()
{
    $ip = false;
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $forwardedIpList = explode(", ", $_SERVER["HTTP_X_FORWARDED_FOR"]);
        if ($ip) {
            array_unshift($forwardedIpList, $ip);
            $ip = false;
        }
        for ($i = 0; $i < count($forwardedIpList); $i++) {
            if (!preg_match("/^(?:10|172\\.(?:1[6-9]|2\\d|3[01])|192\\.168)\\./", $forwardedIpList[$i])) {
                if (version_compare(phpversion(), "5.0.0", ">=")) {
                    if (ip2long($forwardedIpList[$i])) {
                        $ip = $forwardedIpList[$i];
                    }
                } else {
                    if (ip2long($forwardedIpList[$i]) != -1) {
                        $ip = $forwardedIpList[$i];
                    }
                }
            }
        }
    }
    return htmlspecialchars($ip ? $ip : $_SERVER["REMOTE_ADDR"]);
}
function function_9($text, $entities = true)
{
    return str_replace(["<", ">", "\"", "'"], ["&lt;", "&gt;", "&quot;", "&#039;"], preg_replace("/&(?!" . ($entities ? "#[0-9]+|shy" : "(#[0-9]+|[a-z]+)") . ";)/si", "&amp;", $text));
}
function function_10($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (\"" . (isset($_SESSION["ADMIN_ID"]) ? intval($_SESSION["ADMIN_ID"]) : 0) . "\", \"" . time() . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "\")");
}
function function_11()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("./languages/" . $_COOKIE["staffcplanguage"]) && is_file("./languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function function_12($Output = "")
{
    $charset = "UTF-8";
    if (is_array($Output)) {
        foreach ($Output as $outputKey => $outputValue) {
            if (!mb_check_encoding($outputValue, "UTF-8")) {
                $Output[$outputKey] = utf8_encode($outputValue);
            }
        }
    } else {
        $Output = mb_check_encoding($Output, "UTF-8") ? $Output : utf8_encode($Output);
    }
    $Output = json_encode($Output);
    ob_start();
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("X-Powered-By: TSSE");
    header("Content-Length: " . strlen($Output));
    header("Content-type: text/plain; $charset = " . $charset);
    header("X-UA-Compatible: IE=edge,$chrome = 1");
    exit($Output);
}
function function_13()
{
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `last_access` = NOW() WHERE `id` = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $_SESSION["ADMIN_ID"]) . "\"");
    $sessionIp = function_8();
    $userAgent = isset($_SERVER["HTTP_USER_AGENT"]) ? function_9(strtolower($_SERVER["HTTP_USER_AGENT"])) : "";
    $querystring = isset($_SERVER["QUERY_STRING"]) ? "?" . function_9($_SERVER["QUERY_STRING"]) : "";
    $scriptName = function_9($_SERVER["SCRIPT_NAME"]);
    mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO ts_sessions VALUES (\"" . md5($sessionIp . $userAgent) . "\", \"" . $_SESSION["ADMIN_ID"] . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $sessionIp) . "\", \"" . time() . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $scriptName . $querystring) . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $userAgent) . "\", \"0\")");
    if (is_dir("./languages/" . $_SESSION["ADMIN_LANGUAGE"]) && is_file("./languages/" . $_SESSION["ADMIN_LANGUAGE"] . "/staffcp.lang")) {
        setcookie("staffcplanguage", $_SESSION["ADMIN_LANGUAGE"], time() + 31536000);
    }
}
function function_1()
{
    $username = isset($_SESSION["ADMIN_USERNAME"]) ? $_SESSION["ADMIN_USERNAME"] : "";
    $passwordHash = isset($_SESSION["ADMIN_PASSWORD"]) ? $_SESSION["ADMIN_PASSWORD"] : "";
    $pincode = isset($_SESSION["ADMIN_PINCODE"]) ? $_SESSION["ADMIN_PINCODE"] : "";
    $language = isset($_SESSION["ADMIN_LANGUAGE"]) ? $_SESSION["ADMIN_LANGUAGE"] : "";
    $isAuthenticated = false;
    if ($username && $passwordHash && $language) {
        $userQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, u.passhash, u.secret, u.usergroup, u.tzoffset, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u INNER JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$username = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "\" AND u.$status = \"confirmed\" AND u.$enabled = \"yes\" LIMIT 1");
        if (mysqli_num_rows($userQuery)) {
            $userRow = mysqli_fetch_assoc($userQuery);
            if (($userRow["cansettingspanel"] == "yes" || $userRow["canstaffpanel"] == "yes" || $userRow["issupermod"] == "yes") && $userRow["passhash"] === $passwordHash) {
                $pincodeQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT sechash, pincode FROM pincode WHERE `area` = \"2\" LIMIT 1");
                if (mysqli_num_rows($pincodeQuery)) {
                    $pincodeRow = mysqli_fetch_assoc($pincodeQuery);
                    if ($pincodeRow["pincode"] === $pincode) {
                        $isAuthenticated = true;
                        $_SESSION["ADMIN_USERNAME"] = $username;
                        $_SESSION["ADMIN_PASSWORD"] = $passwordHash;
                        $_SESSION["ADMIN_GID"] = 0 + $userRow["usergroup"];
                        $_SESSION["ADMIN_ID"] = 0 + $userRow["id"];
                        $_SESSION["ADMIN_TZOFFSET"] = $userRow["tzoffset"];
                        $_SESSION["ADMIN_CANSETTINGSPANEL"] = $userRow["cansettingspanel"] == "yes" ? "yes" : "no";
                        $_SESSION["ADMIN_PINCODE"] = $pincode;
                        $_SESSION["ADMIN_LANGUAGE"] = $language;
                        function_13();
                    }
                }
            }
        }
    }
    return $isAuthenticated;
}
function function_14()
{
    $errorMessage = "";
    if (isset($_POST["form_hash"]) && $_POST["form_hash"] == $_SESSION["form_hash"]) {
        $username = isset($_POST["username"]) ? $_POST["username"] : "";
        $password = isset($_POST["password"]) ? $_POST["password"] : "";
        $pincode = isset($_POST["pincode"]) ? $_POST["pincode"] : "";
        $language = isset($_POST["staffcplanguage"]) ? $_POST["staffcplanguage"] : "";
        $isAuthenticated = false;
        if ($username && $password && $language) {
            $userQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, u.passhash, u.secret, u.usergroup, u.tzoffset, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u INNER JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$username = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "\" AND u.$status = \"confirmed\" AND u.$enabled = \"yes\" LIMIT 1");
            if (mysqli_num_rows($userQuery)) {
                $userRow = mysqli_fetch_assoc($userQuery);
                if (($userRow["cansettingspanel"] == "yes" || $userRow["canstaffpanel"] == "yes" || $userRow["issupermod"] == "yes") && $userRow["passhash"] === md5($userRow["secret"] . $password . $userRow["secret"])) {
                    $pincodeQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT sechash, pincode FROM pincode WHERE `area` = \"2\" LIMIT 1");
                    if (mysqli_num_rows($pincodeQuery)) {
                        $pincodeRow = mysqli_fetch_assoc($pincodeQuery);
                        if ($pincodeRow["pincode"] === md5(md5($pincodeRow["sechash"]) . md5($pincode))) {
                            $isAuthenticated = true;
                            $_SESSION["ADMIN_USERNAME"] = $username;
                            $_SESSION["ADMIN_PASSWORD"] = md5($userRow["secret"] . $password . $userRow["secret"]);
                            $_SESSION["ADMIN_GID"] = 0 + $userRow["usergroup"];
                            $_SESSION["ADMIN_ID"] = 0 + $userRow["id"];
                            $_SESSION["ADMIN_TZOFFSET"] = $userRow["tzoffset"];
                            $_SESSION["ADMIN_CANSETTINGSPANEL"] = $userRow["cansettingspanel"] == "yes" ? "yes" : "no";
                            $_SESSION["ADMIN_PINCODE"] = md5(md5($pincodeRow["sechash"]) . md5($pincode));
                            $_SESSION["ADMIN_LANGUAGE"] = $language;
                            function_13();
                        }
                    }
                }
            }
        }
        if (!$isAuthenticated) {
            $errorMessage = "Login Failed. Your IP has been logged and admins notified of this failed login attempt.";
        }
    } else {
        $errorMessage = "Invalid form hash. Please try again.";
    }
    if ($errorMessage) {
        $languageLines = file("./languages/" . function_11() . "/staffcp.lang");
        $logMessage = str_replace(["{1}", "{2}"], [isset($username) ? $username : "", function_8()], $languageLines[15]);
        function_10($logMessage);
    }
    function_12(["error" => $errorMessage]);
}
function function_2()
{
    var_25();
    $mainConfigQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = \"MAIN\"");
    $configRow = mysqli_fetch_assoc($mainConfigQuery);
    $mainConfig = @unserialize($configRow["content"]);
    $themeConfigQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = \"THEME\"");
    $configRow = mysqli_fetch_assoc($themeConfigQuery);
    $themeConfig = @unserialize($configRow["content"]);
    echo "\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html $xmlns = \"http://www.w3.org/1999/xhtml\" $dir = \"ltr\" $lang = \"en\">\r\n\t\t<head>\r\n\t\t\t<title>Administrator Panel | Sign in to your account</title>\r\n\t\t\t<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = " . $themeConfig["charset"] . "\" />\t\t\t\r\n\t\t\t<link $rel = \"stylesheet\" $type = \"text/css\" $href = \"style/login.css\" />\t\t\t\r\n\t\t\t<script $type = \"text/javascript\" $src = \"./../scripts/jquery-1.11.2.min.js\"></script>\r\n\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tif (window.top !== window.self) {document.$write = \"\";window.top.$location = window.self.location; setTimeout(function(){document.body.$innerHTML = \"\";},1);window.self.$onload = function(evt){document.body.$innerHTML = \"\"};}\r\n\t\t\t</script>\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<div $id = \"ipLogged\"></div>\r\n\t\t\t\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . (isset($_GET["do"]) ? "?do=" . htmlspecialchars($_GET["do"]) : "") . "\" $id = \"loginForm\" $autocomplete = \"off\">\r\n\t\t\t<input $type = \"hidden\" $name = \"form_hash\" $value = \"" . $_SESSION["form_hash"] . "\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"_do\" $value = \"login\" />\r\n\r\n\t\t\t<div $id = \"loginPanel\">\r\n\t\t\t\t<div class=\"loading\"><img $src = \"./images/progress.gif\" $alt = \"\" $title = \"\" /></div>\r\n\r\n\t\t\t\t<input $type = \"text\" $name = \"username\" $id = \"username\" $placeholder = \"username\" $autocomplete = \"off\" />\r\n\r\n\t\t\t\t<input $type = \"password\" $name = \"password\" $id = \"password\" $placeholder = \"password\" $autocomplete = \"off\" />\r\n\r\n\t\t\t\t<input $type = \"password\" $name = \"pincode\" $id = \"pincode\" $placeholder = \"pincode\" />\r\n\r\n\t\t\t\t" . function_7() . "\r\n\r\n\t\t\t\t<input $type = \"submit\" $name = \"submit\" $id = \"submit\" $value = \"SIGN IN\" />\r\n\t\t\t</div>\r\n\t\t\t\r\n\t\t\t</form>\r\n\r\n\t\t\t<div $id = \"copyrightNotice\">\r\n\t\t\t\tPowered by <a $href = \"https://templateshares.net\" $target = \"_blank\">Templateshares Special Edition</a>\r\n\t\t\t</div>\r\n\t\t\t\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\$(document).ready(function()\r\n\t\t\t\t{\r\n\t\t\t\t\t\$(\"#username\").focus().attr(\"autocomplete\", \"off\");\r\n\t\t\t\t});\r\n\t\t\t\t\r\n\t\t\t\t\$(document).on(\"submit\", \"#loginForm\", function(e)\r\n\t\t\t\t{\r\n\t\t\t\t\te.preventDefault();\r\n\r\n\t\t\t\t\t\$(\"#ipLogged\").animate({marginTop: \"-40px\"});\r\n\t\t\t\t\t\r\n\t\t\t\t\tvar \$form = \$(this), \$fields = \$form.serialize();\r\n\r\n\t\t\t\t\t\$(\".loading\").fadeIn(\"slow\");\r\n\t\t\t\t\t\$(\"#username,#password,#submit\").attr(\"disabled\", true);\r\n\r\n\t\t\t\t\t\$.ajax\r\n\t\t\t\t\t({\r\n\t\t\t\t\t\turl: \"" . $_SERVER["SCRIPT_NAME"] . "\",\r\n\t\t\t\t\t\tdata: \$fields,\r\n\t\t\t\t\t\ttype: \"POST\",\r\n\t\t\t\t\t\tdataType: \"json\",\r\n\t\t\t\t\t\tsuccess: function(response)\r\n\t\t\t\t\t\t{\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\tif(response[\"error\"])\r\n\t\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\t\t\$(\"#username,#password,#submit\").attr(\"disabled\", false);\r\n\t\t\t\t\t\t\t\t\$(\".loading\").fadeOut(\"slow\");\r\n\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t\$(\"#ipLogged\").html(response[\"error\"]).animate({marginTop: 0});\r\n\t\t\t\t\t\t\t\tsetTimeout(function()\r\n\t\t\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\t\t\t\$(\"#ipLogged\").animate({marginTop: \"-40px\"});\r\n\t\t\t\t\t\t\t\t}, 10000);\r\n\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t\$('input[$type = \"password\"]').val(\"\");\r\n\t\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\telse\r\n\t\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\t\t\$(\".loading\").hide();\r\n\t\t\t\t\t\t\t\t\$('<div class=\"transparent\" $style = \"width: 100%; height: 100%; position: fixed; top: 0; left: 0; background: #000; z-index: 100;\"></div><div $style = \"position: absolute; top: 35%; left: 0; width: 100%; text-align: center; z-index: 111; color: #fff;\"><img $src = \"./images/ajax_loading.gif\" $alt = \"\" $title = \"\" $style = \"vertical-align: middle; margin-right: 3px;\" /> You have been successfully logged in.</div>').prependTo(\"body\");\r\n\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\tsetTimeout(function()\r\n\t\t\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\t\t\twindow.$location = \"" . $_SERVER["SCRIPT_NAME"] . (isset($_GET["do"]) ? "?do=" . htmlspecialchars($_GET["do"]) : "?$t = " . time()) . "\";\r\n\t\t\t\t\t\t\t\t}, 1000);\r\n\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\treturn;\r\n\t\t\t\t\t\t\t}\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\r\n\t\t\t\t\treturn false;\r\n\t\t\t\t});\r\n\t\t\t</script>\r\n\t\t</body>\r\n\t</head>\r\n\t";
}

?>