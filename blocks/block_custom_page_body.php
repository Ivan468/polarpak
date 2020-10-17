<?php

	$default_title = "{page_title}";

	$html_template = get_setting_value($block, "html_template", "block_custom_page_body.html"); 
  $t->set_file("block_body", $html_template);

	if ($cms_page_code != "custom_page") {
		return;
	}

	$t->set_var("page_code", htmlspecialchars($custom_page_code));
	$t->set_var("custom_page_code", htmlspecialchars($custom_page_code));
	$t->set_block("page_title", $page_title);
	$t->parse("page_title", false);
	$t->set_block("page_body", $page_body);
	$t->parse("page_body", false);

	$block_parsed = true;

?>