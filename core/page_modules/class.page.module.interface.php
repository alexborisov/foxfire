<?php

/**
 * FOXFIRE PAGE MODULES INTERFACES
 * Specifies the interfaces that page modules use to connect to the FoxFire core. Page modules
 * implement all interfaces listed below (or placeholder functions) otherwise the core will refuse
 * to load the module.
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

interface iFOX_pageModule {

	/**
	 * Returns the human-readable name of the page module. This is displayed in the admin interface.
	 * Example: "Video Gallery"
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Page module admin name
	 */
	public function getName();


	/**
	 * Returns the url-safe slug for the page module. Must be unique from all other
	 * page modules installed on the system. Example: "rad_photo"
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Page module slug
	 */
	public function getSlug();


	/**
	 * Returns the HTTP path to the module's icon. Icons MUST be 64px*64px .PNG files.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | path to icon file
	 */
	public function getIconPath();


	/**
	 * Returns a short (30 words) description of what the page module does.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | description
	 */
	public function getDesc();


	/**
	 * Returns the page module's version number. 64 chars max.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | version number
	 */
	public static function getVersion();


	/**
	 * Returns a composited HTML string, including the name of the content module developer 
	 * and a link to their personal or company website.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | version number
	 */
	public function getAuthor();


	/**
	 * Returns a composited HTML string containing a link to the page module's support page, or dedicated
	 * project site. Example: <a href='https://github.com/FoxFire'>The FoxFire Team</a>
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | version number
	 */
	public static function getSite();


	/**
	 * Returns the page module's screen function
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Name of screen function
	 */
	public function getScreenFunction();


	/**
	 * Returns the page module's default subnav slug
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Name of subnav slug
	 */
	public function getDefaultSubnavSlug();
	

	/**
	 * Performs all of the page module's installation operations
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | True on success. False on failure.
	 */
	public function install();


	/**
	 * Performs all of the page module's installation operations. Completely
	 * removes the module from the system, and deletes all items stored within
	 * pages that this module owns.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | True on success. False on failure.
	 */
	public function uninstall();


	/**
	 * Adds scripts used by the page module to the admin page header
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $tab | Name of the admin tab the plugin is rendering
	 */
	public function enqueueAdminScripts($tab);


	/**
	 * Adds CSS styles used by the page module to the admin page header
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $tab | Name of the admin tab the plugin is rendering
	 */
	public function enqueueAdminStyles($tab);


	/**
	 * Adds scripts used by the page module to the site page header
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $page | Name of the site page the plugin is rendering
	 */
	public function enqueueSiteScripts($page);


	/**
	 * Adds CSS styles used by the page module to the site page header
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $page | Name of the site page the plugin is rendering
	 */
	public function enqueueSiteStyles($page);


	/**
	 * Page module init function. Page modules place all of their hook and filter functions in the init
	 * function inside their class. When the core loads page modules, it fires the init function in each
	 * page module, attaching the module's functions to the core.
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	public function init();


	/**
	 * Specifies the object type the page module displays.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | "album" if the module displays albums, "media" if it displays media items.
	 */
	public function objectType();


	/**
	 * Specifies the view structure of the page module's screens
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array | mixed
	 */
	public function viewStructure();


	/**
	 * Returns the current view of the module's page
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | mixed
	 */
	public function getCurrentView();


	/**
	 * Renders the configuration page for a page module, as seen on the admin
	 * "page modules" screen.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | composited HTML block
	 */
	public function adminConfigPage();


	/**
	 * Renders the HTML block for the first page of items a user sees when they 
	 * view the WordPress page owned by the page module.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param ?
	 * @return string | composited HTML block
	 */
	public function render_landingPage();


	/**
	 * Renders the HTML block for for the second and subsequent pages of items a user
	 * sees when they view the WordPress page owned by the page module.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param ?
	 * @return string | composited HTML block
	 */
	public function render_galleryPage();
	
} // End of class iFOX_pageModule

?>