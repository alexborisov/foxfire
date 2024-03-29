<?php

/**
 * FOXFIRE ROUTER
 * Analyzes a URI passed from the web server and determines the correct page module to send the request to.
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

class FOX_router {


	var $http_referer;		    // $_SERVER['HTTP_REFERER'] sent in from the web server
	var $request_uri;		    // $_SERVER['REQUEST_URI'] sent in from the web server

	var $walk;			    // Walk array for current URI
	var $lofted_pages;		    // The page tree for the root blog or current blog as a hierarchical array
	var $intersect;			    // Intersect object

	var $unit_test = false;		    // Set true to disable die() calls in template loader methods


	// ================================================================================================================

	
	function __construct($args=null) {

		// Handle dependency-injection for unit tests
		if($args){

			$this->http_referer = &$args['http_referer'];
			$this->request_uri = &$args['request_uri'];

			$this->walk = &$args['walk'];
			$this->lofted_pages = &$args['lofted_pages'];
			$this->intersect = &$args['intersect'];

		}
		else {

			$this->http_referer = $_SERVER['HTTP_REFERER'];
			$this->request_uri = $_SERVER['REQUEST_URI'];

			$this->walk = null;
			$this->lofted_pages = null;
			$this->intersect = null;
		}
		
		$this->allowed_protocols = array( 'http', 'https', 'news', 'feed', 'xmpp' );

	}


	/**
	 * Given a URI owned by FoxFire, load the correct templates
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function route(&$status=null) {


		// Convert the URI passed by the web server into a walk array
		// ===============================================================

		try {
			$this->walk = self::buildWalk();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Walk error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		// Intersect the walk array with the site's page tree
		// ===============================================================

		try {
			$this->intersect = self::pageIntersect($this->walk);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Intersect error",
				'data'=>array('walk'=>$this->walk),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		// Match the intersect to a BuddyPress component, and set the global BP variables
		// ===============================================================

		try {
			$result = self::matchComponent($this->intersect, $status);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Match error",
				'data'=>array('intersect'=>$this->intersect),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Given a URI from the web server, create a walk array
	 *
	 * @link http://en.wikipedia.org/wiki/Glossary_of_graph_theory#Walks
	 * @link http://en.wikipedia.org/wiki/Trie
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array $walk | Exception on failure. Walk array on success.
	 */

	public function buildWalk() {

	    
		$parsed_uri = parse_url($this->request_uri);

		if(!$parsed_uri){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Couldn't parse supplied URI string",
				'data'=>$this->request_uri,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		// Strip any surplus "/" characters from the URI string, and
		// explode it into a walk array

		$walk = explode('/', trim($parsed_uri["path"], '/') );

		return $walk;

	}


	/**
	 * Intersect a walk with the site's pages tree, returning the endpoint id,
	 * endpoint slug, and transect array
	 *
	 * @link http://en.wikipedia.org/wiki/Tree_(graph_theory)
	 * @link http://en.wikipedia.org/wiki/Union_(set_theory)
	 * @link http://en.wikipedia.org/wiki/Trie
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param array $walk | Walk array
	 * @return array $result | Exception on failure. Result array on success.
	 */

	public function pageIntersect($walk) {



		$this->lofted_pages = array();
		
		// 1) Possibly pre-screen the URI against a pattern filter to avoid db calls
		//    for impossible to route URL's ...or is it better to have the sub-classes
		//    do the validation then bubble-up to OK the db call? But if we pre-screen
		//    then we can avoid loading the sub-class chain.


		// Intersect the walk array with the pages tree
		// ==============================================================

		try {
			$intersect = self::walkIntersectTree($walk, $this->lofted_pages);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error intersecting walk with pages tree",
				'data'=>array("walk"=>$walk, "lofted_pages"=>$this->lofted_pages),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return $intersect;

	}


	/**
	 * Determine which BP component (if any) matches a given transect
	 *
	 * @link http://en.wikipedia.org/wiki/Cycle_(graph_theory)
	 * @link http://en.wikipedia.org/wiki/Cycle_detection
	 * @version 1.0
	 * @since 1.0
	 * @param array $intersect | Intersect array
	 * @param array $status | Reason no match was found
	 * @return bool $result | Exception on failure. True on match. False on no match.
	 */

	public function matchComponent($intersect, &$status) {


		$transect = $intersect["transect"];
		$route_found = false;

		// CASE 1: Front-page component
		// ====================================================================
		if( $intersect["endpoint_id"] === null ){

			// If a component is set to the front page, and the user is not requesting
			// a specific post via a URL parameter, we have a match

			$not_preview_mode = ( empty($_GET['p']) && empty($_GET['page_id']) );

			if($not_preview_mode){

				$show_page_on_front = (get_option('show_on_front') == 'page'); // Note comparison operator
				$post_id = get_option('page_on_front');

				if($show_page_on_front && $post_id){

					$post = get_post($post_id);

					if( !empty($post) ){

						$this->bp->current_component = (string)$post->post_name;

						$status = array(
							'numeric'=>1,
							'text'=>"Successful match on front-page component.",
							'data'=>array('current_component'=>$this->bp->current_component,
								      'post_id'=>$post_id,
								      'post'=>$post ),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
						);

						$route_found = true;
					}
					else {

						throw new FOX_exception( array(
							'numeric'=>1,
							'text'=>"Site front page set to component, but component's post was empty",
							'data'=>array("post_id"=>$post_id),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						));
					}
				}
			}

			if(!$route_found){

				$status = array(
					'numeric'=>2,
					'text'=>"Site front page with no components active on front page.",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
				);

				return false;
			}

		}

		// CASE 2: Any non-nested component
		// ====================================================================

		if(!$this->bp->current_component){

			try {
				$this->bp->current_component = self::getPrimaryComponentName($intersect["endpoint_name"]);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error fetching primary component name",
					'data'=>array("endpoint_name"=>$intersect["endpoint_name"]),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			if($this->bp->current_component){

				$status = array(
					'numeric'=>3,
					'text'=>"Successful match on primary component",
					'data'=>array('current_component'=>$this->bp->current_component),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
				);

				$route_found = true;
			}
		}


		// CASE 3: Root profile
		// ====================================================================

		if (	!$this->bp->current_component						// 1) Has not matched a component in an earlier stage
			&& !empty($transect)							// 2) There are tokens in the transect
			&& !empty($this->bp->pages->members)					// 3) Members component is active
			&& defined( 'BP_ENABLE_ROOT_PROFILES' ) && BP_ENABLE_ROOT_PROFILES )	// 4) Root profiles constant is defined and true
		{

			// Shift the user name off the transect
			$user_name = array_shift($transect);

			// Switch the user_id based on compatibility mode
			if( bp_is_username_compatibility_mode() ){

				$user_id = (int) bp_core_get_userid( urldecode($user_name) );
			}
			else {
				$user_id = (int) bp_core_get_userid_from_nicename( urldecode($user_name) );
			}

			if($user_id){

				$this->bp->current_component = "members";
				$this->bp->displayed_user->id = $user_id;

				$status = array(
					'numeric'=>4,
					'text'=>"Successful match on root profile",
					'data'=>array('current_component'=>$this->bp->current_component),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
				);

				$route_found = true;

				// Without the 'members' URL chunk, WordPress won't know which page to load,
				// so this filter intercepts the WP query and tells it to load the members page

				$function_string  = '$query_args["pagename"] = "';
				$function_string .= $this->bp->pages->members->name;
				$function_string .= '"; return $query_args;';

				add_filter( 'request', create_function('$query_args', $function_string) );

			}
			else {

				$status = array(
					'numeric'=>5,
					'text'=>"Root profiles enabled. No matching user.",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
				);

				return false;
			}

		}

		// CASE 4: No match
		// ====================================================================

		if(!$this->bp->current_component){

			$status = array(
				'numeric'=>6,
				'text'=>"No matching components",
				'data'=>array('intersect'=>$this->intersect, 'walk'=>$this->walk),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
			);

			return false;
		}

		// Members Component secondary processing
		// ====================================================================

		if( ($this->bp->current_component == "members") && !empty($transect) ){

			// If the component is "members", the transect must either contain no tokens (show all users on site),
			// or the first token in the transect must be a valid user name (show single user)

			$user_name = array_shift($transect);

			// Switch the user_id based on compatibility mode
			if( bp_is_username_compatibility_mode() ){

				$user_id = (int) bp_core_get_userid( urldecode($user_name) );
			}
			else {
				$user_id = (int) bp_core_get_userid_from_nicename( urldecode($user_name) );
			}

			// CASE 1: Token in first transect position isn't a valid user_id
			// ---------------------------------------------------------------------------------------
			if( empty($user_id) ){

				$this->bp->current_component = null;    // Prevent components from loading their templates
				bp_do_404();

				$status = array(
					'numeric'=>7,
					'text'=>"Match on members component, but user_id is not valid.",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
				);

				return false;

			}

			elseif( !empty($user_id) ){

				$this->bp->displayed_user->id = $user_id;

				// CASE 2: Token in first transect position matches a user_id that
				// has been marked as a spammer
				// ---------------------------------------------------------------------------------------
				if( bp_core_is_user_spammer($user_id) ){

					if( is_super_admin() ){

						bp_core_add_message( __( 'This user has been marked as a spammer. Only site admins can view this profile.', 'buddypress' ), 'error' );
					}
					else {
						// If the user viewing the profile is not a super-admin, hide the page
						bp_do_404();

						$status = array(
							'numeric'=>8,
							'text'=>"Match on members component, but user_id is marked as a spammer and viewer is not a super-admin.",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
						);

						return false;
					}

				}
				// CASE 3: There are one or more tokens left in the transect after the user_name has
				// been shifted-out. This means we have a secondary component nested inside the members
				// component. The secondary component's *slug* will be the first token in the transect. We
				// have to set $this->bp->current_component to the *name* of the secondary component so
				// BP loads the correct template chain.
				// ---------------------------------------------------------------------------------------
				elseif( count($transect) > 0) {

					$current_component_slug = array_shift($transect);

					// CASE 3A: Match against the "primary" components that can exist both as a top-level
					// page and a secondary page nested beneath the "members" component. External plugins
					// following the "BuddyPress Example Component" pattern will appear in this array.
					//
					// TODO: This creates a cardinality problem. Primary components will appear at
					// both "example.com/members/membername/slug_name" and "example.com/slug_name". This
					// is further complicated by the fact that some components use the alias location as a
					// *context*, for example, "activity" at the root node shows activity for all users on
					// the site, but "activity" nested in the "members" component shows activity for a user.
					// There needs to be a set of configuration options on the admin back-end to specify
					// which location to use for a given component. Note that this is a legacy problem with
					// the original BP router design and we have emulated it for compatibility.
					// ---------------------------------------------------------------------------------------

					try {
						$this->bp->current_component = self::getPrimaryComponentName($current_component_slug);
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>3,
							'text'=>"Error fetching primary component name",
							'data'=>array("current_component_slug"=>$current_component_slug),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));
					}

					if($this->bp->current_component != null){

						$status = array(
							'numeric'=>9,
							'text'=>"Match on members component with primary nested component",
							'data'=>array(	'bp_pages'=>$this->bp->pages,
									'active_components'=>$this->bp->active_components,
									'current_component_slug'=>$current_component_slug,
									"component"=>$this->bp->current_component),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
						);

						$route_found = true;
					}
					else {

						// CASE 3B: Match against the "secondary" components that can only exist as a secondary
						// page nested beneath the "members" component. Matching is determined by the component's
						// action functions, which hook on the 'bp_init' action. Action functions are located
						// in "/component_name/bp-component_name-actions.php".
						// ---------------------------------------------------------------------------------------

						$this->bp->current_component = $current_component_slug;

						$status = array(
							'numeric'=>10,
							'text'=>"Match on members component, with possible match on secondary nested component",
							'data'=>array(	'bp_pages'=>$this->bp->pages,
									'active_components'=>$this->bp->active_components,
									'current_component_slug'=>$current_component_slug),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
						);

						$route_found = true;

					}

				}
				// CASE 4: There are no tokens left in the transect, so we're at the default screen
				// in the members component. Set $this->bp->current_component to the default profile
				// component (defined in bp-members-loader.php line 113)
				// ---------------------------------------------------------------------------------------
				else {
					$this->bp->current_component = $this->bp->default_component;

					$status = array(
						'numeric'=>11,
						'text'=>"Match on members component with no nested component",
						'data'=>array("component"=>$this->bp->current_component),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__
					);

					$route_found = true;
				}

			}

		}


		// Set BP's global variables
		// ====================================================================

		if( isset($transect[0]) ){

			$this->bp->current_action = array_shift($transect);

			if( count($transect) > 0 ){

				$this->bp->action_variables = $transect;
			}

		}


		// Set WP's global variables
		// ====================================================================

		// Set WP's internal query variables to the same state they would be in if
		// WP had loaded the page itself instead of BP intercepting the page load
		// and replacing it with our own content

		// TODO: We've emulated this for compatibility. BP should try to avoid
		// doing this unless actually necessary, because it costs an extra query on
		// each page load.

		$this->wp_query->queried_object_id = $this->intersect["endpoint_id"];
		$this->wp_query->queried_object    = &get_post($this->intersect["endpoint_id"]);

		return true;

	}


	/**
	 * Returns a flat array of the site's page hierarchy
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array $result | Exception on failure. Page hierarchy as flat array on success.
	 */

	public function getPageHierarchy() {

		// TODO: Add caching capabilities

		global $wpdb;

		// Always get page data from the root blog, except on multiblog mode, when it comes
		// from the current blog

		if( bp_is_multiblog_mode() ){

			$posts_table_name = $wpdb->posts;
		}
		else {
			$posts_table_name = $wpdb->get_blog_prefix( bp_get_root_blog_id() ) . 'posts';
		}

		$sql = "SELECT ID, post_name, post_parent, post_title FROM {$posts_table_name} WHERE post_type = 'page' AND post_status != 'auto-draft'";
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

			$result[$page->ID] = array( "parent"=>$page->post_parent,
						    "slug"=>$page->post_name,
						    "title"=>$page->post_title
					     );
		}
		unset($page);

		return $result;

	}


	/**
	 * Lofts a flat array of nodes into a rooted directed tree (trie) in O(n) time
	 * with only O(n) extra memory. This is also known as the "in-place quick
	 * union" algorithm.
	 *
	 * @link http://en.wikipedia.org/wiki/Tree_(graph_theory)
	 * @link http://en.wikipedia.org/wiki/Glossary_of_graph_theory#Walks
	 * @link http://en.wikipedia.org/wiki/Quicksort (in-place version)
	 * @link http://en.wikipedia.org/wiki/Trie
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param array $nodes | Flat array of nodes
	 * @return array $result | Hierarchical array of nodes
	 */

	public function loftHierarchy($nodes) {

		$tree = array();

		foreach( $nodes as $node_id => $data){

			// Note: we can operate directly on the passed parameter, because unless
			// explicitly told not to by using the "&$" sigil, PHP passes copies
			// of variables into a function.

			$nodes[$node_id]["node_id"] = $node_id;	    // Insert the node_id into each node to make the data
								    // structure easier to use. Note the unit tests are very
								    // picky about the order this gets done in because it
								    // affects its position in the output array.
			if( empty($data["parent"]) ){

				$tree["children"][$node_id] =& $nodes[$node_id];
			}
			else {
				$nodes[$data["parent"]]["children"][$node_id] =& $nodes[$node_id];
			}
		}

		return $tree;

	}


	/**
	 * Finds the longest intersect between a walk and a tree.
	 *
	 * @link http://en.wikipedia.org/wiki/Glossary_of_graph_theory#Walks
	 * @link http://en.wikipedia.org/wiki/Breadth-first_search
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param array $walk | Walk array
	 * @param array $tree | Tree array
	 * @return array $result | Exception on failure. Walk key and matching node id on success.
	 */

	public function walkIntersectTree($walk, $tree) {


		if( !is_array($walk) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Walk is not a valid array",
				'data'=>array( "walk"=>$walk, "tree"=>$tree),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		if( !is_array($tree) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Tree is not a valid array",
				'data'=>array( "walk"=>$walk, "tree"=>$tree),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}


		// Loop through each child node, searching for the
		// child node with the longest walk
		// ================================================

		$min_offset = null;
		$min_node = null;

		foreach( $tree["children"] as $data){

			if($data["slug"] == $walk[0]){

				$reduced_walk = array_slice($walk, 1);
				$intersect = self::walkIntersectTree_iterator($reduced_walk, $data);

				if( ($min_offset === null) || ($intersect["walk_offset"] < $min_offset) ){

					$min_offset = $intersect["walk_offset"];
					$min_node = $intersect["node_id"];
				}
			}

		}
		unset($data);

		// Return the child node with the longest walk, or if
		// there was no matching child node, return this node
		// ================================================

		if($min_offset === null){

			$result = array(
					    "endpoint_id"=>null,
					    "endpoint_name"=>null,
					    "walk_key"=>null,
					    "transect"=>array()
			);
		}
		else {

			// Convert offset to array key number so functions further down
			// the chain can use array_slice() to find the tokens after the
			// endpoint that correspond to actions/arguements (if they exist)

			$walk_key = (count($walk) - $min_offset) - 1;

			$result = array(
					    "endpoint_id" => $min_node,
					    "endpoint_name"=>$walk[$walk_key],
					    "walk_key" => $walk_key,
					    "transect"=>array_slice($walk, ($walk_key +1) )
			);
		}

		return $result;

	}


	/**
	 * Finds the longest intersect between the walk and the tree.
	 *
	 * @link http://en.wikipedia.org/wiki/Glossary_of_graph_theory#Walks
	 * @link http://en.wikipedia.org/wiki/Breadth-first_search
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param array $walk | Walk array
	 * @param array $tree | Tree array
	 * @return array $result | Walk offset and matching node id
	 */

	public function walkIntersectTree_iterator($walk, $tree) {


		// Calculate offsets
		// ================================================

		$walk_offset = count($walk);

		if( is_array($tree["children"]) ){

			$children_count = count($tree["children"]);
		}
		else {
			$children_count = 0;
		}

		// If either termination condition is met, return
		// ================================================

		if( ($walk_offset == 0) || ($children_count == 0) ){

			$result = array(    "node_id"=>$tree["node_id"],
					    "walk_offset"=>$walk_offset
			);

			return $result;
		}

		// Loop through each child node, searching for the
		// child node with the longest walk
		// ================================================

		$min_offset = null;
		$min_node = null;

		foreach( $tree["children"] as $data){

			if($data["slug"] == $walk[0]){

				$reduced_walk = array_slice($walk, 1);
				$intersect = self::walkIntersectTree_iterator($reduced_walk, $data);

				if( ($min_offset === null) || ($intersect["walk_offset"] < $min_offset) ){

					$min_offset = $intersect["walk_offset"];
					$min_node = $intersect["node_id"];
				}
			}
		}
		unset($data);

		// Return the child node with the longest walk, or if
		// there was no matching child node, return this node
		// ================================================

		if($min_offset === null){

			$result = array(
					    "node_id"=>$tree["node_id"],
					    "walk_offset"=>$walk_offset
			);
		}
		else {
			$result = array(
					    "node_id"=>$min_node,
					    "walk_offset"=>$min_offset
			);
		}

		return $result;

	}


	/**
	 * Checks if a slug matches an active "primary" BuddyPress component. Primary components
	 * are components which can exist as a top-level page on the site, and in some cases
	 * a secondary page nested below the "members" component. Third-party components following
	 * the "BuddyPress Example Component" pattern will appear in the results.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $slug | Name of slug to check
	 * @return string $result | Exception on failure. Null on nonexistent. Name of component on success.
	 */

	public function getPrimaryComponentName($slug) {


		if( empty($slug) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty slug",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		// If the BP Pages object hasn't been loaded yet, try to load it
		if( empty($this->bp->pages) ){

			try {
				$this->bp->pages = self::buildDirectoryPages($this->flat_pages);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Failed to load BP pages object",
					'data'=>array("bp_pages"=>$this->bp->pages),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		foreach($this->bp->pages as $component_name => $data){

			// NOTE: We cannot use an algorithm that checks against $this->bp->active_components,
			// because its impossible for 3rd-party components to add themselves to this array
			// using the 'bp_active_components' filter. The filter is placed so early in the call
			// stack it runs before 3rd-party components can load any of their plugin files.

			if( !array_key_exists($component_name, $this->bp->deactivated_components)   // 1) Component is active
			    && $data->name == $slug )						    // 2) Slug matches
			{
				return $component_name;
			}
		}
		unset($component_name, $data);

		// Separate check for search component (because its not a real BP component,
		// and its not included in the $bp->active_components array)

		if($slug == bp_get_search_slug()){

			return "search";
		}

		return null;

	}

	/**
	 * Generates the BP component pages array
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param array $flat_pages | Flat array of all WordPress pages on the site
	 * @return obj $pages | Exception on failure. Structured object containing page ID's, Names, and Slugs on success.
	 */
	function buildDirectoryPages($flat_pages) {


		if( empty($flat_pages) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty flat_pages array",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}


		$page_ids = (array)bp_core_get_directory_page_ids();

		if( empty($page_ids) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"BP core directory page ids option is empty",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$pages = new stdClass;

		// Iterate through each entry in the BP pages config option
		foreach( $page_ids as $component_id => $bp_page_id ) {

			// Iterate through each WP site page in the flat pages array
			foreach( $flat_pages as $wp_page_id => $data ) {

				// If the page ids match, add this page to the components array
				if( $wp_page_id == $bp_page_id ) {

					$pages->{$component_id}->name  = $data['slug'];
					$pages->{$component_id}->id    = $wp_page_id;
					$pages->{$component_id}->title = $data['title'];

					$stem = array();
					$stem[]	= $data['slug'];

					$parent = $data['parent'];

					// If the page is not attached to the root node, traverse the page tree backwards to the
					// root node generating the reverse walk, then flip it and implode it to a string.

					while( $parent != 0 ){

						$stem[]	= $flat_pages[$parent]['slug'];
						$parent = $flat_pages[$parent]['parent'];
					}

					// NOTE: BuddyPress incorrectly calls this a "slug", which is confusing. The correct term
					// is a "stem" (in string form) and a "walk" (in array form).

					$pages->{$component_id}->slug = implode( '/', array_reverse( (array)$stem ) );
				}

				unset($slug);
			}
			unset($wp_page_id, $data);

		}
		unset($component_id, $bp_page_id);

		return apply_filters( 'bp_core_get_directory_pages', $pages );

	}


	/**
	 * Load a specific template file, with fallback support.
	 *
	 * Example: bp_core_load_template( 'members/index' );
	 * Loads: wp-content/themes/[activated_theme]/members/index.php
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $templates | Single template name as string. Multiple template names as array of string.
	 * @return die $result | Exception on failure. Loads template and terminates thread on success.
	 */
	function loadTemplate($templates) {


		if( !$this->intersect["endpoint_id"] ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Cannot load template because router was unable to intersect the current
					 request URI with any pages in the site's page tree.",
				'data'=>array("intersect"=>$this->intersect,
					      "walk"=>$this->walk,
					      "templates"=>$templates),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		// Add a ".php" suffix to each template file in the $templates array
		foreach( (array)$templates as $template ){

			$filtered_templates[] = $template . '.php';
		}

		// Filter the template locations so that plugins can alter where they are located
		$located_template = apply_filters( 'bp_located_template', locate_template( (array) $filtered_templates, false ), $filtered_templates );

		if($located_template){

			// Explicitly set WP's internal query variables to the correct state (because the
			// default is to 404 the page)

			$this->wp_query->is_page = true;
			$this->wp_query->is_404 = false;

			// Explicitly set the HTTP headers. Note that this only sets the headers for the web
			// page. The web server generates its own headers for individual items such as images
			// and CSS stylesheets loaded by the page.

			$protocol = $_SERVER["SERVER_PROTOCOL"];
			$code = 200;
			$text = "OK";

			if( ($protocol != 'HTTP/1.1') && ($protocol != 'HTTP/1.0') ){

				$protocol = 'HTTP/1.0';
			}

			$status_header = "$protocol $code $text";

			header($status_header, true, $code);

			load_template( apply_filters( 'bp_load_template', $located_template ) );

		}

		if(!$this->unit_test){

			// TODO: It's bad practice to place silent die() calls all over an application's code because it
			// makes it very difficult to unit-test. There should only be ONE silent die() in an app, used on
			// successful termination in the controller's core. Beyond that, die() should ONLY be used in
			// a "kernel panic" situation, and should ALWAYS include debugging info like line numbers and
			// a variable dump.

			die;
		}

	}

	/**
	 * Catches URI's at /members/user_name/* when the "extended profiles" component has been disabled
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function catchProfileURI() {

		if( !bp_is_active('xprofile') ){

			bp_core_load_template( apply_filters( 'bp_core_template_display_profile', 'members/single/home' ) );
		}

	}


	/**
	 * Catches invalid access to BuddyPress pages and redirects them accordingly.
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function catchNoAccess() {

		// If $bp_no_status_set is true, we're redirecting to an accessible page

		if($this->bp_no_status_set){

			return false;
		}

		if( !isset($this->wp_query->queried_object) && !bp_is_blog_page() ){

			bp_do_404();
		}

	}


	/**
	 * Redirects a user to login for BP pages that require access control and adds an error message (if
	 * one is provided). If authenticated, redirects user back to requested content by default.
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function handleNoAccess($args = '') {

		global $bp;

		$defaults = array(
			'mode'     => '1',			    // 1 = $root, 2 = wp-login.php
			'message'  => __( 'You must log in to access the page you requested.', 'buddypress' ),
			'redirect' => wp_guess_url(),	// the URL you get redirected to when a user successfully logs in
			'root'     => $bp->root_domain	// the landing page you get redirected to when a user doesn't have access
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		// Apply filters to these variables
		$mode		= apply_filters( 'bp_no_access_mode', $mode, $root, $redirect, $message );
		$redirect	= apply_filters( 'bp_no_access_redirect', $redirect, $root, $message, $mode );
		$root		= trailingslashit( apply_filters( 'bp_no_access_root', $root, $redirect, $message, $mode ) );
		$message	= apply_filters( 'bp_no_access_message', $message, $root, $redirect, $mode );

		switch ( $mode ) {
			// Option to redirect to wp-login.php
			// Error message is displayed with bp_core_no_access_wp_login_error()
			case 2 :
				if ( $redirect ) {
					bp_core_redirect( wp_login_url( $redirect ) . '&action=bpnoaccess' );
				} else {
					bp_core_redirect( $root );
				}

				break;

			// Redirect to root with "redirect_to" parameter
			// Error message is displayed with bp_core_add_message()
			case 1 :
			default :
				if ( $redirect ) {
					$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $root );
				} else {
					$url = $root;
				}

				if ( $message ) {
					bp_core_add_message( $message, 'error' );
				}

				bp_core_redirect( $url );

				break;
		}
	}



} // End of class FOX_router


?>