<?php
define("TSA_VERSION", "2.1 by xam");
$albumAction = isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"]) ? $_POST["do"] : "");
$albumid = isset($_GET["albumid"]) ? intval($_GET["albumid"]) : (isset($_POST["albumid"]) ? intval($_POST["albumid"]) : 0);
$imageid = isset($_GET["imageid"]) ? intval($_GET["imageid"]) : (isset($_POST["imageid"]) ? intval($_POST["imageid"]) : 0);
$cid = isset($_GET["cid"]) ? intval($_GET["cid"]) : (isset($_POST["cid"]) ? intval($_POST["cid"]) : 0);
$album_error = [];
$album_error = [];
$global_image_path = "ts_albums/";
$album_image_path = $global_image_path . "album_images/";
$album_thumbnail_path = $global_image_path . "album_thumbnails/";
if ($albumAction == "view_single_image" && $albumid && $imageid) {
    define("NO_LOGIN_REQUIRED", true);
    define("SKIP_LOCATION_SAVE", true);
    define("DEBUGMODE", true);
    define("SKIP_CRON_JOBS", true);
    define("IN_AJAX", true);
    define("THIS_SCRIPT", "ts_albums.php");
    require "./global.php";
} else {
    define("THIS_SCRIPT", "ts_albums.php");
    require "./global.php";
    if (!isset($CURUSER)) {
        print_no_permission();
    }
    $lang->load("ts_albums");
    $TSSEConfig->TSLoadConfig("ALBUM");
    if (!$is_mod && $album_disabled == "yes") {
        stderr($lang->global["error"], $lang->ts_albums["disabled"]);
        exit;
    }
    $AlbumConfig = [];
    $AlbumConfig["max_size"] = $album_max_filesize;
    $AlbumConfig["max_width"] = $album_max_width;
    $AlbumConfig["max_height"] = $album_max_height;
    $AlbumConfig["allowed_images"] = "gif,jpg,png";
    $AlbumConfig["thumbnail_max_width"] = "125";
    $AlbumConfig["thumbnail_max_height"] = "80";
    if (($album_can_view_usergroups = explode(",", $album_can_view_usergroups)) && !in_array($CURUSER["usergroup"], $album_can_view_usergroups)) {
        print_no_permission();
        exit;
    }
    $PermCanUploadCreate = true;
    if (($album_can_upload_usergroups = explode(",", $album_can_upload_usergroups)) && !in_array($CURUSER["usergroup"], $album_can_upload_usergroups)) {
        $PermCanUploadCreate = false;
    }
}
if ($albumAction == "add_album" && $PermCanUploadCreate) {
    $query = sql_query("SELECT * FROM ts_albums WHERE `userid` = '" . $CURUSER["id"] . "'");
    if ($album_max_albums <= mysqli_num_rows($query)) {
        $album_error[] = sprintf($lang->ts_albums["error15"], $album_max_albums);
        $albumAction = "";
    } else {
        $title = "";
        $description = "";
        $private = "0";
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $title = $_POST["title"] ? trim($_POST["title"]) : "";
            $description = $_POST["description"] ? trim($_POST["description"]) : "";
            $private = isset($_POST["private"]) && $_POST["private"] == "1" ? "1" : "0";
            if ($title && $description) {
                sql_query("INSERT INTO ts_albums (userid, createdate, title, description, private) VALUES ('" . $CURUSER["id"] . "', '" . TIMENOW . "', " . sqlesc($title) . ", " . sqlesc($description) . ", '" . $private . "')") || sqlerr(__FILE__, 148);
                if ($albumid = mysqli_insert_id($GLOBALS["DatabaseConnect"])) {
                    redirect("ts_albums.php?do=show_album&$albumid = " . $albumid);
                    exit;
                }
                $album_error[] = $lang->ts_albums["error2"];
            } else {
                $album_error[] = $lang->ts_albums["error1"];
            }
        }
        stdhead($lang->ts_albums["create_album"]);
        add_breadcrumb($lang->ts_albums["title"], $_SERVER["SCRIPT_NAME"]);
        add_breadcrumb($lang->ts_albums["create_album"]);
        build_breadcrumb();
        show_album_errors();
        echo "\r\n\t\t<form $method = \"post\" $action = \"ts_albums.php\">\r\n\t\t<input $type = \"hidden\" $name = \"do\" $value = \"add_album\" />\r\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->ts_albums["create_album"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_albums["album_title"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars_uni($title) . "\" $size = \"60\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_albums["album_description"] . "</legend>\r\n\t\t\t\t\t\t<textarea $name = \"description\" $rows = \"5\" $cols = \"60\">" . htmlspecialchars_uni($description) . "</textarea>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_albums["type"] . "</legend>\r\n\t\t\t\t\t\t<div><input $type = \"radio\" $name = \"private\" $value = \"0\"" . ($private == "0" ? " $checked = \"checked\"" : "") . " /> " . $lang->ts_albums["public"] . "</div>\r\n\t\t\t\t\t\t<div><input $type = \"radio\" $name = \"private\" $value = \"1\"" . ($private == "1" ? " $checked = \"checked\"" : "") . " /> " . $lang->ts_albums["private"] . "</div>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_albums["save"] . "\" /> <input $type = \"submit\" $value = \"" . $lang->ts_albums["reset"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
        stdfoot();
        exit;
    }
}
if ($albumAction == "show_album" && $albumid) {
    ($query = sql_query("SELECT userid, title, description, private FROM ts_albums WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 210);
    if (mysqli_num_rows($query) == 0) {
        $album_error[] = $lang->ts_albums["error3"];
    } else {
        $album = mysqli_fetch_assoc($query);
        $title = htmlspecialchars_uni($album["title"]);
        $description = htmlspecialchars_uni($album["description"]);
        $albumowner = $album["userid"];
        $private = $album["private"];
        if ($private && $albumowner != $CURUSER["id"] && !$is_mod && !mysqli_num_rows(sql_query("SELECT id FROM friends WHERE `status` = 'c' AND $userid = " . $albumowner . " AND $friendid = " . $CURUSER["id"]))) {
            stderr($lang->global["error"], $lang->ts_albums["error14"]);
            exit;
        }
        stdhead($lang->ts_albums["show_album"]);
        add_breadcrumb($lang->ts_albums["title"], $_SERVER["SCRIPT_NAME"]);
        add_breadcrumb($title);
        build_breadcrumb();
        ($query = sql_query("SELECT imageid, imagename, thumbnailname, caption FROM ts_album_images WHERE $albumid = '" . $albumid . "' ORDER BY imagedate DESC")) || sqlerr(__FILE__, 237);
        if (mysqli_num_rows($query) == 0) {
            echo "\r\n\r\n\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ($is_mod || $albumowner === $CURUSER["id"] ? "\r\n\t\t\t\t\t\t<span $style = \"float: right;\" class=\"smalltext\">\r\n\t\t\t\t\t\t\t<a $href = \"ts_albums.php?do=edit_album&amp;$albumid = " . $albumid . "\">" . $lang->ts_albums["edit_album"] . "</a> |\r\n\t\t\t\t\t\t\t<a $href = \"ts_albums.php?do=delete_album&amp;$albumid = " . $albumid . "\" $onclick = \"return confirm('" . $lang->ts_albums["confirm_delete"] . "');\">" . $lang->ts_albums["delete_album"] . "</a>\r\n\t\t\t\t\t\t</span>\r\n\t\t\t\t\t\t" : "") . "\r\n\t\t\t\t\t\t" . $title . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\">\r\n\t\t\t\t\t\t" . $description . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . (($is_mod || $albumowner === $CURUSER["id"]) && $PermCanUploadCreate ? "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"center\">\r\n\t\t\t\t\t\t<a $href = \"ts_albums.php?do=upload_image&amp;$albumid = " . $albumid . "\">" . $lang->ts_albums["upload_image"] . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" : "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t" . $lang->ts_albums["error8"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>") . "\r\n\t\t\t</table>\r\n\t\t\t";
        } else {
            echo "\r\n\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . ($is_mod || $albumowner === $CURUSER["id"] ? "\r\n\t\t\t\t\t\t<span $style = \"float: right;\" class=\"smalltext\">\r\n\t\t\t\t\t\t\t<a $href = \"ts_albums.php?do=edit_album&amp;$albumid = " . $albumid . "\">" . $lang->ts_albums["edit_album"] . "</a> |\r\n\t\t\t\t\t\t\t<a $href = \"ts_albums.php?do=delete_album&amp;$albumid = " . $albumid . "\" $onclick = \"return confirm('" . $lang->ts_albums["confirm_delete"] . "');\">" . $lang->ts_albums["delete_album"] . "</a> |\r\n\t\t\t\t\t\t\t<a $href = \"ts_albums.php?do=edit_images&amp;$albumid = " . $albumid . "\">" . $lang->ts_albums["edit_images"] . "</a>\r\n\t\t\t\t\t\t</span>\r\n\t\t\t\t\t\t" : "") . "\r\n\t\t\t\t\t\t" . $title . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\">\r\n\t\t\t\t\t\t" . $description . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<iframe $src = \"" . $BASEURL . "/sl.php?$albumid = " . $albumid . "&album\" $frameborder = \"0\" $width = \"100%\" $scrolling = \"no\" $align = \"center\"></iframe>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . (($is_mod || $albumowner === $CURUSER["id"]) && $PermCanUploadCreate ? "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t\t\t<a $href = \"ts_albums.php?do=upload_image&amp;$albumid = " . $albumid . "\">" . $lang->ts_albums["upload_image"] . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" : "") . "\r\n\t\t\t</table>\r\n\t\t\t";
        }
        stdfoot();
        exit;
    }
}
if ($do == "view_single_image" && $albumid && $imageid) {
    if (!empty($_SERVER["HTTP_IF_MODIFIED_SINCE"]) || !empty($_SERVER["HTTP_IF_NONE_MATCH"])) {
        $sapi_name = php_sapi_name();
        if ($sapi_name == "cgi" || $sapi_name == "cgi-fcgi") {
            header("Status: 304 Not Modified");
        } else {
            header("HTTP/1.1 304 Not Modified");
        }
        exit;
    }
    $query = sql_query("SELECT imagedate, imagename, thumbnailname FROM ts_album_images WHERE $albumid = '" . $albumid . "' AND $imageid = '" . $imageid . "'");
    if (mysqli_num_rows($query) == 0) {
        $filename = $global_image_path . "no_image.jpg";
        $imagetype = "Content-type: image/jpeg";
        $imagesize = filesize($filename);
        $dateline = TIMENOW;
        $pictureid = "0";
    } else {
        $image = mysqli_fetch_assoc($query);
        if (isset($_GET["small"]) && $_GET["small"] == "true") {
            $filename = $album_thumbnail_path . htmlspecialchars_uni($image["thumbnailname"]);
        } else {
            $filename = $album_image_path . htmlspecialchars_uni($image["imagename"]);
        }
        if (is_file($filename)) {
            $extension = get_extension($image["imagename"]);
            if ($extension == "jpg" || $extension == "jpeg") {
                $imagetype = "Content-type: image/jpeg";
            } else {
                if ($extension == "png") {
                    $imagetype = "Content-type: image/png";
                } else {
                    $imagetype = "Content-type: image/gif";
                }
            }
            $imagesize = filesize($filename);
            $dateline = $image["imagedate"];
            $pictureid = $imageid;
        } else {
            $filename = $global_image_path . "no_image.jpg";
            $imagetype = "Content-type: image/jpeg";
            $imagesize = filesize($filename);
            $dateline = TIMENOW;
            $pictureid = "0";
        }
    }
    header($imagetype);
    readfile($filename);
    exit;
}
if ($albumAction == "edit_comment" && $usergroups["cancomment"] != "no" && $albumid && $imageid && $cid && 0 < $CURUSER["id"]) {
    ($editCommentQuery = sql_query("SELECT userid, descr FROM ts_album_comments WHERE `cid` = " . sqlesc($cid) . " AND $imageid = " . sqlesc($imageid) . " AND $albumid = " . sqlesc($albumid))) || sqlerr(__FILE__, 389);
    if (0 < mysqli_num_rows($editCommentQuery)) {
        $commentRow = mysqli_fetch_assoc($editCommentQuery);
        if (!$is_mod && $commentRow["userid"] != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        ($albumTitleQuery = sql_query("SELECT `title`  FROM ts_albums WHERE $albumid = " . sqlesc($albumid))) || sqlerr(__FILE__, 403);
        if (0 < mysqli_num_rows($albumTitleQuery)) {
            $albumRow = mysqli_fetch_assoc($albumTitleQuery);
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["submit"])) {
                $commentDescription = trim($_POST["message"]);
                if (strlen($commentDescription) < 3) {
                    $album_error[] = $lang->ts_albums["editerror2"];
                } else {
                    sql_query("UPDATE ts_album_comments SET `descr` = " . sqlesc($commentDescription) . " WHERE `cid` = " . sqlesc($cid) . " AND $imageid = " . sqlesc($imageid) . " AND $albumid = " . sqlesc($albumid)) || sqlerr(__FILE__, 423);
                    header("Location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "#show_comments" . $cid);
                    exit;
                }
            }
            $title = htmlspecialchars_uni($albumRow["title"]);
            stdhead($lang->ts_albums["edit_comment"]);
            add_breadcrumb($lang->ts_albums["title"], $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb($title, $_SERVER["SCRIPT_NAME"] . "?do=show_album&amp;$albumid = " . $albumid);
            add_breadcrumb($lang->ts_albums["show_image"], $_SERVER["SCRIPT_NAME"] . "?do=show_image&amp;$albumid = " . $albumid . "&amp;$imageid = " . $imageid);
            add_breadcrumb($lang->ts_albums["edit_comment"]);
            build_breadcrumb();
            show_album_errors();
            $previewHtml = showPreview("message");
            define("IN_EDITOR", true);
            include_once INC_PATH . "/editor.php";
            echo "\r\n\t\t" . (isset($previewHtml) ? $previewHtml : "") . "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_comment&$albumid = " . $albumid . "&$imageid = " . $imageid . "&$cid = " . $cid . "\">\r\n\t\t\t" . insert_editor(false, "", isset($commentDescription) ? $commentDescription : $commentRow["descr"], $lang->ts_albums["edit_comment"], "", "", "", true, "", $lang->ts_albums["save"]) . "\r\n\t\t</form>\r\n\t\t";
            stdfoot();
            exit;
        }
        print_no_permission();
        exit;
    }
    $album_error[] = $lang->ts_albums["invalidcid"];
}
if ($albumAction == "delete_comment" && $usergroups["cancomment"] != "no" && $albumid && $imageid && $cid && 0 < $CURUSER["id"]) {
    ($deleteCommentQuery = sql_query("SELECT userid FROM ts_album_comments WHERE `cid` = " . sqlesc($cid) . " AND $imageid = " . sqlesc($imageid) . " AND $albumid = " . sqlesc($albumid))) || sqlerr(__FILE__, 460);
    if (0 < mysqli_num_rows($deleteCommentQuery)) {
        $deleteCommentRow = mysqli_fetch_assoc($deleteCommentQuery);
        $owner = $deleteCommentRow["userid"];
        if (!$is_mod && $owner != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        sql_query("DELETE FROM ts_album_comments WHERE `cid` = " . sqlesc($cid) . " AND $imageid = " . sqlesc($imageid) . " AND $albumid = " . sqlesc($albumid)) || sqlerr(__FILE__, 474);
        header("Location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid);
        exit;
    }
    $album_error[] = $lang->ts_albums["invalidcid"];
}
if ($albumAction == "save_comment" && $usergroups["cancomment"] != "no" && $albumid && $imageid && 0 < $CURUSER["id"]) {
    ($query = sql_query("SELECT a.userid as owner, a.title, a.private, i.userid as sender, i.imagedate, i.imagename, i.caption, u.username, g.namestyle FROM ts_albums a INNER JOIN ts_album_images i ON (a.$albumid = i.albumid) LEFT JOIN users u ON (i.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE a.$albumid = '" . $albumid . "' AND i.$imageid = '" . $imageid . "'")) || sqlerr(__FILE__, 486);
    if (mysqli_num_rows($query) == 0) {
        $album_error[] = $lang->ts_albums["error4"];
    } else {
        $album = mysqli_fetch_assoc($query);
        $title = htmlspecialchars_uni($album["title"]);
        $albumowner = $album["owner"];
        $sender = $album["sender"];
        $caption = htmlspecialchars_uni($album["caption"]);
        $private = $album["private"];
        if ($private && $albumowner != $CURUSER["id"] && !$is_mod && !mysqli_num_rows(sql_query("SELECT id FROM friends WHERE `status` = 'c' AND $userid = " . $albumowner . " AND $friendid = " . $CURUSER["id"]))) {
            stderr($lang->global["error"], $lang->ts_albums["error14"]);
            exit;
        }
    }
    $commentMessage = trim($_POST["message"]);
    if (strlen($commentMessage) < 2) {
        $album_error[] = $lang->global["dontleavefieldsblank"];
    }
    if (count($album_error) == 0) {
        sql_query("INSERT INTO ts_album_comments (albumid, imageid, userid, date, descr) VALUES (" . sqlesc($albumid) . ", " . sqlesc($imageid) . ", " . sqlesc($CURUSER["id"]) . ", '" . TIMENOW . "', " . sqlesc($commentMessage) . ")") || sqlerr(__FILE__, 519);
        $cid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        $userOptionsQuery = sql_query("SELECT options FROM users WHERE `id` = " . sqlesc($albumowner));
        $userOptionsRow = mysqli_fetch_assoc($userOptionsQuery);
        if (TS_Match($userOptionsRow["options"], "C1") && $albumowner != $CURUSER["id"]) {
            require_once INC_PATH . "/functions_pm.php";
            send_pm($albumowner, sprintf($lang->ts_albums["newcommenttxt"], "[$url = " . $BASEURL . "/ts_albums.php?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "&$scrollto = show_comments" . $cid . "]" . $title . "[/url]"), $lang->ts_albums["newcommentsub"]);
        }
        if ($albumowner != $sender) {
            $senderOptionsQuery = sql_query("SELECT options FROM users WHERE `id` = " . sqlesc($sender));
            $senderOptionsRow = mysqli_fetch_assoc($senderOptionsQuery);
            if (TS_Match($senderOptionsRow["options"], "C1") && $sender != $CURUSER["id"]) {
                require_once INC_PATH . "/functions_pm.php";
                send_pm($sender, sprintf($lang->ts_albums["newcommenttxt2"], "[$url = " . $BASEURL . "/ts_albums.php?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "&$scrollto = show_comments" . $cid . "]" . $caption . "[/url]"), $lang->ts_albums["newcommentsub2"]);
            }
        }
        header("location: " . $_SERVER["SCRIPT_NAME"] . "?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "#show_comments" . $cid);
        exit;
    }
    $albumAction = "show_image";
}
if ($albumAction == "show_image" && $albumid && $imageid) {
    ($query = sql_query("SELECT a.userid as owner, a.title, a.private, i.userid as sender, i.imagedate, i.imagename, i.caption, u.username, g.namestyle FROM ts_albums a INNER JOIN ts_album_images i ON (a.$albumid = i.albumid) LEFT JOIN users u ON (i.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE a.$albumid = '" . $albumid . "' AND i.$imageid = '" . $imageid . "'")) || sqlerr(__FILE__, 555);
    if (mysqli_num_rows($query) == 0) {
        $album_error[] = $lang->ts_albums["error4"];
    } else {
        $album = mysqli_fetch_assoc($query);
        $title = htmlspecialchars_uni($album["title"]);
        $albumowner = $album["owner"];
        $sender = "<a $href = \"" . ts_seo($album["sender"], $album["username"]) . "\">" . get_user_color($album["username"], $album["namestyle"]) . "</a>";
        $imagename = $album_image_path . htmlspecialchars_uni($album["imagename"]);
        $imagedate = my_datee($dateformat, $album["imagedate"]) . " " . my_datee($timeformat, $album["imagedate"]);
        $caption = htmlspecialchars_uni($album["caption"]);
        $imageurl = $BASEURL . "/ts_albums.php?do=view_single_image&$albumid = " . $albumid . "&$imageid = " . $imageid;
        $bbcodeurl = "[IMG]" . $BASEURL . "/ts_albums.php?do=view_single_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "[/IMG]";
        $thumburl = $BASEURL . "/ts_albums.php?do=view_single_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "&$small = true";
        $thumb = "[IMG]" . $BASEURL . "/ts_albums.php?do=view_single_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "&$small = true[/IMG]";
        $private = $album["private"];
        if ($private && $albumowner != $CURUSER["id"] && !$is_mod && !mysqli_num_rows(sql_query("SELECT id FROM friends WHERE `status` = 'c' AND $userid = " . $albumowner . " AND $friendid = " . $CURUSER["id"]))) {
            stderr($lang->global["error"], $lang->ts_albums["error14"]);
            exit;
        }
        if (!is_file($imagename)) {
            $album_error[] = $lang->ts_albums["error4"];
        } else {
            if (isset($_GET["next_image"])) {
                $query = sql_query("SELECT imageid FROM ts_album_images WHERE imageid < " . $imageid . " AND $albumid = " . $albumid . " ORDER BY imageid DESC LIMIT 1");
                if (mysqli_num_rows($query)) {
                    $Result = mysqli_fetch_assoc($query);
                    $imageid = $Result["imageid"];
                    redirect("ts_albums.php?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid);
                    exit;
                }
            }
            if (isset($_GET["prev_image"])) {
                $query = sql_query("SELECT imageid FROM ts_album_images WHERE imageid > " . $imageid . " AND $albumid = " . $albumid . " ORDER BY imageid ASC LIMIT 1");
                if (mysqli_num_rows($query)) {
                    $Result = mysqli_fetch_assoc($query);
                    $imageid = $Result["imageid"];
                    redirect("ts_albums.php?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid);
                    exit;
                }
            }
            stdhead($lang->ts_albums["show_image"]);
            if (isset($_GET["next_image"])) {
                echo show_notice($lang->ts_albums["error17"], true);
            }
            if (isset($_GET["prev_image"])) {
                echo show_notice($lang->ts_albums["error18"], true);
            }
            add_breadcrumb($lang->ts_albums["title"], $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb($title, $_SERVER["SCRIPT_NAME"] . "?do=show_album&amp;$albumid = " . $albumid);
            add_breadcrumb($lang->ts_albums["show_image"]);
            build_breadcrumb();
            show_album_errors();
            ($Query = sql_query("SELECT c.cid, c.userid, c.date, c.descr, u.username, u.avatar, g.namestyle FROM ts_album_comments c LEFT JOIN users u ON (u.`id` = c.userid) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE c.$albumid = " . sqlesc($albumid) . " AND c.$imageid = " . sqlesc($imageid) . " ORDER BY date ASC")) || sqlerr(__FILE__, 632);
            $AlbumComments = "\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"show_comments\" $name = \"show_comments\">\r\n\t\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\">" . $lang->ts_albums["image_comments"] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t";
            if (0 < mysqli_num_rows($Query)) {
                while ($Comments = mysqli_fetch_assoc($Query)) {
                    $EditComment = "";
                    if ($usergroups["cancomment"] != "no" && ($is_mod || $Comments["userid"] === $CURUSER["id"])) {
                        $EditComment .= "\r\n\t\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=edit_comment&amp;$cid = " . $Comments["cid"] . "&amp;$albumid = " . $albumid . "&amp;$imageid = " . $imageid . "\"><u><i>" . $lang->ts_albums["edit_comment"] . "</i></u></a>\r\n\t\t\t\t\t\t\t| <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=delete_comment&amp;$cid = " . $Comments["cid"] . "&amp;$albumid = " . $albumid . "&amp;$imageid = " . $imageid . "\" $onclick = \"return confirm('" . $lang->ts_albums["confirm_delete2"] . "');\"><u><i>" . $lang->ts_albums["delete_comment"] . "</i></u></a>\r\n\t\t\t\t\t\t</span>";
                    }
                    $Poster = "<a $href = \"" . ts_seo($Comments["userid"], $Comments["username"]) . "\">" . get_user_color($Comments["username"], $Comments["namestyle"]) . "</a>";
                    $AlbumComments .= "\r\n\t\t\t\t\t\t\t<div $style = \"padding-top: 5px;\" $id = \"show_comments" . $Comments["cid"] . "\" $name = \"show_comments" . $Comments["cid"] . "\">\r\n\t\t\t\t\t\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td $colspan = \"2\" $align = \"left\" class=\"subheader\">\r\n\t\t\t\t\t\t\t\t\t\t\t" . $EditComment . "\r\n\t\t\t\t\t\t\t\t\t\t\t" . sprintf($lang->ts_albums["posted"], my_datee($dateformat, $Comments["date"]), my_datee($timeformat, $Comments["date"]), $Poster) . "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td $align = \"left\" $width = \"1%\" $height = \"50\" $valign = \"top\">" . get_user_avatar($Comments["avatar"], false, 50, 50) . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td $valign = \"top\" $width = \"99%\">\r\n\t\t\t\t\t\t\t\t\t\t\t" . format_comment($Comments["descr"]) . "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t";
                }
            } else {
                $AlbumComments .= $lang->ts_albums["nocomments"];
            }
            $AlbumComments .= "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t\t";
            $PostComments = "";
            if ($usergroups["cancomment"] != "no" && 0 < $CURUSER["id"]) {
                $lang->load("quick_editor");
                require INC_PATH . "/functions_quick_editor.php";
                require_once INC_PATH . "/class_tsquickbbcodeeditor.php";
                $QuickEditor = new TSQuickBBCodeEditor();
                $QuickEditor->ImagePath = $pic_base_url;
                $QuickEditor->SmiliePath = $pic_base_url . "smilies/";
                $QuickEditor->FormName = "quickreply";
                $QuickEditor->TextAreaName = "message";
                $PostComments = "\r\n\t\t\t\t" . $QuickEditor->GenerateJavascript() . "\r\n\t\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=save_comment&$albumid = " . $albumid . "&$imageid = " . $imageid . "\" $name = \"quickreply\" $id = \"quickreply\">\r\n\t\t\t\t<input $type = \"hidden\" $name = \"albumid\" $value = \"" . $albumid . "\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"imageid\" $value = \"" . $imageid . "\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"save_comment\" />\r\n\t\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"post_comment\" $name = \"post_comment\">\r\n\t\t\t\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\">" . $lang->ts_albums["post_comment"] . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t\t" . $QuickEditor->GenerateBBCode() . "\r\n\t\t\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t\t\t<textarea $name = \"message\" $style = \"width:670px;height:85px;\" $id = \"message\"></textarea><br />\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->ts_albums["save"] . "\" class=\"button\" />\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"reset\" $value = \"" . $lang->ts_albums["reset"] . "\" class=\"button\" />\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t\t</form>\r\n\t\t\t\t";
            }
            $ImageHash = md5($imagename);
            echo "\r\n\t\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\" $align = \"center\">\r\n\t\t\t\t\t\t" . sprintf($lang->ts_albums["image_details"], $caption, $imagedate, $sender) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"center\">\r\n\t\t\t\t\t\t<span $id = \"" . $ImageHash . "\">&nbsp;</span> <a $href = \"" . str_replace("&$small = true", "", $imagename) . "\" $id = \"ts_show_preview\" $alt = \"\"><img $src = \"" . $imagename . "\" $border = \"0\" $alt = \"\" $onload = \"TSResizeImage(this, '" . $ImageHash . "');\" /></a>\r\n\t\t\t\t\t\t<div $align = \"center\" $style = \"padding-top: 5px;\">\r\n\t\t\t\t\t\t\t<input $type = \"button\" class=\"button\" $value = \"" . $lang->ts_albums["previmage"] . "\" $onclick = \"jumpto('" . $BASEURL . "/ts_albums.php?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "&$prev_image = true');\" /> \r\n\t\t\t\t\t\t\t<input $type = \"button\" class=\"button\" $value = \"" . $lang->ts_albums["report"] . "\" $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 9&$extra = " . $albumid . "&$reporting = " . $imageid . "', 'report', 500, 300); return false;\" />\r\n\t\t\t\t\t\t\t" . ($is_mod || $album["owner"] === $CURUSER["id"] ? "<input $type = \"button\" class=\"button\" $value = \"" . $lang->ts_albums["edit_image"] . "\" $onclick = \"jumpto('" . $BASEURL . "/ts_albums.php?do=edit_images&$albumid = " . $albumid . "&$image_ids = " . $imageid . "', 'report', 500, 300); return false;\" />" : "") . "\r\n\t\t\t\t\t\t\t<input $type = \"button\" class=\"button\" $value = \"" . $lang->ts_albums["nextimage"] . "\" $onclick = \"jumpto('" . $BASEURL . "/ts_albums.php?do=show_image&$albumid = " . $albumid . "&$imageid = " . $imageid . "&$next_image = true');\" /> \r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"center\">\r\n\t\t\t\t\t\t<table $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"left\">\r\n\t\t\t\t\t\t\t\t\t<div class=\"smalltext\" $style = \"padding-top: 5px; padding-bottom: 5px;\">\r\n\t\t\t\t\t\t\t\t\t\t<b>" . $lang->ts_albums["url1"] . "</b>\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"120\" $value = \"" . $imageurl . "\" $readonly = \"readonly\" $onfocus = \"this.select()\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"left\">\r\n\t\t\t\t\t\t\t\t\t<div class=\"smalltext\">\r\n\t\t\t\t\t\t\t\t\t\t<b>" . $lang->ts_albums["url2"] . "</b>\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"120\" $value = \"" . $bbcodeurl . "\" $readonly = \"readonly\" $onfocus = \"this.select()\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"left\">\r\n\t\t\t\t\t\t\t\t\t<div class=\"smalltext\" $style = \"padding-top: 5px; padding-bottom: 5px;\">\r\n\t\t\t\t\t\t\t\t\t\t<b>" . $lang->ts_albums["url1"] . " (" . $lang->ts_albums["thumbnail"] . ")</b>\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"120\" $value = \"" . $thumburl . "\" $readonly = \"readonly\" $onfocus = \"this.select()\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"left\">\r\n\t\t\t\t\t\t\t\t\t<div class=\"smalltext\">\r\n\t\t\t\t\t\t\t\t\t\t<b>" . $lang->ts_albums["url2"] . " (" . $lang->ts_albums["thumbnail"] . ")</b>\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"120\" $value = \"" . $thumb . "\" $readonly = \"readonly\" $onfocus = \"this.select()\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t\t" . $AlbumComments . "\r\n\t\t\t" . $PostComments;
            stdfoot();
            exit;
        }
    }
}
if ($albumAction == "edit_album" && $albumid) {
    ($query = sql_query("SELECT userid, title, description, private FROM ts_albums WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 821);
    if (mysqli_num_rows($query) == 0) {
        $album_error[] = $lang->ts_albums["error3"];
    } else {
        $album = mysqli_fetch_assoc($query);
        $title = htmlspecialchars_uni($album["title"]);
        $description = htmlspecialchars_uni($album["description"]);
        $albumowner = $album["userid"];
        $private = $album["private"];
        if (!$is_mod && $albumowner != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $title = $_POST["title"] ? trim($_POST["title"]) : "";
            $description = $_POST["description"] ? trim($_POST["description"]) : "";
            $private = isset($_POST["private"]) && $_POST["private"] == "1" ? "1" : "0";
            if ($title && $description) {
                sql_query("UPDATE ts_albums SET $title = " . sqlesc($title) . ", $description = " . sqlesc($description) . ", private = '" . $private . "' WHERE $albumid = '" . $albumid . "'") || sqlerr(__FILE__, 847);
                redirect("ts_albums.php?do=show_album&$albumid = " . $albumid);
                exit;
            }
            $album_error[] = $lang->ts_albums["error1"];
        }
        stdhead($lang->ts_albums["edit_album"]);
        add_breadcrumb($lang->ts_albums["title"], $_SERVER["SCRIPT_NAME"]);
        add_breadcrumb($title, $_SERVER["SCRIPT_NAME"] . "?do=show_album&amp;$albumid = " . $albumid);
        add_breadcrumb($lang->ts_albums["edit_album"]);
        build_breadcrumb();
        show_album_errors();
        echo "\r\n\t\t<form $method = \"post\" $action = \"ts_albums.php\">\r\n\t\t<input $type = \"hidden\" $name = \"do\" $value = \"edit_album\" />\r\n\t\t<input $type = \"hidden\" $name = \"albumid\" $value = \"" . $albumid . "\" />\r\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->ts_albums["edit_album"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_albums["album_title"] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars_uni($title) . "\" $size = \"60\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $lang->ts_albums["album_description"] . "</legend>\r\n\t\t\t\t\t\t<textarea $name = \"description\" $rows = \"5\" $cols = \"60\">" . htmlspecialchars_uni($description) . "</textarea>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $lang->ts_albums["type"] . "</legend>\r\n\t\t\t\t\t<div><input $type = \"radio\" $name = \"private\" $value = \"0\"" . ($private == "0" ? " $checked = \"checked\"" : "") . " /> " . $lang->ts_albums["public"] . "</div>\r\n\t\t\t\t\t<div><input $type = \"radio\" $name = \"private\" $value = \"1\"" . ($private == "1" ? " $checked = \"checked\"" : "") . " /> " . $lang->ts_albums["private"] . "</div>\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_albums["save"] . "\" /> <input $type = \"submit\" $value = \"" . $lang->ts_albums["reset"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
        stdfoot();
        exit;
    }
}
if ($albumAction == "delete_album" && $albumid) {
    ($query = sql_query("SELECT userid FROM ts_albums WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 904);
    if (mysqli_num_rows($query) == 0) {
        $album_error[] = $lang->ts_albums["error3"];
    } else {
        $album = mysqli_fetch_assoc($query);
        $albumowner = $album["userid"];
        if (!$is_mod && $albumowner != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        sql_query("DELETE FROM ts_albums WHERE $albumid = '" . $albumid . "'") || sqlerr(__FILE__, 921);
        ($query = sql_query("SELECT imagename, thumbnailname FROM ts_album_images WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 922);
        if (mysqli_num_rows($query)) {
            while ($images = mysqli_fetch_assoc($query)) {
                $firstimage = $album_thumbnail_path . $images["thumbnailname"];
                if (is_file($firstimage)) {
                    unlink($firstimage);
                }
                $secondimage = $album_image_path . $images["imagename"];
                if (is_file($secondimage)) {
                    unlink($secondimage);
                }
            }
            sql_query("DELETE FROM ts_album_images WHERE $albumid = '" . $albumid . "'") || sqlerr(__FILE__, 939);
        }
    }
}
if ($albumAction == "edit_images" && $albumid) {
    ($query = sql_query("SELECT userid, title, description, coverimageid FROM ts_albums WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 947);
    if (mysqli_num_rows($query) == 0) {
        $album_error[] = $lang->ts_albums["error3"];
    } else {
        $album = mysqli_fetch_assoc($query);
        $title = htmlspecialchars_uni($album["title"]);
        $description = htmlspecialchars_uni($album["description"]);
        $albumowner = $album["userid"];
        $coverimageid = $album["coverimageid"];
        $Where = "";
        $image_ids = "";
        if (!$is_mod && $albumowner != $CURUSER["id"]) {
            print_no_permission(true);
            exit;
        }
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            if (isset($_POST["delete"]) && is_array($_POST["delete"]) && count($_POST["delete"])) {
                foreach ($_POST["delete"] as $deleteimageid) {
                    $query = sql_query("SELECT imagename, thumbnailname FROM ts_album_images WHERE $albumid = '" . $albumid . "' AND $imageid = '" . intval($deleteimageid) . "'");
                    if (mysqli_num_rows($query)) {
                        $DeleteImage = mysqli_fetch_assoc($query);
                        if (is_file($album_image_path . $DeleteImage["imagename"])) {
                            unlink($album_image_path . $DeleteImage["imagename"]);
                        }
                        if (is_file($album_thumbnail_path . $DeleteImage["thumbnailname"])) {
                            unlink($album_thumbnail_path . $DeleteImage["thumbnailname"]);
                        }
                        sql_query("DELETE FROM ts_album_images WHERE $albumid = '" . $albumid . "' AND $imageid = '" . intval($deleteimageid) . "'");
                        if (isset($_POST["captions"][$deleteimageid])) {
                            unset($_POST["captions"][$deleteimageid]);
                        }
                    }
                }
            }
            $coverimageid = isset($_POST["coverimageid"]) ? intval($_POST["coverimageid"]) : 0;
            if ($coverimageid) {
                sql_query("UPDATE ts_albums SET $coverimageid = '" . $coverimageid . "' WHERE $albumid = '" . $albumid . "'") || sqlerr(__FILE__, 999);
            }
            if (is_array($_POST["captions"]) && 0 < count($_POST["captions"])) {
                foreach ($_POST["captions"] as $cimageid => $value) {
                    if (empty($value)) {
                        $album_error[] = $lang->ts_albums["error10"];
                    } else {
                        if (is_valid_id($cimageid)) {
                            sql_query("UPDATE ts_album_images SET $caption = " . sqlesc($value) . " WHERE $albumid = '" . $albumid . "' AND $imageid = '" . $cimageid . "'") or sql_query("UPDATE ts_album_images SET $caption = " . sqlesc($value) . " WHERE $albumid = '" . $albumid . "' AND $imageid = '" . $cimageid . "'") || sqlerr(__FILE__, 1013);
                        }
                    }
                }
            }
            if (count($album_error) == 0) {
                redirect("ts_albums.php?do=show_album&$albumid = " . $albumid);
                exit;
            }
        }
        if (isset($_GET["image_ids"])) {
            $image_ids = trim($_GET["image_ids"]);
            if ($image_ids) {
                $image_ids = explode(",", $image_ids);
                if (count($image_ids) && is_array($image_ids)) {
                    foreach ($image_ids as $imgid) {
                        if (!is_valid_id($imgid)) {
                            print_no_permission(true);
                            exit;
                        }
                    }
                    $image_ids = implode(",", $image_ids);
                    $Where .= " AND imageid IN (0, " . $image_ids . ")";
                }
            }
        }
        if (!$is_mod) {
            $Where .= " AND $userid = '" . $CURUSER["id"] . "'";
        }
        ($query = sql_query("SELECT imageid, thumbnailname, caption FROM ts_album_images WHERE $albumid = '" . $albumid . "'" . $Where . " ORDER BY imagedate DESC")) || sqlerr(__FILE__, 1052);
        if (mysqli_num_rows($query) == 0) {
            $album_error[] = $lang->ts_albums["error4"];
        } else {
            stdhead($lang->ts_albums["edit_images"]);
            add_breadcrumb($lang->ts_albums["title"], $_SERVER["SCRIPT_NAME"]);
            add_breadcrumb($title, $_SERVER["SCRIPT_NAME"] . "?do=show_album&amp;$albumid = " . $albumid);
            add_breadcrumb($lang->ts_albums["edit_images"]);
            build_breadcrumb();
            show_album_errors();
            echo "\r\n\t\t\t<form $method = \"post\" $action = \"ts_albums.php" . ($image_ids ? "?do=edit_images&$albumid = " . $albumid . "&$image_ids = " . htmlspecialchars_uni($image_ids) : "") . "\">\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"edit_images\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"albumid\" $value = \"" . $albumid . "\" />\r\n\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t" . $title . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\">\r\n\t\t\t\t\t\t" . $description . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $align = \"center\">\r\n\t\t\t\t";
            while ($images = mysqli_fetch_assoc($query)) {
                echo "\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t\t\t<legend>" . $lang->ts_albums["caption"] . "</legend>\r\n\t\t\t\t\t\t\t\t\t\t<textarea $name = \"captions[" . $images["imageid"] . "]\" $rows = \"5\" $cols = \"50\">" . htmlspecialchars_uni(isset($_POST["captions"][$images["imageid"]]) ? $_POST["captions"][$images["imageid"]] : $images["caption"]) . "</textarea>\r\n\t\t\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t\t\t<legend>" . $lang->ts_albums["image"] . "</legend>\r\n\t\t\t\t\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t\t\t\t\t<input $type = \"radio\" $name = \"coverimageid\" $value = \"" . $images["imageid"] . "\" $tabindex = \"1\"" . ($coverimageid == $images["imageid"] ? " $checked = \"checked\"" : "") . " />" . $lang->ts_albums["cover"] . "\r\n\t\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t\t\t<a $href = \"ts_albums.php?do=show_image&amp;$albumid = " . $albumid . "&amp;$imageid = " . $images["imageid"] . "\"><img $src = \"" . $album_thumbnail_path . htmlspecialchars_uni($images["thumbnailname"]) . "\" $width = \"125\" $height = \"80\" $border = \"0\" $alt = \"" . htmlspecialchars_uni($images["caption"]) . "\" $title = \"" . htmlspecialchars_uni($images["caption"]) . "\" /></a>\r\n\t\t\t\t\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"delete[]\" $value = \"" . $images["imageid"] . "\" /> " . $lang->ts_albums["delete_image"] . "\r\n\t\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t";
            }
            echo "\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_albums["save"] . "\" /> <input $type = \"submit\" $value = \"" . $lang->ts_albums["reset"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t";
            stdfoot();
            exit;
        }
    }
}
if ($albumAction == "upload_image" && $albumid && $PermCanUploadCreate) {
    ($query = sql_query("SELECT userid, title, description, coverimageid, private FROM ts_albums WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 1131);
    if (mysqli_num_rows($query) == 0) {
        $album_error[] = $lang->ts_albums["error3"];
    } else {
        $album = mysqli_fetch_assoc($query);
        $title = htmlspecialchars_uni($album["title"]);
        $description = htmlspecialchars_uni($album["description"]);
        $albumowner = $album["userid"];
        $private = $album["private"];
        if ($private && $albumowner != $CURUSER["id"] && !$is_mod && !mysqli_num_rows(sql_query("SELECT id FROM friends WHERE `status` = 'c' AND $userid = " . $albumowner . " AND $friendid = " . $CURUSER["id"]))) {
            stderr($lang->global["error"], $lang->ts_albums["error14"]);
            exit;
        }
        if (!$is_mod && $albumowner != $CURUSER["id"]) {
            print_no_permission();
            exit;
        }
        ($query = sql_query("SELECT COUNT(imageid) as totalimages FROM ts_album_images WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 1159);
        if (mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $totalimages = $Result["totalimages"];
            if ($album_max_pictures <= $totalimages) {
                stderr($lang->global["error"], sprintf($lang->ts_albums["error12"], $album_max_pictures));
                exit;
            }
        }
        ($query = sql_query("SELECT imagename FROM ts_album_images WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 1171);
        if (mysqli_num_rows($query)) {
            $totalfilesize = 0;
            while ($albumimages__ = mysqli_fetch_assoc($query)) {
                if (is_file($album_image_path . $albumimages__["imagename"])) {
                    $totalfilesize += filesize($album_image_path . $albumimages__["imagename"]);
                }
            }
            if ($album_max_total_filesize <= $totalfilesize) {
                stderr($lang->global["error"], sprintf($lang->ts_albums["error13"], mksize($album_max_total_filesize)));
                exit;
            }
        }
        $uploadableImagesCount = $album_max_pictures - $totalimages;
        if (20 < $uploadableImagesCount) {
            $uploadableImagesCount = 20;
        }
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && 0 < $uploadableImagesCount) {
            $albumImagesArray = isset($_FILES["images"]) ? $_FILES["images"] : "";
            $uploadedImage = false;
            require INC_PATH . "/class_upload.php";
            $totalUploaded = 0;
            $uploadedImageIds = [];
            for ($imageIndex = 0; $imageIndex < $uploadableImagesCount; $imageIndex++) {
                @clearstatcache();
                if (isset($albumImagesArray["name"][$imageIndex]) && ($fileExtension = get_extension($albumImagesArray["name"][$imageIndex])) && is_file($albumImagesArray["tmp_name"][$imageIndex]) && $albumImagesArray["error"][$imageIndex] == "0" && $albumImagesArray["size"][$imageIndex] <= $AlbumConfig["max_size"] && in_array($fileExtension, explode(",", $AlbumConfig["allowed_images"]))) {
                    $originalFileName = str_replace("." . $fileExtension, "", $albumImagesArray["name"][$imageIndex]);
                    $find = ["/[^a-zA-Z0-9\\s]/", "/\\s+/"];
                    $replace = ["_", "_"];
                    $cleanFileName = strtolower(preg_replace($find, $replace, $originalFileName));
                    $fullImagePath = $album_image_path . $cleanFileName . "." . $fileExtension;
                    if (!is_file($fullImagePath) && move_uploaded_file($albumImagesArray["tmp_name"][$imageIndex], $fullImagePath)) {
                        $imageUploadHandler = new Upload($fullImagePath);
                        $imageInfo = @getimagesize($fullImagePath);
                        $imageFileSize = @filesize($fullImagePath);
                        if ($imageUploadHandler->uploaded && check_image($imageInfo[0], $imageInfo[1], $imageInfo["mime"], $imageFileSize)) {
                            $newImageName = "a" . $albumid . "_u" . $CURUSER["id"] . "_" . $cleanFileName;
                            list($width, $height, $type, $attr) = getimagesize($fullImagePath);
                            if ($AlbumConfig["max_width"] < $width || $AlbumConfig["max_height"] < $height) {
                                $imageUploadHandler->$image_resize = true;
                                if ($AlbumConfig["max_width"] < $width) {
                                    $imageUploadHandler->$image_ratio_y = true;
                                    $imageUploadHandler->$image_x = $AlbumConfig["max_width"];
                                } else {
                                    $imageUploadHandler->$image_ratio_x = true;
                                    $imageUploadHandler->$image_y = $AlbumConfig["max_height"];
                                }
                            }
                            $imageUploadHandler->$file_new_name_body = $newImageName;
                            $imageUploadHandler->Process($album_image_path);
                            if ($imageUploadHandler->processed) {
                                unset($imageUploadHandler);
                                $imageUploadHandler = new Upload($fullImagePath);
                                $imageUploadHandler->$image_resize = true;
                                $imageUploadHandler->$image_x = $AlbumConfig["thumbnail_max_width"];
                                $imageUploadHandler->$image_ratio_y = true;
                                $imageUploadHandler->$file_new_name_body = $newImageName;
                                $imageUploadHandler->Process($album_thumbnail_path);
                                if (is_file($fullImagePath)) {
                                    unlink($fullImagePath);
                                }
                                sql_query("INSERT INTO ts_album_images (albumid, userid, imagedate, imagename, thumbnailname, caption) VALUES ('" . $albumid . "', '" . $CURUSER["id"] . "', '" . TIMENOW . "', " . sqlesc($newImageName . "." . $fileExtension) . ", " . sqlesc($newImageName . "." . $fileExtension) . ", '')") || sqlerr(__FILE__, 1268);
                                $newImageId = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                                $uploadedImageIds[] = $newImageId;
                                if ($totalUploaded == 0 && $album["coverimageid"] == "0") {
                                    sql_query("UPDATE ts_albums SET $coverimageid = '" . $newImageId . "' WHERE $albumid = '" . $albumid . "'") || sqlerr(__FILE__, 1273);
                                }
                                $totalUploaded++;
                            }
                        }
                        if (is_file($fullImagePath)) {
                            unlink($fullImagePath);
                        }
                    }
                }
            }
            if (0 < $totalUploaded) {
                redirect("ts_albums.php?do=edit_images&$albumid = " . $albumid . "&$image_ids = " . implode(",", $uploadedImageIds));
                exit;
            }
        }
        $totalusedimages = 0;
        $silentquery = sql_query("SELECT COUNT(imageid) as totalimages FROM ts_album_images WHERE $albumid = '" . $albumid . "'");
        if (mysqli_num_rows($silentquery)) {
            $Result = mysqli_fetch_assoc($silentquery);
            $totalusedimages = $Result["totalimages"];
        }
        $totalquoteused = 0;
        ($query = sql_query("SELECT imagename FROM ts_album_images WHERE $albumid = '" . $albumid . "'")) || sqlerr(__FILE__, 1302);
        if (mysqli_num_rows($query)) {
            while ($albumimages__ = mysqli_fetch_assoc($query)) {
                if (is_file($album_image_path . $albumimages__["imagename"])) {
                    $totalquoteused += filesize($album_image_path . $albumimages__["imagename"]);
                }
            }
        }
        $imageInputFieldsHtml = "";
        if (0 < $uploadableImagesCount) {
            for ($imageInputIndex = 1; $imageInputIndex <= $uploadableImagesCount; $imageInputIndex++) {
                $imageInputFieldsHtml .= "\r\n\t\t\t\t<div $style = \"margin-top:3px\">\r\n\t\t\t\t\t(" . $imageInputIndex . ")&nbsp;&nbsp;<input $type = \"file\" class=\"bginput\" $name = \"images[]\" $size = \"50\" $tabindex = \"1\" />\r\n\t\t\t\t</div>";
            }
        }
        stdhead($lang->ts_albums["upload_title"]);
        add_breadcrumb($lang->ts_albums["title"], $_SERVER["SCRIPT_NAME"]);
        add_breadcrumb($title, $_SERVER["SCRIPT_NAME"] . "?do=show_album&amp;$albumid = " . $albumid);
        add_breadcrumb($lang->ts_albums["upload_title"]);
        build_breadcrumb();
        show_album_errors();
        echo "\r\n\t\t<form $action = \"ts_albums.php?do=upload_image&amp;$albumid = " . $albumid . "\" $method = \"post\" $enctype = \"multipart/form-data\">\r\n\t\t<input $type = \"hidden\" $name = \"do\" $value = \"upload_image\" />\r\n\t\t<input $type = \"hidden\" $name = \"albumid\" $value = \"" . $albumid . "\" />\r\n\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->ts_albums["upload_title"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"panelsurround\" $align = \"center\">\r\n\t\t\t\t\t<div class=\"panel\">\r\n\t\t\t\t\t\t<div $style = \"width:640px\" $align = \"left\">\r\n\t\t\t\t\t\t\t<fieldset class=\"fieldset\">\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->ts_albums["select_images"] . "</legend>\r\n\t\t\t\t\t\t\t\t<div $style = \"padding:3px\">\r\n\t\t\t\t\t\t\t\t\t" . $imageInputFieldsHtml . "\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t<legend>" . $lang->ts_albums["rules"] . "</legend>\r\n\t\t\t\t\t\t\t\t" . $lang->ts_albums["rules5"] . ": <b>" . ts_nf($album_max_pictures - $totalusedimages) . " / " . ts_nf($album_max_pictures) . "</b><br />\r\n\t\t\t\t\t\t\t\t" . $lang->ts_albums["rules6"] . ": <b>" . mksize($album_max_total_filesize - $totalquoteused) . " / " . mksize($album_max_total_filesize) . "</b><br />\r\n\t\t\t\t\t\t\t\t" . $lang->ts_albums["rules1"] . ": <b>" . $AlbumConfig["allowed_images"] . "</b><br />\r\n\t\t\t\t\t\t\t\t" . $lang->ts_albums["rules2"] . ": <b>" . mksize($AlbumConfig["max_size"]) . "</b><br />\r\n\t\t\t\t\t\t\t\t" . sprintf($lang->ts_albums["rules3"], $AlbumConfig["max_width"], $AlbumConfig["max_height"]) . "\r\n\t\t\t\t\t\t\t\t<div $style = \"padding-top: 2px;\" class=\"smalltext\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->ts_albums["rules4"] . "\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_albums["save"] . "\" /> <input $type = \"submit\" $value = \"" . $lang->ts_albums["reset"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
        stdfoot();
        exit;
    }
}
$Where2 = "";
$Where1 = "";
if (isset($_GET["userid"])) {
    $Where1 = " WHERE a.$userid = " . intval($_GET["userid"]);
    $Where2 = " WHERE `userid` = " . intval($_GET["userid"]);
}
$albumcount = @mysqli_num_rows(@sql_query("SELECT * FROM ts_albums" . $Where2));
list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $albumcount, "ts_albums.php?");
($query = sql_query("SELECT a.albumid, a.userid, a.createdate, a.title, a.private, u.username, g.namestyle, i.thumbnailname FROM ts_albums a LEFT JOIN users u ON (a.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN ts_album_images i ON (i.$imageid = a.coverimageid)" . $Where1 . " ORDER BY a.createdate DESC " . $limit)) || sqlerr(__FILE__, 1392);
if (0 < mysqli_num_rows($query)) {
    stdhead($lang->ts_albums["title"]);
    add_breadcrumb($lang->ts_albums["title"]);
    build_breadcrumb();
    show_album_errors();
    echo "\r\n\t" . $pagertop . "\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . ($PermCanUploadCreate ? "\r\n\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t<a $href = \"ts_albums.php?do=add_album\">" . $lang->ts_albums["create_album"] . "</a>\r\n\t\t\t\t</span>\r\n\t\t\t\t" : "") . "\r\n\t\t\t\t" . $lang->ts_albums["recent_albums"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $align = \"center\">\r\n\t\t\t\t\t<tr>\r\n\t";
    for ($albumcount = 0; $albums = mysqli_fetch_assoc($query); $albumcount++) {
        $image = $album_thumbnail_path . htmlspecialchars_uni($albums["thumbnailname"]);
        if (is_file($image)) {
            $albumimage = "\r\n\t\t\t<img $src = \"" . $image . "\" $width = \"125\" $height = \"80\" $border = \"0\" $alt = \"" . $lang->ts_albums["show_album"] . "\" $title = \"" . $lang->ts_albums["show_album"] . "\" " . (!$is_mod && $albums["userid"] != $CURUSER["id"] && $albums["private"] == "1" ? "class=\"privatealbumimage shadow\" " : "class=\"shadow\" ") . "/>";
        } else {
            $albumimage = "<img $src = \"" . $global_image_path . "no_image.jpg\" $width = \"125\" $height = \"80\" $border = \"0\" $alt = \"" . $lang->ts_albums["error9"] . "\" $title = \"" . $lang->ts_albums["error9"] . "\" class=\"shadow\" />";
        }
        if ($albumcount && $albumcount % 5 == 0) {
            echo "\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t";
        }
        echo "\r\n\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t<div class=\"navbits\" $style = \"width: 150px; height: 150px; padding: 10px 0 0 5px;\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t<div $style = \"padding-bottom: 2px;\">\r\n\t\t\t\t\t\t\t\t\t<a $href = \"ts_albums.php?do=show_album&amp;$albumid = " . $albums["albumid"] . "\">" . $albumimage . "</a>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<div class=\"smalltext\" $style = \"padding-top: 10px;\">\r\n\t\t\t\t\t\t\t\t\t" . sprintf($lang->ts_albums["list_albums"], "<a $href = \"ts_albums.php?do=show_album&amp;$albumid = " . $albums["albumid"] . "\">" . cutename($albums["title"], 52) . "</a>", "<a $href = \"" . ts_seo($albums["userid"], $albums["username"]) . "\">" . get_user_color($albums["username"], $albums["namestyle"]) . "</a>") . "\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<div $style = \"padding-top: 10px;\">\r\n\t\t\t\t\t\t\t\t\t" . my_datee($dateformat, $albums["createdate"], "", 0) . "\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t</td>\r\n\t\t";
    }
    echo "\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t" . $pagerbottom . "\r\n\t";
    stdfoot();
    exit;
}
stdhead($lang->ts_albums["title"]);
add_breadcrumb($lang->ts_albums["title"]);
build_breadcrumb();
show_album_errors();
echo "\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . $lang->ts_albums["recent_albums"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t" . ($PermCanUploadCreate ? "<a $href = \"ts_albums.php?do=add_album\">" . $lang->ts_albums["add_album"] . "</a>" : $lang->ts_albums["error11"]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t";
stdfoot();
exit;
function show_album_errors()
{
    global $album_error;
    global $lang;
    if (0 < count($album_error)) {
        $errors = implode("<br />", $album_error);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}
function check_image($width, $height, $type, $size)
{
    global $AlbumConfig;
    $types_array = ["image/gif", "image/jpeg", "image/jpg", "image/png"];
    if (empty($width) || empty($height) || empty($type) || empty($size) || !in_array($type, $types_array) || $AlbumConfig["max_size"] < $size) {
        return false;
    }
    return true;
}

?>