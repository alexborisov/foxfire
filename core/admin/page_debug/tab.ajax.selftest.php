<?php

/**
 * FOXFIRE ADMIN PAGE "AJAX SELF TEST"
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Admin
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

// Prevent hackers from directly calling this page
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

// ============================================================================================================ //

class FOX_tab_ajax_selftest {


	/**
	 * Renders the "Debug" tab
	 *
	 * This tab rendering function creates a single tab within the admin page that its parent class generates. The tab's form
	 * contains a hidden field called 'page_options'. The class's "processor" function parses the variable names in this field
	 * to determine which POST data fields to load and which objects in the $bp->bpa->options[] global to update.
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	function render() {

		global $fox;
		
		?>
		<!-- Begin Config settings -->

		<form name="displayform" method="POST" action="<?php echo $this->filepath.'#display'; ?>" >

		    <?php wp_nonce_field('fox_admin_settings') ?>

		    <?php $fox->config->initKeysArray(); ?>

		    <script type="text/javascript">


			jQuery(document).ready(function(){
			    

			    // Slide test settings box open and closed
			    // ================================================================================

			    jQuery("#fox_js_unit_test_settings .toggle").click( function() {

				var status = jQuery(this).closest("#fox_js_unit_test_settings").children(".fox_js_unit_test").attr('status');

				if( status == 'closed' ){
				
					// Because we're using floating divs, we have to manually calculate the height of the panel,
					// as jQuery's box height function will return the wrong height

					var optionRows = jQuery(this).closest("#fox_js_unit_test_settings").children(".fox_js_unit_test").find(".form-table tr").length;
					var open_height = ( optionRows * 20 + 130 ) + "px";

					jQuery(this).closest("#fox_js_unit_test_settings").children(".fox_js_unit_test").animate({"height": open_height}, {duration: 200 });
					jQuery(this).closest("#fox_js_unit_test_settings").children(".fox_js_unit_test").removeAttr('status', 'closed');
					jQuery(this).closest("#fox_js_unit_test_settings").children(".fox_js_unit_test").attr('status', 'open');

					jQuery(this).children(".text").html('Close');
				}
				else{				
					jQuery(this).closest("#fox_js_unit_test_settings").children(".fox_js_unit_test").animate({height: "0px"}, 500 );
					jQuery(this).closest("#fox_js_unit_test_settings").children(".fox_js_unit_test").removeAttr('status', 'open');
					jQuery(this).closest("#fox_js_unit_test_settings").children(".fox_js_unit_test").attr('status', 'closed');

					jQuery(this).children(".text").html('Open');
				}

			    });
			    
			});

		    </script>

		    <div class="panel_section w20" id="fox_js_unit_test_settings">

			<div class="title"><?php _e('Test Settings',"foxfire") ?></div>

			<div class="fox_js_unit_test" status="closed">

			    <div class="fox_subtitle">QUnit Engine Options</div>

			    <table class="form-table">

				    <tr>
					<th scope="row">
					<input type="checkbox" value="1"
					    <?php $fox->config->printKeyName("system", "jsUnitTest", "engineNoGlobals"); ?>
					    <?php checked(true, $fox->config->getKeyVal("system", "jsUnitTest", "engineNoGlobals") ); ?> />
					    <?php echo "noGlobals"; ?>
					</th>

					<td>
					    <?php echo "Fail test cases that try to create new global variables"; ?>
					</td>
				    </tr>

				    <tr>
					<th scope="row">
					<input type="checkbox" value="1"
					    <?php $fox->config->printKeyName("system", "jsUnitTest", "engineNoTryCatch"); ?>
					    <?php checked(true, $fox->config->getKeyVal("system", "jsUnitTest", "engineNoTryCatch") ); ?> />
					    <?php echo "noTryCatch"; ?>
					</th>

					<td>
					    <?php echo "Exit the test runner when test cases throw exceptions"; ?>
					</td>
				    </tr>

				    <tr>
					<th scope="row">
					<input type="checkbox" value="1"
					    <?php $fox->config->printKeyName("system", "jsUnitTest", "engineHidePassedTests"); ?>
					    <?php checked(true, $fox->config->getKeyVal("system", "jsUnitTest", "engineHidePassedTests") ); ?> />
					    <?php echo "hidePassed"; ?>
					</th>

					<td>
					    <?php echo "Hides passed tests from the test report"; ?>
					</td>
				    </tr>

				    <tr>
					<th scope="row">
					<input type="checkbox" value="1"
					    <?php $fox->config->printKeyName("system", "jsUnitTest", "showUserAgent"); ?>
					    <?php checked(true, $fox->config->getKeyVal("system", "jsUnitTest", "showUserAgent") ); ?> />
					    <?php echo "showUserAgent"; ?>
					</th>

					<td>
					    <?php echo "Display the browser's user-agent information"; ?>
					</td>
				    </tr>

			    </table>

			    <div class="fox_subtitle">Test Groups</div>

			    <table class="form-table">

				<?php

				// List all testgroups that aren't disabled in dictionary.php
				// ===================================================================

				$base_name = $fox->config->getKeyName("system", "jsUnitTest", "activeTests");
				$base_val = $fox->config->getKeyVal("system", "jsUnitTest", "activeTests");

				require ( FOX_PATH_TEST . '/testlib/js/test.core.php' );

				$cls = new FOX_test_js_core();
				$test_groups = $cls->listTestGroups();

				foreach( $test_groups as $group_slug => $group ){

					?>
					<tr>
					    <th scope="row">
						<input type="checkbox" value="1"
						    <?php echo "name='" .$base_name . "[" . $group_slug . "]'"; ?>
						    <?php checked(1, (int)$base_val[$group_slug]  ); ?> />
						<?php echo $group['name']; ?>
					    </th>

					    <td>
						<?php echo $group['desc']; ?>
					    </td>
					</tr>
					<?php
				}
				unset($group_slug, $group);
				?>

			    </table>

			    <div class="fox_submit_js_unit_test_spanner">
				<div class="fox_submit_js_unit_test_wrap">
				    <div class="submit"><input class="fox-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
				</div>
			    </div>

			</div>

			<div class="toggle">
			    <div class="text">Open</div>
			</div>
			
		    </div>

		    <?php $fox->config->printKeysArray(); ?>

		    <div class="fox_js_unit_test_run">
			<div class="submit"><input class="fox-button" id="fox_js_unit_test_run_button" type="submit" name="run_js_self_tests" value="<?php _e('Run Tests') ;?>"/></div>
		    </div>


		    <?php

		    // Include all active testcase files
		    // ===================================================================

		    $test_groups = $cls->listTestGroups();

		    echo "\n<!-- Enqueue FoxFire testcase JS files  ======================== -->";
		    echo "\n<!-- ============================================================ -->";
		    echo "\n";

		    foreach( $test_groups as $group_slug => $group ){

			if( $base_val[$group_slug] ){ 

			    foreach( $group['tests'] as $test_slug => $data ){

				echo "\n<script type='text/javascript' src='" . FOX_URL_TEST . "/testcase/js" . $data['file'];
				echo "?ver=" . mt_rand(1,1000000) . "'></script>";
			    }
			    unset($test_slug, $data);
			}

		    }
		    unset($group_slug, $group);

		    echo "\n\n<!-- ============================================================ -->";
		    echo "\n\n";

		    ?>
		    <script type="text/javascript">

			jQuery(document).ready(function(){

				QUnit.config.urlConfig = [];
				QUnit.config.notrycatch = <?php echo ($fox->config->getKeyVal("system", "jsUnitTest", "engineNoTryCatch") == true) ? "true" : "false" ?>;
				QUnit.config.noglobals = <?php echo ($fox->config->getKeyVal("system", "jsUnitTest", "engineNoGlobals") == true) ? "true" : "false" ?>;
				QUnit.config.hidepassed = <?php echo ($fox->config->getKeyVal("system", "jsUnitTest", "engineHidePassedTests") == true) ? "true" : "false" ?>;

			});

		    </script>

		    <div class="panel_section w30">

			<div class="title"><?php _e('Results',"foxfire") ?> </div>

			<div class="fox_selftest_results">

			     <?php
				 if( $fox->config->getKeyVal("system", "jsUnitTest", "showUserAgent") == true) {

					echo '<h2 id="qunit-userAgent"></h2>';
				 }
			     ?>

			     <ol id="qunit-tests"></ol>
			     <div id="qunit-fixture">test markup, will be hidden</div>
			     <div id="fox_debug_dump"></div>

			</div>

		    </div>

		</form>

	<?php
	}


	/**
	 * Adds the tab's scripts to the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueScripts() {

		$cls = new fox_registerScripts();
		$cls->ajax_api();
		
		wp_register_script("fox-qunit", FOX_URL_TEST . "/qunit/qunit.js");
		wp_enqueue_script("fox-qunit");
	}


	/**
	 * Adds the tab's styles to the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueStyles() {

		wp_enqueue_style( 'fox-qunit', FOX_URL_TEST . "/qunit/qunit.css", false, '2.8.1', 'screen' );	    
	}


} // End of class FOX_tab_ajax_selftest

?>