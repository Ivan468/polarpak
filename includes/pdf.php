<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  pdf.php                                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


class VA_PDF
{
	//Private properties
	var $page;               //current page number
	var $n;                  //current object number
	var $offsets;            //array of object offsets
	var $buffer;             //buffer holding in-memory PDF
	var $pages;              //array containing pages
	var $state;              //current document state
	var $compress;           //compression flag
	var $fwPt,$fhPt;         //dimensions of page format in points
	var $LineWidth;          //line width in user unit
	var $fonts;              //array of used fonts
	var $FontFiles;          //array of font files
	var $diffs;              //array of encoding differences
	var $diffs_objects;      //array of encoding objects
	var $images;             //array of used images
	var $PageLinks;          //array of links in pages
	var $links;              //array of internal links
	var $FontName;           //current font name 
	var $FontFamily;         //current font family
	var $FontStyle;          //current font style
	var $LineHeight;         //the heigt of text line
	var $font_encoding;      // current encoding
	var $underline;          //underlining flag
	var $CurrentFont;        //current font info
	var $FontSizePt;         //current font size in points
	var $FontSize;           //current font size in user unit
	var $DrawColor;          //commands for drawing color
	var $FillColor;          //commands for filling color
	var $TextColor;          //commands for text color
	var $ColorFlag;          //indicates whether fill and text colors are different
	var $ws;                 //word spacing
	var $ZoomMode;           //zoom display mode
	var $LayoutMode;         //layout display mode
	var $title;              //title
	var $subject;            //subject
	var $author;             //author
	var $keywords;           //keywords
	var $creator;            //creator
	var $PDFVersion;         //PDF version number
	// standard font names
	var $core_fonts = array('courier'=>'Courier','courierb'=>'Courier-Bold','courieri'=>'Courier-Oblique','courierbi'=>'Courier-BoldOblique',
		'helvetica'=>'Helvetica','helveticab'=>'Helvetica-Bold','helveticai'=>'Helvetica-Oblique','helveticabi'=>'Helvetica-BoldOblique',
		'times'=>'Times-Roman','timesb'=>'Times-Bold','timesi'=>'Times-Italic','timesbi'=>'Times-BoldItalic',
		'arial'=>'Arial', 'arialb'=>'Arial-Bold', 'arialbi'=>'Arial-BoldItalic',
		'symbol'=>'Symbol','zapfdingbats'=>'ZapfDingbats');


public function __construct()
{
	//Some checks
	$this->_dochecks();
	//Initialization of properties
	$this->page=0;
	$this->n=2;
	$this->buffer='';
	$this->pages=array();
	$this->state=0;
	$this->fonts=array();
	$this->FontFiles=array();
	$this->diffs=array();
	$this->images=array();
	$this->links=array();
	$this->FontFamily='';
	$this->FontStyle='';
	$this->font_encoding = '';
	$this->FontSizePt=12;
	$this->underline=false;
	$this->DrawColor='0 G';
	$this->FillColor='0 g';
	$this->TextColor='0 g';
	$this->ColorFlag=false;
	$this->ws=0;
	$this->fwPt = 595.28;
	$this->fhPt = 841.89;

	//Line width (0.2 mm)
	$this->LineWidth=.567;
	//Full width display mode
	$this->SetDisplayMode('fullwidth');
	//Enable compression
	$this->SetCompression(true);
	//Set default PDF version number
	$this->PDFVersion='1.3';

	// use DejaVuSansCondensed or DejaVuSerifCondensed font as default one 
	$this->add_font('DejaVu','','DejaVuSerifCondensed.ttf',true);
	$this->add_font('DejaVu-Bold','','DejaVuSerifCondensed-Bold.ttf',true);

}

function SetDisplayMode($zoom,$layout='continuous')
{
	//Set display mode in viewer
	if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
		$this->ZoomMode=$zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
		$this->LayoutMode=$layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress)
{
	//Set page compression
	if(function_exists('gzcompress'))
		$this->compress=$compress;
	else
		$this->compress=false;
}

function set_title($title)
{
	$this->title=$title;
}

function set_subject($subject)
{
	$this->subject=$subject;
}

function set_author($author)
{
	$this->author=$author;
}

function set_keywords($keywords)
{
	$this->keywords=$keywords;
}

function set_creator($creator)
{
	$this->creator=$creator;
}

function Error($msg)
{
	//Fatal error
	die('<B>VA_PDF error: </B>'.$msg);
}

function Open()
{
	//Begin document
	$this->state=1;
}

function Close()
{
	//Terminate document
	if($this->state==3)
		return;
	if($this->page==0)
		$this->begin_page();
	//Close page            
	$this->_endpage();
	//Close document
	$this->_enddoc();
}

function begin_page($width = 0, $height = 0)
{
	if ($width > 0) {
		$this->fwPt=$width;
	}
	if ($height > 0) {
		$this->fhPt=$height;
	}
	//Start a new page
	if ($this->state==0) {
		$this->Open();
	}
	$family = $this->FontFamily;
	$style = $this->FontStyle.($this->underline ? 'U' : '');
	$size=$this->FontSizePt;
	$lw=$this->LineWidth;
	$dc=$this->DrawColor;
	$fc=$this->FillColor;
	$tc=$this->TextColor;
	$cf=$this->ColorFlag;
	if($this->page>0)
	{
		//Close page
		$this->_endpage();
	}
	//Start new page
	$this->_beginpage();
	//Set line cap style to square
	$this->_out('2 J');
	//Set line width
	$this->LineWidth=$lw;
	$this->_out(sprintf('%.2f w',$lw));
	//Set font
	if ($family) {
		$this->SetFont($family, $style, $size);
	}
	//Set colors
	$this->DrawColor=$dc;
	if($dc!='0 G')
		$this->_out($dc);
	$this->FillColor=$fc;
	if($fc!='0 g')
		$this->_out($fc);
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
	//Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth=$lw;
		$this->_out(sprintf('%.2f w',$lw));
	}
	//Restore font
	if($family) {
		$this->SetFont($family, $style, $size);
	}
	//Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor=$dc;
		$this->_out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor=$fc;
		$this->_out($fc);
	}
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
}

function end_page() 
{
	$this->_endpage();
}

function PageNo()
{
	//Get current page number
	return $this->page;
}

function SetDrawColor($r,$g=-1,$b=-1)
{
	//Set color for all stroking operations
	if(($r==0 && $g==0 && $b==0) || $g==-1)
		$this->DrawColor=sprintf('%.3f G',$r/255);
	else
		$this->DrawColor=sprintf('%.3f %.3f %.3f RG',$r/255,$g/255,$b/255);
	if($this->page>0)
		$this->_out($this->DrawColor);
}

function SetFillColor($r,$g=-1,$b=-1)
{
	//Set color for all filling operations
	if(($r==0 && $g==0 && $b==0) || $g==-1)
		$this->FillColor=sprintf('%.3f g',$r/255);
	else
		$this->FillColor=sprintf('%.3f %.3f %.3f rg',$r/255,$g/255,$b/255);
	$this->ColorFlag=($this->FillColor!=$this->TextColor);
	if($this->page>0)
		$this->_out($this->FillColor);
}

function SetTextColor($r,$g=-1,$b=-1)
{
	//Set color for text
	if(($r==0 && $g==0 && $b==0) || $g==-1)
		$this->TextColor=sprintf('%.3f g',$r/255);
	else
		$this->TextColor=sprintf('%.3f %.3f %.3f rg',$r/255,$g/255,$b/255);
	$this->ColorFlag=($this->FillColor!=$this->TextColor);
}

function stringwidth($s)
{
	// Get width of a string in the current font
	$s = (string)$s;
	$cw = &$this->CurrentFont['cw'];
	$w=0;
	$unicode = $this->UTF8StringToArray($s);
	foreach($unicode as $char) {
		if (isset($cw[$char])) { 
			$w += $cw[$char]; 
		} else if($char > 0 && $char < 128 && isset($cw[chr($char)])) { 
			$w += $cw[chr($char)]; 
		} else if(isset($this->CurrentFont['desc']['MissingWidth'])) { 
			$w += $this->CurrentFont['desc']['MissingWidth']; 
		} else if(isset($this->CurrentFont['MissingWidth'])) { 
			$w += $this->CurrentFont['MissingWidth']; 
		} else { 
			$w += 500; 
		}
	}
	return $w*$this->FontSize/1000;
}


function SetLineWidth($width)
{
	//Set line width
	$this->LineWidth=$width;
	if($this->page>0)
		$this->_out(sprintf('%.2f w', $width));
}

function Line($x1,$y1,$x2,$y2)
{
	//Draw a line
	$this->_out(sprintf('%.2f %.2f m %.2f %.2f l S',$x1,$y1,$x2,$y2));
}

function Rect($x,$y,$w,$h,$style='')
{
	//Draw a rectangle
	if($style=='F')
		$op='f';
	elseif($style=='FD' || $style=='DF')
		$op='B';
	else
		$op='S';
	$this->_out(sprintf('%.2f %.2f %.2f %.2f re %s',$x,$y,$w,$h,$op));
}

function add_font($family, $style = "", $file = "")
{
	// for UTF-8 use only TTF fonts
	$family = strtolower($family);
	$style = strtoupper($style);
	if($style == "IB") {
		$style = "BI";
	}
	if (!$file) {
		$file = str_replace(" ","",$family).strtolower($style).'.ttf';
	}
	$fontkey = $family.$style;
	if (isset($this->fonts[$fontkey])) {
		return;
	}

	$ttf_filename = $this->get_font_path().$file ;
	$unifilename = $this->get_font_path().strtolower(substr($file ,0,(strpos($file ,'.'))));
	$name = '';
	$originalsize = 0;
	$ttfstat = stat($ttf_filename);

	$ttf = new TTFontFile();
	$ttf->getMetrics($ttf_filename);
	$cw = $ttf->charWidths;
	$name = preg_replace('/[ ()]/','',$ttf->fullName);

	$desc= array(
		'Ascent'=>round($ttf->ascent),
		'Descent'=>round($ttf->descent),
		'CapHeight'=>round($ttf->capHeight),
		'Flags'=>$ttf->flags,
		'FontBBox'=>'['.round($ttf->bbox[0])." ".round($ttf->bbox[1])." ".round($ttf->bbox[2])." ".round($ttf->bbox[3]).']',
		'ItalicAngle'=>$ttf->italicAngle,
		'StemV'=>round($ttf->stemV),
		'MissingWidth'=>round($ttf->defaultWidth)
	);
	$up = round($ttf->underlinePosition);
	$ut = round($ttf->underlineThickness);
	$originalsize = $ttfstat['size']+0;
	$type = 'TTF';

	unset($ttf);

	$i = count($this->fonts)+1;
	$sbarr = range(0,32); // subset array of first 33 chars including Null

	$this->fonts[$fontkey] = array('i'=>$i, 'type'=>$type, 'name'=>$name, 'desc'=>$desc, 'up'=>$up, 'ut'=>$ut, 'cw'=>$cw, 'ttffile'=>$ttf_filename, 'fontkey'=>$fontkey, 'subset'=>$sbarr, 'unifilename'=>$unifilename);

	$this->FontFiles[$fontkey]=array('length1'=>$originalsize, 'type'=>"TTF", 'ttffile'=>$ttf_filename);
	$this->FontFiles[$file]=array('type'=>"TTF");
}


function SetFont($family, $style = "", $size = 0)
{
	//Select a font; size given in points
	$this->FontName = $family;

	$style=strtolower($style);
	// check underline style and remove it 
	if (preg_match("/u/i", $style)) {
		$this->underline = true;
		$style = preg_replace("/u/i", "", $style);
	} else {
		$this->underline=false;
	}

	// check bold, oblique, italic options
	if (preg_match("/[bio]/i", $style)) {
		$family .= "-";
		if (preg_match("/b/i", $style)) { $family .= "Bold"; }
		if (preg_match("/o/i", $style)) { $family .= "Oblique"; }
		if (preg_match("/i/i", $style)) { $family .= "Italic"; }
		$style = preg_replace("/[bio]/i", "", $style);
	}

	// change our default old Helvetica font to new DejaVu
	if (preg_match("/helvetica/i", $family)) {
		$family = preg_replace("/helvetica/i", "DejaVu", $family);
	}

	$family=strtolower($family);
	if ($family == "") {
		$family=$this->FontFamily;
	}
	if ($size==0) {
		$size=$this->FontSizePt;	
	}
	//Test if font is already selected
	if ($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size) {
		return;
	}
	//Test if used for the first time
	$fontkey=$family.$style;

	if(!isset($this->fonts[$fontkey]))
	{
		//Check if one of the standard fonts
		if(isset($this->core_fonts[$fontkey]))
		{
			$cw = array();
			//Load metric file
			$file = $family;
			if ($family== "times" || $family == "helvetica") {
				$file .= strtolower($style);
			}
			include($this->get_font_path().$file.".php");
			if (!is_array($cw) || sizeof($cw) < 1) {
				$this->Error(FONT_METRIC_FILE_ERROR);
			}
			$i = count($this->fonts) + 1;
			$enc = $this->get_font_encoding();
			$this->font_encoding_diff($enc);
			$this->fonts[$fontkey]=array('i'=>$i,'type'=>'core','name'=>$this->core_fonts[$fontkey],'up'=>-100,'ut'=>50,'cw'=>$cw,'enc'=>$enc);

		} else {
			$this->Error('Undefined font: '.$family.' '.$style);
		}
	}
	//Select it
	$this->FontFamily=$family;
	$this->FontStyle=$style;
	$this->FontSizePt=$size;
	$this->FontSize=$size;
	$this->LineHeight=$size;

	$this->CurrentFont=&$this->fonts[$fontkey];
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2f Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

function SetFontSize($size)
{
	//Set font size in points
	if($this->FontSizePt==$size)
		return;
	$this->FontSizePt=$size;
	$this->FontSize=$size;
	$this->LineHeight=$size;
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2f Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

	function set_font_encoding($encoding) 
	{
		if ($encoding) {
			$encoding = preg_replace("/^windows\-/", "cp", $encoding);
		} else {
			$encoding = "host";
		}
		$this->font_encoding = $encoding;
	}

	function get_font_encoding() 
	{
		return $this->font_encoding;
	}

	function font_encoding_diff($enc, $diff = "")
	{
		if ($enc && $enc != "host" && $enc != "winansi") {
			if (!isset($this->diffs[$enc])) {
				if (!$diff) {
					$diff = "0";
					$file = $this->get_font_path() . $enc.".map";
					if (file_exists($file)) {	
						$fp = fopen($file, 'r');
						if ($fp) {
							$prev_code = 0;
							while(!feof($fp)) {
								$line = trim(fgets($fp));
								if ($line && preg_match("/^\\!([^\\s\\t]+)[\\s\\t]+([^\\s\\t]+)[\\s\\t]+([^\\s\\t]+)$/", $line, $matches)) {
									$code = hexdec($matches[1]);
									if ($code - 1 > $prev_code) {
										for ($ci = $prev_code + 1; $ci < $code; $ci++) {
											$diff .= " /.notdef";
										}
									}
									$symbol = $matches[3];
									$diff .= " /" . $symbol;
									$prev_code = $code;
								}
							}
						}
					}
				}
				$this->diffs[$enc] = $diff;
			}
		}
	}

	function AddLink()
	{
		//Create a new internal link
		$n=count($this->links)+1;
		$this->links[$n]=array(0,0);
		return $n;
	}

	function SetLink($link, $y, $page=-1)
	{
		//Set destination of internal link
		if ($page == -1) { $page=$this->page; }
		$this->links[$link]=array($page,$y);
	}

	function Link($x, $y, $w, $h, $link)
	{
		//Put a link on the page
		$this->PageLinks[$this->page][]=array($x,$y,$w,$h,$link);
	}

	function prepare_text($text, $width)
	{
		$lines = array();
		$fontsize = $this->FontSizePt;
		$parts = explode("\n", $text);
		for ($p = 0; $p < sizeof($parts); $p++) {
			$part = trim($parts[$p]);
			$full_words = explode(" ", $part);
			// split long words onto smaller parts
			$words = array();
			for ($w = 0; $w < sizeof($full_words); $w++) {
				$full_word = $full_words[$w];
				while (mb_strlen($full_word, "UTF-8") > 0) {
					$word_length = mb_strlen($full_word, "UTF-8");
					$line_width = $this->stringwidth($full_word);
					while ($line_width > $width && $word_length > 1) {
						$word_length -= 1;
						$line_width = $this->stringwidth(mb_substr($full_word, 0, $word_length, "UTF-8"));
					}
					$words[] = mb_substr($full_word, 0, $word_length, "UTF-8");
					$full_word = mb_substr($full_word, $word_length, NULL, "UTF-8");
				}
			}
			$line = "";
			$last_index = sizeof($words) - 1;
			for ($w = 0; $w <= $last_index; $w++) {
				$line .= $words[$w] . " ";
				$next_word = ($w == $last_index) ? "" : $words[$w + 1];
				$line_width = $this->stringwidth(trim($line.$next_word));
				if ($w == $last_index || $line_width > $width) {
					$lines[] = trim($line);
					$line = "";
				}
			}
		}     
		return $lines;
	}

	function show_xy($text, $right, $top, $width = 0, $height = 0, $horizontal_mode = "left", $vertical_mode = "middle", $direction = "down")
	{
		// check for chinese symbols to change font
		$original_font = "";
		if (preg_match("/\p{Han}+/u", $text)) {
			$original_font = $this->FontName;
			$this->add_font("Chinese",'','Chinese.ttf',true);
			$this->setfont("Chinese", "", $this->FontSize);
		} else if (preg_match("/\p{Thai}/u", $text)) {
			$original_font = $this->FontName;
			$this->add_font("Garuda",'','Garuda.ttf',true);
			$this->setfont("Garuda", "", $this->FontSize);
		}


		$text_height = 0;
		$line_height = $this->LineHeight;
		if ($width > 0) {
			$lines = $this->prepare_text($text, $width);
			$text_height = sizeof($lines) * $line_height;
			$top_indent = 0;
			if ($height > 0 && $height > $text_height) {
				if ($vertical_mode == "middle") {
					$top_indent = round(($height - $text_height) / 2);
				} else if ($vertical_mode == "bottom") {
					$top_indent = $height - $text_height;
				}
			}
			for ($l = 0; $l < sizeof($lines); $l++) {
				$line = $lines[$l];
				$top_indent += $line_height;
				$line_width = $this->stringwidth(trim($line));
				if ($horizontal_mode == "center") {
					$indent = round(($width - $line_width) / 2);
					$this->text (trim($line), $right + $indent, $top - $top_indent);
				} else if ($horizontal_mode == "right") {
					$indent = $width - $line_width;
					$this->text (trim($line), $right + $indent, $top - $top_indent);
				} else {
					$this->text (trim($line), $right, $top - $top_indent);
				}
			}
		} else {
			$text_height = $line_height;
			$this->text(trim($text), $right, $top - $height);
		}
		// back to original font
		if ($original_font) {
			$this->setfont($original_font, $this->FontStyle, $this->FontSize);
		}
		return $text_height;
	}

function text($txt, $x, $y)
{
	// Output a string
	$txt2 = '('.$this->_escape($this->strUTF8ToUTF16BE($txt, false)).')';
	foreach($this->UTF8StringToArray($txt) as $uni)
		$this->CurrentFont['subset'][$uni] = $uni;

	$s = sprintf('BT %.2F %.2F Td %s Tj ET',$x, $y, $txt2);
	if($this->underline && $txt!='')
		$s .= ' '.$this->_dounderline($x,$y,$txt);
	if($this->ColorFlag)
		$s = 'q '.$this->TextColor.' '.$s.' Q';
	$this->_out($s);

}

function word_space($ws)
{
	$this->ws = $ws;
	if($ws>0)
	{
		$this->ws=0;
		$this->_out('0 Tw');
	}
	if($ws>0)
	{
		$this->ws=$ws;
		$this->_out(sprintf('%.3f Tw',$ws));
	}
}


function place_image($file,$x,$y,$type='',$link='',$w=0,$h=0)
{
	//Put an image on the page
	if(!isset($this->images[$file]))
	{
		//First use of image, get info
		if($type=='')
		{
			$pos=strrpos($file,'.');
			if(!$pos)
				$this->Error('Image file has no extension and no type was specified: '.$file);
			$type=substr($file,$pos+1);
		}
		$type=strtolower($type);
		if ($type == 'jpg' || $type == 'jpeg') {
			$info = $this->_parsejpg($file);
		} elseif ($type == 'png') {
			$info = $this->_parsepng($file);
		} elseif ($type == 'gif') {
			$info = $this->_parsegif($file);
		} else {
			$this->Error("Unsupported image type: " . $type);
		}
		$info['i']=count($this->images)+1;
		$this->images[$file]=$info;
	}
	else
		$info=$this->images[$file];
	//Automatic width and height calculation if needed
	if($w==0 && $h==0)
	{
		//Put image at 72 dpi
		$w=$info['w'];
		$h=$info['h'];
	}
	if($w==0)
		$w=$h*$info['w']/$info['h'];
	if($h==0)
		$h=$w*$info['h']/$info['w'];
	$this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q',$w,$h,$x,$y,$info['i']));
	if($link)
		$this->Link($x,$y,$w,$h,$link);
}

	function get_buffer()
	{
		if ($this->state < 3) {
			$this->Close();
		}
		return $this->buffer;
	}

	function save_file($filename)
	{
		if ($this->state < 3) { $this->Close(); }
		$fp = fopen($filename,'wb');
		if (!$fp) {
			$this->Error("Unable to create output file: " . $filename);
		}
		fwrite($fp, $this->buffer, strlen($this->buffer));
		fclose($fp);
	}


function _dochecks()
{
	//Check for locale-related bug
	if(1.1==1)
		$this->Error('Don\'t alter the locale before including class file');
	//Check for decimal separator
	if(sprintf('%.1f',1.0)!='1.0')
		setlocale(LC_NUMERIC,'C');
}

	function get_font_path()
	{
		if (defined("VA_PDF_FONTPATH")) {
			$font_path = constant("VA_PDF_FONTPATH");
		} else if (is_dir(dirname(__FILE__)."/font")) {
			$font_path = dirname(__FILE__)."/font/";
		} else {
			$font_path = "";
		}
		return $font_path;
	}

function _putpages()
{
	$nb=$this->page;
	$wPt=$this->fwPt;
	$hPt=$this->fhPt;

	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	for($n=1;$n<=$nb;$n++)
	{
		//Page
		$this->_newobj();
		$this->_out('<</Type /Page');
		$this->_out('/Parent 1 0 R');
		//if(isset($this->OrientationChanges[$n]))
		//	$this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]',$hPt,$wPt));
		$this->_out('/Resources 2 0 R');
		if(isset($this->PageLinks[$n]))
		{
			//Links
			$annots='/Annots [';
			foreach($this->PageLinks[$n] as $pl)
			{
				$rect=sprintf('%.2f %.2f %.2f %.2f',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
				$annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
				if(is_string($pl[4]))
					$annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
				else
				{
					$l = $this->links[$pl[4]];
					//$h=isset($this->OrientationChanges[$l[0]]) ? $wPt : $hPt;
					$h = $hPt;
					$annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]>>',1+2*$l[0],$h-$l[1]);
				}
			}
			$this->_out($annots.']');
		}
		$this->_out('/Contents '.($this->n+1).' 0 R>>');
		$this->_out('endobj');
		//Page content
		$p=($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
		$this->_newobj();
		$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->_putstream($p);
		$this->_out('endobj');
	}
	//Pages root
	$this->offsets[1]=strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<</Type /Pages');
	$kids='/Kids [';
	for($i=0;$i<$nb;$i++)
		$kids.=(3+2*$i).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$nb);
	$this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]',$wPt,$hPt));
	$this->_out('>>');
	$this->_out('endobj');
}


function _putfonts()
{
	$nf=$this->n;
	foreach($this->diffs as $diff)
	{
		// Encodings
		$this->_newobj();
		$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
		$this->_out('endobj');
	}
	foreach($this->FontFiles as $file=>$info)
	{
	   if (!isset($info['type']) || $info['type']!='TTF') {
		// Font file embedding
		$this->_newobj();
		$this->FontFiles[$file]['n']=$this->n;
		$font='';
		$f=fopen($this->get_font_path().$file,'rb',1);
		if(!$f)
			$this->Error('Font file not found');
		while(!feof($f))
			$font.=fread($f,8192);
		fclose($f);
		$compressed=(substr($file,-2)=='.z');
		if(!$compressed && isset($info['length2']))
		{
			$header=(ord($font[0])==128);
			if($header)
			{
				// Strip first binary header
				$font=substr($font,6);
			}
			if($header && ord($font[$info['length1']])==128)
			{
				// Strip second binary header
				$font=substr($font,0,$info['length1']).substr($font,$info['length1']+6);
			}
		}
		$this->_out('<</Length '.strlen($font));
		if($compressed)
			$this->_out('/Filter /FlateDecode');
		$this->_out('/Length1 '.$info['length1']);
		if(isset($info['length2']))
			$this->_out('/Length2 '.$info['length2'].' /Length3 0');
		$this->_out('>>');
		$this->_putstream($font);
		$this->_out('endobj');
	   }
	}
	foreach($this->fonts as $k=>$font)
	{
		// Font objects
		//$this->fonts[$k]['n']=$this->n+1;
		$type = $font['type'];
		$name = $font['name'];
		if($type=='Core')
		{
			// Standard font
			$this->fonts[$k]['n']=$this->n+1;
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/BaseFont /'.$name);
			$this->_out('/Subtype /Type1');
			if($name!='Symbol' && $name!='ZapfDingbats')
				$this->_out('/Encoding /WinAnsiEncoding');
			$this->_out('>>');
			$this->_out('endobj');
		}
		elseif($type=='Type1' || $type=='TrueType')
		{
			// Additional Type1 or TrueType font
			$this->fonts[$k]['n']=$this->n+1;
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/BaseFont /'.$name);
			$this->_out('/Subtype /'.$type);
			$this->_out('/FirstChar 32 /LastChar 255');
			$this->_out('/Widths '.($this->n+1).' 0 R');
			$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
			if($font['enc'])
			{
				if(isset($font['diff']))
					$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
				else
					$this->_out('/Encoding /WinAnsiEncoding');
			}
			$this->_out('>>');
			$this->_out('endobj');
			// Widths
			$this->_newobj();
			$cw=&$font['cw'];
			$s='[';
			for($i=32;$i<=255;$i++)
				$s.=$cw[chr($i)].' ';
			$this->_out($s.']');
			$this->_out('endobj');
			// Descriptor
			$this->_newobj();
			$s='<</Type /FontDescriptor /FontName /'.$name;
			foreach($font['desc'] as $k=>$v)
				$s.=' /'.$k.' '.$v;
			$file=$font['file'];
			if($file)
				$s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
			$this->_out($s.'>>');
			$this->_out('endobj');
		}
		// TrueType embedded SUBSETS or FULL
		else if ($type=='TTF') {
			$this->fonts[$k]['n']=$this->n+1;
			$ttf = new TTFontFile();
			$fontname = 'MPDFAA'.'+'.$font['name'];
			$subset = $font['subset'];
			unset($subset[0]);
			$ttfontstream = $ttf->makeSubset($font['ttffile'], $subset);
			$ttfontsize = strlen($ttfontstream);
			$fontstream = gzcompress($ttfontstream);
			$codeToGlyph = $ttf->codeToGlyph;
			unset($codeToGlyph[0]);

			// Type0 Font
			// A composite font - a font composed of other fonts, organized hierarchically
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/Subtype /Type0');
			$this->_out('/BaseFont /'.$fontname.'');
			$this->_out('/Encoding /Identity-H'); 
			$this->_out('/DescendantFonts ['.($this->n + 1).' 0 R]');
			$this->_out('/ToUnicode '.($this->n + 2).' 0 R');
			$this->_out('>>');
			$this->_out('endobj');

			// CIDFontType2
			// A CIDFont whose glyph descriptions are based on TrueType font technology
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/Subtype /CIDFontType2');
			$this->_out('/BaseFont /'.$fontname.'');
			$this->_out('/CIDSystemInfo '.($this->n + 2).' 0 R'); 
			$this->_out('/FontDescriptor '.($this->n + 3).' 0 R');
			if (isset($font['desc']['MissingWidth'])){
				$this->_out('/DW '.$font['desc']['MissingWidth'].''); 
			}

			$this->_putTTfontwidths($font, $ttf->maxUni);

			$this->_out('/CIDToGIDMap '.($this->n + 4).' 0 R');
			$this->_out('>>');
			$this->_out('endobj');

			// ToUnicode
			$this->_newobj();
			$toUni = "/CIDInit /ProcSet findresource begin\n";
			$toUni .= "12 dict begin\n";
			$toUni .= "begincmap\n";
			$toUni .= "/CIDSystemInfo\n";
			$toUni .= "<</Registry (Adobe)\n";
			$toUni .= "/Ordering (UCS)\n";
			$toUni .= "/Supplement 0\n";
			$toUni .= ">> def\n";
			$toUni .= "/CMapName /Adobe-Identity-UCS def\n";
			$toUni .= "/CMapType 2 def\n";
			$toUni .= "1 begincodespacerange\n";
			$toUni .= "<0000> <FFFF>\n";
			$toUni .= "endcodespacerange\n";
			$toUni .= "1 beginbfrange\n";
			$toUni .= "<0000> <FFFF> <0000>\n";
			$toUni .= "endbfrange\n";
			$toUni .= "endcmap\n";
			$toUni .= "CMapName currentdict /CMap defineresource pop\n";
			$toUni .= "end\n";
			$toUni .= "end";
			$this->_out('<</Length '.(strlen($toUni)).'>>');
			$this->_putstream($toUni);
			$this->_out('endobj');

			// CIDSystemInfo dictionary
			$this->_newobj();
			$this->_out('<</Registry (Adobe)'); 
			$this->_out('/Ordering (UCS)');
			$this->_out('/Supplement 0');
			$this->_out('>>');
			$this->_out('endobj');

			// Font descriptor
			$this->_newobj();
			$this->_out('<</Type /FontDescriptor');
			$this->_out('/FontName /'.$fontname);
			foreach($font['desc'] as $kd=>$v) {
				if ($kd == 'Flags') { $v = $v | 4; $v = $v & ~32; }	// SYMBOLIC font flag
				$this->_out(' /'.$kd.' '.$v);
			}
			$this->_out('/FontFile2 '.($this->n + 2).' 0 R');
			$this->_out('>>');
			$this->_out('endobj');

			// Embed CIDToGIDMap
			// A specification of the mapping from CIDs to glyph indices
			$cidtogidmap = '';
			$cidtogidmap = str_pad('', 256*256*2, "\x00");
			foreach($codeToGlyph as $cc=>$glyph) {
				$cidtogidmap[$cc*2] = chr($glyph >> 8);
				$cidtogidmap[$cc*2 + 1] = chr($glyph & 0xFF);
			}
			$cidtogidmap = gzcompress($cidtogidmap);
			$this->_newobj();
			$this->_out('<</Length '.strlen($cidtogidmap).'');
			$this->_out('/Filter /FlateDecode');
			$this->_out('>>');
			$this->_putstream($cidtogidmap);
			$this->_out('endobj');

			//Font file 
			$this->_newobj();
			$this->_out('<</Length '.strlen($fontstream));
			$this->_out('/Filter /FlateDecode');
			$this->_out('/Length1 '.$ttfontsize);
			$this->_out('>>');
			$this->_putstream($fontstream);
			$this->_out('endobj');
			unset($ttf);
		} 
		else
		{
			// Allow for additional types
			$this->fonts[$k]['n'] = $this->n+1;
			$mtd='_put'.strtolower($type);
			if(!method_exists($this,$mtd))
				$this->Error('Unsupported font type: '.$type);
			$this->$mtd($font);
		}
	}
}

function _putTTfontwidths(&$font, $maxUni) {
	// special variable to build font width descriptor like \W [ 32 [ 286 361 414 754 572 855 801 247 351 351 450 754 286 304 286 303 ]
	$rangeid = 0;
	$range = array();
	$prevcid = -2;
	$prevwidth = -1;
	$interval = false;
	$startcid = 1;

	$cwlen = $maxUni + 1; 

	// go thru all characters
	for ($cid=$startcid; $cid<$cwlen; $cid++) {
		if (!isset($font['cw'][$cid])) { continue; }
		$width = $font['cw'][$cid];

		if ($cid > 255 && (!isset($font['subset'][$cid]) || !$font['subset'][$cid])) { continue; }
		if (!isset($font['dw']) || (isset($font['dw']) && $width != $font['dw'])) {
			if ($cid == ($prevcid + 1)) {
				if ($width == $prevwidth) {
					if ($width == $range[$rangeid][0]) {
						$range[$rangeid][] = $width;
					}
					else {
						array_pop($range[$rangeid]);
						// new range
						$rangeid = $prevcid;
						$range[$rangeid] = array();
						$range[$rangeid][] = $prevwidth;
						$range[$rangeid][] = $width;
					}
					$interval = true;
					$range[$rangeid]['interval'] = true;
				} else {
					if ($interval) {
						// new range
						$rangeid = $cid;
						$range[$rangeid] = array();
						$range[$rangeid][] = $width;
					}
					else { $range[$rangeid][] = $width; }
					$interval = false;
				}
			} else {
				$rangeid = $cid;
				$range[$rangeid] = array();
				$range[$rangeid][] = $width;
				$interval = false;
			}
			$prevcid = $cid;
			$prevwidth = $width;
		}
	}
	$prevk = -1;
	$nextk = -1;
	$prevint = false;
	foreach ($range as $k => $ws) {
		$cws = count($ws);
		if (($k == $nextk) AND (!$prevint) AND ((!isset($ws['interval'])) OR ($cws < 4))) {
			if (isset($range[$k]['interval'])) { unset($range[$k]['interval']); }
			$range[$prevk] = array_merge($range[$prevk], $range[$k]);
			unset($range[$k]);
		}
		else { $prevk = $k; }
		$nextk = $k + $cws;
		if (isset($ws['interval'])) {
			if ($cws > 3) { $prevint = true; }
			else { $prevint = false; }
			unset($range[$k]['interval']);
			--$nextk;
		}
		else { $prevint = false; }
	}
	$w = '';
	foreach ($range as $k => $ws) {
		if (count(array_count_values($ws)) == 1) { $w .= ' '.$k.' '.($k + count($ws) - 1).' '.$ws[0]; }
		else { $w .= ' '.$k.' [ '.implode(' ', $ws).' ]' . "\n"; }
	}
	$this->_out('/W ['.$w.' ]');
}


function _putimages()
{
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	foreach($this->images as $file => $info) {
		$this->_newobj();
		$this->images[$file]['n']=$this->n;
		$this->_out('<</Type /XObject');
		$this->_out('/Subtype /Image');
		$this->_out('/Width '.$info['w']);
		$this->_out('/Height '.$info['h']);
		if($info['cs']=='Indexed')
			$this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
		else
		{
			$this->_out('/ColorSpace /'.$info['cs']);
			if($info['cs']=='DeviceCMYK')
				$this->_out('/Decode [1 0 1 0 1 0 1 0]');
		}
		$this->_out('/BitsPerComponent '.$info['bpc']);
		if(isset($info['f']))
			$this->_out('/Filter /'.$info['f']);
		if(isset($info['parms']))
			$this->_out($info['parms']);
		if(isset($info['trns']) && is_array($info['trns']))
		{
			$trns='';
			for($i=0;$i<count($info['trns']);$i++)
				$trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
			$this->_out('/Mask ['.$trns.']');
		}
		$this->_out('/Length '.strlen($info['data']).'>>');
		$this->_putstream($info['data']);
		unset($this->images[$file]['data']);
		$this->_out('endobj');
		//Palette
		if($info['cs']=='Indexed')
		{
			$this->_newobj();
			$pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
			$this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
			$this->_putstream($pal);
			$this->_out('endobj');
		}
	}
}

function _putxobjectdict()
{
	foreach($this->images as $image)
		$this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
}

function _putresourcedict()
{
	$this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->_out('/Font <<');
	foreach($this->fonts as $font)
		$this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
	$this->_out('>>');
	$this->_out('/XObject <<');
	$this->_putxobjectdict();
	$this->_out('>>');
}

function _putresources()
{
	$this->_putfonts();
	$this->_putimages();
	//Resource dictionary
	$this->offsets[2]=strlen($this->buffer);
	$this->_out('2 0 obj');
	$this->_out('<<');
	$this->_putresourcedict();
	$this->_out('>>');
	$this->_out('endobj');
}

function _putinfo()
{
	$this->_out('/Producer '.$this->_textstring('VIART LLC'));
	if(!empty($this->title))
		$this->_out('/Title '.$this->_textstring($this->title));
	if(!empty($this->subject))
		$this->_out('/Subject '.$this->_textstring($this->subject));
	if(!empty($this->author))
		$this->_out('/Author '.$this->_textstring($this->author));
	if(!empty($this->keywords))
		$this->_out('/Keywords '.$this->_textstring($this->keywords));
	if(!empty($this->creator))
		$this->_out('/Creator '.$this->_textstring($this->creator));
	$this->_out('/CreationDate '.$this->_textstring('D:'.date('YmdHis')));
}

function _putcatalog()
{
	$this->_out('/Type /Catalog');
	$this->_out('/Pages 1 0 R');
	if($this->ZoomMode=='fullpage')
		$this->_out('/OpenAction [3 0 R /Fit]');
	elseif($this->ZoomMode=='fullwidth')
		$this->_out('/OpenAction [3 0 R /FitH null]');
	elseif($this->ZoomMode=='real')
		$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
	elseif(!is_string($this->ZoomMode))
		$this->_out('/OpenAction [3 0 R /XYZ null null '.($this->ZoomMode/100).']');
	if($this->LayoutMode=='single')
		$this->_out('/PageLayout /SinglePage');
	elseif($this->LayoutMode=='continuous')
		$this->_out('/PageLayout /OneColumn');
	elseif($this->LayoutMode=='two')
		$this->_out('/PageLayout /TwoColumnLeft');
}

function _putheader()
{
	$this->_out('%PDF-'.$this->PDFVersion);
}

function _puttrailer()
{
	$this->_out('/Size '.($this->n+1));
	$this->_out('/Root '.$this->n.' 0 R');
	$this->_out('/Info '.($this->n-1).' 0 R');
}

function _enddoc()
{
	$this->_putheader();
	$this->_putpages();
	$this->_putresources();
	//Info
	$this->_newobj();
	$this->_out('<<');
	$this->_putinfo();
	$this->_out('>>');
	$this->_out('endobj');
	//Catalog
	$this->_newobj();
	$this->_out('<<');
	$this->_putcatalog();
	$this->_out('>>');
	$this->_out('endobj');
	//Cross-ref
	$o=strlen($this->buffer);
	$this->_out('xref');
	$this->_out('0 '.($this->n+1));
	$this->_out('0000000000 65535 f ');
	for($i=1;$i<=$this->n;$i++)
		$this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
	//Trailer
	$this->_out('trailer');
	$this->_out('<<');
	$this->_puttrailer();
	$this->_out('>>');
	$this->_out('startxref');
	$this->_out($o);
	$this->_out('%%EOF');
	$this->state=3;
}

function _beginpage()
{
	$this->page++;
	$this->pages[$this->page]='';
	$this->state=2;
	$this->FontFamily='';
}

function _endpage()
{
	//End of page contents
	$this->state=1;
}

function _newobj()
{
	//Begin a new object
	$this->n++;
	$this->offsets[$this->n]=strlen($this->buffer);
	$this->_out($this->n.' 0 obj');
	return $this->n;
}

function _dounderline($x,$y,$txt)
{
	//Underline text
	$up=$this->CurrentFont['up'];
	$ut=$this->CurrentFont['ut'];
	$w=$this->stringwidth($txt)+$this->ws*substr_count($txt,' ');
	return sprintf('%.2f %.2f %.2f %.2f re f', $x, $y - 1.5, $w, 0.5);
}

function _parsejpg($file)
{
	//Extract info from a JPEG file
	$a=GetImageSize($file);
	if(!$a)
		$this->Error('Missing or incorrect image file: '.$file);
	if($a[2]!=2)
		$this->Error('Not a JPEG file: '.$file);
	if(!isset($a['channels']) || $a['channels']==3)
		$colspace='DeviceRGB';
	elseif($a['channels']==4)
		$colspace='DeviceCMYK';
	else
		$colspace='DeviceGray';
	$bpc=isset($a['bits']) ? $a['bits'] : 8;
	//Read whole file
	$f=fopen($file,'rb');
	$data='';
	while(!feof($f))
		$data.=fread($f,4096);
	fclose($f);
	return array('w'=>$a[0],'h'=>$a[1],'cs'=>$colspace,'bpc'=>$bpc,'f'=>'DCTDecode','data'=>$data);
}


function _parsepng($file)
{
	global $settings;
	//Extract info from a PNG file
	$f=fopen($file,'rb');
	if(!$f)
		$this->Error('Can\'t open image file: '.$file);
	//Check signature
	if(fread($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
		$this->Error('Not a PNG file: '.$file);
	//Read header chunk
	fread($f,4);
	if(fread($f,4)!='IHDR')
		$this->Error('Incorrect PNG file: '.$file);
	$w=$this->_freadint($f);
	$h=$this->_freadint($f);
	$bpc=ord(fread($f,1));
	if($bpc>8)
		$this->Error('16-bit depth not supported: '.$file);
	$ct=ord(fread($f,1));
	if($ct==0) {
		$colspace = 'DeviceGray';
	} elseif($ct==2) {
		$colspace = 'DeviceRGB';
	} elseif($ct==3) {
		$colspace = 'Indexed';
	} else {
		// convert to jpeg
		$im = imagecreatefrompng($file);
		imageinterlace($im, 0);
		$tmp_dir = ".";
		if (function_exists("get_setting_value")) {
			$tmp_dir  = get_setting_value($settings, "tmp_dir", ".");
		}
		$tmp = tempnam($tmp_dir, 'gif');
		if (!$tmp) {
			$this->Error("Unable to create a temporary file");
		}
		if (!imagejpeg($im,$tmp,85)) {
			$this->Error("Error while saving to temporary file");
		}
		imagedestroy($im);
		$info = $this->_parsejpg($tmp);
		unlink($tmp);
		return $info;
	}
	if(ord(fread($f,1))!=0)
		$this->Error('Unknown compression method: '.$file);
	if(ord(fread($f,1))!=0)
		$this->Error('Unknown filter method: '.$file);
	if(ord(fread($f,1))!=0)
		$this->Error('Interlacing not supported: '.$file);
	fread($f,4);
	$parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
	//Scan chunks looking for palette, transparency and image data
	$pal='';
	$trns='';
	$data='';
	do
	{
		$n=$this->_freadint($f);
		$type=fread($f,4);
		if($type=='PLTE')
		{
			//Read palette
			$pal=fread($f,$n);
			fread($f,4);
		}
		elseif($type=='tRNS')
		{
			//Read transparency info
			$t=fread($f,$n);
			if($ct==0)
				$trns=array(ord(substr($t,1,1)));
			elseif($ct==2)
				$trns=array(ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,5,1)));
			else
			{
				$pos=strpos($t,chr(0));
				if($pos!==false)
					$trns=array($pos);
			}
			fread($f,4);
		}
		elseif($type=='IDAT')
		{
			//Read image data block
			$data.=fread($f,$n);
			fread($f,4);
		}
		elseif($type=='IEND')
			break;
		else
			fread($f,$n+4);
	}
	while($n);
	if($colspace=='Indexed' && empty($pal))
		$this->Error('Missing palette in '.$file);
	fclose($f);
	return array('w'=>$w,'h'=>$h,'cs'=>$colspace,'bpc'=>$bpc,'f'=>'FlateDecode','parms'=>$parms,'pal'=>$pal,'trns'=>$trns,'data'=>$data);
}

function _parsegif($file)
{
	global $settings;
	// Extract info from a GIF file (via PNG conversion)
	if (!function_exists("imagepng")) {
		$this->Error("GD extension is required for GIF support");
	} else if (!function_exists("imagecreatefromgif")) {
		$this->Error("GD has no GIF read support");
	}
	$im = imagecreatefromgif($file);
	if(!$im) {
		$this->Error("Missing or incorrect image file: " . $file);
	}
	// disable interlace
	imageinterlace($im, 0);
	// create temporary file to generate PNG image
	$tmp_dir = ".";
	if (function_exists("get_setting_value")) {
		$tmp_dir  = get_setting_value($settings, "tmp_dir", ".");
	}
	$tmp_file = tempnam($tmp_dir,'gif');
	if (!$tmp_file) {
		$this->Error("Unable to create a temporary file");
	}
	if (!imagepng($im,$tmp_file)) {
		$this->Error("Error while saving to temporary file");
	}
	imagedestroy($im);
	$info = $this->_parsepng($tmp_file);
	unlink($tmp_file);

	return $info;
}


function _freadint($f)
{
	//Read a 4-byte integer from file
	$a=unpack('Ni',fread($f,4));
	return $a['i'];
}

function _textstring($s)
{
	//Format a text string
	return '('.$this->_escape($s).')';
}

function _escape($s)
{
	//Add \ before \, ( and )
	//return str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$s)));
	// Escape special characters
	return str_replace(array('\\','(',')',"\r"), array('\\\\','\\(','\\)','\\r'), $s);
}

	function _putstream($s)
	{
		$this->_out('stream');
		$this->_out($s);
		$this->_out('endstream');
	}

	function _out($s)
	{
		//Add a line to the document
		if($this->state==2) {
			$this->pages[$this->page].=$s."\n";
		} else {
			$this->buffer.=$s."\n";
		}
	}

	function arrUTF8ToUTF16BE($unicode, $setbom=false) {
		$outstr = ''; // string to be returned
		if ($setbom) {
			$outstr .= "\xFE\xFF"; // Byte Order Mark (BOM)
		}
		foreach ($unicode as $char) {
			if ($char == 0x200b) {
				// skip Unicode Character 'ZERO WIDTH SPACE' (DEC:8203, U+200B)
			} elseif ($char == 0xFFFD) {
				$outstr .= "\xFF\xFD"; // replacement character
			} elseif ($char < 0x10000) {
				$outstr .= chr($char >> 0x08);
				$outstr .= chr($char & 0xFF);
			} else {
				$char -= 0x10000;
				$w1 = 0xD800 | ($char >> 0x0a);
				$w2 = 0xDC00 | ($char & 0x3FF);
				$outstr .= chr($w1 >> 0x08);
				$outstr .= chr($w1 & 0xFF);
				$outstr .= chr($w2 >> 0x08);
				$outstr .= chr($w2 & 0xFF);
			}
		}
		return $outstr;
	}

	function strUTF8ToUTF16BE($str, $setbom=true) {
		$unicode = $this->UTF8StringToArray($str); // array of UTF-8 unicode values
		$outstr = $this->arrUTF8ToUTF16BE($unicode, $setbom);
		return $outstr;
	}

	// Converts UTF-8 strings to codepoints array
	function UTF8StringToArray($str) {
		$out = array();
		$len = strlen($str);
		for ($i = 0; $i < $len; $i++) {
			$uni = -1;
      $h = ord($str[$i]);
			if ( $h <= 0x7F )
				$uni = $h;
			elseif ( $h >= 0xC2 ) {
				if ( ($h <= 0xDF) && ($i < $len -1) )
					$uni = ($h & 0x1F) << 6 | (ord($str[++$i]) & 0x3F);
				elseif ( ($h <= 0xEF) && ($i < $len -2) )
					$uni = ($h & 0x0F) << 12 | (ord($str[++$i]) & 0x3F) << 6
					| (ord($str[++$i]) & 0x3F);
				elseif ( ($h <= 0xF4) && ($i < $len -3) )
					$uni = ($h & 0x0F) << 18 | (ord($str[++$i]) & 0x3F) << 12
                                       | (ord($str[++$i]) & 0x3F) << 6
                                       | (ord($str[++$i]) & 0x3F);
      }
			if ($uni >= 0) {
				$out[] = $uni;
			}
		}
		return $out;
	}
}


/*******************************************************************************
* TTFontFile class                                                             *
*                                                                              *
* This class is based on The ReportLab Open Source PDF library                 *
* written in Python - http://www.reportlab.com/software/opensource/            *
* together with ideas from the OpenOffice source code and others.              *
*                                                                              *
* Version:  1.04                                                               *
* Date:     2011-09-18                                                         *
* Author:   Ian Back <ianb@bpm1.com>                                           *
* License:  LGPL                                                               *
* Copyright (c) Ian Back, 2010                                                 *
* This header must be retained in any redistribution or                        *
* modification of the file.                                                    *
* Modified: 2020-06-10 by Viart LLC                                            *
*                                                                              *
*******************************************************************************/

// Define the value used in the "head" table of a created TTF file
// 0x74727565 "true" for Mac
// 0x00010000 for Windows
// Either seems to work for a font embedded in a PDF file
// when read by Adobe Reader on a Windows PC(!)
define("_TTF_MAC_HEADER", false);

// TrueType Font Glyph operators
define("GF_WORDS",(1 << 0));
define("GF_SCALE",(1 << 3));
define("GF_MORE",(1 << 5));
define("GF_XYSCALE",(1 << 6));
define("GF_TWOBYTWO",(1 << 7));

class TTFontFile {

var $maxUni;
var $_pos;
var $numTables;
var $searchRange;
var $entrySelector;
var $rangeShift;
var $tables;
var $otables;
var $filename;
var $fh;
var $hmetrics;
var $glyphPos;
var $charToGlyph;
var $ascent;
var $descent;
var $name;
var $familyName;
var $styleName;
var $fullName;
var $uniqueFontID;
var $unitsPerEm;
var $bbox;
var $capHeight;
var $stemV;
var $italicAngle;
var $flags;
var $underlinePosition;
var $underlineThickness;
var $charWidths; // save here width for every symbol by char unicode
var $defaultWidth;
var $maxStrLenRead;

	public function __construct() {
		$this->maxStrLenRead = 200000;	// Maximum size of glyf table to read in as string (otherwise reads each glyph from file)
	}


	function getMetrics($file) {
		$this->filename = $file;
		$this->fh = fopen($file,'rb') or die('Can\'t open file ' . $file);
		$this->_pos = 0;
		$this->charWidths = array();
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->TTCFonts = array();
		$this->version = $version = $this->read_ulong();
		if ($version==0x4F54544F) 
			die("Postscript outlines are not supported");
		if ($version==0x74746366) 
			die("ERROR - TrueType Fonts Collections not supported");
		if (!in_array($version, array(0x00010000,0x74727565)))
			die("Not a TrueType font: version=".$version);
		$this->readTableDirectory();
		$this->extractInfo();
		fclose($this->fh);
	}


	function readTableDirectory() {
	    $this->numTables = $this->read_ushort();
            $this->searchRange = $this->read_ushort();
            $this->entrySelector = $this->read_ushort();
            $this->rangeShift = $this->read_ushort();
            $this->tables = array();	
            for ($i=0;$i<$this->numTables;$i++) {
                $record = array();
                $record['tag'] = $this->read_tag();
                $record['checksum'] = array($this->read_ushort(),$this->read_ushort());
                $record['offset'] = $this->read_ulong();
                $record['length'] = $this->read_ulong();
                $this->tables[$record['tag']] = $record;
		}
	}


	function sub32($x, $y) {
		$xlo = $x[1];
		$xhi = $x[0];
		$ylo = $y[1];
		$yhi = $y[0];
		if ($ylo > $xlo) { $xlo += 1 << 16; $yhi += 1; }
		$reslo = $xlo-$ylo;
		if ($yhi > $xhi) { $xhi += 1 << 16;  }
		$reshi = $xhi-$yhi;
		$reshi = $reshi & 0xFFFF;
		return array($reshi, $reslo);
	}

	function calcChecksum($data)  {
		if (strlen($data) % 4) { $data .= str_repeat("\0",(4-(strlen($data) % 4))); }
		$hi=0x0000;
		$lo=0x0000;
		for($i=0;$i<strlen($data);$i+=4) {
			$hi += (ord($data[$i])<<8) + ord($data[$i+1]);
			$lo += (ord($data[$i+2])<<8) + ord($data[$i+3]);
			$hi += $lo >> 16;
			$lo = $lo & 0xFFFF;
			$hi = $hi & 0xFFFF;
		}
		return array($hi, $lo);
	}

	function get_table_pos($tag) {
		$offset = $this->tables[$tag]['offset'];
		$length = $this->tables[$tag]['length'];
		return array($offset, $length);
	}

	function seek($pos) {
		$this->_pos = $pos;
		fseek($this->fh,$this->_pos);
	}

	function skip($delta) {
		$this->_pos = $this->_pos + $delta;
		fseek($this->fh,$this->_pos);
	}

	function seek_table($tag, $offset_in_table = 0) {
		$tpos = $this->get_table_pos($tag);
		$this->_pos = $tpos[0] + $offset_in_table;
		fseek($this->fh, $this->_pos);
		return $this->_pos;
	}

	function read_tag() {
		$this->_pos += 4;
		return fread($this->fh,4);
	}

	function read_short() {
		$this->_pos += 2;
		$s = fread($this->fh,2);
		$a = (ord($s[0])<<8) + ord($s[1]);
		if ($a & (1 << 15) ) { $a = ($a - (1 << 16)) ; }
		return $a;
	}

	function unpack_short($s) {
		$a = (ord($s[0])<<8) + ord($s[1]);
		if ($a & (1 << 15) ) { 
			$a = ($a - (1 << 16)); 
		}
		return $a;
	}

	function read_ushort() {
		$this->_pos += 2;
		$s = fread($this->fh,2);
		return (ord($s[0])<<8) + ord($s[1]);
	}

	function read_ulong() {
		$this->_pos += 4;
		$s = fread($this->fh,4);
		// if large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function get_ushort($pos) {
		fseek($this->fh,$pos);
		$s = fread($this->fh,2);
		return (ord($s[0])<<8) + ord($s[1]);
	}

	function get_ulong($pos) {
		fseek($this->fh,$pos);
		$s = fread($this->fh,4);
		// iF large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function pack_short($val) {
		if ($val<0) { 
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		return pack("n",$val); 
	}

	function splice($stream, $offset, $value) {
		return substr($stream,0,$offset) . $value . substr($stream,$offset+strlen($value));
	}

	function _set_ushort($stream, $offset, $value) {
		$up = pack("n", $value);
		return $this->splice($stream, $offset, $up);
	}

	function _set_short($stream, $offset, $val) {
		if ($val<0) { 
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		$up = pack("n",$val); 
		return $this->splice($stream, $offset, $up);
	}

	function get_chunk($pos, $length) {
		fseek($this->fh,$pos);
		if ($length <1) { return ''; }
		return (fread($this->fh,$length));
	}

	function get_table($tag) {
		list($pos, $length) = $this->get_table_pos($tag);
		if ($length == 0) { die('Truetype font ('.$this->filename.'): error reading table: '.$tag); }
		fseek($this->fh,$pos);
		return (fread($this->fh,$length));
	}

	function add($tag, $data) {
		if ($tag == 'head') {
			$data = $this->splice($data, 8, "\0\0\0\0");
		}
		$this->otables[$tag] = $data;
	}



/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////

	function extractInfo() {
		///////////////////////////////////
		// name - Naming table
		///////////////////////////////////
			$this->sFamilyClass = 0;
			$this->sFamilySubClass = 0;

			$name_offset = $this->seek_table("name");
			$format = $this->read_ushort();
			if ($format != 0)
				die("Unknown name table format ".$format);
			$numRecords = $this->read_ushort();
			$string_data_offset = $name_offset + $this->read_ushort();
			$names = array(1=>'',2=>'',3=>'',4=>'',6=>'');
			$K = array_keys($names);
			$nameCount = count($names);
			for ($i=0;$i<$numRecords; $i++) {
				$platformId = $this->read_ushort();
				$encodingId = $this->read_ushort();
				$languageId = $this->read_ushort();
				$nameId = $this->read_ushort();
				$length = $this->read_ushort();
				$offset = $this->read_ushort();
				if (!in_array($nameId,$K)) continue;
				$N = '';
				if ($platformId == 3 && $encodingId == 1 && $languageId == 0x409) { // Microsoft, Unicode, US English, PS Name
					$opos = $this->_pos;
					$this->seek($string_data_offset + $offset);
					if ($length % 2 != 0)
						die("PostScript name is UTF-16BE string of odd length");
					$length /= 2;
					$N = '';
					while ($length > 0) {
						$char = $this->read_ushort();
						$N .= (chr($char));
						$length -= 1;
					}
					$this->_pos = $opos;
					$this->seek($opos);
				}
				else if ($platformId == 1 && $encodingId == 0 && $languageId == 0) { // Macintosh, Roman, English, PS Name
					$opos = $this->_pos;
					$N = $this->get_chunk($string_data_offset + $offset, $length);
					$this->_pos = $opos;
					$this->seek($opos);
				}
				if ($N && $names[$nameId]=='') {
					$names[$nameId] = $N;
					$nameCount -= 1;
					if ($nameCount==0) break;
				}
			}
			if ($names[6])
				$psName = $names[6];
			else if ($names[4])
				$psName = preg_replace('/ /','-',$names[4]);
			else if ($names[1])
				$psName = preg_replace('/ /','-',$names[1]);
			else
				$psName = '';
			if (!$psName)
				die("Could not find PostScript font name");
			$this->name = $psName;
			if ($names[1]) { $this->familyName = $names[1]; } else { $this->familyName = $psName; }
			if ($names[2]) { $this->styleName = $names[2]; } else { $this->styleName = 'Regular'; }
			if ($names[4]) { $this->fullName = $names[4]; } else { $this->fullName = $psName; }
			if ($names[3]) { $this->uniqueFontID = $names[3]; } else { $this->uniqueFontID = $psName; }
			if ($names[6]) { $this->fullName = $names[6]; }

		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		$this->skip(18); 
		$this->unitsPerEm = $unitsPerEm = $this->read_ushort();
		$scale = 1000 / $unitsPerEm;

		$this->skip(16);
		$xMin = $this->read_short();
		$yMin = $this->read_short();
		$xMax = $this->read_short();
		$yMax = $this->read_short();
		$this->bbox = array(($xMin*$scale), ($yMin*$scale), ($xMax*$scale), ($yMax*$scale));
		$this->skip(3*2);
		$indexToLocFormat = $this->read_ushort();
		$glyphDataFormat = $this->read_ushort();
		if ($glyphDataFormat != 0)
			die('Unknown glyph data format '.$glyphDataFormat);

		///////////////////////////////////
		// hhea metrics table
		///////////////////////////////////
		// ttf2t1 seems to use this value rather than the one in OS/2 - so put in for compatibility
		if (isset($this->tables["hhea"])) {
			$this->seek_table("hhea");
			$this->skip(4);
			$hheaAscender = $this->read_short();
			$hheaDescender = $this->read_short();
			$this->ascent = ($hheaAscender *$scale);
			$this->descent = ($hheaDescender *$scale);
		}

		///////////////////////////////////
		// OS/2 - OS/2 and Windows metrics table
		///////////////////////////////////
		if (isset($this->tables["OS/2"])) {
			$this->seek_table("OS/2");
			$version = $this->read_ushort();
			$this->skip(2);
			$usWeightClass = $this->read_ushort();
			$this->skip(2);
			$fsType = $this->read_ushort();
			if ($fsType == 0x0002 || ($fsType & 0x0300) != 0) {
				die('ERROR - Font file '.$this->filename.' cannot be embedded due to copyright restrictions.');
				$this->restrictedUse = true;
			}
			$this->skip(20);
			$sF = $this->read_short();
			$this->sFamilyClass = ($sF >> 8);
			$this->sFamilySubClass = ($sF & 0xFF);
			$this->_pos += 10;  //PANOSE = 10 byte length
			$panose = fread($this->fh,10);
			$this->skip(26);
			$sTypoAscender = $this->read_short();
			$sTypoDescender = $this->read_short();
			if (!$this->ascent) $this->ascent = ($sTypoAscender*$scale);
			if (!$this->descent) $this->descent = ($sTypoDescender*$scale);
			if ($version > 1) {
				$this->skip(16);
				$sCapHeight = $this->read_short();
				$this->capHeight = ($sCapHeight*$scale);
			}
			else {
				$this->capHeight = $this->ascent;
			}
		}
		else {
			$usWeightClass = 500;
			if (!$this->ascent) $this->ascent = ($yMax*$scale);
			if (!$this->descent) $this->descent = ($yMin*$scale);
			$this->capHeight = $this->ascent;
		}
		$this->stemV = 50 + intval(pow(($usWeightClass / 65.0),2));

		///////////////////////////////////
		// post - PostScript table
		///////////////////////////////////
		$this->seek_table("post");
		$this->skip(4); 
		$this->italicAngle = $this->read_short() + $this->read_ushort() / 65536.0;
		$this->underlinePosition = $this->read_short() * $scale;
		$this->underlineThickness = $this->read_short() * $scale;
		$isFixedPitch = $this->read_ulong();

		$this->flags = 4;

		if ($this->italicAngle!= 0) 
			$this->flags = $this->flags | 64;
		if ($usWeightClass >= 600)
			$this->flags = $this->flags | 262144;
		if ($isFixedPitch)
			$this->flags = $this->flags | 1;

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		$this->skip(32); 
		$metricDataFormat = $this->read_ushort();
		if ($metricDataFormat != 0)
			die('Unknown horizontal metric data format '.$metricDataFormat);
		$numberOfHMetrics = $this->read_ushort();
		if ($numberOfHMetrics == 0) 
			die('Number of horizontal metrics is 0');

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		$this->skip(4); 
		$numGlyphs = $this->read_ushort();


		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if (($platformID == 3 && $encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					if (!$unicode_cmap_offset) $unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos );
		}
		if (!$unicode_cmap_offset)
			die('Font ('.$this->filename .') does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');


		$glyphToChar = array();
		$charToGlyph = array();
		$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph );

		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$this->getHMTX($numberOfHMetrics, $numGlyphs, $glyphToChar, $scale);
	}


	function makeSubset($file, &$subset) {
		$this->filename = $file;
		$this->fh = fopen($file ,'rb') or die('Can\'t open file ' . $file);
		$this->_pos = 0;
		$this->charWidths = array();
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->skip(4);
		$this->maxUni = 0;
		$this->readTableDirectory();


		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		$this->skip(50); 
		$indexToLocFormat = $this->read_ushort();
		$glyphDataFormat = $this->read_ushort();

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		$this->skip(32); 
		$metricDataFormat = $this->read_ushort();
		$orignHmetrics = $numberOfHMetrics = $this->read_ushort();

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		$this->skip(4);
		$numGlyphs = $this->read_ushort();


		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i=0;$i<$cmapTableCount;$i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if (($platformID == 3 && $encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos );
		}

		if (!$unicode_cmap_offset)
			die('Font ('.$this->filename .') does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');


		$glyphToChar = array();
		$charToGlyph = array();
		$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph );

		$this->charToGlyph = $charToGlyph;

		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$scale = 1;	// not used
		$this->getHMTX($numberOfHMetrics, $numGlyphs, $glyphToChar, $scale);

		///////////////////////////////////
		// loca - Index to location
		///////////////////////////////////
		$this->getLOCA($indexToLocFormat, $numGlyphs);

		$subsetglyphs = array(0=>0); 
		$subsetCharToGlyph = array();
		foreach($subset AS $code) {
			if (isset($this->charToGlyph[$code])) {
				$subsetglyphs[$this->charToGlyph[$code]] = $code;	// Old Glyph ID => Unicode
				$subsetCharToGlyph[$code] = $this->charToGlyph[$code];	// Unicode to old GlyphID

			}
			$this->maxUni = max($this->maxUni, $code);
		}

		list($start,$dummy) = $this->get_table_pos('glyf');

		$glyphSet = array();
		ksort($subsetglyphs);
		$n = 0;
		$fsLastCharIndex = 0;	// maximum Unicode index (character code) in this font, according to the cmap subtable for platform ID 3 and platform- specific encoding ID 0 or 1.
		foreach($subsetglyphs AS $originalGlyphIdx => $uni) {
			$fsLastCharIndex = max($fsLastCharIndex , $uni);
			$glyphSet[$originalGlyphIdx] = $n;	// old glyphID to new glyphID
			$n++;
		}

		ksort($subsetCharToGlyph);
		foreach($subsetCharToGlyph AS $uni => $originalGlyphIdx) {
			$codeToGlyph[$uni] = $glyphSet[$originalGlyphIdx] ;
		}
		$this->codeToGlyph = $codeToGlyph;

		ksort($subsetglyphs);
		foreach($subsetglyphs AS $originalGlyphIdx => $uni) {
			$this->getGlyphs($originalGlyphIdx, $start, $glyphSet, $subsetglyphs);
		}

		$numGlyphs = $numberOfHMetrics = count($subsetglyphs );

		//tables copied from the original
		$tags = array ('name');
		foreach($tags AS $tag) { $this->add($tag, $this->get_table($tag)); }
		$tags = array ('cvt ', 'fpgm', 'prep', 'gasp');
		foreach($tags AS $tag) {
			if (isset($this->tables[$tag])) { $this->add($tag, $this->get_table($tag)); }
		}

		// post - PostScript
		$opost = $this->get_table('post');
		$post = "\x00\x03\x00\x00" . substr($opost,4,12) . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
		$this->add('post', $post);

		// Sort CID2GID map into segments of contiguous codes
		ksort($codeToGlyph);
		unset($codeToGlyph[0]);
		//unset($codeToGlyph[65535]);
		$rangeid = 0;
		$range = array();
		$prevcid = -2;
		$prevglidx = -1;
		// for each character
		foreach ($codeToGlyph as $cid => $glidx) {
			if ($cid == ($prevcid + 1) && $glidx == ($prevglidx + 1)) {
				$range[$rangeid][] = $glidx;
			} else {
				// new range
				$rangeid = $cid;
				$range[$rangeid] = array();
				$range[$rangeid][] = $glidx;
			}
			$prevcid = $cid;
			$prevglidx = $glidx;
		}

		// cmap - Character to glyph mapping - Format 4 (MS / )
		$segCount = count($range) + 1;	// + 1 Last segment has missing character 0xFFFF
		$searchRange = 1;
		$entrySelector = 0;
		while ($searchRange * 2 <= $segCount ) {
			$searchRange = $searchRange * 2;
			$entrySelector = $entrySelector + 1;
		}
		$searchRange = $searchRange * 2;
		$rangeShift = $segCount * 2 - $searchRange;
		$length = 16 + (8*$segCount ) + ($numGlyphs+1);
		$cmap = array(0, 1,		// Index : version, number of encoding subtables
			3, 1,				// Encoding Subtable : platform (MS=3), encoding (Unicode)
			0, 12,			// Encoding Subtable : offset (hi,lo)
			4, $length, 0, 		// Format 4 Mapping subtable: format, length, language
			$segCount*2,
			$searchRange,
			$entrySelector,
			$rangeShift);

		// endCode(s)
		foreach($range AS $start=>$subrange) {
			$endCode = $start + (count($subrange)-1);
			$cmap[] = $endCode;	// endCode(s)
		}
		$cmap[] =	0xFFFF;	// endCode of last Segment
		$cmap[] =	0;	// reservedPad

		// startCode(s)
		foreach($range AS $start=>$subrange) {
			$cmap[] = $start;	// startCode(s)
		}
		$cmap[] =	0xFFFF;	// startCode of last Segment
		// idDelta(s) 
		foreach($range AS $start=>$subrange) {
			$idDelta = -($start-$subrange[0]);
			$n += count($subrange);
			$cmap[] = $idDelta;	// idDelta(s)
		}
		$cmap[] =	1;	// idDelta of last Segment
		// idRangeOffset(s) 
		foreach($range AS $subrange) {
			$cmap[] = 0;	// idRangeOffset[segCount]  	Offset in bytes to glyph indexArray, or 0

		}
		$cmap[] =	0;	// idRangeOffset of last Segment
		foreach($range AS $subrange) {
			foreach($subrange AS $glidx) {
				$cmap[] = $glidx;
			}
		}
		$cmap[] = 0;	// Mapping for last character
		$cmapstr = '';
		foreach($cmap AS $cm) { $cmapstr .= pack("n",$cm); }
		$this->add('cmap', $cmapstr);


		// glyf - Glyph data
		list($glyfOffset,$glyfLength) = $this->get_table_pos('glyf');
		if ($glyfLength < $this->maxStrLenRead) {
			$glyphData = $this->get_table('glyf');
		}

		$offsets = array();
		$glyf = '';
		$pos = 0;

		$hmtxstr = '';
		$xMinT = 0;
		$yMinT = 0;
		$xMaxT = 0;
		$yMaxT = 0;
		$advanceWidthMax = 0;
		$minLeftSideBearing = 0;
		$minRightSideBearing = 0;
		$xMaxExtent = 0;
		$maxPoints = 0;			// points in non-compound glyph
		$maxContours = 0;			// contours in non-compound glyph
		$maxComponentPoints = 0;	// points in compound glyph
		$maxComponentContours = 0;	// contours in compound glyph
		$maxComponentElements = 0;	// number of glyphs referenced at top level
		$maxComponentDepth = 0;		// levels of recursion, set to 0 if font has only simple glyphs
		$this->glyphdata = array();

		foreach($subsetglyphs AS $originalGlyphIdx => $uni) {
			// hmtx - Horizontal Metrics
			$hm = $this->getHMetric($orignHmetrics, $originalGlyphIdx);	
			$hmtxstr .= $hm;

			$offsets[] = $pos;
			$glyphPos = $this->glyphPos[$originalGlyphIdx];
			$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
			if ($glyfLength < $this->maxStrLenRead) {
				$data = substr($glyphData,$glyphPos,$glyphLen);
			}
			else {
				if ($glyphLen > 0) $data = $this->get_chunk($glyfOffset+$glyphPos,$glyphLen);
				else $data = '';
			}

			if ($glyphLen > 0) {
				$up = unpack("n", substr($data,0,2));
			}

			if ($glyphLen > 2 && ($up[1] & (1 << 15)) ) {	// If number of contours <= -1 i.e. composiste glyph
				$pos_in_glyph = 10;
				$flags = GF_MORE;
				$nComponentElements = 0;
				while ($flags & GF_MORE) {
					$nComponentElements += 1;	// number of glyphs referenced at top level
					$up = unpack("n", substr($data,$pos_in_glyph,2));
					$flags = $up[1];
					$up = unpack("n", substr($data,$pos_in_glyph+2,2));
					$glyphIdx = $up[1];
					$this->glyphdata[$originalGlyphIdx]['compGlyphs'][] = $glyphIdx;
					$data = $this->_set_ushort($data, $pos_in_glyph + 2, $glyphSet[$glyphIdx]);
					$pos_in_glyph += 4;
					if ($flags & GF_WORDS) { $pos_in_glyph += 4; }
					else { $pos_in_glyph += 2; }
					if ($flags & GF_SCALE) { $pos_in_glyph += 2; }
					else if ($flags & GF_XYSCALE) { $pos_in_glyph += 4; }
					else if ($flags & GF_TWOBYTWO) { $pos_in_glyph += 8; }
				}
				$maxComponentElements = max($maxComponentElements, $nComponentElements);
			}

			$glyf .= $data;
			$pos += $glyphLen;
			if ($pos % 4 != 0) {
				$padding = 4 - ($pos % 4);
				$glyf .= str_repeat("\0",$padding);
				$pos += $padding;
			}
		}

		$offsets[] = $pos;
		$this->add('glyf', $glyf);

		// hmtx - Horizontal Metrics
		$this->add('hmtx', $hmtxstr);

		// loca - Index to location
		$locastr = '';
		if ((($pos + 1) >> 1) > 0xFFFF) {
			$indexToLocFormat = 1;        // long format
			foreach($offsets AS $offset) { $locastr .= pack("N",$offset); }
		}
		else {
			$indexToLocFormat = 0;        // short format
			foreach($offsets AS $offset) { $locastr .= pack("n",($offset/2)); }
		}
		$this->add('loca', $locastr);

		// head - Font header
		$head = $this->get_table('head');
		$head = $this->_set_ushort($head, 50, $indexToLocFormat);
		$this->add('head', $head);


		// hhea - Horizontal Header
		$hhea = $this->get_table('hhea');
		$hhea = $this->_set_ushort($hhea, 34, $numberOfHMetrics);
		$this->add('hhea', $hhea);

		// maxp - Maximum Profile
		$maxp = $this->get_table('maxp');
		$maxp = $this->_set_ushort($maxp, 4, $numGlyphs);
		$this->add('maxp', $maxp);


		// OS/2 - OS/2
		$os2 = $this->get_table('OS/2');
		$this->add('OS/2', $os2 );

		fclose($this->fh);

		// Put the TTF file together
		$stm = '';
		$this->endTTFile($stm);
		return $stm ;
	}

	//////////////////////////////////////////////////////////////////////////////////
	// Recursively get composite glyph data
	function getGlyphData($originalGlyphIdx, &$maxdepth, &$depth, &$points, &$contours) {
		$depth++;
		$maxdepth = max($maxdepth, $depth);
		if (count($this->glyphdata[$originalGlyphIdx]['compGlyphs'])) {
			foreach($this->glyphdata[$originalGlyphIdx]['compGlyphs'] AS $glyphIdx) {
				$this->getGlyphData($glyphIdx, $maxdepth, $depth, $points, $contours);
			}
		}
		else if (($this->glyphdata[$originalGlyphIdx]['nContours'] > 0) && $depth > 0) {	// simple
			$contours += $this->glyphdata[$originalGlyphIdx]['nContours'];
			$points += $this->glyphdata[$originalGlyphIdx]['nPoints'];
		}
		$depth--;
	}


	//////////////////////////////////////////////////////////////////////////////////
	// Recursively get composite glyphs
	function getGlyphs($originalGlyphIdx, &$start, &$glyphSet, &$subsetglyphs) {
		$glyphPos = $this->glyphPos[$originalGlyphIdx];
		$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
		if (!$glyphLen) { 
			return;
		}
		$this->seek($start + $glyphPos);
		$numberOfContours = $this->read_short();
		if ($numberOfContours < 0) {
			$this->skip(8);
			$flags = GF_MORE;
			while ($flags & GF_MORE) {
				$flags = $this->read_ushort();
				$glyphIdx = $this->read_ushort();
				if (!isset($glyphSet[$glyphIdx])) {
					$glyphSet[$glyphIdx] = count($subsetglyphs);	// old glyphID to new glyphID
					$subsetglyphs[$glyphIdx] = true;
				}
				$savepos = ftell($this->fh);
				$this->getGlyphs($glyphIdx, $start, $glyphSet, $subsetglyphs);
				$this->seek($savepos);
				if ($flags & GF_WORDS)
					$this->skip(4);
				else
					$this->skip(2);
				if ($flags & GF_SCALE)
					$this->skip(2);
				else if ($flags & GF_XYSCALE)
					$this->skip(4);
				else if ($flags & GF_TWOBYTWO)
					$this->skip(8);
			}
		}
	}

	//////////////////////////////////////////////////////////////////////////////////

	function getHMTX($numberOfHMetrics, $numGlyphs, &$glyphToChar, $scale) {
		$start = $this->seek_table("hmtx");
		$aw = 0;
		// add width to char array
		$this->charWidths = array();

		$nCharWidths = 0;
		if (($numberOfHMetrics*4) < $this->maxStrLenRead) {
			$data = $this->get_chunk($start,($numberOfHMetrics*4));
			$arr = unpack("n*", $data);
		} else { 
			$this->seek($start); 
		}
		for( $glyph=0; $glyph<$numberOfHMetrics; $glyph++) {
			if (($numberOfHMetrics*4) < $this->maxStrLenRead) {
				$aw = $arr[($glyph*2)+1];
			} else {
				$aw = $this->read_ushort();
				$lsb = $this->read_ushort();
			}

			if (isset($glyphToChar[$glyph]) || $glyph == 0) {
				if ($aw >= (1 << 15) ) { $aw = 0; }	// 1.03 Some (arabic) fonts have -ve values for width
					// although should be unsigned value - comes out as e.g. 65108 (intended -50)
				if ($glyph == 0) {
					$this->defaultWidth = $scale*$aw;
					continue;
				}
				foreach($glyphToChar[$glyph] AS $char) {

					if ($char != 0 && $char != 65535) {
 						$w = intval(round($scale*$aw)); // width could be zero for some special symbols like Thai 3633 symbol
						if ($char < 196608) {
							$this->charWidths[$char] = $w;
							$nCharWidths++;
						}
					}
				}
			}
		}
		$data = $this->get_chunk(($start+$numberOfHMetrics*4),($numGlyphs*2));
		$arr = unpack("n*", $data);
		$diff = $numGlyphs-$numberOfHMetrics;
		for( $pos=0; $pos<$diff; $pos++) {
			$glyph = $pos + $numberOfHMetrics;
			if (isset($glyphToChar[$glyph])) {
				foreach($glyphToChar[$glyph] AS $char) {
					if ($char != 0 && $char != 65535) {
						$w = intval(round($scale*$aw));
						if ($char < 196608) {
							$this->charWidths[$char] = $w;
							$nCharWidths++;
						}
					}
				}
			}
		}
	}

	function getHMetric($numberOfHMetrics, $gid) {
		$start = $this->seek_table("hmtx");
		if ($gid < $numberOfHMetrics) {
			$this->seek($start+($gid*4));
			$hm = fread($this->fh,4);
		}
		else {
			$this->seek($start+(($numberOfHMetrics-1)*4));
			$hm = fread($this->fh,2);
			$this->seek($start+($numberOfHMetrics*2)+($gid*2));
			$hm .= fread($this->fh,2);
		}
		return $hm;
	}

	function getLOCA($indexToLocFormat, $numGlyphs) {
		$start = $this->seek_table('loca');
		$this->glyphPos = array();
		if ($indexToLocFormat == 0) {
			$data = $this->get_chunk($start,($numGlyphs*2)+2);
			$arr = unpack("n*", $data);
			for ($n=0; $n<=$numGlyphs; $n++) {
				$this->glyphPos[] = ($arr[$n+1] * 2);
			}
		}
		else if ($indexToLocFormat == 1) {
			$data = $this->get_chunk($start,($numGlyphs*4)+4);
			$arr = unpack("N*", $data);
			for ($n=0; $n<=$numGlyphs; $n++) {
				$this->glyphPos[] = ($arr[$n+1]);
			}
		}
		else 
			die('Unknown location table format '.$indexToLocFormat);
	}


	// CMAP Format 4
	function getCMAP4($unicode_cmap_offset, &$glyphToChar, &$charToGlyph ) {
		$this->maxUniChar = 0;
		$this->seek($unicode_cmap_offset + 2);
		$length = $this->read_ushort();
		$limit = $unicode_cmap_offset + $length;
		$this->skip(2);

		$segCount = $this->read_ushort() / 2;
		$this->skip(6);
		$endCount = array();
		for($i=0; $i<$segCount; $i++) { $endCount[] = $this->read_ushort(); }
		$this->skip(2);
		$startCount = array();
		for($i=0; $i<$segCount; $i++) { $startCount[] = $this->read_ushort(); }
		$idDelta = array();
		for($i=0; $i<$segCount; $i++) { $idDelta[] = $this->read_short(); }		// ???? was unsigned short
		$idRangeOffset_start = $this->_pos;
		$idRangeOffset = array();
		for($i=0; $i<$segCount; $i++) { $idRangeOffset[] = $this->read_ushort(); }

		for ($n=0;$n<$segCount;$n++) {
			$endpoint = ($endCount[$n] + 1);
			for ($unichar=$startCount[$n];$unichar<$endpoint;$unichar++) {
				if ($idRangeOffset[$n] == 0)
					$glyph = ($unichar + $idDelta[$n]) & 0xFFFF;
				else {
					$offset = ($unichar - $startCount[$n]) * 2 + $idRangeOffset[$n];
					$offset = $idRangeOffset_start + 2 * $n + $offset;
					if ($offset >= $limit)
						$glyph = 0;
					else {
						$glyph = $this->get_ushort($offset);
						if ($glyph != 0)
						   $glyph = ($glyph + $idDelta[$n]) & 0xFFFF;
					}
				}
				$charToGlyph[$unichar] = $glyph;
				if ($unichar < 196608) { $this->maxUniChar = max($unichar,$this->maxUniChar); }
				$glyphToChar[$glyph][] = $unichar;
			}
		}
	}


		// Put the TTF file together
	function endTTFile(&$stm) {
		$stm = '';
		$numTables = count($this->otables);
		$searchRange = 1;
		$entrySelector = 0;
		while ($searchRange * 2 <= $numTables) {
			$searchRange = $searchRange * 2;
			$entrySelector = $entrySelector + 1;
		}
		$searchRange = $searchRange * 16;
		$rangeShift = $numTables * 16 - $searchRange;

		// Header
		if (_TTF_MAC_HEADER) {
			$stm .= (pack("Nnnnn", 0x74727565, $numTables, $searchRange, $entrySelector, $rangeShift));	// Mac
		} else {
			$stm .= (pack("Nnnnn", 0x00010000 , $numTables, $searchRange, $entrySelector, $rangeShift));	// Windows
		}

		// Table directory
		$tables = $this->otables;

		ksort ($tables); 
		$offset = 12 + $numTables * 16;
		foreach ($tables AS $tag=>$data) {
			if ($tag == 'head') { $head_start = $offset; }
			$stm .= $tag;
			$checksum = $this->calcChecksum($data);
			$stm .= pack("nn", $checksum[0],$checksum[1]);
			$stm .= pack("NN", $offset, strlen($data));
			$paddedLength = (strlen($data)+3)&~3;
			$offset = $offset + $paddedLength;
		}

		// Table data
		foreach ($tables AS $tag=>$data) {
			$data .= "\0\0\0";
			$stm .= substr($data,0,(strlen($data)&~3));
		}

		$checksum = $this->calcChecksum($stm);
		$checksum = $this->sub32(array(0xB1B0,0xAFBA), $checksum);
		$chk = pack("nn", $checksum[0],$checksum[1]);
		$stm = $this->splice($stm,($head_start + 8),$chk);
		return $stm ;
	}
}


class ASCII85 {

 var $width = 72; // Line width for splitting
 var $pos = 0; // Position within the line
 var $tuple = "0"; // Unsigned long being manipulated
 var $count = 0; // Number of bytes being manipulated
 var $out = ""; // Output
 var $pow85; // Power of 85 multiplier
 var $error; // Error
 var $array = array(); // For storing unpacked bytes
 var $i = 1; // Position within byte array

/**
 * Primary encoding method, one argument, the string that is to be encoded
**/
	function encode($string) {
		$this->error = "";
		$this->out = "<~";
		$this->pos = 2;
   
		$array = unpack("C*",$string);
		for($i=1;$i<=count($array);$i++){
			$this->put85($array[$i]);
		}
		if ($this->count > 0) { $this->encode85(false); }
		if ($this->pos + 2 > $this->width) { $this->out.="\n"; }
    $this->out.="~>\n";
		if ($this->error) {
			return $this->error;
		}else{
			return $this->out;
		}
	}

/**
 * Method used to convert an unsigned long to ASCII characters
 * One parameter bool increase the count by one when adding
 * encoded characters to output string
 * @param bool $tru default:true
**/
	function encode85($tru=true) {
		$s = array();
		$i = 5;
		while (--$i >= 0){
			$s[$i] = (int)bcmod($this->tuple,"85");
			$this->tuple = bcdiv($this->tuple,"85");
		}
		$f = $tru ? 1 : 0;
		for($i=0;$i<=$this->count+$f;$i++){
			$this->out .= chr(($s[$i] + ord('!')));
			if ($this->pos++ >= $this->width) {
				$this->pos = 0;
				$this->out.="\n";
			}
		}
	}

/**
 * Method is passed each char of the string to be encoded and adds it
 * to an unsigned long for conversion by encode85
 * @param decimal $c
**/
	function put85($c) {
    switch ($this->count) {
    case 0: $this->tuple = bcadd($this->lshift($c,24),$this->tuple);
			$this->count++;
			break;
    case 1: $this->tuple = bcadd($this->tuple,((string)($c << 16)));
			$this->count++;
			break;
    case 2: $this->tuple = bcadd($this->tuple,((string)($c << 8)));
			$this->count++;
			break;
    case 3:
        $this->tuple = bcadd($this->tuple,((string)$c));
        if ($this->tuple == 0) {
            $this->out.='z';
            if ($this->pos++ >= $this->width) {
                $this->pos = 0;
                $this->out.="\n";
            }
        } else {
            $this->encode85();
        }
        $this->tuple = "0";
        $this->count = 0;
        break;
    }
	}

/**
 * Primary method used to decode an encoded string, one parameter an encoded string
 * Breaks apart string for encoding and returns
 * @param string $string
 * @return string
**/
  function decode($string){
      $this->error = "";
      $this->out = "";
      $this->count = 0;
      $this->pow85 = array((85*85*85*85), (85*85*85), (85*85), 85, 1);
      $string=preg_replace("/^<~/isx","",$string);
      $this->array = str_split($string);
      while($this->i < count($this->array)){
        $this->decode85(current($this->array));
        next($this->array);
        $this->i++;
      }
      if($this->error){
        return $this->error;
      }else{
        return $this->out;
      }
  }
/**
 * Used to pack the output codes, one parameter number of bytes to output
 * @param int $bytes
**/
  function wput($bytes) {
    switch ($bytes) {
    case 4:
        $this->out.=pack("C",$this->rshift($this->tuple,24));
        $this->out.=pack("C",$this->rshift($this->tuple,16));
        $this->out.=pack("C",$this->rshift($this->tuple,8));
        $this->out.=pack("C",((float)$this->tuple));
        break;
    case 3:
        $this->out.=pack("C",$this->rshift($this->tuple,24));
        $this->out.=pack("C",$this->rshift($this->tuple,16));
        $this->out.=pack("C",$this->rshift($this->tuple,8));
        break;
    case 2:
        $this->out.=pack("C",$this->rshift($this->tuple,24));
        $this->out.=pack("C",$this->rshift($this->tuple,16));
        break;
    case 1:
        $this->out.=pack("C",$this->rshift($this->tuple,24));
        break;
    }
    //$this->tuple = "0";
  }
/**
 * Used to decode the chars and add them up in an unsigned long
 * to be encoded, one paramater char to be added
 * @param char $c
**/
  function decode85($c) {
    switch ($c) {
        case 'z':
            if ($this->count != 0) {
                $this->error.="\n: z inside ascii85 5-tuple";
                return;
            }
            $this->out.=pack("C",0x00);
            $this->out.=pack("C",0x00);
            $this->out.=pack("C",0x00);
            $this->out.=pack("C",0x00);
            break;
        case '~':
            $c = next($this->array);
            if ($c == '>') {
                if ($this->count > 0) {
                    $this->count--;
                    $this->tuple = bcadd($this->tuple,$this->pow85[$this->count]);
                    $this->wput($this->count);
                }
                return;
            }
            $this->error.="\n: ~ without > in ascii85 section";
            return;
        case "\n": case "\r": case "\t": case " ":
        case "\0": case "\f": case "\b": case 0177:
            break;
        default:
            //echo (ord($c)-ord('!'))."\n";
            if (ord($c) < ord('!') || ord($c) > ord('u')) {
                $this->error.="\nBad character in ascii85 region: ".current($this->array)." ".$this->i;
                //return;
            }
            $this->tuple = bcadd($this->tuple,bcmul((ord($c)-ord('!')),$this->pow85[$this->count]));
            $this->count++;
            if ($this->count == 5) {
                $this->wput(4);
                $this->count = 0;
                $this->tuple = "0";
            }
            break;
        }
  }
/**
 * Used to allow class to deal with unsigned longs, bitwise left shift
 * Two parameters, number to be shifted, and how much to shift
 * @param int|string $n
 * @param int $b
 * @return string
**/
	function lshift($n,$b){
		for($t=0;$t<$b;$t++){
			$n = bcmul($n,"2");
		}
		return ((string)$n);
	}
/**
 * Used to allow class to deal with unsigned longs, bitwise right shift
 * Two parameters, number to be shifted, and how much to shift
 * @param int $n
 * @param int $b
 * @return int
 */
	function rshift($n,$b){
		for($t=0;$t<$b;$t++){
			$n = bcdiv($n,"2");
		}
		return ((int)$n);
	}
}

function decode_pdf(&$data)
{
	if (preg_match_all("/<<([^>]+)>>\s*stream\s*([^\s].+[^\s])\s*endstream/Uis", $data, $matches)) {
		for($m = 0; $m < count($matches[0]); $m++) {
			$encode = false;
			$title  = $matches[1][$m];
			$stream = $matches[2][$m];
			$plain  = trim($stream);
			if (preg_match("/ASCII85Decode/is", $title)) {
				$encode = true;
				$base85 = new ASCII85();
				$plain  = $base85->decode($plain);
			}
			if (preg_match("/FlateDecode/is", $title)) {
				$encode = true;
				$plain  = gzuncompress($plain);
			}
			if ($encode) {
				$data = str_replace($stream, $plain, $data);
			}
		}
	}
}


