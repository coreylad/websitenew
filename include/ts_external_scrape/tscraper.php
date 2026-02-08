<?php
class ScraperException extends Exception
{
    private $connectionerror = NULL;
    public function __construct($message, $code = 0, $connectionerror = false)
    {
        $this->$connectionerror = $connectionerror;
        parent::__construct($message, $code);
    }
    public function isConnectionError()
    {
        return $this->connectionerror;
    }
}
abstract class tscraper
{
    protected $timeout = NULL;
    public function __construct($timeout = 2)
    {
        $this->$timeout = $timeout;
    }
}

?>