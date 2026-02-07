<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_CRON")) {
    exit;
}
@ini_set("memory_limit", 134217728);
@set_time_limit(0);
$feeds = "";
$tracker_default_charset = $charset;
$feeds_result = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT r.*, f.type, ff.fid as realforumid, u.username FROM ts_rssfeed r INNER JOIN tsf_forums f ON (f.fid = r.fid) INNER JOIN tsf_forums ff ON (ff.fid=f.pid) INNER JOIN users u ON (r.userid=u.id) WHERE r.active = 1");
$CQueryCount++;
while ($feed = mysqli_fetch_assoc($feeds_result)) {
    if ($feed["lastrun"] < TIMENOW - $feed["ttl"]) {
        $feed["counter"] = 0;
        $feeds[(string) $feed["rssfeedid"]] = $feed;
    }
}
if (!empty($feeds)) {
    foreach (array_keys($feeds) as $rssfeedid) {
        $feed =& $feeds[(string) $rssfeedid];
        $feed["xml"] = new TSSE_RSS_Poster();
        $feed["xml"]->fetch_xml($feed["url"]);
        $feed["counter"] = 0;
        $feed["useparent"] = false;
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_rssfeed SET lastrun = " . TIMENOW . " WHERE rssfeedid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $rssfeedid) . "'");
        $realforumid = $feed["realforumid"];
        if ($feed["type"] == "s") {
            $feed["useparent"] = true;
        }
        if (!empty($feed["xml"]->xml_string)) {
            if ($feed["xml"]->parse_xml() !== false) {
                $items = [];
                $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uniquehash FROM ts_rsslog WHERE rssfeedid = " . $rssfeedid);
                $CQueryCount++;
                $AllFeeds = [];
                if (mysqli_num_rows($Query)) {
                    while ($AF = mysqli_fetch_assoc($Query)) {
                        $AllFeeds[$AF["uniquehash"]] = true;
                    }
                }
                foreach ($feed["xml"]->fetch_items() as $item) {
                    $item["rssfeedid"] = $rssfeedid;
                    if (!empty($item["summary"])) {
                        $description = get_item_value($item["summary"]);
                    } else {
                        if (!empty($item["content:encoded"])) {
                            $description = get_item_value($item["content:encoded"]);
                        } else {
                            if (!empty($item["content"])) {
                                $description = get_item_value($item["content"]);
                            } else {
                                $description = get_item_value($item["description"]);
                            }
                        }
                    }
                    if (!isset($item["description"])) {
                        $item["description"] = $description;
                    }
                    if (!isset($item["guid"]) && isset($item["id"])) {
                        $item["guid"] =& $item["id"];
                    }
                    if (!isset($item["pubDate"])) {
                        if (isset($item["published"])) {
                            $item["pubDate"] =& $item["published"];
                        } else {
                            if (isset($item["updated"])) {
                                $item["pubDate"] =& $item["updated"];
                            }
                        }
                    }
                    switch ($feed["xml"]->feedtype) {
                        case "atom":
                            $item["contenthash"] = md5($item["title"]["value"] . $description . $item["link"]["href"]);
                            break;
                        case "rss":
                        default:
                            $item["contenthash"] = md5($item["title"] . $description . $item["link"]);
                            if (is_array($item["guid"]) && !empty($item["guid"]["value"])) {
                                $uniquehash = md5($item["guid"]["value"]);
                            } else {
                                if (!is_array($item["guid"]) && !empty($item["guid"])) {
                                    $uniquehash = md5($item["guid"]);
                                } else {
                                    $uniquehash = $item["contenthash"];
                                }
                            }
                            if (!isset($AllFeeds[$uniquehash])) {
                                if ($feed["maxresults"] == 0 || $feed["counter"] < $feed["maxresults"]) {
                                    $feed["counter"]++;
                                    $items[(string) $uniquehash] = $item;
                                }
                            }
                    }
                }
                if (!empty($items)) {
                    foreach ($items as $uniquehash => $item) {
                        $feedtitle = $feed["xml"]->parse_template($feed["titletemplate"], $item);
                        $feedbody = htmltobbcode($feed["xml"]->parse_template($feed["bodytemplate"], $item), false);
                        $Queries = [];
                        $Queries["fid"] = $feed["fid"];
                        $Queries["subject"] = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $feedtitle) . "'";
                        $Queries["uid"] = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $feed["userid"]) . "'";
                        $Queries["username"] = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $feed["username"]) . "'";
                        $Queries["dateline"] = TIMENOW;
                        $Queries["message"] = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $feedbody) . "'";
                        $Queries["visible"] = $feed["moderate"] == 1 ? 0 : 1;
                        if (1 < strlen($item["title"])) {
                            $buildpostquery = [];
                            foreach ($Queries as $_left => $_right) {
                                $buildpostquery[] = $_left . " = " . $_right;
                            }
                            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO tsf_posts SET " . implode(",", $buildpostquery));
                            $CQueryCount++;
                            $Queries["firstpost"] = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO tsf_threads (fid,subject,uid,username,dateline,firstpost,lastpost,visible,lastposter,lastposteruid) VALUES (" . $Queries["fid"] . "," . $Queries["subject"] . "," . $Queries["uid"] . "," . $Queries["username"] . "," . $Queries["dateline"] . "," . $Queries["firstpost"] . "," . $Queries["dateline"] . "," . $Queries["visible"] . "," . $Queries["username"] . "," . $Queries["uid"] . ")");
                            $CQueryCount++;
                            $Queries["tid"] = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE tsf_posts SET tid = " . $Queries["tid"] . " WHERE pid = '" . $Queries["firstpost"] . "'");
                            $CQueryCount++;
                            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE tsf_forums SET threads = threads + 1, posts = posts + 1, lastpost = " . $Queries["dateline"] . ", lastposter = " . $Queries["username"] . ", lastposteruid = " . $Queries["uid"] . ", lastposttid = " . $Queries["tid"] . ", lastpostsubject = " . $Queries["subject"] . " WHERE fid = " . $Queries["fid"]);
                            $CQueryCount++;
                            if ($feed["useparent"]) {
                                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE tsf_forums SET lastpost = " . $Queries["dateline"] . ", lastposter = " . $Queries["username"] . ", lastposteruid = " . $Queries["uid"] . ", lastposttid = " . $Queries["tid"] . ", lastpostsubject = " . $Queries["subject"] . " WHERE fid = " . $realforumid);
                                $CQueryCount++;
                            }
                            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET totalposts = totalposts + 1 WHERE id = " . $Queries["uid"]);
                            $CQueryCount++;
                            mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO ts_rsslog VALUES (" . $item["rssfeedid"] . ", " . $Queries["tid"] . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $uniquehash) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $item["contenthash"]) . "', " . $Queries["dateline"] . ")");
                            $CQueryCount++;
                        }
                    }
                }
            }
        }
    }
}
if (!function_exists("unhtmlspecialcharscron")) {
    function unhtmlspecialcharscron($text, $doUniCode = false)
    {
        if ($doUniCode) {
            $text = preg_replace_callback("/&#([0-9]+);/siU", function ($matches) {
                return convert_int_to_utf8($matches[1]);
            }, $text);
        }
        return str_replace(["&lt;", "&gt;", "&quot;", "&amp;"], ["<", ">", "\"", "&"], $text);
    }
}
if (!function_exists("xml_set_element_handler")) {
    $extension_dir = ini_get("extension_dir");
    if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
        $extension_file = "php_xml.dll";
    } else {
        $extension_file = "xml.so";
    }
    if ($extension_dir && file_exists($extension_dir . "/" . $extension_file)) {
        ini_set("display_errors", true);
        dl($extension_file);
    }
}
if (!function_exists("ini_size_to_bytes") || ($current_memory_limit = ini_size_to_bytes(@ini_get("memory_limit"))) < 134217728 && 0 < $current_memory_limit) {
    @ini_set("memory_limit", 134217728);
}
class TSParserCron
{
    public $TSParserVersion = "1.0.2";
    public $options = ["htmlspecialchars" => 1, "auto_url" => 1, "short_url" => 1];
    public $tscode_cache = [];
    public $message = "";
    public function __construct()
    {
    }
    public function parse_message($message)
    {
        $this->cache_tscode();
        $this->message = str_replace("\r", "", $message);
        if ($this->options["htmlspecialchars"]) {
            $this->message = htmlspecialchars($this->message);
            $this->message = str_replace("&amp;", "&", $this->message);
        }
        $this->fix_javascript();
        $this->parse_codes();
        $this->message = nl2br($this->message);
        $this->ts_wordwrap();
    }
    public function ts_wordwrap($wraptext = "  ")
    {
        $limit = 136;
        if (!empty($this->message)) {
            $this->message = preg_replace("\r\n\t\t\t\t#((?>[^\\s&/<>\"\\-\\[\\]]|&[\\#a-z0-9]{1,7};){" . $limit . "})(?=[^\\s&/<>\"\\-\\[\\]]|&[\\#a-z0-9]{1,7};)#i", "\$0" . $wraptext, $this->message);
        }
    }
    public function parse_codes()
    {
        if ($this->options["auto_url"]) {
            $this->tscode_auto_url();
        }
        $this->message = str_replace("\$", "&#36;", $this->message);
        $this->message = preg_replace($this->tscode_cache["find"], $this->tscode_cache["replacement"], $this->message);
        $this->message = preg_replace_callback("#\\[url\\]([a-z]+?://)([^\r\n\"<]+?)\\[/url\\]#si", function ($matches) {
            return $this->tscode_parse_url($matches[1] . $matches[2]);
        }, $this->message);
        $this->message = preg_replace_callback("#\\[url\\]([^\r\n\"<]+?)\\[/url\\]#si", function ($matches) {
            return $this->tscode_parse_url($matches[1]);
        }, $this->message);
        $this->message = preg_replace_callback("#\\[url=([a-z]+?://)([^\r\n\"<]+?)\\](.+?)\\[/url\\]#si", function ($matches) {
            return $this->tscode_parse_url($matches[1] . $matches[2], $matches[3]);
        }, $this->message);
        $this->message = preg_replace_callback("#\\[url=([^\r\n\"<&\\(\\)]+?)\\](.+?)\\[/url\\]#si", function ($matches) {
            return $this->tscode_parse_url($matches[1], $matches[2]);
        }, $this->message);
        while (preg_match("#\\[list\\](.*?)\\[\\/list\\]#si", $this->message)) {
            $this->message = preg_replace_callback("#\\[list\\](.*?)\\[\\/list\\]#si", function ($matches) {
                return $this->tscode_parse_list($matches[1], false);
            }, $this->message);
        }
        while (preg_match("#\\[list=ol\\](.*?)\\[\\/list\\]#si", $this->message)) {
            $this->message = preg_replace_callback("#\\[list=ol\\](.*?)\\[\\/list\\]#si", function ($matches) {
                return $this->tscode_parse_list($matches[1], true);
            }, $this->message);
        }
        $this->message = preg_replace_callback("#\\[img\\]\\s*(https?://([^*\\r\\n]+|[a-z0-9/\\._\\- !]+))\\[/img\\]#iU", function ($matches) {
            return $this->tscode_parse_img($matches[1]) . "\n";
        }, $this->message);
    }
    public function tscode_auto_url()
    {
        $this->message = " " . $this->message;
        $this->message = preg_replace("#([\\>\\s\\(\\)])(https?|ftp|news){1}://([\\w\\-]+\\.([\\w\\-]+\\.)*[\\w]+(:[0-9]+)?(/[^\"\\s<\\[]*)?)#i", "\$1[url]\$2://\$3[/url]", $this->message);
        $this->message = preg_replace("#([\\>\\s\\(\\)])(www|ftp)\\.(([\\w\\-]+\\.)*[\\w]+(:[0-9]+)?(/[^\"\\s<\\[]*)?)#i", "\$1[url]\$2.\$3[/url]", $this->message);
        $this->message = substr($this->message, 1);
    }
    public function tscode_parse_list($message, $type = "")
    {
        $message = str_replace("\\\"", "\"", $message);
        $message = preg_replace("#\\s*\\[\\*\\]\\s*#", "</li>\n<li>", $message);
        $message .= "</li>";
        if ($type) {
            $list = "\n<ol type=\"" . $type . "\">" . $message . "</ol>\n";
        } else {
            $list = "<ul>" . $message . "</ul>\n";
        }
        $list = preg_replace("#<(ol type=\"" . $type . "\"|ul)>\\s*</li>#", "<\$1>", $list);
        return $list;
    }
    public function tscode_parse_img($url)
    {
        global $lang;
        $url = str_replace(["  ", "\"", "\\n", "\\r"], "", trim($url));
        return "<img src=\"" . $url . "\" border=\"0\" alt=\"\" />";
    }
    public function fix_javascript()
    {
        $js_array = ["#(&\\#(0*)106;|&\\#(0*)74;|j)((&\\#(0*)97;|&\\#(0*)65;|a)(&\\#(0*)118;|&\\#(0*)86;|v)(&\\#(0*)97;|&\\#(0*)65;|a)(\\s)?(&\\#(0*)115;|&\\#(0*)83;|s)(&\\#(0*)99;|&\\#(0*)67;|c)(&\\#(0*)114;|&\\#(0*)82;|r)(&\\#(0*)105;|&\\#(0*)73;|i)(&\\#112;|&\\#(0*)80;|p)(&\\#(0*)116;|&\\#(0*)84;|t)(&\\#(0*)58;|\\:))#i", "#(o)(nmouseover\\s?=)#i", "#(o)(nmouseout\\s?=)#i", "#(o)(nmousedown\\s?=)#i", "#(o)(nmousemove\\s?=)#i", "#(o)(nmouseup\\s?=)#i", "#(o)(nclick\\s?=)#i", "#(o)(ndblclick\\s?=)#i", "#(o)(nload\\s?=)#i", "#(o)(nsubmit\\s?=)#i", "#(o)(nblur\\s?=)#i", "#(o)(nchange\\s?=)#i", "#(o)(nfocus\\s?=)#i", "#(o)(nselect\\s?=)#i", "#(o)(nunload\\s?=)#i", "#(o)(nkeypress\\s?=)#i"];
        $this->message = preg_replace($js_array, "\$1<strong></strong>\$2\$4", $this->message);
        unset($js_array);
    }
    public function cache_tscode()
    {
        $this->tscode_cache = [];
        $standard_tscode["b"]["regex"] = "#\\[b\\](.*?)\\[/b\\]#si";
        $standard_tscode["b"]["replacement"] = "<div style=\"font-weight: bold; display: inline;\">\$1</div>";
        $standard_tscode["u"]["regex"] = "#\\[u\\](.*?)\\[/u\\]#si";
        $standard_tscode["u"]["replacement"] = "<div style=\"text-decoration: underline; display: inline;\">\$1</div>";
        $standard_tscode["i"]["regex"] = "#\\[i\\](.*?)\\[/i\\]#si";
        $standard_tscode["i"]["replacement"] = "<div style=\"font-style: italic; display: inline;\">\$1</div>";
        $standard_tscode["s"]["regex"] = "#\\[s\\](.*?)\\[/s\\]#si";
        $standard_tscode["s"]["replacement"] = "<del>\$1</del>";
        $standard_tscode["h"]["regex"] = "#\\[h\\](.*?)\\[/h\\]#si";
        $standard_tscode["h"]["replacement"] = "<h3>\$1</h3>";
        $standard_tscode["copy"]["regex"] = "#\\(c\\)#i";
        $standard_tscode["copy"]["replacement"] = "&copy;";
        $standard_tscode["tm"]["regex"] = "#\\(tm\\)#i";
        $standard_tscode["tm"]["replacement"] = "&#153;";
        $standard_tscode["reg"]["regex"] = "#\\(r\\)#i";
        $standard_tscode["reg"]["replacement"] = "&reg;";
        $standard_tscode["color"]["regex"] = "#\\[color=([a-zA-Z]*|\\#?[0-9a-fA-F]{6})](.*?)\\[/color\\]#si";
        $standard_tscode["color"]["replacement"] = "<div style=\"color: \$1; display: inline;\">\$2</div>";
        $standard_tscode["size"]["regex"] = "#\\[size=(.*?)\\](.+?)\\[/size\\]#si";
        $standard_tscode["size"]["replacement"] = "<span style=\"font-size: \$1;\">\$2</span>";
        $standard_tscode["font"]["regex"] = "#\\[font=([a-z ]+?)\\](.+?)\\[/font\\]#si";
        $standard_tscode["font"]["replacement"] = "<div style=\"font-family: \$1; display: inline;\">\$2</div>";
        $standard_tscode["align"]["regex"] = "#\\[align=(left|center|right|justify)\\](.*?)\\[/align\\]#si";
        $standard_tscode["align"]["replacement"] = "<div style=\"text-align: \$1;\">\$2</div>";
        $standard_tscode["hr"]["regex"] = "#\\[hr\\]#si";
        $standard_tscode["hr"]["replacement"] = "<hr />";
        $standard_tscode["pre"]["regex"] = "#\\[pre\\](.*?)\\[/pre\\]#si";
        $standard_tscode["pre"]["replacement"] = "<pre>\$1</pre>";
        $standard_tscode["nfo"]["regex"] = "#\\[nfo\\](.*?)\\[/nfo\\]#si";
        $standard_tscode["nfo"]["replacement"] = "<tt><div style=\"white-space: nowrap; display: inline;\"><font face=\"MS Linedraw\" size=\"2\" style=\"font-size: 10pt; line-height: 10pt\">\$1</font></div></tt>";
        $standard_tscode["youtube"]["regex"] = "#\\[youtube\\](.*?)\\[/youtube\\]#si";
        $standard_tscode["youtube"]["replacement"] = "<object width=\"425\" height=\"350\"><param name=\"movie\" value=\"http://www.youtube.com/v/\$1\"></param><embed src=\"http://www.youtube.com/v/\$1\" type=\"application/x-shockwave-flash\" width=\"425\" height=\"350\"></embed></object>";
        $tscode = $standard_tscode;
        foreach ($tscode as $code) {
            $this->tscode_cache["find"][] = $code["regex"];
            $this->tscode_cache["replacement"][] = $code["replacement"];
        }
    }
    public function tscode_parse_url($url, $name = "")
    {
        $LinkHasName = false;
        if ($name) {
            $LinkHasName = true;
            $name = str_replace(["&amp;", "\\'"], ["&", "'"], $name);
        }
        if (!preg_match("#^[a-z0-9]+://#i", $url)) {
            $url = "http://" . $url;
        }
        $url = str_replace(["&amp;", "\\'"], ["&", "'"], $url);
        $fullurl = $url;
        if (!$name) {
            $name = $url;
        }
        if (!$LinkHasName && $this->options["short_url"] && 55 < strlen($url)) {
            $name = substr($url, 0, 40) . "..." . substr($url, -10);
        }
        $entities = ["\$" => "%24", "&#36;" => "%24", "^" => "%5E", "`" => "%60", "[" => "%5B", "]" => "%5D", "{" => "%7B", "}" => "%7D", "\"" => "%22", "<" => "%3C", ">" => "%3E", " " => "%20"];
        $fullurl = str_replace(array_keys($entities), array_values($entities), $fullurl);
        $name = preg_replace("#&amp;\\#([0-9]+);#si", "&#\$1;", $name);
        $link = "<a href=\"" . $fullurl . "\" target=\"_blank\">" . $name . "</a>";
        return $link;
    }
    public function tscode_handle_size($size, $text)
    {
        $size = intval($size) + 10;
        if (50 < $size) {
            $size = 50;
        }
        $text = "<div style=\"font-size: " . $size . "pt; display: inline;\">" . str_replace("\\'", "'", $text) . "</div>";
        return $text;
    }
}
class TSSE_RSS_Poster
{
    public $xml_string = NULL;
    public $xml_array = NULL;
    public $xml_object = NULL;
    public $template = NULL;
    public $feedtype = NULL;
    public function __construct($options = NULL)
    {
    }
    public function set_xml_string(&$xml_string)
    {
        $this->xml_string =& $xml_string;
    }
    public function fetch_xml($url)
    {
        $xml_string =& fetch_file_via_socket($url);
        if ($xml_string === false || empty($xml_string["body"])) {
            trigger_error("Unable to fetch RSS Feed", 512);
        }
        $xml_string = $xml_string["body"];
        if (preg_match_all("#(<description>)(.*)(</description>)#siU", $xml_string, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (strpos(strtoupper($match[2]), "<![CDATA[") === false && strpos($match[2], "<") !== false) {
                    $output = $match[1] . "<![CDATA[" . $this->escape_cdata($match[2]) . "]]>" . $match[3];
                    $xml_string = str_replace($match[0], $output, $xml_string);
                }
            }
        }
        $this->set_xml_string($xml_string);
        return true;
    }
    public function escape_cdata($xml)
    {
        $xml = preg_replace("#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#", "", $xml);
        return str_replace(["<![CDATA[", "]]>"], ["�![CDATA[", "]]�"], $xml);
    }
    public function parse_xml($target_encoding = false, $ncrencode = false, $override_encoding = false, $escape_html = false)
    {
        $this->xml_object = new TSSE_XML_Parser($this->xml_string);
        $this->xml_object->disable_legacy_mode();
        $this->xml_object->set_target_encoding($target_encoding, $ncrencode, $escape_html);
        $this->xml_object->set_encoding($override_encoding);
        if ($this->xml_object->parse_xml()) {
            $this->xml_array =& $this->xml_object->parseddata;
            if (isset($this->xml_array["xmlns"]) && preg_match("#^http://www.w3.org/2005/atom\$#i", $this->xml_array["xmlns"])) {
                $this->feedtype = "atom";
            } else {
                if (is_array($this->xml_array["channel"])) {
                    $this->feedtype = "rss";
                } else {
                    $this->xml_array = [];
                    $this->feedtype = "unknown";
                    return false;
                }
            }
            return true;
        }
        $this->xml_array = [];
        $this->feedtype = "";
        return false;
    }
    public function fetch_item($id = -1)
    {
        switch ($this->feedtype) {
            case "atom":
                return fetch_item_atom($id);
                break;
            case "rss":
            default:
                return fetch_item_rss($id);
        }
    }
    public function fetch_item_atom($id = -1)
    {
        if (is_array($this->xml_array["entry"][0])) {
            $item =& $this->xml_array["entry"][$id == -1 ? $count++ : $id];
        } else {
            if ($count == 0 || $id == 0) {
                $item =& $this->xml_array["entry"];
            } else {
                $item = NULL;
            }
        }
        return $item;
    }
    public function fetch_item_rss($id = -1)
    {
        if (is_array($this->xml_array["channel"]["item"][0])) {
            $item =& $this->xml_array["channel"]["item"][$id == -1 ? $count++ : $id];
        } else {
            if ($count == 0 || $id == 0) {
                $item =& $this->xml_array["channel"]["item"];
            } else {
                $item = NULL;
            }
        }
        return $item;
    }
    public function fetch_items()
    {
        switch ($this->feedtype) {
            case "atom":
                $tagname = "entry";
                break;
            case "rss":
            default:
                $tagname = "item";
                return getelementsbytagname($this->xml_array, $tagname, true);
        }
    }
    public function fetch_normalised_items()
    {
        $items = $this->fetch_items();
        if (empty($items)) {
            return false;
        }
        $normalised_items = [];
        foreach ($items as $item) {
            $normalised_item = ["link" => $this->fetch_replacement("link", $item), "description" => $this->fetch_replacement("description", $item), "title" => $this->fetch_replacement("title", $item), "id" => $this->fetch_replacement("id", $item), "date" => $this->fetch_replacement("date", $item), "enclosure_link" => $this->fetch_replacement("enclosure_date", $item), "content" => $this->fetch_replacement("content", $item), "author" => $this->fetch_replacement("author", $item)];
            $normalised_item["link"] = $this->xss_clean_url($normalised_item["link"]);
            $normalised_item["enclosure_link"] = $this->xss_clean_url($normalised_item["enclosure_link"]);
            $normalised_items[] = $normalised_item;
        }
        return $normalised_items;
    }
    public function xss_clean($var)
    {
        return preg_replace($preg_find, $preg_replace, htmlspecialchars(trim($var)));
    }
    public function xss_clean_url($url)
    {
        if ($query = parse_url($url, PHP_URL_QUERY)) {
            $url = substr($url, 0, strpos($url, "?"));
            $url = $this->xss_clean($url);
            return $url . "?" . $query;
        }
        return $this->xss_clean($url);
    }
    public function parse_template($template, $item, $unhtmlspecialcharscron = true)
    {
        if (preg_match_all("#\\{(?:feed|rss):([\\w:\\[\\]]+)\\}#siU", $template, $matches)) {
            foreach ($matches[0] as $match_number => $field) {
                $replace = $this->fetch_replacement($matches[1][$match_number], $item);
                $template = str_replace($field, $replace, $template);
            }
        }
        if ($unhtmlspecialcharscron) {
            $template = unhtmlspecialcharscron($template);
        }
        return $template;
    }
    public function tsDate($timestamp = "")
    {
        $format = "m-d-Y h:i A";
        if (empty($timestamp)) {
            $timestamp = time();
        } else {
            if (strstr($timestamp, "-")) {
                $timestamp = strtotime($timestamp);
            }
        }
        return date($format, $timestamp);
    }
    public function fetch_replacement($field, $item)
    {
        switch ($this->feedtype) {
            case "atom":
                $handled_value = NULL;
                if ($handled_value !== NULL) {
                    return $handled_value;
                }
                switch ($field) {
                    case "link":
                        if (empty($item["link"])) {
                            if (!empty($item["guid"])) {
                                return $item["guid"]["value"];
                            }
                            return "";
                        }
                        if (empty($item["link"][0])) {
                            return $item["link"]["href"];
                        }
                        foreach ($item["link"] as $link) {
                            if ($link["rel"] == "alternate" || empty($link["rel"])) {
                                return $link["href"];
                            }
                        }
                        break;
                    case "description":
                        return get_item_value($item["summary"]);
                        break;
                    case "title":
                        return get_item_value($item["title"]);
                        break;
                    case "id":
                        return get_item_value($item["id"]);
                        break;
                    case "date":
                        $timestamp = strtotime(get_item_value($item["updated"]));
                        if (0 < $timestamp) {
                            return $this->tsDate($timestamp);
                        }
                        return get_item_value($item["updated"]);
                        break;
                    case "enclosure_link":
                        if (empty($item["link"][0])) {
                            return "";
                        }
                        foreach ($item["link"] as $link) {
                            if ($link["rel"] == "enclosure") {
                                return $link["href"];
                            }
                        }
                        break;
                    case "content":
                    case "content:encoded":
                        if (empty($item["content"][0])) {
                            return get_item_value($item["content"]);
                        }
                        $return = [];
                        foreach ($item["content"] as $contents) {
                            if (is_array($contents)) {
                                if ($contents["type"] == "html" && $return["type"] != "xhtml") {
                                    $return = $contents;
                                } else {
                                    if ($contents["type"] == "text" && !($return["type"] == "html" || $return["type"] == "xhtml")) {
                                        $return = $contents;
                                    } else {
                                        if ($contents["type"] == "xhtml") {
                                            $return = $contents;
                                        } else {
                                            if ($contents["type"] != "xhtml" || $contents["type"] != "xhtml" || $contents["type"] != "xhtml") {
                                                $return = $contents;
                                            }
                                        }
                                    }
                                }
                            } else {
                                if (empty($return["type"])) {
                                    $return["value"] = $contents;
                                }
                            }
                        }
                        return $return["value"];
                        break;
                    case "author":
                        return get_item_value($item["author"]["name"]);
                        break;
                    default:
                        if (is_array($item[(string) $field])) {
                            if (is_string($item[(string) $field]["value"])) {
                                return $item[(string) $field]["value"];
                            }
                            return "";
                        }
                        return $item[(string) $field];
                }
                break;
            case "rss":
                $handled_value = NULL;
                if ($handled_value !== NULL) {
                    return $handled_value;
                }
                switch ($field) {
                    case "link":
                        if (empty($item["link"])) {
                            if (!empty($item["guid"])) {
                                return $item["guid"]["value"];
                            }
                            return "";
                        }
                        if (is_array($item["link"]) && isset($item["link"]["href"])) {
                            return $item["link"]["href"];
                        }
                        return get_item_value($item["link"]);
                        break;
                    case "description":
                        return get_item_value($item["description"]);
                        break;
                    case "title":
                        return get_item_value($item["title"]);
                        break;
                    case "id":
                    case "guid":
                        return get_item_value($item["guid"]);
                        break;
                    case "pubDate":
                    case "date":
                        $timestamp = strtotime(get_item_value($item["pubDate"]));
                        if (0 < $timestamp) {
                            return $this->tsDate($timestamp);
                        }
                        return $item["pubDate"];
                        break;
                    case "enclosure_link":
                    case "enclosure_href":
                        if (is_array($item["enclosure"])) {
                            return $item["enclosure"]["url"];
                        }
                        return "";
                        break;
                    case "content":
                    case "content:encoded":
                        return get_item_value($item["content:encoded"]);
                        break;
                    case "author":
                    case "dc:creator":
                        if (isset($item["dc:creator"])) {
                            return get_item_value($item["dc:creator"]);
                        }
                        return $item["author"];
                        break;
                    default:
                        if (is_array($item[(string) $field])) {
                            if (is_string($item[(string) $field]["value"])) {
                                return $item[(string) $field]["value"];
                            }
                            return "";
                        }
                        return $item[(string) $field];
                }
                break;
        }
    }
}
class TSSE_XML_Parser
{
    public $xml_parser = NULL;
    public $error_no = 0;
    public $xmldata = "";
    public $parseddata = [];
    public $stack = [];
    public $cdata = "";
    public $tag_count = 0;
    public $include_first_tag = false;
    public $error_code = 0;
    public $error_line = 0;
    public $legacy_mode = true;
    public $encoding = NULL;
    public $target_encoding = NULL;
    public $ncr_encode = NULL;
    public $escape_html = NULL;
    public function __construct($xml, $path = "")
    {
        if ($xml !== false) {
            $this->xmldata = $xml;
        } else {
            if (empty($path)) {
                $this->error_no = 1;
            } else {
                if (!($this->xmldata = @file_get_contents($path))) {
                    $this->error_no = 2;
                }
            }
        }
    }
    public function &parse($encoding = "ISO-8859-1", $emptydata = true)
    {
        $this->encoding = $encoding;
        if (!$this->legacy_mode) {
            $this->resolve_target_encoding();
        }
        if (empty($this->xmldata) || 0 < $this->error_no) {
            $this->error_code = XML_ERROR_NO_ELEMENTS + ("5.2.8" < PHP_VERSION ? 0 : 1);
            return false;
        }
        if (!($this->xml_parser = xml_parser_create($encoding))) {
            return false;
        }
        xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 0);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_character_data_handler($this->xml_parser, [$this, "handle_cdata"]);
        xml_set_element_handler($this->xml_parser, [$this, "handle_element_start"], [$this, "handle_element_end"]);
        xml_parse($this->xml_parser, $this->xmldata, true);
        $err = xml_get_error_code($this->xml_parser);
        if ($emptydata) {
            $this->xmldata = "";
            $this->stack = [];
            $this->cdata = "";
        }
        if ($err) {
            $this->error_code = @xml_get_error_code($this->xml_parser);
            $this->error_line = @xml_get_current_line_number($this->xml_parser);
            xml_parser_free($this->xml_parser);
            return false;
        }
        xml_parser_free($this->xml_parser);
        return $this->parseddata;
    }
    public function parse_xml()
    {
        if ($this->legacy_mode) {
            return $this->legacy_parse_xml();
        }
        if (preg_match("#(<?xml.*encoding=['\"])(.*?)(['\"].*?>)#m", $this->xmldata, $match)) {
            $encoding = strtoupper($match[2]);
            if ($encoding != "UTF-8") {
                $this->xmldata = str_replace($match[0], $match[1] . "UTF-8" . $match[3], $this->xmldata);
            }
            if (!$this->encoding) {
                $this->encoding = $encoding;
            }
        } else {
            if (!$this->encoding) {
                $this->encoding = "UTF-8";
            }
            if (strpos($this->xmldata, "<?xml") === false) {
                $this->xmldata = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . $this->xmldata;
            } else {
                $this->xmldata = preg_replace("#(<?xml.*)(\\?>)#", "\\1 encoding=\"UTF-8\" \\2", $this->xmldata);
            }
        }
        if ("UTF-8" !== $this->encoding) {
            $this->xmldata = $this->to_utf8($this->xmldata, $this->encoding);
        }
        if (!$this->parse("UTF-8")) {
            return false;
        }
        return true;
    }
    public function to_utf8($in, $charset = false, $strip = true)
    {
        if ("" === $in || false === $in || is_null($in)) {
            return $in;
        }
        if (!$charset) {
            $charset = "UTF-8";
        }
        if (function_exists("iconv")) {
            $out = @iconv($charset, "UTF-8//IGNORE", $in);
            return $out;
        }
        if (function_exists("mb_convert_encoding")) {
            return @mb_convert_encoding($in, "UTF-8", $charset);
        }
        if (!$strip) {
            return $in;
        }
        $utf8 = "#([\\x09\\x0A\\x0D\\x20-\\x7E]|[\\xC2-\\xDF][\\x80-\\xBF]|\\xE0[\\xA0-\\xBF][\\x80-\\xBF]|[\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2}|\\xED[\\x80-\\x9F][\\x80-\\xBF]|\\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}|[\\xF1-\\xF3][\\x80-\\xBF]{3}|\\xF4[\\x80-\\x8F][\\x80-\\xBF]{2})#S";
        $out = "";
        $matches = [];
        while (preg_match($utf8, $in, $matches)) {
            $out .= $matches[0];
            $in = substr($in, strlen($matches[0]));
        }
        return $out;
    }
    public function legacy_parse_xml()
    {
        global $tracker_default_charset;
        if (preg_match("#(<?xml.*encoding=['\"])(.*?)(['\"].*?>)#m", $this->xmldata, $match)) {
            $in_encoding = strtoupper($match[2]);
            if ($in_encoding == "ISO-8859-1") {
                $in_encoding = "WINDOWS-1252";
            }
            if ($in_encoding != "UTF-8" || strtoupper($tracker_default_charset) != "UTF-8") {
                $this->xmldata = str_replace($match[0], $match[1] . "ISO-8859-1" . $match[3], $this->xmldata);
            }
        } else {
            $in_encoding = "UTF-8";
            if (strpos($this->xmldata, "<?xml") === false) {
                $this->xmldata = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n" . $this->xmldata;
            } else {
                $this->xmldata = preg_replace("#(<?xml.*)(\\?>)#", "\\1 encoding=\"ISO-8859-1\" \\2", $this->xmldata);
            }
            $in_encoding = "ISO-8859-1";
        }
        $orig_string = $this->xmldata;
        $target_encoding = strtolower($tracker_default_charset) == "iso-8859-1" ? "WINDOWS-1252" : $tracker_default_charset;
        $xml_encoding = $in_encoding != "UTF-8" || strtoupper($tracker_default_charset) != "UTF-8" ? "ISO-8859-1" : "UTF-8";
        $iconv_passed = false;
        if (strtoupper($in_encoding) !== strtoupper($target_encoding)) {
            if (function_exists("iconv") && ($encoded_data = iconv($in_encoding, $target_encoding . "//TRANSLIT", $this->xmldata))) {
                $iconv_passed = true;
                $this->xmldata =& $encoded_data;
            }
            if (!$iconv_passed && function_exists("mb_convert_encoding") && ($encoded_data = @mb_convert_encoding($this->xmldata, $target_encoding, $in_encoding))) {
                $this->xmldata =& $encoded_data;
            }
        }
        if ($this->parse($xml_encoding)) {
            return true;
        }
        if ($iconv_passed && ($this->xmldata = iconv($in_encoding, $target_encoding . "//IGNORE", $orig_string))) {
            if ($this->parse($xml_encoding)) {
                return true;
            }
            return false;
        }
        return false;
    }
    public function handle_cdata(&$parser, $data)
    {
        $this->cdata .= $data;
    }
    public function handle_element_start(&$parser, $name, $attribs)
    {
        $this->cdata = "";
        foreach ($attribs as $key => $val) {
            if (preg_match("#&[a-z]+;#i", $val)) {
                $attribs[(string) $key] = unhtmlspecialcharscron($val);
            }
        }
        array_unshift($this->stack, ["name" => $name, "attribs" => $attribs, "tag_count" => ++$this->tag_count]);
    }
    public function handle_element_end(&$parser, $name)
    {
        $tag = array_shift($this->stack);
        if ($tag["name"] != $name) {
            return NULL;
        }
        $output = $tag["attribs"];
        if (trim($this->cdata) !== "" || $tag["tag_count"] == $this->tag_count) {
            if (sizeof($output) == 0) {
                $output = $this->unescape_cdata($this->cdata);
            } else {
                $this->add_node($output, "value", $this->unescape_cdata($this->cdata));
            }
        }
        if (isset($this->stack[0])) {
            $this->add_node($this->stack[0]["attribs"], $name, $output);
        } else {
            if ($this->include_first_tag) {
                $this->parseddata = [$name => $output];
            } else {
                $this->parseddata = $output;
            }
        }
        $this->cdata = "";
    }
    public function error_string()
    {
        if ($errorstring = @xml_error_string(@$this->error_code())) {
            return $errorstring;
        }
        return "unknown";
    }
    public function error_line()
    {
        if ($this->error_line) {
            return $this->error_line;
        }
        return 0;
    }
    public function error_code()
    {
        if ($this->error_code) {
            return $this->error_code;
        }
        return 0;
    }
    public function add_node(&$children, $name, $value)
    {
        if (!is_array($children) || !in_array($name, array_keys($children))) {
            $children[$name] = $value;
        } else {
            if (is_array($children[$name]) && isset($children[$name][0])) {
                $children[$name][] = $value;
            } else {
                $children[$name] = [$children[$name]];
                $children[$name][] = $value;
            }
        }
    }
    public function unescape_cdata($xml)
    {
        if (!is_array($find)) {
            $find = ["�![CDATA[", "]]�", "\r\n", "\n"];
            $replace = ["<![CDATA[", "]]>", "\n", "\r\n"];
        }
        if (!$this->legacy_mode && $this->encoding != $this->target_encoding) {
            $xml = $this->encode($xml);
        }
        return str_replace($find, $replace, $xml);
    }
    public function set_encoding($encoding)
    {
        $this->encoding = $encoding;
    }
    public function set_target_encoding($target_encoding, $ncr_encode = false, $escape_html = false)
    {
        $this->target_encoding = $target_encoding;
        $this->ncr_encode = $ncr_encode;
        $this->escape_html = $escape_html;
    }
    public function resolve_target_encoding()
    {
        global $tracker_default_charset;
        if (!$this->target_encoding) {
            $this->target_encoding = $tracker_default_charset;
        }
        $this->target_encoding = strtoupper($this->target_encoding);
        if ("ISO-8859-1" == $this->target_encoding) {
            $this->target_encoding = "WINDOWS-1252";
        }
    }
    public function encode($data)
    {
        if ($this->encoding == $this->target_encoding) {
            return $data;
        }
        if ($this->escape_html) {
            $data = @htmlspecialchars($data, ENT_COMPAT, $this->encoding);
        }
        if ($this->ncr_encode) {
            $data = ncrencode($data, true);
        }
        return to_charset($data, $this->encoding, $this->target_encoding);
    }
    public function disable_legacy_mode($disable = true)
    {
        $this->legacy_mode = !$disable;
    }
}
class TSSE_XML_Builder
{
    public $charset = "windows-1252";
    public $content_type = "text/xml";
    public $open_tags = [];
    public $tabs = "";
    public function __construct($content_type = NULL, $charset = NULL)
    {
        global $tracker_default_charset;
        if ($content_type) {
            $this->content_type = $content_type;
        }
        if ($charset == NULL) {
            $charset = $tracker_default_charset;
        }
        $this->charset = strtolower($charset) == "iso-8859-1" ? "windows-1252" : $charset;
    }
    public function fetch_content_type_header()
    {
        return "Content-Type: " . $this->content_type . ($this->charset == "" ? "" : "; charset=" . $this->charset);
    }
    public function fetch_content_length_header()
    {
        return "Content-Length: " . $this->fetch_xml_content_length();
    }
    public function send_content_type_header()
    {
        @header("Content-Type: " . $this->content_type . ($this->charset == "" ? "" : "; charset=" . $this->charset));
    }
    public function send_content_length_header()
    {
        @header("Content-Length: " . @$this->fetch_xml_content_length());
    }
    public function fetch_xml_tag()
    {
        return "<?xml version=\"1.0\" encoding=\"" . $this->charset . "\"?>" . "\n";
    }
    public function fetch_xml_content_length()
    {
        return strlen($this->doc) + strlen($this->fetch_xml_tag());
    }
    public function add_group($tag, $attr = [])
    {
        $this->open_tags[] = $tag;
        $this->doc .= $this->tabs . $this->build_tag($tag, $attr) . "\n";
        $this->tabs .= "\t";
    }
    public function close_group()
    {
        $tag = array_pop($this->open_tags);
        $this->tabs = substr($this->tabs, 0, -1);
        $this->doc .= $this->tabs . "</" . $tag . ">\n";
    }
    public function add_tag($tag, $content = "", $attr = [], $cdata = false, $htmlspecialchars = false)
    {
        $this->doc .= $this->tabs . $this->build_tag($tag, $attr, $content === "");
        if ($content !== "") {
            if ($htmlspecialchars) {
                $this->doc .= htmlspecialchars($content);
            } else {
                if ($cdata || preg_match("/[\\<\\>\\&'\\\"\\[\\]]/", $content)) {
                    $this->doc .= "<![CDATA[" . $this->escape_cdata($content) . "]]>";
                } else {
                    $this->doc .= $content;
                }
            }
            $this->doc .= "</" . $tag . ">\n";
        }
    }
    public function build_tag($tag, $attr, $closing = false)
    {
        $tmp = "<" . $tag;
        if (!empty($attr)) {
            foreach ($attr as $attr_name => $attr_key) {
                if (strpos($attr_key, "\"") !== false) {
                    $attr_key = htmlspecialchars($attr_key);
                }
                $tmp .= " " . $attr_name . "=\"" . $attr_key . "\"";
            }
        }
        $tmp .= $closing ? " />\n" : ">";
        return $tmp;
    }
    public function escape_cdata($xml)
    {
        $xml = preg_replace("#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#", "", $xml);
        return str_replace(["<![CDATA[", "]]>"], ["�![CDATA[", "]]�"], $xml);
    }
    public function output()
    {
        if (!empty($this->open_tags)) {
            trigger_error("There are still open tags within the document", 256);
            return false;
        }
        return $this->doc;
    }
    public function print_xml($full_shutdown = false)
    {
        $this->send_content_type_header();
        if (strpos($_SERVER["SERVER_SOFTWARE"], "Microsoft-IIS") !== false) {
            $this->send_content_length_header();
        }
        echo $this->fetch_xml();
        exit;
    }
    public function fetch_xml()
    {
        return $this->fetch_xml_tag() . $this->output();
    }
}
class XMLparser extends TSSE_XML_Parser
{
}
class XMLexporter extends TSSE_XML_Builder
{
}
function htmltobbcode($text, $parse = true)
{
    $htmltags = ["/\\<b\\>(.*?)\\<\\/b\\>/is", "/\\<i\\>(.*?)\\<\\/i\\>/is", "/\\<u\\>(.*?)\\<\\/u\\>/is", "/\\<ul\\>(.*?)\\<\\/ul\\>/is", "/\\<li\\>(.*?)\\<\\/li\\>/is", "/\\<img(.*?) src=\\\"(.*?)\\\" (.*?)\\>/is", "/\\<div\\>(.*?)\\<\\/div\\>/is", "/\\<br(.*?)\\>/is", "/\\<strong\\>(.*?)\\<\\/strong\\>/is", "/\\<a href=\\\"(.*?)\\\"(.*?)\\>(.*?)\\<\\/a\\>/is"];
    $bbtags = ["[b]\$1[/b]", "[i]\$1[/i]", "[u]\$1[/u]", "[list]\$1[/list]", "[*]\$1", "[img]\$2[/img]", "\$1", "", "[b]\$1[/b]", "[url=\$1]\$3[/url]"];
    $text = preg_replace($htmltags, $bbtags, $text);
    if ($parse) {
        $parse = new TSParserCron();
        $parse->parse_message($text);
        return $parse->message;
    }
    return $text;
}
function &fetch_file_via_socket($rawurl, $postfields = [])
{
    $url = @parse_url($rawurl);
    if (!$url || empty($url["host"])) {
        trigger_error("Invalid URL specified to fetch_file_via_socket()", 256);
        return false;
    }
    if ($url["scheme"] == "https") {
        $url["port"] = $url["port"] ? $url["port"] : 443;
    } else {
        $url["port"] = isset($url["port"]) && $url["port"] ? $url["port"] : 80;
    }
    $url["path"] = isset($url["path"]) && $url["path"] ? $url["path"] : "/";
    if (empty($postfields)) {
        if (isset($url["query"]) && $url["query"]) {
            $url["path"] .= "?" . $url["query"];
        }
        $url["query"] = "";
        $method = "GET";
    } else {
        $fields = [];
        foreach ($postfields as $key => $value) {
            if (!empty($value)) {
                $fields[] = $key . "=" . urlencode($value);
            }
        }
        $url["query"] = implode("&", $fields);
        $method = "POST";
    }
    $communication = false;
    if (function_exists("curl_init") && ($ch = curl_init())) {
        curl_setopt($ch, CURLOPT_URL, $rawurl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $url["query"]);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "TSSE via cURL/PHP");
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        @curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        $full_result = curl_exec($ch);
        curl_close($ch);
        if ($full_result !== false) {
            $communication = true;
        }
    }
    if (!$communication) {
        $fp = fsockopen($url["host"], $url["port"], $errno, $errstr, 5);
        if (!$fp) {
            trigger_error("Unable to connect to host <i>" . $url["host"] . "</i>.<br />" . $errstr, 256);
            return false;
        }
        socket_set_timeout($fp, 5);
        $headers = $method . " " . $url["path"] . " HTTP/1.0\r\n";
        $headers .= "Host: " . $url["host"] . "\r\n";
        $headers .= "User-Agent: TSSE RSS Reader\r\n";
        if (function_exists("gzinflate")) {
            $headers .= "Accept-Encoding: gzip\r\n";
        }
        if ($method == "POST") {
            $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $headers .= "Content-Length: " . strlen($url["query"]) . "\r\n";
        }
        $headers .= "\r\n";
        fwrite($fp, $headers . $url["query"]);
        $full_result = "";
        while (!feof($fp)) {
            $result = fgets($fp, 1024);
            $full_result .= $result;
        }
        fclose($fp);
    }
    preg_match("#^(.*)\\r\\n\\r\\n(.*)\$#sU", $full_result, $matches);
    unset($full_result);
    if ($communication) {
        while (preg_match("#\r\nLocation: #i", $matches[1])) {
            preg_match("#^(.*)\\r\\n\\r\\n(.*)\$#sU", $matches[2], $matches);
        }
    }
    if (function_exists("gzinflate") && preg_match("#\r\nContent-encoding: gzip\r\n#i", $matches[1]) && ($inflated = @gzinflate(@substr($matches[2], 10)))) {
        $matches[2] =& $inflated;
    }
    return ["headers" => $matches[1], "body" => $matches[2]];
}
function getElementsByTagName(&$array, $tagname, $reinitialise = false, $depth = 0)
{
    if ($reinitialise) {
        $output = [];
    }
    if (is_array($array)) {
        foreach (array_keys($array) as $key) {
            if ($key === $tagname) {
                if (is_array($array[(string) $key])) {
                    if ($array[(string) $key][0]) {
                        foreach (array_keys($array[(string) $key]) as $item_key) {
                            $output[] =& $array[(string) $key][(string) $item_key];
                        }
                    } else {
                        $output[] =& $array[(string) $key];
                    }
                }
            } else {
                if (is_array($array[(string) $key]) && $depth < 30) {
                    getElementsByTagName($array[(string) $key], $tagname, false, $depth + 1);
                }
            }
        }
    }
    return $output;
}
function get_item_value($item)
{
    return is_array($item) ? $item["value"] : $item;
}

?>