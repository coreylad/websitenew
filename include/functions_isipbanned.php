<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function IsValidIp($IP)
{
    return preg_match("/^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\$/", $IP) ? true : false;
}
function IsIpBanned($against = "")
{
    global $aggressivecheckip;
    global $UseMemcached;
    if ($UseMemcached) {
        global $TSMemcache;
        if (!($ipbancache = $TSMemcache->check("ipbans"))) {
            $Query = sql_query("SELECT `value` FROM ipbans WHERE `id` = 1");
            $ipbancache = mysqli_fetch_assoc($Query);
            $TSMemcache->add("ipbans", $ipbancache);
        }
    } else {
        $Query = sql_query("SELECT `value` FROM ipbans WHERE `id` = 1");
        $ipbancache = mysqli_fetch_assoc($Query);
    }
    if (isset($ipbancache) && is_array($ipbancache) && trim($ipbancache["value"]) != "") {
        $banarray = @explode(" ", @preg_replace("/\\s\\s+/", " ", @trim($ipbancache["value"])));
        if (is_array($banarray) && count($banarray)) {
            $gethostbyaddr = $aggressivecheckip == "yes" && isvalidip($against) ? @gethostbyaddr($against) : "";
            foreach ($banarray as $cban) {
                if (strpos($cban, "*") === false) {
                    if ($cban === $against || @strstr($against, $cban)) {
                        return true;
                    }
                    if ($aggressivecheckip == "yes" && ($cban === $gethostbyaddr || @strstr($gethostbyaddr, $cban))) {
                        return true;
                    }
                } else {
                    $regexp = str_replace([".", "*"], ["\\.", ".+"], $cban);
                    if (preg_match("@^" . $regexp . "\$@is", $against)) {
                        return true;
                    }
                    if ($aggressivecheckip == "yes" && preg_match("@^" . $regexp . "\$@is", $gethostbyaddr)) {
                        return true;
                    }
                }
            }
            unset($banarray);
            unset($gethostbyaddr);
            unset($cban);
            unset($regexp);
        }
    }
    return false;
}

?>