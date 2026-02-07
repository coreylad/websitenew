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
Translation by xam Version: 0.5

*/

if(!defined('IN_TRACKER'))
  die('Hacking attempt!');

// login.php, takelogin.php
$language['login'] = array 
(
	'head'					=>'Login',
	'loginfirst'			=>'Unfortunately, the page you tried to view <b>can only be used when you\'re logged in</b>. You will be redirected after a successful login.',
	'error1'				=>'ERROR: Incorrect username or password! Please try again or recover your password by clicking <a $href = "recover.php">here</a>.<br />You have <b>{1}</b> remaining tries.',	
	'info'					=>'<p><b>Note</b>: You need cookies enabled to log in.<br /> [<b>{1}</b>] failed logins in a row will result in banning your ip!</p>',
	'username'		=>'Username:',
	'password'			=>'Password:',
	'logout15'			=>'Log me out after 15 minutes inactivity',
	'login'					=>'LOGIN',
	'reset'					=>'RESET',
	'footer'				=>'<center><br /><p>Don\'t have an account? Click <a $href = "signup.php"><b>HERE</b></a> to register your <a $href = "signup.php"><b>FREE</b></a> account!<br /><br />Forget your password? Recover your password <a $href = "recover.php"><b>via email</b></a> or <a $href = "recoverhint.php"><b>via question</b></a>.<br /><br />Haven\'t received the Activation Code? Click <a $href = "'.$_SERVER['SCRIPT_NAME'].'?do=activation_code"><b>here</b></a>.<br /><br />Have a Question? <a $href = "contactus.php"><b>Contact Us</b></a>.</p></center>',
	'banned'				=>'This account has been disabled.',
	'pending'			=>'Please activate your account first!',
	'logged'				=>'You have succesfully logged in...',
	'resend'				=>'Resend Activation Code', // Added v3.9
	'resend2'			=>'Type the email address that corresponds to your {1} account.', // Added v3.9
	'resend3'			=>'Resend', // Added v3.9
	'resend4'			=>'The email you specified is invalid, because it was not found in our database.', // Added v3.9
);
?>