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

class FOX_tab_server {


	/**
	 * Renders the "Server" tab
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
		$version = new FOX_version();

		?>

		<form name="pathsform" method="POST" action="<?php echo $this->filepath.'#paths'; ?>" >

		    <?php wp_nonce_field('fox_admin_settings') ?>

		    <?php $fox->config->initNodesArray(); ?>


		    <?php if (FOX_EXPERT_MODE == 1) {

				$locked = '';
				$lock_status = '<div class="bpa-unlocked-small"></div>';
			   }
			   else {

				$locked = 'disabled="true"';
				$lock_status = '<div class="bpa-locked-small"></div>';
			   }
		    ?>

		    <div class="panel_section w25">

			<div class="title"><?php _e('Server Environment',"foxfire") ?> </div>

			<div class="fox_section_advice">
			    <?php _e("Your server is reporting the file paths listed below. You can override these values by defining them in your wp-config.php file,<br>
				example... <i>define( 'WP_CONTENT_URL', 'http://mysite.com/test/installs/wp-content');</i>","foxfire") ?>
			</div>

			<table class="form-table">

			    <?php
				  $constants = array('PHP_OS','PHP_VERSION','WP_CONTENT_URL','WP_CONTENT_DIR','WP_PLUGIN_DIR','WP_PLUGIN_URL','FOX_FOLDER','FOX_URL_BASE','FOX_PATH_BASE');

				  foreach($constants as $const){
				  ?>
					<tr valign="top">
					    <th align="left"><?php echo $const ?></th>
					    <td>
						<input type="text" size="80"  maxlength="200" name="<?php echo $const ?>" disabled="disabled"
						       value="<?php echo constant($const) ?>" />
					    </td>
					</tr>
				  <?php
				  } ?>

					<tr valign="top">
					    <th align="left"><?php _e('SQL_VERSION',"foxfire"); ?></th>
					    <td>
					    <input type="text" size="80" maxlength="200" name="SQL_VERSION" disabled="disabled"
					       value="<?php echo $version->getSQLVersion(); ?>" />
					    </td>
					</tr>

					<tr valign="top">
					    <th align="left"><?php _e('APACHE_VERSION',"foxfire"); ?></th>
					    <td>
					    <input type="text" size="80" maxlength="200" name="APACHE_VERSION" disabled="disabled"
					       value="<?php echo $version->getApacheVersion(); ?>" />
					    </td>
					</tr>

			</table>
		    </div>

		    <div class="panel_section w25">

			<div class="title"><?php _e("PHP INFO","foxfire") ?> </div>

			<table class="form-table">
				<tr valign="top">
				    <td>
					<?php FOX_debug::php_info_dump();  ?>
				    </td>
				</tr>
			</table>
		    </div>

		    <?php $fox->config->printNodesArray(); ?>

	    </form>

	    <!-- End Path settings -->

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


} // End of class FOX_tab_server

?>