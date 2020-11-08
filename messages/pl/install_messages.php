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
	// informacje dot. instalacji
	"INSTALL_TITLE" => "Instalacja ViArt SHOP",

	"INSTALL_STEP_1_TITLE" => "Instalacja: Krok 1",
	"INSTALL_STEP_1_DESC" => "Dziękujemy za wybranie ViArt SHOP. Aby zakończyć instalację, prosimy wypełnij podane niżej szczegóły. Prosimy zauważyć, że baza danych, którą Wybrałeś/aś powinna już istnieć. Jeśli instalujesz na istniejącej już bazie danych ODBC np. MS Acces powinieneś/aś najpierw utworzyć DSN (Data Source Name) przed przystąpieniem do instalacji.",
	"INSTALL_STEP_2_TITLE" => "Instalacja: Krok 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Instalacja: Krok 3",
	"INSTALL_STEP_3_DESC" => "Prosimy o wybranie układu graficznego dla stron. Będziesz mógł/ła zmienić układ później.",
	"INSTALL_FINAL_TITLE" => "Instalacja: Koniec",
	"SELECT_DATE_TITLE" => "Wybierz format danych",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Ustawienia bazy danych",
	"DB_PROGRESS_MSG" => "Zapełnianie struktury bazy danych w toku.",
	"SELECT_PHP_LIB_MSG" => "Wybierz bibliotekę PHP",
	"SELECT_DB_TYPE_MSG" => "Wybierz rodzaj bazy danych",
	"ADMIN_SETTINGS_MSG" => "Ustawienia administracyjne",
	"DATE_SETTINGS_MSG" => "Formaty danych",
	"NO_DATE_FORMATS_MSG" => "Nie ma dostępnych formatów danych",
	"INSTALL_FINISHED_MSG" => "W tym momencie Twoja podstawowa instalacja jest już kompletna. Prosimy sprawdź ustawienia w sekcji administracyjnej i wprowadź ewentualne wymagane zmiany.",
	"ACCESS_ADMIN_MSG" => "Aby dostać się do sekcji administracyjnej kliknij tu",
	"ADMIN_URL_MSG" => "URL administracyjny",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Dziękujemy za wybranie <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Rodzaj bazy danych",
	"DB_TYPE_DESC" => "Please select the <b>type of database</b> that you are using. If you using SQL Server or Microsoft Access, please select ODBC.",
	"DB_PHP_LIB_FIELD" => "Biblioteka PHP",
	"DB_HOST_FIELD" => "Nazwa hosta",
	"DB_HOST_DESC" => "Please enter the <b>name</b> or <b>IP address of the server</b> on which your ViArt database will run. If you are running your database on your local PC then you can probably just leave this as \"<b>localhost</b>\" and the port blank. If you using a database provided by your hosting company, please see your hosting company's documentation for the server settings.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Nazwa bazy danych / DSN ",
	"DB_NAME_DESC" => "If you are using a database such as MySQL or PostgreSQL then please enter the <b>name of the database</b> where you would like ViArt to create its tables. This database must exist already. If you are just installing ViArt for testing purposes on your local PC then most systems have a \"<b>test</b>\" database you can use. If not, please create a database such as \"viart\" and use that. If you are using Microsoft Access or SQL Server then the Database Name should be the <b>name of the DSN</b> that you have set up in the Data Sources (ODBC) section of your Control Panel.",
	"DB_USER_FIELD" => "Użytkownik",
	"DB_PASS_FIELD" => "Hasło",
	"DB_USER_PASS_DESC" => "<b>Username</b> and <b>Password</b> - please enter the username and password you want to use to access the database. If you are using a local test installation the username is probably \"<b>root</b>\" and the password is probably blank. This is fine for testing, but please note that this is not secure on production servers.",
	"DB_PERSISTENT_FIELD" => "Trwałe połączenie",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Zapełnianie bazy danych",
	"DB_POPULATE_DESC" => "aby utworzyć tabelę bazy danych i jej strukturę oraz wypełnić ją danymi zaznacz to pole",
	"DB_TEST_DATA_FIELD" => "Test Data",
	"DB_TEST_DATA_DESC" => "to add some test data to your database tick the checkbox",
	"ADMIN_EMAIL_FIELD" => "Email administratora",
	"ADMIN_LOGIN_FIELD" => "Login administratora",
	"ADMIN_PASS_FIELD" => "Hasło administratora",
	"ADMIN_CONF_FIELD" => "Potwierdzenie hasła",
	"DATETIME_SHOWN_FIELD" => "Format czasu dla danych (pokazany na stronach)",
	"DATE_SHOWN_FIELD" => "Format daty (pokazany na stronach)",
	"DATETIME_EDIT_FIELD" => "Format czasu dla danych (dla edycji)",
	"DATE_EDIT_FIELD" => "Format daty (dla edycji)",
	"DATE_FORMAT_COLUMN" => "Format danych",

	"DB_LIBRARY_ERROR" => "Funkcje PHP dla {db_library} nie zostały zdefiniowane. Prosimy o sprawdzenie ustawień bazy danych w pliku konfiguracyjnym - php.ini.",
	"DB_CONNECT_ERROR" => "Nie można połączyć się z bazą danych. Prosimy o sprawdzenie parametrów Twojej bazy danych.",
	"INSTALL_FINISHED_ERROR" => "Proces instalacji zakończył się.",
	"WRITE_FILE_ERROR" => "Brak prawa do zapisu dla pliku <b>'includes/var_definition.php'</b>. Przed kontynuacją prosimy zmienić prawa dostępu do pliku.",
	"WRITE_DIR_ERROR" => "Brak prawa do zapisu dla katalogu <b>'includes/'</b>. Przed kontynuacją prosimy zmienić prawa dostępu do katalogu.",
	"DUMP_FILE_ERROR" => "Plik typu dump '{file_name}' nie został odnaleziony.",
	"DB_TABLE_ERROR" => "Tabela '{table_name}' nie została odnaleziona. Prosimy o wypełnienie bazy danych odpowiednimi danymi.",
	"TEST_DATA_ERROR" => "Check <b>{POPULATE_DB_FIELD}</b> before populating tables with test data",
	"DB_HOST_ERROR" => "The hostname that you specified could not be found.",
	"DB_PORT_ERROR" => "Can't connect to database server using specified port.",
	"DB_USER_PASS_ERROR" => "The username or password you specified is not correct.",
	"DB_NAME_ERROR" => "Login settings were correct, but the database '{db_name}' could not be found.",

	// informacje dot. uaktualnienia
	"UPGRADE_TITLE" => "Aktualizacja ViArt SHOP",
	"UPGRADE_NOTE" => "Uwaga: Prosimy o rozpatrzenie wykonania kopii zapasowej bazy danych przed przystąpieniem do działania.",
	"UPGRADE_AVAILABLE_MSG" => "Dostępna jest aktualizacja",
	"UPGRADE_BUTTON" => "Aktualizuj do {version_number} ",
	"CURRENT_VERSION_MSG" => "Twoja aktualnie zainstalowana wersja",
	"LATEST_VERSION_MSG" => "Werjsa dostępna do instalacjii",
	"UPGRADE_RESULTS_MSG" => "Wyniki aktualizacji",
	"SQL_SUCCESS_MSG" => "Zapytania SQL powiodły się",
	"SQL_FAILED_MSG" => "Zapytania SQL nie powiodły się",
	"SQL_TOTAL_MSG" => "Wszystkich zapytań SQL wykonano",
	"VERSION_UPGRADED_MSG" => "Twoja wersja została zaktualizowana do wersji",
	"ALREADY_LATEST_MSG" => "Już masz uaktualnioną ostatnią wersję",
	"DOWNLOAD_NEW_MSG" => "The new version was detected",
	"DOWNLOAD_NOW_MSG" => "Download version {version_number} now",
	"DOWNLOAD_FOUND_MSG" => "We have detected that the new {version_number} version is available to download. Please click the link below to start downloading. After completing the download and replacing the files don't forget to run Upgrade routine again.",
	"NO_XML_CONNECTION" => "Warning! No connection to 'http://www.viart.com/' available!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
