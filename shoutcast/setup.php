<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_SHOUTCAST")) {
    exit;
}
$TSSEConfig->TSLoadConfig("SHOUTCAST");
if (!defined("SKIP_AUT") && ($s_allowedusergroups = explode(",", $s_allowedusergroups)) && !in_array($CURUSER["usergroup"], $s_allowedusergroups)) {
    print_no_permission();
}
// Server configuration variables
$serverName = $s_servername;
$serverIp = $s_serverip;
$serverPort = $s_serverport;
$serverPassword = $s_serverpassword;
$ircServer = $s_serverirc;
$cacheFilePath = (defined("CACHE_PATH") ? CACHE_PATH : "") . $s_servercachefile;
$cacheTolerance = $s_servercachetime;
if (file_exists($cacheFilePath)) {
    clearstatcache();
    $timeDifference = time() - filemtime($cacheFilePath);
} else {
    $timeDifference = $cacheTolerance;
}
$socketConnection = @fsockopen($serverIp, $serverPort, $errno, $errstr, 3);
if ($socketConnection) {
    if ($cacheTolerance <= $timeDifference) {
        if (!isset($shoutcastSuccess)) {
            $xmlFeed = "";
            fputs($socketConnection, "GET /admin.cgi?pass=" . $serverPassword . "&mode=viewxml HTTP/1.0\r\nUser-Agent: SHOUTcast Song Status (Mozilla Compatible)\r\n\r\n");
            while (!feof($socketConnection)) {
                $xmlFeed .= fgets($socketConnection, 8192);
            }
            fclose($socketConnection);
        }
        file_put_contents($cacheFilePath, $xmlFeed);
        flush();
        $xmlCache = fopen($cacheFilePath, "r");
        $xmlPage = "";
        if ($xmlCache) {
            while (!feof($xmlCache)) {
                $xmlPage .= fread($xmlCache, 8192);
            }
            fclose($xmlCache);
        }
    } else {
        $xmlCache = fopen($cacheFilePath, "r");
        $xmlPage = "";
        if ($xmlCache) {
            while (!feof($xmlCache)) {
                $xmlPage .= fread($xmlCache, 8192);
            }
            fclose($xmlCache);
        }
    }
    $shoutcastFields = ["AVERAGETIME", "CURRENTLISTENERS", "PEAKLISTENERS", "MAXLISTENERS", "SERVERGENRE", "SERVERURL", "SERVERTITLE", "SONGTITLE", "SONGURL", "IRC", "ICQ", "AIM", "WEBHITS", "STREAMHITS", "LISTEN", "STREAMSTATUS", "BITRATE", "CONTENT"];
    define("SERVERGENRE", "");
    define("SONGTITLE", "");
    define("SERVERTITLE", "");
    for ($fieldIndex = 0; isset($shoutcastFields[$fieldIndex]) && $shoutcastFields[$fieldIndex] != ""; $fieldIndex++) {
        $pageEdited = preg_replace("@.*<" . $shoutcastFields[$fieldIndex] . ">@is", "", $xmlPage);
        $fieldName = strtolower($shoutcastFields[$fieldIndex]);
        ${$fieldName} = preg_replace("@</" . $shoutcastFields[$fieldIndex] . ">.*@is", "", $pageEdited);
        if ($shoutcastFields[$fieldIndex] == SERVERGENRE || $shoutcastFields[$fieldIndex] == SONGTITLE || $shoutcastFields[$fieldIndex] == SERVERTITLE) {
            ${$fieldName} = urldecode(${$fieldName});
        }
    }
    $pageEdited = preg_replace("@.*<SONGHISTORY>@is", "", $xmlPage);
    $pageEdited = preg_replace("@<SONGHISTORY>.*@is", "", $pageEdited);
    $songHistory = explode("<SONG>", $pageEdited);
    for ($songIndex = 1; isset($songHistory[$songIndex]) && $songHistory[$songIndex] != ""; $songIndex++) {
        $historyIndex = $songIndex - 1;
        $playedAt[$historyIndex] = preg_replace("@.*<PLAYEDAT>@is", "", $songHistory[$songIndex]);
        $playedAt[$historyIndex] = preg_replace("@</PLAYEDAT>.*@is", "", $playedAt[$historyIndex]);
        $songTitle[$historyIndex] = preg_replace("@.*<TITLE>@is", "", $songHistory[$songIndex]);
        $songTitle[$historyIndex] = preg_replace("@</TITLE>.*@is", "", $songTitle[$historyIndex]);
        $songTitle[$historyIndex] = urldecode($songTitle[$historyIndex]);
        $djName[$historyIndex] = preg_replace("@.*<SERVERTITLE>@is", "", $xmlPage);
        $djName[$historyIndex] = preg_replace("@</SERVERTITLE>.*@is", "", $pageEdited);
    }
    $averageMinutes = round($averagetime / 60, 2);
    $ircLink = "irc://" . $ircServer . "/" . htmlspecialchars_uni($irc);
    $listenAmpUrl = "http://" . $serverIp . ":" . $serverPort . "/listen.pls";
    $listenLink = "http://" . $serverIp . ":" . $serverPort . "";
}

?>