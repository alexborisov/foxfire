<?php

/**
 * BP-MEDIA SCRIPT REGISTER CLASS
 * Registers scripts used by the plugin. Note that scripts are not *actually loaded in the browser* unless
 * wp_enqueue_script() is used to enqueue the script in either sub.admin.core.php, or the enqueueSiteScripts()
 * or enqueueAdminScripts() methods within an album module, page module, network module, or media module's
 * loader.php file.
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage AJAX API
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */


class bpm_registerScripts {


	public function admin_jQuery_UI(){

		    wp_register_script('bpm-jquery-ui-accordion', BPM_URL_LIB .'/jquery/ui/jquery.ui.accordion.js');
		    wp_register_script('bpm-jquery-ui-autocomplete', BPM_URL_LIB .'/jquery/ui/jquery.ui.autocomplete.js');
		    wp_register_script('bpm-jquery-ui-button', BPM_URL_LIB .'/jquery/ui/jquery.ui.button.js');
		    wp_register_script('bpm-jquery-ui-core', BPM_URL_LIB .'/jquery/ui/jquery.ui.core.js');
		    wp_register_script('bpm-jquery-ui-datepicker', BPM_URL_LIB .'/jquery/ui/jquery.ui.datepicker.js');
		    wp_register_script('bpm-jquery-ui-dialog', BPM_URL_LIB .'/jquery/ui/jquery.ui.dialog.js');
		    wp_register_script('bpm-jquery-ui-draggable', BPM_URL_LIB .'/jquery/ui/jquery.ui.draggable.js');
		    wp_register_script('bpm-jquery-ui-droppable', BPM_URL_LIB .'/jquery/ui/jquery.ui.droppable.js');
		    wp_register_script('bpm-jquery-ui-mouse', BPM_URL_LIB .'/jquery/ui/jquery.ui.mouse.js');
		    wp_register_script('bpm-jquery-ui-position', BPM_URL_LIB .'/jquery/ui/jquery.ui.position.js');
		    wp_register_script('bpm-jquery-ui-progressbar', BPM_URL_LIB .'/jquery/ui/jquery.ui.progressbar.js');
		    wp_register_script('bpm-jquery-ui-resizable', BPM_URL_LIB .'/jquery/ui/jquery.ui.resizable.js');
		    wp_register_script('bpm-jquery-ui-selectable', BPM_URL_LIB .'/jquery/ui/jquery.ui.selectable.js');
		    wp_register_script('bpm-jquery-ui-slider', BPM_URL_LIB .'/jquery/ui/jquery.ui.slider.js');
		    wp_register_script('bpm-jquery-ui-sortable', BPM_URL_LIB .'/jquery/ui/jquery.ui.sortable.js');
		    wp_register_script('bpm-jquery-ui-tabs', BPM_URL_LIB .'/jquery/ui/jquery.ui.tabs.js');
		    wp_register_script('bpm-jquery-ui-widget', BPM_URL_LIB .'/jquery/ui/jquery.ui.widget.js');

		    wp_register_script('bpm-jquery-effects-core', BPM_URL_LIB .'/jquery/ui/jquery.effects.core.js');
		    wp_register_script('bpm-jquery-effects-fade', BPM_URL_LIB .'/jquery/ui/jquery.effects.fade.js');

	}

	public function admin_albumModules(){

		    wp_register_script('bpm-albumModules', BPM_URL_CORE .'/js/bpm.album.modules.panel.dev.js');
	}

	public function admin_pageModules(){

		    wp_register_script('bpm-adminNavTarget', BPM_URL_CORE .'/js/bpm.admin.nav_target.dev.js');
		    wp_register_script('bpm-adminDraggableChips', BPM_URL_CORE .'/js/bpm.admin.draggable_chips.dev.js');
		    wp_register_script('bpm-adminFloatingKeypicker', BPM_URL_CORE .'/js/bpm.admin.draggable_chips_floating_keypicker.dev.js');
	}

	public function admin_notifier(){

		    wp_register_script('bpm-adminNotifier', BPM_URL_CORE .'/js/bpm.admin.notifier.dev.js');
	}

	public function admin_selfTest(){

		    wp_register_script('bpm-adminSelfTest', BPM_URL_CORE .'/js/bpm.self.test.dev.js');
	}

	public function registerAll(){

		self::admin_jQuery_UI();
		self::admin_albumModules();
		self::admin_pageModules();
		self::admin_notifier();
		self::admin_selfTest();
	}

	public function ajax_api(){

		$paths = BPM_sUtil::glob_recursive(BPM_PATH_AJAX . "/*.js");
		$urls = BPM_sUtil::pluginPathToURL($paths);
		
		foreach($urls as $url){

			$hash = md5($url);

			wp_register_script($hash, $url);
			wp_enqueue_script($hash);
		}
		unset($url);

	}

} // End of class bpm_registerScripts

?>