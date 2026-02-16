<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

check_staff_auth();

$lang = load_staff_language('promotions');
$db = get_pdo();
$act = $_GET['act'] ?? $_POST['act'] ?? '';
$message = '';

try {
    if ($act === 'delete' && isset($_GET['pid'])) {
        verify_csrf_token();
        $pid = intval($_GET['pid']);
        $stmt = $db->prepare("DELETE FROM ts_promotions WHERE pid = ?");
        $stmt->execute([$pid]);
        log_staff_action(str_replace('{1}', $_SESSION['ADMIN_USERNAME'], $lang[17]));
        $status = '<tr><td class="alt2" align="center" colspan="5"><div class="icon-ok">' . escape_html($lang[18]) . '</div></td></tr>';
    }
    
    if ($act === 'edit' && isset($_GET['pid'])) {
        $pid = intval($_GET['pid']);
        $stmt = $db->prepare("SELECT * FROM ts_promotions WHERE pid = ?");
        $stmt->execute([$pid]);
        
        if ($stmt->rowCount() > 0) {
            $promotion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                verify_csrf_token();
                
                $title = trim($_POST['title'] ?? '');
                $includeUsergroup = intval($_POST['include_usergroup'] ?? 0);
                $promoteTo = intval($_POST['promote_to'] ?? 0);
                $demoteTo = intval($_POST['demote_to'] ?? 0);
                $uploadLimit = floatval($_POST['upload_limit'] ?? 0);
                $ratioLimit = trim($_POST['ratio_limit'] ?? '');
                $minRegDays = intval($_POST['min_reg_days'] ?? 0);
                $posts = intval($_POST['posts'] ?? 0);
                $timesWarned = intval($_POST['times_warned'] ?? 0);
                
                if ($uploadLimit || $ratioLimit || $minRegDays || $posts || $timesWarned) {
                    $stmt = $db->prepare("UPDATE ts_promotions SET title = ?, include_usergroup = ?, promote_to = ?, demote_to = ?, upload_limit = ?, ratio_limit = ?, min_reg_days = ?, posts = ?, times_warned = ? WHERE pid = ?");
                    $stmt->execute([$title, $includeUsergroup, $promoteTo, $demoteTo, $uploadLimit, $ratioLimit, $minRegDays, $posts, $timesWarned, $pid]);
                    log_staff_action($lang[18]);
                    $message = show_alert_error($lang[18]);
                    $updated = true;
                } else {
                    $message = show_alert_error($lang[30]);
                }
            } else {
                $title = $promotion['title'];
                $includeUsergroup = $promotion['include_usergroup'];
                $promoteTo = $promotion['promote_to'];
                $demoteTo = $promotion['demote_to'];
                $uploadLimit = $promotion['upload_limit'];
                $ratioLimit = $promotion['ratio_limit'];
                $minRegDays = $promotion['min_reg_days'];
                $posts = $promotion['posts'];
                $timesWarned = $promotion['times_warned'];
            }
            
            if (!isset($updated)) {
                $formHtml = '
            <form method="post" action="' . escape_attr($_SERVER['SCRIPT_NAME']) . '?do=promotions&amp;act=edit&amp;pid=' . escape_attr((string)$pid) . '">
            ' . generate_csrf_token() . '
            ' . show_alert_message('<a href="index.php?do=promotions">' . escape_html($lang[2]) . '</a>') . '
            ' . $message . '
            <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
                <tr>
                    <td class="tcat" align="center" colspan="3">
                        ' . escape_html($lang[20]) . '
                    </td>
                </tr>
                <tr>
                    <td class="alt2" colspan="2">' . escape_html($lang[3]) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[26]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        <input type="text" name="title" value="' . escape_attr($title) . '" size="50" />
                    </td>
                </tr>
                <tr>
                    <td class="alt2" colspan="2">' . escape_html($lang[7]) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[12]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        ' . build_usergroup_select($includeUsergroup, 'include_usergroup', $db) . '
                    </td>
                </tr>
                <tr>
                    <td class="alt2" colspan="2">' . ($promotion['type'] == 'promote' ? escape_html($lang[4]) : escape_html($lang[5])) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[27]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        ' . build_usergroup_select($promotion['type'] == 'promote' ? $promoteTo : $demoteTo, $promotion['type'] == 'promote' ? 'promote_to' : 'demote_to', $db) . '
                    </td>
                </tr>
                ' . ($promotion['type'] == 'promote' ? '
                <tr>
                    <td class="alt2" colspan="2">' . escape_html($lang[8]) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[16]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        <input type="text" name="upload_limit" value="' . escape_attr((string)$uploadLimit) . '" size="10" /> GB
                    </td>
                </tr>
                <tr>
                    <td class="alt2" colspan="2">' . escape_html($lang[9]) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[15]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        <input type="text" name="ratio_limit" value="' . escape_attr($ratioLimit) . '" size="10" />
                    </td>
                </tr>
                <tr>
                    <td class="alt2" colspan="2">' . escape_html($lang[10]) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[13]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        <input type="text" name="min_reg_days" value="' . escape_attr((string)$minRegDays) . '" size="10" />
                    </td>
                </tr>
                <tr>
                    <td class="alt2" colspan="2">' . escape_html($lang[11]) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[14]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        <input type="text" name="posts" value="' . escape_attr((string)$posts) . '" size="10" />
                    </td>
                </tr>
                <input type="hidden" name="times_warned" value="0" />
                ' : '
                <tr>
                    <td class="alt2" colspan="2">' . escape_html($lang[9]) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[15]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        <input type="text" name="ratio_limit" value="' . escape_attr($ratioLimit) . '" size="10" />
                    </td>
                </tr>
                <tr>
                    <td class="alt2" colspan="2">' . escape_html($lang[32]) . '</td>
                </tr>
                <tr>
                    <td class="alt1" width="50%" align="justify">
                        ' . escape_html($lang[31]) . '
                    </td>
                    <td class="alt1" width="50%" valign="top">
                        <input type="text" name="times_warned" value="' . escape_attr((string)$timesWarned) . '" size="10" />
                    </td>
                </tr>
                <input type="hidden" name="upload_limit" value="0" />
                <input type="hidden" name="min_reg_days" value="0" />
                <input type="hidden" name="posts" value="0" />
                ') . '
                <tr>
                    <td class="tcat2"></td>
                    <td class="tcat2">
                        <input type="submit" value="' . escape_attr($lang[28]) . '" /> <input type="reset" value="' . escape_attr($lang[29]) . '" />
                    </td>
                </tr>
            </table>
            </form>
            ';
                echo $formHtml;
            }
        } else {
            $message = show_alert_error($lang[24]);
        }
    }
    
    if ($act === 'new') {
        $title = '';
        $includeUsergroup = 0;
        $promoteTo = 0;
        $demoteTo = 0;
        $uploadLimit = 0;
        $ratioLimit = 0;
        $minRegDays = 0;
        $posts = 0;
        $timesWarned = 0;
        $type = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf_token();
            
            $title = trim($_POST['title'] ?? '');
            $includeUsergroup = intval($_POST['include_usergroup'] ?? 0);
            $promoteTo = intval($_POST['promote_to'] ?? 0);
            $demoteTo = intval($_POST['demote_to'] ?? 0);
            $uploadLimit = floatval($_POST['upload_limit'] ?? 0);
            $ratioLimit = trim($_POST['ratio_limit'] ?? '');
            $minRegDays = intval($_POST['min_reg_days'] ?? 0);
            $posts = intval($_POST['posts'] ?? 0);
            $timesWarned = intval($_POST['times_warned'] ?? 0);
            $type = $_POST['type'] ?? '';
            
            if (($type == 'promote' || $type == 'demote') && ($uploadLimit || $ratioLimit || $minRegDays || $posts || $timesWarned)) {
                $stmt = $db->prepare("INSERT INTO ts_promotions (type, title, include_usergroup, promote_to, demote_to, upload_limit, ratio_limit, min_reg_days, posts, times_warned) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$type, $title, $includeUsergroup, $promoteTo, $demoteTo, $uploadLimit, $ratioLimit, $minRegDays, $posts, $timesWarned]);
                log_staff_action($lang[33]);
                $message = show_alert_error($lang[33]);
                $updated = true;
            } else {
                $message = show_alert_error($lang[30]);
            }
        }
        
        if (!isset($updated)) {
            echo '
        <script type="text/javascript">
            function TSGetID(id) { return document.getElementById(id); }
            function update_promotion_tab(selected) {
                TSGetID("promotetab").style.display = "none";
                TSGetID("demotetab").style.display = "none";
                if (selected != "") {
                    TSGetID(selected+"tab").style.display = "inline";
                }
            }
        </script>
        <form method="post" action="' . escape_attr($_SERVER['SCRIPT_NAME']) . '?do=promotions&amp;act=new">
        ' . generate_csrf_token() . '
        ' . show_alert_message('<a href="index.php?do=promotions">' . escape_html($lang[2]) . '</a>') . '
        ' . $message . '
        <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
            <tr>
                <td class="tcat" align="center" colspan="3">
                    ' . escape_html($lang[25]) . '
                </td>
            </tr>
            <tr>
                <td class="alt2" colspan="2">' . escape_html($lang[3]) . '</td>
            </tr>
            <tr>
                <td class="alt1" width="50%" align="justify">
                    ' . escape_html($lang[26]) . '
                </td>
                <td class="alt1" width="50%" valign="top">
                    <input type="text" name="title" value="' . escape_attr($title) . '" size="50" />
                </td>
            </tr>
            <tr>
                <td class="alt2" colspan="2">' . escape_html($lang[7]) . '</td>
            </tr>
            <tr>
                <td class="alt1" width="50%" align="justify">
                    ' . escape_html($lang[12]) . '
                </td>
                <td class="alt1" width="50%" valign="top">
                    ' . build_usergroup_select($includeUsergroup, 'include_usergroup', $db) . '
                </td>
            </tr>
            <tr>
                <td class="alt2" colspan="2">' . escape_html($lang[6]) . '</td>
            </tr>
            <tr>
                <td class="alt1" width="50%" align="justify">
                    ' . escape_html($lang[36]) . '
                </td>
                <td class="alt1" width="50%" valign="top">
                    <select name="type" onchange="update_promotion_tab(this.value);">
                        <option value="">' . escape_html($lang[36]) . '</option>
                        <option value="promote"' . ($type == 'promote' ? ' selected="selected"' : '') . '>' . escape_html($lang[34]) . '</option>
                        <option value="demote"' . ($type == 'demote' ? ' selected="selected"' : '') . '>' . escape_html($lang[35]) . '</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="alt2" colspan="3">
                    <div id="promotetab" style="display: none;">
                        <table cellspacing="0" cellpadding="0" border="0" class="mainTableNoBorder">
                            <tr><td class="alt2" colspan="2">' . escape_html($lang[4]) . '</td></tr>
                            <tr><td class="alt1" width="50%" align="justify">' . escape_html($lang[12]) . '</td><td class="alt1" width="50%" valign="top"><div id="promote_to"></div></td></tr>
                            <tr><td class="alt2" colspan="2">' . escape_html($lang[8]) . '</td></tr>
                            <tr><td class="alt1" width="50%" align="justify">' . escape_html($lang[16]) . '</td><td class="alt1" width="50%" valign="top"><div id="upload_limit"></div></td></tr>
                            <tr><td class="alt2" colspan="2">' . escape_html($lang[9]) . '</td></tr>
                            <tr><td class="alt1" width="50%" align="justify">' . escape_html($lang[15]) . '</td><td class="alt1" width="50%" valign="top"><div id="ratio_limit"></div></td></tr>
                            <tr><td class="alt2" colspan="2">' . escape_html($lang[10]) . '</td></tr>
                            <tr><td class="alt1" width="50%" align="justify">' . escape_html($lang[13]) . '</td><td class="alt1" width="50%" valign="top"><div id="min_reg_days"></div></td></tr>
                            <tr><td class="alt2" colspan="2">' . escape_html($lang[11]) . '</td></tr>
                            <tr><td class="alt1" width="50%" align="justify">' . escape_html($lang[14]) . '</td><td class="alt1" width="50%" valign="top"><div id="posts"></div></td></tr>
                        </table>
                    </div>
                    <div id="demotetab" style="display: none;">
                        <table cellspacing="0" cellpadding="0" border="0" class="mainTableNoBorder">
                            <tr><td class="alt2" colspan="2">' . escape_html($lang[5]) . '</td></tr>
                            <tr><td class="alt1" width="50%" align="justify">' . escape_html($lang[12]) . '</td><td class="alt1" width="50%" valign="top"><div id="demote_to"></div></td></tr>
                            <tr><td class="alt2" colspan="2">' . escape_html($lang[9]) . '</td></tr>
                            <tr><td class="alt1" width="50%" align="justify">' . escape_html($lang[15]) . '</td><td class="alt1" width="50%" valign="top"><div id="ratio_limit2"></div></td></tr>
                            <tr><td class="alt2" colspan="2">' . escape_html($lang[32]) . '</td></tr>
                            <tr><td class="alt1" width="50%" align="justify">' . escape_html($lang[31]) . '</td><td class="alt1" width="50%" valign="top"><div id="times_warned"></div></td></tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="tcat2"></td>
                <td class="tcat2">
                    <input type="submit" value="' . escape_attr($lang[28]) . '" /> <input type="reset" value="' . escape_attr($lang[29]) . '" />
                </td>
            </tr>
        </table>
        </form>
        ';
        } else {
            $message = show_alert_error($lang[33]);
        }
    }
    
    if (!isset($formHtml)) {
        $list = '';
        $promotions = [];
        $demotions = [];
        $stmt = $db->query("SELECT * FROM ts_promotions");
        while ($promotion = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($promotion['type'] == 'promote') {
                $promotions[] = $promotion;
            } else {
                $demotions[] = $promotion;
            }
        }
        
        if (count($promotions) > 0) {
            $list = '
        <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
            <tr><td class="tcat" align="center" colspan="8">' . escape_html($lang[2]) . '</td></tr>
            <tr>
                <td class="alt2"><b>' . escape_html($lang[3]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[7]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[4]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[8]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[9]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[10]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[11]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[19]) . '</b></td>
            </tr>';
            foreach ($promotions as $promote) {
                $list .= '
                <tr>
                    <td class="alt1">' . escape_html($promote['title']) . '</td>
                    <td class="alt1">' . get_usergroup_name($promote['include_usergroup'], $db) . '</td>
                    <td class="alt1">' . get_usergroup_name($promote['promote_to'], $db) . '</td>
                    <td class="alt1">' . escape_html((string)$promote['upload_limit']) . ' GB</td>
                    <td class="alt1">' . escape_html($promote['ratio_limit']) . '</td>
                    <td class="alt1">' . escape_html((string)$promote['min_reg_days']) . '</td>
                    <td class="alt1">' . escape_html((string)$promote['posts']) . '</td>
                    <td class="alt1">
                        <a href="index.php?do=promotions&amp;act=edit&amp;pid=' . escape_attr((string)$promote['pid']) . '"><img src="images/tool_edit.png" alt="' . escape_attr(trim($lang[20])) . '" title="' . escape_attr(trim($lang[20])) . '" border="0" /></a>
                        <a href="#" onclick="ConfirmDelete(' . $promote['pid'] . ');"><img src="images/tool_delete.png" alt="' . escape_attr(trim($lang[21])) . '" title="' . escape_attr(trim($lang[21])) . '" border="0" /></a>
                    </td>
                </tr>
                ';
            }
            $list .= '</table>';
        }
        
        if (count($demotions) > 0) {
            $list .= '
        <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
            <tr><td class="tcat" align="center" colspan="6">' . escape_html($lang[23]) . '</td></tr>
            <tr>
                <td class="alt2"><b>' . escape_html($lang[3]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[7]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[5]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[9]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[32]) . '</b></td>
                <td class="alt2"><b>' . escape_html($lang[19]) . '</b></td>
            </tr>';
            foreach ($demotions as $demote) {
                $list .= '
                <tr>
                    <td class="alt1">' . escape_html($demote['title']) . '</td>
                    <td class="alt1">' . get_usergroup_name($demote['include_usergroup'], $db) . '</td>
                    <td class="alt1">' . get_usergroup_name($demote['demote_to'], $db) . '</td>
                    <td class="alt1">' . escape_html($demote['ratio_limit']) . '</td>
                    <td class="alt1">' . escape_html((string)$demote['times_warned']) . '</td>
                    <td class="alt1">
                        <a href="index.php?do=promotions&amp;act=edit&amp;pid=' . escape_attr((string)$demote['pid']) . '"><img src="images/tool_edit.png" alt="' . escape_attr(trim($lang[20])) . '" title="' . escape_attr(trim($lang[20])) . '" border="0" /></a>
                        <a href="#" onclick="ConfirmDelete(' . $demote['pid'] . ');"><img src="images/tool_delete.png" alt="' . escape_attr(trim($lang[21])) . '" title="' . escape_attr(trim($lang[21])) . '" border="0" /></a>
                    </td>
                </tr>
                ';
            }
            $list .= '</table>';
        }
        
        echo '
    <script type="text/javascript">
        function ConfirmDelete(pID) {
            if (confirm("' . escape_js(trim($lang[22])) . '")) {
                window.location.href = "index.php?do=promotions&act=delete&pid="+pID+"&csrf_token=' . escape_js(generate_csrf_token(false)) . '";
            } else {
                return false;
            }
        }
    </script>
    ' . show_alert_message('<span style="float: right;"><a href="index.php?do=promotions&amp;act=new">' . escape_html($lang[25]) . '</a></span><a href="index.php?do=manage_cronjobs">' . escape_html($lang[37]) . '</a>') . '
    ' . $message . $list;
    }
} catch (Exception $e) {
    error_log('Promotions Error: ' . $e->getMessage());
    echo show_alert_error('An error occurred. Please try again.');
}

function build_usergroup_select(int $ug, string $name, PDO $db): string {
    $html = '<select name="' . escape_attr($name) . '">';
    $stmt = $db->query("SELECT gid, title, namestyle FROM usergroups");
    while ($group = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = $group['gid'] == $ug ? ' selected="selected"' : '';
        $html .= '<option value="' . escape_attr((string)$group['gid']) . '"' . $selected . '>' . escape_html(str_replace('{username}', $group['title'], strip_tags($group['namestyle']))) . '</option>';
    }
    $html .= '</select>';
    return $html;
}

function get_usergroup_name(int $ug, PDO $db): string {
    $stmt = $db->prepare("SELECT title, namestyle FROM usergroups WHERE gid = ?");
    $stmt->execute([$ug]);
    $row = $stmt->fetch(PDO::FETCH_ROW);
    if ($row) {
        return str_replace('{username}', $row[0], $row[1]);
    }
    return '';
}
