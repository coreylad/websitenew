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
Translation by xam Version: 0.8
*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// cronjobs.php New since v3.6
$language['cronjobs'] = array 
(
	'r_subject'	=> 'Gift from Referral System!',
	'r_message' => 'Hi,

	Thank you for using our Referral System.

	You have been earned {1} credit(s).

	Kind regards.',// Updated in v5.4
	'invite_subject'	 => 'Automatic Invite!',
	'invite_message'	=> 'Congratulations, you have received {1} invite(s).

	If you would like to invite your friends, please click [url='.$BASEURL.'/invite.php?id={2}]here[/url].',// Updated in v5.4 -Updated in v7.1-
	'donor_subject'	=> 'Donor status removed by system.',
	'donor_message'	=>	 'Hi,
	
	Your Donor status has timed out and has been auto-removed by the system, and your VIP status has been removed. 
	
	We would like to thank you once again for your support. 
	
	If you wish to re-new your donation, you can do so by clicking [url='.$BASEURL.'/donate.php]here[/url]. 
	
	Kind Regards.',// Updated in v5.4 -Updated in v7.1-
	'vip_subject'	=>'VIP status removed by system.',
	'vip_message'	=>'Hi,
	
	Your VIP status has timed out and has been auto-removed by the system, and your VIP status has been removed. 
	
	Become a VIP again by donating us or exchanging some Karma Bonus Points.
	
	Kind Regards.',// Updated in v5.4
	'promote_subject'	 =>'Account Promote!',
	'promote_message'	=>'Congratulations, you have been auto-promoted.',
	'demote_subject'	=>'Account Demote!',
	'demote_message'	=>'You have been auto-demoted because your share ratio has dropped below {1}',
	'lwarning_subject'	 =>'You have been Leech-Warned!',
	'lwarning_message'	=>'You have been warned because of having low ratio. You need to get a ratio {1} before next {2} weeks or your account will be banned.',
	'hr_warn_subject'=>'Hit and Run Warning!',//Added in v5.5
	'hr_warn_message'=>'[b]{1}[/b],

You have been warned for Hit & Run on the following torrent:
----------------------------------------------------------------------------------------------------------------
[b]{2}[/b]

Reason for This Warning:
----------------------------------------------------------------------------------------------------------------
a) You have seeded this torrent [b]{3}[/b] hour(s) but it must be seeded [b]{4}[/b] hour(s).
...OR
b) You have bad ratio on this torrent. You must have min. [b]{5}[/b] ratio on this torrent.

Please Re-Start to seed this torrent or you will be warned again soon.
If you don\'t have this torrent on your computer, please click on the following link to download & seed it.
----------------------------------------------------------------------------------------------------------------
[b]{6}[/b]

All torrents must be seeded at least [b]{4}[/b] hour(s) after finished otherwise users will get [b]+1[/b] warn count per torrent.
Please note: Once your total warnings will be reached the global limit (default 7), your account will be suspended.

Thank you for your understanding and support.
Have a great day.',//Updated in v5.7 --Must be Updated for Each Language--
	'ui_subject'=>'You have been demoted!',//Added in v6.3
	'ui_msg'=>'You have been Auto-Demoted because you have not uploaded in {1} days.',//Added in v6.3
	'you_have_been_promoted'	=>'Congratulations, you have been auto-promoted to {1}.',
	'you_have_been_demoted'	=>'You have been auto-demoted to {1} because your share ratio has dropped below {2}',
);
?>