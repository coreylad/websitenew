<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "redirector_footer.php");
require "./global.php";
define("R_VERSION", "0.4 by xam");
$url = fix_url($_GET["url"]);
$url = clean($url);
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<HEAD>\r\n<META http-equiv=\"Content-Type\" CONTENT=\"text/html; charset=";
echo $charset;
echo "\" />\r\n<TITLE>";
echo $SITENAME;
echo "</TITLE>\r\n<style type=\"text/css\">\r\nbody {\r\n\tfont-family: Verdana, Tahoma, Georgia;\r\n\tfont-size: 10px;\r\n}\r\n\r\n.link a{\r\n\tcolor: #FFFFFF;\r\n\ttext-decoration: none;\r\n\tfont-weight:normal;\r\n}\r\n\r\n.link a:hover {\r\n\ttext-decoration: underline;\r\n}\r\n\r\n.linkOrange a{\r\n\tcolor: #EC8749;\r\n\ttext-decoration: none;\r\n}\r\n\r\n.linkOrange a:hover {\r\n\ttext-decoration: underline;\r\n}\r\n\r\n.SmallText, .Link, .OrangeSmallText, .GraySmallText, .BoldSmallText{\r\n\tfont-family: Verdana;\r\n\tfont-size: 10; \r\n}\r\n\r\n.BoldSmallText {\r\n\tfont-weight: bold;\r\n}\r\n\r\n.OrangeSmallText {\r\n\tcolor: #EC8749;\r\n}\r\n\r\n.GraySmallText {\r\n\tcolor: #999999;\r\n}\r\n\r\n.WhiteSmallText {\r\n\tcolor: #FFFFFF;\r\n}\r\n</style>\r\n</HEAD>\r\n\r\n<body bgcolor=\"#444444\">\r\n<!--javascript:top.location = parent.document.referrer;\"--> \r\n<div align=\"center\">\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" height=\"20\" width=\"600\">\r\n\t<tr>\r\n\t\t<td align=\"left\" class=\"link\"><a href=\"";
echo $BASEURL;
echo "\" target=\"_top\"><span style=\"font-weight:bold;font-size:10px;\">";
echo $BASEURL;
echo "</span></a></td>\r\n\t\t\r\n\t\t<td align=\"left\" class=\"link\"><b class=\"OrangeSmallText\">";
echo $lang->global["invalidlink"];
echo "</b> <a href=\"";
echo $BASEURL;
echo "/contactstaff.php?subject=invalid_link&link=";
echo $url;
echo "\" target=\"_top\">";
echo $lang->global["clicktoreport"];
echo "</a></td>\r\n\t\t\r\n\t\t<td align=\"right\" class=\"link\"><a href=\"";
echo $url;
echo "\" target=\"_top\"><img src=\"";
echo $pic_base_url;
echo "/close.gif\" title=\"";
echo $lang->global["buttonremoveframe"];
echo "\" border=\"0\" /></a></td>\r\n\t</tr>\r\n</table>\r\n</div>\r\n</body>\r\n</HTML>";
function clean($data)
{
    $data = trim(strval($data));
    $data = str_replace(chr(0), "", $data);
    return $data;
}

?>