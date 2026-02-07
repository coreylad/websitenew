<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/manage_torrents.lang");
$Message = "";
$Found = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$category = isset($_GET["category"]) ? intval($_GET["category"]) : (isset($_POST["category"]) ? intval($_POST["category"]) : "");
$browsecategory = isset($_GET["browsecategory"]) ? intval($_GET["browsecategory"]) : (isset($_POST["browsecategory"]) ? intval($_POST["browsecategory"]) : "");
$searchword = urldecode(isset($_GET["searchword"]) ? $_GET["searchword"] : (isset($_POST["searchword"]) ? $_POST["searchword"] : ""));
$searchtype = urldecode(isset($_GET["searchtype"]) ? $_GET["searchtype"] : (isset($_POST["searchtype"]) ? $_POST["searchtype"] : ""));
$actiontype = urldecode(isset($_GET["actiontype"]) ? $_GET["actiontype"] : (isset($_POST["actiontype"]) ? $_POST["actiontype"] : ""));
$sort = urldecode(isset($_GET["sort"]) ? $_GET["sort"] : (isset($_POST["sort"]) ? $_POST["sort"] : ""));
$order = urldecode(isset($_GET["order"]) ? $_GET["order"] : (isset($_POST["order"]) ? $_POST["order"] : ""));
$extraquery1 = "";
$extraquery2 = "";
$extralink = "";
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'ANNOUNCE'");
$Result = mysqli_fetch_assoc($Q);
$ANNOUNCE = unserialize($Result["content"]);
$xbt_active = $ANNOUNCE["xbt_active"];
if ($browsecategory) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT type FROM categories WHERE id = '" . $browsecategory . "'");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $cate_type = $Result["type"];
        if ($cate_type == "s") {
            $extraquery1 = " WHERE category = " . $browsecategory;
            $extraquery2 = " WHERE t.category = " . $browsecategory . " ";
            $extralink = "browsecategory=" . $browsecategory . "&amp;";
        } else {
            $array_cats = [];
            $array_cats[] = $browsecategory;
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM categories WHERE pid = '" . $browsecategory . "'");
            while ($sub_cats = mysqli_fetch_assoc($query)) {
                $array_cats[] = $sub_cats["id"];
            }
            $extraquery1 = " WHERE category IN (0," . implode(",", $array_cats) . ")";
            $extraquery2 = " WHERE t.category IN (0," . implode(",", $array_cats) . ")";
            $extralink = "browsecategory=" . $browsecategory . "&amp;";
        }
    }
}
if ($searchword != "") {
    if ($extraquery1) {
        $extraquery1 .= " AND (name LIKE '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "%" . $searchword . "%") . "')";
        $extraquery2 .= " AND (t.name LIKE '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "%" . $searchword . "%") . "') ";
        $extralink .= "searchword=" . urlencode($browsecategory) . "&amp;";
    } else {
        $extraquery1 .= " WHERE (name LIKE '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "%" . $searchword . "%") . "')";
        $extraquery2 .= " WHERE (t.name LIKE '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "%" . $searchword . "%") . "') ";
        $extralink .= "searchword=" . urlencode($searchword) . "&amp;";
    }
}
if ($searchtype != "") {
    switch ($searchtype) {
        case "banned":
            if ($extraquery1) {
                $extraquery1 .= " AND banned = 'yes'";
                $extraquery2 .= " AND t.banned = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE banned = 'yes'";
                $extraquery2 .= " WHERE t.banned = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "deadonly":
            if ($extraquery1) {
                $extraquery1 .= " AND (visible = 'no' OR (seeders=0 AND leechers=0))";
                $extraquery2 .= " AND (t.visible = 'no' OR (t.seeders=0 AND t.leechers=0))";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE (visible = 'no' OR (seeders=0 AND leechers=0))";
                $extraquery2 .= " WHERE (t.visible = 'no' OR (t.seeders=0 AND t.leechers=0)) ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "internal":
            if ($extraquery1) {
                $extraquery1 .= " AND ts_external = 'no'";
                $extraquery2 .= " AND t.ts_external = 'no' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE ts_external = 'no'";
                $extraquery2 .= " WHERE t.ts_external = 'no' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "external":
            if ($extraquery1) {
                $extraquery1 .= " AND ts_external = 'yes'";
                $extraquery2 .= " AND t.ts_external = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE ts_external = 'yes'";
                $extraquery2 .= " WHERE t.ts_external = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "silver":
            if ($extraquery1) {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " AND download_multiplier = '0.5'";
                    $extraquery2 .= " AND t.download_multiplier = '0.5'";
                } else {
                    $extraquery1 .= " AND silver = 'yes'";
                    $extraquery2 .= " AND t.silver = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " WHERE download_multiplier = '0.5'";
                    $extraquery2 .= " WHERE t.download_multiplier = '0.5'";
                } else {
                    $extraquery1 .= " WHERE silver = 'yes'";
                    $extraquery2 .= " WHERE t.silver = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "free":
            if ($extraquery1) {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " AND download_multiplier = '0'";
                    $extraquery2 .= " AND t.download_multiplier = '0'";
                } else {
                    $extraquery1 .= " AND free = 'yes'";
                    $extraquery2 .= " AND t.free = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " WHERE download_multiplier = '0'";
                    $extraquery2 .= " WHERE t.download_multiplier = '0'";
                } else {
                    $extraquery1 .= " WHERE free = 'yes'";
                    $extraquery2 .= " WHERE t.free = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "doubleuploads":
            if ($extraquery1) {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " AND upload_multiplier = '2'";
                    $extraquery2 .= " AND t.upload_multiplier = '2'";
                } else {
                    $extraquery1 .= " AND doubleupload = 'yes'";
                    $extraquery2 .= " AND t.doubleupload = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " WHERE upload_multiplier = '2'";
                    $extraquery2 .= " WHERE t.upload_multiplier = '2'";
                } else {
                    $extraquery1 .= " WHERE doubleupload = 'yes'";
                    $extraquery2 .= " WHERE t.doubleupload = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "recommend":
            if ($extraquery1) {
                $extraquery1 .= " AND sticky = 'yes'";
                $extraquery2 .= " AND t.sticky = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE sticky = 'yes'";
                $extraquery2 .= " WHERE t.sticky = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "anonymous":
            if ($extraquery1) {
                $extraquery1 .= " AND anonymous = 'yes'";
                $extraquery2 .= " AND t.anonymous = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE anonymous = 'yes'";
                $extraquery2 .= " WHERE t.anonymous = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "request":
            if ($extraquery1) {
                $extraquery1 .= " AND isrequest = 'yes'";
                $extraquery2 .= " AND t.isrequest = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE isrequest = 'yes'";
                $extraquery2 .= " WHERE t.isrequest = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
    }
}
if ($Act == "update") {
    $torrentid = isset($_POST["torrentid"]) ? $_POST["torrentid"] : "";
    if (empty($actiontype)) {
        $Message = function_76($Language[43]);
    } else {
        if (!is_array($torrentid) || count($torrentid) < 1) {
            $Message = function_76($Language[44]);
        } else {
            $torrentids = implode(",", $torrentid);
            if ($torrentids) {
                switch ($actiontype) {
                    case "move":
                        if (0 < $category) {
                            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET category = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $category) . "' WHERE id IN (" . $torrentids . ")");
                        } else {
                            $Message = function_76($Language[45]);
                        }
                        break;
                    case "delete":
                        foreach ($torrentid as $id) {
                            function_151($id);
                        }
                        break;
                    case "sticky":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET sticky = IF(sticky = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "free":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET free = IF(free = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET download_multiplier = IF(download_multiplier = '0', '1', '0') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "silver":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET silver = IF(silver = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET download_multiplier = IF(download_multiplier = '0.5', '1', '0.5') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "doubleupload":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET doubleupload = IF(doubleupload = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET upload_multiplier = IF(upload_multiplier = '2', '1', '2') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "visible":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET visible = IF(visible = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "anonymous":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET anonymous = IF(anonymous = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "banned":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET banned = IF(banned = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "nuke":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET isnuked = IF(isnuked = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "openclose":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET allowcomments = IF(allowcomments = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "request":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET isrequest = IF(isrequest = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    default:
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET flags = 0 WHERE download_multiplier = 1 AND upload_multiplier = 1 AND flags != 0");
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET flags = 2 WHERE (download_multiplier = 0 OR download_multiplier = 0.5 OR upload_multiplier = 2) AND flags != 2");
                        if (empty($Message)) {
                            function_79(str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], $actiontype . " (" . $torrentids . ")"], $Language[39]));
                            if (isset($_GET["from_browse"])) {
                                function_78("../browse.php" . (isset($_GET["page"]) ? "?page=" . intval($_GET["page"]) : ""));
                            }
                        }
                }
            } else {
                $Message = function_76("I can not implode torrent ids!");
            }
        }
    }
}
$Links = [];
$Links["searchword"] = urlencode($searchword);
$Links["browsecategory"] = urlencode($browsecategory);
$Links["searchtype"] = urlencode($searchtype);
$Links["page"] = isset($_GET["page"]) ? intval($_GET["page"]) : 0;
$orderby = "t.added DESC, t.name ASC";
if ($sort) {
    $sort_array = ["name", "category", "size", "added", "owner", "seeders", "leechers"];
    $sort = in_array($sort, $sort_array) ? $sort : false;
    $order = strtolower($order) == "asc" ? "ASC" : "DESC";
}
if ($sort) {
    $extralink .= "sort=" . $sort . "&amp;";
    $extralink .= "order=" . $order . "&amp;";
    $orderby = "t." . $sort . " " . $order;
}
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM torrents" . $extraquery1));
list($pagertop, $limit) = function_82(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=manage_torrents&amp;" . $extralink);
$catdropdown = var_460("category", $category, "<option value=\"0\">" . $Language[6] . "</option>");
$catdropdown2 = var_460("browsecategory", $browsecategory, "<option value=\"0\">" . $Language[6] . "</option>");
$searchtype_dropdown = "\r\n<select name=\"searchtype\">\r\n<option value=\"0\">" . $Language[41] . "</option>";
foreach (["deadonly" => $Language[46], "internal" => $Language[47], "external" => $Language[48], "silver" => $Language[49], "free" => $Language[50], "recommend" => $Language[51], "doubleuploads" => $Language[52], "anonymous" => $Language[53], "request" => $Language[54], "banned" => $Language[56]] as $valuename => $description) {
    $searchtype_dropdown .= "\r\n\t<option value=\"" . $valuename . "\"" . ($searchtype == $valuename ? " selected=\"selected\"" : "") . ">" . $description . "</option>";
}
$searchtype_dropdown .= "\r\n</select>";
$Found = "\r\n<form action=\"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_torrents&amp;act=search" . (isset($_GET["page"]) ? "&amp;page=" . intval($_GET["page"]) : "") . "\" method=\"post\" name=\"manage_torrents2\">\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\">\r\n\t\t\t" . $Language[40] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t " . $Language[41] . " <input type=\"text\" name=\"searchword\" value=\"" . htmlspecialchars($searchword) . "\" size=\"40\" dir=\"ltr\" tabindex=\"1\" /> " . $catdropdown2 . " " . $searchtype_dropdown . " <input type=\"submit\" value=\"" . $Language[12] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.*, u.username, g.namestyle, c.name as catname FROM torrents t LEFT JOIN users u ON (t.owner=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) LEFT JOIN categories c ON (t.category=c.id)" . $extraquery2 . " ORDER BY " . $orderby . " " . $limit);
if (mysqli_num_rows($query)) {
    $Found .= "\r\n\t" . $pagertop . "\r\n\t<form action=\"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_torrents&amp;act=update" . (isset($_GET["page"]) ? "&amp;page=" . intval($_GET["page"]) : "") . "\" method=\"post\" name=\"manage_torrents\">\r\n\t<input type=\"hidden\" name=\"searchword\" value=\"" . urlencode($searchword) . "\" />\r\n\t<input type=\"hidden\" name=\"browsecategory\" value=\"" . urlencode($browsecategory) . "\" />\r\n\t<input type=\"hidden\" name=\"searchtype\" value=\"" . urlencode($searchtype) . "\" />\r\n\t<input type=\"hidden\" name=\"sort\" value=\"" . urlencode($sort) . "\" />\r\n\t<input type=\"hidden\" name=\"order\" value=\"" . urlencode($order) . "\" />\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan=\"9\" align=\"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("name", $Language[5]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t\t" . $Language[14] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("category", $Language[6]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("size", $Language[7]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("added", $Language[8]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("owner", $Language[9]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("seeders", $Language[10]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("leechers", $Language[11]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll ('manage_torrents', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t";
    while ($Torrent = mysqli_fetch_assoc($query)) {
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a href=\"../details.php?id=" . $Torrent["id"] . "\">" . htmlspecialchars($Torrent["name"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t" . function_163($Torrent) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Torrent["catname"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . var_238($Torrent["size"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . function_84($Torrent["added"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . $Torrent["username"] . "\">" . function_83($Torrent["username"], $Torrent["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . number_format($Torrent["seeders"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . number_format($Torrent["leechers"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\"><input type=\"checkbox\" name=\"torrentid[]\" value=\"" . $Torrent["id"] . "\" checkme=\"group\" " . (isset($_POST["torrentid"]) && in_array($Torrent["id"], $_POST["torrentid"]) ? " checked=\"checked\"" : "") . "/></td>\r\n\t\t</tr>\r\n\t\t";
    }
    $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td colspan=\"3\" align=\"right\" class=\"tcat2\">\r\n\t\t\t\t<select name=\"actiontype\" onchange=\"check_it(this)\">\r\n\t\t\t\t\t<option value=\"0\"" . ($actiontype == "0" ? " selected=\"selected\"" : "") . ">" . $Language[26] . "</option>\r\n\t\t\t\t\t<option value=\"move\"" . ($actiontype == "move" ? " selected=\"selected\"" : "") . ">" . $Language[27] . "</option>\r\n\t\t\t\t\t<option value=\"delete\"" . ($actiontype == "delete" ? " selected=\"selected\"" : "") . ">" . $Language[28] . "</option>\r\n\t\t\t\t\t<option value=\"sticky\"" . ($actiontype == "sticky" ? " selected=\"selected\"" : "") . ">" . $Language[29] . "</option>\r\n\t\t\t\t\t<option value=\"free\"" . ($actiontype == "free" ? " selected=\"selected\"" : "") . ">" . $Language[30] . "</option>\r\n\t\t\t\t\t<option value=\"silver\"" . ($actiontype == "silver" ? " selected=\"selected\"" : "") . ">" . $Language[31] . "</option>\r\n\t\t\t\t\t<option value=\"doubleupload\"" . ($actiontype == "doubleupload" ? " selected=\"selected\"" : "") . ">" . $Language[36] . "</option>\r\n\t\t\t\t\t<option value=\"visible\"" . ($actiontype == "visible" ? " selected=\"selected\"" : "") . ">" . $Language[32] . "</option>\r\n\t\t\t\t\t<option value=\"anonymous\"" . ($actiontype == "anonymous" ? " selected=\"selected\"" : "") . ">" . $Language[33] . "</option>\r\n\t\t\t\t\t<option value=\"banned\"" . ($actiontype == "banned" ? " selected=\"selected\"" : "") . ">" . $Language[34] . "</option>\r\n\t\t\t\t\t<option value=\"nuke\"" . ($actiontype == "nuke" ? " selected=\"selected\"" : "") . ">" . $Language[35] . "</option>\t\t\t\t\t\r\n\t\t\t\t\t<option value=\"openclose\"" . ($actiontype == "openclose" ? " selected=\"selected\"" : "") . ">" . $Language[37] . "</option>\r\n\t\t\t\t\t<option value=\"request\"" . ($actiontype == "request" ? " selected=\"selected\"" : "") . ">" . $Language[55] . "</option>\r\n\t\t\t\t</select>\r\n\t\t\t\t<input type=\"submit\" value=\"" . $Language[38] . "\" />\r\n\t\t\t</td>\r\n\t\t\t<td colspan=\"6\" align=\"left\" class=\"tcat2\">\r\n\t\t\t\t<div id=\"movetorrent\" style=\"display: none;\">\r\n\t\t\t\t\t" . $catdropdown . "\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t" . $pagertop;
} else {
    $Message = function_76($Language[4]);
}
echo "\r\n<script type=\"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar frm=document.forms[formname];\r\n\t\tfor(i=0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].checked=elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n\tfunction check_it(wHAT)\r\n\t{\r\n\t\tif (wHAT.value == \"move\")\r\n\t\t{\r\n\t\t\tTSGetID(\"movetorrent\").style.display = \"block\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"movetorrent\").style.display = \"none\";\r\n\t\t}\r\n\t}\r\n</script>\r\n\r\n" . $Message . "\r\n" . $Found;
function function_75()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function function_77()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_236("../index.php");
    }
}
function function_78($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href=\"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_88($bytes = 0)
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 0) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 0, 2) . " TB";
}
function function_84($timestamp = "")
{
    var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date(var_265, $timestamp);
}
function function_83($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function function_86($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    var_240 = ceil($numresults / $perpage);
    if (var_240 == 0) {
        var_240 = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if (var_240 < $page) {
            $page = var_240;
        }
    }
}
function function_87($pagenumber, $perpage, $total)
{
    var_241 = $perpage * ($pagenumber - 1);
    var_89 = var_241 + $perpage;
    if ($total < var_89) {
        var_89 = $total;
    }
    var_241++;
    return ["first" => number_format(var_241), "last" => number_format(var_89)];
}
function function_82($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        var_242 = @ceil($results / $perpage);
    } else {
        var_242 = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    function_86($results, $pagenumber, $perpage, 200);
    var_243 = ($pagenumber - 1) * $perpage;
    var_244 = $pagenumber * $perpage;
    if ($results < var_244) {
        var_244 = $results;
        if ($results < var_243) {
            var_243 = $results - $perpage - 1;
        }
    }
    if (var_243 < 0) {
        var_243 = 0;
    }
    var_245 = var_246 = var_247 = var_248 = var_249 = "";
    var_250 = 0;
    if ($results <= $perpage) {
        var_251["pagenav"] = false;
        return ["", "LIMIT " . var_243 . ", " . $perpage];
    }
    var_251["pagenav"] = true;
    $total = number_format($results);
    var_251["last"] = false;
    var_251["first"] = var_251["last"];
    var_251["next"] = var_251["first"];
    var_251["prev"] = var_251["next"];
    if (1 < $pagenumber) {
        var_252 = $pagenumber - 1;
        var_253 = function_87(var_252, $perpage, $results);
        var_251["prev"] = true;
    }
    if ($pagenumber < var_242) {
        var_254 = $pagenumber + 1;
        var_255 = function_87(var_254, $perpage, $results);
        var_251["next"] = true;
    }
    var_256 = "3";
    if (!isset(var_257) || !is_array(var_257)) {
        var_258 = "10 50 100 500 1000";
        var_257[] = preg_split("#\\s+#s", var_258, -1, PREG_SPLIT_NO_EMPTY);
        while (var_250++ < var_242) {
        }
        var_259 = isset(var_252) && var_252 != 1 ? "page=" . var_252 : "";
        var_245 = "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td style=\"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . var_242 . "</li>\r\n\t\t\t\t\t\t" . (var_251["first"] ? "<li><a class=\"smalltext\" href=\"" . $address . "\" title=\"First Page - Show Results " . var_260["first"] . " to " . var_260["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . (var_251["prev"] ? "<li><a class=\"smalltext\" href=\"" . $address . var_259 . "\" title=\"Previous Page - Show Results " . var_253["first"] . " to " . var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . var_245 . "\r\n\t\t\t\t\t\t" . (var_251["next"] ? "<li><a class=\"smalltext\" href=\"" . $address . "page=" . var_254 . "\" title=\"Next Page - Show Results " . var_255["first"] . " to " . var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . (var_251["last"] ? "<li><a class=\"smalltext\" href=\"" . $address . "page=" . var_242 . "\" title=\"Last Page - Show Results " . var_261["first"] . " to " . var_261["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [var_245, "LIMIT " . var_243 . ", " . $perpage];
    }
    if (var_256 <= abs(var_250 - $pagenumber) && var_256 != 0) {
        if (var_250 == 1) {
            var_260 = function_87(1, $perpage, $results);
            var_251["first"] = true;
        }
        if (var_250 == var_242) {
            var_261 = function_87(var_242, $perpage, $results);
            var_251["last"] = true;
        }
        if (in_array(abs(var_250 - $pagenumber), var_257) && var_250 != 1 && var_250 != var_242) {
            var_262 = function_87(var_250, $perpage, $results);
            var_263 = var_250 - $pagenumber;
            if (0 < var_263) {
                var_263 = "+" . var_263;
            }
            var_245 .= "<li><a class=\"smalltext\" href=\"" . $address . (var_250 != 1 ? "page=" . var_250 : "") . "\" title=\"Show results " . var_262["first"] . " to " . var_262["last"] . " of " . $total . "\"><!--" . var_263 . "-->" . var_250 . "</a></li>";
        }
    } else {
        if (var_250 == $pagenumber) {
            var_264 = function_87(var_250, $perpage, $results);
            var_245 .= "<li><a name=\"current\" class=\"current\" title=\"Showing results " . var_264["first"] . " to " . var_264["last"] . " of " . $total . "\">" . var_250 . "</a></li>";
        } else {
            var_262 = function_87(var_250, $perpage, $results);
            var_245 .= "<li><a href=\"" . $address . (var_250 != 1 ? "page=" . var_250 : "") . "\" title=\"Show results " . var_262["first"] . " to " . var_262["last"] . " of " . $total . "\">" . var_250 . "</a></li>";
        }
    }
}
function function_163($torrents)
{
    global $Language;
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'MAIN'");
    $Result = mysqli_fetch_assoc($Q);
    var_27 = unserialize($Result["content"]);
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'THEME'");
    $Result = mysqli_fetch_assoc($Q);
    var_28 = unserialize($Result["content"]);
    var_282 = var_27["BASEURL"] . "/include/templates/" . var_28["defaulttemplate"] . "/images/torrent_flags/";
    var_461 = var_27["pic_base_url"];
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'ANNOUNCE'");
    $Result = mysqli_fetch_assoc($Q);
    $ANNOUNCE = unserialize($Result["content"]);
    $xbt_active = $ANNOUNCE["xbt_active"];
    var_462 = $xbt_active != "yes" && $torrents["free"] == "yes" || $xbt_active == "yes" && $torrents["download_multiplier"] == "0" ? "<img src=\"" . var_282 . "freedownload.gif\" class=\"inlineimg\" alt=\"" . $Language[15] . "\" title=\"" . $Language[15] . "\" />" : "";
    var_463 = $xbt_active != "yes" && $torrents["silver"] == "yes" || $xbt_active == "yes" && $torrents["download_multiplier"] == "0.5" ? "<img src=\"" . var_282 . "silverdownload.gif\" class=\"inlineimg\" alt=\"" . $Language[16] . "\" title=\"" . $Language[16] . "\" />" : "";
    var_464 = $xbt_active != "yes" && $torrents["doubleupload"] == "yes" || $xbt_active == "yes" && $torrents["upload_multiplier"] == "2" ? "<img src=\"" . var_282 . "x2.gif\" alt=\"" . $Language[24] . "\" title=\"" . $Language[24] . "\" class=\"inlineimg\" />" : "";
    var_465 = $torrents["isrequest"] == "yes" ? "<img src=\"" . var_282 . "isrequest.gif\" class=\"inlineimg\" alt=\"" . $Language[17] . "\" title=\"" . $Language[17] . "\" />" : "";
    var_466 = $torrents["isnuked"] == "yes" ? "<img src=\"" . var_282 . "isnuked.gif\" class=\"inlineimg\" alt=\"" . str_replace("{1}", $torrents["WhyNuked"], $Language[18]) . "\" title=\"" . str_replace("{1}", $torrents["WhyNuked"], $Language[18]) . "\" />" : "";
    var_467 = $torrents["sticky"] == "yes" ? "<img src=\"" . var_282 . "sticky.gif\" alt=\"" . $Language[19] . "\" title=\"" . $Language[19] . "\" />" : "";
    var_468 = $torrents["anonymous"] == "yes" ? "<img src=\"" . var_461 . "chatpost.gif\" alt=\"" . $Language[20] . "\" title=\"" . $Language[20] . "\" />" : "";
    var_469 = $torrents["banned"] == "yes" ? "<img src=\"" . var_461 . "disabled.gif\" alt=\"" . $Language[21] . "\" title=\"" . $Language[21] . "\" />" : "";
    var_470 = $torrents["ts_external"] == "yes" ? "<img src=\"" . var_282 . "external.gif\" class=\"inlineimg\"  border=\"0\" alt=\"" . $Language[22] . "\" title=\"" . $Language[22] . "\" /></a>" : "";
    var_471 = $torrents["visible"] == "yes" ? "" : "<img src=\"" . var_461 . "input_error.gif\" class=\"inlineimg\" alt=\"" . $Language[23] . "\" title=\"" . $Language[23] . "\" />";
    var_472 = $torrents["allowcomments"] == "no" ? "<img src=\"" . var_461 . "commentpos.gif\" alt=\"" . $Language[25] . "\" title=\"" . $Language[25] . "\" class=\"inlineimg\" />" : "";
    return var_471 . " " . var_462 . " " . var_463 . " " . var_465 . " " . var_466 . " " . var_467 . " " . var_470 . " " . var_468 . " " . var_469 . " " . var_464 . " " . var_472;
}
function function_164($selectname = "type", $selected = 0, $extra = "", $style = "specialboxn")
{
    var_417 = [];
    var_67 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE type = 's'");
    if (mysqli_num_rows(var_67)) {
        var_416 = [];
        while (var_473 = mysqli_fetch_assoc(var_67)) {
            var_416[] = var_473;
        }
        foreach (var_416 as var_419) {
            var_417[var_419["pid"]] = (isset(var_417[var_419["pid"]]) ? var_417[var_419["pid"]] : "") . "\r\n\t\t\t\t\t<option value=\"" . var_419["id"] . "\"" . (var_419["id"] == $selected ? " selected=\"selected\"" : "") . ">&nbsp;&nbsp;|-- " . var_419["name"] . "</option>\r\n\t\t\t\t\t";
        }
    }
    var_420 = "<select name=\"" . $selectname . "\" id=\"" . $style . "\">\r\n\t" . $extra;
    var_67 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE type = 'c'");
    if (0 < mysqli_num_rows(var_67)) {
        var_415 = [];
        while (var_473 = mysqli_fetch_assoc(var_67)) {
            var_415[] = var_473;
        }
        foreach (var_415 as var_421) {
            var_420 .= "\r\n\t\t\t\t<option value=\"" . var_421["id"] . "\"" . (var_421["id"] == $selected ? " selected=\"selected\"" : "") . " style=\"color:red;\">" . var_421["name"] . "</option>\r\n\t\t\t\t" . (isset(var_417[var_421["id"]]) ? var_417[var_421["id"]] : "") . "\r\n\t\t\t\t";
        }
    }
    var_420 .= "</select>";
    return var_420;
}
function function_151($id)
{
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'MAIN'");
    $Result = mysqli_fetch_assoc($Q);
    var_27 = unserialize($Result["content"]);
    var_427 = "../" . var_27["torrent_dir"];
    $id = intval($id);
    if (!$id) {
        return NULL;
    }
    $file = var_427 . "/" . $id . ".torrent";
    if (@file_exists($file)) {
        @unlink($file);
    }
    var_428 = ["gif", "jpg", "png"];
    foreach (var_428 as var_361) {
        if (@file_exists(var_427 . "/images/" . $id . "." . var_361)) {
            @unlink(var_427 . "/images/" . $id . "." . var_361);
        }
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t_link FROM torrents WHERE id=" . $id);
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        var_429 = $Result["t_link"];
        var_97 = "#https://www.imdb.com/title/(.*)/#U";
        preg_match(var_97, var_429, var_430);
        var_430 = var_430[1];
        foreach (var_428 as var_361) {
            if (@file_exists(var_427 . "/images/" . var_430 . "." . var_361)) {
                @unlink(var_427 . "/images/" . var_430 . "." . var_361);
            }
        }
        for ($i = 0; $i <= 10; $i++) {
            foreach (var_428 as var_361) {
                if (@file_exists(var_427 . "/images/" . var_430 . "_photo" . $i . "." . var_361)) {
                    @unlink(var_427 . "/images/" . var_430 . "_photo" . $i . "." . var_361);
                }
            }
        }
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM peers WHERE torrent = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM xbt_files_users WHERE fid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM comments WHERE torrent = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM bookmarks WHERE torrentid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM snatched WHERE torrentid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM torrents WHERE id=" . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_torrents_details WHERE tid=" . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_thanks WHERE tid=" . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_nfo  WHERE id = " . $id);
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_162($val, $text)
{
    global $Links;
    global $sort;
    global $order;
    var_474 = [];
    foreach ($Links as var_475 => var_476) {
        var_474[] = var_475 . "=" . var_476;
    }
    return "<a href=\"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_torrents&amp;sort=" . $val . "&amp;order=" . (strtolower($order) == "asc" ? "desc" : "asc") . "&amp;" . implode("&amp;", var_474) . "\">" . (strtolower($val) == strtolower($sort) ? "<strong>" . $text . "</strong>**" : $text) . "</a>";
}

?>