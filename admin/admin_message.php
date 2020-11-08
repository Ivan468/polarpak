<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_message.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/record.php");
	include_once($root_folder_path . "includes/parameters.php");

	include_once("./admin_common.php");

	check_admin_security("static_messages");

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_message.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_messages_href", "admin_messages.php");
	$t->set_var("admin_message_href", "admin_message.php");

	$operation     = get_param("operation");
	$language_code = get_param("lang");
	if (!$language_code) {
		$language_code = get_param("language_code");
	}
	$language_code = preg_replace("/[^0-9a-z_\-]/i", "", $language_code);
	$section       = get_param("section");
	$section       = preg_replace("/[^0-9a-z_\-\.]/i", "", $section);
	$msg_key       = get_param("msg_key"); 
	$msg_code      = get_param("msg_code"); 
	$msg_text      = get_param("msg_text"); 
	$sw	           = get_param("sw");

	$eol = get_eol();
	$message_dir = "../messages/".$language_code;
	$language_file = $message_dir."/".$section.".php";
	$return_page = "admin_messages.php?language_code=".urlencode($language_code)."&section=".urlencode($section)."&sw=".urlencode($sw);	

	// include edit file with available messages if there are no any errors present
	$messages = array(); $errors = "";
	if (!is_dir($message_dir)) {
		$errors = va_message("FOLDER_DOESNT_EXIST_MSG")." ".$message_dir;
	} else if (!file_exists($language_file)) {
		$errors = va_message("FILE_DOESNT_EXIST_MSG")." ".$language_file;
	} else if (!is_writable($language_file)) {
		$errors = str_replace("{file_name}", $language_file, va_message("FILE_PERMISSION_MESSAGE"));
	} else {
		include($language_file);
		if ($msg_key && !isset($messages[$msg_key])) {
			$msg_key = "";
		}
	}

	// get and update message
	if (!$errors) {
		if ($operation == "cancel") {
			header("Location: " . $return_page);
			exit;
		} else if ($operation) {
			if (!strlen($msg_code)) {
				$error_message = str_replace("{field_name}", va_message("CODE_MSG"), va_message("REQUIRED_MESSAGE"));
				$errors .= $error_message."<br>".$eol;
			} else if (!preg_match(ALPHANUMERIC_REGEXP, $msg_code)) {
				$error_message = str_replace("{field_name}", va_message("CODE_MSG"), va_message("ALPHANUMERIC_ALLOWED_ERROR"));
				$errors .= $error_message."<br>".$eol;
			}

			if (!strlen($msg_text)) {
				$error_message = str_replace("{field_name}", va_message("MESSAGE_MSG"), va_message("REQUIRED_MESSAGE"));
				$errors .= $error_message."<br>".$eol;
			} else if (preg_match("/<\/?(script|applet|style|link|iframe)/", $msg_text)) {
				$error_message = va_message("BANNED_CONTENT_MSG");
				$errors .= $error_message."<br>".$eol;
			}

			if (!$errors) {
				$file_messages = array();
				if (!strlen($msg_key) || $msg_key == $msg_code) {
					$file_messages = $messages;
					$file_messages[$msg_code] = $msg_text;
				} else {
					foreach ($messages as $file_msg_code => $file_msg_text) {
						if ($msg_key == $file_msg_code) {
							$file_messages[$msg_code] = $msg_text;
						} else {
							$file_messages[$file_msg_code] = $file_msg_text;
						}
					}
					// check and assigned updated message if it wasn't saved for some reason
					if(!isset($file_messages[$msg_code])) {
						$file_messages[$msg_code] = $msg_text;
					}
				}

				$fp = fopen($language_file, "w");
				if ($fp) {
					fwrite($fp, "<?php".$eol);
					fwrite($fp, '$messages = '.var_export($file_messages, true).";".$eol);
					fwrite($fp, '$va_messages = array_merge($va_messages, $messages);'.$eol);
					fclose($fp);
					header("Location: " . $return_page);
					exit;
				} else {
  	      $errors = va_message("CANNOT_OPEN_FILE_MSG")." ".$language_file;
				}
			}
		} else if ($msg_key) {
			$msg_code = $msg_key;
			$msg_text = $messages[$msg_key];
		}
	}

	if ($errors) {
		$t->set_var("errors_list", $errors);
	  $t->parse("errors", false);	
	} 

	$t->set_var("language_code", $language_code);
	$t->set_var("sw", $sw);
	$t->set_var("section", $section);
	$t->set_var("msg_key", htmlspecialchars($msg_key));
	$t->set_var("msg_code", htmlspecialchars($msg_code));
	$t->set_var("msg_text", htmlspecialchars($msg_text));
	$t->set_var("return_page", htmlspecialchars($return_page));

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");	
