<?php

	// set necessary scripts
	set_script_tag("js/ajax.js");

	$default_title = "";

	$group_id = $vars["block_key"];

	$bg_limit = get_setting_value($vars, "bg_limit", 1);
	$params = get_setting_value($vars, "bg_params", "");
	$slider_type = get_setting_value($vars, "slider_type", "");
	$slider_type_code = ""; $slider_type_text = "";
	$transition_delay = get_setting_value($vars, "transition_delay", "");
	$transition_duration = get_setting_value($vars, "transition_duration", "");
	$data_js = ($slider_type) ? "slideshow" : ""; 

	// convert slider type to text and code values
	if ($slider_type == "1" || $slider_type == "vertical-up") { // vertical
		$slider_type = 1;
		$slider_type_code = 1;
		$slider_type_text = "vertical-up";
	} else if ($slider_type == "3" || $slider_type == "vertical-down") { // vertical
		$slider_type = 3;
		$slider_type_code = 3;
		$slider_type_text = "vertical-down";
	} else if ($slider_type == "2" || $slider_type == "horizontal-left") { // horizontal
		$slider_type = 2;
		$slider_type_code = 2;
		$slider_type_text = "horizontal-left";
	} else if ($slider_type == "4" || $slider_type == "horizontal-right") { // horizontal
		$slider_type = 4;
		$slider_type_code = 4;
		$slider_type_text = "horizontal-right";
	} else if ($slider_type == "5" || $slider_type == "slideshow") { // slideshow
		$slider_type = 5;
		$slider_type_code = 5;
		$slider_type_text = "slideshow";
	}
	$slides_class = "banners-".$slider_type_text;

	if (strlen($params)) {
		$pairs = explode(";", $params);
		for ($i = 0; $i < sizeof($pairs); $i++) {
			$pair = explode("=", $pairs[$i], 2);
			if (sizeof($pair) == 2) {
				list($param_name, $param_value) = $pair;
				if ($param_name == "category" || $param_name == "category_id") {
					$current_value = get_param("category_id");
					if (!strlen($current_value)) {
						$current_value = "0";
					}
				} else if ($param_name == "item" || $param_name == "product" || $param_name == "product_id") {
					$current_value = get_param("item_id");
				} else if ($param_name == "user" || $param_name == "user_id") {
					$current_value = get_session("session_user_id");
				} else {
					$current_value = get_param($param_name);
				}
				$param_values = explode(",", $param_value);
				if (!in_array($current_value, $param_values)) {
					return;
				}
			}
		}
	}

	$html_template = get_setting_value($block, "html_template", "block_banners.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("banners", "");
	$t->set_var("banner_style", "");
	$t->set_var("data_js", htmlspecialchars($data_js));
	$t->set_var("slider_type", htmlspecialchars($slider_type));
	$t->set_var("slides_class", htmlspecialchars($slides_class));
	$t->set_var("transition_delay", htmlspecialchars($transition_delay));
	$t->set_var("transition_duration", htmlspecialchars($transition_duration));

	$banner_index = 0; $banners_ids = ""; $banners_sort = array(); $banners = array();
	$sql  = " SELECT b.* FROM (((";
	$sql .= $table_prefix . "banners b ";
	$sql .= " INNER JOIN " . $table_prefix . "banners_assigned ba ON b.banner_id=ba.banner_id) ";
	$sql .= " INNER JOIN " . $table_prefix . "banners_groups bg ON ba.group_id=bg.group_id) ";
	$sql .= " LEFT JOIN " . $table_prefix . "banners_sites bs ON bs.banner_id=b.banner_id) ";
	$sql .= " WHERE bg.group_id=" . $db->tosql($group_id, INTEGER);
	$sql .= " AND bg.is_active=1 ";
	$sql .= " AND b.is_active=1 ";
	$sql .= " AND (b.max_impressions=0 OR b.max_impressions>b.total_impressions) ";
	$sql .= " AND (b.max_clicks=0 OR b.max_clicks>b.total_clicks) ";
	$sql .= " AND (b.expiry_date IS NULL OR b.expiry_date>=" . $db->tosql(va_time(), DATETIME). ") ";
	if (strtolower(get_var("HTTPS")) == "on") {
	  $sql .= " AND b.show_on_ssl=1 ";
	}
	if (isset($site_id)) {
		$sql .= " AND (b.sites_all=1 OR bs.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
	} else {
		$sql .= " AND b.sites_all=1 ";
	}
	$sql .= " ORDER BY b.banner_rank ";
	$db->RecordsPerPage = 100;
	$db->PageNumber = 1;
	$db->query($sql);
	while ($db->next_record()) {
		$banner_id = $db->f("banner_id");
		$banner_rank = $db->f("banner_rank");
		if ($banner_rank < 1) { $banner_rank = 1; }
		$banner_title = get_translation($db->f("banner_title"));
		$show_title = $db->f("show_title");
		$image_src = $db->f("image_src");
		$image_alt= $db->f("image_alt");
		$is_new_window = $db->f("is_new_window");
		$html_text = get_translation($db->f("html_text"));
		$banners_sort[$banner_id] = mt_rand(1, $banner_rank*100);
		$banners[$banner_id] = array(
			"id" => $banner_id, "banner_id" => $banner_id,
			"banner_title" => $banner_title, "show_title" => $show_title,
			"image_src" => $image_src, "image_alt" => $image_alt,
			"is_new_window" => $is_new_window, "html_text" => $html_text,
		);
	}

	// sort banners
	array_multisort($banners_sort, $banners);
	 
	foreach ($banners as $banner_id => $banner_data) {
		$banner_index++;
		if ($banner_index > $bg_limit) { break; }

		$banner_id = $banner_data["banner_id"];
		if (strlen($banners_ids)) { $banners_ids .= ","; }
		$banners_ids .= $banner_id;
		$bc_url = "bc.php?b=" . $banner_id;
		$ajax_bc_url = "bc.php?ajax=1&b=" . $banner_id;

		$banner_title = get_translation($banner_data["banner_title"]);
		$show_title = $banner_data["show_title"];
		$image_src = $banner_data["image_src"];
		$image_alt= $banner_data["image_alt"];
		$target = ($banner_data["is_new_window"] == 1) ? "_blank" : "_top";
		if (!strlen($image_alt)) { $image_alt = $banner_title; }
		$html_text = get_translation($banner_data["html_text"]);

		if ($banner_index > 1 && $slider_type == "slideshow") {
			$t->set_var("banner_style", "display: none;");
		}
		$t->set_var("banner_id", $banner_id);
		$t->set_var("bc_url", htmlspecialchars($bc_url));
		$t->set_var("ajax_bc_url", htmlspecialchars($ajax_bc_url));

		$t->set_var("target", $target);

  
		if (strlen($image_src)) {
			$t->set_var("alt", htmlspecialchars($image_alt));
			$t->set_var("src", htmlspecialchars($image_src));
			$t->parse("banner_image", false);
		} else {
			$t->set_var("banner_image", "");
		}
  
		if ($show_title) {
			$t->set_var("banner_title", $banner_title);
			$t->parse("title_block", false);
		} else {
			$t->set_var("title_block", "");
		}
		$t->set_block("html_text", $html_text);
		$t->parse("html_text", false);

		$t->parse("banners", true);
	}

	if (strlen($banners_ids)) {

		if(!isset($layout_type) || !$layout_type) { $layout_type = "aa"; }
		$block_parsed = true;
  
		// add one impression
		$sql  = " UPDATE " . $table_prefix . "banners ";
		$sql .= " SET total_impressions=total_impressions+1 ";
		$sql .= " WHERE banner_id IN (" . $db->tosql($banners_ids, INTEGERS_LIST) . ")";
		$db->query($sql);
	}

