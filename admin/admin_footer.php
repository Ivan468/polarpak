<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_footer.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$t->set_file("block_body", "admin_footer.html");

	$t->set_var("footer_html", get_setting_value($settings, "html_below_footer", ""));

	if ($va_version_code & 4) {
		$t->global_parse("support_link", false, false, true);
	}
	
	$t->parse_to("block_body", "admin_footer", true);

	// set important init script
	set_script_tag("../js/init.js", false, "admin_footer");
