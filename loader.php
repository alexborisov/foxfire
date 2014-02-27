<?php
/*
Plugin Name: FoxFire
Plugin URI: https://github.com/FoxFire
Description: A powerful DataStore and Caching Framework for WordPress
Version: 1.0
Revision Date: November 20, 2012
Requires at least: WP 3.2.1, BP 1.5, PHP 5.3
Tested up to: WP 3.5-alpha, PHP 5.4.4
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: The FoxFire Team
Author URI: https://github.com/FoxFire
Site Wide Only: True
Network: False
Text Domain: foxfire
Domain Path: /core/languages/
*/


// FoxFire isn't dependent on any other plugins, however plugins that depend on FoxFire
// need a chance to attach their loader functions to the 'fox_coreReady' before we load
// the FoxFire core and fire it. So we don't load the FoxFire core until the 'plugins_loaded'
// action has fired


function fox_loadCore() {

	require( dirname( __FILE__ ) . '/core/foxfire.core.wp.php' );
}
add_action( 'plugins_loaded', 'fox_loadCore', 10 );


?>