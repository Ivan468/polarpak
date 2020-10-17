<?php



	$default_title = "Twitter Feed";



	$tf_recs = get_setting_value($vars, "recs", "5"); 

	$tf_nickname = get_setting_value($vars, "nickname", "");

	$tf_username = get_setting_value($vars, "username", $tf_nickname);

	$consumer_key = get_setting_value($vars, "consumer_key", "");

	$consumer_secret = get_setting_value($vars, "consumer_secret", "");

	$oauth_access_token = get_setting_value($vars, "oauth_access_token", "");

	$oauth_access_token_secret = get_setting_value($vars, "oauth_access_token_secret", "");

	$tf_show_icon = get_setting_value($vars, "tf_show_icon", "");

	$html_template = get_setting_value($block, "html_template", "block_twitter_feed.html"); 

  $t->set_file("block_body", $html_template);



	if (strlen($tf_username) && strlen($oauth_access_token) && strlen($oauth_access_token_secret) && strlen($consumer_key) && strlen($consumer_secret)) {

		
		$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";


		$oauth = array(
			'screen_name' => $tf_nickname,
			'count' => $tf_recs,
			'oauth_consumer_key' => $consumer_key,
			'oauth_nonce' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token' => $oauth_access_token,
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0'
		);


		$base_info = build_base_string($url, 'GET', $oauth);

		$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);

		$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));

		$oauth['oauth_signature'] = $oauth_signature;

		$header = array(build_authorization_header($oauth), 'Expect:');

		$options = array( 
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_HEADER => false,
			CURLOPT_URL => $url . "?screen_name=" . $tf_nickname . "&count=". $tf_recs,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);


		$feed = curl_init();

		curl_setopt_array($feed, $options);

		$json = curl_exec($feed);

		curl_close($feed);

		$twitter_data = json_decode($json);

		foreach ($twitter_data as $tweet){
			
			if($tf_show_icon == 1){
				$t->set_var("tw_img", $tweet->user->profile_image_url);
				$t->parse("tw_image_block", false);
			}
			else{
				$t->set_var("tw_image_block", "");
			}
			$t->set_var("twit", parse_twitter_text($tweet->text));
			$formatted_date = va_date($datetime_show_format, strtotime($tweet->created_at));
			$t->set_var("tw_date", $formatted_date);
			$t->parse("twit_block", true);
		}

		$t->parse("twitter_feeds");

	} else {

		$error_message = str_replace("{field_name}", USERNAME_FIELD, REQUIRED_MESSAGE);

		$t->set_var("errors_list", $error_message);

		$t->parse("twitter_errors");

	}


	$block_parsed = true;


	function build_base_string($baseURI, $method, $params){
		$r = array();
		ksort($params);
		foreach($params as $key=>$value){
			$r[] = "$key=" . rawurlencode($value);
		}
		return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
	}


	function build_authorization_header($oauth){
		$r = 'Authorization: OAuth ';
		$values = array();
		foreach($oauth as $key=>$value){
			$values[] = "$key=\"" . rawurlencode($value) . "\"";
		}
		$r .= implode(', ', $values);
		return $r;
	}

	/**
	 * make links clickable
	 */
	function parse_twitter_text($tw_text) {
		// link URLs
		$tw_text = " " . preg_replace( "/(([[:alnum:]]+:\/\/)|www\.)([^[:space:]]*)" . 
		"([[:alnum:]#?\/&=])/i", "<a href=\"\\1\\3\\4\" target=\"_blank\">" . 
		"\\1\\3\\4</a>", $tw_text);
		 
		// link mailtos
		$tw_text = preg_replace( "/(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)" . 
		"([[:alnum:]-]))/i", "<a href=\"mailto:\\1\">\\1</a>", $tw_text);
		 
		//link twitter users
		$tw_text = preg_replace( "/ +@([a-z0-9_]*) ?/i", " <a href=\"http://twitter.com/\\1\" target=\"_blank\">@\\1</a> ", $tw_text);
		 
		//link twitter arguments
		$tw_text = preg_replace( "/ +#([a-z0-9_]*) ?/i", " <a href=\"http://twitter.com/search?q=%23\\1\" target=\"_blank\">#\\1</a> ", $tw_text);
		 
		// truncates long urls that can cause display problems (optional)
		$tw_text = preg_replace("/>(([[:alnum:]]+:\/\/)|www\.)([^[:space:]]".
		"{30,40})([^[:space:]]*)([^[:space:]]{10,20})([[:alnum:]#?\/&=])".
		"</", ">\\3...\\5\\6<", $tw_text);

		return trim($tw_text);
	}
	
?>