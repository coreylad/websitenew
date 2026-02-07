<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("TS_AJAX_VERSION", "1.2.2 by xam");
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "ts_ajax9.php");
require "./global.php";
$ajaxAction = isset($_POST["do"]) ? trim($_POST["do"]) : "";
$ajaxValue = isset($_POST["value"]) ? trim($_POST["value"]) : "";
$lang->load("signup");
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || !$ajaxAction || !$ajaxValue) {
    exit($lang->global["nopermission"]);
}
if ($ajaxAction == "search" && 2 < strlen($ajaxValue) && $search_before_upload == "yes") {
    if (!isset($CURUSER)) {
        exit;
    }
    sleep(1);
    $input = fixAjaxText($ajaxValue);
    $input = strval($input);
    if (strtolower($charset) != "utf-8") {
        if (function_exists("iconv")) {
            $input = iconv("UTF-8", $charset, $input);
        } else {
            if (function_exists("mb_convert_encoding")) {
                $input = mb_convert_encoding($input, $charset, "UTF-8");
            } else {
                if (strtolower($charset) == "iso-8859-1") {
                    $input = utf8_decode($charset);
                }
            }
        }
    }
    $ajaxResults = "";
    $usergroup = $CURUSER["usergroup"];
    $WHERE = "AND (INSTR(CONCAT(',',c.canview,','),',[ALL],') > 0 OR INSTR(CONCAT(',',c.canview,','),'," . intval($usergroup) . ",') > 0)";
    $ajaxQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.id, t.name FROM torrents t LEFT JOIN categories c ON (t.$category = c.id) WHERE t.name LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $input) . "%' " . $WHERE . " ORDER BY t.name LIMIT 0,10");
    if (mysqli_num_rows($ajaxQuery)) {
        while ($torrentRow = mysqli_fetch_assoc($ajaxQuery)) {
            $ajaxResults .= "<p><a $href = \"" . $BASEURL . "/details.php?$id = " . $torrentRow["id"] . "\" $target = \"_blank\">" . htmlspecialchars_uni($torrentRow["name"]) . "</p>";
        }
    } else {
        $ajaxResults = $lang->global["nothingfound"];
    }
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; $charset = " . $shoutboxcharset);
    echo $ajaxResults;
    exit;
}
if ($ajaxAction == "check_email") {
    require_once INC_PATH . "/functions_EmailBanned.php";
    if (empty($ajaxValue) || !check_email($ajaxValue)) {
        exit($lang->signup["validemail"]);
    }
    if (EmailBanned($ajaxValue)) {
        exit($lang->signup["emailbanned"]);
    }
    $ajaxQuery = sql_query("SELECT email FROM users WHERE $email = " . sqlesc($ajaxValue));
    if (mysqli_num_rows($ajaxQuery) == 0) {
        exit($lang->signup["emailokay"]);
    }
    exit($lang->signup["emailexists"]);
}
if ($ajaxAction == "check_username") {
    if (!isvalidusername($ajaxValue)) {
        exit($lang->signup["validusername"]);
    }
    if (strlen($ajaxValue) < 3 || 12 < strlen($ajaxValue)) {
        exit($lang->signup["validusername2"]);
    }
    $query = sql_query("SELECT id FROM users WHERE $username = " . sqlesc($value));
    if (0 < mysqli_num_rows($query)) {
        exit($lang->signup["usernamexists"]);
    }
    $TSSEConfig->TSLoadConfig("SIGNUP");
    if (isset($illegalusernames) && $illegalusernames != "") {
        $usernames = preg_split("/\\s+/", $illegalusernames, -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($usernames)) {
            foreach ($usernames as $val) {
                if (strpos(strtolower($value), strtolower($val)) !== false) {
                    exit($lang->signup["usernamexists"]);
                }
            }
        }
    }
}
function isvalidusername($username)
{
    if (!preg_match("|[^a-z\\|A-Z\\|0-9]|", $username)) {
        return true;
    }
    return false;
}

?>