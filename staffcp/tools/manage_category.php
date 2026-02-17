<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Get parameters
$Act = isset($_GET['act']) ? trim($_GET['act']) : (isset($_POST['act']) ? trim($_POST['act']) : '');
$Cid = isset($_GET['cid']) ? (int)$_GET['cid'] : (isset($_POST['cid']) ? (int)$_POST['cid'] : 0);

// Load language
$Language = loadStaffLanguage('manage_category');

// Initialize variables
$Message = '';
$name = '';
$sort = 0;

try {
    // Get category details
    $result = $TSDatabase->query('SELECT name, sort FROM ts_staffcp WHERE cid = ?', [$Cid]);
    
    if (!$result || !($Category = $result->fetch(PDO::FETCH_ASSOC))) {
        echo showAlertErrorModern($Language[11] ?? 'Category not found');
        exit;
    }
    
    $name = $Category['name'];
    $sort = (int)$Category['sort'];
    
    // Handle delete action
    if ($Act === 'delete' && $Cid) {
        // Validate form token for delete
        if (!validateFormToken($_GET['token'] ?? '')) {
            echo showAlertErrorModern('Invalid delete token');
            exit;
        }
        
        // Delete category and related tools
        $TSDatabase->beginTransaction();
        
        try {
            $TSDatabase->query('DELETE FROM ts_staffcp_tools WHERE cid = ?', [$Cid]);
            $TSDatabase->query('DELETE FROM ts_staffcp WHERE cid = ?', [$Cid]);
            
            $TSDatabase->commit();
            
            $SysMsg = str_replace(['{1}', '{2}'], [$Category['name'], $_SESSION['ADMIN_USERNAME']], 
                                $Language[4] ?? 'Category {1} deleted by {2}');
            logStaffActionModern($SysMsg);
            
            redirectTo('index.php?do=manage_tools');
            exit;
        } catch (Exception $e) {
            $TSDatabase->rollback();
            throw $e;
        }
    }
    
    // Handle form submission
    if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
        // Validate form token
        if (!validateFormToken($_POST['form_token'] ?? '')) {
            $Message = showAlertErrorModern($Language[9] ?? 'Invalid form token');
        }
        else {
            $name = trim($_POST['name'] ?? '');
            $sort = (int)($_POST['sort'] ?? 0);
            
            if (empty($name)) {
                $Message = showAlertErrorModern($Language[9] ?? 'Category name is required');
            }
            else {
                $TSDatabase->query(
                    'UPDATE ts_staffcp SET name = ?, sort = ? WHERE cid = ?',
                    [$name, $sort, $Cid]
                );
                
                $SysMsg = str_replace(['{1}', '{2}'], [$name, $_SESSION['ADMIN_USERNAME']], 
                                    $Language[3] ?? 'Category {1} updated by {2}');
                logStaffActionModern($SysMsg);
                
                redirectTo('index.php?do=manage_tools');
                exit;
            }
        }
    }
} catch (Exception $e) {
    error_log('Manage category error: ' . $e->getMessage());
    echo showAlertErrorModern($Language[10] ?? 'An error occurred');
    exit;
}

// Output form
?>
<?php echo $Message; ?>
<form method="post" action="index.php?do=manage_category&act=edit&cid=<?php echo escape_attr((string)$Cid); ?>">
<?php echo getFormTokenField(); ?>
<table cellpadding="0" cellspacing="0" border="0" class="tborder">
    <tr>
        <td class="tcat" colspan="2" align="center">
            <?php echo escape_html($Language[2] ?? 'Manage Category'); ?>
        </td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[5] ?? 'Name:'); ?></td>
        <td class="alt1">
            <input type="text" name="name" 
                   value="<?php echo escape_attr($name); ?>" 
                   size="40" />
        </td>
    </tr>
    <tr>
        <td class="alt1"><?php echo escape_html($Language[6] ?? 'Sort Order:'); ?></td>
        <td class="alt1">
            <input type="text" name="sort" 
                   value="<?php echo escape_attr((string)$sort); ?>" 
                   size="40" />
        </td>
    </tr>
    <tr>
        <td class="tcat2"></td>
        <td class="tcat2">
            <input type="submit" value="<?php echo escape_attr($Language[7] ?? 'Update'); ?>" />
            <input type="reset" value="<?php echo escape_attr($Language[8] ?? 'Reset'); ?>" />
        </td>
    </tr>
</table>
</form>
