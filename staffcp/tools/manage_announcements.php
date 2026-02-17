<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('manage_announcements');

// Initialize variables
$Message = '';
$Act = $_GET['act'] ?? $_POST['act'] ?? '';
$Act = is_string($Act) ? trim($Act) : '';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$subject = '';
$message = '';
$minclassread = ['1'];

// Get MAIN config
try {
    $result = $TSDatabase->query('SELECT content FROM ts_config WHERE configname = ?', ['MAIN']);
    if ($result && ($row = $result->fetch(PDO::FETCH_ASSOC))) {
        $MAIN = unserialize($row['content']);
    }
} catch (Exception $e) {
    error_log('Failed to load MAIN config: ' . $e->getMessage());
    $MAIN = [];
}

// Process form submission (POST)
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // Validate form token
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token. Please try again.');
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $minclassread = $_POST['usergroups'] ?? [];
        
        if (!is_array($minclassread)) {
            $minclassread = [];
        }
        
        if ($subject && $message && is_array($minclassread) && count($minclassread)) {
            $Work = implode(',', array_map('intval', $minclassread));
            
            try {
                if ($Act === 'new') {
                    // Insert new announcement
                    $TSDatabase->query(
                        'INSERT INTO announcements (subject, message, `by`, added, minclassread) VALUES (?, ?, ?, NOW(), ?)',
                        [$subject, $message, $_SESSION['ADMIN_USERNAME'], $Work]
                    );
                    
                    if ($TSDatabase->getConnection()->lastInsertId()) {
                        $SysMsg = str_replace(
                            ['{1}', '{2}'],
                            [$subject, $_SESSION['ADMIN_USERNAME']],
                            $Language[10] ?? 'Announcement {1} added by {2}'
                        );
                        logStaffActionModern($SysMsg);
                        $Message = showAlertSuccessModern($SysMsg);
                        
                        // Update users to mark announcement as unread
                        $TSDatabase->query(
                            'UPDATE users SET announce_read = ? WHERE usergroup IN (0, ' . $Work . ')',
                            ['no']
                        );
                        $Act = '';
                    }
                } elseif ($Act === 'edit' && $id) {
                    // Update existing announcement
                    $TSDatabase->query(
                        'UPDATE announcements SET subject = ?, message = ?, minclassread = ? WHERE id = ?',
                        [$subject, $message, $Work, $id]
                    );
                    
                    $SysMsg = str_replace(
                        ['{1}', '{2}'],
                        [$subject, $_SESSION['ADMIN_USERNAME']],
                        $Language[12] ?? 'Announcement {1} updated by {2}'
                    );
                    logStaffActionModern($SysMsg);
                    $Message = showAlertSuccessModern($SysMsg);
                    
                    // Update users to mark announcement as unread
                    $TSDatabase->query(
                        'UPDATE users SET announce_read = ? WHERE usergroup IN (0, ' . $Work . ')',
                        ['no']
                    );
                    $Act = '';
                }
            } catch (Exception $e) {
                error_log('Announcement operation error: ' . $e->getMessage());
                $Message = showAlertErrorModern('Failed to save announcement. Please try again.');
            }
        } else {
            $Message = showAlertErrorModern($Language[3] ?? 'All fields are required');
        }
    }
}

// Process delete action (GET)
if ($Act === 'delete' && $id) {
    // Validate form token for delete action
    if (!validateFormToken($_GET['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token. Please try again.');
        $Act = '';
    } else {
        try {
            $result = $TSDatabase->query('SELECT subject FROM announcements WHERE id = ?', [$id]);
            if ($result && ($Ann = $result->fetch(PDO::FETCH_ASSOC))) {
                $TSDatabase->query('DELETE FROM announcements WHERE id = ?', [$id]);
                
                $subject = $Ann['subject'];
                $SysMsg = str_replace(
                    ['{1}', '{2}'],
                    [$subject, $_SESSION['ADMIN_USERNAME']],
                    $Language[11] ?? 'Announcement {1} deleted by {2}'
                );
                logStaffActionModern($SysMsg);
                $Message = showAlertSuccessModern($SysMsg);
            }
            $Act = '';
        } catch (Exception $e) {
            error_log('Announcement delete error: ' . $e->getMessage());
            $Message = showAlertErrorModern('Failed to delete announcement. Please try again.');
        }
    }
}

/**
 * Render usergroup checkboxes
 */
function renderUserGroupCheckboxes($selected): string
{
    global $TSDatabase;
    
    if (!is_array($selected) && preg_match('@,@', $selected)) {
        $selected = explode(',', $selected);
    }
    
    try {
        // Get current user permissions
        $result = $TSDatabase->query(
            'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
             FROM users u 
             LEFT JOIN usergroups g ON u.usergroup = g.gid 
             WHERE u.id = ? 
             LIMIT 1',
            [$_SESSION['ADMIN_ID']]
        );
        
        $currentUserPerms = $result ? $result->fetch(PDO::FETCH_ASSOC) : [];
        
        $count = 0;
        $userGroupsHtml = "\n\t<table>\n\t\t<tr>\t";
        
        // Get all usergroups
        $result = $TSDatabase->query(
            'SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle 
             FROM usergroups 
             WHERE isbanned = ? 
             ORDER BY disporder ASC',
            ['no']
        );
        
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                // Check if current user has permission to assign this group
                $canAssign = true;
                if ($currentUserPerms) {
                    if ($row['cansettingspanel'] === 'yes' && $currentUserPerms['cansettingspanel'] !== 'yes') {
                        $canAssign = false;
                    }
                    if ($row['canstaffpanel'] === 'yes' && $currentUserPerms['canstaffpanel'] !== 'yes') {
                        $canAssign = false;
                    }
                    if ($row['issupermod'] === 'yes' && $currentUserPerms['issupermod'] !== 'yes') {
                        $canAssign = false;
                    }
                }
                
                if ($canAssign) {
                    if ($count && $count % 8 === 0) {
                        $userGroupsHtml .= "</tr><tr>";
                    }
                    
                    $checked = (is_array($selected) && in_array((string)$row['gid'], $selected, true)) ? ' checked="checked"' : '';
                    $userGroupsHtml .= '<td><input type="checkbox" name="usergroups[]" value="' . escape_attr((string)$row['gid']) . '"' . $checked . ' /></td>';
                    $userGroupsHtml .= '<td>' . escape_html(str_replace('{username}', $row['title'], $row['namestyle'])) . '</td>';
                    $count++;
                }
            }
        }
        
        $userGroupsHtml .= "</tr></table>";
        return $userGroupsHtml;
        
    } catch (Exception $e) {
        error_log('Failed to render usergroup checkboxes: ' . $e->getMessage());
        return '<p>Error loading usergroups</p>';
    }
}

/**
 * Load TinyMCE editor
 */
function loadTinyMCEEditor(int $type = 1, string $mode = 'textareas', string $elements = ''): string
{
    define('EDITOR_TYPE', $type);
    define('TINYMCE_MODE', $mode);
    define('TINYMCE_ELEMENTS', $elements);
    define('WORKPATH', './../scripts/');
    define('TINYMCE_EMOTIONS_URL', './../tinymce_emotions.php');
    
    ob_start();
    include './../tinymce.php';
    $editorContent = ob_get_contents();
    ob_end_clean();
    
    return $editorContent;
}

/**
 * Format timestamp
 */
function formatTimestamp(string $timestamp = ''): string
{
    $dateFormatPattern = 'm-d-Y h:i A';
    
    if (empty($timestamp)) {
        $timestamp = (string)time();
    } elseif (strstr($timestamp, '-')) {
        $timestamp = (string)strtotime($timestamp);
    }
    
    return date($dateFormatPattern, (int)$timestamp);
}

/**
 * Apply username style
 */
function applyUsernameStyle(string $username, string $namestyle): string
{
    return str_replace('{username}', $username, $namestyle);
}

/**
 * Build pagination links
 */
function buildPaginationLinks(int $perpage, int $results, string $address): array
{
    if ($results < $perpage) {
        return ['', ''];
    }
    
    $queryResult = $results ? (int)ceil($results / $perpage) : 0;
    $pagenumber = (int)($_GET['page'] ?? $_POST['page'] ?? 1);
    
    if ($pagenumber < 1) {
        $pagenumber = 1;
    } elseif ($pagenumber > $queryResult) {
        $pagenumber = $queryResult;
    }
    
    $limitOffset = ($pagenumber - 1) * $perpage;
    
    if ($results <= $perpage) {
        return ['', 'LIMIT ' . $limitOffset . ', ' . $perpage];
    }
    
    $total = number_format($results);
    $prevLink = '';
    $nextLink = '';
    
    if ($pagenumber > 1) {
        $previousPage = $pagenumber - 1;
        $prevLink = '<li><a class="smalltext" href="' . escape_attr($address . 'page=' . $previousPage) . '">&lt;</a></li>';
    }
    
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextLink = '<li><a class="smalltext" href="' . escape_attr($address . 'page=' . $nextPageNumber) . '">&gt;</a></li>';
    }
    
    $paginationLinks = '
    <table cellpadding="0" cellspacing="0" border="0" class="mainTableNoBorder">
        <tr>
            <td style="padding: 0px 0px 1px 0px;">
                <div style="float: left;" id="navcontainer_f">
                    <ul>
                        <li>' . $pagenumber . ' - ' . $queryResult . '</li>
                        ' . $prevLink . '
                        ' . $nextLink . '
                    </ul>
                </div>
            </td>
        </tr>
    </table>';
    
    return [$paginationLinks, 'LIMIT ' . $limitOffset . ', ' . $perpage];
}

// Display new announcement form
if ($Act === 'new') {
    echo loadTinyMCEEditor();
    ?>
    <form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=manage_announcements&act=new'); ?>" method="post">
    <?php echo getFormTokenField(); ?>
    <?php echo $Message; ?>
    <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
        <tr>
            <td class="tcat" colspan="2" align="center">
                <?php echo escape_html($Language[2] ?? 'Announcements'); ?> - <?php echo escape_html($Language[6] ?? 'Add New'); ?>
            </td>
        </tr>
        <tr>
            <td class="alt1"><?php echo escape_html($Language[7] ?? 'Subject'); ?></td>
            <td class="alt1">
                <input type="text" name="subject" value="<?php echo escape_attr($subject); ?>" style="width: 99%;" dir="ltr" tabindex="1" />
            </td>
        </tr>
        <tr>
            <td class="alt1" valign="top"><?php echo escape_html($Language[8] ?? 'Message'); ?></td>
            <td class="alt1">
                <textarea name="message" id="message" style="width: 100%; height: 100px;" dir="ltr" tabindex="1"><?php echo escape_html($message); ?></textarea>
                <p><a href="javascript:toggleEditor('message');"><img src="images/tool_refresh.png" border="0" alt="Toggle Editor" /></a></p>
            </td>
        </tr>
        <tr>
            <td class="alt1" valign="top"><?php echo escape_html($Language[9] ?? 'Usergroups'); ?></td>
            <td class="alt1"><?php echo renderUserGroupCheckboxes($minclassread); ?></td>
        </tr>
        <tr>
            <td class="tcat2"></td>
            <td class="tcat2">
                <input type="submit" value="<?php echo escape_attr($Language[14] ?? 'Submit'); ?>" /> 
                <input type="reset" value="<?php echo escape_attr($Language[15] ?? 'Reset'); ?>" />
            </td>
        </tr>
    </table>
    </form>
    <?php
}

// Display edit announcement form
if ($Act === 'edit' && $id) {
    try {
        $result = $TSDatabase->query(
            'SELECT subject, message, minclassread FROM announcements WHERE id = ?',
            [$id]
        );
        
        if ($result && ($Ann = $result->fetch(PDO::FETCH_ASSOC))) {
            echo loadTinyMCEEditor();
            ?>
            <form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=manage_announcements&act=edit&id=' . $id); ?>" method="post">
            <?php echo getFormTokenField(); ?>
            <?php echo $Message; ?>
            <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
                <tr>
                    <td class="tcat" colspan="2" align="center">
                        <?php echo escape_html($Language[2] ?? 'Announcements'); ?> - <?php echo escape_html($Language[4] ?? 'Edit'); ?>
                    </td>
                </tr>
                <tr>
                    <td class="alt1"><?php echo escape_html($Language[7] ?? 'Subject'); ?></td>
                    <td class="alt1">
                        <input type="text" name="subject" value="<?php echo escape_attr($Ann['subject']); ?>" style="width: 99%;" dir="ltr" tabindex="1" />
                    </td>
                </tr>
                <tr>
                    <td class="alt1" valign="top"><?php echo escape_html($Language[8] ?? 'Message'); ?></td>
                    <td class="alt1">
                        <textarea name="message" id="f_offlinemsg" style="width: 100%; height: 100px;" dir="ltr" tabindex="1"><?php echo escape_html($Ann['message']); ?></textarea>
                        <p><a href="javascript:toggleEditor('f_offlinemsg');"><img src="images/tool_refresh.png" border="0" alt="Toggle Editor" /></a></p>
                    </td>
                </tr>
                <tr>
                    <td class="alt1" valign="top"><?php echo escape_html($Language[9] ?? 'Usergroups'); ?></td>
                    <td class="alt1"><?php echo renderUserGroupCheckboxes($Ann['minclassread']); ?></td>
                </tr>
                <tr>
                    <td class="tcat2"></td>
                    <td class="tcat2">
                        <input type="submit" value="<?php echo escape_attr($Language[14] ?? 'Submit'); ?>" /> 
                        <input type="reset" value="<?php echo escape_attr($Language[15] ?? 'Reset'); ?>" />
                    </td>
                </tr>
            </table>
            </form>
            <?php
        }
    } catch (Exception $e) {
        error_log('Failed to load announcement for edit: ' . $e->getMessage());
        echo showAlertErrorModern('Failed to load announcement');
    }
}

// Display list of announcements
try {
    $countResult = $TSDatabase->query('SELECT COUNT(*) as count FROM announcements');
    $results = $countResult ? (int)$countResult->fetch(PDO::FETCH_ASSOC)['count'] : 0;
    
    list($pagertop, $limit) = buildPaginationLinks(25, $results, $_SERVER['SCRIPT_NAME'] . '?do=manage_announcements&');
    
    // Extract LIMIT values for PDO
    preg_match('/LIMIT (\d+), (\d+)/', $limit, $matches);
    $offset = (int)($matches[1] ?? 0);
    $perpage = (int)($matches[2] ?? 25);
    
    $result = $TSDatabase->query(
        'SELECT a.*, u.username, g.namestyle 
         FROM announcements a 
         LEFT JOIN users u ON a.by = u.username 
         LEFT JOIN usergroups g ON u.usergroup = g.gid 
         ORDER BY a.added DESC 
         LIMIT ?, ?',
        [$offset, $perpage]
    );
    
    if ($result && $result->rowCount() > 0) {
        $token = generateFormToken();
        ?>
        <?php echo showAlertSuccessModern('<a href="' . escape_attr($_SERVER['SCRIPT_NAME'] . '?do=manage_announcements&act=new') . '">' . escape_html($Language[6] ?? 'Add New') . '</a>'); ?>
        <?php echo $Message; ?>
        <?php echo $pagertop; ?>
        <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
            <tr>
                <td class="tcat" colspan="6" align="center">
                    <?php echo escape_html($Language[2] ?? 'Announcements'); ?> (<?php echo $results; ?>)
                </td>
            </tr>
            <tr>
                <td class="alt2"><?php echo escape_html($Language[7] ?? 'Subject'); ?></td>
                <td class="alt2"><?php echo escape_html($Language[8] ?? 'Message'); ?></td>
                <td class="alt2"><?php echo escape_html($Language[16] ?? 'By'); ?></td>
                <td class="alt2"><?php echo escape_html($Language[17] ?? 'Added'); ?></td>
                <td class="alt2"><?php echo escape_html($Language[9] ?? 'Usergroups'); ?></td>
                <td class="alt2" align="center"><?php echo escape_html($Language[18] ?? 'Options'); ?></td>
            </tr>
            <?php
            while ($Ann = $result->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <tr>
                    <td class="alt1">
                        <?php echo escape_html($Ann['subject']); ?>
                    </td>
                    <td class="alt1">
                        <?php echo escape_html(strip_tags(substr($Ann['message'], 0, 150))); ?>
                    </td>
                    <td class="alt1">
                        <a href="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=edit_user&username=' . $Ann['username']); ?>">
                            <?php echo applyUsernameStyle(escape_html($Ann['username']), $Ann['namestyle']); ?>
                        </a>
                    </td>
                    <td class="alt1">
                        <?php echo escape_html(formatTimestamp($Ann['added'])); ?>
                    </td>
                    <td class="alt1">
                        <?php echo escape_html($Ann['minclassread']); ?>
                    </td>
                    <td class="alt1" align="center">
                        <a href="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=manage_announcements&act=edit&id=' . $Ann['id']); ?>">
                            <img src="images/tool_edit.png" alt="<?php echo escape_attr($Language[4] ?? 'Edit'); ?>" title="<?php echo escape_attr($Language[4] ?? 'Edit'); ?>" border="0" />
                        </a> 
                        <a href="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=manage_announcements&act=delete&id=' . $Ann['id'] . '&form_token=' . $token); ?>" 
                           onclick="return confirm('Are you sure you want to delete this announcement?');">
                            <img src="images/tool_delete.png" alt="<?php echo escape_attr($Language[5] ?? 'Delete'); ?>" title="<?php echo escape_attr($Language[5] ?? 'Delete'); ?>" border="0" />
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php echo $pagertop; ?>
        <?php
    } else {
        $noAnnouncementsMsg = str_replace(
            '{1}',
            $_SERVER['SCRIPT_NAME'] . '?do=manage_announcements&act=new',
            $Language[13] ?? 'No announcements found. <a href="{1}">Add New</a>'
        );
        echo showAlertErrorModern($noAnnouncementsMsg);
    }
} catch (Exception $e) {
    error_log('Failed to list announcements: ' . $e->getMessage());
    echo showAlertErrorModern('Failed to load announcements list');
}

?>
