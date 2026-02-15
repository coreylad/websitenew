<?php
declare(strict_types=1);

checkStaffAuthentication();

$Language = file("languages/" . getStaffLanguage() . "/delete_torrent.lang");
if ($Language === false) {
    die('Failed to load language file');
}

$Message = "";
$tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : (isset($_POST["tid"]) ? intval($_POST["tid"]) : 0);
$reason = "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST" && $tid) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $reason = $_POST["reason"] ? trim($_POST["reason"]) : "";
    
    if ($reason) {
        try {
            $pdo = $GLOBALS["DatabaseConnect"];
            $stmt = $pdo->prepare("SELECT name, owner FROM torrents WHERE id = ?");
            $stmt->execute([$tid]);
            
            if ($Torrent = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $SysMsg = str_replace(["{1}", "{2}"], [htmlspecialchars($Torrent["name"]), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[7]);
                function_151($tid);
                logStaffAction($SysMsg);
                sendPrivateMessage((int)$Torrent["owner"], $SysMsg . "\r\n\t\t\t" . trim($Language[8]) . ": " . $reason, $Language[2]);
                $Message = showAlertError($SysMsg);
                $tid = 0;
                $reason = "";
            } else {
                $Message = showAlertError(htmlspecialchars($Language[6]));
            }
        } catch (PDOException $e) {
            error_log("Database error in delete_torrent.php: " . $e->getMessage());
            $Message = showAlertError("Database error occurred");
        }
    } else {
        $Message = showAlertError(htmlspecialchars($Language[9]));
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "\t\t\t\t\r\n\r\n" . $Message . "
<form method=\"post\" action=\"index.php?do=delete_torrent\">
<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($_SESSION['csrf_token']) . "\">
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t<tr>
\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">
\t\t\t" . htmlspecialchars($Language[2]) . "
\t\t</td>
\t</tr>
\t<tr>
\t\t<td class=\"alt1\" align=\"right\">" . htmlspecialchars($Language[3]) . "</td>
\t\t<td class=\"alt1\"><input type=\"text\" name=\"tid\" value=\"" . htmlspecialchars((string)$tid) . "\" size=\"10\" /></td>
\t</tr>
\t<tr>
\t\t<td class=\"alt2\" align=\"right\">" . htmlspecialchars($Language[8]) . "</td>
\t\t<td class=\"alt2\"><textarea name=\"reason\" rows=\"2\" cols=\"66\">" . htmlspecialchars($reason) . "</textarea></td>
\t</tr>
\t<tr>
\t\t<td class=\"tcat2\"></td>
\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[4]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[5]) . "\" /></td>
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

function function_151(int $id): void
{
    try {
        $pdo = $GLOBALS["DatabaseConnect"];
        $stmt = $pdo->prepare("SELECT content FROM ts_config WHERE configname = 'MAIN'");
        $stmt->execute();
        $configRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $configData = unserialize($configRow["content"]);
        $fileHandle = "../" . $configData["torrent_dir"];
        
        if (!$id) {
            return;
        }
        
        $file = $fileHandle . "/" . $id . ".torrent";
        if (@file_exists($file)) {
            @unlink($file);
        }
        
        $fileContent = ["gif", "jpg", "png"];
        foreach ($fileContent as $smileyFileExt) {
            if (@file_exists($fileHandle . "/images/" . $id . "." . $smileyFileExt)) {
                @unlink($fileHandle . "/images/" . $id . "." . $smileyFileExt);
            }
        }
        
        $stmt = $pdo->prepare("SELECT t_link FROM torrents WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($actionParam = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fileName = $actionParam["t_link"];
            $resultSet = "#https://www.imdb.com/title/(.*)/#U";
            preg_match($resultSet, $fileName, $fileSize);
            $fileSize = $fileSize[1] ?? '';
            
            foreach ($fileContent as $smileyFileExt) {
                if (@file_exists($fileHandle . "/images/" . $fileSize . "." . $smileyFileExt)) {
                    @unlink($fileHandle . "/images/" . $fileSize . "." . $smileyFileExt);
                }
            }
            
            for ($i = 0; $i <= 10; $i++) {
                foreach ($fileContent as $smileyFileExt) {
                    if (@file_exists($fileHandle . "/images/" . $fileSize . "_photo" . $i . "." . $smileyFileExt)) {
                        @unlink($fileHandle . "/images/" . $fileSize . "_photo" . $i . "." . $smileyFileExt);
                    }
                }
            }
        }
        
        $pdo->prepare("DELETE FROM peers WHERE torrent = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM xbt_files_users WHERE fid = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM comments WHERE torrent = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM bookmarks WHERE torrentid = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM snatched WHERE torrentid = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM torrents WHERE id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM ts_torrents_details WHERE tid = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM ts_thanks WHERE tid = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM ts_nfo WHERE id = ?")->execute([$id]);
        
    } catch (PDOException $e) {
        error_log("Error in function_151: " . $e->getMessage());
    }
}

function sendPrivateMessage(int $receiver = 0, string $msg = "", string $subject = "", int $sender = 0, string $saved = "no", string $location = "1", string $unread = "yes"): void
{
    if ($sender === 0 || !$receiver || empty($msg)) {
        return;
    }
    
    try {
        $pdo = $GLOBALS["DatabaseConnect"];
        $stmt = $pdo->prepare("INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([$sender, $receiver, $subject, $msg, $unread, $saved, $location]);
        
        $updateStmt = $pdo->prepare("UPDATE users SET pmunread = pmunread + 1 WHERE id = ?");
        $updateStmt->execute([$receiver]);
    } catch (PDOException $e) {
        error_log("Error sending private message: " . $e->getMessage());
    }
}

?>
