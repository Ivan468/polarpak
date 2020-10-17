<?php

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$default_title = va_message("LANGUAGE_TITLE");
	$language_selection = get_setting_value($vars, "language_selection", 1);
	$block_type = get_setting_value($vars, "block_type");

	if ($language_selection != "bar" && $language_selection != "header" && $block_type != "built-in") {
		$html_template = get_setting_value($block, "html_template", "block_language.html"); 
		$t->set_file("block_body", $html_template);
	}

	$remove_parameters = array();
	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$current_page = $page_friendly_url . $friendly_extension;
		$query_string = transfer_params($page_friendly_params, true);
	} else {
		$query_string = transfer_params("", true);
	}
	$t->set_var("current_href", $current_page);
	$t->set_var("languages", "");
	$t->set_var("languages_images", "");

	$active_language_code = ""; $active_language_name = ""; $active_language_image = "";
	$sql  = " SELECT language_code, language_name, language_image, language_image_active ";
	$sql .= " FROM " . $table_prefix . "languages WHERE show_for_user=1 ORDER BY language_order, language_code ";
	$db->query($sql);
	while ($db->next_record()) {
		$row_language_code = $db->f("language_code");
		$row_language_name = get_translation($db->f("language_name"));
		$language_image = $db->f("language_image");
		$language_image_active = $db->f("language_image_active");
		// if language is a selected by user, make it "highlighted" use active image if it's not empty
		if ($language_code == $row_language_code && $language_image_active != "") {
			$language_image = $language_image_active;
		}
		if ($language_code == $row_language_code) {
			$active_language_code = $language_code;  
			$active_language_name = $row_language_name;
			$active_language_image = $language_image;
		}
		$language_selected = ($language_code == $row_language_code) ? "selected=\"selected\"" : "";
		$language_query = $query_string;
		if ($language_query) {
			$language_query .= "&";
		} else {
			$language_query .= "?";
		}
 		$language_query .= "lang=" . $row_language_code; 
		$language_url = $current_page . $language_query;

		$t->set_var("language_selected", $language_selected);
		$t->set_var("language_code", $row_language_code);
		$t->set_var("language_code_lowercase", strtolower($row_language_code));
		$t->set_var("language_name", $row_language_name);
		$t->set_var("language_url", htmlspecialchars($language_url));
		if ($block_type == "built-in" || $language_selection == "bar" || $language_selection == "header") {
			if ($language_image) {
				$t->set_var("src", htmlspecialchars($language_image));
				$t->sparse("language_image", false);
			} else {
				$t->set_var("language_image", "");
			}
			$t->parse("languages", true);
		} else if ($language_selection == 1 && $language_image) {
			$t->set_var("src", htmlspecialchars($language_image));
			$t->set_var("alt", htmlspecialchars($row_language_name));
			$t->parse("languages_images", true);
		} else {
			$t->parse("languages", true);
		}
	}

	if ($block_type == "built-in" || $language_selection == "bar" || $language_selection == "header") {
		$t->set_var("active_code", htmlspecialchars($active_language_code));
		$t->set_var("active_language_code", htmlspecialchars($active_language_code));
		$t->set_var("active_code_lowercase", htmlspecialchars(strtolower($active_language_code)));
		$t->set_var("active_language_code_lowercase", htmlspecialchars(strtolower($active_language_code)));
		$t->set_var("active_name", htmlspecialchars($active_language_name));
		$t->set_var("active_language_name", htmlspecialchars($active_language_name));
		$t->set_var("alt", htmlspecialchars($active_language_name));
		if ($active_language_image) {
			$t->set_var("src", htmlspecialchars($active_language_image));
			$t->sparse("active_language_image", false);
		} else {
			$t->set_var("active_language_image", "");
		}
	} else if ($language_selection == 2) {
		$t->set_var("languages_images", "");
		$t->parse("select_languages", false);
	}


	$block_parsed = true;
