<?php

	$default_title = "{category_name}";

	// check if top_id is a parent of category_id parameter 
	$top_id = $block["block_key"];
	$category_id = get_param("category_id");
	$var_category_id = get_setting_value($vars, "category_id");

	$articles_category_id = ""; $articles_top_name = ""; $articles_category_name = ""; $active_category_path = "0,".$top_id;
	if ($var_category_id) {
		$category_data = VA_Articles_Categories::get_category_data($var_category_id);
	} else if ($category_id == $top_id) {
		$category_data = VA_Articles_Categories::get_category_data($top_id);
	} else if (($cms_page_code == "articles_list" || $cms_page_code == "article_details" || $cms_page_code == "article_reviews")
		&& $category_id) {
		$category_data = VA_Articles_Categories::get_category_data($category_id);
		$category_path = get_setting_value($category_data, "category_path");
		$category_ids = explode(",", $category_path);
		if (!in_array($top_id, $category_ids)) {
			$category_data = VA_Articles_Categories::get_category_data($top_id);
		}
	} else {
		$category_data = VA_Articles_Categories::get_category_data($top_id);
	}
		
	$desc_image    = get_setting_value($vars, "articles_category_image", 3);
	$desc_type     = get_setting_value($vars, "articles_category_desc_type", 2);

	$category_name = get_translation(get_setting_value($category_data, "category_name"));
	$category_image = ""; $category_image_alt = "";
	if ($desc_image == 3) {
		$category_image = get_setting_value($category_data, "image_large");
		$category_image_alt = get_translation(get_setting_value($category_data, "image_large_alt"));
	} elseif ($desc_image == 2) {
		$category_image = get_setting_value($category_data, "image_small");
		$category_image_alt = get_translation(get_setting_value($category_data, "image_small_alt"));
	}
	$category_description = "";
	if ($desc_type == 2) {
		$category_description = get_translation(get_setting_value($category_data, "full_description"));
	} elseif ($desc_type == 1) {
		$category_description = get_translation(get_setting_value($category_data, "short_description"));
	}

	$t->set_var("images", "");
	$t->set_var("links", "");

	if(strlen($category_description) || $category_image)
	{
		$html_template = get_setting_value($block, "html_template", "block_category_description.html"); 
	  $t->set_file("block_body", $html_template);

		if (strlen($category_image)) {
			if (!preg_match("/^http\:\/\//", $category_image)) {
				if (isset($restrict_categories_images) && $restrict_categories_images) { $category_image = "image_show.php?art_cat_id=".$category_id."&type=large"; }
			}
			if (!strlen($category_image_alt)) { $category_image_alt = $category_name; }
			$t->set_var("alt", htmlspecialchars($category_image_alt));
			$t->set_var("src", htmlspecialchars($category_image));
			$t->sparse("image_large_block", false);
		} else {
			$t->set_var("image_large_block", "");
		}

		$t->set_var("category_name", $category_name);
		$t->set_var("full_description", $category_description);

		$block_parsed = true;
	}

	/*
	$sql  = " SELECT * FROM " . $table_prefix . "articles_images ";
	$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		set_script_tag("js/images.js");
		$block_parsed = true;
		do { 
			$image_title = $db->f("image_title");
			$image_src = $db->f("image_small");
			$image_alt = $db->f("image_small_alt");
			if (!$image_alt) { $image_alt = $image_title; }
			$image_url = $db->f("image_large");

			$t->set_var("image_alt", htmlspecialchars($image_alt));
			$t->set_var("image_src", htmlspecialchars($image_src));
			$t->set_var("image_url", htmlspecialchars($image_url));
			$t->set_var("image_title", htmlspecialchars($image_title));
			$t->parse("images", true);
		} while ($db->next_record());
	}

	$sql = " SELECT * FROM " . $table_prefix . "articles_links ";
	$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$right_desc = true;
		$block_parsed = true;
		do { 
			$link_url = $db->f("link_url");
			$link_title = $db->f("link_title");

			$t->set_var("link_title", htmlspecialchars($link_title));
			$t->set_var("link_url", htmlspecialchars($link_url));
			$t->parse("links", true);
		} while ($db->next_record());
	}//*/

?>