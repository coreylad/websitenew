<?php
declare(strict_types=1);

checkStaffAuthentication();

$pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
if (!$pdo) {
    die('Database connection not available');
}

$Act = $_GET["act"] ?? $_POST["act"] ?? "";
$Act = trim($Act);
$Cid = isset($_GET["cid"]) ? intval($_GET["cid"]) : (isset($_POST["cid"]) ? intval($_POST["cid"]) : 0);
$Language = file("languages/" . getStaffLanguage() . "/manage_games_categories.lang");
$Message = "";
$List = "";

if ($Act === "delete" && $Cid) {
    try {
        $stmt = $pdo->prepare("SELECT cname FROM ts_games_categories WHERE cid = ?");
        $stmt->execute([$Cid]);
        $Category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("DELETE FROM ts_games_categories WHERE cid = ?");
        $stmt->execute([$Cid]);
        
        if ($stmt->rowCount() > 0) {
            $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($Category["cname"]), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[1]);
            logStaffAction($Message);
            $Message = showAlertMessage($Message);
        }
    } catch (Exception $e) {
        $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
    }
}

if ($Act === "edit" && $Cid) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ts_games_categories WHERE cid = ?");
        $stmt->execute([$Cid]);
        
        if ($stmt->rowCount() > 0) {
            $Category = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
                if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
                    $Message = showAlertError("Invalid CSRF token");
                } else {
                    $Category = [];
                    $Category["cname"] = $_POST["cname"] ?? "";
                    $Category["cname"] = trim($Category["cname"]);
                    $Category["description"] = $_POST["description"] ?? "";
                    $Category["description"] = trim($Category["description"]);
                    $Category["sort"] = isset($_POST["sort"]) ? intval($_POST["sort"]) : 0;
                    
                    if ($Category["cname"]) {
                        $stmt = $pdo->prepare("UPDATE ts_games_categories SET cname = ?, description = ?, sort = ? WHERE cid = ?");
                        $stmt->execute([$Category["cname"], $Category["description"], $Category["sort"], $Cid]);
                        
                        $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($Category["cname"]), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[13]);
                        logStaffAction($Message);
                        $Done = true;
                        $Message = showAlertMessage($Message);
                    } else {
                        $Message = showAlertError($Language[12]);
                    }
                }
            }
            
            if (!isset($Done)) {
                $csrf_token = generateCSRFToken();
                
                echo "\r\n\t\t\t<form method=\"post\" action=\"index.php?do=manage_games_categories\">\r\n\t\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t\t\t<input type=\"hidden\" name=\"act\" value=\"edit\" />\r\n\t\t\t<input type=\"hidden\" name=\"cid\" value=\"" . intval($Cid) . "\" />\r\n\t\t\t" . showAlertMessage("<a href=\"index.php?do=manage_games_categories\">" . htmlspecialchars($Language[17]) . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>" . htmlspecialchars($Language[2]) . " - " . htmlspecialchars($Language[7]) . ": " . htmlspecialchars($Category["cname"]) . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr valign=\"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[3]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"cname\" value=\"" . htmlspecialchars($Category["cname"]) . "\" size=\"70\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr valign=\"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[4]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"description\" value=\"" . htmlspecialchars($Category["description"]) . "\" size=\"70\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr valign=\"top\">\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[5]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"sort\" value=\"" . intval($Category["sort"]) . "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[14]) . "\" accesskey=\"s\" />\r\n\t\t\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[15]) . "\" accesskey=\"r\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    } catch (Exception $e) {
        $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
        echo $Message;
    }
}

if ($Act === "add") {
    $Category = [];
    $Category["cname"] = "";
    $Category["description"] = "";
    $Category["sort"] = "";
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $Message = showAlertError("Invalid CSRF token");
        } else {
            $Category = [];
            $Category["cname"] = $_POST["cname"] ?? "";
            $Category["cname"] = trim($Category["cname"]);
            $Category["description"] = $_POST["description"] ?? "";
            $Category["description"] = trim($Category["description"]);
            $Category["sort"] = isset($_POST["sort"]) ? intval($_POST["sort"]) : 0;
            
            if ($Category["cname"]) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO ts_games_categories (cname, description, sort) VALUES (?, ?, ?)");
                    $stmt->execute([$Category["cname"], $Category["description"], $Category["sort"]]);
                    
                    $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($Category["cname"]), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[16]);
                    logStaffAction($Message);
                    $Message = showAlertMessage($Message);
                    $Done = true;
                } catch (Exception $e) {
                    $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
                }
            } else {
                $Message = showAlertError($Language[12]);
            }
        }
    }
    
    if (!isset($Done)) {
        $csrf_token = generateCSRFToken();
        
        echo "\r\n\t\t<form method=\"post\" action=\"index.php?do=manage_games_categories\">\r\n\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t\t<input type=\"hidden\" name=\"act\" value=\"add\" />\r\n\t\t" . showAlertMessage("<a href=\"index.php?do=manage_games_categories\">" . htmlspecialchars($Language[17]) . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>" . htmlspecialchars($Language[2]) . " - " . htmlspecialchars($Language[9]) . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr valign=\"top\">\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[3]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"cname\" value=\"" . htmlspecialchars($Category["cname"]) . "\" size=\"70\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr valign=\"top\">\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[4]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"description\" value=\"" . htmlspecialchars($Category["description"]) . "\" size=\"70\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr valign=\"top\">\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[5]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"sort\" value=\"" . intval($Category["sort"]) . "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[14]) . "\" accesskey=\"s\" />\r\n\t\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[15]) . "\" accesskey=\"r\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}

$CategoryCount = [];
try {
    $stmt = $pdo->query("SELECT cid FROM ts_games");
    while ($CategoryC = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($CategoryCount[$CategoryC["cid"]])) {
            $CategoryCount[$CategoryC["cid"]]++;
        } else {
            $CategoryCount[$CategoryC["cid"]] = 1;
        }
    }
} catch (Exception $e) {
    error_log("Error loading game counts: " . $e->getMessage());
}

$ChampionCount = [];
try {
    $stmt = $pdo->query("SELECT champ.gid, game.cid FROM ts_games_champions champ LEFT JOIN ts_games game ON (champ.gid = game.gid)");
    while ($CategoryC = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($ChampionCount[$CategoryC["cid"]])) {
            $ChampionCount[$CategoryC["cid"]]++;
        } else {
            $ChampionCount[$CategoryC["cid"]] = 1;
        }
    }
} catch (Exception $e) {
    error_log("Error loading champion counts: " . $e->getMessage());
}

try {
    $stmt = $pdo->query("SELECT * FROM ts_games_categories");
    while ($Category = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $List .= "\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . htmlspecialchars($Category["cname"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t" . htmlspecialchars($Category["description"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t" . intval($Category["sort"]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t" . (isset($CategoryCount[$Category["cid"]]) ? intval($CategoryCount[$Category["cid"]]) : 0) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t" . (isset($ChampionCount[$Category["cid"]]) ? intval($ChampionCount[$Category["cid"]]) : 0) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t<a href=\"index.php?do=manage_games_categories&amp;act=edit&amp;cid=" . intval($Category["cid"]) . "\"><img src=\"./images/tool_edit.png\" alt=\"" . htmlspecialchars($Language[7]) . "\" title=\"" . htmlspecialchars($Language[7]) . "\" border=\"0\" /></a> <a href=\"index.php?do=manage_games_categories&amp;act=delete&amp;cid=" . intval($Category["cid"]) . "\" onclick=\"return ConfirmDelete();\"><img src=\"./images/tool_delete.png\" alt=\"" . htmlspecialchars($Language[8]) . "\" title=\"" . htmlspecialchars($Language[8]) . "\" border=\"0\" /></a>\r\n\t\t</td>\r\n\t</tr>";
    }
} catch (Exception $e) {
    $List .= "<tr><td colspan=\"6\" class=\"alt1\">Error loading categories: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}

echo "\r\n<script type=\"text/javascript\">\r\n\tfunction ConfirmDelete()\r\n\t{\r\n\t\tvar Check = confirm(\"" . trim($Language[10]) . "\");\r\n\t\tif (Check)\r\n\t\t\treturn true;\r\n\t\telse\r\n\t\t\treturn false;\r\n\t}\r\n</script>\r\n" . showAlertMessage("<a href=\"index.php?do=manage_games_categories&amp;act=add\">" . htmlspecialchars($Language[9]) . "</a>") . "\r\n" . $Message . "\r\n<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" align=\"center\" width=\"90%\" style=\"border-collapse:separate\" class=\"tborder\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" colspan=\"6\" align=\"center\">\r\n\t\t\t" . htmlspecialchars($Language[2]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[3]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . htmlspecialchars($Language[4]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t" . htmlspecialchars($Language[5]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t" . htmlspecialchars($Language[18]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t" . htmlspecialchars($Language[19]) . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t" . htmlspecialchars($Language[6]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $List . "\r\n</table>";

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
