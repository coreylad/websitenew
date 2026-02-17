<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('server_info');

// Display phpinfo in iframe
if (isset($_GET['info'])) {
    phpinfo();
    exit;
}

// Output iframe container
?>
<iframe style="width: 99%; height: 99%; border: 0;" frameborder="0" 
        src="<?php echo escape_attr($_SERVER['SCRIPT_NAME'] . '?do=server_info&info=1'); ?>"></iframe>
