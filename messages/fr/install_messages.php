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
	"INSTALL_TITLE" => "Installation De Magasin De ViArt",

	"INSTALL_STEP_1_TITLE" => "Installation : Étape 1",
	"INSTALL_STEP_1_DESC" => "Merci de choisir le magasin de ViArt. Afin d'accomplir ceci installez complètent svp les détails demandés ci-dessous. Veuillez noter que la base de données que vous installez sur devrait déjà exister. Si vous installez sur une base de données qui emploie ODBC, par exemple MME. accès vous devriez d'abord créer un DSN pour lui avant la marche à suivre.",
	"INSTALL_STEP_2_TITLE" => "Installation : Étape 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Installation : Étape 3",
	"INSTALL_STEP_3_DESC" => "Veuillez choisir la disposition d'emplacement. Vous pourrez changer la disposition après.",
	"INSTALL_FINAL_TITLE" => "Installation : Final",
	"SELECT_DATE_TITLE" => "Choisissez Le Format De Date",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Arrangements De Base de données",
	"DB_PROGRESS_MSG" => "Populating database structure progress",
	"SELECT_PHP_LIB_MSG" => "Choisissez La Bibliothèque de PHP",
	"SELECT_DB_TYPE_MSG" => "Choisissez Le Type De Base de données",
	"ADMIN_SETTINGS_MSG" => "Arrangements Administratifs",
	"DATE_SETTINGS_MSG" => "Formats De Date",
	"NO_DATE_FORMATS_MSG" => "Aucuns formats de date disponibles",
	"INSTALL_FINISHED_MSG" => "En ce moment votre installation de base est complète. Veuillez être sûr de vérifier les arrangements dans la pièce d'administration et de faire tous les changements requis.",
	"ACCESS_ADMIN_MSG" => "Pour accéder au clic de pièce d'administration ici",
	"ADMIN_URL_MSG" => "URL D'Administration",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Merci de choisir le magasin de ViArt.",

	"DB_TYPE_FIELD" => "Type De Base de données",
	"DB_TYPE_DESC" => "Please select the <b>type of database</b> that you are using. If you using SQL Server or Microsoft Access, please select ODBC.",
	"DB_PHP_LIB_FIELD" => "Bibliothèque de PHP",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Please enter the <b>name</b> or <b>IP address of the server</b> on which your ViArt database will run. If you are running your database on your local PC then you can probably just leave this as \"<b>localhost</b>\" and the port blank. If you using a database provided by your hosting company, please see your hosting company's documentation for the server settings.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Nom De Base de données/DSN",
	"DB_NAME_DESC" => "If you are using a database such as MySQL or PostgreSQL then please enter the <b>name of the database</b> where you would like ViArt to create its tables. This database must exist already. If you are just installing ViArt for testing purposes on your local PC then most systems have a \"<b>test</b>\" database you can use. If not, please create a database such as \"viart\" and use that. If you are using Microsoft Access or SQL Server then the Database Name should be the <b>name of the DSN</b> that you have set up in the Data Sources (ODBC) section of your Control Panel.",
	"DB_USER_FIELD" => "Username",
	"DB_PASS_FIELD" => "Mot de passe",
	"DB_USER_PASS_DESC" => "<b>Username</b> and <b>Password</b> - please enter the username and password you want to use to access the database. If you are using a local test installation the username is probably \"<b>root</b>\" and the password is probably blank. This is fine for testing, but please note that this is not secure on production servers.",
	"DB_PERSISTENT_FIELD" => "Raccordement Persistant",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Peuplez le DB",
	"DB_POPULATE_DESC" => "pour créer des tables structurez-et peuplez-l'avec le coutil de données le checkbox",
	"DB_TEST_DATA_FIELD" => "Test Data",
	"DB_TEST_DATA_DESC" => "to add some test data to your database tick the checkbox",
	"ADMIN_EMAIL_FIELD" => "Email D'Administrateur",
	"ADMIN_LOGIN_FIELD" => "Ouverture D'Administrateur",
	"ADMIN_PASS_FIELD" => "Mot de passe D'Administrateur",
	"ADMIN_CONF_FIELD" => "Confirmez Le Mot de passe",
	"DATETIME_SHOWN_FIELD" => "Format de Datetime (montré sur l'emplacement)",
	"DATE_SHOWN_FIELD" => "Format de date (montré sur l'emplacement)",
	"DATETIME_EDIT_FIELD" => "Format de Datetime (pour éditer)",
	"DATE_EDIT_FIELD" => "Format de date (pour éditer)",
	"DATE_FORMAT_COLUMN" => "Format De Date",

	"DB_LIBRARY_ERROR" => "PHP functions for {db_library} are not defined. Please check your database settings in your configuration file - php.ini.",
	"DB_CONNECT_ERROR" => "Can't connect to database. Please check your database parameters.",
	"INSTALL_FINISHED_ERROR" => "Installation process already finished.",
	"WRITE_FILE_ERROR" => "Don't have writable permission to file <b>'includes/var_definition.php'</b>. Please change file permissions before you continue.",
	"WRITE_DIR_ERROR" => "Don't have writable permission to folder <b>'includes/'</b>. Please change folder permissions before you continue.",
	"DUMP_FILE_ERROR" => "Dump file '{file_name}' wasn't found.",
	"DB_TABLE_ERROR" => "Table '{table_name}' wasn't found. Please populate the database with the necessary data.",
	"TEST_DATA_ERROR" => "Check <b>{POPULATE_DB_FIELD}</b> before populating tables with test data",
	"DB_HOST_ERROR" => "The hostname that you specified could not be found.",
	"DB_PORT_ERROR" => "Can't connect to database server using specified port.",
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
