<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

@set_time_limit(0);
var_235();
$Language = file("languages/" . getStaffLanguage() . "/sent_mail.lang");
$Message = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$usergroups = [];
$subject = "";
$message = "";
$emails = isset($_GET["emails"]) ? trim($_GET["emails"]) : "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'THEME'");
$Result = mysqli_fetch_assoc($query);
$THEME = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'SMTP'");
$Result = mysqli_fetch_assoc($query);
$SMTP = unserialize($Result["content"]);
if ($Act == "post_mails") {
    $subject = $_POST["subject"] ? trim($_POST["subject"]) : "";
    $message = $_POST["message"] ? trim($_POST["message"]) : "";
    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
    $emails = $_POST["emails"] ? trim($_POST["emails"]) : "";
    $Output = "";
    if ($subject && $message && (is_array($usergroups) && count($usergroups) || $emails)) {
        $Output = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">";
        if ($emails) {
            $SEmails = explode(",", $emails);
            if ($SEmails) {
                foreach ($SEmails as $email) {
                    $Output .= "\t\t\t\t\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $Language[12] . "</td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . htmlspecialchars($email) . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\">";
                    if (var_283($email, $subject, nl2br($message))) {
                        $Output .= "<font $color = \"green\">" . $Language[13] . "</font>";
                    } else {
                        $Output .= "<font $color = \"red\">" . $Language[14] . "</font>";
                    }
                    $Output .= "</td>\r\n\t\t\t\t\t\t</tr>";
                }
                $Output .= "\r\n\t\t\t\t</table>";
                $Message = showAlertError($Language[11]) . $Output;
                logStaffAction(str_replace(["{1}", "{2}", "{3}"], [$_SESSION["ADMIN_USERNAME"], implode(",", $SEmails), $subject], $Language[16]));
            }
        } else {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT email, options FROM users WHERE usergroup IN (0, " . implode(",", $usergroups) . ")");
            if (mysqli_num_rows($query)) {
                while ($EM = mysqli_fetch_assoc($query)) {
                    $Output .= "\t\t\t\t\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $Language[12] . "</td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . htmlspecialchars($EM["email"]) . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\">";
                    if (preg_match("#S0#i", $EM["options"])) {
                        $Output .= "<font $color = \"green\">" . $Language[17] . "</font>";
                    } else {
                        if (var_283($EM["email"], $subject, nl2br($message))) {
                            $Output .= "<font $color = \"green\">" . $Language[13] . "</font>";
                        } else {
                            $Output .= "<font $color = \"red\">" . $Language[14] . "</font>";
                        }
                    }
                    $Output .= "</td>\r\n\t\t\t\t\t</tr>";
                }
            }
            $Output .= "\r\n\t\t\t</table>";
            $Message = showAlertError($Language[11]) . $Output;
            logStaffAction(str_replace(["{1}", "{2}", "{3}"], [$_SESSION["ADMIN_USERNAME"], implode(",", $usergroups), $subject], $Language[15]));
        }
    } else {
        $Message = showAlertError($Language[9]);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
$LoggedAdminDetails = mysqli_fetch_assoc($query);
$showusergroups = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    if (!($UG["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $UG["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $UG["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes")) {
        $showusergroups .= "\r\n\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $UG["gid"] . "\"" . (in_array($UG["gid"], $usergroups) ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> " . strip_tags(str_replace("{username}", $UG["title"], $UG["namestyle"]), "<b><span><strong><em><i><u>") . "</label>\r\n\t\t</div>";
    }
}
echo loadTinyMCEEditor() . "\r\n<script $type = \"text/javascript\">\r\n\tif (TSGetID(\"sending\") != \"\")\r\n\t{\r\n\t\tTSGetID(\"sending\").style.$display = \"none\";\r\n\t}\r\n\tfunction SentMails()\r\n\t{\r\n\t\tTSGetID('buttons').$innerHTML = '<img $src = \"images/progress.gif\"> " . trim($Language[18]) . "';\r\n\t}\r\n</script>\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=sent_mail&$act = post_mails\" $onsubmit = \"SentMails();\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $style = \"width: 155px;\">" . $Language[3] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"subject\" $value = \"" . htmlspecialchars($subject) . "\" $style = \"width: 99%;\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt1\"><textarea $name = \"message\" $id = \"message\" $style = \"width: 99%; height: 200px;\">" . htmlspecialchars($message) . "</textarea>\r\n\t\t<p><a $href = \"javascript:toggleEditor('message');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"emails\" $value = \"" . htmlspecialchars($emails) . "\" $style = \"width: 99%;\"></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt1\">" . $showusergroups . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[6] . "\" /> <input $type = \"reset\" $value = \"" . $Language[7] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $var_81 = ob_get_contents();
    ob_end_clean();
    return $var_81;
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

?>