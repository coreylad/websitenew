<?php
if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.2 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
require_once INC_PATH . "/functions_icons.php";
$_dt = TIMENOW - 86400;
$_qsquery = @sql_query("SELECT userid FROM ts_sessions WHERE `userid` = '0' AND lastactivity > '" . $_dt . "'");
$_guests = ts_nf(@mysqli_num_rows($_qsquery));
$_wgo_query = sql_query("SELECT distinct u.id, u.username, u.options, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle FROM users u LEFT JOIN ts_u_perm p ON (u.`id` = p.userid) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE UNIX_TIMESTAMP(u.last_access) > '" . $_dt . "' ORDER by u.username, u.last_access");
$_most_ever = mysqli_num_rows($_wgo_query) + $_guests;
if (file_exists(TSDIR . "/" . $cache . "/onlinestats.php")) {
    include_once TSDIR . "/" . $cache . "/onlinestats.php";
}
if (!$onlinestats["most_ever"]) {
    $onlinestats["most_ever"] = 0;
}
$_hidden_members = $_active_members = 0;
$_usernames = [];
while ($_active_users = mysqli_fetch_assoc($_wgo_query)) {
    if (TS_Match($_active_users["options"], "B1") && $_active_users["id"] != $CURUSER["id"] && !$is_mod) {
        $_hidden_members++;
    } else {
        if (TS_Match($_active_users["options"], "B1")) {
            $_hidden_members++;
        } else {
            $_active_members++;
        }
        $_usernames[] = "<span $style = \"white-space: nowrap;\"><a $href = \"" . ts_seo($_active_users["id"], $_active_users["username"]) . "\">" . get_user_color($_active_users["username"], $_active_users["namestyle"]) . "</a>" . (TS_Match($_active_users["options"], "B1") ? "+" : "") . get_user_icons($_active_users) . "</span>";
    }
}
$Legends = "\r\n<script $type = \"text/javascript\">\r\n\tfunction ShowDescription2(TextToShow)\r\n\t{\r\n\t\tif (TextToShow != \"\")\r\n\t\t{\r\n\t\t\tTSGetID(\"WaitingToShow2\").$innerHTML = TextToShow;\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"WaitingToShow2\").$innerHTML = \"\";\r\n\t\t}\r\n\t}\r\n</script>\r\n";
if (!isset($UsergroupCache) || isset($UsergroupCache) && !is_array($UsergroupCache)) {
    $UsergroupCache = [];
    if ($UseMemcached) {
        if (!($UsergroupCache = $TSMemcache->check("ugcacheplugins"))) {
            $Query = sql_query("SELECT title, namestyle FROM usergroups");
            while ($P = mysqli_fetch_assoc($Query)) {
                $UsergroupCache[] = $P;
            }
            $TSMemcache->add("ugcacheplugins", $UsergroupCache);
        }
    } else {
        $Query = sql_query("SELECT title, namestyle FROM usergroups");
        while ($P = mysqli_fetch_assoc($Query)) {
            $UsergroupCache[] = $P;
        }
    }
}
foreach ($UsergroupCache as $right) {
    preg_match("#<span $style = \"color:(.*);\">#Ui", $right["namestyle"], $results);
    if (isset($results[1])) {
        $Legends .= "\r\n\t\t<div $style = \"float:left;\">&nbsp;</div>\r\n\t\t<div class=\"alt2\" $style = \"float:left; height:8px; width:8px; padding:0px;cursor: pointer;\" $group = \"" . $right["title"] . "\" $bold = \"y\" $clr = \"" . $results[1] . "\" $onmouseover = \"ShowDescription2('" . $right["title"] . "');\" $onmouseout = \"ShowDescription2('');\">\r\n\t\t\t<div class=\"tborder\" $style = \"height:8px; width:8px; background:" . $results[1] . ";\"></div>\r\n\t\t</div>";
    }
}
$table = "\r\n<table $width = \"100%\" $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $align = \"center\">\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $colspan = \"2\">\r\n\t\t\t" . ($is_mod ? "<span $style = \"float: right;\">[<a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=who_is_online&amp;$act = today\"><b>" . $lang->index["show"] . "</b></a>]</span>" : "") . "\r\n\r\n\t\t\t<span $style = \"float: right; display: none; margin: 0 5px;\" $id = \"todayactiveusersrefresh\"><img $src = \"" . $BASEURL . "/include/templates/" . $defaulttemplate . "/images/ajax_loading.gif\" $alt = \"\" $title = \"\" class=\"middle\" /></span>\r\n\t\t\t\r\n\t\t\t" . sprintf($lang->index["last24visits"], ts_nf($_most_ever)) . sprintf($lang->index["dactiveusers"], ts_nf($_guests), ts_nf($_active_members), ts_nf($_hidden_members)) . "\r\n\t\t</td>\r\n\t<tr>\r\n\t\t<td $colspan = \"2\">\r\n\t\t\t<div $style = \"float: right;\">" . $Legends . "</div>\r\n\t\t\t<div>" . sprintf($lang->index["online"], ts_nf($onlinestats["most_ever"]), my_datee($dateformat, $onlinestats["most_ever_time"]), my_datee($timeformat, $onlinestats["most_ever_time"])) . "</div>\r\n\t\t\t<div $style = \"overflow: auto; height: 100px;\" $align = \"justify\">" . implode(", ", $_usernames) . "</div>\r\n\t\t\t<div $style = \"float: right;\" $id = \"WaitingToShow2\" $name = \"WaitingToShow2\"></div>\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
if (defined("IN_AJAX")) {
    $todayactiveusers = $table;
} else {
    $todayactiveusers = "\r\n\t<div $id = \"todayactiveusers\">\r\n\t\t" . $table . "\r\n\t</div>\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction updateTodayactiveUsers()\r\n\t\t{\r\n\t\t\tjQuery(\"#todayactiveusersrefresh\").show();\r\n\t\t\tjQuery.ajax\r\n\t\t\t({\r\n\t\t\t\tdata: \"plugin=todayactiveusers\",\r\n\t\t\t\ttype: \"POST\",\r\n\t\t\t\turl: baseurl+\"/update-plugin.php\",\r\n\t\t\t\tcontentType: \"application/x-www-form-urlencoded; $charset = \"+charset,\r\n\t\t\t\tencoding: charset,\r\n\t\t\t\tcache: false,\r\n\t\t\t\ttimeout: 25000,\r\n\t\t\t\tsuccess: function(result)\r\n\t\t\t\t{\r\n\t\t\t\t\tjQuery(\"#todayactiveusersrefresh\").hide();\r\n\t\t\t\t\tjQuery(\"#todayactiveusers\").html(result);\r\n\t\t\t\t}\r\n\t\t\t});\r\n\t\t};\r\n\r\n\t\tsetInterval(function(){updateTodayactiveUsers();}, 25000);\r\n\t</script>";
}

?>