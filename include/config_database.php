<?php

declare(strict_types=1);

/*
+--------------------------------------------------------------------------
|   TS Special Edition v.10.0 - PHP 8.5+ Modernized
|   ========================================
|   Database Configuration File
|   Modernized with strict types and PDO support
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

# ****** DATABASE NAME ******
# This is the name of the MySQL database where your TS SE Script will be located.
# This must be created by your webhost.
define('MYSQL_DB', '');

# ****** DATABASE CHARSET ******
# UTF-8 is recommended for modern databases
# Set to utf8mb4 for full Unicode support (emoji, etc.)
define('MYSQL_CHARSET', 'utf8mb4');