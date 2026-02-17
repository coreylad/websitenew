<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

global $TSDatabase, $MAIN;

$Language = loadStaffLanguage('manage_awards');
$Act = trim($_GET['act'] ?? $_POST['act'] ?? '');
$Message = '';

try {
    $result = $TSDatabase->query("SELECT content FROM ts_config WHERE configname = ?", ['MAIN']);
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $MAIN = $row ? unserialize($row['content']) : [];
} catch (Exception $e) {
    error_log('Failed to load MAIN config: ' . $e->getMessage());
    $MAIN = [];
}
if (is_dir("../images")) {
    $AwardImageDir = "../images/awardmedals/";
} else {
    if (is_dir("../pic")) {
        $AwardImageDir = "../pic/awardmedals/";
    } else {
        $AwardImageDir = false;
    }
}
$Output = [];
$ShowAwardImages = '';
$FromUserdetailsUser = trim($_GET['username'] ?? '');

if ($Act === 'delete') {
    $award_id = (int)($_GET['award_id'] ?? 0);
    if ($award_id > 0) {
        try {
            $TSDatabase->query("DELETE FROM ts_awards WHERE award_id = ?", [$award_id]);
            $TSDatabase->query("DELETE FROM ts_awards_users WHERE award_id = ?", [$award_id]);
            $Message = str_replace('{1}', escape_html($_SESSION['ADMIN_USERNAME']), $Language[3] ?? 'Award deleted by {1}');
            logStaffActionModern($Message);
            $Message = showAlertErrorModern($Message);
        } catch (Exception $e) {
            error_log('Delete award error: ' . $e->getMessage());
            $Message = showAlertErrorModern('Failed to delete award');
        }
    }
}
if ($Act === 'edit') {
    $award_id = (int)($_GET['award_id'] ?? 0);
    if ($award_id > 0) {
        try {
            $result = $TSDatabase->query("SELECT award_id, award_name, award_image FROM ts_awards WHERE award_id = ?", [$award_id]);
            $EditAward = $result->fetch(PDO::FETCH_ASSOC);
            
            if ($EditAward) {
                if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
                    if (!validateFormToken($_POST['form_token'] ?? '')) {
                        $Message = showAlertErrorModern('Invalid form token');
                    } else {
                        $award_name = trim($_POST['award_name'] ?? '');
                        $award_image = trim($_POST['award_image'] ?? '');
                        
                        $TSDatabase->query(
                            "UPDATE ts_awards SET award_name = ?, award_image = ? WHERE award_id = ?",
                            [$award_name, $award_image, $award_id]
                        );
                        
                        $Message = str_replace('{1}', escape_html($_SESSION['ADMIN_USERNAME']), $Language[14] ?? 'Award updated by {1}');
                        logStaffActionModern($Message);
                        $Message = showAlertErrorModern($Message);
                        $Act = '';
                        $award_id = 0;
                    }
                }
                
                if ($Message === '') {
                    $Images = scandir($AwardImageDir);
                    $ShowAwardImages = '<select name="award_image" onchange="update_award_image(this.value);">';
                    foreach ($Images as $Image) {
                        if ($Image !== '.' && $Image !== '..' && in_array(getFileExtension($Image), ['gif', 'jpg', 'png'])) {
                            $selected = ($EditAward['award_image'] === $Image) ? ' selected="selected"' : '';
                            $ShowAwardImages .= '<option value="' . escape_attr($Image) . '"' . $selected . '>' . escape_html($Image) . '</option>';
                        }
                    }
                    $ShowAwardImages .= '</select>';
                }
            } else {
                $Act = '';
                $award_id = 0;
            }
        } catch (Exception $e) {
            error_log('Edit award error: ' . $e->getMessage());
            $Act = '';
            $award_id = 0;
        }
    }
}
if ($Act === 'new') {
    if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            $Message = showAlertErrorModern('Invalid form token');
        } else {
            $award_name = trim($_POST['award_name'] ?? '');
            $award_image = trim($_POST['award_image'] ?? '');
            
            try {
                $TSDatabase->query(
                    "INSERT INTO ts_awards (award_name, award_image) VALUES (?, ?)",
                    [$award_name, $award_image]
                );
                
                $Message = str_replace(['{1}', '{2}'], [escape_html($award_name), escape_html($_SESSION['ADMIN_USERNAME'])], $Language[24] ?? 'Award {1} created by {2}');
                logStaffActionModern($Message);
                $Message = showAlertErrorModern($Message);
                $Act = '';
            } catch (Exception $e) {
                error_log('Create award error: ' . $e->getMessage());
                $Message = showAlertErrorModern('Failed to create award');
            }
        }
    }
    
    if ($Message === '') {
        $Images = scandir($AwardImageDir);
        $ShowAwardImages = '<select name="award_image" onchange="update_award_image(this.value);">';
        foreach ($Images as $Image) {
            if ($Image !== '.' && $Image !== '..' && in_array(getFileExtension($Image), ['gif', 'jpg', 'png'])) {
                $ShowAwardImages .= '<option value="' . escape_attr($Image) . '">' . escape_html($Image) . '</option>';
            }
        }
        $ShowAwardImages .= '</select>';
        
        $token_field = getFormTokenField();
        $Output[] = '
		<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
			<tr>
				<td class="tcat">
					' . escape_html($Language[9] ?? 'Create New Award') . '
				</td>
			</tr>
			<tr>
				<td class="alt1">
					<form method="post" action="index.php?do=manage_awards&amp;act=new">
					' . $token_field . '
					<fieldset>
						<legend>' . escape_html($Language[12] ?? 'Award Name') . '</legend>
						<input type="text" name="award_name" value="" size="30" />
					</fieldset>
					<fieldset>
						<legend>' . escape_html($Language[13] ?? 'Award Image') . '</legend>
						' . $ShowAwardImages . '<img id="awardimagepreview" src="' . escape_attr($MAIN['pic_base_url'] ?? '') . 'awardmedals/black.png" border="0" alt="" title="" width="26" height="16" />
					</fieldset>
					<fieldset>
						<legend>' . escape_html($Language[9] ?? 'Create New Award') . '</legend>
						<input type="submit" value="' . escape_attr($Language[10] ?? 'Submit') . '" /> <input type="reset" value="' . escape_attr($Language[11] ?? 'Reset') . '" />
					</fieldset>
					</form>
				</td>
			</tr>
		</table>';
    }
}
if ($Act === 'remove_award') {
    $award_id = (int)($_GET['award_id'] ?? 0);
    $userid = (int)($_GET['userid'] ?? 0);
    
    if ($award_id > 0 && $userid > 0) {
        try {
            $result1 = $TSDatabase->query("SELECT award_name FROM ts_awards WHERE award_id = ?", [$award_id]);
            $result2 = $TSDatabase->query("SELECT username FROM users WHERE id = ?", [$userid]);
            
            $row1 = $result1->fetch(PDO::FETCH_ASSOC);
            $row2 = $result2->fetch(PDO::FETCH_ASSOC);
            
            if ($row1 && $row2) {
                $Awarname = $row1['award_name'];
                $Username = $row2['username'];
                
                $TSDatabase->query("DELETE FROM ts_awards_users WHERE award_id = ? AND userid = ?", [$award_id, $userid]);
                
                $Message = str_replace(['{1}', '{2}', '{3}'], [escape_html($Awarname), escape_html($Username), escape_html($_SESSION['ADMIN_USERNAME'])], $Language[16] ?? 'Award {1} removed from {2} by {3}');
                logStaffActionModern($Message);
                $Message = showAlertErrorModern($Message);
            }
        } catch (Exception $e) {
            error_log('Remove award error: ' . $e->getMessage());
            $Message = showAlertErrorModern('Failed to remove award');
        }
    }
}
if ($Act === 'give_award') {
    $award_id = (int)($_GET['award_id'] ?? 0);
    
    if ($award_id > 0) {
        try {
            $result = $TSDatabase->query("SELECT award_name FROM ts_awards WHERE award_id = ?", [$award_id]);
            $award_row = $result->fetch(PDO::FETCH_ASSOC);
            
            if ($award_row) {
                if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
                    if (!validateFormToken($_POST['form_token'] ?? '')) {
                        $Message = showAlertErrorModern('Invalid form token');
                    } else {
                        $Awarname = $award_row['award_name'];
                        $Username = trim($_POST['username'] ?? '');
                        $reason = trim($_POST['reason'] ?? '');
                        $givenby = (int)$_SESSION['ADMIN_ID'];
                        $date = time();
                        
                        $user_result = $TSDatabase->query("SELECT id FROM users WHERE username = ?", [$Username]);
                        $user_row = $user_result->fetch(PDO::FETCH_ASSOC);
                        
                        if ($user_row) {
                            $userid = (int)$user_row['id'];
                            
                            $TSDatabase->query(
                                "INSERT INTO ts_awards_users (award_id, userid, reason, givenby, date) VALUES (?, ?, ?, ?, ?)",
                                [$award_id, $userid, $reason, $givenby, $date]
                            );
                            
                            $pm_message = str_replace(
                                ['\\n', '{0}', '{1}', '{2}', '{3}'],
                                ["\r\n\t\t\t\t", escape_html($Username), escape_html($MAIN['SITENAME'] ?? 'Site'), escape_html($Awarname), escape_html($reason)],
                                $Language[23] ?? 'You have received award {2}'
                            );
                            
                            $TSDatabase->query(
                                "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES (?, ?, NOW(), ?, ?)",
                                [$givenby, $userid, $Language[22] ?? 'Award Notification', $pm_message]
                            );
                            
                            $TSDatabase->query("UPDATE users SET pmunread = pmunread + 1 WHERE id = ?", [$userid]);
                            
                            $Message = str_replace(['{1}', '{2}', '{3}'], [escape_html($Awarname), escape_html($Username), escape_html($_SESSION['ADMIN_USERNAME'])], $Language[19] ?? 'Award {1} given to {2} by {3}');
                            logStaffActionModern($Message);
                            $Message = showAlertErrorModern($Message);
                            $Act = '';
                            $award_id = 0;
                        } else {
                            $Message = showAlertErrorModern('User not found');
                        }
                    }
                }
            } else {
                $Act = '';
                $award_id = 0;
            }
        } catch (Exception $e) {
            error_log('Give award error: ' . $e->getMessage());
            $Message = showAlertErrorModern('Failed to give award');
            $Act = '';
            $award_id = 0;
        }
    }
}
if ($Act === 'give_award' && isset($_GET['username']) && $_GET['username']) {
    $Username = trim($_GET['username'] ?? '');
    
    try {
        $user_result = $TSDatabase->query("SELECT id FROM users WHERE username = ?", [$Username]);
        $user_row = $user_result->fetch(PDO::FETCH_ASSOC);
        
        if ($user_row) {
            if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
                if (!validateFormToken($_POST['form_token'] ?? '')) {
                    $Message = showAlertErrorModern('Invalid form token');
                } else {
                    $award_id = (int)($_POST['award_id'] ?? 0);
                    $reason = trim($_POST['reason'] ?? '');
                    $givenby = (int)$_SESSION['ADMIN_ID'];
                    $date = time();
                    $userid = (int)$user_row['id'];
                    
                    $award_result = $TSDatabase->query("SELECT award_name FROM ts_awards WHERE award_id = ?", [$award_id]);
                    $award_row = $award_result->fetch(PDO::FETCH_ASSOC);
                    
                    if ($award_row) {
                        $Awarname = $award_row['award_name'];
                        
                        $TSDatabase->query(
                            "INSERT INTO ts_awards_users (award_id, userid, reason, givenby, date) VALUES (?, ?, ?, ?, ?)",
                            [$award_id, $userid, $reason, $givenby, $date]
                        );
                        
                        $pm_message = str_replace(
                            ['\\n', '{0}', '{1}', '{2}', '{3}'],
                            ["\r\n\t\t\t\t", escape_html($Username), escape_html($MAIN['SITENAME'] ?? 'Site'), escape_html($Awarname), escape_html($reason)],
                            $Language[23] ?? 'You have received award {2}'
                        );
                        
                        $TSDatabase->query(
                            "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES (?, ?, NOW(), ?, ?)",
                            [$givenby, $userid, $Language[22] ?? 'Award Notification', $pm_message]
                        );
                        
                        $TSDatabase->query("UPDATE users SET pmunread = pmunread + 1 WHERE id = ?", [$userid]);
                        
                        $Message = str_replace(['{1}', '{2}', '{3}'], [escape_html($Awarname), escape_html($Username), escape_html($_SESSION['ADMIN_USERNAME'])], $Language[19] ?? 'Award {1} given to {2} by {3}');
                        logStaffActionModern($Message);
                        $Message = showAlertErrorModern($Message);
                        $Act = '';
                        $award_id = 0;
                    }
                }
            } else {
                $award_result = $TSDatabase->query("SELECT award_id, award_name FROM ts_awards");
                $Selecbox = '<select name="award_id">';
                while ($Sawards = $award_result->fetch(PDO::FETCH_ASSOC)) {
                    $Selecbox .= '<option value="' . escape_attr($Sawards['award_id']) . '">' . escape_html($Sawards['award_name']) . '</option>';
                }
                $Selecbox .= '</select>';
                
                $token_field = getFormTokenField();
                $Output[] = '
			<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
				<tr>
					<td class="tcat">
						' . escape_html($Language[18] ?? 'Give Award') . '
					</td>
				</tr>
				<tr>
					<td class="alt1">
						<form method="post" action="index.php?do=manage_awards&amp;act=give_award&amp;username=' . escape_attr($Username) . '">
						' . $token_field . '
						<fieldset>
							<legend>' . escape_html($Language[20] ?? 'Username') . '</legend>
							<input type="text" name="username" value="' . escape_attr($Username) . '" size="30" />
						</fieldset>
						<fieldset>
							<legend>' . escape_html($Language[21] ?? 'Reason') . '</legend>
							<input type="text" name="reason" value="" size="30" />
						</fieldset>
						<fieldset>
							<legend>' . escape_html($Language[12] ?? 'Award Name') . '</legend>
							' . $Selecbox . '
						</fieldset>
						<fieldset>
							<legend>' . escape_html($Language[18] ?? 'Give Award') . '</legend>
							<input type="submit" value="' . escape_attr($Language[10] ?? 'Submit') . '" /> <input type="reset" value="' . escape_attr($Language[11] ?? 'Reset') . '" />
						</fieldset>
						</form>
					</td>
				</tr>
			</table>';
            }
        } else {
            $Act = '';
            $award_id = 0;
        }
    } catch (Exception $e) {
        error_log('Give award by username error: ' . $e->getMessage());
        $Act = '';
        $award_id = 0;
    }
}
$AwardUsers = [];
$AwardUserList = [];

try {
    $result = $TSDatabase->query("SELECT a.award_id, a.userid, u.username, g.namestyle FROM ts_awards_users a LEFT JOIN users u ON (a.userid = u.id) LEFT JOIN usergroups g ON (u.usergroup = g.gid)");
    
    while ($au = $result->fetch(PDO::FETCH_ASSOC)) {
        if (isset($AwardUsers[$au['award_id']])) {
            $AwardUsers[$au['award_id']]++;
        } else {
            $AwardUsers[$au['award_id']] = 1;
        }
        
        $highlight_start = ($FromUserdetailsUser === $au['username']) ? '<span class="highlight">' : '';
        $highlight_end = ($FromUserdetailsUser === $au['username']) ? '</span>' : '';
        $username_styled = str_replace('{username}', escape_html($au['username']), $au['namestyle']);
        
        $AwardUserList[$au['award_id']][] = $highlight_start . $username_styled . $highlight_end . 
            ' <a href="index.php?do=manage_awards&amp;act=remove_award&amp;award_id=' . (int)$au['award_id'] . 
            '&amp;userid=' . (int)$au['userid'] . '" onclick="return confirm(\'' . escape_js(trim($Language[17] ?? 'Are you sure?')) . 
            '\');"><img src="images/unconfirmed_users.png" alt="' . escape_attr(trim($Language[15] ?? 'Remove')) . 
            '" title="' . escape_attr(trim($Language[15] ?? 'Remove')) . '" border="0" style="vertical-align: middle;" /></a>';
    }
} catch (Exception $e) {
    error_log('Load award users error: ' . $e->getMessage());
}
try {
    $result = $TSDatabase->query("SELECT * FROM ts_awards ORDER BY award_sort");
    
    while ($a = $result->fetch(PDO::FETCH_ASSOC)) {
        $AwardImage = escape_attr($MAIN['pic_base_url'] ?? '') . 'awardmedals/' . escape_attr($a['award_image']);
        $AwardName = escape_html($a['award_name']);
        $award_id_val = (int)$a['award_id'];
        
        $token_field = getFormTokenField();
        
        $award_users_text = '';
        if (isset($AwardUsers[$award_id_val]) && $AwardUsers[$award_id_val] > 0) {
            $award_users_text = str_replace('{1}', number_format($AwardUsers[$award_id_val]), $Language[7] ?? '{1} users') . '<br />' . implode(' ', $AwardUserList[$award_id_val] ?? []);
        } else {
            $award_users_text = escape_html($Language[8] ?? 'No users');
        }
        
        $inner_content = '';
        if ($Act === 'edit' && $award_id === $award_id_val) {
            $inner_content = '
			<form method="post" action="index.php?do=manage_awards&amp;act=edit&amp;award_id=' . $award_id . '">
			' . $token_field . '
			<fieldset>
				<legend>' . escape_html($Language[12] ?? 'Award Name') . '</legend>
				<input type="text" name="award_name" value="' . $AwardName . '" size="30" />
			</fieldset>
			<fieldset>
				<legend>' . escape_html($Language[13] ?? 'Award Image') . '</legend>
				' . $ShowAwardImages . ' <img id="awardimagepreview" src="' . $AwardImage . '" border="0" alt="" title="" width="26" height="16" />
			</fieldset>
			<fieldset>
				<legend>' . escape_html($Language[4] ?? 'Edit Award') . '</legend>
				<input type="submit" value="' . escape_attr($Language[10] ?? 'Submit') . '" /> <input type="reset" value="' . escape_attr($Language[11] ?? 'Reset') . '" />
			</fieldset>
			</form>
			';
        } elseif ($Act === 'give_award' && $award_id === $award_id_val) {
            $inner_content = '
			<form method="post" action="index.php?do=manage_awards&amp;act=give_award&amp;award_id=' . $award_id . '">
			' . $token_field . '
			<fieldset>
				<legend>' . escape_html($Language[20] ?? 'Username') . '</legend>
				<input type="text" name="username" value="" size="30" />
			</fieldset>
			<fieldset>
				<legend>' . escape_html($Language[21] ?? 'Reason') . '</legend>
				<input type="text" name="reason" value="" size="30" />
			</fieldset>
			<fieldset>
				<legend>' . escape_html($Language[18] ?? 'Give Award') . '</legend>
				<input type="submit" value="' . escape_attr($Language[10] ?? 'Submit') . '" /> <input type="reset" value="' . escape_attr($Language[11] ?? 'Reset') . '" />
			</fieldset>
			</form>
			';
        } else {
            $inner_content = '
				<table>
					<tr>
						<td rowspan="2" valign="top">
							<img src="' . $AwardImage . '" alt="" title="" border="0" />
						</td>
						<td align="center">
							' . $award_users_text . '
						</td>
					</tr>
				</table>';
        }
        
        $Output[] = '
	<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
		<tr>
			<td class="tcat">
				<span style="float: right;">
						<a href="index.php?do=manage_awards&amp;act=give_award&amp;award_id=' . $award_id_val . '"><img src="images/accept.png" alt="' . escape_attr(trim($Language[18] ?? 'Give Award')) . '" title="' . escape_attr(trim($Language[18] ?? 'Give Award')) . '" border="0" style="vertical-align: middle;" /></a> <a href="index.php?do=manage_awards&amp;act=edit&amp;award_id=' . $award_id_val . '"><img src="images/tool_edit.png" alt="' . escape_attr(trim($Language[4] ?? 'Edit')) . '" title="' . escape_attr(trim($Language[4] ?? 'Edit')) . '" border="0" style="vertical-align: middle;" /></a> <a href="index.php?do=manage_awards&amp;act=delete&amp;award_id=' . $award_id_val . '" onclick="return confirm(\'' . escape_js(trim($Language[6] ?? 'Are you sure?')) . '\');"><img src="images/tool_delete.png" alt="' . escape_attr(trim($Language[5] ?? 'Delete')) . '" title="' . escape_attr(trim($Language[5] ?? 'Delete')) . '" border="0" style="vertical-align: middle;" /></a>
				</span>
				' . $AwardName . '
			</td>
		</tr>
		<tr>
			<td class="alt1">
			' . $inner_content . '
			</td>
		</tr>
	</table>';
    }
} catch (Exception $e) {
    error_log('Load awards error: ' . $e->getMessage());
}
$List = '';
$Count = 0;

foreach ($Output as $Award) {
    if ($Count % 2 === 0) {
        $List .= '</td><td valign="top">';
    }
    if ($Count % 6 === 0) {
        $List .= '</td></tr><tr><td valign="top">';
    }
    $List .= $Award;
    $Count++;
}

$pic_base_url = escape_attr($MAIN['pic_base_url'] ?? '');
$new_award_link = escape_html($Language[9] ?? 'Create New Award');
$manage_awards_title = escape_html($Language[2] ?? 'Manage Awards');

echo '
<script type="text/javascript">
	function update_award_image(selected)
	{
		TSGetID("awardimagepreview").src = "' . $pic_base_url . 'awardmedals/"+selected;
	}
</script>
' . showAlertMessage('<a href="index.php?do=manage_awards&amp;act=new">' . $new_award_link . '</a>') . '
' . $Message . '
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
	<tr>
		<td class="tcat" align="center">' . $manage_awards_title . '</td>
	</tr>
</table>

	' . $List . '';

function getStaffLanguage(): string
{
    return getStaffLanguageModern();
}

function checkStaffAuthentication(): void
{
    if (!defined('IN-TSSE-STAFF-PANEL')) {
        redirectTo('../index.php');
    }
}

function showAlertError(string $Error): string
{
    return '<div class="alert"><div>' . $Error . '</div></div>';
}

function logStaffAction(string $log): void
{
    logStaffActionModern($log);
}

function showAlertMessage(string $message = ''): string
{
    return '<div class="alert"><div>' . $message . '</div></div>';
}

function getFileExtension(string $file): string
{
    return strtolower(substr(strrchr($file, '.'), 1));
}

?>