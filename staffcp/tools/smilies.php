<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

$Language = loadStaffLanguage('smilies');
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";

try {
    $query = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $Result = $query->fetch(PDO::FETCH_ASSOC);
    $MAIN = unserialize($Result["content"]);
} catch (PDOException $e) {
    die("Configuration error");
}

$SmiliePath = $MAIN["pic_base_url"] . "smilies/";
if (is_dir("../images")) {
    $ImagePath = "../images/smilies/";
} else {
    if (is_dir("../pic")) {
        $ImagePath = "../pic/smilies/";
    } else {
        $ImagePath = false;
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && $Act === "update_sorder" && ($sid = intval($_POST["sid"] ?? 0))) {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        echo showAlertErrorModern($Language[21] ?? 'Invalid security token. Please try again.');
        exit;
    }
    
    $sorder = intval($_POST["sorder"] ?? 0);
    try {
        $stmt = $TSDatabase->prepare("UPDATE ts_smilies SET `sorder` = :sorder WHERE `sid` = :sid");
        $stmt->execute([':sorder' => $sorder, ':sid' => $sid]);
        function_161();
        logStaffActionModern(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[19]));
    } catch (PDOException $e) {
        $Message = showAlertErrorModern("Error updating smilie order");
    }
}
if ($Act === "delete" && ($sid = intval($_GET["sid"] ?? 0))) {
    try {
        $stmt = $TSDatabase->prepare("DELETE FROM ts_smilies WHERE `sid` = :sid");
        $stmt->execute([':sid' => $sid]);
        function_161();
        logStaffActionModern(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[19]));
    } catch (PDOException $e) {
        $Message = showAlertErrorModern("Error deleting smilie");
    }
}
if (($Act === "edit" && ($sid = intval($_GET["sid"] ?? 0))) || $Act === "new") {
    if ($Act === "edit") {
        try {
            $stmt = $TSDatabase->prepare("SELECT * FROM ts_smilies WHERE `sid` = :sid");
            $stmt->execute([':sid' => $sid]);
            $smilie = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $smilie = false;
        }
    } else {
        $sid = 0;
        $smilie = [];
        $smilie["stitle"] = "";
        $smilie["stext"] = "";
        $smilie["spath"] = "";
        $smilie["sorder"] = "0";
    }
    $stitle = $smilie["stitle"] ?? "";
    $stext = $smilie["stext"] ?? "";
    $spath = $smilie["spath"] ?? "";
    $sorder = $smilie["sorder"] ?? "0";
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            echo showAlertErrorModern($Language[21] ?? 'Invalid security token. Please try again.');
            exit;
        }
        
        $stitle = trim($_POST["stitle"] ?? "");
        $stext = trim($_POST["stext"] ?? "");
        $spath = trim($_POST["spath"] ?? "");
        $sorder = intval($_POST["sorder"] ?? 0);
        
        try {
            if ($Act === "edit") {
                $stmt = $TSDatabase->prepare("UPDATE ts_smilies SET `stitle` = :stitle, `stext` = :stext, `spath` = :spath, `sorder` = :sorder WHERE `sid` = :sid");
                $stmt->execute([':stitle' => $stitle, ':stext' => $stext, ':spath' => $spath, ':sorder' => $sorder, ':sid' => $sid]);
            } else {
                $stmt = $TSDatabase->prepare("INSERT INTO ts_smilies (stitle, stext, spath, sorder) VALUES (:stitle, :stext, :spath, :sorder)");
                $stmt->execute([':stitle' => $stitle, ':stext' => $stext, ':spath' => $spath, ':sorder' => $sorder]);
            }
            $UPDATED = true;
            function_161();
            logStaffActionModern(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[19]));
        } catch (PDOException $e) {
            $Message = showAlertErrorModern("Error saving smilie");
        }
    }
    if (!isset($UPDATED)) {
        $selectbox = "<select $name = \"spath\" $onchange = \"ChangeImage(this.value);\">";
        $SmilieList = scandir($ImagePath);
        foreach ($SmilieList as $sm) {
            if ($sm !== "." && $sm !== ".." && in_array(function_149($sm), ["gif", "jpg", "jpeg", "bmp", "png"])) {
                $selectbox .= "<option $value = \"" . escape_attr($sm) . "\"" . ($sm === $spath ? " $selected = \"selected\"" : "") . ">" . escape_html($sm) . "</option>";
            }
        }
        $selectbox .= "</select>";
        $List = "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction ChangeImage(selected)\r\n\t\t\t{\r\n\t\t\t\tTSGetID(\"imgpreview\").$src = \"" . escape_attr($SmiliePath) . "\"+selected;\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<form $method = \"post\" $action = \"index.php?do=smilies&$act = " . escape_attr($Act) . "&$sid = " . intval($sid) . "\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">\r\n\t\t\t\t\t" . escape_html($Language[2]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[9]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . escape_html($Language[10]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"stitle\" $value = \"" . escape_attr($stitle) . "\" $size = \"30\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[11]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . escape_html($Language[12]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"stext\" $value = \"" . escape_attr($stext) . "\" $size = \"30\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[13]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . escape_html($Language[14]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t" . $selectbox . " <img $id = \"imgpreview\" $src = \"" . escape_attr($SmiliePath . $spath) . "\" $border = \"0\" $title = \"\" $alt = \"\" $style = \"vertical-align: middle;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . escape_html($Language[15]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"sorder\" $value = \"" . escape_attr($sorder) . "\" $size = \"30\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . escape_attr($Language[16]) . "\" /> <input $type = \"reset\" $value = \"" . escape_attr($Language[17]) . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if (!isset($List)) {
    try {
        $query = $TSDatabase->query("SELECT * FROM ts_smilies ORDER BY sorder ASC, stitle DESC");
        $List = "";
        while ($smilie = $query->fetch(PDO::FETCH_ASSOC)) {
            $List .= "\r\n\t\t<div $style = \"float: left; width: 256px; height: 68px; margin: 0 5px 5px 5px;\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" class=\"mainTableNoMinWidth\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\">\r\n\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=smilies&amp;$act = edit&amp;$sid = " . intval($smilie["sid"]) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . escape_attr(trim($Language[3])) . "\" $title = \"" . escape_attr(trim($Language[3])) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=smilies&amp;$act = delete&amp;$sid = " . intval($smilie["sid"]) . "\" $onclick = \"return confirm('" . escape_attr(trim($Language[5])) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . escape_attr(trim($Language[4])) . "\" $title = \"" . escape_attr(trim($Language[4])) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t</span>\r\n\t\t\t\t<img $src = \"" . escape_attr($SmiliePath . $smilie["spath"]) . "\" $border = \"0\" $title = \"" . escape_attr($smilie["stitle"]) . "\" $alt = \"" . escape_attr($smilie["stitle"]) . "\" $style = \"max-width: 60px; max-height: 16px;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<form $method = \"post\" $action = \"" . escape_attr($_SERVER["SCRIPT_NAME"]) . "?do=smilies&$act = update_sorder\" />\r\n\t\t\t\t\t\t" . getFormTokenField() . "\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"sid\" $value = \"" . intval($smilie["sid"]) . "\" />\r\n\t\t\t\t\t\t" . escape_html($Language[7]) . " <input $type = \"text\" $name = \"sorder\" $value = \"" . escape_attr($smilie["sorder"]) . "\" $size = \"2\" /> <input $type = \"submit\" $value = \"" . escape_attr($Language[6]) . "\" />\r\n\t\t\t\t\t</form>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</div>";
        }
    } catch (PDOException $e) {
        $List = "";
    }
    echo "\r\n\t" . showAlertSuccessModern("<a $href = \"index.php?do=smilies&$act = new\">" . escape_html($Language[18]) . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t" . escape_html($Language[2]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>" . $List;
} else {
    echo $List;
}

function function_161(array $array = []): void
{
    global $TSDatabase, $MAIN;
    
    try {
        $query = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
        $Result = $query->fetch(PDO::FETCH_ASSOC);
        $MAIN = unserialize($Result["content"]);
    } catch (PDOException $e) {
        return;
    }
    
    $smileyId = "\$smilies = array (";
    if (count($array) === 0) {
        try {
            $query = $TSDatabase->query("SELECT stext, spath FROM ts_smilies ORDER BY sorder, stitle");
            $smileyImage = [];
            while ($smileyCode = $query->fetch(PDO::FETCH_ASSOC)) {
                $smileyImage[] = "'" . $smileyCode["stext"] . "' => '" . $smileyCode["spath"] . "'";
            }
        } catch (PDOException $e) {
            return;
        }
    } else {
        $smileyImage = [];
        foreach ($array as $smileyOrder => $file) {
            $smileyImage[] = "'" . $smileyOrder . "' => '" . $file . "'";
        }
    }
    $smileyId = $smileyId . implode(", ", $smileyImage) . ");";
    $smileyPath = "../" . $MAIN["cache"] . "/smilies.php";
    $smileyData = "<?php\n/** TS Generated Cache#14 - Do Not Alter\n * Cache Name: Smilies\n * Generated: " . gmdate("r") . "\n*/\n";
    $smileyData .= $smileyId . "\n?>";
    if (!file_put_contents($smileyPath, $smileyData)) {
        logStaffActionModern("I can't update Smilies cache.. Please check permission of " . $smileyPath . " file..");
    }
}
function function_149(string $file): string
{
    return strtolower(substr(strrchr($file, "."), 1));
}

?>