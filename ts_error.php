<?php
define("TSE_VERSION", "1.0 by xam");
if (!defined("NO_LOGIN_REQUIRED")) {
    define("NO_LOGIN_REQUIRED", true);
}
if (!defined("GLOBAL_LOADED")) {
    define("THIS_SCRIPT", "ts_error.php");
    require_once "./global.php";
}
if (!function_exists("TS_Match")) {
    function TS_Match($string, $find)
    {
        return strpos($string, $find) === false ? false : true;
    }
}
$_SERVER["REQUEST_URI"] = !TS_Match($_SERVER["REQUEST_URI"], "takelogin") ? htmlspecialchars(urldecode($_SERVER["REQUEST_URI"])) : "login.php";
$errorid = isset($_GET["errorid"]) ? intval($_GET["errorid"]) : (defined("errorid") ? intval(errorid) : 0);
$errormessages = ["An unknown error has occured, please contact us.", "Request tainting attempted!", "In order to accept POST request originating from this domain, the admin must add this domain to the whitelist.", "Missing or Corrupted language file!", "Security Token Error! Please click on the following link and try again:<br /><a $href = \"" . $_SERVER["REQUEST_URI"] . "\">Refresh Page!</a>", 6 => "The server is too busy at the moment. Please try again later.<br />Click <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$t = " . time() . "\">here</a> to refresh this page.", 7 => "Prefetching is not allowed due to the various privacy issues that arise.", 8 => "Script Error! (SE-I). TS SE is not installed correctly. Please contact us to fix this issue. <a $href = \"https://templateshares.net/special/supportdesk.php?$act = submitticket\">https://templateshares.net/special/supportdesk.php?$act = submitticket</a>", 9 => "Your account has either been suspended or you have been banned from accessing this tracker.!", 10 => "The file(s) uploaded were too large to process.", 11 => "Your submission could not be processed because a security token was missing.<br /><br />If this occurred unexpectedly, please <a $href = \"contactus.php\"><b><u>inform the administrator</b></u></a> and describe the action you performed before you received this error.", 12 => "Your submission could not be processed because a security token was invalid.<br /><br />If this occurred unexpectedly, please <a $href = \"contactus.php\"><b><u>inform the administrator</b></u></a> and describe the action you performed before you received this error.", 13 => "Your submission could not be processed because the token has expired.<br /><br />Please push the back button and reload the previous window.", 400 => "<strong>400 Bad request</strong> -- This means that a request for a URL has been made but the server is not configured or capable of responding to it. This might be the case for URLs that are handed-off to a servlet engine where no default document or servlet is configured, or the HTTP request method is not implemented.", 401 => "<strong>401 Authorization Required</strong> -- \"Authorization is required to view this page. You have not provided valid username/password information.\" This means that the required username and/or password was not properly entered to access a password protected page or area of the web site space.", 403 => "<strong>403 Forbidden</strong> -- \"You are not allowed to access this page.\" (This error refers to pages that the server is finding, ie. they do exist, but the permissions on the file are not sufficient to allow the webserver to \"serve\" the page to any end user with or without a password.)", 404 => "<strong>404 Page Not Found</strong> -- \"The requested URL could not be found on this site.\" This means the page as it was entered in the URL does not exist on the server. This is usually caused by someone incorrectly typing the URL, or by the web master renaming or moving an existing page to a different directory.", 500 => "<strong>500 Internal Server Error</strong> -- \"The server encountered an internal error or misconfiguration and was unable to complete your request. Please contact the server administrator and inform them of the time the error occurred, and anything you might have done to produce this error.\"", 5 => "<b>MySQL ERROR:</b> Check server logs to see more information about this issue.", 1040 => "<b>MySQL ERROR 1040:</b> Too many connections. Your server needs optimization, please contact TSSE Staff.", 2002 => "<b>MySQL ERROR 2002 (HY000):</b> Can't connect to local MySQL server.", 3003 => "<b>MySQL ERROR 3003:</b> Could not select MySQL Database."];
$title = "IP: " . htmlspecialchars($_SERVER["REMOTE_ADDR"]) . " - Date: " . date("F j, Y, g:i a") . " - URL: " . $_SERVER["REQUEST_URI"];
if (!empty($errormessages[$errorid])) {
    if (isset($logphperrors) && $logphperrors == "yes" && isset($GLOBALS["DatabaseConnect"]) && $GLOBALS["DatabaseConnect"] && !in_array($errorid, [5, 6, 7, 8, 9])) {
        write_log($errormessages[$errorid] . " {" . $title . ($errorid == 7 ? htmlspecialchars($_SERVER["HTTP_X_MOZ"]) : "") . "}");
    }
    displayError($errormessages[$errorid]);
} else {
    displayError("An unknown error has occured, please contact us.");
}
function displayError($errormessage)
{
    if (defined("IN_AJAX")) {
        exit($errormessage);
    }
    exit("\r\n\t<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\r\n\t<html>\r\n\t\t<head>\r\n\t\t\t<meta $name = \"google-translate-customization\" $content = \"47f9aab10c6dfb8b-d581edbaa6eb3330-geb002d4be9788f7f-9\"></meta>\r\n\t\t\t<style>\r\n\t\t\t\tbody {overflow: hidden; background: #fff; padding: 0; margin: 0; font-family: verdana, geneva, lucida, \"lucida grande\", arial, helvetica, sans-serif;}\r\n\t\t\t\ta {text-decoration: none; color: #000; }\r\n\t\t\t\ta:hover{text-decoration: underline;}\r\n\t\t\t</style>\r\n\t\t</head>\r\n\t\t<body>\r\n\t\t\t<div $style = \"padding: 0; margin: 50px auto; width: 90%; min-width: 700px;\">\r\n\t\t\t\t<div $style = \"padding: 10px 0; margin-bottom: 3px; background: red; color: #fff; text-align: center; border: 1px solid #000; font-size: 11px;\">\r\n\t\t\t\t\t" . $errormessage . "\r\n\t\t\t\t</div>\r\n\t\t\t\t<div $id = \"google_translate_element\" $style = \"float: right;\"></div>\r\n\t\t\t</div>\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction googleTranslateElementInit() {\r\n\t\t\tnew google.translate.TranslateElement({pageLanguage: \"en\", layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, \"google_translate_element\");\r\n\t\t\t}\r\n\t\t\t</script><script $type = \"text/javascript\" $src = \"//translate.google.com/translate_a/element.js?$cb = googleTranslateElementInit\"></script>\r\n\t\t</body>\r\n\t</html>\r\n\t");
}

?>