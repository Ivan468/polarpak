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
	//telepítési üzenetek
	"INSTALL_TITLE" => " ViArt Webáruház Telepítés",

	"INSTALL_STEP_1_TITLE" => "Telepítés: 1. Lépés",
	"INSTALL_STEP_1_DESC" => "Köszönjük, hogy a ViArt webáruházat választottad. A telepítés folytatásához ki kell töltened az alábbi mezőket. Figyelem: az adatbázis kiválasztásához már a telepítés előtt létre kell hozni egy adatbázist. Ha olyan adatbázist telepítesz ami  ODBC t  használ, mint a Microsoft Access , tovább haladás előtt  létre kell hozni DNS-t.",
	"INSTALL_STEP_2_TITLE" => "Telepítés: 2. Lépés",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Telepítés: 3. Lépés",
	"INSTALL_STEP_3_DESC" => "Válassz a weboldal elrendezések közül. Később természetesen ezt megváltoztathatod.",
	"INSTALL_FINAL_TITLE" => "Telepítés: Befejezés",
	"SELECT_DATE_TITLE" => "Válassz dátumot formátumot",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Adatbázis beállítások",
	"DB_PROGRESS_MSG" => "Adatbázis struktóra létrehozása",
	"SELECT_PHP_LIB_MSG" => "Válassz PHP Könyvtárat",
	"SELECT_DB_TYPE_MSG" => "Válassz adatbázis típust",
	"ADMIN_SETTINGS_MSG" => "Adminisztratív beállítások",
	"DATE_SETTINGS_MSG" => "Dátum formátumok",
	"NO_DATE_FORMATS_MSG" => "Nincs elérhető dátum formátum",
	"INSTALL_FINISHED_MSG" => "A telepítés elkészült. Kérlek, ellenőrizd a beállításokat, és végezd el a szükséges változtatásokat az adminisztrációs menüben.",
	"ACCESS_ADMIN_MSG" => "Az adminisztrációs felület eléréséhez kattintás ide",
	"ADMIN_URL_MSG" => "Adminisztráció URL",
	"MANUAL_URL_MSG" => "Használati utasítás URL",
	"THANKS_MSG" => "Köszönjük, hogy a <b>ViArt Webáruházat</b> választotta.   ",

	"DB_TYPE_FIELD" => "Adatbázis Típus",
	"DB_TYPE_DESC" => "Kérlek válatd ki az <b>adatbázis típust</b>. Ha  SQL Servert vagy Microsoft Access-t használsz, válaszd az ODBC lehetőséget.",
	"DB_PHP_LIB_FIELD" => "PHP Könyvtár",
	"DB_HOST_FIELD" => "Hostnév",
	"DB_HOST_DESC" => "Kérlek add meg a <b>szerver nevét</b>vagy a <b>szerver IP címét</b>ahol a Viart-hoz használni kívánt adatbázis elérhető. Ha az adatbázis lokális PC-én fut akkor valószínűleg jó a \"<b>localhost</b>\" és hagyd a portot üresen. Ha egy szolgáltatód által biztosított adatbázist használsz , akkor tanulmányozd át a szolgáltató szerver beállításainak a dokumentációját (általában itt is jó a localhost).",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Adatbázis Név / DSN",
	"DB_NAME_DESC" => "Ha MySQL vagy PostgreSQL adatbázist használsz, add meg az <b>adatbázis neve</b> mezőben a nevét, hogy a ViArt telepítő létrehozza a szükséges táblákat. Az adatbázisnak már léteznie kell. Ha tesztelés céljából telepíted a Viart-ot lokális pc-re, akkor a legtöbb gépnek van egy <b>test</b>\" adatbázisa , amit használhatsz. Ha nem , akkor készíts egy adatbázist \"viart\" néven, és azt használd. Ha  Microsoft Access-t vagy SQL Servert használsz akkor az adatbázis nevének a <b>DSN neve</b> tartományban kell lenni, amit beállítottál az adatforrásoknál (ODBC)  a kontrol panelen.",
	"DB_USER_FIELD" => "Felhasználó név",
	"DB_PASS_FIELD" => "Jelszó",
	"DB_USER_PASS_DESC" => "<b>felhasználó név</b> és <b>jelszó</b> - kérlek add meg a felhasználó nevet és a jelszót amit az adatbázis eléréséhezz akarsz használni. Ha egy lokális gépen teszt installálást használsz, a lehetséges felhasználónév \"<b>root</b>\" és nincs jelszó. Ez így kiváló tesztelésre , de nem biztonságos egy nyilvános szerveren.",
	"DB_PERSISTENT_FIELD" => "Állandó Kapcsolat",
	"DB_PERSISTENT_DESC" => "Állandó MySQL / Postgre kapcsolat esetén klikkeld be ezt a dobozt, ha nem tudod , hogy mit jelent , inkább hagyd üresen.",
	"DB_CREATE_DB_FIELD" => "Adatbázis Létrehozása",
	"DB_CREATE_DB_DESC" => "az adatbázis létrehozásához kattintsd be a jelölő négyzetet. Csak MySQL és Postgre esetében működik",
	"DB_POPULATE_FIELD" => "Adatbázi feltöltése",
	"DB_POPULATE_DESC" => "az adatbázis struktúra létrehozásához és adatfeltöltéshez kattintsd be a jelölő négyzetet",
	"DB_TEST_DATA_FIELD" => "Demó adatok",
	"DB_TEST_DATA_DESC" => "Ha szeretnéd, hogy a demó tartalmak bekerüljenek az adatbázisba, kkattintsd be a jelölőnégyzetet.",
	"ADMIN_EMAIL_FIELD" => "Ügyintéző Email",
	"ADMIN_LOGIN_FIELD" => "Ügyintéző azonosító",
	"ADMIN_PASS_FIELD" => "Ügyintéző jelszó",
	"ADMIN_CONF_FIELD" => "Ismételd meg a jelszót",
	"DATETIME_SHOWN_FIELD" => "Idő formátum (webhelyen látszik)",
	"DATE_SHOWN_FIELD" => "Dátum formátum (webhelyen látszik)",
	"DATETIME_EDIT_FIELD" => "Idő formátum (szerkesztéskor)",
	"DATE_EDIT_FIELD" => "Dátum formátum (szerkesztéskor)",
	"DATE_FORMAT_COLUMN" => "Dátum formátum",

	"DB_LIBRARY_ERROR" => "PHP funkciók nincsenek definiálva a {db_library} számára. Kérem ellenőrizze az adatbázis beállításait a konfigurációban. Fájl:  php.ini.",
	"DB_CONNECT_ERROR" => "Nem lehet csatlakozni az adatbázishoz. Kérem ellenőrizze az adatbázis paramétereit.",
	"INSTALL_FINISHED_ERROR" => " A telepítés folyamat már befejezett.",
	"WRITE_FILE_ERROR" => "Nincs írási engedélye a <b>'includes/var_definition.php'</b> fájlhoz. Folytatás előtt meg kell változatni.",
	"WRITE_DIR_ERROR" => "Nem rendelkezik írási engedéllyel a <b>'includes/'</b> mappához. Kérem megváltoztatni a mappa engedélyeket.",
	"DUMP_FILE_ERROR" => "Dump fájl '{file_name}' nem található.",
	"DB_TABLE_ERROR" => "Tábla 'table_name' nem található. Kérem feltölteni az adatbázist a szükséges adattal.",
	"TEST_DATA_ERROR" => "Ellenőrizd a <b>{POPULATE_DB_FIELD}</b> mielőtt közéteszel táblákat teszt adatokkal.",
	"DB_HOST_ERROR" => "A hostnév amit meghatároztál, nem található.",
	"DB_PORT_ERROR" => "MySQL szerver meghatározott portjához nem lehet csatlakozni.",
	"DB_USER_PASS_ERROR" => "A meghatározott felhasználónév jelszó helytelen.",
	"DB_NAME_ERROR" => "Login beállítások rendben vannak, de az adatbázis '{db_name}'  nem található.",

	//frissítés üzenetek
	"UPGRADE_TITLE" => " ViArt SHOP frissítés",
	"UPGRADE_NOTE" => "Megjegyezés: Kérem készítsen mentést az adatbázisról, mielőtt frissítene!",
	"UPGRADE_AVAILABLE_MSG" => "Adatbázis frissített változat elérhető",
	"UPGRADE_BUTTON" => "Frissítés a  {version_number} verzióra ",
	"CURRENT_VERSION_MSG" => "Jelenleg telepített változatot",
	"LATEST_VERSION_MSG" => "Elérhető telepíthető változat ",
	"UPGRADE_RESULTS_MSG" => "Frissítés eredménye",
	"SQL_SUCCESS_MSG" => "SQL lekérdezés sikeres",
	"SQL_FAILED_MSG" => "SQL lekérdezés nem sikerült",
	"SQL_TOTAL_MSG" => "Teljes SQL lekérdezés megtörtént",
	"VERSION_UPGRADED_MSG" => "Az adatbázisod frissült a",
	"ALREADY_LATEST_MSG" => "Már a legújabb változattal rendelkezel.",
	"DOWNLOAD_NEW_MSG" => "Új változatot találtunk.",
	"DOWNLOAD_NOW_MSG" => "{version_number} verzió letöltése most.",
	"DOWNLOAD_FOUND_MSG" => "Érzékeltük, hogy az új {version_number} verzió letölthető. Az alábbi linkre kattintva lehet elkezdeni a letöltést. A letöltés után és a fájlok cserélése ne felejtsd  frissítést újra futatni.",
	"NO_XML_CONNECTION" => "Figyelmeztetés! Nincs kapcsolat 'HTTP:www.viart.com/' ! ",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
