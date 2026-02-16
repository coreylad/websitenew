<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$Language = loadStaffLanguage('manage_news');
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
        $stmt = $TSDatabase->prepare("SELECT title FROM news WHERE `id` = :id");
        $stmt->execute([':id' => $Cid]);
        if ($stmt->rowCount()) {
            $News = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $TSDatabase->prepare("DELETE FROM news WHERE `id` = :id");
            $stmt->execute([':id' => $Cid]);
            if ($stmt->rowCount()) {
                $Message = str_replace(["{1}", "{2}"], [escape_html($News["title"]), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[4]);
                logStaffActionModern($Message);
                function_270();
                $Message = showAlertSuccessModern($Message);
            }
        }
    } catch (PDOException $e) {
        $Message = showAlertErrorModern("Error deleting news");
    }
}
if ($Act === "edit" && $Cid) {
    try {
        $stmt = $TSDatabase->prepare("SELECT * FROM news WHERE `id` = :id");
        $stmt->execute([':id' => $Cid]);
        if ($stmt->rowCount()) {
            $News = $stmt->fetch(PDO::FETCH_ASSOC);
            $title = $News["title"];
            $body = $News["body"];
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                if (!validateFormToken($_POST['form_token'] ?? '')) {
                    echo showAlertErrorModern($Language[21] ?? 'Invalid security token. Please try again.');
                    exit;
                }
                
                $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
                $body = isset($_POST["body"]) ? trim($_POST["body"]) : "";
                if ($title && $body) {
                    try {
                        $stmt = $TSDatabase->prepare("UPDATE news SET `title` = :title, `body` = :body WHERE `id` = :id");
                        $stmt->execute([':title' => $title, ':body' => $body, ':id' => $Cid]);
                        function_270();
                        $Message = str_replace(["{1}", "{2}"], [escape_html($News["title"]), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[5]);
                        logStaffActionModern($Message);
                        $Message = showAlertSuccessModern($Message);
                        $Done = true;
                    } catch (PDOException $e) {
                        $Message = showAlertErrorModern("Error updating news");
                    }
                } else {
                    $Message = showAlertErrorModern($Language[9]);
                }
            }
            if (!isset($Done)) {
                echo loadTinyMCEEditor() . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_news&$act = edit&$id = " . intval($Cid) . "\">\r\n\t\t\t" . getFormTokenField() . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[11]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . escape_attr($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"body\" $style = \"width: 100%; height: 100px;\">" . escape_html($body) . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('body');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . escape_attr($Language[13]) . "\" /> <input $type = \"reset\" $value = \"" . escape_attr($Language[14]) . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    } catch (PDOException $e) {
        $Message = showAlertErrorModern("Error loading news");
    }
}
if ($Act === "new") {
    $title = "";
    $body = "";
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            echo showAlertErrorModern($Language[21] ?? 'Invalid security token. Please try again.');
            exit;
        }
        
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $body = isset($_POST["body"]) ? trim($_POST["body"]) : "";
        if (!$title || !$body) {
            $Message = showAlertErrorModern($Language[9]);
        } else {
            try {
                $stmt = $TSDatabase->prepare("INSERT INTO news (userid, added, body, title) VALUES (:userid, NOW(), :body, :title)");
                $stmt->execute([':userid' => $_SESSION["ADMIN_ID"], ':body' => $body, ':title' => $title]);
                if ($stmt->rowCount()) {
                    function_270();
                    $Message = str_replace(["{1}", "{2}"], [escape_html($title), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[6]);
                    logStaffActionModern($Message);
                    $Message = showAlertSuccessModern($Message);
                    $Done = true;
                }
            } catch (PDOException $e) {
                $Message = showAlertErrorModern("Error creating news");
            }
        }
    }
    if (!isset($Done)) {
        echo loadTinyMCEEditor() . "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_news&$act = new\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[18]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . escape_attr($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea $name = \"body\" $style = \"width: 100%; height: 100px;\">" . escape_html($body) . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('body');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . escape_attr($Language[13]) . "\" /> <input $type = \"reset\" $value = \"" . escape_attr($Language[14]) . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$Found = "";
try {
    $query = $TSDatabase->query("SELECT news.*,  u.username, g.namestyle FROM news LEFT JOIN users u ON (news.`userid` = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) ORDER by added DESC");
    if ($query->rowCount() > 0) {
        while ($News = $query->fetch(PDO::FETCH_ASSOC)) {
            $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html($News["title"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . escape_attr($News["username"]) . "\">" . applyUsernameStyle($News["username"], $News["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html(formatTimestamp($News["added"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html(substr(strip_tags($News["body"]), 0, 100)) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_news&amp;$act = edit&amp;$id = " . intval($News["id"]) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . escape_attr($Language[11]) . "\" $title = \"" . escape_attr($Language[11]) . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_news&amp;$act = delete&amp;$id = " . intval($News["id"]) . "\"><img $src = \"images/tool_delete.png\" $alt = \"" . escape_attr($Language[12]) . "\" $title = \"" . escape_attr($Language[12]) . "\" $border = \"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        }
    } else {
        $Found .= "<tr><td $colspan = \"5\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_news&amp;$act = new", $Language[19]) . "</td></tr>";
    }
} catch (PDOException $e) {
    $Found = "<tr><td $colspan = \"5\" class=\"alt1\">Error loading news</td></tr>";
}
echo showAlertSuccessModern("<a $href = \"index.php?do=manage_news&amp;$act = new\">" . escape_html($Language[18]) . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\">" . escape_html($Language[2]) . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[7]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[16]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[15]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . escape_html($Language[8]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . escape_html($Language[17]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>";

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

function formatTimestamp($timestamp = ""): string
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, (int)$timestamp);
}
function function_270(): void
{
    global $MAIN;
    $miscConfig = $MAIN["cache"];
    if (file_exists("../" . $miscConfig . "/news.html")) {
        unlink("../" . $miscConfig . "/news.html");
    }
}
function applyUsernameStyle(string $username, string $namestyle): string
{
    return str_replace("{username}", escape_html($username), $namestyle);
}

?>
