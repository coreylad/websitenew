<?php
define("CSRF_PROTECTION", true);
define("S-M_VERSION", "v1.0 by xam");
define("THIS_SCRIPT", "s_message.php");
require "./global.php";
$torrentid = intval(TS_Global("id"));
if (!$torrentid || !$is_mod) {
    print_no_permission();
}
$TSSEConfig->TSLoadConfig("ANNOUNCE");
if ($xbt_active != "yes") {
    $FirstQuery = sql_query("SELECT DISTINCT userid FROM snatched WHERE `torrentid` = " . sqlesc($torrentid) . " AND $finished = 'yes'");
} else {
    $FirstQuery = sql_query("SELECT DISTINCT uid as userid FROM xbt_files_users WHERE `fid` = " . sqlesc($torrentid) . " AND `active` = 0 AND `left` = 0");
}
if (!mysqli_num_rows($FirstQuery)) {
    print_no_permission(false, true, $lang->global["noresultswiththisid"]);
}
$lang->load("messages");
require_once INC_PATH . "/functions_message.php";
require_once INC_PATH . "/functions_pm.php";
$prvp = showPreview("message");
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && !isset($_POST["previewpost"])) {
    $subject = isset($_POST["subject"]) ? trim($_POST["subject"]) : "";
    $message = isset($_POST["message"]) ? trim($_POST["message"]) : "";
    if ($subject && $message) {
        while ($User = mysqli_fetch_assoc($FirstQuery)) {
            send_pm($User["userid"], $message, $subject);
        }
        write_log("User \"" . $CURUSER["username"] . "\" has been sent a mass pm to all snatched users on the following torrent: " . $torrentid);
        define("FORCE_REDIRECT_MESSAGE", true);
        redirect("details.php?$id = " . $torrentid, $lang->global["msgsend"]);
        exit;
    }
}
$lang->load("details");
stdhead($lang->details["s_message"]);
define("IN_EDITOR", true);
include_once INC_PATH . "/editor.php";
$str = "\n<form $method = \"post\" $name = \"compose\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\n<input $type = \"hidden\" $name = \"securitytoken\" $value = \"" . $CURUSER["securitytoken"] . "\" />\n<input $type = \"hidden\" $name = \"id\" $value = \"" . $torrentid . "\" />";
$postoptionstitle = [1 => $lang->global["buttonsave"]];
$postoptions = [1 => "<input $type = \"checkbox\" $name = \"save\" $value = \"yes\" /> " . $lang->messages["savemsg"]];
$extrasubject = [];
if ($is_mod) {
    $StandartPMS = [$lang->messages["sm"] => "\n\t<select $name = \"standartpms\" $onchange = \"TS_Standart_Messages(this.value);\">\n\t\t<option $value = \"0\">" . $lang->messages["sm"] . "</option>\n\t\t<option $value = \"1\">" . $lang->messages["sm1a"] . "</option>\n\t\t<option $value = \"2\">" . $lang->messages["sm2a"] . "</option>\n\t\t<option $value = \"3\">" . $lang->messages["sm3a"] . "</option>\n\t\t<option $value = \"4\">" . $lang->messages["sm4a"] . "</option>\n\t\t<option $value = \"5\">" . $lang->messages["sm5a"] . "</option>\n\t\t<option $value = \"6\">" . $lang->messages["sm6a"] . "</option>\n\t\t<option $value = \"7\">" . $lang->messages["sm7a"] . "</option>\n\t\t<option $value = \"8\">" . $lang->messages["sm8a"] . "</option>\n\t\t<option $value = \"9\">" . $lang->messages["sm9a"] . "</option>\n\t\t<option $value = \"10\">" . $lang->messages["sm10a"] . "</option>\n\t</select>"];
    $extrasubject = $extrasubject + $StandartPMS;
}
if (!empty($prvp)) {
    $str .= $prvp;
}
$str .= insert_editor(true, isset($_POST["subject"]) ? $_POST["subject"] : (isset($subject) ? $subject : ""), isset($_POST["message"]) ? $_POST["message"] : (isset($body) ? $body : ""), $lang->details["s_message"], "", $postoptionstitle, $postoptions, true, $extrasubject, $lang->global["buttonsend"]);
$str .= "</form>";
echo $str . ($is_mod ? "\n<script $type = \"text/javascript\">\n\tfunction TS_Standart_Messages(mID)\n\t{\n\t\tif (mID && mID > 0)\n\t\t{\n\t\t\$tmessages = new Array();\n\t\t\tmessages[1] = \"" . $lang->messages["sm1b"] . "\";\n\t\t\tmessages[2] = \"" . $lang->messages["sm2b"] . "\";\n\t\t\tmessages[3] = \"" . $lang->messages["sm3b"] . "\";\n\t\t\tmessages[4] = \"" . $lang->messages["sm4b"] . "\";\n\t\t\tmessages[5] = \"" . $lang->messages["sm5b"] . "\";\n\t\t\tmessages[6] = \"" . $lang->messages["sm6b"] . "\";\n\t\t\tmessages[7] = \"" . $lang->messages["sm7b"] . "\";\n\t\t\tmessages[8] = \"" . $lang->messages["sm8b"] . "\";\n\t\t\tmessages[9] = \"" . $lang->messages["sm9b"] . "\";\n\t\t\tmessages[10] = \"" . $lang->messages["sm10b"] . "\";\n\t\t\tif (messages[mID])\n\t\t\t{\n\t\t\t\$ted = tinyMCE.activeEditor;\n\t\t\t\ted.focus();\n\t\t\t\ted.selection.setContent(ed.selection.getContent()+messages[mID]);\n\t\t\t}\n\t\t}\n\t}\n</script>" : "");
stdfoot();

?>