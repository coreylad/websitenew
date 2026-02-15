<?php

declare(strict_types=1);

// Load modern staffcp helpers
require_once __DIR__ . '/../staffcp_modern.php';

// Check authentication
checkStaffAuthentication();

// Load language
$Language = loadStaffLanguage('cache');

// Initialize variables
$Act = isset($_GET['act']) ? trim($_GET['act']) : (isset($_POST['act']) ? trim($_POST['act']) : '');
$Message = '';

// Process cache rebuild request
if (isset($_GET['cache'])) {
    try {
        // Get configuration
        $result = $TSDatabase->query("SELECT content FROM ts_config WHERE configname = 'MAIN'");
        $configRow = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
        $MAIN = $configRow ? unserialize($configRow['content']) : [];
        
        $cacheDir = '../' . ($MAIN['cache'] ?? 'cache');
        
        // Rebuild Categories Cache
        echo showCacheStart('Categories');
        
        $categoriesC = [];
        $result = $TSDatabase->query("SELECT * FROM categories WHERE type = 'c' ORDER BY name, id");
        if ($result) {
            while ($c = $result->fetch(PDO::FETCH_ASSOC)) {
                $categoriesC[] = $c;
            }
        }
        
        $categoriesS = [];
        $result = $TSDatabase->query("SELECT * FROM categories WHERE type = 's' ORDER BY name, id");
        if ($result) {
            while ($c = $result->fetch(PDO::FETCH_ASSOC)) {
                $categoriesS[] = $c;
            }
        }
        
        $content = var_export($categoriesC, true);
        $content2 = var_export($categoriesS, true);
        $filename = $cacheDir . '/categories.php';
        $cachecontents = "<?php\n/** TS Generated Cache#7 - Do Not Alter\n * Cache Name: Categories\n * Generated: " . gmdate('r') . "\n*/\n\n";
        $cachecontents .= "\$_categoriesC = " . $content . ";\n\n";
        $cachecontents .= "\$_categoriesS = " . $content2 . ";\n?>";
        
        if (file_put_contents($filename, $cachecontents)) {
            echo showCacheSuccess();
        } else {
            echo showCacheError();
        }
        
        // Rebuild Smilies Cache
        echo showCacheStart('Smilies');
        
        $smilieArray = [];
        $result = $TSDatabase->query("SELECT stext, spath FROM ts_smilies ORDER BY sorder, stitle");
        if ($result) {
            while ($sml = $result->fetch(PDO::FETCH_ASSOC)) {
                $smilieArray[$sml['stext']] = $sml['spath'];
            }
        }
        
        $filename = $cacheDir . '/smilies.php';
        $cachecontents = "<?php\n/** TS Generated Cache#14 - Do Not Alter\n * Cache Name: Smilies\n * Generated: " . gmdate('r') . "\n*/\n";
        $cachecontents .= "\$smilies = " . var_export($smilieArray, true) . ";\n?>";
        
        if (file_put_contents($filename, $cachecontents)) {
            echo showCacheSuccess();
        } else {
            echo showCacheError();
        }
        
        // Rebuild Index Stats Cache
        echo showCacheStart('Index Stats');
        
        // Get announce configuration
        $result = $TSDatabase->query("SELECT content FROM ts_config WHERE configname = 'ANNOUNCE'");
        $announceRow = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
        $ANNOUNCE = $announceRow ? unserialize($announceRow['content']) : [];
        
        // Get torrents count
        $result = $TSDatabase->query("SELECT COUNT(id) as count FROM torrents");
        $row = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
        $torrents = $row['count'];
        
        // Get seeders and leechers
        if (($ANNOUNCE['xbt_active'] ?? 'no') === 'yes') {
            $result = $TSDatabase->query("SELECT COUNT(1) as count FROM xbt_files_users WHERE `left` = 0 AND active = 1");
            $row = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
            $seeders = $row['count'];
            
            $result = $TSDatabase->query("SELECT COUNT(1) as count FROM xbt_files_users WHERE `left` > 0 AND active = 1");
            $row = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
            $leechers = $row['count'];
        } else {
            $result = $TSDatabase->query("SELECT COUNT(id) as count FROM peers WHERE seeder='yes'");
            $row = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
            $seeders = $row['count'];
            
            $result = $TSDatabase->query("SELECT COUNT(id) as count FROM peers WHERE seeder='no'");
            $row = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
            $leechers = $row['count'];
        }
        
        // Include external peers if configured
        if (isset($MAIN['includeexpeers']) && $MAIN['includeexpeers'] === 'yes') {
            $result = $TSDatabase->query("SELECT SUM(leechers) as leechers, SUM(seeders) as seeders FROM torrents WHERE ts_external = 'yes'");
            if ($result) {
                $row = $result->fetch(PDO::FETCH_ASSOC);
                $leechers += (int)($row['leechers'] ?? 0);
                $seeders += (int)($row['seeders'] ?? 0);
            }
        }
        
        $peers = $seeders + $leechers;
        
        // Get user stats
        $result = $TSDatabase->query("SELECT SUM(downloaded) AS totaldl, SUM(uploaded) AS totalul, COUNT(id) AS totaluser FROM users");
        $row = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['totaldl' => 0, 'totalul' => 0, 'totaluser' => 0];
        $totaldownloaded = $row['totaldl'];
        $totaluploaded = $row['totalul'];
        $registered = $row['totaluser'];
        
        // Get latest user
        $result = $TSDatabase->query("SELECT id, username FROM users WHERE status = 'confirmed' ORDER BY id DESC LIMIT 1");
        $latestuser = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
        if ($latestuser) {
            $latestuser = '<a href="' . escape_attr($MAIN['BASEURL'] . '/userdetails.php?id=' . $latestuser['id']) . '">' . 
                         escape_html($latestuser['username']) . '</a>';
        } else {
            $latestuser = 'None';
        }
        
        // Get forum stats
        $result = $TSDatabase->query("SELECT SUM(posts) AS totalposts, SUM(threads) AS totalthreads FROM tsf_forums");
        $fstats = $result ? $result->fetch(PDO::FETCH_ASSOC) : ['totalposts' => 0, 'totalthreads' => 0];
        $totalposts = $fstats['totalposts'] ?? 0;
        $totalthreads = $fstats['totalthreads'] ?? 0;
        
        // Use modern size formatting
        require_once __DIR__ . '/../../include/size_formatting.php';
        
        $contents = [
            'torrents' => $torrents,
            'seeders' => $seeders,
            'leechers' => $leechers,
            'peers' => (string)$peers,
            'totaldownloaded' => mksize_modern($totaldownloaded),
            'totaluploaded' => mksize_modern($totaluploaded),
            'registered' => $registered,
            'latestuser' => $latestuser,
            'totalposts' => $totalposts,
            'totalthreads' => $totalthreads
        ];
        
        $filename = $cacheDir . '/indexstats.php';
        $cachecontents = "<?php\n/** TS Generated Cache#1 - Do Not Alter\n * Cache Name: Index Stats\n * Generated: " . gmdate('r') . "\n*/\n\n";
        $cachecontents .= "\$indexstats = " . var_export($contents, true) . ";\n?>";
        
        if (file_put_contents($filename, $cachecontents)) {
            echo showCacheSuccess();
        } else {
            echo showCacheError();
        }
        
        // Log action
        $Message = str_replace(['{1}', '{2}'], ['Categories, Smilies, Index Stats ', $_SESSION['ADMIN_USERNAME']], 
                              $Language[3] ?? 'Cache {1} rebuilt by {2}');
        logStaffActionModern($Message);
        echo showAlertSuccessModern($Message);
        
    } catch (Exception $e) {
        error_log('Cache rebuild error: ' . $e->getMessage());
        echo showAlertErrorModern('Failed to rebuild cache: ' . $e->getMessage());
    }
    
    exit;
}

// Output cache rebuild interface
?>
<script type="text/javascript">
    $(document).ready(function() {
        setTimeout(function() {
            $.get('index.php?do=cache&cache=true', function(response) {
                $('.alt1').html(response);
            });
        }, 1000);
    });
</script>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center"><?php echo escape_html($Language[2] ?? 'Rebuilding Cache'); ?></td>
    </tr>
    <tr>
        <td class="alt1">
            <img src="./images/fb_ajax-loader.gif" style="vertical-align: middle;" alt="" title="" /> Updating...
        </td>
    </tr>
</table>
<?php

/**
 * Helper functions for cache operations
 */
function showCacheStart(string $cachename): string
{
    global $Language;
    return '<br />&nbsp;&nbsp;&nbsp;' . escape_html($Language[8] ?? 'Building') . ' (' . escape_html($cachename) . ') ';
}

function showCacheSuccess(): string
{
    return '&nbsp;&nbsp;&nbsp;<img src="images/accept.png" border="0" alt="Success" title="Success" style="vertical-align: middle;" /><br /><br />';
}

function showCacheError(): string
{
    return '&nbsp;&nbsp;&nbsp;<img src="images/alert.png" border="0" alt="Error" title="Error" style="vertical-align: middle;" /><br /><br />';
}
