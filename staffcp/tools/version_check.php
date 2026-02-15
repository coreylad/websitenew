<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Process version check request
if (isset($_GET['checkVersion'])) {
    // Check if function_269 exists (legacy version checker)
    $currentVersion = defined('SHORT_SCRIPT_VERSION') ? SHORT_SCRIPT_VERSION : '0.0';
    $latestVersion = '0.0';
    
    // Try to get latest version if function exists
    if (function_exists('function_269')) {
        function_269();
        $latestVersion = defined('LATEST_VERSION') ? LATEST_VERSION : '0.0';
    }
    
    // Determine version status
    $status = 'UNKNOWN';
    $image = 'alert.png';
    $suggestion = '';
    
    if (version_compare($latestVersion, $currentVersion, '<')) {
        $status = 'FAKE';
        $image = 'alert.png';
        $suggestion = 'Please visit <a href="https://templateshares.net">www.templateshares.net</a> for more information!';
    }
    elseif (version_compare($currentVersion, $latestVersion, '<')) {
        $status = 'OLD';
        $image = 'alert.png';
        $suggestion = 'Please visit <a href="https://templateshares.net">www.templateshares.net</a> for more information!';
    }
    elseif (version_compare($currentVersion, $latestVersion, '==')) {
        $status = 'LATEST';
        $image = 'accept.png';
        $suggestion = '';
    }
    
    // Output result
    $output = '
    <img src="images/tree_ltr.gif" border="0" alt="" title="" style="vertical-align: middle;" /> Your Version: <b>TS SE v.' . escape_html($currentVersion) . '</b><br />
    <img src="images/tree_ltr.gif" border="0" alt="" title="" style="vertical-align: middle;" /> Latest Version: <b>TS SE v.' . escape_html($latestVersion) . '</b><br /><br />
    <img src="images/' . escape_attr($image) . '" border="0" alt="" title="" style="vertical-align: middle;" /> You are currently using the <b>' . escape_html($status) . '</b> of TS Special Edition. ' . $suggestion;
    
    exit($output);
}

// Output version check interface
?>
<script type="text/javascript">
    $(document).ready(function() {
        setTimeout(function() {
            $.get('index.php?do=version_check&checkVersion=true', function(response) {
                $('.alt1').html(response);
            });
        }, 1000);
    });
</script>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center">Version Check</td>
    </tr>
    <tr>
        <td class="alt1">
            <img src="./images/fb_ajax-loader.gif" style="vertical-align: middle;" alt="" title="" /> Checking...
        </td>
    </tr>
</table>
