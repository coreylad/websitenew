<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

$GLOBALS["yesnocount"] = 0;
checkStaffAuthenticationModern();
$Language = loadStaffLanguage('usergroups');
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if ($Act == "save_disporder") {
    // Validate CSRF token
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token. Please try again.');
    } else {
        $Act = "";
        try {
            foreach ($_POST["disporder"] as $gid => $disporder) {
                $TSDatabase->query("UPDATE usergroups SET disporder = ? WHERE gid = ?", [intval($disporder), intval($gid)]);
            }
            $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[163]);
            logStaffActionModern($Message);
            $Message = showAlertSuccessModern($Message);
        } catch (Exception $e) {
            error_log('Save disporder error: ' . $e->getMessage());
            $Message = showAlertErrorModern('Failed to save display order');
        }
    }
}
if ($Act == "delete" && ($gid = intval($_GET["gid"]))) {
    try {
        $result = $TSDatabase->query("SELECT title, type FROM usergroups WHERE gid = ?", [$gid]);
        if ($result && $Ug = $result->fetch(PDO::FETCH_ASSOC)) {
            if (isset($_POST["newgid"]) && ($newgid = intval($_POST["newgid"]))) {
                // Validate CSRF token
                if (!validateFormToken($_POST['form_token'] ?? '')) {
                    $Message = showAlertErrorModern('Invalid form token. Please try again.');
                } elseif ($Ug["type"] == 1) {
                    $Message = showAlertErrorModern($Language[11]);
                } else {
                    try {
                        $result2 = $TSDatabase->query("SELECT title FROM usergroups WHERE gid = ?", [$newgid]);
                        if ($result2 && $Result = $result2->fetch(PDO::FETCH_ASSOC)) {
                            $Newgroupname = $Result["title"];
                            $TSDatabase->query("UPDATE users SET `usergroup` = ? WHERE `usergroup` = ?", [$newgid, $gid]);
                            $TSDatabase->query("DELETE FROM usergroups WHERE gid = ?", [$gid]);
                            $Message = str_replace(["{1}", "{2}", "{3}"], [$Ug["title"], $_SESSION["ADMIN_USERNAME"], $Newgroupname], $Language[13]);
                            logStaffActionModern($Message);
                            $Message = showAlertSuccessModern($Message);
                        }
                    } catch (Exception $e) {
                        error_log('Delete usergroup error: ' . $e->getMessage());
                        $Message = showAlertErrorModern('Failed to delete usergroup');
                    }
                }
            } else {
                $Selectbox = "<select name = \"newgid\">";
                $result3 = $TSDatabase->query("SELECT gid, title FROM usergroups WHERE gid != ?", [$gid]);
                if ($result3) {
                    while ($usergroup = $result3->fetch(PDO::FETCH_ASSOC)) {
                        $Selectbox .= "<option value = \"" . escape_attr((string)$usergroup["gid"]) . "\">" . escape_html($usergroup["title"]) . "</option>";
                    }
                }
                $Selectbox .= "</select>";
                $Output = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t" . str_replace("{1}", escape_html($Ug["title"]), $Language[15]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<form method = \"post\" action = \"index.php?do=usergroups&act = delete&gid = " . escape_attr((string)$gid) . "\">\r\n\t\t\t\t\t" . getFormTokenField() . "\r\n\t\t\t\t\t" . $Language[12] . " " . $Selectbox . " <input type = \"submit\" value = \"" . escape_attr($Language[4]) . "\" /> <input type = \"button\" value = \"" . escape_attr($Language[14]) . "\" onclick = \"window.location.href = 'index.php?do=usergroups';\" />\r\n\t\t\t\t\t</form>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
            }
        }
    } catch (Exception $e) {
        error_log('Delete usergroup check error: ' . $e->getMessage());
        $Message = showAlertErrorModern('Failed to process usergroup deletion');
    }
}
if ($Act == "new" || $Act == "edit" && ($gid = intval($_GET["gid"]))) {
    $clonegid = isset($_GET["clonegid"]) ? intval($_GET["clonegid"]) : "";
    $title = "";
    $description = "";
    $namestyle = "{username}";
    if ($Act == "edit") {
        try {
            $result = $TSDatabase->query("SELECT * FROM usergroups WHERE gid = ?", [$gid]);
            if (!$result || !($EditUG = $result->fetch(PDO::FETCH_ASSOC))) {
                $STOP = true;
            } else {
                $usergroupcache = $EditUG;
                $title = $EditUG["title"];
                $description = $EditUG["description"];
                $namestyle = $EditUG["namestyle"];
            }
        } catch (Exception $e) {
            error_log('Edit usergroup load error: ' . $e->getMessage());
            $STOP = true;
        }
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && !isset($STOP)) {
        // Validate CSRF token
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            $Message = showAlertErrorModern('Invalid form token. Please try again.');
        } else {
            $title = trim($_POST["title"]);
            $description = trim($_POST["description"]);
            $namestyle = trim($_POST["namestyle"]);
            try {
                if ($Act == "new") {
                    $TSDatabase->query("INSERT INTO usergroups (title, description, namestyle, type) VALUES (?, ?, ?, 2)", 
                        [$title, $description, $namestyle]);
                    $NewGID = $TSDatabase->lastInsertId();
                } else {
                    $NewGID = $gid;
                    $TSDatabase->query("UPDATE usergroups SET title = ?, description = ?, namestyle = ? WHERE gid = ?", 
                        [$title, $description, $namestyle, $NewGID]);
                }
                $SGPerms = "";
                foreach ($_POST["SGPERMS"] as $Field => $Value) {
                    $SGPerms .= $Value == "yes" ? "1" : "0";
                }
                $_POST["UGCONFIG"]["sgperms"] = $SGPerms;
                $blogperms = "";
                foreach ($_POST["BLOGPERMS"] as $Field => $Value) {
                    if ($Field == "maxblogs") {
                        $blogperms .= substr(intval($Value), 0, 1);
                    } else {
                        $blogperms .= $Value == "yes" ? "1" : "0";
                    }
                }
                $_POST["UGCONFIG"]["blogperms"] = $blogperms;
                $edituserperms = "";
                foreach ($_POST["EDITUSERPERMS"] as $Field => $Value) {
                    $edituserperms .= $Value == "yes" ? "1" : "0";
                }
                $_POST["UGCONFIG"]["edituserperms"] = $edituserperms;
                $customizeprofile = "";
                foreach ($_POST["CUSTOMIZEPROFILEPERMS"] as $Field => $Value) {
                    $customizeprofile .= $Value == "yes" ? "1" : "0";
                }
                $_POST["UGCONFIG"]["customizeprofile"] = $customizeprofile;
                $QueryArray = [];
                foreach ($_POST["UGCONFIG"] as $Field => $Value) {
                    $QueryArray[] = $Field . " = ?";
                }
                $values = array_values($_POST["UGCONFIG"]);
                $values[] = $NewGID;
                $TSDatabase->query("UPDATE usergroups SET " . implode(",", $QueryArray) . " WHERE gid = ?", $values);
                $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[$Act == "new" ? "23" : "157"]);
                logStaffActionModern($Message);
                $Message = showAlertSuccessModern($Message);
                unset($Act);
            } catch (Exception $e) {
                error_log('Save usergroup error: ' . $e->getMessage());
                $Message = showAlertErrorModern('Failed to save usergroup');
            }
        }
    } else {
        if (!isset($STOP)) {
            if ($Act == "new") {
                if ($clonegid) {
                    try {
                        $result = $TSDatabase->query("SELECT * FROM usergroups WHERE gid = ?", [$clonegid]);
                        if ($result && $row = $result->fetch(PDO::FETCH_ASSOC)) {
                            $EditUG = $row;
                        }
                    } catch (Exception $e) {
                        error_log('Clone usergroup error: ' . $e->getMessage());
                    }
                }
                if (!isset($EditUG)) {
                    $EditUG["isbanned"] = "no";
                    $EditUG["canpm"] = "yes";
                    $EditUG["candownload"] = "yes";
                    $EditUG["candirectdownload"] = "yes";
                    $EditUG["canuploadddl"] = "yes";
                    $EditUG["canupload"] = "no";
                    $EditUG["canrequest"] = "yes";
                    $EditUG["cancomment"] = "yes";
                    $EditUG["canreport"] = "yes";
                    $EditUG["canbookmark"] = "yes";
                    $EditUG["canresetpasskey"] = "no";
                    $EditUG["canviewotherprofile"] = "no";
                    $EditUG["canvote"] = "yes";
                    $EditUG["canrate"] = "yes";
                    $EditUG["canthanks"] = "yes";
                    $EditUG["canshout"] = "yes";
                    $EditUG["caninvite"] = "no";
                    $EditUG["canbonus"] = "no";
                    $EditUG["canmemberlist"] = "no";
                    $EditUG["canfriendlist"] = "no";
                    $EditUG["cansnatch"] = "no";
                    $EditUG["canpeers"] = "no";
                    $EditUG["cantopten"] = "no";
                    $EditUG["cansettingspanel"] = "no";
                    $EditUG["canstaffpanel"] = "no";
                    $EditUG["pmquote"] = "30";
                    $EditUG["floodlimit"] = "60";
                    $EditUG["autoinvite"] = "0";
                    $EditUG["namestyle"] = "{username}";
                    $EditUG["showstaffteam"] = "no";
                    $EditUG["issupermod"] = "no";
                    $EditUG["canaccessoffline"] = "no";
                    $EditUG["isvipgroup"] = "no";
                    $EditUG["canfreeleech"] = "no";
                    $EditUG["canbaduser"] = "no";
                    $EditUG["candeletetorrent"] = "no";
                    $EditUG["isforummod"] = "no";
                    $EditUG["cantransfer"] = "no";
                    $EditUG["canmassdelete"] = "no";
                    $EditUG["canemailnotify"] = "no";
                    $EditUG["cansignature"] = "no";
                    $EditUG["waitlimit"] = "24";
                    $EditUG["slotlimit"] = "3";
                    $EditUG["canexternal"] = "no";
                    $EditUG["cancreatepoll"] = "no";
                    $EditUG["sgperms"] = "10001000";
                    $EditUG["blogperms"] = "100000000";
                    $EditUG["canseedbox"] = "no";
                    $EditUG["showads"] = "yes";
                    $EditUG["edituserperms"] = "000000000";
                    $EditUG["customizeprofile"] = "0000";
                }
                $usergroupcache = $EditUG;
            }
            $Output = "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<form method = \"post\" action = \"index.php?do=usergroups&act = " . ($Act == "new" ? "new" : "edit&gid = " . escape_attr((string)$gid)) . "\">\r\n\t\t\t\t" . getFormTokenField() . "\r\n\t\t\t\t<table width = \"100%\" align = \"center\" cellspacing = \"0\" cellpadding = \"0\" border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" colspan = \"2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" width = \"50%\" align = \"justify\">\r\n\t\t\t\t\t\t\t" . $Language[17] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" width = \"50%\" valign = \"top\">\r\n\t\t\t\t\t\t\t<input type = \"text\" name = \"title\" value = \"" . escape_attr($title) . "\" size = \"70\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2 altSaperator\" colspan = \"2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1 altSaperator\" width = \"50%\" align = \"justify\">\r\n\t\t\t\t\t\t\t" . $Language[18] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1 altSaperator\" width = \"50%\" valign = \"top\">\r\n\t\t\t\t\t\t\t<input type = \"text\" name = \"description\" value = \"" . escape_attr($description) . "\" size = \"70\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" colspan = \"2\"><b>" . $Language[19] . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" align = \"justify\" valign = \"top\" colspan = \"2\">\r\n\t\t\t\t\t\t\t" . $Language[20] . "<br />\r\n\t\t\t\t\t\t\t<textarea name = \"namestyle\" id = \"namestyle\" style = \"width: 99%; height: 40px;\">" . escape_html($namestyle) . "</textarea>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[72] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[26], $Language[27], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[isbanned]\" value = \"yes\"" . ($EditUG["isbanned"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[isbanned]\" value = \"no\"" . ($EditUG["isbanned"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[28], $Language[29], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[isvipgroup]\" value = \"yes\"" . ($EditUG["isvipgroup"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[isvipgroup]\" value = \"no\"" . ($EditUG["isvipgroup"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[30], $Language[31], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canfreeleech]\" value = \"yes\"" . ($EditUG["canfreeleech"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canfreeleech]\" value = \"no\"" . ($EditUG["canfreeleech"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[32], $Language[33], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[showstaffteam]\" value = \"yes\"" . ($EditUG["showstaffteam"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[showstaffteam]\" value = \"no\"" . ($EditUG["showstaffteam"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[showstaffteam]\" value = \"staff\"" . ($EditUG["showstaffteam"] == "staff" ? " checked = \"checked\"" : "") . " /> " . $Language[34]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[35], $Language[36], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canpm]\" value = \"yes\"" . ($EditUG["canpm"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canpm]\" value = \"no\"" . ($EditUG["canpm"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[37], $Language[38], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[candownload]\" value = \"yes\"" . ($EditUG["candownload"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[candownload]\" value = \"no\"" . ($EditUG["candownload"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[202], $Language[203], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[candirectdownload]\" value = \"yes\"" . ($EditUG["candirectdownload"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[candirectdownload]\" value = \"no\"" . ($EditUG["candirectdownload"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[204], $Language[205], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canuploadddl]\" value = \"yes\"" . ($EditUG["canuploadddl"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canuploadddl]\" value = \"no\"" . ($EditUG["canuploadddl"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[39], $Language[40], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canupload]\" value = \"yes\"" . ($EditUG["canupload"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canupload]\" value = \"no\"" . ($EditUG["canupload"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canupload]\" value = \"moderate\"" . ($EditUG["canupload"] == "moderate" ? " checked = \"checked\"" : "") . " /> " . $Language[41]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[42], $Language[43], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canexternal]\" value = \"yes\"" . ($EditUG["canexternal"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canexternal]\" value = \"no\"" . ($EditUG["canexternal"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[44], $Language[45], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cantransfer]\" value = \"yes\"" . ($EditUG["cantransfer"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cantransfer]\" value = \"no\"" . ($EditUG["cantransfer"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[46], $Language[47], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canrequest]\" value = \"yes\"" . ($EditUG["canrequest"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canrequest]\" value = \"no\"" . ($EditUG["canrequest"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[48], $Language[49], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cancomment]\" value = \"yes\"" . ($EditUG["cancomment"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cancomment]\" value = \"no\"" . ($EditUG["cancomment"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25] . " \r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cancomment]\" value = \"moderate\"" . ($EditUG["cancomment"] == "moderate" ? " checked = \"checked\"" : "") . " /> " . $Language[41]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[50], $Language[51], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canreport]\" value = \"yes\"" . ($EditUG["canreport"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canreport]\" value = \"no\"" . ($EditUG["canreport"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[52], $Language[53], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canbookmark]\" value = \"yes\"" . ($EditUG["canbookmark"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canbookmark]\" value = \"no\"" . ($EditUG["canbookmark"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[54], $Language[55], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cancreatepoll]\" value = \"yes\"" . ($EditUG["cancreatepoll"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cancreatepoll]\" value = \"no\"" . ($EditUG["cancreatepoll"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[56], $Language[57], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canvote]\" value = \"yes\"" . ($EditUG["canvote"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canvote]\" value = \"no\"" . ($EditUG["canvote"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[58], $Language[59], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canrate]\" value = \"yes\"" . ($EditUG["canrate"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canrate]\" value = \"no\"" . ($EditUG["canrate"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[60], $Language[61], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canthanks]\" value = \"yes\"" . ($EditUG["canthanks"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canthanks]\" value = \"no\"" . ($EditUG["canthanks"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[62], $Language[63], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canshout]\" value = \"yes\"" . ($EditUG["canshout"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canshout]\" value = \"no\"" . ($EditUG["canshout"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[64], $Language[65], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[caninvite]\" value = \"yes\"" . ($EditUG["caninvite"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[caninvite]\" value = \"no\"" . ($EditUG["caninvite"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[66], $Language[67], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canbonus]\" value = \"yes\"" . ($EditUG["canbonus"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canbonus]\" value = \"no\"" . ($EditUG["canbonus"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[68], $Language[69], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canresetpasskey]\" value = \"yes\"" . ($EditUG["canresetpasskey"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canresetpasskey]\" value = \"no\"" . ($EditUG["canresetpasskey"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[70], $Language[71], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canbaduser]\" value = \"yes\"" . ($EditUG["canbaduser"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canbaduser]\" value = \"no\"" . ($EditUG["canbaduser"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[73] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[74], $Language[75], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canviewotherprofile]\" value = \"yes\"" . ($EditUG["canviewotherprofile"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canviewotherprofile]\" value = \"no\"" . ($EditUG["canviewotherprofile"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[76], $Language[77], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canmemberlist]\" value = \"yes\"" . ($EditUG["canmemberlist"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canmemberlist]\" value = \"no\"" . ($EditUG["canmemberlist"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[78], $Language[79], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canfriendlist]\" value = \"yes\"" . ($EditUG["canfriendlist"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canfriendlist]\" value = \"no\"" . ($EditUG["canfriendlist"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[80], $Language[81], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cansnatch]\" value = \"yes\"" . ($EditUG["cansnatch"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cansnatch]\" value = \"no\"" . ($EditUG["cansnatch"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[82], $Language[83], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canpeers]\" value = \"yes\"" . ($EditUG["canpeers"] == "yes" ? " checked = \"checked\"" : "") . "/> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canpeers]\" value = \"no\"" . ($EditUG["canpeers"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[84], $Language[85], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cantopten]\" value = \"yes\"" . ($EditUG["cantopten"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cantopten]\" value = \"no\"" . ($EditUG["cantopten"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[88] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[89], $Language[90], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canemailnotify]\" value = \"yes\"" . ($EditUG["canemailnotify"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canemailnotify]\" value = \"no\"" . ($EditUG["canemailnotify"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[91], $Language[92], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cansignature]\" value = \"yes\"" . ($EditUG["cansignature"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cansignature]\" value = \"no\"" . ($EditUG["cansignature"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[93], $Language[94], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canseedbox]\" value = \"yes\"" . ($EditUG["canseedbox"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canseedbox]\" value = \"no\"" . ($EditUG["canseedbox"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[95] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[96], $Language[97], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cansettingspanel]\" value = \"yes\"" . ($EditUG["cansettingspanel"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[cansettingspanel]\" value = \"no\"" . ($EditUG["cansettingspanel"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[98], $Language[99], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canstaffpanel]\" value = \"yes\"" . ($EditUG["canstaffpanel"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canstaffpanel]\" value = \"no\"" . ($EditUG["canstaffpanel"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[100], $Language[101], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[issupermod]\" value = \"yes\"" . ($EditUG["issupermod"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[issupermod]\" value = \"no\"" . ($EditUG["issupermod"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[102], $Language[103], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[isforummod]\" value = \"yes\"" . ($EditUG["isforummod"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[isforummod]\" value = \"no\"" . ($EditUG["isforummod"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[104], $Language[105], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canmassdelete]\" value = \"yes\"" . ($EditUG["canmassdelete"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canmassdelete]\" value = \"no\"" . ($EditUG["canmassdelete"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[106], $Language[107], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canaccessoffline]\" value = \"yes\"" . ($EditUG["canaccessoffline"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[canaccessoffline]\" value = \"no\"" . ($EditUG["canaccessoffline"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[108], $Language[109], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[candeletetorrent]\" value = \"yes\"" . ($EditUG["candeletetorrent"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[candeletetorrent]\" value = \"no\"" . ($EditUG["candeletetorrent"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[110] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[111], $Language[112], "\r\n\t\t\t\t\t<input type = \"text\" name = \"UGCONFIG[pmquote]\" value = \"" . $EditUG["pmquote"] . "\" size = \"10\" />") . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[113], $Language[114], "\r\n\t\t\t\t\t<input type = \"text\" name = \"UGCONFIG[floodlimit]\" value = \"" . $EditUG["floodlimit"] . "\" size = \"10\" />") . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[115], $Language[116], "\r\n\t\t\t\t\t<input type = \"text\" name = \"UGCONFIG[slotlimit]\" value = \"" . $EditUG["slotlimit"] . "\" size = \"10\" />") . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[117], $Language[118], "\r\n\t\t\t\t\t<input type = \"text\" name = \"UGCONFIG[waitlimit]\" value = \"" . $EditUG["waitlimit"] . "\" size = \"10\" />") . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[119], $Language[120], "\r\n\t\t\t\t\t<input type = \"text\" name = \"UGCONFIG[autoinvite]\" value = \"" . $EditUG["autoinvite"] . "\" size = \"10\" />") . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[121] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[122], $Language[123], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canview]\" value = \"yes\"" . (getUserGroup("canview") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canview]\" value = \"no\"" . (!getUserGroup("canview") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[124], $Language[125], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[cancreate]\" value = \"yes\"" . (getUserGroup("cancreate") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[cancreate]\" value = \"no\"" . (!getUserGroup("cancreate") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[126], $Language[127], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canpost]\" value = \"yes\"" . (getUserGroup("canpost") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canpost]\" value = \"no\"" . (!getUserGroup("canpost") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[128], $Language[129], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[candelete]\" value = \"yes\"" . (getUserGroup("candelete") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[candelete]\" value = \"no\"" . (!getUserGroup("candelete") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[130], $Language[131], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canjoin]\" value = \"yes\"" . (getUserGroup("canjoin") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canjoin]\" value = \"no\"" . (!getUserGroup("canjoin") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[132], $Language[133], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canedit]\" value = \"yes\"" . (getUserGroup("canedit") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canedit]\" value = \"no\"" . (!getUserGroup("canedit") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[134], $Language[135], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canmanagemsg]\" value = \"yes\"" . (getUserGroup("canmanagemsg") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canmanagemsg]\" value = \"no\"" . (!getUserGroup("canmanagemsg") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[136], $Language[137], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canmanagegroup]\" value = \"yes\"" . (getUserGroup("canmanagegroup") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"SGPERMS[canmanagegroup]\" value = \"no\"" . (!getUserGroup("canmanagegroup") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[138] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[139], $Language[140], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[canview]\" value = \"yes\"" . (function_203("canview") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[canview]\" value = \"no\"" . (!function_203("canview") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[141], $Language[142], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[cancreate]\" value = \"yes\"" . (function_203("cancreate") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[cancreate]\" value = \"no\"" . (!function_203("cancreate") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[143], $Language[144], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[caneditb]\" value = \"yes\"" . (function_203("caneditb") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[caneditb]\" value = \"no\"" . (!function_203("caneditb") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[145], $Language[146], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[candeleteb]\" value = \"yes\"" . (function_203("candeleteb") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[candeleteb]\" value = \"no\"" . (!function_203("candeleteb") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[147], $Language[148], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[canpost]\" value = \"yes\"" . (function_203("canpost") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[canpost]\" value = \"no\"" . (!function_203("canpost") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[149], $Language[150], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[caneditc]\" value = \"yes\"" . (function_203("caneditc") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[caneditc]\" value = \"no\"" . (!function_203("caneditc") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[151], $Language[152], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[candeletec]\" value = \"yes\"" . (function_203("candeletec") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[candeletec]\" value = \"no\"" . (!function_203("candeletec") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[153], $Language[154], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[candisablec]\" value = \"yes\"" . (function_203("candisablec") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"BLOGPERMS[candisablec]\" value = \"no\"" . (!function_203("candisablec") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[155], $Language[156], "\r\n\t\t\t\t\t<input type = \"text\" name = \"BLOGPERMS[maxblogs]\" value = \"" . @substr($usergroupcache["blogperms"], -1, 1) . "\" size = \"10\" />") . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[167] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[168], $Language[169], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[changeusername]\" value = \"yes\"" . (checkEditUserPermission("changeusername") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[changeusername]\" value = \"no\"" . (!checkEditUserPermission("changeusername") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[170], $Language[171], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[changepassword]\" value = \"yes\"" . (checkEditUserPermission("changepassword") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[changepassword]\" value = \"no\"" . (!checkEditUserPermission("changepassword") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[172], $Language[173], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[changeemail]\" value = \"yes\"" . (checkEditUserPermission("changeemail") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[changeemail]\" value = \"no\"" . (!checkEditUserPermission("changeemail") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[174], $Language[175], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[changeusergroup]\" value = \"yes\"" . (checkEditUserPermission("changeusergroup") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[changeusergroup]\" value = \"no\"" . (!checkEditUserPermission("changeusergroup") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[176], $Language[177], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[managedonationstatus]\" value = \"yes\"" . (checkEditUserPermission("managedonationstatus") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[managedonationstatus]\" value = \"no\"" . (!checkEditUserPermission("managedonationstatus") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[178], $Language[179], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[managetimeoptions]\" value = \"yes\"" . (checkEditUserPermission("managetimeoptions") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[managetimeoptions]\" value = \"no\"" . (!checkEditUserPermission("managetimeoptions") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[180], $Language[181], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[manageaccountdetails]\" value = \"yes\"" . (checkEditUserPermission("manageaccountdetails") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[manageaccountdetails]\" value = \"no\"" . (!checkEditUserPermission("manageaccountdetails") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[182], $Language[183], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[manageaccountpermissions]\" value = \"yes\"" . (checkEditUserPermission("manageaccountpermissions") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[manageaccountpermissions]\" value = \"no\"" . (!checkEditUserPermission("manageaccountpermissions") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[184], $Language[185], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[manageaccountwarningdetails]\" value = \"yes\"" . (checkEditUserPermission("manageaccountwarningdetails") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[manageaccountwarningdetails]\" value = \"no\"" . (!checkEditUserPermission("manageaccountwarningdetails") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[186], $Language[187], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[manageaccounthistory]\" value = \"yes\"" . (checkEditUserPermission("manageaccounthistory") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[manageaccounthistory]\" value = \"no\"" . (!checkEditUserPermission("manageaccounthistory") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[188], $Language[189], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[managesupportoptions]\" value = \"yes\"" . (checkEditUserPermission("managesupportoptions") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[managesupportoptions]\" value = \"no\"" . (!checkEditUserPermission("managesupportoptions") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[190], $Language[191], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[managecontactdetails]\" value = \"yes\"" . (checkEditUserPermission("managecontactdetails") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"EDITUSERPERMS[managecontactdetails]\" value = \"no\"" . (!checkEditUserPermission("managecontactdetails") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"3\">\r\n\t\t\t\t\t\t\t" . $Language[192] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[193], $Language[194], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"CUSTOMIZEPROFILEPERMS[cancustomizeprofile]\" value = \"yes\"" . (function_204("cancustomizeprofile") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"CUSTOMIZEPROFILEPERMS[cancustomizeprofile]\" value = \"no\"" . (!function_204("cancustomizeprofile") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[195], $Language[196], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"CUSTOMIZEPROFILEPERMS[caneditfontfamily]\" value = \"yes\"" . (function_204("caneditfontfamily") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"CUSTOMIZEPROFILEPERMS[caneditfontfamily]\" value = \"no\"" . (!function_204("caneditfontfamily") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[197], $Language[198], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"CUSTOMIZEPROFILEPERMS[caneditfontsize]\" value = \"yes\"" . (function_204("caneditfontsize") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"CUSTOMIZEPROFILEPERMS[caneditfontsize]\" value = \"no\"" . (!function_204("caneditfontsize") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[199], $Language[200], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"CUSTOMIZEPROFILEPERMS[caneditcolors]\" value = \"yes\"" . (function_204("caneditcolors") ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"CUSTOMIZEPROFILEPERMS[caneditcolors]\" value = \"no\"" . (!function_204("caneditcolors") ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan = \"2\">\r\n\t\t\t\t\t\t\t" . $Language[164] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . formatFormSectionRow($Language[165], $Language[166], "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[showads]\" value = \"yes\"" . ($EditUG["showads"] == "yes" ? " checked = \"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type = \"radio\" name = \"UGCONFIG[showads]\" value = \"no\"" . ($EditUG["showads"] == "no" ? " checked = \"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t\t<input type = \"submit\" value = \"" . $Language[21] . "\" /> <input type = \"reset\" value = \"" . $Language[22] . "\" /> <input type = \"button\" value = \"" . $Language[14] . "\" onclick = \"window.location.$href = 'index.php?do=usergroups';\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t\t</form>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        }
    }
}
if (!isset($Output)) {
    $Uarray = [];
    try {
        $result = $TSDatabase->query("SELECT usergroup FROM users");
        if ($result) {
            while ($U = $result->fetch(PDO::FETCH_ASSOC)) {
                if (isset($Uarray[$U["usergroup"]])) {
                    $Uarray[$U["usergroup"]]++;
                } else {
                    $Uarray[$U["usergroup"]] = 1;
                }
            }
        }
    } catch (Exception $e) {
        error_log('Load usergroup stats error: ' . $e->getMessage());
    }
    $Output = "\r\n\t\t<form method = \"post\" action = \"index.php?do=usergroups&act = save_disporder\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" align = \"center\"><b>" . escape_html($Language[5]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[6]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[7]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[8]) . "</b></td>\r\n\t\t\t<td class=\"alt2\" align = \"center\"><b>" . escape_html($Language[160]) . "</b></td>\r\n\t\t\t<td class=\"alt2\" align = \"center\"><b>" . escape_html($Language[9]) . "</b></td>\r\n\t\t</tr>";
    try {
        $result = $TSDatabase->query("SELECT gid, disporder, type, title, description, namestyle FROM usergroups WHERE type = 1 ORDER BY gid");
        if ($result) {
            while ($ug = $result->fetch(PDO::FETCH_ASSOC)) {
                $Output .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" align = \"center\">" . escape_html((string)$ug["gid"]) . "</td>\r\n\t\t\t<td class=\"alt1\">" . escape_html(str_replace("{username}", $ug["title"], $ug["namestyle"])) . "</td>\r\n\t\t\t<td class=\"alt1\">" . escape_html($ug["description"]) . "</td>\r\n\t\t\t<td class=\"alt1\">" . escape_html(isset($Uarray[$ug["gid"]]) ? number_format($Uarray[$ug["gid"]]) : "0") . "</td>\r\n\t\t\t<td class=\"alt1\" align = \"center\"><input type = \"text\" name = \"disporder[" . escape_attr((string)$ug["gid"]) . "]\" value = \"" . escape_attr((string)$ug["disporder"]) . "\" size = \"5\" /></td>\r\n\t\t\t<td class=\"alt1\" align = \"center\"><a href = \"index.php?do=usergroups&amp;act = edit&amp;gid = " . escape_attr((string)$ug["gid"]) . "\"><img src = \"images/tool_edit.png\" alt = \"" . escape_attr($Language[3]) . "\" title = \"" . escape_attr($Language[3]) . "\" border = \"0\" /></a> <a href = \"index.php?do=usergroups&amp;act = new&amp;clonegid = " . escape_attr((string)$ug["gid"]) . "\"><img src = \"images/add.png\" alt = \"" . escape_attr($Language[201]) . "\" title = \"" . escape_attr($Language[201]) . "\" border = \"0\" /></a> " . ($ug["type"] == 1 ? "" : " <a href = \"index.php?do=usergroups&amp;act = delete&amp;gid = " . escape_attr((string)$ug["gid"]) . "\" onclick = \"" . ($ug["type"] == 1 ? "alert('" . escape_js(trim($Language[11])) . "'); return false;" : "return confirm('" . escape_js(str_replace("{1}", $ug["title"], trim($Language[10]))) . "');") . "\"><img src = \"images/tool_delete.png\" alt = \"" . escape_attr($Language[4]) . "\" title = \"" . escape_attr($Language[4]) . "\" border = \"0\" /></a>") . "</td>\r\n\t\t</tr>\r\n\t\t";
            }
        }
    } catch (Exception $e) {
        error_log('Load system usergroups error: ' . $e->getMessage());
    }
    $Output .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan = \"6\">\r\n\t\t\t\t" . escape_html($Language[159]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" align = \"center\"><b>" . escape_html($Language[5]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[6]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[7]) . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[8]) . "</b></td>\r\n\t\t\t<td class=\"alt2\" align = \"center\"><b>" . escape_html($Language[160]) . "</b></td>\r\n\t\t\t<td class=\"alt2\" align = \"center\"><b>" . escape_html($Language[9]) . "</b></td>\r\n\t\t</tr>";
    try {
        $result = $TSDatabase->query("SELECT gid, disporder, type, title, description, namestyle FROM usergroups WHERE type = 2 ORDER BY gid");
        if ($result) {
            while ($ug = $result->fetch(PDO::FETCH_ASSOC)) {
                $Output .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" align = \"center\">" . escape_html((string)$ug["gid"]) . "</td>\r\n\t\t\t<td class=\"alt1\">" . escape_html(str_replace("{username}", $ug["title"], $ug["namestyle"])) . "</td>\r\n\t\t\t<td class=\"alt1\">" . escape_html($ug["description"]) . "</td>\r\n\t\t\t<td class=\"alt1\">" . escape_html(isset($Uarray[$ug["gid"]]) ? number_format($Uarray[$ug["gid"]]) : "0") . "</td>\r\n\t\t\t<td class=\"alt1\" align = \"center\"><input type = \"text\" name = \"disporder[" . escape_attr((string)$ug["gid"]) . "]\" value = \"" . escape_attr((string)$ug["disporder"]) . "\" size = \"5\" /></td>\r\n\t\t\t<td class=\"alt1\" align = \"center\"><a href = \"index.php?do=usergroups&amp;act = edit&amp;gid = " . escape_attr((string)$ug["gid"]) . "\"><img src = \"images/tool_edit.png\" alt = \"" . escape_attr($Language[3]) . "\" title = \"" . escape_attr($Language[3]) . "\" border = \"0\" /></a> <a href = \"index.php?do=usergroups&amp;act = new&amp;clonegid = " . escape_attr((string)$ug["gid"]) . "\"><img src = \"images/add.png\" alt = \"" . escape_attr($Language[201]) . "\" title = \"" . escape_attr($Language[201]) . "\" border = \"0\" /></a> " . ($ug["type"] == 1 ? "" : " <a href = \"index.php?do=usergroups&amp;act = delete&amp;gid = " . escape_attr((string)$ug["gid"]) . "\" onclick = \"" . ($ug["type"] == 1 ? "alert('" . escape_js(trim($Language[11])) . "'); return false;" : "return confirm('" . escape_js(str_replace("{1}", $ug["title"], trim($Language[10]))) . "');") . "\"><img src = \"images/tool_delete.png\" alt = \"" . escape_attr($Language[4]) . "\" title = \"" . escape_attr($Language[4]) . "\" border = \"0\" /></a>") . "</td>\r\n\t\t</tr>\r\n\t\t";
            }
        }
    } catch (Exception $e) {
        error_log('Load custom usergroups error: ' . $e->getMessage());
    }
    $Output .= "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" colspan = \"4\">&nbsp;</td>\r\n\t\t<td class=\"tcat2\" align = \"center\">\r\n\t\t\t<input type = \"submit\" value = \"" . escape_attr($Language[161]) . "\" /> <input type = \"reset\" value = \"" . escape_attr($Language[162]) . "\" />\r\n\t\t</td>\r\n\t\t<td class=\"tcat2\">&nbsp;</td>\r\n\t</tr>\r\n\t</form>";
}
echo "\r\n" . showAlertMessage(empty($Act) ? "<a href = \"index.php?do=usergroups&amp;act = new\">" . escape_html($Language[16]) . "</a>" : "<a href = \"index.php?do=usergroups\">" . escape_html($Language[14]) . "</a>") . "\r\n" . $Message . "\r\n<table cellpadding = \"0\" cellspacing = \"0\" border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align = \"center\" colspan = \"" . (isset($Act) && $Act == "" ? 6 : 2) . "\">\r\n\t\t\t" . escape_html($Language[2]) . (isset($Act) && $Act == "new" ? " - " . escape_html($Language[16]) : (isset($Act) && $Act == "edit" ? " - " . escape_html($Language[3]) . " (" . escape_html($EditUG["title"]) . ")" : "")) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Output . "\r\n</table>";
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $editorContent = ob_get_contents();
    ob_end_clean();
    return $editorContent;
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function formatFormSectionRow($first, $second, $third)
{
    $groupId = "";
    if ($GLOBALS["yesnocount"] % 2 == 1) {
        $groupId = " altSaperator";
    }
    $GLOBALS["yesnocount"] += 1;
    return "\r\n\t<tr>\r\n\t\t<td class=\"alt2" . escape_attr($groupId) . "\" colspan = \"2\"><b>" . escape_html($first) . "</b></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1" . escape_attr($groupId) . "\" width = \"50%\" align = \"justify\">\r\n\t\t\t" . escape_html($second) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1" . escape_attr($groupId) . "\" width = \"50%\" valign = \"top\">\r\n\t\t\t" . $third . "\r\n\t\t</td>\r\n\t</tr>\r\n\t";
}
function function_205($Option)
{
    global $usergroupcache;
    $configValue = ["canview" => "0", "cancreate" => "1", "canpost" => "2", "candelete" => "3", "canjoin" => "4", "canedit" => "5", "canmanagemsg" => "6", "canmanagegroup" => "7"];
    $settingHtml = isset($configValue[$Option]) ? $configValue[$Option] : 0;
    return $usergroupcache["sgperms"][$settingHtml] == "1" ? true : false;
}
function function_203($Option)
{
    global $usergroupcache;
    $configValue = ["canview" => "0", "cancreate" => "1", "caneditb" => "2", "candeleteb" => "3", "canpost" => "4", "caneditc" => "5", "candeletec" => "6", "candisablec" => "7"];
    $settingHtml = isset($configValue[$Option]) ? $configValue[$Option] : 0;
    return $usergroupcache["blogperms"][$settingHtml] == "1" ? true : false;
}
function checkEditUserPermission($Option)
{
    global $usergroupcache;
    $configValue = ["changeusername" => "0", "changepassword" => "1", "changeemail" => "2", "changeusergroup" => "3", "managedonationstatus" => "4", "managetimeoptions" => "5", "manageaccountdetails" => "6", "manageaccountpermissions" => "7", "manageaccountwarningdetails" => "8", "manageaccounthistory" => "9", "managesupportoptions" => "10", "managecontactdetails" => "11"];
    $settingHtml = isset($configValue[$Option]) ? $configValue[$Option] : 0;
    return isset($usergroupcache["edituserperms"][$settingHtml]) && $usergroupcache["edituserperms"][$settingHtml] == "1" ? true : false;
}
function function_204($Option)
{
    global $usergroupcache;
    $configValue = ["cancustomizeprofile" => "0", "caneditfontfamily" => "1", "caneditfontsize" => "2", "caneditcolors" => "3"];
    $settingHtml = isset($configValue[$Option]) ? $configValue[$Option] : 0;
    return $usergroupcache["customizeprofile"][$settingHtml] == "1" ? true : false;
}

?>