<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "faq.php");
require "./global.php";
define("TSFAQ_VERSION", "1.2.5 by xam");
include_once INC_PATH . "/functions_security.php";
$lang->load("faq");
$faqAction = isset($_GET["do"]) ? htmlspecialchars_uni($_GET["do"]) : (isset($_POST["do"]) ? htmlspecialchars_uni($_POST["do"]) : "");
$faqErrors = [];
stdhead($lang->faq["faqtitle"]);
echo "\r\n<script type=\"text/javascript\">\r\n\tfunction collapseFAQ(FID)\r\n\t{\r\n\t\tjQuery(document).ready(function()\r\n\t\t{\r\n\t\t\tjQuery(\"#\"+FID).toggle(\"slow\");\r\n\t\t});\r\n\t}\r\n\r\n\tfunction showLink(LINK)\r\n\t{\r\n\t\tprompt(\"" . $lang->faq["faqlink"] . "\", \"\"+LINK+\"\");\r\n\t}\r\n</script>";
if ($faqAction == "search") {
    $searchWords = trim($_GET["words"]);
    $searchType = $_GET["searchtype"] == "titles" ? "titles" : "all";
    if (empty($searchWords) || strlen($searchWords) < 3) {
        $faqErrors[] = $lang->faq["searcherror"];
    } else {
        if ($searchType == "titles") {
            $searchExtra = "a.name LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $searchWords) . "%'";
        } else {
            $searchExtra = "(a.name LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $searchWords) . "%' OR a.description LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $searchWords) . "%')";
        }
        $faqQuery = sql_query("SELECT a.id, a.name, a.description, b.name as title, b.id as fid FROM ts_faq a LEFT JOIN ts_faq b ON (a.pid=b.id) WHERE a.type = '2' AND " . $searchExtra . " ORDER By a.disporder ASC");
        if (mysqli_num_rows($faqQuery) == 0) {
            $faqErrors[] = $lang->faq["searcherror"];
        } else {
            echo "\r\n\t\t\t\t<table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t" . $lang->faq["results"] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>";
            $uldone = false;
            while ($faqRow = mysqli_fetch_assoc($faqQuery)) {
                if (!$uldone) {
                    $uldone = true;
                    echo "<div class=\"subheader\" style=\"padding: 5px;\"><strong>" . $faqRow["title"] . "</strong></div>";
                }
                echo "\r\n\t\t\t\t\t\t<p>\r\n\t\t\t\t\t\t\t<img src=\"" . $pic_base_url . "information.png\" onclick=\"showLink('" . $BASEURL . "/faq.php?do=view&id=" . $faqRow["fid"] . "&fid=" . $faqRow["id"] . "');\" style=\"cursor: pointer;\" class=\"inlineimg\" alt=\"" . $lang->faq["faqlink"] . "\" title=\"" . $lang->faq["faqlink"] . "\" /> <a href=\"javascript:collapseFAQ('faq_" . $faqRow["id"] . "');\"><strong>" . $faqRow["name"] . "</strong></a>\r\n\t\t\t\t\t\t\t<div id=\"faq_" . $faqRow["id"] . "\" style=\"display: none;\">" . $faqRow["description"] . "</div>\r\n\t\t\t\t\t\t</p>";
            }
            echo "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t</table><br />";
        }
    }
}
if ($faqAction == "view") {
    $faqId = intval(TS_Global("id"));
    if (!is_valid_id($faqId)) {
        $faqErrors[] = $lang->faq["faqerror"];
    } else {
        $faqQuery = sql_query("SELECT a.id,a.name,a.description,b.name as title FROM ts_faq a LEFT JOIN ts_faq b ON (a.pid=b.id) WHERE a.type = '2' AND a.pid = '" . $faqId . "' ORDER By a.disporder ASC");
        if (mysqli_num_rows($faqQuery) == 0) {
            $faqErrors[] = $lang->faq["faqerror"];
        } else {
            echo "\r\n\t\t\t\t<table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t" . $lang->faq["faqtitle"] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t";
            $uldone = false;
            while ($faqRow = mysqli_fetch_assoc($faqQuery)) {
                if (!$uldone) {
                    $uldone = true;
                    echo "<div class=\"subheader\" style=\"padding: 5px;\"><strong>" . $faqRow["title"] . "</strong></div>";
                }
                echo "\r\n\t\t\t\t\t\t<p>\r\n\t\t\t\t\t\t\t<img src=\"" . $pic_base_url . "information.png\" onclick=\"showLink('" . $BASEURL . "/faq.php?do=view&id=" . $faqId . "&fid=" . $faqRow["id"] . "');\" style=\"cursor: pointer;\" class=\"inlineimg\" alt=\"" . $lang->faq["faqlink"] . "\" title=\"" . $lang->faq["faqlink"] . "\" /> <a href=\"javascript:collapseFAQ('faq_" . $faqRow["id"] . "');\"><strong>" . $faqRow["name"] . "</strong></a>\r\n\t\t\t\t\t\t\t<div id=\"faq_" . $faqRow["id"] . "\" style=\"display: " . (isset($_GET["fid"]) && $_GET["fid"] == $faqRow["id"] ? "inline" : "none") . ";">" . $faqRow["description"] . "</div>\r\n\t\t\t\t\t\t</p>";
            }
            echo "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t</table><br />";
        }
    }
}
show_faq_errors();
echo "\r\n<form method=\"get\" action=\"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n<input type=\"hidden\" name=\"do\" value=\"search\" />\r\n<table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\r\n\t<tr>\r\n\t\t<td class=\"thead\">\r\n\t\t\t" . $lang->faq["search"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td>\r\n\t\t" . $lang->faq["words"] . " <input type=\"text\" name=\"words\" size=\"30\" value=\"" . (isset($words) ? htmlspecialchars_uni($words) : "") . "\" />\r\n\t\t" . $lang->faq["searchin"] . " \r\n\t\t<select name=\"searchtype\">\r\n\t\t\t<option value=\"all\"" . (isset($searchtype) && $searchtype == "all" ? " selected=\"selected\"" : "") . ">" . $lang->faq["searchin1"] . " </option>\r\n\t\t\t<option value=\"titles\"" . (isset($searchtype) && $searchtype == "titles" ? " selected=\"selected\"" : "") . ">" . $lang->faq["searchin2"] . " </option>\r\n\t\t</select>\r\n\t\t <input type=\"submit\" value=\"" . $lang->faq["dosearch"] . " \" />\r\n\t\t  <input type=\"reset\" value=\"" . $lang->faq["reset"] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n<br />\r\n";
($faqListQuery = sql_query("SELECT id, name FROM ts_faq WHERE type = '1' ORDER By disporder ASC")) || sqlerr(__FILE__, 207);
if (0 < mysqli_num_rows($faqListQuery)) {
    echo "\r\n\t<table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . $lang->faq["faqtitle"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<ul>\r\n\t";
    while ($faqListRow = mysqli_fetch_assoc($faqListQuery)) {
        echo "<li><a href=\"" . $_SERVER["SCRIPT_NAME"] . "?do=view&amp;id=" . $faqListRow["id"] . "\"><u>" . $faqListRow["name"] . "</u></a></li>";
    }
    echo "\r\n\t\t\t\t</ul>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
}
stdfoot();
function show_faq_errors()
{
    global $faqErrors;
    global $lang;
    if (0 < count($faqErrors)) {
        $errors = implode("<br />", $faqErrors);
        echo "\r\n\t\t\t<table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font color=\"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>