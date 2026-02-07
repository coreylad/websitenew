<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/donor_list.lang");
$Message = "";
$ids = [];
$amount = "0";
$type = "0";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $userids = isset($_POST["ids"]) ? $_POST["ids"] : "";
    $amount = isset($_POST["amount"]) ? intval($_POST["amount"]) : "";
    $type = isset($_POST["type"]) ? $_POST["type"] : "";
    if ($userids && $userids[0] != "" && $amount && $type) {
        if (is_array($userids) && 0 < count($userids) && 0 < $amount) {
            $SysMsg = str_replace(["{1}", "{2}", "{3}"], [$amount, $type, $_SESSION["ADMIN_USERNAME"]], $Language[22]);
            $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
            if ($type == "donoruntil") {
                $donorlengthadd = $amount * 7;
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment), donoruntil = IF(donoruntil='0000-00-00 00:00:00', ADDDATE(NOW(), INTERVAL " . $donorlengthadd . " DAY ), ADDDATE( donoruntil, INTERVAL " . $donorlengthadd . " DAY)) WHERE id IN (0," . implode(",", $userids) . ")");
            } else {
                if ($type == "seedbonus") {
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment), seedbonus = seedbonus + " . $amount . " WHERE id IN (0," . implode(",", $userids) . ")");
                } else {
                    if ($type == "invites") {
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment), invites = invites + " . $amount . " WHERE id IN (0," . implode(",", $userids) . ")");
                    }
                }
            }
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}", "{3}"], [$amount, $type, $_SESSION["ADMIN_USERNAME"]], $Language[21]);
                function_79($Message);
                $Message = function_81($Message);
            }
        }
    } else {
        $Message = function_76($Language[20]);
    }
}
$orderby1 = "ASC";
$type = "donoruntil";
if (isset($_GET["orderby"])) {
    $orderby1 = $_GET["orderby"] == "ASC" ? "ASC" : "DESC";
    $type = $_GET["type"] == "donoruntil" ? "donoruntil" : "donated";
}
$orderby = "" . $type . " " . $orderby1 . " ";
$results = mysqli_num_rows(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM users where donor = 'yes' AND enabled = 'yes'"));
list($pagertop, $limit) = function_82(25, $results, $_SERVER["SCRIPT_NAME"] . "?do=donor_list&amp;orderby=" . $orderby1 . "&amp;type=" . $type . "&amp;");
$sql = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, username, last_access, email, ip, uploaded, downloaded, invites, seedbonus, donoruntil, donated, total_donated, g.title, g.namestyle FROM users LEFT JOIN usergroups g ON (users.usergroup=g.gid) WHERE donor = 'yes' AND enabled = 'yes' ORDER BY " . $orderby . " " . $limit);
if (mysqli_num_rows($sql) == 0) {
    echo "\r\n\t\r\n\t" . function_76($Language[1]);
} else {
    $Found = "";
    while ($User = mysqli_fetch_assoc($sql)) {
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . $User["username"] . "\">" . function_83($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $User["title"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($User["email"]) . "\r\n\t\t\t</td>\t\t\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . function_84($User["last_access"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . var_238($User["uploaded"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . var_238($User["downloaded"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $User["seedbonus"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . function_84($User["donoruntil"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $User["donated"] . "/" . $User["total_donated"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t<input type=\"checkbox\" name=\"ids[]\" value=\"" . $User["id"] . "\" checkme=\"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    echo "\r\n\t<script type=\"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar frm=document.forms[formname];\r\n\t\t\tfor(i=0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].checked=elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form action=\"" . $_SERVER["SCRIPT_NAME"] . "?do=donor_list" . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "") . "\" method=\"post\" name=\"donor_list\">\r\n\t\r\n\t" . $Message . "\r\n\t" . $pagertop . "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"11\"><b>" . str_replace("{1}", number_format($results), $Language[3]) . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[10] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[11] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><a href=\"index.php?do=donor_list&amp;orderby=" . ($orderby1 == "ASC" ? "DESC" : "ASC") . "&amp;type=donoruntil\"><b>" . $Language[12] . "</b></a></td>\r\n\t\t\t<td class=\"alt2\"><a href=\"index.php?do=donor_list&amp;orderby=" . ($orderby1 == "ASC" ? "DESC" : "ASC") . "&amp;type=donated\"><b>" . $Language[13] . "</b></a></td>\r\n\t\t\t<td class=\"alt2\" align=\"center\"><input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll ('donor_list', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" align=\"right\" colspan=\"11\">\r\n\t\t\t\t" . $Language[14] . " <input type=\"text\" name=\"amount\" size=\"10\" value=\"" . $amount . "\" /> \r\n\t\t\t\t<select name=\"type\">\r\n\t\t\t\t\t<option value=\"0\"" . ($type == "0" ? " selected=\"selected\"" : "") . ">" . $Language[15] . "</option>\r\n\t\t\t\t\t<option value=\"donoruntil\"" . ($type == "donoruntil" ? " selected=\"selected\"" : "") . ">" . $Language[16] . "</option>\r\n\t\t\t\t\t<option value=\"seedbonus\"" . ($type == "seedbonus" ? " selected=\"selected\"" : "") . ">" . $Language[17] . "</option>\r\n\t\t\t\t\t<option value=\"invites\"" . ($type == "invites" ? " selected=\"selected\"" : "") . ">" . $Language[18] . "</option>\r\n\t\t\t\t</select> \r\n\t\t\t\t<input type=\"submit\" value=\"" . $Language[19] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t" . $pagertop;
}
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
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
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
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
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
        var_245 = "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\t\t\t\t\r\n\t\t\t<td style=\"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . var_242 . "</li>\r\n\t\t\t\t\t\t" . (var_251["first"] ? "<li><a class=\"smalltext\" href=\"" . $address . "\" title=\"First Page - Show Results " . var_260["first"] . " to " . var_260["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . (var_251["prev"] ? "<li><a class=\"smalltext\" href=\"" . $address . var_259 . "\" title=\"Previous Page - Show Results " . var_253["first"] . " to " . var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . var_245 . "\r\n\t\t\t\t\t\t" . (var_251["next"] ? "<li><a class=\"smalltext\" href=\"" . $address . "page=" . var_254 . "\" title=\"Next Page - Show Results " . var_255["first"] . " to " . var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . (var_251["last"] ? "<li><a class=\"smalltext\" href=\"" . $address . "page=" . var_242 . "\" title=\"Last Page - Show Results " . var_261["first"] . " to " . var_261["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\t\t\t\t\t\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t</td>\t\t\t\r\n\t\t</tr>\r\n\t</table>";
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
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>