<?php
if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.2 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$disclaimer = "\r\n<!-- begin disclaimer -->\t\r\n<div $align = \"justify\">\r\n\t" . sprintf($lang->index["diclaimermessage"], $BASEURL) . "\r\n\t<br /><br />\r\n\t" . $lang->index["note"] . "\r\n</div>\r\n<!-- end disclaimer -->";

?>