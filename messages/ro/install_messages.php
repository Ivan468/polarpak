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
	// mesaje instalare
	"INSTALL_TITLE" => "Instalare ViArt SHOP",

	"INSTALL_STEP_1_TITLE" => "Instalare: Pasul 1",
	"INSTALL_STEP_1_DESC" => "Multumim pentru ca ati ales ViArt SHOP. Pentru a continua instalarea, va rugam completati detaliile cerute mai jos. Va rugam retineti ca baza de date selectata ar trebui sa existe deja. Daca instalati intr-o baza de dare care foloseste ODBC, de ex. MS Access ar trebui sa creati mai intai un DSN inainte de a continua.",
	"INSTALL_STEP_2_TITLE" => "Instalare: Pasul 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Instalare: Pasul 3",
	"INSTALL_STEP_3_DESC" => "Va rugam selectati un design pentru site. Veti putea sa schimbati designul ulterior.",
	"INSTALL_FINAL_TITLE" => "Instalare: Final",
	"SELECT_DATE_TITLE" => "Selectare format data",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Setari baza de date",
	"DB_PROGRESS_MSG" => "Progres populare structura baza de date",
	"SELECT_PHP_LIB_MSG" => "Selectati biblioteca PHP",
	"SELECT_DB_TYPE_MSG" => "Selectati tipul bazei de date",
	"ADMIN_SETTINGS_MSG" => "Setari administrare",
	"DATE_SETTINGS_MSG" => "Formate data",
	"NO_DATE_FORMATS_MSG" => "Nu sunt disponibile formate data",
	"INSTALL_FINISHED_MSG" => "In acest moment instalarea de baza este finalizata. Va rugam asigurati-va ca ati verificat setarile in sectiunea administrare si faceti schimbarile necesare.",
	"ACCESS_ADMIN_MSG" => "Click aici pentru a accesa sectiunea administrare",
	"ADMIN_URL_MSG" => "URL administrare",
	"MANUAL_URL_MSG" => "URL manual",
	"THANKS_MSG" => "Va multumim ca ati ales <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Tip baza de date",
	"DB_TYPE_DESC" => "Va rugam selectati <b>tipul bazei de date</b> pe care il folositi. Daca folositi SQL Server sau Microsoft Access, va rugam selectati ODBC.",
	"DB_PHP_LIB_FIELD" => "Biblioteca PHP",
	"DB_HOST_FIELD" => "Nume host",
	"DB_HOST_DESC" => "Va rugam introduceti <b>numele</b> sau <b>IP-ul serverului</b> pe care va rula baza de date ViArt. Daca rulati baza de date pe calculatorul dumneavoastra local atunci probabil ca puteti lasa \"<b>localhost</b>\" si portul necompletat. Daca folositi baza de date pe care compania de hosting v-o pune la dispozitie, va rugam consultati documentatia companiei de hosting referitoare la setarile serverului.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Nume baza de date / DSN",
	"DB_NAME_DESC" => "Daca folositi o baza de date cum ar fi MySQL sau PostgreSQL va rugam introduceti <b>numele bazei de date</b> acolo unde ati dori ca ViArt sa isi creeze tabelele. Aceasta baza de date trebuie sa existe deja. Daca nu faceti decat sa instalati ViArt pentru teste pe calculatorul dumneavoastra local atunci majoritatea sistemelor au o baza de date \"<b>test</b>\" pe care o puteti folosi. Daca nu, va rugam creati o baza de date cum ar fi \"viart\" si folositi-o pe aceasta. Daca folositi Microsoft Access sau SQL Server atunci numele bazei de date ar trebui sa fie <b>numele DSN</b> pe care l-ati setat in sectiunea Data Sources(ODBC) din Control Panel.",
	"DB_USER_FIELD" => "Utilizator",
	"DB_PASS_FIELD" => "Parola",
	"DB_USER_PASS_DESC" => "<b>Utilizator</b> si <b>Parola</b> - va rugam introduceti utilizatorul si parola pe care doriti sa le folositi pentru a accesa aceasta baza de date. Daca folositi o instalatie locala de testare utilizatorul este probabil \"<b>root</b>\" si parola este probabil blank. Aceasta e in regula pentru testare dar nu este sigur pentru servere de productie.",
	"DB_PERSISTENT_FIELD" => "Conexiune persistenta",
	"DB_PERSISTENT_DESC" => "pentru a folosi conexiunile persistente MySQL sau Postgre, bifati aceasta casuta. Daca nu stiti ce semnifica aceasta, atunci cel mai probabil este mai bine sa lasati nebifat.",
	"DB_CREATE_DB_FIELD" => "Creati baza de date",
	"DB_CREATE_DB_DESC" => "pentru a crea baza de date va rugam bifati aici. Functioneaza numai pentru MySQL si Postgre",
	"DB_POPULATE_FIELD" => "Populati baza de date",
	"DB_POPULATE_DESC" => "pentru a crea structura de tabele a bazei de date si a o popula cu date bifati casuta",
	"DB_TEST_DATA_FIELD" => "Date test",
	"DB_TEST_DATA_DESC" => "pentru a adauga date test in baza dumneavoastra de date bifati casuta",
	"ADMIN_EMAIL_FIELD" => "Email administrator",
	"ADMIN_LOGIN_FIELD" => "Login administrator",
	"ADMIN_PASS_FIELD" => "Parola administrator",
	"ADMIN_CONF_FIELD" => "Confirma parola",
	"DATETIME_SHOWN_FIELD" => "Format data si timp (afisat pe site)",
	"DATE_SHOWN_FIELD" => "Format data (afisat pe site)",
	"DATETIME_EDIT_FIELD" => "Format data si timp (pentru modificare)",
	"DATE_EDIT_FIELD" => "Format data (pentru modificare)",
	"DATE_FORMAT_COLUMN" => "Format data",

	"DB_LIBRARY_ERROR" => "Functiile PHP pentru {db_library} nu sunt definite. Va rugam verificati setarile bazei de date in fisierul de configurare - php.ini",
	"DB_CONNECT_ERROR" => "Nu ma pot conecta la baza de date. Va rugam verificati parametrii bazei de date ",
	"INSTALL_FINISHED_ERROR" => "Procesul de instalare deja finalizat.",
	"WRITE_FILE_ERROR" => "Nu aveti permisiuni de scriere a fisierului <b>'includes/var_definition.php'</b>. Va rugam schimbati permisiunile fisierului inainte de a continua.",
	"WRITE_DIR_ERROR" => "Nu aveti permisiuni de scriere a directorului <b>'includes/'</b>. Va rugam schimbati permisiunile directorului inainte de a continua.",
	"DUMP_FILE_ERROR" => "Fisierul de export '{file_name}' nu a fost gasit.",
	"DB_TABLE_ERROR" => "Tabelul '{table_name}' nu a fost gasit. Va rugam populati baza de date cu datele necesare.",
	"TEST_DATA_ERROR" => "Verificati <b>{POPULATE_DB_FIELD}</b> inainte de a popula tabelele cu date test",
	"DB_HOST_ERROR" => "Numele de host pe care l-ati specificat nu a putut fi gasit.",
	"DB_PORT_ERROR" => "Nu ma pot conecta la serverul bazei de date folosind portul specificat",
	"DB_USER_PASS_ERROR" => "Utilizatorul sau parola specificata nu sunt corecte.",
	"DB_NAME_ERROR" => "Setarile de login au fost corecte, dar baza de date '{db_name}' nu a putut fi gasita.",

	// mesaje actualizare
	"UPGRADE_TITLE" => "Actualizare ViArt SHOP",
	"UPGRADE_NOTE" => "Nota: Va sfatuim sa faceti backup la baza de date inainte de a continua ",
	"UPGRADE_AVAILABLE_MSG" => "Actualizare baza de date disponibila",
	"UPGRADE_BUTTON" => "Actualizati baza de date la {version_number} acum",
	"CURRENT_VERSION_MSG" => "Versiunea instalata",
	"LATEST_VERSION_MSG" => "Versiunea disponibila pentru instalare",
	"UPGRADE_RESULTS_MSG" => "Actualizare rezultate",
	"SQL_SUCCESS_MSG" => "Interogarile SQL au fost efectuate cu succes",
	"SQL_FAILED_MSG" => "Interogarile SQL au esuat",
	"SQL_TOTAL_MSG" => "Total interogari SQL executate",
	"VERSION_UPGRADED_MSG" => "Baza de date a fost actualizata la",
	"ALREADY_LATEST_MSG" => "Aveti deja ultima versiune",
	"DOWNLOAD_NEW_MSG" => "Noua versiune a fost detectata",
	"DOWNLOAD_NOW_MSG" => "Descarcati versiunea {version_number} acum",
	"DOWNLOAD_FOUND_MSG" => "Am detectat veriunea noua {version_number} ca fiind disponibila pentru descarcare. Va rugam dati click pe linkul de mai jos pentru a incepe descarcarea. Dupa finalizarea descarcarii si inlocuirea fisierelor nu uitati sa efectuati operatiunea de actualizare inca o data.",
	"NO_XML_CONNECTION" => "Atentie! Conexiunea la 'http://www.viart.com/' indisponibila!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
