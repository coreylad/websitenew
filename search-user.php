<?php
define("THIS_SCRIPT", "users.php");
require "./global.php";
define("U_VERSION", "1.0 by xam");
if ($usergroups["canmemberlist"] != "yes" || !isset($CURUSER)) {
    exit;
}
$username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
if (!$username) {
    exit;
}
if (preg_match("|[^a-z\\|A-Z\\|0-9]|", $username)) {
    exit;
}
$Users = sql_query("SELECT username FROM users WHERE username REGEXP(\"^" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "\")");
if (!mysqli_num_rows($Users)) {
    exit;
}
$Output = [];
while ($Row = mysqli_fetch_assoc($Users)) {
    $Output[] = $Row["username"];
}
jsonHeaders($Output, "application/json");

?>