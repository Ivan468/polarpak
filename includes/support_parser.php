<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  support_parser.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
$old_error_handler = set_error_handler("myErrorHandler");
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	switch ($errno) {
		case E_USER_ERROR:
			$msg  = "<b>My ERROR</b> [$errno] $errstr<br />\n";
			$msg .= "  Fatal error in line $errline of file $errfile";
			$msg .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
			$msg .= "Aborting...<br />\n";
		case E_USER_WARNING:
			$msg  = "My WARNING> [$errno] $errstr<br />\n";
		case E_USER_NOTICE:
			$msg  = "<b>My NOTICE</b> [$errno] $errstr<br />\n";
		default:
			$msg  = "Unkown error type: [$errno] $errstr<br />\n";
  }

	mail("support@viart.com", "Email Parse Error", $msg);

}
*/

// get/set parameters
global $is_admin_path, $is_sub_folder;
$is_sub_folder = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? true : false; 

// check command line options:
// -i incoming@email.com
$options = getopt("i:");
$incoming_email = get_setting_value($options, "i"); 
$outgoing_email = ""; 

if (!isset($save_email_copy)) { $save_email_copy = false; }
if (!isset($emails_folder))   { $emails_folder = "support"; }
$default_attachments_dir = "support_attachments";
$default_files_mask      = "*.gif,*.jpg,*.jpeg,*.bmp,*.tiff,*.tif,*.png,*.ico,*.doc,*.docx,*.txt,*.rtf,*.pdf,*.xls,*.xlsx";

// for admin or subfolder folder use one level up if path is not absolute
if ($is_sub_folder && !preg_match("/^[\/\\\\]/", $emails_folder) && !preg_match("/\:/", $emails_folder)) {
	$emails_folder = "../".$emails_folder;
}

$eol = get_eol();
$test = get_param("test");
if ($test) { $save_email_copy = false; }

if ($test) {
	$fp = fopen("../includes/helpdesk.txt", "r");
} else {
	$fp = fopen("php://stdin", "r");

	//-- save a copy of message
	if ($save_email_copy) {
		srand ((double) microtime() * 1000000);
		$random_value = rand();
		$copy_filename = $emails_folder . date("Y_m_d_H_i_s_",time()) . $random_value .".txt";
		$fw = fopen($copy_filename, 'a');
	}
}

if ($fp) {

	$headers = array();
	$headers["encoding"] = "";
	$headers["charset"] = "";
	$headers["attachment"] = false;
	$headers["inline"] = false;
	$headers["body"] = "";
	$headers["content-type-value"] = "";
	$header_received = false;

	$mail_headers = "";
	$mail_body_text = "";
	$mail_body_html = "";
	$charset = "";

	// receiving header
	$last_end_encoded = false;
	while (!feof($fp) && !$header_received) {
		$line = fgets($fp);
		if ($save_email_copy) {
			fputs($fw, $line);
		}

		$mail_headers .= $line;
		if (preg_match("/^\s*$/", $line)) {
			$header_received = true;
		} else {
			if (preg_match("/^(\w[\w\-]*):(.*)$/", $line, $matches)) {
				$name = strtolower($matches[1]);
				$value = $matches[2];
				$continue_header = false;
			} else {
				$value = $line;
				$continue_header = preg_match("/^[\s\t]/", $line);
			}
			$value = trim($value);
			decode_mail_header($value, $start_encoded, $end_encoded);
			if (!isset($headers[$name])) { $headers[$name] = ""; }
			if ($headers[$name] && (!$continue_header || !$start_encoded || !$last_end_encoded)) {
				$headers[$name] .= " ";
			}
			$headers[$name] .= $value;
			$last_end_encoded = $end_encoded;
		}
	}

	// check header fields
	// if the messages is auto-response then just ignoring such message
	if (isset($headers["x-autoresponder"]) 
		|| isset($headers["x-autoreply"]) 
		|| isset($headers["x-autorespond"]) 
		|| (isset($headers["auto-submitted"]) && strtolower($headers["auto-submitted"]) != "no") ) 
	{
		if ($save_email_copy) {
			fclose($fw);
			chmod($copy_filename, 0755);
		}
		fclose($fp);
		return;
	}

	// handle main header fields
	if (isset($headers["content-type"]) && preg_match("/^([^;\n\r]*)/s", $headers["content-type"], $match)) { 
		$headers["content-type-value"] = trim(strtolower($match[1])); 
		if (preg_match("/delsp=([^\s;]+)/si", $headers["content-type"], $match)) {
			$headers["content-type-delsp"] = trim($match[1]);
		} 
		if (preg_match("/format=([^\s;]+)/si", $headers["content-type"], $match)) {
			$headers["content-type-format"] = trim($match[1]);
		} 
		if (preg_match("/charset=([^\s;]+)/si", $headers["content-type"], $match)) {
			$charset = trim($match[1]);
			$charset = strtolower(trim($charset, "\""));
			$headers["charset"] = $charset;
		} 
	}

	if (isset($headers["content-transfer-encoding"]) && preg_match("/^([^;\n\r]*)/s", $headers["content-transfer-encoding"], $match)) { 
		$headers["encoding"] = trim(strtolower($match[1])); 
	}
	if (isset($headers["content-disposition"]) && preg_match("/attachment/si", $headers["content-disposition"], $match)) { 
		$headers["attachment"] = true;
		// don't allow only attachment
		$headers["attachment-allowed"] = false;
	} else if (isset($headers["content-disposition"]) && preg_match("/inline/si", $headers["content-disposition"], $match)) { 
		$headers["inline"] = true;
	}

	$subject = ""; $from = ""; $from_user = ""; $from_email = ""; $to = ""; $to_emails = array();

	// get subject from header and remove support comments if available and trim the string
	$subject = isset($headers["subject"]) ? $headers["subject"] : "";
	$subject = preg_replace("/^\s*((Re(\[\d+\])?|FW(\[\d+\])?):\s*)+/i", "", $subject);
	$subject = preg_replace("/^Support\s*(Request|Ticket|Issue)\s*/i", "", $subject);
	$subject = preg_replace("/^Heldesk\s*(Request|Ticket|Issue)\s*/i", "", $subject);
	$subject = preg_replace("/^\s*(Request|Ticket|Issue)\s*\:+/i", "", $subject);
	$subject = preg_replace("/^:+\s*/i", "", $subject);
	$subject = trim($subject);
	if (!strlen($subject)) { $subject = "No Subject"; }

	// get sender information
	$from = isset($headers["from"]) ? $headers["from"] : "";
	if (preg_match("/(.*?)<(.*?)>/s", $from, $found)) {
		$from_user  = trim($found[1]);
		$from_email = trim($found[2]);
		if (!strlen($from_email)) {
			$from_email = "<>";
		}
	} else {
		$from_email = trim($from);
	}
	if (!strlen($from_user)) {
		if (preg_match("/^([^@]+)@/", $from_email, $match)) {
			$from_user = trim($match[1]);
		} else {
			$from_user = $from_email;
		}
	}
	// remove quotes from the begining and in the end
	if (preg_match("/^\"(.+)\"$/", $from_user, $match)) {
		$from_user = $match[1];
	}

	//-- determing possible accounts
	$mail_receivers = array(); $cc_emails = array(); 
	$dep_id = ""; $support_type_id = ""; $support_product_id = ""; 
	$attachments_dir = ""; $attachments_mask = "";

	// get receiver emails
	$to = isset($headers["to"]) ? $headers["to"] : "";
	$to_values = explode(",", $to);
	for ($i = 0; $i < sizeof($to_values); $i++) {
		$to_value = $to_values[$i];
		if (preg_match("/<([^@]+@[^@]+(\.[^@]+)*\.[a-z]+)>/i", $to_value, $match)) {
			$mail_receivers[] = $match[1];
		} else if (preg_match("/\s*([^@]+@[^@]+(\.[^@]+)*\.[a-z]+)\s*/i", $to_value, $match)) {
			$mail_receivers[] = trim($match[1]);
		}
	}

	// get receiver cc emails
	$cc = isset($headers["cc"]) ? $headers["cc"] : "";
	$cc_values = explode(",", $cc);
	for ($i = 0; $i < sizeof($cc_values); $i++) {
		$cc_value = $cc_values[$i];
		if (preg_match("/<([^@]+@[^@]+(\.[^@]+)*\.[a-z]+)>/i", $cc_value, $match)) {
			$mail_receivers[] = $match[1];
		} else if (preg_match("/\s*([^@]+@[^@]+(\.[^@]+)*\.[a-z]+)\s*/i", $cc_value, $match)) {
			$mail_receivers[] = trim($match[1]);
		}
	}

	// get all available for pipe emails to exclude them from CC field
	$pipe_emails = array();
	$sql  = " SELECT * FROM " . $table_prefix . "support_pipes ";
	$db->query($sql);
	while ($db->next_record()) {
		$pipe_emails[] = $db->f("incoming_email");
	}

	// check department and it site
	if (!isset($site_id)) { $site_id = 1; } // use first site by default
	$initial_site_id = $site_id; // save initial site_id value
	if ($incoming_email || count($mail_receivers) > 0)	{
		$sql  = " SELECT * FROM " . $table_prefix . "support_pipes ";
		if ($incoming_email) {
			$sql .= " WHERE incoming_email=".$db->tosql($incoming_email, TEXT);
		} else {
			for ($i = 0; $i < sizeof($mail_receivers); $i++) {
				if ($i == 0) {
					$sql .= " WHERE "; 
				} else {
					$sql .= " OR ";
				}
				$sql .= " incoming_email=".$db->tosql($mail_receivers[$i], TEXT);
			}
		}
		$db->query($sql);
		if ($db->next_record()) {
			$dep_id = $db->f("dep_id");
			$support_type_id = $db->f("support_type_id");
			$support_product_id = $db->f("support_product_id");
			$incoming_email = $db->f("incoming_email");
			$outgoing_email = $db->f("outgoing_email");

			if (!strlen($support_type_id)) {
				$support_type_id = 0;
			}
			if (!strlen($support_product_id)) {
				$support_product_id = 0;
			}

			foreach($mail_receivers as $receiver_email) {
				if (!in_array($receiver_email, $pipe_emails)) {
					$cc_emails[] = $receiver_email;
				}
			}

			$dep_name = ""; $sites_all = 0; 
			$dep_new_status_id = ""; $dep_new_admin_mail = ""; $dep_new_user_mail = "";
			$dep_user_reply_admin_mail = ""; $dep_user_reply_user_mail = "";
			$dep_manager_reply_admin_mail = ""; $dep_manager_reply_manager_mail = ""; $dep_manager_reply_user_mail = "";
			$sql  = " SELECT dep_name, sites_all, attachments_dir, attachments_mask, dep_settings, ";
			$sql .= " new_admin_mail, new_user_mail, user_reply_admin_mail, user_reply_user_mail, ";
			$sql .= " manager_reply_admin_mail, manager_reply_manager_mail, manager_reply_user_mail ";
			$sql .= " FROM " . $table_prefix . "support_departments ";
			$sql .= " WHERE dep_id=".$db->tosql($dep_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {	
				$dep_name = $db->f("dep_name");
				$sites_all = $db->f("sites_all");
				$attachments_dir = $db->f("attachments_dir");
				$attachments_mask = $db->f("attachments_mask");

				$dep_settings	= json_decode($db->f("dep_settings"), true);
				$dep_new_status_id = get_setting_value($dep_settings, "new_status_id");

				$dep_new_admin_mail = json_decode($db->f("new_admin_mail"), true);
				$dep_new_user_mail = json_decode($db->f("new_user_mail"), true);

				$dep_user_reply_admin_mail = json_decode($db->f("user_reply_admin_mail"), true);
				$dep_user_reply_user_mail = json_decode($db->f("user_reply_user_mail"), true);

				$dep_manager_reply_admin_mail = json_decode($db->f("manager_reply_admin_mail"), true);
				$dep_manager_reply_manager_mail = json_decode($db->f("manager_reply_manager_mail"), true);
				$dep_manager_reply_user_mail = json_decode($db->f("manager_reply_user_mail"), true);
			}


			if (!$sites_all) {
				// check site_id for found department
				$sql  = " SELECT site_id FROM " . $table_prefix . "support_departments_sites ";
				$sql .= " WHERE dep_id=".$db->tosql($dep_id, INTEGER);
				$sql .= " ORDER BY site_id ";
				$db->query($sql);
				if ($db->next_record()) {
					$site_id = $db->f("site_id");
				}
			}

			if (!$attachments_mask) {
			  $sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
				$sql .= " WHERE setting_type='support' AND setting_name='attachments_users_mask'";
				$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ")";
				$sql .= " ORDER BY site_id ASC ";
				$db->query($sql);
				while ($db->next_record()) {
					$attachments_mask = $db->f("setting_value");
				}
			}
			if (!$attachments_mask) {
				$attachments_mask = $default_files_mask;
			}
			if (!$attachments_dir) {
			  $sql  = " SELECT setting_value FROM " . $table_prefix . "global_settings ";
				$sql .= " WHERE setting_type='support' AND setting_name='attachments_dir' ";
				$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ")";
				$sql .= " ORDER BY site_id ASC ";
				$db->query($sql);
				while ($db->next_record()) {
					$attachments_dir = $db->f("setting_value");
				}
			}
			if (!$attachments_dir) {
				$attachments_dir = $default_attachments_dir;
			}
			// for admin or subfolder folder use one level up if path is not absolute
			if ($is_sub_folder && !preg_match("/^[\/\\\\]/", $attachments_dir) && !preg_match("/\:/", $attachments_dir)) {
				$sub_attachments_dir = "../";
			}
		}
	}
	if ($initial_site_id != $site_id) {
		// refresh global settings
		$settings = get_settings(array("global", "products", "version"));
	}


	if (strlen($dep_id)) {
		// check if message consist from multi parts
		$boundary = "";
		$content_type = isset($headers["content-type"]) ? $headers["content-type"] : "";
		if (preg_match("/boundary=\"([^\"]+)\"/i", $content_type, $match)) {
			$boundary = $match[1];
		} else if (preg_match("/boundary=([^\s]+)/i", $content_type, $match)) {
			$boundary = $match[1];
		} 
		
		// parsing messages
		$message_headers = array(); $messages = array(); 
		$msg_id = 0; $header_received = false;
		if (strlen($boundary)) {
			// if boundary exists check all parts of message
			$is_boundary = false;
			parse_multipart($boundary, false);
		} else {

			// the message has only one part
			$messages[0] = $headers;
			$messages[0]["body"] = "";

			while (!feof($fp)) {
				$line = fgets($fp);
				if ($save_email_copy) {
					fputs($fw, $line);
				}
				$messages[0]["body"] .= $line;
			}

			if ($messages[0]["encoding"] == "base64") { 
				$messages[0]["body"] = base64_decode($messages[0]["body"]);
			} else if ($messages[$msg_id]["encoding"] == "quoted-printable") {
				$messages[0]["body"] = quoted_printable_decode($messages[0]["body"]);
			}

			// if there is no content-type header check it from the mail body
			if ($messages[0]["content-type-value"] == "") {
				if (preg_match("/<html>/i", $messages[0]["body"])) {
					$messages[0]["content-type-value"] = "text/html";
				} else {
					$messages[0]["content-type-value"] = "text/plain";
				}
			}

		}
		
		if ($save_email_copy) { 
			fclose($fw); 
			chmod($copy_filename, 0755);
		}
		fclose($fp);

		//-- analyze parts of the message - getting the body 
		$body = ""; 

		$mail_body_text = "";
		$mail_body_html = "";

		// check for plain text copy
		foreach($messages as $msg_id => $message) {
			if (!$message["attachment"] && !$message["inline"] && $message["content-type-value"] == "text/plain") { 
				if ($mail_body_text) { $mail_body_text .= "--\n"; }
				$mail_body_text .= $message["body"];

			}
		}
		// check for HTML version 
		foreach($messages as $msg_id => $message) {
			if (!$message["attachment"] && !$message["inline"] && $message["content-type-value"] == "text/html") { 
				if ($mail_body_html) { $mail_body_html .= "<br><hr><br>"; }
				$mail_body_html .= $message["body"];
			}
		}

		// add inline messages to the end of messages
		foreach($messages as $msg_id => $message) {
			if ($message["inline"]) { 
				if ($message["content-type-value"] == "text/html") {
					// HTML inline message
					if ($mail_body_html) { $mail_body_html .= "<br><hr><br>"; }
					$mail_body_html .= $message["body"];

					if ($mail_body_text) { $mail_body_text .= "\n--\n"; }
					if ($mail_body_text) { 
						$inline_body = $message["body"];
						$inline_body = preg_replace("/<br\s*\/?>/i", $eol, $inline_body);
						$inline_body = preg_replace("/&nbsp;/i", " ", $inline_body);
						$inline_body = strip_tags($inline_body);
						$inline_body = preg_replace("/&gt;/i", ">", $inline_body);
						$inline_body = preg_replace("/&lt;/i", "<", $inline_body);
						$mail_body_text .= $inline_body;
					}
				} else {
					// Plain text inline message
					if ($mail_body_html) { $mail_body_html .= "<br><hr><br>"; }
					if ($mail_body_html) { $mail_body_html .= nl2br(htmlspecialchars($message["body"])); }

					if ($mail_body_text) { $mail_body_text .= "\n--\n"; }
					$mail_body_text .= $message["body"];
				}
			}
		}


		if ($mail_body_text) {
			$body = $mail_body_text;
		} else {
			$body = $mail_body_html;
			$body = preg_replace("/[\n\r]/", "", $body);
			$body = preg_replace("/<br\s*\/?>/i", $eol, $body);
			$body = preg_replace("/&nbsp;/i", " ", $body);
			$body = strip_tags($body);
			$body = preg_replace("/&gt;/i", ">", $body);
			$body = preg_replace("/&lt;/i", "<", $body);
		}

		// retrieve information about ticket number
		if (preg_match("/Ticket\s+ID\s+is\s+(\d+)/si", $body, $match)) { 
			$ticket_id = $match[1];
		} else if (preg_match("/Ticket\s+ID\:\s*(\d+)/si", $body, $match)) { 
			$ticket_id = $match[1];
		} else if (preg_match("/support_id=(\d+)/s", $body, $match)) {
			$ticket_id = $match[1];
		} else if (preg_match("/Ticket\:\s*(\d+)/si", $body, $match)) { 
			$ticket_id = $match[1];
		} else {
			$ticket_id = "";
		}

		$vc_param = "";
		if (strlen($ticket_id)) {
			// check vc parameter
			if (preg_match("/vc\:\s*([0-9a-f]{8,})/si", $body, $match)) {
				$vc_param = strtolower($match[1]);
			} else if (preg_match("/vc\=\s*([0-9a-f]{8,})/si", $body, $match)) {
				$vc_param = strtolower($match[1]);
			}
		}

		// remove everything after body limit
		$body = preg_replace("/\-+Ticket\-+Body\-+End.*/is", "", $body);
		$body = preg_replace("/\-+(ticket|message)\-+end.*/is", "", $body);
		// trim spaces from the end and begin
		$body = trim($body);
  
		$ticket_date = ""; $ticket_dep_id = ""; $ticket_user_email = ""; $ticket_mail_cc = ""; $ticket_mail_bcc = "";
		if (strlen($ticket_id)) {
			$sql = "SELECT support_id, dep_id, user_email, mail_cc, mail_bcc, date_added FROM " . $table_prefix ."support WHERE support_id=" . $db->tosql($ticket_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) { 
				// check confirmation parameter
				$ticket_dep_id = $db->f("dep_id");
				$ticket_user_email = $db->f("user_email");
				$ticket_mail_cc = $db->f("mail_cc");
				$ticket_mail_bcc = $db->f("mail_bcc");
				$ticket_date = $db->f("date_added", DATETIME);
				$vc_db = strtolower(md5($ticket_id.$ticket_date[3].$ticket_date[4].$ticket_date[5]));
				// compare only first part of vc parameter as sometime last symbols could be cut off
				if (substr($vc_db, 0, 8) != substr($vc_param, 0, 8)) {
					$ticket_id = "";
				}
			} else {
				$ticket_id = "";
			}
		}

		// convert messages to utf-8
		$body = convert_to_utf8($body, $charset);
		$mail_body_text = convert_to_utf8($mail_body_text, $charset);
		$mail_body_html = convert_to_utf8($mail_body_html, $charset);

		// check helpdesk settings
		$support_settings = get_settings("support");
		// additional handle for subject to remove default text on message start
		$user_subject = get_setting_value($support_settings, "user_subject");
		if (preg_match("/^[^:]+:/", $user_subject, $match)) {
			$default_subject = $match[0];
			$subject = str_replace($default_subject, "", $subject);
			$subject = trim($subject, " :");
		}
		$user_subject = get_setting_value($support_settings, "new_user_subject");
		if (preg_match("/^[^:]+:/", $user_subject, $match)) {
			$default_subject = $match[0];
			$subject = str_replace($default_subject, "", $subject);
			$subject = trim($subject, " :");
		}

		$message_id = 0; $from_admin_id = ""; $from_admin_email = "";
		$status_id = ""; $status_name = ""; $status_caption = "";
		// save ticket information
		if (strlen($ticket_id))
		{
			// check if it's admin reply
			$sql  = " SELECT admin_id, email FROM " . $table_prefix ."admins ";
			$sql .= " WHERE email=" . $db->tosql($from_email, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$from_admin_id = $db->f("admin_id");
				$from_admin_email = $db->f("email");
			}
			// check if admin has permission to reply for this department
			if ($from_admin_id) {
				$sql  = " SELECT admin_id FROM " . $table_prefix ."support_users_departments ";
				$sql .= " WHERE admin_id=" . $db->tosql($from_admin_id, INTEGER);
				$sql .= " AND dep_id=" . $db->tosql($ticket_dep_id, INTEGER);
				$from_admin_id = get_db_value($sql);
			}

			if ($from_admin_id) {
				$is_user_reply = 0;
				// check admin status for reply message
				$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE status_type='ADMIN_REPLY' ";
				$db->query($sql);
				if($db->next_record()) {
					$reply_status_id = $db->f("status_id");
				} else {
					$reply_status_id = 0;
				}
			} else {
				$is_user_reply = 1;
				// get user status for reply message
				$sql = " SELECT status_id FROM " . $table_prefix . "support_statuses WHERE status_type='USER_REPLY' ";
				$db->query($sql);
				if($db->next_record()) {
					$reply_status_id = $db->f("status_id");
				} else {
					$reply_status_id = 0;
				}
			}
			if (!$from_admin_id) { $from_admin_id = 0; } // use zero value if admin wasn't found

			$sql  = "INSERT INTO " . $table_prefix . "support_messages ";
			$sql .= " (support_id,is_user_reply,admin_id_assign_by,support_status_id,date_added,reply_from,reply_to,subject,";
			$sql .= " message_text,mail_headers,mail_body_text,mail_body_html) VALUES (";
			$sql .= $db->tosql($ticket_id, INTEGER) . ", ";
			$sql .= $db->tosql($is_user_reply, INTEGER) . ", ";
			$sql .= $db->tosql($from_admin_id, INTEGER, true, false) . ", ";
			$sql .= $db->tosql($reply_status_id, INTEGER) . ", ";
			$sql .= $db->tosql(va_time(), DATETIME) . ", ";
			$sql .= $db->tosql($from_email, TEXT) . ", ";
			$sql .= $db->tosql($to, TEXT) . ", ";
			$sql .= $db->tosql($subject, TEXT) . ", ";
			$sql .= $db->tosql($body, TEXT) . ", ";
			$sql .= $db->tosql($mail_headers, TEXT) . ", ";
			$sql .= $db->tosql($mail_body_text, TEXT) . ", ";
			$sql .= $db->tosql($mail_body_html, TEXT) . ") ";
			$db->query($sql);

			$message_id = $db->last_insert_id();

			// check if customer add a new emails as CC in his reply
			$ticket_mail_cc_lc = strtolower($ticket_mail_cc);
			foreach ($cc_emails as $cc_email) {
				$cc_email_lc = strtolower($cc_email);
				if(strpos($ticket_mail_cc_lc, $cc_email_lc) === false) {
					if ($ticket_mail_cc) { $ticket_mail_cc .= ", "; }
					$ticket_mail_cc .= $cc_email;
				}
			}
  
			$sql  = " UPDATE " . $table_prefix . "support SET ";
			$sql .= " admin_id_assign_to=0, ";
			$sql .= " admin_id_assign_by=" . $db->tosql($from_admin_id, INTEGER, true, false) . ", ";
			$sql .= " support_status_id=" . $db->tosql($reply_status_id, INTEGER) . ", ";
			$sql .= " mail_cc=" . $db->tosql($ticket_mail_cc, TEXT) . ", ";
			$sql .= " date_modified=" . $db->tosql(va_time(), DATETIME);
			$sql .= " WHERE support_id=" . $ticket_id;
			$db->query($sql);
			$new_thread = false;

		} else {

			$ticket_date = va_time();

			// get status for new message
			// check department special NEW status
			if (strlen($dep_new_status_id)) {
				$sql  = " SELECT status_id,status_name,status_caption FROM " . $table_prefix . "support_statuses ";
				$sql .= " WHERE status_id=" . $db->tosql($dep_new_status_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$status_id = $db->f("status_id");
					$status_name = get_translation($db->f("status_name"));
					$status_caption = get_translation($db->f("status_caption"));
				}
			}
			if (!strlen($status_id)) {
				$sql  = " SELECT status_id,status_name,status_caption FROM " . $table_prefix . "support_statuses ";
				$sql .= " WHERE status_type='NEW' ";
				$db->query($sql);
				if ($db->next_record()) {
					$status_id = $db->f("status_id");
					$status_name = get_translation($db->f("status_name"));
					$status_caption = get_translation($db->f("status_caption"));
				} else {
					$status_id = 0;
					$status_name = va_message("NEW_MSG");
					$status_caption = va_message("NEW_MSG");
				}
			}

			// get priority for new message
			$priority_id = 0;
			$sql  = " SELECT sp.priority_id, sup.priority_expiry ";
			$sql .= " FROM " . $table_prefix . "support_priorities sp, " . $table_prefix . "support_users_priorities sup ";
			$sql .= " WHERE sp.priority_id=sup.priority_id ";
			$sql .= " AND user_email=" . $db->tosql($from_email, TEXT);
			$db->query($sql);
			if($db->next_record()) {
				$priority_id = $db->f("priority_id");	
				$current_ts = va_timestamp();
				$priority_expiry = $db->f("priority_expiry", DATETIME);
				if (is_array($priority_expiry)) {
					$priority_expiry_ts = va_timestamp($priority_expiry); 
					if ($current_ts > $priority_expiry_ts) {
						// user rank expired
						$priority_id = 0;
					}
				}
			} 
			if (!$priority_id) {
				$sql  = " SELECT priority_id FROM " . $table_prefix . "support_priorities WHERE is_default=1 ";
				$db->query($sql);
				if ($db->next_record()) {
					$priority_id = $db->f("priority_id");	
				}
			}

			// check user_id for new message
			$sql  = " SELECT user_id FROM " . $table_prefix . "users ";
			$sql .= " WHERE email=" . $db->tosql($from_email, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$user_id = $db->f("user_id");
			} else {
				$user_id = 0;
			}
  
			$ip   = get_ip();
			$sql  = "INSERT INTO " . $table_prefix . "support (";
			$sql .= " site_id, date_modified,support_product_id,support_type_id,support_status_id,support_priority_id,date_added,dep_id,";
			$sql .= " user_id, user_name,user_email,remote_address,summary,description,mail_cc,mail_headers,mail_body_text,mail_body_html) VALUES (";
			if (isset($site_id)) {
				$sql .= $db->tosql($site_id, INTEGER, true, false) . ", ";
			} else {
				$sql .= $db->tosql(1, INTEGER) . ", ";
			}
			$sql .= $db->tosql(va_time(), DATETIME) . ",";
			$sql .= $db->tosql($support_product_id, INTEGER) . ", ";
			$sql .= $db->tosql($support_type_id, INTEGER) . ", ";
			$sql .= $db->tosql($status_id, INTEGER) . ", ";
			$sql .= $db->tosql($priority_id, INTEGER) . ", ";
			$sql .= $db->tosql($ticket_date, DATETIME) . ", ";
			$sql .= $dep_id . ", ";
			$sql .= $db->tosql($user_id, INTEGER) . ", ";
			$sql .= $db->tosql($from_user, TEXT) . ", ";
			$sql .= $db->tosql($from_email, TEXT) . ", ";
			$sql .= $db->tosql($ip, TEXT) . ", ";
			$sql .= $db->tosql($subject, TEXT) . ", ";
			$sql .= $db->tosql($body, TEXT) . ", ";
			if (sizeof($cc_emails) > 0)  {
				$sql .= $db->tosql(implode(", ", $cc_emails), TEXT) . ", ";
			} else {
				$sql .= "'', ";
			}
			$sql .= $db->tosql($mail_headers, TEXT) . ", ";
			$sql .= $db->tosql($mail_body_text, TEXT) . ", ";
			$sql .= $db->tosql($mail_body_html, TEXT) . ") ";
			$db->query($sql);
  
			$ticket_id = $db->last_insert_id();

			$new_thread = true;
		}

		// save attachments
		$attachments = array();
		foreach($messages as $msg_id => $message) {
			if ($message["attachment"] && $message["attachment-allowed"]) { 
				$sql  = " INSERT INTO " . $table_prefix . "support_attachments (support_id, message_id, admin_id, attachment_status, date_added, file_name, file_path) ";
				$sql .= " VALUES (" . $ticket_id . ",";
				$sql .= $db->tosql($message_id, INTEGER) . ", ";
				$sql .= "0, 1, ";
				$sql .= $db->tosql(va_time(), DATETIME) . ", ";
				$sql .= $db->tosql($message["filename"], TEXT) . ", ";
				$sql .= $db->tosql($message["filepath"], TEXT) . ") ";
				$db->query($sql);
				// array to send in email
				$attachments[] = array($message["filename"], $sub_attachments_dir.$message["filepath"]);
			}
		}

		// prepare tags for email
		$mail_tags = array();
		$ticket_date_string = va_date($datetime_show_format, $ticket_date);
		$message_date_string = va_date($datetime_show_format, va_time());
		$site_name = get_setting_value($settings, "site_name", "");
		$site_url = get_setting_value($settings, "site_url", "");

		$admin_site_url = get_setting_value($settings, "admin_site_url", $site_url."admin/");
		$t = new VA_Template(".");
		$vc = md5($ticket_id . $ticket_date[3].$ticket_date[4].$ticket_date[5]);
		$ticket_url = $site_url . "support_messages.php?support_id=" . $ticket_id. "&vc=" . $vc;
		$admin_ticket_url = $admin_site_url."admin_support_reply.php?support_id=".$ticket_id;

		$support_type = ""; 
		if ($support_type_id) {
			$sql  = " SELECT type_name FROM " . $table_prefix . "support_types ";
			$sql .= " WHERE type_id=". $db->tosql($support_type_id, INTEGER);
			$support_type = get_db_value($sql);
		}
		$support_product = "";
		if ($support_product_id) {
			$sql  = " SELECT product_nameFROM " . $table_prefix . "support_products ";
			$sql .= " WHERE product_id=". $db->tosql($support_type_id, INTEGER);
			$support_product = get_db_value($sql);
		}

		$mail_tags = array(
			"site_name" => $site_name, 

			"ticket_id" => $ticket_id,
			"support_id" => $ticket_id,
			"message_id" => $message_id,
			"vc" => $vc,

			"ticket_added" => $ticket_date_string,
			"request_added" => $ticket_date_string,
			"message_added" => $message_date_string,
			"date_added" => $ticket_date_string,
			"date_modified" => $message_date_string,

			"user_id" => $user_id,
			"user_name" => $from_user,
			"user_email" => $from_email,
			"from_user" => $from_user,
			"from_email" => $from_email,

			"summary" => $subject,
			"subject" => $subject,

			"description" => $body,
			"message_text" => $body,

			"department" => $dep_name,
			"dep_name" => $dep_name,
			"product" => $support_product,
			"product_name" => $support_product,
			"type" => $support_type,
			"type_name" => $support_type,
			"status" => $status_name,
			"status_name" => $status_name,


			"site_url" => $site_url,
			"support_url" => $ticket_url,
			"ticket_url" => $ticket_url,
			"user_support_url" => $ticket_url,
			"user_ticket_url" => $ticket_url,
			"admin_support_url" => $admin_ticket_url,
			"admin_ticket_url" => $admin_ticket_url,
		);

		// send notification for new manager reply 
		if ($message_id && $from_admin_id) {

			// check global admin and user notification 
			$manager_reply_user_notification = get_setting_value($support_settings, "manager_reply_user_notification");
			$manager_reply_manager_notification= get_setting_value($support_settings, "manager_reply_manager_notification");
			$manager_reply_admin_notification = get_setting_value($support_settings, "manager_reply_admin_notification");

			// check department notification settings
			$dep_manager_reply_user_notification = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_notification", 0);
			$manager_reply_user_hp_disable = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_hp_disable", 0);
			if ($manager_reply_user_hp_disable) { $manager_reply_user_notification = 0; }

			$dep_manager_reply_manager_notification = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_notification", 0);
			$manager_reply_manager_hp_disable = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_hp_disable", 0);
			if ($manager_reply_manager_hp_disable) { $manager_reply_manager_notification = 0; }

			$dep_manager_reply_admin_notification = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_notification", 0);
			$manager_reply_admin_hp_disable = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_hp_disable", 0);
			if ($manager_reply_admin_hp_disable) { $manager_reply_admin_notification = 0; }

			// global customer notification
			if ($manager_reply_user_notification) {
				$user_subject = get_setting_value($support_settings, "manager_reply_user_subject", $subject);
				$user_message = get_setting_value($support_settings, "manager_reply_user_message", $body);
  
				$mail_cc = get_setting_value($support_settings, "manager_reply_user_cc");
				if ($ticket_mail_cc) {
					if ($mail_cc) { $mail_cc .= ", "; }
					$mail_cc .= $ticket_mail_cc;
				}
				$mail_bcc = get_setting_value($support_settings, "manager_reply_user_bcc");
				if ($ticket_mail_bcc) {
					if ($mail_bcc) { $mail_bcc .= ", "; }
					$mail_bcc .= $ticket_mail_bcc;
				}
		  
				$email_headers = array();
				if ($outgoing_email) {
					$email_headers["from"] = $outgoing_email;	
				} else {
					$email_headers["from"] = get_setting_value($support_settings, "manager_reply_user_from", $settings["admin_email"]);	
				}
				$email_headers["cc"] = $mail_cc;
				$email_headers["bcc"] = $mail_bcc;
				$email_headers["reply_to"] = get_setting_value($support_settings, "manager_reply_user_reply_to");
				$email_headers["return_path"] = get_setting_value($support_settings, "manager_reply_user_return_path");
				$email_headers["mail_type"] = get_setting_value($support_settings, "manager_reply_user_message_type");
				$email_headers["Auto-Submitted"] = "auto-generated";

				va_mail($ticket_user_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
			}
			// end global customer notification

			// department customer notification
			if ($dep_manager_reply_user_notification) {
				$user_subject = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_subject", $subject);
				$user_message = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_message", $body);
  
				$mail_cc = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_cc");
				if ($ticket_mail_cc) {
					if ($mail_cc) { $mail_cc .= ", "; }
					$mail_cc .= $ticket_mail_cc;
				}
				$mail_bcc = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_bcc");
				if ($ticket_mail_bcc) {
					if ($mail_bcc) { $mail_bcc .= ", "; }
					$mail_bcc .= $ticket_mail_bcc;
				}
		  
				$email_headers = array();
				if ($outgoing_email) {
					$email_headers["from"] = $outgoing_email;	
				} else {
					$email_headers["from"] = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_from", $settings["admin_email"]);	
				}
				$email_headers["cc"] = $mail_cc;
				$email_headers["bcc"] = $mail_bcc;
				$email_headers["reply_to"] = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_reply_to");
				$email_headers["return_path"] = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_return_path");
				$email_headers["mail_type"] = get_setting_value($dep_manager_reply_user_mail, "manager_reply_user_message_type");
				$email_headers["Auto-Submitted"] = "auto-generated";

				va_mail($ticket_user_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
			}
			// end department customer notification

			// global manager notification
			if ($manager_reply_manager_notification && $from_admin_email) {
				$manager_subject = get_setting_value($support_settings, "manager_reply_manager_subject", $subject);
				$manager_message = get_setting_value($support_settings, "manager_reply_manager_message", $body);

				$email_headers = array();
				if ($outgoing_email) {
					$email_headers["from"] = $outgoing_email;	
				} else {
					$email_headers["from"] = get_setting_value($support_settings, "manager_reply_manager_from", $settings["admin_email"]);	
				}
				$email_headers["cc"] = get_setting_value($support_settings, "manager_reply_manager_cc");
				$email_headers["bcc"] = get_setting_value($support_settings, "manager_reply_manager_bcc");
				$email_headers["reply_to"] = get_setting_value($support_settings, "manager_reply_manager_reply_to");
				$email_headers["return_path"] = get_setting_value($support_settings, "manager_reply_manager_return_path");
				$email_headers["mail_type"] = get_setting_value($support_settings, "manager_reply_manager_message_type");
				$email_headers["Auto-Submitted"] = "auto-generated";

				va_mail($from_admin_email, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
			} // end global manager notification

			// department manager notification
			if ($dep_manager_reply_manager_notification && $from_admin_email) {
				$manager_subject = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_subject", $subject);
				$manager_message = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_message", $body);

				$email_headers = array();
				if ($outgoing_email) {
					$email_headers["from"] = $outgoing_email;	
				} else {
					$email_headers["from"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_from", $settings["admin_email"]);	
				}
				$email_headers["cc"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_cc");
				$email_headers["bcc"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_bcc");
				$email_headers["reply_to"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_reply_to");
				$email_headers["return_path"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_return_path");
				$email_headers["mail_type"] = get_setting_value($dep_manager_reply_manager_mail, "manager_reply_manager_message_type");
				$email_headers["Auto-Submitted"] = "auto-generated";

				va_mail($from_admin_email, $manager_subject, $manager_message, $email_headers, $attachments, $mail_tags);
			} // end department manager notification

			// global admin notification
			if ($manager_reply_admin_notification) {
				$mail_to = get_setting_value($support_settings, "manager_reply_admin_to", $settings["admin_email"]);

				$admin_subject = get_setting_value($support_settings, "manager_reply_admin_subject", $subject);
				$admin_message = get_setting_value($support_settings, "manager_reply_admin_message", $body);
		  
				$email_headers = array();
				if ($outgoing_email) {
					$email_headers["from"] = $outgoing_email;	
				} else {
					$email_headers["from"] = get_setting_value($support_settings, "manager_reply_admin_from", $settings["admin_email"]);	
				}
				$email_headers["cc"] = get_setting_value($support_settings, "manager_reply_admin_cc");
				$email_headers["bcc"] = get_setting_value($support_settings, "manager_reply_admin_bcc");
				$email_headers["reply_to"] = get_setting_value($support_settings, "manager_reply_admin_reply_to");
				$email_headers["return_path"] = get_setting_value($support_settings, "manager_reply_admin_return_path");
				$email_headers["mail_type"] = get_setting_value($support_settings, "manager_reply_admin_message_type");
				$email_headers["Auto-Submitted"] = "auto-generated";

				va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
			} // end global admin notification

			// department admin notification
			if ($dep_manager_reply_admin_notification) {
				$mail_to = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_to", $settings["admin_email"]);

				$admin_subject = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_subject", $subject);
				$admin_message = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_message", $body);
		  
				$email_headers = array();
				if ($outgoing_email) {
					$email_headers["from"] = $outgoing_email;	
				} else {
					$email_headers["from"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_from", $settings["admin_email"]);	
				}
				$email_headers["cc"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_cc");
				$email_headers["bcc"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_bcc");
				$email_headers["reply_to"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_reply_to");
				$email_headers["return_path"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_return_path");
				$email_headers["mail_type"] = get_setting_value($dep_manager_reply_admin_mail, "manager_reply_admin_message_type");
				$email_headers["Auto-Submitted"] = "auto-generated";

				va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
			} // end department admin notification
		}

		// send notification for new ticket or user reply
		if (!$from_admin_id) {

			if ($new_thread) {
				// admin and user notifications for new ticket
				// check global admin and user notification 
				$admin_notification = get_setting_value($support_settings, "new_admin_notification", 0);
				$user_notification = get_setting_value($support_settings, "new_user_notification", 0);

				// check department notification settings
				$admin_dep_notification = get_setting_value($dep_new_admin_mail, "new_admin_notification", 0);
				$admin_hp_disable = get_setting_value($dep_new_admin_mail, "new_admin_hp_disable", 0);
				if ($admin_hp_disable) { $admin_notification = 0; }
				$user_dep_notification = get_setting_value($dep_new_user_mail, "new_user_notification", 0);
				$user_hp_disable = get_setting_value($dep_new_user_mail, "new_user_hp_disable", 0);
				if ($user_hp_disable) { $user_notification = 0; }

				// send global email notification to admin
				if ($admin_notification) {
					$mail_to = get_setting_value($support_settings, "new_admin_to", $settings["admin_email"]);

					$admin_subject = get_setting_value($support_settings, "new_admin_subject", $subject);
					$admin_message = get_setting_value($support_settings, "new_admin_message", $body);
   		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($support_settings, "new_admin_from", $settings["admin_email"]);	
					}
					$email_headers["cc"] = get_setting_value($support_settings, "new_admin_cc");
					$email_headers["bcc"] = get_setting_value($support_settings, "new_admin_bcc");
					$email_headers["reply_to"] = get_setting_value($support_settings, "new_admin_reply_to");
					$email_headers["return_path"] = get_setting_value($support_settings, "new_admin_return_path");
					$email_headers["mail_type"] = get_setting_value($support_settings, "new_admin_message_type");
					$email_headers["Auto-Submitted"] = "auto-generated";
		    
					va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
				} // end admin notification

				// send department email notification to admin
				if ($admin_dep_notification) {
					$mail_to = get_setting_value($dep_new_admin_mail, "new_admin_to", $settings["admin_email"]);

					$admin_subject = get_setting_value($dep_new_admin_mail, "new_admin_subject", $subject);
					$admin_message = get_setting_value($dep_new_admin_mail, "new_admin_message", $body);
   		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($dep_new_admin_mail, "new_admin_from", $settings["admin_email"]);	
					}
					$email_headers["cc"] = get_setting_value($dep_new_admin_mail, "new_admin_cc");
					$email_headers["bcc"] = get_setting_value($dep_new_admin_mail, "new_admin_bcc");
					$email_headers["reply_to"] = get_setting_value($dep_new_admin_mail, "new_admin_reply_to");
					$email_headers["return_path"] = get_setting_value($dep_new_admin_mail, "new_admin_return_path");
					$email_headers["mail_type"] = get_setting_value($dep_new_admin_mail, "new_admin_message_type");
					$email_headers["Auto-Submitted"] = "auto-generated";
		    
					va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
				} // end department admin notification

				// send global email notification to user 
				if ($user_notification) {
					$user_subject = get_setting_value($support_settings, "new_user_subject", $subject);
					$user_message = get_setting_value($support_settings, "new_user_message", $body);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($support_settings, "new_user_from", $settings["admin_email"]);
					}
					$email_headers["cc"] = get_setting_value($support_settings, "new_user_cc");
					$email_headers["bcc"] = get_setting_value($support_settings, "new_user_bcc");
					$email_headers["reply_to"] = get_setting_value($support_settings, "new_user_reply_to");
					$email_headers["return_path"] = get_setting_value($support_settings, "new_user_return_path");
					$email_headers["mail_type"] = get_setting_value($support_settings, "new_user_message_type");
					$email_headers["Auto-Submitted"] = "auto-generated";
		    
					va_mail($from_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
				} // end user notification

				// send department email notification to user 
				if ($user_dep_notification) {
					$user_subject = get_setting_value($dep_new_user_mail, "new_user_subject", $subject);
					$user_message = get_setting_value($dep_new_user_mail, "new_user_message", $body);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($dep_new_user_mail, "new_user_from", $settings["admin_email"]);
					}
					$email_headers["cc"] = get_setting_value($dep_new_user_mail, "new_user_cc");
					$email_headers["bcc"] = get_setting_value($dep_new_user_mail, "new_user_bcc");
					$email_headers["reply_to"] = get_setting_value($dep_new_user_mail, "new_user_reply_to");
					$email_headers["return_path"] = get_setting_value($dep_new_user_mail, "new_user_return_path");
					$email_headers["mail_type"] = get_setting_value($dep_new_user_mail, "new_user_message_type");
					$email_headers["Auto-Submitted"] = "auto-generated";
		    
					va_mail($from_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
				} // end department user notification

				// end new ticket notification block
			} else {

				// admin and user notifications for user reply
				// check global admin and user notification 
				$user_reply_admin_notification = get_setting_value($support_settings, "user_reply_admin_notification", 0);
				$user_reply_user_notification = get_setting_value($support_settings, "user_reply_user_notification", 0);

				// check department notification settings
				$admin_dep_notification = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_notification", 0);
				$admin_hp_disable = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_hp_disable", 0);
				if ($admin_hp_disable) { $user_reply_admin_notification = 0; }
				$user_dep_notification = get_setting_value($dep_user_reply_user_mail, "user_reply_user_notification", 0);
				$user_hp_disable = get_setting_value($dep_user_reply_user_mail, "user_reply_user_hp_disable", 0);
				if ($user_hp_disable) { $user_reply_user_notification = 0; }

				// send global email notification to admin
				if ($user_reply_admin_notification) {
					$mail_to = get_setting_value($support_settings, "user_reply_admin_to", $settings["admin_email"]);

					$admin_subject = get_setting_value($support_settings, "user_reply_admin_subject", $subject);
					$admin_message = get_setting_value($support_settings, "user_reply_admin_message", $body);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($support_settings, "user_reply_admin_from", $settings["admin_email"]); 
					}
					$email_headers["cc"] = get_setting_value($support_settings, "user_reply_admin_cc");
					$email_headers["bcc"] = get_setting_value($support_settings, "user_reply_admin_bcc");
					$email_headers["reply_to"] = get_setting_value($support_settings, "user_reply_admin_reply_to");
					$email_headers["return_path"] = get_setting_value($support_settings, "user_reply_admin_return_path");
					$email_headers["mail_type"] = get_setting_value($support_settings, "user_reply_admin_message_type");
					$email_headers["Auto-Submitted"] = "auto-generated";
		    
					va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
				} // end admin global notification

				// send department email notification to admin
				if ($admin_dep_notification) {
					$mail_to = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_to", $settings["admin_email"]);

					$admin_subject = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_subject", $subject);
					$admin_message = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_message", $body);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_from", $settings["admin_email"]); 
					}
					$email_headers["cc"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_cc");
					$email_headers["bcc"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_bcc");
					$email_headers["reply_to"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_reply_to");
					$email_headers["return_path"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_return_path");
					$email_headers["mail_type"] = get_setting_value($dep_user_reply_admin_mail, "user_reply_admin_message_type");
					$email_headers["Auto-Submitted"] = "auto-generated";
		    
					va_mail($mail_to, $admin_subject, $admin_message, $email_headers, $attachments, $mail_tags);
				} // end admin department notification

				// send global email notification to user 
				if ($user_reply_user_notification) {
					$user_subject = get_setting_value($support_settings, "user_reply_user_subject", $subject);
					$user_message = get_setting_value($support_settings, "user_reply_user_message", $body);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($support_settings, "user_reply_user_from", $settings["admin_email"]);
					}
					$email_headers["cc"] = get_setting_value($support_settings, "user_reply_user_cc");
					$email_headers["bcc"] = get_setting_value($support_settings, "user_reply_user_bcc");
					$email_headers["reply_to"] = get_setting_value($support_settings, "user_reply_user_reply_to");
					$email_headers["return_path"] = get_setting_value($support_settings, "user_reply_user_return_path");
					$email_headers["mail_type"] = get_setting_value($support_settings, "user_reply_user_message_type");
					$email_headers["Auto-Submitted"] = "auto-generated";
		    
					va_mail($from_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
				} // end user global notification

				// send department email notification to user 
				if ($user_dep_notification) {
					$user_subject = get_setting_value($dep_user_reply_user_mail, "user_reply_user_subject", $subject);
					$user_message = get_setting_value($dep_user_reply_user_mail, "user_reply_user_message", $body);
		    
					$email_headers = array();
					if ($outgoing_email) {
						$email_headers["from"] = $outgoing_email;	
					} else {
						$email_headers["from"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_from", $settings["admin_email"]);
					}
					$email_headers["cc"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_cc");
					$email_headers["bcc"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_bcc");
					$email_headers["reply_to"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_reply_to");
					$email_headers["return_path"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_return_path");
					$email_headers["mail_type"] = get_setting_value($dep_user_reply_user_mail, "user_reply_user_message_type");
					$email_headers["Auto-Submitted"] = "auto-generated";
		    
					va_mail($from_email, $user_subject, $user_message, $email_headers, $attachments, $mail_tags);
				} // end user department notification

				// end reply notification block
			}
		}
		// end sending notifications for new ticket or user reply


	} else {
		// cannot find department to pipe
		if ($save_email_copy) {
			while (!feof($fp)) {
				$line = fgets($fp);
				fputs($fw, $line);
			}
			fclose($fw);
			chmod($copy_filename, 0755);
		}
		fclose($fp);

		$recipients    = $settings["admin_email"];
		$mail_subject  = "auto: ViArt SHOP Notification";
		$email_headers = array();
		$email_headers["from"] = $settings["admin_email"];
		$email_headers["Auto-Submitted"] = "auto-generated";
		$email_headers["Content-Type"] = "text/plain";
		
		$message  = "Can't find the appropriate Helpdesk Department to pipe email for account: ";
		$message .= "'" . join(", ", $mail_receivers) . "'" . $eol;
  
		va_mail($recipients, $mail_subject, $message, $email_headers);
	}

} else {
	// cannot read email message
	$recipients     = $settings["admin_email"];
	$mail_subject   = "auto: ViArt SHOP Notification";
	$email_headers = array();
	$email_headers["from"] = $settings["admin_email"];
	$email_headers["Auto-Submitted"] = "auto-generated";
	$email_headers["Content-Type"] = "text/plain";
	$message  = "Can't read the email message";
	va_mail($recipients, $mail_subject, $message, $email_headers);
}


function parse_multipart($boundary, $is_sub_boundary)
{
	global $fp, $fw, $save_email_copy, $charset;
	global $is_boundary, $header_received;
	global $message_headers, $messages, $msg_id;
	global $settings, $attachments_mask, $sub_attachments_dir, $attachments_dir;
	if (!isset($sub_attachments_dir)) { $sub_attachments_dir = ""; } // subpath to create a full or relative path to attachments directory

	$last_end_encoded = false; // variable to check if we need to add space symbol when concatenating values for header data
	while (!feof($fp)) {
		$line = fgets($fp);
		if ($save_email_copy) { fputs($fw, $line); }

		if (!$is_boundary) {
			// check for first boundary
			if (trim($line) == $boundary || trim($line) == "--" . $boundary) {
				$is_boundary = true;
			} else if ($is_sub_boundary && trim($line) == "--".$boundary."--") {
				// check end of boundary for sub-patterns
				return;
			}
		} else if (!$header_received) {
			// get message header
			if (trim($line) == "") {
				$header_received = true;
				$sub_charset = "";
				$messages[$msg_id] = $message_headers;
				$messages[$msg_id]["charset"] = "";
				$messages[$msg_id]["encoding"] = "";
				$messages[$msg_id]["attachment"] = false;
				$messages[$msg_id]["inline"] = false;
				$messages[$msg_id]["body"] = "";
				$messages[$msg_id]["content-type-value"] = "";


				$sub_boundary = "";
				if (isset($message_headers["content-type"]) && preg_match("/^([^;\n\r]*)/s", $message_headers["content-type"], $match)) { 
					$messages[$msg_id]["content-type-value"] = trim(strtolower($match[1])); 
					if (preg_match("/delsp=([^\s]+)/si", $message_headers["content-type"], $match)) {
						$messages[$msg_id]["content-type-delsp"] = trim($match[1]);
					} 
					if (preg_match("/format=([^\s]+)/si", $message_headers["content-type"], $match)) {
						$messages[$msg_id]["content-type-format"] = trim($match[1]);
					} 
					// check if message consist from multi parts
					if (preg_match("/boundary=\"([^\"]+)\"/i", $message_headers["content-type"], $match)) {
						$sub_boundary = $match[1];
					} else if (preg_match("/boundary=([^\s]+)/i", $message_headers["content-type"], $match)) {
						// trim any semicolons at the end of boundary
						$sub_boundary = rtrim($match[1], ";");
					} 
					if (preg_match("/charset=([^\s]+)/si", $message_headers["content-type"], $match)) {
						$sub_charset = trim($match[1]);
						$sub_charset = strtolower(trim($sub_charset, "\""));
						$messages[$msg_id]["charset"] = $sub_charset;
					} 
				}

				if (strlen($sub_boundary)) {
					$is_boundary = false; $header_received = false; $message_headers = array();
					parse_multipart($sub_boundary, true);
				} else {

					if (isset($message_headers["content-transfer-encoding"]) && preg_match("/^([^;\n\r]*)/s", $message_headers["content-transfer-encoding"], $match)) { 
						$messages[$msg_id]["encoding"] = trim(strtolower($match[1])); 
					}
					// check if the mail part is attachment
					if ((isset($message_headers["content-disposition"]) 
						&& (preg_match("/filename=/i", $message_headers["content-disposition"]) 
						|| preg_match("/attachment/si", $message_headers["content-disposition"], $match)))
						|| preg_match("/name=\"(.+)\"/si", $messages[$msg_id]["content-type"])
						|| preg_match("/name=([^\s]+)/si", $messages[$msg_id]["content-type"])) { 
						$messages[$msg_id]["attachment"] = true;
					} else if (isset($message_headers["content-disposition"]) && preg_match("/inline/si", $message_headers["content-disposition"], $match)) {
						// check if the mail part inline
						$messages[$msg_id]["inline"] = true;
					}
					// if the type of message not text or html consider it as attachment
					if ($messages[$msg_id]["content-type-value"] && $messages[$msg_id]["content-type-value"] != "text/plain"
						&& $messages[$msg_id]["content-type-value"] != "text/html") {
						$messages[$msg_id]["attachment"] = true;
					}
					// check attachment settings 
					if ($messages[$msg_id]["attachment"]) { //-- get the file name
						$messages[$msg_id]["filename"] = "";
						if (isset($message_headers["content-disposition"]) && preg_match("/filename=/i", $message_headers["content-disposition"])) {
							if (preg_match("/filename=[\s\t]*\"([^\"]+)\"/si", $message_headers["content-disposition"], $match)) {
								$messages[$msg_id]["filename"] = trim($match[1]);
							} else if (preg_match("/filename=([^\s]+)/si", $message_headers["content-disposition"], $match)) {
								$messages[$msg_id]["filename"] = trim($match[1]);
							}
						} else {
							if (preg_match("/name=[\s\t]*\"(.+)\"/si", $messages[$msg_id]["content-type"], $match)) {
								$messages[$msg_id]["filename"] = trim($match[1]);
							} else if (preg_match("/name=([^\s]+)/si", $messages[$msg_id]["content-type"], $match)) {
								$messages[$msg_id]["filename"] = trim($match[1]);
							} else if (preg_match("/^text\/([^\s]+)/i", $messages[$msg_id]["content-type-value"], $match)) {
								$messages[$msg_id]["filename"] = trim($match[1]) . ".txt";
							} else if (preg_match("/^message\/([^\s]+)/i", $messages[$msg_id]["content-type-value"], $match)) {
								$messages[$msg_id]["filename"] = trim($match[1]) . ".txt";
							}
						}

						if (strlen($messages[$msg_id]["filename"])) {
							$filename_charset = "";
							$filename = $messages[$msg_id]["filename"];
							$filename = preg_replace("/[\\:\\/\\\\]/", "_", $filename);
							$messages[$msg_id]["filename"] = $filename;
						}
						// remove some symbols
						$messages[$msg_id]["filename"] = preg_replace("/[\n\t\r]/", "", $messages[$msg_id]["filename"]);
			  
						$messages[$msg_id]["attachment-allowed"] = false;
						if (strlen($messages[$msg_id]["filename"])) {
							$attachments_regexp = preg_replace("/\s/", "", $attachments_mask);
							$filename_check = $messages[$msg_id]["filename"];
							if (!preg_match("/\./", $filename_check)) {
								$filename_check .= ".";
							}
							$s = array("\\","^","\$",".","[","]","|","(",")","+","{","}");
							$r = array("\\\\","\\^","\\\$","\\.","\\[","\\]","\\|","\\(","\\)","\\+","\\{","\\}");
							$attachments_regexp = str_replace($s, $r, $attachments_regexp);
							$attachments_regexp = str_replace(array(",", ";", "*", "?"), array(")|(", ")|(", ".*", "."), $attachments_regexp);
							$attachments_regexp = "/^((" . $attachments_regexp . "))$/i";
  						if (preg_match($attachments_regexp, $filename_check)) {
								if (is_dir($sub_attachments_dir.$attachments_dir)) {
									$messages[$msg_id]["attachment-allowed"] = true;
									$filename = $messages[$msg_id]["filename"];

									// check for available name for filename
									$new_filename = $filename;
									$file_index = 0;
									while (file_exists($sub_attachments_dir.$attachments_dir . $new_filename)) {
										$file_index++;
										$delimiter_pos = strpos($filename, ".");
										if($delimiter_pos) {
											$new_filename = substr($filename, 0, $delimiter_pos) . "_" . $file_index . substr($filename, $delimiter_pos);
										} else {
											$new_filename = $index . "_" . $filename;
										}
									}
			          
									$filepath = $attachments_dir . $new_filename;
									$messages[$msg_id]["filepath"] = $filepath;
									$fa = fopen($sub_attachments_dir.$filepath, 'w');
								} else {
									// bad directory for attachments 
									$recipients     = $settings["admin_email"];
									$mail_subject   = "auto: ViArt SHOP Notification";
									$email_headers = array();
									$email_headers["from"] = $settings["admin_email"];
									$email_headers["Auto-Submitted"] = "auto-generated";
									$email_headers["Content-Type"] = "text/plain";
									
									$message  = "Directory for HelpDesk attachments cannot be found: ";
									$message .= "'" . $sub_attachments_dir.$attachments_dir . "'" . $eol;
	              
									va_mail($recipients, $mail_subject, $message, $email_headers);
								}
							}
						}
					}
				}

			} else {
				if (preg_match("/^(\w[\w\-]*):(.*)$/", $line, $matches)) {
					$name = strtolower($matches[1]);
					$value = $matches[2];
					$continue_header = false;
				} else {
					$value = $line;
					$continue_header = preg_match("/^[\s\t]/", $line);
				}
				$value = trim($value);
				decode_mail_header($value, $start_encoded, $end_encoded); // $start_encoded & $end_encoded variable required to check if need to add space symbold when concatenating values for header data
				if (!isset($message_headers[$name])) { $message_headers[$name] = ""; }
				if ($message_headers[$name] && (!$continue_header || !$start_encoded || !$last_end_encoded)) {
					$message_headers[$name] .= " ";
				}
				$message_headers[$name] .= $value;
				$last_end_encoded = $end_encoded;

			}
		} else {
			if (trim($line) == $boundary || trim($line) == "--" . $boundary || trim($line) == "--".$boundary."--") {
				if ($messages[$msg_id]["attachment"]) {
					if ($messages[$msg_id]["attachment-allowed"]) {
						fclose($fa);
						chmod($sub_attachments_dir.$messages[$msg_id]["filepath"], 0755);
					}
				} else if ($messages[$msg_id]["encoding"] == "base64") { 
					// decode base64 message 
					$messages[$msg_id]["body"] = base64_decode($messages[$msg_id]["body"]);
				} else if ($messages[$msg_id]["encoding"] == "quoted-printable") {
					// decode quoted-printable message 
					$messages[$msg_id]["body"] = quoted_printable_decode($messages[$msg_id]["body"]);
				}

				if ($messages[$msg_id]["content-type-value"] == "text/html" || $messages[$msg_id]["content-type-value"] == "text/plain") {
					// convert message HTML and text message to utf-8 if different charset was used
					if($messages[$msg_id]["charset"] != "utf-8") {
						$messages[$msg_id]["body"] = convert_to_utf8($messages[$msg_id]["body"], $messages[$msg_id]["charset"]);
					}
				}

				$msg_id++;
				$message_headers = array();
				$header_received = false;
				if (trim($line) == "--".$boundary."--") {
					$is_boundary = false;
					if ($is_sub_boundary) {
						return;
					}
				}
			} else {
				if ($messages[$msg_id]["attachment"]) {
					if ($messages[$msg_id]["attachment-allowed"]) {
						if ($messages[$msg_id]["encoding"] == "base64") { 
							$line = base64_decode($line);
						} else if ($messages[$msg_id]["encoding"] == "quoted-printable") {
							$line = quoted_printable_decode($line);
						}
						fputs ($fa, $line);
						/*
						if ($messages[$msg_id]["content-type-value"] == "text/html" && $messages[$msg_id]["filename"] == "message.html") {
							$messages[$msg_id]["body"] .= $line;
						}//*/
					} 
				} else {
					$messages[$msg_id]["body"] .= $line;
				}
			}
		}
	}
}

function decode_mail_header(&$message, &$start_encoded, &$end_encoded) 
{
	$start_encoded = preg_match("/^=\?([\w\d\-]+)\?([QB])\?([^\?\s]+)\?=/i", $message); 
	$end_encoded = preg_match("/=\?([\w\d\-]+)\?([QB])\?([^\?\s]+)\?=$/i", $message); 
	if (preg_match_all("/=\?([\w\d\-]+)\?([QB])\?([^\?\s]+)\?=/i", $message, $matches)) {
		for ($m = 0; $m < sizeof($matches[0]); $m++) {
			$encoded_word = $matches[0][$m];
			$sub_charset = $matches[1][$m];
			$encode_type = $matches[2][$m];
			$encoded_text = $matches[3][$m];
			if (strtoupper($encode_type) == "Q") {
				$encoded_text = str_replace("_", "=20", $encoded_text); // additional conversion of underscore into space symbol
				$text = quoted_printable_decode($encoded_text);
			} else if (strtoupper($encode_type) == "B") {
				$text = base64_decode($encoded_text);
			}
			$text = convert_to_utf8($text, $sub_charset);
			$message = str_replace($encoded_word, $text, $message);
		}
	}
	return $message;
}

