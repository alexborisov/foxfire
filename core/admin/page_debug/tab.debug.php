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

class FOX_tab_debug {


	/**
	 * Renders the "Debug" tab
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
		<!-- Begin Config settings -->

		<form name="configform" method="POST" action="<?php echo $this->filepath.'#config'; ?>" >

		    <?php wp_nonce_field('fox_admin_settings') ?>

		    <div class="panel_section">

			<div class="title"><?php _e('Debug Commands',"foxfire") ?> </div>

			<div class="fox_section_advice">
			    <?php _e("These debug commands are used by our testing team to reset the plugin to a known state before running tests. NEVER run these
				commands without specifically being told to by a member of the FoxFire team. You could seriously damage your FoxFire installation.","foxfire") ?>
			</div>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e("Clear the persistent cache","foxfire"); ?></th>
				<td>
				    <input class="fox-button" type="submit" name="reset_cache" value="<?php _e('Run Command') ;?>"/>
				</td>
			    </tr>

			</table>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e("Re-install the plugin's database tables","foxfire"); ?></th>
				<td>
				    <input class="fox-button" type="submit" name="reset_tables" value="<?php _e('Run Command') ;?>"/>
				</td>
			    </tr>

			</table>

			<table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Reset config options to default values',"foxfire"); ?></th>
				<td>
				    <input class="fox-button" type="submit" name="reset_config" value="<?php _e('Run Command') ;?>"/>
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
	

} // End of class FOX_tab_debug

?>