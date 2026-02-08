<?php
define("THIS_SCRIPT", "ts_check_pms.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("DISABLE_IPBAN_SYSTEM", true);
$rootpath = "./../";
require $rootpath . "global.php";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $CURUSER["id"] && 0 < $CURUSER["pmunread"]) {
    $lang->load("header");
    exit("<div $id = \"notice\" class=\"highlight\"><a $href = \"" . $BASEURL . "/messages.php\">" . sprintf($lang->header["newmessage"], ts_nf($CURUSER["pmunread"])) . "</a></div>");
}

?>