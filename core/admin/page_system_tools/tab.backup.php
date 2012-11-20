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

class FOX_tab_backup {


	/**
	 * Renders the "Backup" tab
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

		?>
		<!-- Begin Config settings -->

		<form name="configform" method="POST" action="<?php echo $this->filepath.'#config'; ?>" >

		    <?php wp_nonce_field('fox_admin_settings') ?>

		    <?php $fox->config->initKeysArray(); ?>

		    <div class="fox_tip">
			<div class="fox_bricks_large"></div>
			<div class="fox_tip_text">
			    <?php _e("<b>Template Developers:</b> You can make BP Media installations work perfectly with your
				templates every time! Just include a BP Media config file and tell users to load it. For more information, please see the
				<a href='http://code.google.com/p/buddypress-media/wiki/DataExchangeAPI'>Plugin Config Files</a> documentation page.", "foxfire") ?>
			</div>
		    </div>

		    <div class="panel_section">

			<div class="title"><?php _e('Load Settings',"foxfire") ?> </div>

			<div class="fox_section_advice">
			    <?php _e("This option loads plugin configuration values from a file.","foxfire") ?>
			</div>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Select File',"foxfire"); ?></th>
				<td>
				    <input type="file" name="file" id="file"/>
				</td>
			    </tr>

			</table>
		    </div>

		    <div class="panel_section">

			<div class="title"><?php _e('Save Settings',"foxfire") ?> </div>

			<div class="fox_section_advice">
			    <?php _e("This option saves plugin configuration values to a file.","foxfire") ?>
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

		    <div class="panel_section">

			<div class="title"><?php _e('Reset to Defaults',"foxfire") ?> </div>

			<div class="fox_section_advice">
			    <?php _e("This option will reset all configuration values to the defaults set when the plugin was first installed. Although no user content will be lost,
				resetting the configuration values could cause a wide range of side effects throughout your site. Always save a copy of your current configuration
				values before using this option.","foxfire") ?>
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

		<?php $fox->config->printKeysArray(); ?>

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

} // End of class FOX_tab_backup

?>