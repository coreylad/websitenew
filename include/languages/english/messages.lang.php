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
Translation by xam Version: 1.1

*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// messages.php and sendmessage.php
$language['messages'] = array
(
	'error'						=>'Invalid Mailbox!',
	'inbox'					=>'Inbox',
	'sendbox'				=>'Sendbox',
	'status'					=>'Status',
	'subject'					=>'Subject',
	'sender'					=>'Sender',
	'date'						=>'Date',
	'delmove'				=>'del/move',
	'nomessage'			=>'There is no message to show.',
	'removefriend'		=>'[remove from friends]',
	'addfriend'				=>'[add to friends]',
	'system'					=>'System',
	'nosubject'			=>'No Subject!',
	'unread'					=>'unread',
	'read'						=>'read',
	'buttonmove'		=>'move selected',
	'or'							=>'or',
	'buttondelete'		=>'delete',
	'field1'					=>'Keywords:',
	'field2'					=>'by',
	'field3'					=>'subject',
	'field4'					=>'message',
	'field5'					=>'in',
	'compose'				=>'Compose',
	'mailboxmanager'=>'Mailbox Manager',
	'to'							=>'To',
	'from'						=>'From',
	'reply'						=>'Reply',
	'new'						=>'<strong>(New)</strong>',
	'headdm'				=>'PM ({1})',
	'moveto'				=>'Move to:',
	'buttonmoveto'	=>'Move',
	'delete'					=>'Delete',
	'forwardpm'			=>'Forward',
	'error2'					=>'Messages couldn\'t be moved!',
	'error3'					=>'Messages couldn\'t be deleted!',
	'orjmsgfrom'			=>'Original Message from: ',
	'orjreceiver'			=>'Or.Receiver:',
	'message'				=>'Message:',
	'orjmessage'			=>'Orj. Message:',
	'savemsg'				=>'Save Message',
	'nouser'					=>'Sorry, there is no user with that username.',
	'error4'					=>'Refused!',
	'error5'					=>'This user has blocked PMs from you!',
	'error6'					=>'This user only accepts PMs from users in his friends list!',
	'error7'					=>'This user does not accept PMs!',
	'forwarded'			=>'PM forwarded!',
	'headem'				=>'Editing Mailboxes',
	'addmb'					=>'Add Mailboxes',
	'infomb'					=>'You may add extra mailboxes. You do not have to use all the input boxes.',
	'headem2'				=>'Edit Mailboxes',
	'infomb2'				=>'You may edit the names, or delete the name to delete this virtual directory.<br />Please note, that all messages in this directory will be lost if you delete this directory.',
	'nombtoedit'			=>'There are no mailboxes to edit.',
	'error8'					=>'Could not delete message.',
	'error9'					=>'This account is parked!',
	'error10'					=>'This user have reached his message limit!',
	'jumpto'					=>'Jump to:',
	'head'						=>'Send Message',
	'title'						=>'Send Personal Message',
	'title2'						=>'Message to',
	'delmsg'					=>'Delete message you are replying to',
	'savemsg'				=>'Save message to Sentbox',
	'body'						=>'{1},

You have received a new private message on {2} from {3}. To view this message, you can follow this link:

{4}/messages.php

Please note that you will not receive any further notifications of new messages until you visit {2}.

You can disable new message notifications on your account options page:

{4}/usercp.php?$act = edit_details

Thank you,
{2} Staff
{4}

====== PLEASE DO NOT REPLY, THIS IS AN AUTOMATED MESSAGE ======
',
	'msgsubject'			=>'New Private Message at {1}',
	'floodcomment'	=>'private messages',

	'unknown'	=>'Unknown', //Added v3.9
	'newtitle1'=>'<strong>Private Messages in Folder:</strong> {1}', //Added v3.9
	'newtitle2'=>'Selected Messages: ', //Added v3.9
	'newtitle3'=>'Move to folder..', //Added v3.9
	'newtitle4'=>'Delete', //Added v3.9
	'newtitle5'=>'Mark as read', //Added v3.9
	'newtitle6'=>'Mark as unread', //Added v3.9
	'newtitle7'=>'Go', //Added v3.9
	'newtitle8'=>'Folder <strong>{1}</strong> contains <strong>{2}</strong> messages.', //Added v3.9
	'newtitle9'=>'No private messages selected.', //Added v3.9
	'newtitle10'=>'Folders', //Added v3.9
	'newtitle11'=>'Invalid Private Message specified. If you followed a valid link, please notify the administrator.', //Added v3.9
	'newtitle12'=>'<strong>Private Message:</strong> {1}', //Added v3.9
	'newtitle13'=>'Are you sure you want to delete this pm?', //Added v3.9
	'newtitle14'=>'You must first create a custom folder if you wish to be able to move messages from your inbox. You may start by clicking <a $href = "{1}">Here</a>.', //Added v3.9
	'newtitle15' =>'Please select a destination folder for the items that you wish to move.',//Added v3.9
	'newtitle16' =>'Move Items', //Added v3.9
	'newtitle17' =>'Cancel', //Added v3.9
	'newtitle18'=>'Move Private Messages', //Added v3.9
	'newtitle19'=>'Edit Folders', //Added v3.9
	'newtitle20'=>'Save Folders', //Added v3.9
	'newtitle21'=>'Default Folders',//Added v3.9
	'newtitle22'=>'Your Custom Folders',//Added v3.9
	'newtitle23'=>'Add New Folders',//Added v3.9
	'newtitle24'=>'Folder Name:',//Added v3.9
	'newtitle25'=>'To <strong>rename</strong> a folder, edit the folder\'s name in the list.<br />To <strong>delete</strong> a folder, remove the folder\'s name from the list. All messages stored in this folder will be deleted!',//Added v3.9
	'newtitle26'=>'To add a new folder, enter the name of the new folder in one of the empty boxes in the list.',//Added v3.9
	'newtitle27'=>'Manage Folders', //Added v3.9
	'newtitle28'=>'Send New Message',//Added v3.9
	'newtitle29'=>'Receiver didn\'t read this message yet!',//Added v3.9
	'newtitle30'=>'The message you tried to delete hasn\'t been read. You can only delete messages you\'ve read. ',//Added v3.9
	'newtitle31'=>'Go Back', //Added v3.9
	'newtitle32'=>'Permission denied! Maximum <strong>{1}</strong> folders allowed for this account! You have {2} folders!',//Added v3.9
	'qinfo8'	=>'<strong>Join date:</strong> {1}',//Added v3.9
	'qinfo9'	=>'<strong>Last seen:</strong> {1}, {2}',//Added v3.9
	'newtitle33'=>'Empty This Box',//Added v4.3
	'newtitle34'=>'Are you sure you want to delete ALL pm\'s in this box?',//Added v4.3
	'smmultiple'=>'You may send your message to up to <b>5</b> people at a time. Separate multiple user names with \'<b>;</b>\'',//Added v5.5
	'smmultiplerrror'=>'The following users were not found:  {1}',//Added v5.5
	'smmultiplerrror2'=>'Too many recipients - you are attempting to send to {1} users but you are only allowed to send to 5 users.',//Added v5.5
	'msgsenderror'=>'You can\'t send a message yourself!',//Added v5.5
	'verified1'		=> 'V E R I F I E D',//Added v5.6
	'verified2'		=>'This message was sent by an official <strong>{1}</strong> staff member. It\'s contents can be trusted.',//Added v5.6
	'dumptext' => 'Private Message Dump for User ',// Added v6.0
	'download'=>'Download Messages',// Added v6.0
	'doptions'=>'Display Options',// Added v6.2
	'sm'=>'Standard Messages',// Added v7.0 -- Updated in v7.2
	'sm1a'=>'FAQ',// Added v7.0
	'sm1b'=>'Please visit following page to see FAQ: '.$GLOBALS['BASEURL'].'/faq.php',// Added v7.0
	'sm2a'=>'Port Forward',// Added v7.0
	'sm2b'=>'Please visit following page to get more details about Port Forwarding: http://portforward.com',// Added v7.0
	'sm3a'=>'Tracker Rules',// Added v7.0
	'sm3b'=>'Please visit following page to see Tracker Rules: '.$GLOBALS['BASEURL'].'/rules.php',// Added v7.0
	'sm4a'=>'Forum Rules',// Added v7.0
	'sm4b'=>'Please visit following page to see Forum Rules: '.$GLOBALS['BASEURL'].'/tsf_forums/information.php',// Added v7.0
	'sm5a'=>'Port Check',// Added v7.0
	'sm5b'=>'Please visit following page to check your connectable status: '.$GLOBALS['BASEURL'].'/port_check.php',// Added v7.0
	'sm6a' => '',// Added v7.1
	'sm6b' => '',// Added v7.1
	'sm7a' => '',// Added v7.1
	'sm7b' => '',// Added v7.1
	'sm8a' => '',// Added v7.1
	'sm8b' => '',// Added v7.1
	'sm9a' => '',// Added v7.1
	'sm9b' => '',// Added v7.1
	'sm10a' => '',// Added v7.1
	'sm10b' => '',// Added v7.1
);
?>