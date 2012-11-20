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

class BPM_tab_server {


	/**
	 * Renders the "Server" tab
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
		$version = new BPM_version();

		?>

		<form name="pathsform" method="POST" action="<?php echo $this->filepath.'#paths'; ?>" >

		    <?php wp_nonce_field('bpm_admin_settings') ?>

		    <?php $bpm->config->initKeysArray(); ?>


		    <?php if (BP_MEDIA_EXPERT_MODE == 1) {

				$locked = '';
				$lock_status = '<div class="bpa-unlocked-small"></div>';
			   }
			   else {

				$locked = 'disabled="true"';
				$lock_status = '<div class="bpa-locked-small"></div>';
			   }
		    ?>

		    <div class="panel_section w25">

			<div class="title"><?php _e('Server Environment',"bp-media") ?> </div>

			<div class="bpm_section_advice">
			    <?php _e("Your server is reporting the file paths listed below. You can override these values by defining them in your wp-config.php file,<br>
				example... <i>define( 'WP_CONTENT_URL', 'http://mysite.com/test/installs/wp-content');</i>","bp-media") ?>
			</div>

			<table class="form-table">

			    <?php
				  $constants = array('PHP_OS','PHP_VERSION','WP_CONTENT_URL','WP_CONTENT_DIR','WP_PLUGIN_DIR','WP_PLUGIN_URL','BPM_FOLDER','BPM_URL_BASE','BPM_PATH_BASE');

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
					    <th align="left"><?php _e('SQL_VERSION',"bp-media"); ?></th>
					    <td>
					    <input type="text" size="80" maxlength="200" name="SQL_VERSION" disabled="disabled"
					       value="<?php echo $version->getSQLVersion(); ?>" />
					    </td>
					</tr>

					<tr valign="top">
					    <th align="left"><?php _e('APACHE_VERSION',"bp-media"); ?></th>
					    <td>
					    <input type="text" size="80" maxlength="200" name="APACHE_VERSION" disabled="disabled"
					       value="<?php echo $version->getApacheVersion(); ?>" />
					    </td>
					</tr>

			</table>
		    </div>

		    <div class="panel_section w25">

			<div class="title"><?php _e("PHP INFO","bp-media") ?> </div>

			<table class="form-table">
				<tr valign="top">
				    <td>
					<?php BPM_debug::php_info_dump();  ?>
				    </td>
				</tr>
			</table>
		    </div>

		    <?php $bpm->config->printKeysArray(); ?>

	    </form>

	    <!-- End Path settings -->

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


} // End of class BPM_tab_server

?>