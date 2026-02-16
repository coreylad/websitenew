<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

check_staff_auth();
verify_csrf_token();

$lang = load_staff_language('duplicate_ips');
$db = get_pdo();
$message = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userids = $_POST['ids'] ?? [];
        
        if (is_array($userids) && count($userids) > 0 && $userids[0] !== '') {
            $stmt = $db->prepare("SELECT gid FROM usergroups WHERE isbanned = 'yes' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $usergroupid = $result['gid'] ?? 0;
            
            $placeholders = implode(',', array_fill(0, count($userids), '?'));
            $sysMsg = str_replace('{1}', $_SESSION['ADMIN_USERNAME'], $lang[15]);
            $modcomment = gmdate('Y-m-d') . ' - ' . trim($sysMsg) . "\n";
            
            $stmt = $db->prepare("UPDATE users SET enabled = 'no', usergroup = ?, notifs = ?, modcomment = CONCAT(?, modcomment) WHERE id IN ($placeholders)");
            $params = array_merge([$usergroupid, $sysMsg, $modcomment], $userids);
            $stmt->execute($params);
            
            if ($stmt->rowCount() > 0) {
                $sysMsg = str_replace(['{1}', '{2}'], [implode(',', $userids), $_SESSION['ADMIN_USERNAME']], $lang[14]);
                log_staff_action($sysMsg);
            }
        }
    }
    
    $stmt = $db->query("SELECT ip, count(*) as tot FROM users GROUP BY ip HAVING tot > 1 ORDER BY ip");
    
    if ($stmt->rowCount() === 0) {
        echo show_alert_error($lang[1]);
    } else {
        $stmt2 = $db->prepare("SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.id = ? LIMIT 1");
        $stmt2->execute([$_SESSION['ADMIN_ID']]);
        $loggedAdminDetails = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : (isset($_POST['perpage']) ? intval($_POST['perpage']) : 22);
        $page = isset($_GET['page']) ? intval($_GET['page']) : (isset($_POST['page']) ? intval($_POST['page']) : 1);
        $start = ($page - 1) * $perpage;
        
        $ips = [];
        while ($ip = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ip['ip'] = preg_replace('/[^0-9-\.]/u', '-', trim($ip['ip']));
            if ($ip['ip'] !== '') {
                $ips[] = $ip['ip'];
            }
        }
        
        if (count($ips) > 0) {
            $placeholders = implode(',', array_fill(0, count($ips), '?'));
            $stmt3 = $db->prepare("SELECT ip FROM users WHERE ip IN ($placeholders) ORDER BY ip");
            $stmt3->execute($ips);
            $total = ceil($stmt3->rowCount() / $perpage);
            
            $stmt4 = $db->prepare("SELECT u.id, u.ip, u.username, u.email, u.added, u.last_access, u.uploaded, u.downloaded, u.invites, u.seedbonus, g.title, g.namestyle, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup = g.gid) WHERE u.ip IN ($placeholders) ORDER BY u.ip LIMIT $start, $perpage");
            $stmt4->execute($ips);
            
            $firstpage = $prevpage = $nextpage = $lastpage = '';
            if ($page != 1) {
                $prv = $page - 1;
                $firstpage = '<input type="button" class="button" tabindex="1" value="&laquo; " onclick="window.location = \'index.php?do=duplicate_ips&perpage=' . escape_attr((string)$perpage) . '&page=1\'">';
                $prevpage = '<input type="button" class="button" tabindex="1" value="&lt; " onclick="window.location = \'index.php?do=duplicate_ips&perpage=' . escape_attr((string)$perpage) . '&page=' . escape_attr((string)$prv) . '\'">';
            }
            if ($page != $total && $total > 0) {
                $nxt = $page + 1;
                $nextpage = '<input type="button" class="button" tabindex="1" value=" &gt;" onclick="window.location = \'index.php?do=duplicate_ips&perpage=' . escape_attr((string)$perpage) . '&page=' . escape_attr((string)$nxt) . '\'">';
                $lastpage = '<input type="button" class="button" tabindex="1" value=" &raquo;" onclick="window.location = \'index.php?do=duplicate_ips&perpage=' . escape_attr((string)$perpage) . '&page=' . escape_attr((string)$total) . '\'">';
            }
            
            $ip1 = '';
            $found = '';
            while ($arr = $stmt4->fetch(PDO::FETCH_ASSOC)) {
                if ($arr['ip'] != $ip1) {
                    $face = '700; color:#000000;';
                    $bg = 'background-color:#EBC7C7;"';
                } else {
                    $face = '100';
                    $bg = '';
                }
                
                $username = '<a href="index.php?do=edit_user&amp;username=' . escape_attr($arr['username']) . '">' . apply_username_style($arr['username'], $arr['namestyle']) . '</a>';
                
                if (($arr['cansettingspanel'] == 'yes' && $loggedAdminDetails['cansettingspanel'] != 'yes') || 
                    ($arr['canstaffpanel'] == 'yes' && $loggedAdminDetails['canstaffpanel'] != 'yes') || 
                    ($arr['issupermod'] == 'yes' && $loggedAdminDetails['issupermod'] != 'yes')) {
                    $username = '<i>' . escape_html($lang[18]) . '</i>';
                }
                
                $found .= '
        <tr>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . $username . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . escape_html($arr['ip']) . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . escape_html($arr['email']) . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . format_timestamp($arr['added']) . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . format_timestamp($arr['last_access']) . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . format_bytes($arr['uploaded']) . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . format_bytes($arr['downloaded']) . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . number_format($arr['invites']) . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '">
                ' . escape_html($arr['seedbonus']) . '
            </td>
            <td class="alt1" style="font-weight:' . $face . '; ' . $bg . '" align="center">
                <input type="checkbox" name="ids[]" value="' . escape_attr((string)$arr['id']) . '" checkme="group" />
            </td>
        </tr>
        ';
                $ip1 = $arr['ip'];
            }
            
            $options = [];
            for ($i = 1; $i <= $total; $i++) {
                $options[$i] = $lang[16] . ' ' . $i;
            }
            $pages = '<select name="page" onchange="window.location = \'index.php?do=duplicate_ips&perpage=' . escape_attr((string)$perpage) . '&page=\' + this.value" class="bginput">' . "\n" . build_select_options($options, $page) . '</select>';
            
            echo '
    <script type="text/javascript">
        function select_deselectAll(formname,elm,group)
        {
            var frm = document.forms[formname];
            for(i = 0;i<frm.length;i++)
            {
                if(elm.attributes["checkall"] != null && elm.attributes["checkall"].value == group)
                {
                    if(frm.elements[i].attributes["checkme"] != null && frm.elements[i].attributes["checkme"].value == group)
                    {
                        frm.elements[i].checked = elm.checked;
                    }
                }
                else if(frm.elements[i].attributes["checkme"] != null && frm.elements[i].attributes["checkme"].value == group)
                {
                    if(frm.elements[i].checked == false)
                    {
                        frm.elements[1].checked = false;
                    }
                }
            }
        }
    </script>
    <form action="' . escape_attr($_SERVER['SCRIPT_NAME']) . '?do=duplicate_ips' . (isset($_GET['page']) ? '&page=' . intval($_GET['page']) : '') . '" method="post" name="dublicate_ips">
    ' . generate_csrf_token() . '
    ' . $message . '
    <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
        <tr>
            <td class="tcat" align="center" colspan="10"><b>' . escape_html($lang[3]) . '</b></td>
        </tr>
        <tr>
            <td class="alt2"><b>' . escape_html($lang[4]) . '</b></td>
            <td class="alt2"><b>' . escape_html($lang[5]) . '</b></td>
            <td class="alt2"><b>' . escape_html($lang[6]) . '</b></td>
            <td class="alt2"><b>' . escape_html($lang[13]) . '</b></td>
            <td class="alt2"><b>' . escape_html($lang[7]) . '</b></td>
            <td class="alt2"><b>' . escape_html($lang[8]) . '</b></td>
            <td class="alt2"><b>' . escape_html($lang[9]) . '</b></td>
            <td class="alt2"><b>' . escape_html($lang[10]) . '</b></td>
            <td class="alt2"><b>' . escape_html($lang[11]) . '</b></td>
            <td class="alt2" align="center"><input type="checkbox" checkall="group" onclick="javascript: return select_deselectAll (\'dublicate_ips\', this, \'group\');"></td>
        </tr>
        ' . $found . '
        <tr>
            <td class="tcat2" align="right" colspan="11">
                <input type="submit" value="' . escape_attr($lang[12]) . '" />
            </td>
        </tr>
        <tr>
            <td colspan="11" align="center">
                ' . $total . ' - ' . escape_html($lang[17]) . '  ' . $firstpage . ' ' . $prevpage . ' &nbsp; ' . $pages . ' &nbsp; ' . $nextpage . ' ' . $lastpage . '
            </td>
        </tr>
    </table>
    </form>';
        }
    }
} catch (Exception $e) {
    error_log('Duplicate IPs Error: ' . $e->getMessage());
    echo show_alert_error('An error occurred. Please try again.');
}
