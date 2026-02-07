<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function_54();
require "./ts_db_connect.php";
function_55();
$_FileData = base64_decode("R0lGODlhAQABAIAAAMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==") /* GIF89a\0\0�\0\0���\0\0\0!�\0\0\0\0,\0\0\0\0\0\0\0D\0;... */ /* GIF89a\0\0�\0\0���\0\0\0!�\0\0\0\0,\0\0\0\0\0\0\0D\0;... */ /* GIF89a\0\0�\0\0���\0\0\0!�\0\0\0\0,\0\0\0\0\0\0\0D\0;... */;
$_FileSize = strlen($_FileData);
header("Content-type: image/gif");
if (!(strpos($_SERVER["SERVER_SOFTWARE"], "Microsoft-IIS") !== false && strpos(php_sapi_name(), "cgi") !== false)) {
    header("Content-Length: " . $_FileSize);
    header("Connection: Close");
}
echo $_FileData;
flush();
$ip = var_218();
if (isset($_GET["ref"]) && !empty($_GET["ref"])) {
    $refer = htmlspecialchars($_GET["ref"]);
}
$browser = htmlspecialchars($_SERVER["HTTP_USER_AGENT"]);
$date = date("d/m/Y");
$day = date("d");
$month = date("m");
$year = date("Y");
$daysinmonth = date("t");
$getdaily = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT hits FROM stats_daily WHERE SUBSTRING(subdate, 1, 2)='" . $day . "'"));
$dailyhits = $getdaily["hits"] + 1;
$checkmonth = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT subdate FROM stats_daily WHERE SUBSTRING(subdate, 4, 2)='" . $month . "'"));
if ($checkmonth == 0) {
    mysqli_query($GLOBALS["DatabaseConnect"], "TRUNCATE TABLE stats_daily");
    for ($i = 1; $i < $daysinmonth + 1; $i++) {
        if (strlen($i) == 1) {
            $subdate = "0" . $i . "/" . $month . "/" . $year;
        } else {
            $subdate = $i . "/" . $month . "/" . $year;
        }
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_daily (subdate, hits, uniquehits) VALUES ('" . $subdate . "', '0', '0')");
    }
}
$checkday = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT subdate FROM stats_daily WHERE SUBSTRING(subdate, 1, 2)='" . $day . "'"));
if ($checkday == 1) {
    $countip = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM stats_visitors WHERE SUBSTRING(subdate, 1, 2)='" . $day . "' AND $ip = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "'"));
    $getuniquehits = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uniquehits FROM stats_daily WHERE SUBSTRING(subdate, 1, 2)='" . $day . "'"));
    if ($countip == 0) {
        $uniquehits = $getuniquehits["uniquehits"] + 1;
    } else {
        $uniquehits = $getuniquehits["uniquehits"];
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_daily SET $hits = '" . $dailyhits . "', $uniquehits = '" . $uniquehits . "' WHERE SUBSTRING(subdate, 1, 2)='" . $day . "'");
}
$getmonthly = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_monthly WHERE SUBSTRING(subdate, 4, 2)='" . $month . "'"));
$monthlyhits = $getmonthly["hits"] + 1;
$checkyear = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT subdate FROM stats_monthly WHERE SUBSTRING(subdate, 7, 4)='" . $year . "'"));
if ($checkyear == 0) {
    mysqli_query($GLOBALS["DatabaseConnect"], "TRUNCATE TABLE stats_monthly");
    for ($i = 1; $i < 13; $i++) {
        if (strlen($i) == 1) {
            $subdate = "01/0" . $i . "/" . $year;
        } else {
            $subdate = "01/" . $i . "/" . $year;
        }
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_monthly (subdate, hits, uniquehits) VALUES ('" . $subdate . "', '0', '0')");
    }
}
$checkmonth = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_monthly WHERE SUBSTRING(subdate, 4, 2)='" . $month . "'"));
if ($checkmonth == 1) {
    $countip = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM stats_visitors WHERE SUBSTRING(subdate, 1, 2)='" . $day . "' AND $ip = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "'"));
    $getuniquehits = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uniquehits FROM stats_monthly WHERE SUBSTRING(subdate, 4, 2)='" . $month . "'"));
    if ($countip == 0) {
        $uniquehits = $getuniquehits["uniquehits"] + 1;
    } else {
        $uniquehits = $getuniquehits["uniquehits"];
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_monthly SET $hits = '" . $monthlyhits . "', $uniquehits = '" . $uniquehits . "' WHERE SUBSTRING(subdate, 4, 2)='" . $month . "'");
}
$getyearly = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT hits FROM stats_yearly WHERE SUBSTRING(subdate, 7, 4)='" . $year . "'"));
$yearlyhits = $getyearly["hits"] + 1;
$checkyear = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_yearly WHERE SUBSTRING(subdate, 7, 4)='" . $year . "'"));
if ($checkyear == 0) {
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_yearly (subdate, hits, uniquehits) VALUES ('" . $date . "', '1', '1')");
} else {
    $countip = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM stats_visitors WHERE SUBSTRING(subdate, 1, 2)='" . $day . "' AND $ip = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "'"));
    $getuniquehits = mysqli_fetch_assoc(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uniquehits FROM stats_yearly WHERE SUBSTRING(subdate, 7, 4)='" . $year . "'"));
    if ($countip == 0) {
        $uniquehits = $getuniquehits["uniquehits"] + 1;
    } else {
        $uniquehits = $getuniquehits["uniquehits"];
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_yearly SET $hits = '" . $yearlyhits . "', $uniquehits = '" . $uniquehits . "' WHERE SUBSTRING(subdate, 7, 4)='" . $year . "'");
}
$os = false;
if (preg_match("@win@is", $browser)) {
    $os = "Windows";
}
if (preg_match("@mac@is", $browser)) {
    $os = "Mac";
}
if (preg_match("@linux@is", $browser)) {
    $os = "Linux";
}
if (preg_match("@OS\\/2@is", $browser)) {
    $os = "OS/2";
}
if (preg_match("@BeOS@is", $browser)) {
    $os = "BeOS";
}
if (preg_match("@unix@is", $browser) | preg_match("@SunOS@is", $browser) | preg_match("@FreeBSD@is", $browser) | preg_match("@IRIX@is", $browser) | preg_match("@HP\\\\-UX@is", $browser) | preg_match("@OSF@is", $browser) | preg_match("@AIX@is", $browser)) {
    $os = "Unix";
}
if ($os) {
    $oscheck = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT os FROM stats_os WHERE $os = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $os) . "'"));
    if ($oscheck == 0) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_os (os, hits) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $os) . "', '1')");
    } else {
        $checkunique = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM stats_visitors WHERE $ip = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "'"));
        if ($checkunique == 0) {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_os SET $hits = hits + 1 WHERE $os = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $os) . "'");
        }
    }
}
$DetectedBrowser = false;
if (preg_match("@chrome@is", $browser)) {
    $DetectedBrowser = "Chrome";
} else {
    if (preg_match("@microsoft internet explorer@is", $browser)) {
        $DetectedBrowser = "Internet Explorer";
    } else {
        if (preg_match("@msie@is", $browser)) {
            $DetectedBrowser = "Internet Explorer";
        } else {
            if (preg_match("@opera@is", $browser)) {
                $DetectedBrowser = "Opera";
            } else {
                if (preg_match("@Konqueror@is", $browser)) {
                    $DetectedBrowser = "Konqueror";
                } else {
                    if (preg_match("@icab@is", $browser)) {
                        $DetectedBrowser = "iCab";
                    } else {
                        if (preg_match("@Firefox@is", $browser)) {
                            $DetectedBrowser = "Firefox";
                        } else {
                            if (preg_match("@libwww@is", $browser)) {
                                $DetectedBrowser = "Lynx";
                            } else {
                                if (preg_match("@netscape@is", $browser)) {
                                    $DetectedBrowser = "Netscape";
                                } else {
                                    if (preg_match("@AOL@is", $browser)) {
                                        $DetectedBrowser = "AOL";
                                    } else {
                                        if (preg_match("@mozilla@is", $browser)) {
                                            $DetectedBrowser = "Mozilla";
                                        } else {
                                            if (preg_match("@safari@is", $browser)) {
                                                $DetectedBrowser = "Safari";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
if ($DetectedBrowser) {
    $browsercheck = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT browser FROM stats_browser WHERE $browser = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $DetectedBrowser) . "'"));
    if ($browsercheck == 0) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_browser (browser, hits) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $DetectedBrowser) . "', '1')");
    } else {
        $checkunique = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM stats_visitors WHERE $ip = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "'"));
        if ($checkunique == 0) {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_browser SET $hits = hits + 1 WHERE $browser = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $DetectedBrowser) . "'");
        }
    }
}
$visitorcheck = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT subdate FROM stats_visitors WHERE SUBSTRING(subdate, 1, 2)='" . $day . "'"));
if ($visitorcheck == 0) {
    mysqli_query($GLOBALS["DatabaseConnect"], "TRUNCATE TABLE stats_visitors");
}
$checkip = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM stats_visitors WHERE $ip = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "'"));
if ($checkip == 0) {
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_visitors (subdate, ip) VALUES ('" . $date . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "')");
}
if (isset($refer) && !preg_match("/" . basename($_SERVER["HTTP_HOST"]) . "/i", $refer)) {
    $referercheck = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_referer WHERE $referer = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $refer) . "'"));
    if ($referercheck == 0) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_referer ($referer, hits) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $refer) . "', '1')");
    } else {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_referer SET $hits = hits + 1 WHERE $referer = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $refer) . "'");
    }
}
if (isset($_COOKIE["users_resolution"]) && $_COOKIE["users_resolution"] != "") {
    $resolutioncheck = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM stats_resolution WHERE $resolution = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $_COOKIE["users_resolution"]) . "'"));
    if ($resolutioncheck == 0) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO stats_resolution (resolution, hits) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], htmlspecialchars($_COOKIE["users_resolution"])) . "', '1')");
    } else {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE stats_resolution SET $hits = hits + 1 WHERE $resolution = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $_COOKIE["users_resolution"]) . "'");
    }
}
function function_54()
{
    ini_set("display_errors", 0);
    ini_set("display_startup_errors", 0);
    error_reporting(32759);
    define("IN-TSSE-STAFF-PANEL", true);
}
function function_55()
{
    require "./../include/php_default_timezone_set.php";
}
function function_56($p, $k, $t, $pp)
{
    $p = $k * $t;
    $pp = $p + 333;
    return $p;
}
function getUserRealIpAddress()
{
    $ipAddress = $_SERVER["REMOTE_ADDR"];
    if (isset($_SERVER["HTTP_CLIENT_IP"])) {
        $ipAddress = $_SERVER["HTTP_CLIENT_IP"];
    } else {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && preg_match_all("#\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}#s", $_SERVER["HTTP_X_FORWARDED_FOR"], $var_220)) {
            foreach ($var_220[0] as $ip) {
                if (!preg_match("#^(10|172\\.16|192\\.168)\\.#", $ip)) {
                    $ipAddress = $ip;
                }
            }
        } else {
            if (isset($_SERVER["HTTP_FROM"])) {
                $ipAddress = $_SERVER["HTTP_FROM"];
            }
        }
    }
    return htmlspecialchars($ipAddress);
}

?>