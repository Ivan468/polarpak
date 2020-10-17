<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  download_messages.php                                    ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// download messages
	"DOWNLOAD_WRONG_PARAM" => "Nesprávny download parameter.",
	"DOWNLOAD_MISS_PARAM" => "Chýbajúci download parameter.",
	"DOWNLOAD_INACTIVE" => "Stiahnutie neaktívne.",
	"DOWNLOAD_EXPIRED" => "Váš čas na stiahnutie vypršal.",
	"DOWNLOAD_LIMITED" => "Prekročili ste maximálny počet možných stiahnutí.",
	"DOWNLOAD_PATH_ERROR" => "Nemôžem nájsť cestu k produktu.",
	"DOWNLOAD_RELEASE_ERROR" => "Vydanie nenájdené",
	"DOWNLOAD_USER_ERROR" => "Iba registrovaní užívatelia môžu stiahnuť tento súbor",
	"ACTIVATION_OPTIONS_MSG" => "Možnosti aktivácie",
	"ACTIVATION_MAX_NUMBER_MSG" => "Max Number of Activations",
	"DOWNLOAD_OPTIONS_MSG" => "Downloadable / Software Options",
	"DOWNLOADABLE_MSG" => "Downloadable (Software)",
	"DOWNLOADABLE_DESC" => "for downloadable product you can also specify 'Download Period', 'Path to Downloadable File' and 'Activations Options'",
	"DOWNLOAD_PERIOD_MSG" => "Download Period",
	"DOWNLOAD_PATH_MSG" => "Path to Downloadable File",
	"DOWNLOAD_PATH_DESC" => "you could add multiple paths devided by semicolon",
	"UPLOAD_SELECT_MSG" => "Vyberte súbor pre nahranie a stlačte tlačítko {button_name} .",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Súbor <b>{filename}</b> bol nahratý.",
	"UPLOAD_SELECT_ERROR" => "Prosím vyberte najprv nejaký súbor.",
	"UPLOAD_IMAGE_ERROR" => "Sú povolené iba súbory typu obrázok.",
	"UPLOAD_FORMAT_ERROR" => "This type of file is not allowed.",
	"UPLOAD_SIZE_ERROR" => "Súbory väčššie ako {filesize} nie sú dovolené.",
	"UPLOAD_DIMENSION_ERROR" => "Súbory rozmerov väčších ako {dimension} nie sú dovolené.",
	"UPLOAD_CREATE_ERROR" => "Systém nemôže vytvoriť súbor.",
	"UPLOAD_ACCESS_ERROR" => "You don't have permissions to upload files.",
	"DELETE_FILE_CONFIRM_MSG" => "Are you sure you want to delete this file?",
	"NO_FILES_MSG" => "No files were found",
	"SERIAL_GENERATE_MSG" => "Generate Serial Number",
	"SERIAL_DONT_GENERATE_MSG" => "don't generate",
	"SERIAL_RANDOM_GENERATE_MSG" => "generate random serial for software product",
	"SERIAL_FROM_PREDEFINED_MSG" => "get serial number from predefined list",
	"SERIAL_PREDEFINED_MSG" => "Predefined Serial Numbers",
	"SERIAL_NUMBER_COLUMN" => "Sériové číslo",
	"SERIAL_USED_COLUMN" => "Used",
	"SERIAL_DELETE_COLUMN" => "Delete",
	"SERIAL_MORE_MSG" => "Add more serial numbers?",
	"SERIAL_PERIOD_MSG" => "Serial Number Period",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Show Terms & Conditions",
	"DOWNLOAD_SHOW_TERMS_DESC" => "To download the product user has to read and agree to our terms and conditions",
	"DOWNLOAD_TERMS_USER_ERROR" => "To download the product you have to read and agree to our terms and conditions",

	"DOWNLOAD_TITLE_MSG" => "Download Title",
	"DOWNLOADABLE_FILES_MSG" => "Downloadable Files",
	"DOWNLOAD_INTERVAL_MSG" => "Download Interval",
	"DOWNLOAD_LIMIT_MSG" => "Downloads Limit",
	"DOWNLOAD_LIMIT_DESC" => "počet povolených stiahnutí súboru",
	"MAXIMUM_DOWNLOADS_MSG" => "Maximum Downloads",
	"PREVIEW_TYPE_MSG" => "Preview Type",
	"PREVIEW_TITLE_MSG" => "Preview Title",
	"PREVIEW_PATH_MSG" => "Path to Preview File",
	"PREVIEW_IMAGE_MSG" => "Preview Image",
	"MORE_FILES_MSG" => "More Files",
	"UPLOAD_MSG" => "Upload",
	"USE_WITH_OPTIONS_MSG" => "Use with options only",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Preview as download",
	"PREVIEW_USE_PLAYER_MSG" => "Use player to preview",
	"PROD_PREVIEWS_MSG" => "Previews",

);
$va_messages = array_merge($va_messages, $messages);
