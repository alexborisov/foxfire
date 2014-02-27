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

class FOX_tab_uninstall {


	/**
	 * Renders the "Uninstall" tab
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
		<!-- Begin Uninstall settings -->

		<form name="configform" method="POST" action="<?php echo $this->filepath.'#config'; ?>" >

		    <?php wp_nonce_field('fox_admin_settings') ?>

		    <?php $fox->config->initNodesArray(); ?>

		    <div class="fox_tip">
			<div class="fox_bricks_large"></div>
			<div class="fox_tip_text">
			    <?php _e(" Page outlining how a user would go about completely uninstalling Bp-Media from their server.", "foxfire") ?>
			</div>
		    </div>

		    <div class="panel_section">

			<div class="title"><?php _e('Uninstall',"foxfire") ?> </div>

			<div class="fox_section_advice">
			    <?php _e("This will completely uninstall Bp-Media from the server","foxfire") ?>
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

		<?php $fox->config->printNodesArray(); ?>

	       <div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>

	   </form>
	   <!-- End Path settings --!>

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


} // End of class FOX_tab_uninstall

?>