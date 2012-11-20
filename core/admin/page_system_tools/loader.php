<?php

/**
 * BP-MEDIA ADMIN PAGE "SYSTEM TOOLS"
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
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

class FOX_admin_page_systemTools_intro {


	public function render(){

	    ?>

		<div class="fox_section_intro">

			<div class="icon"><img src="<?php echo FOX_URL_CORE . '/admin/page_system_tools/system_tools_icon.png' ?>" alt="System Tools" width="64" height="64" /></div>

			<div class="title">
			    <?php _e('System Tools',"foxfire") ?>
			</div>

			<div class="details">
			    <?php _e("Settings on these pages control the operation of the plugin core. For more information,
				have a look at the <a href='http://code.google.com/p/buddypress-media/wiki/PLUGIN_systemToolsSettings'>System Tools</a> wiki page.","foxfire") ?>
			</div>

		</div>

	    <?php
	}

} // End of class FOX_admin_page_systemTools_intro


class FOX_admin_page_system extends FOX_admin_page_base {


	public function __construct() {

		// Same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
		$this->filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];
		$this->page = $_GET['page'];

		$this->loadTabs();

	}


	/**
	 * Renders the page
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	function render(){

		// If a form is submitted with data, run the processor function
		// =============================================================

		if( !empty($_POST) ) {

			check_admin_referer('fox_admin_settings');


			if( isset($_POST['update_event_options']) ){

			    if(is_int(isset($_POST['event_logs_per_page']))) {
				$event_logs_per_page = $_POST['event_logs_per_page'];
			    }
			}
			elseif( isset($_POST['add_dummy_event_data']) ){

			    global $fox;

			    $cls = new FOX_log_event();

			    $tree_max = 3;
			    $branch_max = 3;
			    $node_max = 3;

			    for($t = 1; $t < $tree_max; $t++)
			    {
				for ($b=1; $b <= $branch_max; $b++)
				{
				    for ($n=1; $n <=$node_max; $n++)
				    {
					$user_id = rand(1,3);
					$date = rand(time(),date("U", PHP_INT_MAX) );
					$level = rand(1,5);
					$summary = $t.' '.$b.' '.$n.' '.$user_id.' '.$level.' '.$date;
					$data = 'Tree: '.$t.' Branch: '.$b.' Node: '.$n.' UserID: '.$user_id.' Level: '.$level.' Date: '.$date.' Summary: '.$summary;
					$add_result = $cls->add(array("tree"=>$t, "branch"=>$b,"node"=> $n,"user_id"=> $user_id,"level"=> $level,"date"=> $date,"summary"=> $summary,"data"=> $data));

				    }
				}
			    }

			}
			elseif( isset($_POST['empty_event_logs']) ){

			    global $fox;

			    $cls = new FOX_log_event();

			    $cls->truncate();
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
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueStyles() {


		// Load styles used by all tabs on this page
		// ======================================================

		wp_enqueue_style( 'fox-admin', FOX_URL_CORE .'/admin/css/fox.admin.css', false, '2.8.1', 'screen' );
		wp_enqueue_style( 'fox-tabs-h', FOX_URL_CORE .'/admin/css/fox.tabs.h.css', false, '2.5.0', 'screen' );


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

		$this->intro = new FOX_admin_page_systemTools_intro();
	}


	/**
	 * Loads the tab files for this admin page. The order the tab pages are loaded in sets
	 * the order that they are displayed in within the admin page
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	public function loadTabs(){

		include_once ( dirname (__FILE__) . '/tab.logs.php' );
		$this->tabs['FOX_tab_logs'] = __('Event Logs', "foxfire");

		include_once ( dirname (__FILE__) . '/tab.backup.php' );
		$this->tabs['FOX_tab_backup'] = __('Backup & Restore', "foxfire");

		include_once ( dirname (__FILE__) . '/tab.import.php' );
		$this->tabs['FOX_tab_import'] = __('Import & Export', "foxfire");

		include_once ( dirname (__FILE__) . '/tab.uninstall.php' );
		$this->tabs['FOX_tab_uninstall'] = __('Uninstall', "foxfire");

		include_once ( dirname (__FILE__) . '/tab.server.php' );
		$this->tabs['FOX_tab_server'] = __('Server Info', "foxfire");

	}

 } // End of class FOX_admin_page_systemTools_intro

?>