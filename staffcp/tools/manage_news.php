<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$Language = file("languages/" . function_75() . "/manage_news.lang");
$Message = "";
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
$Result = mysqli_fetch_assoc($Q);
$MAIN = unserialize($Result["content"]);
if ($Act == "delete" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title FROM news WHERE $id = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $News = mysqli_fetch_assoc($query);
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM news WHERE $id = '" . $Cid . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Message = str_replace(["{1}", "{2}"], [$News["title"], $_SESSION["ADMIN_USERNAME"]], $Language[4]);
            function_79($Message);
            function_270();
            $Message = function_76($Message);
        }
    }
}
if ($Act == "edit" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM news WHERE $id = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $News = mysqli_fetch_assoc($query);
        $title = $News["title"];
        $body = $News["body"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
            $body = isset($_POST["body"]) ? trim($_POST["body"]) : "";
            if ($title && $body) {
                $Changes = [];
                foreach ($_POST as $name => $value) {
                    $Changes[] = "`" . $name . "` = '" . (!empty($value) ? mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) : $value) . "'";
                }
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE news SET " . implode(", ", $Changes) . " WHERE $id = '" . $Cid . "'");
                function_270();
                $Message = str_replace(["{1}", "{2}"], [$News["title"], $_SESSION["ADMIN_USERNAME"]], $Language[5]);
                function_79($Message);
                $Message = function_76($Message);
                $Done = true;
            } else {
                $Message = function_76($Language[9]);
            }
        }
        if (!isset($Done)) {
            echo function_90() . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_news&$act = edit&$id = " . $Cid . "\">\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $Language[11] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"body\" $style = \"width: 100%; height: 100px;\">" . htmlspecialchars($body) . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('body');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[13] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "new") {
    $title = "";
    $body = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $body = isset($_POST["body"]) ? trim($_POST["body"]) : "";
        if (!$title || !$body) {
            $Message = function_76($Language[9]);
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO news (userid, added, body, title) VALUES ('" . $_SESSION["ADMIN_ID"] . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $body) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "')");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                function_270();
                $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[6]);
                function_79($Message);
                $Message = function_76($Message);
                $Done = true;
            }
        }
    }
    if (!isset($Done)) {
        echo function_90() . "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_news&$act = new\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[18] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea $name = \"body\" $style = \"width: 100%; height: 100px;\">" . htmlspecialchars($body) . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('body');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[13] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$Found = "";
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT news.*,  u.username, g.namestyle FROM news LEFT JOIN users u ON (news.$userid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) ORDER by added DESC");
if (0 < mysqli_num_rows($Query)) {
    while ($News = mysqli_fetch_assoc($Query)) {
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $News["title"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $News["username"] . "\">" . function_83($News["username"], $News["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . function_84($News["added"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . substr(strip_tags($News["body"]), 0, 100) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_news&amp;$act = edit&amp;$id = " . $News["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[11] . "\" $title = \"" . $Language[11] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_news&amp;$act = delete&amp;$id = " . $News["id"] . "\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[12] . "\" $title = \"" . $Language[12] . "\" $border = \"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
} else {
    $Found .= "<tr><td $colspan = \"5\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_news&amp;$act = new", $Language[19]) . "</td></tr>";
}
echo function_81("<a $href = \"index.php?do=manage_news&amp;$act = new\">" . $Language[18] . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\">" . $Language[2] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[7] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[16] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[15] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[8] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[17] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>";
function function_90($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $var_81 = ob_get_contents();
    ob_end_clean();
    return $var_81;
}
function function_75()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function function_77()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_236("../index.php");
    }
}
function function_78($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_84($timestamp = "")
{
    $var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($var_265, $timestamp);
}
function function_270()
{
    global $MAIN;
    $var_393 = $MAIN["cache"];
    if (file_exists("../" . $var_393 . "/news.html")) {
        unlink("../" . $var_393 . "/news.html");
    }
}
function function_83($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>