<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/read_pms.lang");
$Message = "";
$Found = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$keywords = "";
$type = "";
$query1 = "";
$query2 = "";
$link = "";
if ($Act == "delete" && isset($_POST["ids"]) && $_POST["ids"][0] != "") {
    $ids = implode(",", $_POST["ids"]);
    if ($ids) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM messages WHERE id IN (" . $ids . ")");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $SysMsg = str_replace(["{1}", "{2}"], [$ids, $_SESSION["ADMIN_USERNAME"]], $Language[10]);
            logStaffAction($SysMsg);
        }
    }
}
if ($Act == "search") {
    $keywords = isset($_GET["keywords"]) ? urldecode(trim($_GET["keywords"])) : (isset($_POST["keywords"]) ? trim($_POST["keywords"]) : "");
    $type = isset($_GET["type"]) ? urldecode(trim($_GET["type"])) : (isset($_POST["type"]) ? trim($_POST["type"]) : "");
    if ($keywords && $type) {
        $link = "act=search&amp;$keywords = " . urlencode(htmlspecialchars($keywords)) . "&amp;$type = " . urlencode(htmlspecialchars($type)) . "&amp;";
        if ($type == "sender" || $type == "receiver") {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "'");
            if (mysqli_num_rows($query)) {
                $Result = mysqli_fetch_assoc($query);
                $userid = $Result["id"];
                $query1 = " AND " . $type . " = '" . $userid . "'";
                $query2 = " AND m." . $type . " = '" . $userid . "'";
            } else {
                $Message = showAlertError($Language[9]);
            }
        } else {
            if ($type == "subject" || $type == "msg") {
                $query1 = " AND MATCH (" . $type . ") AGAINST ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "*' IN BOOLEAN MODE)";
                $query2 = " AND MATCH (m." . $type . ") AGAINST ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "*' IN BOOLEAN MODE)";
            }
        }
    } else {
        $Message = showAlertError($Language[15]);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
$LoggedAdminDetails = mysqli_fetch_assoc($query);
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM messages WHERE sender != 0" . $query1));
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=read_pms&amp;" . $link);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT m.*, u.username as sendername, uu.username as receivername, g.namestyle as sendernamestyle, g.cansettingspanel, g.canstaffpanel, g.issupermod, gg.namestyle as receivernamestyle, gg.cansettingspanel as cansettingspanel2, gg.canstaffpanel as canstaffpanel2, gg.issupermod as issupermod2 FROM messages m LEFT JOIN users u ON (m.$sender = u.id) LEFT JOIN users uu ON (m.$receiver = uu.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) LEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid) WHERE m.sender != 0" . $query2 . " ORDER BY m.added DESC " . $limit);
if (mysqli_num_rows($query) == 0) {
    echo "\r\n\t\r\n\t" . showAlertError($Language[9]) . "\t\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=read_pms&$act = search\" $method = \"post\" $name = \"search\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\"><b>" . $Language[12] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Language[13] . " <input $type = \"text\" $style = \"width: 40%;\" $value = \"" . htmlspecialchars($keywords) . "\" $name = \"keywords\" /> \r\n\t\t\t\t<select $name = \"type\">\r\n\t\t\t\t\t<option $value = \"0\"" . ($type == "0" ? " $selected = \"selected\"" : "") . ">" . $Language[14] . "</option>\r\n\t\t\t\t\t<option $value = \"sender\"" . ($type == "sender" ? " $selected = \"selected\"" : "") . ">" . $Language[4] . "</option>\r\n\t\t\t\t\t<option $value = \"receiver\"" . ($type == "receiver" ? " $selected = \"selected\"" : "") . ">" . $Language[5] . "</option>\r\n\t\t\t\t\t<option $value = \"subject\"" . ($type == "subject" ? " $selected = \"selected\"" : "") . ">" . $Language[7] . "</option>\r\n\t\t\t\t\t<option $value = \"msg\"" . ($type == "msg" ? " $selected = \"selected\"" : "") . ">" . $Language[8] . "</option>\r\n\t\t\t\t</select> \r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[12] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t</form>";
} else {
    for ($Count = 0; $Msg = mysqli_fetch_assoc($query); $Count++) {
        $class = $Count % 2 == 1 ? "alt2" : "alt1";
        if ($Msg["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $Msg["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $Msg["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes" || $Msg["cansettingspanel2"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $Msg["canstaffpanel2"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $Msg["issupermod2"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes") {
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"6\" class=\"" . $class . "\">\r\n\t\t\t\t\t" . $Language[16] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        } else {
            $msgtext = function_114($Msg["msg"]);
            $Found .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $valign = \"top\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $Msg["sendername"] . "\">" . applyUsernameStyle($Msg["sendername"], $Msg["sendernamestyle"]) . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $valign = \"top\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $Msg["receivername"] . "\">" . applyUsernameStyle($Msg["receivername"], $Msg["receivernamestyle"]) . "</a>\r\n\t\t\t\t\t</td>\t\t\t\t\t\r\n\t\t\t\t\t<td class=\"" . $class . "\" $valign = \"top\">\r\n\t\t\t\t\t\t" . formatTimestamp($Msg["added"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $valign = \"top\">\r\n\t\t\t\t\t\t<font $color = \"" . ($Msg["unread"] == "yes" ? "red" : "green") . "\">" . htmlspecialchars($Msg["subject"]) . "</font>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $msgtext . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\" $valign = \"top\" $style = \"width: 100px;\">\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"ids[]\" $value = \"" . $Msg["id"] . "\" $checkme = \"group\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\t\t\t\t\r\n\t\t\t\t";
        }
    }
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\t\r\n\t\r\n\t" . $Message . "\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=read_pms&$act = search\" $method = \"post\" $name = \"search\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\"><b>" . $Language[12] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Language[13] . " <input $type = \"text\" $style = \"width: 40%;\" $value = \"" . htmlspecialchars($keywords) . "\" $name = \"keywords\" /> \r\n\t\t\t\t<select $name = \"type\">\r\n\t\t\t\t\t<option $value = \"0\"" . ($type == "0" ? " $selected = \"selected\"" : "") . ">" . $Language[14] . "</option>\r\n\t\t\t\t\t<option $value = \"sender\"" . ($type == "sender" ? " $selected = \"selected\"" : "") . ">" . $Language[4] . "</option>\r\n\t\t\t\t\t<option $value = \"receiver\"" . ($type == "receiver" ? " $selected = \"selected\"" : "") . ">" . $Language[5] . "</option>\r\n\t\t\t\t\t<option $value = \"subject\"" . ($type == "subject" ? " $selected = \"selected\"" : "") . ">" . $Language[7] . "</option>\r\n\t\t\t\t\t<option $value = \"msg\"" . ($type == "msg" ? " $selected = \"selected\"" : "") . ">" . $Language[8] . "</option>\r\n\t\t\t\t</select> \r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[12] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t</form>\r\n\t" . $pagertop . "\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=read_pms&$act = delete" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"read_pms\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"6\"><b>" . $Language[2] . " (" . number_format($results) . ")</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('read_pms', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"6\" $align = \"right\" class=\"tcat2\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[11] . "\" />\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t" . $pagertop;
}
class Class_6
{
    public $TSParserVersion = "1.0.3";
    public $options = ["use_smilies" => 1, "max_smilies" => 30, "remove_badwords" => 1, "htmlspecialchars" => 1, "imagerel" => "posts", "auto_url" => 1, "short_url" => 1, "image_preview" => 1];
    public $smilies_cache = [];
    public $badwords_cache = [];
    public $tscode_cache = [];
    public $message = "";
    public $select_all = 0;
    public $Settings = [];
    public function __construct()
    {
        $this->function_115();
        if ($this->options["use_smilies"] && !count($this->smilies_cache)) {
            $this->function_116();
        }
        if (!count($this->tscode_cache)) {
            $this->function_117();
        }
    }
    public function function_115()
    {
        $var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
        $Result = mysqli_fetch_assoc($var_281);
        $this->Settings = unserialize($Result["content"]);
    }
    public function function_118($text, $entities = true)
    {
        return str_replace(["<", ">", "\"", "'"], ["&lt;", "&gt;", "&quot;", "&#039;"], preg_replace("/&(?!" . ($entities ? "#[0-9]+|shy" : "(#[0-9]+|[a-z]+)") . ";)/si", "&amp;", $text));
    }
    public function function_119($message = "", $options = [])
    {
        if (count($options)) {
            $this->$options = $options;
        }
        $this->$message = trim($message);
        if (!$this->message) {
            return "";
        }
        $this->function_120();
        preg_match_all("#\\[(code|php|sql)\\](.*?)\\[/\\1\\](\r\n?|\n?)#si", $this->message, $var_341, PREG_SET_ORDER);
        $this->$message = preg_replace("#\\[(code|php|sql)\\](.*?)\\[/\\1\\](\r\n?|\n?)#si", "~~~TSSE_CODE~~~\n", $this->message);
        if ($this->options["htmlspecialchars"]) {
            $this->$message = str_replace("&amp;", "&", $this->function_118(strip_tags($this->message)));
        }
        if ($this->options["use_smilies"]) {
            $this->function_121();
        }
        $this->function_122();
        $this->$message = nl2br($this->message);
        if ($var_341 && is_array($var_341) && count($var_341)) {
            if (!$this->select_all) {
                $this->$select_all = 1;
                $this->$message = "<script $type = \"text/javascript\" $src = \"" . $this->Settings["BASEURL"] . "/scripts/select_all.js\"></script>" . $this->message;
            }
            foreach ($var_341 as $text) {
                if (strtolower($text[1]) == "code") {
                    $text[2] = str_replace("&amp;", "&", $this->function_118($text[2]));
                    $var_342 = $this->function_123($text[2]);
                } else {
                    if (strtolower($text[1]) == "php") {
                        $var_342 = $this->function_124($text[2]);
                    } else {
                        if (strtolower($text[1]) == "sql") {
                            $text[2] = str_replace("&amp;", "&", $this->function_118($text[2]));
                            $var_342 = $this->function_125($text[2]);
                        }
                    }
                }
                $this->$message = preg_replace("#\\~~~TSSE_CODE~~~\n?#", $var_342, $this->message, 1);
            }
        }
        $this->function_126();
    }
    public function function_126($wraptext = "  ")
    {
        $limit = 136;
        if (!empty($this->message)) {
            $this->$message = preg_replace("\r\n\t\t\t\t#((?>[^\\s&/<>\"\\-\\[\\]]|&[\\#a-z0-9]{1,7};){" . $limit . "})(?=[^\\s&/<>\"\\-\\[\\]]|&[\\#a-z0-9]{1,7};)#i", "\$0" . $wraptext, $this->message);
        }
    }
    public function function_124($code)
    {
        $code = $this->function_127($code, 1);
        $var_343 = ["<br>", "<br />"];
        $var_344 = ["", ""];
        $var_345 = ["&gt;", "&lt;", "&quot;", "&amp;", "&#91;", "&#93;"];
        $var_346 = [">", "<", "\"", "&", "[", "]"];
        $code = rtrim(str_replace($var_343, $var_344, $code));
        $var_347 = $this->function_128($code);
        $code = str_replace($var_345, $var_346, $code);
        if (!preg_match("#<\\?#si", $code)) {
            $code = "<?php BEGIN__TSSE__CODE__SNIPPET " . $code . " \r\nEND__TSSE__CODE__SNIPPET ?>";
            $var_348 = true;
        } else {
            $var_348 = false;
        }
        $var_349 = error_reporting(0);
        $code = highlight_string($code, true);
        error_reporting($var_349);
        if ($var_348) {
            $var_350 = ["#&lt;\\?php( |&nbsp;)BEGIN__TSSE__CODE__SNIPPET( |&nbsp;)#siU", "#(<(span|font)[^>]*>)&lt;\\?(</\\2>(<\\2[^>]*>))php( |&nbsp;)BEGIN__TSSE__CODE__SNIPPET( |&nbsp;)#siU", "#END__TSSE__CODE__SNIPPET( |&nbsp;)\\?(>|&gt;)#siU"];
            $var_351 = ["", "\\4", ""];
            $code = preg_replace($var_350, $var_351, $code);
        }
        $code = preg_replace("/&amp;#([0-9]+);/", "&#\$1;", $code);
        $code = str_replace(["[", "]"], ["&#91;", "&#93;"], $code);
        return "<div class=\"codetop\">PHP: (<a $href = \"javascript:void(0);\" $onclick = \"return HighlightText(this);\" class=\"codeoperation\">+</a>)</div><div class=\"codemain\" $dir = \"ltr\" $style = \"height: " . $var_347 . "px;\"><code $style = \"white-space:nowrap\" $id = \"php_tag\">" . trim($code) . "</code></div>";
    }
    public function function_125($sql)
    {
        $sql = preg_replace("/^<br>/", "", $sql);
        $sql = preg_replace("#^<br />#", "", $sql);
        $sql = preg_replace("/^\\s+/", "", $sql);
        if (!preg_match("/\\s+\$/", $sql)) {
            $sql = $sql . " ";
        }
        $sql = str_replace("\$", "&#36;", $sql);
        $sql = $this->function_127($sql, 1);
        $var_347 = $this->function_128($sql);
        $sql = preg_replace("#(=|\\+|\\-|&gt;|&lt;|~|==|\\!=|LIKE|NOT LIKE|REGEXP)#i", "<span $style = 'color:orange'>\\1</span>", $sql);
        $sql = preg_replace("#(MAX|AVG|SUM|COUNT|MIN)\\(#i", "<span $style = 'color:blue'>\\1</span>(", $sql);
        $sql = preg_replace("#(FROM|INTO)\\s{1,}(\\S+?)\\s{1,}((\\w+)\\s{0,})#i", "<span $style = 'color:green'>\\1</span> <span $style = 'color:orange'>\\2</span> <span $style = 'color:orange'>\\3</span>", $sql);
        $sql = preg_replace("#(?<=join)\\s{1,}(\\S+?)\\s{1,}(\\w+)\\s{0,}#i", " <span $style = 'color:orange'>\\1</span> <span $style = 'color:orange'>\\2</span> ", $sql);
        $sql = preg_replace("!(&quot;|&#39;|&#039;)(.+?)(&quot;|&#39;|&#039;)!i", "<span $style = 'color:red'>\\1\\2\\3</span>", $sql);
        $sql = preg_replace("#\\s{1,}(AND|OR|ON)\\s{1,}#i", " <span $style = 'color:blue'>\\1</span> ", $sql);
        $sql = preg_replace("#(LEFT|JOIN|WHERE|MODIFY|CHANGE|AS|DISTINCT|IN|ASC|DESC|ORDER BY)\\s{1,}#i", "<span $style = 'color:green'>\\1</span> ", $sql);
        $sql = preg_replace("#LIMIT\\s*(\\d+)(?:\\s*([,])\\s*(\\d+))*#i", "<span $style = 'color:green'>LIMIT</span> <span $style = 'color:orange'>\\1\\2 \\3</span>", $sql);
        $sql = preg_replace("#(SELECT|INSERT|UPDATE|DELETE|ALTER TABLE|CREATE TABLE|DROP)#i", "<span $style = 'color:blue;font-weight:bold'>\\1</span>", $sql);
        return "</p><div class=\"codetop\">SQL: (<a $href = \"javascript:void(0);\" $onclick = \"return HighlightText(this);\" class=\"codeoperation\">+</a>)</div><div $dir = \"ltr\" $style = \"height:" . $var_347 . "px;\" class=\"codemain\"><code $id = \"sql_tag\">" . trim($sql) . "</code></div><p>";
    }
    public function function_123($code)
    {
        $code = str_replace(["<br>", "<br />", "\\\""], ["", "", "\""], $code);
        $code = $this->function_127($code, 1);
        $var_347 = $this->function_128($code);
        return "<div class=\"codetop\">Code: (<a $href = \"javascript:void(0);\" $onclick = \"return HighlightText(this);\" class=\"codeoperation\">+</a>)</div><pre class=\"codemain\" $dir = \"ltr\" $style = \"height: " . $var_347 . "px;\" $id = \"code_tag\">" . trim($code) . "</pre>";
    }
    public function function_127($text, $max_amount = 1, $strip_front = true, $strip_back = true)
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
    public function function_128($code)
    {
        $var_352 = count(explode("\n", $code));
        if (30 < $var_352) {
            $var_352 = 30;
        } else {
            if ($var_352 < 1) {
                $var_352 = 1;
            }
        }
        return $var_352 * 22;
    }
    public function function_122()
    {
        $this->function_129();
        if ($this->options["auto_url"]) {
            $this->function_130();
        }
        $this->$message = str_replace("\$", "&#36;", $this->message);
        $this->$message = preg_replace($this->tscode_cache["find"], $this->tscode_cache["replacement"], $this->message);
        $this->$message = preg_replace_callback("#\\[url\\]([a-z]+?://)([^\r\n\"<]+?)\\[/url\\]#si", function ($matches) {
            return $this->function_131($matches[1] . $matches[2]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[url\\]([^\r\n\"<]+?)\\[/url\\]#si", function ($matches) {
            return $this->function_131($matches[1]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[$url = ([a-z]+?://)([^\r\n\"<]+?)\\](.+?)\\[/url\\]#si", function ($matches) {
            return $this->function_131($matches[1] . $matches[2], $matches[3]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[$url = ([^\r\n\"<&\\(\\)]+?)\\](.+?)\\[/url\\]#si", function ($matches) {
            return $this->function_131($matches[1], $matches[2]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[email\\](.*?)\\[/email\\]#si", function ($matches) {
            return $this->function_132($matches[1]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[$email = (.*?)\\](.*?)\\[/email\\]#si", function ($matches) {
            return $this->function_132($matches[1], $matches[2]);
        }, $this->message);
    }
    public function function_129()
    {
        $var_353 = ["#\\[$quote = (?:&quot;|\"|')?(.*?)[\"']?(?:&quot;|\"|')?\\](.*?)\\[\\/quote\\](\r\n?|\n?)#si", "#\\[quote\\](.*?)\\[\\/quote\\](\r\n?|\n?)#si"];
        $var_351 = ["<div class=\"quote\"><cite class=\"smallfont\">Quote: \\1</cite><blockquote class=\"bq\" $dir = \"ltr\"><div>\$2</div></blockquote></div>", "<div class=\"quote\"><blockquote class=\"bq\" $dir = \"ltr\"><div>\$1</div></blockquote></div>"];
        while (preg_match($var_353[0], $this->message) || preg_match($var_353[1], $this->message)) {
            $this->$message = preg_replace($var_353, $var_351, $this->message);
        }
    }
    public function function_130()
    {
        $this->$message = " " . $this->message;
        $this->$message = preg_replace("#([\\>\\s\\(\\)])(https?|ftp|news){1}://([\\w\\-]+\\.([\\w\\-]+\\.)*[\\w]+(:[0-9]+)?(/[^\"\\s<\\[]*)?)#i", "\$1[url]\$2://\$3[/url]", $this->message);
        $this->$message = preg_replace("#([\\>\\s\\(\\)])(www|ftp)\\.(([\\w\\-]+\\.)*[\\w]+(:[0-9]+)?(/[^\"\\s<\\[]*)?)#i", "\$1[url]\$2.\$3[/url]", $this->message);
        $this->$message = substr($this->message, 1);
    }
    public function function_133($message, $isOL = false)
    {
        $var_354 = explode("[*]", $message);
        $var_299 = $isOL ? "<ol>" : "<ul>";
        foreach ($var_354 as $var_355) {
            if (trim($var_355) != "") {
                $var_299 .= "<li>" . $var_355 . "</li>";
            }
        }
        $var_299 .= $isOL ? "</ol>" : "</ul>";
        return $var_299;
    }
    public function function_134($url)
    {
        $url = str_replace(["  ", "\"", "\\n", "\\r"], "", trim($url));
        if ($this->options["image_preview"]) {
            $var_356 = mt_rand() . "_" . md5($url);
            return "<span $id = \"lazyload\"><span $id = \"" . $var_356 . "\">&nbsp;</span> <a $href = \"" . str_replace("&$small = true", "", $url) . "\" $id = \"ts_show_preview\" $alt = \"\"><img $src = \"" . $url . "\" $border = \"0\" $alt = \"\" /></a></span>";
        }
        return "<span $id = \"lazyload\"><img $src = \"" . $url . "\" $border = \"0\" $alt = \"\" /></span>";
    }
    public function function_116()
    {
        $var_357 = "./../../" . $this->Settings["cache"] . "/";
        if (is_file($var_357 . "smilies.php")) {
            require $var_357 . "smilies.php";
            $this->$smilies_cache = $var_358;
            unset($var_358);
        } else {
            $this->$smilies_cache = [];
        }
    }
    public function function_120()
    {
        $var_359 = ["#(&\\#(0*)106;|&\\#(0*)74;|j)((&\\#(0*)97;|&\\#(0*)65;|a)(&\\#(0*)118;|&\\#(0*)86;|v)(&\\#(0*)97;|&\\#(0*)65;|a)(\\s)?(&\\#(0*)115;|&\\#(0*)83;|s)(&\\#(0*)99;|&\\#(0*)67;|c)(&\\#(0*)114;|&\\#(0*)82;|r)(&\\#(0*)105;|&\\#(0*)73;|i)(&\\#112;|&\\#(0*)80;|p)(&\\#(0*)116;|&\\#(0*)84;|t)(&\\#(0*)58;|\\:))#i", "#(o)(nmouseover\\s?=)#i", "#(o)(nmouseout\\s?=)#i", "#(o)(nmousedown\\s?=)#i", "#(o)(nmousemove\\s?=)#i", "#(o)(nmouseup\\s?=)#i", "#(o)(nclick\\s?=)#i", "#(o)(ndblclick\\s?=)#i", "#(o)(nload\\s?=)#i", "#(o)(nsubmit\\s?=)#i", "#(o)(nblur\\s?=)#i", "#(o)(nchange\\s?=)#i", "#(o)(nfocus\\s?=)#i", "#(o)(nselect\\s?=)#i", "#(o)(nunload\\s?=)#i", "#(o)(nkeypress\\s?=)#i"];
        $this->$message = preg_replace($var_359, "\$1<strong></strong>\$2\$4", $this->message);
        unset($var_359);
    }
    public function function_121()
    {
        if (count($this->smilies_cache)) {
            $var_360 = 0;
            foreach ($this->smilies_cache as $code => $var_361) {
                if ($this->options["max_smilies"] < $var_360) {
                } else {
                    if (strpos($this->message, $code) !== false) {
                        $this->$message = str_replace($code, "<img $src = \"" . $this->Settings["pic_base_url"] . "smilies/" . $var_361 . "\" $border = \"0\" $alt = \"\" $title = \"\" />", $this->message);
                        $var_360++;
                    }
                }
            }
        }
    }
    public function function_117()
    {
        $this->$tscode_cache = [];
        $var_362["b"]["regex"] = "#\\[b\\](.*?)\\[/b\\]#si";
        $var_362["b"]["replacement"] = "<strong>\$1</strong>";
        $var_362["i"]["regex"] = "#\\[i\\](.*?)\\[/i\\]#si";
        $var_362["i"]["replacement"] = "<em>\$1</em>";
        $var_362["u"]["regex"] = "#\\[u\\](.*?)\\[/u\\]#si";
        $var_362["u"]["replacement"] = "<span $style = \"text-decoration: underline;\">\$1</span>";
        $var_362["s"]["regex"] = "#\\[s\\](.*?)\\[/s\\]#si";
        $var_362["s"]["replacement"] = "<span $style = \"text-decoration: line-through;\">\$1</span>";
        $var_362["color"]["regex"] = "#\\[$color = (.*?)\\](.+?)\\[/color\\]#si";
        $var_362["color"]["replacement"] = "<span $style = \"color: \$1;\">\$2</span>";
        $var_362["size"]["regex"] = "#\\[$size = (.*?)\\](.+?)\\[/size\\]#si";
        $var_362["size"]["replacement"] = "<span $style = \"font-size: \$1;\">\$2</span>";
        $var_362["font"]["regex"] = "#\\[$font = (.*?)\\](.+?)\\[/font\\]#si";
        $var_362["font"]["replacement"] = "<span $style = \"font-family: \$1;\">\$2</span>";
        $var_362["align"]["regex"] = "#\\[$align = (left|center|right|justify)\\](.*?)\\[/align\\]#si";
        $var_362["align"]["replacement"] = "<p $style = \"text-align: \$1;\">\$2</p>";
        $var_362["hr"]["regex"] = "#\\[hr\\]#si";
        $var_362["hr"]["replacement"] = "<hr />";
        $var_362["h"]["regex"] = "#\\[h\\](.*?)\\[/h\\]#si";
        $var_362["h"]["replacement"] = "<h3>\$1</h3>";
        $var_362["pre"]["regex"] = "#\\[pre\\](.*?)\\[/pre\\]#si";
        $var_362["pre"]["replacement"] = "<pre>\$1</pre>";
        $var_362["nfo"]["regex"] = "#\\[nfo\\](.*?)\\[/nfo\\]#si";
        $var_362["nfo"]["replacement"] = "<tt><div $style = \"white-space: nowrap; display: inline;\"><font $face = \"MS Linedraw\" $size = \"2\" $style = \"font-size: 10pt; line-height: 10pt\">\$1</font></div></tt>";
        $var_362["copy"]["regex"] = "#\\(c\\)#i";
        $var_362["copy"]["replacement"] = "&copy;";
        $var_362["tm"]["regex"] = "#\\(tm\\)#i";
        $var_362["tm"]["replacement"] = "&#153;";
        $var_362["reg"]["regex"] = "#\\(r\\)#i";
        $var_362["reg"]["replacement"] = "&reg;";
        $var_362["youtube"]["regex"] = "#\\[youtube\\](.*?)\\[/youtube\\]#si";
        $var_362["youtube"]["replacement"] = "<object $width = \"425\" $height = \"350\"><param $name = \"movie\" $value = \"http://www.youtube.com/v/\$1\"></param><embed $src = \"http://www.youtube.com/v/\$1\" $type = \"application/x-shockwave-flash\" $width = \"425\" $height = \"350\"></embed></object>";
        $var_363 = $var_362;
        foreach ($var_363 as $code) {
            $this->tscode_cache["find"][] = $code["regex"];
            $this->tscode_cache["replacement"][] = $code["replacement"];
        }
    }
    public function function_131($url, $name = "")
    {
        $var_364 = false;
        if ($name) {
            $var_364 = true;
            $name = str_replace(["&amp;", "\\'"], ["&", "'"], $name);
        }
        if (!preg_match("#^[a-z0-9]+://#i", $url)) {
            $url = "http://" . $url;
        }
        $url = str_replace(["&amp;", "\\'"], ["&", "'"], $url);
        $var_365 = $url;
        if (!$name) {
            $name = $url;
        }
        if (!$var_364 && $this->options["short_url"] && 55 < strlen($url)) {
            $name = substr($url, 0, 40) . "..." . substr($url, -10);
        }
        $entities = ["\$" => "%24", "&#36;" => "%24", "^" => "%5E", "`" => "%60", "[" => "%5B", "]" => "%5D", "{" => "%7B", "}" => "%7D", "\"" => "%22", "<" => "%3C", ">" => "%3E", " " => "%20"];
        $var_365 = str_replace(array_keys($entities), array_values($entities), $var_365);
        $name = preg_replace("#&amp;\\#([0-9]+);#si", "&#\$1;", $name);
        $link = "<a $href = \"" . $var_365 . "\" $target = \"_blank\">" . $name . "</a>";
        return $link;
    }
    public function function_132($email, $name = "")
    {
        $name = str_replace("\\'", "'", $name);
        $email = str_replace("\\'", "'", $email);
        if (!$name) {
            $name = $email;
        }
        if (preg_match("/^([a-zA-Z0-9-_\\+\\.]+?)@[a-zA-Z0-9-]+\\.[a-zA-Z0-9\\.-]+\$/si", $email)) {
            return "<a $href = \"mailto:" . $email . "\">" . $name . "</a>";
        }
        return $email;
    }
    public function function_135($size, $text)
    {
        $size = intval($size) + 10;
        if (50 < $size) {
            $size = 50;
        }
        $text = "<div $style = \"font-size: " . $size . "pt; display: inline;\">" . str_replace("\\'", "'", $text) . "</div>";
        return $text;
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
        var_236("../index.php");
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
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function validatePerPage($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $var_240 = ceil($numresults / $perpage);
    if ($var_240 == 0) {
        $var_240 = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($var_240 < $page) {
            $page = $var_240;
        }
    }
}
function calculatePagination($pagenumber, $perpage, $total)
{
    $var_241 = $perpage * ($pagenumber - 1);
    $var_89 = $var_241 + $perpage;
    if ($total < $var_89) {
        $var_89 = $total;
    }
    $var_241++;
    return ["first" => number_format($var_241), "last" => number_format($var_89)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $var_242 = @ceil($results / $perpage);
    } else {
        $var_242 = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $var_243 = ($pagenumber - 1) * $perpage;
    $var_244 = $pagenumber * $perpage;
    if ($results < $var_244) {
        $var_244 = $results;
        if ($results < $var_243) {
            $var_243 = $results - $perpage - 1;
        }
    }
    if ($var_243 < 0) {
        $var_243 = 0;
    }
    $var_245 = $var_246 = $var_247 = $var_248 = $var_249 = "";
    $var_250 = 0;
    if ($results <= $perpage) {
        $var_251["pagenav"] = false;
        return ["", "LIMIT " . $var_243 . ", " . $perpage];
    }
    $var_251["pagenav"] = true;
    $total = number_format($results);
    $var_251["last"] = false;
    $var_251["first"] = $var_251["last"];
    $var_251["next"] = $var_251["first"];
    $var_251["prev"] = $var_251["next"];
    if (1 < $pagenumber) {
        $var_252 = $pagenumber - 1;
        $var_253 = calculatePagination($var_252, $perpage, $results);
        $var_251["prev"] = true;
    }
    if ($pagenumber < $var_242) {
        $var_254 = $pagenumber + 1;
        $var_255 = calculatePagination($var_254, $perpage, $results);
        $var_251["next"] = true;
    }
    $var_256 = "3";
    if (!isset($var_257) || !is_array($var_257)) {
        $var_258 = "10 50 100 500 1000";
        $var_257[] = preg_split("#\\s+#s", $var_258, -1, PREG_SPLIT_NO_EMPTY);
        while ($var_250++ < $var_242) {
        }
        $var_259 = isset($var_252) && $var_252 != 1 ? "page=" . $var_252 : "";
        $var_245 = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $var_242 . "</li>\r\n\t\t\t\t\t\t" . ($var_251["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $var_260["first"] . " to " . $var_260["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($var_251["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $var_253["first"] . " to " . $var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $var_245 . "\r\n\t\t\t\t\t\t" . ($var_251["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_254 . "\" $title = \"Next Page - Show Results " . $var_255["first"] . " to " . $var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($var_251["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_242 . "\" $title = \"Last Page - Show Results " . $var_261["first"] . " to " . $var_261["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
        return [$var_245, "LIMIT " . $var_243 . ", " . $perpage];
    }
    if ($var_256 <= abs($var_250 - $pagenumber) && $var_256 != 0) {
        if ($var_250 == 1) {
            $var_260 = calculatePagination(1, $perpage, $results);
            $var_251["first"] = true;
        }
        if ($var_250 == $var_242) {
            $var_261 = calculatePagination($var_242, $perpage, $results);
            $var_251["last"] = true;
        }
        if (in_array(abs($var_250 - $pagenumber), $var_257) && $var_250 != 1 && $var_250 != $var_242) {
            $var_262 = calculatePagination($var_250, $perpage, $results);
            $var_263 = $var_250 - $pagenumber;
            if (0 < $var_263) {
                $var_263 = "+" . $var_263;
            }
            $var_245 .= "<li><a class=\"smalltext\" $href = \"" . $address . ($var_250 != 1 ? "page=" . $var_250 : "") . "\" $title = \"Show results " . $var_262["first"] . " to " . $var_262["last"] . " of " . $total . "\"><!--" . $var_263 . "-->" . $var_250 . "</a></li>";
        }
    } else {
        if ($var_250 == $pagenumber) {
            $var_264 = calculatePagination($var_250, $perpage, $results);
            $var_245 .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $var_264["first"] . " to " . $var_264["last"] . " of " . $total . "\">" . $var_250 . "</a></li>";
        } else {
            $var_262 = calculatePagination($var_250, $perpage, $results);
            $var_245 .= "<li><a $href = \"" . $address . ($var_250 != 1 ? "page=" . $var_250 : "") . "\" $title = \"Show results " . $var_262["first"] . " to " . $var_262["last"] . " of " . $total . "\">" . $var_250 . "</a></li>";
        }
    }
}
function formatTimestamp($timestamp = "")
{
    $var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($var_265, $timestamp);
}
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function function_114($message, $htmlspecialchars_uni = true, $noshoutbox = true, $xss_clean = true, $show_smilies = true, $imagerel = "posts")
{
    $options = ["use_smilies" => $show_smilies, "max_smilies" => 30, "remove_badwords" => 1, "htmlspecialchars" => $htmlspecialchars_uni, "imagerel" => $imagerel, "auto_url" => 1, "short_url" => 1, "image_preview" => 1];
    $var_366 = new Class_6();
    $var_366->function_119($message, $options);
    return $var_366->message;
}

?>