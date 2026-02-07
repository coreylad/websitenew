<?php
/*

Decoded by Devil Team

 */

define("TS_IMDB_VERSION", "1.3.0 by xam");
if (!defined("IN_TRACKER")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
require_once INC_PATH . "/functions_ts_remote_connect.php";
header("Content-type: text/html; charset=utf-8");
$t_link = str_replace("http://", "https://", $t_link);
$regex = "#https://www.imdb.com/title/(.*)/#U";
preg_match($regex, $t_link, $_id_);
$_id_ = $_id_[1];
$url = "https://www.imdb.com/title/" . $_id_ . "/";
$text = TS_Fetch_Data($url);
$text = iconv("UTF-8", "ASCII//TRANSLIT", $text);
preg_match_all("~<div class=\"poster\">.*?<a.*?<img.*?src=\"(.*?)\".*?><\\/a><\\/div>~isu", $text, $poster);
if ($poster && isset($poster[1][0]) && $poster[1][0]) {
    $poster = $poster[1][0];
    $cover_phone_name = $torrent_dir . "/images/" . $_id_ . ".jpg";
    file_put_contents($cover_phone_name, TS_Fetch_Data($poster, false));
} else {
    $poster = "";
}
preg_match_all("~<div class=\"title_wrapper\">.*?<h1.*?>(.*?)<\\/h1>~isu", $text, $title);
if ($title && isset($title[1][0]) && $title[1][0]) {
    $title = strip_tags($title[1][0]);
}
preg_match_all("~<div class=\"credit_summary_item\">.*?<h4.*?>Director.*?:<\\/h4>(.*?)<\\/div>~isu", $text, $directors);
if ($directors && isset($directors[1][0]) && $directors[1][0]) {
    $htmlDom = new DOMDocument();
    $htmlDom->loadHTML($directors[1][0]);
    $links = $htmlDom->getElementsByTagName("a");
    $data = [];
    foreach ($links as $link) {
        if (!in_array($link->nodeValue, $data)) {
            $data[] = $link->nodeValue;
        }
    }
    $directors = implode(", ", $data);
} else {
    $directors = "";
}
preg_match_all("~<div class=\"credit_summary_item\">.*?<h4.*?>Writer.*?:<\\/h4>(.*?)<\\/div>~isu", $text, $writers);
if ($writers && isset($writers[1][0]) && $writers[1][0]) {
    $htmlDom = new DOMDocument();
    $htmlDom->loadHTML($writers[1][0]);
    $links = $htmlDom->getElementsByTagName("a");
    $data = [];
    foreach ($links as $link) {
        if (!preg_match("@more credit@", $link->nodeValue) && !in_array($link->nodeValue, $data)) {
            $data[] = $link->nodeValue;
        }
    }
    $writers = implode(", ", $data);
} else {
    $writers = "";
}
preg_match_all("~<div class=\"see-more.*?\">.*?<h4.*?>Genre.*?:<\\/h4>(.*?)<\\/div>~isu", $text, $genres);
if ($genres && isset($genres[1][0]) && $genres[1][0]) {
    $htmlDom = new DOMDocument();
    $htmlDom->loadHTML($genres[1][0]);
    $links = $htmlDom->getElementsByTagName("a");
    $data = [];
    foreach ($links as $link) {
        if (!preg_match("@See full@", $link->nodeValue) && !in_array($link->nodeValue, $data)) {
            $data[] = $link->nodeValue;
        }
    }
    $genres = implode(", ", $data);
} else {
    $genres = "";
}
preg_match_all("~<h4.*?>Release Date:<\\/h4>(.*?)<span~isu", $text, $releaseDate);
if ($releaseDate && isset($releaseDate[1][0]) && $releaseDate[1][0]) {
    $releaseDate = strip_tags($releaseDate[1][0]);
}
preg_match_all("~<div class=\"ratingValue\">(.*?)<\\/div>~isu", $text, $ratingValue);
if ($ratingValue && isset($ratingValue[1][0]) && $ratingValue[1][0]) {
    $ratingValue = strip_tags($ratingValue[1][0]);
}
preg_match_all("~<span.*?\"ratingCount\".*?>(.*?)<\\/span>~isu", $text, $ratingCount);
if ($ratingCount && isset($ratingCount[1][0]) && $ratingCount[1][0]) {
    $ratingCount = strip_tags($ratingCount[1][0]);
}
preg_match_all("~<h4.*?>.*?<h4.*?>Language:<\\/h4>(.*?)<\\/div>~isu", $text, $language);
if ($language && isset($language[1][0]) && $language[1][0]) {
    $htmlDom = new DOMDocument();
    $htmlDom->loadHTML($language[1][0]);
    $links = $htmlDom->getElementsByTagName("a");
    $data = [];
    foreach ($links as $link) {
        if (!preg_match("@See full@", $link->nodeValue) && !in_array($link->nodeValue, $data)) {
            $data[] = $link->nodeValue;
        }
    }
    $language = implode(", ", $data);
} else {
    $language = "";
}
preg_match_all("~<h4.*?>.*?<h4.*?>Country:<\\/h4>(.*?)<\\/div>~isu", $text, $country);
if ($country && isset($country[1][0]) && $country[1][0]) {
    $htmlDom = new DOMDocument();
    $htmlDom->loadHTML($country[1][0]);
    $links = $htmlDom->getElementsByTagName("a");
    $data = [];
    foreach ($links as $link) {
        if (!preg_match("@See full@", $link->nodeValue) && !in_array($link->nodeValue, $data)) {
            $data[] = $link->nodeValue;
        }
    }
    $country = implode(", ", $data);
} else {
    $country = "";
}
preg_match_all("~<h4.*?>Runtime:<\\/h4>(.*?)<\\/div>~isu", $text, $runtime);
if ($runtime && isset($runtime[1][0]) && $runtime[1][0]) {
    $runtime = strip_tags($runtime[1][0]);
}
preg_match_all("~<h2>Storyline</h2>.*<div class=\"inline canwrap\">(.*?)<\\/div>~isu", $text, $storyline);
if ($storyline && isset($storyline[1][0]) && $storyline[1][0]) {
    $storyline = strip_tags($storyline[1][0]);
}
preg_match_all("~<div class=\"mediastrip\">(.*?)<\\/div>~isu", $text, $mediastrip);
if ($mediastrip && isset($mediastrip[1][0]) && $mediastrip[1][0]) {
    $htmlDom = new DOMDocument();
    $htmlDom->loadHTML($mediastrip[1][0]);
    $links = $htmlDom->getElementsByTagName("img");
    $photourls = [];
    foreach ($links as $link) {
        if (!in_array($link->getAttribute("loadlate"), $data)) {
            $photourls[] = $link->getAttribute("loadlate");
        }
    }
}
$extra = "";
$photoarray = [];
if (isset($photourls) && 0 < count($photourls)) {
    $pid = 1;
    foreach ($photourls as $photo) {
        $ext = get_extension($photo);
        $photoname = $torrent_dir . "/images/" . $_id_ . "_photo" . $pid . "." . $ext;
        $pid++;
        if (file_exists($photoname)) {
            @unlink($photoname);
        }
        if (file_put_contents($photoname, TS_Fetch_Data($photo, false)) !== false) {
            $photoarray[] = $BASEURL . "/" . $photoname;
        }
    }
    if ($photoarray && count($photoarray)) {
        $extra = "<br /><br /><div align=\"center\">";
        foreach ($photoarray as $photo) {
            $extra .= " <img src=\"" . $photo . "\" alt=\"\" title=\"\" border=\"0\" /> ";
        }
        $extra .= "</div>";
    }
}
preg_match_all("~<div class=\"credit_summary_item\">.*?<h4.*?>Star.*?:<\\/h4>(.*?)<\\/div>~isu", $text, $stars);
if ($stars && isset($stars[1][0]) && $stars[1][0]) {
    $htmlDom = new DOMDocument();
    @$htmlDom->loadHTML($stars[1][0]);
    $links = $htmlDom->getElementsByTagName("a");
    $data = [];
    foreach ($links as $link) {
        if (!preg_match("@See full@", $link->nodeValue) && !in_array($link->nodeValue, $data)) {
            $data[] = $link->nodeValue;
        }
    }
    $stars = implode(", ", $data);
}
$t_link = "<table width='100%' border='0' align='center' class='none'><tr><td colspan='2' class='none' align='left'><span style='float: right;'><a href='" . $t_link . "' target='_blank' alt='" . $title . "' title='" . $title . "'>IMDB Link</a></span><b>" . $title . "</b></td></tr><tr><td class='none' align='center' valign='top'>" . ($poster ? "<img src='" . $poster . "' border='0' alt='" . $title . "' title='" . $title . "'>" : "") . "</td><td class='none' valign='top' align='left'>" . ($directors ? "<b>Director(s):</b> " . $directors . "<br />" : "") . ($writers ? "<b>Writer(s):</b> " . $writers . "<br />" : "") . "<b>Stars:</b> " . $stars . "<br /><b>Genre(s):</b> " . $genres . "<br />" . ($releaseDate && !is_array($releaseDate) ? "<b>Release Date:</b> " . $releaseDate . "<br />" : "") . ($ratingValue && !is_array($ratingValue) ? "<b>User Rating:</b> " . $ratingValue . " (" . ($ratingCount ? $ratingCount : "awaiting 5") . " votes)<br />" : "") . "<b>Language:</b> " . $language . "<br /><b>Country:</b> " . $country . "<br />" . ($runtime && !is_array($runtime) ? "<b>Runtime:</b> " . $runtime . "<br />" : "") . "<b>Storyline:</b> " . $storyline . $extra . "</td></tr></table>";
function match_all($regex, $str, $i = 0)
{
    if (preg_match_all($regex, $str, $matches) === false) {
        return false;
    }
    return $matches[$i];
}
function get_match($regex, $content)
{
    preg_match($regex, $content, $matches);
    return isset($matches[1]) ? trim($matches[1]) : false;
}
function match($regex, $str, $i = 0)
{
    if (preg_match($regex, $str, $match) == 1) {
        return $match[$i];
    }
    return false;
}

?>