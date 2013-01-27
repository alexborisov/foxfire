<?php

/**
 * FOXFIRE BUDDYPRESS ABSTRACTION CLASS
 * Provides abstraction functions to interface with BuddyPress, simplifying upgrading FoxFire
 * when BuddyPress makes changes to their codebase.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage BuddyPress Abstraction
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_bp {


	/**
	 * Returns the current BuddyPress version
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string $result | current BuddyPress version as string
	 */

	public function getBPVersion(){

		return BP_VERSION;
	}


	/**
	 * Returns the current logged-in user_id
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return int $user_id | current logged-in user_id
	 */

	public function getLoggedInUserID(){

		global $bp;
		return $bp->loggedin_user->id;
	}


	/**
	 * Returns the current displayed user_id
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return int $user_id | current displayed user_id
	 */

	public function getDisplayedUserID(){

		global $bp;
		return $bp->displayed_user->id;
	}


	/**
	 * Determines if the system is running BP 1.5 or greater
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool $result | True if BP 1.5 or greater. False if not.
	 */

	public function isOnePointFive(){

		if( BP_DB_VERSION > 3816 ){
			return true;
		}
		else {
			return false;
		}

	}


	/**
	 * Checks if a WordPress page is owned by FoxFire, BuddyPress, or an external plugin
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int $post_id | post_id of the WordPress page to search
	 * @return bool/array $result | False on failure. Data array on success.
	 */

	public function getPageOwner($post_id) {


		global $wpdb, $fox;

		$bp_components = array (

			"activate"	=> __('Activate',"foxfire"),
			"activity"	=> __('Activity',"foxfire"),
			"blogs"		=> __('Blogs',"foxfire"),
			"forums"	=> __('Forums',"foxfire"),
			"friends"	=> __('Friends',"foxfire"),
			"groups"	=> __('Groups',"foxfire"),
			"members"	=> __('Members',"foxfire"),
			"messages"	=> __('Messages',"foxfire"),
			"register"	=> __('Register',"foxfire"),
			"settings"	=> __('Settings',"foxfire"),
			"xprofile"	=> __('Profile',"foxfire")
		);

		// Make sure that we're working with the root blog, no matter which dashboard the admin screens are being run on
		if ( !empty( $wpdb->blogid ) && ( $wpdb->blogid != bp_get_root_blog_id() ) && ( !defined( 'BP_ENABLE_MULTIBLOG' ) ) ) {
			switch_to_blog( bp_get_root_blog_id() );
		}


		// Build the "active components" array
		// ==================================================================

		$bp_active_components = array(

			// The "register" and "activate" components are always active, but
			// are not present in BP's active components option
			"register"=>1,
			"activate"=>1
		);

		$bp_active_components_db = bp_get_option( 'bp-active-components' );

		if( is_array($bp_active_components_db) ){   // Handle empty option

			foreach( $bp_active_components_db as $key => $fake_var){

				$bp_active_components[$key] = true;
			}
			unset($key, $fake_var);

		}


		// Fetch BP's component pages list, and scrub any broken key=>val
		// pairs from the data array (as BuddyPress does)
		// ==================================================================

		$bp_pages = bp_get_option( 'bp-pages' );

		if( $bp_pages && is_array( $bp_pages ) ) {

			foreach( (array)$bp_pages as $component_name => $page_id ) {

				if( empty( $component_name ) || empty( $page_id ) ) {

					unset( $bp_pages[$component_name] );
				}

			}

		}

		// Determine if other plugins are adding, removing, or not affecting
		// the page_id that we're checking
		// ==================================================================

		// NOTE: A badly designed plugin could also inject itself into BP's router
		// using the 'bp_core_get_directory_pages' filter in bp-core-filters.php
		// line 168. However, by the time the data reaches this filter, the page_id's
		// have already been converted into slugs.

		$bp_pages_filtered = apply_filters( 'bp_core_get_directory_page_ids', $bp_pages );

		if( array_search($post_id, $bp_pages) !== false){

			$in_original = true;
		}
		else {
			$in_original = false;
		}

		if( array_search($post_id, $bp_pages_filtered) !== false){

			$in_filtered = true;
		}
		else {
			$in_filtered = false;
		}


		// CASE 1: BuddyPress owns the page
		// ==================================================================
		if( $in_original && $in_filtered ){

			$component_slug = array_search($post_id, $bp_pages);

			$result = array(
					"exists"=>true,
					"slug"=>$component_slug,
					"plugin_name"=>__('BuddyPress',"foxfire")
			);

			// Look-up the component's name based on its slug

			$component_name = $bp_components[$component_slug];

			if($component_name){

				$result["component_name"] = $component_name;
				$result["module_slug"] = null;
				$result["module_id"] = null;
			}
			else {

				$result["component_name"] = __('Unknown',"foxfire");
				$result["module_slug"] = null;
				$result["module_id"] = null;
			}

			// Check if the component is active

			if( array_key_exists($component_slug, $bp_active_components) ){

				$result["active"] = true;
			}
			else {
				$result["active"] = false;
			}

		}

		// CASE 2: FoxFire or an external plugin are adding a page
		// ==================================================================
		elseif( !$in_original && $in_filtered ) {

			$component_slug = array_search($post_id, $bp_pages_filtered);

			$result = array(
					"exists"=>true,
					"slug"=>$component_slug,
			);

			$fox_page_data = $fox->navigation->getPageOwner($post_id);

			// FoxFire owns the page
			// ====================================
			if($fox_page_data){

				$result["plugin_name"] = __('FoxFire',"foxfire");
				$result["component_name"] = $fox_page_data["module_name"];

			}
			// External plugin owns the page
			// ====================================
			else {
				$result["plugin_name"] = __('Other Plugin',"foxfire");
				$result["component_name"] = null;
			}

			// Check if the component is active

			if( array_key_exists($component_slug, $bp_active_components) ){

				$result["active"] = true;
			}
			else {
				$result["active"] = false;
			}

		}

		// CASE 3: An external plugin deactivating a BP component
		// ==================================================================
		elseif( $in_original && !$in_filtered ) {

			$component_slug = array_search($post_id, $bp_pages_filtered);

			$result = array(
					"exists"=>true,
					"active"=>false,
					"slug"=>$component_slug,
					"plugin_name"=>__('BuddyPress',"foxfire")
			);

			$component_name = $bp_components[$component_slug];

			if($component_name){

				$result["component_name"] = $component_name;
			}
			else {

				$result["component_name"] = __('Unknown',"foxfire");
			}

		}

		// CASE 4: The page is not being claimed
		// ==================================================================
		else {

			$result = array(
					"exists"=>false,
					"active"=>null,
					"slug"=>null,
					"plugin_name"=>null,
					"component_name"=>null,
			);

		}
		
				
		return $result;


	}


	/**
	 * Checks if a slug is owned by FoxFire, BuddyPress, or an external plugin
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $location | location of slug "profile" or "tab"
	 * @return bool/array $result | False on failure. Data array on success.
	 */

	public function getSlugOwner($location, $slug) {

	    
		if( ($location != 'tab') && ($location != 'profile') ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid module location",
				'data'=> array('location'=>$location, 'slug'=>$slug),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
				
		if( empty($slug) ){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid slug name",
				'data'=> array('location'=>$location, 'slug'=>$slug),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
					
		global $bp, $fox;

		$location = strtolower($location);
		$slug = strtolower($slug);

		// BuddyPress lists all their slug constants in /bp-members/bp-members-signup.php. We
		// have to build the array using BP's slug constants in case the site uses a define()
		// to override the component's default slug name. (Typically to translate it). Slugs
		// are converted to lowercase because web servers ignore capitalization in URL's

		$bp_components = array (

			strtolower(BP_ACTIVATION_SLUG)	=> __('Activate',"foxfire"),
			strtolower(BP_ACTIVITY_SLUG)	=> __('Activity',"foxfire"),
			strtolower(BP_BLOGS_SLUG)	=> __('Blogs',"foxfire"),
			strtolower(BP_FORUMS_SLUG)	=> __('Forums',"foxfire"),
			strtolower(BP_FRIENDS_SLUG)	=> __('Friends',"foxfire"),
			strtolower(BP_GROUPS_SLUG)	=> __('Groups',"foxfire"),
			strtolower(BP_MEMBERS_SLUG)	=> __('Members',"foxfire"),
			strtolower(BP_MESSAGES_SLUG)	=> __('Messages',"foxfire"),
			strtolower(BP_REGISTER_SLUG)	=> __('Register',"foxfire"),
			strtolower(BP_SEARCH_SLUG)	=> __('Search',"foxfire"),
			strtolower(BP_SETTINGS_SLUG)	=> __('Settings',"foxfire"),
			strtolower(BP_XPROFILE_SLUG)	=> __('Profile',"foxfire"),
		);

		$result = array();


		// CASE 1: Top-level BuddyPress menu
		// ==============================================
		if( $location == "profile"){

			// Scan the bp_nav array for the slug
			// ========================================
			$exists_in_bp_nav = false;

			foreach( $bp->bp_nav as $key => $data ){

				if( $data["slug"] == $slug ){

					$exists_in_bp_nav = true;
					$matching_slug = $slug;
					$component_name = $data["name"];
					break;
				}
			}
			unset($key, $data);

			// CASE 1A: Exists in the nav array
			// ===================================================
			if($exists_in_bp_nav){

				$result["exists"] = true;
				$result["slug"] = $matching_slug;

				// BuddyPress owns the slug
				// ====================================
				if( array_key_exists($slug, $bp_components) ){

					$result["plugin_name"] = __('BuddyPress',"foxfire");
					$result["component_name"] = $bp_components[$slug];
					$result["module_slug"] = null;
					$result["module_id"] = null;
				}

				// External plugin owns the slug
				// ====================================
				else {

					$result["plugin_name"] = __('Other Plugin',"foxfire");
					$result["component_name"] = $component_name;
					$result["module_slug"] = null;
					$result["module_id"] = null;
				}

			}
			// CASE 1B: Does not exist in the nav array
			// ===================================================
			else {

				try {
					$fox_slug_data = $fox->navigation->getSlugOwner($location, $slug);
				}
				catch (FOXexception $child) {

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Error checking slug owner",
						'data'=> array('location'=>$location, 'slug'=>$slug),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}				

				// FoxFire owns the slug
				// ====================================
				if($fox_slug_data){

					$result["plugin_name"] = __('FoxFire',"foxfire");
					$result["component_name"] = $fox_slug_data["module_name"];
					$result["module_slug"] = $fox_slug_data["module_slug"];
					$result["module_id"] = $fox_slug_data["module_id"];

				}

				// Slug is available for use
				// ====================================
				else {

					$result = array(
						"exists"=>false,
						"active"=>null,
						"slug"=>null,
						"module_id"=>null,
						"module_slug"=>null,
						"plugin_name"=>null,
						"component_name"=>null,
					);
				}
			}			

		}

		// CASE 2: Checking on FoxFire tab
		// ==============================================
		else {
			
			try {
				$fox_slug_data = $fox->navigation->getSlugOwner($location, $slug);
			}
			catch (FOXexception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error checking slug owner",
					'data'=> array('location'=>$location, 'slug'=>$slug),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}			

			// FoxFire owns the slug
			// ====================================
			if($fox_slug_data){

				$result["exists"] = true;
				$result["slug"] = $slug;
				$result["plugin_name"] = __('FoxFire',"foxfire");
				$result["component_name"] = $fox_slug_data["module_name"];
				$result["module_id"] = $fox_slug_data["module_id"];				
			}
			else {
				$result["exists"] = false;			    			    			    
			}

		}
		
		//FOX_debug::printToFile($result);
		return $result;


	}


} // End of class FOX_bp

?>