<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  download_messages.php                                    ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// informacje dot. ściągania plików
	"DOWNLOAD_WRONG_PARAM" => "Zły parametr do ściągnięcia pliku(ów).",
	"DOWNLOAD_MISS_PARAM" => "Brakujący parametr do ściągnięcia pliku(ów).",
	"DOWNLOAD_INACTIVE" => "Ściąganie plików nieaktywne.",
	"DOWNLOAD_EXPIRED" => "Okres w którym mogłeś/aś ściągać pliki wygasł.",
	"DOWNLOAD_LIMITED" => "Pzekroczyłeś maksymalną ilość ściągnięć plików.",
	"DOWNLOAD_PATH_ERROR" => "Ścieżka do produktu nie może zostać odnaleziona.",
	"DOWNLOAD_RELEASE_ERROR" => "Wydanie nie zostało odnalezione.",
	"DOWNLOAD_USER_ERROR" => "Tylko zarejestrowani użytkownicy mogą ściągnąć ten plik.",
	"ACTIVATION_OPTIONS_MSG" => "Activation Options",
	"ACTIVATION_MAX_NUMBER_MSG" => "Max Number of Activations",
	"DOWNLOAD_OPTIONS_MSG" => "Downloadable / Software Options",
	"DOWNLOADABLE_MSG" => "Downloadable (Software)",
	"DOWNLOADABLE_DESC" => "for downloadable product you can also specify 'Download Period', 'Path to Downloadable File' and 'Activations Options'",
	"DOWNLOAD_PERIOD_MSG" => "Download Period",
	"DOWNLOAD_PATH_MSG" => "Path to Downloadable File",
	"DOWNLOAD_PATH_DESC" => "you could add multiple paths divided by semicolon",
	"UPLOAD_SELECT_MSG" => "Wybierz plik, który chcesz wgrać na serwer i wciśnij przycisk {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Plik <b>{filename}</b> został wgrany na serwer.",
	"UPLOAD_SELECT_ERROR" => "Prosimy w pierwszej kolejności o wybranie pliku.",
	"UPLOAD_IMAGE_ERROR" => "Tylko pliki graficzne są dopuszczalne.",
	"UPLOAD_FORMAT_ERROR" => "This type of file is not allowed.",
	"UPLOAD_SIZE_ERROR" => "Pliki większe niż {filesize} nie są dopuszczalne.",
	"UPLOAD_DIMENSION_ERROR" => "Obrazy większe niż {dimension} nie są dopuszczalne.",
	"UPLOAD_CREATE_ERROR" => "System nie mógł utworzyć pliku.",
	"UPLOAD_ACCESS_ERROR" => "You don't have permissions to upload files.",
	"DELETE_FILE_CONFIRM_MSG" => "Are you sure you want to delete this file?",
	"NO_FILES_MSG" => "No files were found",
	"SERIAL_GENERATE_MSG" => "Generate Serial Number",
	"SERIAL_DONT_GENERATE_MSG" => "don't generate",
	"SERIAL_RANDOM_GENERATE_MSG" => "generate random serial for software product",
	"SERIAL_FROM_PREDEFINED_MSG" => "get serial number from predefined list",
	"SERIAL_PREDEFINED_MSG" => "Predefined Serial Numbers",
	"SERIAL_NUMBER_COLUMN" => "Serial Number",
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
	"DOWNLOAD_LIMIT_DESC" => "number of times file can be downloaded",
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
