<?php

declare(strict_types=1);

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
// DEAD CODE: This entire file is never included or required anywhere in the codebase. The find_post() function is unused.
function find_post($pid)
{
    global $CURUSER;
    global $BASEURL;
    global $f_postsperpage;
    if (!$pid) {
        return false;
    }
    if ($CURUSER["postsperpage"] && $CURUSER["postsperpage"] <= 40) {
        $f_postsperpage = intval($CURUSER["postsperpage"]);
    }
    $query = sql_query("SELECT tid FROM " . TSF_PREFIX . "posts WHERE $pid = " . sqlesc($pid));
    if (mysqli_num_rows($query) < 1) {
        return false;
    }
    $Result = mysqli_fetch_assoc($query);
    $tid = $Result["tid"];
    if (!$tid) {
        return false;
    }
    $subres = sql_query("SELECT COUNT(*) FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($tid) . " AND pid < " . sqlesc($pid));
    $subrow = mysqli_fetch_row($subres);
    $count = $subrow[0];
    $page = floor($count / $f_postsperpage);
    if ($page < 2) {
        $page++;
    }
    return $BASEURL . "/tsf_forums/showthread.php?$tid = " . $tid . "&$page = " . $page . "&$scrollto = pid" . $pid;
}

?>