<?php
declare(strict_types=1);

checkStaffAuthentication();

$Language = file("languages/" . getStaffLanguage() . "/search_passkey.lang");
if ($Language === false) {
    die('Failed to load language file');
}

$Message = "";
$passkey = isset($_GET["passkey"]) ? trim($_GET["passkey"]) : "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $passkey = isset($_POST["passkey"]) ? trim($_POST["passkey"]) : "";
    
    if ($passkey) {
        try {
            $pdo = $GLOBALS["DatabaseConnect"];
            $stmt = $pdo->prepare("SELECT id, ip, username, last_access, email, uploaded, downloaded, invites, seedbonus, g.title, g.namestyle FROM users LEFT JOIN usergroups g ON (users.usergroup = g.gid) WHERE torrent_pass = ?");
            $stmt->execute([$passkey]);
            
            if ($User = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $User["ip"] = htmlspecialchars($User["ip"]);
                $userips = [];
                $userips[] = "<a href=\"index.php?do=search_ip&amp;ip=" . urlencode($User["ip"]) . "\">" . $User["ip"] . "</a>";
                
                $findips = $pdo->prepare("SELECT ip FROM iplog WHERE userid = ? ORDER BY ip DESC");
                $findips->execute([$User["id"]]);
                
                while ($uip = $findips->fetch(PDO::FETCH_ASSOC)) {
                    $uip["ip"] = htmlspecialchars($uip["ip"]);
                    if (!in_array("<a href=\"index.php?do=search_ip&amp;ip=" . urlencode($uip["ip"]) . "\">" . $uip["ip"] . "</a>", $userips)) {
                        $userips[] = "<a href=\"index.php?do=search_ip&amp;ip=" . urlencode($uip["ip"]) . "\">" . $uip["ip"] . "</a>";
                    }
                }
                
                $Found = "
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . urlencode($User["username"]) . "\">" . applyUsernameStyle(htmlspecialchars($User["username"]), htmlspecialchars($User["namestyle"])) . "</a>
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t" . htmlspecialchars($User["email"]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t<div align=\"justify\"><small>" . implode(" | ", $userips) . "</small></div>
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t" . htmlspecialchars($User["title"]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t" . htmlspecialchars(formatTimestamp($User["last_access"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t" . htmlspecialchars(formatBytes((float)$User["uploaded"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t" . htmlspecialchars(formatBytes((float)$User["downloaded"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t" . htmlspecialchars(number_format((int)$User["invites"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" valign=\"top\">
\t\t\t\t\t\t" . htmlspecialchars($User["seedbonus"]) . "
\t\t\t\t\t</td>\t\t\t\t\t
\t\t\t\t</tr>
\t\t\t\t";
                
                $Message = "
\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"9\"><b>" . htmlspecialchars($Language[3]) . " for Passkey: " . htmlspecialchars($passkey) . "</b></td>
\t\t\t\t</tr>
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[9]) . "</b></td>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[20]) . "</b></td>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[19]) . "</b></td>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[12]) . "</b></td>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[13]) . "</b></td>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[14]) . "</b></td>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[15]) . "</b></td>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[16]) . "</b></td>
\t\t\t\t\t<td class=\"alt2\"><b>" . htmlspecialchars($Language[17]) . "</b></td>\t\t\t\t\t
\t\t\t\t</tr>
\t\t\t\t" . $Found . "
\t\t\t</table>";
            } else {
                if (strlen($passkey) != 32) {
                    $Message = showAlertError(htmlspecialchars($Language[5]));
                } else {
                    $Message = showAlertError(htmlspecialchars($Language[4]));
                }
            }
        } catch (PDOException $e) {
            error_log("Database error in search_passkey.php: " . $e->getMessage());
            $Message = showAlertError("Database error occurred");
        }
    } else {
        $Message = showAlertError(htmlspecialchars($Language[6]));
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "<form action=\"" . htmlspecialchars($_SERVER["SCRIPT_NAME"]) . "?do=search_passkey\" method=\"post\">\n";
echo "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($_SESSION['csrf_token']) . "\">\n";
echo $Message;
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t<tr>
\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>" . htmlspecialchars($Language[2]) . "</b></td>
\t</tr>
\t<tr valign=\"top\">
\t\t<td class=\"alt1\" align=\"right\">" . htmlspecialchars($Language[7]) . "</td>
\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"passkey\" value=\"" . htmlspecialchars($passkey) . "\" size=\"50\" dir=\"ltr\" tabindex=\"1\" /></td>
\t</tr>\t
\t<tr>
\t\t<td class=\"tcat2\"></td>
\t\t<td class=\"tcat2\">\t
\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[10]) . "\" accesskey=\"s\" />
\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[11]) . "\" accesskey=\"r\" />
\t\t</td>
\t</tr>
</table>
</form>";

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
        echo "
\t\t<script type=\"text/javascript\">
\t\t\twindow.location.href = \"" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\";
\t\t</script>
\t\t<noscript>
\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\" />
\t\t</noscript>";
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
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, (int)$timestamp);
}

function applyUsernameStyle(string $username, string $namestyle): string
{
    return str_replace("{username}", $username, $namestyle);
}

?>
