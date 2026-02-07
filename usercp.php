<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "usercp.php");
define("UCP_VERSION", "2.3.7 by xam");
require "./global.php";
if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("usercp");
// Refactored for clarity and PSR-12 compliance
$userAction = htmlspecialchars_uni(TS_Global("act"));
$userDoAction = htmlspecialchars_uni(TS_Global("do"));
$userId = intval($CURUSER["id"]);
$isStaff = $is_mod;
$pageContents = [];
$mainContent = $subscribedThreads = $subscribedTorrents = "";
$allowedImageTypes = ["gif", "jpg", "png"];
if ($act == "customize_profile") {
    if (!customizeprofilepermissions("cancustomizeprofile")) {
        print_no_permission();
    }
    $AllowedFonts = ["Arial, Helvetica, sans-serif", "'Times New Roman', Times, serif", "'Courier New', Courier, monospace", "Georgia, 'Times New Roman', Times, serif", "Verdana, Arial, Helvetica, sans-serif", "Tahoma, Verdana, Arial, Helvetica, sans-serif", "Geneva, Arial, Helvetica, sans-serif", "'Trebuchet MS', Arial, Helvetica, sans-serif", "'Comic Sans MS', Arial, Helvetica, sans-serif"];
    $AllowedSizes = ["small", "medium", "large"];
    $ValidFields = ["bg_color", "left_side_bg_color", "table_bg_color", "thead_bg_color", "sub_header_bg_color", "link_color", "text_color", "border_color", "font_family", "font_size"];
    if ($do == "save_customize_profile" && strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["colors"]) && count($_POST["colors"])) {
        $Query = [];
        foreach ($_POST["colors"] as $name => $value) {
            if (in_array($name, $ValidFields)) {
                if ($name == "font_family" && (!in_array($value, $AllowedFonts) || !customizeprofilepermissions("caneditfontfamily"))) {
                    $value = "";
                } else {
                    if ($name == "font_size" && (!in_array($value, $AllowedSizes) || !customizeprofilepermissions("caneditfontsize"))) {
                        $value = "";
                    } else {
                        if (!customizeprofilepermissions("caneditcolors")) {
                            $value = "";
                        }
                    }
                }
                $Query[] = $name . " = " . sqlesc(htmlspecialchars_uni($value));
            }
        }
        $Query = implode(",", $Query);
        sql_query("REPLACE INTO ts_custom_profiles SET $userid = " . sqlesc($CURUSER["id"]) . ", " . $Query) || sqlerr(__FILE__, 94);
        unset($Query);
        unset($name);
        unset($value);
        define("FORCE_REDIRECT_MESSAGE", true);
        redirect("usercp.php?$act = customize_profile", $lang->usercp["saved1"]);
        exit;
    } else {
        $Query = sql_query("SELECT * FROM ts_custom_profiles WHERE `userid` = " . sqlesc($CURUSER["id"]));
        if (mysqli_num_rows($Query)) {
            $UserProfileOptions = mysqli_fetch_assoc($Query);
        } else {
            $UserProfileOptions = [];
            $UserProfileOptions["bg_color"] = "";
            $UserProfileOptions["left_side_bg_color"] = "";
            $UserProfileOptions["table_bg_color"] = "";
            $UserProfileOptions["thead_bg_color"] = "";
            $UserProfileOptions["sub_header_bg_color"] = "";
            $UserProfileOptions["link_color"] = "";
            $UserProfileOptions["text_color"] = "";
            $UserProfileOptions["border_color"] = "";
            $UserProfileOptions["font_family"] = "";
            $UserProfileOptions["font_size"] = "";
        }
        $main .= "\r\n\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/jscolor/jscolor.js\"></script>\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = customize_profile&do=save_customize_profile\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"customize_profile\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_customize_profile\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["aoptions"] . "</legend>\r\n\t\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\" class=\"none\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg2"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg3"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[bg_color]\" $value = \"" . $UserProfileOptions["bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[left_side_bg_color]\" $value = \"" . $UserProfileOptions["left_side_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[table_bg_color]\" $value = \"" . $UserProfileOptions["table_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg4"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg5"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg6"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[thead_bg_color]\" $value = \"" . $UserProfileOptions["thead_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[sub_header_bg_color]\" $value = \"" . $UserProfileOptions["sub_header_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[link_color]\" $value = \"" . $UserProfileOptions["link_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg7"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg8"] . "</td>" : "") . "\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"50%\">" . (customizeprofilepermissions("caneditfontfamily") ? $lang->usercp["cuprobg9"] . " / " : "") . (customizeprofilepermissions("caneditfontsize") ? $lang->usercp["cuprobg10"] : "") . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[text_color]\" $value = \"" . $UserProfileOptions["text_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[border_color]\" $value = \"" . $UserProfileOptions["border_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>" : "") . "\r\n\t\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditfontfamily") ? "\r\n\t\t\t\t\t\t\t\t\t\t<select $name = \"colors[font_family]\">\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"\">&nbsp;</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Arial, Helvetica, sans-serif\"" . ($UserProfileOptions["font_family"] == "Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Arial</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"'Times New Roman', Times, serif\"" . ($UserProfileOptions["font_family"] == "'Times New Roman', Times, serif" ? " $selected = \"selected\"" : "") . ">Times New Roman</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"'Courier New', Courier, monospace\"" . ($UserProfileOptions["font_family"] == "'Courier New', Courier, monospace" ? " $selected = \"selected\"" : "") . ">Courier New</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Georgia, 'Times New Roman', Times, serif\"" . ($UserProfileOptions["font_family"] == "Georgia, 'Times New Roman', Times, serif" ? " $selected = \"selected\"" : "") . ">Georgia</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Verdana, Arial, Helvetica, sans-serif\"" . ($UserProfileOptions["font_family"] == "Verdana, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Verdana</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Tahoma, Verdana, Arial, Helvetica, sans-serif\"" . ($UserProfileOptions["font_family"] == "Tahoma, Verdana, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Tahoma</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Geneva, Arial, Helvetica, sans-serif\"" . ($UserProfileOptions["font_family"] == "Geneva, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Geneva</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"'Trebuchet MS', Arial, Helvetica, sans-serif\"" . ($UserProfileOptions["font_family"] == "'Trebuchet MS', Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Trebuchet MS</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"'Comic Sans MS', Arial, Helvetica, sans-serif\"" . ($UserProfileOptions["font_family"] == "'Comic Sans MS', Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Comic Sans MS</option>\r\n\t\t\t\t\t\t\t\t\t\t</select>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditfontsize") ? "\r\n\t\t\t\t\t\t\t\t\t\t<select $name = \"colors[font_size]\">\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"\">&nbsp;</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"small\"" . ($UserProfileOptions["font_size"] == "small" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["small"] . "</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"medium\"" . ($UserProfileOptions["font_size"] == "medium" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["medium"] . "</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"large\"" . ($UserProfileOptions["font_size"] == "large" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["large"] . "</option>\r\n\t\t\t\t\t\t\t\t\t\t</select>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t<br />\r\n\t<img $src = \"" . $pic_base_url . "example.png\" $alt = \"\" $title = \"\" $border = \"0\" />";
        $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["cupro1"], "main" => $main];
    }
}
if ($act == "edit_newsletter") {
    if ($userAction == "customize_profile") {
        if (!customizeprofilepermissions("cancustomizeprofile")) {
            print_no_permission();
        }
        $allowedFonts = ["Arial, Helvetica, sans-serif", "'Times New Roman', Times, serif", "'Courier New', Courier, monospace", "Georgia, 'Times New Roman', Times, serif", "Verdana, Arial, Helvetica, sans-serif", "Tahoma, Verdana, Arial, Helvetica, sans-serif", "Geneva, Arial, Helvetica, sans-serif", "'Trebuchet MS', Arial, Helvetica, sans-serif", "'Comic Sans MS', Arial, Helvetica, sans-serif"];
        $allowedSizes = ["small", "medium", "large"];
        $validFields = ["bg_color", "left_side_bg_color", "table_bg_color", "thead_bg_color", "sub_header_bg_color", "link_color", "text_color", "border_color", "font_family", "font_size"];
        if ($userDoAction == "save_customize_profile" && strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["colors"]) && count($_POST["colors"])) {
            $profileQuery = [];
            foreach ($_POST["colors"] as $fieldName => $fieldValue) {
                if (in_array($fieldName, $validFields)) {
                    if ($fieldName == "font_family" && (!in_array($fieldValue, $allowedFonts) || !customizeprofilepermissions("caneditfontfamily"))) {
                        $fieldValue = "";
                    } else {
                        if ($fieldName == "font_size" && (!in_array($fieldValue, $allowedSizes) || !customizeprofilepermissions("caneditfontsize"))) {
                            $fieldValue = "";
                        } else {
                            if (!customizeprofilepermissions("caneditcolors")) {
                                $fieldValue = "";
                            }
                        }
                    }
                    $profileQuery[] = $fieldName . " = " . sqlesc(htmlspecialchars_uni($fieldValue));
                }
            }
            $profileQuery = implode(",", $profileQuery);
            sql_query("REPLACE INTO ts_custom_profiles SET $userid = " . sqlesc($CURUSER["id"]) . ", " . $profileQuery) || sqlerr(__FILE__, 94);
            unset($profileQuery);
            unset($fieldName);
            unset($fieldValue);
            define("FORCE_REDIRECT_MESSAGE", true);
            redirect("usercp.php?$act = customize_profile", $lang->usercp["saved1"]);
            exit;
        } else {
            $profileQuery = sql_query("SELECT * FROM ts_custom_profiles WHERE `userid` = " . sqlesc($CURUSER["id"]));
            if (mysqli_num_rows($profileQuery)) {
                $userProfileOptions = mysqli_fetch_assoc($profileQuery);
            } else {
                $userProfileOptions = [];
                $userProfileOptions["bg_color"] = "";
                $userProfileOptions["left_side_bg_color"] = "";
                $userProfileOptions["table_bg_color"] = "";
                $userProfileOptions["thead_bg_color"] = "";
                $userProfileOptions["sub_header_bg_color"] = "";
                $userProfileOptions["link_color"] = "";
                $userProfileOptions["text_color"] = "";
                $userProfileOptions["border_color"] = "";
                $userProfileOptions["font_family"] = "";
                $userProfileOptions["font_size"] = "";
            }
            $mainContent .= "\r\n\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/jscolor/jscolor.js\"></script>\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = customize_profile&do=save_customize_profile\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"customize_profile\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_customize_profile\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["aoptions"] . "</legend>\r\n\t\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\" class=\"none\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg2"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg3"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[bg_color]\" $value = \"" . $userProfileOptions["bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[left_side_bg_color]\" $value = \"" . $userProfileOptions["left_side_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[table_bg_color]\" $value = \"" . $userProfileOptions["table_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg4"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg5"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg6"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[thead_bg_color]\" $value = \"" . $userProfileOptions["thead_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[sub_header_bg_color]\" $value = \"" . $userProfileOptions["sub_header_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[link_color]\" $value = \"" . $userProfileOptions["link_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg7"] . "</td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg8"] . "</td>" : "") . "\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"50%\">" . (customizeprofilepermissions("caneditfontfamily") ? $lang->usercp["cuprobg9"] . " / " : "") . (customizeprofilepermissions("caneditfontsize") ? $lang->usercp["cuprobg10"] : "") . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[text_color]\" $value = \"" . $userProfileOptions["text_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[border_color]\" $value = \"" . $userProfileOptions["border_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t</td>" : "") . "\r\n\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditfontfamily") ? "\r\n\t\t\t\t\t\t\t\t\t<select $name = \"colors[font_family]\">\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"\">&nbsp;</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Arial</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"'Times New Roman', Times, serif\"" . ($userProfileOptions["font_family"] == "'Times New Roman', Times, serif" ? " $selected = \"selected\"" : "") . ">Times New Roman</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"'Courier New', Courier, monospace\"" . ($userProfileOptions["font_family"] == "'Courier New', Courier, monospace" ? " $selected = \"selected\"" : "") . ">Courier New</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"Georgia, 'Times New Roman', Times, serif\"" . ($userProfileOptions["font_family"] == "Georgia, 'Times New Roman', Times, serif" ? " $selected = \"selected\"" : "") . ">Georgia</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"Verdana, Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "Verdana, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Verdana</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"Tahoma, Verdana, Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "Tahoma, Verdana, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Tahoma</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"Geneva, Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "Geneva, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Geneva</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"'Trebuchet MS', Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "'Trebuchet MS', Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Trebuchet MS</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"'Comic Sans MS', Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "'Comic Sans MS', Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Comic Sans MS</option>\r\n\t\t\t\t\t\t\t\t\t</select>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditfontsize") ? "\r\n\t\t\t\t\t\t\t\t\t<select $name = \"colors[font_size]\">\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"\">&nbsp;</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"small\"" . ($userProfileOptions["font_size"] == "small" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["small"] . "</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"medium\"" . ($userProfileOptions["font_size"] == "medium" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["medium"] . "</option>\r\n\t\t\t\t\t\t\t\t\t\t<option $value = \"large\"" . ($userProfileOptions["font_size"] == "large" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["large"] . "</option>\r\n\t\t\t\t\t\t\t\t\t</select>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t<br />\r\n\t<img $src = \"" . $pic_base_url . "example.png\" $alt = \"\" $title = \"\" $border = \"0\" />";
            $pageContents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["cupro1"], "main" => $mainContent];
        }
    }
    for ($i = 1; $i <= count($MoodArray); $i++) {
        $Js .= "\r\n\t\tTSMOODS[" . $i . "] = \"" . $MoodArray[$i] . "\";\r\n\t\t";
    }
    $SELECTBOX .= "</select>";
    $main = "\r\n\t<script $type = \"text/javascript\">\r\n\t    var TSMOODS = new Array();\r\n\t\t" . $Js . "\r\n\t\tfunction UpdateMood(SelectedMood)\r\n\t\t{\r\n\t\t\tif (TSMOODS[SelectedMood])\r\n\t\t\t{\r\n\t\t\t\tTSGetID(\"currentmood\").$innerHTML = \"<img $src = '\"+TSMOODS[SelectedMood]+\"'>\";\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tTSGetID(\"currentmood\").$innerHTML = \"\";\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_mood&do=save_mood\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_mood\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_mood\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t" . $lang->usercp["selectmood"] . " " . $SELECTBOX . "\r\n\t\t\t\t<span $id = \"currentmood\" $name = \"currentmood\">" . ($CURUSERMOOD ? " <img $src = \"" . $CURUSERMOOD . "\" $alt = \"\" $title = \"\" $border = \"0\" />" : "") . "</span>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["moodtitle"], "main" => $main];
}
if ($act == "unsubscribe") {
    if ($do == "unsubscribe_threads" && isset($_POST["threadids"]) && is_array($_POST["threadids"])) {
        $deletethreads = [];
        foreach ($_POST["threadids"] as $__tid) {
            if (is_valid_id($__tid)) {
                $deletethreads[] = $__tid;
            }
        }
        if (0 < count($deletethreads)) {
            sql_query("DELETE FROM " . TSF_PREFIX . "subscribe WHERE tid IN (0, " . implode(",", $deletethreads) . ") AND $userid = '" . $userid . "'") or sql_query("DELETE FROM " . TSF_PREFIX . "subscribe WHERE tid IN (0, " . implode(",", $deletethreads) . ") AND $userid = '" . $userid . "'") || sqlerr(__FILE__, 385);
        }
    } else {
        if ($do == "unsubscribe_torrents" && is_array($_POST["torrentids"])) {
            $deletetorrents = [];
            foreach ($_POST["torrentids"] as $__tid) {
                if (is_valid_id($__tid)) {
                    $deletetorrents[] = $__tid;
                }
            }
            if (0 < count($deletetorrents)) {
                sql_query("DELETE FROM bookmarks WHERE torrentid IN (0, " . implode(",", $deletetorrents) . ") AND $userid = '" . $userid . "'") || sqlerr(__FILE__, 400);
            }
        }
    }
    $act = "";
    $do = "";
}
if (empty($act) && empty($do)) {
    ($query = sql_query("SELECT COUNT(id) as totalcomments FROM comments WHERE $user = '" . $userid . "'")) || sqlerr(__FILE__, 409);
    $res = mysqli_fetch_assoc($query);
    $comments = ts_nf($res["totalcomments"]);
    $join_date = my_datee($dateformat, $CURUSER["added"]) . " " . my_datee($timeformat, $CURUSER["added"]);
    $avatar = get_user_avatar($CURUSER["avatar"]);
    $kps = ts_nf($CURUSER["seedbonus"]);
    $invites = ts_nf($CURUSER["invites"]);
    $posts = ts_nf($CURUSER["totalposts"]);
    $substhreads = "\r\n\t<br />\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\" $name = \"unsubscribe_threads\">\r\n\t<input $type = \"hidden\" $value = \"unsubscribe\" $name = \"act\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t<span $style = \"float: right\"><input $checkall = \"group1\" $onclick = \"javascript: return select_deselectAll ('unsubscribe_threads', this, 'group1');\" $type = \"checkbox\" /></span>\r\n\t\t\t\t" . $lang->usercp["s1"] . "\r\n\t\t\t\t</td>\r\n\t\t</tr>";
    ($query = sql_query("SELECT s.tid, t.subject FROM " . TSF_PREFIX . "subscribe s LEFT JOIN " . TSF_PREFIX . "threads t ON (s.$tid = t.tid) WHERE s.$userid = '" . $userid . "'")) || sqlerr(__FILE__, 429);
    if (0 < mysqli_num_rows($query)) {
        while ($subs = mysqli_fetch_assoc($query)) {
            $substhreads .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<span $style = \"float: right\"><input $checkme = \"group1\" $type = \"checkbox\" $name = \"threadids[]\" $value = \"" . $subs["tid"] . "\" /></span>\r\n\t\t\t\t\t<a $href = \"" . $BASEURL . "/tsf_forums/showthread.php?$tid = " . $subs["tid"] . "\">" . cutename($subs["subject"], 90) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        $substhreads .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>" . $lang->usercp["s3"] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
    }
    $substhreads .= "\r\n\t\t<tr>\r\n\t\t\t<td><span $style = \"float: right\"><select $name = \"do\"><option $value = \"unsubscribe_threads\">" . $lang->usercp["s5"] . "</option></select> <input $type = \"submit\" $value = \"" . $lang->usercp["s6"] . "\" /></span></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
    $substorrents = "\r\n\t<br />\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\" $name = \"unsubscribe_torrents\">\r\n\t<input $type = \"hidden\" $value = \"unsubscribe\" $name = \"act\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t<span $style = \"float: right\"><input $checkall = \"group2\" $onclick = \"javascript: return select_deselectAll ('unsubscribe_torrents', this, 'group2');\" $type = \"checkbox\" /></span>\r\n\t\t\t\t" . $lang->usercp["s2"] . "\r\n\t\t\t\t</td>\r\n\t\t</tr>";
    ($query = sql_query("SELECT b.torrentid, t.name FROM bookmarks b LEFT JOIN torrents t ON (b.$torrentid = t.id) WHERE b.$userid = '" . $userid . "'")) || sqlerr(__FILE__, 470);
    if (0 < mysqli_num_rows($query)) {
        while ($subs = mysqli_fetch_assoc($query)) {
            $substorrents .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<span $style = \"float: right\"><input $checkme = \"group2\" $type = \"checkbox\" $name = \"torrentids[]\" $value = \"" . $subs["torrentid"] . "\" /></span>\r\n\t\t\t\t\t<a $href = \"" . $BASEURL . "/details.php?$id = " . $subs["torrentid"] . "\">" . cutename($subs["name"], 90) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        $substorrents .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>" . $lang->usercp["s4"] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
    }
    $substorrents .= "\r\n\t\t<tr>\r\n\t\t\t<td><span $style = \"float: right\"><select $name = \"do\"><option $value = \"unsubscribe_torrents\">" . $lang->usercp["s7"] . "</option></select> <input $type = \"submit\" $value = \"" . $lang->usercp["s6"] . "\" /></span></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
    $main = "\r\n\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $valign = \"top\">\r\n\t\t\t\t\t" . $avatar . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"none\" $valign = \"top\">\r\n\t\t\t\t\t" . sprintf($lang->usercp["details"], $join_date, htmlspecialchars_uni($CURUSER["email"]), $posts, $comments, $kps, $invites) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["title2"], "main" => $main];
}
if ($act == "auto_dst") {
    $dst = user_options($CURUSER["options"], "dst") ? "1" : "0";
    if ($dst == "1") {
        $dst = "0";
    } else {
        if ($dst == "0") {
            $dst = "1";
        }
    }
    $newUoptions = preg_replace("#O[0-1]#", "O" . $dst, $CURUSER["options"]);
    sql_query("UPDATE users SET $options = " . sqlesc($newUoptions) . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 531);
    redirect("usercp.php?$dst_updated = 1", $lang->usercp["dst_updated"]);
    exit;
}
if ($act == "show_gallery") {
    $GalleryPath = INC_PATH . "/avatars/gallery/";
    if (!is_dir($GalleryPath)) {
        redirect("usercp.php?$act = edit_avatar", $lang->usercp["avgalery2"]);
        exit;
    }
    $GalleryFiles = [];
    if ($allgalleryfiles = scandir($GalleryPath)) {
        foreach ($allgalleryfiles as $file) {
            if ($file != "." && $file != ".." && in_array(get_extension($file), $allowed_types)) {
                $GalleryFiles[] = $BASEURL . "/include/avatars/gallery/" . $file;
            }
        }
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["hash"]) && !empty($_POST["hash"]) && strlen($_POST["hash"]) == 15 && isset($_POST["avatar"]) && !empty($_POST["avatar"]) && in_array($_POST["avatar"], $GalleryFiles) && in_array(get_extension($_POST["avatar"]), $allowed_types)) {
        sql_query("UPDATE users SET $avatar = " . sqlesc(htmlspecialchars_uni($_POST["avatar"])) . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 559);
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $upload_path = INC_PATH . "/avatars/";
            if ($searchforfile = scandir($upload_path)) {
                foreach ($searchforfile as $file) {
                    if ($file != "." && $file != ".." && in_array(get_extension($file), $allowed_types)) {
                        $__exp = str_replace([".gif", ".png", ".jpg"], "", $file);
                        $__exp = explode("_", $__exp);
                        $__userid = $__exp[1];
                        if ($__userid == $userid) {
                            @unlink($upload_path . $file);
                        }
                    }
                }
            }
        }
        redirect("usercp.php?$act = edit_avatar", $lang->usercp["a_uploaded"]);
        exit;
    } else {
        if (0 < count($GalleryFiles)) {
            $AvatarHash = mksecret(15);
            $main = "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = show_gallery&do=save_avatar\">\r\n\t\t<input $type = \"hidden\" $name = \"act\" $value = \"show_gallery\" />\r\n\t\t<input $type = \"hidden\" $name = \"do\" $value = \"save_avatar\" />\r\n\t\t<input $type = \"hidden\" $name = \"hash\" $value = \"" . $AvatarHash . "\" />\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\">\r\n\t\t\t<tr>";
            $AvatarCount = 0;
            foreach ($GalleryFiles as $AvatarImage) {
                if ($AvatarCount % 6 == 0) {
                    $main .= "</tr><tr>";
                }
                $main .= "<td class=\"none\" $align = \"center\"><img $src = \"" . $AvatarImage . "\" $border = \"0\" class=\"inlineimg\" $width = \"100\" $height = \"100\" /><br /><input $type = \"radio\" $name = \"avatar\" class=\"none\" $value = \"" . $AvatarImage . "\" /></td>";
                $AvatarCount++;
            }
            $main .= "\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $colspan = \"7\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
            $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["avgalery1"], "main" => $main];
        } else {
            redirect("usercp.php?$act = edit_avatar", $lang->usercp["avgalery2"]);
            exit;
        }
    }
}
if ($act == "edit_avatar") {
    $newname = "";
    $A_Upload = false;
    $rules = sprintf($lang->usercp["a_rules"], $f_avatar_maxwidth, $f_avatar_maxheight, mksize($f_avatar_maxsize), strtoupper(implode(", ", $allowed_types)));
    if ($do == "save_avatar" && isset($_POST["hash"]) && !empty($_POST["hash"]) && strlen($_POST["hash"]) == 15) {
        if (!empty($_FILES["avatar_file"]) && !empty($_FILES["avatar_file"]["name"]) && !empty($_FILES["avatar_file"]["tmp_name"])) {
            @clearstatcache();
            $image_info = @getimagesize($_FILES["avatar_file"]["tmp_name"]);
            $size = @filesize($_FILES["avatar_file"]["tmp_name"]);
            $error = check_avatar($image_info[0], $image_info[1], $image_info["mime"], $size);
            if (!$error) {
                $file_ext = get_extension($_FILES["avatar_file"]["name"]);
                $newname = substr(md5($CURUSER["ip"] . TIMENOW . $CURUSER["id"]), 0, 15) . "_" . $CURUSER["id"] . "." . $file_ext;
                $upload_path = INC_PATH . "/avatars/";
                if (move_uploaded_file($_FILES["avatar_file"]["tmp_name"], $upload_path . $newname)) {
                    $avatar = $BASEURL . "/include/avatars/" . $newname;
                } else {
                    $error = show__message($lang->usercp["a_error4"]);
                }
            }
        } else {
            if (!empty($_POST["avatar_url"])) {
                @clearstatcache();
                $avatar = isset($_POST["avatar_url"]) ? $_POST["avatar_url"] : "";
                $image_info = @getimagesize($avatar);
                if (!($remote_file = @fopen($avatar, "rb")) || !$image_info) {
                    $error = show__message($lang->usercp["a_error1"]);
                    unset($avatar);
                } else {
                    $user_avatar_size = 0;
                    if (!(strlen(@fread($remote_file, 1)) == 0 || $f_avatar_maxsize < $user_avatar_size)) {
                        $user_avatar_size++;
                        if (true) {
                        }
                    }
                    @fclose($remote_file);
                    $error = check_avatar($image_info[0], $image_info[1], $image_info["mime"], $user_avatar_size);
                    if ($error) {
                        unset($avatar);
                    }
                }
            }
        }
        if (isset($avatar) && !empty($avatar) && !$error) {
            sql_query("UPDATE users SET $avatar = " . sqlesc($avatar) . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 689);
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $error = show__message("<img $src = \"" . htmlspecialchars_uni($avatar) . "\" $border = \"0\" class=\"inlineimg\" />", $lang->usercp["a_uploaded"]);
                $A_Upload = true;
                $upload_path = INC_PATH . "/avatars/";
                if ($searchforfile = scandir($upload_path)) {
                    foreach ($searchforfile as $file) {
                        if ($file != "." && $file != ".." && $file != $newname && in_array(get_extension($file), $allowed_types)) {
                            $__exp = str_replace([".gif", ".png", ".jpg"], "", $file);
                            $__exp = explode("_", $__exp);
                            $__userid = $__exp[1];
                            if ($__userid == $userid) {
                                @unlink($upload_path . $file);
                            }
                        }
                    }
                }
            }
        } else {
            $error = isset($error) ? $error : show__message($lang->usercp["a_error1"]);
        }
    }
    $UploadHash = mksecret(15);
    $main = "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction toggleuploadmode(mode)\r\n\t\t{\r\n\t\t\tswitch (mode)\r\n\t\t\t{\r\n\t\t\t\tcase 0:\r\n\t\t\t\t\tshow(\"avatar_file\", \"block\");\r\n\t\t\t\t\thide(\"avatar_url\");\r\n\t\t\t\t\tbreak;\r\n\t\t\t\tcase 1:\r\n\t\t\t\t\thide(\"avatar_file\");\r\n\t\t\t\t\tshow(\"avatar_url\", \"block\");\r\n\t\t\t\t\tbreak;\r\n\t\t\t}\r\n\t\t}\r\n\t\tfunction focusfield(fl) {\r\n\t\t\tif (fl.$value = =\"xq33\") {\r\n\t\t\t\tfl.$value = \"\";\r\n\t\t\t\tfl.style.$color = \"black\";\r\n\t\t\t}\r\n\t\t}\r\n\t\tfunction show(id, type)\r\n\t\t{\r\n\t\t\tvar $o = document.getElementById(id);\r\n\t\t\tif (o)\r\n\t\t\t\to.style.$display = type || \"\";\r\n\t\t}\r\n\r\n\t\tfunction hide(id)\r\n\t\t{\r\n\t\t\tvar $o = document.getElementById(id);\r\n\t\t\tif (o)\r\n\t\t\t\to.style.$display = \"none\";\r\n\t\t}\r\n\t</script>\r\n\t" . (isset($error) ? $error : "") . "\r\n\t<form $name = \"avatarupload\" $enctype = \"multipart/form-data\" $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_avatar&do=save_avatar\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_avatar\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_avatar\" />\r\n\t<input $type = \"hidden\" $name = \"hash\" $value = \"" . $UploadHash . "\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"100%\">\r\n\t\t\t\t\t" . (!$A_Upload ? "\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["a_current"] . "</legend>\r\n\t\t\t\t\t\t" . get_user_avatar($CURUSER["avatar"]) . "\r\n\t\t\t\t\t</fieldset>" : "") . "\r\n\t\t\t\t\t<fieldset $style = \"margin-bottom: 5px;\">\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["a_head"] . "</legend>\r\n\t\t\t\t\t\t\t<p>" . $lang->usercp["a_title"] . "</p>\r\n\t\t\t\t\t\t\t<input $name = \"uploadtype\" $onclick = \"toggleuploadmode(1)\" $checked = \"checked\" $type = \"radio\" /> <strong>" . $lang->usercp["a_option1"] . "</strong><br />\r\n\t\t\t\t\t\t\t<input $name = \"uploadtype\" $onclick = \"toggleuploadmode(0)\" $type = \"radio\" /> <strong>" . $lang->usercp["a_option2"] . "</strong><br />\r\n\t\t\t\t\t\t\t<input $name = \"uploadtype\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?$act = show_gallery'); return false;\" $type = \"radio\" /> <strong>" . $lang->usercp["avgalery3"] . "</strong>\r\n\t\t\t\t\t\t\t<div $id = \"avatar_url\">\r\n\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"avatar_url\" $size = \"70\" $onfocus = \"focusfield(this)\" />\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->usercp["a_button"] . "\" />\r\n\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t<div $id = \"avatar_file\" $style = \"display: none\">\r\n\t\t\t\t\t\t\t\t<input $type = \"file\" $name = \"avatar_file\" $size = \"70\" />\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->usercp["a_button"] . "\" />\r\n\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t<p>\r\n\t\t\t\t\t\t\t" . $rules . "\r\n\t\t\t\t\t\t\t</p>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["link4"], "main" => $main];
}
if ($act == "edit_signature") {
    if ($usergroups["cansignature"] != "yes") {
        $main = $lang->global["nopermission"];
    } else {
        if (isset($_POST["previewpost"]) && !empty($_POST["message"])) {
            $signature = trim($_POST["message"]);
        } else {
            if ($do == "save_signature") {
                $signature = trim($_POST["message"]);
                $sigstrlen = strlen($signature);
                if ($maxchar < $sigstrlen && !$IsStaff) {
                    $error = sprintf($lang->usercp["s_error1"], ts_nf($maxchar), ts_nf($sigstrlen));
                } else {
                    sql_query("UPDATE users SET $signature = " . sqlesc($signature) . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 823);
                    redirect("usercp.php?$act = edit_signature", $lang->usercp["saved2"]);
                    exit;
                }
            }
        }
        define("EDITOR_S_COUNT", 30);
        define("EDITOR_S_PER_ROW", 3);
        define("EDITOR_S_WIDTH", 100);
        define("EDITOR_STYLE_WIDTH", "600px");
        define("EDITOR_STYLE_HEIGHT", "330px");
        define("IN_EDITOR", true);
        require INC_PATH . "/editor.php";
        $signature = isset($signature) ? $signature : $CURUSER["signature"];
        $main .= "\r\n\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_signature&do=save_signature\">\r\n\t\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_signature\" />\r\n\t\t<input $type = \"hidden\" $name = \"do\" $value = \"save_signature\" />\r\n\t\t" . (isset($error) ? show__message($error) . "<br />" : ($signature ? show__message(format_comment($signature), $lang->usercp["s_current"]) . "<br /><br />" : "")) . "\r\n\t\t";
        $main .= insert_editor(false, NULL, $signature, $lang->usercp["link5"]);
        $main .= "\r\n\t\t</form>";
    }
    $contents = ["title" => $lang->usercp["title"], "title2" => NULL, "main" => $main];
}
if ($act == "edit_password") {
    if ($userAction == "customize_profile") {
        if (!customizeprofilepermissions("cancustomizeprofile")) {
            print_no_permission();
        }
        $allowedFonts = ["Arial, Helvetica, sans-serif", "'Times New Roman', Times, serif", "'Courier New', Courier, monospace", "Georgia, 'Times New Roman', Times, serif", "Verdana, Arial, Helvetica, sans-serif", "Tahoma, Verdana, Arial, Helvetica, sans-serif", "Geneva, Arial, Helvetica, sans-serif", "'Trebuchet MS', Arial, Helvetica, sans-serif", "'Comic Sans MS', Arial, Helvetica, sans-serif"];
        $allowedSizes = ["small", "medium", "large"];
        $validFields = ["bg_color", "left_side_bg_color", "table_bg_color", "thead_bg_color", "sub_header_bg_color", "link_color", "text_color", "border_color", "font_family", "font_size"];
        if ($userDoAction == "save_customize_profile" && strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["colors"]) && count($_POST["colors"])) {
            $profileQuery = [];
            foreach ($_POST["colors"] as $fieldName => $fieldValue) {
                if (in_array($fieldName, $validFields)) {
                    if ($fieldName == "font_family" && (!in_array($fieldValue, $allowedFonts) || !customizeprofilepermissions("caneditfontfamily"))) {
                        $fieldValue = "";
                    } else {
                        if ($fieldName == "font_size" && (!in_array($fieldValue, $allowedSizes) || !customizeprofilepermissions("caneditfontsize"))) {
                            $fieldValue = "";
                        } else {
                            if (!customizeprofilepermissions("caneditcolors")) {
                                $fieldValue = "";
                            }
                        }
                    }
                    $profileQuery[] = $fieldName . " = " . sqlesc(htmlspecialchars_uni($fieldValue));
                }
            }
            $profileQuery = implode(",", $profileQuery);
            sql_query("REPLACE INTO ts_custom_profiles SET $userid = " . sqlesc($CURUSER["id"]) . ", " . $profileQuery) || sqlerr(__FILE__, 94);
            unset($profileQuery);
            unset($fieldName);
            unset($fieldValue);
            define("FORCE_REDIRECT_MESSAGE", true);
            redirect("usercp.php?$act = customize_profile", $lang->usercp["saved1"]);
            exit;
        } else {
            $profileQuery = sql_query("SELECT * FROM ts_custom_profiles WHERE `userid` = " . sqlesc($CURUSER["id"]));
            if (mysqli_num_rows($profileQuery)) {
                $userProfileOptions = mysqli_fetch_assoc($profileQuery);
            } else {
                $userProfileOptions = [];
                $userProfileOptions["bg_color"] = "";
                $userProfileOptions["left_side_bg_color"] = "";
                $userProfileOptions["table_bg_color"] = "";
                $userProfileOptions["thead_bg_color"] = "";
                $userProfileOptions["sub_header_bg_color"] = "";
                $userProfileOptions["link_color"] = "";
                $userProfileOptions["text_color"] = "";
                $userProfileOptions["border_color"] = "";
                $userProfileOptions["font_family"] = "";
                $userProfileOptions["font_size"] = "";
            }
            $mainContent .= "\r\n\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/jscolor/jscolor.js\"></script>\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = customize_profile&do=save_customize_profile\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"customize_profile\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_customize_profile\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["aoptions"] . "</legend>\r\n\t\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\" class=\"none\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg2"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg3"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[bg_color]\" $value = \"" . $userProfileOptions["bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[left_side_bg_color]\" $value = \"" . $userProfileOptions["left_side_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[table_bg_color]\" $value = \"" . $userProfileOptions["table_bg_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg4"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg5"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg6"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg7"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"30%\">" . $lang->usercp["cuprobg8"] . "</td>" : "") . "\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\" $width = \"50%\">" . (customizeprofilepermissions("caneditfontfamily") ? $lang->usercp["cuprobg9"] . " / " : "") . (customizeprofilepermissions("caneditfontsize") ? $lang->usercp["cuprobg10"] : "") . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditcolors") ? "\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[text_color]\" $value = \"" . $userProfileOptions["text_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"colors[border_color]\" $value = \"" . $userProfileOptions["border_color"] . "\" $maxlength = \"6\" $size = \"6\" class=\"color {required:false}\" $autocomplete = \"off\" />\r\n\t\t\t\t\t\t\t\t\t</td>" : "") . "\r\n\t\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditfontfamily") ? "\r\n\t\t\t\t\t\t\t\t\t\t<select $name = \"colors[font_family]\">\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"\">&nbsp;</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Arial</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"'Times New Roman', Times, serif\"" . ($userProfileOptions["font_family"] == "'Times New Roman', Times, serif" ? " $selected = \"selected\"" : "") . ">Times New Roman</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"'Courier New', Courier, monospace\"" . ($userProfileOptions["font_family"] == "'Courier New', Courier, monospace" ? " $selected = \"selected\"" : "") . ">Courier New</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Georgia, 'Times New Roman', Times, serif\"" . ($userProfileOptions["font_family"] == "Georgia, 'Times New Roman', Times, serif" ? " $selected = \"selected\"" : "") . ">Georgia</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Verdana, Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "Verdana, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Verdana</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Tahoma, Verdana, Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "Tahoma, Verdana, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Tahoma</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"Geneva, Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "Geneva, Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Geneva</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"'Trebuchet MS', Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "'Trebuchet MS', Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Trebuchet MS</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"'Comic Sans MS', Arial, Helvetica, sans-serif\"" . ($userProfileOptions["font_family"] == "'Comic Sans MS', Arial, Helvetica, sans-serif" ? " $selected = \"selected\"" : "") . ">Comic Sans MS</option>\r\n\t\t\t\t\t\t\t\t\t\t</select>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t\t\t" . (customizeprofilepermissions("caneditfontsize") ? "\r\n\t\t\t\t\t\t\t\t\t\t<select $name = \"colors[font_size]\">\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"\">&nbsp;</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"small\"" . ($userProfileOptions["font_size"] == "small" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["small"] . "</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"medium\"" . ($userProfileOptions["font_size"] == "medium" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["medium"] . "</option>\r\n\t\t\t\t\t\t\t\t\t\t\t<option $value = \"large\"" . ($userProfileOptions["font_size"] == "large" ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["large"] . "</option>\r\n\t\t\t\t\t\t\t\t\t\t</select>" : "") . "\r\n\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t<br />\r\n\t<img $src = \"" . $pic_base_url . "example.png\" $alt = \"\" $title = \"\" $border = \"0\" />";
            $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["cupro1"], "main" => $mainContent];
                        $error .= show__message($lang->usercp["e_error7"]);
                    } else {
                        ($query = sql_query("SELECT email FROM users WHERE `email` = " . sqlesc($newemail1))) || sqlerr(__FILE__, 932);
                        if (0 < mysqli_num_rows($query)) {
                            if (isset($error)) {
                                $error .= show__message($lang->usercp["e_error8"]);
                            } else {
                                $error = show__message($lang->usercp["e_error8"]);
                            }
                        } else {
                            if (!$IsStaff) {
                                $sec = mksecret();
                                $hash = md5($sec . $newemail1 . $sec);
                                $obemail = urlencode($newemail1);
                                sql_query("DELETE FROM ts_user_validation WHERE `userid` = " . sqlesc($userid)) || sqlerr(__FILE__, 951);
                                sql_query("INSERT INTO ts_user_validation (editsecret, userid) VALUES (" . sqlesc($sec) . ", " . sqlesc($userid) . ")") || sqlerr(__FILE__, 952);
                                $body = sprintf($lang->usercp["emailbody"], $CURUSER["username"], $SITENAME, $newemail1, $_SERVER["REMOTE_ADDR"], $BASEURL, $userid, $hash, $obemail);
                                sent_mail($newemail1, sprintf($lang->usercp["emailsubject"], $SITENAME), $body, "profile", false);
                                if (isset($error)) {
                                    $error .= show__message($lang->usercp["saved5"], $lang->usercp["e_pass5"]);
                                } else {
                                    $error = show__message($lang->usercp["saved5"], $lang->usercp["e_pass5"]);
                                }
                            } else {
                                sql_query("UPDATE users SET $email = " . sqlesc($newemail1) . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 966);
                                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                    $error .= show__message($lang->usercp["saved4"], $lang->usercp["e_pass5"]);
                                } else {
                                    $error .= show__message($lang->global["dberror"]);
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!empty($_POST["passhint"]) && !empty($_POST["hintanswer"]) && is_valid_id($_POST["passhint"])) {
            $error = "";
            if (empty($currentpassword) || $CURUSER["passhash"] != md5($CURUSER["secret"] . $currentpassword . $CURUSER["secret"])) {
                $error = show__message($lang->usercp["e_error1"]);
            } else {
                if (in_array($passhint, ["1", "2", "3"])) {
                    if (strlen($hintanswer) < 3) {
                        $error .= show__message($lang->usercp["e_error9"]);
                    } else {
                        if (20 < strlen($hintanswer)) {
                            $error .= show__message($lang->usercp["e_error10"]);
                        } else {
                            if ($hintanswer == $CURUSER["username"]) {
                                $error .= show__message($lang->usercp["e_error11"]);
                            } else {
                                sql_query("REPLACE INTO ts_secret_questions (userid, passhint, hintanswer) VALUES ('" . $userid . "', '" . $passhint . "', " . sqlesc(md5($hintanswer)) . ")") || sqlerr(__FILE__, 1007);
                                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                    $error .= show__message($lang->usercp["saved7"], $lang->usercp["e_pass8"]);
                                } else {
                                    $error .= show__message($lang->global["dberror"]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $QArray = [1 => $lang->usercp["hr0"], 2 => $lang->usercp["hr1"], 3 => $lang->usercp["hr2"]];
    $questions = "<select $name = \"passhint\">";
    foreach ($QArray as $ID => $Question) {
        $questions .= "<option $value = \"" . $ID . "\">" . htmlspecialchars_uni($Question) . "</option>";
    }
    $questions .= "</select>";
    $main = (isset($error) ? $error : "") . "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_password&do=save_password\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_password\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_password\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"100%\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["e_pass"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"password\" $size = \"40\" $value = \"\" $autocomplete = \"off\" $name = \"currentpassword\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["e_pass2"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["e_pass3"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"password\" $size = \"40\" $value = \"\" $autocomplete = \"off\" $name = \"newpassword1\" /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["e_pass4"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"password\" $size = \"40\" $value = \"\" $autocomplete = \"off\" $name = \"newpassword2\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["e_pass5"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["e_pass6"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"text\" $size = \"40\" $value = \"" . htmlspecialchars_uni(isset($newemail1) ? $newemail1 : $CURUSER["email"]) . "\" $name = \"newemail1\" $autocomplete = \"off\" /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["e_pass7"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"text\" $size = \"40\" $value = \"" . htmlspecialchars_uni(isset($newemail2) ? $newemail2 : $CURUSER["email"]) . "\" $name = \"newemail2\" $autocomplete = \"off\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["e_pass8"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["e_pass9"] . "<br /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["e_pass10"] . "<br />\r\n\t\t\t\t\t\t" . $questions . "<br />\r\n\t\t\t\t\t\t" . $lang->usercp["e_pass11"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"text\" $size = \"30\" $value = \"" . (isset($hintanswer) ? $hintanswer : "") . "\" class=\"inlineimg\" $name = \"hintanswer\" $autocomplete = \"off\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset $style = \"margin-bottom: 5px;\">\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["save"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["link6"], "main" => $main];
}
if ($act == "edit_details") {
    $caneditbday = $IsStaff || !$CURUSER["birthday"] ? true : false;
    if ($do == "save_details") {
        $updateset = $update = [];
        if ($caneditbday && is_valid_id($_POST["day"]) && is_valid_id($_POST["month"]) && is_valid_id($_POST["year"])) {
            $day = htmlspecialchars_uni($_POST["day"]);
            $month = htmlspecialchars_uni($_POST["month"]);
            $year = intval($_POST["year"]);
            $bday = $day . "-" . $month . "-" . $year;
            if ($bday != $CURUSER["birthday"]) {
                $updateset[] = "birthday = " . sqlesc($bday);
            }
        }
        if (isset($_POST["country"]) && $_POST["country"] != $CURUSER["country"]) {
            $updateset[] = "country = " . sqlesc(intval($_POST["country"]));
        }
        $UserSpeed = trim($_POST["download"]) . "~" . trim($_POST["upload"]);
        if ($UserSpeed != $CURUSER["speed"]) {
            $updateset[] = "speed = " . sqlesc($UserSpeed);
        }
        if ($_POST["tzoffset"] != $CURUSER["tzoffset"]) {
            $updateset[] = "tzoffset = " . sqlesc(htmlspecialchars_uni($_POST["tzoffset"]));
        }
        if ($_POST["dst"] == "2") {
            $dst = user_options($CURUSER["options"], "dst") ? "1" : "0";
            $autodst = "1";
        } else {
            if ($_POST["dst"] == "1") {
                $dst = "1";
                $autodst = "0";
            } else {
                $dst = "0";
                $autodst = "0";
            }
        }
        if ($usergroups["canemailnotify"] == "yes") {
            $notifs = isset($_POST["pmnotif"]) && $_POST["pmnotif"] == "yes" ? "[pm]" : "";
            $notifs .= isset($_POST["emailnotif"]) && $_POST["emailnotif"] == "yes" ? "[email]" : "";
            $query = sql_query("SELECT id FROM categories") or ($query = sql_query("SELECT id FROM categories")) || sqlerr(__FILE__, 1137);
            while ($cats = mysqli_fetch_assoc($query)) {
                if (isset($_POST["cat" . $cats["id"]]) && $_POST["cat" . $cats["id"]] == "yes") {
                    $notifs .= "[cat" . $cats["id"] . "]";
                }
            }
            $updateset[] = "notifs = " . sqlesc($notifs);
        }
        if (in_array($_POST["torrentsperpage"], [0, 5, 10, 20, 30, 40])) {
            $updateset[] = "torrentsperpage = " . sqlesc(intval($_POST["torrentsperpage"]));
        }
        if (in_array($_POST["postsperpage"], [0, 5, 10, 20, 30, 40])) {
            $updateset[] = "postsperpage = " . sqlesc(intval($_POST["postsperpage"]));
        }
        if (0 < count($updateset)) {
            sql_query("UPDATE users SET " . implode(",", $updateset) . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 1157);
        }
        $new_user_options = $_POST["options"];
        $update[] = "A" . (isset($new_user_options["parked"]) && $new_user_options["parked"] == "yes" ? "1" : "0");
        $update[] = "B" . (isset($new_user_options["invisible"]) && $new_user_options["invisible"] == "yes" ? "1" : "0");
        $update[] = "C" . (isset($new_user_options["commentpm"]) && $new_user_options["commentpm"] == "yes" ? "1" : "0");
        $update[] = "D" . (isset($new_user_options["avatars"]) && $new_user_options["avatars"] == "yes" ? "1" : "0");
        $update[] = "E" . (user_options($CURUSER["options"], "showoffensivetorrents") ? "1" : "0");
        $update[] = "F" . (isset($new_user_options["popup"]) && $new_user_options["popup"] == "yes" ? "1" : "0");
        $update[] = "G0";
        $update[] = "H" . (isset($new_user_options["signatures"]) && $new_user_options["signatures"] == "yes" ? "1" : "0");
        $update[] = "I" . (user_options($CURUSER["options"], "privacy", 1) ? "1" : (user_options($CURUSER["options"], "privacy", 2) ? "2" : (user_options($CURUSER["options"], "privacy", 3) ? "3" : "4")));
        $update[] = "K" . (isset($new_user_options["acceptpms"]) && $new_user_options["acceptpms"] == "yes" ? "1" : ($new_user_options["acceptpms"] == "friends" ? "2" : "3"));
        $update[] = "L" . (isset($new_user_options["gender"]) && $new_user_options["gender"] == "1" ? "1" : (isset($new_user_options["gender"]) && $new_user_options["gender"] == "2" ? "2" : "3"));
        $update[] = "M" . (isset($new_user_options["visitormsg"]) && $new_user_options["visitormsg"] == "yes" ? "1" : (isset($new_user_options["visitormsg"]) && $new_user_options["visitormsg"] == "staff" ? "2" : "3"));
        $update[] = "N" . $autodst;
        $update[] = "O" . $dst;
        $update[] = "P" . (isset($new_user_options["quickmenu"]) && $new_user_options["quickmenu"] == "yes" ? "1" : "0");
        $update[] = "R" . (isset($new_user_options["webseeder"]) && $new_user_options["webseeder"] == "yes" ? "1" : "0");
        $update[] = "S" . (user_options($CURUSER["options"], "newsletter") ? "1" : "0");
        $update[] = "Q" . (isset($new_user_options["shoutbox"]) && $new_user_options["shoutbox"] == 1 ? "1" : "0");
        $update[] = "T" . (isset($new_user_options["fb-shoutbox"]) && $new_user_options["fb-shoutbox"] == 1 ? "1" : "0");
        $options = implode("", $update);
        sql_query("UPDATE users SET $options = " . sqlesc($options) . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 1180);
        unset($new_user_options);
        unset($update);
        unset($options);
        redirect("usercp.php?$act = edit_details", $lang->usercp["saved1"]);
        exit;
    }
    if ($caneditbday) {
        $userbday = @explode("-", $CURUSER["birthday"]);
        $days = "<select $name = \"day\">";
        for ($i = 1; $i <= 31; $i++) {
            $days .= "<option $value = \"" . $i . "\"" . ($userbday[0] == $i ? " $selected = \"selected\"" : "") . ">" . $i . "</option>";
        }
        $days .= "</select>";
        $months_array = @explode(",", $lang->usercp["dob5"]);
        $months = "\r\n\t\t<select $name = \"month\">\r\n\t\t<option $value = \"-1\"></option>";
        $first = 1;
        foreach ($months_array as $left => $right) {
            $months .= "<option $value = \"" . $first . "\"" . (isset($userbday[1]) && $userbday[1] == $first ? " $selected = \"selected\"" : "") . ">" . $right . "</option>";
            $first++;
        }
        $months .= "</select>";
        $year = "\r\n\t\t<input $type = \"text\" $size = \"4\" $name = \"year\" $value = \"" . (isset($userbday[2]) ? $userbday[2] : "") . "\" />\r\n\t\t";
    }
    $country = "<select $name = \"country\">";
    $query = sql_query("SELECT id, name FROM countries ORDER by name") or ($query = sql_query("SELECT id, name FROM countries ORDER by name")) || sqlerr(__FILE__, 1213);
    while ($countries = mysqli_fetch_assoc($query)) {
        $country .= "<option $value = \"" . intval($countries["id"]) . "\"" . ($CURUSER["country"] == $countries["id"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars_uni($countries["name"]) . "</option>";
    }
    $country .= "</select>";
    $downloadspeed = "\r\n\t<select $name = \"download\">\r\n\t<option $value = \"0\">-------</option>";
    require TSDIR . "/" . $cache . "/downloadspeed.php";
    $UserSpeed = explode("~", $CURUSER["speed"]);
    foreach ($_downloadspeed as $ds_b) {
        $downloadspeed .= "<option $value = \"" . intval($ds_b["id"]) . "\"" . ($UserSpeed[0] == $ds_b["id"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars_uni($ds_b["name"]) . "</option>";
    }
    $downloadspeed .= "</select>";
    $uploadspeed = "\r\n\t<select $name = \"upload\">\r\n\t<option $value = \"0\">-------</option>";
    require TSDIR . "/" . $cache . "/uploadspeed.php";
    foreach ($_uploadspeed as $us_b) {
        $uploadspeed .= "<option $value = \"" . intval($us_b["id"]) . "\"" . ($UserSpeed[1] == $us_b["id"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars_uni($us_b["name"]) . "</option>";
    }
    $uploadspeed .= "</select>";
    unset($_uploadspeed);
    unset($_downloadspeed);
    unset($ds_b);
    unset($us_b);
    $torrentsperpage = "\r\n\t<select $name = \"torrentsperpage\">\r\n\t\t<option $value = \"0\">" . $lang->usercp["z5"] . "</option>";
    foreach (["5", "10", "20", "30", "40"] as $perpage) {
        $torrentsperpage .= "<option $value = \"" . $perpage . "\"" . ($CURUSER["torrentsperpage"] == $perpage ? " $selected = \"selected\"" : "") . ">" . sprintf($lang->usercp["z6"], $perpage) . "</option>";
    }
    $torrentsperpage .= "</select>";
    $postsperpage = "\r\n\t<select $name = \"postsperpage\">\r\n\t\t<option $value = \"0\">" . $lang->usercp["z5"] . "</option>";
    foreach (["5", "10", "20", "30", "40"] as $perpage) {
        $postsperpage .= "<option $value = \"" . $perpage . "\"" . ($CURUSER["postsperpage"] == $perpage ? " $selected = \"selected\"" : "") . ">" . sprintf($lang->usercp["z7"], $perpage) . "</option>";
    }
    $postsperpage .= "</select>";
    $lang->load("shoutbox");
    require INC_PATH . "/functions_timezone.php";
    require INC_PATH . "/functions_category2.php";
    $main .= "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction show_hide_list()\r\n\t\t{\r\n\t\t\tvar WorkArea = document.getElementById(\"categorylist\").style.display;\r\n\t\t\tif (WorkArea == \"none\")\r\n\t\t\t{\r\n\t\t\t\tdocument.getElementById(\"categorylist\").style.$display = \"block\";\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tdocument.getElementById(\"categorylist\").style.$display = \"none\";\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_details&do=save_details\" $name = \"edit_details\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_details\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_details\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"100%\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["imode"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["imode2"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[invisible]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "invisible") ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["imode"] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["pacc"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["pacc2"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[parked]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "parked") ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["pacc"] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["pm"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["pm2"] . "<br /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["pm3"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"options[acceptpms]\" $value = \"yes\"" . (user_options($CURUSER["options"], "acceptpms", 1) ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["pm4"] . "\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"options[acceptpms]\" $value = \"friends\"" . (user_options($CURUSER["options"], "acceptpms", 2) ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["pm5"] . "\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"options[acceptpms]\" $value = \"no\"" . (user_options($CURUSER["options"], "acceptpms", 3) ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["pm6"] . "<br /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["pm7"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[popup]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "popup") ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["pm8"] . "<br /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["pm9"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[commentpm]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "commentpm") ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["pm10"] . "\r\n\t\t\t\t\t\t" . ($usergroups["canemailnotify"] == "yes" ? "<br /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["pm11"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"pmnotif\" $value = \"yes\" class=\"inlineimg\"" . (strpos($CURUSER["notifs"], "[pm]") !== false ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["pm12"] . "<br /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["pm13"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"emailnotif\" $value = \"yes\" class=\"inlineimg\"" . (strpos($CURUSER["notifs"], "[email]") !== false ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["pm14"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"nothingtodo\" $value = \"xxx\" class=\"inlineimg\" $onclick = \"javascript: show_hide_list();\" /> " . $lang->usercp["pm15"] . "<span $id = \"categorylist\" $style = \"display: none;\">" . ts_category_list2(1, "edit_details") . "</span>" : "") . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["vm"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["vm2"] . "<br /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["vm3"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[visitormsg]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "visitormsg", 1) ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["vm4"] . "<br /><br />\r\n\t\t\t\t\t\t" . $lang->usercp["vm5"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[visitormsg]\" $value = \"staff\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "visitormsg", 2) ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["vm6"] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["show"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["show1"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[signatures]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "signatures", 1) ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["show2"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[avatars]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "avatars", 1) ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["show3"] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["z1"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["z2"] . "<br /><br />\r\n\t\t\t\t\t\t<b>" . $lang->usercp["z3"] . "</b><br />\r\n\t\t\t\t\t\t" . $torrentsperpage . "<br /><br />\r\n\t\t\t\t\t\t<b>" . $lang->usercp["z4"] . "</b><br />\r\n\t\t\t\t\t\t" . $postsperpage . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t" . ($caneditbday ? "\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["dob1"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["dob6"] . "<br />\r\n\t\t\t\t\t\t<table $border = \"0\" $cellpadding = \"1\" $cellspacing = \"0\" $width = \"40\">\r\n\t\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $lang->usercp["dob2"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $lang->usercp["dob3"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $lang->usercp["dob4"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $days . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $months . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $year . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</fieldset>" : "") . "\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["g1"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["g5"] . "<br />\r\n\t\t\t\t\t\t<select $name = \"options[gender]\">\r\n\t\t\t\t\t\t\t<option $value = \"1\"" . (user_options($CURUSER["options"], "gender", 1) ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["g2"] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"2\"" . (user_options($CURUSER["options"], "gender", 2) ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["g3"] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"3\"" . (user_options($CURUSER["options"], "gender", 3) ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["g4"] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["c1"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["c2"] . "<br />\r\n\t\t\t\t\t\t" . $country . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["is1"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["is4"] . "<br />\r\n\t\t\t\t\t\t<table $border = \"0\" $cellpadding = \"1\" $cellspacing = \"0\" $width = \"200\">\r\n\t\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $lang->usercp["is2"] . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $lang->usercp["is3"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $downloadspeed . "</td>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">" . $uploadspeed . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[webseeder]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "webseeder", 1) ? " $checked = \"checked\"" : "") . " /> " . $lang->usercp["webseeder"] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t" . show_timezone($CURUSER["tzoffset"], user_options($CURUSER["options"], "autodst") ? 1 : 0, user_options($CURUSER["options"], "dst") ? 1 : 0) . "\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["browse1"] . "</legend>\r\n\t\t\t\t\t\t<s>" . $lang->usercp["browse2"] . "</s><br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[quickmenu]\" $value = \"yes\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "quickmenu", 1) ? " $checked = \"checked\"" : "") . " $disabled = \"disabled\" /> <b><s>" . $lang->usercp["browse3"] . "</s></b>\r\n\t\t\t\t\t</fieldset>\r\n\r\n\t\t\t\t\t<div $id = \"shoutbox-options\"></div>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["sboptions"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["disablesbwhy"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[shoutbox]\" $value = \"1\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "shoutbox") ? " $checked = \"checked\"" : "") . " /> <b>" . $lang->usercp["disablesb"] . "</b>\r\n\r\n\t\t\t\t\t\t<br /><br />\r\n\t\t\t\t\t\t" . $lang->shoutbox["fb-shoutbox2"] . "\r\n\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"options[fb-shoutbox]\" $value = \"1\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "fb-shoutbox") ? " $checked = \"checked\"" : "") . " /> <b>" . $lang->shoutbox["fb-shoutbox"] . "</b>\r\n\t\t\t\t\t</fieldset>\r\n\r\n\t\t\t\t\t<fieldset $style = \"margin-bottom: 5px;\">\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["save"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["link3"], "main" => $main];
}
if ($act == "edit_privacy") {
    $TSSEConfig->TSLoadConfig("ANNOUNCE");
    if ($xbt_active == "yes") {
        $query = sql_query("SELECT uid FROM xbt_files_users WHERE `uid` = '" . $userid . "' AND $active = 1") or ($query = sql_query("SELECT uid FROM xbt_files_users WHERE `uid` = '" . $userid . "' AND $active = 1")) || sqlerr(__FILE__, 1422);
    } else {
        ($query = sql_query("SELECT userid FROM peers WHERE `userid` = '" . $userid . "'")) || sqlerr(__FILE__, 1424);
    }
    if (0 < mysqli_num_rows($query)) {
        $usergroups["canresetpasskey"] = "no";
    }
    $equery = "";
    if ($do == "save_privacy") {
        $_POST["showoffensivetorrents"] = isset($_POST["showoffensivetorrents"]) && $_POST["showoffensivetorrents"] == "no" ? "0" : "1";
        $CURUSER["options"] = preg_replace("#I[0-5]#", "I" . (isset($_POST["privacy"]) ? intval($_POST["privacy"]) : 0), $CURUSER["options"]);
        $CURUSER["options"] = preg_replace("#E[0-5]#", "E" . (isset($_POST["showoffensivetorrents"]) ? intval($_POST["showoffensivetorrents"]) : 0), $CURUSER["options"]);
        if (isset($_POST["resetpasskey"]) && $_POST["resetpasskey"] == "yes" && $usergroups["canresetpasskey"] == "yes") {
            $randomtext = md5($SITENAME);
            $randompasskey = md5($CURUSER["username"] . get_date_time() . $CURUSER["passhash"] . $randomtext);
            $equery = ", $torrent_pass = " . sqlesc($randompasskey);
        }
        sql_query("UPDATE users SET $options = " . sqlesc($CURUSER["options"]) . $equery . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 1441);
        redirect("usercp.php?$act = edit_privacy", $lang->usercp["saved6"]);
        exit;
    }
    $main .= "\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_privacy&do=save_privacy\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_privacy\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_privacy\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"100%\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["p0"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["p1"] . "<br />\r\n\t\t\t\t\t\t<b>" . $lang->usercp["p0"] . ":</b><br />\r\n\t\t\t\t\t\t<select $name = \"privacy\">\r\n\t\t\t\t\t\t\t<option $value = \"1\"" . (user_options($CURUSER["options"], "privacy", 1) ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["p2"] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"2\"" . (user_options($CURUSER["options"], "privacy", 2) ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["p3"] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"3\"" . (user_options($CURUSER["options"], "privacy", 3) ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["p4"] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"4\"" . (user_options($CURUSER["options"], "privacy", 4) ? " $selected = \"selected\"" : "") . ">" . $lang->usercp["p5"] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["o0"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["o1"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"showoffensivetorrents\" $value = \"no\" class=\"inlineimg\"" . (user_options($CURUSER["options"], "showoffensivetorrents") ? "" : " $checked = \"checked\"") . " /> " . $lang->usercp["o0"] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t" . ($usergroups["canresetpasskey"] == "yes" ? "\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["r1"] . "</legend>\r\n\t\t\t\t\t\t" . $lang->usercp["r2"] . "<br />\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"resetpasskey\" $value = \"yes\" class=\"inlineimg\" /> " . $lang->usercp["r1"] . "\r\n\t\t\t\t\t</fieldset>" : "") . "\r\n\t\t\t\t\t<fieldset $style = \"margin-bottom: 5px;\">\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["save"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["link7"], "main" => $main];
}
if ($act == "edit_theme_language") {
    if ($do == "save_theme_language" && $_POST["stylesheet"] != $CURUSER["stylesheet"]) {
        sql_query("UPDATE users SET $stylesheet = " . sqlesc(htmlspecialchars_uni($_POST["stylesheet"])) . " WHERE `id` = '" . $userid . "'") || sqlerr(__FILE__, 1497);
        redirect("usercp.php?$act = edit_theme_language", $lang->usercp["saved8"]);
        exit;
    }
    $dirlist = "";
    $link = 0;
    $languages = scandir(INC_PATH . "/languages");
    foreach ($languages as $language) {
        if ($language != "." && $language != ".." && is_dir(INC_PATH . "/languages/" . $language)) {
            if ($link && $link % 8 == 0) {
                $dirlist .= "<br />";
            }
            $dirlist .= "\r\n\t\t\t<a $href = \"javascript:void(0);\" $onclick = \"window.open('" . $BASEURL . "/set_language.php?$language = " . $language . "','set_language','toolbar=no, $scrollbars = no, $resizable = no, $width = 250, $height = 20, $top = 250, $left = 250'); return false;\"><img $src = \"" . $BASEURL . "/include/languages/" . $language . "/flag/flag.gif\" $alt = \"" . $language . "\" $title = \"" . $language . "\" $width = \"32\" $height = \"20\" $border = \"0\" /></a>&nbsp;";
            $link++;
        }
    }
    $__Themes = explode(",", $TSSECache->Cache["ts_themes"]["content"]);
    if (!count($__Themes)) {
        $dirlist2 = "<option $value = \"\">There is no template</option>";
    } else {
        $defaulttemplate = ts_template();
        $t_image = "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction ChangeHeader(What)\r\n\t\t\t{\r\n\t\t\t\tTSGetID(\"t_image\").$src = \"" . $BASEURL . "/include/templates/\"+What+\"/images/header.jpg\";\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<img $src = \"" . $BASEURL . "/include/templates/" . $defaulttemplate . "/images/header.jpg\" $border = \"0\" $width = \"100\" $height = \"20\" $style = \"vertical-align: middle;\" $id = \"t_image\">";
        $dirlist2 = "<select $name = 'stylesheet' $id = 'specialboxn' $style = 'vertical-align: middle;' $onchange = 'ChangeHeader(this.value);'>";
        foreach ($__Themes as $dir) {
            if ($dir != "test" || $IsStaff) {
                $dirlist2 .= "<option $value = \"" . $dir . "\" " . ($CURUSER["stylesheet"] == $dir ? "selected=\"selected\"" : ($defaulttemplate == $dir ? "selected=\"selected\"" : "")) . ">" . $dir . "</option>";
            }
        }
        $dirlist2 .= "</select> " . $t_image;
    }
    $main .= "\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_theme_language&do=save_theme_language\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_theme_language\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_theme_language\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"100%\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["link8"] . "</legend>\r\n\t\t\t\t\t\t<b>" . $lang->usercp["l1"] . ":</b><br />\r\n\t\t\t\t\t\t" . $dirlist . "<br /><br />\r\n\t\t\t\t\t\t<b>" . $lang->usercp["l2"] . ":</b><br />\r\n\t\t\t\t\t\t" . $dirlist2 . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset $style = \"margin-bottom: 5px;\">\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["save"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["link8"], "main" => $main];
}
if ($act == "edit_im") {
    $lang->load("im");
    if ($do == "save_edit_im") {
        $icq = trim($_POST["icq"]);
        $aim = trim($_POST["aim"]);
        $skype = trim($_POST["skype"]);
        $msn = trim($_POST["msn"]);
        $yahoo = trim($_POST["yahoo"]);
        require INC_PATH . "/functions_verify_contact.php";
        verify_msn($msn);
        verify_skype($skype);
        sql_query("UPDATE users SET $contact = " . sqlesc(implode("|", [$icq, $aim, $skype, $msn, $yahoo])) . " WHERE `id` = " . sqlesc($CURUSER["id"]));
        redirect("usercp.php?$act = edit_im");
        exit;
    }
    $CA = @explode("|", $CURUSER["contact"]);
    $main .= "\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_im&do=save_edit_im\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_im\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_edit_im\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"100%\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->im["im"] . "</legend>\r\n\t\t\t\t\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\"><img $src = \"" . $pic_base_url . "contact/icq.gif\" $alt = \"" . $lang->im["icq"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->im["icq"] . "</td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\"><img $src = \"" . $pic_base_url . "contact/aim.gif\" $alt = \"" . $lang->im["aim"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->im["aim"] . "</td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\"><img $src = \"" . $pic_base_url . "contact/skype.gif\" $alt = \"" . $lang->im["skype"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->im["skype"] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"text\" $name = \"icq\" $value = \"" . (isset($CA[0]) ? htmlspecialchars_uni($CA[0]) : "") . "\" /></td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"text\" $name = \"aim\" $value = \"" . (isset($CA[1]) ? htmlspecialchars_uni($CA[1]) : "") . "\" /></td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"text\" $name = \"skype\" $value = \"" . (isset($CA[2]) ? htmlspecialchars_uni($CA[2]) : "") . "\" /></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\"><img $src = \"" . $pic_base_url . "contact/msn.gif\" $alt = \"" . $lang->im["msn"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->im["msn"] . "</td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $colspan = \"2\" $align = \"left\"><img $src = \"" . $pic_base_url . "contact/yahoo.gif\" $alt = \"" . $lang->im["yahoo"] . "\" $border = \"0\" class=\"inlineimg\" /> " . $lang->im["yahoo"] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"text\" $name = \"msn\" $value = \"" . (isset($CA[3]) ? htmlspecialchars_uni($CA[3]) : "") . "\" /></td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $colspan = \"2\" $align = \"left\"><input $type = \"text\" $name = \"yahoo\" $value = \"" . (isset($CA[4]) ? htmlspecialchars_uni($CA[4]) : "") . "\" /></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset $style = \"margin-bottom: 5px;\">\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["save"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["link9"], "main" => $main];
}
if ($act == "show_userbar") {
    $main = "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"100%\">\r\n\t\t\t\t\t<fieldset $style = \"margin-bottom: 5px;\">\r\n\t\t\t\t\t\t<legend><img $src = \"" . $BASEURL . "/torrentbar/torrentbar.php/" . $userid . ".png\" $border = \"0\" class=\"inlineimg\" /></legend>\r\n\t\t\t\t\t\t" . $lang->usercp["ub2"] . "<br /><textarea $onClick = \"highlight(this);\" $cols = \"80\" $rows = \"1\">[$url = " . $BASEURL . "][img]" . $BASEURL . "/torrentbar/torrentbar.php/" . $userid . ".png[/img][/url]</textarea>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["ub1"], "main" => $main];
}
if ($act == "edit_seedbox") {
    ($Query = sql_query("SELECT sb_port, sb_ipaddress FROM ts_seedboxes WHERE $sb_userid = " . $userid)) || sqlerr(__FILE__, 1669);
    if (mysqli_num_rows($Query)) {
        $Seedbox = mysqli_fetch_assoc($Query);
    } else {
        $Seedbox = [];
        $Seedbox["sb_port"] = "";
        $Seedbox["sb_ipaddress"] = "";
    }
    if ($do == "save_edit_seedbox") {
        $sb_port = isset($_POST["sb_port"]) && trim($_POST["sb_port"]) != "" ? trim($_POST["sb_port"]) : "";
        $sb_ipaddress = isset($_POST["sb_ipaddress"]) && trim($_POST["sb_ipaddress"]) != "" ? trim($_POST["sb_ipaddress"]) : "";
        if (empty($sb_port) && empty($sb_ipaddress)) {
            sql_query("DELETE FROM ts_seedboxes WHERE $sb_userid = " . $userid);
        } else {
            if ($sb_port != $Seedbox["sb_port"] || $sb_ipaddress != $Seedbox["sb_ipaddress"]) {
                sql_query("REPLACE INTO ts_seedboxes VALUES ('" . $userid . "', '" . $sb_port . "', " . sqlesc($sb_ipaddress) . ")") || sqlerr(__FILE__, 1692);
            }
        }
        redirect("usercp.php?$act = edit_seedbox");
        exit;
    }
    $main .= "\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = edit_seedbox&do=save_edit_seedbox\">\r\n\t<input $type = \"hidden\" $name = \"act\" $value = \"edit_seedbox\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save_edit_seedbox\" />\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"100%\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["sb1"] . "</legend>\r\n\t\t\t\t\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->usercp["sb2"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->usercp["sb3"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"sb_ipaddress\" $value = \"" . htmlspecialchars_uni($Seedbox["sb_ipaddress"]) . "\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $name = \"sb_port\" $value = \"" . htmlspecialchars_uni($Seedbox["sb_port"]) . "\" /> <i>" . $lang->usercp["sb4"] . "</i>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset $style = \"margin-bottom: 5px;\">\r\n\t\t\t\t\t\t<legend>" . $lang->usercp["save"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->usercp["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->usercp["reset"] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t";
    $contents = ["title" => $lang->usercp["title"], "title2" => $lang->usercp["sb1"], "main" => $main];
}
$TSSEConfig->TSLoadConfig("ANNOUNCE");
stdhead($lang->usercp["title"]);
echo "<table $align = \"center\" $cellpadding = \"0\" $cellspacing = \"0\" $width = \"100%\">\r\n\t<tbody>\r\n\t\t<tr $valign = \"top\">\r\n\t\t\t<td $valign = \"top\" $width = \"180\" class=\"none\">\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t";
echo create_link(NULL, $lang->usercp["link1"]);
echo "\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> <a $href = \"";
echo ts_seo($userid, $CURUSER["username"]);
echo "\">";
echo $lang->usercp["link2"];
echo "</a><br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("customize_profile", $lang->usercp["cupro1"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("edit_avatar", $lang->usercp["link4"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("edit_signature", $lang->usercp["link5"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("edit_password", $lang->usercp["link6"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("edit_details", $lang->usercp["link3"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("edit_privacy", $lang->usercp["link7"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t    <b>&#187;</b> ";
echo create_link("edit_theme_language", $lang->usercp["link8"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("edit_im", $lang->usercp["link9"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("edit_mood", $lang->usercp["link10"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("edit_newsletter", $lang->usercp["n0"]);
echo "\t\t\t\t\t\t\t\t\t";
echo $usergroups["canseedbox"] == "yes" ? "<br /><b>&#187;</b> " . create_link("edit_seedbox", $lang->usercp["sb1"]) : "";
echo "\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t";
echo $lang->usercp["usertools"];
echo "\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> ";
echo create_link("show_userbar", $lang->usercp["ub1"]);
echo "<br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> <a $href = \"";
echo $BASEURL;
echo "/takeflush.php?$id = ";
echo $userid;
echo "\">";
echo $lang->usercp["usertools1"];
echo "</a><br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> <a $href = \"";
echo $BASEURL;
echo "/invite.php\">";
echo $lang->usercp["usertools2"];
echo "</a><br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> <a $href = \"";
echo $BASEURL;
echo "/referrals.php\">";
echo $lang->usercp["usertools3"];
echo "</a><br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> <a $href = \"";
echo $BASEURL;
echo "/port_check.php\">";
echo $lang->usercp["usertools4"];
echo "</a><br />\r\n\t\t\t\t\t\t\t\t\t";
echo $IsStaff ? "<b>&#187;</b> <a $href = \"" . $BASEURL . "/ts_watch_list.php?$action = show_list\">" . $lang->usercp["usertools5"] . "</a><br />" : "";
echo "\t\t\t\t\t\t\t\t\t";
echo $xbt_active == "yes" && $usergroups["cansnatch"] == "yes" ? "<b>&#187;</b> <a $href = \"" . $BASEURL . "/mysnatchlist.php?$uid = " . $userid . "\">" . $lang->usercp["viewsnatchlist"] . "</a>" : "";
echo "\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t";
echo $lang->usercp["m1"];
echo "\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> <a $href = \"";
echo $BASEURL;
echo "/messages.php\">";
echo $lang->usercp["m2"];
echo "</a><br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> <a $href = \"";
echo $BASEURL;
echo "/messages.php?$userid = ";
echo $userid;
echo "&do=editfolders\">";
echo $lang->usercp["m3"];
echo "</a><br />\r\n\t\t\t\t\t\t\t\t\t<b>&#187;</b> <a $href = \"";
echo $BASEURL;
echo "/sendmessage.php\">";
echo $lang->usercp["m4"];
echo "</a>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t\t<td $valign = \"top\" class=\"none\" $style = \"padding-left: 15px\">\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t";
echo $contents["title"];
echo "\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t";
echo $contents["title2"] != "" ? "\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"subheader\">\r\n\t\t\t\t\t\t\t\t\t" . $contents["title2"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>" : "";
echo "\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t";
echo $contents["main"];
echo "\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t\t";
echo $substhreads . $substorrents;
echo "\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</tbody>\r\n</table>\r\n";
stdfoot();
function create_link($act = "", $text)
{
    return "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . ($act ? "?$act = " . $act : "") . "\">" . ((isset($_GET["act"]) || isset($_POST["act"])) && (isset($_GET["act"]) && $_GET["act"] == $act || isset($_POST["act"]) && $_POST["act"] == $act) && !empty($act) ? "<span class=\"highlight\">" . $text . "</span>" : $text) . "</a>";
}
function show__message($message, $subject = "")
{
    global $lang;
    $subject = $subject ? $subject : $lang->global["error"];
    return "<fieldset><legend>" . $subject . "</legend>" . $message . "</fieldset>";
}
function check_avatar($width, $height, $type, $size)
{
    global $f_avatar_maxwidth;
    global $f_avatar_maxheight;
    global $f_avatar_maxsize;
    global $lang;
    global $usergroups;
    $error = false;
    $types_array = ["image/gif", "image/jpeg", "image/jpg", "image/png"];
    if (empty($width) || empty($height) || empty($type) || empty($size)) {
        $error = $lang->usercp["a_error1"];
    } else {
        if (!in_array($type, $types_array)) {
            $error = sprintf($lang->usercp["a_invalid_image"], implode(", ", $types_array), htmlspecialchars_uni($type));
        } else {
            if (($f_avatar_maxwidth < $width || $f_avatar_maxheight < $height) && $usergroups["cansettingspanel"] != "yes") {
                $error = sprintf($lang->usercp["a_error2"], $f_avatar_maxwidth, $f_avatar_maxheight, $width, $height);
            } else {
                if ($f_avatar_maxsize < $size && $usergroups["cansettingspanel"] != "yes") {
                    $error = sprintf($lang->usercp["a_error3"], mksize($f_avatar_maxsize), mksize($size));
                }
            }
        }
    }
    if ($error) {
        return show__message($error);
    }
    return "";
}

?>