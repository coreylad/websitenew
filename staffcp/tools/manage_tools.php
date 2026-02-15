<?php
declare(strict_types=1);

checkStaffAuthentication();

$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : (isset($_POST["tid"]) ? intval($_POST["tid"]) : 0);

$Language = file("languages/" . getStaffLanguage() . "/manage_tools.lang");
if ($Language === false) {
    die('Failed to load language file');
}

$Message = "";
$HTMLOutput = "";

try {
    $pdo = $GLOBALS["DatabaseConnect"];
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST" && $Act === "save_order") {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            die('CSRF token validation failed');
        }
        
        $stmt = $pdo->prepare("UPDATE ts_staffcp_tools SET sort = ? WHERE tid = ?");
        foreach ($_POST["order"] as $_tid => $_sort) {
            $stmt->execute([intval($_sort), intval($_tid)]);
        }
    }
    
    if ($Act && $Tid) {
        $stmt = $pdo->prepare("SELECT * FROM ts_staffcp_tools WHERE tid = ?");
        $stmt->execute([$Tid]);
        
        if ($Tool = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $AllowedUsergroups = explode(",", $Tool["usergroups"]);
            
            if ($_SESSION["ADMIN_GID"] && $AllowedUsergroups && in_array($_SESSION["ADMIN_GID"], $AllowedUsergroups)) {
                if ($Act === "delete") {
                    $deleteStmt = $pdo->prepare("DELETE FROM ts_staffcp_tools WHERE tid = ?");
                    $deleteStmt->execute([$Tid]);
                    
                    if ($deleteStmt->rowCount() > 0) {
                        $SysMsg = str_replace(["{1}", "{2}"], [htmlspecialchars($Tool["toolname"]), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[6]);
                        logStaffAction($SysMsg);
                    }
                }
                
                if ($Act === "edit") {
                    if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
                        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
                            die('CSRF token validation failed');
                        }
                        
                        $category = intval($_POST["cid"]);
                        $toolname = trim($_POST["toolname"]);
                        $filename = trim($_POST["filename"]);
                        $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
                        $sort = intval($_POST["sort"]);
                        
                        if ($category && $toolname && $filename && count($usergroups) && is_array($usergroups)) {
                            $updateStmt = $pdo->prepare("UPDATE ts_staffcp_tools SET cid = ?, toolname = ?, filename = ?, usergroups = ?, sort = ? WHERE tid = ?");
                            $updateStmt->execute([$category, $toolname, $filename, implode(",", $usergroups), $sort, $Tid]);
                            
                            $SysMsg = str_replace(["{1}", "{2}"], [htmlspecialchars($Tool["toolname"]), htmlspecialchars($_SESSION["ADMIN_USERNAME"])], $Language[7]);
                            logStaffAction($SysMsg);
                            redirectTo("index.php?do=manage_tools");
                            exit;
                        }
                        $Message = showAlertError(htmlspecialchars($Language[18]));
                    }
                    
                    $stmt = $pdo->prepare("SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.id = ? LIMIT 1");
                    $stmt->execute([$_SESSION["ADMIN_ID"]]);
                    $LoggedAdminDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $showusergroups = "";
                    $ugStmt = $pdo->query("SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER BY disporder ASC");
                    
                    while ($UG = $ugStmt->fetch(PDO::FETCH_ASSOC)) {
                        if (!($UG["cansettingspanel"] === "yes" && $LoggedAdminDetails["cansettingspanel"] !== "yes" || 
                              $UG["canstaffpanel"] === "yes" && $LoggedAdminDetails["canstaffpanel"] !== "yes" || 
                              $UG["issupermod"] === "yes" && $LoggedAdminDetails["issupermod"] !== "yes")) {
                            $showusergroups .= "
\t\t\t\t\t\t<div style=\"margin-bottom: 3px;\">
\t\t\t\t\t\t\t<label><input type=\"checkbox\" name=\"usergroups[]\" value=\"" . htmlspecialchars((string)$UG["gid"]) . "\"" . (in_array($UG["gid"], $AllowedUsergroups) ? " checked=\"checked\"" : "") . " style=\"vertical-align: middle;\" /> " . strip_tags(str_replace("{username}", htmlspecialchars($UG["title"]), htmlspecialchars($UG["namestyle"])), "<b><span><strong><em><i><u>") . "</label>
\t\t\t\t\t\t</div>";
                        }
                    }
                    
                    $showcategories = "<select name=\"cid\">";
                    $catStmt = $pdo->query("SELECT cid, name FROM ts_staffcp ORDER BY sort ASC");
                    
                    while ($cats = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                        $showcategories .= "<option value=\"" . htmlspecialchars((string)$cats["cid"]) . "\"" . ($Tool["cid"] == $cats["cid"] ? " selected=\"selected\"" : "") . ">" . htmlspecialchars($cats["name"]) . "</option>";
                    }
                    $showcategories .= "</select>";
                    
                    if (!isset($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    
                    $HTMLOutput .= "
\t\t\t\t" . $Message . "
\t\t\t\t<form method=\"post\" action=\"index.php?do=manage_tools&amp;act=edit&amp;tid=" . htmlspecialchars((string)$Tid) . "\">
\t\t\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($_SESSION['csrf_token']) . "\">
\t\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">
\t\t\t\t\t\t\t" . htmlspecialchars($Language[3]) . " - " . htmlspecialchars($Language[4]) . "
\t\t\t\t\t\t</td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[12]) . "</td>
\t\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"toolname\" value=\"" . htmlspecialchars($Tool["toolname"]) . "\" size=\"40\" /></td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[13]) . "</td>
\t\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"filename\" value=\"" . htmlspecialchars($Tool["filename"]) . "\" size=\"40\" /></td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[15]) . "</td>
\t\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"sort\" value=\"" . htmlspecialchars((string)intval($Tool["sort"])) . "\" size=\"40\" /></td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[23]) . "</td>
\t\t\t\t\t\t<td class=\"alt1\">" . $showcategories . "</td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"alt1\" valign=\"top\">" . htmlspecialchars($Language[14]) . "</td>
\t\t\t\t\t\t<td class=\"alt1\">" . $showusergroups . "</td>
\t\t\t\t\t</tr>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"tcat2\"></td>
\t\t\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[16]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[17]) . "\" /></td>
\t\t\t\t\t</tr>
\t\t\t\t</table>
\t\t\t\t</form>";
                }
            }
        }
    }
    
    if (!$HTMLOutput) {
        $StaffTools = [];
        $toolsStmt = $pdo->query("SELECT tid, cid, toolname, filename, usergroups, sort FROM ts_staffcp_tools ORDER BY sort, toolname ASC");
        
        while ($Tools = $toolsStmt->fetch(PDO::FETCH_ASSOC)) {
            $AllowedUsergroups = explode(",", $Tools["usergroups"]);
            
            if ($_SESSION["ADMIN_GID"] && in_array($_SESSION["ADMIN_GID"], $AllowedUsergroups)) {
                $StaffTools[$Tools["cid"]][] = "
\t\t\t<table>
\t\t\t\t<tr>
\t\t\t\t\t<td width=\"1%\">
\t\t\t\t\t\t<a href=\"index.php?do=manage_tools&amp;act=edit&amp;tid=" . htmlspecialchars((string)$Tools["tid"]) . "\"><img src=\"images/tool_edit.png\" alt=\"" . htmlspecialchars(trim($Language[4])) . "\" title=\"" . htmlspecialchars(trim($Language[4])) . "\" border=\"0\" style=\"vertical-align: middle;\" /></a>
\t\t\t\t\t</td>
\t\t\t\t\t<td width=\"1%\">
\t\t\t\t\t\t<a href=\"index.php?do=manage_tools&amp;act=delete&amp;tid=" . htmlspecialchars((string)$Tools["tid"]) . "\" onclick=\"return confirm('" . htmlspecialchars(trim($Language[5])) . ": " . htmlspecialchars(trim(str_replace("'", "`", $Tools["toolname"]))) . "\\n\\n" . htmlspecialchars(trim($Language[9])) . "');\"><img src=\"images/tool_delete.png\" alt=\"" . htmlspecialchars(trim($Language[5])) . "\" title=\"" . htmlspecialchars(trim($Language[5])) . "\" border=\"0\" style=\"vertical-align: middle;\" /></a>
\t\t\t\t\t</td>
\t\t\t\t\t<td width=\"88%\">
\t\t\t\t\t\t" . htmlspecialchars(trim($Tools["toolname"])) . "
\t\t\t\t\t</td>
\t\t\t\t\t<td width=\"10%\" align=\"right\">
\t\t\t\t\t\t<input type=\"text\" size=\"5\" value=\"" . htmlspecialchars((string)$Tools["sort"]) . "\" name=\"order[" . htmlspecialchars((string)$Tools["tid"]) . "]\" />
\t\t\t\t\t</td>
\t\t\t\t</tr>
\t\t\t</table>";
            }
        }
        
        $Output = [];
        $stStmt = $pdo->query("SELECT cid, name FROM ts_staffcp ORDER BY sort ASC");
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        while ($ST = $stStmt->fetch(PDO::FETCH_ASSOC)) {
            $Output[] = "
\t\t<form method=\"post\" action=\"index.php?do=manage_tools&amp;act=save_order\" name=\"sort_order\">
\t\t<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($_SESSION['csrf_token']) . "\">
\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t\t\t<tr>
\t\t\t\t<td class=\"tcat\">
\t\t\t\t\t<span style=\"float: right;\">
\t\t\t\t\t\t<a href=\"index.php?do=new_tool&amp;cid=" . htmlspecialchars((string)$ST["cid"]) . "\"><img src=\"images/tool_new.png\" alt=\"" . htmlspecialchars(trim($Language[24])) . "\" title=\"" . htmlspecialchars(trim($Language[24])) . "\" border=\"0\" style=\"vertical-align: middle;\" /></a> <a href=\"index.php?do=manage_category&amp;cid=" . htmlspecialchars((string)$ST["cid"]) . "\"><img src=\"images/tool_edit.png\" alt=\"" . htmlspecialchars(trim($Language[20])) . "\" title=\"" . htmlspecialchars(trim($Language[20])) . "\" border=\"0\" style=\"vertical-align: middle;\" /></a> <a href=\"index.php?do=manage_category&amp;act=delete&amp;cid=" . htmlspecialchars((string)$ST["cid"]) . "\" onclick=\"return confirm('" . htmlspecialchars(trim($Language[21])) . ": " . htmlspecialchars(trim($ST["name"])) . "\\n\\n" . htmlspecialchars(trim($Language[22])) . "');\"><img src=\"images/tool_delete.png\" alt=\"" . htmlspecialchars(trim($Language[21])) . "\" title=\"" . htmlspecialchars(trim($Language[21])) . "\" border=\"0\" style=\"vertical-align: middle;\" /></a>
\t\t\t\t\t</span>
\t\t\t\t\t" . htmlspecialchars($ST["name"]) . "
\t\t\t\t</td>
\t\t\t</tr>
\t\t\t<tr>
\t\t\t\t<td class=\"alt1\">" . (isset($StaffTools[$ST["cid"]]) ? implode(" ", $StaffTools[$ST["cid"]]) : "&nbsp;" . htmlspecialchars($Language[1])) . "</td>
\t\t\t</tr>
\t\t\t" . (isset($StaffTools[$ST["cid"]]) ? "
\t\t\t<tr>
\t\t\t\t<td class=\"tcat2\" align=\"right\"><input type=\"submit\" value=\"" . htmlspecialchars($Language[10]) . "\" /> <input type=\"reset\" value=\"" . htmlspecialchars($Language[11]) . "\" /></td>
\t\t\t</tr>
\t\t\t" : "") . "
\t\t</table>
\t\t</form>";
        }
        
        $HTMLOutput .= "
\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">
\t\t<tr>
\t\t\t<td class=\"tcat\" align=\"center\">" . htmlspecialchars($Language[3]) . "</td>
\t\t</tr>
\t</table>";
        
        for ($i = 0; $i <= count($Output); $i++) {
            if (isset($Output[$i]) && $Output[$i] !== "") {
                $HTMLOutput .= $Output[$i];
            }
        }
    }
    
    echo $HTMLOutput;
    
} catch (PDOException $e) {
    error_log("Database error in manage_tools.php: " . $e->getMessage());
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
