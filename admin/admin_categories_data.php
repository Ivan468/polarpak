<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_categories_data.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once ("./admin_config.php");
	include_once ($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");

	header("Pragma: no-cache");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-Type: text/html; charset=" . CHARSET);

	$table = get_param("table");
	switch ($table) {
		case "registration_categories":
			check_admin_security("admin_registration");
			break;
		default:
			check_admin_security("products_categories");
			$table = "categories"; 
			break;
	}	

	$eol = get_eol();	
	$data = ""; $select = "";

	$parent_id = get_param("parent_id");
	$sql  = " SELECT c.category_id, c.category_name, COUNT(sc.category_id) as subcategories ";
	$sql .= " FROM (" . $table_prefix . $table . " c ";
	$sql .= " LEFT JOIN " . $table_prefix . $table . " sc ON c.category_id=sc.parent_category_id) ";
	$sql .= " WHERE c.parent_category_id=" . $db->tosql($parent_id, INTEGER);
	$sql .= " GROUP BY c.category_id, c.category_name ";
	$db->query($sql);
	if ($db->next_record()) {
		$select = "<select id=categories_" . $parent_id . " multiple size=12 class=selectCategories onChange=\"selectCategory(" . $parent_id . ", this)\">" . $eol;
		
		$data = array();
		do {
			$category_id = $db->f("category_id");
			$category_name = get_translation($db->f("category_name"));
			$subcategories = $db->f("subcategories");
			$data[] = array("id" => $category_id, "name" => $category_name, "parent_id" => $parent_id, "subcategories" => intval($subcategories));

			$select .= "<option value=" . $category_id . ">" . $category_name . $eol;
			if ($subcategories > 0) {
				$select .= " > ";
			}
		} while ($db->next_record());

		$select .= "</select>";
	} else {
		$data = "[no data]";
	}

	echo json_encode($data);

?>