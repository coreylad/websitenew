<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.1 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$uptime = "\r\nCurrent Server Uptime: " . TS_ServerUpTime() . "<br />\r\nCurrent Server Load: " . TS_ServerLoad() . "<br />\r\nCurrent Memory Usage: " . mksize(memory_get_usage());
function TS_ServerUpTime()
{
    if (strtolower(substr(PHP_OS, 0, 3)) === "win") {
        return "Unknown";
    }
    if (function_exists("shell_exec")) {
        $data = shell_exec("uptime");
        $uptime = explode(" up ", $data);
        $uptime = explode(",", $uptime[1]);
        $uptime = $uptime[0] . ", " . $uptime[1];
        return $uptime;
    }
    return "Unknown";
}
function TS_ServerLoad()
{
    if (strtolower(substr(PHP_OS, 0, 3)) === "win") {
        if (class_exists("COM")) {
            $wmi = new COM("WinMgmts:\\\\.");
            $cpus = $wmi->InstancesOf("Win32_Processor");
            $cpuload = 0;
            $i = 0;
            foreach ($cpus as $cpu) {
                $cpuload += $cpu->LoadPercentage;
                $i++;
            }
            $cpuload = round($cpuload / $i, 2);
            return $cpuload . "%";
        } else {
            return "Unknown";
        }
    } else {
        if (file_exists("/proc/loadavg") && ($load = file_get_contents("/proc/loadavg"))) {
            $load = explode(" ", $load);
            return $load[0] . " - " . $load[1] . " - " . $load[2];
        }
        if (function_exists("exec") && ($loadresult = @exec("uptime"))) {
            preg_match("/averages?: ([0-9\\.]+),[\\s]+([0-9\\.]+),[\\s]+([0-9\\.]+)/", $loadresult, $avgs);
            $uptime = explode(" up ", $loadresult);
            $uptime = explode(",", $uptime[1]);
            $uptime = $uptime[0] . ", " . $uptime[1];
            $data = "Server Load Averages " . $avgs[1] . ", " . $avgs[2] . ", " . $avgs[3] . "\n";
            $data .= "Server Uptime " . $uptime;
            return $data;
        }
        return "Unknown";
    }
}

?>