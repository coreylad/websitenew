<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (@ini_get("output_handler") == "ob_gzhandler" && @ob_get_length() !== false) {
    @ob_end_clean();
    header("Content-Encoding:");
}
$rootpath = "./../";
define("THIS_SCRIPT", "attachment.php");
require $rootpath . "global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("tsf_forums");
if ($usergroups["candownload"] != "yes") {
    print_no_permission();
    exit;
}
$tid = intval(TS_Global("tid"));
$pid = intval(TS_Global("pid"));
$aid = intval(TS_Global("aid"));
if ($usergroups["isforummod"] == "yes" || $usergroups["cansettingspanel"] == "yes" || $usergroups["issupermod"] == "yes") {
    $moderator = true;
} else {
    $moderator = false;
}
$permissions = forum_permissions();
if (isset($_GET["viewattachments"]) && is_valid_id($tid)) {
    $query = sql_query("SELECT tid, fid FROM " . TSF_PREFIX . "threads WHERE tid=" . sqlesc($tid));
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    $thread = mysqli_fetch_assoc($query);
    if (!$moderator && ($permissions[$thread["fid"]]["canview"] != "yes" || $permissions[$thread["fid"]]["canviewthreads"] != "yes")) {
        print_no_permission(true);
        exit;
    }
    $a_query = sql_query("SELECT * FROM " . TSF_PREFIX . "attachments WHERE a_tid = " . sqlesc($tid));
    $columns = "";
    require_once INC_PATH . "/functions_get_file_icon.php";
    while ($attachment = mysqli_fetch_assoc($a_query)) {
        $columns .= "\r\n\t\t<tr>\r\n\t\t\t<td>" . ($attachment["visible"] == 1 ? get_file_icon($attachment["a_name"]) : "<img src=\"" . $BASEURL . "/tsf_forums/images/moderation.png\" alt=\"" . $lang->tsf_forums["moderatemsg9"] . "\" title=\"" . $lang->tsf_forums["moderatemsg9"] . "\" border=\"0\" class=\"inlineimg\" />") . " " . ($attachment["visible"] == 1 || $moderator ? "<a href=\"" . $BASEURL . "/tsf_forums/attachment.php?aid=" . intval($attachment["a_id"]) . "&amp;pid=" . intval($attachment["a_pid"]) . "&amp;tid=" . intval($attachment["a_tid"]) . "\" target=\"_blank\">" . htmlspecialchars_uni($attachment["a_name"]) . "</a>" : $lang->tsf_forums["moderatemsg9"]) . "</td>\r\n\t\t\t<td>" . mksize($attachment["a_size"]) . "</td>\r\n\t\t\t<td>" . ts_nf($attachment["a_count"]) . "</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $defaulttemplate = ts_template();
    echo "\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html xmlns=\"https://www.w3.org/1999/xhtml\">\r\n\t<head profile=\"https://gmpg.org/xfn/11\">\r\n\t<title>" . $lang->tsf_forums["a_info"] . "</title>\r\n\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $charset . "\" />\r\n\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $BASEURL . "/style.php?theme=" . $defaulttemplate . "&style=style.css\" />\r\n\t<link rel=\"shortcut icon\" href=\"/favicon.ico\" type=\"image/x-icon\" />\r\n\t</head>\r\n\t<body>\r\n\t\t<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\" colspan=\"3\">" . $lang->tsf_forums["a_info"] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\">" . $lang->tsf_forums["attachment"] . "</td>\r\n\t\t\t\t<td class=\"subheader\">" . $lang->tsf_forums["a_size"] . "</td>\r\n\t\t\t\t<td class=\"subheader\">" . $lang->tsf_forums["a_count"] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t" . $columns . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td colspan=\"3\" align=\"center\"><a href=\"javascript:void(0);\" onclick=\"opener.location=('showthread.php?tid=" . $tid . "'); self.close();\"><strong>" . $lang->tsf_forums["showandclose"] . "</strong></a></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t</body>\r\n\t</html>\r\n\t";
    exit;
}
if (!is_valid_id($aid) || !is_valid_id($pid)) {
    stderr($lang->global["error"], $lang->tsf_forums["a_error1"]);
    exit;
}
($query = sql_query("SELECT a.a_name, a.visible, a.a_pid, p.uid as posterid, p.fid\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "attachments a\r\n\t\t\t\t\t\t\tLEFT JOIN " . TSF_PREFIX . "posts p ON (a.a_pid=p.pid)\r\n\t\t\t\t\t\t\tWHERE p.pid = " . sqlesc($pid) . " AND a.a_id = " . sqlesc($aid))) || sqlerr(__FILE__, 132);
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["a_error1"]);
    exit;
}
$attachment = mysqli_fetch_assoc($query);
if (empty($attachment["a_name"])) {
    stderr($lang->global["error"], $lang->tsf_forums["a_error1"]);
    exit;
}
if (!$moderator && $permissions[$attachment["fid"]]["canview"] != "yes") {
    print_no_permission(true);
    exit;
}
if (!$moderator && $attachment["visible"] == 0) {
    print_no_permission(true);
    exit;
}
if ($f_thanksbeforedl == "yes" && $attachment["posterid"] != $CURUSER["id"]) {
    $query = sql_query("SELECT uid FROM " . TSF_PREFIX . "thanks WHERE pid = " . sqlesc($attachment["a_pid"]) . " AND uid = " . sqlesc($CURUSER["id"]));
    if (!mysqli_num_rows($query)) {
        stderr($lang->global["error"], $lang->tsf_forums["thanksbeforedl"]);
        exit;
    }
}
$mtype = "";
$filename = @rawurlencode(@basename($attachment["a_name"]));
$filename = @str_replace(["\"", "'", "\\", "/"], "", $filename);
$file_path = $f_upload_path . $filename;
if (!file_exists($file_path)) {
    stderr($lang->global["error"], $lang->tsf_forums["a_error1"]);
    exit;
}
$filesize = @filesize($file_path);
if (function_exists("mime_content_type")) {
    $mtype = mime_content_type($file_path);
} else {
    if (function_exists("finfo_file")) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mtype = finfo_file($finfo, $file_path);
        finfo_close($finfo);
    }
}
if ($mtype == "") {
    $mtype = "application/force-download";
}
sql_query("UPDATE " . TSF_PREFIX . "attachments SET a_count = a_count + 1 WHERE a_pid = " . sqlesc($pid) . " AND a_id = " . sqlesc($aid));
$extension = strtolower(get_extension($attachment["a_name"]));
$imagetypes = ["jpg", "bmp", "png", "gif", "jpeg"];
if (in_array($extension, $imagetypes)) {
    require_once INC_PATH . "/functions_browser.php";
    $filedata = file_get_contents($file_path);
    if (is_browser("ie")) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Disposition: inline; filename=" . basename($filename) . ";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . strlen($filedata) . "");
        header("Content-type: image/" . $extension . "");
    } else {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-disposition: inline; filename=" . $filename);
        header("Content-transfer-encoding: binary");
        header("Content-Length: " . strlen($filedata) . "");
        header("Content-type: image/" . $extension . "");
    }
    echo $filedata;
    exit;
}
require_once INC_PATH . "/functions_browser.php";
if (is_browser("ie")) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Disposition: attachment; filename=" . basename($filename) . ";");
    header("Content-Transfer-Encoding: binary");
} else {
    header("Expires: Tue, 1 Jan 1980 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("X-Powered-By: " . VERSION . " (c) " . date("Y") . " " . $SITENAME);
    header("Accept-Ranges: bytes");
    header("Connection: close");
    header("Content-Transfer-Encoding: binary");
    header("Content-Description: File Transfer");
    header("Content-Type: " . $mtype);
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    header("Content-Length: " . $filesize);
}
ob_implicit_flush(true);
$file = @fopen($file_path, "rb");
if ($file) {
    while (!feof($file)) {
        echo fread($file, 8192);
        flush();
        if (connection_status() != 0) {
            @fclose($file);
            exit;
        }
    }
    @fclose($file);
}
function forum_permissions()
{
    global $CURUSER;
    $permissions = [];
    if (isset($CURUSER) && $CURUSER["usergroup"]) {
        ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "forumpermissions WHERE gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 277);
        if (mysqli_num_rows($query)) {
            while ($perm = mysqli_fetch_assoc($query)) {
                $permissions[$perm["fid"]] = $perm;
            }
        }
    }
    return $permissions;
}

?>