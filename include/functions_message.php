<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function pm_limit($showlimit = true, $disablepm = false, $userid = "", $usergroup = "")
{
    global $lang;
    global $CURUSER;
    global $usergroups;
    if (!isset($CURUSER) || !isset($usergroups)) {
        return false;
    }
    if (!$userid && !$disablepm) {
        $userid = intval($CURUSER["id"]);
    }
    if ($disablepm) {
        $gid = intval($usergroup);
        $getamount = sql_query("SELECT pmquote FROM usergroups WHERE $gid = " . sqlesc($gid) . " LIMIT 1");
        $Result = mysqli_fetch_assoc($getamount);
        $maxpmstorage = intval($Result["pmquote"]);
    } else {
        $maxpmstorage = intval($usergroups["pmquote"]);
    }
    if ($maxpmstorage == 0) {
        return NULL;
    }
    $count1 = mysqli_num_rows(sql_query("SELECT m.* FROM messages m WHERE m.$receiver = " . $userid . " and m.location != 0"));
    $count2 = mysqli_num_rows(sql_query("SELECT m.* FROM messages m WHERE m.$sender = " . $userid . " AND m.$saved = 'yes'"));
    $pmscounttotal = intval($count1 + $count2);
    $overhalf = "";
    $belowhalf = "";
    if ($maxpmstorage <= $pmscounttotal && $disablepm) {
        return false;
    }
    if ($showlimit) {
        if ($maxpmstorage <= $pmscounttotal) {
            $spaceused = 100;
            $spaceused2 = 0;
            $belowhalf = "";
            $overhalf = "100%";
            $warnmsg = show_notice($lang->global["reached_warning2"], true, $lang->global["reached_warning"]) . "<br />";
        } else {
            $spaceused = $pmscounttotal / $maxpmstorage * 100;
            $spaceused2 = 100 - $spaceused;
            $warnmsg = "";
            if ($spaceused <= "50") {
                $belowhalf = round($spaceused, 0) . "%";
            } else {
                $overhalf = round($spaceused, 0) . "%";
            }
        }
        $msg = "\r\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" class=\"tborder\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t<td class=\"trow1\" $align = \"center\"><p>" . sprintf($lang->global["pmlimitmsg"], $pmscounttotal, $maxpmstorage) . "</p>\r\n\t\t<table $align = \"center\" $cellspacing = \"0\" $cellpadding = \"0\" $width = \"230\" $style = \"border: solid 1px #000000;\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"" . $spaceused . "\" class=\"thead\" $align = \"center\">\r\n\t\t\t\t<strong>" . $overhalf . "</span></td>\r\n\t\t\t\t<td $width = \"" . $spaceused2 . "\" class=\"subheader\" $align = \"center\">\r\n\t\t\t\t<strong>" . $belowhalf . "</span></td>\r\n\t\t\t\t<td $width = \"130\" $align = \"center\"><strong>" . $lang->global["pmspace"] . "</strong></td>\r\n\t\t\t</tr>\r\n\t\t</table></tr></td></table><br />";
        $msg = $warnmsg ? $warnmsg . $msg : $msg;
        return $msg;
    }
    return true;
}
function delete_pms($pmids)
{
    global $userid;
    global $mailboxes;
    if (!is_array($pmids)) {
        $pmids = [$pmids];
    }
    foreach ($pmids as $delid) {
        if (is_valid_id($delid)) {
            $res = sql_query("SELECT receiver,saved,sender,location FROM messages WHERE `id` = " . sqlesc((int) $delid));
            $message = mysqli_fetch_assoc($res);
            if ($message["receiver"] == $userid && $message["saved"] == "no") {
                sql_query("DELETE FROM messages WHERE `id` = " . sqlesc((int) $delid)) or sql_query("DELETE FROM messages WHERE `id` = " . sqlesc((int) $delid)) || sqlerr(__FILE__, 89);
            } else {
                if ($message["sender"] == $userid && $message["location"] == $mailboxes["PMDELETED"]) {
                    sql_query("DELETE FROM messages WHERE `id` = " . sqlesc((int) $delid)) or sql_query("DELETE FROM messages WHERE `id` = " . sqlesc((int) $delid)) || sqlerr(__FILE__, 93);
                } else {
                    if ($message["receiver"] == $userid && $message["saved"] == "yes") {
                        sql_query("UPDATE messages SET $location = " . $mailboxes["PMDELETED"] . " WHERE `id` = " . sqlesc((int) $delid)) or sql_query("UPDATE messages SET $location = " . $mailboxes["PMDELETED"] . " WHERE `id` = " . sqlesc((int) $delid)) || sqlerr(__FILE__, 97);
                    } else {
                        if ($message["sender"] == $userid && $message["location"] != $mailboxes["PMDELETED"]) {
                            sql_query("UPDATE messages SET $saved = 'no' WHERE `id` = " . sqlesc((int) $delid)) or sql_query("UPDATE messages SET $saved = 'no' WHERE `id` = " . sqlesc((int) $delid)) || sqlerr(__FILE__, 101);
                        }
                    }
                }
            }
        }
    }
}
function action_box()
{
    global $lang;
    global $mailbox;
    global $mailboxes;
    $box = "\r\n\t<select $name = \"do\">\r\n\t\t" . ($mailbox != $mailboxes["SENDBOX"] ? "<option $value = \"move\">" . $lang->messages["newtitle3"] . "</option>" : "") . "\r\n\t\t<option $value = \"delete\">" . $lang->messages["newtitle4"] . "</option>\r\n\t\t" . ($mailbox != $mailboxes["SENDBOX"] ? "<option $value = \"markasread\">" . $lang->messages["newtitle5"] . "</option>\r\n\t\t<option $value = \"markasunread\">" . $lang->messages["newtitle6"] . "</option>" : "") . "\r\n\t</select>\r\n\t";
    return $box;
}
function get_pmboxes()
{
    global $lang;
    global $userid;
    global $mailboxes;
    global $maxboxs;
    $query = sql_query("SELECT name, boxnumber FROM pmboxes WHERE `userid` = '" . $userid . "' ORDER by boxnumber LIMIT 0, " . $maxboxs);
    $boxes = "\r\n\t<tr><td class=\"subheader\"><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailboxes["INBOX"] . "\">" . $lang->messages["inbox"] . "</a></td></tr>\r\n\t<tr><td class=\"subheader\"><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . $mailboxes["SENDBOX"] . "\">" . $lang->messages["sendbox"] . "</a></td></tr>\r\n\t";
    if (0 < mysqli_num_rows($query)) {
        while ($box = mysqli_fetch_assoc($query)) {
            $boxes .= "<tr><td class=\"subheader\"><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&amp;$mailbox = " . intval($box["boxnumber"]) . "\">" . htmlspecialchars_uni($box["name"]) . "</a></td></tr>";
        }
    }
    return $boxes;
}
function show_message_errors_()
{
    global $_errors;
    global $lang;
    if (0 < count($_errors)) {
        $errors = implode("<br />", $_errors);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\t\t\t\t\t\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\t\t\t\t\t\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>