<?php
/* TS SE Default Template (Footer) by xam - Version 1.2.1
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
+-------------------------------------------------------------------------------------------
| You have no permission to modify this file unless you purchase a Brading Free Product!
+-------------------------------------------------------------------------------------------
*/
if(!defined('IN_TRACKER')) die('Hacking attempt!');
$QueryForm = '';

if (isset($GLOBALS['ts_start_time']))
{
	$GLOBALS['totaltime'] = round((array_sum(explode(' ',microtime())) - $GLOBALS['ts_start_time'] ),4);
}

if ($usergroups['cansettingspanel'] == 'yes')
{
	$QueryForm = '
	<form method="post" action="'.$BASEURL.'/ts_query_explain.php" name="ts_queries">
		'.(isset($GLOBALS['totaltime']) ? '<input type="hidden" name="totaltime" value="'.$GLOBALS['totaltime'].'" />' : '').'
		'.$GLOBALS['queries'].'
	</form>';
}
echo '
	</div>
	</div><!-- id main end here -->
		<div id="footer">
			<div class="fcontent">
				Powered by <font color="white"><strong><a href="https://templateshares.net" target="_blank">'.VERSION.'</a></strong></font>  © '.@date('Y').' <font color="white"><a href="'.$BASEURL.'" target="_self"><strong>'.$SITENAME.'</strong></a></font>
				[Executed in <b> '.(isset($GLOBALS['totaltime']) ? $GLOBALS['totaltime'] : 'N/A').' </b>seconds'.($usergroups['cansettingspanel'] == 'yes' && !defined('SKIP_SHOW_QUERIES') ? ' with <b><a href="javascript:void(0);" onclick="document.forms.ts_queries.submit();">'.intval($GLOBALS['totalqueries']).'</a></b> queries!]' : ']').'
			</div><!-- Class fcontent end here -->			
		</div><!-- id footer end here -->
		'.(isset($GLOBALS['PHP_ERROR']) ? $GLOBALS['PHP_ERROR'] : '').'

'.$alertpm.'
'.($CURUSER['options'] && TS_Match($CURUSER['options'], 'N1') ?'
<!-- TS Auto DST Correction Code -->
<form action="'.$BASEURL.'/usercp.php?act=auto_dst" method="post" name="dstform">
	<input type="hidden" name="act" value="auto_dst" />
</form>
<script type="text/javascript">
<!--
	var tzOffset = '.$CURUSER['tzoffset'].' + '.(TS_Match($CURUSER['options'], 'O1') ? '1' : '0').';
	var utcOffset = new Date().getTimezoneOffset() / 60;
	if (Math.abs(tzOffset + utcOffset) == 1)
	{	// Dst offset is 1 so its changed
		document.forms.dstform.submit();
	}
//-->
</script>
<!-- TS Auto DST Correction Code -->
' : '').(isset($GLOBALS['ts_cron_image']) ? '
<!-- TS Auto Cronjobs code -->
	<img src="'.$BASEURL.'/ts_cron.php?rand='.TIMENOW.'" alt="" title="" width="1" height="1" border="0" />
<!-- TS Auto Cronjobs code -->
' : '').'
<!-- TS Save User Stats Code -->
	<script type="text/javascript">
	<!--
		function TSwriteCookie()
		{
			var today = new Date();
			var the_date = new Date("December 31, 2023");
			var the_cookie_date = the_date.toGMTString();
			var the_cookie = "users_resolution="+ screen.width +"x"+ screen.height;
			var the_cookie = the_cookie + ";expires=" + the_cookie_date;
			document.cookie=the_cookie;
		}
		TSwriteCookie();
	//-->
	</script>
	<img src="'.$BASEURL.'/'.$staffcp_path.'/save_stats.php?rand='.TIMENOW.(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ? '&ref='.urlencode(htmlspecialchars($_SERVER['HTTP_REFERER'])) : '').'" alt="" title="" width="1" height="1" border="0" />
<!-- TS Save User Stats Code -->
'.$QueryForm.'
</body>
</html>
';
/*
+-------------------------------------------------------------------------------------------
| You have no permission to modify this file unless you purchase a Brading Free Product!
+-------------------------------------------------------------------------------------------
*/