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
	"INSTALL_TITLE" => "ViArt SHOP installeerimine",

	"INSTALL_STEP_1_TITLE" => "Installeerimine: Samm 1",
	"INSTALL_STEP_1_DESC" => "Täname, et valisid ViArt SHOP'i. Installeerimise jätkamiseks, täida palun allolevad vajalikud andmed. Pane tähele, et valiksid juba olemasoleva andmebaasi. Kui installeerid andmebaasi, mis kasutab ODBC'd, näiteks MC Access, peaksid enne jätkamist looma sellele DSN'i.",
	"INSTALL_STEP_2_TITLE" => "Installeerimine: Samm 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Installeerimine: Samm 3",
	"INSTALL_STEP_3_DESC" => "Palun vali veebilehe paigutuse. Sa võid pärast paigutust muuta.",
	"INSTALL_FINAL_TITLE" => "Installeerimine: Lõpp",
	"SELECT_DATE_TITLE" => "Vali kuupäeva formaat",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Andmebaasi seaded",
	"DB_PROGRESS_MSG" => "Andmebaasi struktuuri loomise protsess",
	"SELECT_PHP_LIB_MSG" => "Vali PHP Library ",
	"SELECT_DB_TYPE_MSG" => "Vali andmebaasi tüüp",
	"ADMIN_SETTINGS_MSG" => "Administratiivsed seaded",
	"DATE_SETTINGS_MSG" => "Kuupäeva formaadid",
	"NO_DATE_FORMATS_MSG" => "Kuupäeva formaadid ei ole saadaval",
	"INSTALL_FINISHED_MSG" => "Nüüdseks on lõppenud sinu põhiinstalleerimine. Palun kontrolli administreerimise sektsiooni seadeid ning tee vajalikud muudatused.",
	"ACCESS_ADMIN_MSG" => "Administreerimise sektsiooni sisenemiseks kliki siia",
	"ADMIN_URL_MSG" => "Administratsiooni URL",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Täname, et valisid <b>ViArt SHOP</b>",

	"DB_TYPE_FIELD" => "Andmebaasi tüüp",
	"DB_TYPE_DESC" => "Palun vali <b>andmebaasi tüüp</b>, mida sa kasutad. Kui kasutad SQL serverit või Microsoft Access'i, vali palun ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP Library ",
	"DB_HOST_FIELD" => "Host'i nimi",
	"DB_HOST_DESC" => "Palun sisesta <b>nimi</b> või <b>serveri IP aadress</b>, millel hakkab töötama sinu ViArt andmebaas. Kui sinu andmebaas töötab sinu lokaalsel personaalarvutil, siis võid tõenäoliselt selle jätta kui \"<b>localhost</b>\" ja port tühjaks. Kui kasutad andmebaasi, mida pakub sulle hosting firma, vaata palun selle dokumentatsioonist serveri seadeid.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Andmebaasi nimi / DSN",
	"DB_NAME_DESC" => "Kui kasutad MySQL või PostgreSQL sarnast andmebaasi, siis sisesta palun <b>andmebaasi nimi</b> , kuhu sa tahad, et ViArt loob oma tabelid. See andmebaas peab juba eksisteerima. Kui installeerid ViArt'i oma lokaalsesse personaalarvutisse testimise eesmärgil, siis enamikel süsteemidel on \"<b>test</b>\" andmebaas, mida saad kasutada. Kui mitte, loo andmebaas \"viart\" ja kasuta seda. Kui kasutad Microsoft Access'i või SQL serverit, siis andmebaasi nimi peaks olema <b>DSN'i nimi</b>, mille oled seadnud Data Sources (ODBC)sektsioonis oma arvuti Control Panel'is.",
	"DB_USER_FIELD" => "Kasutajanimi",
	"DB_PASS_FIELD" => "Parool",
	"DB_USER_PASS_DESC" => "<b>Kasutajanimi</b> ja <b>parool</b> - palun sisesta kasutajanimi ja parool, mida tahad kasutada andmebaasi sisenemiseks. Kui kasutad lokaalset test installeerimist, on kasutajanimi tõenäoliselt \"<b>root</b>\" ja parool tühi. See sobib testimiseks, kuid pane tähele, et see ei ole ohutu tootmise serverites.",
	"DB_PERSISTENT_FIELD" => "Püsiühendus",
	"DB_PERSISTENT_DESC" => "Et kasutada MySQL või Postgre püsiühendusi, tee märge sellesse ruutu. Kui sa ei tea, mida see tähendab, siis parem oleks jätta see ruut tühjaks.",
	"DB_CREATE_DB_FIELD" => "Loo andmebaas",
	"DB_CREATE_DB_DESC" => "Andmebaasi loomiseks märgi võimalusel ära see ruut. Töötab ainult MySQL ja Postgre puhul.",
	"DB_POPULATE_FIELD" => "Asusta andmebaas",
	"DB_POPULATE_DESC" => "Andmebaasi tabeli struktuuri loomiseks ja selle asustamiseks andmetega tee märkeruutu märge",
	"DB_TEST_DATA_FIELD" => "Testandmed",
	"DB_TEST_DATA_DESC" => "Testandmete lisamiseks oma andmebaasi tee märkeruutu märge",
	"ADMIN_EMAIL_FIELD" => "Administraatori e-post",
	"ADMIN_LOGIN_FIELD" => "Administraatori kasutajanimi",
	"ADMIN_PASS_FIELD" => "Administraatori parool",
	"ADMIN_CONF_FIELD" => "Kinnita parool",
	"DATETIME_SHOWN_FIELD" => "Kuupäeva ja aja formaat (näidatud veebilehel)",
	"DATE_SHOWN_FIELD" => "Kuupäeva formaat (näidatud veebilehel)",
	"DATETIME_EDIT_FIELD" => "Kuupäeva ja aja formaat (muutmiseks)",
	"DATE_EDIT_FIELD" => "Kuupäea formaat (muutmiseks)",
	"DATE_FORMAT_COLUMN" => "Kuupäeva formaat",

	"DB_LIBRARY_ERROR" => "{db_library} PHP funktsioonid ei ole määratletud. Palun kontrolli oma andmebaasi seadeid konfiguratsiooni failis – php.ini.",
	"DB_CONNECT_ERROR" => "Ei saa ühendada andmebaasiga. Palun kontrolli oma andmebaasi parameetreid.",
	"INSTALL_FINISHED_ERROR" => "Installeerimise protsess juba lõppenud.",
	"WRITE_FILE_ERROR" => "Ei ole kirjalikku luba failile <b>'includes/var_definition.php'</b>. Palun muuda faili luba enne jätkamist.",
	"WRITE_DIR_ERROR" => "Ei ole kirjalikku luba kaustale <b>'includes/'</b>. Palun muuda kausta luba enne jätkamist.",
	"DUMP_FILE_ERROR" => "Dump faili '{file_name}' ei leitud.",
	"DB_TABLE_ERROR" => "Tabelit '{table_name}' ei leitud. Palun sisesta andmebaasi vajalikud andmed.",
	"TEST_DATA_ERROR" => "Kontrolli <b>{POPULATE_DB_FIELD}</b> enne testandmete sisestamist andmebaasi",
	"DB_HOST_ERROR" => "Ei leitud sinu täpsustatud host'i nime.",
	"DB_PORT_ERROR" => "Ei saa ühendada andmebaasi serveriga kasutades antud porti.",
	"DB_USER_PASS_ERROR" => "Antud kasutajanimi või parool ei ole õige.",
	"DB_NAME_ERROR" => "Login seaded olid õiged, kuid andmebaasi '{db_name}'  ei leitud.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP'i uuendamine",
	"UPGRADE_NOTE" => "Teade: Palun kaalu andmebaasi varukoopia tegemise võimalust enne jätkamist.",
	"UPGRADE_AVAILABLE_MSG" => "Andmebaasi uuendus on saadaval",
	"UPGRADE_BUTTON" => "Uuenda andmebaas {version_number} versiooniks nüüd",
	"CURRENT_VERSION_MSG" => "Preagu installeeritud versioon",
	"LATEST_VERSION_MSG" => "Versioon installeerimiseks saadaval",
	"UPGRADE_RESULTS_MSG" => "Uuendamise tulemused",
	"SQL_SUCCESS_MSG" => "SQL päringud õnnestusid",
	"SQL_FAILED_MSG" => "SQL päringud ebaõnnestusid",
	"SQL_TOTAL_MSG" => "Kõik SQL päringud lõpetatud",
	"VERSION_UPGRADED_MSG" => "Sinu andmebaas on uuendatud",
	"ALREADY_LATEST_MSG" => "Sul juba on uusim versioon",
	"DOWNLOAD_NEW_MSG" => "Avastatud on uus versioon",
	"DOWNLOAD_NOW_MSG" => "Lae alla  {version_number} versioon nüüd",
	"DOWNLOAD_FOUND_MSG" => "Oleme avastanud, et uus {version_number} versioon on saadaval allalaadimiseks. Palun kliki allolevale lingile, et alustada allalaadimist. Pärast allalaadimise lõppemist ning failide asendamist ära unusta läbi teha uuendamist.",
	"NO_XML_CONNECTION" => "Hoiatus! Ühendus 'http://www.viart.com/' ei ole saadaval!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
