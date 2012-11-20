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

class BPM_tab_slugs {


	/**
	 * Renders the "Slugs" tab
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
	    <!-- Begin Slug settings -->

	    <form name="slugsform" method="post" action="<?php echo $this->filepath.'#slugs'; ?>">

		<?php wp_nonce_field('bpm_admin_settings') ?>

		<?php $bpm->config->initKeysArray(); ?>

		<div class="bpm_tip">
		    <div class="bpm_warning_large"></div>
		    <div class="bpm_tip_text">
			<?php _e("<b>WARNING</b>: Each page slug must have a <u>unique</u> name. Slug names can only contain the characters a-z, A-Z,
			    0-9, '-', and '_' with <u>no spaces</u>. The plugin does not error check the values you enter here. Invalid page slug names
			    can <u>white screen</u> your WordPress install.", "bp-media") ?>
		    </div>
		</div>


		<div class="panel_section">

		    <div class="title"><?php _e('Base Slug',"bp-media") ?> </div>

		    <table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Base Slug',"bp-media"); ?></th>
				<td>
				    <input type="text" size="20" maxlength="30"
					   <?php $bpm->config->printKeyName("slugs", "base", "base"); ?>
					   <?php $bpm->config->printKeyVal("slugs", "base", "base"); ?> />
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Base Slug -> Home',"bp-media"); ?></th>
				<td>
				    <input type="text" size="20" maxlength="30"
					   <?php $bpm->config->printKeyName("slugs", "base", "home"); ?>
					   <?php $bpm->config->printKeyVal("slugs", "base", "home"); ?> />
				</td>
			    </tr>

		    </table>
		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Albums',"bp-media") ?> </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Home',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "albums", "home"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "albums", "home"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Create',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "albums", "create"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "albums", "create"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Edit',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "albums", "edit"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "albums", "edit"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Items',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "albums", "items"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "albums", "items"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Sort',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "albums", "sort"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "albums", "sort"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Delete',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "albums", "delete"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "albums", "delete"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Me',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "albums", "me"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "albums", "me"); ?> />
			    </td>
			</tr>

		    </table>

		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Media Items',"bp-media") ?> </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Home',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "media", "home"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "media", "home"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Single',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "media", "single"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "media", "single"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Create',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "media", "create"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "media", "create"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Edit',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "media", "edit"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "media", "edit"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Delete',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "media", "delete"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "media", "delete"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Tag',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "media", "tag"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "media", "tag"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Me',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("slugs", "media", "me"); ?>
				       <?php $bpm->config->printKeyVal("slugs", "media", "me"); ?> />
			    </td>
			</tr>
		    </table>

		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Member Tags',"bp-media") ?> </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Tag Self',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("member", "tags", "tagSelf"); ?>
				       <?php $bpm->config->printKeyVal("member", "tags", "tagSelf"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Untag Self',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("member", "tags", "unTagSelf"); ?>
				       <?php $bpm->config->printKeyVal("member", "tags", "unTagSelf"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Tag Member',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("member", "tags", "tagMember"); ?>
				       <?php $bpm->config->printKeyVal("member", "tags", "tagMember"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Untag Member',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("member", "tags", "unTagMember"); ?>
				       <?php $bpm->config->printKeyVal("member", "tags", "unTagMember"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Media Of Me',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("member", "tags", "mediaOfMe"); ?>
				       <?php $bpm->config->printKeyVal("member", "tags", "mediaOfMe"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Remove Favorite',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("member", "tags", "removeFavorite"); ?>
				       <?php $bpm->config->printKeyVal("member", "tags", "removeFavorite"); ?> />
			    </td>
			</tr>

		    </table>
		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Keyword Tags',"bp-media") ?> </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Keyword Tags -> All Items',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("keyword", "tags", "showTag"); ?>
				       <?php $bpm->config->printKeyVal("keyword", "tags", "showTag"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Keyword Tags -> Member Items',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("keyword", "tags", "tagsByMember"); ?>
				       <?php $bpm->config->printKeyVal("keyword", "tags", "tagsByMember"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Keyword Tags -> Add Tag',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("keyword", "tags", "addTag"); ?>
				       <?php $bpm->config->printKeyVal("keyword", "tags", "addTag"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Keyword Tags -> Remove Tag',"bp-media"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $bpm->config->printKeyName("keyword", "tags", "removeTag"); ?>
				       <?php $bpm->config->printKeyVal("keyword", "tags", "removeTag"); ?> />
			    </td>
			</tr>

		    </table>
		</div>

		<?php $bpm->config->printKeysArray(); ?>

		<div class="bpm_submit_h_panel_wrap">
		    <div class="submit"><input class="bpm-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
		</div>

	    </form>
	    <!-- End Slug settings -->
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


} // End of class BPM_tab_slugs

?>