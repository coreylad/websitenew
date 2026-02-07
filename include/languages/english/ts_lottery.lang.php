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
Translation by xam Version: 0.3

*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// ts_lottery.php
$language['ts_lottery'] = array 
(
	'disabled'		=>	'Sorry, The Lottery System is currently disabled by Administrator. Please try again later...',
	'no_permission'	 =>	'Sorry, permission denied for this usergroup!',
	'title'	=>	 'Welcome to {1} Lottery System. Good luck and have fun!',	
	'last_lottery_winners'	=>	 'Lottery Winner(s)',
	'winners'	 =>	'<strong>{1} Winner(s):<br /></strong>',// Updated in v5.2
	'last_earn'	=>	 'Earned Amount',
	'start_date'	=>	 'Lottery Start Date',
	'end_date'	=>	 'Lottery End Date',
	'closed_raffles'	 =>	'Closed Raffles',
	'open_raffles'	 =>	'Open Raffles',
	'ticket_limit'		=>	 	'Ticket Purchase Limitations',
	'ticket_status'	=>	 	'You have purchased total <strong>{1} ticket(s)</strong>, you can purchase <strong>{2} more ticket(s)</strong> for this raffle.',// Updated in v5.2
	'per_user'	=>	 'Per User',
	'purchase_button'	 =>	'Purchase Ticket',
	'show_list_button'	 =>	'Click <strong>{1}here{2}</strong> to see list.',// Updated in v5.2
	'show_list_title'=>'Ticket Owners',
	'cant_purchase'	=>	 'You don\'t have enough upload amount for this payment!<br />You have requested total {1} ticket(s) which requires {2} upload amount, but you have {3} upload amount at this time.<br />Need {4} upload amount, Seed more, Get more tickets...',
	'thank_you'	=>	 'Thank you for purchasing tickets..',
	'total_purchased' => 'This raffle has been purchased <strong>x {1} time(s)</strong> by <strong>{2}</strong> unique member(s).',// Updated in v5.2
	'uploaded'	=>	 'Uploaded',
	'downloaded'	=>'Downloaded',
	'ratio'	 =>'Ratio',
	'owner_name'	=>	 'Username',
	'total_tickets'	=>	 'Total Ticket(s)',
	'rules'	 => 'You can maximum <strong>{1} ticket(s)</strong> purchase for this raffle. Ticket cost per raffle: <strong>{2}</strong>. Winners will get <strong>{3}</strong> upload amount. Good luck and have fun.',// Updated in v5.2
	'java_error' => 'You don\'t have enough upload amount for this payment!',
	'end_of_date'	=> 'Sorry, this raffle has been closed! Winners will be announced as soon as possible',
	'msg_subject' => 'Congratulations!',
	'msg_body' => 'Congratulations,

You have won {1} from our Lottery System. Your upload amount has been updated!

Thank you for playing with us.

Have a great day.',
	'modcomment'	=> 'Earned {1} Upload Amount by Lottery System!',
	'no_active_lottery' => 'There is no open raffle yet.',
	'info'=>'How many tickets do you want to purchase?',
	'your_win_ratio'=>'Your win ratio for this lottery: <b>{1}</b>',//Added v5.2
);
?>