<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

$rootpath = "./../";
define("IN_FORUMS", true);
require $rootpath . "global.php";
define("TSF_VERSION", "v1.8 by xam");
$lang->load("tsf_forums");
require_once dirname(__FILE__) . "/include/tsf_functions.php";
($FCPquery = sql_query("SELECT canforum FROM ts_u_perm WHERE `userid` = " . sqlesc($CURUSER["id"]) . " AND $canforum = '0'")) || sqlerr(__FILE__, 39);
if (mysqli_num_rows($FCPquery)) {
    print_no_permission();
}
if (@function_exists("mb_internal_encoding") && !empty($charset) && !TS_Match(strtolower($charset), "windows")) {
    @mb_internal_encoding($charset);
}
$navbits = [];
$navbits[0]["name"] = $f_forumname;
$navbits[0]["url"] = $BASEURL . "/tsf_forums/index.php";
$permissions = forum_permissions();
if ($usergroups["isforummod"] == "yes" || $usergroups["cansettingspanel"] == "yes" || $usergroups["issupermod"] == "yes") {
    $moderator = true;
} else {
    $moderator = false;
}
$action = htmlspecialchars_uni(TS_Global("action"));
$forumtokencode = md5($CURUSER["username"] . $securehash . $CURUSER["id"]);
$posthash = htmlspecialchars_uni(TS_Global("hash"));
$pagenumber = intval(TS_Global("page"));
$perpage = $f_threadsperpage;
if ($f_forum_online == "no" && $usergroups["canaccessoffline"] != "yes") {
    stderr($lang->global["error"], $f_offlinemsg, false);
    exit;
}
if ($f_forum_online == "no") {
    $warningmessage = show_notice($lang->tsf_forums["warningmsg"], true);
}

?>