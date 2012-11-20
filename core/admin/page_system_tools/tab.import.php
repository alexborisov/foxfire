<?php

/**
 * FOXFIRE ADMIN PAGE "SYSTEM TOOLS"
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

class FOX_tab_import {


	/**
	 * Renders the "Imports" tab
	 *
	 * This tab rendering function creates a single tab within the admin page that its parent class generates. The tab's form
	 * contains a hidden field called 'page_options'. The class's "processor" function parses the variable names in this field
	 * to determine which POST data fields to load and which objects in the $bp->bpa->options[] global to update.
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	function render() {

	    global $fox;

	    ?>
	    <!-- Begin Import settings -->

	    <form name="importform" method="post" action="<?php echo $this->filepath.'#import'; ?>">

		<?php wp_nonce_field('fox_admin_settings') ?>

		<div class="fox_tip">
		    <div class="fox_bricks_large"></div>
		    <div class="fox_tip_text">
			<?php _e('<b>PLEASE NOTE: </b>Importing data is a complicated process which might require configuration changes to your web server and/or access to the
			    command prompt. Please review the <a href="http://code.google.com/p/buddypress-media/wiki/ImportingData">Data Import Documentation</a>
			    before attempting to import large installations.', "foxfire") ?>
		    </div>
		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('BP Album 0.1.8',"foxfire") ?> </div>

		    <div class="fox_section_advice">
			<?php _e("This module fully upgrades a BP Album 0.1.8 installation to FoxFire 0.1.9","foxfire") ?>
		    </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Button Goes Here',"foxfire"); ?></th>
			    <td>
				<p></p>
			    </td>
			</tr>

		    </table>
		</div>

		<?php //$fox->config->printKeysArray(); ?>

		<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>

	    </form>

	    <!-- End Import settings -->

	<?php
	}

	/**
	 * Adds the tab's scripts to the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueScripts() {

	}


	/**
	 * Adds the tab's styles to the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueStyles() {

	}

} // End of class FOX_tab_import

?>