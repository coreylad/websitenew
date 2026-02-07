<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function EmailBanned($against)
{
    global $aggressivecheckemail;
    $email = trim(strtolower($against));
    $sql = sql_query("SELECT value FROM bannedemails WHERE id = 1");
    $bannedemails = mysqli_fetch_assoc($sql);
    $bannedemails = $bannedemails["value"];
    if ($bannedemails !== NULL) {
        $bannedemails = @preg_split("/\\s+/", $bannedemails, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($bannedemails as $bannedemail) {
            if (check_email($bannedemail)) {
                $regex = "^" . @preg_quote($bannedemail, "#") . "\$";
            } else {
                $regex = @preg_quote($bannedemail, "#") . ($aggressivecheckemail == "yes" ? "" : "\$");
            }
            if (@preg_match("#" . $regex . "#i", $email)) {
                return true;
            }
        }
    }
    return false;
}

?>