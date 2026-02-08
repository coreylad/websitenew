<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : "showlist");
$Language = file("languages/" . getStaffLanguage() . "/manage_polls.lang");
$Message = "";
define("TSF_PREFIX", "tsf_");
if ($action == "updatepoll" && twitterValidate($_POST["pollid"])) {
    $_queries = [];
    $pollid = intval($_POST["pollid"]);
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'");
    $pollinfo = mysqli_fetch_assoc($query);
    if (!$pollinfo["pollid"]) {
        $Message = showAlertError($Language[17]);
    }
    if (strlen(trim($_POST["pollquestion"])) < 2) {
        $Message = showAlertError($Language[33]);
    } else {
        $_queries[] = "question = " . function_257(htmlspecialchars($_POST["pollquestion"]));
    }
    if (!$Message) {
        $numberoptions = 0;
        $optionarray = [];
        foreach ($_POST["options"] as $left => $right) {
            if ($right != "") {
                $numberoptions++;
                $optionarray[] = htmlspecialchars($right);
            }
        }
        if ($numberoptions < 2) {
            $Message = showAlertError($Language[33]);
        } else {
            $_queries[] = "options = " . function_257(implode("~~~", $optionarray));
        }
        if (!$Message) {
            $votecount = 0;
            $votearray = [];
            foreach ($_POST["pollvotes"] as $left => $right) {
                $votecount++;
                $votearray[] = 0 + $right;
            }
            $savevotearray = [];
            $voters = 0;
            for ($i = 0; $i < $numberoptions; $i++) {
                if ($votearray[$i]) {
                    $voters += $votearray[$i];
                    $savevotearray[] = $votearray[$i];
                } else {
                    $savevotearray[] = "0";
                }
            }
            $_queries[] = "votes = " . function_257(implode("~~~", $savevotearray));
            $_queries[] = "active = '" . (isset($_POST["closepoll"]) && $_POST["closepoll"] == "yes" ? "0" : "1") . "'";
            $_queries[] = "numberoptions = '" . intval($numberoptions) . "'";
            $_queries[] = "timeout = '" . intval($_POST["timeout"]) . "'";
            $_queries[] = "voters = '" . intval($voters) . "'";
            $_queries[] = "public = '" . (isset($_POST["public"]) && $_POST["public"] == "1" ? "1" : "0") . "'";
            $_queries[] = "tid = '" . intval($_POST["tid"]) . "'";
            if (0 < intval($_POST["tid"])) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "threads SET $pollid = '" . $pollid . "' WHERE $tid = '" . intval($_POST["tid"]) . "'");
            }
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "poll SET " . implode(",", $_queries) . " WHERE $pollid = '" . $pollid . "'");
            $action = "showlist";
            $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($_POST["pollquestion"]), $_SESSION["ADMIN_USERNAME"]], $Language[15]);
            logStaffAction($Message);
            $Message = showAlertError($Message);
        }
    }
}
if ($action == "polledit" && twitterValidate($_GET["pollid"])) {
    $pollid = intval($_GET["pollid"]);
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'");
    $pollinfo = mysqli_fetch_assoc($query);
    if (!$pollinfo["pollid"]) {
        $Message = showAlertError($Language[17]);
    }
    if (!$Message) {
        if (10 < $pollinfo["numberoptions"]) {
            $pollinfo["numberoptions"] = 10;
        }
        $pollinfo["closed"] = "";
        if (!$pollinfo["active"]) {
            $pollinfo["closed"] = "checked=\"checked\"";
        }
        if ($pollinfo["public"]) {
            $show["makeprivate"] = true;
            $pollinfo["public"] = "checked=\"checked\"";
        }
        $pollinfo["postdate"] = formatTimestamp($pollinfo["dateline"]);
        $pollinfo["posttime"] = formatTimestamp($pollinfo["dateline"]);
        $splitoptions = explode("~~~", $pollinfo["options"]);
        $splitvotes = explode("~~~", $pollinfo["votes"]);
        $pollinfo["numbervotes"] = "";
        $counter = 0;
        while ($counter++ < $pollinfo["numberoptions"]) {
            $pollinfo["numbervotes"] += $splitvotes[$counter - 1];
        }
        $counter = 0;
        $pollbits = "";
        $pollinfo["question"] = htmlspecialchars($pollinfo["question"]);
        while ($counter++ < $pollinfo["numberoptions"]) {
            $option["question"] = htmlspecialchars($splitoptions[$counter - 1]);
            $option["votes"] = $splitvotes[$counter - 1];
            $option["number"] = $counter;
            $pollbits .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\"><label for=\"opt" . $option["number"] . "\">" . str_replace("{1}", $option["number"], $Language[18]) . ":<br /><input $type = \"text\" class=\"bginput\"  $name = \"options[" . $option["number"] . "]\" $id = \"opt" . $option["number"] . "\" $value = \"" . $option["question"] . "\" $style = \"width: 95%;\" /></label></td>\r\n\t\t\t\t<td class=\"none\"><label for=\"vot" . $option["number"] . "\">" . $Language[19] . ":<br /><input $type = \"text\" class=\"bginput\" $name = \"pollvotes[" . $option["number"] . "]\" $id = \"vot" . $option["number"] . "\" $value = \"" . $option["votes"] . "\" $size = \"5\" /></label></td>\r\n\t\t\t</tr>";
        }
        // DEAD CODE: Always-true condition makes the else branch unreachable
        if (true) {
            $show["additional_option1"] = $pollinfo["numberoptions"] < 10;
            $show["additional_option2"] = $pollinfo["numberoptions"] < 9;
        } else {
            // DEAD CODE: This else block is never executed because the if condition is always true
            $show["additional_option1"] = true;
            $show["additional_option2"] = true;
        }
        $poll = "\r\n\t\t<form $action = \"index.php?do=manage_polls&$action = updatepoll&$pollid = " . $pollid . "&$page = " . intval($_GET["page"]) . "\" $method = \"post\">\r\n\t\t<input $type = \"hidden\" $name = \"action\" $value = \"updatepoll\" />\r\n\t\t<input $type = \"hidden\" $name = \"pollid\" $value = \"" . $pollid . "\" />\r\n\t\t\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t<td class=\"tcat\">\r\n\t\t" . $Language[11] . "\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t<div class=\"panel\">\r\n\t\t<div $align = \"left\">\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[4] . "</legend>\r\n\t\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\" $colspan = \"2\">\r\n\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"pollquestion\" $value = \"" . $pollinfo["question"] . "\" $id = \"pollquestion\" $style = \"width: 99%;\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[20] . "</legend>\r\n\t\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t" . $pollbits . "\r\n\r\n\t\t" . ($show["additional_option1"] ? "\r\n\t\t<tr>\r\n\t\t<td $colspan = \"2\" class=\"none\"><label for=\"add1\">" . $Language[21] . ":<br /><input $type = \"text\" class=\"bginput\" $name = \"options[]\" $id = \"add1\" $style = \"width: 95%;\" /></label></td>\r\n\t\t</tr>\r\n\t\t" : "") . ($show["additional_option2"] ? "<tr>\r\n\t\t<td $colspan = \"2\" class=\"none\"><label for=\"add2\">" . $Language[22] . ":<br /><input $type = \"text\" class=\"bginput\" $name = \"options[]\" $id = \"add2\" $style = \"width: 95%;\" /></label></td>\r\n\t\t</tr>\r\n\t\t" : "") . "\r\n\t\t<tr>\r\n\t\t<td $colspan = \"2\" class=\"none\">" . $Language[23] . "</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[24] . "</legend>\r\n\t\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t<td class=\"none\">" . $Language[25] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t<td class=\"none\"><label for=\"poll_timeout\">" . $Language[26] . " <input $type = \"text\" class=\"bginput\" $name = \"timeout\" $value = \"" . $pollinfo["timeout"] . "\" $size = \"5\" $id = \"poll_timeout\" /> " . str_replace("{1}", $pollinfo["postdate"], $Language[27]) . "</label></td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</fieldset>\r\n\r\n\t\t" . (isset($show["makeprivate"]) ? "\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[20] . "</legend>\r\n\t\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t<td class=\"none\"><label for=\"cb_public\"><input $type = \"checkbox\" $name = \"public\" $value = \"1\" $id = \"cb_public\" $tabindex = \"1\" " . $pollinfo["public"] . " /> " . $Language[28] . "</label></td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</fieldset>\r\n\t\t" : "") . "\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[26] . "</legend>\r\n\t\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t<td class=\"none\"><label for=\"cb_closepoll\"><input $type = \"checkbox\" $name = \"closepoll\" $value = \"yes\" $id = \"cb_closepoll\" " . $pollinfo["closed"] . " /> " . $Language[29] . " " . $Language[30] . "</label></td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[41] . "</legend>\r\n\t\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t<td class=\"none\"><label for=\"cb_threadid\"><input $type = \"text\" $name = \"tid\" $value = \"" . $pollinfo["tid"] . "\" $id = \"cb_threadid\" $size = \"10\" /> " . $Language[42] . "</label></td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</fieldset>\r\n\r\n\t\t</div>\r\n\t\t</div>\r\n\r\n\t\t<div class=\"tcat2\">\r\n\t\t<input $type = \"submit\" class=\"button\" $name = \"sbutton\" $accesskey = \"s\" $value = \"" . $Language[31] . "\" />\r\n\t\t<input $type = \"reset\" class=\"button\" $value = \"" . $Language[32] . "\" />\r\n\t\t</div>\r\n\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\r\n\t\t</form>";
        echo $poll;
    }
}
if ($action == "createpoll") {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        if (strlen(trim($_POST["pollquestion"])) < 2) {
            $Message = showAlertError($Language[33]);
        }
        if (!$Message) {
            $question = htmlspecialchars($_POST["pollquestion"]);
            $numberoptions = 0;
            $optionarray = [];
            foreach ($_POST["options"] as $left => $right) {
                if ($right != "") {
                    $numberoptions++;
                    $optionarray[] = htmlspecialchars($right);
                }
            }
            if ($numberoptions < 2) {
                $Message = showAlertError($Language[33]);
            }
            if (!$Message) {
                $votearray = [];
                for ($i = 0; $i < $numberoptions; $i++) {
                    $votearray[] = "0";
                }
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO " . TSF_PREFIX . "poll VALUES (NULL, " . function_257($question) . ", '" . time() . "', " . function_257(implode("~~~", $optionarray)) . ", " . function_257(implode("~~~", $votearray)) . ", '1', '" . intval($numberoptions) . "', '" . intval($_POST["timeout"]) . "', '" . (isset($_POST["multiple"]) ? intval($_POST["multiple"]) : 0) . "', '0', '" . (isset($_POST["public"]) ? intval($_POST["public"]) : 0) . "', '0', '1', '" . intval($_POST["tid"]) . "')");
                $pollid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                if (0 < intval($_POST["tid"])) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "threads SET $pollid = '" . $pollid . "' WHERE $tid = '" . intval($_POST["tid"]) . "'");
                }
                $action = "showlist";
                $SysMsg = str_replace(["{1}", "{2}"], [$question, $_SESSION["ADMIN_USERNAME"]], $Language[16]);
                logStaffAction($SysMsg);
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'SHOUTBOX'");
                $Result = mysqli_fetch_assoc($query);
                $SHOUTBOX = unserialize($Result["content"]);
                if ($SHOUTBOX["tsshoutbot"] == "yes" && preg_match("#poll#", $SHOUTBOX["tsshoutboxoptions"])) {
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
                    $Result = mysqli_fetch_assoc($query);
                    $MAIN = unserialize($Result["content"]);
                    $shoutbOT = str_replace(["{1}", "{2}"], ["[URL=" . $MAIN["BASEURL"] . "/index.php#showtspoll]" . $question . "[/URL]", "[URL=userdetails.php?$id = " . $_SESSION["ADMIN_ID"] . "]" . $_SESSION["ADMIN_USERNAME"] . "[/URL]"], $Language[34]);
                    $shout_result = mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_shoutbox (date, shout, notice) VALUES ('" . time() . "', " . function_257($shoutbOT) . ", '1')");
                }
            }
        }
    } else {
        $i = 0;
        $options = "";
        while ($i++ < 15) {
            $options .= "<tr><td class=\"none\"><label for=\"opt" . $i . "\">" . str_replace("{1}", $i, $Language[18]) . ":<br><input class=\"bginput\" $name = \"options[" . $i . "]\" $id = \"opt" . $i . "\" $value = \"\" $style = \"width: 99%;\" $type = \"text\"></label></td></tr>";
        }
        echo "\r\n\t\t<form $action = \"index.php?do=manage_polls&$action = createpoll" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\">\r\n\t\t<input $name = \"action\" $value = \"createpoll\" $type = \"hidden\" />\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"tcat\">\r\n\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t<div class=\"panel\">\r\n\t\t<div $align = \"left\">\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\r\n\t\t<legend>" . $Language[4] . "</legend>\r\n\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"none\" $colspan = \"2\">\r\n\t\t<input class=\"bginput\" $name = \"pollquestion\" $value = \"\" $id = \"pollquestion\" $style = \"width: 99%;\" $type = \"text\" />\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[20] . "</legend>\r\n\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\r\n\t\t<tbody>\r\n\t\t" . $options . "\r\n\t\t<tr>\r\n\t\t<td $colspan = \"2\" class=\"none\">" . $Language[23] . "</td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[24] . "</legend>\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"none\">" . $Language[25] . "</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t<td class=\"none\"><label for=\"poll_timeout\">" . $Language[26] . " <input class=\"bginput\" $name = \"timeout\" $value = \"0\" $size = \"5\" $id = \"poll_timeout\" $type = \"text\"> " . str_replace("{1}", formatTimestamp(time()), $Language[27]) . "</label></td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[35] . "</legend>\r\n\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"none\"><label for=\"cb_multiple\"><input $type = \"checkbox\" $name = \"multiple\" $value = \"1\" $id = \"cb_multiple\" $tabindex = \"1\" /> " . $Language[36] . "</label></td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[19] . "</legend>\r\n\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"none\"><label for=\"cb_public\"><input $name = \"public\" $value = \"1\" $id = \"cb_public\" $tabindex = \"1\" $type = \"checkbox\"> " . $Language[28] . "</label></td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[41] . "</legend>\r\n\t\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t<td class=\"none\"><label for=\"cb_threadid\"><input $type = \"text\" $name = \"tid\" $value = \"0\" $id = \"cb_threadid\" $size = \"10\" /> " . $Language[42] . "</label></td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</fieldset>\r\n\r\n\t\t</div>\r\n\t\t</div>\r\n\r\n\t\t<div class=\"tcat2\">\r\n\t\t<input class=\"button\" $name = \"sbutton\" $accesskey = \"s\" $value = \"" . $Language[31] . "\" $type = \"submit\">\r\n\t\t<input class=\"button\" $value = \"" . $Language[32] . "\" $type = \"reset\">\r\n\t\t</div>\r\n\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\r\n\t\t</form>\r\n\t\t";
    }
}
if ($action == "deletepoll" && twitterValidate($_GET["pollid"])) {
    $pollid = intval($_GET["pollid"]);
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT pollid, question FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'");
    $pollinfo = mysqli_fetch_assoc($query);
    if (!$pollinfo["pollid"]) {
        $Message = showAlertError($Language[17]);
    }
    if (!$Message) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'");
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "pollvote WHERE $pollid = '" . $pollid . "'");
        $action = "showlist";
        $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($pollinfo["question"]), $_SESSION["ADMIN_USERNAME"]], $Language[14]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
    }
}
if ($action == "showlist") {
    $results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "poll"));
    list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=manage_polls&amp;");
    $Print = "";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "poll ORDER BY dateline DESC " . $limit);
    $Print .= "\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $width = \"45%\" $align = \"left\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"10%\" $align = \"left\">" . $Language[38] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"15%\" $align = \"center\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"5%\" $align = \"center\">" . $Language[6] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"10%\" $align = \"center\">" . $Language[7] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"15%\" $align = \"center\">" . $Language[8] . "</td>\r\n\t</tr>\r\n\t";
    if (0 < mysqli_num_rows($query)) {
        while ($Poll = mysqli_fetch_assoc($query)) {
            if ($Poll["dateline"] + $Poll["timeout"] * 86400 < time() && $Poll["timeout"] != 0) {
                $Poll["active"] = 0;
            }
            if ($Poll["fortracker"] == "1") {
                $LinkToShow = "<a $href = \"../../index.php?$pollid = " . $Poll["pollid"] . "#showtspoll\">";
            } else {
                $LTSQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT tid FROM " . TSF_PREFIX . "threads WHERE $pollid = " . $Poll["pollid"]);
                if (mysqli_num_rows($LTSQuery)) {
                    $Result = mysqli_fetch_assoc($LTSQuery);
                    $LinkToShow = "<a $href = \"../../tsf_forums/showthread.php?$tid = " . $Result["tid"] . "\">";
                }
            }
            $Print .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"45%\" $align = \"left\" class=\"alt1\">" . $LinkToShow . htmlspecialchars($Poll["question"]) . "</a></td>\r\n\t\t\t\t<td $width = \"10%\" $align = \"left\" class=\"alt1\">" . ($Poll["fortracker"] == 1 ? "<font $color = \"blue\">" . $Language[39] : "<font $color = \"red\">" . $Language[40]) . "</font></td>\r\n\t\t\t\t<td $width = \"15%\" $align = \"center\" class=\"alt1\">" . formatTimestamp($Poll["dateline"]) . "</td>\r\n\t\t\t\t<td $width = \"5%\" $align = \"center\" class=\"alt1\">" . array_sum(explode("~~~", $Poll["votes"])) . "</td>\r\n\t\t\t\t<td $width = \"10%\" $align = \"center\" class=\"alt1\">" . ($Poll["active"] == "0" ? "<font $color = \"red\">" . $Language[10] : "<font $color = \"green\">" . $Language[9]) . "</font></td>\r\n\t\t\t\t<td $width = \"15%\" $align = \"center\" class=\"alt1\"><a $href = \"index.php?do=manage_polls&amp;$action = polledit&amp;$pollid = " . $Poll["pollid"] . "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[11]) . "\" $title = \"" . trim($Language[11]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_polls&amp;$action = deletepoll&amp;$pollid = " . $Poll["pollid"] . "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "\" $onclick = \"return confirm('" . trim($Language[12]) . ": " . trim($Poll["question"]) . "\\n\\n" . trim($Language[13]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[12]) . "\" $title = \"" . trim($Language[12]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a></td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        $Print .= "<tr><td $colspan = \"6\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_polls&amp;$action = createpoll", $Language[37]) . "</td></tr>";
    }
}
echo showAlertMessage("<a $href = \"index.php?do=manage_polls&amp;$action = createpoll&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "\">" . $Language[3] . "</a>");
echo $Message;
echo isset($pagertop) ? $pagertop : "";
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"6\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\r\n\t";
echo isset($Print) ? $Print : "";
echo "</table>\r\n";
echo isset($pagertop) ? $pagertop : "";
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
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_257($data)
{
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $data) . "'";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_258($id)
{
    return is_numeric($id) && 0 < $id && floor($id) == $id;
}
function validatePerPage($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $totalPages = ceil($numresults / $perpage);
    if ($totalPages == 0) {
        $totalPages = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($totalPages < $page) {
            $page = $totalPages;
        }
    }
}
function calculatePagination($pagenumber, $perpage, $total)
{
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    $paginationFirstItem++;
    return ["first" => number_format($paginationFirstItem), "last" => number_format($paginationLastItem)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $queryResult = @ceil($results / $perpage);
    } else {
        $queryResult = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $limitOffset = ($pagenumber - 1) * $perpage;
    $paginationOffset = $pagenumber * $perpage;
    if ($results < $paginationOffset) {
        $paginationOffset = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    $paginationLinks = $prevPage = $nextPage = $pageLinks = $paginationHtml = "";
    $currentPage = 0;
    if ($results <= $perpage) {
        $show["pagenav"] = false;
        return ["", "LIMIT " . $limitOffset . ", " . $perpage];
    }
    $show["pagenav"] = true;
    $total = number_format($results);
    $show["last"] = false;
    $show["first"] = $show["last"];
    $show["next"] = $show["first"];
    $show["prev"] = $show["next"];
    if (1 < $pagenumber) {
        $previousPage = $pagenumber - 1;
        $previousPageInfo = calculatePagination($previousPage, $perpage, $results);
        $show["prev"] = true;
    }
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPageNumber, $perpage, $results);
        $show["next"] = true;
    }
    $pageRangeThreshold = "3";
    if (!isset($paginationSkipLinksArray) || !is_array($paginationSkipLinksArray)) {
        $paginationOptions = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $paginationOptions, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $previousPageQuery = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($show["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($show["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $previousPageQuery . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($show["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($show["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($pageRangeThreshold <= abs($currentPage - $pagenumber) && $pageRangeThreshold != 0) {
        if ($currentPage == 1) {
            $firstPageInfo = calculatePagination(1, $perpage, $results);
            $show["first"] = true;
        }
        if ($currentPage == $queryResult) {
            $lastPageInfo = calculatePagination($queryResult, $perpage, $results);
            $show["last"] = true;
        }
        if (in_array(abs($currentPage - $pagenumber), $paginationSkipLinksArray) && $currentPage != 1 && $currentPage != $queryResult) {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $pageOffsetDisplay = $currentPage - $pagenumber;
            if (0 < $pageOffsetDisplay) {
                $pageOffsetDisplay = "+" . $pageOffsetDisplay;
            }
            $paginationLinks .= "<li><a class=\"smalltext\" $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        }
    }
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

?>