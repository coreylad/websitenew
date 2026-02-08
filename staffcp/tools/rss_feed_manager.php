<?php
if (($current_memory_limit = function_271(@ini_get("memory_limit"))) < 134217728 && 0 < $current_memory_limit) {
    @ini_set("memory_limit", 134217728);
}
@set_time_limit(0);
checkStaffAuthentication();
if (!function_exists("xml_set_element_handler")) {
    $extension_dir = ini_get("extension_dir");
    if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
        $extension_file = "php_xml.dll";
    } else {
        $extension_file = "xml.so";
    }
    if ($extension_dir && file_exists($extension_dir . "/" . $extension_file)) {
        ini_set("display_errors", true);
        dl($extension_file);
    }
}
if (!function_exists("ini_size_to_bytes") || ($current_memory_limit = function_271(@ini_get("memory_limit"))) < 134217728 && 0 < $current_memory_limit) {
    @ini_set("memory_limit", 134217728);
}
$Language = file("languages/" . getStaffLanguage() . "/rss_feed_manager.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$ListFeeds = "";
if ($Act == "delete") {
    $rssfeedid = isset($_GET["rssfeedid"]) ? intval($_GET["rssfeedid"]) : (isset($_POST["rssfeedid"]) ? intval($_POST["rssfeedid"]) : "0");
    if ($rssfeedid) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title FROM ts_rssfeed WHERE $rssfeedid = '" . $rssfeedid . "'");
        if (mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $title = $Result["title"];
            mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_rssfeed WHERE $rssfeedid = '" . $rssfeedid . "'");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $SysMsg = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[26]);
                logStaffAction($SysMsg);
            }
        }
    }
    $Act = "";
}
if ($Act == "edit" && ($rssfeedid = intval($_GET["rssfeedid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_rssfeed WHERE $rssfeedid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $rssfeedid) . "'");
    if (!mysqli_num_rows($query)) {
        redirectTo("index.php?do=rss_feed_manager");
        exit;
    }
    $Feed = mysqli_fetch_assoc($query);
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT username FROM users WHERE `id` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Feed["userid"]) . "'");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $Feed["username"] = $Result["username"];
    } else {
        $Feed["username"] = "";
    }
    $active = isset($_POST["active"]) ? intval($_POST["active"]) : $Feed["active"];
    $title = isset($_POST["title"]) ? trim($_POST["title"]) : $Feed["title"];
    $url = isset($_POST["url"]) ? trim($_POST["url"]) : $Feed["url"];
    $ttl = isset($_POST["ttl"]) ? intval($_POST["ttl"]) : $Feed["ttl"];
    $maxresults = isset($_POST["maxresults"]) ? intval($_POST["maxresults"]) : $Feed["maxresults"];
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : $Feed["username"];
    $fid = isset($_POST["fid"]) ? intval($_POST["fid"]) : $Feed["fid"];
    $moderate = isset($_POST["moderate"]) ? $_POST["moderate"] : $Feed["moderate"];
    $userid = $Feed["userid"];
    $titletemplate = $Feed["titletemplate"];
    $bodytemplate = $Feed["bodytemplate"];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $Errors = "";
        if (!$title) {
            $Errors[] = $Language[34];
        }
        if (!$url) {
            $Errors[] = $Language[23];
        }
        if (!$username) {
            $Errors[] = $Language[35];
        } else {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
            if (!mysqli_num_rows($query)) {
                $Errors[] = $Language[37];
            } else {
                $Result = mysqli_fetch_assoc($query);
                $userid = $Result["id"];
            }
        }
        if (!$fid) {
            $Errors[] = $Language[36];
        } else {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid FROM tsf_forums WHERE $fid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $fid) . "'");
            if (!mysqli_num_rows($query)) {
                $Errors[] = $Language[38];
            }
        }
        if (!$Errors) {
            if (isset($_POST["save"])) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_rssfeed SET $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $url = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $url) . "', $ttl = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ttl) . "', $maxresults = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $maxresults) . "', $userid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $userid) . "', $fid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $fid) . "', $titletemplate = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $titletemplate) . "', $bodytemplate = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $bodytemplate) . "', $moderate = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $moderate) . "', $active = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $active) . "' WHERE $rssfeedid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $rssfeedid) . "'");
                redirectTo("index.php?do=rss_feed_manager");
                exit;
            }
            if (isset($_POST["preview"])) {
                $xml = new Class_28();
                $xml->function_272($url);
                if (empty($xml->xml_string)) {
                    exit("unable_to_open_url");
                }
                if ($xml->function_273() === false) {
                    exit("xml_error_x_at_line_y " . ($xml->$feedtype = = "unknown" ? "Unknown Feed Type" : $xml->xml_object->function_274()) . " " . $xml->xml_object->function_275());
                }
                $output = "<table $cellpadding = \"0\" $cellspacing = \"0\" class=\"mainTable\">";
                $count = 0;
                foreach ($xml->function_276() as $item) {
                    if ($maxresults && $maxresults <= $count++) {
                        $output .= "\r\n\t\t\t\t</table>";
                    } else {
                        if (!empty($item["content:encoded"])) {
                            $content_encoded = true;
                        }
                        $feedtitle = function_277($xml->function_278($titletemplate, $item));
                        $feedbody = function_277($xml->function_278($bodytemplate, $item));
                        $output .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $valign = \"top\" $align = \"left\" class=\"alt1\">\r\n\t\t\t\t\t\t\t\t<h3><em>" . $feedtitle . "</em></h3>\r\n\t\t\t\t\t\t\t\t" . $feedbody . "\r\n\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>";
                    }
                }
            }
        } else {
            $Message = showAlertError(implode("<br />", $Errors));
        }
    }
    $ForumList = "<select $name = \"fid\"><option $value = \"0\">" . $Language[32] . "</option>";
    $Forums = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid, name FROM tsf_forums WHERE `type` != 'c' ORDER by disporder ASC");
    if (mysqli_num_rows($Forums)) {
        while ($Forum = mysqli_fetch_assoc($Forums)) {
            $ForumList .= "<option $value = \"" . $Forum["fid"] . "\"" . ($fid == $Forum["fid"] ? " $selected = \"selected\"" : "") . ">" . $Forum["name"] . "</option>";
        }
    }
    $ForumList .= "</select>";
    echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=rss_feed_manager\">" . $Language[27] . "</a>") . "\r\n\t" . (isset($output) ? $output : "") . "\r\n\t" . $Message . "\r\n\t<form $method = \"post\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == 1 ? " $checked = \"checked\"" : "") . " /> " . $Language[28] . " <input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == 0 ? " $checked = \"checked\"" : "") . " /> " . $Language[29] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . $title . "\" $style = \"width: 400px;\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"url\" $value = \"" . $url . "\" $style = \"width: 400px;\" /></td>\r\n\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[15] . "</b></td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<select $name = \"ttl\" $style = \"width: 100px;\">\r\n\t\t\t\t\t<option $value = \"600\"" . ($ttl == 600 ? " $selected = \"selected\"" : "") . ">10 " . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"1200\"" . ($ttl == 1200 ? " $selected = \"selected\"" : "") . ">20 " . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"1800\"" . ($ttl == 1800 ? " $selected = \"selected\"" : "") . ">30 " . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"3600\"" . ($ttl == 3600 ? " $selected = \"selected\"" : "") . ">60 " . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"7200\"" . ($ttl == 7200 ? " $selected = \"selected\"" : "") . ">2 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"14400\"" . ($ttl == 14400 ? " $selected = \"selected\"" : "") . ">4 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"21600\"" . ($ttl == 21600 ? " $selected = \"selected\"" : "") . ">6 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"28800\"" . ($ttl == 28800 ? " $selected = \"selected\"" : "") . ">8 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"36000\"" . ($ttl == 36000 ? " $selected = \"selected\"" : "") . ">10 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"43200\"" . ($ttl == 43200 ? " $selected = \"selected\"" : "") . ">12 " . $Language[31] . "</option>\r\n\t\t\t\t</select>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"maxresults\" $value = \"" . $maxresults . "\" $style = \"width: 100px;\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"username\" $value = \"" . $username . "\" $style = \"width: 100px;\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[18] . "</b></td>\r\n\t\t\t<td class=\"alt1\">" . $ForumList . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[33] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"radio\" $name = \"moderate\" $value = \"1\"" . ($moderate == 1 ? " $checked = \"checked\"" : "") . " /> " . $Language[28] . " <input $type = \"radio\" $name = \"moderate\" $value = \"0\"" . ($moderate == 0 ? " $checked = \"checked\"" : "") . " /> " . $Language[29] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t<input $type = \"submit\" $name = \"save\" $value = \"" . $Language[19] . "\" /> <input $type = \"submit\" $name = \"preview\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
}
if ($Act == "run" && ($rssfeedid = intval($_GET["rssfeedid"]))) {
    $timenow = time();
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT r.*, f.type, ff.fid as realforumid, u.username FROM ts_rssfeed r INNER JOIN tsf_forums f ON (f.$fid = r.fid) INNER JOIN tsf_forums ff ON (ff.$fid = f.pid) INNER JOIN users u ON (r.$userid = u.id) WHERE r.$rssfeedid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $rssfeedid) . "'");
    if (!mysqli_num_rows($query)) {
        redirectTo("index.php?do=rss_feed_manager");
        exit;
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_rssfeed SET $lastrun = " . $timenow . " WHERE $rssfeedid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $rssfeedid) . "'");
    $feed = mysqli_fetch_assoc($query);
    $feed["xml"] = new Class_28();
    $feed["xml"]->function_272($feed["url"]);
    $feed["counter"] = 0;
    $feed["useparent"] = false;
    $realforumid = $feed["realforumid"];
    if ($feed["type"] == "s") {
        $feed["useparent"] = true;
    }
    if (empty($feed["xml"]->xml_string)) {
        echo "Unable to Open URL: " . $feed["title"] . "<br />";
        return NULL;
    }
    if ($feed["xml"]->function_273() === false) {
        if (defined("IN_CONTROL_PANEL")) {
            echo "xml_error_x_at_line_y " . ($xml->$feedtype = = "unknown" ? "Unknown Feed Type" : $xml->xml_object->function_274()) . " " . $xml->xml_object->function_275() . "<br />";
        }
        return NULL;
    }
    $items = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uniquehash FROM ts_rsslog WHERE $rssfeedid = " . $rssfeedid);
    $AllFeeds = [];
    if (mysqli_num_rows($query)) {
        while ($AF = mysqli_fetch_assoc($query)) {
            $AllFeeds[$AF["uniquehash"]] = true;
        }
    }
    foreach ($feed["xml"]->function_276() as $item) {
        $item["rssfeedid"] = $rssfeedid;
        if (!empty($item["summary"])) {
            $description = function_279($item["summary"]);
        } else {
            if (!empty($item["content:encoded"])) {
                $description = function_279($item["content:encoded"]);
            } else {
                if (!empty($item["content"])) {
                    $description = function_279($item["content"]);
                } else {
                    $description = function_279($item["description"]);
                }
            }
        }
        if (!isset($item["description"])) {
            $item["description"] = $description;
        }
        if (!isset($item["guid"]) && isset($item["id"])) {
            $item["guid"] =& $item["id"];
        }
        if (!isset($item["pubDate"])) {
            if (isset($item["published"])) {
                $item["pubDate"] =& $item["published"];
            } else {
                if (isset($item["updated"])) {
                    $item["pubDate"] =& $item["updated"];
                }
            }
        }
        switch ($feed["xml"]->feedtype) {
            case "atom":
                $item["contenthash"] = md5($item["title"]["value"] . $description . $item["link"]["href"]);
                break;
            case "rss":
            default:
                $item["contenthash"] = md5($item["title"] . $description . $item["link"]);
                if (is_array($item["guid"]) && !empty($item["guid"]["value"])) {
                    $uniquehash = md5($item["guid"]["value"]);
                } else {
                    if (!is_array($item["guid"]) && !empty($item["guid"])) {
                        $uniquehash = md5($item["guid"]);
                    } else {
                        $uniquehash = $item["contenthash"];
                    }
                }
                if (!isset($AllFeeds[$uniquehash])) {
                    if ($feed["maxresults"] == 0 || $feed["counter"] < $feed["maxresults"]) {
                        $feed["counter"]++;
                        $items[(string) $uniquehash] = $item;
                    }
                }
        }
    }
    $output = "<ol>";
    if (!empty($items)) {
        foreach ($items as $uniquehash => $item) {
            $feedtitle = $feed["xml"]->function_278($feed["titletemplate"], $item);
            $feedbody = function_277($feed["xml"]->function_278($feed["bodytemplate"], $item), false);
            $Queries = [];
            $Queries["fid"] = $feed["fid"];
            $Queries["subject"] = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $feedtitle) . "'";
            $Queries["uid"] = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $feed["userid"]) . "'";
            $Queries["username"] = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $feed["username"]) . "'";
            $Queries["dateline"] = $timenow;
            $Queries["message"] = "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $feedbody) . "'";
            $Queries["visible"] = $feed["moderate"] == 1 ? 0 : 1;
            if (1 < strlen($item["title"])) {
                $buildpostquery = [];
                foreach ($Queries as $_left => $_right) {
                    $buildpostquery[] = $_left . " = " . $_right;
                }
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO tsf_posts SET " . implode(",", $buildpostquery));
                $Queries["firstpost"] = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO tsf_threads (fid,subject,uid,username,dateline,firstpost,lastpost,visible,lastposter,lastposteruid) VALUES (" . $Queries["fid"] . "," . $Queries["subject"] . "," . $Queries["uid"] . "," . $Queries["username"] . "," . $Queries["dateline"] . "," . $Queries["firstpost"] . "," . $Queries["dateline"] . "," . $Queries["visible"] . "," . $Queries["username"] . "," . $Queries["uid"] . ")");
                $Queries["tid"] = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE tsf_posts SET $tid = " . $Queries["tid"] . " WHERE $pid = '" . $Queries["firstpost"] . "'");
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE tsf_forums SET $threads = threads + 1, $posts = posts + 1, $lastpost = " . $Queries["dateline"] . ", $lastposter = " . $Queries["username"] . ", $lastposteruid = " . $Queries["uid"] . ", $lastposttid = " . $Queries["tid"] . ", $lastpostsubject = " . $Queries["subject"] . " WHERE $fid = " . $Queries["fid"]);
                if ($feed["useparent"]) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE tsf_forums SET $lastpost = " . $Queries["dateline"] . ", $lastposter = " . $Queries["username"] . ", $lastposteruid = " . $Queries["uid"] . ", $lastposttid = " . $Queries["tid"] . ", $lastpostsubject = " . $Queries["subject"] . " WHERE $fid = " . $realforumid);
                }
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $totalposts = totalposts + 1 WHERE `id` = " . $Queries["uid"]);
                mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO ts_rsslog VALUES (" . $item["rssfeedid"] . ", " . $Queries["tid"] . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $uniquehash) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $item["contenthash"]) . "', " . $Queries["dateline"] . ")");
            }
            $output .= "<li><a $href = \"./../tsf_forums/showthread.php?$tid = " . $Queries["tid"] . "\" $target = \"_blank\">" . function_277($feedtitle) . "</a></li>";
        }
    } else {
        $output .= $Language[39];
    }
    $output .= "</ol>";
    echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=rss_feed_manager\">" . $Language[27] . "</a>") . "\r\n\t" . (isset($output) ? "\r\n\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" class=\"tborder\">\r\n\t\t<tr>\r\n\t\t\t<td $valign = \"top\" $align = \"left\" class=\"alt1\">\r\n\t\t\t\t<ol>\r\n\t\t\t\t\t" . $output . "\r\n\t\t\t\t</ol>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>" : "");
}
if ($Act == "new") {
    $active = isset($_POST["active"]) && $_POST["active"] == 0 ? 0 : 1;
    $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
    $url = isset($_POST["url"]) ? trim($_POST["url"]) : "";
    $ttl = isset($_POST["ttl"]) ? intval($_POST["ttl"]) : 14400;
    $maxresults = isset($_POST["maxresults"]) ? intval($_POST["maxresults"]) : 0;
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $fid = isset($_POST["fid"]) ? intval($_POST["fid"]) : 0;
    $moderate = isset($_POST["moderate"]) && $_POST["moderate"] == 1 ? 1 : 0;
    $userid = 0;
    $titletemplate = "{feed:title}";
    $bodytemplate = "{feed:description}\r\n[$url = {feed:link}]Read more...[/url]";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $Errors = "";
        if (!$title) {
            $Errors[] = $Language[34];
        }
        if (!$url) {
            $Errors[] = $Language[23];
        }
        if (!$username) {
            $Errors[] = $Language[35];
        } else {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
            if (!mysqli_num_rows($query)) {
                $Errors[] = $Language[37];
            } else {
                $Result = mysqli_fetch_assoc($query);
                $userid = $Result["id"];
            }
        }
        if (!$fid) {
            $Errors[] = $Language[36];
        } else {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid FROM tsf_forums WHERE $fid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $fid) . "'");
            if (!mysqli_num_rows($query)) {
                $Errors[] = $Language[38];
            }
        }
        if (!$Errors) {
            if (isset($_POST["save"])) {
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_rssfeed SET $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $url = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $url) . "', $ttl = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ttl) . "', $maxresults = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $maxresults) . "', $userid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $userid) . "', $fid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $fid) . "', $titletemplate = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $titletemplate) . "', $bodytemplate = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $bodytemplate) . "', $moderate = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $moderate) . "', $active = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $active) . "'");
                redirectTo("index.php?do=rss_feed_manager");
                exit;
            }
            if (isset($_POST["preview"])) {
            }
        } else {
            $Message = showAlertError(implode("<br />", $Errors));
        }
    }
    $ForumList = "<select $name = \"fid\"><option $value = \"0\">" . $Language[32] . "</option>";
    $Forums = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid, name FROM tsf_forums WHERE `type` != 'c' ORDER by disporder ASC");
    if (mysqli_num_rows($Forums)) {
        while ($Forum = mysqli_fetch_assoc($Forums)) {
            $ForumList .= "<option $value = \"" . $Forum["fid"] . "\"" . ($fid == $Forum["fid"] ? " $selected = \"selected\"" : "") . ">" . $Forum["name"] . "</option>";
        }
    }
    $ForumList .= "</select>";
    echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=rss_feed_manager\">" . $Language[27] . "</a>") . "\r\n\t" . $Message . "\r\n\t<form $method = \"post\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == 1 ? " $checked = \"checked\"" : "") . " /> " . $Language[28] . " <input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == 0 ? " $checked = \"checked\"" : "") . " /> " . $Language[29] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . $title . "\" $style = \"width: 400px;\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"url\" $value = \"" . $url . "\" $style = \"width: 400px;\" /></td>\r\n\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[15] . "</b></td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<select $name = \"ttl\" $style = \"width: 100px;\">\r\n\t\t\t\t\t<option $value = \"600\"" . ($ttl == 600 ? " $selected = \"selected\"" : "") . ">10 " . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"1200\"" . ($ttl == 1200 ? " $selected = \"selected\"" : "") . ">20 " . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"1800\"" . ($ttl == 1800 ? " $selected = \"selected\"" : "") . ">30 " . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"3600\"" . ($ttl == 3600 ? " $selected = \"selected\"" : "") . ">60 " . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"7200\"" . ($ttl == 7200 ? " $selected = \"selected\"" : "") . ">2 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"14400\"" . ($ttl == 14400 ? " $selected = \"selected\"" : "") . ">4 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"21600\"" . ($ttl == 21600 ? " $selected = \"selected\"" : "") . ">6 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"28800\"" . ($ttl == 28800 ? " $selected = \"selected\"" : "") . ">8 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"36000\"" . ($ttl == 36000 ? " $selected = \"selected\"" : "") . ">10 " . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"43200\"" . ($ttl == 43200 ? " $selected = \"selected\"" : "") . ">12 " . $Language[31] . "</option>\r\n\t\t\t\t</select>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"maxresults\" $value = \"" . $maxresults . "\" $style = \"width: 100px;\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"username\" $value = \"" . $username . "\" $style = \"width: 100px;\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[18] . "</b></td>\r\n\t\t\t<td class=\"alt1\">" . $ForumList . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\"><b>" . $Language[33] . "</b></td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"radio\" $name = \"moderate\" $value = \"1\"" . ($moderate == 1 ? " $checked = \"checked\"" : "") . " /> " . $Language[28] . " <input $type = \"radio\" $name = \"moderate\" $value = \"0\"" . ($moderate == 0 ? " $checked = \"checked\"" : "") . " /> " . $Language[29] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input $type = \"submit\" $name = \"save\" $value = \"" . $Language[19] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
}
if (empty($Act)) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT feed.*, forum.name as forumname, u.username, g.namestyle FROM ts_rssfeed feed LEFT JOIN tsf_forums forum ON (forum.$fid = feed.fid) LEFT JOIN users u ON (u.`id` = feed.userid) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) ORDER by title ASC");
    if (mysqli_num_rows($query)) {
        for ($Count = 0; $Feed = mysqli_fetch_assoc($query); $Count++) {
            $class = $Count % 2 == 1 ? "alt2" : "alt1";
            $URL = parse_url($Feed["url"]);
            $host = $URL["host"];
            $ListFeeds .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\">" . $Feed["title"] . "<br /><a $href = \"" . htmlspecialchars($Feed["url"]) . "\" $target = \"_blank\">" . htmlspecialchars($host) . "</></td>\r\n\t\t\t\t<td class=\"" . $class . "\">" . $Feed["forumname"] . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\">" . applyUsernameStyle($Feed["username"], $Feed["namestyle"]) . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\">" . ($Feed["lastrun"] ? formatTimestamp($Feed["lastrun"]) : "--") . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\">" . ($Feed["active"] == 1 ? $Language[28] : $Language[29]) . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\"><a $href = \"index.php?do=rss_feed_manager&amp;$act = edit&amp;$rssfeedid = " . $Feed["rssfeedid"] . "\"><img $src = \"./images/tool_edit.png\" $alt = \"" . $Language[24] . "\" $title = \"" . $Language[24] . "\" $border = \"0\"></a> <a $href = \"index.php?do=rss_feed_manager&amp;$act = run&amp;$rssfeedid = " . $Feed["rssfeedid"] . "\"><img $src = \"./images/tool_refresh.png\" $alt = \"" . $Language[8] . "\" $title = \"" . $Language[8] . "\" $border = \"0\"></a> <a $href = \"index.php?do=rss_feed_manager&amp;$act = delete&amp;$rssfeedid = " . $Feed["rssfeedid"] . "\" $onclick = \"return confirm('" . trim($Language[10]) . "');\"><img $src = \"./images/tool_delete.png\" $alt = \"" . $Language[25] . "\" $title = \"" . $Language[25] . "\" $border = \"0\"></a></td>\r\n\t\t\t</tr>";
        }
    }
    echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=rss_feed_manager&amp;$act = new\">" . $Language[11] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"6\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[3] . " &amp; " . $Language[14] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[7] . "</b></td>\r\n\t\t</tr>\r\n\t\t" . $ListFeeds . "\r\n\t</table>";
}
class Class_6
{
    public $TSParserVersion = "1.0.2";
    public $options = ["htmlspecialchars" => 1, "auto_url" => 1, "short_url" => 1];
    public $tscode_cache = [];
    public $message = "";
    public function __construct()
    {
    }
    public function function_119($message)
    {
        $this->function_117();
        $this->$message = str_replace("\r", "", $message);
        if ($this->options["htmlspecialchars"]) {
            $this->$message = htmlspecialchars($this->message);
            $this->$message = str_replace("&amp;", "&", $this->message);
        }
        $this->function_120();
        $this->function_122();
        $this->$message = nl2br($this->message);
        $this->function_126();
    }
    public function function_126($wraptext = "  ")
    {
        $feedId = 136;
        if (!empty($this->message)) {
            $this->$message = preg_replace("\r\n\t\t\t\t#((?>[^\\s&/<>\"\\-\\[\\]]|&[\\#a-z0-9]{1,7};){" . $feedId . "})(?=[^\\s&/<>\"\\-\\[\\]]|&[\\#a-z0-9]{1,7};)#i", "\$0" . $wraptext, $this->message);
        }
    }
    public function function_122()
    {
        if ($this->options["auto_url"]) {
            $this->function_130();
        }
        $this->$message = str_replace("\$", "&#36;", $this->message);
        $this->$message = preg_replace($this->tscode_cache["find"], $this->tscode_cache["replacement"], $this->message);
        $this->$message = preg_replace_callback("#\\[url\\]([a-z]+?://)([^\r\n\"<]+?)\\[/url\\]#si", function ($matches) {
            return $this->parseMyCodeUrl($matches[1] . $matches[2]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[url\\]([^\r\n\"<]+?)\\[/url\\]#si", function ($matches) {
            return $this->parseMyCodeUrl($matches[1]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[$url = ([a-z]+?://)([^\r\n\"<]+?)\\](.+?)\\[/url\\]#si", function ($matches) {
            return $this->parseMyCodeUrl($matches[1] . $matches[2], $matches[3]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[$url = ([^\r\n\"<&\\(\\)]+?)\\](.+?)\\[/url\\]#si", function ($matches) {
            return $this->parseMyCodeUrl($matches[1], $matches[2]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[email\\](.*?)\\[/email\\]#si", function ($matches) {
            return $this->function_132($matches[1]);
        }, $this->message);
        $this->$message = preg_replace_callback("#\\[$email = (.*?)\\](.*?)\\[/email\\]#si", function ($matches) {
            return $this->function_132($matches[1], $matches[2]);
        }, $this->message);
    }
    public function function_130()
    {
        $this->$message = " " . $this->message;
        $this->$message = preg_replace("#([\\>\\s\\(\\)])(https?|ftp|news){1}://([\\w\\-]+\\.([\\w\\-]+\\.)*[\\w]+(:[0-9]+)?(/[^\"\\s<\\[]*)?)#i", "\$1[url]\$2://\$3[/url]", $this->message);
        $this->$message = preg_replace("#([\\>\\s\\(\\)])(www|ftp)\\.(([\\w\\-]+\\.)*[\\w]+(:[0-9]+)?(/[^\"\\s<\\[]*)?)#i", "\$1[url]\$2.\$3[/url]", $this->message);
        $this->$message = substr($this->message, 1);
    }
    public function function_133($message, $type = "")
    {
        $message = str_replace("\\\"", "\"", $message);
        $message = preg_replace("#\\s*\\[\\*\\]\\s*#", "</li>\n<li>", $message);
        $message .= "</li>";
        if ($type) {
            $formattedListHtml = "\n<ol $type = \"" . $type . "\">" . $message . "</ol>\n";
        } else {
            $formattedListHtml = "<ul>" . $message . "</ul>\n";
        }
        $formattedListHtml = preg_replace("#<(ol $type = \"" . $type . "\"|ul)>\\s*</li>#", "<\$1>", $formattedListHtml);
        return $formattedListHtml;
    }
    public function function_134($url)
    {
        global $lang;
        $url = str_replace(["  ", "\"", "\\n", "\\r"], "", trim($url));
        return "<img $src = \"" . $url . "\" $border = \"0\" $alt = \"\" />";
    }
    public function function_120()
    {
        $urlReplaced = ["#(&\\#(0*)106;|&\\#(0*)74;|j)((&\\#(0*)97;|&\\#(0*)65;|a)(&\\#(0*)118;|&\\#(0*)86;|v)(&\\#(0*)97;|&\\#(0*)65;|a)(\\s)?(&\\#(0*)115;|&\\#(0*)83;|s)(&\\#(0*)99;|&\\#(0*)67;|c)(&\\#(0*)114;|&\\#(0*)82;|r)(&\\#(0*)105;|&\\#(0*)73;|i)(&\\#112;|&\\#(0*)80;|p)(&\\#(0*)116;|&\\#(0*)84;|t)(&\\#(0*)58;|\\:))#i", "#(o)(nmouseover\\s?=)#i", "#(o)(nmouseout\\s?=)#i", "#(o)(nmousedown\\s?=)#i", "#(o)(nmousemove\\s?=)#i", "#(o)(nmouseup\\s?=)#i", "#(o)(nclick\\s?=)#i", "#(o)(ndblclick\\s?=)#i", "#(o)(nload\\s?=)#i", "#(o)(nsubmit\\s?=)#i", "#(o)(nblur\\s?=)#i", "#(o)(nchange\\s?=)#i", "#(o)(nfocus\\s?=)#i", "#(o)(nselect\\s?=)#i", "#(o)(nunload\\s?=)#i", "#(o)(nkeypress\\s?=)#i"];
        $this->$message = preg_replace($urlReplaced, "\$1<strong></strong>\$2\$4", $this->message);
        unset($urlReplaced);
    }
    public function function_117()
    {
        $this->$tscode_cache = [];
        $bbcodeTags["b"]["regex"] = "#\\[b\\](.*?)\\[/b\\]#si";
        $bbcodeTags["b"]["replacement"] = "<div $style = \"font-weight: bold; display: inline;\">\$1</div>";
        $bbcodeTags["u"]["regex"] = "#\\[u\\](.*?)\\[/u\\]#si";
        $bbcodeTags["u"]["replacement"] = "<div $style = \"text-decoration: underline; display: inline;\">\$1</div>";
        $bbcodeTags["i"]["regex"] = "#\\[i\\](.*?)\\[/i\\]#si";
        $bbcodeTags["i"]["replacement"] = "<div $style = \"font-style: italic; display: inline;\">\$1</div>";
        $bbcodeTags["s"]["regex"] = "#\\[s\\](.*?)\\[/s\\]#si";
        $bbcodeTags["s"]["replacement"] = "<del>\$1</del>";
        $bbcodeTags["h"]["regex"] = "#\\[h\\](.*?)\\[/h\\]#si";
        $bbcodeTags["h"]["replacement"] = "<h3>\$1</h3>";
        $bbcodeTags["copy"]["regex"] = "#\\(c\\)#i";
        $bbcodeTags["copy"]["replacement"] = "&copy;";
        $bbcodeTags["tm"]["regex"] = "#\\(tm\\)#i";
        $bbcodeTags["tm"]["replacement"] = "&#153;";
        $bbcodeTags["reg"]["regex"] = "#\\(r\\)#i";
        $bbcodeTags["reg"]["replacement"] = "&reg;";
        $bbcodeTags["color"]["regex"] = "#\\[$color = ([a-zA-Z]*|\\#?[0-9a-fA-F]{6})](.*?)\\[/color\\]#si";
        $bbcodeTags["color"]["replacement"] = "<div $style = \"color: \$1; display: inline;\">\$2</div>";
        $bbcodeTags["size"]["regex"] = "#\\[$size = (xx-small|x-small|small|medium|large|x-large|xx-large)\\](.*?)\\[/size\\]#si";
        $bbcodeTags["size"]["replacement"] = "<div $style = \"font-size: \$1; display: inline;\">\$2</div>";
        $bbcodeTags["size_int"]["regex"] = "#\\[$size = ([0-9\\+\\-]+?)\\](.*?)\\[/size\\]#esi";
        $bbcodeTags["size_int"]["replacement"] = "\$this->tscode_handle_size(\"\$1\", \"\$2\")";
        $bbcodeTags["font"]["regex"] = "#\\[$font = ([a-z ]+?)\\](.+?)\\[/font\\]#si";
        $bbcodeTags["font"]["replacement"] = "<div $style = \"font-family: \$1; display: inline;\">\$2</div>";
        $bbcodeTags["align"]["regex"] = "#\\[$align = (left|center|right|justify)\\](.*?)\\[/align\\]#si";
        $bbcodeTags["align"]["replacement"] = "<div $style = \"text-align: \$1;\">\$2</div>";
        $bbcodeTags["hr"]["regex"] = "#\\[hr\\]#si";
        $bbcodeTags["hr"]["replacement"] = "<hr />";
        $bbcodeTags["pre"]["regex"] = "#\\[pre\\](.*?)\\[/pre\\]#si";
        $bbcodeTags["pre"]["replacement"] = "<pre>\$1</pre>";
        $bbcodeTags["nfo"]["regex"] = "#\\[nfo\\](.*?)\\[/nfo\\]#si";
        $bbcodeTags["nfo"]["replacement"] = "<tt><div $style = \"white-space: nowrap; display: inline;\"><font $face = \"MS Linedraw\" $size = \"2\" $style = \"font-size: 10pt; line-height: 10pt\">\$1</font></div></tt>";
        $bbcodeTags["youtube"]["regex"] = "#\\[youtube\\](.*?)\\[/youtube\\]#si";
        $bbcodeTags["youtube"]["replacement"] = "<object $width = \"425\" $height = \"350\"><param $name = \"movie\" $value = \"http://www.youtube.com/v/\$1\"></param><embed $src = \"http://www.youtube.com/v/\$1\" $type = \"application/x-shockwave-flash\" $width = \"425\" $height = \"350\"></embed></object>";
        $imageUrl = $bbcodeTags;
        foreach ($imageUrl as $processedCode) {
            $this->tscode_cache["find"][] = $processedCode["regex"];
            $this->tscode_cache["replacement"][] = $processedCode["replacement"];
        }
    }
    public function parseMyCodeUrl($url, $name = "")
    {
        $imageTag = false;
        if ($name) {
            $imageTag = true;
            $name = str_replace(["&amp;", "\\'"], ["&", "'"], $name);
        }
        if (!preg_match("#^[a-z0-9]+://#i", $url)) {
            $url = "http://" . $url;
        }
        $url = str_replace(["&amp;", "\\'"], ["&", "'"], $url);
        $processedUrl = $url;
        if (!$name) {
            $name = $url;
        }
        if (!$imageTag && $this->options["short_url"] && 55 < strlen($url)) {
            $name = substr($url, 0, 40) . "..." . substr($url, -10);
        }
        $feedTitle = ["\$" => "%24", "&#36;" => "%24", "^" => "%5E", "`" => "%60", "[" => "%5B", "]" => "%5D", "{" => "%7B", "}" => "%7D", "\"" => "%22", "<" => "%3C", ">" => "%3E", " " => "%20"];
        $processedUrl = str_replace(array_keys($feedTitle), array_values($feedTitle), $processedUrl);
        $name = preg_replace("#&amp;\\#([0-9]+);#si", "&#\$1;", $name);
        $link = "<a $href = \"" . $processedUrl . "\" $target = \"_blank\">" . $name . "</a>";
        return $link;
    }
    public function function_135($size, $text)
    {
        $size = intval($size) + 10;
        if (50 < $size) {
            $size = 50;
        }
        $text = "<div $style = \"font-size: " . $size . "pt; display: inline;\">" . str_replace("\\'", "'", $text) . "</div>";
        return $text;
    }
}
class Class_28
{
    public $xml_string = NULL;
    public $xml_array = NULL;
    public $xml_object = NULL;
    public $template = NULL;
    public $feedtype = NULL;
    public function __construct($options = NULL)
    {
    }
    public function function_280(&$xml_string)
    {
        $this->$xml_string = & $xml_string;
    }
    public function function_272($url)
    {
        $xml_string = rssGetFeed($url);
        if ($xml_string === false || empty($xml_string["body"])) {
            trigger_error("Unable to fetch RSS Feed", 512);
        }
        $xml_string = $xml_string["body"];
        if (preg_match_all("#(<description>)(.*)(</description>)#siU", $xml_string, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $feedItem) {
                if (strpos(strtoupper($feedItem[2]), "<![CDATA[") === false && strpos($feedItem[2], "<") !== false) {
                    $output = $feedItem[1] . "<![CDATA[" . $this->function_281($feedItem[2]) . "]]>" . $feedItem[3];
                    $xml_string = str_replace($feedItem[0], $output, $xml_string);
                }
            }
        }
        $this->function_280($xml_string);
        return true;
    }
    public function function_281($xml)
    {
        $xml = preg_replace("#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#", "", $xml);
        return str_replace(["<![CDATA[", "]]>"], ["�![CDATA[", "]]�"], $xml);
    }
    public function function_273($target_encoding = false, $ncrencode = false, $override_encoding = false, $escape_html = false)
    {
        $this->$xml_object = new Class_29($this->xml_string);
        $this->xml_object->function_282();
        $this->xml_object->function_283($target_encoding, $ncrencode, $escape_html);
        $this->xml_object->function_284($override_encoding);
        if ($this->xml_object->function_273()) {
            $this->$xml_array = & $this->xml_object->parseddata;
            if (isset($this->xml_array["xmlns"]) && preg_match("#^http://www.w3.org/2005/atom\$#i", $this->xml_array["xmlns"])) {
                $this->$feedtype = "atom";
            } else {
                if (is_array($this->xml_array["channel"])) {
                    $this->$feedtype = "rss";
                } else {
                    $this->$xml_array = [];
                    $this->$feedtype = "unknown";
                    return false;
                }
            }
            return true;
        }
        $this->$xml_array = [];
        $this->$feedtype = "";
        return false;
    }
    public function function_285($id = -1)
    {
        switch ($this->feedtype) {
            case "atom":
                return rssParseFeed($id);
                break;
            case "rss":
            default:
                return rssProcessItem($id);
        }
    }
    public function function_286($id = -1)
    {
        if (is_array($this->xml_array["entry"][0])) {
            $item =& $this->xml_array["entry"][$id == -1 ? $count++ : $id];
        } else {
            if ($count == 0 || $id == 0) {
                $item =& $this->xml_array["entry"];
            } else {
                $item = NULL;
            }
        }
        return $item;
    }
    public function function_287($id = -1)
    {
        if (is_array($this->xml_array["channel"]["item"][0])) {
            $item =& $this->xml_array["channel"]["item"][$id == -1 ? $count++ : $id];
        } else {
            if ($count == 0 || $id == 0) {
                $item =& $this->xml_array["channel"]["item"];
            } else {
                $item = NULL;
            }
        }
        return $item;
    }
    public function function_276()
    {
        switch ($this->feedtype) {
            case "atom":
                $feedUrl = "entry";
                break;
            case "rss":
            default:
                $feedUrl = "item";
                return rssValidateFeed($this->xml_array, $feedUrl, true);
        }
    }
    public function function_288()
    {
        $items = $this->function_276();
        if (empty($items)) {
            return false;
        }
        $feedEnabled = [];
        foreach ($items as $item) {
            $feedCategory = ["link" => $this->function_289("link", $item), "description" => $this->function_289("description", $item), "title" => $this->function_289("title", $item), "id" => $this->function_289("id", $item), "date" => $this->function_289("date", $item), "enclosure_link" => $this->function_289("enclosure_date", $item), "content" => $this->function_289("content", $item), "author" => $this->function_289("author", $item)];
            $feedCategory["link"] = $this->function_290($feedCategory["link"]);
            $feedCategory["enclosure_link"] = $this->function_290($feedCategory["enclosure_link"]);
            $feedEnabled[] = $feedCategory;
        }
        return $feedEnabled;
    }
    public function function_291($var)
    {
        return preg_replace($feedInterval, $preg_replace, htmlspecialchars(trim($var)));
    }
    public function function_290($url)
    {
        if ($query = parse_url($url, PHP_URL_QUERY)) {
            $url = substr($url, 0, strpos($url, "?"));
            $url = $this->function_291($url);
            return $url . "?" . $query;
        }
        return $this->function_291($url);
    }
    public function function_278($template, $item, $decodeSecuredHTML = true)
    {
        if (preg_match_all("#\\{(?:feed|rss):([\\w:\\[\\]]+)\\}#siU", $template, $matches)) {
            foreach ($matches[0] as $feedLastUpdate => $dbTable) {
                $replacementPatterns = $this->function_289($matches[1][$feedLastUpdate], $item);
                $template = str_replace($dbTable, $replacementPatterns, $template);
            }
        }
        if ($decodeSecuredHTML) {
            $template = function_292($template);
        }
        return $template;
    }
    public function function_289($field, $item)
    {
        switch ($this->feedtype) {
            case "atom":
                $feedItems = NULL;
                if ($feedItems !== NULL) {
                    return $feedItems;
                }
                switch ($field) {
                    case "link":
                        if (empty($item["link"])) {
                            if (!empty($item["guid"])) {
                                return $item["guid"]["value"];
                            }
                            return "";
                        }
                        if (empty($item["link"][0])) {
                            return $item["link"]["href"];
                        }
                        foreach ($item["link"] as $link) {
                            if ($link["rel"] == "alternate" || empty($link["rel"])) {
                                return $link["href"];
                            }
                        }
                        break;
                    case "description":
                        return function_279($item["summary"]);
                        break;
                    case "title":
                        return function_279($item["title"]);
                        break;
                    case "id":
                        return function_279($item["id"]);
                        break;
                    case "date":
                        $apiEndpoint = strtotime(function_279($item["updated"]));
                        if (0 < $apiEndpoint) {
                            return formatTimestamp($apiEndpoint);
                        }
                        return function_279($item["updated"]);
                        break;
                    case "enclosure_link":
                        if (empty($item["link"][0])) {
                            return "";
                        }
                        foreach ($item["link"] as $link) {
                            if ($link["rel"] == "enclosure") {
                                return $link["href"];
                            }
                        }
                        break;
                    case "content":
                    case "content:encoded":
                        if (empty($item["content"][0])) {
                            return function_279($item["content"]);
                        }
                        $emailHeaderEncoded = [];
                        foreach ($item["content"] as $tableRow) {
                            if (is_array($tableRow)) {
                                if ($tableRow["type"] == "html" && $emailHeaderEncoded["type"] != "xhtml") {
                                    $emailHeaderEncoded = $tableRow;
                                } else {
                                    if ($tableRow["type"] == "text" && !($emailHeaderEncoded["type"] == "html" || $emailHeaderEncoded["type"] == "xhtml")) {
                                        $emailHeaderEncoded = $tableRow;
                                    } else {
                                        if ($tableRow["type"] == "xhtml") {
                                            $emailHeaderEncoded = $tableRow;
                                        } else {
                                            if ($tableRow["type"] != "xhtml" || $tableRow["type"] != "xhtml" || $tableRow["type"] != "xhtml") {
                                                $emailHeaderEncoded = $tableRow;
                                            }
                                        }
                                    }
                                }
                            } else {
                                if (empty($emailHeaderEncoded["type"])) {
                                    $emailHeaderEncoded["value"] = $tableRow;
                                }
                            }
                        }
                        return $emailHeaderEncoded["value"];
                        break;
                    case "author":
                        return function_279($item["author"]["name"]);
                        break;
                    default:
                        if (is_array($item[(string) $field])) {
                            if (is_string($item[(string) $field]["value"])) {
                                return $item[(string) $field]["value"];
                            }
                            return "";
                        }
                        return $item[(string) $field];
                }
                break;
            case "rss":
                $feedItems = NULL;
                if ($feedItems !== NULL) {
                    return $feedItems;
                }
                switch ($field) {
                    case "link":
                        if (empty($item["link"])) {
                            if (!empty($item["guid"])) {
                                return $item["guid"]["value"];
                            }
                            return "";
                        }
                        if (is_array($item["link"]) && isset($item["link"]["href"])) {
                            return $item["link"]["href"];
                        }
                        return function_279($item["link"]);
                        break;
                    case "description":
                        return function_279($item["description"]);
                        break;
                    case "title":
                        return function_279($item["title"]);
                        break;
                    case "id":
                    case "guid":
                        return function_279($item["guid"]);
                        break;
                    case "pubDate":
                    case "date":
                        $apiEndpoint = strtotime(function_279($item["pubDate"]));
                        if (0 < $apiEndpoint) {
                            return formatTimestamp($apiEndpoint);
                        }
                        return $item["pubDate"];
                        break;
                    case "enclosure_link":
                    case "enclosure_href":
                        if (is_array($item["enclosure"])) {
                            return $item["enclosure"]["url"];
                        }
                        return "";
                        break;
                    case "content":
                    case "content:encoded":
                        return function_279($item["content:encoded"]);
                        break;
                    case "author":
                    case "dc:creator":
                        if (isset($item["dc:creator"])) {
                            return function_279($item["dc:creator"]);
                        }
                        return $item["author"];
                        break;
                    default:
                        if (is_array($item[(string) $field])) {
                            if (is_string($item[(string) $field]["value"])) {
                                return $item[(string) $field]["value"];
                            }
                            return "";
                        }
                        return $item[(string) $field];
                }
                break;
        }
    }
}
class Class_29
{
    public $xml_parser = NULL;
    public $error_no = 0;
    public $xmldata = "";
    public $parseddata = [];
    public $stack = [];
    public $cdata = "";
    public $tag_count = 0;
    public $include_first_tag = false;
    public $error_code = 0;
    public $error_line = 0;
    public $legacy_mode = true;
    public $encoding = NULL;
    public $target_encoding = NULL;
    public $ncr_encode = NULL;
    public $escape_html = NULL;
    public function __construct($xml, $path = "")
    {
        if ($xml !== false) {
            $this->$xmldata = $xml;
        } else {
            if (empty($path)) {
                $this->$error_no = 1;
            } else {
                if (!($this->$xmldata = @file_get_contents($path))) {
                    $this->$error_no = 2;
                }
            }
        }
    }
    public function &rssSaveFeed($encoding = "ISO-8859-1", $emptydata = true)
    {
        $this->$encoding = $encoding;
        if (!$this->legacy_mode) {
            $this->function_293();
        }
        if (empty($this->xmldata) || 0 < $this->error_no) {
            $this->$error_code = XML_ERROR_NO_ELEMENTS + ("5.2.8" < PHP_VERSION ? 0 : 1);
            return false;
        }
        if (!($this->$xml_parser = xml_parser_create($encoding))) {
            return false;
        }
        xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 0);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_character_data_handler($this->xml_parser, [$this, "handle_cdata"]);
        xml_set_element_handler($this->xml_parser, [$this, "handle_element_start"], [$this, "handle_element_end"]);
        xml_parse($this->xml_parser, $this->xmldata, true);
        $xmlData = xml_get_error_code($this->xml_parser);
        if ($emptydata) {
            $this->$xmldata = "";
            $this->$stack = [];
            $this->$cdata = "";
        }
        if ($xmlData) {
            $this->$error_code = @xml_get_error_code($this->xml_parser);
            $this->$error_line = @xml_get_current_line_number($this->xml_parser);
            xml_parser_free($this->xml_parser);
            return false;
        }
        xml_parser_free($this->xml_parser);
        return $this->parseddata;
    }
    public function function_273()
    {
        if ($this->legacy_mode) {
            return $this->function_294();
        }
        if (preg_match("#(<?xml.*$encoding = ['\"])(.*?)(['\"].*?>)#m", $this->xmldata, $feedItem)) {
            $encoding = strtoupper($feedItem[2]);
            if ($encoding != "UTF-8") {
                $this->$xmldata = str_replace($feedItem[0], $feedItem[1] . "UTF-8" . $feedItem[3], $this->xmldata);
            }
            if (!$this->encoding) {
                $this->$encoding = $encoding;
            }
        } else {
            if (!$this->encoding) {
                $this->$encoding = "UTF-8";
            }
            if (strpos($this->xmldata, "<?xml") === false) {
                $this->$xmldata = "<?xml $version = \"1.0\" $encoding = \"UTF-8\"?>\n" . $this->xmldata;
            } else {
                $this->$xmldata = preg_replace("#(<?xml.*)(\\?>)#", "\\1 $encoding = \"UTF-8\" \\2", $this->xmldata);
            }
        }
        if ("UTF-8" !== $this->encoding) {
            $this->$xmldata = $this->function_295($this->xmldata, $this->encoding);
        }
        if (!$this->rssSaveFeed("UTF-8")) {
            return false;
        }
        return true;
    }
    public function function_295($in, $charset = false, $strip = true)
    {
        if ("" === $in || false === $in || is_null($in)) {
            return $in;
        }
        if (!$charset) {
            $charset = "UTF-8";
        }
        if (function_exists("iconv")) {
            $apiResponse = @iconv($charset, "UTF-8//IGNORE", $in);
            return $apiResponse;
        }
        if (function_exists("mb_convert_encoding")) {
            return @mb_convert_encoding($in, "UTF-8", $charset);
        }
        if (!$strip) {
            return $in;
        }
        $xmlParser = "#([\\x09\\x0A\\x0D\\x20-\\x7E]|[\\xC2-\\xDF][\\x80-\\xBF]|\\xE0[\\xA0-\\xBF][\\x80-\\xBF]|[\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2}|\\xED[\\x80-\\x9F][\\x80-\\xBF]|\\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}|[\\xF1-\\xF3][\\x80-\\xBF]{3}|\\xF4[\\x80-\\x8F][\\x80-\\xBF]{2})#S";
        $apiResponse = "";
        $matches = [];
        while (preg_match($xmlParser, $in, $matches)) {
            $apiResponse .= $matches[0];
            $in = substr($in, strlen($matches[0]));
        }
        return $apiResponse;
    }
    public function function_294()
    {
        if (preg_match("#(<?xml.*$encoding = ['\"])(.*?)(['\"].*?>)#m", $this->xmldata, $feedItem)) {
            $xmlElement = strtoupper($feedItem[2]);
            if ($xmlElement == "ISO-8859-1") {
                $xmlElement = "WINDOWS-1252";
            }
            if ($xmlElement != "UTF-8") {
                $this->$xmldata = str_replace($feedItem[0], $feedItem[1] . "ISO-8859-1" . $feedItem[3], $this->xmldata);
            }
        } else {
            $xmlElement = "UTF-8";
            if (strpos($this->xmldata, "<?xml") === false) {
                $this->$xmldata = "<?xml $version = \"1.0\" $encoding = \"ISO-8859-1\"?>\n" . $this->xmldata;
            } else {
                $this->$xmldata = preg_replace("#(<?xml.*)(\\?>)#", "\\1 $encoding = \"ISO-8859-1\" \\2", $this->xmldata);
            }
            $xmlElement = "ISO-8859-1";
        }
        $xmlAttributes = $this->xmldata;
        $target_encoding = $charset == "iso-8859-1" ? "WINDOWS-1252" : "UTF-8";
        $itemTitle = $xmlElement != "UTF-8" ? "ISO-8859-1" : "UTF-8";
        $itemLink = false;
        if (strtoupper($xmlElement) !== strtoupper($target_encoding)) {
            if (function_exists("iconv") && ($itemDescription = iconv($xmlElement, $target_encoding . "//TRANSLIT", $this->xmldata))) {
                $itemLink = true;
                $this->$xmldata = & $itemDescription;
            }
            if (!$itemLink && function_exists("mb_convert_encoding") && ($itemDescription = @mb_convert_encoding($this->xmldata, $target_encoding, $xmlElement))) {
                $this->$xmldata = & $itemDescription;
            }
        }
        if ($this->rssSaveFeed($itemTitle)) {
            return true;
        }
        if ($itemLink && ($this->$xmldata = iconv($xmlElement, $target_encoding . "//IGNORE", $xmlAttributes))) {
            if ($this->rssSaveFeed($itemTitle)) {
                return true;
            }
            return false;
        }
        return false;
    }
    public function function_296(&$parser, $data)
    {
        $this->cdata .= $data;
    }
    public function function_297(&$parser, $name, $attribs)
    {
        $this->$cdata = "";
        foreach ($attribs as $key => $val) {
            if (preg_match("#&[a-z]+;#i", $val)) {
                $attribs[(string) $key] = function_292($val);
            }
        }
        array_unshift($this->stack, ["name" => $name, "attribs" => $attribs, "tag_count" => ++$this->tag_count]);
    }
    public function function_298(&$parser, $name)
    {
        $itemDate = array_shift($this->stack);
        if ($itemDate["name"] != $name) {
            return NULL;
        }
        $output = $itemDate["attribs"];
        if (trim($this->cdata) !== "" || $itemDate["tag_count"] == $this->tag_count) {
            if (sizeof($output) == 0) {
                $output = $this->function_299($this->cdata);
            } else {
                $this->function_300($output, "value", $this->function_299($this->cdata));
            }
        }
        if (isset($this->stack[0])) {
            $this->function_300($this->stack[0]["attribs"], $name, $output);
        } else {
            if ($this->include_first_tag) {
                $this->$parseddata = [$name => $output];
            } else {
                $this->$parseddata = $output;
            }
        }
        $this->$cdata = "";
    }
    public function function_274()
    {
        if ($itemGuid = @xml_error_string(@$this->function_301())) {
            return $itemGuid;
        }
        return "unknown";
    }
    public function function_275()
    {
        if ($this->error_line) {
            return $this->error_line;
        }
        return 0;
    }
    public function function_301()
    {
        if ($this->error_code) {
            return $this->error_code;
        }
        return 0;
    }
    public function function_300(&$children, $name, $value)
    {
        if (!is_array($children) || !in_array($name, array_keys($children))) {
            $children[$name] = $value;
        } else {
            if (is_array($children[$name]) && isset($children[$name][0])) {
                $children[$name][] = $value;
            } else {
                $children[$name] = [$children[$name]];
                $children[$name][] = $value;
            }
        }
    }
    public function function_299($xml)
    {
        if (!is_array($logMessage)) {
            $logMessage = ["�![CDATA[", "]]�", "\r\n", "\n"];
            $replacementPatterns = ["<![CDATA[", "]]>", "\n", "\r\n"];
        }
        if (!$this->legacy_mode && $this->encoding != $this->target_encoding) {
            $xml = $this->function_194($xml);
        }
        return str_replace($logMessage, $replacementPatterns, $xml);
    }
    public function function_284($encoding)
    {
        $this->$encoding = $encoding;
    }
    public function function_283($target_encoding, $ncr_encode = false, $escape_html = false)
    {
        $this->$target_encoding = $target_encoding;
        $this->$ncr_encode = $ncr_encode;
        $this->$escape_html = $escape_html;
    }
    public function function_293()
    {
        if (!$this->target_encoding) {
            $this->$target_encoding = "UTF-8";
        }
        $this->$target_encoding = strtoupper($this->target_encoding);
        if ("ISO-8859-1" == $this->target_encoding) {
            $this->$target_encoding = "WINDOWS-1252";
        }
    }
    public function function_194($data)
    {
        if ($this->$encoding = = $this->target_encoding) {
            return $data;
        }
        if ($this->escape_html) {
            $data = @htmlspecialchars($data, ENT_COMPAT, $this->encoding);
        }
        if ($this->ncr_encode) {
            $data = ncrencode($data, true);
        }
        return rssFormatItem($data, $this->encoding, $this->target_encoding);
    }
    public function function_282($disable = true)
    {
        $this->$legacy_mode = !$disable;
    }
}
class Class_30
{
    public $charset = "windows-1252";
    public $content_type = "text/xml";
    public $open_tags = [];
    public $tabs = "";
    public function __construct($content_type = NULL, $charset = NULL)
    {
        if ($content_type) {
            $this->$content_type = $content_type;
        }
        if ($charset == NULL) {
            $charset = $this->registry->userinfo["lang_charset"];
        }
        $this->$charset = strtolower($charset) == "iso-8859-1" ? "windows-1252" : $charset;
    }
    public function function_302()
    {
        return "Content-Type: " . $this->content_type . ($this->$charset = = "" ? "" : "; $charset = " . $this->charset);
    }
    public function function_303()
    {
        return "Content-Length: " . $this->function_304();
    }
    public function function_305()
    {
        @header("Content-Type: " . $this->content_type . ($this->$charset = = "" ? "" : "; $charset = " . $this->charset));
    }
    public function function_306()
    {
        @header("Content-Length: " . @$this->function_304());
    }
    public function function_307()
    {
        return "<?xml $version = \"1.0\" $encoding = \"" . $this->charset . "\"?>" . "\n";
    }
    public function function_304()
    {
        return strlen($this->doc) + strlen($this->function_307());
    }
    public function function_308($tag, $attr = [])
    {
        $this->open_tags[] = $tag;
        $this->doc .= $this->tabs . $this->function_309($tag, $attr) . "\n";
        $this->tabs .= "\t";
    }
    public function function_310()
    {
        $tag = array_pop($this->open_tags);
        $this->$tabs = substr($this->tabs, 0, -1);
        $this->doc .= $this->tabs . "</" . $tag . ">\n";
    }
    public function function_311($tag, $content = "", $attr = [], $cdata = false, $htmlspecialchars = false)
    {
        $this->doc .= $this->tabs . $this->function_309($tag, $attr, $content === "");
        if ($content !== "") {
            if ($htmlspecialchars) {
                $this->doc .= htmlspecialchars($content);
            } else {
                if ($cdata || preg_match("/[\\<\\>\\&'\\\"\\[\\]]/", $content)) {
                    $this->doc .= "<![CDATA[" . $this->function_281($content) . "]]>";
                } else {
                    $this->doc .= $content;
                }
            }
            $this->doc .= "</" . $tag . ">\n";
        }
    }
    public function function_309($tag, $attr, $closing = false)
    {
        $loopCounter = "<" . $tag;
        if (!empty($attr)) {
            foreach ($attr as $feedError => $parseSuccess) {
                if (strpos($parseSuccess, "\"") !== false) {
                    $parseSuccess = htmlspecialchars($parseSuccess);
                }
                $loopCounter .= " " . $feedError . "=\"" . $parseSuccess . "\"";
            }
        }
        $loopCounter .= $closing ? " />\n" : ">";
        return $loopCounter;
    }
    public function function_281($xml)
    {
        $xml = preg_replace("#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#", "", $xml);
        return str_replace(["<![CDATA[", "]]>"], ["�![CDATA[", "]]�"], $xml);
    }
    public function function_312()
    {
        if (!empty($this->open_tags)) {
            trigger_error("There are still open tags within the document", 256);
            return false;
        }
        return $this->doc;
    }
    public function function_313($full_shutdown = false)
    {
        $this->function_305();
        if (strpos($_SERVER["SERVER_SOFTWARE"], "Microsoft-IIS") !== false) {
            $this->function_306();
        }
        echo $this->function_272();
        exit;
    }
    public function function_272()
    {
        return $this->function_307() . $this->function_312();
    }
}
class Class_31 extends Class_29
{
}
class Class_32 extends Class_30
{
}
function function_271($value)
{
    $value = trim($value);
    $validFeed = intval($value);
    strtolower($value[strlen($value) - 1]);
    switch (strtolower($value[strlen($value) - 1])) {
        case "g":
            $validFeed *= 1024;
            break;
        case "m":
            $validFeed *= 1024;
            break;
        case "k":
            $validFeed *= 1024;
            break;
        default:
            return $validFeed;
    }
}
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function formatTimestamp($timestamp = "")
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, $timestamp);
}
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_292($text, $doUniCode = false)
{
    if ($doUniCode) {
        $text = preg_replace_callback("/&#([0-9]+);/siU", function ($matches) {
            return convertUtf8Char($matches[1]);
        }, $text);
    }
    return str_replace(["&lt;", "&gt;", "&quot;", "&amp;"], ["<", ">", "\"", "&"], $text);
}
function function_277($text, $parse = true)
{
    return strip_tags($text);
}
function function_314($rawurl, $postfields = [])
{
    $url = @parse_url($rawurl);
    if (!$url || empty($url["host"])) {
        trigger_error("Invalid URL specified to fetch_file_via_socket()", 256);
        return false;
    }
    if ($url["scheme"] == "https") {
        $url["port"] = $url["port"] ? $url["port"] : 443;
    } else {
        $url["port"] = isset($url["port"]) && $url["port"] ? $url["port"] : 80;
    }
    $url["path"] = isset($url["path"]) && $url["path"] ? $url["path"] : "/";
    if (empty($postfields)) {
        if (isset($url["query"]) && $url["query"]) {
            $url["path"] .= "?" . $url["query"];
        }
        $url["query"] = "";
        $feedContent = "GET";
    } else {
        $dbQuery = [];
        foreach ($postfields as $key => $value) {
            if (!empty($value)) {
                $dbQuery[] = $key . "=" . urlencode($value);
            }
        }
        $url["query"] = implode("&", $dbQuery);
        $feedContent = "POST";
    }
    $itemsProcessed = false;
    if (function_exists("curl_init") && ($ch = curl_init())) {
        curl_setopt($ch, CURLOPT_URL, $rawurl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        if ($feedContent == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $url["query"]);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "TSSE via cURL/PHP");
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        @curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        $itemsSaved = curl_exec($ch);
        curl_close($ch);
        if ($itemsSaved !== false) {
            $itemsProcessed = true;
        }
    }
    if (!$itemsProcessed) {
        $fp = fsockopen($url["host"], $url["port"], fsockError, fsockErrorStr, 5);
        if (!$fp) {
            trigger_error("Unable to connect to host <i>" . $url["host"] . "</i>.<br />" . fsockErrorStr, 256);
            return false;
        }
        socket_set_timeout($fp, 5);
        $responseCode = $feedContent . " " . $url["path"] . " HTTP/1.0\r\n";
        $responseCode .= "Host: " . $url["host"] . "\r\n";
        $responseCode .= "User-Agent: TSSE RSS Reader\r\n";
        if (function_exists("gzinflate")) {
            $responseCode .= "Accept-Encoding: gzip\r\n";
        }
        if ($feedContent == "POST") {
            $responseCode .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $responseCode .= "Content-Length: " . strlen($url["query"]) . "\r\n";
        }
        $responseCode .= "\r\n";
        fwrite($fp, $responseCode . $url["query"]);
        $itemsSaved = "";
        while (!feof($fp)) {
            $result = fgets($fp, 1024);
            $itemsSaved .= $result;
        }
        fclose($fp);
    }
    preg_match("#^(.*)\\r\\n\\r\\n(.*)\$#sU", $itemsSaved, $matches);
    unset($itemsSaved);
    if ($itemsProcessed) {
        while (preg_match("#\r\nLocation: #i", $matches[1])) {
            preg_match("#^(.*)\\r\\n\\r\\n(.*)\$#sU", $matches[2], $matches);
        }
    }
    if (function_exists("gzinflate") && preg_match("#\r\nContent-encoding: gzip\r\n#i", $matches[1]) && ($duplicates = @gzinflate(@substr($matches[2], 10)))) {
        $matches[2] = $duplicates;
    }
    return ["headers" => $matches[1], "body" => $matches[2]];
}
function function_144(&$array, $tagname, $reinitialise = false, $depth = 0)
{
    if ($reinitialise) {
        $output = [];
    }
    if (is_array($array)) {
        foreach (array_keys($array) as $key) {
            if ($key === $tagname) {
                if (is_array($array[(string) $key])) {
                    if ($array[(string) $key][0]) {
                        foreach (array_keys($array[(string) $key]) as $totalItems) {
                            $output[] =& $array[(string) $key][(string) $totalItems];
                        }
                    } else {
                        $output[] =& $array[(string) $key];
                    }
                }
            } else {
                if (is_array($array[(string) $key]) && $depth < 30) {
                    pluginActivate($array[(string) $key], $tagname, false, $depth + 1);
                }
            }
        }
    }
    return $output;
}
function function_279($item)
{
    return is_array($item) ? $item["value"] : $item;
}

?>