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
Translation by xam Version: 2.6

*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

//  Global Language
$language['global'] = array
(
	'error'							=>'An error has occured!', // Global Error Messages
	'permission'					=>'Unfortunately, you have no permission to view this page.',
	'notavailable'				=>'Unfortunately, this feature currently disabled.',
	'nopermission'				=>'Sorry, permission denied!',
	'permissionlogmessage'	=>'Unauthorized access detected.<br />To Page: {1},<br /> Query String: {2} <br />Username: {3},<br />IP: {4}.<br />This unwanted access has been successful blocked.',
	'print_no_permission'		=>'<table $border = "0" $cellspacing = "0" $cellpadding = "4" class="tborder">
<tr>
<td class="thead"><span class="smalltext"><strong>{1}</strong></span></td>
</tr>
<tr>
<td class="trow1"><!-- start: error_nopermission_loggedin -->
You do not have permission to access this page. This could be because of one of the following reasons:
<ol>
	<li>Your account has either been suspended or you have been banned from accessing this resource.</li>
	<li>You do not have permission to access this page. Are you trying to access administrative pages or a resource that you shouldn\'t be? Check in the tracker rules that you are allowed to perform this action.</li>
	<li>Your account may still be awaiting activation or moderation.</li>
	<li>{2}</li>
</ol>
<!-- end: error_nopermission_loggedin --></td>
</tr>
</table>',
	'print_no_permission_i' =>'Feel free to contact us about this error message.',
	'invalidid'					=>'Invalid ID!',
	'invalididlogged'		=>'Invalid ID! For security reason, we have logged this action!',
	'invalididlogged2'		=>'<div class="error" $align = "center"><b>Error: Invalid ID! For security reason, we have logged this action!</b></div>',
	'invalididlogmsg'		=>'Invalid ID Attempt: URL: {1} - Username: {2} - UserIP : {3} at {4}.',
	'noresultswiththisid'	=>'There is no results returned with this ID!',
	'invalidimagecode'		=>'The string you entered for the image verification did not match what was displayed.<br />You have <b>{1}</b> remaining tries.',
	'nouserid'				=>'No user with this ID!',
	'nousername'			=>'No user with this NAME!',
	'notorrentid'				=>'No torrent with this ID!',
	'notorrentname'		=>'No torrent with this NAME!',
	'accountdisabled'		=>'<b><font $color = "red">This account has been disabled!</font></b>',
	'sorry'						=>'Sorry',
	'invalidaction'			=>'Unknown Action!',
	'dberror'					=>'Database error, please try again later.',
	'trylater'					=>'An error occured. Please try again later.',
	'nothingfound'			=>'Nothing Found',
	'accessdenied'			=>'Access Denied!',
	'permissiondenied'		=>'Permission denied!',
	'flooderror'				=>'This tracker requires that you wait <b>{1}</b> seconds between sending {2}. Please try again in <b>{3}</b> seconds.',
	'dontleavefieldsblank'	=>'Please don\'t leave required fields blank!',
	'allfieldsrequired'		=>'All fields are required!',
	'viptorrent'				=>'You have no permission to view this torrent. It\'s <b><a $href = "donate.php">VIP MEMBERS</a></b> only Torrent.',
	'torrentbanned'		=>'This torrent has been banned!',// Global Error Messages
	'welcomeback'			=>'Welcome back,', // Header Messages
	'logout'					=>'logout',//TSSE 8.0 [ ] removed.
	'ratio'						=>'Ratio:',
	'bonus'					=>'Bonus:',
	'uploaded'				=>'Up:',
	'downloaded'			=>'Down:',
	'whencompleted'		=>'WhenCompleted',
	'donate'					=>'Click here to donate',
	'inboxnonew'			=>'Inbox (No new messages)',
	'enterusername'		=>'Enter Username',
	'inboxnew'				=>'Inbox (There is a new message since your last visit, click here to read.)', // Header Messages
	'home'					=>'Home', // Menu
	'forums'					=>'Forums',
	'browse'					=>'Browse',
	'requests'				=>'Requests',
	'upload'					=>'Upload',
	'usercp'					=>'User CP',
	'irc'						=>'IRC',
	'top10'					=>'Top-10',
	'help'						=>'Help',
	'extra'					=>'Extra',
	'staff'						=>'Staff',
	'redirect'					=>'You will now be redirected...',
	'msgsend'				=>'Message was successfully sent!',
	'staffmenu'				=>'Staff CP',// Menu
	'fakeaccount'			=>'We come to believe you are using a fake account, therefore we\'ve logged this action!',
	'alreadylogged'			=>'You are already logged in!',
	'nowaitmessage'		=>'Click here if you don\'t want to wait any longer.',
	'cachedmessage'		=>'<div $align = "center" class="smalltext">This content was last cached on <strong>{1}</strong>. Stats are updated every <strong>{2}</strong> minutes.</div>',
	'browsermessage'		=>'<p class="error" $align = "justify">If you enable cookies and are still unable to log in, perhaps something happened to cause a problem with your login cookie. We suggest delete your cookies and trying again. To delete cookies in Internet Explorer, go to Tools > Internet Options... and click on the Delete Cookies button. Note that this will delete all cookies stored on your system for other sites as well.</p>',
	'mailerror'				=>'Unable to send mail. Please contact an administrator about this error.',
	'success'					=>'Success',
	'mailsent'				=>'A confirmation email has been mailed to <b>{1}</b>. Please allow a few minutes for the mail to arrive.',
	'mailsent2'				=>'The new account details have been mailed to <b>{1}</b>. Please allow a few minutes for the mail to arrive.',
	'xlocked'					=>'{1} Locked! (the maximum number of failed {1} attempts is reached during reauthentication)',
	'xlocked2'				=>'We come to believe you are trying to cheat our system, therefore we\'ve banned your ip!<br /><br />Click {1}here</a> to fill Unban Request form!<br />Click {2}here</a> to contact us!', // Updated in v5.4
	'warning'					=>'Warning!',
	'accountwarn'			=>"Since your last login, a user has tried to access your account. They were unable to succeed in doing so.\nBelow are the login details:\n\nUsername: {1}\nPassword: {2} (MD5: {3})\n\nIP Address: {4}\nHostname: {5}\n\nIf you believe this is incorrect please contact a member of Staff.\nThank you.",
	'incorrectlogin'			=>'<b>Error</b>: Username or password incorrect!<br /><br />Don\'t remember your password? <b>{1}Recover</a></b> your password!',
	'invitedisabled'			=>'Invite System is currently disabled.',
	'inviteonly'				=>'You must be invited in order to be able to register on our tracker. Have a nice day.',
	'signupdisabled'		=>'Registration is currently disabled.',
	'signuplimitreached'	=>'The current user account limit has been reached. Inactive accounts are pruned all the time, please check back again later...',
	'nodupeaccount'		=>'The IP {1} is already being used on an account! No dupe accounts allowed.',
	'nodupeaccount2'		=>'Sorry, the IP address you are on has been used before, if you believe this is incorrect then contact us!',
	'secimage'				=>'Security Image:<br />(Case sensitive)',
	'seccode'				=>'Security Code: ',
	'slots'						=>'Slots: <font $color = "white">{1}</font>&nbsp;&nbsp;',
	'serverload'				=>'<html><head><meta http-$equiv = "refresh" $content = "5 {1}"></head><body><table $border = 0 $width = 100% $height = 100%><tr><td><h3 $align = center>The server load is very high at the moment. Retrying, please wait...</h3></td></tr></table></body></html>',
	'toomanyusers'			=>'Too many users. Please press the Refresh button in your browser to retry.',
	'ipbanned'				=>'<html><body><h1>403 Forbidden</h1>Unauthorized IP address.</body></html>',
	'trackerclosed'			=>'<font $color = "red"><b>Sorry, Site is down for maintenance, please check back again later...</b></font>',
	'newmessage'			=>'inbox (There is a new message since your last visit, click here to read.)',
	'nonewmessage'		=>'inbox (No new messages)',
	'annoucementempty'	=>'Empty!',
	'nonewannoucement'	=>'There is no new annoucement at this moment.',
	'edit'						=>'Edit',
	'deletecomment'		=>'Delete',
	'vieworj'					=>'View original',
	'lastedited'				=>'Last edited by ',
	'sendmessageto'		=>'Send message to ',
	'reportcomment'		=>'Report this comment',
	'type'						=>'Type',
	'name'					=>'Name',
	'added'					=>'Added',
	'dl'							=>'DL',
	'wait'						=>'Wait',
	'visible'					=>'Visible',
	'avprogress'				=>'Health', // Changed v3.6
	'progress'				=>'Progress',
	'speed'					=>'Speed',
	'notraffic'				=>'No traffic',
	'size'						=>'Size',
	'ttl'						=>'TTL',
	'free'						=>'Free',
	'rec'						=>'Rec.',
	'views'					=>'Views',
	'hits'						=>'Hits',
	'lastaction'				=>'LastAction',
	'leechers'				=>'Leechers',
	'seeders'					=>'Seeders',
	'snatched'				=>'Snatched',
	'uploader'				=>'Uploader',
	'action'					=>'Action',
	'none'						=>'None',
	'greenyes'				=>'<font $color = "green">Yes</font>',
	'redno'					=>'<font $color = "red">No</font>',
	'yes'						=>'<b>yes</b>',
	'no'						=>'<b>no</b>',
	'anonymous'			=>'<i>[Anonymous]</i>',
	'unknown'				=>'<i>(unknown)</i>',
	'freedownload'			=>'<b>Free Torrent</b> (only upload stats are recorded!)',
	'newtorrent'				=>'<b>New Torrent</b> (new release)',
	'disabled'					=>'Disabled',
	'parked'					=>'Your account is parked.',
	'legend'					=>'<fieldset class="fieldset"><legend><b>Legend</b></legend><center>
&nbsp;<b><font $color = "darkred">Staff Leader</font>&nbsp;&nbsp;<font $color = #2587A7>Sysop</font>&nbsp;&nbsp;<font $color = #B000B0>Admin</font>&nbsp;&nbsp;<font $color = #ff5151>Moderator</font>&nbsp;&nbsp;<font $color = #6464FF>Uploader</font>&nbsp;&nbsp;<font $color = #009F00>VIP</font>&nbsp;&nbsp;<font $color = #f9a200>Power User</font>&nbsp;&nbsp;<font $color = black>User</font>&nbsp;&nbsp;Donor\'s<img $src = "{1}star.gif" $border = 0 $style = "vertical-align: middle;">&nbsp;&nbsp;Warned Users<img $src = "{1}warned.gif" $border = 0 $style = "vertical-align: middle;">&nbsp;&nbsp;Banned Users<img $src = "{1}disabled.gif" $border = 0 $style = "vertical-align: middle;"></b></center></fieldset>',
	'pagedown'				=>'Sorry, this page is down for maintenance, please check back again later...',//
	'pleasewait'				=>'Please wait ...',
	'sqlerror'					=>'SQL ERROR',
	'sqlerror2'				=>'An error has occured!. Please contact the administrator about this error..',
	'quote'					=>'Originally Posted by <strong>{1}</strong>',//updated in v6.0
	'code'						=>'CODE',
	'user'						=>'User',
	'poweruser'				=>'Power User',
	'vip'						=>'VIP',
	'uploader'				=>'Uploader',
	'moderator'				=>'Moderator',
	'sysop'					=>'SysOp',
	'administrator'			=>'Administrator',
	'staffleader'				=>'Staff Leader',
	'guest'					=>'Guest',
	'supermod'				=>'Super Moderator',
	'awaitingactivation'	=>'Awating Activation',
	'banned'					=>'Banned',
	'betatester'				=>'Beta Tester',
	'sendtousername'		=>'Send to (username): ',
	'subject'					=>'Subject:',
	'message'				=>'Your Message:',
	'pmspace'				=>'of PM space used.',
	'reached_warning'		=>'Warning. You have reached your message limit.',
	'reached_warning2'	=>'To be able to receive messages you will need to delete old messages.',
	'pmlimitmsg'				=>'You have <strong>{1}</strong> messages stored, of a total <strong>{2}</strong> allowed.',
	'pmmsg'					=>'{1} contains {2} messages.',
	'moresmiles'				=>'More Smiles',
	'moresmilestitle'		=>'More Clickable Smilies',
	'color'						=>'Color',
	'font'						=>'Font',
	'size'						=>'Size',
	'closealltags'			=>'Close all tags',
	'list'						=>'LIST',
	'finduser'					=>'Find User',
	'redirectto'				=>'Redirect to',
	'invalidlink'				=>'Invalid Link?',
	'clicktoreport'			=>'Click here to report',
	'shouterror'				=>'Sorry, you are not authorized to Shout!',
	'proxydetected'		=>'Proxy server detected. We don\'t allow registrations via proxies.',
	'buttonsearch'			=>'search', // submit buttons
	'buttoncheckall'		=>'check all',
	'buttonsave'			=>'Save',
	'buttonreset'			=>'Reset',
	'buttonpreview'		=>'Preview',
	'buttonshout'			=>'shout',
	'buttonclear'			=>'clear',
	'buttonrate'				=>'rate',
	'buttonthanks'			=>'Say thanks!',
	'buttonsubmit'			=>'Submit',
	'buttonrevert'			=>'Revert Changes',
	'buttonselect'			=>'Select User',
	'buttonclosewindow'	=>'Close Window',
	'buttondelete'			=>'Delete!',
	'buttonlogin'			=>'Login',
	'buttongo'				=>'Go!',
	'buttongoback'			=>'Go Back!',
	'buttonrecover'		=>'Recover',
	'buttonreport'			=>'Report!',
	'buttonremoveframe'	=>'Remove this frame',
	'buttonsend'			=>'Send', // submit buttons
	'imgdonated'			=>'Donated', // Image titles
	'imgdisabled'			=>'This account is disabled!',
	'imgwarned'				=>'Warned',
	'imgupdated'			=>'Updated',
	'imgshowhide'			=>'Show/Hide',
	'imgnew'					=>'New',	// Image titles
	'modnotice'				=>'<strong><a $href = "userdetails.php?$id = {1}"><span $style = "color: darkred;"><strong><em>{2}</em></strong></span></a> edited this post at {3} because:</strong>
	<br /><p>{4}</p>',
	'usergroup'			=>'Usergroup:',
	'smilies'				=>'Smilies',
	'postoptions'		=>'Post Options:',
	'title'					=>'Title:',
	'silverdownload'	=>'<b>Silver Torrent</b> (only 50% download stats are recorded!)',
	'started'				=>'Started',
	'imgcommentpos'	=> 'Comment Disabled!',
	'imgsendpmpos'	=> 'PM Disabled!',
	'imgchatpost'		=> 'Shoutbox/Chat Disabled!',
	'imgdownloadpos'	=>'Download Disabled!',
	'imguploadpos'		=> 'Upload Disabled!',
	'previous'			=> 'Previous',
	'first'					=> 'First',
	'next'					=> 'Next',
	'last'					=>' Last',
	'navigation'			=> 'Page {1} of {2} - Showing results {3} to {4} of {5}',
	'secimagehint'		=>'Image too difficult to read? Click here to load a new one.',
	'weaktorrents'		=>'Weak Torrents (Torrents needing seeders)',
	'isnuked'				=>'<b>Nuked</b> (Torrent marked as nuked)',
	'isrequest'			=>'<b>Request</b> (Requested torrent)',
	'nukedetails'		=>'This torrent marked as Nuked. Reason: {1}',
	'year'					=>'Year',
	'years'				=>'Years',
	'month'				=>'Month',
	'months'				=>'Months',
	'week'				=>'Week',
	'weeks'				=>'Weeks',
	'day'					=>'Day',
	'days'					=>'Days',
	'hour'					=>'Hour',
	'hours'				=>'Hours',
	'minute'				=>'Minute',
	'minutes'				=>'Minutes',
	'second'				=>'Second',
	'seconds'			=>'Seconds',
	'GMT'					=>'GMT',
	'today'				=>'Today',
	'yesterday'			=>'Yesterday',
	'noactiveusersonline'	=>'There have been no active users in the last 15 minutes.',
	'logout_error'		=>'An error occurred while attempting to log you out. Click <a $href = "logout.php?$logouthash = {1}" $target = "_self">here</a> to log out.', // Added v3.6
	'click_to_add'		=>'Click a smilie to insert it into your message', // Added v3.6
	'smilies_listing'		=>'Smilies Listing', // Added v3.6
	'more_smilies'		=>'more smilies', // Added v3.6 // Updated v3.7
	'loading'				=>'Loading. Please Wait...', // Added v3.6
	'external'				=>'(external)', //Added v3.7
	'updateexternal'	=>'Update external torrent', //Added v3.7
	'externalupdated'	=>'External torrent has been updated..', //Added v3.7
	'recentlyupdated'	=>'This torrent has already updated..', //Added v3.7
	'seclisten'			=>'Play audio and type the numbers you hear.', //Added v3.7
	'refresh'				=>'refresh', //Added v3.7
	'noenter'				=>'Unfortunately this button has been disabled!\n\nPlease use \'Shout\' button!', //Added v3.7
	'newmessagebox'	=>'There is a new message since your last visit, click OK to read.', // Added v3.8
	'connectablealert' => 'You are showing as Unconnectable on {1} of your torrents. Please visit the <a $href = "{2}">Forums</a> or see <a $href = "{3}">FAQ</a> page for advice on how to make yourself connectable.', // Added v3.8
	'advancedbutton'	=>'Go Advanced', // Added v3.9
	'quickmenu'	=>'Quick Menu', // Added v3.9
	'qinfo1' => 'View public profile', //Added v3.9
	'qinfo2' => 'Send a private message to {1}', //Added v3.9
	'qinfo3' => 'Find all posts by {1}', //Added v3.9
	'qinfo4' => 'Find all threads by {1}', //Added v3.9
	'qinfo5' => 'Add {1} to your buddy list', //Added v3.9
	'qinfo6' => 'Edit User', //Added v3.9
	'qinfo7' => 'Warn User', //Added v3.9
	'qinfo8' =>'Find all comments by {1}', //Added v3.9
	'qinfo9' =>'Find all uploads by {1}', //Added v3.9
	'warningweeks'								=>'{1} week(s).',// Added v4.2
	'warningmessage2'					=>"You have been [$url = rules.php#warning]warned[/url] for {1} by {2}\n\nReason: {3}",// Added v4.2
	'modcommentwarning2'				=>"{1} - Warned for {2} by {3}\nReason: {4}\n{5}",// Added v4.2
	'warningsubject'					=>'You have been warned!',// Added v4.2
	'modcommentwarningremovedby'		=>"{1} - Warning removed by {2}\n{3}",// Added v4.2
	'warningremovedbysubject'			=>'Warning have been removed.',// Added v4.2
	'warningremovedbymessage'			=>'Your warning have been removed by {1}',// Added v4.2
	'gotopage'	=>'Go to Page...',//Added v4.3
	'snotice'		=>'Notice: ',//Added v4.3
	'times'		=>'time(s)',//Added v4.3
	'cancel'		=>'Cancel',//Added in v5.0
	'sys_message'=>'System Message', //Added in v5.1
	'show_results' => 'Show results {1} to {2} of {3}',//Added in v5.3
	'showing_results' => 'Showing results {1} to {2} of {3}',//Added in v5.3
	'first_page'	=>'First Page',//Added in v5.3
	'last_page'	=>'Last Page',//Added in v5.3
	'next_page'	=>'Next Page',//Added in v5.3
	'prev_page'	=>'Prev Page',//Added in v5.3
	'buttonthanks2'=>'Remove Your Thank',//Added in v5.4
	'storrent'=>'Torrent Search',//Added in v5.4
	'storrent2'=>'Keyword(s):',//Added in v5.4
	'unregistered'=>'You are Unregistered, please <a $href = "{1}/signup.php?"><u>register</u></a> or <a $href = "javascript:void(0);" $onclick = "showLoginBox(\'loginbox\');"><u>login</u></a> to gain Full access',//Added in v5.6 -- Updated in v7.1 -- Re-Updated in v7.2
	'h1' => 'You must reply to see the hidden information contained here.',//Added in v5.6
	'h2'	=> 'Hidden Content',//Added in v5.6
	'h3'	=>	'Un-Hidden Content',//Added in v5.6
	'network'=>'Networking',//Added in v5.7
	'selectall'=>'Select ALL',//Added in v6.2
	'spoiler'=>'Spoiler (click to show/hide)',//Added in v6.2
	'male'=>'Male',//Added in v7.0
	'female'=>'Female',//Added in v7.0
	'resized'=>'This image has been resized. Click the image to view the full image. The original image is sized: ',//Added in v7.2

	'directdownloadlink' => 'Direct Download Link',//Added in v7.5
	'invalidddllink' => 'The entered direct download link is not valid.',//Added in v7.5
	'ddlallowedfiletype' => 'Allowed file type: zip',//Added in v7.5
	'dailyvotingreached' => 'You have reached your daily voting limit. Please try again later.',//Added in v7.5
	'redirecttocorrecturl' => 'Please wait while you are being redirected to the correct URL...',//Added in v7.5

	//TSSE 8.0
	'slotview' => 'Slots:',
	'unlimited' => 'Unlimited',
	'profile' => 'Profile',
	'log-out' => 'Log out',

	'qact1' => 'Uploaded Torrents',
	'qact2' => 'Completed Torrents',
	'qact3' => 'Leeching',
	'qact4' => 'Seeding',
	'qact5' => 'Snatchtlist',
	'qact6' => 'Flush Torrents',
	'qact7' => 'Private Messages',
);
?>