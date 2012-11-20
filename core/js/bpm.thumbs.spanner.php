<?php

/**
 * BP-MEDIA LAYOUT ENGINE - "SPANNER"
 * The Spanner layout engine uses an optimum packing algorithm combined with image clipping to create
 * thumbnail galleries with 100% page coverage. It produces an effect that's similar to the algorithm
 * used on Google+ albums, but preserves the order of images, is much more configurable (at the cost
 * of more image clipping), and allows the display of arbitrary fields.
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage AJAX API
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */


class fox_thumbEngine_spanner {


	var $images = array();
	var $cache = array();
	var $meta = array();
	var $common = array();
	var $cover = array();
	var $primary = array();
	var $secondary = array();

	// ============================================================================================================ //

	/**
	 * Loads the configuration data into the layout engine. In normal use, this data will be pulled from
	 * internal browser variables (canvas width) or using AJAX calls
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function fox_thumbEngine_spanner(){

		$this->cfg = array(

			"min_aspect_ratio"=>0.5,
			"max_aspect_ratio"=>4.0,
			"margin"=>5,
			"canvas_width"=>980,
			"content_url"=>FOX_URL_CONTENT . "/images/fireworks/",
			"content_path"=>FOX_PATH_CONTENT . "/images/fireworks/"
		);

		$this->cache = array(

					array(	"name"=>"master",
						"max_x"=>5000,
						"max_y"=>5000,
						"flush_interval"=>3600,
						"path"=>FOX_URL_CONTENT . "/images/fireworks/X/"
					),
					array(	"name"=>"large",
						"max_x"=>600,
						"max_y"=>600,
						"flush_interval"=>3600,
						"path"=>FOX_URL_CONTENT . "/images/fireworks/A/"
					),
					array(	"name"=>"medium",
						"max_x"=>300,
						"max_y"=>300,
						"flush_interval"=>3600,
						"path"=>FOX_URL_CONTENT . "/images/fireworks/B/"
					),
					array(	"name"=>"small",
						"max_x"=>200,
						"max_y"=>200,
						"flush_interval"=>3600,
						"path"=>FOX_URL_CONTENT . "/images/fireworks/C/"
					),
					array(	"name"=>"micro",
						"max_x"=>100,
						"max_y"=>100,
						"flush_interval"=>3600,
						"path"=>FOX_URL_CONTENT . "/images/fireworks/D/"
					)
		);

		$this->meta = array(

			"views"=>	array(	"title"=>"Views",
						"icon"=> FOX_URL_CORE . "/templates/common_assets/icon_view.png"
					),
			"likes"=>	array(	"title"=>"Likes",
						"icon"=> FOX_URL_CORE . "/templates/common_assets/icon_like.png"
					),
			"comments"=>	array(	"title"=>"Comments",
						"icon"=> FOX_URL_CORE . "/templates/common_assets/icon_comment.png"
					),
			"downloads"=>	array(	"title"=>"Downloads",
						"icon"=> FOX_URL_CORE . "/templates/common_assets/icon_download.png"
					)
		);

		$this->cover = array(

			"min_x"=>250,
			"max_x"=>450,
			"min_y"=>250,
			"max_y"=>350,
			"meta"=>array(
					"block_width"=>50,
					"margin"=>50,
					"fields"=>array(
							array("name"=>"views", "precision"=>3),
							array("name"=>"likes", "precision"=>3),
							array("name"=>"comments", "precision"=>3),
							array("name"=>"downloads", "precision"=>3),
					)
			),
			"info"=>array(
					"char_width"=>15,
					"margin"=>20,
					"fields"=>array(
							array("name"=>"title", "prefix"=>"", "max_length"=>50),
							array("name"=>"album", "prefix"=>"in", "max_length"=>50),
							array("name"=>"author", "prefix"=>"by", "max_length"=>50)
					)
			)
		);

		$this->primary = array(

			"rows"=>2,
			"meta"=>array(
					"block_width"=>50,
					"margin"=>75,
					"fields"=>array(
							array("name"=>"views", "precision"=>3),
							array("name"=>"likes", "precision"=>3),
							array("name"=>"comments", "precision"=>3),
					)
			),
			"info"=>array(
					"char_width"=>15,
					"margin"=>5,
					"fields"=>array(
							array("name"=>"title", "prefix"=>"", "max_length"=>50),
							array("name"=>"album", "prefix"=>"in", "max_length"=>50),
							array("name"=>"author", "prefix"=>"by", "max_length"=>50)
					)
			)

		);

		$this->secondary = array(

			"row_y"=>150,
			"meta"=>array(
					"block_width"=>50,
					"margin"=>75,
					"fields"=>array(
							array("name"=>"views", "precision"=>3),
							array("name"=>"likes", "precision"=>3),
							array("name"=>"comments", "precision"=>3),
					)
			),
			"info"=>array(
					"char_width"=>15,
					"margin"=>5,
					"fields"=>array(
							array("name"=>"title", "prefix"=>"", "max_length"=>50),
							array("name"=>"album", "prefix"=>"in", "max_length"=>50),
							array("name"=>"author", "prefix"=>"by", "max_length"=>50)
					)
			)
		);

	}


	/**
	 * Renders the template
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	public function render(&$error=null){


		$this->images = self::parseXML($this->cfg["content_path"], $randomize=false);

		echo '<div class="top_block" style="width:' . ($this->cfg["canvas_width"] + 10) . 'px;">';

		    $cover_result = self::renderCover($this->cover);

		    $primary_args = array_merge($this->primary, $cover_result);
		    self::renderPrimary($primary_args);

		echo '</div>';

		echo '<div class="lower_block">';

		    self::renderSecondary($this->secondary);

		echo '</div>';

	}


	/**
	 * Parses the manifest.xml file for a set of images. This is only used during testing.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param bool $randomize | True to randomize order of images. False to use order set in manifest.xml file.
	 * @return array | Array of image data arrays.
	 */

	public function parseXML($content_path, $randomize=false){


		$cls = new FOX_xml();
		$parsed_xml =  $cls->parseFile($content_path . "manifest.xml", 1, 'tag');

		$temp = array();

		$order = explode(",", $parsed_xml["manifest"]["order"]);
		$parsed_xml = $parsed_xml["manifest"]["items"];

		if( array_key_exists("id", $parsed_xml["item"]) ){

			$parsed_xml = array( "0"=>$parsed_xml["item"]);
		}
		else {
			$parsed_xml = $parsed_xml["item"];
		}

		$total_images = count($parsed_xml) - 1;

		for( $idx = 0; $idx <= $total_images; $idx++ ){

			$image = array();
			$image["id"] = $parsed_xml[$idx]["file"];

			if( $parsed_xml[$idx]["meta"]["pixels_x"] > $parsed_xml[$idx]["meta"]["pixels_y"] ){

				$scale_factor = 600 / $parsed_xml[$idx]["meta"]["pixels_x"];

				$image["x"] = 600;
				$image["y"] = (int)round($parsed_xml[$idx]["meta"]["pixels_y"] * $scale_factor);
			}
			elseif( $parsed_xml[$idx]["meta"]["pixels_y"] > $parsed_xml[$idx]["meta"]["pixels_x"] ){

				$scale_factor = 600 / $parsed_xml[$idx]["meta"]["pixels_y"];

				$image["y"] = 600;
				$image["x"] = (int)round( $parsed_xml[$idx]["meta"]["pixels_x"] * $scale_factor);
			}
			else {
				$image["x"] = 600;
				$image["y"] = 600;
			}

			$image["info"]["author"] = $parsed_xml[$idx]["author"];
			$image["info"]["title"] = $parsed_xml[$idx]["title"];
			$image["info"]["album"] = $parsed_xml[$idx]["album"];

			$image["meta"]["views"] = mt_rand(0,1000000);
			$image["meta"]["likes"] = mt_rand(0,10000);
			$image["meta"]["comments"] = mt_rand(0,1000);
			$image["meta"]["downloads"] = mt_rand(0,100000);

			$temp[$parsed_xml[$idx]["id"]] = $image;

		}
		unset($block_idx);


		// Set the order of the images
		// ============================================================

		$result = array();

		if($randomize == true){

			$result = $temp;
			shuffle($result);
		}
		else {
			foreach($order as $key){

			    $result[] = $temp[$key];
			}
		}

		return $result;

	}


	/**
	 * Renders the "cover image" section of the template's top block
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args | Control args
	 */

	public function renderCover($args){


		$cover = array_shift($this->images);

		$cover_args = array(
				"min_x"=>$args["min_x"],
				"max_x"=>$args["max_x"],
				"min_y"=>$args["min_y"],
				"max_y"=>$args["max_y"],
				"img_x"=>$cover["x"],
				"img_y"=>$cover["y"]
		);

		$block = self::zoom($cover_args);

		$block["id"] = $cover["id"];
		$block["info"] = $cover["info"];
		$block["meta"] = $cover["meta"];

		?>

		    <div class="cover_image"
			 style="position:relative;
				height:<?php echo $block["mask_y"]; ?>px;
				width: <?php echo $block["mask_x"]; ?>px;
				margin: 0px <?php echo $this->cfg["margin"]; ?>px 0px 0px">

			    <img src="<?php echo self::getBestSize($block["mask_x"], $block["mask_y"]) . $block["id"]; ?>"
				 style=" position:relative; bottom:<?php echo $block["offset_y"]; ?>px; right: <?php echo $block["offset_x"]; ?>px;"
				 height="<?php echo $block["image_y"]; ?>px;" width="<?php echo $block["image_x"]; ?>px;"/>

			    <div class="thumb_stats_wrapper"
				 style="background:url(<?php echo FOX_URL_CORE . "/templates/common_assets/00_00_00_055.png";?>)">
				<div class="thumb_stats">

				    <?php

				    echo self::renderMeta($args["meta"], $block);

				    ?>

				</div>
			    </div>

			    <div class="thumb_info"
				 style="background:url(<?php echo FOX_URL_CORE . "/templates/common_assets/00_00_00_055.png";?>)"
				 >
				    <?php

				    echo self::renderInfo($args["info"], $block);

				    ?>
			    </div>

		    </div>

		<?php


		$result = array(

				"mask_x"=>$block["mask_x"],
				"mask_y"=>$block["mask_y"],
		);

		return $result;
	}


	/**
	 * Renders the "primary rows" section of the template's top block
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args | Control args
	 */

	public function renderPrimary($args){

		$primary_x = $this->cfg["canvas_width"] - ($args["mask_x"] + $this->cfg["margin"]) ;
		$primary_y = round( ($args["mask_y"] - (($args["rows"] - 1) * $this->cfg["margin"])) / $args["rows"]);

		// Catch any rounding error
		$delta = $args["mask_y"] - ( ($primary_y * $args["rows"]) + (($args["rows"] - 1) * $this->cfg["margin"]) );


		?>
		<div class="primary_rows">
		<?php

		$rows_left = $args["rows"];

		$first_item = true;

		while($rows_left){

			if($rows_left == 1){

				$row_y = $primary_y + $delta;
			}
			else {
				$row_y = $primary_y;
			}

			$row_args = array(
					"row_x"=>$primary_x,
					"row_y"=>$row_y,
					"margin"=>$this->cfg["margin"],
			);

			$row_data = self::packRow($row_args);

			// INDIVIDUAL ROW
			// ========================================================================

			if($first_item){

				$first_item = false;
			}
			else {
				?>
				<div class="shim"
				     style=" float:left;
					     height:<?php echo $this->cfg["margin"]; ?>px;
					     width:<?php echo $primary_x ?>px;"></div>
				<?php
			}

			?>
			<div class="primary_row">
			<?php

			    $first_item = true;

			    foreach( $row_data as $block){

					if($first_item){

						$first_item = false;
					}
					else {
						?>
						<div class="shim"
						     style=" float:left;
							     height:<?php echo $block["mask_y"]; ?>px;
							     width:<?php echo $this->cfg["margin"]; ?>px;"></div>
						<?php
					}

					?>

					<div class="item"
					     style=" height:<?php echo $block["mask_y"]; ?>px;
						     width:<?php echo $block["mask_x"]; ?>px;">

						<img src="<?php echo self::getBestSize($block["mask_x"], $block["mask_y"]) . $block["id"]; ?>"
						     height="<?php echo $block["img_y"]; ?>px"
						     width="<?php echo $block["img_x"]; ?>px"
						     style=" position:relative; bottom:<?php echo $block["offset_y"]; ?>px; right: <?php echo $block["offset_x"]; ?>px;"/>

						<div class="thumb_stats_wrapper"
						     style="background:url(<?php echo FOX_URL_CORE . "/templates/common_assets/00_00_00_055.png";?>)">
						    <div class="thumb_stats">

							<?php

							    echo self::renderMeta($args["meta"], $block);

							?>

						    </div>
						</div>

						<div class="thumb_info"
						     style="background:url(<?php echo FOX_URL_CORE . "/templates/common_assets/00_00_00_055.png";?>)"
						     >
							<?php

							echo self::renderInfo($args["info"], $block);

							?>
						</div>
					</div>
				    <?php

			    }
			    unset($block);

			?>
			</div>	<!-- primary_row -->
			<?php

			$rows_left--;

		}

		?>
		    </div>  <!-- primary_rows -->

		<?php

	}


	/**
	 * Renders the rows within the template's "lower block"
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args | Control args
	 */

	public function renderSecondary($args){


		while( count($this->images) ){

			$row_args = array(
					"row_x"=>$this->cfg["canvas_width"],
					"row_y"=>$args["row_y"],
					"margin"=>$this->cfg["margin"]
			);

			$row_data = self::packRow($row_args);

			// INDIVIDUAL ROW
			// ========================================================================

			?>
			<div class="shim"
			     style=" float:left;
				     height:<?php echo $this->cfg["margin"]; ?>px;
				     width:<?php echo $this->cfg["canvas_width"]; ?>px;"></div>

			<div class="secondary_row"
			     style="width:<?php echo $this->cfg["width"]; ?>px;">
			<?php

				$first_item = true;

				foreach( $row_data as $block){

					if($first_item){

						$first_item = false;
					}
					else {
						?>
						<div class="shim"
						     style=" float:left;
							     height:<?php echo $block["mask_y"]; ?>px;
							     width:<?php echo $this->cfg["margin"]; ?>px;"></div>
						<?php
					}

					?>

					<div class="item"
					     style=" height:<?php echo $block["mask_y"]; ?>px;
						     width:<?php echo $block["mask_x"]; ?>px;">

						<img src="<?php echo self::getBestSize($block["mask_x"], $block["mask_y"]) . $block["id"]; ?>"
						     height="<?php echo $block["img_y"]; ?>px"
						     width="<?php echo $block["img_x"]; ?>px"
						     style=" position:relative; bottom:<?php echo $block["offset_y"]; ?>px; right: <?php echo $block["offset_x"]; ?>px;"/>

						<div class="thumb_stats_wrapper"
						     style="background:url(<?php echo FOX_URL_CORE . "/templates/common_assets/00_00_00_055.png";?>)">
						    <div class="thumb_stats">

							<?php

							echo self::renderMeta($args["meta"], $block);

							?>

						    </div>
						</div>

						<div class="thumb_info"
						     style="background:url(<?php echo FOX_URL_CORE . "/templates/common_assets/00_00_00_055.png";?>)"
						     >
							<?php

							echo self::renderInfo($args["info"], $block);

							?>
						</div>

					</div>
				    <?php

				}
				unset($block);

			?>
			</div>  <!-- secondary_row -->
			<?php

		}

	}


	/**
	 * Renders the meta block within an media item
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args | Control args
	 * @param array $item | Item instance
	 */

	function renderMeta($args, $item){


		$meta_width = $args["margin"];

		$result = "";

		foreach( $args["fields"] as $data){

			if( array_key_exists($data["name"], $item["meta"]) && ($meta_width < $item["mask_x"]) ){

				$result .= '<img ';
				$result .= 'src="' . $this->meta[$data["name"]]["icon"] . '" ';
				$result .= 'alt="' . $this->meta[$data["name"]]["title"] . '"';
				$result .= 'title="' . $this->meta[$data["name"]]["title"] . '" ';
				$result .= 'style="position:relative" ';
				$result .= '/>';

				$result .= '<div class="text">';
				$result .= FOX_math::siFormat( $item["meta"][$data["name"]], $precision, true);
				$result .= '</div>';
			}

			$meta_width += $args["block_width"];
		}

		return $result;

	}


	/**
	 * Renders the info block within an media item
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args | Control args
	 * @param array $item | Item instance
	 */

	function renderInfo($args, $item){


		// STAGE 1: Intersect the $args['info'] array with the $item['info']
		// array. The order fields are listed in the $item['info'] array sets
		// the order the fields are rendered to the template
		// =========================================================

		$temp = array();

		foreach( $args["fields"] as $field ){

			if( array_key_exists( $field["name"], $item["info"] ) ){

				$val = array();

				$val["value"] =  $item["info"][$field["name"]];
				$val["title"] = $field["title"];
				$val["prefix"] = $field["prefix"];
				$val["max_length"] = $field["max_length"];

				$temp[] = $val;
			}

		}

		// Since the "info" block is *left-justified* we reverse the array so
		// that fields are truncated from the beginning of the text string

		$temp = array_reverse($temp);

		$info_width = $args["margin"];
		$field_offset = 0;
		$fields= array();


		// STAGE 2: Transpose the intersected fields to the $fields array
		// as long as 1) the field value is shorter than the max_length
		// parameter, and until 2) the combined length of the fields
		// exceeds the mask_x parameter
		// =========================================================

		while( $info_width < $item["mask_x"]  && $field_count < count($temp) ){

			if( !is_string($temp[$field_offset]["value"]) ){

				$field_characters = 0;
			}
			else {
				$field_characters = strlen( $temp[$field_offset]["value"] );
			}

			if( ($field_characters > $temp[$field_offset]["max_length"]) // Exceeds max length
			    || $field_characters == 0				     // Field is empty
			) {

				$field_offset++;
				$field_count++;
				continue;
			}
			else {
				$fields[] = $temp[$field_offset];

				$info_width += ($field_characters * $args["char_width"]);
				$field_offset++;
				$field_count++;
			}
		}

		// Reverse the $fields array so that the fields are in the correct order
		$fields = array_reverse($fields);


		// STAGE 3: render the $fields array to a text string
		// =========================================================

		$field_count = count($fields) - 1;
		$result = "";

		for( $key = 0; $key <= $field_count; $key++ ){

			$result .= '<div class="field">' . $fields[$key]["prefix"] . '</div>';
			$result .= '<div class="val">' . $fields[$key]["value"] . '</div>';
		}

		return $result;

	}


	/**
	 * Pulls one or more media items off the stack and loads them into a row
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args | Control args
	 */

	public function packRow($args){


		$max_aspect_ratio = $this->cfg["max_aspect_ratio"];
		$min_aspect_ratio = $this->cfg["min_aspect_ratio"];

		// PASS 1: Create the largest array of masks possible
		// without overflowing the row
		// =========================================================

		$blocks = array();
		$current_width = 0;

		while( count($this->images) > 0 ){

			$image_aspect_ratio = ($this->images[0]["x"] / $this->images[0]["y"]);

			// Handle excessively wide or narrow images

			if($image_aspect_ratio > $max_aspect_ratio){

				$mask_aspect_ratio = $max_aspect_ratio;
			}
			elseif( $image_aspect_ratio < $min_aspect_ratio){

				$mask_aspect_ratio = $min_aspect_ratio;
			}
			else {
				$mask_aspect_ratio = $image_aspect_ratio;
			}

			$block_width = round($args["row_y"] * $mask_aspect_ratio) + $args["margin"];

			if( ($current_width + $block_width) <= $args["row_x"] ){

				$block = array_shift($this->images);

				$block["img_x"] = $block["x"];
				$block["img_y"] = $block["y"];
				$block["mask_x"] = $block["x"] = round($args["row_y"] * $mask_aspect_ratio);
				$block["mask_y"] = $block["y"] = $args["row_y"];

				unset($block["x"], $block["y"]);

				$blocks[] = $block;

				$current_width += $block_width;
			}
			else {
				break;
			}

		}

		// PASS 2: Scale the masks so that the sum of the masks
		// and margins equals the exact width of the row
		// =========================================================

		$blocks_width = $current_width - ( (count($blocks) - 1) * $args["margin"]);
		$target_width = $args["row_x"] - ( (count($blocks) - 1) * $args["margin"]);
		$scale_factor = $target_width / $blocks_width;

		$total_blocks = count($blocks) - 1;
		$current_width = 0;

		for( $block_idx = 0; $block_idx <= $total_blocks; $block_idx++ ){

			$blocks[$block_idx]["mask_x"] = round($blocks[$block_idx]["mask_x"] * $scale_factor);
			$current_width += ($blocks[$block_idx]["mask_x"]);

			// Correct for any rounding error
			if( ($block_idx == $total_blocks) && ($current_width != $target_width) ){

				$delta = $target_width - $current_width;
				$blocks[0]["mask_x"] += $delta;
			}
		}
		unset($block_idx);

		// PASS 3: Zoom the images to the masks so that the images
		// are only clipped in one dimension. Calculate offsets.
		// =========================================================

		for( $block_idx = 0; $block_idx <= $total_blocks; $block_idx++ ){

			$zoom_args = array(
					    "max_x"=>$blocks[$block_idx]["mask_x"],
					    "max_y"=>$blocks[$block_idx]["mask_y"],
					    "min_x"=>$blocks[$block_idx]["mask_x"],
					    "min_y"=>$blocks[$block_idx]["mask_y"],
					    "img_x"=>$blocks[$block_idx]["img_x"],
					    "img_y"=>$blocks[$block_idx]["img_y"]
			);

			$zoom_result = self::zoom($zoom_args);

			$blocks[$block_idx]["img_x"] = $zoom_result["image_x"];
			$blocks[$block_idx]["img_y"] = $zoom_result["image_y"];
			$blocks[$block_idx]["offset_x"] = $zoom_result["offset_x"];
			$blocks[$block_idx]["offset_y"] = $zoom_result["offset_y"];

		}
		unset($block_idx);

		return $blocks;

	}


	/**
	 * Scales and/or crops an image to fit within the specified pixel mask
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args | Control args
	 */

	public function zoom($args){

		$aspect_ratio = $args["img_x"] / $args["img_y"];
		$image_x = $args["img_x"];
		$image_y = $args["img_y"];

		// If necessary, scale the image so it meets the min_x and min_y parameters
		// ========================================================================

		if( $image_x < $args["min_x"] ){

			$scale_factor = ($args["min_x"] / $image_x);
			$image_x = round($image_x * $scale_factor);
			$image_y = round($image_y * $scale_factor);
		}

		if( $image_y < $args["min_y"] ){

			$scale_factor = ($args["min_y"] / $image_y);
			$image_x = round($image_x * $scale_factor);
			$image_y = round($image_y * $scale_factor);
		}

		// If the image exceeds *both* the max_x and max_y parameters, scale the
		// image so that the dimension closest to its max value parameter meets
		// the max value parameter
		// ========================================================================

		if( ($image_x > $args["max_x"]) && ($image_y > $args["max_y"]) ){

			$ratio_x = $image_x / $args["max_x"];
			$ratio_y = $image_y / $args["max_y"];

			$scale_factor = min($ratio_x, $ratio_y);

			$image_x = round($image_x / $scale_factor);
			$image_y = round($image_y / $scale_factor);

		}

		// Calculate the mask dimensions
		// ========================================================================

		if( $image_x > $args["max_x"] ){

			$mask_x = $args["max_x"];
		}
		else {
			$mask_x = $image_x;
		}

		if( $image_y > $args["max_y"] ){

			$mask_y = $args["max_y"];
		}
		else {
			$mask_y = $image_y;
		}

		$result = array(
				"image_x"=>(int)$image_x,
				"image_y"=>(int)$image_y,
				"mask_x"=>(int)$mask_x,
				"mask_y"=>(int)$mask_y,
				"offset_x"=>(int)round( ($image_x - $mask_x) / 2),
				"offset_y"=>(int)round( ($image_y - $mask_y) / 2)
		);

		return $result;

	}


	/**
	 * Finds the path to the optimal cached image size given the mask parameters
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $mask_x | Width of mask in pixels
	 * @param int $mask_y | Height of mask in pixels
	 * @return string | Base URL to optimum cache image directory
	 */

	public function getBestSize($mask_x, $mask_y){

		$sizes = array();
		$best = null;

		foreach($this->cache as $size){

			// Skip cache sizes that are smaller than the image mask
			if( ($size["max_x"] < $mask_x) || ($size["max_x"] < $mask_x) ){

				continue;
			}
			else {

				// Find the size with the lowest amount of overshoot
				$overshoot = ($size["max_x"] - $mask_x) * ($size["max_y"] - $mask_y);

				if( ($best === null) || $overshoot < $best ){

					$best = $overshoot;
					$result = $size["path"];
				}
			}

		}

		return $result;

	}



} // End of class fox_thumbEngine_spanner

?>
