<?php

	global $current_page;
	$default_title = va_message("CURRENCY_TITLE");
	
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$currency_selection = get_setting_value($vars, "currency_selection", 1);
	$tag_name = get_setting_value($vars, "tag_name", "");
	$block_type = get_setting_value($vars, "block_type");
	$template_type = get_setting_value($vars, "template_type", "");

	if ($block_type != "bar" && $block_type != "header" && $template_type != "built-in") {
		if ($template_type == "default") {
			$html_template = "block_currency.html"; 
		} else {
			$html_template = get_setting_value($block, "html_template", "block_currency.html"); 
		}
		if ($block_type == "sub-block") {
		  $t->set_file($vars["tag_name"], $html_template);
		} else {
		  $t->set_file("block_body", $html_template);
		}
	}

	$t->set_var("currencies", "");
	$t->set_var("currencies_images", "");

	$currency = get_currency();
	$currency_code = $currency["code"];

	$remove_parameters = array();
	if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
		$current_page = $page_friendly_url . $friendly_extension;
		$query_string = transfer_params($page_friendly_params, true);
	} else {
		$query_string = transfer_params("", true);
	}

	$active_currency_code = ""; $active_currency_name = ""; $active_currency_image = "";
	$sql  = " SELECT currency_code, currency_title, currency_image, currency_image_active ";
	$sql .= " FROM " . $table_prefix . "currencies ";
	$sql .= " WHERE show_for_user=1 ";
	$db->query($sql);
	while ($db->next_record()) 
	{
		$row_currency_code = $db->f("currency_code");
		$row_currency_title = $db->f("currency_title");
		$currency_image = $db->f("currency_image");
		$currency_image_active = $db->f("currency_image_active");
		$currency_selected = ($currency_code == $row_currency_code) ? "selected" : "";
		// if currency is a selected by user, make it "highlighted" use active image
		if ($currency_code == $row_currency_code && $currency_image_active != "") {
			$currency_image = $currency_image_active;
		}
		if ($currency_code == $row_currency_code) {
			$active_currency_code = $currency_code;
			$active_currency_name = $row_currency_title;
			$active_currency_image = $currency_image;
		}
		$currency_query = $query_string;
		if ($currency_query) {
			$currency_query .= "&";
		} else {
			$currency_query .= "?";
		}
		$currency_query .= "currency_code=" . $row_currency_code; 
		$currency_url = $current_page . $currency_query;
		$t->set_var("currency_selected", $currency_selected);
		$t->set_var("currency_code", $row_currency_code);
		$t->set_var("currency_code_lowercase", strtolower($row_currency_code));
		$t->set_var("currency_title", $row_currency_title);
		$t->set_var("currency_name", $row_currency_title);
		$t->set_var("currency_url", htmlspecialchars($currency_url));

		if ($currency_selection == 1 && $currency_image) {
			$t->set_var("src", htmlspecialchars($currency_image));
			$t->set_var("alt", htmlspecialchars($row_currency_title));
			$t->parse("currencies_images", true);
		} elseif ($currency_selection == "bar" || $currency_selection == "header") {
			if ($currency_image) {
				$t->set_var("src", htmlspecialchars($currency_image));
				$t->set_var("alt", htmlspecialchars($row_currency_title));
				$t->sparse("currency_image", false);
			} else {
				$t->set_var("currency_image", "");
			}
			$t->parse("currencies", true);
		} else {
			$t->parse("currencies", true);
		}

	}

	if ($currency_selection == 2) {
		$t->set_var("currencies_images", "");
		$t->sparse("select_currencies", false);
	} else if ($currency_selection == "bar" || $currency_selection == "header") {
		$t->set_var("active_code", htmlspecialchars($active_currency_code));
		$t->set_var("active_currency_code", htmlspecialchars($active_currency_code));
		$t->set_var("active_code_lowercase", strtolower($active_currency_code));
		$t->set_var("active_currency_code_lowercase", strtolower($active_currency_code));
		$t->set_var("active_currency_name", htmlspecialchars($active_currency_name));
		$t->set_var("alt", htmlspecialchars($active_currency_name));
		if ($active_currency_image) {
			$t->set_var("src", htmlspecialchars($active_currency_image));
			$t->sparse("active_currency_image", false);
		} else {
			$t->set_var("active_currency_image", "");
		}
	}

	$block_parsed = true;

