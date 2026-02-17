<?php
declare(strict_types=1);

checkStaffAuthentication();

$Language = file("languages/" . getStaffLanguage() . "/download_gift.lang");
if ($Language === false) {
    die('Failed to load language file');
}

$Message = "";
$amount = "0";
$type = "GB";
$usergroups = [];
$username = "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $amount = 0 + $_POST["amount"];
    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : "";
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $type = $_POST["type"];
    
    if ($type === "MB") {
        $damount = $amount * 1024 * 1024;
    } else {
        $damount = $amount * 1024 * 1024 * 1024;
    }
    
    if ($amount && $damount && (is_array($usergroups) && count($usergroups) || $username) && $type) {
        try {
            $pdo = $GLOBALS["DatabaseConnect"];
            
            if ($username) {
                if (preg_match("@,@", $username)) {
                    $usernames = explode(",", $username);
                    foreach ($usernames as $user) {
                        $user = trim($user);
                        $stmt = $pdo->prepare("SELECT usergroup FROM users WHERE username = ?");
                        $stmt->execute([$user]);
                        
                        if ($stmt->rowCount() > 0) {
                            $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . str_replace(["{1}", "{2}", "{3}"], [htmlspecialchars($user), htmlspecialchars(formatBytes((float)$damount)), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[13]) . "\\n");
                            
                            $updateStmt = $pdo->prepare("UPDATE users SET downloaded = downloaded + ?, modcomment = IF(ISNULL(modcomment), ?, CONCAT(?, modcomment)) WHERE username = ?");
                            $updateStmt->execute([$damount, $newModComment, $newModComment, $user]);
                        }
                    }
                    $SysMsg = str_replace(["{1}", "{2}", "{3}"], [htmlspecialchars($username), htmlspecialchars(formatBytes((float)$damount)), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[13]);
                } else {
                    $stmt = $pdo->prepare("SELECT usergroup FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    
                    if ($stmt->rowCount() > 0) {
                        $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . str_replace(["{1}", "{2}", "{3}"], [htmlspecialchars($username), htmlspecialchars(formatBytes((float)$damount)), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[13]) . "\\n");
                        
                        $updateStmt = $pdo->prepare("UPDATE users SET downloaded = downloaded + ?, modcomment = IF(ISNULL(modcomment), ?, CONCAT(?, modcomment)) WHERE username = ?");
                        $updateStmt->execute([$damount, $newModComment, $newModComment, $username]);
                        
                        $SysMsg = str_replace(["{1}", "{2}", "{3}"], [htmlspecialchars($username), htmlspecialchars(formatBytes((float)$damount)), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[13]);
                    } else {
                        $Message = showAlertError(htmlspecialchars($Language[12]));
                    }
                }
            } else {
                $work = implode(",", array_map('intval', $usergroups));
                $SysMsg = str_replace(["{1}", "{2}", "{3}"], [htmlspecialchars($work), htmlspecialchars(formatBytes((float)$damount)), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[3]);
                $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . $SysMsg . "\\n");
                
                $placeholders = implode(',', array_fill(0, count($usergroups), '?'));
                $updateStmt = $pdo->prepare("UPDATE users SET downloaded = downloaded + ?, modcomment = IF(ISNULL(modcomment), ?, CONCAT(?, modcomment)) WHERE usergroup IN (0, " . $placeholders . ")");
                
                $params = array_merge([$damount, $newModComment, $newModComment], array_map('intval', $usergroups));
                $updateStmt->execute($params);
            }
            
            if (!$Message && isset($SysMsg)) {
                logStaffAction($SysMsg);
                $Message = showAlertError($SysMsg);
            }
        } catch (PDOException $e) {
            error_log("Database error in download_gift.php: " . $e->getMessage());
            $Message = showAlertError("Database error occurred");
        }
    } else {
        $Message = showAlertError(htmlspecialchars($Language[10]));
    }
}

try {
    $pdo = $GLOBALS["DatabaseConnect"];
    $stmt = $pdo->prepare("SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.id = ? LIMIT 1");
    $stmt->execute([$_SESSION["ADMIN_ID"]]);
    $LoggedAdminDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $count = 0;
    $showusergroups = "
<table>
\t<tr>\t";
    
    $ugStmt = $pdo->query("SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER BY disporder ASC");
    
    while ($UG = $ugStmt->fetch(PDO::FETCH_ASSOC)) {
        if (!($UG["cansettingspanel"] === "yes" && $LoggedAdminDetails["cansettingspanel"] !== "yes" || 
              $UG["canstaffpanel"] === "yes" && $LoggedAdminDetails["canstaffpanel"] !== "yes" || 
              $UG["issupermod"] === "yes" && $LoggedAdminDetails["issupermod"] !== "yes")) {
            if ($count && $count % 8 === 0) {
                $showusergroups .= "</tr><tr>";
            }
            $showusergroups .= "<td><input type=\"checkbox\" name=\"usergroups[]\" value=\"" . htmlspecialchars((string)$UG["gid"]) . "\"" . (is_array($usergroups) && count($usergroups) && in_array($UG["gid"], $usergroups) ? " checked=\"checked\"" : "") . " /></td><td>" . str_replace("{username}", htmlspecialchars($UG["title"]), htmlspecialchars($UG["namestyle"])) . "</td>";
            $count++;
        }
    }
    $showusergroups .= "</tr></table>";
    
} catch (PDOException $e) {
    error_log("Database error loading usergroups: " . $e->getMessage());
    $showusergroups = "";
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "


" . $Message . "
<form method=\"post\" action=\"index.php?do=download_gift\">
<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($_SESSION['csrf_token']) . "\">
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t<tr>
\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">
\t\t\t" . htmlspecialchars($Language[2]) . "
\t\t</td>
\t</tr>
\t<tr>
\t\t<td class=\"alt1\" align=\"right\">" . htmlspecialchars($Language[4]) . "</td>
\t\t<td class=\"alt1\"><input type=\"text\" name=\"amount\" value=\"" . htmlspecialchars($amount) . "\" size=\"10\" />
\t\t\t<select name=\"type\">
\t\t\t\t<option value=\"GB\"" . ($type === "GB" ? " selected=\"selected\"" : "") . ">GB</option>
\t\t\t\t<option value=\"MB\"" . ($type === "MB" ? " selected=\"selected\"" : "") . ">MB</option>
\t\t\t</select></td>
\t</tr>
\t<tr>
\t\t<td class=\"alt2\" align=\"right\">" . htmlspecialchars($Language[11]) . "</td>
\t\t<td class=\"alt2\"><input type=\"text\" name=\"username\" value=\"" . htmlspecialchars($username) . "\" size=\"45\" /> <small>" . htmlspecialchars($Language[14]) . "</small></td>
\t</tr>
\t<tr>
\t\t<td class=\"alt1\" valign=\"top\" align=\"right\">" . htmlspecialchars($Language[6]) . "</td>
\t\t<td class=\"alt1\">" . $showusergroups . "</td>
\t</tr>
\t<tr>
\t\t<td class=\"tcat2\" align=\"right\"></td>
\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[7]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[8]) . "\" /></td>
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

function logStaffAction(string $log): void
{
    try {
        $pdo = $GLOBALS["DatabaseConnect"];
        $stmt = $pdo->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION["ADMIN_ID"], time(), $log]);
    } catch (PDOException $e) {
        error_log("Failed to log staff action: " . $e->getMessage());
    }
}

?>
