<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  photos_manager.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/download_messages.php");

	check_user_session();

	$user_id = get_session("session_user_id");
	$id      = get_param("id");
	$key_id  = get_param("key_id");
	$photo_type   = get_param("photo_type");
	$operation    = get_param("operation");
	$control_name = get_param("control_name");
	$search_file  = get_param("sf");


	$show_preview_image     = get_setting_value($settings, "show_preview_image_client", 0);

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "photos_manager.html");

	$css_file = "";
	$style_name = get_setting_value($settings, "style_name", "");
	$scheme_class = get_setting_value($settings, "scheme_name", "");
	if (strlen($style_name)) {
		$css_file = "styles/".$style_name;
		if (!preg_match("/\.css$/", $style_name)) { $css_file .= ".css"; }
	}
	$t->set_var("css_file", $css_file);
	$t->set_var("scheme_class", $scheme_class);

	$t->set_var("photo_upload_href", "photo_upload.php");
	$t->set_var("photos_manager_href", "photos_manager.php");

	
	if ($operation == "delete" && strlen($id)) {

		$sql  = " SELECT * ";
		$sql .= " FROM " . $table_prefix . "users_photos ";
		$sql .= " WHERE user_id=" . $db->tosql($user_id, INTEGER);
		$sql .= " AND photo_id="   . $db->tosql($id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$tiny_photo = $db->f("tiny_photo");
			$small_photo = $db->f("small_photo");
			$large_photo = $db->f("large_photo");
			$super_photo = $db->f("super_photo");
			// delete files first 
			if ($tiny_photo && file_exists($tiny_photo)) {
				@unlink($tiny_photo);
			}
			if ($small_photo && file_exists($small_photo)) {
				@unlink($small_photo);
			}
			if ($large_photo && file_exists($large_photo)) {
				@unlink($large_photo);
			}
			if ($super_photo && file_exists($super_photo)) {
				@unlink($super_photo);
			}

			// delete record from DB
			$sql = " DELETE FROM " . $table_prefix . "users_photos WHERE photo_id=" . $db->tosql($id, INTEGER);
			$db->query($sql);
		}
	}

	$t->set_var("sf", htmlspecialchars($search_file));

	$s = new VA_Sorter($settings["templates_dir"], "sorter.html", "photos_manager.php");
	$s->set_parameters(false, true, true, true);
	$s->set_default_sorting("1", "asc");
	$s->set_sorter(NAME_MSG, "sorter_photo_name", "1", "photo_name");

	$t->set_var("photo_type", $photo_type);
	$t->set_var("control_name", $control_name);

	$sql    = " SELECT COUNT(*) FROM " . $table_prefix . "users_photos up ";
	$where  = " WHERE up.user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$where .= " AND up.photo_type=" . $db->tosql($photo_type, TEXT);
	if (strlen($search_file)) {
		$where .= " AND up.photo_name LIKE '%" . $db->tosql($search_file, TEXT, false, false) . "%' ";
	}
	
	$total_photos = get_db_value($sql.$where);

	// set up variables for navigator
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", "photos_manager.php");
	$records_per_page = 10;
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_photos, false);

	$sql  = " SELECT up.*,p.profile_name FROM " . $table_prefix . "users_photos up ";
	$sql .= " LEFT JOIN " . $table_prefix . "profiles p ON (p.profile_id=up.key_id AND up.photo_type=1) ";
	$sql .= $where;
	$sql .= $s->order_by;
	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$search_regexp = "";
		if (strlen($search_file)) {
			$search_regexp = preg_quote($search_file, "/");
		}

		$user_delete_photo_url = "photos_manager.php?photo_type=" . urlencode($photo_type);
		if (strlen($search_file)) {
			$user_delete_photo_url .= "&sf=" . urlencode($search_file);
		}
		if (strlen($control_name)) {
			$user_delete_photo_url .= "&control_name=" . urlencode($control_name);
		}
		if (strlen($key_id)) {
			$user_delete_photo_url .= "&key_id=" . urlencode($key_id);
		}
		do {
			$photo_id = $db->f("photo_id");
			$photo_path = $db->f("small_photo");
			$photo_name = basename($photo_path);
			$record_type = $db->f("photo_type"); 
			$key_name = "";
			if ($record_type == 1) {
				$key_name = $db->f("profile_name"); 
			}
			if ($search_regexp === "") {
				$photo_name_html = $photo_name;
			} else {
				$photo_name_html = preg_replace ("/(" . $search_regexp . ")/i", "<font color=blue><b>\\1</b></font>", $photo_name);
			}
			$photo_path_js = str_replace("'", "\\'", $photo_path);
			$t->set_var("photo_id", $photo_id);
			$t->set_var("photo_name", $photo_name);
			$t->set_var("photo_name_html", $photo_name_html);
			$t->set_var("key_name", htmlspecialchars($key_name));
			$t->set_var("photo_path", $photo_path);
			$t->set_var("photo_path_js", $photo_path_js);
			$t->set_var("user_delete_photo_url", $user_delete_photo_url . "&operation=delete&id=" . $photo_id);

			if ($show_preview_image == 1){
			  $t->parse("photo_row", true);
			} else {
			  $t->parse("photo_row_no_preview", true);
			}

		} while ($db->next_record());

		$t->set_var("no_photos", "");
		$t->parse("photos", false);
	} else {
		$t->parse("no_photos", false);
		$t->set_var("photos", "");
	}

	if (!$total_photos && !strlen($search_file)) {
		$t->set_var("search_photos", "");
	} else {
		$t->parse("search_photos", false);
	}

	$t->pparse("main");

?>