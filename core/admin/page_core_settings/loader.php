<?php

/**
 * FOXFIRE SCREEN RENDERING CLASS "ADMIN-> CORE SETTINGS"
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

class FOX_admin_page_core_intro {


	public function render(){

	    ?>

		<div class="fox_section_intro">

			<div class="icon"><img src="<?php echo FOX_URL_CORE . '/admin/page_core_settings/core_icon.png' ?>" alt="Core Settings" width="64" height="64" /></div>

			<div class="title">
			    <?php _e('Core Settings',"foxfire") ?>
			</div>

			<div class="details">
			    <?php _e("Settings on these pages control the operation of the plugin core. For more information,
				have a look at the <a href='http://code.google.com/p/buddypress-media/wiki/PLUGIN_coreSettings'>Core Settings</a> wiki page.","foxfire") ?>
			</div>

		</div>

	    <?php
	}

} // End of class FOX_admin_page_core_intro


class FOX_admin_page_core extends FOX_admin_page_base {


	public function __construct() {

		$this->filepath = admin_url() . 'admin.php?page=' . $_GET['page'];
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

		// If a form is submitted with data, run the processor function (inherited
		// from FOX_admin_page_base)

		if ( !empty($_POST) ) {
			$this->processor();
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
		wp_enqueue_script( 'fox-jquery-ui-core');
		wp_enqueue_script( 'fox-jquery-ui-widget');
		wp_enqueue_script( 'fox-jquery-ui-mouse');
		wp_enqueue_script( 'fox-jquery-ui-position');
		wp_enqueue_script( 'fox-jquery-ui-tabs');
		wp_enqueue_script( 'fox-jquery-ui-slider');
		wp_enqueue_script( 'fox-jquery-ui-dialog');
		wp_enqueue_script( 'fox-jquery-ui-draggable');
		wp_enqueue_script( 'fox-jquery-ui-resizable');


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
		wp_enqueue_style( 'fox-ui-base', FOX_URL_LIB .'/jquery/ui/css/jquery.ui.base.css', false, '2.8.1', 'screen');
		wp_enqueue_style( 'fox-ui-theme', FOX_URL_CORE .'/admin/css/fox.ui.theme.css', false, '2.8.1', 'screen' );
		wp_enqueue_style( 'fox-core-settings', FOX_URL_CORE .'/admin/css/admin-core-settings.css', false, '2.5.0', 'screen' );


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

		$this->intro = new FOX_admin_page_core_intro();
	}


	/**
	 * Loads the tab files for this admin page. The order the tab pages are loaded in sets
	 * the order that they are displayed in within the admin page
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	public function loadTabs(){

		include_once ( dirname (__FILE__) . '/tab.slugs.php' );
		$this->tabs['FOX_tab_slugs'] = __('Page Slugs', "foxfire");

		include_once ( dirname (__FILE__) . '/tab.templates.php' );
		$this->tabs['FOX_tab_templates'] = __('Templates', "foxfire");

		include_once ( dirname (__FILE__) . '/tab.addmedia.php' );
		$this->tabs['FOX_tab_addmedia'] = __('Adding Media', "foxfire");

	}

 } // End of class FOX_admin_page_core

?>