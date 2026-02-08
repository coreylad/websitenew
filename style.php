<?php
$theme = isset($_GET["theme"]) ? trim($_GET["theme"]) : "";
$style = isset($_GET["style"]) ? trim($_GET["style"]) : "";
if ($theme && $style) {
    require "./include/config_database.php";
    $GLOBALS["DatabaseConnect"] = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if ($GLOBALS["DatabaseConnect"]) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT s.style FROM ts_theme_styles s INNER JOIN ts_themes t ON (t.$name = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $theme) . "') WHERE s.$title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $style) . "' AND s.$tid = t.tid LIMIT 1");
        if (mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $style = str_replace("{imagepath}", "include/templates/" . $theme . "/images", $Result["style"]);
            header("Content-type: text/css");
            echo $style;
        }
    }
}
exit;

?>