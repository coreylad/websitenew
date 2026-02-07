<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function cache_check($file = "cachefile")
{
    global $cache;
    global $cachesystem;
    global $cachetime;
    global $lang;
    global $dateformat;
    global $timeformat;
    if ($cachesystem == "yes") {
        $cachefile = TSDIR . "/" . $cache . "/" . $file . ".html";
        $cachetimee = 60 * $cachetime;
        clearstatcache();
        if (file_exists($cachefile) && TIMENOW - $cachetimee < filemtime($cachefile)) {
            include_once $cachefile;
            $filetime = filemtime($cachefile);
            echo "<br />" . show_notice(sprintf($lang->global["cachedmessage"], my_datee($dateformat, $filetime) . " " . my_datee($timeformat, $filetime), $cachetime));
            echo "</td></tr></table>\n";
            stdfoot();
            exit;
        }
        ob_start();
    }
}
function cache_save($file = "cachefile")
{
    global $cache;
    global $cachesystem;
    if ($cachesystem == "yes") {
        file_put_contents(TSDIR . "/" . $cache . "/" . $file . ".html", ob_get_contents());
        ob_end_flush();
    }
}

?>