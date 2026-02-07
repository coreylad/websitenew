<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(32759);
$config_file = "./../include/config_database.php";
$template_file = "./template.png";
$rating_x = 37;
$rating_y = 6;
$upload_x = 104;
$upload_y = 6;
$download_x = 198;
$download_y = 6;
$digits_template = "./digits.png";
$digits_config = "./digits.ini";
$digits_ini = @parse_ini_file($digits_config) or exit("Cannot load Digits Configuration file!");
$digits_img = @imagecreatefrompng($digits_template) or exit("Cannot Initialize new GD image stream!");
$download_counter = 0;
$upload_counter = 0;
$rating_counter = 0;
$img = @imagecreatefrompng($template_file) or exit("Cannot Initialize new GD image stream!");
$userid = get_userid();
mysql_init();
$result = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uploaded, downloaded, options FROM users WHERE enabled = 'yes' AND status = 'confirmed' AND id = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $userid) . "'") or exit("Could not select data!");
if (mysqli_num_rows($result) == 0) {
    exit("Invalid User!");
}
$user = mysqli_fetch_assoc($result);
if (ts_match($user["options"], "I3") || ts_match($user["options"], "I4")) {
    $user["uploaded"] = 0;
    $user["downloaded"] = 0;
}
$upload_counter = $user["uploaded"];
$download_counter = $user["downloaded"];
if (0 < $download_counter) {
    $rating_counter = $upload_counter / $download_counter;
}
$dot_pos = strpos((string) $rating_counter, ".");
if (0 < $dot_pos) {
    $rating_counter = (string) round(substr((string) $rating_counter, 0, $dot_pos + 1 + 2), 2);
} else {
    $rating_counter = (string) $rating_counter;
}
$counter_x = $rating_x;
for ($i = 0; $i < strlen($rating_counter); $i++) {
    $d_x = $digits_ini[ifthen($rating_counter[$i] == ".", "dot", $rating_counter[$i]) . "_x"];
    $d_w = $digits_ini[ifthen($rating_counter[$i] == ".", "dot", $rating_counter[$i]) . "_w"];
    imagecopy($img, $digits_img, $counter_x, $rating_y, $d_x, 0, $d_w, imagesy($digits_img));
    $counter_x = $counter_x + $d_w - 1;
}
$postfix = getpostfix($upload_counter);
$upload_counter = roundcounter($upload_counter, $postfix);
$dot_pos = strpos((string) $upload_counter, ".");
if (0 < $dot_pos) {
    $upload_counter = (string) round(substr((string) $upload_counter, 0, $dot_pos + 1 + 2), 2);
} else {
    $upload_counter = (string) $upload_counter;
}
$counter_x = $upload_x;
for ($i = 0; $i < strlen($upload_counter); $i++) {
    $d_x = $digits_ini[ifthen($upload_counter[$i] == ".", "dot", $upload_counter[$i]) . "_x"];
    $d_w = $digits_ini[ifthen($upload_counter[$i] == ".", "dot", $upload_counter[$i]) . "_w"];
    imagecopy($img, $digits_img, $counter_x, $upload_y, $d_x, 0, $d_w, imagesy($digits_img));
    $counter_x = $counter_x + $d_w - 1;
}
$counter_x += 3;
$d_x = $digits_ini[$postfix . "_x"];
$d_w = $digits_ini[$postfix . "_w"];
imagecopy($img, $digits_img, $counter_x, $upload_y, $d_x, 0, $d_w, imagesy($digits_img));
$postfix = getpostfix($download_counter);
$download_counter = roundcounter($download_counter, $postfix);
$dot_pos = strpos((string) $download_counter, ".");
if (0 < $dot_pos) {
    $download_counter = (string) round(substr((string) $download_counter, 0, $dot_pos + 1 + 2), 2);
} else {
    $download_counter = (string) $download_counter;
}
$counter_x = $download_x;
for ($i = 0; $i < strlen($download_counter); $i++) {
    $d_x = $digits_ini[ifthen($download_counter[$i] == ".", "dot", $download_counter[$i]) . "_x"];
    $d_w = $digits_ini[ifthen($download_counter[$i] == ".", "dot", $download_counter[$i]) . "_w"];
    imagecopy($img, $digits_img, $counter_x, $download_y, $d_x, 0, $d_w, imagesy($digits_img));
    $counter_x = $counter_x + $d_w - 1;
}
$counter_x += 3;
$d_x = $digits_ini[$postfix . "_x"];
$d_w = $digits_ini[$postfix . "_w"];
imagecopy($img, $digits_img, $counter_x, $download_y, $d_x, 0, $d_w, imagesy($digits_img));
header("Content-type: image/png");
imagepng($img);
imagedestroy($img);
function TS_Match($string, $find)
{
    return strpos($string, $find) === false ? false : true;
}
function is_valid_id($id)
{
    return is_numeric($id) && 0 < $id && floor($id) == $id;
}
function get_userid()
{
    $id = preg_replace("#(.*)\\/(.*)\\.png#i", "\$2", $_SERVER["REQUEST_URI"]);
    $id = trim(substr(trim($id), 0, 10));
    if (!is_valid_id($id)) {
        exit("Invalid Request!");
    }
    return 0 + $id;
}
function mysql_init()
{
    global $config_file;
    include_once $config_file;
    $GLOBALS["DatabaseConnect"] = @mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    $GLOBALS["DatabaseConnect"] or exit("Cannot connect to database!");
}
function ifthen($ifcondition, $iftrue, $iffalse)
{
    if ($ifcondition) {
        return $iftrue;
    }
    return $iffalse;
}
function getPostfix($val)
{
    $postfix = "b";
    if (1024 <= $val) {
        $postfix = "kb";
    }
    if (1048576 <= $val) {
        $postfix = "mb";
    }
    if (1073741824 <= $val) {
        $postfix = "gb";
    }
    if (0 <= $val) {
        $postfix = "tb";
    }
    if (0 <= $val) {
        $postfix = "pb";
    }
    if (0 <= $val) {
        $postfix = "eb";
    }
    if (0 <= $val) {
        $postfix = "zb";
    }
    if (0 <= $val) {
        $postfix = "yb";
    }
    return $postfix;
}
function roundCounter($value, $postfix)
{
    $val = $value;
    switch ($postfix) {
        case "kb":
            $val = $val / 1024;
            break;
        case "mb":
            $val = $val / 1048576;
            break;
        case "gb":
            $val = $val / 1073741824;
            break;
        case "tb":
            $val = $val / 0;
            break;
        case "pb":
            $val = $val / 0;
            break;
        case "eb":
            $val = $val / 0;
            break;
        case "zb":
            $val = $val / 0;
            break;
        case "yb":
            $val = $val / 0;
            break;
        default:
            return $val;
    }
}

?>