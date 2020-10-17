<?php

	$default_title = "{COMPARE_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_products_compare.html"); 
  $t->set_file("block_body", $html_template);

	$t->set_var("product_details_href", "product_details.php");

	$tax_rates = get_tax_rates();
	$price_type = get_session("session_price_type");
	$compare_cart = get_session("compare_cart");
	$items_number = is_array($compare_cart) ? count($compare_cart) : 0;

	$errors = "";
	if ($items_number == 0)	{
		$errors = NO_PRODUCTS_MSG;
	}

	$user_info = get_session("session_user_info");
	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	$product_no_image = get_setting_value($settings, "product_no_image", "");
	$watermark = get_setting_value($settings, "watermark_small_image", 0);
	$image_type_name = "small";

	$features = array();
	$items = get_param("items");
	$t->set_var("items_html", htmlspecialchars($items));
	$t->set_var("items_url", urlencode($items));

	// get images and prices
	if (!strlen($errors)) {
		foreach ($compare_cart as $compare_id => $compare_data) {
			$item_id = $compare_data["ITEM_ID"];
			$item_name = $compare_data["ITEM_NAME"];
			$sql  = " SELECT * ";
			$sql .= " FROM " . $table_prefix . "items i ";
			$sql .= " WHERE i.item_id=" . $db->tosql($item_id, INTEGER);
			$db->query($sql);
			while ($db->next_record()) {
				$item_id = $db->f("item_id");
				$a_title = get_translation($db->f("a_title"));
				$image_src = $db->f("small_image");
				$image_alt = get_translation($db->f("small_image_alt"));
				if (!strlen($image_alt)) { $image_alt = $item_name; }
				if (strlen($image_src)) { 
					if (!preg_match("/^http\:\/\//", $image_src) && ($watermark || $restrict_products_images)) {
						$image_src = "image_show.php?item_id=".$item_id."&type=small&vc=".md5($image_src);
					}
				} else {
					$image_src = $product_no_image; 
				}
    
				$compare_cart[$compare_id]["A_TITLE"]= $a_title;
				$compare_cart[$compare_id]["IMAGE_SRC"] = $image_src;
				$compare_cart[$compare_id]["IMAGE_ALT"] = $image_alt;
			}
		}
	}


	if (!strlen($errors)) 
	{
		foreach ($compare_cart as $compare_id => $compare_data) {
			$item_id = $compare_data["ITEM_ID"];
			$item_type_id = $compare_data["ITEM_TYPE_ID"];
			$item_name = $compare_data["ITEM_NAME"];
			$properties = $compare_data["PROPERTIES_INFO"];
  
			// get all properties
			$sql  = " SELECT ip.property_name, ip.property_description ";
			$sql .= " FROM (" . $table_prefix . "items_properties ip ";
			$sql .= " LEFT JOIN " . $table_prefix . "items_properties_sites ips ON ip.property_id=ips.property_id) ";
			$sql .= " WHERE (ip.item_id=" . intval($item_id) . " OR ip.item_type_id=" . $db->tosql($item_type_id, INTEGER) . ") ";
			if (isset($site_id)) {
				$sql .= " AND (ip.sites_all=1 OR ips.site_id=" . $db->tosql($site_id, INTEGER) . ")";
			} else {
				$sql .= " AND ip.sites_all=1 ";
			}
			$sql .= " AND ip.property_type_id=2 ";
			$sql .= " ORDER BY ip.property_order, ip.property_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$group_id = "components";
				$group_name = PROD_SUBCOMPONENTS_MSG;
				$feature_name = get_translation($db->f("property_name"));
				$feature_value = YES_MSG;

				$feature_groups[$group_id] = $group_name;
				if (isset($features[$group_id][$feature_name][$compare_id])) {
					$features[$group_id][$feature_name][$compare_id] .= "; " . $feature_value;
				} else {
					$features[$group_id][$feature_name][$compare_id] = $feature_value;
				}
			}

			// add selected options
			if (is_array($properties)) {
				foreach ($properties as $property_id => $property_data) {
					$group_id = "options";
					$group_name = PROD_OPTIONS_MSG;
					$feature_name = get_translation($property_data["NAME"]);
					$property_values = $property_data["VALUES_INFO"];
		  
					$feature_groups[$group_id] = $group_name;
					foreach ($property_values as $value_id => $value_data) {
				  	$feature_value = $value_data["DESC"];
						if (isset($features[$group_id][$feature_name][$compare_id])) {
							$features[$group_id][$feature_name][$compare_id] .= "; " . $feature_value;
						} else {
							$features[$group_id][$feature_name][$compare_id] = $feature_value;
						}
					}
				}
			}

  
			// get features list
			$sql  = " SELECT fg.group_id,fg.group_name,f.feature_name,f.feature_value ";
			$sql .= " FROM " . $table_prefix . "features f, " . $table_prefix . "features_groups fg ";
			$sql .= " WHERE f.group_id=fg.group_id ";
			$sql .= " AND f.item_id=" . intval($item_id);
			$sql .= " ORDER BY fg.group_order ";
			$db->query($sql);
			while ($db->next_record()) {
				$group_id = $db->f("group_id");
				$group_name = get_translation($db->f("group_name"));
				$feature_name = get_translation($db->f("feature_name"));
				$feature_value = get_translation($db->f("feature_value"));
				$feature_groups[$group_id] = $group_name;
				if (isset($features[$group_id][$feature_name][$compare_id])) {
					$features[$group_id][$feature_name][$compare_id] .= "; " . $feature_value;
				} else {
					$features[$group_id][$feature_name][$compare_id] = $feature_value;
				}
			}
		}

		$column_width = intval(80 / $items_number);
		show_title();

		$t->set_var("column_width", $column_width . "%");
		$t->set_var("colspan", ($items_number + 1));

		foreach ($features as $group_id => $group_features)
		{
			$t->set_var("features", "");
			foreach ($group_features as $feature_name => $features_values)
			{		
				$t->set_var("features_values", "");
				foreach ($compare_cart as $compare_id => $compare_data) {
					$feature_value = isset($features_values[$compare_id]) ? $features_values[$compare_id] : "";
					$t->set_var("feature_value", $feature_value);
					$t->parse("features_values", true);
				}
				$t->set_var("feature_name", $feature_name);
				$t->parse("features", true);
			}

			$t->set_var("group_name", $feature_groups[$group_id]);
			$t->parse("features_groups", true);
		}

		$t->parse("compared", true);
		$t->set_var("errors_block", "");
	} 
	else 
	{
		$t->set_var("compared", "");
		$t->set_var("errors", $errors);
		$t->parse("errors_block", true);
	}

	$block_parsed = true;


function show_title()
{
	global $t, $settings, $currency, $user_tax_free;
	global $column_width, $items_number, $compare_cart;
	global $restrict_products_images, $product_no_image, $watermark, $image_type_name;

	$discount_type = get_session("session_discount_type");
	$discount_amount = get_session("session_discount_amount");
	$display_products = get_setting_value($settings, "display_products", 0);
	$user_id = get_session("session_user_id");		
	
	foreach ($compare_cart as $compare_id => $compare_data) {
		$item_id = $compare_data["ITEM_ID"];
		$item_type_id = $compare_data["ITEM_TYPE_ID"];
		$item_name = $compare_data["ITEM_NAME"];
		$a_title = $compare_data["A_TITLE"];
		$image_src = $compare_data["IMAGE_SRC"];
		$image_alt = $compare_data["IMAGE_ALT"];
		$price = $compare_data["PRICE"];
		$properties_info = $compare_data["PROPERTIES_INFO"];
		$components = $compare_data["COMPONENTS"];				
		// calculate propeties data
		$properties_price = 0; 
		if (is_array($properties_info)) {
			foreach ($properties_info as $property_id => $property) {
				$properties_price += $property["CONTROL_PRICE"] + $property["PRICE"];
			}
		}
		$price+=$properties_price;
		// calculate components prices
		$components_price = 0; 
		if (is_array($components) && sizeof($components) > 0) {
			foreach ($components as $property_id => $component_values) {
				foreach ($component_values as $property_item_id => $component) {
					$component_price = $component["price"];
					$component_tax_id = $component["tax_id"];
					$component_tax_free = $component["tax_free"];
					if ($user_tax_free) { $component_tax_free = $user_tax_free; }
					$sub_item_id = $component["sub_item_id"];
					$sub_quantity = $component["quantity"];
					$sub_qty_action = isset($component["quantity_action"]) ? $component["quantity_action"] : 1;
					if ($sub_quantity < 1)  { $sub_quantity = 1; }
					$sub_type_id = $component["item_type_id"];
					if (!strlen($component_price)) {
						$sub_price = $component["base_price"];
						$sub_buying = $component["buying"];
						$sub_user_price = $component["user_price"];
						$sub_user_action = $component["user_price_action"];
						$sub_prices = get_product_price($sub_item_id, $sub_price, $sub_buying, 0, 0, $sub_user_price, $sub_user_action, $discount_type, $discount_amount);
						$component_price = $sub_prices["base"];
					}
					$price += $component_price * $sub_quantity;
					/*
					// check the price including the tax
					$component_tax_amount = get_tax_amount($tax_rates, $sub_type_id, $component_price, 1, $component_tax_id, $component_tax_free, $component_tax_percent); 
					if ($tax_prices_type == 1) {
						$component_price_excl_tax = $component_price - $component_tax_amount;
						$component_price_incl_tax = $component_price;
					} else {
						$component_price_excl_tax = $component_price;
						$component_price_incl_tax = $component_price + $component_tax_amount;
					}

					if ($sub_qty_action == 2) {
						$goods_excl_tax += ($component_price_excl_tax * $sub_quantity); 
						$goods_incl_tax += ($component_price_incl_tax * $sub_quantity);
						$price_excl_tax += ($component_price_excl_tax * $sub_quantity / $quantity); 
						$price_incl_tax += ($component_price_incl_tax * $sub_quantity / $quantity);
					} else {
						$goods_excl_tax += ($component_price_excl_tax * $sub_quantity * $quantity); 
						$goods_incl_tax += ($component_price_incl_tax * $sub_quantity * $quantity);
						$price_excl_tax += ($component_price_excl_tax * $sub_quantity); 
						$price_incl_tax += ($component_price_incl_tax * $sub_quantity);
					}//*/
				}
			}
		}


		$t->set_var("item_id", $item_id);
		$t->set_var("item_name", $item_name);
		$t->set_var("a_title", htmlspecialchars($a_title));
		if (strlen($image_src)) {
			$t->set_var("image_src", $image_src);
			$t->set_var("image_alt", htmlspecialchars($image_alt));
			$t->parse("image_block", false);
		} else {
			$t->set_var("image_block", "");
		}

		$t->set_var("exclude_href", "compare.php?cart=RM-COMPARE&cart_id=".urlencode($compare_id));
		$t->parse("exclude_link", false);

		if ($display_products != 2 || strlen($user_id)) {
			$t->set_var("price_value", currency_format($price));
			$t->sparse("price_block", false);
		} else {
			$t->set_var("price_block", "");
		}

		$t->parse("top_title", true);		
	}
}

?>