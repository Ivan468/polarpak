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
	"INSTALL_TITLE" => "Instalacija ViArt SHOP-a",

	"INSTALL_STEP_1_TITLE" => "Instalacija: Korak 1",
	"INSTALL_STEP_1_DESC" => "Hvala vam što ste izabrali ViArt SHOP. Kako bi ste nastavili insatalaciju, molimo vas prvo ispunite tražene detalje ispod. Nemojte zaboraviti da bi baza podataka koju ste izabrali već trebala postojati. Ako instalirate bazu podataka koja koristi ODBC, npr. MS Access, trebali biste prvo kreirati DSN za nju prije nego nastavite.",
	"INSTALL_STEP_2_TITLE" => "Instalacija: Korak 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Instalacija: Korak 3",
	"INSTALL_STEP_3_DESC" => "Odaberite izgled stranice. Moći ćete mijenjati izgled kasnije.",
	"INSTALL_FINAL_TITLE" => "Instalacija: završtak",
	"SELECT_DATE_TITLE" => "Odaberite format datuma",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Postavke Baze Podataka",
	"DB_PROGRESS_MSG" => "Napredak punjenja strukture baze podataka",
	"SELECT_PHP_LIB_MSG" => "Odaberite PHP knjižnicu",
	"SELECT_DB_TYPE_MSG" => "Odaberite Tip Baze Podataka",
	"ADMIN_SETTINGS_MSG" => "Postavke Administracije",
	"DATE_SETTINGS_MSG" => "Formati Datuma",
	"NO_DATE_FORMATS_MSG" => "Nema dostupnih formata datuma",
	"INSTALL_FINISHED_MSG" => "U ovom trenutku vaša osnovna instalacija je završena. Provjerite postavke u administraciji i napravite tražene promjene.",
	"ACCESS_ADMIN_MSG" => "Kako bi pristupili administraciji kiknite ovdje",
	"ADMIN_URL_MSG" => "URL Administracije",
	"MANUAL_URL_MSG" => "URL Manual",
	"THANKS_MSG" => "Hvala Vam što ste izabrali <b>ViArt SHOP</b>",

	"DB_TYPE_FIELD" => "Tip Baze Podataka",
	"DB_TYPE_DESC" => "Molim vas odaberite <b>type of database</b> koji koristite. Ako koristite SQL Server ili Microsoft Access, molimo odaberite ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP Knjižnica",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Molimo vas unesite b>name</b> ili <b>IP address of the server</b> na kojem će se baza podataka vašeg ViArta pokretati. Uko pokrećete vašu bazu podataka na vašem lokalnom PC-u, tada vjerovatno možete samo ostaviti kao \"<b>localhost</b>\" i prazan port. Ako koristite bazu podataka vaše hosting kompanije, pogledajte dakutmentaciju vaše hosting kompanije za postavke servera.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Ime Baze Podataka/DSN",
	"DB_NAME_DESC" => "Ako koristite bazu podataka kao što je MySQL ili PostgreSQL onda unesite <b>name of the database</b> gdje bi ste željeli da ViArt stvori svoje tablice. Ova baza podataka mora već postojati. Ako instalirate ViArt za potrebe testiranja na vašem lokalnom PC-u, onda većina sistema ima \"<b>test</b>\" bazu podataka koju može koristiti. Ako ne, kreirajte bazu podataka kao npr. \"viart\" i koristite tu. Ako koristite Microsoft Access ili SQL Server tada ime baze podataka treba biti <b>name of the DSN</b> koju ste postavili u sekciji Data Sources (ODBC) na vašoj upravljačkoj ploči.",
	"DB_USER_FIELD" => "Korisničko ime",
	"DB_PASS_FIELD" => "Lozinka",
	"DB_USER_PASS_DESC" => "<b>Username</b> i <b>Password</b> - Molimo vas unesite korisničko ime i lozinku koju želite koristiti kako bi pristupili bazi podataka. Ako koristite lokalnu testnu instalaciju korisničko ime bi vjerovatno trebalo biti \"<b>root</b>\" a lozinka ostati prazna. Ovo je u redu za testiranje, ali zapamtite da nije sigurno da servere proizvoda.",
	"DB_PERSISTENT_FIELD" => "Persistent Conection",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Kreiraj BP",
	"DB_CREATE_DB_DESC" => "za kreiranje baze podataka ako je moguće, označite ovu kućicu. Radi samo za MySQL i Postgre",
	"DB_POPULATE_FIELD" => "Popuni BP",
	"DB_POPULATE_DESC" => "kako bi kreirali bazu podataka tablično strukturiranu i kako bi je popunili označite ovu kućicu",
	"DB_TEST_DATA_FIELD" => "Testni Podatci",
	"DB_TEST_DATA_DESC" => "kako bi dodali neke testne podatke u vašu bazu podataka, označite kućicu",
	"ADMIN_EMAIL_FIELD" => "Email Administratora",
	"ADMIN_LOGIN_FIELD" => "Prijava Administratora",
	"ADMIN_PASS_FIELD" => "Lozinka Administratora",
	"ADMIN_CONF_FIELD" => "Potvrdite Loziku",
	"DATETIME_SHOWN_FIELD" => "Format datumasata (prikazan na stranici)",
	"DATE_SHOWN_FIELD" => "Format datuma (prikazan na stranici)",
	"DATETIME_EDIT_FIELD" => "Format datumsata (za uređivanje)",
	"DATE_EDIT_FIELD" => "Format datuma (za uređivanje)",
	"DATE_FORMAT_COLUMN" => "Format Datuma",

	"DB_LIBRARY_ERROR" => "PHP funkcije za {db_library} nisu definirane. Molimo provjerite postavke paze podataka u vašoj konfiguracijskoj datoteci - php.ini",
	"DB_CONNECT_ERROR" => "Ne može se spojiti na bazu podataka. Molimo provjerite parametre baze podataka",
	"INSTALL_FINISHED_ERROR" => "Instalacijski proces je gotov.",
	"WRITE_FILE_ERROR" => "Nemate pisano dopuštenje za datoteku <b>'includes/var_definition.php'</b>. Prije nego nastavite, promijenite dopuštenja za datoteku.",
	"WRITE_DIR_ERROR" => "Nemate pisano dopuštenje za mapu <b>'includes/'</b>. Prije nego nastavite, promijenite dopuštenja za mapu",
	"DUMP_FILE_ERROR" => "Dump datoteka '{file_name} nije pronađeno.",
	"DB_TABLE_ERROR" => "Tablica '{table_name}' nije pronađeno. Popunite bazu podataka sa potrebnim podatcima",
	"TEST_DATA_ERROR" => "pProvjerite <b>{POPULATE_DBFIELD}</b> prije nego popunite tablicu sa testnim podatcima.",
	"DB_HOST_ERROR" => "Hostname koje ste naveli nemože biti pronađeno",
	"DB_PORT_ERROR" => "Nemože se spojiti na bazu podataka koristeći specificirani port.",
	"DB_USER_PASS_ERROR" => "Korisničko ime ili lozinka koje ste unijeli nisu točni.",
	"DB_NAME_ERROR" => "Postavke prijave su točni, međutim baza podataka '{db:name}' nije pronađena.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP Unapređenje",
	"UPGRADE_NOTE" => "Napomena: Molim vas razmislite o stvaranju dodatne (backup) baze podataka prije nastavka.",
	"UPGRADE_AVAILABLE_MSG" => "Dostupno unapređenje baze podataka",
	"UPGRADE_BUTTON" => "Unaprijedi bazu podataka u {version_number} sada",
	"CURRENT_VERSION_MSG" => "Trenutno instalirana verzija",
	"LATEST_VERSION_MSG" => "Verzija dostupna za instalaciju",
	"UPGRADE_RESULTS_MSG" => "Rezultati unapređenja",
	"SQL_SUCCESS_MSG" => "SQL upiti uspješni",
	"SQL_FAILED_MSG" => "SQL upiti neuspješni",
	"SQL_TOTAL_MSG" => "Ukupni SQL upiti izvršeni",
	"VERSION_UPGRADED_MSG" => "Vaša baza podataka je uspješno unaprijeđena u ",
	"ALREADY_LATEST_MSG" => "Već imate najnoviju verziju",
	"DOWNLOAD_NEW_MSG" => "Nova verzija je pronađena",
	"DOWNLOAD_NOW_MSG" => "Preuzeti verziju {version_number} sada",
	"DOWNLOAD_FOUND_MSG" => "Pronađena je nova {version_number} verzija koju je moguće preuzeti. Kliknite na link ispod kako bi započeli sa preuzimanjem. Nakon što završite sa preuzimanjem i nakon što zamjenite datoteke, nemojte zaboraviti pokrenuti Rutinu Unapređenja (Upgrade routine) ponovno.",
	"NO_XML_CONNECTION" => "Upozorenje! Nije moguć pristup 'http://www.viart.com/'!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "Završi korisnički ugovor o nadogradnji",
	"AGREE_LICENSE_AGREEMENT_MSG" => "Pročitao sam i slažem se s Ugovorom o licenci",
	"READ_LICENSE_AGREEMENT_MSG" => "Klikni ovdje i pročitaj Ugovor o licenci",
	"LICENSE_AGREEMENT_ERROR" => "Molimo vas prvo pročitajte i složite se sa Ugovorom o licenci kako bi nastavili",

);
$va_messages = array_merge($va_messages, $messages);
