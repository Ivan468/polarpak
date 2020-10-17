<?php

	$default_title = "{category_name}";

	// show ads category info block only on listing and details page
	if ($cms_page_code != "ads_list" && $cms_page_code != "ad_details") {
		return;
	}

	$category_id = get_param("category_id");
	$desc_image = get_setting_value($vars, "ads_cat_desc_image", 3);
	$desc_type = get_setting_value($vars, "ads_cat_desc_type", 2);

	$category_name = ""; $category_image = ""; $category_description = "";
	if (isset($va_vars) && isset($va_vars["ads_c"]) && isset($va_vars["ads_c"][$category_id])) {
		$ad_category = $va_vars["ads_c"][$category_id];

		$category_name = get_translation(get_setting_value($ad_category, "category_name"));
		if ($desc_image == 3) {
			$category_image = get_setting_value($ad_category, "image_large");
		} elseif ($desc_image == 2) {
			$category_image = get_setting_value($ad_category, "image_small");
		}
		if ($desc_type == 2) {
			$category_description = get_translation(get_setting_value($ad_category, "full_description"));
		} elseif ($desc_type == 1) {
			$category_description = get_translation(get_setting_value($ad_category, "short_description"));
		}
	}


	if(strlen($category_description) || $category_image) {
		$html_template = get_setting_value($block, "html_template", "block_category_description.html"); 
	  $t->set_file("block_body", $html_template);
		if (strlen($category_image)) {
			if (preg_match("/^http\:\/\//", $category_image)) {
				$image_size = "";
			} else {
				$image_size = @GetImageSize($category_image);
			}
			$t->set_var("alt", htmlspecialchars($category_name));
			$t->set_var("src", htmlspecialchars($category_image));
			if(is_array($image_size)) {
				$t->set_var("width", "width=\"" . $image_size[0] . "\"");
				$t->set_var("height", "height=\"" . $image_size[1] . "\"");
			} else {
				$t->set_var("width", "");
				$t->set_var("height", "");
			}
			$t->sparse("image_large_block", false);
		} else {
			$t->set_var("image_large_block", "");
		}

		$t->set_var("category_name", $category_name);
		$t->set_var("full_description", $category_description);

		$block_parsed = true;
	}

