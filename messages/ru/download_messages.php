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
	// сообщения загрузки
	"DOWNLOAD_WRONG_PARAM" => "Неверно указаны параметры для скачивания",
	"DOWNLOAD_MISS_PARAM" => "Отсутствие параметра (ов) загрузки.",
	"DOWNLOAD_INACTIVE" => "Загрузка неактивна",
	"DOWNLOAD_EXPIRED" => "Ваш период загрузки истек.",
	"DOWNLOAD_LIMITED" => "Вы превысили максимальное число загрузок.",
	"DOWNLOAD_PATH_ERROR" => "Путь к файлу не найден.",
	"DOWNLOAD_RELEASE_ERROR" => "Выпуск не найден.",
	"DOWNLOAD_USER_ERROR" => "Только зарегистрированные пользователи могут загрузить этот файл.",
	"ACTIVATION_OPTIONS_MSG" => "Опции активации",
	"ACTIVATION_MAX_NUMBER_MSG" => "Максимальное кол-во активаций",
	"DOWNLOAD_OPTIONS_MSG" => "Опции цифровых товаров",
	"DOWNLOADABLE_MSG" => "Цифровые товары",
	"DOWNLOADABLE_DESC" => "для цифровых товаров можно выбрать 'Период загрузки', 'Путь к цифровому товару' и 'Опции активации'",
	"DOWNLOAD_PERIOD_MSG" => "Период загрузки",
	"DOWNLOAD_PATH_MSG" => "Путь к цифровому товару",
	"DOWNLOAD_PATH_DESC" => "Вы можете добавить несколько путей, разделив их точкой с запятой",
	"UPLOAD_SELECT_MSG" => "Выберите файл, чтобы загрузить и нажмите кнопку {button_name}.",
	"SPECIFY_PATH_FILE_MSG" => "Or please specify the path to your file and press 'Continue' button.",
	"UPLOADED_FILE_MSG" => "Файл <b> {filename} </b> загружен.",
	"UPLOAD_SELECT_ERROR" => "Пожалуйста  сначала выберите файл.",
	"UPLOAD_IMAGE_ERROR" => "Разрешены только файлы изображений.",
	"UPLOAD_FORMAT_ERROR" => "Этот тип файлов недопустим",
	"UPLOAD_SIZE_ERROR" => "Файлы больше чем {filesize} не разрешены.",
	"UPLOAD_DIMENSION_ERROR" => "Изображения большие чем {dimension} не разрешены.",
	"UPLOAD_CREATE_ERROR" => "Система не может создать файл.",
	"UPLOAD_ACCESS_ERROR" => "У вас нет прав на загрузку файлов",
	"DELETE_FILE_CONFIRM_MSG" => "Действительно удалить этот файл?",
	"NO_FILES_MSG" => "Файлы не найдены",
	"SERIAL_GENERATE_MSG" => "С генерировать серийный номер",
	"SERIAL_DONT_GENERATE_MSG" => "не генерировать",
	"SERIAL_RANDOM_GENERATE_MSG" => "с генерировать случайный серийный номер для цифрового товара",
	"SERIAL_FROM_PREDEFINED_MSG" => "взять серийный номер из подготовленного списка",
	"SERIAL_PREDEFINED_MSG" => "Подготовленные Серийные номера",
	"SERIAL_NUMBER_COLUMN" => "Серийный номер",
	"SERIAL_USED_COLUMN" => "Использованный",
	"SERIAL_DELETE_COLUMN" => "Удалить",
	"SERIAL_MORE_MSG" => "Добавить серийные номера?",
	"SERIAL_PERIOD_MSG" => "Период серийного номера",
	"DOWNLOAD_SHOW_TERMS_MSG" => "Показать Условия и Соглашения",
	"DOWNLOAD_SHOW_TERMS_DESC" => "Для скачивания товара пользователю необходимо прочитать и подтвердить Условия и Соглашения",
	"DOWNLOAD_TERMS_USER_ERROR" => "Для скачивания товара Вам необходимо прочитать и подтвердить Условия и Соглашения",

	"DOWNLOAD_TITLE_MSG" => "Название закачки",
	"DOWNLOADABLE_FILES_MSG" => "Загрузка файлов",
	"DOWNLOAD_INTERVAL_MSG" => "Интервал загрузки",
	"DOWNLOAD_LIMIT_MSG" => "Лимит загрузки",
	"DOWNLOAD_LIMIT_DESC" => "сколько раз можно загрузить файл",
	"MAXIMUM_DOWNLOADS_MSG" => "Максимум загрузок",
	"PREVIEW_TYPE_MSG" => "Тип пред просмотра",
	"PREVIEW_TITLE_MSG" => "Заголовок перевью",
	"PREVIEW_PATH_MSG" => "файл перевью",
	"PREVIEW_IMAGE_MSG" => "Изображение для перевью",
	"MORE_FILES_MSG" => "больше файлов",
	"UPLOAD_MSG" => "Загрузить",
	"USE_WITH_OPTIONS_MSG" => "Использовать только с опциями",
	"PREVIEW_AS_DOWNLOAD_MSG" => "перевью перед скачиванием",
	"PREVIEW_USE_PLAYER_MSG" => "Использовать плеер",
	"PROD_PREVIEWS_MSG" => "Пред просмотр",

);
$va_messages = array_merge($va_messages, $messages);
