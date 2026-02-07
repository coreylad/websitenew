<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function ts_category_list2($type = 1, $formname = "usercp")
{
    global $usergroups;
    global $CURUSER;
    global $cache;
    global $_categoriesS;
    global $_categoriesC;
    if (!is_array($_categoriesS) || count($_categoriesS) == 0 || !is_array($_categoriesC) || count($_categoriesC) == 0) {
        require TSDIR . "/" . $cache . "/categories.php";
    }
    $subcategoriesss = [];
    if (is_array($_categoriesS) && 0 < count($_categoriesS)) {
        foreach ($_categoriesS as $scquery) {
            if ($scquery["canview"] == "[ALL]" || in_array($CURUSER["usergroup"], explode(",", $scquery["canview"]))) {
                $subcategoriesss[$scquery["pid"]] = (isset($subcategoriesss[$scquery["pid"]]) ? $subcategoriesss[$scquery["pid"]] : "") . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $valign = \"top\" class=\"none\">\r\n\t\t\t\t\t\t\t<input $type = \"checkbox\" $value = \"" . ($type == 1 ? "yes" : $scquery["id"]) . "\" $checkme = \"group" . $scquery["pid"] . "\" $name = \"" . ($type == 1 ? "cat" . $scquery["id"] : "cat[]") . "\"" . (strpos($CURUSER["notifs"], "[cat" . $scquery["id"] . "]") !== false ? " $checked = \"checked\"" : "") . "> <span $style = \"font-size: 11px; font-weight: normal;\">" . $scquery["name"] . "</span>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>";
            }
        }
    }
    $showcategories = "\r\n\t<div $style = \"border: 1px solid #000;\">\r\n\t\t<table $width = \"100%\" $cellspacing = \"0\" $cellpadding = \"0\" $border = \"0\">\r\n\t\t\t<tr>";
    $count = 0;
    if (is_array($_categoriesC) && 0 < count($_categoriesC)) {
        foreach ($_categoriesC as $mcquery) {
            if ($mcquery["canview"] == "[ALL]" || in_array($CURUSER["usergroup"], explode(",", $mcquery["canview"]))) {
                if ($count % 3 == 0) {
                    $showcategories .= "</tr><tr>";
                }
                $showcategories .= "\r\n\t\t\t\t\t\t<td $valign = \"top\" class=\"none\">\r\n\t\t\t\t\t\t\t<input $type = \"checkbox\" $value = \"yes\" $name = \"cat" . $mcquery["id"] . "\"" . (strpos($CURUSER["notifs"], "[cat" . $mcquery["id"] . "]") !== false ? " $checked = \"checked\"" : "") . " $checkall = \"group" . $mcquery["id"] . "\" $onclick = \"javascript: return select_deselectAll ('" . $formname . "', this, 'group" . $mcquery["id"] . "');\"> <span $style = \"font-size: 12px; font-weight: bold;\">" . $mcquery["name"] . "</span>\r\n\t\t\t\t\t\t\t" . (isset($subcategoriesss[$mcquery["id"]]) ? "\r\n\t\t\t\t\t\t\t<div $style = \"margin-left: 20px;\">\r\n\t\t\t\t\t\t\t\t<table>\r\n\t\t\t\t\t\t\t\t\t" . $subcategoriesss[$mcquery["id"]] . "\r\n\t\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t\t</span>" : "") . "\r\n\t\t\t\t\t\t</td>\t\r\n\t\t\t\t";
                $count++;
            }
        }
    }
    $showcategories .= "\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t</div>";
    return $showcategories;
}

?>