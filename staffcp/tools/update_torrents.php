<?php
declare(strict_types=1);

@set_time_limit(0);
checkStaffAuthentication();

$pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
if (!$pdo) {
    die('Database connection not available');
}

$Language = file("languages/" . getStaffLanguage() . "/update_torrents.lang");
$Message = "";
@ini_set("upload_max_filesize", "10485760");
@ini_set("memory_limit", "20000M");

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }
    $perpage = intval($_POST["perpage"]);
    $wait = intval($_POST["wait"]);
} else {
    if (isset($_GET["page"])) {
        $perpage = intval($_GET["perpage"]);
        $wait = intval($_GET["wait"]);
    } else {
        $csrf_token = generateCSRFToken();
        
        echo "\r\n\t<form method=\"post\" action=\"index.php?do=update_torrents\">\r\n\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t" . htmlspecialchars($Language[2]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[8]) . "</td>\r\n\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"perpage\" value=\"20\" size=\"10\" /> " . htmlspecialchars($Language[12]) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . htmlspecialchars($Language[9]) . "</td>\r\n\t\t\t<td class=\"alt2\"><input type=\"text\" name=\"wait\" value=\"30\" size=\"10\" /> " . htmlspecialchars($Language[13]) . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[10]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[11]) . "\" /></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
    }
}

if (isset($perpage) && isset($wait)) {
    if ($perpage) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM torrents");
            $results = (int)$stmt->fetchColumn();
            
            $totalpages = ($results > 0) ? (int)ceil($results / $perpage) : 1;
            $pagenumber = (isset($_GET["page"]) && intval($_GET["page"]) > 0) ? intval($_GET["page"]) : 1;
            
            if ($pagenumber < 1) {
                $pagenumber = 1;
            } elseif ($pagenumber > $totalpages) {
                $pagenumber = $totalpages;
            }
            
            $limitlower = ($pagenumber - 1) * $perpage;
            $limitupper = $pagenumber * $perpage;
            
            if ($results < $limitupper) {
                $limitupper = $results;
                if ($results < $limitlower) {
                    $limitlower = $results - $perpage - 1;
                }
            }
            
            if ($limitlower < 0) {
                $limitlower = 0;
            }
            
            $nextpage = $pagenumber + 1;
        } catch (Exception $e) {
            die("Error: " . htmlspecialchars($e->getMessage()));
        }
    }
    
    try {
        $stmt = $pdo->prepare("SELECT content FROM ts_config WHERE configname = 'ANNOUNCE'");
        $stmt->execute();
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        $ANNOUNCE = unserialize($Result["content"]);
        
        $torrents = [];
        
        if ($ANNOUNCE["xbt_active"] === "yes") {
            $stmt = $pdo->prepare("SELECT fid, `left`, COUNT(*) AS c FROM xbt_files_users WHERE active = 1 GROUP BY fid, `left`");
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row["left"] === "0") {
                    $key = "seeders";
                } else {
                    $key = "leechers";
                }
                $torrents[$row["fid"]][$key] = $row["c"];
            }
        } else {
            $stmt = $pdo->query("SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row["seeder"] === "yes") {
                    $key = "seeders";
                } else {
                    $key = "leechers";
                }
                $torrents[$row["torrent"]][$key] = $row["c"];
            }
            
            $stmt = $pdo->query("SELECT torrentid, COUNT(*) as s FROM snatched WHERE finished = 'yes' GROUP BY torrentid");
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $torrents[$row["torrentid"]]["times_completed"] = $row["s"];
            }
        }
        
        $stmt = $pdo->query("SELECT torrent, COUNT(*) AS c FROM comments GROUP BY torrent");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $torrents[$row["torrent"]]["comments"] = $row["c"];
        }
        
        echo "\r\n\t<div id=\"sending\" name=\"sending\">\r\n\t\t\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\">" . htmlspecialchars($Language[2]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">";
        
        if ($perpage) {
            $stmt = $pdo->prepare("SELECT name, id, ts_external FROM torrents ORDER BY added DESC LIMIT ?, ?");
            $stmt->execute([$limitlower, $limitupper - $limitlower]);
        } else {
            $stmt = $pdo->query("SELECT name, id, ts_external FROM torrents ORDER BY added");
        }
        
        if ($stmt->rowCount() > 0) {
            while ($Torrent = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "\r\n\t\t\t\t\t<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t" . str_replace("{1}", htmlspecialchars($Torrent["name"]), $Language[3]) . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t";
                
                $updateStmt = $pdo->prepare("UPDATE torrents SET comments = ?" . 
                    ($Torrent["ts_external"] === "no" ? ", seeders = ?, leechers = ?" . 
                        ($ANNOUNCE["xbt_active"] === "yes" ? "" : ", times_completed = ?") : "") . 
                    " WHERE id = ?");
                
                $params = [
                    isset($torrents[$Torrent["id"]]["comments"]) ? $torrents[$Torrent["id"]]["comments"] : 0
                ];
                
                if ($Torrent["ts_external"] === "no") {
                    $params[] = isset($torrents[$Torrent["id"]]["seeders"]) ? $torrents[$Torrent["id"]]["seeders"] : 0;
                    $params[] = isset($torrents[$Torrent["id"]]["leechers"]) ? $torrents[$Torrent["id"]]["leechers"] : 0;
                    
                    if ($ANNOUNCE["xbt_active"] !== "yes") {
                        $params[] = isset($torrents[$Torrent["id"]]["times_completed"]) ? $torrents[$Torrent["id"]]["times_completed"] : 0;
                    }
                }
                
                $params[] = $Torrent["id"];
                
                $updateStmt->execute($params);
                
                if ($updateStmt->rowCount() > 0) {
                    echo "<td><font color=\"green\">" . htmlspecialchars($Language[6]) . "</font> " . htmlspecialchars($Language[14]) . " " . number_format(isset($torrents[$Torrent["id"]]["seeders"]) ? $torrents[$Torrent["id"]]["seeders"] : 0) . " / " . htmlspecialchars($Language[15]) . " " . number_format(isset($torrents[$Torrent["id"]]["leechers"]) ? $torrents[$Torrent["id"]]["leechers"] : 0) . " / " . htmlspecialchars($Language[16]) . " " . number_format(isset($torrents[$Torrent["id"]]["comments"]) ? $torrents[$Torrent["id"]]["comments"] : 0) . " " . ($ANNOUNCE["xbt_active"] === "yes" ? "" : "/ " . htmlspecialchars($Language[17]) . " " . number_format(isset($torrents[$Torrent["id"]]["times_completed"]) ? $torrents[$Torrent["id"]]["times_completed"] : 0)) . "</td>";
                } else {
                    echo "<td><font color=\"green\">" . htmlspecialchars($Language[7]) . "</font></td>";
                }
                
                echo "\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t";
            }
        }
        
        echo "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t" . (!$perpage || $totalpages < $nextpage ? "\r\n\t " . htmlspecialchars($Language[4]) : "\r\n\t " . htmlspecialchars($Language[5]) . " (" . intval($wait) . ")\r\n\t<script type=\"text/JavaScript\">\r\n\t\t<!--\r\n\t\t\tsetTimeout(\"location.href = 'index.php?do=update_torrents&page=" . intval($nextpage) . "&perpage=" . intval($perpage) . "&wait=" . intval($wait) . "';\", " . intval($wait) . "000);\r\n\t\t-->\r\n\t</script>\r\n\t") . "\r\n\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t</div>\r\n\t";
    } catch (Exception $e) {
        echo "<div class=\"alert\"><div>Error: " . htmlspecialchars($e->getMessage()) . "</div></div>";
    }
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
