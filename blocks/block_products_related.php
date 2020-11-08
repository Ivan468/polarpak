<?php

	include_once("./includes/products_functions.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	// global array to use in different blocks
	if(!isset($va_data)) { $va_data = array(); }
	if(!isset($va_data["products_index"])) { $va_data["products_index"] = 0; }
	$start_index = $va_data["products_index"] + 1;

	$default_title = "{related_products_title}";

	$user_id      = get_session("session_user_id");	
	$user_info    = get_session("session_user_info");
	$discount_type   = get_session("session_discount_type");
	$discount_amount = get_session("session_discount_amount");	
	$price_type      = get_session("session_price_type");

	$user_tax_free = get_setting_value($user_info, "tax_free", 0);
	
	$friendly_urls      = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$display_products   = get_setting_value($settings, "display_products", 0);
	
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	$image_type       = get_setting_value($vars, "image_type",  2);
	$desc_type        = get_setting_value($vars, "desc_type", 1);
	$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
	product_image_fields($image_type, $image_type_name, $image_field, $image_alt_field, $watermark, $product_no_image);
	// check buttons to show
	$bn_add = get_setting_value($vars, "bn_add", 1);
	$bn_view = get_setting_value($vars, "bn_view", 0);
	$bn_goto = get_setting_value($vars, "bn_goto", 0);
	$bn_wish = get_setting_value($vars, "bn_wish", 0);
	$bn_more = get_setting_value($vars, "bn_more", 0);


	if ($price_type == 1) {
		$price_field = "trade_price";
		$sales_field = "trade_sales";
		$properties_field = "trade_properties_price";
	} else {
		$price_field = "price";
		$sales_field = "sales_price";
		$properties_field = "properties_price";
	}

	$desc_field = "";
	if ($desc_type == 1) {
		$desc_field = "short";
	} elseif ($desc_type == 2) {
		$desc_field = "full";
	} elseif ($desc_type == 3) {
		$desc_field = "high";
	} elseif ($desc_type == 4) {
		$desc_field = "spec";
	}


	$item_id     = get_param("item_id");
	$article_id  = get_param("article_id");
	$thread_id   = get_param("thread_id");	
	$category_id = get_param("category_id");
	
	$related_type_join  = "";
	$related_type_where = "";
	$related_type_order = "";

	if ($cms_block_code == "products_related") {
		$related_type_join  = " LEFT JOIN " . $table_prefix . "items_related rel ON i.item_id=rel.related_id";
		$related_type_where = " rel.item_id=" . $db->tosql($item_id, INTEGER);
		$related_type_order = " rel.related_order, i.item_id ";
		
		$t->set_var("related_products_title", RELATED_PRODUCTS_TITLE);
		$product_page = "product_details.php";
		
		$records_per_page = get_setting_value($vars, "related_per_page", 10);
		$related_columns_param = "related_columns";
	} elseif ($cms_block_code == "forum_related_products") {
		$related_type_join  = " LEFT JOIN " . $table_prefix . "items_forum_topics rel ON i.item_id=rel.item_id ";
		$related_type_where = " rel.thread_id=" . $db->tosql($thread_id, INTEGER);
		$related_type_order = " rel.item_order, i.item_id ";
		
		$t->set_var("related_products_title", RELATED_PRODUCTS_TITLE);
		$product_page = "forum_topic.php";
		
		$records_per_page = get_setting_value($vars, "related_per_page", 10);
		$related_columns_param = "related_columns";
	} elseif ($cms_block_code == "articles_related_products") {
		$related_type_join  = " LEFT JOIN " . $table_prefix . "articles_items_related rel ON i.item_id=rel.item_id";
		$related_type_where = " rel.article_id=" . $db->tosql($article_id, INTEGER);
		$related_type_order = " rel.item_order, i.item_id ";
		
		$t->set_var("related_products_title", ARTICLE_RELATED_PRODUCTS_TITLE);		
		$product_page = "article.php";								
		$sql  = " SELECT ac.category_path, ac.category_id FROM " . $table_prefix . "articles_categories ac ";
		$sql .= " INNER JOIN " . $table_prefix . "articles_assigned aas ON aas.category_id=ac.category_id ";
		$sql .= " WHERE aas.article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$category_id   = $db->f("category_id");
			$art_category_path = $db->f("category_path");
			if ("0," == $art_category_path) {
				$top_category_id = $category_id;
			} else {
				$art_category_path_parts = explode(",", $art_category_path);
				if (isset($art_category_path_parts[1])) {
					$top_category_id = $art_category_path_parts[1];
				} else {
					$top_category_id = $category_id;
				}
			}
		} else {
			$top_category_id = "0";
		}
			
		// TODO: check if block_key could be used for $top_category_id
		$records_per_page      = get_setting_value($vars, "articles_related_products_recs", 5);
		$related_columns_param = "articles_related_products_cols";	

	} elseif ($cms_block_code == "articles_category_products_relat" || $cms_block_code == "articles_category_products_related") {
		$related_type_join  = " LEFT JOIN " . $table_prefix . "articles_categories_items rel ON i.item_id=rel.item_id";
		$related_type_where = " rel.category_id=" . $db->tosql($category_id, INTEGER);
		$related_type_order = " rel.related_order, i.item_id ";
		
		$t->set_var("related_products_title", CATEGORY_RELATED_PRODUCTS_TITLE);

		if ($cms_page_code == "article_details" || $cms_page_code == "article_reviews") {
			$product_page = "article.php";			
			$sql  = " SELECT ac.category_path, ac.category_id FROM " . $table_prefix . "articles_categories ac ";
			$sql .= " INNER JOIN " . $table_prefix . "articles_assigned aas ON aas.category_id=ac.category_id ";
			$sql .= " WHERE aas.article_id=" . $db->tosql($article_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$category_id = $db->f("category_id");
				$art_category_path = $db->f("category_path");
				if ("0," == $art_category_path) {
					$top_category_id = $category_id;
				} else {
					$art_category_path_parts = explode(",", $art_category_path);
					if (isset($art_category_path_parts[1])) {
						$top_category_id = $art_category_path_parts[1];
					} else {
						$top_category_id = $category_id;
					}
				}
			} else {
				$top_category_id = "0";
			}
		} else {
			$product_page = "articles.php";			
			$sql = "SELECT category_path FROM " . $table_prefix . "articles_categories WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$art_category_path = get_db_value($sql);
			if ("0," == $art_category_path) {
				$top_category_id = $category_id;
			} else {
				$art_category_path_parts = explode(",", $art_category_path);
				if (isset($art_category_path_parts[1])) {
					$top_category_id = $art_category_path_parts[1];
				} else {
					$top_category_id = $category_id;
				}
			}
		}		
		
		// TODO: check if block_key could be used for $top_category_id
		$records_per_page      = get_setting_value($vars, "articles_products_cats_recs", 5);
		$related_columns_param = "articles_products_cats_cols";
	} else {
		$block_parsed = false;
		return;
	}
	
	$ri_columns = get_setting_value($vars, $related_columns_param, 1);
	$html_template = get_setting_value($block, "html_template", "block_products.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("product_details_href", "product_details.php");
	$t->set_var("columns_class", "cols-".$ri_columns);
	
	if ($friendly_urls && $page_friendly_url) {
		$pass_parameters = get_transfer_params($page_friendly_params);
		$main_page = $page_friendly_url . $friendly_extension;
	} else {
		$pass_parameters = get_transfer_params();
		$main_page = get_custom_friendly_url($product_page);
	}
	
	$page_param = "ri_page";
	$pages_number = 10;
		
	// prepare params for VA_Products class to show products
	$sql_params = array();
	$sql_params["where"][] = $related_type_where;
	$sql_params["join"][]  = $related_type_join;
	$sql_params["order"][] = $related_type_order;
	$sql_params["group"][] = "rel.related_order, i.item_id";

	// override params:
	$params = array(
		"pb_id" => $pb_id,
		"sql" => $sql_params,
		"recs" => $records_per_page,
		"page_param" => $page_param,
		"pages" => $pages_number,
		"cols" => $ri_columns,
		"qty" => "no",
		"image" => $image_type_name,
		"desc" => $desc_field,
		"add" => $bn_add,
		"view" => $bn_view,
		"goto" => $bn_goto,
		"wish" => $bn_wish,
		"more" => $bn_more,
	);

	$products_number = VA_Products::show_products($params);

	if ($products_number) {
		$block_parsed = true;
	}
