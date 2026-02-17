<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('manage_countries');

$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);

$Message = "";

try {
    $result = $TSDatabase->query("SELECT content FROM ts_config WHERE configname = 'MAIN'");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $MAIN = unserialize($row["content"]);
} catch (Exception $e) {
    error_log('Get MAIN config error: ' . $e->getMessage());
    $MAIN = [];
}

if ($Act === "delete" && $Cid) {
    try {
        $result = $TSDatabase->query("SELECT name FROM countries WHERE id = ?", [$Cid]);
        $country = $result->fetch(PDO::FETCH_ASSOC);
        
        if ($country) {
            $TSDatabase->query("DELETE FROM countries WHERE id = ?", [$Cid]);
            $Message = str_replace(["{1}", "{2}"], [escape_html($country["name"]), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[13]);
            logStaffActionModern($Message);
            $Message = showAlertSuccessModern($Message);
        }
    } catch (Exception $e) {
        error_log('Delete country error: ' . $e->getMessage());
        $Message = showAlertErrorModern('Failed to delete country');
    }
}

if ($Act === "new") {
    $name = "";
    $flagpic = "";
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            $Message = showAlertErrorModern('Invalid form token');
        } else {
            $name = trim($_POST["name"] ?? '');
            $flagpic = trim($_POST["flagpic"] ?? '');
            
            if ($name && $flagpic) {
                try {
                    $TSDatabase->query("INSERT INTO countries (name, flagpic) VALUES (?, ?)", [$name, $flagpic]);
                    $Message = str_replace(["{1}", "{2}"], [escape_html($name), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[15]);
                    logStaffActionModern($Message);
                    $Message = showAlertSuccessModern($Message);
                    $Done = true;
                } catch (Exception $e) {
                    error_log('Add country error: ' . $e->getMessage());
                    $Message = showAlertErrorModern('Failed to add country');
                }
            } else {
                $Message = showAlertErrorModern($Language[4]);
            }
        }
    }
    
    if (!isset($Done)) {
        echo "\r\n\t\t<form action=\"index.php?do=manage_countries&act=new\" method=\"post\" name=\"manage_countries\">\r\n\t\t" . getFormTokenField() . "\r\n\t\t" . $Message . "\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[10]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($Language[5]) . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"name\" value=\"" . escape_attr($name) . "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . escape_html($Language[6]) . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . buildFlagSelector($flagpic, $MAIN) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\t\r\n\t\t\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . escape_attr($Language[11]) . "\" accesskey=\"s\" />\r\n\t\t\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"" . escape_attr($Language[12]) . "\" accesskey=\"r\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}

if ($Act === "edit" && $Cid) {
    try {
        $result = $TSDatabase->query("SELECT name, flagpic FROM countries WHERE id = ?", [$Cid]);
        $Country = $result->fetch(PDO::FETCH_ASSOC);
        
        if ($Country) {
            $name = $Country["name"];
            $flagpic = $Country["flagpic"];
            
            if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {
                if (!validateFormToken($_POST['form_token'] ?? '')) {
                    $Message = showAlertErrorModern('Invalid form token');
                } else {
                    $name = trim($_POST["name"] ?? '');
                    $flagpic = trim($_POST["flagpic"] ?? '');
                    
                    if ($name && $flagpic) {
                        try {
                            $TSDatabase->query("UPDATE countries SET name = ?, flagpic = ? WHERE id = ?", [$name, $flagpic, $Cid]);
                            $Message = str_replace(["{1}", "{2}"], [escape_html($name), escape_html($_SESSION["ADMIN_USERNAME"])], $Language[14]);
                            logStaffActionModern($Message);
                            $Message = showAlertSuccessModern($Message);
                            $Done = true;
                        } catch (Exception $e) {
                            error_log('Update country error: ' . $e->getMessage());
                            $Message = showAlertErrorModern('Failed to update country');
                        }
                    } else {
                        $Message = showAlertErrorModern($Language[4]);
                    }
                }
            }
            
            if (!isset($Done)) {
                echo "\r\n\t\t\t<form action=\"index.php?do=manage_countries&act=edit&id=" . escape_attr((string)$Cid) . "\" method=\"post\" name=\"manage_countries\">\r\n\t\t\t" . getFormTokenField() . "\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t\t\t" . escape_html($Language[2]) . " - " . escape_html($Language[8]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . escape_html($Language[5]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"name\" value=\"" . escape_attr($name) . "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . escape_html($Language[6]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . buildFlagSelector($flagpic, $MAIN) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\">\t\r\n\t\t\t\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"" . escape_attr($Language[11]) . "\" accesskey=\"s\" />\r\n\t\t\t\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"" . escape_attr($Language[12]) . "\" accesskey=\"r\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    } catch (Exception $e) {
        error_log('Get country error: ' . $e->getMessage());
    }
}

$Found = "";
try {
    $result = $TSDatabase->query("SELECT * FROM countries ORDER by name ASC");
    $countries = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($countries) > 0) {
        foreach ($countries as $Country) {
            $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">" . escape_html($Country["name"]) . "</td>\r\n\t\t\t<td class=\"alt1\"><img src=\"" . escape_attr($MAIN["pic_base_url"]) . "flag/" . escape_attr($Country["flagpic"]) . "\" alt=\"\" border=\"\" /></td>\r\n\t\t\t<td class=\"alt1\" align=\"center\"><a href=\"index.php?do=manage_countries&amp;act=edit&amp;id=" . escape_attr((string)$Country["id"]) . "\"><img src=\"images/tool_edit.png\" alt=\"" . escape_attr($Language[8]) . "\" title=\"" . escape_attr($Language[8]) . "\" border=\"0\" /></a> <a href=\"index.php?do=manage_countries&amp;act=delete&amp;id=" . escape_attr((string)$Country["id"]) . "\"><img src=\"images/tool_delete.png\" alt=\"" . escape_attr($Language[9]) . "\" title=\"" . escape_attr($Language[9]) . "\" border=\"0\" /></a></td>\r\n\t\t</tr>";
        }
    } else {
        $Found .= "<tr><td colspan=\"5\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_countries&amp;act=new", $Language[16]) . "</td></tr>";
    }
} catch (Exception $e) {
    error_log('List countries error: ' . $e->getMessage());
}

echo showAlertMessageModern("<a href=\"index.php?do=manage_countries&amp;act=new\">" . escape_html($Language[10]) . "</a>") . "\t\t\r\n\r\n" . $Message . "\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" colspan=\"3\" align=\"center\">\r\n\t\t\t" . escape_html($Language[2]) . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">" . escape_html($Language[5]) . "</td>\r\n\t\t<td class=\"alt2\">" . escape_html($Language[6]) . "</td>\r\n\t\t<td class=\"alt2\" align=\"center\">" . escape_html($Language[7]) . "</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>\r\n";

function buildFlagSelector($selected = "", $MAIN = [])
{
    $countryId = is_dir("../images") ? "../images/flag/" : (is_dir("../pic") ? "../pic/flag/" : null);
    
    if (!$countryId) {
        return "<select name=\"flagpic\"></select>";
    }
    
    $countryName = $MAIN["pic_base_url"] . "/flag/";
    $countryList = scandir($countryId);
    $countryFlag = "<select name=\"flagpic\">";
    
    foreach ($countryList as $countryData) {
        $ext = strtolower(substr(strrchr($countryData, "."), 1));
        if (in_array($ext, ["png", "gif", "jpg"])) {
            $countryFlag .= "<option value=\"" . escape_attr($countryData) . "\"" . ($selected === $countryData ? " selected=\"selected\"" : "") . ">" . escape_html($countryData) . "</option>";
        }
    }
    
    $countryFlag .= "</select>";
    return $countryFlag;
}
