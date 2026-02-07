<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$gid = isset($_GET["gid"]) ? intval($_GET["gid"]) : (isset($_POST["gid"]) ? intval($_POST["gid"]) : 0);
$Language = file("languages/" . getStaffLanguage() . "/manage_games.lang");
$Message = "";
$List = "";
if ($Act == "delete" && $gid) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gname FROM ts_games WHERE $gid = '" . $gid . "'");
    $Game = mysqli_fetch_assoc($Query);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_games WHERE $gid = '" . $gid . "'");
    $Message = str_replace(["{1}", "{2}"], [$Game["gname"], $_SESSION["ADMIN_USERNAME"]], $Language[1]);
    logStaffAction($Message);
    $Message = showAlertMessage($Message);
}
if ($Act == "reset_champions" && $gid) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gname FROM ts_games WHERE $gid = '" . $gid . "'");
    $Game = mysqli_fetch_assoc($Query);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_games_champions WHERE $gid = '" . $gid . "'");
    $Message = str_replace(["{1}", "{2}"], [$Game["gname"], $_SESSION["ADMIN_USERNAME"]], $Language[31]);
    logStaffAction($Message);
    $Message = showAlertMessage($Message);
}
if ($Act == "reset_score" && $gid) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gname FROM ts_games WHERE $gid = '" . $gid . "'");
    $Game = mysqli_fetch_assoc($Query);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_games_scores WHERE $gid = '" . $gid . "'");
    $Message = str_replace(["{1}", "{2}"], [$Game["gname"], $_SESSION["ADMIN_USERNAME"]], $Language[32]);
    logStaffAction($Message);
    $Message = showAlertMessage($Message);
}
if ($Act == "delete_comments" && $gid) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gname FROM ts_games WHERE $gid = '" . $gid . "'");
    $Game = mysqli_fetch_assoc($Query);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_games_comments WHERE $gid = '" . $gid . "'");
    $Message = str_replace(["{1}", "{2}"], [$Game["gname"], $_SESSION["ADMIN_USERNAME"]], $Language[34]);
    logStaffAction($Message);
    $Message = showAlertMessage($Message);
}
if ($Act == "edit" && $gid) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_games WHERE $gid = '" . $gid . "'");
    if (mysqli_num_rows($Query)) {
        $Game = mysqli_fetch_assoc($Query);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $Game = [];
            $Game["gname"] = isset($_POST["gname"]) ? trim($_POST["gname"]) : "";
            $Game["gwords"] = isset($_POST["gwords"]) ? trim($_POST["gwords"]) : "";
            $Game["gtitle"] = isset($_POST["gtitle"]) ? trim($_POST["gtitle"]) : "";
            $Game["gwidth"] = isset($_POST["gwidth"]) ? intval($_POST["gwidth"]) : "";
            $Game["gheight"] = isset($_POST["gheight"]) ? intval($_POST["gheight"]) : "";
            $Game["sort"] = isset($_POST["sort"]) ? intval($_POST["sort"]) : "";
            $Game["objective"] = isset($_POST["objective"]) ? trim($_POST["objective"]) : "";
            $Game["gkeys"] = isset($_POST["gkeys"]) ? trim($_POST["gkeys"]) : "";
            $Game["savescore"] = isset($_POST["savescore"]) ? intval($_POST["savescore"]) : "";
            $Game["cid"] = isset($_POST["cid"]) ? intval($_POST["cid"]) : 0;
            if ($Game["gname"] && $Game["gwidth"] && $Game["gheight"] && $Game["cid"]) {
                $GameFile = "./../ts_games/game_files/" . $Game["gname"] . ".swf";
                if (is_file($GameFile)) {
                    $Query = [];
                    foreach ($Game as $QName => $QValue) {
                        $Query[] = $QName . " = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $QValue) . "'";
                    }
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_games SET " . implode(", ", $Query) . " WHERE $gid = '" . $gid . "'");
                    $Message = str_replace(["{1}", "{2}"], [$Game["gname"], $_SESSION["ADMIN_USERNAME"]], $Language[13]);
                    logStaffAction($Message);
                    $Message = showAlertMessage($Message);
                    $Done = true;
                } else {
                    $Message = showAlertError($Language[28] . " <b>" . $GameFile . "</b>");
                }
            } else {
                $Message = showAlertError($Language[12]);
            }
        }
        if (!isset($Done)) {
            echo "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_games\">\r\n\t\t\t<input $type = \"hidden\" $name = \"act\" $value = \"edit\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"gid\" $value = \"" . $gid . "\" />\r\n\t\t\t" . showAlertMessage("<a $href = \"index.php?do=manage_games\">" . $Language[17] . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>" . $Language[2] . " - " . $Language[7] . ": " . $Game["gname"] . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[3] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gname\" $value = \"" . $Game["gname"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /> <b><i><small>" . $Language[27] . "</small></i></b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gwords\" $value = \"" . $Game["gwords"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[18] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gtitle\" $value = \"" . $Game["gtitle"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[21] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"objective\" $value = \"" . $Game["objective"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[22] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gkeys\" $value = \"" . $Game["gkeys"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[19] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gwidth\" $value = \"" . intval($Game["gwidth"]) . "\" $size = \"15\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[20] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gheight\" $value = \"" . intval($Game["gheight"]) . "\" $size = \"15\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"sort\" $value = \"" . intval($Game["sort"]) . "\" $size = \"15\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[23] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t<select $name = \"savescore\">\r\n\t\t\t\t\t\t\t<option $value = \"1\"" . ($Game["savescore"] == "1" ? " $selected = \"selected\"" : "") . "\">" . $Language[24] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"0\"" . ($Game["savescore"] == "0" ? " $selected = \"selected\"" : "") . "\">" . $Language[25] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[26] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_158($Game["cid"]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[14] . "\" $accesskey = \"s\" />\r\n\t\t\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[15] . "\" $accesskey = \"r\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "add") {
    $Game = [];
    $Game["gname"] = "";
    $Game["gwords"] = "";
    $Game["gtitle"] = "";
    $Game["gwidth"] = "";
    $Game["gheight"] = "";
    $Game["sort"] = "";
    $Game["objective"] = "";
    $Game["gkeys"] = "";
    $Game["savescore"] = "";
    $Game["cid"] = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $Game = [];
        $Game["gname"] = isset($_POST["gname"]) ? trim($_POST["gname"]) : "";
        $Game["gwords"] = isset($_POST["gwords"]) ? trim($_POST["gwords"]) : "";
        $Game["gtitle"] = isset($_POST["gtitle"]) ? trim($_POST["gtitle"]) : "";
        $Game["gwidth"] = isset($_POST["gwidth"]) ? intval($_POST["gwidth"]) : "";
        $Game["gheight"] = isset($_POST["gheight"]) ? intval($_POST["gheight"]) : "";
        $Game["sort"] = isset($_POST["sort"]) ? intval($_POST["sort"]) : "";
        $Game["objective"] = isset($_POST["objective"]) ? trim($_POST["objective"]) : "";
        $Game["gkeys"] = isset($_POST["gkeys"]) ? trim($_POST["gkeys"]) : "";
        $Game["savescore"] = isset($_POST["savescore"]) ? intval($_POST["savescore"]) : "";
        $Game["cid"] = isset($_POST["cid"]) ? intval($_POST["cid"]) : 0;
        $Game["added"] = time();
        if ($Game["gname"] && $Game["gwidth"] && $Game["gheight"] && $Game["cid"]) {
            $GameFile = "./../ts_games/game_files/" . $Game["gname"] . ".swf";
            if (is_file($GameFile)) {
                $Query = [];
                foreach ($Game as $QName => $QValue) {
                    $Query[] = $QName . " = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $QValue) . "'";
                }
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_games SET " . implode(", ", $Query));
                exit(mysqli_error($GLOBALS["DatabaseConnect"]));
            } else {
                $Message = showAlertError($Language[28] . " <b>" . $GameFile . "</b>");
            }
        } else {
            $Message = showAlertError($Language[12]);
        }
    }
    if (!isset($Done)) {
        echo "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_games\">\r\n\t\t<input $type = \"hidden\" $name = \"act\" $value = \"add\" />\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=manage_games\">" . $Language[17] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>" . $Language[2] . " - " . $Language[9] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[3] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gname\" $value = \"" . $Game["gname"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /> <b><i><small>" . $Language[27] . "</small></i></b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gwords\" $value = \"" . $Game["gwords"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[18] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gtitle\" $value = \"" . $Game["gtitle"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[21] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"objective\" $value = \"" . $Game["objective"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[22] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gkeys\" $value = \"" . $Game["gkeys"] . "\" $size = \"70\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[19] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gwidth\" $value = \"" . intval($Game["gwidth"]) . "\" $size = \"15\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[20] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"gheight\" $value = \"" . intval($Game["gheight"]) . "\" $size = \"15\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"sort\" $value = \"" . intval($Game["sort"]) . "\" $size = \"15\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t<td class=\"alt1\">" . $Language[23] . "</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<select $name = \"savescore\">\r\n\t\t\t\t\t\t<option $value = \"1\"" . ($Game["savescore"] == "1" ? " $selected = \"selected\"" : "") . "\">" . $Language[24] . "</option>\r\n\t\t\t\t\t\t<option $value = \"0\"" . ($Game["savescore"] == "0" ? " $selected = \"selected\"" : "") . "\">" . $Language[25] . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[26] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_158($Game["cid"]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[14] . "\" $accesskey = \"s\" />\r\n\t\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[15] . "\" $accesskey = \"r\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM `ts_games`");
while ($Category = mysqli_fetch_assoc($Query)) {
    $List .= "\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t<a $href = \"./../ts_games/index.php?$cid = " . $Category["cid"] . "\" $target = \"_blank\">" . function_159($Category["cid"]) . "</a>\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . (50 < strlen($Category["gwords"]) ? substr($Category["gwords"], 0, 50) . "..." : $Category["gwords"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<a $href = \"./../ts_games/index.php?$act = play&amp;$gid = " . $Category["gid"] . "\" $target = \"_blank\">" . $Category["gtitle"] . "</a>\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t" . intval($Category["gwidth"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t" . intval($Category["gheight"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t" . ($Category["savescore"] == "1" ? $Language[24] : $Language[25]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t" . intval($Category["sort"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t<a $href = \"index.php?do=manage_games&amp;$act = edit&amp;$gid = " . $Category["gid"] . "\"><img $src = \"./images/tool_edit.png\" $alt = \"" . $Language[7] . "\" $title = \"" . $Language[7] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_games&amp;$act = reset_champions&amp;$gid = " . $Category["gid"] . "\"><img $src = \"./images/award_star_delete.png\" $alt = \"" . $Language[29] . "\" $title = \"" . $Language[29] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_games&amp;$act = reset_score&amp;$gid = " . $Category["gid"] . "\"><img $src = \"./images/chart_curve_delete.png\" $alt = \"" . $Language[30] . "\" $title = \"" . $Language[30] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_games&amp;$act = delete_comments&amp;$gid = " . $Category["gid"] . "\"><img $src = \"./images/comments_delete.png\" $alt = \"" . $Language[3] . "\" $title = \"" . $Language[33] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_games&amp;$act = delete&amp;$gid = " . $Category["gid"] . "\" $onclick = \"return ConfirmDelete();\"><img $src = \"./images/tool_delete.png\" $alt = \"" . $Language[8] . "\" $title = \"" . $Language[8] . "\" $border = \"0\" /></a>\r\n\t\t</td>\r\n\t</tr>";
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction ConfirmDelete()\r\n\t{\r\n\t\$tCheck = confirm(\"" . trim($Language[10]) . "\");\r\n\t\tif (Check)\r\n\t\t\treturn true;\r\n\t\telse\r\n\t\t\treturn false;\r\n\t}\r\n</script>\r\n" . showAlertMessage("<a $href = \"index.php?do=manage_games&amp;$act = add\">" . $Language[9] . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $align = \"center\" $width = \"90%\" $style = \"border-collapse:separate\" class=\"tborder\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"8\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[26] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[4] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[18] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[19] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[20] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[23] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[5] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $List . "\r\n</table>";
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_158($selected = "")
{
    $var_451 = "<select $name = \"cid\">";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT cid, cname FROM ts_games_categories ORDER by `sort`");
    while ($Category = mysqli_fetch_assoc($Query)) {
        $var_451 .= "\r\n\t\t<option $value = \"" . $Category["cid"] . "\"" . ($selected == $Category["cid"] ? " $selected = \"selected\"" : "") . ">" . $Category["cname"] . "</option>";
    }
    $var_451 .= "</select>";
    return $var_451;
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_159($cid)
{
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT cname FROM ts_games_categories WHERE $cid = '" . $cid . "'");
    $var_20 = mysqli_fetch_assoc($Query);
    return $var_20["cname"];
}

?>