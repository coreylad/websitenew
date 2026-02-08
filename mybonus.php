<?php
define("THIS_SCRIPT", "mybonus.php");
require "./global.php";
define("MB_VERSION", "v2.2 by xam");
if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
    print_no_permission();
}
$TSSEConfig->TSLoadConfig(["KPS", "ANNOUNCE"]);
require_once INC_PATH . "/functions_pm.php";
$lang->load("mybonus");
$SeedPoints = $CURUSER["seedbonus"];
$Userid = 0 + $CURUSER["id"];
$errors = [];
$messages = [];
if ($bonus == "disable" && !$is_mod || $bonus == "disablesave" && !$is_mod || $usergroups["canbonus"] != "yes") {
    stderr($lang->global["error"], $lang->mybonus["disabled"]);
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["BonusHash"]) && strlen($_POST["BonusHash"]) == 20) {
    $ID = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
    $Query = sql_query("SELECT id, bonusname, points, art, menge FROM bonus WHERE `id` = " . sqlesc($ID));
    if (mysqli_num_rows($Query) == 0) {
        $errors[] = $lang->mybonus["error1"];
    } else {
        $Result = mysqli_fetch_assoc($Query);
        if ($SeedPoints < $Result["points"]) {
            $errors[] = sprintf($lang->mybonus["error2"], $SeedPoints, $Result["points"]);
        } else {
            $KPSUSED = false;
            $DONTCALC = false;
            switch ($Result["art"]) {
                case "traffic":
                    sql_query("UPDATE users SET $uploaded = uploaded + " . $Result["menge"] . ", $seedbonus = IF(seedbonus < " . $Result["points"] . ", 0, seedbonus - " . $Result["points"] . ") WHERE `id` = " . sqlesc($Userid)) || sqlerr(__FILE__, 63);
                    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                        update_user($Userid, "Purchased item: " . $Result["bonusname"] . " for " . $Result["points"] . " points.");
                        $KPSUSED = true;
                    }
                    break;
                case "invite":
                    if ($kpsinvite != "yes") {
                        $errors[] = $lang->mybonus["error3"];
                    } else {
                        sql_query("UPDATE users SET $invites = invites + " . $Result["menge"] . ", $seedbonus = IF(seedbonus < " . $Result["points"] . ", 0, seedbonus - " . $Result["points"] . ") WHERE `id` = " . sqlesc($Userid)) || sqlerr(__FILE__, 77);
                        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                            update_user($Userid, "Purchased item: " . $Result["bonusname"] . " for " . $Result["points"] . " points.");
                            $KPSUSED = true;
                        }
                    }
                    break;
                case "title":
                    if (isset($_POST["update_title"]) && $_POST["update_title"] == "yes") {
                        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
                        if ($kpstitle != "yes") {
                            $errors[] = $lang->mybonus["error3"];
                        } else {
                            if (empty($title)) {
                                $errors[] = $lang->mybonus["error4"];
                            } else {
                                $TSSEConfig->TSLoadConfig("SIGNUP");
                                if (TS_Match($illegalusernames, $title)) {
                                    $errors[] = $lang->mybonus["error5"];
                                } else {
                                    if ($title == $CURUSER["title"]) {
                                        $errors[] = $lang->mybonus["error6"];
                                    } else {
                                        sql_query("UPDATE users SET $title = " . sqlesc(htmlspecialchars_uni($title)) . ", $seedbonus = IF(seedbonus < " . $Result["points"] . ", 0, seedbonus - " . $Result["points"] . ") WHERE `id` = " . sqlesc($Userid)) || sqlerr(__FILE__, 110);
                                        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                            update_user($Userid, "Purchased item: " . $Result["bonusname"] . " for " . $Result["points"] . " points.");
                                            $KPSUSED = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($kpstitle != "yes") {
                        $errors[] = $lang->mybonus["error3"];
                    } else {
                        if ($KPSUSED === false) {
                            $lang->mybonus["title"] = sprintf($lang->mybonus["title"], $SITENAME);
                            $BonusHash = mksecret(20);
                            stdhead($lang->mybonus["title"] . " - " . sprintf($lang->mybonus["left"], $SeedPoints));
                            show_mybonus_errors();
                            echo "\r\n\t\t\t\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"id\" $value = \"" . $Result["id"] . "\" />\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"BonusHash\" $value = \"" . $BonusHash . "\" />\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"update_title\" $value = \"yes\" />\r\n\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"thead\">" . ts_collapse("title") . $lang->mybonus["title"] . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t" . ts_collapse("title", 2) . "\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->mybonus["entertitle"] . " <input $type = \"text\" $size = \"50\" $name = \"title\" $value = \"" . htmlspecialchars_uni($CURUSER["title"]) . "\" class=\"inlineimg\" /> <input $type = \"submit\" $value = \"" . $lang->mybonus["purchase"] . "\" class=\"inlineimg\" /> <input $type = \"button\" $value = \"" . $lang->mybonus["cancel"] . "\" class=\"inlineimg\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "'); return false;\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</form>\r\n\t\t\t\t\t\t";
                            stdfoot();
                            exit;
                        }
                    }
                    break;
                case "class":
                    if ($kpsvip != "yes") {
                        $errors[] = $lang->mybonus["error3"];
                    } else {
                        if ($is_mod || $usergroups["isvipgroup"] == "yes") {
                            $errors[] = $lang->mybonus["error11"];
                        } else {
                            $vip_until = get_date_time(gmtime() + 2419200);
                            sql_query("REPLACE INTO ts_auto_vip (userid, vip_until, old_gid) VALUES ('" . $Userid . "', '" . $vip_until . "', '" . $CURUSER["usergroup"] . "')") || sqlerr(__FILE__, 167);
                            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                $KPSUSED = true;
                                sql_query("UPDATE users SET `usergroup` = 3, $oldusergroup = '" . $CURUSER["usergroup"] . "', $seedbonus = IF(seedbonus < " . $Result["points"] . ", 0, seedbonus - " . $Result["points"] . ") WHERE `id` = " . sqlesc($Userid)) || sqlerr(__FILE__, 171);
                                update_user($Userid, "Purchased item: " . $Result["bonusname"] . " for " . $Result["points"] . " points.");
                            } else {
                                $errors[] = $lang->global["dberror"];
                            }
                        }
                    }
                    break;
                case "gift_1":
                    if (isset($_POST["send_gift"]) && $_POST["send_gift"] == "yes") {
                        $GIFT = isset($_POST["gift"]) ? intval($_POST["gift"]) : "";
                        if ($GIFT && TS_Match($GIFT, "-")) {
                            print_no_permission();
                        }
                        $USERNAME = isset($_POST["username"]) ? trim($_POST["username"]) : "";
                        if ($kpsgift != "yes") {
                            $errors[] = $lang->mybonus["error3"];
                        } else {
                            if (empty($GIFT)) {
                                $errors[] = $lang->mybonus["error7"];
                            } else {
                                $SeedPoints = $SeedPoints - $Result["points"];
                                $DONTCALC = true;
                                if ($SeedPoints < $GIFT) {
                                    $errors[] = sprintf($lang->mybonus["error8"], $SeedPoints, $GIFT);
                                } else {
                                    if ($USERNAME == $CURUSER["username"]) {
                                        $errors[] = $lang->mybonus["error10"];
                                    } else {
                                        ($Query = sql_query("SELECT id FROM users WHERE `username` = " . sqlesc($USERNAME) . " AND $enabled = 'yes' AND $status = 'confirmed'")) || sqlerr(__FILE__, 212);
                                        if (mysqli_num_rows($Query) == 0) {
                                            $errors[] = $lang->mybonus["error9"];
                                        } else {
                                            $qResult = mysqli_fetch_assoc($Query);
                                            $SUSERID = $qResult["id"];
                                            sql_query("UPDATE users SET $seedbonus = seedbonus + " . $GIFT . " WHERE `id` = " . sqlesc($SUSERID)) || sqlerr(__FILE__, 222);
                                            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                                update_user($SUSERID, "Gift: " . $GIFT . " points from " . $CURUSER["username"]);
                                                send_pm($SUSERID, sprintf($lang->mybonus["giftmsg"], "[b]" . $USERNAME . "[/b]", "[URL=" . $BASEURL . "/userdetails.php?$id = " . $Userid . "][b]" . $CURUSER["username"] . "[/b][/URL]", $GIFT), $lang->mybonus["giftsubject"]);
                                                sql_query("UPDATE users SET $seedbonus = IF(seedbonus < " . ($Result["points"] + $GIFT) . ", 0, seedbonus - " . ($Result["points"] + $GIFT) . ") WHERE `id` = " . sqlesc($Userid)) || sqlerr(__FILE__, 227);
                                                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                                    update_user($Userid, "Purchased item: " . $Result["bonusname"] . " for " . $Result["points"] . " points. (" . $GIFT . " Points to " . $USERNAME . ")");
                                                    $KPSUSED = true;
                                                    $SeedPoints = $SeedPoints - $GIFT;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($kpsgift != "yes") {
                        $errors[] = $lang->mybonus["error3"];
                    } else {
                        if ($KPSUSED === false) {
                            $lang->mybonus["title"] = sprintf($lang->mybonus["title"], $SITENAME);
                            $BonusHash = mksecret(20);
                            stdhead($lang->mybonus["title"] . " - " . sprintf($lang->mybonus["left"], $SeedPoints));
                            show_mybonus_errors();
                            echo "\r\n\t\t\t\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"id\" $value = \"" . $Result["id"] . "\" />\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"BonusHash\" $value = \"" . $BonusHash . "\" />\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"send_gift\" $value = \"yes\" />\r\n\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"thead\" $colspan = \"2\">" . ts_collapse("title") . $lang->mybonus["title"] . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t" . ts_collapse("title", 2) . "\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $valign = \"top\" $align = \"right\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->mybonus["username"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"20\" $name = \"username\" $value = \"" . (isset($USERNAME) && $USERNAME ? htmlspecialchars_uni($USERNAME) : "") . "\" class=\"inlineimg\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $valign = \"top\" $align = \"right\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->mybonus["gift"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"20\" $name = \"gift\" $value = \"" . (isset($GIFT) && $GIFT ? htmlspecialchars_uni($GIFT) : "") . "\" class=\"inlineimg\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $colspan = \"2\" class=\"subheader\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->mybonus["purchase"] . "\" class=\"inlineimg\" /> <input $type = \"button\" $value = \"" . $lang->mybonus["cancel"] . "\" class=\"inlineimg\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "'); return false;\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</form>\r\n\t\t\t\t\t\t";
                            stdfoot();
                            exit;
                        }
                    }
                    break;
                case "warning":
                    if ($kpswarning != "yes") {
                        $errors[] = $lang->mybonus["error3"];
                    } else {
                        if ($CURUSER["timeswarned"] < 1) {
                            $errors[] = $lang->mybonus["error15"];
                        } else {
                            sql_query("UPDATE users SET `timeswarned` = IF(timeswarned < 1, 0, timeswarned - " . $Result["menge"] . "), $seedbonus = IF(seedbonus < " . $Result["points"] . ", 0, seedbonus - " . $Result["points"] . ") WHERE `id` = " . sqlesc($Userid)) || sqlerr(__FILE__, 302);
                            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                update_user($Userid, "Purchased item: " . $Result["bonusname"] . " for " . $Result["points"] . " points.");
                                $KPSUSED = true;
                            }
                        }
                    }
                    break;
                case "ratiofix":
                    if (isset($_POST["ratiofix"]) && $_POST["ratiofix"] == "yes") {
                        $TID = isset($_POST["torrentid"]) ? intval($_POST["torrentid"]) : 0;
                        if ($kpsratiofix != "yes" || $xbt_active == "yes") {
                            $errors[] = $lang->mybonus["error3"];
                        } else {
                            if (!is_valid_id($TID)) {
                                $errors[] = $lang->mybonus["error12"];
                            } else {
                                ($Query = sql_query("SELECT uploaded, downloaded, seedtime FROM snatched WHERE `torrentid` = '" . $TID . "' AND $finished = 'yes' AND $userid = '" . $Userid . "'")) || sqlerr(__FILE__, 325);
                                if (mysqli_num_rows($Query) == 0) {
                                    $errors[] = $lang->mybonus["error13"];
                                } else {
                                    $TSSEConfig->TSLoadConfig("HITRUN", 0);
                                    $MinSeedTime = $HITRUN["MinSeedTime"] * 60 * 60;
                                    unset($HITRUN);
                                    $SDetails = mysqli_fetch_assoc($Query);
                                    if ($SDetails["downloaded"] <= $SDetails["uploaded"] && $MinSeedTime <= $SDetails["seedtime"]) {
                                        $errors[] = $lang->mybonus["error14"];
                                    } else {
                                        sql_query("UPDATE snatched SET $uploaded = IF(uploaded < downloaded, downloaded, uploaded), $seedtime = IF(seedtime < " . $MinSeedTime . ", " . $MinSeedTime . ", seedtime) WHERE `torrentid` = '" . $TID . "' AND $finished = 'yes' AND $userid = '" . $Userid . "'") || sqlerr(__FILE__, 342);
                                        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                            sql_query("UPDATE users SET $seedbonus = IF(seedbonus < " . $Result["points"] . ", 0, seedbonus - " . $Result["points"] . ") WHERE `id` = " . sqlesc($Userid)) || sqlerr(__FILE__, 345);
                                            update_user($Userid, "Purchased item: " . $Result["bonusname"] . " for " . $Result["points"] . " points.");
                                            $KPSUSED = true;
                                        } else {
                                            $errors[] = $lang->global["dberror"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($kpsratiofix != "yes" || $xbt_active == "yes") {
                        $errors[] = $lang->mybonus["error3"];
                    } else {
                        if ($KPSUSED === false) {
                            $lang->mybonus["title"] = sprintf($lang->mybonus["title"], $SITENAME);
                            $BonusHash = mksecret(20);
                            stdhead($lang->mybonus["title"] . " - " . sprintf($lang->mybonus["left"], $SeedPoints));
                            show_mybonus_errors();
                            echo "\r\n\t\t\t\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"id\" $value = \"" . $Result["id"] . "\" />\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"BonusHash\" $value = \"" . $BonusHash . "\" />\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"ratiofix\" $value = \"yes\" />\r\n\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"thead\">" . ts_collapse("title") . $lang->mybonus["title"] . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t" . ts_collapse("title", 2) . "\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $valign = \"top\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->mybonus["torrentid"] . " <input $type = \"text\" $size = \"10\" $name = \"torrentid\" $value = \"" . (isset($TID) && $TID ? $TID : 0) . "\" class=\"inlineimg\" /> <input $type = \"submit\" $value = \"" . $lang->mybonus["purchase"] . "\" class=\"inlineimg\" /> <input $type = \"button\" $value = \"" . $lang->mybonus["cancel"] . "\" class=\"inlineimg\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "'); return false;\" />\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</form>\r\n\t\t\t\t\t\t";
                            stdfoot();
                            exit;
                        }
                    }
                    break;
                default:
                    if ($KPSUSED === true) {
                        if ($DONTCALC === false) {
                            $SeedPoints = $SeedPoints - $Result["points"];
                        }
                        $messages[] = sprintf($lang->mybonus["message1"], htmlspecialchars_uni($Result["bonusname"]));
                    }
            }
        }
    }
}
$extraquery = "";
if ($kpsmaxpoint < $CURUSER["seedbonus"] && !$is_mod) {
    $extraquery = " WHERE $art = 'gift_1'";
}
($Query = sql_query("SELECT * FROM bonus" . $extraquery . " ORDER BY id")) || sqlerr(__FILE__, 410);
if (mysqli_num_rows($Query) == 0) {
    stderr($lang->global["error"], $lang->mybonus["disabled"]);
}
$Count = 0;
$AvailableOptions = "<tr>";
for ($BonusHash = mksecret(20); $BOptions = mysqli_fetch_assoc($Query); $Count++) {
    $Alert = "";
    if ($SeedPoints < $BOptions["points"]) {
        $Alert = " $onclick = \"alert('" . sprintf($lang->mybonus["error2"], $SeedPoints, $BOptions["points"]) . "'); return false;\"";
    }
    if ($Count % 3 == 0) {
        $AvailableOptions .= "</tr><tr>";
    }
    $AvailableOptions .= "\r\n\t<td class=\"none\" $valign = \"top\">\r\n\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t<input $type = \"hidden\" $name = \"id\" $value = \"" . $BOptions["id"] . "\" />\r\n\t\t<input $type = \"hidden\" $name = \"BonusHash\" $value = \"" . $BonusHash . "\" />\r\n\t\t<table $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\" $width = \"290\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\">" . ts_collapse("subtitle" . $BOptions["id"]) . htmlspecialchars_uni($BOptions["bonusname"]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t" . ts_collapse("subtitle" . $BOptions["id"], 2) . "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $height = \"60\" $valign = \"top\"><div $align = \"justify\">" . htmlspecialchars_uni($BOptions["description"]) . "</div></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $valign = \"bottom\"><div $style = \"float: right;\"><input $type = \"submit\" $value = \"" . $lang->mybonus["purchase"] . "\" class=\"button\"" . $Alert . " /></div><div class=\"highlight\" $style = \"float: left;\">" . sprintf($lang->mybonus["required"], $BOptions["points"]) . "</div></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t</form>\r\n\t</td>\r\n\t";
}
$AvailableOptions .= "</tr>";
$lang->mybonus["title"] = sprintf($lang->mybonus["title"], $SITENAME);
$Main = "\r\n<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t<tr>\r\n\t\t<td class=\"thead\">" . ts_collapse("title") . $lang->mybonus["title"] . "</td>\r\n\t</tr>\r\n\t" . ts_collapse("title", 2) . "\r\n\t\t<tr>\r\n\t\t\t<td $valign = \"top\">\r\n\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t" . $AvailableOptions . "\r\n\t\t\t\t</table>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</tbody>\r\n</table>\r\n";
stdhead($lang->mybonus["title"] . " - " . sprintf($lang->mybonus["left"], $SeedPoints));
show_mybonus_errors();
show_mybonus_messages();
echo $Main;
stdfoot();
function show_mybonus_errors()
{
    global $errors;
    global $lang;
    if (0 < count($errors)) {
        $error = implode("<br />", $errors);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $error . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}
function show_mybonus_messages()
{
    global $messages;
    global $lang;
    if (0 < count($messages)) {
        $message = implode("<br />", $messages);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["sys_message"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"green\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $message . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}
function update_user($userid, $message)
{
    $bonuscomment = sqlesc(get_date_time() . " - " . $message . "\n");
    sql_query("UPDATE users SET `modcomment` = CONCAT(" . $bonuscomment . ", modcomment) WHERE `id` = " . sqlesc($userid));
}

?>