<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!function_exists("is_valid_id")) {
    function is_valid_id($id)
    {
        return is_numeric($id) && 0 < $id && floor($id) == $id;
    }
}
if (!function_exists("sqlesc")) {
    function sqlesc($value)
    {
        global $TSDatabase;
        if (@get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "'";
    }
}
if (!function_exists("sql_query")) {
    function sql_query($query)
    {
        global $TSDatabase;
        return mysqli_query($GLOBALS["DatabaseConnect"], $query);
    }
}
function send_pm($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    global $TSDatabase;
    if (!($sender != 0 && !is_valid_id($sender) || !is_valid_id($receiver) || empty($msg))) {
        sql_query("\r\n\t\t\t\t\tINSERT INTO messages \r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES \r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), " . sqlesc($subject) . ", " . sqlesc($msg) . ", '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        sql_query("UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $receiver . "'");
    }
}

?>