<?php
/*
Plugin Name: 24seven API
Plugin URI: 
Description: 
Author: Matrosov Stanislav
Version: 1.0.0
*/


define('SEVEN24_URL', __DIR__);
/*define('SEVEN24_APP_ID', '8dd85a11-4369-4c9e-86fa-f921eddd3710');
define('SEVEN24_APP_LOGIN', 'conrad@bluesystems.no');
define('SEVEN24_APP_PASSWORD', 'Upworks!!');*/

// https://github.com/tormjens/24SevenOffice
include( 'classes/class.24sevenoffice.php' );
include( 'classes/class.main.php' );


include( 'inc/events.php' );
include( 'inc/functions.php' );
include( 'inc/admin.php' );

include( 'inc/encrypt.php' );

 