<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("VN_VERSION", "0.8 by xam");
define("THIS_SCRIPT", "viewnfo.php");
require "./global.php";
if (!isset($CURUSER)) {
    print_no_permission();
}
// Torrent NFO image rendering
$nfoId = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
$hasError = false;
$nfoData = [];
if (!is_valid_id($nfoId)) {
    $hasError = true;
    $nfoData["nfo"] = $lang->global["no_permission"];
}
if (!$hasError && version_compare("4.0.6", phpversion()) == 1) {
    $hasError = true;
    $nfoData["nfo"] = "This version of PHP is not fully supported.";
}
if (!$hasError && !extension_loaded("gd")) {
    $hasError = true;
    $nfoData["nfo"] = "Missing GD / GD-2 Library.";
}
if (!$hasError) {
    $nfoQuery = sql_query("SELECT nfo FROM ts_nfo WHERE id = " . sqlesc($nfoId));
    if (mysqli_num_rows($nfoQuery) < 1) {
        $hasError = true;
        $nfoData["nfo"] = $lang->global["nopermission"];
    } else {
        $nfoData = mysqli_fetch_assoc($nfoQuery);
    }
}
if (empty($nfoData["nfo"])) {
    $nfoData["nfo"] = $lang->global["nopermission"];
}
$colorRed = 0;
$colorGreen = 0;
$colorBlue = 0;
$fontColorIndex = 0;
$fontImage = imagecreatefrompng($pic_base_url . "nfogen.png");
$drawX = 0;
$drawY = 0;
$fontWidth = 5;
$fontHeight = 12;
$fontColorIndex = $fontColorIndex * $fontHeight;
$nfoLines = explode("\n", $nfoData["nfo"]);
$imageHeight = count($nfoLines) * 12;
$imageWidth = 0;
foreach ($nfoLines as $lineIndex => $nfoLine) {
    $lineLength = strlen($nfoLine);
    if ($imageWidth < $lineLength) {
        $imageWidth = $lineLength;
    }
}
$imageWidth = $imageWidth * $fontWidth;
if (1600 < $imageWidth) {
    $imageWidth = 1600;
}
$imageResource = imagecreatetruecolor($imageWidth, $imageHeight);
$backgroundColor = imagecolorallocate($imageResource, $colorRed, $colorGreen, $colorBlue);
imagefill($imageResource, 0, 0, $backgroundColor);
foreach ($nfoLines as $lineIndex => $nfoLine) {
    $drawX = $fontWidth;
    for ($charIndex = 0; $charIndex < strlen($nfoLine); $charIndex++) {
        $currentChar = substr($nfoLine, $charIndex, 1);
        if ($currentChar !== "\r" && $currentChar !== "\n") {
            $charOffset = ord($currentChar) * 5;
            imagecopy($imageResource, $fontImage, $drawX, $drawY, $charOffset, $fontColorIndex, $fontWidth, $fontHeight);
            $drawX += $fontWidth;
        }
    }
    $drawY += $fontHeight;
}
header("Content-type: image/png");
imagepng($imageResource);
imagedestroy($imageResource);
exit;

?>