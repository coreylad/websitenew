<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
define("Q_EDITOR", "v0.6 by xam");
function ts_show_bbcode_links($TSformname = "quickreply", $TStextareaname = "message")
{
    global $BASEURL;
    global $pic_base_url;
    global $lang;
    $lang->load("quick_editor");
    $_links_ = "\r\n\t<a $href = \"javascript:insert('[b]', '[/b]', '" . $TSformname . "', '" . $TStextareaname . "');\"><img $src = \"" . $pic_base_url . "codebuttons/bold.gif\" $border = \"0\" $alt = \"" . $lang->quick_editor["bold"] . "\" $title = \"" . $lang->quick_editor["bold"] . "\" /></a>\r\n\t<a $href = \"javascript:insert('[i]', '[/i]', '" . $TSformname . "', '" . $TStextareaname . "');\"><img $src = \"" . $pic_base_url . "codebuttons/italic.gif\" $border = \"0\" $alt = \"" . $lang->quick_editor["italic"] . "\" $title = \"" . $lang->quick_editor["italic"] . "\" /></a>\r\n\t<a $href = \"javascript:insert('[u]', '[/u]', '" . $TSformname . "', '" . $TStextareaname . "');\"><img $src = \"" . $pic_base_url . "codebuttons/underline.gif\" $border = \"0\" $alt = \"" . $lang->quick_editor["underline"] . "\" $title = \"" . $lang->quick_editor["underline"] . "\" /></a>\r\n\t<a $href = \"javascript:insert('[url]', '[/url]', '" . $TSformname . "', '" . $TStextareaname . "');\"><img $src = \"" . $pic_base_url . "codebuttons/link.gif\" $border = \"0\" $alt = \"" . $lang->quick_editor["link"] . "\" $title = \"" . $lang->quick_editor["link"] . "\" /></a>\r\n\t<a $href = \"javascript:insert('[img]', '[/img]', '" . $TSformname . "', '" . $TStextareaname . "');\"><img $src = \"" . $pic_base_url . "codebuttons/image.gif\" $border = \"0\" $alt = \"" . $lang->quick_editor["image"] . "\" $title = \"" . $lang->quick_editor["image"] . "\" /></a>\r\n\t<a $href = \"javascript:insert('[email]', '[/email]', '" . $TSformname . "', '" . $TStextareaname . "');\"><img $src = \"" . $pic_base_url . "codebuttons/email.gif\" $border = \"0\" $alt = \"" . $lang->quick_editor["email"] . "\" $title = \"" . $lang->quick_editor["email"] . "\" /></a>\r\n\t<a $href = \"javascript:insert('[quote]', '[/quote]', '" . $TSformname . "', '" . $TStextareaname . "');\"><img $src = \"" . $pic_base_url . "codebuttons/quote.gif\" $border = \"0\" $alt = \"" . $lang->quick_editor["quote"] . "\" $title = \"" . $lang->quick_editor["quote"] . "\" /></a>\r\n\t<a $href = \"javascript:insert('[code]', '[/code]', '" . $TSformname . "', '" . $TStextareaname . "');\"><img $src = \"" . $pic_base_url . "codebuttons/code.gif\" $border = \"0\" $alt = \"" . $lang->quick_editor["code"] . "\" $title = \"" . $lang->quick_editor["code"] . "\" /></a>";
    return $_links_;
}
function ts_show_shoutbox_bbcode_links($TSformname = "shoutbox", $TStextareaname = "shoutbox")
{
    $colors = ["black" => "#000000", "blue" => "#1818A0", "green" => "#00FF00", "orange" => "#FF8040", "pink" => "#FF00FF", "red" => "#FF0000", "yellow" => "#FFFF00"];
    $_links_ = "\r\n\t<input $type = \"button\" class=\"button\" $style = \"font-weight:bold\" $value = \"B\" $onClick = \"insert('[b]', '[/b]', '" . $TSformname . "', '" . $TStextareaname . "');\" />\r\n\t<input $type = \"button\" class=\"button\" $style = \"font-style:italic\" $value = \"I\" $onClick = \"insert('[i]', '[/i]', '" . $TSformname . "', '" . $TStextareaname . "');\" />\r\n\t<input $type = \"button\" class=\"button\" $style = \"text-decoration:underline\" $value = \"U\" $onClick = \"insert('[u]', '[/u]', '" . $TSformname . "', '" . $TStextareaname . "');\" />\r\n\t";
    return $_links_;
}
function ts_load_colors_shoutbox($TSformname = "shoutbox", $TStextareaname = "shoutbox", $colors = ["black" => "#000000", "blue" => "#1818A0", "green" => "#00FF00", "orange" => "#FF8040", "pink" => "#FF00FF", "red" => "#FF0000", "yellow" => "#FFFF00"])
{
    global $lang;
    global $BASEURL;
    global $pic_base_url;
    $showcolors = "\r\n\t<div $style = \"display: none; margin-right: 13px; margin-bottom: 3px;\" $id = \"show_TScolors\">\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $align = \"right\">";
    foreach ($colors as $colorname => $colorcode) {
        $showcolors .= "<img $src = \"" . $pic_base_url . "codebuttons/" . $colorname . ".gif\" class=\"Shighlightit\" $onClick = \"insert('[$color = " . $colorcode . "]', '[/color]', '" . $TSformname . "', '" . $TStextareaname . "')\" />";
    }
    $showcolors .= "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t</div>";
    return $showcolors;
}

?>