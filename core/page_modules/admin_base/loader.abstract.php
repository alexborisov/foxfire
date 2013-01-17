<?php

/**
 * RADIENT PAGE MODULE ADMIN PAGE LOADER BASE CLASS
 * Loads page module config page tabs at the admin menu
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package Radient
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


abstract class RAD_PM_loader_base {

    
	var $tabs = array();	    // Tabs storage array
	var $parent_class;	    // Instance of the page module class that owns this admin page

	// ============================================================================================================ //


	public function __construct($parent_class) {

		$this->parent_class = $parent_class;
		$this->filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];

		$this->loadTabs();
		$this->render();
	}

	/**
	 * Renders all tabs on the page modules's admin page
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function render() {

	global $rad;

	$selected_tab = $rad->pageModules->getSelectedTab();
	$selected_module = $rad->pageModules->getSelectedModule();

	?>

	<div class="child_tabs_h">

		<ul class="child_nav">
		    <?php

			// Build the navigation tabs
			foreach($this->tabs as $tab_class => $tab_name) {


				// Handle no tab being selected, which happens on the first load of the module page
				if(!$selected_tab){
					$selected_tab = $tab_class;
				}

				if($tab_class == $selected_tab){

					$class = "tab_selected";
				}
				else {
					$class = "tab_default";
				}


				echo "\n\t\t<li class='$class'><a href='admin.php?page=rad-page-modules&module=$selected_module&tab=$tab_class" . "'>" . $tab_name . "</a></li>";
			}
			unset($tab_class, $tab_name);
		    ?>
		</ul>

		<div class="child_panel">

		    <?php

		    foreach($this->tabs as $tab_class => $tab_name) {

			    if($tab_class == $selected_tab){

				    echo "\n\t<div id='$tab_class'>\n";

				    // Looks for the internal class function, otherwise enable a hook for plugins
				    if ( class_exists($tab_class) ){

					    $tab = new $tab_class($this->parent_class);
					    $tab->render($this->parent_class);
				    }
				    else {
					    do_action( 'rad_tab_render_' . $tab_class );
				    }

				    echo "\n\t</div>";
			    }
		    }
		    unset($tab_class, $tab_name);
		    ?>
		</div>
	    
	</div>

	<?php
	}


 }

?>