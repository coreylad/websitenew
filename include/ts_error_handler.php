<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font face='verdana' size='2' color='darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function TSSEErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $BASEURL;
    global $is_mod;
    global $logphperrors;
    global $staffcp_path;
    switch ($errno) {
        case 8:
        case 1024:
            $errors = "PHP Notice";
            break;
        case 2:
        case 512:
            $errors = "PHP Warning";
            break;
        case 1:
        case 256:
            $errors = "PHP Fatal Error";
            break;
        default:
            $errors = "PHP Unknown";
            $DetectedError = sprintf("<div style=\"border:solid 1px #CC0000;  background:#F7CBCA url(" . $BASEURL . "/" . $staffcp_path . "/images/alert.png) 8px 6px no-repeat; \tcolor:#CC0000; font-weight:bold; padding:4px; text-align:center;\"><b>%s</b>: %s in <b>%s</b> on line <b>%d</b></div>\n", $errors, $errstr, basename($errfile), $errline);
            $GLOBALS["PHP_ERROR"] = $is_mod && ($logphperrors == "onlyshow" || $logphperrors == "all") ? $DetectedError : "";
            if ($logphperrors == "savelogs" || $logphperrors == "all") {
                write_log($DetectedError);
            }
            unset($DetectedError);
    }
}

?>