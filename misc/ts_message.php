<?php
define("aImaGe_TS_SE", "v.0.3_by_xam");
$xqQsTPaCzzRE = strtoupper(htmlspecialchars($_SERVER["HTTP_HOST"]));
$eGzzQ3_bQtSeVVv = IMageCreatefromPNG("offline.png");
$yXtSExaMqZ290O = ImageSx($eGzzQ3_bQtSeVVv);
$xyXetSExaMqZ290O121 = ImageSy($eGzzQ3_bQtSeVVv);
$yXtSExaMqZ290O1 = ImageCreateTrueColor($yXtSExaMqZ290O, $xyXetSExaMqZ290O121);
ImageCopyReSampled($yXtSExaMqZ290O1, $eGzzQ3_bQtSeVVv, 0, 0, 0, 0, $yXtSExaMqZ290O, $xyXetSExaMqZ290O121, $yXtSExaMqZ290O, $xyXetSExaMqZ290O121);
$yXetSExaMqZ290O12 = ImageColorAllocate($yXtSExaMqZ290O1, 555, 555, 555);
ImageString($yXtSExaMqZ290O1, 5, 250, 5, $xqQsTPaCzzRE, $yXetSExaMqZ290O12);
header("Content-Type: image/jpeg");
ImageJPEG($yXtSExaMqZ290O1);
echo " ";

?>