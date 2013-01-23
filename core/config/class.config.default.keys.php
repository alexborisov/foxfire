<?php

/**
 * FOXFIRE DEFAULT CONFIG CLASS - KEYS
 * Manages creating configuration keys during initial plugin setup, and during a plugin reset
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Config
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_config_defaultKeys {
    
    
	var $config_class;		    // System config class
	
	
	// ================================================================================================================
	
	
	public function __construct($args=null) {

	    
		// Handle dependency-injection for unit tests
		if($args){

			$this->config_class = &$args['config_class'];			
		}
		else {
			global $fox;			
			$this->config_class = &$fox->config;			
		}

	}
	
	
	/**
         * Core Settings
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function set_coreSettings() {
	    
	    
		$keys = array(

		array('tree'=>'system',		'branch'=>'core',	'key'=>'installed',	    'val'=>true,	    'filter'=>'bool',		'ctrl'=>null),
		array('tree'=>'system',		'branch'=>'core',	'key'=>'version',	    'val'=>1900,	    'filter'=>'int',		'ctrl'=>null),
		array('tree'=>'system',		'branch'=>'core',	'key'=>'releaseDate',	    'val'=>'2011-09-21',    'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'system',		'branch'=>'core',	'key'=>'buildName',	    'val'=>'Tesla',	    'filter'=>'textAndNumbers', 'ctrl'=>null),
		array('tree'=>'integration',	'branch'=>'buddypress', 'key'=>'activityStreamID',  'val'=>'fox',	    'filter'=>'textAndNumbers', 'ctrl'=>null)	

		);	    
		
		foreach( $keys as $key ){
	    
			try {
				$this->config_class->addNode('foxfire', $key['tree'], $key['branch'], $key['key'], $key['val'], $key['filter'], $key['ctrl']);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error creating key",
					'data'=>$key,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));		    
			}		
		}
			    	    	    
	}
		
			
	/**
         * Writes all of FoxFire's base configuration keys to the config class. Note that
	 * page, album, media, and network modules independently write their keys to the
	 * config class during setup, but the actions that trigger it are fired from within
	 * this method.
         *
         * @version 1.0
         * @since 1.0
	 *
         * @return bool | Exception on failure. True on success.
         */
    
	public function load() {

	    
		// The cache salt value is very important. If it is deleted, all items in the cache will have
		// to be regenerated, and people running caches with unlimited disk space will end up with a
		// huge number of "dead" files. The code below makes it extremely difficult to accidentally
		// delete the salt value. It will survive all FOX tables being dropped and the plugin reactivated.

		if (!get_site_option( 'FOX_cache_salt' )) {

		    // IMPORTANT - the cache salt value is generated the same way WP generates their salt value,
		    // and WP generates their salt value this way for specific reasons. A single width MD5 is not
		    // strong enough, so two are used. Each MD5 must use its own random seed. It is important each
		    // MD5 uses only ONE random seed, not two of them added or multiplied together. A single random
		    // seed produces a flat distribution ...but random seeds multiplied together produce a *log*
		    // distribution, and random seeds added together prduce a *bell curve* distribution. This would
		    // dramatically reduce the security of the cache salt value.

		    $salt = md5( mt_rand() ) . md5( mt_rand() ); 
		    update_site_option('FOX_cache_salt', $salt);
		}
		
		// Clear the config class settings for this plugin
		// =================================================================

		try {
			$this->config_class->dropPlugin('foxfire');
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error clearing plugin settings",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Write keys to config class
		// =================================================================
		
		try {
			self::set_coreSettings();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in key loader",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));		    
		}
	   

	}

	
	
} // End of class FOX_config_defaultKeys



/**
 * Action function to instantiate the class during setup
 *
 * @version 1.0
 * @since 1.0
 */

function FOX_config_defaultKeys_install(){

	$cls = new FOX_config_defaultKeys();
	$cls->load();
}
add_action( 'fox_setDefaults', 'FOX_config_defaultKeys_install', 2 );


?>