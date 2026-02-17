<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthenticationModern();

// Load language
$Language = loadStaffLanguage('search_user');
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : "";
$usergroup = "";
$email = isset($_GET["email"]) ? trim($_GET["email"]) : "";
$ip = isset($_GET["ip"]) ? trim($_GET["ip"]) : "";
$joindateafter = "";
$joindatebefore = "";
$lastaccessafter = "";
$lastaccessbefore = "";
$bdayafter = "";
$bdaybefore = "";
$postgreater = "";
$postless = "";
$warnsgreater = "";
$warnless = "";
$ulgreater = "";
$ulless = "";
$dlgreater = "";
$dlless = "";
$ratiogreater = "";
$ratioless = "";
$idgreater = "";
$idless = "";
$modcomment = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    // Validate form token for CSRF protection
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[5] ?? 'Invalid form token. Please try again.');
    } else {
    $username = trim($_POST["username"]);
    $usergroup = intval($_POST["usergroup"]);
    $email = trim($_POST["email"]);
    $ip = trim($_POST["ip"]);
    $joindateafter = trim($_POST["joindateafter"]);
    $joindatebefore = trim($_POST["joindatebefore"]);
    $lastaccessafter = trim($_POST["lastaccessafter"]);
    $lastaccessbefore = trim($_POST["lastaccessbefore"]);
    $bdayafter = trim($_POST["bdayafter"]);
    $bdaybefore = trim($_POST["bdaybefore"]);
    $postgreater = intval($_POST["postgreater"]);
    $postless = intval($_POST["postless"]);
    $warnsgreater = intval($_POST["warnsgreater"]);
    $warnless = intval($_POST["warnless"]);
    $ulgreater = intval($_POST["ulgreater"]);
    $ulless = intval($_POST["ulless"]);
    $dlgreater = intval($_POST["dlgreater"]);
    $dlless = intval($_POST["dlless"]);
    $ratiogreater = trim($_POST["ratiogreater"]);
    $ratioless = trim($_POST["ratioless"]);
    $idgreater = intval($_POST["idgreater"]);
    $idless = intval($_POST["idless"]);
    $modcomment = trim($_POST["modcomment"]);
    $Queries = [];
    $Params = [];
    
    if ($joindateafter) {
        $Queries[] = "UNIX_TIMESTAMP(added) > ?";
        $Params[] = strtotime($joindateafter);
    }
    if ($joindatebefore) {
        $Queries[] = "UNIX_TIMESTAMP(added) < ?";
        $Params[] = strtotime($joindatebefore);
    }
    if ($lastaccessafter) {
        $Queries[] = "UNIX_TIMESTAMP(last_access) > ?";
        $Params[] = strtotime($lastaccessafter);
    }
    if ($lastaccessbefore) {
        $Queries[] = "UNIX_TIMESTAMP(last_access) < ?";
        $Params[] = strtotime($lastaccessbefore);
    }
    if ($bdayafter) {
        $Queries[] = "UNIX_TIMESTAMP(birthday) > ?";
        $Params[] = $bdayafter;
    }
    if ($bdaybefore) {
        $Queries[] = "UNIX_TIMESTAMP(birthday) < ?";
        $Params[] = $bdaybefore;
    }
    if ($postgreater) {
        $Queries[] = "totalposts >= ?";
        $Params[] = $postgreater;
    }
    if ($postless) {
        $Queries[] = "totalposts < ?";
        $Params[] = $postless;
    }
    if ($warnsgreater) {
        $Queries[] = "timeswarned >= ?";
        $Params[] = $warnsgreater;
    }
    if ($warnless) {
        $Queries[] = "timeswarned < ?";
        $Params[] = $warnless;
    }
    if ($ulgreater) {
        $Queries[] = "uploaded >= ?";
        $Params[] = $ulgreater * 1024 * 1024 * 1024;
    }
    if ($ulless) {
        $Queries[] = "uploaded < ?";
        $Params[] = $ulless * 1024 * 1024 * 1024;
    }
    if ($dlgreater) {
        $Queries[] = "downloaded >= ?";
        $Params[] = $dlgreater * 1024 * 1024 * 1024;
    }
    if ($dlless) {
        $Queries[] = "downloaded < ?";
        $Params[] = $dlless * 1024 * 1024 * 1024;
    }
    if ($ratiogreater) {
        $Queries[] = "uploaded / downloaded >= ?";
        $Params[] = $ratiogreater;
    }
    if ($ratioless) {
        $Queries[] = "uploaded / downloaded < ?";
        $Params[] = $ratioless;
    }
    if ($idgreater) {
        $Queries[] = "id >= ?";
        $Params[] = $idgreater;
    }
    if ($idless) {
        $Queries[] = "id < ?";
        $Params[] = $idless;
    }
    if ($modcomment) {
        $Queries[] = "`modcomment` LIKE ?";
        $Params[] = '%' . $modcomment . '%';
    }
    if ($username) {
        if ($_POST["exact"] == "1") {
            $Queries[] = "`username` = ?";
            $Params[] = $username;
        } else {
            $Queries[] = "`username` LIKE ?";
            $Params[] = '%' . $username . '%';
        }
    }
    if ($usergroup) {
        $Queries[] = "`usergroup` = ?";
        $Params[] = $usergroup;
    }
    if ($email) {
        $Queries[] = "`email` = ?";
        $Params[] = $email;
    }
    if ($ip) {
        $Queries[] = "`ip` = ?";
        $Params[] = $ip;
    }
    
    try {
        $sql = "SELECT id, username, last_access, email, ip, uploaded, downloaded, invites, seedbonus, g.title, g.namestyle FROM users LEFT JOIN usergroups g ON (users.usergroup = g.gid)" . (count($Queries) ? " WHERE " . implode(" AND ", $Queries) : "");
        $result = $TSDatabase->query($sql, $Params);
        
        if (!$result || $result->rowCount() == 0) {
            $Message = showAlertErrorModern($Language[4]);
        } else {
            $Found = "";
            while ($User = $result->fetch(PDO::FETCH_ASSOC)) {
                $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . escape_attr($User["username"]) . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($User["title"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($User["email"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($User["ip"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatTimestamp($User["last_access"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($User["uploaded"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(formatBytes($User["downloaded"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html(number_format($User["invites"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . escape_html($User["seedbonus"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . escape_attr($User["username"]) . "\"><img src=\"images/user_edit.png\" alt=\"" . escape_attr($Language[18]) . "\" title=\"" . escape_attr($Language[18]) . "\" border=\"0\" /></a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
            }
            $Message = "\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"10\"><b>" . escape_html($Language[3]) . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[6]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[12]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[19]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[20]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[13]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[14]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[15]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[16]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[17]) . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . escape_html($Language[18]) . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>";
        }
    } catch (Exception $e) {
        error_log('Search user error: ' . $e->getMessage());
        $Message = showAlertErrorModern($Language[4] ?? 'Search failed');
    }
    }
}

// Get usergroups with PDO
$showusergroups = "\r\n<select name=\"usergroup\">\r\n\t<option value=\"0\"></option>";
try {
    $result = $TSDatabase->query("SELECT gid, title FROM usergroups ORDER by disporder ASC", []);
    while ($UG = $result->fetch(PDO::FETCH_ASSOC)) {
        $showusergroups .= "\r\n\t<option value=\"" . escape_attr($UG["gid"]) . "\"" . ($usergroup == $UG["gid"] ? " selected=\"selected\"" : "") . ">" . escape_html($UG["title"]) . "</option>";
    }
} catch (Exception $e) {
    error_log('Failed to load usergroups: ' . $e->getMessage());
}
$showusergroups .= "\r\n</select>";

// Get main config with PDO
try {
    $result = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'", []);
    $Result = $result->fetch(PDO::FETCH_ASSOC);
    $MAIN = unserialize($Result["content"]);
} catch (Exception $e) {
    error_log('Failed to load config: ' . $e->getMessage());
    $MAIN = [];
}
echo "<script type=\"text/javascript\">\r\n\t\$(function()\r\n\t{\r\n\t\t\$('#joindateafter,#joindatebefore,#lastaccessafter,#lastaccessbefore').datepicker({dateFormat: \"yy-mm-dd\", changeMonth: true, changeYear: true, closeText: \"X\", showButtonPanel: true});\r\n\t\t\$('#bdayafter,#bdaybefore').datepicker({dateFormat: 'dd-mm-yy', changeMonth: true, changeYear: true, closeText: \"X\", showButtonPanel: true});\r\n\t});\r\n</script>\r\n<form action=\"";
echo escape_attr($_SERVER["SCRIPT_NAME"]);
echo "?do=search_user\" method=\"post\" name=\"search_user\">\r\n";
echo getFormTokenField() . "\r\n";
echo $Message;
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>";
echo escape_html($Language[2]);
echo "</b></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[6]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"username\" value=\"";
echo escape_attr($username);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[7]);
echo "</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t<div class=\"smallfont\" style=\"white-space:nowrap\">\r\n\t\t\t\t<input type=\"radio\" name=\"exact\" id=\"rb_1_exact_2\" value=\"1\" tabindex=\"1\" />";
echo escape_html($Language[8]);
echo "\t\t\t\t<input type=\"radio\" name=\"exact\" id=\"rb_0_exact_2\" value=\"0\" tabindex=\"1\" checked=\"checked\" />";
echo escape_html($Language[9]);
echo "\t\t\t</div>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[12]);
echo "</td>\r\n\t\t<td class=\"alt1\">";
echo $showusergroups;
echo "</td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[19]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"email\" value=\"";
echo escape_attr($email);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[20]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"ip\" value=\"";
echo escape_attr($ip);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[39]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"modcomment\" value=\"";
echo escape_attr($modcomment);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[21]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"joindateafter\" id=\"joindateafter\" value=\"";
echo escape_attr($joindateafter);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[22]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"joindatebefore\" id=\"joindatebefore\" value=\"";
echo escape_attr($joindatebefore);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[23]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"lastaccessafter\" id=\"lastaccessafter\" value=\"";
echo escape_attr($lastaccessafter);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[24]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"lastaccessbefore\" id=\"lastaccessbefore\" value=\"";
echo escape_attr($lastaccessbefore);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[25]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"bdayafter\" id=\"bdayafter\" value=\"";
echo escape_attr($bdayafter);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[26]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"bdaybefore\" id=\"bdaybefore\" value=\"";
echo escape_attr($bdaybefore);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[27]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"postgreater\" value=\"";
echo escape_attr($postgreater);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[28]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"postless\" value=\"";
echo escape_attr($postless);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[29]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"warnsgreater\" value=\"";
echo escape_attr($warnsgreater);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[30]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"warnless\" value=\"";
echo escape_attr($warnless);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[31]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"ulgreater\" value=\"";
echo escape_attr($ulgreater);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /> (GB)</td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[32]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"ulless\" value=\"";
echo escape_attr($ulless);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /> (GB)</td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[33]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"dlgreater\" value=\"";
echo escape_attr($dlgreater);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /> (GB)</td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[34]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"dlless\" value=\"";
echo escape_attr($dlless);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /> (GB)</td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[35]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"ratiogreater\" value=\"";
echo escape_attr($ratiogreater);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[36]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"ratioless\" value=\"";
echo escape_attr($ratioless);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo escape_html($Language[37]);
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"idgreater\" value=\"";
echo escape_attr($idgreater);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo escape_html($Language[38]);
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"idless\" value=\"";
echo escape_attr($idless);
echo "\" size=\"10\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" align=\"right\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"";
echo escape_attr($Language[10]);
echo "\" accesskey=\"s\" />\r\n\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"";
echo escape_attr($Language[11]);
echo "\" accesskey=\"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
// Helper functions (kept for backward compatibility)
function formatBytes($bytes = 0)
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

function formatTimestamp($timestamp = "")
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, $timestamp);
}

function applyUsernameStyle($username, $namestyle)
{
    // Escape the username before inserting into the style template
    // The namestyle comes from the database (usergroups table) and is trusted HTML
    return str_replace("{username}", escape_html($username), $namestyle);
}

?>