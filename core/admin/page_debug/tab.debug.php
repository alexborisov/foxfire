<?php

/**
 * BP-MEDIA ADMIN PAGE "SYSTEM TOOLS"
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

class BPM_tab_debug {


	/**
	 * Renders the "Debug" tab
	 *
	 * This tab rendering function creates a single tab within the admin page that its parent class generates. The tab's form
	 * contains a hidden field called 'page_options'. The class's "processor" function parses the variable names in this field
	 * to determine which POST data fields to load and which objects in the $bp->bpa->options[] global to update.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	function render() {

		global $bpm;
		
		?>
		<!-- Begin Config settings -->

		<form name="configform" method="POST" action="<?php echo $this->filepath.'#config'; ?>" >

		    <?php wp_nonce_field('bpm_admin_settings') ?>

		    <div class="panel_section">

			<div class="title"><?php _e('Debug Commands',"bp-media") ?> </div>

			<div class="bpm_section_advice">
			    <?php _e("These debug commands are used by our testing team to reset the plugin to a known state before running tests. NEVER run these
				commands without specifically being told to by a member of the BP-Media team. You could seriously damage your BP-Media installation.","bp-media") ?>
			</div>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e("Clear the persistent cache","bp-media"); ?></th>
				<td>
				    <input class="bpm-button" type="submit" name="reset_cache" value="<?php _e('Run Command') ;?>"/>
				</td>
			    </tr>

			</table>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e("Re-install the plugin's database tables","bp-media"); ?></th>
				<td>
				    <input class="bpm-button" type="submit" name="reset_tables" value="<?php _e('Run Command') ;?>"/>
				</td>
			    </tr>

			</table>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Reset config options to default values',"bp-media"); ?></th>
				<td>
				    <input class="bpm-button" type="submit" name="reset_config" value="<?php _e('Run Command') ;?>"/>
				</td>
			    </tr>

			</table>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Delete all uploaded files',"bp-media"); ?></th>
				<td>
				    <input class="bpm-button" type="submit" disabled="disabled" name="delete_content" value="<?php _e('Run Command') ;?>"/>
				</td>
			    </tr>

			</table>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Load plugin with test content',"bp-media"); ?></th>
				<td>
				    <input class="bpm-button" type="submit" disabled="disabled" name="load_content" value="<?php _e('Run Command') ;?>"/>
				</td>
			    </tr>

			</table>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Add plugin menu',"bp-media"); ?></th>
				<td>
				    <input class="bpm-button" type="submit"  name="add_menu" value="<?php _e('Run Command') ;?>"/>
				</td>
			    </tr>

			</table>

		    </div>

	   </form>
	   <!-- End Path settings --!>


	<?php
	}

	/**
	 * Adds the tab's scripts to the page header
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueScripts() {

	}


	/**
	 * Adds the tab's styles to the page header
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueStyles() {

	}
	

} // End of class BPM_tab_debug

?>