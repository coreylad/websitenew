<?php
if (!defined("IN_CRON")) {
    exit;
}
$deadtime = deadtime();
if ($xbt_active == "yes") {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE xbt_files_users SET `active` = 0 WHERE `mtime` < " . $deadtime . " AND `active` = 1");
    $CQueryCount++;
} else {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM peers WHERE UNIX_TIMESTAMP(last_action) < " . $deadtime);
    $CQueryCount++;
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE snatched SET $seeder = 'no' WHERE $seeder = 'yes' AND UNIX_TIMESTAMP(last_action) < " . $deadtime);
    $CQueryCount++;
}

?>