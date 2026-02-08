<?php
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    exit(file_get_contents("lps.dat"));
}

?>