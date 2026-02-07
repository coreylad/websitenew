<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.4 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
require_once INC_PATH . "/functions_icons.php";
$_dt = TIMENOW - TS_TIMEOUT;
$_guests = 0;
$USERLIST = [];
$BOTCACHE = [];
$_wgo_query = sql_query("SELECT distinct s.userid as id, s.useragent, u.username, u.options, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle FROM ts_sessions s LEFT JOIN users u ON (s.$userid = u.id) LEFT JOIN ts_u_perm p ON (u.$id = p.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE s.lastactivity > '" . $_dt . "' ORDER by u.last_access DESC");
$_most_ever = mysqli_num_rows($_wgo_query);
if ($_most_ever) {
    while ($User = mysqli_fetch_assoc($_wgo_query)) {
        if ($User["id"] == 0) {
            if (($DetectBot = ts_spider_detect($User["useragent"])) && !in_array($DetectBot[1], $BOTCACHE)) {
                $User["namestyle"] = $DetectBot[0];
                $User["username"] = $DetectBot[1];
                $BOTCACHE[] = $DetectBot[1];
                $USERLIST[] = $User;
            } else {
                $_guests++;
            }
        } else {
            $USERLIST[] = $User;
        }
    }
}
if (file_exists(TSDIR . "/" . $cache . "/onlinestats.php")) {
    include_once TSDIR . "/" . $cache . "/onlinestats.php";
}
if (!$onlinestats["most_ever"]) {
    $onlinestats["most_ever"] = 0;
}
if ($onlinestats["most_ever"] < $_most_ever) {
    $_cache_array = $onlinestats = ["most_ever" => $_most_ever, "most_ever_time" => TIMENOW];
    $_name = "onlinestats";
    $_filename = TSDIR . "/" . $cache . "/onlinestats.php";
    $_cachecontents = "<?php\n/** TS Generated Cache#2 - Do Not Alter\n * Cache Name: " . $_name . "\n * Generated: " . gmdate("r") . "\n*/\n\n";
    $_cachecontents .= "\$" . $_name . " = " . @var_export($_cache_array, true) . ";\n?>";
    @file_put_contents($_filename, $_cachecontents);
}
$_hidden_members = $_active_members = 0;
$_usernames = [];
foreach ($USERLIST as $_active_users) {
    if (TS_Match($_active_users["options"], "B1") && $_active_users["id"] != $CURUSER["id"] && !$is_mod) {
        $_hidden_members++;
    } else {
        if (TS_Match($_active_users["options"], "B1")) {
            $_hidden_members++;
        } else {
            $_active_members++;
        }
        if ($_active_users["id"]) {
            $_usernames[] = "<span $style = \"white-space: nowrap;\"><a $href = \"" . ts_seo($_active_users["id"], $_active_users["username"]) . "\">" . get_user_color($_active_users["username"], $_active_users["namestyle"]) . "</a>" . (TS_Match($_active_users["options"], "B1") ? "+" : "") . get_user_icons($_active_users) . "</span>";
        } else {
            $_usernames[] = "<span $style = \"white-space: nowrap;\">" . get_user_color($_active_users["username"], $_active_users["namestyle"]) . "</span>";
        }
    }
}
$Legends = "\r\n<script $type = \"text/javascript\">\r\n\tfunction ShowDescription22(TextToShow)\r\n\t{\r\n\t\tif(TextToShow != \"\")\r\n\t\t{\r\n\t\t\tTSGetID(\"WaitingToShow33\").$innerHTML = TextToShow;\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"WaitingToShow33\").$innerHTML = \"\";\r\n\t\t}\r\n\t}\r\n</script>\r\n";
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
        $Legends .= "\r\n\t\t<div $style = \"float:left;\">&nbsp;</div>\r\n\t\t<div class=\"alt2\" $style = \"float:left; height:8px; width:8px; padding:0px;cursor: pointer;\" $group = \"" . $right["title"] . "\" $bold = \"y\" $clr = \"" . $results[1] . "\" $onmouseover = \"ShowDescription22('" . $right["title"] . "');\" $onmouseout = \"ShowDescription22('');\">\r\n\t\t\t<div class=\"tborder\" $style = \"height:8px; width:8px; background:" . $results[1] . ";\"></div>\r\n\t\t</div>";
    }
}
$table = "\r\n<table $width = \"100%\" $cellpadding = \"3\" $cellspacing = \"0\" $border = \"0\" $align = \"center\">\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $colspan = \"2\">\t\t\t\t\r\n\t\t\t" . ($is_mod ? "<span $style = \"float: right;\">[<a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=who_is_online\"><b>" . $lang->index["show"] . "</b></a>]</span>" : "") . "\r\n\t\t\t\r\n\t\t\t<span $style = \"float: right; display: none; margin: 0 5px;\" $id = \"whatsgoinonrefresh\"><img $src = \"" . $BASEURL . "/include/templates/" . $defaulttemplate . "/images/ajax_loading.gif\" $alt = \"\" $title = \"\" class=\"middle\" /></span>\r\n\t\t\t\r\n\t\t\t" . $lang->index["activeusers"] . " " . ts_nf($_most_ever) . sprintf($lang->index["dactiveusers"], ts_nf($_guests), ts_nf($_active_members), ts_nf($_hidden_members)) . "\r\n\t\t</td>\r\n\t<tr>\r\n\t\t<td $colspan = \"2\">\r\n\t\t\t<div $style = \"float: right;\">" . $Legends . "</div>\r\n\t\t\t<div>" . sprintf($lang->index["online"], ts_nf($onlinestats["most_ever"]), my_datee($dateformat, $onlinestats["most_ever_time"]), my_datee($timeformat, $onlinestats["most_ever_time"])) . "</div>\r\n\t\t\t" . implode(", ", $_usernames) . "\r\n\t\t\t<div $style = \"float: right;\" $id = \"WaitingToShow33\" $name = \"WaitingToShow33\"></div>\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
if (defined("IN_AJAX")) {
    $whatsgoinon = $table;
} else {
    $whatsgoinon = "\r\n\t<div $id = \"whatsgoinon\">\r\n\t\t" . $table . "\r\n\t</div>\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction updateWhatsGoinOn()\r\n\t\t{\r\n\t\t\tjQuery(\"#whatsgoinonrefresh\").show();\r\n\t\t\tjQuery.ajax\r\n\t\t\t({\r\n\t\t\t\tdata: \"plugin=whatsgoinon\",\r\n\t\t\t\ttype: \"POST\",\r\n\t\t\t\turl: baseurl+\"/update-plugin.php\",\r\n\t\t\t\tcontentType: \"application/x-www-form-urlencoded; $charset = \"+charset,\r\n\t\t\t\tencoding: charset,\r\n\t\t\t\tcache: false,\r\n\t\t\t\ttimeout: 25000,\r\n\t\t\t\tsuccess: function(result)\r\n\t\t\t\t{\r\n\t\t\t\t\tjQuery(\"#whatsgoinonrefresh\").hide();\r\n\t\t\t\t\tjQuery(\"#whatsgoinon\").html(result);\r\n\t\t\t\t}\r\n\t\t\t});\r\n\t\t};\r\n\r\n\t\tsetInterval(function(){updateWhatsGoinOn();}, 25000);\r\n\t</script>";
}
function TS_Spider_Detect($Data = "")
{
    $SpiderList = ["msnbot" => "MSN Bot", "google" => "Google Bot", "yahoo" => "Yahoo! Bot", "alexa" => "AleXa Bot", "sogou" => "Sogou Web Spider", "baiduspider" => "Baidu Spider", "w3c_validator" => "W3C Validator", "mlbot" => "MLBoT", "yandex" => "YanDeX"];
    foreach ($SpiderList as $Spider => $Name) {
        if (preg_match("@" . $Spider . "@Uis", strtolower($Data))) {
            return ["<b><i><font $color = \"#FF6633\">{username}</font></i></b>", $Name];
        }
    }
    return false;
}

?>