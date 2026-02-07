<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/manage_menu.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$Found = "";
$MainMenus = [];
$ParentCetegories = [];
$SubCategories = [];
$usergroups = [];
if ($Act == "new" && ($lang = trim($_GET["language"]))) {
    $title = "";
    $link = "{BASEURL}/";
    $sort = 0;
    $pid = 0;
    $sub = 0;
    $image = "";
    $usergroups = [];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $link = isset($_POST["link"]) ? trim($_POST["link"]) : "";
        $sort = isset($_POST["sort"]) ? intval($_POST["sort"]) : "0";
        $pid = isset($_POST["pid"]) ? intval($_POST["pid"]) : "0";
        $image = isset($_POST["image"]) ? trim($_POST["image"]) : "";
        $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : "[ALL]";
        if (!$title) {
            $Message = $Language[18];
        } else {
            if (!$usergroups || $usergroups == "[ALL]" || count($usergroups) == 0) {
                $usergroups = "[ALL]";
            } else {
                $UGArray = [];
                foreach ($usergroups as $ug) {
                    $UGArray[] = "[" . $ug . "]";
                }
                $usergroups = implode(",", $UGArray);
            }
            if ($pid) {
                $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT pid FROM ts_menu WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . $pid);
                if (0 < mysqli_num_rows($Query)) {
                    $Result = mysqli_fetch_assoc($Query);
                    $Resulttt = $Result["pid"];
                    if ($Resulttt != 0) {
                        $sub = $pid;
                        $pid = 0;
                        $usergroups = "[ALL]";
                    }
                }
            }
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_menu (pid,sort,title,link,image,permissions,sub,language) VALUES (" . $pid . ", " . $sort . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $link) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $image) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $usergroups) . "', " . $sub . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "')");
            function_79(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[24]));
            function_78("index.php?do=manage_menu&$act = manage_menu_items&$language = " . $lang . "&$updated = true");
            exit;
        }
    }
    echo "\r\n\t" . function_81("<a $href = \"index.php?do=manage_menu&$act = manage_menu_items&$language = " . $lang . "\">" . $Language[16] . "</a>") . "\r\n\t" . ($Message ? function_81($Message) : "") . "\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_menu&$act = new&$language = " . $lang . "\" $method = \"post\" $name = \"manage_menu\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\"><b>" . $Language[5] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[22] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[9] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<input $type = \"text\" $size = \"5\" $name = \"sort\" $value = \"" . ($sort ? $sort : "") . "\" />\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"title\" $value = \"" . ($title ? $title : "") . "\" />\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"link\" $value = \"" . ($link ? $link : "") . "\" />\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"image\" $value = \"" . ($image ? $image : "") . "\" />\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t" . function_197($pid) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\"><b>" . $Language[23] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $align = \"center\" $colspan = \"5\">" . function_198($usergroups) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" $align = \"center\" $colspan = \"5\"><input $type = \"submit\" $value = \"" . $Language[17] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
}
if ($Act == "edit" && ($mid = intval($_GET["mid"])) && ($lang = trim($_GET["language"]))) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . $mid);
    if (0 < mysqli_num_rows($Query)) {
        $Details = mysqli_fetch_assoc($Query);
        $title = $Details["title"];
        $link = $Details["link"];
        $sort = $Details["sort"];
        $pid = $Details["pid"];
        $image = $Details["image"];
        $usergroups = $Details["permissions"] && $Details["permissions"] != "[ALL]" ? explode(",", $Details["permissions"]) : "";
        $sub = $Details["sub"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
            $link = isset($_POST["link"]) ? trim($_POST["link"]) : "";
            $sort = isset($_POST["sort"]) ? intval($_POST["sort"]) : "0";
            $pid = isset($_POST["pid"]) ? intval($_POST["pid"]) : "0";
            $image = isset($_POST["image"]) ? trim($_POST["image"]) : "";
            $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : "[ALL]";
            if (!$title) {
                $Message = $Language[18];
            } else {
                if (!$usergroups || $usergroups == "[ALL]" || count($usergroups) == 0) {
                    $usergroups = "[ALL]";
                } else {
                    $UGArray = [];
                    foreach ($usergroups as $ug) {
                        $UGArray[] = "[" . $ug . "]";
                    }
                    $usergroups = implode(",", $UGArray);
                }
                if ($pid) {
                    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT pid FROM ts_menu WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . $pid);
                    if (0 < mysqli_num_rows($Query)) {
                        $Result = mysqli_fetch_assoc($Query);
                        $Resulttt = $Result["pid"];
                        if ($Resulttt != 0) {
                            $sub = $pid;
                            $pid = 0;
                            $usergroups = "[ALL]";
                        }
                    }
                }
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $pid = " . $pid . ", $sort = " . $sort . ", $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $link = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $link) . "', $image = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $image) . "', $permissions = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $usergroups) . "', $sub = " . $sub . " WHERE $mid = " . $mid);
                function_79(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[24]));
                function_78("index.php?do=manage_menu&$act = manage_menu_items&$language = " . $lang . "&$updated = true");
                exit;
            }
        }
        echo "\r\n\t\t\r\n\t\t" . function_81("<a $href = \"index.php?do=manage_menu&$act = manage_menu_items&$language = " . $lang . "\">" . $Language[16] . "</a>") . "\r\n\t\t" . ($Message ? function_81($Message) : "") . "\r\n\t\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_menu&$act = edit&$mid = " . $mid . "&$language = " . $lang . "\" $method = \"post\" $name = \"manage_menu\">\r\n\t\t<input $type = \"hidden\" $name = \"act\" $value = \"edit\" />\r\n\t\t<input $type = \"hidden\" $name = \"mid\" $value = \"" . $mid . "\" />\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[22] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"text\" $size = \"5\" $name = \"sort\" $value = \"" . ($sort ? $sort : "") . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"title\" $value = \"" . ($title ? $title : "") . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"link\" $value = \"" . ($link ? $link : "") . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"image\" $value = \"" . ($image ? $image : "") . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t" . function_197($pid ? $pid : ($sub ? $sub : 0), $mid) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\"><b>" . $Language[23] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\" $colspan = \"5\">" . function_198($usergroups) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" $align = \"center\" $colspan = \"5\"><input $type = \"submit\" $value = \"" . $Language[13] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
    }
}
if ($Act == "delete" && ($mid = intval($_GET["mid"])) && ($lang = trim($_GET["language"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_menu WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . $mid);
    function_79(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[24]));
    $UPDATED = true;
    $Act = "manage_menu_items";
}
if ($Act == "delete_language" && ($lid = intval($_GET["lid"])) && ($lang = trim($_GET["language"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_menu_languages WHERE $lid = " . $lid . " AND  $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "'");
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_menu WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "'");
    function_79(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[24]));
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
    $Result = mysqli_fetch_assoc($Q);
    $MAIN = unserialize($Result["content"]);
    $filename = "../" . $MAIN["cache"] . "/menu_" . $lang . ".php";
    @unlink($filename);
    $UPDATED = true;
    $Act = "";
}
if (isset($_POST["export"]) && $_POST["export"] == "true" && $Act == "manage_menu_items" && ($lang = trim($_GET["language"]))) {
    $sort = $_POST["sort"];
    $pid = $_POST["pid"];
    $title = $_POST["title"];
    $link = $_POST["link"];
    $image = $_POST["image"];
    foreach ($sort as $mid => $value) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $sort = " . intval($value) . " WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . intval($mid));
    }
    foreach ($pid as $mid => $value) {
        if ($value) {
            $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT pid FROM ts_menu WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . $value);
            if (0 < mysqli_num_rows($Query)) {
                $Result = mysqli_fetch_assoc($Query);
                $Resulttt = $Result["pid"];
                if ($Resulttt != 0) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $sub = " . intval($value) . " , $pid = 0 WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . intval($mid));
                } else {
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $pid = " . intval($value) . ", $sub = 0 WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . intval($mid));
                }
            } else {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $pid = " . intval($value) . ", $sub = 0 WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . intval($mid));
            }
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $pid = " . intval($value) . ", $sub = 0 WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . intval($mid));
        }
    }
    foreach ($title as $mid => $value) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], trim($value)) . "' WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . intval($mid));
    }
    foreach ($link as $mid => $value) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $link = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], trim($value)) . "' WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . intval($mid));
    }
    foreach ($image as $mid => $value) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_menu SET $image = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], trim($value)) . "' WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' AND $mid = " . intval($mid));
    }
    $EXPORT = $_POST["language"];
    function_79(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[24]));
    function_199($_POST["language"]);
}
if ($Act == "manage_menu_items" && isset($_GET["language"]) && ($lang = trim($_GET["language"]))) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $pid = 0 AND $sub = 0 AND $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' ORDER by sort");
    if (mysqli_num_rows($Query) == 0) {
        $Message = function_76($Language[1]);
    } else {
        while ($M = mysqli_fetch_assoc($Query)) {
            $MainMenus[$M["mid"]] = $M;
        }
        $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE pid != 0 AND $sub = 0 AND $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' ORDER by sort");
        if (0 < mysqli_num_rows($Query)) {
            while ($PC = mysqli_fetch_assoc($Query)) {
                $ParentCetegories[$PC["pid"]][$PC["mid"]] = $PC;
            }
        }
        $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $pid = 0 AND sub != 0 AND $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' ORDER by sort");
        if (0 < mysqli_num_rows($Query)) {
            while ($PSC = mysqli_fetch_assoc($Query)) {
                $SubCategories[$PSC["sub"]][$PSC["mid"]] = $PSC;
            }
        }
        $Count = 0;
        foreach ($MainMenus as $mid => $menu) {
            $class = $Count % 2 == 1 ? "alt2" : "alt1";
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"text\" $size = \"5\" $name = \"sort[" . $mid . "]\" $value = \"" . $menu["sort"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"title[" . $mid . "]\" $value = \"" . $menu["title"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"link[" . $mid . "]\" $value = \"" . $menu["link"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"image[" . $mid . "]\" $value = \"" . $menu["image"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\">\r\n\t\t\t\t\t" . var_495($mid, $menu["pid"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\"><a $href = \"index.php?do=manage_menu&amp;$act = edit&amp;$mid = " . $mid . "&amp;$language = " . $lang . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[11] . "\" $title = \"" . $Language[11] . "\" $border = \"0\" /></a> <a $href = \"#\" $onclick = \"ConfirmDelete(" . $mid . ", '" . $lang . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[12] . "\" $title = \"" . $Language[12] . "\" $border = \"0\" /></a></td>\r\n\t\t\t</tr>\r\n\t\t\t";
            $Count++;
            if (isset($ParentCetegories[$mid])) {
                foreach ($ParentCetegories[$mid] as $pmid => $pcategory) {
                    $Found .= "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\">\r\n\t\t\t\t\t\t\t- -  <input $type = \"text\" $size = \"2\" $name = \"sort[" . $pmid . "]\" $value = \"" . $pcategory["sort"] . "\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t\t\t- - <input $type = \"text\" $size = \"37\" $name = \"title[" . $pmid . "]\" $value = \"" . $pcategory["title"] . "\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"link[" . $pmid . "]\" $value = \"" . $pcategory["link"] . "\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t\t\t<input $type = \"text\" $size = \"40\" $name = \"image[" . $pmid . "]\" $value = \"" . $pcategory["image"] . "\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\">\r\n\t\t\t\t\t\t\t" . var_495($pmid, $pcategory["pid"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\"><a $href = \"index.php?do=manage_menu&amp;$act = edit&amp;$mid = " . $pmid . "&amp;$language = " . $lang . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[11] . "\" $title = \"" . $Language[11] . "\" $border = \"0\" /></a> <a $href = \"#\" $onclick = \"ConfirmDelete(" . $pmid . ", '" . $lang . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[12] . "\" $title = \"" . $Language[12] . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t";
                    if (isset($SubCategories[$pmid])) {
                        foreach ($SubCategories[$pmid] as $psmid => $pscategory) {
                            $Found .= "\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t\t- - -  <input $type = \"text\" $size = \"1\" $name = \"sort[" . $psmid . "]\" $value = \"" . $pscategory["sort"] . "\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t\t\t\t\t- - - <input $type = \"text\" $size = \"27\" $name = \"title[" . $psmid . "]\" $value = \"" . $pscategory["title"] . "\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"30\" $name = \"link[" . $psmid . "]\" $value = \"" . $pscategory["link"] . "\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"30\" $name = \"image[" . $psmid . "]\" $value = \"" . $pscategory["image"] . "\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t\t" . var_495($psmid, $pscategory["sub"], true) . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\"><a $href = \"index.php?do=manage_menu&amp;$act = edit&amp;$mid = " . $psmid . "&amp;$language = " . $lang . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[11] . "\" $title = \"" . $Language[11] . "\" $border = \"0\" /></a> <a $href = \"#\" $onclick = \"ConfirmDelete(" . $psmid . ", '" . $lang . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[12] . "\" $title = \"" . $Language[12] . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t";
                        }
                    }
                }
            }
        }
    }
    if ($Message) {
        echo "\r\n\t\t" . $Message;
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction ConfirmDelete(MID, LANG)\r\n\t\t\t{\r\n\t\t\t\tif (confirm(\"" . trim($Language[4]) . "\"))\r\n\t\t\t\t{\r\n\t\t\t\t\tTSJump(\"index.php?do=manage_menu&$act = delete&$mid = \"+MID+\"&$language = \"+LANG);\r\n\t\t\t\t}\r\n\t\t\t\telse\r\n\t\t\t\t{\r\n\t\t\t\t\treturn false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t" . function_81("<a $href = \"index.php?do=manage_menu&amp;$act = new&amp;$language = " . $lang . "\">" . $Language[3] . "</a>") . "\r\n\t\t" . (isset($UPDATED) || isset($_GET["updated"]) ? function_81($Language[15]) : "") . "\r\n\t\t" . (isset($EXPORT) ? function_81(str_replace("{1}", "menu_" . $EXPORT . ".php", $Language[21])) : "") . "\r\n\t\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_menu&$act = manage_menu_items&$language = " . $lang . "\" $method = \"post\" $name = \"manage_menu\">\r\n\t\t<input $type = \"hidden\" $name = \"export\" $value = \"true\" />\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"6\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[22] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[10] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" $align = \"center\" $colspan = \"6\">" . $Language[19] . " " . function_200() . " <input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
    }
}
if ($Act == "new_language") {
    $lang = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $lang = trim($_POST["lang"]);
        if (!is_dir("../include/languages/" . $lang) || !file_exists("../include/languages/" . $lang . "/browse.lang.php")) {
            $Message = function_76($Language[34]);
        } else {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu_languages WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "'");
            if (mysqli_num_rows($query)) {
                $Message = function_76($Language[36]);
            } else {
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_menu_languages (language) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "')");
                if (mysqli_insert_id($GLOBALS["DatabaseConnect"])) {
                    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $pid = 0 AND $sub = 0");
                    while ($MainCat = mysqli_fetch_assoc($Query)) {
                        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_menu VALUES (NULL, 0, " . $MainCat["sort"] . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $MainCat["title"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $MainCat["link"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $MainCat["image"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $MainCat["permissions"]) . "', 0, '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "')");
                        $NewMid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                        if ($NewMid) {
                            $SubQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $pid = " . $MainCat["mid"] . " AND $sub = 0");
                            while ($SubCat = mysqli_fetch_assoc($SubQuery)) {
                                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_menu VALUES (NULL, " . $NewMid . ", " . $SubCat["sort"] . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $SubCat["title"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $SubCat["link"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $SubCat["image"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $SubCat["permissions"]) . "', 0, '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "')");
                                $NewSub = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                                if ($NewSub) {
                                    $DeepSubQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $sub = " . $SubCat["mid"] . " AND $pid = 0");
                                    while ($DeepSubCat = mysqli_fetch_assoc($DeepSubQuery)) {
                                        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_menu VALUES (NULL, 0, " . $DeepSubCat["sort"] . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $DeepSubCat["title"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $DeepSubCat["link"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $DeepSubCat["image"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $DeepSubCat["permissions"]) . "', " . $NewSub . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "')");
                                    }
                                }
                            }
                        }
                    }
                    function_79(str_replace(["{1}", "{2}"], [$lang, $_SESSION["ADMIN_USERNAME"]], $Language[35]));
                    function_78("index.php?do=manage_menu&$act = manage_menu_items&$language = " . $lang . "&$updated = true");
                    exit;
                }
            }
        }
    }
    echo "\r\n\t<form $method = \"post\" $action = \"index.php?do=manage_menu&$act = new_language\">\r\n\t" . function_81("<a $href = \"index.php?do=manage_menu\">" . $Language[16] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">" . $Language[30] . "</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[32] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t" . $Language[33] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"lang\" $value = \"" . $lang . "\" $size = \"30\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"></td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[17] . "\" /> <input $type = \"reset\" $value = \"" . $Language[16] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
}
if (!$Act) {
    $FileCount = [];
    $dirs = scandir("../include/languages/");
    foreach ($dirs as $dir) {
        if ($dir != "." && $dir != "..") {
            $scan = scandir("../include/languages/" . $dir);
            foreach ($scan as $scanned) {
                if ($scanned != "." && $scanned != ".." && function_149($scanned) == "php") {
                    if (isset($FileCount[$dir])) {
                        $FileCount[$dir]++;
                    } else {
                        $FileCount[$dir] = 1;
                    }
                }
            }
        }
    }
    $Count = 0;
    $List = "";
    for ($query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu_languages ORDER BY lid, language"); $l = mysqli_fetch_assoc($query); $Count++) {
        $class = $Count % 2 == 1 ? "alt2" : "alt1";
        $List .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"" . $class . "\" $align = \"center\">" . $l["lid"] . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . $l["language"] . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . $FileCount[$l["language"]] . "</td>\r\n\t\t\t<td class=\"" . $class . "\" $align = \"center\"><img $src = \"../include/languages/" . $l["language"] . "/flag/flag.gif\" $border = \"0\" $alt = \"\" $title = \"\" /></td>\r\n\t\t\t<td class=\"" . $class . "\" $align = \"center\"><a $href = \"index.php?do=manage_menu&amp;$act = manage_menu_items&amp;$language = " . $l["language"] . "\"><img $src = \"images/link_go.png\" $border = \"0\" $alt = \"" . $Language[31] . "\" $title = \"" . $Language[31] . "\" /></a> " . ($l["language"] != "english" && $language["lid"] != 1 ? "<a $href = \"#\" $onclick = \"ConfirmDelete(" . $l["lid"] . ", '" . $l["language"] . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[12] . "\" $title = \"" . $Language[12] . "\" $border = \"0\" /></a>" : "") . "</td>\r\n\t\t</tr>";
    }
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction ConfirmDelete(LID, LANG)\r\n\t\t{\r\n\t\t\tif (confirm(\"" . trim($Language[4]) . "\"))\r\n\t\t\t{\r\n\t\t\t\tTSJump(\"index.php?do=manage_menu&$act = delete_language&$lid = \"+LID+\"&$language = \"+LANG);\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . function_81("<a $href = \"index.php?do=manage_menu&amp;$act = new_language\">" . $Language[30] . "</a>") . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\"><b>" . $Language[25] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[26] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[27] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[28] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[29] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[10] . "</b></td>\r\n\t\t</tr>\r\n\t\t" . $List . "\r\n\t</table>";
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
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_201($mid, $selected, $sub = false)
{
    global $lang;
    $MainMenus = [];
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $sub = 0 AND $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' ORDER by sort");
    while ($M = mysqli_fetch_assoc($Query)) {
        $MainMenus[$M["mid"]] = $M;
    }
    $var_496 = "\r\n\t<select $name = \"pid[" . $mid . "]\">\r\n\t\t<option $value = \"0\"" . ($selected == 0 ? " $selected = \"selected\"" : "") . "></option>";
    foreach ($MainMenus as $var_497 => $var_498) {
        $var_496 .= "<option $value = \"" . $var_498["mid"] . "\"" . ($selected == $var_498["mid"] ? " $selected = \"selected\"" : "") . ">" . $var_498["title"] . "</option>";
    }
    $var_496 .= "</select>";
    return $var_496;
}
function function_197($selected, $mid = 0)
{
    global $lang;
    $MainMenus = [];
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $sub = 0 AND $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' ORDER by sort");
    while ($M = mysqli_fetch_assoc($Query)) {
        if ($M["mid"] != $mid) {
            $MainMenus[$M["mid"]] = $M;
        }
    }
    $var_496 = "\r\n\t<select $name = \"pid\">\r\n\t\t<option $value = \"0\"" . ($selected == 0 ? " $selected = \"selected\"" : "") . "></option>";
    foreach ($MainMenus as $var_497 => $var_498) {
        $var_496 .= "<option $value = \"" . $var_498["mid"] . "\"" . ($selected == $var_498["mid"] ? " $selected = \"selected\"" : "") . ">" . $var_498["title"] . "</option>";
    }
    $var_496 .= "</select>";
    return $var_496;
}
function function_199($lang)
{
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
    $Result = mysqli_fetch_assoc($Q);
    $MAIN = unserialize($Result["content"]);
    $filename = "../" . $MAIN["cache"] . "/menu_" . $lang . ".php";
    $MainMenus = [];
    $ParentCetegories = [];
    $SubCategories = [];
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $pid = 0 AND $sub = 0 AND $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' ORDER by sort");
    while ($M = mysqli_fetch_assoc($Query)) {
        $MainMenus[$M["mid"]] = $M;
    }
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE pid != 0 AND $sub = 0 AND $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' ORDER by sort");
    if (0 < mysqli_num_rows($Query)) {
        while ($PC = mysqli_fetch_assoc($Query)) {
            $ParentCetegories[$PC["pid"]][$PC["mid"]] = $PC;
        }
    }
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_menu WHERE $pid = 0 AND sub != 0 AND $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "' ORDER by sort");
    if (0 < mysqli_num_rows($Query)) {
        while ($PSC = mysqli_fetch_assoc($Query)) {
            $SubCategories[$PSC["sub"]][$PSC["mid"]] = $PSC;
        }
    }
    $var_499 = "<?php\r\n/** TS Generated Cache#15 - Do Not Alter\r\n* Cache Name: TS MENU - " . $lang . "\r\n* Generated: " . gmdate("r") . "\r\n*/\r\n?>\r\n<!-- START TSSE MENU -->\r\n\t<ul class=\"TSSEMenu TSSEMenum\">";
    foreach ($MainMenus as $mid => $var_500) {
        if ($var_500["permissions"] != "[ALL]" && $var_500["permissions"]) {
            $var_501 = [];
            $var_502 = explode(",", $var_500["permissions"]);
            foreach ($var_502 as $var_503) {
                $var_501[] = "'" . $var_503 . "'";
            }
            $var_501 = implode(",", $var_501);
        }
        $var_499 .= "\r\n\t\t" . ($var_500["permissions"] != "[ALL]" && $var_500["permissions"] ? "<?php echo (\$CURUSER['usergroup'] && in_array('['.\$CURUSER['usergroup'].']', array(" . $var_501 . ")) ? '" : "") . "<li class=\"TSSEMenui\"><a class=\"TSSEMenui\" $href = \"" . ($var_500["link"] ? str_replace("{BASEURL}", $MAIN["BASEURL"], $var_500["link"]) : "#") . "\">" . ($var_500["image"] ? "<img $src = \"" . $var_500["image"] . "\" $alt = \"\" $title = \"\" $border = \"0\" /> " : "") . (isset($ParentCetegories[$mid]) ? "<span>" . str_replace("'", "\\'", $var_500["title"]) . "</span>" : str_replace("'", "\\'", $var_500["title"])) . "</a>";
        if (isset($ParentCetegories[$mid])) {
            $var_499 .= "\r\n\t\t\t<!--[if lte IE 6]><table><tr><td><![endif]-->\r\n\t\t\t<ul class=\"TSSEMenum\">";
            foreach ($ParentCetegories[$mid] as $pmid => $pcategory) {
                if ($pcategory["permissions"] != "[ALL]" && $pcategory["permissions"]) {
                    $var_504 = [];
                    $var_505 = explode(",", $pcategory["permissions"]);
                    foreach ($var_505 as $var_506) {
                        $var_504[] = "'" . $var_506 . "'";
                    }
                    $var_504 = implode(",", $var_504);
                }
                $var_499 .= "\r\n\t\t\t\t" . ($pcategory["permissions"] != "[ALL]" && $pcategory["permissions"] ? ($var_500["permissions"] != "[ALL]" && $var_500["permissions"] ? "'." : "<?php echo") . "(\$CURUSER['usergroup'] && in_array('['.\$CURUSER['usergroup'].']', array(" . $var_504 . ")) ? '" : "") . "<li class=\"TSSEMenui\"><a class=\"TSSEMenui\" $href = \"" . ($pcategory["link"] ? str_replace("{BASEURL}", $MAIN["BASEURL"], $pcategory["link"]) : "#") . "\">" . ($pcategory["image"] ? "<img $src = \"" . str_replace("{BASEURL}", $MAIN["BASEURL"], $pcategory["image"]) . "\" $alt = \"\" $title = \"\" $border = \"0\" class=\"inlineimg\" /> " : "") . (isset($SubCategories[$pmid]) ? "<span>" : "") . str_replace("'", "\\'", $pcategory["title"]) . (isset($SubCategories[$pmid]) ? "</span>" : "") . "</a>";
                if (isset($SubCategories[$pmid])) {
                    $var_499 .= "\r\n\t\t\t\t\t\t<!--[if lte IE 6]><table><tr><td><![endif]-->\r\n\t\t\t\t\t\t<ul class=\"TSSEMenum\">";
                    foreach ($SubCategories[$pmid] as $psmid => $pscategory) {
                        $var_499 .= "<li class=\"TSSEMenui\"><a class=\"TSSEMenui\" $href = \"" . ($pscategory["link"] ? str_replace("{BASEURL}", $MAIN["BASEURL"], $pscategory["link"]) : "#") . "\">" . str_replace("'", "\\'", $pscategory["title"]) . "</a></li> ";
                    }
                    $var_499 .= "\r\n\t\t\t\t\t\t</ul>\r\n\t\t\t\t\t\t<!--[if lte IE 6]></td></tr></table></a><![endif]-->";
                }
                $var_499 .= "</li>" . ($pcategory["permissions"] != "[ALL]" && $pcategory["permissions"] ? $var_500["permissions"] != "[ALL]" && $var_500["permissions"] ? "' : '').'" : "' : ''); ?>" : "");
            }
            $var_499 .= "\r\n\t\t\t</ul>\r\n\t\t\t<!--[if lte IE 6]></td></tr></table></a><![endif]-->\r\n\t\t</li>" . ($var_500["permissions"] != "[ALL]" && $var_500["permissions"] ? "' : ''); ?>" : "");
        } else {
            $var_499 .= "</li>" . ($var_500["permissions"] != "[ALL]" && $var_500["permissions"] ? "' : ''); ?>" : "");
        }
    }
    $var_499 .= "\r\n\t</ul>\r\n<!-- END TSSE MENU -->";
    file_put_contents($filename, $var_499);
}
function function_200()
{
    global $lang;
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT language FROM ts_menu_languages WHERE $language = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lang) . "'");
    $List = "<select $name = \"language\">";
    while ($lang = mysqli_fetch_assoc($query)) {
        $List .= "<option $value = \"" . $lang["language"] . "\">" . $lang["language"] . "</option>";
    }
    $List .= "</select>";
    return $List;
}
function function_198($usergroups)
{
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
    $var_318 = mysqli_fetch_assoc($query);
    $count = 0;
    $var_423 = "\r\n\t<table>\r\n\t\t<tr>\t";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
    while ($var_424 = mysqli_fetch_assoc($query)) {
        if (!($var_424["cansettingspanel"] == "yes" && $var_318["cansettingspanel"] != "yes" || $var_424["canstaffpanel"] == "yes" && $var_318["canstaffpanel"] != "yes" || $var_424["issupermod"] == "yes" && $var_318["issupermod"] != "yes")) {
            if ($count && $count % 8 == 0) {
                $var_423 .= "</tr><tr>";
            }
            $var_423 .= "<td><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $var_424["gid"] . "\"" . (is_array($usergroups) && count($usergroups) && (in_array($var_424["gid"], $usergroups) || in_array("[" . $var_424["gid"] . "]", $usergroups)) ? " $checked = \"checked\"" : "") . " /></td><td>" . str_replace("{username}", $var_424["title"], $var_424["namestyle"]) . "</td>";
            $count++;
        }
    }
    $var_423 .= "</tr></table>";
    return $var_423;
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_149($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}

?>