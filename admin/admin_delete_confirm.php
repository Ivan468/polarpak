<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_delete_confirm.php                                 ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "includes/shopping_cart.php");
	include_once($root_folder_path . "includes/friendly_functions.php");
	include_once($root_folder_path . "includes/products_functions.php");
	include_once($root_folder_path . "includes/sites_table.php");
	include_once($root_folder_path . "includes/access_table.php");

	check_admin_security();

	$operation = get_param("operation");
	$approve = get_param("approve");
	$page = get_param("page");
	$param_category_id = get_param("category_id");
	$param_parent_category_id = get_param("parent_category_id");
	$delete_id = get_param("delete_id");
	$delete_ids = get_param("delete_ids");
	if (!$delete_ids) { $delete_ids = $delete_id; }

	if ($operation == "delete_categories") {
		check_admin_security("products_categories");
	}

	$errors = "";
	$redirect_url = "admin.php";
	$permissions = get_permissions();
	$remove_categories = get_setting_value($permissions, "remove_categories", 0);

	if ($approve== "yes") {
		if ($operation == "delete_categories") {
			if ($remove_categories) {
				delete_categories($delete_ids);
				if ($page == "admin_category_edit") {
					$redirect_url = "admin_items_list.php?category_id=".intval($param_parent_category_id);
				} else if ($page == "admin_items_list") {
					$redirect_url = "admin_items_list.php";
					if ($param_category_id) { $redirect_url .= "?category_id=".intval($param_category_id); }
				}
				header("Location: ". $redirect_url);	
				exit;
			} else {
				$errors = DELETE_ALLOWED_ERROR;
			}
		}
		
	} else if ($approve== "no") {
		if ($page == "admin_category_edit") {
			$redirect_url = "admin_category_edit.php?category_id=".intval($param_category_id)."&parent_category_id=".intval($param_parent_category_id);
		} else if ($page == "admin_items_list") {
			$redirect_url = "admin_items_list.php";
			if ($param_category_id) { $redirect_url .= "?category_id=".intval($param_category_id); }
		}
		header("Location: ". $redirect_url);	
		exit;
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_delete_confirm.html");
	$t->set_var("admin_delete_confirm_href", "admin_delete_confirm.php");

	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", CATEGORY_MSG, CONFIRM_DELETE_MSG));

	$t->set_var("admin_category_edit_href", "admin_category_edit.php");
	$t->set_var("admin_category_select_href", "admin_category_select.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");

	$max_also_records = 5;
	if ($operation == "delete_categories") {
		$stat = VA_Categories::categories_stat($delete_ids);

		$categories = $stat["categories"];
		$categories_number = 0; $subcategories_number = 0;
		foreach ($categories as $category_id => $category_data) {
			$category_type = $category_data["type"];
			$category_name = $category_data["name"];
			$category_path = $category_data["path"];
			if (!is_array($category_path)) {
				$category_path = explode(",", trim($category_data["path"], " ,"));
			}
			$path_name = "";
			if (count($category_path)) {
				foreach ($category_path as $id) {
					if (isset($categories[$id])) { $path_name .= strip_tags($categories[$id]["name"])." &gt; ";}
				}
			}
			$path_name.= strip_tags($category_name);
			$t->set_var("category_name", $path_name);
			if ($category_type == "main") {
				$categories_number++;
				$t->parse("delete_categories", true);
			} else if ($category_type == "sub") {
				$subcategories_number++;
				if ($subcategories_number <= $max_also_records) {
					$t->parse("also_categories", true);
				}
			}
		}

		if ($categories_number) {
			$t->set_var("categories_number", intval($categories_number));
			$t->parse("delete_categories_block", false);
		}		
		if ($subcategories_number) {
			if ($subcategories_number > $max_also_records) {
				$t->set_var("category_name", ".....");
				$t->parse("also_categories", true);
			}
			$t->set_var("also_categories_number", intval($subcategories_number));
			$t->parse("also_categories_block", false);
		}		

		$unique_products = $stat["unique_products"];
		$unique_products_number = 0; 
		if (count($unique_products)) {
			foreach ($unique_products as $item_id => $product_data) {
				$unique_products_number++;
				$product_name = strip_tags($product_data["name"]);
				$t->set_var("product_name", $product_name);
				if ($unique_products_number <= $max_also_records) {
					$t->parse("also_products", true);
				}
			}
			if ($unique_products_number > $max_also_records) {
				$t->set_var("product_name", ".....");
				$t->parse("also_products", true);
			}
			$t->set_var("also_products_number", intval($unique_products_number));
			$t->parse("also_products_block", false);
		}

		if ($unique_products_number || $subcategories_number) {
			$t->parse("also_delete_title", false);
		}
	}

	$t->set_var("page", htmlspecialchars($page));
	$t->set_var("operation", htmlspecialchars($operation));
	$t->set_var("category_id", htmlspecialchars($param_category_id));
	$t->set_var("parent_category_id", htmlspecialchars($param_parent_category_id));
	$t->set_var("delete_id", htmlspecialchars($delete_id));
	$t->set_var("delete_ids", htmlspecialchars($delete_ids));

	if ($errors) {
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

?>