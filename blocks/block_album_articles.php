<?php

	$default_title = "{album_name}: {ARTICLES_TITLE}";

	// friendly url settings
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	// block settings
	$articles_recs = get_setting_value($vars, "recs", 50);
	$articles_cols = get_setting_value($vars, "cols", 2);
	if ($articles_cols < 0) { $articles_cols = 1; }
	$show_image_tiny = get_setting_value($vars, "image_tiny", 0);
	$show_image_small = get_setting_value($vars, "image_small", 0);
	$show_image_large = get_setting_value($vars, "image_large", 0);
	$show_image_super = get_setting_value($vars, "image_super", 0);
	$show_short_desc = get_setting_value($vars, "short_description", 0);
	$show_full_desc = get_setting_value($vars, "full_description", 0);

	$html_template = get_setting_value($block, "html_template", "block_album_articles.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("articles_rows",  "");
	$t->set_var("articles_cols",  "");
	$t->set_var("columns_class", "cols-".$articles_cols);


	$sql  = " SELECT a.*  ";
	$sql .= " FROM " . $table_prefix . "albums a ";
	$sql .= " WHERE a.album_id=" . $db->tosql($album_id, TEXT);
	$db->query($sql);
	if ($db->next_record()) {
		$album_name = $db->f("album_name");
	  $t->set_var("album_name", htmlspecialchars($album_name));
	}

	$db->RecordsPerPage = $articles_recs;
	$db->PageNumber = 1;
	$sql  = " SELECT a.*  ";
	$sql .= " FROM (" . $table_prefix . "articles a ";
	$sql .= " INNER JOIN " . $table_prefix . "articles_albums aa ON a.article_id=aa.article_id) ";
	$sql .= " WHERE aa.album_id=" . $db->tosql($album_id, TEXT);
	$sql .= " ORDER BY a.article_title ";
	$db->query($sql);
	if ($db->next_record()) {
		$article_index = 0; $col_style = "";
		if ($articles_cols > 1) {
			$col_style = "width: ".round(100 / $articles_cols, 2)."%;";
		}
		$t->set_var("col_style", $col_style);
		do {
			// get data
			$article_index++;

			$article_id = $db->f("article_id");
			$article_title = $db->f("article_title");
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

			$t->set_var("article_index", $article_index);
			$t->set_var("article_id", $article_id);
			$t->set_var("article_title", htmlspecialchars($article_title));
			if ($friendly_urls && $friendly_url) {
				$t->set_var("article_url", $friendly_url . $friendly_extension);
			} else {
				$t->set_var("article_url", "article.php?article_id=" . $article_id);
			}

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
				$t->sparse("full_description_block", false);
			} else {
				$t->set_var("full_description_block", "");
			}

			$t->parse("articles_cols", true);
			if ($article_index % $articles_cols == 0) {
				$t->parse("articles_rows", true);
				$t->set_var("articles_cols", "");
			}
		} while ($db->next_record());
		if ($article_index % $articles_cols) {
			$t->parse("articles_rows", true);
		}
	}

	$block_parsed = true;

?>