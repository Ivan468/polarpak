<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  forums_functions.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	class VA_Forum_Categories {
		
		static function sql($params) {
			global $table_prefix, $db, $site_id;

			$use_sites = isset($params["no_sites"]) ? false : true;

			VA_Query::prepare_sql($params);
			if (count($params["select"]) == 0) {
				$params["select"][] = "c.category_id"; 
			}
			if (count($params["from"]) == 0) {
				$params["from"][] = $table_prefix."forum_categories c"; 
			}
			$params["where"][] = " c.allowed_view=1 ";
			if ($use_sites && isset($site_id)) {
				if (isset($site_id)) {
					$params["where"][] = " (c.sites_all=1 OR cs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
					$params["join"][] = " LEFT JOIN " . $table_prefix . "forum_categories_sites cs ON cs.category_id=c.category_id";
				} else {
					$params["where"][] = " c.sites_all=1 ";
				}
			}
			$sql = VA_Query::build_sql($params);
			return $sql;
		}

		/**
		 * Internal sql function, that builds query for categories search
		 *
		 * @param Array / String $params
		 * @param Constant $access_level currently not used
		 * @return String
		 */
		static function _sql($params, $access_level) {
			global $table_prefix, $db, $site_id;
			
			$select = ""; $where = ""; $order = ""; $join = ""; $brackets = "";
			$use_sites = true;
			
			if (is_array($params)) {
				$select = isset($params["select"]) ? $params["select"] : "";
				$where  = isset($params["where"]) ? $params["where"] : "";
				$order  = isset($params["order"]) ? $params["order"] : "";
				$join   = isset($params["join"])  ? $params["join"] : "";
				$brackets = isset($params["brackets"])  ? $params["brackets"] : "";
				if (isset($params["no_sites"])) $use_sites = false;
			} else {
				$where = $params;
			}
			
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
					
			if (strlen($brackets)) {
				$sql .= $brackets;
			}
			
			$sql .= $table_prefix . "forum_categories c ";
			if ($use_sites && isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "forum_categories_sites cs ON cs.category_id=c.category_id)";
			}
			if (strlen($join)) {
				$sql .= $join;
			}
			$sql .= " WHERE c.allowed_view=1";
			if ($use_sites) {
				if (isset($site_id)) {
					$sql .= " AND (c.sites_all=1 OR cs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
				} else {
					$sql .= " AND c.sites_all=1 ";
				}
			}		
			if (strlen($where)) {
				$sql .= " AND " . $where;
			}
			
			return $sql;
		}
		
		/**
		 * Check if the category with this id exists
		 *
		 * @param Integer $category_id
		 * @return Boolean
		 */
		static function check_exists($category_id) {
			global $db, $table_prefix, $site_id;
			
			$sql  = " SELECT c.category_id FROM ";
			if (isset($site_id)) {
				$sql .= "( ";
			}
			$sql .= $table_prefix . "forum_categories c ";
			if (isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "forum_categories_sites s ON s.category_id=c.category_id)";
			}
			if (isset($site_id)) {
				$sql .= " WHERE (c.sites_all=1 OR s.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
			} else {
				$sql .= " WHERE c.sites_all=1 ";
			}
			$sql .= " AND c.category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);
			return $db->next_record();
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
			
			$db->query(VA_Forum_Categories::_sql($params_prepared, $access_level));
			
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
	
	class VA_Forums {		
		
		/**
		 * Internal function, that builds queries for forums search
		 *
		 * @param String / Array $params: if string - than equals to normal where parameter, 
		 * if array - could be used for compplex requests,
		 * @param String $params["select"] - fields names, separated by comma
		 * @param String $params["where"]
		 * @param String $params["brackets"] - brackets for joins
		 * @param String $params["join"]  - join query part, if some subtables needed
		 * @param String $params["order"] - full order syntax, like "ORDER BY i.item_id", but also could has GROUP part if needed
		 * @param Constant $access_level: VIEW_FORUM_PERM, VIEW_TOPICS_PERM, VIEW_TOPIC_PERM, POST_TOPICS_PERM, POST_REPLIES_PERM, POST_ATTACHMENTS_PERM
		 * @return String
		 */

		static function sql($params, $access_level) {
			global $table_prefix, $db, $site_id;

			$use_sites = isset($params["no_sites"]) ? false : true;
			$use_acls  = isset($params["no_acls"]) ? false : true;

			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_ITEMS_PERM;
			$access_field = (isset($params["access_field"])) ? $params["access_field"] : false;
					
			$admin_id         = get_session("session_admin_id");
			$user_id          = get_session("session_user_id");
			$user_type_id     = get_session("session_user_type_id");
			$subscription_id  = get_session("session_subscription_id");
			$subscription_ids = get_session("session_subscription_ids");

			VA_Query::prepare_sql($params);
			if (count($params["select"]) == 0) {
				$params["select"][] = "fl.forum_id"; 
			}
			if (count($params["from"]) == 0) {
				$params["from"][] = $table_prefix."forum_list fl"; 
			}
			$params["where"][] = " c.allowed_view=1 ";
			$params["join"][] = " INNER JOIN " . $table_prefix . "forum_categories c ON c.category_id=fl.category_id ";
			if ($use_sites && isset($site_id)) {
				if (isset($site_id)) {
					$params["where"][] = " (c.sites_all=1 OR cs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
					$params["join"][] = " LEFT JOIN " . $table_prefix . "forum_categories_sites cs ON cs.category_id=c.category_id";
				} else {
					$params["where"][] = " c.sites_all=1 ";
				}
			}
			if ($use_acls) {
				if (strlen($user_id) && strlen($subscription_ids)) {
					$where  = " (" . format_binary_for_sql("fl.access_level", $access_level);
					if (strlen($admin_id)) {
						$where .= " OR " . format_binary_for_sql("fl.admin_access_level", $access_level);
					}
					$where .= " OR ("  . format_binary_for_sql("ut.access_level", $access_level) . "  AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . " ) ";
					$where .= " OR ("  . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscription_ids, INTEGERS_LIST) . ")) )";
					$params["where"][] = $where;
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "(fl.admin_access_level&fl.access_level&ut.access_level&sb.access_level) AS user_access_level "; 
						} else {
							$params["select"][] = "(fl.access_level&ut.access_level&sb.access_level) AS user_access_level "; 
						}
					}
				} elseif (strlen($user_id)) {
					$where  = " (" . format_binary_for_sql("fl.access_level", $access_level);
					if (strlen($admin_id)) {
						$where  .= " OR " . format_binary_for_sql("fl.admin_access_level", $access_level);
					}
					$where .= " OR ("  . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
					$params["where"][] = $where;
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "(fl.admin_access_level&fl.access_level&ut.access_level) AS user_access_level "; 
						} else {
							$params["select"][] = "(fl.access_level&ut.access_level) AS user_access_level "; 
						}
					}
				} else {
					$where = " (" . format_binary_for_sql("fl.guest_access_level", $access_level);
					if (strlen($admin_id)) {
						$where  .= " OR " . format_binary_for_sql("fl.admin_access_level", $access_level);
					}
					$where .= ")";
					$params["where"][] = $where;
					if (strlen($admin_id)) {
						$where  .= " OR " . format_binary_for_sql("fl.admin_access_level", $access_level);
					}
					if ($access_field) {
						if (strlen($admin_id)) {
							$params["select"][] = "(fl.admin_access_level&fl.guest_access_level) AS user_access_level "; 
						} else {
							$params["select"][] = "(fl.guest_access_level) AS user_access_level "; 
						}
					}
				}
				if (strlen($user_id)) {
					$params["join"][] = " LEFT JOIN " . $table_prefix . "forum_user_types ut ON ut.forum_id=fl.forum_id ";
				}			
				if (strlen($subscription_ids)) {
					$params["join"][] = " LEFT JOIN " . $table_prefix . "forum_subscriptions sb ON sb.forum_id=fl.forum_id ";
				}
			}

			$sql = VA_Query::build_sql($params);
			return $sql;
		}

		static function _sql($params, $access_level) {
			global $table_prefix, $db, $site_id, $language_code;
			$select = "";
			$where = "";
			$order = "";
			$join = "";
			$brackets = "";
			if (is_array($params)) {
				$select = isset($params["select"]) ? $params["select"] : "";
				$where  = isset($params["where"]) ? $params["where"] : "";
				$order  = isset($params["order"]) ? $params["order"] : "";
				$join   = isset($params["join"])  ? $params["join"] : "";
				$brackets = isset($params["brackets"])  ? $params["brackets"] : "";
			} else {
				$where = $params;
			}
			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_ITEMS_PERM;
						
			$user_id         = get_session("session_user_id");
			$user_type_id    = get_session("session_user_type_id");
			$subscription_id = get_session("session_subscription_id");
			$subscription_ids = get_session("session_subscription_ids");
			
			$sql = " SELECT ";
			if (strlen($select)) {
				$sql .= $select;
			} else {
				$sql .= "fl.forum_id ";
			}
			
			$sql .= " FROM ( ";
			if (isset($site_id)) {
				$sql .= "(";
			};
			if (strlen($user_id)) {
				$sql .= "(";
			};
			if (strlen($subscription_ids)) {
				$sql .= "(";
			}
			if (strlen($brackets)) {
				$sql .= $brackets;
			};
			
			$sql .= " " . $table_prefix . "forum_list fl ";	
			$sql .= " INNER JOIN " . $table_prefix . "forum_categories c ON c.category_id=fl.category_id)";
			if (isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "forum_categories_sites s ON s.category_id=c.category_id)";	
			}
			if (strlen($user_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "forum_user_types ut ON ut.forum_id=fl.forum_id)";
			}
			if (strlen($subscription_ids)) {
				$sql .= " LEFT JOIN " . $table_prefix . "forum_subscriptions sb ON sb.forum_id=fl.forum_id)";
			}
			if (strlen($join)) {
				$sql .= $join;
			};	
			
			if (isset($site_id)) {
				$sql .= " WHERE (c.sites_all=1 OR s.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
			} else {
				$sql .= " WHERE c.sites_all=1 ";
			}			
			if (strlen($user_id) && strlen($subscription_ids)) {
				$sql .= " AND ( " . format_binary_for_sql("fl.access_level", $access_level);
				$sql .= " OR (  " . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") ";
				$sql .= " OR (  " . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscription_id, INTEGERS_LIST) . ")) )";
			} elseif (strlen($user_id)) {
				$sql .= " AND ( " . format_binary_for_sql("fl.access_level", $access_level);
				$sql .= " OR (  " . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
			} else {
				$sql .= " AND " . format_binary_for_sql("fl.guest_access_level", $access_level);
			}
			
			$sql .= " AND c.allowed_view = 1 ";
		
			if (strlen($where)) {
				$sql .= " AND " . $where;
			}
			
			if (strlen($order)) {
				$sql .= " " . $order;
			}
			
			return $sql;
		}
		
		/**
		 * Find all availiable forums ids
		 * @param String / Array $params: if string - than equals to normal where parameter, 
		 * if array - could be used for compplex requests,
		 * @param String $params["where"]
		 * @param String $params["brackets"] - brackets for joins
		 * @param String $params["join"]  - join query part, if some subtables needed
		 * @param String $params["order"] - full order syntax, like "ORDER BY i.item_id", but also could has GROUP part if needed
		 * @param Constant $access_level: VIEW_FORUM_PERM, VIEW_TOPICS_PERM, VIEW_TOPIC_PERM, POST_TOPICS_PERM, POST_REPLIES_PERM, POST_ATTACHMENTS_PERM
		 * @return Array
		 */
		static function find_all_ids($params = "", $access_level = VIEW_FORUM_PERM, $records_per_page = "", $page_number = "") {
			global $db;
			
			$sql = VA_Forums::_sql($params, $access_level);
			if ($records_per_page && $page_number) {
				$db->RecordsPerPage = $records_per_page;
				$db->PageNumber = $page_number;
			}
			$db->query($sql);			
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
		 * Find all availiable forums with specified fields, keys of returned array are items ids
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
		static function find_all($key_field = "fl.forum_id", $fields = array(), $params = "", $access_level = VIEW_CATEGORIES_ITEMS_PERM, $debug = false) {
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
				$sql = VA_Forums::_sql($params_prepared, $access_level);
				if ($db_type == "mysql") {
					echo sql_explain($sql);
				} else {
					echo $sql;
				}
			}
			$db->query(VA_Forums::_sql($params_prepared, $access_level));
			
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
		 * Check if the forum with this id is availiable with selected access level
		 *
		 * @param Integer $forum_id
		 * @param Constant $access_level: VIEW_FORUM_PERM, VIEW_TOPICS_PERM, VIEW_TOPIC_PERM, POST_TOPICS_PERM, POST_REPLIES_PERM, POST_ATTACHMENTS_PERM
		 * @return Boolean
		 */
		static function check_permissions($forum_id, $access_level = VIEW_FORUM_PERM) {
			global $db;
			$db->query(VA_Forums::_sql("fl.forum_id=" . $db->tosql($forum_id, INTEGER), $access_level));
			return $db->next_record();
		}
	}

?>