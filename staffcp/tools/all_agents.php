<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('all_agents');

$Message = '';
$STOP = false;

try {
    $result = $TSDatabase->query("SELECT content FROM ts_config WHERE configname = ?", ['ANNOUNCE']);
    $row = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
    $ANNOUNCE = $row ? unserialize($row['content']) : ['xbt_active' => 'no', 'allowed_clients' => ''];
    
    if (($ANNOUNCE['xbt_active'] ?? 'no') === 'yes') {
        echo showAlertErrorModern($Language[1] ?? 'XBT tracker is active');
        $STOP = true;
    }
} catch (Exception $e) {
    error_log('All agents error: ' . $e->getMessage());
    $ANNOUNCE = ['xbt_active' => 'no', 'allowed_clients' => ''];
}

if (!$STOP) {
    if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            $Message = showAlertErrorModern($Language[9] ?? 'Invalid form token');
        } elseif (isset($_POST['allowed_clients']) && !empty($_POST['allowed_clients'])) {
            try {
                $ANNOUNCE['allowed_clients'] = implode(',', $_POST['allowed_clients']);
                $TSDatabase->query(
                    "REPLACE INTO ts_config VALUES (?, ?)",
                    ['ANNOUNCE', serialize($ANNOUNCE)]
                );
                $Message = showAlertSuccessModern($Language[8] ?? 'Settings updated');
            } catch (Exception $e) {
                error_log('Update agents error: ' . $e->getMessage());
                $Message = showAlertErrorModern('Failed to update settings');
            }
        }
    }
    
    $Found = '';
    try {
        $result = $TSDatabase->query("SELECT agent, peer_id FROM peers GROUP BY agent DESC");
        if ($result) {
            $allowed_clients = explode(',', $ANNOUNCE['allowed_clients'] ?? '');
            $DONE = [];
            
            while ($R = $result->fetch(PDO::FETCH_ASSOC)) {
                $Peer_ID = htmlspecialchars(substr($R['peer_id'], 0, 8));
                if (!in_array($Peer_ID, $DONE, true)) {
                    $DONE[] = $Peer_ID;
                    $checked = in_array($Peer_ID, $allowed_clients, true) ? ' checked="checked"' : '';
                    $Found .= '<tr>
                        <td class="alt1">' . $Peer_ID . '</td>
                        <td class="alt1">' . escape_html($R['agent']) . '</td>
                        <td class="alt1" align="center">
                            <input type="checkbox" name="allowed_clients[]" value="' . escape_attr($Peer_ID) . '" checkme="group"' . $checked . ' />
                        </td>
                    </tr>';
                }
            }
        }
        
        if (!$Found) {
            echo showAlertErrorModern($Language[3] ?? 'No agents found');
            $STOP = true;
        }
    } catch (Exception $e) {
        error_log('Get peers error: ' . $e->getMessage());
        echo showAlertErrorModern($Language[3] ?? 'No agents found');
        $STOP = true;
    }
    
    if (!$STOP) {
?>
    <?php echo $Message; ?>
    <script type="text/javascript">
        function select_deselectAll(formname, elm, group) {
            var frm = document.forms[formname];
            for(var i = 0; i < frm.length; i++) {
                if(elm.attributes["checkall"] != null && elm.attributes["checkall"].value == group) {
                    if(frm.elements[i].attributes["checkme"] != null && frm.elements[i].attributes["checkme"].value == group) {
                        frm.elements[i].checked = elm.checked;
                    }
                } else if(frm.elements[i].attributes["checkme"] != null && frm.elements[i].attributes["checkme"].value == group) {
                    if(frm.elements[i].checked == false) {
                        frm.elements[1].checked = false;
                    }
                }
            }
        }
    </script>
    <?php echo showAlertInfoModern($Language[8] ?? 'Select allowed clients'); ?>
    <form action="<?php echo escape_attr($_SERVER['SCRIPT_NAME']); ?>?do=all_agents" method="post" name="all_agents">
    <?php echo getFormTokenField(); ?>
    <table cellpadding="0" cellspacing="0" border="0" class="mainTable">
        <tr>
            <td class="tcat" align="center" colspan="3"><?php echo escape_html($Language[2] ?? 'Allowed Agents'); ?></td>
        </tr>
        <tr>
            <td class="alt2"><?php echo escape_html($Language[4] ?? 'Peer ID'); ?></td>
            <td class="alt2"><?php echo escape_html($Language[5] ?? 'Agent'); ?></td>
            <td class="alt2" align="center">
                <input type="checkbox" checkall="group" onclick="javascript: return select_deselectAll('all_agents', this, 'group');">
            </td>
        </tr>
        <?php echo $Found; ?>
        <tr>
            <td class="tcat2" align="right" colspan="3">
                <input type="submit" value="<?php echo escape_attr($Language[6] ?? 'Update'); ?>" /> 
                <input type="reset" value="<?php echo escape_attr($Language[7] ?? 'Reset'); ?>" />
            </td>
        </tr>
    </table>
    </form>
<?php
    }
}
