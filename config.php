<?php

// Web-Application URL
define('ROOT_URL', 'http://licorne.be/');  // SERVER URL ( ATTENTION: *WITH* ENDING '/')
// define('ROOT_URL', 'http://10.0.0.10/');  // SERVER URL ( ATTENTION: *WITH* ENDING '/')
define('SUBDIR_URL', '');	// SUBDIRECTORY URL (ATTENTION: *WITH* ENDING '/'. Leave empty if no subdirectory is used)
define('BASE_URL', ROOT_URL.SUBDIR_URL);

// Shoud we use https ?
define('USE_HTTPS', false);

// Database configuration
define('DATABASE_TYPE', 	'mysql' );
define('DATABASE_SERVER',	'localhost' );
define('DATABASE_NAME',		'brevets' );
define('DATABASE_USERNAME',	'brevets' );
define('DATABASE_PASSWORD',	'VtoDbJxjCy6pJLW2' );
define('DATABASE_PREFIX',	'veillee_' );

// Cookies configuration
define('COOKIE_NAME', 'BrevetsGameAuth');
define('COOKIE_TIME', 3600*24*30);	// 30 Days max

// Max login attempts
define('LOGIN_MAX_ATTEMPTS', 10);		// Max number of login attempts
define('LOGIN_DELAY_ATTEMPTS', 15); 	// Delay before next tentative if max number of login attempts is reached

// Is Production or dev active ?
define('DEV_ACTIVE', true);
?>