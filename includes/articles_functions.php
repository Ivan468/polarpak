<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  articles_functions.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

	class VA_Articles_Categories {
		
		static function sql($params, $access_level = VIEW_CATEGORIES_PERM) {
			global $table_prefix, $db, $site_id, $language_code;

			$use_acls  = isset($params["no_acls"]) ? false : true;
			$use_sites = isset($params["no_sites"]) ? false : true;
			$site_ids  = isset($params["site_ids"]) ? $params["site_ids"] : $site_id;

			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_CATEGORIES_PERM;
			$access_field = (isset($params["access_field"])) ? $params["access_field"] : false;
					
			$admin_id         = get_session("session_admin_id");
			$user_id          = get_session("session_user_id");
			$user_type_id     = get_session("session_user_type_id");
			$subscription_ids = get_session("session_subscription_ids");

			VA_Query::prepare_sql($params);
			if (count($params["select"]) == 0) {
				$params["select"][] = "c.category_id"; 
			}
			if (count($params["from"]) == 0) {
				$params["from"][] = $table_prefix."articles_categories c"; 
			}
			if ($use_sites) {
				if (strlen($site_ids)) {
					$params["where"][] = " (c.sites_all=1 OR cs.site_id IN (". $db->tosql($site_ids, INTEGERS_LIST) . ")) ";
					$params["join"][] = " LEFT JOIN " . $table_prefix . "articles_categories_sites AS cs ON cs.category_id=c.category_id";
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
					$params["join"][] = " LEFT JOIN " . $table_prefix . "articles_categories_types AS ut ON ut.category_id=c.category_id";
				}			
				if (strlen($subscription_ids)) {
					$params["join"][] = " LEFT JOIN " . $table_prefix . "articles_categories_subscriptions AS sb ON sb.category_id=c.category_id";
				}
			}

			$sql = VA_Query::build_sql($params);
			return $sql;
		}

		// check article TOP category ID
		static function top_id($category_id) {
			global $db, $table_prefix;
			$top_id = 0;
			$sql_data = array(
				"select" => "c.parent_category_id, c.category_path ",
				"where" => " c.category_id=" . $db->tosql($category_id, INTEGER),
				"no_acls" => true,
			);
			$sql = VA_Articles_Categories::sql($sql_data, VIEW_CATEGORIES_PERM);
			$db->query($sql);
			if ($db->next_record()) {
				$parent_category_id = $db->f("parent_category_id");
				$category_path = $db->f("category_path");
				$category_path = trim($category_path, "\t\r ,");
				$ids = explode(",", $category_path);
				if (count($ids) > 1) {
					$top_id = $ids[1];
				} else if (!$parent_category_id) {
					$top_id = $category_id;
				}
			}
			return $top_id;
		}


		static function _sql($params, $access_level) {
			global $table_prefix, $db, $site_id;
			
			if (!is_array($params)) { $params = array("where" => $params); } // convert old call with 'where' string to new array type

			$select = "";
			$where = "";
			$order = "";
			$join = "";
			$brackets = "";
			$use_sites = true;
			$use_acls  = true;
			
			$select = isset($params["select"]) ? $params["select"] : "";
			$where  = isset($params["where"]) ? $params["where"] : "";
			$order  = isset($params["order"]) ? $params["order"] : "";
			$join   = isset($params["join"])  ? $params["join"] : "";
			$brackets = isset($params["brackets"])  ? $params["brackets"] : "";
			if (isset($params["no_sites"])) $use_sites = false;
			if (isset($params["no_acls"]))  $use_acls = false;
			$site_ids  = isset($params["site_ids"]) ? $params["site_ids"] : $site_id;

			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_CATEGORIES_PERM;
						
			$admin_id        = get_session("session_admin_id");
			$user_id         = get_session("session_user_id");
			$user_type_id    = get_session("session_user_type_id");
			$subscription_id = get_session("session_subscription_id");
			$subscriptions_ids = get_session("session_subscriptions_ids");
			
			$sql = " SELECT ";
			if (strlen($select)) {
				$sql .= $select;
			} else {
				$sql .= " c.category_id ";
			}
			
			$sql .= " FROM ";
			
			if ($use_sites && strlen($site_ids)) {
				$sql .= " (";
			};
			
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " (";
				};
				if (strlen($subscriptions_ids)) {
					$sql .= " (";
				}
			}
			
			if (strlen($brackets)) {
				$sql .= $brackets;
			}
			
			$sql .= $table_prefix . "articles_categories c ";
			
			if ($use_sites && strlen($site_ids)) {
				$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_sites AS cs ON cs.category_id=c.category_id)";
			}
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_types AS ut ON ut.category_id=c.category_id)";
				}
				if (strlen($subscriptions_ids)) {
					$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_subscriptions AS sb ON sb.category_id=c.category_id)";
				}
			}
			if (strlen($join)) {
				$sql .= $join;
			}
			
			$sql .= " WHERE 1=1";
						
			if ($use_sites) {
				if (strlen($site_ids)) {
					$sql .= " AND (c.sites_all=1 OR cs.site_id IN (". $db->tosql($site_ids, INTEGERS_LIST) . ")) ";
				} else {
					$sql .= " AND c.sites_all=1 ";
				}
			}
			if ($use_acls) {
				if (strlen($admin_id)) {
					$sql .= " AND " . format_binary_for_sql("c.admin_access_level", $access_level);
				} else if (strlen($user_id) && strlen($subscriptions_ids)) {				
					$sql .= " AND ( " . format_binary_for_sql("c.access_level", $access_level);					
					$sql .= " OR ("   . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") ";
					$sql .= " OR ("   . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscriptions_ids, INTEGERS_LIST) . ")) )";
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
			
		static function get_category_data($category_id) {
			global $va_data, $db, $table_prefix;
			$category_data = array();
			if (!isset($va_data["articles_categories"])) { $va_data["articles_categories"] = array(); }
			if (isset($va_data["articles_categories"][$category_id])) {
				$category_data = $va_data["articles_categories"][$category_id];
			} else {
				$params = array("select" => "c.*", "where" => "c.category_id=".$db->tosql($category_id, INTEGER));
				$sql = VA_Articles_Categories::sql($params, VIEW_CATEGORIES_PERM);
				$db->query($sql); 
				if ($db->next_record()) {
					$category_data = $db->Record;
					$va_data["articles_categories"][$category_id] = $category_data;
				}
			}
			return $category_data;
		}

		static function check_permissions($category_id, $access_level = VIEW_CATEGORIES_PERM) {
			global $db;
			$params = array();
			$params["where"]   = " c.category_id=" . $db->tosql($category_id, INTEGER);
			$db->query(VA_Articles_Categories::_sql($params, $access_level));
			return $db->next_record();
		}
		
		static function check_exists($category_id) {
			global $db;
			$params = array();
			$params["where"]   = " c.category_id=" . $db->tosql($category_id, INTEGER);
			$params["no_acls"] = true;
			$db->query(VA_Articles_Categories::_sql($params, 0));
			return $db->next_record();
		}
		
		static function find_all_ids($params = "", $access_level = VIEW_CATEGORIES_PERM) {
			global $db;	
			$db->query(VA_Articles_Categories::_sql($params, $access_level));
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
				$sql = VA_Articles_Categories::_sql($params_prepared, $access_level);
				if ($db_type == "mysql") {
					echo sql_explain($sql);
				} else {
					echo $sql;
				}
			}
			$db->query(VA_Articles_Categories::_sql($params_prepared, $access_level));
			
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
		
	class VA_Articles {

		static function sql($params, $access_level = VIEW_CATEGORIES_ITEMS_PERM) {
			global $table_prefix, $db, $site_id, $language_code;

			$use_acls  = isset($params["no_acls"]) ? false : true;
			$authors_filter = (isset($params["authors"]) && $params["authors"]) ? true : false;
			$albums_filter = (isset($params["albums"]) && $params["albums"]) ? true : false;
			$roles_filter = (isset($params["roles"]) && $params["roles"]) ? true : false;
			$use_sites = (isset($params["no_sites"]) && $params["no_sites"]) ? false : true;
			$site_ids  = isset($params["site_ids"]) ? $params["site_ids"] : $site_id;

			if ($albums_filter) { $authors_filter = true; }

			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_CATEGORIES_ITEMS_PERM;
			$access_field = (isset($params["access_field"])) ? $params["access_field"] : false;
					
			$admin_id         = get_session("session_admin_id");
			$user_id          = get_session("session_user_id");
			$user_type_id     = get_session("session_user_type_id");
			$subscription_ids = get_session("session_subscription_ids");

			VA_Query::prepare_sql($params);
			if (count($params["select"]) == 0) {
				$params["select"][] = "a.article_id"; 
			}
			if (count($params["from"]) == 0) {
				$params["from"][] = $table_prefix."articles a"; 
			}
			$params["join"][]	= " LEFT JOIN " . $table_prefix . "articles_assigned aa ON a.article_id=aa.article_id ";
			$params["join"][]	= " LEFT JOIN " . $table_prefix . "articles_categories c ON aa.category_id=c.category_id ";
			$params["join"][]	= " LEFT JOIN " . $table_prefix . "articles_statuses st ON a.status_id=st.status_id ";
			if ($authors_filter) {
				$params["join"][]	= " LEFT JOIN " . $table_prefix . "articles_authors aaut ON aaut.article_id=a.article_id ";
				$params["join"][]	= " LEFT JOIN " . $table_prefix . "authors aut ON aut.author_id=aaut.author_id ";
				$params["join"][]	= " LEFT JOIN " . $table_prefix . "authors_sites auts ON auts.author_id=aaut.author_id ";	
			}
			if ($albums_filter) {
				$params["join"][]	= " LEFT JOIN " . $table_prefix . "albums_authors aalb ON aalb.author_id=aut.author_id ";
				$params["join"][]	= " LEFT JOIN " . $table_prefix . "albums alb ON alb.album_id=aalb.album_id ";
			}
			if ($roles_filter) {
				$params["join"][] = " LEFT JOIN " . $table_prefix ."authors_roles arol ON aaut.role_id=arol.role_id ";
			}

			$params["where"][] = "st.allowed_view=1";
			$params["where"][] = "(a.is_draft=0 OR a.is_draft IS NULL)";

			if ($use_sites) {
				if (strlen($site_ids)) {
					$params["where"][] = " (c.sites_all=1 OR cs.site_id IN (". $db->tosql($site_ids, INTEGERS_LIST) . ")) ";
					$params["join"][] = " LEFT JOIN " . $table_prefix . "articles_categories_sites AS cs ON cs.category_id=c.category_id";
				} else {
					$params["where"][] = " c.sites_all=1 ";
				}
				if ($authors_filter) {
					$params["where"][] = " (aut.sites_all=1 OR auts.site_id IN (". $db->tosql($site_ids, INTEGERS_LIST) . ")) ";
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
					$params["join"][] = " LEFT JOIN " . $table_prefix . "articles_categories_types AS ut ON ut.category_id=c.category_id";
				}			
				if (strlen($subscription_ids)) {
					$params["join"][] = " LEFT JOIN " . $table_prefix . "articles_categories_subscriptions AS sb ON sb.category_id=c.category_id";
				}
			}

			$sql = VA_Query::build_sql($params);
			return $sql;
		}

		static function _sql($params, $access_level) {
			global $table_prefix, $db, $site_id;
			
			if (!is_array($params)) { $params = array("where" => $params); } // convert old call with 'where' string to new array type

			$select = "";
			$where = "";
			$order = "";
			$join = "";
			$brackets = "";
			$use_sites = true;
			$use_acls  = true;
			
			$select = isset($params["select"]) ? $params["select"] : "";
			$where  = isset($params["where"]) ? $params["where"] : "";
			$order  = isset($params["order"]) ? $params["order"] : "";
			$join   = isset($params["join"])  ? $params["join"] : "";
			$brackets = isset($params["brackets"])  ? $params["brackets"] : "";
			if (isset($params["no_sites"])) $use_sites = false;
			if (isset($params["no_acls"]))  $use_acls = false;
			$site_ids  = isset($params["site_ids"]) ? $params["site_ids"] : $site_id;
						
			$access_level = (int) $access_level;
			if (!$access_level) $access_level = VIEW_CATEGORIES_ITEMS_PERM;
					
			$admin_id        = get_session("session_admin_id");
			$user_id         = get_session("session_user_id");
			$user_type_id    = get_session("session_user_type_id");
			$subscription_id = get_session("session_subscription_id");
			$subscriptions_ids = get_session("session_subscriptions_ids");
			
			$sql = " SELECT ";
			if (strlen($select)) {
				$sql .= $select;
			} else {
				$sql .= " a.article_id ";
			}
			$sql .= " FROM ";
			
			
			if ($use_sites && strlen($site_ids)) {
				$sql .= " (";
			}
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " (";
				}
				if (strlen($subscriptions_ids)) {
					$sql .= " (";
				}
			}
			if (strlen($brackets)) {
				$sql .= $brackets;
			}
			
			$sql .= " ((( " . $table_prefix . "articles a ";
			$sql .= " LEFT JOIN " . $table_prefix . "articles_assigned aa ON a.article_id=aa.article_id)";
			$sql .= " LEFT JOIN " . $table_prefix . "articles_categories c ON aa.category_id=c.category_id)";
			$sql .= " LEFT JOIN " . $table_prefix . "articles_statuses st ON a.status_id=st.status_id)";
			
			if ($use_sites && strlen($site_ids)) {
				$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_sites AS cs ON cs.category_id=c.category_id)";
			}
			if ($use_acls) {
				if (strlen($user_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_types AS ut ON ut.category_id=c.category_id)";
				}			
				if (strlen($subscriptions_ids)) {
					$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_subscriptions AS sb ON sb.category_id=c.category_id)";
				}
			}
			if (strlen($join)) {
				$sql .= $join;
			}
			
			$sql .= " WHERE st.allowed_view=1 AND (a.is_draft=0 OR a.is_draft IS NULL) ";

			
			if ($use_sites) {
				if (strlen($site_ids)) {
					$sql .= " AND (c.sites_all=1 OR cs.site_id IN (". $db->tosql($site_ids, INTEGERS_LIST) . ")) ";
				} else {
					$sql .= " AND c.sites_all=1 ";					
				}
			}
				
			if ($use_acls) {
				if (strlen($admin_id)) {
					$sql .= " AND " . format_binary_for_sql("c.admin_access_level", $access_level);
				} else if (strlen($user_id) && strlen($subscriptions_ids)) {
					$sql .= " AND ( " . format_binary_for_sql("c.access_level", $access_level);
					$sql .= " OR ( "  . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") ";
					$sql .= " OR ( "  . format_binary_for_sql("sb.access_level", $access_level) . " AND sb.subscription_id IN (". $db->tosql($subscriptions_ids, INTEGERS_LIST) . ")) )";
				} elseif (strlen($user_id)) {
					$sql .= " AND ( " . format_binary_for_sql("c.access_level", $access_level);
					$sql .= " OR ( "  . format_binary_for_sql("ut.access_level", $access_level) . " AND ut.user_type_id=". $db->tosql($user_type_id, INTEGER, true, false) . ") )";
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

		static function keywords_sql($keywords_string, &$kw_no_records, &$kw_rank, &$kw_join, &$kw_where)
		{
			global $db, $table_prefix, $settings, $va_keyword_like;

			// check if keywords search is active
			$articles_settings = get_settings("articles");
			$keywords_search = get_setting_value($articles_settings, "keywords_search", 0);
			$kw_no_records = false; $kw_rank = ""; $kw_join = ""; $kw_where = "";
			$s_tit = get_param("s_tit");
			$s_des = get_param("s_des");
			$s_aut = get_param("s_aut");
			$s_alb = get_param("s_alb");

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
					if ($s_tit) {
						$kw_field .= " field_id=1 ";
					} 
					if ($s_aut) {
						if ($kw_field) { $kw_field .= " OR "; } 
						$kw_field .= " field_id=2 ";
					}
					if ($s_alb) {
						if ($kw_field) { $kw_field .= " OR "; } 
						$kw_field .= " field_id=3 ";
					}
					if ($s_des) {
						if ($kw_field) { $kw_field .= " OR "; } 
						$kw_field .= " field_id=4 OR field_id=5 ";
					}

					foreach ($keywords_ids as $id => $keyword_ids) {
						$ki = $id;
						if ($kw_rank) { $kw_rank .= "+"; }
						$kw_rank .= "rank" . $ki;
						$kw_join .= " INNER JOIN (";
						$kw_join .= " SELECT item_id, MAX(keyword_rank) AS rank" . $ki;
						$kw_join .= " FROM ".$table_prefix."keywords_articles WHERE keyword_id IN (" . $db->tosql($keyword_ids, INTEGERS_LIST) . ")";
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
				if (!$s_tit && !$s_des && !$s_aut && !$s_alb) {
					$s_tit = 1; $s_des = 1; $s_aut = 1; $s_alb = 1;
				}

				foreach ($kw_values as $id => $word) {
					$s_fields = 0;
					if (strlen($kw_where)) $kw_where .= " AND ";
					$kw_where .= " ( ";
					if ($s_tit == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " a.article_title LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					if ($s_aut == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " aut.author_name LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					if ($s_aut == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " aut.other_name LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					if ($s_aut == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " aut.extra_name LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					if ($s_alb == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " alb.album_name LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					if ($s_des == 1) {
						if ($s_fields > 0) {$kw_where .= " OR ";}
						$s_fields++;
						$kw_where .= " a.full_description LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
						$kw_where .= " OR a.short_description LIKE '%" . $db->tosql($word, TEXT, false) . "%'";
					}
					$kw_where .= " ) ";
				}
			}
		}
		
		static function check_permissions($article_id, $category_id = 0, $access_level = VIEW_CATEGORIES_ITEMS_PERM) {
			global $db;
			$where = " a.article_id=" . $db->tosql($article_id, INTEGER);
			if ($category_id) {
				$where .= " AND c.category_id=" . $db->tosql($category_id, INTEGER);
			}			
			$params = array("where" => $where);
			$db->query(VA_Articles::_sql($params, $access_level));
			return $db->next_record();
		}
		
		static function check_exists($article_id, $category_id = false) {
			global $db;
			$params = array();
			$params["where"] = " a.article_id=" . $db->tosql($article_id, INTEGER);
			if ($category_id) {
				$params["where"] .= " AND c.category_id=" . $db->tosql($category_id, INTEGER);
			}
			$params["no_acls"]  = true;
			$db->query(VA_Articles::_sql($params, 0));
			return $db->next_record();
		}
		
		static function get_category_id($article_id, $access_level = VIEW_CATEGORIES_ITEMS_PERM) {
			global $db;
			$params = array();
			$params["select"] = "aa.category_id";
			$params["where"]  = "a.article_id=" . $db->tosql($article_id, INTEGER);
			$db->query(VA_Articles::_sql($params, $access_level));
			if ($db->next_record()) {
				return $db->f(0);
			} else {
				return 0;
			}
		}
		
		static function get_top_id($article_id) {
			global $db;
			$params = array();
			$params["select"]    = "c.category_id, c.category_path";
			$params["where"]     = "a.article_id=" . $db->tosql($article_id, INTEGER);
			$params["no_sites"] = true;
			$params["no_acls"]  = true;
			$db->query(VA_Articles::_sql($params, 0));
			if ($db->next_record()) {
				$category_id = $db->f(0);
				$category_path = $db->f(1);
				$tmp = explode(",", $category_path);
				if (isset($tmp[1]) && $tmp[1]) {
					return $tmp[1];
				} else {
					return $category_id;
				}
			} else {
				return 0;
			}
		}
		
		static function find_all_ids($params = "", $access_level = VIEW_CATEGORIES_ITEMS_PERM) {
			global $db;	
			if (!is_array($params)) {
				$params = array("where" => $params, "group" => "a.article_id");
			} else if (is_array($params) && !isset($params["group"])) {
				$params["group"] = "a.article_id";
			}
			$sql = VA_Articles::sql($params, $access_level);
			$db->query($sql);
			$ids = array();
			while ($db->next_record()) {
				$ids[] = $db->f(0);;
			}
			return $ids;
		}

		static function find_all($key_field = "a.article_id", $fields = array(), $params = "", $access_level = VIEW_CATEGORIES_ITEMS_PERM, $debug = false) {
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
				$sql = VA_Articles::_sql($params_prepared, $access_level);
				if ($db_type == "mysql") {
					echo sql_explain($sql);
				} else {
					echo $sql;
				}
			}
			$db->query(VA_Articles::_sql($params_prepared, $access_level));
			
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

		static function article_authors($article_data)
		{
			$article_authors = isset($article_data["authors"]) ? $article_data["authors"] : "";
			if (is_array($article_authors)) {
				$authors_first = ""; $authors_default = ""; $authors_hidden = ""; $authors_featured = "";
				foreach ($article_authors as $author_data)  {
					$author_name = $author_data["name"];
					$role_code = $author_data["role"]; 
					if ($role_code == "first") {
						if (strlen($authors_first)) { $authors_first .= " & "; }
						$authors_first .= $author_name;
					} else if ($role_code == "ft" || $role_code == "feat" || $role_code == "featured") {
						if (strlen($authors_featured)) { $authors_featured .= " & "; }
						$authors_featured .= $author_name;
					} else if ($role_code == "hide" || $role_code == "hidden") {
						if (strlen($authors_hidden)) { $authors_hidden .= " & "; }
						$authors_hidden .= $author_name;
					} else {
						if (strlen($authors_default)) { $authors_default .= " & "; }
						$authors_default .= $author_name;
					}
				}
				$authors_names = $authors_first;
				if ($authors_names && $authors_default) { $authors_names .= " & "; }
				$authors_names .= $authors_default;
	    
				$article_data["authors_names"] = $authors_names;
				$article_data["authors_featured"] = $authors_featured;
			}
			return $article_data;
		}

				
		static function delete($articles_ids) {
			global $db, $table_prefix;
			
			if (!strlen($articles_ids)) return false;
			
			$db->query("DELETE FROM " . $table_prefix . "articles_assigned WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "articles_forum_topics WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "articles_related WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "articles_items_related WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "articles_images WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")");
			$db->query("DELETE FROM " . $table_prefix . "articles_reviews WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")"); 
			$db->query("DELETE FROM " . $table_prefix . "articles_authors WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")"); 
			$db->query("DELETE FROM " . $table_prefix . "articles_albums WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")"); 
			$db->query("DELETE FROM " . $table_prefix . "articles WHERE article_id IN (" . $db->tosql($articles_ids, INTEGERS_LIST) . ")");
		}
	}

	function articles_import_rss($is_remote_rss, $remote_rss_url, $remote_rss_date_updated, $remote_rss_refresh_rate, $remote_rss_ttl)
	{
		global $db, $table_prefix, $category_id;

		$current_ts = va_timestamp();

		if ($remote_rss_refresh_rate) {
			$refresh_rate = $remote_rss_refresh_rate;
		} else if ($remote_rss_ttl) {
			$refresh_rate = $remote_rss_ttl;
		} else {
			$refresh_rate = 10;
		}
		
		$refresh_ts = ($refresh_rate * 60);
		if (is_array($remote_rss_date_updated)) {
			$refresh_ts += va_timestamp($remote_rss_date_updated);
		}

		if ($refresh_ts > $current_ts) {
			return false;
		}
  
		$article_order = 1;
		$feeds = '';

		$ch = curl_init();
		if ($ch){
			curl_setopt($ch, CURLOPT_URL, $remote_rss_url);
			// if use proxy server
			//curl_setopt($ch, CURLOPT_PROXY, "proxy_server:port");
			//curl_setopt($ch, CURLOPT_PROXYUSERPWD, "login:password");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			set_curl_options($ch,1);

			$feeds = curl_exec($ch);
			curl_close($ch);
		} else {
			$feeds = false;
		}

		if ($feeds) {
	  
			if ($remote_rss_url && strlen($feeds)) {
				$feeds = trim($feeds);
				if (strlen ($feeds)) {
					$sql = "SELECT * FROM " . $table_prefix."articles_assigned WHERE category_id = " . $db->tosql($category_id, INTEGER, true, false);
					$db->query($sql);
					$articles_ids = "";
					if ($db->next_record()){
						$articles_ids = $db->f("article_id");
						do {
							$articles_ids .= "," . $db->f("article_id");
						} while ($db->next_record());
					}
					VA_Articles::delete($articles_ids);
					if (strpos($feeds,"<ttl>") && strpos($feeds,"<ttl>") < strpos($feeds,"<item>")){
						$ttl = substr($feeds, strpos($feeds, "<ttl>")+strlen("<ttl>"), strpos($feeds, "</ttl>")-strlen("<ttl>")-strpos($feeds, "<ttl>"));
						$sql = "UPDATE " . $table_prefix . "articles_categories SET remote_rss_date_updated=" . $db->tosql($current_ts, DATETIME) . ", remote_rss_ttl=" . $db->tosql($ttl,INTEGER) . " WHERE category_id=" . $db->tosql($category_id, INTEGER, true, false);
					} else {
						$sql = "UPDATE " . $table_prefix . "articles_categories SET remote_rss_date_updated=" . $db->tosql($current_ts, DATETIME) . ", remote_rss_ttl=NULL WHERE category_id=" . $db->tosql($category_id, INTEGER, true, false);
					}
					$db->query($sql);
	  
					$index = 0; $aryItems = array();

					if(preg_match_all("/<item[^>]*>(.+)<\/item>/Uis", $feeds, $matches)) {
						for ($m = 0; $m < sizeof($matches[0]); $m++) {
							$rss_item = $matches[1][$m];

							// initialize variables
							$title = ""; $link = ""; $description = ""; $fulltext = ""; $pubdate = ""; $image = ""; $image_alt = "";
							// get item data
							if (preg_match("/<title[^>]*>(.+)<\/title>/Uis", $rss_item, $match)) { $title = import_rss_clean($match[1]); }
							if (preg_match("/<link[^>]*>(.+)<\/link>/Uis", $rss_item, $match)) { $link = import_rss_clean($match[1]); }
							if (preg_match("/<description[^>]*>(.+)<\/description>/Uis", $rss_item, $match)) { $description = import_rss_clean($match[1]); }
							if (preg_match("/<fulltext[^>]*>(.+)<\/fulltext>/Uis", $rss_item, $match)) { $fulltext = import_rss_clean($match[1]); }
							if (preg_match("/<pubdate[^>]*>(.+)<\/pubdate>/Uis", $rss_item, $match)) { $pubdate = import_rss_clean($match[1]); }
							else if (preg_match("/<\w+\:date[^>]*>(.+)<\/\w+\:date>/Uis", $rss_item, $match)) { $pubdate = import_rss_clean($match[1]); }
							// check for image in description
							if (preg_match("/<img([^>])+>/", $description, $match)) {
							  $image_tag = $match[1];
								if (preg_match("/src\=\"([^\"]+)\"/", $image_tag, $match)) { $image = $match[1]; }
								if (preg_match("/alt\=\"([^\"]+)\"/", $image_tag, $match)) { $image_alt = $match[1]; }
							}
							$aryItems[$index] = array(
								"title" => $title, "link" => $link, "description" => $description,
								"fulltext" => $fulltext, "pubdate" => $pubdate, "image" => $image, "image_alt" => $image_alt,
							);
							$index++;
						}
					}
	  
					for ($i=0;$i<$index;$i++) {
						$db->query("SELECT MAX(article_id) FROM " . $table_prefix . "articles");
						$db->next_record();
						$article_id = $db->f(0) + 1;
	  
						$sql = "INSERT INTO " . $table_prefix . "articles (friendly_url, article_id, article_order, article_date, article_title, date_added, short_description, full_description, status_id, is_remote_rss, details_remote_url, image_small, image_small_alt) ";
						$sql .= "VALUES ('',";
						$sql .= $db->tosql($article_id, INTEGER) . ",";
						$sql .= $db->tosql($article_order, INTEGER) . ",";
						$sql .= $db->tosql(strtotime($aryItems[$i]['pubdate']), DATETIME) . ",";
						$sql .= $db->tosql($aryItems[$i]['title'], TEXT) . ",";
						$sql .= $db->tosql($current_ts, DATETIME) . ",";
						$sql .= $db->tosql($aryItems[$i]['description'], TEXT) . ",";
						$sql .= $db->tosql($aryItems[$i]['fulltext'], TEXT) . ",";
						$sql .= "1,1,";
						$sql .= $db->tosql($aryItems[$i]['link'], TEXT) . ",";
						$sql .= $db->tosql($aryItems[$i]['image'], TEXT, true, false) . ",";
						$sql .= $db->tosql($aryItems[$i]['image_alt'], TEXT, true, false) . ")";
						$db->query($sql);
	  
						$sql  = " INSERT INTO " . $table_prefix . "articles_assigned (article_id, category_id) VALUES (";
						$sql .= $db->tosql($article_id, INTEGER) . ",";
						$sql .= $db->tosql($category_id, INTEGER) . ")";
						$db->query($sql);
					}
				}
			} else {
				return false;
			}
	
			return $index;
		} else {
			return false;
		}
	}
	
	function import_rss_clean($string){
		$string = preg_replace("/\<\!\[CDATA\[/", "", $string);
		$string = preg_replace("/\]\]\>/", "", $string);
		$string = preg_replace("/\&lt;/", "<", $string);
		$string = preg_replace("/\&gt;/", ">", $string);
		return $string;
	}

?>