<?php
$rootpath = "../";
define("THIS_SCRIPT", "syndication.php");
if (isset($_GET["rss"]) && $_GET["rss"] == "true") {
    define("NO_LOGIN_REQUIRED", true);
    require $rootpath . "global.php";
    $secret_key = isset($_GET["secret_key"]) ? htmlspecialchars($_GET["secret_key"]) : "";
    if (empty($secret_key) || strlen($secret_key) != 32) {
        exit;
    }
    $query = sql_query("SELECT * FROM users WHERE $torrent_pass = " . sqlesc($secret_key));
    if (!mysqli_num_rows($query)) {
        exit;
    }
    $user_account = mysqli_fetch_assoc($query);
    if (!$user_account || $user_account["enabled"] != "yes" || $user_account["status"] != "confirmed") {
        exit;
    }
    $GLOBALS["CURUSER"] = $user_account;
    unset($user_account);
    $lang->load("syndication");
    PrintRSS();
    exit;
}
require $rootpath . "global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("syndication");
if (0 < $CURUSER["postsperpage"] && is_valid_id($CURUSER["postsperpage"]) && $CURUSER["postsperpage"] <= 50) {
    $postperpage = intval($CURUSER["postsperpage"]);
} else {
    $postperpage = 0 + $f_postsperpage;
}
add_breadcrumb($lang->syndication["head2"], $BASEURL . "/tsf_forums/syndication.php");
$SystemMessage = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $Links = [];
    $Links[] = "rss=true";
    $Links[] = "secret_key=" . htmlspecialchars_uni($CURUSER["torrent_pass"]);
    if ($_POST["forums"][0] != "all") {
        $forumids = [];
        foreach ($_POST["forums"] as $FID) {
            if (is_valid_id($FID)) {
                $forumids[] = intval($FID);
            }
        }
        $Links[] = "forums=" . implode(",", $forumids);
    }
    $Links[] = "limit=" . min(60, intval($_POST["limit"]));
    $GenerateLink = $BASEURL . "/tsf_forums/syndication.php?" . implode("&amp;", $Links);
    $SystemMessage = show_notice($GenerateLink, false, $lang->syndication["op9"]);
    add_breadcrumb($lang->syndication["op8"], $BASEURL . "/tsf_forums/syndication.php");
}
stdhead($lang->syndication["head"]);
$sfid = isset($_GET["sfid"]) ? intval($_GET["sfid"]) : "all";
if (isset($warningmessage)) {
    echo $warningmessage;
}
build_breadcrumb();
if ($SystemMessage) {
    echo $SystemMessage;
}
$permissions = forum_permissions();
$query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 125);
while ($forum = mysqli_fetch_assoc($query)) {
    if (!isset($_COOKIE["forumpass_" . $forum["fid"]])) {
        $_COOKIE["forumpass_" . $forum["fid"]] = "";
    }
    if (!($forum["password"] != "" && $_COOKIE["forumpass_" . $forum["fid"]] != md5($CURUSER["id"] . $forum["password"] . $securehash))) {
        if ($permissions[$forum["fid"]]["canview"] == "yes") {
            $deepsubforums[$forum["pid"]] = (isset($deepsubforums[$forum["pid"]]) ? $deepsubforums[$forum["pid"]] : "") . "\r\n\t\t<option $value = \"" . $forum["fid"] . "\"" . ($sfid == $forum["fid"] ? " $selected = \"selected\"" : "") . ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $forum["name"] . "</option>";
        }
    }
}
($query = sql_query("\r\n\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\tWHERE f.$type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t")) || sqlerr(__FILE__, 147);
$str = "\r\n\t<select $name = \"forums[]\" $size = \"13\" $multiple = \"multiple\" $style = \"width: 450px;\">\r\n\t<optgroup $label = \"" . $SITENAME . " Forums\">\r\n\t\t<option $value = \"all\"" . ($sfid == "all" ? " $selected = \"selected\"" : "") . ">" . $lang->syndication["op3"] . "</option>";
while ($forum = mysqli_fetch_assoc($query)) {
    if (!isset($_COOKIE["forumpass_" . $forum["fid"]])) {
        $_COOKIE["forumpass_" . $forum["fid"]] = "";
    }
    if (!($forum["password"] != "" && $_COOKIE["forumpass_" . $forum["fid"]] != md5($CURUSER["id"] . $forum["password"] . $securehash))) {
        if ($permissions[$forum["fid"]]["canview"] == "yes") {
            $subforums[$forum["pid"]] = (isset($subforums[$forum["pid"]]) ? $subforums[$forum["pid"]] : "") . "\r\n\t\t\t<option $value = \"" . $forum["fid"] . "\"" . ($sfid == $forum["fid"] ? " $selected = \"selected\"" : "") . ">&nbsp;&nbsp;&nbsp;&nbsp;" . $forum["name"] . "</option>" . (isset($deepsubforums[$forum["fid"]]) ? $deepsubforums[$forum["fid"]] : "");
        }
    }
}
$query = sql_query("\r\n\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\tWHERE f.$type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\tSELECT f.password, f.fid, f.pid, f.name\r\n\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\tWHERE f.$type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t")) || sqlerr(__FILE__, 172);
while ($category = mysqli_fetch_assoc($query)) {
    if (!isset($_COOKIE["forumpass_" . $category["fid"]])) {
        $_COOKIE["forumpass_" . $category["fid"]] = "";
    }
    if (!($category["password"] != "" && $_COOKIE["forumpass_" . $category["fid"]] != md5($CURUSER["id"] . $category["password"] . $securehash))) {
        if ($permissions[$category["fid"]]["canview"] == "yes" && $subforums[$category["fid"]]) {
            $str .= "\r\n\t\t\t<option $value = \"" . $category["fid"] . "\">" . $category["name"] . "</option>" . $subforums[$category["fid"]] . "";
        }
    }
}
$str .= "\r\n\t\t\t</optgroup>\r\n\t\t</select> ";
echo "\r\n<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = generate_link\">\r\n<input $type = \"hidden\" $name = \"action\" $value = \"generate_link\" />\r\n<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" class=\"tborder\" $align = \"center\">\r\n\t<tr>\r\n\t\t<td class=\"thead\"><strong>" . $lang->syndication["head"] . "</strong></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"trow1\" $style = \"border: 0;\" $valign = \"top\">\r\n\t\t\t<fieldset class=\"fieldset\" $style = \"padding: 0px 5px 5px 5px;\">\r\n\t\t\t\t<legend>" . $lang->syndication["op1"] . "</legend>\r\n\t\t\t\t<div>\r\n\t\t\t\t\t<span class=\"smalltext\">\r\n\t\t\t\t\t\t" . $lang->syndication["op2"] . "\r\n\t\t\t\t\t</span>\r\n\t\t\t\t\t" . $str . "\r\n\t\t\t\t</div>\r\n\t\t\t</fieldset>\r\n\t\t\t<fieldset class=\"fieldset\" $style = \"padding: 0px 5px 5px 5px;\">\r\n\t\t\t\t<legend>" . $lang->syndication["op6"] . "</legend>\r\n\t\t\t\t<div>\r\n\t\t\t\t\t<span class=\"smalltext\">\r\n\t\t\t\t\t\t" . $lang->syndication["op7"] . "<br />\r\n\t\t\t\t\t</span>\r\n\t\t\t\t\t<input $type = \"text\" $size = \"5\" $value = \"" . $postperpage . "\" $name = \"limit\" />\r\n\t\t\t\t</div>\r\n\t\t\t</fieldset>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"center\" class=\"subheader\">\r\n\t\t\t<input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->syndication["op8"] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
stdfoot();
exit;
function get_hidden_forums()
{
    global $CURUSER;
    global $securehash;
    $unsearchforums = [];
    ($query = sql_query("SELECT fp.fid,f.fid FROM " . TSF_PREFIX . "forumpermissions fp LEFT JOIN " . TSF_PREFIX . "forums f ON (fp.$fid = f.pid) WHERE fp.$canview = 'no' AND fp.$gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 236);
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            $unsearchforums[] = 0 + $notin["fid"];
        }
    }
    $query = sql_query("SELECT fid,password FROM " . TSF_PREFIX . "forums WHERE password != ''");
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            if ($notin["password"] != "" && $_COOKIE["forumpass_" . $notin["fid"]] != md5($CURUSER["id"] . $notin["password"] . $securehash)) {
                $unsearchforums[] = 0 + $notin["fid"];
            }
        }
    }
    return $unsearchforums;
}
function rfc822($date)
{
    global $CURUSER;
    $timezone = $CURUSER["tzoffset"];
    $fmtdate = gmdate("D, d M Y H:i:s", $date);
    if ($timezone != "") {
        $fmtdate .= " " . str_replace(":", "", $timezone);
    }
    return $fmtdate;
}
function GetURL()
{
    $thisURL = $_SERVER["SCRIPT_NAME"];
    $thisURL = str_replace("/rss.php", "", $thisURL);
    return "http://" . $_SERVER["HTTP_HOST"] . $thisURL;
}
function PrintRSS()
{
    global $SITENAME;
    global $BASEURL;
    global $SITEEMAIL;
    global $charset;
    global $pic_base_url;
    $dreamerURL = geturl();
    $locale = "en-US";
    $desc = "Latest Threads on " . $SITENAME;
    $title = $SITENAME . " RSS Syndicator";
    $copyright = "Copyright &copy; " . date("Y") . " " . $SITENAME;
    $webmaster = $SITEEMAIL;
    $ttl = 20;
    header("Content-type: text/xml");
    echo "<?xml $version = \"1.0\" $encoding = \"" . $charset . "\"?>\n";
    echo "<rss $version = \"2.0\">\r\n          <channel>\r\n            <title>" . htmlspecialchars_uni(addslashes($title)) . "</title>\r\n            <link>" . $dreamerURL . "</link>\r\n            <description>" . htmlspecialchars_uni(addslashes($desc)) . "</description>\r\n            <language>" . $locale . "</language>";
    $defaulttemplate = ts_template();
    $dimagedir = $BASEURL . "/include/templates/" . $defaulttemplate . "/images/";
    $FeedImage = $dimagedir . "rss.png";
    echo "<image>\r\n              <title>" . $title . "</title>\r\n              <url>" . $FeedImage . "</url>\r\n              <link>" . $dreamerURL . "</link>\r\n              <width>100</width>\r\n              <height>30</height>\r\n              <description>" . $title . "</description>\r\n            </image>";
    echo "      <copyright>" . htmlspecialchars_uni(addslashes($copyright)) . "</copyright>\r\n            <webMaster>" . htmlspecialchars_uni(addslashes($webmaster)) . "</webMaster>\r\n            <lastBuildDate>" . rfc822(TIMENOW) . "</lastBuildDate>\r\n            <ttl>" . $ttl . "</ttl>\r\n            <generator>" . $title . "</generator>";
    PrintItems();
    echo "</channel></rss>";
    exit;
}
function PrintItems()
{
    global $SITENAME;
    global $BASEURL;
    global $SITEEMAIL;
    global $secret_key;
    global $lang;
    global $timeformat;
    global $dateformat;
    $lang->load("tsf_forums");
    $Limit = min(60, intval($_GET["limit"]));
    $Forums = isset($_GET["forums"]) ? trim($_GET["forums"]) : "";
    $HiddenForums = get_hidden_forums();
    $WHERE = "";
    if ($Forums) {
        $Searchin = [];
        $Forums = @explode(",", $Forums);
        foreach ($Forums as $ID => $FID) {
            if (!in_array($FID, $HiddenForums)) {
                $Searchin[] = 0 + $FID;
            }
        }
        if (count($Searchin)) {
            $WHERE = " WHERE t.fid IN (0," . implode(",", $Searchin) . ")";
        } else {
            if (count($HiddenForums)) {
                $WHERE = " WHERE t.fid NOT IN (0," . implode(",", $HiddenForums) . ")";
            }
        }
    } else {
        if (count($HiddenForums)) {
            $WHERE = " WHERE t.fid NOT IN (0," . implode(",", $HiddenForums) . ")";
        }
    }
    $Contents = "";
    ($getarticles = sql_query("SELECT t.tid, t.subject, t.dateline, t.username, t.visible, f.name as forumname FROM " . TSF_PREFIX . "threads t LEFT JOIN " . TSF_PREFIX . "forums f ON (t.$fid = f.fid)" . $WHERE . " ORDER BY t.dateline DESC LIMIT " . $Limit)) || sqlerr(__FILE__, 361);
    $rowCount = 0;
    if (0 < @mysqli_num_rows($getarticles)) {
        while (($article = mysqli_fetch_array($getarticles)) && $rowCount < $Limit) {
            $name = htmlspecialchars_uni(addslashes(strip_tags($article["subject"])));
            $Description = sprintf($lang->syndication["descr"], $article["forumname"], htmlspecialchars_uni($article["username"]), my_datee($dateformat, $article["dateline"]), my_datee($timeformat, $article["dateline"]));
            $article["descr"] = format_comment($Description, false);
            $link = $BASEURL . "/tsf_forums/showthread.php?$tid = " . $article["tid"];
            $owner = htmlspecialchars_uni(addslashes(strip_tags($article["username"])));
            $category = htmlspecialchars_uni(addslashes(strip_tags($article["forumname"])));
            $content = htmlspecialchars_uni($article["descr"]);
            $added = my_datee($dateformat, $article["dateline"]);
            echo "<item>\r\n\t\t<title>" . ($article["visible"] == 1 ? $name : $lang->tsf_forums["moderatemsg8"]) . "</title>\r\n\t\t<description>" . $content . "</description>\r\n\t\t<link>" . $link . "</link>\r\n\t\t<author>" . $owner . "</author>\r\n\t\t<category>" . $category . "</category>\r\n\t\t<pubDate>" . $added . "</pubDate>\r\n\t\t</item>";
            $rowCount++;
        }
    }
}
function forum_permissions()
{
    global $CURUSER;
    $permissions = [];
    if (isset($CURUSER) && $CURUSER["usergroup"]) {
        ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "forumpermissions WHERE $gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 398);
        if (mysqli_num_rows($query)) {
            while ($perm = mysqli_fetch_assoc($query)) {
                $permissions[$perm["fid"]] = $perm;
            }
        }
    }
    return $permissions;
}

?>