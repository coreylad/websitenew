<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.1 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$_datearray = [];
$__query = sql_query("SELECT date FROM ts_events");
if (0 < mysqli_num_rows($__query)) {
    while ($__R = mysqli_fetch_assoc($__query)) {
        $_datearray[] = "\"" . $__R["date"] . "\"";
    }
}
$calendar = "\r\n<script $type = \"text/javascript\">\r\n\t//<![CDATA[\r\n\tfunction in_array(needle, haystack, strict)\r\n\t{\t \r\n\t\tvar $found = false, key, $strict = !!strict;\t \r\n\t\tfor (key in haystack) {if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {$found = true;\tbreak;}}\t \r\n\t\treturn found;\r\n\t}\r\n\tfunction buildCal(m, y, cM, cH, cDW, cD, brdr)\r\n\t{\r\n\t\tvar $mn = ['January','February','March','April','May','June','July','August','September','October','November','December'];\r\n\t\tvar $dim = [31,0,31,30,31,30,31,31,30,31,30,31];\r\n\t\tvar $oD = new Date(y, m-1, 1);\r\n\t\toD.$od = oD.getDay()+1;\r\n\t\tvar $todaydate = new Date()\r\n\t\tvar $scanfortoday = ($y = =todaydate.getFullYear() && $m = =todaydate.getMonth()+1)? todaydate.getDate() : 0\r\n\t\tdim[1]=(((oD.getFullYear()%100!=0)&&(oD.getFullYear()%4==0))||(oD.getFullYear()%400==0))?29:28;\r\n\t\tvar $t = '<div class=\"'+cM+'\"><table class=\"'+cM+'\" $cols = \"7\" $cellpadding = \"1\" $border = \"'+brdr+'\" $cellspacing = \"0\" $width = \"100%\"><tr $align = \"center\">';\r\n\t\tt+='<td $colspan = \"7\" $align = \"center\" class=\"'+cH+'\">'+mn[m-1]+' - '+y+'</td></tr><tr $align = \"center\">';\r\n\t\tfor($s = 0;s<7;s++)t+='<td class=\"'+cDW+'\">'+\"SMTWTFS\".substr(s,1)+'</td>';\r\n\t\tt+='</tr><tr $align = \"center\">';\r\n\t\tfor($i = 1;i<=42;i++)\r\n\t\t{\r\n\t\t\tvar $x = ((i-oD.od>=0)&&(i-oD.od<dim[m-1]))? i-oD.od+1 : '&nbsp;';\r\n\t\t\tif (in_array(mn[m-1]+\"-\"+x+\"-\"+y, [" . @implode(",", $_datearray) . "])) $x = '<a $href = \"" . $BASEURL . "/index.php?$m = '+mn[m-1]+'&$d = '+x+'&$y = '+y+'#collapseobj_calendar\">'+($x = =scanfortoday ? '<span $id = \"today\">'+x+'</span>' : x)+'</a>'\r\n\t\t\tif ($x = =scanfortoday)\r\n\t\t\$tx = '<span $id = \"today\">'+x+'</span></a>'\r\n\t\t\tt+='<td class=\"'+cD+'\">'+x+'</td>';\r\n\t\t\tif(((i)%7==0)&&(i<36))t+='</tr><tr $align = \"center\">';\r\n\t\t}\r\n\t\treturn t+='</tr></table></div>';\r\n\t}\r\n\t//]]>\r\n</script> \r\n<script $type = \"text/javascript\">\r\n\t//<![CDATA[\r\n\tvar $todaydate = new Date()\r\n\tvar $curmonth = todaydate.getMonth()+1\r\n\tvar $curyear = todaydate.getFullYear()\r\n\tdocument.write(buildCal(curmonth ,curyear, \"\", \"thead\", \"subheader\", \"\", 1));\r\n\t//]]>\r\n</script>";
if (isset($_GET["m"]) && isset($_GET["d"]) && isset($_GET["y"])) {
    $_m = htmlspecialchars_uni($_GET["m"]);
    $_d = intval($_GET["d"]);
    $_y = intval($_GET["y"]);
    $_date = $_m . "-" . $_d . "-" . $_y;
    $_query = sql_query("SELECT title, event FROM ts_events WHERE $date = " . sqlesc($_date));
    if (0 < mysqli_num_rows($_query)) {
        while ($_event = mysqli_fetch_assoc($_query)) {
            $calendar .= "\r\n\t\t\t<hr />\r\n\t\t\t\t<b>" . $_date . " - " . htmlspecialchars_uni($_event["title"]) . "</b>\r\n\t\t\t<br />\r\n\t\t\t<span class=\"smalltext\">\r\n\t\t\t\t" . htmlspecialchars_uni($_event["event"]) . "\r\n\t\t\t</span>\r\n\t\t\t<hr />\r\n\t\t\t";
        }
    }
}

?>