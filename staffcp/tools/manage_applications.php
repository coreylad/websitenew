<?php
checkStaffAuthentication();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : "");
$Language = file("languages/" . getStaffLanguage() . "/manage_applications.lang");
$Message = "";
$title = "";
$description = "";
$requirements = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
if ($Act == "delete" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title  FROM ts_applications WHERE $aid = '" . $id . "'");
    if (0 < mysqli_num_rows($query)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_applications WHERE $aid = '" . $id . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Result = mysqli_fetch_assoc($query);
            $title = $Result["title"];
            $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[11]);
            logStaffAction($Message);
            $Message = showAlertMessage($Message);
        }
    }
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
    $description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
    $requirements = isset($_POST["requirements"]) ? trim($_POST["requirements"]) : "";
    if ($title && $description && $requirements) {
        if ($Act == "new") {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_applications (title, description , requirements,  created, enabled, `by`) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $requirements) . "', '" . time() . "', '1', '" . $_SESSION["ADMIN_ID"] . "')");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[10]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
                $Act = "";
            }
        } else {
            if ($Act == "edit" && $id) {
                $enabled = isset($_POST["enabled"]) ? intval($_POST["enabled"]) : "1";
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_applications SET $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', $requirements = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $requirements) . "', $enabled = '" . $enabled . "' WHERE $aid = '" . $id . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[12]);
                    logStaffAction($Message);
                    $Message = showAlertMessage($Message);
                }
                $Act = "";
            }
        }
    } else {
        $Message = showAlertError($Language[3]);
    }
}
if ($Act == "new") {
    echo loadTinyMCEEditor() . "\r\n\t<form $action = \"index.php?do=manage_applications&$act = new\" $method = \"post\">\r\n\t\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . " - " . $Language[6] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $style = \"width: 99%;\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t\t<td class=\"alt1\"><textarea $name = \"description\" $id = \"description\" $style = \"width: 100%; height: 100px;\" $dir = \"ltr\" $tabindex = \"1\">" . htmlspecialchars($description) . "</textarea>\r\n\t\t\t<p><a $href = \"javascript:toggleEditor('description');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[21] . "</td>\r\n\t\t\t<td class=\"alt1\"><textarea $name = \"requirements\" $id = \"requirements\" $style = \"width: 100%; height: 100px;\" $dir = \"ltr\" $tabindex = \"1\">" . htmlspecialchars($requirements) . "</textarea>\r\n\t\t\t<p><a $href = \"javascript:toggleEditor('requirements');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[14] . "\" /> <input $type = \"reset\" $value = \"" . $Language[15] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
}
if ($Act == "edit" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_applications WHERE $aid = '" . $id . "'");
    if (0 < mysqli_num_rows($query)) {
        $Ann = mysqli_fetch_assoc($query);
        echo loadTinyMCEEditor() . "\r\n\t\t<form $action = \"index.php?do=manage_applications&$act = edit&$id = " . $id . "\" $method = \"post\">\r\n\t\t\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[4] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars($Ann["title"]) . "\" $style = \"width: 99%;\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea $name = \"description\" $id = \"description\" $style = \"width: 100%; height: 100px;\" $dir = \"ltr\" $tabindex = \"1\">" . htmlspecialchars($Ann["description"]) . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('description');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[21] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea $name = \"requirements\" $id = \"requirements\" $style = \"width: 100%; height: 100px;\" $dir = \"ltr\" $tabindex = \"1\">" . htmlspecialchars($Ann["requirements"]) . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('requirements');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[9] . "</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<select $name = \"enabled\">\r\n\t\t\t\t\t\t<option $value = \"0\"" . (isset($enabled) && $enabled == "0" || $Ann["enabled"] == "0" ? " $selected = \"selected\"" : "") . ">" . $Language[20] . "</option>\r\n\t\t\t\t\t\t<option $value = \"1\"" . (isset($enabled) && $enabled == "1" || $Ann["enabled"] == "1" ? " $selected = \"selected\"" : "") . ">" . $Language[19] . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[14] . "\" /> <input $type = \"reset\" $value = \"" . $Language[15] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "view" && $id) {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $rids = isset($_POST["rids"]) ? $_POST["rids"] : "";
        if (is_array($rids) && $rids[0] != "") {
            $rids = implode(",", $rids);
            if (isset($_POST["approve"])) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_application_requests SET `status` = '2' WHERE rid IN (" . $rids . ")");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uid FROM ts_application_requests WHERE rid IN (" . $rids . ")");
                    while ($User = mysqli_fetch_assoc($query)) {
                        sendPrivateMessage($User["uid"], $Language[37], $Language[36], $_SESSION["ADMIN_ID"]);
                    }
                    $Message = str_replace(["{1}", "{2}"], [$rids, $_SESSION["ADMIN_USERNAME"]], $Language[33]);
                    logStaffAction($Message);
                    $Message = showAlertMessage($Message);
                }
            } else {
                if (isset($_POST["deny"])) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_application_requests SET `status` = '1' WHERE rid IN (" . $rids . ")");
                    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT uid FROM ts_application_requests WHERE rid IN (" . $rids . ")");
                        while ($User = mysqli_fetch_assoc($query)) {
                            sendPrivateMessage($User["uid"], $Language[38], $Language[36], $_SESSION["ADMIN_ID"]);
                        }
                        $Message = str_replace(["{1}", "{2}"], [$rids, $_SESSION["ADMIN_USERNAME"]], $Language[34]);
                        logStaffAction($Message);
                        $Message = showAlertMessage($Message);
                    }
                } else {
                    if (isset($_POST["delete"])) {
                        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_application_requests WHERE rid IN (" . $rids . ")");
                        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                            $Message = str_replace(["{1}", "{2}"], [$rids, $_SESSION["ADMIN_USERNAME"]], $Language[35]);
                            logStaffAction($Message);
                            $Message = showAlertMessage($Message);
                        }
                    }
                }
            }
        }
    }
    $Message = "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_applications&$act = view&$id = " . $id . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"manage_applications\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"6\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . " - " . $Language[22] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . $Language[28] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[23] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[29] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[17] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[9] . "</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('manage_applications', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT r.*, u.username, g.namestyle FROM ts_application_requests r LEFT JOIN users u ON (r.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE r.$aid = '" . $id . "' ORDER BY r.created DESC, r.status ASC");
    if (0 < mysqli_num_rows($query)) {
        while ($R = mysqli_fetch_assoc($query)) {
            $Message .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $R["username"] . "\">" . applyUsernameStyle($R["username"], $R["namestyle"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t" . htmlspecialchars($R["url"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t" . htmlspecialchars($R["info"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t" . formatTimestamp($R["created"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t" . ($R["status"] == "0" ? $Language[30] : ($R["status"] == "2" ? $Language[31] : $Language[32])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"rids[]\" $value = \"" . $R["rid"] . "\" $checkme = \"group\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
        $Message .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"right\" $colspan = \"6\">\r\n\t\t\t\t\t<input $type = \"submit\" $name = \"approve\" $value = \"" . $Language[24] . "\" /> <input $type = \"submit\" $name = \"deny\" $value = \"" . $Language[25] . "\" /> <input $type = \"submit\" $name = \"delete\" $value = \"" . $Language[26] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t";
    } else {
        $Message .= "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"6\" class=\"alt1\">\r\n\t\t\t\t" . $Language[27] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $Message .= "\r\n\t</table>\r\n\t</form>\r\n\t<br />\r\n\t";
}
$AppRequests = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT aid FROM ts_application_requests");
while ($req = mysqli_fetch_assoc($query)) {
    if (isset($AppRequests[$req["aid"]])) {
        $AppRequests[$req["aid"]]++;
    } else {
        $AppRequests[$req["aid"]] = 1;
    }
}
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_applications"));
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=manage_applications&amp;");
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT a.*, u.username, g.namestyle FROM ts_applications a LEFT JOIN users u ON (a.$by = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) ORDER by a.created DESC " . $limit);
if (0 < mysqli_num_rows($query)) {
    echo showAlertMessage("<a $href = \"index.php?do=manage_applications&amp;$act = new\">" . $Language[6] . "</a>") . "\r\n\t" . $Message . "\r\n\t" . $pagertop . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"7\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . " (" . $results . ")\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . $Language[7] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[8] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[16] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[17] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[9] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[22] . "</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\">" . $Language[18] . "</td>\r\n\t\t</tr>\r\n\t\t";
    while ($Ann = mysqli_fetch_assoc($query)) {
        echo "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Ann["title"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . strip_tags(substr($Ann["description"], 0, 150)) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $Ann["username"] . "\">" . applyUsernameStyle($Ann["username"], $Ann["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($Ann["created"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($Ann["enabled"] == "0" ? $Language[20] : $Language[19]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . (isset($AppRequests[$Ann["aid"]]) && 0 < $AppRequests[$Ann["aid"]] ? "<a $href = \"index.php?do=manage_applications&amp;$act = view&amp;$id = " . $Ann["aid"] . "\">" . number_format($AppRequests[$Ann["aid"]]) . "</a>" : 0) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_applications&amp;$act = edit&amp;$id = " . $Ann["aid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[4] . "\" $title = \"" . $Language[4] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_applications&amp;$act = delete&amp;$id = " . $Ann["aid"] . "\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[5] . "\" $title = \"" . $Language[5] . "\" $border = \"0\" /></a> \r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    echo "\r\n\t</table>\r\n\t" . $pagertop;
} else {
    echo showAlertError(str_replace("{1}", "index.php?do=manage_applications&amp;$act = new", $Language[13]));
}
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $editorContent = ob_get_contents();
    ob_end_clean();
    return $editorContent;
}
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
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_148($selected)
{
    if (!is_array($selected) && preg_match("@,@Uis", $selected)) {
        $selected = explode(",", $selected);
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
    $currentUserPerms = mysqli_fetch_assoc($query);
    $count = 0;
    $userGroupsHtml = "\r\n\t<table>\r\n\t\t<tr>\t";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups WHERE `isbanned` = 'no' ORDER by disporder ASC");
    while ($row = mysqli_fetch_assoc($query)) {
        if (!($row["cansettingspanel"] == "yes" && $currentUserPerms["cansettingspanel"] != "yes" || $row["canstaffpanel"] == "yes" && $currentUserPerms["canstaffpanel"] != "yes" || $row["issupermod"] == "yes" && $currentUserPerms["issupermod"] != "yes")) {
            if ($count && $count % 8 == 0) {
                $userGroupsHtml .= "</tr><tr>";
            }
            $userGroupsHtml .= "<td><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $row["gid"] . "\"" . (is_array($selected) && count($selected) && in_array($row["gid"], $selected) ? " $checked = \"checked\"" : "") . " /></td><td>" . str_replace("{username}", $row["title"], $row["namestyle"]) . "</td>";
            $count++;
        }
    }
    $userGroupsHtml .= "</tr></table>";
    return $userGroupsHtml;
}
function validatePerPage($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $totalPages = ceil($numresults / $perpage);
    if ($totalPages == 0) {
        $totalPages = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($totalPages < $page) {
            $page = $totalPages;
        }
    }
}
function calculatePagination($pagenumber, $perpage, $total)
{
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    $paginationFirstItem++;
    return ["first" => number_format($paginationFirstItem), "last" => number_format($paginationLastItem)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $queryResult = @ceil($results / $perpage);
    } else {
        $queryResult = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $limitOffset = ($pagenumber - 1) * $perpage;
    $paginationOffset = $pagenumber * $perpage;
    if ($results < $paginationOffset) {
        $paginationOffset = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    $paginationLinks = $prevPage = $nextPage = $pageLinks = $paginationHtml = "";
    $currentPage = 0;
    if ($results <= $perpage) {
        $paginationHtml["pagenav"] = false;
        return ["", "LIMIT " . $limitOffset . ", " . $perpage];
    }
    $paginationHtml["pagenav"] = true;
    $total = number_format($results);
    $paginationHtml["last"] = false;
    $paginationHtml["first"] = $paginationHtml["last"];
    $paginationHtml["next"] = $paginationHtml["first"];
    $paginationHtml["prev"] = $paginationHtml["next"];
    if (1 < $pagenumber) {
        $previousPage = $pagenumber - 1;
        $previousPageInfo = calculatePagination($previousPage, $perpage, $results);
        $paginationHtml["prev"] = true;
    }
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPageNumber, $perpage, $results);
        $paginationHtml["next"] = true;
    }
    $pageRangeThreshold = "3";
    if (!isset($paginationSkipLinksArray) || !is_array($paginationSkipLinksArray)) {
        $paginationOptions = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $paginationOptions, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $previousPageQuery = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $previousPageQuery . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($pageRangeThreshold <= abs($currentPage - $pagenumber) && $pageRangeThreshold != 0) {
        if ($currentPage == 1) {
            $firstPageInfo = calculatePagination(1, $perpage, $results);
            $paginationHtml["first"] = true;
        }
        if ($currentPage == $queryResult) {
            $lastPageInfo = calculatePagination($queryResult, $perpage, $results);
            $paginationHtml["last"] = true;
        }
        if (in_array(abs($currentPage - $pagenumber), $paginationSkipLinksArray) && $currentPage != 1 && $currentPage != $queryResult) {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $pageOffsetDisplay = $currentPage - $pagenumber;
            if (0 < $pageOffsetDisplay) {
                $pageOffsetDisplay = "+" . $pageOffsetDisplay;
            }
            $paginationLinks .= "<li><a class=\"smalltext\" $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        }
    }
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
    return str_replace("{username}", $username, $namestyle);
}
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages\r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES\r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $receiver . "'");
    }
}

?>