<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/themes.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$ThemeCount = [];
if ($Act == "export_theme" && ($tid = intval($_GET["tid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_themes WHERE $tid = " . $tid);
    if (mysqli_num_rows($query)) {
        $StyleCache = [];
        $Theme = mysqli_fetch_assoc($query);
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_theme_styles WHERE $tid = " . $tid);
        while ($style = mysqli_fetch_assoc($query)) {
            $StyleCache[$style["title"]] = $style["style"];
        }
        $XML = "<?xml $version = \"1.0\"?>\r\n<theme>\r\n\t<name>" . $Theme["name"] . "</name>\r\n\t<description>" . $Theme["description"] . "</description>\r\n\t<required_version>" . $Theme["required_version"] . "</required_version>\r\n\t<theme_version>" . $Theme["theme_version"] . "</theme_version>\r\n\t<author>" . $Theme["author"] . "</author>\r\n\t<style.css>" . $StyleCache["style.css"] . "</style.css>\r\n</theme>";
        header("Content-disposition: attachment; $filename = " . rawurlencode($Theme["name"]) . "-theme.xml");
        header("Content-type: application/octet-stream");
        header("Content-Length: " . strlen($XML));
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $XML;
        exit;
    }
}
if ($Act == "new_style" && ($tid = intval($_GET["tid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_themes WHERE $tid = " . $tid);
    if (mysqli_num_rows($query)) {
        $List = "";
        $TStyle = mysqli_fetch_assoc($query);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $stylesheet = isset($_POST["stylesheet"]) ? trim($_POST["stylesheet"]) : "";
            $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
            $description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
            if (!$stylesheet) {
                $Message = $Language[27];
            }
            if (!$title) {
                $Message = $Language[60];
            }
            if (!$Message) {
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_theme_styles SET $tid = '" . $tid . "', $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $style = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $stylesheet) . "',  $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "'");
                $Message = str_replace(["{1}", "{2}", "{3}"], [$title, $TStyle["name"], $_SESSION["ADMIN_USERNAME"]], $Language[61]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Updated = true;
                unset($List);
                $Act = "edit_styles";
                $_GET["tid"] = $TStyle["tid"];
            } else {
                $Message = showAlertError($Message);
            }
        }
        if (!isset($Updated)) {
            $List = "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=themes&$act = new_style&$tid = " . $tid . "\">\r\n\t\t\t" . showAlertMessage("<a $href = \"index.php?do=themes&amp;$act = edit_styles&amp;$tid = " . $TStyle["tid"] . "\">" . $Language[16] . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\">" . $Language[58] . ": " . $TStyle["name"] . " (" . $tid . ")</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $align = \"left\">" . $Language[14] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"title\" $value = \"" . (isset($title) ? $title : "") . "\" $style = \"width: 99%;\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $align = \"left\">" . $Language[22] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"description\" $value = \"" . (isset($description) ? $description : "") . "\" $style = \"width: 99%;\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $align = \"left\">" . $Language[59] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t<textarea $name = \"stylesheet\" $style = \"width: 99%; height: 200px;\">" . (isset($stylesheet) ? $stylesheet : "") . "</textarea>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "import_theme") {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $CheckVersion = isset($_POST["ignore_version"]) ? false : true;
        if (($Theme = $_FILES["theme"]) && $Theme["name"] && $Theme["tmp_name"] && function_149($Theme["name"]) == "xml" && 0 < $Theme["size"] && $Theme["error"] == "0") {
            if ($Contents = file_get_contents($Theme["tmp_name"])) {
                @unlink($Theme["tmp_name"]);
                $name = themeInstall($Contents, "name");
                $description = themeInstall($Contents, "description");
                $required_version = themeInstall($Contents, "required_version");
                $theme_version = themeInstall($Contents, "theme_version");
                $author = themeInstall($Contents, "author");
                $style = themeInstall($Contents, "style.css");
                if ($name && $description && $required_version && $theme_version && $author && $style) {
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_themes WHERE $name = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "'");
                    if (mysqli_num_rows($query)) {
                        $Message = showAlertError($Language[40]);
                    } else {
                        if (SHORT_SCRIPT_VERSION < $required_version && $CheckVersion) {
                            $Message = showAlertError($Language[41]);
                        } else {
                            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_themes (name, description, required_version, theme_version, author) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $required_version) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $theme_version) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $author) . "')");
                            $tid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                            if ($tid) {
                                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_theme_styles (tid, title, style, description) VALUES (" . $tid . ", 'style.css', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $style) . "', 'This is Master Style.')");
                                $Updated = true;
                                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[33]);
                                logStaffAction($Message);
                                $Message = showAlertError($Message);
                                function_320();
                            } else {
                                $Message = showAlertError($Language[39]);
                            }
                        }
                    }
                } else {
                    $Errors = "";
                    if (!$name) {
                        $Errors .= (string) $name;
                    }
                    if (!$description) {
                        $Errors .= (string) $description;
                    }
                    if (!$required_version) {
                        $Errors .= (string) $required_version;
                    }
                    if (!$theme_version) {
                        $Errors .= (string) $theme_version;
                    }
                    if (!$author) {
                        $Errors .= (string) $author;
                    }
                    if (!$style) {
                        $Errors .= (string) $style;
                    }
                    $Message = showAlertError($Language[45] . " (" . $Errors . ")");
                }
            } else {
                $Message = showAlertError($Language[38]);
            }
        } else {
            $Message = showAlertError($Language[37]);
        }
    }
    if (!isset($Updated)) {
        $List = "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=themes&$act = import_theme\" $enctype = \"multipart/form-data\">\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=themes\">" . $Language[16] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">" . $Language[36] . "</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[34] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[35] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"file\" $name = \"theme\" $style = \"width: 250px;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[42] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[43] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"ignore_version\" $value = \"1\" /> " . $Language[44] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "delete_theme" && ($tid = intval($_GET["tid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM ts_themes WHERE $tid = " . $tid . " AND $isdefault = 0");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $name = $Result["name"];
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_themes WHERE $tid = " . $tid);
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_theme_styles WHERE $tid = " . $tid);
        $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[29]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
        function_320();
    }
}
if ($Act == "copy_theme" && ($tid = intval($_GET["tid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_themes WHERE $tid = " . $tid);
    if (mysqli_num_rows($query)) {
        $Theme = mysqli_fetch_assoc($query);
        $name = "Copy of " . $Theme["name"];
        $description = $Theme["description"];
        $i = 1;
        while ($i <= 99) {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_themes WHERE $name = '" . $name . "'");
            if (mysqli_num_rows($query) == 0) {
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_themes (name, description, required_version, theme_version, author) VALUES ('" . $name . "', 'This is Copy of \"" . $Theme["name"] . "\" Theme', '" . $Theme["required_version"] . "', '" . $Theme["theme_version"] . "', '" . $Theme["author"] . "')");
                $newtid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            } else {
                $name = "Copy " . $i . " of " . $Theme["name"];
                $i++;
            }
        }
        if ($newtid) {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_theme_styles WHERE $tid = " . $tid);
            if (mysqli_num_rows($query)) {
                while ($T = mysqli_fetch_assoc($query)) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_theme_styles (tid, title, style, description) VALUES (" . $newtid . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $T["title"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $T["style"]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $T["description"]) . "')");
                }
                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[28]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                function_320();
            }
        }
    }
}
if ($Act == "manage_style" && ($sid = intval($_GET["sid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_theme_styles WHERE $sid = " . $sid);
    if (mysqli_num_rows($query)) {
        $List = "";
        $TStyle = mysqli_fetch_assoc($query);
        $stylesheet = trim($TStyle["style"]);
        $name = trim($TStyle["title"]);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $stylesheet = isset($_POST["stylesheet"]) ? trim($_POST["stylesheet"]) : "";
            if (!$stylesheet) {
                $Message = $Language[27];
            }
            if (!$Message) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_theme_styles SET $style = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $stylesheet) . "' WHERE $sid = " . $sid);
                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[19]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                unset($List);
            } else {
                $Message = showAlertError($Message);
            }
        }
        if (!isset($Updated)) {
            $List = "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=themes&$act = manage_style&$sid = " . $sid . "\">\r\n\t\t\t" . showAlertMessage("<a $href = \"index.php?do=themes&amp;$act = edit_styles&amp;$tid = " . $TStyle["tid"] . "\">" . $Language[16] . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\">" . $Language[15] . ": " . $name . " (" . $sid . ")</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $align = \"center\">" . $TStyle["description"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t<textarea $name = \"stylesheet\" $style = \"width: 99%; height: 400px;\">" . $stylesheet . "</textarea>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "edit_html" && ($tid = intval($_GET["tid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM ts_themes WHERE $tid = " . $tid);
    if (mysqli_num_rows($query)) {
        $List = "";
        $Theme = mysqli_fetch_assoc($query);
        $name = trim($Theme["name"]);
        $Folder = "../include/templates/" . $name . "/";
        $HeaderFile = $Folder . "header.php";
        $FooterFile = $Folder . "footer.php";
        $header = file_get_contents($HeaderFile);
        $footer = file_get_contents($FooterFile);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $header = isset($_POST["header"]) ? trim($_POST["header"]) : "";
            $footer = isset($_POST["footer"]) ? trim($_POST["footer"]) : "";
            if (empty($header) || empty($footer)) {
                $Message = $Language[49];
            }
            if (!is_writable($HeaderFile)) {
                $Message = $Language[50];
            }
            if (!is_writable($FooterFile)) {
                $Message = $Language[51];
            }
            if (!$Message) {
                $Updated = true;
                if (file_put_contents($HeaderFile, $header) && file_put_contents($FooterFile, $footer)) {
                    $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[57]);
                    logStaffAction($Message);
                    $Message = showAlertError($Message);
                } else {
                    $Message = showAlertError($Language[52]);
                }
            } else {
                $Message = showAlertError($Message);
            }
        }
        if (!isset($Updated)) {
            $List = "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=themes&$act = edit_html&$tid = " . $tid . "\">\r\n\t\t\t" . showAlertMessage("<a $href = \"index.php?do=themes\">" . $Language[16] . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\">" . $Language[48] . ": " . $Theme["name"] . " (" . $tid . ")</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\">" . $Language[53] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $_SERVER["DOCUMENT_ROOT"] . "/include/templates/" . $Theme["name"] . "/header.php " . (is_writable($HeaderFile) ? $Language[55] : $Language[56]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<textarea $name = \"header\" $style = \"width: 99%; height: 400px;\">" . $header . "</textarea>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\">" . $Language[54] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $_SERVER["DOCUMENT_ROOT"] . "/include/templates/" . $Theme["name"] . "/footer.php " . (is_writable($FooterFile) ? $Language[55] : $Language[56]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<textarea $name = \"footer\" $style = \"width: 99%; height: 200px;\">" . $footer . "</textarea>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        } else {
            unset($List);
        }
    }
}
if ($Act == "edit_theme" && ($tid = intval($_GET["tid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_themes WHERE $tid = " . $tid);
    if (mysqli_num_rows($query)) {
        $List = "";
        $Theme = mysqli_fetch_assoc($query);
        $name = trim($Theme["name"]);
        $description = trim($Theme["description"]);
        $Folder = "../include/templates/" . $name . "/";
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
            $description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
            $Folder = "../include/templates/" . $name . "/";
            if (!$name) {
                $Message = $Language[25];
            }
            if (!is_dir($Folder)) {
                $Message = str_replace("{1}", $Folder, $Language[26]);
            }
            if (!$Message) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_themes SET $name = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "' WHERE $tid = " . $tid);
                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[18]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Updated = true;
                unset($List);
                function_320();
            } else {
                $Message = showAlertError($Message);
            }
        }
        if (!isset($Updated)) {
            $List = "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=themes&$act = edit_theme&$tid = " . $tid . "\">\r\n\t\t\t" . showAlertMessage("<a $href = \"index.php?do=themes\">" . $Language[16] . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">" . $Language[7] . ": " . $Theme["name"] . " (" . $tid . ")</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[4] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[24] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"name\" $value = \"" . $name . "\" $size = \"50\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[22] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[47] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"description\" $value = \"" . $description . "\" $size = \"50\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "edit_styles" && ($tid = intval($_GET["tid"]))) {
    $DefaultStyles = ["style.css"];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM ts_themes WHERE $tid = " . $tid);
    if (mysqli_num_rows($query)) {
        $List = "";
        $Result = mysqli_fetch_assoc($query);
        $ThemeName = $Result["name"];
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_theme_styles WHERE $tid = " . $tid . " ORDER BY title ASC");
        if (mysqli_num_rows($query)) {
            while ($Style = mysqli_fetch_assoc($query)) {
                $List .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $align = \"center\">" . $Style["sid"] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Style["title"] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $ThemeName . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Style["description"] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\" $align = \"center\"><a $href = \"index.php?do=themes&amp;$act = manage_style&amp;$sid = " . $Style["sid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[15]) . "\" $title = \"" . trim($Language[15]) . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t</tr>";
            }
        }
        $List = "\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=themes\">" . $Language[16] . "</a> | <a $href = \"index.php?do=themes&amp;$act = new_style&amp;$tid = " . $tid . "\">" . $Language[58] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\">" . $Language[23] . ": " . $ThemeName . " (" . $tid . ")</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[22] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $List . "\r\n\t\t</table>";
    }
}
if (!isset($List)) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM ts_themes WHERE $isdefault = 1");
    $Result = mysqli_fetch_assoc($query);
    $defaultthemename = $Result["name"];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT stylesheet FROM users");
    while ($S = mysqli_fetch_assoc($query)) {
        if (empty($S["stylesheet"]) || $S["stylesheet"] && !is_dir("../include/templates/" . $S["stylesheet"])) {
            $S["stylesheet"] = $defaultthemename;
        }
        if (isset($ThemeCount[$S["stylesheet"]])) {
            $ThemeCount[$S["stylesheet"]]++;
        } else {
            $ThemeCount[$S["stylesheet"]] = 1;
        }
    }
    $List = "";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_themes ORDER by isdefault DESC, name ASC");
    while ($T = mysqli_fetch_assoc($query)) {
        $List .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">" . $T["tid"] . "</td>\r\n\t\t\t<td class=\"alt1\">" . $T["name"] . "</td>\r\n\t\t\t<td class=\"alt1\">" . $T["description"] . "</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">" . (isset($ThemeCount[$T["name"]]) ? number_format($ThemeCount[$T["name"]]) : 0) . "</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">TS SE v." . $T["required_version"] . "</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">v." . $T["theme_version"] . "</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">" . $T["author"] . "</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\"><a $href = \"index.php?do=themes&amp;$act = edit_theme&amp;$tid = " . $T["tid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[7]) . "\" $title = \"" . trim($Language[7]) . "\" $border = \"0\" /></a> <a $href = \"index.php?do=themes&amp;$act = edit_styles&amp;$tid = " . $T["tid"] . "\"><img $src = \"images/link_go.png\" $alt = \"" . trim($Language[23]) . "\" $title = \"" . trim($Language[23]) . "\" $border = \"0\" /></a> <a $href = \"index.php?do=themes&amp;$act = edit_html&amp;$tid = " . $T["tid"] . "\"><img $src = \"images/html.png\" $alt = \"" . trim($Language[48]) . "\" $title = \"" . trim($Language[48]) . "\" $border = \"0\" /></a> <a $href = \"index.php?do=themes&amp;$act = export_theme&amp;$tid = " . $T["tid"] . "\"><img $src = \"images/download.png\" $alt = \"" . trim($Language[46]) . "\" $title = \"" . trim($Language[46]) . "\" $border = \"0\" /></a> <a $href = \"index.php?do=themes&amp;$act = copy_theme&amp;$tid = " . $T["tid"] . "\"><img $src = \"images/add.png\" $alt = \"" . trim($Language[8]) . "\" $title = \"" . trim($Language[8]) . "\" $border = \"0\" /></a> <a $href = \"index.php?do=themes&amp;$act = delete_theme&amp;$tid = " . $T["tid"] . "\"" . ($T["isdefault"] == "1" ? " $onclick = \"alert('" . trim($Language[11]) . "'); return false;\"" : " $onclick = \" return confirm('" . trim($Language[10]) . "');\"") . "><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[9]) . "\" $title = \"" . trim($Language[9]) . "\" $border = \"0\" /></a></td>\r\n\t\t</tr>";
    }
    echo "\r\n\t" . showAlertMessage("<span $style = \"float: right;\"><a $href = \"index.php?do=themes&amp;$act = import_theme\">" . $Language[36] . "</a></span>" . $Language[17]) . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"8\">" . $Language[2] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[3] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[22] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[30] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[31] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[32] . "</b></td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><b>" . $Language[6] . "</b></td>\r\n\t\t</tr>\r\n\t\t" . $List . "\r\n\t</table>";
} else {
    echo $List;
}
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_149($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}
// DEAD CODE: function_321() is never called. XML parser function to extract values by tag name.
function function_321($inXmlset, $needle)
{
    $themeId = "";
    $themeName = xml_parser_create();
    xml_parser_set_option($themeName, XML_OPTION_SKIP_WHITE, 0);
    xml_parse_into_struct($themeName, $inXmlset, $themeDir);
    xml_parser_free($themeName);
    for ($i = 0; $i < count($themeDir); $i++) {
        if ($themeDir[$i]["tag"] == strtoupper($needle)) {
            $themeId = $themeDir[$i]["value"];
        }
    }
    return $themeId;
}
function function_320()
{
    $themeActive = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_themes ORDER BY isdefault, name");
    while ($T = mysqli_fetch_assoc($query)) {
        $themeActive[] = $T["name"];
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_cache` VALUES ('ts_themes', '" . implode(",", $themeActive) . "', " . time() . ")");
}

?>