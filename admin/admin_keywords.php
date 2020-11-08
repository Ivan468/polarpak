<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_keywords.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	@set_time_limit(900);

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/install_messages.php");

	check_admin_security("update_products");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_keywords.html");
	$t->set_var("admin_keywords_href", "admin_keywords.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "items ";
	$total_products = get_db_value($sql);

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "items WHERE is_keywords=1 ";
	$indexed_products = get_db_value($sql);

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "keywords_items ";
	$indexed_keywords = get_db_value($sql);

	$t->set_var("total_products", $total_products);
	$t->set_var("indexed_products", $indexed_products);
	$t->set_var("indexed_keywords", $indexed_keywords);

	$t->pparse("main");

?>