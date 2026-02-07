<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/create_page.lang");
$Message = "";
$pagetitle = "";
$pagecontents = "";
$usergroups = [];
$filename = "";
$method = 1;
$advertising = 1;
$ftp_server = "";
$ftp_port = "";
$ftp_user = "";
$ftp_pass = "";
$ftp_path = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $pagetitle = isset($_POST["pagetitle"]) ? trim($_POST["pagetitle"]) : "";
    $pagecontents = isset($_POST["pagecontents"]) ? trim($_POST["pagecontents"]) : "";
    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
    $filename = isset($_POST["filename"]) ? trim($_POST["filename"]) : "";
    $method = isset($_POST["method"]) ? intval($_POST["method"]) : 1;
    $advertising = isset($_POST["advertising"]) ? intval($_POST["advertising"]) : 1;
    $ftp_server = isset($_POST["ftp_server"]) ? trim($_POST["ftp_server"]) : "";
    $ftp_port = isset($_POST["ftp_port"]) ? intval($_POST["ftp_port"]) : "";
    $ftp_user = isset($_POST["ftp_user"]) ? trim($_POST["ftp_user"]) : "";
    $ftp_pass = isset($_POST["ftp_pass"]) ? trim($_POST["ftp_pass"]) : "";
    $ftp_path = isset($_POST["ftp_path"]) ? trim($_POST["ftp_path"]) : "";
    if (empty($pagetitle) || empty($pagecontents) || empty($filename)) {
        $Message = showAlertError($Language[8]);
    } else {
        $Content = "<?php\r\n/*\r\n+--------------------------------------------------------------------------\r\n|   TS Special Edition v.8.0\r\n|   ========================================\r\n|   by xam\r\n|   (c) 2005 - 2020 Template Shares Services\r\n|   https://templateshares.net\r\n|   ========================================\r\n|   Web: https://templateshares.net\r\n|   Time: \$_ts_date_\r\n|   Signature Key: \$_ts_signature_key_\r\n|   Email: contact@templateshares.net\r\n|   TS SE IS NOT FREE SOFTWARE!\r\n+---------------------------------------------------------------------------\r\n*/";
        if ($usergroups && 0 < count($usergroups)) {
            $Content .= "\r\nrequire('./global.php');\n\r\ndefine('THIS_SCRIPT', '" . $filename . "');\n\r\nif (!in_array(\$CURUSER['usergroup'], array(" . implode(",", $usergroups) . ")))\n\r\n{\n\r\n\tprint_no_permission();\n\r\n}\n";
        } else {
            $Content .= "\r\ndefine('NO_LOGIN_REQUIRED', true);\n\r\nrequire('./global.php');\n\r\ndefine('THIS_SCRIPT', '" . $filename . "');\n";
        }
        if ($advertising == "2") {
            $Content .= "\r\ndefine('DISABLE_ADS', true);\n";
        }
        $Content .= "\r\nstdhead('" . $pagetitle . "');\n\r\necho '\n\r\n<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\n\r\n\t<tr>\n\r\n\t\t<td class=\"thead\">\n\r\n\t\t\t" . $pagetitle . "\n\r\n\t\t</td>\n\r\n\t</tr>\n\r\n\t<tr>\n\r\n\t\t<td>\n\r\n\t\t\t" . addslashes($pagecontents) . "\n\r\n\t\t</td>\n\r\n\t</tr>\n\r\n</table>';\n\r\nstdfoot();\n\r\n?>";
        switch ($method) {
            case "1":
                if (file_put_contents("./../" . $filename, $Content)) {
                    $Message = showAlertError($Language[10]);
                } else {
                    $Message = showAlertError($Language[18]);
                }
                break;
            case "2":
                header("Content-Description: File Transfer");
                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; $filename = " . basename($filename));
                header("Content-Transfer-Encoding: binary");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-$check = 0, pre-$check = 0");
                header("Pragma: public");
                header("Content-Length: " . strlen($Content));
                ob_clean();
                flush();
                echo $Content;
                exit;
                break;
            case "3":
                if ($conn_id = ftp_connect($ftp_server, $ftp_port)) {
                    if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
                        ftp_pasv($conn_id, true);
                        ftp_chdir($conn_id, $ftp_path);
                        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
                        $Result = mysqli_fetch_assoc($query);
                        $MAIN = unserialize($Result["content"]);
                        file_put_contents("./../" . $MAIN["cache"] . "/" . $filename, $Content);
                        if (ftp_put($conn_id, $filename, "./../" . $MAIN["cache"] . "/" . $filename, FTP_ASCII)) {
                            $Message = showAlertError("Successfully uploaded " . $filename . "\n");
                            @unlink("./../" . $MAIN["cache"] . "/" . $filename);
                        } else {
                            $Message = showAlertError("There was a problem while uploading " . $filename . "\n");
                        }
                    } else {
                        $Message = showAlertError("Couldn't connect as " . $ftp_user);
                    }
                    ftp_close($conn_id);
                } else {
                    $Message = showAlertError("Couldn't connect to " . $ftp_server);
                }
                break;
        }
    }
}
$showusergroups = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    $showusergroups .= "\r\n\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $UG["gid"] . "\"" . (in_array($UG["gid"], $usergroups) ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> " . strip_tags(str_replace("{username}", $UG["title"], $UG["namestyle"]), "<b><span><strong><em><i><u>") . "</label>\r\n\t</div>";
}
echo loadTinyMCEEditor(1, "exact", "pagecontents") . "\r\n\r\n<script $type = \"text/javascript\">\r\n\tfunction selected_method_type(selected)\r\n\t{\r\n\t\tif (selected != 3)\r\n\t\t{\r\n\t\t\tTSGetID(\"ftp_details\").style.$display = \"none\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"ftp_details\").style.$display = \"inline\";\r\n\t\t}\r\n\t}\r\n</script>\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=create_page\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"tborder\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"pagetitle\" $value = \"" . $pagetitle . "\" $style = \"width: 99%;\" /></td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[9] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"filename\" $value = \"" . $filename . "\" $style = \"width: 99%;\" /></td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[6] . "</td>\r\n\t\t<td class=\"alt1\"><textarea $name = \"pagecontents\" $id = \"pagecontents\" $style = \"width: 100%; height: 100px;\">" . $pagecontents . "</textarea> \r\n\t\t<p><a $href = \"javascript:toggleEditor('pagecontents');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[7] . "</td>\r\n\t\t<td class=\"alt1\">" . $showusergroups . "</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\"><b>" . $Language[15] . "</b></td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<select $name = \"advertising\">\r\n\t\t\t\t<option $value = \"1\"" . ($advertising == 1 ? " $selected = \"selected\"" : "") . ">" . $Language[16] . "</option>\r\n\t\t\t\t<option $value = \"2\"" . ($advertising == 2 ? " $selected = \"selected\"" : "") . ">" . $Language[17] . "</option>\r\n\t\t\t</select>\r\n\t\t</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\"><b>" . $Language[11] . "</b></td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<select $name = \"method\" $onchange = \"selected_method_type(this.value);\">\r\n\t\t\t\t<option $value = \"1\"" . ($method == 1 ? " $selected = \"selected\"" : "") . ">" . $Language[12] . "</option>\r\n\t\t\t\t<option $value = \"2\"" . ($method == 2 ? " $selected = \"selected\"" : "") . ">" . $Language[13] . "</option>\r\n\t\t\t\t<option $value = \"3\"" . ($method == 3 ? " $selected = \"selected\"" : "") . ">" . $Language[14] . "</option>\r\n\t\t\t</select>\r\n\t\t\t<br />\r\n\t\t\t<div $id = \"ftp_details\" $style = \"display: " . ($method == 3 ? "inline" : "none") . ";\">\r\n\t\t\t\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . $Language[19] . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"ftp_server\" $value = \"" . $ftp_server . "\" $style = \"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . $Language[20] . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"ftp_port\" $value = \"" . $ftp_port . "\" $style = \"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . $Language[21] . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"ftp_user\" $value = \"" . $ftp_user . "\" $style = \"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . $Language[22] . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input $type = \"password\" $name = \"ftp_pass\" $style = \"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . $Language[23] . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"ftp_path\" $value = \"" . $ftp_path . "\" $style = \"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</div>\r\n\t\t</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[3] . "\" /> <input $type = \"reset\" $value = \"" . $Language[4] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $var_81 = ob_get_contents();
    ob_end_clean();
    return $var_81;
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
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>