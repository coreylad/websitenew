<?php
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