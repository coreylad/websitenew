<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_staff_team.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $STAFFTEAM = isset($_POST["staffteam"]) ? $_POST["staffteam"] : "";
    if (is_array($STAFFTEAM) && 0 < count($STAFFTEAM) && $STAFFTEAM[0] != "") {
        $NewStaffArray = [];
        $ErrorArray = [];
        foreach ($STAFFTEAM as $StaffMember) {
            $StaffMember = trim($StaffMember);
            if ($StaffMember && !in_array($StaffMember, $NewStaffArray)) {
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $StaffMember) . "' AND $enabled = 'yes' AND $status = 'confirmed'");
                if (0 < mysqli_num_rows($query)) {
                    $Result = mysqli_fetch_assoc($query);
                    $NewStaffArray[] = $StaffMember . ":" . $Result["id"];
                } else {
                    $ErrorArray[] = $StaffMember;
                }
            }
        }
        if ($NewStaffArray[0] != "") {
            $NewStaffArray = trim(implode(",", $NewStaffArray));
            mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_config` VALUES ('STAFFTEAM', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $NewStaffArray) . "')");
            logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[11]));
            $Message .= showAlertError($Language[7]);
        }
        if (isset($ErrorArray[0]) && $ErrorArray[0] != "") {
            $Message .= showAlertError(str_replace("{1}", htmlspecialchars(implode(", ", $ErrorArray)), $Language[6]));
        }
    } else {
        $Message = showAlertError($Language[8]);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'STAFFTEAM'");
$Result = mysqli_fetch_assoc($query);
$STAFFTEAM = trim($Result["content"]);
$STAFFTEAM = explode(",", $STAFFTEAM);
$List = "";
foreach ($STAFFTEAM as $Member) {
    $NameIDArray = explode(":", $Member);
    $List .= " <input $type = \"text\" $size = \"20\" $name = \"staffteam[]\" $value = \"" . $NameIDArray[0] . "\" /> ";
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tvar $totaladded = 0;\r\n\tfunction addnewfield()\r\n\t{\r\n\t\tvar $name = prompt(\"" . trim($Language[12]) . "\", \"\");\r\n\t\tif (name)\r\n\t\t{\r\n\t\t\tTSGetID(\"newfield\").$innerHTML = TSGetID(\"newfield\").innerHTML+' <input $type = \"text\" $size = \"20\" $name = \"staffteam[]\" $value = \"'+name+'\" />';\r\n\t\t\talert('" . trim($Language[13]) . "');\r\n\t\t}\r\n\t}\r\n</script>\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=manage_staff_team\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<img $src = \"images/tool_new.png\" $border = \"0\" $style = \"vertical-align: middle;\" $onclick = \"javascript: addnewfield();\" $alt = \"" . $Language[9] . "\" $title = \"" . $Language[9] . "\" />\r\n\t\t\t" . $List . " <span $id = \"newfield\"></span> <input $type = \"submit\" $value = \"" . $Language[5] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; $url = " . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>