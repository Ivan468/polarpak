<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_articles.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once("./admin_common.php");

	check_admin_security("articles");

	$permissions = get_permissions();
	$related_forums = get_setting_value($permissions, "forum", 0);

	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_articles.html");

	// set files names
	$t->set_var("admin_articles_top_href", "admin_articles_top.php");
	$t->set_var("admin_article_href", "admin_article.php");
	$t->set_var("admin_article_items_related_href", "admin_article_items_related.php");
	$t->set_var("admin_article_forums_related_href", "admin_article_forums_related.php");
	$t->set_var("admin_articles_href", "admin_articles.php");
	$t->set_var("admin_article_category_items_related_href", "admin_article_category_items_related.php");
	$t->set_var("admin_articles_category_href", "admin_articles_category.php");
	$t->set_var("admin_layout_page_href", "admin_layout_page.php");
	$t->set_var("admin_articles_reviews_href", "admin_articles_reviews.php");
	$t->set_var("admin_tell_friend_href", "admin_tell_friend.php");
	$t->set_var("admin_articles_assign_href", "admin_articles_assign.php");
	$t->set_var("admin_articles_categories_href", "admin_articles_categories.php");
	$t->set_var("admin_article_related_href", "admin_article_related.php");
	$t->set_var("admin_articles_order_href",  "admin_articles_order.php");
	$t->set_var("admin_articles_assign_href", "admin_articles_assign.php");
	$t->set_var("admin_cms_page_layout_href", "admin_cms_page_layout.php");
	$t->set_var("admin_article_image_href", "admin_article_image.php");
	$t->set_var("admin_article_images_href", "admin_article_images.php");
	$t->set_var("admin_article_links_href", "admin_article_links.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$top_category_id = 0;
	$category_id = get_param("category_id");

	// get search parameters
	$s = trim(get_param("s"));
	$sc = get_param("sc");
	$st = get_param("st");
	$search = (strlen($s) || strlen($st)) ? true : false;
	if ($sc) { $category_id = $sc; }
	$sa = "";
	if (strval($sc) == strval("0")) {
		$search_url = "admin_articles_all.php?s=".urlencode($s);
		if ($st) {
			$search_url .= "&st=" . urlencode($st);
		}
		header("Location: " . $search_url);
		exit;
	}

	$shown_fields = "";
	$current_category_name = "";
	$sql  = " SELECT category_name,article_list_fields,article_details_fields,article_required_fields ";
	$sql .= " FROM " . $table_prefix . "articles_categories ";
	$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER, true, false);
	$db->query($sql);
	if ($db->next_record()) {
		$shown_fields = ",," . $db->f("article_list_fields") . ",,";
		$current_category_name = get_translation($db->f("category_name"));
	}
	$t->set_var("current_category_name", $current_category_name);

	$rp = new VA_URL("admin_articles.php", false);
	$rp->add_parameter("category_id", REQUEST, "category_id");
	$rp->add_parameter("sc", GET, "sc");
	$rp->add_parameter("page", GET, "page");
	$rp->add_parameter("s", GET, "s");
	$t->set_var("rp_url", urlencode($rp->get_url()));

	if (!strlen($category_id)) {
		header("Location: admin.php");
		exit;
	} else {
		$sql  = " SELECT category_path,parent_category_id ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$parent_category_id = $db->f("parent_category_id");
			$category_path = $db->f("category_path");
			if ($parent_category_id == 0) {
				$top_category_id = $category_id;
			} else {
				$categories_ids = explode(",", $category_path);
				$top_category_id = $categories_ids[1];
			}
		}
	}

	$tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "articles_categories", "");
	
	$articles_order_column = ""; $articles_order_direction = ""; $top_category_name = "";
	$sql  = " SELECT category_name, articles_order_column,articles_order_direction ";
	$sql .= " FROM " . $table_prefix . "articles_categories ";
	$sql .= " WHERE category_id=" . $db->tosql($top_category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$top_category_name = get_translation($db->f("category_name"));
		$articles_order_column = $db->f("articles_order_column");
		$articles_order_direction = $db->f("articles_order_direction");
	}
	$t->set_var("top_category_name", $top_category_name);
	$t->set_var("top_category_id", $top_category_id);
	$t->set_var("category_id", htmlspecialchars($category_id));

	$sql  = " SELECT full_description FROM " . $table_prefix . "articles_categories WHERE category_id = " . $db->tosql($category_id, INTEGER);
	$db->query($sql);
	if ($db->next_record()) {
		$t->set_var("full_description", $db->f("full_description"));
	} else {
		$t->set_var("full_description", "");
	}

	$sql  = " SELECT category_id, category_name ";
	$sql .= " FROM " . $table_prefix . "articles_categories WHERE parent_category_id = " . $db->tosql($category_id, INTEGER);
	$sql .= " ORDER BY category_order ";
	$db->query($sql);
	if ($db->next_record())
	{
		$t->parse("categories_order_link", false);
		$t->set_var("no_categories", "");
		do
		{
			$t->set_var("row_category_id", $db->f("category_id"));
			$t->set_var("category_name", htmlspecialchars(get_translation($db->f("category_name"))));
			$t->parse("articles_categories");
		} while ($db->next_record());
		$t->parse("categories_header", false);
	}
	else
	{
		$t->set_var("articles_categories", "");
		$t->set_var("categories_order_link", "");
		$t->set_var("tree_current_name", $top_category_name);		
		$t->parse("no_categories");
	}

	// build FROM and JOIN SQL
	$sql_from  = " FROM (((" . $table_prefix . "articles a ";
	$sql_from .= " INNER JOIN " . $table_prefix . "articles_assigned aa ON a.article_id=aa.article_id) ";
	$sql_from .= " INNER JOIN " . $table_prefix . "articles_categories ac ON ac.category_id = aa.category_id)";
	$sql_from .= " LEFT JOIN " . $table_prefix . "articles_statuses st ON a.status_id=st.status_id) ";
	// build WHERE SQL
	$sql_where = "";
	if ($search) {
		$sql_where .= " WHERE (aa.category_id = " . $db->tosql($category_id, INTEGER);
		$sql_where .= " OR ac.category_path LIKE '" . $db->tosql($tree->get_path($category_id), TEXT, false) . "%')";
	} elseif (!$search) {
		$sql_where .= " WHERE aa.category_id = " . $db->tosql($category_id, INTEGER);
	}
	if ($search) {
		$sa = explode(" ", $s);
		for ($si = 0; $si < sizeof($sa); $si++) {
			$sql_where .= " AND a.article_title LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
		}
	}
	if (strlen($st)) {
		$sql_where .= " AND a.status_id=" . $db->tosql($st, INTEGER);
	}
	// build COUNT SQL
	if ($db->DBType == "access") {
		$sql  = " SELECT COUNT(*) ";
		$sql .= " FROM (SELECT DISTINCT a.article_id ";
		$sql .= $sql_from;
		$sql .= $sql_where;
		$sql .= ")";
	} else {
		$sql  = " SELECT COUNT(DISTINCT a.article_id) ";
		$sql .= $sql_from;
		$sql .= $sql_where;
	}
	$total_articles = get_db_value($sql);	

	// set up variables for navigator
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_articles.php");
	$records_per_page = 20;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_articles, false);

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;              

	$sql  = " SELECT a.article_id, a.is_draft, a.article_title, a.article_date, aa.category_id, st.status_name, st.allowed_view ";
	$sql .= " FROM (((" . $table_prefix . "articles a ";
	$sql .= " INNER JOIN " . $table_prefix . "articles_assigned aa ON a.article_id=aa.article_id) ";
	$sql .= " INNER JOIN " . $table_prefix . "articles_categories ac ON ac.category_id = aa.category_id)";
	$sql .= " LEFT JOIN " . $table_prefix . "articles_statuses st ON a.status_id=st.status_id) ";
	if ($search) {
		$sql .= " WHERE (aa.category_id = " . $db->tosql($category_id, INTEGER);
		$sql .= " OR ac.category_path LIKE '" . $db->tosql($tree->get_path($category_id), TEXT, false) . "%')";
	} elseif (!$search) {
		$sql .= " WHERE aa.category_id = " . $db->tosql($category_id, INTEGER);
	}
	if ($search) {
		$sa = explode(" ", $s);
		for ($si = 0; $si < sizeof($sa); $si++) {
			$sql .= " AND a.article_title LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
		}
	}
	$sql .= " GROUP BY a.article_id, a.is_draft, a.article_date, st.status_name, st.allowed_view, a.date_end, a.article_title, a.author_name, a.article_order, a.date_added, a.date_updated ";
	$sql .= " , aa.category_id, aa.article_order ";
	if ($articles_order_column) {
		if ($articles_order_column != "article_order") {
			$sql .= " ORDER BY a." . $articles_order_column . " " . $articles_order_direction;
		} else {
			$sql .= " ORDER BY aa." . $articles_order_column . " " . $articles_order_direction;
		}
	} else {
		$sql .= " ORDER BY aa.article_order, a.article_order ";
	}

	$db->query($sql);
	$is_date_column = strpos($shown_fields, ",article_date,");
	if ($is_date_column) {
		$t->parse("article_date_header_column", false);
	}
	if ($db->next_record())	{
		$article_index = 0;
		$t->parse("articles_order_link", false);
		$t->set_var("no_items", "");
		do {
			$article_index++;
			$article_id = $db->f("article_id");
			$is_draft = $db->f("is_draft");
			$article_title = get_translation($db->f("article_title"));
			if ($is_draft && !strlen($article_title)) {
				$article_title = "[".FOLDER_DRAFT_MSG."]";
			}

			if ($is_date_column) {
				$article_date = $db->f("article_date", DATETIME);
				$article_date = va_date($datetime_show_format, $article_date);
				$t->set_var("article_date", $article_date);
				$t->parse("article_date_column", false);
			}
			$article_status = get_translation($db->f("status_name"));
			$allowed_view = $db->f("allowed_view");
			if ($allowed_view == 0) {
				$status_color = "silver";
			} elseif ($allowed_view == 1) {
				$status_color = "blue";
			} else {
				$status_color = "black";
			}
			$article_status = "<font color=\"" . $status_color . "\">" . $article_status . "</font>";


			$t->set_var("article_category_id", $db->f("category_id"));
			$t->set_var("article_id", $db->f("article_id"));
			if ($search) {
				for ($si = 0; $si < sizeof($sa); $si++) {
					$article_title = preg_replace("/(" . $sa[$si] . ")/i", "<font color=\"blue\">\\1</font>", $article_title);
				}
			}
			
			if ($related_forums) {
				$t->parse("related_forums_priv", false);				
			}
			$t->set_var("article_title", $article_title);
			$t->set_var("article_status", $article_status);

			// set row class
			$row_class = ($article_index % 2 == 0) ? "row1" : "row2";
			if ($is_draft) { $row_class.= " draft"; }
			$t->set_var("row_class", $row_class);

			$t->parse("items_list");
		} while ($db->next_record());
		$t->parse("items_header", false);
	}
	else
	{
		$t->set_var("articles_order_link", "");
		$t->set_var("items_list", "");
		$t->set_var("tree_current_name", $top_category_name);
		$t->parse("no_items");
	}

	// set up search form parameters
	$statuses = get_db_values("SELECT * FROM " . $table_prefix . "articles_statuses WHERE is_shown=1", array(array("", "")));
	set_options($statuses, $st, "st");
	
	$values_before = array();
	$values_before[] = array(0, SEARCH_IN_ALL_MSG);
	$values_before[] = array($category_id, SEARCH_IN_CURRENT_MSG);
	$sql  = " SELECT category_id, category_name ";
	$sql .= " FROM " . $table_prefix . "articles_categories WHERE parent_category_id = " . $db->tosql($category_id, INTEGER);
	$sql .= " ORDER BY category_order ";
	$sc_values = get_db_values($sql, $values_before);
	set_options($sc_values, $category_id, "sc");
	$t->set_var("s", htmlspecialchars($s));
	if ($search) {
		$matching_message = FOUND_RECORDS_MSG;
		$matching_message = str_replace("{found_records}", $total_articles, $matching_message);
		$matching_message = str_replace("{search_string}", htmlspecialchars($s), $matching_message);

		$t->set_var("matching_message", $matching_message);
		$t->parse("s_d", false);
	}

	$t->parse("items_block", false);
	$t->pparse("main");

?>