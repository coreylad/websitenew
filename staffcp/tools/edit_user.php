<?php
declare(strict_types=1);

checkStaffAuthentication();

$Language = file("languages/" . getStaffLanguage() . "/edit_user.lang");
if ($Language === false) {
    die('Failed to load language file');
}

$Message = "";
$username = "";
$userid = "";

try {
    $pdo = $GLOBALS["DatabaseConnect"];
    $stmt = $pdo->prepare("SELECT u.id, g.* FROM users u INNER JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.id = ? LIMIT 1");
    $stmt->execute([$_SESSION["ADMIN_ID"]]);
    
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        die('Admin user not found');
    }
} catch (PDOException $e) {
    error_log("Database error in edit_user.php: " . $e->getMessage());
    die('Database error occurred');
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

function function_104(string $offset = "all"): mixed
{
    $settingName = [
        "4294967284" => "timezone_gmt_minus_1200", "4294967285" => "timezone_gmt_minus_1100",
        "4294967286" => "timezone_gmt_minus_1000", "4294967287" => "timezone_gmt_minus_0900",
        "4294967288" => "timezone_gmt_minus_0800", "4294967289" => "timezone_gmt_minus_0700",
        "4294967290" => "timezone_gmt_minus_0600", "4294967291" => "timezone_gmt_minus_0500",
        "-4.5" => "timezone_gmt_minus_0430", "4294967292" => "timezone_gmt_minus_0400",
        "-3.5" => "timezone_gmt_minus_0330", "4294967293" => "timezone_gmt_minus_0300",
        "4294967294" => "timezone_gmt_minus_0200", "4294967295" => "timezone_gmt_minus_0100",
        "0" => "timezone_gmt_plus_0000", "1" => "timezone_gmt_plus_0100",
        "2" => "timezone_gmt_plus_0200", "3" => "timezone_gmt_plus_0300",
        "3.5" => "timezone_gmt_plus_0330", "4" => "timezone_gmt_plus_0400",
        "4.5" => "timezone_gmt_plus_0430", "5" => "timezone_gmt_plus_0500",
        "5.5" => "timezone_gmt_plus_0530", "5.75" => "timezone_gmt_plus_0545",
        "6" => "timezone_gmt_plus_0600", "6.5" => "timezone_gmt_plus_0630",
        "7" => "timezone_gmt_plus_0700", "8" => "timezone_gmt_plus_0800",
        "9" => "timezone_gmt_plus_0900", "9.5" => "timezone_gmt_plus_0930",
        "10" => "timezone_gmt_plus_1000", "11" => "timezone_gmt_plus_1100",
        "12" => "timezone_gmt_plus_1200"
    ];
    
    return $offset === "all" ? $settingName : $settingName[$offset];
}

function function_105(float $tzoffset = 0, int $autodst = 0, int $dst = 0): string
{
    $Language = file("languages/" . getStaffLanguage() . "/timezone.lang");
    if ($Language === false) {
        return "";
    }
    
    $settingValue = "";
    $count = 0;
    
    foreach (function_104() as $settingType => $settingDescription) {
        $settingValue .= "<option value=\"" . htmlspecialchars((string)$settingType) . "\"" . ($tzoffset == $settingType ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($Language[$count]) . "</option>";
        $count++;
    }
    
    $settingCategory = [];
    if ($autodst) {
        $settingCategory[2] = " selected=\"selected\"";
    } else {
        if ($dst) {
            $settingCategory[1] = " selected=\"selected\"";
        } else {
            $settingCategory[0] = " selected=\"selected\"";
        }
    }
    
    return "
\t<div>" . htmlspecialchars($Language[34]) . "</div>
\t<select name=\"tzoffset\" id=\"sel_tzoffset\">
\t\t" . $settingValue . "
\t</select>
\t";
}

function function_106(string $options, string $field, int $number = 0): bool
{
    if (!($options = strtoupper($options)) || !($field = strtolower($field))) {
        return false;
    }
    
    $array = [
        "parked" => "A1", "invisible" => "B1", "commentpm" => "C1",
        "avatars" => "D1", "showoffensivetorrents" => "E1", "popup" => "F1",
        "leftmenu" => "G1", "signatures" => "H1", "privacy" => "I" . $number,
        "acceptpms" => "K" . $number, "gender" => "L" . $number,
        "visitormsg" => "M" . $number, "autodst" => "N1", "dst" => "O1",
        "quickmenu" => "P1"
    ];
    
    return isset($array[$field]) && preg_match("#" . $array[$field] . "#is", $options) ? true : false;
}

function generateSecret(int $length = 20): string
{
    $characters = [
        "a", "A", "b", "B", "c", "C", "d", "D", "e", "E",
        "f", "F", "g", "G", "h", "H", "i", "I", "j", "J",
        "k", "K", "l", "L", "m", "M", "n", "N", "o", "O",
        "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T",
        "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y",
        "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9"
    ];
    
    $secretString = "";
    for ($i = 1; $i <= $length; $i++) {
        $ch = rand(0, count($characters) - 1);
        $secretString .= $characters[$ch];
    }
    
    return $secretString;
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

function checkEditUserPermission(string $Option): bool
{
    global $currentUserPerms;
    
    $configValue = [
        "changeusername" => "0", "changepassword" => "1", "changeemail" => "2",
        "changeusergroup" => "3", "managedonationstatus" => "4", "managetimeoptions" => "5",
        "manageaccountdetails" => "6", "manageaccountpermissions" => "7",
        "manageaccountwarningdetails" => "8", "manageaccounthistory" => "9",
        "managesupportoptions" => "10", "managecontactdetails" => "11"
    ];
    
    $settingHtml = isset($configValue[$Option]) ? $configValue[$Option] : 0;
    return isset($currentUserPerms["edituserperms"][$settingHtml]) && $currentUserPerms["edituserperms"][$settingHtml] === "1";
}

function function_108(string $str): string
{
    $str = str_replace("&amp;", "&", $str);
    $str = str_replace("&apos", "'", $str);
    $str = str_replace("&#039;", "'", $str);
    $str = str_replace("&quot;", "\"", $str);
    $str = str_replace("&lt;", "<", $str);
    $str = str_replace("&gt;", ">", $str);
    return $str;
}

function showAlertMessage(string $message = ""): string
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>
