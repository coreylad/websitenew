<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function_63();
var_225();
var_226();
var_227();
class Class_4
{
    public $AnahtarKelime = NULL;
    public function __construct($AK)
    {
        $this->AnahtarKelime = trim($AK);
    }
    public function function_17($NEYI)
    {
        $result = "";
        for ($i = 0; $i < strlen($NEYI); $i++) {
            $currentChar = substr($NEYI, $i, 1);
            $keyChar = substr($this->AnahtarKelime, $i % strlen($this->AnahtarKelime) - 1, 1);
            $currentChar = chr(ord($currentChar) + ord($keyChar));
            $result .= $currentChar;
        }
        return urlencode(base64_encode($result));
    }
    public function function_18($NEYI)
    {
        $result = "";
        $NEYI = base64_decode(urldecode($NEYI));
        for ($i = 0; $i < strlen($NEYI); $i++) {
            $currentChar = substr($NEYI, $i, 1);
            $keyChar = substr($this->AnahtarKelime, $i % strlen($this->AnahtarKelime) - 1, 1);
            $currentChar = chr(ord($currentChar) - ord($keyChar));
            $result .= $currentChar;
        }
        return $result;
    }
}
function function_63()
{
    require_once "./../version.php";
    if (!ini_get("allow_url_fopen") && (!function_exists("curl_init") || !($ch = curl_init()))) {
        function_64("PHP allow_url_fopen or CURL must be turned on for this script to work!");
    }
}
function function_65()
{
    $var_228 = "./../../cache/ip.srv";
    if (isset($_SERVER["SERVER_ADDR"]) && !empty($_SERVER["SERVER_ADDR"]) && function_66($_SERVER["SERVER_ADDR"])) {
        $ip = $_SERVER["SERVER_ADDR"];
    } else {
        if (isset($_SERVER["LOCAL_ADDR"]) && !empty($_SERVER["LOCAL_ADDR"]) && function_66($_SERVER["LOCAL_ADDR"])) {
            $ip = $_SERVER["LOCAL_ADDR"];
        } else {
            if (file_exists($var_228) && TIMENOW < filemtime($var_228) + 1800) {
                $ip = file_get_contents($var_228);
            } else {
                if (function_exists("curl_init") && ($ch = curl_init())) {
                    curl_setopt($ch, CURLOPT_URL, "http://templateshares.biz/ip.php");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0");
                    $ip = curl_exec($ch);
                    curl_close($ch);
                    if (is_writable("./../../cache/ip.srv")) {
                        @file_put_contents($var_228, $ip);
                    }
                }
            }
        }
    }
    if (function_66($ip)) {
        return $ip;
    }
    if (file_exists($var_228)) {
        @unlink($var_228);
    }
}
function function_66($ip)
{
    return $ip != "127.0.0.1" && $ip != "::1" && filter_var($ip, FILTER_VALIDATE_IP);
}
function function_67()
{
    define("INSTALL_URL", !empty($_SERVER["SERVER_NAME"]) ? var_229($_SERVER["SERVER_NAME"]) : (!empty($_SERVER["HTTP_HOST"]) ? var_229($_SERVER["HTTP_HOST"]) : ""));
    define("INSTALL_IP", function_65());
    define("CACHED_ADMIN_FILE", "./../cache/admin_session.dat");
    define("ADMIN_CACHE", @file_get_contents(CACHED_ADMIN_FILE));
    define("TSSE2020CHECKTOOLPHP", true);
}
function function_68()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        function_64("Invalid Access!");
    } else {
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/license.php") || is_file($_SERVER["DOCUMENT_ROOT"] . "/license.php") || file_exists("/authenticate/" . SHORT_SCRIPT_VERSION . "/license.php") || is_file("/authenticate/" . SHORT_SCRIPT_VERSION . "/license.php")) {
            function_64("System Error: AL");
        } else {
            if (!file_exists(CACHED_ADMIN_FILE)) {
                function_64("System Error: Invalid License Key II.");
            } else {
                if (!ADMIN_CACHE || strlen(ADMIN_CACHE) != 32) {
                    function_64("System Error: Invalid License Key III. Please re-run the installation script.");
                }
            }
        }
    }
}
function function_69()
{
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_config SET $content = \"\" WHERE `configname` = \"PEER\"");
}
function function_70()
{
    $var_230 = var_231();
    @preg_match("#{LISENCE_KEY_RESPONSE}(.*){LISENCE_KEY_RESPONSE}#is", $var_230, $licenseKey);
    if (isset($licenseKey[1]) && $licenseKey[1]) {
        $var_232 = trim($licenseKey[1]);
        $licenseKey = strtoupper($var_232);
        if (!function_71($licenseKey)) {
            function_64("<b>Critical Error:</b> " . $var_232);
        } else {
            if (md5(INSTALL_URL . $licenseKey . INSTALL_URL) != ADMIN_CACHE) {
                function_64("System Error: License key mismatch. Please re-run the installation script.");
            }
        }
    } else {
        function_64("System Error: Invalid Response!");
    }
}
function function_72()
{
    $GLOBALS["Sifrele"] = new Class_4("TSSE8.02020httpstemplateshares.net!");
    $var_233 = "0=4&1=" . function_73(INSTALL_URL) . "&2=" . function_73(INSTALL_IP) . "&3=" . function_73(SHORT_SCRIPT_VERSION);
    $licenseUrl = "http://www.templateshares.info";
    $licenseHost = "www.templateshares.info";
    $licensePath = "/authenticate/" . SHORT_SCRIPT_VERSION . "/license.php";
    $userAgent = "TssEv8.0070420201510A";
    $referer = "TssEv8.0070420201510R";
    $connectTimeout = 15;
    if (function_exists("curl_init") && ($ch = curl_init())) {
        curl_setopt($ch, CURLOPT_URL, $licenseUrl . $licensePath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $var_233);
        $curlResult = curl_exec($ch);
        curl_close($ch);
        return $curlResult;
    }
    if ($fsock = @fsockopen($licenseHost, 80, fsockError, fsockErrorStr, $connectTimeout)) {
        $httpRequest = "POST " . $licensePath . " HTTP/1.0\r\n";
        $httpRequest .= "Host: " . $licenseHost . "\r\n";
        $httpRequest .= "User-Agent: " . $userAgent . "\r\n";
        $httpRequest .= "Referer: " . $referer . "\r\n";
        $httpRequest .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $httpRequest .= "Content-Length: " . strlen($var_233) . "\r\n\r\n";
        @socket_set_timeout(fsock, $connectTimeout);
        @fwrite(fsock, $httpRequest . $var_233);
        $curlResult = "";
        while (!@feof(fsock)) {
            $curlResult .= @fgets(fsock, 1024);
        }
        @fclose(fsock);
        return $curlResult;
    }
    function_64("Connection Error: fsockopen / Curl (PHP allow_url_fopen or Curl must be turned on for this script to work).");
}
function function_71($installkey = "")
{
    $var_97 = "{########-####-####-####-############}";
    $var_97 = @str_replace("#", "[0-9,A-F]", $var_97);
    if (@preg_match($var_97, $installkey)) {
        return true;
    }
    return false;
}
function function_64($message = "")
{
    var_234();
    echo "\r\n\t<html>\r\n\t\t<head>\r\n\t\t\t<title>TS SE: Critical Error!</title>\r\n\t\t\t<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = UTF-8\" />\r\n\t\t\t<style>\r\n\t\t\t\t*{padding: 0; margin: 0}\r\n\t\t\t\t.alert\r\n\t\t\t\t{\r\n\t\t\t\t\tfont-weight: bold;\r\n\t\t\t\t\tcolor: #fff;\r\n\t\t\t\t\tfont: 14px verdana, geneva, lucida, \"lucida grande\", arial, helvetica, sans-serif;\r\n\t\t\t\t\tbackground: #ffacac;\r\n\t\t\t\t\ttext-align: center;\t\t\t\t\t\r\n\t\t\t\t\tborder-bottom: 4px solid #000;\r\n\t\t\t\t\tpadding: 20px;\r\n\t\t\t\t}\r\n\t\t\t</style>\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<div class=\"alert\">\r\n\t\t\t\t" . $message . "\r\n\t\t\t</div>\r\n\t\t</body>\r\n\t</html>\r\n\t";
    exit;
}
function function_74($url)
{
    return str_replace(["http://www.", "https://www.", "http://", "https://", "www."], "", $url);
}
function function_73($data)
{
    return $GLOBALS["Sifrele"]->function_17($data);
}

?>