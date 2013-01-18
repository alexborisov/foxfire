<?php

/**
 * FOXFIRE SCREEN RENDERING CLASS "ADMIN-> DASHBOARD"
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

class FOX_admin_page_dashboard {


	public function __construct() {

	}


	/**
	 * Enqueues the selected tab's scripts in the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueScripts() {


		wp_enqueue_script( 'fox-jquery-ui-core');
		wp_enqueue_script( 'fox-jquery-ui-widget');
		wp_enqueue_script( 'fox-jquery-ui-mouse');
		wp_enqueue_script( 'fox-jquery-ui-position');
		wp_enqueue_script( 'fox-jquery-ui-tabs');
		wp_enqueue_script( 'fox-jquery-ui-slider');

	}


	/**
	 * Enqueues the selected tab's styles in the page header
	 *
	 * @version 1.0
	 * @since 1.0
	 */

	public function enqueueStyles() {


		wp_enqueue_style( 'fox-admin', FOX_URL_CORE .'/admin/css/fox.admin.css', false, '2.8.1', 'screen' );
		//wp_enqueue_style( 'bpa-tabs-h', FOX_URL_CORE .'/admin/css/bpa.tabs.h.css', false, '2.5.0', 'screen' );
		wp_admin_css( 'css/dashboard' );
		
	}

	
	function render(){

	    $this->meta_boxes();
	    
	    ?>

	    <div class="fox_dashboard_header">

		<div class="fox_dashboard_header_wrap">
		    <span class="fox_dashboard_version">
			<div class="bpa-admin-ver-title">Version: </div> <span class="bpa-admin-ver-text"><?php echo FOX_DISPLAY_VERSION ?></span><br/>
			<div class="bpa-admin-build-title">Build Date: </div> <span class="bpa-admin-build-text"><?php echo FOX_DISPLAY_DATE ?></span><br/>
			<div class="bpa-admin-build-title">Using PHP: </div> <span class="bpa-admin-build-text"><?php echo phpversion();  ?></span>
		    </span>
		</div>

		<div class="fox_dashboard_header_wrap">
		    <div class="fox_release_warning">
			<b><u>DO NOT INSTALL THIS SOFTWARE ON A LIVE WEBSITE</u></b> - Nightly builds are unfinished, untested releases automatically generated
			by our GitHub repository. They give the community a live view of our progress, like
			watching a construction site. For more info, see the <a href="http://code.google.com/p/buddypress-media/issues/list">Nightly Builds</a>
			wiki page.
		    </div>
		</div>

	    </div>


	    <div class="fox_dashboard_body">

	    <?php if (version_compare(PHP_VERSION, '5.0.0', '<')) ngg_check_for_PHP5(); ?>
		    <div id="dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder">
				    <div id="post-body">
					    <div id="dashboard-widgets-main-content">
						    <div class="postbox-container" style="width:49%;">
							    <?php do_meta_boxes('bpa_overview', 'left', ''); ?>
						    </div>
					    <div class="postbox-container" style="width:49%;">
							    <?php do_meta_boxes('bpa_overview', 'right', ''); ?>
						    </div>
					    </div>
				    </div>
			</div>
		    </div>
	    </div>

	    <div class="fox_footer"></div>

	    <script type="text/javascript">
		    //<![CDATA[
	    var ajaxWidgets, ajaxPopulateWidgets;

	    jQuery(document).ready( function($) {
		    // These widgets are sometimes populated via ajax
		    ajaxWidgets = [
			    'dashboard_primary',
			    'dashboard_plugins'
		    ];

		    ajaxPopulateWidgets = function(el) {
			    show = function(id, i) {
				    var p, e = $('#' + id + ' div.inside:visible').find('.widget-loading');
				    if ( e.length ) {
					    p = e.parent();
					    setTimeout( function(){
						    p.load('admin-ajax.php?action=bpa_dashboard&jax=' + id, '', function() {
							    p.hide().slideDown('normal', function(){
								    $(this).css('display', '');
								    if ( 'dashboard_plugins' == id && $.isFunction(tb_init) )
									    tb_init('#dashboard_plugins a.thickbox');
							    });
						    });
					    }, i * 500 );
				    }
			    }
			    if ( el ) {
				    el = el.toString();
				    if ( $.inArray(el, ajaxWidgets) != -1 )
					    show(el, 0);
			    } else {
				    $.each( ajaxWidgets, function(i) {
					    show(this, i);
				    });
			    }
		    };
		    ajaxPopulateWidgets();
	    } );

		    jQuery(document).ready( function($) {
			    // postboxes setup
			    postboxes.add_postbox_toggles('bpa-overview');
		    });
		    //]]>
	    </script>
	    <?php

	}


	/**
	 * Show the server settings in a dashboard widget
	 *
	 * @return void
	 */
	function bpa_overview_server() {

		?>
		<div id="dashboard_server_settings" class="dashboard-widget-holder wp_dashboard_empty">
			<div class="bpa-dashboard-widget">
			    <div>2011.06.12 17:23:31 - "fanquake" failed encode on way_too_big.avi</div>
			    <div>2011.06.12 17:23:39 - "foxly" reached maximum quota of 10,000MB</div>
			    <div>2011.06.12 17:29:01 - "jetfoo2" reached maximum posts count (100)</div>
			    <div>2011.06.12 17:35:17 - "2inov8" tripped BANDWIDTH LIMIT on second_iphone_prototype_found.flv [link] </div>
			</div>
		</div>
		<?php
	}


	/**
	 *  Show latest news from the FoxFire updates list on google code
	 *
	 * @return void
	 */
	function bpa_widget_overview_news() {

		echo '<p class="widget-loading hide-if-no-js">' . __( 'Loading&#8230;' ) . '</p><p class="describe hide-if-js">' . __('This widget requires JavaScript.') . '</p>';
	}


	function bpa_overview_news(){

		?>
		<div class="rss-widget">
		    <?php

		    $rss = @fetch_feed( 'https://api.twitter.com/1/statuses/user_timeline.rss?screen_name=bpm_dev' );


		    if ( is_object($rss) ) {

			if ( is_wp_error($rss) ) {
			    echo '<p>' . sprintf(__('Newsfeed could not be loaded.  Check the <a href="%s">front page</a> to check for updates.', "foxfire"), 'https://twitter.com/#!/bpm_dev') . '</p>';
				return;
			}

			echo '<ul>';

			foreach ( $rss->get_items(0, 5) as $item ) {

			    $link = $item->get_link();

			    while ( stristr($link, 'http') != $link )
				    $link = substr($link, 1);

			    $link = esc_url(strip_tags($link));
			    $title = esc_attr(strip_tags($item->get_title()));

			    if ( empty($title) )
				    $title = __('Untitled');

			    $desc = str_replace( array("\n", "\r"), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option('blog_charset') ) ) ) );
			    $desc = wp_html_excerpt( $desc, 180 );

			    // Append ellipsis. Change existing [...] to [&hellip;].
			    if ( '[...]' == substr( $desc, -5 ) )
				    $desc = substr( $desc, 0, -5 ) . '[&hellip;]';
			    elseif ( '[&hellip;]' != substr( $desc, -10 ) )
				    $desc .= ' [&hellip;]';

			    $desc = esc_html( $desc );

				    $date = $item->get_date();
			    $diff = '';

			    if ( $date ) {

				$diff = human_time_diff( strtotime($date, time()) );

				if ( $date_stamp = strtotime( $date ) )
					$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date_stamp ) . '</span>';
				else
					$date = '';
			    }

			    ?>
			      <li><a class="rsswidget" title="" href='<?php echo $link; ?>'><?php echo $title; ?></a>
				      <span class="rss-date"><?php echo $date; ?></span>
			      <div class="rssSummary"><strong><?php echo $diff; ?></strong> - <?php echo $desc; ?></div></li>
			    <?php
			}

			echo '</ul>';
		      }
		    ?>
		</div>
		<?php

	}


	/**
	 * Show a summary of the used images
	 *
	 * @return void
	 */

	function fox_database() {

		global $bp, $wpdb;

		?>

		<div class="bpa_admin_main_header"><?php _e('Database Load',"foxfire") ?></div>
		<div class="table table_content">
		    <table>

			<?php

			    $cls = new FOX_dbUtil();
			    $result= $cls->getServerStatus($data_group="dashboard");

			    foreach( $result as $key => $val) {

				?>

				<tr class="first">
				    <td class="bpa_admin_stats_a"><?php echo $key; ?></td>
				    <td class="bpa_admin_stats_b"><?php echo $val; ?></td>

				</tr>

				<?php
			    }
			    ?>


		    </table>
		</div>

		<?php
	}





	/**
	 * Load the meta boxes
	 *
	 */

	// add_meta_box($id, $title, $callback, $page, $context = 'advanced', $priority = 'default', $callback_args=null)
	// Note: $id sets the CSS class of the meta box, and $id must be unique across all calls. If multiple calls have
	// the same $id, wp will only render the first call.

	function meta_boxes() {

		
		add_meta_box('fox_database', __('Database Performance (real data)', "foxfire"), array( &$this, 'fox_database' ), 'bpa_overview', 'left', 'core');
		//add_meta_box('dashboard_primary_x', __('News Feed &nbsp<a href="https://twitter.com/#!/bpm_dev">(View All)</a>', "foxfire"), array( &$this, 'bpa_overview_news' ), 'bpa_overview', 'right', 'core');
		add_meta_box('dashboard_primary_y', __('System Log (simulated data)', "foxfire"), array( &$this, 'bpa_overview_server' ) , 'bpa_overview', 'left', 'core');
	}

} // End of class FOX_admin_page_dashboard

?>