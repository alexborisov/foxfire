<?php

/**
 * FOXFIRE ADMIN PAGE CLASS "CORE SETTINGS"
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Admin
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

// Prevent hackers from directly calling this page
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

// ============================================================================================================ //


abstract class FOX_PM_tab_settings_base {


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

	 function render($parent_class) {

	    global $rad, $fox;
	    $module_slug = $parent_class->getSlug();

	    $thumbs = $rad->pageModules->getThumbs();

	    ?>
		<!-- Begin Display Settings -->

		<form name="displayform" method="POST" action="<?php echo $this->filepath.'#display'; ?>" >

		    <?php wp_nonce_field('rad_admin_settings') ?>

		    <?php $fox->config->initNodesArray(); ?>

		    <div class="panel_section w35">

			<div class="title"><?php _e('Thumbnails',"radient") ?></div>

			<?php
			    if($thumbs){
				?>
				<div class="rad_section_advice">
				    <?php _e("Your current theme wants FoxFire to display thumbnail images for this page in a specific way. If you decide to
					override the theme's thumbnail settings, check the page to make sure the theme is still rendering properly.","radient") ?>
				</div>
				<?php
			    }
			?>

			<table class="form-table">

			    <tr>
				<th valign="top"><?php _e('Display algorithm',"radient"); ?></th>
				<td>
				    <?php

				    if( $parent_class->objectType() == "album" ){

					    ?>
					    <label>
						<input type="radio" value="fan"
						    <?php echo "name='" .$base_name . "[thumbAlgorithm]'"; ?>
						    <?php checked('fan', $base_val["thumbAlgorithm"] ); ?> />
						    <?php _e('Fan', "radient"); ?>
					    </label> &nbsp;
					    <?php
				    }
				    ?>

				    <label>
					<input type="radio" value="mosaic"
					    <?php echo "name='" .$base_name . "[thumbAlgorithm]'"; ?>
					    <?php checked('mosaic', $base_val["thumbAlgorithm"] ); ?> />
					    <?php _e('Mosaic', "radient"); ?>
				    </label> &nbsp;

				    <label>
					<input type="radio" value="crop"
					    <?php echo "name='" .$base_name . "[thumbAlgorithm]'"; ?>
					    <?php checked('crop', $base_val["thumbAlgorithm"] ); ?> />
					    <?php _e('Crop', "radient"); ?>
				    </label> &nbsp;

				    <label>
					<input type="radio" value="scale"
					    <?php echo "name='" .$base_name . "[thumbAlgorithm]'"; ?>
					    <?php checked('scale', $base_val["thumbAlgorithm"] ); ?> />
					    <?php _e('Scale', "radient"); ?>
				    </label>

				</td>
			    </tr>

			    <tr>
				<th valign="top"><?php _e('Max items',"radient"); ?></th>
				<td>
					<input type="text" size="4" maxlength="4"
					       <?php echo "name='" .$base_name . "[maxItemsPage]'"; ?>
					       <?php echo "value='" . $base_val["maxItemsPage"] ."'"; ?>
					/>
					<?php _e('page', "radient" ); ?>&nbsp;&nbsp;

					<input type="text" size="4" maxlength="4"
					       <?php echo "name='" .$base_name . "[maxItemsRow]'"; ?>
					       <?php echo "value='" . $base_val["maxItemsRow"] ."'"; ?>
					/>
					<?php _e('row', "radient" ); ?>
				</td>
			    </tr>

			    <tr>
				<th><?php _e('Base thumbnail size', "radient" ); ?></th>
				<td>
				    <select size="1" <?php echo "name='" .$base_name . "[thumbSize]'"; ?> >

					<?php $selected = $base_val["thumbSize"] == $size->name ? 'selected="selected"' : ''; ?>

					<option <?php echo "value='" . $base_val["maxItemsRow"] ."'"; echo $selected; ?> >
					    <?php _e('system &nbsp;', "radient" ) ?>
					</option>

					<?php
//					     foreach($sizes as $size){
//						      $selected = $fox->config->getNodeVal("radient", "featured", "siteLatestUploads", "thumbSize") == $size->name ? 'selected="selected"' : '';
//						      echo '<option value="' . $size->name . '"' . $selected . '>' . $size->name . ' &nbsp;</option>';
//					     }
					?>
				    </select>
				</td>
			    </tr>

			    <tr>
				<th valign="top"><?php _e('Scale in browser to fit',"radient"); ?></th>
				<td>
					<input type="text" size="4" maxlength="4"
					       <?php echo "name='" .$base_name . "[thumbPixelsX]'"; ?>
					       <?php echo "value='" . $base_val["thumbPixelsX"] ."'"; ?>
					/>
					<?php _e('X pixels', "radient" ); ?>&nbsp;&nbsp;

					<input type="text" size="4" maxlength="4"
					       <?php echo "name='" .$base_name . "[thumbPixelsY]'"; ?>
					       <?php echo "value='" . $base_val["thumbPixelsY"] ."'"; ?>
					/>
					<?php _e('Y pixels', "radient" ); ?>
				</td>
			    </tr>

			    <tr>
				<th><?php _e('Sorting', "radient" ); ?></th>
				<td>
				    <select size="1" <?php echo "name='" .$base_name . "[sortMethod]'"; ?> >

					<?php

					    $methods = array(	"random" => __('Random', "radient"),
								"date" => __('Upload Date', "radient"),
								"views" => __('View Count', "radient"),
								"rating" => __('Item Rating', "radient")
					    );

					    foreach($methods as $method => $name){
						  $selected = $base_val["sortMethod"] == $size->name ? 'selected="selected"' : '';
						  echo '<option value="' . $method . '"' . $selected . '>' . $name . ' &nbsp;</option>';
					    }
					    unset($method, $name);

					?>
				    </select>

				    &nbsp; &nbsp;

				    <select size="1" <?php echo "name='" .$base_name . "[sortDirection]'"; ?> >

					<?php

					    $methods = array(	"ASC" => __('ASC', "radient"),
								"DESC" => __('DESC', "radient")
					    );

					    foreach($methods as $method => $name){
						  $selected = $base_val["sortMethod"] == $size->name ? 'selected="selected"' : '';
						  echo '<option value="' . $method . '"' . $selected . '>' . $name . ' &nbsp;</option>';
					    }
					    unset($method, $name);

					?>
				    </select>
				</td>
			    </tr>

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