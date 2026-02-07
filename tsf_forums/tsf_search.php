<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "tsf_search.php");
require "./global.php";
if (0 < $CURUSER["postsperpage"] && is_valid_id($CURUSER["postsperpage"]) && $CURUSER["postsperpage"] <= 50) {
    $postperpage = intval($CURUSER["postsperpage"]);
} else {
    $postperpage = $f_postsperpage;
}
$timecut = TIMENOW - 86400;
sql_query("DELETE FROM " . TSF_PREFIX . "searchlog WHERE dateline<='" . $timecut . "'");
add_breadcrumb($lang->tsf_forums["search"], $BASEURL . "/tsf_forums/tsf_search.php");
if (!$action || $action == "searchthread") {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    $Inthread = $action == "searchthread" ? true : false;
    if ($Inthread) {
        $threadid = isset($_POST["threadid"]) ? intval($_POST["threadid"]) : (isset($_GET["threadid"]) ? intval($_GET["threadid"]) : 0);
        if (!is_valid_id($threadid)) {
            stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
            exit;
        }
        ($query = sql_query("SELECT\r\n\t\t\tt.tid, f.type, f.fid as currentforumid, ff.fid as deepforumid\r\n\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = " . sqlesc($threadid) . " LIMIT 1")) || sqlerr(__FILE__, 59);
        if (mysqli_num_rows($query) == 0) {
            stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
            exit;
        }
        $thread = mysqli_fetch_assoc($query);
        $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
        if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes")) {
            print_no_permission(true);
            exit;
        }
    }
    stdhead($lang->tsf_forums["search"]);
    $sfid = isset($_GET["sfid"]) ? intval($_GET["sfid"]) : "all";
    if (isset($warningmessage)) {
        echo $warningmessage;
    }
    build_breadcrumb();
    if (!$Inthread) {
        $query = sql_query("\r\n\t\t\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t\t")) || sqlerr(__FILE__, 90);
        while ($forum = mysqli_fetch_assoc($query)) {
            if (!isset($_COOKIE["forumpass_" . $forum["fid"]])) {
                $_COOKIE["forumpass_" . $forum["fid"]] = "";
            }
            if (!($forum["password"] != "" && $_COOKIE["forumpass_" . $forum["fid"]] != md5($CURUSER["id"] . $forum["password"] . $securehash))) {
                if (isset($permissions[$forum["fid"]]["canview"]) && $permissions[$forum["fid"]]["canview"] == "yes") {
                    $deepsubforums[$forum["pid"]] = (isset($deepsubforums[$forum["pid"]]) ? $deepsubforums[$forum["pid"]] : "") . "\r\n\t\t\t\t<option $value = \"" . $forum["fid"] . "\"" . ($sfid == $forum["fid"] ? " $selected = \"selected\"" : "") . ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $forum["name"] . "</option>";
                }
            }
        }
        ($query = sql_query("\r\n\t\t\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\t\tWHERE f.$type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t\t")) || sqlerr(__FILE__, 112);
        $str = "\r\n\t\t\t<select $name = \"forums[]\" $size = \"13\" $multiple = \"multiple\" $style = \"width: 450px;\">\r\n\t\t\t<optgroup $label = \"" . $SITENAME . " Forums\">\r\n\t\t\t\t<option $value = \"all\"" . ($sfid == "all" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["select1"] . "</option>";
        while ($forum = mysqli_fetch_assoc($query)) {
            if (!isset($_COOKIE["forumpass_" . $forum["fid"]])) {
                $_COOKIE["forumpass_" . $forum["fid"]] = "";
            }
            if (!($forum["password"] != "" && $_COOKIE["forumpass_" . $forum["fid"]] != md5($CURUSER["id"] . $forum["password"] . $securehash))) {
                if ($permissions[$forum["fid"]]["canview"] == "yes") {
                    $subforums[$forum["pid"]] = (isset($subforums[$forum["pid"]]) ? $subforums[$forum["pid"]] : "") . "\r\n\t\t\t\t\t<option $value = \"" . $forum["fid"] . "\"" . ($sfid == $forum["fid"] ? " $selected = \"selected\"" : "") . ">&nbsp;&nbsp;&nbsp;&nbsp;" . $forum["name"] . "</option>" . (isset($deepsubforums[$forum["fid"]]) ? $deepsubforums[$forum["fid"]] : "");
                }
            }
        }
        $query = sql_query("\r\n\t\t\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\t\tWHERE f.$type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\t\tWHERE f.$type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t\t")) || sqlerr(__FILE__, 138);
        while ($category = mysqli_fetch_assoc($query)) {
            if (!isset($_COOKIE["forumpass_" . $category["fid"]])) {
                $_COOKIE["forumpass_" . $category["fid"]] = "";
            }
            if (!($category["password"] != "" && $_COOKIE["forumpass_" . $category["fid"]] != md5($CURUSER["id"] . $category["password"] . $securehash))) {
                if ($permissions[$category["fid"]]["canview"] == "yes" && isset($subforums[$category["fid"]])) {
                    $str .= "\r\n\t\t\t\t\t<option $value = \"" . $category["fid"] . "\">" . $category["name"] . "</option>" . $subforums[$category["fid"]] . "";
                }
            }
        }
        $str .= "\r\n\t\t\t\t\t</optgroup>\r\n\t\t\t\t</select> ";
    }
    echo "\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t" . ($Inthread ? "\r\n\t<input $type = \"hidden\" $name = \"action\" $value = \"searchinthread\" />\r\n\t<input $type = \"hidden\" $name = \"threadid\" $value = \"" . $threadid . "\" />" : "\r\n\t<input $type = \"hidden\" $name = \"action\" $value = \"do_search\" />\r\n\t") . "\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" $align = \"center\" $width = \"100%\">\r\n\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" class=\"thead\"><strong>" . $lang->tsf_forums["title"] . "</strong></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\r\n\t\t\t<td>\r\n\t\t\t\t<fieldset class=\"fieldset\" $style = \"padding: 5px 10px 10px 5px;\">\r\n\t\t\t\t\t<legend>" . $lang->tsf_forums["title1"] . "</legend>\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t" . $lang->tsf_forums["option1"] . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"keywords\" $value = \"\" $id = \"specialboxn\" />\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t" . (!$Inthread ? "\r\n\t\t\t\t\t<div $style = \"padding: 5px 0px 0px 0px;\">\r\n\t\t\t\t\t\t<select $name = \"postthread\">\r\n\t\t\t\t\t\t\t<option $value = \"1\" $selected = \"selected\">" . $lang->tsf_forums["option2"] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"0\" >" . $lang->tsf_forums["option3"] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</div>" : "") . "\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t\t" . (!$Inthread ? "\r\n\t\t\t<td $rowspan = \"2\">\r\n\t\t\t\t<fieldset class=\"fieldset\" $style = \"padding: 5px 10px 10px 5px;\">\r\n\t\t\t\t\t<legend>" . $lang->tsf_forums["option8"] . "</legend>\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t" . $str . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>" : "") . "\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<fieldset class=\"fieldset\" $style = \"padding: 5px 10px 10px 5px;\">\r\n\t\t\t\t\t<legend>" . $lang->tsf_forums["title2"] . "</legend>\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t" . $lang->tsf_forums["option4"] . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"author\" $value = \"\" $id = \"specialboxn\" $rel = \"autoCompleteUsers\" />\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div $style = \"padding: 5px 0px 0px 0px;\">\r\n\t\t\t\t\t\t<input $name = \"matchusername\" $value = \"1\" $checked = \"checked\" $type = \"checkbox\" class=\"inlineimg\" />" . $lang->tsf_forums["option7"] . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr><td $colspan = \"2\" $align = \"center\" class=\"thead\"> <input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->tsf_forums["button_1"] . "\" /> <input $type = \"reset\" $name = \"reset\" $value = \"" . $lang->tsf_forums["button_2"] . "\" /></td></tr>\r\n\t</table>\r\n\t</form>";
    stdfoot();
    exit;
}
if ($action == "finduserthreads") {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    if (empty($_GET["id"]) || !is_valid_id($_GET["id"])) {
        print_no_permission(true);
    }
    $where_sql = "t.$uid = '" . intval($_GET["id"]) . "'";
    ($query = sql_query("SELECT fp.fid,f.fid FROM " . TSF_PREFIX . "forumpermissions fp LEFT JOIN " . TSF_PREFIX . "forums f ON (fp.$fid = f.pid) WHERE (fp.$canview = 'no' OR fp.$cansearch = 'no') AND fp.$gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 238);
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            $uf[] = 0 + $notin["fid"];
        }
        $unsearchforums = implode(",", $uf);
    }
    $query = sql_query("SELECT fid,password FROM " . TSF_PREFIX . "forums WHERE password != ''");
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            if (!isset($_COOKIE["forumpass_" . $notin["fid"]])) {
                $_COOKIE["forumpass_" . $notin["fid"]] = "";
            }
            if ($_COOKIE["forumpass_" . $notin["fid"]] != md5($CURUSER["id"] . $notin["password"] . $securehash)) {
                $uf2[] = 0 + $notin["fid"];
            }
        }
        if (0 < count($uf2)) {
            if (isset($unsearchforums)) {
                $unsearchforums .= "," . implode(",", $uf2);
            } else {
                $unsearchforums = implode(",", $uf2);
            }
        }
    }
    if (isset($unsearchforums)) {
        $where_sql .= " AND t.fid NOT IN (" . $unsearchforums . ")";
    }
    $sid = md5(uniqid(microtime(), 1));
    $searcharray = ["sid" => $sid, "uid" => intval($CURUSER["id"]), "dateline" => TIMENOW, "ipaddress" => $CURUSER["ip"], "threads" => "", "posts" => "", "searchtype" => "titles", "resulttype" => "threads", "querycache" => $where_sql];
    sql_query("INSERT INTO " . TSF_PREFIX . "searchlog (sid,uid,dateline,ipaddress,threads,posts,searchtype,resulttype,querycache) VALUES (" . sqlesc($searcharray["sid"]) . "," . sqlesc($searcharray["uid"]) . "," . sqlesc($searcharray["dateline"]) . "," . sqlesc($searcharray["ipaddress"]) . "," . sqlesc($searcharray["threads"]) . "," . sqlesc($searcharray["posts"]) . "," . sqlesc($searcharray["searchtype"]) . "," . sqlesc($searcharray["resulttype"]) . "," . sqlesc($searcharray["querycache"]) . ")") || sqlerr(__FILE__, 290);
    redirect("tsf_forums/tsf_search.php?$action = show_search_results&$searchid = " . $sid, $lang->tsf_forums["searchresults"]);
    exit;
}
if ($action == "searchinthread") {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    $threadid = isset($_POST["threadid"]) ? intval($_POST["threadid"]) : (isset($_GET["threadid"]) ? intval($_GET["threadid"]) : 0);
    if (!is_valid_id($threadid)) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    ($query = sql_query("SELECT\r\n\t\tt.tid, f.type, f.fid as currentforumid, ff.fid as deepforumid\r\n\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\tWHERE t.$tid = " . sqlesc($threadid) . " LIMIT 1")) || sqlerr(__FILE__, 313);
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes")) {
        print_no_permission(true);
        exit;
    }
    CheckSearchFlood();
    $resulttype = "posts";
    $search_data = ["keywords" => $_POST["keywords"], "author" => $_POST["author"], "matchusername" => $_POST["matchusername"], "postthread" => 1, "forums" => "skip", "threadid" => $threadid];
    $search_results = perform_search_mysql_ft($search_data);
    $sid = md5(uniqid(microtime(), 1));
    $searcharray = ["sid" => $sid, "uid" => intval($CURUSER["id"]), "dateline" => TIMENOW, "ipaddress" => $CURUSER["ip"], "threads" => $search_results["threads"], "posts" => $search_results["posts"], "searchtype" => $search_results["searchtype"], "resulttype" => $resulttype, "querycache" => $search_results["querycache"]];
    sql_query("INSERT INTO " . TSF_PREFIX . "searchlog (sid,uid,dateline,ipaddress,threads,posts,searchtype,resulttype,querycache) VALUES (" . sqlesc($searcharray["sid"]) . "," . sqlesc($searcharray["uid"]) . "," . sqlesc($searcharray["dateline"]) . "," . sqlesc($searcharray["ipaddress"]) . "," . sqlesc($searcharray["threads"]) . "," . sqlesc($searcharray["posts"]) . "," . sqlesc($searcharray["searchtype"]) . "," . sqlesc($searcharray["resulttype"]) . "," . sqlesc($searcharray["querycache"]) . ")") || sqlerr(__FILE__, 357);
    redirect("tsf_forums/tsf_search.php?$action = show_search_results&$searchid = " . $sid, $lang->tsf_forums["searchresults"]);
    exit;
}
if ($action == "finduserposts") {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    if (empty($_GET["id"]) || !is_valid_id($_GET["id"])) {
        print_no_permission(true);
    }
    $where_sql = "p.$uid = '" . intval($_GET["id"]) . "'";
    ($query = sql_query("SELECT fp.fid,f.fid FROM " . TSF_PREFIX . "forumpermissions fp LEFT JOIN " . TSF_PREFIX . "forums f ON (fp.$fid = f.pid) WHERE (fp.$canview = 'no' OR fp.$cansearch = 'no') AND fp.$gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 374);
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            $uf[] = 0 + $notin["fid"];
        }
        $unsearchforums = implode(",", $uf);
    }
    $query = sql_query("SELECT fid,password FROM " . TSF_PREFIX . "forums WHERE password != ''");
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            if (!isset($_COOKIE["forumpass_" . $notin["fid"]])) {
                $_COOKIE["forumpass_" . $notin["fid"]] = "";
            }
            if ($_COOKIE["forumpass_" . $notin["fid"]] != md5($CURUSER["id"] . $notin["password"] . $securehash)) {
                $uf2[] = 0 + $notin["fid"];
            }
        }
        if (0 < count($uf2)) {
            if (isset($unsearchforums)) {
                $unsearchforums .= "," . implode(",", $uf2);
            } else {
                $unsearchforums = implode(",", $uf2);
            }
        }
    }
    if (isset($unsearchforums)) {
        $where_sql .= " AND p.fid NOT IN (" . $unsearchforums . ")";
    }
    $sid = md5(uniqid(microtime(), 1));
    $searcharray = ["sid" => $sid, "uid" => intval($CURUSER["id"]), "dateline" => TIMENOW, "ipaddress" => $CURUSER["ip"], "threads" => "", "posts" => "", "searchtype" => "titles", "resulttype" => "posts", "querycache" => $where_sql];
    sql_query("INSERT INTO " . TSF_PREFIX . "searchlog (sid,uid,dateline,ipaddress,threads,posts,searchtype,resulttype,querycache) VALUES (" . sqlesc($searcharray["sid"]) . "," . sqlesc($searcharray["uid"]) . "," . sqlesc($searcharray["dateline"]) . "," . sqlesc($searcharray["ipaddress"]) . "," . sqlesc($searcharray["threads"]) . "," . sqlesc($searcharray["posts"]) . "," . sqlesc($searcharray["searchtype"]) . "," . sqlesc($searcharray["resulttype"]) . "," . sqlesc($searcharray["querycache"]) . ")") || sqlerr(__FILE__, 426);
    redirect("tsf_forums/tsf_search.php?$action = show_search_results&$searchid = " . $sid, $lang->tsf_forums["searchresults"]);
    exit;
}
if ($action == "getnew") {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    $where_sql = "t.lastpost >= '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $CURUSER["last_forum_visit"]) . "'";
    ($query = sql_query("SELECT fp.fid,f.fid FROM " . TSF_PREFIX . "forumpermissions fp LEFT JOIN " . TSF_PREFIX . "forums f ON (fp.$fid = f.pid) WHERE (fp.$canview = 'no' OR fp.$cansearch = 'no') AND fp.$gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 440);
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            $uf[] = 0 + $notin["fid"];
        }
        $unsearchforums = implode(",", $uf);
    }
    $query = sql_query("SELECT fid,password FROM " . TSF_PREFIX . "forums WHERE password != ''");
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            if (!isset($_COOKIE["forumpass_" . $notin["fid"]])) {
                $_COOKIE["forumpass_" . $notin["fid"]] = "";
            }
            if ($_COOKIE["forumpass_" . $notin["fid"]] != md5($CURUSER["id"] . $notin["password"] . $securehash)) {
                $uf2[] = 0 + $notin["fid"];
            }
        }
        if (0 < count($uf2)) {
            if (isset($unsearchforums) && $unsearchforums) {
                $unsearchforums .= "," . implode(",", $uf2);
            } else {
                $unsearchforums = implode(",", $uf2);
            }
        }
    }
    if (isset($unsearchforums)) {
        $where_sql .= " AND t.fid NOT IN (" . $unsearchforums . ")";
    }
    $sid = md5(uniqid(microtime(), 1));
    $searcharray = ["sid" => $sid, "uid" => intval($CURUSER["id"]), "dateline" => TIMENOW, "ipaddress" => $CURUSER["ip"], "threads" => "", "posts" => "", "searchtype" => "titles", "resulttype" => "threads", "querycache" => $where_sql];
    sql_query("INSERT INTO " . TSF_PREFIX . "searchlog (sid,uid,dateline,ipaddress,threads,posts,searchtype,resulttype,querycache) VALUES (" . sqlesc($searcharray["sid"]) . "," . sqlesc($searcharray["uid"]) . "," . sqlesc($searcharray["dateline"]) . "," . sqlesc($searcharray["ipaddress"]) . "," . sqlesc($searcharray["threads"]) . "," . sqlesc($searcharray["posts"]) . "," . sqlesc($searcharray["searchtype"]) . "," . sqlesc($searcharray["resulttype"]) . "," . sqlesc($searcharray["querycache"]) . ")") || sqlerr(__FILE__, 492);
    redirect("tsf_forums/tsf_search.php?$action = show_search_results&$searchid = " . $sid, $lang->tsf_forums["searchresults"]);
    exit;
}
if ($action == "daily") {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    if (!isset($_GET["days"]) || isset($_GET["days"]) && $_GET["days"] < 1) {
        $days = 1;
    } else {
        $days = intval($_GET["days"]);
    }
    $datecut = TIMENOW - 86400 * $days;
    $where_sql = "t.lastpost >='" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $datecut) . "'";
    ($query = sql_query("SELECT fp.fid,f.fid FROM " . TSF_PREFIX . "forumpermissions fp LEFT JOIN " . TSF_PREFIX . "forums f ON (fp.$fid = f.pid) WHERE (fp.$canview = 'no' OR fp.$cansearch = 'no') AND fp.$gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 516);
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            $uf[] = 0 + $notin["fid"];
        }
        $unsearchforums = implode(",", $uf);
    }
    $query = sql_query("SELECT fid,password FROM " . TSF_PREFIX . "forums WHERE password != ''");
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            if (!isset($_COOKIE["forumpass_" . $notin["fid"]])) {
                $_COOKIE["forumpass_" . $notin["fid"]] = "";
            }
            if ($_COOKIE["forumpass_" . $notin["fid"]] != md5($CURUSER["id"] . $notin["password"] . $securehash)) {
                $uf2[] = 0 + $notin["fid"];
            }
        }
        if (0 < count($uf2)) {
            if (isset($unsearchforums)) {
                $unsearchforums .= "," . implode(",", $uf2);
            } else {
                $unsearchforums = implode(",", $uf2);
            }
        }
    }
    if (isset($unsearchforums)) {
        $where_sql .= " AND t.fid NOT IN (" . $unsearchforums . ")";
    }
    $sid = md5(uniqid(microtime(), 1));
    $searcharray = ["sid" => $sid, "uid" => intval($CURUSER["id"]), "dateline" => TIMENOW, "ipaddress" => $CURUSER["ip"], "threads" => "", "posts" => "", "searchtype" => "titles", "resulttype" => "threads", "querycache" => $where_sql];
    sql_query("INSERT INTO " . TSF_PREFIX . "searchlog (sid,uid,dateline,ipaddress,threads,posts,searchtype,resulttype,querycache) VALUES (" . sqlesc($searcharray["sid"]) . "," . sqlesc($searcharray["uid"]) . "," . sqlesc($searcharray["dateline"]) . "," . sqlesc($searcharray["ipaddress"]) . "," . sqlesc($searcharray["threads"]) . "," . sqlesc($searcharray["posts"]) . "," . sqlesc($searcharray["searchtype"]) . "," . sqlesc($searcharray["resulttype"]) . "," . sqlesc($searcharray["querycache"]) . ")") || sqlerr(__FILE__, 568);
    redirect("tsf_forums/tsf_search.php?$action = show_search_results&$searchid = " . $sid, $lang->tsf_forums["searchresults"]);
    exit;
}
if ($action == "do_search") {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    CheckSearchFlood();
    $resulttype = "threads";
    $search_data = ["keywords" => isset($_POST["keywords"]) ? $_POST["keywords"] : "", "author" => isset($_POST["author"]) ? $_POST["author"] : "", "postthread" => isset($_POST["postthread"]) ? $_POST["postthread"] : "", "matchusername" => isset($_POST["matchusername"]) ? $_POST["matchusername"] : "", "forums" => isset($_POST["forums"]) ? $_POST["forums"] : ""];
    $search_results = perform_search_mysql_ft($search_data);
    $sid = md5(uniqid(microtime(), 1));
    $searcharray = ["sid" => $sid, "uid" => intval($CURUSER["id"]), "dateline" => TIMENOW, "ipaddress" => $CURUSER["ip"], "threads" => $search_results["threads"], "posts" => $search_results["posts"], "searchtype" => $search_results["searchtype"], "resulttype" => $resulttype, "querycache" => $search_results["querycache"]];
    sql_query("INSERT INTO " . TSF_PREFIX . "searchlog (sid,uid,dateline,ipaddress,threads,posts,searchtype,resulttype,querycache) VALUES (" . sqlesc($searcharray["sid"]) . "," . sqlesc($searcharray["uid"]) . "," . sqlesc($searcharray["dateline"]) . "," . sqlesc($searcharray["ipaddress"]) . "," . sqlesc($searcharray["threads"]) . "," . sqlesc($searcharray["posts"]) . "," . sqlesc($searcharray["searchtype"]) . "," . sqlesc($searcharray["resulttype"]) . "," . sqlesc($searcharray["querycache"]) . ")") || sqlerr(__FILE__, 607);
    redirect("tsf_forums/tsf_search.php?$action = show_search_results&$searchid = " . $sid, $lang->tsf_forums["searchresults"]);
    exit;
}
if ($action == "show_search_results") {
    if ($CURUSER["id"] == 0) {
        print_no_permission();
    }
    require_once INC_PATH . "/functions_cookies.php";
    $sid = isset($_GET["searchid"]) ? $_GET["searchid"] : "";
    if (empty($sid)) {
        add_breadcrumb($lang->tsf_forums["search_results"]);
        stdhead($lang->tsf_forums["search_results"]);
        build_breadcrumb();
        stdmsg($lang->global["error"], $lang->tsf_forums["searcherror4"], false);
        stdfoot();
        exit;
    }
    ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "searchlog WHERE $sid = " . sqlesc($sid))) || sqlerr(__FILE__, 629);
    $search = mysqli_fetch_assoc($query);
    if (!$search["sid"] || $search["uid"] != $CURUSER["id"] && !$moderator) {
        add_breadcrumb($lang->tsf_forums["search_results"]);
        stdhead($lang->tsf_forums["search_results"]);
        build_breadcrumb();
        stdmsg($lang->global["error"], $lang->tsf_forums["searcherror4"], false);
        stdfoot();
        exit;
    }
    $str = "\r\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" $style = \"clear: both;\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\" $colspan = \"7\">\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t<strong>" . $lang->tsf_forums["search_results"] . "</strong>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"1%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["status"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"40%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["thread"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"25%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["forum"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"10%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["author"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"1%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["replies"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"1%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["views"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"15%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["lastpost"] . "</strong></span></td>\r\n\t\t\t</tr>\r\n\t\t";
    if ($search["resulttype"] == "threads") {
        $sortfield = "t.lastpost";
    } else {
        $sortfield = "p.dateline";
    }
    $order = "desc";
    $threads = [];
    if ($search["resulttype"] == "threads") {
        $threadcount = 0;
        if ($search["querycache"] != "") {
            $where_conditions = $search["querycache"];
            $query = sql_query("SELECT t.tid FROM " . TSF_PREFIX . "threads t WHERE " . $where_conditions) or ($query = sql_query("SELECT t.tid FROM " . TSF_PREFIX . "threads t WHERE " . $where_conditions)) || sqlerr(__FILE__, 683);
            while ($thread = mysqli_fetch_assoc($query)) {
                $threads[$thread["tid"]] = $thread["tid"];
                $threadcount++;
            }
            if (0 < $threadcount) {
                $search["threads"] = implode(",", $threads);
                $where_conditions = "t.tid IN (" . $search["threads"] . ")";
            } else {
                add_breadcrumb($lang->tsf_forums["search_results"]);
                stdhead($lang->tsf_forums["search_results"]);
                build_breadcrumb();
                stdmsg($lang->global["error"], $lang->tsf_forums["searcherror"], false);
                stdfoot();
                exit;
            }
        } else {
            $where_conditions = "t.tid IN (" . $search["threads"] . ")";
            ($query = sql_query("SELECT COUNT(t.tid) AS resultcount FROM " . TSF_PREFIX . "threads t WHERE " . $where_conditions)) || sqlerr(__FILE__, 712);
            $count = mysqli_fetch_assoc($query);
            if (!$count["resultcount"]) {
                add_breadcrumb($lang->tsf_forums["search_results"]);
                stdhead($lang->tsf_forums["search_results"]);
                build_breadcrumb();
                stdmsg($lang->global["error"], $lang->tsf_forums["searcherror"], false);
                stdfoot();
                exit;
            }
            $threadcount = $count["resultcount"];
        }
        $sorturl = $_SERVER["SCRIPT_NAME"] . "?$action = show_search_results&amp;$searchid = " . htmlspecialchars_uni($sid);
        sanitize_pageresults($threadcount, $pagenumber, $perpage, 200);
        $multipage = construct_page_nav($pagenumber, $perpage, $threadcount, $sorturl);
        $limitlower = ($pagenumber - 1) * $perpage;
        $limitupper = $pagenumber * $perpage;
        if ($threadcount < $limitupper) {
            $limitupper = $threadcount;
            if ($threadcount < $limitlower) {
                $limitlower = $threadcount - $perpage - 1;
            }
        }
        if ($limitlower < 0) {
            $limitlower = 0;
        }
        ($query = sql_query("\r\n\t\t\tSELECT t.*, f.name as currentforum, f.pid as parent, ff.name as realforum, ff.fid as realforumid, u.username as reallastposterusername, u.id as reallastposteruid, g.namestyle as lastposternamestyle, uu.username as threadstarter, uu.id as threadstarteruid, gg.namestyle as threadstarternamestyle\r\n\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tLEFT JOIN users u ON (t.$lastposteruid = u.id)\r\n\t\t\tLEFT JOIN usergroups g ON (u.$usergroup = g.gid)\r\n\t\t\tLEFT JOIN users uu ON (t.$uid = uu.id)\r\n\t\t\tLEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid)\r\n\t\t\tWHERE " . $where_conditions . "\r\n\t\t\tORDER BY " . $sortfield . " " . $order . "\r\n\t\t\tLIMIT " . $limitlower . ", " . $perpage . "\r\n\t\t")) || sqlerr(__FILE__, 760);
        $thread_cache = [];
        while ($thread = mysqli_fetch_assoc($query)) {
            $thread_cache[$thread["tid"]] = $thread;
        }
        $thread_ids = implode(",", array_keys($thread_cache));
        if (is_array($thread_ids) && 0 < count($thread_ids)) {
            $userinfo = [];
            $query = sql_query("\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tSELECT tid, uid\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "posts\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tWHERE tid IN (0, " . $thread_ids . ")\r\n\t\t\t\t\t\t\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tSELECT tid, uid\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "posts\r\n\t\t\t\t\t\t\t\t\t\t\t\t\tWHERE tid IN (0, " . $thread_ids . ")\r\n\t\t\t\t\t\t\t\t\t\t\t\t")) || sqlerr(__FILE__, 777);
            while ($userposts = mysqli_fetch_assoc($query)) {
                if (isset($userinfo[$userposts["tid"]][$userposts["uid"]])) {
                    $userinfo[$userposts["tid"]][$userposts["uid"]]++;
                } else {
                    $userinfo[$userposts["tid"]][$userposts["uid"]] = 1;
                }
            }
        }
        $query = sql_query("SELECT tid,dateline FROM " . TSF_PREFIX . "threadsread WHERE $uid = " . sqlesc($CURUSER["id"]) . " AND tid IN(" . $thread_ids . ")") or ($query = sql_query("SELECT tid,dateline FROM " . TSF_PREFIX . "threadsread WHERE $uid = " . sqlesc($CURUSER["id"]) . " AND tid IN(" . $thread_ids . ")")) || sqlerr(__FILE__, 792);
        while ($readthread = mysqli_fetch_assoc($query)) {
            $thread_cache[$readthread["tid"]]["lastread"] = $readthread["dateline"];
        }
        foreach ($thread_cache as $thread) {
            $lastread = 0;
            $forumread = ts_get_array_cookie("forumread", $thread["fid"]);
            if ($forumread < $thread["lastpost"]) {
                $cutoff = TIMENOW - 604800;
                if ($cutoff < $thread["lastpost"]) {
                    if (isset($thread["lastread"])) {
                        $lastread = $thread["lastread"];
                    } else {
                        $lastread = 1;
                    }
                }
            }
            if (!$lastread) {
                $readcookie = $threadread = ts_get_array_cookie("threadread", $thread["tid"]);
                if ($forumread < $readcookie) {
                    $lastread = $readcookie;
                } else {
                    $lastread = $forumread;
                }
            }
            if ($lastread < $thread["lastpost"] && $lastread) {
                if (isset($userinfo[$thread["tid"]][$CURUSER["id"]])) {
                    $images = show_forum_images("don");
                } else {
                    $images = show_forum_images("on");
                }
                $unreadpost = 1;
            } else {
                if ($thread["closed"] == "yes") {
                    if (isset($userinfo[$thread["tid"]][$CURUSER["id"]])) {
                        $images = show_forum_images("dofflock");
                    } else {
                        $images = show_forum_images("offlock");
                    }
                } else {
                    if (isset($userinfo[$thread["tid"]][$CURUSER["id"]])) {
                        $images = show_forum_images("doff");
                    } else {
                        $images = show_forum_images("off");
                    }
                }
            }
            $subject = htmlspecialchars_uni(ts_remove_badwords($thread["subject"]));
            $lastpost_data = "";
            $lastpost_data = ["lastpost" => $thread["lastpost"], "lastposter" => get_user_color(htmlspecialchars_uni($thread["reallastposterusername"]), $thread["lastposternamestyle"]), "lastposteruid" => $thread["reallastposteruid"]];
            if ($thread["sticky"] == 1) {
                $class = "sticky";
                $desc = $lang->tsf_forums["stickythread"];
            } else {
                $class = "";
                $desc = "";
            }
            if ($lastpost_data["lastpost"] == 0 || $lastpost_data["lastposter"] == "") {
                $lastpost = "<td class=\"" . $class . "\" $style = \"white-space: nowrap;\"><span $style = \"text-align: center;\">" . $lang->tsf_forums["lastpost_never"] . "</span></td>";
            } else {
                $lastpost_date = my_datee($dateformat, $thread["lastpost"]);
                $lastpost_time = my_datee($timeformat, $thread["lastpost"]);
                $lastpost_profilelink = build_profile_link($lastpost_data["lastposter"], $lastpost_data["lastposteruid"]);
                $lastpost = "\r\n\t\t\t\t<td class=\"" . $class . "\" $style = \"white-space: nowrap;\">\r\n\t\t\t\t\t<span class=\"smalltext\">" . $lastpost_date . " " . $lastpost_time . "<br />\r\n\t\t\t\t\t" . $lang->tsf_forums["by"] . " " . $lastpost_profilelink . "</span> <a $href = \"" . tsf_seo_clean_text($subject, "lastpost", $thread["tid"]) . "\" $alt = \"\" $title = \"\"><img $src = \"" . $BASEURL . "/tsf_forums/images/lastpost.gif\" class=\"inlineimg\" $border = \"0\" $alt = \"" . $lang->tsf_forums["gotolastpost"] . "\" $title = \"" . $lang->tsf_forums["gotolastpost"] . "\"></a>\r\n\t\t\t\t</td>";
            }
            if ($thread["threadstarter"]) {
                $author = get_user_color(htmlspecialchars_uni($thread["threadstarter"]), $thread["threadstarternamestyle"]);
            } else {
                $author = $lang->tsf_forums["guest"];
            }
            $replies = ts_nf($thread["replies"]);
            $views = ts_nf($thread["views"]);
            $thread["pages"] = 0;
            $thread["multipage"] = "";
            $threadpages = "";
            $morelink = "";
            $thread["posts"] = $thread["replies"] + 1;
            if ($postperpage < $thread["posts"]) {
                $thread["pages"] = $thread["posts"] / $postperpage;
                $thread["pages"] = @ceil($thread["pages"]);
                if (4 < $thread["pages"]) {
                    $pagesstop = 4;
                    $morelink = "... <a $href = \"" . tsf_seo_clean_text($subject, "lastpost", $thread["tid"]) . "\">" . $lang->global["last"] . "</a>";
                } else {
                    $pagesstop = $thread["pages"];
                }
                for ($i = 1; $i <= $pagesstop; $i++) {
                    $threadpages .= " <a $href = \"" . tsf_seo_clean_text($subject, "page", $thread["tid"], $i) . "\">" . $i . "</a> ";
                }
                $thread["multipage"] = "<br /><span class=\"smalltext\">(<img $src = \"" . $BASEURL . "/tsf_forums/images/multipage.gif\" $border = \"0\" $alt = \"" . $lang->tsf_forums["multithread"] . "\" $title = \"" . $lang->tsf_forums["multithread"] . "\" class=\"inlineimg\" /> " . $lang->tsf_forums["pages"] . " " . $threadpages . $morelink . ")</span>";
            } else {
                $threadpages = "";
                $morelink = "";
                $thread["multipage"] = "";
            }
            $desc = $stickyimg = $ratingimage = $threadtags = $pollimage = "";
            if ($thread["sticky"] == 1) {
                $stickyimg = "<img $src = \"" . $BASEURL . "/tsf_forums/images/sticky.png\" class=\"inlineimg\" $border = \"0\" $alt = \"" . $lang->tsf_forums["stickythread"] . "\" $title = \"" . $lang->tsf_forums["stickythread"] . "\" />";
                $desc = $lang->tsf_forums["sticky"];
            }
            if ($thread["votenum"]) {
                $thread["voteavg"] = number_format($thread["votetotal"] / $thread["votenum"], 2);
                $thread["rating"] = round($thread["votetotal"] / $thread["votenum"]);
                $ratingimgalt = sprintf($lang->tsf_forums["tratingimgalt"], $thread["votenum"], $thread["voteavg"]);
                $ratingimage = "<img class=\"inlineimg\" $src = \"" . $BASEURL . "/tsf_forums/images/rating/rating_" . $thread["rating"] . ".gif\" $alt = \"" . $ratingimgalt . "\" $title = \"" . $ratingimgalt . "\" $border = \"0\" />";
            }
            if ($thread["pollid"]) {
                $pollimgalt = $lang->tsf_forums["poll17"];
                $pollimage = "<img class=\"inlineimg\" $src = \"" . $BASEURL . "/tsf_forums/images/poll.png\" $alt = \"" . $pollimgalt . "\" $title = \"" . $pollimgalt . "\" $border = \"0\" />";
                $desc = "<strong>" . $lang->tsf_forums["poll17"] . ":</strong> ";
            }
            if ($stickyimg || $ratingimage || $pollimage) {
                $threadtags = "<span $style = \"float: right;\">" . $stickyimg . " " . $pollimage . ($ratingimage ? "<br />" . $ratingimage : "") . "</span>";
            }
            $str .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"center\">" . $images . "</td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $align = \"left\">" . $threadtags . $desc . "<a $href = \"" . tsf_seo_clean_text($subject, "t", $thread["tid"]) . "\">" . ($thread["visible"] == 1 ? $subject : "<span class=\"highlight\">" . $lang->tsf_forums["moderatemsg8"] . "</span>") . "</a>" . $thread["multipage"] . "</td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $align = \"left\"><a $href = \"" . tsf_seo_clean_text($thread["currentforum"], "fd", $thread["fid"]) . "\">" . $thread["currentforum"] . "</a></td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\"><a $href = \"" . tsf_seo_clean_text($author, "u", $thread["threadstarteruid"]) . "\">" . $author . "</a></td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\">" . $replies . "</td>\r\n\t\t\t\t\t<td class=\"" . $class . "\" $align = \"center\">" . $views . "</td>\r\n\t\t\t\t\t" . $lastpost . "\r\n\t\t\t\t</tr>";
            if (!isset($unreadpost) && !isset($page)) {
                require_once INC_PATH . "/functions_cookies.php";
                ts_set_array_cookie("forumread", $thread["fid"], TIMENOW);
            }
        }
    } else {
        $postcount = 0;
        if ($search["querycache"] != "") {
            $where_conditions = $search["querycache"];
            $query = sql_query("SELECT p.pid FROM " . TSF_PREFIX . "posts p WHERE " . $where_conditions) or ($query = sql_query("SELECT p.pid FROM " . TSF_PREFIX . "posts p WHERE " . $where_conditions)) || sqlerr(__FILE__, 1000);
            while ($post = mysqli_fetch_assoc($query)) {
                $posts[$post["pid"]] = $post["pid"];
                $postcount++;
            }
            if (0 < $postcount) {
                $search["posts"] = implode(",", $posts);
                $where_conditions = "p.pid IN (" . $search["posts"] . ")";
            } else {
                add_breadcrumb($lang->tsf_forums["search_results"]);
                stdhead($lang->tsf_forums["search_results"]);
                build_breadcrumb();
                stdmsg($lang->global["error"], $lang->tsf_forums["searcherror"], false);
                stdfoot();
                exit;
            }
        } else {
            $where_conditions = "p.tid IN (" . $search["threads"] . ")";
            if ($search["posts"] != "") {
                $where_conditions .= " AND p.pid IN (" . $search["posts"] . ")";
            }
            ($query = sql_query("SELECT COUNT(p.tid) AS resultcount FROM " . TSF_PREFIX . "posts p WHERE " . $where_conditions)) || sqlerr(__FILE__, 1032);
            $count = mysqli_fetch_assoc($query);
            if (!$count["resultcount"]) {
                add_breadcrumb($lang->tsf_forums["search_results"]);
                stdhead($lang->tsf_forums["search_results"]);
                build_breadcrumb();
                stdmsg($lang->global["error"], $lang->tsf_forums["searcherror"], false);
                stdfoot();
                exit;
            }
            $postcount = $count["resultcount"];
        }
        $sorturl = $_SERVER["SCRIPT_NAME"] . "?$action = show_search_results&amp;$searchid = " . htmlspecialchars_uni($sid);
        sanitize_pageresults($postcount, $pagenumber, $perpage, 200);
        $multipage = construct_page_nav($pagenumber, $perpage, $postcount, $sorturl);
        $limitlower = ($pagenumber - 1) * $perpage;
        $limitupper = $pagenumber * $perpage;
        if ($postcount < $limitupper) {
            $limitupper = $postcount;
            if ($postcount < $limitlower) {
                $limitlower = $postcount - $perpage - 1;
            }
        }
        if ($limitlower < 0) {
            $limitlower = 0;
        }
        ($query = sql_query("\r\n\t\t\t\t\tSELECT\r\n\t\t\t\t\tp.pid, p.tid, p.fid, p.subject, p.visible, u.id as uid, u.username, p.dateline, p.message, f.name, t.subject as threadsubject,\r\n\t\t\t\t\tg.namestyle\r\n\t\t\t\t\tFROM\r\n\t\t\t\t\t" . TSF_PREFIX . "posts p\r\n\t\t\t\t\tLEFT JOIN users u ON (p.$uid = u.id)\r\n\t\t\t\t\tLEFT JOIN usergroups g ON (u.$usergroup = g.gid)\r\n\t\t\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.$tid = t.tid)\r\n\t\t\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (p.$fid = f.fid)\r\n\t\t\t\t\tWHERE " . $where_conditions . "\r\n\t\t\t\t\tORDER BY " . $sortfield . " " . $order . "\r\n\t\t\t\t\tLIMIT " . $limitlower . ", " . $perpage)) || sqlerr(__FILE__, 1079);
        $str = "\r\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" $style = \"clear: both;\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\" $colspan = \"4\">\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t<strong>" . $lang->tsf_forums["search_results"] . "</strong>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"50%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["post"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"10%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["author"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"25%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["forum"] . "</strong></span></td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"15%\"><span class=\"smalltext\"><strong>" . $lang->tsf_forums["posted"] . "</strong></span></td>\r\n\t\t\t</tr>\r\n\t\t";
        while ($post = mysqli_fetch_assoc($query)) {
            $Query = sql_query("SELECT pid FROM " . TSF_PREFIX . "posts WHERE $tid = " . $post["tid"] . " AND pid <= " . $post["pid"]);
            $Count = mysqli_num_rows($Query);
            if ($Count <= $postperpage) {
                $P = 0;
                while (preg_match("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", $post["message"])) {
                }
                $str .= "\r\n\t\t\t<tr>\r\n\t\t\t<td $align = \"left\">\r\n\t\t\t\t" . ($post["visible"] == 1 ? $lang->tsf_forums["thread"] . ": <a $href = \"" . tsf_seo_clean_text($post["threadsubject"], "t", $post["tid"]) . "\"><b>" . htmlspecialchars_uni($post["threadsubject"]) . "</b></a><br />" . $lang->tsf_forums["post"] . " <a $href = \"" . tsf_seo_clean_text($post["subject"], "t", $post["tid"], "pid=" . $post["pid"] . "&$nolastpage = true&$page = " . $P . "&$scrollto = pid" . $post["pid"]) . "\"><b>" . htmlspecialchars_uni($post["subject"]) . "</b></a><br />" . htmlspecialchars_uni($post["message"]) : "<span class=\"highlight\"><a $href = \"" . tsf_seo_clean_text($post["subject"], "t", $post["tid"], "pid=" . $post["pid"] . "&$nolastpage = true&$page = " . $P . "&$scrollto = pid" . $post["pid"]) . "\">" . $lang->tsf_forums["moderatemsg7"] . "</a></span>") . "</td>\r\n\t\t\t<td $align = \"center\" $valign = \"top\"><a $href = \"" . tsf_seo_clean_text($post["username"], "u", $post["uid"]) . "\">" . get_user_color($post["username"], $post["namestyle"]) . "</a></td>\r\n\t\t\t<td $align = \"left\" $valign = \"top\"><a $href = \"" . tsf_seo_clean_text($post["name"], "fd", $post["fid"]) . "\"><b>" . $post["name"] . "</b></a></td>\r\n\t\t\t<td $align = \"center\" $valign = \"top\">" . my_datee($dateformat, $post["dateline"]) . " " . my_datee($timeformat, $post["dateline"]) . "</td>\r\n\t\t\t</tr>";
            } else {
                $P = ceil($Count / $postperpage);
            }
            $post["message"] = preg_replace("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", "", $post["message"]);
        }
    }
    add_breadcrumb($lang->tsf_forums["search_results"]);
    stdhead($lang->tsf_forums["search_results"]);
    build_breadcrumb();
    $str .= "</table>";
    $ptr = "\r\n\t<!-- start: forumdisplay_newthread -->\r\n\t\t<table $width = \"100%\" $border = \"0\" class=\"none\" $style = \"clear: both;\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"none\" $width = \"82%\" $style = \"padding: 0px 0px 5px 0px;\">\r\n\t\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t\t" . $multipage . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t<!-- end: forumdisplay_newthread -->";
    $str .= "\r\n\t<!-- start: forumdisplay_newthread -->\r\n\t\t\t<table $width = \"100%\" $border = \"0\" class=\"none\" $style = \"clear: both;\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"none\" $width = \"82%\">\r\n\t\t\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t\t\t" . $multipage . "\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t<!-- end: forumdisplay_newthread -->\t";
    echo $ptr . $str;
    stdfoot();
    exit;
}
function perform_search_mysql_ft($search)
{
    global $lang;
    global $CURUSER;
    global $usergroups;
    global $securehash;
    global $SITENAME;
    require INC_PATH . "/function_search_clean.php";
    $keywords = clean_keywords_ft($search["keywords"]);
    if (!$keywords && !$search["author"]) {
        add_breadcrumb($lang->tsf_forums["search_results"]);
        stdhead($lang->tsf_forums["search_results"]);
        build_breadcrumb();
        stdmsg($lang->global["error"], $lang->tsf_forums["searcherror2"], false);
        stdfoot();
        exit;
    }
    $query = sql_query("SHOW VARIABLES LIKE 'ft_min_word_len';");
    $array = mysqli_fetch_assoc($query);
    $min_length = $array["Value"];
    if (is_numeric($min_length)) {
        $minsearchword = $min_length;
    } else {
        $minsearchword = 3;
    }
    $subject_lookin = "";
    $message_lookin = "";
    if ($keywords) {
        $keywords_exp = explode("\"", $keywords);
        $inquote = false;
        foreach ($keywords_exp as $phrase) {
            if (!$inquote) {
                $split_words = preg_split("#\\s{1,}#", $phrase, -1);
                foreach ($split_words as $word) {
                    $word = str_replace(["+", "-", "*"], "", $word);
                    if ($word) {
                        if (strlen($word) < $minsearchword) {
                            $lang->$error_minsearchlength = sprintf($lang->tsf_forums["searcherror3"], $minsearchword);
                            add_breadcrumb($lang->tsf_forums["search_results"]);
                            stdhead($lang->tsf_forums["search_results"]);
                            build_breadcrumb();
                            stdmsg($lang->global["error"], $lang->error_minsearchlength, false);
                            stdfoot();
                            exit;
                        }
                    }
                }
            } else {
                $phrase = str_replace(["+", "-", "*"], "", $phrase);
                if (strlen($phrase) < $minsearchword) {
                    $lang->$error_minsearchlength = sprintf($lang->tsf_forums["searcherror3"], $minsearchword);
                    add_breadcrumb($lang->tsf_forums["search_results"]);
                    stdhead($lang->tsf_forums["search_results"]);
                    build_breadcrumb();
                    stdmsg($lang->global["error"], $lang->error_minsearchlength, false);
                    stdfoot();
                    exit;
                }
            }
            $inquote = !$inquote;
        }
        $message_lookin = "AND MATCH(message) AGAINST('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "' IN BOOLEAN MODE)";
        $subject_lookin = "AND MATCH(subject) AGAINST('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $keywords) . "' IN BOOLEAN MODE)";
    }
    $post_usersql = $thread_usersql = "";
    if ($search["author"]) {
        $userids = [];
        if ($search["matchusername"]) {
            $query = sql_query("SELECT id FROM users WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $search["author"]) . "'") or ($query = sql_query("SELECT id FROM users WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $search["author"]) . "'")) || sqlerr(__FILE__, 1247);
            while ($user = mysqli_fetch_assoc($query)) {
            }
            if (count($userids) < 1) {
                add_breadcrumb($lang->tsf_forums["search_results"]);
                stdhead($lang->tsf_forums["search_results"]);
                build_breadcrumb();
                stdmsg($lang->global["error"], $lang->tsf_forums["searcherror"], false);
                stdfoot();
                exit;
            }
            $userids = implode(",", $userids);
            $post_usersql = " AND p.uid IN (" . $userids . ")";
            $thread_usersql = " AND t.uid IN (" . $userids . ")";
        } else {
            $search["author"] = strtolower($search["author"]);
            $query = sql_query("SELECT id FROM users WHERE LOWER(username) LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $search["author"]) . "%'") or ($query = sql_query("SELECT id FROM users WHERE LOWER(username) LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $search["author"]) . "%'")) || sqlerr(__FILE__, 1252);
        }
        $userids[] = $user["id"];
    }
    $forumin = [];
    $fidlist = [];
    $searchin = [];
    if ($search["forums"] != "all" && $search["forums"] != "skip") {
        if (!is_array($search["forums"])) {
            $search["forums"] = [intval($search["forums"])];
        }
        foreach ($search["forums"] as $forum) {
            $forum = intval($forum);
            if (!isset($searchin[$forum])) {
                ($query = sql_query("SELECT f.fid FROM " . TSF_PREFIX . "forums f LEFT JOIN " . TSF_PREFIX . "forumpermissions p ON (f.$fid = p.fid AND p.$gid = '" . $CURUSER["usergroup"] . "') WHERE INSTR(CONCAT(',',parentlist,','),'," . $forum . ",') > 0 AND (ISNULL(p.fid) OR p.$cansearch = 'yes')")) || sqlerr(__FILE__, 1289);
                if (mysqli_num_rows($query) == 1) {
                    $forumin[] = "t.$fid = '" . $forum . "'";
                    $searchin[$forum] = 1;
                } else {
                    while ($sforum = mysqli_fetch_assoc($query)) {
                        $fidlist[] = $sforum["fid"];
                    }
                    if (1 < count($fidlist)) {
                        $forumin[] = "t.fid IN (" . implode(",", $fidlist) . ")";
                    }
                }
            }
        }
    }
    ($query = sql_query("SELECT fp.fid,f.fid FROM " . TSF_PREFIX . "forumpermissions fp LEFT JOIN " . TSF_PREFIX . "forums f ON (fp.$fid = f.pid) WHERE (fp.$canview = 'no' OR fp.$cansearch = 'no') AND fp.$gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 1310);
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            $uf[] = 0 + $notin["fid"];
        }
        $unsearchforums = implode(",", $uf);
    }
    $query = sql_query("SELECT fid,password FROM " . TSF_PREFIX . "forums WHERE password != ''");
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            if (!isset($_COOKIE["forumpass_" . $notin["fid"]])) {
                $_COOKIE["forumpass_" . $notin["fid"]] = "";
            }
            if ($_COOKIE["forumpass_" . $notin["fid"]] != md5($CURUSER["id"] . $notin["password"] . $securehash)) {
                $uf2[] = 0 + $notin["fid"];
            }
        }
        if (0 < count($uf2)) {
            if (isset($unsearchforums)) {
                $unsearchforums .= "," . implode(",", $uf2);
            } else {
                $unsearchforums = implode(",", $uf2);
            }
        }
    }
    $permsql = "";
    if (isset($unsearchforums)) {
        $permsql = " AND t.fid NOT IN (" . $unsearchforums . ")";
    }
    $threadsql = "";
    if (isset($search["threadid"]) && is_valid_id($search["threadid"])) {
        $threadsql = " AND t.$tid = '" . $search["threadid"] . "'";
    }
    $threads = [];
    $posts = [];
    $firstposts = [];
    if (0 < count($forumin)) {
        $forumin = " AND (" . implode(" OR ", $forumin) . ")";
    } else {
        $forumin = "";
    }
    if ($search["postthread"] == 1) {
        $searchtype = "titles";
        $query = sql_query("\r\n\t\t\tSELECT t.tid, t.firstpost\r\n\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\tWHERE 1=1 " . $forumin . " " . $thread_usersql . " " . $threadsql . " " . $permsql . " " . $subject_lookin . "\r\n\t\t") or ($query = sql_query("\r\n\t\t\tSELECT t.tid, t.firstpost\r\n\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\tWHERE 1=1 " . $forumin . " " . $thread_usersql . " " . $threadsql . " " . $permsql . " " . $subject_lookin . "\r\n\t\t")) || sqlerr(__FILE__, 1377);
        while ($thread = mysqli_fetch_assoc($query)) {
            $threads[$thread["tid"]] = $thread["tid"];
            if ($thread["firstpost"]) {
                $posts[$thread["tid"]] = $thread["firstpost"];
            }
        }
        $query = sql_query("\r\n\t\t\tSELECT p.pid, p.tid\r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (t.$tid = p.tid)\r\n\t\t\tWHERE 1=1 " . $forumin . " " . $post_usersql . " " . $threadsql . " " . $permsql . " " . $message_lookin . "\r\n\t\t") or ($query = sql_query("\r\n\t\t\tSELECT p.pid, p.tid\r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (t.$tid = p.tid)\r\n\t\t\tWHERE 1=1 " . $forumin . " " . $post_usersql . " " . $threadsql . " " . $permsql . " " . $message_lookin . "\r\n\t\t")) || sqlerr(__FILE__, 1391);
        while ($post = mysqli_fetch_assoc($query)) {
            $posts[$post["pid"]] = $post["pid"];
            $threads[$post["tid"]] = $post["tid"];
        }
        if (count($posts) < 1 && count($threads) < 1) {
            add_breadcrumb($lang->tsf_forums["search_results"]);
            stdhead($lang->tsf_forums["search_results"]);
            build_breadcrumb();
            stdmsg($lang->global["error"], $lang->tsf_forums["searcherror"], false);
            stdfoot();
            exit;
        }
        $threads = implode(",", $threads);
        $posts = implode(",", $posts);
    } else {
        $searchtype = "posts";
        $query = sql_query("\r\n\t\t\tSELECT t.tid, t.firstpost\r\n\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\tWHERE 1=1 " . $forumin . " " . $thread_usersql . " " . $permsql . " " . $subject_lookin . "\r\n\t\t") or ($query = sql_query("\r\n\t\t\tSELECT t.tid, t.firstpost\r\n\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\tWHERE 1=1 " . $forumin . " " . $thread_usersql . " " . $permsql . " " . $subject_lookin . "\r\n\t\t")) || sqlerr(__FILE__, 1417);
        while ($thread = mysqli_fetch_assoc($query)) {
            $threads[$thread["tid"]] = $thread["tid"];
            if ($thread["firstpost"]) {
                $firstposts[$thread["tid"]] = $thread["firstpost"];
            }
        }
        if (count($threads) < 1) {
            add_breadcrumb($lang->tsf_forums["search_results"]);
            stdhead($lang->tsf_forums["search_results"]);
            build_breadcrumb();
            stdmsg($lang->global["error"], $lang->tsf_forums["searcherror"], false);
            stdfoot();
            exit;
        }
        $threads = implode(",", $threads);
        $firstposts = implode(",", $firstposts);
        if ($firstposts) {
            $query = sql_query("\r\n\t\t\t\tSELECT p.pid\r\n\t\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\t\tWHERE p.pid IN (" . $firstposts . ")\r\n\t\t\t") or ($query = sql_query("\r\n\t\t\t\tSELECT p.pid\r\n\t\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\t\tWHERE p.pid IN (" . $firstposts . ")\r\n\t\t\t")) || sqlerr(__FILE__, 1444);
            while ($post = mysqli_fetch_assoc($query)) {
                $posts[$post["pid"]] = $post["pid"];
            }
            $posts = implode(",", $posts);
        }
    }
    return ["searchtype" => $searchtype, "threads" => $threads, "posts" => $posts, "querycache" => ""];
}
function CheckSearchFlood()
{
    global $CURUSER;
    global $lang;
    ($Query = sql_query("SELECT dateline FROM " . TSF_PREFIX . "searchlog WHERE $uid = " . $CURUSER["id"] . " ORDER BY dateline DESC LIMIT 1")) || sqlerr(__FILE__, 1463);
    if (mysqli_num_rows($Query)) {
        $Result = mysqli_fetch_assoc($Query);
        flood_check($lang->tsf_forums["search"], $Result["dateline"]);
    }
}

?>