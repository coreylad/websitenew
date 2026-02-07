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
define("THIS_SCRIPT", "fb-shoutbox-load-smilies.php");
require "./global.php";
define("FB_LOAD_SMILIES", "1.0 by xam");
require $rootpath . "/" . $cache . "/smilies.php";
$showsmilies = "";
foreach ($smilies as $code => $name) {
    $showsmilies .= "\n\t<img $src = \"" . $pic_base_url . "smilies/" . $name . "\" $alt = \"\" $title = \"\" $border = \"0\" data-$code = \"" . $code . "\" />";
}
echo $showsmilies;

?>