<?php
define("TS_GAMES_VERSION", "1.1 by xam");
$rootpath = "../";
require $rootpath . "global.php";
$Act = TS_Global("act");
$Do = TS_Global("do");
$GName = TS_Global("gname");
$GScore = (double) TS_Global("gscore");
$Cid = intval(TS_Global("cid"));
$Gid = intval(TS_Global("gid"));
$lang->load("ts_games");
$DefaultTitle = sprintf($lang->ts_games["head"], $SITENAME);
$CategoryName = "";
$OUTPUT = "";
if ($Do == "delete_comment" && $Cid && $is_mod) {
    sql_query("DELETE FROM ts_games_comments WHERE `cid` = " . sqlesc($Cid));
    $Cid = 0;
}
if ($Cid) {
    ($Query = sql_query("SELECT cname FROM ts_games_categories WHERE `cid` = " . sqlesc($Cid))) || sqlerr(__FILE__, 43);
    if (mysqli_num_rows($Query) == 0) {
        $Cid = 0;
    } else {
        $Result = mysqli_fetch_assoc($Query);
        $CategoryName = " - " . htmlspecialchars_uni($Result["cname"]);
    }
}
if ($Gid) {
    ($Query = sql_query("SELECT * FROM ts_games WHERE $gid = " . sqlesc($Gid))) || sqlerr(__FILE__, 58);
    if (mysqli_num_rows($Query) == 0) {
        $Gid = 0;
    } else {
        $GAMEDETAILS = mysqli_fetch_assoc($Query);
        $GameName = htmlspecialchars_uni($GAMEDETAILS["gtitle"]);
    }
}
if ($GName) {
    ($Query = sql_query("SELECT gid FROM ts_games WHERE $gname = " . sqlesc($GName))) || sqlerr(__FILE__, 73);
    if (mysqli_num_rows($Query) == 0) {
        $GName = "";
    } else {
        $Result = mysqli_fetch_assoc($Query);
        $Gid = intval($Result["gid"]);
    }
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $GName && $Gid && isset($CURUSER)) {
    if (0 < $GScore) {
        $Query = sql_query("SELECT savescore FROM ts_games WHERE $gid = " . sqlesc($Gid));
        if (mysqli_num_rows($Query)) {
            $Result = mysqli_fetch_assoc($Query);
            if ($Result["savescore"] == "1" && $GScore) {
                $Query = sql_query("SELECT `score` FROM ts_games_champions WHERE $gid = " . sqlesc($Gid));
                if (mysqli_num_rows($Query)) {
                    $Result = mysqli_fetch_assoc($Query);
                    $HighScore = $Result["score"];
                    if ($HighScore < $GScore) {
                        sql_query("UPDATE ts_games_champions SET $userid = " . $CURUSER["id"] . ", $score = " . sqlesc($GScore) . ", $date = " . TIMENOW . " WHERE $gid = " . sqlesc($Gid));
                    }
                } else {
                    sql_query("INSERT INTO ts_games_champions (gid, userid, score, date) VALUES (" . $Gid . ", " . $CURUSER["id"] . ", " . sqlesc($GScore) . ", " . TIMENOW . ")");
                }
                $Query = sql_query("SELECT `score` FROM ts_games_scores WHERE $gid = " . $Gid . " AND $userid = " . $CURUSER["id"] . " ORDER BY `score` DESC LIMIT 1");
                if (mysqli_num_rows($Query)) {
                    $Result = mysqli_fetch_assoc($Query);
                    $UserHighScoreForThisGame = $Result["score"];
                    if ($UserHighScoreForThisGame < $GScore) {
                        sql_query("DELETE FROM ts_games_scores WHERE $gid = " . $Gid . " AND $userid = " . $CURUSER["id"]);
                    }
                }
                sql_query("INSERT INTO ts_games_scores (gid, userid, score, added) VALUES (" . $Gid . ", " . $CURUSER["id"] . ", " . sqlesc($GScore) . ", " . TIMENOW . ")");
            }
        }
    }
    redirect($_SERVER["SCRIPT_NAME"] . "?$act = play&$gid = " . $Gid);
    exit;
}
if (empty($Act) || $Act == "search" || $Act == "play" && $Gid) {
    $GCategories = [];
    $Query = sql_query("SELECT cid, cname FROM ts_games_categories ORDER BY sort") or ($Query = sql_query("SELECT cid, cname FROM ts_games_categories ORDER BY sort")) || sqlerr(__FILE__, 140);
    while ($GCat = mysqli_fetch_assoc($Query)) {
        $GCategories[] = ($GCat["cid"] == $Cid ? "<span class=\"hightlight\">" : "") . " <img $src = \"" . $BASEURL . "/ts_games/images/play.png\" class=\"inlineimg\" $border = \"0\" $alt = \"\" $title = \"\" /> <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$cid = " . $GCat["cid"] . "\">" . htmlspecialchars_uni($GCat["cname"]) . "</a>" . ($GCat["cid"] == $Cid ? "</span>" : "");
    }
    $ShowCategories = "\r\n\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->ts_games["categories"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/ts_games/images/play.png\" class=\"inlineimg\" $border = \"0\" $alt = \"\" $title = \"\" /> <a $href = \"" . $_SERVER["SCRIPT_NAME"] . "\">" . $lang->ts_games["all"] . "</a><hr />\r\n\t\t\t\t\t" . implode("<hr />", $GCategories) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>";
    ($Query = sql_query("SELECT gid, gname, gtitle, gwords FROM ts_games ORDER BY RAND() LIMIT 1")) || sqlerr(__FILE__, 163);
    $RGame = mysqli_fetch_assoc($Query);
    $RandomGame = "\r\n\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->ts_games["rgame"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<span $style = \"float: left; padding-right: 5px;\"><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;$gid = " . $RGame["gid"] . "\"><img $src = \"" . $BASEURL . "/ts_games/images/" . $RGame["gname"] . "1.gif\" class=\"inlineimg\" $border = \"0\" $alt = \"" . htmlspecialchars_uni($RGame["gtitle"]) . "\" $title = \"" . htmlspecialchars_uni($RGame["gtitle"]) . "\" /></a></span>" . cutename($RGame["gwords"], 60) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>";
    $GameSearch = "\r\n\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->ts_games["search"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\">\r\n\t\t\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"act\" $value = \"search\" />\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"keywords\" $size = \"18\" $value = \"\" />\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_games["go"] . "\" />\r\n\t\t\t\t\t</form>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>";
    $Champions = [];
    $ChampionsList = [];
    $ScoreToBeat = [];
    ($Query = sql_query("SELECT c.gid, c.userid, c.score, game.gname, game.gtitle, u.username, g.namestyle FROM ts_games_champions c LEFT JOIN ts_games game ON (c.$gid = game.gid) LEFT JOIN users u ON (c.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) ORDER by c.date DESC, c.score DESC")) || sqlerr(__FILE__, 204);
    if (mysqli_num_rows($Query)) {
        for ($CCount = 0; $Champion = mysqli_fetch_assoc($Query); $CCount++) {
            $GameImage = is_file("images/" . $Champion["gname"] . "2.gif") ? "<img $src = \"" . $BASEURL . "/ts_games/images/" . $Champion["gname"] . "2.gif\" $alt = \"" . htmlspecialchars_uni($Champion["gtitle"]) . "\" $title = \"" . htmlspecialchars_uni($Champion["gtitle"]) . "\" $border = \"0\" class=\"inlineimg\" />" : "";
            $ChamUser = "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;$gid = " . $Champion["gid"] . "\">" . $GameImage . "</a> <a $href = \"" . ts_seo($Champion["userid"], $Champion["username"]) . "\">" . get_user_color($Champion["username"], $Champion["namestyle"]) . "</a> " . sprintf($lang->ts_games["cscore"], ts_nf($Champion["score"]));
            if ($CCount < 5) {
                $ChampionsList[] = str_replace("<br />", " ", $ChamUser);
            }
            $ScoreToBeat[$Champion["gid"]] = $Champion["score"];
            $Champions[$Champion["gid"]] = "<a $href = \"" . ts_seo($Champion["userid"], $Champion["username"]) . "\">" . get_user_color($Champion["username"], $Champion["namestyle"]) . "</a> " . sprintf($lang->ts_games["cscore"], ts_nf($Champion["score"]));
        }
    }
    $ShowChampionList = "";
    if (count($ChampionsList)) {
        $ShowChampionList = "\r\n\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t<tbody>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t" . $lang->ts_games["lchamps"] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t" . implode("<hr />", $ChampionsList) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t</div>";
    }
    add_breadcrumb($DefaultTitle, $_SERVER["SCRIPT_NAME"]);
    $WHERE = "";
    if ($Cid) {
        $WHERE = " WHERE c.$cid = " . sqlesc($Cid) . " ";
        add_breadcrumb($lang->ts_games["title2"] . ": " . trim(str_replace("-", "", $CategoryName)));
    }
    if ($Act == "play") {
        if ($Do == "scores") {
            add_breadcrumb($GameName, $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;$gid = " . $Gid);
            add_breadcrumb($lang->ts_games["vhigh"] . ": " . $GameName);
        } else {
            add_breadcrumb($lang->ts_games["play"] . ": " . $GameName);
        }
    }
    if ($Act == "search") {
        add_breadcrumb($lang->ts_games["search"]);
        $Keywords = TS_Global("keywords");
        if (!empty($Keywords)) {
            $WHERE = " WHERE g.gwords LIKE '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "%" . $Keywords . "%") . "' OR g.gtitle LIKE '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "%" . $Keywords . "%") . "' ";
        }
    }
    $OUTPUT = "\r\n\t<table $align = \"center\" $cellpadding = \"0\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t<tbody>\r\n\t\t\t\t<tr $valign = \"top\">\r\n\t\t\t\t\t<td $valign = \"top\" class=\"none\">";
    if ($Act != "play") {
        $ALLRaters = [];
        $Query = sql_query("SELECT ratingid, score FROM ts_ratings WHERE `ratingid` LIKE '%game_%'");
        if (mysqli_num_rows($Query)) {
            while ($Raters = mysqli_fetch_assoc($Query)) {
                $RGID = str_replace("game_", "", $Raters["ratingid"]);
                $ALLRaters[$RGID][] = $Raters["score"];
            }
        }
        ($Count = sql_query("SELECT c.gid FROM ts_games c" . str_replace("g.", "c.", $WHERE))) || sqlerr(__FILE__, 295);
        $Count = mysqli_num_rows($Count);
        list($pagertop, $pagerbottom, $limit) = pager(10, $Count, "" . $_SERVER["SCRIPT_NAME"] . "" . ($Cid ? "?$cid = " . $Cid . "&amp;" : "?"));
        $OUTPUT .= "\r\n\t\t\t\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"tspager\">\r\n\t\t\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"thead\" $colspan = \"4\">\r\n\t\t\t\t\t\t\t\t\t\t\t<span $style = \"float: left;\">" . $lang->ts_games["head1"] . $CategoryName . "</span>\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"50%\">" . $lang->ts_games["title1"] . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"10%\">" . $lang->ts_games["title2"] . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"20%\">" . $lang->ts_games["title3"] . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"20%\">" . $lang->ts_games["title4"] . "</td>\r\n\t\t\t\t\t\t\t\t\t</tr>";
        ($Query = sql_query("SELECT g.*, c.cname FROM ts_games g LEFT JOIN ts_games_categories c ON (g.$cid = c.cid)" . $WHERE . "ORDER by added DESC, sort ASC " . $limit)) || sqlerr(__FILE__, 314);
        if (mysqli_num_rows($Query)) {
            while ($Game = mysqli_fetch_assoc($Query)) {
                $TotalScore = isset($ALLRaters[$Game["gid"]]) ? true : false;
                if ($TotalScore) {
                    $TotalScore = array_sum($ALLRaters[$Game["gid"]]);
                    $TotalScorers = count($ALLRaters[$Game["gid"]]);
                    $Image = 0 < $TotalScorers ? round($TotalScore / $TotalScorers) : 0;
                    $Average = 0 < $TotalScorers ? round($TotalScore / $TotalScorers, 1) : 0;
                    $SavedScore = "<img $src = \"" . $pic_base_url . "imdb_rating/" . $Image . "-10.png\" $alt = \"" . $Average . "\" $title = \"" . $Average . "\" $border = \"0\" class=\"inlineimg\" />";
                } else {
                    $SavedScore = $lang->ts_games["norating"];
                }
                $GameImage = is_file("images/" . $Game["gname"] . "1.gif") ? "<img $src = \"" . $BASEURL . "/ts_games/images/" . $Game["gname"] . "1.gif\" $alt = \"" . htmlspecialchars_uni($Game["gtitle"]) . "\" $title = \"" . htmlspecialchars_uni($Game["gtitle"]) . "\" $border = \"0\" class=\"inlineimg\" />" : "";
                $OUTPUT .= "\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td $align = \"left\">\r\n\t\t\t\t\t\t\t\t\t\t\t<table $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"center\">" . $GameImage . "</td>\r\n\t\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"none\" $valign = \"top\"><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;$gid = " . $Game["gid"] . "\"><b>" . htmlspecialchars_uni($Game["gtitle"]) . "</b></a><br />" . htmlspecialchars_uni($Game["gwords"]) . "</td>\r\n\t\t\t\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t\t<td $align = \"center\"><img $src = \"" . $BASEURL . "/ts_games/images/play.png\" class=\"inlineimg\" $border = \"0\" $alt = \"\" $title = \"\" /><br /><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$cid = " . $Game["cid"] . "\">" . htmlspecialchars_uni($Game["cname"]) . "</a></td>\r\n\t\t\t\t\t\t\t\t\t\t<td $align = \"center\">" . $SavedScore . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td $align = \"center\"><img $src = \"" . $BASEURL . "/ts_games/images/champion.png\" $alt = \"" . $lang->ts_games["title4"] . "\" $title = \"" . $lang->ts_games["title4"] . "\" $border = \"0\" class=\"inlineimg\" /> " . (isset($Champions[$Game["gid"]]) ? $Champions[$Game["gid"]] : "<br />" . $lang->ts_games["nochamp"]) . "</td>\r\n\t\t\t\t\t\t\t\t\t</tr>";
            }
        } else {
            $OUTPUT .= "<tr><td $colspan = \"4\">" . $lang->ts_games["nogame"] . "</td></tr>";
        }
        $OUTPUT .= "\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t\t" . $pagerbottom . "\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</td>";
    } else {
        if ($Do == "scores" && $Gid) {
            $Query = sql_query("SELECT sid FROM ts_games_scores WHERE $gid = " . sqlesc($Gid));
            $Count = mysqli_num_rows($Query);
            list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $Count, "" . $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;do=scores&amp;$gid = " . $Gid . "&amp;");
            $OUTPUT .= "\r\n\t\t\t<div $style = \"padding-bottom: 15px;\" $id = \"tspager\">\r\n\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"thead\" $colspan = \"3\">\r\n\t\t\t\t\t\t\t\t<span $style = \"float: left;\">" . $lang->ts_games["vhigh"] . ": " . $GameName . "</span>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"subheader\">" . $lang->ts_games["username"] . "</td>\r\n\t\t\t\t\t\t\t<td class=\"subheader\">" . $lang->ts_games["score"] . "</td>\r\n\t\t\t\t\t\t\t<td class=\"subheader\">" . $lang->ts_games["added"] . "</td>\r\n\t\t\t\t\t\t</tr>";
            ($Query = sql_query("SELECT s.userid, s.added, s.score, u.username, g.namestyle FROM ts_games_scores s LEFT JOIN users u ON (s.`userid` = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE s.$gid = " . sqlesc($Gid) . " ORDER by s.score DESC, s.added DESC " . $limit)) || sqlerr(__FILE__, 383);
            if (mysqli_num_rows($Query)) {
                while ($Scorers = mysqli_fetch_assoc($Query)) {
                    $OUTPUT .= "\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td>" . get_user_color($Scorers["username"], $Scorers["namestyle"]) . "</td>\r\n\t\t\t\t\t\t\t\t<td>" . ts_nf($Scorers["score"]) . "</td>\r\n\t\t\t\t\t\t\t\t<td>" . my_datee($dateformat, $Scorers["added"]) . " " . my_datee($timeformat, $Scorers["added"]) . "</td>\r\n\t\t\t\t\t\t\t</tr>";
                }
            } else {
                $OUTPUT .= "<tr><td $colspan = \"3\">" . $lang->ts_games["noresults"] . "</td></tr>";
            }
            $OUTPUT .= "\r\n\t\t\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t\t" . $pagerbottom . "\r\n\t\t</div>";
        } else {
            if ($Act == "play" && $Gid && isset($GAMEDETAILS)) {
                $DISABLE_RIGHT_PANEL = true;
                $OUTPUT .= "\r\n\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t<span $style = \"float: left;\">" . $lang->ts_games["play"] . ": " . $GameName . "</span>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td>";
                $GameFile = "./game_files/" . $GAMEDETAILS["gname"] . ".swf";
                if (is_file($GameFile)) {
                    $location = str_replace("index.php", "", $_SERVER["SCRIPT_NAME"]);
                    $gameURL = $BASEURL . "/ts_games/game_files/" . $GAMEDETAILS["gname"] . ".swf";
                    require INC_PATH . "/class_ts_rating.php";
                    $TSRating = new TS_Rating("game_" . $Gid, $CURUSER["id"]);
                    $SHowForm = $TSRating->ShowForm($lang->ts_games["rategame"]);
                    $rating = "\r\n\t\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_rate.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t\t\t<div $id = \"tsrating_results\" $name = \"tsrating_results\" $style = \"padding-bottom: 5px; padding-top: 10px;\">\r\n\t\t\t\t\t" . $TSRating->GetScore($lang->ts_games["ratedetails"]) . "\r\n\t\t\t\t\t" . ($SHowForm ? $SHowForm : "<br /><i><b>" . $lang->ts_games["alreadyvotes"] . "</b></i>") . "\r\n\t\t\t\t</div>\r\n\t\t\t\t";
                    $OUTPUT .= "\r\n\t\t\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $align = \"center\" $width = \"99%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $align = \"center\" $width = \"" . $GAMEDETAILS["gwidth"] . "\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<object $classid = \"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" $codebase = \"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#$version = 6,0,40,0\" $id = \"playnewin\" $name = \"tssearceda\" $width = \"100%\" $height = \"100%\">\r\n\t\t\t\t\t\t\t<param $name = \"menu\" $value = \"false\" />\r\n\t\t\t\t\t\t\t<param $name = \"movie\" $value = \"" . $gameURL . "?$tgame = 0&$pn_gid = " . $GAMEDETAILS["gid"] . "&$pn_domain = " . $gameURL . "&$pn_uname = " . (isset($CURUSER) ? $CURUSER["username"] : "Guest") . "\" />\r\n\t\t\t\t\t\t\t<param $name = \"type\" $value = \"application/x-shockwave-flash\" />\r\n\t\t\t\t\t\t\t<param $name = \"pluginspage\" $value = \"http://www.macromedia.com/go/getflashplayer/\" />\r\n\t\t\t\t\t\t\t<param $name = \"quality\" $value = \"high\" />\r\n\t\t\t\t\t\t\t<param $name = \"menu\" $value = \"false\" />\r\n\t\t\t\t\t\t\t<param $name = \"width\" $value = \"" . $GAMEDETAILS["gwidth"] . "\" />\r\n\t\t\t\t\t\t\t<param $name = \"height\" $value = \"" . $GAMEDETAILS["gheight"] . "\" />\r\n\t\t\t\t\t\t\t<param $name = \"flashvars\" $value = \"location=" . $location . "&$gamename = " . $GAMEDETAILS["gname"] . "\" />\r\n\r\n\t\t\t\t\t\t\t<embed $src = \"" . $gameURL . "?$tgame = 0&$pn_gid = " . $GAMEDETAILS["gid"] . "&$pn_domain = " . $gameURL . "&$pn_uname = " . (isset($CURUSER) ? $CURUSER["username"] : "Guest") . "\"  $width = \"" . $GAMEDETAILS["gwidth"] . "\" $height = \"" . $GAMEDETAILS["gheight"] . "\" $quality = \"high\" $type = \"application/x-shockwave-flash\" $pluginspage = \"http://www.macromedia.com/go/getflashplayer/\" $flashvars = \"location=" . $location . "&$gamename = " . $GAMEDETAILS["gname"] . "\" $menu = \"false\"></embed>\r\n\t\t\t\t\t\t\t<noembed>" . $lang->ts_games["noflash"] . "</noembed>\r\n\t\t\t\t\t\t\t</object>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td $valign = \"top\">\r\n\t\t\t\t\t\t\t\t<h1>" . $GAMEDETAILS["gtitle"] . "</h1>\r\n\t\t\t\t\t\t\t\t" . $GAMEDETAILS["gwords"] . "\r\n\t\t\t\t\t\t\t\t<hr />\r\n\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t<h1>" . $lang->ts_games["howto"] . "</h1>\r\n\t\t\t\t\t\t\t\t" . $GAMEDETAILS["gkeys"] . "\r\n\t\t\t\t\t\t\t\t<hr />\r\n\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t<h1>" . $lang->ts_games["title3"] . "</h1>\r\n\t\t\t\t\t\t\t\t" . $rating . "\r\n\t\t\t\t\t\t\t\t<hr />\r\n\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t<h1>" . $lang->ts_games["sbeat"] . "</h1>\r\n\t\t\t\t\t\t\t\t<b><font $size = \"4\">" . (isset($ScoreToBeat[$GAMEDETAILS["gid"]]) ? ts_nf($ScoreToBeat[$GAMEDETAILS["gid"]]) : "---") . "</font></b> (<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;do=scores&amp;$gid = " . $Gid . "\">" . $lang->ts_games["vhigh"] . "</a>)\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t\t<br />";
                    if ($Do == "save_comment" && isset($CURUSER)) {
                        $message = TS_Global("message");
                        if ($message) {
                            sql_query("INSERT INTO ts_games_comments (gid, userid, added, comment) VALUES (" . $Gid . ", " . $CURUSER["id"] . ", " . TIMENOW . ", " . sqlesc($message) . ")") || sqlerr(__FILE__, 481);
                        }
                    }
                    if (isset($CURUSER)) {
                        $Query = sql_query("SELECT cid FROM ts_games_comments WHERE $gid = " . sqlesc($Gid));
                        $Count = mysqli_num_rows($Query);
                        list($pagertop, $pagerbottom, $limit) = pager(4, $Count, "" . $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;$gid = " . $Gid . "&amp;");
                        $VisitorMessages = "\r\n\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\" $id = \"tspager\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"thead\" $id = \"PostedQuickVisitorMessages\">" . $lang->ts_games["visitormsg1"] . "</td>\r\n\t\t\t\t\t\t</tr>";
                        ($Query2 = sql_query("SELECT v.cid as visitormsgid, v.userid as visitorid, v.comment as visitormsg, v.added, u.username, u.avatar, g.namestyle FROM ts_games_comments v LEFT JOIN users u ON (v.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE v.$gid = '" . $Gid . "' ORDER by v.added DESC " . $limit)) || sqlerr(__FILE__, 496);
                        if (0 < mysqli_num_rows($Query2)) {
                            while ($vm = mysqli_fetch_assoc($Query2)) {
                                $VisitorUsername = get_user_color($vm["username"], $vm["namestyle"]);
                                $vAvatar = get_user_avatar($vm["avatar"], false, 60, 60);
                                $vAdded = my_datee($dateformat, $vm["added"]) . " " . my_datee($timeformat, $vm["added"]);
                                $vPoster = "<a $href = \"" . ts_seo($vm["visitorid"], $vm["username"]) . "\">" . $VisitorUsername . "</a>";
                                $vMessage = format_comment($vm["visitormsg"]);
                                $VisitorMessages .= "\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $id = \"ShowVisitorMessage" . $vm["visitormsgid"] . "\" $name = \"ShowVisitorMessage" . $vm["visitormsgid"] . "\">\r\n\t\t\t\t\t\t\t\t\t<div $style = \"float: left;\">" . $vAvatar . "</div>\r\n\t\t\t\t\t\t\t\t\t<div $style = \"overflow:auto; padding: 2px;\">\r\n\t\t\t\t\t\t\t\t\t\t<p class=\"subheader\">\r\n\t\t\t\t\t\t\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t\t\t\t\t\t\t" . ($is_mod ? "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;$gid = " . $Gid . "&amp;do=delete_comment&amp;$cid = " . $vm["visitormsgid"] . "#PostedQuickVisitorMessages\"><img $src = \"" . $BASEURL . "/ts_games/images/delete.png\" $alt = \"" . $lang->ts_games["delete"] . "\" $title = \"" . $lang->ts_games["delete"] . "\" $border = \"0\" class=\"inlineimg\" /></a>" : "") . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t" . sprintf($lang->ts_games["visitormsg5"], $vAdded, $vPoster) . "\r\n\t\t\t\t\t\t\t\t\t\t\t</span>\r\n\t\t\t\t\t\t\t\t\t\t</p>\r\n\t\t\t\t\t\t\t\t\t\t<div $name = \"msg" . $vm["visitormsgid"] . "\" $id = \"msg" . $vm["visitormsgid"] . "\">" . $vMessage . "</div>\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t";
                            }
                        } else {
                            $VisitorMessages .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t" . $lang->ts_games["visitormsg4"] . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>";
                        }
                        $VisitorMessages .= "</table>" . $pagerbottom;
                        $lang->load("quick_editor");
                        require INC_PATH . "/functions_quick_editor.php";
                        require_once INC_PATH . "/class_tsquickbbcodeeditor.php";
                        $QuickEditor = new TSQuickBBCodeEditor();
                        $QuickEditor->ImagePath = $pic_base_url;
                        $QuickEditor->SmiliePath = $pic_base_url . "smilies/";
                        $QuickEditor->FormName = "quickreply";
                        $QuickEditor->TextAreaName = "message";
                        $OUTPUT .= "\r\n\t\t\t\t\t" . $VisitorMessages . "\r\n\t\t\t\t\t<br />\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t\t\t\t\t" . $lang->ts_games["visitormsg"] . "\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t\t" . $QuickEditor->GenerateJavascript() . "\r\n\t\t\t\t\t\t\t\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$act = play&amp;do=save_comment&$gid = " . $Gid . "#PostedQuickVisitorMessages\" $name = \"quickreply\" $id = \"quickreply\">\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"hidden\" $name = \"gid\" $value = \"" . $Gid . "\" />\r\n\t\t\t\t\t\t\t\t\t\t<input $type = \"hidden\" $name = \"act\" $value = \"save_comment\" />\r\n\t\t\t\t\t\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"none\" $id = \"showvisitormessage\">\r\n\t\t\t\t\t\t\t\t\t\t\t\t\t" . $QuickEditor->GenerateBBCode() . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t\t<br />\r\n\t\t\t\t\t\t\t\t\t\t\t\t\t<textarea $name = \"message\" $style = \"width:600px;height:65px;\" $id = \"message\"></textarea><br />\r\n\t\t\t\t\t\t\t\t\t\t\t\t\t<input $type = \"submit\" $name = \"submit\" $value = \"" . $lang->ts_games["visitormsg2"] . "\" class=\"button\" />\r\n\t\t\t\t\t\t\t\t\t\t\t\t\t<input $type = \"reset\" $value = \"" . $lang->ts_games["visitormsg3"] . "\" class=\"button\" />\r\n\t\t\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t\t\t\t\t</form>\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t";
                    }
                } else {
                    $OUTPUT .= $lang->ts_games["invalidgame"];
                }
                $OUTPUT .= "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t</div>";
            }
        }
    }
    $OUTPUT .= "\r\n\t\t\t" . (!isset($DISABLE_RIGHT_PANEL) ? "\r\n\t\t\t\t<td $style = \"padding-left: 15px\" $valign = \"top\" $width = \"210\" class=\"none\">\r\n\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t\t" . $ShowCategories . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t\t" . $GameSearch . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t" . $ShowChampionList . "\r\n\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\r\n\t\t\t\t\t\t" . $RandomGame . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t</td>" : "") . "\r\n\t\t\t\t</tr>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>";
}
stdhead($DefaultTitle, true, "<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/prototype.js?$v = " . O_SCRIPT_VERSION . "\"></script>");
build_breadcrumb();
echo $OUTPUT;
stdfoot();

?>