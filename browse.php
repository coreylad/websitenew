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
define('THIS_SCRIPT', 'browse.php');
define('B_VERSION', '6.6.4 by xam');
define('SKIP_MOD_QUERIES', true);
define('SKIP_CACHE_MESSAGE', true);
require('./global.php');

if (!isset($CURUSER))
{
	print_no_permission();
}

/*
	----------------------------------------------------------------------
	TS SE Security Team Message:
	----------------------------------------------------------------------
	Please DO NOT modify this file unless you know what are you doing!
	----------------------------------------------------------------------
*/

if (!headers_sent())
{
	setcookie('acqu', base64_encode($CURUSER['usergroup']), TIMENOW+24*60*60);
}

$TSSEConfig->TSLoadConfig('ANNOUNCE');

$defaulttemplate = ts_template();
$lang->load('browse');
$category = intval(TS_Global('category'));
$keywords = TS_Global('keywords');
$search_type = TS_Global('search_type');
$special_search = TS_Global('special_search');
$sort = TS_Global('sort');
$order = TS_Global('order');
$daysprune = TS_Global('daysprune');
$include_dead_torrents = TS_Global('include_dead_torrents');
$Links = array();
require_once(INC_PATH.'/functions_mkprettytime.php');

if ($progressbar == 'yes')
{
	include_once(INC_PATH.'/functions_external.php');
}

if (!isset($TSSECache->Cache['webseeders']['lastupdate']) OR $TSSECache->Cache['webseeders']['lastupdate'] < TIMENOW - ($cachetime * 60))
{
	$WebSeeders = array();
	$WSQuery = sql_query('SELECT id FROM users WHERE options LIKE \'%R1%\'') or sqlerr(__FILE__,__LINE__);
	if (mysqli_num_rows($WSQuery))
	{
		while($WSU = mysqli_fetch_row($WSQuery))
		{
			$WebSeeders[] = $WSU['0'];
		}
		$WebSeeders = implode(',', $WebSeeders);
	}
	else
	{
		$WebSeeders = false;
	}
	$TSSECache->UpdateCache('webseeders', $WebSeeders);
}

$WebSeeders = $TSSECache->Cache['webseeders']['content'];
$TWebSeeders = array();

if($WebSeeders) 
{
	if ($xbt_active == 'yes')
		$PSQuery = 'SELECT fid as torrent FROM xbt_files_users WHERE $active = 1 AND uid IN ('.$WebSeeders.')';
	else
		$PSQuery = 'SELECT torrent FROM peers WHERE userid IN ('.$WebSeeders.')';

	$PSQueryhash = md5($PSQuery);
	$PSQuery = sql_query($PSQuery) or sqlerr(__FILE__,__LINE__);

	if ($UseMemcached)
	{
		if ($TWebSeeders = $TSMemcache->check($PSQueryhash))
		{
			//Cache exists, lets use it.
		}
		else
		{
			if (mysqli_num_rows($PSQuery))
			{
				while($PSU = mysqli_fetch_assoc($PSQuery))
				{
					if (isset($TWebSeeders[$PSU['torrent']]))
					{
						$TWebSeeders[$PSU['torrent']]++;
					}
					else
					{
						$TWebSeeders[$PSU['torrent']] = 1;
					}
				}
			}
			$TSMemcache->add($PSQueryhash, $TWebSeeders); //default 5 minutes.
		}
	}
	else
	{
		if (mysqli_num_rows($PSQuery))
		{
			while($PSU = mysqli_fetch_assoc($PSQuery))
			{
				if (isset($TWebSeeders[$PSU['torrent']]))
				{
					$TWebSeeders[$PSU['torrent']]++;
				}
				else
				{
					$TWebSeeders[$PSU['torrent']] = 1;
				}
			}
		}
	}
	
	unset($WebSeeders);
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
	elseif ($bdayreward == 'yes' AND $bdayrewardtype AND isset($CURUSER['birthday']) AND !empty($CURUSER['birthday']))
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

require(TSDIR.'/'.$cache.'/categories.php');
$subcategories = array();
$searcincategories = array();
if (count($_categoriesS) > 0)
{
	foreach ($_categoriesS as $categorySub) {
		if ($categorySub['canview'] != '[ALL]' && !in_array($CURUSER['usergroup'], explode(',', $categorySub['canview']))) {
			continue;
		}
		$categorySub['name'] = htmlspecialchars_uni($categorySub['name']);
		$searchInCategories[] = $categorySub['id'];
		$categorySubDescription = htmlspecialchars_uni($categorySub['cat_desc']);
		$seoLinkCategory = ts_seo($categorySub['id'], $categorySub['name'], 'c');
		$subcategories[$categorySub['pid']][] = '
		<span $id = "category' . $categorySub['id'] . '"' . (isset($category) && $category == $categorySub['id'] || (!$category && strpos($CURUSER['notifs'], '[cat' . $categorySub['id'] . ']') !== false && $usergroups['canemailnotify'] == 'yes') ? ' class="highlight"' : '') . '>
			<a $href = "' . $seoLinkCategory . '" $title = "' . $categorySubDescription . '">' . $categorySub['name'] . '</a>
		</span>';
	}
	unset($_categoriesS);
}

$count = 0;
$categories = '
<table $align = "center" $border = "0" $cellpadding = "3" $cellspacing = "0" $width = "100%">
<tbody>
	<tr>
		<td class="thead">
			'.ts_collapse('categories').'
			<div>
				<strong>
					'.$lang->browse['tcategory'].'
				</strong>
			</div>
		</td>
	</tr>
	'.ts_collapse('categories', 2).'
	<tr>
		<td $align = "center">
			<table $border = "0" $cellspacing = "0" $cellpadding = "0" $align = "left">
				<tr class="none">
';


if (($rows = count($_categoriesC)) > 0) {
	foreach ($_categoriesC as $categoryMain) {
		if ($categoryMain['canview'] != '[ALL]' && !in_array($CURUSER['usergroup'], explode(',', $categoryMain['canview']))) {
			continue;
		}
		$searchInCategories[] = $categoryMain['id'];
		if ($count && $count % $tracker_cats_per_row == 0) {
			$categories .= '</tr><tr class="none">';
		}
		$categoryMainName = htmlspecialchars_uni($categoryMain['name']);
		$categoryMainDescription = htmlspecialchars_uni($categoryMain['cat_desc']);
		$seoLinkCategory = ts_seo($categoryMain['id'], $categoryMainName, 'c');
		$categories .= '
					<td class="none">
						<table $border = "0" $cellspacing = "0" $cellpadding = "0">
							<tr>
								<td $width = "' . $table_cat_width . '" class="none" $height = "' . $table_cat_height . '" $valign = "top" $align = "center" $style = "padding: 2px;">
									<a $href = "' . $seoLinkCategory . '"><img $src = "' . $pic_base_url . $table_cat . '/' . $categoryMain['image'] . '" $border = "0" $alt = "' . $categoryMainName . '" $title = "' . $categoryMainName . '" $width = "' . $table_cat_width . '" $height = "' . $table_cat_height . '" /></a>
								</td>
								<td $width = "' . $tracker_cats_width . '" class="none" $valign = "top" $align = "left" $style = "padding-left: 2px; padding-top: 3px; padding-right: 3px;">
									<span $id = "category' . $categoryMain['id'] . '"' . (isset($category) && $category == $categoryMain['id'] || (!$category && strpos($CURUSER['notifs'], '[cat' . $categoryMain['id'] . ']') !== false && $usergroups['canemailnotify'] == 'yes') ? ' class="highlight"' : '') . '>
										<font class="cat_link" $style = "font-size: 14px;"><a $href = "' . $seoLinkCategory . '" $title = "' . $categoryMainDescription . '"><b>' . $categoryMainName . '</b></a></font>
									</span>
									<br />
									' . (isset($subcategories[$categoryMain['id']]) ? implode(', ', $subcategories[$categoryMain['id']]) : $categoryMain['cat_desc']) . '
								</td>
							</tr>
						</table>
					</td>
					';
		$count++;
	}
	unset($_categoriesC);
}

$categories .= '
				</tr>
			</table>
		</td>
	</tr>
</tbody>
</table>';

require_once(INC_PATH.'/functions_category.php');
$catdropdown = ts_category_list('category',(isset($category) ? $category : ''),'<option $value = "0" $style = "color: gray;">'.$lang->browse['alltypes'].'</option>', 'categories');

$SearchTorrent = '
		<table $align = "center" $border = "0" $cellpadding = "3" $cellspacing = "0" $width = "100%">
		<tbody>
			<tr>
				<td class="thead">
					'.ts_collapse('search').'
					'.$lang->browse['tsearch'].'
				</td>
			</tr>
			'.ts_collapse('search', 2).'
			<tr>
				<td>
					<form $method = "post" $action = "'.$_SERVER['SCRIPT_NAME'].'" $name = "searchtorrent" $id = "searchtorrent">
					<input $type = "hidden" $name = "do" $value = "search" />
					<span class="ac_holder">
						<input $type = "text" $name = "keywords" $id = "tsstac" $value = "'.($keywords ? htmlspecialchars_uni($keywords) : '').'"'.($use_ajax_search == 'yes' ? ' $onFocus = "javascript:
						var $options = {
							script: function (input) { return (\'scripts/autocomplete/query.php?$input = \'+input); },
							callback: function (obj) { window.$location = \'details.php?$id = \'+obj.id; }
							};
							var $xml = new AutoComplete(\'tsstac\',options);return true;"' : '').' $autocomplete = "off" $style = "width: 250px;" />
					</span>
					<select $name = "search_type" $id = "search_type">
						<option $value = "t_name"'.($search_type == 't_name' ? ' $selected = "selected"' : '').'>'.$lang->browse['t_name'].'</option>
						<option $value = "t_description"'.($search_type == 't_description' ? ' $selected = "selected"' : '').'>'.$lang->browse['t_description'].'</option>
						<option $value = "t_both"'.($search_type == 't_both' || $search_type == '' ? ' $selected = "selected"' : '').'>'.$lang->browse['t_both'].'</option>
						<option $value = "t_uploader"'.($search_type == 't_uploader' ? ' $selected = "selected"' : '').'>'.$lang->browse['t_uploader'].'</option>
						<option $value = "t_genre"'.($search_type == 't_genre' ? ' $selected = "selected"' : '').'>'.$lang->browse['t_genre'].'</option>
					</select>
					'.$catdropdown.'
					<select $name = "include_dead_torrents">
						<option $value = "yes"'.($include_dead_torrents == 'yes' ? ' $selected = "selected"' : '').'>'.$lang->browse['incdead1'].'</option>
						<option $value = "no"'.($include_dead_torrents == 'no' ? ' $selected = "selected"' : '').'>'.$lang->browse['incdead2'].'</option>
					</select>
					<input $type = "submit" $value = "'.$lang->global['buttonsearch'].'" />
					</form>
				</td>
			</tr>
		</tbody>
		</table>
';

$WHERE = " WHERE".($include_dead_torrents == 'yes' ? '' : " t.$visible = 'yes' AND")." t.$banned = 'no'";

$Links[] = 'include_dead_torrents='.($include_dead_torrents == 'yes' ? 'yes' : 'no');

$WHERE .= " AND (INSTR(CONCAT(',',c.canview,','),',[ALL],') > 0 OR INSTR(CONCAT(',',c.canview,','),',".$CURUSER['usergroup'].",') > 0)";

if (TS_Match($CURUSER['options'], 'E0'))
{
	$WHERE .= " AND t.$offensive = 'no'";
}

$innerjoin = '';
if ($special_search == 'myreseeds')
{
	$Links[] = 'special_search=myreseeds';
	$WHERE .= ' AND t.$seeders = 0 AND t.leechers > 0 AND t.$owner = \''.$CURUSER['id'].'\'';
}
elseif ($special_search == 'mybookmarks')
{
	$Links[] = 'special_search=mybookmarks';
	$innerjoin = ' INNER JOIN bookmarks b ON (b.$torrentid = t.id)';
	$WHERE .= ' AND b.$userid = \''.$CURUSER['id'].'\'';
}
elseif ($special_search == 'mytorrents')
{
	$Links[] = 'special_search=mytorrents';
	$WHERE .= ' AND t.$owner = \''.$CURUSER['id'].'\'';
}
elseif ($special_search == 'weaktorrents')
{
	$Links[] = 'special_search=weaktorrents';
	$WHERE .= " AND t.$moderate = '0' AND t.$visible = 'no' OR (t.leechers > 0 AND t.$seeders = 0 AND t.$moderate = '0') OR (t.$leechers = 0 AND t.$seeders = 0 AND t.$moderate = '0')";
}

$extraquery = array();
if ($keywords AND $search_type)
{
	$OrjKeywords = $keywords;
	$Links[] = 'keywords='.htmlspecialchars_uni($keywords);
	$Links[] = 'search_type='.htmlspecialchars_uni($search_type);
	if ($fulltextsearch == 'yes')
	{
		require(INC_PATH.'/function_search_clean.php');
		$keywords = clean_keywords_ft($keywords);
	}

	if ($keywords)
	{
		if ($fulltextsearch == 'yes')
		{
			$query = sql_query("SHOW VARIABLES LIKE 'ft_min_word_len';");
			$array = mysqli_fetch_assoc($query);
			$min_length = $array['Value'];

			if(is_numeric($min_length))
			{
				$minsearchword = $min_length;
			}
			else
			{
				$minsearchword = 4;
			}

			$keywords_exp = explode("\"", $keywords);
			$inquote = false;
			foreach($keywords_exp as $phrase)
			{
				if(!$inquote)
				{
					$split_words = preg_split("#\s{1,}#", $phrase, -1);
					foreach($split_words as $word)
					{
						$word = str_replace(array("+", "-", "*"), "", $word);
						if(!$word)
						{
							continue;
						}
						if(strlen($word) < $minsearchword)
						{
							$lang->load('tsf_forums');
							$lang->$error_minsearchlength = sprintf($lang->tsf_forums['searcherror3'], $minsearchword);
							stderr($lang->global['error'], $lang->error_minsearchlength,false);
							exit;
						}
					}
				}
				else
				{
					$phrase = str_replace(array("+", "-", "*"), "", $phrase);
					if(strlen($phrase) < $minsearchword)
					{
						$lang->load('tsf_forums');
						$lang->$error_minsearchlength = sprintf($lang->tsf_forums['searcherror3'], $minsearchword);
						stderr($lang->global['error'], $lang->error_minsearchlength,false);
						exit;
					}
				}
				$inquote = !$inquote;
			}
		}
		else
		{
			if (strlen($keywords) < 3)
			{
				stderr($lang->global['error'], $lang->global['nothingfound']);
				exit;
			}
		}

		switch ($search_type)
		{
			case 't_name':
				if ($fulltextsearch == 'yes')
					$extraquery[] = "(MATCH(t.name) AGAINST('".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], $keywords)."' IN BOOLEAN MODE))";
				else
					$extraquery[] = "(t.name LIKE '".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], "%".$keywords."%")."')";
				break;
			case 't_description':
				if ($fulltextsearch == 'yes')
					$extraquery[] = "(MATCH(t.descr) AGAINST('".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], $keywords)."' IN BOOLEAN MODE))";
				else
					$extraquery[] = "(t.descr LIKE '".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], "%".$keywords."%")."')";
				break;
			case 't_both':
				if ($fulltextsearch == 'yes')
					$extraquery[] = "(MATCH(t.name) AGAINST('".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], $keywords)."' IN BOOLEAN MODE) OR MATCH(t.descr) AGAINST('".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], $keywords)."' IN BOOLEAN MODE))";
				else
					$extraquery[] = "(t.name LIKE '".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], "%".$keywords."%")."' OR t.descr LIKE '".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], "%".$keywords."%")."')";
				break;
			case 't_uploader':
				$query = sql_query("SELECT id FROM users WHERE upper(username) = ".sqlesc(strtoupper($OrjKeywords))." LIMIT 1");
				if (mysqli_num_rows($query) > 0)
				{
					$user = mysqli_fetch_assoc($query);
					$extraquery[] = "t.$owner = ".sqlesc($user['id']).(!$is_mod ? " AND t.anonymous != 'yes'" : "");
				}
				else
					$extraquery[] = "t.$owner = ".sqlesc($OrjKeywords);
				break;
			case 't_genre':
				if ($fulltextsearch == 'yes')
					$extraquery[] = "(MATCH(t.t_link) AGAINST('".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], $keywords)."' IN BOOLEAN MODE))";
				else
					$extraquery[] = "(t.t_link LIKE '".mysqli_real_escape_string($GLOBALS['DatabaseConnect'], "%".$keywords."%")."')";
			break;
		}
		$keywords = $OrjKeywords;
	}
}

if ($category)
{
	$query = sql_query("SELECT id FROM categories WHERE $type = 's' AND $pid = '$category'");
	if (mysqli_num_rows($query) > 0)
	{
		$squerycats=array();
		while ($squery=mysqli_fetch_assoc($query))
		{
			$squerycats[] = $squery['id'];
		}
		$extraquery[] = 't.category  IN ('.$category.', '.implode(', ', $squerycats).')';
	}
	else
	{
		$extraquery[] = "t.$category = ".$category;
	}
	$Links[] = 'category='.$category;
}
elseif ($usergroups['canemailnotify'] == 'yes' AND preg_match("#\[cat.+#i", $CURUSER['notifs']))
{
	$defaultcategories = array();
	foreach ($searcincategories as $catid)
	{
		if (strpos($CURUSER['notifs'], '[cat'.$catid.']') !== FALSE)
		{
			$defaultcategories[] = $catid;
		}
	}
	if (count($defaultcategories) > 0)
	{
		$WHERE .= ' AND t.category IN ('.implode(',', $defaultcategories).')';
		unset($defaultcategories);
	}
}

if ($special_search)
{
	$Links[] = 'special_search='.htmlspecialchars_uni($special_search);
}

if (count($extraquery) > 0)
{
	$WHERE .= ' AND '.implode(' AND ', $extraquery);
}

$orderby = 't.sticky, t.added DESC';

if ($sort OR $daysprune)
{
	$sort_array = array(
	'category', 'name', 'added', 'comments', 'size', 'times_completed', 'seeders', 'leechers', 'owner', 'hits', 'sticky', 'free', 'silver', 'isnuked', 'isrequest', 'doubleupload', 'ts_external', 'visible', 'isScene');
	$sort = (in_array($sort, $sort_array) ? $sort : false);
	$order = (strtolower($order) == 'asc' ? 'ASC' : 'DESC');
	$daysprune = ($daysprune == '-1' ? false : intval($daysprune));
	if ($sort)
	{
		if ($xbt_active == 'yes')
		{
			switch($sort)
			{
				case 'free':
					$orderby = 't.download_multiplier ASC';
				break;
				case 'silver':
					$orderby = 't.download_multiplier DESC';
				break;
				case 'doubleupload':
					$orderby = 't.upload_multiplier DESC';
				break;
				default:
					$orderby = 't.'.$sort.' '.$order;
				break;
			}
		}
		else
		{
			$orderby = 't.'.$sort.' '.$order;
		}
		$Links[] = 'sort='.htmlspecialchars_uni($sort);
		$Links[] = 'order='.htmlspecialchars_uni($order);
	}
	if ($daysprune)
	{
		$WHERE .= " AND UNIX_TIMESTAMP(t.added) >= ".(TIMENOW - ($daysprune * 86400));
		$Links[] = 'daysprune='.htmlspecialchars_uni($daysprune);
	}
}

$wait = intval($usergroups['waitlimit']) ? $usergroups['waitlimit'] : '';

$torrentsperpage = ($CURUSER['torrentsperpage'] <> 0 ? intval($CURUSER['torrentsperpage']) : $ts_perpage);

$countquery = 'SELECT t.id, c.name, u.usergroup, g.gid FROM torrents t'.$innerjoin.' LEFT JOIN users u ON (t.$owner = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) LEFT JOIN categories c ON (t.$category = c.id)'.$WHERE.' ORDER BY '.$orderby;
$countqueryhash = md5($countquery);

if ($UseMemcached)
{
	if ($count = $TSMemcache->check('torrentcount_'.$countqueryhash))
	{
		//Cache exists, lets use it.
	}
	else
	{
		$count = mysqli_num_rows(sql_query($countquery));
		$TSMemcache->add('torrentcount_'.$countqueryhash, $count); //default 5 minutes.
	}
}
else
{
	$count = mysqli_num_rows(sql_query($countquery));
}

list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, $_SERVER['SCRIPT_NAME'].'?'.(is_array($Links) && count($Links) > 0 ? implode('&amp;', $Links) : '').'&amp;');

$ListTorrents = '
<script $type = "text/javascript" $src = "'.$BASEURL.'/scripts/sortable/sortable.js?$v = '.O_SCRIPT_VERSION.'"></script>
<script $type = "text/javascript" $src = "'.$BASEURL.'/scripts/ts_update.js?$v = '.O_SCRIPT_VERSION.'"></script>
'.($is_mod ? '
<script $type = "text/javascript">
	function check_it(wHAT)
	{
		if (wHAT.$value = = "move")
		{
			document.getElementById("movetorrent").style.$display = "block";
		}
		else
		{
			document.getElementById("movetorrent").style.$display = "none";
		}
	}
</script>
<form $method = "post" $action = "'.$BASEURL.'/'.$staffcp_path.'/index.php?do=manage_torrents&$act = update&$from_browse = true&$page = '.intval(TS_Global('page')).'" $name = "manage_torrents">' : '').'
<div $id = "listtorrents" $style = "display: inline;">
'.$pagertop.'
<table $align = "center" $border = "0" $cellpadding = "2" $cellspacing = "0" $width = "100%" class="sortable" $id = "sortabletable">
	<tbody>
		<tr>
			<td $align = "center" class="unsortable">
				'.$lang->browse['type'].'
			</td>
			<td class="thead" $align = "left">
				'.$lang->browse['t_name'].'
			</td>
			<td $align = "center" class="unsortable">
				&nbsp;
			</td>
			<td class="thead" $align = "center">
				<img $src = "'.$pic_base_url.'comments.gif" $border = "0" $alt = "'.$lang->browse['sortby3'].'" $title = "'.$lang->browse['sortby3'].'" />
			</td>
			<td $align = "center" class="unsortable">
				<img $src = "'.$pic_base_url.'size.gif" $border = "0" $alt = "'.$lang->browse['sortby6'].'" $title = "'.$lang->browse['sortby6'].'" />
			</td>
			<td class="thead" $align = "center">
				<img $src = "'.$pic_base_url.'snachted.gif" $border = "0" $alt = "'.$lang->browse['sortby7'].'" $title = "'.$lang->browse['sortby7'].'" />
			</td>
			<td class="thead" $align = "center">
				<img $src = "'.$pic_base_url.'seeders.gif" $border = "0" $alt = "'.$lang->browse['sortby4'].'" $title = "'.$lang->browse['sortby4'].'" />
			</td>
			<td class="thead" $align = "center">
				<img $src = "'.$pic_base_url.'leechers.gif" $border = "0" $alt = "'.$lang->browse['sortby5'].'" $title = "'.$lang->browse['sortby5'].'" />
			</td>
			<td class="thead" $align = "center">
				<img $src = "'.$pic_base_url.'uploader.gif" $border = "0" $alt = "'.$lang->browse['sortby8'].'" $title = "'.$lang->browse['sortby8'].'" />
			</td>
			'.($is_mod ? '<td class="unsortable" $align = "center"><input $checkall = "group1" $onclick = "javascript: return select_deselectAll (\'manage_torrents\', this, \'group1\');" $type = "checkbox" /></td>' : '').'
		</tr>
';

$groupby = $torrentspeed == 'yes' ? ' GROUP by t.id ' : '';

$query = ('SELECT '.($torrentspeed == 'yes' ? '(t.size * t.times_completed + SUM(p.downloaded)) / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(t.added)) AS totalspeed,' : '').'t.*, c.name as catname, c.image as catimage, u.username, g.namestyle FROM torrents t'.$innerjoin.' LEFT JOIN categories c ON (t.$category = c.id) LEFT JOIN users u ON (t.$owner = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid)'.($torrentspeed == 'yes' ? ' LEFT JOIN '.($xbt_active == 'yes' ? 'xbt_files_users p ON (t.$id = p.fid)' : 'peers p ON (t.$id = p.torrent)') : '').$WHERE.$groupby.' ORDER BY '.$orderby.' '.$limit);

$queryhash = md5($query);
$TotalTorrents = array();

if ($UseMemcached)
{
	if ($TotalTorrents = $TSMemcache->check($queryhash))
	{
		//Cache exists, lets use it.
	}
	else
	{
		$Query = sql_query($query) or sqlerr(__FILE__,__LINE__);
		if (mysqli_num_rows($Query))
		{
			while ($torrentRow = mysqli_fetch_assoc($Query)) {
				$totalTorrents[] = $torrentRow;
			}
		}
		$TSMemcache->add($queryhash, $TotalTorrents); //default 5 minutes.
	}
}
else
{
	$Query = sql_query($query) or sqlerr(__FILE__,__LINE);
	if (mysqli_num_rows($Query))
	{
		while ($torrentRow = mysqli_fetch_assoc($Query)) {
			$totalTorrents[] = $torrentRow;
		}
	}
}

if ($TotalTorrents AND count($TotalTorrents))
{
	require_once(INC_PATH.'/functions_imdb_rating.php');
	require_once(INC_PATH.'/functions_get_torrent_flags.php');
	$worked = 0;
	foreach($TotalTorrents as $Torrent)
	{
		$ShowImdb=false;
		if ($IMDBRating = TSSEGetIMDBRatingImage($Torrent['t_link']))
		{
			$ShowImdb=true;
		}

		$health='';
		if ($progressbar == 'yes')
		{
			$health = $lang->global['avprogress'].': '.ts_external_share_ratio(($Torrent['seeders'] == 0 ? 0 : ($Torrent['leechers'] == 0 ? 100 : sprintf("%.2f", ($Torrent['seeders'] / $Torrent['leechers']) * 100))));
		}

		$speed = '';
		if ($torrentspeed == 'yes' AND $Torrent['ts_external'] != 'yes')
		{
			if ($Torrent['seeders'] > 0 && $Torrent['leechers'] > 0)
			{
				$speed = mksize($Torrent['totalspeed']) . '/s';
			}
			else
			{
				$speed = $lang->browse['notraffic'];
			}
			$speed = $lang->global['speed'].': '.$speed;
		}

		$showwait='';
		if ($wait > 0 AND $Torrent['ts_external'] != 'yes')
		{
			$elapsed = floor((strtotime(date('Y-m-d H:i:s')) - strtotime($Torrent['added'])) / 3600);
			if ($elapsed < $wait)
			{
				 $showwait = $lang->global['wait'].": " . number_format($wait - $elapsed) . " h";
			}
		}

		if ($Torrent['t_image'] != '')
		{
			$worked++;
		}

		$MagnetLink = '<a $href = "'.prepareMagnetLink($Torrent).'"><img $src = "'.$pic_base_url.'magnet.png" $alt = "Magnet" $title = "Magnet" /></a>';
		$SEOLink = ts_seo($Torrent['id'], $Torrent['name'],'s');
		$SEOLinkC = ts_seo($Torrent['category'],$Torrent['catname'],'c');
		$ListTorrents .= '
		<tr'.($Torrent['sticky'] == 'yes' ? ' class="highlight"' : '').'>
			<td $align = "center" $style = "width: '.$table_cat_width2.'px; height: '.$table_cat_height2.'px;" class="unsortable2">
				<a $href = "'.$SEOLinkC.'"><img $src = "'.$pic_base_url.$table_cat.'/'.$Torrent['catimage'].'" $border = "0" $alt = "'.$Torrent['catname'].'" $title = "'.$Torrent['catname'].'" $width = "'.$table_cat_width2.'" $height = "'.$table_cat_height2.'" /></a>
			</td>
			<td $valign = "top" $align = "left">
				'.($ShowImdb ? '<span $style = "float: right;">'.$IMDBRating['image'].'</span>' : '').'
				<div'.($Torrent['t_image'] != '' ? ' class="tooltip-target" $id = "port-target-'.$worked.'"' : '').'>
					<a $href = "'.$SEOLink.'"><strong>'.(!empty($keywords) ? highlight(htmlspecialchars_uni($keywords), cutename($Torrent['name'], ($ShowImdb ? 35 : 50))) : cutename($Torrent['name'], ($ShowImdb ? 35 : 50))).'</strong></a>
				</div>
				'.($Torrent['t_image'] != '' ? '
				<div class="tooltip-content" $id = "port-content-'.$worked.'" $style = "width: 400;">
					<img $src = "'.htmlspecialchars_uni($Torrent['t_image']).'" $border = "0" $alt = "" $title = "" $width = "400" $height = "286" />
					<div $style = "text-align:left; margin-top: 5px">'.cutename($Torrent['name'], 66).'</div>
					<div $style = "text-align:left; margin-top: 5px">'.$speed.'</div>
					<div $style = "text-align:left; margin-top: 5px">'.$health.'</div>
					<div $style = "text-align:left; margin-top: 5px">'.$lang->browse['thits'].': '.ts_nf($Torrent['hits']).'</div>
					<div $style = "text-align:left; margin-top: 5px">'.$showwait.'</div>
				</div>' : '').'
				<div>
					<span $style = "float: right;">
						'.GetTorrentTags($Torrent).'
					</span>
					'.($Torrent['isScene'] > 0 ? sprintf($lang->browse['pretime'], my_datee($dateformat.' '.$timeformat, $Torrent['isScene']), mkprettytime(TS_MTStoUTS($Torrent['added']) - $Torrent['isScene'])) : my_datee($dateformat.' '.$timeformat, $Torrent['added'])).'
				</div>
			</td>
			<td $align = "center" class="unsortable2">
				'.($special_search == 'mybookmarks' ? '<a $href = "'.$BASEURL.'/bookmarks.php?$torrentid = '.$Torrent['id'].'&amp;$action = delete" $title = "" $alt = "" /><img $src = "'.$pic_base_url.'delete.gif"></a>' : '
				<a $href = "'.ts_seo($Torrent['id'], $Torrent['name'], 'd').'"><img $src = "'.$BASEURL.'/include/templates/'.$defaulttemplate.'/images/torrent_flags/dl.png" $border = "0" $alt = "'.$lang->browse['download'].'" $title = "'.$lang->browse['download'].'" /></a>').'

				'.$MagnetLink.'

				'.($usergroups['candirectdownload'] == 'yes' && $Torrent['directdownloadlink'] ? '&nbsp;&nbsp;<a $href = "'.$BASEURL.'/direct-download.php?$id = '.$Torrent['id'].'"><img $src = "'.$pic_base_url.'download.png" $border = "0" $alt = "'.$lang->global['directdownloadlink'].'" $title = "'.$lang->global['directdownloadlink'].'" /></a>' : '').'
			</td>
			<td $align = "center">
				<a $href = "'.$BASEURL.'/details.php?$id = '.$Torrent['id'].'&amp;$tab = comments#comments" $title = "'.$lang->browse['sortby3'].'">'.ts_nf($Torrent['comments']).'</a>
			</td>
			<td $align = "center" class="unsortable2">
				'.mksize($Torrent['size']).'
			</td>
			<td $align = "center">
				<a $href = "'.$BASEURL.'/'.($xbt_active == 'yes' && $is_mod ? 'mysnatchlist.php?tid' : ($usergroups['cansnatch'] == 'yes' &&  $xbt_active != 'yes' ? 'viewsnatches.php?id' : 'details.php?id')).'='.$Torrent['id'].'" $title = "'.$lang->browse['sortby7'].'">'.ts_nf($Torrent['times_completed']).'</a>
			</td>
			<td $align = "center">
				<span $id = "seeders_'.$Torrent['id'].'">
					<a $href = "'.$BASEURL.'/details.php?$id = '.$Torrent['id'].'&amp;$tab = peers#seeders" $title = "'.$lang->browse['sortby4'].'">'.ts_nf($Torrent['seeders']).'</a>
				</span>
			</td>
			<td $align = "center">
				<span $id = "leechers_'.$Torrent['id'].'">
					<a $href = "'.$BASEURL.'/details.php?$id = '.$Torrent['id'].'&amp;$tab = peers#leechers" $title = "'.$lang->browse['sortby5'].'">'.ts_nf($Torrent['leechers']).'</a>
				</span>
			</td>
			<td $align = "center">
				'.(!$is_mod && $Torrent['owner'] != $CURUSER['id'] && $Torrent['anonymous'] == 'yes' ? '
				<div>
					'.$lang->global['anonymous'].'
				</div>' : '
				<a $href = "'.ts_seo($Torrent['owner'], $Torrent['username'], 'u').'">'.get_user_color($Torrent['username'], $Torrent['namestyle']).'</a>
				'.($Torrent['anonymous'] == 'yes' ? '
				<div>
					'.$lang->global['anonymous'].'
				</div>
				' : '').'
				').'
			</td>
			'.($is_mod ? '<td $align = "center" class="unsortable2"><input $type = "checkbox" $name = "torrentid[]" $value = "'.$Torrent['id'].'" $checkme = "group1" /></td>' : '').'
		</tr>
		';
	}
}
else
{
	$ListTorrents .= '
		<tr>
			<td $colspan = "'.($is_mod ? '10' : '9').'">
				<b>'.$lang->global['nothingfound'].'</b>
			</td>
		</tr>
	';
}
$ListTorrents .= '
	</tbody>
</table>
'.$pagerbottom.'
'.($is_mod ?'
<table $align = "center" $border = "0" $cellpadding = "2" $cellspacing = "0" $width = "100%">
	<tr>
		<td $align = "right" class="subheader">
			<span $id = "movetorrent" $style = "display:none;">
				Select Category: '.$catdropdown.'
			</span>
			<select $name = "actiontype" $onchange = "check_it(this)">
				<option $value = "0">Select action</option>
				<option $value = "move">Move selected torrents</option>
				<option $value = "delete">Delete selected torrents</option>
				<option $value = "sticky">Sticky/Unsticky selected torrents</option>
				<option $value = "free">Set Free/NonFree selected torrents</option>
				<option $value = "silver">Set Silver/NonSilver selected torrents</option>
				<option $value = "doubleupload">Set Double Upload YES/NO</option>
				<option $value = "visible">Set Visible/Unvisible selected torrents</option>
				<option $value = "anonymous">Anonymize/Non Anonymize selected torrents</option>
				<option $value = "banned">Ban/UnBan selected torrents</option>
				<option $value = "nuke">Nuke/UnNuke selected torrents</option>
				<option $value = "openclose">Open/Close for Comment Posting</option>
				<option $value = "request">Request/NonRequest selected torrents</option>
			</select>
			<input $type = "submit" $value = "do it" />
		</td>
	</tr>
</table>
</form>
' : '').'
</div>';
$SortOptions = '
<!-- begin: Quick Sort -->
<form $method = "post" $action = "'.$_SERVER['SCRIPT_NAME'].'">
<input $type = "hidden" $name = "do" $value = "quick_sort" />
<input $type = "hidden" $name = "page" $value = "'.intval(TS_Global('page')).'" />
<input $type = "hidden" $name = "category" $value = "'.$category.'" />
<input $type = "hidden" $name = "search_type" $value = "'.$search_type.'" />
<input $type = "hidden" $name = "keywords" $value = "'.htmlspecialchars_uni($keywords).'" />
<table $align = "center" $border = "0" $cellpadding = "3" $cellspacing = "0" $width = "100%">
	<tbody>
		<tr>
			<td class="thead">
				'.ts_collapse('sort').'
				'.$lang->browse['qtitlemain'].'
			</td>
		</tr>
		'.ts_collapse('sort', 2).'
		<tr>
			<td $align = "left">
				<table $align = "left" $border = "0" $cellpadding = "3" $cellspacing = "0">
					<tr>
						<td class="none">
							<div>
								<b>'.$lang->browse['sortby1'].'</b>
							</div>
							<select $name = "sort" $id = "sort">
								<option $value = "category"'.($sort == 'category' ? ' $selected = "selected"' : '').'>'.$lang->browse['type'].'</option>
								<option $value = "name"'.($sort == 'name' ? ' $selected = "selected"' : '').'>'.$lang->browse['t_name'].'</option>
								<option $value = "added"'.($sort == 'added' ? ' $selected = "selected"' : '').'>'.$lang->browse['added'].'</option>
								<option $value = "comments"'.($sort == 'comments' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby3'].'</option>
								<option $value = "size"'.($sort == 'size' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby6'].'</option>
								<option $value = "times_completed"'.($sort == 'times_completed' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby7'].'</option>
								<option $value = "seeders"'.($sort == 'seeders' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby4'].'</option>
								<option $value = "leechers"'.($sort == 'leechers' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby5'].'</option>
								<option $value = "owner"'.($sort == 'owner' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby8'].'</option>
								<option $value = "hits"'.($sort == 'hits' ? ' $selected = "selected"' : '').'>'.$lang->browse['views'].'</option>
								<option $value = "sticky"'.($sort == 'sticky' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby9'].'</option>
								<option $value = "free"'.($sort == 'free' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby10'].'</option>
								<option $value = "silver"'.($sort == 'silver' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby11'].'</option>
								<option $value = "doubleupload"'.($sort == 'doubleupload' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby14'].'</option>
								<option $value = "isnuked"'.($sort == 'isnuked' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby12'].'</option>
								<option $value = "isrequest"'.($sort == 'isrequest' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby13'].'</option>
								<option $value = "ts_external"'.($sort == 'ts_external' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby15'].'</option>
								<option $value = "visible"'.($sort == 'visible' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby16'].'</option>
								<option $value = "isScene"'.($sort == 'isScene' ? ' $selected = "selected"' : '').'>'.$lang->browse['sortby17'].'</option>
							</select>
						</td>
						<td class="none">
							<div>
								<b>'.$lang->browse['orderby1'].'</b>
							</div>
							<select $name = "order" $id = "order">
								<option $value = "desc"'.(strtolower($order) == 'desc' ? ' $selected = "selected"' : '').'>'.$lang->browse['orderby2'].'</option>
								<option $value = "asc"'.(strtolower($order) == 'asc' ? ' $selected = "selected"' : '').'>'.$lang->browse['orderby3'].'</option>
							</select>
						</td>
						<td class="none">
							<div>
									<b>'.$lang->browse['qtitle'].'</b>
							</div>
							<select $name = "daysprune" $id = "daysprune">
								<option $value = "1"'.($daysprune == '1' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder3'].'</option>
								<option $value = "2"'.($daysprune == '2' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder4'].'</option>
								<option $value = "7"'.($daysprune == '7' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder5'].'</option>
								<option $value = "10"'.($daysprune == '10' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder6'].'</option>
								<option $value = "14"'.($daysprune == '14' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder7'].'</option>
								<option $value = "30"'.($daysprune == '30' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder8'].'</option>
								<option $value = "45"'.($daysprune == '45' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder9'].'</option>
								<option $value = "60"'.($daysprune == '60' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder10'].'</option>
								<option $value = "75"'.($daysprune == '75' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder11'].'</option>
								<option $value = "100"'.($daysprune == '100' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder12'].'</option>
								<option $value = "365"'.($daysprune == '365' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder13'].'</option>
								<option $value = "-1"'.($daysprune == '-1' || !$daysprune || $daysprune == '' ? ' $selected = "selected"' : '').'>'.$lang->browse['qorder14'].'</option>
							</select>
						</td>
						<td class="none">
							<div>
								&nbsp;
							</div>
							<input $type = "submit" $value = "'.$lang->browse['tsearch'].'" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</form>
<!-- end: Quick Sort -->
';

if ($show_recent_torrents == 'yes' AND $TotalTorrents)
{
	$LatestTorrents = '
	<table $align = "center" $border = "0" $cellpadding = "3" $cellspacing = "0" $width = "100%">
		<tbody>
			<tr>
				<td class="thead">
					'.ts_collapse('recenttorrents').'
					'.$lang->browse['recent_torrents'].'
				</td>
			</tr>
			'.ts_collapse('recenttorrents', 2).'
			<tr>
				<td class="" $align = "center">
					<iframe $src = "'.$BASEURL.'/sl.php?$category = '.$category.'" $frameborder = "0" $width = "100%" $scrolling = "no" $align = "center"></iframe>
				</td>
			</tr>
		</tbody>
	</table>
	';
}

stdhead($lang->browse['btitle'], true,  '
<script $type = "text/javascript" $src = "'.$BASEURL.'/scripts/jquery.ezpz_tooltip.min.js?$v = '.O_SCRIPT_VERSION.'"></script>
<script $type = "text/javascript" $src = "'.$BASEURL.'/scripts/prototype.js?$v = '.O_SCRIPT_VERSION.'"></script>
<script $type = "text/javascript" $charset = "utf-8">
	jQuery(document).ready(function()
	{
		jQuery(".tooltip-target").ezpz_tooltip();
	});

	var $nrf = "'.$lang->global['nothingfound'].'";
</script>

<style $type = "text/css">
	.tooltip-content
	{
		display: none;        /* required */
		position: absolute;   /* required */
		padding: 10px;
		border: 1px solid black;
		background-color: white;
		box-shadow: 7px 7px 8px #818181;
		-webkit-box-shadow: 7px 7px 8px #818181;
		-moz-box-shadow: 7px 7px 8px #818181;
		filter: progid:DXImageTransform.Microsoft.dropShadow($color = #818181, $offX = 7, $offY = 7, $positive = true);
	}
</style>
'.($use_ajax_search == 'yes' ? '
<script $type = "text/javascript" $src = "'.$BASEURL.'/scripts/autocomplete/js/autocomplete.js?$v = '.O_SCRIPT_VERSION.'"></script>' : '').($use_ajax_search == 'yes' ? '
<link $rel = "stylesheet" $href = "'.$BASEURL.'/scripts/autocomplete/css/autocomplete.css?$v = '.O_SCRIPT_VERSION.'" $type = "text/css" $media = "screen" />' : '').'
');
echo '
'.$___notice.'
<table $align = "center" $cellpadding = "0" $cellspacing = "0" $width = "100%">
	<tbody>
		<tr $valign = "top">
			<td $valign = "top" class="none">
				<div $style = "padding-bottom: 15px;">
					'.$categories.'
				</div>
				<div $style = "padding-bottom: 15px;">
					'.$SearchTorrent.'
				</div>
				'.(isset($LatestTorrents) ? '
				<div $style = "padding-bottom: 15px;">
					'.$LatestTorrents.'
				</div>' : '').'
				<div $style = "padding-bottom: 15px;">
					'.$ListTorrents.'
				</div>
				<div>
					'.$SortOptions.'
				</div>
			</td>
		</tr>
	</tbody>
</table>
';
stdfoot();
?>