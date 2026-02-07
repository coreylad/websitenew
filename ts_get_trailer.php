<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_get_trailer.php");
require "./global.php";
define("B_VERSION", "0.3 by xam");
define("SKIP_MOD_QUERIES", true);
define("SKIP_CACHE_MESSAGE", true);
$tid = TS_Global("tid");
if (!isset($CURUSER) || !is_valid_id($tid)) {
    print_no_permission();
}
$Query = sql_query("SELECT name, t_link,trailerurl FROM torrents WHERE $id = " . intval($tid));
if (0 < mysqli_num_rows($Query)) {
    $Torrent = mysqli_fetch_assoc($Query);
    if ($Torrent["trailerurl"]) {
        echo "<iframe $width = \"680\" $height = \"385\" $src = \"" . $Torrent["trailerurl"] . "\" $frameborder = \"0\" $allow = \"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>";
        exit;
    }
    preg_match("@https://www.imdb.com/title/(.*)/@Us", $Torrent["t_link"], $result);
    if ($result && isset($result[1]) && $result[1]) {
        require_once INC_PATH . "/functions_ts_remote_connect.php";
        $imdbid = $result[1];
        $findVideoID = "https://api.themoviedb.org/3/find/" . $imdbid . "?$api_key = 4f8d2d006f1e5aaaf1b235213d80b3e5&$language = en-US&$external_source = imdb_id";
        $result = TS_Fetch_Data($findVideoID);
        if ($result) {
            $result = json_decode($result);
            if ($result && $result->movie_results && $result->movie_results[0]->id) {
                $findVideo = "https://api.themoviedb.org/3/movie/" . $result->movie_results[0]->id . "/videos?$api_key = 4f8d2d006f1e5aaaf1b235213d80b3e5";
                $result = TS_Fetch_Data($findVideo);
                if ($result) {
                    $result = json_decode($result);
                }
                if ($result && $result->results[0]->key) {
                    $youtube = "https://www.youtube.com/embed/" . $result->results[0]->key;
                    sql_query("UPDATE torrents SET $trailerurl = " . sqlesc($youtube) . " WHERE $id = " . intval($tid));
                    $Output = "<iframe $width = \"680\" $height = \"385\" $src = \"" . $youtube . "\" $frameborder = \"0\" $allow = \"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>";
                    echo $Output;
                    exit;
                }
            }
        }
    }
}
exit($lang->global["noresultswiththisid"]);
function safeSearchQuery($string = "", $delimer = "+")
{
    if (ctype_digit($string) || !$string) {
        return $string;
    }
    $string = preg_replace("#[^a-zA-Z0-9]#", $delimer, $string);
    $string = preg_replace("#\\" . $delimer . "\\" . $delimer . "+#", $delimer, $string);
    return trim(strtolower($string));
}

?>