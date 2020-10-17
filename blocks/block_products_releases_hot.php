<?php

	include_once("./includes/products_functions.php");
	include_once("./includes/shopping_cart.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$default_title = "{HOT_RELEASES_MSG}";

	$html_template = get_setting_value($block, "html_template", "block_products_releases_hot.html"); 
  $t->set_file("block_body", $html_template);
	$t->set_var("releases_href", "changes_log.php");
	$t->set_var("releases_href", "releases.php");
	$t->set_var("releases", "");

	$sql  = " SELECT release_id, item_id, release_title, version, release_date, release_desc ";
	$sql .= " FROM " . $table_prefix . "releases ";
	$sql .= " WHERE is_showing=1 ";
	if (strlen($item_id)) {
		$sql .= " AND item_id=" . $db->tosql($item_id, INTEGER);
	} else {
		$sql .= " AND show_on_index=1 ";
	}
	$sql .= " ORDER BY release_date DESC ";

	$db->query($sql);
	if ($db->next_record())
	{
		do {
			$t->set_var("release_id", $db->f("release_id"));
			$t->set_var("item_id", $db->f("item_id"));
			$t->set_var("release_title", $db->f("release_title"));
			$t->set_var("version", $db->f("version"));
			$t->set_var("release_desc", $db->f("release_desc"));
			$release_date = $db->f("release_date", DATETIME);
			$t->set_var("release_date", va_date($date_show_format, $release_date));

			$t->parse("releases");
		} while ($db->next_record());              	

		$block_parsed = true;
	}

?>