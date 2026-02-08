<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$TSSEConfig->TSLoadConfig("CLEANUP", 0);
function get_warn_level($warn_level = 0)
{
    global $lang;
    global $CLEANUP;
    global $pic_base_url;
    $warn_percent = $CLEANUP["ban_user_limit"] ? $warn_level ? @round($warn_level / $CLEANUP["ban_user_limit"] * 100) : 0 : 0;
    $image = $pic_base_url . "/warn/warn0.gif";
    if (0 < $warn_percent && $warn_percent < 20) {
        $image = $pic_base_url . "/warn/warn1.gif";
    }
    if (20 < $warn_percent && $warn_percent < 40) {
        $image = $pic_base_url . "/warn/warn2.gif";
    }
    if (40 < $warn_percent && $warn_percent < 60) {
        $image = $pic_base_url . "/warn/warn3.gif";
    }
    if (60 < $warn_percent && $warn_percent < 80) {
        $image = $pic_base_url . "/warn/warn4.gif";
    }
    if (80 < $warn_percent) {
        $image = $pic_base_url . "/warn/warn5.gif";
    }
    return "\r\n\t\t<div $align = \"center\"style=\"clear: both; padding-top: 15px; padding-bottom: 15px; white-space: nowrap;\">\r\n\t\t\t<img $src = \"" . $image . "\" $border = \"0\" $alt = \"" . $lang->global["imgwarned"] . ": " . $warn_percent . "%\" $title = \"" . $lang->global["imgwarned"] . ": " . $warn_percent . "%\" />\r\n\t\t</div>";
}

?>