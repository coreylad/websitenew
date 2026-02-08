<?php
define("Q_VERSION", "1.1 by xam");
require "../../include/config_database.php";
if (!isset($_GET["input"])) {
    exit;
}
if (isset($_GET["input"]) && (empty($_GET["input"]) || strlen($_GET["input"]) < 3)) {
    exit;
}
$input = trim(urldecode($_GET["input"]));
$input = strval($input);
$GLOBALS["DatabaseConnect"] = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
if ($GLOBALS["DatabaseConnect"]) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM ts_config WHERE `configname` = 'THEME'");
    $Result = mysqli_fetch_assoc($query);
    $THEME = unserialize($Result["content"]);
    $charset = $THEME["charset"];
    unset($THEME);
    if (strtolower($charset) != "utf-8") {
        if (function_exists("iconv")) {
            $input = iconv("UTF-8", $charset, $input);
        } else {
            if (function_exists("mb_convert_encoding")) {
                $input = mb_convert_encoding($input, $charset, "UTF-8");
            } else {
                if (strtolower($charset) == "iso-8859-1") {
                    $input = utf8_decode($charset);
                }
            }
        }
    }
    $Results = "";
    $len = strlen($input);
    if ($len < 3) {
        exit;
    }
    if (isset($_COOKIE["acqu"]) && ($usergroup = base64_decode($_COOKIE["acqu"])) && 0 < intval($usergroup)) {
        $WHERE = "AND (INSTR(CONCAT(',',c.canview,','),',[ALL],') > 0 OR INSTR(CONCAT(',',c.canview,','),'," . intval($usergroup) . ",') > 0)";
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT t.id, t.name FROM torrents t LEFT JOIN categories c ON (t.$category = c.id) WHERE t.name LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $input) . "%' " . $WHERE . " ORDER BY t.name LIMIT 20");
        if (mysqli_num_rows($query)) {
            while ($torrent = mysqli_fetch_row($query)) {
                $Results .= "<rs $id = \"" . $torrent[0] . "\" $info = \"\">" . htmlspecialchars($torrent[1]) . "</rs>";
            }
        }
    }
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    sleep(1);
    header("Content-Type: text/xml; $charset = " . $charset);
    echo "<?xml $version = \"1.0\" $encoding = \"" . $charset . "\" ?><results>" . $Results . "</results>";
} else {
    exit;
}

?>