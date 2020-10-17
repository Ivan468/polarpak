<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_bookmarks.php                                      ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/sorter.php");

	check_admin_security();
		
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_bookmarks.html");
	$t->set_var("admin_bookmarks", "");
	
	$s = new VA_Sorter($settings["admin_templates_dir"], "sorter_img.html", "admin_bookmarks.php");
	$s->set_sorter(ID_MSG, "sorter_admin_bookmark_id", "1", "bookmark_id");
	$s->set_sorter(ADMIN_TITLE_MSG, "sorter_admin_title", "2", "title");
	$s->set_sorter(ADMIN_URL_SHORT_MSG, "sorter_admin_url", "3", "url");
	$s->set_sorter(IS_START_PAGE_MSG, "sorter_is_start_page", "4", "is_start_page");
	
	$admin_id = get_session("session_admin_id");
	
	$sql  = " SELECT bookmark_id, title, url, is_start_page, image_path";
	$sql .= " FROM " . $table_prefix . "bookmarks ";
	$sql .= " WHERE admin_id=" . $db->tosql($admin_id, INTEGER);
	$sql .= $s->order_by;
	$db->query($sql);
	if ($db->next_record()) {
		do {
			$bookmark_id = $db->f("bookmark_id");
 			$url = $db->f("url");
 			$title = $db->f("title");
 	 		$notes = $db->f("notes");
 	 		$is_start_page = $db->f("is_start_page") ? YES_MSG : NO_MSG;

			$t->set_var("admin_bookmark_id",  $bookmark_id);		  
  		$t->set_var("admin_url",          htmlspecialchars($url));
  		$t->set_var("admin_title",        htmlspecialchars($title));
   	 	$t->set_var("admin_notes",        htmlspecialchars($notes));
   	 	$t->set_var("admin_start_page",   htmlspecialchars($is_start_page));

			$t->parse("admin_bookmarks", true);
		} while ($db->next_record());
		$t->parse("bookmarks_table", true);
	} else {
		$t->set_var("error", NO_RECORDS);
		$t->parse("errors");
	}
				
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");
?>