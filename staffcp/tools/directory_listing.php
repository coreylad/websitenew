<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/directory_listing.lang");
$Message = "";
$FileList = "";
$Files = [];
$Directories = [];
$CurrentDirectory = isset($_GET["d"]) ? trim($_GET["d"]) : $_SERVER["DOCUMENT_ROOT"];
$supportedimages = ["gif", "png", "jpeg", "jpg"];
if (substr($CurrentDirectory, -1, 1) != "/") {
    $CurrentDirectory = $CurrentDirectory . "/";
}
if (isset($_GET["f"])) {
    ob_start();
    highlight_file($_GET["f"]);
    $Contents = ob_get_contents();
    ob_end_clean();
    echo "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\"align=\"center\">\r\n\t\t\t\t" . htmlspecialchars($_GET["f"]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Contents . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
}
$FileTypes = ["png" => "jpg.gif", "jpeg" => "jpg.gif", "bmp" => "jpg.gif", "jpg" => "jpg.gif", "gif" => "gif.gif", "zip" => "archive.png", "rar" => "archive.png", "tar" => "archive.png", "exe" => "exe.gif", "setup" => "setup.gif", "txt" => "text.png", "htm" => "html.gif", "html" => "html.gif", "fla" => "fla.gif", "swf" => "swf.gif", "xls" => "xls.png", "doc" => "doc.png", "sig" => "sig.gif", "fh10" => "fh10.gif", "pdf" => "pdf.png", "psd" => "psd.gif", "rm" => "real.gif", "mpg" => "video.gif", "mpeg" => "video.gif", "mov" => "video2.gif", "avi" => "video.gif", "eps" => "eps.gif", "gz" => "archive.png", "asc" => "sig.gif", "php" => "php.png", "css" => "css.png", "js" => "script.png", "dat" => "script_save.png", "xml" => "script_code.png", "htaccess" => "script_key.png", "ico" => "script_palette.png"];
$ScanDirectory = scandir($CurrentDirectory);
foreach ($ScanDirectory as $ScanRes) {
    if (is_dir($CurrentDirectory . $ScanRes)) {
        if ($ScanRes != ".") {
            $Directories[] = $ScanRes;
        }
    } else {
        $Files[] = $ScanRes;
    }
}
foreach ($Directories as $Directory) {
    $FileList .= "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . ($Directory == ".." ? "<img $src = \"images/filetypes/dirup.png\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" />" : "<img $src = \"images/filetypes/folder.png\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" />") . " <u><b><font $size = \"2\"><a $href = \"index.php?do=directory_listing&amp;$d = " . $CurrentDirectory . $Directory . "\">" . $Directory . "</a></font></b></u>\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\t\t\t\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\t\t\t\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\t\t\t\r\n\t\t</td>\r\n\t</tr>";
}
foreach ($Files as $File) {
    $Icon = "unknown.png";
    $Ext = function_149($Directory . "/" . $File);
    if (isset($FileTypes[$Ext])) {
        $Icon = $FileTypes[$Ext];
    }
    $FileList .= "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<img $src = \"images/filetypes/" . $Icon . "\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" /> <a $href = \"index.php?do=directory_listing&amp;$f = " . $CurrentDirectory . $File . "\">" . $File . "</a>\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . formatBytes(filesize($CurrentDirectory . $File)) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . formatTimestamp(filemtime($CurrentDirectory . $File)) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . var_425($CurrentDirectory . $File) . "\r\n\t\t</td>\r\n\t</tr>";
}
echo "\t\t\t\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"4\" $align = \"center\">\r\n\t\t\t<span $style = \"float: right;\"><small>" . $CurrentDirectory . "</small></span>\r\n\t\t\t" . $Language[1] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[4] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[5] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $FileList . "\r\n</table>\r\n";
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
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function formatBytes($bytes = 0)
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 0) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 0, 2) . " TB";
}
function formatTimestamp($timestamp = "")
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, $timestamp);
}
function function_149($file = "")
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function function_150($file)
{
    $var_426 = fileperms($file);
    if (($var_426 & 49152) == 49152) {
        $info = "s";
    } else {
        if (($var_426 & 40960) == 40960) {
            $info = "l";
        } else {
            if (($var_426 & 32768) == 32768) {
                $info = "-";
            } else {
                if (($var_426 & 24576) == 24576) {
                    $info = "b";
                } else {
                    if (($var_426 & 16384) == 16384) {
                        $info = "d";
                    } else {
                        if (($var_426 & 8192) == 8192) {
                            $info = "c";
                        } else {
                            if (($var_426 & 4096) == 4096) {
                                $info = "p";
                            } else {
                                $info = "u";
                            }
                        }
                    }
                }
            }
        }
    }
    $info .= $var_426 & 256 ? "r" : "-";
    $info .= $var_426 & 128 ? "w" : "-";
    $info .= $var_426 & 64 ? $var_426 & 2048 ? "s" : "x" : ($var_426 & 2048 ? "S" : "-");
    $info .= $var_426 & 32 ? "r" : "-";
    $info .= $var_426 & 16 ? "w" : "-";
    $info .= $var_426 & 8 ? $var_426 & 1024 ? "s" : "x" : ($var_426 & 1024 ? "S" : "-");
    $info .= $var_426 & 4 ? "r" : "-";
    $info .= $var_426 & 2 ? "w" : "-";
    $info .= $var_426 & 1 ? $var_426 & 512 ? "t" : "x" : ($var_426 & 512 ? "T" : "-");
    return $info;
}

?>