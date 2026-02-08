<?php
if (ini_get("date.timezone") == "" && function_exists("date_default_timezone_set")) {
    @date_default_timezone_set(@date_default_timezone_get());
}

?>