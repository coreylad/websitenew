<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('create_page');

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

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token');
    } else {
        $pagetitle = isset($_POST["pagetitle"]) ? trim($_POST["pagetitle"]) : "";
        $pagecontents = isset($_POST["pagecontents"]) ? trim($_POST["pagecontents"]) : "";
        $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
        $filename = isset($_POST["filename"]) ? trim($_POST["filename"]) : "";
        $method = isset($_POST["method"]) ? intval($_POST["method"]) : 1;
        $advertising = isset($_POST["advertising"]) ? intval($_POST["advertising"]) : 1;
        $ftp_server = isset($_POST["ftp_server"]) ? trim($_POST["ftp_server"]) : "";
        $ftp_port = isset($_POST["ftp_port"]) ? intval($_POST["ftp_port"]) : 0;
        $ftp_user = isset($_POST["ftp_user"]) ? trim($_POST["ftp_user"]) : "";
        $ftp_pass = isset($_POST["ftp_pass"]) ? trim($_POST["ftp_pass"]) : "";
        $ftp_path = isset($_POST["ftp_path"]) ? trim($_POST["ftp_path"]) : "";
        
        if (empty($pagetitle) || empty($pagecontents) || empty($filename)) {
            $Message = showAlertErrorModern($Language[8]);
        } else {
            try {
                $Content = "<?php\r\n/*\r\n+--------------------------------------------------------------------------\r\n|   TS Special Edition v.8.0\r\n|   ========================================\r\n|   by xam\r\n|   (c) 2005 - 2020 Template Shares Services\r\n|   https://templateshares.net\r\n|   ========================================\r\n|   Web: https://templateshares.net\r\n|   Time: \$_ts_date_\r\n|   Signature Key: \$_ts_signature_key_\r\n|   Email: contact@templateshares.net\r\n|   TS SE IS NOT FREE SOFTWARE!\r\n+---------------------------------------------------------------------------\r\n*/";
                
                if ($usergroups && count($usergroups) > 0) {
                    $Content .= "\r\nrequire('./global.php');\n\r\ndefine('THIS_SCRIPT', '" . $filename . "');\n\r\nif (!in_array(\$CURUSER['usergroup'], array(" . implode(",", array_map('intval', $usergroups)) . ")))\n\r\n{\n\r\n\tprint_no_permission();\n\r\n}\n";
                } else {
                    $Content .= "\r\ndefine('NO_LOGIN_REQUIRED', true);\n\r\nrequire('./global.php');\n\r\ndefine('THIS_SCRIPT', '" . $filename . "');\n";
                }
                
                if ($advertising === 2) {
                    $Content .= "\r\ndefine('DISABLE_ADS', true);\n";
                }
                
                $Content .= "\r\nstdhead('" . addslashes($pagetitle) . "');\n\r\necho '\n\r\n<table width=\"100%\" align=\"center\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">\n\r\n\t<tr>\n\r\n\t\t<td class=\"thead\">\n\r\n\t\t\t" . addslashes($pagetitle) . "\n\r\n\t\t</td>\n\r\n\t</tr>\n\r\n\t<tr>\n\r\n\t\t<td>\n\r\n\t\t\t" . addslashes($pagecontents) . "\n\r\n\t\t</td>\n\r\n\t</tr>\n\r\n</table>';\n\r\nstdfoot();\n\r\n?>";
                
                switch ($method) {
                    case 1:
                        if (file_put_contents("./../" . $filename, $Content)) {
                            $Message = showAlertSuccessModern($Language[10]);
                        } else {
                            $Message = showAlertErrorModern($Language[18]);
                        }
                        break;
                    case 2:
                        header("Content-Description: File Transfer");
                        header("Content-Type: application/octet-stream");
                        header("Content-Disposition: attachment; filename=" . basename($filename));
                        header("Content-Transfer-Encoding: binary");
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                        header("Pragma: public");
                        header("Content-Length: " . strlen($Content));
                        ob_clean();
                        flush();
                        echo $Content;
                        exit;
                        break;
                    case 3:
                        $conn_id = @ftp_connect($ftp_server, $ftp_port > 0 ? $ftp_port : 21);
                        if ($conn_id) {
                            if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
                                ftp_pasv($conn_id, true);
                                ftp_chdir($conn_id, $ftp_path);
                                
                                $result = $TSDatabase->query("SELECT content FROM ts_config WHERE configname = 'MAIN'");
                                $row = $result->fetch(PDO::FETCH_ASSOC);
                                $MAIN = unserialize($row["content"]);
                                
                                file_put_contents("./../" . $MAIN["cache"] . "/" . $filename, $Content);
                                if (ftp_put($conn_id, $filename, "./../" . $MAIN["cache"] . "/" . $filename, FTP_ASCII)) {
                                    $Message = showAlertSuccessModern("Successfully uploaded " . escape_html($filename));
                                    @unlink("./../" . $MAIN["cache"] . "/" . $filename);
                                } else {
                                    $Message = showAlertErrorModern("There was a problem while uploading " . escape_html($filename));
                                }
                            } else {
                                $Message = showAlertErrorModern("Couldn't connect as " . escape_html($ftp_user));
                            }
                            ftp_close($conn_id);
                        } else {
                            $Message = showAlertErrorModern("Couldn't connect to " . escape_html($ftp_server));
                        }
                        break;
                }
            } catch (Exception $e) {
                error_log('Create page error: ' . $e->getMessage());
                $Message = showAlertErrorModern('An error occurred while creating the page');
            }
        }
    }
}

$showusergroups = "";
try {
    $result = $TSDatabase->query("SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
    while ($UG = $result->fetch(PDO::FETCH_ASSOC)) {
        $showusergroups .= "\r\n\t<div style=\"margin-bottom: 3px;\">\r\n\t\t<label><input type=\"checkbox\" name=\"usergroups[]\" value=\"" . escape_attr((string)$UG["gid"]) . "\"" . (in_array($UG["gid"], $usergroups) ? " checked=\"checked\"" : "") . " style=\"vertical-align: middle;\" /> " . strip_tags(str_replace("{username}", $UG["title"], $UG["namestyle"]), "<b><span><strong><em><i><u>") . "</label>\r\n\t</div>";
    }
} catch (Exception $e) {
    error_log('Get usergroups error: ' . $e->getMessage());
}

echo loadTinyMCEEditor(1, "exact", "pagecontents") . "\r\n\r\n<script type=\"text/javascript\">\r\n\tfunction selected_method_type(selected)\r\n\t{\r\n\t\tif (selected != 3)\r\n\t\t{\r\n\t\t\tTSGetID(\"ftp_details\").style.display = \"none\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"ftp_details\").style.display = \"inline\";\r\n\t\t}\r\n\t}\r\n</script>\r\n\r\n" . $Message . "\r\n<form method=\"post\" action=\"index.php?do=create_page\">\r\n" . getFormTokenField() . "\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tborder\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t" . escape_html($Language[2]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . escape_html($Language[5]) . "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" name=\"pagetitle\" value=\"" . escape_attr($pagetitle) . "\" style=\"width: 99%;\" /></td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . escape_html($Language[9]) . "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" name=\"filename\" value=\"" . escape_attr($filename) . "\" style=\"width: 99%;\" /></td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\" valign=\"top\">" . escape_html($Language[6]) . "</td>\r\n\t\t<td class=\"alt1\"><textarea name=\"pagecontents\" id=\"pagecontents\" style=\"width: 100%; height: 100px;\">" . escape_html($pagecontents) . "</textarea> \r\n\t\t<p><a href=\"javascript:toggleEditor('pagecontents');\"><img src=\"images/tool_refresh.png\" border=\"0\" /></a></p></td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\" valign=\"top\">" . escape_html($Language[7]) . "</td>\r\n\t\t<td class=\"alt1\">" . $showusergroups . "</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\"><b>" . escape_html($Language[15]) . "</b></td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<select name=\"advertising\">\r\n\t\t\t\t<option value=\"1\"" . ($advertising === 1 ? " selected=\"selected\"" : "") . ">" . escape_html($Language[16]) . "</option>\r\n\t\t\t\t<option value=\"2\"" . ($advertising === 2 ? " selected=\"selected\"" : "") . ">" . escape_html($Language[17]) . "</option>\r\n\t\t\t</select>\r\n\t\t</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\" valign=\"top\"><b>" . escape_html($Language[11]) . "</b></td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<select name=\"method\" onchange=\"selected_method_type(this.value);\">\r\n\t\t\t\t<option value=\"1\"" . ($method === 1 ? " selected=\"selected\"" : "") . ">" . escape_html($Language[12]) . "</option>\r\n\t\t\t\t<option value=\"2\"" . ($method === 2 ? " selected=\"selected\"" : "") . ">" . escape_html($Language[13]) . "</option>\r\n\t\t\t\t<option value=\"3\"" . ($method === 3 ? " selected=\"selected\"" : "") . ">" . escape_html($Language[14]) . "</option>\r\n\t\t\t</select>\r\n\t\t\t<br />\r\n\t\t\t<div id=\"ftp_details\" style=\"display: " . ($method === 3 ? "inline" : "none") . ";\">\r\n\t\t\t\t<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . escape_html($Language[19]) . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"ftp_server\" value=\"" . escape_attr($ftp_server) . "\" style=\"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . escape_html($Language[20]) . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"ftp_port\" value=\"" . escape_attr((string)$ftp_port) . "\" style=\"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . escape_html($Language[21]) . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"ftp_user\" value=\"" . escape_attr($ftp_user) . "\" style=\"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . escape_html($Language[22]) . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input type=\"password\" name=\"ftp_pass\" style=\"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\"><b>" . escape_html($Language[23]) . "</b></td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"ftp_path\" value=\"" . escape_attr($ftp_path) . "\" style=\"width: 300px;\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t</div>\r\n\t\t</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . escape_attr($Language[3]) . "\" /> <input type=\"reset\" value=\"" . escape_attr($Language[4]) . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";

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
