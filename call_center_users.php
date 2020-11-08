<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  call_center_users.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	define("FOUND_USERS_MSG", "We've found <b>{found_records}</b> users matching the term(s) '<b>{search_string}</b>'");

	include_once("./includes/common.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./includes/parameters.php");
	include_once("./messages/".$language_code."/cart_messages.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");

	check_admin_security("create_orders");

	$sw = trim(get_param("sw"));
	$form_id = get_param("form_id");
	$form_name = get_param("form_name");
	$field_name = get_param("field_name");
	$id_name = get_param("id_name");
	$selection_type = get_param("selection_type");

  $t = new VA_Template($settings["templates_dir"]);
  $t->set_file("main","call_center_users.html");
	$t->set_var("call_center_users_href", "call_center_users.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_id", htmlspecialchars($form_id));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("field_name", htmlspecialchars($field_name));
	$t->set_var("id_name", htmlspecialchars($id_name));
	$t->set_var("selection_type", htmlspecialchars($selection_type));

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", "call_center_users.php");
	$s->set_parameters(false, true, true, false);
	$s->set_sorter(ID_MSG, "sorter_user_id", "1", "user_id", "", "", true);
	$s->set_sorter(USERNAME_FIELD, "sorter_login", "2", "login");
	$s->set_sorter(EMAIL_FIELD, "sorter_email", "3", "email");
	$s->set_sorter(NAME_MSG, "sorter_name", "4", "name");
	$s->set_sorter(PHONE_FIELD, "sorter_phone", "5", "phone");

	$where = "";
	$sa = array();
	if ($sw) {
		$sa = explode(" ", $sw);
		for($si = 0; $si < sizeof($sa); $si++) {
			if ($where) { $where .= " AND "; }
			else { $where .= " WHERE "; }
			$where .= " (login LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%'";
			$where .= " OR email LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR first_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR last_name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR name LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%' ";
			$where .= " OR city LIKE '%" . $db->tosql($sa[$si], TEXT, false) . "%')";
		}
	}

	$sql = " SELECT COUNT(*) FROM " . $table_prefix . "users " . $where;
	$db->query($sql);
	$db->next_record();
	$total_records = $db->f(0);

	// set up variables for navigator
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", "call_center_users.php");
	$records_per_page = 15;
	$pages_number = 5;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_records, false);

	$item_index = 0;
	$items_indexes = array();

	$sql  = " SELECT * ";
	$sql .= "	FROM " . $table_prefix . "users ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$t->parse("users_sorters", false);
		do {
			$user_id = $db->f("user_id");
			$user_info = $db->Record;
			$login = htmlspecialchars($db->f("login"));
			$email = htmlspecialchars($db->f("email"));
			$name = htmlspecialchars($db->f("name"));
			$first_name = htmlspecialchars($db->f("first_name"));
			$last_name = htmlspecialchars($db->f("last_name"));
			$phone = htmlspecialchars($db->f("phone"));
			$names = array($name, $first_name, $last_name);
			$full_name = implode(" ", $names);
			$user_info = array("user_id" => $user_id, "login" => $login);
			foreach ($parameters as $parameter_name) {
				$user_info[$parameter_name] = $db->f($parameter_name);
				$user_info["delivery_".$parameter_name] = $db->f("delivery_".$parameter_name);
			}

			if(is_array($sa)) {
				for($si = 0; $si < sizeof($sa); $si++) {
					$login = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $login);					
					$email = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $email);					
					$full_name = preg_replace ("/(" . $sa[$si] . ")/i", "<font color=blue><b>\\1</b></font>", $full_name);					
				}
			}

			$user_json = json_encode($user_info);

			$t->set_var("user_id", $user_id);
			$t->set_var("login", ($login));
			$t->set_var("email", ($email));
			$t->set_var("name", ($full_name));
			$t->set_var("phone", ($phone));
			$t->set_var("user_json", htmlspecialchars($user_json));

			if ($selection_type == "login") {
				$t->sparse("sign_in_block", false);
			} else {
				$t->sparse("select_user_block", false);
			}

			$t->parse("users", true);
		} while ($db->next_record());
	}

	if (strlen($sw)) {
		$found_message = str_replace("{found_records}", $total_records, FOUND_USERS_MSG);
		$found_message = str_replace("{search_string}", htmlspecialchars($sw), $found_message);
		$t->set_var("found_message", $found_message);
		$t->parse("search_results", false);
	}

	$t->pparse("main");


?>