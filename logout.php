<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */


$userIpHash = md5(getUserIpAddress());
if (isset($_GET["logouthash"]) && $_GET["logouthash"] === $userIpHash) {
    setcookie("c_secure_uid", "", 2147483647, "/");
    setcookie("c_secure_pass", "", 2147483647, "/");
    header("Location: index.php?logout=true");
    exit;
}
exit("HASH Error! Please click <a href=\"./logout.php?logouthash=" . $userIpHash . "\">here</a> to try again.");

function getUserIpAddress()
{
    $detectedIp = false;
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $detectedIp = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ipList = explode(", ", $_SERVER["HTTP_X_FORWARDED_FOR"]);
        if ($detectedIp) {
            array_unshift($ipList, $detectedIp);
            $detectedIp = false;
        }
        foreach ($ipList as $ipAddress) {
            if (!preg_match("/^(?:10|172\\.(?:1[6-9]|2\\d|3[01])|192\\.168)\\./", $ipAddress)) {
                if (version_compare(phpversion(), "5.0.0", ">=")) {
                    if (ip2long($ipAddress)) {
                        $detectedIp = $ipAddress;
                    }
                } else {
                    if (ip2long($ipAddress) != -1) {
                        $detectedIp = $ipAddress;
                    }
                }
            }
        }
    }
    return htmlspecialchars($detectedIp ? $detectedIp : $_SERVER["REMOTE_ADDR"]);
}

?>