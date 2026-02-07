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
/* 
TS Special Edition English Language File
Translation by xam Version: 0.1

*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// port_check.php
$language['port_check'] = array 
(
	'head'		=>	'Port Checker (Connectable Checker)',
	'title'			=>	'A test will be performed on your computer to check if the specified port is opened.',
	'checking'	=>	'Checking port ...',
	'good'		=>	'<font $color = "green">OK!</font> Port <b>{1}</b> is open and accepting connections. You will be able to receive incoming BitTorrent connections. Click <a $href = "'.$_SERVER['SCRIPT_NAME'].'">here</a> to check another port.',
	'bad'			=>	'<font $color = "red">ERROR!</font> Port <b>{1}</b> does not appear to be open. Please see www.portforward.com for more information about how to map a port. Click <a $href = "'.$_SERVER['SCRIPT_NAME'].'">here</a> to check another port.',
	'field1'		=>	'Enter Port Number:',
	'field2'		=>	'Check Port',
);
?>