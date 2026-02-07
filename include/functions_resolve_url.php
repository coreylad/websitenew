<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function resolve_request_url()
{
    $port = intval($_SERVER["SERVER_PORT"]);
    $port = in_array($port, [80, 443]) ? "" : ":" . $port;
    $scheme = ":443" == $port || isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off" ? "https://" : "http://";
    $host = fetch_server_value("HTTP_HOST");
    $name = fetch_server_value("SERVER_NAME");
    $host = substr_count($name, ".") < substr_count($host, ".") ? $host : $name;
    $query = ($query = fetch_server_value("QUERY_STRING")) ? "?" . $query : "";
    if (!($scriptpath = fetch_server_value("REQUEST_URI")) && !($scriptpath = fetch_server_value("UNENCODED_URL"))) {
        $scriptpath = fetch_server_value("HTTP_X_REWRITE_URL");
    }
    if ($scriptpath) {
        if ($scriptpath) {
            $query = "";
        }
    } else {
        if (!($scriptpath = fetch_server_value("PATH_INFO")) && !($scriptpath = fetch_server_value("REDIRECT_URL")) && !($scriptpath = fetch_server_value("URL")) && !($scriptpath = fetch_server_value("PHP_SELF"))) {
            $scriptpath = fetch_server_value("SCRIPT_NAME");
        }
    }
    $url = $scheme . $host . "/" . ltrim($scriptpath, "/\\") . $query;
    define("TSSE_URL", $url);
    $url_info = @parse_url(TSSE_URL);
    $url_info["path"] = "/" . ltrim($url_info["path"], "/\\");
    $url_info["query_raw"] = isset($url_info["query"]) ? $url_info["query"] : "";
    $url_info["query"] = strip_sessionhash($url_info["query"]);
    $url_info["query"] = trim($url_info["query"], "?&") ? $url_info["query"] : "";
    if ((PHP_SAPI == "cgi" || PHP_SAPI == "cgi-fcgi") && isset($_SERVER["ORIG_SCRIPT_NAME"]) && !empty($_SERVER["ORIG_SCRIPT_NAME"])) {
        if (substr($_SERVER["SCRIPT_NAME"], 0, strlen($_SERVER["ORIG_SCRIPT_NAME"])) == $_SERVER["ORIG_SCRIPT_NAME"]) {
            $url_info["script"] = $_SERVER["ORIG_SCRIPT_NAME"];
        } else {
            $url_info["script"] = $_SERVER["SCRIPT_NAME"];
        }
    } else {
        $url_info["script"] = isset($_SERVER["ORIG_SCRIPT_NAME"]) && !empty($_SERVER["ORIG_SCRIPT_NAME"]) ? $_SERVER["ORIG_SCRIPT_NAME"] : $_SERVER["SCRIPT_NAME"];
    }
    $url_info["script"] = "/" . ltrim($url_info["script"], "/\\");
    define("TSSE_HTTP_HOST", $url_info["host"]);
}
function fetch_server_value($name)
{
    if (isset($_SERVER[$name]) && $_SERVER[$name]) {
        return $_SERVER[$name];
    }
    if (isset($_ENV[$name]) && $_ENV[$name]) {
        return $_ENV[$name];
    }
    return false;
}
function strip_sessionhash($string)
{
    $string = preg_replace("/(s|sessionhash)=[a-z0-9]{32}?&?/", "", $string);
    return $string;
}

?>