<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

@error_reporting(32759);
@ini_set("display_errors", 0);
@ini_set("display_startup_errors", 0);
@ini_set("pcre.backtrack_limit", -1);
@set_time_limit(0);
if (!defined("DEBUGMODE")) {
    $GLOBALS["ts_start_time"] = array_sum(explode(" ", microtime()));
}
define("DEMOMODE", false);
define("GLOBAL_LOADED", true);
define("IN_TRACKER", true);
define("O_SCRIPT_VERSION", "7.5");
define("TSDIR", dirname(__FILE__));
define("INC_PATH", TSDIR . "/include");
$rootpath = isset($rootpath) ? $rootpath : TSDIR;
require INC_PATH . "/php_default_timezone_set.php";
define("TIMENOW", time());
require_once INC_PATH . "/class_ts_database.php";
$TSDatabase = new TSDatabase();
$TSDatabase->connect();
require_once INC_PATH . "/class_config.php";
$TSSEConfig = new TSConfig();
include_once INC_PATH . "/class_cache.php";
$TSSECache = new TSSECache();
if (defined("THIS_SCRIPT") && THIS_SCRIPT === "index.php") {
    $TSSEConfig->TSLoadConfig(["MAIN", "SECURITY", "TWEAK", "EXTRA", "THEME", "DATETIME", "FORUMCP", "PLUGIN"]);
} else {
    $TSSEConfig->TSLoadConfig(["MAIN", "SECURITY", "TWEAK", "EXTRA", "THEME", "DATETIME", "FORUMCP"]);
}
if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" || $_SERVER["SERVER_PORT"] == 443) {
    if (substr($BASEURL, 0, 7) == "http://") {
        $BASEURL = str_replace(substr($BASEURL, 0, 7), "https://", $BASEURL);
    } else {
        if (substr($BASEURL, 0, 4) == "www.") {
            $BASEURL = str_replace(substr($BASEURL, 0, 4), "https://www.", $BASEURL);
        }
    }
    if (substr($pic_base_url, 0, 7) == "http://") {
        $pic_base_url = str_replace(substr($pic_base_url, 0, 7), "https://", $pic_base_url);
    } else {
        if (substr($pic_base_url, 0, 4) == "www.") {
            $pic_base_url = str_replace(substr($pic_base_url, 0, 4), "https://www.", $pic_base_url);
        }
    }
}
$UseMemcached = false;
if (isset($memcached_enabled) && $memcached_enabled == "yes" && isset($memcached_host) && !empty($memcached_host) && isset($memcached_port) && intval($memcached_port) && class_exists("Memcache", false)) {
    require_once INC_PATH . "/class_ts_memcache.php";
    $TSMemcache = new TSMemcache($memcached_host, $memcached_port);
    $UseMemcached = true;
}
$trackerdefaulttemplate = $defaulttemplate;
$announce_urls = explode(",", $announce_urls);
if ($gzipcompress == "yes" && extension_loaded("zlib") && ini_get("zlib.output_compression") != "1" && ini_get("output_handler") != "ob_gzhandler") {
    @ob_start("ob_gzhandler");
}
if (!defined("IN_FORUMS")) {
    $navbits = [];
    $navbits[0]["name"] = $SITENAME;
    $navbits[0]["url"] = $BASEURL . "/index.php";
}
$tscollapse = [];
if (isset($_COOKIE["ts_collapse"]) && !empty($_COOKIE["ts_collapse"])) {
    $val = preg_split("#\\n#", $_COOKIE["ts_collapse"], -1, PREG_SPLIT_NO_EMPTY);
    foreach ($val as $key) {
        $tscollapse["collapseobj_" . htmlspecialchars($key)] = "display:none;";
        $tscollapse["collapseimg_" . htmlspecialchars($key)] = "_collapsed";
        $tscollapse["collapsecel_" . htmlspecialchars($key)] = "_collapsed";
    }
    unset($val);
}
require_once INC_PATH . "/init.php";
require_once INC_PATH . "/user_options.php";
require_once INC_PATH . "/ts_functions.php";
require_once INC_PATH . "/functions.php";
require_once INC_PATH . "/class_language.php";
require_once INC_PATH . "/functions_tsseo.php";
define("USERIPADDRESS", TSDetectUserIP());
$lang = new trackerlanguage();
$lang->set_path(INC_PATH . "/languages");
if (empty($_COOKIE["ts_language"]) || !file_exists(INC_PATH . "/languages/" . $_COOKIE["ts_language"])) {
    $lang->set_language($defaultlanguage);
} else {
    $lang->set_language($_COOKIE["ts_language"]);
}
$lang->load("global");
if ($ctracker == "yes" && !defined("IN_AJAX")) {
    require_once INC_PATH . "/ctracker.php";
}
$useragent = isset($_SERVER["HTTP_USER_AGENT"]) ? htmlspecialchars_uni(strtolower($_SERVER["HTTP_USER_AGENT"])) : "";
$querystring = isset($_SERVER["QUERY_STRING"]) ? "?" . htmlspecialchars_uni($_SERVER["QUERY_STRING"]) : "";
$page = htmlspecialchars_uni($_SERVER["SCRIPT_NAME"]);
$FID = 0;
if (defined("IN_FORUMS") && preg_match("#tsf_forums\\/showthread\\.php\\?$tid = ([0-9]+)#is", $page . $querystring, $Found)) {
    $Query = sql_query("SELECT fid FROM " . TSF_PREFIX . "threads WHERE $tid = " . sqlesc(intval($Found[1])));
    if (mysqli_num_rows($Query)) {
        $Result = mysqli_fetch_assoc($Query);
        $FID = $Result["fid"];
    }
}
TSBoot(USERIPADDRESS);
$is_mod = isset($usergroups) && isset($CURUSER) ? is_mod($usergroups) : false;
require_once INC_PATH . "/ts_error_handler.php";
set_error_handler("TSSEErrorHandler");
sql_query("REPLACE INTO ts_sessions VALUES (\"" . md5(USERIPADDRESS) . "\", \"" . (isset($CURUSER) ? intval($CURUSER["id"]) : 0) . "\", " . sqlesc(USERIPADDRESS) . ", \"" . TIMENOW . "\", " . sqlesc(!defined("SKIP_LOCATION_SAVE") ? $page . $querystring : "/index.php") . ", " . sqlesc($useragent) . ", \"" . $FID . "\")") || sqlerr(__FILE__, 189);
if (strtoupper(substr(PHP_OS, 0, 3)) != "WIN" && 0 < $loadlimit) {
    if ($TSSECache->Cache["loadavg"]["lastupdate"] < TIMENOW - 300) {
        update_loadavg();
    }
    if ($loadlimit < $TSSECache->Cache["loadavg"]["content"]) {
        define("errorid", 6);
        include_once TSDIR . "/ts_error.php";
        exit("E6");
    }
}
if (!defined("DISABLE_IPBAN_SYSTEM")) {
    require_once INC_PATH . "/functions_isipbanned.php";
    if (IsIpBanned(USERIPADDRESS)) {
        define("errorid", 9);
        include_once TSDIR . "/ts_error.php";
        exit("E9");
    }
}
if (isset($_REQUEST["GLOBALS"]) || isset($_FILES["GLOBALS"])) {
    define("errorid", 1);
    include_once TSDIR . "/ts_error.php";
    exit("E1");
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    if (empty($_POST) && isset($_SERVER["CONTENT_LENGTH"]) && 0 < $_SERVER["CONTENT_LENGTH"]) {
        define("errorid", 10);
        include_once TSDIR . "/ts_error.php";
        exit("E10");
    }
    if (isset($CURUSER) && 0 < $CURUSER["id"] && defined("CSRF_PROTECTION") && CSRF_PROTECTION) {
        if (!in_array(THIS_SCRIPT, ["donate.php", "payment_gateway.php", "purchase.php"])) {
            function verify_security_token($request_token, $user_token)
            {
                if (empty($request_token) || !TS_Match($request_token, "-")) {
                    define("errorid", 11);
                    include_once TSDIR . "/ts_error.php";
                    exit("E11");
                }
                list($time, $token) = explode("-", $request_token);
                if ($token !== sha1($time . $user_token)) {
                    define("errorid", 12);
                    include_once TSDIR . "/ts_error.php";
                    exit("E12");
                }
                if ($time <= TIMENOW - 3600) {
                    define("errorid", 13);
                    include_once TSDIR . "/ts_error.php";
                    exit("E13");
                }
            }
            verify_security_token(isset($_POST["securitytoken"]) ? trim($_POST["securitytoken"]) : "", $CURUSER["securitytoken_raw"]);
        }
    } else {
        if ($BASEURL && isset($_SERVER["HTTP_REFERER"])) {
            $referrer_parts = @parse_url($_SERVER["HTTP_REFERER"]);
            $ref_port = isset($referrer_parts["port"]) ? intval($referrer_parts["port"]) : 0;
            $ref_host = (isset($referrer_parts["host"]) ? $referrer_parts["host"] : "") . (!empty($ref_port) && $ref_port != "80" ? ":" . $ref_port : "");
            $parseBaseURL = @parse_url($BASEURL);
            $http_host = $parseBaseURL["host"];
            $allowed = preg_split("#\\s+#", $allowedreferrers, -1, PREG_SPLIT_NO_EMPTY);
            $allowed[] = preg_replace("#^www\\.#i", "", $http_host);
            $allowed[] = ".paypal.com";
            $allowed[] = ".daopay.com";
            $pass_ref_check = false;
            foreach ($allowed as $host) {
                if (preg_match("#" . preg_quote($host, "#") . "\$#siU", $ref_host)) {
                    $pass_ref_check = true;
                    unset($allowed);
                    if (!$pass_ref_check) {
                        define("errorid", 2);
                        include_once TSDIR . "/ts_error.php";
                        exit("E2");
                    }
                }
            }
        }
    }
}
if (isset($usergroups) && $is_mod) {
    if ($UseMemcached) {
        if (!($staffcache = $TSMemcache->check("STAFFTEAM"))) {
            $Query = sql_query("SELECT `content` FROM `ts_config` WHERE $configname = \"STAFFTEAM\"");
            $Result = mysqli_fetch_assoc($Query);
            $staffcache = @explode(",", $Result["content"]);
            $TSMemcache->add("STAFFTEAM", $staffcache);
        }
    } else {
        $Query = sql_query("SELECT `content` FROM `ts_config` WHERE $configname = \"STAFFTEAM\"");
        $Result = mysqli_fetch_assoc($Query);
        $staffcache = @explode(",", $Result["content"]);
    }
    if (!in_array($CURUSER["username"] . ":" . $CURUSER["id"], $staffcache)) {
        require_once INC_PATH . "/functions_pm.php";
        $msg = "Fake Account Detected: Username: " . $CURUSER["username"] . " - UserID: " . $CURUSER["id"] . " - UserIP : " . USERIPADDRESS;
        send_pm(1, $msg, "Warning: Fake Account Detected!");
        write_log($msg);
        unset($msg);
        stderr($lang->global["error"], $lang->global["fakeaccount"]);
    }
}
if (isset($CURUSER) && TS_Match($CURUSER["options"], "A1") && (!defined("THIS_SCRIPT") || defined("THIS_SCRIPT") && THIS_SCRIPT != "usercp.php")) {
    redirect("usercp.php?$act = edit_details#account_parked");
    exit;
}
if (isset($_SERVER["HTTP_X_MOZ"]) && strpos($_SERVER["HTTP_X_MOZ"], "prefetch") !== false) {
    define("errorid", 7);
    include_once TSDIR . "/ts_error.php";
    exit("E7");
}
if (!defined("NO_LOGIN_REQUIRED") && !isset($CURUSER)) {
    if (!(isset($guestaccess) && $guestaccess == "yes")) {
        if (!defined("THIS_SCRIPT") || defined("THIS_SCRIPT") && THIS_SCRIPT != "index.php") {
            redirect("login.php?$returnto = " . (isset($_SERVER["REQUEST_URI"]) && !empty($_SERVER["REQUEST_URI"]) ? urlencode(fix_url($_SERVER["REQUEST_URI"])) : "index.php"));
            exit;
        }
    }
}
if (!defined("NO_LOGIN_REQUIRED") && !isset($CURUSER) && isset($guestaccess) && $guestaccess == "yes") {
    $query = sql_query("SELECT * FROM usergroups WHERE $gid = 3200");
    $GLOBALS["usergroups"] = mysqli_fetch_assoc($query);
    $tmp_time_g = get_date_time();
    $GLOBALS["CURUSER"]["id"] = "0";
    $GLOBALS["CURUSER"]["username"] = "Guest";
    $GLOBALS["CURUSER"]["passhash"] = "";
    $GLOBALS["CURUSER"]["secret"] = USERIPADDRESS;
    $GLOBALS["CURUSER"]["email"] = "";
    $GLOBALS["CURUSER"]["status"] = "confirmed";
    $GLOBALS["CURUSER"]["enabled"] = "yes";
    $GLOBALS["CURUSER"]["added"] = "";
    $GLOBALS["CURUSER"]["last_login"] = $tmp_time_g;
    $GLOBALS["CURUSER"]["last_access"] = $tmp_time_g;
    $GLOBALS["CURUSER"]["stylesheet"] = "";
    $GLOBALS["CURUSER"]["ip"] = USERIPADDRESS;
    $GLOBALS["CURUSER"]["uploaded"] = "0";
    $GLOBALS["CURUSER"]["downloaded"] = "0";
    $GLOBALS["CURUSER"]["title"] = "Guest";
    $GLOBALS["CURUSER"]["country"] = "";
    $GLOBALS["CURUSER"]["notifs"] = "";
    $GLOBALS["CURUSER"]["modcomment"] = "";
    $GLOBALS["CURUSER"]["donor"] = "no";
    $GLOBALS["CURUSER"]["warned"] = "no";
    $GLOBALS["CURUSER"]["warneduntil"] = "";
    $GLOBALS["CURUSER"]["torrentsperpage"] = "";
    $GLOBALS["CURUSER"]["torrent_pass"] = "";
    $GLOBALS["CURUSER"]["tzoffset"] = "";
    $GLOBALS["CURUSER"]["invites"] = "0";
    $GLOBALS["CURUSER"]["invited_by"] = "";
    $GLOBALS["CURUSER"]["seedbonus"] = "0";
    $GLOBALS["CURUSER"]["leechwarn"] = "no";
    $GLOBALS["CURUSER"]["leechwarnuntil"] = "";
    $GLOBALS["CURUSER"]["timeswarned"] = "0";
    $GLOBALS["CURUSER"]["page"] = $_SERVER["SCRIPT_NAME"];
    $GLOBALS["CURUSER"]["donated"] = "0";
    $GLOBALS["CURUSER"]["donoruntil"] = "";
    $GLOBALS["CURUSER"]["total_donated"] = "0";
    $GLOBALS["CURUSER"]["lastinvite"] = "";
    $GLOBALS["CURUSER"]["announce_read"] = "yes";
    $GLOBALS["CURUSER"]["usergroup"] = "3200";
    $GLOBALS["CURUSER"]["oldusergroup"] = "3200";
    $GLOBALS["CURUSER"]["last_forum_visit"] = $tmp_time_g;
    $GLOBALS["CURUSER"]["last_forum_active"] = $tmp_time_g;
    $GLOBALS["CURUSER"]["avatar"] = "";
    $GLOBALS["CURUSER"]["postsperpage"] = "";
    $GLOBALS["CURUSER"]["signature"] = "";
    $GLOBALS["CURUSER"]["totalposts"] = "0";
    $GLOBALS["CURUSER"]["birthday"] = "";
    $GLOBALS["CURUSER"]["visitorcount"] = "0";
    $GLOBALS["CURUSER"]["options"] = "A0B0C0D1E0F0G0H1I1K3L3M3N0O0P0R0S0Q0";
    $GLOBALS["CURUSER"]["pmunread"] = "0";
    $GLOBALS["CURUSER"]["speed"] = "";
    $GLOBALS["CURUSER"]["contact"] = "";
    $GLOBALS["CURUSER"]["mood"] = "";
    $GLOBALS["CURUSER"]["can_leech"] = "";
    $GLOBALS["CURUSER"]["wait_time"] = "";
    $GLOBALS["CURUSER"]["peers_limit"] = "";
    $GLOBALS["CURUSER"]["torrents_limit"] = "";
    $GLOBALS["CURUSER"]["torrent_pass_version"] = "";
    $GLOBALS["CURUSER"]["download_multiplier"] = "";
    $GLOBALS["CURUSER"]["upload_multiplier"] = "";
    $GLOBALS["CURUSER"]["securitytoken_raw"] = sha1($GLOBALS["CURUSER"]["id"] . sha1($GLOBALS["CURUSER"]["secret"]) . sha1($securehash));
    $GLOBALS["CURUSER"]["securitytoken"] = TIMENOW . "-" . sha1(TIMENOW . $GLOBALS["CURUSER"]["securitytoken_raw"]);
}

?>