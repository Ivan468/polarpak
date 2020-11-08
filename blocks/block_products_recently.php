<?php

	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$default_title = "{RECENTLY_VIEWED_TITLE}";

	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$discount_type = get_session("session_discount_type");
	$discount_amount = get_session("session_discount_amount");

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$recent_records = get_setting_value($vars, "products_recent_records", 5);
	$display_products = get_setting_value($settings, "display_products", 0);
	$product_no_image = get_setting_value($settings, "product_no_image", "");
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	$recent_image = get_setting_value($vars, "recent_image",  0);
	$recent_desc = get_setting_value($vars, "recent_desc", 1);
	$user_id = get_session("session_user_id");
	// check buttons to show
	$bn_add = get_setting_value($vars, "bn_add", 1);
	$bn_view = get_setting_value($vars, "bn_view", 0);
	$bn_goto = get_setting_value($vars, "bn_goto", 0);
	$bn_wish = get_setting_value($vars, "bn_wish", 0);
	$bn_more = get_setting_value($vars, "bn_more", 0);

	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	product_image_fields($recent_image, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);

	$desc_field = "";
	if ($recent_desc == 1) {
		$desc_field = "short";
	} elseif ($recent_desc == 2) {
		$desc_field = "full";
	} elseif ($recent_desc == 3) {
		$desc_field = "high";
	} elseif ($recent_desc == 4) {
		$desc_field = "spec";
	}
	
	$html_template = get_setting_value($block, "html_template", "block_products.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("top_category_name",     PRODUCTS_TITLE);

	$products_shown = false;
	$recent_columns = get_setting_value($vars, "products_recent_cols", 1);
	$recently_viewed = get_session("session_recently_viewed");
	if ($recently_viewed) {
		$params = array(
			"pb_id" => $pb_id,
			"ids" => $recently_viewed,
			"recs" => $recent_records,
			"cols" => $recent_columns,
			"qty" => "no",
			"image" => $image_type_name,
			"desc" => $desc_field,
			"add" => $bn_add,
			"view" => $bn_view,
			"goto" => $bn_goto,
			"wish" => $bn_wish,
			"more" => $bn_more,
		);
		$products_shown = VA_Products::show_products($params);
	}

	if ($products_shown) {
		$block_parsed = true;
	}
