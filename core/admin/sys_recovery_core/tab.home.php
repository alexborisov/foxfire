<?php

/**
 * FOXFIRE SCREEN RENDERING CLASS "ADMIN-> RECOVERY"
 * Generates the admin screen "recovery" page. Handles form submissions. Updates global config variable and
 * stored settings in database when parameters are changed.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Admin
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

// Prevent hackers from directly calling this page
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('You are not allowed to call this page directly.');
}

// ============================================================================================================ //

class FOX_tab_home {


    /**
     * Renders the "System Recovery" tab
     *
     * This tab rendering function creates a single tab within the admin page that its parent class generates. The tab's form
     * contains a hidden field called 'page_options'. The class's "processor" function parses the variable names in this field
     * to determine which POST data fields to load and which objects in the $bp->bpa->options[] global to update.
     *
     * @version 0.1.9
     * @since 0.1.9
     */

    function render() {

        $versions = new FOX_version();

	?>

            <table class="form-table bpa-options">



	<?php if( !$versions->phpOK() ) { ?>

	    <div class="bpa-trouble-wrap">
		<div class="bpa-trouble">

		    <div class="title_block">
			<div class="bpa-graphserver-large"></div>
			<div class="Lheadline"><?php _e('Your PHP Installation is Out Of Date',"foxfire")?>
			</div>
			<div class="Rheadline"><?php _e('Installed: ',"foxfire")?> <?php echo $versions->getPHPVersion() ?>
				<?php _e(' | Required: ',"foxfire") ?> <?php echo $versions->min_php_ver ?>
			</div>
		    </div>

		    <div class="text">

			<?php _e('FoxFire requires at least <b>PHP', "foxfire") ?>
			<?php echo $versions->min_php_ver ?>
			<?php _e('</b>. Activating FoxFire on older versions of
			    PHP can white-screen the admin page and cause errors throughout your website.', "foxfire") ?>

			<?php _e('Also, the current version of WordPress
			    <a href=http://wordpress.org/about/requirements/>requires PHP 5.2.4</a> or higher.
			    ...so contact your web host and tell them its time to upgrade. You can get the latest
			    distro from the official PHP <a href="http://www.php.net/downloads.php">download page</a>.',"foxfire") ?>
		    </div>

		</div>
	    </div>

	<?php } ?>


	<?php if( !$versions->sqlOK() ) { ?>

	    <div class="bpa-trouble-wrap">
		<div class="bpa-trouble">

		    <div class="title_block">
			<div class="bpa-dbserver-large"></div>
			<div class="Lheadline"><?php _e('Your Database Server is Out Of Date',"foxfire")?>
			</div>
			<div class="Rheadline"><?php _e('Installed: ',"foxfire")?> <?php echo $versions->getSQLVersion() ?>
				<?php _e(' | Required: ',"foxfire") ?> <?php echo $versions->min_sql_ver ?>
			</div>
		    </div>

		    <div class="text">

			<?php _e('FoxFire requires at least <b>MySQL', "foxfire") ?>
			<?php echo $versions->min_sql_ver ?>
			<?php _e('</b>. Activating FoxFire on older versions
			    of MySQL could cause data corruption and errors
			    throughout your website.', "foxfire") ?>

			<?php _e('Also, the current version of WordPress
			    <a href=http://wordpress.org/about/requirements/>requires MySQL 5.0</a> or higher.
			    ...so contact your web host and tell them its time to upgrade. You can get the latest distro
			    from the official MySQL <a href="http://www.mysql.com/downloads/mysql/">download page</a>.',"foxfire") ?>
		    </div>

		</div>
	    </div>
	<?php } ?>


	<?php if( !$versions->wpOK() ) { ?>

	    <div class="bpa-trouble-wrap">
		<div class="bpa-trouble">

		    <div class="title_block">
			<div class="bpa-wp-logo-large"></div>
			<div class="Lheadline"><?php _e('Your WordPress Installation is Out Of Date',"foxfire")?>
			</div>
			<div class="Rheadline"><?php _e('Installed: ',"foxfire")?> <?php echo $versions->getWPVersion() ?>
				<?php _e(' | Required: ',"foxfire") ?> <?php echo $versions->min_wp_ver ?>
			</div>
		    </div>

		    <div class="text">

			<?php _e('FoxFire requires at least <b>WP ', "foxfire") ?>
			<?php echo $versions->min_wp_ver ?>
			<?php _e('</b> installed on your web server. Activating FoxFire on older versions
			    of WordPress could cause completely unpredictable results.', "foxfire") ?>

			<?php _e('Upgrading to the latest WordPress version is fast and easy ...just use the',"foxfire")?>
			<?php echo '<a href="'. site_url() . '/wp-admin/update-core.php">' ?>
			<?php _e('built-in updater</a>. You can also manually upgrade an installation, using a ZIP archive
			    from their <a href="http://wordpress.org/download/">download page</a>.',"foxfire") ?>
		    </div>

		</div>
	    </div>
	<?php } ?>


	    <div class="bpa-trouble-wrap">
		<div class="bpa-trouble-outro">
		    <?php _e("<b>Need Help? ...there's almost always a solution on our <a href='http://code.google.com/p/buddypress-media'>Google Code</a>
			site, or the FoxFire <a href='http://buddypress.org/community/groups/bp-album/forum/'>support forum</a>!</b>","foxfire") ?>
		</div>
	    </div>



            </table>


	    <!-- End Trouble Tab -->
	<?php
	}

	/**
	 * Adds the tab's scripts to the page header
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueScripts() {

	}


	/**
	 * Adds the tab's styles to the page header
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function enqueueStyles() {

	}

	

} // End of class FOX_tab_home

?>