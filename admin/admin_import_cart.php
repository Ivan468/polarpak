<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_import_cart.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ("./admin_common.php");
	include_once ($root_folder_path . "includes/record.php");
	include_once ($root_folder_path . "messages/".$language_code."/download_messages.php");
	include_once ($root_folder_path . "messages/".$language_code."/install_messages.php");

	check_admin_security();

	$eol = get_eol();
	$operation = get_param("operation");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_import_cart.html");
	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_import_cart_href", "admin_import_cart.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$r = new VA_Record("");
	$privileges = array(
		array("", ""),
		array("OSCOMMERCE", "osCommerce"),
		array("XCART", "X-Cart"),
		array("ZENCART", "Zen Cart"),
	);
	$r->add_select("cart_type", TEXT, $privileges, "Shopping Cart Type");
	$r->change_property("cart_type", REQUIRED, true);

	$r->add_textbox("db_host", TEXT, DB_HOST_FIELD);
	$r->change_property("db_host", REQUIRED, true);
	$r->change_property("db_host", DEFAULT_VALUE, "localhost");
	$r->add_textbox("db_port", TEXT, DB_PORT_FIELD);
	$r->add_textbox("db_name", TEXT, DB_NAME_FIELD);
	$r->change_property("db_name", REQUIRED, true);
	$r->add_textbox("db_user", TEXT, DB_USER_FIELD);
	$r->change_property("db_user", REQUIRED, true);
	$r->add_textbox("db_password", TEXT, DB_PASS_FIELD);
	$r->add_textbox("cart_path", TEXT, "Cart Directory");
	$r->change_property("cart_path", REQUIRED, true);
	$r->add_checkbox("copy_images", INTEGER);

	
	if ($operation == "import") {
		$r->get_form_parameters();
		$r->validate();
		if (!$r->errors) {
			// import data from MySQL database
			$dblibrary = "mysql";
			$dbtype = "mysql";
			$copy_images = $r->get_value("copy_images");
			$cart_path = $r->get_value("cart_path");
			include_once("../includes/db_$dblibrary.php");

			$dbi = new VA_SQL();
			$dbi->DBType     = $dbtype;
			$dbi->DBDatabase = $r->get_value("db_name");
			$dbi->DBHost     = $r->get_value("db_host");
			$dbi->DBPort     = $r->get_value("db_port");
			$dbi->DBUser     = $r->get_value("db_user");
			$dbi->DBPassword = $r->get_value("db_password");
			$dbi->DBPersistent = true;
			$dbi->HaltOnError = "yes";
			
			$dbi2 = new VA_SQL();
			$dbi2->DBType     = $dbtype;
			$dbi2->DBDatabase = $r->get_value("db_name");
			$dbi2->DBHost     = $r->get_value("db_host");
			$dbi2->DBPort     = $r->get_value("db_port");
			$dbi2->DBUser     = $r->get_value("db_user");
			$dbi2->DBPassword = $r->get_value("db_password");
			$dbi2->DBPersistent = true;
			$dbi2->HaltOnError = "yes";
				
			// check connect to shopping cart database
			if (!$dbi->db_connect(true)) { // always connect as a new link
				if (strlen($dbi->Error)) {
					$r->errors = $dbi->Error . "<br>";
				} else {
					$r->errors = DB_CONNECT_ERROR . "<br>";
				}
			}
		}
		
		//echo "here"; exit;
		
		if (!$r->errors) {
		//	echo "here"; exit;
			@set_time_limit(600);
			$t->parse("import_results", false);
			$t->pparse("main");
			// start upgrading process
			echo "<script language=\"JavaScript\" type=\"text/javascript\">".$eol."<!--".$eol."importProcess();".$eol."//-->".$eol."</script>".$eol;
			flush();
			$cart_type = $r->get_value("cart_type");
			if ($cart_type == "OSCOMMERCE") {
				//echo "here"; exit;
				include_once("./admin_import_oscommerce.php");
			} else if ($cart_type == "XCART") {
				include_once("./admin_import_xcart.php");
			} else if ($cart_type == "ZENCART") {
				include_once("./admin_import_zencart.php");
			}
			echo "<script language=\"JavaScript\" type=\"text/javascript\">".$eol."<!--".$eol."cartImported();".$eol."//-->".$eol."</script>".$eol;
			flush();

			$t->pparse("page_end", false);
			return;
		}

	} else if (!$operation) {
		$r->set_default_values();
	}

	$r->set_form_parameters();
	$t->parse("import_form", false);

	$t->parse("page_end", false);
	$t->pparse("main");

	function importing_data($data_name, $imported_records, $total_records, $final = false) 
	{
		global $eol;
		$data_name = str_replace("'", "\\'", $data_name);
		echo $eol."<script language=\"JavaScript\">".$eol."<!--";
		echo $eol."importingData('".$data_name."',".intval($imported_records).",".intval($total_records).");";
		echo $eol."//-->".$eol."</script>";
		flush();
	}

?>