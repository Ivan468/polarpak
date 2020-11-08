<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_block_images.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");


	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	$ajax = get_param("ajax");
	$ajax_response = array(); // save here response for ajax
	$item_id = get_param("item_id");
	$article_id = get_param("article_id");
	$category_id = get_param("category_id");
	$image_id = get_param("image_id");
	$operation = get_param("operation");
	$va_module_param = get_param("va_module");
	if (!isset($va_module) || $va_module_param) {
		$va_module = $va_module_param;
	}
	// list values
	$image_positions = array(
		array(0, HIDDEN_MSG),
		array(1, IMAGE_IN_SEPARATE_SECTION_MSG),
		array(2, IMAGE_BELOW_LARGE_MSG),
	);



	$image_site_url = "";
	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$image_site_url = $site_url;
	}
	if ($va_module == "articles") {
		check_admin_security("articles");
		$parent_table = $table_prefix."articles";
		$images_table = $table_prefix."articles_images";
		$image_tiny_select = "article_tiny";
		$image_small_select = "article_small";
		$image_large_select = "article_large";
		$image_super_select = "article_super";
	} elseif ($va_module == "articles_categories") {
		check_admin_security("articles");
		$parent_table = $table_prefix."articles_categories";
		$images_table = $table_prefix."articles_images";
		$image_tiny_select = "category_tiny";
		$image_small_select = "category_small";
		$image_large_select = "category_large";
		$image_super_select = "category_super";
	} else {
		check_admin_security("product_images");
		$parent_table = $table_prefix."items";
		$images_table = $table_prefix."items_images";
		$image_tiny_select = "tiny_image";
		$image_small_select = "small_image";
		$image_large_select = "big_image";
		$image_super_select = "super_image";
	}

	// delete operation	
	if ($operation == "delete" && $image_id) {
		$is_default = 0; 
		$sql = " SELECT * FROM " . $images_table." ";
		$sql.= " WHERE image_id=" . $db->tosql($image_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$is_default = $db->f("is_default");
		}

		$sql = " DELETE FROM " . $images_table." ";
		$sql.= " WHERE image_id=" . $db->tosql($image_id, INTEGER);
		$db->query($sql);

		if ($is_default) {
			// clear default image from items table
			if ($va_module == "articles") {
				$sql = " UPDATE " . $parent_table." ";
				$sql.= " SET image_tiny=NULL";
				$sql.= ", image_tiny_alt=NULL";
				$sql.= ", image_small=NULL";
				$sql.= ", image_small_alt=NULL";
				$sql.= ", image_large=NULL";
				$sql.= ", image_large_alt=NULL";
				$sql.= ", image_super=NULL";
				$sql.= ", image_super_alt=NULL";
				$sql.= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
				$db->query($sql);
			} else if ($va_module == "articles_categories") {
			} else {
				$sql = " UPDATE " . $parent_table." ";
				$sql.= " SET tiny_image=NULL";
				$sql.= ", tiny_image_alt=NULL";
				$sql.= ", small_image=NULL";
				$sql.= ", small_image_alt=NULL";
				$sql.= ", big_image=NULL";
				$sql.= ", big_image_alt=NULL";
				$sql.= ", super_image=NULL";
				$sql.= ", super_image_alt=NULL";
				$sql.= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$db->query($sql);
			}

			// check first image to make it default
			$sql = " SELECT * FROM " . $images_table." ";
			if ($va_module == "articles") {
				$sql.= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
			} else if ($va_module == "articles_categories") {
				$sql.= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			} else {
				$sql.= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			}
			$sql.= " ORDER BY image_order ";
			$db->query($sql);
			if ($db->next_record()) {
				$operation = "default";
				$image_id = $db->f("image_id");
			}
		}
	} 

	if ($operation == "default" && $image_id) {
		// remove default option for all images
		$sql  = " UPDATE " . $images_table." ";
		$sql .= " SET is_default=0 ";
		if ($va_module == "articles") {
			$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
		} else if ($va_module == "articles") {
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		} else {
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		}

		$db->query($sql);

		// set default option for image
		$sql  = " UPDATE " . $images_table." ";
		$sql .= " SET is_default=1 ";
		$sql .= " WHERE image_id=" . $db->tosql($image_id, INTEGER);
		$db->query($sql);

		// get information for image
		$sql = " SELECT * FROM " . $images_table." ";
		$sql.= " WHERE image_id=" . $db->tosql($image_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$image_tiny = $db->f("image_tiny");
			$image_tiny_alt = $db->f("image_tiny_alt");
			$image_small = $db->f("image_small");
			$image_small_alt = $db->f("image_small_alt");
			$image_large = $db->f("image_large");
			$image_large_alt = $db->f("image_large_alt");
			$image_super = $db->f("image_super");
			$image_super_alt = $db->f("image_super_alt");

			if ($va_module == "articles") {
				// update default article image
				$sql = " UPDATE " . $parent_table." ";
				$sql.= " SET image_tiny=" . $db->tosql($image_tiny, TEXT);
				$sql.= ", image_tiny_alt=" . $db->tosql($image_tiny_alt, TEXT);
				$sql.= ", image_small=" . $db->tosql($image_small, TEXT);
				$sql.= ", image_small_alt=" . $db->tosql($image_small_alt, TEXT);
				$sql.= ", image_large=" . $db->tosql($image_large, TEXT);
				$sql.= ", image_large_alt=" . $db->tosql($image_large_alt, TEXT);
				$sql.= ", image_super=" . $db->tosql($image_super, TEXT);
				$sql.= ", image_super_alt=" . $db->tosql($image_super_alt, TEXT);
				$sql.= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
				$db->query($sql);
			} else if ($va_module == "articles_categories") {
			} else {
				// update default product image
				$sql = " UPDATE " . $parent_table." ";
				$sql.= " SET tiny_image=" . $db->tosql($image_tiny, TEXT);
				$sql.= ", tiny_image_alt=" . $db->tosql($image_tiny_alt, TEXT);
				$sql.= ", small_image=" . $db->tosql($image_small, TEXT);
				$sql.= ", small_image_alt=" . $db->tosql($image_small_alt, TEXT);
				$sql.= ", big_image=" . $db->tosql($image_large, TEXT);
				$sql.= ", big_image_alt=" . $db->tosql($image_large_alt, TEXT);
				$sql.= ", super_image=" . $db->tosql($image_super, TEXT);
				$sql.= ", super_image_alt=" . $db->tosql($image_super_alt, TEXT);
				$sql.= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
				$db->query($sql);
			}
		}
	}

	if (!$item_id && !$article_id && !$category_id) {
		return;
	}

	if (!isset($block) || !$block) {
		$block = get_param("block");
	}

	if (!$block || $block == "all") {
		// check if we need add parent image to the list
		check_parent_image();
	}

	if (!isset($t)) {
		$t = new VA_Template($settings["admin_templates_dir"]);
	}
  $t->set_file("block_body", "admin_block_images.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_product_href", "admin_product.php");

	$t->set_var("item_id", htmlspecialchars($item_id));
	$t->set_var("article_id", htmlspecialchars($article_id));
	$t->set_var("category_id", htmlspecialchars($category_id));
	$t->set_var("va_module", htmlspecialchars($va_module));
	$t->set_var("image_tiny_select", htmlspecialchars($image_tiny_select));
	$t->set_var("image_small_select", htmlspecialchars($image_small_select));
	$t->set_var("image_large_select", htmlspecialchars($image_large_select));
	$t->set_var("image_super_select", htmlspecialchars($image_super_select));


	if ($block == "edit_image") {

		$t->set_var("admin_select_href", "admin_select.php");
		$t->set_var("admin_upload_href", "admin_upload.php");

		// calculate image order	
		$sql  = " SELECT MAX(image_order) FROM " . $images_table." ";
		if ($va_module == "articles") {
			$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
		} else if ($va_module == "articles_categories") {
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		} else {
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		}
		$sql .= " AND image_position=2 ";
		$image_order = get_db_value($sql) + 1;
  
		$ri = new VA_Record($images_table);
		$ri->redirect = false;
		$ri->add_where("image_id", INTEGER);
		if ($va_module == "articles") {
			$ri->add_textbox("article_id", INTEGER);
			$ri->change_property("article_id", DEFAULT_VALUE, $article_id);
			$ri->change_property("article_id", REQUIRED, true);
		} else if ($va_module == "articles_categories") {
			$ri->add_textbox("category_id", INTEGER);
			$ri->change_property("category_id", DEFAULT_VALUE, $category_id);
			$ri->change_property("category_id", REQUIRED, true);
		} else {
			$ri->add_textbox("item_id", INTEGER);
			$ri->change_property("item_id", DEFAULT_VALUE, $item_id);
			$ri->change_property("item_id", REQUIRED, true);
		}
		$ri->add_checkbox("is_image_default", INTEGER);
		$ri->change_property("is_image_default", COLUMN_NAME, "is_default");
		$ri->add_textbox("image_order", INTEGER);
		$ri->change_property("image_order", DEFAULT_VALUE, $image_order);
		$ri->change_property("image_order", REQUIRED, true);
		$ri->add_textbox("image_title", TEXT, IMAGE_TITLE_MSG);
		$ri->change_property("image_title", REQUIRED, true);
		$ri->add_radio("image_position", INTEGER, $image_positions, IMAGE_POSITION_MSG);
		$ri->change_property("image_position", DEFAULT_VALUE, 2);
		$ri->add_textbox("image_tiny", TEXT, IMAGE_TINY_MSG);
		$ri->add_textbox("image_tiny_alt", TEXT);
		$ri->add_textbox("image_small", TEXT, IMAGE_SMALL_MSG);
		$ri->change_property("image_small", USE_SQL_NULL, false);
		$ri->add_textbox("image_small_alt", TEXT);
		$ri->add_textbox("image_large", TEXT, IMAGE_LARGE_MSG);
		$ri->change_property("image_large", USE_SQL_NULL, false);
		$ri->add_textbox("image_large_alt", TEXT);
		$ri->add_textbox("image_super", TEXT, IMAGE_SUPER_MSG);
		$ri->add_textbox("image_super_alt", TEXT);
		$ri->add_textbox("image_description", TEXT);
		$ri->set_event(BEFORE_INSERT, "check_default_image");
		$ri->set_event(BEFORE_UPDATE, "check_default_image");
  
		$ri->process();

		if ($image_id) {
			$t->sparse("delete_image", false);
		}

		$t->set_var("meta_title", EDIT_IMAGE_MSG);

		if ($operation == "save") {
			if ($ri->errors) {
				$t->parse("edit_image", false);
				$ajax_response["errors"] = $ri->errors;
				$ajax_response["edit_image"] = $t->get_var("edit_image");
			} else {
				// if form submitted successfully parse all blocks 
				$block = "all";
			}
		} else {
			//$t->parse_to("edit_image", "middle", true);
			//$t->parse("frame_layout");
			$t->parse("edit_image", false);
			if ($ajax) {
				$ajax_response["image_id"] = $image_id;
				//$ajax_response["edit_image"] = $t->get_var("frame_layout");
				$ajax_response["edit_image"] = $t->get_var("edit_image");

			}
		}
	}

	// check products image settings
	$tiny_width = get_setting_value($settings, "tiny_image_max_width", 32);
	$tiny_height = get_setting_value($settings, "tiny_image_max_height", 32);
	$small_width = get_setting_value($settings, "small_image_max_width", 96);
	$small_height = get_setting_value($settings, "small_image_max_height", 96);
	$large_width = get_setting_value($settings, "big_image_max_width", 288);
	$large_height = get_setting_value($settings, "big_image_max_height", 288);
	$super_width = get_setting_value($settings, "super_image_max_width", 1024);
	$super_height = get_setting_value($settings, "super_image_max_height", 768);
	$t->set_var("tiny_width", $tiny_width);
	$t->set_var("tiny_height", $tiny_height);
	$t->set_var("small_width", $small_width);
	$t->set_var("small_height", $small_height);
	$t->set_var("large_width", $large_width);
	$t->set_var("large_height", $large_height);
	$t->set_var("super_width", $super_width);
	$t->set_var("super_height", $super_height);


	// check default images
	$default_tiny = ""; $default_small = ""; $default_large = ""; $default_super = "";
	if ($va_module == "articles") {
		$sql  = " SELECT image_tiny, image_small, image_large, image_super "; 
		$sql .= " FROM " . $parent_table." "; 
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER); 
		$db->query($sql);
		$db->next_record();	
		$default_tiny = $db->f("image_tiny");
		$default_small = $db->f("image_small");
		$default_large = $db->f("image_large");
		$default_super = $db->f("image_super");
	} else if ($va_module == "articles_categories") {
	} else {
		$sql  = " SELECT tiny_image, small_image, big_image, super_image "; 
		$sql .= " FROM " . $parent_table." "; 
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER); 
		$db->query($sql);
		$db->next_record();	
		$default_tiny = $db->f("tiny_image");
		$default_small = $db->f("small_image");
		$default_large = $db->f("big_image");
		$default_super = $db->f("super_image");
	}

	if ($default_large) {
		$large_preview_src = $default_large;
		if (!preg_match("/^\//", $large_preview_src) && !preg_match("/^http/i", $large_preview_src)) {
			$large_preview_src = "../".$large_preview_src;
		}
		$t->set_var("large_preview_src", htmlspecialchars($large_preview_src));
		$t->set_var("general_image_src", htmlspecialchars($large_preview_src));
		$t->set_var("large_image_class", "largeImage");
		$t->set_var("general_image_class", "general-preview-image");
	} else {
		$t->set_var("large_image_class", "largeImageHidden");
		$t->set_var("general_image_class", "hidden");
	}

	$images_json = array();
	$yes_icon = "../images/icons/yes.png";
	$no_icon = "../images/icons/no.png";
	$sql = " SELECT * FROM " . $images_table." ";
	if ($va_module == "articles") {
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
	} else if ($va_module == "articles_categories") {
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
	} else {
		$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
	}
	$sql.= " ORDER BY image_order, image_id ";
	$db->query($sql);
	while($db->next_record()) {
		$image_id = $db->f("image_id");
		$is_image_default = $db->f("is_default");
		$image_title = get_translation($db->f("image_title"));
		$image_tiny = $db->f("image_tiny");
		$image_tiny_alt = $db->f("image_tiny_alt");
		$image_small = $db->f("image_small");
		$image_small_alt = $db->f("image_small_alt");
		$image_large = $db->f("image_large");
		$image_large_alt = $db->f("image_large_alt");
		$image_super = $db->f("image_super");
		$image_super_alt = $db->f("image_super_alt");
		$image_position = $db->f("image_position");

		$image_default = "";
		if ($is_image_default) {
			$image_default = " checked=\"checked\" ";
		}

		$admin_image_tiny = $image_tiny;
		$admin_image_small = $image_small;
		$admin_image_large = $image_large;
		$admin_image_super = $image_super;

		if ($image_tiny && !preg_match("/^\//", $image_tiny) && !preg_match("/^http/i", $image_tiny)) {
			$admin_image_tiny = "../".$image_tiny;
		}
		if ($image_small && !preg_match("/^\//", $image_small) && !preg_match("/^http/i", $image_small)) {
			$admin_image_small = "../".$image_small;
		}
		if ($image_large && !preg_match("/^\//", $image_large) && !preg_match("/^http/i", $image_large)) {
			$admin_image_large = "../".$image_large;
		}
		if ($image_super && !preg_match("/^\//", $image_super) && !preg_match("/^http/i", $image_super)) {
			$admin_image_super = "../".$image_super;
		}
		$images_json[$image_id]	= array(
			"image_tiny" => $image_tiny,
			"image_small" => $image_small,
			"image_large" => $image_large,
			"image_super" => $image_super,
			"image_tiny_alt" => $image_tiny_alt,
			"image_small_alt" => $image_small_alt,
			"image_large_alt" => $image_large_alt,
			"image_super_alt" => $image_super_alt,
			"admin_image_tiny" => $admin_image_tiny,
			"admin_image_small" => $admin_image_small,
			"admin_image_large" => $admin_image_large,
			"admin_image_super" => $admin_image_super,
		);


		$tiny_icon = ($image_tiny) ? $yes_icon : $no_icon;
		$small_icon = ($image_small) ? $yes_icon : $no_icon;
		$large_icon = ($image_large) ? $yes_icon : $no_icon;
		$super_icon = ($image_super) ? $yes_icon : $no_icon;

		$t->set_var("image_id", $image_id);
		$t->set_var("image_default", $image_default);
		$t->set_var("tiny_icon", htmlspecialchars($tiny_icon));
		$t->set_var("small_icon", htmlspecialchars($small_icon));
		$t->set_var("large_icon", htmlspecialchars($large_icon));
		$t->set_var("super_icon", htmlspecialchars($super_icon));
		if ($image_position == 1) {
			$t->set_var("image_position", "separate");
		} else if ($image_position == 2) {
			$t->set_var("image_position", "top");
		} else {
			$t->set_var("image_position", "hidden");
		}

		$t->set_var("image_title", htmlspecialchars($image_title));

		if ($image_position == 2) {
			$t->set_var("admin_image_small", $admin_image_small);
			$t->parse("top_small_images", $image_small);
		}

		$t->parse("item_images", true);
	}
	if ($ajax) {
		$ajax_response["images_json"] = $images_json;
	} else {
		$t->set_var("images_list_json", json_encode($images_json));
	}

	if (!$block || $block == "all" || $block == "images_functions") {
		$t->parse("images_functions");
		if ($ajax) {
			$ajax_response["images_functions"] = $t->get_var("images_functions");
		}
	}
	if (!$block || $block == "all" || $block == "images_preview") {
		$t->parse("images_preview");
		if ($ajax) {
			$ajax_response["images_preview"] = $t->get_var("images_preview");
		}
	}
	if (!$block || $block == "all" || $block == "images_list") {
		$t->parse("images_list");
		if ($ajax) {
			$ajax_response["images_list"] = $t->get_var("images_list");
		}
	}
	if (!$block || $block == "all" || $block == "images_upload") {
		if ($va_module == "articles") {
			$upload_image_position = get_admin_settings("articles_image_position");
		} else if ($va_module == "articles_categories") {
			$upload_image_position = get_admin_settings("articles_categories_image_position");
		} else {
			$upload_image_position = get_admin_settings("products_image_position");
		}
		if (!strlen($upload_image_position)) { $upload_image_position = 2; }

		set_options($image_positions, $upload_image_position, "upload_image_position");

		$t->parse("images_upload");
		if ($ajax) {
			$ajax_response["images_upload"] = $t->get_var("images_upload");
		}
	}

	if ($ajax) {
		echo json_encode($ajax_response);
		return;
	}


	function check_default_image()
	{
		global $r, $ri, $db, $table_prefix, $images_table, $parent_table, $va_module;
		if ($ri->get_value("is_image_default")) {

			if ($va_module == "articles") {
				// remove default option for all images
				$sql  = " UPDATE " . $images_table." ";
				$sql .= " SET is_default=0 ";
				$sql .= " WHERE article_id=" . $db->tosql($ri->get_value("article_id"), INTEGER);
				$db->query($sql);

				// update default image
				$sql = " UPDATE " . $parent_table." ";
				$sql.= " SET image_tiny=" . $db->tosql($ri->get_value("image_tiny"), TEXT);
				$sql.= ", image_tiny_alt=" . $db->tosql($ri->get_value("image_tiny_alt"), TEXT);
				$sql.= ", image_small=" . $db->tosql($ri->get_value("image_small"), TEXT);
				$sql.= ", image_small_alt=" . $db->tosql($ri->get_value("image_small_alt"), TEXT);
				$sql.= ", image_large=" . $db->tosql($ri->get_value("image_large"), TEXT);
				$sql.= ", image_large_alt=" . $db->tosql($ri->get_value("image_large_alt"), TEXT);
				$sql.= ", image_super=" . $db->tosql($ri->get_value("image_super"), TEXT);
				$sql.= ", image_super_alt=" . $db->tosql($ri->get_value("image_super_alt"), TEXT);
				$sql.= " WHERE article_id=" . $db->tosql($ri->get_value("article_id"), INTEGER);
				$db->query($sql);
			} else if ($va_module == "articles_categories") {
			} else {
				// remove default option for all images
				$sql  = " UPDATE " . $images_table." ";
				$sql .= " SET is_default=0 ";
				$sql .= " WHERE item_id=" . $db->tosql($ri->get_value("item_id"), INTEGER);
				$db->query($sql);

				// update default image
				$sql = " UPDATE " . $parent_table." ";
				$sql.= " SET tiny_image=" . $db->tosql($ri->get_value("image_tiny"), TEXT);
				$sql.= ", tiny_image_alt=" . $db->tosql($ri->get_value("image_tiny_alt"), TEXT);
				$sql.= ", small_image=" . $db->tosql($ri->get_value("image_small"), TEXT);
				$sql.= ", small_image_alt=" . $db->tosql($ri->get_value("image_small_alt"), TEXT);
				$sql.= ", big_image=" . $db->tosql($ri->get_value("image_large"), TEXT);
				$sql.= ", big_image_alt=" . $db->tosql($ri->get_value("image_large_alt"), TEXT);
				$sql.= ", super_image=" . $db->tosql($ri->get_value("image_super"), TEXT);
				$sql.= ", super_image_alt=" . $db->tosql($ri->get_value("image_super_alt"), TEXT);
				$sql.= " WHERE item_id=" . $db->tosql($ri->get_value("item_id"), INTEGER);
				$db->query($sql);
			}
		}
	}
	

	function check_parent_image()
	{
		global $r, $db, $table_prefix, $images_table, $parent_table, $va_module, $article_id, $category_id, $item_id;
		
		$parent_tiny = ""; $parent_small = ""; $parent_large = ""; $parent_super = "";
		$parent_tiny_alt = ""; $parent_small_alt = ""; $parent_large_alt = ""; $parent_super_alt = "";
		if ($va_module == "articles" || $va_module == "articles_categories") {
			$sql  = " SELECT * FROM " . $parent_table . " ";
			if ($va_module == "articles") {
				$sql.= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
			} else {
				$sql.= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			}
			$db->query($sql);
			if ($db->next_record()) {
				$parent_tiny = $db->f("image_tiny");
				$parent_small = $db->f("image_small");
				$parent_large = $db->f("image_large");
				$parent_super = $db->f("image_super");
				$parent_tiny_alt = $db->f("image_tiny_alt");
				$parent_small_alt = $db->f("image_small_alt");
				$parent_large_alt = $db->f("image_large_alt");
				$parent_super_alt = $db->f("image_super_alt");
			}
		} else {
			$sql  = " SELECT * FROM " . $parent_table . " ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$parent_tiny = $db->f("tiny_image");
				$parent_small = $db->f("small_image");
				$parent_large = $db->f("big_image");
				$parent_super = $db->f("super_image");
				$parent_tiny_alt = $db->f("tiny_image_alt");
				$parent_small_alt = $db->f("small_image_alt");
				$parent_large_alt = $db->f("big_image_alt");
				$parent_super_alt = $db->f("super_image_alt");
			}
		}

		if ($parent_tiny || $parent_small || $parent_large || $parent_super) {
			$sql  = " SELECT image_id,is_default FROM ".$images_table." ";
			if ($va_module == "articles") {
				$sql.= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
			} else if ($va_module == "articles_categories") {
				$sql.= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			} else {
				$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
			}
			if ($parent_tiny) {
				$sql .= " AND image_tiny=" . $db->tosql($parent_tiny, TEXT);
			} else {
				$sql .= " AND (image_tiny IS NULL OR image_tiny='') ";
			}
			if ($parent_small) {
				$sql .= " AND image_small=" . $db->tosql($parent_small, TEXT);
			} else {
				$sql .= " AND (image_small IS NULL OR image_small='') ";
			}
			if ($parent_large) {
				$sql .= " AND image_large=" . $db->tosql($parent_large, TEXT);
			} else {
				$sql .= " AND (image_large IS NULL OR image_large='') ";
			}
			if ($parent_super) {
				$sql .= " AND image_super=" . $db->tosql($parent_super, TEXT);
			} else {
				$sql .= " AND (image_super IS NULL OR image_super='') ";
			}
			$db->query($sql);
			if ($db->next_record()) {
				$image_id = $db->f("image_id");
				$is_default = $db->f("is_default");
				if (!$is_default) {
					// set default option for found image
					$sql  = " UPDATE ".$images_table." ";
					$sql .= " SET is_default=1 ";
					$sql .= " WHERE image_id=" . $db->tosql($image_id, INTEGER);
					$db->query($sql);
				}
			} else {
				// add new default image
				$ii = new VA_Record($images_table);
				$ii->add_where("image_id", INTEGER);
				if ($va_module == "articles") {
					$ii->add_textbox("article_id", INTEGER);
					$ii->set_value("article_id", $article_id);
				} else if ($va_module == "articles_categories") {
					$ii->add_textbox("category_id", INTEGER);
					$ii->set_value("category_id", $category_id);
				} else {
					$ii->add_textbox("item_id", INTEGER);
					$ii->set_value("item_id", $item_id);
				}
				$ii->add_textbox("is_default", INTEGER);
				$ii->add_textbox("image_order", INTEGER);
				$ii->add_textbox("image_position", INTEGER);
				$ii->add_textbox("image_title", TEXT);
				$ii->add_textbox("image_description", TEXT);
				$ii->add_textbox("image_tiny", TEXT);
				$ii->add_textbox("image_small", TEXT);
				$ii->change_property("image_small", USE_SQL_NULL, false);
				$ii->add_textbox("image_large", TEXT);
				$ii->change_property("image_large", USE_SQL_NULL, false);
				$ii->add_textbox("image_super", TEXT);
				$ii->add_textbox("image_tiny_alt", TEXT);
				$ii->add_textbox("image_small_alt", TEXT);
				$ii->add_textbox("image_large_alt", TEXT);
				$ii->add_textbox("image_super_alt", TEXT);

				if ($parent_super) {
					$filename = $parent_super;
				} else if ($parent_large) {
					$filename = $parent_large;
				} else if ($parent_small) {
					$filename = $parent_small;
				} else {
					$filename = $parent_tiny;
				}

				// set values
				$ii->set_value("is_default", 1);
				$ii->set_value("image_order", 1);
				$ii->set_value("image_position", 2);
				$ii->set_value("image_title", basename($filename));
				$ii->set_value("image_description", "");
				$ii->set_value("image_tiny", $parent_tiny);
				$ii->set_value("image_small", $parent_small);
				$ii->set_value("image_large", $parent_large);
				$ii->set_value("image_super", $parent_super);
				$ii->set_value("image_tiny_alt", $parent_tiny_alt);
				$ii->set_value("image_small_alt", $parent_small_alt);
				$ii->set_value("image_large_alt", $parent_large_alt);
				$ii->set_value("image_super_alt", $parent_super_alt);
				$ii->insert_record();
			}
		}
	}

?>