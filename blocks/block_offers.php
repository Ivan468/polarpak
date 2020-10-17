<?php

	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	// set necessary scripts
	set_script_tag("js/shopping.js");
	set_script_tag("js/ajax.js");
	set_script_tag("js/blocks.js");
	set_script_tag("js/images.js");

	$default_title = SPECIAL_OFFER_TITLE;

	// global array to use in different blocks
	if(!isset($va_data)) { $va_data = array(); }
	if(!isset($va_data["products_index"])) { $va_data["products_index"] = 0; }
	$start_index = $va_data["products_index"] + 1;

	$param_pb_id = get_param("pb_id");
	$shopping_cart = get_session("shopping_cart");
	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$discount_type = get_session("session_discount_type");
	$discount_amount = get_session("session_discount_amount");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$display_products = get_setting_value($settings, "display_products", 0);
	$product_no_image = get_setting_value($settings, "product_no_image", "");
	$product_no_image_large = get_setting_value($settings, "product_no_image_large", "");
	$watermark_small = get_setting_value($settings, "watermark_small_image", 0);
	$watermark_big_image = get_setting_value($settings, "watermark_big_image", 0);
	$confirm_add = get_setting_value($settings, "confirm_add", 1);
	$redirect_to_cart = get_setting_value($settings, "redirect_to_cart", ""); 
	$hide_add_limit = get_setting_value($settings, "hide_add_limit", ""); 
	$show_in_cart = get_setting_value($settings, "show_in_cart", ""); 

	// check buttons to show
	$bn_add = get_setting_value($vars, "bn_add", 1);
	$bn_view = get_setting_value($vars, "bn_view", 0);
	$bn_goto = get_setting_value($vars, "bn_goto", 0);
	$bn_wish = get_setting_value($vars, "bn_wish", 0);
	$bn_more = get_setting_value($vars, "bn_more", 0);

	$quantity_control = get_setting_value($vars, "prod_offers_quantity_control", "");
	$multi_add = get_setting_value($vars, "multi_add", 0);

	$prod_offers_points_price = get_setting_value($vars, "prod_offers_points_price", 0);
	$prod_offers_reward_points = get_setting_value($vars, "prod_offers_reward_points", 0);
	$prod_offers_reward_credits = get_setting_value($vars, "prod_offers_reward_credits", 0);

	// popup box vars
	$popup_box = get_setting_value($vars, "popup_box", 0);
	$box_image_type = get_setting_value($vars, "box_image_type", 3);
	product_image_fields($box_image_type, $box_image_type_name, $box_image_field, $box_image_alt_field, $watermark, $box_no_image);
	$box_desc_type = get_setting_value($vars, "box_desc_type", 0);
	$box_desc_field = "";
	if ($box_desc_type == 1) {
		$box_desc_field = "short_description";
	} elseif ($box_desc_type == 2) {
		$box_desc_field = "full_description";
	} elseif ($box_desc_type == 3) {
		$box_desc_field = "highlights";
	} elseif ($box_desc_type == 4) {
		$box_desc_field = "special_offer";
	}

	$product_params = prepare_product_params();

	$current_ts = va_timestamp();

	// global points settings
	$points_system = get_setting_value($settings, "points_system", 0);
	$points_conversion_rate = get_setting_value($settings, "points_conversion_rate", 1);
	$points_decimals = get_setting_value($settings, "points_decimals", 0);
	$points_prices = get_setting_value($settings, "points_prices", 0);

	// global credit settings
	$credit_system = get_setting_value($settings, "credit_system", 0);
	$reward_credits_users = get_setting_value($settings, "reward_credits_users", 0);

	$image_type_name = "small";
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	$user_id = get_session("session_user_id");
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
	
	// new product settings	
	$new_product_enable = get_setting_value($settings, "new_product_enable", 0);	
	$new_product_order  = get_setting_value($settings, "new_product_order", 0);	
	$new_product_field = "";
	if ($new_product_enable) {
		if ($new_product_order == 0) {
			$new_product_field = "issue_date";
		} elseif ($new_product_order == 1) {
			$new_product_field = "date_added";
		} elseif ($new_product_order == 2) {
			$new_product_field = "date_modified";
		}
	}

	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$pass_parameters = get_transfer_params($page_friendly_params);
		$current_page = $page_friendly_url . $friendly_extension;
	} else {
		$current_page = get_custom_friendly_url($script_name);
		$pass_parameters = get_transfer_params();
	}

	srand((double) microtime() * 1000000);
	$rnd = rand();

	$query_string = get_query_string($pass_parameters, "", "", true);
	$rp = $current_page . $query_string;
	$cart_link  = $rp;
	$cart_link .= strlen($query_string) ? "&" : "?";
	$cart_link .= "rnd=" . $rnd . "&";

	$html_template = get_setting_value($block, "html_template", "block_offers.html"); 
  $t->set_file("block_body", $html_template);
	set_script_tag("js/mouse_coords.js");
	$t->set_var("items_cols",  "");
	$t->set_var("items_rows",  "");
	$t->set_var("product_details_href", "product_details.php");
	$t->set_var("basket_href",   get_custom_friendly_url("basket.php"));
	$t->set_var("checkout_href", get_custom_friendly_url("checkout.php"));
	$t->set_var("rp_url", urlencode($rp));
	$t->set_var("confirm_add", $confirm_add);
	$t->set_var("rnd", $rnd);	
	$t->set_var("redirect_to_cart", $redirect_to_cart);	
	$t->set_var("multi_add", $multi_add);	
	$t->set_var("sc_params", htmlspecialchars(json_encode($sc_params)));
	$t->set_var("out_stock_alert", str_replace("'", "\\'", htmlspecialchars(va_constant("PRODUCT_OUT_STOCK_MSG"))));

	if ($param_pb_id == $pb_id) {
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
	
	$items_ids = VA_Products::find_all_ids("i.is_special_offer=1", VIEW_CATEGORIES_ITEMS_PERM);
	if (!$items_ids) return;
	$allowed_items_ids = VA_Products::find_all_ids("i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ")", VIEW_ITEMS_PERM);
	$total_records = count($items_ids);

	$records_per_page = get_setting_value($vars, "prod_offers_recs", 10);
	$pages_number = 5;
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $current_page);
	$page_number = $n->set_navigator("navigator", "pn_pr_sp", SIMPLE, $pages_number, $records_per_page, $total_records, false, $pass_parameters);

	$php_in_hot_desc = get_setting_value($settings, "php_in_products_hot_desc", 0);
	
	// check slider settings
	$slider_style = ""; $sliding_style = ""; $column_style = "";

	$slider_type = get_setting_value($vars, "prod_slider_type", 0);
	$slider_width = get_setting_value($vars, "prod_slider_width", "");		
	$slider_column_width = get_setting_value($vars, "slider_column_width", "");
	$slider_height = get_setting_value($vars, "prod_slider_height", "");	
	$slider_style = get_setting_value($vars, "prod_slider_style", "");
	$data_js = ($slider_type) ? "slideshow" : ""; 
	$t->set_var("data_js", htmlspecialchars($data_js));
	$t->set_var("slider_type", htmlspecialchars($slider_type));
	$t->set_var("slider_column_width", htmlspecialchars($slider_column_width));
	if ($slider_type > 0) { 
		if (strlen($slider_width)) { $slider_style .= "width: " . $slider_width . "; "; }
		if (strlen($slider_height)) { $slider_style .= "height: " . $slider_height. "; "; }
	}
	$original_columns = get_setting_value($vars, "prod_offers_cols", 1);
	if ($slider_type == 1 || $slider_type == 3) { // vertical
		$so_columns =  1;
		$sliding_style = "width: 100%;";
		$column_style = "width: 100%;";
	} else if ($slider_type == 2 || $slider_type == 4) { // horizontal
		$so_columns = $records_per_page; 
		if ($slider_column_width < 1) { $slider_column_width = 300; }
		$column_style = "width:".$slider_column_width."px";

		$t->set_var("slider_columns", $original_columns);
		$records_left = $total_records - ($page_number - 1) * $records_per_page;
		if ($records_left > $records_per_page) {
			$sliding_width = ($slider_column_width*$records_per_page);
		} else {
			$sliding_width = ($slider_column_width*$records_left);
		}
		$sliding_style = "width: ".$sliding_width."px;";
	} else {
		$so_columns = get_setting_value($vars, "prod_offers_cols", 1);
		$column_width = intval(100 / $so_columns)."%";
		$sliding_style = "width: 100%;";
	}

	$t->set_var("col_style", $column_style);
	$t->set_var("column_style", $column_style);
	$t->set_var("sliding_style", $sliding_style);
	$t->set_var("slider_style", $slider_style);
	$t->set_var("columns_class", "cols-".$so_columns);

	$so_number = 0;
	$sql  = " SELECT i.item_id, i.item_type_id, i.item_name, i.a_title, i.friendly_url, ";
	$sql .= " i.special_offer, i.short_description, i.full_description, i.highlights, i.features, ";
	$sql .= " i.buying_price, i." . $price_field . ", i.".$properties_field.", i." . $sales_field . ", i.is_sales, i.is_price_edit, ";
	$sql .= " i.tax_id, i.tax_free, ";
	$sql .= " i.manufacturer_code, m.manufacturer_name, m.affiliate_code, ";
	$sql .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
	$sql .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
	$sql .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
	$sql .= " i.issue_date, i.stock_level, i.use_stock_level, i.disable_out_of_stock, i.hide_out_of_stock, i.hide_add_list, ";
	if ($new_product_field) {
		$sql .= "i." . $new_product_field . ",";
	}
	$sql .= " i.big_image, i.big_image_alt, i.small_image, i.small_image_alt ";
	$sql .= " FROM ((" . $table_prefix . "items i ";
	$sql .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";
 	$sql .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id) ";
	$sql .= " WHERE i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
	$sql .= " ORDER BY i.special_order, i.item_order, i.item_id ";
	
	$items_indexes = array();
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
	                   	
		do 
		{
			$so_number++;
			$va_data["products_index"]++;
			$items_indexes[] = $va_data["products_index"];
			$index = $va_data["products_index"];

			$item_id = $db->f("item_id");
			$form_id = "so_".$pb_id."_".$item_id;
			$item_type_id = $db->f("item_type_id");
			$item_name = get_translation($db->f("item_name"));
			$product_params["form_id"] = $form_id;
			$product_params["item_name"] = strip_tags($item_name);
			$a_title = get_translation($db->f("a_title"));
			$friendly_url = $db->f("friendly_url");
			$special_offer = get_translation($db->f("special_offer"));
			//eval_php_code($special_offer);
			$short_description = get_translation($db->f("short_description"));
			$highlights = get_translation($db->f("highlights"));
			$small_image = $db->f("small_image");
			$small_image_alt = get_translation($db->f("small_image_alt"));
			$buy_link = $db->f("buy_link");
			$affiliate_code = $db->f("affiliate_code");
			$manufacturer_code = $db->f("manufacturer_code");
			$manufacturer_name = $db->f("manufacturer_name");
			$is_price_edit = $db->f("is_price_edit");

			$issue_date_ts = 0;
			$issue_date = $db->f("issue_date", DATETIME);
			if (is_array($issue_date)) {
				$issue_date_ts = va_timestamp($issue_date);
			}
			$stock_level = $db->f("stock_level");
			$use_stock_level = $db->f("use_stock_level");
			$disable_out_of_stock = $db->f("disable_out_of_stock");
			$hide_out_of_stock = $db->f("hide_out_of_stock");
			$hide_add_list = $db->f("hide_add_list");

  		$min_quantity = $db->f("min_quantity");
			$max_quantity = $db->f("max_quantity");
			$quantity_increment = $db->f("quantity_increment");
			$quantity_limit = ($use_stock_level && ($disable_out_of_stock || $hide_out_of_stock));

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
	  
			if ($friendly_urls && $friendly_url) {
				$details_url = $friendly_url . $friendly_extension;
			} else {
				$details_url = "product_details.php?item_id=".urlencode($item_id);
			}
				
			if ($new_product_enable) {
				$new_product_date = $db->f($new_product_field);			
				$is_new_product   = is_new_product($new_product_date);
			} else {
				$is_new_product = false;
			}
			if ($is_new_product) {
				$t->set_var("product_new_class", " ico-new ");
			} else {
				$t->set_var("product_new_class", "");
			}
			
			if (!$allowed_items_ids || !in_array($item_id, $allowed_items_ids)) {
				$t->set_var("restricted_class", " restricted ");
				$bn_add = false;
			} else {
				$t->set_var("restricted_class", "");
			}
			$t->set_var("item_id", $item_id);
			$t->set_var("form_id", $form_id);
			$t->set_var("index", $va_data["products_index"]);
			$t->set_var("item_name", $item_name);
			$t->set_var("a_title", htmlspecialchars($a_title));
			$t->set_var("details_url", htmlspecialchars($details_url));
			$t->set_var("special_offer", $special_offer);
			$t->set_var("short_description", $short_description);
			$t->set_var("highlights", $highlights);
			$t->set_var("sp_tax_price", "");
			$t->set_var("sp_tax_sales", "");
	  
			if ($display_products != 2 || strlen($user_id)) {
				$price = $db->f($price_field);
				$sales_price = $db->f($sales_field);
				$is_sales = $db->f("is_sales");
				$buying_price = $db->f("buying_price");
				$properties_price = $db->f($properties_field);
				$tax_id = $db->f("tax_id");
				$tax_free = $db->f("tax_free");
				if ($user_tax_free) { $tax_free = $user_tax_free; }
					
				$discount_applicable = 1;
				$q_prices    = get_quantity_price($item_id, 1);
				if (sizeof($q_prices)) {
					$user_price  = $q_prices [0];
					$discount_applicable = $q_prices [2];
					if ($is_sales) {
						$sales_price = $user_price;
					} else {
						$price = $user_price;
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
				// add options and components prices
				$price += $properties_price;
				$sales_price += $properties_price;
				$product_params["pe"] = 0;
				if ($is_price_edit) {
					$product_params["pe"] = 1;
					$formatted_price = ($price > 0) ? number_format($price, 2) : "";
					//$t->set_var("sp_price", $formatted_price."<input name=\"price".$index."\" type=\"hidden\" value=\"" . $formatted_price. "\">");
					$t->set_var("sp_price", "<input name=\"price".$index."\" type=\"text\" class=\"price\" value=\"" . $formatted_price. "\">");
					$t->set_var("price_block_class", "price-edit");
					$t->sparse("sp_price_block", false);
					$t->set_var("sp_sales", "");
					$t->set_var("sp_save", "");
				} else if ($is_sales && $sales_price != $price) {
					set_tax_price($va_data["products_index"], $item_type_id, $price, 1, $sales_price, $tax_id, $tax_free, "sp_price", "sp_sales_price", "sp_tax_sales", false);
	  
					$t->sparse("sp_price_block", false);
					$t->sparse("sp_sales", false);
				} else {
					set_tax_price($va_data["products_index"], $item_type_id, $price, 1, 0, $tax_id, $tax_free, "sp_price", "", "sp_tax_price", false);
	  
					$t->sparse("sp_price_block", false);
					$t->set_var("sp_sales", "");
				}
	  
				$item_price = calculate_price($price, $is_sales, $sales_price);
				// show points price
				if ($points_system && $prod_offers_points_price) {
					if ($points_price <= 0) {
						$points_price = $item_price * $points_conversion_rate;
					}
					//$points_price += $components_points_price;
					//$selected_points_price = $selected_price * $points_conversion_rate;
					$product_params["base_points_price"] = $points_price;
					if ($is_points_price) {
						$t->set_var("points_rate", $points_conversion_rate);
						$t->set_var("points_decimals", $points_decimals);
						//$t->set_var("points_price", number_format($points_price + $selected_points_price, $points_decimals));
						$t->set_var("points_price", number_format($points_price, $points_decimals));
						$t->sparse("points_price_block", false);
					} else {
						$t->set_var("points_price_block", "");
					}
				}
	  
				// show reward points
				if ($points_system && $prod_offers_reward_points) {
					$reward_points = calculate_reward_points($reward_type, $reward_amount, $item_price, $buying_price, $points_conversion_rate, $points_decimals);
					//$reward_points += $components_reward_points;
	  
					$product_params["base_reward_points"] = $reward_points;
					if ($reward_type) {
						$t->set_var("reward_points", number_format($reward_points, $points_decimals));
						$t->sparse("reward_points_block", false);
					} else {
						$t->set_var("reward_points_block", "");
					}
				}
	  
				// show reward credits
				if ($credit_system && $prod_offers_reward_credits && ($reward_credits_users == 0 || ($reward_credits_users == 1 && $user_id))) {
					$reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $item_price, $buying_price);
					//$reward_credits += $components_reward_credits;
	  
					$product_params["base_reward_credits"] = $reward_credits;
					if ($credit_reward_type) {
						$t->set_var("reward_credits", currency_format($reward_credits));
						$t->sparse("reward_credits_block", false);
					} else {
						$t->set_var("reward_credits_block", "");
					}
				}

				// show quantity control
				set_quantity_control($quantity_limit, $stock_level, $quantity_control, "products_".$pb_id, "", false, $min_quantity, $max_quantity, $quantity_increment);
			
				// show buttons				
				if ($buy_link) {
					$t->set_var("buy_href", $buy_link . $affiliate_code);
				//} elseif ($is_properties || $product_quantity == "LISTBOX" || $product_quantity == "TEXTBOX" || $is_price_edit) {
				} elseif ($quantity_control == "LISTBOX" || $quantity_control == "TEXTBOX") {
					$t->set_var("buy_href", "javascript:document.form_" . $form_id . ".submit();");
					$t->set_var("wishlist_href", "javascript:document.form_" . $form_id . ".submit();");
				} else {
					$t->set_var("buy_href", htmlspecialchars($cart_link . "cart=ADD&add_id=" . $item_id . "&rp=". urlencode($rp). "#p" . $item_id));
					$t->set_var("wishlist_href", htmlspecialchars($cart_link . "cart=WISHLIST&add_id=" . $item_id . "&rp=". urlencode($rp). "#p" . $item_id));
				}
	  
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
					$hide_add_button = true;
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
				$t->set_var("buy_button", "");
				$t->set_var("cart_add_button", "");
				$t->set_var("cart_add_disabled", "");
				$t->set_var("add_button", "");
				$t->set_var("add_button_disabled", "");

				if ($bn_add) {
					if ($use_stock_level && $stock_level < 1 && $disable_out_of_stock) {
						if ($t->block_exists("cart_add_disabled")) {
							$t->sparse("cart_add_disabled", false);
						} else {
							$t->sparse("add_button_disabled", false);
						}
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
				if (!$bn_view) {
					$t->set_var("view_button", "");
				} else {
					$t->sparse("view_button", false);
				}
				if ($bn_goto && is_array($shopping_cart) && sizeof($shopping_cart) > 0) {
					$t->sparse("checkout_button", false);
				} else {
					$t->set_var("checkout_button", "");
				}
				if ($user_id && !$buy_link && $bn_wish) {
					$t->sparse("wishlist_button", false);
				} else {
					$t->set_var("wishlist_button", "");
				}

				// show/hide 'more' button
				if ($bn_more) {
					$t->sparse("more_button", false);
				} else {
					$t->set_var("more_button", "");
				}

				set_product_params($product_params);
				$json_data = isset($data["json"]) ? $data["json"] : array(); // for compatability with older version
				$json_data["currency"] = $currency;
				$json_data = array_merge($json_data, $product_params);
				$t->set_var("product_data", htmlspecialchars(json_encode($json_data)));

				$param_value = $t->get_var("product_params");
				// set form parameters
				$product_index = $va_data["products_index"];
				$t->set_var("param_name", "product_params".$product_index);
				$t->set_var("param_value", $param_value);
				$t->sparse("form_params", true);
				$t->set_var("param_name", "item_id".$product_index);
				$t->set_var("param_value", $item_id);
				$t->sparse("form_params", true);
				$t->set_var("param_name", "tax_percent".$product_index);
				$t->set_var("param_value", $t->get_var("tax_percent"));
				$t->sparse("form_params", true);
			}

			$image_offer_js = "";
			if ($popup_box) {
				$image_offer_js = " onmousemove=\"moveSpecialOffer(event);\" onmouseover=\"popupSpecialOffer('so_$item_id', 'block');\" onmouseout=\"popupSpecialOffer('so_$item_id', 'none');\" ";
			}
			$t->set_var("image_offer_js", $image_offer_js);
  
			if (!strlen($small_image)) {
				$image_exists = false;
				$small_image = $product_no_image;
			} elseif (!image_exists($small_image)) {
				$image_exists = false;
				$small_image = $product_no_image;
			} else {
				$image_exists = true;
			}
			if ($small_image)
			{
				if (preg_match("/^http\:\/\//", $small_image)) {
					$image_size = "";
				} else {
					$image_size = @GetImageSize($small_image);
					if ($image_exists && ($watermark_small || $restrict_products_images)) {
						$small_image = "image_show.php?item_id=".$item_id."&type=small&vc=".md5($small_image);
					}
				}
				if (!strlen($small_image_alt)) { $small_image_alt = $item_name; }
				$t->set_var("alt", htmlspecialchars($small_image_alt));
				$t->set_var("src", htmlspecialchars($small_image));
				if (is_array($image_size)) {
					$t->set_var("width", "width=\"" . $image_size[0] . "\"");
					$t->set_var("height", "height=\"" . $image_size[1] . "\"");
				} else {
					$t->set_var("width", "");
					$t->set_var("height", "");
				}
				$t->parse("small_image", false);
			} else {
				$t->set_var("small_image", "");
			}
			
			if ($popup_box) {
				$box_image_exists = false;
				if ($box_image_field) {
					$box_image     = $db->f($box_image_field);	
					$box_image_alt = $db->f($box_image_alt_field);	
					if (!strlen($box_image)) {
						$box_image = $box_no_image;
					} elseif (!image_exists($box_image)) {
						$box_image = $box_no_image;
					} else {
						$box_image_exists = true;
					}
				}

				if ($box_image) {
					if (preg_match("/^http\:\/\//", $box_image)) {
						$image_size = "";
					} else {
						$image_size = @GetImageSize($box_image);
						if ($box_image_exists && ($watermark || $restrict_products_images)) {
							$item_image = "image_show.php?item_id=".$item_id."&type=".$box_image_type_name."&vc=".md5($box_image);
						}
					}
					if (!strlen($box_image_alt)) { $box_image_alt= $item_name; }
					$t->set_var("alt", htmlspecialchars($box_image_alt));
					$t->set_var("src", htmlspecialchars($box_image));
					if (is_array($image_size)) {
						$t->set_var("width", "width=\"" . $image_size[0] . "\"");
						$t->set_var("height", "height=\"" . $image_size[1] . "\"");
					} else {
						$t->set_var("width", "");
						$t->set_var("height", "");
					}
					$t->sparse("box_image", false);
				} else {
					$t->set_var("box_image", "");
				}
				
				$box_desc = "";
				if ($box_desc_field) {
					$box_desc = get_translation($db->f($box_desc_field));
				}
				$t->set_var("box_desc", $box_desc);
  
				$t->parse("so_boxes", true);
			}


			$is_next_record = $db->next_record();
			$column_index = ($so_number % $so_columns) ? ($so_number % $so_columns) : $so_columns;
			$t->set_var("column_class", "col-".$column_index);

			$t->parse("items_cols");
			if($so_number % $so_columns == 0) {
				$t->parse("items_rows");
				$t->set_var("items_cols", "");
			}
		} while ($is_next_record);

		$t->set_var("items_indexes", implode(",", $items_indexes));
		$t->set_var("start_index", $start_index);
	}

	if ($so_number % $so_columns != 0) {
		$t->parse("items_rows");
	}

	$block_parsed = true;

	// check if we need to parse hidden block for wishlist types
	if ($user_id && $bn_wish) {
		include_once("./blocks/block_wishlist_types.php");
	}

?>