<?php
define("R_VERSION", "v1.8 by xam");
define("THIS_SCRIPT", "getrss.php");
require "./global.php";
if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("getrss");
$allowed_timezones = ["-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3.5", "-3", "-2", "-1", "0", "1", "2", "3", "3.5", "4", "4.5", "5", "5.5", "6", "7", "8", "9", "9.5", "10", "11", "12"];
$allowed_showrows = ["5", "10", "20", "30", "40", "50"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_queries_ = [];
    $link = $BASEURL . "/rss.php?$secret_key = " . htmlspecialchars_uni($CURUSER["torrent_pass"]) . "&";
    if ($_POST["feedtype"] == "download") {
        $_queries_[] = "feedtype=download";
    } else {
        $_queries_[] = "feedtype=details";
    }
    if (isset($_POST["timezone"]) && in_array($_POST["timezone"], $allowed_timezones, 1)) {
        $_queries_[] = "timezone=" . (int) $_POST["timezone"];
    } else {
        $_queries_[] = "timezone=1";
    }
    if (isset($_POST["showrows"]) && in_array($_POST["showrows"], $allowed_showrows, 1)) {
        $_queries_[] = "showrows=" . (int) $_POST["showrows"];
    } else {
        $_queries_[] = "showrows=20";
    }
    if (isset($_POST["showall"])) {
        $_queries_[] = "categories=all";
    } else {
        $sqlquery = sql_query("SELECT id FROM categories WHERE $type = 'c'");
        while ($res = mysqli_fetch_assoc($sqlquery)) {
            if (isset($_POST["cat" . $res["id"]]) && $_POST["cat" . $res["id"]] == "yes") {
                if (!isset($_POST["cat"]) || isset($_POST["cat"]) && !is_array($_POST["cat"])) {
                    $_POST["cat"] = [];
                }
                array_push($_POST["cat"], $res["id"]);
            }
        }
        if (isset($_POST["cat"])) {
            $_queries_[] = "categories=" . implode(",", (array) $_POST["cat"]);
        } else {
            $_queries_[] = "categories=all";
        }
    }
    $__queries = implode("&", $_queries_);
    if ($__queries) {
        $link .= $__queries;
    }
    stdhead($lang->getrss["title"]);
    echo "\r\n\t<table $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">" . $lang->getrss["done2"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"\"><div $style = \"border: thin inset; padding: 2px; overflow: auto;\"><b>" . htmlspecialchars($link) . "</b></div></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t";
    stdfoot();
    exit;
}
stdhead($lang->getrss["title"]);
include_once INC_PATH . "/functions_category2.php";
$catoptions = ts_category_list2(2, "rss");
echo "<FORM $method = \"post\" $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "\" $name = \"rss\">\r\n<table $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n<TR><TD class=\"thead\" $colspan = \"2\">";
echo $lang->getrss["title"];
echo "</td></tr>\r\n<TR>\r\n<TD class=\"rowhead\">";
echo $lang->getrss["field1"];
echo "</TD>\r\n<TD>\r\n";
echo $catoptions;
echo "</TD>\r\n</TR>\r\n<TR>\r\n<TD class=\"rowhead\">";
echo $lang->getrss["field3"];
echo "</TD>\r\n<TD>\r\n<INPUT $type = \"radio\" $name = \"feedtype\" $value = \"details\" />";
echo $lang->getrss["field4"];
echo "<br />\r\n<INPUT $type = \"radio\" $name = \"feedtype\" $value = \"download\" $checked = \"checked\" />";
echo $lang->getrss["field5"];
echo "</TD>\r\n</TR>\r\n<tr>\r\n\r\n<td $align = \"right\"><b>";
echo $lang->getrss["field6"];
echo "</b></td>\r\n<td $valign = \"top\"><select $name = \"timezone\">\r\n\r\n    <option $value = \"-12\">(GMT -12:00) Eniwetok, Kwajalein</option>\r\n\r\n    <option $value = \"-11\">(GMT -11:00) Midway Island, Samoa</option>\r\n\r\n    <option $value = \"-10\">(GMT -10:00) Hawaii</option>\r\n\r\n    <option $value = \"-9\">(GMT -9:00) Alaska</option>\r\n\r\n    <option $value = \"-8\">(GMT -8:00) Pacific Time (US & Canada)</option>\r\n\r\n    <option $value = \"-7\">(GMT -7:00) Mountain Time (US & Canada)</option>\r\n\r\n    <option $value = \"-6\">(GMT -6:00) Central Time (US & Canada), Mexico City</option>\r\n\r\n    <option $value = \"-5\">(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima</option>\r\n\r\n    <option $value = \"-4\">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz</option>\r\n\r\n    <option $value = \"-3.5\">(GMT -3:30) Newfoundland</option>\r\n\r\n    <option $value = \"-3\">(GMT -3:00) Brazil, Buenos Aires, Georgetown</option>\r\n\r\n    <option $value = \"-2\">(GMT -2:00) Mid-Atlantic</option>\r\n\r\n    <option $value = \"-1\">(GMT -1:00 hour) Azores, Cape Verde Islands</option>\r\n\r\n    <option $value = \"0\">(GMT) Western Europe Time, London, Lisbon, Casablanca</option>\r\n\r\n    <option $value = \"1\">(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris</option>\r\n\r\n    <option $value = \"2\">(GMT +2:00) Kaliningrad, South Africa</option>\r\n\r\n    <option $value = \"3\">(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>\r\n\r\n    <option $value = \"3.5\">(GMT +3:30) Tehran</option>\r\n\r\n    <option $value = \"4\">(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>\r\n\r\n    <option $value = \"4.5\">(GMT +4:30) Kabul</option>\r\n\r\n    <option $value = \"5\">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>\r\n\r\n    <option $value = \"5.5\">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>\r\n\r\n    <option $value = \"6\">(GMT +6:00) Almaty, Dhaka, Colombo</option>\r\n\r\n    <option $value = \"7\">(GMT +7:00) Bangkok, Hanoi, Jakarta</option>\r\n\r\n    <option $value = \"8\">(GMT +8:00) Beijing, Perth, Singapore, Hong Kong</option>\r\n\r\n    <option $value = \"9\">(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>\r\n\r\n    <option $value = \"9.5\">(GMT +9:30) Adelaide, Darwin</option>\r\n\r\n    <option $value = \"10\">(GMT +10:00) Eastern Australia, Guam, Vladivostok</option>\r\n\r\n    <option $value = \"11\">(GMT +11:00) Magadan, Solomon Islands, New Caledonia</option>\r\n\r\n    <option $value = \"12\">(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka</option>\r\n\r\n    </select></td></tr>\r\n<tr><td $align = \"right\"><b>";
echo $lang->getrss["field7"];
echo "</b></td><td><select $name = \"showrows\">\r\n<option $value = \"5\">5</option>\r\n<option $value = \"10\">10</option>\r\n<option $value = \"20\">20</option>\r\n<option $value = \"30\">30</option>\r\n<option $value = \"40\">40</option>\r\n<option $value = \"50\">50</option>\r\n</select> <BUTTON $type = \"submit\" class=button>";
echo $lang->getrss["field8"];
echo "</BUTTON></td></tr>\r\n</TABLE>\r\n</FORM>\r\n";
stdfoot();

?>