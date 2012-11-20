<?php

/**
 * BP-MEDIA ADMIN PAGE "DEBUG"
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Admin
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

// Prevent hackers from directly calling this page
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

// ============================================================================================================ //

class BPM_admin_page_debug_intro {


	public function render(){

	    ?>

		<div class="bpm_section_intro">

			<div class="icon"><img src="<?php echo BPM_URL_CORE . '/admin/page_debug/debug_tools_icon.png' ?>" alt="System Tools" width="64" height="64" /></div>

			<div class="title">
			    <?php _e('Debug Tools',"bp-media") ?>
			</div>

			<div class="details">
			    <?php _e("These admin screens are used to reset the server to a known state during debugging, and to test BP-Media's server-side and JavaScript code libraries. For more
				info see the <a href='http://code.google.com/p/buddypress-media/wiki/PLUGIN_debug'>Debug Tools</a> wiki page.","bp-media") ?>
			</div>

		</div>

	    <?php
	}

} // End of class BPM_admin_page_debug_intro


class BPM_admin_page_debug extends BPM_admin_page_base {


	public function __construct() {

		// Same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
		$this->filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];
		$this->page = $_GET['page'];

		$this->loadTabs();
	}


	/**
	 * Renders the page and
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	function render(){


		// If a form is submitted with data, run the processor function
		// =============================================================

		if( !empty($_POST) ) {

			check_admin_referer('bpm_admin_settings');

			if( isset($_POST['reset_cache']) ){

				global $bpm;
				$bpm->cache->flushAll();

			}
			elseif( isset($_POST['reset_tables']) ){

				global $bpm;
				$bpm->cache->flushAll();

				do_action('bpm_uninstall');
				do_action('bpm_install');

			}
			elseif( isset($_POST['reset_config']) ){

				// Its *very* important to reset the cache. If we fail to do this the default settings functions
				// will find data in the cache but which isn't in the db (because we truncated the tables),
				// try to operate as if the data is still in the db, and as a result, crash.

				global $bpm;
				$bpm->cache->flushAll();

				$reset_ok = bpm_defaultsInstall($defaults_error);

				if(!$reset_ok){

				    echo "\nError resetting defaults\n";
				    BPM_Debug::dump($defaults_error);

				}

			}
			elseif( isset($_POST['load_content']) ){


			}
			elseif( isset($_POST['delete_content']) ){


			}
			elseif( isset($_POST['add_menu']) ){


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
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueScripts() {


		// Load scripts used by all tabs on this page
		// ======================================================

		wp_enqueue_script( 'bpm-adminNotifier');


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
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueStyles() {


		// Load styles used by all tabs on this page
		// ======================================================

		wp_enqueue_style( 'bpm-admin', BPM_URL_CORE .'/admin/css/bpm.admin.css', false, '2.8.1', 'screen' );
		wp_enqueue_style( 'bpm-tabs-h', BPM_URL_CORE .'/admin/css/bpm.tabs.h.css', false, '2.5.0', 'screen' );
		wp_enqueue_style( 'bpm-unit-tests', BPM_URL_CORE .'/admin/css/bpm.unit.tests.css', false, '2.5.0', 'screen' );


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
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	public function loadIntro(){

		$this->intro = new BPM_admin_page_debug_intro();
	}


	/**
	 * Loads the tab files for this admin page. The order the tab pages are loaded in sets
	 * the order that they are displayed in within the admin page
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	public function loadTabs(){

		include_once ( dirname (__FILE__) . '/tab.debug.php' );
		$this->tabs['BPM_tab_debug'] = __('Debug', "bp-media");

		include_once ( dirname (__FILE__) . '/tab.server.selftest.php' );
		$this->tabs['BPM_tab_server_selftest'] = __('Server', "bp-media");

		include_once ( dirname (__FILE__) . '/tab.ajax.selftest.php' );
		$this->tabs['BPM_tab_ajax_selftest'] = __('AJAX', "bp-media");

	}

 } // End of class BPM_admin_page_debug_intro

?>