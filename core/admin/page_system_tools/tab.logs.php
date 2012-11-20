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

class FOX_tab_logs {


	/**
	 * Renders the "Logs" tab
	 *
	 * This tab rendering function creates a single tab within the admin page that its parent class generates. The tab's form
	 * contains a hidden field called 'page_options'. The class's "processor" function parses the variable names in this field
	 * to determine which POST data fields to load and which objects in the $bp->bpa->options[] global to update.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	function render() {

	    global $fox;

	    $cls = new FOX_log_event();
	    ?>

	    <form name="eventlogsform" method="post" action="<?php echo $this->filepath.'#eventlogs'; ?>">

		<?php wp_nonce_field('fox_admin_settings') ?>

		<div class="fox_tip">
		    <div class="fox_bricks_large"></div>
		    <div class="fox_tip_text">
			<?php _e('This section holds the event logs', "foxfire") ?>
		    </div>
		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Event Log Options',"foxfire") ?> </div>

		    <div class="fox_section_advice">
			<?php _e("This panel displays the options for BuddyPress Media event logs","foxfire") ?>
		    </div>

		    <table class="form-table">

			<tr valign="top">
				<th align="left"></th>
				<td>
				    <input class="button-primary" type="submit" name="update_event_options" value="<?php _e('Update Option Changes') ;?>"/>
				    <input class="button-primary" type="submit" name="add_dummy_event_data" value="<?php _e('Add Dummy Data') ;?>"/>
				    <input class="button-primary" type="submit" name="empty_event_logs" value="<?php _e('Empty Event Logs') ;?>"/>
				</td>
			</tr>

		    </table>
		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Event Logs',"foxfire") ?> </div>

                    <div id="eventlogs" class="form-table" ></div>

		</div>
	    </form>

	    <!-- End Import settings -->

	<?php
	}

	/**
	 * Adds the tab's scripts to the page header
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueScripts() {
            wp_enqueue_script('fox-ext-debug', FOX_URL_LIB .'/ext/ext-all-debug.js');
            wp_enqueue_script('fox-admin-system-events', FOX_URL_CORE .'/admin/page_system_tools/event.js');
	}


	/**
	 * Adds the tab's styles to the page header
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueStyles() {

            wp_enqueue_style( 'fox-ext-css-all', FOX_URL_LIB .'/ext/resources/css/ext-all.css' );
	}

} // End of class FOX_tab_logs

?>