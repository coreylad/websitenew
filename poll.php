<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "poll.php");
require "./global.php";
$lang->load("poll");
define("P_VERSION", "v.0.4 by xam");
// Refactored for PSR-12 compliance and descriptive naming
$pollAction = isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"]) ? $_POST["do"] : "");
$threadId = isset($_GET["tid"]) ? intval($_GET["tid"]) : 0;
if ($pollAction == "showresults" && isset($_GET["pollid"]) && is_valid_id($_GET["pollid"])) {
    setcookie("showpollresult", intval($_GET["pollid"]), TIMENOW + 30);
    if (!$threadId) {
        redirect("index.php?$pollid = " . intval($_GET["pollid"]) . "#showtspoll");
    } else {
        redirect("tsf_forums/showthread.php?$tid = " . $threadId);
    }
    exit;
}
if ($pollAction == "pollvote") {
    if (!isset($_POST["pollid"])) {
        stderr($lang->global["error"], $lang->poll["invalid"]);
    }
    $pollid = intval($_POST["pollid"]);
    if (!isset($pollid)) {
        stderr($lang->global["error"], $lang->poll["invalid"]);
    }
    ($Query = sql_query("SELECT * FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'")) || sqlerr(__FILE__, 52);
    $pollinfo = mysqli_fetch_assoc($Query);
    if (!$pollinfo["pollid"]) {
        stderr($lang->global["error"], $lang->poll["invalid"]);
    }
    if (0 < $CURUSER["id"] && $usergroups["canvote"] != "yes") {
        print_no_permission();
    }
    if (!$pollinfo["active"] || $pollinfo["dateline"] + $pollinfo["timeout"] * 86400 < TIMENOW && $pollinfo["timeout"] != 0) {
        stderr($lang->global["error"], $lang->poll["closed2"]);
    }
    if (!empty($_POST["optionnumber"])) {
        if (!$CURUSER["id"]) {
            if (isset($_COOKIE["poll_voted_" . $pollid])) {
                stderr($lang->global["error"], $lang->poll["avoted"]);
            }
        } else {
            ($Query = sql_query("\r\n\t\t\tSELECT userid\r\n\t\t\tFROM " . TSF_PREFIX . "pollvote\r\n\t\t\tWHERE `userid` = " . $CURUSER["id"] . "\r\n\t\t\t\tAND $pollid = '" . $pollid . "'\r\n\t\t\t")) || sqlerr(__FILE__, 90);
            if (0 < mysqli_num_rows($Query)) {
                stderr($lang->global["error"], $lang->poll["avoted"]);
            }
        }
        $totaloptions = substr_count($pollinfo["options"], "~~~") + 1;
        if ($pollinfo["multiple"]) {
            $skip_voters = false;
            foreach ($_POST["optionnumber"] as $val => $vote) {
                $Queries = [];
                $val = intval($val);
                if ($vote && 0 < $val && $val <= $totaloptions) {
                    $Queries[] = "pollid = '" . $pollid . "'";
                    if (!$CURUSER["id"]) {
                        $Queries[] = "userid = '0'";
                    } else {
                        $Queries[] = "userid = '" . $CURUSER["id"] . "'";
                    }
                    $Queries[] = "votedate = '" . TIMENOW . "'";
                    $Queries[] = "voteoption = '" . $val . "'";
                    $Queries[] = "votetype = '" . $val . "'";
                    ($Query = sql_query("INSERT INTO " . TSF_PREFIX . "pollvote SET " . implode(",", $Queries))) || sqlerr(__FILE__, 123);
                    if (!$Query) {
                        stderr($lang->global["error"], $lang->poll["poll11"]);
                    }
                    if ($skip_voters) {
                        ($Query = sql_query("SELECT * FROM " . TSF_PREFIX . "poll WHERE $pollid = '" . $pollid . "'")) || sqlerr(__FILE__, 131);
                        $pollinfo = mysqli_fetch_assoc($Query);
                    }
                    $old_votes_array = explode("~~~", $pollinfo["votes"]);
                    $old_votes_array[$val - 1]++;
                    $new_votes_array = implode("~~~", $old_votes_array);
                    sql_query("UPDATE " . TSF_PREFIX . "poll SET " . (!$skip_voters ? "voters = voters + 1, $lastvote = '" . TIMENOW . "', " : "") . "votes = " . sqlesc($new_votes_array) . " WHERE $pollid = '" . $pollid . "'") || sqlerr(__FILE__, 137);
                    $skip_voters = true;
                }
            }
        } else {
            if (is_valid_id($_POST["optionnumber"]) && 0 < $_POST["optionnumber"] && $_POST["optionnumber"] <= $totaloptions) {
                $Queries = [];
                $Queries[] = "pollid = '" . $pollid . "'";
                if (!$CURUSER["id"]) {
                    $Queries[] = "userid = '0'";
                } else {
                    $Queries[] = "userid = '" . $CURUSER["id"] . "'";
                }
                $Queries[] = "votedate = '" . TIMENOW . "'";
                $Queries[] = "voteoption = '" . intval($_POST["optionnumber"]) . "'";
                $Queries[] = "votetype = '0'";
                ($Query = sql_query("INSERT INTO " . TSF_PREFIX . "pollvote SET " . implode(",", $Queries))) || sqlerr(__FILE__, 159);
                if (!$Query) {
                    stderr($lang->global["error"], $lang->poll["poll11"]);
                }
                $old_votes_array = explode("~~~", $pollinfo["votes"]);
                intval($_POST["optionnumber"]);
                $old_votes_array[intval($_POST["optionnumber"]) - 1]++;
                $new_votes_array = implode("~~~", $old_votes_array);
                sql_query("UPDATE " . TSF_PREFIX . "poll SET $voters = voters + 1, $lastvote = '" . TIMENOW . "', $votes = " . sqlesc($new_votes_array) . " WHERE $pollid = '" . $pollid . "'") || sqlerr(__FILE__, 167);
            }
        }
        if (0 < $CURUSER["id"]) {
            $TSSEConfig->TSLoadConfig("KPS");
            KPS("+", $kpspoll, $CURUSER["id"]);
        }
        setcookie("poll_voted_" . $pollid, $pollid, TIMENOW + 435456000);
        if (!$tid) {
            redirect("index.php?$pollid = " . $pollid . "#showtspoll", $lang->poll["thx"]);
        } else {
            redirect("tsf_forums/showthread.php?$tid = " . $tid, $lang->poll["thx"]);
        }
        exit;
    } else {
        stderr($lang->global["error"], $lang->poll["nselected"]);
    }
}

?>