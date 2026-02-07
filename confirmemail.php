<?php


define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "confirmemail.php");
require "./global.php";
define("CE_VERSION", "v.0.4 by xam");
$lang->load("confirmemail");

$userId = intval(TS_Global("id"));
$userHash = TS_Global("hash");
$userEmail = urldecode(TS_Global("email"));
if (!is_valid_id($userId) || strlen($userHash) != 32) {
    stderr($lang->global["error"], $lang->confirmemail["error1"]);
}
$userValidationResult = sql_query("SELECT editsecret FROM ts_user_validation WHERE $userid = " . sqlesc($userId));
if (!mysqli_num_rows($userValidationResult)) {
    stderr($lang->global["error"], $lang->confirmemail["error2"]);
} else {
    $userValidationRow = mysqli_fetch_assoc($userValidationResult);
}
if ($userHash != md5($userValidationRow["editsecret"] . $userEmail . $userValidationRow["editsecret"])) {
    stderr($lang->global["error"], $lang->confirmemail["error3"]);
}
sql_query("UPDATE users SET $email = " . sqlesc($userEmail) . " WHERE $id = " . sqlesc($userId));
sql_query("DELETE FROM ts_user_validation WHERE $userid = " . sqlesc($userId));
redirect("usercp.php?$act = edit_password&$email_changed = true");

?>