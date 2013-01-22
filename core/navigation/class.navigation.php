<?php

/**
 * FOXFIRE NAVIGATION
 * Maps URLs to screen functions, producing the plugin's navigation tree
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Navigation
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

final class FOX_nav {

	var $config_class;		    // System config class

	var $page_modules_class;	    // Page modules class instance
	var $target_class;		    // Target class instance
	var $policy_class;		    // Policy class instance

	var $token_keys_class;		    // Token key class instance. Stores the id's and names of token keys.
	var $group_keys_class;		    // Group key class instance. Stores the id's and names of group keys.
	var $system_keys_class;		    // System key class instance. Stores the id's and names of group keys.

	var $user_class;		    // User class instance.

	var $target_data;		    // Target data for the current page module
	var $policy_data;		    // Policy data for the current page module

	var $key_delimiter = "^";	    // The character the class uses to split keys into "tree", "branch", and "node"
					    // when parsing returned forms.

	// ================================================================================================================


	function __construct($args=null) {

		// Handle dependency-injection for unit tests
		if($args){

			$this->config_class = &$args['config_class'];
			$this->page_modules_class = &$args['page_modules_class'];
			$this->target_class = &$args['target_class'];
			$this->policy_class = &$args['policy_class'];
			$this->token_keys_class = &$args['token_keys_class'];
			$this->group_keys_class = &$args['group_keys_class'];
			$this->system_keys_class = &$args['system_keys_class'];
			$this->user_class = &$args['user_class'];
		}
		else {
			global $fox, $fox;
			$this->config_class = &$fox->config;
			$this->page_modules_class = &$fox->pageModules;
			$this->target_class = new FOX_loc_module();
			$this->policy_class = new FOX_loc_policy();
			$this->token_keys_class = new FOX_uKeyType();
			$this->group_keys_class = new FOX_uGroupType();
			$this->system_keys_class = new FOX_sysKey();
			$this->user_class = &$fox->user;
		}
		

	}


	/**
	 * Checks if the WordPress page corresponding to the $post_id is owned by one of
	 * FoxFire's page modules, and returns info about the module if it is.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int $post_id | post_id of the WordPress page to search
	 * @return bool/array $result | False on not owned by FoxFire. Data array on success.
	 */

	public function getPageOwner($post_id){

		global $fox;

		$result = array(
			"module_id"=>3,
			"module_slug"=>"test_module",
			"module_name"=>"Test Module"
		);

		return $result;
	}


	/**
	 * Checks if $slug is owned by one of FoxFire's page modules, and returns info about the
	 * module if it is.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $slug | name of the slug to search for
	 * @return bool/array $result | Exception on failure. False on not owned by FoxFire. Data array on success.
	 */

	public function getSlugOwner($location, $slug){


		try {
			$location_data = $this->target_class->getLocation($location);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error fetching location data",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		if( FOX_sUtil::keyExists($slug, $location_data) ){


			try {
				$module_data = $this->page_modules_class->getByID($location_data[$slug]["module_id"]);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error fetching page module data",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			$result = array(
				"module_id"=>$location_data[$slug]["module_id"],
				"module_slug"=>(string)$location_data[$slug]["target"],
				"module_name"=>$module_data["name"]
			);

		}
		else {
			$result = null;
		}

		return $result;

	}


	/**
	 * Determines if a user can access a resource based on their keyring and
	 * the resource's policy object
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool $result | Exception on failure. True if can access. False if cannot access.
	 */

	public function canAccess($policy, $keyring){

		return true;

	}


	/**
	 * Adds a page to the site's navigation tree by injecting it into the BP
	 * active components array using the 'bp_core_get_directory_page_ids' action
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool $result | Exception on failure. True on success.
	 */

	public function injectSite($pages){


		// Avoid hooking on admin screens
		// =======================================================================

		// BuddyPress runs their screen router on all WordPress screens including back-end
		// admin screens. This wastes resorces and causes *epic* debugging problems with
		// page module installers. If the code in this method gets run on the "system tools"
		// screen, it will interfere with the "reset system to default" button.

		// The isLoginScreen() check handles the case where the admin manages to corrupt the 
		// routing table, causing the router to throw an exception on every front-end page, 
		// including the login page (preventing the admin from logging in and fixing it)
	    
		if( FOX_wp::isAdminScreen() || FOX_wp::isLoginScreen() ){

			return $pages;
		}

		$locations = "page";

		try {
			$fox_pages = $this->target_class->getLocation($locations);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in target class getLocation() method",
				'data'=>array('locations'=>$locations),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		if($fox_pages){

			// Combine the module classes into an array so we can fetch
			// all of them with a single operation

			$module_classes = array();

			foreach($fox_pages as $page_data){

				$module_classes[] = $page_data["php_class"];
			}
			unset($page_data);

			try {
				$page_module_data = $this->page_modules_class->getByClass($module_classes);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in page modules getByClass() method",
					'data'=>array('module_classes'=>$module_classes),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}


			foreach($fox_pages as $page_id => $data){

				// Add the page module to the BP pages array
				$pages[$data["php_class"]] = $page_id;

				// Load the page module's class tree
				try {
					$this->page_modules_class->loadModule($page_module_data[$data["php_class"]]["slug"]);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Error in page modules loadModule() method",
						'data'=>$page_module_data[$data["php_class"]]["slug"],
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}

				// Create an anonymous function that instantiates the page module's class
				// and runs its screen method whenever the page module is the $bp->current_component

				$function_body	=   'global $bp;';
				$function_body .=   'if($bp->current_component == "' . $data["php_class"] . '"){';
				$function_body .=   '	$cls = new ' . $data["php_class"] . '();';
				$function_body .=   '	$cls->screen();';
				$function_body .=   '}';

				$function_name = create_function('', $function_body);

				// Attach the anonymous function to the bp_init action
				add_action( 'bp_screens', $function_name );

			}
			unset($page_id, $data);
		}

		return $pages;

	}

	/**
	 * Adds FoxFire's tabs to the BP core navigation array, causing the tabs
	 * to appear on the user's profile
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool $result | Exception on failure. True on success.
	 */

	public function injectProfile(){


		// Avoid hooking on admin screens
		// =======================================================================

		// BuddyPress runs their screen router on all WordPress screens including back
		// end admin screens. This causes *epic* debugging problems with page module
		// installers. If the code in this method gets run on the "system tools"
		// screen, it will interfere with the "reset system to default" button.

		if( FOX_wp::isAdminScreen() ){

			return true;
		}

		// Load page modules registered on "profile" target
		// =======================================================================

		$locations = "profile";

		try {
			$profile_modules_raw = $this->target_class->getLocation($locations);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error loading page modules at target",
				'data'=>array('locations'=>$locations),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// If no page modules are registered on the "profile" target, quit
		if(!$profile_modules_raw){

			return true;
		}

		$profile_modules = array();

		foreach( $profile_modules_raw as $slug_name => $module_data ){

			$row_data = array(	"slug_name"=>$slug_name,
						"php_class"=>$module_data["php_class"],
						"tab_title"=>$module_data["tab_title"],
						"tab_position"=>$module_data["tab_position"]
			);

			$profile_modules[$module_data["module_id"]] = $row_data;
		}
		unset($slug_name, $module_data, $row_data);


		// Fetch active page modules
		// =======================================================================

		try {
			$active_page_modules = $this->page_modules_class->getActiveModules();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error loading active page modules",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// When array_key_intersect() is run on multidimensional arrays, the top-level keys
		// are intersected, and the values are taken from the first array.

		$valid_modules = array_intersect_key($profile_modules, $active_page_modules);

		// If no page active page modules are registered on the "profile" target, quit
		if(!$valid_modules){
			return;
		}

		// Fetch policies for active page modules registered on "profile" target
		// =======================================================================

		$module_ids = array_keys($valid_modules) ;

		try {
			$module_policies = $this->policy_class->getL5($module_ids);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error loading module policies",
				'data'=>array('module_ids'=>$module_ids),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		// Add menu items for page modules that the user has access to
		// =======================================================================

		foreach($valid_modules as $module_id => $module_data){

			$user_keyring = $this->user_class->loggedin_user->keyring;
			$module_policy = $module_policies[$module_id];

			try {
				$can_access = self::canAccess($module_policy, $user_keyring);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Error in self::canAccess() method",
					'data'=>array('module_policy'=>$module_policy, 'user_keyring'=>$user_keyring),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			if($can_access){

				// If the user has access to the module, load its source files
				// ===================================================================

				$page_module_id = $active_page_modules[$module_id];

				try {
					$this->page_modules_class->loadModule($page_module_id);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>5,
						'text'=>"Error in page modules loadModule() method",
						'data'=>array('page_module_id'=>$page_module_id),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}

				// Create an instance of the page module's class
				// ===================================================================

				$cls = new $module_data["php_class"];

				try {
					$screen_function = $cls->getScreenFunction();
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>6,
						'text'=>"Page module threw an error while accessing getScreenFunction()",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}

				try {
					$default_subnav_slug = $cls->getDefaultSubnavSlug();
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>7,
						'text'=>"Page module threw an error while accessing getDefaultSubnavSlug()",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}

				bp_core_new_nav_item( array(
					'name' => $module_data["tab_title"],
					'slug' => $module_data["slug_name"],
					'position' => $module_data["tab_position"],
					'screen_function' => $screen_function,
					'default_subnav_slug' => $default_subnav_slug,
					'show_for_displayed_user' => true
				) );

			}

			unset($user_keyring, $module_policy, $can_access, $cls, $screen_function, $default_subnav_slug);

		}
		unset($module_id, $module_data);

	}


	/**
	 * Adds a page to FoxFire's tab on a user profile
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool $result | Exception on failure. True on success.
	 */

	public function injectTab($pages){

		return true;

	}


	/**
         * Loads a template file
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $plugin_path | Path to the plugin's root folder
	 * @param string $type | Module type - "page" (page module), "album" (album module), "media" (media module)
	 * @param string $slug | Module slug
	 * @param string $template | Name of the template file, including file extension.
	 *
         * @return bool $result | Exception on failure. True on success.
         */
	function loadTemplate($plugin_path, $type, $slug, $template) {


		// Child theme
		// ================================================================
		// A child theme will almost always specify its own custom CSS styles, which are set
		// in the WP global 'STYLESHEETPATH', so check here first.

		if ( file_exists(STYLESHEETPATH . '/' . $type . '/' . $slug . '/' . $template)) {

			$located_template = STYLESHEETPATH . '/' . $type . '/' . $slug . '/' . $template;
		}

		// Parent theme
		// ================================================================
		// If a child theme doesn't contain the requested template, move up the hierarchy and
		// check the parent theme.

		elseif ( file_exists(TEMPLATEPATH . '/' . $type . '/' . $slug . '/' . $template) ) {

			$located_template = TEMPLATEPATH . '/' . $type . '/' . $slug . '/' . $template;
		}

		// Default template
		// ================================================================
		// Every FoxFire module is required to supply a set of default templates for itself. This 
		// allows 3rd-party modules to be added to the system, because there will probably be
		// no template files for them in the default theme.

		else {

			$located_template = $plugin_path . '/modules/' . $type . '/' . $slug . '/templates/' . $template;
		}

		if(!$located_template){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Requested template does not exist",
				'data'=>array('template'=>$template, 'type'=>$type, 'slug'=>$slug),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		require_once($located_template);

		return true;

	}


	/**
         * Loads configuration data for the specified page module
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param int $module_id | id of page module
         * @return bool | Exception on failure. True on success.
         */

	public function loadModule($module_id){


		try {
			$target_raw = $this->target_class->getID($module_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error loading target data",
				'data'=>array('module_id'=>$module_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		if($target_raw["location"] == "page"){

			$this->target_data["location"] = (string)$target_raw["location"];
			$this->target_data["page_id"] = (int)$target_raw["target"];
		}
		else {
			$this->target_data["location"] = (string)$target_raw["location"];
			$this->target_data["slug"] = (string)$target_raw["target"];
			$this->target_data["tab_title"] = (string)$target_raw["tab_title"];
			$this->target_data["tab_position"] = (int)$target_raw["tab_position"];
		}


		try {
			$policy_raw = $this->policy_class->getL5($module_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error loading policy data",
				'data'=>array('module_id'=>$module_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		if($policy_raw){


			// Fetch token, group, and system key names
			// =======================================================================

			try {
				$token_keys = $this->token_keys_class->getAll();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error loading token keys data",
					'data'=>array('module_id'=>$module_id),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			try {
				$group_keys = $this->group_keys_class->getAll();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Error loading group keys data",
					'data'=>array('module_id'=>$module_id),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			try {
				$system_keys = $this->system_keys_class->getAll();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error loading system keys data",
					'data'=>array('module_id'=>$module_id),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}


			// Insert the key's name into the policy data array
			// =======================================================================

			$policy_data = array();

			foreach( $policy_raw as $zone => $rules ){

				foreach( $rules as $rule => $key_types ){

					foreach( $key_types as $key_type => $key_ids ){

						foreach( $key_ids as $key_id => $data){


							switch($key_type){

								case "token" : {

									$key_name = $token_keys[$key_id];

								} break;

								case "group" : {

									$key_name = $group_keys[$key_id];

								} break;

								case "system" : {

									$key_name = $system_keys[$key_id];

								} break;

							}

							$policy_data[$zone][$rule][$key_type][$key_id] = array("name"=>$key_name, "ctrl_val"=>$data);

						}
						unset($key_id, $data, $key_name);
					}
					unset($key_type, $key_ids);
				}
				unset($rule, $key_types);
			}
			unset($zone, $rules);

		}

		// Overwrite the policy_data array with our new policy data array. This ensures
		// keys from previous loads are cleared

		$this->policy_data = $policy_data;

		return true;

	}



	/**
         * Processes config variables passed via an HTML form
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param array $post | HTML form array
         * @return bool | Exception on failure. True on success.
         */

	public function processHTMLForm($post){


		if( empty($post['key_names']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"No key names posted with form",
				'data'=> $post,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}


		$san = new FOX_sanitize();

		// Explode fields array into individual key names
		$options = explode(',', stripslashes($post['key_names']));

		// Sanitize keys. Loft key-value pairs into a multidimensional array
		// ====================================================================

		$processed_keys = array();

		foreach($options as $option) {

			$full_name = explode($this->key_delimiter, $option);

			// Process the raw form strings into proper key names
			$discard = trim( $full_name[0] );
			$raw_tree = trim( $full_name[1] );
			$raw_branch = trim( $full_name[2] );
			$raw_key = trim( $full_name[3] );

			// Passed by reference
			$tree_valid = null; $branch_valid = null; $key_valid = null;
			$tree_error = null; $branch_error = null; $key_error = null;

			try {
				$tree = $san->keyName($raw_tree, null, $tree_valid, $tree_error);
				$branch = $san->keyName($raw_branch, null, $branch_valid, $branch_error);
				$key = $san->keyName($raw_key, null, $key_valid, $key_error);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in sanitizer function",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			if(!$tree_valid){

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Called with invalid tree name",
					'data'=>array('raw_tree'=>$raw_tree, 'san_error'=>$tree_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			elseif(!$branch_valid){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Called with invalid branch name",
					'data'=>array('raw_branch'=>$raw_branch, 'san_error'=>$branch_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			elseif(!$key_valid){

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Called with invalid key name",
					'data'=>array('raw_key'=>$raw_key, 'san_error'=>$key_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

			// Manually generate the $post array keyname to avoid escaping added by PHP
			$post_key = "foxient" . $this->key_delimiter . $tree . $this->key_delimiter . $branch . $this->key_delimiter . $key;
			$processed_keys[$tree][$branch][$key] = FOX_sUtil::formVal($post[$post_key]);

			unset($full_name, $tree, $raw_tree, $branch, $raw_branch, $key, $raw_key);
			unset($tree_error, $branch_error, $key_error, $tree_valid, $branch_valid, $key_valid);

		}
		unset($option);


		// Assemble the processed keys into the right format for the
		// the database classes
		// ===============================================================

		$target_data = array();

		if( $processed_keys["target"]["key"]["location"] == "page" ){

			$target_data["location"] = (string)$processed_keys["target"]["key"]["location"];
			$target_data["module_id"] = (int)$processed_keys["target"]["key"]["module_id"];
			$target_data["target"] = (int)$processed_keys["target"]["key"]["page_id"];
		}
		else {
			$target_data["location"] = (string)$processed_keys["target"]["key"]["location"];
			$target_data["module_id"] = (int)$processed_keys["target"]["key"]["module_id"];
			$target_data["target"] = (string)$processed_keys["target"]["key"]["slug"];
			$target_data["tab_title"] = (string)$processed_keys["target"]["key"]["tab_title"];
			$target_data["tab_position"] = (int)$processed_keys["target"]["key"]["tab_position"];
		}

		$policy_data = array();


		// Loft the keys into a heirarchical array and decode JSON-encoded fields
		// ======================================================================

		foreach( $processed_keys["policy"] as $zone => $rules ){

			foreach( $rules as $rule => $rule_data){

				$rule_decoded = (json_decode($rule_data, true));

				if( !empty($rule_decoded) ){

					foreach($rule_decoded as $key_type => $key_ids){

						foreach($key_ids as $key_id => $key_data){

							// Since we're not handling $ctrl info for keys yet, we need to
							// manually set a value for the key.
						    
							$policy_data[$zone][$rule][$key_type][$key_id] = true;

						}
						unset($key_id, $key_data);

					}
					unset($key_type, $key_ids);

				}

			}
			unset($rule, $rule_data, $rule_decoded);
		}
		unset($zone, $rules);


		// Update the target and policy classes
		// ===============================================================

		try {
			$this->target_class->setTarget($target_data);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error updating target data",
				'data'=> $target_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		try {
	
			$this->policy_class->replaceL5((int)$target_data["module_id"], $policy_data);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Error updating policy data",
				'data'=>array('module_id'=>$target_data["module_id"], 'policy_data'=>$policy_data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return true;

	}


	/**
         * Adds a page module's target and policy data to the site's navigation tree
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param int $module_id | module_id for the page module
	 *
         * @param array $target_data | Navigation target settings for the page module
	 *	=> VAL @param string $location | Display location.
	 *	=> VAL @param int/string $target | Module target. Must be (int) for pages, (string) for all other locations.
	 *	=> VAL @param string $tab_title | Title for the module's tab (not used for pages).
	 *	=> VAL @param int $tab_position | Tab position (0-255) (not used for pages).
	 *	=> VAL @param string $php_class | PHP class for the page module.
	 *
         * @param array $policy_data | Array of policy arrays
	 *	=> ARR @param int '' | Individual policy array
	 *	    => VAL @param int $zone | zone id
	 *	    => VAL @param int $rule | rule id
	 *	    => VAL @param string $key | key name
	 *	    => VAL @param bool/int/float/string/array/obj $val | key value
	 *
         * @return bool | Exception on failure. True on success.
         */

	public function installPageModule($module_id, $target_data, $policy_data){


		// Heavily error-check. This avoids confusing new page module developers
		// with long error-traces that go deep into the plugin
		// =====================================================================

		if( !is_int($module_id) || ($module_id < 1) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid module_id",
				'data'=>array( "module_id"=>$module_id, "target_data"=>$target_data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		if( !is_array($target_data) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Target data variable is not an array",
				'data'=>array( "module_id"=>$module_id, "target_data"=>$target_data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}


		if( empty($target_data["location"]) ) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Missing location field in data array",
				'data'=>array( "module_id"=>$module_id, "target_data"=>$target_data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		if( empty($target_data["target"]) ) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Missing target field in data array",
				'data'=>array( "module_id"=>$module_id, "target_data"=>$target_data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		if( empty($target_data["php_class"]) ) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Missing php_class field in data array",
				'data'=>array( "module_id"=>$module_id, "target_data"=>$target_data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}


		if( $target_data["location"] == 'page' ) {


			if( !is_int($target_data["target"]) ){

				throw new FOX_exception( array(
					'numeric'=>6,
					'text'=>"Attempted to use non-int value as a page target",
					'data'=>array( "module_id"=>$module_id, "target_data"=>$target_data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

		}
		else {

			if( empty($target_data["tab_title"]) || empty($target_data["tab_position"]) ) {

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Non-page targets require tab_title and tab_position to be set",
					'data'=>array( "module_id"=>$module_id, "target_data"=>$target_data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

			if( !is_int($target_data["tab_position"]) ){

				throw new FOX_exception( array(
					'numeric'=>8,
					'text'=>"Attempted to use non-int value as tab position",
					'data'=>array( "module_id"=>$module_id, "target_data"=>$target_data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

		}

		// Add the module_id to the target data array
		$target_data["module_id"] = $module_id;


		// Add settings to navigation system
		// ===============================================================

		try {
			$this->target_class->setTarget($target_data);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>9,
				'text'=>"Error setting target data",
				'data'=> $target_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		try {
			$this->policy_class->setL5($module_id, $policy_data);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Error setting policy data",
				'data'=> $policy_data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return true;

	}



	/**
         * Resets the class's print_keys array, making it ready to accept a new batch of keys. This method
	 * MUST be called at the beginning of each admin form to clear old keys from the singleton.
         *
         * @version 1.0
         * @since 1.0
         */

	public function initNodesArray(){

		$this->print_keys = array();
	}


	/**
         * Creates a composite keyname for a navigation target given the key's branch, and name. Adds
	 * the composited key name to the $print_keys array for printing the form's keys array.
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $key | The key's name
         * @return ECHO | composited key name
         */

	public function printTargetNodeName($key){

		echo 'name="' . $this->getTargetNodeName($key) . '"';
	}

	public function getTargetNodeName($key){

		$key_name = ("foxient" . $this->key_delimiter . "target" . $this->key_delimiter . "key" . $this->key_delimiter . $key);

		// Add formatted key name to the $keys array
		$this->print_keys[$key_name] = true;

		return $key_name;
	}


	/**
         * Prints a navigation target key's value
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $branch | The key's branch name
	 * @param string $key | The key's name
         * @return ECHO | composited key name
         */

	public function printTargetNodeVal($key){

		echo 'value="' . $this->target_data[$key] . '"';
	}


	/**
         * Returns a navigation target key's value
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $key | The key's name
         * @return mixed | composited key name
         */

	public function getTargetNodeVal($key){

		return $this->target_data[$key];
	}


	/**
         * Creates a composite keyname for a policy key given the key's branch, and name. Adds
	 * the composited key name to the $print_keys array for printing the form's keys array.
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $branch | The key's branch name
	 * @param string $key | The key's name
         * @return ECHO | composited key name
         */

	public function printPolicyKeyName($branch, $key){

		echo 'name="' . $this->getPolicyKeyName($branch, $key) . '"';
	}

	public function getPolicyKeyName($branch, $key){

		$key_name = ("foxient" . $this->key_delimiter . "policy" . $this->key_delimiter . $branch . $this->key_delimiter . $key);

		// Add formatted key name to the $keys array
		$this->print_keys[$key_name] = true;

		return $key_name;
	}


	/**
         * Prints a policy key's value
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $branch | The key's branch name
	 * @param string $key | The key's name
         * @return ECHO | composited key name
         */

	public function printPolicyKeyVal($branch, $key){

		echo 'value="' . $this->policy_data[$branch][$key] . '"';
	}

	/**
         * Returns a policy key's value
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $branch | The key's branch name
	 * @param string $key | The key's name
         * @return mixed | composited key name
         */

	public function getPolicyKeyVal($branch, $key){

		return $this->policy_data[$branch][$key];
	}


	/**
         * Creates a composited string used to print a hidden field containing all of the key names enqueued
	 * using keyName() or getNodeName()
         *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $field_name | (optional) name to use for the form field
         * @return ECHO | composited hidden form field string
         */

	public function printNodesArray($field_name=null){

		echo self::getNodesArray($field_name);
	}

	public function getNodesArray($field_name=null){

		// Handle no $field_name being passed

		if(!$field_name){
		    $field_name = "key_names";
		}

                $result = '<input type="hidden" name="' . $field_name . '" value="';

		$keys_left = count( $this->print_keys) - 1;

		foreach( $this->print_keys as $key_name => $value) {

			$result .= $key_name;

			if($keys_left != 0){
				$result .= ", ";
				$keys_left--;
			}
		}
		unset($key_name, $value);

                $result .= '" />';

		return $result;
	}






} // End of class FOX_nav



/**
 * Injects FoxFire's pages into the BP components array
 *
 * @version 1.0
 * @since 1.0
 */

function FOX_nav_injectSite($pages){

	global $razor;

	// The BuddyPress page router fires on every WP page load. This
	// causes it to fire during unit tests, which breaks test isolation
	// by running the whole routing stack on every page load. This test
	// handles this problem

	if($razor){
		return $pages;
	}
	else {
		global $fox;
		$result = $fox->navigation->injectSite($pages);
		return $result;
	}

}
add_filter('bp_core_get_directory_page_ids', 'FOX_nav_injectSite',10,1);



?>