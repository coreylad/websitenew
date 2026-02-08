<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/banned_users.lang");
$Message = "";
$usergroup = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'THEME'");
$Result = mysqli_fetch_assoc($query);
$THEME = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'SMTP'");
$Result = mysqli_fetch_assoc($query);
$SMTP = unserialize($Result["content"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["ids"]) && $_POST["ids"][0] != "") {
    $usergroup = intval($_POST["usergroup"]);
    if ($usergroup) {
        $SysMsg = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[15]);
        $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `enabled` = 'yes', $usergroup = '" . $usergroup . "', $modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment) WHERE id IN (" . implode(",", $_POST["ids"]) . ")");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $SysMsg = str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], implode(",", $_POST["ids"])], $Language[16]);
            logStaffAction($SysMsg);
            if (isset($_POST["email"]) && $_POST["email"] == "yes") {
                echo "\r\n\t\t\t\t<div $id = \"sending\" $name = \"sending\">\r\n\t\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"tcat\">" . $Language[14] . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"alt1\">";
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, username, email FROM users WHERE id IN (" . implode(",", $_POST["ids"]) . ")");
                while ($User = mysqli_fetch_assoc($query)) {
                    if (isset($_POST["unbanip"]) && $_POST["unbanip"] == "yes") {
                        function_113($User["id"]);
                    }
                    $emessage = str_replace(["{1}", "{2}", "{3}"], [$User["username"], $_SESSION["ADMIN_USERNAME"], $MAIN["BASEURL"]], $Language[24]);
                    echo "\r\n\t\t\t\t\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\" $width = \"650\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $width = \"25%\">\r\n\t\t\t\t\t\t\t\t" . $Language[21] . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td $width = \"60%\">\r\n\t\t\t\t\t\t\t\t<b>" . htmlspecialchars($User["email"]) . "</b>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td $width = \"15%\">";
                    if (function_100($User["email"], $Language[14], nl2br($emessage))) {
                        echo "\r\n\t\t\t\t\t\t\t\t\t\t\t<font $color = \"green\">" . $Language[22] . "</font>";
                    } else {
                        echo "\r\n\t\t\t\t\t\t\t\t\t\t\t<font $color = \"red\">" . $Language[23] . "</font>";
                    }
                    echo "\r\n\t\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t</table>";
                }
                echo "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>";
                echo showAlertError($Language[25]);
            } else {
                if (isset($_POST["unbanip"]) && $_POST["unbanip"] == "yes") {
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE id IN (" . implode(",", $_POST["ids"]) . ")");
                    while ($User = mysqli_fetch_assoc($query)) {
                        function_113($User["id"]);
                    }
                }
                $Message = showAlertError($Language[25]);
            }
        }
    } else {
        $Message = showAlertError($Language[17]);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
$LoggedAdminDetails = mysqli_fetch_assoc($query);
$showusergroups = "\r\n<select $name = \"usergroup\" $tabindex = \"1\" class=\"bginput\">";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod FROM usergroups WHERE `isbanned` = 'no' ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    if (!($UG["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $UG["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $UG["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes")) {
        $showusergroups .= "\r\n\t\t<option $value = \"" . $UG["gid"] . "\"" . ($usergroup == $UG["gid"] ? " $selected = \"selected\"" : "") . ">" . $UG["title"] . "</option>";
    }
}
$showusergroups .= "\r\n</select>";
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM users where $enabled = 'no'"));
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=banned_users&amp;");
$sql = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, username, last_access, notifs, email, ip, uploaded, downloaded, invites, seedbonus, g.title, g.namestyle FROM users LEFT JOIN usergroups g ON (users.$usergroup = g.gid) WHERE `enabled` = 'no' ORDER BY last_access DESC " . $limit);
if (mysqli_num_rows($sql) == 0) {
    echo "\r\n\t\r\n\t" . showAlertError($Language[1]);
} else {
    $Found = "";
    while ($User = mysqli_fetch_assoc($sql)) {
        if ($User["notifs"][0] != "[") {
            $Reason = "<small><i>" . str_replace("Reason:", "", $User["notifs"]) . "</i></small>";
        } else {
            $Reason = "--";
        }
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $User["username"] . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $User["title"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($User["email"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($User["ip"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($User["last_access"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatBytes($User["uploaded"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatBytes($User["downloaded"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $User["seedbonus"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Reason . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"ids[]\" $value = \"" . $User["id"] . "\" $checkme = \"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked == false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=banned_users" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"banned_users\">\r\n\t\r\n\t" . $Message . "\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"11\"><b>" . str_replace("{1}", number_format($results), $Language[3]) . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[10] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[19] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('banned_users', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"11\" $align = \"right\" class=\"tcat2\">\r\n\t\t\t\t" . $Language[26] . " <input $type = \"checkbox\" $name = \"unbanip\" $value = \"yes\" $checked = \"checked\" /> " . $Language[20] . " <input $type = \"checkbox\" $name = \"email\" $value = \"yes\" $checked = \"checked\" /> " . $Language[18] . " " . $showusergroups . " <input $type = \"submit\" $value = \"" . $Language[14] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t" . $pagertop;
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
    $paginationOffset = $pagenumber * $perpage;
    if ($results < $paginationOffset) {
        $paginationOffset = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    $paginationLinks = $prevPage = $nextPage = $pageLinks = $paginationHtml = "";
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
        $previousPageInfo = calculatePagination($previousPage, $perpage, $results);
        $paginationHtml["prev"] = true;
    }
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPageNumber, $perpage, $results);
        $paginationHtml["next"] = true;
    }
    $pageRangeThreshold = "3";
    if (!isset($paginationSkipLinksArray) || !is_array($paginationSkipLinksArray)) {
        $paginationOptions = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $paginationOptions, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $previousPageQuery = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $previousPageQuery . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($pageRangeThreshold <= abs($currentPage - $pagenumber) && $pageRangeThreshold != 0) {
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
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_113($userid)
{
    $userId = [];
    $errorMessage = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM iplog WHERE `userid` = '" . $userid . "'");
    if (mysqli_num_rows($errorMessage)) {
        while ($userName = mysqli_fetch_assoc($errorMessage)) {
            if ($userName["ip"]) {
                $userId[] = trim($userName["ip"]);
            }
        }
    }
    if ($userid) {
        $errorMessage = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM users WHERE `id` = '" . $userid . "'");
        if (mysqli_num_rows($errorMessage)) {
            $Result = mysqli_fetch_assoc($errorMessage);
            $userEmail = trim($Result["ip"]);
            if (!in_array($userEmail, $userId)) {
                $userId[] = $userEmail;
            }
            $userGroup = ip2long($userEmail);
            mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM xbt_deny_from_hosts WHERE $begin = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $userGroup) . "' OR $end = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $userGroup) . "'");
        }
    }
    $userStatus = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT value FROM ipbans WHERE `id` = 1");
    if (mysqli_num_rows($userStatus)) {
        $Result = mysqli_fetch_assoc($userStatus);
        $value = trim($Result["value"]);
        $value = explode(" ", $value);
        if (count($userId)) {
            foreach ($userId as $userData) {
                if (in_array($userData, $value)) {
                    $key = array_search($userData, $value);
                    unset($value[$key]);
                }
            }
        }
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ipbans SET $value = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], trim(implode(" ", $value))) . "', $date = NOW(), $modifier = '" . $_SESSION["ADMIN_ID"] . "' WHERE `id` = 1");
    }
}

?>