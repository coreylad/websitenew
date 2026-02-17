<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Language = loadStaffLanguage('advertising');

$Message = '';
$Ads = '';

try {
    $result = $TSDatabase->query("SELECT content FROM ts_cache WHERE cachename = ?", ['ads']);
    if ($result && $row = $result->fetch(PDO::FETCH_ASSOC)) {
        $Ads = $row['content'];
    }
} catch (Exception $e) {
    error_log('Get ads error: ' . $e->getMessage());
}

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[10] ?? 'Invalid form token');
    } else {
        $Ads = trim($_POST['ads'] ?? '');
        
        try {
            $TSDatabase->query(
                "REPLACE INTO ts_cache VALUES (?, ?, ?)",
                ['ads', $Ads, time()]
            );
            
            $SysMsg = str_replace(
                ['{1}', '{2}'],
                ['Ads', $_SESSION['ADMIN_USERNAME']],
                $Language[3] ?? '{1} updated by {2}'
            );
            
            logStaffActionModern($SysMsg);
            $Message = showAlertSuccessModern($SysMsg);
        } catch (Exception $e) {
            error_log('Update ads error: ' . $e->getMessage());
            $Message = showAlertErrorModern('Failed to update ads');
        }
    }
}

function loadTinyMCEEditor(int $type = 1, string $mode = 'textareas', string $elements = ''): string
{
    define('EDITOR_TYPE', $type);
    define('TINYMCE_MODE', $mode);
    define('TINYMCE_ELEMENTS', $elements);
    define('WORKPATH', './../scripts/');
    define('TINYMCE_EMOTIONS_URL', './../tinymce_emotions.php');
    
    ob_start();
    include './../tinymce.php';
    $editorContent = ob_get_contents();
    ob_end_clean();
    
    return $editorContent !== false ? $editorContent : '';
}

echo loadTinyMCEEditor(1, 'exact', 'ads');
?>
<?php echo $Message; ?>
<form method="post" action="index.php?do=advertising">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center">
            <?php echo escape_html($Language[2] ?? 'Advertising'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt2" align="center">
            <?php echo escape_html($Language[6] ?? 'Manage advertising content'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt1">
            <textarea name="ads" id="ads" style="width: 99%; height: 300px;"><?php echo escape_html($Ads); ?></textarea>
            <p><a href="javascript:toggleEditor('ads');"><img src="images/tool_refresh.png" border="0" /></a></p>
        </td>
    </tr>
    <tr>
        <td class="tcat2" align="center">
            <input type="submit" value="<?php echo escape_attr($Language[4] ?? 'Update'); ?>" /> 
            <input type="reset" value="<?php echo escape_attr($Language[5] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
