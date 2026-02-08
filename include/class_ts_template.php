<?php
class TS_Template
{
    private $Cache = NULL;
    public function __construct()
    {
        global $TemplateList;
        $TemplateList = array_merge($TemplateList, ["head", "foot"]);
        $this->CacheTemplates(array_map([$this, "Escape"], $TemplateList));
    }
    public function CacheTemplates($TemplateList)
    {
        global $TSDatabase;
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, content FROM ts_templates WHERE name IN (" . implode(",", $TemplateList) . ")");
        while ($Template = mysqli_fetch_assoc($query)) {
            $this->Cache[$Template["name"]] = $Template["content"];
        }
    }
    public function LoadTemplate($Name)
    {
        global $TSDatabase;
        if (!isset($this->Cache[$Name])) {
            $Result = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT content FROM ts_templates WHERE $name = " . $this->Escape($Name));
            $Row = mysqli_fetch_assoc($Result);
            $Content = $Row["content"];
            $this->Cache[$Name] = $Content;
            unset($Content);
        }
        return $this->fixTemplate($this->Cache[$Name]);
    }
    public function PrintOutput($Output)
    {
        global $charset;
        header("Content-Length: " . strlen($Output));
        header("Content-type: text/html; $charset = " . $charset);
        exit($Output);
    }
    public function Escape($Q)
    {
        global $TSDatabase;
        return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], trim($Q)) . "'";
    }
    public function fixTemplate($Content)
    {
        return str_replace("\\'", "'", addslashes($Content));
    }
}

?>