<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

class TSSECache
{
    public $Cache = NULL;
    public function __construct()
    {
        global $TSDatabase;
        $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM `ts_cache`");
        if (mysqli_num_rows($Q)) {
            while ($C = mysqli_fetch_assoc($Q)) {
                $this->Cache[$C["cachename"]] = ["content" => $C["content"], "lastupdate" => $C["lastupdate"]];
            }
        }
    }
    public function UpdateCache($cachename, $content)
    {
        global $TSDatabase;
        $time = time();
        mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_cache` VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $cachename) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $content) . "', " . $time . ")");
        $this->Cache[$cachename] = ["content" => $content, "lastupdate" => $time];
    }
}

?>