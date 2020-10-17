<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_slider.php                                         ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "includes/record.php");

	include_once("./admin_common.php");

	check_admin_security("sliders");
	
	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main","admin_slider.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_slider_href", "admin_slider.php");
	$t->set_var("admin_sliders_href", "admin_sliders.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", SLIDER_MSG, CONFIRM_DELETE_MSG));
	
	$r = new VA_Record($table_prefix . "sliders");
	$r->return_page = "admin_sliders.php";
	$r->add_where("slider_id", INTEGER);
	$r->add_textbox("slider_title", TEXT, ADMIN_TITLE_MSG);
	$r->add_textbox("slider_name", TEXT, NAME_MSG);
	$r->change_property("slider_name", REQUIRED, true);
	$r->add_textbox("slider_height", TEXT, SLIDER_HEIGHT_MSG);
	//$r->change_property("slider_height", REQUIRED, true);
	$r->add_textbox("slider_width", TEXT, 'width');
	$r->set_event(BEFORE_DELETE, "delete_slider_items");

	$r->process();
	
	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");
	
	/**
	 * Remove items of the removed slider
	 *
	 */
	function delete_slider_items() {
		global $db, $r, $table_prefix;
		$slider_id = $r->get_value("slider_id");
		if (intval($slider_id) > 0) {
			$sql = "DELETE FROM ".$table_prefix."sliders_items ";
			$sql .= "WHERE slider_id = ".$db->tosql($slider_id, INTEGER);
			
			$db->query($sql);
		}
	}
?>