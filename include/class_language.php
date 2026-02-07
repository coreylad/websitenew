<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

class trackerlanguage
{
    public $path = NULL;
    public $language = NULL;
    public function set_path($path)
    {
        $this->$path = $path;
    }
    public function set_language($language = "english")
    {
        $language = str_replace(["/", "\\", ".."], "", trim($language));
        if ($language == "") {
            $language = "english";
        }
        $this->$language = $language;
    }
    public function loadResource($section)
    {
        global $rootpath;
        global $BASEURL;
        global $staffcp_path;
        global $CURUSER;
        global $pic_base_url;
        $lfile = $this->path . "/" . $this->language . "/" . $section . ".lang.php";
        if (file_exists($lfile)) {
            require_once $lfile;
            if (isset($language) && is_array($language)) {
                foreach ($language as $key => $val) {
                    if (!isset($this->{$key}) || $this->{$key} != $val) {
                        $val = preg_replace("#\\{([0-9]+)\\}#", "%\$1\\\$s", $val);
                        $this->{$key} = $val;
                    }
                }
            }
        } else {
            define("errorid", 3);
            include_once TSDIR . "/ts_error.php";
            exit;
        }
    }
}

?>