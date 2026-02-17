<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthenticationModern();

// Load language file
$Language = loadStaffLanguage('manage_cronjobs');
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$STATUS = "";
$Error = [];
if ($Act == "delete" && ($cronid = intval($_GET["cronid"]))) {
    try {
        $stmt = $TSDatabase->prepare("DELETE FROM ts_cron WHERE cronid = ?");
        $stmt->execute([$cronid]);
        logStaffActionModern(str_replace("{1}", $_SESSION["ADMIN_USERNAME"] ?? 'Unknown', $Language[27]));
        $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . escape_html($Language[42]) . "</div></td></tr>";
    } catch (Exception $e) {
        $Error[] = "Database error: " . escape_html($e->getMessage());
    }
}
if ($Act == "set_status" && ($cronid = intval($_GET["cronid"]))) {
    try {
        $stmt = $TSDatabase->prepare("UPDATE ts_cron SET active = IF(active = 1, 0, 1) WHERE cronid = ?");
        $stmt->execute([$cronid]);
        logStaffActionModern(str_replace("{1}", $_SESSION["ADMIN_USERNAME"] ?? 'Unknown', $Language[27]));
        $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . escape_html($Language[43]) . "</div></td></tr>";
    } catch (Exception $e) {
        $Error[] = "Database error: " . escape_html($e->getMessage());
    }
}
if ($Act == "run" && ($cronid = intval($_GET["cronid"]))) {
    try {
        $stmt = $TSDatabase->prepare("UPDATE ts_cron SET nextrun = 0 WHERE cronid = ?");
        $stmt->execute([$cronid]);
        logStaffActionModern(str_replace("{1}", $_SESSION["ADMIN_USERNAME"] ?? 'Unknown', $Language[27]));
        $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . escape_html($Language[43]) . "</div></td></tr>";
    } catch (Exception $e) {
        $Error[] = "Database error: " . escape_html($e->getMessage());
    }
}
if ($Act == "edit" && ($cronid = intval($_GET["cronid"]))) {
    try {
        $stmt = $TSDatabase->prepare("SELECT * FROM ts_cron WHERE cronid = ?");
        $stmt->execute([$cronid]);
        $cronData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $cronData = false;
    }
    if ($cronData) {
        if (strtoupper($_SERVER["REQUEST_METHOD"] ?? '') == "POST") {
            // Validate CSRF token
            $formToken = $_POST['form_token'] ?? null;
            if (!validateFormToken($formToken)) {
                $Error[] = "Invalid security token. Please try again.";
            } else {
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
                    try {
                        $stmt = $TSDatabase->prepare("UPDATE ts_cron SET minutes = ?, filename = ?, description = ?, loglevel = ?, active = ? WHERE cronid = ?");
                        $stmt->execute([$minutes, $filename, $description, $loglevel, $active, $cronid]);
                        logStaffActionModern(str_replace("{1}", $_SESSION["ADMIN_USERNAME"] ?? 'Unknown', $Language[27]));
                        $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . escape_html($Language[43]) . "</div></td></tr>";
                    } catch (Exception $e) {
                        $Error[] = "Database error: " . escape_html($e->getMessage());
                    }
                } else {
                    $Error[] = $Language[46];
                }
            } else {
                $Error[] = $Language[45];
            }
            }
        } else {
            $filename = trim(escape_html($cronData["filename"]));
            $description = trim(escape_html($cronData["description"]));
            $active = $cronData["active"];
            $loglevel = $cronData["loglevel"];
            $TArray = function_326($cronData["minutes"]);
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
            $formTokenField = getFormTokenField();
            $ActionTab = "\r\n\t\t\t<form $method = \"post\" $action = \"" . escape_attr($_SERVER["SCRIPT_NAME"] . "?do=manage_cronjobs&act=edit&cronid=" . $cronid) . "\">\r\n\t\t\t\t" . $formTokenField . "\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t\t" . escape_html($Language[4]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t" . (isset($Error[0]) && $Error[0] != "" ? "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"2\"><div class=\"icon-error\">" . implode("<br />", array_map('escape_html', $Error)) . "</div></td></tr>" : "") . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t\t" . escape_html($Language[47]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<input $type = \"text\" $name = \"filename\" $value = \"" . escape_attr($filename) . "\" $size = \"30\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[9]) . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t\t" . escape_html($Language[48]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<textarea $name = \"description\" $cols = \"70\" $rows = \"4\">" . escape_html($description) . "</textarea>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[10]) . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t\t" . escape_html($Language[49]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<select $name = \"months\">" . $months . "</select>\r\n\t\t\t\t\t\t\t<select $name = \"weeks\">" . $weeks . "</select>\r\n\t\t\t\t\t\t\t<select $name = \"days\">" . $days . "</select>\r\n\t\t\t\t\t\t\t<select $name = \"hours\">" . $hours . "</select>\r\n\t\t\t\t\t\t\t<select $name = \"minutes\">" . $minutes . "</select>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[17]) . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t\t" . escape_html($Language[50]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == "1" ? " $checked = \"checked\"" : "") . " /> " . escape_html($Language[15]) . "\r\n\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == "0" ? " $checked = \"checked\"" : "") . " /> " . escape_html($Language[16]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[12]) . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t\t" . escape_html($Language[51]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"loglevel\" $value = \"1\"" . ($loglevel == "1" ? " $checked = \"checked\"" : "") . " /> " . escape_html($Language[15]) . "\r\n\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"loglevel\" $value = \"0\"" . ($loglevel == "0" ? " $checked = \"checked\"" : "") . " /> " . escape_html($Language[16]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . escape_attr($Language[24]) . "\" /> <input $type = \"reset\" $value = \"" . escape_attr($Language[25]) . "\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</form>\r\n\t\t\t";
        }
    } else {
        $Message = showAlertErrorModern($Language[44]);
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
    if (strtoupper($_SERVER["REQUEST_METHOD"] ?? '') == "POST") {
        // Validate CSRF token
        $formToken = $_POST['form_token'] ?? null;
        if (!validateFormToken($formToken)) {
            $Error[] = "Invalid security token. Please try again.";
        } else {
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
                try {
                    $stmt = $TSDatabase->prepare("INSERT INTO ts_cron (minutes, filename, description, loglevel, active) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$minutes, $filename, $description, $loglevel, $active]);
                    logStaffActionModern(str_replace("{1}", $_SESSION["ADMIN_USERNAME"] ?? 'Unknown', $Language[27]));
                    $STATUS = "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"7\"><div class=\"icon-ok\">" . escape_html($Language[52]) . "</div></td></tr>";
                } catch (Exception $e) {
                    $Error[] = "Database error: " . escape_html($e->getMessage());
                }
            } else {
                $Error[] = $Language[46];
            }
        } else {
            $Error[] = $Language[45];
        }
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
        $formTokenField = getFormTokenField();
        $ActionTab = "\r\n\t\t<form $method = \"post\" $action = \"" . escape_attr($_SERVER["SCRIPT_NAME"] . "?do=manage_cronjobs&act=new") . "\">\r\n\t\t\t" . $formTokenField . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"3\" $align = \"center\">\r\n\t\t\t\t\t\t" . escape_html($Language[23]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . (isset($Error[0]) && $Error[0] != "" ? "<tr><td class=\"alt2\" $align = \"center\" $colspan = \"4\"><div class=\"icon-error\">" . implode("<br />", array_map('escape_html', $Error)) . "</div></td></tr>" : "") . "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . escape_html($Language[47]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"filename\" $value = \"" . escape_attr($filename) . "\" $style = \"width: 433px;\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[9]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t" . escape_html($Language[48]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<textarea $name = \"description\" $style = \"width: 433px; height: 30px;\">" . escape_html($description) . "</textarea>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[10]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t" . escape_html($Language[49]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<select $name = \"months\">" . $months . "</select>\r\n\t\t\t\t\t\t<select $name = \"weeks\">" . $weeks . "</select>\r\n\t\t\t\t\t\t<select $name = \"days\">" . $days . "</select>\r\n\t\t\t\t\t\t<select $name = \"hours\">" . $hours . "</select>\r\n\t\t\t\t\t\t<select $name = \"minutes\">" . $minutes . "</select>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[17]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t" . escape_html($Language[50]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == "1" ? " $checked = \"checked\"" : "") . " /> " . escape_html($Language[15]) . "\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == "0" ? " $checked = \"checked\"" : "") . " /> " . escape_html($Language[16]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[12]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t\t" . escape_html($Language[51]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"loglevel\" $value = \"1\"" . ($loglevel == "1" ? " $checked = \"checked\"" : "") . " /> " . escape_html($Language[15]) . "\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"loglevel\" $value = \"0\"" . ($loglevel == "0" ? " $checked = \"checked\"" : "") . " /> " . escape_html($Language[16]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . escape_attr($Language[24]) . "\" /> <input $type = \"reset\" $value = \"" . escape_attr($Language[25]) . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</form>\r\n\t\t";
    }
}
if (!isset($List)) {
    $Count = 0;
    $List = "";
    try {
        $stmt = $TSDatabase->prepare("SELECT * FROM ts_cron ORDER by active DESC, minutes");
        $stmt->execute();
        while ($Cron = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $class = $Count % 2 == 1 ? "alt2" : "alt1";
            $cronid = (int)$Cron["cronid"];
            $filename = escape_html($Cron["filename"]);
            $description = escape_html($Cron["description"]);
            $minutes = $Cron["minutes"] ? formatSecondsToTime((int)$Cron["minutes"]) : "---";
            $nextrun = $Cron["nextrun"] ? formatTimestamp($Cron["nextrun"]) : "---";
            $loglevel = $Cron["loglevel"] == 1 ? escape_html($Language[15]) : escape_html($Language[16]);
            $activeStatus = $Cron["active"] == 1 ? escape_html($Language[17]) : escape_html($Language[18]);
            $activeColor = $Cron["active"] == 1 ? "green" : "red";
            $activeIcon = $Cron["active"] == 1 ? "alert" : "accept";
            $activeTitle = trim($Language[$Cron["active"] == 1 ? "6" : "7"]);
            
            $List .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"" . $class . "\">" . $filename . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . $description . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . $minutes . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . $nextrun . "</td>\r\n\t\t\t<td class=\"" . $class . "\" $align = \"center\">" . $loglevel . "</td>\r\n\t\t\t<td class=\"" . $class . "\"><font $color = \"" . $activeColor . "\">" . $activeStatus . "</font></td>\r\n\t\t\t<td class=\"" . $class . "\" $align = \"center\">\r\n\t\t\t\t<a $href = \"" . escape_attr("index.php?do=manage_cronjobs&act=set_status&cronid=" . $cronid) . "\"><img $src = \"images/" . $activeIcon . ".png\" $alt = \"" . escape_attr($activeTitle) . "\" $title = \"" . escape_attr($activeTitle) . "\" $border = \"0\" /></a>\r\n\t\t\t\t<a $href = \"" . escape_attr("index.php?do=manage_cronjobs&act=run&cronid=" . $cronid) . "\"><img $src = \"images/tool_refresh.png\" $alt = \"" . escape_attr(trim($Language[5])) . "\" $title = \"" . escape_attr(trim($Language[5])) . "\" $border = \"0\" /></a>\r\n\t\t\t\t<a $href = \"" . escape_attr("index.php?do=manage_cronjobs&act=edit&cronid=" . $cronid) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . escape_attr(trim($Language[4])) . "\" $title = \"" . escape_attr(trim($Language[4])) . "\" $border = \"0\" /></a>\r\n\t\t\t\t<a $href = \"#\" $onclick = \"ConfirmDelete(" . $cronid . ");\"><img $src = \"images/tool_delete.png\" $alt = \"" . escape_attr(trim($Language[3])) . "\" $title = \"" . escape_attr(trim($Language[3])) . "\" $border = \"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
            $Count++;
        }
    } catch (Exception $e) {
        $List = "";
    }
    $ListLogs = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\">\r\n\t\t\t\t" . escape_html($Language[19]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[8]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[20]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[21]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[22]) . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>";
    $Count = 0;
    try {
        $stmt = $TSDatabase->prepare("SELECT * FROM ts_cron_log ORDER by runtime DESC, querycount");
        $stmt->execute();
        while ($Logs = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $class = $Count % 2 == 1 ? "alt2" : "alt1";
            $filename = escape_html($Logs["filename"]);
            $querycount = number_format((int)$Logs["querycount"]);
            $executetime = escape_html($Logs["executetime"]);
            $runtime = $Logs["runtime"] ? formatTimestamp($Logs["runtime"]) : "---";
            $ListLogs .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"" . $class . "\">" . $filename . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . $querycount . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . $executetime . "</td>\r\n\t\t\t<td class=\"" . $class . "\">" . $runtime . "</td>\r\n\t\t</tr>\r\n\t\t";
            $Count++;
        }
    } catch (Exception $e) {
        // Continue with empty list
    }
    $ListLogs .= "\r\n\t</table>";
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction ConfirmDelete(ToolID)\r\n\t\t{\r\n\t\t\tif (confirm(\"" . escape_js(trim($Language[26])) . "\"))\r\n\t\t\t{\r\n\t\t\t\tTSJump(\"index.php?do=manage_cronjobs&$act = delete&$cronid = \"+ToolID);\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . showAlertMessage("<a $href = \"" . escape_attr("index.php?do=manage_cronjobs&act=new") . "\">" . escape_html($Language[23]) . "</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a $href = \"" . escape_attr("index.php?do=manage_settings&tab=1&stab=99") . "\">" . escape_html($Language[53]) . "</a>") . "\r\n\t" . $Message . "\r\n\t" . (isset($ActionTab) ? $ActionTab : "") . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"7\">\r\n\t\t\t\t" . escape_html($Language[2]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $STATUS . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[8]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[9]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[10]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[11]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t<b>" . escape_html($Language[12]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . escape_html($Language[13]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t<b>" . escape_html($Language[14]) . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $List . "\r\n\t</table>\r\n\t" . $ListLogs;
} else {
    echo $List;
}

// Keep helper functions for backward compatibility
function formatSecondsToTime($sec, $padHours = false)
{
    $formattedTime = "";
    $hours = intval($sec / 3600);
    $formattedTime .= $padHours ? str_pad((string)$hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":";
    $minutes = intval($sec / 60 % 60);
    $formattedTime .= str_pad((string)$minutes, 2, "0", STR_PAD_LEFT) . ":";
    $seconds = intval($sec % 60);
    $formattedTime .= str_pad((string)$seconds, 2, "0", STR_PAD_LEFT);
    return $formattedTime;
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
    return date($dateFormatPattern, (int)$timestamp);
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

function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
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
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . escape_js($url) . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . escape_attr($url) . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . escape_js($url) . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . escape_attr($url) . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
    }
    exit;
}

function showAlertError($Error)
{
    return showAlertErrorModern($Error);
}

function logStaffAction($log)
{
    logStaffActionModern($log);
}

?>