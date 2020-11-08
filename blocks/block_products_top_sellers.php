<?php

	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$default_title = "{TOP_SELLERS_TITLE}";

	$category_id = get_param("category_id");
	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$discount_type = get_session("session_discount_type");
	$discount_amount = get_session("session_discount_amount");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$bestsellers_records = get_setting_value($vars, "bestsellers_records", 10);
	$bestsellers_cols = get_setting_value($vars, "cols", 1);
	$bestsellers_days    = get_setting_value($vars, "bestsellers_days",    7);
	$bestsellers_status  = get_setting_value($vars, "bestsellers_status",  "");
	$bestsellers_image = get_setting_value($vars, "bestsellers_image",  0);
	$bestsellers_desc = get_setting_value($vars, "bestsellers_desc", 0);
	$categories_range = get_setting_value($vars, "categories_range");
	$display_products = get_setting_value($settings, "display_products", 0);
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	product_image_fields($bestsellers_image, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);
	$user_id = get_session("session_user_id");
	$price_type = get_session("session_price_type");
	// check buttons to show
	$bn_add = get_setting_value($vars, "bn_add", 1);
	$bn_view = get_setting_value($vars, "bn_view", 0);
	$bn_goto = get_setting_value($vars, "bn_goto", 0);
	$bn_wish = get_setting_value($vars, "bn_wish", 0);
	$bn_more = get_setting_value($vars, "bn_more", 0);

	$desc_field = "";
	if ($bestsellers_desc == 1) {
		$desc_field = "short";
	} elseif ($bestsellers_desc == 2) {
		$desc_field = "full";
	} elseif ($bestsellers_desc == 3) {
		$desc_field = "high";
	} elseif ($bestsellers_desc == 4) {
		$desc_field = "spec";
	}

	$bestsellers_time = mktime(0,0,0, date("m"), date("d") - intval($bestsellers_days), date("Y"));
	$order_placed_date = va_time($bestsellers_time);

	$html_template = get_setting_value($block, "html_template", "block_products.html"); 
  $t->set_file("block_body", $html_template);

	$sql_params = array();
	$select  = " i.item_id, SUM(oi.quantity) AS item_id_counts, i.item_type_id, i.item_name, i.a_title, i.friendly_url,";
	$select .= " i.trade_price, i.trade_sales, i.trade_properties_price, i.price, i.sales_price, i.properties_price, i.is_price_edit, ";
	$select .= " i.is_sales, i.buying_price, i.tax_id, i.tax_free, ";
	$select .= " i.tiny_image, i.tiny_image_alt, i.small_image, i.small_image_alt, i.big_image, i.big_image_alt, super_image, ";
	$select .= " i.short_description, i.full_description, i.highlights, i.special_offer, i.notes, ";
	$select .= " i.use_stock_level, i.stock_level, i.disable_out_of_stock, ";
	$select .= " i.issue_date, i.date_added, i.date_modified ";
	
	//$sql_params["select"] = $select;	
	$sql_params["select"] = "i.item_id, SUM(oi.quantity) AS item_id_counts";
	$sql_params["join"][] = " INNER JOIN " . $table_prefix . "orders_items oi ON i.item_id=oi.item_id ";
	$sql_params["join"][] = " INNER JOIN " . $table_prefix . "orders o ON oi.order_id=o.order_id  ";
	$sql_params["where"][] = " o.order_placed_date >=" . $db->tosql($order_placed_date, DATETIME);
	if ($category_id && ($categories_range == "active" || $categories_range == "subs")) {
		$sql_params["join"][] = " INNER JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id ";
		if ($categories_range == "subs") {
			$category_data = VA_Categories::get_category_data($category_id);
			$search_path = get_setting_value($category_data, "category_path").$category_id.",";
			$sql_params["join"][] = " INNER JOIN " . $table_prefix . "categories c ON ic.category_id=c.category_id ";
			$category_where  = " (c.category_id=" . $db->tosql($category_id, INTEGER);
			$category_where .= " OR c.category_path LIKE '" . $db->tosql($search_path, TEXT, false) . "%') ";
			$sql_params["where"][] = $category_where;
		} else {
			$sql_params["where"][] = " ic.category_id=" . $db->tosql($category_id, INTEGER);
		}
	}
	$sql_params["group"][]  = " i.item_id ";
	if ($db->DBType == "access") {
		$sql_params["order"] = " SUM(oi.quantity) DESC ";
	} else {
		$sql_params["order"] = " item_id_counts DESC ";
	}
	if ($bestsellers_status == "PAID") {
		$sql_params["join"][]  = " INNER JOIN " . $table_prefix . "order_statuses os ON oi.item_status=os.status_id ";
		$sql_params["where"][] = " os.paid_status=1 ";
	} elseif (strlen($bestsellers_status) && $bestsellers_status != "ANY") {
		$sql_params["where"][] = " o.order_status=" . $db->tosql($bestsellers_status, INTEGER);
	}	

	// override params:
	$params = array(
		"pb_id" => $pb_id,
		"sql" => $sql_params,
		"recs" => $bestsellers_records,
		"page_number" => 1,
		"pages" => 1,
		"cols" => $bestsellers_cols,
		"qty" => "no",
		"image" => $image_type_name,
		"desc" => $desc_field,
		"add" => $bn_add,
		"view" => $bn_view,
		"goto" => $bn_goto,
		"wish" => $bn_wish,
		"more" => $bn_more,
	);

	$products_number = VA_Products::show_products($params);

	if ($products_number) {
		$block_parsed = true;
	}
