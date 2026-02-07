<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/reset_password.lang");
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : "";
$password = "";
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
$Result = mysqli_fetch_assoc($Q);
$MAIN = unserialize($Result["content"]);
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'THEME'");
$Result = mysqli_fetch_assoc($Q);
$THEME = unserialize($Result["content"]);
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'SMTP'");
$Result = mysqli_fetch_assoc($Q);
$SMTP = unserialize($Result["content"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    if ($username) {
        $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, email, username, g.cansettingspanel, g.issupermod, g.canstaffpanel FROM users LEFT JOIN usergroups g ON (users.$usergroup = g.gid) WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
        if (mysqli_num_rows($Query) == 0) {
            $Message = showAlertError($Language[4]);
        } else {
            $User = mysqli_fetch_assoc($Query);
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
            $LoggedAdminDetails = mysqli_fetch_assoc($query);
            if ($User["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $User["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $User["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes") {
                $Message = showAlertError($Language[5]);
            } else {
                $SysMsg = str_replace(["{1}", "{2}"], [$username, $_SESSION["ADMIN_USERNAME"]], $Language[11]);
                $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
                $secret = generateSecret();
                $password = generateSecret(8);
                $passhash = md5($secret . $password . $secret);
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $passhash = '" . $passhash . "', $secret = '" . $secret . "', $modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment) WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    logStaffAction($SysMsg);
                    if (isset($_POST["email"]) && $_POST["email"] == "yes") {
                        echo "\r\n\t\t\t\t\t\t<div $id = \"sending\" $name = \"sending\">\r\n\t\t\t\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"tcat\">" . $Language[2] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"alt1\">";
                        $emessage = str_replace(["{1}", "{2}", "{3}", "{4}"], [$username, $_SESSION["ADMIN_USERNAME"], $password, $MAIN["BASEURL"]], $Language[12]);
                        echo "\r\n\t\t\t\t\t\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\" $width = \"650\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $width = \"25%\">\r\n\t\t\t\t\t\t\t\t\t" . $Language[8] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td $width = \"60%\">\r\n\t\t\t\t\t\t\t\t\t<b>" . htmlspecialchars($User["email"]) . "</b>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td $width = \"15%\">";
                        if (var_283($User["email"], $Language[2], nl2br($emessage))) {
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
            $var_287 = "PHP/" . phpversion() . " via the PHP TS SE SMTP Class";
            if ($var_286) {
                $var_287 = utf8_encode($var_287);
            }
            $var_287 = $this->function_94($this->function_93($var_287, $var_286), $var_285);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $var_287 . " <" . $webmasteremail . ">" . $delimiter;
            $headers .= "Auto-Submitted: auto-generated" . $delimiter;
        } else {
            $var_287 = $from;
            if ($var_286) {
                $var_287 = utf8_encode($var_287);
            }
            $var_287 = $this->function_94($this->function_93($var_287, $var_286), $var_285);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $var_287 . " <" . $from . ">" . $delimiter;
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
function function_100($to, $subject, $body)
{
    global $MAIN;
    global $SMTP;
    global $THEME;
    $var_295 = $MAIN["SITENAME"];
    $fromemail = $MAIN["SITEEMAIL"];
    $var_296 = false;
    if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
        $var_297 = "\r\n";
        $var_296 = true;
    } else {
        if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
            $var_297 = "\r";
        } else {
            $var_297 = "\n";
        }
    }
    $var_298 = md5(uniqid(rand(), true) . time());
    $var_41 = $_SERVER["SERVER_NAME"];
    $headers = "From: " . $var_295 . " <" . $fromemail . ">" . $var_297;
    $headers .= "Reply-To: " . $var_295 . " <" . $fromemail . ">" . $var_297;
    $headers .= "Return-Path: " . $var_295 . " <" . $fromemail . ">" . $var_297;
    $headers .= "Message-ID: <" . $var_298 . " thesystem@" . $var_41 . ">" . $var_297;
    $headers .= "X-Mailer: PHP v" . phpversion() . $var_297;
    $headers .= "MIME-Version: 1.0" . $var_297;
    $headers .= "Content-Transfer-Encoding: 8bit" . $var_297;
    $headers .= "Content-type: text/html; $charset = " . $THEME["charset"] . $var_297;
    $headers .= "X-Sender: PHP" . $var_297;
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
    $var_308 = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    $var_309 = "";
    for ($i = 1; $i <= $length; $i++) {
        $ch = rand(0, count($var_308) - 1);
        $var_309 .= $var_308[$ch];
    }
    return $var_309;
}

?>