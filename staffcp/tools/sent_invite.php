<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/sent_invite.lang");
$Message = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = \"MAIN\"");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = \"THEME\"");
$Result = mysqli_fetch_assoc($query);
$THEME = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = \"SMTP\"");
$Result = mysqli_fetch_assoc($query);
$SMTP = unserialize($Result["content"]);
$subject = "";
$invitemessage = str_replace(["{1}", "{2}"], [$MAIN["SITENAME"], $MAIN["BASEURL"]], $Language[16]);
$emails = isset($_GET["emails"]) ? trim($_GET["emails"]) : "";
$Output = "";
if ($Act == "post_invites") {
    $subject = $_POST["subject"] ? trim($_POST["subject"]) : "";
    $invitemessage = $_POST["message"] ? trim($_POST["message"]) : "";
    $emails = $_POST["emails"] ? trim($_POST["emails"]) : "";
    if ($subject && $invitemessage && $emails) {
        $SEmails = explode(",", $emails);
        if ($SEmails) {
            $Output = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">";
            foreach ($SEmails as $email) {
                $FAUIT = false;
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM users WHERE `email` = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $email) . "\"");
                if (mysqli_num_rows($query)) {
                    $FAUIT = true;
                } else {
                    $hash = substr(md5(md5(rand())), 0, 32);
                    $invitemessage = str_replace("{3}", $hash, $invitemessage);
                    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO invites (inviter, invitee, hash, time_invited) VALUES (" . intval($_SESSION["ADMIN_ID"]) . ", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $email) . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $hash) . "\", NOW())");
                    $inviteid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                }
                $Output .= "\t\t\t\t\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $Language[11] . "</td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . htmlspecialchars($email) . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\">";
                if (!$FAUIT) {
                    if (function_100($email, $subject, nl2br($invitemessage))) {
                        $Output .= "<font $color = \"green\">" . $Language[12] . "</font>";
                    } else {
                        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM invites WHERE `id` = " . $inviteid);
                        $Output .= "<font $color = \"red\">" . $Language[13] . "</font>";
                    }
                } else {
                    $Output .= "<font $color = \"darkred\">" . $Language[17] . "</font>";
                }
                $Output .= "</td>\r\n\t\t\t\t</tr>";
            }
            $Output .= "\r\n\t\t\t</table>";
            $Message = showAlertError($Language[10]) . $Output;
            logStaffAction(str_replace(["{1}", "{2}", "{3}"], [$_SESSION["ADMIN_USERNAME"], implode(",", $SEmails), $subject], $Language[14]));
            $invitemessage = str_replace(["{1}", "{2}"], [$MAIN["SITENAME"], $MAIN["BASEURL"]], $Language[16]);
        }
    } else {
        $Message = showAlertError($Language[8]);
    }
}
echo loadTinyMCEEditor() . "\r\n<script $type = \"text/javascript\">\r\n\tif (TSGetID(\"sending\") != \"\")\r\n\t{\r\n\t\tTSGetID(\"sending\").style.$display = \"none\";\r\n\t}\r\n</script>\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=sent_invite&$act = post_invites\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $style = \"width: 200px;\">" . $Language[3] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"subject\" $value = \"" . htmlspecialchars($subject) . "\" $style = \"width: 99%;\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[7] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"emails\" $value = \"" . htmlspecialchars($emails) . "\" $style = \"width: 99%;\"></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt1\"><textarea $name = \"message\" $id = \"message\" $style = \"width: 99%; height: 200px;\" $id = \"textarea1\">" . htmlspecialchars($invitemessage) . "</textarea>\r\n\t\t<p><a $href = \"javascript:toggleEditor('message');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input $type = \"submit\" $value = \"" . $Language[5] . "\" $onclick = \"this.$value = '" . trim($Language[15]) . "';\" /> <input $type = \"reset\" $value = \"" . $Language[6] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
        $toemail = $this->sanitizeEmailText($toemail);
        if (empty($toemail)) {
            return false;
        }
        $delimiter =& $this->delimiter;
        $toemail = $this->decodeHtmlEntities($toemail);
        $subject = $this->sanitizeEmailText($subject);
        $message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
        if ((strtolower($charset) == "iso-8859-1" || $charset == "") && preg_match("/&[a-z0-9#]+;/i", $message)) {
            $message = utf8_encode($message);
            $subject = utf8_encode($subject);
            $emailCharset = "UTF-8";
            $isUtf8Encoded = true;
        } else {
            $emailCharset = $charset;
            $isUtf8Encoded = false;
        }
        $message = $this->decodeHtmlEntities($message, $isUtf8Encoded);
        $subject = $this->encodeEmailHeaderRFC2047($this->decodeHtmlEntities($subject, $isUtf8Encoded), $emailCharset, false, false);
        $from = $this->sanitizeEmailText($from);
        if (empty($from)) {
            $emailFromHeader = "PHP/" . phpversion() . " via the PHP TS SE SMTP Class";
            if ($isUtf8Encoded) {
                $emailFromHeader = utf8_encode($emailFromHeader);
            }
            $emailFromHeader = $this->encodeEmailHeaderRFC2047($this->decodeHtmlEntities($emailFromHeader, $isUtf8Encoded), $emailCharset);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $emailFromHeader . " <" . $webmasteremail . ">" . $delimiter;
            $headers .= "Auto-Submitted: auto-generated" . $delimiter;
        } else {
            $emailFromHeader = $from;
            if ($isUtf8Encoded) {
                $emailFromHeader = utf8_encode($emailFromHeader);
            }
            $emailFromHeader = $this->encodeEmailHeaderRFC2047($this->decodeHtmlEntities($emailFromHeader, $isUtf8Encoded), $emailCharset);
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
        $emailMessageId = "<" . gmdate("YmdHis") . "." . substr(md5($message . microtime()), 0, 12) . "@" . $http_host . ">";
        $headers .= "Message-ID: " . $emailMessageId . $delimiter;
        $headers .= preg_replace("#(\r\n|\r|\n)#s", $delimiter, $uheaders);
        unset($uheaders);
        $headers .= "MIME-Version: 1.0" . $delimiter;
        $headers .= "Content-Type: text/html" . ($emailCharset ? "; $charset = \"" . $emailCharset . "\"" : "") . $delimiter;
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
    public function smtpSendCommand($msg, $expectedResult = false)
    {
        if ($msg !== false && !empty($msg)) {
            fputs($this->smtpSocket, $msg . "\r\n");
        }
        if ($expectedResult !== false) {
            $result = "";
            while ($smtpLine = @fgets($this->smtpSocket, 1024)) {
                $result .= $smtpLine;
                if (!preg_match("#^(\\d{3}) #", $smtpLine, $smtpMatches)) {
                }
            }
            $this->$smtpReturn = intval($smtpMatches[1]);
            return $this->$smtpReturn == $expectedResult;
        }
        return true;
    }
    public function smtpDebugError($msg)
    {
        if ($this->debug) {
            trigger_error($msg, 512);
        }
        return false;
    }
    public function smtpHandshake()
    {
        if (!$this->smtpSocket) {
            return false;
        }
        if (!$this->smtpSendCommand("EHLO " . $this->smtpHost, 250) && !$this->smtpSendCommand("HELO " . $this->smtpHost, 250)) {
            return false;
        }
        return true;
    }
    public function smtpSendEmail()
    {
        if (!$this->toemail) {
            return false;
        }
        $this->$smtpSocket = fsockopen(($this->$secure == "ssl" ? "ssl://" : "tcp://") . $this->smtpHost, $this->smtpPort, fsockError, fsockErrorStr, 30);
        if ($this->smtpSocket) {
            if (!$this->smtpSendCommand(false, 220)) {
                return $this->smtpDebugError($this->smtpReturn . " Unexpected response when connecting to SMTP server");
            }
            if (!$this->smtpHandshake()) {
                return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server during handshake");
            }
            if ($this->$secure == "tls" && function_exists("stream_socket_enable_crypto")) {
                if ($this->smtpSendCommand("STARTTLS", 220) && !stream_socket_enable_crypto($this->smtpSocket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return $this->smtpDebugError("Unable to negotitate TLS handshake.");
                }
                $this->smtpHandshake();
            }
            if ($this->smtpUser && $this->smtpPass && $this->smtpSendCommand("AUTH LOGIN", 334) && (!$this->smtpSendCommand(base64_encode($this->smtpUser), 334) || !$this->smtpSendCommand(base64_encode($this->smtpPass), 235))) {
                return $this->smtpDebugError($this->smtpReturn . " Authorization to the SMTP server failed");
            }
            if (!$this->smtpSendCommand("MAIL FROM:<" . $this->fromemail . ">", 250)) {
                return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server during FROM address transmission");
            }
            $emailRecipientsArray = explode(",", $this->toemail);
            foreach ($emailRecipientsArray as $address) {
                if (!$this->smtpSendCommand("RCPT TO:<" . trim($address) . ">", 250)) {
                    return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server during TO address transmission");
                }
            }
            if ($this->smtpSendCommand("DATA", 354)) {
                $this->smtpSendCommand("Date: " . gmdate("r"), false);
                $this->smtpSendCommand("To: " . $this->toemail, false);
                $this->smtpSendCommand(trim($this->headers), false);
                $this->smtpSendCommand("Subject: " . $this->subject, false);
                $this->smtpSendCommand("\r\n", false);
                $this->$message = preg_replace("#^\\." . $this->delimiter . "#m", ".." . $this->delimiter, $this->message);
                $this->smtpSendCommand($this->message, false);
                if (!$this->smtpSendCommand(".", 250)) {
                    return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server when ending transmission");
                }
                $this->smtpSendCommand("QUIT", 221);
                fclose($this->smtpSocket);
                return true;
            }
            return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server during data transmission");
        } else {
            return $this->smtpDebugError("Unable to connect to SMTP server");
        }
    }
    public function sanitizeEmailText($text)
    {
        $text = preg_replace("/(\r\n|\r|\n)/s", "\r\n", trim($text));
        $pos = strpos($text, "\r\n");
        if ($pos !== false) {
            return substr($text, 0, $pos);
        }
        return $text;
    }
    public function decodeHtmlEntities($text, $doUniCode = false)
    {
        if ($doUniCode) {
            $text = preg_replace_callback("/&#([0-9]+);/siU", function ($matches) {
                return convertUtf8Char($matches[1]);
            }, $text);
        }
        return str_replace(["&lt;", "&gt;", "&quot;", "&amp;"], ["<", ">", "\"", "&"], $text);
    }
    public function encodeEmailHeaderRFC2047($text, $charset = "utf-8", $force_encode = false, $quoted_string = true)
    {
        $text = trim($text);
        if (!$charset) {
            return $text;
        }
        if ($force_encode) {
            $emailEncodingNeeded = true;
        } else {
            $emailEncodingNeeded = false;
            $i = 0;
            while ($i < strlen($text)) {
                if (127 < ord($text[$i])) {
                    $emailEncodingNeeded = true;
                } else {
                    $i++;
                }
            }
        }
        if ($emailEncodingNeeded) {
            $emailSubjectEncoded = preg_replace_callback("#([^a-zA-Z0-9!*+\\-/ ])#", function ($matches) {
                return "'=" . strtoupper(dechex(ord(str_replace("\\\\\"", "\\\"", $matches[1]))));
            }, $text);
            $emailSubjectEncoded = str_replace(" ", "_", $emailSubjectEncoded);
            $emailSubjectEncoded = "=?" . $charset . "?q?" . $emailSubjectEncoded . "?=";
            return $emailSubjectEncoded;
        }
        if ($quoted_string) {
            $text = str_replace(["\"", "(", ")"], ["\\\"", "\\(", "\\)"], $text);
            return "\"" . $text . "\"";
        }
        return preg_replace("#(\\r\\n|\\n|\\r)+#", " ", $text);
    }
}
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $editorContent = ob_get_contents();
    ob_end_clean();
    return $editorContent;
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
function redirectTo($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; $url = " . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
    }
    exit;
}
function function_100($to, $subject, $body)
{
    global $MAIN;
    global $SMTP;
    global $THEME;
    $emailIsPrepared = $MAIN["SITENAME"];
    $fromemail = $MAIN["SITEEMAIL"];
    $siteNameForEmail = false;
    if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
        $emailLineDelimiter = "\r\n";
        $siteNameForEmail = true;
    } else {
        if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
            $emailLineDelimiter = "\r";
        } else {
            $emailLineDelimiter = "\n";
        }
    }
    $uniqueEmailId = md5(uniqid(rand(), true) . time());
    $serverName = $_SERVER["SERVER_NAME"];
    $headers = "From: " . $emailIsPrepared . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Reply-To: " . $emailIsPrepared . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Return-Path: " . $emailIsPrepared . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Message-ID: <" . $uniqueEmailId . " thesystem@" . $serverName . ">" . $emailLineDelimiter;
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
            if ($siteNameForEmail) {
                ini_set("sendmail_from", $SMTP["smtp_from"]);
            }
        }
        $emailHeaderEncoded = mail($to, $subject, $body, $headers);
        if (isset($SMTP["smtp"]) && $SMTP["smtp"] == "yes") {
            ini_restore("SMTP");
            ini_restore("smtp_port");
            if ($siteNameForEmail) {
                ini_restore("sendmail_from");
            }
        }
        return $emailHeaderEncoded;
    }
    $emailHandler = new Class_5($SMTP);
    $emailHandler->function_91($to, trim($subject), trim($body), $fromemail, "", $THEME["charset"], $fromemail, $MAIN["BASEURL"]);
    $emailSendResult = $emailHandler->smtpSendEmail();
    return $emailSendResult;
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