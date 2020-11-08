<?php

	include_once("./includes/products_functions.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");

	// set necessary scripts
	set_script_tag("js/shopping.js");
	set_script_tag("js/ajax.js");
	set_script_tag("js/blocks.js");
	set_script_tag("js/images.js");

	$default_title = "{PRODUCTS_RECOMMENDED_TITLE}";

	// global array to use in different blocks
	if(!isset($va_data)) { $va_data = array(); }
	if(!isset($va_data["products_index"])) { $va_data["products_index"] = 0; }
	$start_index = $va_data["products_index"] + 1;

	$current_ts = va_timestamp();

	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$user_id = get_session("session_user_id");
	$user_name = get_session("session_user_name");
	$discount_type = get_session("session_discount_type");
	$discount_amount = get_session("session_discount_amount");
	$display_products = get_setting_value($settings, "display_products", 0);
	$redirect_to_cart = get_setting_value($settings, "redirect_to_cart", ""); 
	if ($redirect_to_cart == "popup" && isset($is_frame_layout) && $is_frame_layout) {
		$redirect_to_cart = 3;
	}
	$price_type = get_session("session_price_type");
	$image_type_name = "small";

	// settings
	$quantity_control = get_setting_value($settings, "quantity_control_list", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$recs_per_page = get_setting_value($vars, "basket_prod_recom_recs", 10);
	$recs_columns = get_setting_value($vars, "basket_prod_recom_cols", 1);
	$product_no_image = get_setting_value($settings, "product_no_image", "");
	$image_type = get_setting_value($vars, "image_type",  2);
	$desc_type = get_setting_value($vars, "desc_type", 1);
	$user_id = get_session("session_user_id");
	// check buttons to show
	$bn_add = get_setting_value($vars, "bn_add", 1);
	$bn_view = get_setting_value($vars, "bn_view", 0);
	$bn_goto = get_setting_value($vars, "bn_goto", 0);
	$bn_wish = get_setting_value($vars, "bn_wish", 0);
	$bn_more = get_setting_value($vars, "bn_more", 0);

	product_image_fields($image_type, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);

	$desc_field = "";
	if ($desc_type == 1) {
		$desc_field = "short";
	} elseif ($desc_type == 2) {
		$desc_field = "full";
	} elseif ($desc_type == 3) {
		$desc_field = "high";
	} elseif ($desc_type == 4) {
		$desc_field = "spec";
	}

	// get ids
	$items_ids = "";
	$shopping_cart = get_session("shopping_cart");
	if (is_array($shopping_cart)) {
		foreach ($shopping_cart as $cart_id => $item) {
			$item_id = intval($item["ITEM_ID"]);
			if ($item_id) { 
				if (strlen($items_ids)) { $items_ids .= ",";	}
				$items_ids .= $item_id;
			}
		}
	}

	if (!strlen($items_ids)) {
		return;
	}
	
	$sql_params = array();
	$sql_params["join"][]   = " LEFT JOIN " . $table_prefix . "items_related ir ON i.item_id=ir.related_id ";
	$sql_params["where"][] = " ir.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";
	$sql_params["where"][] = " ir.related_id NOT IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";

	$recom_products_ids = VA_Products::find_all_ids($sql_params, VIEW_CATEGORIES_ITEMS_PERM);	
	
	$sql_params = array();
	$sql_params["join"][]   = " LEFT JOIN " . $table_prefix . "items_accessories ia ON i.item_id=ia.accessory_id ";	
	$sql_params["where"][]  = " ia.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";
	$sql_params["where"][]  = " ia.accessory_id NOT IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")";
		
	$recom_accessories_ids = VA_Products::find_all_ids($sql_params, VIEW_CATEGORIES_ITEMS_PERM);	
	
	$recom_ids = array_merge($recom_products_ids, $recom_accessories_ids);
	if (!$recom_ids) return;
	array_unique($recom_ids);

	// prepare params for VA_Products class to show products
	$sql_params = array();
	$sql_params["where"][]  = " i.item_id IN (" . $db->tosql($recom_ids, INTEGERS_LIST) . ")";

	$html_template = get_setting_value($block, "html_template", "block_products.html"); 
  $t->set_file("block_body", $html_template);
	$recommended_title = str_replace("{user_name}", $user_name, PRODUCTS_RECOMMENDED_TITLE);
	$t->set_var("PRODUCTS_RECOMMENDED_TITLE", $recommended_title);

	$params = array(
		"pb_id" => $pb_id,
		"sql" => $sql_params,
		"recs" => $recs_per_page,
		"cols" => $recs_columns,
		"qty" => $quantity_control,
		"image" => $image_type_name,
		"desc" => $desc_field,
		"add" => $bn_add,
		"view" => $bn_view,
		"goto" => $bn_goto,
		"wish" => $bn_wish,
		"more" => $bn_more,
	);

	$products_shown = VA_Products::show_products($params);

	if ($products_shown) {
		$block_parsed = true;
	}
