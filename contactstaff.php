<?php
define("THIS_SCRIPT", "contactstaff.php");
require "./global.php";
$isGuest = !isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == "0" ? true : false;
define("STF_VERSION", "1.2");
include INC_PATH . "/functions_security.php";
$lang->load("contactstaff");
if (!$is_mod && !$isGuest) {
    $query = sql_query("SELECT added FROM staffmessages WHERE $sender = " . sqlesc($CURUSER["id"]) . " ORDER by added DESC LIMIT 1");
    if (0 < mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $last_staffmsg = $Result["added"];
        flood_check($lang->contactstaff["floodcomment"], $last_staffmsg);
    }
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $msgtext = isset($_POST["message"]) ? trim($_POST["message"]) : "";
    $subject = isset($_POST["subject"]) ? trim($_POST["subject"]) : "";
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $prvp = showPreview("message");
    if (isset($_POST["submit"])) {
        if (empty($msgtext) || empty($subject)) {
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        }
        if ($isGuest) {
            if (!check_email($email)) {
                stderr($lang->global["error"], $lang->contactstaff["invalid_email"]);
            }
            $msgtext .= "\nEmail: " . htmlspecialchars($email) . "\nIP: " . USERIPADDRESS . "\n" . htmlspecialchars($_SERVER["HTTP_USER_AGENT"]);
            if (isCaptchaEnabled()) {
                $checkcode = check_code(isset($_POST["imagestring"]) ? $_POST["imagestring"] : "", "", true, "", true);
                if (!$checkcode) {
                    $lang->load("contactus");
                    stderr($lang->global["error"], $lang->contactus["invalidimagecode"]);
                }
            }
        }
        $userid = $isGuest ? 0 : $CURUSER["id"];
        $message = sqlesc($msgtext);
        $subject = sqlesc($subject);
        sql_query("INSERT INTO staffmessages (sender, added, msg, subject) VALUES(" . $userid . ", NOW(), " . $message . ", " . $subject . ")") || sqlerr(__FILE__, 82);
        stderr($lang->contactstaff["contactstaff"], $lang->global["msgsend"]);
        exit;
    }
}
if (isset($_GET["subject"]) && $_GET["subject"] == "invalid_link" && isset($_GET["link"]) && substr($_GET["link"], 0, 7) == "http://") {
    $link = htmlspecialchars_uni($_GET["link"]);
    $link = str_replace("http://referhide.com/?$g = ", "", $link);
    $subject = sprintf($lang->contactstaff["invalidlink"], $link);
}
stdhead($lang->contactstaff["contactstaff"]);
$postoptionstitle = [];
$postoptions = [];
$extrasubject = "";
if ($isGuest) {
    if ($iv == "reCAPTCHA") {
        $imageCodeEditorParts = show_image_code_editor();
        $postoptionstitle = [1 => $imageCodeEditorParts[0]];
        $postoptions = [1 => $imageCodeEditorParts[1]];
    }
    if ($iv == "yes") {
        $imageCodeEditorParts = show_image_code_editor();
        $postoptionstitle = [1 => $imageCodeEditorParts[0], 2 => $imageCodeEditorParts[2]];
        $postoptions = [1 => $imageCodeEditorParts[1], 2 => $imageCodeEditorParts[3]];
    }
    $extrasubject = [$lang->contactstaff["email"] => "<input $type = \"text\" $name = \"email\" $style = \"width: 100%\" $value = \"" . (isset($email) && !empty($email) ? htmlspecialchars_uni($email) : "") . "\" $tabindex = \"2\" />"];
}
$returnto = isset($_GET["returnto"]) ? fix_url($_GET["returnto"]) : (isset($_SERVER["HTTP_REFERER"]) ? fix_url($_SERVER["HTTP_REFERER"]) : "index.php");
define("IN_EDITOR", true);
include INC_PATH . "/editor.php";
$str = show_notice($lang->contactstaff["info"]) . "\n<form $method = \"post\" $name = \"compose\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\n<input $type = \"hidden\" $name = \"returnto\" $value = \"" . $returnto . "\" />\n" . (!empty($prvp) ? $prvp : "") . insert_editor(true, isset($subject) ? $subject : "", isset($msgtext) ? $msgtext : "", $lang->contactstaff["contactstaff"], $lang->contactstaff["sendmessage"], $postoptionstitle, $postoptions, true, $extrasubject) . "\n</form>";
echo $str;
stdfoot();

?>