<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_slider_item_edit.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/record.php");

	include_once("./admin_common.php");

	check_admin_security("sliders");

	$slider_id = get_param("slider_id");
	$item_id = get_param("item_id");
	$return_page = get_param("return_page");
	
	if ($return_page == "") {
		$return_page = "admin_layouts.php";
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_slider_item_edit.html");
	
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	
	// check slider id
	$sql  = " SELECT * ";
	$sql .= " FROM ".$table_prefix."sliders ";
	$sql .= " WHERE slider_id = ".$db->tosql($slider_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$t->set_var("slider_name", $db->f("slider_name"));
		$t->set_var("slider_title", $db->f("slider_title"));
		$t->set_var("slider_id", $slider_id);
	} else {
		header("Location: admin_sliders.php");
		exit;
	}

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_layout_href", "admin_layout.php");
	$t->set_var("admin_slider_item_edit_href" , "admin_slider_item_edit.php");
	$t->set_var("admin_slider_href"  , "admin_slider_items.php?slider_id=".$slider_id);
	$t->set_var("admin_slider_href", "admin_slider.php");
	$t->set_var("admin_sliders_href", "admin_sliders.php");
	$t->set_var("admin_slider_items_href", "admin_slider_items.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", SLIDER_MSG, CONFIRM_DELETE_MSG));

	$r = new VA_Record($table_prefix . "sliders_items");
	$r->return_page = "admin_slider_items.php?slider_id=" . $slider_id;
	
	$r->add_where("item_id", INTEGER);
	$r->add_textbox("slider_id", INTEGER, ID_MSG);
	$r->add_checkbox("show_for_user", INTEGER);
	$r->change_property("show_for_user", DEFAULT_VALUE, 1);
	$r->add_textbox("item_order", INTEGER, ADMIN_ORDER_MSG);
	$r->change_property("item_order", REQUIRED, true);
	$r->add_textbox("item_name", TEXT, NAME_MSG);
	$r->change_property("item_name", REQUIRED, true);
	$r->add_textbox("slider_image", TEXT);
	$r->add_textbox("slider_link", TEXT);
	$r->add_textbox("slider_html", TEXT);
	$slider = array();

	$items = array();
	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("slider_id", $slider_id);
	$t->pparse("main");

?>