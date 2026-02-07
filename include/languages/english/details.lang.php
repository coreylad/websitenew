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
Translation by xam Version: 1.7

*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// details.php
$language['details'] = array
(
	'insertcomment'				=>'Insert Comment',
	'report'					=>'Report',
	'bookmark'					=>'Bookmark',
	'removebookmark'			=>'Remove Bookmark',
	'viewsnatches'				=>'View Snatches',
	'editorrent'				=>'Edit This Torrent',
	'unknown'					=>'Unknown',
	'userip'					=>'USER/IP',
	'conn'						=>'CONN.',
	'up'						=>'UP',
	'urate'						=>'U.RATE',
	'down'						=>'DOWN',
	'drate'						=>'D.RATE',
	'ratio'						=>'RATIO',
	'done'						=>'DONE',
	'since'						=>'SINCE',
	'idle'						=>'IDLE',
	'client'					=>'CLIENT',
	'yes'						=>'Yes',
	'no'						=>'No',
	'inf'						=>'Inf.',
	'detailsfor'				=>'Details for torrent " {1} "',
	'uploaded'					=>'Successfully uploaded!',
	'uploadednote'				=>'<p>You can start seeding now. <b>Note</b> that the torrent won\'t be visible until you do that!</p>',
	'edited'					=>'Successfully edited!',
	'goback'					=>'<p><b>Go back to <a $href = "{1}">whence you came</a>.</b></p>',
	'singleresult'				=>'<div class=success>Your search for " {1} " gave a single result:</div>',
	'bookmarked'				=>'<div class=success>Bookmark added!</div>',
	'bookmarked2'				=>'<div class=error>No need to bookmark this torrent twice now do we?</div>',
	'bookmarked3'				=>'<div class=error>Bookmark deleted!</div>',
	'download'					=>'Download',
	'nodlpermission'			=>'You are not allowed to download.',
	'infohash'					=>'Info Hash',
	'description'				=>'Description',
	'viewnfo'					=>'View NFO',
	'visible'					=>'Visible',
	'visible2'					=>'NO (dead)',
	'banned'					=>'Banned',
	'sticky'					=>'Sticky',
	'type'						=>'Type',
	'type2'						=>'(none selected)',
	'lastactivity'				=>'Last activity',
	'activity'					=>'Activity',
	'size'						=>'Size',
	'bytes'						=>'bytes',
	'noneyet'					=>'none yet (needs at least {1} votes and has got ',
	'none'						=>'none',
	'only'						=>'only',
	'novotes'					=>'No votes yet',
	'invalid'					=>'invalid?',
	'added'						=>'Added',
	'views'						=>'Views',
	'hits'						=>'Hits',
	'snatched'					=>'Snatched',
	'snatched2'					=>'time(s)',
	'snatched3'					=>'<--- Click Here to  all View Snatches',
	'progress'					=>'Progress',
	'uppedby'					=>'Uploader',
	'numfiles'					=>'Num files<br /><a $href = "'.$BASEURL.'/details.php?$id = {1}&$filelist = 1{2}#filelist" class="sublink">[see list]</a>',//Updated in v7.3
	'numfiles2'					=>'in {1} file(s)',
	'numfiles3'					=>'File Details',
	'path'						=>'Path',
	'filelist'					=>'File list</a><br /><a $href = "'.$BASEURL.'/details.php?$id = {1}{2}" class="sublink">[Hide list]</a>',//Updated in v7.3
	'askreseed'					=>'Reseed',
	'askreseed2'				=>'Click <a $href = "'.$BASEURL.'/takereseed.php?$reseedid = {1}"><b>here</b></a> to Ask for a reseed!',//Updated in v7.3
	'peers'						=>'Peers<br /><a $href = "'.$BASEURL.'/details.php?$id = {1}&$dllist = 1{2}#seeders" class="sublink">[see list]</a>',//Updated in v7.3
	'peers2'					=>'{1} seeder(s), {2} leecher(s) = {3} peer(s) total',
	'peersb'					=>'Peers',
	'peers3'					=>'{1} seeder(s), {2} leecher(s) = {3} peer(s) total<br /><font $color = red>Sorry, permission denied!</font>',
	'seeders'					=>'Seeders</a><br /><a $href = "'.$BASEURL.'/details.php?$id = {1}{2}" class="sublink">[Hide list]</a>',//Updated in v7.3
	'seeders2'					=>'Seeder(s)',
	'leechers'					=>'Leechers</a><br /><a $href = "'.$BASEURL.'/details.php?$id = {1}{2}" class="sublink">[Hide list]</a>',//Updated in v7.3
	'leechers2'					=>'Leecher(s)',
	'nothanksyet'				=>'no thanks added yet!',
	'thanksby'					=>'The following user users said thanks to the torrent uploader:',
	'torrentinfo'					=>'Torrent Info',
	'commentsfor'				=>'Comments for torrent "{1}"',
	'nocommentsyet'			=>'There are no comments yet. Be the First to Comment!',
	'quickcomment'			=>'<b>Quick Comment</b>',
	't_link'						=>'IMDB/Web Link', // Changed v3.6
	't_image'						=>'Torrent Image',
	'lastupdate'					=>'Last updated', // Added v3.7
	'warnexternal'				=>'Warning!!!\n----------------\nYou are about to download an external torrent which means download and upload stats aren\'t recorded for this torrent!\n\nClick \"OK\" to continue downloading!', // Added v3.9
	'close'=>'Close Comment',//Added v4.1
	'open'=>'Open Comment',//Added v4.1
	'dltorrent'=>'Download Torrent',//Added in v5.0
	'comments'=>'Comments',//Added in v5.0
	'na'=>'N/A',//Added in v5.0
	'scene3'=>'Pre-Time',//Added in v5.2
	'newrating'=>'Thanks for voting.. You rated: ',//Added in v5.3
	'alreadyvotes'=>'You voted already!',//Added in v5.3
	'ratedetails'=>'{1} rating from {2} vote(s).',//Added in v5.3
	'bigfile'	=>	 '<b>Number of files ({1}) in this torrent too high to show file-list!</b>',//Added in v5.3
	'awatingmoderation' => 'This torrent awating moderation.',//Added in v5.7
	'approvedsbj'=>'Your torrent has been approved!',//Added in v5.7
	'approvedmsg'=>'Hi,

Your torrent has been approved by [b]{1}[/b].

Please start seeding the torrent (you may have to re-download the torrent for seeding).

Torrent Details:
-------------------------------------------
{2}

Have a great day.
',//Added in v5.7
'rtorrent'=>'Rate Torrent: ',//Added in v5.7
'reporting'=>'Please report this torrent in opened popup window.',//Added in v6.0
'smililartorrents'=>'Similar Torrents',//Added in v6.3
'announced'=>'Announced', //Added in v7.0
'username'=>'Username', //Added in v7.0
'uploaded'=>'Uploaded', //Added in v7.0
'downloaded'=>'Downloaded', //Added in v7.0
'left'=>'Left', //Added in v7.0
'completed'=>'Completed', //Added in v7.0
'seedtime'=>'Seed Time',//Added in v7.0
'leechtime'=>'Leech Time',//Added in v7.0
'speed'=>'Speed',//Added in v7.1
's_message'=>'Send PM to Snatched Users', //Added in v7.1
'laction' => '(Last peer action: {1})',//Added in v7.3
'downloadwarning' => '<p $style = "color: red; font-weight: bold; text-decoration: underline;">PAY ATTENTION TO YOUR RATIO!</p>
<p>Your ratio is <span $style = "color: #ff0532; font-weight: bold;">{1}</span> - meaning that you have only uploaded <span $style = "color: #ff0532; font-weight: bold;">{2}</span> of the amount you downloaded.<br />
It\'s important to maintain a good ratio because it helps to make downloads faster for all members.</p>
<p>You can improve your ratio by leaving your torrent running after the download completes.<br />You must maintain a minimum ratio of <span $style = "color: #ff0532; font-weight: bold;">{3}</span> or your download privileges will be removed.</p>',//Added in v7.3
);
?>