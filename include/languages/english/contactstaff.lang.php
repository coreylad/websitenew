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
Translation by xam Version: 0.7
*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// contactstaff.php
$language['contactstaff'] = array
(
	'contactstaff'	=>'Contact Staff',
	'invalidlink'		=>'Invalid Link: {1}',
	'sendmessage'	=>'Send message to Staff',
	'floodcomment'	=>'staff messages',
	'info'				=>'Contact the Sysop asking for Technical Help, or questions yet answered in the <a $href = faq.php>FAQ</a> or <a $href = tsf_forums/index.php>Forums</a> isn\'t good idea, please only contact the Sysop for very important matters, otherwise use the proper ways to get help, as <a $href = tsf_forums/index.php>Forums</a>, etc.<br />All software support questions and those already answered in the <a $href = faq.php>FAQ</a> will be ignored.', // Added v3.6
	'subject'=>'New Staff Message!', // Added v6.0
	'msg'=>'There is a new staff message. Click [$url = '.$BASEURL.'/'.$staffcp_path.'/index.php?do=staff_messages]here[/url] to read!', // Added v6.0 -Updated in v7.1-
	'email' => 'E-mail:',//Added in v7.3
	'invalid_email' => 'Please enter a valid e-mail address.',//Added in v7.3
);
?>