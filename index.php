<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_GET["act"]) && $_GET["act"] == "Arcade" && isset($_GET["do"]) && $_GET["do"] == "newscore") {
    $input = "";
    foreach ($_POST as $left => $right) {
        $input .= "<input $type = \"hidden\" $name = \"" . htmlspecialchars($left) . "\" $value = \"" . htmlspecialchars($right) . "\">";
    }
    echo "\r\n\t<html>\r\n\t\t<head>\r\n\t\t\t<title>Save new score</title>\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<p>You are being redirected...</p>\r\n\t\t\t<form $method = \"post\" $action = \"" . $BASEURL . "/ts_games/index.php?$act = Arcade&do=newscore\">\r\n\t\t\t" . $input . "\r\n\t\t\t</form>\r\n\t\t\t<script $language = \"javascript\">\r\n\t\t\t\tsetTimeout ( \"autoForward()\" , 500 );\r\n\t\t\t\tfunction autoForward()\r\n\t\t\t\t{\r\n\t\t\t\t\tdocument.forms[0].submit()\r\n\t\t\t\t}\r\n\t\t\t</script>\r\n\t\t</body>\t\t\t\t\r\n\t</html>";
    exit;
} else {
    define("THIS_SCRIPT", "index.php");
    define("TS_PLUGIN_VERSION", "2.1 by xam");
    define("IN_PLUGIN_SYSTEM", true);
    require "./global.php";
    if ($ref == "yes") {
        $uref = !empty($_SERVER["HTTP_REFERER"]) ? htmlspecialchars_uni($_SERVER["HTTP_REFERER"]) : "";
        if (!empty($uref) && !TS_Match($uref, basename($BASEURL)) && (empty($_COOKIE["referrer"]) || isset($_COOKIE["referrer"]) && $_COOKIE["referrer"] != $uref)) {
            setcookie("referrer", $uref, TIMENOW + 3600);
            sql_query("REPLACE INTO referrer set $referrer_url = " . sqlesc($uref));
        }
    }
    $lang->load("index");
    define("MAX_NEWS", $_max_news);
    if (isset($_GET["errorid"]) && $_GET["errorid"] == "404") {
        stderr($lang->global["error"], $lang->index[404], false);
    }
    stdhead(sprintf($lang->index["welcome"], $SITENAME));
    $__cute = 20;
    $_curuser_usergroup = !isset($CURUSER["usergroup"]) || !isset($CURUSER) ? "[0]" : "[" . $CURUSER["usergroup"] . "]";
    $pluginsLeft = $pluginsMiddle = $pluginsRight = $pluginCache = [];
    if ($UseMemcached) {
        if (!($pluginCache = $TSMemcache->check("ts_plugins"))) {
            $Query = sql_query("SELECT * FROM ts_plugins WHERE $active = 1 ORDER BY `sort` ASC");
            while ($pluginRow = mysqli_fetch_assoc($Query)) {
                $pluginCache[] = $pluginRow;
            }
            $TSMemcache->add("ts_plugins", $pluginCache);
        }
    } else {
        $Query = sql_query("SELECT * FROM ts_plugins WHERE $active = 1 ORDER BY `sort` ASC");
        while ($pluginRow = mysqli_fetch_assoc($Query)) {
            $pluginCache[] = $pluginRow;
        }
    }
    foreach ($pluginCache as $plugin) {
        switch ($plugin["position"]) {
            case 1:
                $pluginsLeft[] = $plugin;
                break;
            case 2:
                $pluginsMiddle[] = $plugin;
                break;
            case 3:
                $pluginsRight[] = $plugin;
                break;
        }
    }
    $Output = "\r\n<table $width = \"100%\" $cellpadding = \"0\" $cellspacing = \"0\" $style = \"border: 0;\" class=\"pluginTable\" $valign = \"top\">\r\n\t<tr>";
    if (count($Plugins_LEFT)) {
        $leftPlugins = true;
        $Output .= "\r\n\t<td $style = \"width: " . $_left_plugin_width . "px; vertical-align: top;\" class=\"none\" $valign = \"top\">\r\n\t\t<div $style = \"margin-right: 10px;\">";
        foreach ($Plugins_LEFT as $Plugin) {
            if ($Plugin["permission"] === "[guest]" && $_curuser_usergroup === "[0]" || $Plugin["permission"] === "[all]" || strstr($Plugin["permission"], $_curuser_usergroup)) {
                if (!trim($Plugin["content"]) && file_exists(INC_PATH . "/plugins/" . $Plugin["name"] . ".php")) {
                    include_once INC_PATH . "/plugins/" . $Plugin["name"] . ".php";
                    $pluginValueName = isset(${$Plugin["name"]}) ? ${$Plugin["name"]} : "";
                    if ($pluginValueName && $pluginValueName != "") {
                        $Plugin["content"] = $pluginValueName;
                    }
                }
                if ($Plugin["content"]) {
                    $Output .= buildplugincontent($Plugin);
                }
            }
        }
        $Output .= "\r\n\t\t</div>\r\n\t</td>";
    }
    if (count($Plugins_MIDDLE)) {
        $__cute = 180;
        $Output .= "\r\n\t<td $style = \"vertical-align: top;\" class=\"none\" $valign = \"top\">";
        foreach ($Plugins_MIDDLE as $Plugin) {
            if ($Plugin["permission"] === "[guest]" && $_curuser_usergroup === "[0]" || $Plugin["permission"] === "[all]" || strstr($Plugin["permission"], $_curuser_usergroup)) {
                if (!trim($Plugin["content"]) && file_exists(INC_PATH . "/plugins/" . $Plugin["name"] . ".php")) {
                    include_once INC_PATH . "/plugins/" . $Plugin["name"] . ".php";
                    $pluginValueName = isset(${$Plugin["name"]}) ? ${$Plugin["name"]} : "";
                    if ($pluginValueName && $pluginValueName != "") {
                        $Plugin["content"] = $pluginValueName;
                    }
                }
                if ($Plugin["content"]) {
                    $Output .= buildplugincontent($Plugin);
                }
            }
        }
        $Output .= "\r\n\t</td>";
    }
    if (count($Plugins_RIGHT)) {
        $rightPlugins = true;
        $Output .= "\r\n\t<td $style = \"width: " . $_right_plugin_width . "px; vertical-align: top;\" class=\"none\">\r\n\t\t<div $style = \"margin-left: 10px;\">";
        foreach ($Plugins_RIGHT as $Plugin) {
            if ($Plugin["permission"] === "[guest]" && $_curuser_usergroup === "[0]" || $Plugin["permission"] === "[all]" || strstr($Plugin["permission"], $_curuser_usergroup)) {
                if (!trim($Plugin["content"]) && file_exists(INC_PATH . "/plugins/" . $Plugin["name"] . ".php")) {
                    include_once INC_PATH . "/plugins/" . $Plugin["name"] . ".php";
                    $pluginValueName = isset(${$Plugin["name"]}) ? ${$Plugin["name"]} : "";
                    if ($pluginValueName && $pluginValueName != "") {
                        $Plugin["content"] = $pluginValueName;
                    }
                }
                if ($Plugin["content"]) {
                    $Output .= buildplugincontent($Plugin);
                }
            }
        }
        $Output .= "\r\n\t\t</div>\r\n\t</td>";
    }
    $Output .= "\r\n\t</tr>\r\n</table>";
    echo $Output;
    stdfoot();
}
function buildPluginContent($Plugin)
{
    global $pic_base_url;
    global $BASEURL;
    $Plugin["content"] = str_replace(["{pic_base_url}", "{baseurl}"], [$pic_base_url, $BASEURL], $Plugin["content"]);
    $pluginFriendlyName = str_replace(" ", "_", $Plugin["name"]);
    return "\r\n\t<div class=\"pluginWrapper\">\r\n\t\t<div class=\"pluginHeader\">\r\n\t\t\t" . ts_collapse($pluginFriendlyName) . "\r\n\t\t\t" . $Plugin["description"] . "\r\n\t\t</div>\r\n\t\t<div class=\"pluginContent\">\r\n\t\t" . ts_collapse($pluginFriendlyName, 2, "div") . "\r\n\t\t\t\t" . $Plugin["content"] . "\r\n\t\t\t</div>\r\n\t\t</div>\r\n\t</div>";
}

?>