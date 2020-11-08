<?php

	include_once("./includes/shopping_cart.php");
	include_once("./includes/products_functions.php");

	$default_title = "{LATEST_TITLE}";

	$user_info = get_session("session_user_info");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$records_per_page = get_setting_value($vars, "products_latest_recs", 10);
	$latest_columns = get_setting_value($vars, "products_latest_cols", 1);
	$max_recs = get_setting_value($vars, "max_recs", "");
	$nav_pages = get_setting_value($vars, "nav_pages", "10");

	$display_products = get_setting_value($settings, "display_products", 0);
	$prod_latest_image = get_setting_value($vars, "prod_latest_image",  0);
	$prod_latest_desc = get_setting_value($vars, "prod_latest_desc", 0);
	$period_days = get_setting_value($vars, "period_days");

	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	product_image_fields($prod_latest_image, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);
	// check buttons to show
	$bn_add = get_setting_value($vars, "bn_add", 1);
	$bn_view = get_setting_value($vars, "bn_view", 0);
	$bn_goto = get_setting_value($vars, "bn_goto", 0);
	$bn_wish = get_setting_value($vars, "bn_wish", 0);
	$bn_more = get_setting_value($vars, "bn_more", 0);


	$html_template = get_setting_value($block, "html_template", "block_products.html"); 
  $t->set_file("block_body", $html_template);

	$user_id = get_session("session_user_id");

	$php_in_desc = 0; $desc_field = "";
	if ($prod_latest_desc == 1) {
		$desc_field = "short";
	} elseif ($prod_latest_desc == 2) {
		$desc_field = "full";
	} elseif ($prod_latest_desc == 3) {
		$desc_field = "features";
	} elseif ($prod_latest_desc == 4) {
		$desc_field = "spec";
	}

	$current_time = va_time();
	$period_time = 0;
	if ($period_days) {
		$period_time = mktime (0, 0, 0, $current_time[MONTH], $current_time[DAY] - $period_days, $current_time[YEAR]);
	}

	$sql_where = "";
	$order = get_setting_value($vars, "prod_latest_order", 0);
	switch ($order) {
		case 2:
			if ($period_time) { $sql_where = "i.date_modified>=".$db->tosql($period_time, DATETIME); }
			$order_field = "i.date_modified";
		break;		
		case 1:
			if ($period_time) { $sql_where = "i.date_added>=".$db->tosql($period_time, DATETIME); }
			$order_field = "i.date_added";
		break;
		case 0: default:
			$sql_where  = " i.issue_date IS NOT NULL AND i.issue_date<=" . $db->tosql(va_time(), DATETIME);
			if ($period_time) { $sql_where .= " AND i.issue_date>=".$db->tosql($period_time, DATETIME); }
			$order_field = "i.issue_date";
		break;
	
	}
	$sql_order = 	$order_field." DESC, i.item_id";

	// count records only if max records limit available otherwise show only one page
	if ($max_recs) { 
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
	$sql_params["where"][]  = $sql_where;
	$sql_params["order"][]  = $sql_order;

	// override params:
	$params = array(
		"pb_id" => $pb_id,
		"sql" => $sql_params,
		"count_no" => $count_no,
		"recs" => $records_per_page,
		"max_recs" => $max_recs,
		"page_param" => $page_param,
		"page_number" => $page_number,
		"pages" => $pages,
		"cols" => $latest_columns,
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
