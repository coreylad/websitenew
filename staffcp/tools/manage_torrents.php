<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_torrents.lang");
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
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
$Result = mysqli_fetch_assoc($query);
$ANNOUNCE = unserialize($Result["content"]);
$xbt_active = $ANNOUNCE["xbt_active"];
if ($browsecategory) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT type FROM categories WHERE `id` = '" . $browsecategory . "'");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $cate_type = $Result["type"];
        if ($cate_type == "s") {
            $extraquery1 = " WHERE $category = " . $browsecategory;
            $extraquery2 = " WHERE t.$category = " . $browsecategory . " ";
            $extralink = "browsecategory=" . $browsecategory . "&amp;";
        } else {
            $array_cats = [];
            $array_cats[] = $browsecategory;
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM categories WHERE $pid = '" . $browsecategory . "'");
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
                $extraquery1 .= " AND $banned = 'yes'";
                $extraquery2 .= " AND t.$banned = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE $banned = 'yes'";
                $extraquery2 .= " WHERE t.$banned = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "deadonly":
            if ($extraquery1) {
                $extraquery1 .= " AND ($visible = 'no' OR ($seeders = 0 AND $leechers = 0))";
                $extraquery2 .= " AND (t.$visible = 'no' OR (t.$seeders = 0 AND t.$leechers = 0))";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE ($visible = 'no' OR ($seeders = 0 AND $leechers = 0))";
                $extraquery2 .= " WHERE (t.$visible = 'no' OR (t.$seeders = 0 AND t.$leechers = 0)) ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "internal":
            if ($extraquery1) {
                $extraquery1 .= " AND $ts_external = 'no'";
                $extraquery2 .= " AND t.$ts_external = 'no' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE $ts_external = 'no'";
                $extraquery2 .= " WHERE t.$ts_external = 'no' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "external":
            if ($extraquery1) {
                $extraquery1 .= " AND $ts_external = 'yes'";
                $extraquery2 .= " AND t.$ts_external = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE $ts_external = 'yes'";
                $extraquery2 .= " WHERE t.$ts_external = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "silver":
            if ($extraquery1) {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " AND $download_multiplier = '0.5'";
                    $extraquery2 .= " AND t.$download_multiplier = '0.5'";
                } else {
                    $extraquery1 .= " AND $silver = 'yes'";
                    $extraquery2 .= " AND t.$silver = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " WHERE $download_multiplier = '0.5'";
                    $extraquery2 .= " WHERE t.$download_multiplier = '0.5'";
                } else {
                    $extraquery1 .= " WHERE $silver = 'yes'";
                    $extraquery2 .= " WHERE t.$silver = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "free":
            if ($extraquery1) {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " AND $download_multiplier = '0'";
                    $extraquery2 .= " AND t.$download_multiplier = '0'";
                } else {
                    $extraquery1 .= " AND $free = 'yes'";
                    $extraquery2 .= " AND t.$free = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " WHERE $download_multiplier = '0'";
                    $extraquery2 .= " WHERE t.$download_multiplier = '0'";
                } else {
                    $extraquery1 .= " WHERE $free = 'yes'";
                    $extraquery2 .= " WHERE t.$free = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "doubleuploads":
            if ($extraquery1) {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " AND $upload_multiplier = '2'";
                    $extraquery2 .= " AND t.$upload_multiplier = '2'";
                } else {
                    $extraquery1 .= " AND $doubleupload = 'yes'";
                    $extraquery2 .= " AND t.$doubleupload = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                if ($xbt_active == "yes") {
                    $extraquery1 .= " WHERE $upload_multiplier = '2'";
                    $extraquery2 .= " WHERE t.$upload_multiplier = '2'";
                } else {
                    $extraquery1 .= " WHERE $doubleupload = 'yes'";
                    $extraquery2 .= " WHERE t.$doubleupload = 'yes' ";
                }
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "recommend":
            if ($extraquery1) {
                $extraquery1 .= " AND $sticky = 'yes'";
                $extraquery2 .= " AND t.$sticky = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE $sticky = 'yes'";
                $extraquery2 .= " WHERE t.$sticky = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "anonymous":
            if ($extraquery1) {
                $extraquery1 .= " AND $anonymous = 'yes'";
                $extraquery2 .= " AND t.$anonymous = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE $anonymous = 'yes'";
                $extraquery2 .= " WHERE t.$anonymous = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
        case "request":
            if ($extraquery1) {
                $extraquery1 .= " AND $isrequest = 'yes'";
                $extraquery2 .= " AND t.$isrequest = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            } else {
                $extraquery1 .= " WHERE $isrequest = 'yes'";
                $extraquery2 .= " WHERE t.$isrequest = 'yes' ";
                $extralink .= "searchtype=" . urlencode($searchtype) . "&amp;";
            }
            break;
    }
}
if ($Act == "update") {
    $torrentid = isset($_POST["torrentid"]) ? $_POST["torrentid"] : "";
    if (empty($actiontype)) {
        $Message = showAlertError($Language[43]);
    } else {
        if (!is_array($torrentid) || count($torrentid) < 1) {
            $Message = showAlertError($Language[44]);
        } else {
            $torrentids = implode(",", $torrentid);
            if ($torrentids) {
                switch ($actiontype) {
                    case "move":
                        if (0 < $category) {
                            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $category = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $category) . "' WHERE id IN (" . $torrentids . ")");
                        } else {
                            $Message = showAlertError($Language[45]);
                        }
                        break;
                    case "delete":
                        foreach ($torrentid as $id) {
                            function_151($id);
                        }
                        break;
                    case "sticky":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $sticky = IF($sticky = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "free":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $free = IF($free = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $download_multiplier = IF($download_multiplier = '0', '1', '0') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "silver":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $silver = IF($silver = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $download_multiplier = IF($download_multiplier = '0.5', '1', '0.5') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "doubleupload":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $doubleupload = IF($doubleupload = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $upload_multiplier = IF($upload_multiplier = '2', '1', '2') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "visible":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $visible = IF($visible = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "anonymous":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $anonymous = IF($anonymous = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "banned":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $banned = IF($banned = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "nuke":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $isnuked = IF($isnuked = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "openclose":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $allowcomments = IF($allowcomments = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    case "request":
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $isrequest = IF($isrequest = 'yes', 'no', 'yes') WHERE id IN (0," . $torrentids . ")");
                        break;
                    default:
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $flags = 0 WHERE $download_multiplier = 1 AND $upload_multiplier = 1 AND flags != 0");
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET $flags = 2 WHERE ($download_multiplier = 0 OR $download_multiplier = 0.5 OR $upload_multiplier = 2) AND flags != 2");
                        if (empty($Message)) {
                            logStaffAction(str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], $actiontype . " (" . $torrentids . ")"], $Language[39]));
                            if (isset($_GET["from_browse"])) {
                                redirectTo("../browse.php" . (isset($_GET["page"]) ? "?$page = " . intval($_GET["page"]) : ""));
                            }
                        }
                }
            } else {
                $Message = showAlertError("I can not implode torrent ids!");
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
list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=manage_torrents&amp;" . $extralink);
$catdropdown = processTorrent("category", $category, "<option $value = \"0\">" . $Language[6] . "</option>");
$catdropdown2 = processTorrent("browsecategory", $browsecategory, "<option $value = \"0\">" . $Language[6] . "</option>");
$searchtype_dropdown = "\r\n<select $name = \"searchtype\">\r\n<option $value = \"0\">" . $Language[41] . "</option>";
foreach (["deadonly" => $Language[46], "internal" => $Language[47], "external" => $Language[48], "silver" => $Language[49], "free" => $Language[50], "recommend" => $Language[51], "doubleuploads" => $Language[52], "anonymous" => $Language[53], "request" => $Language[54], "banned" => $Language[56]] as $valuename => $description) {
    $searchtype_dropdown .= "\r\n\t<option $value = \"" . $valuename . "\"" . ($searchtype == $valuename ? " $selected = \"selected\"" : "") . ">" . $description . "</option>";
}
$searchtype_dropdown .= "\r\n</select>";
$Found = "\r\n<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_torrents&amp;$act = search" . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"manage_torrents2\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[40] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t " . $Language[41] . " <input $type = \"text\" $name = \"searchword\" $value = \"" . htmlspecialchars($searchword) . "\" $size = \"40\" $dir = \"ltr\" $tabindex = \"1\" /> " . $catdropdown2 . " " . $searchtype_dropdown . " <input $type = \"submit\" $value = \"" . $Language[12] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.*, u.username, g.namestyle, c.name as catname FROM torrents t LEFT JOIN users u ON (t.$owner = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN categories c ON (t.$category = c.id)" . $extraquery2 . " ORDER BY " . $orderby . " " . $limit);
if (mysqli_num_rows($query)) {
    $Found .= "\r\n\t" . $pagertop . "\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_torrents&amp;$act = update" . (isset($_GET["page"]) ? "&amp;$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"manage_torrents\">\r\n\t<input $type = \"hidden\" $name = \"searchword\" $value = \"" . urlencode($searchword) . "\" />\r\n\t<input $type = \"hidden\" $name = \"browsecategory\" $value = \"" . urlencode($browsecategory) . "\" />\r\n\t<input $type = \"hidden\" $name = \"searchtype\" $value = \"" . urlencode($searchtype) . "\" />\r\n\t<input $type = \"hidden\" $name = \"sort\" $value = \"" . urlencode($sort) . "\" />\r\n\t<input $type = \"hidden\" $name = \"order\" $value = \"" . urlencode($order) . "\" />\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"9\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("name", $Language[5]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t" . $Language[14] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("category", $Language[6]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("size", $Language[7]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("added", $Language[8]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("owner", $Language[9]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("seeders", $Language[10]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . function_162("leechers", $Language[11]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('manage_torrents', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t";
    while ($Torrent = mysqli_fetch_assoc($query)) {
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"../details.php?$id = " . $Torrent["id"] . "\">" . htmlspecialchars($Torrent["name"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t" . function_163($Torrent) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Torrent["catname"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatBytes($Torrent["size"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . formatTimestamp($Torrent["added"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $Torrent["username"] . "\">" . applyUsernameStyle($Torrent["username"], $Torrent["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . number_format($Torrent["seeders"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . number_format($Torrent["leechers"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\"><input $type = \"checkbox\" $name = \"torrentid[]\" $value = \"" . $Torrent["id"] . "\" $checkme = \"group\" " . (isset($_POST["torrentid"]) && in_array($Torrent["id"], $_POST["torrentid"]) ? " $checked = \"checked\"" : "") . "/></td>\r\n\t\t</tr>\r\n\t\t";
    }
    $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"3\" $align = \"right\" class=\"tcat2\">\r\n\t\t\t\t<select $name = \"actiontype\" $onchange = \"check_it(this)\">\r\n\t\t\t\t\t<option $value = \"0\"" . ($actiontype == "0" ? " $selected = \"selected\"" : "") . ">" . $Language[26] . "</option>\r\n\t\t\t\t\t<option $value = \"move\"" . ($actiontype == "move" ? " $selected = \"selected\"" : "") . ">" . $Language[27] . "</option>\r\n\t\t\t\t\t<option $value = \"delete\"" . ($actiontype == "delete" ? " $selected = \"selected\"" : "") . ">" . $Language[28] . "</option>\r\n\t\t\t\t\t<option $value = \"sticky\"" . ($actiontype == "sticky" ? " $selected = \"selected\"" : "") . ">" . $Language[29] . "</option>\r\n\t\t\t\t\t<option $value = \"free\"" . ($actiontype == "free" ? " $selected = \"selected\"" : "") . ">" . $Language[30] . "</option>\r\n\t\t\t\t\t<option $value = \"silver\"" . ($actiontype == "silver" ? " $selected = \"selected\"" : "") . ">" . $Language[31] . "</option>\r\n\t\t\t\t\t<option $value = \"doubleupload\"" . ($actiontype == "doubleupload" ? " $selected = \"selected\"" : "") . ">" . $Language[36] . "</option>\r\n\t\t\t\t\t<option $value = \"visible\"" . ($actiontype == "visible" ? " $selected = \"selected\"" : "") . ">" . $Language[32] . "</option>\r\n\t\t\t\t\t<option $value = \"anonymous\"" . ($actiontype == "anonymous" ? " $selected = \"selected\"" : "") . ">" . $Language[33] . "</option>\r\n\t\t\t\t\t<option $value = \"banned\"" . ($actiontype == "banned" ? " $selected = \"selected\"" : "") . ">" . $Language[34] . "</option>\r\n\t\t\t\t\t<option $value = \"nuke\"" . ($actiontype == "nuke" ? " $selected = \"selected\"" : "") . ">" . $Language[35] . "</option>\t\t\t\t\t\r\n\t\t\t\t\t<option $value = \"openclose\"" . ($actiontype == "openclose" ? " $selected = \"selected\"" : "") . ">" . $Language[37] . "</option>\r\n\t\t\t\t\t<option $value = \"request\"" . ($actiontype == "request" ? " $selected = \"selected\"" : "") . ">" . $Language[55] . "</option>\r\n\t\t\t\t</select>\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[38] . "\" />\r\n\t\t\t</td>\r\n\t\t\t<td $colspan = \"6\" $align = \"left\" class=\"tcat2\">\r\n\t\t\t\t<div $id = \"movetorrent\" $style = \"display: none;\">\r\n\t\t\t\t\t" . $catdropdown . "\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t" . $pagertop;
} else {
    $Message = showAlertError($Language[4]);
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction select_deselectAll(formname,elm,group)\r\n\t{\r\n\t\tvar $frm = document.forms[formname];\r\n\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t{\r\n\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value == group)\r\n\t\t\t{\r\n\t\t\t\tif(frm.elements[i].$checked == false)\r\n\t\t\t\t{\r\n\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t}\r\n\tfunction check_it(wHAT)\r\n\t{\r\n\t\tif (wHAT.$value == \"move\")\r\n\t\t{\r\n\t\t\tTSGetID(\"movetorrent\").style.$display = \"block\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tTSGetID(\"movetorrent\").style.$display = \"none\";\r\n\t\t}\r\n\t}\r\n</script>\r\n\r\n" . $Message . "\r\n" . $Found;
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function formatBytes($bytes = 0)
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
function formatTimestamp($timestamp = "")
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, $timestamp);
}
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function validatePerPage($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $totalPages = ceil($numresults / $perpage);
    if ($totalPages == 0) {
        $totalPages = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($totalPages < $page) {
            $page = $totalPages;
        }
    }
}
function calculatePagination($pagenumber, $perpage, $total)
{
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    $paginationFirstItem++;
    return ["first" => number_format($paginationFirstItem), "last" => number_format($paginationLastItem)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $queryResult = @ceil($results / $perpage);
    } else {
        $queryResult = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $limitOffset = ($pagenumber - 1) * $perpage;
    $paginationOffset = $pagenumber * $perpage;
    if ($results < $paginationOffset) {
        $paginationOffset = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    $paginationLinks = $prevPage = $nextPage = $pageLinks = $paginationHtml = "";
    $currentPage = 0;
    if ($results <= $perpage) {
        $paginationHtml["pagenav"] = false;
        return ["", "LIMIT " . $limitOffset . ", " . $perpage];
    }
    $paginationHtml["pagenav"] = true;
    $total = number_format($results);
    $paginationHtml["last"] = false;
    $paginationHtml["first"] = $paginationHtml["last"];
    $paginationHtml["next"] = $paginationHtml["first"];
    $paginationHtml["prev"] = $paginationHtml["next"];
    if (1 < $pagenumber) {
        $previousPage = $pagenumber - 1;
        $previousPageInfo = calculatePagination($previousPage, $perpage, $results);
        $paginationHtml["prev"] = true;
    }
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPageNumber, $perpage, $results);
        $paginationHtml["next"] = true;
    }
    $pageRangeThreshold = "3";
    if (!isset($paginationSkipLinksArray) || !is_array($paginationSkipLinksArray)) {
        $paginationOptions = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $paginationOptions, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $previousPageQuery = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $previousPageQuery . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($pageRangeThreshold <= abs($currentPage - $pagenumber) && $pageRangeThreshold != 0) {
        if ($currentPage == 1) {
            $firstPageInfo = calculatePagination(1, $perpage, $results);
            $paginationHtml["first"] = true;
        }
        if ($currentPage == $queryResult) {
            $lastPageInfo = calculatePagination($queryResult, $perpage, $results);
            $paginationHtml["last"] = true;
        }
        if (in_array(abs($currentPage - $pagenumber), $paginationSkipLinksArray) && $currentPage != 1 && $currentPage != $queryResult) {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $pageOffsetDisplay = $currentPage - $pagenumber;
            if (0 < $pageOffsetDisplay) {
                $pageOffsetDisplay = "+" . $pageOffsetDisplay;
            }
            $paginationLinks .= "<li><a class=\"smalltext\" $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        }
    }
}
function function_163($torrents)
{
    global $Language;
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $Result = mysqli_fetch_assoc($query);
    $formAction = unserialize($Result["content"]);
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'THEME'");
    $Result = mysqli_fetch_assoc($query);
    $formMethod = unserialize($Result["content"]);
    $formMethod2 = $configData["BASEURL"] . "/include/templates/" . $formMethod["defaulttemplate"] . "/images/torrent_flags/";
    $torrentId = $configData["pic_base_url"];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
    $Result = mysqli_fetch_assoc($query);
    $ANNOUNCE = unserialize($Result["content"]);
    $xbt_active = $ANNOUNCE["xbt_active"];
    $torrentName = $xbt_active != "yes" && $torrents["free"] == "yes" || $xbt_active == "yes" && $torrents["download_multiplier"] == "0" ? "<img $src = \"" . $formMethod2 . "freedownload.gif\" class=\"inlineimg\" $alt = \"" . $Language[15] . "\" $title = \"" . $Language[15] . "\" />" : "";
    $torrentHash = $xbt_active != "yes" && $torrents["silver"] == "yes" || $xbt_active == "yes" && $torrents["download_multiplier"] == "0.5" ? "<img $src = \"" . $formMethod2 . "silverdownload.gif\" class=\"inlineimg\" $alt = \"" . $Language[16] . "\" $title = \"" . $Language[16] . "\" />" : "";
    $torrentSize = $xbt_active != "yes" && $torrents["doubleupload"] == "yes" || $xbt_active == "yes" && $torrents["upload_multiplier"] == "2" ? "<img $src = \"" . $formMethod2 . "x2.gif\" $alt = \"" . $Language[24] . "\" $title = \"" . $Language[24] . "\" class=\"inlineimg\" />" : "";
    $torrentSeeders = $torrents["isrequest"] == "yes" ? "<img $src = \"" . $formMethod2 . "isrequest.gif\" class=\"inlineimg\" $alt = \"" . $Language[17] . "\" $title = \"" . $Language[17] . "\" />" : "";
    $torrentLeechers = $torrents["isnuked"] == "yes" ? "<img $src = \"" . $formMethod2 . "isnuked.gif\" class=\"inlineimg\" $alt = \"" . str_replace("{1}", $torrents["WhyNuked"], $Language[18]) . "\" $title = \"" . str_replace("{1}", $torrents["WhyNuked"], $Language[18]) . "\" />" : "";
    $torrentComplete = $torrents["sticky"] == "yes" ? "<img $src = \"" . $formMethod2 . "sticky.gif\" $alt = \"" . $Language[19] . "\" $title = \"" . $Language[19] . "\" />" : "";
    $torrentCategory = $torrents["anonymous"] == "yes" ? "<img $src = \"" . $torrentId . "chatpost.gif\" $alt = \"" . $Language[20] . "\" $title = \"" . $Language[20] . "\" />" : "";
    $torrentOwner = $torrents["banned"] == "yes" ? "<img $src = \"" . $torrentId . "disabled.gif\" $alt = \"" . $Language[21] . "\" $title = \"" . $Language[21] . "\" />" : "";
    $torrentUploaded = $torrents["ts_external"] == "yes" ? "<img $src = \"" . $formMethod2 . "external.gif\" class=\"inlineimg\"  $border = \"0\" $alt = \"" . $Language[22] . "\" $title = \"" . $Language[22] . "\" /></a>" : "";
    $torrentStatus = $torrents["visible"] == "yes" ? "" : "<img $src = \"" . $torrentId . "input_error.gif\" class=\"inlineimg\" $alt = \"" . $Language[23] . "\" $title = \"" . $Language[23] . "\" />";
    $torrentVisible = $torrents["allowcomments"] == "no" ? "<img $src = \"" . $torrentId . "commentpos.gif\" $alt = \"" . $Language[25] . "\" $title = \"" . $Language[25] . "\" class=\"inlineimg\" />" : "";
    return $torrentStatus . " " . $torrentName . " " . $torrentHash . " " . $torrentSeeders . " " . $torrentLeechers . " " . $torrentComplete . " " . $torrentUploaded . " " . $torrentCategory . " " . $torrentOwner . " " . $torrentSize . " " . $torrentVisible;
}
function function_164($selectname = "type", $selected = 0, $extra = "", $style = "specialboxn")
{
    $categoryName = [];
    $errorMessage = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE $type = 's'");
    if (mysqli_num_rows($errorMessage)) {
        $categoryId = [];
        while ($torrentImage = mysqli_fetch_assoc($errorMessage)) {
            $categoryId[] = $torrentImage;
        }
        foreach ($categoryId as $categorySort) {
            $categoryName[$categorySort["pid"]] = (isset($categoryName[$categorySort["pid"]]) ? $categoryName[$categorySort["pid"]] : "") . "\r\n\t\t\t\t\t<option $value = \"" . $categorySort["id"] . "\"" . ($categorySort["id"] == $selected ? " $selected = \"selected\"" : "") . ">&nbsp;&nbsp;|-- " . $categorySort["name"] . "</option>\r\n\t\t\t\t\t";
        }
    }
    $categoryDesc = "<select $name = \"" . $selectname . "\" $id = \"" . $style . "\">\r\n\t" . $extra;
    $errorMessage = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE $type = 'c'");
    if (0 < mysqli_num_rows($errorMessage)) {
        $categoryConfig = [];
        while ($torrentImage = mysqli_fetch_assoc($errorMessage)) {
            $categoryConfig[] = $torrentImage;
        }
        foreach ($categoryConfig as $categoryHtml) {
            $categoryDesc .= "\r\n\t\t\t\t<option $value = \"" . $categoryHtml["id"] . "\"" . ($categoryHtml["id"] == $selected ? " $selected = \"selected\"" : "") . " $style = \"color:red;\">" . $categoryHtml["name"] . "</option>\r\n\t\t\t\t" . (isset($categoryName[$categoryHtml["id"]]) ? $categoryName[$categoryHtml["id"]] : "") . "\r\n\t\t\t\t";
        }
    }
    $categoryDesc .= "</select>";
    return $categoryDesc;
}
function function_151($id)
{
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $Result = mysqli_fetch_assoc($query);
    $formAction = unserialize($Result["content"]);
    $fileHandle = "../" . $configData["torrent_dir"];
    $id = intval($id);
    if (!$id) {
        return NULL;
    }
    $file = $fileHandle . "/" . $id . ".torrent";
    if (@file_exists($file)) {
        @unlink($file);
    }
    $fileContent = ["gif", "jpg", "png"];
    foreach ($fileContent as $smileyFileExt) {
        if (@file_exists($fileHandle . "/images/" . $id . "." . $smileyFileExt)) {
            @unlink($fileHandle . "/images/" . $id . "." . $smileyFileExt);
        }
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t_link FROM torrents WHERE `id` = " . $id);
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $fileName = $Result["t_link"];
        $resultSet = "#https://www.imdb.com/title/(.*)/#U";
        preg_match($resultSet, $fileName, $fileSize);
        $fileSize = $fileSize[1];
        foreach ($fileContent as $smileyFileExt) {
            if (@file_exists($fileHandle . "/images/" . $fileSize . "." . $smileyFileExt)) {
                @unlink($fileHandle . "/images/" . $fileSize . "." . $smileyFileExt);
            }
        }
        for ($i = 0; $i <= 10; $i++) {
            foreach ($fileContent as $smileyFileExt) {
                if (@file_exists($fileHandle . "/images/" . $fileSize . "_photo" . $i . "." . $smileyFileExt)) {
                    @unlink($fileHandle . "/images/" . $fileSize . "_photo" . $i . "." . $smileyFileExt);
                }
            }
        }
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM peers WHERE $torrent = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM xbt_files_users WHERE $fid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM comments WHERE $torrent = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM bookmarks WHERE `torrentid` = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM snatched WHERE `torrentid` = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM torrents WHERE `id` = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_torrents_details WHERE $tid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_thanks WHERE $tid = " . $id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_nfo  WHERE `id` = " . $id);
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_162($val, $text)
{
    global $Links;
    global $sort;
    global $order;
    $torrentDescription = [];
    foreach ($Links as $torrentNfo => $torrentData) {
        $torrentDescription[] = $torrentNfo . "=" . $torrentData;
    }
    return "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?do=manage_torrents&amp;$sort = " . $val . "&amp;$order = " . (strtolower($order) == "asc" ? "desc" : "asc") . "&amp;" . implode("&amp;", $torrentDescription) . "\">" . (strtolower($val) == strtolower($sort) ? "<strong>" . $text . "</strong>**" : $text) . "</a>";
}

?>