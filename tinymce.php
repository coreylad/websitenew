<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("WORKPATH")) {
    exit("Work Path is not defined!");
}
if (!defined("EDITOR_TYPE")) {
    exit("Editor type is not defined!");
}
if (!defined("TINYMCE_MODE")) {
    exit("Mode is not defined!");
}
if (!defined("TINYMCE_ELEMENTS")) {
    exit("Element is not defined!");
}
if (!defined("TINYMCE_EMOTIONS_URL")) {
    exit("Emotions is not defined!");
}
$INFORUMS = strpos($_SERVER["SCRIPT_NAME"], "tsf_forums") === false ? false : true;
if (!defined("TINYMCE_STYLE")) {
    define("TINYMCE_STYLE", "style/bbcode.css");
}
$TINYMCEOUTPUT = "<script type=\"text/javascript\" src=\"" . WORKPATH . "tinymce/tiny_mce.js\"></script>";
if (defined("PIC_BASEURL")) {
    $CodeButtons = PIC_BASEURL . "codebuttons/";
}
switch (EDITOR_TYPE) {
    case 1:
        $TINYMCEOUTPUT .= "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\tvar spopupurl = \"" . TINYMCE_EMOTIONS_URL . "\";\r\n\t\t\tvar spopupwidth = 600;\r\n\t\t\tvar spopupheight = 400;\r\n\r\n\t\t\ttinyMCE.init\r\n\t\t\t(\r\n\t\t\t\t{\r\n\t\t\t\t\tmode : \"" . TINYMCE_MODE . "\",\r\n\t\t\t\t\telements : \"" . TINYMCE_ELEMENTS . "\",\r\n\t\t\t\t\ttheme : \"advanced\",\r\n\t\t\t\t\tskin : \"default\",\r\n\t\t\t\t\ttheme_advanced_layout_manager : \"SimpleLayout\",\r\n\t\t\t\t\tdialog_type : \"modal\",\r\n\r\n\t\t\t\t\tplugins : \"safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template\",\r\n\t\t\t\t\ttheme_advanced_buttons1 : \"formatselect,fontselect,fontsizeselect,|,forecolor,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,link,unlink,image,|,undo,redo,|,code\",\r\n\t\t\t\t\ttheme_advanced_buttons2 : \"\",\r\n\t\t\t\t\ttheme_advanced_buttons3 : \"\",\r\n\t\t\t\t\ttheme_advanced_toolbar_location : \"top\",\r\n\t\t\t\t\ttheme_advanced_toolbar_align : \"left\",\r\n\r\n\t\t\t\t\tinline_styles : false,\r\n\t\t\t\t\trelative_urls : false,\r\n\t\t\t\t\tremove_script_host : false,\r\n\t\t\t\t\tconvert_urls : false\r\n\t\t\t\t}\r\n\t\t\t);\r\n\r\n\t\t\tfunction toggleEditor(id)\r\n\t\t\t{\r\n\t\t\t\tif (!tinyMCE.get(id))\r\n\t\t\t\t\ttinyMCE.execCommand('mceAddControl', false, id);\r\n\t\t\t\telse\r\n\t\t\t\t\ttinyMCE.execCommand('mceRemoveControl', false, id);\r\n\t\t\t}\r\n\t\t</script>";
        break;
    case 2:
        $TINYMCEOUTPUT .= "\r\n\t<script type=\"text/javascript\">\r\n\t\t\tvar spopupurl = \"" . TINYMCE_EMOTIONS_URL . "\";\r\n\t\t\tvar spopupwidth = 600;\r\n\t\t\tvar spopupheight = 500;\r\n\t\t\t//TODO: Add language support via editor.lang.php\r\n\t\t\ttinyMCE.init\r\n\t\t\t({\r\n\t\t\t\tmode : \"" . TINYMCE_MODE . "\",\r\n\t\t\t\telements : \"" . TINYMCE_ELEMENTS . "\",\r\n\t\t\t\ttheme : \"advanced\",\r\n\t\t\t\tplugins : \"bbcode,inlinepopups,emotions,paste\",\r\n\t\t\t\tdialog_type : \"modal\",\r\n\t\t\t\ttheme_advanced_buttons1 : \"removeformat,cleanup,|,undo,redo,|,fontselect,fontsizeselect,forecolor,emotions\",\r\n\t\t\t\ttheme_advanced_buttons2 : \"bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,link,unlink,image,emailbutton" . ($INFORUMS && isset($CodeButtons) ? ",|,hidebutton" : "") . ",|,youtubebutton,vimeobutton,|,phpbutton,codebutton,sqlbutton,|,quotebutton\",\r\n\t\t\t\ttheme_advanced_buttons3 : \"\",\r\n\t\t\t\ttheme_advanced_toolbar_location : \"top\",\r\n\t\t\t\ttheme_advanced_toolbar_align : \"left\",\r\n\r\n\t\t\t\ttheme_advanced_more_colors: 0,\r\n\t\t\t\t\r\n\t\t\t\tentity_encoding : \"raw\",\r\n\t\t\t\tadd_unload_trigger : false,\r\n\t\t\t\tremove_linebreaks : false,\r\n\t\t\t\tinline_styles : false,\r\n\t\t\t\tconvert_fonts_to_spans : false,\r\n\t\t\t\t\r\n\t\t\t\trelative_urls : false,\r\n\t\t\t\tremove_script_host : false,\r\n\t\t\t\tconvert_urls : false,\r\n\r\n\t\t\t\tformats: \r\n\t\t\t\t{\r\n\t\t\t\t\tforecolor : {inline : \"span\", styles : {color : \"%value\"}, wrap_links : false, exact: true},\r\n\t\t\t\t\thilitecolor : {inline : \"span\", styles : {backgroundColor : \"%value\"}, wrap_links : false, exact: true},\r\n\t\t\t\t\tfontname : {inline : \"span\", styles : {fontFamily : \"%value\"}, exact: true},\r\n\t\t\t\t\tfontsize : {inline : \"span\", styles : {fontSize : \"%value\"}, exact: true}\r\n\t\t\t\t},\r\n\r\n\t\t\t\tpaste_auto_cleanup_on_paste : true,\r\n\t\t\t\tpaste_remove_styles: true,\r\n\t\t\t\tpaste_remove_styles_if_webkit: true,\r\n\t\t\t\tpaste_strip_class_attributes: true,\r\n\r\n\t\t\t\tvalid_elements : \r\n\t\t\t\t\"a[href],\"+\r\n\t\t\t\t\"img[src],\"+\r\n\t\t\t\t\"p[style],\"+\r\n\t\t\t\t\"em/i,\"+\r\n\t\t\t\t\"span[style],\"+\r\n\t\t\t\t\"strike/s,\"+\r\n\t\t\t\t\"strong/b,\"+\r\n\t\t\t\t\"br,\"+\r\n\t\t\t\t\"pre,\"+\r\n\t\t\t\t\"ul,\"+\r\n\t\t\t\t\"ol,\"+\r\n\t\t\t\t\"li,\"+\r\n\t\t\t\t\"u\",\r\n\t\t\t\t\r\n\t\t\t\tsetup : function(ed)\r\n\t\t\t\t{\r\n\t\t\t\t\ted.addButton(\"codebutton\",\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttitle : \"CODE\",\r\n\t\t\t\t\t\timage : \"" . $CodeButtons . "code.gif\",\r\n\t\t\t\t\t\tonclick : function()\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\ted.focus();\r\n\t\t\t\t\t\t\ted.selection.setContent('[code]'+ed.selection.getContent()+'[/code]');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\r\n\t\t\t\t\ted.addButton(\"quotebutton\",\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttitle : \"Quote\",\r\n\t\t\t\t\t\timage : \"" . $CodeButtons . "quote.gif\",\r\n\t\t\t\t\t\tonclick : function()\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\ted.focus();\r\n\t\t\t\t\t\t\ted.selection.setContent('[quote]'+ed.selection.getContent()+'[/quote]');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\r\n\t\t\t\t\ted.addButton(\"phpbutton\",\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttitle : \"PHP\",\r\n\t\t\t\t\t\timage : \"" . $CodeButtons . "php.gif\",\r\n\t\t\t\t\t\tonclick : function()\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\ted.focus();\r\n\t\t\t\t\t\t\ted.selection.setContent('[php]'+ed.selection.getContent()+'[/php]');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\t\t\t\t\t\r\n\t\t\t\t\ted.addButton(\"sqlbutton\",\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttitle : \"SqL\",\r\n\t\t\t\t\t\timage : \"" . $CodeButtons . "sql.gif\",\r\n\t\t\t\t\t\tonclick : function()\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\ted.focus();\r\n\t\t\t\t\t\t\ted.selection.setContent('[sql]'+ed.selection.getContent()+'[/sql]');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\r\n\t\t\t\t\ted.addButton(\"emailbutton\",\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttitle : \"E-Mail\",\r\n\t\t\t\t\t\timage : \"" . $CodeButtons . "email.gif\",\r\n\t\t\t\t\t\tonclick : function()\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\ted.focus();\r\n\t\t\t\t\t\t\ted.selection.setContent('[email]'+ed.selection.getContent()+'[/email]');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\r\n\t\t\t\t\ted.addButton(\"youtubebutton\",\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttitle : \"Youtube\",\r\n\t\t\t\t\t\timage : \"" . $CodeButtons . "youtube.gif\",\r\n\t\t\t\t\t\tonclick : function()\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\ted.focus();\r\n\t\t\t\t\t\t\ted.selection.setContent('[youtube]'+ed.selection.getContent()+'[/youtube]');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\r\n\t\t\t\t\ted.addButton(\"vimeobutton\",\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttitle : \"Vimeo\",\r\n\t\t\t\t\t\timage : \"" . $CodeButtons . "vimeo.gif\",\r\n\t\t\t\t\t\tonclick : function()\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\ted.focus();\r\n\t\t\t\t\t\t\ted.selection.setContent('[vimeo]'+ed.selection.getContent()+'[/vimeo]');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\r\n\t\t\t\t\t" . ($INFORUMS && isset($CodeButtons) ? "\r\n\t\t\t\t\ted.addButton(\"hidebutton\",\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\ttitle : \"Hide\",\r\n\t\t\t\t\t\timage : \"" . $CodeButtons . "hide.gif\",\r\n\t\t\t\t\t\tonclick : function()\r\n\t\t\t\t\t\t{\r\n\t\t\t\t\t\t\ted.focus();\r\n\t\t\t\t\t\t\ted.selection.setContent('[hide]'+ed.selection.getContent()+'[/hide]');\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});" : "") . "\r\n\t\t\t\t}\r\n\t\t\t});\r\n\r\n\t\t\tfunction toggleEditor(id)\r\n\t\t\t{\r\n\t\t\t\tif (!tinyMCE.get(id))\r\n\t\t\t\t\ttinyMCE.execCommand('mceAddControl', false, id);\r\n\t\t\t\telse\r\n\t\t\t\t\ttinyMCE.execCommand('mceRemoveControl', false, id);\r\n\t\t\t}\r\n\t</script>";
        echo $TINYMCEOUTPUT;
        break;
    default:
        exit("Invalid Editory Type!");
}

?>