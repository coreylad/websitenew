<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

class TS_SMTP
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
            $this->secure = "tls";
        } else {
            $this->secure = "none";
        }
        $this->smtpHost = $SMTP["smtpaddress"];
        $this->smtpPort = !empty($SMTP["smtpport"]) ? intval($SMTP["smtpport"]) : 25;
        $this->smtpUser =& $SMTP["accountname"];
        $this->smtpPass =& $SMTP["accountpassword"];
        $this->delimiter = "\r\n";
    }
    public function start($toemail, $subject, $message, $from = "", $uheaders = "", $charset = "", $webmasteremail = "", $http_host = "")
    {
        $toemail = $this->fetch_first_line($toemail);
        if (empty($toemail)) {
            return false;
        }
        $delimiter =& $this->delimiter;
        $toemail = $this->dounhtmlspecialchars($toemail);
        $subject = $this->fetch_first_line($subject);
        $message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
        if ((strtolower($charset) == "iso-8859-1" || $charset == "") && preg_match("/&[a-z0-9#]+;/i", $message)) {
            $message = utf8_encode($message);
            $subject = utf8_encode($subject);
            $encoding = "UTF-8";
            $unicode_decode = true;
        } else {
            $encoding = $charset;
            $unicode_decode = false;
        }
        $message = $this->dounhtmlspecialchars($message, $unicode_decode);
        $subject = $this->encode_email_header($this->dounhtmlspecialchars($subject, $unicode_decode), $encoding, false, false);
        $from = $this->fetch_first_line($from);
        if (empty($from)) {
            $mailfromname = "PHP/" . phpversion() . " via the PHP TS SE SMTP Class";
            if ($unicode_decode) {
                $mailfromname = utf8_encode($mailfromname);
            }
            $mailfromname = $this->encode_email_header($this->dounhtmlspecialchars($mailfromname, $unicode_decode), $encoding);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $mailfromname . " <" . $webmasteremail . ">" . $delimiter;
            $headers .= "Auto-Submitted: auto-generated" . $delimiter;
        } else {
            $mailfromname = $from;
            if ($unicode_decode) {
                $mailfromname = utf8_encode($mailfromname);
            }
            $mailfromname = $this->encode_email_header($this->dounhtmlspecialchars($mailfromname, $unicode_decode), $encoding);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $mailfromname . " <" . $from . ">" . $delimiter;
            $headers .= "Sender: " . $webmasteremail . $delimiter;
        }
        $fromemail = $webmasteremail;
        $headers .= "Return-Path: " . $fromemail . $delimiter;
        if (!$http_host) {
            $http_host = substr(md5($message), 12, 18) . ".ts_unknown.unknown";
        }
        $msgid = "<" . gmdate("YmdHis") . "." . substr(md5($message . microtime()), 0, 12) . "@" . $http_host . ">";
        $headers .= "Message-ID: " . $msgid . $delimiter;
        $headers .= preg_replace("#(\r\n|\r|\n)#s", $delimiter, $uheaders);
        unset($uheaders);
        $headers .= "MIME-Version: 1.0" . $delimiter;
        $headers .= "Content-Type: text/html" . ($encoding ? "; charset=\"" . $encoding . "\"" : "") . $delimiter;
        $headers .= "Content-Transfer-Encoding: 8bit" . $delimiter;
        $headers .= "X-Priority: 3" . $delimiter;
        $headers .= "X-Mailer: TS SE Mail via PHP" . $delimiter;
        $headers .= "Date: " . date("r") . $delimiter;
        $this->toemail = $toemail;
        $this->subject = $subject;
        $this->message = $message;
        $this->headers = $headers;
        $this->fromemail = $fromemail;
        return true;
    }
    public function sendMessage($msg, $expectedResult = false)
    {
        if ($msg !== false && !empty($msg)) {
            fputs($this->smtpSocket, $msg . "\r\n");
        }
        if ($expectedResult !== false) {
            $result = "";
            while ($line = @fgets($this->smtpSocket, 1024)) {
                $result .= $line;
                if (!preg_match("#^(\\d{3}) #", $line, $matches)) {
                }
            }
            $this->smtpReturn = intval($matches[1]);
            return $this->smtpReturn == $expectedResult;
        }
        return true;
    }
    public function errorMessage($msg)
    {
        if ($this->debug) {
            trigger_error($msg, 512);
        }
        return false;
    }
    public function sendHello()
    {
        if (!$this->smtpSocket) {
            return false;
        }
        if (!$this->sendMessage("EHLO " . $this->smtpHost, 250) && !$this->sendMessage("HELO " . $this->smtpHost, 250)) {
            return false;
        }
        return true;
    }
    public function send()
    {
        if (!$this->toemail) {
            return false;
        }
        $this->smtpSocket = fsockopen(($this->secure == "ssl" ? "ssl://" : "tcp://") . $this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
        if ($this->smtpSocket) {
            if (!$this->sendMessage(false, 220)) {
                return $this->errorMessage($this->smtpReturn . " Unexpected response when connecting to SMTP server");
            }
            if (!$this->sendHello()) {
                return $this->errorMessage($this->smtpReturn . " Unexpected response from SMTP server during handshake");
            }
            if ($this->secure == "tls" && function_exists("stream_socket_enable_crypto")) {
                if ($this->sendMessage("STARTTLS", 220) && !stream_socket_enable_crypto($this->smtpSocket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return $this->errorMessage("Unable to negotitate TLS handshake.");
                }
                $this->sendHello();
            }
            if ($this->smtpUser && $this->smtpPass && $this->sendMessage("AUTH LOGIN", 334) && (!$this->sendMessage(base64_encode($this->smtpUser), 334) || !$this->sendMessage(base64_encode($this->smtpPass), 235))) {
                return $this->errorMessage($this->smtpReturn . " Authorization to the SMTP server failed");
            }
            if (!$this->sendMessage("MAIL FROM:<" . $this->fromemail . ">", 250)) {
                return $this->errorMessage($this->smtpReturn . " Unexpected response from SMTP server during FROM address transmission");
            }
            $addresses = explode(",", $this->toemail);
            foreach ($addresses as $address) {
                if (!$this->sendMessage("RCPT TO:<" . trim($address) . ">", 250)) {
                    return $this->errorMessage($this->smtpReturn . " Unexpected response from SMTP server during TO address transmission");
                }
            }
            if ($this->sendMessage("DATA", 354)) {
                $this->sendMessage("Date: " . gmdate("r"), false);
                $this->sendMessage("To: " . $this->toemail, false);
                $this->sendMessage(trim($this->headers), false);
                $this->sendMessage("Subject: " . $this->subject, false);
                $this->sendMessage("\r\n", false);
                $this->message = preg_replace("#^\\." . $this->delimiter . "#m", ".." . $this->delimiter, $this->message);
                $this->sendMessage($this->message, false);
                if (!$this->sendMessage(".", 250)) {
                    return $this->errorMessage($this->smtpReturn . " Unexpected response from SMTP server when ending transmission");
                }
                $this->sendMessage("QUIT", 221);
                fclose($this->smtpSocket);
                return true;
            }
            return $this->errorMessage($this->smtpReturn . " Unexpected response from SMTP server during data transmission");
        } else {
            return $this->errorMessage("Unable to connect to SMTP server");
        }
    }
    public function fetch_first_line($text)
    {
        $text = preg_replace("/(\r\n|\r|\n)/s", "\r\n", trim($text));
        $pos = strpos($text, "\r\n");
        if ($pos !== false) {
            return substr($text, 0, $pos);
        }
        return $text;
    }
    public function dounhtmlspecialchars($text, $doUniCode = false)
    {
        if ($doUniCode) {
            $text = preg_replace_callback("/&#([0-9]+);/siU", function ($matches) {
                return convert_int_to_utf8($matches[1]);
            }, $text);
        }
        return str_replace(["&lt;", "&gt;", "&quot;", "&amp;"], ["<", ">", "\"", "&"], $text);
    }
    public function encode_email_header($text, $charset = "utf-8", $force_encode = false, $quoted_string = true)
    {
        $text = trim($text);
        if (!$charset) {
            return $text;
        }
        if ($force_encode) {
            $qp_encode = true;
        } else {
            $qp_encode = false;
            $i = 0;
            while ($i < strlen($text)) {
                if (127 < ord($text[$i])) {
                    $qp_encode = true;
                } else {
                    $i++;
                }
            }
        }
        if ($qp_encode) {
            $outtext = preg_replace_callback("#([^a-zA-Z0-9!*+\\-/ ])#", function ($matches) {
                return "'=" . strtoupper(dechex(ord(str_replace("\\\\\"", "\\\"", $matches[1]))));
            }, $text);
            $outtext = str_replace(" ", "_", $outtext);
            $outtext = "=?" . $charset . "?q?" . $outtext . "?=";
            return $outtext;
        }
        if ($quoted_string) {
            $text = str_replace(["\"", "(", ")"], ["\\\"", "\\(", "\\)"], $text);
            return "\"" . $text . "\"";
        }
        return preg_replace("#(\\r\\n|\\n|\\r)+#", " ", $text);
    }
}

?>