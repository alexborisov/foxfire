/**
 * BP-MEDIA ADMIN DRAGGABLE CHIPS JAVASCRIPT FUNCTIONS
 * Adds draggable chips to admin pages. This version of the class enqueues all changes in hidden fields in
 * the DOM, which are processed as a single operation when the form is submitted.
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
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

function fox_admin_draggableChips(){


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

			var chipType = 	active_chip.attr('key_type');
			var chipID = active_chip.attr('key_id');
			var chipName = active_chip.attr('key_name');

			ui.draggable.draggable( 'option', 'revert', false );


			// CASE 1: Adding a chip from a floating palette
			// ==================================================================
			if( ui.draggable.attr('add_chip') == "1" ) {	// Floating palettes set the attribute
									// 'add_chip' on all their chips

				// Read the contents of the target bin's hidden field, and parse it to a js array
				var chipFieldRaw = active_bin.children('.droppable_chip_list_field').val()
				var chipField = jQuery.parseJSON(chipFieldRaw);

				// Add the chip to the js array, compile the array to JSON, and write it back to the bin's hidden field

				// We have to check first if the array exists and then if the key exists because JavaScript doesn't have
				// multidimensional associative arrays

				if(!chipField) {

					chipField = {};
				}

				if(!chipField[chipType]){

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

			// CASE 2: Moving a chip from one bin to another bin
			// ==================================================================
			else {

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

				// We have to check first if the array exists and then if the key exists because JavaScript doesn't have
				// multidimensional associative arrays

				if(!destChipField) {
				    
					destChipField = {};
				}
				if(!destChipField[chipType]){
					
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

		// Read the type, id, and name of the new chip
		var chipType = active_chip.attr('key_type');
		var chipID = active_chip.attr('key_id');

		// Read the contents of the source bin's hidden field, and parse it to a js array
		var chipFieldRaw = active_chip.closest(".droppable_chip_list").children(".droppable_chip_list_field").val();
		var chipField = jQuery.parseJSON(chipFieldRaw);

		// Remove the chip from the js array, compile the array to JSON, and write it back to the source bin's hidden field
		delete chipField[chipType][chipID];
		active_chip.closest(".droppable_chip_list").children(".droppable_chip_list_field").val( JSON.stringify(chipField) );

		active_chip.fadeOut(250, "linear", function(){ active_chip.remove(); });
		
	});

}