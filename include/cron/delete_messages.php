<?php
if (!defined("IN_CRON")) {
    exit;
}
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM messages WHERE $sender = '0' AND $unread = 'no' AND $location = '1'");
$CQueryCount++;
if ($totalLdeleted = mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
    SaveLog("Total " . $totalLdeleted . " OLD system messages has been deleted.");
    $CQueryCount++;
}
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM messages WHERE $sender = '0' AND $unread = 'yes' AND $location = '1' AND UNIX_TIMESTAMP(added) < " . (TIMENOW - 604800));
$CQueryCount++;
if ($totalLdeleted2 = mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
    SaveLog("Total " . $totalLdeleted2 . " unread system messages has been deleted.");
    $CQueryCount++;
}
echo " ";

?>