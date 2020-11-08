<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_category_edit.php                                  ***
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
	include_once($root_folder_path . "includes/tabs_functions.php");
	include_once($root_folder_path . "includes/sites_table.php");
	include_once($root_folder_path . "includes/access_table.php");

	check_admin_security("products_categories");
	$permissions = get_permissions();
	$add_categories = get_setting_value($permissions, "add_categories", 0);
	$update_categories = get_setting_value($permissions, "update_categories", 0);
	$remove_categories = get_setting_value($permissions, "remove_categories", 0);
	$is_record_controls = false; // global variable to prevent double call of function set_record_controls

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_category_edit.html");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", CATEGORY_MSG, CONFIRM_DELETE_MSG));

	$html_editor = get_setting_value($settings, "html_editor_products", get_setting_value($settings, "html_editor", 1));
	$t->set_var("html_editor", $html_editor);

	$editors_list = 'sd,fd';
	add_html_editors($editors_list, $html_editor);

	$site_url_path = $settings["site_url"] ? $settings["site_url"] : "../";
	$t->set_var("css_file", $site_url_path . "styles/" . $settings["style_name"] . ".css");

	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_category_edit_href", "admin_category_edit.php");
	$t->set_var("admin_category_select_href", "admin_category_select.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_column_code_href", "admin_column_code.php");
	$t->set_var("admin_item_types_select_href", "admin_item_types_select.php");

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("image_site_url", $site_url);					
	} else {
		$t->set_var("image_site_url", "");					
	}

	$category_id = get_param("category_id");

	$table_view_types =
		array(
			array(0, DEFAULT_TABLE_VIEW_MSG),
			array(1, OVERRIDE_TABLE_VIEW_MSG),
		);
	
	$r = new VA_Record($table_prefix . "categories");
	if (get_param("apply")) {
		$r->redirect = false;
	}
	$r->add_where("category_id", INTEGER);
	$r->add_textbox("redirect_category_id", INTEGER);
	$r->add_checkbox("is_showing", INTEGER);
	$r->add_textbox("category_order", INTEGER, CATEGORY_ORDER_MSG);
	$r->change_property("category_order", REQUIRED, true);
	$r->add_textbox("total_views", INTEGER);
	$r->change_property("total_views", USE_IN_INSERT, false);
	$r->change_property("total_views", USE_IN_UPDATE, false);
	$r->add_textbox("category_name", TEXT, CATEGORY_NAME_MSG);
	$r->change_property("category_name", REQUIRED, true);
	$r->add_textbox("user_list_class", TEXT);
	$r->add_textbox("admin_list_class", TEXT);
	$r->add_textbox("friendly_url", TEXT, FRIENDLY_URL_MSG);
	$r->change_property("friendly_url", USE_SQL_NULL, false);
	$r->change_property("friendly_url", BEFORE_VALIDATE, "validate_friendly_url");
	$r->change_property("friendly_url", REGEXP_MASK, FRIENDLY_URL_REGEXP);
	$r->change_property("friendly_url", REGEXP_ERROR, ALPHANUMERIC_ALLOWED_ERROR);
	$r->add_textbox("short_description", TEXT);
	$r->add_textbox("full_description", TEXT);
	$r->add_checkbox("show_sub_products", INTEGER);
	$r->add_checkbox("allowed_post_subcategories", INTEGER);
	$r->add_textbox("image_tiny", TEXT);
	$r->add_textbox("image_tiny_alt", TEXT);
	$r->add_textbox("image_small", TEXT);
	$r->add_textbox("image_small_alt", TEXT);
	$r->add_textbox("image_large", TEXT);
	$r->add_textbox("image_large_alt", TEXT);
	// products fields
	$r->add_checkbox("items_types_all", INTEGER);
	$r->change_property("items_types_all", DEFAULT_VALUE, 1);
	$r->add_textbox("items_types_ids", TEXT);

	//-- parent items
	$sql  = " SELECT * FROM " . $table_prefix . "categories ";
	$sql .= " ORDER BY category_path, category_order ";
	$db->query($sql);
	while ($db->next_record()) {
		$list_id        = $db->f("category_id");
		$list_parent_id = $db->f("parent_category_id");
		$categories[$list_id]["category_path"] = $db->f("category_path");
		$categories[$list_parent_id]["subs"][] = $list_id;
		$parent_categories[$list_id] = $list_parent_id;
	}

	$r->add_textbox("parent_category_id", INTEGER, PARENT_CATEGORY_MSG);
	$r->change_property("parent_category_id", REQUIRED, true);
	// parent category default value
	$parent_category_id = get_param("parent_category_id");
	if (!strlen($parent_category_id)) { $parent_category_id = 0; }
	$r->change_property("parent_category_id", DEFAULT_VALUE, $parent_category_id);

	$r->add_textbox("category_path", TEXT);

	// templates settings
	$r->add_textbox("list_template", TEXT);
	$r->add_textbox("details_template", TEXT);

	// meta data
	$r->add_textbox("a_title", TEXT);
	$r->add_textbox("meta_title", TEXT);
	$r->add_textbox("meta_keywords", TEXT);
	$r->add_textbox("meta_description", TEXT);

	// site navigation
	$r->add_checkbox("header_menu_show", INTEGER);
	$r->add_textbox("header_menu_order", INTEGER, HEADER_MENU_MSG.": ".MENU_ORDER_MSG);
	$r->add_textbox("header_menu_class", TEXT);
	$r->add_checkbox("nav_bar_show", INTEGER);
	$r->add_textbox("nav_bar_order", INTEGER, NAVIGATION_BAR_MSG.": ".MENU_ORDER_MSG);
	$r->add_textbox("nav_bar_class", TEXT);


	// editing information
	$r->add_textbox("admin_id_added_by", INTEGER);
	$r->change_property("admin_id_added_by", USE_IN_UPDATE, false);
	$r->add_textbox("admin_id_modified_by", INTEGER);
	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_modified", DATETIME);

	$google_base_product_types = get_db_values("SELECT type_id, type_name FROM " . $table_prefix . "google_base_types ORDER BY type_name", array(array(-1, GMC_PRODUCT_TYPE_MSG), array(0, NOT_EXPORTED_MSG)));
	$r->add_select("google_base_type_id", INTEGER, $google_base_product_types);
	
	$r->add_checkbox("sites_all", INTEGER);	
	$r->add_textbox("access_level", INTEGER);
	$r->add_textbox("guest_access_level", INTEGER);
	$r->add_textbox("admin_access_level", INTEGER);

	$r->add_select("table_view", INTEGER, $table_view_types, TABLE_VIEW_MSG);

	$r->get_form_values();

	// custom tabs
	$ctab  = new VA_Record($table_prefix . "categories_tabs", "categories_tabs");
	$ctab->add_where("tab_id", INTEGER);
	//$ctab->change_property("quantity_price_id", COLUMN_NAME, "price_id");
	$ctab->add_hidden("category_id", INTEGER);
	$ctab->change_property("category_id", USE_IN_INSERT, true);
	$ctab->change_property("category_id", USE_IN_INSERT, true);
	$ctab->change_property("category_id", PARSE_NAME, "tab_category_id");

	$ctab->add_textbox("tab_order", INTEGER, ADMIN_ORDER_MSG);
	$ctab->change_property("tab_order", REQUIRED, true);
	$ctab->add_textbox("tab_title", TEXT, TITLE_MSG);
	$ctab->change_property("tab_title", REQUIRED, true);
	$ctab->add_textbox("tab_desc", TEXT, INDIVIDUAL_PRICE_MSG);
	$ctab->add_checkbox("hide_tab", INTEGER, HIDE_MSG);

	$number_categories_tabs = get_param("number_categories_tabs");
	$ctab_eg = new VA_EditGrid($ctab, "categories_tabs");
	$ctab_eg->order_by = " ORDER BY tab_order ";
	$ctab_eg->get_form_values($number_categories_tabs);
	// end customer tabs

	// categories columns
	$cc = new VA_Record($table_prefix . "categories_columns", "categories_columns");
	$cc->add_where("column_id", INTEGER);
	$cc->add_hidden("category_id", INTEGER);
	$cc->change_property("category_id", USE_IN_INSERT, true);
	$cc->change_property("category_id", PARSE_NAME, "hidden_category_id");

	$cc->add_textbox("column_order", INTEGER, ADMIN_ORDER_MSG);
	$cc->change_property("column_order", REQUIRED, true);
	$cc->add_textbox("column_class", TEXT, CSS_CLASS_MSG);
	$cc->add_textbox("column_code", TEXT, CODE_MSG);
	$cc->change_property("column_code", REQUIRED, true);
	$cc->change_property("column_code", MAX_LENGTH, 64);
	$cc->add_textbox("column_title", TEXT, TITLE_MSG);
	$cc->change_property("column_title", REQUIRED, true);
	$cc->change_property("column_title", MAX_LENGTH, 255);
	$cc->add_textbox("column_html", TEXT, HTML_MSG);

	$columns_number = get_param("cc_number");
	$cc_eg = new VA_EditGrid($cc, "categories_columns");
	$cc_eg->order_by = " ORDER BY column_order ";
	$cc_eg->get_form_values($columns_number);
	
	if(!strlen($r->get_value("parent_category_id"))) $r->set_value("parent_category_id", "0");
	$parent_category_id = $r->get_value("parent_category_id");
	
	$tab = get_param("tab");
	if (!$tab) { $tab = "general"; }
	$return_page = "admin_items_list.php?category_id=" . $parent_category_id;
	
	$r->return_page = $return_page;

	$operation = get_param("operation");
	$return_page = "admin_items_list.php?category_id=" . $parent_category_id;

	$access_table = new VA_Access_Table($settings["admin_templates_dir"], "access_table.html");
	$access_table->set_access_levels(
		array(
			1 => array(VIEW_MSG, VIEW_CATEGORY_IN_THE_LIST_MSG), 
			2 => array(ACCESS_LIST_MSG, ACCESS_CATEGORY_DETAILS_AND_ITEMS_LIST_MSG),
			4 => array(ACCESS_DETAILS_MSG, ACCESS_CATEGORY_ITEMS_DETAILS_MSG),
			8 => array(POST_MSG, ALLOW_TO_POST_NEW_ITEMS_TO_CATEGORY_MSG)
		)
	);
	$access_table->set_tables("categories", "categories_user_types",  "categories_subscriptions", "category_id", "category_path", $category_id);
	
	$sites_table = new VA_Sites_Table($settings["admin_templates_dir"], "sites_table.html");
	$sites_table->set_tables("categories", "categories_sites", "category_id", "category_path", $category_id);
		
	$r->set_event(BEFORE_SHOW,  "set_record_controls");
	$r->set_event(BEFORE_INSERT,  "before_insert_category");
	$r->set_event(AFTER_VALIDATE, "after_validate_category");
	$r->set_event(AFTER_INSERT,   "after_insert_category");
	$r->set_event(AFTER_UPDATE,   "after_update_category");
	$r->set_event(BEFORE_DELETE,  "delete_category");
	$r->set_event(AFTER_DEFAULT,  "default_category");
	$r->set_event(AFTER_SELECT,  "get_category_columns");
	$r->set_event(AFTER_SHOW,  "show_category_columns");
	$r->set_event(ON_CUSTOM_OPERATION,  "check_custom_operations");

	$r->operations[INSERT_ALLOWED] = $add_categories;
	$r->operations[UPDATE_ALLOWED] = $update_categories;
	$r->operations[DELETE_ALLOWED] = $remove_categories;
	
	$r->process();

	$sites_table->parse("sites_table", $r->get_value("sites_all"));
	$has_any_subscriptions = $access_table->parse("subscriptions_table", $r->get_value("access_level"), $r->get_value("guest_access_level"), $r->get_value("admin_access_level"));
		
	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	if (strlen($category_id)) {
		if ($update_categories) {
			$t->set_var("save_button", UPDATE_BUTTON);
			$t->parse("save", false);
		}
		if ($remove_categories) {
			$t->parse("delete", false);
		}
	} else {
		if ($add_categories) {
			$t->set_var("save_button", ADD_BUTTON);
			$t->parse("save", false);
		}
		$t->set_var("delete", "");
	}
	
	$tabs = array(
		"general"       => array("title" => EDIT_CATEGORY_MSG),
		"images"        => array("title" => IMAGES_MSG),
		"meta"          => array("title" => META_DATA_MSG),
		"item_types"    => array("title" => PRODUCTS_TYPES_MSG),
		"categories_tabs" => array("title" => CUSTOM_TABS_MSG), 
		"table_view"    => array("title" => TABLE_VIEW_MSG),
		"site_nav"      => array("title" => SITE_NAVIGATION_MSG),
		"sites"         => array("title" => ADMIN_SITES_MSG, "show" => $sitelist),
		"subscriptions" => array("title" => ACCESS_LEVELS_MSG, "show" => $has_any_subscriptions),
	);
	parse_tabs($tabs, $tab);

	$t->pparse("main");
	
	function before_insert_category() {
		global $r, $table_prefix;
		$category_id = get_db_value("SELECT MAX(category_id) FROM " . $table_prefix . "categories") + 1;
		$r->set_value("category_id", $category_id);
		return true;
	}
	
	function after_validate_category() {
		global $r, $ctab_eg, $cc_eg, $access_table, $table_prefix, $tab;
		
		set_friendly_url();
		$category_path = VA_Categories::get_path($r->get_value("parent_category_id")).",";
		$r->set_value("category_path", $category_path);
		
		$r->set_value("admin_id_added_by", get_session("session_admin_id"));
		$r->set_value("admin_id_modified_by", get_session("session_admin_id"));
		$r->set_value("date_added", va_time());
		$r->set_value("date_modified", va_time());
		
		$r->set_value("access_level", $access_table->all_selected_access_level);
		$r->set_value("guest_access_level", $access_table->guest_selected_access_level);
		$r->set_value("admin_access_level", $access_table->admin_selected_access_level);

		$is_valid = $r->data_valid;
		$ctab_valid = $ctab_eg->validate();
		$cc_valid = $cc_eg->validate();
		if (!$is_valid) {
			$tab = "general";
		} else if (!$ctab_valid) {
			$tab = "categories_tabs";
		} else if (!$cc_valid) {
			$tab = "categories_columns";
		}

		$r->data_valid = ($is_valid && $ctab_valid && $cc_valid);

		set_record_controls();
	}
	
	function after_update_category($params) {
		global $r, $access_table, $sites_table, $table_prefix, $db, $settings;
		$updated = $params["updated"];
		$category_id = $r->get_value("category_id");
		update_category_tree($category_id, $r->get_value("category_path"));
		$access_table->save_values($category_id, get_param("save_nested_subscriptions"));
		$sites_table->save_values($category_id, $r->get_value("sites_all"), get_param("save_nested_sites"));
		
		//nested products
		$save_products_sites        = get_param('save_products_sites');
		$save_nested_products_sites = get_param('save_nested_products_sites');
		$save_products_subscriptions = get_param('save_products_subscriptions');
		$save_nested_products_subscriptions = get_param('save_nested_products_subscriptions');
		
		$products_ids = array();
		if ($save_products_sites || $save_products_subscriptions) {
			$sql  = " SELECT item_id ";
			$sql .= " FROM " . $table_prefix . "items_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$sql .= " GROUP BY item_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$products_ids[] = $db->f('item_id');
			}
		}	
		
		$subproducts_ids = array();
		$nested_categories = array();
		if ($save_nested_products_sites || $save_nested_products_subscriptions) {
			$sql  = " SELECT category_id";
			$sql .= " FROM " . $table_prefix . 	"categories";
			$sql .= " WHERE category_path LIKE '%," . $db->tosql($category_id, INTEGER, false, false) . ",%'";
			$db->query($sql);
			while ($db->next_record()) {
				$nested_categories[] = $db->f("category_id");
			}
			
			if ($nested_categories) {
				$sql  = " SELECT item_id ";
				$sql .= " FROM " . $table_prefix . "items_categories ";
				$sql .= " WHERE category_id IN (" . $db->tosql($nested_categories, INTEGERS_LIST). ")" ;
				$sql .= " GROUP BY item_id ";
				$db->query($sql);
				while ($db->next_record()) {
					$subproducts_ids[] = $db->f('item_id');
				}
			}
		}
		
		if (($save_products_sites && $products_ids) || ($save_nested_products_sites && $subproducts_ids)) {
			$products_sites_table = new VA_Sites_Table($settings["admin_templates_dir"], "sites_table.html");
			$products_sites_table->set_tables("items", "items_sites", "item_id", false, 0);
			if($save_products_sites && $products_ids) {
				$products_sites_table->save_array_values($products_ids, $r->get_value("sites_all"));
			}
			if($save_nested_products_sites && $subproducts_ids) {
				$products_sites_table->save_array_values($subproducts_ids, $r->get_value("sites_all"));
			}
		}
		
		if (($save_products_subscriptions && $products_ids) || ($save_nested_products_subscriptions && $subproducts_ids)) {
			$products_access_table = new VA_Access_Table($settings["admin_templates_dir"], "access_table.html");
			$products_access_table->set_access_levels(
				array(
					VIEW_CATEGORIES_ITEMS_PERM => array(VIEW_MSG, VIEW_ITEM_IN_THE_LIST_MSG), 
					VIEW_ITEMS_PERM => array(ACCESS_DETAILS_MSG, ACCESS_ITEMS_DETAILS_MSG)
				)
			);
			$products_access_table->set_tables("items", "items_user_types",  "items_subscriptions", "item_id", false, 0);
	
			if($save_products_subscriptions && $products_ids) {
				$products_access_table->save_array_values($products_ids, $r->get_value("access_level"), $r->get_value("guest_access_level"), $r->get_value("admin_access_level"));
			}
			if($save_nested_products_subscriptions && $subproducts_ids) {
				$products_access_table->save_array_values($subproducts_ids, $r->get_value("access_level"), $r->get_value("guest_access_level"), $r->get_value("admin_access_level"));
			}
		}	

		update_category_columns();
		if ($updated && !$r->redirect) {
			// if there is no redirect we need to get columns from database
			get_category_columns();
		}
	}
	
	function after_insert_category($params) {
		global $r, $access_table, $sites_table;
		$added = $params["added"];
		$category_id = $r->get_value("category_id"); 
		$access_table->save_values($category_id, get_param("save_nested_subscriptions"));
		$sites_table->save_values($category_id, $r->get_value("sites_all"), get_param("save_nested_sites"));	

		update_category_columns();
		if ($added && !$r->redirect) {
			// if there is no redirect we need to get columns from database
			get_category_columns();
		}
	}

	function update_category_columns()
	{
		global $r, $ctab_eg, $cc_eg, $number_categories_tabs, $columns_number;
		// update/add categories columns
		$ctab_eg->set_values("category_id", $r->get_value("category_id"));
		$ctab_eg->update_all($number_categories_tabs);
		// update/add categories columns
		$cc_eg->set_values("category_id", $r->get_value("category_id"));
		$cc_eg->update_all($columns_number);
	}

	function delete_category() {
		global $r, $table_prefix, $db, $remove_categories;
		
		if ($r->where_set || $r->operations[DELETE_ALLOWED]) {
			$category_id = $r->get_value("category_id");
			if ($category_id) {
				// first check stat for category if we need to show warning
				$stat = VA_Categories::categories_stat($category_id);
				if ($stat["subcategories_number"] > 0 || $stat["unique_products_number"] > 0) {
					$r->operations[DELETE_ALLOWED] = false;
					$delete_url = "admin_delete_confirm.php?operation=delete_categories&page=admin_category_edit&delete_id=".intval($category_id)."&category_id=".intval($category_id)."&parent_category_id=".intval($parent_category_id);
					header("Location: ".$delete_url);
					exit;
				} else {
					delete_categories($category_id);
				}
			}
		}
	}
	
	function default_category() {
		global $r, $table_prefix, $db;
		
		$parent_category_id = $r->get_value("parent_category_id");		
		$category_order = get_db_value("SELECT MAX(category_order) FROM " . $table_prefix . "categories WHERE parent_category_id=" . $db->tosql($parent_category_id, INTEGER));
		$category_order++;
		$r->set_value("is_showing", 1);
		$r->set_value("category_order", $category_order);
		$r->set_value("parent_category_id", $parent_category_id);
		$r->set_value("access_level", 15);
		$r->set_value("guest_access_level", 7);
		$r->set_value("admin_access_level", 7);
		$r->set_value("sites_all", 1);	
	}
	
	function spaces_level($level)
	{
		$spaces = "";
		for ($i =1; $i <= $level; $i++) {
			$spaces .= "--";
		}
		return $spaces . " ";
	}
	
	
	function update_category_tree($parent_category_id, $category_path)
	{
		global $db, $table_prefix, $categories, $parent_categories;
		
		if (isset($categories[$parent_category_id]["subs"])) {
			$category_path .= $parent_category_id . ",";	
			$subs = $categories[$parent_category_id]["subs"];
			for ($s = 0; $s < sizeof($subs); $s++) {
				$sub_id = $subs[$s];
				$sql  = " UPDATE " . $table_prefix . "categories SET ";
				$sql .= " category_path=" . $db->tosql($category_path, TEXT);
				$sql .= " WHERE category_id=" . $db->tosql($sub_id, INTEGER);
				$db->query($sql);

				if (isset($categories[$sub_id]["subs"])) {
					update_category_tree($sub_id, $category_path);
				}
			}
		}
	}
	
	function get_category_columns()
	{
		global $r, $ctab_eg, $cc_eg, $number_categories_tabs, $columns_number;
		// check data for categories tabs
		$ctab_eg->set_value("category_id", $r->get_value("category_id"));
		$ctab_eg->change_property("tab_id", USE_IN_SELECT, true);
		$ctab_eg->change_property("tab_id", USE_IN_WHERE, false);
		$ctab_eg->change_property("category_id", USE_IN_WHERE, true);
		$ctab_eg->change_property("category_id", USE_IN_SELECT, true);
		$number_categories_tabs = $ctab_eg->get_db_values();
		// check data for categories columns
		$cc_eg->set_value("category_id", $r->get_value("category_id"));
		$cc_eg->change_property("column_id", USE_IN_SELECT, true);
		$cc_eg->change_property("column_id", USE_IN_WHERE, false);
		$cc_eg->change_property("category_id", USE_IN_WHERE, true);
		$cc_eg->change_property("category_id", USE_IN_SELECT, true);
		$columns_number = $cc_eg->get_db_values();
	}

	function show_category_columns()
	{
		global $t, $r, $db, $table_prefix, $ctab_eg, $cc_eg, $number_categories_tabs, $columns_number;
		// set categories tabs 
		if ($number_categories_tabs == 0) {
			$number_categories_tabs = 5;
		}
		$t->set_var("number_categories_tabs", $number_categories_tabs);
		$ctab_eg->set_parameters_all($number_categories_tabs);
		// set categories columns
		if ($columns_number == 0) {
			$columns_number = 5;
		}
		$t->set_var("cc_number", $columns_number);
		$cc_eg->set_parameters_all($columns_number);

		$path_ids = array(); // save ids to prevent unlimited recurring 
		$parent_category_id = $r->get_value("parent_category_id");
		if ($parent_category_id) {
			$parent_category_desc = "";

			$tree_category_id = $parent_category_id;
			$sql  = " SELECT category_name, parent_category_id ";
			$sql .= " FROM ".$table_prefix."categories ";
			$sql .= " WHERE category_id=";
			while ($tree_category_id && !isset($path_ids[$tree_category_id])) {
				$db->query($sql . $db->tosql($tree_category_id, INTEGER));
				if($db->next_record()) {
					if ($parent_category_desc) { $parent_category_desc = " > " . $parent_category_desc; }
					$tree_name = get_translation($db->f("category_name"));
					$parent_category_desc =  $tree_name . $parent_category_desc;
					$tree_category_id = $db->f("parent_category_id");
					$path_ids[$tree_category_id] = true;
				} else {
					$tree_category_id = 0;
				}
			} 
			$t->set_var("parent_category_desc", $parent_category_desc);
			$t->set_var("parent_category_remove_style", "display: inline;");
		} else {
			$t->set_var("parent_category_desc", "[Top]");
			$t->set_var("parent_category_remove_style", "display: none;");

		}

		$path_ids = array(); // save ids to prevent unlimited recurring 
		$redirect_category_id = $r->get_value("redirect_category_id");
		if ($redirect_category_id) {
			$redirect_category_desc = "";

			$tree_category_id = $redirect_category_id;
			$sql  = " SELECT category_name, parent_category_id ";
			$sql .= " FROM ".$table_prefix."categories ";
			$sql .= " WHERE category_id=";
			while ($tree_category_id && !isset($path_ids[$tree_category_id])) {
				$db->query($sql . $db->tosql($tree_category_id, INTEGER));
				if($db->next_record()) {
					if ($redirect_category_desc) { $redirect_category_desc = " > " . $redirect_category_desc; }
					$tree_name = get_translation($db->f("category_name"));
					$redirect_category_desc =  $tree_name . $redirect_category_desc;
					$tree_category_id = $db->f("parent_category_id");
					$path_ids[$tree_category_id] = true;
				} else {
					$tree_category_id = 0;
				}
			} 
			$t->set_var("redirect_category_desc", $redirect_category_desc);
		} else {
			$t->set_var("redirect_category_style", "display: none;");
		}
	}

	function check_custom_operations($params)
	{
		global $r, $number_categories_tabs, $columns_number, $access_table;

		$operation = get_setting_value($params, "operation", "");


		if ($operation == "more_categories_tabs") {
			// add more tabs 
			$number_categories_tabs += 5;
			$r->redirect = false;

			// set access level paramaters
			$r->set_value("access_level", $access_table->all_selected_access_level);
			$r->set_value("guest_access_level", $access_table->guest_selected_access_level);
			$r->set_value("admin_access_level", $access_table->admin_selected_access_level);
		} else if ($operation == "more_categories_columns") {
			// add more columns
			$columns_number += 5;
			$r->redirect = false;

			// set access level paramaters
			$r->set_value("access_level", $access_table->all_selected_access_level);
			$r->set_value("guest_access_level", $access_table->guest_selected_access_level);
			$r->set_value("admin_access_level", $access_table->admin_selected_access_level);
		}
	}


	function set_record_controls()
	{
		global $t, $r, $db, $table_prefix, $is_record_controls;
		if ($is_record_controls) {
			return false;
		} else {
			$is_record_controls = true;
		}
		$items_types_ids = $r->get_value("items_types_ids");
		if ($items_types_ids) {
			$sql  = " SELECT it.item_type_id, it.item_type_name ";
			$sql .= " FROM " . $table_prefix . "item_types it ";
			$sql .= " WHERE it.item_type_id IN (" . $db->tosql($items_types_ids, INTEGERS_LIST) . ") ";
			$sql .= " ORDER BY it.item_type_name ";
			$db->query($sql);
			while($db->next_record())
			{
				$row_type_id = $db->f("item_type_id");
				$type_name = $db->f("item_type_name");
		
				$t->set_var("item_type_id", $row_type_id);
				$t->set_var("item_type_name", $type_name);
				$t->set_var("item_type_name_js", str_replace("\"", "&quot;", $type_name));
		
				$t->parse("selected_item_types", true);
				$t->parse("selected_item_types_js", true);
			}
		}

		// set styles for a tag for items_types_all checkbox
		$items_types_all = $r->get_value("items_types_all");
		if ($items_types_all) {
			$t->set_var("items_types_all_a_class", "disabled");
		} else {
			$t->set_var("items_types_all_a_class", "title");
		}
	}

?>