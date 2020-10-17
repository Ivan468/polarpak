<?php

	$default_title = "{author_name}";

	// friendly url settings
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");
	// block settings
	$show_image_tiny = get_setting_value($vars, "image_tiny", 0);
	$show_image_small = get_setting_value($vars, "image_small", 0);
	$show_image_large = get_setting_value($vars, "image_large", 1);
	$show_image_super = get_setting_value($vars, "image_super", 0);
	$show_short_desc = get_setting_value($vars, "short_description", 0);
	$show_full_desc = get_setting_value($vars, "full_description", 1);

	$html_template = get_setting_value($block, "html_template", "block_author_details.html"); 
  $t->set_file("block_body", $html_template);

	$author_id = get_param("author_id");

	$sql  = " SELECT a.*  ";
	$sql .= " FROM (" . $table_prefix . "authors a ";
	$sql .= " LEFT JOIN " . $table_prefix . "authors_sites aus ON a.author_id=aus.author_id) ";
	$sql .= " WHERE a.author_id=" . $db->tosql($author_id, INTEGER);
	$sql .= " AND (a.sites_all=1 OR aus.site_id=" . $db->tosql($site_id, INTEGER) . ") ";
	$sql .= " ORDER BY author_name ";
	$db->query($sql);
	if ($db->next_record()) {
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
			$t->sparse("full_description_block", false);
		} else {
			$t->set_var("full_description_block", "");
		}

		$block_parsed = true;
	}


?>