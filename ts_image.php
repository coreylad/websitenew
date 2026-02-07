<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "ts_image.php");
require "./global.php";
$Hash = TS_Global("hash");
$Width = intval(TS_Global("width"));
$Height = intval(TS_Global("height"));
$Type = TS_Global("type");
if ($Type == "new") {
    $Code = mksecret(5, false);
    $Hash = md5($securehash . $Code . USERIPADDRESS);
    sql_query("REPLACE INTO ts_captcha VALUES ('" . $Hash . "', '" . $Code . "', '" . TIMENOW . "')");
    generateimage($Hash);
} else {
    if ($Hash && strlen($Hash) == 32) {
        generateimage($Hash);
    } else {
        showerrorimage();
    }
}
class TsCaptcha
{
    public function __construct($hash, $width, $height, $characters)
    {
        $code = $this->GetCode($hash);
        $font = INC_PATH . "/captcha_fonts/monofont.ttf";
        $font_size = $height * 0;
        ($image = @imagecreate($width, $height)) || showerrorimage();
        $background_color = imagecolorallocate($image, 67, 69, 18);
        $text_color = imagecolorallocate($image, 245, 245, 187);
        $noise_color = imagecolorallocate($image, 180, 186, 50);
        for ($i = 0; $i < $width * $height / 3; $i++) {
            imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), 1, 1, $noise_color);
        }
        for ($i = 0; $i < $width * $height / 150; $i++) {
            imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $noise_color);
        }
        ($textbox = imagettfbbox($font_size, 0, $font, $code)) || showerrorimage();
        $x = ($width - $textbox[4]) / 2;
        $y = ($height - $textbox[5]) / 2;
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, $code) || showerrorimage();
        header("Expires: Mon, 01 Jan 1990 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-Type: image/jpeg");
        imagejpeg($image);
        imagedestroy($image);
    }
    public function GetCode($hash)
    {
        $Q = sql_query("SELECT answer FROM ts_captcha WHERE hash = " . sqlesc($hash));
        if (mysqli_num_rows($Q)) {
            $Result = mysqli_fetch_assoc($Q);
            return $Result["answer"];
        }
        showerrorimage();
    }
}
class TsCaptcha2 extends TsCaptcha
{
    public function __construct($hash, $width, $height, $characters)
    {
        $code = $this->GetCode($hash);
        ($im = @imagecreate($width, $height)) || showerrorimage();
        $background_color = imagecolorallocate($im, 0, 0, 0);
        $text_color = imagecolorallocate($im, 233, 14, 91);
        $font_size = $height * 0;
        $bottom = $height / 2 / 2;
        $center = $width / 2 / 2;
        imagestring($im, $font_size, $center, $bottom, $code, $text_color);
        header("Expires: Mon, 01 Jan 1990 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy($im);
    }
}
function ShowErrorImage()
{
    global $pic_base_url;
    header("Content-type: image/gif");
    readfile($pic_base_url . "error.gif");
    exit;
}
function gd_version()
{
    if (!extension_loaded("gd")) {
        showerrorimage();
    }
    ob_start();
    phpinfo(8);
    $info = ob_get_contents();
    ob_end_clean();
    $info = stristr($info, "gd version");
    preg_match("/\\d/", $info, $gd);
    $gd_version = $gd[0];
    return $gd_version;
}
function GenerateImage($Hash)
{
    global $Width;
    global $Height;
    if (empty($Width)) {
        $Width = 200;
    }
    if (empty($Height)) {
        $Height = 60;
    }
    if (function_exists("imagettftext") && function_exists("imagettfbbox") && 2 <= gd_version()) {
        $captcha = new TsCaptcha($Hash, $Width, $Height, 5);
    } else {
        $captcha = new TsCaptcha2($Hash, $Width, $Height, 5);
    }
}

?>