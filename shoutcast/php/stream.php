<?php
set_time_limit(0);
require "./../../include/config_database.php";
$GLOBALS["DatabaseConnect"] = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
if ($GLOBALS["DatabaseConnect"]) {
    define("IN_TRACKER", true);
    require "./../../include/class_config.php";
    $TSSEConfig = new TSConfig();
    $TSSEConfig->TSLoadConfig("SHOUTCAST");
    if ($sock = @fsockopen($s_serverip, $s_serverport, $errno, $errstr, isset($_GET["ping"]) && $_GET["ping"] == "true" ? 1 : 5)) {
        @fputs($sock, "GET / HTTP/1.0\r\n");
        @fputs($sock, "Host: " . $s_servername . "\r\n");
        @fputs($sock, "User-Agent: WinampMPEG/2.8\r\n");
        @fputs($sock, "Connection: close\r\n\r\n");
        if (isset($_GET["ping"]) && $_GET["ping"] == "true") {
            echo @fread($sock, 8);
        } else {
            while ($contents = @fread($sock, 524)) {
                echo $contents;
            }
        }
        fclose($sock);
    }
} else {
    exit("DB Connection Error!");
}

?>