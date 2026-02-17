<?php
declare(strict_types=1);

checkStaffAuthentication();

$Language = file("languages/" . getStaffLanguage() . "/invite_tree.lang");
if ($Language === false) {
    die('Failed to load language file');
}

$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : (isset($_POST["username"]) ? trim($_POST["username"]) : "");
$Found = "";
$IFound = "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    if ($username) {
        try {
            $pdo = $GLOBALS["DatabaseConnect"];
            $stmt = $pdo->prepare("SELECT u.id, u.username, u.added, u.last_access, u.invites, u.uploaded, u.downloaded, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.username = ? LIMIT 1");
            $stmt->execute([$username]);
            
            if ($User = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $Found = "\t\t\t
\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"tcat\" colspan=\"6\" align=\"center\">
\t\t\t\t\t\t" . htmlspecialchars($Language[2]) . " - " . htmlspecialchars($User["username"]) . "
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"alt2\">
\t\t\t\t\t\t" . htmlspecialchars($Language[4]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt2\">
\t\t\t\t\t\t" . htmlspecialchars($Language[9]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt2\">
\t\t\t\t\t\t" . htmlspecialchars($Language[10]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt2\">
\t\t\t\t\t\t" . htmlspecialchars($Language[11]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt2\">
\t\t\t\t\t\t" . htmlspecialchars($Language[12]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt2\">
\t\t\t\t\t\t" . htmlspecialchars($Language[13]) . "
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"alt1\" width=\"20%\">
\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . urlencode($User["username"]) . "\">" . applyUsernameStyle(htmlspecialchars($User["username"]), htmlspecialchars($User["namestyle"])) . "</a>
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" width=\"20%\">
\t\t\t\t\t\t" . htmlspecialchars(formatTimestamp($User["added"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" width=\"20%\">
\t\t\t\t\t\t" . htmlspecialchars(formatTimestamp($User["last_access"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" width=\"10%\">
\t\t\t\t\t\t" . htmlspecialchars(number_format((int)$User["invites"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" width=\"15%\">
\t\t\t\t\t\t" . htmlspecialchars(formatBytes((float)$User["uploaded"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\" width=\"15%\">
\t\t\t\t\t\t" . htmlspecialchars(formatBytes((float)$User["downloaded"])) . "
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t</table>
\t\t\t";
                
                $stmt = $pdo->prepare("SELECT u.username, u.added, u.last_access, u.invites, u.uploaded, u.downloaded, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.invited_by = ? ORDER BY u.username ASC");
                $stmt->execute([$User["id"]]);
                
                if ($stmt->rowCount() > 0) {
                    $IFound = "
\t\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t\t\t\t";
                    while ($IUser = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $IFound .= "
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"alt1\" width=\"20%\">
\t\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . urlencode($IUser["username"]) . "\">" . applyUsernameStyle(htmlspecialchars($IUser["username"]), htmlspecialchars($IUser["namestyle"])) . "</a>
\t\t\t\t\t\t</td>
\t\t\t\t\t\t<td class=\"alt1\" width=\"20%\">
\t\t\t\t\t\t\t" . htmlspecialchars(formatTimestamp($IUser["added"])) . "
\t\t\t\t\t\t</td>
\t\t\t\t\t\t<td class=\"alt1\" width=\"20%\">
\t\t\t\t\t\t\t" . htmlspecialchars(formatTimestamp($IUser["last_access"])) . "
\t\t\t\t\t\t</td>
\t\t\t\t\t\t<td class=\"alt1\" width=\"10%\">
\t\t\t\t\t\t\t" . htmlspecialchars(number_format((int)$IUser["invites"])) . "
\t\t\t\t\t\t</td>
\t\t\t\t\t\t<td class=\"alt1\" width=\"15%\">
\t\t\t\t\t\t\t" . htmlspecialchars(formatBytes((float)$IUser["uploaded"])) . "
\t\t\t\t\t\t</td>
\t\t\t\t\t\t<td class=\"alt1\" width=\"15%\">
\t\t\t\t\t\t\t" . htmlspecialchars(formatBytes((float)$IUser["downloaded"])) . "
\t\t\t\t\t\t</td>
\t\t\t\t\t</tr>
\t\t\t\t\t";
                    }
                    $IFound .= "
\t\t\t\t</table>";
                } else {
                    $Message = showAlertError(htmlspecialchars($Language[8]));
                }
            } else {
                $Message = showAlertError(htmlspecialchars($Language[3]));
            }
        } catch (PDOException $e) {
            error_log("Database error in invite_tree.php: " . $e->getMessage());
            $Message = showAlertError("Database error occurred");
        }
    } else {
        $Message = showAlertError(htmlspecialchars($Language[7]));
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "<form action=\"" . htmlspecialchars($_SERVER["SCRIPT_NAME"]) . "?do=invite_tree\" method=\"post\">\n";
echo "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($_SESSION['csrf_token']) . "\">\n";
echo $Message . $Found . $IFound;
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t<tr>
\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>" . htmlspecialchars($Language[2]) . "</b></td>
\t</tr>\t
\t<tr valign=\"top\">
\t\t<td class=\"alt1\" align=\"right\">" . htmlspecialchars($Language[4]) . "</td>
\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"username\" value=\"" . htmlspecialchars($username) . "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>
\t</tr>
\t<tr>
\t\t<td class=\"tcat2\"></td>
\t\t<td class=\"tcat2\">\t
\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[5]) . "\" accesskey=\"s\" />
\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[6]) . "\" accesskey=\"r\" />
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

function applyUsernameStyle(string $username, string $namestyle): string
{
    return str_replace("{username}", $username, $namestyle);
}

?>
