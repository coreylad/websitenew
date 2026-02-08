<?php
define("OK_VERSION", "0.6 by xam");
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "ok.php");
require "./global.php";
if (isset($CURUSER["id"]) && 0 < $CURUSER["id"]) {
    redirect("index.php");
    exit;
}
$type = TS_Global("type");
$email = TS_Global("email");
if (empty($type)) {
    print_no_permission();
}
$lang->load("ok");
if ($type == "adminactivate") {
    stdhead($lang->ok["head"]);
    stdmsg($lang->ok["title"], $lang->ok["adminactivate"]);
    stdfoot();
} else {
    if ($type == "signup" && !empty($email)) {
        stdhead($lang->ok["head"]);
        stdmsg($lang->ok["title"], sprintf($lang->ok["signupemail"], trim(htmlspecialchars($email)), $SITENAME), false);
        stdfoot();
    } else {
        if ($type == "sysop") {
            stdhead($lang->ok["head2"]);
            if (isset($CURUSER)) {
                stdmsg($lang->ok["title2"], sprintf($lang->ok["sysopact"], $BASEURL), false);
            } else {
                stdmsg($lang->ok["title2"], sprintf($lang->ok["sysopact2"], $BASEURL), false);
            }
            stdfoot();
        } else {
            if ($type == "confirmed") {
                stdhead($lang->ok["head"]);
                stdmsg($lang->ok["title3"], sprintf($lang->ok["confirmed"], $BASEURL), false);
                stdfoot();
            } else {
                if ($type == "confirm") {
                    if (isset($CURUSER)) {
                        stdhead($lang->ok["head"]);
                        stdmsg($lang->ok["title4"], sprintf($lang->ok["confirmed2"], $BASEURL, $SITENAME), false);
                        stdfoot();
                    } else {
                        stdhead($lang->ok["head"]);
                        stdmsg($lang->ok["title4"], sprintf($lang->ok["confirmed3"], $BASEURL), false);
                        stdfoot();
                    }
                } else {
                    print_no_permission();
                }
            }
        }
    }
}

?>