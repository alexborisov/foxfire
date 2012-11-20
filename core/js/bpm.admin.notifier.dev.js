/**
 * BP-MEDIA ADMIN PAGE NOTIFIER JAVASCRIPT FUNCTIONS
 * Displays animated notifications inside the BP-Media admin page header
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Admin JS
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * This library is based on code originally released in "Gritter", by Jordan Boesch
 * http://boedesign.com/blog/2009/07/11/growl-for-jquery-gritter/
 *
 * ========================================================================================================
 */


(function(jQuery){


	/**
	 * Attach to the jQuery namespace, allowing the object to work a lot
	 * like a PHP class instantiated as a global singleton.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	jQuery.bpm_admin_notify = {};


	/**
	 * Sets GLOBAL display options for all notice objects
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int position | position
	 * @param string fade_in_speed | How fast notices fade in
	 * @param int fade_out_speed | How fast notices fade out
	 * @param int time | How long notices remain on the screen before fading out
	 * @param int max_items | Max notices to display in the notification area at a given time
	 */

	jQuery.bpm_admin_notify.options = {

		position: '',
		fade_in_speed: 200,
		fade_out_speed: 300,
		time: 5000,
		max_items: 5
				
	}

	
	/**
	 * Adds a notice to the notification area
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string title | Notice title
	 * @param string text | Notice text
	 * @param string image | Path to image file
	 * @param bool sticky | Make the notice sticky
	 * @param int time | Time to display notice on screen for before fading out
	 * @param string class_name | CSS class to apply to notice object
	 * @param function before_open | Function to call before opening the notice
	 * @param function after_open | Function to call after opening the notice
	 * @param function before_close| Function to call before closing the notice
	 * @param function after_close | Function to call after closing the notice
	 *
	 * @return int id | unique id of the notice object
	 */

	jQuery.bpm_admin_notify.add = function(params){
		
		try {
			return bpm_admin_notify.add( params || {} );
		}
		catch(e) {
		
			var err = 'bpm_admin_notify Error: ' + e;
			
			(typeof(console) != 'undefined' && console.error) ? 
				console.error(err, params) : 
				alert(err);
				
		}
		
	}


	/**
	 * Removes a notice from the notification area
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int id | notification object id
	 * @param array params | notice parameters
	 */

	jQuery.bpm_admin_notify.remove = function(id, params){

		bpm_admin_notify.removeSpecific(id, params || {});
	}


	/**
	 * Removes all notices from the notification area
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array params | notice parameters
	 */

	jQuery.bpm_admin_notify.removeAll = function(params){
	    
		bpm_admin_notify.stop(params || {});
	}


	/**
	 * Notification object literal
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array params | notification object parameters
	 */

	var bpm_admin_notify = {
	    
		// Public
		position: '',
		fade_in_speed: '',
		fade_out_speed: '',
		time: '',
		item_html: '',
	    
		// Private 
		_custom_timer: 0,
		_item_count: 0,
		_is_setup: 0,
		_tpl_close: '<div class="bpm_admin_notify-close"></div>',
		_tpl_item: '<div id="bpm_admin_notify-item-[[number]]" class="bpm_admin_notify-item-wrapper [[item_class]]" style="display:none"><div class="bpm_admin_notify-item">[[close]][[image]]<div class="[[class_name]]"><span class="bpm_admin_notify-title">[[username]]</span><p>[[text]]</p></div></div></div>',
		_tpl_wrap: '<div id="bpm_admin_notice_area"></div>',


		/**
		 * Adds a notice object to the notification area
		 *
		 * @version 0.1.9
		 * @since 0.1.9
		 *
		 * @param string title | Notice title
		 * @param string text | Notice text
		 * @param string image | Path to image file
		 * @param bool sticky | Make the notice sticky
		 * @param int time | Time to display notice on screen for before fading out
		 * @param string class_name | CSS class to apply to notice object
		 * @param function before_open | Function to call before opening the notice
		 * @param function after_open | Function to call after opening the notice
		 * @param function before_close| Function to call before closing the notice
		 * @param function after_close | Function to call after closing the notice
		 *
		 * @return int id | unique id of the notice object
		 */

		add: function(params){

			// Trap missing title or text
			if(!params.title || !params.text){
				throw 'You need to fill out the first 2 params: "title" and "text"'; 
			}
			
			// Set the global config options
			if(!this._is_setup){
				this._runSetup();
			}
	        
			// Basics
			var user = params.title;
			var text = params.text;
			var image = params.image || '';
			var sticky = params.sticky || false;
			var item_class = params.class_name || '';
			var position = jQuery.bpm_admin_notify.options.position;
			var time_alive = params.time || '';

			// When the last notice fades-out, the admin notice content wrapper gets removed from the
			// DOM. Check if the admin notice content wrapper is in the DOM, and add it if it isn't.

			if(jQuery('#bpm_admin_notice_area').length == 0){
				jQuery('.bpm_header_small').append(this._tpl_wrap);
			}

			this._item_count++;

			var number = this._item_count;
			var tmp = this._tpl_item;
			
			// Assign callback functions
			jQuery(['before_open', 'after_open', 'before_close', 'after_close']).each(

				function(i, val){
					bpm_admin_notify['_' + val + '_' + number] = (jQuery.isFunction(params[val])) ? params[val] : function(){}
				}

			);

			// Reset
			this._custom_timer = 0;
			
			// A custom fade time set
			if(time_alive){
				this._custom_timer = time_alive;
			}
			
			var image_str = (image != '') ? '<img src="' + image + '" class="bpm_admin_notify-image" />' : '';
			var class_name = (image != '') ? 'bpm_admin_notify-with-image' : 'bpm_admin_notify-without-image';
			
			// String replacements on the template
			tmp = this._str_replace(
				['[[username]]', '[[text]]', '[[close]]', '[[image]]', '[[number]]', '[[class_name]]', '[[item_class]]'],
				[user, text, this._tpl_close, image_str, this._item_count, class_name, item_class], tmp
			);
	        
			this['_before_open_' + number]();
			jQuery('#bpm_admin_notice_area').addClass(position).append(tmp);
			
			var item = jQuery('#bpm_admin_notify-item-' + this._item_count);
			
			item.delay(1000).animate({opacity: 'toggle', bottom: '+=15'}, this.fade_in_speed, "linear", function(){
				bpm_admin_notify['_after_open_' + number](jQuery(this));
			});
	        
			if(!sticky){
				this._setFadeTimer(item, number);
			}
			
			// Bind the hover/unhover states
			jQuery(item).bind('mouseenter mouseleave', function(event){

				if(event.type == 'mouseenter'){

					if(!sticky){ 
						bpm_admin_notify._restoreItemIfFading(jQuery(this), number);
					}

				}
				else {
					if(!sticky){
						bpm_admin_notify._setFadeTimer(jQuery(this), number);
					}
				}

				bpm_admin_notify._hoverState(jQuery(this), event.type);

			});
			
			return number;
	    
		},
		
		/**
		* If we don't have any more bpm_admin_notify notifications, get rid of the wrapper using this check
		* @private
		* @param {Integer} unique_id The ID of the element that was just deleted, use it for a callback
		* @param {Object} e The jQuery element that we're going to perform the remove() action on
		* @param {Boolean} manual_close Did we close the bpm_admin_notify dialog with the (X) button
		*/
		_countRemoveWrapper: function(unique_id, e, manual_close){
		    
			// Remove it then run the callback function
			e.remove();
			this['_after_close_' + unique_id](e, manual_close);
			
			// Check if the wrapper is empty, if it is.. remove the wrapper
			if(jQuery('.bpm_admin_notify-item-wrapper').length == 0){
				jQuery('#bpm_admin_notice_area').remove();
			}
		
		},
		
		/**
		* Fade out an element after it's been on the screen for x amount of time
		* @private
		* @param {Object} e The jQuery element to get rid of
		* @param {Integer} unique_id The id of the element to remove
		* @param {Object} params An optional list of params to set fade speeds etc.
		* @param {Boolean} unbind_events Unbind the mouseenter/mouseleave events if they click (X)
		*/
		_fade: function(e, unique_id, params, unbind_events){

			var params = params || {},
				fade = (typeof(params.fade) != 'undefined') ? params.fade : true;
				fade_out_speed = params.speed || this.fade_out_speed,
				manual_close = unbind_events;
			
			this['_before_close_' + unique_id](e, manual_close);
			
			// If this is true, then we are coming from clicking the (X)
			if(unbind_events){
				e.unbind('mouseenter mouseleave');
			}
			
			// Fade it out or remove it
			if(fade){
			
				e.animate({
					opacity: 0,
					bottom: '+=15'
				}, fade_out_speed, function(){
					e.animate({ height: 0 }, 300, "linear", function(){
						bpm_admin_notify._countRemoveWrapper(unique_id, e, manual_close);
					})
				})
				
			}
			else {
				
				this._countRemoveWrapper(unique_id, e);
				
			}
					    
		},
		
		/**
		* Perform actions based on the type of bind (mouseenter, mouseleave) 
		* @private
		* @param {Object} e The jQuery element
		* @param {String} type The type of action we're performing: mouseenter or mouseleave
		*/
		_hoverState: function(e, type){
			
			// Change the border styles and add the (X) close button when you hover
			if(type == 'mouseenter'){
		    	
				e.addClass('hover');
				
				// Show close button
				e.find('.bpm_admin_notify-close').show();
				
				// Clicking (X) makes the perdy thing close
				e.find('.bpm_admin_notify-close').click(function(){
				
					var unique_id = e.attr('id').split('-')[2];
					bpm_admin_notify.removeSpecific(unique_id, {}, e, true);
					
				});
			
			}
			// Remove the border styles and hide (X) close button when you mouse out
			else {

				e.removeClass('hover');				
			}
		    
		},
		
		/**
		* Remove a specific notification based on an ID
		* @param {Integer} unique_id The ID used to delete a specific notification
		* @param {Object} params A set of options passed in to determine how to get rid of it
		* @param {Object} e The jQuery element that we're "fading" then removing
		* @param {Boolean} unbind_events If we clicked on the (X) we set this to true to unbind mouseenter/mouseleave
		*/
		removeSpecific: function(unique_id, params, e, unbind_events){
			
			if(!e){
				var e = jQuery('#bpm_admin_notify-item-' + unique_id);
			}

			// We set the fourth param to let the _fade function know to 
			// unbind the "mouseleave" event.  Once you click (X) there's no going back!
			this._fade(e, unique_id, params || {}, unbind_events);
			
		},
		
		/**
		* If the item is fading out and we hover over it, restore it!
		* @private
		* @param {Object} e The HTML element to remove
		* @param {Integer} unique_id The ID of the element
		*/
		_restoreItemIfFading: function(e, unique_id){
			
			clearTimeout(this['_int_id_' + unique_id]);
			e.stop().css({ opacity: '' });
		    
		},
		
		/**
		* Setup the global options - only once
		* @private
		*/
		_runSetup: function(){
		
			for(opt in jQuery.bpm_admin_notify.options){
				this[opt] = jQuery.bpm_admin_notify.options[opt];
			}
			this._is_setup = 1;
		    
		},
		
		/**
		* Set the notification to fade out after a certain amount of time
		* @private
		* @param {Object} item The HTML element we're dealing with
		* @param {Integer} unique_id The ID of the element
		*/
		_setFadeTimer: function(e, unique_id){
			
			var timer_str = (this._custom_timer) ? this._custom_timer : this.time;
			this['_int_id_' + unique_id] = setTimeout(function(){ 
				bpm_admin_notify._fade(e, unique_id);
			}, timer_str);
		
		},
		
		/**
		* Bring everything to a halt
		* @param {Object} params A list of callback functions to pass when all notifications are removed
		*/  
		stop: function(params){
			
			// Callbacks (if passed)
			var before_close = (jQuery.isFunction(params.before_close)) ? params.before_close : function(){};
			var after_close = (jQuery.isFunction(params.after_close)) ? params.after_close : function(){};
			
			var wrap = jQuery('#bpm_admin_notice_area');
			before_close(wrap);
			wrap.fadeOut(function(){
				jQuery(this).remove();
				after_close();
			});
		
		},
		
		/**
		* An extremely handy PHP function ported to JS, works well for templating
		* @private
		* @param {String/Array} search A list of things to search for
		* @param {String/Array} replace A list of things to replace the searches with
		* @return {String} sa The output
		*/  
		_str_replace: function(search, replace, subject, count){
		
			var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
				f = [].concat(search),
				r = [].concat(replace),
				s = subject,
				ra = r instanceof Array, sa = s instanceof Array;
			s = [].concat(s);
			
			if(count){
				this.window[count] = 0;
			}
		
			for(i = 0, sl = s.length; i < sl; i++){
				
				if(s[i] === ''){
					continue;
				}
				
		        for (j = 0, fl = f.length; j < fl; j++){
					
					temp = s[i] + '';
					repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
					s[i] = (temp).split(f[j]).join(repl);
					
					if(count && s[i] !== temp){
						this.window[count] += (temp.length-s[i].length) / f[j].length;
					}
					
				}
			}
			
			return sa ? s : s[0];
		    
		}


	} // ENDOF: var bpm_admin_notify

	
})(jQuery);
