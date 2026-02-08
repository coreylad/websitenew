<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function CheckForProxy($ip)
{
    global $lang;
    $scan_headers = ["HTTP_VIA", "HTTP_X_FORWARDED_FOR", "HTTP_FORWARDED_FOR", "HTTP_X_FORWARDED", "HTTP_FORWARDED", "HTTP_CLIENT_IP", "HTTP_FORWARDED_FOR_IP", "VIA", "X_FORWARDED_FOR", "FORWARDED_FOR", "X_FORWARDED", "FORWARDED", "CLIENT_IP", "FORWARDED_FOR_IP", "HTTP_PROXY_CONNECTION"];
    $proxyDetected = false;
    foreach ($scan_headers as $header) {
        if (isset($_SERVER[$header]) && $_SERVER[$header]) {
            $proxyDetected = true;
        }
    }
    if (!$proxyDetected && in_array($_SERVER["REMOTE_PORT"], [8080, 80, 6588, 8000, 3128, 553, 554])) {
        $proxyDetected = true;
    }
    if (!$proxyDetected && @fsockopen($_SERVER["REMOTE_ADDR"], 80, $errno, $errstr, 2)) {
        $proxyDetected = true;
    }
    if ($proxyDetected) {
        stderr($lang->global["error"], $lang->global["proxydetected"]);
    }
}

?>