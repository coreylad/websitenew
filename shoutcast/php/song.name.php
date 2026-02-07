<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "song.name.php");
$rootpath = "./../../";
define("NO_LOGIN_REQUIRED", true);
require $rootpath . "global.php";
define("TS_SHOUTCAST", true);
define("SKIP_AUT", true);
define("CACHE_PATH", "./../");
require "./../setup.php";

$lastPlayedSongs = "";
foreach ($song as $songTitle) {
    $lastPlayedSongs .= "+ " . htmlspecialchars_uni($songTitle) . "<br />";
}
file_put_contents(CACHE_PATH . "lps.dat", $lastPlayedSongs);
echo "&_result=" . htmlspecialchars($song[0]) . "&";

?>