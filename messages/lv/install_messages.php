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
	"INSTALL_TITLE" => "ViArt SHOP instalācija",

	"INSTALL_STEP_1_TITLE" => "Instalācija. Solis 1.",
	"INSTALL_STEP_1_DESC" => "Paldies, ka esat izvēlējušies ViArt SHOP risinājumu. Lai pabeigtu instalāciju, lūdzu, aizpildiet zemāk pieprasīto informāciju. Lūdzu, atceraties, ka datubāzei jābūt izveidotai pirms instalācijas. Ja instalējat datubāzi, kas izmanto ODBC, piemēram, MS Access, lūdzu, izveidojiet DSN pirms turpiniet tālāk.",
	"INSTALL_STEP_2_TITLE" => "Instalācija. Solis 2.",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Instalācija. Solis 3.",
	"INSTALL_STEP_3_DESC" => "Lūdzu, izvēlaties lapas izskatu. Jums būs to iespēja vēlāk vēl koriģēt.",
	"INSTALL_FINAL_TITLE" => "Instalācija: Pabeigta",
	"SELECT_DATE_TITLE" => "Izvēlaties datuma formātu",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Datubāzes uzstādījumi",
	"DB_PROGRESS_MSG" => "Datubāzes struktūras izveides process",
	"SELECT_PHP_LIB_MSG" => "Izvēlaties PHP Library",
	"SELECT_DB_TYPE_MSG" => "Izvēlaties Datubāzes veidu",
	"ADMIN_SETTINGS_MSG" => "Administratora uzstādījumi",
	"DATE_SETTINGS_MSG" => "Datuma formāts",
	"NO_DATE_FORMATS_MSG" => "Datuma formāti nav pieejami",
	"INSTALL_FINISHED_MSG" => "Šajā brīdī Jūsu instalācija ir pabeigta. Lūzu, pārliecinaties, kādi ir uzstādītie parametri administrācijas sadaļā un veiciet nepieciešamās izmaiņas.",
	"ACCESS_ADMIN_MSG" => "Lai piekļūtu Administratora sadaļai, spiediet šeit",
	"ADMIN_URL_MSG" => "Administratora adrese URL",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Paldies Jums, ka esat izvēlējušies <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Datubāzes veids",
	"DB_TYPE_DESC" => "Please select the <b>type of database</b> that you are using. If you using SQL Server or Microsoft Access, please select ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP Library",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Please enter the <b>name</b> or <b>IP address of the server</b> on which your ViArt database will run. If you are running your database on your local PC then you can probably just leave this as \"<b>localhost</b>\" and the port blank. If you using a database provided by your hosting company, please see your hosting company's documentation for the server settings.",
	"DB_PORT_FIELD" => "Ports",
	"DB_NAME_FIELD" => "Datubāzes nosaukums / DSN",
	"DB_NAME_DESC" => "If you are using a database such as MySQL or PostgreSQL then please enter the <b>name of the database</b> where you would like ViArt to create its tables. This database must exist already. If you are just installing ViArt for testing purposes on your local PC then most systems have a \"<b>test</b>\" database you can use. If not, please create a database such as \"viart\" and use that. If you are using Microsoft Access or SQL Server then the Database Name should be the <b>name of the DSN</b> that you have set up in the Data Sources (ODBC) section of your Control Panel.",
	"DB_USER_FIELD" => "Lietotājvārds",
	"DB_PASS_FIELD" => "Parole",
	"DB_USER_PASS_DESC" => "<b>Username</b> and <b>Password</b> - please enter the username and password you want to use to access the database. If you are using a local test installation the username is probably \"<b>root</b>\" and the password is probably blank. This is fine for testing, but please note that this is not secure on production servers.",
	"DB_PERSISTENT_FIELD" => "Stabils savienojums",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Apstrādāt datubāzi",
	"DB_POPULATE_DESC" => "izveidot datubāzes tabulas struktūru, ievietot datus tabulās, atzīmējiet ar ķeksīti",
	"DB_TEST_DATA_FIELD" => "Test Data",
	"DB_TEST_DATA_DESC" => "to add some test data to your database tick the checkbox",
	"ADMIN_EMAIL_FIELD" => "Administratora e-pasts",
	"ADMIN_LOGIN_FIELD" => "Administratora lietotājs",
	"ADMIN_PASS_FIELD" => "Administratora parole",
	"ADMIN_CONF_FIELD" => "Apstiprināt paroli",
	"DATETIME_SHOWN_FIELD" => "Datuma un laiks formāts (tiks rādīts mājas lapā)",
	"DATE_SHOWN_FIELD" => "Datuma formāts (tiks rādīts mājas lapā)",
	"DATETIME_EDIT_FIELD" => "Datuma un laika formāts (tiks rādīts Administratora lapā)",
	"DATE_EDIT_FIELD" => "Datuma formāts (tiks rādīts Administratora lapā)",
	"DATE_FORMAT_COLUMN" => "Datuma formāts",

	"DB_LIBRARY_ERROR" => "PHP functions for {db_library} are not defined. Please check your database settings in your configuration file - php.ini.",
	"DB_CONNECT_ERROR" => "Nevar pieslēgties datubāzei. Lūdzu, pārbaudiet datubāzes uzstādījumus.",
	"INSTALL_FINISHED_ERROR" => "Instalācijas process ir pabeigts.",
	"WRITE_FILE_ERROR" => "Failam <b>'includes/var_definition.php'</b> nav uzstādītas visas lietošanas tiesības. Pirms turpiniet tālāk, mainīt uzstādītos faila parametrus.",
	"WRITE_DIR_ERROR" => "Folderim <b>'includes/'</b> nav uzstādītas visas lietošanas tiesības. Pirms turpināt tālāk, mainiet foldera uzstādītos pieejas parametrus",
	"DUMP_FILE_ERROR" => "Dump fails '{file_name}' nav atrasts.",
	"DB_TABLE_ERROR" => "Tabula '{table_name}' nav atrasta. Lūdzu, papildiniet datubāzi ar nepieciešamo informāciju.",
	"TEST_DATA_ERROR" => "Check <b>{POPULATE_DB_FIELD}</b> before populating tables with test data",
	"DB_HOST_ERROR" => "The hostname that you specified could not be found.",
	"DB_PORT_ERROR" => "Can't connect to database server using specified port.",
	"DB_USER_PASS_ERROR" => "The username or password you specified is not correct.",
	"DB_NAME_ERROR" => "Login settings were correct, but the database '{db_name}' could not be found.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP Jauninājumi (Upgrade)",
	"UPGRADE_NOTE" => "Piezīme: Pirms turpināt, lūdzu, izveidojiet datubāzes rezerves versiju.",
	"UPGRADE_AVAILABLE_MSG" => "Pieejamie jauninājumi (Upgrade)",
	"UPGRADE_BUTTON" => "Veikt jauninājumus uz {version_number} versiju",
	"CURRENT_VERSION_MSG" => "Patreizējā instalācijas versija",
	"LATEST_VERSION_MSG" => "Pieejamā instalācijas versija",
	"UPGRADE_RESULTS_MSG" => "Jauninājumu rezultāti",
	"SQL_SUCCESS_MSG" => "SQL pieprasījumi izpildīti",
	"SQL_FAILED_MSG" => "SQL pieprasījumi nav izpildīti",
	"SQL_TOTAL_MSG" => "Kopā izpildītie SQL pieprasījumi",
	"VERSION_UPGRADED_MSG" => "Jūsu programmas versija ir atjaunota uz",
	"ALREADY_LATEST_MSG" => "Jums ir pieejama jaunākā programmas versija",
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
