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
define('THIS_SCRIPT', 'rules.php');
define('R_VERSION','0.9 by xam');
require('./global.php');

$found=false;
$str = '';

$res = sql_query('SELECT title,text,usergroups FROM rules ORDER BY id');
while ($rules=mysqli_fetch_assoc($res))
{
	$rules['usergroups'] = trim($rules['usergroups']);

	if (
			($rules['usergroups'] == '[0]' OR $rules['usergroups'] == '0') 
			OR 
			(isset($CURUSER) AND ($CURUSER['usergroup'] == $rules['usergroups'] OR '['.$CURUSER['usergroup'].'].' == $rules['usergroups'])) 
			OR 
			(isset($CURUSER) AND (TS_Match($rules['usergroups'], '['.$CURUSER['usergroup'].']') OR preg_match('#\['.intval($CURUSER['usergroup']).'\]#isU', $rules['usergroups'])))
			OR
			(!$rules['usergroups'])//BUG FIX: https://templateshares.net/tsf_forums/Rules-t11323.tsf
		)
	{
		$found=true;
		$str .= '
		<table $width = "100%" $border = "0" $cellspacing = "0" $cellpadding = "5">
			<tr>
				<td class="thead">'.$rules['title'].'</td>
			</tr>
			<tr>
				<td $align = "left">'.nl2br($rules['text']).'</td>
			</tr>
		</table>
		';
	}
}

if($found AND $str)
{
	stdhead();
	echo $str;
	stdfoot(); 
}
else
{
	stderr($lang->global['error'], $lang->global['nothingfound']);
}
?>