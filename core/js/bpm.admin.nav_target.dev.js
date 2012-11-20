/**
 * BP-MEDIA ADMIN PAGE DYNAMIC TABLES JAVASCRIPT FUNCTIONS
 * Adds field manipulation and animation to tables within BP-Media admin pages
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

var viewMode;
var pageSubmitOK;
var slugSubmitOK;

function bpm_pageModules_navTarget(baseName, moduleID){ 

	// Remove row-striping from the target tables (the row-striping
	// function doesn't ignore hidden objects)

//	jQuery('.targetThree tbody tr').removeClass('alt');
//	jQuery('.targetTwo tbody tr').removeClass('alt');

	toggleMode(baseName, moduleID);
	
	// If the user changes the select box to a different page, check if the page
	// is available, and update the status box if necessary
	// =============================================================================

	updatePageStatus(baseName, moduleID);

	jQuery('.pageSelectBox').change( function() {

		updatePageStatus(baseName, moduleID);
	});


	// If the user changes the slug field to a different value, check if the valid
	// and/or in use, and update the status box if necessary
	// =============================================================================

	updateSlugStatus(baseName, moduleID);

	jQuery('.slugField').change( function() {

		updateSlugStatus(baseName, moduleID);
	});

}


/**
 * Switches between "page" mode and "slug" mode by toggling the visibility of the page
 * and slug tables, based on the display target selected by the user
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function toggleMode(baseName, moduleID){


	// Show the correct table based on the values the page loads from the db
	// ============================================================================

	var search = "input[name=" + baseName + "]:checked";
	var currentTargetType = jQuery(search).val();

	if(currentTargetType == "page"){

		jQuery('.targetOne').attr('target', currentTargetType );
		jQuery('.targetTwo').show();
		jQuery('.targetThree').hide();

		viewMode = "page";
	}
	else {

		jQuery('.targetOne').attr('target', currentTargetType );
		jQuery('.targetThree').show();
		jQuery('.targetTwo').hide();

		viewMode = "slug";
	}

	// If the user selects a target that uses a different navigation mode than
	// what is currently being displayed, flip to the correct mode
	// ============================================================================

	var target = ".targetOne td label input";

	jQuery(target).click( function() {

		var search = "input[name=" + baseName + "]:checked";
		var currentTargetType = jQuery(search).val();
		var previousTargetType = jQuery('.targetOne').attr('target');

		if( currentTargetType != previousTargetType ){

			// Slug mode to page mode transition
			// ===================================================
			if( (previousTargetType != 'page') && (currentTargetType == 'page') ){

				jQuery('.targetThree').hide();
				jQuery('.targetTwo').show();

				// Add a yellow background flash to draw attention
				// to the changes
				jQuery('.targetOne').attr('target', currentTargetType);
				jQuery('.targetTwo tbody tr').css('background-color', '#FFFF50');
				jQuery('.targetTwo tbody tr').animate({'background-color' : '#FFFFFF'}, 700, "linear");

				viewMode = "page";
			}
			// Page mode to slug mode transition
			// ===================================================
			else if( (previousTargetType == 'page') && (currentTargetType != 'page') ){

				jQuery('.targetTwo').hide();
				jQuery('.targetThree').show();

				// Add a yellow background flash to draw attention
				// to the changes
				jQuery('.targetOne').attr('target', currentTargetType);

				jQuery('.targetThree tbody tr').css('border-bottom-color', '#FFFF50');

				jQuery('.targetThree tbody tr.nor_override').css('background-color', '#FFFF50');
				jQuery('.targetThree tbody tr.nor_override').animate({'background-color' : '#FFFFFF', 'border-bottom-color': '#F7F7FC'}, 700, "linear");

				jQuery('.targetThree tbody tr.alt_override').css('background-color', '#FFFF50');
				jQuery('.targetThree tbody tr.alt_override').animate({'background-color' : '#F9F9FA', 'border-bottom-color': '#F7F7FC'}, 700, "linear");

				viewMode = "slug";
			}
			// Slug mode to other slug mode transition
			// ===================================================
			else {

				jQuery('.targetTwo').hide();
				jQuery('.targetThree').show();
				jQuery('.targetOne').attr('target', currentTargetType);

				updateSlugStatus(baseName, moduleID);

				viewMode = "slug";
			}

			toggleSubmitButton();			
		}


	});

}



/**
 * When operating in page mode, checks if the page selected by the user is currently being used
 * as a target by another BP-Media or BuddyPress screen. Based on whether the page is available
 * or not, it adds/removes HTML from the admin page DOM to display the correct page status.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function updatePageStatus(baseName, moduleID){

	// Declaring blocks of HTML one div per line and reassembling with the += operator
	// makes them much easier to read and update

	var html_pageAvailable;

	html_pageAvailable  = '<div class="bppm_pageValidator page_available">';
	    html_pageAvailable += '<div class="icon_available"></div>';
	    html_pageAvailable += '<div class="message_a">';
		html_pageAvailable += 'This WordPress page is';
	    html_pageAvailable += '</div>';
	    html_pageAvailable += '<div class="message_b">';
		html_pageAvailable += 'available for use.';
	    html_pageAvailable += '</div>';
	html_pageAvailable += '</div>';

	var html_pageInUse;

	html_pageInUse  = '<div class="bppm_pageValidator page_inuse">';
	    html_pageInUse += '<div class="icon_inuse"></div>';
	    html_pageInUse += '<div class="message_a">';
		html_pageInUse += 'This WordPress page is currently';
	    html_pageInUse += '</div>';
	    html_pageInUse += '<div class="message_b">';
		html_pageInUse += 'used by ';
	    html_pageInUse += '</div>';
	html_pageInUse += '</div>';


	// Show the correct page status based on the WP page id the admin page loads
	// from the db and the pages currently in use on the system
	// =============================================================================

	varName = baseName + "[targetPage]";

	var search = "option[name=" + varName + "]:selected";
	var page_id = jQuery(search).val();
	var status = jQuery('.pageStatus').attr('status');

	jQuery.ajax({

	    type: 'POST',
	    url: ajaxurl,
	    data: {
		'action':'admin_checkPageStatus',
		'page_id': page_id
	    },
	    dataType: "json",
	    beforeSend: function(){

		jQuery(this).css('cursor','wait');
	    },
	    success: function(response){

		jQuery(this).css('cursor','default');

		if( (response.exists === false) || (response.active === false) ){

			pageSubmitOK = true;

			// Clear any existing HTML inside the status box
			jQuery('.page_inuse').remove();
			jQuery('.page_available').remove();

			// Add new HTML and fade-in the status
			jQuery('.pageStatus').append(html_pageAvailable);
			jQuery('.page_available').hide();
			jQuery('.page_available').delay(300).fadeIn(500, "linear");

		}
		else {

			pageSubmitOK = false;

			jQuery('.page_inuse').remove();
			jQuery('.page_available').remove();
			jQuery('.pageStatus').append(html_pageInUse);

			// Add name of the conflicting page to the description text
			jQuery('.page_inuse .message_b').append(response.plugin_name + '->' + '"' + response.component_name + '"');

			jQuery('.page_inuse').hide();
			jQuery('.page_inuse').delay(300).fadeIn(500, "linear");

		}

		toggleSubmitButton();

	    }
	});

}

/**
 * When operating in slug mode, checks if the slug entered by the user is valid, and if it is currently
 * being used as a target by another BP-Media or BuddyPress screen. Based on whether the slug is available
 * and/or valid, it adds/removes HTML from the admin page DOM to display the correct slug status.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function updateSlugStatus(baseName, moduleID){


	// Declaring blocks of HTML one div per line and reassembling with the += operator
	// makes them much easier to read and update

	var html_slugValid;

	html_slugValid  = '<div class="bppm_pageValidator slug_valid">';
	    html_slugValid += '<div class="icon_available"></div>';
	    html_slugValid += '<div class="message_a">';
		html_slugValid += 'This slug is valid and';
	    html_slugValid += '</div>';
	    html_slugValid += '<div class="message_b">';
		html_slugValid += 'available for use.';
	    html_slugValid += '</div>';
	html_slugValid += '</div>';

	var html_slugFail;

	html_slugFail  = '<div class="bppm_pageValidator slug_fail">';
	    html_slugFail += '<div class="icon_fail"></div>';
	    html_slugFail += '<div class="message_a">';
		html_slugFail += 'This is not a valid';
	    html_slugFail += '</div>';
	    html_slugFail += '<div class="message_b">';
		html_slugFail += 'slug name.';
	    html_slugFail += '</div>';
	html_slugFail += '</div>';

	var html_slugInUse;

	html_slugInUse  = '<div class="bppm_pageValidator slug_inuse">';
	    html_slugInUse += '<div class="icon_inuse"></div>';
	    html_slugInUse += '<div class="message_a">';
		html_slugInUse += 'This slug is valid but is being';
	    html_slugInUse += '</div>';
	    html_slugInUse += '<div class="message_b">';
		html_slugInUse += 'used by ';
	    html_slugInUse += '</div>';
	html_slugInUse += '</div>';

	search = "input[name=" + baseName + "]:checked";
	targetType = jQuery(search).val();

	varName = baseName + "[targetSlug]";
	search = "input:text[name=" + varName + "]";
	slugName = jQuery(search).val();

	var status = jQuery('.slugStatus').attr('status');

	if( !slugIsValid(slugName) ){

		slugSubmitOK = false;

		if( status != "fail"){

			jQuery('.slugStatus').attr('status', "fail");

			jQuery('.slug_valid').remove();
			jQuery('.slug_inuse').remove();

			jQuery('.slugStatus').append(html_slugFail);
			jQuery('.slug_fail').hide();
			jQuery('.slug_fail').delay(300).fadeIn(500, "linear");
		}

		toggleSubmitButton();
	}
	else {

		jQuery.ajax({

		    type: 'POST',
		    url: ajaxurl,
		    data: {
			'action':'admin_checkSlugStatus',
			'location': targetType,
			'slug': slugName
		    },
		    dataType: "json",
		    beforeSend: function(){

			jQuery(this).css('cursor','wait');
		    },
		    success: function(response){

			jQuery(this).css('cursor','default'); 

			if( response.exists === false ){

				slugSubmitOK = true;

				if(status != "valid"){

					// Store the status to the DOM as an attribute to avoid running the
					// fade-in effect again if the user switches to another valid slug
					jQuery('.slugStatus').attr('status', "valid");

					// Clear any existing HTML inside the status box
					jQuery('.slug_inuse').remove();
					jQuery('.slug_fail').remove();

					// Add new HTML and fade-in the status
					jQuery('.slugStatus').append(html_slugValid);
					jQuery('.slug_valid').hide();
					jQuery('.slug_valid').delay(300).fadeIn(500, "linear");
				}
			}
			else if( response.module_id == moduleID ){

				slugSubmitOK = true;

				if(status != "self"){

					// Store the status to the DOM as an attribute to avoid running the
					// fade-in effect again if the user switches to another valid slug
					jQuery('.slugStatus').attr('status', "self");

					// Clear any existing HTML inside the status box
					jQuery('.slug_inuse').remove();
					jQuery('.slug_fail').remove();
				}
			}
			else {

				slugSubmitOK = false;

				if(status != "inuse"){

					jQuery('.slugStatus').attr('status', "inuse");
					jQuery('.slug_valid').remove();
					jQuery('.slug_fail').remove();
					jQuery('.slugStatus').append(html_slugInUse);

					// Add name of the conflicting slug to the description text
					jQuery('.slug_inuse .message_b').append(response.plugin_name +'->' + '"' + response.component_name + '"');

					jQuery('.slug_inuse').hide();
					jQuery('.slug_inuse').delay(300).fadeIn(500, "linear");
				}
			}

			toggleSubmitButton();
		    }
		    
		});

	}

}

/**
 * Enables or disables the submit button depending on whether the entered target
 * data passes validation in the browser
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function toggleSubmitButton(){
    
    if( viewMode == "page"){

	    if(pageSubmitOK == true){

		    jQuery('.bpm-validator-button').removeAttr('disabled');
	    }
	    else {
		    jQuery('.bpm-validator-button').attr('disabled', "disabled");
	    }

    }
    else {

	    if(slugSubmitOK == true){

		    jQuery('.bpm-validator-button').removeAttr('disabled');
	    }
	    else {
		    jQuery('.bpm-validator-button').attr('disabled', "disabled");
	    }
    }

}


/**
 * Checks a slug only contains [a-z], [A-Z], [_-]. Fails on leading and internal spaces,
 * passes on trailing spaces. Matches the behavior of the BPM_sanitize::slug() sanitizer.
 *
 * @version 0.1.9
 * @since 0.1.9
 *
 * @param slug string | Slug string to test
 * @return bool | True if slug is valid. False if not.
 */

function slugIsValid(slug){

	var check = slug.replace(/\s+$/, '');

	if( /[^a-zA-Z\d_-]/.test(check) ){
	    
		return false;
	}
	else {
		return true;
	}    
}