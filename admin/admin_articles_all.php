<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_articles_all.php                                   ***
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
	$t->set_file("main", "admin_articles_all.html");

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
	$t->set_var("admin_articles_all_href", "admin_articles_all.php");
	$t->set_var("admin_article_images_href", "admin_article_images.php");
	$t->set_var("admin_article_links_href", "admin_article_links.php");
	$t->set_var("admin_articles_category_href", "admin_articles_category.php");
	$t->set_var("admin_cms_page_layout_href", "admin_cms_page_layout.php");


	include_once("./admin_header.php");
	include_once("./admin_footer.php");
	
	$s = trim(get_param("s"));
	$sc = get_param("sc");
	$sa = get_param("sa");
	$st = get_param("st");

	$search = strlen($s) ? true : false;
	if(!strlen($sc)) $sc = 0;
	if(!strlen($sa)) $sa = 0;
	
	$search_array[] = array("0", "");
	$search_array[] = array("1", SHOWN_ON_SITE_MSG);
	$search_array[] = array("2", REMOTE_RSS_MSG);
	$search_array[] = array("3", IMAGE_REQUIRED_MSG);

	$top_categories = "";
	$category_id = 0; 
	$shown_fields = "";
	//$shown_fields = ",," . $db->f("article_list_fields") . ",,";
	$sql = " SELECT category_id, category_name, article_list_fields ";
	$sql.= " FROM ".$table_prefix."articles_categories WHERE parent_category_id = 0 ";
	$sql.= " ORDER BY category_order ";
	$db->query($sql);
	$categories_ids = array();
	if ($db->next_record()) {
		do {
			$top_category_id = $db->f("category_id");
			$category_name = get_translation($db->f("category_name"));
			if ($sc == $db->f("category_id") && $sc != 0) {
				$categories_ids2[] = array($db->f("category_id"),$category_name,",,".$db->f("article_list_fields").",,");
			}
			$categories_ids[] = array($db->f("category_id"),$category_name,",,".$db->f("article_list_fields").",,");
			if ($top_categories) { $top_categories .= ","; }
			$top_categories .= $top_category_id;
		} while ($db->next_record());
	}


	for ($i = 0; $i < count($categories_ids); $i++) {
		$sca = get_param("sca_".$categories_ids[$i][0]);
		if ($sca == 1) {
			$t->set_var("sca_checked","checked");
			$categories_ids3[] = $categories_ids[$i];
		} else {
			$t->set_var("sca_checked","");
		}
		$t->set_var("sca_index",$categories_ids[$i][0]);
		$t->set_var("sca_value",1);
		$t->set_var("sca_description",$categories_ids[$i][1]);
		$t->parse("sca",true);
	}
	$t->set_var("top_categories", htmlspecialchars($top_categories));
	
	// get and set statuses
	$statuses = get_db_values("SELECT * FROM " . $table_prefix . "articles_statuses WHERE is_shown=1", array(array("", "")));
	set_options($statuses, $st, "st");

	set_options($search_array,$sa,"sa");
	$t->set_var("s", htmlspecialchars($s));
	$t->set_var("sca_checkboxes_style", "style='display:none;'");
	if (isset($categories_ids3)) {
		$categories_ids = $categories_ids3;
		$t->set_var("sca_checkboxes_style", "");
	} else if (isset($categories_ids2)) {
		$categories_ids = $categories_ids2;
	}
	
	foreach ($categories_ids as $value) {
		
		$articles_ids = array();
		$sql = " SELECT aa.article_id ";
		$sql.= " FROM ((".$table_prefix."articles_assigned aa ";
		$sql.= " INNER JOIN ".$table_prefix."articles_categories ac ON aa.category_id = ac.category_id) ";
		$sql.= " INNER JOIN ".$table_prefix."articles a ON aa.article_id = a.article_id) ";
		$sql.= " WHERE (ac.category_path LIKE '0,".$value[0].",%' OR aa.category_id = ".$value[0].")" ;
		if ($sa == 1) {
			$sql .= " AND a.status_id IN (1,2) ";
		} else if ($sa == 2) {
			$sql .= " AND a.is_remote_rss = 1 ";
		} else if ($sa == 3) {
			$sql.= " AND (a.image_small = '' OR a.image_small IS NULL)";
			$sql.= " AND (a.image_large = '' OR a.image_large IS NULL)";
			$sql.= " AND (a.stream_video = '' OR a.stream_video IS NULL)";
		}
		if (strlen($st)) {
			$sql .= " AND a.status_id=" . $db->tosql($st, INTEGER);
		}
		if ($search) {
			$sa1 = explode(" ", $s);
			for ($si = 0; $si < sizeof($sa1); $si++) {
				$sql .= " AND a.article_title LIKE '%" . $db->tosql($sa1[$si], TEXT, false) . "%'";
			}
		}
		$sql.= " GROUP BY aa.article_id,a.date_added ";
		$sql.= " ORDER BY a.date_added DESC ";
		$db->RecordsPerPage = 5;
		$db->query($sql);
		if ($db->next_record()) {
			do {
				$articles_ids[] = $db->f("article_id");
			} while ($db->next_record());
		}
		$t->set_var("tree_current_name",$value[1]);
		$t->set_var("category_id",$value[0]);
		if (count($articles_ids)) {
			
			$is_date_column = strpos($value[2], ",article_date,");
			if ($is_date_column) {
				$t->parse("article_date_header_column", false);
			} else {
				$t->set_var("article_date_header_column", "");
			}
			
			$t->parse("articles_order_link", false);
			$t->set_var("no_items", "");

			$sql = " SELECT a.*, st.status_name, st.allowed_view ";
			$sql.= " FROM (".$table_prefix."articles a";
			$sql.= " LEFT JOIN " . $table_prefix . "articles_statuses st ON a.status_id=st.status_id) ";
			$sql.= " WHERE a.article_id IN (".$db->tosql($articles_ids,INTEGERS_LIST).")";
			$sql.= " ORDER BY a.date_added DESC ";
			$db->query($sql);
			if ($db->next_record()) {
				$t->set_var("items_list","");
				do {
					$article_id = $db->f("article_id");
					$article_title = get_translation($db->f("article_title"));
					// echo articles
					if ($is_date_column) {
						$article_date = $db->f("article_date", DATETIME);
						$article_date = va_date($datetime_show_format, $article_date);
						$t->set_var("article_date", $article_date);
						$t->parse("article_date_column", false);
					} else {
						$t->set_var("article_date_column", "");
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

					$t->set_var("article_category_id", $value[0]);
					$t->set_var("article_id", $db->f("article_id"));
					
					if ($related_forums) {
						$t->parse("related_forums_priv", false);				
					}
					if ($search) {
						for ($si = 0; $si < sizeof($sa1); $si++) {
							$article_title = preg_replace("/(" . $sa1[$si] . ")/i", "<font color=\"blue\">\\1</font>", $article_title);
						}
					}
					$t->set_var("article_title", $article_title);
					$t->set_var("article_status", $article_status);
					$t->parse("items_list");
					
				} while ($db->next_record());
				
				$t->parse("items_header", false);
				
			}
		} else {
			$t->set_var("items_header", "");
			$t->set_var("articles_order_link", "");
			$t->set_var("items_list", "");
			$t->set_var("no_items","");
			$t->parse("no_items");
		}
		$t->parse("items_block", true);
	}
	if (count($categories_ids)) {
		$t->parse("categories_order_link", false);
		foreach ($categories_ids as $value) {
			$category_id = $value[0];
			$category_name = $value[1];
			
			$t->set_var("row_category_id",$category_id);
			$t->set_var("category_name",htmlspecialchars(get_translation($category_name)));
			
			$t->parse("articles_categories");
		}
	} else {
		$t->set_var("articles_categories", "");
		$t->set_var("categories_order_link", "");
		$t->parse("no_categories");
	}
	
	$t->parse("categories_header", false);
	
	$t->pparse("main");
	
?>