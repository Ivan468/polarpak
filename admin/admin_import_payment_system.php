<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_import_payment_system.php                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit(600);
	@ini_set("auto_detect_line_endings", 1);
	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");

	check_admin_security("import_export");

	$errors = "";
	$xml_file_path = get_param("xml_file_path");
	$operation = get_param("operation");
	$is_file_path = false;
	$tmp_dir = get_setting_value($settings, "tmp_dir", "");
	
	$eol = get_eol();
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_import_payment_system.html");
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->set_var("UPLOAD_SELECT_JS", str_replace("{button_name}", "<b>".UPLOAD_TITLE."</b>", UPLOAD_SELECT_MSG));

	$admin_payment_system_href = "admin_payment_system.php";
	$t->set_var("admin_payment_system_href", $admin_payment_system_href);
	$t->set_var("admin_import_href", "admin_import_payment_system.php");
		
	if ($operation == "upload")
	{
		$tmp_name = "";
		if (strlen($xml_file_path)) {
			if (file_exists($xml_file_path)) {
				$fp = fopen($xml_file_path, "r");
				if (!$fp) {
					$errors = CANT_OPEN_IMPORTED_MSG;
				}
			} else {
				$errors = FILE_DOESNT_EXIST_MSG . "<b>$xml_file_path</b>";
			}
		} else {

			if (isset($_FILES)) {
				$tmp_name = $_FILES["xml_file"]["tmp_name"];
				$filename = $_FILES["xml_file"]["name"];
				$filesize = $_FILES["xml_file"]["size"];
				$upload_error = isset($_FILES["xml_file"]["error"]) ? $_FILES["xml_file"]["error"] : "";
			}
	
			if ($upload_error == 1) {
				$errors = FILESIZE_DIRECTIVE_ERROR_MSG;
			} elseif ($upload_error == 2) {
				$errors = FILESIZE_PARAMETER_ERROR_MSG;
			} elseif ($upload_error == 3) {
				$errors = PARTIAL_UPLOAD_ERROR_MSG;
			} elseif ($upload_error == 4) {
				$errors = NO_FILE_UPLOADED_MSG;
			} elseif ($upload_error == 6) {
				$errors = TEMPORARY_FOLDER_ERROR_MSG;
			} elseif ($upload_error == 7) {
				$errors = FILE_WRITE_ERROR_MSG;
			} elseif ($tmp_name == "none" || !strlen($tmp_name)) {
				$errors = NO_FILE_UPLOADED_MSG;
			}
		}
	
		if (!strlen($errors)) {
			/* if we need to parse file localy
			if ($tmp_name && $tmp_dir) {
				$tmp_filename = "tmp_" . md5(uniqid(rand(), true)) . ".xml";
				if (@move_uploaded_file($tmp_name, $tmp_dir. $tmp_filename)) {
					$xml_file_path = $tmp_dir . $tmp_filename;
				}
			}//*/
	
			if (strlen($xml_file_path)) {
				$fp = fopen($xml_file_path, "r");
			} else {
				$fp = fopen($tmp_name, "r");
			}
			if (!$fp) {
				$errors = CANT_OPEN_IMPORTED_MSG;
			}
		}

	
		if (!strlen($errors)) {
			$operation = "result";
			$xml_data = "";
			while ($data = fgets($fp, 65536)) {
				$xml_data .= $data;
			}
			fclose($fp);
	
			//---------------- parse payment_system ----------------
			if (preg_match_all("/<payment_system>(.*)\<\/payment_system>/Uis", $xml_data, $matches_payment_system, PREG_SET_ORDER)){
				preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/Uis", $matches_payment_system[0][1], $matches, PREG_SET_ORDER);
				$fields = "";
				$values = "";
				$payment_id = 0;
				$payment_order = "1";
				$payment_name = "";
				$user_payment_name = "";
				$payment_url = "";
				foreach($matches as $value){
					if(strlen($value[2])){
						$fields .=(strlen($fields))?", ".$db->tosql(xml_restore_string($value[1]), TEXT, false): $db->tosql(xml_restore_string($value[1]), TEXT, false);
						$values .=(strlen($values))?", ".$db->tosql(xml_restore_string($value[2]), TEXT): $db->tosql(xml_restore_string($value[2]), TEXT);
					}
					if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "payment_order"){
						$payment_order = xml_restore_string($value[2]);
					}
					if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "payment_name"){
						$payment_name = xml_restore_string($value[2]);
					}
					if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "user_payment_name"){
						$user_payment_name = xml_restore_string($value[2]);
					}
					if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "payment_url"){
						$payment_url = xml_restore_string($value[2]);
					}
                    if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "order_total_min" && xml_restore_string($value[2] == "")){
                        $fields .=(strlen($fields))?", ".$db->tosql(xml_restore_string($value[1]), TEXT, false): $db->tosql(xml_restore_string($value[1]), TEXT, false);
                        $values .=(strlen($values))?", "."NULL": "NULL";
                    }
                    if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "order_total_max" && xml_restore_string($value[2] == "")){
                        $fields .=(strlen($fields))?", ".$db->tosql(xml_restore_string($value[1]), TEXT, false): $db->tosql(xml_restore_string($value[1]), TEXT, false);
                        $values .=(strlen($values))?", "."NULL": "NULL";
                    }

				}
				if(strlen($payment_name) && strlen($payment_url)){
					$sql  = " INSERT INTO " . $table_prefix . "payment_systems ";
					$sql .= " (".$fields.") ";
					$sql .= " VALUES(".$values.") ";
					$db->query($sql);

					$sql  = " SELECT payment_id ";
					$sql .= " FROM " . $table_prefix . "payment_systems ";
					$sql .= " WHERE payment_name=" . $db->tosql($payment_name, TEXT);
					$sql .= " AND payment_url=" . $db->tosql($payment_url, TEXT);
					if(strlen($payment_order)){
						$sql .= " AND payment_order=" . $db->tosql($payment_order, INTEGER);
					}
					if(strlen($user_payment_name)){
						$sql .= " AND user_payment_name=" . $db->tosql($user_payment_name, TEXT);
					}
					$sql .= " ORDER BY payment_id DESC";
					$db->query($sql);
					if($db->next_record()) {
						$payment_id = $db->f("payment_id");
					}
				}else{
					$errors = 'payment_name and payment_url is empty!';
				} //---------------- payment_system ----------------

				// begin: payment_parameters parse 
				if($payment_id && preg_match_all("/<payment_parameters>(.*)\<\/payment_parameters>/Uis", $xml_data, $matches_payment_parameters, PREG_SET_ORDER)){
					if($payment_id && preg_match_all("/<parameter>(.*)\<\/parameter>/Uis", $matches_payment_parameters[0][1], $matches_parameter, PREG_SET_ORDER)){
						foreach($matches_parameter as $parameter){
							preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/Uis", $parameter[1], $matches, PREG_SET_ORDER);
							$fields = "";
							$values = "";
							foreach($matches as $value){
								$fields .=(strlen($fields))?", ".$db->tosql(xml_restore_string($value[1]), TEXT, false): $db->tosql(xml_restore_string($value[1]), TEXT, false);
								$values .=(strlen($values))?", ".$db->tosql(xml_restore_string($value[2]), TEXT): $db->tosql(xml_restore_string($value[2]), TEXT);
							}
							if(strlen($fields)){
								$sql  = " INSERT INTO " . $table_prefix . "payment_parameters ";
								$sql .= " (".$fields." , payment_id) ";
								$sql .= " VALUES(".$values." , ".$db->tosql($payment_id, INTEGER).") ";
								$db->query($sql);
							}
						}
					}
				} // end: payment_parameters 

				// begin: user_types parse 
				if($payment_id && preg_match("/<user_types>(.*)\<\/user_types>/Uis", $xml_data, $types_match)) {
					if (preg_match_all("/<user_type_id>(.*)\<\/user_type_id>/Uis", $types_match[1], $matches_ids)) {
						for ($m = 0; $m < sizeof($matches_ids[1]); $m++) {
							$user_type_id = $matches_ids[1][$m];
							if (strlen($user_type_id)) {
								$sql  = " INSERT INTO " . $table_prefix . "payment_user_types ";
								$sql .= " (user_type_id, payment_id) VALUES (";
								$sql .= $db->tosql($user_type_id, INTEGER) ." , ".$db->tosql($payment_id, INTEGER).") ";
								$db->query($sql);
							}
						}
					}
				} // end: user_types 
				// begin: sites parse 
				if($payment_id && preg_match("/<sites>(.*)\<\/sites>/Uis", $xml_data, $types_match)) {
					if (preg_match_all("/<site_id>(.*)\<\/site_id>/Uis", $types_match[1], $matches_ids)) {
						for ($m = 0; $m < sizeof($matches_ids[1]); $m++) {
							$site_id = $matches_ids[1][$m];
							if (strlen($site_id)) {
								$sql  = " INSERT INTO " . $table_prefix . "payment_systems_sites ";
								$sql .= " (site_id, payment_id) VALUES (";
								$sql .= $db->tosql($site_id, INTEGER) ." , ".$db->tosql($payment_id, INTEGER).") ";
								$db->query($sql);
							}
						}
					}
				} // end: sites 

				//---------------- custom_properties ----------------
				if($payment_id && preg_match_all("/<custom_properties>(.*)\<\/custom_properties>/Uis", $xml_data, $matches_custom_properties, PREG_SET_ORDER)){
					if($payment_id && preg_match_all("/<custom_property>(.*)\<\/custom_property>/Uis", $matches_custom_properties[0][1], $matches_custom_property, PREG_SET_ORDER)){
						foreach($matches_custom_property as $custom_property){
							$custom_property_str = $custom_property[1];
							$is_custom_value = false;
							$property_id = 0;
							$site_id = 0;
							$property_order = 0;
							$property_name = "";
							if(strpos($custom_property[1],'<custom_value>')){
								$is_custom_value = true;
								$custom_property_str = preg_replace("/<custom_value>(.*)\<\/custom_value>/Uis","",$custom_property_str);
							}
							preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/Uis", $custom_property_str, $matches, PREG_SET_ORDER);
							$fields = "";
							$values = "";
							foreach($matches as $value){
								$fields .=(strlen($fields))?", ".$db->tosql(xml_restore_string($value[1]), TEXT, false): $db->tosql(xml_restore_string($value[1]), TEXT, false);
								$values .=(strlen($values))?", ".$db->tosql(xml_restore_string($value[2]), TEXT): $db->tosql(xml_restore_string($value[2]), TEXT);
								if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "site_id"){
									$site_id = xml_restore_string($value[2]);
								}
								if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "property_order"){
									$property_order = xml_restore_string($value[2]);
								}
								if($db->tosql(xml_restore_string($value[1]), TEXT, false) == "property_name"){
									$property_name = xml_restore_string($value[2]);
								}
							}
							if(strlen($fields)){
								$sql  = " INSERT INTO " . $table_prefix . "order_custom_properties ";
								$sql .= " (".$fields." , payment_id) ";
								$sql .= " VALUES(".$values." , ".$db->tosql($payment_id, INTEGER).") ";
								$db->query($sql);
								if($is_custom_value){
									$sql  = " SELECT property_id ";
									$sql .= " FROM " . $table_prefix . "order_custom_properties ";
									$sql .= " WHERE site_id=" . $db->tosql($site_id, INTEGER);
									$sql .= " AND payment_id=" . $db->tosql($payment_id, INTEGER);
									$sql .= " AND property_order=" . $db->tosql($property_order, INTEGER);
									$sql .= " AND property_name=" . $db->tosql($property_name, TEXT);
									$sql .= " ORDER BY property_id DESC";
									$db->query($sql);
									if($db->next_record()) {
										$property_id = $db->f("property_id");
									}
									if($payment_id && preg_match_all("/<custom_value>(.*)\<\/custom_value>/Uis", $matches_custom_properties[0][1], $matches_custom_value, PREG_SET_ORDER)){
										foreach($matches_custom_value as $custom_value){
											preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/Uis", $custom_value[1], $matches, PREG_SET_ORDER);
											$fields = "";
											$values = "";
											foreach($matches as $value){
												$fields .=(strlen($fields))?", ".$db->tosql(xml_restore_string($value[1]), TEXT, false): $db->tosql(xml_restore_string($value[1]), TEXT, false);
												$values .=(strlen($values))?", ".$db->tosql(xml_restore_string($value[2]), TEXT): $db->tosql(xml_restore_string($value[2]), TEXT);
											}
											if(strlen($fields)){
												$sql  = " INSERT INTO " . $table_prefix . "order_custom_values ";
												$sql .= " (".$fields." , property_id) ";
												$sql .= " VALUES(".$values." , ".$db->tosql($property_id, INTEGER).") ";
												$db->query($sql);
											}
										}
									}
								}
							}
						}
					}
				}
//---------------- custom_properties ----------------

//---------------- credit_card_info ----------------
				if($payment_id && preg_match_all("/<credit_card_info>(.*)\<\/credit_card_info>/Uis", $xml_data, $matches_credit_card_info, PREG_SET_ORDER)){
					foreach($matches_credit_card_info as $matche_credit_card_info){
						$site_id = 1;
						$credit_card_info = $matche_credit_card_info[1];
						if(preg_match_all("/<site_id>(.*)\<\/site_id>/Uis", $credit_card_info, $matches_site_id, PREG_SET_ORDER)){
							$site_id = $db->tosql(xml_restore_string($matches_site_id[0][1]), INTEGER);
							$credit_card_info = preg_replace("/<site_id>(.*)\<\/site_id>/Uis","",$credit_card_info);
						}
						preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/Uis", $credit_card_info, $matches, PREG_SET_ORDER);
						foreach($matches as $value){
							$setting_name = $db->tosql(xml_restore_string($value[1]), TEXT);
							$setting_value = $db->tosql(xml_restore_string($value[2]), TEXT);
							if(strlen($setting_name)){
								$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
								$sql .= " (site_id, setting_type, setting_name, setting_value) ";
								$sql .= " VALUES(".$site_id.", 'credit_card_info_".$payment_id."', ".$setting_name." , ".$setting_value.") ";
								$db->query($sql);
							}
						}
					}
				} //---------------- credit_card_info ----------------

//---------------- order_final ----------------
				if($payment_id && preg_match_all("/<order_final>(.*)\<\/order_final>/Uis", $xml_data, $matches_order_final, PREG_SET_ORDER)){
					foreach($matches_order_final as $matche_order_final){
						$site_id = 1;
						$order_final = $matche_order_final[1];
						if(preg_match_all("/<site_id>(.*)\<\/site_id>/Uis", $order_final, $matches_site_id, PREG_SET_ORDER)){
							$site_id = $db->tosql(xml_restore_string($matches_site_id[0][1]), INTEGER);
							$order_final = preg_replace("/<site_id>(.*)\<\/site_id>/Uis","",$order_final);
						}
	
						preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/Uis", $order_final, $matches, PREG_SET_ORDER);
						foreach($matches as $value){
							$setting_name = $db->tosql(xml_restore_string($value[1]), TEXT);
							$setting_value = $db->tosql(xml_restore_string($value[2]), TEXT);
							if(strlen($setting_name)){
								$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
								$sql .= " (site_id, setting_type, setting_name, setting_value) ";
								$sql .= " VALUES(".$site_id.", 'order_final_".$payment_id."', ".$setting_name." , ".$setting_value.") ";
								$db->query($sql);
							}
						}
					}
				}
//---------------- order_final ----------------

//---------------- recurring ----------------
				if($payment_id && preg_match_all("/<recurring>(.*)\<\/recurring>/Uis", $xml_data, $matches_recurring, PREG_SET_ORDER)){
					foreach($matches_recurring as $matche_recurring){
						$site_id = 1;
						$recurring = $matche_recurring[1];
						if(preg_match_all("/<site_id>(.*)\<\/site_id>/Uis", $recurring, $matches_site_id, PREG_SET_ORDER)){
							$site_id = $db->tosql(xml_restore_string($matches_site_id[0][1]), INTEGER);
							$recurring = preg_replace("/<site_id>(.*)\<\/site_id>/Uis","",$recurring);
						}
	
						preg_match_all ("/<([^>]*?)>([^<]*?)\<\/[^>]*>/Uis", $recurring, $matches, PREG_SET_ORDER);
						foreach($matches as $value){
							$setting_name = $db->tosql(xml_restore_string($value[1]), TEXT);
							$setting_value = $db->tosql(xml_restore_string($value[2]), TEXT);
							if(strlen($setting_name)){
								$sql  = " INSERT INTO " . $table_prefix . "global_settings ";
								$sql .= " (site_id, setting_type, setting_name, setting_value) ";
								$sql .= " VALUES(".$site_id.", 'recurring_".$payment_id."', ".$setting_name." , ".$setting_value.") ";
								$db->query($sql);
							}
						}
					}
				}
//---------------- recurring ----------------
			} else if(!$xml_file_path) {
			//no payment system in the file
				$errors = $filename . "<br>" . UPLOAD_FORMAT_ERROR ;
			}
		}
	}
	
	if (strlen($errors))
	{
		$t->set_var("errors_list", $errors);
		$t->parse("errors", false);
	}
	else
	{
		$t->set_var("errors", "");
	}
	
	//$t->set_var("xml_file_path", htmlspecialchars($xml_file_path));
	
	$t->set_var("upload_block", "");
	$t->set_var("result_block", "");
	if ($operation == "result" && !$errors) {
		$t->set_var("payment_name", $payment_name);

		$success_payment_name = "Your payment system <b>'<a href='$admin_payment_system_href?payment_id=$payment_id'>$payment_name</a>'</b> has been successfully added.";

		$t->set_var("success_payment_name", $success_payment_name);

		$t->set_var("payment_id", $payment_id);
		$t->parse("result_block", false);
	} else {
		$operation = "upload";
		$t->parse("upload_block", false);
	}

	$t->pparse("main");

	function xml_restore_string($str) 
	{
		return html_entity_decode(str_replace("&apos;", "&#039;", $str), ENT_QUOTES);
	}

?>