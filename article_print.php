<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  article_print.php                                        ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
                           

	include_once("./includes/common.php");
	include_once("./includes/articles_functions.php");

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "article_print.html");
	set_head_tag("meta", array("name"=>"robots","content"=>"noindex"), "name", 1);

	$currency = get_currency();
	$article_id = get_param("article_id");

	if (!VA_Articles::check_exists($article_id)) {
		$t->set_var("item", "");
		$t->set_var("NO_ARTICLE_MSG", NO_ARTICLE_MSG);
		$t->parse("no_item", false);		
		$t->pparse("main", false);
		exit;
	}
	
	if (!VA_Articles::check_permissions($article_id, false, VIEW_ITEMS_PERM)) {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}
	
	// get and set global values
	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	if ($is_ssl) {
		$absolute_url = $secure_url;
	} else {
		$absolute_url = $site_url;
	}
	$css_file = "";
	$style_name = get_setting_value($settings, "style_name", "");
	$scheme_class = get_setting_value($settings, "scheme_name", "");
	if (strlen($style_name)) {
		$css_file  = $absolute_url."styles/".$style_name;
		if (!preg_match("/\.css$/", $style_name)) { $css_file .= ".css"; }
	}
	set_head_tag("base", array("href"=>$absolute_url), "href", 1);
	if ($css_file) {
		set_link_tag($css_file, "stylesheet", "text/css");
	}
	
	$top_id = VA_Articles::get_top_id($article_id);	
	$sql  = " SELECT article_details_fields FROM " . $table_prefix . "articles_categories ";
	$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
	$details_fields = get_db_value($sql); $db->f("article_details_fields");	
	$details_fields = ",," . $details_fields . ",,";

	$article_fields = array(
		"author_name", "author_email", "author_url", "link_url", "download_url", 
		"short_description", "full_description", "keywords", "notes"
	);

	$sql  = " SELECT article_id, article_title, article_date, date_end, ";
	$sql .= " author_name, author_email, author_url, link_url, download_url, ";
	$sql .= " short_description, is_html, full_description, image_small, image_large, ";
	$sql .= " total_votes, total_points, allowed_rate, ";
	$sql .= " keywords, notes ";
	$sql .= " FROM " . $table_prefix . "articles ";
	$sql .= " WHERE article_id= " . $db->tosql($article_id, INTEGER);
	$db->query($sql);
	if ($db->next_record())
	{
		$article_id = $db->f("article_id");
		$article_title = get_translation($db->f("article_title"));
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));
		$allowed_rate = $db->f("allowed_rate");

		if (!$full_description) { $full_description = $short_description; }
		if (strlen($short_description)) {
			$meta_description = $short_description;
		} else if (strlen($full_description)) {
			$meta_description = $full_description;
		} else {
			$meta_description = $article_title;
		}
		$meta_description = get_meta_desc($meta_description); 
		set_head_tag("meta", array("name"=>"description","content"=>$meta_description), "name", 1);
		$canonical_url = "article_print.php?article_id=".$article_id;
		if (isset($canonical_url) && strlen($canonical_url)) {
			set_link_tag(htmlspecialchars($canonical_url), "canonical", "");
		}


		$t->set_var("article_id", $article_id);
		$t->set_var("article_name", $article_title);
		$t->set_var("article_title", $article_title);

		// get fields values
		$article_date_string = ""; $date_end_string = "";
		if (strpos($details_fields, ",article_date,")) {
			$article_date = $db->f("article_date", DATETIME);
			$article_date_string  = va_date($datetime_show_format, $article_date);
			$t->set_var("article_date", $article_date_string);
			$t->global_parse("article_date_block", false, false, true);
		} else {
			$t->set_var("article_date_block", "");
		}
		if (strpos($details_fields, ",date_end,")) {
			$date_end = $db->f("date_end", DATETIME);
			$date_end_string = va_date($datetime_show_format, $date_end);
			$t->set_var("date_end", $date_end_string);
			$t->global_parse("date_end_block", false, false, true);
		} else {
			$t->set_var("date_end_block", "");
		}
		if (strlen($article_date_string) || strlen($date_end_string)) {
			$t->global_parse("date_block", false, false, true);
		}

		for ($i = 0; $i < sizeof($article_fields); $i++) {
			$field_name = $article_fields[$i];
			$fields[$field_name] = get_translation($db->f($field_name));
			if (strlen($fields[$field_name]) && strpos($details_fields, "," . $field_name . ",")) {
				$t->set_var($field_name, $fields[$field_name]);
				$t->global_parse($field_name . "_block", false, false, true);
			} else {
				$fields[$field_name] = "";
				$t->set_var($field_name, "");
				$t->set_var($field_name . "_block", "");
			}
		}

		if (strlen($fields["author_name"]) || strlen($fields["author_email"]) || strlen($fields["author_url"])) {
			$t->global_parse("author_block", false, false, true);
		} else {
			$t->set_var("author_block", false);
		}

		if (strpos($details_fields, ",full_description,")) {
			if ($db->f("is_html") != 1) {
				$full_description = nl2br(htmlspecialchars($full_description));
			}
			$t->set_var("full_description", $full_description);
		} else {
			$t->set_var("full_description", "");
		}

		$image_small = $db->f("image_small");
		if (strpos($details_fields, ",image_small,") && strlen($image_small)) {
			$t->set_var("alt", htmlspecialchars($article_title));
			$t->set_var("src", htmlspecialchars($image_small));
			$t->parse("image_small_block", false);
		} else {
			$t->set_var("image_small_block", "");
		}

		$image_large = $db->f("image_large");
		if (strpos($details_fields, ",image_large,") && strlen($image_large)) {
			$t->set_var("alt", htmlspecialchars($article_title));
			$t->set_var("src", htmlspecialchars($image_large));
			$t->parse("image_large_block", false);
		} else {
			$t->set_var("image_large_block", "");
		}
			
		$t->parse("item");
		$t->set_var("no_item", "");
	}
	
	$t->pparse("main", false);	
?>