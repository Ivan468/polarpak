<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  install_messages.php                                     ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

$messages = array(
	// installation messages
	"INSTALL_TITLE" => "ViArt SHOP Instalace",

	"INSTALL_STEP_1_TITLE" => "Instalace: Krok 1",
	"INSTALL_STEP_1_DESC" => "Děujeme, že jste si vybrali ViArt SHOP. Pro dokončení instalace, vyplňte nasledovný formulář. Nezapomeňte, že databáze musí již existovat. Pokud instalujete na databázi, která využívá ODBC jako například MS Access, měli by jste njdřív vytvořit DSN, teprv pak pokračovat",
	"INSTALL_STEP_2_TITLE" => "Instalace: Krok 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Instalace: Krok 3",
	"INSTALL_STEP_3_DESC" => "Prosím vyberte si vzhled stránky. Později ho můžete změnit.",
	"INSTALL_FINAL_TITLE" => "Instalace: Poslední krok",
	"SELECT_DATE_TITLE" => "Vyberte formát datumu",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Nastavení databáze",
	"DB_PROGRESS_MSG" => "Stav naplnění struktury databáze",
	"SELECT_PHP_LIB_MSG" => "Vyberte PHP knižnici",
	"SELECT_DB_TYPE_MSG" => "Vyberte typ databáze",
	"ADMIN_SETTINGS_MSG" => "Administrační nastavení",
	"DATE_SETTINGS_MSG" => "Formáty datumu",
	"NO_DATE_FORMATS_MSG" => "Žádné formáty datumu nejsou k dispozci",
	"INSTALL_FINISHED_MSG" => "V tomhle okamžiku je Vaše instalace ukončená. Prosím zkontrolujte nastavení v administračních nastaveních a udělejte potřebná nastavení.",
	"ACCESS_ADMIN_MSG" => "Pro přístup k administračním nastavením klikněte sem",
	"ADMIN_URL_MSG" => "Administrační URL",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Děkujeme, že jste si vybrali <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Typ databáze",
	"DB_TYPE_DESC" => "Please select the <b>type of database</b> that you are using. If you using SQL Server or Microsoft Access, please select ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP knižnice",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Please enter the <b>name</b> or <b>IP address of the server</b> on which your ViArt database will run. If you are running your database on your local PC then you can probably just leave this as \"<b>localhost</b>\" and the port blank. If you using a database provided by your hosting company, please see your hosting company's documentation for the server settings.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Jméno databáze / DSN",
	"DB_NAME_DESC" => "If you are using a database such as MySQL or PostgreSQL then please enter the <b>name of the database</b> where you would like ViArt to create its tables. This database must exist already. If you are just installing ViArt for testing purposes on your local PC then most systems have a \"<b>test</b>\" database you can use. If not, please create a database such as \"viart\" and use that. If you are using Microsoft Access or SQL Server then the Database Name should be the <b>name of the DSN</b> that you have set up in the Data Sources (ODBC) section of your Control Panel.",
	"DB_USER_FIELD" => "Užívatelské jméno",
	"DB_PASS_FIELD" => "Heslo",
	"DB_USER_PASS_DESC" => "<b>Username</b> and <b>Password</b> - please enter the username and password you want to use to access the database. If you are using a local test installation the username is probably \"<b>root</b>\" and the password is probably blank. This is fine for testing, but please note that this is not secure on production servers.",
	"DB_PERSISTENT_FIELD" => "Permanetní připojení",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Naplnit databázi",
	"DB_POPULATE_DESC" => "Pro vytvoření tabulky struktury databáze a její naplnění datama zaklikněte tohle políčko",
	"DB_TEST_DATA_FIELD" => "Test Data",
	"DB_TEST_DATA_DESC" => "to add some test data to your database tick the checkbox",
	"ADMIN_EMAIL_FIELD" => "Email administrátora",
	"ADMIN_LOGIN_FIELD" => "Administrátorovo uživatelské jméno",
	"ADMIN_PASS_FIELD" => "Administrátorovo heslo",
	"ADMIN_CONF_FIELD" => "Potvrdit heslo",
	"DATETIME_SHOWN_FIELD" => "Formát času (zobrazený na stránce)",
	"DATE_SHOWN_FIELD" => "Formát datumu (zobrazený na stránce)",
	"DATETIME_EDIT_FIELD" => "Formát času (pro úpravy)",
	"DATE_EDIT_FIELD" => "Formát datumu (pro úpravy)",
	"DATE_FORMAT_COLUMN" => "Formát datumu",

	"DB_LIBRARY_ERROR" => "PHP funkce pro {db_library} nejsou definovány. Prosím zkontrolujte nastavení databáze v konfiguračním souboru – php.ini.",
	"DB_CONNECT_ERROR" => "Nemůžu se připojit k databázi. Zkontrolujte parametry databáze.",
	"INSTALL_FINISHED_ERROR" => "Instalační proces byl již ukončen.",
	"WRITE_FILE_ERROR" => "Nemám právo pro zápis do souboru <b>'includes/var_definition.php'</b>. Před pokračováním zkontrolujte přístupová práva.",
	"WRITE_DIR_ERROR" => "Nemám práva pro zápis do složky <b>'includes/'</b>. Před pokračováním zkontrolujte přístupová práva.",
	"DUMP_FILE_ERROR" => "Dump soubor '{file_name}' nebyl nalezen.",
	"DB_TABLE_ERROR" => "Tabulka '{table_name}' nebyla nalezena. Prosím naplňte databázi příslušnýma datama.",
	"TEST_DATA_ERROR" => "Check <b>{POPULATE_DB_FIELD}</b> before populating tables with test data",
	"DB_HOST_ERROR" => "The hostname that you specified could not be found.",
	"DB_PORT_ERROR" => "Can't connect to database server using specified port.",
	"DB_USER_PASS_ERROR" => "The username or password you specified is not correct.",
	"DB_NAME_ERROR" => "Login settings were correct, but the database '{db_name}' could not be found.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP Aktualizace",
	"UPGRADE_NOTE" => "Poznámka: Zvažte prosím vytvoření zálohy databáze před pokračováním.",
	"UPGRADE_AVAILABLE_MSG" => "Aktualizace k dispozici",
	"UPGRADE_BUTTON" => "Aktualizovat na verzi {version_number} teď",
	"CURRENT_VERSION_MSG" => "Vaše aktuálně nainstalovaná verze",
	"LATEST_VERSION_MSG" => "Verze dostupná pro instalaci",
	"UPGRADE_RESULTS_MSG" => "Výsledky aktualizace",
	"SQL_SUCCESS_MSG" => "SQL dotaz úspěšný",
	"SQL_FAILED_MSG" => "SQL dotaz neúspěšný",
	"SQL_TOTAL_MSG" => "Spolu vykonaných SQL dotazů",
	"VERSION_UPGRADED_MSG" => "Vaše verze byla aktualizována na",
	"ALREADY_LATEST_MSG" => "Máte nainstalovanou nejaktuálnější verzi",
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
