<?php

/**
 * FOXFIRE ADMIN PAGE "DEBUG"
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Admin
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

// Prevent hackers from directly calling this page
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

// ============================================================================================================ //

class FOX_admin_page_debug_intro {


	public function render(){

	    ?>

		<div class="fox_section_intro">

			<div class="icon"><img src="<?php echo FOX_URL_CORE . '/admin/page_debug/debug_tools_icon.png' ?>" alt="System Tools" width="64" height="64" /></div>

			<div class="title">
			    <?php _e('Debug Tools',"foxfire") ?>
			</div>

			<div class="details">
			    <?php _e("These admin screens are used to reset the server to a known state during debugging, and to test FoxFire's server-side and JavaScript code libraries. For more
				info see the <a href='https://github.com/FoxFirewiki/PLUGIN_debug'>Debug Tools</a> wiki page.","foxfire") ?>
			</div>

		</div>

	    <?php
	}

} // End of class FOX_admin_page_debug_intro


class FOX_admin_page_debug extends FOX_admin_page_base {


	public function __construct() {

		// Same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
		$this->filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];
		$this->page = $_GET['page'];

		$this->loadTabs();
	}


	/**
	 * Renders the page and
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	function render(){


		// If a form is submitted with data, run the processor function
		// =============================================================

		if( !empty($_POST) ) {

			check_admin_referer('fox_admin_settings');

			if( isset($_POST['reset_cache']) ){

				global $fox;
				$fox->mCache->flushAll();

			}
			elseif( isset($_POST['reset_tables']) ){

				global $fox;
				$fox->mCache->flushAll();

				do_action('fox_uninstall');
				do_action('fox_install');

			}
			elseif( isset($_POST['reset_config']) ){

				// Its *very* important to reset the cache. If we fail to do this the default settings functions
				// will find data in the cache but which isn't in the db (because we truncated the tables),
				// try to operate as if the data is still in the db, and as a result, crash.

				global $fox;
				$fox->mCache->flushAll();

				$reset_ok = FOX_config_defaultKeys_install($defaults_error);

				if(!$reset_ok){

				    echo "\nError resetting defaults\n";
				    FOX_Debug::dump($defaults_error);

				}

			}
			else {

				$this->processor();
			}

		}

		$this->loadIntro();
		$this->renderTabs();
	}


	/**
	 * Enqueues the selected tab's scripts in the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueScripts() {


		// Load scripts used by all tabs on this page
		// ======================================================

		wp_enqueue_script( 'fox-adminNotifier');


		// Load scripts used by the currently selected tab
		// ======================================================

		$selected_tab = $_GET['tab'];

		foreach($this->tabs as $tab_class => $tab_name) {

			// Handle no tab being selected, which happens on the first load of an admin page
			if(!$selected_tab){
				$selected_tab = $tab_class;
				$_GET['tab'] = $tab_class;

			}

			if($tab_class == $selected_tab){

				$cls = new $tab_class();
				$cls->enqueueScripts();
			}

		}
		unset($tab_class, $tab_name);

	}


	/**
	 * Enqueues the selected tab's styles in the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueStyles() {


		// Load styles used by all tabs on this page
		// ======================================================

		wp_enqueue_style( 'fox-admin', FOX_URL_CORE .'/admin/css/fox.admin.css', false, '2.8.1', 'screen' );
		wp_enqueue_style( 'fox-tabs-h', FOX_URL_CORE .'/admin/css/fox.tabs.h.css', false, '2.5.0', 'screen' );
		wp_enqueue_style( 'fox-unit-tests', FOX_URL_CORE .'/admin/css/fox.unit.tests.css', false, '2.5.0', 'screen' );


		// Load styles used by the currently selected tab
		// ======================================================

		$selected_tab = $_GET['tab'];

		foreach($this->tabs as $tab_class => $tab_name) {

			// Handle no tab being selected, which happens on the first load of an admin page
			if(!$selected_tab){
				$selected_tab = $tab_class;
				$_GET['tab'] = $tab_class;
			}

			if($tab_class == $selected_tab){

				$cls = new $tab_class();
				$cls->enqueueStyles();
			}

		}
		unset($tab_class, $tab_name);

	}


	/**
	 * Loads the intro block for this admin page
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	public function loadIntro(){

		$this->intro = new FOX_admin_page_debug_intro();
	}


	/**
	 * Loads the tab files for this admin page. The order the tab pages are loaded in sets
	 * the order that they are displayed in within the admin page
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	public function loadTabs(){

		include_once ( dirname (__FILE__) . '/tab.debug.php' );
		$this->tabs['FOX_tab_debug'] = __('Debug', "foxfire");

		include_once ( dirname (__FILE__) . '/tab.server.php' );
		$this->tabs['FOX_tab_server'] = __('Server', "foxfire");
		
		include_once ( dirname (__FILE__) . '/tab.server.selftest.php' );
		$this->tabs['FOX_tab_server_selftest'] = __('Unit Tests', "foxfire");

	}

 } // End of class FOX_admin_page_debug_intro

?>