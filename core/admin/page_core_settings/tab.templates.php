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

class FOX_tab_templates {


	/**
	 * Renders the "Display" tab
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
		<!-- Begin Path settings -->

		<form name="pathsform" method="POST" action="<?php echo $this->filepath.'#paths'; ?>" >

		    <?php wp_nonce_field('fox_admin_settings') ?>

		    <?php $fox->config->initNodesArray(); ?>

		    <?php // This block of duplicate code allows the section to be moved around without breaking "locking" functionality ?>
		    <?php ////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

		    <?php if (FOX_EXPERT_MODE == 1) {

				$locked = '';
				$lock_status = '<div class="bpa-unlocked-small"></div>';
			   }
			   else {

				$locked = 'disabled="true"';
				$lock_status = '<div class="bpa-locked-small"></div>';
			   }
		    ?>
		    <?php ////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>


		    <div class="panel_section w35">

			<div class="title"><?php _e('Featured Media',"foxfire") ?> </div>

			<div class="fox_section_advice">
			    <?php _e('Thumbnail images | 25 to 200 hits per pageview | Low latency is very important.',"foxfire") ?>
			</div>

			<table class="form-table">

				<tr>
				    <th valign="top"><?php _e('Cache mode',"foxfire") ?></th>
				    <td>
					<label class="fox_lockable_field">
					    <input type="radio" value="global"
						<?php echo $locked; ?>
						<?php $fox->config->printNodeName("foxfire", "cache", "L1", "mode"); ?>
						<?php checked('global', $fox->config->getNodeVal("foxfire","cache", "L1", "mode") ); ?> />
						<?php _e('Site wide', "foxfire" ) ?> &nbsp;
					</label>

					<label class="fox_lockable_field">
					    <input type="radio" value="single"
						<?php echo $locked; ?>
						<?php $fox->config->printNodeName("foxfire","cache", "L1", "mode"); ?>
						<?php checked('single', $fox->config->getNodeVal("foxfire","cache", "L1", "mode") ); ?> />
						<?php _e('Individual blogs', "foxfire" ) ?> &nbsp;
					</label> <?php echo $lock_status; ?> <br />
				    </td>
				</tr>

				<tr valign="top">
				    <th align="left"><?php _e('Site mode folder path',"foxfire"); ?></th>
				    <td>
					<input type="text" size="80" class="fox_lockable_field" maxlength="200"
					       <?php echo $locked; ?>
					       <?php $fox->config->printNodeName("foxfire","cache", "L1", "siteFolder"); ?>
					       <?php $fox->config->printNodeVal("foxfire","cache", "L1", "siteFolder"); ?> />
					       <?php echo $lock_status; ?>
				    </td>
				</tr>

				<tr valign="top">
				    <th align="left"><?php _e('Site mode folder URL',"foxfire"); ?></th>
				    <td>
					<input type="text" size="80" class="fox_lockable_field" maxlength="200"
					       <?php echo $locked; ?>
					       <?php $fox->config->printNodeName("foxfire","cache", "L1", "siteURI"); ?>
					       <?php $fox->config->printNodeVal("foxfire","cache", "L1", "siteURI"); ?> />
					       <?php echo $lock_status; ?>
				    </td>
				</tr>

				<tr valign="top">
				    <th align="left"><?php _e('Blog mode folder offset',"foxfire"); ?></th>
				    <td>
					<input type="text" size="80" class="fox_lockable_field" maxlength="200"
					       <?php echo $locked; ?>
					       <?php $fox->config->printNodeName("foxfire","cache", "L1", "blogFolderOffset"); ?>
					       <?php $fox->config->printNodeVal("foxfire","cache", "L1", "blogFolderOffset"); ?> />
					       <?php echo $lock_status; ?>
				    </td>
				</tr>

				<tr valign="top">
				    <th align="left"><?php _e('Blog mode URL offset',"foxfire"); ?></th>
				    <td>
					<input type="text" size="80" class="fox_lockable_field" maxlength="200"
					       <?php echo $locked; ?>
					       <?php $fox->config->printNodeName("foxfire","cache", "L1", "blogURIOffset"); ?>
					       <?php $fox->config->printNodeVal("foxfire","cache", "L1", "blogURIOffset"); ?> />
					       <?php echo $lock_status; ?>
				    </td>
				</tr>

			</table>
		    </div>


		    <?php $fox->config->printNodesArray(); ?>

		    <div class="fox_submit_h_panel_wrap">
			<div class="submit"><input class="fox-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
		    </div>

		</form>

		<!-- End Display Settings -->
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


 } // End of class FOX_tab_templates

?>