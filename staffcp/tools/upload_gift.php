<?php
declare(strict_types=1);

checkStaffAuthentication();

$Language = file("languages/" . getStaffLanguage() . "/upload_gift.lang");
$Message = "";
$amount = "0";
$type = "GB";
$usergroups = [];
$username = "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    try {
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            throw new Exception($Language[9] ?? "Invalid CSRF token");
        }

        $amount = 0 + ($_POST["amount"] ?? 0);
        $usergroups = $_POST["usergroups"] ?? [];
        $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
        $type = $_POST["type"] ?? "GB";

        if ($type === "MB") {
            $uamount = $amount * 1024 * 1024;
        } else {
            $uamount = $amount * 1024 * 1024 * 1024;
        }

        if ($amount && $uamount && ((is_array($usergroups) && count($usergroups)) || $username) && $type) {
            $pdo = getPDOConnection();
            
            if ($username) {
                if (preg_match("@,@", $username)) {
                    $usernames = explode(",", $username);
                    foreach ($usernames as $user) {
                        $user = trim($user);
                        $stmt = $pdo->prepare("SELECT usergroup FROM users WHERE username = :username");
                        $stmt->execute(['username' => $user]);
                        
                        if ($stmt->rowCount() > 0) {
                            $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . 
                                str_replace(["{1}", "{2}", "{3}"], 
                                    [$user, formatBytes($uamount), htmlspecialchars($_SESSION["ADMIN_USERNAME"] ?? '')], 
                                    $Language[13]) . "\\n");
                            
                            $stmt = $pdo->prepare("UPDATE users SET uploaded = uploaded + :amount, 
                                modcomment = IF(ISNULL(modcomment), :comment, CONCAT(:comment2, modcomment)) 
                                WHERE username = :username");
                            $stmt->execute([
                                'amount' => $uamount,
                                'comment' => $newModComment,
                                'comment2' => $newModComment,
                                'username' => $user
                            ]);
                        }
                    }
                    $SysMsg = str_replace(["{1}", "{2}", "{3}"], 
                        [$username, formatBytes($uamount), htmlspecialchars($_SESSION["ADMIN_USERNAME"] ?? '')], 
                        $Language[13]);
                } else {
                    $stmt = $pdo->prepare("SELECT usergroup FROM users WHERE username = :username");
                    $stmt->execute(['username' => $username]);
                    
                    if ($stmt->rowCount() > 0) {
                        $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . 
                            str_replace(["{1}", "{2}", "{3}"], 
                                [$username, formatBytes($uamount), htmlspecialchars($_SESSION["ADMIN_USERNAME"] ?? '')], 
                                $Language[13]) . "\\n");
                        
                        $stmt = $pdo->prepare("UPDATE users SET uploaded = uploaded + :amount, 
                            modcomment = IF(ISNULL(modcomment), :comment, CONCAT(:comment2, modcomment)) 
                            WHERE username = :username");
                        $stmt->execute([
                            'amount' => $uamount,
                            'comment' => $newModComment,
                            'comment2' => $newModComment,
                            'username' => $username
                        ]);
                        $SysMsg = str_replace(["{1}", "{2}", "{3}"], 
                            [$username, formatBytes($uamount), htmlspecialchars($_SESSION["ADMIN_USERNAME"] ?? '')], 
                            $Language[13]);
                    } else {
                        $Message = showAlertError($Language[12]);
                    }
                }
                
                if (!$Message && isset($SysMsg)) {
                    logStaffAction($SysMsg);
                    $Message = showAlertError($SysMsg);
                }
            } else {
                $work = implode(",", array_map('intval', $usergroups));
                $SysMsg = str_replace(["{1}", "{2}", "{3}"], 
                    [$work, formatBytes($uamount), htmlspecialchars($_SESSION["ADMIN_USERNAME"] ?? '')], 
                    $Language[3]);
                $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . $SysMsg . "\\n");
                
                $placeholders = implode(',', array_fill(0, count($usergroups), '?'));
                $stmt = $pdo->prepare("UPDATE users SET uploaded = uploaded + ?, 
                    modcomment = IF(ISNULL(modcomment), ?, CONCAT(?, modcomment)) 
                    WHERE usergroup IN (0, $placeholders)");
                $params = array_merge([$uamount, $newModComment, $newModComment], array_map('intval', $usergroups));
                $stmt->execute($params);
                
                logStaffAction($SysMsg);
                $Message = showAlertError($SysMsg);
            }
        } else {
            $Message = showAlertError($Language[10]);
        }
    } catch (Exception $e) {
        error_log("Upload gift error: " . $e->getMessage());
        $Message = showAlertError($Language[10] ?? "An error occurred");
    }
}

try {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
        FROM users u 
        LEFT JOIN usergroups g ON (u.usergroup = g.gid) 
        WHERE u.id = :admin_id 
        LIMIT 1");
    $stmt->execute(['admin_id' => $_SESSION["ADMIN_ID"] ?? 0]);
    $LoggedAdminDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    $count = 0;
    $showusergroups = "\n<table>\n\t<tr>\t";
    
    $stmt = $pdo->query("SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle 
        FROM usergroups ORDER BY disporder ASC");
    
    while ($UG = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!(($UG["cansettingspanel"] === "yes" && $LoggedAdminDetails["cansettingspanel"] !== "yes") ||
              ($UG["canstaffpanel"] === "yes" && $LoggedAdminDetails["canstaffpanel"] !== "yes") ||
              ($UG["issupermod"] === "yes" && $LoggedAdminDetails["issupermod"] !== "yes"))) {
            
            if ($count && $count % 8 === 0) {
                $showusergroups .= "</tr><tr>";
            }
            
            $checked = (is_array($usergroups) && count($usergroups) && in_array($UG["gid"], $usergroups)) ? ' checked="checked"' : '';
            $showusergroups .= '<td><input type="checkbox" name="usergroups[]" value="' . 
                htmlspecialchars((string)$UG["gid"]) . '"' . $checked . ' /></td><td>' . 
                htmlspecialchars(str_replace("{username}", $UG["title"], $UG["namestyle"])) . '</td>';
            $count++;
        }
    }
    $showusergroups .= "</tr></table>";

    $csrf_token = generateCSRFToken();
    
    echo $Message . '
<form method="post" action="index.php?do=upload_gift">
<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token) . '" />
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" colspan="2" align="center">
            ' . htmlspecialchars($Language[2]) . '
        </td>
    </tr>
    <tr>
        <td class="alt1" align="right">' . htmlspecialchars($Language[4]) . '</td>
        <td class="alt1"><input type="text" name="amount" value="' . htmlspecialchars($amount) . '" size="10" />
            <select name="type">
                <option value="GB"' . ($type === "GB" ? ' selected="selected"' : '') . '>GB</option>
                <option value="MB"' . ($type === "MB" ? ' selected="selected"' : '') . '>MB</option>
            </select></td>
    </tr>
    <tr>
        <td class="alt2" align="right">' . htmlspecialchars($Language[11]) . '</td>
        <td class="alt2"><input type="text" name="username" value="' . htmlspecialchars($username) . '" size="45" /> 
            <small>' . htmlspecialchars($Language[14]) . '</small></td>
    </tr>
    <tr>
        <td class="alt1" valign="top" align="right">' . htmlspecialchars($Language[6]) . '</td>
        <td class="alt1">' . $showusergroups . '</td>
    </tr>
    <tr>
        <td class="tcat2" align="right"></td>
        <td class="tcat2"><input type="submit" value="' . htmlspecialchars($Language[7]) . '" /> 
            <input type="reset" value="' . htmlspecialchars($Language[8]) . '" /></td>
    </tr>
</table>
</form>';
} catch (Exception $e) {
    error_log("Upload gift display error: " . $e->getMessage());
    echo showAlertError("An error occurred displaying the form");
}

function getPDOConnection(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = $GLOBALS["DatabaseConnect"];
    }
    return $pdo;
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

function getStaffLanguage(): string
{
    if (isset($_COOKIE["staffcplanguage"]) && 
        is_dir("languages/" . $_COOKIE["staffcplanguage"]) && 
        is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
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
        echo '<script type="text/javascript">window.location.href = "' . htmlspecialchars($url) . '";</script>
        <noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '" /></noscript>';
    }
    exit;
}

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . htmlspecialchars($Error) . "</div></div>";
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
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (:uid, :date, :log)");
        $stmt->execute([
            'uid' => $_SESSION["ADMIN_ID"] ?? 0,
            'date' => time(),
            'log' => $log
        ]);
    } catch (Exception $e) {
        error_log("Failed to log staff action: " . $e->getMessage());
    }
}
