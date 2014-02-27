<?php

/**
 * FOXFIRE ADMIN RECOVERY CORE
 * Brings-up the admin recovery menus in the event of a total core failure
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

		include_once ( dirname (__FILE__) . '/sys_recovery_core/loader.php');
		$this->page_class = new FOX_admin_page_recovery();

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
                        'fox-dashboard', __( 'System Recovery', "foxfire"), __( 'System Recovery', "foxfire" ),
                        'administrator', 'fox-dashboard', array (&$this, 'show_menu')
                );

	}



} // End of class FOX_admin

?>