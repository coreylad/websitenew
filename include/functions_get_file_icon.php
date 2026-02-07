<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function get_file_icon($filename, $path = "images/attach/")
{
    global $BASEURL;
    if ($path == "images/attach/") {
        $path = $BASEURL . "/tsf_forums/" . $path;
    }
    $ext = get_extension($filename);
    return "<img src=\"" . $path . (file_exists($path . $ext . ".gif") ? $ext : "attach") . ".gif\" border=\"0\" class=\"inlineimg\" alt=\"" . $ext . "\" title=\"" . $ext . "\">&nbsp;";
}

?>