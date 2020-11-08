<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_slider_items.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	include_once("./admin_common.php");

	check_admin_security("sliders");
	$operation = get_param("operation");
	$item_id = get_param("item_id");
	$slider_id = get_param("slider_id");
	if (strlen($operation) && $item_id) {
		if (strtolower($operation) == "off") {
			$sql  = " UPDATE " . $table_prefix . "sliders_items SET show_for_user=0 ";
			$sql .= " WHERE item_id= " . $db->tosql($item_id, INTEGER);
			$db->query($sql);
		} elseif (strtolower($operation) == "on") {
			$sql  = " UPDATE " . $table_prefix . "sliders_items SET show_for_user=1 ";
			$sql .= " WHERE item_id= " . $db->tosql($item_id, INTEGER);
			$db->query($sql);
		}
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_slider_items.html");

	// Get slider_id and check if it exists
	$sql  = " SELECT * ";
	$sql .= " FROM ".$table_prefix."sliders ";
	$sql .= " WHERE slider_id = ".$db->tosql($slider_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$t->set_var("slider_id", $slider_id);
	} else {
		header("Location: admin_sliders.php");
		exit;
	}

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_layout_href", "admin_layout.php");
	$t->set_var("admin_item_edit", "admin_slider_item_edit.php?slider_id=".$slider_id);
	$t->set_var("admin_slider_href", "admin_slider.php");
	$t->set_var("admin_sliders_href", "admin_sliders.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	
	$sliders_items_tbl_name = $table_prefix . "sliders_items";
	
	$sql = " SELECT item_id, item_order, item_name, show_for_user FROM ";
	$sql .= $sliders_items_tbl_name." ";
	$sql .= "WHERE slider_id=" . $db->tosql($slider_id, INTEGER);
	$sql .= " ORDER BY item_order ";
	$db->query($sql);
	$sliders = array();
	$slider_count = 0;
	while($db->next_record()) {
		$slider_count++;
		$t->set_var("item_id", $db->f("item_id"));
		$t->set_var("item_order", $db->f("item_order"));
		$t->set_var("item_name", $db->f("item_name"));
		$is_shown = $db->f("show_for_user");
		if ($is_shown == 0) {
			$t->set_var("show_for_user", "No");
			$t->set_var("operation", "on");
		}
		else {
			$t->set_var("show_for_user", "Yes");
			$t->set_var("operation", "off");
		}
		$t->parse("records", true);
	}


	if (empty($slider_count)) {
		$t->parse("no_records", false);
	}

	$t->pparse("main");

?>