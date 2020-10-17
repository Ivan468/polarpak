<?php

	$default_title = "{FIND_FRIEND_WISHLIST_MSG}";

	$html_template = get_setting_value($block, "html_template", "block_wishlist_search.html"); 
  $t->set_file("block_body", $html_template);

	$t->set_var("search_href",   "wishlist.php");

	$se = trim(get_param("se"));

	// set up search form parameters
	$t->set_var("se", htmlspecialchars($se));

	$block_parsed = true;

?>