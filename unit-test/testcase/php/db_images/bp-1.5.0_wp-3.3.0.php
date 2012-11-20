<?php

/**
 * FOXFIRE UNIT TEST DIRECTORY REMAPPER
 *
 * Re-maps the directory paths in the master SQL image to the directory that the unit test script
 * is being run from. This has to be hand-built every time the master SQL image is updated. The master SQL
 * image does *not* have to be updated for every release of wp because the wp core will upgrade the active
 * SQL image to whatever is the current format. It *does* have to be upgraded for each new release of
 * BuddyPress, because bp doesn't detect and auto-update its tables unless it is deactivated/reactivated.
 *
 * @version 1.0
 * @since 1.0
 * @author original version from http://svn.automattic.com/wordpress-tests/
 * @package FoxFire
 * @subpackage Unit Test
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */


function fox_remap_bp15wp33($db_object, $stub, &$error=null){
    
// To create the re-mapping solution:
//
// 1) Create a WP install using the db 'fox_test' with user = test / pass = test
// 2) Install and activate BuddyPress
// 3) Install FoxFire to a DISTINCT plugin directory like "foo_plugin'
// 4) Activate FoxFire
// 5) Pull a db image
// 6) Delete the entire install and database
// 7) Create a new WP install using the db 'fox_test' with user = test / pass = test
// 8) Install and activate BuddyPress
// 9) Install FoxFire to a DISTINCT plugin directory like "bar_plugin', different from the first directory
// 10) Activate FoxFire
// 11) Pull a db image
// 12) Run a DIFF on the two db images
// 13) Find the various arrays that WP has stored to the db which have "foo_plugin" in them and which
//     have changed to "bar_plugin" in the second db image
// 14) Write code and SQL queries to find and re-map these arrays to the $stub passed to the remapper function
//
//     NOTE: We *cannot* use a simple "search and replace" on the DB file because wp often stores plugin info as
//     serialized arrays. If the active plugin directory has a different number of characters in it than the original
//     directory, the offsets in the serialized array won't be valid and WordPress won't load the plugins. This usually
//     causes a "Cannot find class FOX_db" error at the start of the unit tests.
	

	if($stub == "foxfire"){
		$error = " OK";
		return true;		
	}
	else {


		// Update 'active_plugins' option (sets which plugins are active)
		// =======================================================================================

		$sql = "SELECT option_value FROM wp_options WHERE option_name = 'active_plugins'";
		$result = $db_object->get_var($sql);

		if($result){

			// These are the database ops we're changing
			// INSERT INTO `wp_options` (`option_id`, `blog_id`, `option_name`, `option_value`, `autoload`) VALUES
			// (36, 0, 'active_plugins', 'a:2:{i:0;s:19:"foxfire/loader.php";i:1;s:24:"buddypress/bp-loader.php";}', 'yes'),
			// (112, 0, '_site_transient_update_plugins', 'O:8:"stdClass":3:{s:12:"last_checked";i:1313427422;s:7:"checked";a:4:{s:19:"akismet/akismet.php";s:5:"2.5.3";s:24:"buddypress/bp-loader.php";s:10:"1.5-beta-2";s:19:"foxfire/loader.php";s:5:"0.1.9";s:9:"hello.php";s:3:"1.6";}s:8:"response";a:0:{}}', 'yes'),
			// (154, 0, '_transient_plugin_slugs', 'a:4:{i:0;s:19:"akismet/akismet.php";i:1;s:24:"buddypress/bp-loader.php";i:2;s:19:"foxfire/loader.php";i:3;s:9:"hello.php";}', 'no'),

			$active_plugins = unserialize($result);
			$key = array_search('foxfire/loader.php', $active_plugins);

			if($key !== null){

				$active_plugins[$key] = $stub . "/loader.php";
				$active_plugins = serialize($active_plugins);

				$sql = "UPDATE wp_options SET option_value = '$active_plugins' WHERE option_name = 'active_plugins'";
				$result = $db_object->query($sql);

				if(!$result){
					$error = "'active_plugins' - couldn't update db";
					return false;
				}

			}
			else {
				$error = "'active_plugins' - couldn't find key";
				return false;
			}

		}
		else {
			$error = "'active_plugins' - couldn't find db row";
			return false;
		}


		// Update '_site_transient_update_plugins' option (sets last time plugins were checked for updates)
		// =======================================================================================

		$sql = "SELECT option_value FROM wp_options WHERE option_name = '_site_transient_update_plugins'";
		$result = $db_object->get_var($sql);

		if($result){

			$update_plugins = unserialize($result);
			$checked = $update_plugins->checked;

			$key = array_search('foxfire/loader.php', $checked);

			if($key !== null){

				$checked[$key] = $stub . "/loader.php";
				$update_plugins->checked = $checked;
				$update_plugins = serialize($update_plugins);

				$sql = "UPDATE wp_options SET option_value = '$update_plugins' WHERE option_name = '_site_transient_update_plugins'";
				$result = $db_object->query($sql);

				if(!$result){
					$error = "'_site_transient_update_plugins' - couldn't update db";
					return false;
				}

			}
			else {
				$error = "'_site_transient_update_plugins' - couldn't find key";
				return false;
			}

		}
		else {
			$error = "'_site_transient_update_plugins' - couldn't find db row";
			return false;
		}


		// Update '_transient_plugin_slugs' option (sets slugs for all plugins)
		// =======================================================================================

		$sql = "SELECT option_value FROM wp_options WHERE option_name = '_transient_plugin_slugs'";
		$result = $db_object->get_var($sql);

		if($result){

			$plugin_slugs = unserialize($result);
			$key = array_search('foxfire/loader.php', $plugin_slugs);

			if($key !== null){

				$plugin_slugs[$key] = $stub . "/loader.php";
				$plugin_slugs = serialize($plugin_slugs);

				$sql = "UPDATE wp_options SET option_value = '$plugin_slugs' WHERE option_name = '_transient_plugin_slugs'";
				$result = $db_object->query($sql);

				if(!$result){
					$error = "'_transient_plugin_slugs' - couldn't update db";
					return false;
				}

			}
			else {
				$error = "'_transient_plugin_slugs' - couldn't find key";
				return false;
			}

		}
		else {
			$error = "'_transient_plugin_slugs' - couldn't find db row";
			return false;
		}


		// If all db updates were successfully completed, return true
		// ==========================================================

		return true;



	} // ENDOF: if($stub == "foxfire")
	


} // ENDOF: function remap_directory


?>