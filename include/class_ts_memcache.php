<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

class TSMemcache
{
    private $Mobject = NULL;
    private $Host = NULL;
    private $Port = NULL;
    private $Announce = false;
    public function __construct($Host = "localhost", $Port = 11211, $Announce = false)
    {
        $this->Mobject = new Memcache();
        if (!$this->Mobject->connect($Host, $Port)) {
            if ($Announce) {
                Stop("Unable connect to Memcache server.");
            } else {
                exit("<h1>Unable connect to <b>Memcache</b> server.</h1>");
            }
        }
        $this->Host = $Host;
        $this->Port = $Port;
        $this->Announce = $Announce;
    }
    public function check($hash = "")
    {
        return $this->Mobject->get($hash);
    }
    public function add($hash = "", $value = "", $timeout = 300)
    {
        $this->Mobject->add($hash, $value, false, $timeout);
    }
    public function status()
    {
        return $this->Mobject->getServerStatus($this->Host, $this->Port);
    }
    public function stats()
    {
        return $this->Mobject->getStats();
    }
    public function version()
    {
        return $this->Mobject->getVersion();
    }
    public function flush()
    {
        return $this->Mobject->flush();
    }
}

?>