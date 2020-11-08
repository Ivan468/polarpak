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
	"INSTALL_TITLE" => "Viart Shop asennus",

	"INSTALL_STEP_1_TITLE" => "Asennus: Vaihe 1",
	"INSTALL_STEP_1_DESC" => "Kiitos kun asennat ViArt ostoskortin. Ole hyvä ja täydennä alla olevat tiedot. Huomio että tietokannan tulisi jo olla olemassa. Jos asennat käyttäen ODBC/Microsoft Access, luo ensin DNS",
	"INSTALL_STEP_2_TITLE" => "Asennus: Vaihe 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Asennus: Vaihe 3",
	"INSTALL_STEP_3_DESC" => "Valitse sivun teema, tätä asetusta voit muuttaa myöhemmin",
	"INSTALL_FINAL_TITLE" => "Asennus: Valmis",
	"SELECT_DATE_TITLE" => "Valitse päiväyksen näyttö",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Tietokanta-asetukset",
	"DB_PROGRESS_MSG" => "Tietokannan luominen",
	"SELECT_PHP_LIB_MSG" => "Valitse PHP kirjasto",
	"SELECT_DB_TYPE_MSG" => "Valitse tietokannan tyyppi",
	"ADMIN_SETTINGS_MSG" => "Hallinnalliset asetukset",
	"DATE_SETTINGS_MSG" => "Aika/Päiväasetukset",
	"NO_DATE_FORMATS_MSG" => "Ei saatavilla",
	"INSTALL_FINISHED_MSG" => "Tässä vaiheessa perusasennus on valmis. Ole hyvä ja käytä hallinta-asetuksia muuttaaksesi muita toimintoja",
	"ACCESS_ADMIN_MSG" => "Päästäksesi hallintaosioon, paina tästä",
	"ADMIN_URL_MSG" => "Hallinnan osoite",
	"MANUAL_URL_MSG" => "Manuaaliosoite (URL)",
	"THANKS_MSG" => "Kiitos kun valitsit <b>ViArt SHOP:in</b>.",

	"DB_TYPE_FIELD" => "Tietokannan tyyppi",
	"DB_TYPE_DESC" => "Valitse <b>tietokannan tyyppi</b> jota käytät. Jos käytät SQL Serveriä tai Microsoft Accessia, valitse ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP Kirjasto",
	"DB_HOST_FIELD" => "Isäntänimi",
	"DB_HOST_DESC" => "Anna <b>nimi</b> tai <b>serverin IP osoite</b> jossa ViArt tietokanta on. Jos serveri on omalla koneellasti, luultavasti \"<b>localhost</b>\" ja jätä portti tyhjäksi. Tarvittaessa kysy palveluntarjoajaltasi lisätietoja tietokannoista.",
	"DB_PORT_FIELD" => "Portti",
	"DB_NAME_FIELD" => "Tietokannan nimi / DSN",
	"DB_NAME_DESC" => "Jos käytät tietokantaa kuten MySQL tai PostgreSQL ole hyvä ja anna <b>tietokannan nimi</b> mihin haluat ViArtin luovan taulukot. Tämän tietokannan pitää olla jo olemassa. Jos olet vain asentamassa testitarkoituksessa, yleensä on olemassa \"<b>test</b>\" tietokanta jota voit käyttää. Jos ei, ole hyvä ja luo esim tietokanta 'viart'. ",
	"DB_USER_FIELD" => "Käyttäjätunnus",
	"DB_PASS_FIELD" => "Salasana",
	"DB_USER_PASS_DESC" => "<b>Käyttäjätunnus</b> ja <b>Salasana</b> -Ole hyvä ja anna käyttäjätunnus ja salasana tietokannalle.Jos käytät paikallista konetta, käyttäjätunnus on tod.näk \"<b>root</b>\" ja salasana tyhjä. Tämä on ok testauksessa, mutta ei varsinaisessa asennuksessa!",
	"DB_PERSISTENT_FIELD" => "Jatkuva yhteys",
	"DB_PERSISTENT_DESC" => "MYSQL/Postgre jatkuva yhteys. Jos et tiedä mitä se tarkoittaa, on parempi olla koskematta tähän kohtaan",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Luo tietokanta",
	"DB_POPULATE_DESC" => "Luo tietokanta ja rakenne",
	"DB_TEST_DATA_FIELD" => "Testidata",
	"DB_TEST_DATA_DESC" => "Tekee testidataa taulukkoon",
	"ADMIN_EMAIL_FIELD" => "Hallinnan sähköposti",
	"ADMIN_LOGIN_FIELD" => "Hallinan sisäänkirjaus",
	"ADMIN_PASS_FIELD" => "Hallinan salasana",
	"ADMIN_CONF_FIELD" => "Vahvista salasana",
	"DATETIME_SHOWN_FIELD" => "Päivämäärän näyttö (sivustolla)",
	"DATE_SHOWN_FIELD" => "Päiväyksen näyttö (sivustolla)",
	"DATETIME_EDIT_FIELD" => "Päiväyksen näyttö (muokatessa)",
	"DATE_EDIT_FIELD" => "Päivämäärän näyttö (muokatessa)",
	"DATE_FORMAT_COLUMN" => "Päiväysmuoto",

	"DB_LIBRARY_ERROR" => "PHP toiminnot {db_library} eivät ole määriteltyinä. Tarkista tietokannan määritykset tiedostosta - php.ini.",
	"DB_CONNECT_ERROR" => "En saa yhteyttä tietokantaan, tarkista asetukset",
	"INSTALL_FINISHED_ERROR" => "Asennus on jo tehty!",
	"WRITE_FILE_ERROR" => "Tiedostoon<b>'includes/var_definition.php'</b>. Ei voi kirjoittaa. Vaihda asetukset ennen jatkamista",
	"WRITE_DIR_ERROR" => "Kansioon <b>'includes/'</b>. Ei voi kirjoittaa. Vaihda asetuksia ennen jatkamista",
	"DUMP_FILE_ERROR" => "Dumppitiedostoa '{file_name}' ei löytynyt",
	"DB_TABLE_ERROR" => "Taulukkoa '{table_name}' ei löytynyt. Tee tarvittavat muutokset tietokantaan.",
	"TEST_DATA_ERROR" => "Tarkia<b>{POPULATE_DB_FIELD}</b> ennen testitaulukkojen tekemistä",
	"DB_HOST_ERROR" => "Isäntänimeä jonka annoit ei löydy",
	"DB_PORT_ERROR" => "Yhteyttä tähän porttiin (MYSQL) ei voitu luoda",
	"DB_USER_PASS_ERROR" => "Salasana tai käyttäjätunnus ovat väärin",
	"DB_NAME_ERROR" => "Sisäänkirjaus onnistui, mutta tietokantaa '{db_name}' ei löytynyt",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP päivitys",
	"UPGRADE_NOTE" => "HUOM! Tee varmuuskopiot ennen jatkamista",
	"UPGRADE_AVAILABLE_MSG" => "Tietokannan päivitys saatavilla",
	"UPGRADE_BUTTON" => "Päivitä tietokanta versioon {version_number} nyt",
	"CURRENT_VERSION_MSG" => "Nyt asennettu versio",
	"LATEST_VERSION_MSG" => "Versio saatavilla",
	"UPGRADE_RESULTS_MSG" => "Päivityksen tulos",
	"SQL_SUCCESS_MSG" => "SQL kyselyt onnistuivat",
	"SQL_FAILED_MSG" => "SQL kyselyt epäonnistuivat",
	"SQL_TOTAL_MSG" => "SQL kyselyitä suoritettu",
	"VERSION_UPGRADED_MSG" => "Tietokantasi on päivitetty",
	"ALREADY_LATEST_MSG" => "Sinulla on jo viimeisin versio",
	"DOWNLOAD_NEW_MSG" => "Uusin versio havaittu",
	"DOWNLOAD_NOW_MSG" => "Lataa versio {version_number} nyt",
	"DOWNLOAD_FOUND_MSG" => "Huomasimme, että uusi versio {version_number} on saatavilla. Klikkaa linkkiä aloittaaksesi latauksen.ladattuasi ja päivitettyäsi, muista käynnistää päivitystoiminto uudelleen",
	"NO_XML_CONNECTION" => "Varoitus! Yhteyttä osoitteeseen 'http://www.viart.com/' ei voida luoda!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
