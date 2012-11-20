<?php

/**
 * FOXFIRE WORDPRESS ABSTRACTION CLASS
 * Provides abstraction functions to interface with WordPress, simplifying upgrading FoxFire
 * when WordPress makes changes to their codebase.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage WordPress Abstraction
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_wp {
    
	/**
	 * Returns the current WordPress version
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | WordPress Version
	 */

	public function getWPVersion() {
	    
		global $wp_version;
		return $wp_version;
	}
	
	/**
	 * Checks if WordPress is Multisite
	 * 
	 * @version 1.0
	 * @since 1.0
	 * @return string | True if Multisite. False if not.
	 */
	
	public function getWPMultisite() {
	    
	    if ( is_multisite() ) {
		                return true;
		}
		else {
		                return false;
		}
	}

	/**
	 * Checks if the user us currently viewing a screen on the admin back-end
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | True if on admin screen. False if not.
	 */

	public function isAdminScreen() {

		// This WP function does NOT check if the user has administrator rights. It
		// checks if the user is viewing an admin screen. It's a fantastic example
		// of an ambiguous function name that could cause major security problems.
	    
		return is_admin();

	}


	/**
	 * Adds a page to the WordPress installation. Used to create a page for the plugin, so that
	 * it can be added to the top level site menu.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $title | Title for the created page
	 * @param string $slug | Slug for the created page (must be globally unique)
	 * @return bool/int $post_id | False on failure. Unique id of the created post on success.
	 */

	public function addPage($title, $slug) {

		global $wpdb;

		// Make sure that we're working with the root blog, no matter which dashboard the admin screens are being run on
		if ( !empty( $wpdb->blogid ) && ( $wpdb->blogid != bp_get_root_blog_id() ) && ( !defined( 'BP_ENABLE_MULTIBLOG' ) ) ) {
			switch_to_blog( bp_get_root_blog_id() );
		}

		$data = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_title' => ucwords( $title ),
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_name'=>$slug
		);

		$post_id = wp_insert_post($data);

		if($post_id){
			return $post_id;
		}
		else {
			return false;
		}

	}


	/**
	 * Deletes a page from the WordPress installation. Used during uninstallation to remove
	 * any pages created by the plugin.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $post_id | WordPress database id for the page
	 * @return bool $result | False on failure. True on success.
	 */

	public function deletePage($post_id) {

		global $wpdb;

		// Make sure that we're working with the root blog, no matter which dashboard the admin screens are being run on
		if ( !empty( $wpdb->blogid ) && ( $wpdb->blogid != bp_get_root_blog_id() ) && ( !defined( 'BP_ENABLE_MULTIBLOG' ) ) ) {
			switch_to_blog( bp_get_root_blog_id() );
		}

		$delete_ok = wp_delete_post($post_id, $force_delete=true );

		if($delete_ok){
			return true;
		}
		else {
			return false;
		}

	}


	/**
	 * Gets the names and ID's of all pages on the WordPress installation.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $post_id | WordPress database id for the page
	 * @return bool $result | False on failure. True on success.
	 */

	public function getAllPages() {

		global $wpdb;

		// Make sure that we're working with the root blog, no matter which dashboard the admin screens are being run on
		if ( !empty( $wpdb->blogid ) && ( $wpdb->blogid != bp_get_root_blog_id() ) && ( !defined( 'BP_ENABLE_MULTIBLOG' ) ) ) {
			switch_to_blog( bp_get_root_blog_id() );
		}

		$pages = get_pages();

		$result = array();

		foreach($pages as $page){

		    $result[$page->post_name] = $page->ID;
		}
		unset($page);

		return $result;

	}

	/**
	 * Returns a flat array of the site's page hierarchy
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array/exception | Exception on error. Page hierarchy as flat array on success.
	 */

	public function getPageHierarchy() {

		global $wpdb;

		// In this case we want to get the pages for the currently active blog,
		// so we don't switch to the site's root blog

		$sql = "SELECT ID, post_name, post_parent FROM $wpdb->posts WHERE post_type = 'page' AND post_status != 'auto-draft'";
		$pages = $wpdb->get_results($sql);

		// Trap any database errors
		$sql_error = mysql_error($wpdb->dbh);

		if($sql_error){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Database error",
				'data'=>array($sql, $sql_error),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		// Spin the SQL server's output into a useful format
		$result = array();
		
		foreach($pages as $page){

			$result[$page->ID] = array( "parent"=>$page->post_parent, "slug"=>$page->post_name);
		}
		unset($page);

		return $result;

	}


	/**
	 * Sets the HTTP response code returned by the web server, and sets WordPress'
	 * internal variables to the correct states for a given response code.
	 *
	 * @link http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int $code | Response code.
	 * @return bool/exception | Exception on failure. True on success.
	 */

	public function setRequestStatus($code) {

		global $wp_query;

		switch($code){

			case 200 : {

				// OK
				// =========================================================================
				// Standard response for successful HTTP requests. The actual response
				// will depend on the request method used. In a GET request, the response
				// will contain an entity corresponding to the requested resource. In a
				// POST request the response will contain an entity describing or containing
				// the result of the action.

				$text = "OK";
				$wp_query->is_page = true;
				$wp_query->is_404 = false;

			} break;

			case 301 : {

				// MOVED PERMANENTLY
				// =========================================================================
				// The requested page has been permanently moved to a new location. When the
				// server returns this response (as a response to a GET or HEAD request), it
				// automatically forwards the requestor to the new location. You should use
				// this code to let search engines know that a page or site has permanently
				// moved to a new location.

				$text = "Moved Permanently";
				$wp_query->is_page = true;
				$wp_query->is_404 = false;

			} break;

			case 304 : {

				// NOT MODIFIED
				// =========================================================================
				// The requested page hasn't been modified since the last request. When the
				// server returns this response, it doesn't return the contents of the page.
				// You should configure your server to return this response (called the
				// If-Modified-Since HTTP header) when a page hasn't changed since the last
				// time the requestor asked for it. This saves you bandwidth and overhead
				// because your server can tell search engines that a page hasn't changed
				// since the last time it was crawled.

				$text = "Not Modified";
				$wp_query->is_page = true;
				$wp_query->is_404 = false;

			} break;

			case 307 : {

				// TEMPORARY REDIRECT
				// =========================================================================
				// The server is currently responding to the request with a page from a
				// different location, but the requestor should continue to use the original
				// location for future requests. This code is similar to a 301 in that for a
				// GET or HEAD request, it automatically forwards the requestor to a different
				// location, but you shouldn't use it to tell search engines that a page or
				// site has moved because they will continue to crawl and index the
				// original location.

				$text = "Temporary Redirect";
				$wp_query->is_page = true;
				$wp_query->is_404 = false;

			} break;

			case 401 : {

				// NOT AUTHORIZED
				// =========================================================================
				// The request requires authentication. A server returns this status when
				// a user or search engine who is not authenticated tries to access a
				// resource that requires authentication. Also used with API's to tell the
				// requestor that they're not allowed to access the resource until they
				// present a valid API token.

				$text = "Not Authorized";
				$wp_query->is_page = false;
				$wp_query->is_404 = false;

			} break;

			case 403 : {

				// FORBIDDEN
				// =========================================================================
				// The request was a legal request, but the server is refusing to respond
				// to it. Used to tell search engines they are absolutely, positively, not
				// allowed to crawl this page.

				$text = "Forbidden";
				$wp_query->is_page = false;
				$wp_query->is_404 = false;

			} break;

			case 404 : {

				// NOT FOUND
				// =========================================================================
				// The server can't find the requested resource or page.
			    
				$text = "Not Found";
				$wp_query->is_page = false;
				$wp_query->is_404 = true;

			} break;

			case 410 : {

				// GONE
				// =========================================================================
				// The server returns this response when the requested resource has been
				// permanently removed. It is similar to a 404 (Not found) code, but is
				// sometimes used in the place of a 404 for resources that used to exist but
				// no longer do. If the resource has permanently moved, you should use a 301
				// to specify the resource's new location. Using this response code tells
				// search engines that they should drop this content from their index and
				// flush it from their cache.

				$text = "Gone";
				$wp_query->is_page = false;
				$wp_query->is_404 = false;

			} break;

			case 429 : {

				// TOO MANY REQUESTS
				// =========================================================================
				// The user has sent too many requests in a given amount of time. This request
				// code is typically used to ask search engines crawling a site to reduce their
				// page request rate to avoid overloading the server. It is also used with REST
				// API's to alert the requestor that they should reduce their query rate.

				$text = "Too Many Requests";
				$wp_query->is_page = true;
				$wp_query->is_404 = false;

			} break;

			case 509 : {

				// BANDWIDTH LIMIT EXCEEDED
				// =========================================================================
				// This status code is not specified in any RFCs, but is used by many content
				// hosting sites to indicate that a resource has been temporarily removed
				// because it is consuming too much bandwidth.

				$text = "Bandwith Limit Exceeded";
				$wp_query->is_page = true;
				$wp_query->is_404 = false;

			} break;

			default: {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Unrecognized response code",
					'data'=>$code,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));				
			}

		}

		$protocol = $_SERVER["SERVER_PROTOCOL"];

		if( ($protocol != 'HTTP/1.1') && ($protocol != 'HTTP/1.0') ){

			$protocol = 'HTTP/1.0';			
		}

		$status_header = "$protocol $code $text";

		header($status_header, true, $code);

		return true;

	}


} // End of class FOX_wp

?>