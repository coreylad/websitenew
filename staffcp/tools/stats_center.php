<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/stats_center.lang");
$Message = "";
$stats_type = "";
$show_type = "";
$date_from = "";
$date_to = "";
$GeneratedChartImage = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $stats_type = isset($_POST["stats_type"]) ? $_POST["stats_type"] : "";
    $show_type = isset($_POST["show_type"]) ? $_POST["show_type"] : "";
    $date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : "";
    $date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : "";
    if ($stats_type && $show_type && $date_from && $date_to) {
        switch ($stats_type) {
            case "5":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT added FROM users WHERE UNIX_TIMESTAMP(added) >= " . strtotime($date_from) . " AND UNIX_TIMESTAMP(added) <= " . strtotime($date_to) . " ORDER BY added ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "added");
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "added");
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "6":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT dateline FROM tsf_posts WHERE dateline >= " . strtotime($date_from) . " AND dateline <= " . strtotime($date_to) . " ORDER BY dateline ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "dateline", false);
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "dateline", false);
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "7":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT added FROM comments WHERE UNIX_TIMESTAMP(added) >= " . strtotime($date_from) . " AND UNIX_TIMESTAMP(added) <= " . strtotime($date_to) . " ORDER BY added ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "added");
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "added");
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "8":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT added FROM messages WHERE UNIX_TIMESTAMP(added) >= " . strtotime($date_from) . " AND UNIX_TIMESTAMP(added) <= " . strtotime($date_to) . " ORDER BY added ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "added");
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "added");
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "9":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT completedat FROM snatched WHERE UNIX_TIMESTAMP(completedat) >= " . strtotime($date_from) . " AND UNIX_TIMESTAMP(completedat) <= " . strtotime($date_to) . " AND $finished = \"yes\" AND \tcompletedat != \"0000-00-00 00:00:00\" ORDER BY completedat ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "completedat");
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "completedat");
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "10":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT added FROM torrents WHERE UNIX_TIMESTAMP(added) >= " . strtotime($date_from) . " AND UNIX_TIMESTAMP(added) <= " . strtotime($date_to) . " ORDER BY added ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "added");
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "added");
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "11":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT added FROM requests WHERE UNIX_TIMESTAMP(added) >= " . strtotime($date_from) . " AND UNIX_TIMESTAMP(added) <= " . strtotime($date_to) . " ORDER BY added ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "added");
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "added");
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "12":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT dateline FROM tsf_poll WHERE dateline >= " . strtotime($date_from) . " AND dateline <= " . strtotime($date_to) . " ORDER BY dateline ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "dateline", false);
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "dateline", false);
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "13":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT dateline FROM tsf_threads WHERE dateline >= " . strtotime($date_from) . " AND dateline <= " . strtotime($date_to) . " ORDER BY dateline ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "dateline", false);
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "dateline", false);
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
            case "14":
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT added FROM news WHERE UNIX_TIMESTAMP(added) >= " . strtotime($date_from) . " AND UNIX_TIMESTAMP(added) <= " . strtotime($date_to) . " ORDER BY added ASC");
                if (mysqli_num_rows($query)) {
                    switch ($show_type) {
                        case "22":
                            $GeneratedChartImage = function_111($query, "added");
                            break;
                        case "23":
                            $GeneratedChartImage = applyUsernameStyle($query, "added");
                            break;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
                break;
        }
    } else {
        $Message = showAlertError($Language[4]);
    }
}
$StatsTypes = "\r\n<select $name = \"stats_type\" $style = \"width: 150px;\">";
for ($i = 5; $i <= 14; $i++) {
    $StatsTypes .= "\r\n\t<option $value = \"" . $i . "\"" . ($stats_type == $i ? " $selected = \"selected\"" : "") . ">" . $Language[$i] . "</option>";
}
$StatsTypes .= "\r\n</select>";
$ShowBy = "\r\n<select $name = \"show_type\"style=\"width: 150px;\">";
for ($i = 22; $i <= 23; $i++) {
    $ShowBy .= "\r\n\t<option $value = \"" . $i . "\"" . ($show_type == $i ? " $selected = \"selected\"" : "") . ">" . $Language[$i] . "</option>";
}
$ShowBy .= "\r\n</select>";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = \"MAIN\"");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
echo "\r\n<script $type = \"text/javascript\">\r\n\t\$(function()\r\n\t{\r\n\t\t\$(\"#date_from,#date_to\").datepicker({dateFormat: \"dd-mm-yy\", changeMonth: true, changeYear: true, closeText: \"X\", showButtonPanel: true});\r\n\t});\r\n</script>\r\n<script $type = \"text/javascript\">\r\n\tfunction PleaseWait()\r\n\t{\r\n\t\tTSGetID(\"pleasewait\").$disabled = \"disabled\";\r\n\t\tTSGetID(\"pleasewait\").$value = \"" . $Language[29] . "\";\r\n\t}\r\n</script>\r\n\r\n" . $Message . "\r\n" . ($GeneratedChartImage ? "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[26] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t" . $GeneratedChartImage . "\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n<br />\r\n" : "") . "\r\n<form $method = \"post\" $action = \"index.php?do=stats_center\" $name = \"stats_center\" $onsubmit = \"PleaseWait();\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\">" . $Language[17] . "</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . $StatsTypes . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\">" . $Language[21] . "</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . $ShowBy . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\">" . $Language[15] . "</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"date_from\" $id = \"date_from\" $value = \"" . htmlspecialchars($date_from) . "\" $size = \"20\" $dir = \"ltr\" $tabindex = \"1\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\">" . $Language[16] . "</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"date_to\" $id = \"date_to\" $value = \"" . htmlspecialchars($date_to) . "\" $size = \"20\" $dir = \"ltr\" $tabindex = \"1\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input $type = \"submit\" class=\"button\" $id = \"pleasewait\" $tabindex = \"1\" $value = \"" . $Language[26] . "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[27] . "\" $accesskey = \"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_112($query, $field, $strtotime = true)
{
    global $Language;
    $statName = "";
    $statValue = [];
    while ($currentRow = mysqli_fetch_assoc($query)) {
        if (isset($statValue[date("Y", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])][date("m", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])])) {
            date("Y", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field]);
            date("m", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field]);
            $statValue[date("Y", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])][date("m", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])]++;
        } else {
            $statValue[date("Y", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])][date("m", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])] = 1;
        }
    }
    $statFormatted = "http://chart.apis.google.com/chart?$cht = p3&$chd = t:{1}&$chs = 800x375&$chl = {2}";
    $statsArray = [];
    $chartData = [];
    $chartLabels = explode(",", $Language[28]);
    $chartValues = [];
    foreach ($statValue as $graphData => $dataPoints) {
        $chartValues[$graphData] = $dataPoints;
    }
    asort($chartValues);
    foreach ($chartValues as $graphData => $dataPoints) {
        $statsArray = [];
        $chartData = [];
        asort($dataPoints, SORT_STRING);
        foreach ($dataPoints as $seriesData => $plotData) {
            $statsArray[] = $plotData;
            $chartData[] = $chartLabels[intval($seriesData)] . " " . $graphData . " (" . number_format($plotData) . ")";
        }
        $visualData = number_format(array_sum($statsArray));
        $statName .= "<img $src = \"" . str_replace(["{1}", "{2}"], [implode(",", $statsArray), implode("|", $chartData)], $statFormatted) . "\" $border = \"0\" $alt = \"" . $visualData . "\" $title = \"" . $visualData . "\" />";
    }
    return $statName;
}
function function_111($query, $field, $strtotime = true)
{
    $statValue = [];
    while ($currentRow = mysqli_fetch_assoc($query)) {
        if (isset($statValue[date("Y", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])])) {
            date("Y", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field]);
            $statValue[date("Y", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])]++;
        } else {
            $statValue[date("Y", $strtotime ? strtotime($currentRow[$field]) : $currentRow[$field])] = 1;
        }
    }
    $statFormatted = "http://chart.apis.google.com/chart?$cht = p3&$chd = t:{1}&$chs = 600x300&$chl = {2}";
    $statsArray = [];
    $chartData = [];
    foreach ($statValue as $graphData => $plotData) {
        $statsArray[] = $plotData;
        $chartData[] = $graphData . " (" . number_format($plotData) . ")";
    }
    $visualData = number_format(array_sum($statsArray));
    $statName = "<img $src = \"" . str_replace(["{1}", "{2}"], [implode(",", $statsArray), implode("|", $chartData)], $statFormatted) . "\" $border = \"0\" $alt = \"" . $visualData . "\" $title = \"" . $visualData . "\" />";
    return $statName;
}

?>