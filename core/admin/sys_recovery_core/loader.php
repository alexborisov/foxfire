<?php

/**
 * BP-MEDIA SCREEN RENDERING CLASS "ADMIN-> RECOVERY"
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

class BPM_admin_page_recovery_intro {


	public function render(){

	    ?>

		<div class="bpm_section_intro">

			<div class="icon"><img src="<?php echo BPM_URL_CORE . '/admin/sys_recovery_core/recovery_icon.png' ?>" alt="Core Settings" width="64" height="64" /></div>

			<div class="title">
			    <?php _e('System Recovery',"bp-media") ?>
			</div>

			<div class="details">
			    <?php _e("BP-Media has been successfully installed on your server, but there's still work left to do! ...Once the problems
				listed below have been fixed, BP-Media will activate across your website and BP-Media's screens will appear in your
				admin menu.","bp-media") ?>
			</div>

		</div>

	    <?php
	}

} // End of class BPM_admin_page_recovery_intro


class BPM_admin_page_recovery extends BPM_admin_page_base {


	public function __construct() {

		$this->filepath = admin_url() . 'admin.php?page=' . $_GET['page'];
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

		// If a form is submitted with data, run the processor function (inherited
		// from BPM_admin_page_base)

		if ( !empty($_POST) ) {
			$this->processor();
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
		wp_enqueue_script( 'bpm-jquery-ui-core');
		wp_enqueue_script( 'bpm-jquery-ui-widget');
		wp_enqueue_script( 'bpm-jquery-ui-mouse');
		wp_enqueue_script( 'bpm-jquery-ui-position');
		wp_enqueue_script( 'bpm-jquery-ui-tabs');
		wp_enqueue_script( 'bpm-jquery-ui-slider');
		wp_enqueue_script( 'bpm-jquery-ui-dialog');
		wp_enqueue_script( 'bpm-jquery-ui-draggable');
		wp_enqueue_script( 'bpm-jquery-ui-resizable');


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
		wp_enqueue_style( 'bpm-ui-base', BPM_URL_LIB .'/jquery/ui/css/jquery.ui.base.css', false, '2.8.1', 'screen');
		wp_enqueue_style( 'bpm-ui-theme', BPM_URL_CORE .'/admin/css/bpm.ui.theme.css', false, '2.8.1', 'screen' );
		wp_enqueue_style( 'bpm-core-settings', BPM_URL_CORE .'/admin/css/admin-core-settings.css', false, '2.5.0', 'screen' );


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

		$this->intro = new BPM_admin_page_recovery_intro();
	}


	/**
	 * Loads the tab files for this admin page. The order the tab pages are loaded in sets
	 * the order that they are displayed in within the admin page
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	public function loadTabs(){

		include_once ( dirname (__FILE__) . '/tab.home.php' );
		$this->tabs['BPM_tab_home'] = __('Problems', "bp-media");

	}

 } // End of class BPM_admin_page_recovery

?>