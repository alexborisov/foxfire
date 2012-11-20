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

		<?php $fox->config->initKeysArray(); ?>

		<div class="fox_tip">
		    <div class="fox_warning_large"></div>
		    <div class="fox_tip_text">
			<?php _e("<b>WARNING</b>: Each page slug must have a <u>unique</u> name. Slug names can only contain the characters a-z, A-Z,
			    0-9, '-', and '_' with <u>no spaces</u>. The plugin does not error check the values you enter here. Invalid page slug names
			    can <u>white screen</u> your WordPress install.", "foxfire") ?>
		    </div>
		</div>


		<div class="panel_section">

		    <div class="title"><?php _e('Base Slug',"foxfire") ?> </div>

		    <table class="form-table">

			    <tr valign="top">
				<th align="left"><?php _e('Base Slug',"foxfire"); ?></th>
				<td>
				    <input type="text" size="20" maxlength="30"
					   <?php $fox->config->printKeyName("slugs", "base", "base"); ?>
					   <?php $fox->config->printKeyVal("slugs", "base", "base"); ?> />
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Base Slug -> Home',"foxfire"); ?></th>
				<td>
				    <input type="text" size="20" maxlength="30"
					   <?php $fox->config->printKeyName("slugs", "base", "home"); ?>
					   <?php $fox->config->printKeyVal("slugs", "base", "home"); ?> />
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
				       <?php $fox->config->printKeyName("slugs", "albums", "home"); ?>
				       <?php $fox->config->printKeyVal("slugs", "albums", "home"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Create',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "albums", "create"); ?>
				       <?php $fox->config->printKeyVal("slugs", "albums", "create"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Edit',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "albums", "edit"); ?>
				       <?php $fox->config->printKeyVal("slugs", "albums", "edit"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Items',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "albums", "items"); ?>
				       <?php $fox->config->printKeyVal("slugs", "albums", "items"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Sort',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "albums", "sort"); ?>
				       <?php $fox->config->printKeyVal("slugs", "albums", "sort"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Delete',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "albums", "delete"); ?>
				       <?php $fox->config->printKeyVal("slugs", "albums", "delete"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Me',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "albums", "me"); ?>
				       <?php $fox->config->printKeyVal("slugs", "albums", "me"); ?> />
			    </td>
			</tr>

		    </table>

		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Media Items',"foxfire") ?> </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Home',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "media", "home"); ?>
				       <?php $fox->config->printKeyVal("slugs", "media", "home"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Single',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "media", "single"); ?>
				       <?php $fox->config->printKeyVal("slugs", "media", "single"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Create',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "media", "create"); ?>
				       <?php $fox->config->printKeyVal("slugs", "media", "create"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Edit',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "media", "edit"); ?>
				       <?php $fox->config->printKeyVal("slugs", "media", "edit"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Delete',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "media", "delete"); ?>
				       <?php $fox->config->printKeyVal("slugs", "media", "delete"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Media -> Tag',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "media", "tag"); ?>
				       <?php $fox->config->printKeyVal("slugs", "media", "tag"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Album -> Me',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("slugs", "media", "me"); ?>
				       <?php $fox->config->printKeyVal("slugs", "media", "me"); ?> />
			    </td>
			</tr>
		    </table>

		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Member Tags',"foxfire") ?> </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Tag Self',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("member", "tags", "tagSelf"); ?>
				       <?php $fox->config->printKeyVal("member", "tags", "tagSelf"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Untag Self',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("member", "tags", "unTagSelf"); ?>
				       <?php $fox->config->printKeyVal("member", "tags", "unTagSelf"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Tag Member',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("member", "tags", "tagMember"); ?>
				       <?php $fox->config->printKeyVal("member", "tags", "tagMember"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Untag Member',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("member", "tags", "unTagMember"); ?>
				       <?php $fox->config->printKeyVal("member", "tags", "unTagMember"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Media Of Me',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("member", "tags", "mediaOfMe"); ?>
				       <?php $fox->config->printKeyVal("member", "tags", "mediaOfMe"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Member Tags -> Remove Favorite',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("member", "tags", "removeFavorite"); ?>
				       <?php $fox->config->printKeyVal("member", "tags", "removeFavorite"); ?> />
			    </td>
			</tr>

		    </table>
		</div>

		<div class="panel_section">

		    <div class="title"><?php _e('Keyword Tags',"foxfire") ?> </div>

		    <table class="form-table">

			<tr valign="top">
			    <th align="left"><?php _e('Keyword Tags -> All Items',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("keyword", "tags", "showTag"); ?>
				       <?php $fox->config->printKeyVal("keyword", "tags", "showTag"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Keyword Tags -> Member Items',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("keyword", "tags", "tagsByMember"); ?>
				       <?php $fox->config->printKeyVal("keyword", "tags", "tagsByMember"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Keyword Tags -> Add Tag',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("keyword", "tags", "addTag"); ?>
				       <?php $fox->config->printKeyVal("keyword", "tags", "addTag"); ?> />
			    </td>
			</tr>

			<tr valign="top">
			    <th align="left"><?php _e('Keyword Tags -> Remove Tag',"foxfire"); ?></th>
			    <td>
				<input type="text" size="20" maxlength="30"
				       <?php $fox->config->printKeyName("keyword", "tags", "removeTag"); ?>
				       <?php $fox->config->printKeyVal("keyword", "tags", "removeTag"); ?> />
			    </td>
			</tr>

		    </table>
		</div>

		<?php $fox->config->printKeysArray(); ?>

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