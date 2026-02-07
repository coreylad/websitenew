<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : "showlist");
$Language = file("languages/" . function_75() . "/manage_polls.lang");
$Message = "";
define("TSF_PREFIX", "tsf_");
if ($action == "updatepoll" && var_560($_POST["pollid"])) {
    $_queries = [];
    $pollid = intval($_POST["pollid"]);
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'");
    $pollinfo = mysqli_fetch_assoc($Query);
    if (!$pollinfo["pollid"]) {
        $Message = function_76($Language[17]);
    }
    if (strlen(trim($_POST["pollquestion"])) < 2) {
        $Message = function_76($Language[33]);
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
            $Message = function_76($Language[33]);
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
            function_79($Message);
            $Message = function_76($Message);
        }
    }
}
if ($action == "polledit" && var_560($_GET["pollid"])) {
    $pollid = intval($_GET["pollid"]);
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'");
    $pollinfo = mysqli_fetch_assoc($Query);
    if (!$pollinfo["pollid"]) {
        $Message = function_76($Language[17]);
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
        $pollinfo["postdate"] = function_84($pollinfo["dateline"]);
        $pollinfo["posttime"] = function_84($pollinfo["dateline"]);
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
        if (true) {
            $show["additional_option1"] = $pollinfo["numberoptions"] < 10;
            $show["additional_option2"] = $pollinfo["numberoptions"] < 9;
        } else {
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
            $Message = function_76($Language[33]);
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
                $Message = function_76($Language[33]);
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
                function_79($SysMsg);
                $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'SHOUTBOX'");
                $Result = mysqli_fetch_assoc($Q);
                $SHOUTBOX = unserialize($Result["content"]);
                if ($SHOUTBOX["tsshoutbot"] == "yes" && preg_match("#poll#", $SHOUTBOX["tsshoutboxoptions"])) {
                    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
                    $Result = mysqli_fetch_assoc($Q);
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
        echo "\r\n\t\t<form $action = \"index.php?do=manage_polls&$action = createpoll" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\">\r\n\t\t<input $name = \"action\" $value = \"createpoll\" $type = \"hidden\" />\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"tcat\">\r\n\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t<div class=\"panel\">\r\n\t\t<div $align = \"left\">\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\r\n\t\t<legend>" . $Language[4] . "</legend>\r\n\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"none\" $colspan = \"2\">\r\n\t\t<input class=\"bginput\" $name = \"pollquestion\" $value = \"\" $id = \"pollquestion\" $style = \"width: 99%;\" $type = \"text\" />\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[20] . "</legend>\r\n\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\r\n\t\t<tbody>\r\n\t\t" . $options . "\r\n\t\t<tr>\r\n\t\t<td $colspan = \"2\" class=\"none\">" . $Language[23] . "</td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[24] . "</legend>\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"none\">" . $Language[25] . "</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t<td class=\"none\"><label for=\"poll_timeout\">" . $Language[26] . " <input class=\"bginput\" $name = \"timeout\" $value = \"0\" $size = \"5\" $id = \"poll_timeout\" $type = \"text\"> " . str_replace("{1}", function_84(time()), $Language[27]) . "</label></td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[35] . "</legend>\r\n\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"none\"><label for=\"cb_multiple\"><input $type = \"checkbox\" $name = \"multiple\" $value = \"1\" $id = \"cb_multiple\" $tabindex = \"1\" /> " . $Language[36] . "</label></td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[19] . "</legend>\r\n\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tbody><tr>\r\n\t\t<td class=\"none\"><label for=\"cb_public\"><input $name = \"public\" $value = \"1\" $id = \"cb_public\" $tabindex = \"1\" $type = \"checkbox\"> " . $Language[28] . "</label></td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\t\t</fieldset>\r\n\r\n\t\t<fieldset class=\"fieldset\">\r\n\t\t<legend>" . $Language[41] . "</legend>\r\n\t\t<table $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t<td class=\"none\"><label for=\"cb_threadid\"><input $type = \"text\" $name = \"tid\" $value = \"0\" $id = \"cb_threadid\" $size = \"10\" /> " . $Language[42] . "</label></td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</fieldset>\r\n\r\n\t\t</div>\r\n\t\t</div>\r\n\r\n\t\t<div class=\"tcat2\">\r\n\t\t<input class=\"button\" $name = \"sbutton\" $accesskey = \"s\" $value = \"" . $Language[31] . "\" $type = \"submit\">\r\n\t\t<input class=\"button\" $value = \"" . $Language[32] . "\" $type = \"reset\">\r\n\t\t</div>\r\n\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t</tbody></table>\r\n\r\n\t\t</form>\r\n\t\t";
    }
}
if ($action == "deletepoll" && var_560($_GET["pollid"])) {
    $pollid = intval($_GET["pollid"]);
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT pollid, question FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'");
    $pollinfo = mysqli_fetch_assoc($Query);
    if (!$pollinfo["pollid"]) {
        $Message = function_76($Language[17]);
    }
    if (!$Message) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'");
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "pollvote WHERE $pollid = '" . $pollid . "'");
        $action = "showlist";
        $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($pollinfo["question"]), $_SESSION["ADMIN_USERNAME"]], $Language[14]);
        function_79($Message);
        $Message = function_76($Message);
    }
}
if ($action == "showlist") {
    $results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "poll"));
    list($pagertop, $limit) = function_82(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=manage_polls&amp;");
    $Print = "";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "poll ORDER BY dateline DESC " . $limit);
    $Print .= "\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $width = \"45%\" $align = \"left\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"10%\" $align = \"left\">" . $Language[38] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"15%\" $align = \"center\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"5%\" $align = \"center\">" . $Language[6] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"10%\" $align = \"center\">" . $Language[7] . "</td>\r\n\t\t<td class=\"alt2\" $width = \"15%\" $align = \"center\">" . $Language[8] . "</td>\r\n\t</tr>\r\n\t";
    if (0 < mysqli_num_rows($Query)) {
        while ($Poll = mysqli_fetch_assoc($Query)) {
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
            $Print .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"45%\" $align = \"left\" class=\"alt1\">" . $LinkToShow . htmlspecialchars($Poll["question"]) . "</a></td>\r\n\t\t\t\t<td $width = \"10%\" $align = \"left\" class=\"alt1\">" . ($Poll["fortracker"] == 1 ? "<font $color = \"blue\">" . $Language[39] : "<font $color = \"red\">" . $Language[40]) . "</font></td>\r\n\t\t\t\t<td $width = \"15%\" $align = \"center\" class=\"alt1\">" . function_84($Poll["dateline"]) . "</td>\r\n\t\t\t\t<td $width = \"5%\" $align = \"center\" class=\"alt1\">" . array_sum(explode("~~~", $Poll["votes"])) . "</td>\r\n\t\t\t\t<td $width = \"10%\" $align = \"center\" class=\"alt1\">" . ($Poll["active"] == "0" ? "<font $color = \"red\">" . $Language[10] : "<font $color = \"green\">" . $Language[9]) . "</font></td>\r\n\t\t\t\t<td $width = \"15%\" $align = \"center\" class=\"alt1\"><a $href = \"index.php?do=manage_polls&amp;$action = polledit&amp;$pollid = " . $Poll["pollid"] . "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[11]) . "\" $title = \"" . trim($Language[11]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_polls&amp;$action = deletepoll&amp;$pollid = " . $Poll["pollid"] . "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "\" $onclick = \"return confirm('" . trim($Language[12]) . ": " . trim($Poll["question"]) . "\\n\\n" . trim($Language[13]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[12]) . "\" $title = \"" . trim($Language[12]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a></td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        $Print .= "<tr><td $colspan = \"6\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_polls&amp;$action = createpoll", $Language[37]) . "</td></tr>";
    }
}
echo function_81("<a $href = \"index.php?do=manage_polls&amp;$action = createpoll&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "\">" . $Language[3] . "</a>");
echo $Message;
echo isset($pagertop) ? $pagertop : "";
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"6\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\r\n\t";
echo isset($Print) ? $Print : "";
echo "</table>\r\n";
echo isset($pagertop) ? $pagertop : "";
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
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_257($data)
{
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $data) . "'";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_258($id)
{
    return is_numeric($id) && 0 < $id && floor($id) == $id;
}
function function_86($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $var_240 = ceil($numresults / $perpage);
    if ($var_240 == 0) {
        $var_240 = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($var_240 < $page) {
            $page = $var_240;
        }
    }
}
function function_87($pagenumber, $perpage, $total)
{
    $var_241 = $perpage * ($pagenumber - 1);
    $var_89 = $var_241 + $perpage;
    if ($total < $var_89) {
        $var_89 = $total;
    }
    $var_241++;
    return ["first" => number_format($var_241), "last" => number_format($var_89)];
}
function function_82($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $var_242 = @ceil($results / $perpage);
    } else {
        $var_242 = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    function_86($results, $pagenumber, $perpage, 200);
    $var_243 = ($pagenumber - 1) * $perpage;
    $var_244 = $pagenumber * $perpage;
    if ($results < $var_244) {
        $var_244 = $results;
        if ($results < $var_243) {
            $var_243 = $results - $perpage - 1;
        }
    }
    if ($var_243 < 0) {
        $var_243 = 0;
    }
    $var_245 = $var_246 = $var_247 = $var_248 = $var_249 = "";
    $var_250 = 0;
    if ($results <= $perpage) {
        $show["pagenav"] = false;
        return ["", "LIMIT " . $var_243 . ", " . $perpage];
    }
    $show["pagenav"] = true;
    $total = number_format($results);
    $show["last"] = false;
    $show["first"] = $show["last"];
    $show["next"] = $show["first"];
    $show["prev"] = $show["next"];
    if (1 < $pagenumber) {
        $var_252 = $pagenumber - 1;
        $var_253 = function_87($var_252, $perpage, $results);
        $show["prev"] = true;
    }
    if ($pagenumber < $var_242) {
        $var_254 = $pagenumber + 1;
        $var_255 = function_87($var_254, $perpage, $results);
        $show["next"] = true;
    }
    $var_256 = "3";
    if (!isset($var_257) || !is_array($var_257)) {
        $var_258 = "10 50 100 500 1000";
        $var_257[] = preg_split("#\\s+#s", $var_258, -1, PREG_SPLIT_NO_EMPTY);
        while ($var_250++ < $var_242) {
        }
        $var_259 = isset($var_252) && $var_252 != 1 ? "page=" . $var_252 : "";
        $var_245 = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $var_242 . "</li>\r\n\t\t\t\t\t\t" . ($show["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $var_260["first"] . " to " . $var_260["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($show["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $var_253["first"] . " to " . $var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $var_245 . "\r\n\t\t\t\t\t\t" . ($show["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_254 . "\" $title = \"Next Page - Show Results " . $var_255["first"] . " to " . $var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($show["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_242 . "\" $title = \"Last Page - Show Results " . $var_261["first"] . " to " . $var_261["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$var_245, "LIMIT " . $var_243 . ", " . $perpage];
    }
    if ($var_256 <= abs($var_250 - $pagenumber) && $var_256 != 0) {
        if ($var_250 == 1) {
            $var_260 = function_87(1, $perpage, $results);
            $show["first"] = true;
        }
        if ($var_250 == $var_242) {
            $var_261 = function_87($var_242, $perpage, $results);
            $show["last"] = true;
        }
        if (in_array(abs($var_250 - $pagenumber), $var_257) && $var_250 != 1 && $var_250 != $var_242) {
            $var_262 = function_87($var_250, $perpage, $results);
            $var_263 = $var_250 - $pagenumber;
            if (0 < $var_263) {
                $var_263 = "+" . $var_263;
            }
            $var_245 .= "<li><a class=\"smalltext\" $href = \"" . $address . ($var_250 != 1 ? "page=" . $var_250 : "") . "\" $title = \"Show results " . $var_262["first"] . " to " . $var_262["last"] . " of " . $total . "\"><!--" . $var_263 . "-->" . $var_250 . "</a></li>";
        }
    } else {
        if ($var_250 == $pagenumber) {
            $var_264 = function_87($var_250, $perpage, $results);
            $var_245 .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $var_264["first"] . " to " . $var_264["last"] . " of " . $total . "\">" . $var_250 . "</a></li>";
        } else {
            $var_262 = function_87($var_250, $perpage, $results);
            $var_245 .= "<li><a $href = \"" . $address . ($var_250 != 1 ? "page=" . $var_250 : "") . "\" $title = \"Show results " . $var_262["first"] . " to " . $var_262["last"] . " of " . $total . "\">" . $var_250 . "</a></li>";
        }
    }
}
function function_84($timestamp = "")
{
    $var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($var_265, $timestamp);
}

?>