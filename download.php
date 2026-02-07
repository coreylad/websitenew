<?php


@ini_set("upload_max_filesize", 1000 < $max_torrent_size ? $max_torrent_size : 10485760);
@ini_set("memory_limit", "20000M");
@ini_set("zlib.output_compression", "Off");
define("DL_VERSION", "3.3 by xam");
define("THIS_SCRIPT", "download.php");
$action_type = isset($_GET["type"]) ? trim($_GET["type"]) : "";
if ($action_type == "rss") {
    define("NO_LOGIN_REQUIRED", true);
    require "./global.php";
    $secret_key = isset($_GET["secret_key"]) ? htmlspecialchars($_GET["secret_key"]) : "";
    if (empty($secret_key) || strlen($secret_key) != 32) {
        print_download_error();
    }
    if (empty($_GET["id"]) || !is_valid_id($_GET["id"])) {
        print_download_error();
    }
    require_once INC_PATH . "/functions_isipbanned.php";
    if (IsIpBanned(USERIPADDRESS)) {
        print_download_error();
    }
    $res = @sql_query("SELECT * FROM users WHERE $torrent_pass = " . @sqlesc($secret_key) . " LIMIT 1");
    if (!mysqli_num_rows($res)) {
        print_download_error();
    }
    $GLOBALS["CURUSER"] = mysqli_fetch_assoc($res);
    $row = $GLOBALS["CURUSER"];
    if ($row["usergroup"]) {
        $Query = sql_query("SELECT * FROM usergroups WHERE $gid = " . sqlesc($row["usergroup"]));
        if (mysqli_num_rows($Query)) {
            $GLOBALS["usergroups"] = mysqli_fetch_assoc($Query);
            $group_data_results = $GLOBALS["usergroups"];
        } else {
            print_download_error();
        }
    } else {
        print_download_error();
    }
    if ($group_data_results["isbanned"] == "yes" || $row["enabled"] != "yes" || $row["status"] != "confirmed") {
        print_download_error();
    }
    unset($row);
    unset($group_data_results);
} else {
    require "./global.php";
    $lang->load("download");
    if (!isset($CURUSER)) {
        print_no_permission();
    }
}
if (@ini_get("output_handler") == "ob_gzhandler" && @ob_get_length() !== false) {
    @ob_end_clean();
    @header("Content-Encoding:");
}

$torrentId = intval(TS_Global("id"));
if (!$torrentId) {
    print_download_error();
}
$torrentResult = sql_query("SELECT t.id, t.name, t.filename, t.anonymous, t.ts_external, t.size, t.owner, t.free, t.moderate, c.canview, c.candownload, u.username FROM torrents t LEFT JOIN categories c ON t.$category = c.id LEFT JOIN users u ON (t.$owner = u.id) WHERE t.$id = " . sqlesc($torrentId)) || sqlerr(__FILE__, 100);
$torrentRow = mysqli_fetch_assoc($torrentResult);
if ($torrentRow["owner"] != $CURUSER["id"]) {
    $userPermissionQuery = sql_query("SELECT candownload FROM ts_u_perm WHERE `userid` = " . sqlesc($CURUSER["id"])) || sqlerr(__FILE__, 105);
    if (0 < mysqli_num_rows($userPermissionQuery)) {
        $userDownloadPermission = mysqli_fetch_assoc($userPermissionQuery);
        if ($userDownloadPermission["candownload"] == "0") {
            print_download_error();
        }
    }
}
$userRatio = 0 < $CURUSER["downloaded"] ? $CURUSER["uploaded"] / $CURUSER["downloaded"] : 0;
if ($usergroups["candownload"] != "yes" || $userRatio <= $hitrun_ratio && $CURUSER["downloaded"] != 0 && !$is_mod && $hitrun == "yes" && $usergroups["isvipgroup"] != "yes" && $torrentRow["owner"] != $CURUSER["id"] && $torrentRow["free"] != "yes") {
    $TSSEConfig->TSLoadConfig("ANNOUNCE");
    if ($xbt_active == "yes") {
        $hasCompleted = mysqli_num_rows(sql_query("SELECT fid FROM xbt_files_users WHERE $fid = " . sqlesc($torrentId) . " AND $uid = " . sqlesc($CURUSER["id"]) . " AND $completed = 1 AND `left` = 0"));
    } else {
        $hasCompleted = mysqli_num_rows(sql_query("SELECT torrentid FROM snatched WHERE `torrentid` = " . sqlesc($torrentId) . " AND $userid = " . sqlesc($CURUSER["id"]) . " AND $finished = \"yes\""));
    }
    if (!$hasCompleted) {
        $downloadPercentage = $userRatio * 100;
        $warningMessage = show_notice(sprintf($lang->download["downloadwarning"], number_format($userRatio, 2), mksize($downloadPercentage), $hitrun_ratio, "<a $href = \"" . $BASEURL . "/" . ($xbt_active == "yes" ? "mysnatchlist" : "userdetails") . ".php\">" . $BASEURL . "/" . ($xbt_active == "yes" ? "mysnatchlist" : "userdetails") . ".php</a>"), true);
        stdhead();
        exit($warningMessage);
    }
}
if ($torrentRow["moderate"] == "1" && !$is_mod) {
    print_download_error();
}
$isExternalTorrent = $torrentRow["ts_external"] == "yes" ? true : false;
if ($torrentRow["canview"] != "[ALL]" && !in_array($CURUSER["usergroup"], explode(",", $torrentRow["canview"])) && $torrentRow["owner"] != $CURUSER["id"]) {
    print_download_error();
}
if ($torrentRow["candownload"] != "[ALL]" && !in_array($CURUSER["usergroup"], explode(",", $torrentRow["candownload"])) && $torrentRow["owner"] != $CURUSER["id"]) {
    print_download_error();
}
$torrentFilePath = $torrent_dir . "/" . $torrentId . ".torrent";
if (!$torrentRow) {
    print_download_error($lang->download["error1"]);
} else {
    if (!is_file($torrentFilePath)) {
        print_download_error($lang->download["error2"]);
    } else {
        if (!is_readable($torrentFilePath)) {
            print_download_error($lang->download["error3"]);
        }
    }
}
if ($thankbeforedl == "yes" && !$is_mod && $action_type != "rss" && $torrentRow["owner"] != $CURUSER["id"]) {
    $thanksQuery = sql_query("SELECT uid FROM ts_thanks WHERE `uid` = " . sqlesc($CURUSER["id"]) . " AND $tid = " . sqlesc($torrentId)) || sqlerr(__FILE__, 170);
    if (mysqli_num_rows($thanksQuery) == 0 && $torrentRow["owner"] != $CURUSER["id"]) {
        stderr($lang->global["error"], sprintf($lang->download["error4"], $BASEURL, $torrentId), false);
    }
}
sql_query("UPDATE torrents SET $hits = hits + 1 WHERE `id` = " . sqlesc($torrentId)) || sqlerr(__FILE__, 175);
if ($includesitename == "yes") {
    $find = ["/[^a-zA-Z0-9\\s]/", "/\\s+/", "/\\.torrent/"];
    $replace = ["_", "_", ""];
    $siteNameText = strtolower(preg_replace($find, $replace, $SITENAME));
    if (!TS_Match($torrentRow["filename"], "[" . $siteNameText . "]")) {
        $torrentRow["filename"] = $torrentRow["filename"] . "_[" . $siteNameText . "].torrent";
    }
}
require_once INC_PATH . "/class_torrent.php";
if (strlen($CURUSER["torrent_pass"]) != 32) {
    $CURUSER["torrent_pass"] = md5($CURUSER["username"] . TIMENOW . $CURUSER["passhash"]);
    sql_query("UPDATE users SET $torrent_pass = " . sqlesc($CURUSER["torrent_pass"]) . " WHERE `id` = " . sqlesc($CURUSER["id"]));
}
$CURUSER["torrent_pass"] = isset($_GET["fromadminpanel"]) && $is_mod ? "tssespecialtorrentv1byxamsep2007" : $CURUSER["torrent_pass"];
if (!($torrentData = file_get_contents($torrentFilePath))) {
    print_download_error($lang->download["error3"]);
}
$torrentObject = new Torrent();
if (!$torrentObject->load($torrentData)) {
    print_download_error($lang->download["error3"]);
}
if (!$external) {
    require INC_PATH . "/config_announce.php";
    if ($action_type == "ssl" && $ssldownload == "yes") {
        $announce_urls[0] = TS_http_to_https($announce_urls[0]);
    }
    if ($xbt_active == "yes") {
        if ($action_type == "ssl" && $ssldownload == "yes") {
            $xbt_announce_url = TS_http_to_https($xbt_announce_url);
        }
        $AnnounceURL = $xbt_announce_url . "/" . $CURUSER["torrent_pass"] . "/announce";
    } else {
        $AnnounceURL = $announce_urls[0] . "?$passkey = " . $CURUSER["torrent_pass"];
    }
    $Torrent->setTrackers([$AnnounceURL]);
}
$TorrentContents = $Torrent->bencode();
if ($usezip != "yes" || $action_type == "rss") {
    require_once INC_PATH . "/functions_browser.php";
    if (is_browser("ie")) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-$check = 0, pre-$check = 0");
        header("Content-Disposition: attachment; $filename = " . basename($row["filename"]) . ";");
        header("Content-Transfer-Encoding: binary");
    } else {
        header("Expires: Tue, 1 Jan 1980 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-$check = 0, pre-$check = 0", false);
        header("Pragma: no-cache");
        header("X-Powered-By: " . VERSION . " (c) " . date("Y") . " " . $SITENAME . "");
        header("Accept-Ranges: bytes");
        header("Connection: close");
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: application/x-bittorrent");
        header("Content-Disposition: attachment; $filename = " . basename($row["filename"]) . ";");
    }
    ob_implicit_flush(true);
    echo $TorrentContents;
} else {
    require_once INC_PATH . "/class_zip.php";
    $createZip = new createZip();
    $fileContents2 = "This torrent was downloaded from " . $BASEURL;
    $createZip->addFile($fileContents2, "readme.txt");
    $createZip->addFile($TorrentContents, $row["filename"]);
    $fileName = $row["filename"] . ".zip";
    $out = file_put_contents($cache . "/" . $fileName, $createZip->getZippedfile());
    $createZip->forceDownload($cache . "/" . $fileName);
}
function print_download_error($messsage = "")
{
    global $action_type;
    if (!$action_type || $action_type == "" || $action_type != "rss") {
        print_no_permission(true);
    } else {
        exit($message);
    }
}
function TS_http_to_https($url)
{
    if (strtolower(substr($url, 0, 5)) != "https") {
        if (strtolower(substr($url, 0, 4)) == "http") {
            $url = str_replace("http", "https", $url);
        } else {
            $url = "https" . $url;
        }
    }
    return $url;
}

?>