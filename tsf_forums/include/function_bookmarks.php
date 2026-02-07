<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function show_bookmarks($url, $title)
{
    global $lang;
    global $f_EnableBookmarks;
    global $BASEURL;
    if ($f_EnableBookmarks != "yes") {
        return "";
    }
    $url = urlencode($url);
    $title = urlencode(htmlspecialchars($title));
    return "\r\n\t<!-- social bookmarking links -->\r\n\t<br /><br />\r\n\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">" . ts_collapse("social_bookmarks") . $lang->tsf_forums["bookmarks"] . "</td>\r\n\t\t</tr>\r\n\t\t" . ts_collapse("social_bookmarks", 2) . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t<a $rel = \"nofollow\" $href = \"http://digg.com/submit?$phrase = 2&amp;$url = " . $url . "&amp;$title = " . $title . "\" $target = \"socialbookmark\"><img $src = \"" . $BASEURL . "/tsf_forums/images/bookmarks/bookmarksite_digg.gif\" $border = \"0\" class=\"inlineimg\" /></a>\r\n\t\t\t\t\t\t\t\t<a $rel = \"nofollow\" $href = \"http://digg.com/submit?$phrase = 2&amp;$url = " . $url . "&amp;$title = " . $title . "\" $target = \"socialbookmark\" $style = \"text-decoration:none\">Digg</a>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t<a $rel = \"nofollow\" $href = \"http://del.icio.us/post?$url = " . $url . "&amp;$title = " . $title . "\" $target = \"socialbookmark\"><img $src = \"" . $BASEURL . "/tsf_forums/images/bookmarks/bookmarksite_delicious.gif\" $border = \"0\" class=\"inlineimg\" /></a>\r\n\t\t\t\t\t\t\t\t<a $rel = \"nofollow\" $href = \"http://del.icio.us/post?$url = " . $url . "&amp;$title = " . $title . "\" $target = \"socialbookmark\" $style = \"text-decoration:none\">del.icio.us</a>\r\n\t\t\t\t\t\t\t</td class=\"none\">\r\n\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t<a $rel = \"nofollow\" $href = \"http://www.stumbleupon.com/submit?$url = " . $url . "&amp;$title = " . $title . "\" $target = \"socialbookmark\"><img $src = \"" . $BASEURL . "/tsf_forums/images/bookmarks/bookmarksite_stumbleupon.gif\" $border = \"0\" class=\"inlineimg\" /></a>\r\n\t\t\t\t\t\t\t\t<a $rel = \"nofollow\" $href = \"http://www.stumbleupon.com/submit?$url = " . $url . "&amp;$title = " . $title . "\" $target = \"socialbookmark\" $style = \"text-decoration:none\">StumbleUpon</a>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t<a $rel = \"nofollow\" $href = \"http://www.google.com/bookmarks/mark?$op = edit&amp;$output = popup&amp;$bkmk = " . $url . "&amp;$title = " . $title . "\" $target = \"socialbookmark\"><img $src = \"" . $BASEURL . "/tsf_forums/images/bookmarks/bookmarksite_google.gif\" $border = \"0\" class=\"inlineimg\" /></a>\r\n\t\t\t\t\t\t\t\t<a $rel = \"nofollow\" $href = \"http://www.google.com/bookmarks/mark?$op = edit&amp;$output = popup&amp;$bkmk = " . $url . "&amp;$title = " . $title . "\" $target = \"socialbookmark\" $style = \"text-decoration:none\">Google</a>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t<br />\r\n\t<!-- / social bookmarking links -->\r\n\t";
}

?>