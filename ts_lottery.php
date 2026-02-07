<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_lottery.php");
require "./global.php";
if (!isset($CURUSER)) {
    print_no_permission();
}
define("L_VERSION", "1.0 by xam");
$TSSEConfig->TSLoadConfig("LOTTERY");
$lang->load("ts_lottery");
$act = isset($_GET["act"]) ? $_GET["act"] : (isset($_POST["act"]) ? $_POST["act"] : "");
$error = [];
$lottery_title = sprintf($lang->ts_lottery["title"], $SITENAME);
$userid = $is_mod && isset($_GET["userid"]) && is_valid_id($_GET["userid"]) ? intval($_GET["userid"]) : intval($CURUSER["id"]);
if ($lottery_begin_date != "" && $lottery_end_date != "" && $lottery_end_date < get_date_time()) {
    $LOTTERY["lottery_enabled"] = $lottery_enabled;
    $LOTTERY["lottery_allowed_usergroups"] = $lottery_allowed_usergroups;
    $LOTTERY["lottery_ticket_amount"] = $lottery_ticket_amount;
    $LOTTERY["lottery_winner_amount"] = $lottery_winner_amount;
    $LOTTERY["lottery_amount_type"] = $lottery_amount_type;
    $LOTTERY["lottery_max_tickets_per_user"] = $lottery_max_tickets_per_user;
    $LOTTERY["lottery_max_winners"] = $lottery_max_winners;
    $LOTTERY["lottery_begin_date"] = "";
    $LOTTERY["lottery_end_date"] = "";
    $LOTTERY["lottery_last_winners"] = 0;
    $LOTTERY["lottery_last_winners_amount"] = $lottery_winner_amount;
    sql_query("REPLACE INTO `ts_config` VALUES ('LOTTERY', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], serialize($LOTTERY)) . "')");
    $_winners_array = [];
    $__winner_amount = $lottery_winner_amount * ($lottery_amount_type == "MB" ? 1048576 : 1073741824);
    ($_query = sql_query("SELECT DISTINCT userid FROM `ts_lottery_tickets` ORDER BY RAND() LIMIT " . $lottery_max_winners)) || sqlerr(__FILE__, 55);
    if (0 < mysqli_num_rows($_query)) {
        $_subject = $lang->ts_lottery["msg_subject"];
        $_msg = sprintf($lang->ts_lottery["msg_body"], mksize($__winner_amount));
        require_once INC_PATH . "/functions_pm.php";
        while ($_winners = mysqli_fetch_assoc($_query)) {
            if (!in_array($_winners["userid"], $_winners_array)) {
                $_winners_array[] = $_winners["userid"];
                $modcomment = get_date_time() . " - " . sprintf($lang->ts_lottery["modcomment"], mksize($__winner_amount)) . "\n";
                sql_query("UPDATE users SET $uploaded = uploaded + " . $__winner_amount . ", $modcomment = CONCAT(" . sqlesc($modcomment . "") . ", modcomment) WHERE `id` = " . sqlesc($_winners["userid"])) || sqlerr(__FILE__, 67);
                send_pm($_winners["userid"], $_msg, $_subject);
            }
        }
    }
    $LOTTERY["lottery_enabled"] = $lottery_enabled;
    $LOTTERY["lottery_allowed_usergroups"] = $lottery_allowed_usergroups;
    $LOTTERY["lottery_ticket_amount"] = $lottery_ticket_amount;
    $LOTTERY["lottery_winner_amount"] = $lottery_winner_amount;
    $LOTTERY["lottery_amount_type"] = $lottery_amount_type;
    $LOTTERY["lottery_max_tickets_per_user"] = $lottery_max_tickets_per_user;
    $LOTTERY["lottery_max_winners"] = $lottery_max_winners;
    $LOTTERY["lottery_begin_date"] = "";
    $LOTTERY["lottery_end_date"] = "";
    $LOTTERY["lottery_last_winners"] = 0 < count($_winners_array) ? implode(",", $_winners_array) : "";
    $LOTTERY["lottery_last_winners_amount"] = $lottery_winner_amount;
    sql_query("REPLACE INTO `ts_config` VALUES ('LOTTERY', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], serialize($LOTTERY)) . "')");
    sql_query("TRUNCATE TABLE `ts_lottery_tickets`");
    redirect("ts_lottery.php");
    exit;
}
if ($lottery_enabled != "yes" && !$is_mod) {
    stderr($lang->global["error"], $lang->ts_lottery["disabled"]);
}
if ($lottery_allowed_usergroups != "ALL" && !TS_Match($lottery_allowed_usergroups, "[" . $CURUSER["usergroup"] . "]")) {
    stderr($lang->global["error"], $lang->ts_lottery["no_permission"]);
}
if ($lottery_begin_date != "" && $lottery_end_date != "") {
    $user_p_tickets = 0;
    ($query = sql_query("SELECT COUNT(ticketid) as user_p_tickets FROM ts_lottery_tickets WHERE `userid` = " . sqlesc($userid))) || sqlerr(__FILE__, 104);
    if (0 < mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $user_p_tickets = $Result["user_p_tickets"];
    }
    $user_available_tickets = $lottery_max_tickets_per_user - $user_p_tickets;
    if ($user_available_tickets < 0) {
        $user_available_tickets = 0;
    }
    $status_message = sprintf($lang->ts_lottery["ticket_status"], ts_nf($user_p_tickets), ts_nf($user_available_tickets));
    if ($act == "show_list") {
        ($query = sql_query("SELECT l.userid, u.username, u.uploaded, u.downloaded, u.options, g.namestyle FROM ts_lottery_tickets l LEFT JOIN users u ON (l.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid)")) || sqlerr(__FILE__, 123);
        if (0 < mysqli_num_rows($query)) {
            while ($showusers = mysqli_fetch_assoc($query)) {
                if (!isset($show_list_user_count[$showusers["username"]])) {
                    $show_list_user_count[$showusers["username"]] = 0;
                }
                $show_list_user_count[$showusers["username"]]++;
                $show_list_user_array[$showusers["username"]] = ["username" => "<a $href = \"" . ts_seo($showusers["userid"], $showusers["username"]) . "\">" . get_user_color($showusers["username"], $showusers["namestyle"]) . "</a>", "uploaded" => (TS_Match($showusers["options"], "I3") || TS_Match($showusers["options"], "I4")) && !$is_mod ? "0" : $showusers["uploaded"], "downloaded" => (TS_Match($showusers["options"], "I3") || TS_Match($showusers["options"], "I4")) && !$is_mod ? "0" : $showusers["downloaded"], "ratio" => (TS_Match($showusers["options"], "I3") || TS_Match($showusers["options"], "I4")) && !$is_mod ? "0" : (0 < $showusers["downloaded"] ? number_format($showusers["uploaded"] / $showusers["downloaded"], 2) : "0"), "userid" => $showusers["userid"]];
            }
            $show_list = "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\" $colspan = \"5\" $align = \"center\">" . $lang->ts_lottery["show_list_title"] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"20%\">" . $lang->ts_lottery["owner_name"] . "</td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"20%\">" . $lang->ts_lottery["uploaded"] . "</td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"20%\">" . $lang->ts_lottery["downloaded"] . "</td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"20%\">" . $lang->ts_lottery["ratio"] . "</td>\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"20%\">" . $lang->ts_lottery["total_tickets"] . "</td>\r\n\t\t\t</tr>";
            foreach ($show_list_user_array as $username => $user_array_value) {
                $bgcolor = $user_array_value["userid"] == $CURUSER["id"] ? " class=\"highlight\"" : "";
                $show_list .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"left\" $width = \"20%\"" . $bgcolor . ">" . $user_array_value["username"] . "</td>\r\n\t\t\t\t\t<td $align = \"center\" $width = \"20%\"" . $bgcolor . ">" . mksize($user_array_value["uploaded"]) . "</td>\r\n\t\t\t\t\t<td $align = \"center\" $width = \"20%\"" . $bgcolor . ">" . mksize($user_array_value["downloaded"]) . "</td>\r\n\t\t\t\t\t<td $align = \"center\" $width = \"20%\"" . $bgcolor . ">" . $user_array_value["ratio"] . "</td>\r\n\t\t\t\t\t<td $align = \"center\" $width = \"20%\"" . $bgcolor . ">" . ts_nf($show_list_user_count[$username]) . "</td>\r\n\t\t\t\t</tr>";
            }
            $show_list .= "\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t\t";
        }
    }
    if ($act == "purchase_ticket") {
        if ($lottery_end_date < get_date_time()) {
            $error[] = $lang->ts_lottery["end_of_date"];
        } else {
            $_ta = intval($_GET["ticket_amount"]);
            if (!is_valid_id($_ta) || $user_available_tickets < $_ta) {
                $error[] = $status_message;
            } else {
                $_uuploads = $CURUSER["uploaded"];
                $_total_tickets_cost = $_ta * $lottery_ticket_amount * ($lottery_amount_type == "MB" ? 1048576 : 1073741824);
                if ($_uuploads < $_total_tickets_cost) {
                    $_diff = mksize($_total_tickets_cost - $_uuploads);
                    $error[] = sprintf($lang->ts_lottery["cant_purchase"], ts_nf($_ta), mksize($_total_tickets_cost), mksize($_uuploads), $_diff);
                } else {
                    for ($i = 0; $i < $_ta; $i++) {
                        sql_query("INSERT INTO ts_lottery_tickets (userid) VALUES (" . $userid . ")") || sqlerr(__FILE__, 195);
                    }
                    sql_query("UPDATE users SET $uploaded = uploaded - " . $_total_tickets_cost . " WHERE `id` = " . sqlesc($userid)) || sqlerr(__FILE__, 197);
                    redirect("ts_lottery.php?$userid = " . $userid, $lang->ts_lottery["thank_you"]);
                    exit;
                }
            }
        }
    }
    $total_p_tickets = $total_p_users = 0;
    ($query = sql_query("SELECT COUNT(DISTINCT userid) as total_p_users, COUNT(ticketid) as total_p_tickets FROM ts_lottery_tickets")) || sqlerr(__FILE__, 207);
    if (0 < mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $total_p_users = $Result["total_p_users"];
        $total_p_tickets = $Result["total_p_tickets"];
    }
    if (0 < $total_p_tickets) {
        $_calc = 1 / $total_p_tickets * $user_p_tickets * 100;
        $your_win_ratio = @sprintf($lang->ts_lottery["your_win_ratio"], @number_format($_calc, 2)) . "%";
    } else {
        $your_win_ratio = @sprintf($lang->ts_lottery["your_win_ratio"], "--");
    }
}
$winners_array = [];
if ($lottery_last_winners != "") {
    ($query = sql_query("SELECT u.id, u.username, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE id IN (0," . $lottery_last_winners . ")")) || sqlerr(__FILE__, 230);
    if (0 < mysqli_num_rows($query)) {
        while ($user = mysqli_fetch_assoc($query)) {
            $winners_array[] = "<a $href = \"" . ts_seo($user["id"], $user["username"]) . "\">" . get_user_color($user["username"], $user["namestyle"]) . "</a>";
        }
    }
}
$lottery_total_winners = ts_nf(count($winners_array));
$lottery_last_winners_amount = mksize($lottery_amount_type == "MB" ? 1048576 * $lottery_last_winners_amount : 1073741824 * $lottery_last_winners_amount) . " " . $lang->ts_lottery["per_user"];
$show_purchase_button = "";
if ($lottery_begin_date != "" && $lottery_end_date != "" && $user_available_tickets <= $lottery_max_tickets_per_user) {
    $show_purchase_button = "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction EnterTicketAmount()\r\n\t\t{\r\n\t\t\$tCanPurchase = " . $user_available_tickets . ";\r\n\t\t\$tUserUploaded = " . $CURUSER["uploaded"] . ";\t\t\t\r\n\t\t\$tTicketAmount = prompt(\"" . $lang->ts_lottery["info"] . "\", \"1\");\r\n\t\t\tif (TicketAmount && !isNaN(TicketAmount) && parseInt(TicketAmount, 10) > 0)\r\n\t\t\t{\r\n\t\t\t\tif (TicketAmount > CanPurchase)\r\n\t\t\t\t{\r\n\t\t\t\t\talert(\"" . $lang->global["error"] . "\\n\\n" . strip_tags($status_message) . "\");\r\n\t\t\t\t\treturn false;\r\n\t\t\t\t}\r\n\t\t\t\telse\r\n\t\t\t\t{\r\n\t\t\t\t\$tTicketCost = TicketAmount*" . ($lottery_amount_type == "MB" ? 1048576 * $lottery_ticket_amount : 1073741824 * $lottery_ticket_amount) . ";\r\n\t\t\t\t\tif (UserUploaded < TicketCost)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\talert(\"" . $lang->ts_lottery["java_error"] . "\");\r\n\t\t\t\t\t\treturn false;\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\$tFormAction = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&$act = purchase_ticket&$ticket_amount = \"+TicketAmount;\r\n\t\t\t\t\t\tdocument.purchase.$action = FormAction;\r\n\t\t\t\t\t\treturn true;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&$act = purchase_ticket\" $name = \"purchase\" $onsubmit = \"return EnterTicketAmount();\">\r\n\t\t<input $type = \"hidden\" $name = \"act\" $value = \"purchase_ticket\">\r\n\t\t<input $type = \"hidden\" $name = \"userid\" $value = \"" . $userid . "\">\r\n\t\t<input $type = \"submit\" $value = \"" . $lang->ts_lottery["purchase_button"] . "\" class=button>\r\n\t</form>\r\n\t";
}
stdhead($lottery_title);
show_lottery_errors();
echo (isset($show_list) ? $show_list : "") . "\r\n<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" $colspan = \"4\" $align = \"center\">" . $lang->ts_lottery["open_raffles"] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $align = \"left\" $width = \"55%\">" . $lang->ts_lottery["ticket_limit"] . "</td>\r\n\t\t<td class=\"subheader\" $align = \"center\" $width = \"12%\">" . $lang->ts_lottery["start_date"] . "</td>\r\n\t\t<td class=\"subheader\" $align = \"center\" $width = \"12%\">" . $lang->ts_lottery["end_date"] . "</td>\r\n\t\t<td class=\"subheader\" $align = \"center\" $width = \"11%\">" . $lang->ts_lottery["purchase_button"] . "</td>\r\n\t</tr>";
if ($lottery_begin_date != "" && $lottery_end_date != "") {
    echo "\r\n\t<tr>\r\n\t\t<td $align = \"left\" $width = \"55%\">" . $status_message . ($your_win_ratio ? "<br />" . $your_win_ratio : "") . "</td>\r\n\t\t<td $align = \"center\" $width = \"12%\">" . my_datee($dateformat, $lottery_begin_date) . "<br />" . my_datee($timeformat, $lottery_begin_date) . "</td>\r\n\t\t<td $align = \"center\" $width = \"12%\">" . my_datee($dateformat, $lottery_end_date) . "<br />" . my_datee($timeformat, $lottery_end_date) . "</td>\r\n\t\t<td $align = \"center\" $width = \"11%\">" . $show_purchase_button . "</td>\t\t\r\n\t</tr>\r\n\t<tr><td $colspan = \"4\">" . sprintf($lang->ts_lottery["total_purchased"], ts_nf($total_p_tickets), ts_nf($total_p_users)) . " " . sprintf($lang->ts_lottery["show_list_button"], "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$userid = " . $userid . "&$act = show_list\">", "</a>") . "<br />" . sprintf($lang->ts_lottery["rules"], ts_nf($lottery_max_tickets_per_user), mksize($lottery_amount_type == "MB" ? 1048576 * $lottery_ticket_amount : 1073741824 * $lottery_ticket_amount), mksize($lottery_amount_type == "MB" ? 1048576 * $lottery_winner_amount : 1073741824 * $lottery_winner_amount)) . "</td></tr\r\n\t\r\n\t";
} else {
    echo "<tr><td $colspan = \"4\">" . $lang->ts_lottery["no_active_lottery"] . "</td></tr>";
}
echo "\r\n</table>\r\n<br />\r\n<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" $colspan = \"2\" $align = \"center\">" . $lang->ts_lottery["closed_raffles"] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"subheader\" $align = \"left\" $width = \"60%\">" . $lang->ts_lottery["last_lottery_winners"] . "</td>\r\n\t\t<td class=\"subheader\" $align = \"center\" $width = \"20%\">" . $lang->ts_lottery["last_earn"] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"left\" $width = \"45%\">" . sprintf($lang->ts_lottery["winners"], $lottery_total_winners) . " " . implode(", ", $winners_array) . "</td>\r\n\t\t<td $align = \"center\" $width = \"15%\">" . $lottery_last_winners_amount . "</td>\r\n\t</tr>\r\n</table>\r\n<br />\r\n";
stdfoot();
function show_lottery_errors()
{
    global $error;
    global $lang;
    if (0 < count($error)) {
        $errors = implode("<br />", $error);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>