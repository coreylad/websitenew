<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('new_category');

// Initialize variables
$Message = '';
$name = '';
$sort = 0;

// Process form submission
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // Validate form token
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern($Language[8] ?? 'Invalid form token');
    }
    else {
        $name = trim($_POST['name'] ?? '');
        $sort = (int)($_POST['sort'] ?? 0);
        
        if (empty($name)) {
            $Message = showAlertErrorModern($Language[8] ?? 'Category name is required');
        }
        else {
            try {
                // Insert new category
                $TSDatabase->query(
                    'INSERT INTO ts_staffcp (name, sort) VALUES (?, ?)',
                    [$name, $sort]
                );
                
                if ($TSDatabase->rowCount() > 0) {
                    $SysMsg = str_replace(['{1}', '{2}'], [$name, $_SESSION['ADMIN_USERNAME']], 
                                        $Language[3] ?? 'Category {1} created by {2}');
                    logStaffActionModern($SysMsg);
                    redirectTo('index.php?do=manage_tools');
                    exit;
                }
                
                $Message = showAlertErrorModern($Language[9] ?? 'Failed to create category');
            } catch (Exception $e) {
                error_log('New category error: ' . $e->getMessage());
                $Message = showAlertErrorModern($Language[9] ?? 'Failed to create category');
            }
        }
    }
}

// Output form
?>
<form method="post" action="index.php?do=new_category">
<?php echo getFormTokenField(); ?>
<?php echo $Message; ?>
<table cellpadding="0" cellspacing="0" border="0" class="tborder">
    <tr>
        <td class="tcat" colspan="2" align="center">
            <?php echo escape_html($Language[2] ?? 'New Category'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[4] ?? 'Name:'); ?></td>
        <td class="alt1">
            <input type="text" name="name" 
                   value="<?php echo escape_attr($name); ?>" 
                   size="40" />
        </td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[5] ?? 'Sort Order:'); ?></td>
        <td class="alt1">
            <input type="text" name="sort" 
                   value="<?php echo escape_attr((string)$sort); ?>" 
                   size="40" />
        </td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" value="<?php echo escape_attr($Language[6] ?? 'Create'); ?>" />
            <input type="reset" value="<?php echo escape_attr($Language[7] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
