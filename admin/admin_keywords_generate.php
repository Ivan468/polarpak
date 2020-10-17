<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_keywords_generate.php                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit(900);

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/keywords_functions.php");

	check_admin_security("update_products");

	// connection to get product items
	$dbi = new VA_SQL();
	$dbi->DBType      = $db_type;
	$dbi->DBDatabase  = $db_name;
	$dbi->DBUser      = $db_user;
	$dbi->DBPassword  = $db_password;
	$dbi->DBHost      = $db_host;
	$dbi->DBPort      = $db_port;
	$dbi->DBPersistent= $db_persistent;
	$operation = get_param("operation");
	if (!$operation) { $operation = "generate"; }
	if ($operation == "clear"){
		// clear keywords
		$sql = "DELETE from ".$table_prefix."keywords_items ";
		$db->query($sql);
		$sql = "UPDATE ".$table_prefix."items SET is_keywords=0 ";
		$db->query($sql);
	} else if ($operation == "generate") {

		// generate keywords for products
		$sql  = " SELECT * FROM " . $table_prefix . "items ";
		$sql .= " WHERE is_keywords=0 OR is_keywords IS NULL ";

		$dbi->RecordsPerPage = 10;
		$dbi->PageNumber = 1;              
		$dbi->query($sql);
		while ($dbi->next_record()) {
			$item_id = $dbi->f("item_id");
			generate_keywords($dbi->Record);
		}
	}

	// prepare response
	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "items ";
	$total_products = get_db_value($sql);

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "items WHERE is_keywords=1 ";
	$indexed_products = get_db_value($sql);

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "keywords_items ";
	$indexed_keywords = get_db_value($sql);

	$response  = "total_products=".$total_products;
	$response .= "#indexed_products=".$indexed_products;
	$response .= "#indexed_keywords=".$indexed_keywords;
	echo $response;

?>