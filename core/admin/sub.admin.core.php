<?php

/**
 * FOXFIRE ADMIN MENUS
 * Handles all plugin screens at the WP admin menu
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Admin
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

require ( dirname( __FILE__ ) . '/class.admin.page.abstract.php' );


class FOX_admin {


	var $page_class;	    // Active page object


	// ============================================================================================================ //


        // The "array(&$this, 'function_name')" construct tells PHP to run "function_name" inside
        // this class, instead of a global function. It's like $this->function_name() in C++

	function FOX_admin($type) {

		self::loadClass();

		if($type == "site"){

			// Add the admin menu
			add_action( 'admin_menu', array (&$this, 'add_menu') );

			// Add the script and style files
			add_action('admin_print_scripts', array(&$this, 'load_scripts') );
			add_action('admin_print_styles', array(&$this, 'load_styles') );

		}
		elseif($type == "network"){

			// Add the admin menu
			add_action( 'network_admin_menu', array (&$this, 'add_menu') );

			// Add the script and style files
			add_action('admin_print_scripts', array(&$this, 'load_scripts') );
			add_action('admin_print_styles', array(&$this, 'load_styles') );

		}
        }


	function loadClass(){


  		switch ($_GET['page']){

			case "fox-dashboard" : {

				 include_once ( dirname (__FILE__) . '/page_dashboard/loader.php');
				 $this->page_class = new FOX_admin_page_dashboard();

			} break;

			case "fox-setup" : {

				 include_once ( dirname (__FILE__) . '/page_core_settings/loader.php');
				 $this->page_class = new FOX_admin_page_core();

			} break;

			case "fox-system" : {

				 include_once ( dirname (__FILE__) . '/page_system_tools/loader.php');
				 $this->page_class = new FOX_admin_page_system();

			} break;

			case "fox-debug" : {

				 include_once ( dirname (__FILE__) . '/page_debug/loader.php');
				 $this->page_class = new FOX_admin_page_debug();

			} break;
		    

		} 
		

	}


	function load_scripts() {

		if( method_exists($this->page_class, 'enqueueScripts') ){
		    $this->page_class->enqueueScripts();
		}

	}


	function load_styles() {

		if( method_exists($this->page_class, 'enqueueStyles') ){
		    $this->page_class->enqueueStyles();
		}

	}


	function show_menu() {

		$this->page_class->render();
	}


	// Add our menus to the WP Admin Menu tree
	function add_menu()  {

		add_menu_page( __( 'FoxFire', "foxfire"),  __( 'FoxFire', "foxfire" ), 'administrator', 'fox-dashboard',
                        array (&$this, 'show_menu'), FOX_URL_CORE .'/admin/images/admin-icon.png'
                );

                add_submenu_page(
                        'fox-dashboard', __( 'Dashboard', "foxfire"), __( 'Dashboard', "foxfire" ),
                        'administrator', 'fox-dashboard', array (&$this, 'show_menu')
                 );

                add_submenu_page(
                        'fox-dashboard', __( 'Core Settings', "foxfire"), __( 'Core Settings', "foxfire" ),
                        'administrator', 'fox-setup', array (&$this, 'show_menu')
                 );

                add_submenu_page(
                        'fox-dashboard', __( 'System Tools', "foxfire"), __( 'System Tools', "foxfire" ),
                        'administrator', 'fox-system', array (&$this, 'show_menu')
                 );

                add_submenu_page(
                        'fox-dashboard', __( 'Debug Tools', "foxfire"), __( 'Debug Tools', "foxfire" ),
                        'administrator', 'fox-debug', array (&$this, 'show_menu')
                 );

		
	}

	/**
	 * Read an array from a remote url
	 *
	 * @param string $url
	 * @return array of the content
	 */

	function get_remote_array($url) {


		if ( function_exists('wp_remote_request') ) {

			$options = array();
			$options['headers'] = array(
				'User-Agent' => 'FoxFire Updater V' . FOX_VERSION . '; (' . home_url() .')'
			 );

			$response = wp_remote_request($url, $options);

			if ( is_wp_error( $response ) )
				return false;

			if ( 200 != $response['response']['code'] )
				return false;

			$content = unserialize($response['body']);

			if (is_array($content))
				return $content;
		}

		return false;
	}

	
	
} // End of class FOX_admin

?>