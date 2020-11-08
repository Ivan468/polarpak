<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_item_images.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once("./admin_common.php");

	check_admin_security("product_images");

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
	$t->set_file("main", "admin_item_images.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_product_href", "admin_product.php");
	$t->set_var("admin_item_image_href", "admin_item_image.php");
	$t->set_var("admin_item_images_href", "admin_item_images.php");

	$t->set_var("item_id", $item_id);
	$t->set_var("category_id", $category_id);
	$t->set_var("item_name", $item_name);

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_item_images.php");
	$s->set_sorter(ID_MSG, "sorter_image_id", "1", "image_id", "", "", true);
	$s->set_sorter(IMAGE_TITLE_MSG, "sorter_image_title", "2", "image_title");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_item_images.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "items_images ";
	$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT * FROM " . $table_prefix . "items_images ";
	$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
	$sql .= $s->order_by;
	$db->query($sql);
	if ($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {
			$image_title = get_translation($db->f("image_title"));
			$t->set_var("image_id", $db->f("image_id"));
			$t->set_var("image_title", $image_title);
			$t->parse("records", true);
		} while ($db->next_record());
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->pparse("main");

?>