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
if(!defined("IN_TRACKER")) die("Hacking attempt!");
echo '
<script $type = "text/javascript">	
	//<![CDATA[
	var $l_uncheckall = "uncheck all";
	var $l_checkall = "check all";
	var $l_logout = "Are you sure you want to log out?";
	var $l_ajaxerror = "There was a problem with the request. Please report this to administrator.";
	var $l_ajaxerror2 = "Cannot create XMLHTTP instance.";	
	var $l_updateerror = "There was an error performing the update.\n\nError Message:\n-----------------------------------\n"; 
	var $l_pleasewait = "Please wait ...";
	var $l_ff = "Netscape 6 or Mozilla is needed!";
	var $l_newreply = "Post Reply";'.(defined('THIS_SCRIPT') && THIS_SCRIPT == 'signup.php' ? '
	var $l_entername = "Please enter your username.";
	var $l_wrongusername = "Minimum/Maximum characters length: ";
	var $l_spacenotallowed = "Sorry, spaces are not allowed. Try again.";
	var $l_passwordtwice = "Please enter your password twice.";
	var $l_wrongpassword1 = "Minimum/Maximum characters length: ";
	var $l_wrongpassword2 = "You did not enter the same new password twice. Please re-enter your password.";
	var $l_wrongpassword3 = "Sorry, password cannot be same as user name. Try again.";
	var $l_wronghintanswer1 = "Minimum hintanswer characters: ";
	var $l_wronghintanswer2 = "Sorry, hintanswer cannot be same as user name. Try again.";
	var $l_wronghintanswer3 = "Sorry, hintanswer cannot be same as password. Try again.";
	var $l_wrongemail = "Please enter a valid email address.";
	' : '').'
	//]]>
</script>';
?>