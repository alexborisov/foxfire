<?php

/**
 * BP-MEDIA DISK CACHE
 * Handles disk cache operations for media item thumbnails
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Cache
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class BPM_dCache {


	/**
	 * Fetches the filepath and URL info for a given cache level
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $cache_level | Media cache level
	 * @return string $path | File path for cache folder
	 * @return string $url | URL for cache folder
	 */

	public function getLevelPath($cache_level) {

		global $bpm;

		switch ($cache_level) {

			case 0: {
				if ($bp->bpa->options['cache_mode_L0'] == 'global') {
					$path = $bp->bpa->options['cache_path_L0Folder'];
					$url = $bp->bpa->options['cache_path_L0URI'];
				} else {
					$path = bp_media_upload_path() . $bp->bpa->options['cache_offset_L0Folder'];
					$url = bp_media_get_url_from_path( bp_media_upload_path() . $bp->bpa->options['cache_offset_L0URI']);
				} break;
			}

			case 1: {
				if ($bp->bpa->options['cache_mode_L1'] == 'global') {
					$path = $bp->bpa->options['cache_path_L1Folder'];
					$url = $bp->bpa->options['cache_path_L1URI'];
				} else {
					$path = bp_media_upload_path() . $bp->bpa->options['cache_offset_L1Folder'];
					$url = bp_media_get_url_from_path( bp_media_upload_path() . $bp->bpa->options['cache_offset_L1URI']);
				} break;
			}

			case 2: {
				if ($bp->bpa->options['cache_mode_L2'] == 'global') {
					$path = $bp->bpa->options['cache_path_L2Folder'];
					$url = $bp->bpa->options['cache_path_L2URI'];
				} else {
					$path = bp_media_upload_path() . $bp->bpa->options['cache_offset_L2Folder'];
					$url = bp_media_get_url_from_path( bp_media_upload_path() . $bp->bpa->options['cache_offset_L2URI']);
				} break;
			}

			case 3: {
				if ($bp->bpa->options['cache_mode_L3'] == 'global') {
					$path = $bp->bpa->options['cache_path_L3Folder'];
					$url = $bp->bpa->options['cache_path_L3URI'];
				} else {
					$path = bp_media_upload_path() . $bp->bpa->options['cache_offset_L3Folder'];
					$url = bp_media_get_url_from_path( bp_media_upload_path() . $bp->bpa->options['cache_offset_L3URI']);
				} break;
			}

			case 4: {
				if ($bp->bpa->options['cache_mode_L4'] == 'global') {
					$path = $bp->bpa->options['cache_path_L4Folder'];
					$url = $bp->bpa->options['cache_path_L4URI'];
				} else {
					$path = bp_media_upload_path() . $bp->bpa->options['cache_offset_L4Folder'];
					$url = bp_media_get_url_from_path( bp_media_upload_path() . $bp->bpa->options['cache_offset_L4URI']);
				} break;
			}

			default : {
				return null;
			}

		}

		return compact('path', 'url');

	}


	/**
	 * Generates a full set of media item parameters so an item's cache filename can be constructed. Uses a partial
	 * set of parameters, information stored in the $media_template / $albums_template global variables, and
	 * the plugin's database tables.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args [
	 *      @param int $media_id | GUID for media item.
	 *      @param int $org_w | Original width of media item in pixels
	 *      @param int $org_h | Original height of media item in pixels
	 *      @param string $org_path | Original file path of media item
	 *      @param string $alt | Used as alt attribute of preview image.
	 *      @param string $size | Name of a registered size.
	 *      @param string $format | Image file format of cached image
	 *      @param string $watermark | Name of registered watermark to apply to image
	 *      @param string $process | Process queue strategy for item
	 *      @param int $resize_w | Width in pixels to resize media item to.
	 *      @param int $resize_h | Height in pixels to resize media item to.
	 *      @param bool $resize_crop | Crop when aspect ratio is different, default false
	 *      @param bool $resize_upscale | Upscale when original is smaller, default false
	 *      @param int $resize_quality | JPG image quality [1-100], default 90
	 * ]
	 * @return array | processed $args
	 */

	public function fill_resize_args($args) {

		global $bpm, $media_template, $albums_template;


		$defaults = array(
				'resize_crop' => false,
				'resize_upscale' => false,
				'resize_quality' => 90,
				'alt' => null,
						'is_default_for' => false
		);

		$r = wp_parse_args($args,$defaults);

		// Determine the image processing and cache parameters for the size

		// CASE 1: We've been been passed a registered media size
		if( isset($r['size'])) {

			$size_info = bp_media_imageSize_getRegisteredSize($r['size']);

			if($size_info) {

				$r['resize_w'] = $size_info->x_pixels;
				$r['resize_h'] = $size_info->y_pixels;
				$r['format'] = $size_info->format;
				$r['resize_quality'] = $size_info->quality;
				$r['resize_crop'] = $size_info->crop;
				$r['resize_upscale'] = $size_info->scale;
				$r['watermark'] = $size_info->watermark;
				$r['cache_level'] = $size_info->cache;
				$r['process'] = $size_info->process;

				return $r;
			}
			else {
				// If the size name isn't in the database, quit
				return false;
			}

		}
		// CASE 2: We've been passed a pair of x/y resize parameters
		elseif ( isset( $r['resize_w'] ) && isset( $r['resize_h']) ) {

			$r['size'] = 'custom';
			$size_info = bp_media_imageSize_getRegisteredSize($r['size']);

			$r['cache_level'] = $size_info->cache;

			return $r;

		}
		// CASE 3: We've been passed neither, so quit.
		else {
			return false;
		}


	}


	/**
	 * Calculates the resize dimensions for a cache image
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $org_w original width
	 * @param int $org_h original height
	 * @param int $resize_w desidered width
	 * @param int $resize_h desidered height
	 * @param bool $crop if output dimensions ratio will be the same of resize dimensions(true) or original dimensions(false)
	 * @param bool $upscale if display dimension should be enlarged if original is smaller than desidered size, doesn't affect file dimensions
	 * @return array [(file_w, file_h, display_w, display_h) actual width/height the file should be cached and width/height to display the file with
	 */

	public function calculate_dimensions($org_w, $org_h, $resize_w, $resize_h, $crop, $upscale ) {

		// Args validation
		$known_dimensions = (bool)$org_w + (bool)$org_h + (bool)$resize_w + (bool)$resize_h;

		if( $known_dimensions < 3 ) {
			// Cannot calculate without at least three dimensions
			return array(0,0,0,0);
			
		}
		elseif( $known_dimensions < 4 ) {

			// If only 3 dimensions are provided, guess the missing one.
			// This is only a fallback/recovery, the only supported usage is with
			// all the 4 dimension filled with non empty values

			if ( !$org_w ){
				$org_w = round($org_h*$resize_w/$resize_h);
			}
			elseif ( !$org_h ){
				$org_h = round($org_w*$resize_h/$resize_w);
			}
			elseif ( !$resize_w ){
				$resize_w = round($resize_h*$org_w/$org_h);
			}
			elseif ( !$resize_h ){
				$resize_h = round($resize_w*$org_h/$org_w);
			}

		}

		// Calculate size for file stored on disk (file_w, file_h)
		// and for displaying it in the browser (display_w, display_h)
		// ====================================================================================

		if($crop && !$upscale){

				$file_w = $display_w = min($resize_w, $org_w);
				$file_h = $display_h = min($resize_h, $org_h);
				
		}
		else {

				$original_ratio = $org_w / $org_h;
				$resize_ratio = $resize_w / $resize_h;

				$ratio = $crop ? $resize_ratio : $original_ratio;


				if( ($original_ratio > $resize_ratio) XOR $crop ){

					$file_w = min($org_w, $resize_w);
					$file_h = round($file_w / $ratio);

					$display_w = $upscale ? $resize_w : $file_w;
					$display_h = round($display_w / $ratio);

				}
				else {

					$file_h = min($org_h, $resize_h);
					$file_w = round($file_h * $ratio);

					$display_h = $upscale ? $resize_h : $file_h;
					$display_w = round($display_h * $ratio);
				}

		}

		return apply_filters('bp_media_calculate_size', array((int)$file_w,(int)$file_h,(int)$display_w,(int)$display_h), $org_w, $org_h, $resize_w, $resize_h, $crop, $upscale);
	}


	/**
	 * Finds an image in the cache based on supplied info. If a cached version of the image does not exist, it creates one.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * 
	 * @param array $args [
	 *      @param int $media_id | GUID for media item. [Optional if this item is the active item in $media_template]
	 *      @param int $org_w | Original width of media item in pixels [Optional. If not given and not in $media_template, it will be queried from the db]
	 *      @param int $org_h | Original height of media item in pixels [Optional. If not given and not in $media_template, it will be queried from the db]
	 *      @param string $org_path | Original file path of media item [Optional. If not given and not in $media_template, it will be queried from the db]
	 *      @param string $alt | Used as alt attribute of preview image. If empty and not false, the title of the current item in media loop will be used.
	 *      @param string $size | Name of a registered size. If supplied, settings for the registered size will override all resize_* args
	 *      @param int $resize_w | Width in pixels to resize media item to.
	 *      @param int $resize_h | Height in pixels to resize media item to.
	 *      @param string $format | Image file format of cached image
	 *      @param string $watermark | Name of registered watermark to apply to image
	 *      @param string $process | Process queue strategy for item
	 *      @param bool $resize_crop | Crop when aspect ratio is different, default false
	 *      @param bool $resize_upscale | Upscale when original is smaller, default false
	 *      @param int $resize_quality | JPG image quality [1-100], default 90
	 * ]
	 * @return array | url, display_w, display_h, alt, path, file_w, file_h
	 */

	public function getImage($args) {

		global $bpm;

		if(empty($args['owner_id'])) {

			debug_print_backtrace();
			// var_dump(debug_backtrace());
			var_dump($args); die;
		}

		extract($args, EXTR_OVERWRITE);

		if ( $size == 'original' || ($org_w == $resize_w && $org_h == $resize_h) ) {

			$path = $org_path;
			$display_w = $file_w = $org_w;
			$display_h = $file_h = $org_w;
			$url = bp_media_get_url_from_path($path);

			return apply_filters('bp_media_find_or_generate_preview_image', compact('url','display_w','display_h','alt','path','file_w','file_h'), $args);
		}

		list($file_w, $file_h, $display_w, $display_h) = $this->calculate_dimensions( $org_w, $org_h, $resize_w, $resize_h, $resize_crop, $resize_upscale );

		if( ($org_w == $file_w) && ($org_h == $file_h) ){

			$path = $org_path;

		}
		else {
			$cache =  $this->getLevelPath($cache_level);

			$base = $cache['path'] .'/'. ( $is_default_for ? 'default' : $owner_id );
			list( $path, $glob_search ) = $this->get_cache_file_path( $base, $media_id, $size, $file_w, $file_h, $resize_quality, $org_path );

			// Search for previously generated files that have same width, height and quality, no matter what size was requested when they have been generated
			$equivalent_files = glob( $glob_search );

			if ( is_array($equivalent_files) && count($equivalent_files) ){

				$path = $equivalent_files[0];

				// If the "Touch Files" option is set, update the timestamp on the file (Required for Windows PC's and web hosts
				// with last file access update switched off for performance reasons

				if ($bp->bpa->options['cache_server_touchFiles']) {
					touch($path);
				}
			} 
			else {

				@wp_mkdir_p( $base );
				$cache_file = image_resize( $org_path, $file_w, $file_h, $resize_crop, null , $base, $resize_quality );
				rename($cache_file,$path);
			}
		}

		$url = bp_media_get_url_from_path($path);

		return apply_filters('bp_media_find_or_generate_preview_image', compact('url','display_w','display_h','alt','path','file_w','file_h'), $args);

	}


	public function get_cache_file_path( $dir, $media_id, $size, $file_w, $file_h, $resize_quality, $org_path, $is_default_for=false ) {

		$ext = pathinfo($org_path, PATHINFO_EXTENSION);
		$basename = basename($org_path, ".$ext");

		$resize_quality = ('gif' == $ext || 'png' == $ext) ? 0 : $resize_quality;

		if( $is_default_for ) {

			$name = sprintf('%s/%s_%s_w%d_h%d_q%03d.%s', $dir, $is_default_for, $size, $file_w, $file_h, $resize_quality, $ext);
			$glob_search = sprintf('%s/%s_*_w%d_h%d_q%03d.%s', $dir, $is_default_for, $file_w, $file_h, $resize_quality, $ext);

		} else {

			// Note: as each media could have more than one original file ( e.g. video and thumb ) is better only prefixing info so it's always possible to understand which file in org dir is this cache of
			$name = sprintf('%s/%010d_%s_w%d_h%d_q%03d_%s.%s', $dir, $media_id, $size, $file_w, $file_h, $resize_quality, $basename, $ext);
			$glob_search = sprintf('%s/%010d_*_w%d_h%d_q%03d_%s.%s', $dir, $media_id, $file_w, $file_h, $resize_quality, $basename, $ext);

		}

		return array( $name, $glob_search );
	}



	/**
	 * Deletes a single media item from the cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * 
	 * @param int $owner_id | The numeric ID of the media item's owner
	 * @param int $media_id | The numeric ID of the media item
	 * @return int | Number of files removed from cache (multiple image sizes possible for a single media item)
	 */

	public function deleteSingleImage($owner_id, $media_id) {

		global $bpm;

		$file_count = 0;

		for( $level = 0;  $this->getLevelPath($level); $level++ ) {

			$result = $this->getLevelPath($level);

			// Safety measure
			if( empty($result['path'])){
				die;
			}

			foreach( glob( $result['path'] . "/" . $owner_id . "/" . sprintf('%010d_',$media_id) . "*.*") as $filename) {

				@unlink($filename);
				$file_count++;
			}
		}
 
		return $file_count;
	}


	/**
	 * Deletes all cache files for a registered media size
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $size | Name of registered media size
	 * @return int | Number of files removed from cache
	 */

	public function deleteImageSize($size) {

		global $bpm;

		$file_count = 0;

		for($level = 0;  $this>getLevelPath($level); $level++) {

			$result = $this->getLevelPath($level);

			// Safety measure
			if( empty($result['path'])) {
				die;
			}

			foreach( glob( $result['path'] . "/*") as $owner_dir ) {

				if( is_dir($owner_dir) ) {

					foreach( glob( $owner_dir . "/*_$size_*.*") as $filename ) {

						@unlink($filename);
						$file_count++;
					}
				}
			}
		}

		return $file_count;
	}



	/**
	 * Deletes all cache files for a single user
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $user_id | Numeric ID of user
	 * @return int | Number of files removed from cache
	 */

	public function deleteUser($user_id) {

		$file_count = 0;

		for($level = 0;  $this->getLevelPath($level); $level++) {

			$result = $this->getLevelPath($level);

			// Safety measure
			if( empty($result['path']))
				die;

			foreach (glob( $result['path'] . "/" . $user_id . "/*") as $filename) {

				@unlink($filename);
				$file_count++;
			}

			@rmdir($result['path'] . "/" . $user_id);

		}

		return $file_count;
	}



	/**
	 * Reports the storage space currently in use by a specific cache level
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @param int $level | Numeric ID of cache level
	 * @return int $inode_count| Number of inodes currently used by cache level
	 * @return float $storage_count | Number of bytes currently used by cache level
	 */

	public function checkUsage($level=3) {

		$inode_count = 0;
		$storage_count = 0;

		$path = $this->getLevelPath($level);

		if( file_exists($path['path']) && is_dir($path['path']) ){

			foreach (glob( $path['path'] . "/*") as $owner_dir) {

				if(is_dir($owner_dir)) {

					foreach (glob( $owner_dir . "/*") as $filename) {
					    $inode_count++;
					    $storage_count = $storage_count + filesize($filename);
					}
				}

			}
		}

		$result = array();

		$result["inode_count"] = $inode_count;
		$result["storage_count"] = $storage_count;

		return $result;
	}



	/**
	 * Removes files and inodes from a specific cache level to meet the specified inode and disk
	 * space usage limits
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $level | Numeric ID of cache level
	 * @param int $max_inodes | Number of inodes to purge cache to
	 * @param float $max_space | Number of bytes to purge cache level to
	 * @return int $inodes_deleted| Number of inodes deleted by function
	 * @return float $storage_deleted | Number of bytes deleted by function
	 */

	public function purge($level, $max_inodes, $max_space){

		$inode_count = 0;
		$storage_count = 0;

		// This comparison function determines how to sort the file node array
		function node_compare($a, $b) {

			if ($a['timestamp'] == $b['timestamp']) {
				return 0;
			}
			return ($a['timestamp'] > $b['timestamp']) ? -1 : 1;
		}


		$nodes = array();
		$result = $this->getLevelPath($level);

		// We iterate through all of the user directories inside the cache directory,
		// building an array of file info parameters

		foreach( glob( $result['path'] . "/*") as $owner_dir ) {

			if( is_dir($owner_dir )) {

				foreach( glob( $owner_dir . "/*") as $filename ) {

					$file_size = filesize($filename);

					$nodes[] = array (
						'timestamp' => fileatime($filename),    // Timestamp for the file
						'path' => $filename,                    // Full path to the file
						'size' => $file_size                    // Size of file in bytes
					);

					// As we build the array, we keep a running total of the number of files
					// and their space usage in bytes

					$storage_count = $storage_count + $file_size;
					$inode_count++;
				}
			}
		}


		// If the total amount of storage space, and/or the total number of files exceeds the
		// limit passed to the function, we need to purge the cache

		if( (($inode_count > $max_inodes) && ($max_inodes != 0)) || (($storage_count > $max_space) && ($max_space != 0)) ) {

			$inode_count = 0;
			$storage_count = 0;

			// We sort the file array from newest to oldest files, using our
			// custom sort function

			usort($nodes, "node_compare");

			// Then we iterate through the sorted array, starting at the newest file, and keep a
			// running total of the number of the number of files and their total space usage

			// TODO: less iterations if sorted from oldest and deleting until $storage_deleted >= ( $storage_count - $max_space ), same for inode

			foreach ($nodes as $node) {

				$storage_count = $storage_count + $node['size'];
				$inode_count++;

				// Once our total reaches either the maximum number of files or the maximum allowed storage space,
				// we delete every file in the array after that point

				if( (($inode_count > $max_inodes) && ($max_inodes != 0)) || (($storage_count > $max_space) && ($max_space != 0)) ){

					//@unlink($filename);
					$storage_deleted = $storage_deleted + $node['size'];
					$inodes_deleted++;
					//echo "\n KILL:" . $node['timestamp'] . " : " . $node['path'] . "\n";
				}
				else {
				    //echo "\n SAVE:" . $node['timestamp'] . " : " . $node['path'] . "\n";
				}
			}
		}

		return compact($inodes_deleted, $storage_deleted);
	}

} // End of class BPM_dCache

?>