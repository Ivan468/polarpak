<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_file_transfers.php                                 ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");

	check_admin_security("filemanager");

	$operation  = get_param("operation");
	$ids = get_param("ids");
	if ($operation == "remove" && strlen($ids)) {
		$sql  = " DELETE FROM " . $table_prefix . "file_transfers ";
		$sql .= " WHERE transfer_id IN (" . $db->tosql($ids, INTEGERS_LIST) . ")";
		$db->query($sql);
	}

	$param_rnd = get_param("rnd");
	$session_rnd = get_session("session_rnd");
	$rnd = mt_rand();

	// transfer files
	$error_message = ""; $success_message = "";
	if ($operation == "transfer" && $param_rnd && $param_rnd == $session_rnd) {
		include_once("./cron_file_transfers.php");
	}
	set_session("session_rnd", $rnd);


  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_file_transfers.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_file_transfer_href", "admin_file_transfer.php");
	$t->set_var("admin_file_transfers_href", "admin_file_transfers.php");
	$t->set_var("rnd", htmlspecialchars($rnd));

	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_file_transfers.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_id", 1, "transfer_id", "", "", true);
	$s->set_sorter(TYPE_MSG, "sorter_type", 2, "transfer_type");
	$s->set_sorter(FILE_PATH_MSG, "sorter_file_path", 3, "file_path");
	$s->set_sorter(STATUS_MSG, "sorter_status", 4, "transfer_status");
	$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_file_transfers.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	// set up variables for navigator
	$db->query("SELECT COUNT(*) FROM " . $table_prefix . "file_transfers");
	$db->next_record();
	$total_records = $db->f(0);
	$records_per_page = get_param("q") > 0 ? get_param("q") : 25;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", SIMPLE, $pages_number, $records_per_page, $total_records, false);


	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query("SELECT * FROM " . $table_prefix . "file_transfers" . $s->order_by);
	if($db->next_record())
	{
		$index = 0;
		$transfer_types = array(
			"1" => FTP_UPLOAD_MSG,
			"2" => FTP_DOWNLOAD_MSG,
		);
		$t->parse("sorters", "");
		$t->set_var("no_records", "");
		do {
			$index++;
			$row_style = ($index % 2 == 0) ? "row1" : "row2";
			$transfer_type_id = $db->f("transfer_type");
			$transfer_type = isset($transfer_types[$transfer_type_id]) ? $transfer_types[$transfer_type_id] : $transfer_type_id;
			$t->set_var("index", $index);
			$t->set_var("row_style", $row_style);
			$t->set_var("transfer_id", $db->f("transfer_id"));
			$t->set_var("transfer_type", $transfer_type);
			$t->set_var("file_path", htmlspecialchars($db->f("file_path")));
			$t->set_var("transfer_status", htmlspecialchars($db->f("transfer_status")));
			$t->set_var("ftp_host", htmlspecialchars($db->f("ftp_host")));
			$t->parse("records", true);
		} while($db->next_record());

		$t->set_var("records_number", $index);
	} else {
		$t->set_var("records", "");
		$t->set_var("navigator", "");
		$t->parse("no_records", false);
	}

	if ($error_message) {
		$t->set_var("errors_list", $error_message);
		$t->sparse("errors", false);
	} 

	if ($success_message) {
		$t->set_var("success_message", $success_message);
		$t->sparse("success", false);
	} 


	$t->pparse("main");

?>