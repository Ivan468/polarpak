<?php

	include_once("./includes/products_functions.php");

	$default_title = "{MANUFACTURERS_TITLE}";

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	
	$manufacturers_selection = get_setting_value($vars, "manufacturers_selection", 1);
	$manufacturers_image     = get_setting_value($vars, "manufacturers_image", 1);
	$manufacturers_desc      = get_setting_value($vars, "manufacturers_desc", 1);
	$manufacturers_order     = get_setting_value($vars, "manufacturers_order", 1);
	$manufacturers_direction = get_setting_value($vars, "manufacturers_direction", 1);
	
	$manf = get_param("manf");
	$category_id = 0;
	// check category_id parameter only for product pages
	if ($cms_page_code == "products_list" || $cms_page_code == "product_details" 
		|| $cms_page_code == "product_options" || $cms_page_code == "product_reviews") {
		$category_id = get_param("category_id");
		$search_category_id = get_param("search_category_id");
		if ($search_category_id) { $category_id = $search_category_id; }
	}

	$html_template = get_setting_value($block, "html_template", "block_manufacturers.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("products_href", get_custom_friendly_url("products_list.php"));
	$t->set_var("category_id", htmlspecialchars($category_id));
	// clear block vars
	$t->set_var("manufacturers", "");
	$t->set_var("manufacturers_options", "");	
	$t->set_var("manufacturers_select", "");
	$t->set_var("manufacturers_list", "");


	$list_page = get_custom_friendly_url("products_list.php");
	$manf_url = new VA_URL($list_page);
	$manf_url->add_parameter("category_id", CONSTANT, $category_id);

	$search_tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "categories", "tree", TOP_CATEGORY_MSG);
	
	$sql_fields = "";
	if ($manufacturers_selection == 1) {		
		if ($manufacturers_desc == 1) {
			$sql_fields .= ", m.short_description AS description ";
		} elseif ($manufacturers_desc == 2) {
			$sql_fields .= ", m.full_description AS description ";
		}
		if ($manufacturers_image == 2) {
			$sql_fields .= ", m.image_small_alt AS image_alt, m.image_small AS image ";
		} elseif ($manufacturers_image == 3) {
			$sql_fields .= ", m.image_large_alt AS image_alt, m.image_large AS image  ";
		}			
	}
	
	$manufacturers = array();

	// 1. Build items SQL query
	$sql_params = array();
	$sql_params["select"] = " i.item_id, i.manufacturer_id ";		
	$sql_params["join"][] = " INNER JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id ";		
	if ($category_id > 0) {
		$sql_params["join"][] = " INNER JOIN " . $table_prefix . "items_categories ic ON i.item_id=ic.item_id ";		
		$sql_params["join"][] = " INNER JOIN " . $table_prefix . "categories c ON c.category_id = ic.category_id ";
		$sql_params["where"]     = " (c.category_id=" . $db->tosql($category_id, INTEGER);
		$sql_params["where"]    .= " OR c.category_path LIKE '%" . $db->tosql($search_tree->get_path($category_id), TEXT, false) . "%')";
	}
	$sql_params["group"] = "i.item_id, i.manufacturer_id ";	
	$items_sql = VA_Products::_sql($sql_params, VIEW_ITEMS_PERM);

	// 2. Build manufacturers SQL query and JOIN items SQL query
	$sql  = " SELECT m.manufacturer_id, m.manufacturer_order, m.friendly_url, m.manufacturer_name, mc.manufacturer_products ";
	$sql .= $sql_fields;
	$sql .= " FROM " . $table_prefix . "manufacturers m ";
	$sql .= " INNER JOIN ( ";
	$sql .= " SELECT ms.manufacturer_id, COUNT(*) AS manufacturer_products ";
	$sql .= " FROM " . $table_prefix . "manufacturers ms ";
	$sql .= " INNER JOIN (" . $items_sql . ") im ON im.manufacturer_id=ms.manufacturer_id ";
	$sql .= " GROUP BY ms.manufacturer_id) mc ON m.manufacturer_id=mc.manufacturer_id ";
	if ($manufacturers_order == 2) {
		$sql .= "ORDER BY m.manufacturer_order ";		
	} else {
		$sql .= "ORDER BY m.manufacturer_name ";		
	}
	if ($manufacturers_direction == 2) {
		$sql .= " DESC ";		
	} else {
		$sql .= " ASC ";		
	}
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$manufacturer_id = $db->f("manufacturer_id");
			$manufacturer_name = get_translation($db->f("manufacturer_name"));
			$friendly_url = $db->f("friendly_url");
			$manufacturer_products = $db->f("manufacturer_products");
			$description = get_translation($db->f("description"));
			$image_alt = get_translation($db->f("image_alt"));
			$image = get_translation($db->f("image"));

			if ($friendly_urls && $friendly_url) {
				$manf_url->remove_parameter("manf");
				$manufacturer_href = $manf_url->get_url($friendly_url. $friendly_extension);
			} else {
				$manf_url->add_parameter("manf", CONSTANT, $manufacturer_id);
				$manufacturer_href = $manf_url->get_url($list_page);
			}
	  
			$manufacturer_selected = ($manf == $manufacturer_id) ? "selected" : "";
	  
			$t->set_var("manufacturer_id", $manufacturer_id);
			$t->set_var("manufacturer_name", $manufacturer_name);
			$t->set_var("manufacturer_href", $manufacturer_href);
			$t->set_var("manufacturer_selected", $manufacturer_selected);
			$t->set_var("manufacturer_products", $manufacturer_products);
				
			if ($description) {
				$t->set_var("desc_text", $description);
				$t->sparse("desc", false);
			} else {
				$t->set_var("desc", "");
			}
			if ($image) {
				if (preg_match("/^http\:\/\//", $image)) {
					$image_size = "";
				} else {
					$image_size = @GetImageSize($image);					
				}
				if(is_array($image_size)) {
					$t->set_var("width", "width=\"" . $image_size[0] . "\"");
					$t->set_var("height", "height=\"" . $image_size[1] . "\"");
				} else {
					$t->set_var("width", "");
					$t->set_var("height", "");
				}
				$t->set_var("alt", $image_alt);
				$t->set_var("src", $image);
				$t->sparse("image", false);
			} else {
				$t->set_var("image", "");
			}			
			$t->sparse("manufacturers", true);
			$t->sparse("manufacturers_options", true);	

		} while ($db->next_record());

		if ($manufacturers_selection == 2) {
			$t->sparse("manufacturers_select", false);
		} else {
			$t->sparse("manufacturers_list", false);
		}

		$block_parsed = true;
	} else {
		$block_parsed = false;
	}

?>