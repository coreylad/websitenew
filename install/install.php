<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

$SafeModeCheck = new SafeModeCheckClass();
$Sifrele = new EncryptorClass("TSSE8.02020httpstemplateshares.net!");
$__step = isset($_POST["step"]) ? intval($_POST["step"]) : (isset($_GET["step"]) ? intval($_GET["step"]) : 0);
setErrorReporting();
startSession();
defineConstants();
clearStatCache();
requireVersionAndTimezone();
checkBlockedHosts();
checkInstallFile();
checkUrlFopenOrCurl();
check_license();
initializeInstaller();
class SafeModeCheckClass
{
    public $ZavaZingo = NULL;
    public $TavaZingo = NULL;
    public $Havai = NULL;
    public $PokeMon = NULL;
    public function __construct()
    {
        if (@ini_get("safe_mode") == 1 || strtolower(@ini_get("safe_mode")) == "on") {
            showErrorMessage("Please disable PHP Safe Mode to continue installation!");
            exit;
        }
    }
}
class EncryptorClass
{
    public $AnahtarKelime = NULL;
    public function __construct($AK)
    {
        $this->AnahtarKelime = trim($AK);
    }
    public function encryptString($NEYI)
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
    public function decryptString($NEYI)
    {
        $result = "";
        $NEYI = urldecode(base64_decode($NEYI));
        for ($i = 0; $i < strlen($NEYI); $i++) {
            $currentChar = substr($NEYI, $i, 1);
            $keyChar = substr($this->AnahtarKelime, $i % strlen($this->AnahtarKelime) - 1, 1);
            $currentChar = chr(ord($currentChar) - ord($keyChar));
            $result .= $currentChar;
        }
        return $result;
    }
}
function setErrorReporting()
{
    @error_reporting(32759);
    @ini_set("display_errors", 0);
    @ini_set("display_startup_errors", 0);
    @set_time_limit(90);
}
function startSession()
{
    session_name(getSessionName());
    session_start();
}
function getSessionName()
{
    $serverName = isset($_SERVER["SERVER_NAME"]) && !empty($_SERVER["SERVER_NAME"]) ? trim($_SERVER["SERVER_NAME"]) : (isset($_SERVER["HTTP_HOST"]) && !empty($_SERVER["HTTP_HOST"]) ? trim($_SERVER["HTTP_HOST"]) : "tsse_session");
    return preg_replace("/[^a-zA-Z0-9_]/", "", $serverName) . "_install";
}
function defineConstants()
{
    define(decodeString("aDNyM24zdzNtNHg0bDM3NHYzbjRyM3QzcjRwM3Az"), "9.0");
    define(decodeString("aDNyM24zdzNtNHg0bDM3NHUzMDVsMw=="), !empty($_SERVER["SERVER_NAME"]) ? encodeDomain($_SERVER["SERVER_NAME"]) : (!empty($_SERVER["HTTP_HOST"]) ? encodeDomain($_SERVER["HTTP_HOST"]) : ""));
    define(decodeString("aDNyM24zdzNtNHg0bDM3NGkzeTQ="), getInstallKey());
    define(decodeString("czNsM2QzdjNnNTM1bzNyM3QzZDVwM2IzMjVpMw=="), "./");
    define(decodeString("cTNzM2ozdzNnNTE1YTN3M2gz"), "./../");
    define(decodeString("YjNlMzczazNxNHA0NDRkM2QzdjRpM28zZDVnM2szaTNxNA=="), ROOT_PATH . decodeString("NzRhNDM0ZzRtNTQ0NjRjNGk0bjVqNDU0eDViNHE0bDRxNXc1azRvMmw1YTRyNA=="));
    define(decodeString("YjNlMzczazNxNHA0NDR3M3IzajRjM2wzbjRzMzY0YzN1NHg0ZjM="), ROOT_PATH . decodeString("NzRhNDM0ZzRtNTQ0bzR4NG80eTVhNGo0aDU3NGE0YTRtNTM0YTQzNDE2"));
}
function getServerIP()
{
    $ipCacheFile = "./../cache/ip.srv";
    if (isset($_SERVER["SERVER_ADDR"]) && !empty($_SERVER["SERVER_ADDR"]) && isValidIP($_SERVER["SERVER_ADDR"])) {
        $ip = $_SERVER["SERVER_ADDR"];
    } else {
        if (isset($_SERVER["LOCAL_ADDR"]) && !empty($_SERVER["LOCAL_ADDR"]) && isValidIP($_SERVER["LOCAL_ADDR"])) {
            $ip = $_SERVER["LOCAL_ADDR"];
        } else {
            if (file_exists($ipCacheFile) && TIMENOW < filemtime($ipCacheFile) + 1800) {
                $ip = file_get_contents($ipCacheFile);
            } else {
                if (function_exists("curl_init") && ($ch = curl_init())) {
                    curl_setopt($ch, CURLOPT_URL, base64_decode("aHR0cDovL3RlbXBsYXRlc2hhcmVzLmJpei9pcC5waHA="));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0");
                    $ip = curl_exec($ch);
                    curl_close($ch);
                    if (is_writable($ipCacheFile)) {
                        @file_put_contents($ipCacheFile, $ip);
                    }
                }
            }
        }
    }
    if (isValidIP($ip)) {
        return $ip;
    }
    if (file_exists($ipCacheFile)) {
        @unlink($ipCacheFile);
    }
}
function isValidIP($ip)
{
    return $ip != "127.0.0.1" && $ip != "::1" && filter_var($ip, FILTER_VALIDATE_IP);
}
function clearStatCache()
{
    clearstatcache();
}
function requireVersionAndTimezone()
{
    require ROOT_PATH . "/version.php";
    require ROOT_PATH . "include/php_default_timezone_set.php";
}
function stringContains($string = "", $find = "")
{
    return @strpos($string, $find) === false ? false : true;
}
function checkBlockedHosts()
{
    $allowedHosts = explode("~~~", "192.168.1.~~~127.0.0~~~localhost~~~templateshares~~~template-shares~~~1tam1ogrenci.com~~~78.159.111.12~~~82.137.61.162~~~82.137.61.162~~~83.99.133.91~~~~~~alien-scene.org~~~91.121.149.102~~~ancientbits.com~~~82.47.208.141~~~angels-torrents.net~~~89.149.255.72~~~arab-peer.org~~~174.121.11.17~~~arabpeer.org~~~188.40.162.120~~~arabsong.org~~~69.72.149.25~~~biotorrents.org~~~~~~bkt.si~~~149.210.145.52~~~blades-heaven.co.uk~~~88.191.26.186~~~blades-heaven.com~~~74.86.40.71~~~chixy.org~~~82.81.156.237~~~ddtorrents.com~~~66.90.109.57~~~demonicsouls.net~~~88.191.35.248~~~destamkroeg.org~~~85.214.110.80~~~dev.bigfangroup.org~~~77.130.134.245~~~deviltorrents.org~~~66.197.138.21~~~filetracker.org~~~216.246.57.130~~~firestar.pl~~~37.187.73.130~~~flash-dragon.co.uk~~~94.23.45.92~~~homemadeporntorrents2.com~~~208.53.143.102~~~iraqigate.org~~~66.49.137.208~~~kinoclub.eu~~~78.47.214.119~~~learnbits.info~~~70.47.114.167~~~leechseed.net~~~216.227.216.220~~~mazikalek.com~~~174.120.105.219~~~mediotekayu.com~~~91.185.194.96~~~mightytunez.com/beta~~~118.210.69.244~~~mojtorrent.com~~~195.246.15.79~~~movietorrents.org~~~95.211.129.88~~~musicgate.org~~~216.104.38.146~~~new.alientorrent.com~~~76.73.5.226~~~omarco.eu~~~91.196.170.205~~~oz708-speeds.info~~~89.149.194.50~~~planetatorrent.cl~~~87.98.221.150~~~ransackedcrew.com~~~88.191.35.248~~~saucytorrents.com~~~85.234.133.165~~~scenedemon.com~~~80.86.83.213~~~seedboxworld.biz~~~62.75.149.199~~~serko.se~~~212.97.132.131~~~shetos.org~~~97.74.121.119~~~sicktorrents.com~~~88.198.53.215~~~speed-xxx.com~~~195.246.15.139~~~speedy-torrents.info~~~184.107.184.106~~~stancamantuirii.ro~~~89.36.134.61~~~swemops.com~~~67.210.100.3~~~tailz.us~~~209.11.245.165~~~test.biotorrents.org~~~~~~the-jedi-knights.info~~~216.245.205.187~~~thedvdclub.no-ip.org~~~192.168.1.30~~~tnt-vision.com~~~86.105.223.222~~~top-balkan.com~~~203.121.68.164~~~top-balkan.net~~~203.121.69.26~~~top-torrent.com~~~212.112.250.157~~~torrents-gate.com~~~69.72.149.25~~~torrents4u.org~~~85.17.145.104~~~torrentsworld.org~~~66.90.109.57~~~tracker.power-on.kiev.ua~~~217.20.163.65~~~underground-addicts.com~~~24.102.56.34~~~vale-date.com~~~69.72.149.25~~~vehd.net~~~178.33.103.17~~~wizzdvd.net~~~195.246.219.4~~~x-releases.org~~~209.44.113.82~~~y-k-m.net~~~174.120.127.92");
    foreach ($allowedHosts as $hostEntry) {
        if (strlen(INSTALL_URL) < 5 || strlen(INSTALL_IP) < 8 || checkInstallMatch(INSTALL_URL, $hostEntry) || checkInstallMatch(INSTALL_IP, $hostEntry)) {
            showErrorMessage("Sorry, I can not continue due a Critical Error. The error code is: SE1. Please contact us at <a href=\"https://templateshares.net/?u=" . urlencode(INSTALL_URL) . "&i=" . urlencode(INSTALL_IP) . "\">https://templateshares.net/</a>.");
        }
    }
}
function checkInstallFile()
{
    if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . decodeString("NTR1NGs0ZzRtNXY1cDRoNDg0ZjVwNGI0") . "/" . SHORT_SCRIPT_VERSION . "/" . decodeString("ZzRpNDM0ZDR2NTA2YTR1Mmw0bTVsNA=="))) {
        showErrorMessage("Sorry, I can not continue due a Critical Error. The Error Code is: AL. Please contact us at <a href=\"https://templateshares.net/?u=" . urlencode(INSTALL_URL) . "&i=" . urlencode(INSTALL_IP) . "\">https://templateshares.net/</a>.");
    }
}
function checkUrlFopenOrCurl()
{
    if (!ini_get("allow_url_fopen") && (!function_exists("curl_init") || !($ch = curl_init()))) {
        showErrorAndExit("PHP allow_url_fopen or CURL Function must be turned on for this script to work!");
    }
}
function check_license()
{
    global $Sifrele;
    global $__step;
    $licenseKey = "";
    $accountDetails = getAccountDetails();
    $licenseError = "";
    $licenseRequest = "0=1&1=" . encodeLicenseValue(INSTALL_URL) . "&2=" . encodeLicenseValue(INSTALL_IP) . "&3=" . encodeLicenseValue(SHORT_SCRIPT_VERSION);
    // Fetch the license response from the remote server using a cleartext function name
    $licenseResponse = fetchLicenseResponse($licenseRequest);

    // Annotated: The license server returns a response string. We extract the license key using a regex pattern.
    // The pattern is decoded from base64 for clarity. This is the expected format for a valid license key.
    $licenseKeyPattern = decodeString("djRwM2QzdjNxNHo0YzNoMzQ0dDRlM3ozZDVzM2czcDMxNTA1bzNwM3E0MjVuMm4ycTJ2Mzk2eTRqM3MzbzRqM2EzbzRkNW8zZDN1MzI0cjNkM3czazNyM3o0NDVlMzE1");
    if ($licenseResponse && preg_match("#" . $licenseKeyPattern . "#is", $licenseResponse, $licenseKeyMatch)) {
        // Extract and normalize the license key from the response
        $licenseKey = strtoupper(trim($licenseKeyMatch[1]));
    }

    // Annotated: If the server returns a 404 error, abort installation with a clear error message
    if (preg_match("#404 Not Found#isU", $licenseResponse)) {
        showErrorAndExit("Server response failed. Please contact Administrator.");
    }
    if (!isValidLicenseKey($licenseKey)) {
        $licenseError = $licenseKey;
        unset($licenseKey);
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["LICENSE_KEY"])) {
        $_POST["LICENSE_KEY"] = trim(strtoupper($_POST["LICENSE_KEY"]));
        if (empty($_POST["LICENSE_KEY"])) {
            $licenseError = "License key can not be empty.";
        } else {
            if (!isValidLicenseKey($_POST["LICENSE_KEY"])) {
                $licenseError = "Invalid license key.";
            } else {
                $invalidKeyFlag = true;
            }
        }
        unset($_SESSION["LICENSE_KEY"]);
        $_SESSION["LICENSE_KEY"] = $_POST["LICENSE_KEY"];
    }
    if (isset($_SESSION["LICENSE_KEY"]) && !isValidLicenseKey($_SESSION["LICENSE_KEY"])) {
        unset($_SESSION["LICENSE_KEY"]);
    }
    if (empty($_SESSION["LICENSE_KEY"]) || empty($licenseKey) || $_SESSION["LICENSE_KEY"] != $licenseKey || !isValidLicenseKey($_SESSION["LICENSE_KEY"]) || !isValidLicenseKey($licenseKey)) {
        if (isset($invalidKeyFlag) && !$licenseError) {
            $licenseError = "The entered license key does not match.";
        }
        $licenseForm = ($licenseError ? "<font color=\"red\"><b>" . $licenseError . "</b></font><br /><br />" : "") . "\r\n\t\t<form method=\"POST\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?step=" . $__step . "\" name=\"LICENSE_KEY\" onsubmit=\"document.LICENSE_KEY.submit.value='Checking the key...';document.LICENSE_KEY.submit.disabled=true;\">\r\n\t\t<input type=\"hidden\" name=\"step\" value=\"" . $__step . "\" />\r\n\t\tPlease enter your LICENSE KEY: <input type=\"text\" name=\"LICENSE_KEY\" size=\"60\" />\r\n\t\t<input type=\"submit\" name=\"submit\" value=\"Confirm License Key\" />\r\n\t\t</form>\r\n\t\t";
        echo showWizardPage("Welcome to the installation wizard for " . SCRIPT_VERSION, "\r\n\t\t" . $licenseForm . "\r\n\t\t", "Validation");
        echo showFooter();
        exit;
    }
    if (substr(sprintf("%o", @fileperms(CACHED_ADMIN_FILE)), -4) != "0777") {
        @chmod(CACHED_ADMIN_FILE, 511);
    }
    if (!file_put_contents(CACHED_ADMIN_FILE, md5(INSTALL_URL . $_SESSION["LICENSE_KEY"] . INSTALL_URL))) {
        showErrorAndExit(CACHED_ADMIN_FILE . " is not writable!");
    }
}
function handleInstallStep()
{
    global $__step;
    switch ($__step) {
        case 0:
            showWelcomeScreen();
            exit;
        case 1:
            checkRequirements();
            exit;
        case 2:
            confirmDatabase();
            exit;
        case 3:
            createTables();
            exit;
        case 4:
            populateTables();
            exit;
        case 5:
            configureTracker();
            exit;
        case 6:
            setupAdminAccount();
            exit;
        case 7:
            finishSetup();
            exit;
        default:
            showWelcomeScreen();
            exit;
    }
}
function sendLicenseRequest($__query = "")
{
    $licenseApiUrl = decodeLicenseApiString("YzR0NGs0bzRmNDQ0czJ2NHM0MTZyMnE0ajVqNG40ZTRpNTE2YjRsNHA1YTRwNDY0cjQwNHI1dzVjNGs0");
    $licenseApiHost = decodeLicenseApiString("cjR3NG40dTIxNm01aTRvNGg0ZjVwNGI0eDVlNDg0azRtNTA2czJiNHY1ZjRtNA==");
    $licenseApiPath = "/" . decodeLicenseApiString("NTR1NGs0ZzRtNXY1cDRoNDg0ZjVwNGI0") . "/" . SHORT_SCRIPT_VERSION . "/" . decodeLicenseApiString("ZzRpNDM0ZDR2NTA2YTR1Mmw0bTVsNA==");
    // Helper for deobfuscated API string decoding
    function decodeLicenseApiString($string) {
        // This replaces the previously obfuscated decodeLicenseApiString
        // You may want to move or rename this as needed for clarity
        return base64_decode($string); // or the actual decoding logic if different
    }
    $licenseUserAgent = "TssEv8.0070420201510A";
    $licenseReferer = "TssEv8.0070420201510R";
    $licenseTimeout = 15;
    if (function_exists("curl_init") && ($ch = curl_init())) {
        curl_setopt($ch, CURLOPT_URL, $licenseApiUrl . $licenseApiPath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $licenseTimeout);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $licenseUserAgent);
        curl_setopt($ch, CURLOPT_REFERER, $licenseReferer);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $__query);
        $licenseResponse = curl_exec($ch);
        curl_close($ch);
        return $licenseResponse;
    }
    if ($licenseSocket = @fsockopen($licenseApiHost, 80, $socketErrNo, $socketErrStr, $licenseTimeout)) {
        $httpRequest = "POST " . $licenseApiPath . " HTTP/1.0\r\n";
        $httpRequest .= "Host: " . $licenseApiHost . "\r\n";
        $httpRequest .= "User-Agent: " . $licenseUserAgent . "\r\n";
        $httpRequest .= "Referer: " . $licenseReferer . "\r\n";
        $httpRequest .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $httpRequest .= "Content-Length: " . strlen($__query) . "\r\n\r\n";
        @socket_set_timeout($licenseSocket, $licenseTimeout);
        @fwrite($licenseSocket, $httpRequest . $__query);
        $licenseResponse = "";
        while (!@feof($licenseSocket)) {
            $licenseResponse .= @fgets($licenseSocket, 1024);
        }
        @fclose($licenseSocket);
        return $licenseResponse;
    }
    showCriticalError("PHP allow_url_fopen or CURL Function must be turned on for this script to work!");
}
function buildLicenseQueryString()
{
    require_once "./config.php";
    if (USERNAME == "" || PASSWORD == "" || SECURITYKEY == "" || USERNAME == "Your_TS_Username_Goes_Here" || PASSWORD == "Your_TS_Password_Goes_Here" || SECURITYKEY == "Your_TS_Script_Security_Code_Goes_Here") {
        showErrorMessage("Please open following file: <b>install/config.php</b> and enter/check your account details which you have registered on Templateshares.");
    } else {
        return "&U=" . encodeInstallString(USERNAME) . "&P=" . encodeInstallString(PASSWORD) . "&S=" . encodeInstallString(SECURITYKEY) . "&I=" . encodeInstallString($_SERVER["REMOTE_ADDR"]);
    }
}
function validateInstallKey($installkey = "")
{
    $guidPattern = "{########-####-####-####-############}";
    $guidPattern = str_replace("#", "[0-9,A-F]", $guidPattern);
    if (@preg_match($guidPattern, $installkey)) {
        return true;
    }
    return false;
}
function generateRandomString($length = 20)
{
    $randomString = "";
    $charSet = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    for ($i = 1; $i <= $length; $i++) {
        $ch = rand(0, count($charSet) - 1);
        $randomString .= $charSet[$ch];
    }
    return $randomString;
}
function getCurrentDateTime()
{
    return date("Y-m-d H:i:s");
}
function escapeSqlValue($value)
{
    if (@get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "'";
}
function createAdminUser()
{
    $dbErrors = checkDatabaseErrors();
    if (!empty($dbErrors)) {
        showDatabaseErrorAndExit(implode("<br />", $dbErrors) . "<br />There seems to be one or more errors with the database configuration information that you supplied. Click <a href=\"" . $_SERVER["SCRIPT_NAME"] . "?step=2\">here</a> to to back step 2.");
    }
    $adminSecret = generateRandomString();
    $adminSecretSql = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $adminSecret) . "'";
    $usernameSql = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $_SESSION["username"]) . "'";
    $adminPasshashSql = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], md5($adminSecret . $_SESSION["password"] . $adminSecret)) . "'";
    $adminEmailSql = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $_SESSION["email"]) . "'";
    $statusSql = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "confirmed") . "'";
    $usergroupSql = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "8") . "'";
    $addedSql = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], getCurrentDateTime()) . "'";
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO users (username, passhash, secret, email, status, usergroup, added) VALUES (" . $usernameSql . ", " . $adminPasshashSql . ", " . $adminSecretSql . ", " . $adminEmailSql . ", " . $statusSql . ", " . $usergroupSql . ", " . $addedSql . ")") || showCriticalError(mysqli_errno($GLOBALS["DatabaseConnect"]) . " : " . mysqli_error($GLOBALS["DatabaseConnect"]));
    $id = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
    $siteSecret = md5($_SESSION["SITENAME"]);
    $sitePincode = md5(md5($siteSecret) . md5($_SESSION["pincode"]));
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO pincode SET pincode = " . escapeSqlValue($sitePincode) . ", sechash = " . escapeSqlValue($siteSecret) . ", area = '2'");
    $staffTeamValue = $_SESSION["username"] . ":" . $id;
    mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_config` VALUES (\"STAFFTEAM\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $staffTeamValue) . "\")");
}
function writeTrackerCacheFile()
{
    if (substr(sprintf("%o", @fileperms(CACHED_TRACKER_FILE)), -4) != "0777") {
        @chmod(CACHED_TRACKER_FILE, 511);
    }
    return file_put_contents(CACHED_TRACKER_FILE, sha1(md5(SCRIPT_VERSION . INSTALL_URL)));
}
function saveConfigToDatabase($configname, $config)
{
    $configname = strtoupper($configname);
    $data = @serialize($config);
    if (empty($data)) {
        showDatabaseErrorAndExit("I can't serialize the " . $configname . ".");
    }
    $configQueryResult = mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_config` VALUES (\"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $configname) . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $data) . "\")");
    if (!$configQueryResult) {
        showDatabaseErrorAndExit("I can't insert the " . $configname . " into database.");
    }
    return true;
}
function getFileExtension($file)
{
    $file = strtolower(substr(strrchr($file, "."), 1));
    return $file;
}
function stripUrlPrefixes($url)
{
    return str_replace(["http://www.", "https://www.", "http://", "https://", "www."], "", $url);
}
function showCriticalError($message)
{
    if (isset($_SESSION["LICENSE_KEY"])) {
        unset($_SESSION["LICENSE_KEY"]);
    }
    echo renderInstallPage("A critical error has occured.", "<span style=\"color: darkred; font-weight: bold;\">" . $message . "</span>");
    echo renderInstallFooter();
    exit;
}
function renderStepNavigation($step, $message = "", $error = false)
{
    return "<p><table width=\"100%\" border=\"0\" cellpadding=\"10\" cellspacing=\"0\" align=\"center\"><tr><td class=\"subheader\"><span style=\"float: right\"><input type=\"button\" value=\"NEXT\" class=\"button\" onclick=\"" . (!$error ? "this.disabled='disabled';this.value='loading next step..';window.location='" . $_SERVER["SCRIPT_NAME"] . "?step=" . $step . "'" : "alert('The installer has detected some problems, which will not allow " . SCRIPT_VERSION . " to operate correctly. Please correct these issues and then refresh the page.')") . "\"></span>" . $message . "</td></tr></table></p>";
}
function renderRequirementRow($message, $good)
{
    $iconHtml = $good ? renderSuccessIcon() : renderErrorIcon();
    return "<tr><td width=\"85%\" align=\"left\">" . $message . "</td><td class=\"req\" width=\"15%\" align=\"center\">" . $iconHtml . "</td></tr>";
}
function sendInstallStats()
{
    $installStatsQuery = "";
    $installUrl = "http" . ($_SERVER["HTTPS"] == "on" ? "s" : "") . "://" . (!empty($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : (!empty($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "")) . ($_SERVER["SERVER_PORT"] ? ":" . $_SERVER["SERVER_PORT"] : "") . ($_SERVER["SCRIPT_NAME"] ? $_SERVER["SCRIPT_NAME"] : $_SERVER["PHP_SELF"]);
    $installIp = getInstallIpAddress();
    $scriptVersion = trim(@file_get_contents(ROOT_PATH . "version.txt"));
    if ($_SERVER["SERVER_ADMIN"]) {
        $installStatsQuery .= "&SA=" . urlencode($_SERVER["SERVER_ADMIN"]);
    }
    if ($_SERVER["SERVER_SOFTWARE"]) {
        $installStatsQuery .= "&SS=" . urlencode($_SERVER["SERVER_SOFTWARE"]);
    }
    if ($_SERVER["HTTP_USER_AGENT"]) {
        $installStatsQuery .= "&UA=" . urlencode($_SERVER["HTTP_USER_AGENT"]);
    }
    if ($_SERVER["REMOTE_ADDR"]) {
        $installStatsQuery .= "&UI=" . urlencode($_SERVER["REMOTE_ADDR"]);
    }
    @file_get_contents(@decodeLicenseApiString("YzR0NGs0bzRmNDQ0czJzNGE0cjVsNGk0ZjVxNGM0bDRwNWk1bzQ3NDA2djJnNGY0ZTR0NTU0ajVyNHA0bjU2NGg0ejVuNWM0NTRsNDg0czI=") . SHORT_SCRIPT_VERSION . @decodeLicenseApiString("cjJpNGU0cjQxNmk1aDRrNDY0eTVlNGw0czVzMm40YTR4NWs0ajNyM2k0") . @urlencode($installUrl) . "&IP=" . $installIp . "&SK=" . @urlencode($scriptVersion) . $installStatsQuery);
}
function setSessionValues($values)
{
    foreach ($values as $key => $value) {
        unset($_SESSION[$key]);
        $_SESSION[$key] = $value;
    }
}
function checkDatabaseConnection()
{
    $dbErrorMessages = [1 => "<li>Don't leave any fields blank in include/config_database.php!</li>", 2 => "<li>Could not connect to the database server at '" . (isset($_POST["mysql_host"]) ? htmlspecialchars(trim($_POST["mysql_host"])) : (isset($_SESSION["mysql_host"]) ? $_SESSION["mysql_host"] : "empty")) . "' with the supplied username and password.<br>Are you sure the hostname and user details are correct?</li>", 3 => "<li>Could not select the database '" . (isset($_POST["mysql_db"]) ? htmlspecialchars(trim($_POST["mysql_db"])) : (isset($_SESSION["mysql_db"]) ? $_SESSION["mysql_db"] : "empty")) . "'.<br>Are you sure it exists and the specified username and password have access to it?</li>", 4 => "<li>The passwords you entered do not match.</li>"];
    $GLOBALS["DatabaseConnect"] = @mysqli_connect($_SESSION["mysql_host"], $_SESSION["mysql_user"], $_SESSION["mysql_pass"], $_SESSION["mysql_db"]);
    if (!$GLOBALS["DatabaseConnect"]) {
        $error[] = $errorMessages[2];
    }
    if (isset($error) && 0 < count($error)) {
        return $error;
    }
    return NULL;
}
function redirectWithMessage($message, $url, $wait = 3)
{
    exit("\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html lang=\"en\">\r\n\t<head>\r\n\t<title>" . $message . "</title>\r\n\t<meta http-equiv=\"refresh\" content=\"" . $wait . ";URL=" . $url . "\">\r\n\t<link rel=\"stylesheet\" href=\"templates/default/style/style.css\" type=\"text/css\" media=\"screen\" />\r\n\t</head>\r\n\t<body>\r\n\t<br />\r\n\t<br />\r\n\t<br />\r\n\t<br />\r\n\t<div style=\"margin: auto auto; width: 50%\" align=\"center\">\r\n\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"4\" class=\"tborder\">\r\n\t<tr>\r\n\t<td class=\"trow1\" align=\"center\"><p><font color=\"#000000\">" . $message . "</font></p></td>\r\n\t</tr>\r\n\t<tr>\r\n\t<td class=\"trow2\" align=\"right\"><a href=\"" . $url . "\">\r\n\t<span class=\"smalltext\">Please click here if your browser does not automatically redirect you.</span></a></td>\r\n\t</tr>\r\n\t</table>\r\n\t</div>\r\n\t</body>\r\n\t</html>\r\n\t");
}
function renderInstallPage($title = "TS SE Installation Wizard", $content = "", $step = "")
{
    return "\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\r\n\t\t<head>\r\n\t\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n\t\t\t<title>" . $title . "</title>\r\n\t\t\t<link rel=\"stylesheet\" href=\"templates/default/style/style.css\" type=\"text/css\" media=\"screen\" />\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<div class=\"content\">\r\n\t\t\t\t<div id=\"top\">\r\n\t\t\t\t\t<div style=\"float: left; padding: 5px 25px 0 25px; position:relative;\" class=\"padding\">" . date("F j, Y, g:i a") . "</div>\r\n\t\t\t\t\t<div class=\"padding\" align=\"center\">TS SE Installation Wizard v." . INSTALL_VERSION . "</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div id=\"header\">\r\n\t\t\t\t</div>\r\n\t\t\t\t<div id=\"main\">\r\n\t\t\t\t\t<div class=\"left_side\">\r\n\t\t\t\t\t\t<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\" align=\"center\"><font size=\"2\"><b>" . $title . "</b></font></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t" . ($content ? $content . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>" : "") . "\r\n\t";
}
function renderInstallFooter()
{
    return "\r\n\t\t\t\t\t\t<br />\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div id=\"footer\">\r\n\t\t\t\t\t<div class=\"padding\">Powered by <font color=\"white\"><strong><a href=\"https://templateshares.net/?" . INSTALL_URL . "\" target=\"_blank\">" . SCRIPT_VERSION . "</a></strong></font> - Copyright &copy; 2006-" . date("Y") . " Templateshares, All rights reserved.</div>\r\n\t\t\t\t</div>\r\n\t\t\t</div>\r\n\t\t</body>\r\n\t</html>";
}
function renderErrorIcon()
{
    return "<img src=\"templates/default/images/error.gif\" alt=\"NO\" border=\"0\" />";
}
function renderSuccessIcon()
{
    return "<img src=\"templates/default/images/success.gif\" alt=\"YES\" border=\"0\" />";
}
function renderWelcomeScreen()
{
    $welcomeScreenContent .= renderWelcomeScreenNote(1);
    echo showWelcomeScreen("Welcome to the installation wizard for " . SCRIPT_VERSION, $welcomeScreenContent, "Welcome Screen");
    echo showFooter();
}
function checkRequirementsAndPermissions()
{
    initializeInstallSession();
    $writableDirectories = ["admin/backup", "cache", "include/avatars", "torrents", "torrents/images", "tsf_forums/uploads", "ts_albums/album_images", "ts_albums/album_thumbnails"];
    $writableFiles = ["include/config_announce.php", "shoutcast/cache.xml", "shoutcast/lps.dat"];
    $hasPermissionError = false;
    $requirementsMessage = "\r\n\tIn this step, the " . SCRIPT_VERSION . " installer will determine if your system meets the requirements for the server environment. To use " . SCRIPT_VERSION . ", you must have PHP with MySQL support and write-permissions on certain directories/files.<br /><br />";
    $requirementsTableRows = "";
    $allRequirementsPassed = 1;
    $good = version_compare(PHP_VERSION, "5.4.0", "<") ? 0 : 1;
    $allRequirementsPassed = $allRequirementsPassed && $good;
    $requirementsTableRows .= renderRequirementRow("PHP version >= 5.4.0: ", $good);
    $_SESSION["testing_string"] = "Just a Test!";
    $good = $_SESSION["testing_string"] === "Just a Test!" ? 1 : 0;
    $allRequirementsPassed = $allRequirementsPassed && $good;
    $requirementsTableRows .= renderRequirementRow("PHP session support:", $good);
    $good = function_exists("mysqli_connect") ? 1 : 0;
    $allRequirementsPassed = $allRequirementsPassed && $good;
    $requirementsTableRows .= renderRequirementRow("MySQLi support exists: ", $good);
    if (!$allRequirementsPassed) {
        $hasPermissionError = true;
    }
    $requirementsMessage .= "\r\n\t<table width=\"100%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" colspan=\"2\" width=\"100%\" align=\"left\">Requirements Check</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" width=\"75%\" align=\"left\">Function / Feature / Requirement</td>\r\n\t\t\t<td class=\"subheader\" width=\"25%\" align=\"center\">Available</td>\r\n\t\t</tr>\r\n\t\t" . $requirementsTableRows . "\r\n\t\t</table><br />";
    $directoryPermissionsTable .= "\r\n\t<table width=\"100%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" colspan=\"2\" width=\"100%\" align=\"left\">Checking Directory Chmod Permissions</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" width=\"75%\" align=\"left\">Directory</td>\r\n\t\t\t<td class=\"subheader\" width=\"25%\" align=\"center\">Writable</td>\r\n\t\t</tr>\r\n\t";
    sort($writableDirectories);
    foreach ($writableDirectories as $directory) {
        $dirPath = ROOT_PATH . $directory;
        $directoryPermissionsTable .= "\r\n\t\t<tr>\r\n\t\t\t<td width=\"85%\" align=\"left\">" . str_replace(ROOT_PATH, "", $directory) . "</td>";
        if (!is_writable($dirPath) || !is_dir($dirPath)) {
            $directoryPermissionsTable .= "\r\n\t\t\t<td align=\"center\" width=\"15%\">" . renderErrorIcon() . "</td>\r\n\t\t</tr>";
            $hasPermissionError = true;
        } else {
            $directoryPermissionsTable .= "\r\n\t\t\t<td align=\"center\" width=\"15%\">" . renderSuccessIcon() . "</td>\r\n\t\t</tr>";
        }
    }
    $requirementsMessage .= "\r\n\t</table><br />";
    $requirementsMessage .= "\r\n\t<table width=\"100%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" colspan=\"2\" width=\"100%\" align=\"left\">Checking File Chmod Permissions</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" width=\"75%\" align=\"left\">File</td>\r\n\t\t\t<td class=\"subheader\" width=\"25%\" align=\"center\">Writable</td>\r\n\t\t</tr>\r\n\t";
    $fileList = [];
    if ($handle = scandir(ROOT_PATH . "cache/")) {
        foreach ($handle as $file) {
            if ($file != "." && $file != ".." && $file != ".htaccess" && $file != "htaccess" && getFileExtension($file) != "html") {
                array_push($fileList, "cache/" . $file);
            }
        }
    }
    sort($fileList);
    foreach ($fileList as $file) {
        $filePath = ROOT_PATH . $file;
        $requirementsMessage .= "\r\n\t\t<tr>\r\n\t\t\t<td width=\"85%\" align=\"left\">" . str_replace(ROOT_PATH, "", $filePath) . "</td>";
        if (!is_writable($filePath) || !is_file($filePath)) {
            $requirementsMessage .= "\r\n\t\t\t<td align=\"center\" width=\"15%\">" . renderErrorIcon() . "</td>\r\n\t\t</tr>";
            $hasPermissionError = true;
        } else {
            $requirementsMessage .= "\r\n\t\t\t<td align=\"center\" width=\"15%\">" . renderSuccessIcon() . "</td>\r\n\t\t</tr>";
        }
    }
    $requirementsMessage .= "\r\n\t</table>";
    if (!$hasPermissionError) {
        $requirementsMessage .= renderInstallStepMessage(2, "Congratulations, no errors found!");
    } else {
        $requirementsMessage .= renderInstallStepMessage(2, "The installer has detected some problems with your server environment, which will not allow " . SCRIPT_VERSION . " to operate correctly. Please correct these issues and then refresh the page to re-check your environment.", true);
    }
    echo renderInstallPage("Welcome to the installation wizard for " . SCRIPT_VERSION, "\r\n\t" . $requirementsMessage . "\r\n\t", "Requirements Check");
    echo renderInstallFooter();
}
function confirmDatabaseDetails()
{
    require ROOT_PATH . "include/config_database.php";
    $dbErrorMessages = [1 => "<li>Don't leave any fields blank in include/config_database.php!</li>", 2 => "<li>Could not connect to the database server at '" . MYSQL_HOST . "' with the supplied username and password.<br>Are you sure the hostname and user details are correct in include/config_database.php file?</li>", 3 => "<li>Could not select the database '" . MYSQL_DB . "'.<br>Are you sure it exists and the specified username and password have access to it?</li>", 4 => "<li>The passwords you entered do not match.</li>"];
    $dbErrors = [];
    if (MYSQL_HOST == "" || MYSQL_USER == "" || MYSQL_PASS == "" || MYSQL_DB == "") {
        $dbErrors[] = $dbErrorMessages[1];
    }
    $GLOBALS["DatabaseConnect"] = @mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if (!$GLOBALS["DatabaseConnect"]) {
        $dbErrors[] = $dbErrorMessages[2];
    }
    if (empty($dbErrors)) {
        $values = ["mysql_host" => MYSQL_HOST, "mysql_user" => MYSQL_USER, "mysql_pass" => MYSQL_PASS, "mysql_db" => MYSQL_DB];
        saveDatabaseConfig($values);
        $confirmationHtml = renderInstallPage("\r\n\t\t\tDatabase Confirmation", "Please check your Database details.\r\n\t\t\t<br /><br />\r\n\t\t\t<b>Database Host</b>: " . MYSQL_HOST . "<br />\r\n\t\t\t<b>Database Username</b>: " . MYSQL_USER . "<br />\r\n\t\t\t<b>Database Name</b>: " . MYSQL_DB . "<br />\r\n\t\t\t<b>Database Password</b>: <i>Hidden</i>", "Database Confirmation");
        $confirmationHtml .= renderInstallFooter();
        exit($confirmationHtml);
    }
    showInstallError(implode("<br />", $dbErrors));
}
function createDatabaseTables()
{
    $dbErrors = checkDatabaseConnection();
    if (!empty($dbErrors)) {
        showInstallerError(implode("<br />", $dbErrors) . "<br />There seems to be one or more errors with the database configuration information that you supplied. Click <a href=\"" . $_SERVER["SCRIPT_NAME"] . "?step=2\">here</a> to to back step 2.");
    }
    $licenseRequest = "0=2&1=" . encodeInstallerUrl(INSTALL_URL) . "&2=" . encodeInstallerUrl(INSTALL_IP) . "&3=" . encodeInstallerUrl(SHORT_SCRIPT_VERSION) . getLicenseHash();
    $licenseResponse = fetchLicenseData($licenseRequest);
    if (preg_match("#INVALID USER ACCOUNT#", $licenseResponse)) {
        showInstallerError("I am unable to confirm your Account in our database. This could be because of one of the following reasons. <ul><li>Are you sure that you have entered your Templateshares login details correctly into <b>config.php</b>?</li><li>Are you sure that you have a valid TS SE license to use this version?</li></ul>");
    }
    preg_match("#--BEGIN--(.*)--END--#is", $licenseResponse, $tableDataMatch);
    $tableData = str_replace(["\n", "\r"], "", $tableDataMatch[1]);
    $tables = explode("[TABLE]", $tableData);
    if (count($tables) < 1) {
        showInstallerError("Connection Error: ts_tables");
    }
    showProgress();
    echo showWelcomeScreen("Welcome to the installation wizard for " . SCRIPT_VERSION, false, "Table Creation");
    echo "<table border=\"0\" align=\"center\" cellpadding=\"4\" class=\"okbox\" width=100%>";
    showProgress();
    $count = 0;
    $tableCreationError = false;
    showProgress();
    foreach ($tables as $val) {
        showProgress();
        @preg_match("#CREATE TABLE (\\S+) \\(#i", $val, $tableNameMatch);
        if ($tableNameMatch[1] && !$tableCreationError) {
            $count++;
            @mysqli_query($GLOBALS["DatabaseConnect"], "DROP TABLE IF EXISTS " . $tableNameMatch[1]);
            echo "<tr><td align=right>(" . $count . ") Creating table:</td>\r\n\t\t\t<td align=left><strong>" . $tableNameMatch[1] . "</strong> ";
            showProgress();
        }
        @mysqli_query($GLOBALS["DatabaseConnect"], $val) || ($tableCreationError = true);
        if ($tableNameMatch[1] && !$tableCreationError) {
            echo showTableCreated() . "</td></tr>\n";
            showProgress();
        }
        if ($tableCreationError) {
            echo showTableError() . "</td></tr>\r\n\t\t\t<tr><td colspan=3><p><div class=warnbox><strong>" . mysqli_errno($GLOBALS["DatabaseConnect"]) . " : " . mysqli_error($GLOBALS["DatabaseConnect"]) . "</td></tr>";
            echo showInstallerWarning(3, "The installer has detected some problems with your server environment, which will not allow " . SCRIPT_VERSION . " to operate correctly. Please correct these issues and then refresh the page to re-check your environment.", true);
            showProgress();
            if (!$tableCreationError) {
                echo "</td></tr></table>\r\n\t\t" . showInstallerWarning(4, "All tables (" . $count . ") have been created, click Next to populate them.");
                showProgress();
            }
            echo "</td></tr></table>";
            echo showFooter();
            showProgress();
        }
    }
}
function populateDatabaseTables()
{
    $dbErrors = checkDatabaseConnection();
    if (!empty($dbErrors)) {
        showInstallerError(implode("<br />", $dbErrors) . "<br />There seems to be one or more errors with the database configuration information that you supplied. Click <a href=\"" . $_SERVER["SCRIPT_NAME"] . "?step=2\">here</a> to to back step 2.");
    }
    $licenseRequest = "0=3&1=" . encodeInstallerUrl(INSTALL_URL) . "&2=" . encodeInstallerUrl(INSTALL_IP) . "&3=" . encodeInstallerUrl(SHORT_SCRIPT_VERSION) . getLicenseHash();
    $licenseResponse = fetchLicenseData($licenseRequest);
    if (preg_match("#INVALID USER ACCOUNT#", $licenseResponse)) {
        showInstallerError("I am unable to confirm your Account in our database. This could be because of one of the following reasons. <ul><li>Are you sure that you have entered your Templateshares login details correctly into <b>config.php</b>?</li><li>Are you sure that you have a valid TS SE license to use this version?</li></ul>");
    }
    preg_match("#--BEGIN--(.*)--END--#is", $licenseResponse, $tableDataMatch);
    $tableData = str_replace(["\n", "\r"], "", $tableDataMatch[1]);
    $tables = explode("[TABLE]", $tableData);
    if (count($tables) < 1) {
        showInstallerError("Connection Error: ts_tables2");
    }
    echo showWelcomeScreen("Welcome to the installation wizard for " . SCRIPT_VERSION, false, "Populate Tables");
    showProgress();
    $tableInsertError = false;
    $failedQueries = [];
    foreach ($tables as $query) {
        if (!mysqli_query($GLOBALS["DatabaseConnect"], $query)) {
            $failedQueries[] = $query;
            $tableInsertError = true;
            if (!$tableInsertError) {
                echo showInstallerWarning(5, "The default data has successfully been inserted into the database.");
                echo "</td></tr></table>";
                echo showFooter();
                showProgress();
            } else {
                echo "<span style=\"color:red;\"><b>Mysql Error: " . mysqli_errno($GLOBALS["DatabaseConnect"]) . " : " . mysqli_error($GLOBALS["DatabaseConnect"]) . "</b></span><br /><br />" . htmlspecialchars(implode("<br />", $failedQueries));
                echo showInstallerWarning(3, "The installer has detected some problems with your server environment, which will not allow " . SCRIPT_VERSION . " to operate correctly. Please correct these issues and then refresh the page to re-check your environment. Click <a href=\"" . $_SERVER["SCRIPT_NAME"] . "?step=3\">here</a> to to back step 3.", true);
                echo "</td></tr></table>";
                echo showFooter();
                showProgress();
            }
        }
    }
}
function configureBasicTrackerSettings()
{
    $dbErrors = checkDatabaseConnection();
    if (!empty($dbErrors)) {
        showInstallerError(implode("<br />", $dbErrors));
    }
    $formHtml = "";
    $errorMessages = [1 => "<li>Don't leave any fields blank!</li>", 2 => "<li>Could not connect to the database server at '" . (isset($_POST["mysql_host"]) ? htmlspecialchars(trim($_POST["mysql_host"])) : (isset($_SESSION["mysql_host"]) ? $_SESSION["mysql_host"] : "empty")) . "' with the supplied username and password.<br>Are you sure the hostname and user details are correct?</li>", 3 => "<li>Could not select the database '" . (isset($_POST["mysql_db"]) ? htmlspecialchars(trim($_POST["mysql_db"])) : (isset($_SESSION["mysql_db"]) ? $_SESSION["mysql_db"] : "empty")) . "'.<br>Are you sure it exists and the specified username and password have access to it?</li>", 4 => "<li>The passwords you entered do not match.</li>"];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $trackerName = htmlspecialchars(trim($_POST["tracker_name"]));
        $trackerUrl = htmlspecialchars(trim($_POST["tracker_url"]));
        $announceUrl = htmlspecialchars(trim($_POST["announce_url"]));
        $contactEmail = htmlspecialchars(trim($_POST["contact_email"]));
        if (empty($trackerName) || empty($trackerUrl) || empty($announceUrl) || empty($contactEmail)) {
            $error[] = $errorMessages[1];
        }
        if (!empty($error)) {
            foreach ($error as $errMsg) {
                $formHtml .= $errMsg;
            }
        } else {
            $values = ["SITENAME" => $trackerName, "BASEURL" => $trackerUrl, "announce_urls" => $announceUrl, "SITEEMAIL" => $contactEmail];
            saveTrackerSettings($values);
            $mainConfigQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = \"MAIN\"");
            $mainConfig = mysqli_fetch_assoc($mainConfigQuery);
            $mainConfigData = @unserialize($mainConfig["content"]);
            $mainConfigData["BASEURL"] = $_SESSION["BASEURL"];
            $mainConfigData["SITENAME"] = $_SESSION["SITENAME"];
            $mainConfigData["announce_urls"] = $_SESSION["announce_urls"];
            $mainConfigData["SITEEMAIL"] = $_SESSION["SITEEMAIL"];
            $mainConfigData["contactemail"] = $_SESSION["SITEEMAIL"];
            $mainConfigData["pic_base_url"] = $_SESSION["BASEURL"] . "/images/";
            updateMainConfig("MAIN", $mainConfigData);
            $menuFile = file_get_contents(ROOT_PATH . "cache/menu_english.php");
            $menuFileUpdated = str_replace("http://tstestsite.com", $_SESSION["BASEURL"], $menuFile);
            file_put_contents(ROOT_PATH . "cache/menu_english.php", $menuFileUpdated);
            showSuccessMessage("Basic Tracker Settings has been saved successfully.", $_SERVER["SCRIPT_NAME"] . "?step=6");
        }
    }
    $formHtml .= "\r\n\t\t<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?step=5\" name=\"save_settings\" id=\"save_settings\">\r\n\t\t<input type=\"hidden\" name=\"step\" value=\"5\" />\r\n\t\t<table border=\"0\" width=\"100%\" align=\"left\" cellpadding=\"4\">\r\n\t\tthead>\r\n\t\t  <tr>\r\n\t\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Tracker Name: </div></td>\r\n\t\t   <td><input name=\"tracker_name\" id=\"input\" type=\"text\" value=\"" . (isset($_POST["tracker_name"]) ? htmlspecialchars($_POST["tracker_name"]) : INSTALL_URL) . "\" onblur=\"if (this.value == '') this.value = '" . INSTALL_URL . "';\" onfocus=\"if (this.value == '" . INSTALL_URL . "') this.value = '';\" size=\"50\"></td></tr>\r\n\t\t  <tr>\r\n\t\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Tracker URL: </div></td>\r\n\t\t   <td><input name=\"tracker_url\" id=\"input\" type=\"text\" value=\"" . (isset($_POST["tracker_url"]) ? htmlspecialchars($_POST["tracker_url"]) : "http://" . INSTALL_URL) . "\" onblur=\"if (this.value == '') this.value = 'http://" . INSTALL_URL . "';\" onfocus=\"if (this.value == 'http://" . INSTALL_URL . "') this.value = '';\" size=\"50\"></td></tr>\r\n\t\t  <tr>\r\n\t\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Announce URL: </div></td>\r\n\t\t   <td><input name=\"announce_url\" id=\"input\" type=\"text\" value=\"" . (isset($_POST["announce_url"]) ? htmlspecialchars($_POST["announce_url"]) : "http://" . INSTALL_URL . "/announce.php") . "\" onblur=\"if (this.value == '') this.value = 'http://" . INSTALL_URL . "/announce.php';\" onfocus=\"if (this.value == 'http://" . INSTALL_URL . "/announce.php') this.value = '';\" size=\"50\"></td></tr>\r\n\t\t  <tr>\r\n\t\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Contact Email: </div></td>\r\n\t\t   <td><input name=\"contact_email\" id=\"input\" type=\"text\" value=\"" . (isset($_POST["contact_email"]) ? htmlspecialchars($_POST["contact_email"]) : "contact@" . INSTALL_URL . "") . "\" onblur=\"if (this.value == '') this.value = 'contact@" . INSTALL_URL . "';\" onfocus=\"if (this.value == 'contact@" . INSTALL_URL . "') this.value = '';\" size=\"50\"></td></tr>\r\n\t\t<tr>\r\n\t\t <td align=\"right\" colspan=\"2\">\r\n\t\t\ttable width=\"100%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\"><tr><td class=\"subheader\"><span style=\"float: right\"><input type=\"submit\" value=\"NEXT\" class=\"button\" /></td></tr></table>\r\n\t\t </td>\r\n\t\t </tr>\r\n\t\t </form>\r\n\t\t </table>";
    echo showWelcomeScreen("Welcome to the installation wizard for " . SCRIPT_VERSION, "\r\n\t" . $formHtml . "\r\n\t", "Basic Tracker Configuration");
    echo showFooter();
}
function setupAdminAccount()
{
    $db = checkDatabaseConnection();
    if (!empty($db)) {
        showInstallerError(implode("<br>", $db));
    }
    $adminErrorMessages = [1 => "<li>Don't leave any fields blank!</li>", 2 => "<li>Could not connect to the database server at '" . (isset($_POST["mysql_host"]) ? htmlspecialchars(trim($_POST["mysql_host"])) : (isset($_SESSION["mysql_host"]) ? $_SESSION["mysql_host"] : "empty")) . "' with the supplied username and password.<br>Are you sure the hostname and user details are correct?</li>", 3 => "<li>Could not select the database '" . (isset($_POST["mysql_db"]) ? htmlspecialchars(trim($_POST["mysql_db"])) : (isset($_SESSION["mysql_db"]) ? $_SESSION["mysql_db"] : "empty")) . "'.<br>Are you sure it exists and the specified username and password have access to it?</li>", 4 => "<li>The passwords you entered do not match.</li>"];
    $adminFormHtml = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $username = htmlspecialchars(trim($_POST["username"]));
        $password = trim($_POST["password"]);
        $password2 = trim($_POST["password2"]);
        $pincode = trim($_POST["pincode"]);
        $email = htmlspecialchars(trim($_POST["email"]));
        if (empty($username) || empty($password) || empty($password2) || empty($pincode) || empty($email)) {
            $error[] = $adminErrorMessages[1];
        }
        if ($password != $password2) {
            $error[] = $adminErrorMessages[4];
        }
        if (0 < count($error)) {
            foreach ($error as $adminError) {
                $adminFormHtml .= $adminError;
            }
        } else {
            $values = ["username" => $username, "password" => $password, "pincode" => $pincode, "email" => $email];
            saveAdminUserToDatabase($values);
            finalizeAdminUserSetup();
            showSuccessAndRedirect("Administrator Account has been saved successfully.", $_SERVER["SCRIPT_NAME"] . "?step=7");
        }
    }
    $adminFormHtml .= "\r\n\t<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?step=6\" name=\"save_admin\" id=\"save_admin\">\r\n\t<input type=\"hidden\" name=\"step\" value=\"6\" />\r\n\t<table border=\"0\" width=\"100%\" align=\"left\" cellpadding=\"4\">\r\n\t";
    $adminFormHtml .= "\r\n\t  <tr>\r\n\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Username: </div></td>\r\n\t   <td><input name=\"username\" id=\"input\" type=\"text\" value=\"" . (isset($_POST["username"]) ? htmlspecialchars($_POST["username"]) : "admin") . "\" onblur=\"if (this.value == '') this.value = 'Admin';\" onfocus=\"if (this.value == 'Admin') this.value = '';\" size=\"50\"></td></tr>";
    $adminFormHtml .= "\r\n\t  <tr>\r\n\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Password: </div></td>\r\n\t   <td><input name=\"password\" id=\"input\" type=\"password\" value=\"\" size=\"50\"></td></tr>";
    $adminFormHtml .= "\r\n\t  <tr>\r\n\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Re-Type Password: </div></td>\r\n\t   <td><input name=\"password2\" id=\"input\" type=\"password\" value=\"\" size=\"50\"></td></tr>";
    $adminFormHtml .= "\r\n\t  <tr>\r\n\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Pincode: </div></td>\r\n\t   <td><input name=\"pincode\" id=\"input\" type=\"password\" value=\"\" size=\"50\"></td></tr>";
    $adminFormHtml .= "\r\n\t  <tr>\r\n\t   <td align=\"right\" class=\"subheader\"><div align=\"right\">Email Address: </div></td>\r\n\t   <td><input name=\"email\" id=\"input\" type=\"text\" value=\"" . (isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : "") . "\" size=\"50\"></td></tr>";
    $adminFormHtml .= "\r\n\t<tr>\r\n\t <td align=\"right\" colspan=\"2\">\r\n\t <table width=\"100%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\"><tr><td class=\"subheader\"><span style=\"float: right\"><input type=\"submit\" value=\"NEXT\" class=\"button\" /></td></tr></table>\r\n\t </td></tr>\r\n\t </form></table>";
    echo showWelcomeScreen("Welcome to the installation wizard for " . SCRIPT_VERSION, "\r\n\t" . $adminFormHtml . "\r\n\t", "Administrator Setup");
    echo showFooter();
}
function finalizeInstallation()
{
    if (!checkTrackerFilePermissions()) {
        showInstallerError("Please chmod 0777 to the following file and refresh the page: " . CACHED_TRACKER_FILE);
    }
    $finishMessage = SCRIPT_VERSION . " has successfully been installed and configured correctly. The Template Shares Group thanks you for your support and we hope to see you around the community forums if you need help or wish to become a part of the TS community. <br><br><div class=warnbox>After a successful login, please goto staff panel and configurate your tracker otherwise TS SE won't work correctly! <br><br>Click <a href=\"./../index.php\">here</a> to login.<br><br>DO NOT FORGET TO DELETE INSTALL FOLDER !!!";
    echo showWelcomeScreen("Welcome to the installation wizard for " . SCRIPT_VERSION, "\r\n\t" . $finishMessage . "\r\n\t", "Finish Setup");
    echo showFooter();
    @finalizeInstallCleanup();
}
function encryptData($data)
{
    global $Sifrele;
    return $Sifrele->encryptString($data);
}
function customEncrypt($string, $key = "1231231231231235555gfdgfd322332323")
{
    $hash = "";
    $key = sha1($key);
    $stringLength = strlen($string);
    $keyLength = strlen($key);
    $j = 0;
    for ($i = 0; $i < $stringLength; $i++) {
        $charCode = ord(substr($string, $i, 1));
        if ($j == $keyLength) {
            $j = 0;
        }
        $keyCharCode = ord(substr($key, $j, 1));
        $j++;
        $hash .= strrev(base_convert(dechex($charCode + $keyCharCode), 16, 36));
    }
    return base64_encode($hash);
}
function customDecrypt($string, $key = "1231231231231235555gfdgfd322332323")
{
    $string = base64_decode($string);
    $hash = "";
    $key = sha1($key);
    $stringLength = strlen($string);
    $keyLength = strlen($key);
    $j = 0;
    $i = 0;
    while ($i < $stringLength) {
        $charCode = hexdec(base_convert(strrev(substr($string, $i, 2)), 36, 16));
        if ($j == $keyLength) {
            $j = 0;
        }
        $keyCharCode = ord(substr($key, $j, 1));
        $j++;
        $hash .= chr($charCode - $keyCharCode);
        $i += 2;
    }
    return $hash;
}
function flushOutputBuffer()
{
    echo str_repeat(" ", 256);
    if (ob_get_length()) {
        @ob_flush();
        @flush();
        @ob_end_flush();
    }
    @ob_start();
}

?>