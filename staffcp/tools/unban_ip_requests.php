<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/unban_ip_requests.lang");
$Message = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'THEME'");
$Result = mysqli_fetch_assoc($query);
$THEME = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'SMTP'");
$Result = mysqli_fetch_assoc($query);
$SMTP = unserialize($Result["content"]);
if ($Act == "viewreply" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT reply FROM unbanrequests WHERE `id` = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $Res = mysqli_fetch_assoc($query);
        $Message = showAlertError(nl2br(htmlspecialchars($Res["reply"])));
    }
} else {
    if ($Act == "reply" && $Cid) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT email, comment FROM unbanrequests WHERE `id` = '" . $Cid . "'");
        if (mysqli_num_rows($query)) {
            $Res = mysqli_fetch_assoc($query);
            $reply = "----------------------------------------------------------------------------\n" . htmlspecialchars($Res["comment"]) . "\n---------------------------------------------------------------------------------\n\n";
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                $reply = trim($_POST["reply"]);
                if ($reply) {
                    $to = htmlspecialchars($Res["email"]);
                    $subject = $Language[3];
                    var_283($to, $subject, nl2br($reply));
                    $Message = showAlertError($Language[14]);
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE unbanrequests SET $reply = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $reply) . "' WHERE `id` = '" . $Cid . "'");
                    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                        $Message = str_replace(["{1}", "{2}"], [$Cid, $_SESSION["ADMIN_USERNAME"]], $Language[15]);
                        logStaffAction($Message);
                        $Message = showAlertMessage($Message);
                        $Done = true;
                    }
                } else {
                    $Message = showAlertError($Language[11]);
                }
            }
            if (!isset($Done)) {
                echo "\r\n\t\t\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=unban_ip_requests&$act = reply&$id = " . $Cid . "" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"unban_ip_requests\">\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>" . $Language[3] . " - " . $Language[10] . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[10] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $style = \"width: 99%; height: 100px;\" $name = \"reply\" class=\"bginput\" $dir = \"ltr\" $tabindex = \"1\">" . htmlspecialchars($reply) . "</textarea></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[12] . "\" /> <input $type = \"reset\" $value = \"" . $Language[13] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>\r\n\t\t\t";
            }
        }
    } else {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["id"]) && !empty($_POST["id"])) {
            foreach ($_POST["id"] as $id => $ip) {
                mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM unbanrequests WHERE `id` = '" . intval($id) . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $ips[] = $ip;
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM loginattempts WHERE $ip = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "'");
                }
            }
            if (count($ips)) {
                $Message = str_replace(["{1}", "{2}"], [implode(", ", $ips), $_SESSION["ADMIN_USERNAME"]], $Language[9]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
            }
        }
    }
}
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM unbanrequests"));
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=unban_ip_requests&amp;");
$sql = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, realip, email, added, comment, reply FROM unbanrequests ORDER BY added DESC " . $limit);
if (mysqli_num_rows($sql) == 0) {
    echo "\r\n\t\r\n\t" . showAlertError($Language[1]);
} else {
    $Found = "";
    while ($REQ = mysqli_fetch_assoc($sql)) {
        $Found .= "\r\n\t\t<tr $id = \"show_id" . $REQ["id"] . "\" $name = \"show_id" . $REQ["id"] . "\">\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=search_ip&amp;$ip = " . htmlspecialchars($REQ["realip"]) . "\">" . htmlspecialchars($REQ["realip"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=search_user&amp;$email = " . htmlspecialchars($REQ["email"]) . "\">" . htmlspecialchars($REQ["email"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($REQ["added"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($REQ["comment"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=unban_ip_requests&amp;$act = reply&amp;$id = " . $REQ["id"] . "" . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">" . trim($Language[10]) . "</a>\r\n\t\t\t\t" . ($REQ["reply"] == "" ? "" : " / <a $href = \"index.php?do=unban_ip_requests&amp;$act = viewreply&amp;$id = " . $REQ["id"] . "" . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\">" . trim($Language[16]) . "</a>") . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"id[" . $REQ["id"] . "]\" $value = \"" . htmlspecialchars($REQ["realip"]) . "\" $checkme = \"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"6\"><input $type = \"submit\" $value = \"" . $Language[8] . "\" /></td>\r\n\t\t</tr>";
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=unban_ip_requests" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"unban_ip_requests\">\r\n\t" . $Message . "\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"6\"><b>" . $Language[3] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[10] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('unban_ip_requests', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>\r\n\t</form>\r\n\t" . $pagertop;
}
class Class_5
{
    public $smtpHost = NULL;
    public $smtpPort = NULL;
    public $smtpUser = NULL;
    public $smtpPass = NULL;
    public $smtpSocket = NULL;
    public $smtpReturn = 0;
    public $secure = "";
    public $toemail = "";
    public $subject = "";
    public $message = "";
    public $headers = "";
    public $fromemail = "";
    public $delimiter = "\r\n";
    public $debug = true;
    public function __construct($SMTP)
    {
        if ($SMTP["secure_connection"] == "yes") {
            $this->$secure = "tls";
        } else {
            $this->$secure = "none";
        }
        $this->$smtpHost = $SMTP["smtpaddress"];
        $this->$smtpPort = !empty($SMTP["smtpport"]) ? intval($SMTP["smtpport"]) : 25;
        $this->$smtpUser = & $SMTP["accountname"];
        $this->$smtpPass = & $SMTP["accountpassword"];
        $this->$delimiter = "\r\n";
    }
    public function function_91($toemail, $subject, $message, $from = "", $uheaders = "", $charset = "", $webmasteremail = "", $http_host = "")
    {
        $toemail = $this->function_92($toemail);
        if (empty($toemail)) {
            return false;
        }
        $delimiter =& $this->delimiter;
        $toemail = $this->function_93($toemail);
        $subject = $this->function_92($subject);
        $message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
        if ((strtolower($charset) == "iso-8859-1" || $charset == "") && preg_match("/&[a-z0-9#]+;/i", $message)) {
            $message = utf8_encode($message);
            $subject = utf8_encode($subject);
            $var_285 = "UTF-8";
            $var_286 = true;
        } else {
            $var_285 = $charset;
            $var_286 = false;
        }
        $message = $this->function_93($message, $var_286);
        $subject = $this->function_94($this->function_93($subject, $var_286), $var_285, false, false);
        $from = $this->function_92($from);
        if (empty($from)) {
            $emailFromHeader = "PHP/" . phpversion() . " via the PHP TS SE SMTP Class";
            if ($var_286) {
                $emailFromHeader = utf8_encode($emailFromHeader);
            }
            $emailFromHeader = $this->function_94($this->function_93($emailFromHeader, $var_286), $var_285);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $emailFromHeader . " <" . $webmasteremail . ">" . $delimiter;
            $headers .= "Auto-Submitted: auto-generated" . $delimiter;
        } else {
            $emailFromHeader = $from;
            if ($var_286) {
                $emailFromHeader = utf8_encode($emailFromHeader);
            }
            $emailFromHeader = $this->function_94($this->function_93($emailFromHeader, $var_286), $var_285);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $emailFromHeader . " <" . $from . ">" . $delimiter;
            $headers .= "Sender: " . $webmasteremail . $delimiter;
        }
        $fromemail = $webmasteremail;
        $headers .= "Return-Path: " . $fromemail . $delimiter;
        if (!$http_host) {
            $http_host = substr(md5($message), 12, 18) . ".ts_unknown.unknown";
        }
        $var_288 = "<" . gmdate("YmdHis") . "." . substr(md5($message . microtime()), 0, 12) . "@" . $http_host . ">";
        $headers .= "Message-ID: " . $var_288 . $delimiter;
        $headers .= preg_replace("#(\r\n|\r|\n)#s", $delimiter, $uheaders);
        unset($uheaders);
        $headers .= "MIME-Version: 1.0" . $delimiter;
        $headers .= "Content-Type: text/html" . ($var_285 ? "; $charset = \"" . $var_285 . "\"" : "") . $delimiter;
        $headers .= "Content-Transfer-Encoding: 8bit" . $delimiter;
        $headers .= "X-Priority: 3" . $delimiter;
        $headers .= "X-Mailer: TS SE Mail via PHP" . $delimiter;
        $headers .= "Date: " . date("r") . $delimiter;
        $this->$toemail = $toemail;
        $this->$subject = $subject;
        $this->$message = $message;
        $this->$headers = $headers;
        $this->$fromemail = $fromemail;
        return true;
    }
    public function function_95($msg, $expectedResult = false)
    {
        if ($msg !== false && !empty($msg)) {
            fputs($this->smtpSocket, $msg . "\r\n");
        }
        if ($expectedResult !== false) {
            $result = "";
            while ($var_289 = @fgets($this->smtpSocket, 1024)) {
                $result .= $var_289;
                if (!preg_match("#^(\\d{3}) #", $var_289, $var_220)) {
                }
            }
            $this->$smtpReturn = intval($var_220[1]);
            return $this->$smtpReturn = = $expectedResult;
        }
        return true;
    }
    public function function_96($msg)
    {
        if ($this->debug) {
            trigger_error($msg, 512);
        }
        return false;
    }
    public function function_97()
    {
        if (!$this->smtpSocket) {
            return false;
        }
        if (!$this->function_95("EHLO " . $this->smtpHost, 250) && !$this->function_95("HELO " . $this->smtpHost, 250)) {
            return false;
        }
        return true;
    }
    public function function_98()
    {
        if (!$this->toemail) {
            return false;
        }
        $this->$smtpSocket = fsockopen(($this->$secure = = "ssl" ? "ssl://" : "tcp://") . $this->smtpHost, $this->smtpPort, fsockError, fsockErrorStr, 30);
        if ($this->smtpSocket) {
            if (!$this->function_95(false, 220)) {
                return $this->function_96($this->smtpReturn . " Unexpected response when connecting to SMTP server");
            }
            if (!$this->function_97()) {
                return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server during handshake");
            }
            if ($this->$secure = = "tls" && function_exists("stream_socket_enable_crypto")) {
                if ($this->function_95("STARTTLS", 220) && !stream_socket_enable_crypto($this->smtpSocket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return $this->function_96("Unable to negotitate TLS handshake.");
                }
                $this->function_97();
            }
            if ($this->smtpUser && $this->smtpPass && $this->function_95("AUTH LOGIN", 334) && (!$this->function_95(base64_encode($this->smtpUser), 334) || !$this->function_95(base64_encode($this->smtpPass), 235))) {
                return $this->function_96($this->smtpReturn . " Authorization to the SMTP server failed");
            }
            if (!$this->function_95("MAIL FROM:<" . $this->fromemail . ">", 250)) {
                return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server during FROM address transmission");
            }
            $var_290 = explode(",", $this->toemail);
            foreach ($var_290 as $address) {
                if (!$this->function_95("RCPT TO:<" . trim($address) . ">", 250)) {
                    return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server during TO address transmission");
                }
            }
            if ($this->function_95("DATA", 354)) {
                $this->function_95("Date: " . gmdate("r"), false);
                $this->function_95("To: " . $this->toemail, false);
                $this->function_95(trim($this->headers), false);
                $this->function_95("Subject: " . $this->subject, false);
                $this->function_95("\r\n", false);
                $this->$message = preg_replace("#^\\." . $this->delimiter . "#m", ".." . $this->delimiter, $this->message);
                $this->function_95($this->message, false);
                if (!$this->function_95(".", 250)) {
                    return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server when ending transmission");
                }
                $this->function_95("QUIT", 221);
                fclose($this->smtpSocket);
                return true;
            }
            return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server during data transmission");
        } else {
            return $this->function_96("Unable to connect to SMTP server");
        }
    }
    public function function_92($text)
    {
        $text = preg_replace("/(\r\n|\r|\n)/s", "\r\n", trim($text));
        $pos = strpos($text, "\r\n");
        if ($pos !== false) {
            return substr($text, 0, $pos);
        }
        return $text;
    }
    public function function_93($text, $doUniCode = false)
    {
        if ($doUniCode) {
            $text = preg_replace_callback("/&#([0-9]+);/siU", function ($matches) {
                return var_291($matches[1]);
            }, $text);
        }
        return str_replace(["&lt;", "&gt;", "&quot;", "&amp;"], ["<", ">", "\"", "&"], $text);
    }
    public function function_94($text, $charset = "utf-8", $force_encode = false, $quoted_string = true)
    {
        $text = trim($text);
        if (!$charset) {
            return $text;
        }
        if ($force_encode) {
            $var_292 = true;
        } else {
            $var_292 = false;
            $i = 0;
            while ($i < strlen($text)) {
                if (127 < ord($text[$i])) {
                    $var_292 = true;
                } else {
                    $i++;
                }
            }
        }
        if ($var_292) {
            $var_293 = preg_replace_callback("#([^a-zA-Z0-9!*+\\-/ ])#", function ($matches) {
                return "'=" . strtoupper(dechex(ord(str_replace("\\\\\"", "\\\"", $matches[1]))));
            }, $text);
            $var_293 = str_replace(" ", "_", $var_293);
            $var_293 = "=?" . $charset . "?q?" . $var_293 . "?=";
            return $var_293;
        }
        if ($quoted_string) {
            $text = str_replace(["\"", "(", ")"], ["\\\"", "\\(", "\\)"], $text);
            return "\"" . $text . "\"";
        }
        return preg_replace("#(\\r\\n|\\n|\\r)+#", " ", $text);
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
function function_100($to, $subject, $body)
{
    global $MAIN;
    global $SMTP;
    global $THEME;
    $var_295 = $MAIN["SITENAME"];
    $fromemail = $MAIN["SITEEMAIL"];
    $var_296 = false;
    if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
        $emailLineDelimiter = "\r\n";
        $var_296 = true;
    } else {
        if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
            $emailLineDelimiter = "\r";
        } else {
            $emailLineDelimiter = "\n";
        }
    }
    $var_298 = md5(uniqid(rand(), true) . time());
    $var_41 = $_SERVER["SERVER_NAME"];
    $headers = "From: " . $var_295 . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Reply-To: " . $var_295 . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Return-Path: " . $var_295 . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Message-ID: <" . $var_298 . " thesystem@" . $var_41 . ">" . $emailLineDelimiter;
    $headers .= "X-Mailer: PHP v" . phpversion() . $emailLineDelimiter;
    $headers .= "MIME-Version: 1.0" . $emailLineDelimiter;
    $headers .= "Content-Transfer-Encoding: 8bit" . $emailLineDelimiter;
    $headers .= "Content-type: text/html; $charset = " . $THEME["charset"] . $emailLineDelimiter;
    $headers .= "X-Sender: PHP" . $emailLineDelimiter;
    if ($SMTP["smtptype"] == "default") {
        return mail($to, $subject, $body, $headers);
    }
    if ($SMTP["smtptype"] == "advanced") {
        if (isset($SMTP["smtp"]) && $SMTP["smtp"] == "yes") {
            ini_set("SMTP", $SMTP["smtp_host"]);
            ini_set("smtp_port", $SMTP["smtp_port"]);
            if ($var_296) {
                ini_set("sendmail_from", $SMTP["smtp_from"]);
            }
        }
        $var_299 = mail($to, $subject, $body, $headers);
        if (isset($SMTP["smtp"]) && $SMTP["smtp"] == "yes") {
            ini_restore("SMTP");
            ini_restore("smtp_port");
            if ($var_296) {
                ini_restore("sendmail_from");
            }
        }
        return $var_299;
    }
    $var_300 = new Class_5($SMTP);
    $var_300->function_91($to, trim($subject), trim($body), $fromemail, "", $THEME["charset"], $fromemail, $MAIN["BASEURL"]);
    $var_301 = $var_300->function_98();
    return $var_301;
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
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
    $totalPages = ceil($numresults / $perpage);
    if ($totalPages == 0) {
        $totalPages = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($totalPages < $page) {
            $page = $totalPages;
        }
    }
}
function calculatePagination($pagenumber, $perpage, $total)
{
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    $paginationFirstItem++;
    return ["first" => number_format($paginationFirstItem), "last" => number_format($paginationLastItem)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $queryResult = @ceil($results / $perpage);
    } else {
        $queryResult = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $limitOffset = ($pagenumber - 1) * $perpage;
    $var_244 = $pagenumber * $perpage;
    if ($results < $var_244) {
        $var_244 = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    $paginationLinks = $var_246 = $var_247 = $var_248 = $var_249 = "";
    $currentPage = 0;
    if ($results <= $perpage) {
        $paginationHtml["pagenav"] = false;
        return ["", "LIMIT " . $limitOffset . ", " . $perpage];
    }
    $paginationHtml["pagenav"] = true;
    $total = number_format($results);
    $paginationHtml["last"] = false;
    $paginationHtml["first"] = $paginationHtml["last"];
    $paginationHtml["next"] = $paginationHtml["first"];
    $paginationHtml["prev"] = $paginationHtml["next"];
    if (1 < $pagenumber) {
        $previousPage = $pagenumber - 1;
        $var_253 = calculatePagination($previousPage, $perpage, $results);
        $paginationHtml["prev"] = true;
    }
    if ($pagenumber < $queryResult) {
        $var_254 = $pagenumber + 1;
        $var_255 = calculatePagination($var_254, $perpage, $results);
        $paginationHtml["next"] = true;
    }
    $var_256 = "3";
    if (!isset($paginationSkipLinksArray) || !is_array($paginationSkipLinksArray)) {
        $var_258 = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $var_258, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $var_259 = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $var_253["first"] . " to " . $var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $var_254 . "\" $title = \"Next Page - Show Results " . $var_255["first"] . " to " . $var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($var_256 <= abs($currentPage - $pagenumber) && $var_256 != 0) {
        if ($currentPage == 1) {
            $firstPageInfo = calculatePagination(1, $perpage, $results);
            $paginationHtml["first"] = true;
        }
        if ($currentPage == $queryResult) {
            $lastPageInfo = calculatePagination($queryResult, $perpage, $results);
            $paginationHtml["last"] = true;
        }
        if (in_array(abs($currentPage - $pagenumber), $paginationSkipLinksArray) && $currentPage != 1 && $currentPage != $queryResult) {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $pageOffsetDisplay = $currentPage - $pagenumber;
            if (0 < $pageOffsetDisplay) {
                $pageOffsetDisplay = "+" . $pageOffsetDisplay;
            }
            $paginationLinks .= "<li><a class=\"smalltext\" $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        }
    }
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>