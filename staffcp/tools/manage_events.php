<?php
declare(strict_types=1);

checkStaffAuthentication();

$pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
if (!$pdo) {
    die('Database connection not available');
}

$Language = file("languages/" . getStaffLanguage() . "/manage_events.lang");
$Message = "";
$Found = "";
$Act = $_GET["act"] ?? $_POST["act"] ?? "";
$Act = trim($Act);
$id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

if ($Act === "delete" && $id) {
    try {
        $stmt = $pdo->prepare("SELECT title FROM ts_events WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($Events = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stmt = $pdo->prepare("DELETE FROM ts_events WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($Events["title"]), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[16]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
            }
        }
    } catch (Exception $e) {
        $Message = showAlertError("Error deleting event: " . htmlspecialchars($e->getMessage()));
    }
}

if ($Act === "edit" && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ts_events WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($Events = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $title = $Events["title"];
            $event = $Events["event"];
            
            if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
                if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
                    $Message = showAlertError("Invalid CSRF token");
                } else {
                    $title = $_POST["title"] ?? "";
                    $title = trim($title);
                    $event = $_POST["event"] ?? "";
                    $event = trim($event);
                    $date = htmlspecialchars($_POST["month"]) . "-" . intval($_POST["day"]) . "-" . intval($_POST["year"]);
                    
                    if ($title && $event && $date) {
                        $stmt = $pdo->prepare("UPDATE ts_events SET title = ?, event = ?, date = ? WHERE id = ?");
                        $stmt->execute([$title, $event, $date, $id]);
                        
                        $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($Events["title"]), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[17]);
                        logStaffAction($Message);
                        $Message = showAlertMessage($Message);
                        $Done = true;
                    } else {
                        $Message = showAlertError($Language[19]);
                    }
                }
            }
            
            if (!isset($Done)) {
                $_date = explode("-", $Events["date"]);
                $showmonths = "\r\n\t\t\t\t<select name=\"month\">";
                foreach ($months as $_m) {
                    $selected = (isset($_POST["month"]) && $_POST["month"] === $_m) || $_m === $_date[0] ? " selected=\"selected\"" : "";
                    $showmonths .= "\r\n\t\t\t\t\t<option value=\"" . htmlspecialchars($_m) . "\"" . $selected . ">" . htmlspecialchars($_m) . "</option>";
                }
                $showmonths .= "\r\n\t\t\t\t</select>";
                
                $csrf_token = generateCSRFToken();
                
                echo "\t\t\r\n\t\t\t" . $Message . "\r\n\t\t\t<form method=\"post\" action=\"index.php?do=manage_events&act=edit&id=" . intval($id) . "\">\r\n\t\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[2]) . " - " . htmlspecialchars($Language[8]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[4]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"title\" value=\"" . htmlspecialchars($title) . "\" style=\"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">" . htmlspecialchars($Language[5]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea name=\"event\" style=\"width: 99%; height: 100px;\">" . htmlspecialchars($event) . "</textarea></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">" . htmlspecialchars($Language[3]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[10]) . " " . $showmonths . " " . htmlspecialchars($Language[11]) . " <input type=\"text\" name=\"day\" size=\"2\" value=\"" . htmlspecialchars(isset($_POST["day"]) ? $_POST["day"] : $_date[1]) . "\" /> " . htmlspecialchars($Language[12]) . " <input type=\"text\" name=\"year\" size=\"4\" value=\"" . htmlspecialchars(isset($_POST["year"]) ? $_POST["year"] : $_date[2]) . "\"></td>\r\n\t\t\t\t</tr>\t\t\t\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[13]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[14]) . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    } catch (Exception $e) {
        $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
        echo $Message;
    }
}

if ($Act === "new") {
    $title = "";
    $event = "";
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $Message = showAlertError("Invalid CSRF token");
        } else {
            $title = $_POST["title"] ?? "";
            $title = trim($title);
            $event = $_POST["event"] ?? "";
            $event = trim($event);
            $date = htmlspecialchars($_POST["month"]) . "-" . intval($_POST["day"]) . "-" . intval($_POST["year"]);
            
            if ($title && $event && $date) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO ts_events (title, event, date) VALUES (?, ?, ?)");
                    $stmt->execute([$title, $event, $date]);
                    
                    $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($title), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[18]);
                    logStaffAction($Message);
                    $Message = showAlertMessage($Message);
                    $Done = true;
                } catch (Exception $e) {
                    $Message = showAlertError("Error adding event: " . htmlspecialchars($e->getMessage()));
                }
            } else {
                $Message = showAlertError($Language[19]);
            }
        }
    }
    
    if (!isset($Done)) {
        $showmonths = "\r\n\t\t\t<select name=\"month\">";
        foreach ($months as $_m) {
            $selected = (isset($_POST["month"]) && $_POST["month"] === $_m) ? " selected=\"selected\"" : "";
            $showmonths .= "\r\n\t\t\t\t<option value=\"" . htmlspecialchars($_m) . "\"" . $selected . ">" . htmlspecialchars($_m) . "</option>";
        }
        $showmonths .= "\r\n\t\t\t</select>";
        
        $csrf_token = generateCSRFToken();
        
        echo "\t\t\r\n\t\t\r\n\t\t" . $Message . "\r\n\t\t<form method=\"post\" action=\"index.php?do=manage_events&act=new&id=" . intval($id) . "\">\r\n\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[2]) . " - " . htmlspecialchars($Language[7]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[4]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"title\" value=\"" . htmlspecialchars($title) . "\" style=\"width: 99%;\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" valign=\"top\">" . htmlspecialchars($Language[5]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea name=\"event\" style=\"width: 99%; height: 100px;\">" . htmlspecialchars($event) . "</textarea></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" valign=\"top\">" . htmlspecialchars($Language[3]) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[10]) . " " . $showmonths . " " . htmlspecialchars($Language[11]) . " <input type=\"text\" name=\"day\" size=\"2\" value=\"" . htmlspecialchars($_POST["day"] ?? "") . "\" /> " . htmlspecialchars($Language[12]) . " <input type=\"text\" name=\"year\" size=\"4\" value=\"" . htmlspecialchars($_POST["year"] ?? "") . "\"></td>\r\n\t\t\t</tr>\t\t\t\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[13]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[14]) . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM ts_events ORDER BY date DESC");
    
    if ($stmt->rowCount() === 0) {
        $Found .= "<tr><td colspan=\"4\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_events&amp;act=new", $Language[15]) . "</td></tr>";
    } else {
        while ($Events = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Events["date"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Events["title"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Events["event"]) . "\r\n\t\t\t</td>\t\t\t\r\n\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t<a href=\"index.php?do=manage_events&amp;act=edit&amp;id=" . intval($Events["id"]) . "\"><img src=\"images/tool_edit.png\" alt=\"" . htmlspecialchars($Language[8]) . "\" title=\"" . htmlspecialchars($Language[8]) . "\" border=\"0\" /></a> <a href=\"index.php?do=manage_events&amp;act=delete&amp;id=" . intval($Events["id"]) . "\"><img src=\"images/tool_delete.png\" alt=\"" . htmlspecialchars($Language[9]) . "\" title=\"" . htmlspecialchars($Language[9]) . "\" border=\"0\" /></a> \r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        }
    }
} catch (Exception $e) {
    $Found .= "<tr><td colspan=\"4\" class=\"alt1\">Error loading events: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}

echo showAlertMessage("<a href=\"index.php?do=manage_events&amp;act=new\">" . htmlspecialchars($Language[7]) . "</a>") . "\r\n" . $Message . "\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"4\">" . htmlspecialchars($Language[2]) . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[3]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[4]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[5]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t" . htmlspecialchars($Language[6]) . "\r\n\t\t</td>\t\t\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>";

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

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}

function showAlertMessage(string $message = ""): string
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
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
