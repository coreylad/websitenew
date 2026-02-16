<?php
declare(strict_types=1);

checkStaffAuthentication();

try {
    $pdo = $GLOBALS["DatabaseConnect"];
    
    $Language = file("languages/" . getStaffLanguage() . "/manage_faq.lang");
    $Message = "";
    $Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
    
    $stmt = $pdo->prepare("SELECT `content` FROM `ts_config` WHERE `configname` = ?");
    $stmt->execute(['MAIN']);
    $Result = $stmt->fetch(PDO::FETCH_ASSOC);
    $MAIN = unserialize($Result["content"]);
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            throw new Exception('CSRF token validation failed');
        }
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $Act == "save_order") {
        $stmt = $pdo->prepare("UPDATE ts_faq SET disporder = ? WHERE id = ?");
        foreach ($_POST["order"] as $_id => $_sort) {
            $stmt->execute([intval($_sort), intval($_id)]);
        }
    }
    if ($Act == "delete_category" && $id) {
        $stmt = $pdo->prepare("SELECT name FROM ts_faq WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            $Result = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = $Result["name"];
            
            $stmt = $pdo->prepare("DELETE FROM ts_faq WHERE id = ? OR pid = ?");
            $stmt->execute([$id, $id]);
            
            if ($stmt->rowCount() > 0) {
                $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), htmlspecialchars($_SESSION["ADMIN_USERNAME"], ENT_QUOTES, 'UTF-8')], $Language[20]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
            }
        }
    }
    if ($Act == "delete_child" && $id) {
        $stmt = $pdo->prepare("SELECT name FROM ts_faq WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            $Result = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = $Result["name"];
            
            $stmt = $pdo->prepare("DELETE FROM ts_faq WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), htmlspecialchars($_SESSION["ADMIN_USERNAME"], ENT_QUOTES, 'UTF-8')], $Language[23]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
            }
        }
    }
    if ($Act == "edit_category" && $id) {
        $stmt = $pdo->prepare("SELECT name, disporder FROM ts_faq WHERE id = ? AND type = '1'");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            $FAQ = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = $FAQ["name"];
            $disporder = $FAQ["disporder"];
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                $name = trim($_POST["name"]);
                $disporder = intval($_POST["disporder"]);
                if ($name) {
                    $stmt = $pdo->prepare("UPDATE ts_faq SET name = ?, disporder = ? WHERE id = ?");
                    $stmt->execute([$name, $disporder, $id]);
                    $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), htmlspecialchars($_SESSION["ADMIN_USERNAME"], ENT_QUOTES, 'UTF-8')], $Language[21]);
                    logStaffAction($Message);
                    $Message = showAlertError($Message);
                    $Done = true;
                } else {
                    $Message = showAlertError($Language[3]);
                }
            }
            if (!isset($Done)) {
                $csrf_field = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '" />';
                echo "\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = edit_category&$id = " . intval($id) . "\">\r\n\t\t\t" . $csrf_field . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($Language[4], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[12], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\" $size = \"40\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[10], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"10\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"reset\" $value = \"" . htmlspecialchars($Language[9], ENT_QUOTES, 'UTF-8') . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    }
    if ($Act == "new_category") {
        $name = "";
        $disporder = "";
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $name = trim($_POST["name"]);
            $disporder = trim($_POST["disporder"]);
            if ($name) {
                $stmt = $pdo->prepare("INSERT INTO ts_faq (type, name, disporder) VALUES ('1', ?, ?)");
                $stmt->execute([$name, $disporder]);
                if ($stmt->rowCount() > 0) {
                    $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), htmlspecialchars($_SESSION["ADMIN_USERNAME"], ENT_QUOTES, 'UTF-8')], $Language[22]);
                    logStaffAction($Message);
                    $Message = showAlertError($Message);
                    $Done = true;
                }
            } else {
                $Message = showAlertError($Language[3]);
            }
        }
        if (!isset($Done)) {
            $csrf_field = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '" />';
            echo "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = new_category\">\r\n\t\t" . $csrf_field . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($Language[7], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[12], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\" $size = \"99\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[10], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"10\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"reset\" $value = \"" . htmlspecialchars($Language[9], ENT_QUOTES, 'UTF-8') . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
        }
    }
    if ($Act == "new_child" && $id) {
        $stmt = $pdo->prepare("SELECT * FROM ts_faq WHERE id = ? AND type = '1'");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            $name = "";
            $pid = $id;
            $disporder = "";
            $description = "";
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                $name = trim($_POST["name"]);
                $pid = intval($_POST["pid"]);
                $disporder = trim($_POST["disporder"]);
                $description = trim($_POST["description"]);
                if ($name && $pid && $description) {
                    $stmt = $pdo->prepare("INSERT INTO ts_faq (type, name, pid, disporder, description) VALUES ('2', ?, ?, ?, ?)");
                    $stmt->execute([$name, $pid, $disporder, $description]);
                    if ($stmt->rowCount() > 0) {
                        $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), htmlspecialchars($_SESSION["ADMIN_USERNAME"], ENT_QUOTES, 'UTF-8')], $Language[25]);
                        logStaffAction($Message);
                        $Message = showAlertError($Message);
                        $Done = true;
                    }
                } else {
                    $Message = showAlertError($Language[3]);
                }
            }
            if (!isset($Done)) {
                $showcategories = "<select $name = \"pid\">";
                $stmt = $pdo->prepare("SELECT id, name FROM ts_faq WHERE type = '1' ORDER by disporder ASC");
                $stmt->execute();
                while ($cats = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $showcategories .= "<option $value = \"" . intval($cats["id"]) . "\"" . ($pid == $cats["id"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($cats["name"], ENT_QUOTES, 'UTF-8') . "</option>";
                }
                $showcategories .= "</select>";
                $csrf_field = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '" />';
                echo loadTinyMCEEditor() . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = new_child&$id = " . intval($id) . "\">\r\n\t\t\t" . $csrf_field . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($Language[6], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[12], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . htmlspecialchars($Language[13], ENT_QUOTES, 'UTF-8') . "<br /><small>" . htmlspecialchars($Language[27], ENT_QUOTES, 'UTF-8') . "</small></td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"description\" $id = \"description\" $style = \"width: 100%; height: 100px;\">" . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('description');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[26], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $showcategories . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[10], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"10\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"reset\" $value = \"" . htmlspecialchars($Language[9], ENT_QUOTES, 'UTF-8') . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    }
    if ($Act == "edit_child" && $id) {
        $stmt = $pdo->prepare("SELECT name, pid, disporder, description FROM ts_faq WHERE id = ? AND type = '2'");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            $FAQ = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = $FAQ["name"];
            $pid = $FAQ["pid"];
            $disporder = $FAQ["disporder"];
            $description = $FAQ["description"];
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                $name = trim($_POST["name"]);
                $pid = intval($_POST["pid"]);
                $disporder = trim($_POST["disporder"]);
                $description = trim($_POST["description"]);
                if ($name && $pid && $description) {
                    $stmt = $pdo->prepare("UPDATE ts_faq SET name = ?, pid = ?, disporder = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $pid, $disporder, $description, $id]);
                    $Message = str_replace(["{1}", "{2}"], [htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), htmlspecialchars($_SESSION["ADMIN_USERNAME"], ENT_QUOTES, 'UTF-8')], $Language[24]);
                    logStaffAction($Message);
                    $Message = showAlertError($Message);
                    $Done = true;
                } else {
                    $Message = showAlertError($Language[3]);
                }
            }
            if (!isset($Done)) {
                $showcategories = "<select $name = \"pid\">";
                $stmt = $pdo->prepare("SELECT id, name FROM ts_faq WHERE type = '1' ORDER by disporder ASC");
                $stmt->execute();
                while ($cats = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $showcategories .= "<option $value = \"" . intval($cats["id"]) . "\"" . ($pid == $cats["id"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($cats["name"], ENT_QUOTES, 'UTF-8') . "</option>";
                }
                $showcategories .= "</select>";
                $csrf_field = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '" />';
                echo loadTinyMCEEditor() . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = edit_child&$id = " . intval($id) . "\">\r\n\t\t\t" . $csrf_field . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($Language[18], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[12], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . htmlspecialchars($Language[13], ENT_QUOTES, 'UTF-8') . "<br /><small>" . htmlspecialchars($Language[27], ENT_QUOTES, 'UTF-8') . "</small></td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"description\" $id = \"description\" $style = \"width: 100%; height: 100px;\">" . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('description');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[26], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $showcategories . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . htmlspecialchars($Language[10], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"10\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"reset\" $value = \"" . htmlspecialchars($Language[9], ENT_QUOTES, 'UTF-8') . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
            }
        }
    }
    $FAQSubCats = [];
    $stmt = $pdo->prepare("SELECT id, name, pid, disporder FROM ts_faq WHERE type = '2' ORDER BY disporder ASC");
    $stmt->execute();
    while ($FSC = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $FAQSubCats[$FSC["pid"]][] = "\r\n\t<table>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"1%\" $valign = \"top\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_faq&amp;$act = edit_child&amp;$id = " . intval($FSC["id"]) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . htmlspecialchars(trim($Language[18]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[18]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"1%\" $valign = \"top\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_faq&amp;$act = delete_child&amp;$id = " . intval($FSC["id"]) . "\" $onclick = \"return confirm('" . htmlspecialchars(trim($Language[19]), ENT_QUOTES, 'UTF-8') . "\\n\\n" . htmlspecialchars(trim($Language[17]), ENT_QUOTES, 'UTF-8') . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . htmlspecialchars(trim($Language[19]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[19]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"88%\">\r\n\t\t\t\t" . htmlspecialchars(trim($FSC["name"]), ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"10%\" $align = \"right\">\r\n\t\t\t\t<input $type = \"text\" $size = \"5\" $value = \"" . intval($FSC["disporder"]) . "\" $name = \"order[" . intval($FSC["id"]) . "]\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t";
    }
    $Output = [];
    $stmt = $pdo->prepare("SELECT id, name, disporder FROM ts_faq WHERE type = '1' ORDER BY disporder ASC");
    $stmt->execute();
    if (0 < $stmt->rowCount()) {
        while ($FC = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $csrf_field = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '" />';
            $Output[] = "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = save_order\" $name = \"sort_order\">\r\n\t\t" . $csrf_field . "\r\n\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $align = \"center\" $width = \"500\" $style = \"border-collapse:separate\" class=\"tborder\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\">\r\n\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=manage_faq&amp;$act = new_child&amp;$id = " . intval($FC["id"]) . "\"><img $src = \"images/tool_new.png\" $alt = \"" . htmlspecialchars(trim($Language[6]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[6]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_faq&amp;$act = edit_category&amp;$id = " . intval($FC["id"]) . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . htmlspecialchars(trim($Language[4]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[4]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_faq&amp;$act = delete_category&amp;$id = " . intval($FC["id"]) . "\" $onclick = \"return confirm('" . htmlspecialchars(trim($Language[5]), ENT_QUOTES, 'UTF-8') . "\\n\\n" . htmlspecialchars(trim($Language[14]), ENT_QUOTES, 'UTF-8') . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . htmlspecialchars(trim($Language[5]), ENT_QUOTES, 'UTF-8') . "\" $title = \"" . htmlspecialchars(trim($Language[5]), ENT_QUOTES, 'UTF-8') . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t\t</span>\r\n\t\t\t\t\t" . htmlspecialchars($FC["name"], ENT_QUOTES, 'UTF-8') . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . (isset($FAQSubCats[$FC["id"]]) ? implode(" ", $FAQSubCats[$FC["id"]]) : "&nbsp;" . htmlspecialchars($Language[15], ENT_QUOTES, 'UTF-8')) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t" . (isset($FAQSubCats[$FC["id"]]) ? "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" $align = \"right\"><input $type = \"submit\" $value = \"" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "\" /> <input $type = \"reset\" $value = \"" . htmlspecialchars($Language[9], ENT_QUOTES, 'UTF-8') . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t" : "") . "\r\n\t\t</table>\r\n\t\t</form>";
        }
    } else {
        $Output[] = showAlertError(str_replace("{1}", "index.php?do=manage_faq&amp;$act = new_category", $Language[16]));
    }
    echo "\r\n" . showAlertMessage("<a $href = \"index.php?do=manage_faq&$act = new_category\">" . htmlspecialchars($Language[7], ENT_QUOTES, 'UTF-8') . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\" $align = \"center\" $width = \"100%\" $style = \"border-collapse:separate\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $valign = \"top\">";
    for ($i = 0; $i <= count($Output); $i++) {
        if ($i && $i % 3 == 0) {
            echo "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $valign = \"top\">";
        }
        if (isset($Output[$i]) && $Output[$i] != "") {
            echo $Output[$i];
        }
    }
    echo "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
    
} catch (Exception $e) {
    error_log("FAQ Management Error: " . $e->getMessage());
    echo showAlertError("An error occurred: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function loadTinyMCEEditor(int $type = 1, string $mode = "textareas", string $elements = ""): string
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $editorContent = ob_get_contents();
    ob_end_clean();
    return $editorContent;
}

function getStaffLanguage(): string
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}

function checkStaffAuthentication(): void
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}

function redirectTo(string $url): void
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "\" />\r\n\t\t</noscript>";
    }
    exit;
}

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}

function logStaffAction(string $log): void
{
    try {
        $pdo = $GLOBALS["DatabaseConnect"];
        $stmt = $pdo->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION["ADMIN_ID"], time(), $log]);
    } catch (Exception $e) {
        error_log("Failed to log staff action: " . $e->getMessage());
    }
}

function showAlertMessage(string $message = ""): string
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>