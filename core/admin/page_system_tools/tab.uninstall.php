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

class BPM_tab_uninstall {


	/**
	 * Renders the "Uninstall" tab
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
		<!-- Begin Uninstall settings -->

		<form name="configform" method="POST" action="<?php echo $this->filepath.'#config'; ?>" >

		    <?php wp_nonce_field('bpm_admin_settings') ?>

		    <?php $bpm->config->initKeysArray(); ?>

		    <div class="bpm_tip">
			<div class="bpm_bricks_large"></div>
			<div class="bpm_tip_text">
			    <?php _e(" Page outlining how a user would go about completely uninstalling Bp-Media from their server.", "bp-media") ?>
			</div>
		    </div>

		    <div class="panel_section">

			<div class="title"><?php _e('Uninstall',"bp-media") ?> </div>

			<div class="bpm_section_advice">
			    <?php _e("This will completely uninstall Bp-Media from the server","bp-media") ?>
			</div>

			<table class="form-table">

				<tr valign="top">
				    <th align="left"><?php _e('Button Goes Here',"bp-media"); ?></th>
				    <td>
					<p></p>
				    </td>
				</tr>
			</table>
		    </div>

		<?php $bpm->config->printKeysArray(); ?>

	       <div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>

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


} // End of class BPM_tab_uninstall

?>