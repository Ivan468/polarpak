<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_xml.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$root_folder_path = "../";

	@include_once($root_folder_path . "includes/var_definition.php");

	$e = 0;
	$error = Array();

	if (!defined("INSTALLED") || !INSTALLED) {
		define ("CHARSET","iso-8859-1");
		$error[$e][0] = 105;
		$error[$e][1] = "";
		$e++;
		generate_error($error);
	}

	include_once($root_folder_path . "includes/constants.php");
	include_once($root_folder_path . "includes/common_functions.php");
	include_once($root_folder_path . "includes/va_functions.php");

	$xml = get_param('xml');

	if (strpos($xml,"<Messages>")){
		$language_code = get_text($xml,"Messages");
		$language_code = get_text($language_code,"Language");
		if(!file_exists($root_folder_path ."messages/" . $language_code . "/messages.php")){
			define("CHARSET", "iso-8859-1");
			$error[$e][0] = 106;
			$error[$e][1] = $language_code;
			$e++;
			generate_error($error);
		}
	} else {
		$language_code = get_language("messages.php");
	}

	include_once($root_folder_path ."messages/" . $language_code . "/messages.php");
	include_once($root_folder_path . "includes/date_functions.php");
	include_once($root_folder_path . "includes/template.php");
	include_once($root_folder_path . "includes/db_$db_lib.php");
	if (file_exists($root_folder_path . "includes/license.php") ) {
		include_once($root_folder_path . "includes/license.php");
	} 

	// Database Initialize
	$db = new VA_SQL();
	$db->DBType      = $db_type;
	$db->DBDatabase  = $db_name;
	$db->DBHost      = $db_host;
	$db->DBPort      = $db_port;
	$db->DBUser      = $db_user;
	$db->DBPassword  = $db_password;
	$db->DBPersistent= $db_persistent;

	// get site properties
	$settings = va_settings();

	$block_name = "";

	if (strpos($xml,"<GetOrdersStatuses")){
		$block_name = "get_orders_statuses"; 
	} else if (strpos($xml,"<GetOrderStatusDetails")){
		$block_name = "get_orders_statuses";
	} else if (strpos($xml,"<GetOrdersIds")){
		$block_name = "get_orders_ids";
	} else if (strpos($xml,"<UpdateOrderStatus")){
		$block_name = "update_order_status";
	} else if (strpos($xml,"<DuplicateCoupon")){
		$block_name = "add_duplicate_coupons";
	} else if (strpos($xml,"<AddCoupon")) {
		$block_name = "add_duplicate_coupons";
	} else {
		$error[$e][0] = 107;
		$error[$e][1] = "";
		$e++;
		generate_error($error);
	}

	if (strpos($xml,"<Credentials>")){
		$test = get_text($xml,"Credentials");
		$login = get_text($test,"Username");
		$pass = get_text($test,PASSWORD_FIELD);
		$argc = $login;
		$argv = $pass;
		if (!check_adm_security($block_name)){
			switch ($block_name){
				case "get_orders_statuses":
					$block_name = "Get Orders Statuses";
				break;
				case "get_orders_ids":
					$block_name = "Get Orders Ids";
				break;
				case "update_order_status":
					$block_name = "Update Order Status";
				break;
			}
			$error[$e][0] = 108;
			$error[$e][1] = $block_name;
			$e++;
			generate_error($error);
		}
	} else {
		$error[$e][0] = 101;
		$error[$e][1] = "";
		$e++;
		generate_error($error);
	}

	$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings WHERE setting_type='order_info' ";
	$db->query($sql);
	while($db->next_record()) {
		$order_info[$db->f("setting_name")] = $db->f("setting_value");
	}

	if (strpos($xml,"<GetOrdersStatuses")){
		$name = "";
		$sql = "select * from ".$table_prefix."order_statuses";
		$db->query($sql);
		$i = 0;
		if ($db->next_record()){
			do {
				$mas[$i]['status_id'] = $db->f('status_id');
				$mas[$i]['status_name'] = $db->f('status_name');
				$i++;
			} while ($db->next_record());
		} else {
			$error[$e][0] = 102;
			$error[$e][1] = "";
			$e++;
		}

		if (count($error)){
			generate_error($error,"GetOrderStatusDetails");
		}

		generate_xml_status($name,$mas);
	} else if (strpos($xml,"<GetOrderStatusDetails")){

		$criterion = get_text($xml,"Criterions");
		$id = get_text($criterion,"status_id");

		$head = "<Criterions>\n	<status_id>".$id."</status_id>\n</Criterions>\n";

		if (strlen($id)){
			if (intval($id) == 0){
				$error[$e][0] = 103;
				$error[$e][1] = "status_id";
				$e++;
			} else {
				$fields = $db->get_fields($table_prefix."order_statuses");
				$sql = "SELECT * FROM ".$table_prefix."order_statuses WHERE status_id = ".$db->tosql($id, INTEGER, true, false);
				$db->query($sql);
				if ($db->next_record()){
					do{
						for($i=0;$i<count($fields);$i++){
							$column = strtolower($fields[$i]['name']);
							$mas[0][$column] = $db->f($column);
						}
					} while($db->next_record());
				} else {
					include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");
					$error[$e][0] = 111;
					$error[$e][1] = $id;
					$e++;
				}
			}
		} else {
			$error[$e][0] = 102;
			$error[$e][1] = "status_id";
			$e++;
		}

		if (count($error)){
			generate_error($error,"GetOrderStatusDetails",$head);
		}

		generate_xml_status_all($id,$mas);

	} else if (strpos($xml,"<GetOrdersIds")){

		$criterions = get_text($xml,"Criterions");

		$s_on = get_text($criterions,"OrderNumber"); //order No
		$s_ne = get_text($criterions,"NameEmail"); //By Name, E-Mail:	
		$s_kw = get_text($criterions,"Keyword"); //By Keyword, S/N:	
		$s_sd1 = get_text($criterions,"StartDate"); //From Date (YYYY-MM-DD):
		$s_ed1 = get_text($criterions,"EndDate"); //To Date (YYYY-MM-DD):
		$s_os = get_text($criterions,"OrderStatus"); //Where status is:	 
		$s_ci1 = get_text($criterions,"ShipToCountry"); //Ship To Country:	 
		$s_si1 = get_text($criterions,"ShipToState"); //Ship To State:	 
		$s_cct = get_text($criterions,"CreditCardType"); //Credit Card Type:

		$head = "<Criterions>\n";
		$head.= "	<OrderNumber>".$s_on."</OrderNumber>\n";
		$head.= "	<NameEmail>".$s_ne."</NameEmail>\n";
		$head.= "	<Keyword>".$s_kw."</Keyword>\n";
		$head.= "	<StartDate>".$s_sd1."</StartDate>\n";
		$head.= "	<EndDate>".$s_ed1."</EndDate>\n";
		$head.= "	<OrderStatus>".$s_os."</OrderStatus>\n";
		$head.= "	<ShipToCountry>".$s_ci1."</ShipToCountry>\n";
		$head.= "	<ShipToState>".$s_si1."</ShipToState>\n";
		$head.= "	<CreditCardType>".$s_cct."</CreditCardType>\n";
		$head.= "</Criterions>\n";

		if (strlen($s_on)){
			if (intval($s_on) == 0){
				$error[$e][0] = 103;
				$error[$e][1] = "OrderNumber";
				$e++;
			}
		}

		$s_sd = parse_date($s_sd1,$date_edit_format,$date_errors,"StartDate");
		if ($date_errors){
			$error[$e][0] = 104;
			$error[$e][1] = strip_tags($date_errors);
			$e++;
		}
		$date_errors = "";
		$s_ed = parse_date($s_ed1,$date_edit_format,$date_errors,"EndDate");
		if ($date_errors){
			$error[$e][0] = 104;
			$error[$e][1] = strip_tags($date_errors);
			$e++;
		}

		$s_ci = $s_ci1;	$c_id = false;
		if (strlen($s_ci)){
			if ( ($s_ci + 1) == 1 ){
				$sql = "SELECT country_id FROM ".$table_prefix."countries WHERE country_code = '".strtoupper($s_ci)."'";
				$sql.= " or country_name = '".$s_ci."'";
				$s_ci1 = '';
				$db->query($sql);
				if ($db->next_record()){
					do {
						if (strlen($s_ci)) $s_ci.= ", ";
						$s_ci .= $db->f('country_id');
					} while ($db->next_record());
				} else {
					$error[$e][0] = 109;
					$error[$e][1] = $s_ci;
					$e++;
				}
			} else {
				$sql = "SELECT country_id FROM ".$table_prefix."countries WHERE country_id = ".$s_ci;
				$db->query($sql);
				if (!$db->next_record()){
					$error[$e][0] = 109;
					$error[$e][1] = $s_ci;
					$e++;
				} else {
					$c_id = true;
				}
			}
		}

		$s_si = $s_si1;
		if (strlen($s_si)){
			if ( ($s_si + 1) == 1 ){
				$sql = "SELECT state_id FROM ".$table_prefix."states WHERE state_code = '".strtoupper($s_si)."'";
				$sql.= " OR state_name = '".$s_si."'";
				if ($c_id){
					$sql.= " AND ( country_id = 0 OR country_id IN (".$s_ci."))";
				}
				$db->query($sql);
				if ($db->next_record()){
					do {
						if (strlen($s_si)) $s_si.= ", ";
							$s_ci = $db->f('state_id');
					} while ($db->next_record());
				} else {
					$error[$e][0] = 110;
					$error[$e][1] = $s_si;
					$e++;
				}
			} else {
				$sql = "SELECT state_id FROM ".$table_prefix."states WHERE state_id=".$s_si;
				$db->query($sql);
				if (!$db->next_record()){
					$error[$e][0] = 110;
					$error[$e][1] = $s_si;
					$e++;
				}
			}
		}

		if (count($error)){
			generate_error($error,"GetOrdersIds",$head);
		}

		$sql = get_orders($s_on,$s_ne,$s_kw, $s_sd, $s_ed, $s_os, $s_ci, $s_si, $s_cct);
		$db->query($sql);
		if ($db->next_record()){
			do{
				$orders[] = $db->f('order_id');
			} while($db->next_record());
		} else {
			include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");
			//generate_error(104,"GetOrdersIds");
			$orders = 0;
			generate_xml_orders($orders, $s_on, $s_ne, $s_kw, $s_sd, $s_ed, $s_os, $s_ci, $s_si, $s_cct, false, NO_ORDERS_MSG);
		}

		generate_xml_orders($orders, $s_on, $s_ne, $s_kw, $s_sd, $s_ed, $s_os, $s_ci, $s_si, $s_cct);

	} else if (strpos($xml,"<UpdateOrderStatus")){

		$criterions = get_text($xml,"Criterions");
		$order_id = get_text($criterions,"order_id");

		$statuses = get_text($xml,"FieldsToUpdate");
		$status_id = get_text($statuses,"order_status");

		$head = "<Criterions>\n";
		$head.= "	<order_id>".$order_id."</order_id>\n";
		$head.= "</Criterions>\n";
		$head.= "<FieldsToUpdate>\n";
		$head.= "	<order_status>".$status_id."</order_status>\n";
		$head.= "</FieldsToUpdate>\n";

		if (strlen($order_id)){
			if (intval($order_id) == 0){
				$error[$e][0] = 103;
				$error[$e][1] = "order_id";
				$e++;
			}
		} else {
			$error[$e][0] = 102;
			$error[$e][1] = "order_id";
			$e++;
		}

		if (strlen($status_id)){
			if (intval($status_id) == 0){
				$error[$e][0] = 103;
				$error[$e][1] = "order_status";
				$e++;
			}
		} else {
			$error[$e][0] = 102;
			$error[$e][1] = "order_status";
			$e++;
		}

		if (count($error)){
			generate_error($error,"UpdateOrderStatus",$head);
		}

		$sql = "SELECT order_id FROM " . $table_prefix . "orders WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
		if (!$db->next_record()){
			generate_xml_update($order_id, $status_id, "Can't find order with order_id ".screening($order_id),$head);
		}

		$sql  = " SELECT oi.order_item_id ";
		$sql .= " FROM (" . $table_prefix . "orders_items oi ";
		$sql .= " LEFT JOIN " . $table_prefix . "users mu ON oi.item_user_id=mu.user_id) ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);

		$order_items = "";
		if ($db->next_record()){
			do {
				$order_item_id = $db->f("order_item_id");
				if (strlen($order_items)) { $order_items .= ","; }
				$order_items .= $order_item_id;
			} while ($db->next_record());
		}

		include_once($root_folder_path . "includes/order_items.php");
		include_once($root_folder_path . "includes/order_links.php");
		include_once($root_folder_path . "includes/record.php");
		include_once($root_folder_path . "messages/".$language_code."/cart_messages.php");
		$t = new VA_Template($settings["admin_templates_dir"]);
		update_order_status($order_id, $status_id, true, $order_items, $status_error);
		generate_xml_update($order_id, $status_id, $status_error, $head);
	} else if (strpos($xml,"<DuplicateCoupon")) {
	
		$criterions = get_text($xml,"Criterions");
		$coupon_code = get_text($criterions,"coupon_code");//		coupon_code
		
		$FieldsToUpdate = get_text($xml,"FieldsToUpdate");
		$new_coupon_code = get_text($FieldsToUpdate,"coupon_code");
	
		$head = "<Criterions>\n";
		$head .= "	<coupon_code>".$coupon_code."</coupon_code>\n";
		$head .= "</Criterions>\n";
		$head .= "<FieldsToUpdate>\n";
		$head .= "	<coupon_code>".$new_coupon_code."</coupon_code>\n";
		$head .= "</FieldsToUpdate>\n";
		
		if (!strlen($coupon_code)) {
			$error[$e][0] = 103;
			$error[$e][1] = "Criterions->coupon_code";
			$e++;
			if (!strlen($new_coupon_code)) {
				$error[$e][0] = 103;
				$error[$e][1] = "FieldsToUpdate->coupon_code";
				$e++;
			}
		} else {
			if (!strlen($new_coupon_code)) {
				$error[$e][0] = 103;
				$error[$e][1] = "FieldsToUpdate->coupon_code";
				$e++;
			}
		}
		
		if ($e > 0) {
			generate_error($error, "DuplicateCoupon", $head);
		}
		
		$sql = " SELECT * FROM ".$table_prefix."coupons WHERE coupon_code = ".$db->tosql($new_coupon_code,TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$error[$e][0] = 122;
			$error[$e][1] = $new_coupon_code;
			$e++;
			$sql = " SELECT * FROM ".$table_prefix."coupons WHERE coupon_code = ".$db->tosql($coupon_code,TEXT);
			$db->query($sql);
			if (!$db->next_record()) {
				$error[$e][0] = 121;
				$error[$e][1] = $coupon_code;
				$e++;
			}
			generate_error($error, "DuplicateCoupon", $head);
		} else {
			$fields = $db->get_fields($table_prefix."coupons");
			//var_dump($fields);
			$sql = " SELECT * FROM ".$table_prefix."coupons WHERE coupon_code = ".$db->tosql($coupon_code,TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$cf = count($fields);
				$where = "";
				$sql1 = "";
				$sql = "INSERT INTO " . $table_prefix . "coupons ( ";
				for ($c=0; $c < $cf; $c++) {

					if (preg_match("/INT/", $fields[$c]["type"]) || preg_match("/DOUBLE/", $fields[$c]["type"])) { //  if fields is number

						if ($fields[$c]["name"] != "coupon_id") {
							if (strlen($db->f($fields[$c]["name"])) || $db->f($fields[$c]["name"]) == "0") {
								if (strlen($where)) { $where .= ", "; }
								$where .= $db->f($fields[$c]["name"]);
								if (strlen($sql1)) { $sql1 .= ", "; }
								$sql1 .= $fields[$c]["name"];
							} else {
								if (strlen($where)) { $where .= ", "; }
								$where .= "NULL";
								if (strlen($sql1)) { $sql1 .= ", "; }
								$sql1 .= $fields[$c]["name"];
							}
						}
					} else { // if fields string or other, without number
						if ($fields[$c]["name"] == "coupon_code"){
							if (strlen($where)) {$where .= ", ";}
							$where .= $db->tosql($new_coupon_code,TEXT);
							if (strlen($sql1)) {$sql1 .= ", ";}
							$sql1 .= $fields[$c]["name"];
						} else if ($db->f($fields[$c]["name"])) {
							if (strlen($where)) {$where .= ", ";}
							$where .= $db->tosql($db->f($fields[$c]["name"]),TEXT);
							if (strlen($sql1)) {$sql1 .= ", ";}
							$sql1 .= $fields[$c]["name"];
						} else {
							if (strlen($where)) { $where .= ", "; }
							$where .= "NULL";
							if (strlen($sql1)) { $sql1 .= ", "; }
							$sql1 .= $fields[$c]["name"];
						}
					}
					
				}
				$sql .= $sql1 . " ) VALUES ( " . $where . " )";
				$db->query($sql);
				$db->query("SELECT MAX(coupon_id) FROM " . $table_prefix . "coupons");
				$db->next_record();
				$coupon_id = $db->f(0);
				
				generate_xml_duplicate_coupon($new_coupon_code, $coupon_id, $head);
				
			} else {
				$error[$e][0] = 121;
				$error[$e][1] = $coupon_code;
				$e++;
				generate_error($error, "DuplicateCoupon", $head);
			}
		}
	} else if (strpos($xml,"<AddCoupon")) {
		
		$fields = $db->get_fields($table_prefix."coupons");
		$cf = count($fields);
		
		$fields_insert = get_text($xml,"Fields");
		//$coupon_code = get_text($criterions,"coupon_code");//		coupon_code
		$head = "<Fields>\n";
		for ($c=0; $c < $cf; $c++) {
			$field_insert[$fields[$c]["name"]] = get_text($xml,$fields[$c]["name"]);
			$head .= "	<".$fields[$c]["name"].">".$field_insert[$fields[$c]["name"]]."</".$fields[$c]["name"].">\n";
		}
		$head .= "</Fields>\n";
		$where = "";
		$sql1 = "";
		$sql2 = "INSERT INTO " . $table_prefix . "coupons ( ";
		for ($c=0; $c < $cf; $c++) {
			if ($fields[$c]["name"] == "coupon_id") {
				// No value;
			} else if (preg_match("/INT/", $fields[$c]["type"])) {
				if (preg_match("/^\d*$/",$field_insert[$fields[$c]["name"]]) || !strlen($field_insert[$fields[$c]["name"]])) {
					if (strlen($field_insert[$fields[$c]["name"]]) || $field_insert[$fields[$c]["name"]] == "0") {
						if (strlen($where)) { $where .= ", "; }
						$where .= $field_insert[$fields[$c]["name"]];
						if (strlen($sql1)) { $sql1 .= ", "; }
						$sql1 .= $fields[$c]["name"];
					} else {
						if (strlen($where)) { $where .= ", "; }
						$where .= "NULL";
						if (strlen($sql1)) { $sql1 .= ", "; }
						$sql1 .= $fields[$c]["name"];
					}
				} else {
					$error[$e][0] = 103;
					$error[$e][1] = $fields[$c]["name"];
					$e++;
				}
			} else if (preg_match("/DOUBLE/", $fields[$c]["type"])) {
				if (preg_match("/^\d+\.?\d*$/",$field_insert[$fields[$c]["name"]]) || !strlen($field_insert[$fields[$c]["name"]])) {
					if (strlen($field_insert[$fields[$c]["name"]]) || $field_insert[$fields[$c]["name"]] == "0") {
						if (strlen($where)) { $where .= ", "; }
						$where .= $field_insert[$fields[$c]["name"]];
						if (strlen($sql1)) { $sql1 .= ", "; }
						$sql1 .= $fields[$c]["name"];
					} else {
						if (strlen($where)) { $where .= ", "; }
						$where .= "NULL";
						if (strlen($sql1)) { $sql1 .= ", "; }
						$sql1 .= $fields[$c]["name"];
					}
				} else {
					$error[$e][0] = 103;
					$error[$e][1] = $fields[$c]["name"];
					$e++;
				}
			} else if ($fields[$c]["name"] == "expiry_date" || $fields[$c]["name"] == "start_date" || $fields[$c]["name"] == "date_added" || $fields[$c]["name"] == "date_modified") {
				$date_errors = "";
				$date_temp = parse_date($field_insert[$fields[$c]["name"]],$date_edit_format,$date_errors,$fields[$c]["name"]);
				if ($date_errors){
					$error[$e][0] = 104;
					$error[$e][1] = strip_tags($date_errors);
					$e++;
				} else {
					if ($date_temp) {
						if (strlen($where)) {$where .= ", ";}
						$where .= $db->tosql($date_temp,DATE);
						if (strlen($sql1)) {$sql1 .= ", ";}
						$sql1 .= $fields[$c]["name"];
					} else {
						if (strlen($where)) { $where .= ", "; }
						$where .= "NULL";
						if (strlen($sql1)) { $sql1 .= ", "; }
						$sql1 .= $fields[$c]["name"];
					}
				}
			} else {
				if ($fields[$c]["name"] == "coupon_code"){
					
					$sql = " SELECT * FROM ".$table_prefix."coupons WHERE coupon_code = ".$db->tosql($field_insert[$fields[$c]["name"]],TEXT);
					$db->query($sql);
					if ($db->next_record() || !strlen($field_insert[$fields[$c]["name"]])) {
						$error[$e][0] = 122;
						$error[$e][1] = $field_insert[$fields[$c]["name"]];
						$e++;
					} else {
						if (strlen($where)) {$where .= ", ";}
						$where .= $db->tosql($field_insert[$fields[$c]["name"]],TEXT);
						if (strlen($sql1)) {$sql1 .= ", ";}
						$sql1 .= $fields[$c]["name"];
					}
				} else if ($field_insert[$fields[$c]["name"]]) {
					if (strlen($where)) {$where .= ", ";}
					$where .= $db->tosql($field_insert[$fields[$c]["name"]],TEXT);
					if (strlen($sql1)) {$sql1 .= ", ";}
					$sql1 .= $fields[$c]["name"];
				} else {
					if (strlen($where)) { $where .= ", "; }
					$where .= "NULL";
					if (strlen($sql1)) { $sql1 .= ", "; }
					$sql1 .= $fields[$c]["name"];
				}
			}
		}
		
		if ($e > 0) {
			generate_error($error, "AddCoupon", $head);
		} else {
			$sql2 .= $sql1 . " ) VALUES ( " . $where . " )";
			$db->query($sql2);
			$db->query("SELECT MAX(coupon_id) FROM " . $table_prefix . "coupons");
			$db->next_record();
			$coupon_id = $db->f(0);
			//echo $coupon_id;
			generate_xml_duplicate_coupon($field_insert["coupon_code"], $coupon_id, $head);
			
		}
	}
	
	function generate_error($error, $response = "ViArtXML", $head = ""){
		global $language_code,$root_folder_path;
		$xml = "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n";
		$xml .= "<".$response."Response version=\"1.0\">\n";
		$xml .= $head;
		$xml .= "<Errors>\n";
		for ($i=0;$i<count($error);$i++){
			$fields = $error[$i][1];
			switch ($error[$i][0]){
				case 101:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>101</ErrorCode>\n";
					$xml .= "		<ErrorMessage>".screening(LOGIN_PASSWORD_ERROR)."</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 102:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>102</ErrorCode>\n";
					$error1 = REQUIRED_MESSAGE;
					$error1 = str_replace ("{field_name}",$fields,$error1);
					$error1 = strip_tags($error1);
					$xml .= "		<ErrorMessage>".screening($error1)."</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 103:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>103</ErrorCode>\n";
					$error1 = INCORRECT_VALUE_MESSAGE;
					$error1 = str_replace ("{field_name}",$fields,$error1);
					$error1 = strip_tags($error1);
					$xml .= "		<ErrorMessage>".screening($error1)."</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 104:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>104</ErrorCode>\n";
					$xml .= "		<ErrorMessage>".screening($fields)."</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 105:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>105</ErrorCode>\n";
					$xml .= "		<ErrorMessage>ViArt SHOP not installed</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 106:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>106</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Language code - '".screening($fields)."' is not supported by the system.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 107:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>107</ErrorCode>\n";
					$xml .= "		<ErrorMessage>The system doesn't support the submitted request</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 108:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>108</ErrorCode>\n";
					$xml .= "		<ErrorMessage>You don't have permissions to submit a request '".screening($fields)."'</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 109:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>109</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Unable to find the country with the '".screening($fields)."' name.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 110:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>110</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Unable to find the State with the '".screening($fields)."' name.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 111:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>111</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Can't find the status with ID: '".screening($fields)."'.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 121:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>121</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Can't find the coupon with code: '".screening($fields)."'.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 122:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>122</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Coupon with code '".screening($fields)."' isset in DataBase.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
			}
		}
		$xml .= "</Errors>\n";
		$xml .= "</".$response."Response>\n";
		echo $xml;
		exit;
	}
	
	
	
	
	
	
	function generate_xml_status($name,$array){
		$xml = "<?xml version=\"1.0\" encoding=\"".CHARSET."\" ?>\n";
		$xml .= "<OrdersStatusesResponse version=\"1.0\">\n";
		$xml .= "<Criterions>\n";
		$xml .= "</Criterions>\n";
		$xml .= "<statuses>\n";
		for($i=0;$i<count($array);$i++) {
			$xml .= "	<status>\n";
			$xml .= "		<status_id>".$array[$i]["status_id"]."</status_id>\n";
			$xml .= "		<status_name>".screening($array[$i]["status_name"])."</status_name>\n";
			$xml .= "	</status>\n";
		}
		$xml .= "</statuses>\n";
		$xml .= "</OrdersStatusesResponse>\n";

		echo $xml;
		exit;
	}

	function generate_xml_status_all($id,$array){
		$xml = "<?xml version=\"1.0\" encoding=\"".CHARSET."\" ?>\n";
		$xml .= "<OrderStatusDetailsResponse version=\"1.0\">\n";
		$xml .= "<Criterions>\n";
		$xml .= "<status_id>".$id."</status_id>\n";
		$xml .= "</Criterions>\n";
		$xml .= "<status>\n";
		foreach( $array as $index => $arr) {
			foreach( $arr as $key => $val) {
				$xml .= "	<".$key.">".screening($val)."</".$key.">\n";
			}
		}
		$xml .= "</status>\n";
		$xml .= "</OrderStatusDetailsResponse>\n";
		echo $xml;
		exit;
	}

	function generate_xml_orders($array, $s_on, $s_ne, $s_kw, $s_sd, $s_ed, $s_os, $s_ci, $s_si, $s_cct, $orders = true, $message = ''){
		global $db;
		$xml = "<?xml version=\"1.0\" encoding=\"".CHARSET."\" ?>\n";
		$xml .= "<OrdersIdsResponse version=\"1.0\">\n";
		$xml .= "<Criterions>\n";
		$xml .= "	<OrderNumber>".screening($s_on)."</OrderNumber>\n";
		$xml .= "	<NameEmail>".screening($s_ne)."</NameEmail>\n";
		$xml .= "	<Keyword>".screening($s_kw)."</Keyword>\n";
		if (is_array($s_sd)){
			$xml .= "	<StartDate>".screening(str_replace("'","",$db->tosql($s_sd,DATE)))."</StartDate>\n";
		} else {
			$xml .= "	<StartDate>".screening($s_sd)."</StartDate>\n";
		}
		if (is_array($s_ed)){
			$xml .= "	<EndDate>".screening(str_replace("'","",$db->tosql($s_ed,DATE)))."</EndDate>\n";
		} else {
			$xml .= "	<StartDate>".screening($s_ed)."</StartDate>\n";
		}
		$xml .= "	<OrderStatus>".screening($s_os)."</OrderStatus>\n";
		$xml .= "	<ShipToCountry>".screening($s_ci)."</ShipToCountry>\n";
		$xml .= "	<ShipToState>".screening($s_si)."</ShipToState>\n";
		$xml .= "	<CreditCardType>".screening($s_cct)."</CreditCardType>\n";
		$xml .= "</Criterions>\n";
		if ($orders){
			$xml .= "<orders>\n";
			for($i=0;$i<count($array);$i++) {
				$xml .= "	<order>\n";
				$xml .= "		<order_id>".$array[$i]."</order_id>\n";
				$xml .= "	</order>\n";
			}
			$xml .= "</orders>\n";
		} else {
			$xml .= "<Messages>\n";
			$xml .= "	<message>".screening($message)."</message>\n";
			$xml .= "</Messages>\n";
		}
		$xml .= "</OrdersIdsResponse>\n";

		echo $xml;
		exit;
	}

	function generate_xml_update($order_id, $status_id, $status_error, $head){

		$xml = "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n";
		$xml .= "<UpdateOrderStatusResponse version=\"1.0\">\n";
		$xml .= $head;
		if (strlen($status_error)){
			$xml .= "<Errors>\n";
			$xml .= "	<ErrorMessage>".screening($status_error)."</ErrorMessage>\n";
			$xml .= "</Errors>\n";
		} else {
			$xml .= "<OrdersUpdated>\n";
			$xml .= "	<Order>\n";
			$xml .= "	<order_id>".$order_id."</order_id>\n";
			$xml .= "	</Order>\n";
			$xml .= "</OrdersUpdated>\n";
		}
		$xml .= "</UpdateOrderStatusResponse>\n";
		echo $xml;
		exit;
	}
	
	function generate_xml_duplicate_coupon($new_coupon_code, $coupon_id, $head) {
		$xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
		$xml .= "<DuplicateCouponResponse version=\"1.0\">\n";
		$xml .= $head;
		$xml .= "<coupons>\n";
		$xml .= "	<coupon>\n";
		$xml .= "		<coupon_id>".$coupon_id."</coupon_id>\n";
		$xml .= "		<coupon_code>".$new_coupon_code."</coupon_code>\n";
		$xml .= "	</coupon>\n";
		$xml .= "</coupons>\n";
		$xml .= "</DuplicateCouponResponse>\n";
		echo $xml;
		exit;
	}

	function get_text($text_all,$field){
		if (strpos($text_all,"<".$field.">")){
			$text = substr($text_all, strpos($text_all, "<".$field.">")+strlen("<".$field.">"), strpos($text_all, "</".$field.">")-strlen("<".$field.">")-strpos($text_all, "<".$field.">"));
			return $text;
		} else {
			return "";
		}
	}

	/*function generate_error($error, $response = "ViArtXML", $head = ""){
		global $language_code,$root_folder_path;
		$xml = "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n";
		$xml .= "<".$response."Response version=\"1.0\">\n";
		$xml .= $head;
		$xml .= "<Errors>\n";
		for ($i=0;$i<count($error);$i++){
			$fields = $error[$i][1];
			switch ($error[$i][0]){
				case 101:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>101</ErrorCode>\n";
					$xml .= "		<ErrorMessage>".screening(LOGIN_PASSWORD_ERROR)."</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 102:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>102</ErrorCode>\n";
					$error1 = REQUIRED_MESSAGE;
					$error1 = str_replace ("{field_name}",$fields,$error1);
					$error1 = strip_tags($error1);
					$xml .= "		<ErrorMessage>".screening($error1)."</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 103:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>103</ErrorCode>\n";
					$error1 = INCORRECT_VALUE_MESSAGE;
					$error1 = str_replace ("{field_name}",$fields,$error1);
					$error1 = strip_tags($error1);
					$xml .= "		<ErrorMessage>".screening($error1)."</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 104:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>104</ErrorCode>\n";
					$xml .= "		<ErrorMessage>".screening($fields)."</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 105:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>105</ErrorCode>\n";
					$xml .= "		<ErrorMessage>ViArt SHOP not installed</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 106:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>106</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Language code - '".screening($fields)."' is not supported by the system.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 107:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>107</ErrorCode>\n";
					$xml .= "		<ErrorMessage>The system doesn't support the submitted request</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 108:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>108</ErrorCode>\n";
					$xml .= "		<ErrorMessage>You don't have permissions to submit a request '".screening($fields)."'</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 109:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>109</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Unable to find the country with the '".screening($fields)."' name.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 110:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>110</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Unable to find the State with the '".screening($fields)."' name.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
				case 111:
					$xml .= "	<Error>\n"; $xml .= "		<ErrorCode>111</ErrorCode>\n";
					$xml .= "		<ErrorMessage>Can't find the status with ID: '".screening($fields)."'.</ErrorMessage>\n";$xml .= "	</Error>\n";
				break;
			}
		}
		$xml .= "</Errors>\n";
		$xml .= "</".$response."Response>\n";
		echo $xml;
		exit;
	}*/

	function check_adm_security($block_name)
	{
		global $db, $table_prefix, $pass, $login, $settings, $error, $e;

		$allow_access = true;

		if (strlen($login) && strlen($pass)) {
			$password_encrypt = get_setting_value($settings, "password_encrypt", 0);
			$admin_password_encrypt = get_setting_value($settings, "admin_password_encrypt", $password_encrypt);
			if ($admin_password_encrypt == 1) {
				$password_match = md5($pass);
			} else {
				$password_match = $pass;
			}

			$sql  = " SELECT * FROM " . $table_prefix . "admins WHERE ";
			$sql .= " login=" . $db->tosql($login, TEXT);
			$sql .= " AND password=" . $db->tosql($password_match, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$admin_id = $db->f("admin_id");
				$privilege_id = $db->f("privilege_id");
				set_session("session_admin_id", $admin_id);
			} else {
				$error[$e][0] = 101;
				$error[$e][1] = "";
				$e++;
				generate_error($error);
				exit;
			}
		} else {
			$error[$e][0] = 101;
			$error[$e][1] = "";
			$e++;
			generate_error($error);
		}

		if (strlen($block_name)) {
			$sql  = " SELECT permission FROM " . $table_prefix . "admin_privileges_settings ";
			$sql .= " WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER);
			$sql .= " AND block_name=" . $db->tosql($block_name, TEXT);
			$allow_access = get_db_value($sql) ? true : false;
		} else {
			$error[$e][0] = 101;
			$error[$e][1] = "";
			$e++;
			generate_error($error);
			exit;
		}

		return $allow_access;
	}

	function get_orders($s_on,$s_ne,$s_kw, $s_sd, $s_ed, $s_os, $s_ci, $s_si, $s_cct){
		global $db, $table_prefix, $order_info;

		//$t = $db->get_fields($table_prefix."orders");
		$name = "order";
		$where = "";
		$sql  = " SELECT DISTINCT o.order_id order_id FROM ((((" . $table_prefix . "orders o ";
		$sql .= " INNER JOIN " . $table_prefix . "orders_items oi ON o.order_id=oi.order_id)";
		$sql .= " LEFT JOIN " . $table_prefix . "orders_items_serials ois ON o.order_id=ois.order_id)";
		$sql .= " LEFT JOIN " . $table_prefix . "orders_serials_activations osa ON o.order_id=osa.order_id)";
		$sql .= " LEFT JOIN " . $table_prefix . "order_statuses os ON o.order_status=os.status_id) ";

		if(strlen($s_on)) {
			if (preg_match("/^(\d+)(,\d+)*$/", $s_on))	{
				$where  = " (o.order_id IN (" . $s_on . ") ";
				$where .= " OR o.invoice_number=" . $db->tosql($s_on, TEXT);
				$where .= " OR o.transaction_id=" . $db->tosql($s_on, TEXT) . ") ";
			} else {
				$where .= " (o.invoice_number=" . $db->tosql($s_on, TEXT);
				$where .= " o.transaction_id=" . $db->tosql($s_on, TEXT) . ") ";
			}
		}

		if(strlen($s_ne)) {
			if (strlen($where)) { $where .= " AND "; }
			$s_ne = $db->tosql($s_ne, TEXT, false);
			$where .= " (o.email LIKE '%" . $s_ne . "%'";
			$where .= " OR o.name LIKE '%" . $s_ne . "%'";
			$where .= " OR o.first_name LIKE '%" . $s_ne . "%'";
			$where .= " OR o.last_name LIKE '%" . $s_ne . "%')";
		}

		if(strlen($s_kw)) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " (oi.item_name LIKE '%" . $db->tosql($s_kw, TEXT, false) . "%'";
			$where .= " OR oi.item_properties LIKE '%" . $db->tosql($s_kw, TEXT, false) . "%'";
			$where .= " OR ois.serial_number=" . $db->tosql($s_kw, TEXT);
			$where .= " OR osa.generation_key=" . $db->tosql($s_kw, TEXT);
			$where .= " OR osa.activation_key=" . $db->tosql($s_kw, TEXT);
			$where .= " OR o.shipping_type_desc LIKE '%" . $db->tosql($s_kw, TEXT, false) . "%')";
		}

		if(is_array($s_sd)) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_placed_date>=" . $db->tosql($s_sd, DATE);
		}

		if(is_array($s_ed)) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_placed_date<" . $db->tosql($s_ed, DATE);
		}

		if(strlen($s_os)) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.order_status=" . $db->tosql($s_os, INTEGER);
		} else {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " (os.is_list=1 OR os.is_list IS NULL) ";
		}

		if(strlen($s_ci)) {
			if ($order_info["show_delivery_country_id"] == 1) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " o.delivery_country_id IN (".$s_ci.") ";
			} else if ($order_info["show_country_id"] == 1) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " o.country_id IN (".$s_ci.") ";
			} 
		}

		if(strlen($s_si)) {
			if ($order_info["show_delivery_state_id"] == 1) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " o.delivery_state_id IN (".$s_si.") ";
			} else if ($order_info["show_state_id"] == 1) {
				if (strlen($where)) { $where .= " AND "; }
				$where .= " o.state_id IN (".$s_si.") ";
			} 
		}

		if (strlen($s_cct)) {
			if (strlen($where)) { $where .= " AND "; }
			$where .= " o.cc_type=" . $db->tosql($s_cct, INTEGER);
		}

		if (strlen($where)){
			$where_sql = "WHERE ".$where;
		}
		$sql .= $where_sql;
		return $sql;
	}

	function screening($text){
		$text = str_replace("&", "&amp;", $text); 
		$text = str_replace("<", "&lt;", $text); 
		$text = str_replace(">", "&gt;", $text); 
		//$text = str_replace(" ", "&apos;", $text); 
		$text = str_replace("\"", "&quot;", $text);
		return $text;
	}
	
	/*$fields_coupons[] = "coupon_id";
		$fields_coupons[] = "order_id";
		$fields_coupons[] = "order_item_id";
		$fields_coupons[] = "coupon_code";
		$fields_coupons[] = "coupon_title";
		$fields_coupons[] = "is_active";
		$fields_coupons[] = "discount_type";
		$fields_coupons[] = "discount_amount";
		$fields_coupons[] = "discount_tax_free";
		$fields_coupons[] = "free_postage";
		$fields_coupons[] = "coupon_tax_free";
		$fields_coupons[] = "order_tax_free";
		$fields_coupons[] = "items_all";
		$fields_coupons[] = "items_ids";
		$fields_coupons[] = "users_ids";
		$fields_coupons[] = "minimum_amount";
		$fields_coupons[] = "expiry_date";
		$fields_coupons[] = "is_exclusive";
		$fields_coupons[] = "quantity_limit";
		$fields_coupons[] = "coupon_uses";
		$fields_coupons[] = "is_auto_apply";
		$fields_coupons[] = "cart_items_all";
		$fields_coupons[] = "cart_items_ids";
		$fields_coupons[] = "min_quantity";
		$fields_coupons[] = "max_quantity";
		$fields_coupons[] = "discount_quantity";
		$fields_coupons[] = "maximum_amount";
		$fields_coupons[] = "sites_all";
		$fields_coupons[] = "users_all";
		$fields_coupons[] = "users_use_limit";
		$fields_coupons[] = "orders_period";
		$fields_coupons[] = "orders_interval";
		$fields_coupons[] = "orders_min_goods";
		$fields_coupons[] = "orders_max_goods";
		$fields_coupons[] = "friends_discount_type";
		$fields_coupons[] = "friends_all";
		$fields_coupons[] = "friends_ids";
		$fields_coupons[] = "friends_period";
		$fields_coupons[] = "friends_interval";
		$fields_coupons[] = "friends_min_goods";
		$fields_coupons[] = "friends_max_goods";
		$fields_coupons[] = "start_date";
		$fields_coupons[] = "admin_id_added_by";
		$fields_coupons[] = "admin_id_modified_by";
		$fields_coupons[] = "date_added";
		$fields_coupons[] = "date_modified";
		$fields_coupons[] = "apply_order";
		$fields_coupons[] = "items_types_ids";
		$fields_coupons[] = "cart_items_types_ids";
		$fields_coupons[] = "users_types_ids";
		$fields_coupons[] = "friends_types_ids";*/
	
	/*
	
	<?xml version="1.0" encoding="iso-8859-1"?>
<AddCoupon>
<Credentials>
<Username>admin</Username>
<Password>admin</Password>
</Credentials>
<Fields>
<coupon_id>1</coupon_id>
<order_id>0</order_id>
<order_item_id>0</order_item_id>
<coupon_code>�1</coupon_code>
<coupon_title>Coupon All</coupon_title>
<is_active>1</is_active>
<discount_type>1</discount_type>
<discount_amount>10.00</discount_amount>
<discount_tax_free>0</discount_tax_free>
<free_postage>0</free_postage>
<coupon_tax_free>0</coupon_tax_free>
<order_tax_free>0</order_tax_free>
<items_all>0</items_all>
<items_ids></items_ids>
<users_ids></users_ids>
<minimum_amount></minimum_amount>
<expiry_date>2009-03-05 00:00:00</expiry_date>
<is_exclusive>0</is_exclusive>
<quantity_limit>11</quantity_limit>
<coupon_uses>4</coupon_uses>
<is_auto_apply>0</is_auto_apply>
<cart_items_all>0</cart_items_all>
<cart_items_ids></cart_items_ids>
<min_quantity></min_quantity>
<max_quantity></max_quantity>
<discount_quantity></discount_quantity>
<maximum_amount></maximum_amount>
<sites_all>1</sites_all>
<users_all>1</users_all>
<users_use_limit></users_use_limit>
<orders_period></orders_period>
<orders_interval></orders_interval>
<orders_min_goods></orders_min_goods>
<orders_max_goods></orders_max_goods>
<friends_discount_type>0</friends_discount_type>
<friends_all>1</friends_all>
<friends_ids></friends_ids>
<friends_period></friends_period>
<friends_interval></friends_interval>
<friends_min_goods></friends_min_goods>
<friends_max_goods></friends_max_goods>
<start_date></start_date>
<admin_id_added_by>0</admin_id_added_by>
<admin_id_modified_by>0</admin_id_modified_by>
<date_added></date_added>
<date_modified></date_modified>
<apply_order>1</apply_order>
<items_types_ids></items_types_ids>
<cart_items_types_ids></cart_items_types_ids>
<users_types_ids></users_types_ids>
<friends_types_ids></friends_types_ids>
</Fields>
</AddCoupon>
	
	
	*/
	
?>