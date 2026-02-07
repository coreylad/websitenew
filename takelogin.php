<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "takelogin.php");
require "./global.php";
define("TL_VERSION", "1.0.0 by xam");
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || isset($CURUSER) && 0 < $CURUSER["id"]) {
    redirect("index.php");
    exit;
}
require_once INC_PATH . "/functions_security.php";
require_once INC_PATH . "/functions_login.php";
failedloginscheck();
$username = TS_Global("username");
$password = TS_Global("password");
$lang->load("login");
if (empty($username) || empty($password)) {
    redirect("login.php?$error = 3" . (!empty($username) ? "&$username = " . htmlspecialchars_uni($username) : ""));
    exit;
}
if (isCaptchaEnabled()) {
    check_code(isset($_POST["imagestring"]) ? $_POST["imagestring"] : "", "login.php", true, !empty($username) ? "&$username = " . htmlspecialchars_uni($username) : "");
}
$res = sql_query("SELECT id, passhash, secret, enabled, usergroup, status, notifs, pmunread FROM users WHERE `username` = " . sqlesc($username));
if (!mysqli_num_rows($res)) {
    failedlogins("silent");
    redirect("login.php?$error = 1" . (!empty($username) ? "&$username = " . htmlspecialchars_uni($username) : ""));
    exit;
}
$row = mysqli_fetch_assoc($res);
if ($row["passhash"] != md5($row["secret"] . $password . $row["secret"])) {
    $md5pw = md5($password);
    $iphost = @gethostbyaddr(USERIPADDRESS);
    failedlogins("login", false, true, true, (int) $row["id"]);
    redirect("login.php?$error = 4" . (!empty($username) ? "&$username = " . htmlspecialchars_uni($username) : ""));
    exit;
}
if ($row["enabled"] == "no") {
    stderr($lang->login["banned"], $row["notifs"]);
} else {
    if ($row["status"] == "pending") {
        stderr($lang->global["error"], $lang->login["pending"]);
    }
}
logoutcookie();
if (isset($_POST["logout"]) && $_POST["logout"] == "yes") {
    logincookie($row["id"], $row["passhash"], 900);
} else {
    logincookie($row["id"], $row["passhash"]);
}
sql_query("DELETE FROM loginattempts WHERE $banned = 'no' AND $ip = " . sqlesc(USERIPADDRESS));
$returnto = !empty($_POST["returnto"]) ? fix_url($_POST["returnto"]) : "index.php?$logged = true";
$returnto = str_replace([$BASEURL, "//"], ["", "/"], $returnto);
if ($row["pmunread"]) {
    $returnto = "messages.php";
}
redirect($returnto, $lang->login["logged"]);

?>