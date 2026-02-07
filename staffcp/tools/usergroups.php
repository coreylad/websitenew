<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

$GLOBALS["yesnocount"] = 0;
var_235();
$Language = file("languages/" . function_75() . "/usergroups.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if ($Act == "save_disporder") {
    $Act = "";
    foreach ($_POST["disporder"] as $gid => $disporder) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE usergroups SET disporder = " . intval($disporder) . " WHERE gid = " . intval($gid));
    }
    $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[163]);
    function_79($Message);
    $Message = function_76($Message);
}
if ($Act == "delete" && ($gid = intval($_GET["gid"]))) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title, type FROM usergroups WHERE gid = " . $gid);
    if (mysqli_num_rows($Query)) {
        $Ug = mysqli_fetch_assoc($Query);
        if (isset($_POST["newgid"]) && ($newgid = intval($_POST["newgid"]))) {
            if ($Ug["type"] == 1) {
                $Message = function_76($Language[11]);
            } else {
                $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title FROM usergroups WHERE gid = " . $newgid);
                if (mysqli_num_rows($Query)) {
                    $Result = mysqli_fetch_assoc($Query);
                    $Newgroupname = $Result["title"];
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET usergroup = " . $newgid . " WHERE usergroup = " . $gid);
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM usergroups WHERE gid = " . $gid);
                    $Message = str_replace(["{1}", "{2}", "{3}"], [$Ug["title"], $_SESSION["ADMIN_USERNAME"], $Newgroupname], $Language[13]);
                    function_79($Message);
                    $Message = function_76($Message);
                }
            }
        } else {
            $Selectbox = "<select name=\"newgid\">";
            $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title FROM usergroups WHERE gid != " . $gid);
            while ($usergroup = mysqli_fetch_assoc($Query)) {
                $Selectbox .= "<option value=\"" . $usergroup["gid"] . "\">" . $usergroup["title"] . "</option>";
            }
            $Selectbox .= "</select>";
            $Output = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t" . str_replace("{1}", $Ug["title"], $Language[15]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<form method=\"post\" action=\"index.php?do=usergroups&act=delete&gid=" . $gid . "\">\r\n\t\t\t\t\t" . $Language[12] . " " . $Selectbox . " <input type=\"submit\" value=\"" . $Language[4] . "\" /> <input type=\"button\" value=\"" . $Language[14] . "\" onclick=\"window.location.href='index.php?do=usergroups';\" />\r\n\t\t\t\t\t</form>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    }
}
if ($Act == "new" || $Act == "edit" && ($gid = intval($_GET["gid"]))) {
    $clonegid = isset($_GET["clonegid"]) ? intval($_GET["clonegid"]) : "";
    $title = "";
    $description = "";
    $namestyle = "{username}";
    if ($Act == "edit") {
        $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM usergroups WHERE gid = " . $gid);
        if (mysqli_num_rows($Query) == 0) {
            $STOP = true;
        } else {
            $EditUG = mysqli_fetch_assoc($Query);
            $usergroupcache = $EditUG;
            $title = $EditUG["title"];
            $description = $EditUG["description"];
            $namestyle = $EditUG["namestyle"];
        }
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && !isset($STOP)) {
        $title = trim($_POST["title"]);
        $description = trim($_POST["description"]);
        $namestyle = trim($_POST["namestyle"]);
        if ($Act == "new") {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO usergroups (title, description, namestyle, type) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $namestyle) . "', 2)");
            $NewGID = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        } else {
            $NewGID = $gid;
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE usergroups SET title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', namestyle = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $namestyle) . "' WHERE gid = " . $NewGID);
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
            $QueryArray[] = $Field . " = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Value) . "'";
        }
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE usergroups SET " . implode(",", $QueryArray) . " WHERE gid = " . $NewGID);
        $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[$Act == "new" ? "23" : "157"]);
        function_79($Message);
        $Message = function_76($Message);
        unset($Act);
    } else {
        if (!isset($STOP)) {
            if ($Act == "new") {
                if ($clonegid) {
                    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM usergroups WHERE gid = " . $clonegid);
                    if (mysqli_num_rows($Query)) {
                        $EditUG = mysqli_fetch_assoc($Query);
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
            $Output = "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<form method=\"post\" action=\"index.php?do=usergroups&act=" . ($Act == "new" ? "new" : "edit&gid=" . $gid) . "\">\r\n\t\t\t\t<table width=\"100%\" align=\"center\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"mainTableNoBorder\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" colspan=\"2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t\t\t" . $Language[17] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t\t\t<input type=\"text\" name=\"title\" value=\"" . $title . "\" size=\"70\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2 altSaperator\" colspan=\"2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1 altSaperator\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t\t\t" . $Language[18] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1 altSaperator\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t\t\t<input type=\"text\" name=\"description\" value=\"" . $description . "\" size=\"70\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt2\" colspan=\"2\"><b>" . $Language[19] . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" align=\"justify\" valign=\"top\" colspan=\"2\">\r\n\t\t\t\t\t\t\t" . $Language[20] . "<br />\r\n\t\t\t\t\t\t\t<textarea name=\"namestyle\" id=\"namestyle\" style=\"width: 99%; height: 40px;\">" . $namestyle . "</textarea>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[72] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[26], $Language[27], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[isbanned]\" value=\"yes\"" . ($EditUG["isbanned"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[isbanned]\" value=\"no\"" . ($EditUG["isbanned"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[28], $Language[29], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[isvipgroup]\" value=\"yes\"" . ($EditUG["isvipgroup"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[isvipgroup]\" value=\"no\"" . ($EditUG["isvipgroup"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[30], $Language[31], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canfreeleech]\" value=\"yes\"" . ($EditUG["canfreeleech"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canfreeleech]\" value=\"no\"" . ($EditUG["canfreeleech"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[32], $Language[33], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[showstaffteam]\" value=\"yes\"" . ($EditUG["showstaffteam"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[showstaffteam]\" value=\"no\"" . ($EditUG["showstaffteam"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[showstaffteam]\" value=\"staff\"" . ($EditUG["showstaffteam"] == "staff" ? " checked=\"checked\"" : "") . " /> " . $Language[34]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[35], $Language[36], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canpm]\" value=\"yes\"" . ($EditUG["canpm"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canpm]\" value=\"no\"" . ($EditUG["canpm"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[37], $Language[38], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[candownload]\" value=\"yes\"" . ($EditUG["candownload"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[candownload]\" value=\"no\"" . ($EditUG["candownload"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[202], $Language[203], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[candirectdownload]\" value=\"yes\"" . ($EditUG["candirectdownload"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[candirectdownload]\" value=\"no\"" . ($EditUG["candirectdownload"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[204], $Language[205], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canuploadddl]\" value=\"yes\"" . ($EditUG["canuploadddl"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canuploadddl]\" value=\"no\"" . ($EditUG["canuploadddl"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[39], $Language[40], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canupload]\" value=\"yes\"" . ($EditUG["canupload"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canupload]\" value=\"no\"" . ($EditUG["canupload"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canupload]\" value=\"moderate\"" . ($EditUG["canupload"] == "moderate" ? " checked=\"checked\"" : "") . " /> " . $Language[41]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[42], $Language[43], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canexternal]\" value=\"yes\"" . ($EditUG["canexternal"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canexternal]\" value=\"no\"" . ($EditUG["canexternal"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[44], $Language[45], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cantransfer]\" value=\"yes\"" . ($EditUG["cantransfer"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cantransfer]\" value=\"no\"" . ($EditUG["cantransfer"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[46], $Language[47], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canrequest]\" value=\"yes\"" . ($EditUG["canrequest"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canrequest]\" value=\"no\"" . ($EditUG["canrequest"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[48], $Language[49], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cancomment]\" value=\"yes\"" . ($EditUG["cancomment"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cancomment]\" value=\"no\"" . ($EditUG["cancomment"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25] . " \r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cancomment]\" value=\"moderate\"" . ($EditUG["cancomment"] == "moderate" ? " checked=\"checked\"" : "") . " /> " . $Language[41]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[50], $Language[51], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canreport]\" value=\"yes\"" . ($EditUG["canreport"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canreport]\" value=\"no\"" . ($EditUG["canreport"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[52], $Language[53], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canbookmark]\" value=\"yes\"" . ($EditUG["canbookmark"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canbookmark]\" value=\"no\"" . ($EditUG["canbookmark"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[54], $Language[55], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cancreatepoll]\" value=\"yes\"" . ($EditUG["cancreatepoll"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cancreatepoll]\" value=\"no\"" . ($EditUG["cancreatepoll"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[56], $Language[57], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canvote]\" value=\"yes\"" . ($EditUG["canvote"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canvote]\" value=\"no\"" . ($EditUG["canvote"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[58], $Language[59], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canrate]\" value=\"yes\"" . ($EditUG["canrate"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canrate]\" value=\"no\"" . ($EditUG["canrate"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[60], $Language[61], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canthanks]\" value=\"yes\"" . ($EditUG["canthanks"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canthanks]\" value=\"no\"" . ($EditUG["canthanks"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[62], $Language[63], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canshout]\" value=\"yes\"" . ($EditUG["canshout"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canshout]\" value=\"no\"" . ($EditUG["canshout"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[64], $Language[65], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[caninvite]\" value=\"yes\"" . ($EditUG["caninvite"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[caninvite]\" value=\"no\"" . ($EditUG["caninvite"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[66], $Language[67], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canbonus]\" value=\"yes\"" . ($EditUG["canbonus"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canbonus]\" value=\"no\"" . ($EditUG["canbonus"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[68], $Language[69], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canresetpasskey]\" value=\"yes\"" . ($EditUG["canresetpasskey"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canresetpasskey]\" value=\"no\"" . ($EditUG["canresetpasskey"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[70], $Language[71], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canbaduser]\" value=\"yes\"" . ($EditUG["canbaduser"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canbaduser]\" value=\"no\"" . ($EditUG["canbaduser"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[73] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[74], $Language[75], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canviewotherprofile]\" value=\"yes\"" . ($EditUG["canviewotherprofile"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canviewotherprofile]\" value=\"no\"" . ($EditUG["canviewotherprofile"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[76], $Language[77], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canmemberlist]\" value=\"yes\"" . ($EditUG["canmemberlist"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canmemberlist]\" value=\"no\"" . ($EditUG["canmemberlist"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[78], $Language[79], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canfriendlist]\" value=\"yes\"" . ($EditUG["canfriendlist"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canfriendlist]\" value=\"no\"" . ($EditUG["canfriendlist"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[80], $Language[81], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cansnatch]\" value=\"yes\"" . ($EditUG["cansnatch"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cansnatch]\" value=\"no\"" . ($EditUG["cansnatch"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[82], $Language[83], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canpeers]\" value=\"yes\"" . ($EditUG["canpeers"] == "yes" ? " checked=\"checked\"" : "") . "/> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canpeers]\" value=\"no\"" . ($EditUG["canpeers"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[84], $Language[85], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cantopten]\" value=\"yes\"" . ($EditUG["cantopten"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cantopten]\" value=\"no\"" . ($EditUG["cantopten"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[88] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[89], $Language[90], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canemailnotify]\" value=\"yes\"" . ($EditUG["canemailnotify"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canemailnotify]\" value=\"no\"" . ($EditUG["canemailnotify"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[91], $Language[92], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cansignature]\" value=\"yes\"" . ($EditUG["cansignature"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cansignature]\" value=\"no\"" . ($EditUG["cansignature"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[93], $Language[94], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canseedbox]\" value=\"yes\"" . ($EditUG["canseedbox"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canseedbox]\" value=\"no\"" . ($EditUG["canseedbox"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[95] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[96], $Language[97], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cansettingspanel]\" value=\"yes\"" . ($EditUG["cansettingspanel"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[cansettingspanel]\" value=\"no\"" . ($EditUG["cansettingspanel"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[98], $Language[99], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canstaffpanel]\" value=\"yes\"" . ($EditUG["canstaffpanel"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canstaffpanel]\" value=\"no\"" . ($EditUG["canstaffpanel"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[100], $Language[101], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[issupermod]\" value=\"yes\"" . ($EditUG["issupermod"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[issupermod]\" value=\"no\"" . ($EditUG["issupermod"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[102], $Language[103], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[isforummod]\" value=\"yes\"" . ($EditUG["isforummod"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[isforummod]\" value=\"no\"" . ($EditUG["isforummod"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[104], $Language[105], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canmassdelete]\" value=\"yes\"" . ($EditUG["canmassdelete"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canmassdelete]\" value=\"no\"" . ($EditUG["canmassdelete"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[106], $Language[107], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canaccessoffline]\" value=\"yes\"" . ($EditUG["canaccessoffline"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[canaccessoffline]\" value=\"no\"" . ($EditUG["canaccessoffline"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[108], $Language[109], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[candeletetorrent]\" value=\"yes\"" . ($EditUG["candeletetorrent"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[candeletetorrent]\" value=\"no\"" . ($EditUG["candeletetorrent"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[110] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[111], $Language[112], "\r\n\t\t\t\t\t<input type=\"text\" name=\"UGCONFIG[pmquote]\" value=\"" . $EditUG["pmquote"] . "\" size=\"10\" />") . "\r\n\r\n\t\t\t\t\t" . function_202($Language[113], $Language[114], "\r\n\t\t\t\t\t<input type=\"text\" name=\"UGCONFIG[floodlimit]\" value=\"" . $EditUG["floodlimit"] . "\" size=\"10\" />") . "\r\n\r\n\t\t\t\t\t" . function_202($Language[115], $Language[116], "\r\n\t\t\t\t\t<input type=\"text\" name=\"UGCONFIG[slotlimit]\" value=\"" . $EditUG["slotlimit"] . "\" size=\"10\" />") . "\r\n\r\n\t\t\t\t\t" . function_202($Language[117], $Language[118], "\r\n\t\t\t\t\t<input type=\"text\" name=\"UGCONFIG[waitlimit]\" value=\"" . $EditUG["waitlimit"] . "\" size=\"10\" />") . "\r\n\r\n\t\t\t\t\t" . function_202($Language[119], $Language[120], "\r\n\t\t\t\t\t<input type=\"text\" name=\"UGCONFIG[autoinvite]\" value=\"" . $EditUG["autoinvite"] . "\" size=\"10\" />") . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[121] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[122], $Language[123], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canview]\" value=\"yes\"" . (var_507("canview") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canview]\" value=\"no\"" . (!var_507("canview") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[124], $Language[125], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[cancreate]\" value=\"yes\"" . (var_507("cancreate") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[cancreate]\" value=\"no\"" . (!var_507("cancreate") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[126], $Language[127], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canpost]\" value=\"yes\"" . (var_507("canpost") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canpost]\" value=\"no\"" . (!var_507("canpost") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[128], $Language[129], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[candelete]\" value=\"yes\"" . (var_507("candelete") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[candelete]\" value=\"no\"" . (!var_507("candelete") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[130], $Language[131], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canjoin]\" value=\"yes\"" . (var_507("canjoin") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canjoin]\" value=\"no\"" . (!var_507("canjoin") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[132], $Language[133], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canedit]\" value=\"yes\"" . (var_507("canedit") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canedit]\" value=\"no\"" . (!var_507("canedit") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[134], $Language[135], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canmanagemsg]\" value=\"yes\"" . (var_507("canmanagemsg") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canmanagemsg]\" value=\"no\"" . (!var_507("canmanagemsg") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[136], $Language[137], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canmanagegroup]\" value=\"yes\"" . (var_507("canmanagegroup") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"SGPERMS[canmanagegroup]\" value=\"no\"" . (!var_507("canmanagegroup") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[138] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[139], $Language[140], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[canview]\" value=\"yes\"" . (function_203("canview") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[canview]\" value=\"no\"" . (!function_203("canview") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[141], $Language[142], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[cancreate]\" value=\"yes\"" . (function_203("cancreate") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[cancreate]\" value=\"no\"" . (!function_203("cancreate") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[143], $Language[144], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[caneditb]\" value=\"yes\"" . (function_203("caneditb") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[caneditb]\" value=\"no\"" . (!function_203("caneditb") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[145], $Language[146], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[candeleteb]\" value=\"yes\"" . (function_203("candeleteb") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[candeleteb]\" value=\"no\"" . (!function_203("candeleteb") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[147], $Language[148], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[canpost]\" value=\"yes\"" . (function_203("canpost") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[canpost]\" value=\"no\"" . (!function_203("canpost") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[149], $Language[150], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[caneditc]\" value=\"yes\"" . (function_203("caneditc") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[caneditc]\" value=\"no\"" . (!function_203("caneditc") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[151], $Language[152], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[candeletec]\" value=\"yes\"" . (function_203("candeletec") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[candeletec]\" value=\"no\"" . (!function_203("candeletec") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[153], $Language[154], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[candisablec]\" value=\"yes\"" . (function_203("candisablec") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"BLOGPERMS[candisablec]\" value=\"no\"" . (!function_203("candisablec") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[155], $Language[156], "\r\n\t\t\t\t\t<input type=\"text\" name=\"BLOGPERMS[maxblogs]\" value=\"" . @substr($usergroupcache["blogperms"], -1, 1) . "\" size=\"10\" />") . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[167] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[168], $Language[169], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[changeusername]\" value=\"yes\"" . (function_107("changeusername") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[changeusername]\" value=\"no\"" . (!function_107("changeusername") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[170], $Language[171], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[changepassword]\" value=\"yes\"" . (function_107("changepassword") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[changepassword]\" value=\"no\"" . (!function_107("changepassword") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[172], $Language[173], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[changeemail]\" value=\"yes\"" . (function_107("changeemail") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[changeemail]\" value=\"no\"" . (!function_107("changeemail") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[174], $Language[175], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[changeusergroup]\" value=\"yes\"" . (function_107("changeusergroup") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[changeusergroup]\" value=\"no\"" . (!function_107("changeusergroup") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[176], $Language[177], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[managedonationstatus]\" value=\"yes\"" . (function_107("managedonationstatus") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[managedonationstatus]\" value=\"no\"" . (!function_107("managedonationstatus") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[178], $Language[179], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[managetimeoptions]\" value=\"yes\"" . (function_107("managetimeoptions") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[managetimeoptions]\" value=\"no\"" . (!function_107("managetimeoptions") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[180], $Language[181], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[manageaccountdetails]\" value=\"yes\"" . (function_107("manageaccountdetails") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[manageaccountdetails]\" value=\"no\"" . (!function_107("manageaccountdetails") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[182], $Language[183], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[manageaccountpermissions]\" value=\"yes\"" . (function_107("manageaccountpermissions") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[manageaccountpermissions]\" value=\"no\"" . (!function_107("manageaccountpermissions") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[184], $Language[185], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[manageaccountwarningdetails]\" value=\"yes\"" . (function_107("manageaccountwarningdetails") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[manageaccountwarningdetails]\" value=\"no\"" . (!function_107("manageaccountwarningdetails") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[186], $Language[187], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[manageaccounthistory]\" value=\"yes\"" . (function_107("manageaccounthistory") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[manageaccounthistory]\" value=\"no\"" . (!function_107("manageaccounthistory") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[188], $Language[189], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[managesupportoptions]\" value=\"yes\"" . (function_107("managesupportoptions") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[managesupportoptions]\" value=\"no\"" . (!function_107("managesupportoptions") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[190], $Language[191], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[managecontactdetails]\" value=\"yes\"" . (function_107("managecontactdetails") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"EDITUSERPERMS[managecontactdetails]\" value=\"no\"" . (!function_107("managecontactdetails") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"3\">\r\n\t\t\t\t\t\t\t" . $Language[192] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[193], $Language[194], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"CUSTOMIZEPROFILEPERMS[cancustomizeprofile]\" value=\"yes\"" . (function_204("cancustomizeprofile") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"CUSTOMIZEPROFILEPERMS[cancustomizeprofile]\" value=\"no\"" . (!function_204("cancustomizeprofile") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[195], $Language[196], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"CUSTOMIZEPROFILEPERMS[caneditfontfamily]\" value=\"yes\"" . (function_204("caneditfontfamily") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"CUSTOMIZEPROFILEPERMS[caneditfontfamily]\" value=\"no\"" . (!function_204("caneditfontfamily") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[197], $Language[198], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"CUSTOMIZEPROFILEPERMS[caneditfontsize]\" value=\"yes\"" . (function_204("caneditfontsize") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"CUSTOMIZEPROFILEPERMS[caneditfontsize]\" value=\"no\"" . (!function_204("caneditfontsize") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t" . function_202($Language[199], $Language[200], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"CUSTOMIZEPROFILEPERMS[caneditcolors]\" value=\"yes\"" . (function_204("caneditcolors") ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"CUSTOMIZEPROFILEPERMS[caneditcolors]\" value=\"no\"" . (!function_204("caneditcolors") ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" colspan=\"2\">\r\n\t\t\t\t\t\t\t" . $Language[164] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\r\n\t\t\t\t\t" . function_202($Language[165], $Language[166], "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[showads]\" value=\"yes\"" . ($EditUG["showads"] == "yes" ? " checked=\"checked\"" : "") . " /> " . $Language[24] . "\r\n\t\t\t\t\t<input type=\"radio\" name=\"UGCONFIG[showads]\" value=\"no\"" . ($EditUG["showads"] == "no" ? " checked=\"checked\"" : "") . " /> " . $Language[25]) . "\r\n\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t\t<input type=\"submit\" value=\"" . $Language[21] . "\" /> <input type=\"reset\" value=\"" . $Language[22] . "\" /> <input type=\"button\" value=\"" . $Language[14] . "\" onclick=\"window.location.href='index.php?do=usergroups';\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t\t</form>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        }
    }
}
if (!isset($Output)) {
    $Uarray = [];
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT usergroup FROM users");
    while ($U = mysqli_fetch_assoc($Query)) {
        if (isset($Uarray[$U["usergroup"]])) {
            $Uarray[$U["usergroup"]]++;
        } else {
            $Uarray[$U["usergroup"]] = 1;
        }
    }
    $Output = "\r\n\t\t<form method=\"post\" action=\"index.php?do=usergroups&act=save_disporder\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><b>" . $Language[160] . "</b></td>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><b>" . $Language[9] . "</b></td>\r\n\t\t</tr>";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, disporder, type, title, description, namestyle FROM usergroups WHERE type = 1 ORDER BY gid");
    while ($ug = mysqli_fetch_assoc($Query)) {
        $Output .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" align=\"center\">" . $ug["gid"] . "</td>\r\n\t\t\t<td class=\"alt1\">" . str_replace("{username}", $ug["title"], $ug["namestyle"]) . "</td>\r\n\t\t\t<td class=\"alt1\">" . $ug["description"] . "</td>\r\n\t\t\t<td class=\"alt1\">" . (isset($Uarray[$ug["gid"]]) ? number_format($Uarray[$ug["gid"]]) : "0") . "</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\"><input type=\"text\" name=\"disporder[" . $ug["gid"] . "]\" value=\"" . $ug["disporder"] . "\" size=\"5\" /></td>\r\n\t\t\t<td class=\"alt1\" align=\"center\"><a href=\"index.php?do=usergroups&amp;act=edit&amp;gid=" . $ug["gid"] . "\"><img src=\"images/tool_edit.png\" alt=\"" . $Language[3] . "\" title=\"" . $Language[3] . "\" border=\"0\" /></a> <a href=\"index.php?do=usergroups&amp;act=new&amp;clonegid=" . $ug["gid"] . "\"><img src=\"images/add.png\" alt=\"" . $Language[201] . "\" title=\"" . $Language[201] . "\" border=\"0\" /></a> " . ($ug["type"] == 1 ? "" : " <a href=\"index.php?do=usergroups&amp;act=delete&amp;gid=" . $ug["gid"] . "\" onclick=\"" . ($ug["type"] == 1 ? "alert('" . trim($Language[11]) . "'); return false;" : "return confirm('" . str_replace("{1}", $ug["title"], trim($Language[10])) . "');") . "\"><img src=\"images/tool_delete.png\" alt=\"" . $Language[4] . "\" title=\"" . $Language[4] . "\" border=\"0\" /></a>") . "</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $Output .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan=\"6\">\r\n\t\t\t\t" . $Language[159] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><b>" . $Language[160] . "</b></td>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><b>" . $Language[9] . "</b></td>\r\n\t\t</tr>";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, disporder, type, title, description, namestyle FROM usergroups WHERE type = 2 ORDER BY gid");
    while ($ug = mysqli_fetch_assoc($Query)) {
        $Output .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" align=\"center\">" . $ug["gid"] . "</td>\r\n\t\t\t<td class=\"alt1\">" . str_replace("{username}", $ug["title"], $ug["namestyle"]) . "</td>\r\n\t\t\t<td class=\"alt1\">" . $ug["description"] . "</td>\r\n\t\t\t<td class=\"alt1\">" . (isset($Uarray[$ug["gid"]]) ? number_format($Uarray[$ug["gid"]]) : "0") . "</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\"><input type=\"text\" name=\"disporder[" . $ug["gid"] . "]\" value=\"" . $ug["disporder"] . "\" size=\"5\" /></td>\r\n\t\t\t<td class=\"alt1\" align=\"center\"><a href=\"index.php?do=usergroups&amp;act=edit&amp;gid=" . $ug["gid"] . "\"><img src=\"images/tool_edit.png\" alt=\"" . $Language[3] . "\" title=\"" . $Language[3] . "\" border=\"0\" /></a> <a href=\"index.php?do=usergroups&amp;act=new&amp;clonegid=" . $ug["gid"] . "\"><img src=\"images/add.png\" alt=\"" . $Language[201] . "\" title=\"" . $Language[201] . "\" border=\"0\" /></a> " . ($ug["type"] == 1 ? "" : " <a href=\"index.php?do=usergroups&amp;act=delete&amp;gid=" . $ug["gid"] . "\" onclick=\"" . ($ug["type"] == 1 ? "alert('" . trim($Language[11]) . "'); return false;" : "return confirm('" . str_replace("{1}", $ug["title"], trim($Language[10])) . "');") . "\"><img src=\"images/tool_delete.png\" alt=\"" . $Language[4] . "\" title=\"" . $Language[4] . "\" border=\"0\" /></a>") . "</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $Output .= "\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" colspan=\"4\">&nbsp;</td>\r\n\t\t<td class=\"tcat2\" align=\"center\">\r\n\t\t\t<input type=\"submit\" value=\"" . $Language[161] . "\" /> <input type=\"reset\" value=\"" . $Language[162] . "\" />\r\n\t\t</td>\r\n\t\t<td class=\"tcat2\">&nbsp;</td>\r\n\t</tr>\r\n\t</form>";
}
echo "\r\n" . function_81(empty($Act) ? "<a href=\"index.php?do=usergroups&amp;act=new\">" . $Language[16] . "</a>" : "<a href=\"index.php?do=usergroups\">" . $Language[14] . "</a>") . "\r\n" . $Message . "\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"" . (isset($Act) && $Act == "" ? 6 : 2) . "\">\r\n\t\t\t" . $Language[2] . (isset($Act) && $Act == "new" ? " - " . $Language[16] : (isset($Act) && $Act == "edit" ? " - " . $Language[3] . " (" . $EditUG["title"] . ")" : "")) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Output . "\r\n</table>";
function function_90($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    var_81 = ob_get_contents();
    ob_end_clean();
    return var_81;
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
function function_78($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; url=" . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script type=\"text/javascript\">\r\n\t\t\t\t\twindow.location.href=\"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script type=\"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.href='" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-equiv=\"refresh\" content=\"5;url=" . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
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
function function_202($first, $second, $third)
{
    var_508 = "";
    if ($GLOBALS["yesnocount"] % 2 == 1) {
        var_508 = " altSaperator";
    }
    $GLOBALS["yesnocount"] += 1;
    return "\r\n\t<tr>\r\n\t\t<td class=\"alt2" . var_508 . "\" colspan=\"2\"><b>" . $first . "</b></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1" . var_508 . "\" width=\"50%\" align=\"justify\">\r\n\t\t\t" . $second . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1" . var_508 . "\" width=\"50%\" valign=\"top\">\r\n\t\t\t" . $third . "\r\n\t\t</td>\r\n\t</tr>\r\n\t";
}
function function_205($Option)
{
    global $usergroupcache;
    var_304 = ["canview" => "0", "cancreate" => "1", "canpost" => "2", "candelete" => "3", "canjoin" => "4", "canedit" => "5", "canmanagemsg" => "6", "canmanagegroup" => "7"];
    var_319 = isset(var_304[$Option]) ? var_304[$Option] : 0;
    return $usergroupcache["sgperms"][var_319] == "1" ? true : false;
}
function function_203($Option)
{
    global $usergroupcache;
    var_304 = ["canview" => "0", "cancreate" => "1", "caneditb" => "2", "candeleteb" => "3", "canpost" => "4", "caneditc" => "5", "candeletec" => "6", "candisablec" => "7"];
    var_319 = isset(var_304[$Option]) ? var_304[$Option] : 0;
    return $usergroupcache["blogperms"][var_319] == "1" ? true : false;
}
function function_107($Option)
{
    global $usergroupcache;
    var_304 = ["changeusername" => "0", "changepassword" => "1", "changeemail" => "2", "changeusergroup" => "3", "managedonationstatus" => "4", "managetimeoptions" => "5", "manageaccountdetails" => "6", "manageaccountpermissions" => "7", "manageaccountwarningdetails" => "8", "manageaccounthistory" => "9", "managesupportoptions" => "10", "managecontactdetails" => "11"];
    var_319 = isset(var_304[$Option]) ? var_304[$Option] : 0;
    return isset($usergroupcache["edituserperms"][var_319]) && $usergroupcache["edituserperms"][var_319] == "1" ? true : false;
}
function function_204($Option)
{
    global $usergroupcache;
    var_304 = ["cancustomizeprofile" => "0", "caneditfontfamily" => "1", "caneditfontsize" => "2", "caneditcolors" => "3"];
    var_319 = isset(var_304[$Option]) ? var_304[$Option] : 0;
    return $usergroupcache["customizeprofile"][var_319] == "1" ? true : false;
}

?>