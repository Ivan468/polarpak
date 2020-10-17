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
	//съобщения свързани със сваляне (download)
	"DOWNLOAD_WRONG_PARAM" => "Грешни параметри за сваляне.",
	"DOWNLOAD_MISS_PARAM" => "Изпуснати параметри за сваляне.",
	"DOWNLOAD_INACTIVE" => "Неактивно сваляне.",
	"DOWNLOAD_EXPIRED" => "Вашият период за сваляне изтече.",
	"DOWNLOAD_LIMITED" => "Достигнахте вашият максимален брой сваляния.",
	"DOWNLOAD_PATH_ERROR" => "Пътят към продукта не е намерен.",
	"DOWNLOAD_RELEASE_ERROR" => "Версията не бе намерена",
	"DOWNLOAD_USER_ERROR" => "Само регистрирани потребители могат да свалят този файл.",
	"ACTIVATION_OPTIONS_MSG" => "Опции за активация",
	"ACTIVATION_MAX_NUMBER_MSG" => "Максимален брой активации",
	"DOWNLOAD_OPTIONS_MSG" => "Разрешено за сваляне / Софтуерни опции",
	"DOWNLOADABLE_MSG" => "Разрешено за сваляне (Софтуер)",
	"DOWNLOADABLE_DESC" => "За изтегляемият продукт можете да посочите \"Период на теглене\", \"Път до файла за теглене\" и \"Опции за активацията\".",
	"DOWNLOAD_PERIOD_MSG" => "Период за сваляне",
	"DOWNLOAD_PATH_MSG" => "Път към файла за сваляне",
	"DOWNLOAD_PATH_DESC" => "Можете да добавите няколко пътя, разделени с точка и запетая",
	"UPLOAD_SELECT_MSG" => "Избери файл за качване и натисни {button_name} бутон.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Файлът <b>{filename}</b> беше качен.",
	"UPLOAD_SELECT_ERROR" => "Моля, първо изберете файл.",
	"UPLOAD_IMAGE_ERROR" => "Само файлове-картинки са разрешени.",
	"UPLOAD_FORMAT_ERROR" => "Този тип файл не е разрешен.",
	"UPLOAD_SIZE_ERROR" => "Файлове по-големи от {filesize} не се допускат.",
	"UPLOAD_DIMENSION_ERROR" => "Картинки по-големи от {dimension} не са позволени.",
	"UPLOAD_CREATE_ERROR" => "Системата не може да създаде файла.",
	"UPLOAD_ACCESS_ERROR" => "Вие нямате права да качвате файлове.",
	"DELETE_FILE_CONFIRM_MSG" => "Сигурни ли сте, че искате да изтриете този файл?",
	"NO_FILES_MSG" => "Не са намерени файлове.",
	"SERIAL_GENERATE_MSG" => "Генериране сериен номер",
	"SERIAL_DONT_GENERATE_MSG" => "Не генерирай",
	"SERIAL_RANDOM_GENERATE_MSG" => "Генерирай произволен сериен номер за софтуерен продукт",
	"SERIAL_FROM_PREDEFINED_MSG" => "Вземи сериен номер от предварителен списък",
	"SERIAL_PREDEFINED_MSG" => "Предварителни списъци със серийни номера",
	"SERIAL_NUMBER_COLUMN" => "Сериен номер",
	"SERIAL_USED_COLUMN" => "Използван",
	"SERIAL_DELETE_COLUMN" => "Изтриване",
	"SERIAL_MORE_MSG" => "Добави още серийни номера?",
	"SERIAL_PERIOD_MSG" => "Период на сериен номер",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Покажи общите условия",
	"DOWNLOAD_SHOW_TERMS_DESC" => "За да свали продукта потребителят трябва да се запознае с общите условия",
	"DOWNLOAD_TERMS_USER_ERROR" => "За да свалите продукта трябва да се запознаете с общите условия",

	"DOWNLOAD_TITLE_MSG" => "Заглавие на тегленето",
	"DOWNLOADABLE_FILES_MSG" => "Файлове за теглене",
	"DOWNLOAD_INTERVAL_MSG" => "Интервал на теглене",
	"DOWNLOAD_LIMIT_MSG" => "Лимит на тегленията",
	"DOWNLOAD_LIMIT_DESC" => "Брой пъти, които един файл може да се изтегли",
	"MAXIMUM_DOWNLOADS_MSG" => "Максимален брой тегления",
	"PREVIEW_TYPE_MSG" => "Тип на превюто",
	"PREVIEW_TITLE_MSG" => "Заглавие на превюто",
	"PREVIEW_PATH_MSG" => "Път до превюто на файла",
	"PREVIEW_IMAGE_MSG" => "Превю на изображението",
	"MORE_FILES_MSG" => "Още файлове",
	"UPLOAD_MSG" => "Качване",
	"USE_WITH_OPTIONS_MSG" => "Използвай само с опции",
	"PREVIEW_AS_DOWNLOAD_MSG" => "Превю докато теглиш",
	"PREVIEW_USE_PLAYER_MSG" => "Използвай плеър за превю",
	"PROD_PREVIEWS_MSG" => "Превюта",

);
$va_messages = array_merge($va_messages, $messages);
