<?php

/**
 * FOXFIRE ADMIN PAGE CLASS "CORE SETTINGS"
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

class FOX_tab_addmedia {


	/**
	 * Renders the "Uploader" tab
	 *
	 * This tab rendering function creates a single tab within the admin page that its parent class generates. The tab's form
	 * contains a hidden field called 'page_options'. The class's "processor" function parses the variable names in this field
	 * to determine which POST data fields to load and which objects in the $bp->bpa->options[] global to update.
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function render() {

		global $fox;

		?>

		<!-- Begin Adding Media Settings -->

		<form name="uploaderform" method="post" action="<?php echo $this->filepath.'#add_media'; ?>">

		    <?php wp_nonce_field('fox_admin_settings') ?>

		    <?php $fox->config->initNodesArray(); ?>

		    <div class="panel_section">

			<div class="title"><?php _e('HTTP Uploader',"foxfire") ?> </div>

			<div class="fox_section_advice">
			    <?php _e('Works on all browsers and platforms, but can only upload one file per session.',"foxfire") ?>
			</div>

			<table class="form-table bpa-options">

			    <tr>
				<th scope="row"><?php _e( 'Enable HTTP uploader', "foxfire" ) ?></th>
				<td>
				    <input type="radio" value="1"
					<?php $fox->config->printNodeName("file", "uploader", "enableHTTP"); ?>
					<?php checked(true, $fox->config->getNodeVal("file", "uploader", "enableHTTP") ); ?> />
					<?php _e( 'Yes', "foxfire" ) ?> &nbsp;
				    <input type="radio" value="0"
					<?php $fox->config->printNodeName("file", "uploader", "enableHTTP"); ?>
					<?php checked('', $fox->config->getNodeVal("file", "uploader", "enableHTTP") ); ?> />
					<?php _e( 'No', "foxfire" ) ?>
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Max upload size',"foxfire"); ?></th>
				<td>
				    <input type="text" size="16" maxlength="16"
					<?php $fox->config->printNodeName("file", "uploader", "singleMaxSize"); ?>
					<?php $fox->config->printNodeVal("file", "uploader", "singleMaxSize"); ?> />
					<?php _e('bytes',"foxfire"); ?>
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Max threads per user',"foxfire"); ?></th>
				<td>
				    <input type="text" size="6" maxlength="6"
					<?php $fox->config->printNodeName("file", "uploader", "singleMaxStreams"); ?>
					<?php $fox->config->printNodeVal("file", "uploader", "singleMaxStreams"); ?> />
				</td>
			    </tr>

			</table>

		    </div>

		    <div class="panel_section">

			<div class="title"><?php _e('Flash Uploader',"foxfire") ?></div>

			<div class="fox_section_advice">
			    <?php _e('Works only in browsers that support Javascript and Flash, but can upload hundreds of files per session.',"foxfire") ?>
			</div>

			<table class="form-table bpa-options">

			    <tr>
				<th scope="row"><?php _e( 'Enable flash uploader', "foxfire" ) ?></th>
				<td>
				    <input type="radio" value="1"
					<?php $fox->config->printNodeName("file", "uploader", "enableMulti"); ?>
					<?php checked(true, $fox->config->getNodeVal("file", "uploader", "enableMulti") ); ?> />
					<?php _e( 'Yes', "foxfire" ) ?> &nbsp;
				    <input type="radio" value="0"
					<?php $fox->config->printNodeName("file", "uploader", "enableMulti"); ?>
					<?php checked('', $fox->config->getNodeVal("file", "uploader", "enableMulti") ); ?> />
					<?php _e( 'No', "foxfire" ) ?>
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Max upload size',"foxfire"); ?></th>
				<td>
				    <input type="text" size="16" maxlength="16"
					<?php $fox->config->printNodeName("file", "uploader", "multiMaxSize"); ?>
					<?php $fox->config->printNodeVal("file", "uploader", "multiMaxSize"); ?> />
					<?php _e('bytes',"foxfire"); ?>
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Max threads per user',"foxfire"); ?></th>
				<td>
				    <input type="text" size="6" maxlength="6"
					<?php $fox->config->printNodeName("file", "uploader", "multiMaxStreams"); ?>
					<?php $fox->config->printNodeVal("file", "uploader", "multiMaxStreams"); ?> />
				</td>
			    </tr>

			</table>
		    </div>

		    <div class="panel_section">

			<div class="title"><?php _e('Media Download',"foxfire") ?></div>

			<div class="fox_section_advice">
			    <?php _e('Downloads remote media files from other servers.',"foxfire") ?>
			</div>

			<table class="form-table bpa-options">

			    <tr valign="top">
				<th align="left"><?php _e('Max remote file size',"foxfire"); ?></th>
				<td>
				    <input type="text" size="16" maxlength="16"
					<?php $fox->config->printNodeName("file", "downloader", "maxSize"); ?>
					<?php $fox->config->printNodeVal("file", "downloader", "maxSize"); ?> />
					<?php _e('bytes',"foxfire"); ?>
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Max remote file download timeout',"foxfire"); ?></th>
				<td>
				    <input type="text" size="6" maxlength="6"
					<?php $fox->config->printNodeName("file", "downloader", "maxTime"); ?>
					<?php $fox->config->printNodeVal("file", "downloader", "maxTime"); ?> />
					<?php _e('seconds',"foxfire"); ?>
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Mandatory content-length header',"foxfire"); ?></th>
				<td>
				    <input type="radio" value="1"
					<?php $fox->config->printNodeName("file", "downloader", "requireLengthHeader"); ?>
					<?php checked(true, $fox->config->getNodeVal("file", "downloader", "requireLengthHeader") ); ?> />
					<?php _e( 'Yes', "foxfire" ) ?> &nbsp;
				    <input type="radio" value="0"
					<?php $fox->config->printNodeName("file", "downloader", "requireLengthHeader"); ?>
					<?php checked('', $fox->config->getNodeVal("file", "downloader", "requireLengthHeader") ); ?> />
					<?php _e( 'No', "foxfire" ) ?>
					<br/><?php _e('Download a file only if the hosting server declares the file size (could reject valid media if remote server is not well configured)',"foxfire"); ?>
				</td>
			    </tr>

			    <tr valign="top">
				<th align="left"><?php _e('Strict validation before download',"foxfire"); ?></th>
				<td>
				    <input type="radio" value="1"
					<?php $fox->config->printNodeName("file", "downloader", "requireStrictValidation"); ?>
					<?php checked(true, $fox->config->getNodeVal("file", "downloader", "requireStrictValidation") ); ?> />
					<?php _e( 'Yes', "foxfire" ) ?> &nbsp;
				    <input type="radio" value="0"
					<?php $fox->config->printNodeName("file", "downloader", "requireStrictValidation"); ?>
					<?php checked('', $fox->config->getNodeVal("file", "downloader", "requireStrictValidation") ); ?> />
					<?php _e( 'No', "foxfire" ) ?>
					<br/><?php _e('Be sure to not download unsupported media (could reject valid media if remote server is not well configured)',"foxfire"); ?>
				</td>
			    </tr>

			</table>
		    </div>

		    <div class="panel_section">

			<div class="title"><?php _e('Media Validation',"foxfire") ?></div>

			<div class="fox_section_advice">
			    <?php _e('Local file (uploaded or downloaded) validation',"foxfire") ?>
			</div>

			<table class="form-table bpa-options">

    <?php /*
			<tr>
			    <th valign="top"><?php _e('Content type detection method ',"foxfire") ?></th>
			    <td>
				    <?php $available_methods = FOX_Multimedia::test_available_detection_methods(); ?>

				    <label>
					    <input name="media_validation_detection_method" type="radio" value="fileinfo"<?php
					    disabled( !in_array('fileinfo', $available_methods) );
					    checked('fileinfo', $bp->bpa->options['media_validation_detection_method']); ?> />
					    <?php echo sprintf(__('php Fileinfo extension %s', "foxfire"), '<a href="http://www.php.net/manual/en/book.fileinfo.php">[->php.net]</a>' ); ?>
					    <?php if(!in_array('fileinfo', $available_methods)) _e('(disabled: php extension not available)', "foxfire"); ?>
				    </label><br />

				    <label>
					    <input name="media_validation_detection_method" type="radio" value="shell"<?php
					    disabled( !in_array('shell', $available_methods) );
					    checked('shell', $bp->bpa->options['media_validation_detection_method']); ?> />
					    <?php _e('this shell command (use \'%s\' as filepath): ', "foxfire"); ?>
				    </label>
				    <input type="text" size="16" maxlength="16" name="media_validation_detector_shell_command" value="<?php echo $bp->bpa->options['media_validation_detector_shell_command']; ?>" />
				    <?php if(!in_array('shell', $available_methods)) _e('(disabled: command not working or execution not permitted)', "foxfire"); ?>
				    <br />

				    <label>
					    <input name="media_validation_detection_method" type="radio" value="mime_content_type"<?php
					    disabled( !in_array('mime_content_type', $available_methods) );
					    checked('mime_content_type', $bp->bpa->options['media_validation_detection_method']); ?> />
					    <?php echo sprintf(__('php mime_content_type function %s (deprecated, use Fileinfo if possible)', "foxfire"), '<a href="http://it.php.net/manual/en/function.mime-content-type.php">[->php.net]</a>' ); ?>
					    <?php if(!in_array('fileinfo', $available_methods)) _e('(disabled: php extension not available)', "foxfire"); ?>
				    </label><br />

				    <label>
					    <input name="media_validation_detection_method" type="radio" value="builtin"<?php
					    disabled( !in_array('builtin', $available_methods) );
					    checked('builtin', $bp->bpa->options['media_validation_detection_method']); ?> />
					    <?php _e('builtin detector, that is probably slower and less accurate, but should work in all environments', "foxfire"); ?>
					    <?php if(!in_array('builtin', $available_methods)) _e('(disabled: not available, please report this issue)', "foxfire"); ?>
				    </label><br />

			    </td>
			</tr>
     *
     */ ?>

			<tr valign="top">
				<th align="left"><?php _e('Validate content type',"foxfire"); ?></th>
				<td>
				    <input type="radio" value="1"
					<?php $fox->config->printNodeName("file", "downloader", "validateContentType"); ?>
					<?php checked(true, $fox->config->getNodeVal("file", "downloader", "validateContentType") ); ?> />
					<?php _e( 'Yes', "foxfire" ) ?> &nbsp;
				    <input type="radio" value="0"
					<?php $fox->config->printNodeName("file", "downloader", "validateContentType"); ?>
					<?php checked('', $fox->config->getNodeVal("file", "downloader", "validateContentType") ); ?> />
					<?php _e( 'No', "foxfire" ) ?>
					<br/><?php _e('Should always be active as a security measure, deactivate only if you have problems with content type detection and you understand the risks of not strictly checking file type',"foxfire"); ?>
				</td>
			</tr>

			</table>
		    </div>

		    <?php $fox->config->printNodesArray(); ?>

		    <div class="fox_submit_h_panel_wrap">
			<div class="submit"><input class="fox-button" type="submit" name="updateoption" value="<?php _e('Save Changes') ;?>"/></div>
		    </div>

		</form>

		<!-- End Adding Media Settings -->
	<?php
	}

	/**
	 * Adds the tab's scripts to the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueScripts() {

	}


	/**
	 * Adds the tab's styles to the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueStyles() {

	}

} // End of class FOX_tab_addmedia

?>