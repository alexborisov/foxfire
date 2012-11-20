/**
 * BP-MEDIA ADMIN DRAGGABLE CHIPS JAVASCRIPT FUNCTIONS
 * Adds draggable chips and floating chip palettes to admin pages. This version of the class posts events
 * to the server via AJAX as chips are moved, instead of enqueuing them in the DOM to be submitted as a
 * single save operation. PHP router functions are located in the comments block at the end of the file.
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Admin JS
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */


/**
 * Make chips in the parent page draggable and bins in the parent page droppable
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function bpm_admin_draggableChips(){


	jQuery('.draggable_chip').draggable( {
		containment: '.child_panel',
		appendTo: 'body',
		cursor: 'move',
		revert: true,
		opacity: 0.5
	});

	makeChipsDeletable()
	
	jQuery('.droppable_chip_list').droppable( {
		hoverClass: "chip_list_wrap_active",
		drop: handleChipDrop
	});

}


/**
 * Makes chips draggable between bins and from floating palettes to bins.
 *
 * @version 0.1.9
 * @since 0.1.9
 *
 * @param event | The file or URL to process
 * @param ui | The file or URL to process
 */

function handleChipDrop( event, ui ) {

	var source_section = ui.draggable.closest(".key_panel").attr('name');
	var source_bin = ui.draggable.closest(".droppable_chip_list").attr('name');

	var dest_section = jQuery(this).closest(".key_panel").attr('name');
	var dest_bin = jQuery(this).closest(".droppable_chip_list").attr('name');

	var key_id = ui.draggable.attr('key_id');
	var key_type = ui.draggable.attr('key_type');


	// If the user drops the chip back into the bin it came from,
	// revert the drop operation
	// ==================================================================
	if( (source_bin == dest_bin) && (source_section == dest_section) ){

		ui.draggable.draggable( 'option', 'revert', true );
		jQuery(".draggable_chip").attr("style", "position: relative;")
	}
	else {

		// Otherwise, check if the key_id + key_type already exists in the dest
		// list by counting the number of results a query for the key_id returns
		// ==================================================================

		var exists_in_list = jQuery(this).find(".draggable_chip[key_id='" + key_id + "']").filter(".draggable_chip[key_type='" + key_type + "']").length;


		if( exists_in_list ){

			ui.draggable.draggable( 'option', 'revert', true ); // Revert the drag if the key
									    // already exists in the bin
		}
		else {

			var active_bin = jQuery(this);
			var active_chip = ui.draggable.closest(".draggable_chip");

			ui.draggable.draggable( 'option', 'revert', false );

			// CASE 1: Adding a chip from a floating palette
			// ==================================================================
			if( ui.draggable.attr('add_chip') == "1" ) {	// Floating palettes set the attribute
									// 'add_chip' on all their chips
				jQuery.ajax({

				    type: 'POST',
				    url: ajaxurl,
				    data: {
					'action':'admin_addChip',
					'module_type': 'page',
					'module_id': 4,
					'dest_section_name': dest_section,
					'dest_bin_name': dest_bin,
					'key_id': key_id
				    },
				    beforeSend: function(){

					jQuery(this).css('cursor','wait');
				    },
				    success: function(response){

					jQuery(this).css('cursor','default');

					if( response == 'OK'){

						// Read the contents of the target bin's hidden field, and parse it to a js array
						var chipFieldRaw = active_bin.children('.droppable_chip_list_field').val()
						var chipField = jQuery.parseJSON(chipFieldRaw);

						// Read the type, id, and name of the new chip
						var chipType = 	active_chip.attr('key_type');
						var chipID = active_chip.attr('key_id');
						var chipName = active_chip.attr('key_name');

						// Add the chip to the js array, compile the array to JSON, and write it back to the bin's hidden field

						if( !chipField[chipType]){
							// We have to do this because JavaScript doesn't have multidimensional associative arrays
							chipField[chipType] = {};
						}

						chipField[chipType][chipID] = chipName;
						active_bin.children('.droppable_chip_list_field').val( JSON.stringify(chipField) );

						// Remove 'add_chip' attribute from the chip
						active_chip.removeAttr('add_chip');

						// Add new chip to dest bin's chip list. Can't use jQuery(this) because it will
						// point to the AJAX object
						active_bin.children('.chip_list').append( active_chip );

						// Snap chip's position to the place it would normally be floating in the list
						active_chip.attr("style", "position: relative;");

						// Fade-in the chip
						active_chip.css("opacity", 0.3).animate({opacity : 1.0}, 1000);

						resetFloatingPalette();
						makeChipsDeletable();
					}

					// alert('The server responded: ' + response);
				    }
				});

			}

			// CASE 2: Moving a chip from one bin to another bin
			// ==================================================================
			else {

				jQuery.ajax({

				    type: 'POST',
				    url: ajaxurl,
				    data: {
					'action':'admin_moveChip',
					'module_type': 'page',
					'module_id': 4,
					'source_section_name': source_section,
					'source_bin_name': source_bin,
					'dest_section_name': dest_section,
					'dest_bin_name': dest_bin,
					'key_id': key_id
				    },
				    beforeSend: function(){
	
					jQuery(this).css('cursor','wait');
				    },
				    success: function(response){

					jQuery(this).css('cursor','default');

					if( response == 'OK'){

						// Read the type, id, and name of the new chip
						var chipType = 	active_chip.attr('key_type');
						var chipID = active_chip.attr('key_id');
						var chipName = active_chip.attr('key_name');

						// Read the contents of the source bin's hidden field, and parse it to a js array
						var sourceChipFieldRaw = ui.draggable.closest(".droppable_chip_list").children(".droppable_chip_list_field").val();
						var sourceChipField = jQuery.parseJSON(sourceChipFieldRaw);

						// Remove the chip from the js array, compile the array to JSON, and write it back to the source bin's hidden field
						delete sourceChipField[chipType][chipID];
						ui.draggable.closest(".droppable_chip_list").children(".droppable_chip_list_field").val( JSON.stringify(sourceChipField) );

						// Read the contents of the dest bin's hidden field, and parse it to a js array
						var destChipFieldRaw = active_bin.children('.droppable_chip_list_field').val();
						var destChipField = jQuery.parseJSON(destChipFieldRaw);

						// Add the chip to the js array, compile the array to JSON, and write it back to the dest bin's hidden field
						
						if( !destChipField[chipType]){
							// We have to do this because JavaScript doesn't have multidimensional associative arrays
							destChipField[chipType] = {};
						}

						destChipField[chipType][chipID] = chipName;
						active_bin.children('.droppable_chip_list_field').val( JSON.stringify(destChipField) );
						
						// Add new chip to dest bin's chip list. Can't use jQuery(this) because it will
						// point to the AJAX object
						active_bin.children('.chip_list').append( active_chip );

						// Snap chip's position to the place it would normally be floating in the list
						active_chip.attr("style", "position: relative;");

						// Fade-in the chip
						active_chip.css("opacity", 0.3).animate({opacity : 1.0}, 1000);
					}

					// alert('The server responded: ' + response);
				    }
				});


			}

		}

	}

}

/**
 * Makes chips in the parent page deletable.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function makeChipsDeletable(){


  	// Prevent the chip from dragging if the user clicks on the "x", otherwise the
	// chip will drag to the new bin then vanish. This also lets the user cancel a
	// delete by moving their mouse out of the "x"
	// ================================================================================

	jQuery(".draggable_chip .actions").mousedown( function() {

		jQuery(this).closest(".draggable_chip").draggable( "option", "disabled", true );
	});

	jQuery('.child_panel').mouseup( function() {

		jQuery('.draggable_chip').draggable( { disabled: false });
	});


	// Delete chip from bin when the user clicks on the "x"
	// ================================================================================

	jQuery(".draggable_chip .actions").click( function() {

		var active_chip = jQuery(this).closest(".draggable_chip");

		active_chip.draggable( "option", "disabled", true );

		var section_name = jQuery(this).closest(".key_panel").attr('name');
		var bin_name = jQuery(this).closest(".droppable_chip_list").attr('name');
		var key_id = jQuery(this).closest(".draggable_chip").attr('key_id');

		jQuery.ajax({

			type: 'POST',
			url: ajaxurl,
			data: {
			    'action':'admin_deleteChip',
			    'module_type': 'page',
			    'module_id': 4,
			    'section_name': section_name,
			    'bin_name': bin_name,
			    'key_id': key_id
			},
			beforeSend: function(){

			    jQuery(this).css('cursor','wait');
			},
			success: function(response){


			    // Read the type, id, and name of the new chip
			    var chipType = active_chip.attr('key_type');
			    var chipID = active_chip.attr('key_id');

			    // Read the contents of the source bin's hidden field, and parse it to a js array
			    var chipFieldRaw = active_chip.closest(".droppable_chip_list").children(".droppable_chip_list_field").val();
			    var chipField = jQuery.parseJSON(chipFieldRaw);

			    // Remove the chip from the js array, compile the array to JSON, and write it back to the source bin's hidden field
			    delete chipField[chipType][chipID];
			    active_chip.closest(".droppable_chip_list").children(".droppable_chip_list_field").val( JSON.stringify(chipField) );

			    jQuery(this).css('cursor','default');

			    if( response == 'OK'){
				    active_chip.fadeOut(250, "linear", function(){ active_chip.remove(); });
			    }

			}
		});


	});

}


///**
// * Processes delete chip action on an admin config page
// *
// * @version 0.1.9
// * @since 0.1.9
// *
// * @return bool/int/string/array $echo | Response varies with form and the module that owns it
// */
//
// function bpm_ajax_admin_deleteChip($unit_test=false) {
//
//
//	// Block non-admin users from running this function
//	if( !is_super_admin() ){
//		    die;
//	}
//
//	$san = new BPM_sanitize();
//
//	// Grab variables from the $_POST array and sanitize them
//	$module_type = $san->classOrFunction($_POST['module_type'], $ctrl=null, $module_type_ok, $module_type_error);
//	$module_id = $san->int($_POST['module_id'], $ctrl=null, $module_id_ok, $module_id_error);
//	$section_name = $san->classOrFunction($_POST['section_name'], $ctrl=null, $section_ok, $section_error);
//	$bin_name = $san->classOrFunction($_POST['bin_name'], $ctrl=null, $bin_ok, $bin_error);
//	$key_id = $san->int($_POST['key_id'], $ctrl=null, $key_ok, $key_error);
//
//	if(!$module_type_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing module_type. Error: $module_type_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$module_id_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing module_id. Error: $module_id_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$section_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing section name. Error: $section_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$bin_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing bin name. Error: $bin_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$key_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing key_id. Error: $key_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//
//	$result = "OK";
//
//	// Return response
//	if(!$unit_test){
//		echo $result;
//		die();	// Required by WP in AJAX mode
//	}
//	else {
//		return $result;
//	}
//
//
// } // WP action name is "wp_ajax_" + "formActionName"
// add_action('wp_ajax_admin_deleteChip', 'bpm_ajax_admin_deleteChip');
//
//
///**
// * Processes move chip action on an admin config page
// *
// * @version 0.1.9
// * @since 0.1.9
// *
// * @return bool/int/string/array $echo | Response varies with form and the module that owns it
// */
//
// function bpm_ajax_admin_moveChip($unit_test=false) {
//
//
//	// Block non-admin users from running this function
//	if( !is_super_admin() ){
//		    die;
//	}
//
//	$san = new BPM_sanitize();
//
//	// Grab variables from the $_POST array and sanitize them
//	$module_type = $san->classOrFunction($_POST['module_type'], $ctrl=null, $module_type_ok, $module_type_error);
//	$module_id = $san->int($_POST['module_id'], $ctrl=null, $module_id_ok, $module_id_error);
//
//	$source_section_name = $san->classOrFunction($_POST['source_section_name'], $ctrl=null, $source_section_ok, $source_section_error);
//	$source_bin_name = $san->classOrFunction($_POST['source_bin_name'], $ctrl=null, $source_bin_ok, $source_bin_error);
//
//	$dest_section_name = $san->classOrFunction($_POST['dest_section_name'], $ctrl=null, $dest_section_ok, $dest_section_error);
//	$dest_bin_name = $san->classOrFunction($_POST['dest_bin_name'], $ctrl=null, $dest_bin_ok, $dest_bin_error);
//
//	$key_id = $san->int($_POST['key_id'], $ctrl=null, $key_ok, $key_error);
//
//	if(!$module_type_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing module_type. Error: $module_type_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$module_id_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing module_id. Error: $module_id_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$source_section_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing source section name. Error: $source_section_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$source_bin_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing source bin name. Error: $source_bin_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$dest_section_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing dest section name. Error: $dest_section_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$dest_bin_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing dest bin name. Error: $dest_bin_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$key_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing key_id. Error: $key_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//
//	$result = "OK";
//
//	// Return response
//	if(!$unit_test){
//		echo $result;
//		die();	// Required by WP in AJAX mode
//	}
//	else {
//		return $result;
//	}
//
//
// } // WP action name is "wp_ajax_" + "formActionName"
// add_action('wp_ajax_admin_moveChip', 'bpm_ajax_admin_moveChip');
//
//
///**
// * Processes add chip action on an admin config page
// *
// * @version 0.1.9
// * @since 0.1.9
// *
// * @return bool/int/string/array $echo | Response varies with form and the module that owns it
// */
//
// function bpm_ajax_admin_addChip($unit_test=false) {
//
//
//	// Block non-admin users from running this function
//	if( !is_super_admin() ){
//		    die;
//	}
//
//	$san = new BPM_sanitize();
//
//	// Grab variables from the $_POST array and sanitize them
//	$module_type = $san->classOrFunction($_POST['module_type'], $ctrl=null, $module_type_ok, $module_type_error);
//	$module_id = $san->int($_POST['module_id'], $ctrl=null, $module_id_ok, $module_id_error);
//
//	$dest_section_name = $san->classOrFunction($_POST['dest_section_name'], $ctrl=null, $dest_section_ok, $dest_section_error);
//	$dest_bin_name = $san->classOrFunction($_POST['dest_bin_name'], $ctrl=null, $dest_bin_ok, $dest_bin_error);
//
//	$key_id = $san->int($_POST['key_id'], $ctrl=null, $key_ok, $key_error);
//
//	if(!$module_type_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing module_type. Error: $module_type_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$module_id_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing module_id. Error: $module_id_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$dest_section_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing dest section name. Error: $dest_section_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$dest_bin_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing dest bin name. Error: $dest_bin_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//	elseif(!$key_ok){
//
//	    if(!$unit_test){
//		    echo __METHOD__ . " called with bad or missing key_id. Error: $key_error";
//		    die;
//	    }
//	    else {
//		    return false;
//	    }
//	}
//
//	$result = "OK";
//
//	// Return response
//	if(!$unit_test){
//		echo $result;
//		die();	// Required by WP in AJAX mode
//	}
//	else {
//		return $result;
//	}
//
//
// } // WP action name is "wp_ajax_" + "formActionName"
// add_action('wp_ajax_admin_addChip', 'bpm_ajax_admin_addChip');
