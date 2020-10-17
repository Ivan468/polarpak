<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_category_select.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once("../messages/".$language_code."/cart_messages.php");
	include_once("./admin_common.php");

	check_admin_security("products_categories");

	$sw = trim(get_param("sw"));
	$form_name = get_param("form_name");
	$field_name = get_param("field_name");
	$id_name = get_param("id_name");
	$selection_type = get_param("selection_type");
	$list_type = get_param("list_type");
	$start_id = get_param("start_id");

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_category_select.html");
	$t->set_var("admin_category_select_href", "admin_category_select.php");
	$t->set_var("sw", htmlspecialchars($sw));
	$t->set_var("form_name", htmlspecialchars($form_name));
	$t->set_var("field_name", htmlspecialchars($field_name));
	$t->set_var("id_name", htmlspecialchars($id_name));
	$t->set_var("selection_type", htmlspecialchars($selection_type));
	$t->set_var("list_type", htmlspecialchars($list_type));

	$ajax = get_param("ajax");
	$category_id = get_param("category_id");
	if (!$category_id) { $category_id = 0; }
	$parent_ids = 0;
	if ($ajax) { // Ajax call for tree branch
		$parent_ids = $category_id;
	} else if ($category_id) { // Tree-type structure
		if ($list_type == "articles_category" || $list_type == "articles_categories") {
			$sql  = " SELECT category_path ";
			$sql .= " FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		} else {
			$sql  = " SELECT category_path ";
			$sql .= " FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		}
		$db->query($sql);
		if ($db->next_record()) {
			$parent_ids  = $db->f("category_path");
			$parent_ids .= $category_id;
		}
	}	else {
		$parent_ids = 0;
	}

	$start_path = "";
	if ($start_id) {
		if ($list_type == "articles_category" || $list_type == "articles_categories") {
			$sql  = " SELECT category_path FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($start_id, INTEGER);;
		} else {
			$sql  = " SELECT category_path FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id=" . $db->tosql($start_id, INTEGER);;
		}
		$db->query($sql);
		if ($db->next_record()) {
			$start_path = $db->f("category_path").$start_id.",";
		}

	}

	$categories = array();
	$categories_ids = array();
	if ($list_type == "articles_category" || $list_type == "articles_categories") {
		$sql  = " SELECT c.category_id, c.parent_category_id, c.category_path, c.category_name, c.friendly_url ";
		$sql .= " FROM " . $table_prefix . "articles_categories c ";
		if ($start_id) {
			$sql .= " WHERE c.parent_category_id IN (" . $db->tosql($start_id, INTEGERS_LIST) . ") ";
		} else {
			$sql .= " WHERE c.parent_category_id IN (" . $db->tosql($parent_ids, INTEGERS_LIST) . ") ";
		}
		$sql .= " ORDER BY c.category_order, c.category_name ";
	} else {
		$sql  = " SELECT c.category_id, c.category_path, c.category_name, c.friendly_url, ";
		$sql .= " c.image, c.image_alt, c.image_large, c.image_large_alt, c.parent_category_id ";		
		$sql .= " FROM " . $table_prefix . "categories c ";
		$sql .= " WHERE c.parent_category_id IN (" . $db->tosql($parent_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY c.category_order, c.category_name ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$cur_category_id = $db->f("category_id");
		$categories_ids[] = $cur_category_id;
		$category_path = $db->f("category_path");
		$category_name = get_translation($db->f("category_name"));
		$friendly_url = $db->f("friendly_url");
		$image = $db->f("image");
		$image_alt = get_translation($db->f("image_alt"));
		$image_tree = true;
		$image_onclick = "loadCategories($cur_category_id);return false;";

		$parent_category_id = $db->f("parent_category_id");
		$categories[$cur_category_id]["parent_id"] = $parent_category_id;
		$categories[$cur_category_id]["category_path"] = $category_path;
		$categories[$cur_category_id]["category_name"] = $category_name;
		$categories[$cur_category_id]["friendly_url"] = $friendly_url;

		$categories[$cur_category_id]["image"] = $image;
		$categories[$cur_category_id]["image_tree"] = $image_tree;
		$categories[$cur_category_id]["image_alt"] = $image_alt;
		$categories[$cur_category_id]["image_large"] = $db->f("image_large");
		$categories[$cur_category_id]["image_large_alt"] = get_translation($db->f("image_large_alt"));
		$categories[$cur_category_id]["image_onclick"] = $image_onclick;

		$categories[$parent_category_id]["subs"][] = $cur_category_id;
	}

	// calculate subs categories
	if ($list_type == "articles_category" || $list_type == "articles_categories") {
		$sql  = " SELECT parent_category_id, COUNT(*) AS subs_number ";
		$sql .= " FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
		$sql .= " GROUP BY parent_category_id ";
	} else {
		$sql  = " SELECT parent_category_id, COUNT(*) AS subs_number ";
		$sql .= " FROM " . $table_prefix . "categories ";
		$sql .= " WHERE parent_category_id IN (" . $db->tosql($categories_ids, INTEGERS_LIST) . ") ";
		$sql .= " GROUP BY parent_category_id ";
	}
	$db->query($sql);
	while ($db->next_record()) {
		$parent_category_id = $db->f("parent_category_id");
		$subs_number = $db->f("subs_number");
		$categories[$parent_category_id]["subs_number"] = $subs_number;
	}

	if ($ajax) { // Ajax call for tree branch
		show_categories_tree($category_id);
		$sub_categories_html = $t->get_var("subcategories_".$category_id);
		echo $sub_categories_html;
		exit;
	} else {
		if ($start_id) {
			show_categories_tree($start_id);
		} else {
			show_categories_tree(0);	
		}
	}


	$t->pparse("main");


	function show_categories_tree($parent_id)
	{
		global $t, $categories, $start_id;

		$subs = (isset($categories[$parent_id]) && isset($categories[$parent_id]["subs"])) ? $categories[$parent_id]["subs"] : array();
		for ($i = 0, $ic = count($subs); $i < $ic; $i++)
		{
			$current_id = $subs[$i];
			$show_category_id = $subs[$i];
			$category_path = $categories[$show_category_id]["category_path"];
			$category_name    = $categories[$show_category_id]["category_name"];
			$category_name_js = str_replace("'", "\\'", htmlspecialchars($category_name));
			$image_tree       = isset($categories[$show_category_id]["image_tree"]) ? is_array($categories[$show_category_id]["image_tree"]) : false; 
			
			$subs_number   = isset($categories[$show_category_id]["subs_number"]) ? $categories[$show_category_id]["subs_number"] : 0; // number of categories which could be loaded 
			$has_nested    = isset($categories[$show_category_id]["subs"]) ? is_array($categories[$show_category_id]["subs"]) : false;
			$is_last       = ($i == $ic - 1);
			$is_first      = ($i == 0);
			
			if ($has_nested) {
				show_categories_tree($show_category_id);
			}
			
			$category_image = ""; $image_alt = "";
			if ($subs_number) {
				if ($has_nested) {
					$category_image = "../images/icons/minus.gif";
				} else {
					$category_image = "../images/icons/plus.gif";
				}
			} else {
				//$category_image = "../images/icons/empty.gif";
			}
			$image_onclick = isset($categories[$show_category_id]["image_onclick"]) ? $categories[$show_category_id]["image_onclick"] : "";
			
			$t->set_var("category_id", $show_category_id);
			$t->set_var("category_name", $category_name);
			$t->set_var("category_name_hidden", htmlspecialchars($category_name));
			$t->set_var("category_path", $category_path);

			$t->set_var("category_name_js", $category_name_js);

			$category_class = "";
			if ($is_first) {$category_class .= " firstCategory";}
			if ($is_last) { 
				$category_class .= " lastCategory";
			}
			if (!$subs_number) { 
				if ($is_last) {
					$category_class .= " lastEmptyCategory"; 
				} else {
					$category_class .= " emptyCategory"; 
				}
			}
			$t->set_var("category_class", $category_class);		
			
			if ($category_image) {
				if (preg_match("/^(http|https|ftp|ftps)\:\/\//", $category_image)) {
					$image_size = "";
				} else {
					$image_size = @GetImageSize($category_image);
				}
				if (!strlen($image_alt)) { $image_alt = $category_name; }
				$t->set_var("alt", htmlspecialchars($image_alt));
				$t->set_var("src", htmlspecialchars($category_image));
				if (is_array($image_size)) {
					$t->set_var("width", "width=\"" . $image_size[0] . "\"");
					$t->set_var("height", "height=\"" . $image_size[1] . "\"");
				} else {
					$t->set_var("width", "");
					$t->set_var("height", "");
				}
				if ($image_onclick) {
					$t->set_var("onclick", htmlspecialchars($image_onclick));
				} else {
					$t->set_var("onclick", "");
				}
				$t->parse("category_image", false);
			} else {
				$t->set_var("category_image", "");
			}
			
			if ($has_nested) {
				$t->set_var("subcategories", $t->get_var("subcategories_".$current_id));
				$t->set_var("subcategories_".$current_id, "");
			} else {
				$t->set_var("subcategories", "");
			}		

			// parse all categories to their parent tag
			$t->parse_to("categories", "categories_" . $parent_id);
		}		

		// parse categories block
		$t->set_var("parent_id", $parent_id);
		$t->set_var("categories", $t->get_var("categories_".$parent_id));
		if ($parent_id && $parent_id == $start_id) {
			$t->parse("categories_block");
		} else if ($parent_id) {
			$t->parse_to("categories_block", "subcategories_".$parent_id);
		} else {
			$t->parse("categories_block");
		}	

	}

?>