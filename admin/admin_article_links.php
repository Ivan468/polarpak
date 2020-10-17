<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_article_links.php                                  ***
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

	check_admin_security("articles");

	$article_id = get_param("article_id");
	$category_id = get_param("category_id");
	if ($article_id) {
		$category_id = 0;
		$section_field = "article_title";
		$sql  = " SELECT article_title FROM " . $table_prefix . "articles_items ";
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
	} else {
		$section_field = "category_name";
		$sql  = " SELECT category_name FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
	}
	$db->query($sql);
	if ($db->next_record()) {
		$section_title = get_translation($db->f($section_field));
	} else {
		die(OBJECT_NO_EXISTS_MSG);
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_article_links.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_articles_href", "admin_articles.php");
	$t->set_var("admin_article_link_href", "admin_article_link.php");
	$t->set_var("admin_article_links_href", "admin_article_links.php");

	$t->set_var("article_id", intval($article_id));
	$t->set_var("category_id", intval($category_id));
	$t->set_var("section_title", htmlspecialchars($section_title));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_article_links.php");
	$s->set_sorter(ID_MSG, "sorter_link_id", "1", "link_id");
	$s->set_sorter(ADMIN_TITLE_MSG, "sorter_link_title", "2", "link_title");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_article_links.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "articles_links ";
	if ($article_id) {
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
	} else {
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
	}
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$sql  = " SELECT * FROM " . $table_prefix . "articles_links ";
	if ($article_id) {
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
	} else {
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
	}
	$sql .= $s->order_by;
	$db->query($sql);
	if ($db->next_record())
	{
		$t->parse("sorters", false);
		$t->set_var("no_records", "");
		do {
			$t->set_var("link_id", $db->f("link_id"));
			$t->set_var("link_title", $db->f("link_title"));
			$t->parse("records", true);
		} while($db->next_record());
	}
	else
	{
		$t->set_var("sorters", "");
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	$t->pparse("main");

?>
