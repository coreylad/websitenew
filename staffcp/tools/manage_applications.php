<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthenticationModern();

$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : "");
$Language = loadStaffLanguage("manage_applications");
$Message = "";
$title = "";
$description = "";
$requirements = "";

$result = $TSDatabase->query("SELECT `content` FROM `ts_config` WHERE `configname` = ?", ['MAIN']);
$Result = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
$MAIN = $Result ? unserialize($Result["content"]) : [];
if ($Act == "delete" && $id) {
    $result = $TSDatabase->query("SELECT title FROM ts_applications WHERE aid = ?", [$id]);
    if ($result && $result->fetch(PDO::FETCH_ASSOC)) {
        $result = $TSDatabase->query("SELECT title FROM ts_applications WHERE aid = ?", [$id]);
        $Result = $result->fetch(PDO::FETCH_ASSOC);
        
        $TSDatabase->query("DELETE FROM ts_applications WHERE aid = ?", [$id]);
        
        $title = $Result["title"];
        $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[11]);
        logStaffActionModern($Message);
        $Message = showAlertMessage($Message);
    }
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    // Validate form token for CSRF protection
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[3] ?? 'Invalid form token. Please try again.');
    } else {
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
        $requirements = isset($_POST["requirements"]) ? trim($_POST["requirements"]) : "";
        if ($title && $description && $requirements) {
            if ($Act == "new") {
                $TSDatabase->query(
                    "INSERT INTO ts_applications (title, description, requirements, created, enabled, `by`) VALUES (?, ?, ?, ?, ?, ?)",
                    [$title, $description, $requirements, time(), '1', $_SESSION["ADMIN_ID"]]
                );
                
                $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[10]);
                logStaffActionModern($Message);
                $Message = showAlertMessage($Message);
                $Act = "";
            } else {
                if ($Act == "edit" && $id) {
                    $enabled = isset($_POST["enabled"]) ? intval($_POST["enabled"]) : 1;
                    $TSDatabase->query(
                        "UPDATE ts_applications SET title = ?, description = ?, requirements = ?, enabled = ? WHERE aid = ?",
                        [$title, $description, $requirements, $enabled, $id]
                    );
                    
                    $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[12]);
                    logStaffActionModern($Message);
                    $Message = showAlertMessage($Message);
                    $Act = "";
                }
            }
        } else {
            $Message = showAlertErrorModern($Language[3]);
        }
    }
}
if ($Act == "new") {
    echo loadTinyMCEEditor() . "\r\n\t<form action = \"index.php?do=manage_applications&act = new\" method = \"post\">\r\n\t" . getFormTokenField() . "\r\n\t" . $Message . "\r\n\t<table cellpadding = \"0\" cellspacing = \"0\" border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan = \"2\" align = \"center\">\r\n\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[6]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t<td class=\"alt1\"><input type = \"text\" name = \"title\" value = \"" . escape_attr($title) . "\" style = \"width: 99%;\" dir = \"ltr\" tabindex = \"1\" /></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" valign = \"top\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t<td class=\"alt1\"><textarea name = \"description\" id = \"description\" style = \"width: 100%; height: 100px;\" dir = \"ltr\" tabindex = \"1\">" . escape_html($description) . "</textarea>\r\n\t\t\t<p><a href = \"javascript:toggleEditor('description');\"><img src = \"images/tool_refresh.png\" border = \"0\" /></a></p></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" valign = \"top\">" . escape_html($Language[21]) . "</td>\r\n\t\t\t<td class=\"alt1\"><textarea name = \"requirements\" id = \"requirements\" style = \"width: 100%; height: 100px;\" dir = \"ltr\" tabindex = \"1\">" . escape_html($requirements) . "</textarea>\r\n\t\t\t<p><a href = \"javascript:toggleEditor('requirements');\"><img src = \"images/tool_refresh.png\" border = \"0\" /></a></p></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input type = \"submit\" value = \"" . escape_attr($Language[14]) . "\" /> <input type = \"reset\" value = \"" . escape_attr($Language[15]) . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
}
if ($Act == "edit" && $id) {
    $result = $TSDatabase->query("SELECT * FROM ts_applications WHERE aid = ?", [$id]);
    if ($result && $Ann = $result->fetch(PDO::FETCH_ASSOC)) {
        echo loadTinyMCEEditor() . "\r\n\t\t<form action = \"index.php?do=manage_applications&act = edit&id = " . $id . "\" method = \"post\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t<table cellpadding = \"0\" cellspacing = \"0\" border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" colspan = \"2\" align = \"center\">\r\n\t\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[4]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type = \"text\" name = \"title\" value = \"" . escape_attr($Ann["title"]) . "\" style = \"width: 99%;\" dir = \"ltr\" tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" valign = \"top\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea name = \"description\" id = \"description\" style = \"width: 100%; height: 100px;\" dir = \"ltr\" tabindex = \"1\">" . escape_html($Ann["description"]) . "</textarea>\r\n\t\t\t\t<p><a href = \"javascript:toggleEditor('description');\"><img src = \"images/tool_refresh.png\" border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" valign = \"top\">" . escape_html($Language[21]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea name = \"requirements\" id = \"requirements\" style = \"width: 100%; height: 100px;\" dir = \"ltr\" tabindex = \"1\">" . escape_html($Ann["requirements"]) . "</textarea>\r\n\t\t\t\t<p><a href = \"javascript:toggleEditor('requirements');\"><img src = \"images/tool_refresh.png\" border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" valign = \"top\">" . escape_html($Language[9]) . "</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<select name = \"enabled\">\r\n\t\t\t\t\t\t<option value = \"0\"" . (isset($enabled) && $enabled == "0" || $Ann["enabled"] == "0" ? " selected = \"selected\"" : "") . ">" . escape_html($Language[20]) . "</option>\r\n\t\t\t\t\t\t<option value = \"1\"" . (isset($enabled) && $enabled == "1" || $Ann["enabled"] == "1" ? " selected = \"selected\"" : "") . ">" . escape_html($Language[19]) . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input type = \"submit\" value = \"" . escape_attr($Language[14]) . "\" /> <input type = \"reset\" value = \"" . escape_attr($Language[15]) . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "view" && $id) {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        // Validate form token for CSRF protection
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            $Message = showAlertErrorModern('Invalid form token. Please try again.');
        } else {
            $rids = isset($_POST["rids"]) ? $_POST["rids"] : "";
            if (is_array($rids) && $rids[0] != "") {
                // Validate that all rids are integers
                $rids = array_map('intval', $rids);
                $ridsStr = implode(",", $rids);
                
                if (isset($_POST["approve"])) {
                    $TSDatabase->query("UPDATE ts_application_requests SET `status` = '2' WHERE rid IN (" . $ridsStr . ")");
                    
                    $result = $TSDatabase->query("SELECT uid FROM ts_application_requests WHERE rid IN (" . $ridsStr . ")");
                    if ($result) {
                        while ($User = $result->fetch(PDO::FETCH_ASSOC)) {
                            sendPrivateMessage($User["uid"], $Language[37], $Language[36], $_SESSION["ADMIN_ID"]);
                        }
                    }
                    $Message = str_replace(["{1}", "{2}"], [$ridsStr, $_SESSION["ADMIN_USERNAME"]], $Language[33]);
                    logStaffActionModern($Message);
                    $Message = showAlertMessage($Message);
                } else {
                    if (isset($_POST["deny"])) {
                        $TSDatabase->query("UPDATE ts_application_requests SET `status` = '1' WHERE rid IN (" . $ridsStr . ")");
                        
                        $result = $TSDatabase->query("SELECT uid FROM ts_application_requests WHERE rid IN (" . $ridsStr . ")");
                        if ($result) {
                            while ($User = $result->fetch(PDO::FETCH_ASSOC)) {
                                sendPrivateMessage($User["uid"], $Language[38], $Language[36], $_SESSION["ADMIN_ID"]);
                            }
                        }
                        $Message = str_replace(["{1}", "{2}"], [$ridsStr, $_SESSION["ADMIN_USERNAME"]], $Language[34]);
                        logStaffActionModern($Message);
                        $Message = showAlertMessage($Message);
                    } else {
                        if (isset($_POST["delete"])) {
                            $TSDatabase->query("DELETE FROM ts_application_requests WHERE rid IN (" . $ridsStr . ")");
                            
                            $Message = str_replace(["{1}", "{2}"], [$ridsStr, $_SESSION["ADMIN_USERNAME"]], $Language[35]);
                            logStaffActionModern($Message);
                            $Message = showAlertMessage($Message);
                        }
                    }
                }
            }
        }
    }
    $Message = "\r\n\t<script type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar frm = document.forms[formname];\r\n\t\t\tfor(i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form action = \"" . escape_attr($_SERVER["SCRIPT_NAME"] . "?do=manage_applications&act = view&id = " . $id . (isset($_GET["page"]) ? "&page = " . intval($_GET["page"]) : "")) . "\" method = \"post\" name = \"manage_applications\">\r\n\t" . getFormTokenField() . "\r\n\t<table cellpadding = \"0\" cellspacing = \"0\" border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan = \"6\" align = \"center\">\r\n\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[22]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[28]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[23]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[29]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[17]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[9]) . "</td>\r\n\t\t\t<td class=\"alt2\" align = \"center\"><input type = \"checkbox\" checkall = \"group\" onclick = \"javascript: return select_deselectAll ('manage_applications', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t";
    $result = $TSDatabase->query("SELECT r.*, u.username, g.namestyle FROM ts_application_requests r LEFT JOIN users u ON (r.uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE r.aid = ? ORDER BY r.created DESC, r.status ASC", [$id]);
    if ($result && $R = $result->fetch(PDO::FETCH_ASSOC)) {
        do {
            $Message .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" valign = \"top\">\r\n\t\t\t\t\t<a href = \"index.php?do=edit_user&amp;username = " . escape_attr($R["username"]) . "\">" . applyUsernameStyle($R["username"], $R["namestyle"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" valign = \"top\">\r\n\t\t\t\t\t" . escape_html($R["url"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" valign = \"top\">\r\n\t\t\t\t\t" . escape_html($R["info"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" valign = \"top\">\r\n\t\t\t\t\t" . formatTimestamp($R["created"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" valign = \"top\">\r\n\t\t\t\t\t" . escape_html($R["status"] == "0" ? $Language[30] : ($R["status"] == "2" ? $Language[31] : $Language[32])) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" align = \"center\" valign = \"top\">\r\n\t\t\t\t\t<input type = \"checkbox\" name = \"rids[]\" value = \"" . escape_attr((string)$R["rid"]) . "\" checkme = \"group\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        } while ($R = $result->fetch(PDO::FETCH_ASSOC));
        $Message .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" align = \"right\" colspan = \"6\">\r\n\t\t\t\t\t<input type = \"submit\" name = \"approve\" value = \"" . escape_attr($Language[24]) . "\" /> <input type = \"submit\" name = \"deny\" value = \"" . escape_attr($Language[25]) . "\" /> <input type = \"submit\" name = \"delete\" value = \"" . escape_attr($Language[26]) . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t";
    } else {
        $Message .= "\r\n\t\t<tr>\r\n\t\t\t<td colspan = \"6\" class=\"alt1\">\r\n\t\t\t\t" . escape_html($Language[27]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $Message .= "\r\n\t</table>\r\n\t</form>\r\n\t<br />\r\n\t";
}
$AppRequests = [];
$result = $TSDatabase->query("SELECT aid FROM ts_application_requests");
if ($result) {
    while ($req = $result->fetch(PDO::FETCH_ASSOC)) {
        if (isset($AppRequests[$req["aid"]])) {
            $AppRequests[$req["aid"]]++;
        } else {
            $AppRequests[$req["aid"]] = 1;
        }
    }
}

$countResult = $TSDatabase->query("SELECT COUNT(*) as count FROM ts_applications");
$results = $countResult ? $countResult->fetch(PDO::FETCH_ASSOC)['count'] : 0;
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=manage_applications&amp;");

$result = $TSDatabase->query("SELECT a.*, u.username, g.namestyle FROM ts_applications a LEFT JOIN users u ON (a.by = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) ORDER by a.created DESC " . $limit);
if ($result && $Ann = $result->fetch(PDO::FETCH_ASSOC)) {
    echo showAlertMessage("<a href = \"index.php?do=manage_applications&amp;act = new\">" . escape_html($Language[6]) . "</a>") . "\r\n\t" . $Message . "\r\n\t" . $pagertop . "\r\n\t<table cellpadding = \"0\" cellspacing = \"0\" border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan = \"7\" align = \"center\">\r\n\t\t\t\t" . escape_html($Language[2]) . " (" . $results . ")\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[7]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[8]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[16]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[17]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[9]) . "</td>\r\n\t\t\t<td class=\"alt2\">" . escape_html($Language[22]) . "</td>\r\n\t\t\t<td class=\"alt2\" align = \"center\">" . escape_html($Language[18]) . "</td>\r\n\t\t</tr>\r\n\t\t";
    do {
        echo "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html($Ann["title"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html(strip_tags(substr($Ann["description"], 0, 150))) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a href = \"index.php?do=edit_user&amp;username = " . escape_attr($Ann["username"]) . "\">" . applyUsernameStyle($Ann["username"], $Ann["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($Ann["created"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . escape_html($Ann["enabled"] == "0" ? $Language[20] : $Language[19]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . (isset($AppRequests[$Ann["aid"]]) && 0 < $AppRequests[$Ann["aid"]] ? "<a href = \"index.php?do=manage_applications&amp;act = view&amp;id = " . $Ann["aid"] . "\">" . number_format($AppRequests[$Ann["aid"]]) . "</a>" : 0) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" align = \"center\">\r\n\t\t\t\t<a href = \"index.php?do=manage_applications&amp;act = edit&amp;id = " . $Ann["aid"] . "\"><img src = \"images/tool_edit.png\" alt = \"" . escape_attr($Language[4]) . "\" title = \"" . escape_attr($Language[4]) . "\" border = \"0\" /></a> <a href = \"index.php?do=manage_applications&amp;act = delete&amp;id = " . $Ann["aid"] . "\"><img src = \"images/tool_delete.png\" alt = \"" . escape_attr($Language[5]) . "\" title = \"" . escape_attr($Language[5]) . "\" border = \"0\" /></a> \r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    } while ($Ann = $result->fetch(PDO::FETCH_ASSOC));
    echo "\r\n\t</table>\r\n\t" . $pagertop;
} else {
    echo showAlertErrorModern(str_replace("{1}", "index.php?do=manage_applications&amp;act = new", $Language[13]));
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
    return getStaffLanguageModern();
}

function checkStaffAuthentication()
{
    checkStaffAuthenticationModern();
}

function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . escape_html($Error) . "</div></div>";
}

function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . escape_html($message) . "</div></div>";
}

function logStaffAction($log)
{
    logStaffActionModern($log);
}

function function_148($selected)
{
    global $TSDatabase;
    if (!is_array($selected) && preg_match("@,@Uis", $selected)) {
        $selected = explode(",", $selected);
    }
    $result = $TSDatabase->query("SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.id = ? LIMIT 1", [$_SESSION["ADMIN_ID"]]);
    $currentUserPerms = $result ? $result->fetch(PDO::FETCH_ASSOC) : [];
    $count = 0;
    $userGroupsHtml = "\r\n\t<table>\r\n\t\t<tr>\t";
    $result = $TSDatabase->query("SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups WHERE `isbanned` = 'no' ORDER by disporder ASC");
    if ($result) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if (!($row["cansettingspanel"] == "yes" && $currentUserPerms["cansettingspanel"] != "yes" || $row["canstaffpanel"] == "yes" && $currentUserPerms["canstaffpanel"] != "yes" || $row["issupermod"] == "yes" && $currentUserPerms["issupermod"] != "yes")) {
                if ($count && $count % 8 == 0) {
                    $userGroupsHtml .= "</tr><tr>";
                }
                $userGroupsHtml .= "<td><input type = \"checkbox\" name = \"usergroups[]\" value = \"" . escape_attr((string)$row["gid"]) . "\"" . (is_array($selected) && count($selected) && in_array($row["gid"], $selected) ? " checked = \"checked\"" : "") . " /></td><td>" . str_replace("{username}", escape_html($row["title"]), $row["namestyle"]) . "</td>";
                $count++;
            }
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
        $paginationLinks = "\r\n\t<table cellpadding = \"0\" cellspacing = \"0\" border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style = \"float: left;\" id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" href = \"" . escape_attr($address) . "\" title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" href = \"" . escape_attr($address . $previousPageQuery) . "\" title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" href = \"" . escape_attr($address . "page=" . $nextPageNumber) . "\" title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" href = \"" . escape_attr($address . "page=" . $queryResult) . "\" title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
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
            $paginationLinks .= "<li><a class=\"smalltext\" href = \"" . escape_attr($address . ($currentPage != 1 ? "page=" . $currentPage : "")) . "\" title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a name = \"current\" class=\"current\" title = \"Showing results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a href = \"" . escape_attr($address . ($currentPage != 1 ? "page=" . $currentPage : "")) . "\" title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
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
    return str_replace("{username}", escape_html($username), $namestyle);
}
function sendPrivateMessage($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    global $TSDatabase;
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        $TSDatabase->query(
            "INSERT INTO messages (sender, receiver, added, subject, msg, unread, saved, location) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)",
            [$sender, $receiver, $subject, $msg, $unread, $saved, $location]
        );
        $TSDatabase->query("UPDATE users SET pmunread = pmunread + 1 WHERE `id` = ?", [$receiver]);
    }
}

?>