
/**
 * BP-MEDIA ADMIN PAGE "SELF TEST" JAVASCRIPT FUNCTIONS
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

function bpm_selfTest() {
    

	// Load the unit test results via AJAX, then replace the spinner
	// with the results text block
	// ===================================================================

	jQuery.ajax({

	    type: 'POST',
	    url: ajaxurl,
	    data: {
		'action':'admin_selfTest'
	    },
	    beforeSend: function(){
		// Set the cursor
		jQuery(this).css('cursor','wait');
	    },
	    success: function(response){

		//alert('The server responded: ' + response);

		// Reset the cursor
		jQuery(this).css('cursor','default');

		// Remove the spinner
		jQuery('.bpm_terminal_box .spinner').remove();

		// Add the results
		var html_text;

		html_text  = '<div class="text">';
		    html_text += response;
		html_text += '</div>';

		jQuery('.bpm_terminal_box').append(html_text);

		// Clear the timer event (otherwise it will throw an error when
		// we remove the 'test_timer' id from the DOM)
		window.clearTimeout(SD);
		
	    }

	});

}

var sec = 1;   // set starting seconds
var min = 0;   // set starting minutes
var hour = 0;   // set starting hours

function testTimer() {

	sec++;

	if( sec == 60 ){
		sec = 0;
		min = min + 1;
	}
	else {
		min = min;
	}

	if( min == 60 ){
		min = 0;
		hour = hour + 1;
	}
	else {
		hour = hour;
	}

	if( sec<=9 ){
		print_sec = "0" + sec;
	}
	else{
		print_sec = sec;
	}

	if( min<=9 ){
		print_min = "0" + min;
	}
	else{
		print_min = min;
	}

	if( hour<=9 ){
		print_hour = "0" + hour;
	}
	else{
		print_hour = hour;
	}

	time = print_hour + " : " + print_min + " : " + print_sec;


	// Modify the test_timer div to read the timer's count
	document.getElementById('test_timer').innerHTML = time;
	
	// Add a timeout event to run the function again in 1 second
	SD = window.setTimeout("testTimer();", 1000);

}

