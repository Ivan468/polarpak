<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_table_items_files.php                              ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$table_name = $table_prefix . "items_files";
	$table_alias = "i";
	$table_pk = "file_id";
	$table_title = DOWNLOADABLE_FILES_MSG;
	$min_column_allowed = 1;

	$db_columns = array(
		"file_id"           => array(ID_MSG, INTEGER, 1, false),
		"item_id"           => array(PRODUCT_ID_MSG, INTEGER, 3, false),
		"item_type_id"      => array(PROD_TYPE_MSG, INTEGER, 3, true, 1),
		"download_type"     => array(DOWNLOAD_TYPE_MSG, INTEGER, 2, false, 0),
		"download_title"    => array(DOWNLOAD_TITLE_MSG, TEXT, 2, false),
		"download_path"     => array(DOWNLOAD_PATH_MSG, TEXT, 2, false),
		"download_period"   => array(DOWNLOAD_PERIOD_MSG, INTEGER, 2, false, 0),
		"download_interval" => array(DOWNLOAD_INTERVAL_MSG, INTEGER, 2, false, 0),
		"download_limit"    => array(DOWNLOAD_LIMIT_MSG, INTEGER, 2, false, 0),
		"preview_type"      => array(PREVIEW_TYPE_MSG, INTEGER, 2, false, 0),
		"preview_title"     => array(PREVIEW_TITLE_MSG, TEXT, 2, false),
		"preview_path"      => array(PREVIEW_PATH_MSG, TEXT, 2, false),
		"preview_image"     => array(PREVIEW_IMAGE_MSG, TEXT, 2, false),
		"preview_position"  => array(PREVIEW_POSITION_MSG, INTEGER, 2, false, 0),
	);
?>