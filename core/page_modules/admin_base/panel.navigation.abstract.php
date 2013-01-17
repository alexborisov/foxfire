<?php

/**
 * RADIENT ADMIN PAGE CLASS 
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


abstract class RAD_PM_tab_navigation_base {


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
	    //FOX_debug::dump($rad->pageModules);

	    $module_slug = $parent_class->getSlug();
	    $module_id = $parent_class->getId();

	    $rad->navigation->loadModule($module_id);

	    $cls = new RAD_wp();
	    $pages = $cls->getAllPages();

	    ?>

		<script type="text/javascript">

		    jQuery(document).ready(function(){

			    rad_pageModules_navTarget('<?php echo $rad->navigation->getTargetNodeName("location"); ?>', '<?php echo $module_id; ?>');
			    rad_admin_draggableChips();
			    rad_admin_keyPicker();
		    });

		</script>

		<!-- Begin Display Settings -->

		<form name="displayform" method="POST" action="<?php echo $this->filepath.'#display'; ?>" >

		    <?php 
		    
		    wp_nonce_field('rad_admin_settings');
		    
		    $rad->navigation->initNodesArray();

		    // Include the module_id and form_type as a hidden keys
		    ?>
		    <input type="hidden" <?php $rad->navigation->printTargetNodeName("module_id");?> value='<?php echo $module_id; ?>'/>
		    <input type="hidden" name="navigation_form" value='true'/>
		    <?php

		    $targets = $rad->pageModules->getTargets();
		    $views = $rad->pageModules->getViews();
		    $caps = $rad->pageModules->getCaps();
		    //global $bp; FOX_debug::dump($bp); die;
		    if($targets){
			    
			    ?>
			    <div class="panel_section w35">

				<div class="title"><?php _e('Target Location',"radient") ?></div>

				<div class="rad_section_advice">
				    <?php _e("Radient can display content anywhere on your site, including dedicated WordPress pages, inside BuddyPress screens,
					and as embeddable widgets. For more information, see the
					<a href='www.code.google.com/p/buddypress-media/wiki/docs_targetLocation'>target location</a> wiki page.","radient") ?>
				</div>

				<table class="form-table targetOne">

				    <tr>
					<th valign="top"><?php _e('Location',"radient"); ?></th>
					<td>
					    <?php
					    foreach($targets as $target => $fake_var){
						?>
						<label>
						    <input type="radio" value="<?php echo $target; ?>"
							<?php $rad->navigation->printTargetNodeName("location"); ?>
							<?php checked($target, $rad->navigation->getTargetNodeVal("location")); ?> />
							<?php echo ucwords($target);?>
						</label> &nbsp;
						<?php
					    }
					    unset($target, $fake_var);
					    ?>
					</td>
				    </tr>

				</table>

				<table class="form-table targetTwo">

				    <tr>
					<th valign="top"><?php _e('Site page',"radient"); ?></th>

					<td class="pageStatus">

					    <select size="1" <?php $rad->navigation->printTargetNodeName("page_id"); ?> class="bpa-lockable-field pageSelectBox" >

						<?php
						     foreach($pages as $page_name => $page_id){

							    if($page_id == $rad->navigation->getTargetNodeVal("page_id") ) {

								    $selected = 'selected="selected"';
							    }
							    else {
								    $selected = '';
							    }
							    
							    echo '<option value="' . $page_id . '"' . $selected . '>' . $page_name . ' &nbsp;</option>';
						     }
						?>
					    </select>					    
					</td>
				    </tr>

				</table>

				<table class="form-table targetThree">

				    <tr class="alt_override">
					<th valign="top"><?php _e('Slug name',"radient"); ?></th>

					<td class="slugStatus">

						<input type="text" size="20" class="bpa-lockable-field slugField" maxlength="32"
							   <?php $rad->navigation->printTargetNodeName("slug"); ?>
							   <?php $rad->navigation->printTargetNodeVal("slug"); ?>
						/>

					</td>
				    </tr>

				    <tr class="nor_override">
					<th valign="top"><?php _e('Position',"radient"); ?></th>

					<td>

						<input type="text" size="20" class="bpa-lockable-field" maxlength="32"
							   <?php $rad->navigation->printTargetNodeName("tab_position"); ?>
							   <?php $rad->navigation->printTargetNodeVal("tab_position"); ?>
						/>

					</td>
				    </tr>

				    <tr class="alt_override">
					<th valign="top"><?php _e('Title',"radient"); ?></th>

					<td>

						<input type="text" size="20" class="bpa-lockable-field" maxlength="32"
							   <?php $rad->navigation->printTargetNodeName("tab_title"); ?>
							   <?php $rad->navigation->printTargetNodeVal("tab_title"); ?>
						/>

					</td>
				    </tr>

				</table>

			    </div>
			    <?php
		    }
		    ?>		    
		
			<div class="panel_section w25">

			    <div class="title"><?php _e('Visibility',"radient") ?></div>

			    <div class="rad_section_advice">
				<?php _e("Visibility controls which users on your site can see this page. For more information, see the
				    <a href='www.code.google.com/p/buddypress-media/wiki/docs_visibility'>page visibility</a> wiki page.","radient") ?>
			    </div>

			    <div class="key_panel" name="visibility">

				<table class="form-table key_handler" >

				    <tr>
					<th valign="top"><?php _e('show_to',"radient"); ?></th>

					<td>
					    <div class="chip_list_wrap droppable_chip_list" name="show_to">

						<input type="hidden" class="droppable_chip_list_field" 
							   <?php $rad->navigation->printPolicyKeyName("visibility", "show_to"); ?>
							   <?php echo "value='" . json_encode( $rad->navigation->getPolicyKeyVal("visibility", "show_to") ) . "'"; ?>
						/>

						<ul class="chip_list"> <?php

						    $types = $rad->navigation->getPolicyKeyVal("visibility", "show_to");

						    if($types){

							    foreach($types as $type => $keys){

								    if($keys){

									    foreach( $keys as $key_id => $key_data){

										    if( $type == "token" ){

											    $keytype = "token_chip";
										    }
										    elseif( $type == "group"){

											    $keytype = "group_chip";
										    }
										    else {
											    $keytype = "system_chip";
										    }

										    ?>
										    <li key_id="<?php echo $key_id; ?>"
											key_type="<?php echo $type; ?>"
											key_name="<?php echo $key_data["name"]; ?>"
											class="<?php echo $keytype; ?> draggable_chip">

											<div class="textblock">
											    <div class="name"><?php echo $key_data["name"]; ?> </div>
											</div>

											<div class="actions">
											    <div class="delete">x</div>
											</div>

										    </li> <?php
									    }
									    unset($key_id, $keytype, $key_data);
								    }

							    }
							    unset($type, $keys);
						    }

						    ?>
						</ul>

					    </div>
					</td>
				    </tr>

				    <tr>
					<th valign="top"><?php _e('hide_from',"radient"); ?></th>
					<td>
					    <div class="chip_list_wrap droppable_chip_list" name="hide_from" >

						<input type="hidden" class="droppable_chip_list_field"
							   <?php $rad->navigation->printPolicyKeyName("visibility", "hide_from"); ?>
							   <?php echo "value='" . json_encode( $rad->navigation->getPolicyKeyVal("visibility", "hide_from") ) . "'"; ?>
						/>

						<ul class="chip_list"> <?php

						    $types = $rad->navigation->getPolicyKeyVal("visibility", "hide_from");

						    if($types){

							    foreach($types as $type => $keys){

								    if($keys){

									    foreach( $keys as $key_id => $key_data){

										    if( $type == "token" ){

											    $keytype = "token_chip";
										    }
										    elseif( $type == "group"){

											    $keytype = "group_chip";
										    }
										    else {
											    $keytype = "system_chip";
										    }

										    ?>
										    <li key_id="<?php echo $key_id; ?>"
											key_type="<?php echo $type; ?>"
											key_name="<?php echo $key_data["name"]; ?>"
											class="<?php echo $keytype; ?> draggable_chip">

											<div class="textblock">
											    <div class="name"><?php echo $key_data["name"]; ?> </div>
											</div>

											<div class="actions">
											    <div class="delete">x</div>
											</div>

										    </li> <?php
									    }
									    unset($key_id, $keytype, $key_data);
								    }

							    }
							    unset($type, $keys);
						    }

						    ?>
						</ul>

					    </div>
					</td>
				    </tr>

				</table>

				<div class="key_manager"><img src="<?php echo RAD_URL_CORE . '/admin/css/images/rad_key_editor.png' ?>"
							      alt="<?php _e('Key Manager',"radient") ?>"
							      width="32" height="32"
							      title="Open the Key Manager"/>
				</div>

			</div>			

		    </div>

		    <?php

		    if($views){

			    ?>
			    <div class="panel_section w25">

				<div class="title"><?php _e('User Views',"radient") ?></div>

				<div class="rad_section_advice">
				    <?php _e("Your current theme supports different views for this page based on the keys a user has. For more info,
					see the <a href='www.code.google.com/p/buddypress-media/wiki/docs_pageUserViews'>user views</a> wiki page.","radient") ?>
				</div>

				<div class="key_panel" name="views">

				    <table class="form-table key_handler">

					<?php

					foreach($views as $view => $description){
					    ?>
					    <tr>

						<th class="page_modules_role_lock" valign="top"><?php echo $view?></th>

						<td>
						    <div class="chip_list_wrap droppable_chip_list" name="<?php echo $view?>" >

							<input type="hidden" class="droppable_chip_list_field" 
								   <?php $rad->navigation->printPolicyKeyName("views", $view); ?>
								   <?php echo "value='" . json_encode( $rad->navigation->getPolicyKeyVal("views", $view) ) . "'"; ?>
							/>

							<ul class="chip_list"> <?php

							    $types = $rad->navigation->getPolicyKeyVal("views", $view);

							    if($types){

								    foreach($types as $type => $keys){

									    if($keys){

										    foreach( $keys as $key_id => $key_data){

											    if( $type == "token" ){

												    $keytype = "token_chip";
											    }
											    elseif( $type == "group"){

												    $keytype = "group_chip";
											    }
											    else {
												    $keytype = "system_chip";
											    }

											    ?>
											    <li key_id="<?php echo $key_id; ?>"
												key_type="<?php echo $type; ?>"
												key_name="<?php echo $key_data["name"]; ?>"
												class="<?php echo $keytype; ?> draggable_chip">

												<div class="textblock">
												    <div class="name"><?php echo $key_data["name"]; ?> </div>
												</div>

												<div class="actions">
												    <div class="delete">x</div>
												</div>

											    </li> <?php
										    }
										    unset($key_id, $keytype, $key_data);
									    }

								    }
								    unset($type, $keys);
							    }

							    ?>
							</ul>

						    </div>
						</td>
					    </tr>
					    <?php
					}
					unset($view, $description);
					?>

				    </table>

				    <div class="key_manager"><img src="<?php echo RAD_URL_CORE . '/admin/css/images/rad_key_editor.png' ?>"
								  alt="<?php _e('Key Manager',"radient") ?>"
								  width="32" height="32"
								  title="Open the Key Manager"/>
				    </div>

				</div>
			    </div>
			    <?php
		    }


		    if($caps){

			    ?>
			    <div class="panel_section w25">

				<div class="title"><?php _e('User Capabilities',"radient") ?></div>

				<div class="rad_section_advice">
				    <?php _e("Your current theme supports capability-locking based on the keys a user has. For more info,
					see the <a href='www.code.google.com/p/buddypress-media/wiki/docs_pageAccessControl'>access control</a> wiki page.","radient") ?>
				</div>

				<div class="key_panel" name="access">

				    <table class="form-table key_handler">

					<?php
					foreach($caps as $cap => $description){
					    ?>
					    <tr>

						<th class="page_modules_role_lock" valign="top"><?php echo $cap?></th>

						<td>
						    <div class="chip_list_wrap droppable_chip_list" name="<?php echo $cap?>">

							<input type="hidden" class="droppable_chip_list_field"
								   <?php $rad->navigation->printPolicyKeyName("caps", $cap); ?>
								   <?php echo "value='" . json_encode( $rad->navigation->getPolicyKeyVal("caps", $cap) ) . "'"; ?>
							/>

							<ul class="chip_list"> <?php

							    $types = $rad->navigation->getPolicyKeyVal("caps", $cap);

							    if($types){

								    foreach($types as $type => $keys){

									    if($keys){

										    foreach( $keys as $key_id => $key_data){

											    if( $type == "token" ){

												    $keytype = "token_chip";
											    }
											    elseif( $type == "group"){

												    $keytype = "group_chip";
											    }
											    else {
												    $keytype = "system_chip";
											    }

											    ?>
											    <li key_id="<?php echo $key_id; ?>"
												key_type="<?php echo $type; ?>"
												key_name="<?php echo $key_data["name"]; ?>"
												class="<?php echo $keytype; ?> draggable_chip">

												<div class="textblock">
												    <div class="name"><?php echo $key_data["name"]; ?> </div>
												</div>

												<div class="actions">
												    <div class="delete">x</div>
												</div>

											    </li> <?php
										    }
										    unset($key_id, $keytype, $key_data);
									    }

								    }
								    unset($type, $keys);
							    }

							    ?>
							</ul>

						    </div>
						</td>
					    </tr>
					    <?php
					}
					unset($cap, $description);
					?>

				    </table>

				    <div class="key_manager"><img src="<?php echo RAD_URL_CORE . '/admin/css/images/rad_key_editor.png' ?>"
								  alt="<?php _e('Key Manager',"radient") ?>"
								  width="32" height="32"
								  title="Open the Key Manager"/>
				    </div>

				</div>
			    </div>
			    <?php
		    }
		    ?>

		    <?php $rad->navigation->printNodesArray(); ?>

		    <div class="rad_submit_v_panel_wrap">
			<div class="submit"><input class="rad-button rad-validator-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
		    </div>

		</form>

		<div class="palette_content"></div>

		<!-- End Display Settings -->
	<?php
	}


 }

?>