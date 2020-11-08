<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  call_center_products.php                                 ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./messages/".$language_code."/cart_messages.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");

	check_admin_security("create_orders");

	$sw = trim(get_param("sw"));
	$form_id = get_param("form_id");
	$form_name = get_param("form_name");
	$field_name = get_param("field_name");
	$id_name = get_param("id_name");
	$selection_type = get_param("selection_type");

  $t = new VA_Template($settings["templates_dir"]);
  $t->set_file("main","call_center_products.html");
	$t->set_var("call_center_products_href", "call_center_products.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_id", htmlspecialchars($form_id));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("field_name", htmlspecialchars($field_name));
	$t->set_var("id_name", htmlspecialchars($id_name));
	$t->set_var("selection_type", htmlspecialchars($selection_type));
	// set necessary scripts
	//set_script_tag("js/shopping.js");
	//set_script_tag("js/ajax.js");
	//set_script_tag("js/blocks.js");

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", "call_center_products.php");
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
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", "call_center_products.php");
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$item_index = 0;
	$items_indexes = array();

	$sql  = " SELECT item_id, item_name, item_code, manufacturer_code, is_price_edit, price, is_sales, sales_price ";
	$sql .= "	FROM " . $table_prefix . "items ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("products_sorters", false);
		do {
			$item_index++;
			$items_indexes[] = $item_index;

			$item_id = $db->f("item_id");
			$item_name = get_translation($db->f("item_name"));
			$item_code = $db->f("item_code");
			$manufacturer_code = $db->f("manufacturer_code");
			$is_price_edit = $db->f("is_price_edit");
			$price = $db->f("price");
			$is_sales = $db->f("is_sales");
			$sales_price = $db->f("sales_price");
			if ($is_sales && $sales_price > 0) {
				$price = $sales_price;
			}
			$item_name_js = str_replace("'", "\\'", htmlspecialchars($item_name));

			if(is_array($sa)) {
				for($si = 0; $si < sizeof($sa); $si++) {
					$item_code = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $item_code);					
					$item_name = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $item_name);					
					$manufacturer_code = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $manufacturer_code);
				}
			}

			$t->set_var("item_id", $item_id);
			$t->set_var("item_index", $item_index);

			$t->set_var("item_name", $item_name);
			$t->set_var("item_name_js", $item_name_js);

			$t->set_var("item_code", $item_code);
			$t->set_var("manufacturer_code", $manufacturer_code);
			if ($is_price_edit) {
				$t->set_var("price", "<input type=\"text\" size=\"5\" name=\"price".$item_index."\" value=\"".$price."\"/>");
			} else {
				$t->set_var("price", currency_format($price));
			}
			$t->set_var("price_js", number_format($price, 2, 	".", ""));

			$t->parse("products", true);
		} while ($db->next_record());
	}

	$t->set_var("items_indexes", implode(",", $items_indexes));

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_PRODUCTS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("search_results", false);
	}

	$t->pparse("main");


?>