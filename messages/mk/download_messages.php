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
	// download messages
	"DOWNLOAD_WRONG_PARAM" => "Грешни параметри на симнување/downlodiranje",
	"DOWNLOAD_MISS_PARAM" => "Недостасуваат параметри на симнување/downlodiranje",
	"DOWNLOAD_INACTIVE" => "Download неактивен",
	"DOWNLOAD_EXPIRED" => "Вашиот период за download e поминат ",
	"DOWNLOAD_LIMITED" => "Реализиравте максимален број на downlodiranja",
	"DOWNLOAD_PATH_ERROR" => "Патеката кон производот не може да се најде",
	"DOWNLOAD_RELEASE_ERROR" => "Изданието не е најдено",
	"DOWNLOAD_USER_ERROR" => "Оваа датотека е дозволена само за регистрирани корисници",
	"ACTIVATION_OPTIONS_MSG" => "Активирање на опција",
	"ACTIVATION_MAX_NUMBER_MSG" => "Максимален број на активирања",
	"DOWNLOAD_OPTIONS_MSG" => "Опции за превземање/downlodiranje ",
	"DOWNLOADABLE_MSG" => "Downloadable (Software)",
	"DOWNLOADABLE_DESC" => "За производите кои се  симнуваат може да наведете  'Download Period', 'Path to Downloadable File' и  'Activations Options'",
	"DOWNLOAD_PERIOD_MSG" => "Период за превземање",
	"DOWNLOAD_PATH_MSG" => "Патека кон датотека за превземање",
	"DOWNLOAD_PATH_DESC" => "може да поставите повеќе патишта разделени со запирка",
	"UPLOAD_SELECT_MSG" => "Селектирајте дадотека за пренос/Upload и протиснете  {button_name}",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Датотеката  <b>{filename}</b> е префрлена.",
	"UPLOAD_SELECT_ERROR" => "Ве молиме прво селектирајте датотека",
	"UPLOAD_IMAGE_ERROR" => "Дозволен е пренос само на слики",
	"UPLOAD_FORMAT_ERROR" => "Овој тип на датотека не е дозволен",
	"UPLOAD_SIZE_ERROR" => "Датотеки поголеми од {filesize} не се дозволени.",
	"UPLOAD_DIMENSION_ERROR" => "Слики поголеми од {dimension} не се дозволени",
	"UPLOAD_CREATE_ERROR" => "Системот неможе да креира датотека",
	"UPLOAD_ACCESS_ERROR" => "Вие немате дозвола за пренос/upload датотека",
	"DELETE_FILE_CONFIRM_MSG" => "Навистина би ја избришале оваа датотека?",
	"NO_FILES_MSG" => "Ниедна датотека не е најдена",
	"SERIAL_GENERATE_MSG" => "Генерирање сериски број",
	"SERIAL_DONT_GENERATE_MSG" => "не генерира",
	"SERIAL_RANDOM_GENERATE_MSG" => "генерирање случаен сериски број за software производ",
	"SERIAL_FROM_PREDEFINED_MSG" => "земете сериски број од преддефинирани броеви",
	"SERIAL_PREDEFINED_MSG" => "Претходно дефинирани сериски броеви",
	"SERIAL_NUMBER_COLUMN" => "Сериски број",
	"SERIAL_USED_COLUMN" => "Употребен",
	"SERIAL_DELETE_COLUMN" => "Избриши",
	"SERIAL_MORE_MSG" => "Дадади повеќе сериски броеви?",
	"SERIAL_PERIOD_MSG" => "Период на важење сериски број",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Прикажи термини и услови",
	"DOWNLOAD_SHOW_TERMS_DESC" => "За да превземете дадотека корисникот мора да се сложи со термините и условите",
	"DOWNLOAD_TERMS_USER_ERROR" => "За да превземете дадотека вие мора да се сложите со термините и условите",

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
