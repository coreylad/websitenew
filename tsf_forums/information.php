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
define('THIS_SCRIPT', 'information.php');
$rootpath = '../';
define('NO_LOGIN_REQUIRED', true);
require($rootpath.'global.php');

define('I_VERSION', '0.3 by xam');

stdhead('Site Information - Forum Rules');
echo '
<table cellpadding="6" cellspacing="1" border="0" width="100%" align="center">
	<tr>
		<td class="thead">Forum Rules</td>
	</tr>
	<tr>
	<td class="alt1">
		<span style="float: right;">
			<script src="http://www.gmodules.com/ig/ifr?url=http://www.google.com/ig/modules/translatemypage.xml&up_source_language=en&w=160&h=60&title=&border=&output=js"></script>
		</span>
		Hello <b>'.($CURUSER['username'] ? $CURUSER['username'] : 'Guest').'</b>. Welcome to <b>'.$SITENAME.'</b>.<br />
		<br />
		Your userid is: '.($CURUSER['id'] ? $CURUSER['id'] : 0).'<br />
		<ul>
			<li>
				<b>Rule No. 1 - Flaming, Bashing, and Trolling</b> <br />
				<font size="1">Hate posts and personal attacks will not be tolerated on '.$SITENAME.'. Treat others on these message boards as you would expect them to treat you. Posting topics specifically designed to provoke a negative response from someone (aka trolling) is also asked to be avoided. There are however some posts that will cause debates that may offend you. If you are easily offended by discussions about race, religion, and other highly opinionated topics we suggest you stay out of those topics.</font></li>
				</ul><ul><li><b>Rule No. 2 - No \'Spamming\'</b><br />
				<font size="1">Purposely spamming a message board with senseless, vacuous, or empty messages to gain a higher post count, or just to annoy others is HIGHLY frowned upon! Commercial spamming and advertising are just as unwelcome, double posting is allowed to an extent. Many users use \'Threaded Mode\' and it is allowed to double post if posting to two different replies.</font>
			</li>
			</ul>
			<ul>
				<li>
					<b>Rule No. 3 - Impersonating Other Users / Accessing Another User(\'s) Account</b><br />
					<font size="1">You may not impersonate another board member or create an account specifically for the purpose of provoking other users. Also, accessing or using someone else\'s account or attempting to access another poster\'s account is strictly prohibited. You will be banned.</font>
				</li>
			</ul>
			<ul>
				<li>
					<b>Rule No. 4 - Piracy, and Warez</b><br />
					<font size="1">Absolutely no warez. Linking or giving information about any site that distributes illegal software, or warez, seeking help to circumvent any copyright laws, or encouraging software or media piracy is grounds for an immediate ban.</font>
				</li>
			</ul>
			<ul>
				<li>
					<b>Rule No. 5 - Nudity/****</b><br />
					<font size="1">Please remember that this board is a \'PUBLIC\' forum. There are 12 year olds here just as there are 50 year olds. Nudity and **** is prohibited for obvious reasons.</font>
				</li>
			</ul>
			<ul>
			<li>
				<b>Rule No. 6 - Copyrighted Material</b><br />
				<font size="1">'.$SITENAME.' users agree not to post any material that is protected by copyright, trademark or other proprietary right without the express permission of the owner(s) of said copyright, trademark or other proprietary right.</font></li>
				</ul><ul><li><b>Rule No. 7 - Report Button</b><br />
				<font size="1">Please note that the report button was made for reporting bad posts only. Please do not abuse this button to get attention to your thread/other threads, or try to get faster support.</font>
			</li>
			</ul>
			<ul>
				<li>
					<b>Rule No. 8 - Requesting Support</b> <br />
					<font size="1">Before asking for support ensure that you read the sticky topics placed throughout the forums. Chances are your query will be anwsered and will save staff and members time in anwsering repeated questions.</font></li>
					</ul><ul><li><b>Rule no. 9 -</b><font size="1"> This forum is seen by younger individuals please do not post pictures with <b>BAD WORDS</b> written on them because the word filter will miss this.</font>
				</li>
			</ul>
			<ul>
				<li>
					<b>Rule no. 10 -</b><font size="1"> This is an English speaking forum, please type your post in <font color="red"><b>English</b></font>. If you don\'t speak English, please check our International Forums.</font>
				</li>
		</ul>
	</td>
	</tr>
</table>
';
stdfoot();
?>