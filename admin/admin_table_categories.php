<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_table_categories.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "categories";
	$table_alias = "c";
	$table_pk = "category_id";
	$table_title = CATEGORIES_TITLE;
	$min_column_allowed = 1;

	$tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "categories", "tree");
	$category_id = get_param("category_id");
	if (!strlen($category_id)) { $category_id = 0; }

	$db_columns = array(
		"category_id" => array(CAT_ID_MSG, INTEGER, 1, false),
		"is_showing" => array(IS_CAT_SHOWN_MSG, INTEGER, 2, true, 1),
		"parent_category_id" => array(PARENT_CAT_ID_MSG, INTEGER, 2, true, $category_id),
		"category_name" => array(CATEGORY_NAME_MSG, TEXT, 2, true),
		"friendly_url" => array(FRIENDLY_URL_MSG, TEXT, 2, false, ""),
		"category_path" => array(CAT_PATH_MSG, TEXT, 4, true, $tree->get_path($category_id)),
		"category_order" => array(CATEGORY_ORDER_MSG, INTEGER, 2, true, 1),
		"short_description" => array(SHORT_DESCRIPTION_MSG, TEXT, 2, false),
		"full_description" => array(FULL_DESCRIPTION_MSG, TEXT, 2, false),
		"show_sub_products" => array(SUBCATEGORIES_PRODUCTS_MSG, INTEGER, 2, true, 1),
		
		"image" => array(IMAGE_SMALL_MSG, TEXT, 2, false),
		"image_alt" => array(IMAGE_SMALL_ALT_MSG, TEXT, 2, false),
		"image_large" => array(IMAGE_LARGE_MSG, TEXT, 2, false),
		"image_large_alt" => array(IMAGE_LARGE_ALT_MSG, TEXT, 2, false),

		"meta_title" => array(META_TITLE_MSG, TEXT, 2, false),
		"meta_keywords" => array(META_KEYWORDS_MSG, TEXT, 2, false),
		"meta_description" => array(META_DESCRIPTION_MSG, TEXT, 2, false),
		"total_views" => array(TOTAL_VIEWS_MSG, INTEGER, 2, false, 0),
		"google_base_type_id" => array(GOOGLE_BASE_PRODUCT_TYPE_MSG, INTEGER, 3, true, 1),

		"header_menu_show" => array(SHOW_IN_HEADER_MENU_MSG, INTEGER, 2, false),
		"header_menu_order" => array(HEADER_MENU_MSG.": ".MENU_ORDER_MSG, INTEGER, 2, false),
		"header_menu_class" => array(HEADER_MENU_MSG.": ".MENU_CSS_CLASS_MSG, TEXT, 2, false),

		"nav_bar_show" => array(SHOW_IN_NAV_BAR_MSG, INTEGER, 2, false),
		"nav_bar_order" => array(NAVIGATION_BAR_MSG.": ".MENU_ORDER_MSG, INTEGER, 2, false),
		"nav_bar_class" => array(NAVIGATION_BAR_MSG.": ".MENU_CSS_CLASS_MSG, TEXT, 2, false),
	);

	$db_aliases["id"] = "category_id";
	$db_aliases["category_id"] = "category_id";
	$db_aliases["category id"] = "category_id";
	$db_aliases["title"] = "category_name";
	$db_aliases["category"] = "category_name";
	$db_aliases["category title"] = "category_name";
	$db_aliases["category name"] = "category_name";
	$db_aliases["category_title"] = "category_name";
	$db_aliases["category_name"] = "category_name";
	$db_aliases["category shown"] = "is_showing";
	$db_aliases["category_shown"] = "is_showing";
	$db_aliases["is show"] = "is_showing";
	$db_aliases["is shown"] = "is_showing";
	$db_aliases["is showning"] = "is_showing";
	$db_aliases["is_show"] = "is_showing";
	$db_aliases["is_shown"] = "is_showing";
	$db_aliases["is_showning"] = "is_showing";
	$db_aliases["category order"] = "category_order";
	$db_aliases["category_order"] = "category_order";

?>