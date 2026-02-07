<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

// Deobfuscated: SafeModeChecker and LicenseCipher
$SafeModeCheck = new SafeModeChecker();
$LicenseCipher = new LicenseCipher("TSSE8.02020httpstemplateshares.net!");
$__step = isset($_POST["step"]) ? intval($_POST["step"]) : (isset($_GET["step"]) ? intval($_GET["step"]) : 0);
$PREVIOUS_VERSIONS = ["5.6", "5.7", "6.0", "6.1", "6.2", "6.3", "7.0", "7.1", "7.2", "7.3", "7.4", "7.5"];
initErrorReporting();
initSession();
defineScriptConstants();
checkEnvironment();
initUpgradeStep1();
initUpgradeStep2();
initUpgradeStep3();
initUpgradeStep4();
check_license();
initializeInstaller();
class SafeModeChecker
{
    public $ZavaZingo = NULL;
    public $TavaZingo = NULL;
    public $Havai = NULL;
    public $PokeMon = NULL;
    public function __construct()
    {
        if (@ini_get("safe_mode") == 1 || strtolower(@ini_get("safe_mode")) == "on") {
            showCriticalError("Please disable PHP Safe Mode to continue upgrade!");
            exit;
        }
    }
}
class LicenseCipher
{
    public $AnahtarKelime = NULL;
    public function __construct($AK)
    {
        $this->AnahtarKelime = trim($AK);
    }
    public function encode($input)
    {
        $result = "";
        for ($i = 0; $i < strlen($input); $i++) {
            $currentChar = substr($input, $i, 1);
            $keyChar = substr($this->AnahtarKelime, $i % strlen($this->AnahtarKelime) - 1, 1);
            $currentChar = chr(ord($currentChar) + ord($keyChar));
            $result .= $currentChar;
        }
        return urlencode(base64_encode($result));
    }
    public function decode($input)
    {
        $result = "";
        $input = urldecode(base64_decode($input));
        for ($i = 0; $i < strlen($input); $i++) {
            $currentChar = substr($input, $i, 1);
            $keyChar = substr($this->AnahtarKelime, $i % strlen($this->AnahtarKelime) - 1, 1);
            $currentChar = chr(ord($currentChar) - ord($keyChar));
            $result .= $currentChar;
        }
        return $result;
    }
}
function initErrorReporting()
{
    @error_reporting(32759);
    @ini_set("display_errors", 0);
    @ini_set("display_startup_errors", 0);
    @set_time_limit(90);
}
function initSession()
{
    session_name(var_635());
    session_start();
}
function getSessionName()
{
    $serverName = isset($_SERVER["SERVER_NAME"]) && !empty($_SERVER["SERVER_NAME"]) ? trim($_SERVER["SERVER_NAME"]) : (isset($_SERVER["HTTP_HOST"]) && !empty($_SERVER["HTTP_HOST"]) ? trim($_SERVER["HTTP_HOST"]) : "tsse_session");
    return preg_replace("/[^a-zA-Z0-9_]/", "", $serverName) . "_install";
}
function defineScriptConstants()
{
    define(decodeLicenseApiString("aDNyM24zdzNtNHg0bDM3NHYzbjRyM3QzcjRwM3Az"), "8.8");
    define(decodeLicenseApiString("aDNyM24zdzNtNHg0bDM3NHUzMDVsMw=="), !empty($_SERVER["SERVER_NAME"]) ? stripUrlPrefixes($_SERVER["SERVER_NAME"]) : (!empty($_SERVER["HTTP_HOST"]) ? stripUrlPrefixes($_SERVER["HTTP_HOST"]) : ""));
    define(decodeLicenseApiString("aDNyM24zdzNtNHg0bDM3NGkzeTQ="), var_636());
    define(decodeLicenseApiString("czNsM2QzdjNnNTM1bzNyM3QzZDVwM2IzMjVpMw=="), "./");
    define(decodeLicenseApiString("cTNzM2ozdzNnNTE1YTN3M2gz"), "./../");
    define(decodeLicenseApiString("YjNlMzczazNxNHA0NDRkM2QzdjRpM28zZDVnM2szaTNxNA=="), ROOT_PATH . decodeLicenseApiString("NzRhNDM0ZzRtNTQ0NjRjNGk0bjVqNDU0eDViNHE0bDRxNXc1azRvMmw1YTRyNA=="));
    define(decodeLicenseApiString("YjNlMzczazNxNHA0NDR3M3IzajRjM2wzbjRzMzY0YzN1NHg0ZjM="), ROOT_PATH . decodeLicenseApiString("NzRhNDM0ZzRtNTQ0bzR4NG80eTVhNGo0aDU3NGE0YTRtNTM0YTQzNDE2"));
}
function getInstallIpAddress()
{
    $var_228 = "./../cache/ip.srv";
    if (isset($_SERVER["SERVER_ADDR"]) && !empty($_SERVER["SERVER_ADDR"]) && var_637($_SERVER["SERVER_ADDR"])) {
        $ip = $_SERVER["SERVER_ADDR"];
    } else {
        if (isset($_SERVER["LOCAL_ADDR"]) && !empty($_SERVER["LOCAL_ADDR"]) && isValidIP($_SERVER["LOCAL_ADDR"])) {
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
                    if (is_writable("./../cache/ip.srv")) {
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
function stringContains($string, $find)
{
    return strpos($string, $find) === false ? false : true;
}
    $allowedHosts = explode("~~~", "192.168.1.~~~127.0.0~~~localhost~~~templateshares~~~template-shares~~~1tam1ogrenci.com~~~78.159.111.12~~~82.137.61.162~~~82.137.61.162~~~83.99.133.91~~~~~~alien-scene.org~~~91.121.149.102~~~ancientbits.com~~~82.47.208.141~~~angels-torrents.net~~~89.149.255.72~~~arab-peer.org~~~174.121.11.17~~~arabpeer.org~~~188.40.162.120~~~arabsong.org~~~69.72.149.25~~~biotorrents.org~~~~~~bkt.si~~~149.210.145.52~~~blades-heaven.co.uk~~~88.191.26.186~~~blades-heaven.com~~~74.86.40.71~~~chixy.org~~~82.81.156.237~~~ddtorrents.com~~~66.90.109.57~~~demonicsouls.net~~~88.191.35.248~~~destamkroeg.org~~~85.214.110.80~~~dev.bigfangroup.org~~~77.130.134.245~~~deviltorrents.org~~~66.197.138.21~~~filetracker.org~~~216.246.57.130~~~firestar.pl~~~37.187.73.130~~~flash-dragon.co.uk~~~94.23.45.92~~~homemadeporntorrents2.com~~~208.53.143.102~~~iraqigate.org~~~66.49.137.208~~~kinoclub.eu~~~78.47.214.119~~~learnbits.info~~~70.47.114.167~~~leechseed.net~~~216.227.216.220~~~mazikalek.com~~~174.120.105.219~~~mediotekayu.com~~~91.185.194.96~~~mightytunez.com/beta~~~118.210.69.244~~~mojtorrent.com~~~195.246.15.79~~~movietorrents.org~~~95.211.129.88~~~musicgate.org~~~216.104.38.146~~~new.alientorrent.com~~~76.73.5.226~~~omarco.eu~~~91.196.170.205~~~oz708-speeds.info~~~89.149.194.50~~~planetatorrent.cl~~~87.98.221.150~~~ransackedcrew.com~~~88.191.35.248~~~saucytorrents.com~~~85.234.133.165~~~scenedemon.com~~~80.86.83.213~~~seedboxworld.biz~~~62.75.149.199~~~serko.se~~~212.97.132.131~~~shetos.org~~~97.74.121.119~~~sicktorrents.com~~~88.198.53.215~~~speed-xxx.com~~~195.246.15.139~~~speedy-torrents.info~~~184.107.184.106~~~stancamantuirii.ro~~~89.36.134.61~~~swemops.com~~~67.210.100.3~~~tailz.us~~~209.11.245.165~~~test.biotorrents.org~~~~~~the-jedi-knights.info~~~216.245.205.187~~~thedvdclub.no-ip.org~~~192.168.1.30~~~tnt-vision.com~~~86.105.223.222~~~top-balkan.com~~~203.121.68.164~~~top-balkan.net~~~203.121.69.26~~~top-torrent.com~~~212.112.250.157~~~torrents-gate.com~~~69.72.149.25~~~torrents4u.org~~~85.17.145.104~~~torrentsworld.org~~~66.90.109.57~~~tracker.power-on.kiev.ua~~~217.20.163.65~~~underground-addicts.com~~~24.102.56.34~~~vale-date.com~~~69.72.149.25~~~vehd.net~~~178.33.103.17~~~wizzdvd.net~~~195.246.219.4~~~x-releases.org~~~209.44.113.82~~~y-k-m.net~~~174.120.127.92");
    foreach ($allowedHosts as $hostEntry) {
        if (strlen(INSTALL_URL) < 5 || strlen(INSTALL_IP) < 8 || stringContains(INSTALL_URL, $hostEntry) || stringContains(INSTALL_IP, $hostEntry)) {
            showErrorMessage("Sorry, I can not continue due a Critical Error. The error code is: SE1. Please contact us at <a $href = \"https://templateshares.net/?$u = " . urlencode(INSTALL_URL) . "&$i = " . urlencode(INSTALL_IP) . "\">https://templateshares.net/</a>.");
        }
    }
}
function function_328()
{
    if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . decodeLicenseApiString("NTR1NGs0ZzRtNXY1cDRoNDg0ZjVwNGI0") . "/" . SHORT_SCRIPT_VERSION . "/" . decodeLicenseApiString("ZzRpNDM0ZDR2NTA2YTR1Mmw0bTVsNA=="))) {
        showCriticalError("Sorry, I can not continue due a Critical Error. The Error Code is: AL. Please contact us at <a $href = \"https://templateshares.net/?$u = " . urlencode(INSTALL_URL) . "&$i = " . urlencode(INSTALL_IP) . "\">https://templateshares.net/</a>.");
    }
}
function function_329()
{
    if (!ini_get("allow_url_fopen") && (!function_exists("curl_init") || !($ch = curl_init()))) {
        showCriticalError("PHP allow_url_fopen or CURL Function must be turned on for this script to work!");
    }
}
function check_license()
{
    global $__step;
    $licenseKey = "";
    $accountDetails = buildLicenseQueryString();
    $licenseError = "";
    $licenseRequest = "0=1&1=" . encodeInstallString(INSTALL_URL) . "&2=" . encodeInstallString(INSTALL_IP) . "&3=" . encodeInstallString(SHORT_SCRIPT_VERSION);
    $licenseResponse = sendLicenseRequest($licenseRequest);
    if ($licenseResponse && preg_match("#" . decodeLicenseApiString("djRwM2QzdjNxNHo0YzNoMzQ0dDRlM3ozZDVzM2czcDMxNTA1bzNwM3E0MjVuMm4ycTJ2Mzk2eTRqM3MzbzRqM2EzbzRkNW8zZDN1MzI0cjNkM3czazNyM3o0NDVlMzE1") . "#is", $licenseResponse, $licenseKey)) {
        $licenseKey = strtoupper(trim($licenseKey[1]));
    }
    if (preg_match("#404 Not Found#isU", $licenseResponse)) {
        showCriticalError("Server response failed. Please contact Administrator.");
    }
    if (!validateInstallKey($licenseKey)) {
        $licenseError = $licenseKey;
        unset($licenseKey);
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["LICENSE_KEY"])) {
        $_POST["LICENSE_KEY"] = trim(strtoupper($_POST["LICENSE_KEY"]));
        if (empty($_POST["LICENSE_KEY"])) {
            $licenseError = "License key can not be empty.";
        } else {
            if (!validateInstallKey($_POST["LICENSE_KEY"])) {
                $licenseError = "Invalid license key.";
            } else {
                $invalidKeyFlag = true;
            }
        }
        unset($_SESSION["LICENSE_KEY"]);
        $_SESSION["LICENSE_KEY"] = $_POST["LICENSE_KEY"];
    }
    if (isset($_SESSION["LICENSE_KEY"]) && !validateInstallKey($_SESSION["LICENSE_KEY"])) {
        unset($_SESSION["LICENSE_KEY"]);
    }
    if (empty($_SESSION["LICENSE_KEY"]) || empty($licenseKey) || $_SESSION["LICENSE_KEY"] != $licenseKey || !validateInstallKey($_SESSION["LICENSE_KEY"]) || !validateInstallKey($licenseKey)) {
        if (isset(invalidKeyFlag) && !$licenseError) {
            $licenseError = "The entered license key does not match.";
        }
        $licenseForm = ($licenseError ? "<font $color = \"red\"><b>" . $licenseError . "</b></font><br /><br />" : "") . "\r\n\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$step = " . $__step . "\" $name = \"LICENSE_KEY\" $onsubmit = \"document.LICENSE_KEY.submit.$value = 'Checking the key...';document.LICENSE_KEY.submit.$disabled = true;\">\r\n\t\t<input $type = \"hidden\" $name = \"step\" $value = \"" . $__step . "\" />\r\n\t\tPlease enter your LICENSE KEY: <input $type = \"text\" $name = \"LICENSE_KEY\" $size = \"60\" />\r\n\t\t<input $type = \"submit\" $name = \"submit\" $value = \"Confirm License Key\" />\r\n\t\t</form>\r\n\t\t";
        echo renderInstallPage("Welcome to the upgrade wizard for " . SCRIPT_VERSION, "\r\n\t\t" . licenseForm . "\r\n\t\t", "Validation");
        echo renderInstallFooter();
        exit;
    }
    if (substr(sprintf("%o", @fileperms(CACHED_ADMIN_FILE)), -4) != "0777") {
        @chmod(CACHED_ADMIN_FILE, 511);
    }
    if (!file_put_contents(CACHED_ADMIN_FILE, md5(INSTALL_URL . $_SESSION["LICENSE_KEY"] . INSTALL_URL))) {
        showCriticalError(CACHED_ADMIN_FILE . " is not writable!");
    }
}
function handleInstallStep()
{
    global $__step;
    switch ($__step) {
        case 0:
            renderWelcomeScreen();
            exit;
            break;
        case 1:
            checkRequirementsAndPermissions();
            exit;
            break;
        case 2:
            confirmDatabaseDetails();
            exit;
            break;
        case 3:
            createDatabaseTables();
            exit;
            break;
        case 4:
            populateDatabaseTables();
            exit;
            break;
        case 7:
            finalizeInstallation();
            exit;
            break;
        default:
            renderWelcomeScreen();
            exit;
    }
}
function sendLicenseRequest($__query = "")
{
    $licenseUrl = decodeLicenseApiString("YzR0NGs0bzRmNDQ0czJ2NHM0MTZyMnE0ajVqNG40ZTRpNTE2YjRsNHA1YTRwNDY0cjQwNHI1dzVjNGs0");
    $licenseHost = decodeLicenseApiString("cjR3NG40dTIxNm01aTRvNGg0ZjVwNGI0eDVlNDg0azRtNTA2czJiNHY1ZjRtNA==");
    $licensePath = "/" . decodeLicenseApiString("NTR1NGs0ZzRtNXY1cDRoNDg0ZjVwNGI0") . "/" . SHORT_SCRIPT_VERSION . "/" . decodeLicenseApiString("ZzRpNDM0ZDR2NTA2YTR1Mmw0bTVsNA==");
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $__query);
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
        $httpRequest .= "Content-Length: " . strlen($__query) . "\r\n\r\n";
        @socket_set_timeout(fsock, $connectTimeout);
        @fwrite(fsock, $httpRequest . $__query);
        $curlResult = "";
        while (!@feof(fsock)) {
            $curlResult .= @fgets(fsock, 1024);
        }
        @fclose(fsock);
        return $curlResult;
    }
    showCriticalError("PHP allow_url_fopen or CURL Function must be turned on for this script to work!");
}
function buildLicenseQueryString()
{
    require_once "./config.php";
    if (USERNAME == "" || PASSWORD == "" || SECURITYKEY == "" || USERNAME == "Your_TS_Username_Goes_Here" || PASSWORD == "Your_TS_Password_Goes_Here" || SECURITYKEY == "Your_TS_Script_Security_Code_Goes_Here") {
        showCriticalError("Please open following file: <b>install/config.php</b> and enter/check your account details which you have registered on Templateshares.");
    } else {
        return "&U=" . encodeInstallString(USERNAME) . "&P=" . encodeInstallString(PASSWORD) . "&S=" . encodeInstallString(SECURITYKEY) . "&I=" . encodeInstallString($_SERVER["REMOTE_ADDR"]);
    }
}
function validateInstallKey($installkey = "")
{
    $var_97 = "{########-####-####-####-############}";
    $var_97 = str_replace("#", "[0-9,A-F]", $var_97);
    if (@preg_match($var_97, $installkey)) {
        return true;
    }
    return false;
}
function escapeSqlValue($value)
{
    if (@get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "'";
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
        showCriticalError("I can't serialize the " . $configname . ".");
    }
    $var_638 = mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_config` VALUES (\"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $configname) . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $data) . "\")");
    if (!$var_638) {
        showCriticalError("I can't insert the " . $configname . " into database.");
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
    if (isset($_SESSION["upgrade_from"])) {
        unset($_SESSION["upgrade_from"]);
    }
    echo renderInstallPage("A critical error has occured.", "<span $style = \"color: darkred; font-weight: bold;\">" . $message . "</span>");
    echo renderInstallFooter();
    exit;
}
function renderInstallStepMessage($step, $message = "", $error = false, $jump = false)
{
    return "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction ConfirmAction(msg)\r\n\t\t{\r\n\t\t\tif (confirm(msg))\r\n\t\t\t{\r\n\t\t\t\twindow.$location = \"" . $_SERVER["SCRIPT_NAME"] . "?$step = " . $step . "\";\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<p>\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"10\" $cellspacing = \"0\" $align = \"center\"><tr><td class=\"subheader\"><span $style = \"float: right\"><input $type = \"button\" $value = \"NEXT\" class=\"button\" $onclick = \"" . (!$error ? "window.$location = '" . $_SERVER["SCRIPT_NAME"] . "?$step = " . $step . "'" : ($jump ? "return ConfirmAction('The installer has detected same problems, are you sure that you want to continue?');" : "alert('The installer has detected some problems, which will not allow " . SCRIPT_VERSION . " to operate correctly. Please correct these issues and then refresh the page.')")) . "\" /></span>" . $message . "</td></tr></table>\r\n\t</p>";
}
function renderRequirementRow($message, $good)
{
    if ($good) {
        $var_639 = renderSuccessIcon();
    } else {
        $var_639 = renderErrorIcon();
    }
    return "<tr><td $width = \"85%\" $align = \"left\">" . $message . "</td><td class=\"req\" $width = \"15%\" $align = \"center\">" . $var_639 . "</td></tr>";
}
function sendInstallStats()
{
    $licenseQueryString = "";
    $var_44 = "http" . ($_SERVER["HTTPS"] == "on" ? "s" : "") . "://" . (!empty($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : (!empty($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "")) . ($_SERVER["SERVER_PORT"] ? ":" . $_SERVER["SERVER_PORT"] : "") . ($_SERVER["SCRIPT_NAME"] ? $_SERVER["SCRIPT_NAME"] : $_SERVER["PHP_SELF"]);
    $clientIp = getInstallIpAddress();
    $versionText = trim(@file_get_contents(ROOT_PATH . "version.txt"));
    if ($_SERVER["SERVER_ADMIN"]) {
        $licenseQueryString .= "&SA=" . urlencode($_SERVER["SERVER_ADMIN"]);
    }
    if ($_SERVER["SERVER_SOFTWARE"]) {
        $licenseQueryString .= "&SS=" . urlencode($_SERVER["SERVER_SOFTWARE"]);
    }
    if ($_SERVER["HTTP_USER_AGENT"]) {
        $licenseQueryString .= "&UA=" . urlencode($_SERVER["HTTP_USER_AGENT"]);
    }
    if ($_SERVER["REMOTE_ADDR"]) {
        $licenseQueryString .= "&UI=" . urlencode($_SERVER["REMOTE_ADDR"]);
    }
    @file_get_contents(@decodeLicenseApiString("YzR0NGs0bzRmNDQ0czJzNGE0cjVsNGk0ZjVxNGM0bDRwNWk1bzQ3NDA2djJnNGY0ZTR0NTU0ajVyNHA0bjU2NGg0ejVuNWM0NTRsNDg0czI=") . SHORT_SCRIPT_VERSION . @decodeLicenseApiString("cjJpNGU0cjQxNmk1aDRrNDY0eTVlNGw0czVzMm40YTR4NWs0ajNyM2k0") . @urlencode($var_44) . "&IP=" . $clientIp . "&SK=" . @urlencode($versionText) . $licenseQueryString);
}
function setSessionValues($values)
{
    foreach ($values as $var_41 = > $value) {
        unset($_SESSION[$var_41]);
        $_SESSION[$var_41] = $value;
    }
}
function checkDatabaseConnection()
{
    $errorMessages = [1 => "<li>Don't leave any fields blank in include/config_database.php!</li>", 2 => "<li>Could not connect to the database server at '" . (isset($_POST["mysql_host"]) ? htmlspecialchars(trim($_POST["mysql_host"])) : (isset($_SESSION["mysql_host"]) ? $_SESSION["mysql_host"] : "empty")) . "' with the supplied username and password.<br>Are you sure the hostname and user details are correct?</li>", 3 => "<li>Could not select the database '" . (isset($_POST["mysql_db"]) ? htmlspecialchars(trim($_POST["mysql_db"])) : (isset($_SESSION["mysql_db"]) ? $_SESSION["mysql_db"] : "empty")) . "'.<br>Are you sure it exists and the specified username and password have access to it?</li>", 4 => "<li>The passwords you entered do not match.</li>"];
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
    exit("\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html $lang = \"en\">\r\n\t<head>\r\n\t<title>" . $message . "</title>\r\n\t<meta http-$equiv = \"refresh\" $content = \"" . $wait . ";URL=" . $url . "\">\r\n\t<link $rel = \"stylesheet\" $href = \"templates/default/style/style.css\" $type = \"text/css\" $media = \"screen\" />\r\n\t</head>\r\n\t<body>\r\n\t<br />\r\n\t<br />\r\n\t<br />\r\n\t<br />\r\n\t<div $style = \"margin: auto auto; width: 50%\" $align = \"center\">\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" class=\"tborder\">\r\n\t<tr>\r\n\t<td class=\"trow1\" $align = \"center\"><p><font $color = \"#000000\">" . $message . "</font></p></td>\r\n\t</tr>\r\n\t<tr>\r\n\t<td class=\"trow2\" $align = \"right\"><a $href = \"" . $url . "\">\r\n\t<span class=\"smalltext\">Please click here if your browser does not automatically redirect you.</span></a></td>\r\n\t</tr>\r\n\t</table>\r\n\t</div>\r\n\t</body>\r\n\t</html>\r\n\t");
}
function renderInstallPage($title = "TS SE upgrade Wizard", $content = "", $step = "")
{
    return "\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html $xmlns = \"http://www.w3.org/1999/xhtml\" xml:$lang = \"en\" $lang = \"en\">\r\n\t\t<head>\r\n\t\t\t<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = utf-8\" />\r\n\t\t\t<title>" . $title . "</title>\r\n\t\t\t<link $rel = \"stylesheet\" $href = \"templates/default/style/style.css\" $type = \"text/css\" $media = \"screen\" />\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<div class=\"content\">\r\n\t\t\t\t<div $id = \"top\">\r\n\t\t\t\t\t<div $style = \"float: left; padding: 5px 25px 0 25px; position:relative;\" class=\"padding\">" . date("F j, Y, g:i a") . "</div>\r\n\t\t\t\t\t<div class=\"padding\" $align = \"center\">TS SE upgrade Wizard v." . INSTALL_VERSION . "</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div $id = \"header\">\r\n\t\t\t\t</div>\r\n\t\t\t\t<div $id = \"main\">\r\n\t\t\t\t\t<div class=\"left_side\">\r\n\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\" $align = \"center\"><font $size = \"2\"><b>" . $title . "</b></font></td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t" . ($content ? $content . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>" : "") . "\r\n\t";
}
function renderInstallFooter()
{
    return "\r\n\t\t\t\t\t\t<br />\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div $id = \"footer\">\r\n\t\t\t\t\t<div class=\"padding\">Powered by <font $color = \"white\"><strong><a $href = \"https://templateshares.net/?" . INSTALL_URL . "\" $target = \"_blank\">" . SCRIPT_VERSION . "</a></strong></font> - Copyright &copy; 2006-" . date("Y") . " Templateshares, All rights reserved.</div>\r\n\t\t\t\t</div>\r\n\t\t\t</div>\r\n\t\t</body>\r\n\t</html>";
}
function renderErrorIcon()
{
    return "<img $src = \"templates/default/images/error.gif\" $alt = \"NO\" $border = \"0\" />";
}
function renderSuccessIcon()
{
    return "<img $src = \"templates/default/images/success.gif\" $alt = \"YES\" $border = \"0\" />";
}
function renderWelcomeScreen()
{
    $welcomeContent = "\r\n\tThis wizard will upgrade and configure a copy of " . SCRIPT_VERSION . " on your server.\r\n\t<p>Now that you've uploaded the " . SCRIPT_VERSION . " files, the database and settings need to be created and imported. Below is an outline of what is going to be completed during upgrade.</p>\r\n\t<ul>\r\n\t<li>" . SCRIPT_VERSION . " requirements checked,</li>\r\n\t<li>Configuration of database engine,</li>\r\n\t<li>Version selection,</li>\r\n\t<li>Updating tables,</li>\r\n\t<li>Finishing Setup.</li>\r\n\t</ul>\r\n\tBefore we go any further, please ensure that all the files have been uploaded in binary mode, and that the folder \"CACHE\" has suitable permissions to allow this script to write to it (0777 should be sufficient).<br /><br />\r\n\r\n\t" . SCRIPT_VERSION . " requires PHP 5.2 or better and an MYSQL database.<br /><br />\r\n\r\n\t<b>You will also need the following information that your webhost can provide:</b><br />\r\n\t<ul>\r\n\t<li> Any linux (unix), windows webserver running Apache will work. IIS may work but is not recommended, some users might have trouble with file permissions when running IIS.</li>\r\n\t<li><b>The Apache webserver version 1.3 or greater.</b></li>\r\n\t<li><b>MYSQL 5.1 or greater.</b></li>\r\n\t<ul><li> Your MYSQL database name.</li></ul>\r\n\t<ul><li> Your MYSQL username.</li></ul>\r\n\t<ul><li> Your MYSQL password.</li></ul>\r\n\t<ul><li> Your MYSQL host address (usually localhost).</li></ul>\r\n\t<li><b>PHP version 5.2 or greater.</b></li>\r\n\t<ul><li>Ioncube Loader</li></ul>\r\n\t<ul><li>Session Support</li></ul>\r\n\t</ul>\r\n\t<div class=warnbox>Please remember, you can not run this upgrade script more than once.</div>\r\n\t<br />\r\n\tAfter each step has successfully been completed, click Next button to move on to the next step." . renderInstallStepMessage(1);
    echo renderInstallPage("Welcome to the upgrade wizard for " . SCRIPT_VERSION, $welcomeContent, "Welcome Screen");
    echo renderInstallFooter();
}
function checkRequirementsAndPermissions()
{
    initializeInstallSession();
    $directoriesToCheck = ["admin/backup", "cache", "include/avatars", "torrents", "torrents/images", "tsf_forums/uploads", "ts_albums/album_images", "ts_albums/album_thumbnails"];
    $fileList = ["include/config_announce.php", "shoutcast/cache.xml", "shoutcast/lps.dat"];
    $hasErrors = false;
    $outputHtml = "\r\n\tIn this step, the " . SCRIPT_VERSION . " installer will determine if your system meets the requirements for the server environment. To use " . SCRIPT_VERSION . ", you must have PHP with MySQL support and write-permissions on certain directories/files.<br /><br />";
    $var_299 = "";
    $allRequirementsMet = 1;
    $good = version_compare(PHP_VERSION, "5.4.0", "<") ? 0 : 1;
    $allRequirementsMet = $allRequirementsMet && $good;
    $var_299 .= renderRequirementRow("PHP version >= 5.4.0: ", $good);
    $_SESSION["testing_string"] = "Just a Test!";
    $good = $_SESSION["testing_string"] === "Just a Test!" ? 1 : 0;
    $allRequirementsMet = $allRequirementsMet && $good;
    $var_299 .= renderRequirementRow("PHP session support:", $good);
    $good = function_exists("mysqli_connect") ? 1 : 0;
    $allRequirementsMet = $allRequirementsMet && $good;
    $var_299 .= renderRequirementRow("MySQLi support exists: ", $good);
    if (!$allRequirementsMet) {
        $hasErrors = true;
    }
    $outputHtml .= "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"4\" $cellspacing = \"0\" $align = \"center\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"2\" $width = \"100%\" $align = \"left\">Requirements Check</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"75%\" $align = \"left\">Function / Feature / Requirement</td>\r\n\t\t\t<td class=\"subheader\" $width = \"25%\" $align = \"center\">Available</td>\r\n\t\t</tr>\r\n\t\t" . $var_299 . "\r\n\t\t</table><br />";
    $outputHtml .= "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"4\" $cellspacing = \"0\" $align = \"center\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"2\" $width = \"100%\" $align = \"left\">Checking Directory Chmod Permissions</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"75%\" $align = \"left\">Directory</td>\r\n\t\t\t<td class=\"subheader\" $width = \"25%\" $align = \"center\">Writable</td>\r\n\t\t</tr>\r\n\t";
    sort($directoriesToCheck);
    foreach ($directoriesToCheck as $directory) {
        $fullPath = ROOT_PATH . $directory;
        $outputHtml .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"85%\" $align = \"left\">" . str_replace(ROOT_PATH, "", $fullPath) . "</td>";
        if (!is_writable($fullPath) || !is_dir($fullPath)) {
            $outputHtml .= "\r\n\t\t\t<td $align = \"center\" $width = \"15%\">" . renderErrorIcon() . "</td>\r\n\t\t</tr>";
            $hasErrors = true;
        } else {
            $outputHtml .= "\r\n\t\t\t<td $align = \"center\" $width = \"15%\">" . renderSuccessIcon() . "</td>\r\n\t\t</tr>";
        }
    }
    $outputHtml .= "\r\n\t</table><br />";
    $outputHtml .= "\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"4\" $cellspacing = \"0\" $align = \"center\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"2\" $width = \"100%\" $align = \"left\">Checking File Chmod Permissions</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"75%\" $align = \"left\">File</td>\r\n\t\t\t<td class=\"subheader\" $width = \"25%\" $align = \"center\">Writable</td>\r\n\t\t</tr>\r\n\t";
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
        $outputHtml .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"85%\" $align = \"left\">" . str_replace(ROOT_PATH, "", $filePath) . "</td>";
        if (!is_writable($filePath) || !is_file($filePath)) {
            $outputHtml .= "\r\n\t\t\t<td $align = \"center\" $width = \"15%\">" . renderErrorIcon() . "</td>\r\n\t\t</tr>";
            $hasErrors = true;
        } else {
            $outputHtml .= "\r\n\t\t\t<td $align = \"center\" $width = \"15%\">" . renderSuccessIcon() . "</td>\r\n\t\t</tr>";
        }
    }
    $outputHtml .= "\r\n\t</table>";
    if (!$hasErrors) {
        $outputHtml .= renderInstallStepMessage(2, "Congratulations, no errors found!");
    } else {
        $outputHtml .= renderInstallStepMessage(2, "The installer has detected some problems with your server environment, which will not allow " . SCRIPT_VERSION . " to operate correctly. Please correct these issues and then refresh the page to re-check your environment.", true);
    }
    echo renderInstallPage("Welcome to the upgrade wizard for " . SCRIPT_VERSION, "\r\n\t" . $outputHtml . "\r\n\t", "Requirements Check");
    echo renderInstallFooter();
}
function confirmDatabaseDetails()
{
    require ROOT_PATH . "include/config_database.php";
    $errorTemplates = [1 => "<li>Don't leave any fields blank in include/config_database.php!</li>", 2 => "<li>Could not connect to the database server at '" . MYSQL_HOST . "' with the supplied username and password.<br>Are you sure the hostname and user details are correct in include/config_database.php file?</li>", 3 => "<li>Could not select the database '" . MYSQL_DB . "'.<br>Are you sure it exists and the specified username and password have access to it?</li>", 4 => "<li>The passwords you entered do not match.</li>"];
    if (MYSQL_HOST == "" || MYSQL_USER == "" || MYSQL_PASS == "" || MYSQL_DB == "") {
        $dbErrors[] = $errorTemplates[1];
    }
    $GLOBALS["DatabaseConnect"] = @mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if (!$GLOBALS["DatabaseConnect"]) {
        $dbErrors[] = $errorTemplates[2];
    }
    if (!isset($dbErrors)) {
        $values = ["mysql_host" => MYSQL_HOST, "mysql_user" => MYSQL_USER, "mysql_pass" => MYSQL_PASS, "mysql_db" => MYSQL_DB];
        setSessionValues($values);
        $pageContent = renderInstallPage("\r\n\t\t\tDatabase Confirmation", "Please check your Database details.\r\n\t\t\t<br /><br />\r\n\t\t\t<b>Database Host</b>: " . MYSQL_HOST . "<br />\r\n\t\t\t<b>Database Username</b>: " . MYSQL_USER . "<br />\r\n\t\t\t<b>Database Name</b>: " . MYSQL_DB . "<br />\r\n\t\t\t<b>Database Password</b>: <i>Hidden</i>" . renderInstallStepMessage(3, "If your Database details shown above are correct, click Next button to move on to the next step."));
        $pageContent .= renderInstallFooter();
        exit($pageContent);
    }
    showCriticalError(implode("<br />", $dbErrors));
}
function createDatabaseTables()
{
    global $__step;
    global $PREVIOUS_VERSIONS;
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["upgrade_from"]) && in_array($_POST["upgrade_from"], $PREVIOUS_VERSIONS, true)) {
        $_SESSION["upgrade_from"] = trim($_POST["upgrade_from"]);
        var_652("Upgrade Version configuration has been saved successfully.", $_SERVER["SCRIPT_NAME"] . "?$step = 4");
    }
    $var_446 = "<option $value = \"\"></option>";
    foreach ($PREVIOUS_VERSIONS as $version) {
        $var_446 .= "\r\n\t\t<option $value = \"" . $version . "\">Upgrade From TS Special Edition v." . $version . " to " . SCRIPT_VERSION . "</option>";
    }
    $var_643 = "This wizard will upgrade and configure a copy of " . SCRIPT_VERSION . " on your server.";
    $var_643 .= "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction confirmSelectedVersion(WhatSelected)\r\n\t\t{\r\n\t\t\tif (!WhatSelected.value)\r\n\t\t\t{\r\n\t\t\t\talert(\"Invalid version selected!\");\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t\tvar SelectedVersion = \"TS Special Edition v.\"+WhatSelected.value;\r\n\t\t\tif (confirm(\"Are you sure that you want to upgrade from\\n\\n\"+SelectedVersion+\"\\nto\\n" . SCRIPT_VERSION . "\"))\r\n\t\t\t{\r\n\t\t\t\treturn true;\r\n\t\t\t}\r\n\t\t\treturn false;\r\n\t\t}\r\n\t</script>\r\n\t<br /><br />\r\n\t<b>Please select Upgrade Type:</b>\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$step = " . $__step . "\" $name = \"upgrade\" $onsubmit = \"return confirmSelectedVersion(this.upgrade_from);\">\r\n\t<input $type = \"hidden\" $name = \"step\" $value = \"" . $__step . "\" />\r\n\t<select $name = \"upgrade_from\" $id = \"upgrade_from\">\r\n\t\t" . $var_446 . "\r\n\t</select>\r\n\t<input $type = \"submit\" class=\"button\" $value = \"Upgrade Now!\" />\r\n\t</form>";
    echo renderInstallPage("Welcome to the upgrade wizard for " . SCRIPT_VERSION, $var_643, "Welcome Screen");
    echo renderInstallFooter();
}
function populateDatabaseTables()
{
    global $PREVIOUS_VERSIONS;
    if (!isset($_SESSION["upgrade_from"]) || !in_array($_SESSION["upgrade_from"], $PREVIOUS_VERSIONS, true)) {
        $_SESSION["upgrade_from"] = "";
        header("Location: " . $_SERVER["SCRIPT_NAME"] . "?$step = 3&$from = 4");
        exit;
    }
    $db = checkDatabaseConnection();
    if (!empty($db)) {
        showCriticalError(implode("<br />", $db) . "<br />There seems to be one or more errors with the database configuration information that you supplied. Click <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$step = 2\">here</a> to to back step 2.");
    }
    $licenseRequest = "0=5&1=" . encodeInstallString(INSTALL_URL) . "&2=" . encodeInstallString(INSTALL_IP) . "&3=" . encodeInstallString(SHORT_SCRIPT_VERSION) . "&4=" . encodeInstallString($_SESSION["upgrade_from"]) . buildLicenseQueryString();
    $licenseResponse = sendLicenseRequest($licenseRequest);
    if (preg_match("#INVALID USER ACCOUNT#", $licenseResponse)) {
        showCriticalError("I am unable to confirm your Account in our database. This could be because of one of the following reasons. <ul><li>Are you sure that you have entered your Templateshares login details correctly into <b>config.php</b>?</li><li>Are you sure that you have a valid TS SE license to use this version?</li></ul>");
    } else {
        if (preg_match("#INVALID UPGRADE VERSION OR UPGRADE FILE DOES NOT EXISTS!#", $licenseResponse)) {
            showCriticalError("Invalid Upgrade Script Version! Click <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$step = 3\">here</a> to start from the beginning.");
        }
    }
    $licenseResponse = @preg_match("#--BEGIN--(.*)--END--#is", $licenseResponse, $matches);
    $licenseResponse = @str_replace(["\n", "\r"], "", $matches[1]);
    $tableDefinitions = @explode("[TABLE]", $licenseResponse);
    if (count($tableDefinitions) < 1) {
        showCriticalError("Connection Error: upgrade");
    }
    echo renderInstallPage("Welcome to the upgrade wizard for " . SCRIPT_VERSION, false, "Populate Tables");
    echo "<table $border = \"0\" $align = \"center\" $cellpadding = \"4\" class=\"okbox\" $width = 100%>\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $align = \"center\">Query Count</td>\r\n\t\t<td class=\"subheader\">Message</td>\r\n\t\t<td class=\"subheader\" $align = \"center\">Status</td>\r\n\t</tr>";
    var_658();
    $hasQueryErrors = false;
    $count = 0;
    $failedQueryCount = 0;
    foreach ($tableDefinitions as $tableQuery) {
        @preg_match("#CREATE TABLE (\\S+) \\(#i", $tableQuery, $var_585);
        if (isset($var_585[1]) && !empty($var_585[1])) {
            @mysqli_query($GLOBALS["DatabaseConnect"], "DROP TABLE IF EXISTS " . $var_585[1]);
        }
        if (!mysqli_query($GLOBALS["DatabaseConnect"], $tableQuery)) {
            $hasQueryErrors = true;
            $failedQueryCount++;
            $status = renderErrorIcon();
            $message = " <font $color = \"red\"><b>(" . mysqli_errno($GLOBALS["DatabaseConnect"]) . ") " . mysqli_error($GLOBALS["DatabaseConnect"]) . "</b></font> ";
        } else {
            $message = " <font $color = \"green\"><b>OK</b></font> ";
            $status = renderSuccessIcon();
        }
        $count++;
        echo "<tr><td $align = \"center\">Query <b>" . $count . "</b></td><td>" . $message . "</td><td $align = \"center\">" . $status . "</td></tr>";
        var_658();
    }
    echo "</table>";
    var_658();
    if (!$hasQueryErrors) {
        echo renderInstallStepMessage(7, "The default data has successfully been inserted into the database.");
        echo "</td></tr></table>";
        echo renderInstallFooter();
        var_658();
    } else {
        echo renderInstallStepMessage(7, "There was total <b>" . $failedQueryCount . "</b> queries failed during upgrade which will not allow " . SCRIPT_VERSION . " to operate correctly.<br />Please use your backup and revert back to the previous version and than re-run the upgrade script.", true, true);
        echo "</td></tr></table>";
        echo renderInstallFooter();
        var_658();
    }
}
function finalizeInstallation()
{
    global $PREVIOUS_VERSIONS;
    if (!isset($_SESSION["upgrade_from"]) || !in_array($_SESSION["upgrade_from"], $PREVIOUS_VERSIONS, true)) {
        $_SESSION["upgrade_from"] = "";
        header("Location: " . $_SERVER["SCRIPT_NAME"] . "?$step = 3");
        exit;
    }
    if (!writeTrackerCacheFile()) {
        showCriticalError("Please chmod 0777 to the following file and refresh the page: " . CACHED_TRACKER_FILE);
    }
    $successMessage = SCRIPT_VERSION . " has successfully been installed and configured correctly. The Template Shares Group thanks you for your support and we hope to see you around the community forums if you need help or wish to become a part of the TS community. <br><br><div class=warnbox>After a successful login, please goto staff panel and configurate your tracker otherwise TS SE won't work correctly! <br><br>Click <a $href = \"./../index.php\">here</a> to login.<br><br>DO NOT FORGET TO DELETE INSTALL FOLDER !!!";
    echo renderInstallPage("Welcome to the upgrade wizard for " . SCRIPT_VERSION, "\r\n\t" . $successMessage . "\r\n\t", "Finish Setup");
    echo renderInstallFooter();
    @sendInstallStats();
}
function encryptData($data)
{
    global $LicenseCipher;
    return $LicenseCipher->encode($data);
}
function customEncrypt($string, $key = "1231231231231235555gfdgfd322332323")
{
    $hash = "";
    $key = sha1($key);
    $stringLength = strlen($string);
    $keyLength = strlen($key);
    $j = 0;
    for ($i = 0; $i < $stringLength; $i++) {
        $charByte = ord(substr($string, $i, 1));
        if ($j == $keyLength) {
            $j = 0;
        }
        $keyByte = ord(substr($key, $j, 1));
        $j++;
        $hash .= strrev(base_convert(dechex($charByte + $keyByte), 16, 36));
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
        $charByte = hexdec(base_convert(strrev(substr($string, $i, 2)), 36, 16));
        if ($j == $keyLength) {
            $j = 0;
        }
        $keyByte = ord(substr($key, $j, 1));
        $j++;
        $hash .= chr($charByte - $keyByte);
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