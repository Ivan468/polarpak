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
	"INSTALL_TITLE" => "Webbutik - Installation",

	"INSTALL_STEP_1_TITLE" => "Installation: Steg 1",
	"INSTALL_STEP_1_DESC" => "Tack för att du har valt att installera webbutik. För att komma igång med installationen behöver du fylla i nedanstående obligatoriska fält. Vänligen se till att databasen du valt redan finns. Om du installerar till en databas som använder ODBC eller MS Access, så behöver du först skapa en DSN-koppling till den innan du fortsätter.",
	"INSTALL_STEP_2_TITLE" => "Installation: Steg 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Installation: Steg 3",
	"INSTALL_STEP_3_DESC" => "Vänligen välj en webbsides-layout. Du kan ändra layouten senare.",
	"INSTALL_FINAL_TITLE" => "Installation: Sista Steget",
	"SELECT_DATE_TITLE" => "Välj Datumformat",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Databasinställningar",
	"DB_PROGRESS_MSG" => "Skapar databasstrukturen",
	"SELECT_PHP_LIB_MSG" => "Välj PHP-bibliotek",
	"SELECT_DB_TYPE_MSG" => "Välj databastyp",
	"ADMIN_SETTINGS_MSG" => "Administrativa inställningar",
	"DATE_SETTINGS_MSG" => "Datumformat",
	"NO_DATE_FORMATS_MSG" => "Inget datumformat tillgängligt",
	"INSTALL_FINISHED_MSG" => "Grundinstallationen är genomförd. Var vänlig se till att alla inställningar i adminstrationssektionen är som de ska.",
	"ACCESS_ADMIN_MSG" => "För att komma åt administrationen, klicka här",
	"ADMIN_URL_MSG" => "Administrations-URL",
	"MANUAL_URL_MSG" => "Manual-URL",
	"THANKS_MSG" => "Tack för att du valt <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Databastyp",
	"DB_TYPE_DESC" => "Var vänlig välj vilken <b>typ av databas</b> som du använder. Om du använder SQL Server eller Microsoft Access, var vänlig välj ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP-bibliotek",
	"DB_HOST_FIELD" => "Domän",
	"DB_HOST_DESC" => "Var vänlig ange <b>namn</b> eller <b>IP-adress för servern</b> där din ViArt databas kommer köras. Om du kör din databas på din lokala PC så kan du troligtvis enbart låta det stå \\\"<b>localhost</b>\\\" och lämna fältet för porten tomt. Om du använder en databas hos din webhost, var vänlig kontrollera uppgifterna för serverinställningarna med dem.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Databasnamn / DSN",
	"DB_NAME_DESC" => "Om du använder en databas som t ex MySQL eller PostgreSQL var vänlig ange <b>namnet på databasen</b> där du vill att ViArt ska skapa tabeller. Det måste vara en befintlig databas. Om du bara installerar ViArt i prövosyfte på din lokala PC så har de flesta system en \\\"<b>test</b>\\\"-databas som du kan använda. Om det inte finns någon sådan så bör du skapa en databas som du t ex döper till \\\"viart\\\" och sedan använda den. Om du använder Microsoft Access eller SQL Server så bör databasnamnet vara <b>DSN-namnet</b> som du har angett i Data Sources (ODBC) delen av din kontrollpanel.",
	"DB_USER_FIELD" => "Användarnamn",
	"DB_PASS_FIELD" => "Lösenord",
	"DB_USER_PASS_DESC" => "<b>Användarnamn</b> och <b>lösenord</b> - var vänlig ange användarnamn och lösenord som du vill använda för åtkomst av databasen. Om du använde ren lokal testinstallation så är användarnamnet troligtvis \\\"<b>root</b>\\\" och lösenordsfältet lämnas tomt. Det är helt okej när man testar, men var vänlig notera att det inte är säkert på produktionsservrar.",
	"DB_PERSISTENT_FIELD" => "Persistent Connection",
	"DB_PERSISTENT_DESC" => "För att använda MySQL eller Postgre persistent connections, bocka i denna ruta. Om du inte vet vad det innebär så är det säkerligen bäst att lämna rutan oklickad.",
	"DB_CREATE_DB_FIELD" => "Skapa databas",
	"DB_CREATE_DB_DESC" => "För att om möjligt skapa en databas, bocka i rutan. Fungerar bara för MySQL och Postgre",
	"DB_POPULATE_FIELD" => "Fyll databasen",
	"DB_POPULATE_DESC" => "För att skapa databasens tabellstruktur och fylla den med data bocka i denna rutan.",
	"DB_TEST_DATA_FIELD" => "Testdata",
	"DB_TEST_DATA_DESC" => "För att lägga till lite testdata i din databas bocka i rutan.",
	"ADMIN_EMAIL_FIELD" => "Administrationsepost",
	"ADMIN_LOGIN_FIELD" => "Administration användarnamn",
	"ADMIN_PASS_FIELD" => "Administration lösenord",
	"ADMIN_CONF_FIELD" => "Bekräfta lösenord",
	"DATETIME_SHOWN_FIELD" => "Tidsformat (visad på webbplatsen)",
	"DATE_SHOWN_FIELD" => "Datumformat (visad på webbplatsen)",
	"DATETIME_EDIT_FIELD" => "Tidsformat (för ändring)",
	"DATE_EDIT_FIELD" => "Datumformat (för ändring)",
	"DATE_FORMAT_COLUMN" => "Datumformat",

	"DB_LIBRARY_ERROR" => "PHP-funktioner för {db_library} är inte angivna. Var vänlig kontrollera dina databasinställningar i din konfigurationsfil - php.ini.",
	"DB_CONNECT_ERROR" => "Kan inte koppla mot databasen. Vänligen kolla dina databas-parametrar.",
	"INSTALL_FINISHED_ERROR" => "Installationsprocessen är färdig.",
	"WRITE_FILE_ERROR" => "Har inte skrivrättigheter till filen <b>'includes/var_definition.php'</b>. Vänligen gör ändringar för skrivrättigheter på filen innan du fortsätter.",
	"WRITE_DIR_ERROR" => "Har inte skrivrättigheter till katalogen <b>'includes/'</b>. Vänligen ändra katalogens rättigheter innan du fortsätter.",
	"DUMP_FILE_ERROR" => "Dump-filen '{file_name}' hittades inte.",
	"DB_TABLE_ERROR" => "Tabellen '{table_name}' hittades inte. Vänligen fyll databasen med nödvändig information.",
	"TEST_DATA_ERROR" => "Klicka i <b>{POPULATE_DB_FIELD}</b> innan du fyller tabeller med testdata",
	"DB_HOST_ERROR" => "Hostnamnet som du angav kan inte hittas.",
	"DB_PORT_ERROR" => "Kan inte ansluta till databasservern via den angivna porten.",
	"DB_USER_PASS_ERROR" => "Användarnamnet eller lösenordet som du har angett är inte rätt.",
	"DB_NAME_ERROR" => "Inloggningsuppgifterna var rätt, men databasen '{db_name}' kunde inte hittas.",

	// upgrade messages
	"UPGRADE_TITLE" => "Shop - Uppgradering",
	"UPGRADE_NOTE" => "OBS: Vänligen tänk på att göra en backup av databasen innan du fortsätter med uppgraderingen.",
	"UPGRADE_AVAILABLE_MSG" => "Uppgradering tillgänglig",
	"UPGRADE_BUTTON" => "Uppgradera till {version_number} nu",
	"CURRENT_VERSION_MSG" => "Din nuvarande version",
	"LATEST_VERSION_MSG" => "Version tillgänglig att installera",
	"UPGRADE_RESULTS_MSG" => "Uppgraderingsresultat",
	"SQL_SUCCESS_MSG" => "SQL-queries - lyckade",
	"SQL_FAILED_MSG" => "SQL-queries - misslyckade",
	"SQL_TOTAL_MSG" => "Totalt antal SQL-queries körda",
	"VERSION_UPGRADED_MSG" => "Din version har uppgraderas till",
	"ALREADY_LATEST_MSG" => "Du har redan den senaste versionen",
	"DOWNLOAD_NEW_MSG" => "En ny version har hittats",
	"DOWNLOAD_NOW_MSG" => "Ladda ner version {version_number} nu",
	"DOWNLOAD_FOUND_MSG" => "Vi har upptäckt att den nya versionen {version_number} är tillgänglig för nedladdning. Var vänlig klicka på länken nedanför för att starta nedladdningen. Efter att nedladdningen är klar och filerna är utbytta, glöm inte att köra uppgraderingsrutinen igen.",
	"NO_XML_CONNECTION" => "Varning! Ingen uppkoppling till 'http://www.viart.com/' är tillgänglig!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
