<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language with new helper
$Language = loadStaffLanguage('manage_torrent_categories');

// Get and sanitize input parameters with null coalescing
$Act = trim($_GET['act'] ?? $_POST['act'] ?? '');
$Cid = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$Message = '';

// Handle delete action
if ($Act === 'delete' && $Cid) {
    try {
        $result = $TSDatabase->query(
            'SELECT name, type FROM categories WHERE id = ? LIMIT 1',
            [$Cid]
        );
        
        if ($result && ($Category = $result->fetch(PDO::FETCH_ASSOC))) {
            $TSDatabase->query('DELETE FROM categories WHERE id = ?', [$Cid]);
            
            // If this is a parent category, delete all subcategories
            if ($Category['type'] === 'c') {
                $TSDatabase->query('DELETE FROM categories WHERE pid = ?', [$Cid]);
            }
            
            $SysMsg = str_replace(['{1}', '{2}'], [$Category['name'], $_SESSION['ADMIN_USERNAME'] ?? 'Unknown'], $Language[10] ?? 'Category {1} deleted by {2}');
            logStaffActionModern($SysMsg);
            rebuildCategoryCache();
        }
    } catch (Exception $e) {
        error_log('Delete category error: ' . $e->getMessage());
    }
}
// Handle edit action
if ($Act === 'edit' && $Cid) {
    try {
        $result = $TSDatabase->query('SELECT * FROM categories WHERE id = ? LIMIT 1', [$Cid]);
        
        if ($result && ($Category = $result->fetch(PDO::FETCH_ASSOC))) {
            if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
                // Validate CSRF token
                if (!validateFormToken($_POST['form_token'] ?? '')) {
                    $Message = showAlertErrorModern($Language[30] ?? 'Invalid form token. Please try again.');
                } else {
                    $Changes = [];
                    
                    // Handle usergroups for canview permission
                    if (isset($_POST['usergroups']) && is_array($_POST['usergroups'])) {
                        if (in_array('[ALL]', $_POST['usergroups'], true) || empty($_POST['usergroups'][0])) {
                            $_POST['canview'] = '[ALL]';
                        } else {
                            $_POST['canview'] = implode(',', array_map('trim', $_POST['usergroups']));
                        }
                        unset($_POST['usergroups']);
                    }
                    
                    // Handle usergroups2 for candownload permission
                    if (isset($_POST['usergroups2']) && is_array($_POST['usergroups2'])) {
                        if (in_array('[ALL]', $_POST['usergroups2'], true) || empty($_POST['usergroups2'][0])) {
                            $_POST['candownload'] = '[ALL]';
                        } else {
                            $_POST['candownload'] = implode(',', array_map('trim', $_POST['usergroups2']));
                        }
                        unset($_POST['usergroups2']);
                    }
                    
                    // Build update array for allowed fields
                    $allowedFields = ['name', 'image', 'cat_desc', 'pid', 'canview', 'candownload', 'type'];
                    $updateFields = [];
                    $updateValues = [];
                    
                    foreach ($_POST as $name => $value) {
                        if (in_array($name, $allowedFields, true) && $name !== 'form_token' && $name !== 'act' && $name !== 'id') {
                            $updateFields[] = $name . ' = ?';
                            $updateValues[] = is_string($value) ? trim($value) : $value;
                        }
                    }
                    
                    if (!empty($updateFields)) {
                        $updateValues[] = $Cid;
                        $sql = 'UPDATE categories SET ' . implode(', ', $updateFields) . ' WHERE id = ?';
                        $TSDatabase->query($sql, $updateValues);
                        
                        rebuildCategoryCache();
                        $Message = str_replace(['{1}', '{2}'], [$Category['name'], $_SESSION['ADMIN_USERNAME'] ?? 'Unknown'], $Language[11] ?? 'Category {1} updated by {2}');
                        logStaffActionModern($Message);
                        $Message = showAlertSuccessModern($Message);
                        $Done = true;
                    }
                }
            }
            
            if (!isset($Done)) {
                $Extra = '';
                
                // If subcategory, show parent category dropdown
                if ($Category['type'] === 's') {
                    $Selectbox = '<select name="pid">';
                    $result = $TSDatabase->query('SELECT id, name FROM categories WHERE type = ? ORDER BY name ASC', ['c']);
                    
                    while ($result && ($Cats = $result->fetch(PDO::FETCH_ASSOC))) {
                        $selected = ($Cats['id'] == $Category['pid']) ? ' selected="selected"' : '';
                        $Selectbox .= '<option value="' . escape_attr((string)$Cats['id']) . '"' . $selected . '>' . 
                                      escape_html($Cats['name']) . '</option>';
                    }
                    $Selectbox .= '</select>';
                    $Extra = "\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"alt1\">" . escape_html($Language[17] ?? 'Parent Category:') . 
                             "</td>\n\t\t\t\t\t<td class=\"alt1\">" . $Selectbox . "</td>\n\t\t\t\t</tr>\n\t\t\t\t";
                }
                
                // Build canview usergroups checkboxes
                $canview = explode(',', (string)$Category['canview']);
                $sgids = buildUsergroupCheckboxes('usergroups', $canview, $Language);
                
                // Build candownload usergroups checkboxes
                $candownload = explode(',', (string)$Category['candownload']);
                $sgids2 = buildUsergroupCheckboxes('usergroups2', $candownload, $Language);
                
                echo "\n\t\t\t\n\t\t\t<form method=\"post\" action=\"" . escape_attr('index.php?do=manage_torrent_categories&act=edit&id=' . $Cid) . "\">\n\t\t\t" . 
                     getFormTokenField() . "\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\n\t\t\t\t\t\t" . 
                     escape_html($Language[6] ?? 'Edit Category') . "\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"alt1\">" . 
                     escape_html($Language[13] ?? 'Name:') . "</td>\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"name\" value=\"" . 
                     escape_attr($Category['name']) . "\" size=\"40\" /></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"alt1\">" . 
                     escape_html($Language[15] ?? 'Description:') . "</td>\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"cat_desc\" value=\"" . 
                     escape_attr($Category['cat_desc']) . "\" size=\"40\" /></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"alt1\">" . 
                     escape_html($Language[14] ?? 'Image:') . "</td>\n\t\t\t\t\t<td class=\"alt1\">" . buildCategoryImageDropdown($Category['image']) . 
                     "</td>\n\t\t\t\t</tr>\n\t\t\t\t" . $Extra . "\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">" . 
                     escape_html($Language[27] ?? 'Who Can View:') . "</td>\n\t\t\t\t\t<td class=\"alt1\">\n\t\t\t\t\t\t" . $sgids . 
                     "\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">" . 
                     escape_html($Language[29] ?? 'Who Can Download:') . "</td>\n\t\t\t\t\t<td class=\"alt1\">\n\t\t\t\t\t\t" . $sgids2 . 
                     "\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"tcat2\"></td>\n\t\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . 
                     escape_attr($Language[20] ?? 'Submit') . "\" /> <input type=\"reset\" value=\"" . 
                     escape_attr($Language[21] ?? 'Reset') . "\" /></td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t\t</form>";
            }
        }
    } catch (Exception $e) {
        error_log('Edit category error: ' . $e->getMessage());
        $Message = showAlertErrorModern($Language[31] ?? 'Failed to edit category');
    }
}
// Handle new category action
if ($Act === 'new') {
    $name = '';
    $image = '';
    $cat_desc = '';
    $type = '';
    $pid = '';
    $Message = '';
    $canview = '[ALL]';
    $candownload = '[ALL]';
    
    if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
        // Validate CSRF token
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            $Message = showAlertErrorModern($Language[30] ?? 'Invalid form token. Please try again.');
        } else {
            $name = trim($_POST['name'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $cat_desc = trim($_POST['cat_desc'] ?? '');
            
            // Handle usergroups
            if (isset($_POST['usergroups']) && is_array($_POST['usergroups'])) {
                $canview = implode(',', array_map('trim', $_POST['usergroups']));
            }
            
            if (isset($_POST['usergroups2']) && is_array($_POST['usergroups2'])) {
                $candownload = implode(',', array_map('trim', $_POST['usergroups2']));
            }
            
            if (isset($_POST['pid']) && (int)$_POST['pid'] !== 0) {
                $pid = (int)$_POST['pid'];
                $type = 's';
            } else {
                $pid = 0;
                $type = 'c';
            }
            
            if (empty($name) || empty($image)) {
                $Message = showAlertErrorModern($Language[24] ?? 'Name and Image are required');
            } else {
                try {
                    $TSDatabase->query(
                        'INSERT INTO categories (name, image, cat_desc, type, pid, canview, candownload) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)',
                        [$name, $image, $cat_desc, $type, $pid, $canview, $candownload]
                    );
                    
                    rebuildCategoryCache();
                    $Message = str_replace(['{1}', '{2}'], [$name, $_SESSION['ADMIN_USERNAME'] ?? 'Unknown'], $Language[12] ?? 'Category {1} created by {2}');
                    logStaffActionModern($Message);
                    $Message = showAlertSuccessModern($Message);
                    $Done = true;
                } catch (Exception $e) {
                    error_log('Create category error: ' . $e->getMessage());
                    $Message = showAlertErrorModern($Language[32] ?? 'Failed to create category');
                }
            }
        }
    }
    
    if (!isset($Done)) {
        $id = (int)($_GET['id'] ?? 0);
        $Extra = '';
        
        if ($id) {
            $Head = $Language[26] ?? 'New Subcategory';
            $Selectbox = '<select name="pid"><option value="0"></option>';
            
            try {
                $result = $TSDatabase->query('SELECT id, name FROM categories WHERE type = ? ORDER BY name ASC', ['c']);
                while ($result && ($Cats = $result->fetch(PDO::FETCH_ASSOC))) {
                    $selected = ($Cats['id'] == $id) ? ' selected="selected"' : '';
                    $Selectbox .= '<option value="' . escape_attr((string)$Cats['id']) . '"' . $selected . '>' . 
                                  escape_html($Cats['name']) . '</option>';
                }
            } catch (Exception $e) {
                error_log('Load categories error: ' . $e->getMessage());
            }
            
            $Selectbox .= '</select>';
            $Extra = "\n\t\t\t<tr>\n\t\t\t\t<td class=\"alt1\">" . escape_html($Language[17] ?? 'Parent Category:') . 
                     "</td>\n\t\t\t\t<td class=\"alt1\">" . $Selectbox . "</td>\n\t\t\t</tr>\n\t\t\t";
        } else {
            $Head = $Language[25] ?? 'New Category';
        }
        
        // Build usergroups checkboxes
        $canview = ['[ALL]'];
        $sgids = buildUsergroupCheckboxes('usergroups', $canview, $Language);
        
        $candownload = ['[ALL]'];
        $sgids2 = buildUsergroupCheckboxes('usergroups2', $candownload, $Language);
        
        echo "\n\t\t\n\t\t" . $Message . "\n\t\t<form method=\"post\" action=\"" . escape_attr('index.php?do=manage_torrent_categories&act=new&id=' . $id) . "\">\n\t\t" . 
             getFormTokenField() . "\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\n\t\t\t<tr>\n\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\n\t\t\t\t\t" . 
             escape_html($Head) . "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td class=\"alt1\">" . 
             escape_html($Language[13] ?? 'Name:') . "</td>\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"name\" value=\"" . 
             escape_attr($name) . "\" size=\"40\" /></td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td class=\"alt1\">" . 
             escape_html($Language[15] ?? 'Description:') . "</td>\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"cat_desc\" value=\"" . 
             escape_attr($cat_desc) . "\" size=\"40\" /></td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td class=\"alt1\">" . 
             escape_html($Language[14] ?? 'Image:') . "</td>\n\t\t\t\t<td class=\"alt1\">" . buildCategoryImageDropdown($image) . 
             "</td>\n\t\t\t</tr>\n\t\t\t" . $Extra . "\n\t\t\t<tr>\n\t\t\t\t<td class=\"alt1\" valign=\"top\">" . 
             escape_html($Language[27] ?? 'Who Can View:') . "</td>\n\t\t\t\t<td class=\"alt1\">\n\t\t\t\t\t" . $sgids . 
             "\n\t\t\t\t</td>\n\t\t\t</tr>\t\n\t\t\t<tr>\n\t\t\t\t<td class=\"alt1\" valign=\"top\">" . 
             escape_html($Language[29] ?? 'Who Can Download:') . "</td>\n\t\t\t\t<td class=\"alt1\">\n\t\t\t\t\t" . $sgids2 . 
             "\n\t\t\t\t</td>\n\t\t\t</tr>\t\n\t\t\t<tr>\n\t\t\t\t<td class=\"tcat2\"></td>\n\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . 
             escape_attr($Language[20] ?? 'Submit') . "\" /> <input type=\"reset\" value=\"" . 
             escape_attr($Language[21] ?? 'Reset') . "\" /></td>\n\t\t\t</tr>\n\t\t</table>\n\t\t</form>";
    }
}
// Build category listing
$SubCategories = [];

try {
    $result = $TSDatabase->query('SELECT * FROM categories WHERE type = ? ORDER BY name ASC', ['s']);
    
    while ($result && ($SC = $result->fetch(PDO::FETCH_ASSOC))) {
        $SubCategories[$SC['pid']][] = "\n\t<!-- Sub Category -->\n\t<table>\n\t\t<tr>\n\t\t\t<td width=\"1%\">\n\t\t\t\t<a href=\"" . 
            escape_attr('index.php?do=manage_torrent_categories&amp;act=edit&amp;id=' . $SC['id']) . "\"><img src=\"images/tool_edit.png\" alt=\"" . 
            escape_attr(trim($Language[3] ?? 'Edit')) . "\" title=\"" . escape_attr(trim($Language[3] ?? 'Edit')) . 
            "\" border=\"0\" style=\"vertical-align: middle;\" /></a>\n\t\t\t</td>\n\t\t\t<td width=\"1%\">\n\t\t\t\t<a href=\"" . 
            escape_attr('index.php?do=manage_torrent_categories&amp;act=delete&amp;id=' . $SC['id']) . "\" onclick=\"return confirm('" . 
            escape_js(trim($Language[4] ?? 'Delete') . ': ' . trim($SC['name']) . "\n\n" . trim($Language[5] ?? 'Are you sure?')) . 
            "');\"><img src=\"images/tool_delete.png\" alt=\"" . escape_attr(trim($Language[4] ?? 'Delete')) . "\" title=\"" . 
            escape_attr(trim($Language[4] ?? 'Delete')) . "\" border=\"0\" style=\"vertical-align: middle;\" /></a>\n\t\t\t</td>\n\t\t\t<td width=\"88%\">\n\t\t\t\t" . 
            escape_html(trim($SC['name'])) . "\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<!-- Sub Category -->\n\t";
    }
} catch (Exception $e) {
    error_log('Load subcategories error: ' . $e->getMessage());
}

$Output = [];

try {
    $result = $TSDatabase->query('SELECT * FROM categories WHERE type = ? ORDER BY name ASC', ['c']);
    
    while ($result && ($ST = $result->fetch(PDO::FETCH_ASSOC))) {
        $Output[] = "\n\t<!-- Category -->\n\t<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" align=\"center\" width=\"400\" style=\"border-collapse:separate\" class=\"tborder\">\n\t\t<tr>\n\t\t\t<td class=\"tcat\">\n\t\t\t\t<span style=\"float: right;\">\n\t\t\t\t\t<a href=\"" . 
            escape_attr('index.php?do=manage_torrent_categories&amp;act=new&amp;id=' . $ST['id']) . "\"><img src=\"images/tool_new.png\" alt=\"" . 
            escape_attr(trim($Language[18] ?? 'New Subcategory')) . "\" title=\"" . escape_attr(trim($Language[18] ?? 'New Subcategory')) . 
            "\" border=\"0\" style=\"vertical-align: middle;\" /></a> <a href=\"" . 
            escape_attr('index.php?do=manage_torrent_categories&amp;act=edit&amp;id=' . $ST['id']) . "\"><img src=\"images/tool_edit.png\" alt=\"" . 
            escape_attr(trim($Language[6] ?? 'Edit Category')) . "\" title=\"" . escape_attr(trim($Language[6] ?? 'Edit Category')) . 
            "\" border=\"0\" style=\"vertical-align: middle;\" /></a> <a href=\"" . 
            escape_attr('index.php?do=manage_torrent_categories&amp;act=delete&amp;id=' . $ST['id']) . "\" onclick=\"return confirm('" . 
            escape_js(trim($Language[7] ?? 'Delete Category') . ': ' . trim($ST['name']) . "\n\n" . trim($Language[8] ?? 'This will delete all subcategories!')) . 
            "');\"><img src=\"images/tool_delete.png\" alt=\"" . escape_attr(trim($Language[7] ?? 'Delete Category')) . "\" title=\"" . 
            escape_attr(trim($Language[7] ?? 'Delete Category')) . "\" border=\"0\" style=\"vertical-align: middle;\" /></a>\n\t\t\t\t</span>" . 
            escape_html($ST['name']) . "\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td class=\"alt1\">\n\t\t\t\t" . 
            (isset($SubCategories[$ST['id']]) ? implode(' ', $SubCategories[$ST['id']]) : '&nbsp;' . escape_html($Language[9] ?? 'No subcategories')) . 
            "\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<!-- Category -->\n\t";
    }
} catch (Exception $e) {
    error_log('Load categories error: ' . $e->getMessage());
}

$List = '';
$Count = 0;

foreach ($Output as $Category) {
    if ($Count % 2 === 0) {
        $List .= '</td><td valign="top">';
    }
    if ($Count % 6 === 0) {
        $List .= '</td></tr><tr><td valign="top">';
    }
    $List .= $Category;
    $Count++;
}

echo "\n" . showAlertInfoModern('<a href="' . escape_attr('index.php?do=manage_torrent_categories&amp;act=new') . '">' . 
     escape_html(trim($Language[25] ?? 'New Category')) . '</a>') . "\n" . $Message . 
     "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\n\t<tr>\n\t\t<td class=\"tcat\" align=\"center\">" . 
     escape_html($Language[2] ?? 'Manage Torrent Categories') . "</td>\n\t</tr>\n</table>" . $List;

/**
 * Build usergroup checkboxes for permissions
 * 
 * @param string $fieldName Field name for checkboxes
 * @param array $selectedGroups Currently selected groups
 * @param array $Language Language array
 * @return string HTML for checkboxes
 */
function buildUsergroupCheckboxes(string $fieldName, array $selectedGroups, array $Language): string
{
    global $TSDatabase;
    
    $output = "\n\t\t\t<fieldset>\n\t\t\t\t<legend>Select Usergroup(s)</legend>\n\t\t\t\t\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">\n\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t<td class=\"none\"><input type=\"checkbox\" name=\"" . 
              escape_attr($fieldName) . "[]\" value=\"[ALL]\"" . 
              (in_array('[ALL]', $selectedGroups, true) ? ' checked="checked"' : '') . " /></td><td class=\"none\">" . 
              escape_html($Language[28] ?? 'All Groups') . '</td>';
    
    try {
        $result = $TSDatabase->query('SELECT gid, title, namestyle FROM usergroups ORDER BY title ASC');
        $count = 1;
        
        while ($result && ($gid = $result->fetch(PDO::FETCH_ASSOC))) {
            if ($count % 4 === 1) {
                $output .= "</tr><tr>";
            }
            
            $checked = in_array((string)$gid['gid'], $selectedGroups, true) ? ' checked="checked"' : '';
            $output .= "\n\t\t\t\t<td class=\"none\"><input type=\"checkbox\" name=\"" . escape_attr($fieldName) . 
                       "[]\" value=\"" . escape_attr((string)$gid['gid']) . "\"" . $checked . " /></td>\n\t\t\t\t<td class=\"none\">" . 
                       str_replace('{username}', escape_html($gid['title']), $gid['namestyle']) . '</td>';
            $count++;
        }
    } catch (Exception $e) {
        error_log('Load usergroups error: ' . $e->getMessage());
    }
    
    $output .= "\n\t\t\t\t\t\t</tr>\n\t\t\t\t\t</table>\n\t\t\t</fieldset>";
    
    return $output;
}

/**
 * Build category image dropdown selector
 * 
 * @param string $selected Currently selected image
 * @return string HTML for dropdown
 */
function buildCategoryImageDropdown(string $selected = ''): string
{
    global $TSDatabase;
    
    try {
        $result = $TSDatabase->query('SELECT content FROM ts_config WHERE configname = ? LIMIT 1', ['MAIN']);
        
        if ($result && ($row = $result->fetch(PDO::FETCH_ASSOC))) {
            $configData = unserialize($row['content']);
            
            $imageDir = '';
            if (is_dir(__DIR__ . '/../../images/' . ($configData['table_cat'] ?? 'cats'))) {
                $imageDir = __DIR__ . '/../../images/' . ($configData['table_cat'] ?? 'cats') . '/';
            } elseif (is_dir(__DIR__ . '/../../pic/' . ($configData['table_cat'] ?? 'cats'))) {
                $imageDir = __DIR__ . '/../../pic/' . ($configData['table_cat'] ?? 'cats') . '/';
            }
            
            if ($imageDir && is_dir($imageDir)) {
                $files = scandir($imageDir);
                $dropdown = '<select name="image">';
                
                foreach ($files as $file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'gif', 'jpg', 'jpeg'], true)) {
                        $isSelected = ($selected === $file) ? ' selected="selected"' : '';
                        $dropdown .= '<option value="' . escape_attr($file) . '"' . $isSelected . '>' . 
                                     escape_html($file) . '</option>';
                    }
                }
                
                $dropdown .= '</select>';
                return $dropdown;
            }
        }
    } catch (Exception $e) {
        error_log('Build image dropdown error: ' . $e->getMessage());
    }
    
    return '<select name="image"></select>';
}

/**
 * Rebuild category cache file
 * Modernized version of function_153()
 * 
 * @return bool Success status
 */
function rebuildCategoryCache(): bool
{
    global $TSDatabase;
    
    try {
        $result = $TSDatabase->query('SELECT content FROM ts_config WHERE configname = ? LIMIT 1', ['MAIN']);
        
        if (!$result || !($configRow = $result->fetch(PDO::FETCH_ASSOC))) {
            return false;
        }
        
        $configData = unserialize($configRow['content']);
        
        // Get parent categories
        $catId = [];
        $result = $TSDatabase->query('SELECT * FROM categories WHERE type = ? ORDER BY name, id', ['c']);
        while ($result && ($cat = $result->fetch(PDO::FETCH_ASSOC))) {
            $catId[] = $cat;
        }
        
        // Get subcategories
        $catName = [];
        $result = $TSDatabase->query('SELECT * FROM categories WHERE type = ? ORDER BY name, id', ['s']);
        while ($result && ($cat = $result->fetch(PDO::FETCH_ASSOC))) {
            $catName[] = $cat;
        }
        
        $catSort = var_export($catId, true);
        $catDesc = var_export($catName, true);
        
        $cachePath = __DIR__ . '/../../' . ($configData['cache'] ?? 'cache') . '/categories.php';
        $cacheData = "<?php\n/** TS Generated Cache#7 - Do Not Alter\n * Cache Name: Categories\n * Generated: " . 
                     gmdate('r') . "\n*/\n\n";
        $cacheData .= "\$_categoriesC = " . $catSort . ";\n\n";
        $cacheData .= "\$_categoriesS = " . $catDesc . ";\n?>";
        
        file_put_contents($cachePath, $cacheData);
        
        return true;
    } catch (Exception $e) {
        error_log('Rebuild category cache error: ' . $e->getMessage());
        return false;
    }
}

?>

?>