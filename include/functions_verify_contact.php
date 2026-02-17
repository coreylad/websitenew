<?php

declare(strict_types=1);

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function verify_msn($msn)
{
    global $lang;
    if ($msn == "" || check_email($msn)) {
        return NULL;
    }
    stderr($lang->global["error"], $lang->im["e1"]);
}
function verify_skype($skype)
{
    global $lang;
    if ($skype == "" || preg_match("#^[a-z0-9_.,-]{6,32}\$#si", $skype)) {
        return NULL;
    }
    stderr($lang->global["error"], $lang->im["e2"]);
}

?>