<?php

/**
 * BP-MEDIA ADMIN PAGE CLASS "CORE SETTINGS"
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

class BPM_tab_templates {


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

	 function render() {

		global $bpm;

		?>
		<!-- Begin Path settings -->

		<form name="pathsform" method="POST" action="<?php echo $this->filepath.'#paths'; ?>" >

		    <?php wp_nonce_field('bpm_admin_settings') ?>

		    <?php $bpm->config->initKeysArray(); ?>

		    <?php // This block of duplicate code allows the section to be moved around without breaking "locking" functionality ?>
		    <?php ////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

		    <?php if (BP_MEDIA_EXPERT_MODE == 1) {

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

			<div class="title"><?php _e('Featured Media',"bp-media") ?> </div>

			<div class="bpm_section_advice">
			    <?php _e('Thumbnail images | 25 to 200 hits per pageview | Low latency is very important.',"bp-media") ?>
			</div>

			<table class="form-table">

				<tr>
				    <th valign="top"><?php _e('Cache mode',"bp-media") ?></th>
				    <td>
					<label class="bpa-lockable-field">
					    <input type="radio" value="global"
						<?php echo $locked; ?>
						<?php $bpm->config->printKeyName("cache", "L1", "mode"); ?>
						<?php checked('global', $bpm->config->getKeyVal("cache", "L1", "mode") ); ?> />
						<?php _e('Site wide', "bp-media" ) ?> &nbsp;
					</label>

					<label class="bpa-lockable-field">
					    <input type="radio" value="single"
						<?php echo $locked; ?>
						<?php $bpm->config->printKeyName("cache", "L1", "mode"); ?>
						<?php checked('single', $bpm->config->getKeyVal("cache", "L1", "mode") ); ?> />
						<?php _e('Individual blogs', "bp-media" ) ?> &nbsp;
					</label> <?php echo $lock_status; ?> <br />
				    </td>
				</tr>

				<tr valign="top">
				    <th align="left"><?php _e('Site mode folder path',"bp-media"); ?></th>
				    <td>
					<input type="text" size="80" class="bpa-lockable-field" maxlength="200"
					       <?php echo $locked; ?>
					       <?php $bpm->config->printKeyName("cache", "L1", "siteFolder"); ?>
					       <?php $bpm->config->printKeyVal("cache", "L1", "siteFolder"); ?> />
					       <?php echo $lock_status; ?>
				    </td>
				</tr>

				<tr valign="top">
				    <th align="left"><?php _e('Site mode folder URL',"bp-media"); ?></th>
				    <td>
					<input type="text" size="80" class="bpa-lockable-field" maxlength="200"
					       <?php echo $locked; ?>
					       <?php $bpm->config->printKeyName("cache", "L1", "siteURI"); ?>
					       <?php $bpm->config->printKeyVal("cache", "L1", "siteURI"); ?> />
					       <?php echo $lock_status; ?>
				    </td>
				</tr>

				<tr valign="top">
				    <th align="left"><?php _e('Blog mode folder offset',"bp-media"); ?></th>
				    <td>
					<input type="text" size="80" class="bpa-lockable-field" maxlength="200"
					       <?php echo $locked; ?>
					       <?php $bpm->config->printKeyName("cache", "L1", "blogFolderOffset"); ?>
					       <?php $bpm->config->printKeyVal("cache", "L1", "blogFolderOffset"); ?> />
					       <?php echo $lock_status; ?>
				    </td>
				</tr>

				<tr valign="top">
				    <th align="left"><?php _e('Blog mode URL offset',"bp-media"); ?></th>
				    <td>
					<input type="text" size="80" class="bpa-lockable-field" maxlength="200"
					       <?php echo $locked; ?>
					       <?php $bpm->config->printKeyName("cache", "L1", "blogURIOffset"); ?>
					       <?php $bpm->config->printKeyVal("cache", "L1", "blogURIOffset"); ?> />
					       <?php echo $lock_status; ?>
				    </td>
				</tr>

			</table>
		    </div>


		    <?php $bpm->config->printKeysArray(); ?>

		    <div class="bpm_submit_h_panel_wrap">
			<div class="submit"><input class="bpm-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
		    </div>

		</form>

		<!-- End Display Settings -->
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


 } // End of class BPM_tab_templates

?>