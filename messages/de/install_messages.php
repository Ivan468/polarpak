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
	"INSTALL_TITLE" => "ViArt SHOP Installierung",

	"INSTALL_STEP_1_TITLE" => "Installierung: Schritt 1",
	"INSTALL_STEP_1_DESC" => "Vielen Dank, dass Sie sich für ViArt SHOP entschieden haben. Damit die Installierung abgeschlossen werden kann, machen Sie bitte die folgenden Angaben. Vergewissern Sie sich, dass bereits eine Datenbank existiert. Wenn Sie in eine ODBC-Datenbank installieren, z.B. MS Access, sollten Sie dafür zunächst ein DSN erzeugen.",
	"INSTALL_STEP_2_TITLE" => "Installierung: Schritt 2",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Installierung: Schritt 3",
	"INSTALL_STEP_3_DESC" => "Bitte wählen Sie ein Seitenlayout. Sie können das Layout später ändern.",
	"INSTALL_FINAL_TITLE" => "Installierung: Abschluss",
	"SELECT_DATE_TITLE" => "Datumsformat auswählen",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Datenbank-Einstellungen",
	"DB_PROGRESS_MSG" => "Fortschritt Datenbankstruktur bestücken",
	"SELECT_PHP_LIB_MSG" => "PHP-Bibliothek wählen",
	"SELECT_DB_TYPE_MSG" => "Datenbank-Typ wählen",
	"ADMIN_SETTINGS_MSG" => "Administrations-Einstellungen",
	"DATE_SETTINGS_MSG" => "Datumsformate",
	"NO_DATE_FORMATS_MSG" => "Keine Datumsformate verfügbar",
	"INSTALL_FINISHED_MSG" => "Die Basisinstallation ist nun abgeschlossen. Überprüfen Sie nun die Einstellungen im Administrationsbereich und nehmen Sie ggf. die notwendigen Anpassungen vor.",
	"ACCESS_ADMIN_MSG" => "Um in den Administrationsbereich zu gelangen, klicken Sie bitte hier.",
	"ADMIN_URL_MSG" => "Administration-URL",
	"MANUAL_URL_MSG" => "Manual-URL",
	"THANKS_MSG" => "Danke, dass Sie <b>ViArt SHOP</b> gewählt haben.",

	"DB_TYPE_FIELD" => "Datenbank-Typ",
	"DB_TYPE_DESC" => "Bitte wählen Sie den Datenbanktyp, den Sie benutzen. Wenn Sie SQL Server oder Microsoft Access benutzen, wählen Sie bitte ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP-Bibliothek",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Geben Sie bitte die IP-Adresse des Servers ein, auf dem Ihre ViArt-Datenbank läuft. Wenn die Datenbank auf Ihrem lokalen PC läuft, können Sie die Einstellung bei \"localhost\" mit leerer Portnummer belassen. Wenn Sie eine Datenbank Ihres Hosters benutzen, entnehmen Sie bitte die Servereinstellungen der Dokumentation Ihres Hosters.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Datenbank-Name/DSN",
	"DB_NAME_DESC" => "Wenn Sie eine Datenbank wie MySQL oder PostgreSQL  verwenden, geben Sie bitte den Namen der Datenbank ein, in der ViArt seine Tabellen anlegen soll. Diese Datenbank muss bereits existieren. Wenn Sie ViArt zu Testzwecken auf Ihrem lokalen PC installieren: Viele Systeme haben bereits eine \"test\"-Datenbank, die Sie benutzen können. Wenn nicht, legen Sie bitte eine Datenbank an, z.B. \"viart\", und verwenden Sie diese. Wenn Sie Microsoft Access oder SQL Server benutzen, sollte der Datenbank-Name dem DSN entsprechen, den Sie in der ODBC-Sektion Ihrer Systemsteuerung angelegt haben.",
	"DB_USER_FIELD" => "Benutzername",
	"DB_PASS_FIELD" => "Passwort",
	"DB_USER_PASS_DESC" => "Benutzername und Passwort - geben Sie bitte Benutzername und Passwort für den Datenbankzugriff an. Wenn Sie eine lokale Testinstallation benutzen, wird der Benutzername vermutlich \"root\" sein mit leerem Passwort. Zu Testzwecken ist dies ausreichend. Auf Produktionsservern ist dies jedoch sehr unsicher.",
	"DB_PERSISTENT_FIELD" => "Persistente Verbindung",
	"DB_PERSISTENT_DESC" => "Um persistente MySQL//Postgre-Verbindungen zu benutzen, markieren Sie bitte die Checkbox. Wenn Sie nicht wissen, was das bedeutet, lassen Sie die Box am besten unmarkiert.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "DB bestücken",
	"DB_POPULATE_DESC" => "um die Datenbank-Tabellenstruktur zu erzeugen und mit Daten zu bestücken, markieren Sie die Checkbox.",
	"DB_TEST_DATA_FIELD" => "Testdaten",
	"DB_TEST_DATA_DESC" => "um Testdaten zur Datenbank hinzuzufügen, markieren Sie bitte die Checkbox",
	"ADMIN_EMAIL_FIELD" => "Administrator E-Mail",
	"ADMIN_LOGIN_FIELD" => "Administrator Anmeldung",
	"ADMIN_PASS_FIELD" => "Administrator Passwort",
	"ADMIN_CONF_FIELD" => "Passwort bestätigen",
	"DATETIME_SHOWN_FIELD" => "Datum/Zeit-Format (auf Seite angezeigt)",
	"DATE_SHOWN_FIELD" => "Datumsformat (auf Seite angezeigt)",
	"DATETIME_EDIT_FIELD" => "Datum/Zeit-Format (bei Bearbeitung)",
	"DATE_EDIT_FIELD" => "Datumsformat (bei Bearbeitung)",
	"DATE_FORMAT_COLUMN" => "Datumsformat",

	"DB_LIBRARY_ERROR" => "PHP-Funktionen für {db_library} sind nicht definiert. Bitte überprüfen Sie die Datenbank-Einstellung in der Konfigurationsdatei php.ini.",
	"DB_CONNECT_ERROR" => "Kann nicht mit der Datenbank verbinden. Bitte überprüfen Sie Ihre Datenbank-Parameter.",
	"INSTALL_FINISHED_ERROR" => "Installation bereits abgeschlossen.",
	"WRITE_FILE_ERROR" => "Kein Schreibrechte auf Datei <b>'includes/var_definition.php'</b>. Zum Fortfahren ändern Sie bitte die Berechtigungen.",
	"WRITE_DIR_ERROR" => "Kein Schreibrechte auf Ordner <b>'includes/'</b>. Zum Fortfahren ändern Sie bitte die Berechtigungen.",
	"DUMP_FILE_ERROR" => "Dump-Datei '{file_name}' wurde nicht gefunden.",
	"DB_TABLE_ERROR" => "Tabelle '{table_name}' wurde nicht gefunden. Bitte bestücken Sie die Datenbank mit den notwendigen Daten.",
	"TEST_DATA_ERROR" => "Überprüfen Sie <b>{POPULATE_DB_FIELD}</b> bevor Sie die Tabellen mit Testdaten bestücken.",
	"DB_HOST_ERROR" => "Der angegebene Hostname wurde nicht gefunden.",
	"DB_PORT_ERROR" => "Kann nicht mit dem MySQL-Server auf dem angegeben Port verbinden.",
	"DB_USER_PASS_ERROR" => "Angebener Benutzername oder Passwort nicht korrekt.",
	"DB_NAME_ERROR" => "Die Anmeldedaten sind korrekt, aber die Datenbank '{db_name}' wurde nicht gefunden.",

	// upgrade messages
	"UPGRADE_TITLE" => "ViArt SHOP Aktualisierung",
	"UPGRADE_NOTE" => "Hinweis: Bitte führen Sie eine Datensicherung Ihrer Datenbank durch, bevor Sie fortfahren.",
	"UPGRADE_AVAILABLE_MSG" => "Datenbank-Aktualisierung verfügbar",
	"UPGRADE_BUTTON" => "Aktualisiere Datenbank nun auf {version_number}",
	"CURRENT_VERSION_MSG" => "Aktuell installierte Version",
	"LATEST_VERSION_MSG" => "Version verfügbar zur Installation",
	"UPGRADE_RESULTS_MSG" => "Aktualisierungs-Ergebnisse",
	"SQL_SUCCESS_MSG" => "SQL-Abfragen erfolgreich",
	"SQL_FAILED_MSG" => "SQL-Abfrage gescheitert",
	"SQL_TOTAL_MSG" => "SQL-Abfragen insgesamt ausgeführt",
	"VERSION_UPGRADED_MSG" => "Ihre Datenbank wurde aktualisiert auf",
	"ALREADY_LATEST_MSG" => "Sie haben bereits die neueste Version",
	"DOWNLOAD_NEW_MSG" => "Die neue Version wurde gefunden",
	"DOWNLOAD_NOW_MSG" => "Laden Sie Version {version_number} nun herunter",
	"DOWNLOAD_FOUND_MSG" => "Es wurde festgestellt, dass die neue Version {version_number} zum Download bereit steht. Bitte klicken Sie auf den Link unten um den Download zu starten. Nach Abschluss des Downloads und Ersetzen der Dateien vergessen Sie bitte nicht, die Upgrade-Routine noch einmal auszuführen.",
	"NO_XML_CONNECTION" => "Warnung! Keine Verbindung zu 'http://www.viart.com' verfügbar",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
