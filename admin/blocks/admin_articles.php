<?php
function admin_articles_block($block_name, $params = array()) {
	global $t, $db, $table_prefix, $db_type;
	
	$t->set_file("block_body", "admin_block_articles.html");
	
	$t->set_var("admin_articles_all_href", "admin_articles_all.php");	
	$t->set_var("admin_article_href", "admin_article.php");
	$t->set_var("admin_article_items_related_href", "admin_article_items_related.php");
	$t->set_var("admin_article_related_href", "admin_article_related.php");
	$t->set_var("admin_article_forums_related_href", "admin_article_forums_related.php");
	$t->set_var("admin_articles_assign_href", "admin_articles_assign.php");
		
	$permissions = get_permissions();
	$articles    = get_permissions($permissions, "articles", 0);
	if (!$articles) return;
	$related_forums = get_setting_value($permissions, "forum", 0);
	
	$category_id = get_param("category_id");
	$s           = strip_tags(rtrim(trim(get_param("s"))));
	$search      = (strlen($s)) ? true : false;
	
	$t->set_var("s", $s);
	if ($s) {
		$t->parse("s_title", false);
	}
	
	$product_category_path = "";
	if (strlen($category_id)) {
		$product_category_name = "<b>Top</b> category";
		if ($category_id) {
			$sql  = " SELECT category_name, category_path FROM " . $table_prefix . "articles_categories";
			$sql .= " WHERE category_id=" . $db->tosql("category_id", INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$product_category_name = "<b>" . get_translation($db->f("category_name")) . "</b> category";
				$product_category_path = $db->f("category_path");
			} else {
				$category_id = 0;
			}
		}
	} else {
		$product_category_name = "<b>All</b> categories";
	}
	$t->set_var("product_category_name", $product_category_name);
	
	$where = "";
	$join  = "";
	$brackets = "";
	if ($search && $product_category_path) {
		$brackets .= "((";
		$join  .= " LEFT JOIN " . $table_prefix . "articles_assigned ic ON i.article_id=ic.article_id) ";
		$join  .= " LEFT JOIN " . $table_prefix . "articles_categories c ON c.category_id = ic.category_id) ";
		
		$where .= " AND (ic.category_id = " . $db->tosql($category_id, INTEGER);
		$where .= " OR c.category_path LIKE '" . $db->tosql($product_category_path, TEXT, false) . "%')";
	} else {
		$brackets .= "(";
		$join  .= " LEFT JOIN " . $table_prefix . "articles_assigned ic ON i.article_id=ic.article_id) ";
		if (strlen($category_id)) {
			$where .= " AND ic.category_id = " . $db->tosql($category_id, INTEGER);
		}
	}
	if ($s) {
		$sa = explode(" ", $s);
		for($si = 0; $si < sizeof($sa); $si++) {
			$sa[$si] = str_replace("%","\%",$sa[$si]);
			$where .= " AND (i.article_title LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			if (sizeof($sa) == 1 && preg_match("/^\d+$/", $sa[0])) {
				$where .= " OR i.article_id =" . $db->tosql($sa[0], INTEGER);
			}
			$where .= ")";
		}
	}
	
	// calculate records
	$sql  = " SELECT COUNT(*) FROM (SELECT i.article_id";
	$sql .= " FROM " . $brackets . $table_prefix . "articles i " . $join;
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$sql .= " GROUP BY i.article_id) ag ";
	$total_records = get_db_value($sql);
	
	if(!$total_records) return;
	$t->set_var("total_records", $total_records);
	
	$sql  = " SELECT i.article_id, i.article_title, st.status_name, st.allowed_view, ic.category_id";
	$sql .= " FROM (" . $brackets . $table_prefix . "articles i " . $join;
	$sql .= " LEFT JOIN " . $table_prefix . "articles_statuses st ON i.status_id=st.status_id) ";
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$sql .= " GROUP BY i.article_id, i.article_title, st.status_name, st.allowed_view, ic.category_id ";
	$sql .= " ORDER BY i.article_id ";
	$db->RecordsPerPage = isset($params['records_per_page']) ? $params['records_per_page'] : 5;
	$db->query($sql);
	$item_index = 1;
	$t->set_var("items_list", "");
	while ($db->next_record()) {
		$item_index++;
		$article_id          = $db->f("article_id");
		$article_category_id = $db->f("category_id");
		$article_title       = get_translation($db->f("article_title"));
		
		$article_status = $db->f("status_name");
		$allowed_view = $db->f("allowed_view");
		if ($allowed_view == 0) {
			$status_color = "silver";
		} elseif ($allowed_view == 1) {
			$status_color = "blue";
		} else {
			$status_color = "black";
		}
		$article_status = "<font color=\"" . $status_color . "\">" . $article_status . "</font>";

		$t->set_var("article_category_id", $article_category_id);
		$t->set_var("article_id", $db->f("article_id"));	
		
		$article_title = htmlspecialchars($article_title);
		if (is_array($sa)) {
			for ($si = 0; $si < sizeof($sa); $si++) {
				$regexp = "";
				for ($si = 0; $si < sizeof($sa); $si++) {
					if (strlen($regexp)) $regexp .= "|";
					$regexp .= htmlspecialchars(str_replace(
					array( "/", "|",  "$", "^", "?", ".", "{", "}", "[", "]", "(", ")", "*"),
					array("\/","\|","\\$","\^","\?","\.","\{","\}","\[","\]","\(","\)","\*"),$sa[$si]));
				}
				if (strlen($regexp)) {
					$article_title = preg_replace ("/(" . $regexp . ")/i", "<font color=\"blue\">\\1</font>", $article_title);
				}
			}
		}
		$t->set_var("article_title", $article_title);
		$t->set_var("article_status", $article_status);
		
		if ($related_forums) {
			$t->parse("related_forums_priv", false);
		}

		$row_style = ($item_index % 2 == 0) ? "row1" : "row2";
		$t->set_var("row_style", $row_style);
		$t->parse("items_list");
	}
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
	
	return $total_records;
}
?>