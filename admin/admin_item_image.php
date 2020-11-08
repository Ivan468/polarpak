<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_item_image.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once("./admin_common.php");

	check_admin_security("products_categories");

	$item_id = get_param("item_id");
	$category_id = get_param("category_id");
	$sql  = " SELECT item_name FROM " . $table_prefix . "items ";
	$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$item_name = get_translation($db->f("item_name"));
	} else {
		die(str_replace("{item_id}", $item_id, PRODUCT_ID_NO_LONGER_EXISTS_MSG));
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_item_image.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_product_href", "admin_product.php");
	$t->set_var("admin_item_image_href", "admin_item_image.php");
	$t->set_var("admin_item_images_href", "admin_item_images.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", IMAGE_MSG, CONFIRM_DELETE_MSG));

	$t->set_var("item_id", $item_id);
	$t->set_var("category_id", $category_id);
	$t->set_var("item_name", $item_name);

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("image_site_url", $site_url);					
	} else {
		$t->set_var("image_site_url", "");					
	}

	$image_positions = array(
		array(0, HIDDEN_MSG),
		array(1, IMAGE_IN_SEPARATE_SECTION_MSG),
		array(2, IMAGE_BELOW_LARGE_MSG),
	);

	// calculate image order
	$sql  = " SELECT MAX(image_order) FROM " . $table_prefix . "items_images ";
	$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
	$sql .= " AND image_position=2 ";
	$image_order = get_db_value($sql) + 1;

	$r = new VA_Record($table_prefix . "items_images");
	$r->return_page = "admin_item_images.php";

	$r->add_where("image_id", INTEGER);
	$r->add_textbox("item_id", INTEGER);
	$r->change_property("item_id", DEFAULT_VALUE, $item_id);
	$r->change_property("item_id", TRANSFER, true);
	$r->add_checkbox("is_image_default", INTEGER);
	$r->change_property("is_image_default", COLUMN_NAME, "is_default");
	$r->add_textbox("image_order", INTEGER);
	$r->change_property("image_order", DEFAULT_VALUE, $image_order);
	$r->change_property("image_order", REQUIRED, true);
	$r->add_textbox("image_title", TEXT, IMAGE_TITLE_MSG);
	$r->change_property("image_title", REQUIRED, true);
	$r->add_radio("image_position", INTEGER, $image_positions, IMAGE_POSITION_MSG);
	$r->change_property("image_position", DEFAULT_VALUE, 2);
	$r->add_textbox("image_tiny", TEXT, IMAGE_TINY_MSG);
	$r->add_textbox("image_tiny_alt", TEXT);
	$r->add_textbox("image_small", TEXT, IMAGE_SMALL_MSG);
	$r->change_property("image_small", USE_SQL_NULL, false);
	$r->add_textbox("image_small_alt", TEXT);
	$r->add_textbox("image_large", TEXT, IMAGE_LARGE_MSG);
	$r->change_property("image_large", USE_SQL_NULL, false);
	$r->add_textbox("image_large_alt", TEXT);
	$r->add_textbox("image_super", TEXT, IMAGE_SUPER_MSG);
	$r->add_textbox("image_super_alt", TEXT);
	$r->add_textbox("image_description", TEXT);
	$r->add_hidden("category_id", INTEGER);

	$r->set_event(BEFORE_INSERT, "check_default_image");
	$r->set_event(BEFORE_UPDATE, "check_default_image");

	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

	function check_default_image()
	{
		global $r, $db, $table_prefix;
		if ($r->get_value("is_image_default") && $r->get_value("item_id")) {
			// remove default option for all images
			$sql  = " UPDATE " . $table_prefix . "items_images ";
			$sql .= " SET is_default=0 ";
			$sql .= " WHERE item_id=" . $db->tosql($r->get_value("item_id"), INTEGER);
			$db->query($sql);

			// update default product image
			$sql = " UPDATE " . $table_prefix . "items ";
			$sql.= " SET tiny_image=" . $db->tosql($r->get_value("image_tiny"), TEXT);
			$sql.= ", tiny_image_alt=" . $db->tosql($r->get_value("image_tiny_alt"), TEXT);
			$sql.= ", small_image=" . $db->tosql($r->get_value("image_small"), TEXT);
			$sql.= ", small_image_alt=" . $db->tosql($r->get_value("image_small_alt"), TEXT);
			$sql.= ", big_image=" . $db->tosql($r->get_value("image_large"), TEXT);
			$sql.= ", big_image_alt=" . $db->tosql($r->get_value("image_large_alt"), TEXT);
			$sql.= ", super_image=" . $db->tosql($r->get_value("image_super"), TEXT);
			$sql.= ", super_image_alt=" . $db->tosql($r->get_value("image_super_alt"), TEXT);
			$sql.= " WHERE item_id=" . $db->tosql($r->get_value("item_id"), INTEGER);
			$db->query($sql);
		}
	}

?>