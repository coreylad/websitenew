<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
class Torrent
{
    private $torrent = NULL;
    private $info = NULL;
    public $error = NULL;
    public function loadResource(&$data)
    {
        $this->torrent = BEncode::decode($data);
        if ($this->torrent->get_type() == "error") {
            $this->error = $this->torrent->get_plain();
            return false;
        }
        if ($this->torrent->get_type() != "dictionary") {
            $this->error = "The file was not a valid torrent file.";
            return false;
        }
        $this->info = $this->torrent->get_value("info");
        if (!$this->info) {
            $this->error = "Could not find info dictionary.";
            return false;
        }
        return true;
    }
    public function getResource($resourceName)
    {
        return $this->torrent->get_value($resourceName) ? $this->torrent->get_value($resourceName)->get_plain() : NULL;
    }
    public function getComment()
    {
        return $this->torrent->get_value("comment") ? $this->torrent->get_value("comment")->get_plain() : NULL;
    }
    public function getCreationDate()
    {
        return $this->torrent->get_value("creation date") ? $this->torrent->get_value("creation date")->get_plain() : NULL;
    }
    public function getCreatedBy()
    {
        return $this->torrent->get_value("created by") ? $this->torrent->get_value("created by")->get_plain() : NULL;
    }
    public function getName()
    {
        return $this->info->get_value("name")->get_plain();
    }
    public function getPieceLength()
    {
        return $this->info->get_value("piece length")->get_plain();
    }
    public function getPieces()
    {
        return $this->info->get_value("pieces")->get_plain();
    }
    public function getPrivate()
    {
        if ($this->info->get_value("private")) {
            return $this->info->get_value("private")->get_plain();
        }
        return -1;
    }
    public function getFiles()
    {
        $filelist = [];
        $length = $this->info->get_value("length");
        if ($length) {
            $file = new Torrent_File();
            $file->name = $this->info->get_value("name")->get_plain();
            $file->length = $this->info->get_value("length")->get_plain();
            array_push($filelist, $file);
        } else {
            if ($this->info->get_value("files")) {
                $files = $this->info->get_value("files")->get_plain();
                while (list($key, $value) = each($files)) {
                    $file = new Torrent_File();
                    $path = $value->get_value("path")->get_plain();
                    while (list($key, $value2) = each($path)) {
                        $file->name .= "/" . $value2->get_plain();
                    }
                    $file->name = ltrim($file->name, "/");
                    $file->length = $value->get_value("length")->get_plain();
                    array_push($filelist, $file);
                }
            }
        }
        return $filelist;
    }
    public function getTrackers()
    {
        $trackerlist = [];
        if ($this->torrent->get_value("announce-list")) {
            $trackers = $this->torrent->get_value("announce-list")->get_plain();
            while (list($key, $value) = each($trackers)) {
                if (is_array($value->get_plain())) {
                    while (list($key, $value2) = each($value)) {
                        while (list($key, $value3) = each($value2)) {
                            array_push($trackerlist, $value3->get_plain());
                        }
                    }
                } else {
                    array_push($trackerlist, $value->get_plain());
                }
            }
        } else {
            if ($this->torrent->get_value("announce")) {
                array_push($trackerlist, $this->torrent->get_value("announce")->get_plain());
            }
        }
        return $trackerlist;
    }
    public function addTracker($tracker_url)
    {
        $trackers = $this->getTrackers();
        $trackers[] = $tracker_url;
        $this->setTrackers($trackers);
    }
    public function removeWhatever($Whatever)
    {
        if ($this->torrent->get_value($Whatever)) {
            $this->torrent->remove($Whatever);
        }
    }
    public function setTrackers($trackerlist)
    {
        if (1 <= count($trackerlist)) {
            $this->torrent->remove("announce-list");
            $string = new BEncode_String($trackerlist[0]);
            $this->torrent->set("announce", $string);
        }
        if (1 < count($trackerlist)) {
            $list = new BEncode_List();
            while (list($key, $value) = each($trackerlist)) {
                $list2 = new BEncode_List();
                $string = new BEncode_String($value);
                $list2->add($string);
                $list->add($list2);
            }
            $this->torrent->set("announce-list", $list);
        }
    }
    public function setFiles($filelist)
    {
        $length = $this->info->get_value("length");
        if ($length) {
            $filelist[0] = str_replace("\\", "/", $filelist[0]);
            $string = new BEncode_String($filelist[0]);
            $this->info->set("name", $string);
        } else {
            if ($this->info->get_value("files")) {
                $files = $this->info->get_value("files")->get_plain();
                for ($i = 0; $i < count($files); $i++) {
                    $file_parts = split("/", $filelist[$i]);
                    $path = new BEncode_List();
                    foreach ($file_parts as $part) {
                        $string = new BEncode_String($part);
                        $path->add($string);
                    }
                    $files[$i]->set("path", $path);
                }
            }
        }
    }
    public function setComment($value)
    {
        $type = "comment";
        $key = $this->torrent->get_value($type);
        if ($value == "") {
            $this->torrent->remove($type);
        } else {
            if ($key) {
                $key->set($value);
            } else {
                $string = new BEncode_String($value);
                $this->torrent->set($type, $string);
            }
        }
    }
    public function setCreatedBy($value)
    {
        $type = "created by";
        $key = $this->torrent->get_value($type);
        if ($value == "") {
            $this->torrent->remove($type);
        } else {
            if ($key) {
                $key->set($value);
            } else {
                $string = new BEncode_String($value);
                $this->torrent->set($type, $string);
            }
        }
    }
    public function setSource($value)
    {
        $type = "source";
        $key = $this->torrent->get_value($type);
        if ($value == "") {
            $this->torrent->remove($type);
        } else {
            if ($key) {
                $key->set($value);
            } else {
                $string = new BEncode_String($value);
                $this->torrent->set($type, $string);
            }
        }
    }
    public function setCreationDate($value)
    {
        $type = "creation date";
        $key = $this->torrent->get_value($type);
        if ($value == "") {
            $this->torrent->remove($type);
        } else {
            if ($key) {
                $key->set($value);
            } else {
                $int = new BEncode_Int($value);
                $this->torrent->set($type, $int);
            }
        }
    }
    public function setPrivate($value)
    {
        if ($value == -1) {
            $this->info->remove("private");
        } else {
            $int = new BEncode_Int($value);
            $this->info->set("private", $int);
        }
    }
    public function bencode()
    {
        return $this->torrent->encode();
    }
    public function getHash()
    {
        return pack("H*", sha1($this->info->encode()));
    }
}
class Torrent_File
{
    public $name = NULL;
    public $length = NULL;
}
class BEncode
{
    public static function &decode(&$raw, &$offset = 0)
    {
        if (strlen($raw) <= $offset) {
            return new BEncode_Error("Decoder exceeded max length.");
        }
        $char = $raw[$offset];
        switch ($char) {
            case "i":
                $int = new BEncode_Int();
                $int->decode($raw, $offset);
                return $int;
                break;
            case "d":
                $dict = new BEncode_Dictionary();
                if ($check = $dict->decode($raw, $offset)) {
                    return $check;
                }
                return $dict;
                break;
            case "l":
                $list = new BEncode_List();
                $list->decode($raw, $offset);
                return $list;
                break;
            case "e":
                $AvoidPHPWarning = new BEncode_End();
                return $AvoidPHPWarning;
                break;
            case "0":
            case is_numeric($char):
                $str = new BEncode_String();
                $str->decode($raw, $offset);
                return $str;
                break;
            default:
                $digits = strpos($raw, ":", $offset) - $offset;
                if ($digits < 0 || 20 < $digits) {
                    return NULL;
                }
                $len = (int) substr($raw, $offset, $digits);
                $offset += $digits + 1;
                $str = substr($raw, $offset, $len);
                $offset += $len;
                return (string) $str;
        }
    }
}
class BEncode_End
{
    public function get_type()
    {
        return "end";
    }
}
class BEncode_Error
{
    private $error = NULL;
    public function __construct($error)
    {
        $this->error = $error;
    }
    public function get_plain()
    {
        return $this->error;
    }
    public function get_type()
    {
        return "error";
    }
}
class BEncode_Int
{
    private $value = NULL;
    public function __construct($value = NULL)
    {
        $this->value = $value;
    }
    public function decode(&$raw, &$offset)
    {
        $end = strpos($raw, "e", $offset);
        $offset++;
        $this->value = substr($raw, $offset, $end - $offset);
        $offset += $end - $offset;
    }
    public function get_plain()
    {
        return $this->value;
    }
    public function get_type()
    {
        return "int";
    }
    public function encode()
    {
        return "i" . $this->value . "e";
    }
    public function set($value)
    {
        $this->value = $value;
    }
}
class BEncode_Dictionary
{
    public $value = [];
    public function decode(&$raw, &$offset)
    {
        $dictionary = [];
        while (true) {
            $offset++;
            $name = BEncode::decode($raw, $offset);
            if ($name->get_type() != "end") {
                if ($name->get_type() == "error") {
                    return $name;
                }
                if ($name->get_type() != "string") {
                    return new BEncode_Error("Key name in dictionary was not a string.");
                }
                $offset++;
                $value = BEncode::decode($raw, $offset);
                if ($value->get_type() == "error") {
                    return $value;
                }
                $dictionary[$name->get_plain()] = $value;
            }
        }
        $this->value = $dictionary;
    }
    public function get_value($key)
    {
        if (isset($this->value[$key])) {
            return $this->value[$key];
        }
        return NULL;
    }
    public function encode()
    {
        $this->sort();
        $encoded = "d";
        while (list($key, $value) = each($this->value)) {
            $bstr = new BEncode_String();
            $bstr->set($key);
            $encoded .= $bstr->encode();
            $encoded .= $value->encode();
        }
        $encoded .= "e";
        return $encoded;
    }
    public function get_type()
    {
        return "dictionary";
    }
    public function remove($key)
    {
        unset($this->value[$key]);
    }
    public function set($key, $value)
    {
        $this->value[$key] = $value;
    }
    private function sort()
    {
        ksort($this->value);
    }
    public function count()
    {
        return count($this->value);
    }
}
class BEncode_List
{
    private $value = [];
    public function add($bval)
    {
        array_push($this->value, $bval);
    }
    public function decode(&$raw, &$offset)
    {
        $list = [];
        while (true) {
            $offset++;
            $value = BEncode::decode($raw, $offset);
            if ($value->get_type() != "end") {
                if ($value->get_type() == "error") {
                    return $value;
                }
                array_push($list, $value);
            }
        }
        $this->value = $list;
    }
    public function encode()
    {
        $encoded = "l";
        for ($i = 0; $i < count($this->value); $i++) {
            $encoded .= $this->value[$i]->encode();
        }
        $encoded .= "e";
        return $encoded;
    }
    public function get_plain()
    {
        return $this->value;
    }
    public function get_type()
    {
        return "list";
    }
}
class BEncode_String
{
    private $value = NULL;
    public function __construct($value = NULL)
    {
        $this->value = $value;
    }
    public function decode(&$raw, &$offset)
    {
        $end = strpos($raw, ":", $offset);
        $len = substr($raw, $offset, $end - $offset);
        $offset += $len + $end - $offset;
        $end++;
        $this->value = substr($raw, $end, $len);
    }
    public function get_plain()
    {
        return $this->value;
    }
    public function get_type()
    {
        return "string";
    }
    public function encode()
    {
        $len = strlen($this->value);
        return $len . ":" . $this->value;
    }
    public function set($value)
    {
        $this->value = $value;
    }
}

?>