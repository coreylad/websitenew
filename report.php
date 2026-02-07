<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "report.php");
require "./global.php";
define("R_VERSION", "v2.3.2 by xam");
if ($usergroups["canreport"] != "yes") {
    print_no_permission();
}
$lang->load("report");
$errors = [];
$uid = intval($CURUSER["id"]);
$date = TIMENOW;
$reporting = isset($_GET["reporting"]) ? intval($_GET["reporting"]) : 0;
$type = isset($_GET["type"]) ? intval($_GET["type"]) : 0;
$page = isset($_GET["page"]) ? intval($_GET["page"]) : 0;
$extra = isset($_GET["extra"]) ? intval($_GET["extra"]) : 0;
$url = "";
$Done = "";
if (!$reporting || !$type) {
    print_no_permission();
}
if (!in_array($type, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], true)) {
    print_no_permission();
}
switch ($type) {
    case "1":
        $url = "userdetails.php?id=" . $reporting;
        break;
    case "2":
        $url = "details.php?id=" . $reporting;
        break;
    case "3":
        $url = "details.php?id=" . $extra . "&tab=comments&page=" . $page . "&viewcomm=" . $reporting . "&scrollto=cid" . $reporting;
        break;
    case "4":
        $url = "tsf_forums/showthread.php?tid=" . $extra . "&page=" . $page . "&nolastpage=true&pid=" . $reporting . "&scrollto=pid" . $reporting;
        break;
    case "5":
        $url = "userdetails.php?id=" . $extra . "&page=" . $page . "&scrollto=ShowVisitorMessage" . $reporting;
        break;
    case "6":
        $url = "ts_blog.php?do=show_blog&bid=" . $extra . "&page=" . $page . "&cid=" . $reporting . "&scrollto=show_comments" . $reporting;
        break;
    case "7":
        $url = "viewrequests.php?do=view_request&rid=" . $reporting;
        break;
    case "8":
        $url = "ts_subtitles.php?sid=" . $reporting . "&page=" . $page . "&scrollto=sid" . $reporting;
        break;
    case "9":
        $url = "ts_albums.php?do=show_image&albumid=" . $extra . "&imageid=" . $reporting;
        break;
    case "10":
        $url = "ts_tutorials.php?do=show_tutorial&tid=" . $extra . "&page=" . $page . "&cid=" . $reporting . "&scrollto=show_comments" . $reporting;
        break;
    default:
        if (!$url) {
            print_no_permission();
        }
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $reason = trim($_POST["reason"]);
            if (!$reason || strlen($reason) < 3) {
                $errors[] = $lang->report["error1"];
            } else {
                $Insert = sql_query("INSERT INTO ts_reports (uid, date, reporting, type, reason, url) VALUES ('" . $uid . "', '" . $date . "', '" . $reporting . "', '" . $type . "', " . sqlesc($reason) . ", " . sqlesc($url) . ")");
                if (!$Insert) {
                    $errors[] = $lang->report["error2"];
                } else {
                    $Done = show_notice($lang->report["thanks"], false, $lang->report["r" . $type]);
                }
            }
        }
        $defaulttemplate = ts_template();
        echo "\r\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $charset . "\" />\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $BASEURL . "/style.php?theme=" . $defaulttemplate . "&style=style.css\" />\r\n<title>" . $lang->report["r" . $type] . "</title>\r\n<script type=\"text/javascript\" src=\"" . $BASEURL . "/scripts/disablerightclick.js\"></script>\r\n</head>\r\n<body>\r\n";
        if ($Done) {
            echo "\r\n\t<script type=\"text/javascript\">\r\n\t\tsetTimeout(\"self.close()\", 5000);\r\n\t</script>\r\n\t" . $Done . "\r\n\t</body>\r\n\t</html>";
            exit;
        }
        show_report_errors();
        echo "\r\n<a id=\"report_form\" name=\"report_form\"></a>\r\n<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?reporting=" . $reporting . "&type=" . $type . "&page=" . $page . "&extra=" . $extra . "\">\r\n<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" align=\"left\">\r\n\t\t\t" . $lang->report["r" . $type] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td>\r\n\t\t\t<b>" . $lang->report["reason"] . ":</b><br />\r\n\t\t\t<textarea rows=\"6\" cols=\"60\" name=\"reason\">" . (isset($reason) ? htmlspecialchars_uni($reason) : "") . "</textarea>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"subheader\" align=\"center\">\r\n\t\t\t<input type=\"submit\" value=\"" . $lang->report["submit"] . "\" /> <input type=\"reset\" value=\"" . $lang->report["reset"] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n</body>\r\n</html>\r\n";
}
function show_report_errors()
{
    global $errors;
    global $lang;
    if (0 < count($errors)) {
        $errors = implode("<br />", $errors);
        echo "\r\n\t\t\t<table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font color=\"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>