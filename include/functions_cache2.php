<?php

declare(strict_types=1);

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function clear_cache($file)
{
    global $cache;
    $cachefile = TSDIR . "/" . $cache . "/" . $file . ".html";
    if (file_exists($cachefile)) {
        @unlink($cachefile);
    }
}
function cache_check2($file)
{
    global $lang;
    global $cache;
    global $cachesystem;
    global $cachetime;
    global $lang;
    global $dateformat;
    global $timeformat;
    if ($cachesystem == "yes") {
        clearstatcache();
        $cachefile = TSDIR . "/" . $cache . "/" . $file . ".html";
        if (file_exists($cachefile) && ($filetime = filemtime($cachefile)) && TIMENOW - 60 * $cachetime < $filetime) {
            return (!defined("SKIP_CACHE_MESSAGE") ? show_notice(sprintf($lang->global["cachedmessage"], my_datee($dateformat, $filetime) . " " . my_datee($timeformat, $filetime), $cachetime)) : "") . file_get_contents($cachefile);
        }
    }
    return false;
}
function cache_save2($file, $contents, $extra = "", $forceupdate = false)
{
    global $cache;
    global $cachesystem;
    global $cachetime;
    global $dateformat;
    global $timeformat;
    clearstatcache();
    $cachefile = TSDIR . "/" . $cache . "/" . $file . ".html";
    if (defined("FORCE_UPDATE") || $forceupdate || $cachesystem == "yes" && (!file_exists($cachefile) || filemtime($cachefile) < TIMENOW - 60 * $cachetime)) {
        file_put_contents($cachefile, $contents . $extra);
    }
}

?>