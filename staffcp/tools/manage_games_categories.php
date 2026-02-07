<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["cid"]) ? intval($_GET["cid"]) : (isset($_POST["cid"]) ? intval($_POST["cid"]) : 0);
$Language = file("languages/" . function_75() . "/manage_games_categories.lang");
$Message = "";
$List = "";
if ($Act == "delete" && $Cid) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT cname FROM ts_games_categories WHERE $cid = '" . $Cid . "'");
    $Category = mysqli_fetch_assoc($Query);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_games_categories WHERE $cid = '" . $Cid . "'");
    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        $Message = str_replace(["{1}", "{2}"], [$Category["cname"], $_SESSION["ADMIN_USERNAME"]], $Language[1]);
        function_79($Message);
        $Message = function_81($Message);
    }
}
if ($Act == "edit" && $Cid) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_games_categories WHERE $cid = '" . $Cid . "'");
    if (mysqli_num_rows($Query)) {
        $Category = mysqli_fetch_assoc($Query);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $Category = [];
            $Category["cname"] = isset($_POST["cname"]) ? trim($_POST["cname"]) : "";
            $Category["description"] = isset($_POST["description"]) ? trim($_POST["description"]) : "";
            $Category["sort"] = isset($_POST["sort"]) ? intval($_POST["sort"]) : "";
            if ($Category["cname"]) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_games_categories SET $cname = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Category["cname"]) . "', $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Category["description"]) . "', $sort = '" . $Category["sort"] . "' WHERE $cid = '" . $Cid . "'");
                $Message = str_replace(["{1}", "{2}"], [$Category["cname"], $_SESSION["ADMIN_USERNAME"]], $Language[13]);
                function_79($Message);
                $Done = true;
                $Message = function_81($Message);
            } else {
                $Message = function_76($Language[12]);
            }
        }
        if (!isset($Done)) {
            echo "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_games_categories\">\r\n\t\t\t<input $type = \"hidden\" $name = \"act\" $value = \"edit\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"cid\" $value = \"" . $Cid . "\" />\r\n\t\t\t" . function_81("<a $href = \"index.php?do=manage_games_categories\">" . $Language[17] . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>" . $Language[2] . " - " . $Language[7] . ": " . htmlspecialchars($Category["cname"]) . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[3] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"cname\" $value = \"" . htmlspecialchars($Category["cname"]) . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"description\" $value = \"" . htmlspecialchars($Category["description"]) . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"sort\" $value = \"" . intval($Category["sort"]) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[14] . "\" $accesskey = \"s\" />\r\n\t\t\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[15] . "\" $accesskey = \"r\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "add") {
    $Category = [];
    $Category["cname"] = "";
    $Category["description"] = "";
    $Category["sort"] = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $Category = [];
        $Category["cname"] = isset($_POST["cname"]) ? trim($_POST["cname"]) : "";
        $Category["description"] = isset($_POST["description"]) ? trim($_POST["description"]) : "";
        $Category["sort"] = isset($_POST["sort"]) ? intval($_POST["sort"]) : "";
        if ($Category["cname"]) {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_games_categories SET $cname = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Category["cname"]) . "', $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Category["description"]) . "', $sort = '" . $Category["sort"] . "'");
            $Message = str_replace(["{1}", "{2}"], [$Category["cname"], $_SESSION["ADMIN_USERNAME"]], $Language[16]);
            function_79($Message);
            $Message = function_81($Message);
            $Done = true;
        } else {
            $Message = function_76($Language[12]);
        }
    }
    if (!isset($Done)) {
        echo "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_games_categories\">\r\n\t\t<input $type = \"hidden\" $name = \"act\" $value = \"add\" />\r\n\t\t" . function_81("<a $href = \"index.php?do=manage_games_categories\">" . $Language[17] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>" . $Language[2] . " - " . $Language[9] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[3] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"cname\" $value = \"" . htmlspecialchars($Category["cname"]) . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"description\" $value = \"" . htmlspecialchars($Category["description"]) . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"sort\" $value = \"" . intval($Category["sort"]) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[14] . "\" $accesskey = \"s\" />\r\n\t\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[15] . "\" $accesskey = \"r\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$CategoryCount = [];
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT cid FROM ts_games");
while ($CategoryC = mysqli_fetch_assoc($Query)) {
    if (isset($CategoryCount[$CategoryC["cid"]])) {
        $CategoryCount[$CategoryC["cid"]]++;
    } else {
        $CategoryCount[$CategoryC["cid"]] = 1;
    }
}
$ChampionCount = [];
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT champ.gid, game.cid FROM ts_games_champions champ LEFT JOIN ts_games game ON (champ.$gid = game.gid)");
while ($CategoryC = mysqli_fetch_assoc($Query)) {
    if (isset($ChampionCount[$CategoryC["cid"]])) {
        $ChampionCount[$CategoryC["cid"]]++;
    } else {
        $ChampionCount[$CategoryC["cid"]] = 1;
    }
}
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM `ts_games_categories`");
while ($Category = mysqli_fetch_assoc($Query)) {
    $List .= "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . htmlspecialchars($Category["cname"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . htmlspecialchars($Category["description"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t" . intval($Category["sort"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t" . (isset($CategoryCount[$Category["cid"]]) ? $CategoryCount[$Category["cid"]] : 0) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t" . (isset($ChampionCount[$Category["cid"]]) ? $ChampionCount[$Category["cid"]] : 0) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t<a $href = \"index.php?do=manage_games_categories&amp;$act = edit&amp;$cid = " . $Category["cid"] . "\"><img $src = \"./images/tool_edit.png\" $alt = \"" . $Language[7] . "\" $title = \"" . $Language[7] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_games_categories&amp;$act = delete&amp;$cid = " . $Category["cid"] . "\" $onclick = \"return ConfirmDelete();\"><img $src = \"./images/tool_delete.png\" $alt = \"" . $Language[8] . "\" $title = \"" . $Language[8] . "\" $border = \"0\" /></a>\r\n\t\t</td>\r\n\t</tr>";
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction ConfirmDelete()\r\n\t{\r\n\t\$tCheck = confirm(\"" . trim($Language[10]) . "\");\r\n\t\tif (Check)\r\n\t\t\treturn true;\r\n\t\telse\r\n\t\t\treturn false;\r\n\t}\r\n</script>\r\n" . function_81("<a $href = \"index.php?do=manage_games_categories&amp;$act = add\">" . $Language[9] . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $align = \"center\" $width = \"90%\" $style = \"border-collapse:separate\" class=\"tborder\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"6\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[4] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[5] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[18] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[19] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $List . "\r\n</table>";
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
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>