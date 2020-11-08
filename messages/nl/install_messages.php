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
	// installation messages
	"INSTALL_TITLE" => "ViArt SHOP Installatie",

	"INSTALL_STEP_1_TITLE" => "Installatie: Stap 1",
	"INSTALL_STEP_1_DESC" => "Bedankt dat u voor ViArt SHOP heeft gekozen. Vul a.u.b. onderstaande gegevens in om de installatie succesvol af te kunnen ronden . Let er op dat de database die u installeert ook daadwerkelijk bestaat. Als u naar een database installeert die gebruik maakt van ODBC, of MS Access dient u eerst een DSN te maken alvorens door te gaan.",
	"INSTALL_STEP_2_TITLE" => "Installatie: Stap 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Installatie: Stap 3",
	"INSTALL_STEP_3_DESC" => "Selecteer a.u.b. een site-layout. Deze kunt u later altijd nog wijzigen.",
	"INSTALL_FINAL_TITLE" => "Installatie: Afsluiting",
	"SELECT_DATE_TITLE" => "Selecteer Datumformaat",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Database Instellingen",
	"DB_PROGRESS_MSG" => "Populating database structure progress",
	"SELECT_PHP_LIB_MSG" => "Selecteer PHP bibliotheek",
	"SELECT_DB_TYPE_MSG" => "Selecteer database type",
	"ADMIN_SETTINGS_MSG" => "Administratieve instellingen",
	"DATE_SETTINGS_MSG" => "Datumformaten",
	"NO_DATE_FORMATS_MSG" => "Geen datumformaten beschikbaar",
	"INSTALL_FINISHED_MSG" => "Op dit punt is uw basisinsstallatie gereed. Controleer a.u.b. nu uw instellingen in het administratiedeel en doe de noodzakelijke aanpassingen.",
	"ACCESS_ADMIN_MSG" => "Voor toegang tot het administratiedeel klik hier",
	"ADMIN_URL_MSG" => "Administratie URL",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Bedankt voor het kiezen van<b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Database Type",
	"DB_TYPE_DESC" => "Please select the <b>type of database</b> that you are using. If you using SQL Server or Microsoft Access, please select ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP Library",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Please enter the <b>name</b> or <b>IP address of the server</b> on which your ViArt database will run. If you are running your database on your local PC then you can probably just leave this as \"<b>localhost</b>\" and the port blank. If you using a database provided by your hosting company, please see your hosting company's documentation for the server settings.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Database Name / DSN",
	"DB_NAME_DESC" => "If you are using a database such as MySQL or PostgreSQL then please enter the <b>name of the database</b> where you would like ViArt to create its tables. This database must exist already. If you are just installing ViArt for testing purposes on your local PC then most systems have a \"<b>test</b>\" database you can use. If not, please create a database such as \"viart\" and use that. If you are using Microsoft Access or SQL Server then the Database Name should be the <b>name of the DSN</b> that you have set up in the Data Sources (ODBC) section of your Control Panel.",
	"DB_USER_FIELD" => "Username",
	"DB_PASS_FIELD" => "Password",
	"DB_USER_PASS_DESC" => "<b>Username</b> and <b>Password</b> - please enter the username and password you want to use to access the database. If you are using a local test installation the username is probably \"<b>root</b>\" and the password is probably blank. This is fine for testing, but please note that this is not secure on production servers.",
	"DB_PERSISTENT_FIELD" => "Persistent Connection",
	"DB_PERSISTENT_DESC" => "to use MySQL persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Populate DB",
	"DB_POPULATE_DESC" => "to create the database table structure and populate it with data tick the checkbox",
	"DB_TEST_DATA_FIELD" => "Test Data",
	"DB_TEST_DATA_DESC" => "to add some test data to your database tick the checkbox",
	"ADMIN_EMAIL_FIELD" => "Administrateur Email",
	"ADMIN_LOGIN_FIELD" => "Administrateur Login",
	"ADMIN_PASS_FIELD" => "Administrateur Password",
	"ADMIN_CONF_FIELD" => "Bevestig Password",
	"DATETIME_SHOWN_FIELD" => "Datumtijd Formaat (getoond op site)",
	"DATE_SHOWN_FIELD" => "Datum Formaat (getoond op site)",
	"DATETIME_EDIT_FIELD" => "Datumtijd Formaat (bij bewerkingen)",
	"DATE_EDIT_FIELD" => "Datum Formaat (bij bewerkingen)",
	"DATE_FORMAT_COLUMN" => "Datum Formaat",

	"DB_LIBRARY_ERROR" => "PHP functions for {db_library} are not defined. Please check your database settings in your configuration file - php.ini.",
	"DB_CONNECT_ERROR" => "Can't connect to database. Please check your database parameters.",
	"INSTALL_FINISHED_ERROR" => "Installation process already finished.",
	"WRITE_FILE_ERROR" => "Don't have writable permission to file <b>'includes/var_definition.php'</b>. Please change file permissions before you continue.",
	"WRITE_DIR_ERROR" => "Don't have writable permission to folder <b>'includes/'</b>. Please change folder permissions before you continue.",
	"DUMP_FILE_ERROR" => "Dump file '{file_name}' wasn't found.",
	"DB_TABLE_ERROR" => "Table '{table_name}' wasn't found. Please populate the database with the necessary data.",
	"TEST_DATA_ERROR" => "Check <b>{POPULATE_DB_FIELD}</b> before populating tables with test data",
	"DB_HOST_ERROR" => "The hostname that you specified could not be found.",
	"DB_PORT_ERROR" => "Can't connect to MySQL server using specified port.",
	"DB_USER_PASS_ERROR" => "The username or password you specified is not correct.",
	"DB_NAME_ERROR" => "Login settings were correct, but the database '{db_name}' could not be found.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP Upgrade",
	"UPGRADE_NOTE" => "Note: Please consider making a database backup before proceeding.",
	"UPGRADE_AVAILABLE_MSG" => "Database upgrade available",
	"UPGRADE_BUTTON" => "Upgrade database to {version_number} now",
	"CURRENT_VERSION_MSG" => "Currently installed version",
	"LATEST_VERSION_MSG" => "Version available to install",
	"UPGRADE_RESULTS_MSG" => "Upgrade results",
	"SQL_SUCCESS_MSG" => "SQL queries succeed",
	"SQL_FAILED_MSG" => "SQL queries failed",
	"SQL_TOTAL_MSG" => "Total SQL queries executed",
	"VERSION_UPGRADED_MSG" => "Your database has been upgraded to",
	"ALREADY_LATEST_MSG" => "You already have the latest version",
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
