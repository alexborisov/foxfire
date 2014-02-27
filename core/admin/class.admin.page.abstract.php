<?php

/**
 * FOXFIRE ADMIN PAGE BASE CLASS
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

abstract class FOX_admin_page_base {

    
	var $tabs = array();	    // Tabs storage array
	var $intro;		    // Intro block for page
	var $page;		    // Admin page slug
	

	// ============================================================================================================ //


	/**
	 * Processes configuration settings for an admin page tab
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param GLOBAL $_POST | all variables set in the global $_POST array
	 */

	function processor() {

	    global $fox;

	    if ( isset($_POST['updateoption']) ) {

		    check_admin_referer('fox_admin_settings');

		    if( current_user_can('install_plugins') ){

			    // Save options and show user if update was successful
			    $result = $fox->config->processHTMLForm($_POST);

			    if($result){

				?>
				<script type="text/javascript">

				    jQuery(document).ready(function(){

					jQuery.fox_admin_notify.add({

						title: "<?php _e("Settings Updated", "foxfire"); ?>",
						text:  "<?php _e("FoxFire has updated settings for this screen.", "foxfire"); ?>",
						image:  "<?php echo FOX_URL_CORE . '/admin/css/images/fox_admin_notify_success.png' ?>"
					});

				    });

				</script>
				<?php
			    }
			    else {

				?>
				<script type="text/javascript">

				    jQuery(document).ready(function(){

					jQuery.fox_admin_notify.add({

						title: "<?php _e("Update Error", "foxfire"); ?>",
						text:  "<?php _e("Error updating settings for this screen.", "foxfire"); ?>",
						image:  "<?php echo FOX_URL_CORE . '/admin/css/images/fox_admin_notify_fail.png' ?>"
					});

				    });

				</script>
				<?php
			    }

		    }
		    else {
			
			    die("You do not have security clearance to do this");
		    }
		    
	    }

	}


	/**
	 * Renders all tabs on the admin screen "Setup" page
	 *
	 * Prints the jQuery configuration code to the HTML stream. Uses entries in the $tabs array to iterate through
	 * the tab rendering functions inside the class. Each tab rendering function writes HTML for one tab to the
	 * HTML stream, encapsulated for jQuery.
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function renderTabs() {

	    
	    ?>
	    <script type="text/javascript">

		jQuery(document).ready(function(){

		    <?php // Adds row-striping to our tables  ////////////////////////// ?>
		    jQuery('table.form-table tbody tr:even').addClass('alt');

		});


	    </script>


	    <div class="fox_header_small"></div>

	    <div class="tabs_h">

		<?php $this->intro->render(); ?>

		<ul class="ui-tabs-nav">
		    <?php

			$selected_tab = $_GET['tab'];
			
			// Build the navigation tabs
			foreach($this->tabs as $tab_class => $tab_name) {


				// Handle no tab being selected, which happens on the first load of an admin page
				if(!$selected_tab){
					$selected_tab = $tab_class;
					$_GET['tab'] = $tab_class;

				}

				if($tab_class == $selected_tab){

					$class = "ui-state-default ui-tabs-selected ui-state-active";
					$active_tab = new $tab_class();
				}
				else {
					$class = "ui-state-default";
				}


				echo "\n\t\t<li class='" . $class . "'><a href='admin.php?page=$this->page&tab=" . $tab_class . "'>" . $tab_name . "</a></li>";
			}
			unset($tab_class, $tab_name);
		    ?>
		</ul>
		
		<div id='' class='panel'>
			<?php $active_tab->render(); ?>
		</div>

	    </div>

	    <div class="fox_footer"></div>

	<?php
	}


	/**
	 * Enqueues the selected tab's scripts in the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueScripts() {


		$selected_tab = $_GET['tab'];

		// Build the navigation tabs
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


		$selected_tab = $_GET['tab'];

		// Build the navigation tabs
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

	
	
 } // End of abstract class FOX_admin_page_base

?>