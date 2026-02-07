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
define('THIS_SCRIPT', 'details.php');
define('D_VERSION', '3.9.0 by xam');
define('SKIP_MOD_QUERIES', true);
define('SKIP_CACHE_MESSAGE', true);
require('./global.php');
require_once(INC_PATH.'/commenttable.php');
$lang->load('details');
$lang->load('browse');

$TSSEConfig->TSLoadConfig('ANNOUNCE');

if($usergroups['candownload'] != 'yes' OR !isset($CURUSER))
{
	print_no_permission(false);
}


$torrentId = intval(TS_Global('id'));

if (!is_valid_id($torrentId)) {
	print_no_permission(false);
}

$tabSection = 'details';
if (isset($_GET['tab']) && $_GET['tab'] != '') {
	$tabSection = htmlspecialchars_uni($_GET['tab']);
}

$defaultTemplate = ts_template();
$imageDir = $BASEURL.'/include/templates/'.$defaultTemplate.'/images/torrent_flags/';
$templateImageDir = $BASEURL.'/include/templates/'.$defaultTemplate.'/images/';

$torrentQuery = 'SELECT t.id, t.name, t.info_hash, t.allowcomments, t.banned, t.descr, t.category, t.size, t.numfiles, t.anonymous, t.added, t.comments, t.hits, t.times_completed, t.leechers, t.seeders, t.owner, t.free, t.sticky, t.offensive, t.silver, t.t_image, t.t_link, t.isnuked, t.WhyNuked, t.isrequest, t.ts_external, t.doubleupload, t.isScene, t.moderate, t.download_multiplier, t.upload_multiplier, t.ts_external_lastupdate, t.mtime, t.directdownloadlink, n.nfo, c.name as categoryname, c.canview, c.pid, c.type, c.id as categoryid, d.video_info, d.audio_info, u.username, u.donor, u.warned, u.leechwarn, g.namestyle FROM torrents t LEFT JOIN ts_nfo n ON (t.id=n.id) LEFT JOIN categories c ON (t.category=c.id) LEFT JOIN ts_torrents_details d ON (t.id=d.tid) LEFT JOIN users u ON (t.owner=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE t.id = '.sqlesc($torrentId);

if ($UseMemcached) {
	$memcacheHash = 'details_' . $torrentId;
	if ($torrent = $TSMemcache->check($memcacheHash)) {
		//Cache exists, lets use it.
	} else {
		$torrentResult = sql_query($torrentQuery) or sqlerr(__FILE__, __LINE__);
		if (!mysqli_num_rows($torrentResult)) {
			stderr($lang->global['error'], $lang->global['notorrentid']);
		}
		$torrent = mysqli_fetch_assoc($torrentResult);
		$TSMemcache->add($memcacheHash, $torrent);
	}
} else {
	$torrentResult = sql_query($torrentQuery) or sqlerr(__FILE__, __LINE__);
	if (!mysqli_num_rows($torrentResult)) {
		stderr($lang->global['error'], $lang->global['notorrentid']);
	}
	$torrent = mysqli_fetch_assoc($torrentResult);
}

if ($torrent['id'] != $torrentId) {
	stderr($lang->global['error'], $lang->global['notorrentid']);
}

if ($torrent['canview'] != '[ALL]' AND !in_array($CURUSER['usergroup'], explode(',',$torrent['canview'])))
	print_no_permission(true);
elseif ($torrent['banned'] == 'yes' && !$is_mod)
	stderr($lang->global['error'], $lang->global['torrentbanned']);
elseif ($torrent['moderate'] == '1' && !$is_mod)
	stderr($lang->global['error'], $lang->details['awatingmoderation']);

$APPROVED=false;
if ($is_mod AND isset($_GET['approve']) AND $_GET['approve'] == 'true' AND $torrent['moderate'] == '1')
{
	sql_query('UPDATE torrents SET moderate = \'0\' WHERE id = '.sqlesc($id)) or sqlerr(__FILE__,__LINE__);
	if (mysqli_affected_rows($GLOBALS['DatabaseConnect']))
	{
		$Msg=sprintf($lang->details['approvedmsg'], $CURUSER['username'], '[URL]'.$BASEURL.'/details.php?id='.$id.'[/URL]');
		require_once(INC_PATH.'/functions_pm.php');
		send_pm($torrent['owner'], $Msg, $lang->details['approvedsbj']);
		$APPROVED=true;
		//===add karma
		$TSSEConfig->TSLoadConfig('KPS');
		KPS("+",$kpsupload,$torrent['owner']);
		//===end
	}
}

$SimilarTorrents='';
if ($showsmiliartorrents == 'yes')
{
	$query = sql_query("SELECT MATCH(t.name) AGAINST('".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], $torrent['name'])."' IN BOOLEAN MODE) as score, t.id, t.name, t.anonymous, t.owner, t.category, t.size, t.added, t.seeders, t.leechers, c.image as catimage, c.name as catname, u.username, g.namestyle FROM torrents t LEFT JOIN categories c ON (c.id=t.category) LEFT JOIN users u ON (t.owner=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE MATCH(t.name) AGAINST('".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], $torrent['name'])."' IN BOOLEAN MODE) AND t.id != '$id' AND t.visible = 'yes' AND t.banned = 'no' AND t.moderate = '0' ORDER BY score DESC LIMIT 10") or sqlerr(__FILE__,__LINE__);
	if (mysqli_num_rows($query) > 0)
	{
		$FoundSMTQ = '';
		while($SMTQ = mysqli_fetch_assoc($query))
		{
			if ($SMTQ['score'] > 4)
			{
				$SEOLink = ts_seo($SMTQ['id'], $SMTQ['name'],'s');
				$SEOLinkC = ts_seo($SMTQ['category'],$SMTQ['catname'],'c');
				$FoundSMTQ .= '
				<tr>
					<td align="center" style="width: 40px; height: 36px;" >
						<a href="'.$SEOLinkC.'"><img src="'.$pic_base_url.$table_cat.'/'.$SMTQ['catimage'].'" border="0" alt="'.$SMTQ['catname'].'" title="'.$SMTQ['catname'].'" width="'.$table_cat_width.'" height="'.$table_cat_height.'" /></a>
					</td>
					<td>
						<a href="'.$SEOLink.'">'.htmlspecialchars_uni($SMTQ['name']).'</a>
					</td>
					<td>
						'.my_datee($dateformat.' '.$timeformat, $SMTQ['added']).'
					</td>
					<td>
						'.(!$is_mod && $SMTQ['owner'] != $CURUSER['id'] && $SMTQ['anonymous'] == 'yes' ? '
						<div class="gray">
							'.$lang->global['anonymous'].'
						</div>' : '
						<a href="'.ts_seo($SMTQ['owner'], $SMTQ['username'], 'u').'">'.get_user_color($SMTQ['username'], $SMTQ['namestyle']).'</a>
						'.($SMTQ['anonymous'] == 'yes' ? '
						<div class="gray">
							'.$lang->global['anonymous'].'
						</div>
						' : '').'
						').'
					</td>
					<td align="center">
						'.ts_nf($SMTQ['seeders']).'
					</td>
					<td align="center">
						'.ts_nf($SMTQ['leechers']).'
					</td>
				</tr>';
			}
		}
		if ($FoundSMTQ)
		{
			$SimilarTorrents = '
			<br />
			<table width="100%" border="0" class="none" style="clear: both;" cellpadding="4" cellspacing="0">
				<tr>
					<td class="thead" colspan="6">
						'.ts_collapse('SimilarTorrents').'
						'.$lang->details['smililartorrents'].'
					</td>
				</tr>
				'.ts_collapse('SimilarTorrents',2).'
				<tr>
					<td class="subheader" width="5%">'.$lang->details['type'].'</td>
					<td class="subheader" width="45%">'.$lang->global['name'].'</td>
					<td class="subheader"width="15%">'.$lang->details['added'].'</td>
					<td class="subheader" width="15%">'.$lang->details['uppedby'].'</td>
					<td class="subheader" align="center" width="10%">'.$lang->details['seeders2'].'</td>
					<td class="subheader" align="center" width="10%">'.$lang->details['leechers2'].'</td>
				</tr>
				'.$FoundSMTQ.'
			</table>
			';
		}
	}
}

$Subtitles = '';
if ($showsubtitles == 'yes')
{
	$query = sql_query("SELECT s.*, u.username, g.namestyle, c.name, c.flagpic FROM ts_subtitles s LEFT JOIN users u ON (s.uid=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) LEFT JOIN countries c ON (s.language=c.id) WHERE tid = '$id' ORDER by s.date");
	if (mysqli_num_rows($query) > 0)
	{
		$lang->load('ts_subtitles');
		$Subtitles = '
		<table width="100%" border="0" class="none" style="clear: both;" cellpadding="4" cellspacing="0">
			<tr>
				<td class="thead" colspan="8">
					'.ts_collapse('ts_subtitles').'
					<a href="'.$BASEURL.'/ts_subtitles.php">'.$lang->ts_subtitles['head'].'</a>
				</td>
			</tr>
			'.ts_collapse('ts_subtitles',2).'
			<tr>
				<td class="subheader" width="45%" align="left">'.$lang->ts_subtitles['1'].'</td>
				<td class="subheader" width="5%" align="center">'.$lang->ts_subtitles['dltitle'].'</td>
				<td class="subheader" width="10%" align="center">'.$lang->ts_subtitles['2'].'</td>
				<td class="subheader" width="5%" align="center">'.$lang->ts_subtitles['3'].'</td>
				<td class="subheader" width="5%" align="center">'.$lang->ts_subtitles['4'].'</td>
				<td class="subheader" width="5%" align="center">'.$lang->ts_subtitles['dlcount'].'</td>
				<td class="subheader" width="15%" align="center">'.$lang->ts_subtitles['5'].'</td>
				<td class="subheader" width="10%" align="center">'.$lang->ts_subtitles['6'].'</td>
			</tr>
		';
		while ($subtitle = mysqli_fetch_assoc($query))
		{
			$sstitle = htmlspecialchars_uni($subtitle['title']);
			$adminlink = $is_mod ? ' [<b><a href="ts_subtitles.php?action=delete&amp;id='.$subtitle['id'].'">'.$lang->ts_subtitles['delete'].'</a></b>]' : '';
			$editlink = '<span style="float: right;" id="sid'.$subtitle['id'].'" name="sid'.$subtitle['id'].'"><a onclick="TSOpenPopup(\''.$BASEURL.'/report.php?type=8&reporting='.$subtitle['id'].'&page='.(isset($_GET['page']) ? intval($_GET['page']) : 0).'\', \'report\', 500, 300); return false;" href="javascript:void(0);"><img src="'.$pic_base_url.'report2.gif" border="0" alt="'.$lang->ts_subtitles['report'].'" title="'.$lang->ts_subtitles['report'].'" /></a></span>';
			$editlink .= ($is_mod || $subtitle['uid'] == $CURUSER['id']) ? '[<b><a href="ts_subtitles.php?action=edit&amp;id='.$subtitle['id'].'">'.$lang->ts_subtitles['edit'].'</a></b>]' : '';

			$Subtitles .= '
			<tr>
				<td align="left">'.$sstitle.' '.$editlink.' '.$adminlink.'</td>
				<td align="center"><a href="ts_subtitles.php?action=download&amp;id='.$subtitle['id'].'"><img src="'.$Imagedir.'dl.png" border="0" class="inlineimg" alt="'.$lang->ts_subtitles['download'].'" title="'.$lang->ts_subtitles['download'].'"></a></td>
				<td align="center"><img src="'.$pic_base_url.'flag/'.$subtitle['flagpic'].'" border="0" alt="'.htmlspecialchars_uni($subtitle['name']).'" title="'.htmlspecialchars_uni($subtitle['name']).'"></td>
				<td align="center">'.htmlspecialchars_uni($subtitle['fps']).'</td>
				<td align="center">'.htmlspecialchars_uni($subtitle['cds']).'</td>
				<td align="center">'.ts_nf($subtitle['dlcount']).'</td>
				<td align="center">'.my_datee($dateformat, $subtitle['date']).' '.my_datee($timeformat, $subtitle['date']).'</td>
				<td align="center"><a href="'.ts_seo($subtitle['uid'], $subtitle['username']).'">'.get_user_color($subtitle['username'], $subtitle['namestyle']).'</a></td>
			</tr>
			';
		}
		$Subtitles .= '</table><br />';
	}
}

$lang->load('upload');
$lang->load('quick_editor');
require_once(INC_PATH.'/class_tsquickbbcodeeditor.php');
$QuickEditor = new TSQuickBBCodeEditor();
$QuickEditor->ImagePath = $pic_base_url;
$QuickEditor->SmiliePath = $pic_base_url.'smilies/';

include(INC_PATH.'/functions_quick_editor.php');
define('TOTAL_FILES', $torrent['numfiles']);
include(INC_PATH.'/functions_filelist.php');
require_once(INC_PATH.'/functions_mkprettytime.php');

if ($torrent['type'] == 's')
{
	require(TSDIR.'/'.$cache.'/categories.php');
	foreach ($_categoriesC as $catarray)
	{
		if ($catarray['id'] == $torrent['pid'])
		{
			$parentcategory = $catarray['name'];
			$parentcatid = $catarray['id'];
			break;
		}
	}
	if ($parentcategory && $parentcatid)
	{
		$seolink = ts_seo($parentcatid,$parentcategory,'c');
		$seolink2 = ts_seo($torrent['categoryid'],$torrent['categoryname'],'c');
		add_breadcrumb($parentcategory, $seolink);
		add_breadcrumb($torrent['categoryname'], $seolink2);
		$torrent["categoryname"] = '<a href="'.$seolink.'" target="_self" alt="'.$parentcategory.'" title="'.$parentcategory.'" />'.$parentcategory.'</a> / <a href="'.$seolink2.'" target="_self" alt="'.$torrent['categoryname'].'" title="'.$torrent['categoryname'].'" />'.$torrent['categoryname'].'</a>';
	}
}
else
{
	$seolink2 = ts_seo($torrent['categoryid'],$torrent['categoryname'],'c');
	add_breadcrumb($torrent['categoryname'], $seolink2);
	$torrent["categoryname"] = '<a href="'.$seolink2.'" target="_self" alt="'.$torrent['categoryname'].'" title="'.$torrent['categoryname'].'" />'.$torrent['categoryname'].'</a>';
}

$isfree = $issilver = $isdoubleupload = false;
$___notice = '';
if ($xbt_active != 'yes')
{
	include(TSDIR.'/'.$cache.'/freeleech.php');
	$TSSEConfig->TSLoadConfig('KPS');
	if ($__F_START < get_date_time() && $__F_END > get_date_time())
	{
		switch($__FLSTYPE)
		{
			case 'freeleech';
				$___notice = show_notice(sprintf($lang->browse['f_leech'], $__F_START, $__F_END),false,$lang->browse['f_leech_h']);
				$isfree = true;
			break;
			case 'silverleech';
				$___notice = show_notice(sprintf($lang->browse['s_leech'], $__F_START, $__F_END),false,$lang->browse['s_leech_h']);
				$issilver = true;
			break;
			case 'doubleupload';
				$___notice = show_notice(sprintf($lang->browse['d_leech'], $__F_START, $__F_END),false,$lang->browse['d_leech_h']);
				$isdoubleupload = true;
			break;
		}
	}
	elseif ($bdayreward == 'yes' AND $bdayrewardtype)
	{
		if (($curuserbday = explode('-', $CURUSER['birthday'])) AND isset($curuserbday[0]) AND isset($curuserbday[1]) AND date('j-n') === $curuserbday[0].'-'.$curuserbday[1])
		{
			switch ($bdayrewardtype)
			{
				case 'freeleech';
					$___notice = show_notice(sprintf($lang->browse['f_leech'], $curuserbday[0].'-'.$curuserbday[1].'-'.date('Y'), ($curuserbday[0] + 1).'-'.$curuserbday[1].'-'.date('Y')),false,$lang->browse['f_leech_h']);
					$isfree = true;
				break;
				case 'silverleech';
					$___notice = show_notice(sprintf($lang->browse['s_leech'], $curuserbday[0].'-'.$curuserbday[1].'-'.date('Y'), ($curuserbday[0] + 1).'-'.$curuserbday[1].'-'.date('Y')),false,$lang->browse['s_leech_h']);
					$issilver = true;
				break;
				case 'doubleupload';
					$___notice = show_notice(sprintf($lang->browse['d_leech'], $curuserbday[0].'-'.$curuserbday[1].'-'.date('Y'), ($curuserbday[0] + 1).'-'.$curuserbday[1].'-'.date('Y')),false,$lang->browse['d_leech_h']);
					$isdoubleupload = true;
				break;
			}
		}
	}
}

require_once(INC_PATH.'/functions_get_torrent_flags.php');

$metadesc = substr(strip_tags($torrent['name']), 0, 154).'...';

$HEAD = strip_tags($torrent['name']);
stdhead($HEAD, true, '<script type="text/javascript" src="'.$BASEURL.'/scripts/prototype.js?v='.O_SCRIPT_VERSION.'"></script>');
add_breadcrumb($HEAD, $_SERVER['SCRIPT_NAME']);
build_breadcrumb();

if ($hitrun == 'yes')
{
	$ratio = ($CURUSER['downloaded'] > 0 ? $CURUSER['uploaded'] / $CURUSER['downloaded'] : 0);
	$percentage = $ratio * 100;
	
	if ($torrent['free'] != 'yes' AND $usergroups['isvipgroup'] != 'yes' AND $ratio <= ($hitrun_ratio + 0.4) AND $torrent['owner'] != $CURUSER['id'] AND !$is_mod AND $CURUSER['downloaded'] <> 0)
	{
		echo show_notice(sprintf($lang->details['downloadwarning'], number_format($ratio, 2), mksize($percentage), $hitrun_ratio), true);
	}
}

$sratio = $torrent['leechers'] > 0 ? $torrent['seeders'] / $torrent['leechers'] : 1;
$lratio = $torrent['seeders'] > 0 ? $torrent['leechers'] / $torrent['seeders'] : 1;

$ShowEDetails = false;
if (!empty($torrent['video_info']) OR !empty($torrent['audio_info']))
{
	$video_info = $torrent['video_info'] != '~~~~' ? @explode('~', $torrent['video_info']) : array();
	$audio_info = $torrent['audio_info'] != '~~~' ? @explode('~', $torrent['audio_info']) : array();
	
	if (count($video_info) OR count($audio_info))
	{
		$ShowEDetails = true;
	}
}

$pagertop = '';
$pagerbottom = '';
$showcommenttable = '';
$count = TSRowCount('id', 'comments', 'torrent='.$id);
$lastcommentid = 0;
if (!$count)
{
	$showcommenttable .= '
	<table class="none" border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr>
			<td>
				<div style="display: block;" id="ajax_comment_preview">'.$lang->details['nocommentsyet'].'</div>
				<div style="display: block;" id="ajax_comment_preview2"></div>
			</td>
		</tr>
	</table>
	';
}
else
{
	$showcommenttable .= '
	<script type="text/javascript" src="'.$BASEURL.'/scripts/quick_pager.js"></script>
	';
	require(INC_PATH.'/functions_ts_ajax_pager.php');
	list($pagertop, $pagerbottom, $limit) = TSAjaxPager($ts_perpage, $count, $id);
	$subres = sql_query("SELECT c.id, c.torrent as torrentid, c.text, c.user, c.added, c.editedby, c.editedat, c.modnotice, c.modeditid, c.modeditusername, c.modedittime, c.totalvotes, c.visible, uu.username as editedbyuname, gg.namestyle as editbynamestyle, u.added as registered, u.enabled, u.warned, u.leechwarn, u.username, u.title, u.usergroup, u.last_access, u.options, u.donor, u.uploaded, u.downloaded, u.avatar as useravatar, u.signature, g.title as grouptitle, g.namestyle FROM comments c LEFT JOIN users uu ON (c.editedby=uu.id) LEFT JOIN usergroups gg ON (uu.usergroup=gg.gid) LEFT JOIN users u ON (c.user=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE c.torrent = ".sqlesc($id)." ORDER BY c.id $limit") or sqlerr(__FILE__, __LINE__);

	$allrows = array();
	while ($subrow = mysqli_fetch_assoc($subres))
	{
		$allrows[] = $subrow;
		$lastcommentid++;
	}
	$showcommenttable .= commenttable($allrows,'','',false,true,true);
}

$rowspan = 9;
$reseed = '';
if ($torrent['seeders'] == 0 && $torrent['ts_external'] == 'no' && $torrent['times_completed'])
{
	$reseed = '
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['askreseed'].'</td>
		<td valign="top" style="padding-left: 5px;">'.sprintf($lang->details['askreseed2'], $id).'</td>
	</tr>';
	$rowspan++;
}

if ($torrent['isScene'] > 0)
{
	$rowspan++;
	$isScene = '
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->upload['isScene1'].'</td>
		<td valign="top" style="padding-left: 5px;">'.$lang->global['greenyes'].'</td>
	</tr>
	';
	$rowspan++;
	$isScene .= '
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['scene3'].'</td>
		<td valign="top" style="padding-left: 5px;">'.sprintf($lang->browse['pretime'], my_datee($dateformat.' '.$timeformat, $torrent['isScene']), mkprettytime(TS_MTStoUTS($torrent['added']) - $torrent['isScene'])).'</td>
	</tr>
	';
}

if (isset($_GET['cerror']))
{
	switch ($_GET['cerror'])
	{
		case 1:
			$cerror = $lang->global['notorrentid'];
		break;
		case 2:
			$cerror = $lang->global['dontleavefieldsblank'];
		break;
		case 3:
			$cerror = sprintf($lang->global['flooderror'], $usergroups['floodlimit'], $lang->comment['floodcomment'], "-");
		break;
		default:
			$cerror = $lang->global['error'];
		break;
	}
}

if ($usergroups['canpeers'] != 'yes' OR $torrent['ts_external'] == 'yes')
{
	$peerstable = sprintf($lang->details['peers3'], ts_nf($torrent['seeders']), ts_nf($torrent['leechers']), (ts_nf($torrent['seeders'] + $torrent['leechers']))).($torrent['seeders'] == 0 && $torrent['ts_external'] == 'no' ? '<br />'.sprintf($lang->details['askreseed2'],$id) : '');
}
elseif ($xbt_active == 'yes')
{
	require(INC_PATH.'/functions_xbt_table.php');
	require(INC_PATH.'/functions_icons.php');
	$downloaders = array();
	$seeders = array();
	$subres = sql_query('SELECT p.uploaded, p.downloaded, p.fid, p.uid, p.active, p.announced, p.completed, p.`left`, p.mtime, p.up_rate, p.down_rate, p.ipa, p.port, u.id, u.enabled, u.username, u.options, u.warned, u.donor, u.leechwarn, g.namestyle, perm.canupload, perm.candownload, perm.cancomment, perm.canmessage, perm.canshout FROM xbt_files_users p LEFT JOIN users u ON (p.uid=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) LEFT JOIN ts_u_perm perm ON (p.uid=perm.userid) WHERE p.fid = '.$id.' AND p.active = 1 ORDER BY IF(p.`left` > 0, p.downloaded, p.uploaded) DESC') or sqlerr(__FILE__,__LINE__);
	while($subrow=mysqli_fetch_assoc($subres))
	{
		if ($subrow['left'] == '0')
			$seeders[] = $subrow;
		else
			$downloaders[] = $subrow;
	}
	$query = sql_query('SELECT value FROM xbt_config WHERE name = "announce_interval"');
	$Result = mysqli_fetch_assoc($query);
	$xbt_announce_interval = $Result['value'];
	$peerstable = XBT_Peers_Table($seeders, $lang->details['seeders2'], false).XBT_Peers_Table($downloaders, $lang->details['leechers2']);
}
else
{
	require(INC_PATH.'/functions_details.php');
	$downloaders = array();
	$seeders = array();
	$subres = sql_query("SELECT p.seeder, p.finishedat, p.downloadoffset, p.uploadoffset, p.ip, p.port, p.uploaded, p.downloaded, p.to_go, UNIX_TIMESTAMP(p.started) AS st, p.connectable, p.agent, p.peer_id, UNIX_TIMESTAMP(p.last_action) AS la, p.userid,  u.id, u.enabled, u.username, u.options, u.warned, u.donor, g.namestyle FROM peers p LEFT JOIN users u ON (p.userid=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE p.torrent = ".$id) or sqlerr(__FILE__,__LINE__);
	while ($subrow = mysqli_fetch_array($subres))
	{
		if ($subrow["seeder"] == "yes")
			$seeders[] = $subrow;
		else
			$downloaders[] = $subrow;
	}
	usort($seeders, 'seed_sort');
	usort($downloaders, 'leech_sort');
	$peerstable = dltable($lang->details['seeders2'], $seeders, $torrent, 'seeders');
	$peerstable .= '<br />'.dltable($lang->details['leechers2'], $downloaders, $torrent, 'leechers');
}

$QuickEditor->FormName='comment';
$QuickEditor->TextAreaName='message';
$showcommenttableQuickPost = '
<br />
'.$QuickEditor->GenerateJavascript().'
'.(!empty($cerror) ? '<div class="error">'.$cerror.'</div>' : '').'
'.($useajax == 'yes' ? '<script type="text/javascript" src="'.$BASEURL.'/scripts/quick_comment.js"></script>' : '').'
<script type="text/javascript" src="'.$BASEURL.'/scripts/quick_preview.js"></script>
<form name="comment" id="comment" method="post" action="comment.php?action=add&tid='.$id.'">
<input type="hidden" name="ctype" value="quickcomment">
<input type="hidden" name="page" value="'.intval(isset($_GET['page']) ? $_GET['page'] : 0).'">
	<div id="showcommenttableQuickPost" name="showcommenttableQuickPost" style="display: block;">
		<table width="100%" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td class="thead">'.ts_collapse('quickcomment').'<a name="startquickcomment">'.$lang->details['quickcomment'].'</a></td>
			</tr>
			'.ts_collapse('quickcomment', 2).'
			<tr>
				<td>
					'.$QuickEditor->GenerateBBCode().'
				</td>
			</tr>
			<tr>
				<td align="center"><textarea name="message" style="width:850px;height:120px;" id="message"></textarea></td>
			</tr>
			<tr>
				<td align="center">
				'.($useajax == 'yes' ? '<img src="'.$dimagedir.'loading.gif" class="inlineimg" border="0" alt="" title="" id="loading-layer" style="display:none;" /> <input type="button" class="button" value="'.$lang->global['buttonsubmit'].'" name="quickcomment" id="quickcomment" onclick="javascript:TSajaxquickcomment(\''.$id.'\', \''.$lastcommentid.'\');" />' : '<input type="submit" name="submit" value="'.$lang->global['buttonsubmit'].'" class="button" />').'
				<input type="button" class="button" name="button" value="'.$lang->global['buttonpreview'].'" onclick="javascript:TSajaxquickpreview();" /> <input type="button" value="'.$lang->global['advancedbutton'].'" class="button" onclick="jumpto(\''.$BASEURL.'/comment.php?action=add&tid='.$id.'\')" />
				</td>
			</tr>
		</table>
	</div>
</form>'.elastic();

if ($usergroups['canbookmark'] == 'yes')
{
	$onclick1 = $onclick2 = '';
	if ($useajax == 'yes')
	{
		$onclick1 = ' onclick="TSajaxquickbookmark('.$id.',\'add\'); return false;"';
		$onclick2 = ' onclick="TSajaxquickbookmark('.$id.',\'delete\'); return false;"';
	}
	$bookmark = '
	'.($useajax == 'yes' ? '
	<script type="text/javascript" src="./scripts/quick_bookmark.js"></script>' : '').'
	<p>
	<span id="bookmark-loading-layer" style="display:none; float: left;"><img src="'.$dimagedir.'loading.gif" border="0" alt="" title="" class="inlineimg"></span>
	<span id="bookmark-done-layer" style="display:none; float: left;"><img src="'.$pic_base_url.'input_true.gif" border="0" alt="" title="" class="inlineimg"></span>
	<a href="'.$BASEURL.'/bookmarks.php?action=add&torrentid='.$id.'"'.$onclick1.'>'.$lang->details['bookmark'].'</a> - <a href="'.$BASEURL.'/bookmarks.php?action=delete&torrentid='.$id.'"'.$onclick2.'>'.$lang->details['removebookmark'].'</a>
	</p>';
}
else
{
	$bookmark = $lang->global['notavailable'];
}

if($torrent['anonymous'] == 'yes' AND $torrent['owner'] != $CURUSER['id'] AND !$is_mod)
{
		$username = $lang->global['anonymous'];
}
else
{
	$username = '<a href="'.ts_seo($torrent['owner'], $torrent['username']).'">'.get_user_color($torrent['username'], $torrent['namestyle']).'</a>'.($torrent['donor'] == 'yes' ? ' <img src="'.$pic_base_url.'star.gif" alt="'.$lang->global['imgdonated'].'" title="'.$lang->global['imgdonated'].'">' : '').($torrent['warned'] == 'yes' || $torrent['leechwarn'] == 'yes' ? '<img src="'.$pic_base_url.'warned.gif" alt="'.$lang->global['imgwarned'].'" title="'.$lang->global['imgwarned'].'">' : '');
}

$rating = '';
if ($ratingsystem == 'yes')
{
	require(INC_PATH.'/class_ts_rating.php');
	$TSRating = new TS_Rating('torrent_'.$id, $CURUSER['id']);
	$SHowForm = $TSRating->ShowForm($lang->details['rtorrent']);
	$rating = '
	<script type="text/javascript" src="'.$BASEURL.'/scripts/quick_rate.js?v='.O_SCRIPT_VERSION.'"></script>
	<div id="tsrating_results" name="tsrating_results" style="padding-bottom: 5px; padding-top: 10px;">
		'.$TSRating->GetScore($lang->details['ratedetails']).'
		'.($SHowForm ? $SHowForm : '<br /><i><b>'.$lang->details['alreadyvotes'].'</b></i>').'
	</div>
	';
}

if ($thankssystem == 'yes')
{
	$AllThanks = '
	<script type="text/javascript" src="'.$BASEURL.'/scripts/quick_thanks.js?v='.O_SCRIPT_VERSION.'"></script>
	<img src="'.$dimagedir.'loading.gif" class="inlineimg" border="0" alt="" title="" id="thanks-loading-layer" style="display:none; float: left;" />
	';
	$IsThanked=false;
	$ThanksArray=array();
	$Tquery = sql_query('SELECT t.uid, u.username, g.namestyle FROM ts_thanks t LEFT JOIN users u ON (u.id=t.uid) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE t.tid = \''.$id.'\' ORDER BY u.username');
	if (mysqli_num_rows($Tquery) > 0)
	{
		while($thanks=mysqli_fetch_assoc($Tquery))
		{
			if ($thanks['uid'] == $CURUSER['id'])
			{
				$IsThanked=true;
			}
			$ThanksArray[] = '<a href="'.ts_seo($thanks['uid'], $thanks['username']).'">'.get_user_color($thanks['username'], $thanks['namestyle']).'</a>';
		}
	}

	$TButton = '';
	if (!$IsThanked AND $torrent['owner'] != $CURUSER['id'])
	{
		$TButton = '<div id="thanks_button"><input type="button" value="'.$lang->global['buttonthanks'].'" onclick="javascript:TSajaxquickthanks('.$id.');" /></div>';
	}
	elseif ($IsThanked)
	{
		$TButton = '<div id="thanks_button"><input type="button" value="'.$lang->global['buttonthanks2'].'" onclick="javascript:TSajaxquickthanks('.$id.', true);" /></div>';
	}

	if (count($ThanksArray) == 0)
	{
		$ThanksArray = 0;
		$AllThanks = $AllThanks.$TButton.'<div id="torrent_thanks" name="torrent_thanks"><b><i>'.$lang->details['nothanksyet'].'</i></b></div>';
	}
	else
	{
		$AllThanks = $AllThanks.$TButton.'<div id="torrent_thanks" name="torrent_thanks">'.implode(', ', $ThanksArray).'</div>';
		$ThanksArray = count($ThanksArray);
	}
}

$ShowTLINK = '';
if (!empty($torrent['t_link']))
{
	require_once(INC_PATH.'/functions_imdb_rating.php');
	if ($IMDBRating = TSSEGetIMDBRatingImage($torrent['t_link']))
	{
		$torrent['t_link'] = str_replace('<b>User Rating:</b>', '<b>User Rating:</b> '.$IMDBRating['image'], $torrent['t_link']);
	}
	else
	{
		function format_urls($s,$target='_blank')
		{
			return preg_replace("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^()<>\s]+)/i","\\1<a href=\"\\2\" target=\"$target\">\\2</a>", $s);
		}
		//$torrent['t_link'] = format_urls(str_replace('"', '&quot;', $torrent['t_link']), '_blank');
	}
	$ShowTLINK = '
	
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
		<tr>
			<td class="thead">
				'.($is_mod ? '
				<span style="float: right;"><div id="imdbupdatebutton" name="imdbupdatebutton"><a href="javascript:void(0);" onclick="TS_IMDB(\''.$id.'\'); return false;"><b><u><i>'.$lang->global['refresh'].'</i></u></b></a></div></span>' : '').$lang->details['t_link'].'
			</td>
		</tr>
		<tr>
			<td>
				<div id="imdbdetails" name="imdbdetails">'.$torrent['t_link'].'</div>
			</td>
		</tr>
		<tr>
			<td align="center" class="subheader">
				<a href="'.$BASEURL.'/ts_get_trailer.php?tid='.$id.'" id="ts_get_trailer"><u>Trailers &amp; Videos</u></a>
			</td>
		</tr>
	</table>
	<script type="text/javascript">
		jQuery("#ts_get_trailer").colorbox({iframe:true, innerWidth:695, innerHeight:405});
	</script>
	<br />';
}

$skipformatcomment = false;
if (preg_match('@'.$BASEURL.'\/viewnfo.php\?id='.$id.'@Uis', $torrent['descr'], $DescResults))
{
	$skipformatcomment = true;
	$torrent['descr'] = str_replace($BASEURL.'/viewnfo.php?id='.$id, '<img src="'.$BASEURL.'/viewnfo.php?id='.$id.'" border="0" alt="" title="" />', $torrent['descr']);
}

$MagnetLink = '<a href="'.prepareMagnetLink($torrent).'"><img src="'.$pic_base_url.'magnet.png" alt="Magnet" title="Magnet" /></a>';

$details = '
<table cellspacing="0" border="0" cellpadding="4" width="100%">
	<tr>
		<td colspan="3" class="thead">'.GetTorrentTags($torrent, false).' '.htmlspecialchars_uni($torrent['name']).'</td>
	</tr>
	<tr>
		<td rowspan="'.$rowspan.'" align="center" valign="top" width="175">'.($torrent['t_image'] != '' ? '<a href="'.htmlspecialchars_uni($torrent['t_image']).'" id="ts_show_preview"><img src="'.htmlspecialchars_uni($torrent['t_image']).'" border="0" width="175" height="175">' : '<img src="'.$Imagedir.'nopreview.gif" border="0">').'</a>'.$rating.'</td>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['download'].'</td>
		<td style="padding-left: 5px;" valign="top" width="430">
			<div style="float: right;">
				<!-- AddThis Button BEGIN -->
				<script type="text/javascript">var addthis_pub="templateshares";</script>
				<a href="https://www.addthis.com/bookmark.php?v=20" onmouseover="return addthis_open(this, \'\', \'[URL]\', \'[TITLE]\')" onmouseout="addthis_close()" onclick="return addthis_sendto()"><img src="https://s7.addthis.com/static/btn/lg-bookmark-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a><script type="text/javascript" src="https://s7.addthis.com/js/200/addthis_widget.js"></script>
				<!-- AddThis Button END -->
			</div>
			<a href="'.ts_seo($id,$torrent['name'],'d').'" alt="'.$lang->details['dltorrent'].'" title="'.$lang->details['dltorrent'].'"><img src="'.$Imagedir.'download.png" alt="'.$lang->details['dltorrent'].'" title="'.$lang->details['dltorrent'].'" border="0" /></a> '.($ssldownload == 'yes' ? '<a href="'.ts_seo($id,$torrent['name'],'ssl').'"><img src="'.$Imagedir.'ssldownload.png" alt="'.$lang->details['dltorrent'].' (SSL)" title="'.$lang->details['dltorrent'].' (SSL)" border="0" /></a>' : '').'
			'.($usergroups['candirectdownload'] == 'yes' && $torrent['directdownloadlink'] ? '&nbsp;&nbsp;<a href="'.$BASEURL.'/direct-download.php?id='.$torrent['id'].'"><img src="'.$pic_base_url.'download.png" border="0" alt="'.$lang->global['directdownloadlink'].'" title="'.$lang->global['directdownloadlink'].'" /></a> '.$lang->global['directdownloadlink'] : '').' 
			'.$MagnetLink.'
		</td>
	</tr>
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['added'].'</td>
		<td valign="top" style="padding-left: 5px;">'.my_datee($dateformat, $torrent['added']).' '.my_datee($timeformat, $torrent['added']).'  - '.sprintf($lang->details['laction'], my_datee($dateformat, $torrent['mtime']).' '.my_datee($timeformat, $torrent['mtime'])).'</td>
	</tr>
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['type'].'</td>
		<td valign="top" style="padding-left: 5px;">'.$torrent['categoryname'].'</td>
	</tr>
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['size'].'</td>
		<td valign="top" style="padding-left: 5px;">'.mksize($torrent['size']).' '.sprintf($lang->details['numfiles2'], ts_nf($torrent['numfiles'])).'</td>
	</tr>
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['hits'].'</td>
		<td valign="top" style="padding-left: 5px;">'.ts_nf($torrent['hits']).'</td>
	</tr>
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['snatched'].'</td>
		<td valign="top" style="padding-left: 5px;"><a href="'.$BASEURL.'/'.($xbt_active == 'yes' && $is_mod ? 'mysnatchlist.php?tid' : ($usergroups['cansnatch'] == 'yes' &&  $xbt_active != 'yes' ? 'viewsnatches.php?id' : 'details.php?id')).'='.$id.'">'.ts_nf($torrent['times_completed']).'</a> '.$lang->details['snatched2'].' '.($is_mod ? '[<a href="'.$BASEURL.'/s_message.php?id='.$id.'">'.$lang->details['s_message'].'</a>]' : '').'</td>
	</tr>
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['uppedby'].'</td>
		<td valign="top" style="padding-left: 5px;">'.$username.'</td>
	</tr>
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['comments'].'</td>
		<td valign="top" style="padding-left: 5px;">'.ts_nf($torrent['comments']).' '.$lang->details['comments'].'</td>
	</tr>
	<tr>
		<td style="padding-left: 5px;" class="subheader" valign="top" width="147" align="right">'.$lang->details['peersb'].'</td>
		<td valign="top" style="padding-left: 5px;">'.sprintf($lang->details['peers2'], ts_nf($torrent['seeders']), ts_nf($torrent['leechers']), ts_nf($torrent['seeders']+$torrent['leechers'])).'</td>
	</tr>
	'.(isset($reseed) ? $reseed : '').(isset($isScene) ? $isScene : '').'
	'.($ShowEDetails ? '
	<tr>
		<td align="center" valign="top" width="175">
			<table cellpadding="2" cellspacing="0" width="100%" align="center">
				<tr>
					<td colspan="2" class="subheader">'.$lang->upload['video'].'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['codec'].'</td><td>'.(isset($video_info[0]) && !empty($video_info[0]) ? htmlspecialchars_uni($video_info[0]) : $lang->details['na']).'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['bitrate'].'</td><td>'.(isset($video_info[1]) && !empty($video_info[1]) ? htmlspecialchars_uni($video_info[1]).' kbps' : $lang->details['na']).'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['resulation'].'</td><td>'.(isset($video_info[2]) && !empty($video_info[2]) ? htmlspecialchars_uni($video_info[2]) : $lang->details['na']).'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['length'].'</td><td>'.(isset($video_info[3]) && !empty($video_info[3]) ? htmlspecialchars_uni($video_info[3]).' '.$lang->global['minutes'] : $lang->details['na']).'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['quality'].'</td><td>'.(isset($video_info[4]) && !empty($video_info[4]) ? htmlspecialchars_uni($video_info[4]).'/10' : $lang->details['na']).'</td>
				</tr>
				<tr>
					<td colspan="2" class="subheader">'.$lang->upload['audio'].'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['codec'].'</td><td>'.(isset($audio_info[0]) && !empty($audio_info[0]) ? htmlspecialchars_uni($audio_info[0]) : $lang->details['na']).'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['bitrate'].'</td><td>'.(isset($audio_info[1]) && !empty($audio_info[1]) ? htmlspecialchars_uni($audio_info[1]).' kbps' : $lang->details['na']).'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['frequency'].'</td><td>'.(isset($audio_info[2]) && !empty($audio_info[2]) ? htmlspecialchars_uni($audio_info[2]).' HZ' : $lang->details['na']).'</td>
				</tr>
				<tr>
					<td valign="top" align="right" width="40%">'.$lang->upload['language'].'</td><td>'.(isset($audio_info[3]) && !empty($audio_info[3]) ? htmlspecialchars_uni($audio_info[3]) : $lang->details['na']).'</td>
				</tr>
			</table>
		</td>
		' : '').'
		'.($thankssystem == 'yes' ? '
		'.($ShowEDetails ? '' : '<tr>').'
		<td colspan="'.($ShowEDetails ? 2 : 3).'" valign="top">
		<table width="100%" border="0" cellpadding="5" cellspacing="0">
			<tr>
				<td class="thead">'.$lang->details['thanksby'].' ('.ts_nf($ThanksArray).')</td>
			</tr>
			<tr>
				<td>
					<div style="overflow: auto; height: '.($ShowEDetails ? 200 : ($ThanksArray <= 20 ? 40 : 100)).'px;">'.$AllThanks.'</div>
				</td>
			</tr>
		</table>
		</td>' : '').'
		'.($thankssystem == 'yes' || $ShowEDetails ? '</tr>' : '').'
	</table>
	<br />
	'.$Subtitles.'
	'.$ShowTLINK.'
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
		<tr>
			<td class="thead">'.sprintf($lang->details['detailsfor'], $torrent['name']).'</td>
		</tr>
		<tr>
			<td>
				'.($skipformatcomment ? $torrent['descr'] : format_comment($torrent['descr'])).'
			</td>
		</tr>
	</table>
	'.$SimilarTorrents.'
';

echo '
<script type="text/javascript" src="'.$BASEURL.'/scripts/yui/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="'.$BASEURL.'/scripts/yui/element/element-min.js"></script>
<script type="text/javascript" src="'.$BASEURL.'/scripts/yui/tabview/tabview-min.js"></script>'.($is_mod ? '
<script type="text/javascript">
	l_updated = "'.$lang->global['imgupdated'].'";
	l_refresh = "'.$lang->global['refresh'].'";
</script>
<script type="text/javascript" src="'.$BASEURL.'/scripts/quick_imdb.js"></script>' : '');

$show_manage = '';
if ($CURUSER['id'] === $torrent['owner'] OR $is_mod)
{
	$show_manage .= '<a href="'.$BASEURL.'/upload.php?id='.$id.'"  onmouseout="window.status=\'\'; return true;" onMouseOver="window.status=\''.$lang->details['editorrent'].'\'; return true;">'.$lang->details['editorrent'].'</a> - ';
}

if ($is_mod)
{
	$show_manage .= '<a href="'.$BASEURL.'/'.$staffcp_path.'/index.php?do=hit_and_run&torrentid='.$id.'" onmouseout="window.status=\'\'; return true;" onMouseOver="window.status=\'\'; return true;">Hit & Run</a> - <a href="'.$BASEURL.'/'.$staffcp_path.'/index.php?do=delete_torrent&amp;tid='.$id.'" onmouseout="window.status=\'\'; return true;" onMouseOver="window.status=\'\'; return true;">Delete Torrent</a> -
	<a href="'.$BASEURL.'/comment.php?tid='.$id.'&action='.($torrent['allowcomments'] != 'yes' ? 'open' : 'close').'"  onmouseout="window.status=\'\'; return true;" onMouseOver="window.status=\''.($torrent['allowcomments'] == 'no' ? $lang->details['open'] : $lang->details['close']).'\'; return true;">'.($torrent['allowcomments'] != 'yes' ? $lang->details['open'] : $lang->details['close']).'</a> -
	<a href="'.$BASEURL.'/'.$staffcp_path.'/index.php?do=torrent_info&amp;tid='.$id.'" onmouseout="window.status=\'\'; return true;" onMouseOver="window.status=\'\'; return true;">Torrent Info</a>'.($torrent['moderate'] == '1' && !$APPROVED ? ' - <a href="'.$_SERVER['SCRIPT_NAME'].'?id='.$id.'&amp;approve=true">Approve This Torrent</a>' : '');
}

$show_nfo = '';
if (!empty($torrent['nfo']))
{
	$show_nfo .= '<img src="'.$BASEURL.'/viewnfo.php?id='.$id.'" border="0" alt="'.$torrent['name'].'" title="'.$torrent['name'].'" />';
}

echo '
<div class="yui-skin-sam">
	<div id="details" class="yui-navset">
		<ul class="yui-nav">
			<li'.($tab == 'details' ? ' class="selected"' : '').'><a href="#details"><em>'.$lang->details['torrentinfo'].'</em></a></li>
			<li'.($tab == 'comments' ? ' class="selected"' : '').'><a href="#comments"><em>'.$lang->details['comments'].'</em></a></li>
			<li'.($tab == 'filelist' ? ' class="selected"' : '').'><a href="#filelist"><em>'.$lang->details['numfiles3'].'</em></a></li>
			<li'.($tab == 'peers' ? ' class="selected"' : '').'><a href="#peers"><em>'.$lang->details['peersb'].'</em></a></li>
			<li'.($tab == 'report' ? ' class="selected"' : '').'><a href="'.$BASEURL.'/report.php?type=2&reporting='.$id.'" id="cIFrame"><em>'.$lang->details['report'].'</em></a></li>
			<li'.($tab == 'bookmark' ? ' class="selected"' : '').'><a href="#bookmark"><em>'.$lang->details['bookmark'].'</em></a></li>
			'.($show_nfo != '' ? '<li'.($tab == 'nfo' ? ' class="selected"' : '').'><a href="#nfo"><em>NFO</em></a></li>' : '').'
			'.($show_manage != '' ? '<li'.($tab == 'manage' ? ' class="selected"' : '').'><a href="#manage"><em>Manage Torrent</em></a></li>' : '').'
			'.($is_mod || ($usergroups['candeletetorrent'] == 'yes' && $CURUSER['id'] == $torrent['owner']) ? '<li'.($tab == 'delete' ? ' class="selected"' : '').'><a href="#delete"><em>Delete Torrent</em></a></li>' : '').'
		</ul>
		<div class="yui-content">
			<div id="details">'.$details.'</div>
			<div id="comments">
				<div id="show_allcomments" name="show_allcomments" style="display: block;">
					'.$pagertop.'
					'.$showcommenttable.'
					'.$pagerbottom.'
				</div>
				'.$showcommenttableQuickPost.'
			</div>
			<div id="filelist">'.$s.'</div>
			<div id="peers">'.$peerstable.'</div>
			<div id="report">'.$lang->details['reporting'].'</div>
			<div id="bookmark">'.$bookmark.'</div>
			'.($show_nfo != '' ? '<div id="nfo">'.$show_nfo.'</div>' : '').'
			'.($show_manage != '' ? '<div id="manage">'.$show_manage.'</div>' : '').'
			'.($is_mod || ($usergroups['candeletetorrent'] == 'yes' && $CURUSER['id'] == $torrent['owner']) ? '
			<div id="delete">
				<form method="post" action="delete.php">
				<input type="hidden" name="id" value="'.$id.'" />
				<table border="0" cellspacing="0" cellpadding="5" width="100%">
					<tr>
						<td colspan="2" class="thead">
							<b>Delete torrent.</b> Reason:
						</td>
					</tr>
					<tr>
						<td>
							<input name="reasontype" type="radio" value="1" /> Dead
						</td>
						<td>
							0 seeders, 0 leechers = 0 peers total
						</td>
					</tr>
					<tr>
						<td>
							<input name="reasontype" type="radio" value="2" /> Duplicate
						</td>
						<td>
							<input type="text" size="40" name="reason[]"  id="specialboxn" />
						</td>
					</tr>
					<tr>
						<td>
							<input name="reasontype" type="radio" value="3" /> Nuked
						</td>
						<td>
							<input type="text" size="40" name="reason[]"  id="specialboxn" />
						</td>
					</tr>
					<tr>
						<td>
							<input name="reasontype" type="radio" value="4" /> Rules Broken:
						</td>
						<td>
							<input type="text" size="40" name="reason[]"  id="specialboxn" /> <strong><font color=red>(req)</font></strong>
						</td>
					</tr>
					<tr>
						<td>
							<input name="reasontype" type="radio" value="5" checked="checked" /> Other
						</td>
						<td>
							<input type="text" size="40" name="reason[]" id="specialboxn" /> <strong><font color=red>(req)</font></strong>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<input type="submit" value="'.$lang->global['buttondelete'].'" />
						</td>
					</tr>
				</table>
				</form>			
			</div>' : '').'
		</div>
	</div>
	<script type="text/javascript">
	(function() {
		var tabView = new YAHOO.widget.TabView("details");
		jQuery("#cIFrame").colorbox({iframe:true, innerWidth:500, innerHeight:300});
	})();
	</script>
</div>';
stdfoot();
?>