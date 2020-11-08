<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_site_map_xml_build.php                             ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit(900);

	$root_folder_path = "../";
	include_once($root_folder_path . "includes/var_definition.php");
	include_once($root_folder_path . "includes/constants.php");
	include_once($root_folder_path . "includes/common_functions.php");
	include_once($root_folder_path . "includes/articles_functions.php");
	include_once($root_folder_path . "includes/va_functions.php");
	include_once($root_folder_path . "includes/db_$db_lib.php");
	include_once("./admin_common.php");

	check_admin_security("site_settings");
	$va_version_code = va_version_code();
	$use_languages = false;// if we need to generate link for all available languages
	$param_site_id = get_session("session_site_id");

	// get global settings
	$site_settings = array();
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='global' ";
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($param_site_id, INTEGER) . ")";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while ($db->next_record()) {
		$site_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	// get site map settings
	$sm_settings = array();
	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
	$sql .= " WHERE setting_type='site_map' ";
	$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($param_site_id, INTEGER) . ")";
	$sql .= " ORDER BY site_id ASC ";
	$db->query($sql);
	while ($db->next_record()) {
		$sm_settings[$db->f("setting_name")] = $db->f("setting_value");
	}

	$friendly_urls = get_setting_value($site_settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($site_settings, "friendly_extension", "");
	$site_url = get_setting_value($site_settings, "site_url", "");

	$site_map_folder = get_setting_value($sm_settings, "site_map_folder", "../");
	$site_map_filename = get_setting_value($sm_settings, "site_map_filename", "sitemap");
	$site_map_filepath = $site_map_folder.$site_map_filename."_index.xml";

	$datetime_loc_format = array("YYYY", "-", "MM", "-", "DD", "T", "HH", ":", "mm", ":", "ss", "+00:00");

	$sm_errors = "";
	$message_build_xml = "";
	$site_map_fileindex = $site_map_folder.$site_map_filename.".xml";
	if (file_exists($site_map_fileindex)) {
		if (!is_writable($site_map_fileindex)) {
			$sm_errors .= str_replace("{filename}", $site_map_fileindex, SM_WRITE_FILE_ERROR) . "<br>";
		}
	} elseif (!is_writable($site_map_folder) ) {
		$sm_errors .= SM_WRITE_DIR_ERROR . "<br>";
	}

	$languages = array();
	if ($use_languages) {
		$sql = "SELECT language_code FROM " . $table_prefix . "languages WHERE show_for_user = '1' ";
		$db->query($sql);
		while ($db->next_record()) {
			$languages[] = $db->f('language_code');
		}
	}

	if (!strlen($sm_errors)) {
		$show_custom_pages = get_param("site_map_custom_pages");
		$show_products_categories = get_param("site_map_categories");
		$show_manufacturers = get_param("site_map_manufacturers");
		$show_products = get_param("site_map_items");
		$show_forum_categories = get_param("site_map_forum_categories");
		$show_forums = get_param("site_map_forums");
		$show_ad_categories = get_param("site_map_ad_categories");
		$show_ads = get_param("site_map_ads");
		$show_manual_categories = get_param("site_map_manual_categories");
		$show_manuals = get_param("site_map_manuals");
		$show_manual_articles = get_param("site_map_manual_articles");
		$remove_prod_dup = get_param("remove_prod_dup");
		$remove_art_dup = get_param("remove_art_dup");
		
		// Database Initialize
		$db2 = new VA_SQL();
		$db2->DBType      = $db_type;
		$db2->DBDatabase  = $db_name;
		$db2->DBHost      = $db_host;
		$db2->DBPort      = $db_port;
		$db2->DBUser      = $db_user;
		$db2->DBPassword  = $db_password;
		$db2->DBPersistent= $db_persistent;

		$dbp = new VA_SQL();
		$dbp->DBType      = $db_type;
		$dbp->DBDatabase  = $db_name;
		$dbp->DBHost      = $db_host;
		$dbp->DBPort      = $db_port;
		$dbp->DBUser      = $db_user;
		$dbp->DBPassword  = $db_password;
		$dbp->DBPersistent= $db_persistent;

		$count = 0;
		$total_count = 0;
		$sitemap_count = 1;
		$sitemap_started = false;

		$show_map = false;
		// Custom Pages
		if ($show_custom_pages) {
			$sql  = " SELECT p.page_code, p.page_url, p.friendly_url ";
			$sql .= " FROM ";
			if (isset($param_site_id)) {
				$sql .= "(";
			}
			$sql .= $table_prefix . "pages p ";
			if (isset($param_site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "pages_sites s ON (s.page_id=p.page_id AND p.sites_all=0)) ";
			}
			$sql .= " WHERE p.is_showing=1 AND p.is_site_map=1 ";
			$sql .= " AND p.user_types_all=1 ";
			if (isset($param_site_id)) {
				$sql .= " AND (p.sites_all=1 OR s.site_id=" . $db->tosql($param_site_id, INTEGER, true, false) . ") ";
			} else {
				$sql .= " AND p.sites_all=1";
			}
			$db2->query($sql);
			while ($db2->next_record()) {
				if ($db2->f('page_url')) {
					$item_url = $site_url . $db2->f('page_url');
				} elseif ($db2->f('friendly_url') && $friendly_urls){
					$item_url = $site_url . $db2->f('friendly_url') . $friendly_extension;
				} else {
					$item_url = $site_url . "page.php?page=" . $db2->f('page_code');
				}
				xml_add_url($item_url);
				$parsed_url = parse_url($item_url);
				if(isset($parsed_url['query'])){
					$query_symbol = '&';
				}else{
					$query_symbol = '?';
				}
				if ($use_languages) {
					foreach ($languages as $language_code){
						$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
						xml_add_url($language_item_url);
					}
				}
			}
		}
		// Custom Pages

		// Product
		if ($va_version_code & 1){
			if ($show_products_categories) {
				$item_url = $site_url . "products_list.php";
				xml_add_url($item_url);
			}
			
			if ($show_manufacturers) {
				$sql  = " SELECT manufacturer_id, manufacturer_name, friendly_url ";
				$sql .=	" FROM " . $table_prefix . "manufacturers  ";
				$sql .= " ORDER BY manufacturer_order, manufacturer_name ";
				$db->query($sql);
				while ($db->next_record()) {
					$manufacturer_id    = $db->f("manufacturer_id");
					$manufacturer_name  = $db->f("manufacturer_name");
					$friendly_url       = $db->f("friendly_url");
					if ($friendly_url && $friendly_urls) {
						$item_url = $site_url.$friendly_url . $friendly_extension;
					} else {
						$item_url = $site_url."products_list.php?manf=" . $manufacturer_id;
					}
					xml_add_url($item_url);
					$parsed_url = parse_url($item_url);
					if(isset($parsed_url['query'])){
						$query_symbol = '&';
					}else{
						$query_symbol = '?';
					}
					if ($use_languages) {
						foreach ($languages as $language_code){
							$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
							xml_add_url($language_item_url, $date_modified);
						}
					}
		  
				}
			}

			if ($show_products) {
				$show_category_id = 0;
				$sql  = " SELECT i.item_id, i.friendly_url, i.date_added, i.date_modified ";
				$sql .= " FROM (" ;
				if ($multisites_version && isset($param_site_id)) {
					if (isset($param_site_id)) {
						$sql .= "(";
					}
				}
				$sql .= $table_prefix . "items i LEFT JOIN " . $table_prefix . "items_categories ic ON ic.item_id=i.item_id) ";
				if ($multisites_version && isset($param_site_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "items_sites its ON i.sites_all = 0 AND i.item_id = its.item_id)";
				}
				$sql .= " WHERE ic.category_id=" . $dbp->tosql($show_category_id, INTEGER);
				$sql .= " AND i.is_approved=1 AND i.is_showing=1 ";
				$sql .= " AND " . format_binary_for_sql("i.guest_access_level", VIEW_ITEMS_PERM);
				if ($multisites_version) {
					if (isset($param_site_id)) {
						$sql .= " AND ( i.sites_all = 1 OR its.site_id=" . $db->tosql($param_site_id, INTEGER, true, false) . ") ";
					} else {
						$sql .= " AND i.sites_all = 1";
					}
				}
				$dbp->query($sql);
				while ($dbp->next_record()) {
					if ($dbp->f("friendly_url") && $friendly_urls) {
						$item_url = $site_url . $dbp->f('friendly_url') . $friendly_extension;
					} else {
						$item_url = $site_url . "product_details.php?item_id=" . $dbp->f('item_id');
					}
					$date_modified = '';
					if ($dbp->f("date_modified")) {
						$date_modified = $dbp->f("date_modified", DATETIME);
					} elseif ($dbp->f("date_added")) {
						$date_modified = $dbp->f("date_added", DATETIME);
					}
					if (is_array($date_modified)) {
						$date_modified = va_date($datetime_loc_format, $date_modified);
					} else {
						$date_modified = '';
					}
					xml_add_url($item_url, $date_modified);
					$parsed_url = parse_url($item_url);
					if(isset($parsed_url['query'])){
						$query_symbol = '&';
					}else{
						$query_symbol = '?';
					}
					if ($use_languages) {
						foreach ($languages as $language_code){
							$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
							xml_add_url($language_item_url, $date_modified);
						}
					}
				}
			}
			
			if ($show_products_categories || $show_products) {
				//array that contains all products ids as keys in case dubplicates should be removed
				$items_ids_list = array();
				$sql  = " SELECT c.category_id, c.friendly_url, c.date_added, c.date_modified ";
				$sql .= " FROM " ;
				if ($multisites_version && isset($param_site_id)) {
					if (isset($param_site_id)) {
						$sql .= "(";
					}
				}
				$sql .= $table_prefix . "categories c";
				if ($multisites_version && isset($param_site_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "categories_sites cs ON c.sites_all = 0 AND c.category_id = cs.category_id)";
				}
				$sql .= " WHERE c.is_showing=1";
				$sql .= " AND " . format_binary_for_sql("c.guest_access_level", VIEW_CATEGORIES_PERM);
				if ($multisites_version) {
					if (isset($param_site_id)) {
						$sql .= " AND ( c.sites_all = 1 OR cs.site_id=" . $db->tosql($param_site_id, INTEGER, true, false) . ") ";
					} else {
						$sql .= " AND c.sites_all = 1";
					}
				}
				$db2->query($sql);
				while ($db2->next_record()) {
					$show_category_id = $db2->f("category_id");
					if ($db2->f("friendly_url") && $friendly_urls) {
						$item_url = $site_url . $db2->f("friendly_url") . $friendly_extension;
					} else {
						$item_url = $site_url . "products_list.php?category_id=" . $show_category_id;
					}
					$date_modified = '';
					if ($db2->f("date_modified")) {
						$date_modified = $db2->f("date_modified", DATETIME);
					} elseif ($db2->f("date_added")) {
						$date_modified = $db2->f("date_added", DATETIME);
					}
					if (is_array($date_modified)) {
						$date_modified = va_date($datetime_loc_format, $date_modified);
					} else {
						$date_modified = '';
					}
					xml_add_url($item_url, $date_modified);
					$parsed_url = parse_url($item_url);
					if(isset($parsed_url['query'])){
						$query_symbol = '&';
					}else{
						$query_symbol = '?';
					}
					if ($use_languages) {
						foreach ($languages as $language_code){
							$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
							xml_add_url($language_item_url, $date_modified);
						}
					}

					$sql  = " SELECT i.item_id, i.friendly_url, i.date_added, i.date_modified ";
					$sql .= " FROM (" ;
					if ($multisites_version && isset($param_site_id)) {
						if (isset($param_site_id)) {
							$sql .= "(";
						}
					}
					$sql .= $table_prefix . "items i LEFT JOIN " . $table_prefix . "items_categories ic ON ic.item_id=i.item_id) ";
					if ($multisites_version && isset($param_site_id)) {
						$sql .= " LEFT JOIN " . $table_prefix . "items_sites its ON i.sites_all = 0 AND i.item_id = its.item_id)";
					}
					$sql .= " WHERE ic.category_id=" . $dbp->tosql($show_category_id, INTEGER);
					$sql .= " AND i.is_approved=1 AND i.is_showing=1 ";
					$sql .= " AND " . format_binary_for_sql("i.guest_access_level", VIEW_ITEMS_PERM);
					if ($multisites_version) {
						if (isset($param_site_id)) {
							$sql .= " AND ( i.sites_all = 1 OR its.site_id=" . $db->tosql($param_site_id, INTEGER, true, false) . ") ";
						} else {
							$sql .= " AND i.sites_all = 1";
						}
					}
					$dbp->query($sql);
					while ($dbp->next_record()) {
						$ms_item_id = $dbp->f('item_id');
						if(($remove_prod_dup && !isset($items_ids_list[$ms_item_id])) || !$remove_prod_dup){
							//in case only one instance should be added to site map add item_id as key (isset works much more faster than in_array check)
							$items_ids_list[$ms_item_id] = 1;
							if ($dbp->f("friendly_url") && $friendly_urls) {
								$item_url = $site_url . $dbp->f("friendly_url") . $friendly_extension;
							} else {
								$item_url = $site_url . "product_details.php?item_id=" . $ms_item_id;
							}
							$date_modified = '';
							if ($dbp->f("date_modified")) {
								$date_modified=$dbp->f("date_modified", DATETIME);
							} elseif ($dbp->f("date_added")) {
								$date_modified=$dbp->f("date_added", DATETIME);
							}
							if (is_array($date_modified)) {
								$date_modified = va_date($datetime_loc_format, $date_modified);
							} else {
								$date_modified = '';
							}
							xml_add_url($item_url, $date_modified);
							$parsed_url = parse_url($item_url);
							if(isset($parsed_url['query'])){
								$query_symbol = '&';
							}else{
								$query_symbol = '?';
							}
							if ($use_languages) {
								foreach ($languages as $language_code){
									$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
									xml_add_url($language_item_url, $date_modified);
								}
							}
						}
					}
				}
			}
		}
		// Products

		// Articles
		if ($va_version_code & 2){
			$categories_allowed = array();
			//array that contains all articles ids as keys in case dubplicates should be removed
			$articles_ids_list = array();
			$sql  = " SELECT c.category_id, c.category_name, c.parent_category_id ";
			$sql .= " FROM " ;
			if ($multisites_version && isset($param_site_id)) {
				if (isset($param_site_id)) {
					$sql .= "(";
				}
			}
			$sql .=  $table_prefix . "articles_categories c ";
			if ($multisites_version && isset($param_site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_sites cs ON c.sites_all = 0 AND c.category_id = cs.category_id)";
			}
			//$sql .= " WHERE c.parent_category_id=0";			
			$sql .= " WHERE " . format_binary_for_sql("c.guest_access_level", VIEW_CATEGORIES_PERM);
			if ($multisites_version) {
				if (isset($param_site_id)) {
					$sql .= " AND ( c.sites_all = 1 OR cs.site_id=" . $db->tosql($param_site_id, INTEGER, true, false) . ") ";
				} else {
					$sql .= " AND c.sites_all = 1";
				}
			}
			$sql .= " ORDER BY c.category_order, c.category_name ";
			$db2->query($sql);
			while ($db2->next_record()) {
				$categories_allowed[] = $db2->f("category_id");
			}
			foreach ($categories_allowed as $row => $category_id)
			{
				$sql  = " SELECT category_id, friendly_url ";
				$sql .= " FROM " . $table_prefix . "articles_categories ";
				$sql .= " WHERE " . format_binary_for_sql("guest_access_level", VIEW_ITEMS_PERM);
				//$sql .= " AND (category_path LIKE '%" . $category_id . ",%'";
				//$sql .= " OR category_id=" . $category_id . ")";
				$sql .= " AND category_id=" . $category_id;
				$db2->query($sql);
				while ($db2->next_record()) {
					$show_category_id = $db2->f("category_id");
					if (get_param("site_map_articles_categories_".$show_category_id)) {
						if ($db2->f("friendly_url") && $friendly_urls) {
							$item_url = $site_url . $db2->f("friendly_url") . $friendly_extension;
						} else {
							$item_url = $site_url . "articles.php?category_id=" . $show_category_id;
						}
						xml_add_url($item_url);
					
						$parsed_url = parse_url($item_url);
						if(isset($parsed_url['query'])){
							$query_symbol = '&';
						}else{
							$query_symbol = '?';
						}
						if ($use_languages) {
							foreach ($languages as $language_code){
								$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
								xml_add_url($language_item_url);
							}
						}
					}
					
					if (get_param("site_map_articles_".$show_category_id)) {

						$sql_data["select"] = "a.article_id, a.article_title, a.friendly_url, a.date_added, a.date_updated, aa.article_order ";
						$sql_data["where"] = " aa.category_id = " . $db->tosql($show_category_id, INTEGER);
						$sql_data["group"] = " a.article_id, a.article_title, a.friendly_url, a.date_added, a.date_updated, aa.article_order ";
						$sql_data["order"] = " aa.article_order, a.article_id ";
						$sql_data["access_field"] = true;
						$sql = VA_Articles::sql($sql_data, VIEW_CATEGORIES_ITEMS_PERM);
						$dbp->query($sql);
						/*
						$sql  = " SELECT a.article_id, a.friendly_url, a.date_added, a.date_updated ";
						$sql .= " FROM (" . $table_prefix . "articles a LEFT JOIN " . $table_prefix . "articles_assigned aa ON aa.article_id=a.article_id) ";
						$sql .= " WHERE aa.category_id=" . $dbp->tosql($show_category_id, INTEGER);
						$sql .= " GROUP BY a.article_id, a.friendly_url, a.date_added, a.date_updated ";//*/
						while ($dbp->next_record()) {
							$sm_article_id = $dbp->f("article_id");
							if(($remove_art_dup && !isset($articles_ids_list[$sm_article_id])) || !$remove_art_dup){
								$articles_ids_list[$sm_article_id] = 1;
								if ($dbp->f("friendly_url") && $friendly_urls) {
									$item_url = $site_url . $dbp->f("friendly_url") . $friendly_extension;
								} else {
									$item_url = $site_url . "article.php?article_id=" . $sm_article_id;
								}
								$date_modified = '';
								if ($dbp->f("date_updated")) {
									$date_modified=$dbp->f("date_updated", DATETIME);
								} elseif ($dbp->f("date_added")) {
									$date_modified = $dbp->f("date_added", DATETIME);
								}
								if (is_array($date_modified)) {
									$date_modified = va_date($datetime_loc_format, $date_modified);
								} else {
									$date_modified = '';
								}
								xml_add_url($item_url, $date_modified);
								$parsed_url = parse_url($item_url);
								if(isset($parsed_url['query'])){
									$query_symbol = '&';
								}else{
									$query_symbol = '?';
								}
								if ($use_languages) {
									foreach ($languages as $language_code){
										$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
										xml_add_url($language_item_url, $date_modified);
									}
								}
							}
						}
					}
				}
			}
		}
		// Articles

		// Forum
		if ($va_version_code & 12){
			if ($show_forum_categories || $show_forums) {
				$sql  = " SELECT c.category_id, c.friendly_url ";
				$sql .= " FROM " ;
				if ($multisites_version && isset($param_site_id)) {
					if (isset($param_site_id)) {
						$sql .= "(";
					}
				}
				$sql .= $table_prefix . "forum_categories c ";
				if ($multisites_version && isset($param_site_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "forum_categories_sites cs ON c.sites_all = 0 AND c.category_id = cs.category_id)";
				}
				$sql .= " WHERE c.allowed_view=1";
				if ($multisites_version) {
					if (isset($param_site_id)) {
						$sql .= " AND ( c.sites_all = 1 OR cs.site_id=" . $db->tosql($param_site_id, INTEGER, true, false) . ") ";
					} else {
						$sql .= " AND c.sites_all = 1";
					}
				}
				$db2->query($sql);
				while ($db2->next_record()) {
					if ($show_forum_categories) {
						$show_category_id = $db2->f("category_id");
						if ($db2->f("friendly_url") && $friendly_urls) {
							$item_url = $site_url . $db2->f("friendly_url") . $friendly_extension;
						} else {
							$item_url = $site_url."forums.php?category_id=".$show_category_id;
						}
						xml_add_url($item_url);
						$parsed_url = parse_url($item_url);
						if(isset($parsed_url['query'])){
							$query_symbol = '&';
						}else{
							$query_symbol = '?';
						}
						if ($use_languages) {
							foreach ($languages as $language_code){
								$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
								xml_add_url($language_item_url);
							}
						}
					}
					
					if ($show_forums) {
						$sql  = " SELECT f.forum_id, f.friendly_url, f.date_added, f.last_post_added ";
						$sql .= " FROM " . $table_prefix . "forum_list f ";
						$sql .= " WHERE f.category_id=" . $dbp->tosql($show_category_id, INTEGER);
						$sql .= " AND " . format_binary_for_sql("f.guest_access_level", VIEW_FORUM_PERM);
						$dbp->query($sql);
						while ($dbp->next_record()) {
							if ($dbp->f("friendly_url") && $friendly_urls) {
								$item_url = $site_url . $dbp->f("friendly_url") . $friendly_extension;
							} else {
								$item_url = $site_url . "forum.php?forum_id=" . $dbp->f('forum_id');
							}
							$date_modified='';
							if ($dbp->f("last_post_added")) {
								$date_modified = $dbp->f("last_post_added", DATETIME);
							} elseif ($dbp->f("date_added")) {
								$date_modified = $dbp->f("date_added", DATETIME);
							}
							if (is_array($date_modified)) {
								$date_modified = va_date($datetime_loc_format, $date_modified);
							} else {
								$date_modified = '';
							}
							xml_add_url($item_url, $date_modified);
							$parsed_url = parse_url($item_url);
							if(isset($parsed_url['query'])){
								$query_symbol = '&';
							}else{
								$query_symbol = '?';
							}
							if ($use_languages) {
								foreach ($languages as $language_code){
									$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
									xml_add_url($language_item_url, $date_modified);
								}
							}
						}
					}
				}
			}
		}
		// Forum

		// Ads
		if ($va_version_code & 16){
			if ($show_ad_categories || $show_ads) {
				$sql  = " SELECT c.category_id, c.friendly_url ";
				$sql .= " FROM " ;
				if ($multisites_version && isset($param_site_id)) {
					if (isset($param_site_id)) {
						$sql .= "(";
					}
				}
				$sql .= $table_prefix . "ads_categories c ";
				if ($multisites_version && isset($param_site_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "ads_categories_sites cs ON c.sites_all = 0 AND c.category_id = cs.category_id)";
				}
				$sql .= " WHERE " . format_binary_for_sql("c.guest_access_level", VIEW_CATEGORIES_PERM);
				if ($multisites_version) {
					if (isset($param_site_id)) {
						$sql .= " AND ( c.sites_all = 1 OR cs.site_id=" . $db->tosql($param_site_id, INTEGER, true, false) . ") ";
					} else {
						$sql .= " AND c.sites_all = 1";
					}
				}
				$db2->query($sql);
				while ($db2->next_record()) {
					$show_category_id = $db2->f("category_id");
					if ($show_ad_categories) {
						if ($db2->f("friendly_url") && $friendly_urls) {
							$item_url = $site_url . $db2->f("friendly_url") . $friendly_extension;
						} else {
							$item_url = $site_url . "ads.php?category_id=" . $show_category_id;
						}
						xml_add_url($item_url);
						$parsed_url = parse_url($item_url);
						if(isset($parsed_url['query'])){
							$query_symbol = '&';
						}else{
							$query_symbol = '?';
						}
						if ($use_languages) {
							foreach ($languages as $language_code){
								$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
								xml_add_url($language_item_url);
							}
						}
					}
					
					if ($show_ads) {
						$sql  = " SELECT ai.item_id, ai.friendly_url, ai.date_added, ai.date_updated ";
						$sql .= " FROM (" . $table_prefix . "ads_items ai LEFT JOIN " . $table_prefix . "ads_assigned aa ON aa.item_id=ai.item_id) ";
						$sql .= " WHERE aa.category_id=" . $dbp->tosql($show_category_id, INTEGER);
						$dbp->query($sql);
						while ($dbp->next_record()) {
							if ($dbp->f("friendly_url") && $friendly_urls){
								$item_url = $site_url.$dbp->f("friendly_url") . $friendly_extension;
							} else {
								$item_url = $site_url . "ads_details.php?category_id=" . $show_category_id . "&item_id=" . $dbp->f('item_id');
							}
							$date_modified = '';
							if ($dbp->f("date_updated")) {
								$date_modified=$dbp->f("date_updated", DATETIME);
							} elseif ($dbp->f("date_added")) {
								$date_modified=$dbp->f("date_added", DATETIME);
							}
							if (is_array($date_modified)) {
								$date_modified = va_date($datetime_loc_format, $date_modified);
							} else {
								$date_modified = '';
							}
							xml_add_url($item_url, $date_modified);
							$parsed_url = parse_url($item_url);
							if(isset($parsed_url['query'])){
								$query_symbol = '&';
							}else{
								$query_symbol = '?';
							}
							if ($use_languages) {
								foreach ($languages as $language_code){
									$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
									xml_add_url($language_item_url, $date_modified);
								}
							}
						}
					}
				}
			}
		}
		// Ads

		// Manual
		if ($va_version_code & 36){
			if ($show_manual_categories || $show_manuals || $show_manual_articles) {
				$dbm = new VA_SQL();
				$dbm->DBType     = $db_type;
				$dbm->DBDatabase = $db_name;
				$dbm->DBUser     = $db_user;
				$dbm->DBPassword = $db_password;
				$dbm->DBHost     = $db_host;
				$dbm->DBPort       = $db_port;
				$dbm->DBPersistent = $db_persistent;

				$sql  = " SELECT c.category_id, c.friendly_url, c.date_added, c.date_modified ";
				$sql .= " FROM " ;
				if ($multisites_version && isset($param_site_id)) {
					if (isset($param_site_id)) {
						$sql .= "(";
					}
				}
				$sql .=  $table_prefix . "manuals_categories c ";
				if ($multisites_version && isset($param_site_id)) {
					$sql .= " LEFT JOIN " . $table_prefix . "manuals_categories_sites cs ON c.sites_all = 0 AND c.category_id = cs.category_id)";
				}
				$sql .= " WHERE " . format_binary_for_sql("c.guest_access_level", VIEW_CATEGORIES_PERM);
				if ($multisites_version) {
					if (isset($param_site_id)) {
						$sql .= " AND ( c.sites_all = 1 OR cs.site_id=" . $db->tosql($param_site_id, INTEGER, true, false) . ") ";
					} else {
						$sql .= " AND c.sites_all = 1";
					}
				}
				$db2->query($sql);
				while ($db2->next_record()) {
					$show_category_id = $db2->f("category_id");
					if ($show_manual_categories) {
						if ($db2->f("friendly_url") && $friendly_urls) {
							$item_url = $site_url . $db2->f("friendly_url") . $friendly_extension;
						} else {
							$item_url = $site_url . "manuals.php?category_id=" . $show_category_id;
						}
						$date_modified = '';
						if ($db2->f("date_modified")) {
							$date_modified = $db2->f("date_modified", DATETIME);
						}elseif ($db2->f("date_added")) {
							$date_modified = $db2->f("date_added", DATETIME);
						}
						if (is_array($date_modified)) {
							$date_modified = va_date($datetime_loc_format, $date_modified);
						} else {
							$date_modified = '';
						}
						xml_add_url($item_url, $date_modified);
						$parsed_url = parse_url($item_url);
						if(isset($parsed_url['query'])){
							$query_symbol = '&';
						}else{
							$query_symbol = '?';
						}
						if ($use_languages) {
							foreach ($languages as $language_code){
								$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
								xml_add_url($language_item_url, $date_modified);
							}
						}
					}
					$sql  = " SELECT m.manual_id, m.friendly_url, m.date_added, m.date_modified ";
					$sql .= " FROM " . $table_prefix . "manuals_list m ";
					$sql .= " WHERE m.category_id=" . $dbp->tosql($show_category_id, INTEGER);
					$dbp->query($sql);
					while ($dbp->next_record()) {
						$manual_id = $dbp->f('manual_id');
						if ($show_manuals) {
							if ($dbp->f("friendly_url") && $friendly_urls) {
								$item_url = $site_url . $dbp->f("friendly_url") . $friendly_extension;
							} else {
								$item_url = $site_url . "manuals_articles.php?manual_id=" . $dbp->f('manual_id');
							}
							$date_modified = '';
							if ($dbp->f("date_modified")) {
								$date_modified = $dbp->f("date_modified", DATETIME);
							}elseif ($dbp->f("date_added")) {
								$date_modified = $dbp->f("date_added", DATETIME);
							}
							if (is_array($date_modified)) {
								$date_modified = va_date($datetime_loc_format, $date_modified);
							} else {
								$date_modified = '';
							}
							xml_add_url($item_url, $date_modified);
							$parsed_url = parse_url($item_url);
							if(isset($parsed_url['query'])){
								$query_symbol = '&';
							}else{
								$query_symbol = '?';
							}
							if ($use_languages) {
								foreach ($languages as $language_code){
									$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
									xml_add_url($language_item_url, $date_modified);
								}
							}
						}
						
						if ($show_manual_articles) {
							$sql  = " SELECT article_id, friendly_url, date_added, date_modified ";
							$sql .= " FROM " . $table_prefix . "manuals_articles ";
							$sql .= " WHERE manual_id=" . $dbp->tosql($manual_id, INTEGER);
							$dbm->query($sql);
							while ($dbm->next_record()) {
								if ($dbm->f("friendly_url") && $friendly_urls) {
									$item_url = $site_url . $dbm->f("friendly_url") . $friendly_extension;
								} else {
									$item_url = $site_url . "manuals_article_details.php?article_id=" . $dbm->f('article_id');
								}
								$date_modified = '';
								if ($dbp->f("date_modified")) {
									$date_modified = $dbm->f("date_modified", DATETIME);
								}elseif ($dbp->f("date_added")) {
									$date_modified = $dbm->f("date_added", DATETIME);
								}
								if (is_array($date_modified)) {
									$date_modified = va_date($datetime_loc_format, $date_modified);
								} else {
									$date_modified = '';
								}
								xml_add_url($item_url, $date_modified);
								$parsed_url = parse_url($item_url);
								if(isset($parsed_url['query'])){
									$query_symbol = '&';
								}else{
									$query_symbol = '?';
								}
								if ($use_languages) {
									foreach ($languages as $language_code){
										$language_item_url = $item_url.$query_symbol.'language_code='.$language_code;
										xml_add_url($language_item_url, $date_modified);
									}
								}
							}
						}
					}
				}
			}
		}
		// Manual
		if (file_exists($site_map_folder . $site_map_filename . $sitemap_count . ".xml") && $sitemap_started) {
			$xml = "</urlset>";
			$fp = fopen($site_map_folder . $site_map_filename . $sitemap_count . ".xml", "a");
			@fwrite($fp, $xml);
			@fclose($fp);
			$total_count = $total_count + $count;
		}

		$xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"" . chr(63) . ">\n";
		$xml .= "\t<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		for ($i = 1; $i <= $sitemap_count; $i++) {
			$xml .= "\t<sitemap>\n";
			$xml .= "\t\t<loc>".htmlspecialchars($site_url.$site_map_filename.$i.".xml", ENT_QUOTES, "UTF-8")."</loc>\n";
			$xml .= "\t\t<lastmod>".va_date($datetime_loc_format)."</lastmod>\n";
			$xml .= "\t</sitemap>\n";
		}

		$xml .= "\t</sitemapindex>";
		$fp = fopen($site_map_fileindex, "w");
		@fwrite($fp, $xml);
		@fclose($fp);

		$message_build_xml .= str_replace("{urls_number}", $total_count, SM_URLS_ADDED);
	} else {
		$message_build_xml = $sm_errors;
	}

	// Adds an URL block to XML file
	function xml_add_url($loc, $lastmod = "", $changefreq = "", $priority = "") {
		global $total_count, $count;
		global $sitemap_count;
		global $sitemap_started;
		global $site_map_folder, $site_map_filename;

		$count++;
		$xml = "";

		$site_map_fileurls = $site_map_folder . $site_map_filename . $sitemap_count . ".xml";
		if (!$sitemap_started) {
			if (file_exists($site_map_fileurls) && is_writable($site_map_fileurls)){
				$fp = fopen($site_map_fileurls, "w");
				@fwrite($fp, '');
				@fclose($fp);
			}
			$sitemap_started = true;
		  	$xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"" . chr(63) . ">\n";
		  	$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		}

		$xml .= "\t<url>\n";
		$xml .= "\t\t<loc>" . htmlspecialchars($loc, ENT_QUOTES, "UTF-8") . "</loc>\n";
		if (!strlen($lastmod)){
			$datetime_loc_format = array("YYYY", "-", "MM", "-", "DD", "T", "HH", ":", "mm", ":", "ss", "+00:00");
			$lastmod = va_date($datetime_loc_format);
		}
		if (strlen($lastmod)){
			$xml .="\t\t<lastmod>".$lastmod."</lastmod>\n";
		}
		if (strlen($changefreq)){
			$xml .="\t\t<changefreq>".$changefreq."</changefreq>\n";
		}
		if (strlen($priority)){
			$xml .="\t\t<priority>".$priority."</priority>\n";
		}
		$xml .= "\t</url>\n";

		if (file_exists($site_map_fileurls)) {
			clearstatcache();
			$size = filesize($site_map_fileurls);
			if (($size > 10000000) || ($count % 50000 == 0)) { // due to Google Sitemap protocol limitations
				$sitemap_started = false;
				$xml .= "</urlset>";
				$total_count = $total_count + $count;
				$count = 0;
				$sitemap_count++;
			}
		}

		$fp = fopen($site_map_fileurls, "a");
		@fwrite($fp, $xml);
		@fclose($fp);
	}

