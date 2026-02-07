<?php
/* TS SE Default Template (Header) by xam - Version 1.4.1
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
if(!defined('IN_TRACKER')) die('Hacking attempt!');
/*
ob_start();

header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

header('X-Powered-By: TSSE '.O_SCRIPT_VERSION);
header('Content-Type: text/html; charset='.$charset);
header('X-UA-Compatible: IE=edge,chrome=1');
*/
?>
<!DOCTYPE html>
<html id="TSSE" lang="en-US" dir="LTR" >

<head>

<base href="<?php echo $BASEURL; ?>/" />
<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>" />

<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="MobileOptimized" content="width" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

<meta name="description" content="<?php echo $metadesc; ?>" />

<link rel="stylesheet" href="<?php echo $BASEURL; ?>/style.php?theme=<?php echo $defaulttemplate; ?>&amp;style=style.css&amp;v=3a" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo $BASEURL; ?>/scripts/colorbox/colorbox.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo $BASEURL; ?>/scripts/jquery-ui-1.10.4.custom/css/flick/jquery-ui-1.10.4.custom.css" type="text/css" media="screen" />

<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo $BASEURL; ?>/rss.php" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php echo $BASEURL; ?>/rss.php" />

<link rel="shortcut icon" href="<?php echo $BASEURL; ?>/favicon.ico" type="image/x-icon" />

<script type="text/javascript">
	//<![CDATA[
	var baseurl="<?php echo htmlspecialchars_uni($BASEURL); ?>";
	var dimagedir="<?php echo $pic_base_url; ?>";
	var themedir="<?php echo $BASEURL; ?>/include/templates/<?php echo $defaulttemplate; ?>/";
	var charset="<?php echo $charset; ?>";
	var userid="<?php echo (isset($CURUSER['id']) ? (int)$CURUSER['id'] : 0); ?>";
	var securitytoken = "<?php echo (isset($CURUSER['securitytoken']) ? $CURUSER['securitytoken'] : ''); ?>";
	var lang_resized = "<?php echo $lang->global['resized']; ?>";
	if (window.top !== window.self) {document.write = "";window.top.location = window.self.location; setTimeout(function(){document.body.innerHTML='';},1);window.self.onload=function(evt){document.body.innerHTML='';};}
	//]]>
</script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/jquery-1.11.2.min.js?v=<?php echo O_SCRIPT_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/jquery.lazyload.mini.js?v=<?php echo O_SCRIPT_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/colorbox/jquery.colorbox-min.js?v=<?php echo O_SCRIPT_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/bookmarkscroll.js?v=<?php echo O_SCRIPT_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/main.js?uv=3a&v=<?php echo O_SCRIPT_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/menu.js?v=<?php echo O_SCRIPT_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/collapse.js?v=<?php echo O_SCRIPT_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/tooltip.js?v=<?php echo O_SCRIPT_VERSION; ?>"></script>
<script type="text/javascript" src="<?php echo $BASEURL; ?>/scripts/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js?v=<?php echo O_SCRIPT_VERSION; ?>"></script>

<?php
if($iv == 'reCAPTCHA')
{
		echo '<script src="https://www.google.com/recaptcha/api.js?hl='.urlencode($reCAPTCHALanguage).'" async defer></script>';
}
?>

<script src="https://kit.fontawesome.com/ed49cd77f2.js" crossorigin="anonymous"></script>
<link href="//fonts.googleapis.com/css?family=Montserrat:300,700|Open+Sans:400,700" rel="stylesheet" type="text/css">

<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function()
	{
		jQuery(document).ajaxStart(function()
		{
			jQuery("#ajaxloader").show();
		}).ajaxStop(function()
		{
			jQuery("#ajaxloader").hide();
		});

		jQuery("#select_zone").change(function()
		{
			var searchZone = jQuery(this).val();
			var actionURL = '';
			var baseurl = '<?php echo $BASEURL; ?>';

			if (searchZone == "forum")
			{
				actionURL = "/tsf_forums/tsf_search.php?action=do_search";
			}
			else if (searchZone == "request")
			{
				actionURL = "/viewrequests.php?do=search_request";
			}
			else if (searchZone == "subtitle")
			{
				actionURL = "/ts_subtitles.php?action=search";
			}
			else
			{
				actionURL = "/browse.php?do=search&search_type=t_both";
			}

			actionURL = baseurl+actionURL;
			jQuery("#search_form").attr("action", actionURL);
		});

		jQuery(window).on("scroll", function()
		{
			if(jQuery(window).scrollTop() > 30)
			{
				jQuery("#menu").addClass("fixedMenu");
			}
			else
			{
				jQuery("#menu").removeClass("fixedMenu");
			}
		});

		jQuery("#quickprofileview").click(function(e)
		{
			e.preventDefault;
			jQuery(".messagePreview").hide();
			jQuery(".preview").toggle();
			return false;
		});

		jQuery('input[name="quicksearch"]').click(function(e)
		{
			e.preventDefault;
			jQuery('.quicksearch').toggle();
			return false;
		});

		if ( !navigator.userAgent.match( /ipad/i ) )
		{
			jQuery("#lazyload img").lazyload({placeholder : themedir+"images/loading.gif", effect:"fadeIn"});
		}
		jQuery("a#ts_show_preview").colorbox({photo: true, title: function()
		{
			var url = jQuery(this).attr('href');
			return '<a href="'+url+'" target="_blank"><font color="white"><?php echo $lang->header['open_new_window']; ?></font></a>';
		}});
	});
	//]]>
</script>
<?php
$lang->load('scripts');
$langdir = scandir(INC_PATH.'/languages');
$dirlist = '';
foreach ($langdir as $language)
{
	if ($language != '.' AND $language != '..' AND is_dir(INC_PATH.'/languages/'.$language))
	{
		$dirlist .= '
		<a href="'.$BASEURL.'/set_language.php?language='.$language.'&amp;redirect=yes"><img src="'.$BASEURL.'/include/languages/'.$language.'/flag/flag.gif" alt="'.$language.'" title="'.$language.'" width="32" height="20" border="0" class="inline" /></a> ';
	}
}

echo '
'.(isset($includescripts) ? $includescripts : '').'
</head>
<body class="yui-skin-sam">
<div id="ajaxloader" style="position: fixed; bottom: 20px; left: 10px; width: 100%; z-index: 2147483647; text-align: left; display: none;">
	<div style="padding: 10px 30px; background: #000;  opacity: 0.7; display: inline-block; border-radius:4px;">
		<img src="'.$BASEURL.'/include/templates/'.$defaulttemplate.'/images/ajax_loading.gif" alt="" title="" class="middle" />
	</div>
</div>';

if(user_options($CURUSER['options'], 'fb-shoutbox') AND !user_options($CURUSER['options'], 'shoutbox'))
{
	include(TSDIR.'/ts_shoutbox/fb-shoutbox.php');
}

echo '
	<div id="top">
		<div class="content">
			<div class="logo"><img src="'.$BASEURL.'/include/templates/'.$defaulttemplate.'/images/logo.jpg" alt="" title="" /></div>		
			<div class="memberArea">				
				'.(isset($CURUSER) && $CURUSER['id'] > 0 ? '
					<div class="preview">
						<div class="prContent">
							<div class="avatar">'.get_user_avatar($CURUSER['avatar']).'</div>
							<div class="welcomeback">
								'. $lang->global['welcomeback'].' <a href="'. ts_seo($CURUSER['id'], $CURUSER['username']).'">'. get_user_color($CURUSER['username'],$usergroups['namestyle']).'</a> '.$medaldon.' '.$warn.'<br>
								'. $lang->global['ratio'].' '. $ratio.' | '. $lang->global['bonus'].' <a href="'. $BASEURL.'/mybonus.php">'. number_format($CURUSER['seedbonus'], 2).'</a><br>'.$lang->global['uploaded'].' <font color="green">'. mksize($CURUSER['uploaded']).'</font> | '. $lang->global['downloaded'].' <font color="red">'. mksize($CURUSER['downloaded']).'</font>
								<br>'.$lang->global['slotview'].' '.(!$usergroups['slotlimit'] ? $lang->global['unlimited'] : $usergroups['slotlimit']).'
							</div>
							<div class="clear"></div>
							<div class="pactions">
								<a href="javascript:void(0);" class="apactions" data-action="showuploaded" data-lang="'.$lang->global['qact1'].'">'.$lang->global['qact1'].'</a>
								<a href="javascript:void(0);" class="apactions" data-action="showcompleted" data-lang="'.$lang->global['qact2'].'">'.$lang->global['qact2'].'</a>
								<a href="javascript:void(0);" class="apactions" data-action="showleechs" data-lang="'.$lang->global['qact3'].'">'.$lang->global['qact3'].'</a>
								<a href="javascript:void(0);" class="apactions" data-action="showseeds" data-lang="'.$lang->global['qact4'].'">'.$lang->global['qact4'].'</a>
								<a href="javascript:void(0);" class="apactions" data-action="showsnatches" data-lang="'.$lang->global['qact5'].'">'.$lang->global['qact5'].'</a>
								<a href="javascript:void(0);" class="apactions" data-action="flushtorrents" data-lang="'.$lang->global['qact6'].'">'.$lang->global['qact6'].'</a>
							</div>
							<div class="qactions">
								<a href="'.ts_seo($CURUSER['id'], $CURUSER['username']).'">'.$lang->global['profile'].'</a> | <a href="'.$BASEURL.'/logout.php?logouthash='.md5(USERIPADDRESS).'" onclick="return log_out();">'.$lang->global['log-out'].'</a>
							</div>
						</div>
					</div>
					<div class="messagePreview">
					</div>
					<a href="javsacript:void(0);" id="quickprofileview" class="button"><i class="far fa-address-card"></i> '.$CURUSER['username'].'</a>					
					<a href="'.$BASEURL.'/friends.php" class="button"><i class="fas fa-user-friends"></i></a>
					<a href="'.$BASEURL.'/messages.php" class="button" id="rmypms"><i class="far fa-envelope"></i></a>
					'.($CURUSER['pmunread'] > 0 ? '<div class="popup">'.$CURUSER['pmunread'].'</div>' : '').'
					<script>
						jQuery(document).ready(function()
						{
							jQuery(document).on("click", "#rmypms", function(e)
							{
								e.preventDefault();
									var $clickedMessageBox = jQuery(this);

									if(jQuery(".messagePreview").is(":visible"))
									{
										jQuery(".messagePreview").hide();
										return;
									}

									jQuery(".preview").hide();

									$clickedMessageBox .html(\'<img src="'.$BASEURL.'/include/templates/'.$defaulttemplate.'/images/ajax_loading.gif" alt="" title="" class="middle" id="prLoader" style="padding-left: 5px;" />\');
									jQuery("#dialog").empty().remove();
									
									jQuery.ajax
									({
										url: baseurl+"/messages.php",
										data: "fma=true&securitytoken="+securitytoken,
										type: "POST",
										contentType: "application/x-www-form-urlencoded; charset="+charset,
										success: function(msresp)
										{
											$clickedMessageBox .html(\'<i class="far fa-envelope"></i>\');
											jQuery(".messagePreview").html(msresp).fadeIn();
										}
									});
								return false;
							});

							jQuery(document).on("click", ".apactions", function(e)
							{
								e.preventDefault();
									var $fireAction=jQuery(this).data("action"), $fireTitle=jQuery(this).data("lang");
									
									jQuery("#prLoader").remove();
									jQuery("#dialog").empty().remove();

									jQuery(\'<img src="'.$BASEURL.'/include/templates/'.$defaulttemplate.'/images/ajax_loading.gif" alt="" title="" class="middle" id="prLoader" style="padding-left: 5px;" />\').appendTo(jQuery(this));
									
									jQuery.ajax
									({
										url: baseurl+"/ts_ajax2.php",
										data: "what="+$fireAction+"&userid='.$CURUSER['id'].'&securitytoken="+securitytoken,
										type: "POST",
										contentType: "application/x-www-form-urlencoded; charset="+charset,
										success: function(sresp)
										{											
											jQuery(\'<div id="dialog">\'+sresp+\'</div>\').dialog({resizable: false,modal:true,width:"75%", height: "auto", title: $fireTitle});
											jQuery("#prLoader").remove();
										}
									});
								return false;
							});
						});
					</script>
				' : '
				<a href="'.$BASEURL.'/login.php" class="button"><i class="fas fa-key"></i> '.$lang->header['login'].'</a><a href="'.$BASEURL.'/signup.php" class="button"><i class="fas fa-user-edit"></i> '.$lang->header['register'].'</a>').'
			</div>
				'.(count($langdir) > 1 ? '<div class="flags">'.$dirlist.'</div>' : '').'
			<div class="clear"></div>
		</div>
	</div>';

if (isset($usergroups['showads']) AND $usergroups['showads'] == 'no' AND !defined('DISABLE_ADS'))
{
	define('DISABLE_ADS', true);
}

if (!defined('DISABLE_ADS') AND (isset($TSSECache->Cache['ads']) AND $ads = $TSSECache->Cache['ads']['content']))
{
	$str  = '<table class="main" border="1" cellspacing="0" cellpadding="0" width="100%"><tr><td class="text">';
	if (strstr($ads, '[TS_ADS]'))
	{
		$ts_ads_count = explode('[TS_ADS]', $ads);
		$random_ts_ads = rand(0, (count($ts_ads_count) -1));
		$str .= $ts_ads_count[$random_ts_ads];
	}
	else $str .= $ads;
	$str .= '</td></tr></table>';
	echo $str;
	unset($ads, $str);
}

echo '
<div id="menu">
	<div class="content">
		<div class="search">
			<i class="fas fa-search fasicon"></i>
			<input type="button" name="quicksearch" value="'. $lang->header['qsearch'].'" placeholder="" class="buttonwithpadding" />
			<div class="quicksearch">
				<form method="post" action="'.$BASEURL.'/browse.php?do=search&search_type=t_both" name="search_form" id="search_form">
					<input type="hidden" name="search_type" value="t_both" />
					<input type="hidden" name="do" value="search" />
					<input type="hidden" name="postthread" value="1" />
					<input type="hidden" name="forums[]" value="all" />
					<div id="ts_search_form" >						
						<input type="text" value="" placeholder="'.$lang->global['storrent2'].'" name="keywords" /> '.$lang->header['in'].'
						<select name="search_zone" id="select_zone">
							<option value="torrent" >'.$lang->header['torrents'].'</option>
							<option value="forum" >'.$lang->header['forums'].'</option>
							<option value="request" >'.$lang->header['requests'].'</option>
							<option value="subtitle" >'.$lang->header['subtitles'].'</option>
						</select>
						<input type="submit" value="'.$lang->global['buttonsearch'].'" />
					</div>
				</form>
			</div>
		</div>';
	$menu_language = (isset($_COOKIE['ts_language']) ? $_COOKIE['ts_language'] : $defaultlanguage);
	if (file_exists($rootpath.'/'.$cache.'/menu_'.$menu_language.'.php'))
		require_once($rootpath.'/'.$cache.'/menu_'.$menu_language.'.php');
	else
		require_once($rootpath.'/'.$cache.'/menu_english.php');
	?>
		<div class="clear"></div>
	</div><!-- class content end here -->
</div><!-- id menu end here -->
<div id="main">
	<div class="left_side">
<?php
if ($offlinemsg)
{
	$warnmessages[] = sprintf($lang->header['trackeroffline'], $BASEURL);
}

if ($is_mod AND !defined('SKIP_MOD_QUERIES') AND !defined('IN_AJAX'))
{
	$_staffmessages = mysqli_num_rows(sql_query('SELECT id FROM staffmessages WHERE answeredby = \'0\''));
	$_ts_reports = mysqli_num_rows(sql_query('SELECT rid FROM ts_reports WHERE confirmed = \'0\''));
	$_unbanrequests = mysqli_num_rows(sql_query('SELECT id FROM unbanrequests WHERE reply = \'\''));

	if ($_staffmessages OR $_ts_reports OR $_unbanrequests)
	{
		$lang->load('staff_announcements');
	}

	if ($_staffmessages)
	{
		$infomessages[] = sprintf($lang->staff_announcements['msg1'], $_staffmessages, $BASEURL);
	}

	if ($_ts_reports)
	{
		$infomessages[] = sprintf($lang->staff_announcements['msg2'], $_ts_reports, $BASEURL);
	}

	if ($_unbanrequests)
	{
		$infomessages[] = sprintf($lang->staff_announcements['msg3'], $_unbanrequests, $BASEURL);
	}
}

if (!defined('IN_AJAX') && !$is_mod && isset($CURUSER) && $CURUSER['donoruntil'] != '0000-00-00 00:00:00' && warn_donor(TS_MTStoUTS($CURUSER['donoruntil']) - gmtime()) && $CURUSER['id'] > 0)
{
	require_once(INC_PATH.'/functions_mkprettytime.php');
	$warnmessages[] = sprintf($lang->header['warndonor'], $BASEURL, mkprettytime(TS_MTStoUTS($CURUSER['donoruntil']) - gmtime()));
}

if((!defined('IN_AJAX') && isset($CURUSER) && $CURUSER['id'] > 0 && $CURUSER['downloaded'] > 0 && $CURUSER['leechwarn'] == 'yes' AND TS_MTStoUTS($CURUSER['leechwarnuntil']) > TIMENOW))
{
	$TSSEConfig->TSLoadConfig('CLEANUP');
	require_once(INC_PATH.'/functions_mkprettytime.php');
	$warnmessages[] = sprintf($lang->header['warned'], $leechwarn_remove_ratio, mkprettytime(TS_MTStoUTS($CURUSER['leechwarnuntil']) - TIMENOW));
}

if (isset($CURUSER) AND $CURUSER['id'] > 0 AND $CURUSER['announce_read'] == 'no')
	$infomessages[] = '<span id="new_ann" style="display: block;"><a href="'.$BASEURL.'/clear_ann.php" id="readAnnouncement">'.$lang->header['newann'].'</a></span>
	<script>jQuery("#readAnnouncement").colorbox({iframe:true, innerWidth:650, innerHeight:300});</script>';

if ($CURUSER['pmunread'] > 0 AND $msgalert)
	$infomessages[] = '<a href="'.$BASEURL.'/messages.php">'.sprintf($lang->header['newmessage'], ts_nf($CURUSER['pmunread'])).'</a>';

if (isset($warnmessages))
{
	echo show_notice(implode('<br />',$warnmessages), true);
	unset($warnmessages);
}

if (isset($infomessages))
{
	echo show_notice(implode('<br />',$infomessages));
	unset($infomessages);
}