<?php

/**
 * FOXFIRE PAGE MODULE BASE CLASS
 * Provides common functions used in page modules
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Page Modules
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

abstract class FOX_pageModule_base {


	var $plugin_slug;		    // WordPress slug for plugin that owns this page module
	var $plugin_path;		    // Path to the plugin that owns this page module
	
	var $module_name;		    // Human readable name for this page module
	var $module_slug;		    // Internal slug for this page module
	var $module_path;		    // Path to the page module's root folder	
	
	var $icon_path;			    // Path to the page module's icon
	
	var $description;		    // Description of this page module
	var $version;			    // Version number
	var $author;			    // Author HTML string 
	var $site;			    // Site HTML string 
	
	var $screen_function;		    // Screen function for this page module
	
	var $default_subnav_slug;	    // Default sub-navigation slug used by the page module when none is 
					    // supplied in the request URI
	
	var $has_admin_page;		    // True if the page module has an admin config page
	var $admin_class;		    // Name of the page module's admin loader class
	


	// ============================================================================================================ //


	/**
	 * Returns the human-readable name of the page module. This is displayed in the admin interface.
	 * Example: "Foo Module"
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Page module admin name
	 */
	
	public function getName(){

		return $this->module_name;
	}

	
	/**
	 * Returns the url-safe slug for the page module. Must be unique from all other
	 * page modules installed on the system. Example: "foo_module"
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Page module slug
	 */
	public function getSlug(){

		return $this->module_slug;
	}


	/**
	 * Returns the HTTP path to the module's icon. Icons MUST be 64px*64px .PNG files.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | path to icon file
	 */
	public function getIconPath(){
		
		return $this->icon_path;				
	}


	/**
	 * Returns a short (30 words) description of what the page module does.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | description
	 */
	public function getDesc(){

		return $this->description;
	}


	/**
	 * Returns the page module's version number. 64 chars max.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | version number
	 */
	public function getVersion(){

		return $this->version;
	}


	/**
	 * Returns a composited HTML string, including the name of the content module developer
	 * and a link to their personal or company website.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | version number
	 */
	public function getAuthor(){

		return $this->author;
	}


	/**
	 * Returns a composited HTML string containing a link to the page module's support page, or dedicated
	 * project site. Example: <a href='http://code.google.com/p/buddypress-media/'>The Radient Team</a>
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | version number
	 */
	public function getSite(){

		return $this->site;
	}


	/**
	 * Returns the page module's screen function
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Name of screen function
	 */
	public function getScreenFunction(){

		return $this->screen_function;
	}


	/**
	 * Returns the page module's default subnav slug
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Name of subnav slug
	 */
	public function getDefaultSubnavSlug(){

		return $this->default_subnav_slug;
	}
	
	
	/**
	 * Returns the unique ID for the page module as assigned by the plugin core.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return int | Exception on failure. Page module ID on success.
	 */
	public function getID(){

		global $fox;

		try{
			$module_data = $fox->pageModules->getBySlug($this->module_slug);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				    'numeric'=>1,
				    'text'=>"Error fetching module data",
				    'data'=> array('module_slug' => $this->module_slug),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
			));
		}

		if( !is_int($module_data["module_id"]) || ($module_data["module_id"] < 1) ){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"Module singleton returned invalid module_id",
				    'data'=>array('module_slug'=>$this->module_slug, 'module_id'=>$module_data["module_id"]),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
			));
		}

		return $module_data["module_id"];

	}
	
	
	/**
	 * Adds scripts used by the page module to the admin page header
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $tab | Name of the admin tab the plugin is rendering
	 */
	public function enqueueAdminScripts($tab){
		
		return $tab;			

	}


	/**
	 * Adds CSS styles used by the page module to the admin page header
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $tab | Name of the admin tab the plugin is rendering
	 */
	public function enqueueAdminStyles($tab){

	    
		wp_enqueue_style( $this->module_slug, $this->module_path . '/admin/admin.css', false, '2.8.1', 'screen' );
		
		return $tab;

	}


	/**
	 * Adds scripts used by the page module to the site page header
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $page | Name of the site page the plugin is rendering
	 */
	public function enqueueSiteScripts($page){

		return $page;
		
	}


	/**
	 * Adds CSS styles used by the page module to the site page header
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $page | Name of the site page the plugin is rendering
	 */
	public function enqueueSiteStyles($page){

		return $page;
		
	}
	
	
	/**
	 * Page module init function. Page modules place all of their hook and filter functions in the init
	 * function inside their class. When the core loads page modules, it fires the init function in each
	 * page module, attaching the module's functions to the core.
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	public function init(){

		return true;
	}	

	
	/**
	 * Checks if the page module has an admin config page in the WordPress backend
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | True if the page module has an admin config page. False if not.
	 */
	
	public function hasAdminPage(){

		return $this->has_admin_page;
	}	
	
	
	/**
	 * Renders the page module's WordPress backend admin config page
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Exception on failure. Composited HTML block on success.
	 */
	
	public function renderAdminPage(){

	    
		if(!$this->has_admin_page){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Attempting to render admin config page for a page module that doesn't have an admin config page.",
				'data'=> array('name'=>$this->module_name, 'module_slug'=>$this->module_slug, 'has_admin_page'=>$this->has_admin_page),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}
		
		
		try {
			// Include the default loader file
			require ( $this->module_path . '/admin/loader.php' );

			// Instantiate the loader class, passing it the page module instance
			$loader = new $this->admin_class($this);

			$loader->loadTabs();
			$loader->render();
		
		}
		catch (FOXexception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error loading page module admin class",
				'data'=> array('name'=>$this->module_name, 'module_slug'=>$this->module_slug, 'admin_class'=>$this->admin_class),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		
		return $loader;
				
	}	
	
	
	public function register(){

	    
		global $fox;

		$php_class = get_class($this);
		$active = false;
		
		try {
			$module_id = $fox->pageModules->register($this->module_slug, $this->module_name, $php_class, $active);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error registering page module",
				'data'=>array(	'plugin_slug'=>$this->plugin_slug, 'module_slug'=>$this->module_slug, 
						'module_name'=>$this->module_name, 'php_class'=>$php_class, 'active'=>$active),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return $module_id;

	}
	
	
	
	public function renderScreen(){	    

		global $fox;

		$this->enqueueSiteScripts(true);
		$this->enqueueSiteStyles(true);

		$fox->navigation->loadTemplate($this->plugin_path, "page", $this->module_slug, "index.php");

	}	
	
	
	
	
} // End of abstract class FOX_pageModule_base

?>