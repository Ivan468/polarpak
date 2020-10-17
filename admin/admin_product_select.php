<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_product_select.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/sorter.php");
	include_once ($root_folder_path . "includes/navigator.php");
	include_once ("../messages/".$language_code."/cart_messages.php");

	include_once("./admin_common.php");

	check_admin_security("products_categories");

	$site_url = get_setting_value($settings, "site_url", "");
	$parsed_url = parse_url($site_url);
	$domain_url = $parsed_url["scheme"]."://".$parsed_url["host"]."/";

	$sw = trim(get_param("sw"));
	$js_type = get_param("js_type");
	$form_id = get_param("form_id");
	$form_name = get_param("form_name");
	$field_name = get_param("field_name");
	$id_name = get_param("id_name");
	$selection_type = get_param("selection_type");
	$items_field = get_param("items_field");
	$item_fields = get_param("item_fields");
	$items_object = get_param("items_object");
	$item_template = get_param("item_template");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_product_select.html");
	$t->set_var("admin_product_select_href", "admin_product_select.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("js_type", htmlspecialchars($js_type));
	$t->set_var("form_id", htmlspecialchars($form_id));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("field_name", htmlspecialchars($field_name));
	$t->set_var("id_name", htmlspecialchars($id_name));
	$t->set_var("selection_type", htmlspecialchars($selection_type));
	$t->set_var("item_fields", htmlspecialchars($item_fields));
	$t->set_var("items_field", htmlspecialchars($items_field));
	$t->set_var("items_object", htmlspecialchars($items_object));
	$t->set_var("item_template", htmlspecialchars($item_template));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_product_select.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_item_id", "1", "item_id", "", "", true);
	$s->set_sorter(PROD_NAME_MSG, "sorter_item_name", "2", "item_name");
	$s->set_sorter(PROD_CODE_MSG, "sorter_item_code", "3", "item_code");
	$s->set_sorter(MANUFACTURER_CODE_MSG, "sorter_manufacturer_code", "4", "manufacturer_code");
	$s->set_sorter(PRICE_MSG, "sorter_price", "5", "price");

	$where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			if ($where) { $where .= " AND "; }
			else { $where .= " WHERE "; }
			$where .= " (item_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR item_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR manufacturer_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "items " . $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	// set up variables for navigator
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_product_select.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$sql  = " SELECT item_id, item_type_id, item_name, item_code, manufacturer_code, ";
	$sql .= " buying_price, price, is_sales, sales_price, ";
	$sql .= " supplier_id, min_quantity, packages_number, weight, actual_weight, ";
	$sql .= " width, height, length, is_shipping_free, shipping_cost, ";
	$sql .= " tiny_image, small_image, big_image, super_image ";
	$sql .= "	FROM " . $table_prefix . "items ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("products_sorters", false);
		do {
			$item_id = $db->f("item_id");
			$item_type_id = $db->f("item_type_id");
			$item_name = $db->f("item_name");
			$item_code = $db->f("item_code");
			$tiny_image = $db->f("tiny_image");
			$small_image = $db->f("small_image");
			$large_image = $db->f("big_image");
			$super_image = $db->f("super_image");
			$manufacturer_code = $db->f("manufacturer_code");
			// pricing data
			$buying_price = doubleval($db->f("buying_price"));
			$base_price = doubleval($db->f("price"));
			$is_sales = $db->f("is_sales");
			$sales_price = doubleval($db->f("sales_price"));
			$price = ($is_sales && $sales_price > 0) ? $sales_price : $base_price;
			$discount_amount = $base_price - $price;
			$item_name_js = json_encode($item_name, JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
			// quanity and shipping data
			$supplier_id = $db->f("supplier_id");
			$quantity = $db->f("min_quantity");
			if ($quantity < 1) { $quantity = 1; }
			$packages_number = $db->f("packages_number");
			$weight = $db->f("weight");
			$actual_weight = $db->f("actual_weight");
			$width = $db->f("width");
			$height = $db->f("height");
			$length = $db->f("length");
			$is_shipping_free = $db->f("is_shipping_free");
			$shipping_cost = doubleval($db->f("shipping_cost"));


			$item_name_html = $item_name;
			$item_code_html = $item_code;
			$manufacturer_code_html = $manufacturer_code;
			if(is_array($sa)) {
				for($si = 0; $si < sizeof($sa); $si++) {
					$item_code_html = preg_replace ("/(" . preg_quote($sa[$si], "/") . ")/i", "<font color=blue><b>\\1</b></font>", $item_code_html);					
					$item_name_html = preg_replace ("/(" . preg_quote($sa[$si], "/") . ")/i", "<font color=blue><b>\\1</b></font>", $item_name_html);					
					$manufacturer_code_html = preg_replace ("/(" . preg_quote($sa[$si], "/") . ")/i", "<font color=blue><b>\\1</b></font>", $manufacturer_code_html);
				}
			}

			$t->set_var("item_id", $item_id);
			$t->set_var("item_name", $item_name_html);
			$t->set_var("item_code", $item_code_html);
			$t->set_var("manufacturer_code", $manufacturer_code_html);
			$t->set_var("price", currency_format($price));
			$t->set_var("price_js", number_format($price, 2, 	".", ""));

			$price_js = number_format($price, 2, 	".", "");

			if (preg_match("/^\//", $tiny_image)) {
				$tiny_image = $domain_url.$tiny_image;
			} else if ($tiny_image && !preg_match("/^http/i", $tiny_image)) {
				$tiny_image = $site_url.$tiny_image;
			}
			if (preg_match("/^\//", $small_image)) {
				$small_image = $domain_url.$small_image;
			} else if ($small_image && !preg_match("/^http/i", $small_image)) {
				$small_image = $site_url.$small_image;
			}
			if (preg_match("/^\//", $large_image)) {
				$large_image = $domain_url.$large_image;
			} else if ($large_image && !preg_match("/^http/i", $large_image)) {
				$large_image = $site_url.$large_image;
			}
			if (preg_match("/^\//", $super_image)) {
				$super_image = $domain_url.$super_image;
			} else if ($super_image && !preg_match("/^http/i", $super_image)) {
				$super_image = $site_url.$super_image;
			}
			$image_src = $tiny_image ? $tiny_image : $small_image;
			if (!$image_src) { $image_src = $site_url . "images/tr.gif"; }

			if ($js_type || $item_template) {
				// new improved JSON method
				$item_params = array(
					"id" => $item_id, 
					"item_id" => $item_id, 
					"item_type_id" => $item_type_id, 
					"item_name" => strip_tags($item_name), 
					"item_code" => $item_code, 
					"manufacturer_code" => $manufacturer_code, 
					"buying_price" => currency_format($buying_price), 
					"item_price" => currency_format($price), 
					"base_price" => currency_format($base_price), 
					"price" => currency_format($price), 
					"discount_price" => currency_format($discount_amount), 
					"buying_value" => ($buying_price), 
					"item_value" => ($price), 
					"base_value" => ($base_price), 
					"price_value" => ($price), 
					"discount_value" => ($discount_amount), 
					"image_src" => ($image_src), 
					"tiny_image" => ($tiny_image), 
					"small_image" => ($small_image), 
					"large_image" => ($large_image), 

					"supplier_id" => ($supplier_id), 
					"quantity" => ($quantity), 
					"packages_number" => ($packages_number), 
					"weight" => ($weight), 
					"actual_weight" => ($actual_weight), 
					"width" => ($width), 
					"height" => ($height), 
					"length" => ($length), 
					"is_shipping_free" => ($is_shipping_free), 
					"shipping_cost" => ($shipping_cost), 
				);
				$params = array(
					"form_name" => $form_name,
					"item_fields" => $item_fields,
					"items_field" => $items_field,
					"items_object" => $items_object,
					"item_template" => $item_template,
					"item" => $item_params,
				);
				$json_params = json_encode($params, JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);

				$t->set_var("onclick", "jsonSelectItem(".$json_params.");");
			} else {
				// old JS method
				$t->set_var("onclick", "selectProduct(\"$item_id\", $item_name_js, \"$price_js\");");
			}

			$t->parse("products", true);
		} while ($db->next_record());
	}

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_PRODUCTS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("search_results", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");


?>