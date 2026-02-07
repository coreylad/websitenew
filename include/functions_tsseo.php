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
function Slug($string)
{
    return strtolower(trim(preg_replace("~[^0-9a-z]+~i", "-", html_entity_decode(preg_replace("~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i", "\$1", htmlentities($string, ENT_QUOTES, "UTF-8")), ENT_QUOTES, "UTF-8")), "-"));
}
function MakeFriendlyText($text, $delimer = "-")
{
    $text = str_replace(["&amp;", "&quot;", "&#039;", "&lt;", "&gt;"], ["&", "\"", "'", "<", ">"], $text);
    $text = strip_tags($text);
    return slug($text);
}
function ts_seo($id, $text, $type = "u")
{
    global $BASEURL;
    global $ts_seo;
    $id = intval($id);
    if ($ts_seo == "yes") {
        $text = makefriendlytext($text);
        switch ($type) {
            case "c":
                return $BASEURL . "/torrent-category-" . $id . "/" . $text;
                break;
            case "d":
                return $BASEURL . "/download-torrent-" . $id . "/" . $text;
                break;
            case "ssl":
                return $BASEURL . "/download-torrent-ssl-" . $id . "/" . $text;
                break;
            case "s":
                return $BASEURL . "/torrent-details-" . $id . "/" . $text;
                break;
            case "u":
                return $BASEURL . "/user-profile-" . $id . "/" . $text;
                break;
        }
    } else {
        if ($type == "c") {
            return $BASEURL . "/browse.php?category=" . $id;
        }
        if ($type == "d") {
            return $BASEURL . "/download.php?id=" . $id;
        }
        if ($type == "ssl") {
            return $BASEURL . "/download.php?id=" . $id . "&type=ssl";
        }
        if ($type == "s") {
            return $BASEURL . "/details.php?id=" . $id;
        }
        if ($type == "u") {
            return $BASEURL . "/userdetails.php?id=" . $id;
        }
    }
}
function tsf_seo_clean_text($text = "", $type = "", $id = 0, $extra = "")
{
    global $BASEURL;
    global $ts_seo;
    $id = intval($id);
    if ($ts_seo == "yes") {
        $text = makefriendlytext($text);
        $extra = str_replace("?", "&", $extra);
        switch ($type) {
            case "f":
                return $BASEURL . "/forum-" . $id . "/" . $text . $extra;
                break;
            case "fd":
                return $BASEURL . "/threads-" . $id . "/" . $text . $extra;
                break;
            case "t":
                return $BASEURL . "/thread-" . $id . "/" . $text . $extra;
                break;
            case "lastpost":
                return $BASEURL . "/thread-" . $id . "/" . $text . "&action=lastpost";
                break;
            case "page":
                return $BASEURL . "/thread-" . $id . "/" . $text . "&page=" . intval($extra);
                break;
            case "u":
                return $BASEURL . "/user-profile-" . $id . "/" . $text;
                break;
        }
    } else {
        switch ($type) {
            case "f":
                return $BASEURL . "/tsf_forums/index.php?fid=" . $id . $extra;
                break;
            case "fd":
                return $BASEURL . "/tsf_forums/forumdisplay.php?fid=" . $id . $extra;
                break;
            case "t":
                return $BASEURL . "/tsf_forums/showthread.php?tid=" . $id . $extra;
                break;
            case "lastpost":
                return $BASEURL . "/tsf_forums/showthread.php?tid=" . $id . "&action=lastpost";
                break;
            case "page":
                return $BASEURL . "/tsf_forums/showthread.php?tid=" . $id . "&page=" . intval($extra);
                break;
            case "u":
                return $BASEURL . "/userdetails.php?id=" . $id . $extra;
                break;
        }
    }
}

?>