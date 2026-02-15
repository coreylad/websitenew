<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language with new helper
$Language = loadStaffLanguage('add_user');

// Initialize variables
$Message = '';
$email = '';
$username = '';
$password = '';
$usergroup = 1;

// Process form submission
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // Get and sanitize input
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $usergroup = (int)($_POST['usergroup'] ?? 1);
    
    // Validate form token for CSRF protection
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertError($Language[5] ?? 'Invalid form token. Please try again.');
    }
    // Validate all required fields
    elseif (empty($username) || empty($password) || empty($email) || !$usergroup) {
        $Message = showAlertError($Language[4] ?? 'All fields are required');
    }
    // Validate username format
    elseif (!validateUsername($username)) {
        $Message = showAlertError($Language[2] ?? 'Username can only contain letters, numbers, underscores and hyphens');
    }
    // Validate email format
    elseif (!validateEmail($email)) {
        $Message = showAlertError($Language[3] ?? 'Invalid email address');
    }
    else {
        // Check if username or email already exists using prepared statement
        try {
            $result = $TSDatabase->query(
                'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1',
                [$username, $email]
            );
            
            if ($result && $result->fetch()) {
                $Message = showAlertError($Language[1] ?? 'Username or email already exists');
            }
            else {
                // Create new user
                $secret = generateSecret();
                $passhash = md5($secret . $password . $secret);
                $SysMsg = str_replace(['{1}', '{2}'], [$username, $_SESSION['ADMIN_USERNAME']], $Language[14] ?? 'User {1} created by {2}');
                $modcomment = gmdate('Y-m-d') . ' - ' . trim($SysMsg) . "\n";
                
                // Insert user with prepared statement
                $TSDatabase->query(
                    'INSERT INTO users (status, username, passhash, secret, email, added, usergroup, modcomment) 
                     VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)',
                    ['confirmed', $username, $passhash, $secret, $email, $usergroup, $modcomment]
                );
                
                // Get the new user ID
                $userid = $TSDatabase->lastInsertId();
                
                if ($userid) {
                    logStaffActionModern($SysMsg);
                    redirectTo('index.php?do=edit_user&username=' . urlencode($username));
                }
                else {
                    $Message = showAlertError($Language[13] ?? 'Failed to create user');
                }
            }
        }
        catch (Exception $e) {
            error_log('Add user error: ' . $e->getMessage());
            $Message = showAlertError($Language[13] ?? 'Failed to create user');
        }
    }
}

// Get logged-in admin details
try {
    $result = $TSDatabase->query(
        'SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod 
         FROM users u 
         LEFT JOIN usergroups g ON u.usergroup = g.gid 
         WHERE u.id = ? 
         LIMIT 1',
        [$_SESSION['ADMIN_ID']]
    );
    $LoggedAdminDetails = $result ? $result->fetch(PDO::FETCH_ASSOC) : [];
}
catch (Exception $e) {
    $LoggedAdminDetails = [];
}

// Build usergroups dropdown with security checks
$showusergroups = '<select name="usergroup" tabindex="1" class="bginput">';

try {
    $result = $TSDatabase->query('SELECT gid, title, cansettingspanel, canstaffpanel, issupermod FROM usergroups ORDER BY disporder ASC');
    
    if ($result) {
        while ($UG = $result->fetch(PDO::FETCH_ASSOC)) {
            // Security check: only show groups the admin can assign
            $canAssign = true;
            if ($UG['cansettingspanel'] === 'yes' && ($LoggedAdminDetails['cansettingspanel'] ?? 'no') !== 'yes') {
                $canAssign = false;
            }
            if ($UG['canstaffpanel'] === 'yes' && ($LoggedAdminDetails['canstaffpanel'] ?? 'no') !== 'yes') {
                $canAssign = false;
            }
            if ($UG['issupermod'] === 'yes' && ($LoggedAdminDetails['issupermod'] ?? 'no') !== 'yes') {
                $canAssign = false;
            }
            
            if ($canAssign) {
                $selected = ($usergroup == $UG['gid']) ? ' selected="selected"' : '';
                $showusergroups .= '<option value="' . escape_attr((string)$UG['gid']) . '"' . $selected . '>' . 
                                   escape_html($UG['title']) . '</option>';
            }
        }
    }
}
catch (Exception $e) {
    error_log('Failed to load usergroups: ' . $e->getMessage());
}

$showusergroups .= '</select>';

// Output form
?>
<form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=add_user'); ?>" method="post">
<?php echo getFormTokenField(); ?>
<?php echo $Message; ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center" colspan="2"><b><?php echo escape_html($Language[6] ?? 'Add User'); ?></b></td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right"><?php echo escape_html($Language[7] ?? 'Username:'); ?></td>
        <td class="alt1">
            <input type="text" class="bginput" name="username" 
                   value="<?php echo escape_attr($username); ?>" 
                   size="35" dir="ltr" tabindex="1" />
        </td>
    </tr>
    <tr valign="top">
        <td class="alt2" align="right"><?php echo escape_html($Language[8] ?? 'Password:'); ?></td>
        <td class="alt2">
            <input type="password" class="bginput" name="password" 
                   value="" size="35" dir="ltr" tabindex="1" autocomplete="off" />
        </td>
    </tr>
    <tr valign="top">
        <td class="alt1" align="right"><?php echo escape_html($Language[9] ?? 'Email:'); ?></td>
        <td class="alt1">
            <input type="text" class="bginput" name="email" 
                   value="<?php echo escape_attr($email); ?>" 
                   size="35" dir="ltr" tabindex="1" />
        </td>
    </tr>
    <tr valign="top">
        <td class="alt2" align="right"><?php echo escape_html($Language[10] ?? 'Usergroup:'); ?></td>
        <td class="alt2"><?php echo $showusergroups; ?></td>
    </tr>
    <tr>
        <td class="tcat2" align="right"></td>
        <td class="tcat2">
            <input type="submit" class="button" tabindex="1" 
                   value="<?php echo escape_attr($Language[11] ?? 'Create User'); ?>" accesskey="s" />
            <input type="reset" class="button" tabindex="1" 
                   value="<?php echo escape_attr($Language[12] ?? 'Reset'); ?>" accesskey="r" />
        </td>
    </tr>
</table>
</form>
