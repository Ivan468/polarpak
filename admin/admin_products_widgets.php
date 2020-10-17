<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_products_widgets.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path."includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/record.php");

	define ("GENERATE_BUTTON", "Generate");
	define ("BUTTONS_MSG", "Buttons");
	define ("FORM_NAME_MSG", "Form Name");
	define ("WIDGET_FORM_NAME_DESC", "use unique names if you intend to use more than one widget on page");

	include_once("./admin_common.php");

	check_admin_security("products_categories");

	$items_ids = get_param("items_ids");
	$site_url = get_setting_value($settings, "site_url", "");
	$parsed_url = parse_url($site_url);
	$domain_url = $parsed_url["scheme"]."://".$parsed_url["host"]."/";

  $t = new VA_Template($settings["admin_templates_dir"]);
  $t->set_file("main","admin_products_widgets.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_page_href", "admin_page.php");
	$t->set_var("admin_product_select_href", "admin_product_select.php");
	$t->set_var("admin_products_widgets_href", "admin_products_widgets.php");
	$t->set_var("site_url", htmlspecialchars($site_url));

	$items = array(); $index = 0;
	if ($items_ids) {
		$sql  = " SELECT i.item_id, i.item_name, i.price, i.short_description, ";
		$sql .= " i.tiny_image, i.small_image, i.big_image, i.super_image ";
		$sql .= " FROM " . $table_prefix . "items i ";
		$sql .= " WHERE i.item_id IN (" . $db->tosql($items_ids, INTEGERS_LIST) . ") ";
		$sql .= " ORDER BY i.item_name ";
		$db->query($sql);
		while($db->next_record()) {
			$item_id = $db->f("item_id");
			$item_name = $db->f("item_name");
			$tiny_image = $db->f("tiny_image");
			$small_image = $db->f("small_image");
			$large_image = $db->f("big_image");
			$super_image = $db->f("super_image");
			$min_quantity = $db->f("min_quantity");
			$max_quantity = $db->f("max_quantity");
			$price = $db->f("price");
			// for widget control add site url for all images
			if (preg_match("/^\//", $tiny_image)) {
				$tiny_image = $domain_url.$tiny_image;
			} else if ($tiny_image && !preg_match("/^http/i", $tiny_image)) {
				$tiny_image = $site_url.$tiny_image;
			}
			if (preg_match("/^\//", $small_image)) {
				$small_image = $domain_url.$small_image;
			} else if ($small_image && !preg_match("/^http/i", $small_image)) {
				$small_image = $site_url.$small_image;
			}
			if (preg_match("/^\//", $large_image)) {
				$large_image = $domain_url.$large_image;
			} else if ($large_image && !preg_match("/^http/i", $large_image)) {
				$large_image = $site_url.$large_image;
			}
			if (preg_match("/^\//", $super_image)) {
				$super_image = $domain_url.$super_image;
			} else if ($super_image && !preg_match("/^http/i", $super_image)) {
				$super_image = $site_url.$super_image;
			}
			$image_src = $tiny_image ? $tiny_image : $small_image;
			if (!$image_src) { $image_src = $site_url."images/tr.gif"; }

			$items[$index] = $db->Record;
			$items[$index]["id"] = $item_id;
			$items[$index]["item_price"] = currency_format($price);
			$items[$index]["item_name"] = htmlspecialchars($item_name);
			$items[$index]["tiny_image"] = htmlspecialchars($tiny_image);
			$items[$index]["small_image"] = htmlspecialchars($small_image);
			$items[$index]["large_image"] = htmlspecialchars($large_image);
			$items[$index]["super_image"] = htmlspecialchars($super_image);
			$items[$index]["min_quantity"] = htmlspecialchars($min_quantity);
			$items[$index]["max_quantity"] = htmlspecialchars($max_quantity);
	
			$t->set_var("item_id", $item_id);
			$t->set_var("item_name", $item_name);
			$t->set_var("item_price", currency_format($price));

			$t->set_var("item_name_js", str_replace("\"", "&quot;", $item_name));
			$t->set_var("image_src", $image_src);

			$t->parse_to("item_template", "selected_items", true);

			$index++;
		}
	}

	// parse template
	$t->set_var("item_id", "[item_id]");
	$t->set_var("image_src", "[image_src]");
	$t->set_var("item_price", "[item_price]");
	$t->set_var("item_name", "[item_name]");
	$t->parse("item_template", false);

	$t->set_var("items", json_encode($items, JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT));

	// set options
	$image_types = array(
		array("no", NO_IMAGE_MSG),
		array("tiny", IMAGE_TINY_MSG),
		array("small", IMAGE_SMALL_MSG),
		array("large", IMAGE_LARGE_MSG),
	);

	$product_controls =
		array(
			array("NONE",    NONE_MSG),
			array("LABEL",   LABEL_MSG),
			array("LISTBOX", LISTBOX_MSG),
			array("TEXTBOX", TEXTBOX_MSG)
			);

	$r = new VA_Record("");
	$r->add_textbox("widget_form_name", TEXT);
	$r->add_select("image_type", TEXT, $image_types);
	$r->add_select("quantity_control", TEXT, $product_controls);
	$r->add_checkbox("add_to_cart", INTEGER);
	$r->add_checkbox("view_cart", INTEGER);
	$r->add_checkbox("goto_checkout", INTEGER);
	$r->add_textbox("columns", TEXT);
	// default values
	$r->set_value("widget_form_name", "widget");
	$r->set_value("image_type", "small");
	$r->set_value("add_to_cart", "1");
	$r->set_value("columns", "3");
	// set form
	$r->set_form_parameters();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");


	$t->pparse("main");

?>