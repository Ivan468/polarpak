<?php

	$default_title = "{DESIGNS_MSG}";

	// block to show active designs
	$layout_id = get_setting_value($settings, "layout_id", "");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$design_selection = get_setting_value($vars, "design_selection", 1);

	$html_template = get_setting_value($block, "html_template", "block_designs.html"); 
  $t->set_file("block_body", $html_template);

	$remove_parameters = array();
	if ($friendly_urls && $page_friendly_url) {
		$current_page = $page_friendly_url . $friendly_extension;
		$query_string = transfer_params($page_friendly_params, true);
	} else {
		$query_string = transfer_params("", true);
	}
	$t->set_var("current_href", $current_page);

	$sql  = " SELECT l.layout_id, l.layout_name, l.user_layout_name ";
	$sql .= " FROM (" . $table_prefix . "layouts l ";
	if (isset($site_id))  {
		$sql .= " LEFT JOIN " . $table_prefix . "layouts_sites AS ls ON ls.layout_id=l.layout_id) ";
	} else {
		$sql .= " ) ";
	}
	$sql .= " WHERE l.show_for_user=1 ";
	if (isset($site_id))  {
		$sql .= " AND (l.sites_all=1 OR ls.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
	} else {
		$sql .= " AND l.sites_all=1 ";					
	}
	$sql .= " GROUP BY l.layout_id, l.layout_name, l.user_layout_name ";
	$sql .= " ORDER BY l.layout_id ";
	$db->query($sql);
	while ($db->next_record()) {
		$row_layout_id = $db->f("layout_id");
		$layout_name = get_translation($db->f("layout_name"));
		$user_layout_name = get_translation($db->f("user_layout_name"));
		if (strlen($user_layout_name)) { $layout_name = $user_layout_name; }

		$layout_selected = ($layout_id == $row_layout_id) ? "selected" : "";
		$t->set_var("layout_selected", $layout_selected);
		$t->set_var("layout_id", $row_layout_id);
		$t->set_var("layout_name", $layout_name);

		$layout_query = $query_string;
		if($layout_query) {
			$layout_query .= "&";
		} else {
			$layout_query .= "?";
		}
 		$layout_query .= "set_layout_id=" . $row_layout_id; 
		$layout_url = $current_page . $layout_query;
		$t->set_var("layout_url", htmlspecialchars($layout_url));

		$t->parse("layouts", true);
		$t->parse("layouts_options", true);
	}

	if ($design_selection == 2) {
		$t->sparse("layouts_select", false);
	} else {
		$t->sparse("layouts_list", false);
	}

	$block_parsed = true;

?>