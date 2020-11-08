<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_messages.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once ($root_folder_path . "includes/sorter.php");
	include_once ($root_folder_path . "includes/navigator.php");
	include_once ($root_folder_path . "includes/record.php");

	include_once("./admin_common.php");

	check_admin_security("static_messages");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_messages.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_messages_href", "admin_messages.php");
	$t->set_var("admin_message_href", "admin_message.php");

	include_once("./admin_header.php");
	include_once("./admin_footer.php");


	$messages_per_page = 1000;
	$navigator_pages = 10;
	$page = intval(get_param("page"));
	$messages_passing = 0;
	if ($page > 1) {
		$messages_passing = ($page - 1) * $messages_per_page;  	
	}

	$section = get_param("section");
	$section = preg_replace("/[^0-9a-z_\-\.]/i", "", $section);

	$language_code = get_language();
	$sw = get_param("sw");

	$messages_url = new VA_URL("admin_messages.php", false);
	$messages_url->add_parameter("sw", CONSTANT, $sw);
	$messages_url->add_parameter("lang", CONSTANT, $language_code);
	$messages_url->add_parameter("page", CONSTANT, $page);

	$section_url= new VA_URL("admin_messages_export", false);
	$section_url->add_parameter("lang", CONSTANT, $language_code);

	// getting languages list
	$sql = " SELECT language_code, language_name, language_image FROM " . $table_prefix . "languages WHERE show_for_user=1 ";
	$db->query($sql);
	while ($db->next_record()) {
		$row_language_code = $db->f("language_code");
		$row_language_name = $db->f("language_name");
		$language_image = $db->f("language_image");
		$t->set_var("language_code", $row_language_code);
		$t->set_var("language_name", $row_language_name);	

		if ($language_image) {
			if ($section){
				$language_href = "?section=" . $section ."&";
			} else {
				$language_href = "?";
			}
			if (file_exists($root_folder_path . $language_image)) {
        $src = $root_folder_path . htmlspecialchars($language_image);
	      $t->set_var("language_image", "<img border='0' src='$src' alt='$row_language_name' title='$row_language_name'>");
			}	else {
				$t->set_var("language_image", "<b>$row_language_code</b>" );
			}	      
			$language_href .= "lang=" . $row_language_code; 
			$t->set_var("language_href", $language_href);
	 
			$t->parse("languages_images", true);
		}
	}

	// get current language 
	$sql = " SELECT language_name, language_image FROM " . $table_prefix . "languages ";
	$sql.= " WHERE language_code=" . $db->tosql($language_code, TEXT);
	$db->query($sql);
	if ($db->next_record()) {
	  $active_language_name = $db->f("language_name");
		$active_language_image = $db->f("language_image");
		$t->set_var("current_language", $active_language_name);
		$t->set_var("current_language_code", $language_code);      
		$t->set_var("active_language", $active_language_name);
		$t->set_var("active_language_code", $language_code);      

		if (file_exists($root_folder_path . $active_language_image)) {
  		$image_size = preg_match("/^http\:\/\//", $active_language_image) ? "" : @GetImageSize($root_folder_path.$active_language_image);
      $src = $root_folder_path . htmlspecialchars($active_language_image);
			if (is_array($image_size)) {
        $image_width = "width=\"" . $image_size[0] . "\"";
	      $image_height = "height=\"" . $image_size[1] . "\"";
			} else {
        $image_width = "";
	      $image_height = "";
			}
      $t->set_var("current_language_image", "<img border='0' src='$src' $image_width $image_height alt='$active_language_image'>");
		}	else {
			$t->set_var("current_language_image", "<b>$row_language_code</b>" );
		}
	} 
	$language_messages = str_replace("{language_name}", $active_language_name, va_message("LANGUAGE_MESSAGES_MSG"));
	$t->set_var("language_messages", htmlspecialchars($language_messages));

	// getting files list
	$message_dir = "../messages/".$language_code;
	if (is_dir($message_dir) && ($handle = opendir($message_dir))) {
		$total_messages_found = 0; $messages_shown = 0; 
	  while (($file = readdir($handle)) !== false) { 
			if (is_file($message_dir."/".$file) && preg_match("/\.php$/i", $file)) {
				$file_writable = is_writable($message_dir."/".$file);
				$file_section = str_replace(".php", "", $file);
				// show all available message files
				if ($file_writable) {
					$t->set_var("is_readonly", "");
				} else {
					$t->set_var("is_readonly", "&nbsp;<font style='font-size:8pt; color:red;'>(Readonly)</font>");
				}
				$t->set_var("section_title", $file_section);
				$t->set_var("language_filename", $file);
				$t->set_var("file_section", htmlspecialchars($file_section));
				$t->set_var("section_name", htmlspecialchars($file_section));

				$section_url->add_parameter("section", CONSTANT, $file_section);
				$t->set_var("section_url", $section_url->get_url("admin_messages.php"));
				$t->set_var("export_url", $section_url->get_url("admin_messages_export.php"));
				$t->set_var("import_url", $section_url->get_url("admin_messages_import.php"));

				$t->parse("sections",true);
				// end files block show

				if ($sw) {
					$message_regexp = prepare_regexp($sw);
					$messages = array();
					$file_messages_found = 0;
					include($message_dir."/".$file);
					foreach ($messages as $message_name => $message_text) {
						if(preg_match("/$message_regexp/i", $message_name) || preg_match("/$message_regexp/i", $message_text)) {
							$total_messages_found++; $messages_passing--;
							if ($messages_passing < 0 && $messages_shown < $messages_per_page) {
								$file_messages_found++; $messages_shown++;
								$row_style = ($file_messages_found % 2 == 0) ? "row1" : "row2";
        				$t->set_var("row_style", $row_style);
						    $t->set_var("constant_name",$message_name);
								$t->set_var("message_string",$message_text);
								if ($file_writable) {
	 								$t->set_var("language_code", htmlspecialchars($language_code));
	 								$t->set_var("section_title", htmlspecialchars($file_section));
	 								$t->set_var("message_name", htmlspecialchars($message_name));
	 								$t->set_var("sw", htmlspecialchars($sw));
	 								$t->set_var("message_readonly", "");
	 								$t->parse("message_edit", false);
								} else {
	 								$t->set_var("message_edit", "");
	 								$t->parse("message_readonly", false);
								}
 								$t->parse("message_details",true);
							}
						}      
					}
					if ($file_messages_found) {
						$t->set_var("language_file", $file);
						if ($file_writable) {
							$t->parse("add_new_block",false);
						} else {
							$t->set_var("add_new_block","");	
						}
 						$t->parse("message_block", true);
						$t->set_var("message_details", "");
					}
				}
			}
		}
		
    if ($sw && $total_messages_found == 0) {
			$t->set_var("message_block","<div>".va_message("NO_MESSAGES_MSG")."</div><br>");
		}
    closedir($handle); 
    $is_message_dir = true;
 	 	$t->set_var("sw", htmlspecialchars($sw)); 	 		
	} else {
		$errors_list = " No messages  ";
	}	

	// getting section content
	if ($section && isset($is_message_dir) && !$sw) {
		$current_filename = $section . ".php";
		if (file_exists($message_dir . "/" . $current_filename)) {
			$total_messages_found = 0; $messages_shown = 0;
			$messages = array(); 
			include($message_dir."/".$current_filename);
			foreach ($messages as $message_name => $message_text) {
				$total_messages_found++; $messages_passing--;
				if ($messages_passing < 0 && $messages_shown < $messages_per_page) {
					$messages_shown++;
					$row_style = ($messages_shown % 2 == 0) ? "row1" : "row2";
					$t->set_var("row_style", $row_style);
			    $t->set_var("constant_name",$message_name);
					$t->set_var("message_string",$message_text);
					if (is_writable($message_dir . "/" . $current_filename)) {
	 					$t->set_var("language_code", htmlspecialchars($language_code));
	 					$t->set_var("section", htmlspecialchars($section));
	 					$t->set_var("file_section", htmlspecialchars($section));
	 					$t->set_var("message_name", htmlspecialchars($message_name));
	 					$t->set_var("sw", htmlspecialchars($sw));
	 					$t->set_var("message_readonly", "");
	 					$t->parse("message_edit", false);
						$t->parse("add_new_block",false);
					} else {
						$t->set_var("message_edit","");	
						$t->set_var("add_new_block","");	
					}
					$t->parse("message_details",true);
				}
			}

			$t->set_var("language_file", $current_filename);
			$t->set_var("section_name", htmlspecialchars($section));
			$t->set_var("section_title", htmlspecialchars($section));
			$t->parse("add_new_block",false);
 			$t->parse("message_block",false);
		} else {
			$errors_list = va_message("FILE_DOESNT_EXIST_MSG") . $current_filename ;
		}
	} 

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_messages_found, va_message("FOUND_RECORDS_MSG"));
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->sparse("search_results", false);
	}

	if ($total_messages_found > $messages_per_page) {
		$n = new VA_Navigator($settings["admin_templates_dir"], "navigator.html", "admin_messages.php");
		$n->set_parameters(true, true, false);
		$n->set_navigator("navigator", "page", MOVING, $navigator_pages, $messages_per_page, $total_messages_found, false);
	}


	if (isset($errors_list) && strlen($errors_list)) {  
		$t->set_var("errors_list", $errors_list);		
		$t->parse("errors", false);
	}

	$t->pparse("main");

