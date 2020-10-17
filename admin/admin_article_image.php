<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  admin_article_image.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");
	include_once($root_folder_path . "includes/record.php");

	check_admin_security("articles");

	$article_id = get_param("article_id");
	$category_id = get_param("category_id");
	if ($article_id) {
		$category_id = 0;
		$image_type = "article";
		$section_field = "article_title";
		$sql  = " SELECT article_title FROM " . $table_prefix . "articles_items ";
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
	} else {
		$image_type = "category";
		$section_field = "category_name";
		$sql  = " SELECT category_name FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
	}
	$db->query($sql);
	if ($db->next_record()) {
		$section_title = get_translation($db->f($section_field));
	} else {
		die(OBJECT_NO_EXISTS_MSG);
	}

	$t = new VA_Template($settings["admin_templates_dir"]);
	$t->set_file("main", "admin_article_image.html");

	$t->set_var("admin_href", "admin.php");
	$t->set_var("admin_article_href", "admin_article.php");
	$t->set_var("admin_article_edit_href", "admin_article_edit.php");
	$t->set_var("admin_article_image_href", "admin_article_image.php");
	$t->set_var("admin_article_images_href", "admin_article_images.php");
	$t->set_var("admin_upload_href", "admin_upload.php");
	$t->set_var("admin_select_href", "admin_select.php");
	$t->set_var("CONFIRM_DELETE_JS", str_replace("{record_name}", IMAGE_MSG, CONFIRM_DELETE_MSG));

	$t->set_var("article_id", htmlspecialchars($article_id));
	$t->set_var("category_id", htmlspecialchars($category_id));
	$t->set_var("section_title", htmlspecialchars($section_title));
	$t->set_var("image_type", htmlspecialchars($image_type));

	$full_image_url = get_setting_value($settings, "full_image_url", 0);
	$site_url_path = get_setting_value($settings, "site_url", "");
	if ($full_image_url){
		$t->set_var("image_site_url", $site_url_path);					
	} else {
		$t->set_var("image_site_url", "");					
	}

	$rp = get_param("rp");
	if (!$rp) { 
		$rp = "admin_article_images.php"; 
		if ($article_id) {
			$rp .= "?article_id=".urlencode($article_id);
		} else {
			$rp .= "?category_id=".urlencode($category_id);
		}
	}

	$r = new VA_Record($table_prefix . "articles_images");
	$r->return_page = $rp;

	$r->add_where("image_id", INTEGER);
	$r->add_textbox("article_id", INTEGER);
	$r->change_property("article_id", DEFAULT_VALUE, $article_id);
	if ($article_id) {
		$category_id = 0;
		$r->change_property("article_id", TRANSFER, true);
	}
	$r->add_textbox("category_id", INTEGER);
	$r->change_property("category_id", DEFAULT_VALUE, $category_id);
	if ($category_id) {
		$r->change_property("category_id", TRANSFER, true);
	}
	$r->add_textbox("image_title", TEXT, IMAGE_TITLE_MSG);
	$r->change_property("image_title", REQUIRED, true);
	$r->add_textbox("image_small", TEXT, IMAGE_SMALL_MSG);
	$r->change_property("image_small", REQUIRED, true);
	$r->add_textbox("image_small_alt", TEXT);
	$r->add_textbox("image_large", TEXT, IMAGE_LARGE_MSG);
	$r->add_textbox("image_large_alt", TEXT);

	$r->add_textbox("date_added", DATETIME);
	$r->change_property("date_added", USE_IN_UPDATE, false);
	$r->add_textbox("date_modified", DATETIME);
	$r->change_property("date_modified", USE_IN_INSERT, false);

	$r->set_event(AFTER_REQUEST, "set_article_image_values");

	$r->add_hidden("rp", TEXT);

	$r->process();

	include_once("./admin_header.php");
	include_once("./admin_footer.php");

	$t->pparse("main");

function set_article_image_values()
{
	global $r;
	$r->set_value("date_added", va_time());
	$r->set_value("date_modified", va_time());
}

?>
