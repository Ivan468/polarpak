<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  manuals_functions.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	class VA_Manuals_Categories {
		
		static function _sql($params, $access_level) {
			global $table_prefix, $db, $site_id;
			
			$select = "";
			$where = "";
			$order = "";
			$join = "";
			$brackets = "";
			$use_sites = true;
			$use_acls  = true;
			
			if (is_array($params)) {
				$select = isset($params["select"]) ? $params["select"] : "";
				$where  = isset($params["where"]) ? $params["where"] : "";
				$order  = isset($params["order"]) ? $params["order"] : "";
				$join   = isset($params["join"])  ? $params["join"] : "";
				$brackets = isset($params["brackets"])  ? $params["brackets"] : "";
				if (isset($params["no_sites"])) $use_sites = false;
				if (isset($params["no_acls"]))  $use_acls = false;
			} else {
				$where = $params;
			}
			
			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_CATEGORIES_PERM;
						
			$user_id         = get_session("session_user_id");
			$user_type_id    = get_session("session_user_type_id");
			$subscription_id = get_session("session_subscription_id");
			$subscription_ids = get_session("session_subscription_ids");
			
			$sql = " SELECT ";
			if (strlen($select)) {
				$sql .= $select;
			} else {
				$sql .= " c.category_id ";
			}
			
			$sql .= " FROM ";
			
			if ($use_sites && isset($site_id)) {
				$sql .= " (";
			};
			
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " (";
				};
				if (strlen($subscription_ids)) {
					$sql .= " (";
				}
			}
			
			if (strlen($brackets)) {
				$sql .= $brackets;
			}
			
			$sql .= $table_prefix . "manuals_categories c ";
			
			if ($use_sites && isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "manuals_categories_sites AS cs ON cs.category_id=c.category_id)";
			}
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "manuals_categories_types AS ut ON ut.category_id=c.category_id)";
				}
				if (strlen($subscription_ids)) {
					$sql .= " LEFT JOIN " . $table_prefix . "manuals_categories_subscriptions AS sb ON sb.category_id=c.category_id)";
				}
			}
			if (strlen($join)) {
				$sql .= $join;
			}
			
			$sql .= " WHERE 1=1";
						
			if ($use_sites) {
				if (isset($site_id)) {
					$sql .= " AND (c.sites_all=1 OR cs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
				} else {
					$sql .= " AND c.sites_all=1 ";
				}
			}
			if ($use_acls) {
				if (strlen($user_id) && strlen($subscription_ids)) {				
					$sql .= " AND ( " . format_binary_for_sql("c.access_level", $access_level);					
					$sql .= " OR ("   . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") ";
					$sql .= " OR ("   . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscription_id, INTEGERS_LIST) . ")) )";
				} elseif (strlen($user_id)) {
					$sql .= " AND (" . format_binary_for_sql("c.access_level", $access_level) . " ";
					$sql .= " OR (" . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
				} else {
					$sql .= " AND " . format_binary_for_sql("c.guest_access_level", $access_level);
				}
			}			
			
			if (strlen($where)) {
				$sql .= " AND " . $where;
			}
			
			if (strlen($order)) {
				$sql .= " " . $order;
			}
			
			return $sql;
		}		
		
		static function check_permissions($category_id, $access_level = VIEW_CATEGORIES_PERM) {
			global $db;
			$db->query(VA_Manuals_Categories::_sql("c.category_id=" . $db->tosql($category_id, INTEGER), $access_level));
			return $db->next_record();
		}
		
		static function check_exists($category_id) {
			global $db;
			$params["where"]   = " c.category_id=" . $db->tosql($category_id, INTEGER);
			$params["no_acls"] = true;
			$db->query(VA_Manuals_Categories::_sql($params, 0));
			return $db->next_record();
		}
		
		static function find_all_ids($params = "", $access_level = VIEW_CATEGORIES_PERM) {
			global $db;	
			$db->query(VA_Manuals_Categories::_sql($params, $access_level));
			$ids = array();
			while ($db->next_record()) {
				$id = $db->f(0);
				if (!in_array($id, $ids)) {
					$ids[] = $id;
				}
			}
			return $ids;
		}
		
		static function find_all($key_field = "c.category_id", $fields = array(), $params = "", $access_level = VIEW_CATEGORIES_PERM, $debug = false) {
			global $db, $db_type;			
			if (is_array($params)) {
				$params_prepared = $params;
				$params_prepared["select"] = implode(",", $fields);
			} else {
				$params_prepared = array();
				$params_prepared["where"] = $params;
			}
			$params_prepared["select"] = "";
			if ($key_field) {
				$params_prepared["select"] .= $key_field . ",";
			}
			if ($fields) {
				$params_prepared["select"] .= implode(",", $fields);
			}
			if ($debug) {
				$sql = VA_Manuals_Categories::_sql($params_prepared, $access_level);
				if ($db_type == "mysql") {
					echo sql_explain($sql);
				} else {
					echo $sql;
				}
			}
			$db->query(VA_Manuals_Categories::_sql($params_prepared, $access_level));
			
			$results = array();
			if ($key_field) {
				while ($db->next_record()) {
					$key = $db->f(0);
					$result = array();
					foreach ($fields AS $number => $field) {
						$result[$field] = $db->f($number + 1);
					}
					$results[$key] = $result;
				}
			} else {
				while ($db->next_record()) {
					$result = array();
					foreach ($fields AS $number => $field) {
						$result[$field] = $db->f($number);
					}
					$results[] = $result;
				}
			}
			return $results;
		}
	}
	
	class VA_Manuals {
		
		static function _sql($params, $access_level) {
			global $table_prefix, $db, $site_id;
			
			$select = "";
			$where = "";
			$order = "";
			$join = "";
			$brackets = "";
			$use_sites = true;
			$use_acls  = true;
			
			if (is_array($params)) {
				$select = isset($params["select"]) ? $params["select"] : "";
				$where  = isset($params["where"]) ? $params["where"] : "";
				$order  = isset($params["order"]) ? $params["order"] : "";
				$join   = isset($params["join"])  ? $params["join"] : "";
				$brackets = isset($params["brackets"])  ? $params["brackets"] : "";
				if (isset($params["no_sites"])) $use_sites = false;
				if (isset($params["no_acls"]))  $use_acls = false;
			} else {
				$where = $params;
			}
			
			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_CATEGORIES_PERM;
						
			$user_id         = get_session("session_user_id");
			$user_type_id    = get_session("session_user_type_id");
			$subscription_id = get_session("session_subscription_id");
			$subscription_ids = get_session("session_subscription_ids");
			
			$sql = " SELECT ";
			if (strlen($select)) {
				$sql .= $select;
			} else {
				$sql .= " ml.manual_id ";
			}
			
			$sql .= " FROM ";
			
			if ($use_sites && isset($site_id)) {
				$sql .= " (";
			};
			
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " (";
				};
				if (strlen($subscription_ids)) {
					$sql .= " (";
				}
			}
			
			if (strlen($brackets)) {
				$sql .= $brackets;
			}
			
			$sql .= " ( " . $table_prefix . "manuals_list ml ";
			$sql .= " INNER JOIN " . $table_prefix . "manuals_categories c ON c.category_id = ml.category_id)";
			
			if ($use_sites && isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "manuals_categories_sites AS cs ON cs.category_id=c.category_id)";
			}
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "manuals_categories_types AS ut ON ut.category_id=c.category_id)";
				}
				if (strlen($subscription_ids)) {
					$sql .= " LEFT JOIN " . $table_prefix . "manuals_categories_subscriptions AS sb ON sb.category_id=c.category_id)";
				}
			}
			if (strlen($join)) {
				$sql .= $join;
			}
			
			$sql .= " WHERE ml.allowed_view = 1 ";
						
			if ($use_sites) {
				if (isset($site_id)) {
					$sql .= " AND (c.sites_all=1 OR cs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
				} else {
					$sql .= " AND c.sites_all=1 ";
				}
			}
			if ($use_acls) {
				if (strlen($user_id) && strlen($subscription_ids)) {				
					$sql .= " AND ( " . format_binary_for_sql("c.access_level", $access_level);					
					$sql .= " OR ("   . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") ";
					$sql .= " OR ("   . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscription_id, INTEGERS_LIST) . ")) )";
				} elseif (strlen($user_id)) {
					$sql .= " AND (" . format_binary_for_sql("c.access_level", $access_level) . " ";
					$sql .= " OR (" . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
				} else {
					$sql .= " AND " . format_binary_for_sql("c.guest_access_level", $access_level);
				}
			}			
			
			if (strlen($where)) {
				$sql .= " AND " . $where;
			}
			
			if (strlen($order)) {
				$sql .= " " . $order;
			}
			
			return $sql;
		}		
		
		static function check_permissions($manual_id, $access_level = VIEW_CATEGORIES_PERM) {
			global $db;
			$db->query(VA_Manuals::_sql(" ml.manual_id=" . $db->tosql($manual_id, INTEGER), $access_level));
			return $db->next_record();
		}
		
		static function check_exists($manual_id) {
			global $db;
			$params["where"]   = " ml.manual_id=" . $db->tosql($manual_id, INTEGER);
			$params["no_acls"] = true;
			$db->query(VA_Manuals::_sql($params, 0));
			return $db->next_record();
		}
		
		static function find_all_ids($params = "", $access_level = VIEW_CATEGORIES_PERM) {
			global $db;	
			$db->query(VA_Manuals::_sql($params, $access_level));
			$ids = array();
			while ($db->next_record()) {
				$id = $db->f(0);
				if (!in_array($id, $ids)) {
					$ids[] = $id;
				}
			}
			return $ids;
		}
		
		static function find_all($key_field = "ml.manual_id", $fields = array(), $params = "", $access_level = VIEW_CATEGORIES_PERM, $debug = false) {
			global $db, $db_type;
			if (is_array($params)) {
				$params_prepared = $params;
				$params_prepared["select"] = implode(",", $fields);
			} else {
				$params_prepared = array();
				$params_prepared["where"] = $params;
			}
			$params_prepared["select"] = "";
			if ($key_field) {
				$params_prepared["select"] .= $key_field . ",";
			}
			if ($fields) {
				$params_prepared["select"] .= implode(",", $fields);
			}
			if ($debug) {
				$sql = VA_Manuals::_sql($params_prepared, $access_level);
				if ($db_type == "mysql") {
					echo sql_explain($sql);
				} else {
					echo $sql;
				}
			}
			$db->query(VA_Manuals::_sql($params_prepared, $access_level));
			
			$results = array();
			if ($key_field) {
				while ($db->next_record()) {
					$key = $db->f(0);
					$result = array();
					foreach ($fields AS $number => $field) {
						$result[$field] = $db->f($number + 1);
					}
					$results[$key] = $result;
				}
			} else {
				while ($db->next_record()) {
					$result = array();
					foreach ($fields AS $number => $field) {
						$result[$field] = $db->f($number);
					}
					$results[] = $result;
				}
			}
			return $results;
		}
	}

	class VA_Manuals_Articles {

		static function update_articles_path($article_id) {
			global $db, $table_prefix;
			// build articles path 
			$article_path = "";
			// save all ids to check cross references
			$ids = array(); $current_id = $article_id;
			do {
				$ids[$current_id] = $current_id;
				$sql  = " SELECT parent_article_id FROM ".$table_prefix."manuals_articles ";
				$sql .= " WHERE article_id=".$db->tosql($current_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$current_id = $db->f("parent_article_id");
					if (isset($ids[$current_id])) { 
						// cross reference found run query to fix it
						$current_id = 0; 
						$sql  = " UPDATE ".$table_prefix."manuals_articles SET parent_article_id=0,article_path='0,' ";
						$sql .= " WHERE article_id=".$db->tosql($current_id, INTEGER);
						$db->query($sql);
					}
				} else {
					$current_id = 0;
				}
				$article_path = $current_id.",".$article_path;
			} while ($current_id);

			// update path for main article
			$sql  = " UPDATE ".$table_prefix."manuals_articles SET article_path=".$db->tosql($article_path, TEXT);
			$sql .= " WHERE article_id=".$db->tosql($article_id, INTEGER);
			$db->query($sql);

			// check for all sub childrens to set new path for them as well
			$sub_article_path = $article_path.$article_id.",";
			if ($article_id) {
				$articles = array(array("id" => $article_id, "path" => $sub_article_path));
			} else {
				$articles = array(array("id" => 0, "path" => "0,"));
			}

			while(($article = array_pop($articles))) {
				$parent_article_id = $article["id"];
				$parent_article_path = $article["path"];
				// set new path for all children 
				$sql  = " UPDATE ".$table_prefix."manuals_articles SET article_path=".$db->tosql($parent_article_path, TEXT);
				$sql .= " WHERE parent_article_id=".$db->tosql($parent_article_id, INTEGER);
				$db->query($sql);
				// check one level down childrens and add them to array for next cycle update
				$sql  = " SELECT article_id FROM ".$table_prefix."manuals_articles ";
				$sql .= " WHERE parent_article_id=".$db->tosql($parent_article_id, INTEGER);;
				$db->query($sql);
				while ($db->next_record()) {
					$sub_article_id = $db->f("article_id");
					$sub_article_path = $parent_article_path.$sub_article_id.",";
					$articles[] = array("id" => $sub_article_id, "path" => $sub_article_path);
				}
			}
		}

		static function update_articles_order($parent_article_id) {
			global $db, $table_prefix;
			// check parent section number
			$parent_section_number = "";
			$sql  = " SELECT section_number FROM ".$table_prefix."manuals_articles ";
			$sql .= " WHERE article_id=".$db->tosql($parent_article_id, INTEGER);
			$parent_section_number = get_db_value($sql);

			// prepare order for articles
			$article_order = 0; $articles = array();
			$sql  = " SELECT article_id FROM ".$table_prefix."manuals_articles ";
			$sql .= " WHERE parent_article_id=".$db->tosql($parent_article_id, INTEGER);
			$sql .= " ORDER BY article_order, article_id ";
			$db->query($sql);
			while ($db->next_record()) {
				$article_order++;
				$article_id = $db->f("article_id");
				$article_section = ($parent_section_number) ? $parent_section_number.".".$article_order : $article_order;
				$articles[] = array("id" => $article_id, "order" => $article_order, "section" => $article_section);
			}

			// update order and section for articles
			while(($article = array_pop($articles))) {
				$parent_id = $article["id"];
				$parent_order = $article["order"];
				$parent_section = $article["section"];
				// set new order and section 
				$sql  = " UPDATE ".$table_prefix."manuals_articles ";
				$sql .= " SET article_order=".$db->tosql($parent_order, INTEGER);
				$sql .= ", section_number=".$db->tosql($parent_section, TEXT);
				$sql .= " WHERE article_id=".$db->tosql($parent_id, INTEGER);
				$db->query($sql);

				// check one level down childrens and add them to array for next cycle update
				$sub_article_order = 0;
				$sql  = " SELECT article_id FROM ".$table_prefix."manuals_articles ";
				$sql .= " WHERE parent_article_id=".$db->tosql($parent_id, INTEGER);;
				$sql .= " ORDER BY article_order, article_id ";
				$db->query($sql);
				while ($db->next_record()) {
					$sub_article_order++;
					$sub_article_id = $db->f("article_id");
					$sub_article_section = $parent_section.".".$sub_article_order;
					$articles[] = array("id" => $sub_article_id, "order" => $sub_article_order, "section" => $sub_article_section);
				}
			}
		}

	}
?>