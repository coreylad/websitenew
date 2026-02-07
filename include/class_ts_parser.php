<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

class TSParser
{
    public $TSParserVersion = "1.0.3";
    public $options = ["use_smilies" => 1, "max_smilies" => 30, "remove_badwords" => 1, "htmlspecialchars" => 1, "imagerel" => "posts", "auto_url" => 1, "short_url" => 1, "image_preview" => 1];
    public $smilies_cache = [];
    public $badwords_cache = [];
    public $tscode_cache = [];
    public $message = "";
    public $select_all = 0;
    public function __construct()
    {
        if ($this->options["use_smilies"] && !count($this->smilies_cache)) {
            $this->cache_smilies();
        }
        if ($this->options["remove_badwords"] && !count($this->badwords_cache)) {
            $this->cache_badwords();
        }
        if (!count($this->tscode_cache)) {
            $this->cache_tscode();
        }
    }
    public function parse_message($message = "", $options = [])
    {
        global $BASEURL;
        global $lang;
        if (count($options)) {
            $this->options = $options;
        }
        $this->message = trim($message);
        if (!$this->message) {
            return "";
        }
        if ($this->options["remove_badwords"]) {
            $this->parse_badwords();
        }
        $this->fix_javascript();
        preg_match_all("#\\[(code|php|sql)\\](.*?)\\[/\\1\\](\r\n?|\n?)#si", $this->message, $code_matches, PREG_SET_ORDER);
        $this->message = preg_replace("#\\[(code|php|sql)\\](.*?)\\[/\\1\\](\r\n?|\n?)#si", "~~~TSSE_CODE~~~\n", $this->message);
        if ($this->options["htmlspecialchars"]) {
            $this->message = str_replace("&amp;", "&", htmlspecialchars_uni(strip_tags($this->message)));
        }
        if ($this->options["use_smilies"]) {
            $this->parse_smilies();
        }
        $this->parse_codes();
        $this->message = nl2br($this->message);
        $this->message = preg_replace("/<br[\\s]?[\\/]?>/i", "<br />", $this->message);
        $this->message = preg_replace("/(<br[\\s]?[\\/]?>[\\s]*){3,}/i", "<br /><br />", $this->message);
        if ($code_matches && is_array($code_matches) && count($code_matches)) {
            if (!$this->select_all) {
                $this->select_all = 1;
                $this->message = "<script type=\"text/javascript\" src=\"" . $BASEURL . "/scripts/select_all.js?v=" . O_SCRIPT_VERSION . "\"></script>" . $this->message;
            }
            foreach ($code_matches as $text) {
                if (strtolower($text[1]) == "code") {
                    $text[2] = str_replace("&amp;", "&", htmlspecialchars_uni($text[2]));
                    $code = $this->tscode_parse_code($text[2]);
                } else {
                    if (strtolower($text[1]) == "php") {
                        $code = $this->tscode_parse_php($text[2]);
                    } else {
                        if (strtolower($text[1]) == "sql") {
                            $text[2] = str_replace("&amp;", "&", htmlspecialchars_uni($text[2]));
                            $code = $this->tscode_parse_sql($text[2]);
                        }
                    }
                }
                $this->message = preg_replace("#\\~~~TSSE_CODE~~~\n?#", $code, $this->message, 1);
            }
        }
        if (preg_match("/\\[hide\\](.*?)\\[\\/hide\\]/is", $this->message)) {
            while (preg_match("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", $this->message)) {
                if (!defined("IS_THIS_USER_POSTED")) {
                    $this->message = preg_replace("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", show_notice($lang->global["h1"], true, $lang->global["h2"], ""), $this->message);
                } else {
                    $this->message = preg_replace("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", show_notice("\$1", false, $lang->global["h3"], ""), $this->message);
                }
            }
        }
        $this->ts_wordwrap();
    }
    public function ts_wordwrap($wraptext = "  ")
    {
        $limit = 136;
        if (!empty($this->message)) {
            $this->message = preg_replace("\r\n\t\t\t\t#((?>[^\\s&/<>\"\\-\\[\\]]|&[\\#a-z0-9]{1,7};){" . $limit . "})(?=[^\\s&/<>\"\\-\\[\\]]|&[\\#a-z0-9]{1,7};)#i", "\$0" . $wraptext, $this->message);
        }
    }
    public function tscode_parse_php($code)
    {
        global $lang;
        $code = $this->strip_front_back_whitespace($code, 1);
        $codefind1 = ["<br>", "<br />"];
        $codereplace1 = ["", ""];
        $codefind2 = ["&gt;", "&lt;", "&quot;", "&amp;", "&#91;", "&#93;"];
        $codereplace2 = [">", "<", "\"", "&", "[", "]"];
        $code = rtrim(str_replace($codefind1, $codereplace1, $code));
        $blockheight = $this->fetch_block_height($code);
        $code = str_replace($codefind2, $codereplace2, $code);
        if (!preg_match("#<\\?#si", $code)) {
            $code = "<?php BEGIN__TSSE__CODE__SNIPPET " . $code . " \r\nEND__TSSE__CODE__SNIPPET ?>";
            $addedtags = true;
        } else {
            $addedtags = false;
        }
        $oldlevel = error_reporting(0);
        $code = highlight_string($code, true);
        error_reporting($oldlevel);
        if ($addedtags) {
            $search = ["#&lt;\\?php( |&nbsp;)BEGIN__TSSE__CODE__SNIPPET( |&nbsp;)#siU", "#(<(span|font)[^>]*>)&lt;\\?(</\\2>(<\\2[^>]*>))php( |&nbsp;)BEGIN__TSSE__CODE__SNIPPET( |&nbsp;)#siU", "#END__TSSE__CODE__SNIPPET( |&nbsp;)\\?(>|&gt;)#siU"];
            $replace = ["", "\\4", ""];
            $code = preg_replace($search, $replace, $code);
        }
        $code = preg_replace("/&amp;#([0-9]+);/", "&#\$1;", $code);
        $code = str_replace(["[", "]"], ["&#91;", "&#93;"], $code);
        return "<div class=\"codeeditor\"><div class=\"codetop\">PHP:</div><div class=\"codemain\" dir=\"ltr\"><code style=\"white-space:nowrap\" id=\"php_tag\">" . trim($code) . "</code></div></div>";
    }
    public function tscode_parse_sql($sql)
    {
        global $lang;
        $sql = preg_replace("/^<br>/", "", $sql);
        $sql = preg_replace("#^<br />#", "", $sql);
        $sql = preg_replace("/^\\s+/", "", $sql);
        if (!preg_match("/\\s+\$/", $sql)) {
            $sql = $sql . " ";
        }
        $sql = str_replace("\$", "&#36;", $sql);
        $sql = $this->strip_front_back_whitespace($sql, 1);
        $blockheight = $this->fetch_block_height($sql);
        $sql = preg_replace("#(=|\\+|\\-|&gt;|&lt;|~|==|\\!=|LIKE|NOT LIKE|REGEXP)#i", "<span style='color:orange'>\\1</span>", $sql);
        $sql = preg_replace("#(MAX|AVG|SUM|COUNT|MIN)\\(#i", "<span style='color:blue'>\\1</span>(", $sql);
        $sql = preg_replace("#(FROM|INTO)\\s{1,}(\\S+?)\\s{1,}((\\w+)\\s{0,})#i", "<span style='color:green'>\\1</span> <span style='color:orange'>\\2</span> <span style='color:orange'>\\3</span>", $sql);
        $sql = preg_replace("#(?<=join)\\s{1,}(\\S+?)\\s{1,}(\\w+)\\s{0,}#i", " <span style='color:orange'>\\1</span> <span style='color:orange'>\\2</span> ", $sql);
        $sql = preg_replace("!(&quot;|&#39;|&#039;)(.+?)(&quot;|&#39;|&#039;)!i", "<span style='color:red'>\\1\\2\\3</span>", $sql);
        $sql = preg_replace("#\\s{1,}(AND|OR|ON)\\s{1,}#i", " <span style='color:blue'>\\1</span> ", $sql);
        $sql = preg_replace("#(LEFT|JOIN|WHERE|MODIFY|CHANGE|AS|DISTINCT|IN|ASC|DESC|ORDER BY)\\s{1,}#i", "<span style='color:green'>\\1</span> ", $sql);
        $sql = preg_replace("#LIMIT\\s*(\\d+)(?:\\s*([,])\\s*(\\d+))*#i", "<span style='color:green'>LIMIT</span> <span style='color:orange'>\\1\\2 \\3</span>", $sql);
        $sql = preg_replace("#(SELECT|INSERT|UPDATE|DELETE|ALTER TABLE|CREATE TABLE|DROP)#i", "<span style='color:blue;font-weight:bold'>\\1</span>", $sql);
        return "</p><div class=\"codeeditor\"><div class=\"codetop\">SQL:</div><div dir=\"ltr\" class=\"codemain\"><code id=\"sql_tag\">" . trim($sql) . "</code></div></div><p>";
    }
    public function tscode_parse_code($code)
    {
        global $lang;
        $code = str_replace(["<br>", "<br />", "\\\""], ["", "", "\""], $code);
        $code = $this->strip_front_back_whitespace($code, 1);
        $blockheight = $this->fetch_block_height($code);
        return "<div class=\"codeeditor\"><div class=\"codetop\">" . $lang->global["code"] . ":</div><pre class=\"codemain\" dir=\"ltr\" id=\"code_tag\">" . trim($code) . "</pre></div>";
    }
    public function strip_front_back_whitespace($text, $max_amount = 1, $strip_front = true, $strip_back = true)
    {
        $max_amount = intval($max_amount);
        if ($strip_front) {
            $text = preg_replace("#^(( |\\t)*((<br>|<br />)[\\r\\n]*)|\\r\\n|\\n|\\r){0," . $max_amount . "}#si", "", $text);
        }
        if ($strip_back) {
            $text = strrev(preg_replace("#^(((>rb<|>/ rb<)[\\n\\r]*)|\\n\\r|\\n|\\r){0," . $max_amount . "}#si", "", strrev(rtrim($text))));
        }
        return $text;
    }
    public function fetch_block_height($code)
    {
        $numlines = count(explode("\n", $code));
        if (30 < $numlines) {
            $numlines = 30;
        } else {
            if ($numlines < 1) {
                $numlines = 1;
            }
        }
        return $numlines * 22;
    }
    public function parse_codes()
    {
        $this->tscode_parse_quotes();
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
        $this->message = preg_replace_callback("#\\[email\\](.*?)\\[/email\\]#si", function ($matches) {
            return $this->tscode_parse_email($matches[1]);
        }, $this->message);
        $this->message = preg_replace_callback("#\\[email=(.*?)\\](.*?)\\[/email\\]#si", function ($matches) {
            return $this->tscode_parse_email($matches[1], $matches[2]);
        }, $this->message);
        $this->message = preg_replace_callback("#\\[youtube\\](.*?)\\[\\/youtube\\]#si", function ($matches) {
            return $this->parseYoutube($matches[1]);
        }, $this->message);
        $this->message = preg_replace_callback("#\\[vimeo\\](.*?)\\[\\/vimeo\\]#si", function ($matches) {
            return $this->parseVimeo($matches[1]);
        }, $this->message);
        if (!defined("TS_CUSTOM_BBCODE")) {
            define("TS_CUSTOM_BBCODE", true);
        }
        require_once INC_PATH . "/ts_custom_bbcode.php";
        $this->message = ts_custom_bbcode($this->message);
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
    public function tscode_parse_quotes()
    {
        global $lang;
        $pattern = ["#\\[quote=(?:&quot;|\"|')?(.*?)[\"']?(?:&quot;|\"|')?\\](.*?)\\[\\/quote\\](\r\n?|\n?)#si", "#\\[quote\\](.*?)\\[\\/quote\\](\r\n?|\n?)#si"];
        $replace = ["<div class=\"quote\"><cite class=\"smallfont\">" . sprintf($lang->global["quote"], "\\1") . "</cite><blockquote class=\"bq\" dir=\"ltr\"><div>\$2</div></blockquote></div>", "<div class=\"quote\"><blockquote class=\"bq\" dir=\"ltr\"><div>\$1</div></blockquote></div>"];
        while (preg_match($pattern[0], $this->message) || preg_match($pattern[1], $this->message)) {
            $this->message = preg_replace($pattern, $replace, $this->message);
        }
    }
    public function tscode_auto_url()
    {
        $this->message = " " . $this->message;
        $this->message = preg_replace("#([\\>\\s\\(\\)])(https?|ftp|news){1}://([\\w\\-]+\\.([\\w\\-]+\\.)*[\\w]+(:[0-9]+)?(/[^\"\\s<\\[]*)?)#i", "\$1[url]\$2://\$3[/url]", $this->message);
        $this->message = preg_replace("#([\\>\\s\\(\\)])(www|ftp)\\.(([\\w\\-]+\\.)*[\\w]+(:[0-9]+)?(/[^\"\\s<\\[]*)?)#i", "\$1[url]\$2.\$3[/url]", $this->message);
        $this->message = substr($this->message, 1);
    }
    public function tscode_parse_list($message, $isOL = false)
    {
        $message = trim(str_replace("\\\"", "\"", $message));
        $LiTags = explode("[*]", $message);
        $return = $isOL ? "<ol>" : "<ul>";
        foreach ($LiTags as $Li) {
            if (trim($Li) != "") {
                $return .= "<li>" . $Li . "</li>";
            }
        }
        $return .= $isOL ? "</ol>" : "</ul>";
        return $return;
    }
    public function tscode_parse_img($url)
    {
        global $lang;
        $url = str_replace(["  ", "\"", "\\n", "\\r"], "", trim($url));
        if ($this->options["image_preview"] && !defined("IS_SHOUTBOX")) {
            $ImageHash = mt_rand() . "_" . md5($url);
            return "<span id=\"lazyload\"><span id=\"" . $ImageHash . "\">&nbsp;</span> <img src=\"" . $url . "\" border=\"0\" alt=\"\" onload=\"TSResizeImage(this, '" . $ImageHash . "');\" /></span>";
        }
        if (defined("IS_SHOUTBOX")) {
            return "<a href=\"" . $url . "\" target=\"_blank\" rel=\"nofollow\" class=\"colorbox\"><img src=\"" . $url . "\" border=\"0\" alt=\"\" style=\"max-width: 300px; max-height: 100px; vertical-align: top;\" /></a>";
        }
        return "<span id=\"lazyload\"><img src=\"" . $url . "\" border=\"0\" alt=\"\" /></span>";
    }
    public function cache_smilies()
    {
        global $rootpath;
        global $cache;
        if (is_file($rootpath . "/" . $cache . "/smilies.php")) {
            require $rootpath . "/" . $cache . "/smilies.php";
            $this->smilies_cache = $smilies;
            unset($smilies);
        } else {
            $this->smilies_cache = [];
        }
    }
    public function cache_badwords()
    {
        global $badwords;
        $this->badwords_cache = isset($badwords) && !empty($badwords) ? strpos($badwords, ",") === false ? [$badwords] : explode(",", $badwords) : [];
        unset($badwords);
    }
    public function parse_badwords()
    {
        global $censorchar;
        if (count($this->badwords_cache)) {
            if (!isset($censorchar) || empty($censorchar)) {
                $censorchar = "*";
            }
            foreach ($this->badwords_cache as $censorword) {
                $censorword = trim($censorword);
                if (!(empty($censorword) || $censorword == ",")) {
                    if (substr($censorword, 0, 2) == "\\{") {
                        if (substr($censorword, -2, 2) == "\\}") {
                            $censorword = substr($censorword, 2, -2);
                        }
                        $nonword_chars = "\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\x7f";
                        $this->message = preg_replace("#(?<=[" . $nonword_chars . "]|^)" . $censorword . "(?=[" . $nonword_chars . "]|\$)#si", str_repeat($censorchar, strlen($censorword)), $this->message);
                    } else {
                        $this->message = preg_replace("#" . $censorword . "#si", str_repeat($censorchar, strlen($censorword)), $this->message);
                    }
                }
            }
        }
    }
    public function fix_javascript()
    {
        $js_array = ["#(&\\#(0*)106;|&\\#(0*)74;|j)((&\\#(0*)97;|&\\#(0*)65;|a)(&\\#(0*)118;|&\\#(0*)86;|v)(&\\#(0*)97;|&\\#(0*)65;|a)(\\s)?(&\\#(0*)115;|&\\#(0*)83;|s)(&\\#(0*)99;|&\\#(0*)67;|c)(&\\#(0*)114;|&\\#(0*)82;|r)(&\\#(0*)105;|&\\#(0*)73;|i)(&\\#112;|&\\#(0*)80;|p)(&\\#(0*)116;|&\\#(0*)84;|t)(&\\#(0*)58;|\\:))#i", "#(o)(nmouseover\\s?=)#i", "#(o)(nmouseout\\s?=)#i", "#(o)(nmousedown\\s?=)#i", "#(o)(nmousemove\\s?=)#i", "#(o)(nmouseup\\s?=)#i", "#(o)(nclick\\s?=)#i", "#(o)(ndblclick\\s?=)#i", "#(o)(nload\\s?=)#i", "#(o)(nsubmit\\s?=)#i", "#(o)(nblur\\s?=)#i", "#(o)(nchange\\s?=)#i", "#(o)(nfocus\\s?=)#i", "#(o)(nselect\\s?=)#i", "#(o)(nunload\\s?=)#i", "#(o)(nkeypress\\s?=)#i"];
        $this->message = preg_replace($js_array, "\$1<strong></strong>\$2\$4", $this->message);
        unset($js_array);
    }
    public function parse_smilies()
    {
        global $BASEURL;
        global $pic_base_url;
        if (count($this->smilies_cache)) {
            $used = 0;
            foreach ($this->smilies_cache as $code => $image) {
                if ($this->options["max_smilies"] < $used) {
                } else {
                    if ($code && strpos($this->message, $code) !== false) {
                        $this->message = str_replace($code, "<img src=\"" . $pic_base_url . "smilies/" . $image . "\" border=\"0\" alt=\"\" title=\"\"" . (defined("IS_SHOUTBOX") ? " style=\"vertical-align: middle;\"" : "") . " />", $this->message);
                        $used++;
                    }
                }
            }
        }
    }
    public function cache_tscode()
    {
        $this->tscode_cache = [];
        $standard_tscode["b"]["regex"] = "#\\[b\\](.*?)\\[/b\\]#si";
        $standard_tscode["b"]["replacement"] = "<strong>\$1</strong>";
        $standard_tscode["i"]["regex"] = "#\\[i\\](.*?)\\[/i\\]#si";
        $standard_tscode["i"]["replacement"] = "<em>\$1</em>";
        $standard_tscode["u"]["regex"] = "#\\[u\\](.*?)\\[/u\\]#si";
        $standard_tscode["u"]["replacement"] = "<span style=\"text-decoration: underline;\">\$1</span>";
        $standard_tscode["s"]["regex"] = "#\\[s\\](.*?)\\[/s\\]#si";
        $standard_tscode["s"]["replacement"] = "<span style=\"text-decoration: line-through;\">\$1</span>";
        $standard_tscode["color"]["regex"] = "#\\[color=(.*?)\\](.+?)\\[/color\\]#si";
        $standard_tscode["color"]["replacement"] = "<span style=\"color: \$1;\">\$2</span>";
        $standard_tscode["size"]["regex"] = "#\\[size=(.*?)\\](.+?)\\[/size\\]#si";
        $standard_tscode["size"]["replacement"] = "<span style=\"font-size: \$1;\">\$2</span>";
        $standard_tscode["font"]["regex"] = "#\\[font=(.*?)\\](.+?)\\[/font\\]#si";
        $standard_tscode["font"]["replacement"] = "<span style=\"font-family: \$1;\">\$2</span>";
        $standard_tscode["align"]["regex"] = "#\\[align=(left|center|right|justify)\\](.*?)\\[/align\\]#si";
        $standard_tscode["align"]["replacement"] = "<p style=\"text-align: \$1;\">\$2</p>";
        $standard_tscode["hr"]["regex"] = "#\\[hr\\]#si";
        $standard_tscode["hr"]["replacement"] = "<hr />";
        $standard_tscode["h"]["regex"] = "#\\[h\\](.*?)\\[/h\\]#si";
        $standard_tscode["h"]["replacement"] = "<h3>\$1</h3>";
        $standard_tscode["pre"]["regex"] = "#\\[pre\\](.*?)\\[/pre\\]#si";
        $standard_tscode["pre"]["replacement"] = "<pre>\$1</pre>";
        $standard_tscode["nfo"]["regex"] = "#\\[nfo\\](.*?)\\[/nfo\\]#si";
        $standard_tscode["nfo"]["replacement"] = "<tt><div style=\"white-space: nowrap; display: inline;\"><font face=\"MS Linedraw\" size=\"2\" style=\"font-size: 10pt; line-height: 10pt\">\$1</font></div></tt>";
        $standard_tscode["copy"]["regex"] = "#\\(c\\)#i";
        $standard_tscode["copy"]["replacement"] = "&copy;";
        $standard_tscode["tm"]["regex"] = "#\\(tm\\)#i";
        $standard_tscode["tm"]["replacement"] = "&#153;";
        $standard_tscode["reg"]["regex"] = "#\\(r\\)#i";
        $standard_tscode["reg"]["replacement"] = "&reg;";
        $tscode = $standard_tscode;
        foreach ($tscode as $code) {
            $this->tscode_cache["find"][] = $code["regex"];
            $this->tscode_cache["replacement"][] = $code["replacement"];
        }
    }
    public function parseYoutube($URL = "")
    {
        $output = "";
        if ($URL) {
            $video_id = "";
            if (preg_match("%(?:youtube(?:-nocookie)?\\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\\.be/)([^\"&?/\\s]{11})%i", $URL, $match)) {
                $video_id = $match[1];
            }
            if ($video_id) {
                $output = "\r\n                <iframe id=\"player\" width=\"500\" height=\"314\" src=\"https://www.youtube.com/embed/" . $video_id . "\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>";
            }
        }
        return $output;
    }
    public function parseVimeo($URL = "")
    {
        $output = "";
        if ($URL) {
            $video_id = "";
            $regs = [];
            if (preg_match("/(https?:\\/\\/)?(www\\.)?(player\\.)?vimeo\\.com\\/([a-z]*\\/)*([0-9]{6,11})[?]?.*/", $URL, $output_array)) {
                $video_id = $output_array[5];
            }
            if ($video_id) {
                $output = "\r\n                <iframe src=\"https://player.vimeo.com/video/" . $video_id . "?title=0&byline=0&portrait=0\" width=\"500\" height=\"314\" frameborder=\"0\" allow=\"autoplay; fullscreen\" allowfullscreen></iframe>";
            }
        }
        return $output;
    }
    public function tscode_parse_url($url, $name = "")
    {
        global $BASEURL;
        global $a_anonymizer;
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
        if ($a_anonymizer == "yes" && !defined("ANONYMIZER_DISABLED")) {
            $link = "<a href=\"" . (!TS_Match($fullurl, $BASEURL) ? $BASEURL . "/redirector.php?url=" . $fullurl . "\" target=\"_blank\"" : $fullurl . "\"") . ">" . $name . "</a>";
        } else {
            $link = "<a href=\"" . $fullurl . "\"" . (!TS_Match($fullurl, $BASEURL) ? " target=\"_blank\"" : "") . ">" . $name . "</a>";
        }
        return $link;
    }
    public function tscode_parse_email($email, $name = "")
    {
        $name = str_replace("\\'", "'", $name);
        $email = str_replace("\\'", "'", $email);
        if (!$name) {
            $name = $email;
        }
        if (preg_match("/^([a-zA-Z0-9-_\\+\\.]+?)@[a-zA-Z0-9-]+\\.[a-zA-Z0-9\\.-]+\$/si", $email)) {
            return "<a href=\"mailto:" . $email . "\">" . $name . "</a>";
        }
        return $email;
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

?>