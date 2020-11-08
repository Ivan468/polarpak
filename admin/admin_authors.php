<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_authors.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
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

	$sw = trim(get_param("sw"));
	$form_name = get_param("form_name");
	$items_field = get_param("items_field");
	$items_object = get_param("items_object");
	$item_template = get_param("item_template");
	$selection_type = get_param("selection_type");
	$win_type = get_param("win_type");
	$sort_ord = get_param("sort_ord");
	$sort_dir = get_param("sort_dir");

	$custom_breadcrumb = array(
		"admin_global_settings.php" => SETTINGS_MSG,
		"admin_lookup_tables.php" => STATIC_TABLES_MSG,
		"admin_authors.php" => AUTHORS_MSG,
	);

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_authors.html");
	if ($win_type == "popup") {
		$t->set_var("colspan", "4");
		$t->parse("select_column", false);
	} else {
		$t->set_var("colspan", "3");
		$t->set_var("select_column", "");
	}

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_lookup_tables_href", "admin_lookup_tables.php");
	$t->set_var("admin_author_href", "admin_author.php");

	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("items_field", htmlspecialchars($items_field));
	$t->set_var("items_object", htmlspecialchars($items_object));
	$t->set_var("item_template", htmlspecialchars($item_template));
	$t->set_var("selection_type", htmlspecialchars($selection_type));
	$t->set_var("win_type", htmlspecialchars($win_type));
	$t->set_var("sort_ord", htmlspecialchars($sort_ord));
	$t->set_var("sort_dir", htmlspecialchars($sort_dir));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_authors.php");
	$s->set_default_sorting(2, "asc");
	$s->set_sorter(ID_MSG, "sorter_author_id", "1", "author_id");
	$s->set_sorter(AUTHOR_NAME_MSG, "sorter_author_name", "2", "author_name");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_authors.php");

	if ($win_type != "popup") {
		include_once("./admin_header.php");
		include_once("./admin_footer.php");
	}

	$admin_author_url = new VA_URL("admin_author.php", false);
	$admin_author_url->add_parameter("sw", REQUEST, "sw");
	$admin_author_url->add_parameter("form_name", REQUEST, "form_name");
	$admin_author_url->add_parameter("items_field", REQUEST, "items_field");
	$admin_author_url->add_parameter("items_object", REQUEST, "items_object");
	$admin_author_url->add_parameter("item_template", REQUEST, "item_template");
	$admin_author_url->add_parameter("selection_type", REQUEST, "selection_type");
	$admin_author_url->add_parameter("win_type", REQUEST, "win_type");
	$admin_author_url->add_parameter("sort_ord", REQUEST, "sort_ord");
	$admin_author_url->add_parameter("sort_dir", REQUEST, "sort_dir");
	$admin_author_url->add_parameter("page", REQUEST, "page");
	$t->set_var("admin_author_new_url", htmlspecialchars($admin_author_url->get_url()));
	$admin_author_url->add_parameter("author_id", DB, "author_id");

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
				$where .= " (author_name LIKE '%" . $db->tosql($kw, TEXT, false) . "%' )";
			}
		}
	}

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "authors " . $where);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 10;
	$n->set_parameters(true, true, false);
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "authors " . $where . $s->order_by);
	if($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {

			$author_id = $db->f("author_id");
			$author_name = $db->f("author_name");
			if (strlen($regexp)) {
				$author_name_highlight = preg_replace ("/(" . $regexp . ")/i", "<span class=\"highlight\">\\1</span>", $author_name);
			} else {
				$author_name_highlight = $author_name;
			}

			$t->set_var("author_id", htmlspecialchars($author_id));
			$t->set_var("author_name", $author_name_highlight);

			// for poup window show select option
			if ($win_type == "popup") {
				$item_params = array(
					"id" => $author_id, 
					"author_id" => $author_id, 
					"author_name" => htmlspecialchars(strip_tags($author_name)), 
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

			$t->set_var("admin_author_edit_url", htmlspecialchars($admin_author_url->get_url()));
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