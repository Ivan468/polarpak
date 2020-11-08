<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  install_messages.php                                     ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// повідомлення інсталяції
	"INSTALL_TITLE" => "Iнсталяцiя ViArt SHOP",

	"INSTALL_STEP_1_TITLE" => "Iнсталяцiя: Крок 1",
	"INSTALL_STEP_1_DESC" => "Дякуємо за вибiр ViArt SHOP. Для того, щоб закiнчити процес iнсталяцiї будь-ласка заповнiть всi необхiднi даннi. Зауважте, що база, яку ви будете використовувати вже має бути створена. Якщо ви використовуєте базу, що використовує ODBC, наприклад MS Access, для початку вам необхiдно створити запис DSN для того щоб продовжити.",
	"INSTALL_STEP_2_TITLE" => "Iнсталяцiя: Крок 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Iнсталяцiя: Крок 3",
	"INSTALL_STEP_3_DESC" => "Будь-ласка виберіть дизайн сайту. Надалі ви зможете його змінити на інший.",
	"INSTALL_FINAL_TITLE" => "Iнсталяцiя: Завершення",
	"SELECT_DATE_TITLE" => "Вибiр формату дати",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Налаштування бази",
	"DB_PROGRESS_MSG" => "Прогрес наповення структури бази",
	"SELECT_PHP_LIB_MSG" => "Виберiть PHP бiблiотеку",
	"SELECT_DB_TYPE_MSG" => "Виберiть тип бази",
	"ADMIN_SETTINGS_MSG" => "Налаштування адмiнiстратора",
	"DATE_SETTINGS_MSG" => "Формати дати",
	"NO_DATE_FORMATS_MSG" => "Немає жодного доступного формату дати",
	"INSTALL_FINISHED_MSG" => "На цьому кроцi ваша базова iнсталяцiя завершена. Будь-ласка перевiрте всi вашi налаштування в адмiнiстативнiй частинi i зробiть всi необхiднi змiни.",
	"ACCESS_ADMIN_MSG" => "Для доступу до адмiнiстративної частини будь-ласка натиснiть тут",
	"ADMIN_URL_MSG" => "Посилання на адмiнiстративну частину",
	"MANUAL_URL_MSG" => "Посилання на документацію",
	"THANKS_MSG" => "Дякуємо за вибiр <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Тип бази",
	"DB_TYPE_DESC" => "Виберіть <b>тип бази даних</b> що використовується. Якщо використовується SQL Server або Microsoft Access, виберіть ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP бiблiотека",
	"DB_HOST_FIELD" => "Назва хосту",
	"DB_HOST_DESC" => "Введіть <b>назву</b> або <b>IP адресу серверу</b> на якому Ваша база даних ViArt  буде запущена. Якщо база даних виконується на локальному комп'ютері, просто залиште назву сервера \"<b>localhost</b>\", а значення порту порожнім. Якщо використовується база даних хостінг компанії, використовуйте документацію хостінг компанії для серверних налаштувань.",
	"DB_PORT_FIELD" => "Порт",
	"DB_NAME_FIELD" => "Iм'я бази / DSN",
	"DB_NAME_DESC" => "Якщо використовується база даних така як MySQL або PostgreSQL, введіть <b>Назву бази даних</b> де буде розташовано таблиці ViArt. База даних повинна бути вже створена. Якщо ViArt встановлюється лише для тестування на локальній машині, більшість систем мають тестову (\"<b>test</b>\") базу даних, яку Ви можете використати. Якщо ні, створіть базу даних, з назвою наприклад 'viart'. Якщо використовується Microsoft Access або SQL Server тоді Назва Бази даних повинна бути <b>назвою DSN</b> яку встановлено в секції Data Sources (ODBC) Панелі Керування.",
	"DB_USER_FIELD" => "Користувач",
	"DB_PASS_FIELD" => "Пароль",
	"DB_USER_PASS_DESC" => "<b>І'мя користувача</b> та <b>Пароль</b> - введіть ім'я та пароль для доступу до бази даних. Якщо використовується локальна тестова інсталяція ім'я як правило це \"<b>root</b>\", а пароль як правило порожній. Це зручно для тестування, однак заради безпеки не використовуйте такі імена та паролі на виробничих серверах.",
	"DB_PERSISTENT_FIELD" => "Постiйний зв'язок",
	"DB_PERSISTENT_DESC" => "Для використання постійного з'єднання з MySQL або Postgre базою. Якщо ви не знаєте що це означає, краще залиште як є.",
	"DB_CREATE_DB_FIELD" => "Створити базу даних",
	"DB_CREATE_DB_DESC" => "для створення бази даних (тільки MySQL та Postgre) натисність тут ",
	"DB_POPULATE_FIELD" => "Заповнити базу",
	"DB_POPULATE_DESC" => "для того щоб створити структуру таблиць та заповнити їх, натисніть тут",
	"DB_TEST_DATA_FIELD" => "Тестові дані",
	"DB_TEST_DATA_DESC" => "натисніть тут, щоб додати деякі тестові дані",
	"ADMIN_EMAIL_FIELD" => "Електронна адреса адмiнiстратора",
	"ADMIN_LOGIN_FIELD" => "Логiн адмiнiстратора",
	"ADMIN_PASS_FIELD" => "Пароль адмiнiстратора",
	"ADMIN_CONF_FIELD" => "Пiдтвердження паролю",
	"DATETIME_SHOWN_FIELD" => "Формат дати з часом (вiдображення на сайтi)",
	"DATE_SHOWN_FIELD" => "Формат дати (вiдображення на сайтi)",
	"DATETIME_EDIT_FIELD" => "Формат дати з часом (для редагування)",
	"DATE_EDIT_FIELD" => "Формат дати (для редагування)",
	"DATE_FORMAT_COLUMN" => "Формат дати ",

	"DB_LIBRARY_ERROR" => "PHP функції для {db_library} не визначені. Перевірте налаштування бази даних у файлі конфігурацій - php.ini.",
	"DB_CONNECT_ERROR" => "Не можна встановити зв'язок з базою. Перевiрте параметри зв'язку з базою.",
	"INSTALL_FINISHED_ERROR" => "Процес iнсталяцiї вже завершено.",
	"WRITE_FILE_ERROR" => "Немає прав для змiни файлу <b>'includes/var_definition.php'</b>. Будь-ласка змiнiть права доступу перед тим як продовжити.",
	"WRITE_DIR_ERROR" => "Немає прав для запису в папки <b>'includes/'</b>. Будь-ласка змiнiть права доступу перед тим як продовжити.",
	"DUMP_FILE_ERROR" => "Файл бази '{file_name}' не знайдено.",
	"DB_TABLE_ERROR" => "Табличку '{table_name}' не знайдено. Будь-ласка наповнiть базу необхiдною iнформацiєю.",
	"TEST_DATA_ERROR" => "Спочатку поставте позначку <b>{POPULATE_DB_FIELD}</b>, якщо бажаєте додати тестові дані",
	"DB_HOST_ERROR" => "Не знайдено вказаний сервер.",
	"DB_PORT_ERROR" => "Неможливо під'єднатися до сервера бази даних по вказаному портому.",
	"DB_USER_PASS_ERROR" => "Вказані ім'я користувача та пароль не вірні",
	"DB_NAME_ERROR" => "І'мя та пароль вірні, але базу '{db_name}' не знайдено.",

	// повідомлення оновлень
	"UPGRADE_TITLE" => "Оновлення бази даних",
	"UPGRADE_NOTE" => "Примiтка: будь-ласка створiть копiю бази данних перед початком.",
	"UPGRADE_AVAILABLE_MSG" => "Нова версiя доступна",
	"UPGRADE_BUTTON" => "Поновити до {version_number}",
	"CURRENT_VERSION_MSG" => "Ваша поточна версiя",
	"LATEST_VERSION_MSG" => "Версiя доступна для iнсталяцiї",
	"UPGRADE_RESULTS_MSG" => "Результати поновлення",
	"SQL_SUCCESS_MSG" => "SQL запитiв успiшних",
	"SQL_FAILED_MSG" => "SQL запитiв з помилками",
	"SQL_TOTAL_MSG" => "Всього SQL запитiв виконано",
	"VERSION_UPGRADED_MSG" => "Ваша версiя була поновлена до",
	"ALREADY_LATEST_MSG" => "У вас стоїть остання доступна версiя",
	"DOWNLOAD_NEW_MSG" => "Знайдено оновлену версію",
	"DOWNLOAD_NOW_MSG" => "Скачайте версію {version_number} зараз",
	"DOWNLOAD_FOUND_MSG" => "З'ясовано що версія {version_number} доступна для скачування. Натисніть на посилання щоб розпочати скачування. Після закінчення скачування та заміни файлів не забудьте знову запустити процедуру Оновлення.",
	"NO_XML_CONNECTION" => "Увага! Відсутнє з'єднання з 'http://www.viart.com/'!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
