<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function ts_category_list($selectname = "type", $selected = 0, $extra = "", $style = "specialboxn")
{
    global $usergroups;
    global $cache;
    global $_categoriesS;
    global $_categoriesC;
    global $CURUSER;
    $subcategoriesss = [];
    if (!is_array($_categoriesS) || count($_categoriesS) == 0 || !is_array($_categoriesC) || count($_categoriesC) == 0) {
        require TSDIR . "/" . $cache . "/categories.php";
    }
    if (is_array($_categoriesS) && 0 < count($_categoriesS)) {
        foreach ($_categoriesS as $scquery) {
            if ($scquery["canview"] == "[ALL]" || in_array($CURUSER["usergroup"], explode(",", $scquery["canview"]))) {
                $subcategoriesss[$scquery["pid"]] = (isset($subcategoriesss[$scquery["pid"]]) ? $subcategoriesss[$scquery["pid"]] : "") . "\r\n\t\t\t\t\t<option value=\"" . $scquery["id"] . "\"" . ($scquery["id"] == $selected ? " selected=\"selected\"" : "") . ">&nbsp;&nbsp;|-- " . $scquery["name"] . "</option>\r\n\t\t\t\t\t";
            }
        }
    }
    $showcategories = "<select name=\"" . $selectname . "\" id=\"" . $style . "\">\r\n\t" . $extra;
    if (is_array($_categoriesC) && 0 < count($_categoriesC)) {
        foreach ($_categoriesC as $mcquery) {
            if ($mcquery["canview"] == "[ALL]" || in_array($CURUSER["usergroup"], explode(",", $mcquery["canview"]))) {
                $showcategories .= "\r\n\t\t\t\t<option value=\"" . $mcquery["id"] . "\"" . ($mcquery["id"] == $selected ? " selected=\"selected\"" : "") . ">" . $mcquery["name"] . "</option>\r\n\t\t\t\t" . (isset($subcategoriesss[$mcquery["id"]]) ? $subcategoriesss[$mcquery["id"]] : "") . "\r\n\t\t\t\t";
            }
        }
    }
    $showcategories .= "</select>";
    return $showcategories;
}

?>