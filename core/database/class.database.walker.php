<?php

/**
 * BP-MEDIA DATABASE WALKER CLASS
 * Pages through database query results
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Database
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/wiki/DOCS_FOX_db_top
 *
 * ========================================================================================================
 */

abstract class FOX_db_walker {


	var $current_item;		// The index of the current item within the albumType array
	var $item_count;                // The total number of items within the template object
	var $total_item_count;		// The total number of matching items within the database
	var $items = array();           // Array of objects retrieved from the database
	var $in_the_loop;               // Boolean flag. Signals when the template is in "the loop"
	var $total_pages;		// Number of pages of items in the result set.

	// ============================================================================================================ //


	public function __construct() {}


        /**
         * Determines if there are items inside the template object
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         * @return bool | True if items. False if empty.
         */

	public function has_items() {

		// Note $current_item lags $item_count by 1, because the $items array is zero-indexed

		if ( $this->current_item + 1 < $this->item_count ) {

			return true;
		}
		elseif ( ($this->current_item + 1 == $this->item_count) && ($this->item_count > 0) ) {

			$this->rewind_items();
		}

		$this->in_the_loop = false;

		return false;
	}


        /**
         * Returns read pointer to the first item
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function rewind_items() {

		$this->current_item = 0;
	}


        /**
         * Sets "in_the_loop" flag true, and advances read pointer to the next item.
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         * @return object | query result object
         */

	public function the_item() {

		$this->in_the_loop = true;
		$result = $this->items[$this->current_item];

		$this->current_item++;

		return $result;
	}



        /**
         * Determines if there is another page of items following the currently selected page
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         * @return bool | True if page exists. False if not.
         */

	public function has_next_page(){

		if( (int)$this->ctrl_args["page"] < $this->total_pages){

			return true;
		} else {

			return false;
		}

	}



        /**
         * Determines if there is another page of items preceding the currently selected page
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         * @return bool | True if page exists. False if not.
         */

	public function has_prev_page(){

		if( (int)$this->ctrl_args["page"] > 1 ){

			return true;
		} else {

			return false;
		}

	}


} // End of class FOX_db_walker

?>