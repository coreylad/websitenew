<?php
declare(strict_types=1);

checkStaffAuthentication();

$pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
if (!$pdo) {
    die('Database connection not available');
}

$Language = file("languages/" . getStaffLanguage() . "/manage_bonus.lang");
$Message = "";
$Act = $_GET["act"] ?? $_POST["act"] ?? "";
$Act = trim($Act);
$id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);

if ($Act === "delete" && $id) {
    try {
        $stmt = $pdo->prepare("SELECT bonusname FROM bonus WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE FROM bonus WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $Result = $stmt->fetch(PDO::FETCH_ASSOC);
                $bonusname = $Result["bonusname"];
                $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($bonusname), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[11]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
            }
        }
    } catch (Exception $e) {
        $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
    }
}

if ($Act === "new") {
    $bonusname = "";
    $points = "";
    $description = "";
    $art = "";
    $menge = "";
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $Message = showAlertError("Invalid CSRF token");
        } else {
            $bonusname = trim($_POST["bonusname"]);
            $points = trim($_POST["points"]);
            $description = trim($_POST["description"]);
            $art = trim($_POST["art"]);
            $menge = trim($_POST["menge"]);
            
            if ($bonusname && $points && $description && $art && $menge) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO bonus (bonusname, points, description, art, menge) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$bonusname, $points, $description, $art, $menge]);
                    
                    if ($stmt->rowCount() > 0) {
                        $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($bonusname), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[13]);
                        logStaffAction($Message);
                        $Message = showAlertMessage($Message);
                        $Done = true;
                    }
                } catch (Exception $e) {
                    $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
                }
            } else {
                $Message = showAlertError($Language[3]);
            }
        }
    }
    
    if (!isset($Done)) {
        $csrf_token = generateCSRFToken();
        
        echo "\r\n\t\t" . $Message . "\r\n\t\t<form method=\"post\" action=\"index.php?do=manage_bonus&act=new\">\r\n\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[2]) . " - " . htmlspecialchars($Language[6]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[14]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"bonusname\" value=\"" . htmlspecialchars($bonusname) . "\" size=\"99\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" valign=\"top\">" . htmlspecialchars($Language[18]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea name=\"description\" style=\"width: 100%; height: 100px;\">" . htmlspecialchars($description) . "</textarea></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[15]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"points\" value=\"" . htmlspecialchars($points) . "\" size=\"30\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[16]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"menge\" value=\"" . htmlspecialchars($menge) . "\" size=\"30\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[17]) . "</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<select name=\"art\">\r\n\t\t\t\t\t\t<option value=\"traffic\"" . ($art === "traffic" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[19]) . "</option>\r\n\t\t\t\t\t\t<option value=\"invite\"" . ($art === "invite" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[20]) . "</option>\r\n\t\t\t\t\t\t<option value=\"title\"" . ($art === "title" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[21]) . "</option>\r\n\t\t\t\t\t\t<option value=\"class\"" . ($art === "class" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[22]) . "</option>\r\n\t\t\t\t\t\t<option value=\"gift_1\"" . ($art === "gift_1" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[23]) . "</option>\r\n\t\t\t\t\t\t<option value=\"warning\"" . ($art === "warning" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[24]) . "</option>\r\n\t\t\t\t\t\t<option value=\"ratiofix\"" . ($art === "ratiofix" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[25]) . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\t\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[7]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[8]) . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}

if ($Act === "edit" && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM bonus WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $Bonus = $stmt->fetch(PDO::FETCH_ASSOC);
            $bonusname = $Bonus["bonusname"];
            $points = $Bonus["points"];
            $description = $Bonus["description"];
            $art = $Bonus["art"];
            $menge = $Bonus["menge"];
            
            if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
                if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
                    $Message = showAlertError("Invalid CSRF token");
                } else {
                    $bonusname = trim($_POST["bonusname"]);
                    $points = trim($_POST["points"]);
                    $description = trim($_POST["description"]);
                    $art = trim($_POST["art"]);
                    $menge = trim($_POST["menge"]);
                    
                    if ($bonusname && $points && $description && $art && $menge) {
                        $stmt = $pdo->prepare("UPDATE bonus SET bonusname = ?, points = ?, description = ?, art = ?, menge = ? WHERE id = ?");
                        $stmt->execute([$bonusname, $points, $description, $art, $menge, $id]);
                        
                        if ($stmt->rowCount() > 0) {
                            $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($bonusname), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[12]);
                            logStaffAction($Message);
                            $Message = showAlertMessage($Message);
                            $Done = true;
                        }
                    } else {
                        $Message = showAlertError($Language[3]);
                    }
                }
            }
            
            if (!isset($Done)) {
                $csrf_token = generateCSRFToken();
                
                echo "\r\n\t\t\t<form method=\"post\" action=\"index.php?do=manage_bonus&act=edit&id=" . intval($id) . "\">\r\n\t\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[2]) . " - " . htmlspecialchars($Language[4]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[14]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"bonusname\" value=\"" . htmlspecialchars($bonusname) . "\" size=\"99\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">" . htmlspecialchars($Language[18]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea name=\"description\" style=\"width: 100%; height: 100px;\">" . htmlspecialchars($description) . "</textarea></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[15]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"points\" value=\"" . htmlspecialchars($points) . "\" size=\"30\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[16]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"menge\" value=\"" . htmlspecialchars($menge) . "\" size=\"30\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[17]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t<select name=\"art\">\r\n\t\t\t\t\t\t\t<option value=\"traffic\"" . ($art === "traffic" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[19]) . "</option>\r\n\t\t\t\t\t\t\t<option value=\"invite\"" . ($art === "invite" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[20]) . "</option>\r\n\t\t\t\t\t\t\t<option value=\"title\"" . ($art === "title" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[21]) . "</option>\r\n\t\t\t\t\t\t\t<option value=\"class\"" . ($art === "class" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[22]) . "</option>\r\n\t\t\t\t\t\t\t<option value=\"gift_1\"" . ($art === "gift_1" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[23]) . "</option>\r\n\t\t\t\t\t\t\t<option value=\"warning\"" . ($art === "warning" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[24]) . "</option>\r\n\t\t\t\t\t\t\t<option value=\"ratiofix\"" . ($art === "ratiofix" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[25]) . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\t\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[7]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[8]) . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    } catch (Exception $e) {
        $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
        echo $Message;
    }
}

$Found = "";

try {
    $stmt = $pdo->query("SELECT * FROM bonus ORDER BY bonusname ASC");
    
    if ($stmt->rowCount() > 0) {
        while ($Bonus = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Bonus["bonusname"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Bonus["points"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Bonus["art"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($Bonus["art"] === "traffic" || $Bonus["art"] === "gift_1" ? formatBytes((float)$Bonus["menge"]) : number_format((float)$Bonus["menge"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars(substr($Bonus["description"], 0, 100)) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t<a href=\"index.php?do=manage_bonus&amp;act=edit&amp;id=" . intval($Bonus["id"]) . "\"><img src=\"images/tool_edit.png\" alt=\"" . htmlspecialchars($Language[4]) . "\" title=\"" . htmlspecialchars($Language[4]) . "\" border=\"0\" /></a> <a href=\"index.php?do=manage_bonus&amp;act=delete&amp;id=" . intval($Bonus["id"]) . "\"><img src=\"images/tool_delete.png\" alt=\"" . htmlspecialchars($Language[5]) . "\" title=\"" . htmlspecialchars($Language[5]) . "\" border=\"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        }
    } else {
        $Found .= "<tr><td colspan=\"6\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_bonus&amp;act=new", $Language[10]) . "</td></tr>";
    }
} catch (Exception $e) {
    $Found .= "<tr><td colspan=\"6\" class=\"alt1\">Error loading bonuses: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}

echo showAlertMessage("<a href=\"index.php?do=manage_bonus&amp;act=new\">" . htmlspecialchars($Language[6]) . "</a>") . "\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"6\">" . htmlspecialchars($Language[2]) . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[14]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[15]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[17]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[16]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[18]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t" . htmlspecialchars($Language[26]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>";

function getStaffLanguage(): string
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}

function checkStaffAuthentication(): void
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}

function redirectTo(string $url): void
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href = \"" . htmlspecialchars($url) . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . htmlspecialchars($url) . "\" />\r\n\t\t</noscript>";
    }
    exit;
}

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}

function showAlertMessage(string $message = ""): string
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

function logStaffAction(string $log): void
{
    $pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION["ADMIN_ID"], time(), $log]);
        } catch (Exception $e) {
            error_log("Failed to log staff action: " . $e->getMessage());
        }
    }
}

function formatBytes(float $bytes = 0): string
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 1073741824000) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 1099511627776, 2) . " TB";
}

function generateCSRFToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
