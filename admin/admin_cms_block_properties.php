<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_cms_block_properties.php                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path."includes/sorter.php");
	include_once($root_folder_path."includes/navigator.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/download_messages.php");

	include_once("./admin_common.php");

	check_admin_security("cms_settings");

	// begin delete selected properties
	$operation = get_param("operation");
	$block_id = get_param("block_id");

	// check if block exists so we can add/update options
	$sql  = " SELECT block_name FROM " . $table_prefix . "cms_blocks ";
	$sql .= " WHERE block_id=" . $db->tosql($block_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$block_name = $db->f("block_name");
	} else {
		header("Location: admin_cms_blocks.php");
		exit;
	}

	// update and remove operations
	$properties_ids = get_param("properties_ids");
	if (strlen($operation)) {
		if ($properties_ids) {
			if ($operation == "remove_properties") {
				$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_properties ";
				$sql .= " WHERE property_id IN (" . $db->tosql($properties_ids, INTEGERS_LIST) . ")";
				$db->query($sql);
				$sql  = " DELETE FROM " . $table_prefix . "cms_blocks_values ";
				$sql .= " WHERE property_id IN (" . $db->tosql($properties_ids, INTEGERS_LIST) . ")";
				$db->query($sql);
			}
		}
	}
	// end operations

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_cms_block_properties.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_cms_block_properties_href", "admin_cms_block_properties.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("block_id", htmlspecialchars($block_id));


	$admin_cms_block_property_url = new VA_URL("admin_cms_block_property.php", false);
	$admin_cms_block_property_url->add_parameter("page", REQUEST, "page");
	$admin_cms_block_property_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$admin_cms_block_property_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$admin_cms_block_property_url->add_parameter("op", REQUEST, "op");
	$admin_cms_block_property_url->add_parameter("os_ord", REQUEST, "os_ord");
	$admin_cms_block_property_url->add_parameter("os_dir", REQUEST, "os_dir");
	$admin_cms_block_property_url->add_parameter("block_id", REQUEST, "block_id");
	$t->set_var("admin_cms_block_property_new_url", $admin_cms_block_property_url->get_url());

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_cms_block_properties.php", "os");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_property_id", "1", "cbp.property_id", "", "", true);
	$s->set_sorter(NAME_MSG, "sorter_property_name", "2", "cbp.property_name");
	$s->set_sorter(SORT_ORDER_MSG, "sorter_property_order", "3", "cbp.property_order");
	$s->set_sorter(CONTROL_TYPE_MSG, "sorter_control_type", "4", "cbp.control_type");

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_cms_block_properties.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "cms_blocks_properties cmb ";
	$sql .= " WHERE block_id=" . $db->tosql($block_id, INTEGER);
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "op", SIMPLE, $pages_number, $records_per_page, $total_records, false);
	
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT * FROM " . $table_prefix . "cms_blocks_properties cbp ";
	$sql .= " WHERE cbp.block_id=" . $db->tosql($block_id, INTEGER);
	$sql .= $s->order_by;
	$db->query($sql);
	$property_index = 0;
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		$admin_cms_block_property_url->add_parameter("property_id", DB, "property_id");
	
		do
		{
			$property_index++;
			$t->set_var("property_index", $property_index);
			$property_id = $db->f("property_id");
			$property_order = $db->f("property_order");
			$control_type = $db->f("control_type");
			$property_name = get_translation($db->f("property_name"));
			parse_value($property_name);

			$t->set_var("admin_cms_block_property_url", $admin_cms_block_property_url->get_url());

			$row_style = ($property_index % 2 == 0) ? "row1" : "row1";
			$t->set_var("row_style", $row_style);

			$t->set_var("property_id", $property_id);
			$t->set_var("property_order", $property_order);
			$t->set_var("control_type", $control_type);
			$t->set_var("property_name", htmlspecialchars($property_name));

			$t->parse("records", true);
		} while($db->next_record());

		$t->parse("remove_button", false);
		$t->set_var("properties_index", $property_index);
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->set_var("admin_href", "admin.php");
	$t->pparse("main");
?>
