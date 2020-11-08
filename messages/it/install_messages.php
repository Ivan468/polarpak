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
	"INSTALL_TITLE" => "Installazione di ViArt SHOP",

	"INSTALL_STEP_1_TITLE" => "Installazione: Primo Passo",
	"INSTALL_STEP_1_DESC" => "Grazie per aver scelta ViArt SHOP. Per contiuare l'installazione riempire tutti i dettagli sottostanti. Attenzione: il database selezionato deve esistere. Se installate il database usando ODBC, es. MS Access e' neccessario creare il DSN prima di procedere.",
	"INSTALL_STEP_2_TITLE" => "Installazione: Secondo Passo",
	"INSTALL_STEP_2_DESC" => "",
	"DESIGN_SELECTION_MSG" => "Design Selection",
	"INSTALL_STEP_3_TITLE" => "Installazione: Terzo Passo",
	"INSTALL_STEP_3_DESC" => "Selezionare un layout per il sito. Potra' comunque essere cambiato successivamente.",
	"INSTALL_FINAL_TITLE" => "Installazione: Passo Finale",
	"SELECT_DATE_TITLE" => "Seleziona il formato Data",
	"GET_SUPPORT_MSG" => "Get Support",
	"INSTALLATION_HELP_MSG" => "Installation Help",

	"DB_SETTINGS_MSG" => "Settaggi Database",
	"DB_PROGRESS_MSG" => "Avanzamento del popolamento del database",
	"SELECT_PHP_LIB_MSG" => "Selezionare PHP library",
	"SELECT_DB_TYPE_MSG" => "Selezionare il tipo di Database",
	"ADMIN_SETTINGS_MSG" => "Impostazioni d'amministrazione",
	"DATE_SETTINGS_MSG" => "Formati Data",
	"NO_DATE_FORMATS_MSG" => "Nessun formato data disponibile",
	"INSTALL_FINISHED_MSG" => "L'installazione di base e' completa. Assicuratevi di aver controllato tutte le opzioni nella sezione di amministrazione e fate tutti i cambiamenti necessari.",
	"ACCESS_ADMIN_MSG" => "Per accedere alla sezione di amministrazione premere qui",
	"ADMIN_URL_MSG" => "URL per l'amministrazione",
	"MANUAL_URL_MSG" => "URL del Manuale",
	"THANKS_MSG" => "Grazie per aver scelto <b>ViArt SHOP</b>.",

	"DB_TYPE_FIELD" => "Tipo Database",
	"DB_TYPE_DESC" => "Selezionare <b>il tipo di database</b> che tu stai usando. Se usi SQL Server o Microsoft Access, seleziona ODBC.",
	"DB_PHP_LIB_FIELD" => "PHP Library",
	"DB_HOST_FIELD" => "Hostname",
	"DB_HOST_DESC" => "Inserisci <b>il nome</b> o <b>indirizzo IP del server</b> sul quale il database ViArt funzionera'. Se il database funzionera' sul tuo PC locale , puoi lasciare \"<b>localhost</b>\" e il valore port in bianco. Se il database sara' fornito da terze parti, contattare l'amministratore di tale sistema.",
	"DB_PORT_FIELD" => "Port",
	"DB_NAME_FIELD" => "Nome del database / DSN",
	"DB_NAME_DESC" => "Se stai usando un database come MySQL o PostgreSQL allora inserisci il <b>nome del database</b> dove desideri che ViArt crei le tabelle. Tale database deve gia' esistere Se stai installando ViArt in ambiente di test sul tuo PC locale, esiste un database di \"<b>test</b>\"  che puoi usare. Altrimenti crea un database vuoto chiamato ViArt. Se stai usando Microsoft Access o SQL Server allora il Nome del Database dovra' essere il <b>nome del DSN</b> che hai specificato nella sezione Data Sources (ODBC) del tuo Pannello di Controllo.",
	"DB_USER_FIELD" => "Nome Utente",
	"DB_PASS_FIELD" => "Password",
	"DB_USER_PASS_DESC" => "<b>Username</b> and <b>Password</b> - Inserire nome utente e password per accedere al database. Se state usando una installazione di test locale il nome utente sara' probabilmente \"<b>root</b>\" e la password probabilmente sara' vuota. Questo va bene per i test ma non e' sicuro su server in produzione.",
	"DB_PERSISTENT_FIELD" => "Persistent Connection",
	"DB_PERSISTENT_DESC" => "Per usre MySQL/Postgre persistent connections, selezionate questa casella. Se non sapete cosa significa, allora lasciarlo non selezionato e' la soluzione migliore.",
	"DB_CREATE_DB_FIELD" => "Create DB",
	"DB_CREATE_DB_DESC" => "to create database if possible, tick this box. Works only for MySQL and Postgre",
	"DB_POPULATE_FIELD" => "Popolare il DB",
	"DB_POPULATE_DESC" => "per creare la struttura delle tabelle del database e popolarle selezionare la checkbox",
	"DB_TEST_DATA_FIELD" => "Dati di prova",
	"DB_TEST_DATA_DESC" => "per aggiungere i dati di prova al database selezionare la checkbox",
	"ADMIN_EMAIL_FIELD" => "Indirizzo Email Amministratore",
	"ADMIN_LOGIN_FIELD" => "Nome Utente Amministratore",
	"ADMIN_PASS_FIELD" => "Password Amministratore",
	"ADMIN_CONF_FIELD" => "Conferma password",
	"DATETIME_SHOWN_FIELD" => "Formato Ora (mostrata sul sito)",
	"DATE_SHOWN_FIELD" => "Formato Data (mostrata sul sito)",
	"DATETIME_EDIT_FIELD" => "Formato Ora (per modifica)",
	"DATE_EDIT_FIELD" => "Formato Data (per modifica)",
	"DATE_FORMAT_COLUMN" => "Formato Data",

	"DB_LIBRARY_ERROR" => "Funzioni PHP per {db_library} non definite. Controlla nel file di configurazione php.ini i settaggi del database.",
	"DB_CONNECT_ERROR" => "Impossibile contattare il database. Controllare i parametri di configurazione.",
	"INSTALL_FINISHED_ERROR" => "Processo di installazione gia' completato",
	"WRITE_FILE_ERROR" => "Non hai i permessi di scrittura sul file <b>'includes/var_definition.php'</b>. Modificare i permessi prima di procedere.",
	"WRITE_DIR_ERROR" => "Non hai il permesso di scrittura nella cartella <b>'includes/'</b>. Cambiare i permessi della cartella prima di continuare.",
	"DUMP_FILE_ERROR" => "Dump file '{file_name}' non e' stato trovato.",
	"DB_TABLE_ERROR" => "La tabella '{table_name}' non e' stata trovata. Popolare il database con i dati necessari.",
	"TEST_DATA_ERROR" => "Controllare <b>{POPULATE_DB_FIELD}</b> prima di popolare le tabelle con il dati di prova.",
	"DB_HOST_ERROR" => "L'hostname specificato non e' stato trovato.",
	"DB_PORT_ERROR" => "Impossibile contattare MySQL server alla porta specificata.",
	"DB_USER_PASS_ERROR" => "Il nome utente o la password specificate non sono corrette.",
	"DB_NAME_ERROR" => "I dati di Login sono corretti, ma il database  '{db_name}' non esiste.",

	// upgrade messages
	"UPGRADE_TITLE" => "Aggiornamento di ViArt SHOP",
	"UPGRADE_NOTE" => "Nota: Si consiglia di eseguire un backup del database prima di procedere.",
	"UPGRADE_AVAILABLE_MSG" => "Aggiornamento database disponibile",
	"UPGRADE_BUTTON" => "Aggiorna il database alla {version_number} now",
	"CURRENT_VERSION_MSG" => "Versione attualmente installata",
	"LATEST_VERSION_MSG" => "Versione disponibile per l'installazione",
	"UPGRADE_RESULTS_MSG" => "Risultati dell'aggiornamento",
	"SQL_SUCCESS_MSG" => "SQL queries corretta",
	"SQL_FAILED_MSG" => "SQL queries fallita",
	"SQL_TOTAL_MSG" => "Totale SQL queries eseguite",
	"VERSION_UPGRADED_MSG" => "Il database e' stato aggiornato a",
	"ALREADY_LATEST_MSG" => "Hai gia' l'ultima versione",
	"DOWNLOAD_NEW_MSG" => "Una nuova versione e' stata rilevata",
	"DOWNLOAD_NOW_MSG" => "Scarica la versione {version_number} ora",
	"DOWNLOAD_FOUND_MSG" => "Rilevata nuova {version_number} versione disponibile per il download. Cliccare il link sottostante per iniziare il download. Dopo aver completato il download e rimpiazzato i file non dimenticare di rilanciare la routine di Upgrade.",
	"NO_XML_CONNECTION" => "Attenzione! Nessuna connessione a 'http://www.viart.com' disponibile!",

	"END_USER_LICENSE_AGREEMENT_MSG" => "End User License Agreement",
	"AGREE_LICENSE_AGREEMENT_MSG" => "I have read and agree to the License Agreement",
	"READ_LICENSE_AGREEMENT_MSG" => "Click here to read license agreement",
	"LICENSE_AGREEMENT_ERROR" => "Please read and agree to the License Agreement before proceeding.",

);
$va_messages = array_merge($va_messages, $messages);
