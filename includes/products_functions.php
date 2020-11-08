<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  products_functions.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	class VA_Categories {

		static function sql($params, $access_level = VIEW_CATEGORIES_PERM) {
			global $table_prefix, $db, $site_id, $language_code;

			$use_sites = (is_array($params) && isset($params["no_sites"])) ? false : true;
			$use_acls  = (is_array($params) && isset($params["no_acls"])) ? false : true;
			$use_not   = (is_array($params) && isset($params["not"])) ? true : false;

			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_CATEGORIES_PERM;
			$access_field = (is_array($params) && isset($params["access_field"])) ? $params["access_field"] : false;
					
			$admin_id         = get_session("session_admin_id");
			$user_id          = get_session("session_user_id");
			$user_type_id     = get_session("session_user_type_id");
			$subscription_ids = get_session("session_subscription_ids");

			VA_Query::prepare_sql($params);
			if (count($params["select"]) == 0) {
				$params["select"][] = "c.category_id"; 
			}
			if (count($params["from"]) == 0) {
				$params["from"][] = $table_prefix."categories c"; 
			}

			if ($use_sites && isset($site_id)) {
				if (isset($site_id)) {
					$params["where"][] = " (c.sites_all=1 OR cs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
					$params["join"][] = " LEFT JOIN " . $table_prefix . "categories_sites AS cs ON cs.category_id=c.category_id";
				} else {
					$params["where"][] = " c.sites_all=1 ";
				}
			}

			if ($use_acls) {
				if (strlen($user_id) && strlen($subscription_ids)) {
					$where  = " (" . format_binary_for_sql("c.access_level", $access_level);
					if (strlen($admin_id)) {
						$where .= " OR " . format_binary_for_sql("c.admin_access_level", $access_level);
					}
					$where .= " OR ("  . format_binary_for_sql("ut.access_level", $access_level) . "  AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . " ) ";
					$where .= " OR ("  . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscription_ids, INTEGERS_LIST) . ")) )";
					$params["where"][] = $where;
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "(c.admin_access_level&c.access_level&ut.access_level&sb.access_level) AS user_access_level "; 
						} else {
							$params["select"][] = "(c.access_level&ut.access_level&sb.access_level) AS user_access_level "; 
						}
					}
				} elseif (strlen($user_id)) {
					$where  = " (" . format_binary_for_sql("c.access_level", $access_level);
					if (strlen($admin_id)) {
						$where  .= " OR " . format_binary_for_sql("c.admin_access_level", $access_level);
					}
					$where .= " OR ("  . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
					$params["where"][] = $where;
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "(c.admin_access_level&c.access_level&ut.access_level) AS user_access_level "; 
						} else {
							$params["select"][] = "(c.access_level&ut.access_level) AS user_access_level "; 
						}
					}
				} else {
					$where = " (" . format_binary_for_sql("c.guest_access_level", $access_level);
					if (strlen($admin_id)) {
						$where  .= " OR " . format_binary_for_sql("c.admin_access_level", $access_level);
					}
					$where .= ")";
					$params["where"][] = $where;
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "(c.admin_access_level&c.guest_access_level) AS user_access_level "; 
						} else {
							$params["select"][] = "(c.guest_access_level) AS user_access_level "; 
						}
					}
				}
				if (strlen($user_id)) {
					$params["join"][] = " LEFT JOIN " . $table_prefix . "categories_user_types AS ut ON ut.category_id=c.category_id";
				}			
				if (strlen($subscription_ids)) {
					$params["join"][] = " LEFT JOIN " . $table_prefix . "categories_subscriptions AS sb ON sb.category_id=c.category_id";
				}
			}

			if ($use_not) {
				$params["where"][] = " c.sites_all<>1 ";
			} else {
				$params["where"][] = " c.is_showing=1 ";
			}

			$sql = VA_Query::build_sql($params);
			return $sql;
		}

		/**
		 * Internal sql function, that builds query for categories search
		 *
		 * @param Array / String $params
		 * @param Constant $access_level: VIEW_CATEGORIES_PERM, VIEW_CATEGORIES_ITEMS_PERM, VIEW_ITEMS_PERM, ADD_ITEMS_PERM
		 * @return String
		 */
		static function _sql($params, $access_level) {
			global $table_prefix, $db, $site_id;
			
			$select = "";
			$where = "";
			$order = "";
			$join = "";
			$brackets = "";
			$use_sites = true;
			$use_acls  = true;
			$use_not   = false;
				
			if (is_array($params)) {
				$select = isset($params["select"]) ? $params["select"] : "";
				$where  = isset($params["where"]) ? $params["where"] : "";
				$order  = isset($params["order"]) ? $params["order"] : "";
				$join   = isset($params["join"])  ? $params["join"] : "";
				$brackets = isset($params["brackets"])  ? $params["brackets"] : "";
				if (isset($params["no_sites"])) $use_sites = false;
				if (isset($params["no_acls"]))  $use_acls = false;
				if (isset($params["not"]))      $use_not = true;
			} else {
				$where = $params;
			}
			
			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_CATEGORIES_PERM;
						
			$admin_id        = get_session("session_admin_id");
			$user_id         = get_session("session_user_id");
			$user_type_id    = get_session("session_user_type_id");
			$subscription_id = get_session("session_subscription_id");
			$subscription_ids = get_session("session_subscriptions_ids");
			
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
			
			$sql .= $table_prefix . "categories c ";
			
			if ($use_sites && isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "categories_sites AS cs ON cs.category_id=c.category_id)";
			}
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "categories_user_types AS ut ON ut.category_id=c.category_id)";
				}
				if (strlen($subscription_ids)) {
					$sql .= " LEFT JOIN " . $table_prefix . "categories_subscriptions AS sb ON sb.category_id=c.category_id)";
				}
			}
			if (strlen($join)) {
				$sql .= $join;
			}
			
			if ($use_not) {
				$sql .= " WHERE NOT(c.is_showing=1";
			} else {
				$sql .= " WHERE c.is_showing=1";
			}
					
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
					if (strlen($admin_id)) {
						$sql .= " OR " . format_binary_for_sql("c.admin_access_level", $access_level);
					}
					$sql .= " OR ("   . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") ";
					$sql .= " OR ("   . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscription_ids, INTEGERS_LIST) . ")) )";
				} elseif (strlen($user_id)) {
					$sql .= " AND (" . format_binary_for_sql("c.access_level", $access_level) . " ";
					if (strlen($admin_id)) {
						$sql .= " OR " . format_binary_for_sql("c.admin_access_level", $access_level);
					}
					$sql .= " OR (" . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
				} else {
					$sql .= " AND (" . format_binary_for_sql("c.guest_access_level", $access_level);
					if (strlen($admin_id)) {
						$sql .= " OR " . format_binary_for_sql("c.admin_access_level", $access_level);
					}
					$sql .= " ) ";
				}
			}			
			
			if (strlen($where)) {
				$sql .= " AND " . $where;
			}
			if ($use_not) {
				$sql .= " ) ";
			}
			
			return $sql;
		}
		/**
		 * Check if the category with this id is availiable with selected access level
		 *
		 * @param Integer $category_id
		 * @param Constant $access_level: VIEW_CATEGORIES_PERM, VIEW_CATEGORIES_ITEMS_PERM, VIEW_ITEMS_PERM, ADD_ITEMS_PERM
		 * @return Boolean
		 */
		static function check_permissions($category_id, $access_level = VIEW_CATEGORIES_PERM) {
			global $db;
			$db->query(VA_Categories::_sql("c.category_id=" . $db->tosql($category_id, INTEGER), $access_level));
			return $db->next_record();
		}
		
		/**
		 * Check if the category with this id exists
		 *
		 * @param Integer $category_id
		 * @return Boolean
		 */
		static function check_exists($category_id) {
			global $db;
			$params["where"] = " c.category_id=" . $db->tosql($category_id, INTEGER);
			$params["no_acls"]  = true;
			$db->query(VA_Categories::_sql($params, 0));
			return $db->next_record();
		}
		
		/**
		 * Find all categories availiable by selected access level
		 *
		 * @param String $where: please enter search that will be added to global search, c. - is abbr for the category
		 * @param Constant $access_level: VIEW_CATEGORIES_PERM, VIEW_CATEGORIES_ITEMS_PERM, VIEW_ITEMS_PERM, ADD_ITEMS_PERM
		 * @return Array
		 */
		static function find_all_ids($where = "", $access_level = VIEW_CATEGORIES_PERM) {
			global $db;
			
			$db->query(VA_Categories::_sql($where, $access_level));

			$ids = array();
			while ($db->next_record()) {
				$id = $db->f(0);
				if (!in_array($id, $ids)) {
					$ids[] = $id;
				}
			}
			
			return $ids;
		}
		
		static function find_all($key_field = "c.category_id", $fields = array(), $params = "", $access_level = VIEW_CATEGORIES_PERM) {
			global $db;			
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
			
			$db->query(VA_Categories::_sql($params_prepared, $access_level));
			
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

		static function categories_stat($categories_ids) {
			global $db, $dbs, $table_prefix;
			if (!isset($dbs) || !is_object($dbs)) { $dbs = new VA_SQL($db); }

			$categories_number = 0; $subcategories_number = 0; 
			$total_products = 0; $shared_products_number = 0; $unique_products_number = 0;
			$categories_ids = preg_replace("/[\s\n\r]/s", "", $categories_ids);
			$main_categories = explode(",", $categories_ids);

			$all_categories_ids = array(); 
			$categories = array(); $subcategories = array();
			$sql  = " SELECT category_id,category_path,category_name FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
			$dbs->query($sql);
			while ($dbs->next_record()) {
				$category_id = $dbs->f("category_id");
				$category_name = $dbs->f("category_name");
				$category_path = $dbs->f("category_path");
				if (!in_array($category_id, $categories)) {
					$categories_number++;
					$categories[$category_id] = array("type" => "main", "id" => $category_id, "name" => $category_name, "path" => $category_path);
					$all_categories_ids[] = $category_id;
					$sql  = " SELECT category_id,category_path,category_name FROM " . $table_prefix . "categories ";
					$sql .= " WHERE category_path LIKE '" . $db->tosql($category_path.$category_id.",", TEXT, false) . "%'";
					$db->query($sql);
					while($db->next_record()) {
						$sub_category_id = $db->f("category_id");
						$sub_category_name = $db->f("category_name");
						$sub_category_path = $db->f("category_path");
						if (!in_array($sub_category_id, $main_categories) && !in_array($sub_category_id, $categories)) {
							$subcategories_number++;
							$categories[$sub_category_id] = array("type" => "sub", "id" => $sub_category_id, "name" => $sub_category_name, "path" => $sub_category_path);
							$all_categories_ids[] = $sub_category_id;
						}
					}
				}
			}
			// check categories for path parameter
			$path_ids = array();
			foreach ($categories as $category_id => $category_data) {
				$category_path = trim($category_data["path"], " ,");
				if ($category_path) {
					$ids = explode(",", $category_path);
					foreach ($ids as $id) {
						if ($id && !isset($categories[$id])) { $path_ids[] = $id; }
					}
				}
			}
			if (count($path_ids)) {
				$sql  = " SELECT category_id,category_path,category_name FROM " . $table_prefix . "categories ";
				$sql .= " WHERE category_id IN (" . $db->tosql($path_ids, INTEGERS_LIST) . ") ";
				$db->query($sql);
				while($db->next_record()) {
					$category_id = $db->f("category_id");
					$category_name = $db->f("category_name");
					$category_path = $db->f("category_path");
					$categories[$category_id] = array("type" => "path", "id" => $category_id, "name" => $category_name, "path" => $category_path);
				}
			}

			// calculate total products
			$items_ids = array(); $items = array(); $shared_products = array(); $unique_products = array();
			$sql  = " SELECT i.item_id,i.item_name FROM " . $table_prefix . "items_categories ic ";
			$sql .= " INNER JOIN " . $table_prefix . "items i ON ic.item_id=i.item_id ";
			$sql .= " WHERE category_id IN (" . $db->tosql($all_categories_ids, INTEGERS_LIST) . ") ";
			$sql .= " GROUP BY i.item_id,i.item_name ";
			$db->query($sql);
			while ($db->next_record()) {
				$item_id = $db->f("item_id");
				$item_name = $db->f("item_name");
				$items_ids[] = $item_id;
				$unique_products[$item_id] = array("id" => $item_id, "name" => $item_name);
				$total_products++;
			}
			if (count($items_ids)) {
				// get unique and shared products
				$sql  = " SELECT i.item_id, i.item_name FROM " . $table_prefix . "items i ";
				$sql .= " INNER JOIN (SELECT item_id FROM " . $table_prefix . "items_categories ";
				$sql .= " WHERE item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
				$sql .= " AND category_id NOT IN (" . $db->tosql($all_categories_ids, INTEGERS_LIST) . ") ";
				$sql .= " GROUP BY item_id) ic ON ic.item_id=i.item_id ";
				$db->query($sql);
				while ($db->next_record()) {
					$item_id = $db->f("item_id");
					$shared_products[$item_id] = array("id" => $item_id, "name" => $item_name);
					if (isset($unique_products[$item_id])) {
						unset($unique_products[$item_id]);
					}
				}
			}

			$shared_products_number = count($shared_products);
			$unique_products_number = count($unique_products);

			$stat = array(			
				"categories" => $categories,
				"categories_number" => $categories_number,
				"subcategories_number" => $subcategories_number,
				"total_products" => $total_products,
				"shared_products" => $shared_products,
				"shared_products_number" => $shared_products_number,
				"unique_products" => $unique_products,
				"unique_products_number" => $unique_products_number,
			);
			return $stat;
		}

		static function get_category_data($category_id) {
			global $va_data, $db, $table_prefix;
			$category_data = array();
			if (!isset($va_data["products"])) { $va_data["products"] = array(); }
			if (!isset($va_data["products"]["categories"])) { $va_data["products"]["categories"] = array(); }
			if (isset($va_data["products"]["categories"][$category_id])) {
				$category_data = $va_data["products"]["categories"][$category_id];
			} else {
				$params = array("select" => "c.*", "where" => "c.category_id=".$db->tosql($category_id, INTEGER));
				$sql = VA_Categories::sql($params, VIEW_CATEGORIES_PERM);
				$db->query($sql); 
				if ($db->next_record()) {
					$category_data = $db->Record;
				} else {
					$category_data = false;
				}
				$va_data["products"]["categories"][$category_id] = $category_data;
			}
			return $category_data;
		}

		static function get_path($category_id, $return_type = 1) {
			// 1 - return ids delimited by commas as string, 2 - return array of ids
			$path = array(); 
			while ($category_id && !isset($path[$category_id])) {
				$category_data = VA_Categories::get_category_data($category_id);
				if (is_array($category_data)) {
					$path[$category_id] = $category_id;
					$category_id = $category_data["parent_category_id"];
				} else {
					$category_id = 0;
				}
			} 
			$path[0] = 0;
			$path = array_reverse(array_keys($path));
			if ($return_type == 2) {
				return $path;
			} else {
				return implode(",", $path);
			}
		}
	}
	
	class VA_Products {		
		/**
		 * Internal function, that builds queries for products search
		 *
		 * @param String / Array $params: if string - than equals to normal where parameter, 
		 * if array - could be used for compplex requests,
		 * @param String $params["select"] - fields names, separated by comma
		 * @param String $params["where"]
		 * @param String $params["brackets"] - brackets for joins
		 * @param String $params["join"]  - join query part, if some subtables needed
		 * @param String $params["order"] - full order syntax, like "ORDER BY i.item_id", but also could has GROUP part if needed
		 * @param String $params["no_sites"] - dont include sites part in sql
		 * @param String $params["no_acls"] - dont include access levels part
		 * @param Constant $access_level: VIEW_CATEGORIES_ITEMS_PERM, VIEW_ITEMS_PERM
		 * @return String
		 */

		static function sql($params, $access_level, $is_showing = true, $is_count = false) {
			global $table_prefix, $db, $site_id, $language_code;
			$use_sites = (is_array($params) && isset($params["no_sites"])) ? false : true;
			$use_acls  = (is_array($params) && isset($params["no_acls"])) ? false : true;
			$access_out_stock = (is_array($params) && isset($params["access_out_stock"])) ? $params["access_out_stock"] : false;
			$no_subs = (is_array($params) && isset($params["no_subs"])) ? $params["no_subs"] : true;

			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_ITEMS_PERM;
			$access_field = (is_array($params) && isset($params["access_field"])) ? $params["access_field"] : false;
					
			$admin_id         = get_session("session_admin_id");
			$user_id          = get_session("session_user_id");
			$user_type_id     = get_session("session_user_type_id");
			$subscription_id  = get_session("session_subscription_id");
			$subscription_ids = get_session("session_subscription_ids");

			VA_Query::prepare_sql($params);
			if (count($params["select"]) == 0) {
				$params["select"][] = "i.item_id"; 
			}
			if (count($params["from"]) == 0) {
				$params["from"][] = $table_prefix."items i"; 
			}
			$params["where"][] = " i.is_approved=1 ";
			$params["where"][] = " (i.is_draft=0 OR i.is_draft IS NULL) ";

			if ($is_showing) {
				$params["where"][] = " i.is_showing=1 ";
			}
			if (!$access_out_stock) {
				$params["where"][] = " ((i.hide_out_of_stock=1 AND i.stock_level > 0) OR i.hide_out_of_stock=0 OR i.hide_out_of_stock IS NULL)";
			}
			$params["where"][] = " (i.language_code IS NULL OR i.language_code='' OR i.language_code=" . $db->tosql($language_code, TEXT) . ")";
			// get parent products only
			if ($no_subs) {
				$params["where"][] = " (i.parent_item_id=0 OR i.parent_item_id IS NULL) "; 
			}

			if ($use_sites && isset($site_id)) {
				if (isset($site_id)) {
					$params["where"][] = " (i.sites_all=1 OR s.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
					$params["join"][] = " LEFT JOIN " . $table_prefix . "items_sites AS s ON s.item_id=i.item_id";
				} else {
					$params["where"][] = " i.sites_all=1 ";
				}
			}
			if ($use_acls) {
				if (strlen($user_id) && strlen($subscription_ids)) {
					$where  = " (" . format_binary_for_sql("i.access_level", $access_level);
					if (strlen($admin_id)) {
						$where .= " OR " . format_binary_for_sql("i.admin_access_level", $access_level);
					}
					$where .= " OR ("  . format_binary_for_sql("ut.access_level", $access_level) . "  AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . " ) ";
					$where .= " OR ("  . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscription_ids, INTEGERS_LIST) . ")) )";
					$params["where"][] = $where;
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "i.admin_access_level, i.access_level AS user_access_level, ut.access_level AS type_access_level,sb.access_level AS sb_access_level "; 
						} else {
							$params["select"][] = "i.access_level AS user_access_level, ut.access_level AS type_access_level,sb.access_level AS sb_access_level "; 
						}
					}
				} elseif (strlen($user_id)) {
					$where  = " (" . format_binary_for_sql("i.access_level", $access_level);
					if (strlen($admin_id)) {
						$where  .= " OR " . format_binary_for_sql("i.admin_access_level", $access_level);
					}
					$where .= " OR ("  . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
					$params["where"][] = $where;
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "i.admin_access_level, i.access_level AS user_access_level, ut.access_level AS type_access_level "; 
						} else {
							$params["select"][] = "i.access_level AS user_access_level,ut.access_level AS type_access_level "; 
						}
					}
				} else {
					$where = " (" . format_binary_for_sql("i.guest_access_level", $access_level);
					if (strlen($admin_id)) {
						$where  .= " OR " . format_binary_for_sql("i.admin_access_level", $access_level);
					}
					$where .= ")";
					$params["where"][] = $where;
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "i.admin_access_level, i.guest_access_level AS user_access_level "; 
						} else {
							$params["select"][] = "i.guest_access_level AS user_access_level "; 
						}
					}
				}
				if (strlen($user_id)) {
					$params["join"][] = " LEFT JOIN " . $table_prefix . "items_user_types AS ut ON ut.item_id=i.item_id";
				}			
				if (strlen($subscription_ids)) {
					$params["join"][] = " LEFT JOIN " . $table_prefix . "items_subscriptions AS sb ON sb.item_id=i.item_id";
				}
			}

			$sql = VA_Query::build_sql($params);
			return $sql;
		}

		static function _sql($params, $access_level, $is_showing = true, $is_count = false) {
			$sql = VA_Products::sql($params, $access_level, $is_showing, $is_count);
			return $sql;
		}

		static function count($params = "", $access_level = VIEW_CATEGORIES_ITEMS_PERM, $is_showing = true) {
			$sql = VA_Products::sql($sql_params, $access_level, $is_showing);
		  $count_sql = "SELECT COUNT(*) FROM (".$sql.") count_sql";
			$count = get_db_value($count_sql);
			return $count;
		}

		static function data($params = "", $access_level = VIEW_CATEGORIES_ITEMS_PERM, $records_per_page = "", $page_number = "")
		{
			global $db;
			$data = array();
			$sql = VA_Products::_sql($params, $access_level);
			if ($records_per_page && $page_number) {
				$db->RecordsPerPage = $records_per_page;
				$db->PageNumber = $page_number;
			}
			$db->query($sql);
			while ($db->next_record()) {
				$data[] = $db->Record;
			}
			return $data;
		}

		/**
		 * Check if the item with this id exists
		 *
		 * @param Integer $item_id
		 * @return Boolean
		 */
		static function check_exists($item_id, $access_out_stock = false) {
			global $db;
			$params["where"] = " i.item_id=" . $db->tosql($item_id, INTEGER);
			$params["no_acls"]  = true;
			$params["access_out_stock"] = $access_out_stock;

			$db->query(VA_Products::_sql($params, 0));
			return $db->next_record();
		}
		/**
		 * Check if the item with this id is availiable with selected access level
		 *
		 * @param Integer $item_id
		 * @param Constant $access_level: VIEW_CATEGORIES_ITEMS_PERM, VIEW_ITEMS_PERM
		 * @return Boolean
		 */		

		static function check_permissions($item_id, $access_level = VIEW_ITEMS_PERM, $is_showing = true, $access_out_stock = false) {
			global $db;

			$params = array();
			$params["where"] = " i.item_id=" . $db->tosql($item_id, INTEGER);
			$params["access_out_stock"] = $access_out_stock;
			$params["no_subs"] = false; // check subproduct as well

			$db->query(VA_Products::_sql($params, $access_level, $is_showing));
			return $db->next_record();
		}
		/**
		 * Find all availiable items ids
		 * @param String / Array $params: if string - than equals to normal where parameter, 
		 * if array - could be used for compplex requests,
		 * @param String $params["where"]
		 * @param String $params["brackets"] - brackets for joins
		 * @param String $params["join"]  - join query part, if some subtables needed
		 * @param String $params["order"] - full order syntax, like "ORDER BY i.item_id", but also could has GROUP part if needed
		 * @param Constant $access_level: VIEW_CATEGORIES_ITEMS_PERM, VIEW_ITEMS_PERM
		 * @param Boolean $debug - turn on debug output
		 * @return Array
		 */
		static function find_all_ids($params = "", $access_level = VIEW_CATEGORIES_ITEMS_PERM, $debug = false) {
			global $db;
			if ($debug) {
				$sql = VA_Products::_sql($params, $access_level);
				if ($db->DBType == "mysql") {
					echo sql_explain($sql);
				} else {
					echo $sql;
				}				
			}	
			$db->query(VA_Products::_sql($params, $access_level));
			$ids = array();
			while ($db->next_record()) {
				$id = $db->f(0);
				if (!in_array($id, $ids)) {
					$ids[] = $id;
				}
			}
			return $ids;
		}
		/**
		 * Find all availiable items with specified fields, keys of returned array are items ids
		 * @param String $key_field
		 * @param Array $fields
		 * @param String / Array $params: if string - than equals to normal where parameter, 
		 * if array - could be used for compplex requests,
		 * @param String $params["where"]
		 * @param String $params["brackets"] - brackets for joins
		 * @param String $params["join"]  - join query part, if some subtables needed
		 * @param String $params["order"] - full order syntax, like "ORDER BY i.item_id", but also could has GROUP part if needed
		 * @param Constant $access_level: VIEW_CATEGORIES_ITEMS_PERM, VIEW_ITEMS_PERM
		 * @param Boolean $debug - turn on debug output
		 * @return Array
		 */
		static function find_all($key_field = "i.item_id", $fields = array(), $params = "", $access_level = VIEW_CATEGORIES_ITEMS_PERM, $debug = false) {
			global $db;			
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
				$sql = VA_Products::_sql($params_prepared, $access_level);
				if ($db->DBType == "mysql") {
					echo sql_explain($sql);
				} else {
					echo $sql;
				}
			}
			$db->query(VA_Products::_sql($params_prepared, $access_level));
			
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
		/**
		 * Find category id for selected item
		 * @param Integer $item_id
		 * @param Constant $access_level: VIEW_CATEGORIES_ITEMS_PERM, VIEW_ITEMS_PERM
		 * @return Integer
		 */
		static function get_category_id($item_id, $access_level = VIEW_ITEMS_PERM) {
			global $db, $table_prefix;
			$params = array();
			$params["select"] = "c.category_id";
			$params["where"]  = "ic.item_id=" . $db->tosql($item_id, INTEGER);
			$params["brackets"]  = "(";
			$params["join"]  = "INNER JOIN " . $table_prefix . "items_categories ic ON ic.category_id = c.category_id)";
			$db->query(VA_Categories::_sql($params, $access_level));
			if ($db->next_record()) {
				return $db->f(0);
			} else {
				return 0;
			}		
		}		

		static function keywords_sql($keywords_string, &$kw_no_records, &$kw_rank, &$kw_join, &$kw_where)
		{
			global $db, $table_prefix, $settings, $va_keyword_like;

			// check if keywords search is active
			$keywords_search = get_setting_value($settings, "keywords_search", 0);
			$kw_no_records = false; $kw_rank = ""; $kw_join = ""; $kw_where = "";
			$s_tit = get_param("s_tit");
			$s_cod = get_param("s_cod");
			$s_des = get_param("s_des");

			// get words for search
			$keywords_string = trim($keywords_string);
			$keywords_string = preg_replace(KEYWORD_REPLACE_REGEXP, " ", $keywords_string);
			$kw_values = explode(" ", $keywords_string);
			foreach ($kw_values as $id => $word) {
				if(function_exists("mb_strtolower")) {
					$word = mb_strtolower($word, "UTF-8");
				} else {
					$word = strtolower($word);
				}
				$word = trim($word, "'");
				if (strlen($word)) {
					$kw_values[$id] = $word;
				} else {
					unset($kw_values[$id]);
				}
			}

			if ($keywords_string && !sizeof($kw_values)) { 
				$kw_rank = "0";
				$kw_where = "true=false";
				$kw_no_records = true;
				return; 
			}

			if ($keywords_search) {
				$keywords_ids = array();
				foreach ($kw_values as $id => $word) {
					$sql  = " SELECT keyword_id FROM " . $table_prefix . "keywords ";
					if (isset($va_keyword_like) && $va_keyword_like) {
						$sql .= " WHERE keyword_name LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					} else {
						$sql .= " WHERE keyword_name=" . $db->tosql($word, TEXT);
					}
					$db->query($sql);
					if ($db->next_record()) {
						do {
							if (!isset($keywords_ids[$id])) { $keywords_ids[$id] = array(); }
							$keywords_ids[$id][] = $db->f("keyword_id");
						} while ($db->next_record());
					} else {
						$kw_no_records = true;
					}
				}
	  
				if (!$kw_no_records) {

					// search by certain fields 
					$kw_field = "";
					if (!($s_tit && $s_cod && $s_des)) {
						if ($s_tit) {
							$kw_field .= " field_id=1 ";
						} 
						if ($s_cod) {
							if ($kw_field) { $kw_field .= " OR "; } 
							$kw_field .= " field_id=2 OR field_id=3 ";
						}
						if ($s_des) {
							if ($kw_field) { $kw_field .= " OR "; } 
							$kw_field .= " field_id>3 ";
						}
					}

					foreach ($keywords_ids as $id => $keyword_ids) {
						$ki = $id;
						if ($kw_rank) { $kw_rank .= "+"; }
						$kw_rank .= "rank" . $ki;
						$kw_join .= " INNER JOIN (";
						$kw_join .= " SELECT item_id, MAX(keyword_rank) AS rank" . $ki;
						$kw_join .= " FROM ".$table_prefix."keywords_items WHERE keyword_id IN (" . $db->tosql($keyword_ids, INTEGERS_LIST) . ")";
						if ($kw_field) { $kw_join .= " AND (" . $kw_field . ") "; }
						$kw_join .= " GROUP BY item_id         ";
						$kw_join .= " ) k".$ki . " ON k".$ki.".item_id=i.item_id ";
					}
					$kw_rank = "(" . $kw_rank . ")";
				} else {
					$kw_rank = "0";
					$kw_where = "true=false";
				}
			} else {
				// use simple search by DB fields
				foreach ($kw_values as $id => $word) {
					$s_fields = 0;
					if (strlen($kw_where)) $kw_where .= " AND ";
					$kw_where .= " ( ";
					if ($s_tit == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " i.item_name LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					if ($s_des == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " i.full_description LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
						$kw_where .= " OR i.short_description LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					if ($s_cod == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " i.item_code LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
						$kw_where .= " OR i.manufacturer_code LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					if ($s_fields == 0) {
						$kw_where .= " i.item_name LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
						$kw_where .= " OR i.item_code LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
						$kw_where .= " OR i.manufacturer_code LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
						$kw_where .= " OR i.short_description LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
						$kw_where .= " OR i.full_description LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					$kw_where .= " ) ";
				}
			}

		}

		static function show_products($params)
		{
			global $t, $db, $table_prefix, $va_data, $settings, $sc_params, $currency, $script_name, $page_friendly_url, $page_friendly_params;
			// clear template blocks and tags before parse
			$tags= array(
				"pb_id", "form_params", "sc_message", "sc_errors", "products_rows", "products_cols", "navigator_block", "product_rating", "product_views", "product_image", 
				"price_block", "sales", "save", "points_price_block", "reward_points_block", "reward_credits_block", 
				"quantity", "buy_button", "cart_add_button", "cart_add_disabled", "add_button", "add_button_disabled", "view_button", "checkout_button", "checkout_button", 
				"short_description", "full_description", "highlights", "features", "special", "notes", 
				"data_js", "slider_class", "slider_style", "pb_id", "slider_type", "transition_delay", "transition_duration", 
				"data_style", "data_block_style", "data_slide_style", "data_width_style", "columns_class", 
			);
			foreach ($tags as $tag_name) { $t->set_var($tag_name, ""); }
			// end of clearing

			// global array to use in different blocks
			if(!isset($va_data)) { $va_data = array(); }
			if(!isset($va_data["products_index"])) { $va_data["products_index"] = 0; }
			$start_index = $va_data["products_index"] + 1;

			// check shopping cart in case we need to show Checkout button
			$shopping_cart = get_session("shopping_cart");

			// global settings
			$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
			$friendly_extension = get_setting_value($settings, "friendly_extension", "");
			$display_products   = get_setting_value($settings, "display_products", 0);
			$confirm_add   = get_setting_value($settings, "confirm_add", 1);
			$redirect_to_cart = get_setting_value($settings, "redirect_to_cart", ""); 
			if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
				$pass_parameters = get_transfer_params($page_friendly_params);
				$current_page = $page_friendly_url . $friendly_extension;
			} else {
				$current_page = get_custom_friendly_url($script_name);
				$pass_parameters = get_transfer_params();
			}
			// points settings
			$points_system = get_setting_value($settings, "points_system", 0);
			$points_conversion_rate = get_setting_value($settings, "points_conversion_rate", 1);
			$points_decimals = get_setting_value($settings, "points_decimals", 0);
			$points_price_list = get_setting_value($settings, "points_price_list", 0);
			$reward_points_list = get_setting_value($settings, "reward_points_list", 0);
			$points_prices = get_setting_value($settings, "points_prices", 0);
			// credit settings
			$credit_system = get_setting_value($settings, "credit_system", 0);
			$reward_credits_users = get_setting_value($settings, "reward_credits_users", 0);
			$reward_credits_list = get_setting_value($settings, "reward_credits_list", 0);
			// new product settings	
			$new_product_enable = get_setting_value($settings, "new_product_enable", 0);	
			$new_product_order  = get_setting_value($settings, "new_product_order", 0);	
			$new_product_field = "";
			if ($new_product_enable) {
				if ($new_product_order == 0) {
					$new_product_field = "issue_date";
				} elseif ($new_product_order == 1) {
					$new_product_field = "date_added";
				} elseif ($new_product_order == 2) {
					$new_product_field = "date_modified";
				}
			}
			// user and price field settings
			$user_id = get_session("session_user_id");
			$user_type_id = get_session("session_user_type_id");
			$user_info = get_session("session_user_info");
			$user_tax_free = get_setting_value($user_info, "tax_free", 0);
			$discount_type = get_setting_value($user_info, "discount_type");
			$discount_amount = get_setting_value($user_info, "discount_amount");
			$price_type = get_setting_value($user_info, "price_type");
			if ($price_type == 1) {
				$price_field = "trade_price";
				$sales_field = "trade_sales";
				$properties_field = "trade_properties_price";
			} else {
				$price_field = "price";
				$sales_field = "sales_price";
				$properties_field = "properties_price";
			}
			$price_matrix_list = false;
			// get image settings
			$watermark = false; $image_field = ""; $image_field_alt = "";
			$restrict_products_images = get_setting_value($settings, "restrict_products_images", "");
			$image_type = get_setting_value($params, "image", "small");	
			$product_no_image = get_setting_value($settings, "product_no_image", "");
			if (!preg_match("/no|tiny|small|big|large|super/", $image_type)) {
				$image_type = "small";
			} else if ($image_type == "large") {
				$image_type = "big";
			} 
			if ($image_type != "no") {
				$watermark = get_setting_value($settings, "watermark_".$image_type."_image", 0);
				$image_field = $image_type."_image";
				$image_field_alt = $image_type."_image_alt";
			}
			// end image settings

			// build cart_link param
			if ($friendly_urls && isset($page_friendly_url) && $page_friendly_url) {
				$pass_parameters = get_transfer_params($page_friendly_params);
				$current_page = $page_friendly_url . $friendly_extension;
			} else {
				$current_page = get_custom_friendly_url($script_name);
				$pass_parameters = get_transfer_params();
			}
			$rnd = mt_rand();
			$current_ts = va_timestamp();
    
			$query_string = get_query_string($pass_parameters, "", "", true);
			$rp = $current_page . $query_string;
			$cart_link  = $rp;
			$cart_link .= strlen($query_string) ? "&" : "?";
			$cart_link .= "rnd=" . $rnd . "&";
			
			// check block parameters
			$pb_id = $params["pb_id"];
			$recs = get_setting_value($params, "recs", 25);
			$max_recs = get_setting_value($params, "max_recs");
			$cols = get_setting_value($params, "cols", 1);
			$page_param = get_setting_value($params, "page_param");
			$page_number = get_setting_value($params, "page_number");
			$pages_number = get_setting_value($params, "pages", 5);
			$quantity_control = get_setting_value($params, "qty", "LABEL");
			$add_button = get_setting_value($params, "add");
			$view_button = get_setting_value($params, "view");
			$goto_button = get_setting_value($params, "goto");
			$wish_button = get_setting_value($params, "wish");
			$more_button = get_setting_value($params, "more");
			$show_rating = get_setting_value($params, "rating"); // option to show product rating
			// check description types to show
			$desc_types = get_setting_value($params, "desc", 1);
			if (!is_array($desc_types)) { $desc_types = array($desc_types); }
			// set variables
			$t->set_var("pb_id", intval($pb_id));
			$t->set_var("columns_class", "cols-".intval($cols));
			$t->set_var("redirect_to_cart", htmlspecialchars($redirect_to_cart));
			$t->set_var("sc_params", htmlspecialchars(json_encode($sc_params)));
			$t->set_var("out_stock_alert", str_replace("'", "\\'", htmlspecialchars(va_constant("PRODUCT_OUT_STOCK_MSG"))));

			// set necessary scripts
			set_script_tag("js/shopping.js");
			set_script_tag("js/ajax.js");
			set_script_tag("js/blocks.js");
			set_script_tag("js/images.js");

			$item_ids = array(); 
			$ids_sort = array(); // array with correct sorting for ids
			$items_sort = array(); // final array with correct sorting
			$item_index = 0;
			$total_records = 0;
			$sql = get_setting_value($params, "sql");
			$count = get_setting_value($params, "count", 1);
			if (is_array($sql)) {
				if ($count) {
					$count_sql = $sql;
					if (isset($count_sql["order"])) { unset($count_sql["order"]); }
					$sub_sql = VA_Products::sql($count_sql, VIEW_CATEGORIES_ITEMS_PERM);
					$count_sql = " SELECT COUNT(*) FROM (".$sub_sql.") count_sql ";
					// calculate records
					$total_records = get_db_value($count_sql);
					if ($max_recs && $total_records > $max_recs) { $total_records = $max_recs; }

					if ($page_param) {
						$n = new VA_Navigator($settings["templates_dir"], "navigator.html", $current_page);
						$page = $n->set_navigator("navigator", $page_param, CENTERED, $pages_number, $recs, $total_records, false, $pass_parameters, array(), "#products_".$pb_id);
					}
				}
				$sql = VA_Products::sql($sql, VIEW_CATEGORIES_ITEMS_PERM);
				$db->RecordsPerPage = $recs;
				if ($page_param) {
					$db->PageNumber = $page;	
				} else if ($page_number) {
					$db->PageNumber = $page_number;	
				}
				$db->query($sql);
				while ($db->next_record()) {
					$item_index++;
					$item_id = $db->f("item_id");
					$item_ids[] = $item_id;
					$ids_sort[$item_id] = $item_index;
				}
			} else {
				$item_ids = get_setting_value($params, "ids");
				if (!is_array($item_ids) && strlen($item_ids)) { 
					$item_ids = explode(",", $item_ids); 
				}
				$ids_sort = array_flip($item_ids);
			}

			// get product data with all necessary fields 
			$select  = " i.item_id, i.item_type_id, i.item_name, i.a_title, i.friendly_url, ";
			$select .= " i.special_offer, i.short_description, i.full_description, i.highlights, ";
			$select .= " i.buying_price, i." . $price_field . ", i.".$properties_field.", i." . $sales_field . ", i.is_sales, i.is_price_edit, ";
			$select .= " i.tax_id, i.tax_free, i.manufacturer_code, m.manufacturer_name, m.affiliate_code, i.buy_link, ";
			$select .= " i.is_points_price, i.points_price, i.reward_type, i.reward_amount, i.credit_reward_type, i.credit_reward_amount, ";
			$select .= " it.reward_type AS type_bonus_reward, it.reward_amount AS type_bonus_amount, ";
			$select .= " it.credit_reward_type AS type_credit_reward, it.credit_reward_amount AS type_credit_amount, ";
			$select .= " i.stock_level, i.use_stock_level, i.disable_out_of_stock, i.hide_out_of_stock, i.hide_add_list, ";
			$select .= " i.min_quantity, i.max_quantity, i.quantity_increment, ";
			$select .= " i.issue_date, i.date_added, i.date_modified, i.votes, i.points, ";
			$select .= " i.tiny_image, i.tiny_image_alt, i.small_image, i.small_image_alt, i.big_image, i.big_image_alt, super_image ";
			// build count and select JOIN
			$sql_join = array(
				" LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id ",
				" LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id ",
			);
			$sql_where = array(
				"i.item_id IN (" . $db->tosql($item_ids, INTEGERS_LIST) . ") ",
			);
			$sql_params = array("select" => $select, "join" => $sql_join, "where" => $sql_where, "access_field" => true);
			$sql = VA_Products::sql($sql_params, VIEW_CATEGORIES_ITEMS_PERM);
			// get product list
			$items = array();
			$db->query($sql);
			while ($db->next_record()) {
				$item_id = $db->f("item_id");
				$issue_date = $db->f("issue_date", DATETIME);
				$items[$item_id] = $db->Record;
				$items[$item_id]["issue_date"] = $issue_date;
				$items_sort[$item_id] = $ids_sort[$item_id]; // as there is a very rare possiblity that some item could be disabled after first query we rebuild sorting array
			}
			// sort products by their initial order when we get ids with ORDER BY condition
			array_multisort($items_sort, $items);
	
			// parse rows and cols for products
			$block_index = 0;
			$items_indexes = array();
			if (count($items)) {
				foreach ($items as $item_id => $item_data) {
					// indexes
					$block_index++;
					$va_data["products_index"]++;
					$items_indexes[] = $va_data["products_index"];
					$index = $va_data["products_index"];

					// product data from DB
					$item_id = $item_data["item_id"];
					$item_type_id = $item_data["item_type_id"];
					$item_name = get_translation($item_data["item_name"]);
					$product_params["form_id"] = $index;
					$product_params["item_name"] = strip_tags($item_name);
					$a_title = get_translation($item_data["a_title"]);
					$friendly_url = $item_data["friendly_url"];
					$special_offer = get_translation($item_data["special_offer"]);
					$short_description = get_translation($item_data["short_description"]);
					$full_description = get_translation($item_data["full_description"]);
					$special_offer = get_translation($item_data["special_offer"]);
					$highlights = get_translation($item_data["highlights"]);
					$small_image = $item_data["small_image"];
					$small_image_alt = get_translation($item_data["small_image_alt"]);
					$buy_link = $item_data["buy_link"];
					$affiliate_code = $item_data["affiliate_code"];
					$manufacturer_code = $item_data["manufacturer_code"];
					$manufacturer_name = $item_data["manufacturer_name"];
					$is_price_edit = $item_data["is_price_edit"];


					$issue_date_ts = 0;
					$issue_date = $item_data["issue_date"];
					if (is_array($issue_date)) {
						$issue_date_ts = va_timestamp($issue_date);
					}
					$stock_level = $item_data["stock_level"];
					$use_stock_level = $item_data["use_stock_level"];
					$disable_out_of_stock = $item_data["disable_out_of_stock"];
					$hide_out_of_stock = $item_data["hide_out_of_stock"];
					$hide_add_list = $item_data["hide_add_list"];
		    
					$min_quantity = $item_data["min_quantity"];
					$max_quantity = $item_data["max_quantity"];
					$quantity_increment = $item_data["quantity_increment"];
					$quantity_limit = ($use_stock_level && ($disable_out_of_stock || $hide_out_of_stock));


					// product rating calculation
					$votes = $item_data["votes"];
					$points = $item_data["points"];
					$rating_avg = $votes ? round($points / $votes, 2) : 0;

					// points data
					$is_points_price = $item_data["is_points_price"];
					$points_price = $item_data["points_price"];
					$reward_type = $item_data["reward_type"];
					$reward_amount = $item_data["reward_amount"];
					$credit_reward_type = $item_data["credit_reward_type"];
					$credit_reward_amount = $item_data["credit_reward_amount"];
					if (!strlen($reward_type)) {
						$reward_type = $item_data["type_bonus_reward"];
						$reward_amount = $item_data["type_bonus_amount"];
					}
					if (!strlen($credit_reward_type)) {
						$credit_reward_type = $item_data["type_credit_reward"];
						$credit_reward_amount = $item_data["type_credit_amount"];
					}
					if (!strlen($is_points_price)) {
						$is_points_price = $points_prices;
					}
	      
					if ($friendly_urls && $friendly_url) {
						$details_url = $friendly_url . $friendly_extension;
					} else {
						$details_url = "product_details.php?item_id=".urlencode($item_id);
					}
				
					if ($new_product_enable) {
						$new_product_date = $item_data[$new_product_field];
						$is_new_product   = is_new_product($new_product_date);
					} else {
						$is_new_product = false;
					}
					if ($is_new_product) {
						$t->set_var("product_new_class", " ico-new ");
					} else {
						$t->set_var("product_new_class", "");
					}
					
					$user_access_level = intval($item_data["user_access_level"]);
					$type_access_level = intval(get_setting_value($item_data, "type_access_level", 0)); // when user is not sign in this field is absent
					$admin_access_level = intval(get_setting_value($item_data, "admin_access_level", 0)); // field available only for logged in admins
					$sb_access_level = intval(get_setting_value($item_data, "sb_access_level", 0));

					$access_level = ($user_access_level|$type_access_level|$admin_access_level|$sb_access_level);
					$item_add_button = $add_button;
					if ($access_level&VIEW_ITEMS_PERM) {
						$t->set_var("restricted_class", "");
					} else {
						$t->set_var("restricted_class", " restricted ");
						$item_add_button = false;
					}
					$t->set_var("item_id", $item_id);
					$t->set_var("form_id", $index);
					$t->set_var("block_index", $block_index);
					$t->set_var("index", $va_data["products_index"]);
					$t->set_var("item_name", $item_name);
					$t->set_var("a_title", htmlspecialchars($a_title));
					$t->set_var("details_url", htmlspecialchars($details_url));

          // show product rating if approriate settings was set
					$t->set_var("product_rating", "");
					if ($show_rating) {
						$rating_int = intval($rating_avg);
						$rating_dec = round($rating_avg, 2) - $rating_int;
						if ($rating_int == 0) {
							$rating_class = "rating-0";
						} else if ($rating_dec >= 0.75) {
							$rating_class = "rating-".($rating_int+1)."-0";
						} else if ($rating_dec < 0.25) {
							$rating_class = "rating-".$rating_int."-0";
						} else {
							$rating_class = "rating-".$rating_int."-5";
						}
						$t->set_var("rating_value", number_format($rating_avg, 1, ".", ","));
						$t->set_var("rating_class", htmlspecialchars($rating_class));
						$t->sparse("product_rating", false);
					}

					// clear and show desc fields
					$t->set_var("desc_block", "");
					$t->set_var("short_description", "");
					$t->set_var("full_description", "");
					$t->set_var("special_offer", "");
					$t->set_var("highlights", "");
					$t->set_var("features", "");
					$t->set_var("notes", "");

					$desc_block = false;
					if (in_array("short", $desc_types)) {
						$desc_block = true;
						$t->set_var("desc_text", $short_description);
						$t->parse("short_description", false);
					} 
					if (in_array("full", $desc_types)) {
						$desc_block = true;
						$t->set_var("desc_text", $full_description);
						$t->parse("full_description", false);
					}
					if (in_array("high", $desc_types)) {
						$desc_block = true;
						$t->set_var("desc_text", $highlights);
						$t->parse("highlights", false);
					}
					if (in_array("spec", $desc_types)) {
						$desc_block = true;
						$t->set_var("desc_text", $special_offer);
						$t->parse("special_offer", false);
					}
					if (in_array("note", $desc_types)) {
						$desc_block = true;
						$t->set_var("desc_text", $notes);
						$t->parse("notes", false);
					}

					if ($desc_block) {
						$t->parse("desc_block", false);
					}

					// show/hide 'more' button
					if ($more_button) {
						$t->sparse("more_button", false);
					} else {
						$t->set_var("more_button", "");
					}

					$t->set_var("tax_price", "");
					$t->set_var("tax_sales", "");
	  
					if ($display_products != 2 || strlen($user_id)) {
						$price = $item_data[$price_field];
						$sales_price = $item_data[$sales_field];
						$is_sales = $item_data["is_sales"];
						$buying_price = $item_data["buying_price"];
						$properties_price = $item_data[$properties_field];
						$tax_id = $item_data["tax_id"];
						$tax_free = $item_data["tax_free"];
						if ($user_tax_free) { $tax_free = $user_tax_free; }
							
						$discount_applicable = 1;
						$q_prices    = get_quantity_price($item_id, 1);
						if (sizeof($q_prices)) {
							$user_price  = $q_prices [0];
							$discount_applicable = $q_prices [2];
							if ($is_sales) {
								$sales_price = $user_price;
							} else {
								$price = $user_price;
							}
						}				
	      
						if ($discount_applicable) {
							if ($discount_type == 1 || $discount_type == 3) {
								$price -= round(($price * $discount_amount) / 100, 2);
								$sales_price -= round(($sales_price * $discount_amount) / 100, 2);
							} elseif ($discount_type == 2) {
								$price -= round($discount_amount, 2);
								$sales_price -= round($discount_amount, 2);
							} elseif ($discount_type == 4) {
								$price -= round((($price - $buying_price) * $discount_amount) / 100, 2);
								$sales_price -= round((($sales_price - $buying_price) * $discount_amount) / 100, 2);
							}
						}
						// add options and components prices
						$price += $properties_price;
						$sales_price += $properties_price;
	      
						if ($is_price_edit) {
							$product_params["pe"] = 1;
							$formatted_price = ($price > 0) ? number_format($price, 2) : "";
							$t->set_var("price", $formatted_price."<input name=\"price".$index."\" type=\"hidden\" value=\"" . $formatted_price. "\">");
							$t->set_var("price_control", "<input name=\"price".$index."\" type=\"text\" class=\"price\" value=\"" . $formatted_price. "\">");
							$t->set_var("price_block_class", "price-edit");
							$t->sparse("price_block", false);
							$t->set_var("sales", "");
							$t->set_var("save", "");
						} else if ($is_sales && $sales_price != $price) {
							set_tax_price($va_data["products_index"], $item_type_id, $price, 1, $sales_price, $tax_id, $tax_free, "price", "sales_price", "tax_sales", false);
	      
							$t->sparse("price_block", false);
							$t->sparse("sales", false);
							$t->sparse("save", false);
						} else {
							set_tax_price($va_data["products_index"], $item_type_id, $price, 1, 0, $tax_id, $tax_free, "price", "", "tax_price", false);
	      
							$t->sparse("price_block", false);
							$t->set_var("sales", "");
							$t->set_var("save", "");
						}
	      
						$item_price = calculate_price($price, $is_sales, $sales_price);
						// show points price

						if ($points_system && $points_price_list) {
							if ($points_price <= 0) {
								$points_price = $item_price * $points_conversion_rate;
							}
							//$points_price += $components_points_price;
							//$selected_points_price = $selected_price * $points_conversion_rate;
							$product_params["base_points_price"] = $points_price;
							if ($is_points_price) {
								$t->set_var("points_rate", $points_conversion_rate);
								$t->set_var("points_decimals", $points_decimals);
								//$t->set_var("points_price", number_format($points_price + $selected_points_price, $points_decimals));
								$t->set_var("points_price", number_format($points_price, $points_decimals));
								$t->sparse("points_price_block", false);
							} else {
								$t->set_var("points_price_block", "");
							}
						}
	      
						// show reward points
						if ($points_system && $reward_points_list) {
							$reward_points = calculate_reward_points($reward_type, $reward_amount, $item_price, $buying_price, $points_conversion_rate, $points_decimals);
							//$reward_points += $components_reward_points;
	      
							$product_params["base_reward_points"] = $reward_points;
							if ($reward_type) {
								$t->set_var("reward_points", number_format($reward_points, $points_decimals));
								$t->sparse("reward_points_block", false);
							} else {
								$t->set_var("reward_points_block", "");
							}
						}
	      
						// show reward credits
						if ($credit_system && $reward_credits_list && ($reward_credits_users == 0 || ($reward_credits_users == 1 && $user_id))) {
							$reward_credits = calculate_reward_credits($credit_reward_type, $credit_reward_amount, $item_price, $buying_price);
							//$reward_credits += $components_reward_credits;
	      
							$product_params["base_reward_credits"] = $reward_credits;
							if ($credit_reward_type) {
								$t->set_var("reward_credits", currency_format($reward_credits));
								$t->sparse("reward_credits_block", false);
							} else {
								$t->set_var("reward_credits_block", "");
							}
						}
		    
						// show quantity control
						set_quantity_control($quantity_limit, $stock_level, $quantity_control, "products_".$pb_id, "", false, $min_quantity, $max_quantity, $quantity_increment);
					
						// show buttons				
						$internal_buy_link = "";
						$external_buy_link = $item_data["buy_link"];
						if (strlen($external_buy_link)) {
							$external_buy_link .= $item_data["affiliate_code"];
						} elseif ($quantity_control == "LISTBOX" || $quantity_control == "TEXTBOX" || $is_price_edit) {
							$t->set_var("wishlist_href", "javascript:document.products_" . $pb_id. ".submit();");
						} else {
							$internal_buy_link = $cart_link."cart=ADD&add_id=" . $item_id . "&rp=". urlencode($rp). "#p" . $pb_id;
							$t->set_var("wishlist_href", htmlspecialchars($cart_link."cart=WISHLIST&add_id=" . $item_id . "&rp=". urlencode($rp). "#p" . $pb_id));
						}
						set_buy_button($pb_id, $va_data["products_index"], $internal_buy_link, $external_buy_link);
				  
						$t->set_var("buy_button", "");
						$t->set_var("cart_add_button", "");
						$t->set_var("cart_add_disabled", "");
						$t->set_var("add_button", "");
						$t->set_var("add_button_disabled", "");

						if ($item_add_button) {
							if ($use_stock_level && $stock_level < 1 && $disable_out_of_stock) {
								if ($t->block_exists("cart_add_disabled")) {
									$t->sparse("cart_add_disabled", false);
								} else {
									$t->sparse("add_button_disabled", false);
								}
							} else {
								if ($external_buy_link && $t->block_exists("buy_button")) {
									$t->sparse("buy_button", false);
								} else {
									if (($use_stock_level && $stock_level < 1) || $issue_date_ts > $current_ts) {
										$t->set_var("ADD_TO_CART_MSG", va_constant("PRE_ORDER_MSG"));
									} else {
										$t->set_var("ADD_TO_CART_MSG", va_constant("ADD_TO_CART_MSG"));
									}
									if ($t->block_exists("cart_add_button")) {
										$t->sparse("cart_add_button", false);
									} else {
										$t->sparse("add_button", false);
									}
								}
							}
						}

						if (!$view_button) {
							$t->set_var("view_button", "");
						} else {
							$t->sparse("view_button", false);
						}
						if ($goto_button && is_array($shopping_cart) && sizeof($shopping_cart) > 0) {
							$t->sparse("checkout_button", false);
						} else {
							$t->set_var("checkout_button", "");
						}
						if ($user_id && !$buy_link && $wish_button) {
							$t->sparse("wishlist_button", false);
						} else {
							$t->set_var("wishlist_button", "");
						}

						set_product_params($product_params);
						$json_data = array(); // for compatability with older version
						$json_data["currency"] = $currency;
						$json_data = array_merge($json_data, $product_params);
						$t->set_var("product_data", htmlspecialchars(json_encode($json_data)));
					}
					/* TODO: probably add popup functionality like for special offer block 
					$image_offer_js = "";
					if ($popup_box) {
						$image_offer_js = " onmousemove=\"moveSpecialOffer(event);\" onmouseover=\"popupSpecialOffer('so_$item_id', 'block');\" onmouseout=\"popupSpecialOffer('so_$item_id', 'none');\" ";
					}
					$t->set_var("image_offer_js", $image_offer_js); */

          // set product image if it was selected
					$t->set_var("product_image", "");
					if ($image_field) {
						$product_image = $item_data[$image_field];
						$product_image_alt = get_translation($item_data[$image_field_alt]);
						if (!strlen($product_image)) {
							$image_exists = false;
							$product_image = $product_no_image;
						} elseif (!image_exists($product_image)) {
							$image_exists = false;
							$product_image = $product_no_image;
						} else {
							$image_exists = true;
						}
						if (strlen($product_image)) {
							if (preg_match("/^http(s)?:\/\//", $product_image)) {
								$image_size = "";
							} else {
								$image_size = @getimagesize($product_image);
								if ($image_exists && ($watermark || $restrict_products_images)) {
									$product_image = "image_show.php?item_id=".$item_id."&type=".$image_type."&vc=".md5($product_image);
								}
							}
							if (!strlen($product_image_alt)) {
								$product_image_alt = $item_name;
							}
							$t->set_var("alt", htmlspecialchars($product_image_alt));
							$t->set_var("src", htmlspecialchars($product_image));
							$t->parse("product_image", false);
						} 
					}

					$column_index = ($block_index % $cols) ? ($block_index % $cols) : $cols;
					$t->set_var("column_class", "col-".$column_index);

					$t->parse("products_cols");

					if($block_index % $cols == 0) {
						$t->parse("products_rows");
						$t->set_var("products_cols", "");
					}
				} while ($db->next_record());

				$t->set_var("items_indexes", implode(",", $items_indexes));
				$t->set_var("start_index", $start_index);
			}

			if ($block_index % $cols != 0) {
				$t->parse("products_rows");
			}
			// end of parse
			return $block_index;
		}

	}
