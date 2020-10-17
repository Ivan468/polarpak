<?php
	$default_title = "";
	$is_ajax = get_param("is_ajax");
	
	//change coockies state
	if($is_ajax == 1){
	
		$cookies_control = get_param("cookies_control");
		
		if($cookies_control == 1){
		
			set_session("cookie_control", 1);
			
			//clear stored data
			setCookie("cookie_visit", 0, va_timestamp() + (3600 * 24 * 366));
			setCookie("cookie_lang", 0, va_timestamp() + (3600 * 24 * 366));
			setCookie("cookie_af", 0, va_timestamp() + (3600 * 24 * 366));
			setCookie("cookie_friend", 0, va_timestamp() + (3600 * 24 * 366));
			
			echo "CTRL";
			
		}
		
		else{
		
			set_session("cookie_control", 0);
			
			echo "NULL";
			
		}
		
		exit;
		
	}

	$html_template = get_setting_value($block, "html_template", "block_cookies_control.html"); 
  $t->set_file("block_body", $html_template);
	$cookie_control = get_session("cookie_control");

	if($cookie_control == 1){
		$t->set_var("dsblClass", "background-position:0 0;");
	}
	if(!$layout_type) { $layout_type = "no"; }
	$block_parsed = true;
?>