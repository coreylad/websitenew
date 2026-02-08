<?php
if (!defined("INC_PATH") || !defined("TSDIR")) {
    exit("Include and TSDIR paths does not defined correctly!");
}
class TSDatabase
{
    public $DatabaseConnect = "";
    public function Connect()
    {
        require_once INC_PATH . "/config_database.php";
        if (!($this->DatabaseConnect = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB))) {
            mysqli_errno($this->DatabaseConnect);
            switch (mysqli_errno($this->DatabaseConnect)) {
                case 1040:
                    define("errorid", 1040);
                    break;
                case 2002:
                    define("errorid", 2002);
                    break;
                default:
                    define("errorid", 5);
                    include_once TSDIR . "/ts_error.php";
                    exit;
            }
        } else {
            $this->set_charset();
            $GLOBALS["DatabaseConnect"] = $this->DatabaseConnect;
        }
    }
    public function set_charset()
    {
        if (MYSQL_CHARSET != "") {
            if (function_exists("mysqli_set_charset")) {
                mysqli_set_charset($this->DatabaseConnect, MYSQL_CHARSET);
            } else {
                mysqli_query($this->DatabaseConnect, "SET NAMES " . MYSQL_CHARSET);
            }
        }
    }
}

?>