<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  forums.php                                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./includes/forums_functions.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$display_forums = get_setting_value($settings, "display_forums", 0);
	if ($display_forums == 1) {
		// user need to be logged in before viewing forum 
		check_user_session();
	}

	$cms_page_code = "forum_list";
	$script_name   = "forums.php";
	$current_page  = get_custom_friendly_url("forums.php");
	$currency = get_currency();

	$category_id   = get_param("category_id");

	$html_title = ""; $meta_description = ""; $meta_keywords = ""; 
	
	// retrieve info about current category
	if ($category_id) {
		if (VA_Forum_Categories::check_exists($category_id)) {
			$sql  = " SELECT category_name, short_description, full_description ";
			$sql .= " FROM " . $table_prefix . "forum_categories ";
			$sql .= " WHERE category_id = " . $db->tosql($category_id, INTEGER);
			$sql .= " AND allowed_view = 1 ";
			$db->query($sql);
			if ($db->next_record()) {
				$category_name = get_translation($db->f("category_name"));
				$short_description = get_translation($db->f("short_description"));
				$full_description = get_translation($db->f("full_description"));
	  
				$auto_meta_title = $category_name; 
				if (strlen($short_description)) {
					$meta_description = $short_description;
				} elseif (strlen($full_description)) {
					$meta_description = $full_description;
				}
			} else {
				header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
				exit;
			}
		} else {
			header ("Location: " . get_custom_friendly_url("forums.php"));
			exit;
		}
	} else {
		$auto_meta_title = FORUM_TITLE;
	}

	include_once("./includes/page_layout.php");
	
?>