<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkEnvironmentRequirements();
var_225();
var_226();
var_227();
// DEAD CODE: Class_4 encryption class is only instantiated in fetchLicenseValidation() which is never called. Used for legacy TSSE license validation.
class Class_4
{
    public $AnahtarKelime = NULL;
    public function __construct($AK)
    {
        $this->AnahtarKelime = trim($AK);
    }
    public function encrypt($NEYI)
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
    public function decrypt($NEYI)
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
function checkEnvironmentRequirements()
{
    require_once "./../version.php";
    if (!ini_get("allow_url_fopen") && (!function_exists("curl_init") || !($ch = curl_init()))) {
        displayCriticalError("PHP allow_url_fopen or CURL must be turned on for this script to work!");
    }
}
// DEAD CODE: detectServerIP() is only called by initializeConstants() to get INSTALL_IP, but INSTALL_IP is never used anywhere. Legacy IP detection via templateshares.biz.
function detectServerIP()
{
    $ipCacheFile = "./../../cache/ip.srv";
    if (isset($_SERVER["SERVER_ADDR"]) && !empty($_SERVER["SERVER_ADDR"]) && isValidPublicIP($_SERVER["SERVER_ADDR"])) {
        $ip = $_SERVER["SERVER_ADDR"];
    } else {
        if (isset($_SERVER["LOCAL_ADDR"]) && !empty($_SERVER["LOCAL_ADDR"]) && isValidPublicIP($_SERVER["LOCAL_ADDR"])) {
            $ip = $_SERVER["LOCAL_ADDR"];
        } else {
            if (file_exists($ipCacheFile) && TIMENOW < filemtime($ipCacheFile) + 1800) {
                $ip = file_get_contents($ipCacheFile);
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
                        @file_put_contents($ipCacheFile, $ip);
                    }
                }
            }
        }
    }
    if (isValidPublicIP($ip)) {
        return $ip;
    }
    if (file_exists($ipCacheFile)) {
        @unlink($ipCacheFile);
    }
}
// DEAD CODE: isValidPublicIP() is only called by unused detectServerIP(). Helper for IP validation.
function isValidPublicIP($ip)
{
    return $ip != "127.0.0.1" && $ip != "::1" && filter_var($ip, FILTER_VALIDATE_IP);
}
function initializeConstants()
{
    define("INSTALL_URL", !empty($_SERVER["SERVER_NAME"]) ? var_229($_SERVER["SERVER_NAME"]) : (!empty($_SERVER["HTTP_HOST"]) ? var_229($_SERVER["HTTP_HOST"]) : ""));
    define("INSTALL_IP", detectServerIP());
    define("CACHED_ADMIN_FILE", "./../cache/admin_session.dat");
    define("ADMIN_CACHE", @file_get_contents(CACHED_ADMIN_FILE));
    define("TSSE2020CHECKTOOLPHP", true);
}
// DEAD CODE: validateLicenseCache() is never called. Legacy TSSE license validation that checks for license.php files and ADMIN_CACHE validity.
function validateLicenseCache()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        displayCriticalError("Invalid Access!");
    } else {
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/license.php") || is_file($_SERVER["DOCUMENT_ROOT"] . "/license.php") || file_exists("/authenticate/" . SHORT_SCRIPT_VERSION . "/license.php") || is_file("/authenticate/" . SHORT_SCRIPT_VERSION . "/license.php")) {
            displayCriticalError("System Error: AL");
        } else {
            if (!file_exists(CACHED_ADMIN_FILE)) {
                displayCriticalError("System Error: Invalid License Key II.");
            } else {
                if (!ADMIN_CACHE || strlen(ADMIN_CACHE) != 32) {
                    displayCriticalError("System Error: Invalid License Key III. Please re-run the installation script.");
                }
            }
        }
    }
}
// DEAD CODE: clearPeerConfig() is never called. Legacy function to clear PEER config in database.
function clearPeerConfig()
{
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_config SET $content = \"\" WHERE `configname` = \"PEER\"");
}
// DEAD CODE: parseLicenseResponse() is never called. Legacy TSSE license key response parser - validates license key format and MD5 hash.
function parseLicenseResponse()
{
    $licenseResponse = var_231();
    @preg_match("#{LISENCE_KEY_RESPONSE}(.*){LISENCE_KEY_RESPONSE}#is", $licenseResponse, $licenseKey);
    if (isset($licenseKey[1]) && $licenseKey[1]) {
        $trimmedKey = trim($licenseKey[1]);
        $licenseKey = strtoupper($trimmedKey);
        if (!isValidLicenseKeyFormat($licenseKey)) {
            displayCriticalError("<b>Critical Error:</b> " . $trimmedKey);
        } else {
            if (md5(INSTALL_URL . $licenseKey . INSTALL_URL) != ADMIN_CACHE) {
                displayCriticalError("System Error: License key mismatch. Please re-run the installation script.");
            }
        }
    } else {
        displayCriticalError("System Error: Invalid Response!");
    }
}
// DEAD CODE: fetchLicenseValidation() is never called. Legacy TSSE license validation via HTTP POST to templateshares.info (domain likely defunct).
function fetchLicenseValidation()
{
    $GLOBALS["Sifrele"] = new Class_4("TSSE8.02020httpstemplateshares.net!");
    $postData = "0=4&1=" . encryptData(INSTALL_URL) . "&2=" . encryptData(INSTALL_IP) . "&3=" . encryptData(SHORT_SCRIPT_VERSION);
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
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
        $httpRequest .= "Content-Length: " . strlen($postData) . "\r\n\r\n";
        @socket_set_timeout(fsock, $connectTimeout);
        @fwrite(fsock, $httpRequest . $postData);
        $curlResult = "";
        while (!@feof(fsock)) {
            $curlResult .= @fgets(fsock, 1024);
        }
        @fclose(fsock);
        return $curlResult;
    }
    displayCriticalError("Connection Error: fsockopen / Curl (PHP allow_url_fopen or Curl must be turned on for this script to work).");
}
// DEAD CODE: isValidLicenseKeyFormat() is only called by unused parseLicenseResponse(). Validates license key format using regex pattern.
function isValidLicenseKeyFormat($installkey = "")
{
    $licenseKeyPattern = "{########-####-####-####-############}";
    $licenseKeyPattern = @str_replace("#", "[0-9,A-F]", $licenseKeyPattern);
    if (@preg_match($licenseKeyPattern, $installkey)) {
        return true;
    }
    return false;
}
function displayCriticalError($message = "")
{
    var_234();
    echo "\r\n\t<html>\r\n\t\t<head>\r\n\t\t\t<title>TS SE: Critical Error!</title>\r\n\t\t\t<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = UTF-8\" />\r\n\t\t\t<style>\r\n\t\t\t\t*{padding: 0; margin: 0}\r\n\t\t\t\t.alert\r\n\t\t\t\t{\r\n\t\t\t\t\tfont-weight: bold;\r\n\t\t\t\t\tcolor: #fff;\r\n\t\t\t\t\tfont: 14px verdana, geneva, lucida, \"lucida grande\", arial, helvetica, sans-serif;\r\n\t\t\t\t\tbackground: #ffacac;\r\n\t\t\t\t\ttext-align: center;\t\t\t\t\t\r\n\t\t\t\t\tborder-bottom: 4px solid #000;\r\n\t\t\t\t\tpadding: 20px;\r\n\t\t\t\t}\r\n\t\t\t</style>\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<div class=\"alert\">\r\n\t\t\t\t" . $message . "\r\n\t\t\t</div>\r\n\t\t</body>\r\n\t</html>\r\n\t";
    exit;
}
// DEAD CODE: stripUrlPrefix() is never called. Helper to strip http/https/www prefixes from URLs.
function stripUrlPrefix($url)
{
    return str_replace(["http://www.", "https://www.", "http://", "https://", "www."], "", $url);
}
// DEAD CODE: encryptData() is only called by unused fetchLicenseValidation(). Wrapper for Class_4 encryption.
function encryptData($data)
{
    return $GLOBALS["Sifrele"]->encrypt($data);
}

?>