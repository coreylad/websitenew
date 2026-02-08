<?php
if (!defined("IN_CRON")) {
    exit;
}
if ($xbt_active == "yes") {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE `torrents` SET $flags = 1 WHERE $name = \"\"");
    $CQueryCount++;
}

?>