<?php

	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$default_title = "{top_category_name} &nbsp; {TOP_RATED_TITLE}";

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$product_no_image = get_setting_value($settings, "product_no_image", "");
	$top_rated_image = get_setting_value($vars, "top_rated_image",  0);
	$top_rated_desc = get_setting_value($vars, "top_rated_desc", 0);
	$top_rates_recs = get_setting_value($vars, "top_rates_recs", 10);
	$max_recs = get_setting_value($vars, "max_recs", $top_rates_recs);

	$top_rated_cols = get_setting_value($vars, "cols", 1);

	// check buttons to show
	$bn_add = get_setting_value($vars, "bn_add", 1);
	$bn_view = get_setting_value($vars, "bn_view", 0);
	$bn_goto = get_setting_value($vars, "bn_goto", 0);
	$bn_wish = get_setting_value($vars, "bn_wish", 0);
	$bn_more = get_setting_value($vars, "bn_more", 0);

	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	product_image_fields($top_rated_image, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);

	$php_in_desc = 0; $desc_field = "";
	if ($top_rated_desc == 1) {
		$desc_field = "short";
	} elseif ($top_rated_desc == 2) {
		$desc_field = "full";
	} elseif ($top_rated_desc == 3) {
		$desc_field = "features";
	} elseif ($top_rated_desc == 4) {
		$desc_field = "spec";
	}

	$html_template = get_setting_value($block, "html_template", "block_products.html"); 
  $t->set_file("block_body", $html_template);

	// count records only if max records limit available otherwise show only one page
	if ($max_recs > $top_rates_recs) { 
		$count_no = false; // disable SQL COUNT
		$page_param = "p".$pb_id;
		$page_number = "";
		$pages = $nav_pages;
	} else {
		$count_no = true; // disable SQL COUNT
		$page_param = "";
		$page_number = 1;
		$pages = 1;
	}

	// prepare params for VA_Products class to show products
	$sql_params = array();
	$sql_params["where"][]  = "i.votes>=" . $db->tosql(get_setting_value($settings, "min_votes", 10), INTEGER);
	$sql_params["where"][]  = "i.rating>=" . $db->tosql(get_setting_value($settings, "min_rating", 1), FLOAT);
	$sql_params["order"][]  = "i.rating DESC, i.votes DESC ";

	$params = array(
		"pb_id" => $pb_id,
		"sql" => $sql_params,
		"count_no" => $count_no,
		"recs" => $top_rates_recs,
		"max_recs" => $max_recs,
		"page_param" => $page_param,
		"page_number" => $page_number,
		"pages" => $pages,
		"cols" => $top_rated_cols,
		"qty" => "no",
		"image" => $image_type_name,
		"desc" => $desc_field,
		"add" => $bn_add,
		"view" => $bn_view,
		"goto" => $bn_goto,
		"wish" => $bn_wish,
		"more" => $bn_more,
		"rating" => true, 
	);

	$products_shown = VA_Products::show_products($params);

	if ($products_shown) {
		$block_parsed = true;
	}
