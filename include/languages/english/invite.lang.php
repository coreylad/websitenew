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
Translation by xam Version: 0.4

*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// invite.php
$language['invite'] = array 
(
	'failed'					=>'Failed',
	'success'					=>'Success',
	'head'						=>'Invite System',
	'status'					=>'You have invited following friends',
	'noinvitesyet'				=>'No invites yet.',
	'username'					=>'Username',
	'email'						=>'Email',
	'lastseen'					=>'Last Seen',
	'uploaded'					=>'Uploaded',
	'downloaded'				=>'Downloaded',
	'ratio'						=>'Ratio',
	'status2'					=>'Status',
	'never'						=>'never',
	'confirmed'					=>'Confirmed',
	'pending'					=>'Pending',
	'status3'					=>'Invites awaiting confirmation',
	'nooutyet'					=>'No invitations sent out at the moment.',
	'hash'						=>'Invite Hash',
	'senddate'				=>'Send Date',
	'info'						=>'Invite someone to join {1}.',
	'button'					=>'Invite Someone',
	'field1'					=>'Email of your Friend:',
	'field2'					=>'* Must be valid!',
	'field3'					=>'If you would like to say something custom to your addressees, type it here:',
	'field4'					=>'You have {1} invites left.',
	'button2'					=>'Send Invite',
	'button3'					=>'Clear Form',
	'noinvitesleft'				=>'You have no invites left!<br /><br />Seed a torrent, earn invite.',
	'invitesystemoff'			=>'Sorry, invite system is currently disabled. Please try again later.',
	'alert'						=>'ALERT! Invite system is currently disabled!',
	'invalidemail'				=>'The e-mail you specified is invalid.',
	'invalidemail2'				=>'The e-mail you specified is invalid, because it was found in our database.',
	'nonote'					=>'No Note',
	'subject'					=>'You have been invited to register on {1}!',
	'message'					=>'Hi,

You have been invited by {1} to register on "{2}".

Your registration link: {3}/signup.php?$invitehash = {4}&$type = invite

Please note: You will need to accept the invitation within {5} days, or else the link will become inactive.

We hope to see you soon!

Best Regards,
{2} Team

{1} has attached a note to the invitation:
----------------------------------------------------
{6}
----------------------------------------------------
',
	'error'						=>'An error occured. Please try again later.',
	'sent'						=>'Thank you. The Invite was sent out successfully to the following user: {1}',
	'manuellink'				=>'Your friend registration link as shown below:<br /> {1}/signup.php?$invitehash = {2}&$type = invite',
	'selecttype'				=>'Please Select Invite Type: ',
	'type1' => 'By Email (Automatic)',
	'type2' => 'Manual (Copy & Paste)',
	'typebutton'	=>'go to next',
	'action'			=>'Delete',
	'actionbutton'	=>'Delete Selected',
	'invitedeadtime'=>'Dead Time',
	'added'			=>'Registered',
	'default_invite_msg' => 'Hey mate,

Check this site.. 

Have a great day.',//Added in v5.1
	'ratioerror' => 'Sorry, you must have at least <b>{1}</b> ratio to invite your friends.',//Added in v6.1
);
?>