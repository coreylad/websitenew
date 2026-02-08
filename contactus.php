<?php
define("NO_LOGIN_REQUIRED", true);
define("DISABLE_IPBAN_SYSTEM", true);
define("THIS_SCRIPT", "contactus.php");
require "./global.php";
$lang->load("contactus");
define("CU_VERSION", "0.5 by xam");
// Action to perform
$contactAction = isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"]) ? $_POST["do"] : "");
// Error messages for activation/validation
$contactActivationErrors = [];
// Whether to show the process confirmation
$showProcessConfirmation = false;
include_once INC_PATH . "/functions_security.php";
if ($contactAction == "process") {
    $contactUserEmail = trim($_POST["useremail"]);
    $contactMessage = trim($_POST["message"]);
    $contactSubject = trim($_POST["subject"]);
    $contactUserBrowser = $useragent;
    if (!check_email($contactUserEmail)) {
        $contactActivationErrors[] = $lang->contactus["invalidemail"];
    }
    if (empty($contactMessage) || strlen($contactMessage) < 10) {
        $contactActivationErrors[] = $lang->contactus["invalidmessage"];
    }
    if (empty($contactSubject) || strlen($contactSubject) < 3) {
        $contactActivationErrors[] = $lang->contactus["invalidsubject"];
    }
    if (isCaptchaEnabled()) {
        $captchaCheck = check_code(isset($_POST["imagestring"]) ? $_POST["imagestring"] : "", "", true, "", true);
        if (!$captchaCheck) {
            $contactActivationErrors[] = $lang->contactus["invalidimagecode"];
        }
    }
    if (count($contactActivationErrors) == 0) {
        $contactEmailMessage = $contactMessage . "\r\n\t\t_________________________________________________________________________________\r\n\t\tUser IP: " . htmlspecialchars_uni(USERIPADDRESS) . "\r\n\t\tUser Email: " . htmlspecialchars_uni($contactUserEmail) . "\r\n\t\tUser Browser: " . htmlspecialchars_uni($contactUserBrowser) . "\r\n\t\t";
        define("REPLY_TO", htmlspecialchars_uni(USERIPADDRESS) . " <" . htmlspecialchars_uni($contactUserEmail) . ">");
        $mailSent = sent_mail($contactemail, $contactSubject, $contactEmailMessage, "contactus");
        $showProcessConfirmation = true;
    }
}
stdhead($lang->contactus["header"]);
if ($showProcessConfirmation) {
    show_process();
    stdfoot();
    exit;
}
show_contactus_errors();
echo "\r\n<form $method = \"post\" $name = \"contactus\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\" $onsubmit = \"document.contactus.cbutton.$value = '" . $lang->contactus["pleasewait"] . "';document.contactus.cbutton.$disabled = true\">\r\n<input $type = \"hidden\" $name = \"do\" $value = \"process\" />\r\n<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t<tr>\r\n\t\t<td $align = \"left\" class=\"thead\" $colspan = \"2\">\r\n\t\t\t" . $lang->contactus["header"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"right\" $width = \"20%\" $valign = \"top\">\r\n\t\t\t<b>" . $lang->contactus["email"] . "</b>\r\n\t\t</td>\r\n\t\t<td $align = \"left\" $width = \"80%\" $valign = \"top\">\r\n\t\t\t<input $type = \"text\" $name = \"useremail\" $value = \"" . (isset($contactUserEmail) ? htmlspecialchars_uni($contactUserEmail) : "") . "\" $size = \"30\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"right\" $width = \"20%\" $valign = \"top\">\r\n\t\t\t<b>" . $lang->contactus["subject"] . "</b>\r\n\t\t</td>\r\n\t\t<td $align = \"left\" $width = \"80%\" $valign = \"top\">\r\n\t\t\t<input $type = \"text\" $name = \"subject\" $value = \"" . (isset($contactSubject) ? htmlspecialchars_uni($contactSubject) : "") . "\" $size = \"30\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"right\" $width = \"20%\" $valign = \"top\">\r\n\t\t\t<b>" . $lang->contactus["message"] . "</b>\r\n\t\t</td>\r\n\t\t<td $align = \"left\" $width = \"80%\" $valign = \"top\">\r\n\t\t\t<textarea $name = \"message\" $cols = \"100\" $rows = \"10\">" . (isset($contactMessage) ? htmlspecialchars_uni($contactMessage) : "") . "</textarea>\r\n\t\t</td>\r\n\t</tr>";
show_image_code();
echo "\r\n\t<tr>\r\n\t\t<td $colspan = \"2\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->contactus["button1"] . "\" $name = \"cbutton\" /> <input $type = \"reset\" $value = \"" . $lang->contactus["button2"] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
stdfoot();
function show_contactus_errors()
{
    global $contactActivationErrors;
    global $lang;
    if (0 < count($contactActivationErrors)) {
        $errors = implode("<br />", $contactActivationErrors);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}
function show_process()
{
    global $lang;
    echo "\r\n\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . $lang->contactus["processheader"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t" . $lang->contactus["processmessage"] . "\r\n\t\t\t\t\t</strong>\r\n\t\t\t\t</font>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>\r\n\t\t<br />\r\n\t";
}

?>