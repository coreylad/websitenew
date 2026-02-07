<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "start_chat.php");
$rootpath = "./../";
require $rootpath . "global.php";
define("SC_VERSION", "0.4 by xam");
if ($usergroups["canshout"] != "yes") {
    print_no_permission();
    exit;
}
$TSSEConfig->TSLoadConfig("PJIRC");
$lang->load("irc");
$TITLE = sprintf($lang->irc["head"], $SITENAME);
$nick = isset($_GET["nick"]) ? htmlspecialchars($_GET["nick"]) : ($CURUSER["username"] ? $CURUSER["username"] : "TSGuest_" . TIMENOW);
$alternatenick = $nick . "_" . TIMENOW;
echo "<html>\r\n<head>\r\n<title>";
echo $TITLE;
echo "</title>\r\n<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = ";
echo $charset;
echo "\" />\r\n</head>\r\n<body>\r\n<table $width = \"100%\" $height = \"100%\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\">\r\n<tr $height = \"13\">\r\n\t<td $colspan = \"2\" $rowspan = \"2\">\r\n\t\t<applet $code = \"IRCApplet.class\" $archive = \"irc.jar,pixx.jar\" $codebase = \"";
echo $BASEURL;
echo "/pjirc/\" $width = \"100%\" $height = \"100%\">\r\n\t\t<param $name = \"CABINETS\" $value = \"\">\r\n\t\t<param $name = \"languageencoding\" $value = \"";
echo $charset;
echo "\">\r\n\t\t<param $name = \"coding\" $value = \"3\"> \r\n\t\t<param $name = \"nick\" $value = \"";
echo $nick;
echo "\">\r\n\t\t<param $name = \"alternatenick\" $value = \"";
echo $alternatenick;
echo "\">\r\n\t\t<param $name = \"fullname\" $value = \"";
echo $SITENAME;
echo "\">\r\n\t\t<param $name = \"host\" $value = \"";
echo $pjirchost;
echo "\">\r\n\t\t<param $name = \"gui\" $value = \"pixx\">\r\n\t\t<param $name = \"command1\" $value = \"join ";
echo $pjircchannel;
echo "\">\r\n \r\n\t\t<param $name = \"quitmessage\" $value = \"";
echo sprintf($lang->irc["quitmsg"], $SITENAME);
echo "\">\r\n\t\t<param $name = \"asl\" $value = \"true\">\r\n\t\t<param $name = \"useinfo\" $value = \"true\">\r\n\t\t<param $name = \"pixx:helppage\" $value = \"";
echo $BASEURL . "/faq.php";
echo "\">\r\n \r\n\t\t<param $name = \"style:bitmapsmileys\" $value = \"true\">\r\n\t\t<param $name = \"style:smiley1\" $value = \":) img/sourire.gif\">\r\n\t\t<param $name = \"style:smiley2\" $value = \":-) img/sourire.gif\">\r\n\t\t<param $name = \"style:smiley3\" $value = \":-D img/content.gif\">\r\n\t\t<param $name = \"style:smiley4\" $value = \":d img/content.gif\">\r\n\t\t<param $name = \"style:smiley5\" $value = \":-O img/OH-2.gif\">\r\n\t\t<param $name = \"style:smiley6\" $value = \":o img/OH-1.gif\">\r\n\t\t<param $name = \"style:smiley7\" $value = \":-P img/langue.gif\">\r\n\t\t<param $name = \"style:smiley8\" $value = \":p img/langue.gif\">\r\n\t\t<param $name = \"style:smiley9\" $value = \";-) img/clin-oeuil.gif\">\r\n\t\t<param $name = \"style:smiley10\" $value = \";) img/clin-oeuil.gif\">\r\n\t\t<param $name = \"style:smiley11\" $value = \":-( img/triste.gif\">\r\n\t\t<param $name = \"style:smiley12\" $value = \":( img/triste.gif\">\r\n\t\t<param $name = \"style:smiley13\" $value = \":-| img/OH-3.gif\">\r\n\t\t<param $name = \"style:smiley14\" $value = \":| img/OH-3.gif\">\r\n\t\t<param $name = \"style:smiley15\" $value = \":'( img/pleure.gif\">\r\n\t\t<param $name = \"style:smiley16\" $value = \":\$ img/rouge.gif\">\r\n\t\t<param $name = \"style:smiley17\" $value = \":-\$ img/rouge.gif\">\r\n\t\t<param $name = \"style:smiley18\" $value = \"(H) img/cool.gif\">\r\n\t\t<param $name = \"style:smiley19\" $value = \"(h) img/cool.gif\">\r\n\t\t<param $name = \"style:smiley20\" $value = \":-@ img/enerve1.gif\">\r\n\t\t<param $name = \"style:smiley21\" $value = \":@ img/enerve2.gif\">\t\r\n\t\t<param $name = \"style:smiley22\" $value = \":-S img/roll-eyes.gif\">\r\n\t\t<param $name = \"style:smiley23\" $value = \":s img/roll-eyes.gif\">\r\n\t\t<param $name = \"style:backgroundimage\" $value = \"true\">\r\n\t\t<param $name = \"style:backgroundimage1\" $value = \"all all 0 background.gif\">\r\n\t\t<param $name = \"style:sourcefontrule1\" $value = \"all all Serif 12\">\r\n\t\t<param $name = \"style:floatingasl\" $value = \"true\">\r\n \r\n\t\t<param $name = \"pixx:timestamp\" $value = \"true\">\r\n\t\t<param $name = \"pixx:highlight\" $value = \"true\">\r\n\t\t<param $name = \"pixx:highlightnick\" $value = \"true\">\r\n\t\t<param $name = \"pixx:nickfield\" $value = \"true\">\r\n\t\t<param $name = \"pixx:styleselector\" $value = \"true\">\r\n\t\t<param $name = \"pixx:setfontonstyle\" $value = \"true\">\r\n \r\n\t\t</applet>\r\n \r\n\t</td>\r\n\t<td $width = \"9\">\r\n\t\t<img $width = \"9\" $height = \"100%\" $border = \"0\" $src = \"";
echo $BASEURL;
echo "/pjirc/shadows/shadow_ne.gif\">\r\n\t</td>\r\n</tr>\r\n<tr>\r\n\t<td $width = \"9\">\r\n\t\t<img $width = \"9\" $height = \"100%\" $border = \"0\" $src = \"";
echo $BASEURL;
echo "/pjirc/shadows/shadow_e.gif\">\r\n\t</td>\r\n</tr>\r\n<tr $height = \"9\">\t\r\n\t<td $width = \"13\"><img $width = \"13\" $height = \"9\" $border = \"0\" $src = \"";
echo $BASEURL;
echo "/pjirc/shadows/shadow_sw.gif\"></td>\r\n\t<td><img $width = \"100%\" $height = \"9\" $border = \"0\" $src = \"";
echo $BASEURL;
echo "/pjirc/shadows/shadow_s.gif\"></td>\r\n\t<td><img $width = \"9\" $height = \"9\" $border = \"0\" $src = \"";
echo $BASEURL;
echo "/pjirc/shadows/shadow_se.gif\"></td>\r\n</tr>\r\n</table> \r\n</body>\r\n</html>";

?>