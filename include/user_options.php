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
function user_options($options, $field, $number = 0)
{
    if (!($options = strtoupper($options)) || !($field = strtolower($field))) {
        return false;
    }
    $array = ["parked" => "A1", "invisible" => "B1", "commentpm" => "C1", "avatars" => "D1", "showoffensivetorrents" => "E1", "popup" => "F1", "leftmenu" => "G1", "signatures" => "H1", "privacy" => "I" . $number, "acceptpms" => "K" . $number, "gender" => "L" . $number, "visitormsg" => "M" . $number, "autodst" => "N1", "dst" => "O1", "quickmenu" => "P1", "webseeder" => "R1", "newsletter" => "S1", "shoutbox" => "Q1", "fb-shoutbox" => "T1"];
    return TS_Match($options, $array[$field]) ? true : false;
}
function customizeprofilepermissions($Option)
{
    global $usergroups;
    $Options = ["cancustomizeprofile" => "0", "caneditfontfamily" => "1", "caneditfontsize" => "2", "caneditcolors" => "3"];
    $What = isset($Options[$Option]) ? $Options[$Option] : 0;
    return $usergroups["customizeprofile"][$What] == "1" ? true : false;
}

?>