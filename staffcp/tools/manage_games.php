<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

check_staff_auth();

$act = $_GET['act'] ?? $_POST['act'] ?? '';
$gid = intval($_GET['gid'] ?? $_POST['gid'] ?? 0);
$lang = load_staff_language('manage_games');
$db = get_pdo();
$message = '';
$list = '';

try {
    if ($act === 'delete' && $gid) {
        verify_csrf_token();
        $stmt = $db->prepare("SELECT gname FROM ts_games WHERE gid = ?");
        $stmt->execute([$gid]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("DELETE FROM ts_games WHERE gid = ?");
        $stmt->execute([$gid]);
        
        $msg = str_replace(['{1}', '{2}'], [$game['gname'], $_SESSION['ADMIN_USERNAME']], $lang[1]);
        log_staff_action($msg);
        $message = show_alert_message($msg);
    }
    
    if ($act === 'reset_champions' && $gid) {
        verify_csrf_token();
        $stmt = $db->prepare("SELECT gname FROM ts_games WHERE gid = ?");
        $stmt->execute([$gid]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("DELETE FROM ts_games_champions WHERE gid = ?");
        $stmt->execute([$gid]);
        
        $msg = str_replace(['{1}', '{2}'], [$game['gname'], $_SESSION['ADMIN_USERNAME']], $lang[31]);
        log_staff_action($msg);
        $message = show_alert_message($msg);
    }
    
    if ($act === 'reset_score' && $gid) {
        verify_csrf_token();
        $stmt = $db->prepare("SELECT gname FROM ts_games WHERE gid = ?");
        $stmt->execute([$gid]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("DELETE FROM ts_games_scores WHERE gid = ?");
        $stmt->execute([$gid]);
        
        $msg = str_replace(['{1}', '{2}'], [$game['gname'], $_SESSION['ADMIN_USERNAME']], $lang[32]);
        log_staff_action($msg);
        $message = show_alert_message($msg);
    }
    
    if ($act === 'delete_comments' && $gid) {
        verify_csrf_token();
        $stmt = $db->prepare("SELECT gname FROM ts_games WHERE gid = ?");
        $stmt->execute([$gid]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("DELETE FROM ts_games_comments WHERE gid = ?");
        $stmt->execute([$gid]);
        
        $msg = str_replace(['{1}', '{2}'], [$game['gname'], $_SESSION['ADMIN_USERNAME']], $lang[34]);
        log_staff_action($msg);
        $message = show_alert_message($msg);
    }
    
    if ($act === 'edit' && $gid) {
        $stmt = $db->prepare("SELECT * FROM ts_games WHERE gid = ?");
        $stmt->execute([$gid]);
        
        if ($stmt->rowCount() > 0) {
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                verify_csrf_token();
                
                $game = [];
                $game['gname'] = trim($_POST['gname'] ?? '');
                $game['gwords'] = trim($_POST['gwords'] ?? '');
                $game['gtitle'] = trim($_POST['gtitle'] ?? '');
                $game['gwidth'] = intval($_POST['gwidth'] ?? 0);
                $game['gheight'] = intval($_POST['gheight'] ?? 0);
                $game['sort'] = intval($_POST['sort'] ?? 0);
                $game['objective'] = trim($_POST['objective'] ?? '');
                $game['gkeys'] = trim($_POST['gkeys'] ?? '');
                $game['savescore'] = intval($_POST['savescore'] ?? 0);
                $game['cid'] = intval($_POST['cid'] ?? 0);
                
                if ($game['gname'] && $game['gwidth'] && $game['gheight'] && $game['cid']) {
                    $gameFile = './../ts_games/game_files/' . $game['gname'] . '.swf';
                    if (is_file($gameFile)) {
                        $stmt = $db->prepare("UPDATE ts_games SET gname = ?, gwords = ?, gtitle = ?, gwidth = ?, gheight = ?, sort = ?, objective = ?, gkeys = ?, savescore = ?, cid = ? WHERE gid = ?");
                        $stmt->execute([$game['gname'], $game['gwords'], $game['gtitle'], $game['gwidth'], $game['gheight'], $game['sort'], $game['objective'], $game['gkeys'], $game['savescore'], $game['cid'], $gid]);
                        
                        $msg = str_replace(['{1}', '{2}'], [$game['gname'], $_SESSION['ADMIN_USERNAME']], $lang[13]);
                        log_staff_action($msg);
                        $message = show_alert_message($msg);
                        $done = true;
                    } else {
                        $message = show_alert_error($lang[28] . ' <b>' . escape_html($gameFile) . '</b>');
                    }
                } else {
                    $message = show_alert_error($lang[12]);
                }
            }
            
            if (!isset($done)) {
                echo '
            <form method="post" action="index.php?do=manage_games">
            ' . generate_csrf_token() . '
            <input type="hidden" name="act" value="edit" />
            <input type="hidden" name="gid" value="' . escape_attr((string)$gid) . '" />
            ' . show_alert_message('<a href="index.php?do=manage_games">' . escape_html($lang[17]) . '</a>') . '
            ' . $message . '
            <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
                <tr>
                    <td class="tcat" align="center" colspan="2"><b>' . escape_html($lang[2]) . ' - ' . escape_html($lang[7]) . ': ' . escape_html($game['gname']) . '</b></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[3]) . '</td>
                    <td class="alt1"><input type="text" class="bginput" name="gname" value="' . escape_attr($game['gname']) . '" size="70" dir="ltr" tabindex="1" /> <b><i><small>' . escape_html($lang[27]) . '</small></i></b></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[4]) . '</td>
                    <td class="alt1"><input type="text" class="bginput" name="gwords" value="' . escape_attr($game['gwords']) . '" size="70" dir="ltr" tabindex="1" /></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[18]) . '</td>
                    <td class="alt1"><input type="text" class="bginput" name="gtitle" value="' . escape_attr($game['gtitle']) . '" size="70" dir="ltr" tabindex="1" /></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[21]) . '</td>
                    <td class="alt1"><input type="text" class="bginput" name="objective" value="' . escape_attr($game['objective']) . '" size="70" dir="ltr" tabindex="1" /></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[22]) . '</td>
                    <td class="alt1"><input type="text" class="bginput" name="gkeys" value="' . escape_attr($game['gkeys']) . '" size="70" dir="ltr" tabindex="1" /></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[19]) . '</td>
                    <td class="alt1"><input type="text" class="bginput" name="gwidth" value="' . escape_attr((string)$game['gwidth']) . '" size="15" dir="ltr" tabindex="1" /></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[20]) . '</td>
                    <td class="alt1"><input type="text" class="bginput" name="gheight" value="' . escape_attr((string)$game['gheight']) . '" size="15" dir="ltr" tabindex="1" /></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[5]) . '</td>
                    <td class="alt1"><input type="text" class="bginput" name="sort" value="' . escape_attr((string)$game['sort']) . '" size="15" dir="ltr" tabindex="1" /></td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[23]) . '</td>
                    <td class="alt1">
                        <select name="savescore">
                            <option value="1"' . ($game['savescore'] == '1' ? ' selected="selected"' : '') . '>' . escape_html($lang[24]) . '</option>
                            <option value="0"' . ($game['savescore'] == '0' ? ' selected="selected"' : '') . '>' . escape_html($lang[25]) . '</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[26]) . '</td>
                    <td class="alt1">' . build_category_select($game['cid'], $db) . '</td>
                </tr>
                <tr>
                <td class="tcat2"></td>
                <td class="tcat2">
                    <input type="submit" class="button" tabindex="1" value="' . escape_attr($lang[14]) . '" accesskey="s" />
                    <input type="reset" class="button" tabindex="1" value="' . escape_attr($lang[15]) . '" accesskey="r" />
                </td>
            </tr>
            </table>
            </form>';
            }
        }
    }
    
    if ($act === 'add') {
        $game = ['gname' => '', 'gwords' => '', 'gtitle' => '', 'gwidth' => 0, 'gheight' => 0, 'sort' => 0, 'objective' => '', 'gkeys' => '', 'savescore' => 1, 'cid' => 0];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf_token();
            
            $game['gname'] = trim($_POST['gname'] ?? '');
            $game['gwords'] = trim($_POST['gwords'] ?? '');
            $game['gtitle'] = trim($_POST['gtitle'] ?? '');
            $game['gwidth'] = intval($_POST['gwidth'] ?? 0);
            $game['gheight'] = intval($_POST['gheight'] ?? 0);
            $game['sort'] = intval($_POST['sort'] ?? 0);
            $game['objective'] = trim($_POST['objective'] ?? '');
            $game['gkeys'] = trim($_POST['gkeys'] ?? '');
            $game['savescore'] = intval($_POST['savescore'] ?? 0);
            $game['cid'] = intval($_POST['cid'] ?? 0);
            $game['added'] = time();
            
            if ($game['gname'] && $game['gwidth'] && $game['gheight'] && $game['cid']) {
                $gameFile = './../ts_games/game_files/' . $game['gname'] . '.swf';
                if (is_file($gameFile)) {
                    $stmt = $db->prepare("INSERT INTO ts_games (gname, gwords, gtitle, gwidth, gheight, sort, objective, gkeys, savescore, cid, added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$game['gname'], $game['gwords'], $game['gtitle'], $game['gwidth'], $game['gheight'], $game['sort'], $game['objective'], $game['gkeys'], $game['savescore'], $game['cid'], $game['added']]);
                    redirect_to('index.php?do=manage_games');
                    exit;
                } else {
                    $message = show_alert_error($lang[28] . ' <b>' . escape_html($gameFile) . '</b>');
                }
            } else {
                $message = show_alert_error($lang[12]);
            }
        }
        
        if (!isset($done)) {
            echo '
        <form method="post" action="index.php?do=manage_games">
        ' . generate_csrf_token() . '
        <input type="hidden" name="act" value="add" />
        ' . show_alert_message('<a href="index.php?do=manage_games">' . escape_html($lang[17]) . '</a>') . '
        ' . $message . '
        <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
            <tr>
                <td class="tcat" align="center" colspan="2"><b>' . escape_html($lang[2]) . ' - ' . escape_html($lang[9]) . '</b></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[3]) . '</td>
                <td class="alt1"><input type="text" class="bginput" name="gname" value="' . escape_attr($game['gname']) . '" size="70" dir="ltr" tabindex="1" /> <b><i><small>' . escape_html($lang[27]) . '</small></i></b></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[4]) . '</td>
                <td class="alt1"><input type="text" class="bginput" name="gwords" value="' . escape_attr($game['gwords']) . '" size="70" dir="ltr" tabindex="1" /></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[18]) . '</td>
                <td class="alt1"><input type="text" class="bginput" name="gtitle" value="' . escape_attr($game['gtitle']) . '" size="70" dir="ltr" tabindex="1" /></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[21]) . '</td>
                <td class="alt1"><input type="text" class="bginput" name="objective" value="' . escape_attr($game['objective']) . '" size="70" dir="ltr" tabindex="1" /></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[22]) . '</td>
                <td class="alt1"><input type="text" class="bginput" name="gkeys" value="' . escape_attr($game['gkeys']) . '" size="70" dir="ltr" tabindex="1" /></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[19]) . '</td>
                <td class="alt1"><input type="text" class="bginput" name="gwidth" value="' . escape_attr((string)$game['gwidth']) . '" size="15" dir="ltr" tabindex="1" /></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[20]) . '</td>
                <td class="alt1"><input type="text" class="bginput" name="gheight" value="' . escape_attr((string)$game['gheight']) . '" size="15" dir="ltr" tabindex="1" /></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[5]) . '</td>
                <td class="alt1"><input type="text" class="bginput" name="sort" value="' . escape_attr((string)$game['sort']) . '" size="15" dir="ltr" tabindex="1" /></td>
            </tr>
            <tr valign="top">
                <td class="alt1">' . escape_html($lang[23]) . '</td>
                <td class="alt1">
                    <select name="savescore">
                        <option value="1"' . ($game['savescore'] == '1' ? ' selected="selected"' : '') . '>' . escape_html($lang[24]) . '</option>
                        <option value="0"' . ($game['savescore'] == '0' ? ' selected="selected"' : '') . '>' . escape_html($lang[25]) . '</option>
                    </select>
                </td>
            </tr>
                <tr valign="top">
                    <td class="alt1">' . escape_html($lang[26]) . '</td>
                    <td class="alt1">' . build_category_select($game['cid'], $db) . '</td>
                </tr>
            <tr>
            <td class="tcat2"></td>
            <td class="tcat2">
                <input type="submit" class="button" tabindex="1" value="' . escape_attr($lang[14]) . '" accesskey="s" />
                <input type="reset" class="button" tabindex="1" value="' . escape_attr($lang[15]) . '" accesskey="r" />
            </td>
        </tr>
        </table>
        </form>';
        }
    }
    
    $stmt = $db->query("SELECT * FROM ts_games");
    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $gwords = strlen($category['gwords']) > 50 ? substr($category['gwords'], 0, 50) . '...' : $category['gwords'];
        $saveScoreLabel = $category['savescore'] == '1' ? $lang[24] : $lang[25];
        $categoryName = get_category_name($category['cid'], $db);
        
        $list .= '
    <tr>
        <td class="alt1" align="center">
            <a href="./../ts_games/index.php?cid=' . escape_attr((string)$category['cid']) . '" target="_blank">' . escape_html($categoryName) . '</a>
        </td>
        <td class="alt1">
            ' . escape_html($gwords) . '
        </td>
        <td class="alt1">
            <a href="./../ts_games/index.php?act=play&amp;gid=' . escape_attr((string)$category['gid']) . '" target="_blank">' . escape_html($category['gtitle']) . '</a>
        </td>
        <td class="alt1" align="center">
            ' . intval($category['gwidth']) . '
        </td>
        <td class="alt1" align="center">
            ' . intval($category['gheight']) . '
        </td>
        <td class="alt1" align="center">
            ' . escape_html($saveScoreLabel) . '
        </td>
        <td class="alt1" align="center">
            ' . intval($category['sort']) . '
        </td>
        <td class="alt1" align="center">
            <a href="index.php?do=manage_games&amp;act=edit&amp;gid=' . escape_attr((string)$category['gid']) . '"><img src="./images/tool_edit.png" alt="' . escape_attr($lang[7]) . '" title="' . escape_attr($lang[7]) . '" border="0" /></a>
            <a href="index.php?do=manage_games&amp;act=reset_champions&amp;gid=' . escape_attr((string)$category['gid']) . '&amp;csrf_token=' . escape_attr(generate_csrf_token(false)) . '"><img src="./images/award_star_delete.png" alt="' . escape_attr($lang[29]) . '" title="' . escape_attr($lang[29]) . '" border="0" /></a>
            <a href="index.php?do=manage_games&amp;act=reset_score&amp;gid=' . escape_attr((string)$category['gid']) . '&amp;csrf_token=' . escape_attr(generate_csrf_token(false)) . '"><img src="./images/chart_curve_delete.png" alt="' . escape_attr($lang[30]) . '" title="' . escape_attr($lang[30]) . '" border="0" /></a>
            <a href="index.php?do=manage_games&amp;act=delete_comments&amp;gid=' . escape_attr((string)$category['gid']) . '&amp;csrf_token=' . escape_attr(generate_csrf_token(false)) . '"><img src="./images/comments_delete.png" alt="' . escape_attr($lang[3]) . '" title="' . escape_attr($lang[33]) . '" border="0" /></a>
            <a href="index.php?do=manage_games&amp;act=delete&amp;gid=' . escape_attr((string)$category['gid']) . '&amp;csrf_token=' . escape_attr(generate_csrf_token(false)) . '" onclick="return ConfirmDelete();"><img src="./images/tool_delete.png" alt="' . escape_attr($lang[8]) . '" title="' . escape_attr($lang[8]) . '" border="0" /></a>
        </td>
    </tr>';
    }
    
    echo '
<script type="text/javascript">
    function ConfirmDelete()
    {
        Check = confirm("' . escape_js(trim($lang[10])) . '");
        if (Check)
            return true;
        else
            return false;
    }
</script>
' . show_alert_message('<a href="index.php?do=manage_games&amp;act=add">' . escape_html($lang[9]) . '</a>') . '
' . $message . '
<table cellpadding="5" cellspacing="0" border="0" align="center" width="90%" style="border-collapse:separate" class="tborder">
    <tr>
        <td class="tcat" colspan="8" align="center">
            ' . escape_html($lang[2]) . '
        </td>
    </tr>
    <tr>
        <td class="alt2" align="center">
            ' . escape_html($lang[26]) . '
        </td>
        <td class="alt2">
            ' . escape_html($lang[4]) . '
        </td>
        <td class="alt2">
            ' . escape_html($lang[18]) . '
        </td>
        <td class="alt2" align="center">
            ' . escape_html($lang[19]) . '
        </td>
        <td class="alt2" align="center">
            ' . escape_html($lang[20]) . '
        </td>
        <td class="alt2" align="center">
            ' . escape_html($lang[23]) . '
        </td>
        <td class="alt2" align="center">
            ' . escape_html($lang[5]) . '
        </td>
        <td class="alt2" align="center">
            ' . escape_html($lang[6]) . '
        </td>
    </tr>
    ' . $list . '
</table>';
} catch (Exception $e) {
    error_log('Manage Games Error: ' . $e->getMessage());
    echo show_alert_error('An error occurred. Please try again.');
}

function build_category_select(int $selected, PDO $db): string {
    $html = '<select name="cid">';
    $stmt = $db->query("SELECT cid, cname FROM ts_games_categories ORDER by sort");
    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selectedAttr = $selected == $category['cid'] ? ' selected="selected"' : '';
        $html .= '<option value="' . escape_attr((string)$category['cid']) . '"' . $selectedAttr . '>' . escape_html($category['cname']) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function get_category_name(int $cid, PDO $db): string {
    $stmt = $db->prepare("SELECT cname FROM ts_games_categories WHERE cid = ?");
    $stmt->execute([$cid]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['cname'] ?? '';
}
