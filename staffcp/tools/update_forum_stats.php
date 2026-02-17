<?php
declare(strict_types=1);

@set_time_limit(0);
checkStaffAuthentication();

$Language = file("languages/" . getStaffLanguage() . "/update_forum_stats.lang");
if ($Language === false) {
    die('Failed to load language file');
}

$Message = "";
$TotalQueryCount = 0;
define("TSF_PREFIX", "tsf_");

try {
    $pdo = $GLOBALS["DatabaseConnect"];
    
    echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"mainTable\"><tr><td class=\"alt1\">";
    echo htmlspecialchars($Language[3]);
    
    $stmt = $pdo->prepare("DELETE FROM " . TSF_PREFIX . "threads WHERE tid = 0 OR fid = 0");
    $stmt->execute();
    $TotalQueryCount++;
    
    $stmt = $pdo->prepare("DELETE FROM " . TSF_PREFIX . "posts WHERE pid = 0 OR tid = 0 OR fid = 0");
    $stmt->execute();
    $TotalQueryCount++;
    
    echo htmlspecialchars($Language[2]);
    echo htmlspecialchars($Language[4]);
    
    $TotalReplies = [];
    $stmt = $pdo->query("SELECT tid FROM " . TSF_PREFIX . "posts");
    $TotalQueryCount++;
    
    while ($Posts = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($Posts["tid"]) && $Posts["tid"]) {
            if (isset($TotalReplies[$Posts["tid"]])) {
                $TotalReplies[$Posts["tid"]]++;
            } else {
                $TotalReplies[$Posts["tid"]] = 1;
            }
        }
    }
    
    $updateStmt = $pdo->prepare("UPDATE " . TSF_PREFIX . "threads SET replies = ? WHERE tid = ?");
    foreach ($TotalReplies as $Tid => $ReplyCount) {
        $ReplyCount = $ReplyCount - 1;
        $updateStmt->execute([$ReplyCount, $Tid]);
        $TotalQueryCount++;
    }
    
    echo htmlspecialchars($Language[2]);
    echo htmlspecialchars($Language[5]);
    
    $TotalThreads = [];
    $stmt = $pdo->query("SELECT fid FROM " . TSF_PREFIX . "threads");
    $TotalQueryCount++;
    
    while ($Threads = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($Threads["fid"]) && $Threads["fid"]) {
            if (isset($TotalThreads[$Threads["fid"]])) {
                $TotalThreads[$Threads["fid"]]++;
            } else {
                $TotalThreads[$Threads["fid"]] = 1;
            }
        }
    }
    
    $updateStmt = $pdo->prepare("UPDATE " . TSF_PREFIX . "forums SET threads = ? WHERE fid = ?");
    foreach ($TotalThreads as $Fid => $ThreadCount) {
        $updateStmt->execute([$ThreadCount, $Fid]);
        $TotalQueryCount++;
    }
    
    echo htmlspecialchars($Language[2]);
    echo htmlspecialchars($Language[6]);
    
    $TotalPosts = [];
    $stmt = $pdo->query("SELECT fid FROM " . TSF_PREFIX . "posts");
    $TotalQueryCount++;
    
    while ($Posts = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($Posts["fid"]) && $Posts["fid"]) {
            if (isset($TotalPosts[$Posts["fid"]])) {
                $TotalPosts[$Posts["fid"]]++;
            } else {
                $TotalPosts[$Posts["fid"]] = 1;
            }
        }
    }
    
    $updateStmt = $pdo->prepare("UPDATE " . TSF_PREFIX . "forums SET posts = ? WHERE fid = ?");
    foreach ($TotalPosts as $Fid => $PostCount) {
        $updateStmt->execute([$PostCount, $Fid]);
        $TotalQueryCount++;
    }
    
    echo htmlspecialchars($Language[2]);
    echo htmlspecialchars($Language[7]) . " " . htmlspecialchars((string)$TotalQueryCount);
    echo "</td></tr></table>";
    
} catch (PDOException $e) {
    error_log("Database error in update_forum_stats.php: " . $e->getMessage());
    echo "<div class=\"alert\"><div>Database error occurred</div></div>";
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

?>
