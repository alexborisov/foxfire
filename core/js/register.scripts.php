<?php

/**
 * FOXFIRE SCRIPT REGISTER CLASS
 * Registers scripts used by the plugin. Note that scripts are not *actually loaded in the browser* unless
 * wp_enqueue_script() is used to enqueue the script in either sub.admin.core.php, or the enqueueSiteScripts()
 * or enqueueAdminScripts() methods within an album module, page module, network module, or media module's
 * loader.php file.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage AJAX API
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */


class FOX_registerScripts {


	public function admin_jQuery_UI(){

		    wp_register_script('fox-jquery-ui-accordion', FOX_URL_LIB .'/jquery/ui/jquery.ui.accordion.js');
		    wp_register_script('fox-jquery-ui-autocomplete', FOX_URL_LIB .'/jquery/ui/jquery.ui.autocomplete.js');
		    wp_register_script('fox-jquery-ui-button', FOX_URL_LIB .'/jquery/ui/jquery.ui.button.js');
		    wp_register_script('fox-jquery-ui-core', FOX_URL_LIB .'/jquery/ui/jquery.ui.core.js');
		    wp_register_script('fox-jquery-ui-datepicker', FOX_URL_LIB .'/jquery/ui/jquery.ui.datepicker.js');
		    wp_register_script('fox-jquery-ui-dialog', FOX_URL_LIB .'/jquery/ui/jquery.ui.dialog.js');
		    wp_register_script('fox-jquery-ui-draggable', FOX_URL_LIB .'/jquery/ui/jquery.ui.draggable.js');
		    wp_register_script('fox-jquery-ui-droppable', FOX_URL_LIB .'/jquery/ui/jquery.ui.droppable.js');
		    wp_register_script('fox-jquery-ui-mouse', FOX_URL_LIB .'/jquery/ui/jquery.ui.mouse.js');
		    wp_register_script('fox-jquery-ui-position', FOX_URL_LIB .'/jquery/ui/jquery.ui.position.js');
		    wp_register_script('fox-jquery-ui-progressbar', FOX_URL_LIB .'/jquery/ui/jquery.ui.progressbar.js');
		    wp_register_script('fox-jquery-ui-resizable', FOX_URL_LIB .'/jquery/ui/jquery.ui.resizable.js');
		    wp_register_script('fox-jquery-ui-selectable', FOX_URL_LIB .'/jquery/ui/jquery.ui.selectable.js');
		    wp_register_script('fox-jquery-ui-slider', FOX_URL_LIB .'/jquery/ui/jquery.ui.slider.js');
		    wp_register_script('fox-jquery-ui-sortable', FOX_URL_LIB .'/jquery/ui/jquery.ui.sortable.js');
		    wp_register_script('fox-jquery-ui-tabs', FOX_URL_LIB .'/jquery/ui/jquery.ui.tabs.js');
		    wp_register_script('fox-jquery-ui-widget', FOX_URL_LIB .'/jquery/ui/jquery.ui.widget.js');

		    wp_register_script('fox-jquery-effects-core', FOX_URL_LIB .'/jquery/ui/jquery.ui.effect.js');
		    wp_register_script('fox-jquery-effects-fade', FOX_URL_LIB .'/jquery/ui/jquery.ui.effect-fade.js');		    		    

	}

	public function admin_dataTables(){

		    wp_register_script('fox-datatables-core', FOX_URL_LIB .'/datatables/media/js/jquery.dataTables.min.js');	    		    
	}
	
	public function admin_dataTables_columnFilter(){

		    wp_register_script('fox-datatables-plugin-columnfilter', FOX_URL_LIB .'/datatables/extras/ColumnFilter/media/js/jquery.dataTables.columnFilter.js');	    		    
	}
	
	public function admin_dataTables_tableToolsPlus(){

		    wp_register_script('fox-datatables-plugin-zeroclipboard', FOX_URL_LIB .'/datatables/extras/TableToolsPlus/media/js/ZeroClipboard.js');	    
		    wp_register_script('fox-datatables-plugin-tabletoolsplus', FOX_URL_LIB .'/datatables/extras/TableToolsPlus/media/js/jquery.dataTables.tableToolsPlus.js');
	 
	}	
	
	public function admin_albumModules(){

		    wp_register_script('fox-albumModules', FOX_URL_CORE .'/js/fox.album.modules.panel.dev.js');
	}

	public function admin_pageModules(){

		    wp_register_script('fox-adminNavTarget', FOX_URL_CORE .'/js/fox.admin.nav_target.dev.js');
		    wp_register_script('fox-adminDraggableChips', FOX_URL_CORE .'/js/fox.admin.draggable_chips.dev.js');
		    wp_register_script('fox-adminFloatingKeypicker', FOX_URL_CORE .'/js/fox.admin.draggable_chips_floating_keypicker.dev.js');
	}

	public function admin_notifier(){

		    wp_register_script('fox-adminNotifier', FOX_URL_CORE .'/js/fox.admin.notifier.dev.js');
	}
	
	public function admin_selfTest(){

		    wp_register_script('fox-adminSelfTest', FOX_URL_CORE .'/js/fox.self.test.dev.js');
	}

	public function registerAll(){

		self::admin_jQuery_UI();
		self::admin_dataTables();		
		self::admin_albumModules();
		self::admin_pageModules();
		self::admin_notifier();
		self::admin_selfTest();
	}

	public function ajax_api(){

		$paths = FOX_sUtil::glob_recursive(FOX_PATH_AJAX . "/*.js");
		$urls = FOX_sUtil::pluginPathToURL($paths);
		
		foreach($urls as $url){

			$hash = md5($url);

			wp_register_script($hash, $url);
			wp_enqueue_script($hash);
		}
		unset($url);

	}

} // End of class fox_registerScripts

?>