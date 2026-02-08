<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_cronjobs.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$STATUS = "";
$Error = [];
if ($Act == "delete" && ($cronid = intval($_GET["cronid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_cron WHERE $cronid = " . $cronid);
    logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[27]));
    $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . $Language[42] . "</div></td></tr>";
}
if ($Act == "set_status" && ($cronid = intval($_GET["cronid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_cron SET $active = IF($active = 1, 0, 1) WHERE $cronid = " . $cronid);
    logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[27]));
    $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . $Language[43] . "</div></td></tr>";
}
if ($Act == "run" && ($cronid = intval($_GET["cronid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_cron SET $nextrun = 0 WHERE $cronid = " . $cronid);
    logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[27]));
    $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . $Language[43] . "</div></td></tr>";
}
if ($Act == "edit" && ($cronid = intval($_GET["cronid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_cron WHERE $cronid = " . $cronid);
    if (mysqli_num_rows($query)) {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $filename = trim($_POST["filename"]);
            $description = trim($_POST["description"]);
            $active = isset($_POST["active"]) && $_POST["active"] == "1" ? "1" : "0";
            $loglevel = isset($_POST["loglevel"]) && $_POST["loglevel"] == "1" ? "1" : "0";
            $mosecs = 2678400;
            $wsecs = 604800;
            $dsecs = 86400;
            $hsecs = 3600;
            $msecs = 60;
            $minutes = 0;
            if (0 < $_POST["months"]) {
                $minutes += $mosecs * $_POST["months"];
            }
            if (0 < $_POST["weeks"]) {
                $minutes += $wsecs * $_POST["weeks"];
            }
            if (0 < $_POST["days"]) {
                $minutes += $dsecs * $_POST["days"];
            }
            if (0 < $_POST["hours"]) {
                $minutes += $hsecs * $_POST["hours"];
            }
            if (0 < $_POST["minutes"]) {
                $minutes += $msecs * $_POST["minutes"];
            }
            $TArray = function_326($minutes);
            if ($filename && file_exists("../include/cron/" . $filename)) {
                if (0 < $minutes) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_cron SET $minutes = " . $minutes . ", $filename = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $filename) . "', $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', $loglevel = " . $loglevel . ", $active = " . $active . " WHERE $cronid = " . $cronid);
                    logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[27]));
                    $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . $Language[43] . "</div></td></tr>";
                } else {
                    $Error[] = $Language[46];
                }
            } else {
                $Error[] = $Language[45];
            }
        } else {
            $Res = mysqli_fetch_assoc($query);
            $filename = trim(htmlspecialchars($Res["filename"]));
            $description = trim(htmlspecialchars($Res["description"]));
            $active = $Res["active"];
            $loglevel = $Res["loglevel"];
            $TArray = function_326($Res["minutes"]);
        }
        if (empty($STATUS)) {
            $months = "";
            for ($i = 0; $i <= 12; $i++) {
                $months .= "<option $value = \"" . $i . "\"" . function_318($TArray["months"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[31], $Language[30]) . "</option>";
            }
            $weeks = "";
            for ($i = 0; $i <= 4; $i++) {
                $weeks .= "<option $value = \"" . $i . "\"" . function_318($TArray["weeks"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[33], $Language[32]) . "</option>";
            }
            $days = "";
            for ($i = 0; $i <= 31; $i++) {
                $days .= "<option $value = \"" . $i . "\"" . function_318($TArray["days"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[35], $Language[34]) . "</option>";
            }
            $hours = "";
            for ($i = 0; $i <= 24; $i++) {
                $hours .= "<option $value = \"" . $i . "\"" . function_318($TArray["hours"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[37], $Language[36]) . "</option>";
            }
            $minutes = "";
            for ($i = 0; $i <= 60; $i++) {
                $minutes .= "<option $value = \"" . $i . "\"" . function_318($TArray["minutes"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[39], $Language[38]) . "</option>";
            }
            $ActionTab = "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_cronjobs&amp;$act = edit&amp;$cronid = " . $cronid . "\">\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t\t" . $Language[4] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t" . (isset($Error[0]) && $Error[0] != "" ? "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"2\"><div class=\"icon-error\">" . implode("<br />", $Error) . "</div></td></tr>" : "") . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[8] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t" . $Language[47] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<input $type = \"text\" $name = \"filename\" $value = \"" . $filename . "\" $size = \"30\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t\t" . $Language[48] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<textarea $name = \"description\" $cols = \"70\" $rows = \"4\">" . $description . "</textarea>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[10] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t\t" . $Language[49] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<select $name = \"months\">" . $months . "</select>\r\n\t\t\t\t\t\t\t<select $name = \"weeks\">" . $weeks . "</select>\r\n\t\t\t\t\t\t\t<select $name = \"days\">" . $days . "</select>\r\n\t\t\t\t\t\t\t<select $name = \"hours\">" . $hours . "</select>\r\n\t\t\t\t\t\t\t<select $name = \"minutes\">" . $minutes . "</select>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[17] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t\t" . $Language[50] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == "1" ? " $checked = \"checked\"" : "") . " /> " . $Language[15] . "\r\n\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == "0" ? " $checked = \"checked\"" : "") . " /> " . $Language[16] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[12] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t\t" . $Language[51] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"loglevel\" $value = \"1\"" . ($loglevel == "1" ? " $checked = \"checked\"" : "") . " /> " . $Language[15] . "\r\n\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"loglevel\" $value = \"0\"" . ($loglevel == "0" ? " $checked = \"checked\"" : "") . " /> " . $Language[16] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[24] . "\" /> <input $type = \"reset\" $value = \"" . $Language[25] . "\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</form>\r\n\t\t\t";
        }
    } else {
        $Message = showAlertError($Language[44]);
    }
}
if ($Act == "new") {
    $TArray = [];
    $TArray["months"] = 0;
    $TArray["weeks"] = 0;
    $TArray["days"] = 0;
    $TArray["hours"] = 0;
    $TArray["minutes"] = 0;
    $filename = "";
    $description = "";
    $active = 1;
    $loglevel = 1;
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $filename = trim($_POST["filename"]);
        $description = trim($_POST["description"]);
        $active = isset($_POST["active"]) && $_POST["active"] == "1" ? "1" : "0";
        $loglevel = isset($_POST["loglevel"]) && $_POST["loglevel"] == "1" ? "1" : "0";
        $mosecs = 2678400;
        $wsecs = 604800;
        $dsecs = 86400;
        $hsecs = 3600;
        $msecs = 60;
        $minutes = 0;
        if (0 < $_POST["months"]) {
            $minutes += $mosecs * $_POST["months"];
        }
        if (0 < $_POST["weeks"]) {
            $minutes += $wsecs * $_POST["weeks"];
        }
        if (0 < $_POST["days"]) {
            $minutes += $dsecs * $_POST["days"];
        }
        if (0 < $_POST["hours"]) {
            $minutes += $hsecs * $_POST["hours"];
        }
        if (0 < $_POST["minutes"]) {
            $minutes += $msecs * $_POST["minutes"];
        }
        $TArray = function_326($minutes);
        if ($filename && file_exists("../include/cron/" . $filename)) {
            if (0 < $minutes) {
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_cron (minutes, filename, description, loglevel, active) VALUES (" . $minutes . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $filename) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', " . $loglevel . ", " . $active . ")");
                logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[27]));
                $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . $Language[52] . "</div></td></tr>";
            } else {
                $Error[] = $Language[46];
            }
        } else {
            $Error[] = $Language[45];
        }
    }
    if (empty($STATUS)) {
        $months = "";
        for ($i = 0; $i <= 12; $i++) {
            $months .= "<option $value = \"" . $i . "\"" . function_318($TArray["months"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[31], $Language[30]) . "</option>";
        }
        $weeks = "";
        for ($i = 0; $i <= 4; $i++) {
            $weeks .= "<option $value = \"" . $i . "\"" . function_318($TArray["weeks"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[33], $Language[32]) . "</option>";
        }
        $days = "";
        for ($i = 0; $i <= 31; $i++) {
            $days .= "<option $value = \"" . $i . "\"" . function_318($TArray["days"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[35], $Language[34]) . "</option>";
        }
        $hours = "";
        for ($i = 0; $i <= 24; $i++) {
            $hours .= "<option $value = \"" . $i . "\"" . function_318($TArray["hours"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[37], $Language[36]) . "</option>";
        }
        $minutes = "";
        for ($i = 0; $i <= 60; $i++) {
            $minutes .= "<option $value = \"" . $i . "\"" . function_318($TArray["minutes"] == $i, " $selected = \"selected\"") . ">" . $i . " " . function_318(1 < $i, $Language[39], $Language[38]) . "</option>";
        }
        $ActionTab = "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_cronjobs&amp;$act = new\">\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"3\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[23] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . (isset($Error[0]) && $Error[0] != "" ? "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"4\"><div class=\"icon-error\">" . implode("<br />", $Error) . "</div></td></tr>" : "") . "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[8] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[47] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"filename\" $value = \"" . $filename . "\" $style = \"width: 433px;\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $Language[48] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<textarea $name = \"description\" $style = \"width: 433px; height: 30px;\">" . $description . "</textarea>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[10] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $Language[49] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<select $name = \"months\">" . $months . "</select>\r\n\t\t\t\t\t\t<select $name = \"weeks\">" . $weeks . "</select>\r\n\t\t\t\t\t\t<select $name = \"days\">" . $days . "</select>\r\n\t\t\t\t\t\t<select $name = \"hours\">" . $hours . "</select>\r\n\t\t\t\t\t\t<select $name = \"minutes\">" . $minutes . "</select>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[17] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $Language[50] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == "1" ? " $checked = \"checked\"" : "") . " /> " . $Language[15] . "\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == "0" ? " $checked = \"checked\"" : "") . " /> " . $Language[16] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[12] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $Language[51] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"loglevel\" $value = \"1\"" . ($loglevel == "1" ? " $checked = \"checked\"" : "") . " /> " . $Language[15] . "\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"loglevel\" $value = \"0\"" . ($loglevel == "0" ? " $checked = \"checked\"" : "") . " /> " . $Language[16] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[24] . "\" /> <input $type = \"reset\" $value = \"" . $Language[25] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</form>\r\n\t\t";
    }
}
if (!isset($List)) {
    $Count = 0;
    $List = "";
    for ($query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_cron ORDER by active DESC, minutes"); $Cron = mysqli_fetch_assoc($query); $Count++) {
        $class = $Count % 2 == 1 ? "alt2" : "alt1";
        $List .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"" . $class . "\">" . htmlspecialchars($Cron["filename"]) . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . htmlspecialchars($Cron["description"]) . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . ($Cron["minutes"] ? formatSecondsToTime($Cron["minutes"]) : "---") . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . ($Cron["nextrun"] ? formatTimestamp($Cron["nextrun"]) : "---") . "</td>\r\n\t\t\t<td class=\"" . $class . "\" $align = \"center\">" . ($Cron["loglevel"] == 1 ? $Language[15] : $Language[16]) . "</td>\r\n\t\t\t<td class=\"" . $class . "\"><font $color = \"" . ($Cron["active"] == 1 ? "green\">" . $Language[17] : "red\">" . $Language[18]) . "</font></td>\r\n\t\t\t<td class=\"" . $class . "\" $align = \"center\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_cronjobs&amp;$act = set_status&amp;$cronid = " . $Cron["cronid"] . "\"><img $src = \"images/" . ($Cron["active"] == 1 ? "alert" : "accept") . ".png\" $alt = \"" . trim($Language[$Cron["active"] == 1 ? "6" : "7"]) . "\" $title = \"" . trim($Language[$Cron["active"] == 1 ? "6" : "7"]) . "\" $border = \"0\" /></a>\r\n\t\t\t\t<a $href = \"index.php?do=manage_cronjobs&amp;$act = run&amp;$cronid = " . $Cron["cronid"] . "\"><img $src = \"images/tool_refresh.png\" $alt = \"" . trim($Language[5]) . "\" $title = \"" . trim($Language[5]) . "\" $border = \"0\" /></a>\r\n\t\t\t\t<a $href = \"index.php?do=manage_cronjobs&amp;$act = edit&amp;$cronid = " . $Cron["cronid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[4]) . "\" $title = \"" . trim($Language[4]) . "\" $border = \"0\" /></a>\r\n\t\t\t\t<a $href = \"#\" $onclick = \"ConfirmDelete(" . $Cron["cronid"] . ");\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[3]) . "\" $title = \"" . trim($Language[3]) . "\" $border = \"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $ListLogs = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\">\r\n\t\t\t\t" . $Language[19] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[8] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[20] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[21] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[22] . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>";
    $Count = 0;
    for ($query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_cron_log ORDER by runtime DESC, querycount"); $Logs = mysqli_fetch_assoc($query); $Count++) {
        $class = $Count % 2 == 1 ? "alt2" : "alt1";
        $ListLogs .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"" . $class . "\">" . htmlspecialchars($Logs["filename"]) . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . number_format($Logs["querycount"]) . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . htmlspecialchars($Logs["executetime"]) . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . ($Logs["runtime"] ? formatTimestamp($Logs["runtime"]) : "---") . "</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $ListLogs .= "\r\n\t</table>";
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction ConfirmDelete(ToolID)\r\n\t\t{\r\n\t\t\tif (confirm(\"" . trim($Language[26]) . "\"))\r\n\t\t\t{\r\n\t\t\t\tTSJump(\"index.php?do=manage_cronjobs&$act = delete&$cronid = \"+ToolID);\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . showAlertMessage("<a $href = \"index.php?do=manage_cronjobs&amp;$act = new\">" . $Language[23] . "</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a $href = \"index.php?do=manage_settings&amp;$tab = 1&amp;$stab = 99\">" . $Language[53] . "</a>") . "\r\n\t" . $Message . "\r\n\t" . (isset($ActionTab) ? $ActionTab : "") . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"7\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $STATUS . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[8] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[9] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[10] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[11] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t<b>" . $Language[12] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[13] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t<b>" . $Language[14] . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $List . "\r\n\t</table>\r\n\t" . $ListLogs;
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
function formatTimestamp($timestamp = "")
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, $timestamp);
}
function function_89($stamp = "")
{
    global $Language;
    $dateFrom = 31536000;
    $mosecs = 2678400;
    $wsecs = 604800;
    $dsecs = 86400;
    $hsecs = 3600;
    $msecs = 60;
    $timeDiff = floor($stamp / $dateFrom);
    $stamp %= $dateFrom;
    $months = floor($stamp / $mosecs);
    $stamp %= $mosecs;
    $weeks = floor($stamp / $wsecs);
    $stamp %= $wsecs;
    $days = floor($stamp / $dsecs);
    $stamp %= $dsecs;
    $hours = floor($stamp / $hsecs);
    $stamp %= $hsecs;
    $minutes = floor($stamp / $msecs);
    $stamp %= $msecs;
    $timePeriod = $stamp;
    if ($timeDiff == 1) {
        $timePeriodDisplay["years"] = "<b>1</b> " . $Language[28];
    } else {
        if (1 < $timeDiff) {
            $timePeriodDisplay["years"] = "<b>" . $timeDiff . "</b> " . $Language[29];
        }
    }
    if ($months == 1) {
        $timePeriodDisplay["months"] = "<b>1</b> " . $Language[30];
    } else {
        if (1 < $months) {
            $timePeriodDisplay["months"] = "<b>" . $months . "</b> " . $Language[31];
        }
    }
    if ($weeks == 1) {
        $timePeriodDisplay["weeks"] = "<b>1</b> " . $Language[32];
    } else {
        if (1 < $weeks) {
            $timePeriodDisplay["weeks"] = "<b>" . $weeks . "</b> " . $Language[33];
        }
    }
    if ($days == 1) {
        $timePeriodDisplay["days"] = "<b>1</b> " . $Language[34];
    } else {
        if (1 < $days) {
            $timePeriodDisplay["days"] = "<b>" . $days . "</b> " . $Language[35];
        }
    }
    if ($hours == 1) {
        $timePeriodDisplay["hours"] = "<b>1</b> " . $Language[36];
    } else {
        if (1 < $hours) {
            $timePeriodDisplay["hours"] = "<b>" . $hours . "</b> " . $Language[37];
        }
    }
    if ($minutes == 1) {
        $timePeriodDisplay["minutes"] = "<b>1</b> " . $Language[38];
    } else {
        if (1 < $minutes) {
            $timePeriodDisplay["minutes"] = "<b>" . $minutes . "</b> " . $Language[39];
        }
    }
    if ($timePeriod == 1) {
        $timePeriodDisplay["seconds"] = "<b>1</b> " . $Language[40];
    } else {
        if (1 < $timePeriod) {
            $timePeriodDisplay["seconds"] = "<b>" . $timePeriod . "</b> " . $Language[41];
        }
    }
    if (isset($timePeriodDisplay) && is_array($timePeriodDisplay)) {
        $cronTask = implode(", ", $timePeriodDisplay);
    } else {
        $cronTask = "0 " . $Language[40];
    }
    return "<small\">" . $cronTask . "</small>";
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_326($stamp)
{
    $dateFrom = 31536000;
    $mosecs = 2678400;
    $wsecs = 604800;
    $dsecs = 86400;
    $hsecs = 3600;
    $msecs = 60;
    $timeDiff = floor($stamp / $dateFrom);
    $stamp %= $dateFrom;
    $months = floor($stamp / $mosecs);
    $stamp %= $mosecs;
    $weeks = floor($stamp / $wsecs);
    $stamp %= $wsecs;
    $days = floor($stamp / $dsecs);
    $stamp %= $dsecs;
    $hours = floor($stamp / $hsecs);
    $stamp %= $hsecs;
    $minutes = floor($stamp / $msecs);
    $stamp %= $msecs;
    $timePeriod = $stamp;
    return ["years" => $timeDiff, "months" => $months, "weeks" => $weeks, "days" => $days, "hours" => $hours, "minutes" => $minutes];
}
function function_318($expression, $returntrue, $returnfalse = "")
{
    return $expression ? $returntrue : $returnfalse;
}

?>