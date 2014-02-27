<?php

/**
 * FOXFIRE ADMIN PAGE "SERVER SELF TEST"
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

class FOX_tab_server_selftest {


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

		<form name="configform" method="POST" action="<?php echo $this->filepath.'#config'; ?>" >

		    <?php wp_nonce_field('fox_admin_settings') ?>

		    <?php $fox->config->initNodesArray(); ?>


			<script type="text/javascript">

				fox_selfTest();

			</script>


			<div class="panel_section w30">

			    <div class="title"><?php _e('Test Options',"foxfire") ?> </div>

			    <div class="fox_section_advice">
				<?php _e("These options control how tests are run on your server. FoxFire creates a 'ghost' copy of your website during
				    testing and runs the tests as a separate thread. You can safely run tests on a production website. Do not change the options
				    below unless you have been instructed to by a member of the FoxFire team.","foxfire") ?>
			    </div>

			    <table class="form-table">

				<tr>
				    <th><?php _e('Platform family', "foxfire" ) ?></th>
				    <td>
					<label>
					 <input type="radio" value="unix"
						<?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "platformFamily"); ?>
						<?php checked('unix', $fox->config->getNodeVal("foxfire", "system", "phpUnitTest", "platformFamily") ); ?> />
						<?php _e('Unix', "foxfire" ) ?> &nbsp;
					</label>
					<label>
					    <input type="radio" value="windows"
						<?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "platformFamily"); ?>
						<?php checked('windows', $fox->config->getNodeVal("foxfire", "system", "phpUnitTest", "platformFamily") ); ?> />
						<?php _e('Windows', "foxfire" ) ?> &nbsp;
					</label>
				    </td>
				</tr>

				<tr>
				    <th><?php _e('SQL Server',"foxfire") ?></th>
				    <td>
					Database <input type="text" size="15" maxlength="32"
					    <?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "dbServerName"); ?>
					    <?php $fox->config->printNodeVal("foxfire", "system", "phpUnitTest", "dbServerName"); ?> /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
					Login <input type="text" size="6" maxlength="32"
					    <?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "dbServerLogin"); ?>
					    <?php $fox->config->printNodeVal("foxfire", "system", "phpUnitTest", "dbServerLogin"); ?> /> &nbsp;
					Pass <input type="password" size="6" maxlength="32"
					    <?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "dbServerPass"); ?>
					    <?php $fox->config->printNodeVal("foxfire", "system", "phpUnitTest", "dbServerPass"); ?> /> &nbsp;

				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('Table prefix',"foxfire"); ?></th>
				    <td>
				    <input type="text" size="5" maxlength="200"
					   <?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "tablePrefix"); ?>
					   <?php $fox->config->printNodeVal("foxfire", "system", "phpUnitTest", "tablePrefix"); ?> />
				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('PHP path',"foxfire"); ?></th>
				    <td>
				    <input type="text" size="60" maxlength="200"
					   <?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "PHPPath"); ?>
					   <?php $fox->config->printNodeVal("foxfire", "system", "phpUnitTest", "PHPPath"); ?> />
				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('WordPress path',"foxfire"); ?></th>
				    <td>
				    <input type="text" size="60" maxlength="200"
					   <?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "wordpressPath"); ?>
					   <?php $fox->config->printNodeVal("foxfire", "system", "phpUnitTest", "wordpressPath"); ?> />
				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('Test suite path',"foxfire"); ?></th>
				    <td>
				    <input type="text" size="60" maxlength="200"
					   <?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "testsuitePath"); ?>
					   <?php $fox->config->printNodeVal("foxfire", "system", "phpUnitTest", "testsuitePath"); ?> />
				    </td>
				</tr>

				<tr>
				    <th align="left"><?php _e('Options',"foxfire"); ?></th>
				    <td>
				    <input type="text" size="60" maxlength="1024"
					   <?php $fox->config->printNodeName("foxfire", "system", "phpUnitTest", "options"); ?>
					   <?php $fox->config->printNodeVal("foxfire", "system", "phpUnitTest", "options"); ?> />
				    </td>
				</tr>

			    </table>

			</div>

			<?php $fox->config->printNodesArray(); ?>

			<div class="fox_submit_h_panel_wrap">
			    <div class="submit"><input class="fox-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
			    <div class="submit"><input class="fox-button" type="submit" name="run_self_tests" value="<?php _e('Run Tests') ;?>"/></div>
			</div>

		<?php

		if( isset($_POST['run_self_tests']) ){


		?>

			<div class="panel_section w30">

			    <div class="title"><?php _e('Terminal Window',"foxfire") ?> </div>

			    <div class="fox_terminal_box">

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
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueScripts() {

		wp_enqueue_script( 'fox-adminSelfTest');
	}


	/**
	 * Adds the tab's styles to the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueStyles() {

	}

} // End of class FOX_tab_server_selftest

?>