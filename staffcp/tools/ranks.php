<?php
declare(strict_types=1);

checkStaffAuthentication();

$pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
if (!$pdo) {
    die('Database connection not available');
}

$Language = file("languages/" . getStaffLanguage() . "/ranks.lang");
$Act = $_GET["act"] ?? $_POST["act"] ?? "";
$Act = trim($Act);
$Message = "";

if ($Act === "delete" && ($rid = intval($_GET["rid"] ?? 0))) {
    try {
        $stmt = $pdo->prepare("DELETE FROM ts_ranks WHERE rid = ?");
        $stmt->execute([$rid]);
        
        $Message = str_replace(["{1}", "{2}"], [strval($rid), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[3]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
    } catch (Exception $e) {
        $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
    }
}

if ($Act === "edit" && ($rid = intval($_GET["rid"] ?? 0))) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ts_ranks WHERE rid = ?");
        $stmt->execute([$rid]);
        
        if ($stmt->rowCount() > 0) {
            $rank = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
                if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
                    $Message = showAlertError("Invalid CSRF token");
                } else {
                    $usergroup = intval($_POST["usergroup"]);
                    $minposts = intval($_POST["minposts"]);
                    $displaytype = intval($_POST["displaytype"]);
                    $image = trim($_POST["image"]);
                    
                    $stmt = $pdo->prepare("UPDATE ts_ranks SET image = ?, displaytype = ?, minposts = ?, usergroup = ? WHERE rid = ?");
                    $stmt->execute([$image, $displaytype, $minposts, $usergroup, $rid]);
                    
                    $Message = str_replace(["{1}", "{2}"], [strval($rid), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[4]);
                    logStaffAction($Message);
                    $Message = showAlertError($Message);
                    $Updated = true;
                }
            } else {
                $usergroup = $rank["usergroup"];
                $minposts = $rank["minposts"];
                $displaytype = $rank["displaytype"];
                $image = $rank["image"];
            }
            
            if (!isset($Updated)) {
                $csrf_token = generateCSRFToken();
                
                $List = "\r\n\t\t\t<form method=\"post\" action=\"" . htmlspecialchars($_SERVER["SCRIPT_NAME"]) . "?do=ranks&amp;act=edit&amp;rid=" . intval($rid) . "\">\r\n\t\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t\t\t" . showAlertMessage("<a href=\"index.php?do=ranks\">" . htmlspecialchars($Language[5]) . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"3\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[6]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" colspan=\"2\">" . htmlspecialchars($Language[7]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[8]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t\t" . function_109($usergroup, "usergroup") . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" colspan=\"2\">" . htmlspecialchars($Language[9]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[10]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t\t<input type=\"text\" name=\"minposts\" value=\"" . intval($minposts) . "\" size=\"10\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" colspan=\"2\">" . htmlspecialchars($Language[11]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[12]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t\t<select name=\"displaytype\">\r\n\t\t\t\t\t\t\t<option value=\"1\"" . ($displaytype === 1 ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[18]) . "</option>\r\n\t\t\t\t\t\t\t<option value=\"2\"" . ($displaytype === 2 ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[19]) . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" colspan=\"2\">" . htmlspecialchars($Language[13]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[14]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t\t<input type=\"text\" name=\"image\" value=\"" . htmlspecialchars($image) . "\" size=\"40\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t<input type=\"submit\" value=\"" . htmlspecialchars($Language[20]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[21]) . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t</table>\r\n\t\t\t</form>\r\n\t\t\t";
            } else {
                unset($List);
            }
        }
    } catch (Exception $e) {
        $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
    }
}

if ($Act === "new") {
    $usergroup = 0;
    $minposts = 0;
    $displaytype = 1;
    $image = "";
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $Message = showAlertError("Invalid CSRF token");
        } else {
            $usergroup = intval($_POST["usergroup"]);
            $minposts = intval($_POST["minposts"]);
            $displaytype = intval($_POST["displaytype"]);
            $image = trim($_POST["image"]);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO ts_ranks (image, displaytype, minposts, usergroup) VALUES (?, ?, ?, ?)");
                $stmt->execute([$image, $displaytype, $minposts, $usergroup]);
                
                $Message = str_replace(["{1}", "{2}"], [strval($pdo->lastInsertId()), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[22]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Updated = true;
            } catch (Exception $e) {
                $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
            }
        }
    }
    
    if (!isset($Updated)) {
        $csrf_token = generateCSRFToken();
        
        $List = "\r\n\t\t<form method=\"post\" action=\"" . htmlspecialchars($_SERVER["SCRIPT_NAME"]) . "?do=ranks&amp;act=new\">\r\n\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t\t" . showAlertMessage("<a href=\"index.php?do=ranks\">" . htmlspecialchars($Language[5]) . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"3\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[23]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" colspan=\"2\">" . htmlspecialchars($Language[7]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[8]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t" . function_109($usergroup, "usergroup") . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" colspan=\"2\">" . htmlspecialchars($Language[9]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[10]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t<input type=\"text\" name=\"minposts\" value=\"" . intval($minposts) . "\" size=\"10\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" colspan=\"2\">" . htmlspecialchars($Language[11]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[12]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t<select name=\"displaytype\">\r\n\t\t\t\t\t\t<option value=\"1\"" . ($displaytype === 1 ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[18]) . "</option>\r\n\t\t\t\t\t\t<option value=\"2\"" . ($displaytype === 2 ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[19]) . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" colspan=\"2\">" . htmlspecialchars($Language[13]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" width=\"50%\" align=\"justify\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[14]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" width=\"50%\" valign=\"top\">\r\n\t\t\t\t\t<input type=\"text\" name=\"image\" value=\"" . htmlspecialchars($image) . "\" size=\"40\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input type=\"submit\" value=\"" . htmlspecialchars($Language[20]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[21]) . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
    } else {
        unset($List);
    }
}

if (!isset($List)) {
    $List = "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"5\">\r\n\t\t\t\t" . htmlspecialchars($Language[28]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . htmlspecialchars($Language[13]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . htmlspecialchars($Language[7]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . htmlspecialchars($Language[9]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . htmlspecialchars($Language[11]) . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . htmlspecialchars($Language[26]) . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>";
    
    try {
        $stmt = $pdo->query("SELECT * FROM ts_ranks");
        
        if ($stmt->rowCount() > 0) {
            while ($rank = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $List .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\"><img src=\"../" . htmlspecialchars($rank["image"]) . "\" border=\"0\" alt=\"\" title=\"\" /></td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_110($rank["usergroup"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . number_format($rank["minposts"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . ($rank["displaytype"] === 1 ? htmlspecialchars($Language[18]) : htmlspecialchars($Language[19])) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><a href=\"index.php?do=ranks&amp;act=edit&amp;rid=" . intval($rank["rid"]) . "\"><img src=\"images/tool_edit.png\" alt=\"" . trim(htmlspecialchars($Language[6])) . "\" title=\"" . trim(htmlspecialchars($Language[6])) . "\" border=\"0\" /></a> <a href=\"#\" onclick=\"ConfirmDelete(" . intval($rank["rid"]) . ");\"><img src=\"images/tool_delete.png\" alt=\"" . trim(htmlspecialchars($Language[27])) . "\" title=\"" . trim(htmlspecialchars($Language[27])) . "\" border=\"0\" /></a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
        }
    } catch (Exception $e) {
        $List .= "<tr><td colspan=\"5\" class=\"alt1\">Error loading ranks: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
    
    $List .= "\r\n\t</table>";
    
    echo "\r\n\t<script type=\"text/javascript\">\r\n\t\tfunction ConfirmDelete(rID)\r\n\t\t{\r\n\t\t\tif (confirm(\"" . trim($Language[24]) . "\"))\r\n\t\t\t{\r\n\t\t\t\tTSJump(\"index.php?do=ranks&act=delete&rid=\"+rID);\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . showAlertMessage("<a href=\"index.php?do=ranks&amp;act=new\">" . htmlspecialchars($Language[23]) . "</a>") . "\r\n\t" . $Message . $List;
} else {
    echo $List;
}

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

function redirectTo(string $url, bool $timeout = false): void
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; url=" . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script type=\"text/javascript\">\r\n\t\t\t\t\twindow.location.href = \"" . htmlspecialchars($url) . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . htmlspecialchars($url) . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script type=\"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.href = '" . htmlspecialchars($url) . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-equiv=\"refresh\" content=\"5;url=" . htmlspecialchars($url) . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
    }
    exit;
}

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
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

function showAlertMessage(string $message = ""): string
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

function function_109(int $ug, string $name): string
{
    global $Language;
    $pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
    
    $settingOptions = "\r\n\t<select name=\"" . htmlspecialchars($name) . "\">\r\n\t\t<option value=\"0\"" . ($ug === 0 ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[17]) . "</option>";
    
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT gid, title, namestyle FROM usergroups");
            
            while ($usergroup = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settingOptions .= "\r\n\t\t<option value=\"" . intval($usergroup["gid"]) . "\"" . ($usergroup["gid"] === $ug ? " selected=\"selected\"" : "") . ">" . str_replace("{username}", htmlspecialchars($usergroup["title"]), $usergroup["namestyle"]) . "</option>";
            }
        } catch (Exception $e) {
            error_log("Error loading usergroups: " . $e->getMessage());
        }
    }
    
    $settingOptions .= "\r\n\t</select>";
    
    return $settingOptions;
}

function function_110(int $ug): string
{
    global $Language;
    $pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
    
    if ($ug === 0) {
        return "<b><i>" . htmlspecialchars($Language[17]) . "</i></b>";
    }
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT title, namestyle FROM usergroups WHERE gid = ?");
            $stmt->execute([$ug]);
            $usergroup = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usergroup) {
                return str_replace("{username}", htmlspecialchars($usergroup["title"]), $usergroup["namestyle"]);
            }
        } catch (Exception $e) {
            error_log("Error loading usergroup: " . $e->getMessage());
        }
    }
    
    return "Unknown";
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
