<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_email_tags_help.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "messages/" . $language_code . "/cart_messages.php");
	include_once($root_folder_path . "messages/" . $language_code . "/reviews_messages.php");
	include_once("./admin_common.php");

	check_admin_security("sales_orders");

	$type = get_param("type");
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_email_tags_help.html");
	$t->show_tags = true;

	// clear all blocks
	$t->set_var("review_product_tags", "");
	$t->set_var("review_tags", "");
	$t->set_var("review_comment_tags", "");
	$t->set_var("question_tags", "");
	$t->set_var("question_reply_tags", "");
	
	if ($type == "review") {
		$t->sparse("review_product_tags", false);
		$t->sparse("review_tags", false);
	} else if ($type == "question") {
		$t->sparse("review_product_tags", false);
		$t->sparse("question_tags", false);
	} else if ($type == "comment") {
		$t->sparse("review_product_tags", false);
		$t->sparse("review_tags", false);
		$t->sparse("review_comment_tags", false);
	} else if ($type == "reply") {
		$t->sparse("review_product_tags", false);
		$t->sparse("question_tags", false);
		$t->sparse("question_reply_tags", false);
	}

	$t->pparse("main");

