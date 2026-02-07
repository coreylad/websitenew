<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$UNREGISTERED = "\r\n<script $type = \"text/javascript\">\r\n\t//<![CDATA[\r\n\tvar $iv = \"" . $iv . "\";\r\n\tfunction showLoginBox(boxname)\r\n\t{\r\n\t\tif ($iv = = \"reCAPTCHA\")\r\n\t\t{\r\n\t\t\twindow.$location = \"login.php?\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tif (TSGetID(boxname).style.$display = = \"none\")\r\n\t\t\t{\r\n\t\t\t\tts_show(boxname);\r\n\t\t\t\tTSGetID(\"usernameloginphp\").focus();\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tts_hide(boxname);\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n\r\n\tjQuery(document).on(\"click\", \"#closebar\", function(e)\r\n\t{\r\n\t\tjQuery(\"#topbar\").hide();\r\n\t});\r\n\r\n\tjQuery(document).ready(function()\r\n\t{\r\n\t\tvar \$topbar = jQuery(\"#topbar\");\r\n\r\n\t\t\$topbar.show();\r\n\r\n\t\tjQuery(window).scroll(function()\r\n\t\t{\r\n\t\t\t\$topbar.stop().animate({\"marginTop\": (jQuery(window).scrollTop() + 3) + \"px\"}, \"slow\" );\r\n\t\t});\r\n\t});\r\n\t//]]>\r\n</script>";

?>