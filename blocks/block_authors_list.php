<?php

	$default_title = "{AUTHORS_MSG}";

	// friendly url settings
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	// block settings
	$authors_recs = get_setting_value($vars, "recs", 50);
	$authors_cols = get_setting_value($vars, "cols", 2);
	if ($authors_cols < 0) { $authors_cols = 1; }
	$show_image_tiny = get_setting_value($vars, "image_tiny", 0);
	$show_image_small = get_setting_value($vars, "image_small", 0);
	$show_image_large = get_setting_value($vars, "image_large", 0);
	$show_image_super = get_setting_value($vars, "image_super", 0);
	$show_short_desc = get_setting_value($vars, "short_description", 0);
	$show_full_desc = get_setting_value($vars, "full_description", 0);
	$html_template = get_setting_value($block, "html_template", "block_authors_list.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("authors_rows",  "");
	$t->set_var("authors_cols",  "");
	$t->set_var("columns_class", "cols-".$authors_cols);

	$db->RecordsPerPage = $authors_recs;
	$db->PageNumber = 1;
	$sql  = " SELECT a.*  ";
	$sql .= " FROM (" . $table_prefix . "authors a ";
	$sql .= " LEFT JOIN " . $table_prefix . "authors_sites aus ON a.author_id=aus.author_id) ";
	$sql .= " WHERE (a.sites_all=1 OR aus.site_id=" . $db->tosql($site_id, INTEGER) . ") ";
	if (isset($authors_first) && strlen($authors_first)) {
		if ($authors_first == "0-9") {
			$sql .= " AND (a.name_first IN ('0','1','2','3','4','5','6','7','8','9') ";
			$sql .= " OR a.other_first IN ('0','1','2','3','4','5','6','7','8','9') )";
		} else {
			$sql .= " AND (a.name_first=" . $db->tosql($authors_first, TEXT);
			$sql .= " OR a.other_first=" . $db->tosql($authors_first, TEXT) . ")";
		}
	}
	$sql .= " ORDER BY a.author_name ";
	$db->query($sql);
	if ($db->next_record()) {
		$author_index = 0; $col_style = "";
		if ($authors_cols > 1) {
			$col_style = "width: ".round(100 / $authors_cols, 2)."%;";
		}
		$t->set_var("col_style", $col_style);
		do {
			// get data
			$author_index++;

			$author_id = $db->f("author_id");
			$author_name = $db->f("author_name");
			$friendly_url = $db->f("friendly_url");

			$image_tiny = $db->f("image_tiny");
			$image_tiny_alt = $db->f("image_tiny_alt");
			$image_small = $db->f("image_small");
			$image_small_alt = $db->f("image_small_alt");
			$image_large = $db->f("image_large");
			$image_large_alt = $db->f("image_large_alt");
			$image_super = $db->f("image_super");
			$image_super_alt = $db->f("image_super_alt");

			$short_description = get_translation($db->f("short_description"));
			$full_description = get_translation($db->f("full_description"));

			$t->set_var("author_id", $author_id);
			$t->set_var("author_name", htmlspecialchars($author_name));
			if ($friendly_urls && $friendly_url) {
				$author_url = $friendly_url . $friendly_extension;
				$author_lyrics_url = $friendly_url."-lyrics".$friendly_extension;
				$author_articles_url = $friendly_url."-articles".$friendly_extension;
			} else {
				$author_url = "author.php?author_id=" . $author_id;
				$author_lyrics_url = "author_articles.php?author_id=" . $author_id;
				$author_articles_url = "author_articles.php?author_id=" . $author_id;
			}
			$t->set_var("author_url", htmlspecialchars($author_url));
			$t->set_var("author_lyrics_url", htmlspecialchars($author_lyrics_url));
			$t->set_var("author_articles_url", htmlspecialchars($author_articles_url));

			if ($show_image_tiny && $image_tiny) {
				$t->set_var("image_tiny_src", $image_tiny);
				$t->set_var("image_tiny_alt", $image_tiny_alt);
				$t->sparse("image_tiny_block", false);
			} else {
				$t->set_var("image_tiny_block", "");
			}
			if ($show_image_small && $image_small) {
				$t->set_var("image_small_src", $image_small);
				$t->set_var("image_small_alt", $image_small_alt);
				$t->sparse("image_small_block", false);
			} else {
				$t->set_var("image_small_block", "");
			}
			if ($show_image_large && $image_large) {
				$t->set_var("image_large_src", $image_large);
				$t->set_var("image_large_alt", $image_large_alt);
				$t->sparse("image_large_block", false);
			} else {
				$t->set_var("image_large_block", "");
			}
			if ($show_image_super && $image_super) {
				$t->set_var("image_super_src", $image_super);
				$t->set_var("image_super_alt", $image_super_alt);
				$t->sparse("image_super_block", false);
			} else {
				$t->set_var("image_super_block", "");
			}
			if ($show_short_desc) {
				$t->set_var("short_description", $short_description);
				$t->sparse("short_description_block", false);
			} else {
				$t->set_var("short_description_block", "");
			}
			if ($show_full_desc) {
				$t->set_var("full_description", $full_description);
				//$t->set_var("full_description", nl2br(htmlspecialchars($full_description)));
				$t->sparse("full_description_block", false);
			} else {
				$t->set_var("full_description_block", "");
			}

			$column_index = ($author_index % $authors_cols) ? ($author_index % $authors_cols) : $authors_cols;
			$t->set_var("column_class", "col-".$column_index);

			$t->parse("authors_cols", true);
			if ($author_index % $authors_cols == 0) {
				$t->parse("authors_rows", true);
				$t->set_var("authors_cols", "");
			}
		} while ($db->next_record());
		if ($author_index % $authors_cols) {
			$t->parse("authors_rows", true);
		}
		$block_parsed = true;
	}


?>