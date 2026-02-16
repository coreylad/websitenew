<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$Language = loadStaffLanguage('manage_rules');
$Message = "";

try {
    $query = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $Result = $query->fetch(PDO::FETCH_ASSOC);
    $MAIN = unserialize($Result["content"]);
} catch (PDOException $e) {
    die("Configuration error");
}

if ($Act === "delete" && $Cid) {
    try {
        $stmt = $TSDatabase->prepare("SELECT title FROM rules WHERE `id` = :id");
        $stmt->execute([':id' => $Cid]);
        if ($stmt->rowCount()) {
            $Rules = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $TSDatabase->prepare("DELETE FROM rules WHERE `id` = :id");
            $stmt->execute([':id' => $Cid]);
            if ($stmt->rowCount()) {
                $Message = str_replace(["{1}", "{2}"], [escape_html($Rules["title"]), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[4]);
                logStaffActionModern($Message);
                $Message = showAlertSuccessModern($Message);
            }
        }
    } catch (PDOException $e) {
        $Message = showAlertErrorModern("Error deleting rule");
    }
}
if ($Act === "edit" && $Cid) {
    try {
        $stmt = $TSDatabase->prepare("SELECT * FROM rules WHERE `id` = :id");
        $stmt->execute([':id' => $Cid]);
        if ($stmt->rowCount()) {
            $Rules = $stmt->fetch(PDO::FETCH_ASSOC);
            $title = $Rules["title"];
            $text = $Rules["text"];
            $usergroups = explode(",", $Rules["usergroups"]);
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                if (!validateFormToken($_POST['form_token'] ?? '')) {
                    echo showAlertErrorModern($Language[21] ?? 'Invalid security token. Please try again.');
                    exit;
                }
                
                $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
                $text = isset($_POST["text"]) ? trim($_POST["text"]) : "";
                $usergroups = isset($_POST["usergroups"]) ? implode(",", $_POST["usergroups"]) : "";
                if ($title && $text) {
                    try {
                        $stmt = $TSDatabase->prepare("UPDATE rules SET `title` = :title, `text` = :text, `usergroups` = :usergroups WHERE `id` = :id");
                        $stmt->execute([':title' => $title, ':text' => $text, ':usergroups' => $usergroups, ':id' => $Cid]);
                        $Message = str_replace(["{1}", "{2}"], [escape_html($Rules["title"]), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[5]);
                        logStaffActionModern($Message);
                        $Message = showAlertSuccessModern($Message);
                        $Done = true;
                    } catch (PDOException $e) {
                        $Message = showAlertErrorModern("Error updating rule");
                    }
                } else {
                    $Message = showAlertErrorModern($Language[9]);
                }
            }
            if (!isset($Done)) {
                echo loadTinyMCEEditor() . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_rules&$act = edit&$id = " . intval($Cid) . "\">\r\n\t\t\t" . getFormTokenField() . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[11]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . escape_attr($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"text\" $id = \"text\" $style = \"width: 100%; height: 100px;\">" . escape_html($text) . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('text');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . escape_html($Language[20]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_148($usergroups) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . escape_attr($Language[13]) . "\" /> <input $type = \"reset\" $value = \"" . escape_attr($Language[14]) . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    } catch (PDOException $e) {
        $Message = showAlertErrorModern("Error loading rule");
    }
}
if ($Act === "new") {
    $title = "";
    $text = "";
    $usergroups = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            echo showAlertErrorModern($Language[21] ?? 'Invalid security token. Please try again.');
            exit;
        }
        
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $text = isset($_POST["text"]) ? trim($_POST["text"]) : "";
        $usergroups = isset($_POST["usergroups"]) ? implode(",", $_POST["usergroups"]) : "";
        if (!$title || !$text) {
            $Message = showAlertErrorModern($Language[9]);
        } else {
            try {
                $stmt = $TSDatabase->prepare("INSERT INTO rules (title, text, usergroups) VALUES (:title, :text, :usergroups)");
                $stmt->execute([':title' => $title, ':text' => $text, ':usergroups' => $usergroups]);
                if ($stmt->rowCount()) {
                    $Message = str_replace(["{1}", "{2}"], [escape_html($title), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[6]);
                    logStaffActionModern($Message);
                    $Message = showAlertSuccessModern($Message);
                    $Done = true;
                }
            } catch (PDOException $e) {
                $Message = showAlertErrorModern("Error creating rule");
            }
        }
    }
    if (!isset($Done)) {
        echo loadTinyMCEEditor() . "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_rules&$act = new\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[18]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . escape_attr($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea $name = \"text\" $id = \"text\" $style = \"width: 100%; height: 100px;\">" . escape_html($text) . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('text');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . escape_html($Language[20]) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . function_148($usergroups) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . escape_attr($Language[13]) . "\" /> <input $type = \"reset\" $value = \"" . escape_attr($Language[14]) . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$Found = "";
try {
    $query = $TSDatabase->query("SELECT * FROM rules");
    if ($query->rowCount() > 0) {
        while ($Rules = $query->fetch(PDO::FETCH_ASSOC)) {
            $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html($Rules["title"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html(substr(strip_tags($Rules["text"]), 0, 150)) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html($Rules["usergroups"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_rules&amp;$act = edit&amp;$id = " . intval($Rules["id"]) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . escape_attr($Language[11]) . "\" $title = \"" . escape_attr($Language[11]) . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_rules&amp;$act = delete&amp;$id = " . intval($Rules["id"]) . "\"><img $src = \"images/tool_delete.png\" $alt = \"" . escape_attr($Language[12]) . "\" $title = \"" . escape_attr($Language[12]) . "\" $border = \"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        }
    } else {
        $Found .= "<tr><td $colspan = \"4\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_rules&amp;$act = new", $Language[19]) . "</td></tr>";
    }
} catch (PDOException $e) {
    $Found = "<tr><td $colspan = \"4\" class=\"alt1\">Error loading rules</td></tr>";
}
echo showAlertSuccessModern("<a $href = \"index.php?do=manage_rules&amp;$act = new\">" . escape_html($Language[18]) . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\">" . escape_html($Language[2]) . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[7]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[8]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[20]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . escape_html($Language[17]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>";

function loadTinyMCEEditor(int $type = 1, string $mode = "textareas", string $elements = ""): string
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

function function_148($selected = ""): string
{
    global $TSDatabase;
    
    if (!is_array($selected)) {
        $selected = explode(",", $selected);
    }
    
    try {
        $stmt = $TSDatabase->prepare("SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.`id` = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION["ADMIN_ID"]]);
        $currentUserPerms = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return "";
    }
    
    $count = 0;
    $userGroupsHtml = "\r\n\t<table>\r\n\t\t<tr>\t";
    
    try {
        $query = $TSDatabase->query("SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups WHERE `isbanned` = 'no' ORDER by disporder ASC");
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            if (!($row["cansettingspanel"] === "yes" && $currentUserPerms["cansettingspanel"] !== "yes" || $row["canstaffpanel"] === "yes" && $currentUserPerms["canstaffpanel"] !== "yes" || $row["issupermod"] === "yes" && $currentUserPerms["issupermod"] !== "yes")) {
                if ($count && $count % 8 === 0) {
                    $userGroupsHtml .= "</tr><tr>";
                }
                $userGroupsHtml .= "<td><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[" . intval($row["gid"]) . "]\"" . (is_array($selected) && count($selected) && (in_array("[" . $row["gid"] . "]", $selected) || preg_match("#\\[" . intval($row["gid"]) . "\\]#isU", implode("", $selected))) ? " $checked = \"checked\"" : "") . " /></td><td>" . str_replace("{username}", escape_html($row["title"]), $row["namestyle"]) . "</td>";
                $count++;
            }
        }
    } catch (PDOException $e) {
        return "";
    }
    
    $userGroupsHtml .= "</tr></table>";
    return $userGroupsHtml;
}

?>
