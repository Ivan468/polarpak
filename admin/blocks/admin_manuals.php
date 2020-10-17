<?php
function admin_manuals_block($block_name, $params = array()) {
	global $t, $db, $table_prefix, $db_type;
	
	$t->set_file("block_body", "admin_block_manuals.html");
	$t->set_var("admin_manual_href",         "admin_manual.php");
	$t->set_var("admin_manual_article_href", "admin_manual_article.php");
	
	$permissions = get_permissions();
	$manuals     = get_permissions($permissions, "manual", 0);
	if (!$manuals) return;
	
	$category_id = get_param("category_id");
	$s           = strip_tags(rtrim(trim(get_param("s"))));
	$search      = (strlen($s)) ? true : false;
	
	$t->set_var("s", $s);
	if ($s) {
		$t->parse("s_title", false);
	}
	
	if (strlen($category_id)) {
		$product_category_name = "<b>Top</b> category";
		if ($category_id) {
			$sql  = " SELECT category_name FROM " . $table_prefix . "manuals_categories";
			$sql .= " WHERE category_id=" . $db->tosql("category_id", INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$product_category_name = "<b>" . get_translation($db->f("category_name")) . "</b> category";
			} else {
				$category_id = 0;
			}
		}
	} else {
		$product_category_name = "<b>All</b> categories";
	}
	$t->set_var("product_category_name", $product_category_name);
	
	$where    = "";
	$brackets = "(";
	$join     = " LEFT JOIN " . $table_prefix . "manuals_list ic ON i.manual_id=ic.manual_id) ";
	if (strlen($category_id)) {
		$where .= " AND ic.category_id= " . $db->tosql($category_id, INTEGER);
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
	$sql .= " FROM " . $brackets . $table_prefix . "manuals_articles i " . $join;
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$sql .= " GROUP BY i.article_id) ag ";
	$total_records = get_db_value($sql);
	
	if(!$total_records) return;
	$t->set_var("total_records", $total_records);
	
	$sql  = " SELECT i.article_id, i.article_title, i.parent_article_id, i.section_number, i.date_modified, i.manual_id, ic.category_id, ic.manual_title";
	$sql .= " FROM " . $brackets . $table_prefix . "manuals_articles i " . $join;
	$sql .= " WHERE 1=1 ";
	$sql .= $where;
	$sql .= " GROUP BY i.article_id, i.article_title, i.parent_article_id, i.section_number, i.date_modified, i.manual_id, ic.category_id, ic.manual_title ";
	$sql .= " ORDER BY i.article_id ";
	$db->RecordsPerPage = isset($params['records_per_page']) ? $params['records_per_page'] : 5;
	$db->query($sql);
	$item_index = 1;
	$t->set_var("items_list", "");
	while ($db->next_record()) {
		$item_index++;
		$article_id          = $db->f("article_id");
		$manual_id           = $db->f("manual_id");
		$manual_title        = get_translation($db->f("manual_title"));
		$article_category_id = $db->f("category_id");
		$parent_article_id   = $db->f("parent_article_id");
		$article_title       = get_translation($db->f("article_title"));
		
		$t->set_var("article_id",          $article_id);
		$t->set_var("manual_id",           $manual_id);
		$t->set_var("article_category_id", $article_category_id);
		$t->set_var("parent_article_id",   $parent_article_id);
		$t->set_var("manual_title",        $manual_title);
		
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
		$t->set_var("article_title",  $article_title);
		$t->set_var("section_number", $db->f("section_number"));
		$t->set_var("date_modified",  $db->f("date_modified"));
						
		$row_style = ($item_index % 2 == 0) ? "row1" : "row2";
		$t->set_var("row_style", $row_style);
		$t->parse("items_list");
	}
	
	$t->parse("block_body", false);
	$t->parse_to("block_body", $block_name, true);
	
	return $total_records;
}
?>