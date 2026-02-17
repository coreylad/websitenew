<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

$Language = loadStaffLanguage('directory_listing');
$Message = "";
$FileList = "";
$Files = [];
$Directories = [];
$CurrentDirectory = isset($_GET["d"]) ? trim($_GET["d"]) : $_SERVER["DOCUMENT_ROOT"];
$supportedimages = ["gif", "png", "jpeg", "jpg"];
if (substr($CurrentDirectory, -1, 1) !== "/") {
    $CurrentDirectory = $CurrentDirectory . "/";
}
if (isset($_GET["f"])) {
    ob_start();
    highlight_file($_GET["f"]);
    $Contents = ob_get_contents();
    ob_end_clean();
    echo "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\"align=\"center\">\r\n\t\t\t\t" . escape_html($_GET["f"]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Contents . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
}
$FileTypes = ["png" => "jpg.gif", "jpeg" => "jpg.gif", "bmp" => "jpg.gif", "jpg" => "jpg.gif", "gif" => "gif.gif", "zip" => "archive.png", "rar" => "archive.png", "tar" => "archive.png", "exe" => "exe.gif", "setup" => "setup.gif", "txt" => "text.png", "htm" => "html.gif", "html" => "html.gif", "fla" => "fla.gif", "swf" => "swf.gif", "xls" => "xls.png", "doc" => "doc.png", "sig" => "sig.gif", "fh10" => "fh10.gif", "pdf" => "pdf.png", "psd" => "psd.gif", "rm" => "real.gif", "mpg" => "video.gif", "mpeg" => "video.gif", "mov" => "video2.gif", "avi" => "video.gif", "eps" => "eps.gif", "gz" => "archive.png", "asc" => "sig.gif", "php" => "php.png", "css" => "css.png", "js" => "script.png", "dat" => "script_save.png", "xml" => "script_code.png", "htaccess" => "script_key.png", "ico" => "script_palette.png"];
$ScanDirectory = scandir($CurrentDirectory);
foreach ($ScanDirectory as $ScanRes) {
    if (is_dir($CurrentDirectory . $ScanRes)) {
        if ($ScanRes !== ".") {
            $Directories[] = $ScanRes;
        }
    } else {
        $Files[] = $ScanRes;
    }
}
foreach ($Directories as $Directory) {
    $FileList .= "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . ($Directory === ".." ? "<img $src = \"images/filetypes/dirup.png\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" />" : "<img $src = \"images/filetypes/folder.png\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" />") . " <u><b><font $size = \"2\"><a $href = \"index.php?do=directory_listing&amp;$d = " . escape_attr($CurrentDirectory . $Directory) . "\">" . escape_html($Directory) . "</a></font></b></u>\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\t\t\t\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\t\t\t\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\t\t\t\r\n\t\t</td>\r\n\t</tr>";
}
foreach ($Files as $File) {
    $Icon = "unknown.png";
    $Ext = function_149($Directory . "/" . $File);
    if (isset($FileTypes[$Ext])) {
        $Icon = $FileTypes[$Ext];
    }
    $FileList .= "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<img $src = \"images/filetypes/" . escape_attr($Icon) . "\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" /> <a $href = \"index.php?do=directory_listing&amp;$f = " . escape_attr($CurrentDirectory . $File) . "\">" . escape_html($File) . "</a>\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . escape_html(formatBytes(filesize($CurrentDirectory . $File))) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . escape_html(formatTimestamp(filemtime($CurrentDirectory . $File))) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . escape_html(generateToken($CurrentDirectory . $File)) . "\r\n\t\t</td>\r\n\t</tr>";
}
echo "\t\t\t\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"4\" $align = \"center\">\r\n\t\t\t<span $style = \"float: right;\"><small>" . escape_html($CurrentDirectory) . "</small></span>\r\n\t\t\t" . escape_html($Language[1]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[2]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[3]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[4]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[5]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $FileList . "\r\n</table>\r\n";
function formatBytes(int $bytes = 0): string
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 1073741824000) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / (1024 * 1024 * 1024 * 1024), 2) . " TB";
}
function formatTimestamp($timestamp = ""): string
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, (int)$timestamp);
}
function function_149(string $file = ""): string
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function generateToken(string $file): string
{
    return md5($file);
}
function function_150($file): string
{
    $filePermissions = fileperms($file);
    if (($filePermissions & 49152) == 49152) {
        $info = "s";
    } else {
        if (($filePermissions & 40960) == 40960) {
            $info = "l";
        } else {
            if (($filePermissions & 32768) == 32768) {
                $info = "-";
            } else {
                if (($filePermissions & 24576) == 24576) {
                    $info = "b";
                } else {
                    if (($filePermissions & 16384) == 16384) {
                        $info = "d";
                    } else {
                        if (($filePermissions & 8192) == 8192) {
                            $info = "c";
                        } else {
                            if (($filePermissions & 4096) == 4096) {
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
    $info .= $filePermissions & 256 ? "r" : "-";
    $info .= $filePermissions & 128 ? "w" : "-";
    $info .= $filePermissions & 64 ? $filePermissions & 2048 ? "s" : "x" : ($filePermissions & 2048 ? "S" : "-");
    $info .= $filePermissions & 32 ? "r" : "-";
    $info .= $filePermissions & 16 ? "w" : "-";
    $info .= $filePermissions & 8 ? $filePermissions & 1024 ? "s" : "x" : ($filePermissions & 1024 ? "S" : "-");
    $info .= $filePermissions & 4 ? "r" : "-";
    $info .= $filePermissions & 2 ? "w" : "-";
    $info .= $filePermissions & 1 ? $filePermissions & 512 ? "t" : "x" : ($filePermissions & 512 ? "T" : "-");
    return $info;
}

?>