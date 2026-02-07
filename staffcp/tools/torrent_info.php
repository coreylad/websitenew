<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

@ini_set("upload_max_filesize", 10485760);
@ini_set("memory_limit", "20000M");
@set_time_limit(0);
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/torrent_info.lang");
$Message = "";
$tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : (isset($_POST["tid"]) ? intval($_POST["tid"]) : "");
if ($tid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, filename, seeders, leechers FROM torrents WHERE `id` = '" . $tid . "'");
    if (mysqli_num_rows($query)) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
        $Result = mysqli_fetch_assoc($query);
        $MAIN = unserialize($Result["content"]);
        $Result = mysqli_fetch_assoc($query);
        $fn = "../" . $MAIN["torrent_dir"] . "/" . $tid . ".torrent";
        $name = $Result["name"];
        $filename = $Result["filename"];
        $seeders = $Result["seeders"];
        $leechers = $Result["leechers"];
        if (is_file($fn) && is_readable($fn) && ($Data = file_get_contents($fn))) {
            $Torrent = new Class_7();
            if ($Torrent->parseTorrentData($Data)) {
                $trackers = $Torrent->getTrackerList();
                $TrackersRow = "";
                $i = 0;
                foreach ($trackers as $tracker) {
                    $i++;
                    $TrackersRow .= formatDisplayRow(empty($TrackersRow) ? "Tracker:" : NULL, "Announce URL (" . number_format($i) . "):", $tracker);
                }
                $files = $Torrent->getFileList();
                $FilesRow = "";
                $i = 0;
                foreach ($files as $file) {
                    $i++;
                    $FilesRow .= formatDisplayRow(empty($FilesRow) ? "Files:" : NULL, "Filename (" . number_format($i) . "):", $file->name . " (" . formatBytes($file->length) . ")");
                }
                $Private = $Torrent->getPrivateFlag();
                $STR = "\r\n\t\t\t\t" . $Message . "\r\n\t\t\t\t\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\t\t\t\t\t\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t\t\t" . $Language[2] . " (" . htmlspecialchars($name) . ")\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . formatDisplayRow("Torrent", "Filename:", $filename) . "\r\n\t\t\t\t\t\t\t" . formatDisplayRow(NULL, "Info Hash:", chunk_split(strtoupper(bin2hex($Torrent->getInfoHash())), 8, " ")) . "\r\n\r\n\t\t\t\t\t\t\t" . $TrackersRow . "\r\n\r\n\t\t\t\t\t\t\t" . formatDisplayRow("Meta Data", "Created On:", date("m/d/y h:i:s", $Torrent->getCreationDate())) . "\r\n\t\t\t\t\t\t\t" . formatDisplayRow(NULL, "Created By:", $Torrent->getCreatedBy()) . "\r\n\t\t\t\t\t\t\t" . formatDisplayRow(NULL, "Comment:", $Torrent->getComment()) . "\r\n\t\t\t\t\t\t\t" . formatDisplayRow(NULL, "Piece Length:", formatBytes($Torrent->getPieceLength())) . "\r\n\t\t\t\t\t\t\t" . formatDisplayRow(NULL, "Private:", $Private == -1 ? "Off - Unset" : ($Private == 0 ? "Off - Set" : "On - Set")) . "\r\n\r\n\t\t\t\t\t\t\t" . $FilesRow . "\r\n\r\n\t\t\t\t\t\t\t" . formatDisplayRow("Peers", "Seeders:", number_format($seeders)) . "\r\n\t\t\t\t\t\t\t" . formatDisplayRow(NULL, "Leechers:", number_format($leechers)) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>";
            } else {
                $Message = showAlertError($Language[7]);
            }
        } else {
            $Message = showAlertError($Language[8]);
        }
    } else {
        $Message = showAlertError($Language[6]);
    }
}
if (!isset($STR)) {
    echo "\t\t\t\t\r\n\t\r\n\t" . $Message . "\r\n\t<form $method = \"post\" $action = \"index.php?do=torrent_info\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $align = \"right\">" . $Language[3] . "</td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"tid\" $value = \"" . intval($tid) . "\" $size = \"10\" /></td>\r\n\t\t</tr>\t\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[4] . "\" /> <input $type = \"reset\" $value = \"" . $Language[5] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
} else {
    echo $STR;
}
class Class_7
{
    private $torrent = NULL;
    private $info = NULL;
    public $error = NULL;
    public function parseTorrentData(&$data)
    {
        $this->$torrent = BencodeDecoder::decodeBencode($data);
        if ($this->torrent->getBencodeEnd() == "error") {
            $this->$error = $this->torrent->getValue();
            return false;
        }
        if ($this->torrent->getBencodeEnd() != "dictionary") {
            $this->$error = "The file was not a valid torrent file.";
            return false;
        }
        $this->$info = $this->torrent->getDictionaryValue("info");
        if (!$this->info) {
            $this->$error = "Could not find info dictionary.";
            return false;
        }
        return true;
    }
    public function getTorrentValue($Whatever)
    {
        return $this->torrent->getDictionaryValue($Whatever) ? $this->torrent->getDictionaryValue($Whatever)->getValue() : NULL;
    }
    public function getComment()
    {
        return $this->torrent->getDictionaryValue("comment") ? $this->torrent->getDictionaryValue("comment")->getValue() : NULL;
    }
    public function getCreationDate()
    {
        return $this->torrent->getDictionaryValue("creation date") ? $this->torrent->getDictionaryValue("creation date")->getValue() : NULL;
    }
    public function getCreatedBy()
    {
        return $this->torrent->getDictionaryValue("created by") ? $this->torrent->getDictionaryValue("created by")->getValue() : NULL;
    }
    public function getName()
    {
        return $this->info->getDictionaryValue("name")->getValue();
    }
    public function getPieceLength()
    {
        return $this->info->getDictionaryValue("piece length")->getValue();
    }
    public function getPieces()
    {
        return $this->info->getDictionaryValue("pieces")->getValue();
    }
    public function getPrivateFlag()
    {
        if ($this->info->getDictionaryValue("private")) {
            return $this->info->getDictionaryValue("private")->getValue();
        }
        return -1;
    }
    public function getFileList()
    {
        $var_477 = [];
        $length = $this->info->getDictionaryValue("length");
        if ($length) {
            $file = new Class_8();
            $file->$name = $this->info->getDictionaryValue("name")->getValue();
            $file->$length = $this->info->getDictionaryValue("length")->getValue();
            array_push($var_477, $file);
        } else {
            if ($this->info->getDictionaryValue("files")) {
                $files = $this->info->getDictionaryValue("files")->getValue();
                while (list($key, $value) = each($files)) {
                    $file = new Class_8();
                    $var_480 = $value->getDictionaryValue("path")->getValue();
                    while (list($key, $var_481) = each($var_480)) {
                        $file->name .= "/" . $var_481->getValue();
                    }
                    $file->$name = ltrim($file->name, "/");
                    $file->$length = $value->getDictionaryValue("length")->getValue();
                    array_push($var_477, $file);
                }
            }
        }
        return $var_477;
    }
    public function getTrackerList()
    {
        $var_482 = [];
        if ($this->torrent->getDictionaryValue("announce-list")) {
            $trackers = $this->torrent->getDictionaryValue("announce-list")->getValue();
            while (list($key, $value) = each($trackers)) {
                if (is_array($value->getValue())) {
                    while (list($key, $var_481) = each($value)) {
                        while (list($key, $var_484) = each($var_481)) {
                            array_push($var_482, $var_484->getValue());
                        }
                    }
                } else {
                    array_push($var_482, $value->getValue());
                }
            }
        } else {
            if ($this->torrent->getDictionaryValue("announce")) {
                array_push($var_482, $this->torrent->getDictionaryValue("announce")->getValue());
            }
        }
        return $var_482;
    }
    public function addTracker($tracker_url)
    {
        $trackers = $this->getTrackerList();
        $trackers[] = $tracker_url;
        $this->setTrackerList($trackers);
    }
    public function removeTorrentKey($Whatever)
    {
        if ($this->torrent->getDictionaryValue($Whatever)) {
            $this->torrent->deleteDictionaryKey($Whatever);
        }
    }
    public function setTrackerList($trackerlist)
    {
        if (1 <= count($trackerlist)) {
            $this->torrent->deleteDictionaryKey("announce-list");
            $string = new Class_9($trackerlist[0]);
            $this->torrent->setDictionaryValue("announce", $string);
        }
        if (1 < count($trackerlist)) {
            $var_8 = new Class_10();
            while (list($key, $value) = each($trackerlist)) {
                $var_485 = new Class_10();
                $string = new Class_9($value);
                $var_485->appendToList($string);
                $var_8->appendToList($var_485);
            }
            $this->torrent->setDictionaryValue("announce-list", $var_8);
        }
    }
    public function setFileList($filelist)
    {
        $length = $this->info->getDictionaryValue("length");
        if ($length) {
            $filelist[0] = str_replace("\\", "/", $filelist[0]);
            $string = new Class_9($filelist[0]);
            $this->info->setDictionaryValue("name", $string);
        } else {
            if ($this->info->getDictionaryValue("files")) {
                $files = $this->info->getDictionaryValue("files")->getValue();
                for ($i = 0; $i < count($files); $i++) {
                    $var_486 = split("/", $filelist[$i]);
                    $var_480 = new Class_10();
                    foreach ($var_486 as $var_487) {
                        $string = new Class_9($var_487);
                        $var_480->appendToList($string);
                    }
                    $files[$i]->setDictionaryValue("path", $var_480);
                }
            }
        }
    }
    public function setComment($value)
    {
        $type = "comment";
        $key = $this->torrent->getDictionaryValue($type);
        if ($value == "") {
            $this->torrent->deleteDictionaryKey($type);
        } else {
            if ($key) {
                $key->setValue($value);
            } else {
                $string = new Class_9($value);
                $this->torrent->setDictionaryValue($type, $string);
            }
        }
    }
    public function setCreatedBy($value)
    {
        $type = "created by";
        $key = $this->torrent->getDictionaryValue($type);
        if ($value == "") {
            $this->torrent->deleteDictionaryKey($type);
        } else {
            if ($key) {
                $key->setValue($value);
            } else {
                $string = new Class_9($value);
                $this->torrent->setDictionaryValue($type, $string);
            }
        }
    }
    public function setSource($value)
    {
        $type = "source";
        $key = $this->torrent->getDictionaryValue($type);
        if ($value == "") {
            $this->torrent->deleteDictionaryKey($type);
        } else {
            if ($key) {
                $key->setValue($value);
            } else {
                $string = new Class_9($value);
                $this->torrent->setDictionaryValue($type, $string);
            }
        }
    }
    public function setCreationDate($value)
    {
        $type = "creation date";
        $key = $this->torrent->getDictionaryValue($type);
        if ($value == "") {
            $this->torrent->deleteDictionaryKey($type);
        } else {
            if ($key) {
                $key->setValue($value);
            } else {
                $var_488 = new Class_11($value);
                $this->torrent->setDictionaryValue($type, $var_488);
            }
        }
    }
    public function setPrivateFlag($value)
    {
        if ($value == -1) {
            $this->info->deleteDictionaryKey("private");
        } else {
            $var_488 = new Class_11($value);
            $this->info->setDictionaryValue("private", $var_488);
        }
    }
    public function getRawBencodeData()
    {
        return $this->torrent->encodeToBencode();
    }
    public function getInfoHash()
    {
        return pack("H*", sha1($this->info->encodeToBencode()));
    }
}
class Class_8
{
    public $name = NULL;
    public $length = NULL;
}
class BencodeDecoder
{
    public static function &decodeBencode(&$raw, &$offset = 0)
    {
        if (strlen($raw) <= $offset) {
            return new Class_12("Decoder exceeded max length.");
        }
        $currentChar = $raw[$offset];
        switch ($currentChar) {
            case "i":
                $var_488 = new Class_11();
                $var_488->decodeBencode($raw, $offset);
                return $var_488;
                break;
            case "d":
                $var_489 = new Class_13();
                if ($check = $var_489->decodeBencode($raw, $offset)) {
                    return $check;
                }
                return $var_489;
                break;
            case "l":
                $var_8 = new Class_10();
                $var_8->decodeBencode($raw, $offset);
                return $var_8;
                break;
            case "e":
                $var_490 = new Class_14();
                return $var_490;
                break;
            case "0":
            case is_numeric($currentChar):
                $var_309 = new Class_9();
                $var_309->decodeBencode($raw, $offset);
                return $var_309;
                break;
            default:
                $var_491 = strpos($raw, ":", $offset) - $offset;
                if ($var_491 < 0 || 20 < $var_491) {
                    return NULL;
                }
                $len = (int) substr($raw, $offset, $var_491);
                $offset += $var_491 + 1;
                $var_309 = substr($raw, $offset, $len);
                $offset += $len;
                return (string) $var_309;
        }
    }
}
class Class_14
{
    public function getBencodeEnd()
    {
        return "end";
    }
}
class Class_12
{
    private $error = NULL;
    public function __construct($error)
    {
        $this->$error = $error;
    }
    public function getValue()
    {
        return $this->error;
    }
    public function getBencodeEnd()
    {
        return "error";
    }
}
class Class_11
{
    private $value = NULL;
    public function __construct($value = NULL)
    {
        $this->$value = $value;
    }
    public function decodeBencode(&$raw, &$offset)
    {
        $end = strpos($raw, "e", $offset);
        $offset++;
        $this->$value = substr($raw, $offset, $end - $offset);
        $offset += $end - $offset;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getBencodeEnd()
    {
        return "int";
    }
    public function encodeToBencode()
    {
        return "i" . $this->value . "e";
    }
    public function setValue($value)
    {
        $this->$value = $value;
    }
}
class Class_13
{
    public $value = [];
    public function decodeBencode(&$raw, &$offset)
    {
        $var_492 = [];
        while (true) {
            $offset++;
            $name = BencodeDecoder::decodeBencode($raw, $offset);
            if ($name->getBencodeEnd() != "end") {
                if ($name->getBencodeEnd() == "error") {
                    return $name;
                }
                if ($name->getBencodeEnd() != "string") {
                    return new Class_12("Key name in dictionary was not a string.");
                }
                $offset++;
                $value = BencodeDecoder::decodeBencode($raw, $offset);
                if ($value->getBencodeEnd() == "error") {
                    return $value;
                }
                $var_492[$name->getValue()] = $value;
            }
        }
        $this->$value = $var_492;
    }
    public function getDictionaryValue($key)
    {
        if (isset($this->value[$key])) {
            return $this->value[$key];
        }
        return NULL;
    }
    public function encodeToBencode()
    {
        $this->sortKeys();
        $var_493 = "d";
        while (list($key, $value) = each($this->value)) {
            $var_494 = new Class_9();
            $var_494->setValue($key);
            $var_493 .= $var_494->encodeToBencode();
            $var_493 .= $value->encodeToBencode();
        }
        $var_493 .= "e";
        return $var_493;
    }
    public function getBencodeEnd()
    {
        return "dictionary";
    }
    public function deleteDictionaryKey($key)
    {
        unset($this->value[$key]);
    }
    public function setDictionaryValue($key, $value)
    {
        $this->value[$key] = $value;
    }
    private function sortKeys()
    {
        ksort($this->value);
    }
    public function getKeyCount()
    {
        return count($this->value);
    }
}
class Class_10
{
    private $value = [];
    public function appendToList($bval)
    {
        array_push($this->value, $bval);
    }
    public function decodeBencode(&$raw, &$offset)
    {
        $var_8 = [];
        while (true) {
            $offset++;
            $value = BencodeDecoder::decodeBencode($raw, $offset);
            if ($value->getBencodeEnd() != "end") {
                if ($value->getBencodeEnd() == "error") {
                    return $value;
                }
                array_push($var_8, $value);
            }
        }
        $this->$value = $var_8;
    }
    public function encodeToBencode()
    {
        $var_493 = "l";
        for ($i = 0; $i < count($this->value); $i++) {
            $var_493 .= $this->value[$i]->encodeToBencode();
        }
        $var_493 .= "e";
        return $var_493;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getBencodeEnd()
    {
        return "list";
    }
}
class Class_9
{
    private $value = NULL;
    public function __construct($value = NULL)
    {
        $this->$value = $value;
    }
    public function decodeBencode(&$raw, &$offset)
    {
        $end = strpos($raw, ":", $offset);
        $len = substr($raw, $offset, $end - $offset);
        $offset += $len + $end - $offset;
        $end++;
        $this->$value = substr($raw, $end, $len);
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getBencodeEnd()
    {
        return "string";
    }
    public function encodeToBencode()
    {
        $len = strlen($this->value);
        return $len . ":" . $this->value;
    }
    public function setValue($value)
    {
        $this->$value = $value;
    }
}
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
function formatDisplayRow($title = "", $label = "", $content = "")
{
    return ($title ? "\r\n\t<h2>" . $title . "</h2>" : "") . "\r\n\t<div class=\"row\">\r\n\t\t<div class=\"label\">" . $label . "</div>\r\n\t\t<div class=\"content\">" . htmlspecialchars($content) . "</div>\r\n\t</div>";
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

?>