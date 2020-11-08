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
	// повідомлення для скачування
	"DOWNLOAD_WRONG_PARAM" => "Невірно вказано параметр(и) для скачування",
	"DOWNLOAD_MISS_PARAM" => "Не вказано параметр(и) для скачування",
	"DOWNLOAD_INACTIVE" => "Закачка неактивна",
	"DOWNLOAD_EXPIRED" => "Період для скачування закінчився",
	"DOWNLOAD_LIMITED" => "Ви перевищили кількість скачувань",
	"DOWNLOAD_PATH_ERROR" => "Неможливо встановити шлях до продукту",
	"DOWNLOAD_RELEASE_ERROR" => "Неможливо знайти вказану версію продукту",
	"DOWNLOAD_USER_ERROR" => "Тільки зареєстрованні користувачі можуть скачувати цей файл",
	"ACTIVATION_OPTIONS_MSG" => "Опції активації",
	"ACTIVATION_MAX_NUMBER_MSG" => "Максимальная кількість активацій",
	"DOWNLOAD_OPTIONS_MSG" => "Опції програмних продуктів",
	"DOWNLOADABLE_MSG" => "Програмні продукти",
	"DOWNLOADABLE_DESC" => "для програмних продуктів можна вибрати 'Період завантаження', 'Шлях до програмного продукту' та 'Опції активації'",
	"DOWNLOAD_PERIOD_MSG" => "Період для скачування",
	"DOWNLOAD_PATH_MSG" => "Шлях до програмного продукту",
	"DOWNLOAD_PATH_DESC" => "Ви можете додати кілька шляхів, розділивши їх крапкою з комою",
	"UPLOAD_SELECT_MSG" => "Виберіть файл для закачування і натисніть кнопку {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Файл <b>{filename}</b> був закачаний.",
	"UPLOAD_SELECT_ERROR" => "Будь ласка виберіть спочатку файл.",
	"UPLOAD_IMAGE_ERROR" => "Тільки малюнки дозволяються.",
	"UPLOAD_FORMAT_ERROR" => "Неприпустимий",
	"UPLOAD_SIZE_ERROR" => "Файли більші за {filesize} не дозволяються.",
	"UPLOAD_DIMENSION_ERROR" => "Малюнки більші за {dimension} не дозволяються.",
	"UPLOAD_CREATE_ERROR" => "Система не може створити файл.",
	"UPLOAD_ACCESS_ERROR" => "Ви не маєте прав на завантаження файлів",
	"DELETE_FILE_CONFIRM_MSG" => "Видалити цей файл?",
	"NO_FILES_MSG" => "Файли не знайдено",
	"SERIAL_GENERATE_MSG" => "Генерувати серійний номер",
	"SERIAL_DONT_GENERATE_MSG" => "не генерувати",
	"SERIAL_RANDOM_GENERATE_MSG" => "генерувати серійний номер для програмного продукту",
	"SERIAL_FROM_PREDEFINED_MSG" => "взяти серійник з підготовленого переліку",
	"SERIAL_PREDEFINED_MSG" => "Підготовлені серійні номери",
	"SERIAL_NUMBER_COLUMN" => "Серійний номер",
	"SERIAL_USED_COLUMN" => "Використаний",
	"SERIAL_DELETE_COLUMN" => "Видалити",
	"SERIAL_MORE_MSG" => "Додати серійних номерів?",
	"SERIAL_PERIOD_MSG" => "Період серійного номеру",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Показати Умови і Угоди",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Для скачування продукту користувач повинен прочитати та погодитись з Умовами і Угодами",
	"DOWNLOAD_TERMS_USER_ERROR" => "Для скачування продукту Вам необхідно прочитати та погодитись з Умовами і Угодами",

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
