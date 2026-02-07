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
Translation by xam Version: 1.5

*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// TSF FORUMS (all files)
$language['tsf_forums'] = array 
(
	'forum'			=>'Forum',
	'threads'			=>'Threads',
	'posts'			=>'Posts',
	'lastpost'			=>'Last Post',
	'stats'			=>'<b>Board Statistics</b>',
	'stats_info'		=>'Our members have made a total of <b>{1}</b> posts in <b>{2}</b> threads.<br />
							We currently have <b>{3}</b> members registered.<br />
							Please welcome our newest member, <b>{4}</b>',
	'activeusers'	=>'<b>{1}</b> users active in the past <b>{2}</b> minutes:<br />',
	'by'				=>'by',
	'invalidfid'		=>'The specified forum does not exist.',
	'invalid_tid'		=>'The specified thread does not exist.',
	'invalid_post'	=>'The specified post does not exist.',
	'noforumsyet'	=>'There is no registered forums yet!',
	'lastpost_never'=>'Never',
	'guest'			=>'Guest',
	'whosonline'		=>'<b>Who\'s Online</b>',
	'new_posts'		=>'Forum Contains New Posts',
	'no_new_posts'=>'Forum Contains No New Posts',
	'forum_locked'	=>'Forum is Closed for Posting',
	't_new_posts'	=>'New posts',
	't_no_new_posts'=>'No new posts',
	'thread_locked'=>'Thread is closed',
	'new_thread'	=>'Post Thread',
	'mark_read'		=>'Mark this forum read',
	'thread'			=>'Thread Subject',
	'author'			=>'Author',
	'replies'			=>'Replies',
	'views'			=>'Views',
	'stickythread'	=>'Important Thread!',
	'status2'			=>'Status',	//Changed name in v5.0
	'pages'			=>'Pages: ',
	'multithread'	=>'Multi-page thread',
	'new_thread'	=>'New Thread',
	'new_reply'		=>'New Reply',
	'post_edited'	=>'Thank you, this post has been edited.',
	'message'		=>'Message',
	'new_thread_in'=>'New Thread in {1}',
	'mod_options'	=>'Moderator Options:',
	'mod_options_c'=>'<b>Close Thread</b>: prevent further posting in this thread.',
	'mod_options_s'=>'<b>Stick Thread:</b> stick this thread to the top of the forum.',
	'mod_options_cc'=>'Open / Close Thread',
	'mod_options_ss'=>'Sticky / Unsticky Thread',
	'mod_options_m'=>'Move Thread',
	'mod_options_dd'=>'Delete Thread',
	'new_thread_head'=>'Post a new Thread',
	'new_reply_head'=>'Post a New Reply',
	'new_reply_head2'=>'Reply to thread: ',
	'cant_post'	=>'You may not post in this forum either because the forum is closed, or it is a category.',
	'too_short'	=>'Message or Subject is too short!',
	'thread_created'=>'The new thread has been created.',
	'no_thread'		=>'There is no thread to show.',
	'editedby'		=>'This post was last modified: {1} {2} by {3}',
	'reply_post'		=>'Reply',
	'quote_post'	=>'Quote',
	'report_post'	=>'Report',
	'edit_post'		=>'Edit',
	'edit_this_post'=>'Edit Post',
	'a_post'			=>'a post',
	'delete_post'	=>'Delete',
	'pm_post'		=>'Send PM',
	'profile_post'	=>'Profile',
	'redirect_last_post'=>'Redirecting to the last post...',
	'post'				=>'Post: ',
	're'					=>'RE: ',
	'jump_text'		=>'Forum Jump: ',
	'go_button'		=>'go',
	'usergroup'		=>'Group: ',
	'jdate'			=>'Joined: ',
	'status'			=>'Status: ',
	'totalposts'		=>'Posts: ',
	'user_offline'	=>'{1} is Offline now',//updated in v6.0
	'user_online'	=>'{1} is Online now',//updated in v6.0
	'post_done'		=>'Your new reply has been saved...',
	'thread_closed'	 =>'Sorry this thread has been closed!',
	'yes'				=>'Yes I am Sure!',
	'no'				=>'No, Please Return!',
	'cancel'			=>'Cancel',
	'mod_del_thread'=>'Delete Thread: {1}',
	'mod_del_thread_2'=>'Are you sure you wish to delete the selected thread?<br />Once a thread has been deleted it cannot be restored and any posts!',
	'mod_del_post'=>'Delete Post: {1}',
	'mod_del_post_2'=>'Are you sure you wish to delete the selected post?<br />Once a post has been deleted it cannot be restored!',
	'mod_move'		=>'Select New Forum: ',
	'warningmsg'=>'<a $href = "./../admin/settings.php?$action = forumsettings">Your board status is currently set to closed.</a>',
	'search_results'	=>'Search Results: ',
	'search'			=>'Search',
	'title'				=>'Search Forums',
	'title1'			=>'Search by Key Word',
	'title2'			=>'Search by User Name',
	'title3'			=>'Search Options',
	'option1'			=>'Keyword(s):',
	'option2'			=>'Search Entire Post',
	'option3'			=>'Search Titles Only',
	'option4'			=>'User Name:',
	'option5'			=>'Find Posts by User',
	'option6'			=>'Find Threads Started by User',
	'option7'			=>'Exact Name',
	'option8'			=>'Search in Forum(s)',
	'button_1'		=>'Search Now',
	'button_2'		=>'Reset Fields',
	'select1'			=>'Search All Open Forums',
	'searcherror'	=>'Sorry, but no results were returned using the query information you provided.<br />Please redefine your search terms and try again.',
	'searcherror2'	=>'You did not enter any search terms.<br />At a minimum, you must enter either some search terms or a username to search by.',
	'searcherror3'	=>'One or more of your search terms were shorter than the minimum length. The minimum search term length is {1} characters.<br /><br />If you\'re trying to search for an entire phrase, enclose it within double quotes.<br />For example "The quick brown fox jumps over the lazy dog"',
	'searcherror4'	=>'An invalid search was specified.  Please go back and try again.',
	'searchresults'	=>'Thank you, your search has been submitted and you will now be taken to the results list.',
	'markforumread'=>'The selected forum has been marked as read.',
	'markforumsread'=>'All the forums have been marked as read.',
	'markallread'	=>'Mark All Forums Read',
	'country'			=>'Country: ',
	'tooltip'			=>'<strong>Last seen:</strong> {1}<br /><strong>Downloaded:</strong> {2}<br /><strong>Uploaded:</strong> {3}<br /><strong>Ratio:</strong> {4}<br />',//Updated v4.1
	'a_error1'		=>'The specified attachment does not exist.',
	'a_error2'		=>'The file upload failed. Please choose a valid file and try again.',
	'a_error3'		=>'The type of file that you attached is not allowed. Please remove the attachment or choose a different type.',
	'a_error4'		=>'The file you attached is too large. The maximum size for that type of file is {1}.',
	'a_error5'		=>'It appears this file already uploaded. Please choose a different file to attach.',
	'a_info'			=>'Attached File(s)',
	'a_size'			=>'Size: ',
	'a_count'		=>'Downloads: ',
	'attachment'	=>'Attachment:',
	'a_remove'		=>'Check this box to remove attachment from this post.',
	'deny'				=>'This user wishes to remain anonymous!',
	'thread_review'=>'Thread Review (Newest First)', // Added v3.6
	'posted_by'		=>'Posted by', // Added v3.6
	'quick_reply'	=>'Quick Reply', // Added v3.6
	'post_reply'		=>'Post Reply', // Added v3.6
	'preview_reply'	 =>'Preview Post', // Added v3.6
	'search_forum'	=>'Search this forum', // Added v3.6
	'click_hold_edit'=>'(Click and hold to edit)', // Added v3.6
	'ajax_loading' =>'Loading. <br />Please Wait..', // Added v3.6
	'saving_changes' =>'Saving changes..', // Added v3.6
	'noperm'			=>'Permission denied!', // Added v3.6
	'posted'			=>'Posted', //Added v3.7
	'announcements'=>'Announcement:', //Added v3.7
	'atitle'			=>'Forum Announcements', //Added v3.7
	'invalidaid'	=>'The specified announcement does not exist.', //Added v3.7
	'gotolastpost'	=>'Go to last post', //Added v3.7
	'deleteposts'	=>'Delete Posts', //Added v3.7
	'deletethreads'	 =>'Delete Threads', //Added v3.7
	'subs'				=>'Subscribe to this Thread', //Added v3.8
	'delsubs'			=>'Unsubscribe to the Thread', //Added v3.8
	'asubs'			=>'You are already subscribed to this thread!',  //Added v3.8
	'dsubs'			=>'A subscription for this thread has been added.', //Added v3.8
	'msubs'			=>'Dear {1},

You are subscribed to the thread {2}, there is a new posts to this thread, the last poster was {3}.

To visit this thread, please visit this page:
{4}/tsf_forums/showthread.php?$tid = {5}

All the best,
{6} Team.

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Unsubscription information:

To unsubscribe from this thread, please visit this page:
{4}/tsf_forums/subscription.php?do=removesubscription&$tid = {5}', //Added v3.8
	'rsubs'			=>'A subscription for this thread has been removed!', //Added v3.8
	'isubs'				=>'You will be notified by email when someone else replies to that thread.', //Added v3.8
	'goadvanced'	=>'Go Advanced', //Added v3.9	
	'sforums'	 =>'Subforums', //Updated in v6.2
	'tbdays'=>'Today\'s Birthdays', //Added v3.9
	'tbdayss'=>'<b>{1}</b> members are celebrating their birthday today!',  //Added v3.9
	'rate1'=>'Rate Thread',//Added v4.0
	'rate2'=>'Rate This Thread',//Added v4.0
	'rateop5'=>'Excellent',//Added v4.0
	'rateop4'=>'Good',//Added v4.0
	'rateop3'=>'Average',//Added v4.0
	'rateop2'=>'Bad',//Added v4.0
	'rateop1'=>'Terrible',//Added v4.0
	'ratenow'=>'Rate Now',//Added v4.0
	'rateresult1'=>'Your vote on this thread has been added.',//Added v4.0
	'rateresult2'=>'You have already voted on this thread.',//Added v4.0	
	'rateresult3'=>'You have selected an invalid rating for this thread.',//Added v4.0
	'rateresult4'=>'Invalid Post Hash!', //Added v4.0
	'sticky'=>'<strong>Sticky:</strong> ', //Added v4.0
	'tratingimgalt'=>'Thread Rating: {1} votes, {2} average.',//Added v4.0
	'showandclose'=>'Show Thread & Close Window',//Added v4.0
	'poll1'=>'Post a Poll',//Added v4.0
	'poll2'=>'Yes, post a poll with this thread',//Added v4.0
	'poll3'=>'Number of poll options:',//Added v4.0
	'poll4'=>'Poll Question:',//Added v4.0
	'poll5'=>'Poll Options',//Added v4.0
	'poll6'=>'Option {1}',	//Added v4.0
	'poll7'=>'Submit New Poll',//Added v4.0
	'poll8'=>'Please complete both the question field and at least 2 option fields.',//Added v4.0
	'poll9'=>'You cannot add a poll to this thread because it already has a poll attached to it.',//Added v4.0
	'poll10'=>'Thank you for posting! You will now be taken to your post. If you opted to post a poll, you will now be allowed to do so.',//Added v4.0
	'poll11'=>'You have already voted on this poll!',//Added v4.0
	'poll12'=>'This poll is closed',//Added v4.0
	'poll13'=>'You may not vote on this poll!',//Added v4.0
	'poll14'=>'View Poll Results: ',//Added v4.0
	'poll15'=>'Votes: ',//Added v4.0
	'poll16'=>'Edit Poll',//Added v4.0
	'poll17'=>'Poll',//Added v4.0
	'poll18'=>'View Poll Results',//Added v4.0
	'poll19'=>'Vote Now',//Added v4.0
	'poll20'=>'You did not select an option to vote for. Please press back to return to the poll and choose an option before voting.',//Added v4.0
	'poll21'=>'Invalid Poll!',//Added v4.0
	'poll22'=>'Closed Poll',//Added v4.0
	'poll23'=>'To close this poll, check this box.<br />Note: Closing this poll makes it impossible to vote. It however does not stop people from replying to the thread',//Added v4.0
	'modlist'=>'Moderator(s): {1}',//Added v4.1
	'hidden'=>'<i><b>Hidden</b></i>',//Added v4.1
	'fpassword'=>'Your administrator has required a password to access this forum!',//Added v4.1
	'fpassword2'=>'Please enter this password now. Note: This requires cookies!',//Added v4.1
	'fpassword3'=>'Login',//Added v4.1
	'modnotice1'			=>'Moderator Message:',//Added v4.1
	'modnotice2'			=>'Activate the checkbox to remove this Moderator Message.',//Added v4.1
	'starter'=>'Thread Starter',//Added in v5.0
	'rating'=>'Rating',//Added in v5.0
	'foptions'=>'Forum Options',//Added in v5.0
	'toptions'=>'Thread Options',//Added in v5.0
	'pthread'=>'Print This Thread',//Added in v5.0
	'ethread'=>'Email This Thread',//Added in v5.0
	'ethreadh'=>'Send Thread to a Friend',//Added in v5.0
	'fname'=>'Friend Name:',//Added in v5.0
	'femail'=>'Friend Email:',//Added in v5.0
	'tsubject'=>'Message Subject:',//Added in v5.0
	'tmsg'=>'Message:',//Added in v5.0
	'tmsgh'=>'I thought you might be interested in reading this web page: {1}

From,
{2}
	',
	'tmsgs'=>'{1},

This is a message from {2} ( {3} ) from the {4} Community Forum ( {5} ).

The message is as follows:

{6}

{4} Community Forum takes no responsibility for messages sent through its system.',//Added in v5.0
	'picons1'	=>	'Post Icons: ',//Added in v5.0
	'picons2'	=>	'You may choose an icon for your message from the following list:',//Added in v5.0
	'pcions3'	=>	'No Icon',//Added in v5.0
	'sthread' => 'Search this Thread',//Added v5.3	
	'mop1'=>'Open Threads',//Added v5.3
	'mop2'=>'Close Threads',//Added v5.3
	'mop3'=>'Sticky Threads',//Added v5.3
	'mop4'=>'Un-Sticky Threads',//Added v5.3
	'mop5'=>'Merge Threads',//Added v5.3
	'mop6'=>'Destination Thread',//Added v5.3
	'mergeerror'=>'Not much would be accomplished by merging this item with itself.',//Added v5.3
	'top'=>'Top',//Added v5.3
	'thank'=>'The following user says thank you to {1} for this useful post:',//Added v5.6
	'thanks'=>'The following {1} users say thank you to {2} for this useful post:',//Added v5.6
	'thanked'=>'You have already thanked this post!',//Added v5.6
	'awards'=>'Awards',//Added v5.7
	'mergeposts'=>'Merge Posts',//Added v5.7
	'moveposts'=>'Move Posts',//Added v5.7
	'moveposts2'=>'Move Posts to New Thread',//Added v5.7
	'moveposts3'=>'Move Posts to Existing Thread',//Added v5.7
	'moveposts4'=>'Use the field below to specify the id of the thread that the selected posts are to be merged into.<br />Note that all posts will be inserted into their chronological positions within this thread.',//Added v5.7
	'moveposts5'=>'Destination Forum',//Added v5.7
	'copyposts'=>'Copy Posts',//Added v5.7
	'copyposts2'=>'Copy Posts to New Thread',//Added v5.7
	'copyposts3'=>'Copy Posts to Existing Thread',//Added v5.7
	'userviewing'=>' ({1} Viewing)',//Added v5.7
	'bookmarks'=>'Bookmarks',//Added v6.2	
	'you_have_p'=>'You have posted in this thread',//Added v6.2
	't_new_posts'		=>'Thread Contains New Posts',//Added v6.2
	't_no_new_posts'=>'Thread Contains No New Posts',//Added v6.2
	'qorder1'=>'Last Post Time',//Added v6.2
	'qorder2'=>'Thread Start Time',//Added v6.2
	'asc'=>'Ascending',//Added v6.2
	'desc'=>'Descending',//Added v6.2
	'qorder3'=>'Last Day',//Added v6.2
	'qorder4'=>'Last 2 Days',//Added v6.2
	'qorder5'=>'Last Week',//Added v6.2
	'qorder6'=>'Last 10 Days',//Added v6.2
	'qorder7'=>'Last 2 Weeks',//Added v6.2
	'qorder8'=>'Last Month',//Added v6.2
	'qorder9'=>'Last 45 Days',//Added v6.2
	'qorder10'=>'Last 2 Months',//Added v6.2
	'qorder11'=>'Last 75 Days',//Added v6.2
	'qorder12'=>'Last 100 Days',//Added v6.2
	'qorder13'=>'Last Year',//Added v6.2
	'qorder14'=>'Beginning',//Added v6.2
	'qtitle1'=>'Sorted By',//Added v6.2
	'qtitle2'=>'Sort Order',//Added v6.2
	'qtitle3'=>'From The',//Added v6.2
	'qtitlemain'=>'Display Options',//Added v6.2
	'p_thread'=>'Previous Thread',//Added v6.2
	'n_thread'=>'Next Thread',//Added v6.2
	'p_error'=>'There are no threads older than the previous one. Click the back button in your browser to return to it.',//Added v6.2
	'n_error'=>'There are no threads newer than the previous one. Click the back button in your browser to return to it.',//Added v6.2
	'stats2'=>'<b>Attachment Statistics</b>',//Added v6.2
	'stats2_details'=>'Total Attachments: <b>{1}</b><br />Disk Space Used: <b>{2}</b><br />Average Attachment Filesize: <b>{3}</b><br />Total Downloads: <b>{4}</b><br />',//Added v6.2
	'moderatemsg1'=>'Thank you for posting! Your post will not be visible until a moderator has approved it for posting.',//Added in v7.0
	'moderatemsg2'=>'Thread(s) / Post(s) awaiting moderation!',//Added in v7.0
	'moderatemsg3'=>'Approve Threads',//Added in v7.0
	'moderatemsg4'=>'Un-Approve Threads',//Added in v7.0
	'moderatemsg5'=>'Approve Posts',//Added in v7.0
	'moderatemsg6'=>'Un-Approve Posts',//Added in v7.0
	'moderatemsg7'=>'This post awaiting moderation!',//Added in v7.0
	'moderatemsg8'=>'This thread awaiting moderation!',//Added in v7.0
	'moderatemsg9'=>'This attachment awaiting moderation!',//Added in v7.0
	'moderatemsg10'=>'Approve Attachments',//Added in v7.0
	'moderatemsg11'=>'Un-Approve Attachments',//Added in v7.0
	'thanksbeforedl'=>'Sorry, you must click on thank you button before download this attachment.',//Added in v7.1

	'x_posted_thread' => '[B]{1}[/B] has just posted the thread: [B]{2}[/B]',//Added in v7.5
	'x_replied_thread' => '[B]{1}[/B] has just replied to the thread: [B]{2}[/B]',//Added in v7.5
);
?>