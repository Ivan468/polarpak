<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_manual.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/editgrid.php");
	include_once($root_folder_path . "includes/manuals_functions.php");
	include_once($root_folder_path . "messages/".$language_code."/manuals_messages.php");

	include_once("./admin_common.php");

	//$admin_leftside_breadrcumbs = array();

	check_admin_security("manual");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_manual.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_manual_href", "admin_manual.php");
	$t->set_var("admin_manual_thread_href", "admin_manual_thread.php");
	$t->set_var("admin_manual_article_href", "admin_manual_article.php");
	$t->set_var("admin_manual_settings_href", "admin_manual_settings.php");
	$t->set_var("admin_manual_edit_href", "admin_manual_edit.php");
	$t->set_var("admin_manual_category_href", "admin_manual_category.php");

	// check selected manual
	$admin_settings = get_admin_settings();
	$manual_id = get_param("manual_id");
	$category_id = get_param("category_id");
	$search_string = trim(get_param("search_string"));
	$search_manual = get_param("search_manual");
	if (strlen($manual_id)) {
		$manuals_settings["manual_id"] = $manual_id;
		update_admin_settings($manuals_settings);
	} else {
		$manual_id = get_setting_value($admin_settings, "manual_id");
		if (!strlen($manual_id)) {
			// check nearestmanual
			$sql  = " SELECT fc.category_id, fc.category_name, fl.manual_id, fl.manual_title ";
			$sql .= " FROM " . $table_prefix . "manuals_categories fc ";
			$sql .= " LEFT JOIN " . $table_prefix . "manuals_list fl ";
			$sql .= " ON fc.category_id = fl.category_id ";
			if ($category_id) {
				$sql .= " WHERE fc.category_id = " . $db->tosql($category_id, INTEGER);
			}
			$sql .= " ORDER BY fc.category_order, fc.category_id, fl.manual_order ";
			$db->query($sql);
			if ($db->next_record()) {
				$manual_id = $db->f("manual_id");
			}		
		}
	}
	if ($manual_id) {
		$sql  = " SELECT manual_title FROM " . $table_prefix . "manuals_list ";
		$sql .= " WHERE manual_id=" . intval($manual_id);
		$manual_title = get_db_value($sql);
		$t->set_var("manual_id", intval($manual_id));
		$t->parse("new_article_link", false);
	} else {
		$manual_title = NO_ACTIVE_MANUAL_MSG;
		$t->set_var("title_block", NO_ACTIVE_MANUAL_MSG);
	}

	// check if some article was opened
	$ajax = get_param("ajax");
	$operation = get_param("operation");
	$ajax_id = get_param("id");
	if ($ajax) { 
		if ($operation == "below") {
			$below_id = get_param("below_id");
			$move_id = get_param("move_id");
			if ($below_id && $move_id) {
				$sql  = " SELECT parent_article_id FROM ".$table_prefix."manuals_articles ";
				$sql .= " WHERE article_id=".$db->tosql($move_id, INTEGER);
				$old_parent_article = get_db_value($sql); 

				$sql  = " SELECT parent_article_id,article_order FROM ".$table_prefix."manuals_articles ";
				$sql .= " WHERE article_id=".$db->tosql($below_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$parent_article_id = $db->f("parent_article_id");
					$article_order = $db->f("article_order");
					// shift all articles by one to insert a new article
					$sql  = " UPDATE ".$table_prefix."manuals_articles SET article_order=article_order+1 ";
					$sql .= " WHERE parent_article_id=".$db->tosql($parent_article_id, INTEGER);
					$sql .= " AND article_order>".$db->tosql($article_order, INTEGER);
					$db->query($sql);
					// update moved article data 
					$sql  = " UPDATE ".$table_prefix."manuals_articles ";		
					$sql .= " SET parent_article_id=".$db->tosql($parent_article_id, INTEGER);
					$sql .= ", article_order=".$db->tosql(($article_order+1), INTEGER);;
					$sql .= " WHERE article_id=".$db->tosql($move_id, INTEGER);
					$db->query($sql);
					// update article path for moved articles and it sub-articles
					VA_Manuals_Articles::update_articles_path($move_id);
					// update articles orders and section number for new and old positions
					VA_Manuals_Articles::update_articles_order($parent_article_id);
					if ($old_parent_article != $parent_article_id) {
						VA_Manuals_Articles::update_articles_order($old_parent_article);
					}
				}
				$data = array("success" => "ok");
			} else {
				$data = array("errors" => ERRORS_MSG);
			}
			echo json_encode($data);
			return;
		} else if ($operation == "sublevel" || $operation == "subload") {
			$top_id = get_param("top_id");
			$move_id = get_param("move_id");
			if ($top_id && $move_id) {
				$sql  = " SELECT parent_article_id FROM ".$table_prefix."manuals_articles ";
				$sql .= " WHERE article_id=".$db->tosql($move_id, INTEGER);
				$old_parent_article = get_db_value($sql); 

				$sql  = " SELECT article_id FROM ".$table_prefix."manuals_articles ";
				$sql .= " WHERE article_id=".$db->tosql($top_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					// shift all articles by one to insert a new article
					$sql  = " UPDATE ".$table_prefix."manuals_articles SET article_order=article_order+1 ";
					$sql .= " WHERE parent_article_id=".$db->tosql($top_id, INTEGER);

					// update moved article data 
					$sql  = " UPDATE ".$table_prefix."manuals_articles ";		
					$sql .= " SET parent_article_id=".$db->tosql($top_id, INTEGER);
					$sql .= ", article_order=".$db->tosql(1, INTEGER);;
					$sql .= " WHERE article_id=".$db->tosql($move_id, INTEGER);
					$db->query($sql);
					// update article path for moved articles and it sub-articles
					VA_Manuals_Articles::update_articles_path($move_id);
					// update articles orders and section number for new and old positions
					VA_Manuals_Articles::update_articles_order($top_id);
					if ($old_parent_article != $top_id) {
						VA_Manuals_Articles::update_articles_order($old_parent_article);
					}
				}
				$data = array("success" => "ok");
			} else {
				$data = array("errors" => ERRORS_MSG);
			}
			if ($operation == "sublevel") {
				echo json_encode($data);
				return;
			} else {
				// open tree
				$ajax_id = $top_id;
			}
		} 

		// open tree
		$start_level = 1;
		$article_id = $ajax_id;
		$manuals_settings["manual_article_id"] = $ajax_id;
		update_admin_settings($manuals_settings);
	} else {
		$article_id = get_setting_value($admin_settings, "manual_article_id");
	}
	if (!strlen($article_id)) { 
		$article_id = 0;
	}

	// check parent articles to show opened
	$parent_ids = array();
	$sql  = " SELECT ma.article_id, ma.article_path FROM ".$table_prefix."manuals_articles ma ";
	$sql .= " WHERE ma.manual_id=" . $db->tosql($manual_id, INTEGER);
	if ($ajax) {
		$sql .= " AND ma.parent_article_id IN (" . $db->tosql($ajax_id, INTEGERS_LIST) . ") ";
	} else {
		$sql .= " AND (ma.parent_article_id=0 OR ma.parent_article_id IS NULL ";
		if ($article_id) {
			$sql .= " OR ma.parent_article_id IN (" . $db->tosql($article_id, INTEGERS_LIST) . ") ";
		}
		$sql .= " ) ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$db_article_id = $db->f("article_id");
		$parent_ids[$db_article_id] = $db_article_id;
		$db_article_path = $db->f("article_path");
		if ($db_article_path) {
			$ids = explode(",", $db_article_path);	
			if ($ajax) { $start_level = sizeof($ids); }
			foreach ($ids as $path_id) {
				if ($path_id) { $parent_ids[$path_id] = $path_id; }
			}
		}	
	}

	// get only unique values
	$parent_ids = array_keys($parent_ids);
	// get manual article to show
	$articles = array();

	if ($parent_ids) {
		$sql  = " SELECT ma.article_id, ma.parent_article_id, ma.article_order, ma.article_title, ma.friendly_url, ";
		$sql .= " ma.short_description, ma.image_small, ma.image_small_alt, ma.image_large, ma.image_large_alt ";		
		$sql .= ", sma.subs_number ";
		$sql .= " FROM " . $table_prefix . "manuals_articles ma ";
		$sql .= " LEFT JOIN (";
		$sql .= " SELECT parent_article_id, COUNT(*) AS subs_number ";
		$sql .= " FROM " . $table_prefix . "manuals_articles ";
		$sql .= " WHERE parent_article_id IN (" . $db->tosql($parent_ids, INTEGERS_LIST) . ") ";
		$sql .= " GROUP BY parent_article_id ";
		$sql .= " ) sma ON sma.parent_article_id=ma.article_id ";
		$sql .= " WHERE ma.article_id IN (" . $db->tosql($parent_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY ma.article_order, ma.article_title ";
		$db->query($sql);
		while ($db->next_record()) {
			$db_article_id = $db->f("article_id");
			$article_order = $db->f("article_order");
			$article_title = get_translation($db->f("article_title"));
			$friendly_url = $db->f("friendly_url");
			$short_description = get_translation($db->f("short_description"));
			$subs_number = $db->f("subs_number");
			$article_url = "admin_manual_article.php?article_id=".$db_article_id;
  
			$parent_article_id = $db->f("parent_article_id");
			$articles[$db_article_id]["parent_id"] = $parent_article_id;
			$articles[$db_article_id]["title"] = $article_title;
			$articles[$db_article_id]["class"] = "";
			$articles[$db_article_id]["a_title"] = "";
			$articles[$db_article_id]["url"] = $article_url;
			$articles[$db_article_id]["short_description"] = $short_description;
			$articles[$db_article_id]["subs_number"] = $subs_number;
  
			$articles[$db_article_id]["image_small"] = $db->f("image_small");
			$articles[$db_article_id]["image_small_alt"] = get_translation($db->f("image_small_alt"));
			$articles[$db_article_id]["image_large"] = $db->f("image_large");
			$articles[$db_article_id]["image_large_alt"] = get_translation($db->f("image_large_alt"));
			$articles[$db_article_id]["allowed"] = false;
  
			$articles[$parent_article_id]["subs"][$db_article_id] = $article_order;
		}
	}

	if ($ajax) { // Ajax call for tree branch
		set_tree($articles, $ajax_id, $start_level, "", "", "");
		$t->set_var("subnodes", $t->get_var("subnodes_" .$start_level));
		$t->pparse("subnodes_block");//*/
		exit;
	} else if (sizeof($articles) > 0 && isset($articles[0])) {
		set_tree($articles, 0, 0, "", "", "");
	}

/*
	$ajax = get_param("ajax");
	$article_id = get_param("article_id");
	if (strlen($article_id)) { 
		$manuals_settings["manual_article_id"] = $article_id;
		update_admin_settings($manuals_settings);
	} else {
		$article_id = get_setting_value($admin_settings, "manual_article_id");
	}
	if (!strlen($article_id)) { 
		$article_id = 0;
	}

	$parent_ids = 0;
	if ($ajax) { // Ajax call for tree branch
		$parent_ids = $article_id;
	} else if ($article_id) { 
		// show opened branches
		$sql  = " SELECT article_id, article_path ";
		$sql .= " FROM " . $table_prefix . "manuals_articles ";
		$sql .= " WHERE article_id IN (" . $db->tosql($article_id, INTEGERS_LIST) . ") ";
		$sql .= " AND manual_id=" . $db->tosql($manual_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$article_id = $db->f("article_id");
			$article_path = $db->f("article_path");
			$parent_ids .= ",".$article_path.$article_id;
		}
	}

	$articles = array();
	$articles_ids = array();
	$sql  = " SELECT ma.article_id, ma.parent_article_id, ma.article_path, ma.section_number, ma.article_title ";
	$sql .= " FROM " . $table_prefix . "manuals_articles ma ";
	$sql .= " WHERE ma.parent_article_id IN (" . $db->tosql($parent_ids, INTEGERS_LIST) . ") ";
	$sql .= " AND ma.manual_id=" . $db->tosql($manual_id, INTEGER);
	$sql .= " ORDER BY ma.article_order, ma.article_title ";
	$db->query($sql);
	while ($db->next_record()) {
		$cur_article_id = $db->f("article_id");
		$parent_article_id = $db->f("parent_article_id");
		$articles_ids[] = $cur_article_id;
		$article_path = $db->f("article_path");
		$section_number = $db->f("section_number");

		$article_title = $section_number.". ".get_translation($db->f("article_title")) . " [#".$cur_article_id . "]";

		$articles[$cur_article_id]["parent_id"] = $parent_article_id;
		$articles[$cur_article_id]["article_path"] = $article_path;
		$articles[$cur_article_id]["article_title"] = $article_title;

		$articles[$parent_article_id]["subs"][] = $cur_article_id;
	}

	// calculate subs articles
	if ($articles_ids) {
		$sql  = " SELECT parent_article_id, COUNT(*) AS subs_number ";
		$sql .= " FROM " . $table_prefix . "manuals_articles ";
		$sql .= " WHERE parent_article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ") ";
		$sql .= " GROUP BY parent_article_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$parent_article_id = $db->f("parent_article_id");
			$subs_number = $db->f("subs_number");
			$articles[$parent_article_id]["subs_number"] = $subs_number;
		}
	}

	if ($ajax) { // Ajax call for tree branch
		show_articles($article_id);
		$sub_articles_html = $t->get_var("subarticles_".$article_id);
		echo $sub_articles_html;
		exit;
	} else {
		show_articles(0);	
	}
//*/


//	function set_tree(&$nodes, $parent_id, $level, array(0), ""$image_type = 1, $tree_type = "")

	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_manual.php");
	$n->set_parameters(false, true, false);

	// search in the articles
	if (strlen($search_string)) {
		// build where 
		$where = "";
		$sa = explode(" ", $search_string);
		for($si = 0; $si < sizeof($sa); $si++) {
			$word = trim($sa[$si]);
			if (strlen($word)) {
				$word = str_replace("%", "\%", $word);
				if ($where) { $where .= " AND "; } 
				else { $where .= " WHERE "; }
				$where .= " (ma.article_title LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
				$where .= " OR ma.short_description LIKE '%" . $db->tosql($word, TEXT, false) . "%' ";
				$where .= " OR ma.full_description LIKE '%" . $db->tosql($word, TEXT, false) . "%')";
			}
		}
		if (strlen($search_manual) && $search_manual != "all") {
			$where .= " AND ma.manual_id=".$db->tosql($search_manual, INTEGER);
		}

		// calculate number of found articles
		$sql  = " SELECT COUNT(*) FROM " . $table_prefix . "manuals_articles ma ";
		$sql .= $where;
		$total_records = get_db_value($sql);
		$records_per_page = 25;
		$pages_number = 5;
		$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);


		$found_message = str_replace("{found_records}", $total_records, FOUND_ARTICLES_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($search_string), $found_message);
		$t->set_var("found_message", $found_message);
		
		// show found articles
		if ($total_records) {
			$sql  = " SELECT ma.*, ml.manual_title FROM ".$table_prefix."manuals_articles ma ";
			$sql .= " INNER JOIN ".$table_prefix."manuals_list ml ON ml.manual_id=ma.manual_id ";
			$sql .= $where;
			$db->RecordsPerPage = $records_per_page;
			$db->PageNumber = $page_number;
			$db->query($sql);
			if ($db->next_record()) {
				$last_manual_id = $db->f("manual_id");
				do {
					$current_manual_id = $db->f("manual_id");
					if ($current_manual_id != $last_manual_id) {
						$t->set_var("found_manual_title", $last_manual_title);
						$t->parse("found_manuals", true);
						$t->set_var("found_articles", "");
					}

					$article_id = $db->f("article_id");
					$article_manual_id = $db->f("manual_id");

					$article_title = get_translation($db->f("article_title"));
					$section_number = $db->f("section_number");
		  
					$t->set_var("article_id", htmlspecialchars($article_id));
					$t->set_var("article_manual_id", htmlspecialchars($article_manual_id));
					$t->set_var("section_number", htmlspecialchars($section_number));
					$t->set_var("article_title", htmlspecialchars($article_title));
		  
					$t->parse("found_articles", true);

					$last_manual_id = $current_manual_id;
					$last_manual_title = $db->f("manual_title");

				} while ($db->next_record());

				$t->set_var("found_manual_title", $last_manual_title);
				$t->parse("found_manuals", true);
			}
		}

		$t->parse("articles_search", false);

	}


	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$manuals = array(array("all", ALL_MSG)); // array for manuals list
	// Show manual categories in the left
	$sql  = " SELECT fc.category_id, fc.category_name, fl.manual_id, fl.manual_title ";
	$sql .= " FROM " . $table_prefix . "manuals_categories fc ";
	$sql .= " LEFT JOIN " . $table_prefix . "manuals_list fl ";
	$sql .= " ON fc.category_id = fl.category_id ";
	if ($category_id) {
		$sql .= " WHERE fc.category_id = " . $db->tosql($category_id, INTEGER);
	}
	$sql .= " ORDER BY fc.category_order, fc.category_id, fl.manual_order ";
	$db->query($sql);
	if ($db->next_record()) {
		$last_category_id = "";
		do {
			$list_manual_id   = $db->f("manual_id");
			$list_category_id = $db->f("category_id");
			if ($last_category_id != $list_category_id) {
				$last_category_id = $list_category_id;
				$list_category_name = get_translation($db->f("category_name"));
				$t->set_var("list_category_id", $list_category_id);
				$t->set_var("list_category_name", htmlspecialchars($list_category_name));
				$t->parse("list_category", false);
				if ($list_category_id == $category_id) {
					$t->set_var("category_id", $category_id);
					$t->set_var("current_category", htmlspecialchars($list_category_name));
				}
			} else {
				$t->set_var("list_category", "");
			}

			if ($list_manual_id) {
				$list_manual_title = strip_tags(get_translation($db->f("manual_title")));
				$manuals[] = array($list_manual_id, $list_manual_title);
				
				if ($list_manual_id == $manual_id) {
					$manual_title = $list_manual_title;
					$category_id = $db->f("category_id");
					$category_name = get_translation($db->f("category_name"));
				}
				
				$t->set_var("manual_id", $list_manual_id);
				$t->set_var("manual_title", htmlspecialchars($list_manual_title));
				$t->set_var("category_id", $db->f("category_id"));
				$t->parse("list_manual", false);
			} else {
				$t->set_var("list_manual", "");
			}
			$t->parse("list_block", true);
		} while ($db->next_record());
		$t->set_var("title_block", $manual_title);
		$t->parse("new_manual_link", false);
		$t->set_var("block_no_categories", "");

	} else {
		$t->set_var("new_manual_link", "");
		$t->set_var("message_list", NO_CATEGORIES_MSG);
		$t->parse("block_message", false);
	}

	// search form 
	if (!strlen($search_manual)) { $search_manual = $manual_id; } 
	set_options($manuals, $search_manual, "search_manual");

	$t->set_var("search_string", htmlspecialchars($search_string));
	
	$t->pparse("main");
	
	function show_articles($parent_id)
	{
		global $t, $articles, $start_id;

		$subs = (isset($articles[$parent_id]) && isset($articles[$parent_id]["subs"])) ? $articles[$parent_id]["subs"] : array();
		for ($i = 0, $ic = count($subs); $i < $ic; $i++)
		{
			$current_id = $subs[$i];
			$show_article_id = $subs[$i];
			$article_path = $articles[$show_article_id]["article_path"];
			$article_title    = $articles[$show_article_id]["article_title"];
			$article_title_js = str_replace("'", "\\'", htmlspecialchars($article_title));
			
			$subs_number   = isset($articles[$show_article_id]["subs_number"]) ? $articles[$show_article_id]["subs_number"] : 0; // number of articles which could be loaded 
			$has_nested    = isset($articles[$show_article_id]["subs"]) ? is_array($articles[$show_article_id]["subs"]) : false;
			$is_last       = ($i == $ic - 1);
			$is_first      = ($i == 0);
			
			if ($has_nested) {
				show_articles($show_article_id);
			}
			
			$article_image = ""; $image_alt = "";
			if ($subs_number) {
				if ($has_nested) {
					$article_image = "../images/icons/minus.gif";
				} else {
					$article_image = "../images/icons/plus.gif";
				}
			} else {
				//$article_image = "../images/icons/empty.gif";
			}
			$image_onclick = "loadArticles($show_article_id);return false;";
			//$image_onclick = isset($articles[$show_article_id]["image_onclick"]) ? $articles[$show_article_id]["image_onclick"] : "";
			
			$t->set_var("article_id", $show_article_id);
			$t->set_var("article_title", $article_title);
			$t->set_var("article_path", $article_path);


			$article_class = "";
			if ($is_first) {$article_class .= " firstArticle";}
			if ($is_last) { 
				$article_class .= " lastArticle";
			}
			if (!$subs_number) { 
				if ($is_last) {
					$article_class .= " lastEmptyArticle"; 
				} else {
					$article_class .= " emptyArticle"; 
				}
			}
			$t->set_var("article_class", $article_class);		
			
			if ($article_image) {
				$t->set_var("src", htmlspecialchars($article_image));
				if ($image_onclick) {
					$t->set_var("onclick", htmlspecialchars($image_onclick));
				} else {
					$t->set_var("onclick", "");
				}
				$t->parse("article_image", false);
			} else {
				$t->set_var("article_image", "");
			}
			
			if ($has_nested) {
				$t->set_var("subarticles", $t->get_var("subarticles_".$current_id));
				$t->set_var("subarticles_".$current_id, "");
			} else {
				$t->set_var("subarticles", "");
			}		

			// parse all articles to their parent tag
			$t->parse_to("articles", "articles_" . $parent_id);
		}		

		// parse articles block
		$t->set_var("parent_id", $parent_id);
		$t->set_var("articles", $t->get_var("articles_".$parent_id));
		if ($parent_id && $parent_id == $start_id) {
			$t->parse("articles_block");
		} else if ($parent_id) {
			$t->parse_to("articles_block", "subarticles_".$parent_id);
		} else {
			$t->parse("articles_block");
		}	

	}


?>