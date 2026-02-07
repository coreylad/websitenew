<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
function commenttable($rows, $type = "", $edit = "", $lc = false, $quote = false, $return = false)
{
    global $is_mod;
    global $CURUSER;
    global $BASEURL;
    global $rootpath;
    global $pic_base_url;
    global $lang;
    global $usergroups;
    global $timeformat;
    global $dateformat;
    global $useajax;
    global $regdateformat;
    include_once INC_PATH . "/functions_ratio.php";
    $moderator = $is_mod;
    $dt = TIMENOW - TS_TIMEOUT;
    $totalrows = count($rows);
    $quickmenu = "";
    $showcommentstable = "";
    $ajax_quick_edit_loaded = false;
    $QuickVoteLoaded = false;
    if (defined("LCID")) {
        $_count = LCID;
    } else {
        $_count = 0;
    }
    $lang->load("comment");
    foreach ($rows as $row) {
        if ($row["totalvotes"] != "0|0") {
            $TotalVotes = @explode("|", $row["totalvotes"]);
            $row["totalvotes"] = $TotalVotes[0] - $TotalVotes[1];
            if ($row["totalvotes"] > -5 || $moderator) {
            }
        } else {
            $row["totalvotes"] = 0;
        }
        $_count++;
        $p_commenthistory = $p_edit = $p_delete = $p_text = $p_report = $p_quote = $p_approve = "";
        if (!$QuickVoteLoaded) {
            $showcommentstable .= "<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_vote.js?$v = " . O_SCRIPT_VERSION . "\"></script>";
        }
        if (0 < $CURUSER["id"] && $row["user"] == $CURUSER["id"] || $moderator) {
            if ($useajax == "yes") {
                if (!$ajax_quick_edit_loaded) {
                    $showcommentstable .= "\r\n\t\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\t\tvar $l_quick_save_button = \"" . $lang->global["buttonsave"] . "\";\r\n\t\t\t\t\t\tvar $l_quick_cancel_button = \"" . $lang->global["cancel"] . "\";\r\n\t\t\t\t\t\tvar $l_quick_adv_button = \"" . $lang->global["advancedbutton"] . "\";\r\n\t\t\t\t\t\tvar $bbcodes = '" . trim(str_replace(["'", "\n", "\r"], ["\\'", "", ""], ts_show_bbcode_links("quick_edit_form", "newContent"))) . "';\r\n\t\t\t\t\t</script>\r\n\t\t\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/inline_quick_edit.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_editor.js?$v = " . O_SCRIPT_VERSION . "\"></script>";
                    $ajax_quick_edit_loaded = true;
                }
                $p_edit = "<input $type = \"button\" $onclick = \"TSQuickEditPost('post_message_" . $row["id"] . "','" . $BASEURL . "/" . $edit . "comment.php?$action = edit&amp;$cid = " . $row["id"] . "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "');bookmarkscroll.scrollTo('post_message_" . $row["id"] . "');\" $value = \"" . $lang->global["edit"] . "\" />";
            } else {
                $p_edit = "<input $type = \"button\" $onclick = \"jumpto('" . $BASEURL . "/" . $edit . "comment.php?$action = edit&amp;$cid = " . $row["id"] . "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "');\" $value = \"" . $lang->global["edit"] . "\" />";
            }
        }
        if ($moderator) {
            $p_delete = "<input $type = \"button\" $value = \"" . $lang->global["deletecomment"] . "\" $onclick = \"jumpto('" . $BASEURL . "/" . $edit . "comment.php?$action = delete&amp;$cid = " . $row["id"] . "&amp;$tid = " . $row["torrentid"] . "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "');\" />";
            $p_commenthistory = "<div $style = \"float: left;\"><input $type = \"button\" $value = \"" . $lang->comment["history"] . "\" $onclick = \"jumpto('" . $BASEURL . "/userhistory.php?$action = viewcomments&$id = " . $row["user"] . "');\" /></div>";
        }
        if ($quote === true) {
            $QuoteTag = htmlspecialchars(mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "[$quote = " . $row["username"] . "]" . $row["text"] . "[/quote]"));
            $p_quote .= "<input $type = \"button\" $id = \"quote_" . $row["id"] . "\" $onclick = \"parseQuote('" . $QuoteTag . "', 'message', " . $row["torrentid"] . ", " . $row["id"] . ");\" $value = \"" . $lang->comment["quote"] . "\" />";
        }
        if ($moderator || $usergroups["canreport"] == "yes") {
            $p_report = "<input $type = \"button\" $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 3&$reporting = " . $row["id"] . "&$extra = " . $row["torrentid"] . "&$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "', 'report', 500, 300); return false;\" $value = \"" . $lang->global["reportcomment"] . "\" $id = \"report_image_" . $row["id"] . "\" />";
        }
        if ($row["editedby"]) {
            $p_text .= "\r\n\t\t\t\t<br />\r\n\t\t\t\t<div>\r\n\t\t\t\t\t<font $size = \"1\" class=\"small\">" . $lang->global["lastedited"] . " <a $href = \"" . ts_seo($row["editedby"], $row["editedbyuname"]) . "\">" . get_user_color($row["editedbyuname"], $row["editbynamestyle"]) . "</a> " . my_datee($dateformat, $row["editedat"]) . " " . my_datee($timeformat, $row["editedat"]) . "</font>\r\n\t\t\t\t</div>";
        }
        if (!empty($row["modnotice"])) {
            $p_text .= "\r\n\t\t\t\t<br />\r\n\t\t\t\t<div class=\"modnotice\">\r\n\t\t\t\t\t" . sprintf($lang->global["modnotice"], $row["modeditid"], $row["modeditusername"], my_datee($dateformat, $row["modedittime"]) . " " . my_datee($timeformat, $row["modedittime"]), format_comment($row["modnotice"])) . "\r\n\t\t\t\t</div>\r\n\t\t\t";
        }
        $signature = !empty($row["signature"]) && TS_Match($CURUSER["options"], "H1") ? "<br /><hr $size = \"1\" $width = \"50%\"  $align = \"left\" />" . format_comment($row["signature"], true, true, true, true, "signatures") : "";
        if ($row["visible"] == 0) {
            if ($is_mod) {
                $p_approve = "<input $type = \"button\" $value = \"" . $lang->comment["approve"] . "\" $onclick = \"jumpto('" . $BASEURL . "/comment.php?$action = approve&$cid = " . $row["id"] . "&$tid = " . $row["torrentid"] . "');\" />";
                $textbody = show_notice($lang->comment["modinfomsg"]) . format_comment($row["text"]);
            } else {
                $textbody = show_notice($lang->comment["modinfomsg"]);
            }
        } else {
            $textbody = format_comment($row["text"]);
        }
        if (TS_Match($row["options"], "B1") && !$moderator && $row["user"] != $CURUSER["id"]) {
            $IsUserOnline = "<input $type = \"button\" $value = \"" . $lang->comment["offline"] . "\" />";
        } else {
            if ($dt < TS_MTStoUTS($row["last_access"]) || $row["user"] == $CURUSER["id"]) {
                $IsUserOnline = "<input $type = \"button\" $value = \"" . $lang->comment["online"] . "\" />";
            } else {
                $IsUserOnline = "<input $type = \"button\" $value = \"" . $lang->comment["offline"] . "\" />";
            }
        }
        $SendPM = " <input $type = \"button\" $value = \"" . $lang->comment["pm"] . "\" $onclick = \"jumpto('" . $BASEURL . "/sendmessage.php?$receiver = " . $row["user"] . "');\" />";
        if ((TS_Match($row["options"], "I3") || TS_Match($row["options"], "I4")) && !$moderator) {
            $OnMouseOver = "onmouseover=\"ddrivetip('" . $lang->global["nopermission"] . "', 200)\"; $onmouseout = \"hideddrivetip()\" ";
        } else {
            $Ratio = get_user_ratio($row["uploaded"], $row["downloaded"]);
            $UserStats = "<b>" . $lang->global["added"] . ":</b> " . my_datee($regdateformat, $row["registered"]) . "<br /><b>" . $lang->global["uploaded"] . "</b> " . mksize($row["uploaded"]) . "<br /><b>" . $lang->global["downloaded"] . "</b> " . mksize($row["downloaded"]) . "<br /><b>" . $lang->global["ratio"] . "</b> " . strip_tags($Ratio);
            $OnMouseOver = "onmouseover=\"ddrivetip('" . $UserStats . "', 200)\"; $onmouseout = \"hideddrivetip()\" ";
        }
        $username = $row["username"] ? "<a " . $OnMouseOver . "href=\"" . ts_seo($row["user"], $row["username"]) . "\" $alt = \"" . $row["username"] . "\">" . get_user_color($row["username"], $row["namestyle"]) . "</a> (" . ($row["title"] ? htmlspecialchars_uni($row["title"]) : get_user_color($row["grouptitle"], $row["namestyle"])) . ") " . ($row["donor"] == "yes" ? " <img $src = \"" . $pic_base_url . "star.gif\" $alt = \"" . $lang->global["imgdonated"] . "\" $title = \"" . $lang->global["imgdonated"] . "\" $border = \"0\" class=\"inlineimg\" />" : "") . ($row["warned"] == "yes" || $row["leechwarn"] == "yes" ? " <img $src = \"" . $pic_base_url . "warned.gif\" $alt = \"" . $lang->global["imgwarned"] . "\" $title = \"" . $lang->global["imgwarned"] . "\" $border = \"0\" class=\"inlineimg\" />" : "") . ($row["enabled"] != "yes" || $row["usergroup"] == 9 ? " <img $src = \"" . $pic_base_url . "disabled.gif\" $alt = \"" . $lang->global["imgdisabled"] . "\" $title = \"" . $lang->global["imgdisabled"] . "\" $border = \"0\" class=\"inlineimg\" />" : "") : $lang->global["guest"];
        $HighLight = $row["totalvotes"] <= -5 ? " class=\"highlight\"" : "";
        $showcommentstable .= ($_count == 1 ? "" : "<br />") . "\r\n\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t<tbody>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $colspan = \"2\"" . ($HighLight ? $HighLight : " class=\"subheader\"") . ">\r\n\t\t\t\t\t\t<div $style = \"float: right;\"><span $id = \"commentvotes" . $row["id"] . "\" $name = \"commentvotes" . $row["id"] . "\">" . $row["totalvotes"] . "</span> <img $src = \"" . $pic_base_url . "down.png\" $alt = \"\" $title = \"\" $border = \"0\" class=\"inlineimg\" $style = \"cursor: pointer;\" $onclick = \"TSQuickVote('" . $row["id"] . "', '-1'); return false;\" /> <img $src = \"" . $pic_base_url . "up.png\" $alt = \"\" $title = \"\" $border = \"0\" class=\"inlineimg\" $style = \"cursor: pointer;\" $onclick = \"TSQuickVote('" . $row["id"] . "', '1'); return false;\" /></div>\r\n\t\t\t\t\t\t<div $style = \"float: left;\"><a $name = \"cid" . $row["id"] . "\" $id = \"cid" . $row["id"] . "\"></a><a $href = \"#cid" . $row["id"] . "\">#" . $_count . "</a> by " . $username . " " . my_datee($dateformat, $row["added"]) . " " . my_datee($timeformat, $row["added"]) . "</div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"center\" $valign = \"top\" $height = \"1%\" $width = \"1%\"" . $HighLight . ">\r\n\t\t\t\t\t\t" . get_user_avatar($row["useravatar"], false, 100, 100) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $align = \"left\" $valign = \"top\"" . $HighLight . ">\r\n\t\t\t\t\t\t<div $id = \"post_message_" . $row["id"] . "\" $style = \"display: inline;\">" . $textbody . "</div>\r\n\t\t\t\t\t\t<p $id = \"textinfo\">" . $p_text . "</p>\r\n\t\t\t\t\t\t" . $signature . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr" . $HighLight . ">\r\n\t\t\t\t\t<td $align = \"center\" $height = \"32\" $width = \"100\">" . $IsUserOnline . $SendPM . "</td>\r\n\t\t\t\t\t<td><div $style = \"float: right;\">" . $p_approve . " " . $p_report . " " . $p_delete . " " . $p_edit . " " . $p_quote . " <input $type = \"button\" $onclick = \"bookmarkscroll.scrollTo('show_allcomments');\" $value = \"" . $lang->comment["top"] . "\" /></div>" . $p_commenthistory . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t";
    }
    $showcommentstable .= "<div $style = \"display: block;\" $id = \"ajax_comment_preview\"></div><div $style = \"display: block;\" $id = \"ajax_comment_preview2\"></div>";
    if ($return) {
        return $showcommentstable;
    }
    echo $showcommentstable;
}

?>