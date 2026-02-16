<?php
declare(strict_types=1);

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/plugins.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";

if ($Act == "delete" && ($pid = intval($_GET["pid"]))) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    try {
        $stmt = $GLOBALS["DatabaseConnect"]->prepare("DELETE FROM ts_plugins WHERE pid = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $stmt->close();
        
        $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
    } catch (Exception $e) {
        error_log("Error deleting plugin: " . $e->getMessage());
    }
}

if ($Act == "change_status" && ($pid = intval($_GET["pid"]))) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    try {
        $stmt = $GLOBALS["DatabaseConnect"]->prepare("UPDATE ts_plugins SET active = IF(active = 1, 0, 1) WHERE pid = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $stmt->close();
        
        $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
    } catch (Exception $e) {
        error_log("Error changing plugin status: " . $e->getMessage());
    }
}
if ($Act == "edit" && ($pid = intval($_GET["pid"])) || $Act == "new") {
    if ($Act == "edit") {
        try {
            $stmt = $GLOBALS["DatabaseConnect"]->prepare("SELECT * FROM ts_plugins WHERE pid = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $result = $stmt->get_result();
            $Plugin = $result->fetch_assoc();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error fetching plugin: " . $e->getMessage());
            $Plugin = [];
        }
    } else {
        $pid = 0;
        $Plugin = [];
        $Plugin["name"] = "";
        $Plugin["description"] = "";
        $Plugin["content"] = "";
        $Plugin["position"] = "2";
        $Plugin["sort"] = "0";
        $Plugin["permission"] = "0";
        $Plugin["active"] = "1";
    }
    
    $name = $Plugin["name"] ?? "";
    $description = $Plugin["description"] ?? "";
    $content = $Plugin["content"] ?? "";
    $position = $Plugin["position"] ?? "2";
    $sort = $Plugin["sort"] ?? "0";
    $permission = $Plugin["permission"] ?? "0";
    $active = $Plugin["active"] ?? "1";
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }
        
        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
        $content = trim($_POST["content"]);
        $position = intval($_POST["position"]);
        $sort = intval($_POST["sort"]);
        $permission = isset($_POST["usergroups"]) && is_array($_POST["usergroups"]) ? implode("", $_POST["usergroups"]) : "";
        $active = intval($_POST["active"]);
        
        try {
            if ($Act == "edit") {
                $stmt = $GLOBALS["DatabaseConnect"]->prepare("UPDATE ts_plugins SET name = ?, description = ?, content = ?, position = ?, sort = ?, permission = ?, active = ? WHERE pid = ?");
                $stmt->bind_param("sssisisi", $name, $description, $content, $position, $sort, $permission, $active, $pid);
            } else {
                $stmt = $GLOBALS["DatabaseConnect"]->prepare("INSERT INTO ts_plugins (name, description, content, position, sort, permission, active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssisii", $name, $description, $content, $position, $sort, $permission, $active);
            }
            $stmt->execute();
            $stmt->close();
            
            $UPDATED = true;
            $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
            logStaffAction($Message);
            $Message = showAlertError($Message);
        } catch (Exception $e) {
            error_log("Error saving plugin: " . $e->getMessage());
            $Message = showAlertError("An error occurred while saving the plugin.");
        }
    }
    if (!isset($UPDATED)) {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['csrf_token'];
        
        try {
            $squery = $GLOBALS["DatabaseConnect"]->query("SELECT gid, title, namestyle FROM usergroups");
            $sgids = "";
            while ($gid = $squery->fetch_assoc()) {
                $sgids .= "\r\n\t\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[" . htmlspecialchars($gid["gid"], ENT_QUOTES, 'UTF-8') . "]\"" . ($permission && strstr($permission, "[" . $gid["gid"] . "]") ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> " . htmlspecialchars(strip_tags(str_replace("{username}", $gid["title"], $gid["namestyle"]), "<b><span><strong><em><i><u>"), ENT_QUOTES, 'UTF-8') . "</label>\r\n\t\t\t</div>";
            }
        } catch (Exception $e) {
            error_log("Error fetching usergroups: " . $e->getMessage());
            $sgids = "";
        }
        
        $sgids .= "\r\n\t\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[guest]\"" . ($permission && strstr($permission, "[guest]") ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> -" . htmlspecialchars($Language[33], ENT_QUOTES, 'UTF-8') . "-</label>\r\n\t\t\t</div>";
        $sgids .= "\r\n\t\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[all]\"" . ($permission && strstr($permission, "[all]") ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> -" . htmlspecialchars($Language[34], ENT_QUOTES, 'UTF-8') . "-</label>\r\n\t\t\t</div>";
        
        $List = loadTinyMCEEditor() . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=plugins&$act = " . htmlspecialchars($Act, ENT_QUOTES, 'UTF-8') . "&$pid = " . intval($pid) . "\">\r\n\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . "\" />\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=plugins\">" . htmlspecialchars($Language[23], ENT_QUOTES, 'UTF-8') . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . htmlspecialchars($Language[16], ENT_QUOTES, 'UTF-8') . "<div class=\"alt2Div\">" . htmlspecialchars($Language[17], ENT_QUOTES, 'UTF-8') . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\" $style = \"width: 97%;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . htmlspecialchars($Language[18], ENT_QUOTES, 'UTF-8') . "<div class=\"alt2Div\">" . htmlspecialchars($Language[19], ENT_QUOTES, 'UTF-8') . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"description\" $value = \"" . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "\" $style = \"width: 97%;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . htmlspecialchars($Language[20], ENT_QUOTES, 'UTF-8') . "<div class=\"alt2Div\">" . htmlspecialchars($Language[21], ENT_QUOTES, 'UTF-8') . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<textarea $name = \"content\" $style = \"width: 99%; height: 80px;\">" . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('content');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . htmlspecialchars($Language[10], ENT_QUOTES, 'UTF-8') . "<div class=\"alt2Div\">" . htmlspecialchars($Language[24], ENT_QUOTES, 'UTF-8') . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<select $name = \"position\">\r\n\t\t\t\t\t\t<option $value = \"1\"" . ($position == 1 ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($Language[11], ENT_QUOTES, 'UTF-8') . "</option>\r\n\t\t\t\t\t\t<option $value = \"2\"" . ($position == 2 ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($Language[12], ENT_QUOTES, 'UTF-8') . "</option>\r\n\t\t\t\t\t\t<option $value = \"3\"" . ($position == 3 ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($Language[13], ENT_QUOTES, 'UTF-8') . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . htmlspecialchars($Language[25], ENT_QUOTES, 'UTF-8') . "<div class=\"alt2Div\">" . htmlspecialchars($Language[28], ENT_QUOTES, 'UTF-8') . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<select $name = \"active\">\r\n\t\t\t\t\t\t<option $value = \"1\"" . ($active == 1 ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($Language[26], ENT_QUOTES, 'UTF-8') . "</option>\r\n\t\t\t\t\t\t<option $value = \"0\"" . ($active == 0 ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($Language[27], ENT_QUOTES, 'UTF-8') . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . htmlspecialchars($Language[7], ENT_QUOTES, 'UTF-8') . "<div class=\"alt2Div\">" . htmlspecialchars($Language[29], ENT_QUOTES, 'UTF-8') . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"sort\" $value = \"" . htmlspecialchars($sort, ENT_QUOTES, 'UTF-8') . "\" $size = \"10\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . htmlspecialchars($Language[30], ENT_QUOTES, 'UTF-8') . "<div class=\"alt2Div\">" . htmlspecialchars($Language[31], ENT_QUOTES, 'UTF-8') . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t" . $sgids . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"reset\" $value = \"" . htmlspecialchars($Language[22], ENT_QUOTES, 'UTF-8') . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "save_order") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    try {
        foreach ($_POST as $row) {
            $segments = explode(":", $row);
            $position = str_replace("column-", "", $segments[0]);
            $plugins = explode(",", $segments[1]);
            
            $stmt = $GLOBALS["DatabaseConnect"]->prepare("UPDATE ts_plugins SET position = ?, sort = ? WHERE pid = ?");
            
            foreach ($plugins as $sort => $pid) {
                $pid = str_replace("plugin-", "", $pid);
                $stmt->bind_param("sii", $position, $sort, $pid);
                $stmt->execute();
                
                echo "Moved Plugin: " . intval($pid) . " to position: " . htmlspecialchars($position, ENT_QUOTES, 'UTF-8') . " and updated rank to: " . intval($sort) . "<br />";
            }
            
            $stmt->close();
        }
        
        $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
        logStaffAction($Message);
    } catch (Exception $e) {
        error_log("Error saving plugin order: " . $e->getMessage());
        echo "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
    
    exit;
} else {
    if (!isset($List)) {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['csrf_token'];
        
        try {
            $LeftPlugins = [];
            $MiddlePlugins = [];
            $RightPlugins = [];
            
            $query = $GLOBALS["DatabaseConnect"]->query("SELECT * FROM ts_plugins ORDER BY active DESC, sort ASC");
            while ($plugin = $query->fetch_assoc()) {
                if ($plugin["position"] == "1") {
                    $LeftPlugins[] = $plugin;
                } else {
                    if ($plugin["position"] == "2") {
                        $MiddlePlugins[] = $plugin;
                    } else {
                        $RightPlugins[] = $plugin;
                    }
                }
            }
            
            $List1 = implode(" ", function_315($LeftPlugins));
            $List2 = implode(" ", function_315($MiddlePlugins));
            $List3 = implode(" ", function_315($RightPlugins));
        } catch (Exception $e) {
            error_log("Error fetching plugins: " . $e->getMessage());
            $List1 = $List2 = $List3 = "";
        }
        
        echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tvar settings \$t = \r\n\t\t{\r\n\t\t\t" . pluginDeactivate() . "\r\n\t\t};\r\n\t\tvar $options = \r\n\t\t{\r\n\t\t\tportal \t: \"columns\",\r\n\t\t\teditorEnabled : true,\r\n\t\t\tsaveurl : \"index.php?do=plugins&$act = save_order\",\r\n\t\t\tTSDebug : true\r\n\t\t};\r\n\t\tvar $data = {};\r\n\t\tvar portal;\r\n\t\tEvent.observe(window, \"load\", function()\r\n\t\t{\r\n\t\t\$tportal = new Portal(settings, options, data);\r\n\t\t});\r\n\t</script>\r\n\t" . showAlertMessage("<a $href = \"index.php?do=plugins&$act = new&csrf_token=" . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($Language[9], ENT_QUOTES, 'UTF-8') . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<div $id = \"wrapper\">\r\n\t\t\t\t\t<div $id = \"columns\">\r\n\t\t\t\t\t\t<div $id = \"column-1\" class=\"column menu\"></div>\r\n\t\t\t\t\t\t<div $id = \"column-2\" class=\"column blocks\"></div>\r\n\t\t\t\t\t\t<div $id = \"column-3\" class=\"column sidebar\"></div>\r\n\t\t\t\t\t\t<div class=\"portal-column\" $id = \"portal-column-block-list\" $style = \"display: none;\">\r\n\t\t\t\t\t\t\t" . $List1 . "\r\n\t\t\t\t\t\t\t" . $List2 . "\r\n\t\t\t\t\t\t\t" . $List3 . "\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div $style = \"clear:both;\"></div>\r\n\t\t\t\t\t<div $id = \"debug\" $style = \"display: none;\">\r\n\t\t\t\t\t\t<p $style = \"margin:0px;\" $id = \"data\"></p>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
    } else {
        echo $List;
    }
}
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $editorContent = ob_get_contents();
    ob_end_clean();
    return $editorContent;
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
function redirectTo($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; $url = " . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log): void
{
    try {
        $stmt = $GLOBALS["DatabaseConnect"]->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (?, ?, ?)");
        $uid = $_SESSION["ADMIN_ID"];
        $time = time();
        $stmt->bind_param("sis", $uid, $time, $log);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error logging staff action: " . $e->getMessage());
    }
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
// DEAD CODE: function_316() is never called. Appears to format plugin positions for JSON output.
function function_316(): string
{
    try {
        $plugins = $GLOBALS["DatabaseConnect"]->query("SELECT * FROM ts_plugins ORDER BY sort ASC");
        $pluginId = [];
        while ($plugin = $plugins->fetch_assoc()) {
            $pluginId["column-" . $plugin["position"]][] = $plugin;
        }
        $pluginName = [];
        foreach ($pluginId as $pluginVersion => $plugins) {
            $pluginEnabled = [];
            foreach ($plugins as $plugin) {
                $pluginEnabled[] = "'plugin-" . intval($plugin["pid"]) . "'";
            }
            $pluginName[] = "'" . $pluginVersion . "'" . ":[" . implode(",", $pluginEnabled) . "]";
        }
        return implode(",", $pluginName);
    } catch (Exception $e) {
        error_log("Error in function_316: " . $e->getMessage());
        return "";
    }
}
function function_315($PluginArray): array
{
    global $Language;
    $gameId = [];
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrfToken = $_SESSION['csrf_token'];
    
    foreach ($PluginArray as $Plugin) {
        $gameId[] = "\r\n\t\t<div class=\"block\" $id = \"plugin-" . intval($Plugin["pid"]) . "\">\r\n\t\t\t<h1 class=\"draghandle\">\r\n\t\t\t\t" . htmlspecialchars($Plugin["description"], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t</h1>\r\n\t\t\t<p>\r\n\t\t\t\t" . ($Plugin["active"] == 1 ? "<a $href = \"index.php?do=plugins&amp;$act = change_status&amp;$pid = " . intval($Plugin["pid"]) . "&amp;csrf_token=" . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . "\"><img $src = \"images/accept.png\" $alt = \"" . htmlspecialchars(trim($Language[15]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[15]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>" : "<a $href = \"index.php?do=plugins&amp;$act = change_status&amp;$pid = " . intval($Plugin["pid"]) . "&amp;csrf_token=" . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . "\"><img $src = \"images/cancel.png\" $alt = \"" . htmlspecialchars(trim($Language[14]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[14]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>") . "\r\n\t\t\t\t<a $href = \"index.php?do=plugins&amp;$act = edit&amp;$pid = " . intval($Plugin["pid"]) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . htmlspecialchars(trim($Language[4]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[4]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=plugins&amp;$act = delete&amp;$pid = " . intval($Plugin["pid"]) . "&amp;csrf_token=" . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . "\" $onclick = \"return confirm('" . htmlspecialchars(trim($Language[6]), ENT_QUOTES, 'UTF-8') . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . htmlspecialchars(trim($Language[5]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[5]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</p>\r\n\t\t</div>";
    }
    return $gameId;
}

?>