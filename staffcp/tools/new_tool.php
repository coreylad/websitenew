<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('new_tool');

$Message = '';
$category = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
$toolname = isset($_GET['toolname']) ? trim($_GET['toolname']) : '';
$filename = isset($_GET['filename']) ? trim($_GET['filename']) : '';
$usergroups = [];
$sort = 0;

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[12] ?? 'Invalid form token');
    } else {
        $category = (int)($_POST['cid'] ?? 0);
        $toolname = trim($_POST['toolname'] ?? '');
        $filename = trim($_POST['filename'] ?? '');
        $usergroups = $_POST['usergroups'] ?? [];
        $sort = (int)($_POST['sort'] ?? 0);
        
        if ($category && $toolname && $filename && count($usergroups) && is_array($usergroups)) {
            try {
                $TSDatabase->query(
                    'INSERT INTO ts_staffcp_tools (cid, toolname, filename, usergroups, sort) VALUES (?, ?, ?, ?, ?)',
                    [$category, $toolname, $filename, implode(',', $usergroups), $sort]
                );
                
                if ($TSDatabase->rowCount() > 0) {
                    $SysMsg = str_replace(
                        ['{1}', '{2}'],
                        [$toolname, $_SESSION['ADMIN_USERNAME']],
                        $Language[3] ?? 'Tool {1} created by {2}'
                    );
                    
                    logStaffActionModern($SysMsg);
                    redirectTo('index.php?do=manage_tools');
                    exit;
                }
                
                $Message = showAlertErrorModern($Language[12] ?? 'Failed to create tool');
            } catch (Exception $e) {
                error_log('Create tool error: ' . $e->getMessage());
                $Message = showAlertErrorModern($Language[12] ?? 'Failed to create tool');
            }
        } else {
            $Message = showAlertErrorModern($Language[11] ?? 'Please fill all fields');
        }
    }
}

try {
    $result = $TSDatabase->query(
        'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
         FROM users u 
         LEFT JOIN usergroups g ON u.usergroup = g.gid 
         WHERE u.id = ? 
         LIMIT 1',
        [$_SESSION['ADMIN_ID']]
    );
    
    $LoggedAdminDetails = $result ? $result->fetch(PDO::FETCH_ASSOC) : [
        'cansettingspanel' => 'no',
        'canstaffpanel' => 'yes',
        'issupermod' => 'no'
    ];
} catch (Exception $e) {
    error_log('Get admin details error: ' . $e->getMessage());
    $LoggedAdminDetails = [
        'cansettingspanel' => 'no',
        'canstaffpanel' => 'yes',
        'issupermod' => 'no'
    ];
}

$showusergroups = '';
try {
    $result = $TSDatabase->query(
        'SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle 
         FROM usergroups 
         ORDER BY disporder ASC'
    );
    
    if ($result) {
        while ($UG = $result->fetch(PDO::FETCH_ASSOC)) {
            $canShow = !(
                ($UG['cansettingspanel'] === 'yes' && $LoggedAdminDetails['cansettingspanel'] !== 'yes') ||
                ($UG['canstaffpanel'] === 'yes' && $LoggedAdminDetails['canstaffpanel'] !== 'yes') ||
                ($UG['issupermod'] === 'yes' && $LoggedAdminDetails['issupermod'] !== 'yes')
            );
            
            if ($canShow) {
                $checked = (in_array($UG['gid'], $usergroups, true) || $UG['cansettingspanel'] === 'yes') ? ' checked="checked"' : '';
                $groupName = strip_tags(str_replace('{username}', $UG['title'], $UG['namestyle']), '<b><span><strong><em><i><u>');
                
                $showusergroups .= '<div style="margin-bottom: 3px;">
                    <label>
                        <input type="checkbox" name="usergroups[]" value="' . (int)$UG['gid'] . '"' . $checked . ' style="vertical-align: middle;" /> 
                        ' . $groupName . '
                    </label>
                </div>';
            }
        }
    }
} catch (Exception $e) {
    error_log('Get usergroups error: ' . $e->getMessage());
}

$showcategories = '<select name="cid">';
try {
    $result = $TSDatabase->query('SELECT cid, name FROM ts_staffcp ORDER BY sort ASC');
    if ($result) {
        while ($cats = $result->fetch(PDO::FETCH_ASSOC)) {
            $selected = ($category === $cats['cid']) ? ' selected="selected"' : '';
            $showcategories .= '<option value="' . (int)$cats['cid'] . '"' . $selected . '>' . escape_html($cats['name']) . '</option>';
        }
    }
} catch (Exception $e) {
    error_log('Get categories error: ' . $e->getMessage());
}
$showcategories .= '</select>';

?>
<?php echo $Message; ?>
<form method="post" action="index.php?do=new_tool">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="tborder">
    <tr>
        <td class="tcat" colspan="2" align="center">
            <?php echo escape_html($Language[2] ?? 'New Tool'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[5] ?? 'Tool Name'); ?></td>
        <td class="alt1"><input type="text" name="toolname" value="<?php echo escape_attr($toolname); ?>" size="40" /></td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[6] ?? 'Filename'); ?></td>
        <td class="alt1"><input type="text" name="filename" value="<?php echo escape_attr($filename); ?>" size="40" /></td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[7] ?? 'Sort Order'); ?></td>
        <td class="alt1"><input type="text" name="sort" value="<?php echo (int)$sort; ?>" size="40" /></td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[13] ?? 'Category'); ?></td>
        <td class="alt1"><?php echo $showcategories; ?></td>
    </tr>
    <tr>
        <td class="alt1" valign="top"><?php echo escape_html($Language[8] ?? 'User Groups'); ?></td>
        <td class="alt1"><?php echo $showusergroups; ?></td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" value="<?php echo escape_attr($Language[9] ?? 'Create'); ?>" /> 
            <input type="reset" value="<?php echo escape_attr($Language[10] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
