<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/all_agents.lang");
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'ANNOUNCE'");
$Result = mysqli_fetch_assoc($Q);
$ANNOUNCE = unserialize($Result["content"]);
$Message = "";
if ($ANNOUNCE["xbt_active"] == "yes") {
    echo "\r\n\t" . function_76($Language[1]);
    $STOP = true;
}
if (!isset($STOP)) {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && isset($_POST["allowed_clients"]) && !empty($_POST["allowed_clients"])) {
        $ANNOUNCE["allowed_clients"] = implode(",", $_POST["allowed_clients"]);
        mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_config` VALUES ('ANNOUNCE', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], serialize($ANNOUNCE)) . "')");
    }
    $Found = "";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT agent, peer_id FROM peers GROUP BY agent DESC");
    if (0 < mysqli_num_rows($Query)) {
        $allowed_clients = explode(",", $ANNOUNCE["allowed_clients"]);
        $DONE = [];
        while ($R = mysqli_fetch_assoc($Query)) {
            $Peer_ID = substr(htmlspecialchars($R["peer_id"]), 0, 8);
            if (!in_array($Peer_ID, $DONE)) {
                $DONE[] = $Peer_ID;
                $Found .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $Peer_ID . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($R["agent"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t\t\t<input type=\"checkbox\" name=\"allowed_clients[]\" value=\"" . $Peer_ID . "\" checkme=\"group\" " . (in_array($Peer_ID, $allowed_clients) ? " checked=\"checked\"" : "") . "/>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
        }
    } else {
        echo "\r\n\t\t\r\n\t\t" . function_76($Language[3]);
    }
    echo "\r\n\t<script type=\"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar frm=document.forms[formname];\r\n\t\t\tfor(i=0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].checked=elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].value == group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].checked == false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form action=\"" . $_SERVER["SCRIPT_NAME"] . "?do=all_agents\" method=\"post\" name=\"all_agents\">\r\n\t" . function_81($Language[8]) . "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"3\">" . $Language[2] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[4] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[5] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t\t<input type=\"checkbox\" checkall=\"group\" onclick=\"javascript: return select_deselectAll ('all_agents', this, 'group');\">\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" align=\"right\" colspan=\"3\">\r\n\t\t\t\t<input type=\"submit\" value=\"" . $Language[6] . "\" /> <input type=\"reset\" value=\"" . $Language[7] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
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
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>