<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("PG_VERSION", "2.0 by xam");
define("DISABLE_ADS", true);
define("THIS_SCRIPT", "payment_gateway.php");
$method = isset($_GET["method"]) ? trim(strtolower($_GET["method"])) : "";
$prodcode = isset($_GET["prodcode"]) ? trim(urldecode($_GET["prodcode"])) : "";
$pin = isset($_GET["pin"]) ? trim(urldecode($_GET["pin"])) : "";
$orderno = isset($_GET["orderno"]) ? 0 + $_GET["orderno"] : "";
$appcode = isset($_GET["appcode"]) ? 0 + $_GET["appcode"] : "";
if ($prodcode && $pin && $appcode) {
    $method = "daopay";
}
if (!$method) {
    exit;
}
if ($method == "daopay") {
    require "./global.php";
} else {
    define("NO_LOGIN_REQUIRED", true);
    require "./global.php";
}
$lang->load("donate");
$TSSEConfig->TSLoadConfig("PAYPAL");
if (isset($protectedusergroups) && $protectedusergroups) {
    $protectedusergroups = explode(",", $protectedusergroups);
} else {
    $protectedusergroups = [];
}
$Query = sql_query("SELECT * FROM ts_subscriptions_api WHERE method = " . sqlesc($method));
if (mysqli_num_rows($Query)) {
    $API = mysqli_fetch_assoc($Query);
    $log = "";
    $paymentFinished = false;
    switch ($method) {
        case "paymentwall":
            define("SECRET", $API["secretkey2"]);
            define("IP_WHITELIST_CHECK_ACTIVE", true);
            define("CREDIT_TYPE_CHARGEBACK", 2);
            $ipsWhitelist = ["174.36.92.186", "174.36.96.66", "174.36.92.187", "174.36.92.192", "174.37.14.28", "178.132.211.102"];
            $uid = isset($_GET["uid"]) ? $_GET["uid"] : NULL;
            $item_number = isset($_GET["goodsid"]) ? $_GET["goodsid"] : NULL;
            $slength = isset($_GET["slength"]) ? $_GET["slength"] : NULL;
            $speriod = isset($_GET["speriod"]) ? $_GET["speriod"] : NULL;
            $type = isset($_GET["type"]) ? $_GET["type"] : NULL;
            $ref = isset($_GET["ref"]) ? $_GET["ref"] : NULL;
            $sig = isset($_GET["sig"]) ? $_GET["sig"] : NULL;
            $sign_version = isset($_GET["sign_version"]) ? $_GET["sign_version"] : NULL;
            $result = false;
            $errors = [];
            if (!empty($uid) && !empty($item_number) && isset($type) && !empty($ref) && !empty($sig)) {
                if (empty($sign_version) || $sign_version <= 1) {
                    $signatureParams = ["uid" => $uid, "goodsid" => $item_number, "slength" => $slength, "speriod" => $speriod, "type" => $type, "ref" => $ref];
                } else {
                    $signatureParams = [];
                    foreach ($_GET as $param => $value) {
                        $signatureParams[$param] = $value;
                    }
                    unset($signatureParams["sig"]);
                }
                $signatureCalculated = calculatePingbackSignature($signatureParams, SECRET, $sign_version);
                if (!IP_WHITELIST_CHECK_ACTIVE || in_array($_SERVER["REMOTE_ADDR"], $ipsWhitelist)) {
                    if ($sig == $signatureCalculated) {
                        if ($type == CREDIT_TYPE_CHARGEBACK) {
                            $Query = sql_query("SELECT p.*, u.donoruntil, u.username, u.usergroup, u.oldusergroup FROM ts_subscriptions_payments p INNER JOIN users u ON (p.uid=u.id) WHERE p.item = " . sqlesc($item_number) . " AND p.completed = 1");
                            if (mysqli_num_rows($Query)) {
                                $SP = mysqli_fetch_assoc($Query);
                                $Query = sql_query("SELECT * FROM ts_subscriptions WHERE sid = \"" . $SP["sid"] . "\" AND active = 1");
                                if (mysqli_num_rows($Query)) {
                                    $Sub = mysqli_fetch_assoc($Query);
                                    $result = true;
                                    $SaveLog = [];
                                    foreach ($_POST as $key => $value) {
                                        $SaveLog[] = $key . ": " . $value;
                                    }
                                    foreach ($_GET as $key => $value) {
                                        $SaveLog[] = $key . ": " . $value;
                                    }
                                    sql_query("UPDATE ts_subscriptions_payments SET completed=0, updated = \"" . time() . "\", method = \"" . $method . "\", log = " . sqlesc(implode("|", $SaveLog)) . " WHERE item = " . sqlesc($item_number));
                                    $userquery = [];
                                    $userquery[] = "donated = IF(donated > " . $Sub["cost"] . ", donated-" . $Sub["cost"] . ", 0)";
                                    $userquery[] = "total_donated = IF(total_donated > " . $Sub["cost"] . ", total_donated-" . $Sub["cost"] . ", 0)";
                                    $userquery[] = "donor = 'no'";
                                    $userquery[] = "modcomment = CONCAT('" . get_date_time() . " - CREDIT_TYPE_CHARGEBACK: " . $Sub["title"] . "\\n', modcomment)";
                                    if (!in_array($SP["usergroup"], $protectedusergroups) && 0 < $Sub["usergroup"] && $SP["oldusergroup"]) {
                                        $userquery[] = "usergroup = '" . $SP["oldusergroup"] . "'";
                                    }
                                    if (0 < $Sub["seedbonus"]) {
                                        $userquery[] = "seedbonus = IF(seedbonus>" . $Sub["seedbonus"] . ", seedbonus-" . $Sub["seedbonus"] . ", 0)";
                                    }
                                    if (0 < $Sub["invites"]) {
                                        $userquery[] = "invites = IF(invites>" . $Sub["invites"] . ", invites-" . $Sub["invites"] . ", 0)";
                                    }
                                    if (0 < $Sub["uploaded"]) {
                                        $Sub["uploaded"] = $Sub["uploaded"] * 1024 * 1024 * 1024;
                                        $userquery[] = "uploaded = IF(uploaded>" . $Sub["uploaded"] . ", uploaded-" . $Sub["uploaded"] . ", 0)";
                                    }
                                    $userquery[] = "donoruntil = \"0000-00-00 00:00:00\"";
                                    if (0 < count($userquery)) {
                                        sql_query("UPDATE users SET " . implode(",", $userquery) . " WHERE id = '" . $SP["uid"] . "'");
                                    }
                                }
                            }
                        } else {
                            $Query = sql_query("SELECT p.*, u.donoruntil, u.username, u.usergroup FROM ts_subscriptions_payments p INNER JOIN users u ON (p.uid=u.id) WHERE p.item = " . sqlesc($item_number) . " AND p.completed = 0");
                            if (mysqli_num_rows($Query)) {
                                $SP = mysqli_fetch_assoc($Query);
                                $Query = sql_query("SELECT * FROM ts_subscriptions WHERE sid = \"" . $SP["sid"] . "\" AND active = 1");
                                if (mysqli_num_rows($Query)) {
                                    $Sub = mysqli_fetch_assoc($Query);
                                    $paymentFinished = true;
                                    $result = true;
                                } else {
                                    $errors["item"] = "Subscription not active!";
                                }
                            } else {
                                $errors["item"] = "Item not found or Already processed before!";
                            }
                        }
                    } else {
                        $errors["signature"] = "Signature is not valid!";
                    }
                } else {
                    $errors["whitelist"] = "IP not in whitelist!";
                }
            } else {
                $errors["params"] = "Missing parameters!";
            }
            if ($errors && $item_number) {
                $log = "";
                foreach ($errors as $_l => $_r) {
                    $log .= $_l . " => " . $_r . "\n";
                }
                sql_query("UPDATE ts_subscriptions_payments SET log = " . sqlesc($log) . ", updated = \"" . time() . "\" WHERE item = " . sqlesc($item_number));
            }
            if ($result) {
                echo "OK";
            } else {
                echo implode(" ", $errors);
            }
            break;
        case "paypal":
            $item_number = isset($_POST["item_number"]) ? $_POST["item_number"] : "";
            if ($item_number) {
                $paymentQuery = sql_query("SELECT p.*, u.donoruntil, u.username, u.usergroup FROM ts_subscriptions_payments p INNER JOIN users u ON (p.uid=u.id) WHERE p.item = " . sqlesc($item_number) . " AND p.completed = 0");
                if (mysqli_num_rows($paymentQuery)) {
                    $query = [];
                    $query[] = "cmd=_notify-validate";
                    foreach ($_POST as $key => $val) {
                        $query[] = $key . "=" . urlencode($val);
                    }
                    $query = implode("&", $query);
                    $result = "";
                    if (function_exists("curl_init") && ($ch = curl_init())) {
                        curl_setopt($ch, CURLOPT_URL, "https://www." . ($paypal_demo_mode == "yes" ? "sandbox." : "") . "paypal.com/cgi-bin/webscr");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Connection: Close"]);
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                        $result = curl_exec($ch);
                        curl_close($ch);
                    }
                    if ($result == "VERIFIED") {
                        $SP = mysqli_fetch_assoc($paymentQuery);
                        $subscriptionQuery = sql_query("SELECT * FROM ts_subscriptions WHERE sid = \"" . $SP["sid"] . "\" AND active = 1");
                        if (mysqli_num_rows($subscriptionQuery)) {
                            $Sub = mysqli_fetch_assoc($subscriptionQuery);
                            if (isset($_POST["tax"]) && 0 < $_POST["tax"]) {
                                $_POST["mc_gross"] -= $_POST["tax"];
                            }
                            $mc_gross = doubleval($_POST["mc_gross"]);
                            if (doubleval($Sub["cost"]) <= $mc_gross && strtoupper($_POST["mc_currency"]) == strtoupper($Sub["currency"])) {
                                if ($_POST["payment_status"] == "Completed" && in_array($_POST["txn_type"], ["web_accept", "subscr_payment"])) {
                                    $paymentFinished = true;
                                } else {
                                    $log = "Payment is not completed yet.. Pending or Reserval.";
                                }
                            } else {
                                $log = "Invalid Cost.";
                            }
                        } else {
                            $log = "Subscription not active!";
                        }
                        ____setResponseHeader("200 OK");
                        $responseHeaderSet = true;
                    } else {
                        $log = "Invalid Payment. -*-DETAILED LOG-*- Result: " . $result . " - POST[email] = " . (isset($_POST["receiver_email"]) ? $_POST["receiver_email"] : "") . " - API[email] = " . (isset($API["email"]) ? $API["email"] : "");
                    }
                    if ($log && $item_number) {
                        sql_query("UPDATE ts_subscriptions_payments SET log = " . sqlesc($log) . ", updated = \"" . time() . "\" WHERE item = " . sqlesc($item_number));
                    }
                    if (!isset($responseHeaderSet)) {
                        ____setResponseHeader("503 Service Unavailable");
                    }
                }
            }
            break;
        case "daopay":
            $item_number = md5($CURUSER["id"] . $prodcode);
            if ($appcode == $API["secretkey"]) {
                $handle = fopen("https://daopay.com/svc/pincheck?appcode=" . $appcode . "&prodcode=" . urlencode($prodcode) . "&pin=" . urlencode($pin), "r");
                if ($handle) {
                    $reply = fgets($handle);
                    if (substr($reply, 0, 2) == "ok") {
                        $Query = sql_query("SELECT p.*, u.donoruntil, u.username, u.usergroup FROM ts_subscriptions_payments p INNER JOIN users u ON (p.uid=u.id) WHERE p.item = " . sqlesc($item_number) . " AND p.completed = 0");
                        if (mysqli_num_rows($Query)) {
                            $SP = mysqli_fetch_assoc($Query);
                            $Query = sql_query("SELECT * FROM ts_subscriptions WHERE sid = \"" . $SP["sid"] . "\" AND active = 1");
                            if (mysqli_num_rows($Query)) {
                                $Sub = mysqli_fetch_assoc($Query);
                                $paymentFinished = true;
                            } else {
                                $log = "Subscription not active!";
                            }
                        } else {
                            $log = "Item not found or Already processed before!";
                        }
                    } else {
                        $log = "PIN code invalid/expired!";
                    }
                } else {
                    $log = "Connection error: Can not connect to Daopay!";
                }
            } else {
                $log = "Invalid Application Code!";
            }
            if ($log && $item_number) {
                sql_query("UPDATE ts_subscriptions_payments SET log = " . sqlesc($log) . ", updated = \"" . time() . "\" WHERE item = " . sqlesc($item_number));
            }
            redirect("donate.php?act=thanks");
            exit;
            break;
        default:
            if (isset($paymentFinished) && $paymentFinished && isset($item_number) && $item_number) {
                $SaveLog = [];
                foreach ($_POST as $key => $value) {
                    $SaveLog[] = $key . ": " . $value;
                }
                foreach ($_GET as $key => $value) {
                    $SaveLog[] = $key . ": " . $value;
                }
                sql_query("UPDATE ts_subscriptions_payments SET completed = 1, updated = \"" . time() . "\", method = \"" . $method . "\", log = " . sqlesc(implode("|", $SaveLog)) . " WHERE item = " . sqlesc($item_number));
                $userquery = [];
                $userquery[] = "donated = '" . $Sub["cost"] . "'";
                $userquery[] = "total_donated = total_donated + '" . $Sub["cost"] . "'";
                $userquery[] = "donor = 'yes'";
                $userquery[] = "modcomment = CONCAT('" . get_date_time() . " - " . $Sub["title"] . "\\n', modcomment)";
                if (!in_array($SP["usergroup"], $protectedusergroups)) {
                    if (0 < $Sub["usergroup"]) {
                        $userquery[] = "usergroup = '" . $Sub["usergroup"] . "'";
                    }
                    $userquery[] = "oldusergroup = '" . $SP["usergroup"] . "'";
                }
                if (0 < $Sub["seedbonus"]) {
                    $userquery[] = "seedbonus = seedbonus + '" . $Sub["seedbonus"] . "'";
                }
                if (0 < $Sub["invites"]) {
                    $userquery[] = "invites = invites + '" . $Sub["invites"] . "'";
                }
                if (0 < $Sub["uploaded"]) {
                    $userquery[] = "uploaded = uploaded + '" . $Sub["uploaded"] * 1024 * 1024 * 1024 . "'";
                }
                $donoruntil = $Sub["length"] * ($Sub["lengthtype"] == "days" ? 1 : ($Sub["lengthtype"] == "weeks" ? 7 : ($Sub["lengthtype"] == "months" ? 30 : 365)));
                if ($SP["donoruntil"] == "0000-00-00 00:00:00") {
                    $donoruntil = get_date_time(gmtime() + $donoruntil * 86400);
                    $userquery[] = "donoruntil = '" . $donoruntil . "'";
                } else {
                    $userquery[] = "donoruntil = ADDDATE(donoruntil, INTERVAL " . $donoruntil . " DAY)";
                }
                if (0 < count($userquery)) {
                    sql_query("UPDATE users SET " . implode(",", $userquery) . " WHERE id = '" . $SP["uid"] . "'");
                    require_once INC_PATH . "/functions_pm.php";
                    send_pm($SP["uid"], $lang->donate["thanks"], $lang->donate["subject"]);
                    sql_query("INSERT INTO funds (cash, user, added) VALUES (" . sqlesc($Sub["cost"]) . ", " . sqlesc(intval($SP["uid"])) . ", NOW())");
                    if (!$informuserids) {
                        $Query = sql_query("SELECT u.id, g.gid FROM users u INNER JOIN usergroups g ON (u.usergroup=g.gid) WHERE g.cansettingspanel = 'yes'");
                        while ($SM = mysqli_fetch_assoc($Query)) {
                            send_pm($SM["id"], "Username: [url=" . ts_seo($SP["uid"], $SP["username"]) . "]" . $SP["username"] . "[/url]\n\nDetails:\n" . $Sub["title"] . "\r\n\t\t\t\t\t\nAmount: " . $Sub["cost"] . " " . $Sub["currency"], "New Donation from " . $SP["username"]);
                        }
                    } else {
                        $informuserids = explode(",", $informuserids);
                        foreach ($informuserids as $iuserid) {
                            send_pm($iuserid, "Username: [url=" . ts_seo($SP["uid"], $SP["username"]) . "]" . $SP["username"] . "[/url]\n\nDetails:\n" . $Sub["title"] . "\nAmount: " . $Sub["cost"] . " " . $Sub["currency"], "New Donation from " . $SP["username"]);
                        }
                    }
                }
            }
    }
}
function ____setResponseHeader($status_code)
{
    header("HTTP/1.1 " . $status_code);
}
function calculatePingbackSignature($params, $secret, $version)
{
    $str = "";
    if ($version == 2) {
        ksort($params);
    }
    foreach ($params as $k => $v) {
        $str .= $k . "=" . $v;
    }
    $str .= $secret;
    return md5($str);
}

?>