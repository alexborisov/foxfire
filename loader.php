<?php

/**
 * Plugin Name: FoxFire
 * Plugin URI:  https://github.com/foxly/foxfire
 * Description: A powerful DataStore and Caching Framework for WordPress
 * Author:      Foxly & Friends
 * Author URI:  https://github.com/foxly
 * Version:     2.0
 * Text Domain: foxfire
 * Domain Path: /core/languages/
 * License:     GPLv2 or later (license.txt)
 */

// FoxFire isn't dependent on any other plugins, however plugins that depend on FoxFire
// need a chance to attach their loader functions to the 'fox_coreReady' before we load
// the FoxFire core and fire it. So we don't load the FoxFire core until the 'plugins_loaded'
// action has fired


function fox_loadCore() {

	require( dirname( __FILE__ ) . '/core/foxfire.core.php' );
}
add_action( 'plugins_loaded', 'fox_loadCore', 10 );


?>