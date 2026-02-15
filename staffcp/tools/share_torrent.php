<?php
declare(strict_types=1);

checkStaffAuthentication();

$Language = file("languages/" . getStaffLanguage() . "/share_torrent.lang");
if ($Language === false) {
    die('Failed to load language file');
}

$Message = "";
$Found = "";
$name = "";
$tracker = "http://";

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }
    
    $name = trim($_POST["name"]);
    $tracker = trim($_POST["tracker"]);
    
    if ($name && $tracker && $tracker !== "http://") {
        try {
            $pdo = $GLOBALS["DatabaseConnect"];
            $stmt = $pdo->prepare("SELECT t.id, t.name, t.added, t.size, t.leechers, t.seeders, u.username, g.namestyle, c.name as catname FROM torrents t LEFT JOIN users u ON (t.owner = u.id) LEFT JOIN usergroups g ON (u.usergroup = g.gid) LEFT JOIN categories c ON (t.category = c.id) WHERE MATCH (t.name) AGAINST (? IN BOOLEAN MODE)");
            $stmt->execute([$name . '*']);
            
            if ($stmt->rowCount() > 0) {
                $Found = "
\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t\t\t\t<tr>\t
\t\t\t\t\t<td class=\"tcat\" colspan=\"8\" align=\"center\">
\t\t\t\t\t\t" . htmlspecialchars($Language[16]) . "
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"alt2\" align=\"center\">
\t\t\t\t\t\t" . htmlspecialchars($Language[15]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt2\">
\t\t\t\t\t\t" . htmlspecialchars($Language[5]) . "
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
\t\t\t\t\t<td class=\"alt2\">
\t\t\t\t\t\t" . htmlspecialchars($Language[14]) . "
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t";
                
                while ($Torrent = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $Found .= "
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"alt1\" align=\"center\">
\t\t\t\t\t\t<a href=\"../download.php?id=" . urlencode((string)$Torrent["id"]) . "&amp;fromadminpanel=true\"><img src=\"images/download.png\" border=\"0\" alt=\"" . htmlspecialchars(trim($Language[17])) . "\" title=\"" . htmlspecialchars(trim($Language[17])) . "\" /></a>
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\">
\t\t\t\t\t\t" . htmlspecialchars($Torrent["name"]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\">
\t\t\t\t\t\t" . htmlspecialchars($Torrent["catname"]) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\">
\t\t\t\t\t\t" . htmlspecialchars(formatBytes((float)$Torrent["size"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\">
\t\t\t\t\t\t" . htmlspecialchars(formatTimestamp($Torrent["added"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\">
\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . urlencode($Torrent["username"]) . "\">" . applyUsernameStyle(htmlspecialchars($Torrent["username"]), htmlspecialchars($Torrent["namestyle"])) . "</a>
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\">
\t\t\t\t\t\t" . htmlspecialchars(number_format((int)$Torrent["seeders"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td class=\"alt1\">
\t\t\t\t\t\t" . htmlspecialchars(number_format((int)$Torrent["leechers"])) . "
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t\t";
                }
                
                $Found .= "
\t\t\t</table>
\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t\t\t\t<tr>\t
\t\t\t\t\t<td class=\"tcat\" align=\"center\">
\t\t\t\t\t\t" . htmlspecialchars($Language[6]) . "
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"alt1\">
\t\t\t\t\t\t<iframe src=\"" . htmlspecialchars($tracker) . "\" width=\"100%\" height=\"300\"></iframe>
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t</table>";
            } else {
                $Message = showAlertError(htmlspecialchars($Language[4]));
            }
        } catch (PDOException $e) {
            error_log("Database error in share_torrent.php: " . $e->getMessage());
            $Message = showAlertError("Database error occurred");
        }
    } else {
        $Message = showAlertError(htmlspecialchars($Language[3]));
    }
}

$SelectBoxTorrents = "";
try {
    $pdo = $GLOBALS["DatabaseConnect"];
    $stmt = $pdo->query("SELECT name FROM torrents ORDER BY added DESC, name ASC LIMIT 50");
    
    if ($stmt->rowCount() > 0) {
        $SelectBoxTorrents = "
\t<select name=\"torrentname\" onchange=\"TSUpdateFieldTorrent(this);\">
\t<option value=\"\"" . ($name === "" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[18]) . "</option>";
        
        while ($Torrents = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Torrents["name"] = htmlspecialchars($Torrents["name"]);
            $SelectBoxTorrents .= "
\t\t<option value=\"" . $Torrents["name"] . "\"" . ($name === $Torrents["name"] ? " selected=\"selected\"" : "") . ">" . $Torrents["name"] . "</option>";
        }
        $SelectBoxTorrents .= "
\t</select>";
    }
} catch (PDOException $e) {
    error_log("Database error loading torrents: " . $e->getMessage());
}

$AutoSelectBox = "
<select name=\"remotetracker\" onchange=\"TSUpdateField(this);\">
\t<option value=\"http://\"" . ($tracker === "http://" ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[18]) . "</option>
\t<option value=\"http://www.mininova.org/upload\"" . ($tracker === "http://www.mininova.org/upload" ? " selected=\"selected\"" : "") . ">MiniNova</option>
\t<option value=\"http://www.demonoid.com/torrent_upload.php5\"" . ($tracker === "http://www.demonoid.com/torrent_upload.php5" ? " selected=\"selected\"" : "") . ">Demonoid</option>
\t<option value=\"http://www.thepiratebay.org/upload\"" . ($tracker === "http://www.thepiratebay.org/upload" ? " selected=\"selected\"" : "") . ">ThePirateBay</option>
\t<option value=\"http://www.meganova.org/upload.html\"" . ($tracker === "http://www.meganova.org/upload.html" ? " selected=\"selected\"" : "") . ">MegaNova</option>
</select>
";

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "<script type=\"text/javascript\">
\tfunction TSUpdateField(Where)
\t{
\t\tTSGetID(\"tracker\").value = Where.value;
\t}
\tfunction TSUpdateFieldTorrent(Where)
\t{
\t\tTSGetID(\"name\").value = Where.value;
\t}
</script>
<form action=\"" . htmlspecialchars($_SERVER["SCRIPT_NAME"]) . "?do=share_torrent\" method=\"post\">
<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($_SESSION['csrf_token']) . "\">
";
echo $Message . $Found;
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t<tr>
\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>" . htmlspecialchars($Language[2]) . "</b></td>
\t</tr>\t
\t<tr valign=\"top\">
\t\t<td class=\"alt1\"  align=\"right\">" . htmlspecialchars($Language[5]) . "</td>
\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"name\" id=\"name\" value=\"" . htmlspecialchars($name) . "\" size=\"45\" dir=\"ltr\" tabindex=\"1\" /> " . $SelectBoxTorrents . "</td>
\t</tr>
\t<tr valign=\"top\">
\t\t<td class=\"alt2\" align=\"right\">" . htmlspecialchars($Language[6]) . "</td>
\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" id=\"tracker\" name=\"tracker\" value=\"" . htmlspecialchars($tracker) . "\" size=\"45\" dir=\"ltr\" tabindex=\"1\" /> " . $AutoSelectBox . "</td>
\t</tr>
\t<tr>
\t\t<td class=\"tcat2\"></td>
\t\t<td class=\"tcat2\">\t
\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[7]) . "\" accesskey=\"s\" />
\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"" . htmlspecialchars($Language[8]) . "\" accesskey=\"r\" />
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
