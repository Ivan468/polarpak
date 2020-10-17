<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  file_functions.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	define("FM_FILE_REGEXP", "/\.(gif|jpg|jpeg|bmp|png|tif|tiff|doc|docx|pdf|xls|xlsx|html|htm|css|swf|flv|avi|asf|wmv|vma|mpg|mpeg|zip|gz)$/i");
	define("FM_IMAGE_REGEXP", "/\.(gif|jpg|jpeg|bmp|png|tif|tiff)$/i");
	define("FM_ARCHIVE_REGEXP", "/\.(zip)$/i");
	define("FM_HTML_REGEXP", "/\.(htm|html)$/i");
	define("FM_CSS_REGEXP", "/\.(css)$/i");
	define("FM_JS_REGEXP", "/\.(js)$/i");
	define("FM_DOC_REGEXP", "/\.(doc|docx|pdf|xls|xlsx|html|htm)$/i");
	define("FM_VIDEO_REGEXP", "/\.(swf|flv|avi|asf|wmv|vma|mpg|mpeg)$/i");

	// global settings for file manager
	$conf_root_dir = "..";
	$conf_array_dirs = array("templates", "css", "styles", "image", "images","video", "videos", "previews", "pdf", "pdfs", "documents", "docs");
	$conf_array_files_ext = array("gif", "jpg", "jpeg", "bmp", "png", "doc", "pdf", "xls", "html", "htm", "css", "swf", "flv", "avi", "asf", "wmv", "vma", "mpg", "mpeg");

	$tmp_dir_arch = "va_temp";
	
	$array_text_files = array("html", "htm", "css");
	$array_image_file = array("gif", "jpg", "jpeg", "bmp", "png");
	$array_download_file = array("doc", "pdf", "xls", "swf", "flv", "avi", "asf", "wmv", "vma", "mpg", "mpeg");
	
	$array_archive_file = array("zip", "gz");

function fm_dir(&$dir, &$site_dir, &$top_dir)
{
	global $conf_array_dirs;
	$dir = get_param("dir");
	if (!$dir) { $dir = get_param("root_dir"); }
	if (!$dir) { $dir = get_param("dir_root"); }
	if (!$dir) { $dir = get_param("dir_path"); }
	if (!$dir) { $dir = get_param("d"); }
	$dir = trim($dir, " \t\n\r\0\x0B.\\\/");
	// check FM dirs and remove point and double point dirs '..'
	$dirs = array(); 
	$check_dirs = explode("/", $dir);
	foreach ($check_dirs as $dir_name) {
		if ($dir_name != "." && $dir_name != "..") {
			$dirs[] = $dir_name;
		}
	}
	$top_dir = isset($dirs[0]) ? $dirs[0] : "";
	$dir = implode("/", $dirs);
	if(!in_array($top_dir, $conf_array_dirs) || !is_dir("../".$top_dir)) { 
		$dir = ""; $top_dir = ""; 
	}
	$site_dir = $dir;
	$dir = ($dir) ? "../".$dir : "..";
}

function unique_filename($file_path_original)
{
	$file_path = $file_path_original;
	$file_index = 0;
	while (file_exists($file_path)) {
		$file_index++;
		$delimiter_pos = strrpos($file_path_original, ".");
		// check slash position
		$slash_pos = strrpos($file_path_original, "/");
		$back_slash_pos = strrpos($file_path_original, "\\");
		if($delimiter_pos && $delimiter_pos > intval($slash_pos) && $delimiter_pos > intval($back_slash_pos)) {
			$file_path = substr($file_path_original, 0, $delimiter_pos) . "_" . $file_index . substr($file_path_original, $delimiter_pos);
		} else {
			$file_path = $index . "_" . $file_path_original;
		}
	}
	return $file_path;
}


function mkdir_recursively($file_path, &$errors)
{
	$errors = "";
	// check if we need create a new dirs recursively
	if (preg_match("/^(.+[\/\\\\])[^\/\\\\]*$/", $file_path, $matches)) {
		$dir_path = $matches[1];
		if (!file_exists($dir_path)) {
	    $dir_path = preg_replace("/(\/){2,}|(\\\){1,}/", "/", $dir_path); 
	    $dir_path = rtrim($dir_path, "/");
			$dir_parts = explode("/", $dir_path);
			$dir_path = ""; // clear var to create dir one by one
			foreach ($dir_parts as $dir_part) {
				$start_path = $dir_path;
				$dir_path .= $dir_part."/";
				if (!is_dir($dir_path)) {
					$dir_created = @mkdir($dir_path, 0777);
					if (!$dir_created) {
						if (!is_writable($start_path)) {
							$errors .= str_replace("{folder_name}", $start_path, FOLDER_PERMISSION_MESSAGE) . "<br>\n";
						}	else if (!file_exists($dir_path)) {
							$errors .= FOLDER_DOESNT_EXIST_MSG." ".$dir_path."\n";
						}
						break;
					}
				}
			}
		}
	}
}

function rmdir_recursively($dir_path) 
{
	if(is_dir($dir_path)) {
		if ($dir = opendir($dir_path)) {
			$dir_path .= "/";
			while ($dir_obj = readdir($dir)) {
				if ($dir_obj != "." && $dir_obj != "..") {
					if (is_dir($dir_path.$dir_obj)) {
						rmdir_recursively($dir_path.$dir_obj);
					} else if (is_file($dir_path.$dir_obj)) {
						unlink($dir_path.$dir_obj);
					}
				}
			}
			closedir($dir);
		}
		rmdir($dir_path);
	} else if (is_file($dir_path)) {
		unlink($dir_path);
	}
}


function parse_file_mask(&$file_path_mask) 
{
	global $t, $date_formats, $user_id, $order_id;
	if (!isset($t)) { $t = new VA_Template(""); }
	// set random value
	$rnd = mt_rand();
	$t->set_var("rnd", $rnd);
	$t->set_var("rand", $rnd);
	$t->set_var("random", $rnd);
	// check order_id and user_id vars
	if (isset($order_id) && !$t->var_exists("order_id")) {
		$t->set_var("order_id", $order_id);
	}
	if (isset($user_id) && !$t->var_exists("user_id")) {
		$t->set_var("user_id", $user_id);
	}

	if (preg_match_all("/\{(\w+)\}/is", $file_path_mask, $matches)) {
		for ($p = 0; $p < sizeof($matches[1]); $p++) {
			$tag = $matches[1][$p];
			if (in_array($tag, $date_formats)) {
				$file_path_mask = str_replace("{".$tag."}", va_date($tag), $file_path_mask);
			} else {
				$file_path_mask = str_replace("{".$tag."}", $t->get_var($tag), $file_path_mask);
			} 
		}
	}
	return $file_path_mask;
}


function dir_slash(&$dir_name, &$slash)
{
	if ($dir_name) {
		if ($slash) {
			if (!preg_match("/".preg_quote($slash, "/")."$/", $dir_name)) {
				$dir_name .= $slash;
			}
		} else if (preg_match("/\//", $dir_name)) {
			$slash = "/";
			if (!preg_match("/\/$/", $dir_name)) { $dir_name .= "/"; }
		} else if (preg_match("/\\\\/", $dir_name)) {
			$slash = "\\";
			if (!preg_match("/\\\\$/", $dir_name)) { $dir_name .= "\\"; }
		} else if ($dir_name) {
			$slash = "/";
			$dir_name .= "/"; 
		}
	}
}

function check_files_mask($root_dir, $sub_dir, $regex, &$files_matched, &$files_mismatched) 
{
	if (!is_array($files_matched)) { $files_matched = array(); }
	if (!is_array($files_mismatched)) { $files_mismatched = array(); }

	dir_slash($root_dir, $slash); // always append slash to the end of dir
	dir_slash($sub_dir, $slash); 
	$dir_path = $root_dir.$sub_dir;
	// read dir content
	if ($dir = @opendir($dir_path)) {
		while ($dir_obj = @readdir($dir)) {
			if ($dir_obj != "." && $dir_obj != "..") {
				if (@is_dir($dir_path.$dir_obj)) {
					// check sub dir files
					check_files_mask($root_dir, $sub_dir.$dir_obj, $regex, $files_matched, $files_mismatched);
				} else if (@is_file($dir_path.$dir_obj)) {
					if (preg_match($regex, $dir_obj)) {
						$files_matched[] = $sub_dir.$dir_obj;
					} else {
						$files_mismatched[] = $sub_dir.$dir_obj;
					}
				}
			}
		}
		@closedir($dir);
	}

	return (count($files_mismatched) == 0);
}


