<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
class Class_15 extends Exception
{
}
class Class_16
{
    public $key = NULL;
    public $secret = NULL;
    public function __construct($key, $secret, $callback_url = NULL)
    {
        $this->$key = $key;
        $this->$secret = $secret;
        $this->$callback_url = $callback_url;
    }
    public function __toString()
    {
        return "OAuthConsumer[$key = " . $this->key . ",$secret = " . $this->secret . "]";
    }
}
$Language = file("languages/" . getStaffLanguage() . "/twitter.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$username = "";
$password = "";
$message = isset($_GET["message"]) ? trim(urldecode($_GET["message"])) : "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = \"SOCIAL\"");
$Result = mysqli_fetch_assoc($query);
$SOCIAL = unserialize($Result["content"]);
if ($Act == "post") {
    $_message = isset($_POST["message"]) ? trim($_POST["message"]) : "";
    if (!$_message) {
        $Message = showAlertError($Language[8]);
    } else {
        $consumerKey = $SOCIAL["ConsumerKey"];
        $consumerSecret = $SOCIAL["ConsumerSecret"];
        $accessToken = $SOCIAL["AccessToken"];
        $accessTokenSecret = $SOCIAL["AccessTokenSecret"];
        if (!$consumerKey || !$consumerSecret || !$accessToken || !$accessTokenSecret) {
            $Message = showAlertError("Please setup Twitter API options first. (Manage Tracker Settings > SOCIAL)");
        } else {
            $Twitter = new Class_17($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
            $Twitter->postTweet(substr(strip_tags($_message), 0, 120));
            $Message = showAlertError($Language[12]);
        }
    }
}
echo "\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=twitter&$act = post\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[11] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt1\"><textarea $name = \"message\" $id = \"textarea1\" $style = \"width: 99%; height: 50px;\" $placeholder = \"" . $Language[13] . "\"></textarea></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input $type = \"submit\" $value = \"" . $Language[5] . "\" $onclick = \" this.$value = '" . trim($Language[7]) . "';\" /> \r\n\t\t\t<input $type = \"reset\" $value = \"" . $Language[6] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
class Class_18
{
    public $key = NULL;
    public $secret = NULL;
    public function __construct($key, $secret)
    {
        $this->$key = $key;
        $this->$secret = $secret;
    }
    public function toQueryString()
    {
        return "oauth_token=" . Class_19::urlEncodeRFC3986($this->key) . "&$oauth_token_secret = " . Class_19::urlEncodeRFC3986($this->secret);
    }
    public function __toString()
    {
        return $this->toQueryString();
    }
}
abstract class Class_20
{
    public abstract function getSignatureMethodName();
    public abstract function computeSignature($request, $consumer, $token);
    public function verifySignature($request, $consumer, $token, $signature)
    {
        $var_509 = $this->computeSignature($request, $consumer, $token);
        return $var_509 == $signature;
    }
}
class Class_21 extends Class_20
{
    public function getSignatureMethodName()
    {
        return "HMAC-SHA1";
    }
    public function computeSignature($request, $consumer, $token)
    {
        $var_510 = $request->getBaseString();
        $request->$base_string = $base_string;
        $var_511 = [$consumer->secret, $token ? $token->secret : ""];
        $var_511 = Class_19::urlEncodeRFC3986($var_511);
        $key = implode("&", $var_511);
        return base64_encode(hash_hmac("sha1", $base_string, $key, true));
    }
}
class Class_22 extends Class_20
{
    public function getSignatureMethodName()
    {
        return "PLAINTEXT";
    }
    public function computeSignature($request, $consumer, $token)
    {
        $var_511 = [$consumer->secret, $token ? $token->secret : ""];
        $var_511 = Class_19::urlEncodeRFC3986($var_511);
        $key = implode("&", $var_511);
        $request->$base_string = $key;
        return $key;
    }
}
abstract class Class_23 extends Class_20
{
    public function getSignatureMethodName()
    {
        return "RSA-SHA1";
    }
    protected abstract function getPrivateKey(&$request);
    protected abstract function getPublicKey(&$request);
    public function computeSignature($request, $consumer, $token)
    {
        $base_string = $request->getBaseString();
        $request->$base_string = $base_string;
        $var_512 = $this->getPublicKey($request);
        $var_513 = var_514($var_512);
        $var_515 = var_516($base_string, $signature, $var_513);
        var_517($var_513);
        return base64_encode($signature);
    }
    public function verifySignature($request, $consumer, $token, $signature)
    {
        $var_518 = base64_decode($signature);
        $base_string = $request->getBaseString();
        $var_512 = $this->getPrivateKey($request);
        $var_519 = var_520($var_512);
        $var_515 = var_521($base_string, $var_518, $var_519);
        var_517($var_519);
        return $var_515 == 1;
    }
}
class Class_24
{
    protected $parameters = NULL;
    protected $http_method = NULL;
    protected $http_url = NULL;
    public $base_string = NULL;
    public static $version = "1.0";
    public static $POST_INPUT = "php://input";
    public function __construct($http_method, $http_url, $parameters = NULL)
    {
        $parameters = $parameters ? $parameters : [];
        $parameters = array_merge(Class_19::parseQueryString(parse_url($http_url, PHP_URL_QUERY)), $parameters);
        $this->$parameters = $parameters;
        $this->$http_method = $http_method;
        $this->$http_url = $http_url;
    }
    public static function fromCurrentRequest($http_method = NULL, $http_url = NULL, $parameters = NULL)
    {
        $var_38 = !isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on" ? "http" : "https";
        $http_url = $http_url ? $http_url : $var_38 . "://" . $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        $http_method = $http_method ? $http_method : $_SERVER["REQUEST_METHOD"];
        if (!$parameters) {
            $var_522 = Class_19::getAllHttpHeaders();
            $parameters = Class_19::parseQueryString($_SERVER["QUERY_STRING"]);
            if ($http_method == "POST" && isset($var_522["Content-Type"]) && strstr($var_522["Content-Type"], "application/x-www-form-urlencoded")) {
                $var_523 = Class_19::parseQueryString(file_get_contents(self::$POST_INPUT));
                $parameters = array_merge($parameters, $var_523);
            }
            if (isset($var_522["Authorization"]) && substr($var_522["Authorization"], 0, 6) == "OAuth ") {
                $var_524 = Class_19::parseAuthorizationHeader($var_522["Authorization"]);
                $parameters = array_merge($parameters, $var_524);
            }
        }
        return new Class_24($http_method, $http_url, $parameters);
    }
    public static function forSignedRequest($consumer, $token, $http_method, $http_url, $parameters = NULL)
    {
        $parameters = $parameters ? $parameters : [];
        $var_525 = ["oauth_version" => Class_24::$version, "oauth_nonce" => Class_24::generateNonce(), "oauth_timestamp" => Class_24::getTimestamp(), "oauth_consumer_key" => $consumer->key];
        if ($token) {
            $var_525["oauth_token"] = $token->key;
        }
        $parameters = array_merge($var_525, $parameters);
        return new Class_24($http_method, $http_url, $parameters);
    }
    public function setParameter($name, $value, $allow_duplicates = true)
    {
        if ($allow_duplicates && isset($this->parameters[$name])) {
            if (is_scalar($this->parameters[$name])) {
                $this->parameters[$name] = [$this->parameters[$name]];
            }
            $this->parameters[$name][] = $value;
        } else {
            $this->parameters[$name] = $value;
        }
    }
    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : NULL;
    }
    public function getAllParameters()
    {
        return $this->parameters;
    }
    public function removeParameter($name)
    {
        unset($this->parameters[$name]);
    }
    public function getNormalizedParameters()
    {
        $var_310 = $this->parameters;
        if (isset($var_310["oauth_signature"])) {
            unset($var_310["oauth_signature"]);
        }
        return Class_19::buildQueryString($var_310);
    }
    public function getBaseString()
    {
        $var_526 = [$this->getHttpMethod(), $this->getNormalizedUrl(), $this->getNormalizedParameters()];
        $var_526 = Class_19::urlEncodeRFC3986($var_526);
        return implode("&", $var_526);
    }
    public function getHttpMethod()
    {
        return strtoupper($this->http_method);
    }
    public function getNormalizedUrl()
    {
        $var_526 = parse_url($this->http_url);
        $var_38 = isset($var_526["scheme"]) ? $var_526["scheme"] : "http";
        $port = isset($var_526["port"]) ? $var_526["port"] : ($var_38 == "https" ? "443" : "80");
        $var_39 = isset($var_526["host"]) ? $var_526["host"] : "";
        $var_480 = isset($var_526["path"]) ? $var_526["path"] : "";
        if ($var_38 == "https" && $port != "443" || $var_38 == "http" && $port != "80") {
            $var_39 = $var_39 . ":" . $port;
        }
        return $var_38 . "://" . $var_39 . $var_480;
    }
    public function toUrl()
    {
        $var_523 = $this->getQueryString();
        $var_527 = $this->getNormalizedUrl();
        if ($var_523) {
            $var_527 .= "?" . $var_523;
        }
        return $var_527;
    }
    public function getQueryString()
    {
        return Class_19::buildQueryString($this->parameters);
    }
    public function getAuthorizationHeader($realm = NULL)
    {
        $paginationFirstItem = true;
        if ($realm) {
            $var_527 = "Authorization: OAuth $realm = \"" . Class_19::urlEncodeRFC3986($realm) . "\"";
            $paginationFirstItem = false;
        } else {
            $var_527 = "Authorization: OAuth";
        }
        $var_528 = [];
        foreach ($this->parameters as $k => $var_124) {
            if (substr($k, 0, 5) == "oauth") {
                if (is_array($var_124)) {
                    throw new Class_15("Arrays not supported in headers");
                }
                $var_527 .= $paginationFirstItem ? " " : ",";
                $var_527 .= Class_19::urlEncodeRFC3986($k) . "=\"" . Class_19::urlEncodeRFC3986($var_124) . "\"";
                $paginationFirstItem = false;
            }
        }
        return $var_527;
    }
    public function __toString()
    {
        return $this->toUrl();
    }
    public function signRequest($signature_method, $consumer, $token)
    {
        $this->setParameter("oauth_signature_method", $signature_method->getSignatureMethodName(), false);
        $signature = $this->computeSignature($signature_method, $consumer, $token);
        $this->setParameter("oauth_signature", $signature, false);
    }
    public function computeSignature($signature_method, $consumer, $token)
    {
        $signature = $signature_method->computeSignature($this, $consumer, $token);
        return $signature;
    }
    private static function getTimestamp()
    {
        return time();
    }
    private static function generateNonce()
    {
        $var_529 = microtime();
        $rand = mt_rand();
        return md5($var_529 . $rand);
    }
}
class Class_25
{
    protected $timestamp_threshold = 300;
    protected $version = "1.0";
    protected $signature_methods = [];
    protected $data_store = NULL;
    public function __construct($data_store)
    {
        $this->$data_store = $data_store;
    }
    public function addSignatureMethod($signature_method)
    {
        $this->signature_methods[$signature_method->getSignatureMethodName()] = $signature_method;
    }
    public function validateRequestToken(&$request)
    {
        $this->validateVersion($request);
        $consumer = $this->getConsumer($request);
        $token = NULL;
        $this->verifySignature($request, $consumer, $token);
        $var_530 = $request->getParameter("oauth_callback");
        $var_531 = $this->data_store->getNewRequestToken($consumer, $var_530);
        return $var_531;
    }
    public function validateAccessToken(&$request)
    {
        $this->validateVersion($request);
        $consumer = $this->getConsumer($request);
        $token = $this->getToken($request, $consumer, "request");
        $this->verifySignature($request, $consumer, $token);
        $var_532 = $request->getParameter("oauth_verifier");
        $var_531 = $this->data_store->getNewAccessToken($token, $consumer, $var_532);
        return $var_531;
    }
    public function validateAndReturnCredentials(&$request)
    {
        $this->validateVersion($request);
        $consumer = $this->getConsumer($request);
        $token = $this->getToken($request, $consumer, "access");
        $this->verifySignature($request, $consumer, $token);
        return [$consumer, $token];
    }
    private function validateVersion(&$request)
    {
        $version = $request->getParameter("oauth_version");
        if (!$version) {
            $version = "1.0";
        }
        if ($version !== $this->version) {
            throw new Class_15("OAuth version '" . $version . "' not supported");
        }
        return $version;
    }
    private function getSignatureMethod($request)
    {
        $signature_method = $request instanceof Twitter_OAuthRequest ? $request->getParameter("oauth_signature_method") : NULL;
        if (!$signature_method) {
            throw new Class_15("No signature method parameter. This parameter is required");
        }
        if (!in_array($signature_method, array_keys($this->signature_methods))) {
            throw new Class_15("Signature method '" . $signature_method . "' not supported " . "try one of the following: " . implode(", ", array_keys($this->signature_methods)));
        }
        return $this->signature_methods[$signature_method];
    }
    private function getConsumer($request)
    {
        $var_533 = $request instanceof Twitter_OAuthRequest ? $request->getParameter("oauth_consumer_key") : NULL;
        if (!$var_533) {
            throw new Class_15("Invalid consumer key");
        }
        $consumer = $this->data_store->lookupConsumer($var_533);
        if (!$consumer) {
            throw new Class_15("Invalid consumer");
        }
        return $consumer;
    }
    private function getToken($request, $consumer, $token_type = "access")
    {
        $var_534 = $request instanceof Twitter_OAuthRequest ? $request->getParameter("oauth_token") : NULL;
        $token = $this->data_store->lookupToken($consumer, $token_type, $var_534);
        if (!$token) {
            throw new Class_15("Invalid " . $token_type . " token: " . $var_534);
        }
        return $token;
    }
    private function verifySignature($request, $consumer, $token)
    {
        $var_535 = $request instanceof Twitter_OAuthRequest ? $request->getParameter("oauth_timestamp") : NULL;
        $var_536 = $request instanceof Twitter_OAuthRequest ? $request->getParameter("oauth_nonce") : NULL;
        $this->validateTimestamp($var_535);
        $this->validateNonce($consumer, $token, $var_536, $var_535);
        $signature_method = $this->getSignatureMethod($request);
        $signature = $request->getParameter("oauth_signature");
        $var_537 = $signature_method->verifySignature($request, $consumer, $token, $signature);
        if (!$var_537) {
            throw new Class_15("Invalid signature");
        }
    }
    private function validateTimestamp($timestamp)
    {
        if (!$timestamp) {
            throw new Class_15("Missing timestamp parameter. The parameter is required");
        }
        $var_538 = time();
        if ($this->timestamp_threshold < abs($var_538 - $timestamp)) {
            throw new Class_15("Expired timestamp, yours " . $timestamp . ", ours " . $var_538);
        }
    }
    private function validateNonce($consumer, $token, $nonce, $timestamp)
    {
        if (!$nonce) {
            throw new Class_15("Missing nonce parameter. The parameter is required");
        }
        $var_539 = $this->data_store->checkNonceUsed($consumer, $token, $nonce, $timestamp);
        if ($var_539) {
            throw new Class_15("Nonce already used: " . $nonce);
        }
    }
}
class Class_26
{
    public function lookupConsumer($consumer_key)
    {
    }
    public function lookupToken($consumer, $token_type, $token)
    {
    }
    public function checkNonceUsed($consumer, $token, $nonce, $timestamp)
    {
    }
    public function getNewRequestToken($consumer, $callback = NULL)
    {
    }
    public function getNewAccessToken($token, $consumer, $verifier = NULL)
    {
    }
}
class Class_19
{
    public static function urlEncodeRFC3986($input)
    {
        if (is_array($input)) {
            return array_map(["Twitter_OAuthUtil", "urlencode_rfc3986"], $input);
        }
        if (is_scalar($input)) {
            return str_replace("+", " ", str_replace("%7E", "~", rawurlencode($input)));
        }
        return "";
    }
    public static function urlDecode($string)
    {
        return urldecode($string);
    }
    public static function parseAuthorizationHeader($header, $only_allow_oauth_parameters = true)
    {
        $var_310 = [];
        if (preg_match_all("/(" . ($only_allow_oauth_parameters ? "oauth_" : "") . "[a-z_-]*)=(:?\"([^\"]*)\"|([^,]*))/", $header, $var_220)) {
            foreach ($var_220[1] as $i => $var_540) {
                $var_310[$var_540] = Class_19::urlDecode(empty($var_220[3][$i]) ? $var_220[4][$i] : $var_220[3][$i]);
            }
            if (isset($var_310["realm"])) {
                unset($var_310["realm"]);
            }
        }
        return $var_310;
    }
    public static function getAllHttpHeaders()
    {
        if (function_exists("apache_request_headers")) {
            $var_541 = apache_request_headers();
            $var_527 = [];
            foreach ($var_541 as $key => $value) {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("-", " ", $key))));
                $var_527[$key] = $value;
            }
        } else {
            $var_527 = [];
            if (isset($_SERVER["CONTENT_TYPE"])) {
                $var_527["Content-Type"] = $_SERVER["CONTENT_TYPE"];
            }
            if (isset($_ENV["CONTENT_TYPE"])) {
                $var_527["Content-Type"] = $_ENV["CONTENT_TYPE"];
            }
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) == "HTTP_") {
                    $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                    $var_527[$key] = $value;
                }
            }
        }
        return $var_527;
    }
    public static function parseQueryString($input)
    {
        if (!isset($input) || !$input) {
            return [];
        }
        $var_542 = explode("&", $input);
        $var_543 = [];
        foreach ($var_542 as $var_544) {
            $split = explode("=", $var_544, 2);
            $var_545 = Class_19::urlDecode($split[0]);
            $value = isset($split[1]) ? Class_19::urlDecode($split[1]) : "";
            if (isset($var_543[$var_545])) {
                if (is_scalar($var_543[$var_545])) {
                    $var_543[$var_545] = [$var_543[$var_545]];
                }
                $var_543[$var_545][] = $value;
            } else {
                $var_543[$var_545] = $value;
            }
        }
        return $var_543;
    }
    public static function buildQueryString($params)
    {
        if (!$params) {
            return "";
        }
        $var_546 = Class_19::urlEncodeRFC3986(array_keys($params));
        $var_547 = Class_19::urlEncodeRFC3986(array_values($params));
        $params = array_combine($var_546, $var_547);
        uksort($params, "strcmp");
        $var_542 = [];
        foreach ($params as $var_545 => $value) {
            if (is_array($value)) {
                sort($value, SORT_STRING);
                foreach ($value as $var_548) {
                    $var_542[] = $var_545 . "=" . $var_548;
                }
            } else {
                $var_542[] = $var_545 . "=" . $value;
            }
        }
        return implode("&", $var_542);
    }
}
class Class_17
{
    public $httpOptions = NULL;
    private $signatureMethod = NULL;
    private $consumer = NULL;
    private $token = NULL;
    public static $cacheExpire = 1800;
    public static $cacheDir = NULL;
    const API_URL = "http://api.twitter.com/1.1/";
    const ME = 1;
    const ME_AND_FRIENDS = 2;
    const REPLIES = 3;
    const RETWEETS = 128;
    public function __construct($consumerKey, $consumerSecret, $accessToken = NULL, $accessTokenSecret = NULL)
    {
        if (!extension_loaded("curl")) {
            throw new Class_27("PHP extension CURL is not loaded.");
        }
        $this->$signatureMethod = new Class_21();
        $this->$consumer = new Class_16($consumerKey, $consumerSecret);
        $this->$token = new Class_16($accessToken, $accessTokenSecret);
    }
    public function verifyCredentials()
    {
        try {
            $res = $this->apiCall("account/verify_credentials", "GET");
            return !empty($res->id);
        } catch (Class_27 $var_549) {
            if ($var_549->var_550() === 401) {
                return false;
            }
            throw $var_549;
        }
    }
    public function postTweet($message)
    {
        return $this->apiCall("statuses/update", "POST", ["status" => $message]);
    }
    public function getTimeline($flags = self::ME, $count = 20, $data = NULL)
    {
        if (!isset($var_551[$flags & 3])) {
            throw new var_552();
        }
        return $this->cachedApiCall("statuses/" . $var_551[$flags & 3], (array) $data + ["count" => $count, "include_rts" => $flags & 128 ? 1 : 0]);
    }
    public function getUserInfo($user)
    {
        return $this->cachedApiCall("users/show", ["screen_name" => $user]);
    }
    public function deleteTweet($id)
    {
        $res = $this->apiCall("statuses/destroy/" . $id, "GET");
        return $res->id ? $res->id : false;
    }
    public function searchTweets($query)
    {
        return $this->apiCall("search/tweets", "GET", is_array($query) ? $query : ["q" => $query])->statuses;
    }
    public function apiCall($resource, $method, $data = NULL)
    {
        if (!strpos($resource, "://")) {
            if (!strpos($resource, ".")) {
                $resource .= ".json";
            }
            $resource = "http://api.twitter.com/1.1/" . $resource;
        }
        foreach (array_keys((array) $data, NULL, true) as $key) {
            unset($data[$key]);
        }
        $request = Class_24::forSignedRequest($this->consumer, $this->token, $method, $resource, $data);
        $request->signRequest($this->signatureMethod, $this->consumer, $this->token);
        $var_553 = [CURLOPT_HEADER => false, CURLOPT_RETURNTRANSFER => true] + ($method === "POST" ? [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $request->getQueryString(), CURLOPT_URL => $request->getNormalizedUrl()] : [CURLOPT_URL => $request->toUrl()]) + $this->httpOptions;
        $var_554 = curl_init();
        curl_setopt_array($var_554, $var_553);
        $result = curl_exec($var_554);
        if (curl_errno($var_554)) {
            throw new Class_27("Server error: " . curl_error($var_554));
        }
        $var_555 = 0 <= version_compare(PHP_VERSION, "5.4.0") ? @json_decode($result, false, 128, JSON_BIGINT_AS_STRING) : @json_decode($result);
        if ($var_555 === false) {
            throw new Class_27("Invalid server response");
        }
        $var_342 = curl_getinfo($var_554, CURLINFO_HTTP_CODE);
        if (400 <= $var_342) {
            throw new Class_27(isset($var_555->errors[0]->message) ? $var_555->errors[0]->message : "Server error #" . $var_342, $var_342);
        }
        return $var_555;
    }
    public function cachedApiCall($resource, $data = NULL, $cacheExpire = NULL)
    {
        if (!self::$cacheDir) {
            return $this->apiCall($resource, "GET", $data);
        }
        if ($cacheExpire === NULL) {
            $cacheExpire = self::$cacheExpire;
        }
        $var_556 = self::$cacheDir . "/twitter." . md5($resource . json_encode($data) . serialize([$this->consumer, $this->token]));
        $var_393 = @json_decode(@file_get_contents($var_556));
        if ($var_393 && time() < @filemtime($var_556) + $cacheExpire) {
            return $var_393;
        }
        try {
            $var_555 = $this->apiCall($resource, "GET", $data);
            file_put_contents($var_556, json_encode($var_555));
            return $var_555;
        } catch (Class_27 $var_549) {
            if ($var_393) {
                return $var_393;
            }
            throw $var_549;
        }
    }
    public static function formatTweetEntities($status)
    {
        if (!is_object($status)) {
            trigger_error("Twitter::clickable() has been changed; pass as parameter status object, not just text.", 512);
            return preg_replace_callback("~(?<!\\w)(https?://\\S+\\w|www\\.\\S+\\w|@\\w+|#\\w+)|[<>&]~u", ["Twitter", "clickableCallback"], html_entity_decode($status, ENT_QUOTES, "UTF-8"));
        }
        $var_557 = [];
        foreach ($status->entities->hashtags as $var_558) {
            $var_557[$var_558->indices[0]] = ["http://twitter.com/search?$q = %23" . $var_558->text, "#" . $var_558->text, $var_558->indices[1]];
        }
        foreach ($status->entities->urls as $var_558) {
            if (!isset($var_558->expanded_url)) {
                $var_557[$var_558->indices[0]] = [$var_558->url, $var_558->url, $var_558->indices[1]];
            } else {
                $var_557[$var_558->indices[0]] = [$var_558->expanded_url, $var_558->display_url, $var_558->indices[1]];
            }
        }
        foreach ($status->entities->user_mentions as $var_558) {
            $var_557[$var_558->indices[0]] = ["http://twitter.com/" . $var_558->screen_name, "@" . $var_558->screen_name, $var_558->indices[1]];
        }
        krsort($var_557);
        $var_559 = $status->text;
        foreach ($var_557 as $pos => $var_558) {
            $var_559 = iconv_substr($var_559, 0, $pos, "UTF-8") . "<a $href = \"" . htmlspecialchars($var_558[0]) . "\">" . htmlspecialchars($var_558[1]) . "</a>" . iconv_substr($var_559, $var_558[2], iconv_strlen($var_559, "UTF-8"), "UTF-8");
        }
        return $var_559;
    }
    private static function formatEntityMatch($m)
    {
        $m = htmlspecialchars($m[0]);
        if ($m[0] === "#") {
            $m = substr($m, 1);
            return "<a $href = 'http://twitter.com/search?$q = %23" . $m . "'>#" . $m . "</a>";
        }
        if ($m[0] === "@") {
            $m = substr($m, 1);
            return "@<a $href = 'http://twitter.com/" . $m . "'>" . $m . "</a>";
        }
        if ($m[0] === "w") {
            return "<a $href = 'http://" . $m . "'>" . $m . "</a>";
        }
        if ($m[0] === "h") {
            return "<a $href = '" . $m . "'>" . $m . "</a>";
        }
        return $m;
    }
}
class Class_27 extends Exception
{
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
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>