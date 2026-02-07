<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("EDITOR_VERSION", "v.2.0");
function TinyMceEditorIndex($type = 2, $mode = "exact", $elements = "message")
{
    global $rootpath;
    global $BASEURL;
    global $staffcp_path;
    global $pic_base_url;
    $INFORUMS = strpos($_SERVER["SCRIPT_NAME"], "tsf_forums") === false ? false : true;
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", $BASEURL . "/scripts/");
    define("TINYMCE_EMOTIONS_URL", $BASEURL . "/tinymce_emotions.php");
    define("NO_JQUERY", true);
    define("TINYMCE_STYLE", "./" . ($INFORUMS ? "../" : "") . $staffcp_path . "/style/bbcode.css");
    define("PIC_BASEURL", $pic_base_url);
    ob_start();
    include $rootpath . "/tinymce.php";
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}
function insert_editor($subject = true, $subjectvalue = "", $textarevalue = "", $head1 = "", $head2 = "", $postoptionstitle = "", $postoptions = "", $preview = true, $extrasubject = "", $buttonname = "", $javascript = "", $textareasubject = "", $FirstTabs = [])
{
    global $lang;
    $StartContent = "\r\n\t<!-- begin editor -->\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $valign = \"top\">\r\n\t\t\t\t<strong>" . $head1 . "</strong>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $valign = \"top\">\r\n\t\t\t\t<span class=\"smalltext\"><strong>" . $head2 . "</strong></span>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    $SubjectContent = "";
    if ($subject) {
        $SubjectContent = "\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<strong>" . $lang->global["subject"] . "</strong>\r\n\t\t\t\t<br />\r\n\t\t\t\t<input $type = \"text\" $name = \"subject\" $style = \"width: 100%\" $value = \"" . (!empty($subjectvalue) ? htmlspecialchars_uni($subjectvalue) : "") . "\" $tabindex = \"1\"" . (defined("SUBJECT_EXTRA") ? SUBJECT_EXTRA : "") . " />\r\n\t\t\t</td>\r\n\t\t</tr>";
    }
    if (!empty($extrasubject)) {
        foreach ($extrasubject as $left => $right) {
            $SubjectContent .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<strong>" . $left . "</strong>\r\n\t\t\t\t\t<br />\r\n\t\t\t\t\t" . $right . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>";
        }
    }
    $TextareaContent = "\r\n\t<tr>\r\n\t\t<td>\r\n\t\t\t<textarea $id = \"message\" $name = \"message\" $style = \"width: 100%; height: 305px; visibility: hidden;\" $tabindex = \"2\">" . (!empty($textarevalue) ? $textarevalue : "") . "</textarea>\r\n\t\t</td>\r\n\t</tr>";
    $PostContent = "";
    if (!empty($postoptions)) {
        foreach ($postoptions as $p => $v) {
            if (!empty($p) && !empty($v)) {
                $PostContent .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<strong>" . $postoptionstitle[$p] . "</strong>\r\n\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t" . $v . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>";
            }
        }
    }
    $ButtonContent = "\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t<input $type = \"submit\" $name = \"submit\" $value = \"" . (empty($buttonname) ? $lang->global["buttonsave"] : $buttonname) . "\" $tabindex = \"3\" $accesskey = \"s\"" . $javascript . " /> " . ($preview ? "<input $type = \"submit\" $name = \"previewpost\" $value = \"" . $lang->global["buttonpreview"] . "\" $tabindex = \"4\" />" : "") . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    $EndContent = "\r\n\t\t</tr>\r\n\t</table>";
    $ScriptContent = tinymceeditorindex() . "<!-- end editor -->";
    $FirstContent = "";
    if (count($FirstTabs)) {
        foreach ($FirstTabs as $p => $v) {
            if (!empty($p) && !empty($v)) {
                $FirstContent .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<strong>" . $p . "</strong>\r\n\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t" . $v . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>";
            }
        }
    }
    return $StartContent . $FirstContent . $SubjectContent . $TextareaContent . $PostContent . $ButtonContent . $EndContent . $ScriptContent;
}

?>