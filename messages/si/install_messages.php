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
	"INSTALL_TITLE" => "Namestitev ViArt SHOP ",

	"INSTALL_STEP_1_TITLE" => "Namestitev Korak 1",
	"INSTALL_STEP_1_DESC" => "Hvala, ker ste izbrali ViArt SHOP. <br> Za nadaljevanje namestitve izpolnite spodnja polja po navodilih. Upoštevajte, da mora biti že ustvarjena baza podatkov, katere parametre povezave, ki jih določite. <br> Če namestite v bazo podatkov z uporabo ODBC, na primer MS Access, morate najprej ustvariti sistem zanjo DSN.<br>",
	"INSTALL_STEP_2_TITLE" => "Namestitev: Korak 2",
	"INSTALL_STEP_2_DESC" => "Namestitev: Dokončanje",
	"DESIGN_SELECTION_MSG" => "Izbor dizajna",
	"INSTALL_STEP_3_TITLE" => "Namestitev: Korak 3",
	"INSTALL_STEP_3_DESC" => "Izberite predlogo spletnega mesta. Kasneje jo lahko spremenite v skrbniškem meniju.",
	"INSTALL_FINAL_TITLE" => "Namestitev: Dokončanje",
	"SELECT_DATE_TITLE" => "Izberite obliko datuma",
	"GET_SUPPORT_MSG" => "Pridobiti Podporo",
	"INSTALLATION_HELP_MSG" => "Pomoč pri namestitvi",

	"DB_SETTINGS_MSG" => "Parametre baze podatkov",
	"DB_PROGRESS_MSG" => "<b>Počakajte ... Postopek polnjenja baze podatkov je v teku<b>",
	"SELECT_PHP_LIB_MSG" => "Izberite vrsto za uporabo",
	"SELECT_DB_TYPE_MSG" => "Izberite vrsto baze podatkov",
	"ADMIN_SETTINGS_MSG" => "Namestitve administratorjev",
	"DATE_SETTINGS_MSG" => "Oblika datuma",
	"NO_DATE_FORMATS_MSG" => "Ta oblika ni dovoljena.",
	"INSTALL_FINISHED_MSG" => "S tem se konča namestitev <b> ViArt SHOP </b>. <br> <br> Zdaj lahko nadaljujete na upravni del in nadaljujete z nastavitvami in upravljanjem spletne trgovine. <br>",
	"ACCESS_ADMIN_MSG" => "Za dostop do upravnega odseka kliknite tukaj.",
	"ADMIN_URL_MSG" => "Naslov strani administracije",
	"MANUAL_URL_MSG" => "Dokumentacija:",
	"THANKS_MSG" => "Hvala za izbiro <b>ViArt SHOP</b>",

	"DB_TYPE_FIELD" => "Vrsta baze podatkov",
	"DB_TYPE_DESC" => "Izberite <b> vrsto baze podatkov </b>, ki jo uporabljate. Če uporabljate SQL Server ali Microsoft Access, izberite ODBC. Pri nameščanju z bazo ODBC, kot je MS Access, morate najprej ustvariti sistemski DSN za to.",
	"DB_PHP_LIB_FIELD" => "Vrsta baze podatkov",
	"DB_HOST_FIELD" => "Ime strežnika",
	"DB_HOST_DESC" => "Vnesite <b> Ime </b> ali <b> IP naslov strežnika </b>, ki vsebuje bazo podatkov za <b> ViArt SHOP </b>. Če se strežnik baz podatkov nahaja v vašem lokalnem računalniku, v to polje vnesite \"<b>localhost</b>\" in pristanišče pustite prazno. Če uporabljate bazo podatkov v podjetju, ki gostuje strežnik, preverite nastavitve v dokumentaciji, ki jo ponuja to gostovanje.",
	"DB_PORT_FIELD" => "Pristanišče",
	"DB_NAME_FIELD" => "Ime baze podatkov / DSN",
	"DB_NAME_DESC" => "Če uporabljate baze podatkov, kot sta MySQL ali PostgreSQL, vnesite <b> ime baze podatkov </b>, v katerem bo ViArt ustvaril potrebne tabele. Ta osnova mora obstajati vnaprej. Če ViArt za namene testiranja namestite v svoj lokalni računalnik, potem večina MySQL sistemov vsebuje bazo \"<b> test </b>\", ki jo lahko uporabite. V nasprotnem primeru ustvarite bazo podatkov, na primer \"ViArt \", in jo nato uporabite. Če uporabljate Microsoft Access ali SQL Server, mora <b> Ime baze podatkov </b> vsebovati <b> 0DSN </b>, ki ste ga konfigurirali v \"Viri podatkov (ODBC) \" na nadzorni plošči Windows.",
	"DB_USER_FIELD" => "Uporabniško ime BP",
	"DB_PASS_FIELD" => "Geslo ",
	"DB_USER_PASS_DESC" => "<b>Uporabniško ime </b> in <b> Geslo </b> - vnesite uporabniško ime in geslo za povezavo z bazo podatkov, prosim. Če uporabljate lokalno nastavitev preizkusa, je verjetno uporabniško ime \"<b>root </b>\", geslo pa je prazno. To je priročno za testiranje, vendar z varnostnega vidika na delujočem strežniku nesprejemljivo.",
	"DB_PERSISTENT_FIELD" => "Trajna povezava",
	"DB_PERSISTENT_DESC" => "potrdite polje, če želite uporabljati obstojno povezavo za MySQL ali Postgre. Če niste prepričani, kaj to pomeni, ne izberite te opcije.",
	"DB_CREATE_DB_FIELD" => "Ustvariti bazo podatkov",
	"DB_CREATE_DB_DESC" => "Vklopi se za ustvarjanje baze podatkov, če je mogoče. Ne deluje vedno in samo za MySQL и Postgre",
	"DB_POPULATE_FIELD" => "Ustvariti tabele",
	"DB_POPULATE_DESC" => "omogočite tako, da odkljukate, da ustvarite tabele zbirke podatkov in jih napolnite",
	"DB_TEST_DATA_FIELD" => "Testni podatki",
	"DB_TEST_DATA_DESC" => "omogočite s klikom, da dodate nekaj testnih demo podatkov",
	"ADMIN_EMAIL_FIELD" => "E-pošta administratorja",
	"ADMIN_LOGIN_FIELD" => "Uporabniško ime administratorja",
	"ADMIN_PASS_FIELD" => "Geslo administratorja",
	"ADMIN_CONF_FIELD" => "Potrdite geslo",
	"DATETIME_SHOWN_FIELD" => "Oblika datuma in časa (prikazuje se na spletnem mestu)",
	"DATE_SHOWN_FIELD" => "Oblika datuma (prikazuje se na spletnem mestu)",
	"DATETIME_EDIT_FIELD" => "Oblika datuma in časa (za urejanje)",
	"DATE_EDIT_FIELD" => "Oblika datuma (za urejanje)",
	"DATE_FORMAT_COLUMN" => "Oblika datuma",

	"DB_LIBRARY_ERROR" => "Funkcije PHP za {db_library} niso definirane. Preverite nastavitve baze podatkov v konfiguracijski datoteki- php.ini.",
	"DB_CONNECT_ERROR" => "Ni mogoče povezati z bazo podatkov. Preverite nastavitve povezave z bazo podatkov.",
	"INSTALL_FINISHED_ERROR" => "Čestitamo! Postopek namestitve je zaključen.",
	"WRITE_FILE_ERROR" => "Dovoljenje za ponovno pisanje datoteke ni <b> 'includes/var_definition.php' </b>. Pred nadaljevanjem namestitve spremenite dovoljenja za datoteke.",
	"WRITE_DIR_ERROR" => "Ni dovoljenja za pisanje v mapo <b>, 'includes / ' </b>. Pred nadaljevanjem namestitve spremenite dovoljenja mape.",
	"DUMP_FILE_ERROR" => "Dump datoteko ' {file_name} ' ni bilo mogoče najti.",
	"DB_TABLE_ERROR" => "Tabelo ' {table_name} ' ni bilo mogoče najti. Prosimo, da izpolnite bazo podatkov s potrebnimi podatki.",
	"TEST_DATA_ERROR" => "Postavite kljukico <b>{POPULATE_DB_FIELD}</b>, če želite vnesti preskusne podatke",
	"DB_HOST_ERROR" => "Navedenega strežnika ni bilo mogoče najti.",
	"DB_PORT_ERROR" => "Ni mogoče povezati s strežnikom baz podatkov na določenem pristanišču.",
	"DB_USER_PASS_ERROR" => "Uporabniško ime ali geslo sta napačna.",
	"DB_NAME_ERROR" => "Parametri povezave so pravilni, vendar baze podatkov '{db_name}'  ni bilo mogoče najti.",

	// upgrade messages
	"UPGRADE_TITLE" => "Posodobitve",
	"UPGRADE_NOTE" => "Opomba: Pred posodobitvo ustvarite varnostno kopijo baze podatkov.",
	"UPGRADE_AVAILABLE_MSG" => "Na voljo je nova verzija",
	"UPGRADE_BUTTON" => "Posodobiti na {version_number} zdaj",
	"CURRENT_VERSION_MSG" => "Vaša trenutna verzija",
	"LATEST_VERSION_MSG" => "Na voljo verzija za namestitev",
	"UPGRADE_RESULTS_MSG" => "Rezultat posodobitve",
	"SQL_SUCCESS_MSG" => "SQL zahtev je zaključenih",
	"SQL_FAILED_MSG" => "SQL zahtev z napakami",
	"SQL_TOTAL_MSG" => "Skupaj zaključenih SQL zahtev",
	"VERSION_UPGRADED_MSG" => "Vaša verzija je posodobljena na",
	"ALREADY_LATEST_MSG" => "Imate nameščeno zadnjo verzijo",
	"DOWNLOAD_NEW_MSG" => "Odkrita je nova verzija",
	"DOWNLOAD_NOW_MSG" => "Prenesiti verzijo zdaj {version_number}",
	"DOWNLOAD_FOUND_MSG" => "Ugotovili smo, da je za prenos na voljo nova verzija {version_number}. Za začetek prenosa kliknite spodnjo povezavo. Po prenosu in zamenjavi datotek ne pozabite znova zagnati postopka posodabljanja.",
	"NO_XML_CONNECTION" => "Pozor! Ni se mogoče povezati s 'http://www.viart.com/'!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "Licenčna Pogodba",
	"AGREE_LICENSE_AGREEMENT_MSG" => "Prebral sem in sprejemam licenčno Pogodbo.",
	"READ_LICENSE_AGREEMENT_MSG" => "Licenčna Pogodba",
	"LICENSE_AGREEMENT_ERROR" => "<b>Pred nadaljevanjem preberite in sprejmite licenčno Pogodbo!<b>",

);
$va_messages = array_merge($va_messages, $messages);
