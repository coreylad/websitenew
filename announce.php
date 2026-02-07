<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

error_reporting(32759);
ini_set("display_errors", 0);
define("THIS_SCRIPT", "announce.php");
define("IN_ANNOUNCE", true);
define("TSDIR", dirname(__FILE__));
require TSDIR . "/include/php_default_timezone_set.php";
require TSDIR . "/include/config_announce.php";
require TSDIR . "/include/languages/" . $defaultlanguage . "/announce.lang.php";
if ($xbt_active == "yes") {
    if (!isset($xbt_announce_url) || empty($xbt_announce_url)) {
        stop($l["xbt_error1"]);
    } else {
        if (isset($_SERVER["QUERY_STRING"]) && !empty($_SERVER["QUERY_STRING"]) && preg_match("@passkey\\=(\\w{32})@isU", $_SERVER["QUERY_STRING"], $Result)) {
            if (isset($Result[1]) && strlen($Result[1]) == 32) {
                header("Location: " . $xbt_announce_url . "/" . $Result[1] . "/announce?" . $_SERVER["QUERY_STRING"]);
            } else {
                stop($l["xbt_error2"]);
            }
        } else {
            stop($l["xbt_error3"]);
        }
    }
    exit;
}
if ($announce_system == "no") {
    stop($l["offline"]);
}
$compact = isset($_GET["compact"]) ? 0 + $_GET["compact"] : 0;
$peer_id = isset($_GET["peer_id"]) ? $_GET["peer_id"] : "";
$port = isset($_GET["port"]) ? 0 + $_GET["port"] : "";
$event = isset($_GET["event"]) ? $_GET["event"] : "";
$downloaded = isset($_GET["downloaded"]) ? 0 + $_GET["downloaded"] : "";
$uploaded = isset($_GET["uploaded"]) ? 0 + $_GET["uploaded"] : "";
$left = isset($_GET["left"]) ? 0 + $_GET["left"] : "";
$numwant = isset($_GET["numwant"]) ? 0 + $_GET["numwant"] : (isset($_GET["num_want"]) ? 0 + $_GET["num_want"] : (isset($_GET["num want"]) ? 0 + $_GET["num want"] : 0));
if ($event == "stopped" && $numwant) {
    $numwant = 0;
}
if ($event != "stopped" && !$numwant) {
    $numwant = 50;
}
if (100 < $numwant) {
    $numwant = 100;
}
$update_user = $update_torrent = $update_snatched = [];
$ORJ_Get_Passkey = isset($_GET["passkey"]) ? $_GET["passkey"] : "";
if ($ORJ_Get_Passkey && strpos($ORJ_Get_Passkey, "?")) {
    $chop = $ORJ_Get_Passkey;
    $delim = "?";
    $half = strtok($chop, $delim);
    $onehalf = [];
    while (is_string($half)) {
        if ($half) {
            $onehalf[] = $half;
        }
        $half = strtok($delim);
    }
    unset($chop);
    unset($delim);
    unset($half);
    $_GET["passkey"] = $onehalf[0];
    $delim2 = "=";
    $hash = strtok($onehalf[1], $delim2);
    $onehash = [];
    while (is_string($hash)) {
        if ($hash) {
            $onehash[] = $hash;
        }
        $hash = strtok($delim2);
    }
    $_GET["info_hash"] = $onehash[1];
    unset($onehalf);
    unset($delim2);
    unset($hash);
    unset($onehash);
}
$passkey = isset($_GET["passkey"]) ? $_GET["passkey"] : "";
$info_hash = isset($_GET["info_hash"]) ? $_GET["info_hash"] : "";
if (strlen($passkey) == 32 && strlen($info_hash) == 20 && strlen($peer_id) == 20 && 0 < $port && $port < 65535) {
    if ($passkey && $passkey == "tssespecialtorrentv1byxamsep2007") {
        stop($l["registerfirst"] . $BASEURL . "/signup.php");
    }
} else {
    if (strlen($info_hash) != 20 && strpos($ORJ_Get_Passkey, "?")) {
        $passkey = $ORJ_Get_Passkey;
        $tmp = substr($passkey, strpos($passkey, "?"));
        $passkey = substr($passkey, 0, strpos($passkey, "?"));
        $tmpname = substr($tmp, 1, strpos($tmp, "=") - 1);
        $tmpvalue = substr($tmp, strpos($tmp, "=") + 1);
        $GLOBALS[$tmpname] = $tmpvalue;
        if (strlen($info_hash) != 20) {
            stop($l["error"]);
        }
    } else {
        stop($l["error"]);
    }
}
if (isset($_SERVER["HTTP_X_SUCURI_CLIENTIP"])) {
    $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_X_SUCURI_CLIENTIP"];
}
$ip = trim(htmlspecialchars($_SERVER["REMOTE_ADDR"]));
$agent = trim(htmlspecialchars($_SERVER["HTTP_USER_AGENT"]));
$seeder = $left == 0 ? "yes" : "no";
if ($bannedclientdetect == "yes") {
    if (isset($_SERVER["HTTP_ACCEPT"]) && $_SERVER["HTTP_ACCEPT"] == "text/html, */*" && $_SERVER["HTTP_ACCEPT_ENCODING"] == "identity") {
        stop($l["bannedclient"]);
    } else {
        if (trim($allowed_clients) != "" && !in_array(substr($peer_id, 0, 8), explode(",", trim($allowed_clients)))) {
            stop($l["bannedclient"]);
        }
    }
}
$UseMemcached = false;
if (isset($memcached_enabled) && $memcached_enabled == "yes" && isset($memcached_host) && !empty($memcached_host) && isset($memcached_port) && intval($memcached_port) && class_exists("Memcache", false)) {
    require TSDIR . "/include/class_ts_memcache.php";
    $TSMemcache = new TSMemcache($memcached_host, $memcached_port, true);
    $UseMemcached = true;
}
require TSDIR . "/include/config_database.php";
$GLOBALS["DatabaseConnect"] = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
if (!$GLOBALS["DatabaseConnect"]) {
    stop($l["cerror"]);
}
$_SERVER["REQUEST_TIME"] = time();
$query = "\n\t\t\t\t\tSELECT u.id as userid, u.enabled, u.ip, u.uploaded, u.downloaded, u.birthday,\n\t\t\t\t\tg.isbanned, g.candownload, g.canfreeleech, g.waitlimit, g.slotlimit,\n\t\t\t\t\tsb.sb_port , sb.sb_ipaddress\n \t\t\t\t\tFROM users u\n\t\t\t\t\tINNER JOIN usergroups g ON (u.`usergroup` = g.gid)\n\t\t\t\t\tLEFT JOIN ts_seedboxes sb ON (sb.$sb_userid = u.id)\n\t\t\t\t\tWHERE u.$torrent_pass = " . sqlesc($passkey) . "\n\t\t\t\t\tLIMIT 1";
if ($UseMemcached) {
    if (!($UserResult = $TSMemcache->check($passkey))) {
        ($query = mysqli_query($GLOBALS["DatabaseConnect"], $query)) || stop($l["sqlerror"] . " U-Query: 1");
        $UserResult = mysqli_fetch_assoc($query);
        $TSMemcache->add($passkey, $UserResult);
    }
} else {
    ($query = mysqli_query($GLOBALS["DatabaseConnect"], $query)) || stop($l["sqlerror"] . " U-Query: 1");
    $UserResult = mysqli_fetch_assoc($query);
}
if ($UserResult["enabled"] != "yes" || $UserResult["isbanned"] != "no") {
    stop($l["qerror1"]);
}
$query = "\n\t\t\t\t\tSELECT id, name, category, size, added, visible, banned, owner, free, silver, doubleupload, moderate, seeders, leechers, times_completed FROM torrents\n\t\t\t\t\tWHERE " . hash_where("info_hash", $info_hash) . "\n\t\t\t\t\tLIMIT 1";
if ($UseMemcached) {
    $hash = md5($info_hash);
    if (!($TorrentResult = $TSMemcache->check($hash))) {
        ($query = mysqli_query($GLOBALS["DatabaseConnect"], $query)) || stop($l["sqlerror"] . " T-Query: 1");
        $TorrentResult = mysqli_fetch_assoc($query);
        $TSMemcache->add($hash, $TorrentResult);
    }
} else {
    ($query = mysqli_query($GLOBALS["DatabaseConnect"], $query)) || stop($l["sqlerror"] . " T-Query: 1");
    $TorrentResult = mysqli_fetch_assoc($query);
}
if (!($Tid = $TorrentResult["id"]) || $TorrentResult["moderate"] == "1" || $TorrentResult["banned"] != "no") {
    stop($l["qerror2"]);
}
$Result = array_merge($TorrentResult, $UserResult);
unset($TorrentResult);
unset($UserResult);
$Result["ip"] = trim($Result["ip"]);
$Result["sb_ipaddress"] = trim($Result["sb_ipaddress"]);
$Result["sb_port"] = trim($Result["sb_port"]);
if ($checkip == "yes") {
    $CorrectIPFound = false;
    if ($ip == $Result["ip"]) {
        $CorrectIPFound = true;
    } else {
        if ($Result["sb_ipaddress"] && $Result["sb_port"]) {
            if (strpos($Result["sb_port"], "-") !== false) {
                $portrange = explode("-", trim($Result["sb_port"]));
                $CorrectPortFound = false;
                if ($portrange[0] && $portrange[1]) {
                    $i = $portrange[0];
                    while ($i <= $portrange[1]) {
                        if ($i == $port) {
                            $CorrectPortFound = true;
                        } else {
                            $i++;
                        }
                    }
                }
                if ($CorrectPortFound && $Result["sb_ipaddress"] == $ip) {
                    $CorrectIPFound = true;
                }
            } else {
                if ($Result["sb_port"] == $port && $Result["sb_ipaddress"] == $ip) {
                    $CorrectIPFound = true;
                }
            }
        }
    }
    if (!$CorrectIPFound) {
        stop($l["invalidip"]);
    }
}
if ($detectbrowsercheats == "yes" && isset($_SERVER["HTTP_COOKIE"]) && isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
    send_action("This user tried to cheat with a browser!", true);
    stop($l["invalidagent"]);
}
$fields = "peer_id, ip, port, uploaded, downloaded, seeder, last_action, (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(last_action)) AS announcetime, UNIX_TIMESTAMP(prev_action) AS prevts, connectable, userid";
$gp_eq = $nc == "yes" ? " AND $connectable = 'yes'" : "";
$wantseeds = $seeder == "yes" ? " AND $seeder = 'no'" : "";
$resp = "d8:completei" . $Result["seeders"] . "e10:downloadedi" . $Result["times_completed"] . "e10:incompletei" . $Result["leechers"] . "e8:intervali" . $announce_interval . "e12:min intervali" . $announce_interval . ($privatetrackerpatch == "yes" && $compact != 1 ? "e7:privatei1" : "") . "e5:peers" . ($compact != 1 ? "l" : "");
$peer = [];
$peer_num = 0;
$query_peers = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT " . $fields . " FROM peers WHERE $torrent = " . sqlesc($Tid) . $wantseeds . $gp_eq . " ORDER BY last_action DESC LIMIT " . $numwant);
if ($compact != 1) {
    while ($result_peers = mysqli_fetch_assoc($query_peers)) {
        $result_peers["peer_id"] = str_pad($result_peers["peer_id"], 20);
        if ($result_peers["peer_id"] === $peer_id) {
            $self = $result_peers;
        } else {
            $resp .= "d" . benc_str("ip") . benc_str($result_peers["ip"]);
            if (!$_GET["no_peer_id"]) {
                $resp .= benc_str("peer id") . benc_str($result_peers["peer_id"]);
            }
            $resp .= benc_str("port") . "i" . $result_peers["port"] . "e" . "e";
        }
    }
    $resp .= "ee";
} else {
    while ($result_peers = mysqli_fetch_assoc($query_peers)) {
        $peer_ip = explode(".", $result_peers["ip"]);
        $peer_ip = pack("C*", $peer_ip[0], $peer_ip[1], $peer_ip[2], $peer_ip[3]);
        $peer_port = pack("n*", (int) $result_peers["port"]);
        $time = intval(time() % 7680 / 60);
        if ($left == 0) {
            $time += 128;
        }
        $time = pack("C", $time);
        $peer[] = $time . $peer_ip . $peer_port;
        $peer_num++;
    }
    $o = "";
    for ($i = 0; $i < $peer_num; $i++) {
        $o .= substr($peer[$i], 1, 6);
    }
    $resp .= strlen($o) . ":" . $o . "e";
    unset($peer);
}
$selfwhere = "torrent = '" . $Tid . "' AND " . hash_where("peer_id", $peer_id);
if (!isset($self)) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT " . $fields . " FROM peers WHERE " . $selfwhere);
    if (mysqli_num_rows($query)) {
        $self = mysqli_fetch_assoc($query);
    }
}
if (isset($self) && 0 < $announce_wait && $_SERVER["REQUEST_TIME"] - $announce_wait < $self["prevts"]) {
    stop($l["antispam"] . $announce_wait);
}
if (!isset($self)) {
    if ($seeder != "yes") {
        if ($Result["candownload"] != "yes" && $Result["owner"] != $Result["userid"]) {
            stop($l["dlerror"]);
        }
        if (intval($Result["waitlimit"]) && $Result["owner"] != $Result["userid"]) {
            $elapsed = @floor(($_SERVER["REQUEST_TIME"] - @strtotime($Result["added"])) / 3600);
            if ($elapsed <= $Result["waitlimit"]) {
                stop($l["werror"] . " (" . ($Result["waitlimit"] - $elapsed) . $l["hour"] . ")");
            }
        }
        if (intval($Result["slotlimit"]) && $Result["owner"] != $Result["userid"]) {
            ($res = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM peers WHERE `userid` = " . sqlesc($Result["userid"]) . " AND $seeder = 'no'")) || stop($l["sqlerror"] . " P1");
            if (($totalactivetorrents = mysqli_num_rows($res)) && $Result["slotlimit"] <= $totalactivetorrents) {
                stop($l["merror"] . $Result["slotlimit"]);
            }
        }
    }
} else {
    require_once TSDIR . "/" . $cache . "/freeleech.php";
    $TIMENOW = date("Y-m-d H:i:s");
    if ($__F_START < $TIMENOW && $TIMENOW < $__F_END) {
        switch ($__FLSTYPE) {
            case "freeleech":
                $Result["free"] = "yes";
                $Result["canfreeleech"] = "yes";
                break;
            case "silverleech":
                $Result["silver"] = "yes";
                break;
            case "doubleupload":
                $Result["doubleupload"] = "yes";
                break;
        }
    }
    unset($__F_START);
    unset($__F_END);
    unset($__FLSTYPE);
    unset($TIMENOW);
    if ($bdayreward == "yes" && $bdayrewardtype && $Result["birthday"]) {
        $curuserbday = explode("-", $Result["birthday"]);
        if (date("j-n") == $curuserbday[0] . "-" . $curuserbday[1]) {
            switch ($bdayrewardtype) {
                case "freeleech":
                    $Result["free"] = "yes";
                    $Result["canfreeleech"] = "yes";
                    break;
                case "silverleech":
                    $Result["silver"] = "yes";
                    break;
                case "doubleupload":
                    $Result["doubleupload"] = "yes";
                    break;
            }
        }
    }
    unset($curuserbday);
    unset($bdayreward);
    unset($bdayrewardtype);
    $realupload = max(0, $uploaded - $self["uploaded"]);
    $upthis = $Result["doubleupload"] == "yes" ? $realupload * 2 : $realupload;
    $downthis = max(0, $downloaded - $self["downloaded"]);
    $upspeed = 0 < $realupload ? $realupload / $self["announcetime"] : 0;
    $downspeed = 0 < $downthis ? $downthis / $self["announcetime"] : 0;
    $announcetime = $self["seeder"] == "yes" ? "seedtime = seedtime + " . $self["announcetime"] : "leechtime = leechtime + " . $self["announcetime"];
    if (0 < $upthis || 0 < $downthis) {
        if (536870912 < $realupload && $aggressivecheat == "yes") {
            send_action("There was no Leecher on this torrent however this user uploaded " . $realupload . " bytes, which might be a cheat attempt with a cheat software such as Ratio Maker, Ratio Faker etc..");
        }
        $dled = $Result["silver"] == "yes" && 1 < $downthis ? $downthis / 2 : $downthis;
        if (0 < $upthis) {
            $update_user[] = "uploaded = uploaded + " . $upthis;
        }
        if (0 < $dled && $Result["free"] != "yes" && $Result["canfreeleech"] != "yes") {
            $update_user[] = "downloaded = downloaded + " . $dled;
        }
    }
    if ($max_rate < $upspeed && $aggressivecheat == "yes") {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO cheat_attempts (added, uid, agent, transfer_rate, beforeup, upthis, timediff, ip, torrentid) VALUES(NOW(), " . $Result["userid"] . ", " . sqlesc($agent) . ", " . sqlesc($upspeed) . ", " . sqlesc($Result["uploaded"]) . ", " . sqlesc($realupload) . ", " . sqlesc($self["announcetime"]) . ", " . sqlesc($ip) . ", " . sqlesc($Tid) . ")") || stop($l["sqlerror"] . " C1");
    }
}
if ($event == "stopped") {
    if (isset($self)) {
        if ($snatchmod == "yes") {
            $update_snatched[] = "seeder = 'no'";
            $update_snatched[] = "connectable = 'no'";
            $update_snatched[] = "last_action = NOW()";
            $update_snatched[] = "port = " . $port;
            $update_snatched[] = "agent = " . sqlesc($agent);
            $update_snatched[] = $announcetime;
            if (0 < $upspeed) {
                $update_snatched[] = "upspeed = '" . $upspeed . "'";
            }
            if (0 < $downspeed) {
                $update_snatched[] = "downspeed = '" . $downspeed . "'";
            }
            $update_snatched[] = "ip = " . sqlesc($ip);
            $update_snatched[] = "uploaded = uploaded + " . $realupload;
            $update_snatched[] = "downloaded = downloaded +" . $downthis;
            $update_snatched[] = "to_go = " . $left;
        }
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM peers WHERE " . $selfwhere);
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $update_torrent[] = $self["seeder"] == "yes" ? "seeders = IF(seeders > 0, seeders - 1, 0)" : "leechers = IF(leechers > 0, leechers - 1, 0)";
        }
    }
} else {
    if ($event == "completed") {
        if ($snatchmod == "yes") {
            $update_snatched[] = "finished = 'yes'";
            $update_snatched[] = "completedat = NOW()";
        }
        $update_torrent[] = "times_completed = times_completed + 1";
    }
    if (isset($self)) {
        $connectable = $self["connectable"] == "yes" ? "yes" : checkconnect($ip, $port);
        if ($snatchmod == "yes") {
            $update_snatched[] = "seeder = '" . $seeder . "'";
            $update_snatched[] = "connectable = '" . $connectable . "'";
            $update_snatched[] = "last_action = NOW()";
            $update_snatched[] = "port = " . $port;
            $update_snatched[] = "agent = " . sqlesc($agent);
            $update_snatched[] = $announcetime;
            if (0 < $upspeed) {
                $update_snatched[] = "upspeed = '" . $upspeed . "'";
            }
            if (0 < $downspeed) {
                $update_snatched[] = "downspeed = '" . $downspeed . "'";
            }
            $update_snatched[] = "ip = " . sqlesc($ip);
            $update_snatched[] = "uploaded = uploaded + " . $realupload;
            $update_snatched[] = "downloaded = downloaded + " . $downthis;
            $update_snatched[] = "to_go = " . $left;
        }
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE peers SET $uploaded = " . $uploaded . ", $downloaded = " . $downloaded . ", $to_go = " . $left . ", $last_action = NOW(), $prev_action = '" . $self["last_action"] . "', $seeder = '" . $seeder . "'" . ($seeder == "yes" && $self["seeder"] != $seeder ? ", $finishedat = " . $_SERVER["REQUEST_TIME"] : "") . " WHERE " . $selfwhere);
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"]) && $self["seeder"] != $seeder) {
            if ($seeder == "yes") {
                $update_torrent[] = "seeders = seeders + 1";
                $update_torrent[] = "leechers = IF(leechers > 0, leechers - 1, 0)";
            } else {
                $update_torrent[] = "leechers = leechers + 1";
                $update_torrent[] = "seeders = IF(seeders > 0, seeders - 1, 0)";
            }
        }
    } else {
        if (in_array($port, explode(",", $banned_ports))) {
            stop($l["invalidport"]);
        }
        $connectable = checkconnect($ip, $port);
        if ($nc == "yes" && $connectable == "no") {
            stop($l["conerror"]);
        }
        if ($snatchmod == "yes") {
            $res = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM snatched WHERE `torrentid` = " . sqlesc($Tid) . " AND $userid = " . sqlesc($Result["userid"]));
            if (!mysqli_num_rows($res)) {
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO snatched (torrentid, userid, port, startdat, last_action, agent, ip) VALUES (" . $Tid . ", " . $Result["userid"] . ", " . $port . ", NOW(), NOW(), " . sqlesc($agent) . ", " . sqlesc($ip) . ")");
            }
        }
        $ret = mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO peers (connectable, torrent, peer_id, ip, port, uploaded, downloaded, to_go, started, last_action, seeder, userid, agent, uploadoffset, downloadoffset, passkey) VALUES ('" . $connectable . "', " . $Tid . ", " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", " . $port . ", " . $uploaded . ", " . $downloaded . ", " . $left . ", NOW(), NOW(), '" . $seeder . "', " . $Result["userid"] . ", " . sqlesc($agent) . ", " . $uploaded . ", " . $downloaded . ", " . sqlesc($passkey) . ")");
        if ($ret) {
            $update_torrent[] = $seeder == "yes" ? "seeders = seeders + 1" : "leechers = leechers + 1";
        }
    }
}
if (0 < $kpsseed && $seeder == "yes" && ($bonus == "enable" || $bonus == "disablesave")) {
    if ($kpstype == "time" && isset($self["announcetime"]) && $announce_interval - 10 < $self["announcetime"]) {
        $update_user[] = "seedbonus = seedbonus + " . $kpsseed;
    } else {
        if (isset($realupload) && $kpsgbamount * 1024 * 1024 * 1024 < $realupload) {
            $multipler = round($realupload / ($kpsgbamount * 1024 * 1024 * 1024));
            $update_user[] = "seedbonus = seedbonus + " . $kpsseed * $multipler;
        }
    }
}
if ($seeder == "yes") {
    if ($Result["visible"] == "no") {
        $update_torrent[] = "visible = 'yes'";
    }
    $update_torrent[] = "`mtime` = '" . $_SERVER["REQUEST_TIME"] . "'";
}
if ($update_torrent && count($update_torrent)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET " . implode(", ", $update_torrent) . " WHERE `id` = '" . $Tid . "'");
    unset($update_torrent);
}
if ($update_user && count($update_user) && isset($self)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET " . implode(",", $update_user) . " WHERE `id` = " . sqlesc($Result["userid"]));
    unset($update_user);
}
if ($update_snatched && count($update_snatched)) {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE snatched SET " . implode(", ", $update_snatched) . " WHERE `torrentid` = '" . $Tid . "' AND $userid = '" . $Result["userid"] . "'");
    unset($update_snatched);
}
header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
if ($compact != 1 && isset($_SERVER["HTTP_ACCEPT_ENCODING"]) && $_SERVER["HTTP_ACCEPT_ENCODING"] == "gzip" && $gzipcompress == "yes") {
    header("Content-type: text/html; $charset = " . $charset);
    header("Content-Encoding: gzip");
    $resp = gzencode($resp, 9, FORCE_GZIP);
} else {
    if ($compact) {
        header("Content-Type: text/plain; $charset = " . $charset);
    } else {
        header("Content-type: text/html; $charset = " . $charset);
    }
}
exit($resp);
function hash_where($name, $hash)
{
    $shhash = preg_replace("/ *\$/s", "", $hash);
    return "(" . $name . " = " . sqlesc($hash) . " OR " . $name . " = " . sqlesc($shhash) . ")";
}
function benc_str($s)
{
    return strlen($s) . ":" . $s;
}
function checkconnect($host, $port)
{
    global $A_checkconnectable;
    if ($A_checkconnectable == "no") {
        return "yes";
    }
    if ($fp = @fsockopen($host, $port, $errno, $errstr, 5)) {
        @fclose($fp);
        return "yes";
    }
    return "no";
}
function Stop($msg)
{
    header("Content-Type: text/plain");
    header("Pragma: no-cache");
    exit("d14:failure reason" . strlen($msg) . ":" . $msg . "e");
}
function sqlesc($value)
{
    if (@get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "'";
}
function send_action($actionmessage, $resetpasskey = false)
{
    global $announce_actions;
    global $Tid;
    global $Result;
    global $ip;
    global $passkey;
    if ($announce_actions != "yes") {
        return NULL;
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO announce_actions (torrentid, userid, ip, passkey, actionmessage, actiontime) VALUES (" . implode(",", array_map("sqlesc", [$Tid, $Result["userid"], $ip, $passkey, $actionmessage, $_SERVER["REQUEST_TIME"]])) . ")");
    if ($resetpasskey) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $torrent_pass = '' WHERE $torrent_pass = " . sqlesc($passkey));
    }
}

?>