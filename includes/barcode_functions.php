<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  barcode_functions.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function barcode_image ($text, $image_type = "png", $codetype = "code128") 
{
	gd_errors_checks($image_type);

	$errors = "";

	$img = ImageCreate(100, 30);
  $black = ImageColorAllocate($img, 0, 0, 0);
  $white = ImageColorAllocate($img, 255, 255, 255);
	imagefill($img, 0, 0, $white);				

	if ($codetype == "code128") {
		if ((preg_match( "/[^0-9A-Z\-\*\+\$\%\/\.\s\"\!\#\&'\(\)\,\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}\~\\\\]/i", $text)) || (strlen($text) < 1)) {
			$errors	.= "Invalid code128";
		}	else {
			$img = barcode_code128($text);
		}
	} elseif ($codetype == "ean13") {
		if ((preg_match("/[^0-9]/",$text)) || (strlen($text) != 12)) {
			$errors	.= "Invalid ean13";
		}	else {
			$img = barcode_ean13($text);
		}
	} elseif ($codetype == "code39") {
		if (preg_match( "/[^0-9A-Z\-*+\$%\/. ]/", $text )) {
			$errors	.= "Invalid code39";
		}	else {
			$img = barcode_code39($text);
		}
	} elseif ($codetype == "int25") {
		if ((preg_match("/[^0-9]/",$text)) || (strlen($text)!=12)) {
			$errors	.= "Invalid int25";
		}	else {
			$img = barcode_int25($text);
		}
	} elseif ($codetype == "upca") {
		if ((preg_match("/[^0-9]/",$text)) || (strlen($text)!=12)) {
			$errors	.= "Invalid upca";
		}	else {
			$img = barcode_upca($text);
		}
	} elseif ($codetype == "postnet") {
		if (preg_match("/[^0-9]/",$text)) {
			$errors	.= "Invalid postnet";
		}	else {
			$img = barcode_postnet($text);
		}
	} else {
		$errors	.= INVALID_CODE_TYPE_MSG;
 	}

	if ($errors) {
		barcode_message ($errors, $img);
	} 

	return $img;
}

function draw_barcode ($text, $image_type = "png", $codetype = "code128")
{
	$img = barcode_image ($text, $image_type, $codetype);
	if ($image_type == "png") {
		header("Content-type: image/png");
		imagepng($img);    	
	} else if ($image_type == "gif") {
		header("Content-type: image/gif");
		imagegif($img);
	} else if ($image_type == "jpg") {
		header("Content-type: image/jpeg");
		imagejpeg($img);      	
	}
	imagedestroy($img);
}

function save_barcode ($filename, $text, $image_type = "png", $codetype = "code128")
{
	$img = barcode_image ($text, $image_type, $codetype);
	if ($image_type == "png") {
		imagepng($img, $filename);    	
	} else if ($image_type == "gif") {
		imagegif($img, $filename);
	} else if ($image_type == "jpg") {
		imagejpeg($img, $filename);      	
	}
	imagedestroy($img);
}

function barcode_message($text, &$img) 
{
	$black = imagecolorallocate($img, 0, 0, 0);
	$white = ImageColorAllocate($img, 255, 255, 255);
	$image_width = imagesx($img);
	$image_height = imagesy($img);
	$font = 2;

	$xcenter = $image_width/2 - strlen($text) * (imagefontwidth($font))/2;
	$ycenter = 0;
	
	imagefilledrectangle($img, $xcenter, $ycenter, $xcenter+strlen($text) * (imagefontwidth($font)), $ycenter+imagefontheight($font), $white);			
	imagestring($img,	$font, $xcenter,	$ycenter,	$text, $black	);
}

function barcode_license_message(&$img)
{
	$license_valid = false;
	if (function_exists("va_license")) {
		$va_license = va_license();
		$hosts = $va_license["hosts"];
		$host_name = getenv("HTTP_HOST");
		if (!strlen($host_name) && isset($_SERVER["HTTP_HOST"])) {
			$host_name = $_SERVER["HTTP_HOST"];
		}
		$host_name = preg_replace("/^www\./i", "", $host_name);
		$host_name = preg_replace("/:\d+$/i", "", $host_name);
		if (in_array($host_name, $hosts)) {
			$license_valid = true;
		} 
	}
	if (!$license_valid) {
		barcode_message ("www.viart.com", $img);
	}
}


function gd_errors_checks($image_type = "png")
{	
	$error = "";
	if (!function_exists("gd_info"))	{
		$error = GD_LIBRARY_ERROR_MSG;
	} else if ($image_type == "png") {
		if (!(imagetypes() & IMG_PNG)) {
			$error	= "PNG image format is not supported by GD.";
		}
	} else if ($image_type == "gif") {
		if (!(imagetypes() & IMG_GIF)) {
			$error	= "GIF image format is not supported by GD.";
		}
	} else if ($image_type == "jpg") {
		if (!(imagetypes() & IMG_JPG)) {
			$error	= "JPEG image format is not supported by GD.";
		}
	} else {
		$error	= "Invalid image format. Format must be png, jpg or png";	
	}
	if ($error) {
		die($error);
	}
}

function barcode_code128($text, $barcodeheight = 30)
{
	$barwidth = 1;
	$font = 2;  

	$code = array (
  "0" => "212222",  // " "
  "1" => "222122",  // "!"
  "2" => "222221",  // "{QUOTE}"
  "3" => "121223",  // "#"
  "4" => "121322",  // "$"
  "5" => "131222",  // "%"
  "6" => "122213",  // "&"
  "7" => "122312",  // "'"
  "8" => "132212",  // "("
  "9" => "221213",  // ")"
  "10" => "221312", // "*"
  "11" => "231212", // "+"
  "12" => "112232", // ","
  "13" => "122132", // "-"
  "14" => "122231", // "."
  "15" => "113222", // "/"
  "16" => "123122", // "0"
  "17" => "123221", // "1"
  "18" => "223211", // "2"
  "19" => "221132", // "3"
  "20" => "221231", // "4"
  "21" => "213212", // "5"
  "22" => "223112", // "6"
  "23" => "312131", // "7"
  "24" => "311222", // "8"
  "25" => "321122", // "9"
  "26" => "321221", // ":"
  "27" => "312212", // ";"
  "28" => "322112", // "<"
  "29" => "322211", // "="
  "30" => "212123", // ">"
  "31" => "212321", // "?"
  "32" => "232121", // "@"
  "33" => "111323", // "A"
  "34" => "131123", // "B"
  "35" => "131321", // "C"
  "36" => "112313", // "D"
  "37" => "132113", // "E"
  "38" => "132311", // "F"
  "39" => "211313", // "G"
  "40" => "231113", // "H"
  "41" => "231311", // "I"
  "42" => "112133", // "J"
  "43" => "112331", // "K"
  "44" => "132131", // "L"
  "45" => "113123", // "M"
  "46" => "113321", // "N"
  "47" => "133121", // "O"
  "48" => "313121", // "P"
  "49" => "211331", // "Q"
  "50" => "231131", // "R"
  "51" => "213113", // "S"
  "52" => "213311", // "T"
  "53" => "213131", // "U"
  "54" => "311123", // "V"
  "55" => "311321", // "W"
  "56" => "331121", // "X"
  "57" => "312113", // "Y"
  "58" => "312311", // "Z"
  "59" => "332111", // "["
  "60" => "314111", // "\"
  "61" => "221411", // "]"
  "62" => "431111", // "^"
  "63" => "111224", // "_"
  "64" => "111422", // "`"
  "65" => "121124", // "a"
  "66" => "121421", // "b"
  "67" => "141122", // "c"
  "68" => "141221", // "d"
  "69" => "112214", // "e"
  "70" => "112412", // "f"
  "71" => "122114", // "g"
  "72" => "122411", // "h"
  "73" => "142112", // "i"
  "74" => "142211", // "j"
  "75" => "241211", // "k"
  "76" => "221114", // "l"
  "77" => "413111", // "m"
  "78" => "241112", // "n"
  "79" => "134111", // "o"
  "80" => "111242", // "p"
  "81" => "121142", // "q"
  "82" => "121241", // "r"
  "83" => "114212", // "s"
  "84" => "124112", // "t"
  "85" => "124211", // "u"
  "86" => "411212", // "v"
  "87" => "421112", // "w"
  "88" => "421211", // "x"
  "89" => "212141", // "y"
  "90" => "214121", // "z"
  "91" => "412121", // "{"
  "92" => "111143", // "|"
  "93" => "111341", // "}"
  "94" => "131141", // "~"
  "95" => "114113", // 95
  "96" => "114311", // 96
  "97" => "411113", // 97
  "98" => "411311", // 98
  "99" => "113141", // 99
  "100" => "114131", // 100
  "101" => "311141", // 101
  "102" => "411131" // 102
	);              
	                
    // We start with the Code128 Start Code character.  We
    // initialize checksum to 104, rather than calculate it.
    // We then add the startcode to $allbars, the main string
    // containing the bar sizes for the entire code.
    $startcode= '211214';
    $stopcode = '2331112';
    $checksum = 104;

    $allbars = $startcode;
                  
    // Next, we read the $text string that was passed to the
    // method and for each character, we determine the bar
    // pattern and add it to the end of the $allbars string.
    // In addition, we continually add the character's value
    // to the checksum
    $bars = '';   
    for ($i=0; $i < strlen($text); ++$i) {
        $char = $text[$i];
        $val = ord($char) - 32;
        $checksum += ($val * ($i + 1));
        $bars = $code[ord($char) - 32];
        $allbars = $allbars . $bars;
    }           

    // Then, Take the Mod 103 of the total to get the index
    // of the Code128 Check Character.  We get its bar
    // pattern and add it to $allbars in the next section.
    $checkdigit = $checksum % 103;
    $bars = $code[$checkdigit];

    // Finally, we get the Stop Code pattern and put the
    // remaining pieces together.  We are left with the
    // string $allbars containing all of the bar widths
    // and can now think about writing it to the image.

    $allbars = $allbars . $bars . $stopcode;

    //------------------------------------------------------//
    // Next, we will calculate the width of the resulting
    // bar code and size the image accordingly.

    // 10 Pixel "Quiet Zone" in front, and 10 Pixel
    // "Quiet Zone" at the end.
    $barcodewidth = 20;

    // We will read each of the characters (1,2,3,or 4) in
    // the $allbars string and add its width to the running
    // total $barcodewidth.  The height of the barcode is
    // calculated by taking the bar height plus the font height.

    for ($i=0; $i < strlen($allbars); ++$i) {
        $nval = $allbars[$i];
        $barcodewidth += ($nval * $barwidth);
    }
    $barcodelongheight = (int) (imagefontheight($font) / 2) + $barcodeheight;

    $img = ImageCreate($barcodewidth, $barcodelongheight+ imagefontheight($font)+1);
    $black = ImageColorAllocate($img, 0, 0, 0);
    $white = ImageColorAllocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $white);

    //------------------------------------------------------//
    // Finally, we write our text line centered across the
    // bottom and the bar patterns and display the image.


    // First, print the image, centered across the bottom.
    imagestring(
			$img, 
			$font, 
			$barcodewidth / 2 - strlen($text) / 2 * (imagefontwidth($font)), 
			$barcodeheight + imagefontheight($font) / 2, 
			$text, 
			$black
		);

    // We set $xpos to 10 so we start bar printing after 
    // position 10 to simulate the 10 pixel "Quiet Zone"
    $xpos = 10;

    // We will now process each of the characters in the $allbars
    // array.  The number in each position is read and then alternating
    // black bars and spaces are drawn with the corresponding width.
    $bar = 1;
    for ($i=0; $i < strlen($allbars); ++$i) {
      $nval = $allbars[$i];
      $width = $nval * $barwidth;
      if ($bar==1) {
				imagefilledrectangle($img, $xpos, 0, $xpos + $width-1, $barcodelongheight, $black);
				$xpos += $width;
				$bar = 0;
      } else {
				$xpos += $width;
				$bar = 1;
      }
    }

	// add license message 
	barcode_license_message($img);

	return $img;
} // function draw128()

function barcode_ean13 ($text, $barcodeheight = 30)
{

	if (empty($text)) die ("Error: empty EAN string");
	if (strlen($text)!=12) die ("Error: EAN string must be 12 symbols");	

	//control sum calculation: 
	//n=SUM(even_digits)*3 + SUM(odd_digits) 
	//control_sum = 10*ceil(n/10) - n;
	
	$even_chars = 0; $odd_chars = 0;
	for ($i=0; $i<12; $i++) {
		$onechar = substr($text,$i,1);
		if ($i % 2 ==0) {
			$odd_chars += (int)$onechar;
		} else {
			$even_chars += (int)$onechar;
		}
	}	
	$summ = $odd_chars + ($even_chars * 3);
	$control_symbol = 10*ceil($summ/10) - $summ;
	$text	.= $control_symbol; // 13th digit; 


	$font = 2;  // gd internal small font
	$barwidth = 1;
	$number_set = array(
           '0' => array(
                    'A' => array(0,0,0,1,1,0,1),
                    'B' => array(0,1,0,0,1,1,1),
                    'C' => array(1,1,1,0,0,1,0)
                        ),
           '1' => array(
                    'A' => array(0,0,1,1,0,0,1),
                    'B' => array(0,1,1,0,0,1,1),
                    'C' => array(1,1,0,0,1,1,0)
                        ),
           '2' => array(
                    'A' => array(0,0,1,0,0,1,1),
                    'B' => array(0,0,1,1,0,1,1),
                    'C' => array(1,1,0,1,1,0,0)
                        ),
           '3' => array(
                    'A' => array(0,1,1,1,1,0,1),
                    'B' => array(0,1,0,0,0,0,1),
                    'C' => array(1,0,0,0,0,1,0)
                        ),
           '4' => array(
                    'A' => array(0,1,0,0,0,1,1),
                    'B' => array(0,0,1,1,1,0,1),
                    'C' => array(1,0,1,1,1,0,0)
                        ),
           '5' => array(
                    'A' => array(0,1,1,0,0,0,1),
                    'B' => array(0,1,1,1,0,0,1),
                    'C' => array(1,0,0,1,1,1,0)
                        ),
           '6' => array(
                    'A' => array(0,1,0,1,1,1,1),
                    'B' => array(0,0,0,0,1,0,1),
                    'C' => array(1,0,1,0,0,0,0)
                        ),
           '7' => array(
                    'A' => array(0,1,1,1,0,1,1),
                    'B' => array(0,0,1,0,0,0,1),
                    'C' => array(1,0,0,0,1,0,0)
                        ),
           '8' => array(
                    'A' => array(0,1,1,0,1,1,1),
                    'B' => array(0,0,0,1,0,0,1),
                    'C' => array(1,0,0,1,0,0,0)
                        ),
           '9' => array(
                    'A' => array(0,0,0,1,0,1,1),
                    'B' => array(0,0,1,0,1,1,1),
                    'C' => array(1,1,1,0,1,0,0)
                        )
        );

	$number_set_left_coding = array(
           '0' => array('A','A','A','A','A','A'),
           '1' => array('A','A','B','A','B','B'),
           '2' => array('A','A','B','B','A','B'),
           '3' => array('A','A','B','B','B','A'),
           '4' => array('A','B','A','A','B','B'),
           '5' => array('A','B','B','A','A','B'),
           '6' => array('A','B','B','B','A','A'),
           '7' => array('A','B','A','B','A','B'),
           '8' => array('A','B','A','B','B','A'),
           '9' => array('A','B','B','A','B','A')
  );

        // Calculate the barcode width
				// 8 == 3 left + 5 center + 3 right
  $barcodewidth = (strlen($text)) * (7 * $barwidth) + 8 + imagefontwidth($font)+1 ;

  $barcodelongheight = (int) (imagefontheight($font)/2) + $barcodeheight;

  $img = ImageCreate( $barcodewidth, $barcodelongheight + imagefontheight($font) + 1 );

  // Alocate the black and white colors
  $black = ImageColorAllocate($img, 0, 0, 0);
  $white = ImageColorAllocate($img, 255, 255, 255);

  // Fill image with white color
  imagefill($img, 0, 0, $white);

  // get the first digit which is the key for creating the first 6 bars
  $key = substr($text,0,1);

  // Initiate x position
  $xpos = 0;

  // print first digit
  imagestring($img, $font, $xpos, $barcodeheight, $key, $black);
  $xpos= imagefontwidth($font) + 1;

  // Draws the left guard pattern (bar-space-bar)
  // bar
  imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
  $xpos += $barwidth;
  // space
  $xpos += $barwidth;
  // bar
  imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
  $xpos += $barwidth;

  // Draw left $text contents
  $set_array = $number_set_left_coding[$key];
  for ($idx = 1; $idx < 7; $idx ++) {
      $value = substr($text,$idx,1);
      imagestring ($img, $font, $xpos+1, $barcodeheight, $value, $black);
      foreach ($number_set[$value][$set_array[$idx-1]] as $bar) {
          if ($bar) {
              imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodeheight, $black);
          }
          $xpos += $barwidth;
      }
  }

  // Draws the center pattern (space-bar-space-bar-space)
  // space
  $xpos += $barwidth;
  // bar
  imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
  $xpos += $barwidth;
  // space
  $xpos += $barwidth;
  // bar
  imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
  $xpos += $barwidth;
  // space
  $xpos += $barwidth;


  // Draw right $text contents
  for ($idx = 7; $idx < 13; $idx ++) {
      $value=substr($text,$idx,1);
      imagestring ($img, $font, $xpos+1, $barcodeheight, $value, $black);
      foreach ($number_set[$value]['C'] as $bar) {
          if ($bar) {
              imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodeheight, $black);
          }
          $xpos += $barwidth;
      }
  }

  // Draws the right guard pattern (bar-space-bar)
  // bar
  imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
  $xpos += $barwidth;
  // space
  $xpos += $barwidth;
  // bar
  imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
  $xpos += $barwidth;

	// add license message 
	barcode_license_message($img);

	return $img;
} // draw ean13

function barcode_code39 ($text, $barcodeheight = 30)
{
	if (preg_match( "/[^0-9A-Z\-*+\$%\/. ]/", $text )) {
		die("Invalid text for Code39");
	}

	$barthinwidth = 1;
	$barthickwidth = 3;
	$coding_map = array(
        '0' => '000110100',
        '1' => '100100001',
        '2' => '001100001',
        '3' => '101100000',
        '4' => '000110001',
        '5' => '100110000',
        '6' => '001110000',
        '7' => '000100101',
        '8' => '100100100',
        '9' => '001100100',
        'A' => '100001001',
        'B' => '001001001',
        'C' => '101001000',
        'D' => '000011001',
        'E' => '100011000',
        'F' => '001011000',
        'G' => '000001101',
        'H' => '100001100',
        'I' => '001001100',
        'J' => '000011100',
        'K' => '100000011',
        'L' => '001000011',
        'M' => '101000010',
        'N' => '000010011',
        'O' => '100010010',
        'P' => '001010010',
        'Q' => '000000111',
        'R' => '100000110',
        'S' => '001000110',
        'T' => '000010110',
        'U' => '110000001',
        'V' => '011000001',
        'W' => '111000000',
        'X' => '010010001',
        'Y' => '110010000',
        'Z' => '011010000',
        '-' => '010000101',
        '*' => '010010100',
        '+' => '010001010',
        '$' => '010101000',
        '%' => '000101010',
        '/' => '010100010',
        '.' => '110000100',
        ' ' => '011000100'
    );

  $final_text = '*' . $text . '*';

  $barcode = "";
	$split_text = preg_split("//", $final_text, -1, PREG_SPLIT_NO_EMPTY);	
	foreach ($split_text as $character) {
		$color = 1; // 1: Black, 0: White
		// if $bit is 1, line is wide; if $bit is 0 line is thin
		$chars = preg_split("//", $coding_map[$character] . '0', -1, PREG_SPLIT_NO_EMPTY);	
		foreach ($chars as $bit) {
			$barcode .= (($bit == 1) ? str_repeat("$color", $barthickwidth) : str_repeat("$color", $barthinwidth));
			$color = (($color == 0) ? 1 : 0);
		}
  }

  $barcode_len = strlen($barcode);

  // Create GD image object
  $img = imagecreate($barcode_len, $barcodeheight);

  // Allocate black and white colors to the image
  $black = imagecolorallocate($img, 0, 0, 0);
  $white = imagecolorallocate($img, 255, 255, 255);
//  $font_height = ( $noText ? 0 : imagefontheight( "gdFontSmall" ) );
	$font_height = (imagefontheight("gdFontSmall"));

  $font_width = imagefontwidth("gdFontSmall");
  imagefill($img, 0, 0, $white);

  // Initialize X position
  $xpos = 0;

  // draw barcode bars to image
/*
	if ($noText) {
		foreach (str_split($barcode) as $character_code ) {
			if ($character_code == 0 ) {
				imageline($img, $xpos, 0, $xpos, $barcodeheight, $white);
			} else {
				imageline($img, $xpos, 0, $xpos, $barcodeheight, $black);
			}
			$xpos++;
		}
	} else {
		// drawing text
	}
*/
	$split_text = preg_split("//", $barcode, -1, PREG_SPLIT_NO_EMPTY);	
	foreach ($split_text as $character_code ) {
		if ($character_code == 0) {
			imageline($img, $xpos, 0, $xpos, $barcodeheight - $font_height - 1, $white);
		} else {
			imageline($img, $xpos, 0, $xpos, $barcodeheight - $font_height - 1, $black);
		}
		$xpos++;
	}
// draw text under barcode
	imagestring($img,
		"gdFontSmall",
		($barcode_len - $font_width * strlen($text))/2,
		$barcodeheight - $font_height,
		$text,
		$black
	);

	// add license message 
	barcode_license_message($img);
 
	return $img;

} // draw code 39


function barcode_int25($text, $barcodeheight = 30)
{
	$barthinwidth = 1;
	$barthickwidth = 3;
	$coding_map = array(
		'0' => '00110',    
		'1' => '10001',    
		'2' => '01001',    
		'3' => '11000',    
		'4' => '00101',    
		'5' => '10100',    
		'6' => '01100',    
		'7' => '00011',    
		'8' => '10010',    
		'9' => '01010'     
	);

	$text = trim($text);

	if (!preg_match("/[0-9]/",$text)) 
		die("Invalid text for Int25");

// if odd $text lenght adds a '0' at string beginning
	$text = strlen($text) % 2 ? '0' . $text : $text;

// Calculate the barcode width
	$barcodewidth = (strlen($text)) * (3 * $barthinwidth + 2 * $barthickwidth) +
    (strlen($text)) * 2.5 +
    (7 * $barthinwidth + $barthickwidth) + 3;

// Create the image
	$img = ImageCreate($barcodewidth, $barcodeheight);

// Alocate the black and white colors, fill image with white color
	$black = ImageColorAllocate($img, 0, 0, 0);
	$white = ImageColorAllocate($img, 255, 255, 255);
	imagefill($img, 0, 0, $white);

// Initiate x position
	$xpos = 0;

// Draws the leader
	for ($i=0; $i < 2; $i++) {
		$elementwidth = $barthinwidth;
		imagefilledrectangle($img, $xpos, 0, $xpos + $elementwidth - 1, $barcodeheight, $black);
		$xpos += $elementwidth;
		$xpos += $barthinwidth;
		$xpos ++;
	}

// Draw $text contents
	for ($idx = 0; $idx < strlen($text); $idx += 2) {       // Draw 2 chars at a time
		$oddchar  = substr($text, $idx, 1);                 // get odd char
		$evenchar = substr($text, $idx + 1, 1);             // get even char

    // interleave
		for ($baridx = 0; $baridx < 5; $baridx++) {
        // Draws odd char corresponding bar (black)
			$elementwidth = (substr($coding_map[$oddchar], $baridx, 1)) ?  $barthickwidth : $barthinwidth;
			imagefilledrectangle($img, $xpos, 0, $xpos + $elementwidth - 1, $barcodeheight, $black);
			$xpos += $elementwidth;
        // Left enought space to draw even char (white)
			$elementwidth = (substr($coding_map[$evenchar], $baridx, 1)) ?  $barthickwidth : $barthinwidth;
			$xpos += $elementwidth; 
			$xpos ++;
		}
	}


// Draws the trailer
	$elementwidth = $barthickwidth;
	imagefilledrectangle($img, $xpos, 0, $xpos + $elementwidth - 1, $barcodeheight, $black);
	$xpos += $elementwidth;
	$xpos += $barthinwidth;
	$xpos ++;
	$elementwidth = $barthinwidth;
	imagefilledrectangle($img, $xpos, 0, $xpos + $elementwidth - 1, $barcodeheight, $black);

	// add license message 
	barcode_license_message($img);

	return $img;

} // draw int25

function barcode_upcaX ($text, $barcodeheight = 30)
{	
	$font = 2;  // gd internal small font
	$barwidth = 1;
	$number_set = array(
		'0' => array(
         'A' => array(0,0,0,1,1,0,1),
         'B' => array(0,1,0,0,1,1,1),
         'C' => array(1,1,1,0,0,1,0)),
		'1' => array(
         'A' => array(0,0,1,1,0,0,1),
         'B' => array(0,1,1,0,0,1,1),
         'C' => array(1,1,0,0,1,1,0)),
		'2' => array(
         'A' => array(0,0,1,0,0,1,1),
         'B' => array(0,0,1,1,0,1,1),
         'C' => array(1,1,0,1,1,0,0)),
		'3' => array(
         'A' => array(0,1,1,1,1,0,1),
         'B' => array(0,1,0,0,0,0,1),
         'C' => array(1,0,0,0,0,1,0)),
		'4' => array(
         'A' => array(0,1,0,0,0,1,1),
         'B' => array(0,0,1,1,1,0,1),
         'C' => array(1,0,1,1,1,0,0)),
		'5' => array(
         'A' => array(0,1,1,0,0,0,1),
         'B' => array(0,1,1,1,0,0,1),
         'C' => array(1,0,0,1,1,1,0)),
		'6' => array(
         'A' => array(0,1,0,1,1,1,1),
         'B' => array(0,0,0,0,1,0,1),
         'C' => array(1,0,1,0,0,0,0)),
		'7' => array(
         'A' => array(0,1,1,1,0,1,1),
         'B' => array(0,0,1,0,0,0,1),
         'C' => array(1,0,0,0,1,0,0)),
		'8' => array(
         'A' => array(0,1,1,0,1,1,1),
         'B' => array(0,0,0,1,0,0,1),
         'C' => array(1,0,0,1,0,0,0)),
		'9' => array(
         'A' => array(0,0,0,1,0,1,1),
         'B' => array(0,0,1,0,1,1,1),
         'C' => array(1,1,1,0,1,0,0))
	);
	$number_set_left_coding = array(
		'0' => array('A','A','A','A','A','A'),
		'1' => array('A','A','B','A','B','B'),
		'2' => array('A','A','B','B','A','B'),
		'3' => array('A','A','B','B','B','A'),
		'4' => array('A','B','A','A','B','B'),
		'5' => array('A','B','B','A','A','B'),
		'6' => array('A','B','B','B','A','A'),
		'7' => array('A','B','A','B','A','B'),
		'8' => array('A','B','A','B','B','A'),
		'9' => array('A','B','B','A','B','A')
	);

//	if (!preg_match("/[0-9]/",$text)) 	die ("Invalid text for UPC-A");
//	if (strlen($text)!=12) 							die ("Error: UPC-A string must be 12 symbols");	
	$error = false;
	if ((!preg_match("/[0-9]/",$text)) || (strlen($text)!=12)) {
		$barcodewidth= (12 * 7 * $barwidth) + 3 + 5 + 3 + 2 * (imagefontwidth($font)+1);
		$error = true;
	} else {
    // Calculate the barcode width
		$barcodewidth = (strlen($text)) * (7 * $barwidth)
			+ 3 // left
			+ 5 // center
			+ 3 // right
			+ imagefontwidth($font)+1
			+ imagefontwidth($font)+1;   // check digit's padding
	}

	$barcodelongheight = (int) (imagefontheight($font)/2)+$barcodeheight;

// Create the image
	$img = ImageCreate($barcodewidth, $barcodelongheight+ imagefontheight($font)+1);
// Alocate the black and white colors. Fill image with white color
	$black = ImageColorAllocate($img, 0, 0, 0);
	$white = ImageColorAllocate($img, 255, 255, 255);
	imagefill($img, 0, 0, $white);

	if ($error) {
    $imgerror = ImageCreate($barcodewidth, $barcodelongheight+imagefontheight($font)+1);
    $red      = ImageColorAllocate($imgerror, 255, 0, 0);
    $black    = ImageColorAllocate($imgerror, 0, 0, 0);
    imagefill($imgerror, 0, 0, $red);

    imagestring(
        $imgerror,
        $font,
        $barcodewidth / 2 - (10/2 * imagefontwidth($font)),
        $barcodeheight / 2,
        'Code Error',
        $black
    );
	}
// get the first digit which is the key for creating the first 6 bars
	$key = substr($text,0,1);

// Initiate x position
	$xpos = 0;

// print first digit
	imagestring($img, $font, $xpos, $barcodeheight, $key, $black);
	$xpos= imagefontwidth($font) + 1;



// Draws the left guard pattern (bar-space-bar)
	imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
	$xpos += $barwidth;
	$xpos += $barwidth;
	imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
	$xpos += $barwidth;

	$set_array = $number_set_left_coding[$key];



	foreach ($number_set['0'][$set_array[0]] as $bar) {
		if ($bar) {
  		imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
		}
		$xpos += $barwidth;
	}



// Draw left $text contents
	for ($idx = 1; $idx < 6; $idx ++) {
		$value=substr($text,$idx,1);
		imagestring ($img, $font, $xpos+1, $barcodeheight, $value, $black);

//foreach ($number_set[$value][$set_array[$idx-1]] as $bar) {

		foreach ($number_set[$value][$set_array[$idx]] as $bar) {
    	if ($bar) {
        imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodeheight, $black);
    	}
    	$xpos += $barwidth;
		}
	}
// Draws the center pattern (space-bar-space-bar-space)
// space
	$xpos += $barwidth;
// bar
	imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
	$xpos += $barwidth;
// space
	$xpos += $barwidth;
// bar
	imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
	$xpos += $barwidth;
// space
	$xpos += $barwidth;


// Draw right $text contents
	for ($idx = 6; $idx < 11; $idx ++) {
	  $value=substr($text,$idx,1);
    imagestring ($img, $font, $xpos+1, $barcodeheight, $value, $black);
    foreach ($number_set[$value]['C'] as $bar) {
			if ($bar) {
    		imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodeheight, $black);
			}
			$xpos += $barwidth;
    }
	}

	$value = substr($text,11,1);
	foreach ($number_set[$value]['C'] as $bar) {
		if ($bar) {
			imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
    }
    $xpos += $barwidth;
	}

// Draws the right guard pattern (bar-space-bar)
// bar
	imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
	$xpos += $barwidth;
// space
	$xpos += $barwidth;
// bar
	imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $barcodelongheight, $black);
	$xpos += $barwidth;


// Print Check Digit
	imagestring($img, $font, $xpos+1, $barcodeheight, $value, $black);

	if ($error) {
    return $imgerror;
	} else {

		// add license message 
		barcode_license_message($img);

    return $img;
	}

} // draw upc-a X


function barcode_upca($text)
{
  define("BCD_DEFAULT_TEXT_OFFSET"     ,   2);
  define("BCD_DEFAULT_TEXT_FILL_OFFSET", -10);
  define("BCS_ALIGN_CENTER"            ,   4);
  define("BCS_IMAGE_PNG"               ,  64);
  define("BCS_DRAW_TEXT"               , 128);
  define("BCS_STRETCH_TEXT"            , 256);
  define("BCD_DEFAULT_STYLE"           , BCS_ALIGN_CENTER | BCS_IMAGE_PNG | BCS_DRAW_TEXT | BCS_STRETCH_TEXT);

    $mChars = "0123456789";
    $mCharSetL = array (
       /* 0 */ "0001101",
       /* 1 */ "0011001",
       /* 2 */ "0010011",
       /* 3 */ "0111101",
       /* 4 */ "0100011",
       /* 5 */ "0110001",
       /* 6 */ "0101111",
       /* 7 */ "0111011",
       /* 8 */ "0110111",
       /* 9 */ "0001011" );
    $mCharSetR = array (
       /* 0 */ "1110010",
       /* 1 */ "1100110",
       /* 2 */ "1101100",
       /* 3 */ "1000010",
       /* 4 */ "1011100",
       /* 5 */ "1001110",
       /* 6 */ "1010000",
       /* 7 */ "1000100",
       /* 8 */ "1001000",
       /* 9 */ "1110100");

	$mValue = $text;
	$xres = 1;
	$mWidth = 120; 
	$mHeight = 80;
  $mFont = 2;
	$mStyle = BCD_DEFAULT_STYLE;
  $img = ImageCreate($mWidth, $mHeight);
	$black = ImageColorAllocate($img, 0, 0, 0);
	$white = ImageColorAllocate($img, 255, 255, 255);
	imagefill($img, 0, 0, $white);

  	$len = strlen($mValue);
	  // Start, Stop is 101
	  // Middle Bar  is 01010
	  $StartSize = $xres * 3;
	  $StopSize  = $xres * 3;
	  $MidSize   = $xres * 5;
	  $CharSize  = $xres * 7; // Same for all chars
	  $size = $CharSize * $len + $StartSize + $MidSize + $StopSize;

    $cPos = 0;
    $sPos = (integer)(($mWidth - $size ) / 2);
    $ySize = $mHeight - imagefontheight($mFont);
//	DrawStart
		$DrawPos = $sPos;   
	  ImageLine($img, $DrawPos, 0, $DrawPos, $ySize,  $black);
    $DrawPos += $xres;
    $DrawPos += $xres;
		ImageLine($img, $DrawPos, 0, $DrawPos, $ySize, $black);
    $DrawPos += $xres;

    for($i = 0; $i < 6; $i++) {
		  $cchar = $mValue[$i];
      $c = strpos($mChars, $cchar);
      $cset  = $mCharSetL[$c];
      for($j = 0; $j < strlen($cset); $j++)  {
        if(intval(substr($cset, $j, 1)) == 1)
	  	  	ImageLine($img, $DrawPos, 0, $DrawPos, $ySize, $black);
        $DrawPos += $xres;
      }
    }
//  DrawMiddle code is '01010'
    $DrawPos += $xres;
	  ImageLine($img, $DrawPos, 0, $DrawPos, $ySize, $black);
    $DrawPos += $xres;
    $DrawPos += $xres;
	  ImageLine($img, $DrawPos, 0, $DrawPos, $ySize, $black);
    $DrawPos += $xres;
    $DrawPos += $xres;                                

    for($i = 6; $i < $len; $i++) {
		  $cchar = $mValue[$i];
      $c = strpos($mChars, $cchar);
      $cset  = $mCharSetR[$c];

      for($j = 0; $j < strlen($cset); $j++) {
        if(intval(substr($cset, $j, 1)) == 1) 
  	      ImageLine($img, $DrawPos, 0, $DrawPos, $ySize, $black);
        $DrawPos += $xres;
      }
    }       
//  DrawStop
    ImageLine($img, $DrawPos, 0, $DrawPos, $ySize,  $black);
    $DrawPos += $xres;
    $DrawPos += $xres;
    ImageLine($img, $DrawPos, 0, $DrawPos, $ySize, $black);
    $DrawPos += $xres;

// Draw text
    if($mStyle & BCS_DRAW_TEXT) {
      $mid = $sPos + $size/2;
      $len5 = (strlen($mCharSetL[$c])+1)*$xres*5;
      $ht = imagefontheight($mFont);

      ImageFilledRectangle($img,
                           $mid-$len5-$xres*2,
                           $ySize + 0 + BCD_DEFAULT_TEXT_OFFSET + BCD_DEFAULT_TEXT_FILL_OFFSET,
                           $mid-$xres*2,
                           $ySize + 0 + BCD_DEFAULT_TEXT_OFFSET + BCD_DEFAULT_TEXT_FILL_OFFSET + $ht,
                           $white
                          );
      ImageFilledRectangle($img,
                           $mid+$xres*2,
                           $ySize + 0 + BCD_DEFAULT_TEXT_OFFSET + BCD_DEFAULT_TEXT_FILL_OFFSET,
                           $mid+$len5+$xres*2,
                           $ySize + 0 + BCD_DEFAULT_TEXT_OFFSET + BCD_DEFAULT_TEXT_FILL_OFFSET + $ht,
                           $white
                          );

      imagestring($img,($mFont-2 > 1 ? $mFont-2 : 1), $sPos-$xres*3-ImageFontWidth($mFont > 1 ? $mFont - 1 : 1),
                      $ySize + 0 + BCD_DEFAULT_TEXT_OFFSET + BCD_DEFAULT_TEXT_FILL_OFFSET, $mValue[0], $black);
      $left = $mid-$len5;

      for ($i=1;$i<$len/2;$i++)
        imagestring($img, $mFont, $left+($size/$len)*($i-1), $ySize + 0 + BCD_DEFAULT_TEXT_OFFSET + BCD_DEFAULT_TEXT_FILL_OFFSET, $mValue[$i], $black);

      $left = $mid+$xres*4;

      for ($i=$len/2;$i<$len-1;$i++) 
				imagestring($img, $mFont, $left+($size/$len)*($i-$len/2), $ySize + 0 + BCD_DEFAULT_TEXT_OFFSET + BCD_DEFAULT_TEXT_FILL_OFFSET, $mValue[$i], $black);

      imagestring($img, ($mFont-2 > 1 ? $mFont-2 : 1), $sPos+$xres*6 + $size, $ySize + 0 + BCD_DEFAULT_TEXT_OFFSET + BCD_DEFAULT_TEXT_FILL_OFFSET, $mValue[$len-1], $black);
		}

	// add license message 
	barcode_license_message($img);

	return $img;

} //upca


function barcode_postnet($text)
{
	$barshortheight = 17;
	$bartallheight = 25;
	$barwidth = 2;
	$coding_map = array(
		'0' => '11000',
		'1' => '00011',
		'2' => '00101',
		'3' => '00110',
		'4' => '01001',
		'5' => '01010',
		'6' => '01100',
		'7' => '10001',
		'8' => '10010',
		'9' => '10100'
	);

	if (!preg_match("/[0-9]/",$text)) 
		die("Invalid text for PostNet");

	$barcodewidth = (strlen($text)) * 2 * 5 * $barwidth + $barwidth*3;
	$img = ImageCreate($barcodewidth, $bartallheight);

	$black = ImageColorAllocate($img, 0, 0, 0);
	$white = ImageColorAllocate($img, 255, 255, 255);
	imagefill($img, 0, 0, $white);

	$xpos = 0;

	imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $bartallheight, $black);
	$xpos += 2*$barwidth;

	for ($idx = 0; $idx < strlen($text); $idx++) {
		$char  = substr($text, $idx, 1);

		for ($baridx = 0; $baridx < 5; $baridx++) {
			$elementheight = (substr($coding_map[$char], $baridx, 1)) ?  0 : $barshortheight;
			imagefilledrectangle($img, $xpos, $elementheight, $xpos + $barwidth - 1, $bartallheight, $black);
			$xpos += 2*$barwidth;
		}
	}

        // Draws the trailer
	imagefilledrectangle($img, $xpos, 0, $xpos + $barwidth - 1, $bartallheight, $black);
	$xpos += 2*$barwidth;

	// add license message 
	barcode_license_message($img);

	return $img;

} // postnet

?>