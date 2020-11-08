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
	"INSTALL_TITLE" => "ViArt SHOP Inštalácia",

	"INSTALL_STEP_1_TITLE" => "Inštalácia: Krok 1",
	"INSTALL_STEP_1_DESC" => "Ďakujeme, že ste si vybrali ViArt SHOP. Aby ste mohli dokončiť inštaláciu, vyplňte nasledovný formulár. Nezabudnite, že databáza musí už existovať. Ak inštalujete na databázu, ktorá používa ODBC ako napríklad MS Access, mali by ste najprv vytvoriť DSN, až potom pokračovať",
	"INSTALL_STEP_2_TITLE" => "Inštalácia: Krok 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Inštalácia: Krok 3",
	"INSTALL_STEP_3_DESC" => "Prosím vyberte si vzhľad stránky. Neskôr ho môžete zmeniť.",
	"INSTALL_FINAL_TITLE" => "Inštalácia: Posledný krok",
	"SELECT_DATE_TITLE" => "Vyberte formát dátumu",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Nastavenia databázy",
	"DB_PROGRESS_MSG" => "Stav napĺňania štruktúry databázy",
	"SELECT_PHP_LIB_MSG" => "Vyberte PHP knižnicu",
	"SELECT_DB_TYPE_MSG" => "Vyberte typ databázy",
	"ADMIN_SETTINGS_MSG" => "Administračné nastavenia",
	"DATE_SETTINGS_MSG" => "Formáty dátumu",
	"NO_DATE_FORMATS_MSG" => "Žiadne formáty dátumu nie sú k dispozícii",
	"INSTALL_FINISHED_MSG" => "V tomto okamihu je Vaša inštalácia ukončená. Prosím skontrolujte nastavenia v administračných nastaveniach a urobte potrebné nastavenia.",
	"ACCESS_ADMIN_MSG" => "Pre prístup k administračným nastaveniam kliknite sem",
	"ADMIN_URL_MSG" => "Administračná URL",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Ďakujeme, že ste si vybrali <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Typ databázy",
	"DB_TYPE_DESC" => "Prosím vyberte  <b>typ databázy</b>,ktorú používate. Ak používate MS Access alebo SQL Server, vyberte ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP knižnica",
	"DB_HOST_FIELD" => "Názov hostiteľa",
	"DB_HOST_DESC" => "Zadajte prosím <b>meno</b> alebo <b>IP adresu servera</b> na ktorom pobeží databáza Viart. Ak beží databáza na rovnakom počítači ako web server, ponechajte v tomto poli \"<b>localhost</b>\" a číslo portu nechajte prázdne. Ak používate databázu od vášho poskytovateľa, použite údaje, ktoré vám pri vytvorení databázy poslal.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Meno databázy / DSN",
	"DB_NAME_DESC" => "Ak používate databázu MySQL alebo PostgreSQL, zadajte prosím <b>meno databázy</b>, kde chcete vytvoriť tabuľky Viart-u. Táto databáza musí už existovať na serveri. Ak inštalujete Viart na testovacie účely na vašom lokálnom PC, tak väčšina systémov má \"<b>test</b>\" databázu, ktorú môžete použiť. Ak nemáte databázu, tak ju vytvorte napríklad ako \"viart\" a použite ju. Ak používate Microsoft Access alebo SQL Server, potom by názov databázy mal byť <b>meno DSN</b>, ktoré ste nastavili v Zdrojoch dát (ODBC) v ovládacom paneli.",
	"DB_USER_FIELD" => "Užívateľské meno",
	"DB_PASS_FIELD" => "Heslo",
	"DB_USER_PASS_DESC" => "<b>Meno</b> a <b>Heslo</b> - zadajte prosím meno a heslo účtu, ktorý má prístup k databáze. Ak používate lokálnu testovaciu inštaláciu tak meno je pravdepodobne \"<b>root</b>\"  a heslo je zrejme žiadne. Na testovacie účely je to v poriadku, ale berte na vedomie, že na produkčných serveroch to nieje bezpečné.",
	"DB_PERSISTENT_FIELD" => "Permanetné pripojenie",
	"DB_PERSISTENT_DESC" => "pre použitie  MySQL alebo Postgre trvalých pripojení, zaškrtnite toto políčko. Ak neviete, čo to znamená, ponechajte políčko bez zmeny.",
	"DB_CREATE_DB_FIELD" => "Vytvoriť DB",
	"DB_CREATE_DB_DESC" => "pre vytvorenie novej databázy (ak máte na to práva), zaškrtnite toto políčko. Funkčné iba s MySQL a Postgre",
	"DB_POPULATE_FIELD" => "Naplniť databázu",
	"DB_POPULATE_DESC" => "Pre vytvorenie tabuľky štruktúry databázy a jej naplnenie dátami zakliknite toto políčko",
	"DB_TEST_DATA_FIELD" => "Testovacie údaje",
	"DB_TEST_DATA_DESC" => "pre naplnenie databázy testovacími údajmi, zaškrtnite toto políčko.",
	"ADMIN_EMAIL_FIELD" => "Administrátorov email",
	"ADMIN_LOGIN_FIELD" => "Administrátorove užívateľské meno",
	"ADMIN_PASS_FIELD" => "Administrátorove heslo",
	"ADMIN_CONF_FIELD" => "Potvrdiť heslo",
	"DATETIME_SHOWN_FIELD" => "Formát času (zobrazený na stránke)",
	"DATE_SHOWN_FIELD" => "Formát dátumu (zobrazený na stránke)",
	"DATETIME_EDIT_FIELD" => "Formát času (pre úpravy)",
	"DATE_EDIT_FIELD" => "Formát dátumu (pre úpravy)",
	"DATE_FORMAT_COLUMN" => "Formát dátumu",

	"DB_LIBRARY_ERROR" => "PHP funkcie pre {db_library} nie sú definované. Prosím skontrolujte nastavenia databázy v konfiguračnom súbore - php.ini.",
	"DB_CONNECT_ERROR" => "Nemôžem sa pripojiť k databáze. Skontrolujte parametre databázy.",
	"INSTALL_FINISHED_ERROR" => "Inštalačný proces bol už ukončený.",
	"WRITE_FILE_ERROR" => "Nemám práva na zápis do súboru <b>'includes/var_definition.php'</b>. Pred pokračovaním skontrolujte prístupové práva.",
	"WRITE_DIR_ERROR" => "Nemám práva na zápis do adresára <b>'includes/'</b>. Pred pokračovaním skontrolujte prístupové práva.",
	"DUMP_FILE_ERROR" => "Dump súbor '{file_name}' nebol nájdený.",
	"DB_TABLE_ERROR" => "Tabuľka '{table_name}' nebola nájdená. Prosím naplňte databázu príslušnými dátami.",
	"TEST_DATA_ERROR" => "Skontrolovať <b>{POPULATE_DB_FIELD}</b> pred naplnením tabuľky testovacími údajmi.",
	"DB_HOST_ERROR" => "Zadaný názov hostiteľa sa nepodarilo nájsť.",
	"DB_PORT_ERROR" => "Nemôžem sa pripojiť na databázový server na definovanom porte.",
	"DB_USER_PASS_ERROR" => "Zadané meno alebo heslo nie je správne.",
	"DB_NAME_ERROR" => "Prihlasovacie údaje sú správne, ale databáza '{db_name}' nebola nájdená.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP Aktualizácia",
	"UPGRADE_NOTE" => "Poznámka: Zvážte prosím vytvorenie zálohy databázy pred pokračovaním.",
	"UPGRADE_AVAILABLE_MSG" => "Aktualizácia k dispozícii",
	"UPGRADE_BUTTON" => "Aktualizovať na verziu {version_number} teraz",
	"CURRENT_VERSION_MSG" => "Vaša aktuálne nainštalovaná verzia",
	"LATEST_VERSION_MSG" => "Verzia dostupná na inštaláciu",
	"UPGRADE_RESULTS_MSG" => "Výsledky aktualizácie",
	"SQL_SUCCESS_MSG" => "SQL dotaz úspešný",
	"SQL_FAILED_MSG" => "SQL dotaz neúspešný",
	"SQL_TOTAL_MSG" => "Spolu vykonaných SQL dotazov",
	"VERSION_UPGRADED_MSG" => "Vaša verzia bola aktualizovaná na",
	"ALREADY_LATEST_MSG" => "Máte nainštalovanú najaktuálnejšiu verziu",
	"DOWNLOAD_NEW_MSG" => "Bola nájdená nová verzia.",
	"DOWNLOAD_NOW_MSG" => "Stiahnuť verziu {version_number}",
	"DOWNLOAD_FOUND_MSG" => "Zistili sme, že je na stiahnutie k dispozícií nová verzia {version_number}. Kliknite prosím na linku dole, pre stiahnutie tejto verzie. Po skončení sťahovania a prepísania súborov na serveri sa nezabudnite vrátiť na túto stránku a spustite upgrade procedúru.",
	"NO_XML_CONNECTION" => "Upozornenie! Spojenie k 'http://www.viart.com/' sa nepodarilo!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "Licenčné podmienky pre koncového používateľa",
	"AGREE_LICENSE_AGREEMENT_MSG" => "Prečítal/a som si a súhlasím s licenčnými podmienkami",
	"READ_LICENSE_AGREEMENT_MSG" => "Kliknite sem pre zobrazenie licenčných podmienok.",
	"LICENSE_AGREEMENT_ERROR" => "Predtým ako budete pokračovať, prečítajte si prosím licenčné podmienky",

);
$va_messages = array_merge($va_messages, $messages);
