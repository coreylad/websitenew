<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('nuke_torrent');

$Message = '';
$tid = 0;
$reason = '';

if (isset($_GET['tid'])) {
    $tid = (int)$_GET['tid'];
}
if (isset($_POST['tid'])) {
    $tid = (int)$_POST['tid'];
}

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' && $tid > 0) {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[10] ?? 'Invalid form token');
    } else {
        $reason = trim($_POST['reason'] ?? '');
        
        if ($reason) {
            try {
                $result = $TSDatabase->query(
                    'SELECT name FROM torrents WHERE id = ?',
                    [$tid]
                );
                
                if ($result && $torrent = $result->fetch(PDO::FETCH_ASSOC)) {
                    $TSDatabase->query(
                        'UPDATE torrents SET isnuked = ?, WhyNuked = ? WHERE id = ?',
                        ['yes', $reason, $tid]
                    );
                    
                    $SysMsg = str_replace(
                        ['{1}', '{2}', '{3}'],
                        [$torrent['name'], $_SESSION['ADMIN_USERNAME'], $reason],
                        $Language[7] ?? 'Torrent {1} nuked by {2}. Reason: {3}'
                    );
                    
                    logStaffActionModern($SysMsg);
                    $Message = showAlertSuccessModern($SysMsg);
                } else {
                    $Message = showAlertErrorModern($Language[6] ?? 'Torrent not found');
                }
            } catch (Exception $e) {
                error_log('Nuke torrent error: ' . $e->getMessage());
                $Message = showAlertErrorModern($Language[6] ?? 'Error nuking torrent');
            }
        } else {
            $Message = showAlertErrorModern($Language[9] ?? 'Please enter a reason');
        }
    }
}

?>
<?php echo $Message; ?>
<form method="post" action="index.php?do=nuke_torrent">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" colspan="2" align="center">
            <?php echo escape_html($Language[2] ?? 'Nuke Torrent'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[3] ?? 'Torrent ID'); ?></td>
        <td class="alt1"><input type="text" name="tid" value="<?php echo (int)$tid; ?>" size="10" /></td>
    </tr>
    <tr>
        <td class="alt2" align="right"><?php echo escape_html($Language[8] ?? 'Reason'); ?></td>
        <td class="alt2"><input type="text" name="reason" value="<?php echo escape_attr($reason); ?>" size="50" /></td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" value="<?php echo escape_attr($Language[4] ?? 'Submit'); ?>" /> 
            <input type="reset" value="<?php echo escape_attr($Language[5] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
