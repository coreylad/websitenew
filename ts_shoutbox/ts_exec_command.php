<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_exec_command.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
$rootpath = "./../";
define("NO_LOGIN_REQUIRED", true);
require $rootpath . "global.php";
$Command = trim(strtolower($_POST["command"]));
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || !$CURUSER["id"] || !$is_mod || !$Command) {
    exit($lang->global["nopermission"]);
}
if (preg_match_all("#^/pruneshout(.*)\$#", $Command, $Matches, PREG_SET_ORDER) && $is_mod) {
    execCommand_pruneshout($Matches);
} else {
    if (preg_match_all("#^/prune(.*)\$#", $Command, $Matches, PREG_SET_ORDER) && $is_mod) {
        execCommand_prune($Matches);
    } else {
        if (preg_match_all("#^/ban(.*)\$#", $Command, $Matches, PREG_SET_ORDER) && $is_mod) {
            execCommand_ban($Matches);
        } else {
            if (preg_match_all("#^/unban(.*)\$#", $Command, $Matches, PREG_SET_ORDER) && $is_mod) {
                execCommand_unban($Matches);
            } else {
                if (preg_match_all("#^/warn(.*)\$#", $Command, $Matches, PREG_SET_ORDER) && $is_mod) {
                    execCommand_warn($Matches);
                } else {
                    if (preg_match_all("#^/unwarn(.*)\$#", $Command, $Matches, PREG_SET_ORDER) && $is_mod) {
                        execCommand_unwarn($Matches);
                    } else {
                        exit($lang->global["nopermission"]);
                    }
                }
            }
        }
    }
}
function execCommand_pruneshout($Data)
{
    $Data = trim($Data[0][1]);
    if (!empty($Data)) {
        sql_query("delete from ts_shoutbox where shout = " . sqlesc($Data)) or sql_query("delete from ts_shoutbox where shout = " . sqlesc($Data)) || sqlerr(__FILE__, 68);
    }
}
function execCommand_prune($Data)
{
    $Data = trim($Data[0][1]);
    if (empty($Data)) {
        sql_query("delete from ts_shoutbox") or sql_query("delete from ts_shoutbox") || sqlerr(__FILE__, 78);
    } else {
        $query = sql_query("SELECT id FROM users WHERE username = " . sqlesc($Data));
        if (0 < mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $Userid = $Result["id"];
            sql_query("delete from ts_shoutbox where uid = " . sqlesc($Userid)) || sqlerr(__FILE__, 88);
        }
    }
    return true;
}
function execCommand_ban($Data)
{
    $Data = trim($Data[0][1]);
    if (!empty($Data)) {
        $query = sql_query("SELECT id FROM users WHERE username = " . sqlesc($Data));
        if (0 < mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $Userid = $Result["id"];
            $query = sql_query("SELECT userid FROM ts_u_perm WHERE userid = " . sqlesc($Userid));
            if (0 < mysqli_num_rows($query)) {
                sql_query("UPDATE ts_u_perm SET canshout = 0 WHERE userid = " . sqlesc($Userid));
            } else {
                sql_query("INSERT INTO ts_u_perm (userid, canshout) VALUES (" . sqlesc($Userid) . ", 0)");
            }
        }
    }
}
function execCommand_unban($Data)
{
    $Data = trim($Data[0][1]);
    if (!empty($Data)) {
        $query = sql_query("SELECT id FROM users WHERE username = " . sqlesc($Data));
        if (0 < mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $Userid = $Result["id"];
            $query = sql_query("SELECT userid FROM ts_u_perm WHERE canshout = 0 AND userid = " . sqlesc($Userid));
            if (0 < mysqli_num_rows($query)) {
                sql_query("UPDATE ts_u_perm SET canshout = 1 WHERE userid = " . sqlesc($Userid));
            }
        }
    }
}
function execCommand_warn($Data)
{
    global $lang;
    global $CURUSER;
    $Data = trim($Data[0][1]);
    if (!empty($Data)) {
        ($query = sql_query("SELECT id, modcomment FROM users WHERE username=" . sqlesc($Data))) || sqlerr(__FILE__, 145);
        if ($query && 0 < mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $userid = $Result["id"];
            $modcomment = $Result["modcomment"];
            $warneduntil = get_date_time(gmtime() + 604800);
            $dur = sprintf($lang->global["warningweeks"], 1);
            $modcomment = sprintf($lang->global["modcommentwarning2"], gmdate("Y-m-d"), $dur, $CURUSER["username"], "Warned in Shoutbox", $modcomment);
            sql_query("UPDATE users SET modcomment = " . sqlesc($modcomment) . ", warneduntil = " . sqlesc($warneduntil) . ", warned = 'yes', timeswarned = timeswarned + 1 WHERE id=" . sqlesc($userid)) || sqlerr(__FILE__, 156);
            require_once INC_PATH . "/functions_pm.php";
            send_pm($userid, sprintf($lang->global["warningmessage2"], $dur, $CURUSER["username"], "ShoutBox!"), $lang->global["warningsubject"]);
        }
    }
}
function execCommand_unwarn($Data)
{
    global $lang;
    global $CURUSER;
    $Data = trim($Data[0][1]);
    if (!empty($Data)) {
        ($query = sql_query("SELECT id, modcomment FROM users WHERE username=" . sqlesc($Data))) || sqlerr(__FILE__, 169);
        if ($query && 0 < mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $userid = $Result["id"];
            $modcomment = $Result["modcomment"];
            $modcomment = sprintf($lang->global["modcommentwarningremovedby"], gmdate("Y-m-d"), $CURUSER["username"], $modcomment);
            sql_query("UPDATE users SET modcomment = " . sqlesc($modcomment) . ", warneduntil = '0000-00-00 00:00:00', warned = 'no', timeswarned = IF(timeswarned > 0, timeswarned - 1, 0) WHERE id=" . sqlesc($userid)) || sqlerr(__FILE__, 178);
            require_once INC_PATH . "/functions_pm.php";
            send_pm($userid, sprintf($lang->global["warningremovedbymessage"], $CURUSER["username"]), $lang->global["warningremovedbysubject"]);
        }
    }
}

?>