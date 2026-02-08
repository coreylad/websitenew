<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/reset_password.lang");
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : "";
$password = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'THEME'");
$Result = mysqli_fetch_assoc($query);
$THEME = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'SMTP'");
$Result = mysqli_fetch_assoc($query);
$SMTP = unserialize($Result["content"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    if ($username) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, email, username, g.cansettingspanel, g.issupermod, g.canstaffpanel FROM users LEFT JOIN usergroups g ON (users.$usergroup = g.gid) WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
        if (mysqli_num_rows($query) == 0) {
            $Message = showAlertError($Language[4]);
        } else {
            $User = mysqli_fetch_assoc($query);
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
            $LoggedAdminDetails = mysqli_fetch_assoc($query);
            if ($User["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $User["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $User["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes") {
                $Message = showAlertError($Language[5]);
            } else {
                $SysMsg = str_replace(["{1}", "{2}"], [$username, $_SESSION["ADMIN_USERNAME"]], $Language[11]);
                $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
                $secret = generateSecret();
                $password = generateSecret(8);
                $passhash = md5($secret . $password . $secret);
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $passhash = '" . $passhash . "', $secret = '" . $secret . "', $modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment) WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    logStaffAction($SysMsg);
                    if (isset($_POST["email"]) && $_POST["email"] == "yes") {
                        echo "\r\n\t\t\t\t\t\t<div $id = \"sending\" $name = \"sending\">\r\n\t\t\t\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"tcat\">" . $Language[2] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"alt1\">";
                        $emessage = str_replace(["{1}", "{2}", "{3}", "{4}"], [$username, $_SESSION["ADMIN_USERNAME"], $password, $MAIN["BASEURL"]], $Language[12]);
                        echo "\r\n\t\t\t\t\t\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\" $width = \"650\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $width = \"25%\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[8] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td $width = \"60%\">\r\n\t\t\t\t\t\t\t\t\t<b>" . htmlspecialchars($User["email"]) . "</b>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td $width = \"15%\">";
                        if (function_100($User["email"], $Language[2], nl2br($emessage))) {
                            echo "\r\n\t\t\t\t\t\t\t\t\t\t\t\t<font $color = \"green\">" . $Language[9] . "</font>";
                        } else {
                            echo "\r\n\t\t\t\t\t\t\t\t\t\t\t\t<font $color = \"red\">" . $Language[10] . "</font>";
                        }
                        echo "\r\n\t\t\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t\t</div>";
                        echo showAlertError($SysMsg);
                    }
                    $Message = showAlertError($SysMsg);
                } else {
                    $Message = showAlertError($Language[15]);
                }
            }
        }
    } else {
        $Message = showAlertError($Language[3]);
    }
}
echo "<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=reset_password\" $method = \"post\">\r\n";
echo $Message;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\">";
echo $Language[6];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"username\" $value = \"";
echo htmlspecialchars($username);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\">";
echo $Language[16];
echo "</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t<div class=\"smallfont\" $style = \"white-space:nowrap\">\r\n\t\t\t\t<input $type = \"radio\" $name = \"email\" $id = \"rb_1_exact_2\" $value = \"yes\" $tabindex = \"1\" $checked = \"checked\" />";
echo $Language[17];
echo "\t\t\t\t<input $type = \"radio\" $name = \"email\" $id = \"rb_0_exact_2\" $value = \"no\" $tabindex = \"1\" />";
echo $Language[18];
echo "\t\t\t</div>\r\n\t\t</td>\r\n\t</tr>\r\n\t";
if ($password != "") {
    echo "\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\">";
    echo $Language[19];
    echo "</td>\r\n\t\t<td class=\"alt1\">";
    echo htmlspecialchars($password);
    echo "</td>\r\n\t</tr>\r\n\t";
}
echo "\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[13];
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[14];
echo "\" $accesskey = \"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function generateSecret($length = 20)
{
    $characters = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    $secretString = "";
    for ($i = 1; $i <= $length; $i++) {
        $ch = rand(0, count($characters) - 1);
        $secretString .= $characters[$ch];
    }
    return $secretString;
}

?>