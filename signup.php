<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "signup.php");
define("NO_LOGIN_REQUIRED", true);
define("S_VERSION", "3.2 by xam");
define("DISABLE_ADS", true);
require "./global.php";
if (isset($CURUSER) && 0 < $CURUSER["id"]) {
    print_no_permission();
    exit;
}
if ($site_online != "yes") {
    stderr($lang->global["error"], $lang->global["signupdisabled"]);
}
$lang->load("signup");
$defaulttemplate = ts_template();
$referrer = isset($_POST["referrer"]) ? htmlspecialchars_uni($_POST["referrer"]) : (isset($_GET["referrer"]) ? htmlspecialchars_uni($_GET["referrer"]) : "");
$invitehash = isset($_POST["invitehash"]) ? htmlspecialchars_uni($_POST["invitehash"]) : (isset($_GET["invitehash"]) ? htmlspecialchars_uni($_GET["invitehash"]) : "");
$TSSEConfig->TSLoadConfig("SIGNUP");
if ($registration != "on" && $invitesystem != "on") {
    stderr($lang->global["error"], $lang->global["signupdisabled"]);
}
if (0 < intval($maxusers)) {
    $Count = mysqli_num_rows(sql_query("SELECT id FROM users WHERE id > 0"));
    if ($maxusers <= $Count) {
        stderr($lang->global["error"], $lang->global["signuplimitreached"]);
    }
}
if (0 < intval($maxip)) {
    $Count = mysqli_num_rows(sql_query("SELECT id FROM users WHERE $ip = " . sqlesc(USERIPADDRESS)));
    if (intval($maxip) <= $Count) {
        stderr($lang->global["error"], sprintf($lang->global["nodupeaccount"], htmlspecialchars_uni(USERIPADDRESS)), false);
    }
}
if (!empty($badcountries)) {
    function Detect_User_Country()
    {
        $country = "";
        $numbers = preg_split("/\\./", USERIPADDRESS);
        include INC_PATH . "/ip_files/" . $numbers[0] . ".php";
        $code = $numbers[0] * 16777216 + $numbers[1] * 65536 + $numbers[2] * 256 + $numbers[3];
        foreach ($ranges as $key => $value) {
            if ($key <= $code && $code <= $ranges[$key][0]) {
                $country = $ranges[$key][1];
                if ($country == "") {
                    $country = "unkown";
                }
                return $country;
            }
        }
    }
    $two_letter_country_code = @Detect_User_Country();
    $badcountries = @explode(",", $badcountries);
    if (@in_array(@strtoupper($two_letter_country_code), $badcountries)) {
        stderr($lang->global["error"], $lang->global["signupdisabled"]);
    }
}
if ($pd == "yes") {
    include INC_PATH . "/proxydetector.php";
    CheckForProxy(USERIPADDRESS);
}
if ($iv == "yes" || $iv == "reCAPTCHA") {
    function ts_session_nameiv()
    {
        $servername = isset($_SERVER["SERVER_NAME"]) && !empty($_SERVER["SERVER_NAME"]) ? trim($_SERVER["SERVER_NAME"]) : (isset($_SERVER["HTTP_HOST"]) && !empty($_SERVER["HTTP_HOST"]) ? trim($_SERVER["HTTP_HOST"]) : "tsse_session");
        return preg_replace("/[^a-zA-Z0-9_]/", "", $servername) . "_iv";
    }
    session_name(ts_session_nameiv());
    session_start();
    require TSDIR . "/iv/iv.php";
}
if (!isset($_GET["agree"])) {
    stdhead($lang->signup["agree1"]);
    echo "\r\n\t<form $method = \"get\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t<input $type = \"hidden\" $name = \"agree\" $value = \"yes\" />\r\n\t<input $type = \"hidden\" $name = \"invitehash\" $value = \"" . $invitehash . "\" />\r\n\t" . ($referrer ? "<input $type = \"hidden\" $name = \"referrer\" $value = \"" . $referrer . "\" />" : "") . "\r\n\t<table $width = \"100%\" $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $align = \"center\">\r\n\t\t\t\t" . $lang->signup["agree1"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t" . $lang->signup["agree2"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t<iframe $src = \"" . $BASEURL . "/useragreement.php?$skip = true\" $border = \"0\" $width = \"100%\" $height = \"300\"></iframe>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->signup["agree3"] . "\" />\r\n\t\t\t</tr>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
    stdfoot();
    exit;
}
$Errors = [];
if ("POST" == strtoupper($_SERVER["REQUEST_METHOD"])) {
    foreach ($_POST as $left => $right) {
        ${$left} = trim($right);
    }
    if (isset($username)) {
        function isvalidusername($username)
        {
            if (!preg_match("|[^a-z\\|A-Z\\|0-9]|", $username)) {
                return true;
            }
            return false;
        }
        if (!isvalidusername($username)) {
            $Errors[] = $lang->signup["validusername"];
        } else {
            if (strlen($username) < 3 || 12 < strlen($username)) {
                $Errors[] = $lang->signup["validusername2"];
            } else {
                ($UsernameQuery = sql_query("SELECT id FROM users WHERE $username = " . sqlesc($username))) || sqlerr(__FILE__, 184);
                if (0 < mysqli_num_rows($UsernameQuery)) {
                    $Errors[] = $lang->signup["usernamexists"];
                } else {
                    if (isset($illegalusernames) && $illegalusernames != "") {
                        $usernames = preg_split("/\\s+/", $illegalusernames, -1, PREG_SPLIT_NO_EMPTY);
                        if (is_array($usernames)) {
                            foreach ($usernames as $val) {
                                if (strpos(strtolower($username), strtolower($val)) !== false) {
                                    $Errors[] = $lang->signup["usernamexists"];
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        $Errors[] = $lang->signup["validusername"];
    }
    if (isset($password) && isset($password2)) {
        if ($password != $password2) {
            $Errors[] = $lang->signup["validpassword2"];
        } else {
            if (strlen($password) < 6 || 40 < strlen($password)) {
                $Errors[] = $lang->signup["validpassword3"];
            } else {
                if ($password == $username) {
                    $Errors[] = $lang->signup["validpassword4"];
                }
            }
        }
    } else {
        $Errors[] = $lang->signup["validpassword"];
    }
    if (isset($email)) {
        require_once INC_PATH . "/functions_EmailBanned.php";
        if (!check_email($email)) {
            $Errors[] = $lang->signup["validemail"];
        } else {
            if (EmailBanned($email)) {
                $Errors[] = $lang->signup["emailbanned"];
            } else {
                ($EmailQuery = sql_query("SELECT email FROM users WHERE $email = " . sqlesc($email))) || sqlerr(__FILE__, 247);
                if (0 < mysqli_num_rows($EmailQuery)) {
                    $Errors[] = $lang->signup["emailexists"];
                }
            }
        }
    } else {
        $Errors[] = $lang->signup["validemail"];
    }
    if ($r_secretquestion == "yes") {
        if (isset($passhint) && isset($hintanswer)) {
            if (!(in_array(intval($passhint), ["1", "2", "3"]) && 6 <= strlen($hintanswer) && $hintanswer != $username)) {
                $Errors[] = $lang->signup["validhintanswer4"];
            }
        } else {
            $Errors[] = $lang->signup["validhintanswer4"];
        }
    }
    if ($r_country == "yes") {
        if (isset($country)) {
            ($CountryQuery = sql_query("SELECT id FROM countries WHERE $id = " . sqlesc($country))) || sqlerr(__FILE__, 282);
            if (mysqli_num_rows($CountryQuery) == 0) {
                $Errors[] = $lang->signup["validcountry"];
            }
        } else {
            $Errors[] = $lang->signup["validcountry"];
        }
    }
    $ReferredBy = false;
    if ($r_referrer == "yes" && isset($referrer) && $referrer) {
        ($ReferrerQuery = sql_query("SELECT id FROM users WHERE $username = " . sqlesc($referrer))) || sqlerr(__FILE__, 299);
        if (0 < mysqli_num_rows($ReferrerQuery)) {
            $Result = mysqli_fetch_assoc($ReferrerQuery);
            $ReferredBy = $Result["id"];
        }
    }
    $defaultuseroptions = "A0B0C0D1E1F0G1H1I2K1L1M1N1O0P1R0S1Q0T0";
    if ($r_timezone == "yes") {
        if (isset($tzoffset) && isset($dst)) {
            if ($dst == "2") {
                $dst = "0";
                $autodst = "1";
            } else {
                if ($dst == "1") {
                    $dst = "1";
                    $autodst = "0";
                } else {
                    $dst = "0";
                    $autodst = "0";
                }
            }
            $defaultuseroptions = str_replace(["N1", "O0"], ["N" . $autodst, "O" . $dst], $defaultuseroptions);
        } else {
            $Errors[] = $lang->signup["validtimezone"];
        }
    }
    $birthday = "";
    if ($r_bday == "yes") {
        if (isset($day) && isset($month) && isset($year)) {
            $day = intval($day);
            $month = intval($month);
            $year = intval($year);
            if ($day && $month && $year) {
                $birthday = $day . "-" . $month . "-" . $year;
            } else {
                $Errors[] = $lang->signup["validbday"];
            }
        } else {
            $Errors[] = $lang->signup["validbday"];
        }
    }
    if ($r_gender == "yes") {
        if (isset($gender)) {
            $defaultuseroptions = str_replace("L1", "L" . ($gender == "male" ? "1" : "2"), $defaultuseroptions);
        } else {
            $Errors[] = $lang->signup["validgender"];
        }
    }
    $invited_by = "0";
    if ($registration != "on" && $invitesystem == "on") {
        if (isset($invitehash)) {
            ($InviteQuery = sql_query("SELECT inviter FROM invites WHERE $hash = " . sqlesc($invitehash))) || sqlerr(__FILE__, 377);
            if (mysqli_num_rows($InviteQuery)) {
                $Result = mysqli_fetch_assoc($InviteQuery);
                $invited_by = $Result["inviter"];
            } else {
                $Errors[] = $lang->signup["validinvitecode"];
            }
        } else {
            $Errors[] = $lang->signup["validinvitecode"];
        }
    }
    if (count($Errors) == 0) {
        $secret = mksecret();
        $passhash = md5($secret . $password . $secret);
        $editsecret = $verification == "admin" ? "" : mksecret();
        $uploaded = 0 < $autogigsignup ? $autogigsignup * 1024 * 1024 * 1024 : 0;
        $seedbonus = 0 < $autosbsignup ? $autosbsignup : 0;
        $usergroup = $_d_usergroup ? $_d_usergroup : 1;
        $invites = 0 + $invite_count;
        $country = isset($country) ? $country : 0;
        $tzoffset = isset($tzoffset) ? $tzoffset : $timezoneoffset;
        $newUserQuery = sql_query("INSERT INTO users (username, passhash, secret, email, status, added, ip, uploaded, country, tzoffset, invites, invited_by, seedbonus, usergroup, birthday, options) VALUES (" . sqlesc($username) . ", " . sqlesc($passhash) . ", " . sqlesc($secret) . ", " . sqlesc($email) . ", 'pending', NOW(), " . sqlesc(USERIPADDRESS) . ", " . sqlesc($uploaded) . ", " . sqlesc($country) . ", " . sqlesc($tzoffset) . ", " . sqlesc($invites) . ", " . sqlesc($invited_by) . ", " . sqlesc($seedbonus) . ", " . sqlesc($usergroup) . ", " . sqlesc($birthday) . ", " . sqlesc($defaultuseroptions) . ")");
        if ($newUserQuery && ($newuserid = mysqli_insert_id($GLOBALS["DatabaseConnect"]))) {
            if ($verification != "admin") {
                sql_query("REPLACE INTO ts_user_validation (editsecret, userid) VALUES (" . sqlesc($editsecret) . ", " . sqlesc($newuserid) . ")") || sqlerr(__FILE__, 412);
            }
            if (0 < $newuserid && $ReferredBy) {
                $credit = 107374182;
                sql_query("INSERT INTO referrals (uid,referring,credit) VALUES ('" . $ReferredBy . "', '" . $newuserid . "', '" . $credit . "')") || sqlerr(__FILE__, 418);
                sql_query("UPDATE users SET $uploaded = uploaded + " . $credit . " WHERE $id = '" . $ReferredBy . "'") || sqlerr(__FILE__, 419);
            }
            if ($registration != "on" && $invitesystem == "on") {
                sql_query("INSERT INTO friends VALUES (0," . sqlesc($newuserid) . ", " . sqlesc($invited_by) . ",'c')") || sqlerr(__FILE__, 424);
                sql_query("INSERT INTO friends VALUES (0," . sqlesc($invited_by) . ", " . sqlesc($newuserid) . ",'c')") || sqlerr(__FILE__, 425);
                sql_query("DELETE FROM invites WHERE $hash = " . sqlesc($invitehash)) || sqlerr(__FILE__, 426);
            }
            if ($r_secretquestion == "yes") {
                sql_query("REPLACE INTO ts_secret_questions (userid, passhint, hintanswer) VALUES ('" . $newuserid . "', '" . $passhint . "', " . sqlesc(md5($hintanswer)) . ")") || sqlerr(__FILE__, 431);
            }
            $psecret = md5($editsecret);
            $usern = htmlspecialchars_uni($username);
            if ($send_welcome_pm == "yes") {
                require_once INC_PATH . "/functions_pm.php";
                send_pm($newuserid, $send_welcome_pm_body, $send_welcome_pm_title);
            }
            if ($verification == "automatic") {
                redirect("confirm.php?$id = " . $newuserid . "&$secret = " . $psecret);
                exit;
            }
            if ($verification == "admin") {
                redirect("ok.php?$type = adminactivate");
                exit;
            }
            $body = sprintf($lang->signup["verifiyemailbody"], $usern, $BASEURL, $newuserid, $psecret, $SITENAME);
            sent_mail($email, sprintf($lang->signup["verifiyemailsubject"], $SITENAME), $body, "signup", false);
            redirect("ok.php?$type = signup&$email = " . urlencode(htmlspecialchars_uni($email)));
            exit;
        }
        $Errors[] = $lang->global["dberror"];
        $ShowErrors = "";
        foreach ($Errors as $Error) {
            $ShowErrors .= "<img $src = \"" . $pic_base_url . "warned.gif\" $alt = \"" . $lang->global["error"] . "\" $title = \"" . $lang->global["error"] . "\" $border = \"0\" class=\"inglineimg\" /> " . $Error;
        }
        $ShowErrors = show_notice($ShowErrors, true, $lang->signup["failed"]);
    } else {
        $ShowErrors = "";
        foreach ($Errors as $Error) {
            $ShowErrors .= "<p><img $src = \"" . $pic_base_url . "warned.gif\" $alt = \"" . $lang->global["error"] . "\" $title = \"" . $lang->global["error"] . "\" $border = \"0\" class=\"inglineimg\" /> " . $Error . "</p>";
        }
        $ShowErrors = show_notice($ShowErrors, true, $lang->signup["failed"]);
    }
}
$showsecretquestion = "";
if ($r_secretquestion == "yes") {
    $questions = [1 => $lang->signup["hr0"], 2 => $lang->signup["hr1"], 3 => $lang->signup["hr2"]];
    $options = "\r\n\t<select $name = \"passhint\" $id = \"passhint\">";
    foreach ($questions as $v => $q) {
        $options .= "\r\n\t\t<option $value = \"" . $v . "\"" . (isset($passhint) && $passhint == $v ? " $selected = \"selected\"" : "") . ">" . $q . "</option>";
    }
    $options .= "\r\n\t</select>";
    $showsecretquestion = "\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["sq"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t" . $options . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["sqa"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t<input $type = \"text\" $name = \"hintanswer\" $id = \"hintanswer\" $value = \"" . (isset($hintanswer) ? htmlspecialchars_uni($hintanswer) : "") . "\" $autocomplete = \"off\" $size = \"35\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t";
}
$showcountries = "";
if ($r_country == "yes") {
    $ip_num = sprintf("%u", ip2long(USERIPADDRESS));
    $qry = sql_query("SELECT cn FROM geo_csv WHERE " . sqlesc($ip_num) . " BETWEEN start AND end");
    if (mysqli_num_rows($qry)) {
        $res = mysqli_fetch_assoc($qry);
    }
    $options = "\r\n\t<select $name = \"country\" $id = \"country\">\r\n\t\t<option $value = \"\"></option>";
    $ct_r = sql_query("SELECT id,name FROM countries ORDER BY name") or ($ct_r = sql_query("SELECT id,name FROM countries ORDER BY name")) || sqlerr(__FILE__, 532);
    while ($ct_a = mysqli_fetch_assoc($ct_r)) {
        $options .= "\r\n\t\t<option $value = \"" . $ct_a["id"] . "\"" . (isset($country) && $country == $ct_a["id"] ? " $selected = \"selected\"" : (isset($res["cn"]) && $res["cn"] != "" && $ct_a["name"] == $res["cn"] ? " $selected = \"selected\"" : "")) . ">" . $ct_a["name"] . "</option>";
    }
    $options .= "\r\n\t</select>";
    $showcountries = "\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["country"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t" . $options . "\r\n\t\t</td>\r\n\t</tr>\r\n\t";
}
$showreferrer = "";
if ($r_referrer == "yes") {
    $showreferrer = "\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["referrer"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t<input $type = \"text\" $name = \"referrer\" $id = \"referrer\" $value = \"" . $referrer . "\" $autocomplete = \"off\" $size = \"35\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t";
}
$showtimezone = "";
if ($r_timezone == "yes") {
    require INC_PATH . "/functions_timezone.php";
    $showtimezone = "\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\" $valign = \"top\">\r\n\t\t\t" . $lang->signup["timezone"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t" . show_timezone($timezoneoffset, 1) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t";
}
$showbday = "";
if ($r_bday == "yes") {
    $lang->load("usercp");
    $months = explode(",", $lang->usercp["dob5"]);
    $days = 31;
    $displaydays = "\r\n\t<select $name = \"day\" $id = \"day\">\r\n\t\t<option $value = \"\">--------</option>";
    for ($i = 1; $i <= $days; $i++) {
        $displaydays .= "\r\n\t\t<option $value = \"" . $i . "\"" . (isset($day) && $day == $i ? " $selected = \"selected\"" : "") . ">" . $i . "</option>";
    }
    $displaydays .= "\r\n\t</select>";
    $displaymonths = "\r\n\t<select $name = \"month\" $id = \"month\">\r\n\t\t<option $value = \"\">--------</option>";
    $first = 1;
    foreach ($months as $left => $right) {
        $displaymonths .= "\r\n\t\t<option $value = \"" . $first . "\"" . (isset($month) && $month == $first ? " $selected = \"selected\"" : "") . ">" . $right . "</option>";
        $first++;
    }
    $displaymonths .= "\r\n\t</select>";
    $year = " <input $type = \"text\" $name = \"year\" $id = \"year\" $value = \"" . (isset($year) ? htmlspecialchars_uni($year) : "") . "\" $size = \"4\" />";
    $showbday = "\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\" $valign = \"top\">\r\n\t\t\t" . $lang->signup["bday"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t" . $displaydays . $displaymonths . $year . "\r\n\t\t</td>\r\n\t</tr>\r\n\t";
}
$showgender = "";
if ($r_gender == "yes") {
    $showgender = "\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\" $valign = \"top\">\r\n\t\t\t" . $lang->signup["gender"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t<input $type = \"radio\" $name = \"gender\" $id = \"gender\" $value = \"male\"" . (isset($gender) && $gender == "male" ? " $checked = \"checked\"" : (!isset($gender) ? " $checked = \"checked\"" : "")) . " /> " . $lang->signup["male"] . "\r\n\t\t\t<input $type = \"radio\" $name = \"gender\" $id = \"gender\" $value = \"female\"" . (isset($gender) && $gender == "female" ? " $checked = \"checked\"" : "") . " /> " . $lang->signup["female"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t";
}
$showinvitecode = "";
if ($registration != "on" && $invitesystem == "on") {
    $showinvitecode = "\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["invitecode"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t<input $type = \"text\" $name = \"invitehash\" $id = \"invitehash\" $value = \"" . $invitehash . "\" $autocomplete = \"off\" $size = \"35\" />\r\n\t\t</td>\r\n\t</tr>";
}
stdhead(sprintf($lang->signup["title"], $SITENAME), true, "<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/form_validation.js?$v = " . O_SCRIPT_VERSION . "\"></script>");
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction CheckEmail()\r\n\t{\r\n\t\tCheckEmailButton(0);\r\n\t\tvar $email = TSGetID(\"email\").value;\r\n\t\tvar $emailRegex = /^[\\w-\\.]+@([\\w-]+\\.)+[\\w-]{2,4}\$/;\r\n\t\tif($email = = \"\" || !email.match(emailRegex))\r\n\t\t{\r\n\t\t\tinlineMsg(\"email\", \"" . $lang->signup["validemail"] . "\");\r\n\t\t\tCheckEmailButton(1);\r\n\t\t\treturn false;\r\n\t\t}\r\n\t\tvar $mypostrequest = new ajaxRequest()\r\n\t\tmypostrequest.$onreadystatechange = function()\r\n\t\t{\r\n\t\t\tif (mypostrequest.$readyState = =4)\r\n\t\t\t{\r\n\t\t\t\tif (mypostrequest.$status = =200 || window.location.href.indexOf(\"http\")==-1)\r\n\t\t\t\t{\r\n\t\t\t\t\$tresult = mypostrequest.responseText;\r\n\t\t\t\t\tif (result != \"\")\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tinlineMsg(\"email\", result);\r\n\t\t\t\t\t\tCheckEmailButton(1);\r\n\t\t\t\t\t\treturn false;\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tCheckEmailButton(0);\r\n\t\t\t\t\t\tTSGetID(\"checkemail\").style.$display = \"none\";\r\n\t\t\t\t\t\tinlineMsg(\"email\", \"" . $lang->signup["emailokay"] . "\");\r\n\t\t\t\t\t\treturn true;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse\r\n\t\t\t\t{\r\n\t\t\t\t\talert(mypostrequest.status)\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t\tvar $emailvalue = encodeURIComponent(email);\r\n\t\tvar $parameters = \"time=\"+fetch_unix_timestamp()+\"&do=check_email&$value = \"+emailvalue;\r\n\t\tmypostrequest.open(\"POST\", \"ts_ajax9.php\", true)\r\n\t\tmypostrequest.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\")\r\n\t\tmypostrequest.send(parameters);\r\n\t}\r\n\r\n\tfunction CheckUsername()\r\n\t{\r\n\t\tCheckUsernameButton(0);\r\n\t\tvar $username = TSGetID(\"username\").value;\r\n\t\tvar $illegalChars = /[\\W_]/; // allow only letters and numbers\r\n\t\tif($username = = \"\" || illegalChars.test(username))\r\n\t\t{\r\n\t\t\tinlineMsg(\"username\",\"" . $lang->signup["validusername"] . "\");\r\n\t\t\tCheckUsernameButton(1);\r\n\t\t\treturn false;\r\n\t\t}\r\n\t\telse if (username.length < 3 || username.length > 12)\r\n\t\t{\r\n\t\t\tinlineMsg(\"username\",\"" . $lang->signup["validusername2"] . "\");\r\n\t\t\tCheckUsernameButton(1);\r\n\t\t\treturn false;\r\n\t\t}\r\n\t\tvar $mypostrequest = new ajaxRequest()\r\n\t\tmypostrequest.$onreadystatechange = function()\r\n\t\t{\r\n\t\t\tif (mypostrequest.$readyState = =4)\r\n\t\t\t{\r\n\t\t\t\tif (mypostrequest.$status = =200 || window.location.href.indexOf(\"http\")==-1)\r\n\t\t\t\t{\r\n\t\t\t\t\$tresult = mypostrequest.responseText;\r\n\t\t\t\t\tif (result != \"\")\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tinlineMsg(\"username\", result);\r\n\t\t\t\t\t\tCheckUsernameButton(1);\r\n\t\t\t\t\t\treturn false;\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tCheckUsernameButton(0);\r\n\t\t\t\t\t\tTSGetID(\"checkusername\").style.$display = \"none\";\r\n\t\t\t\t\t\tinlineMsg(\"username\", \"" . $lang->signup["usernameokay"] . "\");\r\n\t\t\t\t\t\treturn true;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse\r\n\t\t\t\t{\r\n\t\t\t\t\talert(mypostrequest.status)\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t\tvar $namevalue = encodeURIComponent(username);\r\n\t\tvar $parameters = \"time=\"+fetch_unix_timestamp()+\"&do=check_username&$value = \"+namevalue;\r\n\t\tmypostrequest.open(\"POST\", \"ts_ajax9.php\", true)\r\n\t\tmypostrequest.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\")\r\n\t\tmypostrequest.send(parameters);\r\n\t}\r\n\r\n\tfunction CheckUsernameButton(Status)\r\n\t{\r\n\t\tif (Status == 1)\r\n\t\t{\r\n\t\t\tTSGetID(\"checkusername\").$value = \"" . $lang->signup["checkbutton"] . "\";\r\n\t\t\tTSGetID(\"checkusername\").$disabled = \"\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"checkusername\").$value = \"" . $lang->global["pleasewait"] . "\";\r\n\t\t\tTSGetID(\"checkusername\").$disabled = \"disabled\";\r\n\t\t}\r\n\t}\r\n\r\n\tfunction CheckEmailButton(Status)\r\n\t{\r\n\t\tif (Status == 1)\r\n\t\t{\r\n\t\t\tTSGetID(\"checkemail\").$value = \"" . $lang->signup["checkbutton"] . "\";\r\n\t\t\tTSGetID(\"checkemail\").$disabled = \"\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"checkemail\").$value = \"" . $lang->global["pleasewait"] . "\";\r\n\t\t\tTSGetID(\"checkemail\").$disabled = \"disabled\";\r\n\t\t}\r\n\t}\r\n\r\n\tfunction ajaxRequest()\r\n\t{\r\n\t\tvar $activexmodes = [\"Msxml2.XMLHTTP\", \"Microsoft.XMLHTTP\"] //activeX versions to check for in IE\r\n\t\tif (window.ActiveXObject)\r\n\t\t{ //Test for support for ActiveXObject in IE first (as XMLHttpRequest in IE7 is broken)\r\n\t\t\tfor (var $i = 0; i<activexmodes.length; i++)\r\n\t\t\t{\r\n\t\t\t\ttry\r\n\t\t\t\t{\r\n\t\t\t\t\treturn new ActiveXObject(activexmodes[i])\r\n\t\t\t\t}\r\n\t\t\t\tcatch(e)\r\n\t\t\t\t{\r\n\t\t\t\t\t//suppress error\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t\telse if (window.XMLHttpRequest) // if Mozilla, Safari etc\r\n\t\t\treturn new XMLHttpRequest()\r\n\t\telse\r\n\t\t\treturn false\r\n\t}\r\n\r\n\tfunction passwordChanged()\r\n\t{\r\n\t\tvar $strength = document.getElementById(\"strength\");\r\n\t\tvar $strongRegex = new RegExp(\"^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*\$\", \"g\");\r\n\t\tvar $mediumRegex = new RegExp(\"^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*\$\", \"g\");\r\n\t\tvar $enoughRegex = new RegExp(\"(?=.{6,}).*\", \"g\");\r\n\t\tvar $pwd = document.getElementById(\"password\");\r\n\t\tif (pwd.value.$length = =0)\r\n\t\t{\r\n\t\t\tstrength.$innerHTML = \"\";\r\n\t\t}\r\n\t\telse if (false == enoughRegex.test(pwd.value))\r\n\t\t{\r\n\t\t\tstrength.$innerHTML = \"\";\r\n\t\t}\r\n\t\telse if (strongRegex.test(pwd.value))\r\n\t\t{\r\n\t\t\tstrength.$innerHTML = '&nbsp;" . $lang->signup["strong"] . "';\r\n\t\t}\r\n\t\telse if (mediumRegex.test(pwd.value))\r\n\t\t{\r\n\t\t\tstrength.$innerHTML = '&nbsp;" . $lang->signup["medium"] . "';\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tstrength.$innerHTML = '&nbsp;" . $lang->signup["weak"] . "';\r\n\t\t}\r\n\t}\r\n\r\n\$tfetch_unix_timestamp = function()\r\n\t{\r\n\t\treturn parseInt(new Date().getTime().toString().substring(0, 10))\r\n\t}\r\n\r\n\tfunction TS_ValidateForm(form)\r\n\t{\r\n\t\tvar $username = form.username.value;\r\n\t\tvar $illegalChars = /[\\W_]/; // allow only letters and numbers\r\n\t\tif($username = = \"\" || illegalChars.test(username))\r\n\t\t{\r\n\t\t\tinlineMsg(\"username\",\"" . $lang->signup["validusername"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\t\telse if (username.length < 3 || username.length > 12)\r\n\t\t{\r\n\t\t\tinlineMsg(\"username\",\"" . $lang->signup["validusername2"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tvar $password = form.password.value;\r\n\t\tvar $password2 = form.password2.value;\r\n\r\n\t\tif ($password = = \"\")\r\n\t\t{\r\n\t\t\tinlineMsg(\"password\", \"" . $lang->signup["validpassword"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tif ($password2 = = \"\")\r\n\t\t{\r\n\t\t\tinlineMsg(\"password2\", \"" . $lang->signup["validpassword"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tif (password != password2)\r\n\t\t{\r\n\t\t\tinlineMsg(\"password\", \"" . $lang->signup["validpassword2"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tif (password.length < 6 || password.length > 40)\r\n\t\t{\r\n\t\t\tinlineMsg(\"password\", \"" . $lang->signup["validpassword3"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tif ($password = = username)\r\n\t\t{\r\n\t\t\tinlineMsg(\"password\", \"" . $lang->signup["validpassword4"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tvar $email = form.email.value;\r\n\t\tvar $emailRegex = /^[\\w-\\.]+@([\\w-]+\\.)+[\\w-]{2,4}\$/;\r\n\t\tif($email = = \"\" || !email.match(emailRegex))\r\n\t\t{\r\n\t\t\tinlineMsg(\"email\", \"" . $lang->signup["validemail"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tvar $hintanswer = form.hintanswer.value;\r\n\t\tif ($hintanswer = = \"\")\r\n\t\t{\r\n\t\t\tinlineMsg(\"hintanswer\", \"" . $lang->signup["validhintanswer"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tif (hintanswer.length < 6 || hintanswer.length > 32)\r\n\t\t{\r\n\t\t\tinlineMsg(\"hintanswer\", \"" . $lang->signup["validhintanswer2"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tif ($hintanswer = = username)\r\n\t\t{\r\n\t\t\tinlineMsg(\"hintanswer\", \"" . $lang->signup["validhintanswer3"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tvar $country = form.country.value;\r\n\t\tif ($country = = \"\")\r\n\t\t{\r\n\t\t\tinlineMsg(\"country\", \"" . $lang->signup["validcountry"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\tvar $day = form.day.value;\r\n\t\tvar $month = form.month.value;\r\n\t\tvar $year = form.year.value;\r\n\r\n\t\tif ($day = = \"\" || $month = = \"\" || $year = = \"\")\r\n\t\t{\r\n\t\t\tinlineMsg(\"year\", \"" . $lang->signup["validbday"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\r\n\t\t" . ($showinvitecode != "" ? "\r\n\t\tvar $invitehash = form.invitehash.value;\r\n\t\tif ($invitehash = = \"\" || invitehash.length != 32)\r\n\t\t{\r\n\t\t\tinlineMsg(\"invitehash\", \"" . $lang->signup["validinvitecode"] . "\");\r\n\t\t\treturn false;\r\n\t\t}\r\n\t\t" : "") . "\r\n\r\n\t\tform.submit.$value = \"" . $lang->global["pleasewait"] . "\";\r\n\t\tform.submit.$disabled = \"disabled\";\r\n\t\treturn true;\r\n\t}\r\n</script>\r\n<form $name = \"form\" $id = \"form\" class=\"form\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$agree = yes\" $onsubmit = \"return TS_ValidateForm(this);\" $method = \"post\">\r\n" . (isset($ShowErrors) ? $ShowErrors : "") . "\r\n<table $width = \"100%\" $align = \"center\" $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . sprintf($lang->signup["title"], $SITENAME) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["username"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t<input $type = \"text\" $name = \"username\" $id = \"username\" $value = \"" . (isset($username) ? htmlspecialchars_uni($username) : "") . "\" $autocomplete = \"off\" $size = \"35\" />" . ($allow_ajax_check == "yes" ? " <input $type = \"button\" $onclick = \"CheckUsername();\" $value = \"" . $lang->signup["checkbutton"] . "\" $id = \"checkusername\" />" : "") . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["password"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t<input $type = \"password\" $name = \"password\" $id = \"password\" $value = \"\" $autocomplete = \"off\" $size = \"35\" $onkeyup = \"return passwordChanged();\" /><span $id = \"strength\"></span>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["password2"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t<input $type = \"password\" $name = \"password2\" $id = \"password2\" $value = \"\" $autocomplete = \"off\" $size = \"35\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $width = \"30%\" $align = \"right\">\r\n\t\t\t" . $lang->signup["email"] . "\r\n\t\t</td>\r\n\t\t<td $width = \"70%\" $align = \"left\">\r\n\t\t\t<input $type = \"text\" $name = \"email\" $id = \"email\" $value = \"" . (isset($email) ? htmlspecialchars_uni($email) : "") . "\" $autocomplete = \"off\" $size = \"35\" />" . ($allow_ajax_check == "yes" ? " <input $type = \"button\" $onclick = \"CheckEmail();\" $value = \"" . $lang->signup["checkbutton"] . "\" $id = \"checkemail\" />" : "") . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $showsecretquestion . "\r\n\t" . $showcountries . "\r\n\t" . $showreferrer . "\r\n\t" . $showtimezone . "\r\n\t" . $showbday . "\r\n\t" . $showgender . "\r\n\t" . $showinvitecode . "\r\n\t<tr>\r\n\t\t<td $colspan = \"2\" $align = \"center\" class=\"subheader\">\r\n\t\t\t<input $type = \"submit\" $id = \"submit\" $value = \"" . $lang->signup["signup"] . "\" />\r\n\t\t\t<input $type = \"reset\" $id = \"reset\" $value = \"" . $lang->signup["reset"] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
stdfoot();

?>