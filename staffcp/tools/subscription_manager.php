<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/subscription_manager.lang");
$Message = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$title = "";
$description = "";
$active = "1";
$disporder = "1";
$usergroup = "0";
$seedbonus = "";
$invites = "";
$uploaded = "";
$cost = "";
$currency = "usd";
$length = "";
$lengthtype = "";
if ($Act == "delete" && isset($_GET["sid"]) && ($Sid = intval($_GET["sid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_subscriptions WHERE $sid = '" . $Sid . "'");
    $Act = "";
}
if ($Act == "edit" && isset($_GET["sid"]) && ($Sid = intval($_GET["sid"]))) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_subscriptions WHERE $sid = '" . $Sid . "'");
    if (0 < mysqli_num_rows($Query)) {
        $Sub = mysqli_fetch_assoc($Query);
        $title = $Sub["title"];
        $description = $Sub["description"];
        $active = $Sub["active"];
        $disporder = $Sub["disporder"];
        $usergroup = $Sub["usergroup"];
        $seedbonus = $Sub["seedbonus"];
        $invites = $Sub["invites"];
        $uploaded = $Sub["uploaded"];
        $cost = $Sub["cost"];
        $currency = $Sub["currency"];
        $length = $Sub["length"];
        $lengthtype = $Sub["lengthtype"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
            $description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
            $active = isset($_POST["active"]) ? intval($_POST["active"]) : 0;
            $disporder = isset($_POST["disporder"]) ? intval($_POST["disporder"]) : 0;
            $usergroup = isset($_POST["usergroup"]) ? intval($_POST["usergroup"]) : 0;
            $seedbonus = isset($_POST["seedbonus"]) ? intval($_POST["seedbonus"]) : 0;
            $invites = isset($_POST["invites"]) ? intval($_POST["invites"]) : 0;
            $uploaded = isset($_POST["uploaded"]) ? intval($_POST["uploaded"]) : 0;
            $cost = isset($_POST["cost"]) ? trim($_POST["cost"]) : "";
            $currency = isset($_POST["currency"]) ? trim($_POST["currency"]) : "usd";
            $length = isset($_POST["length"]) ? intval($_POST["length"]) : 0;
            $lengthtype = isset($_POST["lengthtype"]) ? trim($_POST["lengthtype"]) : "weeks";
            $RequiredFields = ["title", "cost", "currency", "length", "lengthtype"];
            foreach ($RequiredFields as $Required) {
                if (!${$Required}) {
                    $Message = function_76($Language[32]);
                    if (empty($Message)) {
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_subscriptions SET $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', $active = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $active) . "', $disporder = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $disporder) . "', $usergroup = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $usergroup) . "', $seedbonus = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $seedbonus) . "', $invites = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $invites) . "', $uploaded = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $uploaded) . "',  $cost = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $cost) . "', $currency = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $currency) . "', $length = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $length) . "', $lengthtype = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lengthtype) . "' WHERE $sid = '" . $Sid . "'");
                        function_78("index.php?do=subscription_manager");
                        exit;
                    }
                }
            }
        }
        $ShowUsergroups = "\r\n\t\t<select $name = \"usergroup\">\r\n\t\t\t<option $value = \"0\">" . $Language[25] . "</option>";
        $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title FROM usergroups WHERE $isbanned = 'no' AND gid > 0 ORDER by disporder");
        while ($UG = mysqli_fetch_assoc($Query)) {
            $ShowUsergroups .= "\r\n\t\t\t<option $value = \"" . $UG["gid"] . "\"" . ($usergroup == $UG["gid"] ? " $selected = \"selected\"" : "") . ">" . $UG["title"] . "</option>";
        }
        $ShowUsergroups .= "\r\n\t\t</select>";
        echo "\r\n\t\t<form $action = \"index.php?do=subscription_manager&$act = edit&$sid = " . $Sid . "\" $method = \"post\">\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">\r\n\t\t\t\t\t" . $Language[34] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>* " . $Language[3] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"1\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[13] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"description\" $value = \"" . htmlspecialchars($description) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"2\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[4] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == "1" ? " $checked = \"checked\"" : "") . " /> " . $Language[23] . "\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == "0" ? " $checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[7] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"6\" $dir = \"ltr\" $tabindex = \"4\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[14] . "</legend>\r\n\t\t\t\t\t\t" . $ShowUsergroups . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[15] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"seedbonus\" $value = \"" . htmlspecialchars($seedbonus) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"5\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[16] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"invites\" $value = \"" . htmlspecialchars($invites) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"6\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[17] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"uploaded\" $value = \"" . htmlspecialchars($uploaded) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"7\" /> " . $Language[26] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>* " . $Language[27] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"cost\" $value = \"" . htmlspecialchars($cost) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"7\" />\r\n\t\t\t\t\t\t" . function_101($currency) . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>* " . $Language[18] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"length\" $value = \"" . htmlspecialchars($length) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"7\" />\r\n\t\t\t\t\t\t<select $name = \"lengthtype\">\r\n\t\t\t\t\t\t\t<option $value = \"days\"" . ($lengthtype == "days" ? " $selected = \"selected\"" : "") . ">" . $Language[19] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"weeks\"" . ($lengthtype == "weeks" ? " $selected = \"selected\"" : "") . ">" . $Language[20] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"months\"" . ($lengthtype == "months" ? " $selected = \"selected\"" : "") . ">" . $Language[21] . "</option>\t\r\n\t\t\t\t\t\t\t<option $value = \"years\"" . ($lengthtype == "years" ? " $selected = \"selected\"" : "") . ">" . $Language[22] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[29] . "\" /> <input $type = \"reset\" $value = \"" . $Language[30] . "\" /> " . $Language[31] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
    } else {
        $Act = "";
    }
}
if ($Act == "new") {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
        $active = isset($_POST["active"]) ? intval($_POST["active"]) : 0;
        $disporder = isset($_POST["disporder"]) ? intval($_POST["disporder"]) : 0;
        $usergroup = isset($_POST["usergroup"]) ? intval($_POST["usergroup"]) : 0;
        $seedbonus = isset($_POST["seedbonus"]) ? intval($_POST["seedbonus"]) : 0;
        $invites = isset($_POST["invites"]) ? intval($_POST["invites"]) : 0;
        $uploaded = isset($_POST["uploaded"]) ? intval($_POST["uploaded"]) : 0;
        $cost = isset($_POST["cost"]) ? trim($_POST["cost"]) : "";
        $currency = isset($_POST["currency"]) ? trim($_POST["currency"]) : "usd";
        $length = isset($_POST["length"]) ? intval($_POST["length"]) : 0;
        $lengthtype = isset($_POST["lengthtype"]) ? trim($_POST["lengthtype"]) : "weeks";
        $RequiredFields = ["title", "cost", "currency", "length", "lengthtype"];
        foreach ($RequiredFields as $Required) {
            if (!${$Required}) {
                $Message = function_76($Language[32]);
                if (empty($Message)) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_subscriptions VALUES (NULL, '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $active) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $disporder) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $usergroup) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $seedbonus) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $invites) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $uploaded) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $cost) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $currency) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $length) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $lengthtype) . "')");
                    function_78("index.php?do=subscription_manager");
                    exit;
                }
            }
        }
    }
    $ShowUsergroups = "\r\n\t<select $name = \"usergroup\">\r\n\t\t<option $value = \"0\">" . $Language[25] . "</option>";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title FROM usergroups WHERE $isbanned = 'no' AND gid > 0 ORDER by disporder");
    while ($UG = mysqli_fetch_assoc($Query)) {
        $ShowUsergroups .= "\r\n\t\t<option $value = \"" . $UG["gid"] . "\"" . ($usergroup == $UG["gid"] ? " $selected = \"selected\"" : "") . ">" . $UG["title"] . "</option>";
    }
    $ShowUsergroups .= "\r\n\t</select>";
    echo "\r\n\t<form $action = \"index.php?do=subscription_manager&$act = new\" $method = \"post\">\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">\r\n\t\t\t\t" . $Language[12] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>* " . $Language[3] . "</legend>\r\n\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"1\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $Language[13] . "</legend>\r\n\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"description\" $value = \"" . htmlspecialchars($description) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"2\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $Language[4] . "</legend>\r\n\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == "1" ? " $checked = \"checked\"" : "") . " /> " . $Language[23] . "\r\n\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == "0" ? " $checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $Language[7] . "</legend>\r\n\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"6\" $dir = \"ltr\" $tabindex = \"4\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $Language[14] . "</legend>\r\n\t\t\t\t\t" . $ShowUsergroups . "\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $Language[15] . "</legend>\r\n\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"seedbonus\" $value = \"" . htmlspecialchars($seedbonus) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"5\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $Language[16] . "</legend>\r\n\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"invites\" $value = \"" . htmlspecialchars($invites) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"6\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $Language[17] . "</legend>\r\n\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"uploaded\" $value = \"" . htmlspecialchars($uploaded) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"7\" /> " . $Language[26] . "\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\">\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>* " . $Language[27] . "</legend>\r\n\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"cost\" $value = \"" . htmlspecialchars($cost) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"7\" />\t\t\t\t\t\r\n\t\t\t\t\t" . function_101($currency) . "\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\">\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>* " . $Language[18] . "</legend>\r\n\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"length\" $value = \"" . htmlspecialchars($length) . "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"7\" />\t\t\t\t\t\r\n\t\t\t\t\t<select $name = \"lengthtype\">\r\n\t\t\t\t\t\t<option $value = \"days\"" . ($lengthtype == "days" ? " $selected = \"selected\"" : "") . ">" . $Language[19] . "</option>\r\n\t\t\t\t\t\t<option $value = \"weeks\"" . ($lengthtype == "weeks" ? " $selected = \"selected\"" : "") . ">" . $Language[20] . "</option>\r\n\t\t\t\t\t\t<option $value = \"months\"" . ($lengthtype == "months" ? " $selected = \"selected\"" : "") . ">" . $Language[21] . "</option>\t\r\n\t\t\t\t\t\t<option $value = \"years\"" . ($lengthtype == "years" ? " $selected = \"selected\"" : "") . ">" . $Language[22] . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[29] . "\" /> <input $type = \"reset\" $value = \"" . $Language[30] . "\" /> " . $Language[31] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
}
if (empty($Act)) {
    $List = "";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_subscriptions ORDER BY disporder");
    if (mysqli_num_rows($Query)) {
        while ($Sub = mysqli_fetch_assoc($Query)) {
            $List .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($Sub["title"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . ($Sub["active"] == "1" ? $Language[23] : $Language[24]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($Sub["cost"]) . " " . htmlspecialchars($Sub["currency"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($Sub["length"]) . " " . ($Sub["lengthtype"] == "days" ? $Language[19] : ($Sub["lengthtype"] == "weeks" ? $Language[20] : ($Sub["lengthtype"] == "months" ? $Language[21] : $Language[22]))) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . intval($Sub["disporder"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"index.php?do=subscription_manager&amp;$act = edit&amp;$sid = " . $Sub["sid"] . "\">" . trim($Language[9]) . "</a> - <a $href = \"index.php?do=subscription_manager&amp;$act = delete&amp;$sid = " . $Sub["sid"] . "\">" . trim($Language[10]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    }
    echo "\r\n\t" . function_81("<span $style = \"float: right;\">" . trim($Language[33]) . " </span> <a $href = \"index.php?do=subscription_manager&$act = new\">" . trim($Language[12]) . "</a> ") . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"6\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . $Language[3] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[4] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[27] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[18] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[7] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[8] . "</td>\r\n\t\t</tr>\r\n\t\t" . $List . "\r\n\t</table>\r\n\t";
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
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_101($selected = "")
{
    $var_302 = [];
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT currency FROM ts_subscriptions_api");
    while ($var_303 = mysqli_fetch_assoc($Query)) {
        $var_302[] = $var_303["currency"];
    }
    $var_304 = "<select $name = \"currency\">";
    $var_305 = [];
    foreach ($var_302 as $var_306) {
        $var_306 = explode(",", $var_306);
        foreach ($var_306 as $var_307) {
            if (!in_array($var_307, $var_305)) {
                $var_305[] = trim($var_307);
                $var_304 .= "<option $value = \"" . $var_307 . "\"" . ($selected == $var_307 ? " $selected = \"selected\"" : "") . ">" . strtoupper($var_307) . "</option>";
            }
        }
    }
    $var_304 .= "</select>";
    return $var_304;
}

?>