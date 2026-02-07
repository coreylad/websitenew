<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function TS_Clean_String($imputString)
{
    $whatToCleanArray = [chr(13), chr(10), chr(13) . chr(10), chr(10) . chr(13), "\n", "  ", "   ", "    ", "\n\n", "\n\r", "<br>", "<br/>", "<br />"];
    $cleanWithArray = ["", "", "", "", "", "", "", "", "", "", "", "", ""];
    $cleaned = str_replace($whatToCleanArray, $cleanWithArray, $imputString);
    $cleaned = trim($cleaned);
    return $cleaned;
}
function TS_Fetch_Data($URL, $cleantext = true)
{
    $USERAGENT = "Googlebot/2.1 (+http://www.google.com/bot.html)";
    $REFERER = "http://www.google.com";
    @ini_set("user_agent", $USERAGENT);
    $data = false;
    $timeout = 10;
    if (function_exists("curl_init") && ($ch = curl_init())) {
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_REFERER, $REFERER);
        curl_setopt($ch, CURLOPT_USERAGENT, $USERAGENT);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);
        $CURLUSED = true;
    }
    if (!isset($CURLUSED) && !$data && ini_get("allow_url_fopen")) {
        $old = ini_set("default_socket_timeout", $timeout);
        $contents = "";
        if ($handle = @fopen($URL, "rb")) {
            ini_set("default_socket_timeout", $old);
            stream_set_timeout($handle, $timeout);
            stream_set_blocking($handle, 0);
            while (!feof($handle)) {
                $contents .= fread($handle, 8192);
            }
            fclose($handle);
            $data = $contents;
            unset($contents);
        }
    }
    return $data ? $cleantext ? ts_clean_string($data) : $data : false;
}

?>