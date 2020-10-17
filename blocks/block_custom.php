<?php

	$default_title = "{custom_title}";

	$block_number = $vars["block_key"];
	
	$var_css_class = get_setting_value($vars, "cb_css_class", ""); // old parameter used in previous verison [DELETE]
	$user_type = get_setting_value($vars, "cb_user_type", "");
	$admin_type = get_setting_value($vars, "cb_admin_type", "");
	$params = get_setting_value($vars, "cb_params", "");
	$block_type = get_setting_value($vars, "block_type", "");
	$popup_type = get_setting_value($vars, "popup_type", "");

	$user_check = true;
	if (strlen($user_type)) {
		if (strtoupper($user_type) == "NON") {
			if (strlen(get_session("session_user_id"))) {
				$user_check = false;
			}
		} else if (strtoupper($user_type) == "ANY") {
			if (!strlen(get_session("session_user_id"))) {
				$user_check = false;
			}
		} else if (strtoupper($user_type) != "ALL") {
			if ($user_type != get_session("session_user_type_id")) {
				$user_check = false;
			}
		}
	}

	if (!$user_check && !strlen($admin_type)) {
		return;
	}

	$admin_check = true;
	if (strlen($admin_type)) {
		if (strtoupper($admin_type) == "ANY") {
			if (!strlen(get_session("session_admin_id"))) {
				$admin_check = false;
			}
		} else {
			if ($admin_type != get_session("session_admin_privilege_id")) {
				$admin_check = false;
			}
		}
	}

	if (!$admin_check && (!$user_check || !strlen($user_type))) {
		return;
	}



	if (strlen($params)) {
		$pairs = explode(";", $params);
		for ($i = 0; $i < sizeof($pairs); $i++) {
			$pair = explode("=", trim($pairs[$i]), 2);
			if (sizeof($pair) == 2) {
				list($param_name, $param_value) = $pair;
				if ($param_name == "category" || $param_name == "category_id") {
					$current_value = get_param("category_id");
					if (!strlen($current_value) && ($cms_page_code == "products_list" || $cms_page_code == "ads_list") ) {
						$current_value = "0";
					}
				} else if ($param_name == "item" || $param_name == "product" || $param_name == "product_id") {
					$current_value = get_param("item_id");
				} else if ($param_name == "user" || $param_name == "user_id") {
					$current_value = get_session("session_user_id");
				} else if ($param_name == "user_type" || $param_name == "user_group") {
					$current_value = get_session("session_user_type_id");
				} else if ($param_name == "profile_country" || $param_name == "profile_country_code" || $param_name == "user_country" || $param_name == "user_country_code") {
					$user_info = get_session("session_user_info");
					$current_value = strtolower(get_setting_value($user_info, "country_code", ""));
				} else if ($param_name == "profile_delivery_country" || $param_name == "profile_delivery_country_code" || $param_name == "user_delivery_country" || $param_name == "user_delivery_country_code") {
					$user_info = get_session("session_user_info");
					$current_value = strtolower(get_setting_value($user_info, "delivery_country_code", ""));
				} else if ($param_name == "country" || $param_name == "country_code" || $param_name == "ip_country" || $param_name == "ip_country_code") {
					$current_value = strtolower(get_setting_value($_SERVER, "GEOIP_COUNTRY_CODE"));  // check country code from GeoIP service
				} else if ($param_name == "site" || $param_name == "site_id") {
					$current_value = $site_id;
				} else {
					$current_value = strtolower(get_param($param_name));
				}
				$group_value = strlen($current_value) ? "any" : "non";
				$param_value = str_replace(" ", "", $param_value);
				$param_values = explode(",", strtolower($param_value));
				if (!in_array($current_value, $param_values) && !in_array($group_value, $param_values)) {
					return;
				}
			}
		}
	}


  $sql  = " SELECT block_name, block_title, block_class, block_path, block_desc FROM " . $table_prefix . "custom_blocks ";
  $sql .= " WHERE block_id=" . intval($block_number);
	$db->query($sql);
	if($db->next_record()) {
		$block_css_class = $db->f("block_class");
		// add custom parameter as well
		//$block_class = trim($block_class." ".$css_class);
		$custom_title = get_translation($db->f("block_title"));
		$block_path = $db->f("block_path");
		$custom_body = "";
		if ($block_path) {
			$file_path = $block_path;
			if (!preg_match("/^http(s)?:\/\//", $file_path) && !file_exists($file_path)) {
				// check default dir for file
			  $t->set_file("custom_body", $file_path);
			  $t->parse("custom_body", false);
			  $custom_body = $t->get_var("custom_body");

			} else {
				$custom_body = join("", file($file_path));
			}
		} else {
			$custom_body = get_translation($db->f("block_desc"));
		}
		$custom_body = get_translation($custom_body);
		// get currency message could run db query so call them last
		$custom_title = get_currency_message($custom_title, $currency);
		$custom_body = get_currency_message($custom_body, $currency);
		//eval_php_code($custom_body);
	} else {
		return;
	}
	
	if(!strlen($custom_body) && !strlen($custom_title)) {
		return;
	}

	if ($block_type != "header") {
		$html_template = get_setting_value($block, "html_template", "block_custom.html"); 
		$t->set_file("block_body", $html_template);
	}
	if ($popup_type) {
		$t->sparse("close_icon", false);
		// override default block class bk-custom-block
		$cms_css_class = "bk-custom-popup";
	} else {
		$t->set_var("close_icon", "");
	}

	$t->set_block("custom_title", $custom_title);
	$t->parse("custom_title", false);
	$t->set_block("custom_body", $custom_body);
	$t->parse("custom_body", false);

	$block_parsed = true;
