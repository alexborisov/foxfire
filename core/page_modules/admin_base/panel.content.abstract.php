<?php

/**
 * RADIENT ADMIN PAGE CLASS "CORE SETTINGS"
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package Radient
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


abstract class RAD_PM_tab_content_base {


	/**
	 * Renders the "Display" tab
	 *
	 * This tab rendering function creates a single tab within the admin page that its parent class generates. The tab's form
	 * contains a hidden field called 'page_options'. The class's "processor" function parses the variable names in this field
	 * to determine which POST data fields to load and which objects in the $bp->bpa->options[] global to update.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	 function render($parent_class) {

	    global $rad, $fox;
	    $module_slug = $parent_class->getSlug();



	    ?>
		<!-- Begin Display Settings -->

		<form name="displayform" method="POST" action="<?php echo $this->filepath.'#display'; ?>" >

		    <?php wp_nonce_field('rad_admin_settings') ?>

		    <?php $fox->config->initNodesArray(); ?>


		    <div class="panel_section w30">

			<div class="title"><?php _e('Album Modules',"radient") ?></div>

			<table class="form-table">

			    <?php
			    $base_name = $fox->config->getNodeName("radient", "pageModules", $module_slug, "activeAlbumModules");
			    $base_val = $fox->config->getNodeVal("radient", "pageModules", $module_slug, "activeAlbumModules");

			    $active_modules = $rad->albumModules->getActiveModules();

			    foreach( $active_modules as $module ){

				    ?>
				    <tr>
					<th scope="row"><?php echo $module['name']; ?></th>
					<td>
					    <input type="radio" value="1"
						<?php echo "name='" .$base_name . "[" . $module['module_id'] . "]'"; ?>
						<?php checked(1, (int)$base_val[$module['module_id']]  ); ?> />
						<?php _e('Enabled', "radient" ) ?> &nbsp;

					    <input type="radio" value="0"
						<?php echo "name='" .$base_name . "[" . $module['module_id'] . "]'"; ?>
						<?php checked(0, (int)$base_val[$module['module_id']]  ); ?> />
						<?php _e('Disabled', "radient" ) ?>
					</td>
				    </tr>
				    <?php
			    }
			    ?>

			</table>
		    </div>

		    <div class="panel_section w30">

			<div class="title"><?php _e('Media Modules',"radient") ?></div>

			<table class="form-table">

			    <?php
			    $base_name = $fox->config->getNodeName("radient", "pageModules", $module_slug, "activeMediaModules");
			    $base_val = $fox->config->getNodeVal("radient", "pageModules", $module_slug, "activeMediaModules");

			    $active_modules = $rad->mediaModules->getActiveModules();

			    foreach( $active_modules as $module ){

				    ?>
				    <tr>
					<th scope="row"><?php echo $module['name']; ?></th>
					<td>
					    <input type="radio" value="1"
						<?php echo "name='" .$base_name . "[" . $module['module_id'] . "]'"; ?>
						<?php checked(1, (int)$base_val[$module['module_id']]  ); ?> />
						<?php _e('Enabled', "radient" ) ?> &nbsp;

					    <input type="radio" value="0"
						<?php echo "name='" .$base_name . "[" . $module['module_id'] . "]'"; ?>
						<?php checked(0, (int)$base_val[$module['module_id']]  ); ?> />
						<?php _e('Disabled', "radient" ) ?>
					</td>
				    </tr>
				    <?php
			    }
			    ?>

			</table>
		    </div>

		    <?php $fox->config->printNodesArray(); ?>

		    <div class="rad_submit_v_panel_wrap">
			<div class="submit"><input class="rad-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
		    </div>

		</form>

		<!-- End Display Settings -->
	<?php
	}


 }

?>