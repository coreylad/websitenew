<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/promotions.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if ($Act == "delete" && ($pid = intval($_GET["pid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_promotions WHERE $pid = " . $pid);
    logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[17]));
    $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"5\"><div class=\"icon-ok\">" . $Language[18] . "</div></td></tr>";
}
if ($Act == "edit" && ($pid = intval($_GET["pid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_promotions WHERE $pid = " . $pid);
    if (mysqli_num_rows($query)) {
        $promotion = mysqli_fetch_assoc($query);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $title = @trim($_POST["title"]);
            $include_usergroup = @intval($_POST["include_usergroup"]);
            $promote_to = @intval($_POST["promote_to"]);
            $demote_to = @intval($_POST["demote_to"]);
            $upload_limit = 0 + $_POST["upload_limit"];
            $ratio_limit = @trim($_POST["ratio_limit"]);
            $min_reg_days = @intval($_POST["min_reg_days"]);
            $posts = @intval($_POST["posts"]);
            $times_warned = @intval($_POST["times_warned"]);
            if ($upload_limit || $ratio_limit || $min_reg_days || $posts || $times_warned) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_promotions SET $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $include_usergroup = " . $include_usergroup . ", $promote_to = " . $promote_to . ", $demote_to = " . $demote_to . ", $upload_limit = " . $upload_limit . ", $ratio_limit = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ratio_limit) . "', $min_reg_days = " . $min_reg_days . ", $posts = " . $posts . ", $times_warned = " . $times_warned . " WHERE $pid = " . $pid);
                logStaffAction($Language[18]);
                $Message = showAlertError($Language[18]);
                $Updated = true;
            } else {
                $Message = showAlertError($Language[30]);
            }
        } else {
            $title = $promotion["title"];
            $include_usergroup = $promotion["include_usergroup"];
            $promote_to = $promotion["promote_to"];
            $demote_to = $promotion["demote_to"];
            $upload_limit = $promotion["upload_limit"];
            $ratio_limit = $promotion["ratio_limit"];
            $min_reg_days = $promotion["min_reg_days"];
            $posts = $promotion["posts"];
            $times_warned = $promotion["times_warned"];
        }
        if (!isset($Updated)) {
            $List = "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=promotions&amp;$act = edit&amp;$pid = " . $pid . "\">\r\n\t\t\t" . showAlertMessage("<a $href = \"index.php?do=promotions\">" . $Language[2] . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\">\r\n\t\t\t\t\t\t" . $Language[20] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[3] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[26] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"title\" $value = \"" . $title . "\" $size = \"50\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[7] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t" . function_109($include_usergroup, "include_usergroup") . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . ($promotion["type"] == "promote" ? $Language[4] : $Language[5]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[27] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t" . function_109($promotion["type"] == "promote" ? $promote_to : $demote_to, $promotion["type"] == "promote" ? "promote_to" : "demote_to") . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t" . ($promotion["type"] == "promote" ? "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[8] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[16] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"upload_limit\" $value = \"" . $upload_limit . "\" $size = \"10\" /> GB\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[15] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"ratio_limit\" $value = \"" . $ratio_limit . "\" $size = \"10\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[10] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[13] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"min_reg_days\" $value = \"" . $min_reg_days . "\" $size = \"10\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[11] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[14] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"posts\" $value = \"" . $posts . "\" $size = \"10\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<input $type = \"hidden\" $name = \"times_warned\" $value = \"0\" />\r\n\t\t\t\t" : "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[15] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"ratio_limit\" $value = \"" . $ratio_limit . "\" $size = \"10\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[32] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[31] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"times_warned\" $value = \"" . $times_warned . "\" $size = \"10\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<input $type = \"hidden\" $name = \"upload_limit\" $value = \"0\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"min_reg_days\" $value = \"0\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"posts\" $value = \"0\" />\r\n\t\t\t\t") . "\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[28] . "\" /> <input $type = \"reset\" $value = \"" . $Language[29] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t</table\r\n\t\t\t</form>\r\n\t\t\t";
        } else {
            unset($List);
        }
    } else {
        $Message = showAlertError($Language[24]);
    }
}
if ($Act == "new") {
    $title = "";
    $include_usergroup = 0;
    $promote_to = 0;
    $demote_to = 0;
    $upload_limit = 0;
    $ratio_limit = 0;
    $min_reg_days = 0;
    $posts = 0;
    $times_warned = 0;
    $type = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $title = @trim($_POST["title"]);
        $include_usergroup = @intval($_POST["include_usergroup"]);
        $promote_to = @intval($_POST["promote_to"]);
        $demote_to = @intval($_POST["demote_to"]);
        $upload_limit = 0 + $_POST["upload_limit"];
        $ratio_limit = @trim($_POST["ratio_limit"]);
        $min_reg_days = @intval($_POST["min_reg_days"]);
        $posts = @intval($_POST["posts"]);
        $times_warned = @intval($_POST["times_warned"]);
        $type = $_POST["type"];
        if (($type == "promote" || $type == "demote") && ($upload_limit || $ratio_limit || $min_reg_days || $posts || $times_warned)) {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_promotions (type, title, include_usergroup, promote_to, demote_to, upload_limit, ratio_limit, min_reg_days, posts, times_warned) VALUES ('" . $type . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', " . $include_usergroup . ", " . $promote_to . ", " . $demote_to . ", " . $upload_limit . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ratio_limit) . "', " . $min_reg_days . ", " . $posts . ", " . $times_warned . ")");
            logStaffAction($Language[33]);
            $Message = showAlertError($Language[33]);
            $Updated = true;
        } else {
            $Message = showAlertError($Language[30]);
        }
    }
    if (!isset($Updated)) {
        $List = "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction update_promotion_tab(selected)\r\n\t\t\t{\r\n\t\t\t\tTSGetID(\"promotetab\").style.$display = \"none\";\r\n\t\t\t\tTSGetID(\"demotetab\").style.$display = \"none\";\r\n\t\t\t\tif (selected != \"\")\r\n\t\t\t\t{\r\n\t\t\t\t\tTSGetID(selected+\"tab\").style.$display = \"inline\";\r\n\t\t\t\t\tif ($selected = = \"promote\")\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tTSGetID(\"demote_to\").$innerHTML = \"\";\r\n\t\t\t\t\t\tTSGetID(\"ratio_limit2\").$innerHTML = \"\";\r\n\t\t\t\t\t\tTSGetID(\"times_warned\").$innerHTML = \"\";\r\n\r\n\t\t\t\t\t\tTSGetID(\"promote_to\").$innerHTML = '" . addslashes(mysqli_real_escape_string($GLOBALS["DatabaseConnect"], function_109($promote_to, "promote_to"))) . "';\r\n\t\t\t\t\t\tTSGetID(\"upload_limit\").$innerHTML = '<input $type = \"text\" $name = \"upload_limit\" $value = \"" . $upload_limit . "\" $size = \"10\" /> GB';\r\n\t\t\t\t\t\tTSGetID(\"ratio_limit\").$innerHTML = '<input $type = \"text\" $name = \"ratio_limit\" $value = \"" . $ratio_limit . "\" $size = \"10\" />';\r\n\t\t\t\t\t\tTSGetID(\"min_reg_days\").$innerHTML = '<input $type = \"text\" $name = \"min_reg_days\" $value = \"" . $min_reg_days . "\" $size = \"10\" />';\r\n\t\t\t\t\t\tTSGetID(\"posts\").$innerHTML = '<input $type = \"text\" $name = \"posts\" $value = \"" . $posts . "\" $size = \"10\" />';\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse if ($selected = = \"demote\")\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tTSGetID(\"promote_to\").$innerHTML = \"\";\r\n\t\t\t\t\t\tTSGetID(\"upload_limit\").$innerHTML = \"\";\r\n\t\t\t\t\t\tTSGetID(\"ratio_limit\").$innerHTML = \"\";\r\n\t\t\t\t\t\tTSGetID(\"min_reg_days\").$innerHTML = \"\";\r\n\t\t\t\t\t\tTSGetID(\"posts\").$innerHTML = \"\";\r\n\r\n\t\t\t\t\t\tTSGetID(\"demote_to\").$innerHTML = '" . addslashes(mysqli_real_escape_string($GLOBALS["DatabaseConnect"], function_109($demote_to, "demote_to"))) . "';\r\n\t\t\t\t\t\tTSGetID(\"ratio_limit2\").$innerHTML = '<input $type = \"text\" $name = \"ratio_limit\" $value = \"" . $ratio_limit . "\" $size = \"10\" />';\r\n\t\t\t\t\t\tTSGetID(\"times_warned\").$innerHTML = '<input $type = \"text\" $name = \"times_warned\" $value = \"" . $times_warned . "\" $size = \"10\" />';\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=promotions&amp;$act = new\">\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=promotions\">" . $Language[2] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\">\r\n\t\t\t\t\t" . $Language[25] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[3] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[26] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"title\" $value = \"" . $title . "\" $size = \"50\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[7] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t" . function_109($include_usergroup, "include_usergroup") . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[6] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[36] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<select $name = \"type\" $onchange = \"update_promotion_tab(this.value);\">\r\n\t\t\t\t\t\t<option $value = \"\">" . $Language[36] . "</option>\r\n\t\t\t\t\t\t<option $value = \"promote\"" . ($type == "promote" ? " $selected = \"selected\"" : "") . ">" . $Language[34] . "</option>\r\n\t\t\t\t\t\t<option $value = \"demote\"" . ($type == "demote" ? " $selected = \"selected\"" : "") . ">" . $Language[35] . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"3\">\r\n\r\n\t\t\t\t\t<div $id = \"promotetab\" $style = \"display: none;\">\r\n\t\t\t\t\t\t<table $cellspacing = \"0\" $cellpadding = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[4] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"promote_to\"></div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[8] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[16] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"upload_limit\"></div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[15] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"ratio_limit\"></div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[10] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[13] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"min_reg_days\"></div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[11] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[14] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"posts\"></div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>\r\n\r\n\t\t\t\t\t<div $id = \"demotetab\" $style = \"display: none;\">\r\n\t\t\t\t\t\t<table $cellspacing = \"0\" $cellpadding = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[5] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"demote_to\"></div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[15] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"ratio_limit2\"></div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[32] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[31] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t<div $id = \"times_warned\"></div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>\r\n\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[28] . "\" /> <input $type = \"reset\" $value = \"" . $Language[29] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t</table\r\n\t\t</form>\r\n\t\t";
    } else {
        $Message = showAlertError($Language[33]);
    }
}
if (!isset($List)) {
    $List = "";
    $Promotions = [];
    $Demotions = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_promotions");
    while ($Promotion = mysqli_fetch_assoc($query)) {
        if ($Promotion["type"] == "promote") {
            $Promotions[] = $Promotion;
        } else {
            $Demotions[] = $Promotion;
        }
    }
    if ($Promotions[0] != "") {
        $List = "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"8\">\r\n\t\t\t\t\t" . $Language[2] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[3] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[7] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[4] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[8] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[9] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[10] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[11] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[19] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>";
        foreach ($Promotions as $Promote) {
            $List .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Promote["title"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_110($Promote["include_usergroup"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_110($Promote["promote_to"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Promote["upload_limit"]) . " GB</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Promote["ratio_limit"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Promote["min_reg_days"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Promote["posts"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><a $href = \"index.php?do=promotions&amp;$act = edit&amp;$pid = " . $Promote["pid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[20]) . "\" $title = \"" . trim($Language[20]) . "\" $border = \"0\" /></a> <a $href = \"#\" $onclick = \"ConfirmDelete(" . $Promote["pid"] . ");\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[21]) . "\" $title = \"" . trim($Language[21]) . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
        }
        $List .= "\r\n\t\t\t</table>";
    }
    if ($Demotions[0] != "") {
        $List .= "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"6\">\r\n\t\t\t\t\t" . $Language[23] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[3] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[7] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[5] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[9] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[32] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t<b>" . $Language[19] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>";
        foreach ($Demotions as $Demote) {
            $List .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Demote["title"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_110($Demote["include_usergroup"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_110($Demote["demote_to"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Demote["ratio_limit"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Demote["times_warned"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><a $href = \"index.php?do=promotions&amp;$act = edit&amp;$pid = " . $Demote["pid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[20]) . "\" $title = \"" . trim($Language[20]) . "\" $border = \"0\" /></a> <a $href = \"#\" $onclick = \"ConfirmDelete(" . $Demote["pid"] . ");\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[21]) . "\" $title = \"" . trim($Language[21]) . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
        }
        $List .= "\r\n\t\t\t</table>";
    }
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction ConfirmDelete(pID)\r\n\t\t{\r\n\t\t\tif (confirm(\"" . trim($Language[22]) . "\"))\r\n\t\t\t{\r\n\t\t\t\tTSJump(\"index.php?do=promotions&$act = delete&$pid = \"+pID);\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . showAlertMessage("<span $style = \"float: right;\"><a $href = \"index.php?do=promotions&amp;$act = new\">" . $Language[25] . "</a></span><a $href = \"index.php?do=manage_cronjobs\">" . $Language[37] . "</a>") . "\r\n\t" . $Message . $List;
} else {
    echo $List;
}
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
        redirectTo("../index.php");
    }
}
function redirectTo($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; $url = " . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_110($ug)
{
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title, namestyle FROM usergroups WHERE $gid = " . $ug);
    $gameName = mysqli_fetch_row($query);
    return str_replace("{username}", $gameName[0], $gameName[1]);
}
function function_109($ug, $name)
{
    $settingOptions = "<select $name = \"" . $name . "\">";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups");
    while ($gameName = mysqli_fetch_assoc($query)) {
        $settingOptions .= "<option $value = \"" . $gameName["gid"] . "\"" . ($gameName["gid"] == $ug ? " $selected = \"selected\"" : "") . ">" . str_replace("{username}", $gameName["title"], strip_tags($gameName["namestyle"])) . "</option>";
    }
    $settingOptions .= "</select>";
    return $settingOptions;
}

?>