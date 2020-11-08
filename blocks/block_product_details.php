<?php                           
	// save block vars as bar_vars to use vars for sub blocks
	$details_vars = $vars;
	$details_cms_css_class = $cms_css_class;

	include_once("./messages/".$language_code."/reviews_messages.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/previews_functions.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");

	// check ajax call for sub menu block
	$ajax = get_param("ajax");
	$pb_type = get_param("pb_type");
	if ($ajax && $pb_type) {
		$ajax_data = array();
		$ajax_data["pb_id"] = $pb_id;	
		$ajax_data["pb_type"] = $pb_type;
		if ($pb_type == "product_reviews" || $pb_type == "reviews") {
			$script_run_mode = "include";
			$vars = array("block_type" => "sub_block", "block_code" => "product_reviews");
			if (file_exists("./blocks_custom/block_reviews.php")) {
				include("./blocks_custom/block_reviews.php");
			} else {
				include("./blocks/block_reviews.php");
			}
			$script_run_mode = "";
			$ajax_data["html_id"] = "reviews_".$pb_id;	
			$ajax_data["block"] = $t->get_var("reviews_data");	
			echo json_encode($ajax_data);	
		} else if ($pb_type == "product_questions" || $pb_type == "questions") {
			$script_run_mode = "include";
			$vars = array("block_type" => "sub_block", "block_code" => "product_questions");
			if (file_exists("./blocks_custom/block_reviews.php")) {
				include("./blocks_custom/block_reviews.php");
			} else {
				include("./blocks/block_reviews.php");
			}
			$script_run_mode = "";
			$ajax_data["html_id"] = "questions_".$pb_id;	
			$ajax_data["block"] = $t->get_var("reviews_data");	
			echo json_encode($ajax_data);	
		}
		$layout_type = "no";
		$block_parsed = false; // don't need to parse block layout
		return;
	}

	// set necessary scripts
	set_script_tag("js/shopping.js");
	set_script_tag("js/ajax.js");
	set_script_tag("js/blocks.js");
	set_script_tag("js/images.js");

	$default_title = "{item_name}";
	$html_template = get_setting_value($block, "html_template", "block_product_details.html"); 
 	$t->set_file("block_body", $html_template);
	$t->set_var("sc_params", htmlspecialchars(json_encode($sc_params)));

	if(!isset($va_data)) { $va_data = array(); }
	if(!isset($va_data["products_index"])) { $va_data["products_index"] = 0; }
	$start_index = $va_data["products_index"] + 1;

	$category_data = get_setting_value($va_data, "product_category", ""); 
	$category_name = get_translation(get_setting_value($category_data, "category_name")); 
	$category_short_description = get_translation(get_setting_value($category_data, "short_description")); 
	$category_full_description = get_translation(get_setting_value($category_data, "full_description")); 
	$t->set_var("category_name", $category_name);
	$t->set_var("category_short_description", $category_short_description);
	$t->set_var("category_full_description", $category_full_description);

	$hide_add_limit = get_setting_value($settings, "hide_add_limit", ""); 
	$show_in_cart = get_setting_value($settings, "show_in_cart", ""); 
	$redirect_to_cart = get_setting_value($settings, "redirect_to_cart", ""); 
	$t->set_var("redirect_to_cart", $redirect_to_cart);
	$confirm_add = get_setting_value($settings, "confirm_add", 1);
	$t->set_var("confirm_add", $confirm_add);
	$t->set_var("multi_add", 0);
	
	$shopping_cart = get_session("shopping_cart");
	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$discount_type = get_session("session_discount_type");
	$discount_amount = get_session("session_discount_amount");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$quantity_control = get_setting_value($settings, "quantity_control_details", "");
	$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
	$points_system = get_setting_value($settings, "points_system", 0);
	$points_conversion_rate = get_setting_value($settings, "points_conversion_rate", 1);
	$points_decimals = get_setting_value($settings, "points_decimals", 0);
	$points_price_details = get_setting_value($settings, "points_price_details", 0);
	$reward_points_details = get_setting_value($settings, "reward_points_details", 0);
	$points_prices = get_setting_value($settings, "points_prices", 0);
	$price_matrix_details = get_setting_value($settings, "price_matrix_details", 0);
	$price_matrix_tab = get_setting_value($vars, "price_matrix_tab", 0);
	
	// credit settings
	$credit_system = get_setting_value($settings, "credit_system", 0);
	$reward_credits_users = get_setting_value($settings, "reward_credits_users", 0);
	$reward_credits_details = get_setting_value($settings, "reward_credits_details", 0);

	// new product settings	
	$new_product_enable = get_setting_value($settings, "new_product_enable", 0);	
	$new_product_order  = get_setting_value($settings, "new_product_order", 0);	
	
	$use_tabs = get_setting_value($vars, "use_tabs", 1);
	$use_tabs = 1;
	$details_manufacturer_image = get_setting_value($vars, "show_manufacturer_image", 0);
	$access_out_stock = get_setting_value($settings, "access_out_stock", 0);
	$display_products = get_setting_value($settings, "display_products", 0);
	$product_no_image_large = get_setting_value($settings, "product_no_image_large", "");
	$show_item_code = get_setting_value($settings, "item_code_details", 0);
	$show_manufacturer_code = get_setting_value($settings, "manufacturer_code_details", 0);
	$php_in_full_desc = get_setting_value($settings, "php_in_products_full_desc", 0);
	$php_in_hot_desc = get_setting_value($settings, "php_in_products_hot_desc", 0);
	$php_in_features = get_setting_value($settings, "php_in_products_features", 0);
	$php_in_notes = get_setting_value($settings, "php_in_products_notes", 0);

	$hide_weight_details = get_setting_value($settings, "hide_weight_details", 0);
	$shop_hide_add_details = get_setting_value($settings, "hide_add_details", 0);
	$shop_hide_view_details = get_setting_value($settings, "hide_view_details", 0);
	$shop_hide_checkout_details = get_setting_value($settings, "hide_checkout_details", 0);
	$shop_hide_wishlist_details = get_setting_value($settings, "hide_wishlist_details", 0);
	$shop_hide_shipping_details = get_setting_value($settings, "hide_shipping_details", 0);
	$shop_hide_free_shipping = get_setting_value($settings, "hide_free_shipping_details", 0);
	$weight_measure = get_setting_value($settings, "weight_measure", "");
	$stock_level_details = get_setting_value($settings, "stock_level_details", 0);

	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	$watermark_small_image = get_setting_value($settings, "watermark_small_image", 0);
	$watermark_big_image = get_setting_value($settings, "watermark_big_image", 0);
	$watermark_super_image = get_setting_value($settings, "watermark_super_image", 0);
	$open_large_image = get_setting_value($settings, "open_large_image", 0);

	// get products reviews settings
	$reviews_settings = get_settings("products_reviews");
	$reviews_allowed_view = get_setting_value($reviews_settings, "allowed_view", 0);
	$reviews_allowed_post = get_setting_value($reviews_settings, "allowed_post", 0);
	// get products reviews settings
	$product_questions = get_settings("product_questions");
	$questions_allowed_view = get_setting_value($product_questions, "allowed_view", 0);
	$questions_allowed_post = get_setting_value($product_questions, "allowed_post", 0);

	$product_params = prepare_product_params();

	$user_id = get_session("session_user_id");		
	$user_type_id = get_session("session_user_type_id");
	$price_type = get_session("session_price_type");
	if ($price_type == 1) {
		$price_field = "trade_price";
		$sales_field = "trade_sales";
		$properties_field = "trade_properties_price";
	} else {
		$price_field = "price";
		$sales_field = "sales_price";
		$properties_field = "properties_price";
	}

	$category_id = get_param("category_id");
	$item_id = get_param("item_id");
	if (!strlen($category_id) && strlen($item_id)) {		
		$category_id = VA_Products::get_category_id($item_id, VIEW_ITEMS_PERM);
	}

	// build query string
	$transfer_query = transfer_params("", true);
	$reviews_url = get_custom_friendly_url("reviews.php") . $transfer_query;
	$tell_friend_href = get_custom_friendly_url("tell_friend.php") . "?item_id=" . urlencode($item_id) . "&type=products";

	$t->set_var("products_href", get_custom_friendly_url("products_list.php"));
	$t->set_var("product_details_href", get_custom_friendly_url("product_details.php"));
	$t->set_var("basket_href",      get_custom_friendly_url("basket.php"));
	$t->set_var("checkout_href",    get_custom_friendly_url("checkout.php"));
	$t->set_var("reviews_url",      htmlspecialchars($reviews_url));
	$t->set_var("reviews_href",     htmlspecialchars($reviews_url));
	$t->set_var("tell_friend_href", htmlspecialchars($tell_friend_href));
	$t->set_var("product_print_href", get_custom_friendly_url("product_print.php"));
	$t->set_var("cl",               $currency["left"]);
	$t->set_var("cr",               $currency["right"]);
	$t->set_var("tax_prices_type",  $tax_prices_type);
	$t->set_var("PRODUCT_OUT_STOCK_MSG", htmlspecialchars(va_constant("PRODUCT_OUT_STOCK_MSG")));
	$t->set_var("out_stock_alert",       str_replace("'", "\\'", htmlspecialchars(va_constant("PRODUCT_OUT_STOCK_MSG"))));

	$t->set_var("checkout_button", "");
	$t->set_var("compare_button", "");

	// random value
	srand ((double) microtime() * 1000000);
	$random_value = rand();
	$current_ts = va_timestamp();

	// generate page link with query parameters
	$page = get_custom_friendly_url("product_details.php");
	$remove_parameters = array("rnd", "cart", "item_id");
	$query_string = get_query_string($_GET, $remove_parameters, "", false);
	$page	.= $query_string;
	$page .= strlen($query_string) ? "&" : "?";
	$cart_link  = $page;
	$page .= "item_id=" . urlencode($item_id);
	$cart_link .= "rnd=" . $random_value . "&";

	$t->set_var("rnd", $random_value);
	$t->set_var("rp_url", urlencode($page));
	$t->set_var("rp", htmlspecialchars($page));

	$t->set_var("current_category_id", htmlspecialchars($category_id));
	
	if (!VA_Products::check_exists($item_id, $access_out_stock)) {
		$t->set_var("item", "");
		$t->set_var("links_block", "");		
		$t->set_var("item_name", "&nbsp;");
		$t->set_var("NO_PRODUCT_MSG", va_constant("NO_PRODUCT_MSG"));
		$t->parse("no_item", false);		
		$block_parsed = true;
		return;
	}
	
	if (!VA_Products::check_permissions($item_id, VIEW_ITEMS_PERM, true, $access_out_stock)) {
		$site_url = get_setting_value($settings, "site_url", "");
		$secure_url = get_setting_value($settings, "secure_url", "");
		$secure_user_login = get_setting_value($settings, "secure_user_login", 0);
		if ($secure_user_login) {
			$user_login_url = $secure_url . get_custom_friendly_url("user_login.php");
		} else {
			$user_login_url = $site_url . get_custom_friendly_url("user_login.php");
		}
		$return_page = get_request_uri();
		header ("Location: " . $user_login_url . "?return_page=" . urlencode($return_page) . "&type_error=2&ssl=".intval($is_ssl));
		exit;
	}
	
	$sql  = " SELECT i.item_id, i.item_type_id, i.special_offer, i.item_code, i.item_name, i.a_title, i.friendly_url, ";
	$sql .= " i.highlights, i.full_desc_type, i.short_description, i.full_description, ";
	$sql .= " i.big_image, i.big_image_alt, i.small_image, i.small_image_alt, i.super_image, ";
	$sql .= " i.meta_title, i.meta_keywords, i.meta_description, ";
	$sql .= " i.buying_price, i." . $price_field . ", i.is_price_edit, i.".$properties_field.", i." . $sales_field . ", i.discount_percent, ";
	$sql .= " i.tax_id, i.tax_free, i.buy_link, i.is_sales, i.is_compared, ";
	$sql .= " i.total_views, i.votes, i.points,  i.allow_reviews, i.allow_questions, ";
	$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
	$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
	$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
	$sql .= " i.manufacturer_code, m.manufacturer_name, m.affiliate_code, m.image_large AS manufacturer_image_src, m.image_large_alt AS manufacturer_image_alt, m.image_small AS manufacturer_image_small_src, m.image_small_alt AS manufacturer_image_small_alt,";
	$sql .= " i.template_name, i.preview_url, i.preview_width, i.preview_height, ";
	$sql .= " i.hide_add_details, i.hide_view_details, i.hide_checkout_details, i.hide_wishlist_details, i.hide_shipping_details, ";
	$sql .= " i.issue_date, i.stock_level, i.is_shipping_free, i.notes, i.weight, con.condition_name, ";
	$sql .= " sr.shipping_rule_desc, st_in.shipping_time_desc AS in_stock_message, st_out.shipping_time_desc AS out_stock_message, ";
	$sql .= " i.use_stock_level, i.disable_out_of_stock, i.hide_out_of_stock, i.min_quantity, i.max_quantity, i.quantity_increment ";
	// new product db
	if ($new_product_enable) {
		switch ($new_product_order) {
			case 0:
				$sql .= ", i.issue_date AS new_product_date ";
			break;
			case 1:
				$sql .= ", i.date_added AS new_product_date ";
			break;
			case 2:
				$sql .= ", i.date_modified AS new_product_date ";
			break;
		}		
	}
	$sql .= " FROM ((((((";
	$sql .= $table_prefix . "items i ";
	$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "conditions con ON i.condition_id=con.condition_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_in ON i.shipping_in_stock=st_in.shipping_time_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "shipping_times st_out ON i.shipping_out_stock=st_out.shipping_time_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "shipping_rules sr ON i.shipping_rule_id=sr.shipping_rule_id) ";
	$sql .= " WHERE i.item_id = " . $db->tosql($item_id, INTEGER);

	$t->set_var("category_id", htmlspecialchars($category_id));	
	$db->query($sql);
	if ($db->next_record())
	{
		$item_number = 0;

		// set custom template for specific product
		$template_name = $db->f("template_name");
		if (strlen($template_name)) {
			if (@file_exists($settings["templates_dir"]."/".$template_name) || @file_exists("./templates/user/".$template_name)) {
				$t->set_file("block_body", $template_name);
			}
		}
		
		$item_number++;

		$item_id = $db->f("item_id");
		$item_type_id = $db->f("item_type_id");
		$item_code = $db->f("item_code");

		$item_name_initial = $db->f("item_name");
		$item_name = get_translation($item_name_initial);
		$product_params["item_name"] = strip_tags($item_name);
		$a_title = $db->f("a_title");
		$friendly_url = $db->f("friendly_url");
		$va_data["products_index"]++;
		$form_id = $va_data["products_index"];
		$product_params["form_id"] = $form_id;
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));
		$special_offer = get_translation($db->f("special_offer"));
		$full_desc_type = $db->f("full_desc_type");
		if ($full_desc_type != 1) {
			$full_description = nl2br(htmlspecialchars($full_description));
		}

		// get images
		$image_super = $db->f("super_image");
		$super_image = $db->f("super_image");

		$is_compared = $db->f("is_compared");
		$notes = get_translation($db->f("notes"));
		$issue_date_ts = 0;
		$issue_date = $db->f("issue_date", DATETIME);
		if (is_array($issue_date)) {
			$issue_date_ts = va_timestamp($issue_date);
		}

		$price = $db->f($price_field);
		$is_price_edit = $db->f("is_price_edit");
		$is_sales = $db->f("is_sales");
		$sales_price = $db->f($sales_field);
		$coupons_ids = ""; $coupons_discount = ""; $coupons_applied = array();
		get_sales_price($price, $is_sales, $sales_price, $item_id, $item_type_id, "", "", $coupons_ids, $coupons_discount, $coupons_applied);
		
		$discount_applicable = 1;
		$q_prices = get_quantity_price($item_id, 1);
		// calculate price
		if (sizeof($q_prices)) {
			$user_price  = $q_prices [0];
			$discount_applicable = $q_prices [2];
			if ($is_sales) {
				$sales_price = $user_price;
			} else {
				$price = $user_price;
			}
		}

		$properties_price = $db->f($properties_field);
		$buying_price = $db->f("buying_price");

		// points data
		$is_points_price = $db->f("is_points_price");
		$points_price = $db->f("points_price");
		$reward_type = $db->f("reward_type");
		$reward_amount = $db->f("reward_amount");
		$credit_reward_type = $db->f("credit_reward_type");
		$credit_reward_amount = $db->f("credit_reward_amount");
		if (!strlen($reward_type)) {
			$reward_type = $db->f("type_bonus_reward");
			$reward_amount = $db->f("type_bonus_amount");
		}
		if (!strlen($credit_reward_type)) {
			$credit_reward_type = $db->f("type_credit_reward");
			$credit_reward_amount = $db->f("type_credit_amount");
		}
		if (!strlen($is_points_price)) {
			$is_points_price = $points_prices;
		}

		$weight = $db->f("weight");
		$item_tax_id = $db->f("tax_id");
		$item_tax_free = $db->f("tax_free");
		$tax_free = ($item_tax_free || $user_tax_free);
		$manufacturer_code = $db->f("manufacturer_code");
		$manufacturer_name = get_translation($db->f("manufacturer_name"));
		// show manufactures image
		if ($details_manufacturer_image == 2){
			$manufacturer_image_src = $db->f("manufacturer_image_small_src");
			$manufacturer_image_alt = get_translation($db->f("manufacturer_image_small_alt"));
		} else if ($details_manufacturer_image == 3){
			$manufacturer_image_src = $db->f("manufacturer_image_src");
			$manufacturer_image_alt = get_translation($db->f("manufacturer_image_alt"));
		} else {
			$manufacturer_image_src = "";
			$manufacturer_image_alt = "";
		}
		$stock_level = $db->f("stock_level");
		$use_stock_level = $db->f("use_stock_level");
		$disable_out_of_stock = $db->f("disable_out_of_stock");
		$hide_out_of_stock = $db->f("hide_out_of_stock");
		$hide_add_details = $db->f("hide_add_details");
		$hide_view_details = $db->f("hide_view_details");
		$hide_checkout_details = $db->f("hide_checkout_details");
		$hide_wishlist_details = $db->f("hide_wishlist_details");
		$hide_shipping_details = $db->f("hide_shipping_details");
		$is_shipping_free = $db->f("is_shipping_free");
		$hide_free_shipping = $db->f("hide_free_shipping_details");

		$min_quantity = $db->f("min_quantity");
		$max_quantity = $db->f("max_quantity");
		$quantity_increment = $db->f("quantity_increment");
		$quantity_limit = ($use_stock_level && ($disable_out_of_stock || $hide_out_of_stock));
		$total_views = intval($db->f("total_views"));
		$allow_reviews = $db->f("allow_reviews");
		$allow_questions = $db->f("allow_questions");

		$condition_name = get_translation($db->f("condition_name"));
		$in_stock_message = get_translation($db->f("in_stock_message"));
		$out_stock_message = get_translation($db->f("out_stock_message"));
		$product_params["use_sl"] = $use_stock_level;
		$product_params["sl"] = $stock_level;
		$product_params["in_sm"] = $in_stock_message;
		$product_params["out_sm"] = $out_stock_message;
		$product_params["min_qty"] = $min_quantity;
		$product_params["max_qty"] = $max_quantity;

		// meta data
		$meta_title = get_translation($db->f("meta_title"));
		$meta_keywords = get_translation($db->f("meta_keywords"));
		$meta_description = get_translation($db->f("meta_description"));

		// preview data
		$preview_url = $db->f("preview_url");
		$preview_width = $db->f("preview_width");
		$preview_height = $db->f("preview_height");		
		
		if (!$full_description) { $full_description = $short_description; }

		$auto_meta_title = $meta_title;
		if (!strlen($auto_meta_title)) { $auto_meta_title = $item_name; }
		$auto_meta_description = $meta_description;
		if (!strlen($auto_meta_description)) {
			if (strlen($short_description)) {
				$auto_meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$auto_meta_description = $full_description;
			}
		}

		if ($discount_applicable) {
			if ($discount_type == 1 || $discount_type == 3) {
				$price -= round(($price * $discount_amount) / 100, 2);
				$sales_price -= round(($sales_price * $discount_amount) / 100, 2);
			} elseif ($discount_type == 2) {
				$price -= round($discount_amount, 2);
				$sales_price -= round($discount_amount, 2);
			} elseif ($discount_type == 4) {
				$price -= round((($price - $buying_price) * $discount_amount) / 100, 2);
				$sales_price -= round((($sales_price - $buying_price) * $discount_amount) / 100, 2);
			}
		}
		$item_price = calculate_price($price, $is_sales, $sales_price);

		$data = show_items_properties("products_".$pb_id, $form_id, $item_id, $item_type_id, $item_price, $item_tax_id, $tax_free, "details", $product_params, true, $price_matrix_details);
		$is_properties  = $data["params"]["is_any"];
		$properties_ids = $data["params"]["ids"];
		$selected_price = $data["params"]["price"];
		$components_price = $data["params"]["components_price"];
		$components_tax_price = $data["params"]["components_tax_price"];
		$components_points_price = $data["params"]["components_points_price"];
		$components_reward_points = $data["params"]["components_reward_points"];
		$components_reward_credits = $data["params"]["components_reward_credits"];
		$json_data = $data["json"];
		$json_data["currency"] = $currency;

		if ($new_product_enable) {
			$new_product_date = $db->f("new_product_date");			
			$is_new_product = is_new_product($new_product_date);
		} else {
			$is_new_product = false;
		}
		if ($is_new_product) {
			$t->set_var("product_new_class", " ico-new ");
		} else {
			$t->set_var("product_new_class", "");
		}
		
		$t->set_var("item_id", $item_id);
		$t->set_var("form_id", $form_id);
		$t->set_var("item_name", $item_name);
		$t->set_var("product_name", $item_name);
		$t->set_var("product_title", $item_name);
		$t->set_var("item_name_url", urlencode(strip_tags($item_name)));
		$t->set_var("product_name_url", urlencode(strip_tags($item_name)));
		$t->set_var("product_title_url", urlencode(strip_tags($item_name)));
		$t->set_var("item_name_strip", htmlspecialchars(strip_tags($item_name)));
		$t->set_var("manufacturer_code", htmlspecialchars($manufacturer_code));
		$t->set_var("manufacturer_name", htmlspecialchars($manufacturer_name));
		$t->set_var("manufacturer_image_src", htmlspecialchars($manufacturer_image_src));
		$t->set_var("manufacturer_image_alt", htmlspecialchars($manufacturer_image_alt));
		$t->set_var("total_views", $total_views);
		$t->set_var("tax_price", "");
		$t->set_var("tax_sales", "");

		// show item code
		if ($show_item_code && $item_code) {
			$t->set_var("item_code", htmlspecialchars($item_code));
			$t->sparse("item_code_block", false);
		} else {
			$t->set_var("item_code_block", "");
		}
		// show manufacturer code
		if ($show_manufacturer_code && $manufacturer_code) {
			$t->set_var("manufacturer_code", htmlspecialchars($manufacturer_code));
			$t->sparse("manufacturer_code_block", false);
		} else {
			$t->set_var("product_code", "");
		}

		// show manufacturer's image
		if (strlen($manufacturer_image_src)) {
			$t->sparse("manufacturer_image", false);
		} else {
			$t->set_var("manufacturer_image", "");
		}

		$t->set_var("item_added", "");
		$t->set_var("sc_errors", "");

		if ($item_id == $sc_item_id) {
			// show message about added products 
			if ($sc_errors) {
				$t->set_var("errors_list", $sc_errors);
				$t->parse("sc_errors", false);
			} 
			if ($sc_message) {
				$t->set_var("added_message", $sc_message);
				$t->parse("item_added", false);
			}
		}

		if ($condition_name) {
			$t->set_var("condition_name", $condition_name);
			$t->sparse("condition_block", false);
		} else {
			$t->set_var("condition_block", "");
		}

		$t->set_var("stock_level", $stock_level);
		if ($stock_level_details) {
			if ($use_stock_level) {
				$t->set_var("sl_style", "");
			} else {
				$t->set_var("sl_style", "display: none;");
			}
			$t->set_var("stock_level", $stock_level);
			$t->parse("stock_level_block", false);
		} else {
			$t->set_var("stock_level_block", "");
		}

		if (!$use_stock_level || $stock_level > 0) {
			$shipping_time_desc = $in_stock_message;
		} else {
			$shipping_time_desc = $out_stock_message;
		}
		if (strlen($shipping_time_desc)) {
			$t->set_var("shipping_time_desc", get_translation($shipping_time_desc));
			$t->set_var("sm_style", "");
		} else {
			$t->set_var("sm_style", "display: none;");
		}
		$t->parse("availability", false);

		if ($is_shipping_free && !$shop_hide_free_shipping && !$hide_free_shipping) {
			$t->sparse("shipping_free", false);
		} else {
			$t->set_var("shipping_free", "");
		}

		if (strlen($db->f("shipping_rule_desc")))
		{
			$t->set_var("shipping_rule_desc", get_translation($db->f("shipping_rule_desc")));
			$t->parse("shipping_block", false);
		}

		$highlights = get_translation($db->f("highlights"));
		// remove empty block from higlights added by HTML editor
		$highlights = preg_replace("/^\s*<br\/?>\s*$/si", "", $highlights);
		$highlights = preg_replace("/^\s*<div[^>]*>\s*<br\/?>\s*<\/div>\s*$/si", "", $highlights);
		if ($highlights) {
			$highlights = str_replace("{item_name}", $item_name, $highlights);
			$highlights = str_replace("{item_code}", $item_code, $highlights);
			$highlights = str_replace("{manufacturer_code}", $manufacturer_code, $highlights);
			$highlights = str_replace("{manufacturer_image_src}", $manufacturer_image_src, $highlights);
			$highlights = str_replace("{manufacturer_image_alt}", $manufacturer_image_alt, $highlights);
			$t->set_var("highlights", $highlights);
			$t->sparse("highlights_block", false);
		}
		//eval_php_code($special_offer);
		$t->set_var("special_offer", $special_offer);

		$details_supersize_image = get_setting_value($vars, "show_super_image", 0);
		$zoom_width = get_setting_value($vars, "zoom_width", 0);
		$zoom_height = get_setting_value($vars, "zoom_height", 0);
		$image_onclick = ""; $image_onmouseover = ""; $image_onmousemove = ""; 
		// prepare JS for super image even if there is no super image
		if ($details_supersize_image == 0){
			if ($open_large_image) {
				$image_onclick = "popupImage(this);";
			} else {
				$image_onclick = "openSuperImage(this);";
			}
		} else if ($details_supersize_image == 1){
			$image_onmouseover = "popupImageMouseOver(this);";
		} else if ($details_supersize_image == 2){
			$image_onmousemove = "activateZoom(event, this);";
		}
		$t->set_var("image_onclick", htmlspecialchars($image_onclick));
		$t->set_var("image_onmouseover", htmlspecialchars($image_onmouseover));
		$t->set_var("image_onmousemove", htmlspecialchars($image_onmousemove));
		$t->set_var("image_zoom_width", htmlspecialchars($zoom_width));
		$t->set_var("image_zoom_height", htmlspecialchars($zoom_height));

		// check and parse super image before large as super image link could be added to large image
		$src = ""; $width = ""; $height = "";
		if (strlen($super_image))
		{
			if ($restrict_products_images || $watermark_super_image) { 
				if ($super_image && !preg_match("/^http(s)?:\/\//", $super_image)) {
					$super_image = "image_show.php?item_id=".$item_id."&type=super&vc=".md5($super_image); 
				}
			}
			$src = htmlspecialchars($super_image);
			$t->set_var("super_link_style", "");
		} else {
			$t->set_var("super_link_style", "display: none;");
		}
		// show block for super image even there is no super image for main image as additional images may has super images
		if ($open_large_image) {
			$open_large_image_function = "popupImage(this); return false;";
		} elseif ($width) {
			$open_large_image_function = "return openSuperImage(this);";
		} else {
			$open_large_image_function = "return openSuperImage(this);";
		}
		$t->set_var("src", $src);
		$t->set_var("open_large_image_function", $open_large_image_function);
		$t->sparse("super_image", false);
	
	
		$image_small_default = $db->f("small_image");
		$image_large_default = $db->f("big_image");
		$image_super_default = $db->f("super_image");

		$big_image = $db->f("big_image");
		if (!$big_image) { 
			$big_image = $db->f("small_image"); 
			$watermark = $watermark_small_image;
			$watermark_type = "small";
		} else {
			$watermark = $watermark_big_image;
			$watermark_type = "large";
		}
		if (($watermark || $restrict_products_images) && $big_image) { 
			$big_image = "image_show.php?item_id=".$item_id."&type=".$watermark_type."&vc=".md5($big_image); 
		}
		if (!$big_image) { $big_image = $product_no_image_large; } 
		
		$big_image_alt = get_translation($db->f("big_image_alt"));
		if (!$big_image_alt) { $big_image_alt = get_translation($db->f("small_image_alt")); }
		$product_image_width = 0;
		if ($big_image)
		{
			if (!strlen($big_image_alt)) {
				$big_image_alt = $item_name;
			}
			$t->set_var("alt", htmlspecialchars($big_image_alt));
			$t->set_var("src", htmlspecialchars($big_image));
			
			// set link to sper image
			if (strlen($super_image)){
				$t->set_var("src_sup", htmlspecialchars($super_image));
				$t->set_var("image_super", htmlspecialchars($super_image));
			} else {
				$t->set_var("src_sup", htmlspecialchars($big_image));
				$t->set_var("image_super", htmlspecialchars($big_image));
			}			
			$t->parse("big_image", false);
		} else {
			$t->set_var("big_image", "");
		}		

		if (strlen($preview_url)) {
			if (!$preview_width) { $preview_width = 500; }
			if (!$preview_height) { $preview_height = 400; }
			$t->set_var("preview_url", $preview_url);
			$t->set_var("preview_width", $preview_width);
			$t->set_var("preview_height", $preview_height);
			$t->sparse("product_preview", false);
		} else {
			$t->set_var("product_preview", "");
		}

		// show points price
		if ($points_system && $points_price_details) {
			if ($points_price <= 0) {
				$points_price = $item_price * $points_conversion_rate;
			}
			$points_price += $components_points_price;
			$selected_points_price = $selected_price * $points_conversion_rate;
			$product_params["base_points_price"] = $points_price;
			if ($is_points_price) {
				$t->set_var("points_rate", $points_conversion_rate);
				$t->set_var("points_decimals", $points_decimals);
				$t->set_var("points_price", number_format($points_price + $selected_points_price, $points_decimals));
				$t->parse("points_price_block", false);
			} else {
				$t->set_var("points_price_block", "");
			}
		}

		// show reward points
		if ($points_system && $reward_points_details) {
			$reward_points = calculate_reward_points($reward_type, $reward_amount, $item_price, $buying_price, $points_conversion_rate, $points_decimals);
			$reward_points += $components_reward_points;

			$product_params["reward_type"] = $reward_type;
			$product_params["reward_amount"] = $reward_amount;
			$product_params["base_reward_points"] = $reward_points;
			if ($reward_type) {
				$t->set_var("reward_points", number_format($reward_points, $points_decimals));
				$t->parse("reward_points_block", false);
			} else {
				$t->set_var("reward_points_block", "");
			}
		}

		// show reward credits
		if ($credit_system && $reward_credits_details && ($reward_credits_users == 0 || ($reward_credits_users == 1 && $user_id))) {
			$reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $item_price, $buying_price);
			$reward_credits += $components_reward_credits;

			$product_params["base_reward_credits"] = $reward_credits;
			if ($credit_reward_type) {
				$t->set_var("reward_credits", currency_format($reward_credits));
				$t->parse("reward_credits_block", false);
			} else {
				$t->set_var("reward_credits_block", "");
			}
		}

		$product_params["pe"] = 0;
		if ($display_products != 2 || strlen($user_id)) {
			set_quantity_control($quantity_limit, $stock_level, $quantity_control, "products_".$pb_id, $form_id, false, $min_quantity, $max_quantity, $quantity_increment);
	  
			$base_price = calculate_price($price, $is_sales, $sales_price);
			$product_params["base_price"] = $base_price;
			if ($is_price_edit) {
				$t->set_var("price_block_class", "price-edit");
				if ($price > 0) {
					$control_price = number_format($price, 2);
				} else {
					$control_price = "";
				}
				$product_params["pe"] = 1;
				$t->set_var("price", $control_price);
				$t->set_var("price_control", "<input name=\"price".$form_id."\" id=\"price_control\" type=\"text\" class=\"price\" value=\"" . $control_price . "\">");
				$t->sparse("price_block", false);
				$t->set_var("sales", "");
				$t->set_var("save", "");
			} elseif ($sales_price != $price && $is_sales) {
				$discount_percent = round($db->f("discount_percent"), 0);
				if (!$discount_percent && $price > 0) 
					$discount_percent = round(($price - $sales_price) / ($price / 100), 0);
	  
				$t->set_var("discount_percent", $discount_percent);

				set_tax_price($form_id, $item_type_id, $price + $selected_price, 1, $sales_price + $selected_price, $item_tax_id, $tax_free, "price", "sales_price", "tax_sales", true, $components_price, $components_tax_price);
	  
				$product_params["pe"] = 0;
				$t->sparse("price_block", false);
				$t->sparse("sales", false);
				$t->sparse("save", false);
			}
			else
			{
				set_tax_price($form_id, $item_type_id, $price + $selected_price, 1, 0, $item_tax_id, $tax_free, "price", "", "tax_price", true, $components_price, $components_tax_price);

				$product_params["pe"] = 0;
				$t->sparse("price_block", false);
				$t->set_var("sales", "");
				$t->set_var("save", "");
			}
	  
			$internal_buy_link = "";
			$external_buy_link = $db->f("buy_link");
			if (strlen($external_buy_link)) {
				$external_buy_link .= $db->f("affiliate_code");
			} elseif ($is_properties || $quantity_control == "LISTBOX" || $quantity_control == "TEXTBOX" || $is_price_edit) {
				$t->set_var("wishlist_href", "javascript:document.products_" . $pb_id. ".submit();");
			} else {
				$internal_buy_link = $cart_link . "cart=ADD&item_id=" . $item_id . "&rp=" . urlencode($page);
				$t->set_var("wishlist_href", $cart_link . "cart=WISHLIST&item_id=" . $item_id . "&rp=" . urlencode($page));
			}
			set_buy_button($pb_id, $form_id, $internal_buy_link, $external_buy_link);
	  
			$items_in_cart = 0;
			if (($hide_add_limit || $show_in_cart) && is_array($shopping_cart) && count($shopping_cart) > 0) {
				foreach ($shopping_cart as $cart_id => $cart_data) {
					if ($cart_data["ITEM_ID"] == $item_id) {
						$items_in_cart += $cart_data["QUANTITY"];
					}
				}
			}
			if ($hide_add_limit && $max_quantity && $items_in_cart == $max_quantity) {
				// if maximum allowed quantity is already added to cart hide 'add to cart' button
				$hide_add_details = true;
			}

			$t->set_var("in_cart", "");
			if ($show_in_cart) {
				if ($items_in_cart) {
					$t->set_var("hidden_class", "");
				} else {
					$t->set_var("hidden_class", "hidden-block");
				}
				$t->sparse("in_cart", false);
			} 

			// parse 'add to cart' button
			$t->set_var("buy_button", "");
			$t->set_var("cart_add_button", "");
			$t->set_var("cart_add_disabled", "");
			$t->set_var("add_button", "");
			$t->set_var("add_button_disabled", "");

			if (!$hide_add_details && !$shop_hide_add_details) {
				if ($use_stock_level && $stock_level < 1 && $disable_out_of_stock) {
					if ($t->block_exists("cart_add_disabled")) {
						$t->sparse("cart_add_disabled", false);
					} else {
						$t->sparse("add_button_disabled", false);
					}
				} else {
					if ($external_buy_link && $t->block_exists("buy_button")) {
						$t->sparse("buy_button", false);
					} else {
						if (($use_stock_level && $stock_level < 1) || $issue_date_ts > $current_ts) {
							$t->set_var("ADD_TO_CART_MSG", va_constant("PRE_ORDER_MSG"));
						} else {
							$t->set_var("ADD_TO_CART_MSG", va_constant("ADD_TO_CART_MSG"));
						}
						if ($t->block_exists("cart_add_button")) {
							$t->sparse("cart_add_button", false);
						} else {
							$t->sparse("add_button", false);
						}
					}
				}
			}

			if (!$shop_hide_view_details && !$hide_view_details) {
				$t->sparse("view_button", false);
			}
			if (!$shop_hide_checkout_details && !$hide_checkout_details && is_array($shopping_cart)) {
				$t->sparse("checkout_button", false);
			}
			if ($user_id && !$external_buy_link && !$shop_hide_wishlist_details && !$hide_wishlist_details) {
				$t->sparse("wishlist_button", false);
			}
			if ($is_compared) {
				$t->sparse("compare_button", false);
			}
			if (!$external_buy_link && !$shop_hide_shipping_details && !$hide_shipping_details) {
				$t->sparse("shipping_button", false);
				include_once("./blocks/block_shipping_frame.php");
			}


		}
		set_product_params($product_params);
		$json_data = array_merge($json_data, $product_params);
		$t->set_var("product_data", htmlspecialchars(json_encode($json_data)));

		// start tabs
		$tabs = array();
		$tabs_order = array();

		// parse description block
		$parse_description = false;
		if ($full_description) {
			//eval_php_code($full_description);
			$t->set_var("full_description", $full_description);
			$t->parse("description", false);
			$parse_description = true;
		} else {
			$t->set_var("description", "");
		}

		if (strlen($notes)) {
			//eval_php_code($notes);
			$t->set_var("notes", $notes);
			$t->parse("notes_block", false);
			$parse_description = true;
		}

		if (!$hide_weight_details && $weight > 0) {
			if (strpos ($weight, ".") !== false) {
				while (substr($weight, strlen($weight) - 1) == "0") {
					$weight = substr($weight, 0, strlen($weight) - 1);
				}
			}
			if (substr($weight, strlen($weight) - 1) == ".") {
				$weight = substr($weight, 0, strlen($weight) - 1);
			}
			$t->set_var("weight", $weight . " " . $weight_measure);
			$t->sparse("weight_block", false);
			$parse_description = true;
		}
		if ($parse_description) {
			$tabs["desc"] = va_constant("PROD_DESCRIPTION_MSG");
			$tabs_order["desc"] = get_setting_value($settings, "desc_order", 1);
		}
		// end description block
		
		// specification details
		$t->set_var("specification", "");
		// new-spec begin
		$sql  = " SELECT COUNT(*) FROM (" . $table_prefix . "features f ";
		$sql .= " INNER JOIN " . $table_prefix . "features_groups fg ON f.group_id=fg.group_id) ";
		$sql .= " WHERE f.item_id=" . intval($item_id);
		$sql .= " AND fg.show_on_details=1 ";
		$sql .= " AND (f.show_on_details=1 OR f.show_as_group=1) ";
		$db->query($sql);
		$db->next_record();
		$total_spec = $db->f(0);
		if ($total_spec > 0) {
			$tabs["spec"] = va_constant("PROD_SPECIFICATION_MSG");
			$tabs_order["spec"] = get_setting_value($settings, "spec_order", 2);

			$sql  = " SELECT fg.group_id,fg.group_name,f.feature_name,f.feature_value ";
			$sql .= " FROM " . $table_prefix . "features f, " . $table_prefix . "features_groups fg ";
			$sql .= " WHERE f.group_id=fg.group_id ";
			$sql .= " AND f.item_id=" . intval($item_id);
			$sql .= " AND fg.show_on_details=1 ";
			$sql .= " AND (f.show_on_details=1 OR f.show_as_group=1) ";
			$sql .= " ORDER BY fg.group_order, f.feature_id ";
			$db->query($sql);
			// new-spec end 
			if ($db->next_record()) {
				$last_group_id = $db->f("group_id");
				do {
					$group_id = $db->f("group_id");
					$feature_name = get_translation($db->f("feature_name"));
					$feature_value = get_translation($db->f("feature_value"));
					if ($group_id != $last_group_id) {
						$t->set_var("group_name", $last_group_name);
						$t->parse("groups", true);
						$t->set_var("features", "");
					}
      
					$t->set_var("feature_name", $feature_name);
					$t->set_var("feature_value", $feature_value);
					$t->parse("features", true);
      
					$last_group_id = $group_id;
					$last_group_name = get_translation($db->f("group_name"));
				} while ($db->next_record());
				$t->set_var("group_name", $last_group_name);
				$t->parse("groups", true);
			} 
		}
		// end specification

		// item previews 
		$previews = new VA_Previews();
		$previews->item_id          = $item_id;
		$previews->preview_type     = array(1,2);
		$previews->preview_position = 2;
		$previews->showAll("product_previews_under_large");
		$previews->preview_position = 1;
		$total_previews = $previews->showAll("product_previews_tab");

		if ($total_previews ) {
			$tabs["previews"] = va_constant("PROD_PREVIEWS_MSG");
			$tabs_order["previews"] = get_setting_value($settings, "previews_order", 3);
		}
		
		// product images 
		$t->set_var("images", "");

		$image_number = 0;
		$image_section_number = 0;
		$image_below_large = 0;
		$images_section_cols = 5;
		$images_below_cols = 5;
		$default_matched = 0;
		$sql  = " SELECT image_id, image_position, image_title, image_small, image_large, image_super, image_description  ";
		$sql .= " FROM " . $table_prefix . "items_images ";
		$sql .= " WHERE item_id=" . intval($item_id);
		$sql .= " ORDER BY image_order, image_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$image_number++;
	  
			$image_id = $db->f("image_id");
			$image_position = $db->f("image_position");
			$image_title = get_translation($db->f("image_title"));
			$image_description = get_translation($db->f("image_description"));
			$image_small = $db->f("image_small");
			$image_large = $db->f("image_large");
			$image_super = $db->f("image_super");
			$image_small_size  = ""; $image_small_width = 0;
			if (!preg_match("/^http(s)?:\/\//", $image_small)) {
				$image_small_size = @getimagesize($image_small);
				if (is_array($image_small_size)) {	
					$image_small_width = $image_small_size[0];
					$image_small_size = $image_small_size[3];
				}
			}
			// check what section use to parse image
			if ($image_position == 1) {
				$image_name = "rollover_image";
				$super_id = "rollover_super";
				$image_section_number++;
			} else if ($image_position == 2) {
				$image_name = "image_" . $form_id;
				$super_id = "super_link_" . $form_id;
				$image_below_large++;

				if ($image_small_default == $image_small && $image_large_default == $image_large && $image_super_default == $image_super) {
					$default_matched++;
				}
			} else {
				continue;
			}
			// check possible columns number
			if ($image_section_number == 1) {
				if ($image_section_number == 1 && $image_small_width && !preg_match("/^http(s)?:\/\//", $image_large)) {
					$image_large_size = @getimagesize($image_large);
					if (is_array($image_large_size)) {	
						$image_large_width = $image_large_size[0];
						$images_section_cols = intval($image_large_width / $image_small_width);
						if ($images_section_cols < 2) { $images_section_cols = 2; }
					}
				}
			} else if ($image_section_number == 2) {
				// images below main image
				if ($image_below_large == 1) {
					if ($product_image_width && $image_small_width) {
						$images_below_cols = intval($product_image_width / $image_small_width);
						if ($images_below_cols < 2) { $images_below_cols = 2; }
					}
				}
			}


			if ($restrict_products_images || $watermark_small_image) { 
				if ($image_small && !preg_match("/^http(s)?:\/\//", $image_small)) {
					$image_small = "image_show.php?image_id=".$image_id."&type=small&vc=".md5($image_small); 
				}
			}
			if ($restrict_products_images || $watermark_big_image) { 
				if ($image_large && !preg_match("/^http(s)?:\/\//", $image_large)) {
					$image_large = "image_show.php?image_id=".$image_id."&type=large&vc=".md5($image_large); 
				}
			}
			if ($restrict_products_images || $watermark_super_image) { 
				if ($image_super && !preg_match("/^http(s)?:\/\//", $image_super)) {
					$image_super = "image_show.php?image_id=".$image_id."&type=super&vc=".md5($image_super); 
				}
			}
			if (!strlen($image_large)) {
				$image_large = $image_small;
			}
			$rollover_js = ""; $image_click_js = "";
			// pass different super image id for different position
			$rollover_js = "rolloverImage(".$image_id.", '".$image_large."', '".$image_name."', '".$super_id."', '".$image_super."'); ";
    
			$t->set_var("image_id", $image_id);
			$t->set_var("image_title", $image_title);
			$t->set_var("image_alt", htmlspecialchars($image_title));
			$t->set_var("image_small", $image_small);
			$t->set_var("image_size",  $image_small_size);
			$t->set_var("image_large", $image_large);
			if ($image_super) {
				$t->set_var("image_super", $image_super);
			} else {
				$t->set_var("image_large", $image_large);
			}

			$t->set_var("image_description", $image_description);
	   
			$image_click_js = $rollover_js;
			if ($image_section_number == 1) {
				$t->set_var("rollover_image", $image_large);
				$t->set_var("rollover_super_src", $image_super);
				if ($open_large_image) {
					$rollover_super_click = "popupImage(this); return false;";
				} else {
					$rollover_super_click  = "openImage(this); return false;";
				}
				$t->set_var("rollover_super_click", $rollover_super_click);
				if (!$image_super) {
					$t->set_var("rollover_super_style", "display: none;");
				}
			}

			if ($image_super) {
				$image_click_js = ($open_large_image) ? "popupImage(this); return false;" : "openSuperImage(this); return false;	";
			} else {
				$image_click_js = "return false;";
			}

			$t->set_var("rollover_js", $rollover_js);
			$t->set_var("image_click_js", $image_click_js);

			if ($image_position == 1) {
				$t->parse("images_cols", true);
				if ($image_section_number % $images_section_cols == 0) {
					$t->parse("images_rows", true);
					$t->set_var("images_cols", "");
				}
			} else {
				$t->parse("main_images_cols", true);
				if ($image_below_large % $images_below_cols == 0) {
					$t->parse("main_images_rows", true);
					$t->set_var("main_images_cols", "");
				}
			}
		}	    
		// parse row if columns left  
		if ($image_section_number && $image_section_number % $images_section_cols != 0) {
			$t->parse("images_rows", true);
		}
		if ($image_below_large && $image_below_large % $images_below_cols != 0) {
			$t->parse("main_images_rows", true);
		}

		if ($image_section_number) {
			$tabs["images"] = va_constant("PROD_IMAGES_MSG");
			$tabs_order["images"] = get_setting_value($settings, "images_order", 4);
		}
		if ($image_below_large && $image_below_large != $default_matched) {
			$t->parse("main_images", false);
		}
		// end images

		// product accessories
		$accessories_cols = get_setting_value($vars, "accessories_cols", 2);
		if ($accessories_cols < 1) { $accessories_cols = 2; }
		$accessories_image_type = get_setting_value($vars, "accessories_image_type", "small");
		$accessories_image_field = $accessories_image_type;
		if ($accessories_image_type == "large") { $accessories_image_field = "big"; }
		$accessories_watermark = get_setting_value($settings, "watermark_".$accessories_image_field."_image", 0);
		// check description types to show
		$accessories_desc_types = get_setting_value($vars, "accessories_desc", "short");
		if (!is_array($accessories_desc_types)) { $accessories_desc_types = array($accessories_desc_types); }
		$accessories_more_button = get_setting_value($vars, "accessories_more", 1);

		$t->set_var("accessories_block", "");
		$t->set_var("accessories_columns_class", "cols-".$accessories_cols);
		$sql_params = array();
		$sql_params["join"][]   = " INNER JOIN " . $table_prefix . "items_accessories ia ON i.item_id=ia.accessory_id ";	
		$sql_params["where"][]  = " ia.item_id=" . $db->tosql($item_id, INTEGER);		
		$accessories_ids   = VA_Products::find_all_ids($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
		$total_accessories = 0;
		if ($accessories_ids) {
			$total_accessories = count($accessories_ids);
			$allowed_accessories_ids = VA_Products::find_all_ids("i.item_id IN (" . $db->tosql($accessories_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);
			
			$tabs["accessories"] = va_constant("PROD_ACCESSORIES_MSG");
			$tabs_order["accessories"] = get_setting_value($settings, "accessories_order", 5);

			$accessory_index = 0;
			$sql  = " SELECT i.item_id, i.item_type_id, i.item_name, i.a_title, i.friendly_url, ";
			$sql .= " i.tiny_image, i.small_image, i.big_image, i.super_image, ";
			$sql .= " i.tiny_image_alt, i.small_image_alt, i.big_image_alt, i.super_image_alt, ";
			$sql .= " i.special_offer, i.short_description, i.full_description, i.highlights, i.notes, ";
			$sql .= " i.buying_price, i." . $price_field . ", i.".$properties_field.", i." . $sales_field . ", i.is_sales, i.tax_id, i.tax_free ";
			$sql .= " FROM ((" . $table_prefix . "items i ";
			$sql .= " INNER JOIN " . $table_prefix . "items_accessories ia ON i.item_id=ia.accessory_id)";
			$sql .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id) ";
			$sql .= " WHERE ia.item_id=" . $db->tosql($item_id, INTEGER);
			$sql .= " AND i.item_id IN (" . $db->tosql($accessories_ids, INTEGERS_LIST) . ")";
			$sql .= " ORDER BY ia.accessory_order ";
			$db->query($sql);
			while ($db->next_record()) {
				$accessory_index++;
				$accessory_id = $db->f("item_id");
				$accessory_type_id = $db->f("item_type_id");
				$accessory_name = get_translation($db->f("item_name"));
				$accessory_a_title = get_translation($db->f("a_title"));
				$accessory_friendly_url = $db->f("friendly_url");
				// desc fields
  			$accessory_short_description = get_translation($db->f("short_description"));
  			$accessory_full_description = get_translation($db->f("full_description"));
  			$accessory_special_offer = get_translation($db->f("special_offer"));
  			$accessory_highlights = get_translation($db->f("highlights"));
  			$accessory_notes = get_translation($db->f("notes"));

				$buy_accessory_href = $page . "&rnd=" . $random_value . "&cart=ADD&accessory_id=" . $accessory_id;
				if ($friendly_urls && $accessory_friendly_url) {
					$t->set_var("accessory_details_url", $accessory_friendly_url . $friendly_extension);
				} else {
					$t->set_var("accessory_details_url", get_custom_friendly_url("product_details.php") . "?item_id=" . $accessory_id);
				}

				// show images
				$accessory_image = $db->f($accessories_image_field."_image");
				$accessory_image_alt = $db->f($accessories_image_field."_image_alt");

				if (strlen($accessory_image)) {
					if (!preg_match("/^http(s)?:\/\//", $accessory_image)) {
						if ($accessories_watermark || $restrict_products_images) {
							$accessory_image = "image_show.php?item_id=".$accessory_id."&type=".$accessories_image_type."&vc=".md5($accessory_image);
						}
					}
					if (!strlen($accessory_image_alt)) {
						$accessory_image_alt = $accessory_name;
					}
					$t->set_var("alt", htmlspecialchars($accessory_image_alt));
					$t->set_var("src", htmlspecialchars($accessory_image));
					$t->sparse("accessory_image", false);
				} else {
					$t->set_var("accessory_image", "");
				}

				$price = $db->f($price_field);
				$buying_price = $db->f("buying_price");
				$sales_price = $db->f($sales_field);
				$is_sales = $db->f("is_sales");
				$properties_price = $db->f($properties_field);
				
				$discount_applicable = 1;
				$q_prices    = get_quantity_price($accessory_id, 1);
				if (sizeof($q_prices)) {
					$user_price  = $q_prices [0];
					$discount_applicable = $q_prices [2];
					if ($is_sales) {
						$sales_price = $user_price;
					} else {
						$price = $user_price;
					}
				}
			
				$accessory_tax_id = $db->f("tax_id");
				$accessory_tax_free = $db->f("tax_free");
				if ($user_tax_free) { $accessory_tax_free = $user_tax_free; }
				$accessory_price = calculate_price($price, $is_sales, $sales_price);
				if ($discount_applicable) {
					if ($discount_type == 1 || $discount_type == 3) {
						$accessory_price -= round(($accessory_price * $discount_amount) / 100, 2);
					} elseif ($discount_type == 2) {
						$accessory_price -= round($discount_amount, 2);
					} elseif ($discount_type == 4) {
						$accessory_price -= round((($accessory_price - $buying_price) * $discount_amount) / 100, 2);
					}
				}
				// add properties and components prices
				$accessory_price += $properties_price;

				set_tax_price($accessory_id, $accessory_type_id, $accessory_price, 1, 0, $accessory_tax_id, $accessory_tax_free, "accessory_price", "", "accessory_tax_price", false, 0, 0, false);
				
				$t->set_var("accessory_id", $accessory_id);
				$t->set_var("accessory_name", $accessory_name);
				$t->set_var("accessory_a_title", htmlspecialchars($accessory_a_title));
				if ($display_products != 2 || strlen($user_id)) {
					$t->set_var("buy_accessory_href", $buy_accessory_href);
					$t->sparse("accessory_price_block", false);
				}
				if (!$allowed_accessories_ids || !in_array($accessory_id, $allowed_accessories_ids)) {
					$t->set_var("restricted_class", " restricted ");
				} else {
					$t->set_var("restricted_class", "");
				}

				// clear and show desc fields
				$t->set_var("accessory_desc_block", "");
				$t->set_var("accessory_short_description", "");
				$t->set_var("accessory_full_description", "");
				$t->set_var("accessory_special_offer", "");
				$t->set_var("accessory_highlights", "");
				$t->set_var("accessory_notes", "");

				$desc_block = false;
				if (in_array("short", $accessories_desc_types)) {
					$desc_block = true;
					$t->set_var("desc_text", $accessory_short_description);
					$t->sparse("accessory_short_description", false);
				} 
				if (in_array("full", $accessories_desc_types)) {
					$desc_block = true;
					$t->set_var("desc_text", $accessory_full_description);
					$t->sparse("accessory_full_description", false);
				}
				if (in_array("high", $accessories_desc_types)) {
					$desc_block = true;
					$t->set_var("desc_text", $accessory_highlights);
					$t->sparse("accessory_highlights", false);
				}
				if (in_array("spec", $accessories_desc_types)) {
					$desc_block = true;
					$t->set_var("desc_text", $accessory_special_offer);
					$t->sparse("accessory_special_offer", false);
				}
				if (in_array("note", $accessories_desc_types)) {
					$desc_block = true;
					$t->set_var("desc_text", $accessory_notes);
					$t->sparse("accessory_notes", false);
				}

				if ($desc_block) {
					$t->sparse("accessory_desc_block", false);
				}

				// show/hide 'more' button
				if ($accessories_more_button) {
					$t->sparse("accessory_more_button", false);
				} else {
					$t->set_var("accessory_more_button", "");
				}
	    
				$column_index = ($accessory_index % $accessories_cols) ? ($accessory_index % $accessories_cols) : $accessories_cols;
				$t->set_var("column_class", "col-".$column_index);
				$t->parse("accessories_cols", true);
				if ($accessory_index % $accessories_cols == 0) {
					$t->parse("accessories_rows", true);
					$t->set_var("accessories_cols", "");
				}
			} while ($db->next_record());

			if ($accessory_index % $accessories_cols != 0) {
				$t->parse("accessories_rows", true);
			}
		}

		//---------- new reviews block
		$t->set_var("reviews_tab", "");
		if ($allow_reviews != -1 &&
			($reviews_allowed_view == 1 || ($reviews_allowed_view == 2 && strlen($user_id))
			|| $reviews_allowed_post == 1 || ($reviews_allowed_post == 2 && strlen($user_id)))) {
			$tabs["reviews"] = va_constant("REVIEWS_MSG");
			$tabs_order["reviews"] = get_setting_value($settings, "reviews_order", 6);
  
			$script_run_mode = "include";
			$vars = array("block_type" => "sub_block", "block_code" => "product_reviews");
			if (file_exists("./blocks_custom/block_reviews.php")) {
				include("./blocks_custom/block_reviews.php");
			} else {
				include("./blocks/block_reviews.php");
			}
			$script_run_mode = "";
			$t->parse("block_reviews", false);

			$t->sparse("reviews_tab", false);
		}
		//---------- end reviews block
		//---------- new questions block
		$t->set_var("questions_tab", "");
		if ($allow_questions != -1 && 
			($questions_allowed_view == 1 || ($questions_allowed_view == 2 && strlen($user_id))
			|| $questions_allowed_post == 1 || ($questions_allowed_post == 2 && strlen($user_id)))) {
			$tabs["questions"] = va_constant("QUESTIONS_MSG");
			$tabs_order["questions"] = get_setting_value($settings, "questions_order", 6);
  
			$script_run_mode = "include";
			$vars = array("block_type" => "sub_block", "block_code" => "product_questions");
			if (file_exists("./blocks_custom/block_reviews.php")) {
				include("./blocks_custom/block_reviews.php");
			} else {
				include("./blocks/block_reviews.php");
			}
			$script_run_mode = "";
			$t->parse("block_questions", false);

			$t->sparse("questions_tab", false);
		}
		//---------- end questions block

		// custom tabs
		$items_tabs = array();
		$sql  = " SELECT * FROM " . $table_prefix . "items_tabs ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$sql .= " AND hide_tab<>1 ";
		$db->query($sql);
		while ($db->next_record()) {
			$tab_id = $db->f("tab_id");
			$tab_order = $db->f("tab_order");
			$tab_title = get_translation($db->f("tab_title"));
			$tab_desc = get_translation($db->f("tab_desc"));
			parse_value($tab_desc);
			$tabs["tab".$tab_id] = $tab_title;
			$tabs_order["tab".$tab_id] = $tab_order;

			$items_tabs[$tab_id] = $tab_desc;
		}

		// Price matrix tab
		if ($price_matrix_details && $price_matrix_tab) {
			$tabs["tabprice_matrix"] = get_setting_value($vars, "price_matrix_tab_title", va_constant("PRICE_MATRIX_MSG")); 
			$tabs_order["tabprice_matrix"] = get_setting_value($vars, "price_matrix_tab_order", 100);
			$items_tabs["price_matrix"] = $t->get_var("price_matrix");
			$t->set_var("price_matrix", "");
		}

		array_multisort($tabs_order, $tabs);

		// parse tabs
		$tab = get_param("tab");
		if (!strlen($tab) && count($tabs) > 0) { 
			$tab_keys = array_keys($tabs);
			$tab = $tab_keys[0]; 
		}
		$t->set_var("tab", htmlspecialchars($tab));

		foreach($items_tabs as $tab_id => $tab_desc) {
			if ($tab == ("tab".$tab_id)) {
				$data_class = "tab-show";
			} else {
				$data_class = "tab-hide";
			}
			$t->set_var("tab_id", $tab_id);
			$t->set_var("tab_desc", $tab_desc);
			$t->set_var("data_class", $data_class);
			$t->parse("items_tabs", true);
		}


		if ($friendly_urls && $friendly_url) {
			$tab_transfer_query = transfer_params(array("item_id"), false);
			$tab_href = $friendly_url . $friendly_extension . $tab_transfer_query;
		} else {
			$tab_href = get_custom_friendly_url("product_details.php") . $transfer_query;
		}
		if (strrpos($tab_href, "?")) {
			$tab_href .= "&tab=";
		} else {
			$tab_href .= "?tab=";
		}

		$t->set_var("tabs", ""); 
		foreach ($tabs as $tab_name => $tab_title) {
			if ($tab == $tab_name) {
				$data_class = "tab-show";
				$tab_class = "tab-active";
			} else {
				$data_class = "tab-hide";
				$tab_class = "";
			}
			$t->set_var("tab_class", $tab_class);
			$t->set_var("data_class", $data_class);
			$t->set_var($tab_name."_class", $data_class);
			$t->set_var("tab_id", $tab_name . "_tab");
			$t->set_var("tab_td_id", $tab_name . "_td_tab");
			$t->set_var("tab_a_id", $tab_name . "_a_tab");
			$t->set_var("tab_name", $tab_name);
			$t->set_var("tab_title", htmlspecialchars($tab_title));
			$t->set_var("tab_href", htmlspecialchars($tab_href.$tab_name));
			//$t->set_var("tab_style", $tab_style);
			//$t->set_var($tab_name . "_style", $data_style);

			$t->parse("tabs", true);
		}
		$t->parse("tabs_block", false);
		
		// parse all sections/tabs
		if ($parse_description) {
			$t->sparse("description_block", false);
		}
		if ($total_spec > 0) {
			$t->sparse("specification_block", false);
		}
		if ($image_section_number) {
			$t->sparse("images_block", false);
		}
		if ($total_previews) {
			$t->sparse("previews_block", false);
		}		
		if ($total_accessories > 0) {
			$t->sparse("accessories_block", false);
		}
		if ($reviews_allowed_view == 1 || ($reviews_allowed_view == 2 && strlen($user_id)) 
			|| $reviews_allowed_post == 1 || ($reviews_allowed_post == 2 && strlen($user_id))) {
			$t->sparse("reviews_block", false);
		}

		// parse item block
		$t->parse("item", false);

		// update total views for product
		$products_viewed = get_session("session_products_viewed");
		if (!isset($products_viewed[$item_id])) {
			$sql  = " UPDATE " . $table_prefix . "items SET total_views=" . $db->tosql(($total_views + 1), INTEGER);
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$db->query($sql);

			$products_viewed[$item_id] = true;
			set_session("session_products_viewed", $products_viewed);
		}


		// fill in recently viewed products
		$recent_records = 10;
		$recently_viewed = get_session("session_recently_viewed");
		if (!is_array($recently_viewed)) {
			$recently_viewed = array();
		} 
		$recent_index = 0;
		foreach ($recently_viewed as $recent_key => $recent_id) {
			if ($recent_id == $item_id) {
				unset($recently_viewed[$recent_key]);
			} else {
				$recent_index++;
				if ($recent_index >= $recent_records) {
					unset($recently_viewed[$recent_key]);
				}
			}
		}
		$t->sparse("links_block");
		array_unshift($recently_viewed, $item_id);
		set_session("session_recently_viewed", $recently_viewed);
	}


	$block_parsed = true;
	// check if we need to parse hidden block for wishlist types
	if ($user_id && !$external_buy_link && !$shop_hide_wishlist_details) {
		include_once("./blocks/block_wishlist_types.php");
	}
