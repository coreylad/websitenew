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
var_235();
$Language = file("languages/" . function_75() . "/torrent_info.lang");
$Message = "";
$tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : (isset($_POST["tid"]) ? intval($_POST["tid"]) : "");
if ($tid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, filename, seeders, leechers FROM torrents WHERE id = '" . $tid . "'");
    if (mysqli_num_rows($query)) {
        $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'MAIN'");
        $Result = mysqli_fetch_assoc($Q);
        $MAIN = unserialize($Result["content"]);
        $Result = mysqli_fetch_assoc($query);
        $fn = "../" . $MAIN["torrent_dir"] . "/" . $tid . ".torrent";
        $name = $Result["name"];
        $filename = $Result["filename"];
        $seeders = $Result["seeders"];
        $leechers = $Result["leechers"];
        if (is_file($fn) && is_readable($fn) && ($Data = file_get_contents($fn))) {
            $Torrent = new Class_7();
            if ($Torrent->function_165($Data)) {
                $trackers = $Torrent->function_181();
                $TrackersRow = "";
                $i = 0;
                foreach ($trackers as $tracker) {
                    $i++;
                    $TrackersRow .= function_327(empty($TrackersRow) ? "Tracker:" : NULL, "Announce URL (" . number_format($i) . "):", $tracker);
                }
                $files = $Torrent->function_180();
                $FilesRow = "";
                $i = 0;
                foreach ($files as $file) {
                    $i++;
                    $FilesRow .= function_327(empty($FilesRow) ? "Files:" : NULL, "Filename (" . number_format($i) . "):", $file->name . " (" . var_238($file->length) . ")");
                }
                $Private = $Torrent->function_179();
                $STR = "\r\n\t\t\t\t" . $Message . "\r\n\t\t\t\t\r\n\t\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\t\t\t\t\t\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" align=\"center\">\r\n\t\t\t\t\t\t\t" . $Language[2] . " (" . htmlspecialchars($name) . ")\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . function_327("Torrent", "Filename:", $filename) . "\r\n\t\t\t\t\t\t\t" . function_327(NULL, "Info Hash:", chunk_split(strtoupper(bin2hex($Torrent->function_166())), 8, " ")) . "\r\n\r\n\t\t\t\t\t\t\t" . $TrackersRow . "\r\n\r\n\t\t\t\t\t\t\t" . function_327("Meta Data", "Created On:", date("m/d/y h:i:s", $Torrent->function_174())) . "\r\n\t\t\t\t\t\t\t" . function_327(NULL, "Created By:", $Torrent->function_175()) . "\r\n\t\t\t\t\t\t\t" . function_327(NULL, "Comment:", $Torrent->function_173()) . "\r\n\t\t\t\t\t\t\t" . function_327(NULL, "Piece Length:", var_238($Torrent->function_177())) . "\r\n\t\t\t\t\t\t\t" . function_327(NULL, "Private:", $Private == -1 ? "Off - Unset" : ($Private == 0 ? "Off - Set" : "On - Set")) . "\r\n\r\n\t\t\t\t\t\t\t" . $FilesRow . "\r\n\r\n\t\t\t\t\t\t\t" . function_327("Peers", "Seeders:", number_format($seeders)) . "\r\n\t\t\t\t\t\t\t" . function_327(NULL, "Leechers:", number_format($leechers)) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>";
            } else {
                $Message = function_76($Language[7]);
            }
        } else {
            $Message = function_76($Language[8]);
        }
    } else {
        $Message = function_76($Language[6]);
    }
}
if (!isset($STR)) {
    echo "\t\t\t\t\r\n\t\r\n\t" . $Message . "\r\n\t<form method=\"post\" action=\"index.php?do=torrent_info\">\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" align=\"right\">" . $Language[3] . "</td>\r\n\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"tid\" value=\"" . intval($tid) . "\" size=\"10\" /></td>\r\n\t\t</tr>\t\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . $Language[4] . "\" /> <input type=\"reset\" value=\"" . $Language[5] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
} else {
    echo $STR;
}
class Class_7
{
    private $torrent = NULL;
    private $info = NULL;
    public $error = NULL;
    public function function_165(&$data)
    {
        $this->torrent = function_167::function_168($data);
        if ($this->torrent->function_169() == "error") {
            $this->error = $this->torrent->function_170();
            return false;
        }
        if ($this->torrent->function_169() != "dictionary") {
            $this->error = "The file was not a valid torrent file.";
            return false;
        }
        $this->info = $this->torrent->function_171("info");
        if (!$this->info) {
            $this->error = "Could not find info dictionary.";
            return false;
        }
        return true;
    }
    public function function_172($Whatever)
    {
        return $this->torrent->function_171($Whatever) ? $this->torrent->function_171($Whatever)->function_170() : NULL;
    }
    public function function_173()
    {
        return $this->torrent->function_171("comment") ? $this->torrent->function_171("comment")->function_170() : NULL;
    }
    public function function_174()
    {
        return $this->torrent->function_171("creation date") ? $this->torrent->function_171("creation date")->function_170() : NULL;
    }
    public function function_175()
    {
        return $this->torrent->function_171("created by") ? $this->torrent->function_171("created by")->function_170() : NULL;
    }
    public function function_176()
    {
        return $this->info->function_171("name")->function_170();
    }
    public function function_177()
    {
        return $this->info->function_171("piece length")->function_170();
    }
    public function function_178()
    {
        return $this->info->function_171("pieces")->function_170();
    }
    public function function_179()
    {
        if ($this->info->function_171("private")) {
            return $this->info->function_171("private")->function_170();
        }
        return -1;
    }
    public function function_180()
    {
        var_477 = [];
        $length = $this->info->function_171("length");
        if ($length) {
            $file = new Class_8();
            $file->name = $this->info->function_171("name")->function_170();
            $file->length = $this->info->function_171("length")->function_170();
            array_push(var_477, $file);
        } else {
            if ($this->info->function_171("files")) {
                $files = $this->info->function_171("files")->function_170();
                while (list($key, $value) = each($files)) {
                    $file = new Class_8();
                    var_480 = $value->function_171("path")->function_170();
                    while (list($key, var_481) = each(var_480)) {
                        $file->name .= "/" . var_481->function_170();
                    }
                    $file->name = ltrim($file->name, "/");
                    $file->length = $value->function_171("length")->function_170();
                    array_push(var_477, $file);
                }
            }
        }
        return var_477;
    }
    public function function_181()
    {
        var_482 = [];
        if ($this->torrent->function_171("announce-list")) {
            $trackers = $this->torrent->function_171("announce-list")->function_170();
            while (list($key, $value) = each($trackers)) {
                if (is_array($value->function_170())) {
                    while (list($key, var_481) = each($value)) {
                        while (list($key, var_484) = each(var_481)) {
                            array_push(var_482, var_484->function_170());
                        }
                    }
                } else {
                    array_push(var_482, $value->function_170());
                }
            }
        } else {
            if ($this->torrent->function_171("announce")) {
                array_push(var_482, $this->torrent->function_171("announce")->function_170());
            }
        }
        return var_482;
    }
    public function function_182($tracker_url)
    {
        $trackers = $this->function_181();
        $trackers[] = $tracker_url;
        $this->function_183($trackers);
    }
    public function function_184($Whatever)
    {
        if ($this->torrent->function_171($Whatever)) {
            $this->torrent->function_185($Whatever);
        }
    }
    public function function_183($trackerlist)
    {
        if (1 <= count($trackerlist)) {
            $this->torrent->function_185("announce-list");
            $string = new Class_9($trackerlist[0]);
            $this->torrent->function_186("announce", $string);
        }
        if (1 < count($trackerlist)) {
            var_8 = new Class_10();
            while (list($key, $value) = each($trackerlist)) {
                var_485 = new Class_10();
                $string = new Class_9($value);
                var_485->function_187($string);
                var_8->function_187(var_485);
            }
            $this->torrent->function_186("announce-list", var_8);
        }
    }
    public function function_188($filelist)
    {
        $length = $this->info->function_171("length");
        if ($length) {
            $filelist[0] = str_replace("\\", "/", $filelist[0]);
            $string = new Class_9($filelist[0]);
            $this->info->function_186("name", $string);
        } else {
            if ($this->info->function_171("files")) {
                $files = $this->info->function_171("files")->function_170();
                for ($i = 0; $i < count($files); $i++) {
                    var_486 = split("/", $filelist[$i]);
                    var_480 = new Class_10();
                    foreach (var_486 as var_487) {
                        $string = new Class_9(var_487);
                        var_480->function_187($string);
                    }
                    $files[$i]->function_186("path", var_480);
                }
            }
        }
    }
    public function function_189($value)
    {
        $type = "comment";
        $key = $this->torrent->function_171($type);
        if ($value == "") {
            $this->torrent->function_185($type);
        } else {
            if ($key) {
                $key->function_186($value);
            } else {
                $string = new Class_9($value);
                $this->torrent->function_186($type, $string);
            }
        }
    }
    public function function_190($value)
    {
        $type = "created by";
        $key = $this->torrent->function_171($type);
        if ($value == "") {
            $this->torrent->function_185($type);
        } else {
            if ($key) {
                $key->function_186($value);
            } else {
                $string = new Class_9($value);
                $this->torrent->function_186($type, $string);
            }
        }
    }
    public function function_191($value)
    {
        $type = "source";
        $key = $this->torrent->function_171($type);
        if ($value == "") {
            $this->torrent->function_185($type);
        } else {
            if ($key) {
                $key->function_186($value);
            } else {
                $string = new Class_9($value);
                $this->torrent->function_186($type, $string);
            }
        }
    }
    public function function_192($value)
    {
        $type = "creation date";
        $key = $this->torrent->function_171($type);
        if ($value == "") {
            $this->torrent->function_185($type);
        } else {
            if ($key) {
                $key->function_186($value);
            } else {
                var_488 = new Class_11($value);
                $this->torrent->function_186($type, var_488);
            }
        }
    }
    public function function_193($value)
    {
        if ($value == -1) {
            $this->info->function_185("private");
        } else {
            var_488 = new Class_11($value);
            $this->info->function_186("private", var_488);
        }
    }
    public function function_167()
    {
        return $this->torrent->function_194();
    }
    public function function_166()
    {
        return pack("H*", sha1($this->info->function_194()));
    }
}
class Class_8
{
    public $name = NULL;
    public $length = NULL;
}
class function_167
{
    public static function &function_168(&$raw, &$offset = 0)
    {
        if (strlen($raw) <= $offset) {
            return new Class_12("Decoder exceeded max length.");
        }
        currentChar = $raw[$offset];
        switch (currentChar) {
            case "i":
                var_488 = new Class_11();
                var_488->function_168($raw, $offset);
                return var_488;
                break;
            case "d":
                var_489 = new Class_13();
                if ($check = var_489->function_168($raw, $offset)) {
                    return $check;
                }
                return var_489;
                break;
            case "l":
                var_8 = new Class_10();
                var_8->function_168($raw, $offset);
                return var_8;
                break;
            case "e":
                var_490 = new Class_14();
                return var_490;
                break;
            case "0":
            case is_numeric(currentChar):
                var_309 = new Class_9();
                var_309->function_168($raw, $offset);
                return var_309;
                break;
            default:
                var_491 = strpos($raw, ":", $offset) - $offset;
                if (var_491 < 0 || 20 < var_491) {
                    return NULL;
                }
                $len = (int) substr($raw, $offset, var_491);
                $offset += var_491 + 1;
                var_309 = substr($raw, $offset, $len);
                $offset += $len;
                return (string) var_309;
        }
    }
}
class Class_14
{
    public function function_169()
    {
        return "end";
    }
}
class Class_12
{
    private $error = NULL;
    public function __construct($error)
    {
        $this->error = $error;
    }
    public function function_170()
    {
        return $this->error;
    }
    public function function_169()
    {
        return "error";
    }
}
class Class_11
{
    private $value = NULL;
    public function __construct($value = NULL)
    {
        $this->value = $value;
    }
    public function function_168(&$raw, &$offset)
    {
        $end = strpos($raw, "e", $offset);
        $offset++;
        $this->value = substr($raw, $offset, $end - $offset);
        $offset += $end - $offset;
    }
    public function function_170()
    {
        return $this->value;
    }
    public function function_169()
    {
        return "int";
    }
    public function function_194()
    {
        return "i" . $this->value . "e";
    }
    public function function_186($value)
    {
        $this->value = $value;
    }
}
class Class_13
{
    public $value = [];
    public function function_168(&$raw, &$offset)
    {
        var_492 = [];
        while (true) {
            $offset++;
            $name = function_167::function_168($raw, $offset);
            if ($name->function_169() != "end") {
                if ($name->function_169() == "error") {
                    return $name;
                }
                if ($name->function_169() != "string") {
                    return new Class_12("Key name in dictionary was not a string.");
                }
                $offset++;
                $value = function_167::function_168($raw, $offset);
                if ($value->function_169() == "error") {
                    return $value;
                }
                var_492[$name->function_170()] = $value;
            }
        }
        $this->value = var_492;
    }
    public function function_171($key)
    {
        if (isset($this->value[$key])) {
            return $this->value[$key];
        }
        return NULL;
    }
    public function function_194()
    {
        $this->function_195();
        var_493 = "d";
        while (list($key, $value) = each($this->value)) {
            var_494 = new Class_9();
            var_494->function_186($key);
            var_493 .= var_494->function_194();
            var_493 .= $value->function_194();
        }
        var_493 .= "e";
        return var_493;
    }
    public function function_169()
    {
        return "dictionary";
    }
    public function function_185($key)
    {
        unset($this->value[$key]);
    }
    public function function_186($key, $value)
    {
        $this->value[$key] = $value;
    }
    private function function_195()
    {
        ksort($this->value);
    }
    public function function_196()
    {
        return count($this->value);
    }
}
class Class_10
{
    private $value = [];
    public function function_187($bval)
    {
        array_push($this->value, $bval);
    }
    public function function_168(&$raw, &$offset)
    {
        var_8 = [];
        while (true) {
            $offset++;
            $value = function_167::function_168($raw, $offset);
            if ($value->function_169() != "end") {
                if ($value->function_169() == "error") {
                    return $value;
                }
                array_push(var_8, $value);
            }
        }
        $this->value = var_8;
    }
    public function function_194()
    {
        var_493 = "l";
        for ($i = 0; $i < count($this->value); $i++) {
            var_493 .= $this->value[$i]->function_194();
        }
        var_493 .= "e";
        return var_493;
    }
    public function function_170()
    {
        return $this->value;
    }
    public function function_169()
    {
        return "list";
    }
}
class Class_9
{
    private $value = NULL;
    public function __construct($value = NULL)
    {
        $this->value = $value;
    }
    public function function_168(&$raw, &$offset)
    {
        $end = strpos($raw, ":", $offset);
        $len = substr($raw, $offset, $end - $offset);
        $offset += $len + $end - $offset;
        $end++;
        $this->value = substr($raw, $end, $len);
    }
    public function function_170()
    {
        return $this->value;
    }
    public function function_169()
    {
        return "string";
    }
    public function function_194()
    {
        $len = strlen($this->value);
        return $len . ":" . $this->value;
    }
    public function function_186($value)
    {
        $this->value = $value;
    }
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
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href=\"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_327($title = "", $label = "", $content = "")
{
    return ($title ? "\r\n\t<h2>" . $title . "</h2>" : "") . "\r\n\t<div class=\"row\">\r\n\t\t<div class=\"label\">" . $label . "</div>\r\n\t\t<div class=\"content\">" . htmlspecialchars($content) . "</div>\r\n\t</div>";
}
function function_88($bytes = 0)
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