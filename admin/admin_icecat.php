<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_icecat.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/



/*
new fields:
ALTER TABLE va_items ADD COLUMN icecat_error VARCHAR(255);
ALTER TABLE va_items ADD COLUMN icecat_updated DATETIME;
ALTER TABLE va_items ADD COLUMN icecat_status_id INT(11) NOT NULL default '1';
CREATE INDEX va_items_icecat_status_id ON va_items (icecat_status_id);

data file:
http://data.icecat.biz/prodid/prodid_d.txt 
http://data.icecat.biz/prodid/prodid_d.txt.gz
*/
	
	@set_time_limit(600);
	chdir (dirname(__FILE__));
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/install_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once("./admin_common.php");

	// Database Initialize
	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	//check_admin_security("products_categories");

	$icecat_statuses_aa = array(
		0 => "ICECat N/A",
		1 => "Ready for Import",
		2 => "Import Error",
		3 => "Data Imported",
	);
	$icecat_statuses = array(
		array(0, "ICECat N/A"),
		array(1, "Ready for Import"),
		array(2, "Import Error"),
		array(3, "Data Imported"),
	);

	$language_code = "en"; // always get english descriptions
	$prod_txt_path = "../downloads/prodid_d.txt";
	$prod_txt_url = "http://data.icecat.biz/prodid/prodid_d.txt";
	$prod_gz_url = "http://data.icecat.biz/prodid/prodid_d.txt.gz";
	// auth header
	$credentials = "pglavey:pQnKOP";
	$http_headers = array(
		"Authorization: Basic " . base64_encode($credentials),
	);

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_icecat.html");
	$t->set_var("admin_icecat_href", "admin_icecat.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$eol = get_eol();
	$operation = get_param("operation");
	$status_id = get_param("status_id");
	$new_status_id = get_param("new_status_id");

	// additional connection 
	$dbs = new VA_SQL();
	$dbs->DBType      = $db_type;
	$dbs->DBDatabase  = $db_name;
	$dbs->DBUser      = $db_user;
	$dbs->DBPassword  = $db_password;
	$dbs->DBHost      = $db_host;
	$dbs->DBPort      = $db_port;
	$dbs->DBPersistent= $db_persistent;


	if ($operation == "import") {
		// output information step by step during upgrade process
		$t->parse("import_result", false);
		$t->pparse("main");
		flush();
		// start upgrading process
		echo "<script language=\"JavaScript\" type=\"text/javascript\">".$eol."<!--".$eol."importProcess();".$eol."//-->".$eol."</script>".$eol;
		flush();

		// features class
		$it = new VA_Record($table_prefix . "items");
		$it->add_where("item_id", INTEGER);
		$it->add_textbox("small_image", TEXT);
		$it->add_textbox("big_image", TEXT);
		$it->add_textbox("super_image", TEXT);
		$it->add_textbox("short_description", TEXT);
  	$it->add_textbox("full_description", TEXT);
		// ICECat fields
  	$it->add_textbox("icecat_status_id", INTEGER);
  	$it->add_textbox("icecat_error", TEXT);
  	$it->add_textbox("icecat_updated", DATETIME);

	    
		// features group class
		$fg = new VA_Record($table_prefix . "features_groups");
		if ($db->DBType == "postgre") {
			$fg->add_textbox("group_id", INTEGER);
		} else {
			$fg->add_where("group_id", INTEGER);
		} 
		$fg->add_textbox("group_order", INTEGER);
		$fg->add_textbox("group_name", TEXT);

		// features class
		$f = new VA_Record($table_prefix . "features");
		$f->add_where("feature_id", INTEGER);
		$f->add_textbox("item_id", INTEGER);
		$f->add_textbox("group_id", INTEGER);
		$f->add_textbox("feature_name", TEXT);
		$f->add_textbox("feature_value", TEXT);
		
		// url template
		//$url = "http://data.icecat.biz/xml_s3/xml_server3.cgi?prod_id=PA03368-B001;vendor=FUJITSU;shopname=openICEcat-xml;lang=int;output=productxml";
		//$url = "http://data.icecat.biz/xml_s3/xml_server3.cgi?ean_upc=1221196;lang=en;output=productxml";
		// urls to get product details
		$ean_url = "http://data.icecat.biz/xml_s3/xml_server3.cgi?ean_upc={ean_code};lang=en;output=productxml";
		$url = "http://data.icecat.biz/xml_s3/xml_server3.cgi?prod_id={manufacturer_code};vendor={manufacturer_name};shopname=openICEcat-xml;lang={language_code};output=productxml";
		
		$items_per_cycle = 25;
		$import_failed = 0;
		$import_success = 0;
  
		$sql  = " SELECT i.item_id, i.manufacturer_code, m.manufacturer_name ";
		$sql .= " FROM (" . $table_prefix . "items i ";
		$sql .= " LEFT JOIN " . $table_prefix . "manufacturers m ON i.manufacturer_id=m.manufacturer_id) ";
		$sql .= " WHERE i.icecat_status_id=1 ";
		$items_sql = $sql;
		$dbs->RecordsPerPage = $items_per_cycle;
		$dbs->PageNumber = 1;              
		$dbs->query($items_sql);
		if ($dbs->next_record()) {
			$items_number = 0;
			do {
				@set_time_limit(600);
				$items_number++;
				$item_id = $dbs->f("item_id");
				$manufacturer_code = $dbs->f("manufacturer_code");
				$manufacturer_name = $dbs->f("manufacturer_name");
				$search = array("{manufacturer_code}", "{manufacturer_name}", "{language_code}");
				$replace = array($manufacturer_code, $manufacturer_name, $language_code);
				$request_url = str_replace($search, $replace, $url);
				// check for data on ICECat
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $request_url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
				curl_setopt ($ch, CURLOPT_HTTPHEADER, $http_headers);
				$icecat_response = curl_exec ($ch);
				curl_close ($ch);
	
				// try to parse ICECat response  
				parse_icecat_response($icecat_response, $product, $features, $error, $error_code);

				if ($error_code == -3) {
					// can't parse ICECat response try to find product by it ean code
					$ean_code = get_ean_code($manufacturer_code, $manufacturer_name);
					if ($ean_code) {
						$search = array("{ean_code}", "{manufacturer_name}", "{language_code}");
						$replace = array($ean_code, $manufacturer_name, $language_code);
						$request_url = str_replace($search, $replace, $ean_url);
  
						// check for data on ICECat
						$ch = curl_init();
						curl_setopt ($ch, CURLOPT_URL, $request_url);
						curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
						curl_setopt ($ch, CURLOPT_HTTPHEADER, $http_headers);
						$icecat_response = curl_exec ($ch);
						curl_close ($ch);

						parse_icecat_response($icecat_response, $product, $features, $error, $error_code);
					}
				}

				// update product information and output progress
				if ($error) {
					// output error
					$import_failed++;
					$item_link = "<a href=\"admin_product.php?item_id=$item_id&current_tab=icecat\"><b>$item_id</b></a>: ";
					output_block_info($import_failed, "importFailed");
					output_block_info($item_link . $error . "<br>", "importErrors", true);
					// save error in database
					$it->change_property("small_image", USE_IN_UPDATE, false);
					$it->change_property("big_image", USE_IN_UPDATE, false);
					$it->change_property("super_image", USE_IN_UPDATE, false);
					$it->change_property("short_description", USE_IN_UPDATE, false);
					$it->change_property("full_description", USE_IN_UPDATE, false);

					$it->set_value("item_id", $item_id);
					$it->set_value("icecat_status_id", 2); // error status
					$it->set_value("icecat_error", $error);
					$it->set_value("icecat_updated", va_time());
					$it->update_record();
				} else {
					// output success import
					$import_success++;
					output_block_info($import_success, "importSuccess");
					// save data in the database
					$it->change_property("small_image", USE_IN_UPDATE, true);
					$it->change_property("big_image", USE_IN_UPDATE, true);
					$it->change_property("super_image", USE_IN_UPDATE, true);
					$it->change_property("short_description", USE_IN_UPDATE, true);
					$it->change_property("full_description", USE_IN_UPDATE, true);

					$it->set_value("item_id", $item_id);
					$it->set_value("icecat_status_id", 3); // updated status
					$it->set_value("icecat_error", "");
					$it->set_value("icecat_updated", va_time());
					$it->set_value("small_image", $product["thumbpic"]);
					$it->set_value("big_image", $product["lowpic"]);
					$it->set_value("super_image", $product["highpic"]);
					$it->set_value("short_description", $product["shortdesc"]);
					$it->set_value("full_description", $product["longdesc"]);
					$it->update_record();
				}
  
				// update product features
				$f->set_value("item_id", $item_id);
				if (sizeof($features) > 0) {
					$sql = " DELETE FROM " . $table_prefix . "features WHERE item_id=" . $db->tosql($item_id, INTEGER);
					$db->query($sql);
					foreach ($features as $id => $feature) {
						$f->set_value("group_id", $feature["group_id"]);
						$f->set_value("feature_name", $feature["name"]);
						$f->set_value("feature_value", $feature["value"]);
						$f->insert_record();
					}
				}
  
				if ($items_per_cycle == $items_number) {
					// get next records to continue update data
					$items_number = 0;
					$dbs->RecordsPerPage = $items_per_cycle;
					$dbs->PageNumber = 1;              
					$dbs->query($items_sql);
				}
			} while ($dbs->next_record());

			// final ooutput
			echo "<script language=\"JavaScript\" type=\"text/javascript\">".$eol."<!--".$eol."importFinished();".$eol."//-->".$eol."</script>".$eol;
			flush();

			$t->pparse("page_end", false);
			flush();
		}
	} else if ($operation == "download") {
		$filesize = remote_filesize($prod_txt_url);
		echo "File Size: " . $filesize;

		$parsed_url = parse_url($prod_txt_url);
		$host = $parsed_url["host"];
		$path = $parsed_url["path"];
	
		$fr = fopen($prod_txt_url, "r");
		//$fr = @fsockopen($host, 80, $errno, $errstr, 5);
		if (!$fr) {
			echo "Can't download file.";
		} else {
			/*
			$out  = "GET " . $path . " HTTP/1.1\r\n";
			$out .= "User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fr, $out);
			*/

			$fw = fopen("../downloads/prodid_d.txt", "w");
			while (!feof ($fr)) {
			  $line = fread ($fr, 8192);
				fputs($fw, $line);
			}
			fclose($fr);
			fclose($fw);
			echo "<br>File Downloaded";
		}
	} else {
		if ($operation == "status") {
			if (strlen($status_id) && strlen($new_status_id)) {
				$sql  = " UPDATE " . $table_prefix . "items ";
				$sql .= " SET icecat_status_id=" . $db->tosql($new_status_id, INTEGER);
				$sql .= " WHERE icecat_status_id=" . $db->tosql($status_id, INTEGER);
				$db->query($sql);
			}
			$status_id = get_param("status_id");
			$new_status_id = get_param("new_status_id");

		}

		// check products stats
		$sql = " SELECT COUNT(*) FROM " . $table_prefix . "items ";
		$total_items = get_db_value($sql);

		$sql  = " SELECT icecat_status_id, COUNT(*) AS icecat_items ";
		$sql .= " FROM " . $table_prefix . "items ";
		$sql .= " GROUP BY icecat_status_id ";
		$db->query($sql);
		while ($db->next_record()) {
			$icecat_status_id = $db->f("icecat_status_id");
			$icecat_items = $db->f("icecat_items");
			$icecat_status = get_setting_value($icecat_statuses_aa, $icecat_status_id, NOT_AVAILABLE_MSG);
			$t->set_var("icecat_status", $icecat_status);
			$t->set_var("icecat_status_id", $icecat_status_id);

			$t->set_var("icecat_items", $icecat_items);
			if ($icecat_status_id == 1) {
				$t->parse("import_link", false);
			} else {
				$t->set_var("import_link", "");
			}

			set_options($icecat_statuses, $icecat_status_id, "new_status_id");		
			$t->parse("update_status", "");

			$t->parse("items", true);
		}

		$t->set_var("total_items", $total_items);
		$t->parse("items_stats", false);
		$t->parse("page_end", false);
		$t->pparse("main");
		flush();
	}


function parse_icecat_response($icecat_response, &$product, &$features, &$error, &$error_code)
{
	global $db, $fg, $table_prefix;

	$product = array(); $features = array(); $error = "";
	if (preg_match("/<Product[^>]+>/is", $icecat_response, $match)) {
		$product_tag = $match[0];
		// convert xml into array
		preg_match_all ("/(\w+)=\"([^\"]+)\"/i", $product_tag, $matches, PREG_SET_ORDER);
		for($i = 0; $i < sizeof($matches); $i++) {
			$product[strtolower($matches[$i][1])] = decode_icecat_xml($matches[$i][2]);
		}
		$code = get_setting_value($product, "code", "");
		if ($code == -1) { // error code returned
			$error = get_setting_value($product, "errormessage", ERRORS_MSG);
			$error_code = -1;
		} else if ($code != 1) { // no success code present
			$error = get_setting_value($product, "errormessage", "");
			$error_code = -2;
		}

		// if there is no error check other product details
		if (!$error) {
			// check for product description 
			if(preg_match("/<ProductDescription[^>]+>/is", $icecat_response, $match)) {
				$desc_tag = $match[0];
				// added desc attributes to product array
				preg_match_all ("/(\w+)=\"([^\"]+)\"/i", $desc_tag, $matches, PREG_SET_ORDER);
				for($i = 0; $i < sizeof($matches); $i++) {
					$product[strtolower($matches[$i][1])] = decode_icecat_xml($matches[$i][2]);
				}
			}

			// check specification groups
			$groups = array();
			if (preg_match_all ("/<CategoryFeatureGroup([^>]+)>.*<Name([^>]+)>.*<\/CategoryFeatureGroup>/Uis", $icecat_response, $groups_matches, PREG_SET_ORDER)) {
				for ($m = 0; $m < sizeof($groups_matches); $m++) {
					$name = array();
					$group_tag = $groups_matches[$m][1];
					$name_tag = $groups_matches[$m][2];
					preg_match_all ("/(\w+)=\"([^\"]+)\"/Si", $group_tag, $attributes_matches, PREG_SET_ORDER);
					for($i = 0; $i < sizeof($attributes_matches); $i++) {
						$name[strtolower($attributes_matches[$i][1])] = decode_icecat_xml($attributes_matches[$i][2]);
					}
					$id = get_setting_value($name, "id", "");
					$group_order = get_setting_value($name, "no", "1");
					preg_match_all ("/(\w+)=\"([^\"]+)\"/si", $name_tag, $attributes_matches, PREG_SET_ORDER);
					for($i = 0; $i < sizeof($attributes_matches); $i++) {
						$name[strtolower($attributes_matches[$i][1])] = decode_icecat_xml($attributes_matches[$i][2]);
					}
					$group_name = get_setting_value($name, "value", "");
					$groups[$id] = array("icecat_id" => $id, "order" => $group_order, "name" => $group_name);
				}
			}

			// check specification groups in the shop database
			foreach ($groups as $id => $group_data) {
				$group_name = $group_data["name"];
				$group_oder = $group_data["order"];
				$sql  = " SELECT group_id FROM " . $table_prefix . "features_groups ";
				$sql .= " WHERE group_name=" . $db->tosql($group_name, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$group_id = $db->f("group_id");
				} else {
					// add new groups to our database
					if ($db->DBType == "postgre") {
						$group_id = get_db_value(" SELECT NEXTVAL('seq_" . $table_prefix . "features_groups') ");
						$fg->set_value("group_id", $group_id);
					}
					$fg->set_value("group_order", $group_order);
					$fg->set_value("group_name", $group_name);
					$fg->insert_record();
					if ($db->DBType == "mysql") {
						$group_id = get_db_value(" SELECT LAST_INSERT_ID() ");
					} elseif ($db->DBType == "access") {
						$group_id = get_db_value(" SELECT @@IDENTITY ");
					} elseif ($db->DBType == "db2") {
						$group_id = get_db_value(" SELECT PREVVAL FOR seq_" . $table_prefix . "features_groups FROM " . $table_prefix . "features_groups");
					}
				}
				$groups[$id]["group_id"] = $group_id;
			}

			// check features
			if (preg_match_all ("/<ProductFeature([^>]+)>(.*)<\/ProductFeature>/Uis", $icecat_response, $feature_matches, PREG_SET_ORDER)) {
				for($m = 0; $m < sizeof($feature_matches); $m++) {
					$feature = array();
					$feature_tags = $feature_matches[$m][0];
					$feature_attributes = $feature_matches[$m][1];
					preg_match_all ("/(\w+)=\"([^\"]+)\"/i", $feature_attributes, $attributes_matches, PREG_SET_ORDER);
					for($fa = 0; $fa < sizeof($attributes_matches); $fa++) {
						$feature[strtolower($attributes_matches[$fa][1])] = decode_icecat_xml($attributes_matches[$fa][2]);
					}
					$feature_value = get_setting_value($feature, "presentation_value", "");
					$icecat_group_id = get_setting_value($feature, "categoryfeaturegroup_id", "");
					$feature_group_id = $groups[$icecat_group_id]["group_id"];

					$name = array();
					if (preg_match ("/<Name[^>]+>/Uis", $feature_tags, $name_match)) {
						$name_tag = $name_match[0];
						preg_match_all ("/(\w+)=\"([^\"]+)\"/i", $name_tag, $attributes_matches, PREG_SET_ORDER);
						for($i = 0; $i < sizeof($attributes_matches); $i++) {
							$name[strtolower($attributes_matches[$i][1])] = decode_icecat_xml($attributes_matches[$i][2]);
						}
					}
					$feature_name = get_setting_value($name, "value", "");

					$features[] = array(
						"name" => $feature_name, "value" => $feature_value, 
						"icecat_group_id" => $icecat_group_id, "group_id" => $feature_group_id
					);
				}
			}


		}
	} else {
		$error = "Can't parse ICECat response.";
		$error_code = -3;
	}
	
}

function decode_icecat_xml($xml)
{
	$search = array("&amp;", "&#039;", "&apos;", "&quot;", "&lt;", "&gt;", "\\n");
	$replace = array("&", "'", "'", "\"", "<", ">", "\n");
	return str_replace ($search, $replace, $xml);
}

function output_block_info($message, $control_name, $append = false) 
{
	global $eol;
	$message = str_replace(array("'", "\n", "\r"), array("\\'", "\\n", "\\r"), $message);
	echo "<script language=\"JavaScript\" type=\"text/javascript\">".$eol."<!--".$eol."updateBlockInfo('".$message."','".$control_name."',".intval($append).");".$eol."//-->".$eol."</script>".$eol;
	flush();
}

function get_ean_code($manufacturer_code, $manufacturer_name)
{
	global $prod_txt_path;
	$ean = "";
	$manufacturer_code = strtoupper($manufacturer_code);
	$manufacturer_name = strtoupper($manufacturer_name);
	$row = 0;
	$matched = false;
	$fr = fopen($prod_txt_path, "r");
	while (!feof($fr) && !$matched) {
		$row++;
	  $line = fgets ($fr);
		$values = explode("\t\t\t", $line);
		if (sizeof($values) == 7) {
			$part_number = strtoupper($values[0]);
			$brand = strtoupper($values[1]);
			if (strval($part_number) == strval($manufacturer_code) && strval($brand) == strval($manufacturer_name)) {
				$matched = true;
				$ean = $values[5];
			}
		}
	}
	return $ean;
}


?>