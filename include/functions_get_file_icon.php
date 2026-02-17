<?php

declare(strict_types=1);

function get_file_icon($filename, $path = "images/attach/")
{
    global $BASEURL;
    if ($path == "images/attach/") {
        $path = $BASEURL . "/tsf_forums/" . $path;
    }
    $ext = get_extension($filename);
    return "<img $src = \"" . $path . (file_exists($path . $ext . ".gif") ? $ext : "attach") . ".gif\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $ext . "\" $title = \"" . $ext . "\">&nbsp;";
}

?>