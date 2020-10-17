<?php

	$default_title = "Facebook Feed";

	$ff_recs = get_setting_value($vars, "recs", "5"); 
	$ff_limit = $ff_recs * 2; 
	$ff_nickname = get_setting_value($vars, "nickname", "");
	$ff_username = get_setting_value($vars, "username", $ff_nickname);

	// AAACRocT97gsBAF2iKWbHEPlELKv0higczOENBaz64fMNaQyCYX1mA0DQiXhvjwQW5BFqBY0WDCZCcvsZChYGfvh4cFelEZD
	$ff_access_token = get_setting_value($vars, "access_token", "");

	$html_template = get_setting_value($block, "html_template", "block_facebook_feed.html"); 
  $t->set_file("block_body", $html_template);

	$t->set_var("ff_recs", $ff_recs);
  $t->set_var("ff_limit", $ff_limit);
	$t->set_var("ff_username", $ff_username);
	$t->set_var("ff_access_token", $ff_access_token);

	// check errors
	$errors = "";
	if (!strlen($ff_username)) {
		$errors .= str_replace("{field_name}", USERNAME_FIELD, REQUIRED_MESSAGE) . "<br>";
	}
	if (!strlen($ff_access_token)) {
		$errors .= str_replace("{field_name}", ACCESS_TOKEN_MSG, REQUIRED_MESSAGE) . "<br>";
	}

	if (strlen($errors)) {
	  $t->set_var("errors_list", $errors);
	  $t->parse("facebook_errors");
	} else {
	  $t->parse("facebook_feeds");
	}


	$block_parsed = true;

?>