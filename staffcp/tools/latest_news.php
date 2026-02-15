<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

if (isset($_GET['getNews'])) {
    function_268();
    echo NEWS;
    exit;
}

function function_268(string $url = 'http://www.templateshares.info/tsnews/tsnews.txt'): void
{
    @ini_set('user_agent', 'TS_SE via cURL/PHP');
    $data = false;
    $timeout = 10;
    
    if (function_exists('curl_init') && ($ch = curl_init())) {
        $newsItem = true;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
    }
    
    if (!isset($newsItem) && !$data && ini_get('allow_url_fopen')) {
        $newsCount = ini_set('default_socket_timeout', (string)$timeout);
        $tableRow = '';
        if ($handle = @fopen($url, 'rb')) {
            ini_set('default_socket_timeout', $newsCount);
            stream_set_timeout($handle, $timeout);
            stream_set_blocking($handle, false);
            while (!feof($handle)) {
                $tableRow .= fread($handle, 8192);
            }
            fclose($handle);
            $data = $tableRow;
            unset($tableRow);
        }
    }
    
    if ($data) {
        define('NEWS', $data);
    } else {
        define('NEWS', 'Connection Error!');
    }
}

?>
<script type="text/javascript">
    $(document).ready(function() {
        setTimeout(function() {
            $.get('index.php?do=latest_news&getNews=true', function(response) {
                $('.alt1').html(response);
            });
        }, 1000);
    });
</script>
<table cellpadding="0" cellspacing="0" border="0" class="mainTable">
    <tr>
        <td class="tcat" align="center">Latest TS SE News</td>
    </tr>
    <tr>
        <td class="alt1">
            <img src="./images/fb_ajax-loader.gif" style="vertical-align: middle;" alt="" title="" /> Checking...
        </td>
    </tr>
</table>
