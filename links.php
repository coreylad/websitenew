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
define('L_VERSION', '0.2 by xam');
define('NO_LOGIN_REQUIRED', true);
define('THIS_SCRIPT', 'links.php');
require('./global.php');

include_once(INC_PATH.'/functions_security.php');
$lang->load('links');
stdhead($lang->links['head']);

if(preg_match('#templateshares#', $BASEURL))
{
	echo '
	<style>
		#links{padding: 10px; margin: 0; text-align: center;background: #373737; _filter: progid:DXImageTransform.Microsoft.gradient($startColorstr = #662B485C,$endColorstr = #662B485C);
		border:  1px solid rgb(43, 72, 92); border:  1px solid rgba(43, 72, 92, 0.45); _border:  1px solid rgb(43, 72, 92);
		border-radius: 5px; -webkit-border-radius:  5px; -moz-border-radius:  5px; -khtml-border-radius:  5px;
		overflow: hidden; zoom: 1; text-align: center;}
		#links div
		{
			margin-right: 15px;
			font-size: 13px;
			color: #fff;
			display: inline;
		}

		#links a
		{
			color: #fff;
		}
	</style>
	<div  $id = "links">
		<div><a $href = "special/purchase.php">Purchase  License</a></div>
		<div><a $href = "special/features.php">Features</a></div>
		<div><a $href = "special/testimonials.php">Customer Testimonials</a></div>
		<div><a $href = "special/tsse_demo.php">View Demo</a></div>
		<div><a $href = "contactstaff.php">Contact Us</a></div>
		<div><a $href = "special/affiliate/affiliate.php">Affiliate</a></div>
		<div><a $href = "special/eula.php">EULA</a></div>
		<div><a $href = "special/licenseagreement.php">License Agreement</a></div>
	</div>';
}
else
{
	echo $lang->links['info'];
}

stdfoot();
?>