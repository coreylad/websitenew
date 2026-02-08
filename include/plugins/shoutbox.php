<?php
if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "2.2 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$FBShoutbox = user_options($CURUSER["options"], "fb-shoutbox");
if (!$FBShoutbox) {
    if (user_options($CURUSER["options"], "shoutbox") === false || !isset($CURUSER)) {
        $UpDownButtons = false;
        $MaxSmilies = 99;
        require_once "./ts_shoutbox/ts_shoutbox.php";
        $shoutbox = $SHOUTBOXCONTENTS;
    } else {
        $lang->load("shoutbox");
        $shoutbox = sprintf($lang->shoutbox["shoutboxisdisabled"], $BASEURL);
    }
}

?>