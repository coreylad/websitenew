<?php
declare(strict_types=1);

checkStaffAuthentication();

$pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
if (!$pdo) {
    die('Database connection not available');
}

$Language = file("languages/" . getStaffLanguage() . "/search_ip.lang");
$Message = "";
$username = $_GET["username"] ?? "";
$username = trim($username);
$ip = $_GET["ip"] ?? "";
$ip = trim($ip);

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $Message = showAlertError("Invalid CSRF token");
    } else {
        $username = trim($_POST["username"]);
        $ip = trim($_POST["ip"]);
        
        if ($username) {
            try {
                $stmt = $pdo->prepare("SELECT u.id, u.ip, u.username, u.last_access, u.email, u.uploaded, u.downloaded, u.invites, u.seedbonus, g.title, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->rowCount() > 0) {
                    $User = $stmt->fetch(PDO::FETCH_ASSOC);
                    $User["ip"] = htmlspecialchars($User["ip"]);
                    $userips = [];
                    $userips[] = "<a href=\"index.php?do=search_ip&amp;ip=" . htmlspecialchars($User["ip"]) . "\">" . htmlspecialchars($User["ip"]) . "</a>";
                    
                    $stmt = $pdo->prepare("SELECT ip FROM iplog WHERE userid = ? ORDER BY ip DESC");
                    $stmt->execute([$User["id"]]);
                    
                    while ($uip = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $uip["ip"] = htmlspecialchars($uip["ip"]);
                        if (!in_array("<a href=\"index.php?do=search_ip&amp;ip=" . htmlspecialchars($uip["ip"]) . "\">" . htmlspecialchars($uip["ip"]) . "</a>", $userips)) {
                            $userips[] = "<a href=\"index.php?do=search_ip&amp;ip=" . htmlspecialchars($uip["ip"]) . "\">" . htmlspecialchars($uip["ip"]) . "</a>";
                        }
                    }
                    
                    $Found = "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . htmlspecialchars($User["username"]) . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["email"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t<div align=\"justify\"><small>" . implode(" | ", $userips) . "</small></div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["title"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . formatTimestamp($User["last_access"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . formatBytes((float)$User["uploaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . formatBytes((float)$User["downloaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["seedbonus"]) . "\r\n\t\t\t\t\t</td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                    $Message = "\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"9\"><b>" . htmlspecialchars($Language[3]) . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[9]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[20]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[19]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[12]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[13]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[14]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[15]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[16]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[17]) . "</b></td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . $Found . "\r\n\t\t\t</table>";
                } else {
                    $Message = showAlertError($Language[5]);
                }
            } catch (Exception $e) {
                $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
            }
        } else {
            if ($ip) {
                try {
                    $stmt = $pdo->prepare("SELECT i.ip, u.id, u.username, u.last_access, u.email, u.uploaded, u.downloaded, u.invites, u.seedbonus, u.ip as ip2, g.title, g.namestyle FROM iplog i LEFT JOIN users u ON (i.userid = u.id) LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE i.ip LIKE ?");
                    $stmt->execute(['%' . $ip . '%']);
                    
                    if ($stmt->rowCount() > 0) {
                        $Found = "";
                        while ($User = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            if ($User["username"]) {
                                $Found .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . htmlspecialchars($User["username"]) . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t\t</td>\t\t\t\t\t\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["email"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["ip"] ? $User["ip"] : ($User["ip2"] ? $User["ip2"] : "-")) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["title"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["last_access"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . formatBytes((float)$User["uploaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . formatBytes((float)$User["downloaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["seedbonus"]) . "\r\n\t\t\t\t\t</td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                            }
                        }
                        $Message = "\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"9\"><b>" . htmlspecialchars($Language[3]) . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[9]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[20]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[19]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[12]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[13]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[14]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[15]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[16]) . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[17]) . "</b></td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . $Found . "\r\n\t\t\t</table>";
                    } else {
                        $Message = showAlertError($Language[4]);
                    }
                } catch (Exception $e) {
                    $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
                }
            } else {
                $Message = showAlertError($Language[6]);
            }
        }
    }
}

$csrf_token = generateCSRFToken();

echo "<form action=\"";
echo htmlspecialchars($_SERVER["SCRIPT_NAME"]);
echo "?do=search_ip\" method=\"post\">\r\n";
echo "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n";
echo $Message;
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>";
echo htmlspecialchars($Language[2]);
echo "</b></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo htmlspecialchars($Language[7]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"ip\" value=\"";
echo htmlspecialchars($ip);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo htmlspecialchars($Language[8]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"username\" value=\"";
echo htmlspecialchars($username);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"";
echo htmlspecialchars($Language[10]);
echo "\" accesskey=\"s\" />\r\n\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"";
echo htmlspecialchars($Language[11]);
echo "\" accesskey=\"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";

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

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}

function formatTimestamp(string $timestamp = ""): string
{
    $dateFormatPattern = "m-d-Y h:i A";
    
    if (empty($timestamp)) {
        $timestamp_int = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp_int = strtotime($timestamp);
        } else {
            $timestamp_int = (int)$timestamp;
        }
    }
    
    return date($dateFormatPattern, $timestamp_int);
}

function applyUsernameStyle(string $username, string $namestyle): string
{
    return str_replace("{username}", htmlspecialchars($username), $namestyle);
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
