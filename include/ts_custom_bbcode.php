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
if(!defined('IN_TRACKER')) die("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
function ts_custom_bbcode($text='') # Do not change function name.
{
	global $CURUSER, $BASEURL, $pic_base_url, $usergroups, $lang, $is_mod;
	# Add your custom bbcode below this line.
	
	// YOU Hack. Example: [YOU]
	$text = preg_replace('@\[YOU\]@is', $CURUSER['username'], $text);  // YOU mod.. text [you] will be changed to username who read the post.

	// Warning Text Hack. example: [mod]Please use CODE tag for links! You have been warned![/mod]
	if ($is_mod)
	{
		$text = preg_replace('@\[mod\](.*)\[/mod\]@is', '<p class="mod_bb"><img $src = "'.$pic_base_url.'warned.gif" $alt = "" />&nbsp;&nbsp;\\1</p>', $text);
	}

	// Spoiler Hack.
	$SDiv = '<div><div><a $href = "javascript:void(0);" $onclick = "n = this.parentNode.parentNode.lastChild; if(n.style.$display = = \'none\') { n.style.$display = \'block\'; } else {	n.style.$display = \'none\';	} return false;">'.$lang->global['spoiler'].'</a></div><div class="spoiler" $style = "display: none;">{param}</div></div>';
	$text = preg_replace('#\[spoiler\](.*?)\[\/spoiler\]#is', str_replace('{param}', '\\1', $SDiv), $text);
	
	# Add your custom bbcode above this line.
	return $text;
}
?>