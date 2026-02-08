<?php
define("THIS_SCRIPT", "ts_subtitles.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$action = htmlspecialchars_uni(TS_Global("action"));
define("TS_SUBTITLE_VERSION", "1.3.2 by xam");
$lang->load("ts_subtitles");
$canupload = $usergroups["canupload"] == "yes" ? true : false;
$candownload = $usergroups["candownload"] == "yes" ? true : false;
$errormessage = $title = "";
$allowed_file_types = TS_Match($subtitle_allowed_file_types, ",") ? explode(",", $subtitle_allowed_file_types) : (!empty($subtitle_allowed_file_types) ? [$subtitle_allowed_file_types] : ["rar", "zip"]);
$maxsize = $max_subtitle_size;
$userid = intval($CURUSER["id"]);
$username = htmlspecialchars_uni($CURUSER["username"]);
// Use descriptive variable names for clarity and PSR-12 compliance
$subtitleAction = isset($_GET["do"]) ? htmlspecialchars_uni($_GET["do"]) : (isset($_POST["do"]) ? htmlspecialchars_uni($_POST["do"]) : "");
$subtitleId = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : "");
$defaulttemplate = ts_template();
if ($action == "delete" && $is_mod && is_valid_id($subtitleId)) {
    $subtitleQuery = sql_query("SELECT title,filename FROM ts_subtitles WHERE `id` = " . sqlesc($subtitleId));
    if (0 < mysqli_num_rows($subtitleQuery)) {
        $subtitleResult = mysqli_fetch_assoc($subtitleQuery);
        $subtitleFilename = $subtitleResult["filename"];
        $subtitleTitle = htmlspecialchars_uni($subtitleResult["title"]);
        if (file_exists($torrent_dir . "/" . $subtitleFilename)) {
            @unlink($torrent_dir . "/" . $subtitleFilename);
        }
        sql_query("DELETE FROM ts_subtitles WHERE `id` = " . sqlesc($subtitleId));
        write_log("Subtitle: " . $subtitleTitle . " deleted by " . $username);
    }
}
if ($action == "edit" && is_valid_id($subtitleId)) {
    $subtitleQuery = sql_query("SELECT * FROM ts_subtitles WHERE `id` = " . sqlesc($subtitleId));
    $subtitleData = mysqli_fetch_assoc($subtitleQuery);
    $canEditSubtitle = $subtitleData["uid"] === $CURUSER["id"] ? true : false;
    if (mysqli_num_rows($subtitleQuery) == 0 || !$canEditSubtitle && !$is_mod) {
        print_no_permission();
    }
    if ($subtitleAction == "save" && empty($errormessage)) {
        $subtitleTitle = trim($_POST["title"]);
        $subtitleLanguage = intval($_POST["language"]);
        $subtitleCDs = intval($_POST["cds"]);
        $subtitleFPS = trim($_POST["fps"]);
        $subtitleTorrentId = intval($_POST["tid"]);
        if (empty($subtitleTitle) || empty($subtitleLanguage) || empty($subtitleCDs) || empty($subtitleFPS)) {
            $errormessage = $lang->global["dontleavefieldsblank"];
        } else {
            if ($subtitleTorrentId) {
                $torrentQuery = sql_query("SELECT id FROM torrents WHERE `id` = " . $subtitleTorrentId);
                if (mysqli_num_rows($torrentQuery) < 1) {
                    $errormessage = $lang->global["notorrentid"];
                }
            }
            if (!$errormessage) {
                sql_query("UPDATE ts_subtitles SET $title = " . sqlesc($subtitleTitle) . ", $language = " . sqlesc($subtitleLanguage) . ", $cds = " . sqlesc($subtitleCDs) . ", $fps = " . sqlesc($subtitleFPS) . ", $tid = " . $subtitleTorrentId . " WHERE `id` = " . sqlesc($subtitleId));
                header("Location: " . $BASEURL . $_SERVER["SCRIPT_NAME"] . "?$id = " . $subtitleId);
                exit;
            }
        }
    }
    stdhead($lang->ts_subtitles["head2"]);
    show_error();
    $countryOptions = "<option $value = '0'>---------------</option>\n";
    $countryQuery = sql_query("SELECT id,name FROM countries ORDER BY name") or ($countryQuery = sql_query("SELECT id,name FROM countries ORDER BY name")) || sqlerr(__FILE__, 138);
    while ($countryData = mysqli_fetch_assoc($countryQuery)) {
        $countryOptions .= "<option $value = '" . intval($countryData["id"]) . "'" . ($subtitleData["language"] == $countryData["id"] ? " $selected = 'selected'" : "") . ">" . htmlspecialchars_uni($countryData["name"]) . "</option>\n";
    }
    $cancelButton = [$lang->ts_subtitles["cancel"] => $BASEURL . $_SERVER["SCRIPT_NAME"]];
    echo jumpbutton($cancelButton) . "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t<input $type = \"hidden\" $name = \"action\" $value = \"edit\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save\" />\r\n\t<input $type = \"hidden\" $name = \"id\" $value = \"" . $subtitleId . "\" />\r\n\t<table $width = \"100%\" $border = \"0\" class=\"none\" $style = \"clear: both;\" $cellpadding = \"4\" $cellspacing = \"1\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" class=\"thead\">" . $lang->ts_subtitles["head2"] . "</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[1] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"title\" $size = \"60\" $value = \"" . htmlspecialchars_uni($subtitleData["title"]) . "\" /></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[2] . "</b></td>\r\n\t\t\t<td $align = \"left\"><select $name = \"language\">" . $countryOptions . "</select></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[3] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"fps\" $size = \"10\" $value = \"" . htmlspecialchars_uni($subtitleData["fps"]) . "\" /></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[4] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"cds\" $size = \"5\" $value = \"" . intval($subtitleData["cds"]) . "\" /></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles["torrent"] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"tid\" $size = \"5\" $value = \"" . intval($subtitleData["tid"]) . "\" /></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->ts_subtitles["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_subtitles["reset"] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t";
    stdfoot();
    exit;
}
if ($action == "upload" && ($canupload || $is_mod)) {
    $subtitleTitle = "";
    $subtitleLanguage = 0;
    $subtitleCDs = 0;
    $subtitleFPS = "";
    $subtitleTorrentId = 0;
    if ($subtitleAction == "save") {
        $subtitleTitle = trim($_POST["title"]);
        $subtitleLanguage = intval($_POST["language"]);
        $subtitleCDs = intval($_POST["cds"]);
        $subtitleFPS = trim($_POST["fps"]);
        $subtitleTorrentId = intval($_POST["tid"]);
        if (empty($subtitleTitle) || empty($subtitleLanguage) || empty($subtitleCDs) || empty($subtitleFPS)) {
            $errormessage = $lang->global["dontleavefieldsblank"];
        }
        $uploadedSubtitleFile = $_FILES["subtitlefile"];
        if (empty($uploadedSubtitleFile) || empty($uploadedSubtitleFile["name"]) || empty($uploadedSubtitleFile["tmp_name"]) || empty($uploadedSubtitleFile["size"]) || !is_uploaded_file($uploadedSubtitleFile["tmp_name"])) {
            $errormessage = $lang->ts_subtitles["uploaderror"];
        } else {
            if ($maxsize < filesize($uploadedSubtitleFile["tmp_name"])) {
                $errormessage = sprintf($lang->ts_subtitles["sizeerror"], mksize($maxsize));
            }
        }
        if ($subtitleTorrentId) {
            $torrentQuery = sql_query("SELECT id FROM torrents WHERE `id` = " . $subtitleTorrentId);
            if (mysqli_num_rows($torrentQuery) < 1) {
                $errormessage = $lang->global["notorrentid"];
            }
        }
        if (empty($errormessage)) {
            $subtitleFileExtension = strtolower(get_extension($uploadedSubtitleFile["name"]));
            if (!in_array($subtitleFileExtension, $allowed_file_types, true)) {
                $errormessage = $lang->ts_subtitles["uploaderror"];
            } else {
                if (file_exists($torrent_dir . "/" . $uploadedSubtitleFile["name"])) {
                    $errormessage = $lang->ts_subtitles["fileexists"];
                }
            }
            if (empty($errormessage)) {
                $subtitleFilename = ts_remove_whitespaces($uploadedSubtitleFile["name"]);
                if (move_uploaded_file($uploadedSubtitleFile["tmp_name"], $torrent_dir . "/" . $subtitleFilename)) {
                    $uploadDate = TIMENOW;
                    sql_query("INSERT INTO ts_subtitles (title,language,cds,fps,uid,date,filename,tid) VALUES (" . sqlesc($subtitleTitle) . ", " . sqlesc($subtitleLanguage) . ", " . sqlesc($subtitleCDs) . ", " . sqlesc($subtitleFPS) . ", " . sqlesc($userid) . ", " . sqlesc($uploadDate) . ", " . sqlesc($subtitleFilename) . "," . $subtitleTorrentId . ")");
                    $newSubtitleId = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                    write_log("Subtitle: " . $subtitleTitle . " uploaded by " . $username);
                    header("Location: " . $BASEURL . $_SERVER["SCRIPT_NAME"] . "?$id = " . $newSubtitleId);
                    exit;
                }
                $errormessage = $lang->ts_subtitles["uploaderror"];
            }
        }
    }
    stdhead($lang->ts_subtitles["upload"]);
    show_error();
    $countryOptions = "<option $value = '0'>---------------</option>\n";
    $countryQuery = sql_query("SELECT id,name FROM countries ORDER BY name") or ($countryQuery = sql_query("SELECT id,name FROM countries ORDER BY name")) || sqlerr(__FILE__, 268);
    while ($countryData = mysqli_fetch_assoc($countryQuery)) {
        $countryOptions .= "<option $value = '" . intval($countryData["id"]) . "'" . (isset($subtitleLanguage) && $subtitleLanguage == $countryData["id"] ? " $selected = 'selected'" : "") . ">" . htmlspecialchars_uni($countryData["name"]) . "</option>\n";
    }
    $cancelButton = [$lang->ts_subtitles["cancel"] => $BASEURL . $_SERVER["SCRIPT_NAME"]];
    echo jumpbutton($cancelButton) . "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\" $enctype = \"multipart/form-data\">\r\n\t<input $type = \"hidden\" $name = \"action\" $value = \"upload\" />\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save\" />\r\n\t<table $width = \"100%\" $border = \"0\" class=\"none\" $style = \"clear: both;\" $cellpadding = \"4\" $cellspacing = \"1\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" class=\"thead\">" . $lang->ts_subtitles["upload"] . "</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[1] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"title\" $size = \"60\" $value = \"" . htmlspecialchars_uni($subtitleTitle) . "\" /</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[2] . "</b></td>\r\n\t\t\t<td $align = \"left\"><select $name = \"language\">" . $countryOptions . "</select></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[3] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"fps\" $size = \"10\" $value = \"" . htmlspecialchars_uni($subtitleFPS) . "\" /></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[4] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"cds\" $size = \"5\" $value = \"" . intval($subtitleCDs) . "\" /></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles[7] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"file\" $name = \"subtitlefile\" $size = \"30\" /></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\"><b>" . $lang->ts_subtitles["torrent"] . "</b></td>\r\n\t\t\t<td $align = \"left\"><input $type = \"text\" $name = \"tid\" $size = \"5\" $value = \"" . $subtitleTorrentId . "\" /></td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->ts_subtitles["save"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_subtitles["reset"] . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t";
    stdfoot();
    exit;
}
if ($action == "download" && is_valid_id($id) && ($candownload || $is_mod)) {
    function download($name)
    {
        global $SITENAME;
        $status = false;
        $path = $name;
        if (!is_file($path) || connection_status() != 0) {
            return false;
        }
        require_once INC_PATH . "/functions_browser.php";
        if (is_browser("ie")) {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-$check = 0, pre-$check = 0");
            header("Content-Disposition: attachment; $filename = " . basename($name) . ";");
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
            header("Content-Type: application/force-download");
            header("Content-Length: " . filesize($path));
            header("Content-Disposition: attachment; $filename = " . basename($name) . ";");
        }
        ob_implicit_flush(true);
        if ($file = fopen($path, "rb")) {
            while (!feof($file) && connection_status() == 0) {
                echo fread($file, 8192);
                flush();
            }
            $status = connection_status() == 0;
            fclose($file);
        }
        return $status;
    }
    $subtitleFileQuery = sql_query("SELECT filename FROM ts_subtitles WHERE `id` = " . sqlesc($subtitleId));
    if (0 < mysqli_num_rows($subtitleFileQuery)) {
        $subtitleFileResult = mysqli_fetch_assoc($subtitleFileQuery);
        $subtitleFilename = $subtitleFileResult["filename"];
        $subtitleFilePath = $torrent_dir . "/" . $subtitleFilename;
        if (!file_exists($subtitleFilePath)) {
            $errormessage = $lang->ts_subtitles["filenotexists"];
        } else {
            sql_query("UPDATE ts_subtitles SET $dlcount = dlcount + 1 WHERE `id` = " . sqlesc($subtitleId));
            download($subtitleFilePath);
            exit;
        }
    } else {
        $errormessage = $lang->ts_subtitles["invalidid"];
    }
}
$extraquery1 = $extraquery2 = "";
$extralink = "?";
if ($action == "search") {
    $keywords = isset($_GET["keywords"]) ? trim($_GET["keywords"]) : (isset($_POST["keywords"]) ? trim($_POST["keywords"]) : "");
    if (strlen($keywords) < 3) {
        $errormessage = $lang->ts_subtitles["searcherror"];
    } else {
        $extraquery1 = "`title` LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "%'";
        $extraquery2 = " WHERE s.title LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "%'";
        $extralink = "?$action = search&amp;$keywords = " . htmlspecialchars_uni(urlencode($keywords)) . "&amp;";
    }
}
if ($canupload || $is_mod) {
    $uploadlink = "<a $href = \"" . $BASEURL . $_SERVER["SCRIPT_NAME"] . "?$action = upload\">" . $lang->ts_subtitles["upload"] . "</a>";
} else {
    $uploadlink = "";
}
$torrentsperpage = $CURUSER["torrentsperpage"] != 0 ? intval($CURUSER["torrentsperpage"]) : $ts_perpage;
$count = TSRowCount("id", "ts_subtitles", $extraquery1);
list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, $_SERVER["SCRIPT_NAME"] . $extralink);
stdhead($SITENAME . " " . $lang->ts_subtitles["head"]);
show_error();
echo "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t<input $type = \"hidden\" $name = \"action\" $value = \"search\" />\r\n\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . ts_collapse("search") . "\r\n\t\t\t\t" . $lang->ts_subtitles["search"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . ts_collapse("search", 2) . "\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t" . $lang->ts_subtitles["skey"] . " <input $type = \"text\" $name = \"keywords\" $size = \"75\" $value = \"" . (!empty($keywords) ? htmlspecialchars_uni($keywords) : "") . "\"> <input $type = \"submit\" $value = \"" . $lang->ts_subtitles["search2"] . "\">\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t<br>\r\n\t" . $pagertop . "\r\n\t<table $width = \"100%\" $border = \"0\" class=\"none\" $style = \"clear: both;\" $cellpadding = \"4\" $cellspacing = \"1\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"8\">\r\n\t\t\t\t" . ts_collapse("ts_subtitles") . "\r\n\t\t\t\t" . $SITENAME . " " . $lang->ts_subtitles["head"] . " [<b>" . $uploadlink . "</b>]\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . ts_collapse("ts_subtitles", 2) . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"45%\" $align = \"left\">" . $lang->ts_subtitles[1] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"5%\" $align = \"center\">" . $lang->ts_subtitles["dltitle"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"10%\" $align = \"center\">" . $lang->ts_subtitles[2] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"5%\" $align = \"center\">" . $lang->ts_subtitles[3] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"5%\" $align = \"center\">" . $lang->ts_subtitles[4] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"5%\" $align = \"center\">" . $lang->ts_subtitles["dlcount"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"15%\" $align = \"center\">" . $lang->ts_subtitles[5] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"10%\" $align = \"center\">" . $lang->ts_subtitles[6] . "</td>\r\n\t\t</tr>\r\n\t";
    $subtitleListQuery = sql_query("SELECT s.*, t.name as torrentname, u.username, g.namestyle, c.name, c.flagpic FROM ts_subtitles s LEFT JOIN torrents t ON (s.$tid = t.id) LEFT JOIN users u ON (s.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN countries c ON (s.$language = c.id)" . $extraquery2 . " ORDER by s.date DESC " . $limit);
if (mysqli_num_rows($subtitleListQuery) == 0) {
    echo "<tr><td $colspan = \"8\">" . $lang->ts_subtitles["norecord"] . "</td></tr>";
} else {
    while ($subtitleRow = mysqli_fetch_assoc($subtitleListQuery)) {
        $subtitleDisplayTitle = $action == "search" && !empty($keywords) ? highlight($keywords, htmlspecialchars_uni($subtitleRow["title"])) : htmlspecialchars_uni($subtitleRow["title"]);
        $subtitleAdminLink = $is_mod ? " [<b><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = delete&amp;$id = " . $subtitleRow["id"] . "\">" . $lang->ts_subtitles["delete"] . "</a></b>]" : "";
        $subtitleEditLink = "<span $style = \"float: right;\" $id = \"sid" . $subtitleRow["id"] . "\" $name = \"sid" . $subtitleRow["id"] . "\"><a $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 8&$reporting = " . $subtitleRow["id"] . "&$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "', 'report', 500, 300); return false;\" $href = \"javascript:void(0);\"><img $src = \"" . $pic_base_url . "report2.gif\" $border = \"0\" $alt = \"" . $lang->ts_subtitles["report"] . "\" $title = \"" . $lang->ts_subtitles["report"] . "\" /></a></span>";
        $subtitleEditLink .= $is_mod || $subtitleRow["uid"] == $userid ? "[<b><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = edit&amp;$id = " . $subtitleRow["id"] . "\">" . $lang->ts_subtitles["edit"] . "</a></b>]" : "";
        $subtitleTorrentName = "";
        if ($subtitleRow["torrentname"]) {
            $subtitleSEOLink = ts_seo($subtitleRow["tid"], $subtitleRow["torrentname"], "s");
            $subtitleTorrentName = "<br /><a $href = \"" . $subtitleSEOLink . "\"><font class=\"small\">" . htmlspecialchars_uni($subtitleRow["torrentname"]) . "</font></a>";
        }
        echo "\r\n\t\t<tr" . (!empty($subtitleId) && $subtitleRow["id"] == $subtitleId ? " $bgcolor = \"#FFCC00\"" : "") . ">\r\n\t\t\t<td $align = \"left\">" . $subtitleDisplayTitle . " " . $subtitleEditLink . " " . $subtitleAdminLink . " " . $subtitleTorrentName . "</td>\r\n\t\t\t<td $align = \"center\"><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = download&amp;$id = " . $subtitleRow["id"] . "\"><img $src = \"" . $BASEURL . "/include/templates/" . $defaulttemplate . "/images/torrent_flags/dl.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->ts_subtitles["download"] . "\" $title = \"" . $lang->ts_subtitles["download"] . "\"></a></td>\r\n\t\t\t<td $align = \"center\"><img $src = \"" . $pic_base_url . "flag/" . $subtitleRow["flagpic"] . "\" $border = \"0\" $alt = \"" . htmlspecialchars_uni($subtitleRow["name"]) . "\" $title = \"" . htmlspecialchars_uni($subtitleRow["name"]) . "\"></td>\r\n\t\t\t<td $align = \"center\">" . htmlspecialchars_uni($subtitleRow["fps"]) . "</td>\r\n\t\t\t<td $align = \"center\">" . htmlspecialchars_uni($subtitleRow["cds"]) . "</td>\r\n\t\t\t<td $align = \"center\">" . ts_nf($subtitleRow["dlcount"]) . "</td>\r\n\t\t\t<td $align = \"center\">" . my_datee($dateformat, $subtitleRow["date"]) . " " . my_datee($timeformat, $subtitleRow["date"]) . "</td>\r\n\t\t\t<td $align = \"center\"><a $href = \"" . ts_seo($subtitleRow["uid"], $subtitleRow["username"]) . "\">" . get_user_color($subtitleRow["username"], $subtitleRow["namestyle"]) . "</a></td>\r\n\t\t</tr>\r\n\t\t";
    }
}
echo "\r\n</table>\r\n" . $pagerbottom;
stdfoot();
function jumpbutton($where)
{
    $str = "<table $align = \"center\" $border = \"0\" $cellpadding = \"0\" $cellspacing = \"0\" $width = \"100%\" class=\"none\">\r\n\t<tbody><div class=\"hoptobuttons\">";
    if (!is_array($where)) {
    }
    foreach ($where as $value => $jump) {
        if (!empty($value) && !empty($jump)) {
            $str .= "<input $value = \"" . $value . "\" $onclick = \"jumpto('" . $jump . "');\" class=\"hoptobutton\" $type = \"button\">";
        }
    }
    $str .= "</div></tbody></table>";
    return $str;
}
function show_error()
{
    global $errormessage;
    global $lang;
    global $ts_template;
    if (!empty($errormessage)) {
        echo "\r\n\t\t<table $width = \"100%\" $border = \"0\" class=\"none\" $style = \"clear: both;\" $cellpadding = \"4\" $cellspacing = \"1\">\r\n\t\t\t<tr><td class=\"thead\">" . $lang->global["error"] . "</td></tr>\r\n\t\t\t<tr><td><font $color = \"red\"><strong>" . $errormessage . "</strong></font></td></tr>\r\n\t\t\t</table>\r\n\t\t<br />";
    }
}
function ts_remove_whitespaces($text = "", $replace = "_")
{
    return preg_replace("#\\s+#", $replace, $text);
}

?>