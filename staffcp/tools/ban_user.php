<?php
declare(strict_types=1);

checkStaffAuthentication();

$pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
if (!$pdo) {
    die('Database connection not available');
}

$Language = file("languages/" . getStaffLanguage() . "/ban_user.lang");
$Message = "";
$username = $_GET["username"] ?? "";
$username = trim($username);
$usergroup = "";
$reason = "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $Message = showAlertError("Invalid CSRF token");
    } else {
        $username = trim($_POST["username"]);
        $usergroup = intval($_POST["usergroup"]);
        $reason = trim($_POST["reason"]);
        
        if ($username && $usergroup && $reason) {
            try {
                $stmt = $pdo->prepare("SELECT u.ip, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->rowCount() === 0) {
                    $Message = showAlertError($Language[2]);
                } else {
                    $User = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $stmt = $pdo->prepare("SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.id = ? LIMIT 1");
                    $stmt->execute([$_SESSION["ADMIN_ID"]]);
                    $LoggedAdminDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (($User["cansettingspanel"] === "yes" && $LoggedAdminDetails["cansettingspanel"] !== "yes") || 
                        ($User["canstaffpanel"] === "yes" && $LoggedAdminDetails["canstaffpanel"] !== "yes") || 
                        ($User["issupermod"] === "yes" && $LoggedAdminDetails["issupermod"] !== "yes")) {
                        $Message = showAlertError($Language[14]);
                    } else {
                        $SysMsg = str_replace(["{1}", "{2}"], [htmlspecialchars($username), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[15]);
                        $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . " Reason: " . $reason . "\n";
                        
                        $stmt = $pdo->prepare("UPDATE users SET enabled = 'no', usergroup = ?, notifs = ?, modcomment = CONCAT(?, modcomment) WHERE username = ?");
                        $stmt->execute([$usergroup, $reason, $modcomment, $username]);
                        
                        if ($stmt->rowCount() > 0) {
                            logStaffAction($SysMsg);
                            
                            if (($_POST["banip"] ?? "0") === "1") {
                                $stmt = $pdo->prepare("SELECT value FROM ipbans WHERE id = 1");
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    $banned = $stmt->fetch(PDO::FETCH_ASSOC);
                                    if ($banned["value"] !== "") {
                                        $value = trim($banned["value"] . " " . $User["ip"]);
                                    } else {
                                        $value = trim($User["ip"]);
                                    }
                                    
                                    $stmt = $pdo->prepare("UPDATE ipbans SET value = ?, date = NOW(), modifier = ? WHERE id = 1");
                                    $stmt->execute([$value, $_SESSION["ADMIN_ID"]]);
                                } else {
                                    $stmt = $pdo->prepare("INSERT INTO ipbans VALUES (1, ?, NOW(), ?)");
                                    $stmt->execute([trim($User["ip"]), $_SESSION["ADMIN_ID"]]);
                                }
                                
                                $IP = ip2long(trim($User["ip"]));
                                
                                $stmt = $pdo->prepare("SELECT * FROM xbt_deny_from_hosts WHERE begin = ? OR end = ?");
                                $stmt->execute([$IP, $IP]);
                                
                                if ($stmt->rowCount() === 0) {
                                    $stmt = $pdo->prepare("INSERT INTO xbt_deny_from_hosts (begin, end) VALUES (?, ?)");
                                    $stmt->execute([$IP, $IP]);
                                }
                            }
                            
                            $Message = showAlertError($Language[3]);
                        } else {
                            $Message = showAlertError($Language[4]);
                        }
                    }
                }
            } catch (Exception $e) {
                $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
            }
        } else {
            $Message = showAlertError($Language[1]);
        }
    }
}

$showusergroups = "\r\n<select name=\"usergroup\">";
try {
    $stmt = $pdo->query("SELECT gid, title FROM usergroups WHERE isbanned = 'yes' ORDER BY disporder ASC");
    while ($UG = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($usergroup === $UG["gid"]) ? " selected=\"selected\"" : "";
        $showusergroups .= "\r\n\t<option value=\"" . intval($UG["gid"]) . "\"" . $selected . ">" . htmlspecialchars($UG["title"]) . "</option>";
    }
} catch (Exception $e) {
    error_log("Error loading usergroups: " . $e->getMessage());
}
$showusergroups .= "\r\n</select>";

$csrf_token = generateCSRFToken();

echo "<form action=\"";
echo htmlspecialchars($_SERVER["SCRIPT_NAME"]);
echo "?do=ban_user\" method=\"post\">\r\n";
echo "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n";
echo $Message;
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>";
echo htmlspecialchars($Language[8]);
echo "</b></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo htmlspecialchars($Language[5]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"username\" value=\"";
echo htmlspecialchars($username);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo htmlspecialchars($Language[6]);
echo "</td>\r\n\t\t<td class=\"alt2\">";
echo $showusergroups;
echo "</td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo htmlspecialchars($Language[7]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"reason\" value=\"";
echo htmlspecialchars($reason);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo htmlspecialchars($Language[11]);
echo "</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t<div class=\"smallfont\" style=\"white-space:nowrap\">\r\n\t\t\t\t<input type=\"radio\" name=\"banip\" id=\"rb_1_exact_2\" value=\"1\" tabindex=\"1\" />";
echo htmlspecialchars($Language[12]);
echo "\t\t\t\t<input type=\"radio\" name=\"banip\" id=\"rb_0_exact_2\" value=\"0\" tabindex=\"1\" checked=\"checked\" />";
echo htmlspecialchars($Language[13]);
echo "\t\t\t</div>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" align=\"right\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"";
echo htmlspecialchars($Language[8]);
echo "\" accesskey=\"s\" />\r\n\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"";
echo htmlspecialchars($Language[9]);
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
