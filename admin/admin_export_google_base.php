<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_export_google_base.php                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit (1800);
	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "includes/shopping_cart.php");
	include_once ($root_folder_path . "messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");
	
	

	check_admin_security("import_export");
	check_admin_security("products_export_google_base");
	$startTime = microtime(true);

	// check default country 
	$default_country_id = get_setting_value($settings, "country_id");
	$default_country_code = get_db_value("SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($default_country_id, INTEGER));
	$default_state_id = get_setting_value($settings, "state_id");

	// check tax settings
	$google_base_tax = get_setting_value($settings, "google_base_tax", true);

	$tax_rates = get_tax_rates(true);	
	$default_tax = current($tax_rates);
	$tax_country_id = isset($default_tax["country_id"]) ? $default_tax["country_id"] : "";
	$tax_country_code = get_db_value("SELECT country_code FROM " . $table_prefix . "countries WHERE country_id=" . $db->tosql($tax_country_id, INTEGER));
	// if tax country is not US disable tax showing as Google Merchant tax parameter only for US
	if (strtoupper($tax_country_code) != "US") {
		$google_base_tax = false;
	}

	$tax_prices_type = get_setting_value($settings, "tax_prices_type");
	
	$google_base_ftp_login = get_setting_value($settings, "google_base_ftp_login");
	$google_base_ftp_password = get_setting_value($settings, "google_base_ftp_password");
	
	$google_base_filename = get_setting_value($settings, "google_base_filename");
	$google_base_title = get_setting_value($settings, "google_base_title");
	$google_base_description = get_setting_value($settings, "google_base_description");
	$google_base_encoding = get_setting_value($settings, "google_base_encoding", "UTF-8");
	
	$google_base_save_path = get_setting_value($settings, "google_base_save_path", get_setting_value($settings, "tmp_dir", "../images/"));
	$google_base_export_type = get_setting_value($settings, "google_base_export_type", 0);
       	
	$google_base_country = get_setting_value($settings, "google_base_country", 0);
	$show_stats = get_setting_value($settings, "google_base_show_stats", 1);

	$weight_measure = get_setting_value($settings, "weight_measure", "");

	if (!$google_base_filename) {
		$google_base_filename = 'googlebase.xml';
	}
	$google_base_days_expiry       = get_setting_value($settings, "google_base_days_expiry", 30);
	$google_base_product_condition = get_setting_value($settings, "google_base_product_condition", "new");

	$site_url = get_setting_value($settings, "site_url");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	$product_link = $site_url . get_custom_friendly_url("product_details.php") . "?item_id=";
	
	$current_date = getdate();
	$expiration_date = mktime ($current_date["hours"], $current_date["minutes"], $current_date["seconds"], $current_date["mon"], $current_date["mday"] + $google_base_days_expiry, $current_date["year"]);
	$expiration_date_formatted = date("Y-m-d", $expiration_date);
	
	$dbd = new VA_SQL();
	$dbd->DBType       = $db->DBType;
	$dbd->DBDatabase   = $db->DBDatabase;
	$dbd->DBUser       = $db->DBUser;
	$dbd->DBPassword   = $db->DBPassword;
	$dbd->DBHost       = $db->DBHost;
	$dbd->DBPort       = $db->DBPort;
	$dbd->DBPersistent = $db->DBPersistent;	
	
    /*
     * not exported categories ids
     */
    $banned_ids = array();
    $sql  = "SELECT category_id FROM " . $table_prefix . "categories WHERE google_base_type_id = 0";
    $db->query($sql);
	while($db->next_record()) {
        $banned_ids[] = $db->f("category_id");       
    }

	// write in file or output to the browser
	$write_to_file = false;
	if($google_base_export_type == 1 && $google_base_ftp_login && $google_base_ftp_password) {
		$fp = fopen($google_base_save_path . $google_base_filename, "w+");
		if(!$fp) {
			echo MODULE_COULDNT_WRITE_TO_MSG . $google_base_save_path . $google_base_filename . CHECK_PERMISSIONS_MSG . "<br/>";
			fclose($fp);
			exit;
		}
		$write_to_file = true;
	}

	// get schema type through all items
	$schema_type = 'g';		
	$sql  = " SELECT a.attribute_name, a.attribute_type, a.value_type, f.feature_value FROM (" . $table_prefix . "features f ";
	$sql .= " INNER JOIN " . $table_prefix . "google_base_attributes a ON a.attribute_id=f.google_base_attribute_id)";
	$sql .= " WHERE a.attribute_type = 'c' AND f.feature_value IS NOT NULL AND a.attribute_name IS NOT NULL ";
	$db->query($sql);
	if($db->next_record()) {	
		$schema_type = 'c';
	}	

	// search items
	$s  = trim(get_param("s"));
	$sc = get_param("sc");
	$sl = get_param("sl");
	$sm = get_param("sm");
	$spt = get_param("spt");
	$ss = get_param("ss");
	$ap = get_param("ap");
	
	$search = (strlen($sc) || strlen($s) || strlen($sl) || strlen($sm) || strlen($spt) || strlen($ss) || strlen($ap)) ? true : false;

	$tree = new VA_Tree("category_id", "category_name", "parent_category_id", $table_prefix . "categories", "tree");
	
	$sql  = " SELECT i.item_id, i_gbt.type_name AS i_gb_type, it_gbt.type_name AS it_gb_type, c.google_base_type_id AS cat_gb_type ";
	
	$sql_tables  = " FROM (((((" . $table_prefix . "items i ";
	$sql_tables .= " LEFT JOIN " . $table_prefix . "items_categories ic ON ic.item_id=i.item_id) ";
	$sql_tables .= " LEFT JOIN " . $table_prefix . "categories c ON ic.category_id=c.category_id) ";
	$sql_tables .= " LEFT JOIN " . $table_prefix . "item_types it ON i.item_type_id=it.item_type_id) ";

	$sql_tables .= " LEFT JOIN " . $table_prefix . "google_base_types i_gbt ON i.google_base_type_id=i_gbt.type_id) ";
	$sql_tables .= " LEFT JOIN " . $table_prefix . "google_base_types it_gbt ON it.google_base_type_id=it_gbt.type_id) ";

	$where = " WHERE i.google_base_type_id!=0 AND it.google_base_type_id>=0 AND c.google_base_type_id != 0 ";
	if (!$search) {
		$where .= " AND i.is_showing=1 ";
		$where .= " AND ((i.hide_out_of_stock=1 AND i.stock_level > 0) OR i.hide_out_of_stock=0)";
	}
	if($search && $sc != 0) {
		$where .= " AND c.category_id = ic.category_id ";
		$where .= " AND (ic.category_id = " . $db->tosql($sc, INTEGER);
		$where .= " OR c.category_path LIKE '" . $db->tosql($tree->get_path($sc), TEXT, false) . "%')";
	} else if(strlen($sc) && !$search) {
		$where .= " AND ic.category_id = " . $db->tosql($sc, INTEGER);
	}
	if($s) {
		$sa = explode(" ", $s);
		for($si = 0; $si < sizeof($sa); $si++) {
			$where .= " AND (i.item_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR i.item_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR i.manufacturer_code LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}
	if (strlen($sm)) {
		$where[] = " i.manufacturer_id= " . $dbe->tosql($sm, INTEGER);
	}
	if (strlen($spt)) {
		$where[] = " i.item_type_id= " . $dbe->tosql($spt, INTEGER);
	}
	if(strlen($sl)) {
		if ($sl == 1) {
			$where .= " AND (i.stock_level>0 OR i.stock_level IS NULL) ";
		} else {
			$where .= " AND i.stock_level<1 ";
		}
	}
	if(strlen($ss)) {
		if ($ss == 1) {
			$where .= " AND i.is_showing=1 ";
		} else {
			$where .= " AND i.is_showing=0 ";
		}
	}
	if(strlen($ap)) {
		if ($ap == 1) {
			$where .= " AND i.is_approved=1 ";
		} else {
			$where .= " AND i.is_approved=0 ";
		}
	}
	$order_by = " ORDER BY i.item_id ";
	$group_by = " GROUP BY i.item_id, i_gbt.type_name, it_gbt.type_name ";

	// calculate records number
	$count_sql = " SELECT COUNT(*) FROM (SELECT i.item_id " . $sql_tables . $where . " GROUP BY i.item_id) ci ";
	$total_records = get_db_value($count_sql);
	$records_per_page = 1000;
	$total_pages = ceil($total_records / $records_per_page);
	/*
     * @var errors variable
     */
    $error = "";
    /*
     * @var buffered content variable
     */
    $xml_content = "";
        
	if (!$total_records && $show_stats == 0) {
		echo NO_PRODUCTS_EXPORT_MSG;
		exit;
	}
	else if (!$total_records && $show_stats == 1){
		$error = NO_PRODUCTS_EXPORT_MSG . '<br />';
	}

	if (!$error) {
		
		//forming google types array
		$google_types_arr = array();
		$tsql = 'SELECT * FROM ' . $table_prefix . 'google_base_types';
		$tmp_arr = get_db_values($tsql, false);
		for ($i=0; $i < count($tmp_arr); $i++) {
			$google_types_arr[$tmp_arr[$i][0]] = $tmp_arr[$i][1];
		}
             
		//tmp data
		$xml_string ="";

		//start buffering;
		ob_start();
		
		// force download header data
		if (!$write_to_file && $show_stats == 0) { 
			header("Pragma: private");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", false);
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=" . $google_base_filename);
			header("Content-Transfer-Encoding: binary");	
		}
		
		// output XML header
		write_to(
			"<?xml version='1.0' encoding='" . $google_base_encoding . "'?>" . $eol
			 . "<rss version='2.0' xmlns:" . $schema_type . "='http://base.google.com/ns/1.0'>" . $eol
			 . "<channel>" . $eol, true
		);
		if(strlen($google_base_title)) {
			write_to("\t<title>" . htmlspecialchars(charset_conv($google_base_title)) . "</title>" . $eol);
		}
		if(strlen($google_base_description)) {
			write_to("\t<description>" . htmlspecialchars(charset_conv($google_base_description)) . "</description> " . $eol);		
		}
		write_to("\t<link>" . get_setting_value($settings, "site_url") . "</link>" . $eol);			


		$data_sql = $sql.$sql_tables.$where.$group_by.$order_by;
		$forced_items = array();
		for ($page_number = 1; $page_number <= $total_pages; $page_number++) {

			$items_ids = array();
			$items_types = array();
			$items_categories = array();
			// get all products ids and check their google types 
			$db->RecordsPerPage = $records_per_page;
			$db->PageNumber = $page_number;
			$db->query($data_sql);
			while ($db->next_record()) {
				$item_id = $db->f("item_id");
				$item_type = $db->f("i_gb_type");
                
				if (!strlen($item_type)) {
					$item_type = $db->f("it_gb_type");     
				}
				$cat_gb_type = $db->f("cat_gb_type");
				if ($cat_gb_type != -1){
					$forced_items[$item_id] = $cat_gb_type;
				}
				// populate data
				$items_ids[] = $item_id;
				if (strlen($item_type)) {
					$items_types[$item_id] = $item_type;
				} else {
					$items_categories[] = $item_id;
				}
			}

			// check categories google types
			if (sizeof($items_categories)) {
				$sql  = " SELECT ic.item_id, gbt.type_name ";
				$sql .= " FROM ((" . $table_prefix . "items_categories ic ";
				$sql .= " LEFT JOIN " . $table_prefix . "categories c ON ic.category_id=c.category_id) ";
				$sql .= " LEFT JOIN " . $table_prefix . "google_base_types gbt ON c.google_base_type_id=gbt.type_id) ";
				$sql .= " WHERE ic.item_id IN ( " . $db->tosql($items_categories, INTEGERS_LIST) . ")";

				$db->query($sql);
				while ($db->next_record()) {
					$item_id = $db->f("item_id");
					$item_type = $db->f("type_name");

					if (strlen($item_type)) {
						$items_types[$item_id] = $item_type;
					} 
				}
			}

			// output products data
			$sql  = " SELECT i.item_id, i.item_type_id, i.item_code, i.item_name, i.full_description, i.big_image, i.meta_keywords, i.friendly_url, i.google_base_type_id, ";
			$sql .= " i.manufacturer_code, i.weight, i.issue_date, m.manufacturer_name, ";
			$sql .= " i.price, i.sales_price, i.is_sales, i.properties_price, i.tax_id, i.tax_free, i.use_stock_level, i.stock_level, i.hide_out_of_stock, i.disable_out_of_stock ";
			$sql .= " FROM (" . $table_prefix . "items i ";
			$sql .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id) ";
			$sql .= " WHERE i.item_id IN ( " . $db->tosql($items_ids, INTEGERS_LIST) . ")";
			$db->query($sql);
			$warning = array();
			
			while ($db->next_record()) {
				$item_id      = $db->f("item_id");
				$item_type_id = $db->f("item_type_id");
				$item_name    = get_translation($db->f("item_name"));		
				$friendly_url = $db->f("friendly_url");
				$manufacturer_name = $db->f("manufacturer_name");
				$manufacturer_code = $db->f("manufacturer_code");
				$image_url = $db->f("big_image");
				$product_google_item_type = $db->f("google_base_type_id");
				
				
				if (!strlen($image_url)) {
					$warning[$item_id]['img'] = GMC_IMG_WARN . $eol;
				}

				$item_google_type = "";
				if (isset($items_types[$item_id])) {
						$item_google_type = $items_types[$item_id];
				}
				/*
				 *in case category setting is applied
				 */
				if(array_key_exists($item_id, $forced_items)){		
					$item_google_type = $google_types_arr[$forced_items[$item_id]];
				}
				
				if ($friendly_urls && strlen($friendly_url)) {
					$product_details_url = $site_url . $friendly_url . $friendly_extension;
				} else {
					$product_details_url = $product_link . $item_id;
				}
				
				write_to("\t<item>" . $eol);		
				write_to("\t\t<g:id>" . $item_id . $google_base_country . "</g:id>" . $eol);
				write_to("\t\t<title><![CDATA[" . charset_conv($item_name) . "]]></title>" . $eol);
				write_to("\t\t<link>". $product_details_url ."</link>" . $eol);
						
				$item_code    = $db->f("item_code");
				if ($item_code) {			
					if (preg_match('/.*books.*/i', $item_google_type)) {
						write_to("\t\t<" . $schema_type . ":isbn>" . $item_code . "</" . $schema_type . ":isbn>" . $eol);
					}
					else if (preg_match('/.*media.*/i', $item_google_type) || preg_match('/.*software.*/i', $item_google_type)) {
						write_to("\t\t<" . $schema_type . ":upc>" . $item_code . "</" . $schema_type . ":upc>" . $eol);
					}
					else {
						write_to("\t\t<" . $schema_type . ":gtin>" . $item_code . "</" . $schema_type . ":gtin>" . $eol);
					}
				}
						
				write_to("\t\t<" . $schema_type . ":product_type>" . htmlspecialchars($item_google_type) . "</" . $schema_type . ":product_type>" . $eol);
				write_to("\t\t<" . $schema_type . ":google_product_category>" . htmlspecialchars($item_google_type) . "</" . $schema_type . ":google_product_category>" . $eol);
				write_to("\t\t<" . $schema_type . ":expiration_date>" . $expiration_date_formatted . "</" . $schema_type . ":expiration_date>" . $eol);
				write_to("\t\t<" . $schema_type . ":condition>" . $google_base_product_condition . "</" . $schema_type . ":condition>" . $eol);
				
				$description = trim(strip_tags(get_translation($db->f("full_description"))));			
		  
				if (!strlen($description)) {
					$description = trim(strip_tags(get_translation($db->f("short_description"))));			
				}
				if (!strlen($description)) {
					$description = trim(strip_tags(get_translation($db->f("features"))));			
				}
				if (!strlen($description)) {
					$description = $item_name;			
				}
		  
				write_to( "\t\t<description><![CDATA[" . charset_conv($description) . "]]></description>" . $eol);

				if (preg_match('/.*apparel.*/i', $item_google_type) && !strlen($manufacturer_name) && $google_base_country != 0) {
					$warning[$item_id]['brand'] = GMC_APPAREL_BRAND_WARN . $eol;
				}
				if (strlen($manufacturer_name)) {
					write_to("\t\t<" . $schema_type . ":brand><![CDATA[" . charset_conv($manufacturer_name) . "]]></" . $schema_type . ":brand>" . $eol);
				}
				if (preg_match('/.*apparel.*/i', $item_google_type) == 0 && (
						(!strlen($manufacturer_code) && !strlen($item_code)) ||
						(!strlen($manufacturer_code) && !strlen($manufacturer_name)) ||
						(!strlen($item_code) && !strlen($manufacturer_name))
					) && $google_base_country != 0 && $google_base_country != 3 && preg_match('/.*books.*/i', $item_google_type) == 0
				) {
					$warning[$item_id]['codes'] = GMC_CODE_WARN . $eol;
				}
				else if (preg_match('/.*books.*/i', $item_google_type) && !strlen($item_code) && $google_base_country != 0) {
					$warning[$item_id]['books'] = GMC_ISBN_WARN . $eol;
				}
				
				if (strlen($manufacturer_code)) {
					write_to( "\t\t<" . $schema_type . ":mpn><![CDATA[" . charset_conv($manufacturer_code) . "]]></" . $schema_type . ":mpn>" . $eol);
				}
				if ($image_url && !preg_match("/^http\:\/\//", $image_url)) {
					$image_url = $settings["site_url"] . $image_url;
				}
				if (strlen($image_url)) {
					write_to( "\t\t<" . $schema_type . ":image_link>" . $image_url . "</" . $schema_type . ":image_link>" . $eol);
				}
			
				$price            = $db->f("price");
				$sales_price      = $db->f("sales_price");
				$is_sales         = $db->f("is_sales");
				$properties_price = $db->f("properties_price");
				$tax_id           = $db->f("tax_id");		
				$tax_free         = $db->f("tax_free");		
				if ($is_sales && $sales_price > 0) {
					$price = $sales_price;
				}
				$price += $properties_price;
				if ($google_base_tax) {			
					$price_tax = get_tax_amount($tax_rates, $item_type_id, $price, 1, $tax_id, $tax_free, $tax_percent);			
					if ($tax_prices_type == 1) {
						$price_incl = $price;
						$price_excl = $price - $price_tax;
					} else {
						$price_incl = $price + $price_tax;
						$price_excl = $price;
					}
					$price = $price_incl;
					write_to( "\t\t<".$schema_type.":tax>" . $eol);	
					write_to( "\t\t\t<" . $schema_type . ":country>" . $tax_country_code . "</" . $schema_type . ":country>" . $eol);	
					write_to( "\t\t\t<" . $schema_type . ":rate>" . $tax_percent. "</" . $schema_type . ":rate>" . $eol);	
					write_to( "\t\t</".$schema_type.":tax>" . $eol);	

				}
				write_to( "\t\t<" . $schema_type . ":price>" . $price . "</" . $schema_type . ":price>" . $eol);			
				
		  
				$use_stock_level = $db->f("use_stock_level");
				$hide_out_of_stock = $db->f("hide_out_of_stock");
				$disable_out_of_stock = $db->f("disable_out_of_stock");
				$stock_level = $db->f("stock_level");
				if (!$use_stock_level || $stock_level > 0 || $disable_out_of_stock == 0) {
					write_to( "\t\t<" . $schema_type . ":pickup>true</" . $schema_type . ":pickup>" . $eol);
				} else {
					write_to( "\t\t<" . $schema_type . ":pickup>false</" . $schema_type . ":pickup>" . $eol);
				}
				
				if ($use_stock_level && $stock_level > 0) {
					write_to( "\t\t<" . $schema_type . ":quantity>" . intval($stock_level) . "</" . $schema_type . ":quantity>" . $eol);	
				}

				if ($stock_level > 0) {
					$availability = 'in stock';
				}
				else if ($disable_out_of_stock == 0 && $hide_out_of_stock == 0 && $use_stock_level == 1) {
					$availability = 'available for order';
				}
				else if ($disable_out_of_stock == 0 && $hide_out_of_stock == 0 && $use_stock_level == 0) {
					$availability = 'preorder';
				}
				else {
					$availability = 'out of stock';
				}
				write_to( "\t\t<" . $schema_type . ":availability>" . $availability . "</" . $schema_type . ":availability>" . $eol);
				
				$weight = $db->f("weight");
				if ($weight) {
					if ($weight_measure) {
						write_to( "\t\t<" . $schema_type . ":weight>" . $weight . " " . $weight_measure . "</" . $schema_type . ":weight>" . $eol);		
					} else {
						write_to( "\t\t<" . $schema_type . ":weight>" . $weight . "</" . $schema_type . ":weight>" . $eol);
					}
				}
				
				$issue_date = $db->f("issue_date");
				if ($issue_date) {
					$tmp =  explode('-', $issue_date);
					if (strlen($tmp[0]))
						write_to( "\t\t<" . $schema_type . ":year>" . $tmp[0] . "</" . $schema_type . ":year>" . $eol);			
				}
				
				$order = '"'; $replace = "''";
				$sql  = " SELECT a.attribute_name, a.attribute_type, a.value_type, f.feature_value FROM (" . $table_prefix . "features f ";
				$sql .= " INNER JOIN " . $table_prefix . "google_base_attributes a ON a.attribute_id=f.google_base_attribute_id)";
				$sql .= " WHERE f.item_id=" . $db->tosql($item_id, INTEGER);
				$dbd->query($sql);
				while ($dbd->next_record()) {	
					$attribute_name = $dbd->f("attribute_name");
					$attribute_type = $dbd->f("attribute_type");
					$value_type     = $dbd->f("value_type");
					$feature_value  = $dbd->f("feature_value");
					$feature_value  = htmlentities($feature_value, ENT_QUOTES, $google_base_encoding);
					$feature_value  = rtrim(trim(str_replace($order, $replace, $feature_value)));
					if ($attribute_name && $attribute_type && $feature_value) {
						if ($attribute_type == 'g') {
							write_to( "\t\t<" . $schema_type . ":" . $attribute_name . "><![CDATA[" . charset_conv($feature_value) . "]]></" . $schema_type . ":" . $attribute_name . ">" . $eol);
						} else {
							write_to( "\t\t<" . $schema_type . ":" . $attribute_name . " type='" . $value_type . "'><![CDATA[" . charset_conv($feature_value) . "]]></" . $schema_type . ":" . $attribute_name . ">" . $eol);		
						}
					}
				}	
				
				write_to("\t</item>" . $eol);
				if(@!$warning[$item_id]) {
					write_to(false, true);
				}
				else {
					$xml_string = "";
				}
			} // end products output
		}
		// output XML footer
		write_to("</channel>" . $eol . "</rss>",true);
		
		$xml_content = ob_get_contents();
		ob_end_clean();
	}
	if (!$write_to_file && $show_stats == 0) {
		echo $xml_content;
		exit;
	}
	
	if (strlen($xml_content) && !@file_put_contents($google_base_save_path . $google_base_filename, $xml_content)) {
		$error .= 'problem with access to folder<br />feed was not created<br />please check folder permissions';
	}

	if ($write_to_file) {
		fclose($fp);
		$conn_id = ftp_connect("uploads.google.com", 21, 5);
		if (!$conn_id) {
			echo COULDNOT_CONNECT_GOOGLE_MSG ."<br/>";
			echo DATA_FEED_SUBMIT_MSG_MSG . "<br/>";
			echo "<a href='" . $google_base_save_path . $google_base_filename . "'>" . DOWNLOAD_BUTTON  . $google_base_save_path . $google_base_filename . "</a>";
			exit;
		}
		
		$login_result = ftp_login($conn_id, $google_base_ftp_login, $google_base_ftp_password);
		if (!$login_result) {
			echo COULDNOT_CONNECT_GOOGLE_MSG ."<br/>";
			echo DATA_FEED_SUBMIT_MSG_MSG . "<br/>";
			echo "<a href='" . $google_base_save_path . $google_base_filename . "'>" . DOWNLOAD_BUTTON  . $google_base_save_path . $google_base_filename . "</a>";
			ftp_close($conn_id);
			exit;
		}
		ftp_pasv($conn_id, true);	
		
		$upload = ftp_put($conn_id, $google_base_filename, $google_base_save_path . $google_base_filename, FTP_BINARY);
		if (!$upload) {
			echo FTP_UPLOAD_FAILED_MSG ."<br/>";
			echo DATA_FEED_SUBMIT_MSG_MSG . "<br/>";
			echo "<a href='" . $google_base_save_path . $google_base_filename . "'>".DOWNLOAD_BUTTON . $google_base_save_path . $google_base_filename . "</a>";
		} else {
			echo FTP_UPLOAD_SUCCEED_MSG;
		}	
		ftp_close($conn_id);	
	}
	
	$endTime = microtime(true);
	$workTime = $endTime - $startTime;


	if ($show_stats == 1) {
		$t = new VA_Template($settings["admin_templates_dir"]);
		$t->set_file("main","admin_export_google_base.html");
		include_once("./admin_header.php");
		include_once("./admin_footer.php");
		$countries = array (GMC_OTHER_COUNTRIES_MSG, GMC_US_MSG, GMC_UK_DE_FR_MSG, GMC_JP_MSG, GMC_ALL_COUNTRIES_MSG);
		if($error) {
			$t->set_var('error_msg',$error);
			$t->parse('block_error', false);
			$t->set_var('block_stats','');
			$t->set_var('feed_button', '');
		}
		else {
			$fileUrl = $site_url . $google_base_save_path . $google_base_filename;
			$t->set_var('block_error','');
			$t->set_var('gb_file_name', $fileUrl);
			$t->set_var('country', $countries[$google_base_country]);
			$t->set_var('work_time', round($workTime,3));
			$t->set_var('path', $fileUrl);
			if ($warning) {
				$t->set_var('mess_style','color:red;float:left;margin-bottom:4px;');
				$t->set_var('mess_text', ERRORS_MSG);
				foreach ($warning as $it_id => $warning_item) {	
					$t->set_var('warning_mess', '');
					$t->set_var('warning_mess_block', '');
					$t->set_var('id', '');
					$t->set_var('id', $it_id);
					foreach ($warning_item as $mess) {
						$t->set_var('warning_mess', $mess);
						$t->parse('warning_mess_block', true);
					}
				$t->parse('warning_item_block', true);
				}
				$t->parse('warnings_block', false);
			}
			else {
				$t->set_var('mess_style','color:#2f97ec;float:left;margin-bottom:4px;');
				$t->set_var('mess_text', GMC_FEED_SUCCESS_MSG);
				$t->set_var('warning_mess_block', '');
				$t->set_var('warnings_block', '');
			}
			$t->parse('feed_button', false);
			$t->parse('block_stats', false);
		}
		$t->pparse("main");
	}
	
	function charset_conv($string)
	{
		global $google_base_encoding;

		if (strtolower(CHARSET) != strtolower($google_base_encoding)) {
			if (function_exists("iconv")) {
				$string = iconv(CHARSET, $google_base_encoding, $string);			
			} else if(function_exists("mb_convert_encoding")) {
				$string = mb_convert_encoding($string, $google_base_encoding, CHARSET);
			}
		}
		return $string;
	}
	
	function write_to($xml, $output = false) {
		global $write_to_file, $fp, $xml_string;
		if ($write_to_file) {
			fwrite($fp, $xml);
		} 
		else {
			if ($output === true) {
				echo $xml_string .= $xml;
				$xml_string = null;
			}
			else {
				$xml_string .= $xml;
			}
		}
	}
?>