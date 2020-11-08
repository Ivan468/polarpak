<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_cms_layout_load.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path."includes/record.php");
	include_once($root_folder_path."includes/cms_functions.php");
	include_once($root_folder_path."messages/".$language_code."/cart_messages.php");

	check_admin_security("cms_settings");

	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	$layout_id = get_param("layout_id");

  $t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("frame", "admin_cms_frame.html");
	$t->set_file("container", "admin_cms_container.html");

	// parse cms layout
	$sql  = " SELECT * FROM " . $table_prefix . "cms_layouts "; 
	$sql .= " WHERE layout_id=". $db->tosql($layout_id, INTEGER); 
	$db->query($sql);
	if ($db->next_record()) {
		$layout_id = $db->f("layout_id");
		$admin_template = $db->f("admin_template");

		$t->set_file("page_layout", $admin_template);

		$sql  = " SELECT * FROM " . $table_prefix . "cms_frames ";
		$sql .= " WHERE layout_id=" . $db->tosql($layout_id, INTEGER);
		$db->query($sql);
		while ($db->next_record()) {
			$frame_id = $db->f("frame_id");
			$frame_name = $db->f("frame_name");
			parse_value($frame_name);
			$tag_name = $db->f("tag_name");
			$blocks_allowed = $db->f("blocks_allowed");
	  
			$t->set_var("frame_id", $frame_id);
			$t->set_var("frame_name", $frame_name);
			$t->set_var("tag_name", $tag_name);
			
			if ($blocks_allowed) {
				$t->parse_to("frame_top", $tag_name."_top", false);
				$t->parse_to("frame_bottom", $tag_name."_bottom", false);
				$t->sparse("frame_top", false);
				$t->sparse("frame_bottom", false);
				$t->parse_to("frame", $tag_name, false);
			} else {
				$t->parse_to("container_top", $tag_name."_top", false);
				$t->parse_to("container_bottom", $tag_name."_bottom", false);
				$t->sparse("container_top", false);
				$t->sparse("container_bottom", false);
				$t->parse_to("container", $tag_name, false);
			}
		}

		$t->pparse("page_layout", false);
	}


?>