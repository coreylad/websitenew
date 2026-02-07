<?php
/*
+--------------------------------------------------------------------------
|   TS Special Edition v.8.0 
|   ========================================
|   by xam
|   (c) 2005 - 2020 Template Shares Services
|   https://templateshares.net
|   ========================================
|   Web: https://templateshares.net
|   Time: $_ts_date_
|   Signature Key: $_ts_signature_key_
|   Email: contact@templateshares.net
|   TS SE IS NOT FREE SOFTWARE!
+---------------------------------------------------------------------------
*/
define('NO_LOGIN_REQUIRED', true);
define('THIS_SCRIPT', 'formats.php');
require('./global.php');

$lang->load('formats');

stdhead($lang->formats['head']);
echo $lang->formats['info'];
stdfoot();
?>