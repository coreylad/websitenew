<?php
declare(strict_types=1);

checkStaffAuthentication();

try {
    $pdo = getPDOConnection();
    $Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
    $Language = file("languages/" . getStaffLanguage() . "/stats.lang");
    $Message = "";
    $day = date("d");
    $month = date("m");
    $year = date("Y");
    
    if ($Act == "referers") {
        $stmt = $pdo->query("SELECT * FROM stats_referer ORDER BY hits DESC");
        $referrows = $stmt->rowCount();
        if ($referrows != 0) {
            $Found = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[23], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[24], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t";
            while ($getreferer = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $referer = htmlspecialchars(substr(str_replace("&amp;", "&", $getreferer["referer"] ?? ''), 0, 142), ENT_QUOTES, 'UTF-8');
                $hits = htmlspecialchars(number_format((int)$getreferer["hits"]), ENT_QUOTES, 'UTF-8');
                $Found .= "\r\n\t\t\t <tr>\r\n\t\t\t\t<td $width = \"90%\" $height = \"25\" class=\"alt1\"><small>" . $referer . "</small></td>\r\n\t\t\t\t<td $width = \"10%\" $height = \"25\" class=\"alt1\">" . $hits . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
            }
            echo "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b><a $href = \"index.php?do=stats\">" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "</a> - " . htmlspecialchars($Language[18], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>\r\n\t\t";
        }
    }
    if ($Act == "daily") {
        $stmt = $pdo->prepare("SELECT * FROM stats_daily WHERE SUBSTRING(subdate, 4, 2) = ?");
        $stmt->execute([$month]);
        $numrows = $stmt->rowCount();
        if ($numrows != 0) {
            $Found = "";
            while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $bgcolor = (substr($myrow["subdate"], 0, 2) == date("d") ? "FF6666" : "CCCCCC");
                $subdateDay = htmlspecialchars(substr($myrow["subdate"], 0, 2), ENT_QUOTES, 'UTF-8');
                $hits = htmlspecialchars(number_format((int)$myrow["hits"]), ENT_QUOTES, 'UTF-8');
                $uniquehits = htmlspecialchars(number_format((int)$myrow["uniquehits"]), ENT_QUOTES, 'UTF-8');
                $Found .= "\r\n\t\t\t\t<tr $align = \"center\" $bgcolor = \"#" . $bgcolor . "\">\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $subdateDay . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $hits . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $uniquehits . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
            echo "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b><a $href = \"index.php?do=stats\">" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "</a> - " . htmlspecialchars($Language[19], ENT_QUOTES, 'UTF-8') . ": " . htmlspecialchars(date("F"), ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[25], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[24], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[26], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>\r\n\t\t";
        }
    }
    if ($Act == "montly") {
        $stmt = $pdo->prepare("SELECT * FROM stats_monthly WHERE SUBSTRING(subdate, 7, 4) = ?");
        $stmt->execute([$year]);
        $numrows = $stmt->rowCount();
        if ($numrows != 0) {
            $Found = "";
            while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $bgcolor = (substr($myrow["subdate"], 3, 2) == date("m") ? "FF6666" : "CCCCCC");
                $subdateMonth = htmlspecialchars(substr($myrow["subdate"], 3, 2), ENT_QUOTES, 'UTF-8');
                $hits = htmlspecialchars(number_format((int)$myrow["hits"]), ENT_QUOTES, 'UTF-8');
                $uniquehits = htmlspecialchars(number_format((int)$myrow["uniquehits"]), ENT_QUOTES, 'UTF-8');
                $Found .= "\r\n\t\t\t\t<tr $align = \"center\" $bgcolor = \"#" . $bgcolor . "\">\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $subdateMonth . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $hits . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $uniquehits . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
            echo "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b><a $href = \"index.php?do=stats\">" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "</a> - " . htmlspecialchars($Language[20], ENT_QUOTES, 'UTF-8') . ": " . htmlspecialchars(date("Y"), ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[27], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[24], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[26], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>\r\n\t\t";
        }
    }
    if ($Act == "yearly") {
        $stmt = $pdo->query("SELECT * FROM stats_yearly");
        $numrows = $stmt->rowCount();
        if ($numrows != 0) {
            $Found = "";
            while ($myrow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subdateYear = htmlspecialchars(substr($myrow["subdate"], 6, 4), ENT_QUOTES, 'UTF-8');
                $hits = htmlspecialchars(number_format((int)$myrow["hits"]), ENT_QUOTES, 'UTF-8');
                $uniquehits = htmlspecialchars(number_format((int)$myrow["uniquehits"]), ENT_QUOTES, 'UTF-8');
                $Found .= "\r\n\t\t\t\t<tr $align = \"center\">\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $subdateYear . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $hits . "</td>\r\n\t\t\t\t\t<td class=\"text\" $height = \"25\">" . $uniquehits . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
            echo "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\"><b><a $href = \"index.php?do=stats\">" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "</a> - " . htmlspecialchars($Language[21], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[28], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[24], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . htmlspecialchars($Language[26], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>\r\n\t\t";
        }
    }
    if ($day != "01") {
        $initial = (int)$day - 1;
        if (strlen((string)$initial) == 1) {
            $previous = "0" . $initial;
            $subdate = "0" . $initial . "/" . $month . "/" . $year;
        } else {
            $previous = (string)$initial;
            $subdate = $initial . "/" . $month . "/" . $year;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM stats_daily WHERE SUBSTRING(subdate, 1, 2) = ?");
        $stmt->execute([$previous]);
        $historyrows = $stmt->rowCount();
        $historyhits = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT * FROM stats_history WHERE title = ?");
        $stmt->execute(['Largest Daily Hits']);
        $history_large_rows = $stmt->rowCount();
        $history_large_hits = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($history_large_rows == 0) {
            $stmt = $pdo->prepare("INSERT INTO stats_history (title, hits, subdate) VALUES (?, ?, ?)");
            $stmt->execute(['Largest Daily Hits', 0, $subdate]);
            $history_large_rows = 1;
        }
        
        if ($historyrows == 1 && $history_large_rows == 1 && $history_large_hits["hits"] < $historyhits["hits"]) {
            $stmt = $pdo->prepare("UPDATE stats_history SET hits = ?, subdate = ? WHERE title = ?");
            $stmt->execute([$historyhits["hits"], $subdate, 'Largest Daily Hits']);
        }
        
        $stmt = $pdo->prepare("SELECT * FROM stats_history WHERE title = ?");
        $stmt->execute(['Smallest Daily Hits']);
        $history_small_rows = $stmt->rowCount();
        $history_small_hits = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($history_small_rows == 0) {
            $stmt = $pdo->prepare("INSERT INTO stats_history (title, hits, subdate) VALUES (?, ?, ?)");
            $stmt->execute(['Smallest Daily Hits', 10000000000, $subdate]);
            $history_small_rows = 1;
            $history_small_hits["hits"] = 0;
        }
        
        if ($historyrows == 1 && $history_small_rows == 1 && $historyhits["hits"] < $history_small_hits["hits"]) {
            $stmt = $pdo->prepare("UPDATE stats_history SET hits = ?, subdate = ? WHERE title = ?");
            $stmt->execute([$historyhits["hits"], $subdate, 'Smallest Daily Hits']);
        }
        
        $stmt = $pdo->prepare("SELECT * FROM stats_history WHERE title = ?");
        $stmt->execute(['Largest Daily Visits']);
        $history_largeu_rows = $stmt->rowCount();
        $history_largeu_hits = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($history_largeu_rows == 0) {
            $stmt = $pdo->prepare("INSERT INTO stats_history (title, hits, subdate) VALUES (?, ?, ?)");
            $stmt->execute(['Largest Daily Visits', 0, $subdate]);
            $history_largeu_rows = 1;
        }
        
        if ($historyrows == 1 && $history_largeu_rows == 1 && $history_largeu_hits["hits"] < $historyhits["uniquehits"]) {
            $stmt = $pdo->prepare("UPDATE stats_history SET hits = ?, subdate = ? WHERE title = ?");
            $stmt->execute([$historyhits["uniquehits"], $subdate, 'Largest Daily Visits']);
        }
        
        $stmt = $pdo->prepare("SELECT * FROM stats_history WHERE title = ?");
        $stmt->execute(['Smallest Daily Visits']);
        $history_smallu_rows = $stmt->rowCount();
        $history_smallu_hits = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($history_smallu_rows == 0) {
            $stmt = $pdo->prepare("INSERT INTO stats_history (title, hits, subdate) VALUES (?, ?, ?)");
            $stmt->execute(['Smallest Daily Visits', 10000000000, $subdate]);
            $history_smallu_rows = 1;
            $history_smallu_hits["hits"] = 0;
        }
        
        if ($historyrows == 1 && $history_smallu_rows == 1 && $historyhits["uniquehits"] < $history_smallu_hits["hits"]) {
            $stmt = $pdo->prepare("UPDATE stats_history SET hits = ?, subdate = ? WHERE title = ?");
            $stmt->execute([$historyhits["uniquehits"], $subdate, 'Smallest Daily Visits']);
        }
    }
    $result = $pdo->query("SELECT * FROM stats_yearly");
    $numrows = $result->rowCount();
    $result2 = $pdo->query("SELECT * FROM stats_history");
    
    if ($numrows != 0) {
        $topreferer = $pdo->query("SELECT * FROM stats_referer ORDER BY (hits + 0) DESC LIMIT 5");
        $referrows = $topreferer->rowCount();
        
        $resolution = $pdo->query("SELECT * FROM stats_resolution ORDER BY (hits + 0) DESC LIMIT 5");
        $resolutionrows = $resolution->rowCount();
        
        $total = $pdo->query("SELECT SUM(hits) AS hits FROM stats_yearly")->fetch(PDO::FETCH_ASSOC);
        $total2 = $pdo->query("SELECT SUM(uniquehits) AS visits FROM stats_yearly")->fetch(PDO::FETCH_ASSOC);
        
        $day = date("d");
        $stmt = $pdo->prepare("SELECT * FROM stats_daily WHERE SUBSTRING(subdate, 1, 2) = ?");
        $stmt->execute([$day]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $ostotal = $pdo->query("SELECT SUM(hits) AS hits FROM stats_os")->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT SUM(hits) AS hits FROM stats_os WHERE os = ?");
        $stmt->execute(['Windows']);
        $windowstotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $windows_hits = $windowstotal["hits"] ?? 0;
        $windows = $ostotal["hits"] > 0 ? round($windows_hits / $ostotal["hits"] * 100) : 0;
        
        $stmt->execute(['Mac']);
        $mactotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $mac_hits = $mactotal["hits"] ?? 0;
        $mac = $ostotal["hits"] > 0 ? round($mac_hits / $ostotal["hits"] * 100) : 0;
        
        $stmt->execute(['Linux']);
        $linuxtotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $linux_hits = $linuxtotal["hits"] ?? 0;
        $linux = $ostotal["hits"] > 0 ? round($linux_hits / $ostotal["hits"] * 100) : 0;
        
        $stmt->execute(['OS/2']);
        $os2total = $stmt->fetch(PDO::FETCH_ASSOC);
        $os2_hits = $os2total["hits"] ?? 0;
        $os2 = $ostotal["hits"] > 0 ? round($os2_hits / $ostotal["hits"] * 100) : 0;
        
        $stmt->execute(['BeOS']);
        $beostotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $beos_hits = $beostotal["hits"] ?? 0;
        $beos = $ostotal["hits"] > 0 ? round($beos_hits / $ostotal["hits"] * 100) : 0;
        
        $stmt->execute(['Unix']);
        $unixtotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $unix_hits = $unixtotal["hits"] ?? 0;
        $unix = $ostotal["hits"] > 0 ? round($unix_hits / $ostotal["hits"] * 100) : 0;
        
        $browsertotal = $pdo->query("SELECT SUM(hits) AS hits FROM stats_browser")->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT SUM(hits) AS hits FROM stats_browser WHERE browser = ?");
        $stmt->execute(['Internet Explorer']);
        $ietotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $ie_hits = $ietotal["hits"] ?? 0;
        $ie = $browsertotal["hits"] > 0 ? round($ie_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['Opera']);
        $operatotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $opera_hits = $operatotal["hits"] ?? 0;
        $opera = $browsertotal["hits"] > 0 ? round($opera_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['Konqueror']);
        $konquerortotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $konqueror_hits = $konquerortotal["hits"] ?? 0;
        $konqueror = $browsertotal["hits"] > 0 ? round($konqueror_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['iCab']);
        $icabtotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $icab_hits = $icabtotal["hits"] ?? 0;
        $icab = $browsertotal["hits"] > 0 ? round($icab_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['Firefox']);
        $firefoxtotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $firefox_hits = $firefoxtotal["hits"] ?? 0;
        $firefox = $browsertotal["hits"] > 0 ? round($firefox_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['Mozilla']);
        $mozillatotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $mozilla_hits = $mozillatotal["hits"] ?? 0;
        $mozilla = $browsertotal["hits"] > 0 ? round($mozilla_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['Lynx']);
        $lynxtotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $lynx_hits = $lynxtotal["hits"] ?? 0;
        $lynx = $browsertotal["hits"] > 0 ? round($lynx_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['Netscape']);
        $netscapetotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $netscape_hits = $netscapetotal["hits"] ?? 0;
        $netscape = $browsertotal["hits"] > 0 ? round($netscape_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['Safari']);
        $safaritotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $safari_hits = $safaritotal["hits"] ?? 0;
        $safari = $browsertotal["hits"] > 0 ? round($safari_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['Chrome']);
        $chrometotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $chrome_hits = $chrometotal["hits"] ?? 0;
        $chrome = $browsertotal["hits"] > 0 ? round($chrome_hits / $browsertotal["hits"] * 100) : 0;
        
        $stmt->execute(['AOL']);
        $aoltotal = $stmt->fetch(PDO::FETCH_ASSOC);
        $aol_hits = $aoltotal["hits"] ?? 0;
        $aol = $browsertotal["hits"] > 0 ? round($aol_hits / $browsertotal["hits"] * 100) : 0;
        
        $Extra = "";
        while ($myrow = $result2->fetch(PDO::FETCH_ASSOC)) {
            $title = htmlspecialchars($myrow["title"], ENT_QUOTES, 'UTF-8');
            $hits = htmlspecialchars(number_format((int)$myrow["hits"]), ENT_QUOTES, 'UTF-8');
            $subdate = htmlspecialchars($myrow["subdate"], ENT_QUOTES, 'UTF-8');
            $Extra .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . $title . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . $hits . " <i>(" . $subdate . ")</i></td>\r\n\t\t</tr>\r\n\t\t";
        }
        
        $Extra2 = "";
        if ($referrows != 0) {
            $Extra2 = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $height = \"25\" $colspan = \"2\" class=\"tcat\"><b>" . htmlspecialchars($Language[16], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t";
            while ($getreferer = $topreferer->fetch(PDO::FETCH_ASSOC)) {
                $referer = htmlspecialchars(substr(str_replace("&amp;", "&", $getreferer["referer"] ?? ''), 0, 142), ENT_QUOTES, 'UTF-8');
                $hits = htmlspecialchars(number_format((int)$getreferer["hits"]), ENT_QUOTES, 'UTF-8');
                $Extra2 .= "\r\n\t\t\t <tr>\r\n\t\t\t\t<td $width = \"90%\" $height = \"25\" class=\"alt1\"><small>" . $referer . "</small></td>\r\n\t\t\t\t<td $width = \"10%\" $height = \"25\" class=\"alt1\">" . $hits . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
            }
        }
        
        $Extra3 = "";
        if ($resolutionrows != 0) {
            $Extra3 = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $height = \"25\" $colspan = \"2\" class=\"tcat\"><b>" . htmlspecialchars($Language[22], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t";
            while ($resolutions = $resolution->fetch(PDO::FETCH_ASSOC)) {
                $res = htmlspecialchars($resolutions["resolution"], ENT_QUOTES, 'UTF-8');
                $hits = htmlspecialchars(number_format((int)$resolutions["hits"]), ENT_QUOTES, 'UTF-8');
                $Extra3 .= "\r\n\t\t\t <tr>\r\n\t\t\t\t<td $width = \"90%\" $height = \"25\" class=\"alt1\">" . $res . "</td>\r\n\t\t\t\t<td $width = \"10%\" $height = \"25\" class=\"alt1\">" . $hits . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
            }
        }
        echo "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $width = \"75%\" $align = \"left\" class=\"tcat\"><b>" . htmlspecialchars($Language[4], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td $width = \"25%\" class=\"tcat\" $align = \"center\"><b>" . htmlspecialchars($Language[5], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/firefox.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Firefox</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$firefox, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"25%\" $align = \"center\" class=\"alt1\"><img $src = \"images/ie.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td $width = \"50%\" class=\"alt1\">Internet Explorer </td>\r\n\t\t\t<td $width = \"25%\" $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$ie, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/chrome.png\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Chrome</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$chrome, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/opera.png\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Opera</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$opera, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/konqueror.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Konqueror</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$konqueror, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/icab.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">iCab</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$icab, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/mozilla.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Mozilla</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$mozilla, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/lynx.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Lynx</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$lynx, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/netscape.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Netscape</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$netscape, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/safari.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Safari</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$safari, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/aol.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">AOL</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$aol, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t</table>\r\n\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $width = \"75%\" $align = \"left\" class=\"tcat\"><b>" . htmlspecialchars($Language[6], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td $width = \"25%\" class=\"tcat\" $align = \"center\"><b>" . htmlspecialchars($Language[5], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"25%\" $align = \"center\" $height = \"25\" class=\"alt1\"><img $src = \"images/windows.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td $width = \"50%\" class=\"alt1\">Windows</td>\r\n\t\t\t<td $width = \"25%\" $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$windows, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/mac.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Mac</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$mac, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/linux.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Linux</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$linux, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/os2.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">OS/2</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$os2, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/beos.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">BeOS</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$beos, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\" class=\"alt1\"><img $src = \"images/unix.gif\" $width = \"35\" $height = \"35\"></td>\r\n\t\t\t<td class=\"alt1\">Unix</td>\r\n\t\t\t<td $align = \"center\" class=\"alt1\">" . htmlspecialchars((string)$unix, ENT_QUOTES, 'UTF-8') . "%</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td $align = \"left\" class=\"tcat\" $colspan = \"2\">\r\n\t\t\t\t<b>" . htmlspecialchars($Language[7], ENT_QUOTES, 'UTF-8') . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $Extra . "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . htmlspecialchars($Language[12], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . htmlspecialchars(number_format((int)($total["hits"] ?? 0)), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . htmlspecialchars($Language[13], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . htmlspecialchars(number_format((int)($total2["visits"] ?? 0)), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . htmlspecialchars($Language[14], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . htmlspecialchars(number_format((int)($current["hits"] ?? 0)), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"40%\" $height = \"25\" class=\"alt1\"><b>" . htmlspecialchars($Language[15], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t\t\t<td $width = \"60%\" $height = \"25\" class=\"alt1\">" . htmlspecialchars(number_format((int)($current["uniquehits"] ?? 0)), ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t</tr>\r\n\t</table>\r\n\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t" . $Extra2 . "\r\n\t</table>\r\n\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t" . $Extra3 . "\r\n\t</table>\r\n\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\">\r\n\t\t\t\t<b>" . htmlspecialchars($Language[17], ENT_QUOTES, 'UTF-8') . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<p><a $href = \"index.php?do=stats&amp;$act = referers\">" . htmlspecialchars($Language[18], ENT_QUOTES, 'UTF-8') . "</a></p>\r\n\t\t\t\t<p><a $href = \"index.php?do=stats&amp;$act = daily\">" . htmlspecialchars($Language[19], ENT_QUOTES, 'UTF-8') . "</a></p>\r\n\t\t\t\t<p><a $href = \"index.php?do=stats&amp;$act = montly\">" . htmlspecialchars($Language[20], ENT_QUOTES, 'UTF-8') . "</a></p>\r\n\t\t\t\t<p><a $href = \"index.php?do=stats&amp;$act = yearly\">" . htmlspecialchars($Language[21], ENT_QUOTES, 'UTF-8') . "</a></p>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\r\n\t\r\n\t\r\n\t";
    } else {
        echo "\r\n\t\r\n\t" . showAlertError(htmlspecialchars($Language[3], ENT_QUOTES, 'UTF-8'));
    }
} catch (PDOException $e) {
    error_log("Database error in stats.php: " . $e->getMessage());
    echo "\r\n\t\r\n\t" . showAlertError("A database error occurred. Please try again later.");
} catch (Exception $e) {
    error_log("Error in stats.php: " . $e->getMessage());
    echo "\r\n\t\r\n\t" . showAlertError("An error occurred. Please try again later.");
}

function getPDOConnection(): PDO
{
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    
    if (!isset($GLOBALS["DatabaseConnect"])) {
        throw new Exception("Database connection not available");
    }
    
    $mysqli = $GLOBALS["DatabaseConnect"];
    $host = ini_get("mysqli.default_host") ?: "localhost";
    $dbname = "";
    $username = ini_get("mysqli.default_user") ?: "root";
    $password = ini_get("mysqli.default_pw") ?: "";
    
    if (defined('DB_HOST')) {
        $host = DB_HOST;
    }
    if (defined('DB_NAME')) {
        $dbname = DB_NAME;
    }
    if (defined('DB_USER')) {
        $username = DB_USER;
    }
    if (defined('DB_PASS')) {
        $password = DB_PASS;
    }
    
    try {
        $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $GLOBALS['pdo'] = $pdo;
        return $pdo;
    } catch (PDOException $e) {
        error_log("PDO Connection failed: " . $e->getMessage());
        throw $e;
    }
}

function getStaffLanguage(): string
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}

function checkStaffAuthentication(): void
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}

function redirectTo(string $url): void
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\" />\r\n\t\t</noscript>";
    }
    exit;
}

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}

?>