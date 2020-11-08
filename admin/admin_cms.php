<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_cms.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");
	include_once($root_folder_path."messages/".$language_code."/support_messages.php");
	include_once($root_folder_path."messages/".$language_code."/manuals_messages.php");
	include_once($root_folder_path."messages/".$language_code."/forum_messages.php");
	include_once($root_folder_path."messages/".$language_code."/profiles_messages.php");

	include_once("./admin_common.php");

	check_admin_security("cms_settings");

	$va_version_code = va_version_code();

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_cms.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_layout_href", "admin_layout.php");
	$t->set_var("admin_layout_header_href", "admin_layout_header.php");
	$t->set_var("admin_layout_page_href", "admin_layout_page.php");
	$t->set_var("admin_items_list_href", "admin_items_list.php");
	$t->set_var("admin_articles_href", "admin_articles.php");
	$t->set_var("admin_layouts_url", urlencode("admin_layouts.php"));
	$t->set_var("admin_cms_url", urlencode("admin_cms.php"));

	$articles = array();
	$sql  = " SELECT ac.category_id, ac.category_name ";
	$sql .= " FROM " . $table_prefix . "articles_categories ac ";
	$sql .= " WHERE ac.parent_category_id=0 ";
	$db->query($sql);
	while ($db->next_record()) {
		$category_id = $db->f("category_id");
		$category_name = get_translation($db->f("category_name"));
		$articles[$category_id] = $category_name;
	}

	$modules = array();
	$sql  = " SELECT m.module_id, m.module_code, m.module_name ";
	$sql .= " FROM " . $table_prefix . "cms_modules m ";
	$sql .= " ORDER BY m.module_order ";
	$db->query($sql);
	while($db->next_record()) {
		$module_id = $db->f("module_id");
		$module_code = $db->f("module_code");
		$module_name = get_translation($db->f("module_name"));

		if ($module_code == "articles") {
			foreach ($articles as $category_id => $category_name) {
				$article_module = $module_name;
				$t->set_var("category_name", $category_name);
				parse_value($article_module);
				$modules[$module_id."_".$category_id] = array(
					"id" => $module_id, "name" => $article_module, "key_code" => $category_id, "key_type" => "category");
			}
		} else {
			$modules[$module_id] = array("id" => $module_id,  "name" => $module_name);
		}
	}

	$pages = array();
	$sql  = " SELECT p.page_id, p.module_id, p.page_code, p.page_name ";
	$sql .= " FROM " . $table_prefix . "cms_pages p ";
	$sql .= " ORDER BY p.page_order ";
	$db->query($sql);
	while($db->next_record()) {
		$page_id = $db->f("page_id");
		$module_id = $db->f("module_id");
		$page_code = $db->f("page_code");
		$page_name = get_translation($db->f("page_name"));
		parse_value($page_name);

		$pages[$module_id][$page_id] = array("code" => $page_code, "name" => $page_name);
	}

	foreach ($modules as $module_key => $module) {
		$page_index = 0;
		$module_id = $module["id"];
		$module_pages = isset($pages[$module_id]) ? $pages[$module_id] : "";
		$key_code = isset($module["key_code"]) ? $module["key_code"] : "";
		$key_type = isset($module["key_type"]) ? $module["key_type"] : "";

		$t->set_var("module_name", $module["name"]);

		// parse pages
		$t->set_var("cms_pages_rows", "");
		$t->set_var("cms_pages_cols", "");
		if (is_array($module_pages) && sizeof($module_pages) > 0)  {
			foreach ($module_pages as $page_id => $page) {
				$page_index++;
				$page_code = $page["code"];
				$page_name = $page["name"];

				$page_url = new VA_URL("admin_cms_page_layout.php", false);
				$page_url->add_parameter("page_id", CONSTANT, $page_id);
				$page_url->add_parameter("key_code", CONSTANT, $key_code);
				$page_url->add_parameter("key_type", CONSTANT, $key_type);
				$page_url->add_parameter("rp", CONSTANT, "admin_cms.php");

				$t->set_var("page_id", $page_id);
				$t->set_var("page_code", $page_code);
				$t->set_var("page_name", $page_name);
				$t->set_var("admin_cms_page_url", $page_url->get_url());
				
				$t->parse("cms_pages_cols", true);
				if ($page_index % 5 == 0) {
					$t->parse("cms_pages_rows", true);
					$t->set_var("cms_pages_cols", "");
				}
			}
			if ($page_index % 5 != 0) {
				$t->parse("cms_pages_rows", true);
			}
			$t->parse("cms_modules", true);
		}

	}

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

  // shop - 1, cms - 2, helpdesk - 4, forum - 8, ads - 16, manuals - 32

	$t->pparse("main");

?>