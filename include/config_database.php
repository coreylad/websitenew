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
|	IF YOU GET ANY ERRORS WHILE ATTEMPTING TO CONNECT TO MYSQL,
|	PLEASE ENSURE THAT YOU HAVE ENTERED CORRECT DETAILS IN THIS FILE.
+---------------------------------------------------------------------------
*/
# ****** MASTER DATABASE SERVER NAME ******
# This is the hostname or IP address of the MySQL Database Server.
# If you are unsure of what to put here, leave the default values.
define('MYSQL_HOST', 'localhost');

# ****** MASTER DATABASE USERNAME & PASSWORD ******
# This is the username and password you use to access MySQL.
# These must be obtained through your webhost.
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');

#	****** DATABASE NAME ******
#	This is the name of the MySQL database where your TS SE Script will be located.
#	This must be created by your webhost.
define('MYSQL_DB', '');

#	****** DATABASE CHARSET ******
# If you need to set the default connection charset because your database
# is using a charset other than latin1, you can set the charset here.
# If you don't set the charset to be the same as your database, you
# may receive collation errors.  Ignore this setting unless you
# are sure you need to use it.
# Example: define('MYSQL_CHARSET', 'utf8');
define('MYSQL_CHARSET', '');
?>