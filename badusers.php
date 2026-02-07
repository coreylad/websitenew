<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "badusers.php");
require "./global.php";
if (!isset($CURUSER) || isset($CURUSER["id"]) && $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("badusers");
define("BU_VERSION", "0.8 by xam");
$act = isset($_POST["act"]) ? htmlspecialchars($_POST["act"]) : (isset($_GET["act"]) ? htmlspecialchars($_GET["act"]) : "showlist");
$do = isset($_POST["do"]) ? htmlspecialchars($_POST["do"]) : (isset($_GET["do"]) ? htmlspecialchars($_GET["do"]) : "");
if ($act == "delete") {
    if (!$is_mod) {
        print_no_permission(true);
        exit;
    }
    $id = intval($_GET["id"]);
    int_check($id);
    sql_query("DELETE FROM badusers WHERE id = " . sqlesc($id));
    write_log("BAD USER (id: " . $id . ") deleted by " . htmlspecialchars_uni($CURUSER["username"]));
    redirect("badusers.php", $lang->badusers["deleted"]);
} else {
    if ($act == "edit") {
        if (!$is_mod) {
            print_no_permission(true);
            exit;
        }
        if ($do == "save") {
            $id = intval($_POST["id"]);
            int_check($id);
            $username = isset($_POST["username"]) ? trim($_POST["username"]) : (isset($_GET["username"]) ? trim($_GET["username"]) : "");
            $email = isset($_POST["email"]) ? trim($_POST["email"]) : (isset($_GET["email"]) ? trim($_GET["email"]) : "");
            $ipaddress = isset($_POST["ipaddress"]) ? trim($_POST["ipaddress"]) : (isset($_GET["ipaddress"]) ? trim($_GET["ipaddress"]) : "");
            $comment = isset($_POST["comment"]) ? trim($_POST["comment"]) : (isset($_GET["comment"]) ? trim($_GET["comment"]) : "");
            if (empty($username) || empty($email) || empty($comment)) {
                stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
                exit;
            }
            sql_query("UPDATE badusers SET username = " . sqlesc($username) . ", email = " . sqlesc($email) . ", ipaddress = " . sqlesc($ipaddress) . ", comment = " . sqlesc($comment) . " WHERE id = " . sqlesc($id));
            write_log("BAD USER (id: " . $id . ") edited by " . htmlspecialchars_uni($CURUSER["username"]));
            redirect("badusers.php", $lang->badusers["edited"]);
        } else {
            $id = intval($_GET["id"]);
            int_check($id);
            $query = sql_query("SELECT username,email,ipaddress,comment FROM badusers WHERE id = " . sqlesc($id));
            if (mysqli_num_rows($query) == 0) {
                stderr($lang->global["error"], $lang->global["nothingfound"]);
                exit;
            }
            $baduser = mysqli_fetch_assoc($query);
            stdhead($lang->badusers["edith"]);
            $str = "\r\n\t\t<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t<input type=\"hidden\" name=\"act\" value=\"edit\">\r\n\t\t<input type=\"hidden\" name=\"do\" value=\"save\">\r\n\t\t<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">\r\n\t\t<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n\t\t\t<tbody>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<table class=\"tback\" border=\"0\" cellpadding=\"6\" cellspacing=\"0\" width=\"100%\">\r\n\t\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"thead\" colspan=\"4\" align=\"center\">" . $lang->badusers["edith"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\t\t\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"rowhead\">" . $lang->badusers["username"] . "</td><td><input type=\"text\" name=\"username\" id=\"specialboxn\" value=\"" . htmlspecialchars_uni($baduser["username"]) . "\" /></td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"rowhead\">" . $lang->badusers["email"] . "</td><td><input type=\"text\" name=\"email\" id=\"specialboxn\" value=\"" . htmlspecialchars_uni($baduser["email"]) . "\" /></td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"rowhead\">" . $lang->badusers["ipaddress"] . "</td><td><input type=\"text\" name=\"ipaddress\" id=\"specialboxn\" value=\"" . htmlspecialchars_uni($baduser["ipaddress"]) . "\" /></td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"rowhead\">" . $lang->badusers["comment"] . "</td><td><input type=\"text\" name=\"comment\" id=\"specialboxn\" value=\"" . htmlspecialchars_uni($baduser["comment"]) . "\" /> <input type=\"submit\" value=\"" . $lang->global["buttonsave"] . "\" class=\"button\" /></td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
            echo $str;
            stdfoot();
        }
    } else {
        if ($act == "insert") {
            if ($usergroups["canbaduser"] != "yes") {
                print_no_permission(true);
                exit;
            }
            if ($do == "save") {
                $username = isset($_POST["username"]) ? trim($_POST["username"]) : (isset($_GET["username"]) ? urldecode($_GET["username"]) : "");
                $email = isset($_POST["email"]) ? trim($_POST["email"]) : (isset($_GET["email"]) ? urldecode($_GET["email"]) : "");
                $ipaddress = isset($_POST["ipaddress"]) ? trim($_POST["ipaddress"]) : (isset($_GET["ipaddress"]) ? urldecode($_GET["ipaddress"]) : "");
                $comment = isset($_POST["comment"]) ? trim($_POST["comment"]) : (isset($_GET["comment"]) ? urldecode($_GET["comment"]) : "");
                $userid = isset($_POST["userid"]) ? intval($_POST["userid"]) : (isset($_GET["userid"]) ? intval($_GET["userid"]) : 0);
                $addedby = $CURUSER["username"] . ":" . intval($CURUSER["id"]);
                if (empty($username) || empty($email) || empty($comment)) {
                    stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
                    exit;
                }
                $query = sql_query("SELECT * FROM badusers WHERE username = " . sqlesc($username) . " LIMIT 0 , 1");
                if (mysqli_num_rows($query) != 0) {
                    stderr($lang->global["error"], $lang->badusers["alreadyexists"], false);
                }
                sql_query("INSERT INTO badusers (userid,username,email,ipaddress,comment,added,addedby) VALUES (" . sqlesc($userid) . ", " . sqlesc($username) . ", " . sqlesc($email) . ", " . sqlesc($ipaddress) . ", " . sqlesc($comment) . ", " . sqlesc(get_date_time()) . ", " . sqlesc($addedby) . ")") || sqlerr(__FILE__, 144);
                redirect("badusers.php", $lang->badusers["saved"]);
            } else {
                stdhead($lang->badusers["insert"]);
                $str = "\r\n\t\t<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t\t<input type=\"hidden\" name=\"act\" value=\"insert\">\r\n\t\t<input type=\"hidden\" name=\"do\" value=\"save\">\r\n\t\t<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n\t\t\t<tbody>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<table class=\"tback\" border=\"0\" cellpadding=\"6\" cellspacing=\"0\" width=\"100%\">\r\n\t\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"thead\" colspan=\"4\" align=\"center\">" . $lang->badusers["insert"] . "</td>\r\n\t\t\t\t\t\t\t\t</tr>\t\t\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"rowhead\">" . $lang->badusers["username"] . "</td><td><input type=\"text\" name=\"username\" id=\"specialboxn\" /></td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"rowhead\">" . $lang->badusers["email"] . "</td><td><input type=\"text\" name=\"email\" id=\"specialboxn\" /></td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"rowhead\">" . $lang->badusers["ipaddress"] . "</td><td><input type=\"text\" name=\"ipaddress\" id=\"specialboxn\" /></td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"rowhead\">" . $lang->badusers["comment"] . "</td><td><input type=\"text\" name=\"comment\" id=\"specialboxn\" /> <input type=\"submit\" value=\"" . $lang->global["buttonsave"] . "\" class=\"button\" /></td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
                echo $str;
                stdfoot();
            }
        } else {
            if ($act == "showlist") {
                $countrows = number_format(TSRowCount("id", "badusers"));
                $page = isset($_GET["page"]) ? intval($_GET["page"]) : 0;
                list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $countrows, $_SERVER["SCRIPT_NAME"] . "?act=" . $act . "&");
                $query = sql_query("SELECT * FROM badusers ORDER by added DESC " . $limit);
                stdhead($lang->badusers["head"]);
                if ($usergroups["canbaduser"] == "yes") {
                    echo "\r\n\t\t<p align=\"right\">\r\n\t\t\t<input type=\"button\" value=\"" . $lang->badusers["insert"] . "\" onClick=\"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?act=insert')\" class=\"hoptobutton\" />\r\n\t\t</p>";
                }
                $colspan = $is_mod ? "6" : "4";
                $str = "\r\n\t<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<table class=\"tback\" border=\"0\" cellpadding=\"6\" cellspacing=\"0\" width=\"100%\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td class=\"thead\" colspan=\"" . $colspan . "\" align=\"center\">" . $lang->badusers["head"] . "</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t<tr class=\"subheader\">\r\n\t\t\t\t\t\t\t\t<td align=\"left\" width=\"15%\">" . $lang->badusers["username"] . "</td>\r\n\t\t\t\t\t\t\t\t<td align=\"left\" width=\"15%\">" . $lang->badusers["email"] . "</td>\r\n\t\t\t\t\t\t\t\t<td align=\"center\" width=\"15%\">" . $lang->badusers["ipaddress"] . "</td>\r\n\t\t\t\t\t\t\t\t<td align=\"left\" width=\"35%\">" . $lang->badusers["comment"] . "</td>\r\n\t\t\t\t\t\t\t\t" . ($is_mod ? "\r\n\t\t\t\t\t\t\t\t<td align=\"center\" width=\"10%\">" . $lang->badusers["addedby"] . "</td>\r\n\t\t\t\t\t\t\t\t<td align=\"center\" width=\"10%\">" . $lang->badusers["action"] . "</td>" : "") . "\r\n\t\t\t\t\t\t\t</tr>";
                if (mysqli_num_rows($query) == 0) {
                    $str .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td colspan=\"" . $colspan . "\">" . $lang->global["nothingfound"] . "</td>\r\n\t\t\t</tr>";
                } else {
                    while ($baduser = mysqli_fetch_assoc($query)) {
                        $ipaddress = !$is_mod ? mask($baduser["ipaddress"], -2) : $baduser["ipaddress"];
                        $addedby = explode(":", $baduser["addedby"]);
                        $str .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td align=\"left\" width=\"15%\">" . htmlspecialchars_uni($baduser["username"]) . "</td>\r\n\t\t\t\t<td align=\"left\" width=\"15%\">" . htmlspecialchars_uni($baduser["email"]) . "</td>\r\n\t\t\t\t<td align=\"center\" width=\"15%\">" . htmlspecialchars_uni($ipaddress) . "</td>\r\n\t\t\t\t<td align=\"left\" width=\"35%\"><font color=\"red\">" . htmlspecialchars_uni($baduser["comment"]) . "</font></td>\r\n\t\t\t\t" . ($is_mod ? "\r\n\t\t\t\t<td align=\"center\" width=\"10%\"><a href=\"" . ts_seo($addedby[1], $addedby[0]) . "\">" . $addedby[0] . "</a></td>\r\n\t\t\t\t<td align=\"center\" width=\"10%\"><a href=\"" . $_SERVER["SCRIPT_NAME"] . "?act=edit&id=" . intval($baduser["id"]) . "\"><img src=\"" . $pic_base_url . "edit.gif\" alt=\"" . $lang->badusers["edit"] . "\" title=\"" . $lang->badusers["edit"] . "\" border=\"0\"></a>&nbsp;&nbsp;<a href=\"" . $_SERVER["SCRIPT_NAME"] . "?act=delete&id=" . intval($baduser["id"]) . "\"><img src=\"" . $pic_base_url . "delete.gif\" alt=\"" . $lang->badusers["delete"] . "\" title=\"" . $lang->badusers["delete"] . "\" border=\"0\"></a></td>" : "") . "\r\n\t\t\t</tr>";
                    }
                }
                $str .= "\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t</table>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
                $str .= $pagerbottom;
                echo $str;
                stdfoot();
            }
        }
    }
}
function mask($str, $start = 0, $length = NULL)
{
    $mask = preg_replace("/\\S/", "*", $str);
    if (is_null($length)) {
        $mask = substr($mask, $start);
        $str = substr_replace($str, $mask, $start);
    } else {
        $mask = substr($mask, $start, $length);
        $str = substr_replace($str, $mask, $start, $length);
    }
    return $str;
}

?>