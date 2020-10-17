<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  common_functions.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	function get_query_string($variables, $remove_parameters = "", $query_string = "", $set_hidden_parameters = false)
	{
		global $t;

		if (is_array($variables))
		{
			$hidden_parameters = "";
			if (!is_array($remove_parameters)) {
				$remove_parameters = array($remove_parameters);
			}
			foreach($variables as $key => $value) {
				if (strlen($value) && !in_array($key, $remove_parameters)) {
					$query_string .= strlen($query_string) ? "&" : "";
					$query_string .= urlencode($key) . "=" . urlencode($value);
					if ($set_hidden_parameters) {
						$hidden_parameters .= "<input type=\"hidden\" name=\"" . htmlspecialchars($key) . "\" value=\"";
						$hidden_parameters .= htmlspecialchars($value) . "\" />";
					}
				}
			}
			if ($set_hidden_parameters) {
				$t->set_var("hidden_parameters", $hidden_parameters);
			}
		}

		if ($query_string) {$query_string = "?" . $query_string; }
		return $query_string;
	}

	function get_transfer_params($remove_parameters = "")
	{
		$pass_parameters = array();
		$available_params = array(
			"search_string", "category_id", "search_category_id", "item_id", "article_id",
			"s_tit", "s_cod", "s_des", "manf", "user", "u", "lprice", "hprice",
			"lweight", "hweight", "page", "sw", "sf", "forum_id", "thread_id",
			"sort", "sort_ord", "sort_dir", "filter", "country", "state", "zip",
			"pn_pr", "pn_ar", "pn_ad", "pn_th", "pn_pr_sp", 
		);

		for ($si = 0; $si < sizeof($available_params); $si++) {
			$param_name  = $available_params[$si];
			$param_value = get_param($param_name);
			if (strlen($param_value)) {
				$pass_parameters[$param_name] = $param_value;
			}
		}
		$pq = get_param("pq");
		$fq = get_param("fq");
		if ($pq > 0) {
			for ($pi = 1; $pi <= $pq; $pi++) {
				$property_name = get_param("pn_" . $pi);
				$property_value = get_param("pv_" . $pi);
				if (strlen($property_name) && strlen($property_value)) {
					$pass_parameters["pq"] = $pq;
					$pass_parameters["pn_" . $pi] = $property_name;
					$pass_parameters["pv_" . $pi] = $property_value;
				}
			}
		}
		if ($fq > 0) {
			for ($fi = 1; $fi <= $fq; $fi++) {
				$feature_name = get_param("fn_" . $fi);
				$feature_value = get_param("fv_" . $fi);
				if (strlen($feature_name) && strlen($feature_value)) {
					$pass_parameters["fq"] = $fq;
					$pass_parameters["fn_" . $fi] = $feature_name;
					$pass_parameters["fv_" . $fi] = $feature_value;
				}
			}
		}
		// check parameters to be removed
		if (is_array($remove_parameters)) {
			for ($rp = 0; $rp < sizeof($remove_parameters); $rp++) {
				$param_name = $remove_parameters[$rp];
				if (isset($pass_parameters[$param_name])) {
					unset($pass_parameters[$param_name]);
				}
			}
		}
		return $pass_parameters;
	}

	function transfer_params($remove_parameters, $set_hidden_parameters = false)
	{
		$pass_parameters = get_transfer_params($remove_parameters);
		return get_query_string($pass_parameters, "", "", $set_hidden_parameters);
	}

	function get_param($param_name, $param_type = 0)
	{
	  $param_value = "";
		if (!defined("GET")) {
			$param_value = isset($_REQUEST[$param_name]) ? $_REQUEST[$param_name] : "";
		} elseif (isset($_POST[$param_name]) && $param_type != GET) {
			$param_value = $_POST[$param_name];
		} elseif (isset($_GET[$param_name]) && $param_type != POST) {
			$param_value = $_GET[$param_name];
		}
		if (function_exists("mb_detect_encoding")) {
			if (!mb_detect_encoding($param_value, "UTF-8", true)) {
				$encoding = mb_detect_encoding($param_value, "ISO-8859-1, Windows-1251, Windows-1252, UTF-8", true);
				$param_value = mb_convert_encoding ($param_value, "UTF-8", $encoding);
			}
		}

		return $param_value;
	}

	function get_cookie($parameter_name)
	{
		return isset($_COOKIE[$parameter_name]) ? $_COOKIE[$parameter_name] : "";
	}

	function get_session($parameter_name)
	{
		global $session_prefix;
		$parameter_name = $session_prefix . $parameter_name;
		return isset($_SESSION[$parameter_name]) ? $_SESSION[$parameter_name] : "";
	}

	function set_session($parameter_name, $parameter_value)
	{
		global $session_prefix;
		$parameter_name = $session_prefix . $parameter_name;
		$_SESSION[$parameter_name] = $parameter_value;
	}

	function get_options($values, $selected_value)
	{
		$eol = get_eol();
		$options = "";
		if (is_array($values))
		{
			for ($i = 0; $i < sizeof($values); $i++)
			{
				if ($values[$i][0] == $selected_value && strlen($values[$i][0]) == strlen($selected_value)) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$options .= "<option " . $selected;
				$options .= " value=\"" . htmlspecialchars($values[$i][0]) . "\">";
				$options .= htmlspecialchars($values[$i][1]) . "</option>". $eol;
			}
		}
		return $options;
	}

	function set_options($values, $value, $block_name, $events = "")
	{
		global $t, $data_separator;
		if (!isset($data_separator) || !$data_separator) { $data_separator = "; "; }
		
		$control_values = array(); $control_desc = array();
		$t->set_var($block_name, "");
		if (is_array($values))
		{
			for ($i = 0; $i < sizeof($values); $i++)
			{
				$cur_val = $values[$i][0];
				call_event($events, BEFORE_SHOW_VALUE, array("current_value" => $cur_val));
				$checked = ""; $selected = ""; $classname = ""; $data_checked = "";
				if (is_array($value)) {
					for ($j = 0; $j < sizeof($value); $j++) {
						if (strval($cur_val) == strval($value[$j])) {					
							$control_values[] = $cur_val;
							$control_desc[] = $values[$i][1];
							$checked = "checked"; $selected = "selected"; $classname = "selected"; $data_checked = "checked";
							break;
						}
					}
				} elseif (strval($cur_val) == strval($value) && $values[$i][1] != "-----Additional Attribute----") {
					$control_values[] = $cur_val;
					$control_desc[] = $values[$i][1];
					$checked = "checked"; $selected = "selected"; $classname = "selected"; $data_checked = "checked";
				}
				$t->set_var($block_name . "_index", ($i + 1));
				$t->set_var($block_name . "_checked", $checked);
				$t->set_var($block_name . "_selected", $selected);
				$t->set_var($block_name . "_class", $classname);
				$t->set_var($block_name . "_classname", $classname);
				$t->set_var($block_name . "_value", htmlspecialchars($cur_val));
				$t->set_var($block_name . "_description", htmlspecialchars($values[$i][1]));
				// set template vars for special HTML JS control 
				$t->set_var("data_checked", $data_checked);
				$t->set_var("data_value", htmlspecialchars($cur_val));
				$t->set_var("data_desc", htmlspecialchars($values[$i][1]));
				$t->set_var("data_description", htmlspecialchars($values[$i][1]));

				$t->parse($block_name);
				call_event($events, AFTER_SHOW_VALUE, array("current_value" => $cur_val));
			}
		}
		if (count($control_values)) {
			$t->set_var($block_name."_selected", "selected");
		} else {
			$t->set_var($block_name."_selected", "");
		}
		$t->set_var($block_name . "_separator", $data_separator);
		$t->set_var($block_name . "_value", implode(",", $control_values));
		$t->set_var($block_name . "_description", implode($data_separator, $control_desc));

	}

	function get_ip()
	{
		return get_var("REMOTE_ADDR");
	}

	function user_country(&$country_id, &$country_code) 
	{
		global $db, $table_prefix;
		$db_rsi = $db->set_rsi("s");

		$user_id = get_session("session_user_id");
		if ($user_id) {
			$user_info = get_session("session_user_info");
			$country_id = get_setting_value($user_info, "country_id");
			$country_code = get_setting_value($user_info, "country_code");
		}
		if (!$country_id) {
			$country_code = get_setting_value($_SERVER, "GEOIP_COUNTRY_CODE");  // check country code from GeoIP service
			if ($country_code) {
				$sql  = " SELECT country_id FROM ".$table_prefix."countries ";
				$sql .= " WHERE country_code=" . $db->tosql($country_code, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$country_id = $db->f("country_id");
				}
			}
		}
		$db->set_rsi($db_rsi);
	}


	function prepare_regexp($regexp)
	{
		$escape_symbols = array("\\","/","^","\$",".","[","]","|","(",")","?","*","+","-","{","}");
		for ($i = 0; $i < sizeof($escape_symbols); $i++) {
			$regexp = str_replace($escape_symbols[$i], "\\" . $escape_symbols[$i], $regexp);
		}
		return $regexp;
	}

	function get_array_value($value_ids, $values, $glue = "")
	{
		$value_desc = "";
		if (is_array($values) && (is_array($value_ids) || strlen($value_ids))) {
			for ($i = 0; $i < sizeof($values); $i++) {
				if (is_array($value_ids)) {
					if (in_array($values[$i][0], $value_ids)) {
						if (strlen($value_desc)) { $value_desc .= $glue; }
						$value_desc .= $values[$i][1];
					}
				} elseif ($values[$i][0] == $value_ids) {
					$value_desc = $values[$i][1];
					break;
				}
			}
		}
		return $value_desc;
	}

	function get_array_id($value_desc, $values)
	{
		$value_id = "";
		if (is_array($values) && strlen($value_desc)) {
			for ($i = 0; $i < sizeof($values); $i++) {
				if ($values[$i][1] == $value_desc) {
					$value_id = $values[$i][0];
					break;
				}
			}
		}
		return $value_id;
	}

	function parse_value(&$value)
	{
		global $t, $va_messages;
		if ($value) {
			$value = get_translation($value);
			if (preg_match("/^\w+$/", $value) && defined($value)) { 
				$value = constant($value); 
			} else if (preg_match_all("/\{(\w+)\}/is", $value, $matches)) {
				for ($m = 0; $m < sizeof($matches[1]); $m++) {
					$tag = $matches[1][$m];
					if (isset($va_messages) && isset($va_messages[$tag])) {
						$value = str_replace("{".$tag."}", $va_messages[$tag], $value);
					} else if (defined($tag)) { 
						$value = str_replace("{".$tag."}", constant($tag), $value);
					} else if (isset($t)) {
						$value = str_replace("{".$tag."}", $t->get_var($tag), $value);
					}
				}
			} 
		}
		return $value;
	}

	function get_db_values($sql, $values_before, $shown_symbols = 0)
	{
		global $db;
		$db_rsi = $db->set_rsi("s");

		$values = array();

		$i = 0;
		if (is_array($values_before))
		{
			for ($j = 0; $j < sizeof($values_before); $j++)
			{
				$value_desciption = get_translation($values_before[$j][1]);
				$value_desciption = parse_value($value_desciption);
				if ($shown_symbols > 0 && strlen($value_desciption) > $shown_symbols) {
					$value_desciption = substr($value_desciption, 0, $shown_symbols) . "...";
				}
				$values[$i][0] = $values_before[$j][0];
				$values[$i][1] = $value_desciption;
				$i++;
			}
		}

		$db->query($sql);
		if ($db->next_record())
		{
			do {
				$value_desciption = get_translation($db->f(1));
				$value_desciption = parse_value($value_desciption);
				if ($shown_symbols > 0 && strlen($value_desciption) > $shown_symbols) {
					$value_desciption = substr($value_desciption, 0, $shown_symbols) . "...";
				}
				$values[$i][0] = $db->f(0);
				$values[$i][1] = $value_desciption;
				$i++;
			} while ($db->next_record());
		}

		$db->set_rsi($db_rsi);
		return $values;
	}

	function get_setting_value($settings_array, $setting_name, $default_value = "")
	{
		return (is_array($settings_array) && isset($settings_array[$setting_name]) && 
			(is_array($settings_array[$setting_name]) || strlen($settings_array[$setting_name]))) ? $settings_array[$setting_name] : $default_value;
	}

	function get_settings($types, $param_site_id = "")
	{
		global $db, $table_prefix, $site_id, $va_data;
		$db_rsi = $db->set_rsi("s");
		if (!$param_site_id) { $param_site_id = $site_id; }

		if (!isset($va_data["settings"])) { $va_data["settings"] = array(); }
		if (!isset($va_data["settings"][$param_site_id])) { $va_data["settings"][$param_site_id] = array(); }

		$settings = array();	// save result here
		if (!is_array($types)) { $types = array($types); }

		foreach ($types as $setting_type) {
			if (isset($va_data["settings"][$param_site_id][$setting_type])) {
				$type_settings = $va_data["settings"][$param_site_id][$setting_type];
			} else {
				$type_settings = array();
				$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
				$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
				$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($param_site_id, INTEGER) . ")";
				$sql .= " ORDER BY site_id ASC ";
				$db->query($sql);
				while ($db->next_record()) {
					$type_settings[$db->f("setting_name")] = $db->f("setting_value");
				}
				if ($setting_type == "global") {
					// update site data for global settings
					$sql  = " SELECT * FROM " . $table_prefix . "sites ";
					$sql .= " WHERE site_id=" . $db->tosql($site_id, INTEGER);
					$db->query($sql);
					if ($db->next_record()) {
						$type_settings["site_name"] = get_translation($db->f("site_name"));
						$type_settings["site_url"] = $db->f("site_url");
						$type_settings["secure_url"] = $db->f("site_url");
						$type_settings["admin_url"] = $db->f("admin_url");
						$type_settings["image_url"] = $db->f("image_url");
						$type_settings["site_class"] = $db->f("site_class");
						$type_settings["site_description"] = get_translation($db->f("site_description"));
						$type_settings["is_mobile"] = $db->f("is_mobile");
						$type_settings["is_mobile_redirect"] = $db->f("is_mobile_redirect");
					}
				}
				$va_data["settings"][$param_site_id][$setting_type] = $type_settings;
			}
			$settings = array_merge ($settings, $type_settings);
		}

		$db->set_rsi($db_rsi);
		return $settings;
	}

	function update_settings($setting_type, $param_site_id, $new_settings, $db_settings = "")
	{
		global $db, $table_prefix, $site_id;

		if (!is_array($db_settings)) { 
			$db_settings = get_settings($setting_type, $param_site_id);
		}
		foreach ($db_settings as $setting_name => $db_value) {
		  if (!isset($new_settings[$setting_name])) {
				$sql  = " DELETE FROM " . $table_prefix . "global_settings ";
				$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
				$sql .= " AND setting_name=" . $db->tosql($setting_name, TEXT);
				$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
				$db->query($sql);
			} else {
				if ($new_settings[$setting_name] != $db_value) {
					$sql  = " UPDATE " . $table_prefix . "global_settings ";
					$sql .= " SET setting_value=" . $db->tosql($new_settings[$setting_name], TEXT);
					$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
					$sql .= " AND setting_name=" . $db->tosql($setting_name, TEXT);
					$sql .= " AND site_id=" . $db->tosql($param_site_id,INTEGER);
					$db->query($sql);
				}
				unset($new_settings[$setting_name]); // delete updated values from array
			}
		}
		// add new records
		foreach ($new_settings as $setting_name => $setting_value) {
			$sql  = "INSERT INTO " . $table_prefix . "global_settings (setting_type, setting_name, setting_value, site_id) VALUES (";
			$sql .= $db->tosql($setting_type, TEXT) .", ";
			$sql .= $db->tosql($setting_name, TEXT) .", ";
			$sql .= $db->tosql($setting_value, TEXT) .", ";
			$sql .= $db->tosql($param_site_id,INTEGER) . ") ";
			$db->query($sql);
		}
	}

	function user_settings($user_id)
	{
		global $db, $va_data, $table_prefix;

		if(isset($va_data["user_settings"])) { 
			$user_settings = $va_data["user_settings"];
		} else {
			$db_rsi = $db->set_rsi("s");
			$user_settings = array();
			$sql  = " SELECT * ";
			$sql .= " FROM " . $table_prefix . "users u ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$user_settings = $db->f("user_settings");
				if ($user_settings) {
					$user_settings = json_decode($user_settings, true);
				}
			}
			$db->set_rsi($db_rsi);
		}
		return $user_settings;
	}

	function get_meta_desc($meta_description)
	{
		$meta_description = preg_replace("/<script.*<\\/script>/isU", "", $meta_description); // remove JS from the text
		$meta_description = preg_replace("/[\r\n\t]/", " ", $meta_description); // replace big space symbols
		$meta_description = preg_replace("/\s{2,}/", " ", $meta_description); // leave only one space between words
		$meta_description = trim(strip_tags($meta_description)); // strip HTML tags from meta description
		$meta_description = html_entity_decode($meta_description, null, "UTF-8");
		if (function_exists("mb_strlen")) {
			if (strlen(mb_strlen($meta_description, "UTF-8")) > 255) {
				$meta_description = mb_substr($meta_description, 0, 250, "UTF-8"). "...";
			}
		} else {
			if (strlen($meta_description) > 255) {
				$meta_description = substr($meta_description, 0, 250) . " ...";
			}
		}

		return $meta_description;
	}

	function get_eol()
	{
		if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
			$eol = "\r\n";
		} elseif (strtoupper(substr(PHP_OS, 0, 3) == 'MAC')) {
			$eol = "\r";
		} else {
			$eol = "\n";
		}
		return $eol;
	}

	function get_email_headers($mail_from, $mail_cc, $mail_bcc, $mail_reply_to, $mail_return_path, $mail_type, $eol = "")
	{
		$mail_headers  = "";

		if (!$eol) {
			$eol = get_eol();
		}

		$mail_headers .= "Date: " . date("r") . $eol; // RFC 2822 formatted date
		if ($mail_from) { $mail_headers .= "From: " . $mail_from . $eol; }
		if ($mail_cc)  {
			$mail_cc = str_replace(";", ",", $mail_cc);
			$mail_headers .= "cc: " . $mail_cc . $eol;
		}
		if ($mail_bcc)  {
			$mail_bcc = str_replace(";", ",", $mail_bcc);
			$mail_headers .= "Bcc: " . $mail_bcc . $eol;
		}
		if ($mail_reply_to) { $mail_headers .= "Reply-To: " . $mail_reply_to . $eol; }
		if ($mail_return_path)  { $mail_headers .= "Return-path: " . $mail_return_path . $eol; }
		$mail_headers .= "MIME-Version: 1.0";
		if (strlen($mail_type)) {
			if ($mail_type) {
				$mail_headers .= $eol . "Content-Type: text/html;" . $eol;
			} else {
				$mail_headers .= $eol . "Content-Type: text/plain;" . $eol;
			}
			$mail_headers .= "\tcharset=\"utf-8\"";
		}

		return $mail_headers;
	}

	function email_headers_string($mail_headers, $eol = "")
	{
		$headers_string  = "";

		if (!$eol) {
			$eol = get_eol();
		}
		if (!isset($mail_headers["Date"])) {
			if ($headers_string) { $headers_string .= $eol; }
			$headers_string .= "Date: " . date("r"); // RFC 2822 formatted date
		}
		foreach ($mail_headers as $header_type => $header_value) {
			parse_value($header_value);
			if ($header_type == "to") {
				$header_type = "To";
				$header_value = str_replace(";", ",", $header_value);
			} elseif ($header_type == "from") {
				$header_type = "From";
				$header_value = str_replace("\"", "\\\"", $header_value);
			} elseif ($header_type == "cc") {
				$header_type  = "Cc";
				$header_value = str_replace(";", ",", $header_value);
			} elseif ($header_type == "bcc") {
				$header_type  = "Bcc";
				$header_value = str_replace(";", ",", $header_value);
			} elseif ($header_type == "reply_to") {
				$header_type  = "Reply-To";
			} elseif ($header_type == "return_path") {
				$header_type  = "Return-path";
			} elseif ($header_type == "mail_type") {
				if (isset($mail_headers["Content-Type"])) {
					$header_type = ""; $header_value = "";
				} else {
					$header_type  = "Content-Type";
					if ($header_value == 1 || strval($header_value) == "text/html") {
						$header_value = "text/html;" . $eol;
					} else {
						$header_value = "text/plain;" . $eol;
					}
					$header_value .= "\tcharset=\"utf-8\"";
				} 
			}
			if ($header_type && strlen($header_value)) {
				if ($headers_string) { $headers_string .= $eol; }
				$headers_string .= $header_type . ": " . $header_value;
			}
		}
		if (!isset($mail_headers["Message-ID"])) {
			if ($headers_string) { $headers_string .= $eol; }
			$server_name = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "localhost";
			$message_id = uniqid(time().mt_rand());
			$headers_string .= "Message-ID: <".$message_id."@".$server_name.">"; // RFC 2822 formatted date
		}
		if (!isset($mail_headers["MIME-Version"])) {
			if ($headers_string) { $headers_string .= $eol; }
			$headers_string .= "MIME-Version: 1.0";
		}

		return $headers_string;
	}

	function call_event($events, $event_name, $additional_parameters = "")
	{
		if (is_array($events) && isset($events[$event_name]) && function_exists($events[$event_name])) {
			if (isset($events[$event_name . "_params"]) && is_array($events[$event_name . "_params"])) {
				$event_parameters = $events[$event_name . "_params"];
			} else {
				$event_parameters = array();
			}
			if (is_array($additional_parameters)) {
				foreach ($additional_parameters as $key => $value) {
					$event_parameters[$key] = $value;
				}
			}
			$event_parameters["event"] = $event_name;
			call_user_func($events[$event_name], $event_parameters);
		}
	}

	function get_db_value($sql)
	{
		global $db;

		$db->query($sql);
		if ($db->next_record()) {
			return $db->f(0);
		} else  {
			return "";
		}
	}

	function get_page_url()
	{
		$server_name = getenv("SERVER_NAME");
		$request_uri = getenv("REQUEST_URI");
	}

	function get_script_name()
	{
		global $current_page;
		if (isset($current_page) && $current_page) {
			$script_name = $current_page;
		} elseif (get_var("PHP_SELF")) {
			$script_name = get_var("PHP_SELF");
		} elseif (get_var("SCRIPT_NAME")) {
			$script_name = get_var("SCRIPT_NAME");
		} elseif (get_var("SCRIPT_FILENAME")) {
			$script_name = get_var("SCRIPT_FILENAME");
		} elseif (get_var("REQUEST_URI")) {
			$script_name = get_var("REQUEST_URI");
		} else {
			$script_name = get_var("SCRIPT_URL");
		}

		return basename($script_name);
	}

	function get_request_page()
	{
		global $current_page;
		$request_page = get_var("REQUEST_URI");
		if (!strlen($request_page)) { $request_page = get_var("URL"); }
		if (!strlen($request_page)) { $request_page = get_var("HTTP_X_REWRITE_URL"); }
		if (!strlen($request_page) 
			&& isset($_SERVER["SERVER_SOFTWARE"]) && preg_match("/IIS/i", $_SERVER["SERVER_SOFTWARE"]) 
			&& isset($_SERVER["QUERY_STRING"]) && preg_match("/^404;/i", $_SERVER["QUERY_STRING"])
		) { 
			// IIS 404 Error
			$request_page = preg_replace("/^404;/", "", $_SERVER["QUERY_STRING"]); 
		}
		$request_page = preg_replace("/\?.*$/", "", $request_page);
		if (!$request_page || substr($request_page, -1) == "/") {
			if (get_var("SCRIPT_URL")) {
				$request_page = get_var("SCRIPT_URL");
			} elseif (isset($current_page) && $current_page) {
				$request_page = $current_page;
			} elseif (get_var("SCRIPT_NAME")) {
				$request_page = get_var("SCRIPT_NAME");
			} elseif (get_var("PHP_SELF")) {
				$request_page = get_var("PHP_SELF");
			}
		}

		return basename($request_page);
	}

	function get_request_uri()
	{
		$server = get_var("SERVER_SOFTWARE");
		$request_uri = get_var("REQUEST_URI");
		if (!strlen($request_uri)) { $request_uri = get_var("URL"); }
		if (!strlen($request_uri)) { $request_uri = get_var("HTTP_X_REWRITE_URL"); }
		if (!strlen($request_uri) 
			&& isset($_SERVER["SERVER_SOFTWARE"]) && preg_match("/IIS/i", $_SERVER["SERVER_SOFTWARE"]) 
			&& isset($_SERVER["QUERY_STRING"]) && preg_match("/^404;/i", $_SERVER["QUERY_STRING"]))
		{ 
			// IIS 404 Error
			$request_uri = preg_replace("/^404;/", "", $_SERVER["QUERY_STRING"]); 
		}

		if (!strlen($request_uri)) {
			$request_uri = get_var("SCRIPT_NAME");
			if (!$request_uri) { $request_uri = get_var("SCRIPT_URL"); }
			if (!$request_uri) { $request_uri = get_var("PHP_SELF"); }
			$query_string = get_var("QUERY_STRING");
			if (strlen($query_string)) {
				$request_uri .= "?" . $query_string;
			}
		}
		return $request_uri;
	}

	function get_request_path($request_uri = "")
	{
		if ($request_uri === "") { $request_uri = get_request_uri(); }
		if (preg_match("/^https?:\\/\\/[^\\/]+([^\\?]+)/i", $request_uri, $match)) {
			$request_uri_path = $match[1];
		} else if (preg_match("/^([^\\?]+)/i", $request_uri, $match)) {
			$request_uri_path = $match[1];
		} else {
			$request_uri_path = "/";
		}
		return $request_uri_path;
	}

	function check_user_session()
	{
		global $is_ssl, $settings;
		if (!strlen(get_session("session_user_id"))) {
			$site_url = get_setting_value($settings, "site_url", "");
			$secure_url = get_setting_value($settings, "secure_url", "");
			$secure_user_login = get_setting_value($settings, "secure_user_login", 0);
			if ($secure_user_login) {
				$user_login_url = $secure_url . get_custom_friendly_url("user_login.php");
			} else {
				$user_login_url = $site_url . get_custom_friendly_url("user_login.php");
			}
			if ($is_ssl) {
				$page_site_url = $secure_url;
			} else {
				$page_site_url = $site_url;
			}
			$return_page = get_request_uri();
			if (preg_match("/^https?:\\/\\/[^\\/]+(\\/.*)$/i", $page_site_url, $matches)) {
				$page_path_regexp = prepare_regexp($matches[1]);
				if (preg_match("/^" .$page_path_regexp. "/i", $return_page)) {
					$return_page = $page_site_url . preg_replace("/^" .$page_path_regexp. "/i", "", $return_page);
				} 
			}

			header ("Location: " . $user_login_url . "?return_page=" . urlencode($return_page) . "&type_error=1");
			exit;
		}
	}

	function check_user_security($setting_name = "")
	{
		global $db, $settings, $table_prefix;
		check_user_session();

		if ($setting_name) {
			$sql  = " SELECT setting_value ";
			$sql .= " FROM " . $table_prefix . "user_types_settings ";
			$sql .= " WHERE type_id=" . $db->tosql(get_session("session_user_type_id"), INTEGER);
			$sql .= " AND setting_name=" . $db->tosql($setting_name, TEXT);
			$allow_access = get_db_value($sql);
			if (!$allow_access) {
				$site_url = get_setting_value($settings, "site_url", "");
				$user_home_url = $site_url . get_custom_friendly_url("user_home.php");
				header ("Location: " . $user_home_url);
				exit;
			}
		}
	}


	// OLD way to check black IP
	function check_black_ip($ip_address = "", $address_action = 1)
	{
		global $db, $table_prefix;

		if (!$ip_address) {
			$ip_address = get_ip();
		}
		$ip_parts = explode(".", $ip_address);
		$where = " WHERE address_action=" . $db->tosql($address_action, INTEGER) . " AND (";
		$ip_where = "";
		for ($i = 0; $i < sizeof($ip_parts); $i++) {
			if ($i) {
				$ip_where .= ".";
				$where .= " OR ";
			}
			$ip_where .= $ip_parts[$i];
			$where .= " ip_address=" . $db->tosql($ip_where, TEXT);
		}
		$where .= ") ";
		$sql = " SELECT COUNT(*) FROM ".$table_prefix."black_ips " . $where;

		$black_ips = get_db_value($sql);

		return $black_ips;
	}

	function blacklist_check($module = "site", $ips_check = "")
	{
		global $db, $table_prefix;
		$ip_addresses = array(); $black_ips = array(); $single_check = false; $module_rule = ""; $ip_loop = 0;
		if (!is_array($ips_check)) { 
			$single_check = true;
			if (!$ips_check) { $ips_check = get_ip(); }
			$ip_addresses = array($ips_check => $ips_check); 
		} else {
			foreach ($ips_check as $ip_address) {
				$ip_addresses[$ip_address] = $ip_address;
			}
		}

		while (count($ip_addresses) && $ip_loop < 8) {
			$ip_loop++; $where = "";
			foreach ($ip_addresses as $ip_key => $ip_address) {
				$where .= ($where) ? " OR " : " WHERE ";
				$where .= " ip_address=" . $db->tosql($ip_key, TEXT);
			}
			$sql  = " SELECT ip_address, ip_rules FROM ".$table_prefix."black_ips ";
			$sql .= $where;
			$db->query($sql);
			while ($db->next_record()) {
				$ip_key = $db->f("ip_address"); 
				$ip_rules = json_decode($db->f("ip_rules"), true);
				$ip_rule = get_setting_value($ip_rules, $module, "blocked");
				$ip_address = $ip_addresses[$ip_key];
				$black_ips[$ip_address] = array("rule" => $ip_rule, "range" => $ip_key);
				unset($ip_addresses[$ip_key]);
			}
			// check for next IP range
			$ips = $ip_addresses; $ip_addresses = array();
			foreach ($ips as $ip_key => $ip_address) {
				// check for next IP range
				if (preg_match("/[\.\:]+[0-9a-f]+$/i", $ip_key)) {
					$ip_key = preg_replace("/[\.\:]+[0-9a-f]+$/i", "", $ip_key);
				} else {
					$ip_key = "";
				}
				if ($ip_key) { $ip_addresses[$ip_key] = $ip_address; }
			}
		} 

		if ($single_check) {
			return isset($black_ips[$ips_check]) ? $black_ips[$ips_check]["rule"] : "";
		} else {
			return ($black_ips);
		}
	}

	function check_banned_content($message)
	{
		global $db, $table_prefix;

		$is_banned = false; $banned_regexp = "";
		$sql = " SELECT content_text FROM ".$table_prefix."banned_contents ";
		$db->query($sql);
		while ($db->next_record()) {
			if ($banned_regexp) { $banned_regexp .= "|"; }
			$content_text = $db->f("content_text");
			$banned_regexp .= "(" . prepare_regexp($content_text) . ")";
		}

		if ($banned_regexp) {
			$is_banned = preg_match("/".$banned_regexp."/is", $message);
		}

		return $is_banned;
	}

	function hmac_md5 ($data, $key)
	{
		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.

		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			$key = pack("H*",md5($key));
		}
		$key  = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
	}

	function set_menus(&$menus, $parent_id, $level, $menu_active_code = "")
	{
		global $t, $settings, $current_page, $language_code;

		if (!is_array($menus) || !count($menus)) {
			return;
		}
		// clear previous data and check active menu
		if ($level == 0) { 
			$t->set_var("menus", "");  // clear menus

			// init variable to match active menu
			$site_url = get_setting_value("site_url", $settings, "");
			$parsed_url = parse_url($site_url);
			$site_path = isset($parsed_url["path"]) ? $parsed_url["path"] : "/";
			$request_uri_base = basename(get_request_uri());
			$request_uri_path = get_request_path();
			$request_basename = basename($request_uri_path);
			$request_page     = get_request_page();
			if (!isset($current_page)) { $current_page = $request_page; }

			// check active menu items
			foreach ($menus as $menu_id => $menu_data) {
				if (isset($menu_data["menu_url"])) {
					$menu_url = $menu_data["menu_url"];
					$menu_code = isset($menu_data["menu_code"]) ? $menu_data["menu_code"] : "";
					$match_type = isset($menu_data["match_type"]) ? $menu_data["match_type"] : 2;
					$menu_active = isset($menu_data["active"]) ? $menu_data["active"] : false; 
					$menu_friendly_url = get_custom_friendly_url($menu_url);
					$menu_basename = basename($menu_friendly_url);
					$url_matched = false;
					if (($request_uri_base == $menu_basename) || ($menu_active_code && $menu_active_code == $menu_code)) {
						// full match by menu code or by Request URI
						$url_matched = true;
					} else if ($match_type > 0) {
						if ($request_basename == $menu_basename) {
							$url_matched = true;
						}
						if ($url_matched && $match_type == 2 && strpos($menu_url, "?")) {
							$menu_request_uri = preg_replace("/\#.*$/", "", $menu_url);
							$menu_request_uri = preg_replace("/^.*\?/", "", $menu_request_uri);
							if ($menu_request_uri) {
								$menu_params = explode("&", $menu_request_uri);
								for($s = 0; $s < sizeof($menu_params); $s++) {
									if (preg_match("/^(.+)=(.+)$/", $menu_params[$s], $matches)) {
										$param_name = $matches[1];
										$menu_param_value = $matches[2];
										$request_param_value = get_param($param_name);
										if (strval($menu_param_value) != strval($request_param_value)) {
											$url_matched = false;
										}
									}
								}
							}
						}
					}
        
					if ($menu_url == "index.php") {
						$menu_url = $site_url;
					} if (preg_match("/^\//", $menu_url)) {
						$menu_url = preg_replace("/^".preg_quote($site_path, "/")."/i", "", $menu_url);
						$menu_url = $site_url . get_custom_friendly_url($menu_url);
					} else if (!preg_match("/^http\:\/\//", $menu_url) && !preg_match("/^https\:\/\//", $menu_url) && !preg_match("/^javascript\:/", $menu_url)) {
						$menu_url = $site_url . $menu_friendly_url;
					}
					$menus[$menu_id]["active"] = ($menu_active || $url_matched); // child could activate parent menu before
					$menus[$menu_id]["menu_url"] = $menu_url;
					if ($url_matched) {
						$active_parent_id = isset($menus[$menu_id]["parent"]) ? $menus[$menu_id]["parent"] : "";
						while ($active_parent_id) {
							$menus[$active_parent_id]["active"] = true;
							$active_parent_id = isset($menus[$active_parent_id]["parent"]) ? $menus[$active_parent_id]["parent"] : "";
						}
					}
				}
			}
		}
		
		$subs = (isset($menus[$parent_id]) && isset($menus[$parent_id]["subs"])) ? $menus[$parent_id]["subs"] : array();
		asort($subs); // sort menu items first to show them in correct order
		
		$menu_index = 0; $menu_count = count($subs);
		foreach ($subs as $show_menu_id => $menu_order)
		{
			$menu_index++;

			$menu_type = isset($menus[$show_menu_id]["menu_type"]) ? $menus[$show_menu_id]["menu_type"] : "";
			$menu_html = isset($menus[$show_menu_id]["menu_html"]) ? $menus[$show_menu_id]["menu_html"] : "";
			$menu_block = isset($menus[$show_menu_id]["block"]) ? $menus[$show_menu_id]["block"] : "";
			$menu_class = isset($menus[$show_menu_id]["menu_class"]) ? $menus[$show_menu_id]["menu_class"] : "";
			$menu_pos   = isset($menus[$show_menu_id]["menu_pos"]) ? $menus[$show_menu_id]["menu_pos"] : "";
			if ($menu_pos == "right") {
				$menu_class = trim($menu_class ." nav-right");
			}

			if ($menu_type == "html" || $menu_type == "custom") {
				$t->set_block("menu_html", $menu_html);
				$t->parse_to("menu_html", "menus");
				continue;
			} else if ($menu_block) {
				$t->set_var("menu_class", $menu_class);
				$t->parse_to($menu_block, "menus", true);
				continue;
			}

			$menu_url           = $menus[$show_menu_id]["menu_url"];
			$menu_target        = $menus[$show_menu_id]["menu_target"];
			$menu_title         = $menus[$show_menu_id]["menu_title"];
			$menu_image         = $menus[$show_menu_id]["menu_image"];
			$menu_image_active  = $menus[$show_menu_id]["menu_image_active"];
			$menu_active        = $menus[$show_menu_id]["active"];

			$has_nested    = (isset($menus[$show_menu_id]["subs"]) && is_array($menus[$show_menu_id]["subs"]) && count($menus[$show_menu_id]["subs"])) ? true : false;
			
			if ($has_nested) {
				set_menus($menus, $show_menu_id, $level + 1, $menu_active_code);
			}

			if ($menu_active) {				
				$menu_class = trim($menu_class." nav-active");
				$menu_image = $menu_image_active;
			}
			if ($has_nested) {
				$menu_class = trim($menu_class." nav-childs");
			}

			$t->set_var("menu_href",  htmlspecialchars($menu_url));
			if ($menu_target) {
				$t->set_var("menu_target", "target=\"".htmlspecialchars($menu_target)."\"");
			} else {
				$t->set_var("menu_target", "");
			}
			$t->set_var("menu_class", $menu_class);
			$t->set_var("menu_title", $menu_title);

			if ($menu_image) {
				$t->set_var("alt", htmlspecialchars($menu_title));
				$t->set_var("src", htmlspecialchars($menu_image));
				$t->sparse("menu_image", false);
			} else {
				$t->set_var("menu_image", "");
			}

			if (strlen($menu_title)) {
				$t->sparse("menu_text", false);
			}

			
			if ($has_nested) {
				$t->set_var("submenus", $t->get_var("submenus_" . ($level + 1)));
				$t->parse("submenus_block");
				$t->set_var("submenus_" . ($level + 1), "");
				$t->set_var("submenus", "");
			} else {
				$t->set_var("submenus_block", "");
			}
			
			if ($level > 0) {
				$t->parse_to("menus", "submenus_" . $level);
			} else {
				$t->parse("menus");
			}
			
			if ($has_nested) {
				$t->set_var("submenus_block", "");
			}
			
			if ($menu_index == $menu_count && $level == 0) {
				//$t->parse("menus_rows");
				//$t->set_var("menus", "");
			}
		}
	}
	
	function show_categories(&$categories, $parent_id, $level, $active_category_id)
	{
		global $t;
		
		$subs = (isset($categories[$parent_id]) && isset($categories[$parent_id]["subs"])) ? $categories[$parent_id]["subs"] : array();
		asort($subs); // sort category items first to show them in correct order
		
		$category_index = 0; $category_count = count($subs);
		foreach ($subs as $show_category_id => $category_order)
		{
			$category_index++;

			$category_block = isset($categories[$show_category_id]["block"]) ? $categories[$show_category_id]["block"] : "";
			if ($category_block) {
				$t->parse($category_block, false);
				continue;
			}

			$category_url   = $categories[$show_category_id]["category_url"];
			$category_name  = $categories[$show_category_id]["category_name"];
			$image_small    = $categories[$show_category_id]["image_small"];
			$image_large    = $categories[$show_category_id]["image_large"];
			$category_class = $categories[$show_category_id]["category_class"];

			$has_nested    = (isset($categories[$show_category_id]["subs"]) && is_array($categories[$show_category_id]["subs"]) && count($categories[$show_category_id]["subs"])) ? true : false;
			
			if ($has_nested) {
				show_categories($categories, $show_category_id, $level + 1, $active_category_id);
			}

			$is_active = false;
			if ($show_category_id == $active_category_id) {
				$is_active = true;
			} 			
			if ($is_active) {				
				$category_class = trim($category_class." nav-active");
			}
			if ($has_nested) {
				$category_class = trim($category_class." nav-childs");
			}

			$t->set_var("category_url",  htmlspecialchars($category_url));
			$t->set_var("category_class", $category_class);
			$t->set_var("category_name", $category_name);

			if ($has_nested) {
				$t->set_var("subcategories", $t->get_var("subcategories_" . ($level + 1)));
				$t->parse("subcategories_list");
				$t->set_var("subcategories_" . ($level + 1), "");
				$t->set_var("subcategories", "");
			} else {
				$t->set_var("subcategories_list", "");
			}
			
			if ($level > 0) {
				$t->parse_to("category_item", "subcategories_" . $level);
			} else {
				$t->parse("category_item");
			}
			
			if ($has_nested) {
				$t->set_var("subcategories_list", "");
			}
			
			if ($category_index == $category_count && $level == 0) {
				$t->parse("categories_list");
				$t->set_var("category_item", "");
			}
		}
	}

	function set_tree(&$nodes, $parent_id, $level, $active_nodes, $image_type = 1, $tree_type = "")
	{
		global $t, $restrict_categories_images;
		if ($level == 0) { $t->set_var("nodes", ""); } // clear nodes if we start from the top level
		if (!is_array($active_nodes)) { $active_nodes = array($active_nodes); }

		$subs = (isset($nodes[$parent_id]) && isset($nodes[$parent_id]["subs"])) ? $nodes[$parent_id]["subs"] : array();
		asort($subs); // sort category items first to show them in correct order
		
		foreach ($subs as $show_node_id => $node_order) 
		{
			$node_title   = $nodes[$show_node_id]["title"];
			$node_url     = $nodes[$show_node_id]["url"];
			$a_title      = isset($nodes[$show_node_id]["a_title"]) ? $nodes[$show_node_id]["a_title"] : "";
			$node_class   = isset($nodes[$show_node_id]["node_class"]) ? $nodes[$show_node_id]["node_class"] : "";
			if (!$node_class && isset($nodes[$show_node_id]["class"])) { $node_class = $nodes[$show_node_id]["class"]; }
			$subs_number   = isset($nodes[$show_node_id]["subs_number"]) ? $nodes[$show_node_id]["subs_number"] : 0; // number of categories which could be loaded 
			$has_nested    = isset($nodes[$show_node_id]["subs"]) ? is_array($nodes[$show_node_id]["subs"]) : false;
			$is_restricted = isset($nodes[$show_node_id]["allowed"]) ? !$nodes[$show_node_id]["allowed"] : false;
			
			if ($has_nested) {
				set_tree($nodes, $show_node_id, $level + 1, $active_nodes, $image_type, $tree_type);
			}                                         
			$t->set_var("node_url", htmlspecialchars($node_url));

			if (in_array($show_node_id, $active_nodes)) {
				$node_class .= " node-active";
			} 			
			if ($has_nested || $subs_number) {
				$node_class .= " node-childs";
				if ($has_nested && $tree_type != "7") {
					$node_class .= " node-open";
				}
			}
			$node_class = trim($node_class);
	
			$node_image = ""; $image_alt = "";
			if ($image_type == 2) {
				$node_image = isset($nodes[$show_node_id]["image_small"]) ? $nodes[$show_node_id]["image_small"] : "";
				$image_alt = isset($nodes[$show_node_id]["image_small_alt"]) ? $nodes[$show_node_id]["image_small_alt"] : "";
			} else if ($image_type == 3) {
				$node_image = isset($nodes[$show_node_id]["image_large"]) ? $nodes[$show_node_id]["image_large"] : "";
				$image_alt = isset($nodes[$show_node_id]["image_large_alt"]) ? $nodes[$show_node_id]["image_large_alt"] : "";
			} else {
				$node_image = false;
			}
			$short_description = isset($nodes[$show_node_id]["short_description"]) ? $nodes[$show_node_id]["short_description"] : "";
			
			$t->set_var("node_id", $show_node_id);
			$t->set_var("node_title", $node_title);
			$t->set_var("a_title", htmlspecialchars($a_title));
			$t->set_var("level", $level);
			
			$t->set_var("node_class", $node_class);		
			if ($is_restricted) {
				$t->set_var("restricted_class", " restricted ");
			} else {
				$t->set_var("restricted_class", "");
			}
			
			if ($node_image) 
			{
				if (!preg_match("/^(http|https|ftp|ftps)\:\/\//", $node_image)) {
					if (isset($restrict_categories_images) && $restrict_categories_images) {
						$node_image = "image_show.php?node_id=".$show_node_id;
					}
				}
				if (!strlen($image_alt)) { $image_alt = $node_title; }
				$t->set_var("alt", htmlspecialchars($image_alt));
				$t->set_var("src", htmlspecialchars($node_image));
				$t->parse("node_image", false);
			} else {
				$t->set_var("node_image", "");
			}
			
			if ($has_nested) {
				$t->set_var("subnodes", $t->get_var("subnodes_" . ($level + 1)));
				$t->parse("subnodes_block");
				$t->set_var("subnodes_" . ($level + 1), "");
				$t->set_var("subnodes", "");
			} else {
				$t->set_var("subnodes_block", "");
			}		

			if ($level > 0) {
				$t->parse_to("nodes", "subnodes_" . $level);
			} else {
				$t->parse("nodes");
			}	
			
			if ($has_nested) {
				$t->set_var("subnodes_block", "");
			}		
		}		
	}


	function process_level_colors($message) {
				
		$eol = get_eol();
		// set level colors
		$level_colors = array(
			"0"=>"black", "1"=>"blue", "2"=>"red", "3"=>"green", "4"=>"gray", "5"=>"navy", "6"=>"olive", "7"=>"brown", "8"=>"purple"
		);
		
		$msg_strings = explode("\n", $message);
		$message     = "";
		$last_level  = 0;
		$ln = 0;

		$message .= "<div style=\"color:".$level_colors[0].";\">";
		foreach ($msg_strings as $line) {
			$ln++;
			//-- get current level
			if (preg_match("/^>+/", $line, $match)) {
				$cur_level = strlen($match[0]);
			} else {
				$cur_level = 0;
			}
			$line = preg_replace("/^>+/", "", $line);
			if (!trim($line)) { $line = "&nbsp;"; }
			$level_diff = $last_level - $cur_level;
			if ($level_diff > 0) {
				$tags = "";
				for ($t = 1; $t <= $level_diff; $t++) {
					$tags .="</div>";
				}
				$line = $tags . $line;
			} elseif ($level_diff < 0) {
				$tags = "";
				for ($t = $last_level; $t < $cur_level; $t++) {
					$tags .="<div style='color:". $level_colors[($t + 1) % 9] ."; margin-left: 5pt; padding-left: 5pt; border-left-style:solid; border-left-width:thin;'>";
				}
				$line = $tags . $line;
			} else {
				if ($ln > 1) {
					$line = "<br>".$eol.$line;
				}
			}
			$last_level = $cur_level;
			$message .= $line;
		}

		//-- add end tags
		$tags = "";
		for ($t = 1; $t <= $last_level; $t++) {
			$tags .= "</div>";
		}
		$message .= $tags . "</div>";
		
		return $message;
	}

	function process_message($message, $icons_enable = 0, $allow_bbcode = false, $convert_links = 1, $convert_long_words = 1, $symbols = 128)
	{
		global $icons_codes, $icons_tags;

		$eol = get_eol();
		
		if ($convert_long_words) {
			split_long_words($message, $convert_links, $symbols);
		}

		$message = preg_replace("/</", "&lt;", $message);
		$message = preg_replace("/!^>/", "&gt;", $message);
		
		if ($allow_bbcode) {

			$bb_message = $message;
			$message = "";
			$opened_tags = array();
			$open_tags = array(
				"b" => "<b>",
				"u" => "<u>",
				"i" => "<i>",
				"list" => "<ul>",
				"*" => "<li>",
				"code" => "<code>",
				"quote" => "<div style=\"border: 1px solid lightgray; background-color: white; padding: 5px; margin: 5px;\">",
				"url" => "<a rel=\"nofollow\" target=\"_blank\" href=\"",
				"mail" => "<a rel=\"nofollow\" href=\"mailto:",
				"color" => "<span style=\"color: ",
				"size" => "<span style=\"font-size: ",
				"font" => "<span style=\"font-family: ",
			);
			$close_tags = array(
				"b" => "</b>",
				"u" => "</u>",
				"i" => "</i>",
				"list" => "</ul>",
				"*" => "</li>",
				"code" => "</code>",
				"quote" => "</div>",
				"url" => "</a>",
				"mail" => "</a>",
				"color" => "</span>",
				"size" => "</span>",
				"font" => "</span>",
			);

			$pos = strpos($bb_message, "[");
			while($pos !== false) {
				$message .= substr($bb_message, 0, $pos);
				$bb_message = substr($bb_message, $pos);
				if (preg_match("/^\[img\]\s*([^\['\"\s]+)\s*\[\/img\]/", $bb_message, $matches)) {
					$tag = $matches[0];
					$img_src = trim($matches[1]);
					$bb_message = substr($bb_message, strlen($tag));
					$message .= "<img src=\"".$img_src."\" border=\"0\" />";
				} else if (preg_match("/^\[url\]\s*([^\['\"\s]+)\s*\[\/url\]/", $bb_message, $matches)) {
					$tag = $matches[0];
					$url = trim($matches[1]);
					$url_name = preg_replace("/^(http|https|ftp|ftps):\/\//", "", $url);
					if (strlen($url_name) > 32) {
						$url_name = substr($url_name, 0, 30) . "...";
					}
					$bb_message = substr($bb_message, strlen($tag));
					$message .= "<a rel=\"nofollow\" target=\"_blank\" href=\"".$url."\">".$url_name."</a>";
				} else if (preg_match("/^\[(url|color|size|font|mail)=['\"]?\s*([^]'\"]+)\s*['\"]?\]/", $bb_message, $matches)) {
					$tag = $matches[0];
					$tag_name = $matches[1];
					$tag_value = $matches[2];
					if($tag_name == 'size'){
						$tag_value .='pt';
					}
					$opened_tags[] = $tag_name;
					$bb_message = substr($bb_message, strlen($tag));
					$message .= $open_tags[$tag_name] . $tag_value . "\">";
				} else if (preg_match("/^\[(b|u|i|list|code|quote|\*)\]/", $bb_message, $matches)) {
					$tag = $matches[0];
					$tag_name = $matches[1];
					if ($tag_name == "*" || $tag_name == "list") {
						// remove and close opened list element
						for ($lt = sizeof($opened_tags) - 1; $lt >= 0; $lt--) {
							$open_tag = $opened_tags[$lt]; 
							if ($open_tag == "*") {
								array_splice($opened_tags, $lt, 1);
								$message .= $close_tags["*"];
								break;
							}
						}
					}
					$opened_tags[] = $tag_name;
					$bb_message = substr($bb_message, strlen($tag));
					// remove precedent spaces for some blocks
					if (preg_match("/quote|list|\*/i", $tag_name)) {
						$message = rtrim($message);
					}
					$message .= $open_tags[$tag_name];
					// remove following spaces for some blocks
					if (preg_match("/quote|list|\*/i", $tag_name)) {
						$bb_message = ltrim($bb_message);
					}
				} else if (preg_match("/^\[\/(b|u|i|list|code|quote|url|color|size|font|mail|\*)\]/", $bb_message, $matches)) {
					$tag = $matches[0];
					$close_tag = $matches[1];
					$tags_size = sizeof($opened_tags);
					$bb_message = substr($bb_message, strlen($tag));

					// remove precedent spaces for some blocks
					if (preg_match("/quote|list|\*/i", $tag_name)) {
						$message = rtrim($message);
					}

					// close opened list elements
					if ($close_tag == "list") {
						for ($lt = sizeof($opened_tags) - 1; $lt >= 0; $lt--) {
							$open_tag = $opened_tags[$lt]; 
							if ($open_tag == "*") {
								array_splice($opened_tags, $lt, 1);
								$message .= $close_tags["*"];
								break;
							}
						}
					}
					// check and close tag if we open it
					for ($lt = sizeof($opened_tags) - 1; $lt >= 0; $lt--) {
						$tag_name = $opened_tags[$lt]; 
						if ($close_tag == $tag_name) {
							array_splice($opened_tags, $lt, 1);
							$message .= $close_tags[$close_tag];
							break;
						}
					}

					// remove following spaces for some blocks
					if (preg_match("/quote|list|\*/i", $tag_name)) {
						$bb_message = ltrim($bb_message);
					}
				} else {
					$bb_message = substr($bb_message, 1);
					$message .= "[";
				}

				// find next position
				$pos = strpos($bb_message, "[");
			}
			$message .= $bb_message;
			// close all open tags
			foreach ($opened_tags as $key => $tag_name) {
				$message .= $close_tags[$tag_name];
			}
		} 
		if ($convert_links) {
			if (preg_match_all("/(.*)(https?:\\/\\/[\w\d]+[^\s]+)(.*)/i", $message, $matches)) {
				$links = array();
				for ($p = 0; $p < sizeof($matches[0]); $p++) {
					$before_link = $matches[1][$p];
					$link = $matches[2][$p];
					$after_link = $matches[3][$p];
					if ((!strlen($before_link) || preg_match("/\s$/", $before_link)) && 
						(!strlen($after_link) || preg_match("/^\s/", $after_link))) {
						if ($convert_long_words && strlen($link) > $symbols) {
							$link_name = substr($link, 0, $symbols) . "...";
						} else {
							$link_name = $link;
						}
						$message = str_replace($before_link.$link.$after_link, $before_link."<a rel=\"nofollow\" href=\"" . htmlspecialchars($link) . "\" target=\"_blank\">" . htmlspecialchars($link_name) . "</a>".$after_link, $message);
					}
				}
			}
		}
		
		$message = str_replace("\r","", $message);
		if ($icons_enable && is_array($icons_codes)) { // replace emotion icons
			$message = str_replace($icons_codes, $icons_tags, $message);
		}		
		$message = process_level_colors($message);

		return $message;
	}

	function get_tax_amount($tax_rates, $item_type, &$price, $quantity, $item_tax_id, $tax_free, &$tax_percent, $default_tax_rates = "", $return_type = 1, $tax_prices_type = "", $tax_round = "")
	{
		global $settings, $currency;

		if ($quantity <= 0) { $quantity = 1; } // used to calculated fixed tax
		$taxes_values = array();
		$item_tax_amount = 0; 
		if (!strlen($tax_prices_type)) {
			$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
		}
		if (!strlen($tax_round)) {
			$tax_round = get_setting_value($settings, "tax_round", 1);
		}
		$decimals = get_setting_value($currency, "decimals", 2);

		if (is_array($default_tax_rates) && $tax_prices_type == 1 && !$tax_free) {
			// if price includes tax check if default taxes different from user taxes
			$tax_rates_identical = true;
			if (is_array($tax_rates)) {
				$tax_rates_identical = ($tax_rates == $default_tax_rates);
			} else {
				$tax_rates_identical = false;
			}

			if (!$tax_rates_identical) {
				// calculate price without tax to apply different tax rates
				$default_tax_percent = 0; $default_fixed_tax = 0;
				foreach ($default_tax_rates as $id => $tax_rate) {
					// check if tax should be used for current item
					$tax_id = $tax_rate["tax_id"];
					$tax_type = $tax_rate["tax_type"]; 
					if ($tax_type == 1 || ($tax_type == 2 && $item_tax_id == $tax_id)) {
						// check default tax percent
						if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["tax_percent"]) 
							&& strlen($tax_rate["types"][$item_type]["tax_percent"])) {
							$default_tax_percent += $tax_rate["types"][$item_type]["tax_percent"];
						} else {
							$default_tax_percent += $tax_rate["tax_percent"];
						}
						// check default tax amount
						if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["fixed_amount"]) 
							&& strlen($tax_rate["types"][$item_type]["fixed_amount"])) {
							$default_fixed_tax += doubleval($tax_rate["types"][$item_type]["fixed_amount"]) * $quantity;
						} else {
							$default_fixed_tax += doubleval($tax_rate["fixed_amount"]) * $quantity;
						}
					}
				}
				// deduct default tax
				$price_excl_tax = (($price * 100) / ($default_tax_percent + 100)) - $default_fixed_tax;

				$tax_percent = 0; $fixed_tax = 0;
				if (is_array($tax_rates)) {
					foreach ($tax_rates as $id => $tax_rate) {
						// check if tax should be used for current item
						$tax_id = $tax_rate["tax_id"];
						$tax_type = $tax_rate["tax_type"]; 
						if ($tax_type == 1 || ($tax_type == 2 && $item_tax_id == $tax_id)) {
							// check tax percent
							if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["tax_percent"]) 
								&& strlen($tax_rate["types"][$item_type]["tax_percent"])) {
								$tax_percent += $tax_rate["types"][$item_type]["tax_percent"];
							} else {
								$tax_percent += $tax_rate["tax_percent"];
							}
					  
							// check tax amount 
							if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["fixed_amount"]) 
								&& strlen($tax_rate["types"][$item_type]["fixed_amount"])) {
								$fixed_tax += doubleval($tax_rate["types"][$item_type]["fixed_amount"]) * $quantity;
							} else {
								$fixed_tax += doubleval($tax_rate["fixed_amount"]) * $quantity;
							}
						}
					}
				}
				// calculate price with current tax
				$price = va_round($price_excl_tax + (($price_excl_tax * $tax_percent) / 100) + $fixed_tax, $decimals);
			}
		}

		if (!isset($tax_percent)) { $tax_percent = 0; }
		if (!$tax_free) {
			// calculate summary tax
			$tax_percent = 0; $fixed_tax = 0; $item_tax_amount = 0;
			if (is_array($tax_rates)) {
				foreach ($tax_rates as $id => $tax_rate) {
					// check if tax should be used for current item
					$tax_id = $tax_rate["tax_id"];
					$tax_type = $tax_rate["tax_type"]; 
					if ($tax_type == 1 || ($tax_type == 2 && $item_tax_id == $tax_id)) {
						$current_tax_percent = 0; $current_fixed_tax = 0; $current_item_tax = 0;
						// check tax percent
						if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["tax_percent"]) 
							&& strlen($tax_rate["types"][$item_type]["tax_percent"])) {
							$current_tax_percent = $tax_rate["types"][$item_type]["tax_percent"];
						} else {
							$current_tax_percent = $tax_rate["tax_percent"];
						}
						// check tax amount 
						if (isset($tax_rate["types"][$item_type]) && isset($tax_rate["types"][$item_type]["fixed_amount"]) 
							&& strlen($tax_rate["types"][$item_type]["fixed_amount"])) {
							$current_fixed_tax = doubleval($tax_rate["types"][$item_type]["fixed_amount"]) * $quantity;
						} else {
							$current_fixed_tax = doubleval($tax_rate["fixed_amount"]) * $quantity;
						}
						// calculate tax amount for each tax
						if ($tax_prices_type == 1) { // prices includes tax
							$current_item_tax = $price - (($price * 100) / ($current_tax_percent + 100)) - $current_fixed_tax;
						} else {
							$current_item_tax = (($price * $current_tax_percent) / 100) + $current_fixed_tax;
						}
						if ($tax_round == 1) {
							$current_item_tax = va_round($current_item_tax, $decimals);
						}
						$taxes_values[$tax_id] = array(
							"tax_name" => $tax_rate["tax_name"], "show_type" => isset($tax_rate["show_type"])? $tax_rate["show_type"] : 0, 
							"tax_percent" => $current_tax_percent, "fixed_value" => $current_fixed_tax, "tax_amount" => $current_item_tax,
						);
						$tax_percent += $current_tax_percent;
						$fixed_tax += $current_fixed_tax;
						$item_tax_amount += $current_item_tax;
					}
				}
			}
		} else {
			$tax_percent = 0; $fixed_tax = 0;
		}

		if ($return_type == 2) {
			return $taxes_values;
		} else {
			return $item_tax_amount;
		}
	}

	function va_round($number, $precision)
	{
		global $settings;
		$round_type = get_setting_value($settings, "round_type", 1); // 1 - Common, 2 - Round to even
		if ($round_type == 1) {
			return round($number + pow(0.1, ($precision + 2)), $precision); // small correction to get common rounding
		} else {
			return round($number, $precision); // round to even method
		}
	}

	function add_tax_values(&$tax_rates, $tax_values, $item_type, $tax_round = "")
	{
		global $settings, $currency;

		if (!strlen($tax_round)) {
			$tax_round = get_setting_value($settings, "tax_round", 1);
		}
		$decimals = get_setting_value($currency, "decimals", 2);
		$total_tax = 0;
		if (is_array($tax_values)) {
			foreach($tax_values as $tax_id => $tax_info) {
				$tax_amount = $tax_info["tax_amount"];
				if ($tax_round == 1) {
					$tax_amount = round($tax_amount, $decimals);
				}
				$total_tax += $tax_amount;
				if (!isset($tax_rates[$tax_id][$item_type])) {
					$tax_rates[$tax_id][$item_type] = 0;
				}
				if (!isset($tax_rates[$tax_id]["tax_total"])) {
					$tax_rates[$tax_id]["tax_total"] = 0;
				}
				$tax_rates[$tax_id][$item_type] += $tax_amount;
				$tax_rates[$tax_id]["tax_total"] += $tax_amount;
			}
		}
		return $total_tax;
	}

	function check_image_validation($validation_number, $id = 0)
	{
		if (!$id) { $id = 0; }
		$validation_numbers = get_session("session_validation_numbers");
		if (isset($validation_numbers[$id]) && $validation_numbers[$id] == $validation_number) {
			unset($validation_numbers[$id]);
			set_session("session_validation_numbers", $validation_numbers);
			return $validation_number;
		} else {
			return false;
		}
	}

	function get_nice_bytes($bytes)
	{
	  if ($bytes >= 1024 && $bytes < 1048576) {
			return round($bytes / 1024) . "Kb";
		} elseif ($bytes >= 1048576) {
			return round($bytes / 1048576, 1) . "Mb";
		} else {
			return $bytes." bytes";
		}
	}

	function get_currency($currency_code = "", $update_session = true)
	{
		global $db, $table_prefix, $is_admin_path, $site_id, $va_data;
  
		$currency = array(); $param_currency_code = ""; $cookie_currency_code = "";
		// if currency wasn't passed check if user select it and parameter was passed
		if (!$currency_code) { 
			$currency_code = get_param("currency_code"); 
			$param_currency_code = $currency_code;
		}
		// if there is no currency code check it in session as it could be already retrieved 
		if (!$currency_code) { 
			$currency = get_session("session_currency"); 
			if (!is_array($currency)) { $currency = array(); } 
			// if there is no currency session check currency code in cookie
			if (!count($currency)) {
				$currency_code = get_setting_value($va_data, "_ccy");
				$cookie_currency_code = $currency_code;
			}
			// if there is no currency in session and cookie check if we can detect country by IP and then select appropriate currency
			if (!$currency_code && !count($currency)) {
				if (function_exists("geoip_country_code_by_name")) {
					$ip = get_ip();
					$country_code = geoip_country_code_by_name($ip);
					if ($country_code) {
						$sql = " SELECT currency_code FROM ".$table_prefix."countries WHERE country_code=".$db->tosql($country_code, TEXT);
						$db->query($sql);
						if ($db->next_record()) {
							$currency_code = $db->f("currency_code"); 
						}
					}
				}
			}
		}

		// check currency if it wasn't retrieved yet
		if (!count($currency)) {
			$currency_data = array();
			if ($currency_code) {
				// check currency by code if it was selected
				$sql  = " SELECT c.* ";
				$sql .= " FROM (" . $table_prefix . "currencies c ";
				$sql .= " LEFT JOIN " . $table_prefix . "currencies_sites cs ON c.currency_id=cs.currency_id) ";
				$sql .= " WHERE ( c.currency_code=" . $db->tosql($currency_code, TEXT);
				$sql .= " OR c.currency_value=" . $db->tosql($currency_code, TEXT) . " ) ";
				$sql .= " AND c.show_for_user=1 ";
				$sql .= " AND (c.sites_all=1 OR cs.site_id=" . $db->tosql($site_id, INTEGER) . ")";
				$db->query($sql);
				if ($db->next_record()) {
					$currency_data = $db->Record;
					// if user select some currency save his selection in cookies to use for future visits
					if ($param_currency_code) {
						va_data_cookie_update(array("_ccy" => $param_currency_code));
					}
				} else if ($cookie_currency_code) {
					// currency from cookie wasn't found so delete it
					va_data_cookie_update(array("_ccy" => ""));
				}
			}
			if (!count($currency_data)) {
				// check default shown currency for user
				$sql  = " SELECT c.* ";
				$sql .= " FROM (" . $table_prefix . "currencies c ";
				$sql .= " LEFT JOIN " . $table_prefix . "currencies_sites cs ON c.currency_id=cs.currency_id) ";
				$sql .= " WHERE c.show_for_user=1 ";
				$sql .= " AND c.is_default_show=1 ";
				$sql .= " AND (c.sites_all=1 OR cs.site_id=" . $db->tosql($site_id, INTEGER) . ")";
				$db->query($sql);
				if ($db->next_record()) {
					$currency_data = $db->Record;
				}
			}
			if (!count($currency_data)) {
				// check default currency
				$sql  = " SELECT c.* ";
				$sql .= " FROM " . $table_prefix . "currencies c ";
				$sql .= " WHERE c.is_default=1 ";
				$db->query($sql);
				if ($db->next_record()) {
					$currency_data = $db->Record;
				}
			}
			if (!count($currency_data)) {
				$currency_data = array(
					"decimals_number" => 2, "decimal_point" => ".", "thousands_separator" => "",
					"currency_code" => "", "currency_value" => "", 
					"symbol_left" => "", "symbol_right" => "",
					"exchange_rate" => 1, 
				);
			}
			// save currency in session
			$decimals_number = $currency_data["decimals_number"];
			$decimal_point = $currency_data["decimal_point"];
			if (!strlen($decimals_number)) { $decimals_number = 2; }
			if (!strlen($decimal_point)) { $decimal_point = "."; }
			$currency["code"] = $currency_data["currency_code"];
			$currency["value"] = $currency_data["currency_value"];
			$currency["left"] = $currency_data["symbol_left"];
			$currency["right"] = $currency_data["symbol_right"];
			$currency["rate"] = $currency_data["exchange_rate"];
			$currency["decimals"] = intval($decimals_number);
			$currency["point"] = $decimal_point;
			$currency["separator"] = $currency_data["thousands_separator"];

			if ($update_session) {
				set_session("session_currency", $currency);
			}
		}
		return $currency;
	}


	function currency_format($price, $price_currency = "", $tax_amount = 0)
	{
		global $settings, $currency;
		$price = doubleval($price);
		if (!is_array($price_currency)) {
			$price_currency = $currency;
		}
		if ($tax_amount) {
			$tax_prices_type = get_setting_value($settings, "tax_prices_type", 0);
			if ($tax_prices_type == 1) {
				$price_incl = $price;
				$price_excl = $price - $tax_amount;
			} else {
				$price_incl = $price + $tax_amount;
				$price_excl = $price;
			}
			$tax_prices = get_setting_value($settings, "tax_prices", 0);
			if ($tax_prices == 2 || $tax_prices == 3) {
				$price = $price_incl;
			} else {
				$price = $price_excl;
			}
		}
		if ($price < 0) {
			$formatted_price = "-" . $price_currency["left"] . number_format(abs($price) * $price_currency["rate"], intval($price_currency["decimals"]), $price_currency["point"], $price_currency["separator"]) . $price_currency["right"];
		} else {
			$formatted_price = $price_currency["left"] . number_format($price * $price_currency["rate"], intval($price_currency["decimals"]), $price_currency["point"], $price_currency["separator"]) . $price_currency["right"];
		}
		return $formatted_price;
	}

	function set_curl_options(&$ch, $curl_options)
	{
		if (isset($curl_options["CURLOPT_PROXY"]) && strlen($curl_options["CURLOPT_PROXY"])) {
			curl_setopt($ch, CURLOPT_PROXY, $curl_options["CURLOPT_PROXY"]);
			if (isset($curl_options["CURLOPT_PROXYUSERPWD"]) && strlen($curl_options["CURLOPT_PROXYUSERPWD"])) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $curl_options["CURLOPT_PROXYUSERPWD"]);
			}
		}

	}

	/**
	 * Return file size of remote file
	 *
	 * @param string $url
	 * @return integer
	 */
	function remote_filesize($url)
	{
		$size = 0;
		$parsed_url = parse_url($url);
		$sch = isset($parsed_url["scheme"]) ? $parsed_url["scheme"] : "";
		$host = isset($parsed_url["host"]) ? $parsed_url["host"] : "";
		$port = isset($parsed_url["port"]) ? $parsed_url["port"] : "";
		$user = isset($parsed_url["user"]) ? $parsed_url["user"] : "";
		$pass = isset($parsed_url["pass"]) ? $parsed_url["pass"] : "";
		$path = isset($parsed_url["path"]) ? $parsed_url["path"] : "";
		$query = isset($parsed_url["query"]) ? $parsed_url["query"] : "";
		if (in_array($sch, array("http", "https", "ftp", "ftps"))) {
			if (($sch == "http") || ($sch == "https")) {
				switch ($sch) {
					case "http":
						if (!$port) { $port = 80; } break;
					case "https";
						if (!$port) { $port = 443; } break;
				}
				$socket = @fsockopen($host, $port);
				if ($socket) {
					$out  = "HEAD $path?$query HTTP/1.0\r\n";
					$out .= "Host: $host\r\n";
					$out .= "UserAgent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n";
					$out .= "Connection: Close\r\n\r\n";
					fwrite($socket, $out);
					// read the response header
					$header = "";
					while (!feof($socket)){
						$header .= fread($socket, 1024*8);
					}
					fclose($socket);
					if (strlen($header)) {
						// try to acquire Content-Length within the response
						preg_match('/Content-Length:\s(\d+)/', $header, $matches);
						if (isset($matches[1])) {
							$size = $matches[1];
						}
					}
				}
			} elseif (($sch == "ftp") || ($sch == "ftps")) {
				if (strlen($host) && strlen($path)) {
					if (!$port) { $port = 21; }
					if (!$user) { $user = "anonymous"; }
					if (!$pass) { $pass = ""; }
					switch ($sch) {
						case "ftp":
							$ftpid = @ftp_connect($host, $port); break;
						case "ftps":
							$ftpid = @ftp_ssl_connect($host, $port); break;
						default:
							$ftpid = 0;
					}
					$ftpsize = 0;
					if ($ftpid) {
						$login = ftp_login($ftpid, $user, $pass);
						if ($login) {
							$ftpsize = ftp_size($ftpid, $path);
						}
						ftp_close($ftpid);
					}
					if ($ftpsize > 0) { $size = $ftpsize; }
				}
			}
		}

		return $size;
	}

	function eval_php_code(&$block_body)
	{
		/* NOTE: this code is a potential security threat as it allows to run any scripts from admin panel
		if (preg_match_all("/(<\?php|<\?)(.*)\?>/Uis", $block_body, $matches)) {
			for ($p = 0; $p < sizeof($matches[0]); $p++) {
				ob_start();
				eval($matches[2][$p]);
				$output = ob_get_contents();
				ob_end_clean();
				$block_body = str_replace($matches[0][$p], $output, $block_body);
			}
		}//*/
	}

	function split_long_words(&$text, $convert_links = 1, $symbols = 128)
	{
		if (preg_match_all("/[^\s\r\n]{" . $symbols . ",}/i", $text, $matches)) {
			$correction = $symbols - intval($symbols / 6);
			for ($p = 0; $p < sizeof($matches[0]); $p++) {
				$long_word = $matches[0][$p];
				if (!$convert_links || !preg_match("/^=\"https?:\\/\\/[^\s]+/i", $long_word)) {
					$original_word = $long_word;
					$new_word = ""; 
					$word_length = strlen($long_word);			
					while ($word_length) {
						if ($word_length > $symbols) {
							$word_part = substr($long_word, 0, $symbols);
							if ($word_length > $correction && preg_match("/^(.{".$correction."}.*[,\.\-\!\?\&\*_]).*/", $word_part, $word_match)) {
								$word_part = $word_match[1];
							}
						} else {
							$word_part = $long_word;
						}
						$word_part_len = strlen($word_part);
						$new_word .= $word_part . " ";
						$long_word = substr($long_word, $word_part_len);
						$word_length = strlen($long_word);
					}
					$text = str_replace($original_word, $new_word, $text);
				}
			}
		}
	}

	function user_login($login, $password, $user_id, $remember_me, $redirect_page, $make_redirects, &$errors)
	{
		global $db, $table_prefix, $settings, $parameters, $additional_parameters, $cc_parameters, $call_center_user_parameters, $phone_parameters;
		global $site_id, $multisites_version;
		$is_errors = false;
		$operation = get_param("operation");
		$secure_sessions = get_setting_value($settings, "secure_sessions", 0);
		$password_encrypt = get_setting_value($settings, "password_encrypt", 0);
		if ($password_encrypt == 1) {
			$password_match = md5($password);
		} else {
			$password_match = $password;
		}
		// prepare site urls
		$site_url = get_setting_value($settings, "site_url", "");
		$secure_url = get_setting_value($settings, "secure_url", "");

		$sql  = " SELECT u.*, ";
		$sql .= " ut.sites_all, uts.site_id, ";
		$sql .= " u.discount_type AS user_discount_type, u.discount_amount AS user_discount_amount, ";
		$sql .= " ut.discount_type AS group_discount_type, ut.discount_amount AS group_discount_amount, ";
		$sql .= " u.reward_type AS user_reward_type, u.reward_amount AS user_reward_amount, ";
		$sql .= " ut.reward_type AS group_reward_type, ut.reward_amount AS group_reward_amount, ";
		$sql .= " u.credit_reward_type AS user_credit_reward_type, u.credit_reward_amount AS user_credit_reward_amount, ";
		$sql .= " ut.credit_reward_type AS group_credit_reward_type, ut.credit_reward_amount AS group_credit_reward_amount, ";
		$sql .= " u.subscription_id, ut.is_subscription, u.expiry_date,  ";
		$sql .= " u.tax_free AS user_tax_free, ut.tax_free AS group_tax_free, ";
		$sql .= " u.order_min_goods_cost AS user_min_goods, u.order_max_goods_cost AS user_max_goods, ";
		$sql .= " ut.order_min_goods_cost AS group_min_goods, ut.order_max_goods_cost AS group_max_goods, ";
		$sql .= " ut.price_type, c.currency_code, c.country_code ";
		$sql .= " FROM (((" . $table_prefix . "users u ";
		$sql .= " LEFT JOIN " . $table_prefix . "user_types ut ON u.user_type_id=ut.type_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "countries c ON u.country_id=c.country_id) ";
		$sql .= " LEFT JOIN " . $table_prefix . "user_types_sites AS uts ON uts.type_id=ut.type_id)";
		$sql .= " WHERE (ut.sites_all=1 OR uts.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") AND ";
		if ($user_id) {
			$sql .= " u.user_id=" . $db->tosql($user_id, INTEGER);
		} else {
			$sql .= " u.login=" . $db->tosql($login, TEXT);
			$sql .= " AND u.password=" . $db->tosql($password_match, TEXT);
		}
		$db->query($sql);
		if ($db->next_record()) 
		{
			$current_date = va_time();
			$current_ts = va_timestamp();
			$user_data = $db->Record;
			// check account expiration date
			$expiry_date = $db->f("expiry_date", DATETIME);
			if (is_array($expiry_date)) {
				$expiry_date_ts = mktime (0, 0, 0, $expiry_date[MONTH], $expiry_date[DAY] + 1, $expiry_date[YEAR]);
			} else {
				$expiry_date_ts = $current_ts;
			}
			// check user sites 
			$sites_all = $db->f("sites_all");
			$site_ids = array();
			do {
				$site_ids = $db->f("site_id");
			} while ($db->next_record());

			$user_id = $user_data["user_id"];
			$layout_id = $user_data["layout_id"];
			$is_approved = $user_data["is_approved"];
			$is_sms_allowed = $user_data["is_sms_allowed"];
			$total_points = $user_data["total_points"];
			$credit_balance = $user_data["credit_balance"];
			$user_tax_free = $user_data["user_tax_free"];
			$group_tax_free = $user_data["group_tax_free"];
			$tax_free = ($user_tax_free || $group_tax_free);
			$order_min_goods_cost = $user_data["user_min_goods"];
			if (!strlen($order_min_goods_cost)) {
				$order_min_goods_cost = $user_data["group_min_goods"];
			}
			$order_max_goods_cost = $user_data["user_max_goods"];
			if (!strlen($order_max_goods_cost)) {
				$order_max_goods_cost = $user_data["group_max_goods"];
			}
			$user_type_id = $user_data["user_type_id"];
			$is_subscription = $user_data["is_subscription"];
			$registration_last_step = $user_data["registration_last_step"];
			$registration_total_steps = $user_data["registration_total_steps"];
			if ($registration_last_step < $registration_total_steps) {
				// if registration process wasn't finished
				set_session("session_new_user", "registration");
				set_session("session_new_user_id", $user_id);
				set_session("session_new_user_type_id", $user_type_id);
				// check secure option
				$secure_user_profile = get_setting_value($settings, "secure_user_profile", 0);
				if ($secure_user_profile || $secure_sessions) {
					$user_profile_url = $secure_url . get_custom_friendly_url("user_profile.php");
				} else {
					$user_profile_url = $site_url . get_custom_friendly_url("user_profile.php");
				}
				if ($secure_sessions) {
					session_set_cookie_params (0, "/", "", true);
					session_regenerate_id();
				}
				header ("Location: " . $user_profile_url);
				exit;
			} elseif ($current_ts > $expiry_date_ts && $is_subscription) {
				// if user have to pay for subscription
				set_session("session_new_user", "expired");
				set_session("session_new_user_id", $user_id);
				set_session("session_new_user_type_id", $user_type_id);
				// add some data into session for expired user as well
				$user_info = array(
					"tax_free" => $tax_free, "is_sms_allowed" => $is_sms_allowed,
					"total_points" => $total_points, "credit_balance" => $credit_balance,
					"order_min_goods_cost" => $order_min_goods_cost, "order_max_goods_cost" => $order_max_goods_cost,
				);
				set_session("session_user_info", $user_info);
				include_once (dirname(__FILE__)."/shopping_functions.php");
				add_subscription($user_type_id, "", $subscription_name);
				// check secure option
				$secure_order_profile = get_setting_value($settings, "secure_order_profile", 0);
				if ($secure_order_profile || $secure_sessions) {
					$order_info_url = $secure_url . get_custom_friendly_url("order_info.php");
				} else {
					$order_info_url = $site_url . get_custom_friendly_url("order_info.php");
				}
				if ($secure_sessions) {
					session_set_cookie_params (0, "/", "", true);
					session_regenerate_id();
				}
				header("Location: " . $order_info_url);
				exit;
			} elseif ($current_ts <= $expiry_date_ts && $is_approved) {
				$login = $user_data["login"];
				$nickname = $user_data["nickname"];
				if (!strlen($nickname)) { $nickname = $login; }
				$email = $user_data["email"];
				$country_id = $user_data["country_id"];
				$country_code = $user_data["country_code"];
				$delivery_country_id = $user_data["delivery_country_id"];
				$delivery_country_code = $user_data["delivery_country_code"];
				if (!$delivery_country_id) {
					$delivery_country_id = $country_id;
					$delivery_country_code = $country_code;
				}
				if (!$country_id) {
					$country_id = $delivery_country_id;
					$country_code = $delivery_country_code;
				}
				$currency_code = $user_data["currency_code"];
				$user_discount_type = $user_data["user_discount_type"];
				$user_discount_amount = $user_data["user_discount_amount"];
				$group_discount_type = $user_data["group_discount_type"];
				$group_discount_amount = $user_data["group_discount_amount"];
				$user_reward_type = $user_data["user_reward_type"];
				$user_reward_amount = $user_data["user_reward_amount"];
				$group_reward_type = $user_data["group_reward_type"];
				$group_reward_amount = $user_data["group_reward_amount"];
				$user_credit_reward_type = $user_data["user_credit_reward_type"];
				$user_credit_reward_amount = $user_data["user_credit_reward_amount"];
				$group_credit_reward_type = $user_data["group_credit_reward_type"];
				$group_credit_reward_amount = $user_data["group_credit_reward_amount"];
				$price_type = $user_data["price_type"];
				$subscription_id = $user_data["subscription_id"];
				
				set_session("session_new_user", "");
				set_session("session_new_user_id", "");
				set_session("session_new_user_type_id", "");
				set_session("session_user_id", $user_id);
				set_session("session_user_type_id", $user_type_id);
				set_session("session_user_login", $login);
				set_session("session_subscription_id", $subscription_id);

				if (strlen($user_data["name"])) {
					$user_name = $user_data["name"];
				} elseif (strlen($user_data["first_name"]) || strlen($user_data["last_name"])) {
					$user_name = $user_data["first_name"] . " " . $user_data["last_name"];
				} elseif (strlen($user_data["delivery_name"])) {
					$user_name = $user_data["delivery_name"];
				} elseif (strlen($user_data["delivery_first_name"]) || strlen($user_data["delivery_last_name"])) {
					$user_name = $user_data["delivery_first_name"] . " " . $user_data["delivery_last_name"];
				} else {
					$user_name = $login;
				}
				$user_name = trim($user_name);
				set_session("session_user_name", $user_name);
				set_session("session_user_email", $email);
				$discount_type = ""; $discount_amount = "";
				if ($user_discount_type > 0) {
					$discount_type = $user_discount_type;
					$discount_amount = $user_discount_amount;
				} elseif ($group_discount_type)  {
					$discount_type = $group_discount_type;
					$discount_amount = $group_discount_amount;
				}
				set_session("session_discount_type", $discount_type);
				set_session("session_discount_amount", $discount_amount);
				set_session("session_price_type", $price_type);

				$reward_type = ""; $reward_amount = "";
				if ($user_reward_type > 0) {
					$reward_type = $user_reward_type;
					$reward_amount = $user_reward_amount;
				} elseif ($group_reward_type)  {
					$reward_type = $group_reward_type;
					$reward_amount = $group_reward_amount;
				}

				$credit_reward_type = ""; $credit_reward_amount = "";
				if ($user_credit_reward_type > 0) {
					$credit_reward_type = $user_credit_reward_type;
					$credit_reward_amount = $user_credit_reward_amount;
				} elseif ($group_credit_reward_type)  {
					$credit_reward_type = $group_credit_reward_type;
					$credit_reward_amount = $group_credit_reward_amount;
				}

				// check for subscriptions
				$subscriptions_ids = "";
				$check_date_ts = mktime (0, 0, 0, $current_date[MONTH], $current_date[DAY], $current_date[YEAR]);
				$sql  = " SELECT subscription_id ";
				$sql .= " FROM " . $table_prefix . "orders_items ";
				$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$sql .= " AND is_subscription=1 ";
				$sql .= " AND subscription_expiry_date>=" . $db->tosql($check_date_ts, DATETIME);
				$db->query($sql);
				while ($db->next_record()) {
					if ($subscriptions_ids) { $subscriptions_ids .= ","; }
					$subscriptions_ids .= $db->f("subscription_id");
				}
				set_session("session_subscriptions_ids", $subscriptions_ids);

				// check if all required fields has values
				$sections = array();
				$sql  = " SELECT ups.section_id, ups.section_code, ups.section_name ";
				$sql .= " FROM (" . $table_prefix . "user_profile_sections ups ";
				$sql .= " LEFT JOIN " . $table_prefix . "user_profile_sections_types upst ON ups.section_id=upst.section_id) ";
				$sql .= " WHERE ups.is_active=1 ";
				$sql .= " AND (ups.user_types_all=1 OR upst.user_type_id=" . $db->tosql($user_type_id, INTEGER) . ") ";
				$sql .= " ORDER BY ups.section_order, ups.section_id ";
				$db->query($sql);
				while ($db->next_record()) {
					$sections[$db->f("section_code")] = $db->f("section_name");
				}

				$user_profile = array();
				$setting_type = "user_profile_" . $user_type_id;
				$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
				$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
				if (isset($site_id)) {
					$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ") ";
					$sql .= " ORDER BY site_id ASC";
				} else {
					$sql .= " AND site_id=1";
				}
				$db->query($sql);
				while ($db->next_record()) {
					$user_profile[$db->f("setting_name")] = $db->f("setting_value");
				}

				$required_fields = false;
				include_once(dirname(__FILE__)."/parameters.php");
				// check main parameters
				for ($i = 0; $i < sizeof($parameters); $i++)
				{
					$personal_param = $parameters[$i];
					$show_personal = get_setting_value($user_profile, "show_".$parameters[$i], 0);
					$personal_required = get_setting_value($user_profile, $parameters[$i]."_required", 0);
					$delivery_param = "delivery_".$parameters[$i];
					$show_delivery = get_setting_value($user_profile, "show_delivery_".$parameters[$i], 0);
					$delivery_required = get_setting_value($user_profile, "delivery_".$parameters[$i]."_required", 0);
					$personal_value = get_setting_value($user_data, $personal_param, "");
					$delivery_value = get_setting_value($user_data, $delivery_param, "");
					if ( (isset($sections["personal"]) && $show_personal && $personal_required && !strlen($personal_value)) 
						|| (isset($sections["delivery"]) && $show_delivery && $delivery_required && !strlen($delivery_value))
					) {
						$required_fields = true;
					}			
				}
				// check birth date field
				if (isset($sections["personal"])) {
					$show_birth_date = get_setting_value($user_profile, "show_birth_date", 0);
					$birth_date_required = get_setting_value($user_profile, "birth_date_required", 0);
					if ($show_birth_date && $birth_date_required) {
						$birth_year = get_setting_value($user_data, "birth_year");
						$birth_month = get_setting_value($user_data, "birth_month");
						$birth_day = get_setting_value($user_data, "birth_day");
						if (!$birth_year || !$birth_month || !$birth_day) {
							$required_fields = true;
						}
					}
				}
				// check additional parameters
				if (isset($sections["additional"])) {
					for ($i = 0; $i < sizeof($additional_parameters); $i++)
					{
						$param_name = $additional_parameters[$i];
						$show_param = get_setting_value($user_profile, "show_".$param_name, 0);
						$param_required = get_setting_value($user_profile, $param_name."_required", 0);
						if ( $show_param && $param_required && !strlen($user_data[$param_name]) ) {
							$required_fields = true;
						}			
					}
				}

				// check required custom parameters
				$profile_properties = array();
				$sql  = " SELECT upp.property_id, upp.control_type ";
				$sql .= " FROM (" . $table_prefix . "user_profile_properties upp ";
				$sql .= " INNER JOIN " . $table_prefix . "user_profile_sections ups ON upp.section_id=ups.section_id) ";
				$sql .= " WHERE upp.user_type_id=" . $db->tosql($user_type_id, INTEGER);
				$sql .= " AND upp.property_show IN (1, 3) "; // show for all users, show for registered users
				$sql .= " AND ups.is_active=1 ";
				$sql .= " AND upp.required=1 ";
				$db->query($sql);
				while ($db->next_record()) {
					$property_id = $db->f("property_id");
					$profile_properties[$property_id] = true;
				}

				if (is_array($profile_properties) && sizeof($profile_properties)) {
					// get all user properties
					$user_properties = array();
					$sql  = " SELECT up.property_id, up.property_value ";
					$sql .= " FROM " . $table_prefix . "users_properties up ";
					$sql .= " WHERE up.user_id=" . $db->tosql($user_id, INTEGER);
					$db->query($sql);
					while ($db->next_record()) {
						$property_id = $db->f("property_id");
						$property_value = $db->f("property_id");
						if (strlen($property_value) && isset($profile_properties[$property_id])) {
							unset($profile_properties[$property_id]);
						}
					}
					if (is_array($profile_properties) && sizeof($profile_properties) > 0) {
						$required_fields = true;
					}
				}

				if ($required_fields && $operation != "fast_order") {
					// if there are any new fields are required redirect user to his profile so he can update it
					$make_redirects = true;
					// check secure option
					$secure_user_profile = get_setting_value($settings, "secure_user_profile", 0);
					if ($secure_user_profile || $secure_sessions) {
						$redirect_page = $secure_url . get_custom_friendly_url("user_profile.php");
					} else {
						$redirect_page = $site_url . get_custom_friendly_url("user_profile.php");
					}
				}

				$user_info = array(
					"user_id" => $user_id, "user_type_id" => $user_type_id, "layout_id" => $layout_id,
					"site_ids" => $site_ids, "sites_all" => $sites_all, 
					"login" => $login, "nickname" => $nickname, "name" => $user_name, "subscriptions_ids" => $subscriptions_ids,
					"country_id" => $country_id, "country_code" => $country_code, 
					"delivery_country_id" => $delivery_country_id, "delivery_country_code" => $delivery_country_code, 
					"email" => $email, "discount_type" => $discount_type, "discount_amount" => $discount_amount,
					"price_type" => $price_type, "tax_free" => $tax_free, "is_sms_allowed" => $is_sms_allowed,
					"reward_type" => $reward_type, "reward_amount" => $reward_amount, 
					"credit_reward_type" => $credit_reward_type, "credit_reward_amount" => $credit_reward_amount, 
					"total_points" => $total_points, "credit_balance" => $credit_balance,
					"order_min_goods_cost" => $order_min_goods_cost, "order_max_goods_cost" => $order_max_goods_cost,
				);
				set_session("session_user_info", $user_info);

				if ($remember_me && $login && $password && get_session("cookie_control") != 1)
				{
					setcookie("cookie_user_login", $login, va_timestamp() + 3600 * 24 * 366);
					setcookie("cookie_user_password", $password, va_timestamp() + 3600 * 24 * 366);
				}

				// get currency if available
				if ($currency_code) {
					get_currency($currency_code);
				}

				// load user cart and move current added items to it
				include_once (dirname(__FILE__)."/shopping_functions.php");
				cart_retrieve("login");

				// update shopping cart if it's available
				$shopping_cart = get_session("shopping_cart");
				if (is_array($shopping_cart) && sizeof($shopping_cart) > 0) {
					recalculate_shopping_cart();
				}

				// check if need to regenerate session id for secure session
				if ($secure_sessions) {
					session_set_cookie_params (0, "/", "", true);
					session_regenerate_id();
				}

				// update last visit time
				$sql  = " UPDATE " . $table_prefix . "users SET last_visit_date=" . $db->tosql(va_time(), DATETIME);
				$sql .= ", last_visit_ip=" . $db->tosql(get_ip(), TEXT);
				$sql .= ", last_visit_page=" . $db->tosql(get_request_uri(), TEXT);
				$sql .= ", last_logged_date=" . $db->tosql(va_time(), DATETIME);
				$sql .= ", last_logged_ip=" . $db->tosql(get_ip(), TEXT);
				$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
				$db->query($sql);

				if ($make_redirects && $redirect_page) {
					// convert redirect page to the full url
					$ssl = get_param("ssl");
					if ($ssl) {
						$page_site_url = $secure_url;
					} else {
						$page_site_url = $site_url;
					}
					$return_page = get_request_uri();
					if (!preg_match("/^https?:\\/\\//i", $redirect_page) && preg_match("/^https?:\\/\\/[^\\/]+(\\/.*)$/i", $page_site_url, $matches)) {
						$page_path_regexp = prepare_regexp($matches[1]);
						if (preg_match("/^" .$page_path_regexp. "/i", $redirect_page)) {
							$redirect_page = $page_site_url . preg_replace("/^" .$page_path_regexp. "/i", "", $redirect_page);
						} 
					}
					header("Location: " . $redirect_page);
					exit;
				}
			} elseif ($current_ts > $expiry_date_ts) {
				$is_errors = true;
				$errors .= ACCOUNT_EXPIRED_MSG . "<br>";
			} else {
				$is_errors = true;
				$errors .= ACCOUNT_APPROVE_ERROR . "<br>";
			}
		}
		else
		{
			$is_errors = true;
			if ($user_id) {
				$errors .= NO_RECORDS_MSG . "<br>";
			} else {
				$errors .= LOGIN_PASSWORD_ERROR . "<br>";
			}
		}
		if ($is_errors && get_session("cookie_control") != 1) {
			setcookie("cookie_user_login");
			setcookie("cookie_user_password");
		}
		return (!$is_errors);
	}

	function user_logout()
	{
		global $settings;

		set_session("session_user_id", "");
		set_session("session_new_user_id", "");
		set_session("session_new_user_type_id", "");
		set_session("session_new_user", "");
		set_session("session_user_type_id", "");
		set_session("session_user_login", "");
		set_session("session_user_name", "");
		set_session("session_user_email", "");
		set_session("session_discount_type", "");
		set_session("session_discount_amount", "");
		set_session("session_price_type", "");
		set_session("session_user_info", "");
		// clear current user cart 
		set_session("db_cart_id", "");
		set_session("shopping_cart", "");
		set_session("session_coupons", "");
		/*
		if (get_setting_value($settings, "logout_cart_clear", 0) == 1) {
			set_session("shopping_cart", "");
			set_session("session_coupons", "");
		}
		// update shopping cart if it's available
		$shopping_cart = get_session("shopping_cart");
		if (is_array($shopping_cart) && sizeof($shopping_cart) > 0) {
			include_once (dirname(__FILE__)."/shopping_functions.php");
			recalculate_shopping_cart();					
		}//*/
		if(get_session("cookie_control") != 1){
			setcookie("cookie_user_login");
			setcookie("cookie_user_password");
		}
	}

	function auto_user_login()
	{
		// automatically login customer
		$session_user_id = get_session("session_user_id");
		if (!$session_user_id) {
			$cookie_login = get_cookie("cookie_user_login");
			$cookie_password = get_cookie("cookie_user_password");
			if ($cookie_login && $cookie_password) {
				user_login($cookie_login, $cookie_password, "", false, "", false, $errors);
			}
		}
	}

	function update_user_status($user_id, $status_id)
	{
		global $db, $table_prefix, $settings;

		$current_date = va_time();
		$user_ip = get_ip();
		$admin_id = get_session("session_admin_id");
		// update user status
		$sql  = " UPDATE " . $table_prefix . "users SET ";
		$sql .= " is_approved=" . $db->tosql($status_id, INTEGER) . ",";
		if ($admin_id) {
			$sql .= " admin_modified_date=" . $db->tosql($current_date, DATETIME) . ", ";
			$sql .= " admin_modified_ip=" . $db->tosql($user_ip, TEXT);
		} else {
			$sql .= " modified_date=" . $db->tosql($current_date, DATETIME) . ", ";
			$sql .= " modified_ip=" . $db->tosql($user_ip, TEXT);
		}
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);

		// get products settings for user
		$product_settings = array();
		$sql  = " SELECT user_type_id ";
		$sql .= " FROM " . $table_prefix . "users ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$type_id = $db->f("user_type_id");
			$setting_type = "user_product_" . $type_id;
			$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
			$db->query($sql);
			while ($db->next_record()) {
				$product_settings[$db->f("setting_name")] = $db->f("setting_value");
			}
		}

		$activate_products = get_setting_value($product_settings, "activate_products", 0);
		$deactivate_products = get_setting_value($product_settings, "deactivate_products", 0);
		if ($status_id == 1 && $activate_products == 1) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_showing=1 ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
		} elseif ($status_id == 0 && $deactivate_products == 1) {
			$sql  = " UPDATE " . $table_prefix . "items SET is_showing=0 ";
			$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
			$db->query($sql);
		}
	}

	function get_friend_info($type_info = 1, $friend_code = "") 
	{
		global $db, $table_prefix, $site_id;

		$friend_info = array();
		$user_id = get_session("session_user_id");
		// 1 - typical friend, 2 - affiliate friend
		if ($type_info == 1) { // 1: friend
			$friend_code = get_session("session_friend");
			$friend_user_id = get_session("session_friend_id");
			$friend_type_id = get_session("session_friend_type_id");
		} else if ($type_info == 2) { // 2: affiliate user
			$friend_code = get_session("session_af");
			$friend_user_id = get_session("session_af_id");
			$friend_type_id = get_session("session_af_type_id");
		} else {
			$friend_user_id = ""; $friend_type_id = "";
		}
	
		if (strlen($friend_code) && !strlen($friend_user_id)) {
			$sql  = " SELECT u.user_id,u.user_type_id,u.affiliate_code FROM (";
			if (isset($site_id)) { $sql .= "("; }
			$sql .= $table_prefix . "users u";
			$sql .= " LEFT JOIN " . $table_prefix . "user_types ut ON ut.type_id=u.user_type_id)";
			if (isset($site_id)) {
				$sql .= " LEFT JOIN " . $table_prefix . "user_types_sites s ON s.type_id=ut.type_id)";
			}
			if ($type_info == 1) {
				$sql .= " WHERE (u.nickname=" . $db->tosql($friend_code, TEXT);
				$sql .= " OR u.login=" . $db->tosql($friend_code, TEXT) . ") ";
			} else if ($type_info == 2) {
				$sql .= " WHERE u.affiliate_code=" . $db->tosql($friend_code, TEXT);
			} else {
				$sql .= " WHERE (u.nickname=" . $db->tosql($friend_code, TEXT);
				$sql .= " OR u.login=" . $db->tosql($friend_code, TEXT);
				$sql .= " OR u.affiliate_code=" . $db->tosql($friend_code, TEXT) . ") ";
			}

			if (isset($site_id)) {
				$sql .= " AND (ut.sites_all=1 OR s.site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";			
			} else {
				$sql .= " AND ut.sites_all=1";
			}
			$db->query($sql);
			if ($db->next_record()) {
				$affiliate_code = $db->f("affiliate_code");
				$friend_user_id = $db->f("user_id");
				$friend_type_id = $db->f("user_type_id");
				if (strtolower($friend_code) == strtolower($affiliate_code)) {
					$friend_type = "affiliate";
				} else {
					$friend_type = "friend";
				}
				$friend_info = array(
					"type" => $friend_type,
					"code" => $friend_code,
					"user_id" => $friend_user_id,
					"user_type_id" => $friend_type_id,
				);
			}

			if ($friend_user_id == $user_id) {
				// user can't use himself as his own friend
				$friend_user_id = 0; $friend_type_id = 0; $friend_info = array();
			}
			if ($type_info == 1) {
				set_session("session_friend_id", $friend_user_id);
				set_session("session_friend_type_id", $friend_type_id);
			} else if ($type_info == 2) {
				set_session("session_af_id", $friend_user_id);
				set_session("session_af_type_id", $friend_type_id);
			}
		}
		if ($type_info == 3) {
			return $friend_info;
		} else {
			return $friend_user_id;
		}
	}

	function sms_send_allowed($cell_phone_number)
	{
		global $settings, $db, $table_prefix;
		$user_id = get_session("session_user_id");
		if ($user_id) {
			$user_info = get_session("session_user_info");
			$is_sms_allowed = get_setting_value($user_info, "is_sms_allowed", 0);
		} else {
			$is_sms_allowed = get_setting_value($settings, "is_sms_allowed", 0);
		}
		if ($is_sms_allowed == 2) {
			// check if number in allowed list
			$cell_phone_number = preg_replace("/[^\d]/", "", $cell_phone_number);
			$sql = " SELECT cell_phone_id FROM " . $table_prefix . "allowed_cell_phones WHERE cell_phone_number=" . $db->tosql($cell_phone_number, TEXT);
			$db->query($sql);
			if ($db->next_record()) {
				$is_sms_allowed = 1;
			} else {
				$sql = " SELECT is_sms_allowed FROM " . $table_prefix . "users WHERE cell_phone=" . $db->tosql($cell_phone_number, TEXT);
				$db->query($sql);
				if ($db->next_record()) {
					$is_sms_allowed = $db->f("is_sms_allowed");
				} else {
					$is_sms_allowed = 0;
				}
			}
		}

		return $is_sms_allowed;
	}

	// Trancate text to desired length.
	// The last word is not trancated, so the length of result string may be greater than param $length
	function trancate_to_word($text, $length)
	{
		return preg_replace('/(^.{'.$length.'}.*?\s).+/is', '$1', $text);
	}


	function prepare_custom_friendly_urls()
	{
		global $db, $table_prefix, $settings, $custom_friendly_urls, $site_id;
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_extension = get_setting_value($settings, "friendly_extension", "");
		$current_version = va_version();
		if ($friendly_urls && (compare_versions($current_version, "3.3.5") > 0)) {
			$sql  = " SELECT u.script_name, u.friendly_url ";
			if (isset($site_id)) {
				$sql .= " FROM (" . $table_prefix . "friendly_urls u";
				$sql .= " LEFT JOIN  " . $table_prefix . "friendly_urls_sites us ON (us.friendly_id=u.friendly_id AND u.sites_all=0))";
				$sql .= " WHERE u.sites_all=1 OR us.site_id=" . $db->tosql($site_id, INTEGER, true, false);
			} else {
				$sql .= " FROM " . $table_prefix . "friendly_urls u ";			
				$sql .= " WHERE u.sites_all=1";
			}
			$db->query($sql);
			while ($db->next_record()) {
				$custom_friendly_urls[$db->f("script_name")] = $db->f("friendly_url") . $friendly_extension;
			}
		}
		return $custom_friendly_urls;
	}

	function get_custom_friendly_url($script_name)
	{
		global $custom_friendly_urls;
		return (is_array($custom_friendly_urls) && isset($custom_friendly_urls[$script_name]) && strlen($custom_friendly_urls[$script_name])) ? $custom_friendly_urls[$script_name] : $script_name;
	}
	
	function check_selected_url($script_url, $match_type = 2)
	{
		global $current_page;
		$request_page = get_request_page();
		if (!isset($current_page)) { $current_page = $request_page; }
		$request_uri_path = get_request_path();

		$parsed_url  = parse_url($script_url);
		if (isset($parsed_url["path"])) {
			$script_name = $parsed_url["path"];
			if (isset($parsed_url["query"])) {
				parse_str($parsed_url["query"], $script_vars);
			} else {
				$script_vars = array();
			}
		} else {
			$script_name = $script_url;
		}
		
		$url_matched = false;
		if ($match_type > 0) {
			if ($script_name  == $request_page || $script_name  == $current_page || $script_name  == $request_uri_path) {
				$url_matched = true;
			}
			if ($url_matched && $match_type == 2 && $script_vars) {
				foreach ($script_vars AS $key => $var) {
					if(get_param($key) != $var) {
						$url_matched = false;
						break;
					}
				}
			}
		}
		return $url_matched;
	}

 	function compare_versions($version1, $version2)
	{
		$first_numbers = explode(".", $version1);
		$second_numbers = explode(".", $version2); 

		if (count($first_numbers) > count($second_numbers)) {
			for ($i = 0; isset($first_numbers[$i]); $i++) {
				if (!isset($second_numbers[$i])) $second_numbers[$i] = "0";
			}
		} else {
			for ($i = 0; isset($second_numbers[$i]); $i++) {
				if (!isset($first_numbers[$i])) $first_numbers[$i] = "0";
			}
		}
			
		foreach ($first_numbers as $key => $value) {
			if ($first_numbers[$key] > $second_numbers[$key]) {
				return 1; // version greater
			} elseif ($first_numbers[$key] < $second_numbers[$key]) {
				return -1; // version lower
			}
		}
	
		return 0; // the same version
	}

	function friendly_url_redirect($friendly_url, $friendly_params)
	{
		global $is_friendly_url, $settings, $disable_friendly_redirect;
		$site_url = get_setting_value($settings, "site_url", "");
		$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
		$friendly_url_redirect = get_setting_value($settings, "friendly_url_redirect", 0);
		$disable_friendly_redirect = isset($disable_friendly_redirect) ? $disable_friendly_redirect : false;
		if (!$is_friendly_url && !$disable_friendly_redirect && $friendly_urls && $friendly_url_redirect && $friendly_url && sizeof($_POST) == 0) {
			$friendly_extension = get_setting_value($settings, "friendly_extension", "");
			$query_string  = get_query_string($_GET, $friendly_params);
			$friendly_url .= $friendly_extension.$query_string;
			header("HTTP/1.1 301 Moved Permanently");
			header("Status: 301 Moved Permanently");
			header("Location: " . $site_url . $friendly_url, true, 301);
			exit;
		}
	}

	/**
	 * Return escaped string ready to use in XML code
	 *
	 * @param string $str
	 * @return string
	 */
	function xml_escape_string($str) 
	{
		return str_replace("&#039;", "&apos;", htmlspecialchars($str, ENT_QUOTES));
	}
	
	/**
	 * Check whether the local image file exists
	 *
	 * @param string $check_image
	 * @return boolean
	 */
	function image_exists($check_image)
	{
		global $root_folder_path;

		if (strlen($check_image)) {
			if (!preg_match("/^http(s)?:\/\//", $check_image)) {
				while (strpos($check_image, "//") !== false) {
					$check_image = str_replace("//", "/", $check_image);
				}
				if (substr($check_image, 0, 1) == "/") {
					$check_image = substr($check_image, 1);
					$request_uri = get_var("REQUEST_URI");
					$current_path = substr($request_uri, 0, strpos($request_uri, "?"));
					while (strpos($current_path, "//") !== false) {
						$current_path = str_replace("//", "/", $current_path);
					}
					if (substr($current_path, 0, 1) == "/") {
						$current_path = substr($current_path, 1);
					}
					$current_path_parts = explode("/", $current_path);
					$check_image = str_repeat("../", sizeof($current_path_parts) - 1) . $check_image;
				} else {
					$check_image = $root_folder_path . $check_image;
				}
				if (!file_exists($check_image)) { 
					return false;
				}
			}
		}
		return true;
	}

	function prepare_user_name(&$full_name, &$first_name, &$last_name)
	{	
		if (strlen($full_name) && !strlen($first_name) && !strlen($last_name)) {
			$name = $full_name;
			$name_parts = explode(" ", $name, 2);
			if (sizeof($name_parts) == 2) {
				$first_name = $name_parts[0];
				$last_name = $name_parts[1];
			} else {
				$first_name = $name_parts[0];
				$last_name = "";
			}
		} elseif (!strlen($full_name) && (strlen($first_name) || strlen($last_name))) {
			$full_name = trim($first_name . " " . $last_name);
		}
	}	

	function prepare_js_value($js_value)
	{
		$find = array("%", "+", "&", "\"", "'", "\n", "\r", "=", "|", "#");
		$replace = array("%25", "%2B", "%26", "%22", "%27", "%0A", "%0D", "%3D", "%7C", "%23");
		$js_value = str_replace($find, $replace, $js_value);
		return $js_value;
	}

	function is_utf8($string) 
	{
		return preg_match('/^(?:
			[\x09\x0A\x0D\x20-\x7E]              # ASCII
			| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		)*$/xs', $string);
	} 

	function charset_decode_utf8($string) 
	{ 
		// avoid using 0xA0 (\240) in regexp ranges. RH73 does not like that
		if (!preg_match("/[\200-\237]/", $string) && !preg_match("/[\241-\377]/", $string)) {
			// if there are only 8-bit characters return string
			return $string; 
		}

		// decode three byte unicode characters 
		$string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",        
			"'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'", $string); 

    // decode two byte unicode characters 
		$string = preg_replace("/([\300-\337])([\200-\277])/e", 
			"'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", $string); 

		return $string; 
	}

	function convert_to_utf8($string, $from_charset = "")
	{
		if (!$from_charset && function_exists("mb_detect_encoding")) {
			$from_charset = mb_detect_encoding($string);
		}
		$from_charset = strtolower($from_charset);
		if (!$from_charset && !is_utf8($string)) {
			if (function_exists("mb_convert_encoding")) {
				$string = mb_convert_encoding ($string, "utf-8");
			}
		} else if ($from_charset && $from_charset != "utf-8" && $from_charset != "ascii") {
			if (function_exists("mb_convert_encoding")) {
				$string = mb_convert_encoding ($string, "utf-8", $from_charset);
			} else if (function_exists("iconv")) {
				$string = iconv ($from_charset, "utf-8", $string);
			}
		}
		// remove 4-byte characters from a UTF-8 if it's doesn't support by DB (MySQL require utf8mb4 charset and utf8mb4_unicode_ci collation)
		$string = preg_replace("/[\x{10000}-\x{10FFFF}]/u", "\xEF\xBF\xBD", $string); // use replacement character U+FFFD to avoid unicode attacks

		return $string;
	}

	function sql_explain($sql) {
		global $db;
		$eol = get_eol();
				
		$debug_output  = "";
		$debug_output .= $sql . $eol;
		
		$db->query("EXPLAIN $sql ");
		
		if ($db->next_record()) {
			
			$debug_output .= "<table>" . $eol;
			
			$fields = array_keys($db->Record);
			$debug_output .= "<tr>" . $eol;
			for ($i=0,$ic=count($fields); $i<$ic; $i++) {
				if(intval($fields[$i])) {
					unset ($fields[$i]);
				} else {
					$debug_output .= "<th>" . $fields[$i] . "</th>" . $eol;
				}
			}
			$debug_output .= "</tr>" . $eol;
			
			do {
				$debug_output .= "<tr>" . $eol;
				foreach ($fields AS $field) {
					$debug_output .= "<td>" . $db->f($field) . "</td>" . $eol;
				}
				$debug_output .= "</tr>" . $eol;
			} while ($db->next_record());
			
			$debug_output .= "</table>" . $eol;
		}
		return $debug_output;
	}
	
	function format_binary_for_sql($field_1, $field_2) {
		global $db_type;
		
		if ($db_type == "postgre" || $db_type == "sqlsrv") {
			return $field_1 . "&" . $field_2 . " > 0 ";			
		} else {
			return $field_1 . "&" . $field_2;
		}
	}
	
	function set_cache($cache_data, $cache_type="0", $cache_name="0", $cache_parameter="0") {
		global $db, $table_prefix;
		$current_version = va_version();
		if (compare_versions($current_version, "3.6.32") < 0) {
			return $cache_data;
		}

		// delete old cache if it exists
		$sql  = " DELETE FROM ".$table_prefix."caches ";
		$sql .= " WHERE cache_type = ".$db->tosql($cache_type,TEXT);
		$sql .= " AND cache_name = ".$db->tosql($cache_name,TEXT);
		$sql .= " AND cache_parameter = ".$db->tosql($cache_parameter,TEXT);
		$db->query($sql);

		// save new cache data
		$sql = " INSERT INTO ".$table_prefix."caches (cache_data,cache_date,cache_type,cache_name,cache_parameter) ";
		$sql.= " VALUES (".$db->tosql($cache_data,TEXT).",";
		$sql.= $db->tosql(strtotime(date("Y-m-d H:i:s")),DATE).",";
		$sql.= $db->tosql($cache_type,TEXT).",";
		$sql.= $db->tosql($cache_name,TEXT).",";
		$sql.= $db->tosql($cache_parameter,TEXT).")";
		$db->query($sql);
		return $cache_data;
	}

	function get_cache($hour=24,$daily = 0,$cache_type="0",$cache_name="0",$cache_parameter="0") {
		global $db,$table_prefix;
		$current_version = va_version();
		if (compare_versions($current_version, "3.6.32") < 0) {
			return false;
		}
		$sql  = " SELECT * FROM ".$table_prefix."caches WHERE cache_type = ".$db->tosql($cache_type,TEXT);
		$sql .= " AND cache_name = ".$db->tosql($cache_name,TEXT);
		$sql .= " AND cache_parameter = ".$db->tosql($cache_parameter,TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$date = $db->f("cache_date", DATETIME);
			$date_ts = va_timestamp($date);
			if (!$daily) {
				if ($date_ts + $hour * 60 * 60 > strtotime(date("Y-m-d H:i:s"))) {
					return $db->f("cache_data");
				} else {
					return false;
				}
			} else {
				if (strtotime(date("Y-m-d") . " 00:00:00") < $date_ts + 24 * 60 * 60) {
					return $db->f("cache_data");
				} else {
					return false;
				}
			}
		}
		return false;
	}

 	function comp_vers($version1, $version2)
	{
		// remove letters if available
		$version1 = preg_replace("/[^\d\.]/", "", $version1);
		$version2 = preg_replace("/[^\d\.]/", "", $version2);

		$first_numbers = explode(".", $version1);
		$second_numbers = explode(".", $version2); 

		if (count($first_numbers) > count($second_numbers)) {
			for ($i = 0; isset($first_numbers[$i]); $i++) {
				if (!isset($second_numbers[$i])) $second_numbers[$i] = "0";
			}
		} else {
			for ($i = 0; isset($second_numbers[$i]); $i++) {
				if (!isset($first_numbers[$i])) $first_numbers[$i] = "0";
			}
		}
			
		foreach ($first_numbers as $key => $value) {
			if ($first_numbers[$key] > $second_numbers[$key]) {
				return 1;
			} elseif ($first_numbers[$key] < $second_numbers[$key]) {
				return 2;
			}
		}
	
		return 0;
	}

	function set_link_tag($href, $rel, $type)
	{
		if ($rel == "stylesheet") { $href .= "?".VA_BUILD; } // append build date to refresh css file
		set_head_tag("link", array("href" => $href, "rel" => $rel, "type" => $type), "href", 1); 
	}

	function set_script_tag($src, $async = true, $tag_block = "head_tags")
	{
		$src .= "?".VA_BUILD; // append build date to refresh scripts
		$attributes = array("src" => $src);
		if ($async) { $attributes["async"] = "async"; }
		set_head_tag("script", $attributes, "src", 2, "", $tag_block); 
	}

	function set_css_script($href)
	{
		global $t;
		$href .= "?".VA_BUILD; // append build date to refresh css file
		$css_script = "<script>var l=document.createElement('link');l.rel='stylesheet';l.href='".$href."';document.querySelector('head').appendChild(l);</script>";
		$t->set_block("css_script", $css_script);
		$t->parse_to("css_script", "hidden_blocks", true);
	}

	function set_head_tag($tag_name, $attributes, $unique_attribute = "", $close_type = 1, $tag_content = "", $tag_block = "head_tags") 
	{
		global $t, $html_tags;
		$attribute_value = ($unique_attribute) ? $attributes[$unique_attribute] : "";
		if (!$attribute_value || !isset($html_tags[$tag_name]) || !isset($html_tags[$tag_name][$attribute_value])) {
			if (strlen($attribute_value)) {
				$html_tags[$tag_name][$attribute_value] = true;
			}
			$html_tag = "<".$tag_name;
			foreach ($attributes as $attribute_name => $attribute_value) {
				if (strlen($attribute_value)) {
					$html_tag .= " ".$attribute_name."=\"".htmlspecialchars($attribute_value)."\"";
				}
			}
			if ($close_type == 1) {
				$html_tag .= " />";
			} else if ($close_type == 2) {
				$html_tag .= ">".$tag_content."</".$tag_name.">";
			} else {
				$html_tag .= ">";
			}
			$t->set_block("html_tag", $html_tag.get_eol());
			$t->parse_to("html_tag", $tag_block, true);
		}
	}

function va_mail($mail_to, $mail_subject, $mail_body, $mail_headers = "", $attachments = "", $mail_tags = "")
{
	global $t, $settings;

	if (!strlen($mail_to)) { 
		return false;
	}
	$eol = get_eol();
	if (!is_array($mail_headers)) { $mail_headers = array(); }
	$mail_type = get_setting_value($mail_headers, "mail_type", 0);

	// set mail tags to parse mail body
	if (is_array($mail_tags)) {
		if (!isset($t)) { $t = new VA_Template(""); }
		if ($mail_type) {
			foreach ($mail_tags as $tag_name => $tag_value) {
				$t->set_var($tag_name, nl2br(htmlspecialchars($tag_value)));
			}
		} else {
			$t->set_vars($mail_tags);
		}
	}
	// parse mail headers
	foreach ($mail_headers as $header_type => $header_value) {
		parse_value($header_value);
		$mail_headers[$header_type] = $header_value;
	}
	// use base64 encode for mail body
	$mail_headers["Content-Transfer-Encoding"] = "base64";
	parse_value($mail_body);
	$mail_body = preg_replace("/\r\n|\r|\n/", $eol, $mail_body);
	$mail_body = chunk_split(base64_encode($mail_body));

	// set mail tags without encoding to parse mail variables
	if (is_array($mail_tags) && $mail_type) {
		$t->set_vars($mail_tags);
	}

	$mail_to = str_replace(";", ",", $mail_to);
	parse_value($mail_to);

	parse_value($mail_subject);
	// convert mail subject to correct MIME header 
	if ($mail_subject) {
		if (function_exists("mb_encode_mimeheader")) {
			mb_internal_encoding("utf-8");
			$mail_subject = mb_encode_mimeheader($mail_subject, "utf-8", "B", $eol);
		} else if (function_exists("iconv_mime_encode")) {
			$mail_subject = iconv_mime_encode ("Subject", $mail_subject, array("input-charset"=>"utf-8", "output-charset"=>"utf-8", "line-break-chars" => $eol));
			$mail_subject = preg_replace("/^Subject:\s*/", "", $mail_subject);
		} else {
			$mail_subject = "=?utf-8?B?".base64_encode($mail_subject)."?=";
		}
	}

	$admin_email = get_setting_value($settings, "admin_email");
	$mail_type = get_setting_value($mail_headers, "mail_type", 0);
	$mail_from = get_setting_value($mail_headers, "from", $admin_email);
	parse_value($mail_from);
	$email_additional_headers = get_setting_value($settings, "email_additional_headers", "");
	$email_additional_parameters = get_setting_value($settings, "email_additional_parameters", "");

	// set additional mail headers
	$add_mail_headers = preg_split("/[\r\n]+/", $email_additional_headers, -1, PREG_SPLIT_NO_EMPTY);
	foreach ($add_mail_headers as $header) {
		$header = explode(":", $header);
		if (sizeof($header) == 2) {
			$mail_headers = array_merge(array(trim($header[0]) => trim($header[1])), $mail_headers);
		}
	}

	if (is_array($attachments) && sizeof($attachments) > 0) {
		$boundary = "--va_". md5(va_timestamp()) . "_" . va_timestamp(); 
		$mail_headers["Content-Type"] = "multipart/mixed; boundary=\"" . $boundary . "\"";
		if (isset($mail_headers["mail_type"])) {
			unset($mail_headers["mail_type"]);
		}

		$original_body = $mail_body;
		$mail_body  = "This is a multi-part message in MIME format." . $eol . $eol;
		$mail_body .= "--" . $boundary . $eol;
		if ($mail_type) {
			$mail_body .= "Content-Type: text/html;" . $eol;
		} else {
			$mail_body .= "Content-Type: text/plain;" . $eol;
		}
		$mail_body .= "\tcharset=\"utf-8\"". $eol;
		$mail_body .= "Content-Transfer-Encoding: base64" . $eol;
		$mail_body .= $eol;
		$mail_body .= $original_body;
		$mail_body .= $eol . $eol;

		for ($at = 0; $at < sizeof($attachments); $at++) {
			$attachment_info = $attachments[$at];
			if (!is_array($attachment_info)) {
				$filepath = $attachment_info;
				$attachment_info = array(basename($filepath), $filepath, "");
			} elseif (sizeof($attachment_info) == 1) {                                                    
				$filepath = $attachment_info[0];
				$attachment_info = array(basename($filepath), $filepath, "");
			} 

			$filename = $attachment_info[0];
			if (!$filename) { $filename = basename($filepath); }
			if (!$filename) { $filename = "noname.txt"; }
			$filepath = $attachment_info[1];
			$filetype = isset($attachment_info[2]) ? $attachment_info[2] : "";
			if (preg_match("/^(http|https|ftp|ftps):\/\//", $filepath)) {
				$is_remote_file = true;
			} else {
				$is_remote_file = false;
			}
			$filebody = "";
			if ($filetype == "pdf") {
				$filebody = $pdf->get_buffer();
			} elseif ($filetype == "buffer") {
				$filebody = $filepath;
			} elseif ($filetype == "fp") {
				// read entire file from file pointer
				while (!feof($fp)) {
					$filebody .= fread($fp, 8192);
				}
			} elseif ($is_remote_file || (@file_exists($filepath) && !@is_dir($filepath))) {
				// read entire file into filebody
				$fp = fopen($filepath, "rb");
				while (!feof($fp)) {
					$filebody .= fread($fp, 8192);
				}
				fclose($fp);
			}

			if ($filebody) {
				$file_base64 = chunk_split(base64_encode($filebody)); 

				$mail_body .= "--" . $boundary . $eol;
				if (preg_match("/\.gif$/", $filename)) {
					$mail_body .= "Content-Type: image/gif;" . $eol;
				} elseif (preg_match("/\.pdf$/", $filename)) {
					$mail_body .= "Content-Type: application/pdf;" . $eol;
				} else {
					$mail_body .= "Content-Type: application/octet-stream;" . $eol;
				}
				$mail_body .= "\tname=\"" . $filename . "\"" . $eol;
				$mail_body .= "Content-Transfer-Encoding: base64" . $eol;
				$mail_body .= "Content-Disposition: attachment;" . $eol;
				$mail_body .= "\tfilename=\"" . $filename . "\"" . $eol;
				$mail_body .= $eol;
				$mail_body .= $file_base64;
				$mail_body .= $eol . $eol;
			}
		}
		// end multipart message
		$mail_body .= "--" . $boundary . "--" . $eol;
		$mail_body .= $eol;
	} else {
		$mail_headers["mail_type"] = $mail_type;
	}

	$smtp_mail = get_setting_value($settings, "smtp_mail", 0);
	if ($smtp_mail) {
		$admin_id = get_session("session_admin_id");

		$smtp_debug = false;
		$smtp_host = get_setting_value($settings, "smtp_host", "127.0.0.1");
		$smtp_port = get_setting_value($settings, "smtp_port", 25);
		$smtp_timeout = get_setting_value($settings, "smtp_timeout", 30);
		$smtp_username = get_setting_value($settings, "smtp_username", "");
		$smtp_password = get_setting_value($settings, "smtp_password", "");
		$smtp_socket = $smtp_host.":".$smtp_port;

		$errors = "";

		//$socket = @fsockopen($smtp_host, $smtp_port, $errno, $error, $smtp_timeout);
		$socket = stream_socket_client($smtp_socket, $errno, $errstr, $smtp_timeout);
		if (!$socket) {
			$errors = $error;
			return false;
		}
		// read server reply
		$response = smtp_check_response($socket, 220, $error, $smtp_debug);
		if ($error) {
			$errors = $error;
			return false;
		}
		$smtp_username = get_setting_value($settings, "smtp_username", "");
		$smtp_password = get_setting_value($settings, "smtp_password", "");

		if (strlen($smtp_username) && strlen($smtp_password))
		{ 
			smtp_send_request($socket, "EHLO ".$smtp_host."\r\n", $error, $smtp_debug);
			$response = smtp_check_response($socket, "250", $error, $smtp_debug);
			if ($error) { $errors = $error; return false; }

			// check if SMTP server require TLS encryption
			if (preg_match("/STARTTLS/i", $response)) {
				// send command to start TLS encryption
				smtp_send_request($socket, "STARTTLS\r\n", $error, $smtp_debug);
				smtp_check_response($socket, "220", $error, $smtp_debug);
				if ($error) { $errors = $error; return false; }
		        
		        
				// start TLS encryption
				$crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT; // from 5.6.7 mean only 1.0
				if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
					$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
					$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
				}
				stream_socket_enable_crypto($socket, true, $crypto_method);
		        
				// after set TLS send EHLO again
				smtp_send_request($socket, "EHLO ".$smtp_host."\r\n", $error, $smtp_debug);
				smtp_check_response($socket, "250", $error, $smtp_debug);
				if ($error) { $errors = $error; return false; }
			}

			smtp_send_request($socket, "AUTH LOGIN\r\n", $error, $smtp_debug);
			smtp_check_response($socket, "334", $error, $smtp_debug);
			if ($error) { $errors = $error; return false; }

			smtp_send_request($socket, base64_encode($smtp_username)."\r\n", $error, $smtp_debug);
			smtp_check_response($socket, "334", $error, $smtp_debug);
			if ($error) { $errors = $error; return false; }

			smtp_send_request($socket, base64_encode($smtp_password)."\r\n", $error, $smtp_debug);
			smtp_check_response($socket, "235", $error, $smtp_debug);
			if ($error) { $errors = $error; return false; }
		}
		else
		{
			smtp_send_request($socket, "HELO " . $smtp_host . "\r\n", $error, $smtp_debug);
			smtp_check_response($socket, "250", $error, $smtp_debug);
			$errors .= $error;
		}

		if ($errors) { return false; }

		smtp_send_request($socket, "MAIL FROM: <" . $mail_from . ">\r\n", $error, $smtp_debug);
		smtp_check_response($socket, "250", $error, $smtp_debug);
		if ($error) { $errors = $error; return false; }

		if (!isset($mail_headers["to"]) || !$mail_headers["to"]) {
			$mail_headers["to"] = $mail_to;
		}
		$header_names = array("to", "cc", "bcc");
		for ($hf = 0; $hf < sizeof($header_names); $hf++) {
			$recipients_string = get_setting_value($mail_headers, $header_names[$hf], "");
			parse_value($recipients_string);
			$recipients_string = str_replace(";", ",", $recipients_string);
			if ($recipients_string) {
				$recipients_values = explode(",", $recipients_string);
				for ($i = 0; $i < sizeof($recipients_values); $i++) {
					$recipient_email = "";
					$recipient_value = $recipients_values[$i];
					if (preg_match("/<([^@]+@[^@]+(\.[^@]+)*\.[a-z]+)>/i", $recipient_value, $match)) {
						$recipient_email = $match[1];
					} elseif (preg_match("/\s*([^@]+@[^@]+(\.[^@]+)*\.[a-z]+)\s*/i", $recipient_value, $match)) {
						$recipient_email = trim($match[1]);
					}
					if ($recipient_email) {
						smtp_send_request($socket, "RCPT TO: <" . $recipient_email . ">\r\n", $error, $smtp_debug);
						smtp_check_response($socket, "250", $error, $smtp_debug);
						$errors .= $error;
					}
				}
			}
		}

		if ($errors) {
			return false;
		}

		// Preparing for sending data
		smtp_send_request($socket, "DATA\r\n", $error, $smtp_debug);
		smtp_check_response($socket, "354", $error, $smtp_debug);
		if ($error) {
			$errors = $error; return false;
		}

		// Send subject
		smtp_send_request($socket, "Subject: " . $mail_subject . "\r\n", $error, $smtp_debug);
  
		// Add other headers 
		$headers_string = email_headers_string($mail_headers, "\r\n");
		smtp_send_request($socket, $headers_string. "\r\n\r\n", $error, $smtp_debug);
  
		// Send the mail body 
		smtp_send_request($socket, $mail_body. "\r\n.\r\n", $error, $smtp_debug);
		smtp_check_response($socket, "250", $error, $smtp_debug);
		if ($error) {
			$errors = $error; return false;
		}

		smtp_send_request($socket, "QUIT\r\n", $error, $smtp_debug);
		fclose($socket);

		return true;
	} else {
		$headers_string = email_headers_string($mail_headers);
		$safe_mode = (strtolower(ini_get("safe_mode")) == "on" || intval(ini_get("safe_mode")) == 1) ? true : false;
		if ($safe_mode) {
			return @mail($mail_to, $mail_subject, $mail_body, $headers_string);
		} else {
			return @mail($mail_to, $mail_subject, $mail_body, $headers_string, $email_additional_parameters);
		}
	} 
}

function smtp_send_request($socket, $request, &$error, $debug = false) 
{
	if ($debug) { echo "\n<br/>REQUEST: ".$request; }
	$result = fwrite ($socket, $request);
	if ($result === false) {
		$error = "Error happen when sending request.";
		if ($debug) { echo "\n<br/>ERROR: ".$error; }
	}
	return $result;
}


function smtp_check_response($socket, $check_code, &$error, $debug = false) 
{
	$response = ""; $response_code = "";
	do {
		$line = fgets($socket, 512);
		if ($debug) { echo "\n<br/>RESPONSE: ".$line; }
		if (preg_match("/^(\d{3})\s/", $line, $matches)) {
			$response_code = $matches[1];
		}
		$response .= $line;
	} while ($line !== false && !$response_code);

	if ($check_code == $response_code) {
		return $response;
	} else {
		if ($response) {
			$error = "Error while sending email. Server response: " . $response . "\n";
		} else {
			$error = "No response from mail server.\n";
		}
		if ($debug) { echo "\n<br/>ERROR: ".$error; }
		return false;
	}
}

	function get_admin_permissions() 
	{
		global $db, $table_prefix;

		$permissions = array();
		$privilege_id = get_session("session_admin_privilege_id");
		if (strlen($privilege_id)) {
			$sql  = " SELECT block_name, permission FROM " . $table_prefix . "admin_privileges_settings ";
			$sql .= " WHERE privilege_id=" . $db->tosql($privilege_id, INTEGER, true, false);
			$db->query($sql);
			while($db->next_record()) {
				$block_name = $db->f("block_name");
				$permissions[$block_name] = $db->f("permission");
			}
		}
		
		return $permissions;
	}

function check_interval(&$time_left, &$error_message, $time_interval = 60)
{
	$message_sent = get_session("session_message_post");
	if (!$message_sent) { $message_sent = get_session("session_start_ts"); }
	if (!$message_sent) { $message_sent = va_timestamp(); }
	// allow to post message 1 per minute to prevent mass spam
	if (!$time_interval) {
		$messages_interval = 1;
		$messages_period = 2;
		$periods = array(0, 1, 60, 3600);
		$time_interval = $messages_interval * $periods[$messages_period];
	}

	$current_time = va_timestamp();
	// check if user can send a new message
	if (($message_sent + $time_interval) > $current_time) {
		$time_left = $message_sent + $time_interval - $current_time;
		$interval_message = str_replace("{quantity}", $time_left, SECONDS_QTY_MSG);
		$error_message = str_replace("{interval_time}", $interval_message , MESSAGE_INTERVAL_ERROR);
		return false;
	} else {
		$time_left = 0; $error_message = "";
		return true;
	}
}

function va_rowstocols(&$array_in, $cols) {
	$array_values = array(); $array_keys = array();
	$total_rows = ceil(count($array_in) / $cols);
	$rows_division = count($array_in) / $cols;
	$letter_index = 0; $subtract_index = 0; $prev_col_index = 0;
	foreach ($array_in as $array_key => $array_value) {
		$col_index = floor($letter_index / $rows_division);
		if ($prev_col_index != $col_index) { $subtract_index = $letter_index; }
		$new_index = (($letter_index - $subtract_index) * $cols) + $col_index;
		$array_values[$new_index] = $array_value;
		$array_keys[$new_index] = $array_key;
		// save current index value and increase letter index
		$prev_col_index = $col_index;
		$letter_index++;
	}
	ksort($array_keys);
	// save array with new order
	$array_in = array();
	foreach ($array_keys as $new_index => $array_key) {
		$array_in[$array_key] = $array_values[$new_index];
	}
}

	function va_charset_convert($in_charset, $out_charset, $str)
	{
		if (strtolower($in_charset) != strtolower($out_charset)) {
			if (function_exists("iconv")) {
				$str = iconv($in_charset, $out_charset, $str);			
			} else if(function_exists("mb_convert_encoding")) {
				$str = mb_convert_encoding($str, $in_charset, $out_charset);
			}
		}
		return $str;
	}
	
	function get_css_dim ($expr,$def_units='px')
	{
		$css_value = round($expr, 1);
		$pattern = '/(px|em|ex|\%|in|cm|mm|pt|pc)$/i';
		preg_match ($pattern, $expr, $pockets);
		if ($pockets) $cssUnit = strtolower($pockets[0]);
		else $cssUnit = $def_units;
		return $css_value . $cssUnit;
	}

function check_mobile()
{
	$useragent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
	$is_mobile = (preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)));
	return $is_mobile;
}

function check_tablet() 
{
	$user_agent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";

	$tablet_matches = array(
		'iPad'              => 'iPad|iPad.*Mobile', 
		'NexusTablet'       => '^.*Android.*Nexus(((?:(?!Mobile))|(?:(\s(7|10).+))).)*$',
		'SamsungTablet'     => 'SAMSUNG.*Tablet|Galaxy.*Tab|SC-01C|GT-P1000|GT-P1010|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P6810|GT-P7501',
		'Kindle'            => 'Kindle|Silk.*Accelerated',
		'AsusTablet'        => 'Transformer|TF101',
		'BlackBerryTablet'  => 'PlayBook|RIM Tablet',
		'HTCtablet'         => 'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200',
		'MotorolaTablet'    => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
		'NookTablet'        => 'Android.*Nook|NookColor|nook browser|BNTV250A|LogicPD Zoom2',
		'AcerTablet'        => 'Android.*\b(A100|A101|A110|A200|A210|A211|A500|A501|A510|A511|A700|A701|W500|W500P|W501|W501P|W510|W511|W700|G100|G100W|B1-A71)\b',
		'ToshibaTablet'     => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)',
		'LGTablet'          => '\bL-06C|LG-V900|LG-V909',
		'YarvikTablet'      => 'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)',
		'MedionTablet'      => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
		'ArnovaTablet'      => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT',
		'ArchosTablet'      => 'Android.*ARCHOS|101G9|80G9',
		'AinolTablet'       => 'NOVO7|Novo7Aurora|Novo7Basic|NOVO7PALADIN',
		'SonyTablet'        => 'Sony Tablet|Sony Tablet S|SGPT12|SGPT121|SGPT122|SGPT123|SGPT111|SGPT112|SGPT113|SGPT211|SGPT213|EBRD1101|EBRD1102|EBRD1201',
		'CubeTablet'        => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)|CUBE U8GT',
		'CobyTablet'        => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
		'SMiTTablet'        => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
		'RockChipTablet'    => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
		'TelstraTablet'     => 'T-Hub2',
		'FlyTablet'         => 'IQ310|Fly Vision',
		'bqTablet'          => 'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)',
		'HuaweiTablet'      => 'MediaPad|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
		'NecTablet'         => '\bN-06D|\bN-08D',
		'BronchoTablet'     => 'Broncho.*(N701|N708|N802|a710)',
		'VersusTablet'      => 'TOUCHPAD.*[78910]',
		'ZyncTablet'        => 'z1000|Z99 2G|z99|z930|z999|z990|z909|Z919|z900',
		'NabiTablet'        => 'Android.*\bNabi',
		'PlaystationTablet' => 'Playstation.*(Portable|Vita)',
		'GenericTablet'     => 'Android.*\b97D\b|Tablet(?!.*PC)|ViewPad7|MID7015|BNTV250A|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|hp-tablet',
	);

	$is_tablet = false;
	foreach ($tablet_matches as $device => $regexp){
		if (preg_match("/".$regexp."/is", $user_agent)) {
			$is_tablet = true; break;
		}
	}

	return $is_tablet;
}

function check_bot()
{
	$useragent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
  // additional possible rules to detect some other bots - transcoder|reader|extractor|monitoring|analyze
	$is_bot = (!$useragent || !preg_match("/^Mozilla/i", $useragent) || preg_match("/bot|spider|slurp|crawl|archiver|uptime|validator|fetcher|cron|checker|wget|curl|libwww/i", $useragent)); 
	return $is_bot;
}

function get_device_type()
{
	$device_type = 1;
	if (check_mobile()) {
		$device_type = 2;
	} else if (check_tablet()) {
		$device_type = 3;
	} else if (check_bot()) {
		$device_type = 4;
	}
	return $device_type;
}

function save_log_file($filename, $name, $content)
{
	$fp = @fopen($filename, "a");
	if ($fp) {
		$current_date = date("Y-m-j H:i:s A");
		@fwrite($fp, "\n-------------------------------");
		@fwrite($fp, "\nNAME: ".$name);
		@fwrite($fp, "\nDATE: ".$current_date);
		@fwrite($fp, "\n-------------------------------");
		@fwrite($fp, "\n".$content."\n");
		@fclose($fp);
	}
}

function save_debug_data($filename = "")
{
	global $session_prefix;

	if (!$filename) { $filename = "./db/debug-data.csv"; }
	$fp = @fopen($filename, "a");
	if ($fp) {
		$current_ts = time();
		$current_date = date("Y-m-j H:i:s A", $current_ts);
		// check tracking visit_id if possible
		$visit_param = isset($session_prefix) ? $session_prefix."visit_id" : "visit_id";
		$visit_id = isset($_SESSION[$visit_param]) ? $_SESSION[$visit_param] : "";
		// get user IP and country code for it if possible
		$user_ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
		$user_ip_country_code = isset($_SERVER["GEOIP_COUNTRY_CODE"]) ? $_SERVER["GEOIP_COUNTRY_CODE"] : "";
		$user_forwarded_ips = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : "";
		$user_agent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : ""; 
		// get page data
		$http_host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : ""; 
		$request_uri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : ""; 
		$referrer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : ""; 
		// remove array values from session
		$session_data = $_SESSION;
		if (is_array($session_data)) {
			foreach ($session_data as $session_key => $session_value) {
				if (is_array($session_value)) { unset($session_data[$session_key]); }
			}
		}
		// end remove arrays from session

		$get_data =  var_export($_GET, true);
		$post_data =  var_export($_POST, true);
		$cookie_data =  var_export($_COOKIE, true);
		$session_data =  var_export($session_data, true);
		$get_data =  preg_replace("/[\r\n]/s", "", $get_data);
		$post_data =  preg_replace("/[\r\n]/s", "", $post_data);
		$cookie_data =  preg_replace("/[\r\n]/s", "", $cookie_data);
		$session_data =  preg_replace("/[\r\n]/s", "", $session_data);

		$data = array(
			"Time" => $current_ts,
			"Date" => $current_date,
			"Visit_id" => $visit_id,
			"User_ip" => $user_ip,
			"User_ip_country_code" => $user_ip_country_code,
			"Forwarded_ips" => $user_forwarded_ips,
			"User_agent" => $user_agent,
			"Host" => $http_host,
			"Uri" => $request_uri,
			"Referrer" => $referrer,
			"Get" => $get_data,
			"Post" => $post_data,
			"Cookie" => $cookie_data ,
			"Session" => $session_data,
		);

		if (!filesize($filename)) {
			$data_keys = array_keys($data);
			fputcsv ($fp, $data_keys);
		}
		// get only values to save as CSV data
		$data_values = array_values($data);
		fputcsv ($fp, $data_values);
		@fclose($fp);
	}
}


function get_language()
{
	global $db, $table_prefix;
	global $default_language, $va_browser_language, $is_admin_path, $admin_language;

	if (isset($is_admin_path) && $is_admin_path) {
		$root_folder_path =  "../";
		if (isset($admin_language) && $admin_language !== "") {
			$default_language = $admin_language;
		}
		$cookie_lang_name = "cookie_admin_language";
		$sess_lang_name = "session_admin_language";
	} else {
		$is_admin_path = false;
		$root_folder_path =  "./";
		$cookie_lang_name = "cookie_lang";
		$sess_lang_name = "session_language";
	}
	$param_lang = get_param("lang");
	$sess_lang = get_session($sess_lang_name);
	$cook_lang = get_cookie($cookie_lang_name); 
	$lang_code = $param_lang;
	if (!$lang_code) { $lang_code = $sess_lang; }
	if (!$lang_code) { $lang_code = $cook_lang; }
	if (!$lang_code && isset($va_browser_language) && $va_browser_language) { 
		// check browser language
		$accept_language = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : ""; 
		$check_langs = array(); $lang_qualities = array();
		if ($accept_language) {
			$langs = explode(",", $accept_language);
			foreach($langs as $lang) {
				if (preg_match("/^([a-z]{2})(\-([a-z]{2}))?(\s*;\s*q=([0-9\.]+))?$/i", $lang, $matches)) {
					//	uk,en-us;q=0.7,en;q=0.3
					$check_code = $matches[1];
					$sub_code = isset($matches[3]) ? $matches[3] : "";
					$quality = isset($matches[5]) ? $matches[5] : 1;
					$check_langs[] = $check_code;
					$lang_qualities[] = $quality;
				}
			}
			array_multisort ($lang_qualities, SORT_DESC, $check_langs);
		}
		// check if we have any of accept languages
		foreach($check_langs as $id => $check_code) {
			$sql  = " SELECT language_code FROM ".$table_prefix."languages ";
			$sql .= " WHERE language_code=" . $db->tosql($check_code, TEXT);
			$sql .= " AND show_for_user=1 ";
			$db->query($sql);
			if ($db->next_record()) {
				$lang_code = $check_code; 
				break;
			}
		}

	}
	if (!$lang_code) { $lang_code = $default_language; }

	// check if we have messages for selected language
	if (strlen($lang_code) == 2 && file_exists($root_folder_path."messages/".$lang_code."/messages.php")) {
		// save selected language in cookies
		if ($param_lang && get_session("cookie_control") != 1) {
			set_session($sess_lang_name, $lang_code);
			setcookie($cookie_lang_name, $param_lang, time() + 3600 * 24 * 366); 
		}
	} else {
		$lang_code = "en";
	}

	// save language in session if it wasn't saved before
	if (!$sess_lang) {
		set_session($sess_lang_name, $lang_code);
	}

	return $lang_code;
}

function va_constant($constant_name)
{
	global $va_messages;
	if (isset($va_messages) && isset($va_messages[$constant_name])) {
		return $va_messages[$constant_name];
	} else if (defined($constant_name)) {
		return constant($constant_name);
	} else {
		return $constant_name;
	}
}

function va_message($constant_name)
{
	global $va_messages;
	if (isset($va_messages) && isset($va_messages[$constant_name])) {
		return $va_messages[$constant_name];
	} else if (defined($constant_name)) {
		return constant($constant_name);
	} else {
		return $constant_name;
	}
}

function va_config($auto_user_login = true)
{
	global $db, $db_type, $settings, $va_track, $table_prefix, $tracking_ignore;
	global $is_admin_path, $is_debug_script, $site_id, $root_site_id, $multisites_version;

	if (!isset($is_admin_path)) { $is_admin_path = false; }
	if (!isset($is_debug_script)) { $is_debug_script = false; }
	if ($is_admin_path && function_exists("comp_vers")) {
		if (comp_vers(va_version(), "3.3.3") == 1) {
			$multisites_version = true;		
		} else {
			$multisites_version = false;		
		}
		// get site id information
		if (isset($site_id)) {
			$root_site_id = $site_id;
		} else {
			$root_site_id = 1;
		}
		$param_site_id = get_param("param_site_id");
		if (!$param_site_id) { $param_site_id = get_session("session_site_id"); }
		if (!$param_site_id) { $param_site_id = $root_site_id; }
		set_session("session_site_id", $param_site_id);		
	} else {
		$multisites_version = true;
		//if (!isset($site_id)) { $site_id = 1; }
	}


	// get general settings from session or from database
	$session_start = get_session("session_start");
	if ($session_start) { 
		set_session("session_start_initial", ""); // clear flag for initial visit
	}
	$settings = DEBUG ? "" : get_session("session_settings");

	$update_layout = false;
	if (!is_array($settings)) {
		$settings = array();
		$update_layout = true; // always update layout settings when general settings updated
		
		$sql  = "SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= "WHERE (setting_type='global' OR setting_type='products' OR setting_type='version') ";
		if ($multisites_version) {
			if (isset($site_id) && ($site_id>1) )  {
				$sql .= "AND ( site_id=1 OR site_id = " . $db->tosql($site_id, INTEGER) ." ) ";
				$sql .= "ORDER BY site_id ASC ";
			} else {
				$sql .= "AND site_id=1 ";
			}
		}		
		$db->query($sql);
		while ($db->next_record()) {
			$settings[$db->f("setting_name")] = $db->f("setting_value");
		}
		if ($multisites_version) {
			$sql  = " SELECT * FROM " . $table_prefix . "sites ";
			if (isset($site_id)) {
				$sql .= " WHERE site_id=" . $db->tosql($site_id, INTEGER);
			} else {
				$sql .= " WHERE site_id=1 ";
			}
			$db->query($sql);
			if ($db->next_record()) {
				$settings["site_name"] = get_translation($db->f("site_name"));
				$settings["site_url"] = $db->f("site_url");
				$settings["secure_url"] = $db->f("site_url");
				$settings["admin_url"] = $db->f("admin_url");
				$settings["image_url"] = $db->f("image_url");
				$settings["site_class"] = $db->f("site_class");
				$settings["site_description"] = get_translation($db->f("site_description"));
				$settings["is_mobile"] = $db->f("is_mobile");
				$settings["is_mobile_redirect"] = $db->f("is_mobile_redirect");
			}
		}
	}
	// get site url to check domain
	$site_url = get_setting_value($settings, "site_url", "");

	// check if site offline
	$site_offline = get_setting_value($settings, "site_offline", 0);
	if (!$is_admin_path && !$is_debug_script && $site_offline) {
		$offline_message = get_setting_value($settings, "offline_message", OFFLINE_MSG);
		$admin_id = get_session("session_admin_id");
		$show_site = get_param("show_site");
		if ($show_site) {
			set_session("session_show_site", 1);
		} else {
			$show_site = get_session("session_show_site");
		}
		if (!($admin_id && $show_site)) {
			if ($admin_id) {
				// show link for administrator so he can check the site
				$offline_message .= "<br/><br/>".MENU_ADMIN.": <a href=\"?show_site=1\">".CHECK_SITE_MSG."</a>";
			}
			echo $offline_message;
			exit;
		}
	}

	// when we get general settings we can try automatically login user
	$user_id = get_session("session_user_id");
	if ($auto_user_login && !$user_id) {
		// automatic user login
		auto_user_login();
	}

	// update last visit page if user logged in
	if ($user_id) {
		$last_visit_page = get_request_uri();
		if (strlen($last_visit_page) > 255) {
			$last_visit_page = substr($last_visit_page, 0, 255);
		}
		$sql  = " UPDATE " . $table_prefix . "users SET ";
		$sql .= " last_visit_date=" . $db->tosql(va_time(), DATETIME);
		$sql .= ", last_visit_ip=" . $db->tosql(get_ip(), TEXT);
		$sql .= ", last_visit_page=" . $db->tosql($last_visit_page, TEXT);
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$db->query($sql);
	}


	// get layout settings
	$param_layout_id = get_param("set_layout_id");
	if ($update_layout || $param_layout_id)
	{
		// check for layout data
		$layout_id = ""; $layout_data = "";
		$user_id = get_session("session_user_id");
		if ($param_layout_id) {
			if ($multisites_version) {
				$sql  = " SELECT * FROM " . $table_prefix . "layouts AS lt ";
				if (isset($site_id))  {
					$sql .= " LEFT JOIN " . $table_prefix . "layouts_sites AS ls ON ls.layout_id=lt.layout_id";
					$sql .= " WHERE (lt.sites_all=1 OR ls.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
				} else {
					$sql .= " WHERE lt.sites_all=1 ";					
				}
				$sql .= " AND lt.layout_id=" . $db->tosql($param_layout_id, INTEGER);
				$sql .= " AND lt.show_for_user=1 ";
			} else {
				$sql  = " SELECT * FROM " . $table_prefix . "layouts ";
				$sql .= " WHERE layout_id=" . $db->tosql($param_layout_id, INTEGER);
				$sql .= " AND show_for_user=1 ";
			}
			$db->query($sql);
			if ($db->next_record()) {
				$layout_id = $param_layout_id;
				$layout_data = $db->Record;
				set_session("session_layout_id", $layout_id);
				if ($user_id) {
					$sql  = " UPDATE " . $table_prefix . "users SET layout_id=" . $db->tosql($layout_id, INTEGER);
					$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
					$db->query($sql);
				}
			}
		}

		$session_layout_id = get_session("session_layout_id");
		if (!$layout_id && $session_layout_id) {
			if ($multisites_version) {
				$sql  = " SELECT * FROM " . $table_prefix . "layouts AS lt ";
				if (isset($site_id))  {
					$sql .= " LEFT JOIN " . $table_prefix . "layouts_sites AS ls ON ls.layout_id=lt.layout_id";
					$sql .= " WHERE (lt.sites_all=1 OR ls.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
				} else {
					$sql .= " WHERE lt.sites_all=1 ";					
				}
				$sql .= " AND lt.layout_id=" . $db->tosql($session_layout_id, INTEGER);
				$sql .= " AND lt.show_for_user=1 ";
			} else {
				$sql  = " SELECT * FROM " . $table_prefix . "layouts ";
				$sql .= " WHERE layout_id=" . $db->tosql($session_layout_id, INTEGER);
				$sql .= " AND show_for_user=1 ";
			}
			$db->query($sql);
			if ($db->next_record()) {
				$layout_id = $session_layout_id;
				$layout_data = $db->Record;
			}
		} 

		if (!$layout_id && $user_id) {
			$user_info = get_session("session_user_info");
			$user_layout_id = get_setting_value($user_info, "layout_id", "");
			if ($user_layout_id) {
				if ($multisites_version) {
					$sql  = " SELECT * FROM " . $table_prefix . "layouts AS lt ";
					if (isset($site_id))  {
						$sql .= " LEFT JOIN " . $table_prefix . "layouts_sites AS ls ON ls.layout_id=lt.layout_id";
						$sql .= " WHERE (lt.sites_all=1 OR ls.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
					} else {
						$sql .= " WHERE lt.sites_all=1 ";					
					}
					$sql .= " AND lt.layout_id=" . $db->tosql($user_layout_id, INTEGER);
					$sql .= " AND lt.show_for_user=1 ";
				} else {
					$sql  = " SELECT * FROM " . $table_prefix . "layouts ";
					$sql .= " WHERE layout_id=" . $db->tosql($user_layout_id, INTEGER);
					$sql .= " AND show_for_user=1 ";
				}				
				$db->query($sql);
				if ($db->next_record()) {
					$layout_id = $user_layout_id;
					$layout_data = $db->Record;
				} else {
					$sql  = " UPDATE " . $table_prefix . "users SET layout_id=NULL ";
					$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
					$db->query($sql);
				}
			}
		}

		if (!$layout_id) {
			$default_layout_id = get_setting_value($settings, "layout_id", "");
			if ($default_layout_id) {
				$sql  = " SELECT * FROM " . $table_prefix . "layouts ";
				$sql .= " WHERE layout_id=" . $db->tosql($default_layout_id, INTEGER);
				$db->query($sql);
				if ($db->next_record()) {
					$layout_id = $default_layout_id;
					$layout_data = $db->Record;
				}
			}
		}

		if (!$layout_id) {
			$layout_data["templates_dir"] = "./templates/user";
			$layout_data["admin_templates_dir"] = "../templates/admin";
			$layout_data["top_menu_type"] = 1;
			$layout_data["style_name"] = "default";
			$layout_data["scheme_name"] = "";
		}
		foreach ($layout_data as $setting_name => $setting_value) {
			// move layout settings to general settings array
			$settings[$setting_name] = $setting_value;
		}
		$settings["layout_id"] = $layout_id;

		// save general settings in session
		set_session("session_settings", $settings);
	}

	if (isset($tracking_ignore) && $tracking_ignore) {
		$is_tracking = false;
	} else {
		$is_tracking = true;
	}
	// disable tracking if customer disable it
	$va_cookie = isset($_COOKIE["_va_cookie"]) ? json_decode($_COOKIE["_va_cookie"], true) : "";
	$cookie_analytics = get_setting_value($va_cookie, "analytics");
	if ($cookie_analytics == "no") {
		$is_tracking = false;
	}


	// get/save affialite with keywords and friend information if available
	$af = get_param("af");
	$kw = get_param("kw");
	$fr = get_param("friend");
	$session_start = get_session("session_start");
	if ($session_start && (strlen($af) || strlen($fr))) {
		$af_session = get_session("session_af");
		$kw_session = get_session("session_kw");
		$fr_session = get_session("session_friend");
		if ($af != $af_session) {
			// start new session for new affiliate code
			$session_start = false;
		}
		if ($fr != $fr_session) {
			// start new session for new friend code
			$session_start = false;
			set_session("session_friend_id", "");
		}
	}

	$parent_visit_id = "";
	$va_track = array();
	if (!$session_start && !$is_admin_path) {
		// check new track cookie
		$va_track = get_cookie("_va_track");
		if ($va_track) {
			$va_track = json_decode($va_track, true);
			$parent_visit_id = get_setting_value($va_track, "pid");
		} else {
			$va_track = array();
			// get old cookie information
			$cookie_visit = get_cookie("cookie_visit");
			$cookie_ip = ""; $visit_number = 0; $parent_visit_id = 0;
			if ($cookie_visit) {
				$cookie_visit = va_decrypt($cookie_visit, "cookie");
				$visit_info = explode("|", $cookie_visit);
				$cookie_ip = isset($visit_info[0]) ? $visit_info[0] : "";
				$visit_number = isset($visit_info[1]) ? $visit_info[1] : 0;
				$parent_visit_id = isset($visit_info[2]) ? $visit_info[2] : 0;
				// delete old cookie var
				setCookie("cookie_visit", "", (time() - 72000));
			}
			// save to new var
			if ($cookie_ip) { $va_track["ipi"] = $cookie_ip; } 
			if ($visit_number) { $va_track["vis"] = $visit_number; } 
			if ($parent_visit_id) { $va_track["pid"] = $parent_visit_id; } 
		}
		// calculate user visit and update track value
		$visit_number = get_setting_value($va_track, "vis", 0);
		$visit_number++;
		$va_track["vis"] = $visit_number;
		
		// it is first entrance
		$user_ip = get_ip();
		$user_agent = get_var("HTTP_USER_AGENT");
		$referer = get_var("HTTP_REFERER");
		$referer_host = "";
		if ($referer) {
			$parsed_url = parse_url($referer);
			$referer_host = isset($parsed_url["host"]) ? $parsed_url["host"] : "";

			$parsed_url = parse_url($site_url);
			$site_host = isset($parsed_url["host"]) ? $parsed_url["host"] : "localhost";
			$site_host = str_replace("www.", "", $site_host);
			$host_regexp = preg_quote($site_host, "/");
			if (preg_match("/".$host_regexp."/i", $referer_host)) {
				// ignore referer if it's the same host
				$referer = ""; $referer_host = "";
			}
		}

		set_session("session_start", 1);
		set_session("session_start_initial", 1); // flag that session just started
		set_session("session_start_ts", va_timestamp()); // save time when session was started
		set_session("session_referer", $referer);
		set_session("session_initial_ip", $user_ip);
		$va_track["dlv"] = time(); // date of last visit
		if (!isset($va_track["ipi"])) { $va_track["ipi"] = $user_ip; } // set initial IP
		$va_track["ipl"] = $user_ip; // set last visit IP
		if ($referer) { $va_track["ref"] = $referer; } // set referer

		set_session("session_cookie_ip", $va_track["ipi"]);
		set_session("session_visit_number", $visit_number);

		// get/save affialite and keywords information if available
		$affiliate_expire = get_setting_value($settings, "affiliate_cookie_expire", 60);
		if (!strlen($affiliate_expire)) { $affiliate_expire = 60; }
		if (!strlen($af)) { 
			$af = get_setting_value($va_track, "af");
			if (strlen($af)) {
				$afe = get_setting_value($va_track, "afe", 0);
				if ($afe < time()) { // affiliate cookie expired
					$af = "";
					unset($va_track["af"]); 
					unset($va_track["afe"]);
				}
			} else {
				$af = get_cookie("cookie_af"); // check old cookie var
				if (strlen($af)) {
					$va_track["af"] = $af; 
					$va_track["afe"] = time() + (3600 * 24 * $affiliate_expire); // when cookie expire
					setCookie("cookie_af", "", (time() - 72000)); // delete old cookie var
				}
			}
		} else {
			$va_track["af"] = $af; 
			$va_track["afe"] = time() + (3600 * 24 * $affiliate_expire); // when cookie expire
		}
		if ($kw) {
			$va_track["kw"] = $kw; 
		} else {
			$kw = get_setting_value($va_track, "kw");
		}
		set_session("session_af", $af);
		set_session("session_kw", $kw);

		// get/save friend information if available
		$fr_expire = get_setting_value($settings, "friend_cookie_expire", 60);
		if (!strlen($fr_expire)) { $fr_expire = 60; }
		if (!strlen($fr)) { 
			$fr = get_setting_value($va_track, "fr");
			if (strlen($fr)) {
				$fre = get_setting_value($va_track, "fre", 0);
				if ($fre < time()) { // affiliate cookie expired
					$fr = "";
					unset($va_track["fr"]); 
					unset($va_track["fre"]);
				}
			} else {
				$fr = get_cookie("cookie_friend"); // check old cookie var
				if (strlen($fr)) {
					$va_track["fr"] = $fr; 
					$va_track["fre"] = time() + (3600 * 24 * $fr_expire); // when cookie expire
					setCookie("cookie_friend", "", (time() - 72000)); // delete old cookie var
				}
			}
		} else {
			$va_track["fr"] = $fr; 
			$va_track["fre"] = time() + (3600 * 24 * $fr_expire); // when cookie expire
		}
		set_session("session_friend", $fr);

		// retrive cart if it was saved before
		$va_cart = json_decode(get_cookie("_va_cart"), true);
		$cookie_cart_id = get_setting_value($va_cart, "cartid");
		if ($cookie_cart_id) {
			include_once (dirname(__FILE__)."/shopping_functions.php");
			cart_retrieve("init");
		}
	}

	if ($is_tracking && !$session_start && !$is_admin_path) {
		$visit_id = 0;
		if (isset($settings["tracking_visits"]) && $settings["tracking_visits"] == 1) {

			// check search engine information
			$referer_engine_id = 0; $robot_engine_id = 0; $keywords_parameter = "";
			if ($user_agent || $referer) {
				$sql = " SELECT * FROM " . $table_prefix . "search_engines ";
				$db->query($sql);
				while ($db->next_record() && !$referer_engine_id && !$robot_engine_id) {
					$engine_id = $db->f("engine_id");
					$engine_parameter = $db->f("keywords_parameter");
					$referer_regexp = $db->f("referer_regexp");
					$user_agent_regexp = $db->f("user_agent_regexp");
					$ip_regexp = $db->f("ip_regexp");
					if ($referer && $referer_regexp && preg_match($referer_regexp, $referer)) {
						$referer_engine_id = $engine_id;
						$keywords_parameter = $engine_parameter;
					}
					if ($user_agent && $user_agent_regexp && preg_match($user_agent_regexp, $user_agent)) {
						$robot_engine_id = $engine_id;
					}
					if ($user_ip && $ip_regexp && preg_match($ip_regexp, $user_ip)) {
						$robot_engine_id = $engine_id;
					}
				}
			}

			// update keywords information
			if ($keywords_parameter && preg_match("/[\?\&]".$keywords_parameter."=([^&]+)/i", $referer, $matches)) {
				$kw = urldecode($matches[1]);
				set_session("session_kw", $kw);
			}

			$request_uri = get_request_uri(); 
			$request_page = get_request_page();
			$date_added = va_time();
			$week_added = get_yearweek($date_added);
			$sql  = " INSERT INTO " . $table_prefix . "tracking_visits (";
			$sql .= " parent_visit_id, visit_number, ";
			$sql .= " ip_long, ip_text, forwarded_ips, ";
			$sql .= " affiliate_code, keywords, user_agent, request_uri, request_page, ";
			$sql .= " referer, referer_host, referer_engine_id, robot_engine_id, ";
			$sql .= " date_added, year_added, month_added, week_added, day_added, hour_added, ";
			$sql .= " site_id) VALUES (";
			$sql .= $db->tosql($parent_visit_id, INTEGER, true, false) . ", " . $db->tosql($visit_number, INTEGER) . ", ";
			$sql .= $db->tosql(va_ip2long($user_ip), INTEGER, true, false) . ", " . $db->tosql($user_ip, TEXT, true, false) . ", ";
			$sql .= $db->tosql(get_var("HTTP_X_FORWARDED_FOR"), TEXT) . ", ";
			$sql .= $db->tosql($af, TEXT, true, false) . ", " . $db->tosql($kw, TEXT, true, false) . ", " . $db->tosql($user_agent, TEXT, true, false) . ", ";
			$sql .= $db->tosql($request_uri, TEXT, true, false) . ", " . $db->tosql($request_page, TEXT, true, false) . ", ";
			$sql .= $db->tosql($referer, TEXT, true, false) . ", " . $db->tosql($referer_host, TEXT, true, false) . ", ";
			$sql .= $db->tosql($referer_engine_id, INTEGER, true, false) . ", " . $db->tosql($robot_engine_id, INTEGER, true, false) . ", ";
			$sql .= $db->tosql($date_added, DATETIME, true, false) . ", " . $db->tosql($date_added[YEAR], INTEGER, true, false) . ", ";
			$sql .= $db->tosql($date_added[MONTH], INTEGER, true, false) . ", ";
			$sql .= $db->tosql($week_added, INTEGER, true, false) . ", ";
			$sql .= $db->tosql($date_added[DAY], INTEGER, true, false) . ", ";
			$sql .= $db->tosql($date_added[HOUR], INTEGER, true, false) . ", ";
			if (isset($site_id)) {
				$sql .= $db->tosql($site_id, INTEGER, true, false) . ") ";
			} else {
				$sql .= $db->tosql(1, INTEGER, true, false) . ") ";
			}
			$db->query($sql);
			$visit_id = $db->last_insert_id();
		}
		// update cookies visit
		$parent_visit_id = get_setting_value($va_track, "pid");
		if (!$parent_visit_id) { $va_track["pid"] = $parent_visit_id; }
		set_session("session_visit_id", $visit_id);
	}

	if ($is_tracking && isset($settings["tracking_pages"]) && $settings["tracking_pages"] == 1) {
		$visit_id = get_session("session_visit_id");
		$user_ip = get_ip();
		$request_uri = get_request_uri(); 
		if (strlen($request_uri) > 255) {
			$request_uri = substr($request_uri, 0, 255);
		}
		$request_page = get_request_page();
		$date_added = va_time();
		$sql  = " INSERT INTO " . $table_prefix . "tracking_pages (";
		$sql .= " visit_id,  ";
		$sql .= " ip_long, ip_text, forwarded_ips, ";
		$sql .= " request_uri, request_page, ";
		$sql .= " date_added, year_added, month_added, day_added, hour_added, ";
		$sql .= " site_id) VALUES (";
		$sql .= $db->tosql($visit_id, INTEGER, true, false) . ", ";
		$sql .= $db->tosql(va_ip2long($user_ip), INTEGER, true, false) . ", " . $db->tosql($user_ip, TEXT, true, false) . ", ";
		$sql .= $db->tosql(get_var("HTTP_X_FORWARDED_FOR"), TEXT) . ", ";
		$sql .= $db->tosql($request_uri, TEXT, true, false) . ", " . $db->tosql($request_page, TEXT, true, false) . ", ";
		$sql .= $db->tosql($date_added, DATETIME, true, false) . ", " . $db->tosql($date_added[YEAR], INTEGER, true, false) . ", ";
		$sql .= $db->tosql($date_added[MONTH], INTEGER, true, false) . ", " . $db->tosql($date_added[DAY], INTEGER, true, false) . ", ";
		$sql .= $db->tosql($date_added[HOUR], INTEGER, true, false) . ", ";
		if (isset($site_id)) {
			$sql .= $db->tosql($site_id, INTEGER, true, false) . ") ";
		} else {
			$sql .= $db->tosql(1, INTEGER, true, false) . ") ";
		}
		$db->query($sql);
	}

	// check if need to update _va_track cookies
	if (is_array($va_track) && count($va_track) > 0 && $cookie_analytics != "no") {
		setCookie("_va_track", json_encode($va_track), time() + (3600 * 24 * 366));
	}

	// MOBILE CHANGES: 
	// check mobile user agent and mobile version only for new user visit after and only when we save his visit on main site
	$parent_site = get_param("parent_site"); // check this parameter if user want to move from mobile site to parent
	if (!$session_start && !$parent_site && !$is_admin_path && isset($site_id)) {
		$is_mobile_device = check_mobile();
		$is_tablet_device = check_tablet();
		if ($is_mobile_device || $is_tablet_device) {
			$is_site_mobile = get_setting_value($settings, "is_mobile", 0);
			$parent_mobile_redirect = get_setting_value($settings, "is_mobile_redirect", 0);
			if (!$is_site_mobile) {
				// check if there is available mobile site for our main site
				$sql  = " SELECT site_id, is_mobile_redirect FROM " . $table_prefix . "sites ";
				$sql .= " WHERE parent_site_id=" . $db->tosql($site_id, INTEGER);
				$sql .= " AND is_mobile=1 ";
				$db->query($sql);
				if ($db->next_record()) {
					$is_mobile_redirect = $db->f("is_mobile_redirect");
					$mobile_site_id = $db->f("site_id");
					if ($is_mobile_redirect || $parent_mobile_redirect) {
						$sql  = "SELECT setting_value FROM " . $table_prefix . "global_settings ";
						$sql .= "WHERE setting_type='global' AND setting_name='site_url' ";
						$sql .= "AND site_id=" . $db->tosql($mobile_site_id, INTEGER);
						$mobile_site_url = get_db_value($sql);
						$site_url = get_setting_value($settings, "site_url", "");
						if ($mobile_site_url && $mobile_site_url != $site_url) {
						  // build redirect url to mobile site 
							$site_path = "/";
							if (preg_match("/^https?\:\/\/[^\/]+(.+)$/i", $site_url, $match)) {
								$site_path = $match[1];
							}
							$request_uri = get_request_uri();
							$site_uri = preg_replace("/^".preg_quote($site_path, "/")."/", "", $request_uri);

							header ("Location: " . $mobile_site_url.$site_uri);
							exit;
						}
					}
				}
			}
		}
	}
	// END MOBILE CHANGES: 
	return $settings;
}

function va_cookie_bar()
{
	global $t, $settings;
	$va_cookie = isset($_COOKIE["_va_cookie"]) ? json_decode($_COOKIE["_va_cookie"], true) : "";
	$cookie_bar = get_setting_value($settings, "cookie_bar", 0);
	$show_bar = get_param("cookie_bar");
	if ($cookie_bar) {
		if ($va_cookie && !$show_bar) {
			$cookie_analytics = get_setting_value($va_cookie, "analytics");
			$cookie_personal = get_setting_value($va_cookie, "personal");
			$cookie_target = get_setting_value($va_cookie, "target");
			$cookie_other = get_setting_value($va_cookie, "other");
			if ($cookie_analytics == "no") {
				setcookie("_va_track", "", time() - 360000); // viart tracking cookie
				setcookie("_ga", "", time() - 360000); // google analytics cookie
				setcookie("_gid", "", time() - 360000); // google analytics cookie
				setcookie("_gat", "", time() - 360000); // google analytics cookie
				setcookie("AMP_TOKEN", "", time() - 360000); // google analytics cookie
			}
			if ($cookie_personal == "no") {
				setcookie("_va_cart", "", time() - 360000); // viart cart cookie
				setcookie("cookie_lang", "", time() - 360000); // viart language cookie
				setcookie("cookie_user_login", "", time() - 360000); // viart user login cookie
				setcookie("cookie_user_password", "", time() - 360000); // viart user password cookie
			}
			if ($cookie_target == "no") {
				// code to delete targeting cookies
			}
			if ($cookie_other == "no") {
				// code to delete other cookies
			}
		} else {
			include_once (dirname(__FILE__)."/tabs_functions.php");
			$t->set_file("cookie_bar", "block_cookie_bar.html");
	  
			$disabled_message = va_constant("YOU_CANT_DISABLE_COOKIE_MSG");
			$t->set_var("disabled_message", htmlspecialchars($disabled_message));
			$cookie_bar_message = get_setting_value($settings, "cookie_bar_message", va_constant("COOKIE_BAR_DESC"));
			$t->set_var("cookie_bar_message", $cookie_bar_message);
			$cookie_consent_time = get_setting_value($settings, "cookie_consent_time", 0);
			$t->set_var("cookie_consent_time", $cookie_consent_time);
			$t->set_var("cookie_time", $cookie_consent_time);
	  
			// parse cookie settings and details
			$cookie_types = array(
				"necessary" => array("name" => va_constant("NECESSARY_COOKIES_MSG"), "message" => va_constant("NECESSARY_COOKIES_DESC")), 
				"analytics" => array("name" => va_constant("ANALYTICS_COOKIES_MSG"), "message" => va_constant("ANALYTICS_COOKIES_DESC")), 
				"personal" => array("name" => va_constant("PERSONAL_COOKIES"), "message" => va_constant("PERSONAL_COOKIES_DESC")), 
				"target" => array("name" => va_constant("TARGET_COOKIES_MSG"), "message" => va_constant("TARGET_COOKIES_DESC")), 
				"other" => array("name" => va_constant("OTHER_COOKIES_MSG"), "message" => va_constant("OTHER_COOKIES_DESC")), 
			);
			foreach ($cookie_types as $cookie_type => $cookie_data) {
				$cookie_show = get_setting_value($settings, "cookie_".$cookie_type."_show", 0);
				$cookie_disable = get_setting_value($settings, "cookie_".$cookie_type."_disable", 0);
				$cookie_name = get_setting_value($settings, "cookie_".$cookie_type."_name", $cookie_data["name"]);
				$cookie_message = get_setting_value($settings, "cookie_".$cookie_type."_message", $cookie_data["message"]);
				$cookie_type_class = ($cookie_disable) ? "" : "cookie-disable";
				$cookie_type_disabled = ($cookie_disable) ? "" : "disabled=\"disabled\"";
				$cookie_type_value = get_setting_value($va_cookie, $cookie_type);
				$cookie_type_checked = ($cookie_type_value != "no") ? "checked=\"checked\"" : "";
	  
				if ($cookie_show) {
					$tabs[$cookie_type] = array(
						"title" => $cookie_name,
						"data" => $cookie_message,
					);
					$t->set_var("cookie_type", $cookie_type);
					$t->set_var("cookie_name", $cookie_name);
					$t->set_var("cookie_type_class", $cookie_type_class);
					$t->set_var("cookie_type_checked", $cookie_type_checked);
					$t->set_var("cookie_type_disabled", $cookie_type_disabled);
	  
					$t->parse("cookie_types", true);
				}
			}
	  
			parse_tabs($tabs, "", "tab"); 
	  
			$t->parse_to("cookie_bar", "hidden_blocks", true);
		}
	}
}

function va_ip2long($ip) {
	$ip_long = ip2long($ip);
	if ($ip_long > 2147483647) { $ip_long -= 4294967296; }
	return $ip_long;
}

function va_data_cookie_update($update_data)
{
	$va_cookie = isset($_COOKIE["_va_cookie"]) ? json_decode($_COOKIE["_va_cookie"], true) : "";
	$cookie_personal = get_setting_value($va_cookie, "personal");
	if ($cookie_personal != "no") {
		$cookie_data = get_cookie("_va_data");
		if ($cookie_data) { $cookie_data = json_decode($cookie_data, true); } 
		if (!is_array($cookie_data)) { $cookie_data = array(); }
		foreach ($update_data as $key => $value) {
			$cookie_data[$key] = $value;
		}
		setCookie("_va_data", json_encode($cookie_data), time() + (3600 * 24 * 366));
	}
}

function va_cart_update($cart_data)
{
	$va_cart = get_cookie("_va_cart");
	if ($va_cart) { 
		$va_cart = json_decode($va_cart, true); 
	} else  { 
		$va_track = get_cookie("_va_track"); // check track old cookie where 
		$va_track = json_decode($va_track, true); 
		if (isset($va_track["cartid"]) && isset($va_track["sessid"])) {
			$va_cart = array("cartid" => $va_track["cartid"], "sessid" => $va_track["sessid"]);
			unset($va_track["cartid"]); 
			unset($va_track["sessid"]); 
			setCookie("_va_track", json_encode($va_track), time() + (3600 * 24 * 366));
		}
	}
	if (!is_array($va_cart)) { $va_cart = array(); }
	foreach ($cart_data as $key => $value) {
		$va_cart[$key] = $value;
	}
	setCookie("_va_cart", json_encode($va_cart), time() + (3600 * 24 * 366));
}
