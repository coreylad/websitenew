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
function prepareMagnetLink($Torrent)
{
    global $xbt_active;
    global $xbt_announce_url;
    global $CURUSER;
    global $announce_urls;
    global $torrent_dir;
    $AnnounceURL = trim($xbt_active == "yes" ? $xbt_announce_url . "/" . $CURUSER["torrent_pass"] . "/announce" : $announce_urls[0] . "?passkey=" . $CURUSER["torrent_pass"]);
    $magnetLink = "magnet:?xt=urn:btih:" . bin2hex($Torrent["info_hash"]) . "&dn=" . urlencode($Torrent["name"]) . "&xl=" . (0 + $Torrent["size"]);
    if ($Torrent["ts_external"] == "yes") {
        require_once INC_PATH . "/class_torrent.php";
        $TorrentFile = $torrent_dir . "/" . $Torrent["id"] . ".torrent";
        if (!is_file($TorrentFile) || !($Data = @file_get_contents($TorrentFile))) {
            exit($TorrentFile);
        }
        $Torrent = new Torrent();
        $Torrent->load($Data);
        if ($Torrent->error) {
            exit("error");
        }
        if (strlen($Torrent->getPieces()) % 20 != 0) {
            return "";
        }
        foreach ($Torrent->getTrackers() as $URL) {
            $magnetLink .= "&tr=" . urlencode($URL);
        }
    } else {
        $magnetLink .= "&tr=" . urlencode($AnnounceURL);
    }
    return $magnetLink;
}
function jsonHeaders($Output = "", $contentType = "text/plain")
{
    global $charset;
    if (is_array($Output)) {
        foreach ($Output as $Var => $Val) {
            if (!mb_check_encoding($Val, "UTF-8")) {
                $Output[$Var] = utf8_encode($Val);
            }
        }
    } else {
        $Output = mb_check_encoding($Output, "UTF-8") ? $Output : utf8_encode($Output);
    }
    $Output = json_encode($Output);
    ob_start();
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("X-Powered-By: TSSE " . O_SCRIPT_VERSION);
    header("Content-Length: " . strlen($Output));
    header("Content-type: " . $contentType . "; charset=" . $charset);
    header("X-UA-Compatible: IE=edge,chrome=1");
    exit($Output);
}
function elastic($textareaName = "message")
{
    global $BASEURL;
    return "\r\n\t<script type=\"text/javascript\" src=\"" . $BASEURL . "/scripts/jquery.elastic.js\"></script>\r\n\t<script type=\"text/javascript\">\r\n\t\tjQuery(document).ready(function()\r\n\t\t{\r\n\t\t\tjQuery('textarea[name=\"" . $textareaName . "\"]').elastic();\r\n\t\t});\r\n\t</script>";
}
function format_comment($message, $htmlspecialchars_uni = true, $noshoutbox = true, $xss_clean = true, $show_smilies = true, $imagerel = "posts")
{
    $options = ["use_smilies" => $show_smilies, "max_smilies" => 30, "remove_badwords" => 1, "htmlspecialchars" => $htmlspecialchars_uni, "imagerel" => $imagerel, "auto_url" => 1, "short_url" => 1, "image_preview" => 1];
    require_once INC_PATH . "/class_ts_parser.php";
    $TSParser = new TSParser();
    $TSParser->parse_message($message, $options);
    return $TSParser->message;
}
function fixAjaxText($text = "")
{
    return trim($text);
}
function showPreview($postvalue = "")
{
    global $lang;
    global $dateformat;
    global $timeformat;
    $message = TS_Global($postvalue);
    if ($message && isset($_POST["previewpost"])) {
        return "\r\n\t\t<table width=\"100%\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t<span style=\"float: right;\">\r\n\t\t\t\t\t\t" . my_datee($dateformat . " - " . $timeformat, TIMENOW) . "\r\n\t\t\t\t\t</span>\r\n\t\t\t\t\t" . $lang->global["buttonpreview"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\t\t\t\t\r\n\t\t\t\t<td>" . format_comment($message) . "</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t<br />";
    }
}
function update_loadavg()
{
    global $TSSECache;
    if (function_exists("exec") && ($stats = @exec("uptime 2>&1")) && trim($stats) != "" && preg_match("#: ([\\d.,]+),?\\s+([\\d.,]+),?\\s+([\\d.,]+)\$#", $stats, $regs)) {
        $loadavg = $regs[2];
    } else {
        if (@file_exists("/proc/loadavg") && ($filestuff = @file_get_contents("/proc/loadavg"))) {
            $loadavg = explode(" ", $filestuff);
            $loadavg = $loadavg[1];
        } else {
            $loadavg = 0;
        }
    }
    $loadavg = str_replace(",", "", $loadavg);
    $TSSECache->UpdateCache("loadavg", $loadavg);
    unset($loadavg);
}
function TS_Match($string, $find)
{
    return strpos($string, $find) === false ? false : true;
}
function TS_Global($name = "")
{
    return isset($_GET[(string) $name]) ? !is_array($_GET[(string) $name]) ? trim($_GET[(string) $name]) : $_GET[(string) $name] : (isset($_POST[(string) $name]) ? !is_array($_POST[(string) $name]) ? trim($_POST[(string) $name]) : $_POST[(string) $name] : "");
}
function fix_url($url)
{
    $url = htmlspecialchars($url);
    return str_replace(["&amp;", " "], ["&", "&nbsp;"], $url);
}
function htmlspecialchars_uni($text, $entities = true)
{
    return str_replace(["<", ">", "\"", "'"], ["&lt;", "&gt;", "&quot;", "&#039;"], preg_replace("/&(?!" . ($entities ? "#[0-9]+|shy" : "(#[0-9]+|[a-z]+)") . ";)/si", "&amp;", $text));
}
function ts_remove_badwords($check)
{
    global $badwords;
    if (empty($badwords)) {
        return $check;
    }
    if (($barray = @explode(",", $badwords)) && count($barray)) {
        foreach ($barray as $b) {
            $check = @str_ireplace($b, $b[0] . @str_repeat("*", @strlen($b) - 2) . $b[@strlen($b) - 1], $check);
        }
        unset($barray);
    }
    return $check;
}
function check_email($email)
{
    return preg_match("#^[a-z0-9.!\\#\$%&'*+-/=?^_`{|}~]+@([0-9.]+|([^\\s'\"<>@,;]+\\.+[a-z]{2,6}))\$#si", $email);
}
function highlight($search, $subject, $hlstart = "<b><font color='#f7071d'>", $hlend = "</font></b>")
{
    $srchlen = strlen($search);
    if ($srchlen == 0) {
        return $subject;
    }
    $find = $subject;
    while ($find = stristr($find, $search)) {
        $srchtxt = substr($find, 0, $srchlen);
        $find = substr($find, $srchlen);
        $subject = str_replace($srchtxt, $hlstart . $srchtxt . $hlend, $subject);
    }
    return $subject;
}
function TS_MTStoUTS($datetime = "")
{
    if (empty($datetime)) {
        return "";
    }
    $Parts = explode(" ", $datetime);
    $Datebits = explode("-", $Parts[0]);
    if (isset($Parts[1])) {
        $Timebits = explode(":", $Parts[1]);
        return mktime($Timebits[0], $Timebits[1], $Timebits[2], $Datebits[1], $Datebits[2], $Datebits[0]);
    }
    return mktime(0, 0, 0, $Datebits[1], $Datebits[2], $Datebits[0]);
}
function build_breadcrumb()
{
    global $nav;
    global $navbits;
    global $BASEURL;
    global $pic_base_url;
    $navsep = " / ";
    if (isset($navbits) && is_array($navbits)) {
        @reset($navbits);
        foreach ($navbits as $key => $navbit) {
            if (isset($navbits[$key + 1])) {
                $nav .= "<a href=\"" . $navbit["url"] . "\">" . $navbit["name"] . "</a>" . (isset($navbits[$key + 2]) ? $navsep : "");
            }
        }
    }
    $navsize = isset($navbits) && is_array($navbits) ? count($navbits) : 0;
    $navbit = $navbits[$navsize - 1];
    $activesep = $nav ? " / " : "";
    echo "\r\n\t<div class=\"navbits\">\r\n\t\t<div id=\"shadetabs\">\r\n\t\t\t<img src=\"" . $pic_base_url . "tree_ltr.gif\" border=\"0\" class=\"inlineimg\" alt=\"\" /> " . $nav . $activesep . $navbit["name"] . "\r\n\t\t</div>\r\n\t</div>\r\n\t";
}
function add_breadcrumb($name, $url = "")
{
    global $navbits;
    $navsize = isset($navbits) && is_array($navbits) ? count($navbits) : 0;
    $navbits[$navsize]["name"] = $name;
    $navbits[$navsize]["url"] = $url;
}
function show_notice($notice = "", $iserror = false, $title = "", $BR = "<br />")
{
    global $BASEURL;
    global $lang;
    $defaulttemplate = ts_template();
    $imagepath = $BASEURL . "/include/templates/" . $defaulttemplate . "/images/";
    $lastword = $iserror ? "e" : "n";
    $uniqeid = md5(TIMENOW);
    return "\r\n\t<script type=\"text/javascript\">\r\n\t\tfunction ts_show_tag(id, status)\r\n\t\t{\r\n\t\t\tif (TSGetID(id)){if (status == true || status == false){TSGetID(id).style.display = (status == true)?\"none\":\"\";}\r\n\t\t\telse{TSGetID(id).style.display = (TSGetID(id).style.display == \"\")?\"none\":\"\";}}\r\n\t\t}\r\n\t</script>\r\n\t<div class=\"notification-border-" . $lastword . "\" id=\"notification_" . $uniqeid . "\" align=\"center\">\r\n\t\t<table class=\"notification-th-" . $lastword . "\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\r\n\t\t\t<tbody>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td align=\"left\" width=\"100%\" class=\"none\">\r\n\t\t\t\t\t&nbsp;<span class=\"notification-title-" . $lastword . "\">" . ($title ? $title : $lang->global["sys_message"]) . "</span>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"none\"><img src=\"" . $imagepath . "notification_close.gif\" alt=\"\" onclick=\"ts_show_tag('notification_" . $uniqeid . "', true);\" class=\"hand\" border=\"0\" height=\"13\" width=\"13\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t<div class=\"notification-body\">\r\n\t\t\t" . $notice . "\r\n\t\t</div>\r\n\t</div>\r\n\t" . $BR;
}
function sql_query($_run_query)
{
    global $usergroups;
    global $TSDatabase;
    if (!defined("DEBUGMODE")) {
        $query_start = array_sum(explode(" ", microtime()));
    }
    ($__return = mysqli_query($GLOBALS["DatabaseConnect"], $_run_query)) || write_log("MySQL Error: " . mysqli_error($GLOBALS["DatabaseConnect"]) . " {Error No: " . mysqli_errno($GLOBALS["DatabaseConnect"]) . " - File: " . $_SERVER["SCRIPT_NAME"] . "}.. The query was: " . htmlspecialchars($_run_query));
    if (!defined("DEBUGMODE")) {
        $query_end = round(array_sum(explode(" ", microtime())) - $query_start, 4);
        if (!isset($GLOBALS["queries"])) {
            $GLOBALS["queries"] = "";
        }
        if (isset($GLOBALS["totalqueries"])) {
            $GLOBALS["totalqueries"]++;
        } else {
            $GLOBALS["totalqueries"] = 1;
        }
        $GLOBALS["queries"] .= "<input type=\"hidden\" name=\"queries[]\" value=\"" . base64_encode(substr($query_end, 0, 8) . "," . base64_encode(trim($_run_query))) . "\" />";
    }
    return $__return;
}
function TSRowCount($C, $T, $E = "")
{
    ($Q = sql_query("SELECT COUNT(" . $C . ") FROM " . $T . ($E ? " WHERE " . $E : ""))) || sqlerr(__FILE__, 374);
    $R = mysqli_fetch_row($Q);
    return $R[0];
}
function write_log($Text)
{
    sql_query("INSERT INTO sitelog VALUES (NULL, NOW(), " . sqlesc($Text) . ")");
}
function KPS($Type = "+", $Points = "1.0", $ID = "")
{
    if ($ID == 0) {
        return NULL;
    }
    global $bonus;
    global $TSSEConfig;
    if (!isset($bonus)) {
        $TSSEConfig->TSLoadConfig("KPS");
        $bonus = $GLOBALS["bonus"];
    }
    if ($bonus == "enable" || $bonus == "disablesave") {
        $ID = str_replace("'", "", $ID);
        sql_query("UPDATE users SET seedbonus = seedbonus " . $Type . " '" . $Points . "' WHERE id = '" . $ID . "'");
    }
}
function sent_mail($to = "", $subject = "", $body = "", $type = "confirmation", $showmsg = true, $multiple = false, $multiplemail = "")
{
    global $rootpath;
    global $SITENAME;
    global $SITEEMAIL;
    global $charset;
    global $lang;
    global $TSSEConfig;
    global $BASEURL;
    $TSSEConfig->TSLoadConfig("SMTP");
    $fromname = $SITENAME;
    $fromemail = $SITEEMAIL;
    $windows = false;
    if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
        $eol = "\r\n";
        $windows = true;
    } else {
        if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
            $eol = "\r";
        } else {
            $eol = "\n";
        }
    }
    $options = ["use_smilies" => 0, "max_smilies" => 30, "remove_badwords" => 0, "htmlspecialchars" => 1, "imagerel" => "", "auto_url" => 1, "short_url" => 0, "image_preview" => 0];
    if (!defined("ANONYMIZER_DISABLED")) {
        define("ANONYMIZER_DISABLED", true);
    }
    require_once INC_PATH . "/class_ts_parser.php";
    $TSParser = new TSParser();
    $TSParser->parse_message($body, $options);
    $body = $TSParser->message;
    $mid = md5(uniqid(rand(), true) . TIMENOW);
    $name = $_SERVER["SERVER_NAME"];
    $headers = "From: " . $fromname . " <" . $fromemail . ">" . $eol;
    $headers .= "Reply-To: " . (!defined("REPLY_TO") ? $fromname . " <" . $fromemail . ">" : REPLY_TO) . $eol;
    $headers .= "Return-Path: " . (!defined("REPLY_TO") ? $fromname . " <" . $fromemail . ">" : REPLY_TO) . $eol;
    $headers .= "Message-ID: <" . $mid . " thesystem@" . $name . ">" . $eol;
    $headers .= "X-Mailer: PHP v" . phpversion() . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;
    $headers .= "Content-Transfer-Encoding: 8bit" . $eol;
    $headers .= "Content-type: text/html; charset=" . $charset . $eol;
    $headers .= "X-Sender: PHP" . $eol;
    if ($multiple) {
        $headers .= "Bcc: " . $multiplemail . "." . $eol;
    }
    if ($GLOBALS["smtptype"] == "default") {
        $mail = mail($to, $subject, $body, $headers);
        if (!$mail && $showmsg) {
            stderr($lang->global["error"], $lang->global["mailerror"]);
        }
    } else {
        if ($GLOBALS["smtptype"] == "advanced") {
            if (isset($GLOBALS["smtp"]) && $GLOBALS["smtp"] == "yes") {
                ini_set("SMTP", $GLOBALS["smtp_host"]);
                ini_set("smtp_port", $GLOBALS["smtp_port"]);
                if ($windows) {
                    ini_set("sendmail_from", $GLOBALS["smtp_from"]);
                }
            }
            $mail = mail($to, $subject, $body, $headers);
            if (!$mail && $showmsg) {
                stderr($lang->global["error"], $lang->global["mailerror"]);
            }
            if (isset($GLOBALS["smtp"]) && $GLOBALS["smtp"] == "yes") {
                ini_restore(SMTP);
                ini_restore(smtp_port);
                if ($windows) {
                    ini_restore("sendmail_from");
                }
            }
        } else {
            if ($GLOBALS["smtptype"] == "external") {
                $SMTP = [];
                $SMTP["secure_connection"] = $GLOBALS["secure_connection"];
                $SMTP["smtpaddress"] = $GLOBALS["smtpaddress"];
                $SMTP["smtpport"] = $GLOBALS["smtpport"];
                $SMTP["accountname"] = $GLOBALS["accountname"];
                $SMTP["accountpassword"] = $GLOBALS["accountpassword"];
                require_once INC_PATH . "/class_ts_smtp.php";
                $TSMAIL = new TS_SMTP($SMTP);
                $TSMAIL->start($to, trim($subject), trim($body), $fromemail, "", $charset, $fromemail, $BASEURL);
                $Status = $TSMAIL->send();
            }
        }
    }
    if ($showmsg) {
        if ($type == "confirmation") {
            stderr($lang->global["success"], sprintf($lang->global["mailsent"], htmlspecialchars_uni($to)), false);
        } else {
            if ($type == "details") {
                stderr($lang->global["success"], sprintf($lang->global["mailsent2"], htmlspecialchars_uni($to)), false);
            }
        }
    } else {
        return true;
    }
}
function TSBoot($IPADDRESS = "")
{
    global $BASEURL;
    global $rootpath;
    global $SITENAME;
    global $iplog1;
    global $lang;
    global $cachetime;
    global $cache;
    global $where;
    global $UseMemcached;
    global $querystring;
    global $page;
    global $securehash;
    if (empty($_COOKIE["c_secure_pass"]) || empty($_COOKIE["c_secure_uid"]) || strlen($_COOKIE["c_secure_pass"]) != 32) {
        return NULL;
    }
    if (!($id = intval($_COOKIE["c_secure_uid"]))) {
        return NULL;
    }
    ($res = sql_query("SELECT HIGH_PRIORITY * FROM users WHERE id = " . sqlesc($id))) || sqlerr(__FILE__, 498);
    if (!mysqli_num_rows($res)) {
        return NULL;
    }
    $row = mysqli_fetch_assoc($res);
    if ($_COOKIE["c_secure_pass"] != securehash($row["passhash"])) {
        return NULL;
    }
    if ($iplog1 == "yes" && $IPADDRESS != $row["ip"] && !empty($IPADDRESS)) {
        sql_query("REPLACE INTO iplog VALUES (" . sqlesc($IPADDRESS) . ", '" . $id . "')") || sqlerr(__FILE__, 506);
    }
    if ($IPADDRESS != $row["ip"]) {
        $updateuser[] = "ip = " . sqlesc($IPADDRESS);
    }
    if (strlen($row["torrent_pass"]) != 32) {
        $torrent_pass = md5($row["username"] . TIMENOW . $row["passhash"]);
        $updateuser[] = "torrent_pass = '" . $torrent_pass . "'";
    }
    if ($where == "yes" && $page != $row["page"] && !defined("SKIP_LOCATION_SAVE") && !ts_match("ts_error", $page)) {
        $updateuser[] = "page = " . sqlesc($page . $querystring);
    }
    if (900 < TIMENOW - @ts_mtstouts($row["last_login"])) {
        $updateuser[] = "last_login = '" . $row["last_access"] . "'";
    }
    $updateuser[] = "last_access = NOW()";
    if (defined("IN_FORUMS")) {
        if (900 < TIMENOW - $row["last_forum_active"]) {
            $updateuser[] = "last_forum_visit='" . $row["last_forum_active"] . "'";
        }
        $updateuser[] = "last_forum_active='" . TIMENOW . "'";
    }
    $row["securitytoken_raw"] = sha1($row["id"] . sha1($row["secret"]) . sha1($securehash));
    $row["securitytoken"] = TIMENOW . "-" . sha1(TIMENOW . $row["securitytoken_raw"]);
    if (0 < count($updateuser)) {
        sql_query("UPDATE LOW_PRIORITY users SET " . implode(",", $updateuser) . " WHERE id = '" . $id . "'") || sqlerr(__FILE__, 533);
    }
    $GLOBALS["CURUSER"] = $row;
    if ($UseMemcached) {
        global $TSMemcache;
        if (!($row2 = $TSMemcache->check("usergroup_" . $row["usergroup"]))) {
            ($Query = sql_query("SELECT HIGH_PRIORITY * FROM usergroups WHERE gid = '" . $row["usergroup"] . "'")) || sqlerr(__FILE__, 545);
            $row2 = mysqli_fetch_assoc($Query);
            $TSMemcache->add("usergroup_" . $row["usergroup"], $row2);
        }
    } else {
        ($Query = sql_query("SELECT HIGH_PRIORITY * FROM usergroups WHERE gid = '" . $row["usergroup"] . "'")) || sqlerr(__FILE__, 552);
        $row2 = mysqli_fetch_assoc($Query);
    }
    $GLOBALS["usergroups"] = $row2;
    if ($row2["isbanned"] != "no" || $row["enabled"] != "yes" || $row["status"] != "confirmed") {
        print_no_permission(false, true, $row["notifs"]);
        exit;
    }
    unset($row);
    unset($row2);
    $GLOBALS["ts_cron_image"] = !defined("SKIP_CRON_JOBS") ? true : false;
}
function TSDetectUserIP()
{
    $serverVars = ["HTTP_CLIENT_IP", "HTTP_X_FORWARDED_FOR", "HTTP_X_FORWARDED", "HTTP_X_CLUSTER_CLIENT_IP", "HTTP_FORWARDED_FOR", "HTTP_FORWARDED", "HTTP_X_SUCURI_CLIENTIP", "REMOTE_ADDR"];
    foreach ($serverVars as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(",", $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 || FILTER_FLAG_IPV6 || FILTER_FLAG_NO_PRIV_RANGE || FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return htmlspecialchars_uni($ip);
                }
            }
        }
    }
}
function mksize($bytes = 0)
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
function sqlesc($value)
{
    global $TSDatabase;
    if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "'";
}
function ts_template()
{
    global $CURUSER;
    global $TSSECache;
    global $trackerdefaulttemplate;
    if (isset($CURUSER["stylesheet"]) && !empty($CURUSER["stylesheet"]) && isset($TSSECache->Cache["ts_themes"]["content"]) && !empty($TSSECache->Cache["ts_themes"]["content"]) && in_array($CURUSER["stylesheet"], explode(",", $TSSECache->Cache["ts_themes"]["content"]), true)) {
        $GLOBALS["defaulttemplate"] = $CURUSER["stylesheet"];
        return $CURUSER["stylesheet"];
    }
    return $trackerdefaulttemplate;
}
function mksecret($length = 20, $UseNumbers = true)
{
    if ($UseNumbers) {
        $set = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    } else {
        $set = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z"];
    }
    $str = "";
    for ($i = 1; $i <= $length; $i++) {
        $ch = rand(0, count($set) - 1);
        $str .= $set[$ch];
    }
    return $str;
}
function securehash($var = "")
{
    global $securehash;
    return md5($var . USERIPADDRESS . $securehash);
}
function warn_donor($s, $warnday = 3)
{
    if ($s < 0) {
        $s = 0;
    }
    $t = [];
    foreach (["60:sec", "60:min", "24:hour", "0:day"] as $x) {
        $y = explode(":", $x);
        if (1 < $y[0]) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        } else {
            $v = $s;
        }
        $t[$y[1]] = $v;
    }
    return $t["day"] < $warnday ? true : false;
}
function cutename($name, $max = 35, $html = true)
{
    return $html ? htmlspecialchars_uni($max < strlen($name) ? mb_substr($name, 0, $max, "UTF-8") . "..." : $name) : ($max < strlen($name) ? mb_substr($name, 0, $max, "UTF-8") . "..." : $name);
}
function get_extension($file = "")
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function ts_nf($number = 0)
{
    return number_format($number, 0, ".", ",");
}
function ts_collapse($id, $type = 1, $element = "tbody")
{
    global $BASEURL;
    global $tscollapse;
    $defaulttemplate = ts_template();
    if ($type === 1) {
        return "<a style=\"float: right;\" href=\"javascript: void(0);\" onclick=\"return toggle_collapse('" . $id . "')\"><img id=\"collapseimg_" . $id . "\" src=\"" . $BASEURL . "/include/templates/" . $defaulttemplate . "/images/collapse_tcat" . (isset($tscollapse["collapseimg_" . $id . ""]) ? $tscollapse["collapseimg_" . $id . ""] : "") . ".png\" alt=\"\" border=\"0\" /></a>";
    }
    if ($type === 2) {
        return "<" . $element . " id=\"collapseobj_" . $id . "\" style=\"" . (isset($tscollapse["collapseobj_" . $id]) ? $tscollapse["collapseobj_" . $id] : "none") . "\">";
    }
}
function is_mod($user = [])
{
    return isset($user["cansettingspanel"]) && $user["cansettingspanel"] === "yes" || isset($user["issupermod"]) && $user["issupermod"] === "yes" || isset($user["canstaffpanel"]) && $user["canstaffpanel"] === "yes" ? true : false;
}
function pager($perpage, $results, $address = "")
{
    global $lang;
    global $BASEURL;
    if ($results < $perpage) {
        return ["", "", ""];
    }
    if ($results) {
        $totalpages = @ceil($results / $perpage);
    } else {
        $totalpages = 0;
    }
    if (isset($_GET["showlast"]) && $_GET["showlast"] == "true") {
        $pagenumber = $totalpages;
    } else {
        $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    }
    sanitize_pageresults($results, $pagenumber, $perpage, 200);
    $limitlower = ($pagenumber - 1) * $perpage;
    $limitupper = $pagenumber * $perpage;
    if ($results < $limitupper) {
        $limitupper = $results;
        if ($results < $limitlower) {
            $limitlower = $results - $perpage - 1;
        }
    }
    if ($limitlower < 0) {
        $limitlower = 0;
    }
    $pagenav = $firstlink = $prevlink = $lastlink = $nextlink = "";
    $curpage = 0;
    if ($results <= $perpage) {
        $show["pagenav"] = false;
        return ["", "", "LIMIT " . $limitlower . ", " . $perpage];
    }
    $show["pagenav"] = true;
    $total = ts_nf($results);
    $show["last"] = false;
    $show["first"] = $show["last"];
    $show["next"] = $show["first"];
    $show["prev"] = $show["next"];
    if (1 < $pagenumber) {
        $prevpage = $pagenumber - 1;
        $prevnumbers = fetch_start_end_total_array($prevpage, $perpage, $results);
        $show["prev"] = true;
    }
    if ($pagenumber < $totalpages) {
        $nextpage = $pagenumber + 1;
        $nextnumbers = fetch_start_end_total_array($nextpage, $perpage, $results);
        $show["next"] = true;
    }
    $pagenavpages = "3";
    if (!isset($pagenavsarr) || !is_array($pagenavsarr)) {
        $pagenavs = "10 50 100 500 1000";
        $pagenavsarr[] = preg_split("#\\s+#s", $pagenavs, -1, PREG_SPLIT_NO_EMPTY);
        while ($curpage++ < $totalpages) {
        }
        $prp = isset($prevpage) && $prevpage != 1 ? "page=" . $prevpage . "scrollto=tspager" : "scrollto=tspager";
        $pagenav = "\r\n\t<a id=\"tspager\" name=\"tspager\"></a>\r\n\t<table width=\"100%\" border=\"0\" class=\"none\" style=\"clear: both;\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"none\" width=\"100%\" style=\"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $totalpages . "</li>\r\n\t\t\t\t\t\t" . ($show["first"] ? "<li><a class=\"smalltext\" href=\"" . $address . "scrollto=tspager\" title=\"" . $lang->global["first_page"] . " - " . sprintf($lang->global["show_results"], $firstnumbers["first"], $firstnumbers["last"], $total) . "\">&laquo; " . $lang->global["first"] . "</a></li>" : "") . ($show["prev"] ? "<li><a class=\"smalltext\" href=\"" . $address . $prp . "\" title=\"" . $lang->global["prev_page"] . " - " . sprintf($lang->global["show_results"], $prevnumbers["first"], $prevnumbers["last"], $total) . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $pagenav . "\r\n\t\t\t\t\t\t" . ($show["next"] ? "<li><a class=\"smalltext\" href=\"" . $address . "page=" . $nextpage . "&scrollto=tspager\" title=\"" . $lang->global["next_page"] . " - " . sprintf($lang->global["show_results"], $nextnumbers["first"], $nextnumbers["last"], $total) . "\">&gt;</a></li>" : "") . ($show["last"] ? "<li><a class=\"smalltext\" href=\"" . $address . "page=" . $totalpages . "&scrollto=tspager\" title=\"" . $lang->global["last_page"] . " - " . sprintf($lang->global["show_results"], $lastnumbers["first"], $lastnumbers["last"], $total) . "\">" . $lang->global["last"] . " <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t\t<li><a href=\"javascript:void(0);\" id=\"quicknavpage\">" . $lang->global["buttongo"] . "</a></li>\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t<script type=\"text/javascript\">\r\n\t\tmenu_register(\"quicknavpage\", true);\r\n\t</script>\r\n\t<div id=\"quicknavpage_menu\" class=\"menu_popup\" style=\"display:none;\">\r\n\t<form action=\"" . $address . "\" method=\"get\" onsubmit=\"return TSGoToPage('" . $address . "', '')\">\r\n\t\t<table border=\"0\" cellpadding=\"2\" cellspacing=\"1\">\r\n\t\t\t<tbody>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\" nowrap=\"nowrap\">" . $lang->global["gotopage"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\" title=\"\">\r\n\t\t\t\t\t\t<input id=\"Page_Number\" style=\"font-size: 11px;\" size=\"4\" type=\"text\">\r\n\t\t\t\t\t\t<input value=\"" . $lang->global["buttongo"] . "\" type=\"button\" onclick=\"TSGoToPage('" . $address . "', '')\">\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t</form>\r\n\t</div>\r\n\t<script type=\"text/javascript\">\r\n\t\tmenu.activate(true);\r\n\t</script>\r\n\t";
        $pagenav2 = str_replace(["quicknavpage", "Page_Number"], ["quicknavpage2", "Page_Number2"], $pagenav);
        return [$pagenav, $pagenav2, "LIMIT " . $limitlower . ", " . $perpage];
    }
    if ($pagenavpages <= abs($curpage - $pagenumber) && $pagenavpages != 0) {
        if ($curpage == 1) {
            $firstnumbers = fetch_start_end_total_array(1, $perpage, $results);
            $show["first"] = true;
        }
        if ($curpage == $totalpages) {
            $lastnumbers = fetch_start_end_total_array($totalpages, $perpage, $results);
            $show["last"] = true;
        }
        if (in_array(abs($curpage - $pagenumber), $pagenavsarr) && $curpage != 1 && $curpage != $totalpages) {
            $pagenumbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $relpage = $curpage - $pagenumber;
            if (0 < $relpage) {
                $relpage = "+" . $relpage;
            }
            $pagenav .= "<li><a class=\"smalltext\" href=\"" . $address . ($curpage != 1 ? "page=" . $curpage . "&scrollto=tspager" : "scrollto=tspager") . "\" title=\"" . sprintf($lang->global["show_results"], $pagenumbers["first"], $pagenumbers["last"], $total) . "\"><!--" . $relpage . "-->" . $curpage . "</a></li>";
        }
    } else {
        if ($curpage == $pagenumber) {
            $numbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $pagenav .= "<li><a name=\"current\" class=\"current\" title=\"" . sprintf($lang->global["showing_results"], $numbers["first"], $numbers["last"], $total) . "\">" . $curpage . "</a></li>";
        } else {
            $pagenumbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $pagenav .= "<li><a href=\"" . $address . ($curpage != 1 ? "page=" . $curpage . "&scrollto=tspager" : "scrollto=tspager") . "\" title=\"" . sprintf($lang->global["show_results"], $pagenumbers["first"], $pagenumbers["last"], $total) . "\">" . $curpage . "</a></li>";
        }
    }
}
function sanitize_pageresults($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $numpages = ceil($numresults / $perpage);
    if ($numpages == 0) {
        $numpages = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($numpages < $page) {
            $page = $numpages;
        }
    }
}
function fetch_start_end_total_array($pagenumber, $perpage, $total)
{
    $first = $perpage * ($pagenumber - 1);
    $last = $first + $perpage;
    if ($total < $last) {
        $last = $total;
    }
    $first++;
    return ["first" => ts_nf($first), "last" => ts_nf($last)];
}
function get_user_color($username, $namestyle, $white = false)
{
    if ($white) {
        $new_username = "<font color=\"#ffffff\">" . $username . "</font>";
    } else {
        $new_username = str_replace("{username}", $username, $namestyle);
    }
    return $new_username;
}
function int_check($value)
{
    global $CURUSER;
    global $BASEURL;
    global $lang;
    $msg = sprintf($lang->global["invalididlogmsg"], htmlspecialchars_uni($_SERVER["REQUEST_URI"]), "<a href=\"" . $BASEURL . "/userdetails.php?id=" . $CURUSER["id"] . "\">" . $CURUSER["username"] . "</a>", USERIPADDRESS, get_date_time());
    if (is_array($value)) {
        foreach ($value as $val) {
            if (!is_valid_id($val)) {
                write_log($msg);
                print_no_permission();
            }
        }
    } else {
        if (!is_valid_id($value)) {
            write_log($msg);
            print_no_permission();
        }
    }
}
function is_valid_id($id)
{
    return is_numeric($id) && 0 < $id && floor($id) == $id;
}
function flood_check($type = "", $last = "", $shoutbox = false)
{
    global $lang;
    global $usergroups;
    if (!$usergroups["floodlimit"]) {
        return "";
    }
    $timecut = TIMENOW - $usergroups["floodlimit"];
    if (strstr($last, "-")) {
        $last = ts_mtstouts($last);
    }
    if ($timecut <= $last && $usergroups["floodlimit"] != 0) {
        $remaining_time = $usergroups["floodlimit"] - (TIMENOW - $last);
        if ($shoutbox == 0) {
            stderr($lang->global["error"], sprintf($lang->global["flooderror"], $usergroups["floodlimit"], $type, $remaining_time), false);
        } else {
            return "<font color=\"#9f040b\" size=\"2\">" . sprintf($lang->global["flooderror"], $usergroups["floodlimit"], $type, $remaining_time) . "</font>";
        }
    } else {
        return NULL;
    }
}
function print_no_permission($log = false, $stdhead = true, $extra = "", $stdfood = true)
{
    global $lang;
    global $SITENAME;
    global $BASEURL;
    global $CURUSER;
    if ($log) {
        $page = htmlspecialchars_uni($_SERVER["SCRIPT_NAME"]);
        $query = htmlspecialchars_uni($_SERVER["QUERY_STRING"]);
        $message = sprintf($lang->global["permissionlogmessage"], $page, $query, "<a href=\"" . $BASEURL . "/userdetails.php?id=" . $CURUSER["id"] . "\">" . $CURUSER["username"] . "</a>", $CURUSER["ip"]);
        write_log($message);
    }
    if ($stdhead) {
        stdhead($lang->global["nopermission"]);
        echo sprintf($lang->global["print_no_permission"], $SITENAME, $extra != "" ? "<font color=\"#9f040b\">" . $extra . "</font>" : $lang->global["print_no_permission_i"]);
        if ($stdfood) {
            stdfoot();
        }
    } else {
        echo sprintf($lang->global["print_no_permission"], $SITENAME, $extra != "" ? "<font color=\"#9f040b\">" . $extra . "</font>" : $lang->global["print_no_permission_i"]);
        if ($stdfood) {
            stdfoot();
        }
    }
    exit;
}
function my_datee($format, $stamp = "", $offset = "", $ty = 1)
{
    global $CURUSER;
    global $lang;
    global $dateformat;
    global $timezoneoffset;
    global $dstcorrection;
    if (empty($stamp)) {
        $stamp = TIMENOW;
    } else {
        if (strstr($stamp, "-")) {
            $stamp = ts_mtstouts($stamp);
        }
    }
    if (!$offset && $offset != "0") {
        if ($CURUSER && 0 < $CURUSER["id"]) {
            $offset = $CURUSER["tzoffset"];
            $dstcorr = ts_match($CURUSER["options"], "O1") ? "yes" : "no";
        } else {
            $offset = $timezoneoffset;
            $dstcorr = $dstcorrection;
        }
        if ($dstcorr == "yes") {
            $offset++;
            if (substr($offset, 0, 1) != "-") {
                $offset = "+" . $offset;
            }
        }
    }
    if ($offset == "-") {
        $offset = 0;
    }
    $date = gmdate($format, $stamp + $offset * 3600);
    if ($dateformat == $format && $ty) {
        $stamp = TIMENOW;
        $todaysdate = gmdate($format, $stamp + $offset * 3600);
        $yesterdaysdate = gmdate($format, $stamp - 86400 + $offset * 3600);
        if ($todaysdate == $date) {
            $date = $lang->global["today"];
        } else {
            if ($yesterdaysdate == $date) {
                $date = $lang->global["yesterday"];
            }
        }
    }
    return $date;
}
function get_date_time($timestamp = 0)
{
    if ($timestamp) {
        return date("Y-m-d H:i:s", $timestamp);
    }
    return date("Y-m-d H:i:s");
}
function gmtime()
{
    return ts_mtstouts(get_date_time());
}
function sqlerr($file = "", $line = "")
{
    redirect("ts_error.php?errorid=5");
    exit;
}

?>