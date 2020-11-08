<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_properties.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("product_properties");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_properties.html");

	$t->set_var("admin_href", $admin_site_url . "admin.php");
	$t->set_var("admin_property_href", "admin_property.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_product_href", "admin_product.php");
	$t->set_var("admin_item_type_href", "admin_item_type.php");
	$t->set_var("admin_item_types_href", "admin_item_types.php");
	$t->set_var("admin_properties_href", "admin_properties.php");
	$t->set_var("admin_copy_component_selection_href", "admin_products_copy_properties.php");

	$p = get_param('page');
	if ($p) {
		$a_p = "&page=".$p;
		$t->set_var("and_page",$a_p);
		$t->set_var("p",$p);
		$p = "?page=".$p;
		$t->set_var("page",$p);
	} else {
		$t->set_var("page", "");
		$t->set_var("and_page", "");
	}

	$options = get_param('options_ids');
	$operation = get_param('operation');
	
	$item_id = get_param("item_id");
	$item_type_id = get_param("item_type_id");
	$category_id = get_param("category_id");
	
	$return_page = "admin_properties.php?category_id=".$category_id."&item_id=".$item_id;
	//echo  $return_page;
	if (strlen($operation)) {
		if ($operation == "delete"){
			$db->query("DELETE FROM " . $table_prefix . "items_properties WHERE property_id IN (" . $options.")");
			$db->query("DELETE FROM " . $table_prefix . "items_properties_values WHERE property_id IN (".$options.")");
			$db->query("DELETE FROM " . $table_prefix . "items_properties_sites WHERE property_id IN (".$options.")");
			$db->query("DELETE FROM " . $table_prefix . "items_properties_sizes WHERE property_id IN (".$options.")");
			if (strlen($item_type_id)){
				$return_page = "admin_properties.php?item_type_id=".intval($item_type_id);
			}
			header("Location: " . $return_page);
			exit;
		}
	}

	$item_id = get_param("item_id");
	$category_id = get_param("category_id");
	if(!strlen($category_id)) $category_id = "0";
	$item_type_id = get_param("item_type_id");

	$t->set_var("item_id", htmlspecialchars($item_id));
	$t->set_var("item_type_id", htmlspecialchars($item_type_id));
	$t->set_var("category_id", htmlspecialchars($category_id));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_properties.php");
	$s->set_default_sorting(3, "asc");
	$s->set_sorter(ID_MSG, "sorter_property_id", "1", "property_id");
	$s->set_sorter(NAME_MSG, "sorter_property_name", "2", "property_name");
	$s->set_sorter(ADMIN_ORDER_MSG, "sorter_property_order", "3", "property_order");
	$s->set_sorter(TYPE_MSG, "sorter_property_type_id", "4", "property_type_id");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_properties.php");

	if ($item_type_id > 0) {
		$sql  = " SELECT item_type_name FROM " . $table_prefix . "item_types ";
		$sql .= " WHERE item_type_id=" . $db->tosql($item_type_id, INTEGER);
		$db->query($sql);
		if($db->next_record()) {
			$t->set_var("item_type_name", get_translation($db->f("item_type_name")));
		} else {
			die(str_replace("{item_type_id}", $item_id, PROD_TYPE_ID_NO_LONGER_EXISTS_MSG));
		}

		//$t->parse("type_path");
	} else {
		$sql  = " SELECT item_name FROM " . $table_prefix . "items ";
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$t->set_var("item_name", get_translation($db->f("item_name")));
		} else {
			die(str_replace("{item_id}", $item_id, PRODUCT_ID_NO_LONGER_EXISTS_MSG));
		}
	}



	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_properties ";
	if ($item_type_id > 0) {
		$sql .= " WHERE item_type_id=" . $db->tosql($item_type_id, INTEGER);
	} else {
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
	}

	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 20;
	$pages_number = 10;

	$admin_property_url = new VA_URL("admin_property.php", true);
	$t->set_var("admin_property_new_url", $admin_property_url->get_url());

	$admin_component_single_url = new VA_URL("admin_component_single.php", true);
	$t->set_var("admin_component_single_url", $admin_component_single_url->get_url());

	$admin_component_selection_url = new VA_URL("admin_component_selection.php", true);
	$t->set_var("admin_component_selection_url", $admin_component_selection_url->get_url());

	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT ip.property_id, ip.property_name, ip.property_order, ip.property_type_id, ip.property_code, ";
	$sql .= " i.item_code, i.manufacturer_code ";
	$sql .= " FROM " . $table_prefix . "items_properties ip ";
	$sql .= " LEFT JOIN " . $table_prefix . "items i ON ip.sub_item_id=i.item_id ";
	if ($item_type_id > 0) {
		$sql .= " WHERE ip.item_type_id=" . $db->tosql($item_type_id, INTEGER);
	} else {
		$sql .= " WHERE ip.item_id=" . $db->tosql($item_id, INTEGER);
	}
	$sql .= $s->order_by;
	$db->query($sql);
	if($db->next_record())
	{
		$i = 0;
		$admin_property_url->add_parameter("property_id", DB, "property_id");
		$admin_component_single_url->add_parameter("property_id", DB, "property_id");
		$admin_component_selection_url->add_parameter("property_id", DB, "property_id");
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do
		{
			$i++;
			$property_id = $db->f("property_id");
			$property_type_id = $db->f("property_type_id");
			$property_order = $db->f("property_order");
			$property_name = get_translation($db->f("property_name"));
			$property_codes = array();
			$property_code = $db->f("property_code");
			$item_code = $db->f("item_code");
			$manufacturer_code = $db->f("manufacturer_code");
			if (strlen($property_code)) { $property_codes[] = $property_code; }
			if (strlen($item_code)) { $property_codes[] = $item_code; }
			if (strlen($manufacturer_code)) { $property_codes[] = $manufacturer_code; }

			if ($property_type_id == "3") {
				$property_type = SUBCOMPONENT_SELECTION_MSG;
				$admin_property_edit_url = $admin_component_selection_url->get_url();
			} else if ($property_type_id == "2") {
				$property_type = SUBCOMPONENT_MSG;
				$admin_property_edit_url = $admin_component_single_url->get_url();
			} else {
				$property_type = OPTION_MSG;
				$admin_property_edit_url = $admin_property_url->get_url();
			}

			$t->set_var("onpage_id", $i);
			$t->set_var("property_id", $property_id);
			$t->set_var("property_type", $property_type);
			$t->set_var("property_order", $property_order);
			$t->set_var("property_name", htmlspecialchars($db->f("property_name")));
			$t->set_var("property_code", htmlspecialchars(implode(" / ", $property_codes)));

			$t->set_var("admin_property_edit_url", $admin_property_edit_url);

			$t->parse("records", true);
		} while($db->next_record());
		$t->set_var("onpage", $i);
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>