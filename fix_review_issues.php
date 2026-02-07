<?php
/**
 * Fix Specific Issues Found in Code Review
 * Addresses edge cases where $ was incorrectly added/left in place
 * 
 * DEAD CODE: This is a temporary utility script for fixing review issues.
 * Not part of the application runtime. Can be removed after fixes are applied.
 */

$fixes = [
    // File => [search => replace]
    'include/languages/english/donate.lang.php' => [
        '$href =' => 'href=',
        '$onclick =' => 'onclick=',
        'window.opener.location.$href' => 'window.opener.location.href',
    ],
    'include/languages/english/details.lang.php' => [
        '$href =' => 'href=',
    ],
    'include/languages/english/delete.lang.php' => [
        '$leechers =' => 'leechers',
    ],
    'include/languages/english/cronjobs.lang.php' => [
        '[$url =' => '[url=',
        '?$id =' => '?id=',
    ],
    'include/functions_tsseo.php' => [
        '?$category =' => '?category=',
    ],
    'include/functions_ts_get_awards.php' => [
        'a.$award_id' => 'a.award_id',
        'a.$userid' => 'a.userid',
    ],
    'include/class_tsquickbbcodeeditor.php' => [
        'var $input =' => 'var input =',
    ],
    'include/cron/rssposter.php' => [
        '$this->$message' => '$this->message',
    ],
    'include/class_zip.php' => [
        'post-$check = 0, pre-$check = 0' => 'post-check=0, pre-check=0',
    ],
    'ff.php' => [
        'window.$sidebar = =' => 'window.sidebar ==',
        'window.sidebar.$addSearchEngine = =' => 'window.sidebar.addSearchEngine ==',
    ],
];

$fixed = 0;
foreach ($fixes as $file => $replacements) {
    $filePath = __DIR__ . '/' . $file;
    if (!file_exists($filePath)) {
        echo "SKIP: $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    $original = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        $fixed++;
        echo "FIXED: $file\n";
    }
}

echo "\nFixed $fixed files\n";
