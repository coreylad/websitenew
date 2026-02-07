<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font face=\"verdana\" size=\"2\" color=\"darkred\"><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function setLoginCookie($id, $passhash, $expires = 2147483647)
{
    $passhash = securehash($passhash);
    if ($expires != 2147483647) {
        $expires = TIMENOW + $expires;
    }
    setcookie("c_secure_uid", $id, $expires, "/");
    setcookie("c_secure_pass", $passhash, $expires, "/");
}
function clearLoginCookie()
{
    setcookie("c_secure_uid", "", 2147483647, "/");
    setcookie("c_secure_pass", "", 2147483647, "/");
}
function registration_check($type = "invitesystem", $maxuserscheck = true, $ipcheck = true)
{
    global $invitesystem;
    global $registration;
    global $maxusers;
    global $maxip;
    global $lang;
    global $cache;
    if ($type == "invitesystem" && $invitesystem == "off") {
        stderr($lang->global["error"], $lang->global["invitedisabled"]);
    }
    if ($type == "normal" && $registration == "off") {
        stderr($lang->global["error"], $invitesystem == "on" ? $lang->global["inviteonly"] : $lang->global["signupdisabled"], false);
    }
    if ($maxuserscheck) {
        require_once TSDIR . "/" . $cache . "/indexstats.php";
        if ($maxusers <= $indexstats["registered"]) {
            stderr($lang->global["error"], $lang->global["signuplimitreached"]);
        }
    }
    ($a = @mysqli_fetch_row(@sql_query("SELECT COUNT(ip) FROM users WHERE ip=" . @sqlesc(USERIPADDRESS)))) || sqlerr(__FILE__, 52);
    if (0 < intval($maxip) && $maxip <= $a[0]) {
        stderr($lang->global["error"], sprintf($lang->global["nodupeaccount"], htmlspecialchars_uni(USERIPADDRESS)), false);
    } else {
        ($a = @mysqli_fetch_row(@sql_query("SELECT COUNT(ip) FROM iplog WHERE ip=" . @sqlesc(USERIPADDRESS)))) || sqlerr(__FILE__, 59);
        if (0 < $a[0] && 0 < intval($maxip)) {
            stderr($lang->global["error"], $lang->global["nodupeaccount2"]);
        }
    }
}
function cur_user_check()
{
    global $CURUSER;
    global $lang;
    if (isset($CURUSER) && 0 < $CURUSER["id"]) {
        redirect("index.php", $lang->global["alreadylogged"]);
    }
}

?>