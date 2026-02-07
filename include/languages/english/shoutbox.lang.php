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
Re-Coded since v6.0
*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// ts_shoutbox.php, ts_post_shout.php, ts_update_shout.php
$language['shoutbox'] = array
(
	'floodcomment' =>'shoutbox messages',
	'announcement' =>'Shoutbox Announcements',
	'shout' => 'Shout',
	'reset' => 'Reset',
	'maxchars' => 'Max. Allowed Chars Per Shout is: {1}',
	'sure'=>'Are you sure that you want to delete this Shout?',
	'deleted'=>'Shout has been deleted!',
	'updated'=>'Shout has been updated!',
	'sysnotice'=>'[i][b]System Notice:[/b] {1}[/i]',
	'alreadyposted'=>'This message was already posted before by you!',
	'loading'=>'ShoutBox is being loaded...',
	'pruneshout'=>'Pruneshout',
	'pruneshout1'=>'Pruneshout will delete a single word throughout the shout or a group of words.',
	'prune'=>'Prune',
	'prune1'=>'Prune deletes all text within the shoutbox with no chance of recover.  There are two ways to use this command. 1) /prune 2) /prune username',
	'ban'=>'Ban',
	'ban1'=>'Ban will ban a user from the shoutbox till you unban them. To use type /ban [username here without the brackets]',
	'unban'=>'UnBan',
	'unban1'=>'Unban will unban a user from the shoutbox giving them access to shout again. To use type /unban [username here without the brackets]',
	'warn'=>'Warn',
	'warn1'=>'Warn will give a user a warning for not following the rules of the shoutbox. To use type /warn [username here without the brackets]',
	'unwarn'=>'UnWarn',
	'unwarn1'=>'UnWarn will remove a user a warning allowing them to use the shoutbox again. To use type /unwarn [username here without the brackets]',
	'admincommands'=>'Shoutbox Admin Commands:',
	'runcommand'=>'Run Command',
	'cancel'=>'Cancel',
	'save'=>'Save',
	'commanddone'=>'Your command has been executed.',
	'noshout'=>'There are no shouts yet. Be the First to Shout!',
	'pm'=>'Send PM to {1}', //Added in v6.1
	'private'=>'Private Shout', //Added in v6.1
	'c1'=>'Public Room', //Added in v6.2
	'c2'=>'Admin Room', //Added in v6.2
	'c3'=>'Staff Room', //Added in v6.2
	'c4'=>'VIP Room', //Added in v6.2
	'c5'=>'Uploader Room', //Added in v6.2
	'c'=>'Channels', //Added in v6.2
	'inactive'=>'We have noticed that you have been inactive for over 5 minute(s).<br>We have stopped running the Shoutbox due to your inactivity.<br>If you are back again, please click <a $href = \"#shoutbox\" $onclick = \"TSImBack();\"><u>here</u></a>.',//Added in v7.1
	'tagerror'=>'Following tags has been disabled by Administrator: {1}. Please remove those tags from your shout and try again.',//Added in v7.2
	'shoutboxisdisabled' => 'Shoutbox has been disabled. To enable it again, click <a $href = "{1}/usercp.php?$act = edit_details#shoutbox-options">here</a>.',//Added in v7.4
	'fb-shoutbox' => 'Fixed Shoutbox',//Added in v7.4
	'fb-shoutbox2' => 'Check this box to use Shoutbox everywhere.',//Added in v7.4	
	'fb-shoutbox3' => 'Type your message here and press enter',//Added in v7.4
	'fb-shoutbox4' => 'Disable auto-scroll',//Added in v7.4
	'fb-shoutbox5' => 'Enable auto-scroll',//Added in v7.4
	'fb-shoutbox6' => 'Members in Chat',//Added in v7.4
	'fb-shoutbox7' => 'Auto-scroll has been disabled.',//Added in v7.4
	'fb-shoutbox8' => 'Auto-scroll has been enabled.',//Added in v7.4
);
?>