<?php

/**
 * BP-MEDIA ADMIN PAGE "SERVER SELF TEST"
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Admin
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

// Prevent hackers from directly calling this page
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

// ============================================================================================================ //

class BPM_tab_server_selftest {


	/**
	 * Renders the "Debug" tab
	 *
	 * This tab rendering function creates a single tab within the admin page that its parent class generates. The tab's form
	 * contains a hidden field called 'page_options'. The class's "processor" function parses the variable names in this field
	 * to determine which POST data fields to load and which objects in the $bp->bpa->options[] global to update.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	function render() {

		global $bpm;

		?>
		<!-- Begin Config settings -->

		<form name="configform" method="POST" action="<?php echo $this->filepath.'#config'; ?>" >

		    <?php wp_nonce_field('bpm_admin_settings') ?>

		    <?php $bpm->config->initKeysArray(); ?>


			<script type="text/javascript">

				bpm_selfTest();

			</script>


			<div class="panel_section w30">

			    <div class="title"><?php _e('Test Options',"bp-media") ?> </div>

			    <div class="bpm_section_advice">
				<?php _e("These options control how tests are run on your server. BP-Media creates a 'ghost' copy of your website during
				    testing and runs the tests as a separate thread. You can safely run tests on a production website. Do not change the options
				    below unless you have been instructed to by a member of the BP-Media team.","bp-media") ?>
			    </div>

			    <table class="form-table">

				<tr>
				    <th><?php _e('Platform family', "bp-media" ) ?></th>
				    <td>
					<label>
					 <input type="radio" value="unix"
						<?php $bpm->config->printKeyName("system", "phpUnitTest", "platformFamily"); ?>
						<?php checked('unix', $bpm->config->getKeyVal("system", "phpUnitTest", "platformFamily") ); ?> />
						<?php _e('Unix', "bp-media" ) ?> &nbsp;
					</label>
					<label>
					    <input type="radio" value="windows"
						<?php $bpm->config->printKeyName("system", "phpUnitTest", "platformFamily"); ?>
						<?php checked('windows', $bpm->config->getKeyVal("system", "phpUnitTest", "platformFamily") ); ?> />
						<?php _e('Windows', "bp-media" ) ?> &nbsp;
					</label>
				    </td>
				</tr>

				<tr>
				    <th><?php _e('SQL Server',"bp-media") ?></th>
				    <td>
					Database <input type="text" size="15" maxlength="32"
					    <?php $bpm->config->printKeyName("system", "phpUnitTest", "dbServerName"); ?>
					    <?php $bpm->config->printKeyVal("system", "phpUnitTest", "dbServerName"); ?> /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
					Login <input type="text" size="6" maxlength="32"
					    <?php $bpm->config->printKeyName("system", "phpUnitTest", "dbServerLogin"); ?>
					    <?php $bpm->config->printKeyVal("system", "phpUnitTest", "dbServerLogin"); ?> /> &nbsp;
					Pass <input type="password" size="6" maxlength="32"
					    <?php $bpm->config->printKeyName("system", "phpUnitTest", "dbServerPass"); ?>
					    <?php $bpm->config->printKeyVal("system", "phpUnitTest", "dbServerPass"); ?> /> &nbsp;

				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('Table prefix',"bp-media"); ?></th>
				    <td>
				    <input type="text" size="5" maxlength="200"
					   <?php $bpm->config->printKeyName("system", "phpUnitTest", "tablePrefix"); ?>
					   <?php $bpm->config->printKeyVal("system", "phpUnitTest", "tablePrefix"); ?> />
				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('PHP path',"bp-media"); ?></th>
				    <td>
				    <input type="text" size="60" maxlength="200"
					   <?php $bpm->config->printKeyName("system", "phpUnitTest", "PHPPath"); ?>
					   <?php $bpm->config->printKeyVal("system", "phpUnitTest", "PHPPath"); ?> />
				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('WordPress path',"bp-media"); ?></th>
				    <td>
				    <input type="text" size="60" maxlength="200"
					   <?php $bpm->config->printKeyName("system", "phpUnitTest", "wordpressPath"); ?>
					   <?php $bpm->config->printKeyVal("system", "phpUnitTest", "wordpressPath"); ?> />
				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('Test suite path',"bp-media"); ?></th>
				    <td>
				    <input type="text" size="60" maxlength="200"
					   <?php $bpm->config->printKeyName("system", "phpUnitTest", "testsuitePath"); ?>
					   <?php $bpm->config->printKeyVal("system", "phpUnitTest", "testsuitePath"); ?> />
				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('Options',"bp-media"); ?></th>
				    <td>
				    <input type="text" size="60" maxlength="1024"
					   <?php $bpm->config->printKeyName("system", "phpUnitTest", "options"); ?>
					   <?php $bpm->config->printKeyVal("system", "phpUnitTest", "options"); ?> />
				    </td>
				</tr>

			    </table>

			</div>

			<?php $bpm->config->printKeysArray(); ?>

			<div class="bpm_submit_h_panel_wrap">
			    <div class="submit"><input class="bpm-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
			    <div class="submit"><input class="bpm-button" type="submit" name="run_self_tests" value="<?php _e('Run Tests') ;?>"/></div>
			</div>

		<?php

		if( isset($_POST['run_self_tests']) ){


		?>

			<div class="panel_section w30">

			    <div class="title"><?php _e('Terminal Window',"bp-media") ?> </div>

			    <div class="bpm_terminal_box">

				<div class="spinner">
				    <div class="icon"></div>
				    <div class="text_1">Running Tests</div>
				    <div class="text_2">This may take up to 10 minutes to complete on some shared servers.</div>
				    <div class="timer" id="test_timer"></div>
				</div>

			    </div>

			</div>

			<script type="text/javascript">

				// The timer has to be loaded here because the #test_timer id
				// doesn't exist in the DOM until this point. Trying to load the
				// timer JS earlier will cause a script error.

				window.onload = testTimer();

			</script>

		<?php
		}
		?>

	   </form>
	   <!-- End Path settings --!>

	<?php
	}

	/**
	 * Adds the tab's scripts to the page header
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueScripts() {

		wp_enqueue_script( 'bpm-adminSelfTest');
	}


	/**
	 * Adds the tab's styles to the page header
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueStyles() {

	}

} // End of class BPM_tab_server_selftest

?>