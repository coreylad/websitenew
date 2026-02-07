<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "poll.php");
require "./global.php";
$tid = isset($_POST["tid"]) ? intval($_POST["tid"]) : (isset($_GET["tid"]) ? intval($_GET["tid"]) : 0);
$polloptions = isset($_POST["polloptions"]) ? intval($_POST["polloptions"]) : (isset($_GET["polloptions"]) ? intval($_GET["polloptions"]) : 10);
$polloptions = !is_valid_id($polloptions) || $polloptions < 2 || 20 < $polloptions ? 4 : $polloptions;
$pollAction = isset($_POST["do"]) ? htmlspecialchars_uni($_POST["do"]) : (isset($_GET["do"]) ? htmlspecialchars_uni($_GET["do"]) : "");
$question = isset($_POST["question"]) ? trim($_POST["question"]) : (isset($_GET["question"]) ? trim($_GET["question"]) : "");
$options = isset($_POST["options"]) ? $_POST["options"] : (isset($_GET["options"]) ? $_GET["options"] : "");
$pollid = isset($_POST["pollid"]) ? intval($_POST["pollid"]) : (isset($_GET["pollid"]) ? intval($_GET["pollid"]) : 0);
$posthash = isset($_POST["posthash"]) ? trim($_POST["posthash"]) : (isset($_GET["posthash"]) ? trim($_GET["posthash"]) : "");
if (!is_valid_id($tid)) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
($threadQuery = sql_query("SELECT \r\n\t\t\tt.tid, t.closed, t.pollid, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = " . sqlesc($tid) . " LIMIT 1")) || sqlerr(__FILE__, 48);
if (mysqli_num_rows($threadQuery) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$threadRow = mysqli_fetch_assoc($threadQuery);
 $forumModerator = is_forum_mod($threadRow["type"] == "s" ? $threadRow["deepforumid"] : $threadRow["currentforumid"], $CURUSER["id"]);
if (!$moderator && !$forumModerator && ($permissions[$threadRow["currentforumid"]]["canview"] != "yes" || $permissions[$threadRow["currentforumid"]]["canviewthreads"] != "yes")) {
    print_no_permission(true);
    exit;
}
if ($pollAction == "createnewpoll" && $usergroups["cancreatepoll"] == "yes") {
    if ($threadRow["pollid"]) {
        stderr($lang->global["error"], $lang->tsf_forums["poll9"]);
        exit;
    }
    if (empty($question) || strlen($question) < $f_minmsglength || count($options) < 2 || 20 < count($options)) {
        $pollError = $lang->tsf_forums["poll8"];
    } else {
        $optionsCount = 0;
        $optionsArray = $votesArray = [];
        foreach ($options as $optionId => $optionText) {
            if (!empty($optionText) && $f_minmsglength < strlen($optionText)) {
                $optionsArray[$optionId] = trim($optionText);
                $votesArray[$optionId] = 0;
                $optionsCount++;
            }
        }
        if ($optionsCount < 2) {
            $pollError = $lang->tsf_forums["poll8"];
        }
    }
    if (isset($pollError)) {
        $pollAction = "new";
    } else {
        $question = sqlesc($question);
        $dateline = sqlesc(TIMENOW);
        $options = sqlesc(implode("~~~", $optionsArray));
        $votes = sqlesc(implode("~~~", $votesArray));
        $numberOptions = sqlesc(intval($optionsCount));
        sql_query("INSERT INTO " . TSF_PREFIX . "poll (question, dateline, options, votes, numberoptions) VALUES (" . $question . ", " . $dateline . ", " . $options . ", " . $votes . ", " . $numberOptions . ")") || sqlerr(__FILE__, 111);
        $pollid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        sql_query("UPDATE " . TSF_PREFIX . "threads SET $pollid = " . sqlesc($pollid) . " WHERE $tid = " . sqlesc($tid));
        redirect("tsf_forums/showthread.php?$tid = " . $tid);
        exit;
    }
}
if ($pollAction == "new" && $usergroups["cancreatepoll"] == "yes") {
    if ($threadRow["pollid"]) {
        stderr($lang->global["error"], $lang->tsf_forums["poll9"]);
        exit;
    }
    $showPollOptionsHtml = "";
    for ($optionIndex = 1; $optionIndex <= $polloptions; $optionIndex++) {
        $showPollOptionsHtml .= "\r\n\t\t" . sprintf($lang->tsf_forums["poll6"], $optionIndex) . "<br />\r\n\t\t<input $type = \"text\" $name = \"options[" . $optionIndex . "]\" $value = \"" . (isset($options[$optionIndex]) ? htmlspecialchars_uni($options[$optionIndex]) : "") . "\" $size = \"50\"><br />\r\n\t\t";
    }
    stdhead($lang->tsf_forums["poll1"]);
    if (isset($pollError)) {
        stdmsg($lang->global["error"], $pollError, false);
    }
    echo "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"createnewpoll\">\r\n\t<input $type = \"hidden\" $name = \"tid\" $value = \"" . $tid . "\">\r\n\t<input $type = \"hidden\" $name = \"polloptions\" $value = \"" . $polloptions . "\">\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" class=\"none\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">" . $lang->tsf_forums["poll1"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<FIELDSET>\r\n\t\t\t\t\t<LEGEND>" . $lang->tsf_forums["poll4"] . "</LEGEND>\r\n\t\t\t\t\t<input $type = \"text\" $size = \"50\" $name = \"question\" $value = \"" . htmlspecialchars_uni($question) . "\">\r\n\t\t\t\t</FIELDSET>\r\n\t\t\t\t<FIELDSET>\t\t\t\r\n\t\t\t\t\t<LEGEND>" . $lang->tsf_forums["poll5"] . "</LEGEND>\r\n\t\t\t\t\t" . $showPollOptionsHtml . "\r\n\t\t\t\t</FIELDSET>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->tsf_forums["poll7"] . "\"></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
    stdfoot();
    exit;
}

?>