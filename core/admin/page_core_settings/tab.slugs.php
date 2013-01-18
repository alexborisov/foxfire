<?php

/**
 * FOXFIRE ADMIN PAGE CLASS "CORE SETTINGS"
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

class FOX_tab_slugs {


	/**
	 * Renders the "Slugs" tab
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
	    <!-- Begin Slug settings -->

	    <form name="slugsform" method="post" action="<?php echo $this->filepath.'#slugs'; ?>">

		<?php wp_nonce_field('fox_admin_settings') ?>

		<?php $fox->config->initNodesArray(); ?>

		<div class="panel_section">

		    <div class="title"><?php _e('Base Slug',"foxfire") ?> </div>

		    <table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Base Slug',"foxfire"); ?></th>
				<td>
				    <input type="text" size="20" maxlength="30"
					   <?php $fox->config->printNodeName("foxfire", "slugs", "base", "base"); ?>
					   <?php $fox->config->printNodeVal("foxfire", "slugs", "base", "base"); ?> />
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Base Slug -> Home',"foxfire"); ?></th>
				<td>
				    <input type="text" size="20" maxlength="30"
					   <?php $fox->config->printNodeName("foxfire", "slugs", "base", "home"); ?>
					   <?php $fox->config->printNodeVal("foxfire", "slugs", "base", "home"); ?> />
				</td>
			    </tr>

		    </table>
		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Albums',"foxfire") ?> </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Home',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printNodeName("foxfire", "slugs", "albums", "home"); ?>
				       <?php $fox->config->printNodeVal("foxfire", "slugs", "albums", "home"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Create',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printNodeName("foxfire", "slugs", "albums", "create"); ?>
				       <?php $fox->config->printNodeVal("foxfire", "slugs", "albums", "create"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Edit',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printNodeName("foxfire", "slugs", "albums", "edit"); ?>
				       <?php $fox->config->printNodeVal("foxfire", "slugs", "albums", "edit"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Items',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printNodeName("foxfire", "slugs", "albums", "items"); ?>
				       <?php $fox->config->printNodeVal("foxfire", "slugs", "albums", "items"); ?> />
			    </td>
			</tr>

		    </table>

		</div>

		<?php $fox->config->printNodesArray(); ?>

		<div class="fox_submit_h_panel_wrap">
		    <div class="submit"><input class="fox-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
		</div>

	    </form>
	    <!-- End Slug settings -->
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


} // End of class FOX_tab_slugs

?>