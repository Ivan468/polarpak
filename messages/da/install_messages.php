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
	"INSTALL_TITLE" => "ViArt SHOP Installation",

	"INSTALL_STEP_1_TITLE" => "Installation: Trin 1",
	"INSTALL_STEP_1_DESC" => "Tak, fordi du valgte ViArt SHOP. For at fortsætte installationen, skal du udfylde de ønskede oplysninger nedenfor. Bemærk, at den database, du vælger allerede bør eksistere. Hvis du installerer til en database, der bruger ODBC, f.eks MS Access skal du først oprette en DSN for det, før du fortsætter.",
	"INSTALL_STEP_2_TITLE" => "Installation: Trin 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Installation: Trin 3",
	"INSTALL_STEP_3_DESC" => "Vælg et site layout. Du vil være i stand til at ændre layoutet bagefter.",
	"INSTALL_FINAL_TITLE" => "Installation: Afslutning",
	"SELECT_DATE_TITLE" => "Vælg dato format",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Database indstillinger",
	"DB_PROGRESS_MSG" => "Vises i database struktur fremskridt",
	"SELECT_PHP_LIB_MSG" => "Vælg PHP Bibliotek",
	"SELECT_DB_TYPE_MSG" => "Vælg database type",
	"ADMIN_SETTINGS_MSG" => "Administrative indstillinger",
	"DATE_SETTINGS_MSG" => "Dato formater",
	"NO_DATE_FORMATS_MSG" => "Ingen datoformater til rådighed",
	"INSTALL_FINISHED_MSG" => "På dette tidspunkt er den grundlæggende installation færdig. Sørg for at kontrollere indstillingerne i administrationsdelen, og foretag de nødvendige ændringer.",
	"ACCESS_ADMIN_MSG" => "klik her for at få adgang til administrationsdelen",
	"ADMIN_URL_MSG" => "Administration URL",
	"MANUAL_URL_MSG" => "Manual URL",
	"THANKS_MSG" => "Mange tak, fordi du valgte <b> ViArt SHOP </ b>.",

	"DB_TYPE_FIELD" => "Database type",
	"DB_TYPE_DESC" => "Vælg venligst den <b> type database </ b>, du bruger. Hvis du bruger SQL Server eller Microsoft Access, kan du vælge ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP Bibliotek",
	"DB_HOST_FIELD" => "Værtsnavn",
	"DB_HOST_DESC" => "Angiv <b> navn </ b> eller <b> IP-adressen på den server </ b>, hvor din ViArt database vil køre. Hvis du kører din database på dit lokale pc så kan du sikkert bare overlade dette som \"<b> localhost </ b>\" og porten tomt. Hvis du bruger en database fra din hosting selskab, kan du se din hosting virksomhedens dokumentation for serverindstillinger.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Database Navn / DSN",
	"DB_NAME_DESC" => "Hvis du bruger en database som MySQL eller PostgreSQL venligst indtast <b> databasens navn </ b>, hvor du ønsker ViArt at skabe sin tabeller. Denne database skal eksistere allerede.Hvis du kun installerer ViArt til testformål på din lokale PC har de fleste systemer \"<b> test </ b>\" database, du kan bruge. Hvis ikke, kan du oprette en database som \"viart\" og bruge den. Hvis du bruger Microsoft Access eller SQL Server derefter databasenavnet bør <b> navnet på DSN </ b>, at du har oprettet i Datakilder (ODBC) del af dit Kontrolpanel.",
	"DB_USER_FIELD" => "Brugernavn",
	"DB_PASS_FIELD" => "Password",
	"DB_USER_PASS_DESC" => "<b> Brugernavn </ b> og <b> Password </ b> - angiv brugernavn og adgangskode, du vil bruge til at få adgang til databasen. Hvis du bruger en lokal test installation brugernavnet er sandsynligvis \"<b> root </ b>\" og password er sandsynligvis tomt. This is fine for testing, but please note that this is not secure on production servers. Det er godt nok til afprøvning, men vær opmærksom på, at dette ikke er sikker på produktionen servere.",
	"DB_PERSISTENT_FIELD" => "Persistent Connection",
	"DB_PERSISTENT_DESC" => "to use MySQL or Postgre persistent connections, tick this box. If you do not know what it means, then leaving it unticked is probably best.",
	"DB_CREATE_DB_FIELD" => "Opret DB",
	"DB_CREATE_DB_DESC" => "til at oprette databasen, hvis det er muligt, sættes kryds i denne boks. Virker kun for MySQL og Postgre",
	"DB_POPULATE_FIELD" => "Populate DB",
	"DB_POPULATE_DESC" => "for at oprette database tabel strukturen og udfylde den med data afkryds afkrydsningsfeltet",
	"DB_TEST_DATA_FIELD" => "Test data",
	"DB_TEST_DATA_DESC" => "For at tilføje data til din database, sæt kryds i checkboksen",
	"ADMIN_EMAIL_FIELD" => "Administrator Email",
	"ADMIN_LOGIN_FIELD" => "Administrator Login",
	"ADMIN_PASS_FIELD" => "Administrator Password",
	"ADMIN_CONF_FIELD" => "Bekræft Password",
	"DATETIME_SHOWN_FIELD" => "Tidspunkt (vises på sitet)",
	"DATE_SHOWN_FIELD" => "Dato (vises på sitet)",
	"DATETIME_EDIT_FIELD" => "Tidspunkt (til redigering)",
	"DATE_EDIT_FIELD" => "Dato (til redigering)",
	"DATE_FORMAT_COLUMN" => "Dato",

	"DB_LIBRARY_ERROR" => "PHP funktioner for {db_library} er ikke defineret. Kontroller din database indstillinger i din opsætningsfil - php.ini.",
	"DB_CONNECT_ERROR" => "Kan ikke opnå forbindelse til database. Tjek databasens parametre",
	"INSTALL_FINISHED_ERROR" => "Installationen allerede færdig.",
	"WRITE_FILE_ERROR" => "Du har ikke skrivestilladelse til filen <b> 'includes / var_definition.php' </ b>. Ændre venligst filrettigheder, før du fortsætter.",
	"WRITE_DIR_ERROR" => "Du har ikke skrivestilladelse til mappen <b> 'includes /' </ b>. Ændre venligst mappetilladelser før du fortsætter.",
	"DUMP_FILE_ERROR" => "Dump file '{file_name}' wasn't found.",
	"DB_TABLE_ERROR" => "Table '{table_name}' wasn't found. Please populate the database with the necessary data.",
	"TEST_DATA_ERROR" => "Check <b>{POPULATE_DB_FIELD}</b> before populating tables with test data",
	"DB_HOST_ERROR" => "Angivet værtsnavn blev ikke fundet.",
	"DB_PORT_ERROR" => "Den angivne port kunne ikke opnå forbindelse til database.",
	"DB_USER_PASS_ERROR" => "Det angivne brugernavn eller password er ikke rigtigt.",
	"DB_NAME_ERROR" => "Login indstillinger er korrekte, men databasen ",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP opgradering",
	"UPGRADE_NOTE" => "Note: Overvej at køre backup af database før du går videre.",
	"UPGRADE_AVAILABLE_MSG" => "Opgradering af database tilgængelig.",
	"UPGRADE_BUTTON" => "Opgrader database til {version_number}",
	"CURRENT_VERSION_MSG" => "Aktuelt installeret version",
	"LATEST_VERSION_MSG" => "Version tilgængelig for installering",
	"UPGRADE_RESULTS_MSG" => "Opgrader resultater",
	"SQL_SUCCESS_MSG" => "SQL spørgsmål lykkedes",
	"SQL_FAILED_MSG" => "SQL spørgsmål mislykkedes",
	"SQL_TOTAL_MSG" => "Total SQL spørgsmål gennemført",
	"VERSION_UPGRADED_MSG" => "Din database er blevet opgraderet til",
	"ALREADY_LATEST_MSG" => "Du har allerede den seneste version",
	"DOWNLOAD_NEW_MSG" => "Den nye version er fundet.",
	"DOWNLOAD_NOW_MSG" => "Download version {version_number}",
	"DOWNLOAD_FOUND_MSG" => "We have detected that the new {version_number} version is available to download. Please click the link below to start downloading. After completing the download and replacing the files don't forget to run Upgrade routine again.",
	"NO_XML_CONNECTION" => "Advarsel! Ingen forbindelse til 'http://www.viart.com/'",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
