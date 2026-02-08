<?php
define("THIS_SCRIPT", "forumdisplay.php");
require "./global.php";
if (isset($_GET["fid"]) && is_valid_id($_GET["fid"])) {
    $fid = intval($_GET["fid"]);
    if (!isset($permissions[$fid]["canview"]) || $permissions[$fid]["canview"] != "yes") {
        stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
        exit;
    }
    $extra_link_nav = "";
    $extra_link_nav2 = "&$action = quick_sort";
    $order_by = "t.sticky DESC, t.lastpost DESC";
    $where = "t.$fid = " . sqlesc($fid);
    $where2 = "fid = " . sqlesc($fid);
    $sort = "";
    $order = "";
    $daysprune = "";
    $action = isset($_GET["action"]) ? trim($_GET["action"]) : "";
    if ($action == "quick_sort") {
        $sort_array = ["subject", "lastpost", "dateline", "replies", "views", "username", "votetotal"];
        $sort = isset($_GET["sort"]) && in_array(strtolower($_GET["sort"]), $sort_array) ? trim($_GET["sort"]) : false;
        $order = isset($_GET["order"]) && strtolower($_GET["order"]) == "asc" ? "ASC" : "DESC";
        $daysprune = isset($_GET["daysprune"]) && $_GET["daysprune"] == "-1" ? false : (isset($_GET["daysprune"]) ? intval($_GET["daysprune"]) : 0);
        if ($sort) {
            $order_by = "t." . $sort . " " . $order;
            $extra_link_nav .= "&amp;$action = quick_sort&amp;$sort = " . htmlspecialchars_uni($sort) . "&amp;$order = " . htmlspecialchars_uni($order) . "";
            $extra_link_nav2 .= "&amp;$order = " . htmlspecialchars_uni($order) . "";
        }
        if ($daysprune) {
            $where = "t.$fid = " . sqlesc($fid) . " AND t.lastpost >= " . (TIMENOW - $daysprune * 86400);
            $where2 = "fid = " . sqlesc($fid) . " AND lastpost >= " . (TIMENOW - $daysprune * 86400);
            $extra_link_nav .= "&amp;$daysprune = " . htmlspecialchars_uni($daysprune);
            $extra_link_nav2 .= "&amp;$daysprune = " . htmlspecialchars_uni($daysprune);
        }
    }
    if (0 < $CURUSER["postsperpage"] && is_valid_id($CURUSER["postsperpage"]) && $CURUSER["postsperpage"] <= 50) {
        $postperpage = intval($CURUSER["postsperpage"]);
    } else {
        $postperpage = $f_postsperpage;
    }
    $_totalthreads = TSRowCount("tid", TSF_PREFIX . "threads", $where2);
    sanitize_pageresults($_totalthreads, $pagenumber, $perpage, 200);
    $limitlower = ($pagenumber - 1) * $perpage;
    $limitupper = $pagenumber * $perpage;
    if ($_totalthreads < $limitupper) {
        $limitupper = $_totalthreads;
        if ($_totalthreads < $limitlower) {
            $limitlower = $_totalthreads - $perpage - 1;
        }
    }
    if ($limitlower < 0) {
        $limitlower = 0;
    }
    ($query = sql_query("\r\n\t\t\t\t\t\t\t\tSELECT t.*, f.description, f.password, f.type, f.name as currentforum, f.pid as parent, ff.name as realforum, ff.fid as realforumid, u.username as reallastposterusername, u.id as reallastposteruid, g.namestyle as lastposternamestyle, uu.username as threadstarter, uu.id as threadstarteruid, gg.namestyle as threadstarternamestyle\r\n\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\t\t\t\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\t\t\t\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\t\t\t\t\t\tLEFT JOIN users u ON (t.$lastposteruid = u.id)\r\n\t\t\t\t\t\t\t\tLEFT JOIN usergroups g ON (u.`usergroup` = g.gid)\r\n\t\t\t\t\t\t\t\tLEFT JOIN users uu ON (t.$uid = uu.id)\r\n\t\t\t\t\t\t\t\tLEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid)\r\n\t\t\t\t\t\t\t\tWHERE " . $where . "\r\n\t\t\t\t\t\t\t\tORDER BY " . $order_by . "\r\n\t\t\t\t\t\t\t\tLIMIT " . $limitlower . ", " . $perpage . "\r\n\t\t\t\t\t\t\t")) || sqlerr(__FILE__, 111);
    $totalthreads = [];
    $threads = [];
    $tids = [];
    $multipage = "";
    if (mysqli_num_rows($query)) {
        while ($forum = mysqli_fetch_assoc($query)) {
            if (!isset($currentforum)) {
                $currentforum = $forum["currentforum"];
            }
            if (!isset($fDescription)) {
                $fDescription = substr(strip_tags($forum["description"]), 0, 154) . "...";
                $metadesc = $fDescription;
            }
            check_forum_password($forum["password"], $fid, $BASEURL . "/tsf_forums/forumdisplay.php?$fid = " . $fid . "&do=password");
            $threads[$forum["tid"]] = $forum;
            $tids[$forum["tid"]] = $forum["tid"];
            $realforumid = 0 + $forum["realforumid"];
            $totalthreads[] = $forum["tid"];
        }
        $multipage = construct_page_nav($pagenumber, $perpage, $_totalthreads, tsf_seo_clean_text($currentforum, "fd", $fid, $extra_link_nav), "", false);
    }
    if (count($totalthreads)) {
        $attach = [];
        $a_query = @sql_query("SELECT a_tid FROM " . TSF_PREFIX . "attachments WHERE a_tid IN (0," . @implode(",", $totalthreads) . ")");
        if (0 < mysqli_num_rows($a_query)) {
            while ($s_attachments = mysqli_fetch_assoc($a_query)) {
                if (isset($attach[$s_attachments["a_tid"]])) {
                    $attach[$s_attachments["a_tid"]]++;
                } else {
                    $attach[$s_attachments["a_tid"]] = 1;
                }
            }
        }
    }
    if (is_array($tids) && count($tids)) {
        $tids = @implode(",", $tids);
    }
    if (count($threads)) {
        $query = sql_query("SELECT * FROM " . TSF_PREFIX . "threadsread WHERE `uid` = " . sqlesc($CURUSER["id"]) . " AND tid IN (" . $tids . ")") or ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "threadsread WHERE `uid` = " . sqlesc($CURUSER["id"]) . " AND tid IN (" . $tids . ")")) || sqlerr(__FILE__, 172);
        while ($readthread = mysqli_fetch_assoc($query)) {
            $threads[$readthread["tid"]]["lastread"] = $readthread["dateline"];
        }
    }
    if (is_array($tids) && count($tids)) {
        $userinfo = [];
        $query = sql_query("\r\n\t\t\t\t\t\t\t\t\t\t\tSELECT tid, uid\r\n\t\t\t\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "posts\r\n\t\t\t\t\t\t\t\t\t\t\tWHERE tid IN (0, " . $tids . ")\r\n\t\t\t\t\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\t\t\t\t\tSELECT tid, uid\r\n\t\t\t\t\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "posts\r\n\t\t\t\t\t\t\t\t\t\t\tWHERE tid IN (0, " . $tids . ")\r\n\t\t\t\t\t\t\t\t\t\t")) || sqlerr(__FILE__, 186);
        while ($userposts = mysqli_fetch_assoc($query)) {
            if (isset($userinfo[$userposts["tid"]][$userposts["uid"]])) {
                $userinfo[$userposts["tid"]][$userposts["uid"]]++;
            } else {
                $userinfo[$userposts["tid"]][$userposts["uid"]] = 1;
            }
        }
    }
    require_once INC_PATH . "/functions_cookies.php";
    $forumread = ts_get_array_cookie("forumread", $fid);
    if ($forumread < $CURUSER["last_forum_visit"]) {
        $forumread = $CURUSER["last_forum_visit"];
    }
    $lang->load("header");
    $forum_options = "\r\n<a $href = \"javascript:void(0);\" $id = \"forumoptions" . $fid . "\"><span class=\"\">&nbsp;" . $lang->tsf_forums["foptions"] . "&nbsp;</span></a>\r\n<script $type = \"text/javascript\">\r\n\t\tmenu_register(\"forumoptions" . $fid . "\");\r\n</script>\r\n<div $id = \"forumoptions" . $fid . "_menu\" class=\"menu_popup\" $style = \"display:none;\">\r\n\t<table $cellspacing = \"0\" $cellpadding = \"5\" $border = \"0\" $width = \"200\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\"><b>" . $lang->tsf_forums["foptions"] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/newthread.php?$fid = " . $fid . "\">" . $lang->tsf_forums["new_thread"] . "</a></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/misc.php?$action = markread&$fid = " . $fid . "\">" . $lang->tsf_forums["mark_read"] . "</a></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/tsf_search.php?$sfid = " . $fid . "\">" . $lang->tsf_forums["search_forum"] . "</a></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/syndication.php?$sfid = " . $fid . "\">" . $lang->header["extrarssfeed"] . "</a></td>\r\n\t\t</tr>\r\n\t</table>\r\n</div>\r\n<script $type = \"text/javascript\">\r\n\t\tmenu.activate(true);\r\n</script>\r\n";
    $unreadpost = $load_javascript = 0;
    $str = "";
    $forummoderator = false;
    if (count($threads)) {
        foreach ($threads as $thread) {
            if (!isset($moderatorquerydone)) {
                $forummoderator = is_forum_mod($thread["type"] == "s" ? $realforumid : $fid, $CURUSER["id"]);
                $moderatorquerydone = 1;
            }
            if ($permissions[$thread["fid"]]["canview"] != "yes") {
                if ($fid != $thread["parent"]) {
                    print_no_permission(true, false);
                    exit;
                }
            } else {
                $lastread = 0;
                $class = $thread["sticky"] == 1 ? "highlight" : "";
                if ($forumread < $thread["lastpost"]) {
                    $cutoff = TIMENOW - 604800;
                    if ($cutoff < $thread["lastpost"]) {
                        if (isset($thread["lastread"])) {
                            $lastread = $thread["lastread"];
                        } else {
                            $lastread = 1;
                        }
                    }
                }
                if (!$lastread) {
                    $readcookie = $threadread = ts_get_array_cookie("threadread", $thread["tid"]);
                    if ($forumread < $readcookie) {
                        $lastread = $readcookie;
                    } else {
                        $lastread = $forumread;
                    }
                }
                if ($thread["visible"] == 0) {
                    $images = "<img $src = \"" . $BASEURL . "/tsf_forums/images/moderation.png\" $alt = \"" . $lang->tsf_forums["moderatemsg2"] . "\" $title = \"" . $lang->tsf_forums["moderatemsg2"] . "\" $border = \"0\" class=\"inlineimg\" $alt = \"\"  />";
                } else {
                    if ($lastread < $thread["lastpost"] && $lastread) {
                        if (isset($userinfo[$thread["tid"]][$CURUSER["id"]])) {
                            $images = show_forum_images("don");
                        } else {
                            $images = show_forum_images("on");
                        }
                        $unreadpost = 1;
                    } else {
                        if ($thread["closed"] == "yes") {
                            if (isset($userinfo[$thread["tid"]][$CURUSER["id"]])) {
                                $images = show_forum_images("dofflock");
                            } else {
                                $images = show_forum_images("offlock");
                            }
                        } else {
                            if (isset($userinfo[$thread["tid"]][$CURUSER["id"]])) {
                                $images = show_forum_images("doff");
                            } else {
                                $images = show_forum_images("off");
                            }
                        }
                    }
                }
                $lastpost_data = $_clean_subject = "";
                $lastpost_data = ["lastpost" => $thread["lastpost"], "lastposter" => get_user_color(htmlspecialchars_uni($thread["reallastposterusername"]), $thread["lastposternamestyle"]), "lastposteruid" => $thread["reallastposteruid"]];
                $desc = $stickyimg = $ratingimage = $threadtags = $attachimage = $pollimage = "";
                $subject = htmlspecialchars_uni(ts_remove_badwords($thread["subject"]));
                if ($thread["sticky"] == 1) {
                    $stickyimg = "<img $src = \"" . $BASEURL . "/tsf_forums/images/sticky.png\" class=\"inlineimg\" $border = \"0\" $alt = \"" . $lang->tsf_forums["stickythread"] . "\" $title = \"" . $lang->tsf_forums["stickythread"] . "\" />";
                    $desc = $lang->tsf_forums["sticky"];
                }
                if ($thread["votenum"]) {
                    $thread["voteavg"] = number_format($thread["votetotal"] / $thread["votenum"], 2);
                    $thread["rating"] = round($thread["votetotal"] / $thread["votenum"]);
                    $ratingimgalt = sprintf($lang->tsf_forums["tratingimgalt"], $thread["votenum"], $thread["voteavg"]);
                    $ratingimage = "<img class=\"inlineimg\" $src = \"" . $BASEURL . "/tsf_forums/images/rating/rating_" . $thread["rating"] . ".gif\" $alt = \"" . $ratingimgalt . "\" $title = \"" . $ratingimgalt . "\" $border = \"0\" />";
                }
                if ($thread["pollid"]) {
                    $pollimgalt = $lang->tsf_forums["poll17"];
                    $pollimage = "<img class=\"inlineimg\" $src = \"" . $BASEURL . "/tsf_forums/images/poll.png\" $alt = \"" . $pollimgalt . "\" $title = \"" . $pollimgalt . "\" $border = \"0\" />";
                    $desc = "<strong>" . $lang->tsf_forums["poll17"] . ":</strong> ";
                }
                if (isset($attach[$thread["tid"]])) {
                    $attachimgalt = ts_nf($attach[$thread["tid"]]) . " " . $lang->tsf_forums["a_info"];
                    $attachimage = "<a $href = \"javascript:void(0);\" $onclick = \"TSOpenPopup('" . $BASEURL . "/tsf_forums/attachment.php?$viewattachments = true&amp;$tid = " . $thread["tid"] . "','attachment', 400, 200); return false\"> <img class=\"inlineimg\" $src = \"" . $BASEURL . "/tsf_forums/images/attachment.png\" $alt = \"" . $attachimgalt . "\" $title = \"" . $attachimgalt . "\" $border = \"0\" /></a>";
                }
                if ($stickyimg || $ratingimage || $attachimage || $pollimage) {
                    $threadtags = "<span $style = \"float: right;\">" . $stickyimg . " " . $pollimage . " " . $attachimage . ($ratingimage ? "</span><span $style = \"clear: both; float: right;\">" . $ratingimage : "") . "</span>";
                }
                if ($lastpost_data["lastpost"] == 0) {
                    $lastpost = "<td class=\"" . $class . "\" $style = \"white-space: nowrap;\" $width = \"15%\"><span $style = \"text-align: center;\">" . $lang->tsf_forums["lastpost_never"] . "</span></td>";
                } else {
                    $lastpost_date = my_datee($dateformat, $thread["lastpost"]);
                    $lastpost_time = my_datee($timeformat, $thread["lastpost"]);
                    $lastpost_profilelink = build_profile_link($lastpost_data["lastposter"], $lastpost_data["lastposteruid"]);
                    $lastpost = "\r\n\t\t\t<td class=\"" . $class . "\" $width = \\\"15%\\\">\r\n\t\t\t\t<span class=\"smalltext\" $style = \"text-align: right; white-space: nowrap;\">" . $lastpost_date . " " . $lastpost_time . "<br />\r\n\t\t\t\t<span $style = \"float: right;\">" . $lang->tsf_forums["by"] . " " . $lastpost_profilelink . " <a $href = \"" . tsf_seo_clean_text($subject, "lastpost", $thread["tid"]) . "\" $alt = \"\" $title = \"\"><img $src = \"" . $BASEURL . "/tsf_forums/images/lastpost.gif\" class=\"inlineimg\" $border = \"0\" $alt = \"" . $lang->tsf_forums["gotolastpost"] . "\" $title = \"" . $lang->tsf_forums["gotolastpost"] . "\" /></a></span>\r\n\t\t\t</td>";
                }
                if ($thread["threadstarter"]) {
                    $author = get_user_color(htmlspecialchars_uni($thread["threadstarter"]), $thread["threadstarternamestyle"]);
                } else {
                    $author = $lang->tsf_forums["guest"];
                }
                $replies = ts_nf($thread["replies"]);
                $views = ts_nf($thread["views"]);
                $thread["pages"] = 0;
                $thread["multipage"] = "";
                $threadpages = "";
                $morelink = "";
                $thread["posts"] = $thread["replies"] + 1;
                if ($postperpage < $thread["posts"]) {
                    $thread["pages"] = $thread["posts"] / $postperpage;
                    $thread["pages"] = @ceil($thread["pages"]);
                    if (4 < $thread["pages"]) {
                        $pagesstop = 4;
                        $morelink = "... <a $href = \"" . tsf_seo_clean_text($subject, "lastpost", $thread["tid"]) . "\">" . $lang->global["last"] . "</a>";
                    } else {
                        $pagesstop = $thread["pages"];
                    }
                    for ($i = 1; $i <= $pagesstop; $i++) {
                        $threadpages .= " <a $href = \"" . tsf_seo_clean_text($subject, "page", $thread["tid"], $i) . "\">" . $i . "</a> ";
                    }
                    $thread["multipage"] = " <span class=\"smalltext\">(<img $src = \"" . $BASEURL . "/tsf_forums/images/multipage.gif\" $border = \"0\" $alt = \"" . $lang->tsf_forums["multithread"] . "\" $title = \"" . $lang->tsf_forums["multithread"] . "\" class=\"inlineimg\" /> " . $lang->tsf_forums["pages"] . " " . $threadpages . $morelink . ")</span>";
                } else {
                    $threadpages = "";
                    $morelink = "";
                    $thread["multipage"] = "";
                }
                if ($thread["uid"] == $CURUSER["id"] && $thread["closed"] != "yes" && $permissions[$thread["fid"]]["caneditposts"] == "yes" || $moderator || $forummoderator) {
                    $inline_edit_class = "subject_editable";
                    $load_javascript = 1;
                } else {
                    $inline_edit_class = "";
                }
                $str .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\" $width = \"3%\">" . $images . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\" $width = \"3%\"><img $src = \"" . $BASEURL . "/tsf_forums/images/icons/icon" . $thread["iconid"] . ".gif\" $border = \"0\" $alt = \"\"></td>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"left\" $width = \"64%\">" . $threadtags . $desc . "<a class=\"" . $inline_edit_class . "\" $id = \"tid_" . $thread["tid"] . "\" $href = \"" . tsf_seo_clean_text($subject, "t", $thread["tid"]) . "\">" . ($thread["visible"] == 0 && !$moderator && !$forummoderator && $thread["uid"] != $CURUSER["id"] ? "<span class=\"highlight\">" . $lang->tsf_forums["moderatemsg8"] . "</span>" : $subject) . "</a>" . $thread["multipage"] . "<br /><a $href = \"" . tsf_seo_clean_text($thread["threadstarter"], "u", $thread["threadstarteruid"]) . "\">" . $author . "</a></td>\r\n\t\t\t\t" . $lastpost . "\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\" $width = \"5%\">" . $replies . "</td>\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\" $width = \"5%\">" . $views . "</td>\r\n\t\t\t\t" . ($moderator || $forummoderator ? "\r\n\t\t\t\t<td class=\"" . $class . "\" $align = \"center\" $width = \"5%\"><input $type = \"checkbox\" $id = \"threadids\" $checkme = \"group1\" $name = \"threadids[]\" $value = \"" . $thread["tid"] . "\"></td>" : "") . "\r\n\t\t\t</tr>";
                if ($unreadpost == 0 && ($pagenumber == 1 || !$pagenumber)) {
                    require_once INC_PATH . "/functions_cookies.php";
                    ts_set_array_cookie("forumread", $fid, TIMENOW);
                }
            }
        }
        add_breadcrumb($thread["realforum"], tsf_seo_clean_text($thread["realforum"], $thread["type"] == "s" ? "fd" : "f", $thread["realforumid"]));
        if (isset($currentforum)) {
            add_breadcrumb($currentforum);
        }
    } else {
        $str .= "<tr><td $align = \"left\" $colspan = \"" . ($moderator || $forummoderator ? 7 : 6) . "\"><strong>" . $lang->tsf_forums["no_thread"] . "</strong></td></tr>";
        add_breadcrumb($lang->tsf_forums["no_thread"]);
    }
    $str .= "</table>";
    $colspan = 6;
    $moderation_script = "";
    if ($moderator || $forummoderator) {
        $colspan++;
        $moderation_script = "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction TSdo_action()\r\n\t\t{\r\n\t\t\tvar WorkArea = document.getElementById(\"mod_tools\").value;\r\n\t\t\tswitch(WorkArea)\r\n\t\t\t{\r\n\t\t\t\tcase \"newthread\":\r\n\t\t\t\t\tjumpto('" . $BASEURL . "/tsf_forums/newthread.php?$fid = " . $fid . "');\r\n\t\t\t\tbreak;\r\n\t\t\t\t" . ($usergroups["canmassdelete"] == "yes" ? "case \"deletethreads\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/massdelete.php?$action = deletethreads\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;" : "") . "\r\n\t\t\t\tcase \"approve\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = approve\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"unapprove\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = unapprove\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"approveattachments\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = approveattachments\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"unapproveattachments\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = unapproveattachments\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"open\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = open\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"close\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = close\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"sticky\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = sticky\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"unsticky\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = unsticky\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"movethreads\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = movethreads\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tcase \"mergethreads\":\r\n\t\t\t\t\tdocument.moderation.$action = \"" . $BASEURL . "/tsf_forums/modtools.php?$action = mergethreads\";\r\n\t\t\t\t\tdocument.moderation.submit();\r\n\t\t\t\tbreak;\r\n\t\t\t\tdefault:\r\n\t\t\t\t\treturn false;\r\n\t\t\t\tbreak;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t";
    }
    $str0 = "\r\n\t<!-- start: forumdisplay_newthread/Pagination -->\r\n\t<div $style = \"float: left; margin-bottom: 3px;\" $id = \"navcontainer_f\">\r\n\t\t" . $multipage . "\r\n\t</div>\r\n\t<div $style = \"float: right; margin-bottom: 3px;\">\r\n\t\t<input $value = \"" . $lang->tsf_forums["new_thread"] . "\" $onclick = \"jumpto('" . $BASEURL . "/tsf_forums/newthread.php?$fid = " . $fid . "');\" $type = \"button\" />\r\n\t</div>\r\n\t<!-- end: forumdisplay_newthread/Pagination -->\r\n\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $style = \"clear: both;\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"" . ($colspan - 3) . "\" $align = \"left\">\r\n\t\t\t\t<strong>" . (isset($currentforum) ? $currentforum : "") . "</strong>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"thead\" $colspan = \"3\" $align = \"center\">\r\n\t\t\t\t<strong>" . $forum_options . "</strong>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"6%\" $colspan = \"2\">\r\n\t\t\t\t&nbsp;\r\n\t\t\t</td>\r\n\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"64%\">\r\n\t\t\t\t<span class=\"smalltext\" $style = \"float: right\">" . (isset($sort) && $sort == "votetotal" ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/selected.gif\" $border = \"0\" class=\"inlineimg\" $alt = \"\" /> " : "") . "<a $href = \"" . tsf_seo_clean_text(isset($currentforum) ? $currentforum : "", "fd", $fid, $extra_link_nav2 . "&amp;$sort = votetotal&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0)) . "\"><strong><u>" . $lang->tsf_forums["rating"] . "</u></strong></a></span> " . (isset($sort) && $sort == "subject" ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/selected.gif\" $border = \"0\" class=\"inlineimg\" $alt = \"\" /> " : "") . "<span class=\"smalltext\"><a $href = \"" . tsf_seo_clean_text(isset($currentforum) ? $currentforum : "", "fd", $fid, $extra_link_nav2 . "&amp;$sort = subject&$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0)) . "\"><strong><u>" . $lang->tsf_forums["thread"] . "</u></strong></a></span> / " . (isset($sort) && $sort == "username" ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/selected.gif\" $border = \"0\" class=\"inlineimg\" $alt = \"\" /> " : "") . "<span class=\"smalltext\"><a $href = \"" . tsf_seo_clean_text(isset($currentforum) ? $currentforum : "", "fd", $fid, $extra_link_nav2 . "&amp;$sort = username&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0)) . "\"><strong><u>" . $lang->tsf_forums["starter"] . "</u></strong></a></span>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"15%\">\r\n\t\t\t\t" . (isset($sort) && $sort == "lastpost" ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/selected.gif\" $border = \"0\" class=\"inlineimg\" $alt = \"\" /> " : "") . "<span class=\"smalltext\"><a $href = \"" . tsf_seo_clean_text(isset($currentforum) ? $currentforum : "", "fd", $fid, $extra_link_nav2 . "&amp;$sort = lastpost&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0)) . "\"><strong><u>" . $lang->tsf_forums["lastpost"] . "</u></strong></a></span>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"5%\">\r\n\t\t\t\t" . (isset($sort) && $sort == "replies" ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/selected.gif\" $border = \"0\" class=\"inlineimg\" $alt = \"\" /> " : "") . "<span class=\"smalltext\"><a $href = \"" . tsf_seo_clean_text(isset($currentforum) ? $currentforum : "", "fd", $fid, $extra_link_nav2 . "&amp;$sort = replies&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0)) . "\"><strong><u>" . $lang->tsf_forums["replies"] . "</u></strong></a></span>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"5%\">\r\n\t\t\t\t" . (isset($sort) && $sort == "views" ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/selected.gif\" $border = \"0\" class=\"inlineimg\" $alt = \"\" /> " : "") . "<span class=\"smalltext\"><a $href = \"" . tsf_seo_clean_text(isset($currentforum) ? $currentforum : "", "fd", $fid, $extra_link_nav2 . "&amp;$sort = views&amp;$page = " . intval(isset($_GET["page"]) ? $_GET["page"] : 0)) . "\"><strong><u>" . $lang->tsf_forums["views"] . "</u></strong></a></span>\r\n\t\t\t</td>\r\n\t\t\t" . ($moderator || $forummoderator ? "\r\n\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"5%\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $checkall = \"group1\" $onclick = \"javascript: return select_deselectAll ('moderation', this, 'group1');\" $type = \"checkbox\">\r\n\t\t\t\t</td>\r\n\t\t\t" : "") . "\r\n\t\t</tr>";
    $moderation = "<input $value = \"" . $lang->tsf_forums["new_thread"] . "\" $onclick = \"jumpto('" . $BASEURL . "/tsf_forums/newthread.php?$fid = " . $fid . "');\" $type = \"button\" />";
    if ($moderator || $forummoderator) {
        $str0 .= "\r\n\t<form $method = \"post\" $action = \"\" $name = \"moderation\">\r\n\t" . (isset($realforumid) ? "<input $type = \"hidden\" $name = \"parentfid\" $value = \"" . $realforumid . "\" />" : "") . "\r\n\t<input $type = \"hidden\" $name = \"currentfid\" $value = \"" . $fid . "\" />\r\n\t<input $type = \"hidden\" $name = \"hash\" $value = \"" . $forumtokencode . "\" />";
        $moderation = "\r\n\t\t<select $name = \"mod_tools\" $onchange = \"TSdo_action();\" $id = \"mod_tools\">\r\n\t\t\t<option $value = \"\" $selected = \"Selected\">" . $lang->tsf_forums["mod_options"] . "</option>\\\r\n\t\t\t<option $value = \"\">--------------------------</option>\r\n\t\t\t<option $value = \"approve\">" . $lang->tsf_forums["moderatemsg3"] . "</option>\r\n\t\t\t<option $value = \"unapprove\">" . $lang->tsf_forums["moderatemsg4"] . "</option>\r\n\t\t\t<option $value = \"approveattachments\">" . $lang->tsf_forums["moderatemsg10"] . "</option>\r\n\t\t\t<option $value = \"unapproveattachments\">" . $lang->tsf_forums["moderatemsg11"] . "</option>\r\n\t\t\t" . ($usergroups["canmassdelete"] == "yes" ? "<option $value = \"deletethreads\">" . $lang->tsf_forums["deletethreads"] . "</option>" : "") . "\r\n\t\t\t<option $value = \"open\">" . $lang->tsf_forums["mop1"] . "</option>\r\n\t\t\t<option $value = \"close\">" . $lang->tsf_forums["mop2"] . "</option>\r\n\t\t\t<option $value = \"sticky\">" . $lang->tsf_forums["mop3"] . "</option>\r\n\t\t\t<option $value = \"unsticky\">" . $lang->tsf_forums["mop4"] . "</option>\r\n\t\t\t<option $value = \"movethreads\">" . $lang->tsf_forums["mod_options_m"] . "</option>\r\n\t\t\t<option $value = \"mergethreads\">" . $lang->tsf_forums["mop5"] . "</option>\r\n\t\t</select>\r\n\t\t<input $value = \"" . $lang->tsf_forums["new_thread"] . "\" $onclick = \"jumpto('" . $BASEURL . "/tsf_forums/newthread.php?$fid = " . $fid . "');\" $type = \"button\" />\r\n\t</form>";
    }
    $str .= "\r\n\t<!-- start: forumdisplay_newthread -->\r\n\t<div $style = \"float: left; margin-bottom: 5px; margin-top: 3px;\" $id = \"navcontainer_f\">\r\n\t\t" . $multipage . "\r\n\t</div>\r\n\t<div $style = \"float: right; margin-bottom: 5px; margin-top: 3px;\">\r\n\t\t" . $moderation . "\r\n\t</div>\r\n\t<!-- end: forumdisplay_newthread -->\r\n\r\n\t<!-- begin: Quick Sort -->\r\n\t<form $method = \"get\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t<input $type = \"hidden\" $name = \"fid\" $value = \"" . $fid . "\" />\r\n\t<input $type = \"hidden\" $name = \"action\" $value = \"quick_sort\" />\r\n\t" . (isset($_GET["page"]) ? "<input $type = \"hidden\" $name = \"page\" $value = \"" . intval($_GET["page"]) . "\" />" : "") . "\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $align = \"left\" $width = \"100%\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->tsf_forums["qtitlemain"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t\t\t<b>" . $lang->tsf_forums["qtitle1"] . "</b>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<select $name = \"sort\">\r\n\t\t\t\t\t\t\t\t\t<option $value = \"subject\"" . ($sort == "subject" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["thread"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"username\"" . ($sort == "username" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["starter"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"votetotal\"" . ($sort == "votetotal" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["rating"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"lastpost\"" . ($sort == "lastpost" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder1"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"replies\"" . ($sort == "replies" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["replies"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"views\"" . ($sort == "views" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["views"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"dateline\"" . ($sort == "dateline" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder2"] . "</option>\r\n\t\t\t\t\t\t\t\t</select>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t\t\t<b>" . $lang->tsf_forums["qtitle2"] . "</b>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<select $name = \"order\">\r\n\t\t\t\t\t\t\t\t\t<option $value = \"asc\"" . ($order == "ASC" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["asc"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"desc\"" . ($order == "DESC" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["desc"] . "</option>\r\n\t\t\t\t\t\t\t\t</select>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t\t\t\t<b>" . $lang->tsf_forums["qtitle3"] . "</b>\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<select $name = \"daysprune\">\r\n\t\t\t\t\t\t\t\t\t<option $value = \"1\"" . ($daysprune == "1" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder3"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"2\"" . ($daysprune == "2" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder4"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"7\"" . ($daysprune == "7" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder5"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"10\"" . ($daysprune == "10" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder6"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"14\"" . ($daysprune == "14" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder7"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"30\"" . ($daysprune == "30" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder8"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"45\"" . ($daysprune == "45" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder9"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"60\"" . ($daysprune == "60" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder10"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"75\"" . ($daysprune == "75" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder11"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"100\"" . ($daysprune == "100" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder12"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"365\"" . ($daysprune == "365" ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder13"] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"-1\"" . (!$daysprune ? " $selected = \"selected\"" : "") . ">" . $lang->tsf_forums["qorder14"] . "</option>\r\n\t\t\t\t\t\t\t\t</select>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"none\">\r\n\t\t\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t\t\t&nbsp;\r\n\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t<input $type = \"submit\" class=\"button\" $value = \"" . $lang->tsf_forums["button_1"] . "\" />\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t</form>\r\n\t<!-- end: Quick Sort -->\r\n\r\n\t<!-- begin: footer -->\r\n\t<table class=\"subheader\" $style = \"clear: both; padding-top: 10px;\" $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $align = \"center\" $width = \"100%\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\" $style = \"padding: 10px 0px 10px 0px; margin: 0px 0px 0px 0px;\">\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/on.gif\" $alt = \"" . $lang->tsf_forums["t_new_posts"] . "\" $title = \"" . $lang->tsf_forums["t_new_posts"] . "\" />\r\n\t\t\t\t\t<span class=\"smalltext\">" . $lang->tsf_forums["t_new_posts"] . "</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/off.gif\" $alt = \"" . $lang->tsf_forums["t_no_new_posts"] . "\" $title = \"" . $lang->tsf_forums["t_no_new_posts"] . "\" />\r\n\t\t\t\t\t<span class=\"smalltext\">" . $lang->tsf_forums["t_no_new_posts"] . "</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/offlock.gif\" $alt = \"" . $lang->tsf_forums["thread_locked"] . "\" $title = \"" . $lang->tsf_forums["thread_locked"] . "\" />\r\n\t\t\t\t\t<span class=\"smalltext\">" . $lang->tsf_forums["thread_locked"] . "</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/dot.gif\" $alt = \"" . $lang->tsf_forums["you_have_p"] . "\" $title = \"" . $lang->tsf_forums["you_have_p"] . "\" />\r\n\t\t\t\t\t<span class=\"smalltext\">" . $lang->tsf_forums["you_have_p"] . "</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t<!-- end: footer -->";
    $query = sql_query("SELECT m.userid, m.forumid, u.username, g.namestyle\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "moderators m\r\n\t\t\t\t\t\t\tINNER JOIN users u ON (m.$userid = u.id)\r\n\t\t\t\t\t\t\tINNER JOIN usergroups g ON (u.`usergroup` = g.gid)");
    $imodcache = [];
    if (mysqli_num_rows($query)) {
        while ($forummoderators = mysqli_fetch_assoc($query)) {
            $imodcache[(string) $forummoderators["forumid"]][(string) $forummoderators["userid"]] = $forummoderators;
        }
    }
    ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.*, u.username as realrealusername, u.id as reallastposteruserid, g.namestyle\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tLEFT JOIN users u ON (f.$lastposteruid = u.id)\r\n\t\t\t\t\t\t\tLEFT JOIN usergroups g ON (g.$gid = u.usergroup)\r\n\t\t\t\t\t\t\tWHERE f.$type = 's' AND f.$pid = " . sqlesc($fid) . " ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 744);
    $subforums = "\r\n\t\t<!-- start: subforums -->\r\n\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t\t<thead>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"thead\" $colspan = \"6\">\r\n\t\t\t\t\t\t\t" . ts_collapse("subforums#" . $fid) . "\r\n\t\t\t\t\t\t\t<strong>" . (isset($currentforum) ? $currentforum : "") . " " . $lang->tsf_forums["sforums"] . "</a></strong>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</thead>\r\n\t\t\t\t" . ts_collapse("subforums#" . $fid, 2) . "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"subheader\" $width = \"32\">&nbsp;</td>\r\n\t\t\t\t\t\t<td class=\"subheader\" $width = \"32\">&nbsp;</td>\r\n\t\t\t\t\t\t<td class=\"subheader\"><strong>" . $lang->tsf_forums["forum"] . "</strong></td>\r\n\t\t\t\t\t\t<td class=\"subheader\" $style = \"white-space: nowrap;\" $align = \"center\" $width = \"85\"><strong>" . $lang->tsf_forums["threads"] . "</strong></td>\r\n\t\t\t\t\t\t<td class=\"subheader\" $style = \"white-space: nowrap;\" $align = \"center\" $width = \"85\"><strong>" . $lang->tsf_forums["posts"] . "</strong></td>\r\n\t\t\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"200\"><strong>" . $lang->tsf_forums["lastpost"] . "</strong></td>\r\n\t\t\t\t\t</tr>\r\n\t\t";
    $showsubforums = false;
    if (mysqli_num_rows($query)) {
        while ($forum = mysqli_fetch_assoc($query)) {
            if (isset($permissions[$forum["fid"]]["canview"]) && $permissions[$forum["fid"]]["canview"] == "yes") {
                $showsubforums = true;
                $lastpost_data = "";
                $hideinfo = false;
                $posts = ts_nf($forum["posts"]);
                $threads = ts_nf($forum["threads"]);
                if ($forum["password"] != "" && ($_COOKIE["forumpass_" . $forum["fid"]] != md5($CURUSER["id"] . $forum["password"] . $securehash) || empty($_COOKIE["forumpass_" . $forum["fid"]]) || strlen($_COOKIE["forumpass_" . $forum["fid"]]) != 32)) {
                    $hideinfo = true;
                }
                $lastpost_data = ["lastpost" => $forum["lastpost"], "lastpostsubject" => $forum["lastpostsubject"], "lastposter" => get_user_color(htmlspecialchars_uni($forum["realrealusername"]), $forum["namestyle"]), "lastposttid" => $forum["lastposttid"], "lastposteruid" => $forum["reallastposteruserid"]];
                if ($hideinfo) {
                    unset($lastpost_data);
                }
                if (($lastpost_data["lastpost"] == 0 || $lastpost_data["lastposter"] == "") && !$hideinfo) {
                    $lastpost = "<span $style = \"text-align: center;\">" . $lang->tsf_forums["lastpost_never"] . "</span>";
                } else {
                    if ($hideinfo != 1) {
                        $lastpost_date = my_datee($dateformat, $forum["lastpost"]);
                        $lastpost_time = my_datee($timeformat, $forum["lastpost"]);
                        $lastpost_profilelink = build_profile_link($lastpost_data["lastposter"], $lastpost_data["lastposteruid"]);
                        $lastposttid = $lastpost_data["lastposttid"];
                        $lastpost_subject = $full_lastpost_subject = $lastpost_data["lastpostsubject"];
                        if (25 < @strlen($lastpost_subject)) {
                            $lastpost_subject = cutename($lastpost_subject, 25, false);
                        }
                        $full_lastpost_subject = htmlspecialchars_uni(ts_remove_badwords($full_lastpost_subject));
                        $_clean_subject = ts_remove_badwords($lastpost_subject);
                        $lastpost = "\r\n\t\t\t<span class=\"smalltext\">\r\n\t\t\t\t<a $href = \"" . tsf_seo_clean_text($_clean_subject, "lastpost", $lastposttid) . "\" $title = \"" . $full_lastpost_subject . "\" $title = \"" . $full_lastpost_subject . "\"><strong>" . htmlspecialchars_uni($_clean_subject) . "</strong></a>\r\n\t\t\t\t<br />" . $lastpost_date . " " . $lastpost_time . "<br />" . $lang->tsf_forums["by"] . " " . $lastpost_profilelink . " <a $href = \"" . tsf_seo_clean_text($_clean_subject, "lastpost", $lastposttid) . "\" $alt = \"" . $full_lastpost_subject . "\" $title = \"" . $full_lastpost_subject . "\"><img $src = \"" . $BASEURL . "/tsf_forums/images/lastpost.gif\" class=\"inlineimg\" $border = \"0\" $alt = \"" . $lang->tsf_forums["gotolastpost"] . "\" $title = \"" . $lang->tsf_forums["gotolastpost"] . "\"></a>\r\n\t\t\t</span>";
                    }
                }
                $forumread = ts_get_array_cookie("forumread", $forum["fid"]);
                if ($CURUSER["last_forum_visit"] < $lastpost_data["lastpost"] && $forumread < $lastpost_data["lastpost"] && $lastpost_data["lastpost"] != 0) {
                    $folder = "on";
                    $altonoff = $lang->tsf_forums["new_posts"];
                } else {
                    $folder = "off";
                    $altonoff = $lang->tsf_forums["no_new_posts"];
                }
                $moderatorslist = "";
                if (isset($imodcache[(string) $forum["pid"]]) && is_array($imodcache[(string) $forum["pid"]]) && 0 < count($imodcache[(string) $forum["pid"]])) {
                    foreach ($imodcache[(string) $forum["pid"]] as $fmoderator) {
                        if ($moderatorslist == "") {
                            $moderatorslist = "<a $href = \"" . tsf_seo_clean_text($fmoderator["username"], "u", $fmoderator["userid"]) . "\" $rel = \"nofollow\">" . get_user_color($fmoderator["username"], $fmoderator["namestyle"]) . "</a>";
                        } else {
                            $moderatorslist .= ", <a $href = \"" . tsf_seo_clean_text($fmoderator["username"], "u", $fmoderator["userid"]) . "\" $rel = \"nofollow\">" . get_user_color($fmoderator["username"], $fmoderator["namestyle"]) . "</a>";
                        }
                    }
                }
                $subforums .= "\r\n\r\n\t\t\t<!-- start: subforums -->\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/" . $folder . ".gif\" $alt = \"" . $altonoff . "\" $title = \"" . $altonoff . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t\t" . ($forum["image"] ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/forumicons/" . $forum["image"] . "\" $alt = \"\" $title = \"\" />" : "") . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $valign = \"top\">\r\n\t\t\t\t\t\t<strong><a $href = \"" . tsf_seo_clean_text($forum["name"], "fd", $forum["fid"]) . "\">" . $forum["name"] . "</a></strong>\r\n\t\t\t\t\t\t<div class=\"smalltext\">" . $forum["description"] . "</div>\r\n\t\t\t\t\t\t" . ($moderatorslist ? "<div class=\"smalltext\">" . sprintf($lang->tsf_forums["modlist"], $moderatorslist) . "</div>" : "") . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $style = \"white-space: nowrap;\" $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $threads . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $style = \"white-space: nowrap;\" $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $posts . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $tyle = \"white-space: nowrap;\" $align = \"right\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $lastpost . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t<!-- end: subforums -->";
            }
        }
    }
    $subforums .= "\r\n\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t<!-- end: subforums -->\r\n";
    if ($load_javascript) {
        $str .= "\r\n\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/tsf_forums/scripts/prototype.lite.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/tsf_forums/scripts/moo.ajax.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/tsf_forums/scripts/inline_edit.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tvar $loading_text = '" . $lang->tsf_forums["ajax_loading"] . "';\r\n\t\t\t\tvar $saving_changes = '" . $lang->tsf_forums["saving_changes"] . "';\r\n\t\t\t\tnew inlineEditor(\"" . $BASEURL . "/tsf_forums/tsf_ajax.php?$action = edit_subject\", {className: \"subject_editable\", spinnerImage: \"images/spinner.gif\", lang_click_edit: \"" . $lang->tsf_forums["click_hold_edit"] . "\"});\r\n\t\t\t</script>" . $moderation_script;
    }
    stdhead(@str_replace("&amp;", "&", isset($currentforum) ? $currentforum : ""));
    if (isset($warningmessage)) {
        echo $warningmessage;
    }
    build_breadcrumb();
    echo show_announcements(isset($realforumid) ? $realforumid : 0, $fid) . ($showsubforums ? $subforums : "") . $str0 . $str;
    stdfoot();
} else {
    stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
    exit;
}

?>