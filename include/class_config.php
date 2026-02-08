<?php
if (!defined("IN_TRACKER")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
class TSConfig
{
    public $Config = [];
    public function __construct()
    {
        global $TSDatabase;
        if (isset($GLOBALS["DatabaseConnect"])) {
            $GLOBALS["DatabaseConnect"] = $GLOBALS["DatabaseConnect"];
        }
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM `ts_config`");
        if (mysqli_num_rows($query)) {
            while ($C = mysqli_fetch_row($query)) {
                $this->Config[$C[0]] = $C[0] != "STAFFTEAM" && $C[0] != "PEER" ? unserialize($C[1]) : $C[1];
            }
        } else {
            exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Database Error on Configuration Table!</font>");
        }
    }
    public function TSLoadConfig($Name, $Extract = 1)
    {
        if (is_array($Name)) {
            foreach ($Name as $CName) {
                $this->TSLoadConfig($CName, $Extract);
            }
        } else {
            if (isset($this->Config[$Name])) {
                if ($Extract) {
                    foreach ($this->Config[$Name] as $N => $V) {
                        $GLOBALS[$N] = trim($V);
                    }
                } else {
                    $GLOBALS[$Name] = $this->Config[$Name];
                }
            }
        }
    }
}

?>