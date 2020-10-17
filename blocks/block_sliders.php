<?php 

	$default_title = "";

	$pb_id = $block["pb_id"];
	$block_id = $block["block_key"];

	$data_js = "slideshow";
	$slider_type = get_setting_value($vars, "slider_type", "5");
	$block_view_type = get_setting_value($vars, "block_view_type", "1");
	$slider_width = get_setting_value($vars, "slider_width", "");
	$slider_height = get_setting_value($vars, "slider_height", "");
	$transition_delay = get_setting_value($vars, "transition_delay", 5);
	$transition_duration = get_setting_value($vars, "transition_duration", 1);
	$nav_type = get_setting_value($vars, "nav_type", 1);
	$nav_pos = get_setting_value($vars, "nav_pos", "rm");

	$html_template = get_setting_value($block, "html_template", "block_sliders.html");

	$t->set_file("block_body", $html_template);
	$t->set_var("pb_id", $pb_id);
	$t->set_var("data_js", htmlspecialchars($data_js));
	$t->set_var("slider_type", htmlspecialchars($slider_type));
	$t->set_var("transition_delay", htmlspecialchars($transition_delay));
	$t->set_var("transition_duration", htmlspecialchars($transition_duration));
	$t->set_var("nav_type", htmlspecialchars($nav_type));
	$t->set_var("nav_pos", htmlspecialchars($nav_pos));

	$t->set_var("cols", "");
	$t->set_var("rows", "");

	// check slider default values
	$sql  = " SELECT slider_height, slider_width, slider_title ";
	$sql .= " FROM ". $table_prefix . "sliders";
	$sql .= " WHERE slider_id = " . $db->tosql($block_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		if (!$slider_width) {
			$slider_width = $db->f("slider_width");
		}
		if (!$slider_height) {
			$slider_height = $db->f("slider_height");
		}
		$default_title = $db->f("slider_title");
	}
	if ($block_view_type == 1) {
		if(!isset($layout_type) || !$layout_type) { $layout_type = "bk"; }
	} else if ($block_view_type == 2) {
		// content and borders
		$default_title = ""; // clear any title
		if(!isset($layout_type) || !$layout_type) { $layout_type = "bk"; }
	}	else {
		// content only
		$default_title = ""; // clear any title
		if(!isset($layout_type) || !$layout_type) { $layout_type = "aa"; }
	}

	if ($slider_type == "1" || $slider_type == "3") { // vertical
		$t->set_var("slides_class", "slides-vertical");
	} else if ($slider_type == "2" || $slider_type == "4") { // horizontal
		$t->set_var("slides_class", "slides-horizontal");
	} else if ($slider_type == "5") { // slideshow
		$t->set_var("slides_class", "slides-slideshow");
	}
	$slides_style = "";
	if (strlen($slider_width)) {
		$slides_style .= "width: " . get_css_dim($slider_width) . "; ";
	}
	if (strlen($slider_height)) {
		$slides_style .= "height: " . get_css_dim($slider_height). "; ";
	}
	$t->set_var("slides_style", $slides_style);

	$row = 0;
	$sql  = " SELECT item_id, item_name, slider_image, slider_link, slider_html, item_order ";
	$sql .= " FROM " . $table_prefix . "sliders_items ";				
	$sql .= " WHERE slider_id=" . $db->tosql($block_id, INTEGER);
	$sql .= " AND show_for_user=1 ";
	$sql .= " ORDER BY item_order ";
	$db->query($sql);
	while ($db->next_record()) {
		$row++;
		$item_name = $db->f("item_name");
		$slider_html = $db->f("slider_html");
		$slider_image = $db->f("slider_image");
		$slider_link = $db->f("slider_link");

		if ($slider_type != "5") {
			$t->set_var("data_id", "data_".$pb_id);
		} else if ($slider_type == "5") {
			$t->set_var("data_id", "data_".$pb_id."_".$row);
			if ($row == 1) {
				$t->set_var("slide_style", "");
			} else {
				$t->set_var("slide_style", "position: absolute; visibility: hidden; top: 0; left: 0;");
			}
		}

		$t->set_var("slider_image", "");
		$t->set_var("slider_image_link", "");
		if ($slider_image) {
			$t->set_var("src", htmlspecialchars($slider_image));
			$t->set_var("alt", htmlspecialchars($item_name));
			if ($slider_link && $slider_link != "#") {
				$t->set_var("slider_link", htmlspecialchars($slider_link));
				$t->parse("slider_image_link", false);
			} else {
				$t->parse("slider_image", false);
			}
		}
		$t->set_var("slider_html", $slider_html);

		$t->parse("cols", false);
		$t->parse("rows", true);
	}

	$block_parsed = true;

?>