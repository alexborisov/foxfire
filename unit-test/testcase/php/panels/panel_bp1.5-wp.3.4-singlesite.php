<?php

/**
 * FOXFIRE TEST PANEL - BP 1.5 | WP 3.4 | SINGLE SITE MODE
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Test Panel
 * @license GPL v2.0
 *
 * ========================================================================================================
 */


class panel_FOX_bp15_wp34_singleSite extends RAZ_testPanel_base {

    	
	public function __construct() {

		$this->slug = "panel_FOX_bp15_wp34_singleSite";
		$this->name = "BP 1.5 | WP 3.4 | SINGLE SITE MODE";   
		
	}	
	
	
	/**
	 * This method is used to add globals and defines that would normally be set in 'wp-config.php'
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function setupGlobals() {

	    
		define('DB_CHARSET', 'utf8');
		define('DB_COLLATE', '');

		// The keys and salts defined below have to be the ones that were set in 'wp-config.php' on 
		// the WP installation used to create the database image file loaded by setupDB()
		
		define('AUTH_KEY',         'ug|O,+Tnh-p+(dd0w:,=mOYtesp[a3:8IevPA/fxp$)q02[ttMN2Q=P-M4OB}W@J');
		define('SECURE_AUTH_KEY',  '*PJy_vCaQ(wE&1[z3B|O0+%_G#V-qyl%iJ0a,gjZPgo9ndM2M?;I2nk[?;b+/P|I');
		define('LOGGED_IN_KEY',    '?t%o+ @l41.3FCOYBV|]O`_pIM@K}q-f;|bvJfB95;Do}0.o*IUPOK04@DB,f&nJ');
		define('NONCE_KEY',        'P_vG_Bn<GR?NM]Xy0 qME,a38pA1?b|RF,Rh+~uY Nn/&/3^$GRI|.Jqw0<xC.sS');
		define('AUTH_SALT',        'pJ(EvMRv9 kNn|,Ox,*eOq`N3DsIv8l*!A_ciI7h[[7E${~n/feBk/iBfImXC-BB');
		define('SECURE_AUTH_SALT', 'QHXYK$hI4X}7)M4@W#KHE+T+VsoNCA!d&h*GddH|ESh+Ot-{<l?V=E,]KL#j-Ym,');
		define('LOGGED_IN_SALT',   '3Ud>[wqQp$s:tKwA6(gJrb}^H;k-SO%-DZ`WeP:IJ,[ mz$M%k#yjG-40wt mQ[,');
		define('NONCE_SALT',       'QR!Wk0dL[%qLxK-hB$ude%7I5We=%XwZT|+4|NHw+kDCi+u;o+^T`N+hC4)d*EQ9');	
		
		define('WPLANG', '');
		define('WP_DEBUG', false);		
	}
	
	
	/**
	 * Move assets (such as plugin files) into place before the test runner spins-up
	 * the WordPress installation
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function setupAssets() {

	}
	
	
	/**
	 * Set up db image and remapping options
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function setupDB() {
		
		global $razor;
	    
		// Enable SQL image load and set path
		$razor->load_db_image = true;
		$razor->db_image_file = $razor->path_testplan . '/db_images/bp-1.5.0_wp-3.3.0.sql';
		
		// Enable plugin folder remap and set remapper function
		$razor->remap_folder = true;
		require_once($razor->path_testplan . '/db_images/bp-1.5.0_wp-3.3.0.php');
		$razor->folder_remap_function = "fox_remap_bp15wp33";
		
	}
	
	
	/**
	 * Perform any setup tasks that have to be completed after the DB image has been loaded
	 * but before the test groups are run
	 *
	 * @version 1.0
	 * @since 1.0
	 */	
	function setupState() {
	    
	}
	
	
	/**
	 * Cleanup assets after completion of the test panel
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function tearDownAssets() {

	}			
	
}

$cls = new panel_FOX_bp15_wp34_singleSite();

global $razor;
$razor->registerTestPanel($cls);

?>