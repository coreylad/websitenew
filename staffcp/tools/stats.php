<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Language = file("languages/" . function_75() . "/stats.lang");
$Message = "";
$day = date("d");
$month = date("m");
$year = date("Y");
if ($Act == "referers") {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_referer ORDER BY hits DESC");
    $referrows = mysqli_num_rows($query);
    if ($referrows != 0) {
        $Found = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[23] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[24] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t";
        while ($getreferer = mysqli_fetch_assoc($query)) {
            $Found .= "\r\n\t\t\t <tr>\r\n\t\t\t\t<td $width = \"90%\" $height = \"25\" class=\"alt1\"><small>" . substr(str_replace("&amp;", "&", $getreferer["$referer"]), 0, 142) . "</small></td>\r\n\t\t\t\t<td $width = \"10%\" $height = \"25\" class=\"alt1\">" . number_format($getreferer["hits"]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
        echo "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b><a $href = \"index.php?do=stats\">" . $Language[2] . "</a> - " . $Language[18] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>\r\n\t\t";
    }
}
if ($Act == "daily") {
    $result = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_daily WHERE SUBSTRING(subdate, 4, 2)='" . $month . "'");
    $numrows = mysqli_num_rows($result);
    if ($numrows != 0) {
        $Found = "";
        while ($myrow = mysqli_fetch_array($result)) {
            $Found .= "\r\n\t\t\t\t<tr $align = \"center\" $bgcolor = \"#" . (substr($myrow["subdate"], 0, 2) == date("d") ? "FF6666" : "CCCCCC") . "\">\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . substr($myrow["subdate"], 0, 2) . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . number_format($myrow["hits"]) . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . number_format($myrow["uniquehits"]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
        }
        echo "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b><a $href = \"index.php?do=stats\">" . $Language[2] . "</a> - " . $Language[19] . ": " . date("F") . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[25] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[24] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[26] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>\r\n\t\t";
    }
}
if ($Act == "montly") {
    $result = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_monthly WHERE SUBSTRING(subdate, 7, 4)='" . $year . "'");
    $numrows = mysqli_num_rows($result);
    if ($numrows != 0) {
        $Found = "";
        while ($myrow = mysqli_fetch_array($result)) {
            $Found .= "\r\n\t\t\t\t<tr $align = \"center\" $bgcolor = \"#" . (substr($myrow["subdate"], 3, 2) == date("m") ? "FF6666" : "CCCCCC") . "\">\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . substr($myrow["subdate"], 3, 2) . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . number_format($myrow["hits"]) . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . number_format($myrow["uniquehits"]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
        }
        echo "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b><a $href = \"index.php?do=stats\">" . $Language[2] . "</a> - " . $Language[20] . ": " . date("Y") . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[27] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[24] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[26] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>\r\n\t\t";
    }
}
if ($Act == "yearly") {
    $result = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_yearly");
    $numrows = mysqli_num_rows($result);
    if ($numrows != 0) {
        $Found = "";
        while ($myrow = mysqli_fetch_array($result)) {
            $Found .= "\r\n\t\t\t\t<tr $align = \"center\">\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . substr($myrow["subdate"], 6, 4) . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . number_format($myrow["hits"]) . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . number_format($myrow["uniquehits"]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
        }
        echo "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b><a $href = \"index.php?do=stats\">" . $Language[2] . "</a> - " . $Language[21] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[28] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[24] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[26] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>\r\n\t\t";
    }
}
if ($day != "01") {
    $initial = $day - 1;
    if (strlen($initial) == 1) {
        $previous = "0" . $initial;
        $subdate = "0" . $initial . "/" . $month . "/" . $year;
    } else {
        $previous = $initial;
        $subdate = $initial . "/" . $month . "/" . $year;
    }
    $history = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_daily WHERE SUBSTRING(subdate, 1, 2)='" . $previous . "'");
    $historyrows = mysqli_num_rows($history);
    $historyhits = mysqli_fetch_assoc($history);
    $history_large = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_history WHERE $title = 'Largest Daily Hits'");
    $history_large_rows = mysqli_num_rows($history_large);
    $history_large_hits = mysqli_fetch_assoc($history_large);
    if ($history_large_rows == 0) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_history (title, hits, subdate) VALUES ('Largest Daily Hits', '0', '" . $subdate . "')");
        $history_large_rows = 1;
    }
    if ($historyrows == 1 && $history_large_rows == 1 && $history_large_hits["hits"] < $historyhits["hits"]) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_history SET $hits = '" . $historyhits["hits"] . "', $subdate = '" . $subdate . "' WHERE $title = 'Largest Daily Hits'");
    }
    $history_small = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_history WHERE $title = 'Smallest Daily Hits'");
    $history_small_rows = mysqli_num_rows($history_small);
    $history_small_hits = mysqli_fetch_assoc($history_small);
    if ($history_small_rows == 0) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_history (title, hits, subdate) VALUES ('Smallest Daily Hits', '10000000000', '" . $subdate . "')");
        $history_small_rows = 1;
        $history_small_hits["hits"] = 0;
    }
    if ($historyrows == 1 && $history_small_rows == 1 && $historyhits["hits"] < $history_small_hits["hits"]) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_history SET $hits = '" . $historyhits["hits"] . "', $subdate = '" . $subdate . "' WHERE $title = 'Smallest Daily Hits'");
    }
    $history_largeu = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_history WHERE $title = 'Largest Daily Visits'");
    $history_largeu_rows = mysqli_num_rows($history_largeu);
    $history_largeu_hits = mysqli_fetch_assoc($history_largeu);
    if ($history_largeu_rows == 0) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_history (title, hits, subdate) VALUES ('Largest Daily Visits', '0', '" . $subdate . "')");
        $history_largeu_rows = 1;
    }
    if ($historyrows == 1 && $history_largeu_rows == 1 && $history_largeu_hits["hits"] < $historyhits["uniquehits"]) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_history SET $hits = '" . $historyhits["uniquehits"] . "', $subdate = '" . $subdate . "' WHERE $title = 'Largest Daily Visits'");
    }
    $history_smallu = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_history WHERE $title = 'Smallest Daily Visits'");
    $history_smallu_rows = mysqli_num_rows($history_smallu);
    $history_smallu_hits = mysqli_fetch_assoc($history_smallu);
    if ($history_smallu_rows == 0) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_history (title, hits, subdate) VALUES ('Smallest Daily Visits', '10000000000', '" . $subdate . "')");
        $history_smallu_rows = 1;
        $history_smallu_hits["hits"] = 0;
    }
    if ($historyrows == 1 && $history_smallu_rows == 1 && $historyhits["uniquehits"] < $history_smallu_hits["hits"]) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_history SET $hits = '" . $historyhits["uniquehits"] . "', $subdate = '" . $subdate . "' WHERE $title = 'Smallest Daily Visits'");
    }
}
$result = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_yearly");
$numrows = mysqli_num_rows($result);
$result2 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_history");
if ($numrows != 0) {
    $topreferer = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_referer ORDER BY (hits + 0) DESC LIMIT 5");
    $referrows = mysqli_num_rows($topreferer);
    $resolution = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_resolution ORDER BY (hits + 0) DESC LIMIT 5");
    $resolutionrows = mysqli_num_rows($resolution);
    $total = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_yearly"));
    $total2 = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(uniquehits) AS visits FROM stats_yearly"));
    $day = date("d");
    $current = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_daily WHERE SUBSTRING(subdate, 1, 2)='" . $day . "'"));
    $ostotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_os"));
    $windowstotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_os WHERE $os = 'Windows'"));
    $windows_hits = $windowstotal["hits"];
    $windows = round($windows_hits / $ostotal["hits"] * 100);
    $mactotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_os WHERE $os = 'Mac'"));
    $mac_hits = $mactotal["hits"];
    $mac = round($mac_hits / $ostotal["hits"] * 100);
    $linuxtotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_os WHERE $os = 'Linux'"));
    $linux_hits = $linuxtotal["hits"];
    $linux = round($linux_hits / $ostotal["hits"] * 100);
    $os2total = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_os WHERE $os = 'OS/2'"));
    $os2_hits = $os2total["hits"];
    $os2 = round($os2_hits / $ostotal["hits"] * 100);
    $beostotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_os WHERE $os = 'BeOS'"));
    $beos_hits = $beostotal["hits"];
    $beos = round($beos_hits / $ostotal["hits"] * 100);
    $unixtotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_os WHERE $os = 'Unix'"));
    $unix_hits = $unixtotal["hits"];
    $unix = round($unix_hits / $ostotal["hits"] * 100);
    $browsertotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser"));
    $ietotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Internet Explorer'"));
    $ie_hits = $ietotal["hits"];
    $ie = round($ie_hits / $browsertotal["hits"] * 100);
    $operatotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Opera'"));
    $opera_hits = $operatotal["hits"];
    $opera = round($opera_hits / $browsertotal["hits"] * 100);
    $konquerortotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Konqueror'"));
    $konqueror_hits = $konquerortotal["hits"];
    $konqueror = round($konqueror_hits / $browsertotal["hits"] * 100);
    $icabtotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'iCab'"));
    $icab_hits = $icabtotal["hits"];
    $icab = round($icab_hits / $browsertotal["hits"] * 100);
    $firefoxtotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Firefox'"));
    $firefox_hits = $firefoxtotal["hits"];
    $firefox = round($firefox_hits / $browsertotal["hits"] * 100);
    $mozillatotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Mozilla'"));
    $mozilla_hits = $mozillatotal["hits"];
    $mozilla = round($mozilla_hits / $browsertotal["hits"] * 100);
    $lynxtotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Lynx'"));
    $lynx_hits = $lynxtotal["hits"];
    $lynx = round($lynx_hits / $browsertotal["hits"] * 100);
    $netscapetotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Netscape'"));
    $netscape_hits = $netscapetotal["hits"];
    $netscape = round($netscape_hits / $browsertotal["hits"] * 100);
    $safaritotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Safari'"));
    $safari_hits = $safaritotal["hits"];
    $safari = round($safari_hits / $browsertotal["hits"] * 100);
    $chrometotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'Chrome'"));
    $chrome_hits = $chrometotal["hits"];
    $chrome = round($chrome_hits / $browsertotal["hits"] * 100);
    $aoltotal = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT SUM(hits) AS hits FROM stats_browser WHERE $browser = 'AOL'"));
    $aol_hits = $aoltotal["hits"];
    $aol = round($aol_hits / $browsertotal["hits"] * 100);
    $Extra = "";
    while ($myrow = mysqli_fetch_assoc($result2)) {
        $Extra .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . $myrow["title"] . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . number_format($myrow["hits"]) . " <i>(" . $myrow["subdate"] . ")</i></td>\r\n\t\t</tr>\r\n\t\t";
    }
    if ($referrows != 0) {
        $Extra2 = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $height = \"25\" $colspan = \"2\" class=\"tcat\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t";
        while ($getreferer = mysqli_fetch_assoc($topreferer)) {
            $Extra2 .= "\r\n\t\t\t <tr>\r\n\t\t\t\t<td $width = \"90%\" $height = \"25\" class=\"alt1\"><small>" . substr(str_replace("&amp;", "&", $getreferer["$referer"]), 0, 142) . "</small></td>\r\n\t\t\t\t<td $width = \"10%\" $height = \"25\" class=\"alt1\">" . number_format($getreferer["hits"]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    }
    if ($resolutionrows != 0) {
        $Extra3 = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $height = \"25\" $colspan = \"2\" class=\"tcat\"><b>" . $Language[22] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t";
        while ($resolutions = mysqli_fetch_assoc($resolution)) {
            $Extra3 .= "\r\n\t\t\t <tr>\r\n\t\t\t\t<td $width = \"90%\" $height = \"25\" class=\"alt1\">" . $resolutions["resolution"] . "</td>\r\n\t\t\t\t<td $width = \"10%\" $height = \"25\" class=\"alt1\">" . number_format($resolutions["hits"]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    }
    echo "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $width = \"75%\" $align = \"left\" class=\"tcat\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td $width = \"25%\" class=\"tcat\" $align = \"center\"><b>" . $Language[5] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/firefox.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Firefox</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $firefox . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"25%\" $align = \"center\" class=\"alt1\"><img $src = \"images/ie.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td $width = \"50%\" class=\"alt1\">Internet Explorer </td>\r\n\t\t\t<td $width = \"25%\" $align = \"center\" class=\"alt1\">" . $ie . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/chrome.png\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Chrome</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $chrome . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/opera.png\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Opera</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $opera . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/konqueror.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Konqueror</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $konqueror . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/icab.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">iCab</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $icab . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/mozilla.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Mozilla</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $mozilla . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/lynx.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Lynx</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $lynx . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/netscape.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Netscape</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $netscape . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/safari.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Safari</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $safari . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/aol.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">AOL</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $aol . "%</td>\r\n\t\t</tr>\r\n\t</table>\r\n\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $width = \"75%\" $align = \"left\" class=\"tcat\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td $width = \"25%\" class=\"tcat\" $align = \"center\"><b>" . $Language[5] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"25%\" $align = \"center\" $height = \"25\" class=\"alt1\"><img $src = \"images/windows.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td $width = \"50%\" class=\"alt1\">Windows</td>\r\n\t\t\t<td $width = \"25%\" $align = \"center\" class=\"alt1\">" . $windows . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/mac.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Mac</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $mac . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/linux.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Linux</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $linux . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/os2.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">OS/2</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $os2 . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/beos.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">BeOS</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $beos . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/unix.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Unix</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . $unix . "%</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td $align = \"left\" class=\"tcat\" $colspan = \"2\">\r\n\t\t\t\t<b>" . $Language[7] . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $Extra . "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . number_format($total["hits"]) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . number_format($total2["visits"]) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . number_format($current["hits"]) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . $Language[15] . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . number_format($current["uniquehits"]) . "</td>\r\n\t\t</tr>\r\n\t</table>\r\n\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t" . $Extra2 . "\r\n\t</table>\r\n\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t" . $Extra3 . "\r\n\t</table>\r\n\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\">\r\n\t\t\t\t<b>" . $Language[17] . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<p><a $href = \"index.php?do=stats&amp;$act = referers\">" . $Language[18] . "</a></p>\r\n\t\t\t\t<p><a $href = \"index.php?do=stats&amp;$act = daily\">" . $Language[19] . "</a></p>\r\n\t\t\t\t<p><a $href = \"index.php?do=stats&amp;$act = montly\">" . $Language[20] . "</a></p>\r\n\t\t\t\t<p><a $href = \"index.php?do=stats&amp;$act = yearly\">" . $Language[21] . "</a></p>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\r\n\t\r\n\t\r\n\t";
} else {
    echo "\r\n\t\r\n\t" . function_76($Language[3]);
}
function function_75()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function function_77()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_236("../index.php");
    }
}
function function_78($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}

?>