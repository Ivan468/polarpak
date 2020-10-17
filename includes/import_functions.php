<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  import_functions.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


class VA_Import
{
	public $r; // record object for main table import
	public $rr; // additional record for related table import
	public $rc; // special record for order coupons 

	public $data = array(); // imported data 
	public $columns = array(); // columns meta data and their sources for different tables 

	public function __construct($import_type = "")
	{
	}

	public function set_data($data) 
	{
		$this->data = $data;
	}

	public function get_columns($table_name) 
	{
		return isset($this->columns[$table_name]) ? $this->columns[$table_name] : array();
	}

	public function set_columns($table_name, $columns) 
	{
		$this->columns[$table_name] = $columns;
	}

}


/**
 * Import classes
 */
class FilesImportStrategy
{
	private static $_fileType = null;

	public static function getParser($feed_type, $source_file, $file_handler, $delimiter = ""){
		if($feed_type == "csv"){
			self::$_fileType = new CsvDataParser($source_file, $file_handler, $delimiter);
		}
		elseif($feed_type == "xml"){
			self::$_fileType = new XmlDataParser($source_file, $file_handler);
		}
		else{
			throw new Exception("Feed type must be xml or csv");
		}

		return self::$_fileType;
	}
}

abstract class DataParser
{
	/**
	 * @var string
	 */
	protected $data_source = null;

	/**
	 * @var resource
	 */
	protected $file_handler = null;

	/**
	 * @var array
	 */
	protected $header_array = null;

	/**
	 * @var array
	 */
	protected $data_array = array();

	/**
	 * @var array
	 * structured array for json conversion
	 */
	protected $header_array_conv = null;

	/**
	 * @param string $source_file - path to feed
	 * @return void
	 */
	public function __construct($source_file, $file_handler){
		$this->data_source = $source_file;
		$this->file_handler = $file_handler;
	}

	/**
	 * @return path to data source
	 */
	public function getFilePath(){
		return $this->data_source;
	}

	/**
	 * @return array
	 */
	public function getHeaders(){
		return $this->header_array;
	}
	abstract function getFieldsHeaders();
	abstract function getFieldsData();

}

class CsvDataParser extends DataParser
{
	/**
	 * @var string - csv delimiter
	 */
	protected $delimiter = null;

	public function __construct($source_file, $file_handler, $delimiter){
		parent::__construct($source_file, $file_handler);

		$this->delimiter = $delimiter;
	}

	public function getFieldsHeaders(){
		if($this->header_array === null){
			/*rewind to file beginning*/
			rewind($this->file_handler);
			$this->header_array = fgetcsv($this->file_handler, 8192, $this->delimiter);
			$header_data = array();
			foreach ($this->header_array as $id => $column_name) {
				$column_name = trim($column_name); // trim column name and save updated value
				// remove possible utf-8 BOM data on start and quotes symbols
				if (preg_match("/^\xEF\xBB\xBF/", $column_name)) {
					$column_name = preg_replace("/^\xEF\xBB\xBF/", "", $column_name); 
					$column_name = trim($column_name, '"'); 
				}

				$this->header_array[$id] = $column_name;
				if(function_exists("mb_strtolower")) {
					$lowercase_column = trim(mb_strtolower($column_name, "UTF-8"));
				} else {
					$lowercase_column = trim(strtolower($column_name));
				}
				$header_data[$lowercase_column] = array("id" => $id, "title" => $column_name);
			}
			$this->header_array_conv = $header_data;
		}
		return $this->header_array_conv;
	}

	public function getFieldsData(){
		if(count($this->data_array) === 0){
			$this->buildAssocFromCSV();
		}

		$this->data_array;
		return $this->data_array;
	}

	private function buildAssocFromCSV(){
		$row = 0;
		$this->getFieldsHeaders();
		for ($c=0; $c < count($this->header_array); $c++){
			if(function_exists("mb_strtolower")) {
				$lowercase_value = trim(mb_strtolower($this->header_array[$c], "UTF-8"));
			} else {
				$lowercase_value = trim(strtolower($this->header_array[$c]));
			}
			$this->header_array[$c] = str_replace(" ", "_", $lowercase_value);
		}
		//var_dump($this->header_array);
		while (($data = fgetcsv($this->file_handler, 65536, $this->delimiter)) !== FALSE) {
			for ($c = 0; $c < count($data); $c++){
				//$this->data_array["product_" . $row][$this->header_array[$c]] = html_entity_decode(htmlentities($data[$c], ENT_COMPAT, "UTF-8"), ENT_COMPAT, "UTF-8");
				$this->data_array["product_" . $row][$this->header_array[$c]]=$data[$c];
			}
			$row++;
		}
	}

}

class XmlDataParser extends DataParser
{
	/**
	 * @var string - xml document version
	 */
	protected $document_version = null;

	/**
	 * @var String - xml node name
	 */
	protected $item_parent = null;

	/**
	 * @var object DOMDocument
	 */
	protected $xml_document = null;

	/**
	 * @var array for select list
	 */
	protected $stringify_header_array = null;

	/**
	 * @var array of xml nodes names
	 */
	private $keys_list;

	/**
	 * @var String delimiter character
	 */
	const ELEM_GLUE = " > ";

	/**
	 * @var String xml root element name
	 */
	private $root_name = "";

	/**
	 * @var String list or accepted fields
	 */
	private $keys_encoded = null;
	

	public function __construct($source_file, $file_handler){
		parent::__construct($source_file, $file_handler);
	}

	public function getFieldsHeaders(){
		if($this->header_array === null){
			$this->header_array = array();

			$this->buildXmlDocument();
		}
		$header_data = array();
		foreach ($this->header_array as $id => $column_name) {
			if(function_exists("mb_strtolower")) {
				$lowercase_column = trim(mb_strtolower($column_name, "UTF-8"));
			} else {
				$lowercase_column = trim(strtolower($column_name));
			}
			$header_data[$lowercase_column] = array("id" => $id, "title" => $column_name);
		}
		$this->header_array_conv = $header_data;
		return $this->header_array_conv;
		//return $this->header_array;
	}

	public function getFieldsData(){
		if($this->header_array === null){
			$this->header_array = array();

			$this->buildXmlDocument();
		}

		if(count($this->data_array) === 0){
			$tmp_data = $this->buildAssocFromXML();
			/*
			 * on operation=insert
			 * <input type="hidden" name="xml_product_root" value="product">
			 */
			$this->productHolderNode = strtolower(get_param("xml_product_root"));
			$this->data_array = $this->filterXMLArray($this->productHolderNode, $tmp_data);

			if(!isset($this->data_array[0])){
				$this->data_array = array($this->data_array);
			}

		}

		//var_dump($this->data_array);exit;
		return $this->data_array;
	}
	
	/**
	 * public interface of xml data parser
	 * @return array of tuples prepared for Viart set_options function
	 */
	public function getHeaderVisual(){
		if($this->stringify_header_array === null){
			$this->stringify_header_array = array();
			$root_name = $this->xml_document->documentElement->nodeName;
			$this->root_name = $root_name;
			$headers_data = $this->buildVisual($this->xml_document->documentElement);
			$this->keys_list[$root_name] = $headers_data;
			$this->scanHeaders($this->keys_list);
		}

		return $this->stringify_header_array;
	}


	/**
	 * public xml interface elements as json
	 */
	public function getHeadersEncoded(){
		if($this->stringify_header_array === null){
			$this->getHeaderVisual();
		}
		if($this->keys_encoded === null){

			$this->getKeys($this->keys_list[$this->root_name]);
			$this->keys_encoded = json_encode($this->keys_encoded);
		}
		return $this->keys_encoded;
	}

	private function getKeys($data){
		foreach($data as $key => $value){
			if(is_array($value)){
				$this->keys_encoded[$key] = $this->showElemKeys($value);
				$this->getKeys($value);
			}
		}	
	}

	private function showElemKeys($ar){
		$tmp_data = array();
		foreach($ar as $key => $value){
			$tmp_data[] = $key;
			if(is_array($ar[$key])){
				$tmp_data = array_merge($this->showElemKeys($ar[$key]), $tmp_data);
			}
		}

		return $tmp_data;
	}

	private function scanHeaders($data){

		foreach ($data as $key => $value) {
			if(is_array($value)){
				$parent_list = "";
				$keys = $this->getKeysList($key, $this->keys_list);
				if(is_array($keys)){
					$parent_list = implode(self::ELEM_GLUE, $keys) . self::ELEM_GLUE . $key;
				}
				if(strlen($parent_list)){
					$this->stringify_header_array[] = array($key, $parent_list);
				}
				$this->scanHeaders($value);
			}
		}	
	}


	private function getKeysList($subject, $array){
		foreach ($array as $key => $value){
			if (is_array($value)){
				if (in_array($subject, array_keys($value)))
					return array($key);
				else{
					$chain = $this->getKeysList($subject, $value);
					if(!is_null($chain))
						return array_merge(array($key), $chain);
				}
			}
		}

		return null;
	}

	private function buildVisual(DOMNode $node, $path = ""){

		$nodesData = array();
		$result_str = "";

		if(isset($node->childNodes)) {
			foreach($node->childNodes as $child) {
				$nodesData[strtolower($child->nodeName)] = isset($nodesData[$child->nodeName]) ? $nodesData[$child->nodeName] + 1 : 1;
			}
		}

		if($node->nodeType == XML_TEXT_NODE) {
			echo $node->getNodePath() . '<br>';
			$result_str = html_entity_decode(htmlentities($node->nodeValue, ENT_COMPAT, "UTF-8"),
				ENT_COMPAT, "UTF-8");
		}
		else {
			if($node->hasChildNodes()){
				$children = $node->childNodes;

				for($i=0; $i<$children->length; $i++) {

					$child = $children->item($i);

					if($child->nodeName != "#text") {
						if($nodesData[strtolower($child->nodeName)] > 1) {
							$result_str[strtolower($child->nodeName)][] = $this->buildVisual($child);
						}
						else {
							$result_str[strtolower($child->nodeName)] = $this->buildVisual($child);
						}
					}

				}

			}

		}

		return $result_str;
	}

	/**
	 * @param $nodeName - node which contains single product data
	 * @param $XMLArray - converted xml document array
	 * @return array of needed nodes
	 */
	private function filterXMLArray($nodeName, $XMLArray){
		$found = array();

		foreach($XMLArray as $key => $val) {
			if($key == $nodeName){
				$found = $val;
			}
			elseif(is_array($val)){
				$found = array_merge($found, $this->filterXMLArray($nodeName, $val));
			}
		}
		return $found;
	}

	/**
	 * @return void
	 * create xml document and fills in required properties
	 */
	private function buildXmlDocument(){
		if($this->document_version === null){
			$this->document_version = $this->_getDocumentVersion();
		}

		$this->xml_document = new DOMDocument($this->document_version, 'UTF-8');
		$this->xml_document->load($this->data_source);
		$this->buildHeaders($this->xml_document->documentElement);

	}

	private function _getDocumentVersion(){
		$this->document_version = "1.0";
		$title_line = fgets($this->file_handler, 4096);
		if(preg_match('/version=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is', $title_line, $matches)){
			$this->document_version = $matches[2];
		}

		return $this->document_version;

	}

	private function buildHeaders(DOMNode $domNode){
		foreach ($domNode->childNodes as $node){
			if(!in_array($node->nodeName, $this->header_array) && $node->nodeType !== XML_TEXT_NODE){
				array_push($this->header_array, $node->nodeName);
			}
			if($node->hasChildNodes()) {
				$this->buildHeaders($node);
			}
		}
	}

	private function convertToArr(DOMNode $node) {
		$nodesData = array();
		$result_str = "";

		if(isset($node->childNodes)) {
			foreach($node->childNodes as $child) {
				$nodesData[strtolower($child->nodeName)] = isset($nodesData[$child->nodeName]) ? $nodesData[$child->nodeName] + 1 : 1;
			}
		}

		if($node->nodeType == XML_TEXT_NODE) {
			$result_str = html_entity_decode(htmlentities($node->nodeValue, ENT_COMPAT, "UTF-8"),
				ENT_COMPAT, "UTF-8");
		}
		else {
			if($node->hasChildNodes()){
				$children = $node->childNodes;

				for($i=0; $i<$children->length; $i++) {
					$child = $children->item($i);

					if($child->nodeName != "#text") {
						if($nodesData[strtolower($child->nodeName)] > 1) {
							$result_str[strtolower($child->nodeName)][] = $this->convertToArr($child);
						}
						else {
							$result_str[strtolower($child->nodeName)] = $this->convertToArr($child);
						}
					}
					else if ($child->nodeName == "#text") {
						$text = $this->convertToArr($child);

						if (trim($text) != '') {
							$result_str = $this->convertToArr($child);
						}
					}
					else if ($child->nodeType == XML_CDATA_SECTION_NODE) {
						$result_str = $child->textContent;
					}
				}
			}

		}

		return $result_str;
	}

	private function buildAssocFromXML() {
		return $this->convertToArr($this->xml_document->documentElement);
	}

}