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

// viewrequests.php
$language['requests'] = array 
(	
	'offline'						=>'Sorry, Request section currently disabled.',
	'noreqid'						=>'No Request with that ID!',
	'addtitle3'						=>'Comment on Request: {1}',
	'addtitle4'						=>'Add Request Comment',
	'addtitle5'						=>'Edit Request Comment',
	'addtitle6'						=>'Add Request',
	'sure'							=>'You\'re about to delete this comment. Click <a $href = ?$action = delete&$cid = {1}&$sure = 1{2}>here</a>, if you\'re sure.',	
	'goback'						=>'Back',
	'rhead'							=>'Requests Page',
	'reqrules'						=>'Request Rules',
	'reqrulesinfo1'					=>'To make a request you must have a ratio of at least <b>0.5</b> and have uploaded at least <b>10 GB</b>.<br />A request will also cost you <b><a class=altlink $href = mybonus.php>5 Karma Points</a></b>....<br /><br />',
	'permissionerror'				=>'<font $color = "red">We see that you have <b>not</b> met the minimum requirements.</font>',
	'searchbeforereq'				=>'Please search torrents before adding a request!',
	'in'							=>'in',
	'alltypes'						=>'(all types)',
	'incdead'						=>'including dead torrents',
	'addreqinfo'					=>'Requests are for Users with a good ratio who have uploaded at least 10 gigs Only... Share and you shall recieve!',
	'field1'						=>'Title:',
	'field2'						=>'(select a category)',
	'field3'						=>'Image:',
	'field4'						=>'(Direct link to image, NO TAGS NEEDED! Will be shown in description!)',
	'field5'						=>'Description',
	'field6'						=>'Category',
	'field7'						=>'Request',
	'field8'						=>'Added',
	'field9'						=>'Requested By',
	'field10'						=>'Show ALL',
	'field11'						=>'Edit Request',
	'field12'						=>'Delete Request',
	'field13'						=>'Re-set Request',
	'field14'						=>'Vote for this request:',
	'field15'						=>'Vote',
	'field16'						=>'Report Request',
	'field17'						=>'<b>Torrent ID:</b>',
	'field18'						=>'Fill Request',
	'field19'						=>'This Request was filled:',
	'error1'						=>'You must enter a title!',
	'error2'						=>'You must select a category to put the request in!',
	'error3'						=>'You must enter a description!',
	'error4'						=>'Image MUST be in jpg, gif or png format.',
	'error5'						=>'No Request with this ID!',
	'error6'						=>'Something is wrong with that url.<br /> URL <u>must</u> be: <b>{1}/details.php?$id = (torrent id)</b>',
	'rhead2'						=>'Request of Details: ',
	'by'							=>'by:',
	'ratio'							=>'ratio',
	'at'							=>'at',
	'gmt'							=>'GMT',
	'edit'							=>'Edit',
	'delete'						=>'Delete',
	'report'						=>'Report',
	'pm'							=>'PM',
	'profile'						=>'Profile',
	'editedby'						=>'Edited by',
	'addcomment'					=>'Add Comment',
	'rhead3'						=>'Edit Request',
	'staffonly'						=>'Staff Only',
	'filled'						=>'Filled:',
	'filledbyid'					=>'Filled by ID:',
	'filledurl'						=>'Torrent URL:',
	'rhead4'						=>'Request Filled',
	'filledmsg'						=>'Your request, [b]{1}[/b] has been filled by [b]{2}[/b].
	
	You can download your request from [b][$url = {3}]{3}[/url][/b].
	Please do not forget to leave thanks where due.
	
	If for some reason this is not what you requested, please reset your request so someone else can fill it by following [b][$url = {4}/viewrequests.php?do=reset_request&$rid = {5}]this[/url][/b] link.
	
	Do [b]NOT[/b] follow this link unless you are sure that this does not match your request.',
	'filledmsgsubject'				=>'Request Filled!',
	'filledvotemsg'					=>'The Request you voted for [b]{1}[/b] has been filled by [b]{2}[/b].
	
	You can download your request from [b][$url = {3}]{3}[/url][/b].
	
	Please do not forget to leave thanks where due.',
	'filledvotesubject'				=>'Request {1} was just uploaded!',
	'voters'						=>'Voters',	
	'username'						=>'Username',
	'ul'							=>'Uploaded',
	'dl'							=>'Downloaded',
	'sratio'						=>'Share Ratio',
	'sanity'						=>'You are about to delete this request. Click <a $href = {1}?$id = {2}&$del_req = 1&$sure = 1>here</a>, if you are sure.',
	'rtitle'						=>'Request Title',
	'makereq'						=>'Add a new request',
	'viewreq'						=>'View my requests',
	'hidefilled'					=>'Hide Filled',
	'onlyfilled'					=>'Only Filled',
	'viewselected'					=>'view only selected',
	'searchreq'						=>'Search Requests',
	'filled?'						=>'Filled?',
	'filledby'						=>'Filled By',
	'votes'							=>'Votes',
	'selectall'						=>'select all',
	'unselectall'					=>'un-select all',
	'deleteselected'				=>'delete selected',
	'nothingfound'					=>'Nothing found!',
	'orphaned'						=>'(orphaned)',
	'status'						=>'Status:',
	'pending'						=>'Pending',
	'denied'						=>'Denied',
	'allowed'						=>'Allowed',
	'allow'							=>'Allow',
	'votesdecide'					=>'Lets Votes Decide',
	'for'							=>'For',
	'against'						=>'Against',	
	'viewreq'						=>'View Requests',
	'comm'							=>'Comm.',
	'addedby'						=>'Added By',
	'shoutbOT'          =>'There is a new request: {1} by {2}.',//Added v4.1
	'already_voted'	=>'It appears you\'ve already voted for this request, you can only vote once!', //Added v4.3
	'not_voted_yet'	=>'We could not find a vote for this request. Either you haven\'t voted yet or Request has already been filled.', //Added v4.3
	'f_image_not_filled'=>'Request has not yes been filled.',//Added v4.3
	'f_image_filled'=>'Request has been filled.',//Added v4.3
	'add_vote'=>'Add Vote',//Added v4.3
	'remove_vote'=>'Remove Vote',//Added v4.3
	'are_you_sure'=>'Are you sure that you want to delete this request?',//Added v4.3
	'can_not_add'	=>'You have already one un-filled request, you can only request once.',//Added v4.3
	'no_perm'=>'Sorry, you have no permission to add a request!',//Added v4.3
	'action'=>'Action(s)',//Added v4.3
	'return'=>'Return to Requests',//Added v4.3
	'view_details'=>'View torrent details',//Added v4.3
	'words'=>'Search Word(s): ',//Added v4.3
	'searcherror'	=>'Sorry, but no results were returned using the query information you provided. Please redefine your search terms and try again.',
	'searcherror2'	=>'You did not enter any search terms. At a minimum, you must enter either some search terms. (Min. 3 chars)',
	'nothingfound'=>'There is no request to show!',
	'filledrequests'=>'Filled Requests',//Added v6.0
	'unfilledrequests'=>'UnFilled Requests',//Added v6.0
	'pointerror'=>'Sorry, you have insufficient bonus points.',//Added v6.0
);
?>