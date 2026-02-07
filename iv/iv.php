<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("IV_VERSION", "0.3 by xam");
if (isset($_SESSION["error_count"]) && 5 <= $_SESSION["error_count"]) {
    stderr($lang->global["error"], $lang->global["signupdisabled"]);
    exit;
}
if (!isset($_SESSION["correct_image_entered"])) {
    if (!isset($_GET["selected"])) {
        $amount_of_images_to_show = 4;
        $answer_position = rand(1, $amount_of_images_to_show);
        $answers = [];
        $allowed_images = ["png", "gif", "jpg"];
        if ($ivfiles = scandir("./iv/images/")) {
            foreach ($ivfiles as $file) {
                if ($file != "." && $file != ".." && $file != "show_image.php" && $file != ".htaccess" && $file != "thumbs.db" && in_array(strtolower(substr(strrchr($file, "."), 1)), $allowed_images)) {
                    $answers[] = $file;
                }
            }
        }
        $amount = count($answers);
        $the_answer = rand(1, $amount);
        for ($i = 1; $i < $amount_of_images_to_show; $i++) {
            if ($answer_position == $i) {
                $show_image[] = $the_answer;
            }
            $tmp = rand(1, $amount);
            while ($tmp == $the_answer || isset($show_image) && is_array($show_image) && in_array($tmp, $show_image)) {
                $tmp = rand(1, $amount);
            }
            $show_image[] = $tmp;
        }
        if ($answer_position == $i) {
            $show_image[] = $the_answer;
        }
        $_SESSION["answer_position"] = $answer_position;
        $_SESSION["show_images"] = $show_image;
        $question = substr($answers[$the_answer - 1], 0, -4);
    } else {
        if (isset($_GET["selected"]) && isset($_SESSION["answer_position"]) && $_GET["selected"] == $_SESSION["answer_position"]) {
            $_SESSION["correct_image_entered"] = 1;
        } else {
            $badchoice = 1;
            unset($_SESSION["correct_image_entered"]);
            if (isset($_SESSION["error_count"])) {
                $_SESSION["error_count"]++;
            } else {
                $_SESSION["error_count"] = 1;
            }
        }
    }
    if (!isset($_SESSION["correct_image_entered"])) {
        $lang->load("iv");
        stdhead($lang->iv["title"]);
        unset($_SESSION["correct_image_entered"]);
        if (isset($badchoice)) {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\talert(\"" . $lang->iv["error2"] . "\");\r\n\t\t\t</script>\r\n\t\t\t" . show_notice(sprintf($lang->iv["error1"], $_SERVER["SCRIPT_NAME"] . "?$failed = true" . ($invitehash ? "&amp;$invitehash = " . $invitehash : "") . ($referrer ? "&amp;$referrer = " . $referrer : "")), true, $lang->iv["title"], "");
        } else {
            echo "\r\n\t\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\">" . $lang->iv["title"] . "</td>\r\n\t\t\t\t</tr>\t\t\t\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"center\"><br />\r\n\t\t\t\t\t\t<div class=\"panel\" $style = \"width:70%\">\r\n\t\t\t\t\t\t\t" . ($answers ? "\r\n\t\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$selected = 1" . ($invitehash ? "&amp;$invitehash = " . $invitehash : "") . ($referrer ? "&amp;$referrer = " . $referrer : "") . "\"><img $src = \"" . $BASEURL . "/iv/images/show_image.php?$image = 1\" $border = \"0\" $alt = \"\" /></a>\r\n\t\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$selected = 2" . ($invitehash ? "&amp;$invitehash = " . $invitehash : "") . ($referrer ? "&amp;$referrer = " . $referrer : "") . "\"><img $src = \"" . $BASEURL . "/iv/images/show_image.php?$image = 2\" $border = \"0\" $alt = \"\" /></a>\r\n\t\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$selected = 3" . ($invitehash ? "&amp;$invitehash = " . $invitehash : "") . ($referrer ? "&amp;$referrer = " . $referrer : "") . "\"><img $src = \"" . $BASEURL . "/iv/images/show_image.php?$image = 3\" $border = \"0\" $alt = \"\" /></a>\r\n\t\t\t\t\t\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$selected = 4" . ($invitehash ? "&amp;$invitehash = " . $invitehash : "") . ($referrer ? "&amp;$referrer = " . $referrer : "") . "\"><img $src = \"" . $BASEURL . "/iv/images/show_image.php?$image = 4\" $border = \"0\" $alt = \"\" /></a>\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t<br /><br />\r\n\t\t\t\t\t\t\t" . $lang->iv["image"] . "<br /><b>" . htmlspecialchars($question) . "</b>\r\n\t\t\t\t\t\t\t<br /><br />\r\n\t\t\t\t\t\t\t<input $type = \"button\" $value = \"" . $lang->iv["refresh"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "'); return false;\" />\r\n\t\t\t\t\t\t\t" : "") . "\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>";
        }
        stdfoot();
        exit;
    }
}

?>