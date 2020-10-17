<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_tags.php                                           ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	check_admin_security("");

	$win_type = get_param("win_type");

	$sw = trim(get_param("sw"));
	$form_name = get_param("form_name");
	$field_name = get_param("field_name");
	$selection_type = get_param("selection_type");
	$items_field = get_param("items_field");
	$items_object = get_param("items_object");
	$item_template = get_param("item_template");
	$win_type = get_param("win_type");
	$sort_ord = get_param("sort_ord");
	$sort_dir = get_param("sort_dir");

	$custom_breadcrumb = array(
		"admin_global_settings.php" => SETTINGS_MSG,
		"admin_static_tables.php" => STATIC_TABLES_MSG,
		"admin_tags.php" => TAGS_MSG,
	);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_tags.html");
	if ($win_type == "popup") {
		$t->set_var("colspan", "4");
		$t->parse("select_column", false);
	} else {
		$t->set_var("colspan", "3");
		$t->set_var("select_column", "");
	}

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_tag_href", "admin_tag.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("items_field", htmlspecialchars($items_field));
	$t->set_var("items_object", htmlspecialchars($items_object));
	$t->set_var("item_template", htmlspecialchars($item_template));
	$t->set_var("selection_type", htmlspecialchars($selection_type));
	$t->set_var("win_type", htmlspecialchars($win_type));
	$t->set_var("sort_ord", htmlspecialchars($sort_ord));
	$t->set_var("sort_dir", htmlspecialchars($sort_dir));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_tags.php");
	$s->set_default_sorting(2, "asc");
	$s->set_sorter(ID_MSG, "sorter_tag_id", "1", "tag_id");
	$s->set_sorter(NAME_MSG, "sorter_tag_name", "2", "tag_name");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_tags.php");

	if ($win_type != "popup") {
		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	$admin_tag_url = new VA_URL("admin_tag.php", false);
	$admin_tag_url->add_parameter("sw", REQUEST, "sw");
	$admin_tag_url->add_parameter("form_name", REQUEST, "form_name");
	$admin_tag_url->add_parameter("items_field", REQUEST, "items_field");
	$admin_tag_url->add_parameter("items_object", REQUEST, "items_object");
	$admin_tag_url->add_parameter("item_template", REQUEST, "item_template");
	$admin_tag_url->add_parameter("selection_type", REQUEST, "selection_type");
	$admin_tag_url->add_parameter("win_type", REQUEST, "win_type");
	$admin_tag_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$admin_tag_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$admin_tag_url->add_parameter("page", REQUEST, "page");
	$t->set_var("admin_tag_new_url", htmlspecialchars($admin_tag_url->get_url()));
	$admin_tag_url->add_parameter("tag_id", DB, "tag_id");

	$where = ""; $regexp = ""; 
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			$kw = trim($sa[$si]);
			// build regexp first
			if (strlen($regexp)) { $regexp .= "|"; }
			$regexp .= preg_quote($kw, "/");
			// build where condition
			$kw = str_replace("%","\%",$kw);
			if ($kw) {
				if ($where) {
					$where .= " AND ";
				} else {
					$where .= " WHERE ";
				}
				$where .= " (tag_name LIKE '%" . $db->tosql($kw, TEXT, false) . "%' )";
			}
		}
	}

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "tags " . $where);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 10;
	$n->set_parameters(true, true, false);
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "tags " . $where . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {
			$tag_id = $db->f("tag_id");
			$tag_name = $db->f("tag_name");
			if (strlen($regexp)) {
				$tag_name_highlight = preg_replace ("/(" . $regexp . ")/i", "<span class=\"highlight\">\\1</span>", $tag_name);
			} else {
				$tag_name_highlight = $tag_name;
			}

			$t->set_var("tag_id", htmlspecialchars($tag_id));
			$t->set_var("tag_name", $tag_name_highlight);

			// for poup window show select option
			if ($win_type == "popup") {
				$item_params = array(
					"id" => $tag_id, 
					"tag_id" => $tag_id, 
					"tag_name" => htmlspecialchars(strip_tags($tag_name)), 
				);
				$params = array(
					"form_name" => $form_name,
					"items_field" => $items_field,
					"items_object" => $items_object,
					"item_template" => $item_template,
					"item" => $item_params,
				);
				$json_params = htmlspecialchars(json_encode($params));
				$t->set_var("onclick", "jsonSelectItem(".$json_params.");");
				$t->parse("select_cell", false);
			} else {
				$t->set_var("select_cell", "");
			}

			$t->set_var("admin_tag_edit_url", htmlspecialchars($admin_tag_url->get_url()));
	
			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->pparse("main");

?>
