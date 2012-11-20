/**
 * FOXFIRE ADMIN DRAGGABLE CHIPS FLOATING KEY PICKER
 * Adds floating chip palettes to admin pages.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Admin JS
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */


/**
 * Opens the key manager floating palette when its icon is clicked in the parent page.
 *
 * @version 1.0
 * @since 1.0
 */

function fox_admin_keyPicker(){


	jQuery(".key_manager").click(function() {

	    var html_titleBar;

	    html_titleBar  =  '<div class="fox_title_bar_icon">';
	    html_titleBar  += '<div class="key_manager_icon"></div>';
	    html_titleBar  += '</div>';	    
	    html_titleBar  += '<div class="title_string">Key Manager</div>';

	    // Set the open position of the dialog 50 pixels from the top and 50 pixels
	    // from the left of the browser window

	    var x_pos= jQuery(document).width() - 350;
	    var y_pos = 50;

	    jQuery(".palette_content").dialog({

		    dialogClass:'fox_floating_palette',
		    modal: false,
		    autoOpen: true,
		    position: [x_pos, y_pos],

		    // In order to use the "fade" effect below, the script aliases "fox-jquery-effects-core"
		    // and "fox-jquery-effects-fade" have to be included in the page. Other possible effects:
		    // 'blind', 'clip', 'drop', 'explode', 'fold', 'puff', 'slide', 'scale', 'size', 'pulsate'

		    show: {effect: "fade", duration: 500},
		    hide: {effect: "fade", duration: 500},
		    open: function(){

			    // Because jquery.ui doesn't float the title text inside the title bar, and
			    // has it set up as a <span> its just easier to remove it, then replace
			    // it with our floating icon and floating title text

			    jQuery('.fox_floating_palette .ui-dialog-titlebar .ui-dialog-title').remove();

			    // Remove our HTML (if it exists) to prevent multiple copies getting added
			    // when the user closes the dialog and opens it again

			    jQuery('.fox_floating_palette .ui-dialog-titlebar .fox_title_bar_icon').remove();
			    jQuery('.fox_floating_palette .ui-dialog-titlebar .title_string').remove();

			    jQuery('.fox_floating_palette .ui-dialog-titlebar').prepend(html_titleBar);

			    resetFloatingPalette();

		    },
		    height: 400,
		    width: 300
	    });
	    
	});

}

/**
 * Loads HTML into the floating key manager palette and makes its chips draggable
 * to the parent page.
 *
 * @version 1.0
 * @since 1.0
 */

function resetFloatingPalette(){

	var html_chipList;

	html_chipList  =   '<div class="section">';
	    html_chipList  +=  '<div class ="title">Groups</div>';
	    html_chipList  +=  '<div class="chip_list_wrap" name="group_chips">';
		html_chipList  += '<ul class="chip_list">';
		    html_chipList  += '<li key_id="1" key_type="group" key_name="Administrators" class="group_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Administrators</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		    html_chipList  += '<li key_id="2" key_type="group" key_name="Members" class="group_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Members</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		    html_chipList  += '<li key_id="3" key_type="group" key_name="Editors" class="group_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Editors</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		    html_chipList  += '<li key_id="4" key_type="group" key_name="Paid Users" class="group_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Paid Users</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		    html_chipList  += '<li key_id="5" key_type="group" key_name="Guests" class="group_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Guests</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		html_chipList  += '</ul>';
	    html_chipList  += '</div>';
	html_chipList  +=  '</div>';

	html_chipList  +=   '<div class="section">';
	    html_chipList  +=  '<div class ="title">Keys</div>';
	    html_chipList  +=  '<div class="chip_list_wrap" name="system_chips">';
		html_chipList  += '<ul class="chip_list">';
		    html_chipList  += '<li key_id="8" key_type="token" key_name="Administrators" class="token_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Administrators</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		    html_chipList  += '<li key_id="9" key_type="token" key_name="Everyone" class="token_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Everyone</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		    html_chipList  += '<li key_id="10" key_type="token" key_name="Content Owner" class="token_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Content Owner</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		html_chipList  += '</ul>';
	    html_chipList  += '</div>';
	html_chipList  +=  '</div>';

	html_chipList  +=   '<div class="section">';
	    html_chipList  +=  '<div class ="title">System</div>';
	    html_chipList  +=  '<div class="chip_list_wrap" name="system_chips">';
		html_chipList  += '<ul class="chip_list">';
		    html_chipList  += '<li key_id="6" key_type="system" key_name="Spammers" class="system_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Spammers</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		    html_chipList  += '<li key_id="7" key_type="system" key_name="Suspended" class="system_chip draggable_chip" add_chip="1">';
		    html_chipList  += '<div class="textblock"><div class="name">Suspended</div> </div>';
		    html_chipList  += '<div class="actions"><div class="delete">x</div></div>';
		    html_chipList  += '</li>';
		html_chipList  += '</ul>';
	    html_chipList  += '</div>';
	html_chipList  +=  '</div>';

	// Fill the panel with our HTML
	jQuery('.palette_content').html(html_chipList);

	// Make the chips draggable. This has to be done *every* time the HTML is set.
	jQuery('.draggable_chip').draggable( {

		helper: 'clone',
		appendTo: 'body',
		cursor: 'move',
		revert: 'invalid',
		opacity: 0.5,
		zIndex: 2700
	});

}