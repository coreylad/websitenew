<?php
@ini_set("upload_max_filesize", 10485760);
@ini_set("memory_limit", "20000M");
@set_time_limit(0);
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/fix_hash.lang");
$Message = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = \"MAIN\"");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
if (isset($_GET["usedid"])) {
    $usedid = intval($_GET["usedid"]);
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, info_hash, filename FROM torrents WHERE id > \"" . $usedid . "\" ORDER BY id ASC LIMIT 1");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $id = $Result["id"];
        $filename = $Result["filename"];
        $orj_info_hash = $Result["info_hash"];
    } else {
        echo showAlertError("All available torrent hashes has been fixed..");
        $Done = true;
    }
} else {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, info_hash, filename FROM torrents ORDER BY id ASC LIMIT 1");
    $Result = mysqli_fetch_assoc($query);
    $id = $Result["id"];
    $filename = $Result["filename"];
    $orj_info_hash = $Result["info_hash"];
}
if (!isset($Done)) {
    if ($Data = file_get_contents("../" . $MAIN["torrent_dir"] . "/" . $id . ".torrent")) {
        $Torrent = new Class_7();
        if ($Torrent->parseTorrentData($Data)) {
            $info_hash = $Torrent->getInfoHash();
            if ($info_hash != $orj_info_hash) {
                if ($info_hash) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $info_hash = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $info_hash) . "\" WHERE `id` = \"" . $id . "\"");
                    if (!mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                        $INFO = $Language[6];
                    } else {
                        $INFO = $Language[7];
                    }
                } else {
                    $ERROR = $Language[5];
                }
            } else {
                $INFO = $Language[8];
            }
        } else {
            $ERROR = $Language[4];
        }
    } else {
        $ERROR = $Language[3];
    }
    unset($Data);
    unset($Torrent);
    unset($info_hash);
    unset($MAIN);
    echo "\r\n\t<p $align = \"center\" $style = \"height:40px; background-color:#FFFFCC; layer-backgroundcolor:#FFFFCC; border:1px solid #000000; text-align: center;\">\r\n\t\t<br />\r\n\t\t<img $src = \"images/progress.gif\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" /> " . str_replace(["{1}", "{2}"], [htmlspecialchars($filename), $id], $Language[2]) . (isset($ERROR) ? " <font $color = \"red\"><i><b>ERROR: " . $ERROR . "</b></i></font>" : (isset($INFO) ? " <font $color = \"green\"><i><b>INFO: " . $INFO . "</b></i></font>" : "")) . "\r\n\t</p>\r\n\r\n\t<script $type = \"text/javascript\">\r\n\t\t<!--\r\n\t\t\tfunction delayer()\r\n\t\t\t{\r\n\t\t\t\twindow.$location = \"index.php?do=fix_hash&$usedid = " . $id . "\";\r\n\t\t\t}\r\n\t\t\tsetTimeout(\"delayer()\", 1000)\r\n\t\t//-->\r\n\t</script>";
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
        $hashOld = [];
        $hashNew = $this->info->getDictionaryValue("length");
        if ($hashNew) {
            $file = new Class_8();
            $file->$name = $this->info->getDictionaryValue("name")->getValue();
            $file->$length = $this->info->getDictionaryValue("length")->getValue();
            array_push($hashOld, $file);
        } else {
            if ($this->info->getDictionaryValue("files")) {
                $hashEncoded = $this->info->getDictionaryValue("files")->getValue();
                while (list($key, $value) = each($hashEncoded)) {
                    $file = new Class_8();
                    $infoHash = $value->getDictionaryValue("path")->getValue();
                    while (list($key, $peerId) = each($infoHash)) {
                        $file->name .= "/" . $peerId->getValue();
                    }
                    $file->$name = ltrim($file->name, "/");
                    $file->$length = $value->getDictionaryValue("length")->getValue();
                    array_push($hashOld, $file);
                }
            }
        }
        return $hashOld;
    }
    public function getTrackerList()
    {
        $peerIp = [];
        if ($this->torrent->getDictionaryValue("announce-list")) {
            $peerPort = $this->torrent->getDictionaryValue("announce-list")->getValue();
            while (list($key, $value) = each($peerPort)) {
                if (is_array($value->getValue())) {
                    while (list($key, $peerId) = each($value)) {
                        while (list($key, $peerUploaded) = each($peerId)) {
                            array_push($peerIp, $peerUploaded->getValue());
                        }
                    }
                } else {
                    array_push($peerIp, $value->getValue());
                }
            }
        } else {
            if ($this->torrent->getDictionaryValue("announce")) {
                array_push($peerIp, $this->torrent->getDictionaryValue("announce")->getValue());
            }
        }
        return $peerIp;
    }
    public function addTracker($tracker_url)
    {
        $peerPort = $this->getTrackerList();
        $peerPort[] = $tracker_url;
        $this->setTrackerList($peerPort);
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
            $formattedListHtml = new Class_10();
            while (list($key, $value) = each($trackerlist)) {
                $peerDownloaded = new Class_10();
                $string = new Class_9($value);
                $peerDownloaded->appendToList($string);
                $formattedListHtml->appendToList($peerDownloaded);
            }
            $this->torrent->setDictionaryValue("announce-list", $formattedListHtml);
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
                $hashEncoded = $this->info->getDictionaryValue("files")->getValue();
                for ($i = 0; $i < count($hashEncoded); $i++) {
                    $peerLeft = split("/", $filelist[$i]);
                    $infoHash = new Class_10();
                    foreach ($peerLeft as $peerEvent) {
                        $string = new Class_9($peerEvent);
                        $infoHash->appendToList($string);
                    }
                    $hashEncoded[$i]->setDictionaryValue("path", $infoHash);
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
                $peerAgent = new Class_11($value);
                $this->torrent->setDictionaryValue($type, $peerAgent);
            }
        }
    }
    public function setPrivateFlag($value)
    {
        if ($value == -1) {
            $this->info->deleteDictionaryKey("private");
        } else {
            $peerAgent = new Class_11($value);
            $this->info->setDictionaryValue("private", $peerAgent);
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
                $peerAgent = new Class_11();
                $peerAgent->decodeBencode($raw, $offset);
                return $peerAgent;
                break;
            case "d":
                $peerKey = new Class_13();
                if ($check = $peerKey->decodeBencode($raw, $offset)) {
                    return $check;
                }
                return $peerKey;
                break;
            case "l":
                $formattedListHtml = new Class_10();
                $formattedListHtml->decodeBencode($raw, $offset);
                return $formattedListHtml;
                break;
            case "e":
                $peerConnectable = new Class_14();
                return $peerConnectable;
                break;
            case "0":
            case is_numeric($currentChar):
                $secretString = new Class_9();
                $secretString->decodeBencode($raw, $offset);
                return $secretString;
                break;
            default:
                $peerSeeder = strpos($raw, ":", $offset) - $offset;
                if ($peerSeeder < 0 || 20 < $peerSeeder) {
                    return NULL;
                }
                $len = (int) substr($raw, $offset, $peerSeeder);
                $offset += $peerSeeder + 1;
                $secretString = substr($raw, $offset, $len);
                $offset += $len;
                return (string) $secretString;
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
        $peerStarted = [];
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
                $peerStarted[$name->getValue()] = $value;
            }
        }
        $this->$value = $peerStarted;
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
        $torrentData = "d";
        while (list($key, $value) = each($this->value)) {
            $peerData = new Class_9();
            $peerData->setValue($key);
            $torrentData .= $peerData->encodeToBencode();
            $torrentData .= $value->encodeToBencode();
        }
        $torrentData .= "e";
        return $torrentData;
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
        $formattedListHtml = [];
        while (true) {
            $offset++;
            $value = BencodeDecoder::decodeBencode($raw, $offset);
            if ($value->getBencodeEnd() != "end") {
                if ($value->getBencodeEnd() == "error") {
                    return $value;
                }
                array_push($formattedListHtml, $value);
            }
        }
        $this->$value = $formattedListHtml;
    }
    public function encodeToBencode()
    {
        $torrentData = "l";
        for ($i = 0; $i < count($this->value); $i++) {
            $torrentData .= $this->value[$i]->encodeToBencode();
        }
        $torrentData .= "e";
        return $torrentData;
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
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (\"" . $_SESSION["ADMIN_ID"] . "\", \"" . time() . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "\")");
}

?>