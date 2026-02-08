<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function TSAjaxShoutBOT($text)
{
    return sql_query("INSERT INTO ts_shoutbox (date, shout, notice) VALUES ('" . TIMENOW . "', " . sqlesc($text) . ", '1')");
}

?>