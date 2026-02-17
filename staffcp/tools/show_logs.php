<?php
declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

check_staff_auth();

$lang = load_staff_language('show_logs');
$db = get_pdo();
$act = $_GET['act'] ?? $_POST['act'] ?? '';
$message = '';
$found = '';
$pagertop = '';
$limit = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lid']) && count($_POST['lid']) > 0) {
        verify_csrf_token();
        $lids = array_map('intval', $_POST['lid']);
        $placeholders = implode(',', array_fill(0, count($lids), '?'));
        $stmt = $db->prepare("DELETE FROM ts_staffcp_logs WHERE lid IN ($placeholders)");
        $stmt->execute($lids);
    }
    
    if ($act === 'delete_all') {
        verify_csrf_token();
        $db->exec("TRUNCATE TABLE ts_staffcp_logs");
    }
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM ts_staffcp_logs");
    $results = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    list($pagertop, $limit) = build_pagination_links(25, $results, $_SERVER['SCRIPT_NAME'] . '?do=show_logs&amp;');
    
    $stmt = $db->query("SELECT l.*, u.username FROM ts_staffcp_logs l LEFT JOIN users u ON (l.uid = u.id) ORDER by date DESC $limit");
    
    if ($stmt->rowCount() == 0) {
        $message = show_alert_error($lang[1]);
    } else {
        $count = 0;
        while ($log = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $class = $count % 2 == 1 ? 'alt2' : 'alt1';
            $username = $log['uid'] ? '<a href="../userdetails.php?id=' . escape_attr((string)$log['uid']) . '">' . escape_html($log['username']) . '</a>' : escape_html($lang[9]);
            $found .= '
        <tr>
            <td class="' . $class . '">' . $username . '</td>
            <td class="' . $class . '">' . format_timestamp($log['date']) . '</td>
            <td class="' . $class . '">' . escape_html($log['log']) . '</td>
            <td class="' . $class . '" align="center"><input type="checkbox" name="lid[]" value="' . escape_attr((string)$log['lid']) . '" checkme="group" /></td>
        </td>
        ';
            $count++;
        }
        $found .= '
        <tr>
            <td class="tcat2" align="right" colspan="4"><input type="submit" value="' . escape_attr($lang[8]) . '" /></td>
        </tr>';
    }
    
    if ($message) {
        echo '
    ' . $message;
    } else {
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
    
        function ConfirmDelete()
        {
            if (confirm("' . escape_js($lang[11]) . '"))
            {
                window.location.href = "index.php?do=show_logs&act=delete_all&csrf_token=' . escape_js(generate_csrf_token(false)) . '";
            }
            else
            {
                return false;
            }
        }
    </script>
    ' . show_alert_message('<a href="#" onclick="ConfirmDelete();">' . escape_html($lang[10]) . '</a>') . '
    <form action="' . escape_attr($_SERVER['SCRIPT_NAME']) . '?do=show_logs' . (isset($_GET['page']) ? '&page=' . intval($_GET['page']) : '') . '" method="post" name="show_logs">
    ' . generate_csrf_token() . '
    ' . $pagertop . '
    <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
        <tr>
            <td class="tcat" align="center" colspan="4"><b>' . escape_html($lang[3]) . '</b></td>
        </tr>
        <tr>
            <td class="tcat2"><b>' . escape_html($lang[4]) . '</b></td>
            <td class="tcat2"><b>' . escape_html($lang[5]) . '</b></td>
            <td class="tcat2"><b>' . escape_html($lang[6]) . '</b></td>
            <td class="tcat2" align="center"><input type="checkbox" checkall="group" onclick="javascript: return select_deselectAll (\'show_logs\', this, \'group\');"></td>
        </tr>
        ' . $found . '
    </table>
    ' . $pagertop . '
    </form>
    ';
    }
} catch (Exception $e) {
    error_log('Show Logs Error: ' . $e->getMessage());
    echo show_alert_error('An error occurred. Please try again.');
}

function validate_per_page(int $numresults, int &$page, int &$perpage, int $maxperpage = 20, int $defaultperpage = 20): void {
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } elseif ($maxperpage < $perpage) {
        $perpage = $maxperpage;
    }
    
    $totalPages = ceil($numresults / $perpage);
    if ($totalPages == 0) {
        $totalPages = 1;
    }
    
    if ($page < 1) {
        $page = 1;
    } elseif ($totalPages < $page) {
        $page = $totalPages;
    }
}

function calculate_pagination(int $pagenumber, int $perpage, int $total): array {
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    $paginationFirstItem++;
    return ['first' => number_format($paginationFirstItem), 'last' => number_format($paginationLastItem)];
}

function build_pagination_links(int $perpage, int $results, string $address): array {
    if ($results < $perpage) {
        return ['', ''];
    }
    
    $queryResult = $results ? (int)ceil($results / $perpage) : 0;
    
    $pagenumber = isset($_GET['page']) ? intval($_GET['page']) : (isset($_POST['page']) ? intval($_POST['page']) : 1);
    validate_per_page($results, $pagenumber, $perpage, 200);
    
    $limitOffset = ($pagenumber - 1) * $perpage;
    $paginationOffset = $pagenumber * $perpage;
    if ($results < $paginationOffset) {
        $paginationOffset = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    
    $paginationLinks = '';
    if ($results <= $perpage) {
        return ['', 'LIMIT ' . $limitOffset . ', ' . $perpage];
    }
    
    $total = number_format($results);
    $hasPrev = false;
    $hasNext = false;
    $previousPageQuery = '';
    
    if (1 < $pagenumber) {
        $previousPage = $pagenumber - 1;
        $previousPageInfo = calculate_pagination($previousPage, $perpage, $results);
        $hasPrev = true;
        $previousPageQuery = $previousPage != 1 ? 'page=' . $previousPage : '';
    }
    
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextPageInfo = calculate_pagination($nextPageNumber, $perpage, $results);
        $hasNext = true;
    }
    
    $paginationLinks = '
    <table cellpadding="0" cellspacing="0" border="0" class="mainTableNoBorder">
        <tr>
            <td style="padding: 0px 0px 1px 0px;">
                <div style="float: left;" id="navcontainer_f">
                    <ul>
                        <li>' . $pagenumber . ' - ' . $queryResult . '</li>
                        ' . ($hasPrev ? '<li><a class="smalltext" href="' . escape_attr($address . $previousPageQuery) . '" title="Previous Page - Show Results ' . (isset($previousPageInfo) ? escape_attr($previousPageInfo['first'] . ' to ' . $previousPageInfo['last']) : '') . ' of ' . escape_attr($total) . '">&lt;</a></li>' : '') . '
                        ' . ($hasNext ? '<li><a class="smalltext" href="' . escape_attr($address . 'page=' . (isset($nextPageNumber) ? $nextPageNumber : 1)) . '" title="Next Page - Show Results ' . (isset($nextPageInfo) ? escape_attr($nextPageInfo['first'] . ' to ' . $nextPageInfo['last']) : '') . ' of ' . escape_attr($total) . '">&gt;</a></li>' : '') . '
                    </ul>
                </div>
            </td>
        </tr>
    </table>';
    
    return [$paginationLinks, 'LIMIT ' . $limitOffset . ', ' . $perpage];
}
