
/**
 * BP-MEDIA ADMIN PAGE "ALBUM MODULES" JAVASCRIPT FUNCTIONS
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

function bpm_albumModules_types(moduleID) {


	// Apply initial striping to rows
	// ==============================

	jQuery('.level_row:even').removeClass('level_row_odd').addClass('level_row_even');
	jQuery('.level_row:odd').removeClass('level_row_even').addClass('level_row_odd');


	// Make all the album type boxes start in the closed state
	// =======================================================

	jQuery('.item').attr('id', 'closed');
	jQuery(".item .openCloseIdentifier").hide();


	// Panel dialog boxes
	// ================================================================================

	var panelCreateOpts =	{   modal: true,
				    autoOpen: false,
				    height: 370,
				    width: 680,
				    overlay: {background: "url(img/modal.png) repeat"}
				};

	var panelEditOpts =	{   modal: true,
				    autoOpen: false,
				    height: 400,
				    width: 680,
				    overlay: {background: "url(img/modal.png) repeat"}
				};

	var panelDeleteOpts =	{   modal: true, 
				    autoOpen: false,
				    height: 440,
				    width: 470,
				    overlay: {background: "url(img/modal.png) repeat"}
				};


	// Create album type
	// ------------------------------

	jQuery("#panel_create").dialog(panelCreateOpts);

	jQuery(".bpm_albumtype_list .new_albumtype .bpm_button").click( function() {

	    jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    'action':'bpm_admin_getDialog',
		    'tree_name':'page_album_modules',
		    'class_name':'BPM_admin_ajaxDialogs',
		    'method_name':'panelCreate',
		    'params':	{   'module_id': moduleID
				}
		},
		beforeSend: function(){
		},
		success: function(response){
		    jQuery("#panel_create").html(response);
		    jQuery("#panel_create").dialog("open");
		}
	    });

	    return false;

	 });

	// Edit album type
	// ------------------------------

	jQuery("#panel_edit").dialog(panelEditOpts);

	jQuery(".item .header .actions .buttons .edit ").click( function() {

	    var type_id = jQuery(this).closest(".item").attr('bpm_type_id');

	    jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    'action':'bpm_admin_getDialog',
		    'tree_name':'page_album_modules',
		    'class_name':'BPM_admin_ajaxDialogs',
		    'method_name':'panelEdit',
		    'params':	{   'module_id': moduleID,
				    'type_id': type_id
				}
		},
		beforeSend: function(){
		},
		success: function(response){
		    //alert(response);
		    jQuery("#panel_edit").html(response);
		    jQuery("#panel_edit").dialog("open");
		}
	    });

	    return false;

	 });

	// Delete album type
	// ------------------------------

	jQuery("#panel_delete").dialog(panelDeleteOpts);

	jQuery(".item .header .actions .buttons .delete").click( function() {

	    var type_id = jQuery(this).closest(".item").attr('bpm_type_id');

	    jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    'action':'bpm_admin_getDialog',
		    'tree_name':'page_album_modules',
		    'class_name':'BPM_admin_ajaxDialogs',
		    'method_name':'panelDelete',
		    'params':	{   'module_id': moduleID,
				    'type_id': type_id
				}
		},
		beforeSend: function(){
		},
		success: function(response){
		    //alert(response);
		    jQuery("#panel_delete").html(response);
		    jQuery("#panel_delete").dialog("open");
		}
	    });

	    return false;

	 });


	// Row dialog boxes
	// ================================================================================

	var rowCreateOpts = {	modal: true,
				autoOpen: false,
				height: 450,
				width: 680,
				overlay: {background: "url(img/modal.png) repeat"}
			    };

	var rowEditOpts =   {	modal: true,
				autoOpen: false,
				height: 450,
				width: 680,
				overlay: {background: "url(img/modal.png) repeat"}
			    };

	var rowDeleteOpts = {	modal: true,
				autoOpen: false,
				height: 440,
				width: 470,
				overlay: {background: "url(img/modal.png) repeat"}
			    };


	// Create type level
	// ------------------------------

	jQuery("#row_create").dialog(rowCreateOpts);

	jQuery(".bpm_albumtype_list .item .level_panel .new_level .bpm_button").click( function() {

	    jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    'action':'bpm_admin_getDialog',
		    'tree_name':'page_album_modules',
		    'class_name':'BPM_admin_ajaxDialogs',
		    'method_name':'rowCreate',
		    'params':	{   'module_id': moduleID
				}
		},
		beforeSend: function(){
		},
		success: function(response){
		    jQuery("#row_create").html(response);
		    jQuery("#row_create").dialog("open");
		}
	    });

	    return false;

	 });

	// Edit type level
	// ------------------------------

	jQuery("#row_edit").dialog(rowEditOpts);

	jQuery(".item .level_panel .level_list_wrap ul.level_list .level_row .actions .edit").click( function() {

	    var type_id = jQuery(this).closest(".item").attr('bpm_type_id');
	    var level_id = jQuery(this).closest(".level_row").attr('bpm_level_id');

	    jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    'action':'bpm_admin_getDialog',
		    'tree_name':'page_album_modules',
		    'class_name':'BPM_admin_ajaxDialogs',
		    'method_name':'rowEdit',
		    'params':	{   'module_id': moduleID,
				    'type_id': type_id,
				    'level_id': level_id
				}
		},
		beforeSend: function(){
		},
		success: function(response){
		    jQuery("#row_edit").html(response);
		    jQuery("#row_edit").dialog("open");
		}
	    });

	    return false;

	 });

	// Delete type level
	// ------------------------------

	jQuery("#row_delete").dialog(rowDeleteOpts);

	jQuery(".item .level_panel .level_list_wrap ul.level_list .level_row .actions .delete").click( function() {

	    var type_id = jQuery(this).closest(".item").attr('bpm_type_id');
	    var level_id = jQuery(this).closest(".level_row").attr('bpm_level_id');

	    jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
		    'action':'bpm_admin_getDialog',
		    'tree_name':'page_album_modules',
		    'class_name':'BPM_admin_ajaxDialogs',
		    'method_name':'rowDelete',
		    'params':	{   'module_id': moduleID,
				    'type_id': type_id,
				    'level_id': level_id
				}
		},
		beforeSend: function(){
		},
		success: function(response){
		    jQuery("#row_delete").html(response);
		    jQuery("#row_delete").dialog("open");
		}
	    });

	    return false;

	 });



	// Slide open and closed menus
	// ================================================================================

	jQuery(".item .footer .action").click( function() {

	    var type_id = jQuery(this).attr('bpm_type_id');

	    if(jQuery(this).closest(".footer").children(".openCloseIdentifier").is(":hidden")){

		    // Because we're using floating divs, we have to manually calculate the height of the panel,
		    // as jQuery's box height function will return the wrong height

		    var open_height = ( jQuery(this).closest(".item").find(".level_panel .level_list_wrap li").length * 20 + 90 ) + "px";

		    jQuery(this).closest(".item").children(".level_panel").animate({"height": open_height}, {duration: 200 });
		    jQuery(this).closest(".item").removeAttr('id', 'closed');
		    jQuery(this).closest(".item").attr('id', 'open');
		    jQuery(this).closest(".footer").children(".openCloseIdentifier").show();
		    jQuery(this).closest(".level_list").sortable();
	    }
	    else{

		    jQuery(this).closest(".item").children(".level_panel").animate({height: "0px"}, 500 );
		    jQuery(this).closest(".item").removeAttr('id', 'open');
		    jQuery(this).closest(".item").attr('id', 'closed');
		    jQuery(this).closest(".footer").children(".openCloseIdentifier").hide();
	    }

	});

	// Make rows sortable
	// ================================================================================

	jQuery('.level_list').sortable({

	    cursor: 'move',
	    helper: 'clone',
	    opacity: 0.5,
	    delay: 100,
	    start: function(){
	    },
	    stop: function(){

		// Post AJAX request to server

		var sorted = jQuery(this).sortable('toArray');
		var type_id = jQuery(this).attr('bpm_type_id');

		jQuery.ajax({

		    type: 'POST',
		    url: ajaxurl,
		    data: {
			'action':'admin_albumTypesLevelSort',
			'module_id': moduleID,
			'type_id': type_id,
			'levels': sorted
		    },
		    beforeSend: function(){
			// Set the cursor
			jQuery(this).css('cursor','wait');
		    },
		    success: function(response){

			// Reset the cursor
			jQuery(this).css('cursor','default');

			// Re-apply row striping
			jQuery('.level_row:even').removeClass('level_row_odd').addClass('level_row_even');
			jQuery('.level_row:odd').removeClass('level_row_even').addClass('level_row_odd');

			// alert('The server responded: ' + response);
		    }
		});
	    }
	});

}