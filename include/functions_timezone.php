<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$lang->load("timezone");
function fetch_timezone($offset = "all")
{
    $timezones = ["4294967284" => "timezone_gmt_minus_1200", "4294967285" => "timezone_gmt_minus_1100", "4294967286" => "timezone_gmt_minus_1000", "4294967287" => "timezone_gmt_minus_0900", "4294967288" => "timezone_gmt_minus_0800", "4294967289" => "timezone_gmt_minus_0700", "4294967290" => "timezone_gmt_minus_0600", "4294967291" => "timezone_gmt_minus_0500", "-4.5" => "timezone_gmt_minus_0430", "4294967292" => "timezone_gmt_minus_0400", "-3.5" => "timezone_gmt_minus_0330", "4294967293" => "timezone_gmt_minus_0300", "4294967294" => "timezone_gmt_minus_0200", "4294967295" => "timezone_gmt_minus_0100", "0" => "timezone_gmt_plus_0000", "1" => "timezone_gmt_plus_0100", "2" => "timezone_gmt_plus_0200", "3" => "timezone_gmt_plus_0300", "3.5" => "timezone_gmt_plus_0330", "4" => "timezone_gmt_plus_0400", "4.5" => "timezone_gmt_plus_0430", "5" => "timezone_gmt_plus_0500", "5.5" => "timezone_gmt_plus_0530", "5.75" => "timezone_gmt_plus_0545", "6" => "timezone_gmt_plus_0600", "6.5" => "timezone_gmt_plus_0630", "7" => "timezone_gmt_plus_0700", "8" => "timezone_gmt_plus_0800", "9" => "timezone_gmt_plus_0900", "9.5" => "timezone_gmt_plus_0930", "10" => "timezone_gmt_plus_1000", "11" => "timezone_gmt_plus_1100", "12" => "timezone_gmt_plus_1200"];
    return $offset == "all" ? $timezones : $timezones[(string) $offset];
}
function show_timezone($tzoffset = 0, $autodst = 0, $dst = 0)
{
    global $lang;
    $timezoneoptions = "";
    foreach (fetch_timezone() as $optionvalue => $timezonephrase) {
        $timezoneoptions .= "<option $value = \"" . $optionvalue . "\"" . ($tzoffset == $optionvalue ? " $selected = \"selected\"" : "") . ">" . $lang->timezone[(string) $timezonephrase] . "</option>";
    }
    $selectdst = [];
    if ($autodst) {
        $selectdst[2] = " $selected = \"selected\"";
    } else {
        if ($dst) {
            $selectdst[1] = " $selected = \"selected\"";
        } else {
            $selectdst[0] = " $selected = \"selected\"";
        }
    }
    return "\r\n\t<fieldset class=\"fieldset\">\r\n\t\t<legend><label for=\"sel_tzoffset\">" . $lang->timezone["time_zone"] . "</label></legend>\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\">" . $lang->timezone["time_auto_corrected_to_location"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<span $style = \"float:right\">\r\n\t\t\t\t<select $name = \"tzoffset\" $id = \"sel_tzoffset\">\r\n\t\t\t\t\t" . $timezoneoptions . "\r\n\t\t\t\t</select>\r\n\t\t\t\t</span>\r\n\t\t\t\t<label for=\"sel_tzoffset\"><b>" . $lang->timezone["time_zone"] . ":</b></label>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\">" . $lang->timezone["allow_daylight_savings_time"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\">\r\n\t\t\t\t<span $style = \"float:right\">\r\n\t\t\t\t<select $name = \"dst\" $id = \"sel_dst\">\r\n\t\t\t\t\t<option $value = \"2\"" . (isset($selectdst[2]) ? $selectdst[2] : "") . ">" . $lang->timezone["dstauto"] . "</option>\r\n\t\t\t\t\t<option $value = \"1\"" . (isset($selectdst[1]) ? $selectdst[1] : "") . ">" . $lang->timezone["dston"] . "</option>\r\n\t\t\t\t\t<option $value = \"0\"" . (isset($selectdst[0]) ? $selectdst[0] : "") . ">" . $lang->timezone["dstoff"] . "</option>\r\n\t\t\t\t</select>\r\n\t\t\t\t</span>\r\n\t\t\t\t<label for=\"sel_dst\"><b>" . $lang->timezone["dst_correction_option"] . ":</b></label>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t</fieldset>\r\n\t";
}

?>