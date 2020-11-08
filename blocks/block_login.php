<?php

	global $current_page;
	$default_title = "{LOGIN_TITLE}";

	// check admin call center and users permissions
	$permissions = get_admin_permissions();
	$users_perm = get_setting_value($permissions, "site_users", 0);

	$tag_name = get_setting_value($vars, "tag_name", "");
	$block_type = get_setting_value($vars, "block_type", "");
	$template_type = get_setting_value($vars, "template_type", "");
	$html_id = "pb_".$pb_id;
	if ($block_type != "bar" && $block_type != "header" && $template_type != "built-in") {
		if ($template_type == "default") {
			$html_template = "block_login.html"; 
		} else {
			$html_template = get_setting_value($block, "html_template", "block_login.html"); 
		}
		if ($block_type == "sub-block") {
			$html_id = "login_".$pb_id;
		  $t->set_file($vars["tag_name"], $html_template);
		} else {
			$html_id = "pb_".$pb_id;
		  $t->set_file("block_body", $html_template);
		}
	}
	$t->set_var("html_id", $html_id);
  $t->set_var("call_center_users_href", "call_center_users.php");

	if ($users_perm) {
		// only administrators with users permissions can sign in as different users
		set_script_tag("js/users.js");
		$t->sparse("admin_select_user", false);
	}

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_user_login = get_setting_value($settings, "secure_user_login", 0);
	$secure_user_profile = get_setting_value($settings, "secure_user_profile", 0);
	if ($secure_user_login && !get_session("session_user_id")) {
		// make secure login if user is not logged in
		$user_login_url = $secure_url . get_custom_friendly_url("user_login.php");
		$forgot_password_url = $secure_url . get_custom_friendly_url("forgot_password.php");
		$login_form_url = $secure_url . $current_page;
	} else {
		$user_login_url = $site_url . get_custom_friendly_url("user_login.php");
		$forgot_password_url = $site_url . get_custom_friendly_url("forgot_password.php");
		$login_form_url = $site_url . $current_page;
	}
	if ($secure_user_profile) {
		$user_profile_url = $secure_url . get_custom_friendly_url("user_profile.php");
	} else {
		$user_profile_url = $site_url . get_custom_friendly_url("user_profile.php");
	}

	$user_home_url = $site_url . get_custom_friendly_url("user_home.php");
	$query_string = transfer_params("", true);
	$return_page = get_param("return_page");
	if (!$return_page) {
		$return_page = $site_url . $current_page . $query_string;
		$return_page .= "#block_login_".$pb_id;
	}

	$t->set_var("user_types", "");
  $t->set_var("user_home_href", $user_home_url);
  $t->set_var("forgot_password_href", $forgot_password_url);
  $t->set_var("login_form_url", $login_form_url);
  $t->set_var("return_page", htmlspecialchars($return_page));

	$param_pb_id = get_param("pb_id");
	$operation = get_param("operation");

	$login_errors = ""; $user_login = "";
	if(strlen($operation))
	{
		if ($operation == "logout") {
			user_logout();
		} else if ($operation == "login") {

			$user_login = get_param("login");
			$user_password = get_param("password");
			
			if(!strlen($user_login)) {
				$error_message = str_replace("{field_name}", LOGIN_FIELD, REQUIRED_MESSAGE);
				$login_errors .= $error_message . "<br>";
			  $t->set_var("login_class", "error");
			}
	  
			if(!strlen($user_password)) {
				$error_message = str_replace("{field_name}", PASSWORD_FIELD, REQUIRED_MESSAGE);
				$login_errors .= $error_message . "<br>";
			  $t->set_var("password_class", "error");
			}

			if(!$login_errors && blacklist_check("log_in") == "blocked") {
				$login_errors = BLACK_IP_MSG;
			}
			
			if(!strlen($login_errors)) {
				user_login($user_login, $user_password, "", 0, "", false, $login_errors);
			}
		}

		if (($operation == "login" || $operation == "logout") && !$login_errors) {
			// make redirect to original page after successful login/logout operations
			header("Location: " . $return_page);
			exit;
		}
	}

	if(strlen($login_errors)) {
		$t->set_var("errors_list", $login_errors);
		$t->sparse("login_errors", false);
	}

	if (get_session("session_user_id"))	{
		$user_info = get_session("session_user_info");
		$user_login = get_setting_value($user_info, "login", "");
		$user_name = get_setting_value($user_info, "name", "");
		$t->set_var("login", htmlspecialchars($user_login));
		$t->set_var("user_name", htmlspecialchars($user_name));
		$t->set_var("LOGIN_AS_NAME", str_replace("{user_name}", $user_name, LOGIN_AS_MSG));
		$t->set_var("operation", "logout");
		$t->set_var("login_form", "");
		$t->parse("logout_form", false);
	}	else {
		// parse user types allowed for registration
		if ($t->block_exists("user_types")) {
			$sql  = " SELECT ut.type_id, ut.type_name ";
			$sql .= " FROM (" . $table_prefix . "user_types ut ";
			$sql .= " LEFT JOIN " . $table_prefix . "user_types_sites uts ON uts.type_id=ut.type_id)";
			$sql .= " WHERE (ut.sites_all=1 OR uts.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
			$sql .= " AND ut.is_active=1 AND ut.show_for_user=1";
			$sql .= " GROUP BY ut.type_id, ut.type_name  ";
			$db->query($sql);
			if ($db->next_record()) {
				do {
					$type_id = $db->f("type_id");
					$type_name = get_translation($db->f("type_name"));
					$t->set_var("user_type_name",  $type_name);
					$t->set_var("user_profile_url",  $user_profile_url . "?type=" . $type_id);
					$t->parse("user_types", true);
				} while ($db->next_record());
	  
				$t->sparse("new_user_block", false);
			}
		}

		$t->set_var("login", htmlspecialchars($user_login));
		$t->set_var("operation", "login");
		$t->set_var("logout_form", "");
		$t->parse("login_form", false);
	}

	$block_parsed = true;

